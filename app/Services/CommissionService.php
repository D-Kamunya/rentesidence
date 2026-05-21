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
     * Process commission for a completed product order.
     *
     * Owner resolution: products.owner_user_id = owners.id (primary key)
     * So we must do Owner::find() first to get the actual users.id.
     *
     * Idempotent — safe to call multiple times, will not double-process.
     */
    public function processOrderCommission(ProductOrder $order): WalletTransaction
    {
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

        // ── Affiliate commission (category rate) ───────────────────
        try {
            app(\App\Services\AffiliateCommissionService::class)->handleMarketplaceCommission($order);
        } catch (\Exception $e) {
            Log::error('Affiliate marketplace commission failed', [
                'order_id' => $order->id,
                'error'    => $e->getMessage(),
            ]);
        }
        return $walletTransaction;
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

        // Flat 1% rent commission
        $grossAmount      = (float) $order->transaction_amount;
        $rate             = self::RENT_COMMISSION_RATE;
        $commissionAmount = round($grossAmount * ($rate / 100), 2);
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