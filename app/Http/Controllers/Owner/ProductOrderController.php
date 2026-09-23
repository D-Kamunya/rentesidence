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

    /** Owner toggle: does the on-site caretaker (maintainer) handle marketplace dispatch, or does
     *  the owner organise it themselves? Surfaced on the shop dashboard. Default ON. */
    public function updateDispatchSetting(Request $request)
    {
        $enabled = $request->boolean('caretaker_dispatch_enabled');
        Owner::where('user_id', auth()->id())->update(['caretaker_dispatch_enabled' => $enabled]);

        return back()->with('success', $enabled
            ? __('Your caretaker will now handle dispatch for marketplace orders.')
            : __('You\'ll organise dispatch yourself — your caretaker won\'t see the dispatch queue.'));
    }

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
        $order->fulfilment_status = FULFILMENT_DELIVERED; // completing = delivered; keeps it out of the caretaker dispatch queue
        $order->delivered_at      = $order->delivered_at ?: now(); // starts the return window
        $order->save();

        // Escrow: completing (delivered) STARTS the return window — it does NOT release the money.
        // Payout happens when the buyer confirms receipt or the window closes (held funds cover an
        // in-window refund with no clawback).

        // ── Dispatch notification to tenant ──────────────────────────
        $emailData = (object) [
            'subject' => __('Your order #:id is complete — please confirm receipt', ['id' => $order->order_id]),
            'title'   => __('Order complete'),
            'message' => __('Your order #:id has been completed. Once you have your product in good order, please tap "Confirm receipt" so the seller can be paid.', ['id' => $order->order_id]),
        ];

        $notificationData = (object) [
            'title' => __('Order complete'),
            'body'  => __('Order #:id complete — tap Confirm receipt to close it out.', ['id' => $order->order_id]),
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
            // Paid → the buyer is owed a refund. Queue it for admin green-light (no money moves
            // yet); the actual B2C payout to the buyer happens on admin approval.
            app(\App\Services\CommissionService::class)->requestRefund($order);
        } else {
            // Unpaid — cancel cleanly, nothing to refund.
            $order->payment_status = PRODUCT_ORDER_STATUS_CANCELLED;
        }
        $order->order_status = ORDER_STATUS_CANCELLED;

        $order->save();
    
        $emailData = (object) [
            'subject' => __('Your order #:id has been cancelled', ['id' => $order->order_id]),
            'title'   => __('Order cancelled'),
            'message' => __('Your order #:id has been cancelled by the owner.', ['id' => $order->order_id]),
        ];
        $notificationData = (object) [
            'title' => __('Order cancelled'),
            'body'  => __('Order #:id has been cancelled.', ['id' => $order->order_id]),
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

        // The owner approves the refund → it enters the ADMIN queue for a green-lit B2C payout to
        // the buyer. Money moves only on admin approval + M-Pesa confirmation (no more manual "3–5
        // days", and the ledger is reversed at payout-confirm, not here). Idempotent.
        app(\App\Services\CommissionService::class)->requestRefund($order);

        $emailData = (object) [
            'subject' => __('Refund approved for order #:id', ['id' => $order->order_id]),
            'title'   => __('Refund approved'),
            'message' => __('Your refund for order #:id has been approved and is being processed. You will receive the money on your M-Pesa shortly.', ['id' => $order->order_id]),
        ];
        $notificationData = (object) [
            'title' => __('Refund approved'),
            'body'  => __('Your refund for order #:id is being processed.', ['id' => $order->order_id]),
            'url'   => route('tenant.order.index'),
        ];
        SendOrderStatusNotificationJob::dispatch($order, $emailData, $notificationData);

        return $this->success([], __('Refund approved and queued for payout to the buyer.'));
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
