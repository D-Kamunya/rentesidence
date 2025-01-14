<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckoutRequest;
use App\Models\Bank;
use App\Models\MpesaAccount;
use App\Models\Currency;
use App\Models\FileManager;
use App\Models\Gateway;
use App\Models\GatewayCurrency;
use App\Models\Invoice;
use App\Models\Order;
use App\Services\Payment\Payment;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function checkout(CheckoutRequest $request)
    {
        $invoice = Invoice::where('owner_user_id', auth()->user()->owner_user_id)->where('tenant_id', auth()->user()->tenant->id)->findOrFail($request->invoice_id);
        $gateway = Gateway::where(['owner_user_id' => auth()->user()->owner_user_id, 'slug' => $request->gateway, 'status' => ACTIVE])->firstOrFail();
        $gatewayCurrency = GatewayCurrency::where(['owner_user_id' => auth()->user()->owner_user_id, 'gateway_id' => $gateway->id, 'currency' => $request->currency])->firstOrFail();
        if ($gateway->slug == 'bank') {
            DB::beginTransaction();
            try {
                $bank = Bank::where(['gateway_id' => $gateway->id, 'id' => $request->bank_id])->firstOrFail();
                $bank_id = $bank->id;
                $bank_name = $bank->name;
                $bank_account_number = $bank->bank_account_number;
                $deposit_by = $request->deposit_by;
                $deposit_slip_id = null;
                if ($request->hasFile('bank_slip')) {
                    /*File Manager Call upload for Thumbnail Image*/

                    $newFile = new FileManager();
                    $upload = $newFile->upload('Order', $request->bank_slip);

                    if ($upload['status']) {
                        $deposit_slip_id = $upload['file']->id;
                        $upload['file']->origin_type = "App\Models\Order";
                        $upload['file']->save();
                    } else {
                        throw new Exception($upload['message']);
                    }
                    /*End*/
                } else {
                    throw new Exception('The Bank slip is required');
                }
                $order = $this->placeOrder($invoice, $gateway, $gatewayCurrency, $bank_id, $bank_name, $bank_account_number, $deposit_by, $deposit_slip_id); // new order create
                $order->deposit_slip_id = $deposit_slip_id;
                $order->save();
                $invoice->order_id = $order->id;
                $invoice->save();
                DB::commit();
                return redirect()->route('tenant.invoice.index')->with('success', __('Bank Details Sent Successfully! Wait for approval'));
            } catch (\Exception $e) {
                DB::rollBack();
                return redirect()->route('tenant.invoice.index')->with('error', __('Payment Failed!'));
            }
        } elseif ($gateway->slug == 'mpesa'){
            if ($request->has('mpesa_transaction_code')) {
                $order = $this->placeOrder($invoice, $gateway, $gatewayCurrency,null, null, null, null, null, $request->mpesa_transaction_code); // new order create
                $order->save();
                DB::commit();
                return redirect()->route('tenant.invoice.index')->with('success', __('Mpesa Transaction Code Submitted Successfully! Wait for approval'));
            }else{
                $mpesaAccount = MpesaAccount::where(['gateway_id' => $gateway->id, 'id' => $request->mpesa_account_id])->first();
                if (is_null($mpesaAccount)) {
                    throw new Exception('Mpesa Account not found');
                }
                $paymentData['mpesaAccount'] = $mpesaAccount;
                $order = $this->placeOrder($invoice, $gateway, $gatewayCurrency);
                DB::commit();
            }
        }elseif ($gateway->slug == 'cash') {
            $order = $this->placeOrder($invoice, $gateway, $gatewayCurrency); // new order create
            $invoice->order_id = $order->id;
            $invoice->save();
            return redirect()->route('tenant.invoice.index')->with('success', __('Cash Payment Request Sent Successfully! Wait for approval'));
        } else {
            $order = $this->placeOrder($invoice, $gateway, $gatewayCurrency); // new order create
        }


        $object = [
            'id' => $order->id,
            'gateway' => $gateway->slug,
            'callback_url' => route('payment.verify'),
            'currency' => $gatewayCurrency->currency,
            'type' => 'Rent Payment'
        ];

        $payment = new Payment($gateway->slug, $object);
        $paymentData['amount'] = $order->total;
        $responseData = $payment->makePayment($paymentData);
        if ($responseData['success']) {
            $order->payment_id = $responseData['payment_id'];
            $order->save();
            if($gateway->slug=='mpesa'){
                $url=$responseData['redirect_url'] . '&merchant_id=' . $responseData['merchant_request_id']. '&checkout_id=' . $responseData['checkout_request_id'];
                return redirect($url);
            }
            return redirect($responseData['redirect_url']);
        } else {
            return redirect()->back()->with('error', $responseData['message']);
        }
    }

    public function placeOrder($invoice, $gateway, $gatewayCurrency, $bank_id = null, $bank_name = null, $bank_account_number = null, $deposit_by = null, $deposit_slip_id = null,$mpesa_transaction_code = null)
    {
        return Order::create([
            'user_id' => auth()->id(),
            'invoice_id' => $invoice->id,
            'amount' => $invoice->amount,
            'system_currency' => Currency::where('current_currency', 'on')->first()->currency_code,
            'gateway_id' => $gateway->id,
            'gateway_currency' => $gatewayCurrency->currency,
            'conversion_rate' => $gatewayCurrency->conversion_rate,
            'subtotal' => $invoice->amount,
            'total' => $invoice->amount,
            'transaction_amount' => $invoice->amount * $gatewayCurrency->conversion_rate,
            'payment_status' => INVOICE_STATUS_PENDING,
            'bank_id' => $bank_id,
            'bank_name' => $bank_name,
            'bank_account_number' => $bank_account_number,
            'deposit_by' => $deposit_by,
            'deposit_slip_id' => $deposit_slip_id,
            'mpesa_transaction_code' => $mpesa_transaction_code
        ]);
    }

    public function verify(Request $request)
    {
        $order_id = $request->get('id', '');
        $payerId = $request->get('PayerID', NULL);
        $payment_id = $request->get('paymentId', NULL);
        $gateway_slug = $request->get('gateway', NULL);
        $merchant_id = $request->get('merchant_id', NULL);
        $checkout_id = $request->get('checkout_id', NULL);


        if($gateway_slug=='mpesa'){
            sleep(45);
        }

        $order = Order::findOrFail($order_id);
        if ($order->status == INVOICE_STATUS_PAID) {
            return redirect()->route('tenant.invoice.index')->with('error', __('Your order has been paid!'));
        }

        
        return handlePaymentConfirmation($order,$payerId,$gateway_slug,null);
    }

    public function verifyRedirect($type = 'error')
    {
        $data['type'] = $type;
        if ($type == 'success') {
            $data['message'] = __('Payment Successful!');
        } else {
            $data['message'] = __('Payment Failed!');
        }
        return view('common.verify-redirect', $data);
    }
}
