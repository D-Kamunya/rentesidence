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
use Illuminate\Http\Request;

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
            'product' => new FinancePartnerModule(['interest_rate_type' => 'reducing_balance', 'repayment_frequency' => 'monthly', 'monthly_settlement_enabled' => true, 'early_repayment_allowed' => true, 'status' => 'active']),
            'modules' => Module::where('is_financeable', true)->where('is_active', true)->get(),
        ]);
    }

    public function productStore(Request $request, FinancePartnerService $partners)
    {
        $data = $this->validateProduct($request);
        $product = $partners->createProduct($this->partner(), $data);
        $this->syncUnderwritingRules($product, $data);

        return redirect()->route('finance-partner.products.index')->with('success', __('Product published.'));
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
            'applications' => FinanceApplication::with('owner', 'module', 'property')
                ->where('finance_partner_id', $this->partner()->id)->latest()->paginate(20),
        ]);
    }

    public function applicationShow(int $id)
    {
        $application = FinanceApplication::with('owner', 'module', 'property', 'partnerModule', 'documents', 'statusHistory')
            ->where('finance_partner_id', $this->partner()->id)->findOrFail($id);

        return view('finance-partner.applications.show', [
            'pageTitle' => 'Application ' . ($application->application_number ?? ('#' . $application->id)),
            'application' => $application,
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
            ->with('success', __('Application approved — facility created.'));
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
