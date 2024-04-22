<?php

namespace App\Http\Controllers\Saas;

use App\Http\Controllers\Controller;
use App\Models\Bank;
use App\Models\MpesaAccount;
use App\Models\SubscriptionOrder;
use App\Models\PaymentCheck;
use App\Models\Gateway;
use App\Models\User;
use App\Services\GatewayService;
use App\Services\SubscriptionService;
use App\Traits\ResponseTrait;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

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
        $ownerId = auth()->user()->id;
        $data['pageTitle'] = __('My Subscription');
        // Retrieve records from the SubscriptionOrder model
        $latestMpesaSubscriptionOrder = SubscriptionOrder::whereNotNull('payment_id')
            ->where('user_id', $ownerId) // Filter by user_id
            ->latest() // Order by created_at in descending order
            ->first(); // Retrieve only the latest record
        // Handle any pending mpesa subscription transactions
        if($latestMpesaSubscriptionOrder && strpos($latestMpesaSubscriptionOrder->payment_id, 'ws') === 0 && $latestMpesaSubscriptionOrder->payment_status == ORDER_PAYMENT_STATUS_PENDING) {
            $paymentCheck = PaymentCheck::where('subscription_payment_id', $latestMpesaSubscriptionOrder->id)->first();
            if (!$paymentCheck) {
                $paymentCheck = new PaymentCheck();
                $paymentCheck->subscription_payment_id = $latestMpesaSubscriptionOrder->id;
                $paymentCheck->check_count=0;
                $paymentCheck->last_check_at=now();
                $paymentCheck->save();
                $gateway = Gateway::find($latestMpesaSubscriptionOrder->gateway_id);
                // Clear specific flash messages
                Session::forget('success');
                Session::forget('error');
                handleSubscriptionPaymentConfirmation($latestMpesaSubscriptionOrder, null, $gateway->slug, $paymentCheck);
            }else{
                if($paymentCheck->check_count < 3){
                    $gateway = Gateway::find($latestMpesaSubscriptionOrder->gateway_id);
                    // Clear specific flash messages
                    Session::forget('success');
                    Session::forget('error');
                    handleSubscriptionPaymentConfirmation($latestMpesaSubscriptionOrder, null, $gateway->slug, $paymentCheck);
                }else {
                    // Get the creation timestamp of the subscription order
                    $subscriptionOrderCreatedAt = $latestMpesaSubscriptionOrder->created_at;
                    // Add 5 hours to the subscription order creation timestamp
                    $fiveHoursAfterSubscriptionOrderCreation = $subscriptionOrderCreatedAt->copy()->addHours(5);
                    // Check if the last_check_at timestamp in the payment check is greater than or equal to 5 hours after subscription order creation
                    $paymentCheckLastCheck = $paymentCheck->last_check_at;
                    if ($paymentCheckLastCheck->greaterThanOrEqualTo($fiveHoursAfterSubscriptionOrderCreation)) {
                        // Last check is more than or equal to 5 hours after subscription order creation
                        // Your logic here
                    } else {
                        // Last check is less than 5 hours after subscription order creation
                        $gateway = Gateway::find($latestMpesaSubscriptionOrder->gateway_id);
                        // Clear specific flash messages
                        Session::forget('success');
                        Session::forget('error');
                        handleSubscriptionPaymentConfirmation($latestMpesaSubscriptionOrder, null,$gateway->slug, $paymentCheck);
                    }
                }
            }
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
            $data['mpesaAccounts'] = MpesaAccount::where('owner_user_id', $user->id)->where('status', ACTIVE)->get();
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
