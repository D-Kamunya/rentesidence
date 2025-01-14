<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Services\GatewayService;
use App\Services\InvoiceService;
use App\Services\TenantService;
use App\Models\Order;
use App\Models\PaymentCheck;
use App\Models\Gateway;
use App\Traits\ResponseTrait;
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

    public function pay($id)
    {
        $data['pageTitle'] = __('Invoices Pay');
        $data['navInvoiceMMActiveClass'] = 'mm-active';
        $data['navInvoiceActiveClass'] = 'active';
        $data['invoice'] = $this->invoiceService->getByIdCheckTenantAuthId($id);
        $data['gateways'] = $this->gatewayService->getActiveAll(auth()->user()->owner_user_id);
        $data['banks'] = $this->gatewayService->getActiveBanks();
        $data['mpesaAccounts'] = $this->gatewayService->getActiveMpesaAccounts();
        return view('tenant.invoices.pay', $data);
    }

    public function getCurrencyByGateway(Request $request)
    {
        $data = $this->invoiceService->getCurrencyByGatewayId($request->id);
        return $this->success($data);
    }
}
