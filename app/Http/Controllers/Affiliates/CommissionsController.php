<?php

namespace App\Http\Controllers\Affiliates;

use App\Http\Controllers\Controller;
use App\Models\AffiliateCommission;
use App\Models\AffiliateCommissionPayment;
use App\Models\AffiliateWithdrawal;
use App\Services\AffiliateCommissionService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CommissionsController extends Controller
{
    protected AffiliateCommissionService $svc;

    public function __construct(AffiliateCommissionService $svc)
    {
        $this->svc = $svc;
    }

    public function index()
    {
        $affiliate   = auth()->user()->affiliate;
        $affiliateId = $affiliate->id;

        $availableBalance = $this->svc->getAvailableBalance($affiliateId);
        $currentMonthTotal = $this->svc->getLatestPeriodPayout(
            $affiliateId,
            (int) now()->format('n'),
            (int) now()->format('Y')
        );
        $lifeTimeGross   = $this->svc->getLifeTimeGrossCommissions($affiliateId);
        $totalWithdrawals = AffiliateWithdrawal::where('affiliate_id', $affiliateId)
            ->where('status', AFFILIATE_WITHDRAWAL_APPROVED)
            ->sum('amount');

        // Latest summary row per month — deduplicated by max id
        $monthlySummaries = AffiliateCommissionPayment::where('affiliate_id', $affiliateId)
            ->whereIn('id', function ($q) use ($affiliateId) {
                $q->selectRaw('MAX(id)')
                  ->from('affiliate_commission_payments')
                  ->where('affiliate_id', $affiliateId)
                  ->groupBy('period_year', 'period_month');
            })
            ->orderByDesc('period_year')
            ->orderByDesc('period_month')
            ->paginate(20);

        // ── Withdrawals for the Withdrawal History tab ──
        $withdrawals = AffiliateWithdrawal::where('affiliate_id', $affiliateId)
            ->latest()
            ->paginate(15, ['*'], 'withdrawals_page');

        return view('affiliate.commissions.index', compact(
            'availableBalance',
            'currentMonthTotal',
            'lifeTimeGross',
            'totalWithdrawals',
            'monthlySummaries',
            'withdrawals'
        ));
    }

    public function detail(Request $request)
    {
        $affiliate   = auth()->user()->affiliate;
        $affiliateId = $affiliate->id;
        $month       = (int) $request->get('month', now()->format('n'));
        $year        = (int) $request->get('year', now()->format('Y'));

        $period = Carbon::createFromDate($year, $month, 1)->format('F Y');

        $rows = AffiliateCommission::where('affiliate_id', $affiliateId)
            ->where('period_month', $month)
            ->where('period_year', $year)
            ->with(['owner.user'])      // 👈 Eager load owner.user relationship
            ->get();

        $subscription = $rows->where('source', AFFILIATE_COMMISSION_SOURCE_SUBSCRIPTION)
            ->map(fn($r) => [
                'date'                => $r->created_at->format('d M Y'),
                'owner'               => $r->owner?->user?->name ?? $r->owner?->name ?? '—',   // 👈 Get name from User through Owner
                'type'                => $r->type == NEW_CLIENT ? 'New Client' : 'Recurring',
                'subscription_amount' => $r->subscription_amount,
                'rate'                => $r->commission_rate,
                'commission_amount'   => $r->commission_amount,
            ])->values()->toArray();

        $rent = $rows->where('source', AFFILIATE_COMMISSION_SOURCE_RENT)
            ->map(fn($r) => [
                'date'             => $r->created_at->format('d M Y'),
                'owner'            => $r->owner?->user?->name ?? $r->owner?->name ?? '—',   // 👈 Get name from User through Owner
                'rate'             => $r->commission_rate,
                'commission_amount'=> $r->commission_amount,
            ])->values()->toArray();

        $marketplace = $rows->where('source', AFFILIATE_COMMISSION_SOURCE_MARKETPLACE)
            ->map(fn($r) => [
                'date'             => $r->created_at->format('d M Y'),
                'owner'            => $r->owner?->user?->name ?? $r->owner?->name ?? '—',   // 👈 Get name from User through Owner
                'rate'             => $r->commission_rate,
                'commission_amount'=> $r->commission_amount,
            ])->values()->toArray();

        return response()->json([
            'success' => true,
            'data'    => compact('period', 'subscription', 'rent', 'marketplace'),
        ]);
    }
}