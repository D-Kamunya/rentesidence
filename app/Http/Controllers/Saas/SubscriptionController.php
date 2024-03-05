<?php

namespace App\Http\Controllers\Saas;

use App\Http\Controllers\Controller;
use App\Models\Bank;
use App\Models\SubscriptionOrder;
use App\Models\Gateway;
use App\Models\User;
use App\Services\GatewayService;
use App\Services\SubscriptionService;
use App\Traits\ResponseTrait;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SubscriptionController extends Controller
{
    use ResponseTrait;
    public $subscriptionService;

    public function __construct()
    {
        $this->subscriptionService = new SubscriptionService;
    }

    public function index(Request $request)
    {
        $data['pageTitle'] = __('My Subscription');
        // Retrieve records from the SubscriptionOrder model
        $latestMpesaSubscriptionOrder = SubscriptionOrder::whereNotNull('payment_id')
            ->where('payment_id', 'LIKE', 'ws%')
            ->latest() // Order by created_at in descending order
            ->first(); // Retrieve only the latest record
        // Handle any pending mpesa subscription transactions
        if($latestMpesaSubscriptionOrder && $latestMpesaSubscriptionOrder->payment_status == ORDER_PAYMENT_STATUS_PENDING) {
            $gateway = Gateway::find($latestMpesaSubscriptionOrder->gateway_id);
            // Clear specific flash messages
            Session::forget('success');
            Session::forget('error');
            handleSubscriptionPaymentConfirmation($latestMpesaSubscriptionOrder, null,$gateway->slug);
        }
        $data['userPlan'] = $this->subscriptionService->getCurrentPlan();
        if (!is_null($request->id)) {
            $data['gateways'] = $this->order($request);
        }
        return view('saas.owner.subscriptions.index', $data);
    }

    public function getPlan()
    {
        $data['plans'] = $this->subscriptionService->getAllPackages();
        $data['currentPlan'] = $this->subscriptionService->getCurrentPlan();
        return view('saas.owner.subscriptions.partials.plan-list', $data)->render();
    }

    public function order(Request $request)
    {
        try {
            $user = User::where('role', USER_ROLE_ADMIN)->first();
            if (is_null($user)) {
                throw new Exception(__(SOMETHING_WENT_WRONG));
            }
            $gateWayService = new GatewayService;
            $data['gateways'] = $gateWayService->getActiveAll($user->id);
            $data['plan'] = $this->subscriptionService->getById($request->id);
            $data['durationType'] = $request->duration_type ?? 1;
            $data['quantity'] = $request->quantity ?? 1;
            $data['banks'] = Bank::where('owner_user_id', $user->id)->where('status', ACTIVE)->get();
            $data['startDate'] = now();
            if ($request->duration_type == PACKAGE_DURATION_TYPE_MONTHLY) {
                $data['endDate'] = Carbon::now()->addMonth();
            } else {
                $data['endDate'] = Carbon::now()->addYear();
            }
            return view('saas.owner.subscriptions.partials.gateway-list', $data)->render();
        } catch (Exception $e) {
            return $this->error([], $e->getMessage());
        }
    }

    public function getCurrencyByGateway(Request $request)
    {
        $data = $this->subscriptionService->getCurrencyByGatewayId($request->id);
        return $this->success($data);
    }

    public function cancel()
    {
        $this->subscriptionService->cancel();
        return back()->with('success', __('Canceled Successful!'));
    }
}
