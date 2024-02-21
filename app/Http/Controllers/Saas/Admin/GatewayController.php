<?php

namespace App\Http\Controllers\Saas\Admin;

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

        return view('admin.setting.gateway', $data);
    }

    public function store(GatewayRequest $request)
    {
        return $this->gatewayService->store($request);
    }

    public function getInfo(Request $request)
    {
        return $this->gatewayService->getCurrenciesByGatewayId($request->id);
    }
}
