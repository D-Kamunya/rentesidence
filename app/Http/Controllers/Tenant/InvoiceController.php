<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Services\GatewayService;
use App\Services\InvoiceService;
use App\Services\TenantService;
use App\Models\Order;
use App\Models\PaymentCheck;
use App\Models\Gateway;
use App\Models\Invoice;
use App\Traits\ResponseTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class InvoiceController extends Controller
{
    use ResponseTrait;
    public $invoiceService;
    public $tenantService;
    public $gatewayService;

    public function __construct()
    {
        $this->invoiceService = new InvoiceService;
        $this->tenantService = new TenantService();
        $this->gatewayService = new GatewayService;
    }
    public function index()
    {
        $tenantId = auth()->user()->tenant->user_id;
        $data['pageTitle'] = __('Invoices');
        // Security deposit the landlord is holding for this tenancy — surfaced as reassurance
        // ("your money is recorded, refundable at move-out"), the tenant-facing half of Model A.
        $tenantRecord = auth()->user()->tenant;
        $data['depositHeld'] = $tenantRecord
            ? app(\App\Services\DepositService::class)->totalHeldForTenant((int) $tenantRecord->id)
            : 0;
        // The latest deposit settlement (if any) — the tenant confirms receipt / disputes it here.
        $data['depositSettlement'] = $tenantRecord
            ? \App\Models\DepositSettlement::with('items')->where('tenant_id', $tenantRecord->id)->latest('id')->first()
            : null;

        // Notice-to-vacate context: required period, earliest valid move-out, and any live notice.
        $vn = app(\App\Services\VacationNoticeService::class);
        $ownerId = (int) ($tenantRecord->owner_user_id ?? 0);
        $data['noticeDays']     = $tenantRecord ? $vn->noticePeriodDays($ownerId) : 30;
        $data['noticeEarliest'] = $tenantRecord ? $vn->earliestMoveOut($ownerId)->toDateString() : null;
        $data['activeNotice']   = $tenantRecord ? $vn->activeNotice((int) $tenantRecord->id) : null;
        $data['canGiveNotice']  = $tenantRecord && (int) $tenantRecord->status === TENANT_STATUS_ACTIVE;
        // Retrieve records from the SubscriptionOrder model
        // $latestMpesaOrder = Order::whereNotNull('payment_id')
        //     ->where('user_id', $tenantId) // Filter by user_id
        //     ->latest() // Order by created_at in descending order
        //     ->first(); // Retrieve only the latest record
        // // Handle any pending mpesa subscription transactions
        // if($latestMpesaOrder && strpos($latestMpesaOrder->payment_id, 'ws') === 0 && $latestMpesaOrder->payment_status == ORDER_PAYMENT_STATUS_PENDING) {
        //     $paymentCheck = PaymentCheck::where('invoice_payment_id', $latestMpesaOrder->id)->first();
        //     if (!$paymentCheck) {
        //         $paymentCheck = new PaymentCheck();
        //         $paymentCheck->invoice_payment_id = $latestMpesaOrder->id;
        //         $paymentCheck->check_count=0;
        //         $paymentCheck->last_check_at=now();
        //         $paymentCheck->save();
        //         $gateway = Gateway::find($latestMpesaOrder->gateway_id);
        //         // Clear specific flash messages
        //         Session::forget('success');
        //         Session::forget('error');
        //         handlePaymentConfirmation($latestMpesaOrder, null, $gateway->slug, $paymentCheck);
        //     }else{
        //         if($paymentCheck->check_count < 3){
        //             $gateway = Gateway::find($latestMpesaOrder->gateway_id);
        //             // Clear specific flash messages
        //             Session::forget('success');
        //             Session::forget('error');
        //             handlePaymentConfirmation($latestMpesaOrder, null, $gateway->slug, $paymentCheck);
        //         }else {
        //             // Get the creation timestamp of the subscription order
        //             $subscriptionOrderCreatedAt = $latestMpesaOrder->created_at;
        //             // Add 5 hours to the subscription order creation timestamp
        //             $fiveHoursAfterSubscriptionOrderCreation = $subscriptionOrderCreatedAt->copy()->addHours(5);
        //             // Check if the last_check_at timestamp in the payment check is greater than or equal to 5 hours after subscription order creation
        //             $paymentCheckLastCheck = $paymentCheck->last_check_at;
        //             if ($paymentCheckLastCheck->greaterThanOrEqualTo($fiveHoursAfterSubscriptionOrderCreation)) {
        //                 // Last check is more than or equal to 5 hours after subscription order creation
        //                 // Your logic here
        //             } else {
        //                 // Last check is less than 5 hours after subscription order creation
        //                 $gateway = Gateway::find($latestMpesaOrder->gateway_id);
        //                 // Clear specific flash messages
        //                 Session::forget('success');
        //                 Session::forget('error');
        //                 handlePaymentConfirmation($latestMpesaOrder, null, $gateway->slug, $paymentCheck);
        //             }
        //         }
        //     }
            
        // }
        $data['invoices'] = $this->invoiceService->getByTenantId(auth()->user()->tenant->id);
        // Upcoming rent months (name + amount + state) for the "Pay Upcoming Rent" modal.
        $data['upcomingRentMonths'] = app(\App\Services\InvoiceRecurringService::class)
            ->upcomingRentMonths(auth()->user()->tenant);
        return view('tenant.invoices.index', $data);
    }

    public function details($id)
    {
        $data['invoice'] = $this->invoiceService->getById($id);
        $data['items'] = $this->invoiceService->getItemsByInvoiceId($id);
        $data['owner'] = $this->invoiceService->ownerInfo(auth()->user()->owner_user_id);
        $data['tenant'] = $this->tenantService->getDetailsById($data['invoice']->tenant_id);
        $data['order'] = $this->invoiceService->getOrderById($data['invoice']->order_id);
        return view('tenant.invoices.print', $data);
    }

    /**
     * Post-payment rent receipt (mirrors the marketplace order receipt). Distinct from the
     * formal invoice document (details()/print) — this is the celebratory confirmation the
     * tenant lands on after a successful rent payment, with buttons back to their invoices.
     * Scoped to the authenticated tenant via getByIdCheckTenantAuthId (owner + tenant match),
     * so a tenant can never open another tenant's receipt by id.
     */
    public function receipt($id)
    {
        $invoice = $this->invoiceService->getByIdCheckTenantAuthId($id);

        $data['pageTitle'] = __('Payment Receipt');
        $data['invoice']   = $invoice;
        $data['items']     = $this->invoiceService->getItemsByInvoiceId($id);
        $data['owner']     = $this->invoiceService->ownerInfo(auth()->user()->owner_user_id);
        $data['order']     = $invoice->order_id
            ? $this->invoiceService->getOrderById($invoice->order_id)
            : null;

        return view('tenant.invoices.receipt', $data);
    }

   
    public function pay($id)
    {
        $data['pageTitle']              = __('Invoices Pay');
        $data['navInvoiceMMActiveClass'] = 'mm-active';
        $data['navInvoiceActiveClass']   = 'active';
        $data['invoice']                = $this->invoiceService->getByIdCheckTenantAuthId($id);
        $data['gateways']               = $this->gatewayService->getActiveAll(auth()->user()->owner_user_id);
        $data['banks']                  = $this->gatewayService->getActiveBanks();
        $data['mpesaAccounts']          = $this->gatewayService->getActiveMpesaAccounts();
    
        // ── Transaction model check ──────────────────────────────────────────────
        // Determines whether the owner is on transaction pricing.
        // If true, the blade hides gateway selection and shows M-Pesa-only UI.
        $subscription = \Illuminate\Support\Facades\DB::table('owner_packages')
            ->where('user_id', auth()->user()->owner_user_id)
            ->where('status', 1)
            ->latest()
            ->first();
    
        $pricingModel                   = $subscription?->pricing_model ?? 'free';
        $data['isTransactionModel']     = $pricingModel === 'transaction';
    
        // Resolve the centresidence rent gateway ID for the JS currency auto-fetch.
        // Only needed when isTransactionModel is true, but safe to pass always.
        $rentAccountId                  = getOption('centresidence_rent_mpesa_account_id');
        $rentMpesaAccount               = $rentAccountId
            ? \App\Models\MpesaAccount::find($rentAccountId)
            : null;
        $rentGateway                    = $rentMpesaAccount
            ? \App\Models\Gateway::find($rentMpesaAccount->gateway_id)
            : null;
        $data['ownerMpesaGatewayId']    = $rentGateway?->id;
    
        return view('tenant.invoices.pay', $data);
    }

    /**
     * Advance / early rent: generate the current + up to 10 future months' rent invoices on
     * demand (idempotent — never double-bills, and the cron won't re-generate them), then send
     * the tenant back to their invoices where the new ones are payable via the normal flow.
     */
    public function generateUpcoming(Request $request)
    {
        $tenant = auth()->user()->tenant;
        if (!$tenant || (int) $tenant->status !== TENANT_STATUS_ACTIVE) {
            return back()->with('error', __('No active tenancy found.'));
        }

        $recurringService = app(\App\Services\InvoiceRecurringService::class);

        // Only periods the tenant is actually shown as "available" (not already invoiced/paid,
        // within the 10-month window, monthly-rent unit) can be generated — tamper-safe.
        $available = collect($recurringService->upcomingRentMonths($tenant))
            ->where('state', 'available')
            ->keyBy('period');

        if ($available->isEmpty()) {
            return back()->with('error', __('There are no upcoming rent months available to prepare right now.'));
        }

        $selected = array_filter((array) $request->input('periods', []), fn ($p) => $available->has($p));
        if (empty($selected)) {
            return back()->with('error', __('Please pick at least one month to prepare.'));
        }

        $setting = $recurringService->ensureUnitRecurringSetting($tenant);
        $setting->loadMissing('items');

        $count = 0;
        foreach ($selected as $periodStr) {
            $recurringService->generateRentInvoiceForPeriod($tenant, $setting, Carbon::parse($periodStr)->startOfMonth());
            $count++;
        }

        return redirect()->route('tenant.invoice.index')
            ->with('success', __(':n rent invoice(s) prepared — pay them below.', ['n' => $count]));
    }

    public function getCurrencyByGateway(Request $request)
    {
        $data = $this->invoiceService->getCurrencyByGatewayId($request->id);
        return $this->success($data);
    }

    public function instantRentPayShow($token)
    {
        $invoice = Invoice::where('payment_token', $token)
            ->where('payment_token_expires_at', '>', now())
            ->firstOrFail();

        return view('tenant.invoices.instant-pay', compact('invoice'));
    }
}
