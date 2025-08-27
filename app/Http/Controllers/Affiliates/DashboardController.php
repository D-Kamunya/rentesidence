<?php

namespace App\Http\Controllers\Affiliates;
use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use App\Services\AffiliateCommissionService;
use App\Models\User;
use App\Models\AffiliateCommission;
use App\Models\AffiliateCommissionPayment;
use App\Models\AffiliateWithdrawal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DashboardController extends Controller
{

    protected $svc;

    public function __construct(AffiliateCommissionService $svc)
    {
        $this->svc = $svc;
    }

    public function dashboard(Request $request)
    {
        $user = auth()->user();
        $affiliateId = $user->affiliate->id; // assume relation user->affiliate

        $month = $request->get('month', Carbon::now()->format('n'));
        $year = $request->get('year', Carbon::now()->format('Y'));

        $currentMonthPayment = AffiliateCommissionPayment::where('affiliate_id', $affiliateId)
            ->where('period_month', $month)
            ->where('period_year', $year)
            ->latest()
            ->first();
        // current month total (latest payment row)
        $currentMonthTotal = $this->svc->getLatestPeriodPayout($affiliateId, (int)$month, (int)$year);

        // lifetime earnings minus withdrawals
        $availableBalance = $this->svc->getAvailableBalance($affiliateId);

        // lifetime gross (sum latest per month, before withdrawals)
        // reuse service internals (slightly modified)
        $lifeTimeGross = $this->svc->getLifeTimeGrossCommissions($affiliateId);
        
        // Total withdrawals approved
        $totalWithdrawals = AffiliateWithdrawal::where('affiliate_id', $affiliateId)
            ->where('status', AFFILIATE_WITHDRAWAL_APPROVED)
            ->sum('amount');

        $totalReferrals = AffiliateCommission::where('affiliate_id', $affiliateId)
            ->distinct('owner_id')
            ->count('owner_id');

        $summary = [
            'current_monthly_earning' => $currentMonthTotal,
            'new_clients' => $currentMonthPayment?->total_new_clients ?? 0,
            'recurring_clients' => $currentMonthPayment?->total_recurring_clients ?? 0,
            'available_balance' => $availableBalance,
            'total_commissions' => $lifeTimeGross,
            'total_payouts' => $totalWithdrawals,
            'total_referrals' => $totalReferrals
        ];

         $commissionTrends = AffiliateCommissionPayment::select(
                'period_month',
                'period_year',
                'total_commission_payout'
            )
            ->where('affiliate_id', $affiliateId)
            ->orderBy('period_year', 'asc')
            ->orderBy('period_month', 'asc')
            ->get()
            ->groupBy(fn ($row) => Carbon::createFromDate($row->period_year, $row->period_month)->format('M'))
            ->map(fn ($rows, $month) => [
                'month' => $month,
                'amount' => $rows->last()->total_commission_payout
            ])
            ->take(6)
            ->values()
            ->toArray();

        // Latest 10 commissions for current month
        $recentCommissions = AffiliateCommission::with(['owner', 'subscription.package'])
            ->where('affiliate_id', $affiliateId)
            ->where('period_month', $month)
            ->where('period_year', $year)
            ->latest()
            ->take(10)
            ->get()
            ->map(function ($commission) {
                return [
                    'date' => $commission->created_at->format('Y-m-d'),
                    'owner' => $commission->owner->name ?? 'N/A',
                    'package' => $commission->subscription->package->name ?? 'N/A',
                    'type' => ucfirst($commission->type),
                    'amount' => $commission->subscription_amount,
                ];
            })
            ->toArray();

        // Pass the data to the view
        return view('affiliates.dashboard', compact('summary', 'commissionTrends', 'recentCommissions'));
    }
}
