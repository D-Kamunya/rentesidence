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
use App\Services\AffiliateOs\ProductRegistry;
use App\Services\Commission\CommissionEventData;
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

        // Client type (new vs recurring) + recurring-window checks stay here — they
        // depend on the affiliate/owner's prior commissions; the rate math itself
        // lives in the product strategy, and persistence goes through recordEvent.
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

        $product  = ProductRegistry::default();
        $strategy = ProductRegistry::commissionStrategy($product);
        $computed = $strategy->compute(new CommissionEventData(
            product:     $product,
            source:      AFFILIATE_COMMISSION_SOURCE_SUBSCRIPTION,
            grossAmount: $subscriptionAmount,
            clientType:  $type,
        ));

        // Don't persist zero-value rows (consistent with rent/marketplace) — they add
        // nothing to earnings and only show up as empty months in the breakdown.
        if ($computed['commission_amount'] <= 0) return null;

        $commission = $this->recordEvent([
            'product'                 => $product,
            'affiliate_id'            => $affiliateId,
            'owner_id'                => $ownerId,
            'source'                  => AFFILIATE_COMMISSION_SOURCE_SUBSCRIPTION,
            'subscription_id'         => $subscriptionId,
            'subscription_payment_id' => $order->id,
            'external_ref'            => (string) $order->id,
            'subscription_amount'     => $subscriptionAmount,
            'type'                    => $type,
            'commission_rate'         => $computed['rate'],
            'commission_amount'       => $computed['commission_amount'],
            'currency'                => $strategy->currency(),
            'cadence'                 => $computed['cadence'],
            'period_month'            => $periodMonth,
            'period_year'             => $periodYear,
        ]);

        return $commission->toArray();
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

        // Rate math lives in the product's strategy (rent = 15% of our 1%).
        $product  = ProductRegistry::default();
        $strategy = ProductRegistry::commissionStrategy($product);
        $computed = $strategy->compute(new CommissionEventData(
            product:     $product,
            source:      AFFILIATE_COMMISSION_SOURCE_RENT,
            grossAmount: (float) $order->transaction_amount,
        ));

        if ($computed['commission_amount'] <= 0) return;

        $this->recordEvent([
            'product'           => $product,
            'affiliate_id'      => $affiliateId,
            'owner_id'          => $ownerRecord->id,
            'source'            => AFFILIATE_COMMISSION_SOURCE_RENT,
            'order_id'          => $order->id,
            'external_ref'      => (string) $order->id,
            'commission_rate'   => $computed['rate'],
            'commission_amount' => $computed['commission_amount'],
            'currency'          => $strategy->currency(),
            'cadence'           => $computed['cadence'],
            'period_month'      => $periodMonth,
            'period_year'       => $periodYear,
        ]);
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
    /**
     * The affiliate's marketplace cut = a share of CENTRESIDENCE's commission on
     * the sale (mirrors rent's "15% of our 1%"), so an affiliate can never earn
     * more than we did. `$ratePercent` is `product_categories.affiliate_commission`,
     * now read as a **% of our commission** — NOT a % of gross. Pure + testable.
     */
    public static function scopedMarketplaceCommission(float $ourCommissionAmount, float $ratePercent): float
    {
        if ($ourCommissionAmount <= 0 || $ratePercent <= 0) {
            return 0.0;
        }

        return round($ourCommissionAmount * ($ratePercent / 100), 2);
    }

    public function handleMarketplaceCommission(ProductOrder $order, ?float $ourCommissionAmount = null): void
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

        // Rate from product category — now read as a % of OUR commission (not gross).
        $rate = (float) ($firstProduct->productCategory->affiliate_commission ?? 0);
        if ($rate <= 0) return;

        // Base the cut on Centresidence's own commission on this sale (a true cut,
        // like rent) so we can never pay an affiliate more than we earned. The
        // caller (processOrderCommission) passes it; recompute defensively if not.
        if ($ourCommissionAmount === null) {
            $cs = new CommissionService;
            $ourCommissionAmount = $cs->calculate(
                (float) $order->transaction_amount,
                $cs->effectiveRate($firstProduct, (int) $ownerRecord->user_id)
            )['commission_amount'];
        }

        // The strategy applies the category rate to OUR commission (a true cut).
        $product  = ProductRegistry::default();
        $strategy = ProductRegistry::commissionStrategy($product);
        $computed = $strategy->compute(new CommissionEventData(
            product:       $product,
            source:        AFFILIATE_COMMISSION_SOURCE_MARKETPLACE,
            grossAmount:   (float) $order->transaction_amount,
            ourCommission: (float) $ourCommissionAmount,
            ratePercent:   $rate,
        ));

        if ($computed['commission_amount'] <= 0) return;

        $this->recordEvent([
            'product'           => $product,
            'affiliate_id'      => $affiliateId,
            'owner_id'          => $ownerRecord->id,
            'source'            => AFFILIATE_COMMISSION_SOURCE_MARKETPLACE,
            'order_id'          => $order->id,
            'external_ref'      => (string) $order->id,
            'commission_rate'   => $computed['rate'],
            'commission_amount' => $computed['commission_amount'],
            'currency'          => $strategy->currency(),
            'cadence'           => $computed['cadence'],
            'period_month'      => $periodMonth,
            'period_year'       => $periodYear,
        ]);
    }


    /**
     * Reverse the affiliate's marketplace commission when a sale is refunded — a NEGATIVE ledger
     * entry (not a deletion) in the current period, so it's auditable and idempotent. It reduces
     * the affiliate's lifetime/available balance; if they already withdrew that commission, their
     * available balance goes negative and is recovered from future earnings (carried-forward
     * clawback). No-op if the affiliate never earned on this order or it was already reversed.
     */
    public function reverseMarketplaceCommission(ProductOrder $order): void
    {
        $firstProduct = $order->orderItems->first()?->product;
        if (! $firstProduct) return;

        $ownerRecord = Owner::find($firstProduct->owner_user_id);
        if (! $ownerRecord || ! $ownerRecord->affiliate_id) return;
        $affiliateId = $ownerRecord->affiliate_id;

        $original = AffiliateCommission::where('affiliate_id', $affiliateId)
            ->where('source', AFFILIATE_COMMISSION_SOURCE_MARKETPLACE)
            ->where('external_ref', (string) $order->id)
            ->first();
        if (! $original) return; // affiliate never earned on this order

        // Idempotency — reversal already booked.
        $reversalRef = $order->id . '-reversal';
        $alreadyReversed = AffiliateCommission::where('affiliate_id', $affiliateId)
            ->where('source', AFFILIATE_COMMISSION_SOURCE_MARKETPLACE)
            ->where('external_ref', $reversalRef)
            ->exists();
        if ($alreadyReversed) return;

        $this->recordEvent([
            'product'           => ProductRegistry::default(),
            'affiliate_id'      => $affiliateId,
            'owner_id'          => $ownerRecord->id,
            'source'            => AFFILIATE_COMMISSION_SOURCE_MARKETPLACE,
            'order_id'          => $order->id,
            'external_ref'      => $reversalRef,
            'commission_rate'   => $original->commission_rate,
            'commission_amount' => -1 * abs((float) $original->commission_amount),
            'currency'          => $original->currency,
            'cadence'           => $original->cadence,
            'period_month'      => (int) now()->format('n'),
            'period_year'       => (int) now()->format('Y'),
        ]);
    }

    // ──────────────────────────────────────────────────────────
    // LEDGER WRITE (the §3.2 spoke contract)
    // ──────────────────────────────────────────────────────────

    /**
     * The single idempotent write path into the commission-event ledger. Given a
     * fully-resolved commission (product, source, external_ref + the amount the
     * product's strategy computed), persist it exactly once and refresh the period
     * summary. Idempotency is keyed on (product, source, external_ref): a repeat of
     * the same money event (e.g. a re-fired webhook) returns the existing row and
     * never double-credits — the guarantee the old per-source ad-hoc checks missed
     * for subscriptions entirely. See docs/affiliate-os-design.md §6.
     *
     * @param array $attrs product, affiliate_id, owner_id, source, external_ref,
     *                      commission_rate, commission_amount, currency, cadence,
     *                      period_month, period_year (+ optional subscription_id,
     *                      subscription_payment_id, order_id, subscription_amount, type)
     */
    public function recordEvent(array $attrs): AffiliateCommission
    {
        $product = $attrs['product'] ?? ProductRegistry::default();
        $source  = $attrs['source'];
        $ref     = (string) $attrs['external_ref'];

        $commission = DB::transaction(function () use ($attrs, $product, $source, $ref) {
            // One commission per money event. The lock serialises a concurrent
            // same-ref insert; the unique index (ac_product_source_ref_unique) is
            // the ultimate guard.
            $existing = AffiliateCommission::where('product', $product)
                ->where('source', $source)
                ->where('external_ref', $ref)
                ->lockForUpdate()
                ->first();

            if ($existing) {
                return $existing; // already credited — idempotent no-op
            }

            $commission = AffiliateCommission::create([
                'affiliate_id'            => $attrs['affiliate_id'],
                'owner_id'                => $attrs['owner_id'] ?? null,
                'product'                 => $product,
                'source'                  => $source,
                'external_ref'            => $ref,
                'subscription_id'         => $attrs['subscription_id'] ?? null,
                'subscription_payment_id' => $attrs['subscription_payment_id'] ?? null,
                'subscription_amount'     => $attrs['subscription_amount'] ?? 0,
                'order_id'                => $attrs['order_id'] ?? null,
                'type'                    => $attrs['type'] ?? null,
                'commission_rate'         => $attrs['commission_rate'],
                'commission_amount'       => $attrs['commission_amount'],
                'currency'                => $attrs['currency'] ?? 'KES',
                'cadence'                 => $attrs['cadence'] ?? null,
                'period_month'            => $attrs['period_month'],
                'period_year'             => $attrs['period_year'],
            ]);

            $this->recalculatePeriodSummary($attrs['affiliate_id'], $attrs['period_month'], $attrs['period_year']);

            return $commission;
        });

        // Commission-earned alerts are sent as a MONTHLY DIGEST (App\Console\Commands\
        // AffiliateCommissionDigest), NOT per event — one email/month instead of one per rent
        // payment × owner, which saves a lot of sending at scale.
        return $commission;
    }

    // ──────────────────────────────────────────────────────────
    // PERIOD SUMMARY
    // ──────────────────────────────────────────────────────────

    public function recalculatePeriodSummary(int $affiliateId, int $month, int $year): AffiliateCommissionPayment
    {
        // Serialize recalcs for this affiliate. Each caller runs this inside its
        // commission's transaction; taking a row lock on the affiliate forces a
        // concurrent recalc to WAIT for the prior one to commit — so by the time
        // it re-sums the source-of-truth (affiliate_commissions) it sees the prior
        // commission too. Without this, two commissions landing at once could each
        // re-sum without the other and the later write would drop one (lost update).
        // (No-op on sqlite in tests, which is fine — there is no concurrency there.)
        Affiliate::whereKey($affiliateId)->lockForUpdate()->first();

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

        // Payout = the commission ACTUALLY recorded on each row (locked at the rate in force when it
        // was earned), NOT a re-derivation at the CURRENT global rate. Re-deriving would let a later
        // rate change retroactively rewrite historical earnings, and make this rollup disagree with
        // the raw affiliate_commissions the referrals page sums. Mirrors rent/marketplace below.
        $newPayout            = round((float) $newQuery->sum('commission_amount'), 2);

        $recurringAmount      = (float) $recurringQuery->sum('subscription_amount');
        $recurringPayout      = round((float) $recurringQuery->sum('commission_amount'), 2);
        // distinct() mutates the builder, so count DISTINCT owners LAST (after the sums above).
        $recurringClientsCount= (int) $recurringQuery->distinct('owner_id')->count('owner_id');

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

        // One row per (affiliate, period): update the existing summary in place or
        // create it. The unique index (acp_affiliate_period_unique) guarantees the
        // one-row invariant, so readers no longer need MAX(id)-per-period dedup.
        return AffiliateCommissionPayment::updateOrCreate(
            [
                'affiliate_id' => $affiliateId,
                'period_month' => $month,
                'period_year'  => $year,
            ],
            [
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
            ]
        );
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
        // Available = lifetime gross − money already spoken for. "Spoken for" must
        // include PENDING as well as APPROVED withdrawals: a pending request has
        // reserved that money. Counting only APPROVED let an affiliate stack
        // multiple pending requests that each pass the balance check but together
        // over-draw the balance. (See also getReservedWithdrawals.)
        return round(
            $this->getLifeTimeGrossCommissions($affiliateId) - $this->getReservedWithdrawals($affiliateId),
            2
        );
    }

    public function getAvailableBalance(int $affiliateId): float
    {
        return $this->getLifetimeEarningsMinusWithdrawals($affiliateId);
    }

    /**
     * Lifetime gross commissions actually earned. With one row per period enforced
     * by the unique index (see recalculatePeriodSummary), this is a plain sum — no
     * MAX(id)-per-period dedup needed. Correct-by-construction rather than by every
     * reader remembering to dedupe (the old shape caused a double-count bug).
     */
    public function getLifeTimeGrossCommissions(int $affiliateId): float
    {
        return (float) AffiliateCommissionPayment::where('affiliate_id', $affiliateId)
            ->sum('total_commission_payout');
    }

    /**
     * Money already reserved against the balance: paid-out (APPROVED), in-flight to
     * M-Pesa (PROCESSING) and awaiting approval (PENDING). PROCESSING must be reserved
     * so an in-flight B2C payout can't be double-withdrawn; a FAILED payout is NOT
     * reserved, so the reservation is released and the balance restored automatically.
     */
    public function getReservedWithdrawals(int $affiliateId): float
    {
        return (float) AffiliateWithdrawal::where('affiliate_id', $affiliateId)
            ->whereIn('status', [
                AFFILIATE_WITHDRAWAL_APPROVED,
                AFFILIATE_WITHDRAWAL_PROCESSING,
                AFFILIATE_WITHDRAWAL_PENDING,
            ])
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