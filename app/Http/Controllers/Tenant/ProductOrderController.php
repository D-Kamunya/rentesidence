<?php

namespace App\Http\Controllers\Tenant;


use App\Http\Controllers\Controller;
use App\Services\ProductOrderService;
use App\Traits\ResponseTrait;
use Exception;
use Illuminate\Http\Request;

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
        else {
            $responseData  = $this->productOrderService->getAllProductOrders();
            return view('tenant.products.order.index')->with($responseData);
        }
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
