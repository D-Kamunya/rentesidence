<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\GatewayRequest;
use App\Services\GatewayService;
use Illuminate\Http\Request;

class GatewayController extends Controller
{
    public $gatewayService;

    public function __construct()
    {
        $this->gatewayService = new GatewayService;
    }

    public function index(Request $request)
    {
        $data['pageTitle'] = __('Gateway');
        $data['subGatewaySettingActiveClass'] = 'active';
        $data['gateways'] = $this->gatewayService->getAll();

        return view('owner.setting.gateway', $data);
    }

    public function store(GatewayRequest $request)
    {
        return $this->gatewayService->store($request);
    }

    public function getInfo(Request $request)
    {
        return $this->gatewayService->getCurrenciesByGatewayId($request->id);
    }

    /**
     * Inline "how you get paid" setup — a single Till OR Paybill, activated in place from the
     * dashboard checklist. Keeps the owner on the dashboard instead of sending them into the full
     * gateway settings screen (the #1 onboarding drop-off). Delegates to the same service that owns
     * the M-Pesa invariants (KES locked at 1.00), so inline and full settings stay consistent.
     */
    public function quickMpesa(Request $request)
    {
        $validated = $request->validate([
            'account_type'   => 'required|in:TILLNUMBER,PAYBILL',
            'till_number'    => 'required_if:account_type,TILLNUMBER|nullable|digits_between:5,10',
            'paybill_number' => 'required_if:account_type,PAYBILL|nullable|digits_between:5,10',
            'account_name'   => 'required_if:account_type,PAYBILL|nullable|string|max:120',
        ]);

        return $this->gatewayService->quickMpesaSetup($validated);
    }
}
