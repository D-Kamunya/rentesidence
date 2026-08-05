<?php

namespace App\Http\Controllers\Saas;

use App\Centresidence\Exceptions\FacilityActiveModeLockException;
use App\Centresidence\Services\PaymentModeService;
use App\Http\Controllers\Controller;
use App\Models\Bank;
use App\Models\MpesaAccount;
use App\Models\SubscriptionOrder;
use App\Models\PaymentCheck;
use App\Models\Gateway;
use App\Models\User;
use App\Models\Package;
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
        // $latestMpesaSubscriptionOrder = SubscriptionOrder::whereNotNull('payment_id')
        //     ->where('user_id', $ownerId) // Filter by user_id
        //     ->latest() // Order by created_at in descending order
        //     ->first(); // Retrieve only the latest record
        // // Handle any pending mpesa subscription transactions
        // if($latestMpesaSubscriptionOrder && strpos($latestMpesaSubscriptionOrder->payment_id, 'ws') === 0 && $latestMpesaSubscriptionOrder->payment_status == ORDER_PAYMENT_STATUS_PENDING) {
        //     $paymentCheck = PaymentCheck::where('subscription_payment_id', $latestMpesaSubscriptionOrder->id)->first();
        //     if (!$paymentCheck) {
        //         $paymentCheck = new PaymentCheck();
        //         $paymentCheck->subscription_payment_id = $latestMpesaSubscriptionOrder->id;
        //         $paymentCheck->check_count=0;
        //         $paymentCheck->last_check_at=now();
        //         $paymentCheck->save();
        //         $gateway = Gateway::find($latestMpesaSubscriptionOrder->gateway_id);
        //         // Clear specific flash messages
        //         Session::forget('success');
        //         Session::forget('error');
        //         handleSubscriptionPaymentConfirmation($latestMpesaSubscriptionOrder, null, $gateway->slug, $paymentCheck);
        //     }else{
        //         if($paymentCheck->check_count < 3){
        //             $gateway = Gateway::find($latestMpesaSubscriptionOrder->gateway_id);
        //             // Clear specific flash messages
        //             Session::forget('success');
        //             Session::forget('error');
        //             handleSubscriptionPaymentConfirmation($latestMpesaSubscriptionOrder, null, $gateway->slug, $paymentCheck);
        //         }else {
        //             // Get the creation timestamp of the subscription order
        //             $subscriptionOrderCreatedAt = $latestMpesaSubscriptionOrder->created_at;
        //             // Add 5 hours to the subscription order creation timestamp
        //             $fiveHoursAfterSubscriptionOrderCreation = $subscriptionOrderCreatedAt->copy()->addHours(5);
        //             // Check if the last_check_at timestamp in the payment check is greater than or equal to 5 hours after subscription order creation
        //             $paymentCheckLastCheck = $paymentCheck->last_check_at;
        //             if ($paymentCheckLastCheck->greaterThanOrEqualTo($fiveHoursAfterSubscriptionOrderCreation)) {
        //                 // Last check is more than or equal to 5 hours after subscription order creation
        //                 // Your logic here
        //             } else {
        //                 // Last check is less than 5 hours after subscription order creation
        //                 $gateway = Gateway::find($latestMpesaSubscriptionOrder->gateway_id);
        //                 // Clear specific flash messages
        //                 Session::forget('success');
        //                 Session::forget('error');
        //                 handleSubscriptionPaymentConfirmation($latestMpesaSubscriptionOrder, null,$gateway->slug, $paymentCheck);
        //             }
        //         }
        //     }
        // }
        $data['userPlan'] = $this->subscriptionService->getCurrentPlan();

        // Centresidence: the live monthly cost of this owner's subscription-billed
        // modules, plus what they actually pay for the plan — so the My
        // Subscription page can show package + modules = total transparently.
        // Guarded so the legacy page never breaks on non-Centresidence installs.
        // Price of the owner's CURRENT plan, tied to the active package's OWN
        // order — not the latest paid order globally, which lingers after a
        // switch to a free/transaction plan (no new payment) and showed a stale
        // amount. Free plans carry no order → 0.
        $activePackage = \App\Models\OwnerPackage::where('user_id', $ownerId)
            ->where('status', ACTIVE)->latest('id')->first();
        $data['packageAmount'] = ($activePackage && $activePackage->order_id)
            ? (float) (\App\Models\SubscriptionOrder::where('id', $activePackage->order_id)->value('amount') ?? 0)
            : 0.0;
        $data['moduleCosts'] = ['lines' => [], 'total' => '0.00', 'has_modules' => false];
        $data['moduleInvoiceStatus'] = null;
        $data['infraOutstanding'] = 0.0; // unpaid infra the owner can settle now
        $data['infraOutstandingCount'] = 0; // how many issued bills that spans
        $data['infraBundlesWithPlan'] = false; // monthly owners pay infra via renewal, not standalone

        // Transaction-mode owners pay NO monthly subscription — Centresidence
        // takes a per-rent-transaction fee instead, and their module infra is
        // recovered from rent (not billed alongside a plan). The billing card
        // branches on this so the numbers read honestly for each pricing model.
        $isTransactionMode = app(PaymentModeService::class)->isTransactionMode($ownerId);
        $data['isTransactionMode'] = $isTransactionMode;
        $data['rentCommissionRate'] = \App\Services\CommissionService::RENT_COMMISSION_RATE;

        if (\Illuminate\Support\Facades\Schema::hasTable('property_modules')) {
            // Subscription owners see subscription-billed modules; transaction
            // owners see their transaction-billed infra (recovered from rent).
            $billingModel = $isTransactionMode
                ? \App\Centresidence\Models\PropertyModule::BILLING_TRANSACTION
                : \App\Centresidence\Models\PropertyModule::BILLING_SUBSCRIPTION;
            $data['moduleCosts'] = app(\App\Centresidence\Services\OwnerModuleCostService::class)->currentMonthly($ownerId, $billingModel);

            if ($isTransactionMode) {
                // Worst status across this owner's current-month rent-recovered infra invoices.
                $statuses = \App\Centresidence\Models\OwnerInfrastructureInvoice::where('owner_id', $ownerId)
                    ->whereDate('billing_month', now()->startOfMonth()->toDateString())->pluck('status');
            } else {
                // Worst status across this owner's current-month subscription module invoices.
                $statuses = \App\Centresidence\Models\CentresidenceCommissionInvoice::where('owner_id', $ownerId)
                    ->whereDate('billing_month', now()->startOfMonth()->toDateString())->pluck('status');
            }
            $data['moduleInvoiceStatus'] = $statuses->contains('overdue') ? 'overdue'
                : ($statuses->contains('partially_paid') ? 'partially_paid'
                : ($statuses->contains('pending') ? 'pending'
                : ($statuses->isNotEmpty() ? 'paid' : null)));

            // Actual unpaid infra the owner can settle now (subscription owners only;
            // transaction owners recover infra from rent — nothing to pay here).
            if (! $isTransactionMode) {
                $out = app(\App\Centresidence\Services\InfraBillPaymentService::class)->outstanding($ownerId);
                $data['infraOutstanding'] = $out['total'];
                $data['infraOutstandingCount'] = $out['invoices']->count();
                // Monthly-plan owners settle infra WITH the plan renewal (bundled), so
                // the standalone infra Pay button is redundant — one pay path for them.
                // Yearly / plan-less owners pay infra standalone (keep the button).
                $renew = $this->subscriptionService->getSubscriptionState($ownerId)['renew'] ?? null;
                $data['infraBundlesWithPlan'] = ($renew['duration_type'] ?? 0) == PACKAGE_DURATION_TYPE_MONTHLY;
            }
        }

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
            // Paid plans are non-transaction; block starting checkout while a
            // financing facility is active (the owner must stay on transaction).
            $targetPlan = Package::find($request->id);
            if ($msg = $this->facilityLockMessage($targetPlan->pricing_model ?? null)) {
                return $this->error([], $msg);
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
            // Outstanding module-infra bundled into this checkout (shown as a line so
            // the Total Due matches the actual KES charge; server-side placeOrder is
            // the source of truth and only bundles it on a KES transaction).
            $data['infraOutstanding'] = (float) app(\App\Centresidence\Services\InfraBillPaymentService::class)
                ->outstanding((int) auth()->id())['total'];
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
        // Cancelling drops the owner off the transaction plan, which would break
        // at-source facility repayment. Block while a facility is active.
        if ($msg = $this->facilityLockMessage('free')) {
            return back()->with('error', $msg);
        }

        $this->subscriptionService->cancel();
        return back()->with('success', __('Canceled Successful!'));
    }

    /**
     * Centresidence guard: financing requires transaction mode and an owner may
     * not leave it while a facility is active (handbook §9). Returns a friendly
     * message if blocked, or null if the switch to $targetMode is allowed.
     */
    private function facilityLockMessage(?string $targetMode): ?string
    {
        try {
            app(PaymentModeService::class)->assertCanSwitchTo((int) auth()->id(), $targetMode ?? 'free');
            return null;
        } catch (FacilityActiveModeLockException $e) {
            return __('You have an active infrastructure financing facility, so you must stay on the transaction plan until it is fully repaid. Settle the facility first to change or cancel your plan.');
        }
    }

    public function confirmFreeView(Request $request)
    {
        $request->validate([
            'package_id' => ['required', 'integer', 'exists:packages,id'],
        ]);
 
        $package = Package::findOrFail($request->package_id);
 
        // Hard guard — refuse if someone calls this for a paid plan
        if (!in_array($package->pricing_model ?? '', ['free', 'transaction'])) {
            abort(403, 'Confirmation view only available for free and transaction plans.');
        }
 
        return view('saas.owner.subscriptions.partials.confirm-free')->render();
    }
 
    /**
     * Directly activate a free or transaction plan — no payment involved.
     *
     * Security layers:
     *   1. CSRF (POST form with @csrf in the blade partial)
     *   2. `confirmed` checkbox must be accepted (value=1, user must tick it)
     *   3. pricing_model hard guard — abort(403) for any paid plan ID
     *   4. Auth middleware (already on owner route group)
     *   5. Rate limited via throttle:10,1 on the route definition
     */
    public function activateFree(Request $request)
    {
        $request->validate([
            'package_id' => ['required', 'integer', 'exists:packages,id'],
            'confirmed'  => ['required', 'accepted'],
        ]);
 
        $package = Package::findOrFail($request->package_id);
 
        // Hard guard — only free/transaction plans allowed through this endpoint
        if (!in_array($package->pricing_model ?? '', ['free', 'transaction'])) {
            abort(403, 'This endpoint is only available for free and transaction plans.');
        }
 
        if ($package->status !== ACTIVE) {
            return back()->with('error', __('This plan is no longer available.'));
        }

        // Block leaving transaction mode while a financing facility is active.
        if ($msg = $this->facilityLockMessage($package->pricing_model)) {
            return back()->with('error', $msg);
        }

        // 50-year duration → effectively never expires; active until cancelled.
        // setUserPackage does Carbon::now()->addDays($duration) internally.
        $duration = 365 * 50;
 
        setUserPackage(auth()->id(), $package, $duration, 1, null);
 
        $message = $package->pricing_model === 'transaction'
            ? __('Transaction plan activated! Rent payments will be collected via the Centresidence M-Pesa account and held in your Centresidence Wallet.')
            : __('Free plan activated successfully!');
 
        return redirect()->route('owner.subscription.index')->with('success', $message);
    }
}