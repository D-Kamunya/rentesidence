<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Services\TenantProductOrderService;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use App\Models\ProductOrder;
use App\Models\Owner;
use App\Jobs\SendOrderStatusNotificationJob;

class ProductOrderController extends Controller
{
    use ResponseTrait;

    public $productOrderService;

    public function __construct()
    {
        $this->productOrderService = new TenantProductOrderService();
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            return $this->productOrderService->getAllProductOrdersData($request);
        }
    
        $responseData = $this->productOrderService->getAllProductOrders($request);
        return view('tenant.products.order.index')->with($responseData);
    }

    public function paidProductOrdersIndex(Request $request)
    {
        if ($request->ajax()) {
            return $this->productOrderService->getPaidProductOrdersData($request);
        }
    }

    public function pendingProductOrdersIndex(Request $request)
    {
        if ($request->ajax()) {
            return $this->productOrderService->getPendingProductOrdersData($request);
        }
    }

    public function bankPendingProductOrders(Request $request)
    {
        if ($request->ajax()) {
            return $this->productOrderService->getBankPendingProductOrdersData($request);
        }
    }

    public function cancel(Request $request, $id)
    {
        $order = ProductOrder::where('user_id', auth()->id())->findOrFail($id);

        // A tenant can self-cancel only while the order is still open, unpaid-or-paid, and NOT yet
        // dispatched. Once it's on its way, cancellation must go through the owner/caretaker.
        $cancellable = in_array($order->payment_status, [ORDER_PAYMENT_STATUS_PENDING, ORDER_PAYMENT_STATUS_PAID])
            && $order->order_status !== ORDER_STATUS_COMPLETED
            && $order->order_status !== ORDER_STATUS_CANCELLED
            && (int) $order->fulfilment_status < FULFILMENT_DISPATCHED;

        if (! $cancellable) {
            $message = (int) $order->fulfilment_status >= FULFILMENT_DISPATCHED
                ? __('This order is already on its way and can no longer be cancelled here. Please contact your property manager to arrange it.')
                : __('This order can no longer be cancelled.');

            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $message], 422);
            }
            return redirect()->back()->with('error', $message);
        }

        if ($order->payment_status === ORDER_PAYMENT_STATUS_PAID) {
            // Paid → queue a refund for admin green-light (the B2C payout moves on approval).
            app(\App\Services\CommissionService::class)->requestRefund($order);
        } else {
            // Unpaid — cancel cleanly, nothing to refund.
            $order->payment_status = PRODUCT_ORDER_STATUS_CANCELLED;
        }

        $order->save();
    
        // Notify owner that tenant has cancelled
        $emailData = (object) [
            'subject' => __('Order #') . $order->order_id . __(' has been cancelled by the tenant'),
            'title'   => __('Order Cancellation'),
            'message' => $order->payment_status === PRODUCT_ORDER_STATUS_REFUND_PENDING
                ? __('Order #') . $order->order_id . __(' was cancelled after payment. A refund is pending your action.')
                : __('Order #') . $order->order_id . __(' has been cancelled by the tenant.'),
        ];
        // Notify the owner (resolve owner user from order items)
        $ownerId = $order->orderItems->first()?->product?->owner_user_id;
        if ($ownerId) {
            $ownerUser = \App\Models\Owner::find($ownerId)?->user;
            if ($ownerUser) {
                $notificationData = (object) [
                    'title' => __('Order Cancelled by Tenant'),
                    'body'  => $emailData->message,
                    'url'   => route('owner.order.index'),
                ];
                // Re-use the job but send to owner instead of tenant
                SendOrderStatusNotificationJob::dispatchToUser(
                    $ownerUser, $order, $emailData, $notificationData
                );
            }
        }
    
        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'refund_pending' => $order->payment_status === PRODUCT_ORDER_STATUS_REFUND_PENDING,
                'message' => $order->payment_status === PRODUCT_ORDER_STATUS_REFUND_PENDING
                    ? __('Order cancelled. A refund request has been sent to the owner.')
                    : __('Order cancelled successfully.'),
            ]);
        }
    
        return redirect()->back()->with('success', __('Order cancelled.'));
    }

    /**
     * Buyer-initiated refund request — for a PAID order that's already on its way or delivered (so
     * self-cancel no longer applies). Queues it for admin green-light; the owner is notified. Does
     * NOT move money — an admin approves the B2C payout.
     */
    public function requestRefund(Request $request, $id)
    {
        $order = ProductOrder::where('user_id', auth()->id())->findOrFail($id);

        $refundable = $order->payment_status === ORDER_PAYMENT_STATUS_PAID
            && ! in_array($order->refund_status, [REFUND_STATUS_PROCESSING, REFUND_STATUS_REFUNDED], true);

        if (! $refundable) {
            $message = __('This order can\'t be refunded right now.');
            return $request->wantsJson()
                ? response()->json(['success' => false, 'message' => $message], 422)
                : redirect()->back()->with('error', $message);
        }

        app(\App\Services\CommissionService::class)->requestRefund($order);

        // Let the owner know a buyer wants a refund.
        SendOrderStatusNotificationJob::dispatch(
            $order,
            (object) [
                'subject' => __('Refund requested for order #:id', ['id' => $order->order_id]),
                'title'   => __('Refund requested'),
                'message' => __('The buyer has requested a refund for order #:id. It is queued for review.', ['id' => $order->order_id]),
            ],
            (object) [
                'title' => __('Refund requested'),
                'body'  => __('A refund was requested for order #:id.', ['id' => $order->order_id]),
                'url'   => route('owner.order.index'),
            ],
        );

        $message = __('Your refund request has been submitted and is being reviewed.');
        return $request->wantsJson()
            ? response()->json(['success' => true, 'message' => $message])
            : redirect()->back()->with('success', $message);
    }
}