<?php

namespace App\Http\Controllers\Affiliates;

use App\Http\Controllers\Controller;
use App\Models\Affiliate;
use App\Models\AffiliateCommission;
use App\Models\AffiliateCommissionPayment;
use App\Models\AffiliateWithdrawal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LeaderboardController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->get('period', 'all_time');
        $currentAffiliateId = auth()->user()->affiliate->id;
        
        // Build date constraints based on period
        $dateConstraint = null;
        $periodLabel = 'All Time';
        
        switch ($period) {
            case 'this_month':
                $dateConstraint = [now()->format('n'), now()->format('Y')];
                $periodLabel = 'This Month';
                break;
            case 'last_month':
                $dateConstraint = [now()->subMonth()->format('n'), now()->subMonth()->format('Y')];
                $periodLabel = 'Last Month';
                break;
            case 'this_year':
                $dateConstraint = [null, now()->format('Y')];
                $periodLabel = 'This Year';
                break;
            case 'last_3_months':
                $periodLabel = 'Last 3 Months';
                break;
        }

        // ── Name expression (handles nullable last_name) ──
        $nameExpr = DB::raw("CONCAT(users.first_name, ' ', COALESCE(users.last_name, '')) as name");

        // ── Top Earners (by total commission) ──
        $topEarnersQuery = Affiliate::query()
            ->join('users', 'affiliates.user_id', '=', 'users.id')
            ->leftJoin('affiliate_commissions', 'affiliates.id', '=', 'affiliate_commissions.affiliate_id')
            ->select(
                'affiliates.id',
                'affiliates.referral_code',
                $nameExpr,
                'users.email',
                DB::raw('COALESCE(SUM(affiliate_commissions.commission_amount), 0) as total_earned'),
                DB::raw('COUNT(DISTINCT affiliate_commissions.owner_id) as total_referrals')
            )
            ->groupBy('affiliates.id', 'affiliates.referral_code', 'users.first_name', 'users.last_name', 'users.email')
            ->orderByDesc('total_earned');

        // ── Top Recruiters (by number of referrals) ──
        $topRecruitersQuery = Affiliate::query()
            ->join('users', 'affiliates.user_id', '=', 'users.id')
            ->leftJoin('affiliate_commissions', 'affiliates.id', '=', 'affiliate_commissions.affiliate_id')
            ->select(
                'affiliates.id',
                'affiliates.referral_code',
                $nameExpr,
                'users.email',
                DB::raw('COUNT(DISTINCT affiliate_commissions.owner_id) as total_referrals'),
                DB::raw('COALESCE(SUM(affiliate_commissions.commission_amount), 0) as total_earned')
            )
            ->groupBy('affiliates.id', 'affiliates.referral_code', 'users.first_name', 'users.last_name', 'users.email')
            ->orderByDesc('total_referrals');

        // Apply date filters
        if ($period === 'this_month' || $period === 'last_month') {
            $topEarnersQuery->where('affiliate_commissions.period_month', $dateConstraint[0])
                           ->where('affiliate_commissions.period_year', $dateConstraint[1]);
            $topRecruitersQuery->where('affiliate_commissions.period_month', $dateConstraint[0])
                              ->where('affiliate_commissions.period_year', $dateConstraint[1]);
        } elseif ($period === 'this_year') {
            $topEarnersQuery->where('affiliate_commissions.period_year', $dateConstraint[1]);
            $topRecruitersQuery->where('affiliate_commissions.period_year', $dateConstraint[1]);
        } elseif ($period === 'last_3_months') {
            $threeMonthsAgo = now()->subMonths(3);
            $topEarnersQuery->where(function ($q) use ($threeMonthsAgo) {
                $q->where('affiliate_commissions.period_year', '>', $threeMonthsAgo->format('Y'))
                  ->orWhere(function ($q2) use ($threeMonthsAgo) {
                      $q2->where('affiliate_commissions.period_year', $threeMonthsAgo->format('Y'))
                         ->where('affiliate_commissions.period_month', '>=', $threeMonthsAgo->format('n'));
                  });
            });
            $topRecruitersQuery->where(function ($q) use ($threeMonthsAgo) {
                $q->where('affiliate_commissions.period_year', '>', $threeMonthsAgo->format('Y'))
                  ->orWhere(function ($q2) use ($threeMonthsAgo) {
                      $q2->where('affiliate_commissions.period_year', $threeMonthsAgo->format('Y'))
                         ->where('affiliate_commissions.period_month', '>=', $threeMonthsAgo->format('n'));
                  });
            });
        }

        $topEarners = $topEarnersQuery->take(6)->get();
        $topRecruiters = $topRecruitersQuery->take(6)->get();

        // ── Current Affiliate's Earnings ──
        $currentAffiliateEarnings = AffiliateCommission::where('affiliate_id', $currentAffiliateId);

        if ($period === 'this_month' || $period === 'last_month') {
            $currentAffiliateEarnings->where('period_month', $dateConstraint[0])
                                     ->where('period_year', $dateConstraint[1]);
        } elseif ($period === 'this_year') {
            $currentAffiliateEarnings->where('period_year', $dateConstraint[1]);
        } elseif ($period === 'last_3_months') {
            $threeMonthsAgo = now()->subMonths(3);
            $currentAffiliateEarnings->where(function ($q) use ($threeMonthsAgo) {
                $q->where('period_year', '>', $threeMonthsAgo->format('Y'))
                  ->orWhere(function ($q2) use ($threeMonthsAgo) {
                      $q2->where('period_year', $threeMonthsAgo->format('Y'))
                         ->where('period_month', '>=', $threeMonthsAgo->format('n'));
                  });
            });
        }

        $myEarnings = $currentAffiliateEarnings->sum('commission_amount');
        $myReferrals = AffiliateCommission::where('affiliate_id', $currentAffiliateId)
            ->distinct('owner_id')
            ->count('owner_id');

        // ── Recent big wins (last 7 days) — anonymous social proof ──
        // Amount + source + when ONLY. The view shows "An affiliate" and no owner, so we never even
        // load an owner's or another affiliate's identity into this payload (privacy hygiene — the
        // commission row already proves the win; the names would only expose unrelated people).
        $recentBigWins = AffiliateCommission::where('created_at', '>=', now()->subDays(7))
            ->where('commission_amount', '>=', 100)
            ->orderByDesc('commission_amount')
            ->take(10)
            ->get()
            ->map(function ($c) {
                return [
                    'amount' => $c->commission_amount,
                    'source' => ucfirst((string) $c->source),
                    'when'   => $c->created_at->diffForHumans(),
                ];
            });

        // Header opportunity stat (see the view): how many months an affiliate earns RECURRING
        // commission on every owner — a positive program fact that reveals nothing about our scale.
        // We deliberately do NOT surface any platform-wide count here ("total paid", "owners
        // referred", or even "active affiliates"): while the program is young, small aggregates
        // both reveal our client/affiliate-base size AND demotivate (they wrongly read as "few are
        // here, so this must be hard"). The other two header tiles are the affiliate's OWN figures.
        $recurringMonths = (int) (getOption('RECURRING_COMMISSION_MONTHS') ?: 12);

        return view('affiliate.leaderboard.index', compact(
            'topEarners',
            'topRecruiters',
            'myEarnings',
            'myReferrals',
            'currentAffiliateId',
            'period',
            'periodLabel',
            'recentBigWins',
            'recurringMonths'
        ));
    }
}