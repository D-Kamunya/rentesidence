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
        $order = ProductOrder::where('user_id', auth()->id())
            ->whereIn('payment_status', [ORDER_PAYMENT_STATUS_PENDING, ORDER_PAYMENT_STATUS_PAID])
            ->where('order_status', '!=', ORDER_STATUS_COMPLETED)
            ->where('order_status', '!=', ORDER_STATUS_CANCELLED)
            ->findOrFail($id);
    
        if ($order->payment_status === ORDER_PAYMENT_STATUS_PAID) {
            // Money already moved — flag for refund
            $order->payment_status = PRODUCT_ORDER_STATUS_REFUND_PENDING;
        } else {
            // Unpaid — cancel cleanly
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
}