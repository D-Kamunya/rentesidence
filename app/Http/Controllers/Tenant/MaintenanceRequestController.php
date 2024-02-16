<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\MaintenanceRequest;
use App\Services\MaintenanceIssueService;
use App\Services\MaintenanceRequestService;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;

class MaintenanceRequestController extends Controller
{
    use ResponseTrait;
    public $maintenanceRequestService, $maintenanceIssueService;

    public function __construct()
    {
        $this->maintenanceRequestService = new MaintenanceRequestService;
        $this->maintenanceIssueService = new MaintenanceIssueService;
    }

    public function index(Request $request)
    {
        $data['pageTitle'] = __('Maintenance');
        $data['issues'] = $this->maintenanceIssueService->getActiveAll();
        $data['tenant'] = auth()->user()->tenant;
        if ($request->ajax()) {
            return $this->maintenanceRequestService->getAllDataByTenant();
        }
        return view('tenant.maintenance-request', $data);
    }

    public function store(MaintenanceRequest $request)
    {
        return $this->maintenanceRequestService->store($request);
    }

    public function getInfo(Request $request)
    {
        $data = $this->maintenanceRequestService->getById($request->id);
        return $this->success($data);
    }
}
