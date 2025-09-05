<?php

namespace App\Services;

use App\Models\Affiliate;
use App\Models\AffiliateCommission;
use App\Models\AffiliateCommissionPayment;
use App\Models\SubscriptionOrder;
use App\Models\OwnerPackage;
use App\Models\Owner;
use App\Models\AffiliateWithdrawal;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AffiliateCommissionService
{
    /**
     * Process a subscription payment -> create affiliate commission (new|recurring)
     * and recalculate the affiliate commission payment for the period.
     *
     * @param SubscriptionOrder $order
     * @param int|null $affiliateId Optional affiliate id (if null we try to resolve)
     * @return array|null Commission record or null if nothing done
     */
    public function handleSubscriptionPayment(SubscriptionOrder $order, ?int $affiliateId = null)
    {
        // Only process paid orders
        if (!in_array(strtolower($order->payment_status), [ORDER_PAYMENT_STATUS_PAID])) {
            return null;
        }

        // Determine period month/year
        $paidAt = $order->created_at ?? Carbon::now();
        $periodMonth = (int) $paidAt->format('n');
        $periodYear  = (int) $paidAt->format('Y');

        // Resolve affiliate id:
        $affiliateId = $this->findAffiliateForOwner($order->user_id); // try lookup by owner user id

        if (empty($affiliateId)) {
            // No affiliate attached — nothing to do
            return null;
        }

        $affiliate = Affiliate::find($affiliateId);
        if (!$affiliate) return null;

        // Use subscription id and subscription amount:
        $subscriptionId = $order->package_id ?? null; // package_id on subscription order
        $subscriptionAmount = (float)( $order->transaction_amount ?? 0);
       
        // Owner id (owner who subscribed)
        $ownerId = $order->user_id;

        return DB::transaction(function () use (
            $affiliateId, $ownerId, $subscriptionId, $subscriptionAmount, $periodMonth, $periodYear, $order
        ) {
            // 1) Determine if ever existed (new vs recurring)
            $everExists = AffiliateCommission::where('affiliate_id', $affiliateId)
                ->where('owner_id', $ownerId)
                ->exists();

            $type = $everExists ? RECURRING_CLIENT:NEW_CLIENT;

            // // 2) Prevent duplicate commission for same affiliate+subscription+period+type
            // $already = AffiliateCommission::where('affiliate_id', $affiliateId)
            //     ->where('subscription_id', $subscriptionId)
            //     ->where('period_month', $periodMonth)
            //     ->where('period_year', $periodYear)
            //     ->where('type', $type)
            //     ->exists();

            // if ($already) {
            //     // nothing to do (already counted this period)
            //     return null;
            // }
            $firstCommission = AffiliateCommission::where('affiliate_id', $affiliateId)
                ->where('owner_id', $ownerId)
                ->orderBy('created_at', 'asc')
                ->first();

            if (!$firstCommission) {
                // No commission exists yet — this is the first one, so allow creation
                $monthsElapsed = 0;
            } else {
                $startDate = $firstCommission->created_at;
                $monthsElapsed = Carbon::parse($startDate)->diffInMonths(now());
            }

            if (getOption('RECURRING_COMMISSION_RATE') === null || getOption('RECURRING_COMMISSION_MONTHS') === null || getOption('FIRST_TIME_COMMISSION_RATE') === null) {
                return;
            }

            // Stop if limit reached
            if ($monthsElapsed >= (int) getOption('RECURRING_COMMISSION_MONTHS')) {
                return; // Do not generate new commission
            }

            // 3) Create commission record
            $commission = AffiliateCommission::create([
                'affiliate_id' => $affiliateId,
                'owner_id' => $ownerId,
                'subscription_id' => $subscriptionId,
                'subscription_payment_id' => $order->id,
                'subscription_amount' => $subscriptionAmount,
                'type' => $type,
                'period_month' => $periodMonth,
                'period_year' => $periodYear,
            ]);

            

            // 4) Recalculate & store the commission payment summary for this affiliate & period
            $this->recalculatePeriodSummary($affiliateId, $periodMonth, $periodYear);

            return $commission->toArray();
        });
    }

    /**
     * Recalculate summary totals for affiliate for a specific period and insert a new
     * AffiliateCommissionPayment row (keeps history).
     *
     * Fields to set on AffiliateCommissionPayment:
     * 'affiliate_id','period_month','period_year',
     * 'total_new_clients','total_recurring_clients','new_commissions_amount','recurring_commissions_amount',
     * 'new_commission_payout','recurring_commission_payout','total_commission_payout'
     *
     * @param int $affiliateId
     * @param int $month
     * @param int $year
     * @return AffiliateCommissionPayment
     */
    public function recalculatePeriodSummary(int $affiliateId, int $month, int $year)
    {
        // Gather new commissions (this period)
        $newCommissionsQuery = AffiliateCommission::where('affiliate_id', $affiliateId)
            ->where('type', NEW_CLIENT)
            ->where('period_month', $month)
            ->where('period_year', $year);
        $newCommissionsCount = $newCommissionsQuery->count();
        $newCommissionsAmount = (float) $newCommissionsQuery->sum('subscription_amount');

        // Gather recurring commissions (this period)
        $recurringCommissionsQuery = AffiliateCommission::where('affiliate_id', $affiliateId)
            ->where('type', RECURRING_CLIENT)
            ->where('period_month', $month)
            ->where('period_year', $year);
        // total recurring clients (distinct owners) - tier is based on number of recurring clients
        $recurringClientsCount = (int) $recurringCommissionsQuery->distinct('owner_id')->count('owner_id');
        $recurringCommissionsAmount = (float) $recurringCommissionsQuery->get()->sum('subscription_amount');
        // Compute payouts

        $newCommissionPayout = round($newCommissionsAmount * ((float) getOption('FIRST_TIME_COMMISSION_RATE') / 100), 2);
        $recurringCommissionPayout = round($recurringCommissionsAmount * ((float) getOption('RECURRING_COMMISSION_RATE') / 100), 2);
        $totalCommissionPayout = round($newCommissionPayout + $recurringCommissionPayout, 2);

        // Insert a new AffiliateCommissionPayment row (we keep history of recalculations)
        $payment = AffiliateCommissionPayment::create([
            'affiliate_id' => $affiliateId,
            'period_month' => $month,
            'period_year' => $year,
            'total_new_clients' => $newCommissionsCount,
            'total_recurring_clients' => $recurringClientsCount,
            'new_commissions_amount' => $newCommissionsAmount,
            'recurring_commissions_amount' => $recurringCommissionsAmount,
            'new_commission_payout' => $newCommissionPayout,
            'recurring_commission_payout' => $recurringCommissionPayout,
            'total_commission_payout' => $totalCommissionPayout,
        ]);

        return $payment;
    }

    /**
     * Try to find affiliate id for a given owner user id.
     * This is a best-effort method: look for an Affiliate where user_id == owner id.
     * You can change this resolver to match your actual ownership -> affiliate mapping.
     *
     * @param int|null $ownerUserId
     * @return int|null
     */
    protected function findAffiliateForOwner($ownerUserId): ?int
    {
        if (empty($ownerUserId)) return null;
        return Owner::where('user_id', $ownerUserId)->value('affiliate_id');
    }

    /**
     * Dashboard helper: get latest commission payment for affiliate for given period.
     * Returns numeric total_commission_payout or 0.
     */
    public function getLatestPeriodPayout(int $affiliateId, int $month, int $year): float
    {
        $row = AffiliateCommissionPayment::where('affiliate_id', $affiliateId)
            ->where('period_month', $month)
            ->where('period_year', $year)
            ->orderByDesc('created_at')
            ->first();

        return $row ? (float)$row->total_commission_payout : 0.0;
    }

    /**
     * Dashboard helper: compute lifetime earnings as sum of the latest commission payment for each month (dedup months)
     * minus approved withdrawals.
     *
     * Implementation note:
     * - We select for this affiliate, grouped by (period_year, period_month) the latest row id (max id),
     * - then sum total_commission_payout of those latest rows.
     */
    public function getLifetimeEarningsMinusWithdrawals(int $affiliateId): float
    {
        // Subquery: get latest id per month-year for this affiliate
        $sub = AffiliateCommissionPayment::selectRaw('MAX(id) as max_id')
            ->where('affiliate_id', $affiliateId)
            ->groupBy('period_year', 'period_month');

        // Sum the latest rows' total_commission_payout
        $sumRow = AffiliateCommissionPayment::whereIn('id', $sub->pluck('max_id'))->sum('total_commission_payout');

        $totalEarnings = (float) $sumRow;

        // Subtract approved withdrawals
        $withdrawn = AffiliateWithdrawal::where('affiliate_id', $affiliateId)
            ->whereIn('status', ['approved', 'paid']) // your statuses
            ->sum('amount');

        return round($totalEarnings - (float)$withdrawn, 2);
    }

    /**
     * Get available balance (simple calculation):
     * - sum of latest monthly payouts (as above) minus approved withdrawals
     * This is same as getLifetimeEarningsMinusWithdrawals() logically but retained for naming clarity
     */
    public function getAvailableBalance(int $affiliateId): float
    {
        return $this->getLifetimeEarningsMinusWithdrawals($affiliateId);
    }

    public function getLifeTimeGrossCommissions(int $affiliateId): float
    {
        return $this->getLifetimeEarningsMinusWithdrawals($affiliateId) + 
                         \App\Models\AffiliateWithdrawal::where('affiliate_id', $affiliateId)
                            ->whereIn('status',[AFFILIATE_WITHDRAWAL_APPROVED])->sum('amount');
    }
}
