<?php

namespace App\Http\Controllers\Owner;

use App\Centresidence\Exceptions\UnderwritingFailedException;
use App\Centresidence\Models\FieldStudyRequest;
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
    public function module(int $moduleId, Request $request, PaymentModeService $modes, FinancePartnerService $partners)
    {
        $module = Module::where('is_active', true)->findOrFail($moduleId);

        return view('owner.financing.module', [
            'pageTitle' => $module->name,
            'module' => $module,
            'catalogue' => ModulePricingCatalogueItem::where('module_id', $module->id)->where('is_active', true)->first(),
            'products' => $this->migrated() ? $partners->marketplaceProductsForModule($module->id) : collect(),
            'isTransactionMode' => $modes->isTransactionMode((int) auth()->id()),
            // An accepted survey quote in play — reveals the financiers on a quote-based module and
            // carries the amount into the application.
            'acceptedQuote' => $this->acceptedQuote($request->integer('fsr') ?: null, $module->id),
        ]);
    }

    /** Application form for a chosen partner product (or a mode-switch prompt). */
    public function apply(int $partnerModuleId, Request $request, PaymentModeService $modes, InfrastructureCostEngine $infra)
    {
        $product = FinancePartnerModule::with('partner', 'module')->findOrFail($partnerModuleId);
        $catalogue = ModulePricingCatalogueItem::where('module_id', $product->module_id)->where('is_active', true)->first();

        // No pre-apply wall — owners apply on their CURRENT plan. If they're not already
        // on transaction billing, the form shows an origin-aware note that their billing
        // switches to the Transaction plan ONLY if the facility is approved & disbursed
        // (the switch is deferred to disbursement).
        $currentMode = $modes->currentMode((int) auth()->id());

        // Quote-based application (accepted site-survey quote): a fixed all-in amount, not a
        // catalogue × qty calculation — render the simplified quote apply form.
        if ($fsrId = $request->integer('fsr')) {
            if ($fsr = $this->acceptedQuote($fsrId, $product->module_id)) {
                $property = Property::where('owner_user_id', auth()->id())
                    ->withSum('propertyUnits', 'general_rent')->find($fsr->property_id);

                return view('owner.financing.apply-quote', [
                    'pageTitle'      => __('Apply for financing'),
                    'product'        => $product,
                    'fsr'            => $fsr,
                    'property'       => $property,
                    'currentMode'    => $currentMode,
                    // Affordability context so the form can show the live monthly + rent-share, and
                    // the owner can consent to a higher cap — mirrors the normal apply page.
                    'propertyRent'   => (float) ($property->property_units_sum_general_rent ?? 0),
                    'existingInfra'  => $property ? $infra->projectedMonthlyForProperty($property)['cost']->toFloat() : 0.0,
                    'infraPerDevice' => (float) ($product->module?->activeCostComponents->where('cost_model', 'per_active_device')->sum('rate') ?? 0),
                    'infraFlat'      => (float) ($product->module?->activeCostComponents->where('cost_model', 'flat_monthly')->sum('rate') ?? 0),
                    'rentCapPct'     => (int) config('centresidence.billing.max_total_rent_deduction_percentage', 60),
                    'consentMaxPct'  => (int) config('centresidence.billing.max_consented_rent_deduction_percentage', 90),
                ]);
            }
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
            'currentMode' => $currentMode,
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

    /** Submit a financing application (create draft + soft underwriting). */
    public function store(Request $request, FinanceApplicationService $applications, CashflowService $cashflow, InfrastructureCostEngine $infra)
    {
        // Quote-based application (from an accepted site-survey quote) — a fixed all-in amount, not
        // a catalogue × qty calculation. Handled separately.
        if ($request->integer('field_study_request_id')) {
            return $this->storeFromQuote($request, $applications, $cashflow, $infra);
        }

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
        } catch (UnderwritingFailedException $e) {
            $reasons = collect($e->hardFailures)->pluck('message')->filter()->implode(' ');

            return redirect()->route('owner.financing.apply', $product->id)
                ->with('error', __('Your property did not meet this product\'s requirements: ') . $reasons);
        }

        return redirect()->route('owner.financing.mine')
            ->with('success', __('Application submitted. The finance partner will review it.'));
    }

    /** The owner's applications + active facilities + self-financed orders. */
    /**
     * Field-study workflow — the owner's site-survey requests for custom installs (e.g. reticulated
     * gas) that can't be priced from the standard catalogue, plus the form to request a new one.
     */
    public function surveys()
    {
        $ownerId = (int) auth()->id();

        return view('owner.financing.surveys', [
            'pageTitle'  => __('Site surveys'),
            'requests'   => FieldStudyRequest::with(['module', 'property'])->where('owner_id', $ownerId)->latest()->get(),
            'modules'    => Module::where('is_active', true)->where('requires_field_study', true)->orderBy('name')->get(),
            'properties' => Property::where('owner_user_id', $ownerId)->withCount('propertyUnits')->orderBy('name')->get(),
            // Sidebar: open Financing, highlight Site surveys.
            'navFinancingMMShowClass'         => 'mm-show',
            'subNavSiteSurveysMMActiveClass'  => 'mm-active',
            'subNavSiteSurveysActiveClass'    => 'active',
        ]);
    }

    public function requestSurvey(Request $request)
    {
        $data = $request->validate([
            'module_id'   => 'required|integer',
            'property_id' => 'required|integer',
            'units'       => 'nullable|integer|min:1',
            'note'        => 'nullable|string|max:1000',
        ]);
        $ownerId = (int) auth()->id();

        // Guards: a real field-study module + one of THIS owner's properties (IDOR).
        $module   = Module::where('is_active', true)->where('requires_field_study', true)->find($data['module_id']);
        $property = Property::where('owner_user_id', $ownerId)->withCount('propertyUnits')->find($data['property_id']);
        if (! $module || ! $property) {
            return back()->with('error', __('Please choose a valid module and one of your properties.'));
        }

        // Cap the requested units to what the property actually has (same guard as the finance flow).
        $maxUnits = (int) $property->property_units_count;
        $units    = $data['units'] ?? null;
        if ($units !== null && $maxUnits > 0 && $units > $maxUnits) {
            return back()->with('error', __(':name has :max units — you cannot request more than that.', ['name' => $property->name ?? __('this property'), 'max' => $maxUnits]))->withInput();
        }

        FieldStudyRequest::create([
            'owner_id'    => $ownerId,
            'property_id' => $property->id,
            'units'       => $units,
            'module_id'   => $module->id,
            'status'      => FieldStudyRequest::STATUS_REQUESTED,
            'note'        => $data['note'] ?? null,
        ]);

        try {
            $admin = \App\Models\User::where('role', USER_ROLE_ADMIN)->first();
            if ($admin) {
                addNotification(
                    __('New site-survey request'),
                    __('An owner requested a site survey for :module on :property.', ['module' => $module->name, 'property' => $property->name]),
                    route('admin.centresidence.field-studies'),
                    null, $admin->id, $ownerId
                );
            }
        } catch (\Throwable $e) {
            // notification is best-effort
        }

        return redirect()->route('owner.financing.surveys')
            ->with('success', __('Your site-survey request has been submitted. Our team will assess the property and send you a quotation.'));
    }

    /** Owner accepts a quote → the module's financier list, carrying the quote so they can apply for it. */
    public function proceedSurvey(int $id)
    {
        $request = FieldStudyRequest::where('owner_id', (int) auth()->id())->findOrFail($id);
        if (! $request->isQuoted()) {
            return back()->with('error', __('This request has not been quoted yet.'));
        }

        // Reveal the financiers for this module, carrying the accepted quote (fsr) so the chosen
        // financier's application is pre-filled with the quoted amount. Status flips to 'applied'
        // only when the finance application is actually submitted (store()).
        return redirect()->route('owner.financing.module', ['moduleId' => $request->module_id, 'fsr' => $request->id])
            ->with('success', __('Quote of KES :amt accepted — choose a financier below to apply for it.', ['amt' => number_format((float) $request->quoted_amount, 2)]));
    }

    /** The accepted, owner-scoped field-study request referenced by ?fsr= (or null). */
    private function acceptedQuote(?int $fsrId, int $moduleId): ?FieldStudyRequest
    {
        if (! $fsrId) {
            return null;
        }
        $fsr = FieldStudyRequest::where('owner_id', (int) auth()->id())->where('module_id', $moduleId)->find($fsrId);

        return ($fsr && $fsr->isQuoted()) ? $fsr : null;
    }

    /** Create a finance application from an accepted site-survey QUOTE (fixed amount, no catalogue). */
    private function storeFromQuote(Request $request, FinanceApplicationService $applications, CashflowService $cashflow, InfrastructureCostEngine $infra)
    {
        $data = $request->validate([
            'field_study_request_id'    => 'required|integer',
            'finance_partner_module_id' => 'required|integer',
            'repayment_months'          => 'required|integer|min:1',
            'owner_contribution'        => 'nullable|numeric|min:0',
            'consented_deduction_cap'   => 'nullable|integer|min:60|max:' . (int) config('centresidence.billing.max_consented_rent_deduction_percentage', 90),
        ]);

        $fsr = FieldStudyRequest::where('owner_id', (int) auth()->id())->find($data['field_study_request_id']);
        if (! $fsr || ! $fsr->isQuoted()) {
            return redirect()->route('owner.financing.surveys')->with('error', __('That quote is no longer available.'));
        }
        $product = FinancePartnerModule::findOrFail($data['finance_partner_module_id']);
        if ((int) $product->module_id !== (int) $fsr->module_id) {
            return back()->with('error', __('Please choose a financier offered for this installation.'));
        }

        // Affordability context so the SAME rent-cap feasibility gate as the normal flow runs.
        $property      = Property::where('owner_user_id', (int) auth()->id())->withSum('propertyUnits', 'general_rent')->find($fsr->property_id);
        $propertyRent  = (float) ($property->property_units_sum_general_rent ?? 0);
        $existingInfra = $property ? $infra->projectedMonthlyForProperty($property)['cost']->toFloat() : 0.0;

        // financed = quoted − contribution; clamp to the financier's min/max (mirrors the normal flow).
        $quoted       = (float) $fsr->quoted_amount;
        $contribution = min(max((float) ($data['owner_contribution'] ?? 0), 0), $quoted);
        $financed     = $quoted - $contribution;
        $max = (float) $product->max_amount;
        $min = (float) $product->min_amount;
        if ($financed <= 0.0) {
            return back()->with('error', __('Your contribution covers the whole quote — no financing is needed.'));
        }
        if ($max > 0 && $financed > $max + 0.01) {
            return back()->with('error', __('You would finance KES :f, above this financier\'s ceiling of KES :m. Add a larger down-payment or pick another financier.', ['f' => number_format($financed, 2), 'm' => number_format($max, 2)]));
        }
        if ($min > 0 && $financed < $min - 0.01) {
            return back()->with('error', __('You would finance only KES :f, below this financier\'s minimum of KES :m. Lower your down-payment.', ['f' => number_format($financed, 2), 'm' => number_format($min, 2)]));
        }

        try {
            $application = $applications->createDraft([
                'owner_id'                  => (int) auth()->id(),
                'property_id'               => $fsr->property_id,
                'module_id'                 => $fsr->module_id,
                'finance_partner_id'        => $product->finance_partner_id,
                'finance_partner_module_id' => $product->id,
                'catalogue_item_id'         => null,
                'quantity'                  => $fsr->units ?: 1,
                'quoted_amount'             => $quoted, // ← the override: quote is the all-in project cost
                'owner_contribution'        => $contribution,
                'repayment_months'          => (int) $data['repayment_months'],
                'consented_deduction_cap'   => ! empty($data['consented_deduction_cap']) && $data['consented_deduction_cap'] > 60
                    ? (int) $data['consented_deduction_cap'] : null,
                // Feed the rent-cap feasibility gate (skipping this would let a quote facility be
                // sized beyond what the property's rent can service).
                'property_rent'             => $propertyRent,
                'existing_infra'            => $existingInfra,
            ]);
            $applications->submit($application, $cashflow->underwritingContext($application), (int) auth()->id());
        } catch (\App\Centresidence\Exceptions\FacilityInfeasibleException $e) {
            return back()->with('error', __('This facility would push rent deductions past the allowed cap. Add a larger down-payment or a longer term.'))->withInput();
        } catch (\Throwable $e) {
            return back()->with('error', __('We could not start your application right now. Please try again.'))->withInput();
        }

        $fsr->update(['status' => FieldStudyRequest::STATUS_APPLIED]);

        return redirect()->route('owner.financing.mine')
            ->with('success', __('Your financing application for the quoted amount has been submitted.'));
    }

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
