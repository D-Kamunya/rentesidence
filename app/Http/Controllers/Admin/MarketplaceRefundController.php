<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductOrder;
use App\Services\CommissionService;

/**
 * Admin green-light for marketplace refunds. Marketplace money is held by the platform, so the
 * B2C payout back to the buyer is gated behind an admin approval here (security). The actual
 * money movement + ledger reversal happen in CommissionService (async, M-Pesa-confirmed).
 */
class MarketplaceRefundController extends Controller
{
    public function index()
    {
        $refunds = ProductOrder::with(['user', 'orderItems.product'])
            ->whereIn('refund_status', [REFUND_STATUS_REQUESTED, REFUND_STATUS_PROCESSING, REFUND_STATUS_FAILED])
            ->orderByRaw("FIELD(refund_status, '" . REFUND_STATUS_REQUESTED . "', '" . REFUND_STATUS_FAILED . "', '" . REFUND_STATUS_PROCESSING . "')")
            ->latest()
            ->paginate(20);

        return view('admin.marketplace.refunds', [
            'pageTitle' => __('Marketplace Refunds'),
            'refunds'   => $refunds,
        ]);
    }

    /** Green-light a requested (or retry a failed) refund → fire the B2C payout to the buyer. */
    public function approve($id, CommissionService $commissions)
    {
        $order = ProductOrder::findOrFail($id);

        $result = $commissions->approveAndSendRefund($order);

        return back()->with($result['ok'] ? 'success' : 'error', $result['message']);
    }

    /**
     * Decline a REQUESTED refund — for a mistaken/unwarranted request. Clears the refund flag so
     * the order returns to its normal held/completed state (the buyer can still confirm receipt or
     * the window auto-releases). Only a requested refund can be declined; once a payout is in
     * flight (processing) it can't be. The buyer is told.
     */
    public function decline($id)
    {
        $order = ProductOrder::findOrFail($id);

        if ($order->refund_status !== REFUND_STATUS_REQUESTED) {
            return back()->with('error', __('Only a refund still awaiting review can be declined.'));
        }

        $order->forceFill(['refund_status' => null])->save();

        try {
            \App\Jobs\SendOrderStatusNotificationJob::dispatch(
                $order,
                (object) [
                    'subject' => __('Refund request declined — order #:id', ['id' => $order->order_id]),
                    'title'   => __('Refund request declined'),
                    'message' => __('Your refund request for order #:id was reviewed and declined. If you have received the order in good order, please confirm receipt; otherwise reach us via Help & Support.', ['id' => $order->order_id]),
                ],
                (object) [
                    'title' => __('Refund request declined'),
                    'body'  => __('Your refund request for order #:id was declined.', ['id' => $order->order_id]),
                    'url'   => route('tenant.order.index'),
                ],
            );
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Refund decline notify failed: ' . $e->getMessage(), ['order_id' => $order->id]);
        }

        return back()->with('success', __('Refund request declined. The order returns to its normal state.'));
    }
}
