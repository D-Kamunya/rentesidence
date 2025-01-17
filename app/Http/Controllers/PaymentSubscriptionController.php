<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckoutRequest;
use App\Models\Bank;
use App\Models\Currency;
use App\Models\FileManager;
use App\Models\Gateway;
use App\Models\MpesaAccount;
use App\Models\GatewayCurrency;
use App\Models\Package;
use App\Models\SubscriptionOrder;
use App\Models\User;
use App\Services\Payment\Payment;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentSubscriptionController extends Controller
{
    public function checkout(CheckoutRequest $request)
    {
        DB::beginTransaction();
        try {
            $user = User::where('role', USER_ROLE_ADMIN)->first();
            $durationType = $request->duration_type == PACKAGE_DURATION_TYPE_MONTHLY ? PACKAGE_DURATION_TYPE_MONTHLY : PACKAGE_DURATION_TYPE_YEARLY;
            $quantity = (int) $request->quantity > 0 ? $request->quantity : 1;
            $package = Package::findOrFail($request->package_id);
            $gateway = Gateway::where(['owner_user_id' => $user->id, 'slug' => $request->gateway, 'status' => ACTIVE])->firstOrFail();
            $gatewayCurrency = GatewayCurrency::where(['gateway_id' => $gateway->id, 'currency' => $request->currency])->firstOrFail();
            if ($gateway->slug == 'bank') {
                $bank = Bank::where(['owner_user_id' => $user->id, 'gateway_id' => $gateway->id, 'id' => $request->bank_id])->first();
                if (is_null($bank)) {
                    throw new Exception('Bank not found');
                }
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
                $order = $this->placeOrder($package, $durationType, $quantity, $gateway, $gatewayCurrency, $bank_id, $bank_name, $bank_account_number, $deposit_by, $deposit_slip_id); // new order create
                $order->deposit_slip_id = $deposit_slip_id;
                $order->save();
                DB::commit();
                return redirect()->route('owner.subscription.index')->with('success', __('Bank Details Sent Successfully! Wait for approval'));
            } elseif ($gateway->slug == 'cash') {
                $order = $this->placeOrder($package, $durationType, $quantity, $gateway, $gatewayCurrency); // new order create
                $order->save();
                DB::commit();
                return redirect()->route('owner.subscription.index')->with('success', __('Cash Payment Request Sent Successfully! Wait for approval'));
            } elseif ($gateway->slug == 'mpesa'){
                if ($request->has('mpesa_transaction_code')) {
                    $order = $this->placeOrder($package, $durationType, $quantity, $gateway, $gatewayCurrency, null, null, null, null, null, $request->mpesa_transaction_code); // new order create
                    $order->save();
                    DB::commit();
                    return redirect()->route('owner.subscription.index')->with('success', __('Mpesa Transaction Code Submitted Successfully! Wait for approval'));
                }else{
                    $mpesaAccount = MpesaAccount::where(['owner_user_id' => $user->id, 'gateway_id' => $gateway->id, 'id' => $request->mpesa_account_id])->first();
                    if (is_null($mpesaAccount)) {
                        throw new Exception('Mpesa Account not found');
                    }
                    $paymentData['mpesaAccount'] = $mpesaAccount;
                    $order = $this->placeOrder($package, $durationType, $quantity, $gateway, $gatewayCurrency);
                    DB::commit();
                    }
                
            } else {
                $order = $this->placeOrder($package, $durationType, $quantity, $gateway, $gatewayCurrency); // new order create
                DB::commit();
            }
            $object = [
                'id' => $order->id,
                'gateway' => $gateway->slug,
                'callback_url' => route('payment.subscription.verify'),
                'currency' => $gatewayCurrency->currency,
                'type' => 'subscription'
            ];

            $payment = new Payment($gateway->slug, $object);
            $paymentData['amount'] = $order->total;
            $responseData = $payment->makePayment($paymentData);
            if ($responseData['success']) {
                $order->payment_id = $responseData['payment_id'];
                $order->save();
                if($gateway->slug=='mpesa'){
                    $url=$responseData['redirect_url'] . '&merchant_id=' . $responseData['merchant_request_id']. '&checkout_id=' . $responseData['checkout_request_id'];
                    return response()->json([
                        'success' => true,
                        'data' => $url
                    ]);
                }else{
                    return redirect($responseData['redirect_url']);
                }
            } else {
                if($gateway->slug=='mpesa'){
                    return response()->json([
                        'success' => false,
                        'data' => $responseData['message']
                    ]);
                }
                else{
                    return redirect()->back()->with('error', $responseData['message']);
                }
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('owner.subscription.index')->with('error', __('Payment Failed!'));
        }
    }

    public function placeOrder($package, $durationType, $quantity, $gateway, $gatewayCurrency, $bank_id = null, $bank_name = null, $bank_account_number = null, $deposit_by = null, $deposit_slip_id = null, $mpesa_transaction_code = null)
    {
        $price = 0;
        $perPrice = 0;
        if ($durationType == PACKAGE_DURATION_TYPE_MONTHLY) {
            $price = $package->monthly_price;
            $perPrice = $package->per_monthly_price * $quantity;
        } else {
            $price = $package->yearly_price;
            $perPrice = $package->per_yearly_price * $quantity;
        }
        $total = $price + $perPrice;

        return SubscriptionOrder::create([
            'user_id' => auth()->id(),
            'package_id' => $package->id,
            'package_type' => $package->type,
            'quantity' => $quantity,
            'system_currency' => Currency::where('current_currency', 'on')->first()->currency_code,
            'gateway_id' => $gateway->id,
            'duration_type' => $durationType,
            'gateway_currency' => $gatewayCurrency->currency,
            'amount' => $price,
            'subtotal' => $price,
            'total' => $price,
            'transaction_amount' => $price * $gatewayCurrency->conversion_rate,
            'conversion_rate' => $gatewayCurrency->conversion_rate,
            'payment_status' => ORDER_PAYMENT_STATUS_PENDING,
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
        $formattedGateway = ucfirst(strtolower($gateway_slug));

        $order = SubscriptionOrder::findOrFail($order_id);
        if ($order->payment_status == ORDER_PAYMENT_STATUS_PAID) {
            return redirect()->route('owner.subscription.index')->with('success', __($formattedGateway.' Payment Successfull. \nPackage Subscription Renewed!'));
        }elseif ($order->payment_status == ORDER_PAYMENT_STATUS_CANCELLED) {
            return redirect()->route('owner.subscription.index')->with('error', __($formattedGateway.' Payment Declined! \nPackage Subscription Not Renewed'));
        }
        
        return handleSubscriptionPaymentConfirmation($order, $payerId, $gateway_slug, null);
    }
    
}
