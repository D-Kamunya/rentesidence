<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OwnerWallet;
use App\Models\Package;
use App\Models\Product;
use App\Models\ProductOrder;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CommissionService
{
    /**
     * Minimum commission floor — overrides all other logic for marketplace.
     */
    const MINIMUM_COMMISSION = 3.0;

    /**
     * Flat commission rate for rent transaction-model owners.
     */
    const RENT_COMMISSION_RATE = 1.0;

    // ──────────────────────────────────────────────────────────
    // MARKETPLACE
    // ──────────────────────────────────────────────────────────

    /**
     * Get the effective commission rate for a product given an owner's package.
     * Formula: MAX(base_commission + markup - discount, MINIMUM_COMMISSION)
     */
    public function effectiveRate(Product $product, int $ownerUserId): float
    {
        $baseCommission = $product->productCategory?->base_commission ?? 0;
        $package        = $this->ownerPackage($ownerUserId);

        $markup   = $package?->commission_markup   ?? 3.0;
        $discount = $package?->commission_discount ?? 0.0;

        $effective = $baseCommission + $markup - $discount;

        return max($effective, self::MINIMUM_COMMISSION);
    }

    /**
     * Calculate commission breakdown for a given gross amount and rate.
     *
     * @return array ['commission_rate' => float, 'commission_amount' => float, 'net_amount' => float]
     */
    public function calculate(float $grossAmount, float $rate): array
    {
        $commissionAmount = round($grossAmount * ($rate / 100), 2);
        $netAmount        = round($grossAmount - $commissionAmount, 2);

        return [
            'commission_rate'   => $rate,
            'commission_amount' => $commissionAmount,
            'net_amount'        => $netAmount,
        ];
    }

    /**
     * ESCROW — hold a paid order's proceeds with the platform. Called when the order is PAID:
     * the owner is NOT credited yet (best-practice marketplace escrow), so a refund before
     * delivery is trivial. Release happens on delivery via releaseOnDelivery(). Idempotent;
     * leaves legacy pre-escrow orders (already credited under the old immediate model) alone.
     */
    public function holdOnPayment(ProductOrder $order): void
    {
        if ($order->settlement_status === null && ! $this->alreadyCredited($order)) {
            $order->forceFill(['settlement_status' => SETTLEMENT_STATUS_HELD])->save();
        }
    }

    /**
     * ESCROW — release held proceeds to the owner on DELIVERY: credit the wallet + book the
     * platform/affiliate commission, exactly once, then stamp the order released. A refunded or
     * already-released order is a no-op. Legacy orders (settlement_status null) also release
     * cleanly (processOrderCommission is idempotent), so nothing regresses.
     */
    public function releaseOnDelivery(ProductOrder $order): ?WalletTransaction
    {
        if (in_array($order->settlement_status, [SETTLEMENT_STATUS_RELEASED, SETTLEMENT_STATUS_REFUNDED], true)) {
            return null;
        }

        $wt = $this->processOrderCommission($order);

        $order->forceFill([
            'settlement_status'      => SETTLEMENT_STATUS_RELEASED,
            'settlement_released_at' => now(),
        ])->save();

        return $wt;
    }

    /** Has this order already been credited to the owner's wallet? */
    public function alreadyCredited(ProductOrder $order): bool
    {
        return WalletTransaction::where('product_order_id', $order->id)
            ->where('type', 'credit')
            ->where('transaction_source', 'marketplace')
            ->exists();
    }

    /**
     * Process commission for a completed product order — the RELEASE step (credit owner wallet
     * net + book platform/affiliate commission). Called from releaseOnDelivery() on DELIVERY.
     *
     * Owner resolution: products.owner_user_id = owners.id (primary key)
     * So we must do Owner::find() first to get the actual users.id.
     *
     * Idempotent — safe to call multiple times, will not double-process.
     */
    public function processOrderCommission(ProductOrder $order): WalletTransaction
    {
        return DB::transaction(function () use ($order) {
        // Serialize per-order so concurrent payment callbacks (an M-Pesa webhook retry racing
        // the verify fallback / another caller) can't both credit: the second waits for the
        // first, then finds the existing credit row and returns it. Makes the method genuinely
        // idempotent regardless of caller — the counterpart to reverseOrderCommission().
        \App\Models\ProductOrder::whereKey($order->id)->lockForUpdate()->first();

        $existingCredit = WalletTransaction::where('product_order_id', $order->id)
            ->where('type', 'credit')
            ->where('transaction_source', 'marketplace')
            ->first();
        if ($existingCredit) {
            return $existingCredit; // already processed — never double-credit
        }

        // Resolve owner from the first order item's product
        $firstProduct = $order->orderItems->first()?->product;
        if (!$firstProduct) {
            throw new \Exception("Cannot process commission: no products found on order #{$order->id}");
        }

        // products.owner_user_id stores owners.id (NOT users.id)
        // Must look up Owner first to get the correct users.id for the wallet
        $ownerRecord = \App\Models\Owner::find($firstProduct->owner_user_id);
        if (!$ownerRecord) {
            throw new \Exception("Cannot process commission: owner record not found for order #{$order->id} (owner_user_id={$firstProduct->owner_user_id})");
        }
        $ownerUserId = $ownerRecord->user_id; // this is the correct users.id

        // Calculate commission
        $grossAmount = (float) $order->transaction_amount;
        $rate        = $this->effectiveRate($firstProduct, $ownerUserId);
        $breakdown   = $this->calculate($grossAmount, $rate);

        // Get or create wallet
        $wallet = OwnerWallet::forUser($ownerUserId);

        // Credit wallet
        $wallet->increment('balance', $breakdown['net_amount']);

        // Log transaction
        $walletTransaction = WalletTransaction::create([
            'owner_wallet_id'    => $wallet->id,
            'product_order_id'   => $order->id,
            'invoice_order_id'   => null,
            'transaction_source' => 'marketplace',
            'gross_amount'       => $grossAmount,
            'commission_rate'    => $breakdown['commission_rate'],
            'commission_amount'  => $breakdown['commission_amount'],
            'net_amount'         => $breakdown['net_amount'],
            'type'               => 'credit',
            'description'        => "Marketplace sale — Order #{$order->order_id}",
        ]);

        // ── Affiliate commission (a share of OUR commission, like rent) ──
        try {
            app(\App\Services\AffiliateCommissionService::class)
                ->handleMarketplaceCommission($order, $breakdown['commission_amount']);
        } catch (\Exception $e) {
            Log::error('Affiliate marketplace commission failed', [
                'order_id' => $order->id,
                'error'    => $e->getMessage(),
            ]);
        }
        return $walletTransaction;
        });
    }

    /**
     * Reverse a marketplace sale's money when a refund is confirmed — the counter-entry to
     * processOrderCommission. Debits the owner's wallet by the net they were credited and books a
     * matching affiliate reversal. Idempotent (won't double-reverse). If the owner (or affiliate)
     * has already WITHDRAWN those proceeds, the balance simply goes negative — a carried-forward
     * clawback recovered from their future earnings; we never force-claw already-paid-out cash,
     * but the books reconcile. Returns null if there was nothing to reverse.
     */
    public function reverseOrderCommission(ProductOrder $order): ?WalletTransaction
    {
        return DB::transaction(function () use ($order) {
            $original = WalletTransaction::where('product_order_id', $order->id)
                ->where('type', 'credit')
                ->where('transaction_source', 'marketplace')
                ->first();
            if (! $original) {
                return null; // never credited (e.g. unpaid) — nothing to reverse
            }

            // Idempotency: a reversal already exists for this order.
            $alreadyReversed = WalletTransaction::where('product_order_id', $order->id)
                ->where('type', 'refund')
                ->exists();
            if ($alreadyReversed) {
                return null;
            }

            $wallet = OwnerWallet::find($original->owner_wallet_id);
            if ($wallet) {
                // May drive the balance negative if already withdrawn — recovered from future credits.
                $wallet->decrement('balance', $original->net_amount);
            }

            // Store the counter-entry with NEGATIVE amounts so every report that SUMs
            // wallet_transactions untyped (platform commission, GMV, per-owner commission) nets to
            // zero for a refunded sale — otherwise a positive reversal row would DOUBLE-count our
            // commission + GMV instead of reversing them.
            $reversal = WalletTransaction::create([
                'owner_wallet_id'    => $original->owner_wallet_id,
                'product_order_id'   => $order->id,
                'invoice_order_id'   => null,
                'transaction_source' => 'marketplace',
                'gross_amount'       => -1 * abs((float) $original->gross_amount),
                'commission_rate'    => $original->commission_rate,
                'commission_amount'  => -1 * abs((float) $original->commission_amount),
                'net_amount'         => -1 * abs((float) $original->net_amount),
                'type'               => 'refund',
                'description'        => "Refund reversal — Order #{$order->order_id}",
            ]);

            // Reverse the affiliate's cut too (secondary — never block the owner reversal on it).
            try {
                app(\App\Services\AffiliateCommissionService::class)->reverseMarketplaceCommission($order);
            } catch (\Throwable $e) {
                Log::error('Affiliate marketplace reversal failed', ['order_id' => $order->id, 'error' => $e->getMessage()]);
            }

            return $reversal;
        });
    }

    // ──────────────────────────────────────────────────────────
    // MARKETPLACE REFUNDS (escrow-aware, admin-green-lit B2C to the buyer)
    // ──────────────────────────────────────────────────────────

    /**
     * A refund is requested (buyer wants their money back, or the owner can't fulfil). This only
     * QUEUES it for admin approval — money never moves without an admin green-light. Idempotent.
     */
    public function requestRefund(ProductOrder $order): void
    {
        if (in_array($order->refund_status, [REFUND_STATUS_PROCESSING, REFUND_STATUS_REFUNDED], true)) {
            return; // already in-flight or done
        }
        $order->forceFill([
            'payment_status' => PRODUCT_ORDER_STATUS_REFUND_PENDING,
            'refund_status'  => REFUND_STATUS_REQUESTED,
        ])->save();
    }

    /**
     * ADMIN green-lights the refund → send the buyer their money back via M-Pesa B2C. The send is
     * fired OUTSIDE any transaction (an accepted request must survive) and the order is left in
     * `processing` until the async B2C ResultURL confirms (handleRefundResult). Retryable from a
     * previously FAILED state. Returns ['ok'=>bool, 'message'=>string].
     */
    public function approveAndSendRefund(ProductOrder $order): array
    {
        if (! in_array($order->refund_status, [REFUND_STATUS_REQUESTED, REFUND_STATUS_FAILED], true)) {
            return ['ok' => false, 'message' => __('This refund is not awaiting approval.')];
        }

        $buyer = \App\Models\User::find($order->user_id);
        $phone = $buyer?->contact_number;
        if (empty($phone)) {
            return ['ok' => false, 'message' => __('The buyer has no M-Pesa phone number on file to refund to.')];
        }

        $amount = (float) $order->transaction_amount; // full gross the buyer paid
        if ($amount <= 0) {
            return ['ok' => false, 'message' => __('Nothing to refund on this order.')];
        }

        $result = app(\App\Services\Payment\MpesaB2CService::class)
            ->send($phone, $amount, 'Marketplace refund #' . $order->order_id, 'MarketplaceRefund');

        if (! ($result['success'] ?? false)) {
            $order->forceFill(['refund_status' => REFUND_STATUS_FAILED])->save();
            return ['ok' => false, 'message' => $result['message'] ?? __('The refund payout could not be initiated.')];
        }

        $order->forceFill([
            'refund_status'    => REFUND_STATUS_PROCESSING,
            'refund_reference' => $result['reference'] ?? null,
            'refund_amount'    => $amount,
        ])->save();

        return ['ok' => true, 'message' => __('Refund payout initiated — awaiting M-Pesa confirmation.')];
    }

    /**
     * Async B2C ResultURL outcome for a refund. Idempotent — only a `processing` refund transitions,
     * so a re-fired Safaricom callback is a no-op. On success we finalize (reverse the owner ledger
     * only if the proceeds were already RELEASED — a held order was never credited, so nothing to
     * claw back); on failure we mark FAILED for admin retry.
     */
    public function handleRefundResult(ProductOrder $order, bool $success, ?string $receipt = null): void
    {
        if ($order->refund_status !== REFUND_STATUS_PROCESSING) {
            return;
        }

        if (! $success) {
            $order->forceFill(['refund_status' => REFUND_STATUS_FAILED])->save();
            return;
        }

        // Reverse the owner/platform/affiliate ledger ONLY if proceeds were released (or a legacy
        // order was credited under the old immediate model). A held order was never credited.
        if ($order->settlement_status === SETTLEMENT_STATUS_RELEASED || $this->alreadyCredited($order)) {
            try {
                $this->reverseOrderCommission($order);
            } catch (\Throwable $e) {
                Log::error('Refund ledger reversal failed', ['order_id' => $order->id, 'error' => $e->getMessage()]);
            }
        }

        $order->forceFill([
            'refund_status'     => REFUND_STATUS_REFUNDED,
            'settlement_status' => SETTLEMENT_STATUS_REFUNDED,
            'payment_status'    => PRODUCT_ORDER_STATUS_CANCELLED,
            'order_status'      => ORDER_STATUS_CANCELLED,
            'refund_reference'  => $receipt ?: $order->refund_reference,
            'refunded_at'       => now(),
        ])->save();

        // Tell the buyer the money has actually landed.
        try {
            \App\Jobs\SendOrderStatusNotificationJob::dispatch(
                $order,
                (object) [
                    'subject' => __('Refund completed for order #:id', ['id' => $order->order_id]),
                    'title'   => __('Refund completed'),
                    'message' => __('Your refund for order #:id has been sent to your M-Pesa. Thank you.', ['id' => $order->order_id]),
                ],
                (object) [
                    'title' => __('Refund completed'),
                    'body'  => __('Your refund for order #:id has been sent to your M-Pesa.', ['id' => $order->order_id]),
                    'url'   => route('tenant.order.index'),
                ],
            );
        } catch (\Throwable $e) {
            Log::error('Refund completion notification failed', ['order_id' => $order->id, 'error' => $e->getMessage()]);
        }
    }

    // ──────────────────────────────────────────────────────────
    // RENT
    // ──────────────────────────────────────────────────────────

    /**
     * Process commission for a completed rent payment.
     *
     * Owner resolution: invoices.owner_user_id = users.id (directly)
     * No Owner model lookup needed — use the value directly with OwnerWallet::forUser().
     *
     * Idempotent — safe to call multiple times, will not double-process.
     */
    public function processRentCommission(Order $order): WalletTransaction
    {
        // Resolve invoice using order->invoice_id (reliable)
        $invoice = $order->invoice ?? \App\Models\Invoice::find($order->invoice_id);
        if (!$invoice) {
            throw new \Exception("Cannot process rent commission: invoice not found on order #{$order->id}");
        }

        // invoices.owner_user_id stores users.id directly (confirmed by DB inspection)
        // No Owner::find() needed — use directly with OwnerWallet::forUser()
        $ownerUserId = $invoice->owner_user_id;
        if (!\App\Models\User::where('id', $ownerUserId)->exists()) {
            throw new \Exception("Cannot process rent commission: owner user {$ownerUserId} not found for order #{$order->id}");
        }

        // Flat 1% rent commission. This method is reached ONLY for transaction-
        // mode owners: every call site gates on ownerIsTransactionModel($invoice)
        // because only then does rent route to the Centresidence M-Pesa account
        // where the fee is levied. The gate lives at checkout (the routing
        // decision), NOT here — do not re-check the owner's current mode, which
        // can change between checkout and the payment callback after the money
        // has already landed in the company account.
        $grossAmount      = (float) $order->transaction_amount;
        $rate             = self::RENT_COMMISSION_RATE;
        // The 1% applies to the RENT portion only. In transaction mode every tenant
        // payment routes to the company account, but late fees, deposits and other
        // charges are not commissionable — only rent is. A pure non-rent payment
        // therefore carries zero commission and is credited to the owner in full.
        $rentPortion      = min((float) $invoice->rentPortion(), $grossAmount);
        $commissionAmount = round($rentPortion * ($rate / 100), 2);
        $netAmount        = round($grossAmount - $commissionAmount, 2);

        // Get or create wallet
        $wallet = OwnerWallet::forUser($ownerUserId);

        // Credit wallet
        $wallet->increment('balance', $netAmount);

        // Log transaction
       $walletTransaction = WalletTransaction::create([
            'owner_wallet_id'    => $wallet->id,
            'product_order_id'   => null,
            'invoice_order_id'   => $order->id,
            'transaction_source' => 'rent',
            'gross_amount'       => $grossAmount,
            'commission_rate'    => $rate,
            'commission_amount'  => $commissionAmount,
            'net_amount'         => $netAmount,
            'type'               => 'credit',
            'description'        => "payment — Invoice #{$invoice->invoice_no}",
        ]);

        // ── Affiliate commission (15% of our 1%) ───────────────────
        try {
            app(\App\Services\AffiliateCommissionService::class)->handleRentCommission($order);
        } catch (\Exception $e) {
            Log::error('Affiliate rent commission failed', [
                'order_id' => $order->id,
                'error'    => $e->getMessage(),
            ]);
        }
        return $walletTransaction;
    }

    // ──────────────────────────────────────────────────────────
    // SHARED HELPERS
    // ──────────────────────────────────────────────────────────

    /**
     * Preview commission for display in UI (no DB writes).
     */
    public function preview(float $price, float $baseCommission, int $ownerUserId): array
    {
        $package  = $this->ownerPackage($ownerUserId);
        $markup   = $package?->commission_markup   ?? 3.0;
        $discount = $package?->commission_discount ?? 0.0;

        $effective        = max($baseCommission + $markup - $discount, self::MINIMUM_COMMISSION);
        $commissionAmount = round($price * ($effective / 100), 2);
        $netAmount        = round($price - $commissionAmount, 2);

        return [
            'base_commission'   => $baseCommission,
            'markup'            => $markup,
            'discount'          => $discount,
            'effective_rate'    => $effective,
            'commission_amount' => $commissionAmount,
            'net_amount'        => $netAmount,
            'price'             => $price,
        ];
    }

    /**
     * Get the active package for an owner user.
     * Falls back to the default/free package if none found.
     */
    private function ownerPackage(int $ownerUserId): ?Package
    {
        $subscription = DB::table('owner_packages')
            ->where('user_id', $ownerUserId)
            ->where('status', 1)
            ->latest()
            ->first();

        if ($subscription) {
            return Package::find($subscription->package_id);
        }

        return Package::where('is_default', 1)->first();
    }
}