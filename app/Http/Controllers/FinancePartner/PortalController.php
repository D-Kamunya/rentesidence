<?php

namespace App\Http\Controllers\FinancePartner;

use App\Centresidence\Models\FinanceApplication;
use App\Centresidence\Models\FinanceFacility;
use App\Centresidence\Models\FinancePartner;
use App\Centresidence\Models\FinancePartnerModule;
use App\Centresidence\Models\Module;
use App\Centresidence\Models\UnderwritingRule;
use App\Centresidence\Services\FinanceApplicationService;
use App\Centresidence\Services\FinancePartnerService;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/**
 * Finance Partner portal — manage products and review owner applications.
 */
class PortalController extends Controller
{
    private function partner(): ?FinancePartner
    {
        return FinancePartner::where('user_id', auth()->id())->first();
    }

    public function dashboard()
    {
        $partner = $this->partner();
        if (! $partner) {
            return view('finance-partner.no-profile', ['pageTitle' => 'Finance Partner']);
        }

        $facilities = FinanceFacility::where('finance_partner_id', $partner->id)->where('status', 'active');
        $allFacilities = FinanceFacility::where('finance_partner_id', $partner->id);

        // Portfolio economics — what's lent, what it will return, and what's been
        // collected/remitted so far. Gives the partner a projections-style summary.
        $activePrincipal   = (float) (clone $facilities)->sum('principal_amount');
        $expectedReturn    = (float) (clone $facilities)->sum('total_repayable');   // gross over the life of active loans
        $expectedInterest  = max($expectedReturn - $activePrincipal, 0);
        $totalDisbursed    = (float) (clone $allFacilities)->disbursed()->sum('disbursed_amount');

        $collected = (float) \App\Centresidence\Models\SettlementTransaction::where('beneficiary_type', \App\Centresidence\Models\SettlementTransaction::BENEFICIARY_PARTNER)
            ->where('beneficiary_id', $partner->id)->sum('amount');
        $remitted  = (float) \App\Centresidence\Models\PartnerRemittanceBatch::where('finance_partner_id', $partner->id)
            ->where('status', 'confirmed')->sum('total_amount');

        return view('finance-partner.dashboard', [
            'pageTitle' => 'Dashboard',
            'partner' => $partner,
            'metrics' => [
                'products' => FinancePartnerModule::where('finance_partner_id', $partner->id)->count(),
                'pending' => FinanceApplication::where('finance_partner_id', $partner->id)
                    ->whereIn('status', ['submitted', 'under_review'])->count(),
                'active_facilities' => (clone $facilities)->count(),
                'outstanding' => (clone $facilities)->sum('outstanding_principal'),
            ],
            'portfolio' => [
                'active_principal'   => $activePrincipal,
                'expected_return'    => $expectedReturn,
                'expected_interest'  => $expectedInterest,
                'total_disbursed'    => $totalDisbursed,
                'collected'          => $collected,
                'remitted'           => $remitted,
                'awaiting_remit'     => max($collected - $remitted, 0),
                'completed'          => (int) (clone $allFacilities)->where('status', 'completed')->count(),
            ],
            'recentApplications' => FinanceApplication::with('owner', 'module')
                ->where('finance_partner_id', $partner->id)->latest()->limit(8)->get(),
        ]);
    }

    // ── Products ──────────────────────────────────────────────────────────

    public function products()
    {
        return view('finance-partner.products.index', [
            'pageTitle' => 'My Products',
            'products' => FinancePartnerModule::with('module')
                ->where('finance_partner_id', $this->partner()->id)->latest()->get(),
        ]);
    }

    public function productCreate()
    {
        return view('finance-partner.products.form', [
            'pageTitle' => 'New product',
            'product' => new FinancePartnerModule(['interest_rate_type' => 'reducing_balance', 'repayment_frequency' => 'monthly', 'monthly_settlement_enabled' => true, 'early_repayment_allowed' => true, 'accelerated_repayment_allowed' => true, 'status' => 'active']),
            'modules' => Module::where('is_financeable', true)->where('is_active', true)->get(),
        ]);
    }

    public function productStore(Request $request, FinancePartnerService $partners)
    {
        // A product can't be published until the partner can actually be paid —
        // repayments settle to their payout account, so it must exist first.
        if (! $this->partner()->hasPayoutAccount()) {
            return redirect()->route('finance-partner.payout-account')
                ->with('error', __('Set your payout account before publishing a product — that’s where your repayments are settled.'));
        }

        $data = $this->validateProduct($request);
        $product = $partners->createProduct($this->partner(), $data);
        $this->syncUnderwritingRules($product, $data);

        return redirect()->route('finance-partner.products.index')->with('success', __('Product published.'));
    }

    // ── Notifications ─────────────────────────────────────────────────────

    public function notification()
    {
        \App\Models\Notification::where('user_id', auth()->id())->update(['is_seen' => ACTIVE]);

        return view('finance-partner.notification', [
            'pageTitle'     => 'Notifications',
            'notifications' => getNotification(auth()->id()),
        ]);
    }

    // ── Profile (account details + password) ──────────────────────────────

    public function profile()
    {
        $partner = $this->partner();
        if (! $partner) {
            return view('finance-partner.no-profile', ['pageTitle' => 'Finance Partner']);
        }

        return view('finance-partner.profile', [
            'pageTitle' => 'My profile',
            'partner'   => $partner,
            'user'      => auth()->user(),
        ]);
    }

    public function profileUpdate(Request $request)
    {
        $user = auth()->user();
        $data = $request->validate([
            'first_name'     => 'required|string|max:191',
            'last_name'      => 'required|string|max:191',
            'email'          => 'required|email|max:191|unique:users,email,' . $user->id,
            'contact_number' => 'nullable|numeric|unique:users,contact_number,' . $user->id,
            'company_name'   => 'nullable|string|max:191',
            'trading_name'   => 'nullable|string|max:191',
        ]);

        $user->update([
            'first_name'     => $data['first_name'],
            'last_name'      => $data['last_name'],
            'email'          => $data['email'],
            'contact_number' => $data['contact_number'] ?? $user->contact_number,
        ]);

        if ($partner = $this->partner()) {
            $partner->update([
                'company_name' => $data['company_name'] ?? $partner->company_name,
                'trading_name' => $data['trading_name'] ?? $partner->trading_name,
            ]);
        }

        return redirect()->route('finance-partner.profile')->with('success', __('Profile updated.'));
    }

    public function passwordUpdate(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password'         => 'required|confirmed|min:6|different:current_password',
        ], [
            'password.different' => __('Your new password must be different from your current one.'),
        ]);

        $user = auth()->user();
        if (! Hash::check($request->current_password, $user->password)) {
            return back()->with('error', __('Your current password is incorrect.'));
        }

        $user->update(['password' => Hash::make($request->password)]);

        return redirect()->route('finance-partner.profile')->with('success', __('Password changed.'));
    }

    // ── Payout account (where repayments settle) ──────────────────────────

    public function payoutAccount()
    {
        $partner = $this->partner();
        if (! $partner) {
            return view('finance-partner.no-profile', ['pageTitle' => 'Finance Partner']);
        }

        return view('finance-partner.payout-account', [
            'pageTitle' => 'Payout account',
            'partner'   => $partner,
            'account'   => (array) ($partner->settlement_account_details ?? []),
        ]);
    }

    public function payoutAccountSave(Request $request)
    {
        $partner = $this->partner();
        abort_unless($partner, 403);

        $data = $request->validate([
            'type'    => 'required|in:mpesa_paybill,bank,mpesa_till',
            'paybill' => 'required_unless:type,mpesa_till|nullable|string|max:20',
            'account' => 'nullable|string|max:60',
            'till'    => 'required_if:type,mpesa_till|nullable|string|max:20',
            'label'   => 'nullable|string|max:100',
        ]);

        $details = match ($data['type']) {
            'mpesa_paybill' => ['type' => 'mpesa_paybill', 'paybill' => $data['paybill'], 'account' => $data['account'] ?? '', 'label' => $data['label'] ?? ''],
            'bank'          => ['type' => 'bank', 'paybill' => $data['paybill'], 'account' => $data['account'] ?? '', 'label' => $data['label'] ?? ''],
            'mpesa_till'    => ['type' => 'mpesa_till', 'till' => $data['till'], 'label' => $data['label'] ?? ''],
        };

        $partner->update(['settlement_account_details' => $details]);

        return redirect()->route('finance-partner.payout-account')->with('success', __('Payout account saved.'));
    }

    public function productEdit(int $id)
    {
        return view('finance-partner.products.form', [
            'pageTitle' => 'Edit product',
            'product' => FinancePartnerModule::where('finance_partner_id', $this->partner()->id)->findOrFail($id),
            'modules' => Module::where('is_financeable', true)->where('is_active', true)->get(),
        ]);
    }

    public function productUpdate(Request $request, int $id, FinancePartnerService $partners)
    {
        $product = FinancePartnerModule::where('finance_partner_id', $this->partner()->id)->findOrFail($id);
        $data = $this->validateProduct($request);
        $partners->updateProduct($product, $data);
        $product->underwritingRules()->delete();
        $this->syncUnderwritingRules($product, $data);

        return redirect()->route('finance-partner.products.index')->with('success', __('Product updated.'));
    }

    // ── Applications ──────────────────────────────────────────────────────

    public function applications()
    {
        return view('finance-partner.applications.index', [
            'pageTitle' => 'Applications',
            'applications' => FinanceApplication::with('owner', 'module', 'property', 'facility')
                ->where('finance_partner_id', $this->partner()->id)->latest()->paginate(20),
        ]);
    }

    public function applicationShow(int $id)
    {
        $application = FinanceApplication::with('owner', 'module', 'property', 'partnerModule', 'documents', 'statusHistory')
            ->where('finance_partner_id', $this->partner()->id)->findOrFail($id);

        // Once approved, disbursement happens in the same pipeline — surface it
        // right here instead of a separate trip to Facilities.
        $facility = FinanceFacility::where('finance_application_id', $application->id)
            ->where('finance_partner_id', $this->partner()->id)->latest('id')->first();

        // Where the partner should send the disbursal — so they never have to call
        // us or (worse) contact the owner directly. Centresidence is the installer/
        // payee today; the account + reference let us reconcile the payment.
        $payee = null;
        if ($facility && ! $facility->isDisbursed()) {
            $acctId = getOption('centresidence_rent_mpesa_account_id');
            $payee = [
                'name'      => 'Centresidence',
                'mpesa'     => $acctId ? \App\Models\MpesaAccount::find($acctId) : null,
                'bank'      => getOption('centresidence_disbursement_bank_details'),
                'reference' => $facility->facility_number,
                'amount'    => $facility->disbursed_amount,
            ];
        }

        return view('finance-partner.applications.show', [
            'pageTitle' => 'Application ' . ($application->application_number ?? ('#' . $application->id)),
            'application' => $application,
            'facility' => $facility,
            'payee' => $payee,
        ]);
    }

    public function approve(Request $request, int $id, FinanceApplicationService $applications)
    {
        $application = FinanceApplication::where('finance_partner_id', $this->partner()->id)->findOrFail($id);
        $amount = $request->validate(['approved_amount' => 'required|numeric|min:1'])['approved_amount'];

        try {
            if ($application->status === FinanceApplication::STATUS_SUBMITTED) {
                $applications->moveToReview($application, (int) auth()->id());
            }
            $applications->approve($application, (string) $amount, (int) auth()->id());
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('finance-partner.applications.show', $id)
            ->with('success', __('Application approved — facility created. Release the funds below to disburse.'))
            ->with('scroll_to_disburse', true);
    }

    public function reject(Request $request, int $id, FinanceApplicationService $applications)
    {
        $application = FinanceApplication::where('finance_partner_id', $this->partner()->id)->findOrFail($id);
        $reason = $request->validate(['rejection_reason' => 'required|string|max:1000'])['rejection_reason'];

        try {
            if ($application->status === FinanceApplication::STATUS_SUBMITTED) {
                $applications->moveToReview($application, (int) auth()->id());
            }
            $applications->reject($application, $reason, (int) auth()->id());
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('finance-partner.applications.show', $id)->with('success', __('Application rejected.'));
    }

    public function facilities()
    {
        return view('finance-partner.facilities', [
            'pageTitle' => 'Facilities',
            'facilities' => FinanceFacility::with('owner', 'property')
                ->where('finance_partner_id', $this->partner()->id)->latest()->paginate(20),
        ]);
    }

    /**
     * Per-facility servicing detail — how this loan is amortising: the actual
     * principal drawdown, the contract schedule, every rent collection that
     * serviced it, and which remittance batches carried it back to the partner.
     */
    public function facilityOverview(int $id)
    {
        $facility = FinanceFacility::with(['owner', 'property', 'schedules', 'application.partnerModule'])
            ->where('finance_partner_id', $this->partner()->id)->findOrFail($id);

        // Collections that serviced THIS facility, newest first, with the rent order.
        $collections = \App\Centresidence\Models\SettlementTransaction::where('finance_facility_id', $facility->id)
            ->where('beneficiary_type', \App\Centresidence\Models\SettlementTransaction::BENEFICIARY_PARTNER)
            ->orderByDesc('id')->get();

        // Remittance batch items that carried this facility's repayments.
        $remittanceItems = \App\Centresidence\Models\PartnerRemittanceBatchItem::with('batch')
            ->where('facility_id', $facility->id)
            ->get()
            ->sortByDesc(fn ($it) => optional($it->batch)->id)
            ->values();

        // Derived servicing figures (actual, not just the planned schedule).
        $principal      = (float) $facility->principal_amount;
        $outstanding    = (float) $facility->outstanding_principal;
        $principalRepaid = max($principal - $outstanding, 0);
        $pctRepaid      = $principal > 0 ? round($principalRepaid / $principal * 100, 1) : 0;
        $interestCollected = (float) $collections->where('transaction_type', 'rent_deduction_interest')->sum('amount');
        $penaltyCollected  = (float) $collections->whereIn('transaction_type', ['rent_deduction_penalty'])->sum('amount');
        $totalCollected    = (float) $collections->sum('amount');
        $totalRemitted     = (float) $remittanceItems->sum('amount');
        $costOfFinance     = max((float) $facility->total_repayable - $principal, 0);

        // Next payment = earliest not-fully-paid period (schedules are ordered by
        // period_number), so a partially-paid current period is shown, not skipped.
        $nextDue = $facility->schedules->first(fn ($s) => $s->status !== 'paid');
        $arrears = $facility->schedules->where('status', 'overdue');

        // Centresidence fees on this facility — origination is per-facility (booked at
        // creation, collected only after disbursement); servicing is netted per remittance.
        $fees          = app(\App\Centresidence\Services\PartnerFeeService::class);
        $servicingRate = $fees->servicingRate($this->partner());
        $originationFee       = (float) $facility->origination_fee_amount;
        $originationCollected = (float) $facility->origination_fee_collected;
        $originationOutstanding = $facility->originationOutstanding();

        return view('finance-partner.facility-overview', [
            'pageTitle'         => 'Facility ' . ($facility->facility_number ?? ('#' . $facility->id)),
            'facility'          => $facility,
            'collections'       => $collections,
            'remittanceItems'   => $remittanceItems,
            'principalRepaid'   => $principalRepaid,
            'pctRepaid'         => $pctRepaid,
            'interestCollected' => $interestCollected,
            'penaltyCollected'  => $penaltyCollected,
            'totalCollected'    => $totalCollected,
            'totalRemitted'     => $totalRemitted,
            'costOfFinance'     => $costOfFinance,
            'nextDue'           => $nextDue,
            'arrears'           => $arrears,
            'servicingRate'          => $servicingRate,
            'originationFee'         => $originationFee,
            'originationCollected'   => $originationCollected,
            'originationOutstanding' => $originationOutstanding,
        ]);
    }

    /**
     * The partner records that they've released the facility funds (M-Pesa or bank).
     * It moves to pending_confirmation; the payee confirms receipt to release it.
     */
    public function recordDisbursement(Request $request, int $id, \App\Centresidence\Services\FinanceFacilityService $facilities)
    {
        $facility = FinanceFacility::where('finance_partner_id', $this->partner()->id)->findOrFail($id);
        if ($facility->isDisbursed()) {
            return back()->with('error', __('This facility is already disbursed.'));
        }
        $data = $request->validate([
            'disbursement_channel'   => 'required|in:mpesa,bank',
            'disbursement_reference' => 'nullable|string|max:191',
        ]);
        $facilities->recordDisbursement($facility, $data['disbursement_channel'], $data['disbursement_reference'] ?? null);

        return back()->with('success', __('Disbursement recorded. The payee will confirm receipt to release the facility for repayment.'));
    }

    /** The partner confirms they received the owner's early-settlement payoff → closes the facility. */
    public function confirmSettlement(int $id, \App\Centresidence\Services\FinanceFacilityService $facilities)
    {
        $facility = FinanceFacility::where('finance_partner_id', $this->partner()->id)->findOrFail($id);
        if ($facility->early_settlement_status !== FinanceFacility::EARLY_PENDING) {
            return back()->with('error', __('Nothing to confirm — this facility has no pending settlement.'));
        }
        $facilities->confirmEarlySettlement($facility);

        return back()->with('success', __('Settlement confirmed — the facility is now closed.'));
    }

    /** Remittances Centresidence has paid this partner (their collected repayments). */
    public function remittances()
    {
        return view('finance-partner.remittances', [
            'pageTitle' => 'Remittances',
            'batches' => \App\Centresidence\Models\PartnerRemittanceBatch::where('finance_partner_id', $this->partner()->id)
                ->with(['items.facility', 'items.settlementTransaction'])
                ->latest()->paginate(20),
        ]);
    }

    /** The partner confirms they received a bank remittance we marked sent. */
    public function confirmRemittance(int $id, \App\Centresidence\Services\PartnerRemittanceService $remittances)
    {
        $batch = \App\Centresidence\Models\PartnerRemittanceBatch::where('finance_partner_id', $this->partner()->id)->findOrFail($id);
        if ($batch->status !== \App\Centresidence\Models\PartnerRemittanceBatch::STATUS_SENT) {
            return back()->with('error', __('Nothing to confirm — this batch isn’t awaiting confirmation.'));
        }
        $remittances->confirmBatch($batch);

        return back()->with('success', __('Receipt confirmed. Thank you.'));
    }

    // ── Learn: module education (financier lens) ──────────────────────────

    public function learnModules()
    {
        $partner = $this->partner();
        $financedModuleIds = $partner
            ? FinancePartnerModule::where('finance_partner_id', $partner->id)->pluck('module_id')->all()
            : [];

        $market = $this->marketStats();

        $modules = Module::where('is_active', true)->where('is_financeable', true)
            ->orderBy('display_order')->get()
            ->map(function (Module $m) use ($financedModuleIds, $market) {
                $m->you_finance = in_array($m->id, $financedModuleIds, true);
                $m->stats = $market['stats'][$m->id] ?? [];
                $m->leaders = $market['leaders'][$m->id] ?? [];
                return $m;
            });

        return view('finance-partner.learn.modules', [
            'pageTitle' => 'Modules',
            'modules' => $modules,
        ]);
    }

    public function learnModule(int $id)
    {
        $module = Module::where('is_active', true)->findOrFail($id);
        $partner = $this->partner();
        $market = $this->marketStats();

        return view('finance-partner.learn.module', [
            'pageTitle' => $module->name,
            'module' => $module,
            'catalogue' => \App\Centresidence\Models\ModulePricingCatalogueItem::where('module_id', $module->id)->where('is_active', true)->first(),
            'youFinance' => $partner
                ? FinancePartnerModule::where('finance_partner_id', $partner->id)->where('module_id', $module->id)->exists()
                : false,
            'stats' => $market['stats'][$module->id] ?? [],
            'leaders' => $market['leaders'][$module->id] ?? [],
        ]);
    }

    /**
     * Per-module market intelligence for finance partners — demand, uptake,
     * approval, repayment health and indicative yield, computed honestly from
     * real applications/facilities/products (null where there is no data yet,
     * so empty modules read "gathering data" rather than a misleading 0%).
     *
     * @return array{stats: array<int,array>, leaders: array<int,array>, total_applications: int}
     */
    private function marketStats(): array
    {
        $apps = FinanceApplication::selectRaw(
            "module_id, count(*) total, "
            . "sum(case when status in ('approved','disbursed') then 1 else 0 end) funded, "
            . "avg(requested_amount) avg_ticket"
        )->groupBy('module_id')->get()->keyBy('module_id');

        $facs = FinanceFacility::selectRaw(
            "module_id, count(*) total, "
            . "sum(case when status='active' then 1 else 0 end) active, "
            . "sum(case when status='completed' then 1 else 0 end) completed, "
            . "sum(case when status='defaulted' then 1 else 0 end) defaulted, "
            . "sum(case when status='active' then outstanding_principal else 0 end) outstanding"
        )->groupBy('module_id')->get()->keyBy('module_id');

        $rates = FinancePartnerModule::selectRaw('module_id, avg(interest_rate) avg_rate')
            ->where('status', 'active')->groupBy('module_id')->get()->keyBy('module_id');

        $totalApps = (int) $apps->sum('total');
        $stats = [];

        foreach (Module::pluck('id') as $id) {
            $a = $apps->get($id);
            $f = $facs->get($id);
            $r = $rates->get($id);
            $aTotal = (int) ($a->total ?? 0);
            $fTotal = (int) ($f->total ?? 0);
            $defaulted = (int) ($f->defaulted ?? 0);

            $stats[$id] = [
                'applications'      => $aTotal,
                'uptake_pct'        => $totalApps > 0 ? (int) round($aTotal / $totalApps * 100) : null,
                'approval_rate'     => $aTotal > 0 ? (int) round((int) ($a->funded ?? 0) / $aTotal * 100) : null,
                'facilities_active' => (int) ($f->active ?? 0),
                'facilities_total'  => $fTotal,
                'outstanding'       => (float) ($f->outstanding ?? 0),
                'repayment_health'  => $fTotal > 0 ? (int) round(($fTotal - $defaulted) / $fTotal * 100) : null,
                'avg_interest'      => ($r && $r->avg_rate !== null) ? (float) $r->avg_rate : null,
                'avg_ticket'        => ($a && $a->avg_ticket !== null) ? (float) $a->avg_ticket : null,
            ];
        }

        // Leader badges — only assigned when there is data to rank.
        $s = collect($stats);
        $leaders = [];
        $rank = [
            'demand'   => [$s->filter(fn ($x) => $x['applications'] > 0)->sortByDesc('applications')->keys()->first(), 'Highest demand', 'ri-fire-line'],
            'repay'    => [$s->filter(fn ($x) => $x['facilities_total'] > 0)->sortByDesc('repayment_health')->keys()->first(), 'Best repayment', 'ri-shield-check-line'],
            'financed' => [$s->filter(fn ($x) => $x['facilities_active'] > 0)->sortByDesc('facilities_active')->keys()->first(), 'Most financed', 'ri-line-chart-line'],
            'yield'    => [$s->filter(fn ($x) => $x['avg_interest'] !== null)->sortByDesc('avg_interest')->keys()->first(), 'Best yield', 'ri-percent-line'],
        ];
        foreach ($rank as [$mid, $label, $icon]) {
            if ($mid !== null) {
                $leaders[$mid][] = ['label' => $label, 'icon' => $icon];
            }
        }

        return ['stats' => $stats, 'leaders' => $leaders, 'total_applications' => $totalApps];
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    private function validateProduct(Request $request): array
    {
        return $request->validate([
            'module_id' => 'required|integer',
            'product_name' => 'required|string|max:191',
            'interest_rate' => 'required|numeric|min:0',
            'interest_rate_type' => 'required|in:fixed,reducing_balance,flat',
            'interest_calculation_method' => 'nullable|in:monthly_rest,daily_rest,flat_upfront',
            'min_amount' => 'required|numeric|min:0',
            'max_amount' => 'required|numeric|min:0',
            'min_repayment_months' => 'required|integer|min:1',
            'max_repayment_months' => 'required|integer|min:1',
            'repayment_frequency' => 'required|in:daily,weekly,biweekly,monthly',
            'max_rent_deduction_percentage' => 'required|numeric|min:0|max:100',
            'required_cashflow_months' => 'nullable|integer|min:0',
            'min_occupancy_rate' => 'nullable|numeric|min:0|max:100',
            'grace_period_days' => 'nullable|integer|min:0',
            'default_threshold_days' => 'nullable|integer|min:0',
            'early_repayment_allowed' => 'nullable|boolean',
            'early_repayment_penalty_percentage' => 'nullable|numeric|min:0',
            'accelerated_repayment_allowed' => 'nullable|boolean',
            'daily_settlement_enabled' => 'nullable|boolean',
            'monthly_settlement_enabled' => 'nullable|boolean',
            'settlement_day' => 'nullable|integer|min:1|max:28',
            'status' => 'required|in:active,inactive,suspended',
        ]);
    }

    /** Turn the product's stated requirements into hard underwriting rules. */
    private function syncUnderwritingRules(FinancePartnerModule $product, array $data): void
    {
        if (! empty($data['min_occupancy_rate']) && $data['min_occupancy_rate'] > 0) {
            $product->underwritingRules()->create([
                'rule_name' => 'min_occupancy', 'rule_type' => 'threshold', 'parameter' => 'occupancy_rate',
                'operator' => UnderwritingRule::OP_GTE, 'value' => (string) $data['min_occupancy_rate'],
                'is_hard_rule' => true, 'error_message' => __('Property occupancy is below the required minimum.'),
            ]);
        }
        if (! empty($data['required_cashflow_months']) && $data['required_cashflow_months'] > 0) {
            $product->underwritingRules()->create([
                'rule_name' => 'cashflow_history', 'rule_type' => 'threshold', 'parameter' => 'cashflow_history_months',
                'operator' => UnderwritingRule::OP_GTE, 'value' => (string) $data['required_cashflow_months'],
                'is_hard_rule' => true, 'error_message' => __('Insufficient cashflow history for this product.'),
            ]);
        }
    }
}
