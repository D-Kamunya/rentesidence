<?php

namespace App\Console\Commands;

use App\Models\ProductOrder;
use App\Services\CommissionService;
use Illuminate\Console\Command;

/**
 * Escrow safety net: release held marketplace proceeds to the owner after an acceptance window,
 * so money never gets stuck if an order is paid but never explicitly marked delivered. Mirrors
 * the "auto-confirm receipt" that mature marketplaces use. A held order with a pending/failed
 * refund is skipped (the refund path owns it). Idempotent — releaseOnDelivery won't double-credit.
 */
class AutoReleaseMarketplaceSettlements extends Command
{
    protected $signature = 'marketplace:auto-release-settlements';

    protected $description = 'Release held marketplace proceeds to owners after the acceptance window.';

    public function handle(CommissionService $commissions): int
    {
        $days   = (int) getOption('marketplace_auto_release_days', 7);
        $cutoff = now()->subDays(max(1, $days));

        $orders = ProductOrder::where('settlement_status', SETTLEMENT_STATUS_HELD)
            ->where('payment_status', ORDER_PAYMENT_STATUS_PAID)
            ->whereNull('refund_status') // never auto-release something with a refund in play
            ->where('updated_at', '<=', $cutoff)
            ->limit(500)
            ->get();

        $released = 0;
        foreach ($orders as $order) {
            try {
                $order->loadMissing('orderItems.product');
                $commissions->releaseOnDelivery($order);
                $released++;
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('Auto-release failed', ['order_id' => $order->id, 'error' => $e->getMessage()]);
            }
        }

        $this->info("Auto-released {$released} held marketplace settlement(s) older than {$days} day(s).");
        return self::SUCCESS;
    }
}
