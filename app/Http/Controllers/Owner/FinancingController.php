<?php

namespace App\Http\Controllers\Owner;

use App\Centresidence\Exceptions\OwnerNotInTransactionModeException;
use App\Centresidence\Exceptions\UnderwritingFailedException;
use App\Centresidence\Models\FinanceApplication;
use App\Centresidence\Models\FinanceFacility;
use App\Centresidence\Models\FinancePartnerModule;
use App\Centresidence\Models\Module;
use App\Centresidence\Models\ModulePlatformFeeConfig;
use App\Centresidence\Models\ModulePricingCatalogueItem;
use App\Centresidence\Models\SelfFinancedModule;
use App\Centresidence\Services\CashflowService;
use App\Centresidence\Services\SelfFinancingService;
use App\Centresidence\Services\FacilityInterestService;
use App\Centresidence\Services\FinanceApplicationService;
use App\Centresidence\Services\FinanceFacilityService;
use App\Centresidence\Services\FinancePartnerService;
use App\Centresidence\Services\InfrastructureCostEngine;
use App\Centresidence\Services\PaymentModeService;
use App\Http\Controllers\Controller;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

/**
 * Owner-facing infrastructure financing (distinct from the existing product
 * "marketplace"/"My Shop"). Owners browse partner financing offers, apply
 * (gated to transaction pricing mode), and manage their facilities — including
 * accelerated repayment and early settlement.
 */
class FinancingController extends Controller
{
    private function migrated(): bool
    {
        return Schema::hasTable('finance_partner_modules');
    }

    /** Marketplace-style module discovery — every module owners can deploy. */
    public function index(PaymentModeService $modes, FinancePartnerService $partners)
    {
        $modules = collect();
        if ($this->migrated()) {
            $modules = Module::query()->where('is_active', true)->where('is_financeable', true)
                ->orderBy('display_order')->get()
                ->map(fn (Module $m) => [
                    'module' => $m,
                    'catalogue' => ModulePricingCatalogueItem::where('module_id', $m->id)->where('is_active', true)->first(),
                    'financiers' => $partners->marketplaceProductsForModule($m->id)->count(),
                ]);
        }

        return view('owner.financing.index', [
            'pageTitle' => 'Infrastructure Financing',
            'modules' => $modules,
            'isTransactionMode' => $modes->isTransactionMode((int) auth()->id()),
        ]);
    }

    /** Module detail — what it is, how it boosts cashflow, and who finances it. */
    public function module(int $moduleId, PaymentModeService $modes, FinancePartnerService $partners)
    {
        $module = Module::where('is_active', true)->findOrFail($moduleId);

        return view('owner.financing.module', [
            'pageTitle' => $module->name,
            'module' => $module,
            'catalogue' => ModulePricingCatalogueItem::where('module_id', $module->id)->where('is_active', true)->first(),
            'products' => $this->migrated() ? $partners->marketplaceProductsForModule($module->id) : collect(),
            'isTransactionMode' => $modes->isTransactionMode((int) auth()->id()),
        ]);
    }

    /** Application form for a chosen partner product (or a mode-switch prompt). */
    public function apply(int $partnerModuleId, PaymentModeService $modes, InfrastructureCostEngine $infra)
    {
        $product = FinancePartnerModule::with('partner', 'module')->findOrFail($partnerModuleId);
        $catalogue = ModulePricingCatalogueItem::where('module_id', $product->module_id)->where('is_active', true)->first();

        if (! $modes->isTransactionMode((int) auth()->id())) {
            return view('owner.financing.switch-mode', [
                'pageTitle' => 'Switch to transaction mode',
                'product' => $product,
            ]);
        }

        // withCount lets the form offer "apply to all N units"; withSum gives
        // the property's monthly rent for the affordability projection.
        $properties = Property::where('owner_user_id', auth()->id())
            ->withCount('propertyUnits')
            ->withSum('propertyUnits', 'general_rent')->get();

        // The owner's EXISTING transaction-module infra per property — this
        // recurring cost competes with a new facility for the same rent, so the
        // projection counts it (matching the server gate) and we surface it.
        $existingInfra = $properties->mapWithKeys(function (Property $p) use ($infra) {
            $row = $infra->projectedMonthlyForProperty($p);

            return [$p->id => ['cost' => $row['cost']->toFloat(), 'modules' => $row['modules']]];
        });

        return view('owner.financing.apply', [
            'pageTitle' => 'Apply for financing',
            'product' => $product,
            'catalogue' => $catalogue,
            'properties' => $properties,
            'existingInfra' => $existingInfra,
            // Global ceiling on rent deductions, surfaced so the owner sees if a
            // facility would push them past it, plus the max they may consent to.
            'rentCapPct' => (int) config('centresidence.billing.max_total_rent_deduction_percentage', 60),
            'consentMaxPct' => (int) config('centresidence.billing.max_consented_rent_deduction_percentage', 90),
            // The financed module's own monthly infra (per-device + flat), so the
            // affordability projection matches the server-side feasibility gate.
            'infraPerDevice' => (float) ($product->module?->activeCostComponents->where('cost_model', 'per_active_device')->sum('rate') ?? 0),
            'infraFlat' => (float) ($product->module?->activeCostComponents->where('cost_model', 'flat_monthly')->sum('rate') ?? 0),
            // The exact platform-fee % the calculator will charge, so the live
            // estimate on the form matches the server to the cent.
            'feePct' => (float) ModulePlatformFeeConfig::where('module_id', $product->module_id)
                ->where('is_active', true)->latest('id')->value('fee_percentage'),
        ]);
    }

    /** "Rent & deductions" — what Centresidence took from each rent payment and why. */
    public function deductions()
    {
        $ownerId = (int) auth()->id();
        $rows = collect();

        if ($this->migrated() && Schema::hasTable('settlement_transactions')) {
            // The owner's rent payments (orders) that triggered settlements.
            $orderIds = \App\Models\Order::whereHas('invoice', fn ($q) => $q->where('owner_user_id', $ownerId))->pluck('id');

            $byOrder = \App\Centresidence\Models\SettlementTransaction::whereIn('rent_transaction_id', $orderIds)
                ->orderByDesc('created_at')->get()->groupBy('rent_transaction_id');

            $orders = \App\Models\Order::with('invoice.propertyUnit.property')
                ->whereIn('id', $byOrder->keys())->get()->keyBy('id');

            // The platform commission (transaction-mode 1%) is booked separately by
            // CommissionService on the rent credit — pull it per order so the owner
            // sees the FULL deduction picture and a correct net.
            $platformFees = \App\Models\WalletTransaction::whereIn('invoice_order_id', $byOrder->keys())
                ->where('type', 'credit')->where('transaction_source', 'rent')
                ->get()->groupBy('invoice_order_id')
                ->map(fn ($g) => (float) $g->sum('commission_amount'));

            $rows = $byOrder->map(function ($txns, $orderId) use ($orders, $platformFees) {
                $order = $orders->get($orderId);
                $platformFee = (float) ($platformFees[$orderId] ?? 0);
                $commission = (float) $txns->where('transaction_type', 'commission_recovery')->sum('amount');
                $infra = (float) $txns->where('transaction_type', 'infrastructure_recovery')->sum('amount');
                $facility = (float) $txns->whereIn('transaction_type', ['rent_deduction_principal', 'rent_deduction_interest', 'rent_deduction_penalty'])->sum('amount');
                $deducted = (float) $txns->sum('amount') + $platformFee;
                $gross = (float) ($order->amount ?? 0);

                return [
                    'date'         => $txns->max('created_at'),
                    'property'     => optional(optional(optional($order)->invoice)->propertyUnit)->property,
                    'gross'        => $gross,
                    'platform_fee' => $platformFee,
                    'commission'   => $commission,
                    'infra'        => $infra,
                    'facility'     => $facility,
                    'deducted'     => $deducted,
                    'net'          => $gross > 0 ? $gross - $deducted : null,
                ];
            })->sortByDesc('date')->values();
        }

        return view('owner.financing.deductions', ['pageTitle' => 'Rent & deductions', 'rows' => $rows]);
    }

    /** Switch the owner onto transaction mode so financing can proceed. */
    public function switchMode(Request $request, PaymentModeService $modes)
    {
        $modes->switchTo((int) auth()->id(), PaymentModeService::MODE_TRANSACTION);

        return redirect()->route('owner.financing.apply', $request->input('partner_module_id'))
            ->with('success', __('You are now on transaction mode and can apply for financing.'));
    }

    /** Submit a financing application (create draft + soft underwriting). */
    public function store(Request $request, FinanceApplicationService $applications, CashflowService $cashflow, InfrastructureCostEngine $infra)
    {
        $data = $request->validate([
            'finance_partner_module_id' => 'required|integer',
            'property_id' => 'required|integer',
            'catalogue_item_id' => 'required|integer',
            'quantity' => 'required|integer|min:1',
            'repayment_months' => 'required|integer|min:1',
            'owner_contribution' => 'nullable|numeric|min:0',
            'consented_deduction_cap' => 'nullable|integer|min:60|max:' . (int) config('centresidence.billing.max_consented_rent_deduction_percentage', 90),
        ]);

        $product = FinancePartnerModule::findOrFail($data['finance_partner_module_id']);
        $property = Property::where('owner_user_id', auth()->id())
            ->withCount('propertyUnits')
            ->withSum('propertyUnits', 'general_rent')->findOrFail($data['property_id']);
        $catalogue = ModulePricingCatalogueItem::findOrFail($data['catalogue_item_id']);

        // A property needs units before any module can be deployed on it.
        $maxUnits = (int) $property->property_units_count;
        if ($maxUnits === 0) {
            return redirect()->route('owner.financing.apply', $product->id)->with('error', __(
                'Add units to :name before deploying modules — a property needs units first.',
                ['name' => $property->name ?? __('this property')]
            ));
        }
        // A property can't deploy more units than it physically has.
        if ((int) $data['quantity'] > $maxUnits) {
            return redirect()->route('owner.financing.apply', $product->id)->with('error', __(
                'You selected :q units but :name has only :max units. Reduce the quantity.',
                ['q' => (int) $data['quantity'], 'name' => $property->name ?? __('this property'), 'max' => $maxUnits]
            ));
        }

        // Server-side guard so the application can never exceed what the partner
        // actually offers (the form shows the same limits). Mirrors the
        // calculator: total project cost = (hardware + install) × qty + platform
        // fee; the owner may pay a down-payment, so the partner only finances the
        // remainder — the min/max ceiling applies to that FINANCED amount.
        $feePct = (float) ModulePlatformFeeConfig::where('module_id', $product->module_id)
            ->where('is_active', true)->latest('id')->value('fee_percentage');
        $perUnit = (float) $catalogue->unit_price + (float) $catalogue->installation_cost;
        $total = $perUnit * (int) $data['quantity'] * (1 + $feePct / 100);
        $contribution = min(max((float) ($data['owner_contribution'] ?? 0), 0), $total);
        $financed = $total - $contribution;

        $max = (float) $product->max_amount;
        $min = (float) $product->min_amount;
        if ($financed <= 0.0) {
            return redirect()->route('owner.financing.apply', $product->id)->with('error', __(
                'Your contribution covers the whole cost — use the self-finance option instead of partner financing.'
            ));
        }
        if ($max > 0 && $financed > $max + 0.01) {
            return redirect()->route('owner.financing.apply', $product->id)->with('error', __(
                'You would be financing KES :financed, above this financier\'s ceiling of KES :max. Add a larger down-payment, reduce units, or choose another financier.',
                ['financed' => number_format($financed, 2), 'max' => number_format($max, 2)]
            ));
        }
        if ($min > 0 && $financed < $min - 0.01) {
            return redirect()->route('owner.financing.apply', $product->id)->with('error', __(
                'You would be financing only KES :financed, below this financier\'s minimum of KES :min. Lower your down-payment.',
                ['financed' => number_format($financed, 2), 'min' => number_format($min, 2)]
            ));
        }

        try {
            $application = $applications->createDraft([
                'owner_id' => (int) auth()->id(),
                'property_id' => $property->id,
                'module_id' => $product->module_id,
                'finance_partner_id' => $product->finance_partner_id,
                'finance_partner_module_id' => $product->id,
                'catalogue_item_id' => $data['catalogue_item_id'],
                'quantity' => (int) $data['quantity'],
                'repayment_months' => (int) $data['repayment_months'],
                'owner_contribution' => $contribution,
                'consented_deduction_cap' => ! empty($data['consented_deduction_cap']) && $data['consented_deduction_cap'] > 60
                    ? (int) $data['consented_deduction_cap'] : null,
                'property_rent' => (float) ($property->property_units_sum_general_rent ?? 0),
                // Existing transaction-module infra on this property already draws
                // from its rent — the gate counts it so the facility can't be sized
                // beyond what the remaining rent budget can actually service.
                'existing_infra' => $infra->projectedMonthlyForProperty($property)['cost']->toFloat(),
            ]);

            $applications->submit($application, $cashflow->underwritingContext($application), (int) auth()->id());
        } catch (\App\Centresidence\Exceptions\FacilityInfeasibleException $e) {
            return redirect()->route('owner.financing.apply', $product->id)->with('error', __(
                'This facility needs about :req% of this property\'s rent each month — above your :cap% deduction limit, so it could not repay within the agreed term. To proceed: accept a higher deduction limit, add a larger down-payment, or choose a longer repayment term (up to :max months).',
                ['req' => round($e->requiredPct), 'cap' => round($e->effectiveCapPct), 'max' => (int) $product->max_repayment_months]
            ));
        } catch (OwnerNotInTransactionModeException $e) {
            return redirect()->route('owner.financing.apply', $product->id)
                ->with('error', __('Please switch to transaction mode before applying.'));
        } catch (UnderwritingFailedException $e) {
            $reasons = collect($e->hardFailures)->pluck('message')->filter()->implode(' ');

            return redirect()->route('owner.financing.apply', $product->id)
                ->with('error', __('Your property did not meet this product\'s requirements: ') . $reasons);
        }

        return redirect()->route('owner.financing.mine')
            ->with('success', __('Application submitted. The finance partner will review it.'));
    }

    /** The owner's applications + active facilities + self-financed orders. */
    public function mine(FacilityInterestService $interest)
    {
        $applications = collect();
        $facilities = collect();
        $selfFinanced = collect();
        if ($this->migrated()) {
            $applications = FinanceApplication::with('partner', 'module')
                ->where('owner_id', auth()->id())->latest()->get();
            $facilities = FinanceFacility::with('partner', 'property', 'application.partnerModule')
                ->where('owner_id', auth()->id())->latest()->get()
                ->each(function (FinanceFacility $f) use ($interest) {
                    $q = $interest->earlySettlementQuote($f);
                    $f->payoff           = $q['total']->toDecimal();
                    $f->payoff_principal = $q['principal']->toDecimal();
                    $f->payoff_interest  = $q['interest']->toDecimal();
                    $f->payoff_penalty   = $q['penalty']->toDecimal();
                    $f->payoff_fee       = $q['fee']->toDecimal();
                    // Partner's early-settlement policy — surfaced so we never show
                    // a settle button the financier disallows, and the fee is honest.
                    $pm = $f->application?->partnerModule;
                    $f->early_repayment_allowed = $pm ? ($pm->early_repayment_allowed !== false) : true;
                    $f->early_repayment_fee_pct = $pm ? (float) $pm->early_repayment_penalty_percentage : 0.0;
                    $f->accelerated_repayment_allowed = $pm ? ($pm->accelerated_repayment_allowed !== false) : true;
                });
            $selfFinanced = SelfFinancedModule::with('module', 'property')
                ->where('owner_id', auth()->id())->latest()->get();
        }

        return view('owner.financing.mine', [
            'pageTitle' => 'My Financing',
            'applications' => $applications,
            'facilities' => $facilities,
            'selfFinanced' => $selfFinanced,
        ]);
    }

    /** Self-financing form for a catalogue item (no partner, no mode gate). */
    public function selfFinance(int $catalogueItemId, PaymentModeService $modes)
    {
        $item = ModulePricingCatalogueItem::with('module')->findOrFail($catalogueItemId);

        return view('owner.financing.self-finance', [
            'pageTitle' => 'Self-finance a module',
            'item' => $item,
            'properties' => Property::where('owner_user_id', auth()->id())->withCount('propertyUnits')->get(),
            // Free owners have no rail for the module's monthly infra cost — warn
            // upfront so they upgrade before filling in the form.
            'hasModuleBillingRail' => $modes->hasModuleBillingRail((int) auth()->id()),
        ]);
    }

    /** Record a self-financed module order. */
    public function selfFinanceStore(Request $request, SelfFinancingService $selfFinancing, PaymentModeService $modes)
    {
        $data = $request->validate([
            'catalogue_item_id' => 'required|integer',
            'property_id' => 'required|integer',
            'quantity' => 'required|integer|min:1',
        ]);

        // Smart modules carry a monthly platform & gateway cost. A free plan has
        // no way to bill it, so self-financing (like partner financing) needs a
        // paying mode — subscription (billed on the plan) or transaction (rent).
        if (! $modes->hasModuleBillingRail((int) auth()->id())) {
            return redirect()->route('owner.financing.self-finance', $data['catalogue_item_id'])->with('error', __(
                'Smart modules carry a monthly platform & gateway cost that a free plan can\'t bill. Move to a subscription or transaction plan before self-financing a module.'
            ));
        }

        $item = ModulePricingCatalogueItem::findOrFail($data['catalogue_item_id']);
        $property = Property::where('owner_user_id', auth()->id())
            ->withCount('propertyUnits')->findOrFail($data['property_id']);

        // A property needs units before any module can be deployed on it.
        $maxUnits = (int) $property->property_units_count;
        if ($maxUnits === 0) {
            return redirect()->route('owner.financing.self-finance', $item->id)->with('error', __(
                'Add units to :name before deploying modules — a property needs units first.',
                ['name' => $property->name ?? __('this property')]
            ));
        }
        // A property can't deploy more units than it physically has.
        if ((int) $data['quantity'] > $maxUnits) {
            return redirect()->route('owner.financing.self-finance', $item->id)->with('error', __(
                'You selected :q units but :name has only :max units. Reduce the quantity.',
                ['q' => (int) $data['quantity'], 'name' => $property->name ?? __('this property'), 'max' => $maxUnits]
            ));
        }

        $selfFinancing->createOrder((int) auth()->id(), $property->id, $item, (int) $data['quantity']);

        return redirect()->route('owner.financing.mine')
            ->with('success', __('Self-financing order created. Complete payment to schedule deployment.'));
    }

    /** Toggle accelerated repayment on a facility. */
    public function accelerate(int $facilityId, FinanceFacilityService $facilities)
    {
        $facility = FinanceFacility::where('owner_id', auth()->id())->findOrFail($facilityId);
        try {
            $facilities->setAccelerated($facility, ! $facility->accelerated_repayment);
        } catch (\RuntimeException $e) {
            return back()->with('error', __('Your financier does not offer accelerated repayment on this facility.'));
        }

        return back()->with('success', $facility->fresh()->accelerated_repayment
            ? __('Accelerated repayment enabled.')
            : __('Accelerated repayment disabled.'));
    }

    /** Settle a facility early. */
    public function settleEarly(Request $request, int $facilityId, FinanceFacilityService $facilities)
    {
        $facility = FinanceFacility::where('owner_id', auth()->id())->findOrFail($facilityId);
        $channel = $request->input('channel') === 'manual' ? 'manual' : 'mpesa';

        try {
            $result = $facilities->initiateEarlySettlement($facility, $channel, $request->input('reference'));
        } catch (\RuntimeException $e) {
            return back()->with('error', __('Early settlement is not available on this facility.'));
        }

        if (($result['status'] ?? '') === 'settled') {
            return back()->with('success', __('Facility settled. Total paid: KES ') . number_format((float) $result['total'], 2));
        }

        return back()->with('success', $channel === 'manual'
            ? __('Recorded. Once the financier confirms your payment of KES :amt, the facility is closed.', ['amt' => number_format((float) $result['total'], 2)])
            : __('Check your phone to authorise the M-Pesa payoff of KES :amt.', ['amt' => number_format((float) $result['total'], 2)]));
    }
}
