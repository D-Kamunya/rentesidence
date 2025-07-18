<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\TenantCloseRequest;
use App\Http\Requests\TenantDeleteRequest;
use App\Http\Requests\TenantRequest;
use App\Http\Requests\TenantEditRequest;
use App\Models\Property;
use App\Services\InvoiceTypeService;
use App\Services\SmsMail\AdvantaSmsService;
use App\Services\LocationService;
use App\Services\PropertyService;
use App\Services\TenantService;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;

class TenantController extends Controller
{
    use ResponseTrait;
    public $tenantService, $propertyService, $locationService, $invoiceTypeService;

    public function __construct()
    {
        $this->tenantService = new TenantService;
        $this->propertyService = new PropertyService;
        $this->invoiceTypeService = new InvoiceTypeService;
        $this->locationService = new LocationService;
    }

    public function index(Request $request)
    {
        $data['navTenantMMShowClass'] = 'mm-show';
        if ($request->type == 'history') {
            $data['pageTitle'] = __('Tenants History');
            $data['subNavTenantHistoryMMActiveClass'] = 'mm-active';
            $data['subNavTenantHistoryActiveClass'] = 'active';
            if ($request->ajax()) {
                return $data['tenants'] = $this->tenantService->getAllHistoryData();
            }
            return view('owner.tenants.history', $data);
        } else {
            $data['subNavAllTenantMMActiveClass'] = 'mm-active';
            $data['subNavAllTenantActiveClass'] = 'active';
            $data['pageTitle'] = __('Tenants');
            $data['properties'] = $this->propertyService->getAll();
            if (getOption('app_card_data_show', 1) == 1) {
                $data['tenants'] = $this->tenantService->getActiveAll();
            }
            if ($request->ajax()) {
                return $this->tenantService->getAllData();
            }
            return view('owner.tenants.index', $data);
        }
    }

    public function sendLoginDets(Request $request)
    {
        $data['tenants'] = $this->tenantService->getAllTenantsLogins();
           // Loop through tenants and send SMS
        foreach ($data['tenants'] as $tenant) {
            $message = "Dear {$tenant->first_name}, Welcome to Centresidence Property Management Technologies! Here are your account details:";
            $message .= " Email: {$tenant->email}";
            $message .= " Password: 123456";
            $message .= " Please use these to access your account on centresidence.com and update your information. For any questions, contact your agent Mr. Wanjohi at 0720847025. We will also be on your property tomorrow for any assistance";
            try {
                AdvantaSmsService::sendSms([$tenant->contact_number], $message, auth()->id());
            } catch (\Exception $e) {
                \Log::error("SMS sending failed for {$tenant->contact_number}: " . $e->getMessage());
            }
        }
        return response()->json(['message' => 'SMS sent to all tenants.']);
    }

    public function create()
    {
        if (getOwnerLimit(RULES_TENANT) < 1) {
            return back()->with('error', __('Your Tenant Limit is Finished. Choose or Renew Package Plan'));
        }
        $data['pageTitle'] = __('Add Tenant');
        $data['subNavAllTenantMMActiveClass'] = 'mm-active';
        $data['subNavAllTenantActiveClass'] = 'active';
        $data['countries'] = $this->locationService->getCountry()->getData()->data;
        $data['properties'] = Property::query()->with('propertyUnits')->where('owner_user_id', auth()->id())->get();
        return view('owner.tenants.add', $data);
    }

    public function edit($id)
    {
        $data['pageTitle'] = __('Edit Tenant');
        $data['subNavAllTenantMMActiveClass'] = 'mm-active';
        $data['subNavAllTenantActiveClass'] = 'active';
        $data['tenant'] = $this->tenantService->getDetailsById($id);
        $data['countries'] = $this->locationService->getCountry()->getData()->data;
        $data['previousStates'] = $this->locationService->getStateByCountryId($data['tenant']->previous_country_id)->getData()->data->states;
        $data['previousSities'] = $this->locationService->getCitiesByStateId($data['tenant']->previous_state_id)->getData()->data->cities;
        $data['permanentStates'] = $this->locationService->getStateByCountryId($data['tenant']->permanent_country_id)->getData()->data->states;
        $data['permanentSities'] = $this->locationService->getCitiesByStateId($data['tenant']->permanent_state_id)->getData()->data->cities;
        $data['properties'] = $this->propertyService->getAll();
        $data['units'] = $this->propertyService->getPropertyWithUnitsById($data['tenant']->property_id)->getData()->data->units ?? [];
        return view('owner.tenants.edit', $data);
    }

    public function store(Request $request)
    {
         // Determine which validation rules to apply
        if ($request->has('edit_form')) {
            // Editing an existing tenant
            $validated = app(TenantEditRequest::class)->validated();
        } else {
            // Creating a new tenant
            $validated = app(TenantRequest::class)->validated();
        }

        // Now replace original $request with validated data
        $request->merge($validated);

        if ($request->step == FORM_STEP_ONE) {
            return $this->tenantService->step1($request);
        } elseif ($request->step == FORM_STEP_TWO) {
            return $this->tenantService->step2($request);
        } elseif ($request->step == FORM_STEP_THREE) {
            return $this->tenantService->step3($request);
        }
        return $this->error();
    }

    public function details(Request $request, $id)
    {
        $data['navTenantMMShowClass'] = 'mm-show';
        $data['subNavAllTenantMMActiveClass'] = 'mm-active';
        $data['subNavAllTenantActiveClass'] = 'active';

        if ($request->tab == 'profile') {
            $data['pageTitle'] = __('Profile');
            $data['navTenantProfileActiveClass'] = 'active';
            $data['tenant'] = $this->tenantService->getDetailsById($id);
            $data['paymentDueInvoiceCount'] = count($this->tenantService->paymentDue($id));
            return view('owner.tenants.details.profile', $data);
        } elseif ($request->tab == 'home') {
            $data['pageTitle'] = __('Home Details');
            $data['navTenantHomeActiveClass'] = 'active';
            $data['tenant'] = $this->tenantService->getDetailsById($id);
            return view('owner.tenants.details.home', $data);
        } elseif ($request->tab == 'payment') {
            $data['pageTitle'] = __('Payment Details');
            $data['navTenantPaymentActiveClass'] = 'active';
            $data['tenant'] = $this->tenantService->getById($id);
            $data['invoiceTypes'] = $this->invoiceTypeService->getAll();
            if ($request->ajax()) {
                return $this->tenantService->payment($id);
            }
            return view('owner.tenants.details.payment', $data);
        } elseif ($request->tab == 'document') {
            $data['pageTitle'] = __('Document');
            $data['navTenantDocumentActiveClass'] = 'active';
            $data['tenant'] = $this->tenantService->getById($id);
            return view('owner.tenants.details.document', $data);
        } elseif ($request->tab == 'closing-history') {
            $data['pageTitle'] = __('Closing History');
            $data['navTenantClosingHistoryActiveClass'] = 'active';
            $data['tenant'] = $this->tenantService->closingStatusHistory($id);
            return view('owner.tenants.details.closing-history', $data);
        }
    }

    public function closeHistoryStore(TenantCloseRequest $request, $id)
    {
        return $this->tenantService->closeHistoryStore($request, $id);
    }

    public function documentDestroy($id)
    {
        return $this->tenantService->documentDestroy($id);
    }

    public function delete(TenantDeleteRequest $request)
    {
        return $this->tenantService->delete($request);
    }
}
