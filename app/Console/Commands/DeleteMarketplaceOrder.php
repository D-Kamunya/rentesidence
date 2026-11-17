<?php

namespace App\Console\Commands;

use App\Models\ProductOrder;
use Illuminate\Console\Command;

/**
 * Removes a marketplace order (and its line items) by order_id — for clearing TEST data so the admin
 * surfaces (e.g. the Refunds queue) show only real orders. Shows the record and asks to confirm first.
 * Soft-delete, so it's recoverable (ProductOrder uses SoftDeletes). It does NOT reverse any wallet /
 * commission ledger entries — it's a test-data cleanup, not a financial reversal — so use it only on
 * genuine test orders.
 *
 *   php artisan marketplace:delete-order ORDERIDTSRIIEVT
 */
class DeleteMarketplaceOrder extends Command
{
    protected $signature = 'marketplace:delete-order {order_id : The order_id to remove} {--force : Skip the confirmation prompt}';
    protected $description = 'Soft-delete a marketplace order (test-data cleanup) by order_id, with its line items.';

    public function handle(): int
    {
        $orderId = (string) $this->argument('order_id');

        $order = ProductOrder::with('user')->where('order_id', $orderId)->first();
        if (! $order) {
            $this->error("No marketplace order found with order_id \"{$orderId}\".");
            return self::FAILURE;
        }

        $buyer = $order->user
            ? trim(($order->user->first_name ?? '') . ' ' . ($order->user->last_name ?? '')) . ' (' . ($order->user->contact_number ?: $order->user->email) . ')'
            : '—';

        $this->table(['Field', 'Value'], [
            ['Order ID', $order->order_id],
            ['Buyer', $buyer],
            ['Amount', $order->transaction_amount],
            ['Payment status', $order->payment_status],
            ['Refund status', $order->refund_status ?: '—'],
            ['Created', (string) $order->created_at],
        ]);

        if (! $this->option('force') && ! $this->confirm('Soft-delete this order? (recoverable via restore; ledger entries are NOT reversed)')) {
            $this->warn('Aborted.');
            return self::SUCCESS;
        }

        // Soft-delete the order only — it disappears from the Refunds queue and every order list, and
        // its line items (which never show without their order) stay intact so a restore is clean.
        $order->delete();

        $this->info("Order \"{$orderId}\" removed. It will no longer appear in the Refunds queue or order lists.");
        return self::SUCCESS;
    }
}
