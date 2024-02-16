<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Services\GatewayService;
use App\Services\InvoiceService;
use App\Services\TenantService;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    use ResponseTrait;
    public $invoiceService;
    public $tenantService;
    public $gatewayService;

    public function __construct()
    {
        $this->invoiceService = new InvoiceService;
        $this->tenantService = new TenantService();
        $this->gatewayService = new GatewayService;
    }
    public function index()
    {
        $data['pageTitle'] = __('Invoices');
        $data['invoices'] = $this->invoiceService->getByTenantId(auth()->user()->tenant->id);
        return view('tenant.invoices.index', $data);
    }

    public function details($id)
    {
        $data['invoice'] = $this->invoiceService->getById($id);
        $data['items'] = $this->invoiceService->getItemsByInvoiceId($id);
        $data['owner'] = $this->invoiceService->ownerInfo(auth()->user()->owner_user_id);
        $data['tenant'] = $this->tenantService->getDetailsById($data['invoice']->tenant_id);
        $data['order'] = $this->invoiceService->getOrderById($data['invoice']->order_id);
        return view('tenant.invoices.print', $data);
    }

    public function pay($id)
    {
        $data['pageTitle'] = __('Invoices Pay');
        $data['navInvoiceMMActiveClass'] = 'mm-active';
        $data['navInvoiceActiveClass'] = 'active';
        $data['invoice'] = $this->invoiceService->getByIdCheckTenantAuthId($id);
        $data['gateways'] = $this->gatewayService->getActiveAll(auth()->user()->owner_user_id);
        $data['banks'] = $this->gatewayService->getActiveBanks();
        return view('tenant.invoices.pay', $data);
    }

    public function getCurrencyByGateway(Request $request)
    {
        $data = $this->invoiceService->getCurrencyByGatewayId($request->id);
        return $this->success($data);
    }
}
