<?php

namespace App\Http\Controllers\Affiliates;

use App\Http\Controllers\Controller;
use App\Models\Affiliate;
use App\Models\AffiliateCommission;
use App\Models\Owner;
use App\Models\User;
use App\Models\Lead;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReferralsController extends Controller
{
    public function index()
    {
        $affiliate = auth()->user()->affiliate;
        $affiliateId = $affiliate->id;

        // Get all referred owners with their earnings summary
        $referrals = Owner::where('affiliate_id', $affiliateId)
            ->with(['user'])
            ->withCount(['commissions as total_commissions_count' => function ($q) use ($affiliateId) {
                $q->where('affiliate_id', $affiliateId);
            }])
            ->withSum(['commissions as total_earned' => function ($q) use ($affiliateId) {
                $q->where('affiliate_id', $affiliateId);
            }], 'commission_amount')
            ->withSum(['commissions as subscription_earned' => function ($q) use ($affiliateId) {
                $q->where('affiliate_id', $affiliateId)
                  ->where('source', AFFILIATE_COMMISSION_SOURCE_SUBSCRIPTION);
            }], 'commission_amount')
            ->withSum(['commissions as rent_earned' => function ($q) use ($affiliateId) {
                $q->where('affiliate_id', $affiliateId)
                  ->where('source', AFFILIATE_COMMISSION_SOURCE_RENT);
            }], 'commission_amount')
            ->withSum(['commissions as marketplace_earned' => function ($q) use ($affiliateId) {
                $q->where('affiliate_id', $affiliateId)
                  ->where('source', AFFILIATE_COMMISSION_SOURCE_MARKETPLACE);
            }], 'commission_amount')
            ->orderByDesc('total_earned')
            ->paginate(20);

        // Get first and last commission dates for each referral
        $referrals->getCollection()->transform(function ($owner) use ($affiliateId) {
            $firstCommission = AffiliateCommission::where('affiliate_id', $affiliateId)
                ->where('owner_id', $owner->id)
                ->oldest()
                ->first();
            
            $lastCommission = AffiliateCommission::where('affiliate_id', $affiliateId)
                ->where('owner_id', $owner->id)
                ->latest()
                ->first();

            $owner->first_commission_date = $firstCommission?->created_at;
            $owner->last_commission_date = $lastCommission?->created_at;
            $owner->client_type = $owner->commissions()
                ->where('affiliate_id', $affiliateId)
                ->where('source', AFFILIATE_COMMISSION_SOURCE_SUBSCRIPTION)
                ->exists() 
                ? (AffiliateCommission::where('affiliate_id', $affiliateId)
                    ->where('owner_id', $owner->id)
                    ->where('source', AFFILIATE_COMMISSION_SOURCE_SUBSCRIPTION)
                    ->where('type', NEW_CLIENT)
                    ->exists() ? 'new_client' : 'recurring_client')
                : 'other';
            
            // Determine if active (commission in last 30 days)
            $owner->is_active = $lastCommission && $lastCommission->created_at->gt(now()->subDays(30));
            
            return $owner;
        });

        // Summary stats
        $totalReferrals = Owner::where('affiliate_id', $affiliateId)->count();
        $activeReferrals = $referrals->getCollection()->where('is_active', true)->count();
        $totalEarned = $referrals->getCollection()->sum('total_earned');
        $newThisMonth = Owner::where('affiliate_id', $affiliateId)
            ->whereHas('commissions', function ($q) use ($affiliateId) {
                $q->where('affiliate_id', $affiliateId)
                  ->where('source', AFFILIATE_COMMISSION_SOURCE_SUBSCRIPTION)
                  ->where('type', NEW_CLIENT)
                  ->where('period_month', now()->format('n'))
                  ->where('period_year', now()->format('Y'));
            })
            ->count();

        return view('affiliate.referrals.index', compact(
            'referrals',
            'totalReferrals',
            'activeReferrals', 
            'totalEarned',
            'newThisMonth'
        ));
    }

    /**
     * Get detailed earnings breakdown for a specific referral
     */
    public function show($ownerId)
    {
        $affiliateId = auth()->user()->affiliate->id;
        
        $owner = Owner::with(['user'])
            ->where('affiliate_id', $affiliateId)
            ->findOrFail($ownerId);

        // Monthly earnings breakdown
        $monthlyEarnings = AffiliateCommission::where('affiliate_id', $affiliateId)
            ->where('owner_id', $ownerId)
            ->select(
                'period_month',
                'period_year',
                'source',
                DB::raw('SUM(commission_amount) as total')
            )
            ->groupBy('period_year', 'period_month', 'source')
            ->orderByDesc('period_year')
            ->orderByDesc('period_month')
            ->get()
            ->groupBy(function ($row) {
                return Carbon::createFromDate($row->period_year, $row->period_month, 1)->format('Y-m');
            })
            ->map(function ($monthGroup, $period) {
                return [
                    'period' => Carbon::parse($period . '-01')->format('M Y'),
                    'subscription' => $monthGroup->where('source', AFFILIATE_COMMISSION_SOURCE_SUBSCRIPTION)->sum('total'),
                    'rent' => $monthGroup->where('source', AFFILIATE_COMMISSION_SOURCE_RENT)->sum('total'),
                    'marketplace' => $monthGroup->where('source', AFFILIATE_COMMISSION_SOURCE_MARKETPLACE)->sum('total'),
                    'total' => $monthGroup->sum('total'),
                ];
            })
            ->values();

        // Recent commissions
        $recentCommissions = AffiliateCommission::where('affiliate_id', $affiliateId)
            ->where('owner_id', $ownerId)
            ->with(['subscription.package'])
            ->latest()
            ->take(15)
            ->get()
            ->map(function ($c) {
                return [
                    'date' => $c->created_at->format('M d, Y'),
                    'source' => ucfirst($c->source),
                    'type' => $c->type ? ucfirst($c->type) : '—',
                    'rate' => $c->commission_rate . '%',
                    'amount' => $c->commission_amount,
                    'package' => $c->subscription?->package?->name ?? '—',
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'owner' => [
                    'name' => $owner->user->name ?? 'N/A',
                    'email' => $owner->user->email ?? 'N/A',
                    'joined' => $owner->created_at->format('M Y'),
                ],
                'stats' => [
                    'total_earned' => $owner->commissions()
                        ->where('affiliate_id', $affiliateId)
                        ->sum('commission_amount'),
                    'subscription_total' => $owner->commissions()
                        ->where('affiliate_id', $affiliateId)
                        ->where('source', AFFILIATE_COMMISSION_SOURCE_SUBSCRIPTION)
                        ->sum('commission_amount'),
                    'rent_total' => $owner->commissions()
                        ->where('affiliate_id', $affiliateId)
                        ->where('source', AFFILIATE_COMMISSION_SOURCE_RENT)
                        ->sum('commission_amount'),
                    'marketplace_total' => $owner->commissions()
                        ->where('affiliate_id', $affiliateId)
                        ->where('source', AFFILIATE_COMMISSION_SOURCE_MARKETPLACE)
                        ->sum('commission_amount'),
                ],
                'monthly_earnings' => $monthlyEarnings,
                'recent_commissions' => $recentCommissions,
            ],
        ]);
    }
}