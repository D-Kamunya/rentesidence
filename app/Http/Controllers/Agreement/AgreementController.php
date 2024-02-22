<?php

namespace App\Http\Controllers\Agreement;

use App\Http\Controllers\Controller;
use App\Http\Requests\AgreementRequest;
use App\Http\Requests\TestAgreementRequest;
use App\Models\Setting;
use App\Services\Agreement\AgreementService;
use App\Services\PropertyService;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AgreementController extends Controller
{
    use ResponseTrait;

    public $agreementService, $tenantService, $propertyService;

    public function __construct()
    {
        $this->agreementService = new AgreementService;
        $this->propertyService = new PropertyService;
    }
    public function index(Request $request)
    {
        $data['pageTitle'] = __('Agreements');
        if ($request->ajax()) {
            return $this->agreementService->getAllData();
        }
        $data['properties'] = $this->propertyService->getAll();
        return view('agreement.owner.send', $data);
    }

    public function tenantAgreement()
    {
        $data['pageTitle'] = __('Agreements');
        $data['agreements'] = $this->agreementService->tenantAgreements();
        return view('agreement.tenant.index', $data);
    }

    public function send(AgreementRequest $request): object
    {
        return $this->agreementService->send($request);
    }

    public function testSend(TestAgreementRequest $request): object
    {
        return $this->agreementService->testSend($request);
    }

    public function callback(Request $request)
    {
        Log::info('<<<<< agreement callback >>>>>>>>');
        Log::info($request->all());
        $option = Setting::firstOrCreate(['option_key' => 'docu_sign_callback_code']);
        $option->option_value = $request['code'];
        $option->save();
        return redirect()->route('admin.setting.agreement.setting')->with('success', __('Allowed Successfully'));
    }

    public function download($envelope_id)
    {
        return $this->agreementService->download($envelope_id);
    }

    public function tenantDownload($envelope_id)
    {
        return $this->agreementService->tenantDownload($envelope_id);
    }

    public function test($envelope_id)
    {
        return $this->agreementService->test($envelope_id);
    }
}
