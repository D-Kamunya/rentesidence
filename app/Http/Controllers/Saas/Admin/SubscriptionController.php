<?php

namespace App\Http\Controllers\Saas\Admin;

use App\Http\Controllers\Controller;
use App\Services\OwnerSubscriptionOrderService;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    use ResponseTrait;
    public $orderService;

    public function __construct()
    {
        $this->orderService = new OwnerSubscriptionOrderService;
    }

    public function orders(Request $request)
    {
        if ($request->ajax()) {
            return $this->orderService->getAllData($request);
        } else {
            $data['pageTitle'] = __('All Owner Orders');
            return view('admin.subscriptions.orders', $data);
        }
    }

    public function ordersStatus(Request $request)
    {
        if ($request->ajax()) {
            return $this->orderService->getByStatus($request);
        }
    }

    public function orderGetInfo(Request $request)
    {
        $data = $this->orderService->orderGetInfo($request->id);
        return $this->success($data);
    }

    public function orderPaymentStatusChange(Request $request)
    {
        return $this->orderService->orderPaymentStatusChange($request);
    }
}
