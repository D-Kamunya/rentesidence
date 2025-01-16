<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckoutRequest;
use App\Models\Bank;
use App\Models\Currency;
use App\Models\FileManager;
use App\Models\Gateway;
use App\Models\MpesaAccount;
use App\Models\GatewayCurrency;
use App\Models\ProductOrder;
use App\Models\User;
use App\Services\Payment\Payment;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProductPaymentController extends Controller
{
    public function checkout(CheckoutRequest $request)
    {
        DB::beginTransaction();
        try {
            $adminId = User::where('role', USER_ROLE_ADMIN)->first();
            $ownerId = auth()->user()->owner_user_id; 
            $gateway = Gateway::where(['owner_user_id' => $ownerId, 'slug' => $request->gateway, 'status' => ACTIVE])->firstOrFail();
            $gatewayCurrency = GatewayCurrency::where(['gateway_id' => $gateway->id, 'currency' => $request->currency])->firstOrFail();
            $cartAmount = $request->cartTotal;
            $mpesaAmount = $request->mpesa_amount;
            $products = $request->input('products');

            if ($gateway->slug == 'bank') {
                $bank = Bank::where(['owner_user_id' => $ownerId, 'gateway_id' => $gateway->id, 'id' => $request->bank_id])->first();
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
                    $upload = $newFile->upload('ProductOrder', $request->bank_slip);

                    if ($upload['status']) {
                        $deposit_slip_id = $upload['file']->id;
                        $upload['file']->origin_type = "App\Models\ProductOrder";
                        $upload['file']->save();
                    } else {
                        throw new Exception($upload['message']);
                    }
                    /*End*/
                } else {
                    throw new Exception('The Bank slip is required');
                }
                $order = $this->placeOrder($cartAmount, $gateway, $gatewayCurrency, $bank_id, $bank_name, $bank_account_number, $deposit_by, $deposit_slip_id); // new order create
                $order->deposit_slip_id = $deposit_slip_id;
                $order->save();
                $this->addProductOrderItems($order, $products);
                DB::commit();
                echo '
                    <script>
                        localStorage.removeItem("cartItems");
                    </script>
                    ';
                return redirect()->route('tenant.product.index')->with('success', __('Bank Details Sent Successfully! Wait for approval'));
            } elseif ($gateway->slug == 'cash') {
                $order = $this->placeOrder($cartAmount, $gateway, $gatewayCurrency); // new order create
                $order->save();
                 // Loop through each product in the cart
                $this->addProductOrderItems($order, $products);
                DB::commit();
                echo '
                    <script>
                        localStorage.removeItem("cartItems");
                    </script>
                    ';
                return redirect()->route('tenant.product.index')->with('success', __('Cash Payment Request Sent Successfully! Wait for approval'));
            } elseif ($gateway->slug == 'mpesa'){
                if ($request->has('mpesa_transaction_code')) {
                    $order = $this->placeOrder($mpesaAmount, $gateway, $gatewayCurrency, null, null, null, null, null, $request->mpesa_transaction_code); // new order create
                    $order->save();
                    $this->addProductOrderItems($order, $products);
                    DB::commit();
                    echo '
                        <script>
                            localStorage.removeItem("cartItems");
                        </script>
                        ';
                    return redirect()->route('tenant.product.index')->with('success', __('Mpesa Transaction Code Submitted Successfully! Wait for approval'));
                }else{
                    $mpesaAccount = MpesaAccount::where(['owner_user_id' => $ownerId, 'gateway_id' => $gateway->id, 'id' => $request->mpesa_account_id])->first();
                    if (is_null($mpesaAccount)) {
                        throw new Exception('Mpesa Account not found');
                    }
                    $paymentData['mpesaAccount'] = $mpesaAccount;
                    $order = $this->placeOrder($cartAmount, $gateway, $gatewayCurrency);
                    $this->addProductOrderItems($order, $products);
                    DB::commit();
                    }
                
            } else {
                $order = $this->placeOrder($cartAmount, $gateway, $gatewayCurrency); // new order create
                $this->addProductOrderItems($order, $products);
                DB::commit();
            }

            $object = [
                'id' => $order->id,
                'gateway' => $gateway->slug,
                'callback_url' => route('payment.products.verify'),
                'currency' => $gatewayCurrency->currency,
                'type' => 'ProductOrder'
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
        } catch (\Exception $e) {
            Log::error('Payment failed: '.$e->getMessage(), [
            'exception' => $e, // Logs the whole exception object
            'request' => $request->all() // Optionally log request data
            ]);

            DB::rollBack();
            return redirect()->route('tenant.product.index')->with('error', __('Payment Failed!'));
        }
        
    }


    public function addProductOrderItems( $order, $products)
    {

        foreach ($products as $product) {
            // Create an order item
            $item = $order->orderItems()->create([
                'product_id' => $product['id'],
                'quantity' => $product['quantity'],
            ]);
        }
    }


    public function placeOrder( $amount, $gateway, $gatewayCurrency, $bank_id = null, $bank_name = null, $bank_account_number = null, $deposit_by = null, $deposit_slip_id = null, $mpesa_transaction_code = null)
    {

        return ProductOrder::create([
            'mpesa_transaction_code' => $mpesa_transaction_code,
            'user_id' => auth()->id(),
            'amount' => $amount,
            'system_currency' => Currency::where('current_currency', 'on')->first()->currency_code,
            'gateway_id' => $gateway->id,
            'gateway_currency' => $gatewayCurrency->currency,
            'subtotal' => $amount,
            'total' => $amount,
            'transaction_amount' => $amount * $gatewayCurrency->conversion_rate,
            'conversion_rate' => $gatewayCurrency->conversion_rate,
            'payment_status' => ORDER_PAYMENT_STATUS_PENDING,
            'bank_id' => $bank_id,
            'bank_name' => $bank_name,
            'bank_account_number' => $bank_account_number,
            'deposit_by' => $deposit_by,
            'deposit_slip_id' => $deposit_slip_id
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

        $order = ProductOrder::findOrFail($order_id);
        if ($order->payment_status == ORDER_PAYMENT_STATUS_PAID) {
            return redirect()->route('tenant.product.index')->with('success', __('$formattedGateway Payment Successful.\nProduct Order Paid!'));
        }elseif ($order->payment_status == ORDER_PAYMENT_STATUS_CANCELLED) {
            return redirect()->route('owner.subscription.index')->with('error', __('$formattedGateway Payment Declined!\nProduct Order Not Paid!'));
        }
        
        return handleProductPaymentConfirmation($order, $payerId, $gateway_slug, null);
    }
    
}
