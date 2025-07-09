<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\InvoiceRecurringRequest;
use App\Services\InvoiceRecurringService;
use App\Services\InvoiceTypeService;
use App\Services\PropertyService;
use App\Services\TenantService;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use App\Models\Tenant;

class InvoiceRecurringController extends Controller
{
    use ResponseTrait;
    public $invoiceRecurringService;
    public $propertyService;
    public $invoiceTypeService;
    public $tenantService;

    public function __construct()
    {
        $this->invoiceRecurringService = new InvoiceRecurringService;
        $this->propertyService = new PropertyService;
        $this->invoiceTypeService = new InvoiceTypeService;
        $this->tenantService = new TenantService();
    }

    public function index(Request $request)
    {
        $data['pageTitle'] = __('Recurring Invoice Setting');
        if ($request->ajax()) {
            return $this->invoiceRecurringService->getAllData($request);
        } else {
            $data['properties'] = $this->propertyService->getAll();
            $data['invoiceTypes'] = $this->invoiceTypeService->getAll();
            return view('owner.invoice.recurring', $data);
        }
    }

    public function store(InvoiceRecurringRequest $request)
    {
        return $this->invoiceRecurringService->store($request);
    }

    public function details($id)
    {
        $data['invoice'] = $this->invoiceRecurringService->getById($id);
        $data['items'] = $this->invoiceRecurringService->getItemsByInvoiceRecurringId($id);
        $data['tenant'] = Tenant::query()
                        ->leftJoin('users', 'tenants.user_id', '=', 'users.id')
                        ->leftJoin('tenant_details', 'tenants.id', '=', 'tenant_details.tenant_id')
                        ->leftJoin('properties', 'tenants.property_id', '=', 'properties.id')
                        ->leftJoin('property_details', 'properties.id', '=', 'property_details.property_id')
                        ->leftJoin('property_units', 'tenants.unit_id', '=', 'property_units.id')
                        ->select(['tenants.*', 'users.first_name', 'users.last_name', 'users.contact_number', 'users.email', 'property_units.unit_name', 'properties.name as property_name', 'property_details.address as property_address', 'tenant_details.previous_address', 'tenant_details.previous_country_id', 'tenant_details.previous_state_id', 'tenant_details.previous_city_id', 'tenant_details.previous_zip_code', 'tenant_details.permanent_address', 'tenant_details.permanent_country_id', 'tenant_details.permanent_state_id', 'tenant_details.permanent_city_id', 'tenant_details.permanent_zip_code'])
                        ->where('tenants.unit_id', $data['invoice']->property_unit_id)
                        ->firstOrFail();
        
        return $this->success($data);
    }

    public function destroy($id)
    {
        return $this->invoiceRecurringService->destroy($id);
    }
}
