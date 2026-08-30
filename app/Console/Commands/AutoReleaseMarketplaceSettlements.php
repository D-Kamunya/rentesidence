<?php

namespace App\Console\Commands;

use App\Models\ProductOrder;
use App\Services\CommissionService;
use Illuminate\Console\Command;

/**
 * Escrow safety net: release held marketplace proceeds to the owner after an acceptance window,
 * so money never gets stuck if an order is paid but never explicitly marked delivered. Mirrors
 * the "auto-confirm receipt" that mature marketplaces use. A held order with a pending/failed
 * refund is skipped (the refund path owns it). Idempotent — releaseSettlement won't double-credit.
 */
class AutoReleaseMarketplaceSettlements extends Command
{
    protected $signature = 'marketplace:auto-release-settlements';

    protected $description = 'Release held marketplace proceeds to owners after the acceptance window.';

    public function handle(CommissionService $commissions): int
    {
        $windowDays = (int) getOption('marketplace_return_window_days', 2);
        $graceDays  = (int) getOption('marketplace_auto_release_days', 7);

        // (1) DELIVERED orders whose return window has closed → pay the owner (refunds can no
        //     longer be requested, so there's nothing left to claw back).
        $delivered = ProductOrder::where('settlement_status', SETTLEMENT_STATUS_HELD)
            ->where('payment_status', ORDER_PAYMENT_STATUS_PAID)
            ->where('order_status', ORDER_STATUS_COMPLETED)
            ->whereNull('refund_status')
            ->whereNotNull('delivered_at')
            ->where('delivered_at', '<=', now()->subDays(max(1, $windowDays)))
            ->limit(500)
            ->get();

        // (2) SAFETY NET: paid but never marked delivered for a long time → release anyway so money
        //     never gets stuck in escrow (a longer grace than the return window).
        $stuck = ProductOrder::where('settlement_status', SETTLEMENT_STATUS_HELD)
            ->where('payment_status', ORDER_PAYMENT_STATUS_PAID)
            ->where('order_status', ORDER_STATUS_PENDING)
            ->whereNull('refund_status')
            ->where('updated_at', '<=', now()->subDays(max(1, $graceDays)))
            ->limit(500)
            ->get();

        $released = 0;
        foreach ($delivered->merge($stuck) as $order) {
            try {
                $order->loadMissing('orderItems.product');
                $commissions->releaseSettlement($order);
                $released++;
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('Auto-release failed', ['order_id' => $order->id, 'error' => $e->getMessage()]);
            }
        }

        $this->info("Auto-released {$released} held marketplace settlement(s) — window {$windowDays}d after delivery / {$graceDays}d grace if undelivered.");
        return self::SUCCESS;
    }
}
