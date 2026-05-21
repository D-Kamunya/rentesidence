<?php

namespace App\Http\Controllers\Affiliates;

use App\Http\Controllers\Controller;
use App\Models\Affiliate;
use App\Models\AffiliateCommission;
use App\Models\AffiliateCommissionPayment;
use App\Models\AffiliateWithdrawal;
use App\Models\Owner;
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

        $topEarners = $topEarnersQuery->take(20)->get();
        $topRecruiters = $topRecruitersQuery->take(20)->get();

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

        // ── Recent big wins (last 7 days) ──
        $recentBigWins = AffiliateCommission::with(['affiliate.user', 'owner.user'])
            ->where('created_at', '>=', now()->subDays(7))
            ->where('commission_amount', '>=', 100)
            ->orderByDesc('commission_amount')
            ->take(10)
            ->get()
            ->map(function ($c) {
                return [
                    'affiliate_name' => trim(($c->affiliate->user->first_name ?? '') . ' ' . ($c->affiliate->user->last_name ?? '')),
                    'owner_name' => trim(($c->owner->user->first_name ?? '') . ' ' . ($c->owner->user->last_name ?? '')),
                    'amount' => $c->commission_amount,
                    'source' => ucfirst($c->source),
                    'when' => $c->created_at->diffForHumans(),
                ];
            });

        // ── Total platform stats ──
        $totalPlatformEarnings = AffiliateCommission::sum('commission_amount');
        $totalAffiliates = Affiliate::count();
        $totalReferrals = Owner::whereNotNull('affiliate_id')->count();

        return view('affiliate.leaderboard.index', compact(
            'topEarners',
            'topRecruiters',
            'myEarnings',
            'myReferrals',
            'currentAffiliateId',
            'period',
            'periodLabel',
            'recentBigWins',
            'totalPlatformEarnings',
            'totalAffiliates',
            'totalReferrals'
        ));
    }
}