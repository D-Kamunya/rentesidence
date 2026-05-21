<?php

namespace App\Http\Controllers\Owner;


use App\Http\Controllers\Controller;
use App\Services\ProductOrderService;
use App\Traits\ResponseTrait;
use Exception;
use Illuminate\Http\Request;
use App\Jobs\SendOrderStatusNotificationJob;
use App\Models\Owner;
use App\Models\ProductOrder;

class ProductOrderController extends Controller
{
    use ResponseTrait;
    public $productOrderService;

    public function __construct()
    {
        $this->productOrderService = new ProductOrderService();
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            return $this->productOrderService->getAllProductOrdersData($request);
        }
    
        $responseData = $this->productOrderService->getAllProductOrders($request);
        return view('owner.products.order.index')->with($responseData);
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

    // public function bankPendingProductOrders(Request $request)
    // {
    //     if ($request->ajax()) {
    //         return $this->productOrderService->getBankPendingProductOrdersData($request);
    //     }
    // }

    public function markComplete(Request $request, $id)
    {
        // Scope to orders belonging to this owner only
        $ownerId = Owner::where('user_id', auth()->id())->value('id');
    
        $order = ProductOrder::whereHas('orderItems.product', function ($q) use ($ownerId) {
            $q->where('owner_user_id', $ownerId);
        })->findOrFail($id);
    
        if ($order->order_status === ORDER_STATUS_COMPLETED) {
            return $this->error([], __('Order is already completed.'));
        }
    
        $order->order_status = ORDER_STATUS_COMPLETED;
        $order->payment_status = ORDER_PAYMENT_STATUS_PAID;
        $order->save();
    
        // ── Dispatch notification to tenant ──────────────────────────
        $emailData = (object) [
            'subject' => __('Your order #') . $order->order_id . __(' has been completed'),
            'title'   => __('Order Completed'),
            'message' => __('Your order #') . $order->order_id . __(' has been marked as completed by the owner. Your product is on its way or ready for collection.'),
        ];
    
        $notificationData = (object) [
            'title' => __('Order Completed'),
            'body'  => __('Order #') . $order->order_id . __(' has been completed.'),
            'url'   => route('tenant.order.index'),
        ];
    
        SendOrderStatusNotificationJob::dispatch($order, $emailData, $notificationData);
    
        return $this->success([], __('Order marked as completed and tenant notified.'));
    }

    public function cancel(Request $request, $id)
    {
        $ownerId = Owner::where('user_id', auth()->id())->value('id');
    
        $order = ProductOrder::whereHas('orderItems.product', function ($q) use ($ownerId) {
                $q->where('owner_user_id', $ownerId);
            })
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
        $order->order_status = ORDER_STATUS_CANCELLED;
        
        $order->save();
    
        $emailData = (object) [
            'subject' => __('Your order #') . $order->order_id . __(' has been cancelled'),
            'title'   => __('Order Cancelled'),
            'message' => __('Your order #') . $order->order_id . __(' has been cancelled by the owner.'),
        ];
        $notificationData = (object) [
            'title' => __('Order Cancelled'),
            'body'  => __('Order #') . $order->order_id . __(' has been cancelled.'),
            'url'   => route('tenant.order.index'),
        ];
        SendOrderStatusNotificationJob::dispatch($order, $emailData, $notificationData);
    
        return $this->success([], __('Order cancelled and tenant notified.'));
    }

    public function confirmRefund(Request $request, $id)
    {
        $ownerId = Owner::where('user_id', auth()->id())->value('id');
    
        $order = ProductOrder::whereHas('orderItems.product', function ($q) use ($ownerId) {
                $q->where('owner_user_id', $ownerId);
            })
            ->where('payment_status', PRODUCT_ORDER_STATUS_REFUND_PENDING)
            ->findOrFail($id);
    
        $order->payment_status = PRODUCT_ORDER_STATUS_CANCELLED; // refund issued — close the loop
        $order->order_status   = ORDER_STATUS_CANCELLED;
        $order->save();
    
        $emailData = (object) [
            'subject' => __('Refund confirmed for order #') . $order->order_id,
            'title'   => __('Refund Confirmed'),
            'message' => __('The owner has confirmed your refund for order #') . $order->order_id . __('. Please allow 3–5 business days for funds to reflect.'),
        ];
        $notificationData = (object) [
            'title' => __('Refund Confirmed'),
            'body'  => __('Your refund for order #') . $order->order_id . __(' has been confirmed.'),
            'url'   => route('tenant.order.index'),
        ];
        SendOrderStatusNotificationJob::dispatch($order, $emailData, $notificationData);
    
        return $this->success([], __('Refund confirmed and tenant notified.'));
    }

    // public function overDueInvoiceIndex(Request $request)
    // {
    //     if ($request->ajax()) {
    //         return $this->invoiceService->getOverDueInvoicesData($request);
    //     }
    // }

    // public function details($id)
    // {
    //     $data['invoice'] = $this->invoiceService->getById($id);
    //     $data['items'] = $this->invoiceService->getItemsByInvoiceId($id);
    //     $data['owner'] = $this->invoiceService->ownerInfo(auth()->id());
    //     $data['tenant'] = $this->tenantService->getDetailsById($data['invoice']->tenant_id);
    //     $data['order'] = $this->invoiceService->getOrderById($data['invoice']->order_id);

    //     if ($data['owner'] && empty($data['owner']->print_name)) {
    //         $data['owner']->print_name = getOption('app_name');
    //     } 
    //     if ($data['owner'] && empty($data['owner']->print_address)) {
    //         $data['owner']->print_address= getOption('app_location');
    //     } 
    //     if ($data['owner'] && empty($data['owner']->print_contact)) {
    //         $data['owner']->print_contact = getOption('app_contact_number');
    //     } 
    //     return $this->success($data);
    // }

    // public function print($id)
    // {
    //     $data['invoice'] = $this->invoiceService->getById($id);
    //     $data['items'] = $this->invoiceService->getItemsByInvoiceId($id);
    //     $data['owner'] = $this->invoiceService->ownerInfo(auth()->id());
    //     $data['tenant'] = $this->tenantService->getDetailsById($data['invoice']->tenant_id);
    //     $data['order'] = $this->invoiceService->getOrderById($data['invoice']->order_id);
    //     return view('tenant.invoices.print', $data);
    // }

    // public function store(InvoiceRequest $request)
    // {
    //     return $this->invoiceService->store($request);
    // }

    // public function paymentStatus(PaymentStatusRequest $request)
    // {
    //     return $this->invoiceService->paymentStatusChange($request);
    // }

    // public function destroy($id)
    // {
    //     return $this->invoiceService->destroy($id);
    // }

    // public function types()
    // {
    //     $invoiceTypes = $this->invoiceService->types();
    //     return $this->success($invoiceTypes);
    // }

    // public function sendNotification(NotificationRequest $request)
    // {
    //     try {
    //         if ($request->notification_type == NOTIFICATION_TYPE_SINGLE) {
    //             return $this->invoiceService->sendSingleNotification($request);
    //         } elseif ($request->notification_type == NOTIFICATION_TYPE_MULTIPLE) {
    //             return $this->invoiceService->sendMultiNotification($request);
    //         }
    //     } catch (Exception $e) {
    //         return $this->error([]);
    //     }
    // }
}
