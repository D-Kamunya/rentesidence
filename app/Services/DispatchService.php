<?php

namespace App\Services;

use App\Jobs\SendOrderStatusNotificationJob;
use App\Models\ProductOrder;
use Illuminate\Support\Facades\Log;

/**
 * Marketplace fulfilment transitions (dispatch). A paid order goes NONE → DISPATCHED → DELIVERED;
 * delivery completes the order. Handled by the on-site caretaker (maintainer) or the owner.
 * Idempotent transitions + a tenant notification on each step. Never changes payment_status.
 */
class DispatchService
{
    public function markDispatched(ProductOrder $order, ?int $byUserId = null): array
    {
        if ((int) $order->payment_status !== PRODUCT_ORDER_STATUS_PAID) {
            return ['ok' => false, 'message' => __('Only a paid order can be dispatched.')];
        }
        if ((int) $order->fulfilment_status >= FULFILMENT_DISPATCHED) {
            return ['ok' => false, 'message' => __('This order is already dispatched.')];
        }

        $order->fulfilment_status = FULFILMENT_DISPATCHED;
        $order->dispatched_at     = now();
        $order->dispatched_by     = $byUserId;
        $order->save();

        $this->notifyTenant(
            $order,
            __('Your order #:id is on the way', ['id' => $order->order_id]),
            __('Order dispatched'),
            __('Your order #:id has been dispatched and is on its way to you.', ['id' => $order->order_id]),
            __('Order #:id is on the way.', ['id' => $order->order_id])
        );

        return ['ok' => true, 'message' => __('Order marked as dispatched.')];
    }

    public function markDelivered(ProductOrder $order, ?int $byUserId = null): array
    {
        if ((int) $order->fulfilment_status < FULFILMENT_DISPATCHED) {
            return ['ok' => false, 'message' => __('Dispatch the order before marking it delivered.')];
        }
        if ((int) $order->fulfilment_status >= FULFILMENT_DELIVERED) {
            return ['ok' => false, 'message' => __('This order is already delivered.')];
        }

        $order->fulfilment_status = FULFILMENT_DELIVERED;
        $order->delivered_at      = now();
        $order->dispatched_by     = $order->dispatched_by ?: $byUserId;
        $order->order_status      = ORDER_STATUS_COMPLETED; // delivery completes the order
        $order->save();

        // Escrow: delivery releases the held proceeds to the owner (credit + commission).
        try {
            app(\App\Services\CommissionService::class)->releaseOnDelivery($order);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Marketplace release on delivery failed', ['order_id' => $order->id, 'error' => $e->getMessage()]);
        }

        $this->notifyTenant(
            $order,
            __('Order #:id delivered', ['id' => $order->order_id]),
            __('Order delivered'),
            __('Your order #:id has been delivered. Thank you!', ['id' => $order->order_id]),
            __('Order #:id delivered.', ['id' => $order->order_id])
        );

        return ['ok' => true, 'message' => __('Order marked as delivered.')];
    }

    private function notifyTenant(ProductOrder $order, string $subject, string $title, string $message, string $body): void
    {
        try {
            $emailData        = (object) ['subject' => $subject, 'title' => $title, 'message' => $message];
            $notificationData = (object) ['title' => $title, 'body' => $body, 'url' => route('tenant.order.index')];
            SendOrderStatusNotificationJob::dispatch($order, $emailData, $notificationData);
        } catch (\Throwable $e) {
            Log::error('Dispatch notification failed', ['order_id' => $order->id, 'error' => $e->getMessage()]);
        }
    }
}
