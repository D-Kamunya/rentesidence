<?php

namespace App\Services;

use App\Models\Affiliate;
use App\Models\AffiliateCommission;
use App\Models\AffiliateCommissionPayment;
use App\Models\AffiliateWithdrawal;
use App\Models\Owner;
use App\Models\Order;
use App\Models\ProductOrder;
use App\Models\SubscriptionOrder;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AffiliateCommissionService
{
    // ──────────────────────────────────────────────────────────
    // SUBSCRIPTION
    // ──────────────────────────────────────────────────────────

    public function handleSubscriptionPayment(SubscriptionOrder $order, ?int $affiliateId = null)
    {
        if (!in_array(strtolower($order->payment_status), [ORDER_PAYMENT_STATUS_PAID])) {
            return null;
        }

        $paidAt      = $order->created_at ?? Carbon::now();
        $periodMonth = (int) $paidAt->format('n');
        $periodYear  = (int) $paidAt->format('Y');

        $affiliateId = $this->findAffiliateForOwner($order->user_id);
        if (empty($affiliateId)) return null;

        $affiliate = Affiliate::find($affiliateId);
        if (!$affiliate) return null;

        $subscriptionId     = $order->package_id ?? null;
        $subscriptionAmount = (float) ($order->transaction_amount ?? 0);
        $ownerRecord = \App\Models\Owner::where('user_id', $order->user_id)->first();
        if (!$ownerRecord) return null;
        $ownerId = $ownerRecord->id;

        return DB::transaction(function () use (
            $affiliateId, $ownerId, $subscriptionId, $subscriptionAmount,
            $periodMonth, $periodYear, $order
        ) {
            $everExists = AffiliateCommission::where('affiliate_id', $affiliateId)
                ->where('owner_id', $ownerId)
                ->where('source', AFFILIATE_COMMISSION_SOURCE_SUBSCRIPTION)
                ->exists();

            $type = $everExists ? RECURRING_CLIENT : NEW_CLIENT;

            $firstCommission = AffiliateCommission::where('affiliate_id', $affiliateId)
                ->where('owner_id', $ownerId)
                ->where('source', AFFILIATE_COMMISSION_SOURCE_SUBSCRIPTION)
                ->orderBy('created_at', 'asc')
                ->first();

            $monthsElapsed = $firstCommission
                ? Carbon::parse($firstCommission->created_at)->diffInMonths(now())
                : 0;

            if (
                getOption('RECURRING_COMMISSION_RATE') === null ||
                getOption('RECURRING_COMMISSION_MONTHS') === null ||
                getOption('FIRST_TIME_COMMISSION_RATE') === null
            ) {
                return null;
            }

            if ($monthsElapsed >= (int) getOption('RECURRING_COMMISSION_MONTHS')) {
                return null;
            }

            $rate = $type === NEW_CLIENT
                ? (float) getOption('FIRST_TIME_COMMISSION_RATE')
                : (float) getOption('RECURRING_COMMISSION_RATE');

            $commissionAmount = round($subscriptionAmount * ($rate / 100), 2);

            $commission = AffiliateCommission::create([
                'affiliate_id'            => $affiliateId,
                'owner_id'                => $ownerId,
                'subscription_id'         => $subscriptionId,
                'subscription_payment_id' => $order->id,
                'subscription_amount'     => $subscriptionAmount,
                'type'                    => $type,
                'source'                  => AFFILIATE_COMMISSION_SOURCE_SUBSCRIPTION,
                'commission_rate'         => $rate,
                'commission_amount'       => $commissionAmount,
                'period_month'            => $periodMonth,
                'period_year'             => $periodYear,
            ]);

            $this->recalculatePeriodSummary($affiliateId, $periodMonth, $periodYear);

            return $commission->toArray();
        });
    }

    // ──────────────────────────────────────────────────────────
    // RENT
    // ──────────────────────────────────────────────────────────

    /**
     * Process affiliate commission for a completed rent payment.
     * 15% of the 1% centresidence takes = 0.15% of gross transaction amount.
     * Valid for RECURRING_COMMISSION_MONTHS months from first commission.
     * Called from CommissionService::processRentCommission() after owner wallet credit.
     */
    public function handleRentCommission(Order $order): void
    {
        $invoice = $order->invoice ?? \App\Models\Invoice::find($order->invoice_id);
        if (!$invoice) return;

        // Resolve owners.id from users.id — affiliate_commissions.owner_id FK points to owners.id
        $ownerRecord = Owner::where('user_id', $invoice->owner_user_id)->first();
        if (!$ownerRecord) return;

        $affiliateId = $ownerRecord->affiliate_id;
        if (!$affiliateId) return;

        $months = (int) getOption('RECURRING_COMMISSION_MONTHS', 12);
        if ($months <= 0) return;

        $paidAt      = $order->updated_at ?? Carbon::now();
        $periodMonth = (int) $paidAt->format('n');
        $periodYear  = (int) $paidAt->format('Y');

        // Idempotency — don't double-process the same order
        $already = AffiliateCommission::where('affiliate_id', $affiliateId)
            ->where('source', AFFILIATE_COMMISSION_SOURCE_RENT)
            ->where('order_id', $order->id)
            ->exists();

        if ($already) return;

        // Check months elapsed from first rent commission for this affiliate+owner
        $firstCommission = AffiliateCommission::where('affiliate_id', $affiliateId)
            ->where('owner_id', $ownerRecord->id)
            ->where('source', AFFILIATE_COMMISSION_SOURCE_RENT)
            ->orderBy('created_at', 'asc')
            ->first();

        $monthsElapsed = $firstCommission
            ? Carbon::parse($firstCommission->created_at)->diffInMonths(now())
            : 0;

        if ($monthsElapsed >= $months) return;

        // 15% of the 1% centresidence commission = 0.15% of gross
        $grossAmount      = (float) $order->transaction_amount;
        $rate             = 0.15; // 15% of 1% = 0.15% effective
        $commissionAmount = round($grossAmount * ($rate / 100), 2);

        DB::transaction(function () use (
            $affiliateId, $ownerRecord, $invoice, $order,
            $commissionAmount, $rate, $periodMonth, $periodYear
        ) {
            AffiliateCommission::create([
                'affiliate_id'     => $affiliateId,
                'owner_id'         => $ownerRecord->id,
                'source'           => AFFILIATE_COMMISSION_SOURCE_RENT,
                'order_id'         => $order->id,
                'commission_rate'  => $rate,
                'commission_amount'=> $commissionAmount,
                'period_month'     => $periodMonth,
                'period_year'      => $periodYear,
            ]);

            $this->recalculatePeriodSummary($affiliateId, $periodMonth, $periodYear);
        });
    }

    // ──────────────────────────────────────────────────────────
    // MARKETPLACE
    // ──────────────────────────────────────────────────────────

    /**
     * Process affiliate commission for a completed product order.
     * Rate comes from product_categories.affiliate_commission.
     * Valid for RECURRING_COMMISSION_MONTHS months from first commission.
     * Called from CommissionService::processOrderCommission() after owner wallet credit.
     */
    public function handleMarketplaceCommission(ProductOrder $order): void
    {
        $firstProduct = $order->orderItems->first()?->product;
        if (!$firstProduct) return;

        // products.owner_user_id → owners.id → owners.affiliate_id
        $ownerRecord = Owner::find($firstProduct->owner_user_id);
        if (!$ownerRecord) return;

        $affiliateId = $ownerRecord->affiliate_id;
        if (!$affiliateId) return;

        $months = (int) getOption('RECURRING_COMMISSION_MONTHS', 12);          
        if ($months <= 0) return;

        $paidAt      = $order->updated_at ?? Carbon::now();
        $periodMonth = (int) $paidAt->format('n');
        $periodYear  = (int) $paidAt->format('Y');

        // Idempotency
        $already = AffiliateCommission::where('affiliate_id', $affiliateId)
            ->where('source', AFFILIATE_COMMISSION_SOURCE_MARKETPLACE)
            ->where('order_id', $order->id)
            ->exists();

        if ($already) return;

        // Check months elapsed from first marketplace commission for this affiliate+owner
        $firstCommission = AffiliateCommission::where('affiliate_id', $affiliateId)
            ->where('owner_id', $ownerRecord->id)
            ->where('source', AFFILIATE_COMMISSION_SOURCE_MARKETPLACE)
            ->orderBy('created_at', 'asc')
            ->first();

        $monthsElapsed = $firstCommission
            ? Carbon::parse($firstCommission->created_at)->diffInMonths(now())
            : 0;

        if ($monthsElapsed >= $months) return;

        // Rate from product category
        $rate = (float) ($firstProduct->productCategory->affiliate_commission ?? 0);
        if ($rate <= 0) return;

        $grossAmount      = (float) $order->transaction_amount;
        $commissionAmount = round($grossAmount * ($rate / 100), 2);

        DB::transaction(function () use (
            $affiliateId, $ownerRecord, $order,
            $commissionAmount, $rate, $periodMonth, $periodYear
        ) {
            AffiliateCommission::create([
                'affiliate_id'      => $affiliateId,
                'owner_id'          => $ownerRecord->id,
                'source'            => AFFILIATE_COMMISSION_SOURCE_MARKETPLACE,
                'order_id'          => $order->id,
                'commission_rate'   => $rate,
                'commission_amount' => $commissionAmount,
                'period_month'      => $periodMonth,
                'period_year'       => $periodYear,
            ]);

            $this->recalculatePeriodSummary($affiliateId, $periodMonth, $periodYear);
        });
    }


    // ──────────────────────────────────────────────────────────
    // PERIOD SUMMARY
    // ──────────────────────────────────────────────────────────

    public function recalculatePeriodSummary(int $affiliateId, int $month, int $year): AffiliateCommissionPayment
    {
        // ── Subscription ──────────────────────────────────────
        $newQuery = AffiliateCommission::where('affiliate_id', $affiliateId)
            ->where('source', AFFILIATE_COMMISSION_SOURCE_SUBSCRIPTION)
            ->where('type', NEW_CLIENT)
            ->where('period_month', $month)
            ->where('period_year', $year);

        $recurringQuery = AffiliateCommission::where('affiliate_id', $affiliateId)
            ->where('source', AFFILIATE_COMMISSION_SOURCE_SUBSCRIPTION)
            ->where('type', RECURRING_CLIENT)
            ->where('period_month', $month)
            ->where('period_year', $year);

        $newCount             = $newQuery->count();
        $newAmount            = (float) $newQuery->sum('subscription_amount');
        $recurringClientsCount= (int) $recurringQuery->distinct('owner_id')->count('owner_id');
        $recurringAmount      = (float) $recurringQuery->sum('subscription_amount');

        $newPayout       = round($newAmount * ((float) getOption('FIRST_TIME_COMMISSION_RATE', 0) / 100), 2);
        $recurringPayout = round($recurringAmount * ((float) getOption('RECURRING_COMMISSION_RATE', 0) / 100), 2);

        // ── Rent ─────────────────────────────────────────────
        $rentAmount  = (float) AffiliateCommission::where('affiliate_id', $affiliateId)
            ->where('source', AFFILIATE_COMMISSION_SOURCE_RENT)
            ->where('period_month', $month)
            ->where('period_year', $year)
            ->sum('commission_amount');

        $rentPayout = $rentAmount; // already the net amount

        // ── Marketplace ───────────────────────────────────────
        $marketplaceAmount = (float) AffiliateCommission::where('affiliate_id', $affiliateId)
            ->where('source', AFFILIATE_COMMISSION_SOURCE_MARKETPLACE)
            ->where('period_month', $month)
            ->where('period_year', $year)
            ->sum('commission_amount');

        $marketplacePayout = $marketplaceAmount; // already the net amount

        // ── Total ─────────────────────────────────────────────
        $totalPayout = round($newPayout + $recurringPayout + $rentPayout + $marketplacePayout, 2);

        return AffiliateCommissionPayment::create([
            'affiliate_id'                   => $affiliateId,
            'period_month'                   => $month,
            'period_year'                    => $year,
            'total_new_clients'              => $newCount,
            'total_recurring_clients'        => $recurringClientsCount,
            'new_commissions_amount'         => $newAmount,
            'recurring_commissions_amount'   => $recurringAmount,
            'new_commission_payout'          => $newPayout,
            'recurring_commission_payout'    => $recurringPayout,
            'rent_commissions_amount'        => $rentAmount,
            'rent_commission_payout'         => $rentPayout,
            'marketplace_commissions_amount' => $marketplaceAmount,
            'marketplace_commission_payout'  => $marketplacePayout,
            'total_commission_payout'        => $totalPayout,
        ]);
    }

    // ──────────────────────────────────────────────────────────
    // DASHBOARD HELPERS
    // ──────────────────────────────────────────────────────────

    public function getLatestPeriodPayout(int $affiliateId, int $month, int $year): float
    {
        $row = AffiliateCommissionPayment::where('affiliate_id', $affiliateId)
            ->where('period_month', $month)
            ->where('period_year', $year)
            ->orderByDesc('created_at')
            ->first();

        return $row ? (float) $row->total_commission_payout : 0.0;
    }

    public function getLifetimeEarningsMinusWithdrawals(int $affiliateId): float
    {
        $sub = AffiliateCommissionPayment::selectRaw('MAX(id) as max_id')
            ->where('affiliate_id', $affiliateId)
            ->groupBy('period_year', 'period_month');

        $totalEarnings = (float) AffiliateCommissionPayment::whereIn('id', $sub->pluck('max_id'))
            ->sum('total_commission_payout');

        $withdrawn = AffiliateWithdrawal::where('affiliate_id', $affiliateId)
            ->whereIn('status', [AFFILIATE_WITHDRAWAL_APPROVED])
            ->sum('amount');

        return round($totalEarnings - (float) $withdrawn, 2);
    }

    public function getAvailableBalance(int $affiliateId): float
    {
        return $this->getLifetimeEarningsMinusWithdrawals($affiliateId);
    }

    public function getLifeTimeGrossCommissions(int $affiliateId): float
    {
        return $this->getLifetimeEarningsMinusWithdrawals($affiliateId)
            + (float) AffiliateWithdrawal::where('affiliate_id', $affiliateId)
                ->whereIn('status', [AFFILIATE_WITHDRAWAL_APPROVED])
                ->sum('amount');
    }

    // ──────────────────────────────────────────────────────────
    // SHARED HELPERS
    // ──────────────────────────────────────────────────────────

    protected function findAffiliateForOwner($ownerUserId): ?int
    {
        if (empty($ownerUserId)) return null;
        return Owner::where('user_id', $ownerUserId)->value('affiliate_id');
    }
}