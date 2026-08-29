<?php

namespace App\Http\Controllers\Admin;

use App\Centresidence\Models\CentresidenceCommissionInvoice;
use App\Centresidence\Models\Device;
use App\Centresidence\Models\FacilityDefault;
use App\Centresidence\Models\FinanceApplication;
use App\Centresidence\Models\FinanceFacility;
use App\Centresidence\Models\FinancePartner;
use App\Centresidence\Models\Gateway;
use App\Centresidence\Models\InfrastructureTopology;
use App\Centresidence\Models\Module;
use App\Centresidence\Models\OwnerInfrastructureInvoice;
use App\Centresidence\Models\PropertyModule;
use App\Centresidence\Models\SelfFinancedModule;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

/**
 * Admin read-only visibility into the Centresidence Infrastructure & Finance OS
 * (handbook §9.10 admin views, §20 commission transparency). Every method is
 * guarded so the pages render a friendly notice if the module hasn't been
 * migrated yet, rather than erroring.
 */
class CentresidenceController extends Controller
{
    private function migrated(): bool
    {
        return Schema::hasTable('finance_facilities') && Schema::hasTable('modules');
    }

    public function index()
    {
        if (! $this->migrated()) {
            return view('admin.centresidence.not-migrated', ['pageTitle' => 'Centresidence']);
        }

        $activeFacilities = FinanceFacility::where('status', 'active');
        $commission = CentresidenceCommissionInvoice::query();

        $metrics = [
            'active_facilities'      => (clone $activeFacilities)->count(),
            'outstanding_principal'  => (clone $activeFacilities)->sum('outstanding_principal'),
            'expected_monthly'       => (clone $activeFacilities)->sum('monthly_target'),
            'facilities_in_default'  => FinanceFacility::where('status', 'defaulted')->count(),
            'platform_fees'          => FinanceFacility::sum('platform_fee_amount'),
            'pending_applications'   => FinanceApplication::whereIn('status', ['submitted', 'under_review'])->count(),
            'partners'               => FinancePartner::count(),
            'commission_metered'     => (clone $commission)->sum('metered_commission_total'),
            'commission_non_metered' => (clone $commission)->sum('non_metered_commission_total'),
            'commission_invoices'    => (clone $commission)->count(),
            'fallback_active'        => (clone $commission)->where('fallback_deduction_active', true)->count(),
            'active_modules'         => PropertyModule::where('status', 'active')->count(),
            'active_devices'         => Device::where('status', 'active')->count(),
            'gateways'               => Gateway::count(),
        ];

        $recentFacilities = FinanceFacility::with('partner', 'owner')->latest()->limit(8)->get();
        $recentApplications = FinanceApplication::with('owner', 'partner')->latest()->limit(8)->get();

        return view('admin.centresidence.dashboard', [
            'pageTitle' => 'Centresidence Overview',
            'metrics' => $metrics,
            'recentFacilities' => $recentFacilities,
            'recentApplications' => $recentApplications,
        ]);
    }

    public function partners()
    {
        $partners = $this->migrated()
            ? FinancePartner::withCount('products')->latest()->paginate(20)
            : collect();

        return view('admin.centresidence.partners', compact('partners') + ['pageTitle' => 'Finance Partners']);
    }

    public function applications()
    {
        $applications = $this->migrated()
            ? FinanceApplication::with('owner', 'partner', 'module')->latest()->paginate(20)
            : collect();

        return view('admin.centresidence.applications', compact('applications') + ['pageTitle' => 'Finance Applications']);
    }

    public function facilities()
    {
        $facilities = $this->migrated()
            ? FinanceFacility::with('partner', 'owner', 'property')->latest()->paginate(20)
            : collect();

        return view('admin.centresidence.facilities', compact('facilities') + ['pageTitle' => 'Finance Facilities']);
    }

    /** Confirm a partner-recorded disbursement (Centresidence is the payee for installer modules). */
    public function confirmDisbursement(int $id, \App\Centresidence\Services\FinanceFacilityService $facilities)
    {
        $facility = FinanceFacility::findOrFail($id);
        if ($facility->disbursement_status !== FinanceFacility::DISBURSE_PENDING) {
            return back()->with('error', __('Nothing to confirm — this facility isn’t awaiting confirmation.'));
        }
        $facilities->confirmDisbursement($facility);
        // Owner notify + SMS handled by the FacilityDisbursed listener.
        return back()->with('success', __('Disbursement confirmed. Repayment can now begin.'));
    }

    /** Manual admin lever: record a disbursement done outside the system (bank/M-Pesa) and release the facility. */
    public function forceDisburse(Request $request, int $id, \App\Centresidence\Services\FinanceFacilityService $facilities)
    {
        $facility = FinanceFacility::findOrFail($id);
        if ($facility->isDisbursed()) {
            return back()->with('error', __('This facility is already disbursed.'));
        }
        $data = $request->validate([
            'disbursement_channel'   => 'required|in:mpesa,bank,manual',
            'disbursement_reference' => 'nullable|string|max:191',
        ]);
        $facility->forceFill(['disbursement_channel' => $data['disbursement_channel']])->save();
        $facilities->disburse($facility, $data['disbursement_reference'] ?? null, $data['disbursement_channel']);
        // Owner notify + SMS handled by the FacilityDisbursed listener.
        return back()->with('success', __('Facility marked disbursed. Repayment can now begin.'));
    }

    /** Partner remittances — prepare batches from collected repayments, mark them sent (bank), confirm. */
    public function remittances()
    {
        $batches = \App\Centresidence\Models\PartnerRemittanceBatch::with('partner', 'items.facility', 'items.settlementTransaction')->latest()->paginate(20);
        $pendingPartnerIds = \App\Centresidence\Models\SettlementTransaction::query()
            ->where('beneficiary_type', \App\Centresidence\Models\SettlementTransaction::BENEFICIARY_PARTNER)
            ->where('reconciliation_status', \App\Centresidence\Models\SettlementTransaction::RECON_PENDING)
            ->distinct()->pluck('beneficiary_id');

        return view('admin.centresidence.remittances', compact('batches', 'pendingPartnerIds') + ['pageTitle' => 'Remittances']);
    }

    /** Prepare a remittance batch for every partner with collected-but-unremitted repayments. */
    public function remittancePrepare(\App\Centresidence\Services\PartnerRemittanceService $remittances)
    {
        $ids = \App\Centresidence\Models\SettlementTransaction::query()
            ->where('beneficiary_type', \App\Centresidence\Models\SettlementTransaction::BENEFICIARY_PARTNER)
            ->where('reconciliation_status', \App\Centresidence\Models\SettlementTransaction::RECON_PENDING)
            ->distinct()->pluck('beneficiary_id');

        $n = 0;
        foreach ($ids as $pid) {
            if ($remittances->prepareBatchForPartner((int) $pid)) {
                $n++;
            }
        }
        return back()->with('success', __(':n remittance batch(es) prepared.', ['n' => $n]));
    }

    /** Manual lever: record that a batch was paid to the partner by bank; they confirm receipt. */
    public function remittanceMarkSent(Request $request, int $id, \App\Centresidence\Services\PartnerRemittanceService $remittances)
    {
        $batch = \App\Centresidence\Models\PartnerRemittanceBatch::findOrFail($id);
        $batch->forceFill(['settlement_method' => 'bank_transfer'])->save();
        $remittances->markSent($batch, $request->input('reference'));

        return back()->with('success', __('Marked sent — the partner will confirm receipt.'));
    }

    // ── Integrations (operational drivers + secret status) ─────────────────

    /**
     * Safe, DB-backed operational toggles + a read-only status of the secrets
     * that stay in .env. Lets an operator go live without editing .env; secret
     * VALUES are never shown or editable here.
     */
    public function integrations()
    {
        $drivers = [
            'collection' => getOption('centresidence_collection_driver') ?: config('centresidence.collections.driver', 'log'),
            'payout'     => getOption('centresidence_payout_driver') ?: config('centresidence.payouts.driver', 'log'),
            'chirpstack' => getOption('centresidence_chirpstack_driver') ?: config('centresidence.chirpstack.driver', 'simulated'),
        ];

        // Secret presence only — never the value.
        $secrets = [
            ['M-Pesa API key/secret', (bool) config('mpesa.mpesa_consumer_key') && (bool) config('mpesa.mpesa_consumer_secret'), __('Collections & payouts (STK / B2B)')],
            ['M-Pesa STK passkey',    (bool) config('mpesa.passkey'),              __('STK push')],
            ['M-Pesa payout initiator', (bool) config('mpesa.initiator_name') && (bool) config('mpesa.initiator_password'), __('B2B partner payouts')],
            ['ChirpStack API token',  (bool) config('centresidence.chirpstack.api_token'), __('Live LoRaWAN provisioning')],
            ['ChirpStack webhook secret', (bool) config('centresidence.chirpstack.webhook_secret'), __('Authenticating meter uplinks')],
        ];

        return view('admin.centresidence.integrations', [
            'pageTitle' => 'Integrations',
            'drivers'   => $drivers,
            'secrets'   => $secrets,
        ]);
    }

    public function integrationsSave(Request $request)
    {
        $data = $request->validate([
            'collection_driver' => 'required|in:log,mpesa',
            'payout_driver'     => 'required|in:log,mpesa',
            'chirpstack_driver' => 'required|in:simulated,live',
        ]);

        setOption('centresidence_collection_driver', $data['collection_driver']);
        setOption('centresidence_payout_driver', $data['payout_driver']);
        setOption('centresidence_chirpstack_driver', $data['chirpstack_driver']);

        return back()->with('success', __('Integration settings saved. New payments and provisioning use these immediately.'));
    }

    /** Tell the owner (and partner) the facility is live and repayment starts. */
    private function notifyDisbursed(FinanceFacility $facility): void
    {
        if (function_exists('addNotification') && $facility->owner_id) {
            addNotification(
                __('Your financed infrastructure is live'),
                __('Facility :ref has been disbursed — repayment begins from your next rent.', ['ref' => $facility->facility_number ?? ('#' . $facility->id)]),
                route('owner.financing.mine'),
                null,
                $facility->owner_id
            );
        }
    }

    public function defaults()
    {
        $defaults = $this->migrated()
            ? FacilityDefault::with('facility.owner')->latest()->paginate(20)
            : collect();

        return view('admin.centresidence.defaults', compact('defaults') + ['pageTitle' => 'Defaults & Collections']);
    }

    public function revenue()
    {
        $commissionInvoices = $this->migrated()
            ? CentresidenceCommissionInvoice::with('owner', 'property')->latest()->paginate(20)
            : collect();
        $infraInvoices = $this->migrated()
            ? OwnerInfrastructureInvoice::with('owner', 'property')->latest()->limit(20)->get()
            : collect();

        return view('admin.centresidence.revenue', [
            'pageTitle' => 'Commission & Revenue',
            'commissionInvoices' => $commissionInvoices,
            'infraInvoices' => $infraInvoices,
        ]);
    }

    public function modules()
    {
        $modules = $this->migrated()
            ? Module::with('costComponents', 'pricingCatalogueItems')->orderBy('display_order')->get()
            : collect();

        return view('admin.centresidence.modules', compact('modules') + ['pageTitle' => 'Modules & Cost Components']);
    }

    /** Cost-model options for the component editor. */
    private const COST_MODELS = ['per_active_device', 'per_gateway_allocation', 'per_unit_consumed', 'flat_monthly'];

    public function moduleEdit(int $id)
    {
        $module = Module::with('pricingCatalogueItems', 'costComponents')->findOrFail($id);

        return view('admin.centresidence.module-edit', [
            'pageTitle' => 'Edit ' . $module->name,
            'module' => $module,
            'catalogue' => $module->pricingCatalogueItems->first(),
            'costModels' => self::COST_MODELS,
        ]);
    }

    private function costComponentData(Request $request): array
    {
        $validated = $request->validate([
            'component_name' => 'required|string|max:191',
            'cost_model' => 'required|in:' . implode(',', self::COST_MODELS),
            'rate' => 'required|numeric|min:0',
            'status' => 'required|in:active,inactive',
        ]);

        return $validated + [
            'requires_gateway'     => $request->boolean('requires_gateway'),
            'is_fallback_eligible' => $request->boolean('is_fallback_eligible'),
            'is_prorated'          => $request->boolean('is_prorated'),
        ];
    }

    public function costComponentStore(Request $request, int $moduleId)
    {
        Module::findOrFail($moduleId)->costComponents()->create($this->costComponentData($request));

        return back()->with('success', __('Cost component added.'));
    }

    public function costComponentUpdate(Request $request, int $id)
    {
        \App\Centresidence\Models\ModuleCostComponent::findOrFail($id)->update($this->costComponentData($request));

        return back()->with('success', __('Cost component updated.'));
    }

    public function costComponentDestroy(int $id)
    {
        \App\Centresidence\Models\ModuleCostComponent::findOrFail($id)->delete();

        return back()->with('success', __('Cost component removed.'));
    }

    public function moduleUpdate(Request $request, int $id)
    {
        $module = Module::findOrFail($id);

        $data = $request->validate([
            'name' => 'required|string|max:191',
            'tagline' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'cashflow_benefit' => 'nullable|string',
            'financier_overview' => 'nullable|string',
            'how_it_works' => 'nullable|string',
            'benefits' => 'nullable|string',
            'icon' => 'nullable|string|max:100',
            'accent_color' => 'nullable|string|max:20',
            'image' => 'nullable|image|max:4096',
            'unit_price' => 'nullable|numeric|min:0',
            'installation_cost' => 'nullable|numeric|min:0',
            'settlement_target' => 'nullable|in:centresidence,owner',
            'token_units_per_kes' => 'nullable|numeric|min:0',
            'token_commission_per_unit' => 'nullable|numeric|min:0',
        ]);

        // Benefits: one per line → array.
        $benefits = collect(preg_split('/\r\n|\r|\n/', (string) ($data['benefits'] ?? '')))
            ->map(fn ($b) => trim($b))->filter()->values()->all();

        $update = [
            'name' => $data['name'], 'tagline' => $data['tagline'] ?? null, 'description' => $data['description'] ?? null,
            'cashflow_benefit' => $data['cashflow_benefit'] ?? null, 'financier_overview' => $data['financier_overview'] ?? null,
            'how_it_works' => $data['how_it_works'] ?? null,
            'benefits' => $benefits, 'icon' => $data['icon'] ?? null, 'accent_color' => $data['accent_color'] ?? null,
            'is_financeable' => $request->boolean('is_financeable'), 'is_active' => $request->boolean('is_active'),
            'settlement_target' => $data['settlement_target'] ?? 'centresidence',
        ];

        if ($module->is_metered) {
            $update['token_units_per_kes'] = $data['token_units_per_kes'] ?? $module->token_units_per_kes;
            $update['token_commission_per_unit'] = $data['token_commission_per_unit'] ?? 0;
        }

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('centresidence/modules', 'public');
            $update['image_url'] = Storage::disk('public')->url($path);
        }

        $module->update($update);

        if ($request->filled('unit_price') || $request->filled('installation_cost')) {
            $catalogue = $module->pricingCatalogueItems()->first();
            $values = ['unit_price' => $data['unit_price'] ?? 0, 'installation_cost' => $data['installation_cost'] ?? 0];
            if ($catalogue) {
                $catalogue->update($values);
            } else {
                $module->pricingCatalogueItems()->create($values + ['item_name' => $module->name . ' Unit', 'is_active' => true]);
            }
        }

        return redirect()->route('admin.centresidence.modules')->with('success', __('Module updated.'));
    }

    public function selfFinanced()
    {
        $orders = $this->migrated()
            ? SelfFinancedModule::with('owner', 'property', 'module')->latest()->paginate(20)
            : collect();

        return view('admin.centresidence.self-financed', compact('orders') + ['pageTitle' => 'Self-financed Modules']);
    }

    public function deployForm(Request $request)
    {
        return view('admin.centresidence.deploy', [
            'pageTitle' => 'Deploy module',
            'properties' => \App\Models\Property::withCount('propertyUnits')->orderBy('name')->get(),
            'modules' => Module::where('is_active', true)->orderBy('display_order')->get(),
            'gateways' => Gateway::withCount('devices')->where('status', 'active')->orderBy('name')->get(),
            'prefill' => [
                'property_id' => $request->integer('property_id'),
                'module_id' => $request->integer('module_id'),
                'quantity' => $request->integer('quantity') ?: 1,
                'self_financed_id' => $request->integer('self_financed_id'),
            ],
        ]);
    }

    public function deployStore(Request $request, \App\Centresidence\Services\DeviceProvisioningService $provisioning)
    {
        $data = $request->validate([
            'property_id' => 'required|integer',
            'module_id' => 'required|integer',
            'quantity' => 'required|integer|min:1',
            'gateway_id' => 'nullable|integer|exists:cs_gateways,id',
            'self_financed_id' => 'nullable|integer',
        ]);

        $property = \App\Models\Property::withCount('propertyUnits')->findOrFail($data['property_id']);
        $module = Module::findOrFail($data['module_id']);

        // A property can't deploy more units than it physically has.
        $maxUnits = (int) $property->property_units_count;
        if ($maxUnits === 0) {
            return back()->withInput()->with('error', __('That property has no units yet — add units before deploying.'));
        }
        if ((int) $data['quantity'] > $maxUnits) {
            return back()->withInput()->with('error', __('Quantity exceeds the property\'s :max units.', ['max' => $maxUnits]));
        }

        try {
            $propertyModule = $provisioning->deploy(
                (int) $property->owner_user_id, $property->id, $module, (int) $data['quantity'],
                $data['gateway_id'] ?? null
            );
        } catch (\App\Centresidence\Exceptions\GatewayCapacityExceededException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        } catch (\App\Centresidence\Exceptions\ModuleDeploymentRequiresPaidPlanException $e) {
            return back()->withInput()->with('error', __(
                'This owner is on a free plan. Modules carry a monthly platform & gateway cost a free plan can\'t bill — the owner must move to a subscription or transaction plan before deployment.'
            ));
        }

        if (! empty($data['self_financed_id'])) {
            $order = SelfFinancedModule::find($data['self_financed_id']);
            if ($order) {
                app(\App\Centresidence\Services\SelfFinancingService::class)->markDeployed($order);
            }
        }

        $created = $provisioning->lastProvisionedCount;
        $message = $created > 0
            ? __(':n device(s) provisioned. Attach the real DevEUIs as the hardware is fitted.', ['n' => $created])
            : __('No new devices to provision — this module is already deployed to that quantity on the property.');

        // Carry BOTH filters: property_module_id shows exactly the freshly-deployed
        // set, property_id makes the property filter dropdown reflect "…meters for X".
        return redirect()->route('admin.centresidence.devices', [
            'property_module_id' => $propertyModule->id,
            'property_id'        => $propertyModule->property_id,
        ])->with('success', $message);
    }

    public function devices(Request $request)
    {
        $query = Device::with('propertyModule.module', 'propertyModule.property', 'gateway', 'propertyUnit')->latest();

        if ($pmId = $request->integer('property_module_id')) {
            $query->where('property_module_id', $pmId);
        }
        if ($propertyId = $request->integer('property_id')) {
            $query->whereHas('propertyModule', fn ($q) => $q->where('property_id', $propertyId));
        }
        if ($gatewayId = $request->integer('gateway_id')) {
            $query->where('gateway_id', $gatewayId);
        }
        if (($status = $request->get('status')) && in_array($status, ['provisioning', 'active', 'inactive', 'decommissioned'], true)) {
            $query->where('status', $status);
        }
        if ($search = trim((string) $request->get('q'))) {
            // Search by device reference (#id), DevEUI or name — so a reported
            // device can be found instantly among thousands.
            $query->where(function ($q) use ($search) {
                $q->where('dev_eui', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('id', ltrim($search, '#'));
            });
        }

        // Property filter list = only properties that actually have deployments.
        $deployedPropertyIds = PropertyModule::distinct()->pluck('property_id');
        $properties = \App\Models\Property::whereIn('id', $deployedPropertyIds)
            ->orderBy('name')->get();

        // Units per deployed property → populates each device's unit selector.
        $unitsByProperty = \App\Models\PropertyUnit::whereIn('property_id', $deployedPropertyIds)
            ->orderBy('unit_name')
            ->get(['id', 'property_id', 'unit_name'])
            ->groupBy('property_id');

        return view('admin.centresidence.devices', [
            'pageTitle' => 'Devices',
            'devices' => $query->paginate(50)->withQueryString(),
            'gateways' => Gateway::orderBy('name')->get(),
            'properties' => $properties,
            'unitsByProperty' => $unitsByProperty,
            'filters' => $request->only(['q', 'property_id', 'gateway_id', 'status', 'property_module_id']),
        ]);
    }

    public function deviceUpdate(Request $request, int $id)
    {
        $device = Device::findOrFail($id);
        $data = $request->validate([
            'dev_eui' => 'nullable|string|max:191|unique:devices,dev_eui,' . $device->id,
            'name' => 'nullable|string|max:191',
            'gateway_id' => 'nullable|integer|exists:cs_gateways,id',
            'property_unit_id' => 'nullable|integer|exists:property_units,id',
            'app_key' => ['nullable', 'string', 'regex:/^[0-9a-fA-F]{32}$/'],
            'status' => 'required|in:provisioning,active,inactive,decommissioned',
        ]);

        // A meter can only be attached to a unit on its OWN property (no cross-property leak).
        if (! empty($data['property_unit_id'])) {
            $propertyId = optional($device->propertyModule)->property_id;
            $unitOnProperty = \App\Models\PropertyUnit::where('id', $data['property_unit_id'])
                ->where('property_id', $propertyId)->exists();
            if (! $unitOnProperty) {
                return back()->with('error', __('That unit does not belong to this device’s property.'));
            }
        }

        // Respect gateway capacity when re-binding to a different gateway.
        if (! empty($data['gateway_id']) && (int) $data['gateway_id'] !== (int) $device->gateway_id) {
            $gateway = Gateway::find($data['gateway_id']);
            if ($gateway && $gateway->max_devices) {
                $onGateway = Device::where('gateway_id', $gateway->id)->count();
                if ($onGateway + 1 > (int) $gateway->max_devices) {
                    return back()->with('error', __('Gateway :name is at capacity (:max devices).', ['name' => $gateway->name, 'max' => $gateway->max_devices]));
                }
            }
        }

        $device->update([
            'dev_eui'          => $data['dev_eui'] ?: null,
            'name'             => $data['name'] ?? $device->name,
            'gateway_id'       => $data['gateway_id'] ?: null,
            'property_unit_id' => $data['property_unit_id'] ?: null,
            'status'           => $data['status'],
        ]);

        // OTAA AppKey (device secret): only overwrite when a new one is supplied —
        // an empty submit keeps the existing key. Never echoed back to the page.
        if (! empty($data['app_key'])) {
            $device->forceFill([
                'metadata' => array_merge($device->metadata ?? [], ['app_key' => strtolower($data['app_key'])]),
            ])->save();
        }

        // Live network: once a real DevEUI is set (placeholder replaced), (re)register
        // the device on ChirpStack so it can join. Idempotent + logs-and-continues.
        if (config('centresidence.chirpstack.driver') === 'live'
            && $device->dev_eui && ! str_starts_with($device->dev_eui, 'DEV-')) {
            app(\App\Centresidence\Services\ChirpStack\ChirpStackDriver::class)->registerDevice($device->fresh());
        }

        // Keep the module's active_meter_count in sync with status changes.
        if ($device->propertyModule) {
            $device->propertyModule->forceFill([
                'active_meter_count' => $device->propertyModule->activeDevices()->count(),
            ])->save();
        }

        return back()->with('success', __('Device updated.'));
    }

    public function gatewayStore(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:191',
            'eui' => 'nullable|string|max:191|unique:cs_gateways,eui',
            'vendor' => 'nullable|string|max:191',
            'model' => 'nullable|string|max:191',
            'max_devices' => 'nullable|integer|min:1',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        Gateway::create($data + [
            'status' => 'active',
            // Simulated while the ChirpStack driver is not live.
            'is_simulated' => config('centresidence.chirpstack.driver', 'simulated') !== 'live',
        ]);

        return redirect()->route('admin.centresidence.infrastructure')->with('success', __('Gateway registered.'));
    }

    public function gatewayUpdate(Request $request, int $id)
    {
        $gateway = Gateway::withCount('devices')->findOrFail($id);

        $data = $request->validate([
            'name' => 'required|string|max:191',
            'eui' => 'nullable|string|max:191|unique:cs_gateways,eui,' . $gateway->id,
            'max_devices' => 'nullable|integer|min:1',
            'status' => 'required|in:active,inactive,maintenance',
        ]);

        if (! empty($data['max_devices']) && $data['max_devices'] < $gateway->devices_count) {
            return back()->with('error', __('Capacity (:n) is below the :c devices already bound to this gateway.', [
                'n' => $data['max_devices'], 'c' => $gateway->devices_count,
            ]));
        }

        $gateway->update([
            'name' => $data['name'], 'eui' => $data['eui'] ?: null,
            'max_devices' => $data['max_devices'] ?: null, 'status' => $data['status'],
        ]);

        return back()->with('success', __('Gateway updated.'));
    }

    public function partnerStore(Request $request, \App\Centresidence\Services\FinancePartnerService $partners)
    {
        $data = $request->validate([
            'company_name' => 'required|string|max:191',
            'trading_name' => 'nullable|string|max:191',
            'contact_person' => 'nullable|string|max:191',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:50',
            'password' => 'required|string|min:6',
            'origination_fee_percentage' => 'nullable|numeric|min:0|max:100',
            'servicing_fee_percentage'   => 'nullable|numeric|min:0|max:100',
        ]);

        $partners->provision(
            [
                'company_name'   => $data['company_name'],
                'trading_name'   => $data['trading_name'] ?? null,
                'contact_person' => $data['contact_person'] ?? null,
                'email'          => $data['email'],
                'phone'          => $data['phone'] ?? null,
                'status'         => FinancePartner::STATUS_ACTIVE,
                'origination_fee_percentage' => $data['origination_fee_percentage'] ?? null,
                'servicing_fee_percentage'   => $data['servicing_fee_percentage'] ?? null,
            ],
            [
                'first_name' => $data['contact_person'] ?: $data['company_name'],
                'last_name'  => 'Partner',
                'email'      => $data['email'],
                'password'   => $data['password'],
            ]
        );

        return redirect()->route('admin.centresidence.partners')
            ->with('success', __('Finance partner created. They can sign in with that email and password (role: finance partner).'));
    }

    /** Set (or clear, to fall back to the platform default) a partner's Centresidence fee rates. */
    public function partnerFees(Request $request, int $id)
    {
        $data = $request->validate([
            'origination_fee_percentage' => 'nullable|numeric|min:0|max:100',
            'servicing_fee_percentage'   => 'nullable|numeric|min:0|max:100',
        ]);

        $partner = FinancePartner::findOrFail($id);
        $partner->update([
            'origination_fee_percentage' => $data['origination_fee_percentage'] ?? null,
            'servicing_fee_percentage'   => $data['servicing_fee_percentage'] ?? null,
        ]);

        return redirect()->route('admin.centresidence.partners')
            ->with('success', __('Fees updated for :name. New facilities and remittances use these rates.', ['name' => $partner->company_name]));
    }

    public function infrastructure()
    {
        $gateways = $this->migrated() ? Gateway::withCount('devices')->latest()->limit(50)->get() : collect();
        $topology = $this->migrated()
            ? InfrastructureTopology::with('owner', 'property')->where('status', 'active')->latest()->limit(50)->get()
            : collect();

        return view('admin.centresidence.infrastructure', [
            'pageTitle' => 'Infrastructure',
            'gateways' => $gateways,
            'topology' => $topology,
        ]);
    }
}
