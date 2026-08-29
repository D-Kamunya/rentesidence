<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\InvoiceRequest;
use App\Http\Requests\PaymentStatusRequest;
use App\Http\Requests\NotificationRequest;
use App\Services\InvoiceService;
use App\Services\PropertyService;
use App\Services\TenantService;
use App\Traits\ResponseTrait;
use Exception;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    use ResponseTrait;
    public $invoiceService;
    public $tenantService;
    public $propertyService;

    public function __construct()
    {
        $this->invoiceService = new InvoiceService();
        $this->tenantService = new TenantService();
        $this->propertyService = new PropertyService;
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            return $this->invoiceService->getAllInvoicesData($request);
        }
        else {
            $responseData  = $this->invoiceService->getAllInvoices();
            $responseData['properties'] = $this->propertyService->getAll(false);
            $responseData['invoiceMonths'] = $this->invoiceService->getInvoiceMonths();
            return view('owner.invoice.index')->with($responseData);
        }
    }

    public function paidInvoiceIndex(Request $request)
    {
        if ($request->ajax()) {
            return $this->invoiceService->getPaidInvoicesData($request);
        }
    }

    public function pendingInvoiceIndex(Request $request)
    {
        if ($request->ajax()) {
            return $this->invoiceService->getPendingInvoicesData($request);
        }
    }

    public function bankPendingInvoice(Request $request)
    {
        if ($request->ajax()) {
            return $this->invoiceService->getBankPendingInvoicesData($request);
        }
    }

    public function overDueInvoiceIndex(Request $request)
    {
        if ($request->ajax()) {
            return $this->invoiceService->getOverDueInvoicesData($request);
        }
    }

    public function details($id)
    {
        $data['invoice'] = $this->invoiceService->getById($id);
        $data['items'] = $this->invoiceService->getItemsByInvoiceId($id);
        $data['owner'] = $this->invoiceService->ownerInfo(auth()->id());
        // Null-safe: a tenant that was removed / can't be resolved must not blow up
        // the whole details view (that left the loader hanging on such invoices).
        $data['tenant'] = $this->tenantDetailsSafe($data['invoice']->tenant_id);
        $data['order'] = $this->invoiceService->getOrderById($data['invoice']->order_id);

        // Effective print details (owner profile → platform fallback) are resolved centrally
        // in InvoiceService::ownerInfo(), so they're consistent across preview, print & orders.
        return $this->success($data);
    }

    public function print($id)
    {
        $data['invoice'] = $this->invoiceService->getById($id);
        $data['items'] = $this->invoiceService->getItemsByInvoiceId($id);
        $data['owner'] = $this->invoiceService->ownerInfo(auth()->id());
        $data['tenant'] = $this->tenantDetailsSafe($data['invoice']->tenant_id);
        $data['order'] = $this->invoiceService->getOrderById($data['invoice']->order_id);
        return view('tenant.invoices.print', $data);
    }

    /** Resolve a tenant's details without throwing when it can't be found. */
    private function tenantDetailsSafe($tenantId)
    {
        if (! $tenantId) {
            return null;
        }

        try {
            return $this->tenantService->getDetailsById($tenantId);
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function store(InvoiceRequest $request)
    {
        return $this->invoiceService->store($request);
    }

    public function paymentStatus(PaymentStatusRequest $request)
    {
        return $this->invoiceService->paymentStatusChange($request);
    }

    public function destroy($id)
    {
        return $this->invoiceService->destroy($id);
    }

    public function types()
    {
        $invoiceTypes = $this->invoiceService->types();
        return $this->success($invoiceTypes);
    }

    public function sendNotification(NotificationRequest $request)
    {
        try {
            if ($request->notification_type == NOTIFICATION_TYPE_SINGLE) {
                return $this->invoiceService->sendSingleNotification($request);
            } elseif ($request->notification_type == NOTIFICATION_TYPE_MULTIPLE) {
                return $this->invoiceService->sendMultiNotification($request);
            }
        } catch (Exception $e) {
            return $this->error([]);
        }
    }
}
