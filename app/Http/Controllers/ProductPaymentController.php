<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckoutRequest;
use App\Models\Currency;
use App\Models\FileManager;
use App\Models\Gateway;
use App\Models\GatewayCurrency;
use App\Models\MpesaAccount;
use App\Models\ProductOrder;
use App\Models\User;
use App\Services\CommissionService;
use App\Services\Payment\Payment;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProductPaymentController extends Controller
{
    protected CommissionService $commissionService;

    public function __construct()
    {
        $this->commissionService = new CommissionService();
    }

    public function checkout(CheckoutRequest $request)
    {
        DB::beginTransaction();
        try {
            // ── using Centresidence gateway, not owner's ──────
            // The centresidence_mpesa_account_id is set by admin in markeplace accounts settings
            // ── Gateway resolution: client hint → fallback to centresidence ──
            // If a specific mpesa_account_id is passed (future owner-selected flow),
            // use it. Otherwise fall back to the platform default.
            $accountId = $request->input('mpesa_account_id') 
                        ?? getOption('centresidence_mpesa_account_id');

            if (!$accountId) {
                throw new Exception('Marketplace payments are not configured. Please contact support.');
            }

            $mpesaAccount    = MpesaAccount::findOrFail($accountId);
            $gateway         = Gateway::findOrFail($mpesaAccount->gateway_id);
            $gatewayCurrency = GatewayCurrency::where('gateway_id', $gateway->id)->first();

            if (!$gatewayCurrency) {
                throw new Exception('Payment currency not configured. Please contact support.');
            }

            $cartAmount = $request->cartTotal;
            $products   = $request->input('products');

            // ── Place order ──────────────────────────────────────────
            $order = $this->placeOrder($cartAmount, $gateway, $gatewayCurrency);
            $this->addProductOrderItems($order, $products);
            DB::commit();

            // ── Route to STK push via centresidence account ──────────
            $paymentData['mpesaAccount'] = $mpesaAccount;

            $object = [
                'id'           => $order->id,
                'gateway'      => $gateway->slug,
                'callback_url' => route('payment.products.verify'),
                'currency'     => $gatewayCurrency->currency,
                'type'         => 'ProductOrder',
            ];

            $payment      = new Payment($gateway->slug, $object);
            $paymentData['amount'] = $order->total;
            $responseData = $payment->makePayment($paymentData);

            if ($responseData['success']) {
                $order->payment_id = $responseData['payment_id'];
                $order->save();

                $url           = $responseData['redirect_url']
                    . '&merchant_id=' . $responseData['merchant_request_id']
                    . '&checkout_id=' . $responseData['checkout_request_id'];
                $transactionId = $responseData['checkout_request_id'];

                return response()->json([
                    'success'        => true,
                    'redirect_url'   => $url,
                    'transaction_id' => $transactionId,
                    'order_id'       => $order->id,
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'error'   => $responseData['message'],
                ]);
            }

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Marketplace payment failed: ' . $e->getMessage(), [
                'exception' => $e,
                'request'   => $request->all(),
            ]);
            return response()->json([
                'success' => false,
                'error'   => __('Payment failed. Please try again.'),
            ]);
        }
    }

    public function verify(Request $request)
    {
        $order_id     = $request->get('id', '');
        $callback     = $request->get('callback', false);
        $stkSuccess   = $request->get('stk_success', false);
        $payerId      = $request->get('PayerID', NULL);
        $gateway_slug = $request->get('gateway', NULL);
        $formattedGateway = ucfirst(strtolower($gateway_slug));

        if (filter_var($callback, FILTER_VALIDATE_BOOLEAN) === true) {
            if (filter_var($stkSuccess, FILTER_VALIDATE_BOOLEAN) === true) {

                $order = ProductOrder::find($order_id);
                if ($order && $order->payment_status !== ORDER_PAYMENT_STATUS_PAID) {
                    DB::beginTransaction();
                    try {
                        $order->payment_status = ORDER_PAYMENT_STATUS_PAID;
                        $order->transaction_id = str_replace('-', '', uuid_create());
                        $order->save();

                        // ── Process commission on successful payment ─────
                        $order->load('orderItems.product');
                        $this->commissionService->processOrderCommission($order);

                        DB::commit();
                    } catch (\Exception $e) {
                        DB::rollBack();
                        Log::error('Commission processing failed: ' . $e->getMessage(), [
                            'order_id' => $order_id,
                            'trace'     => $e->getTraceAsString(),
                        ]);
                    }
                }

                return redirect()->route('tenant.product.order.receipt', $order_id)
                    ->with('success', __($formattedGateway . ' Payment Successful.'));

            } else {
                $order = ProductOrder::find($order_id);
                if ($order && $order->payment_status === ORDER_PAYMENT_STATUS_PENDING) {
                    DB::beginTransaction();
                    try {
                        $order->payment_status = PRODUCT_ORDER_STATUS_CANCELLED;
                        $order->save();
                        DB::commit();
                    } catch (\Exception $e) {
                        DB::rollBack();
                    }
                }

                return redirect()->route('tenant.product.index')
                    ->with('error', __($formattedGateway . ' STK Payment Declined!'));
            }
        }

        $order = ProductOrder::findOrFail($order_id);

        if ($order->payment_status == ORDER_PAYMENT_STATUS_PAID) {
            return redirect()->route('tenant.product.order.receipt', $order->id)
                ->with('success', __($formattedGateway . ' Payment Successful.'));
        } elseif ($order->payment_status == ORDER_PAYMENT_STATUS_CANCELLED) {
            return redirect()->route('tenant.product.index')
                ->with('error', __($formattedGateway . ' Payment Declined!'));
        }

        return handleProductPaymentConfirmation($order, $payerId, $gateway_slug, null);
    }

    public function addProductOrderItems($order, $products): void
    {
        foreach ($products as $product) {
            $order->orderItems()->create([
                'product_id' => $product['id'],
                'quantity'   => $product['quantity'],
            ]);
        }
    }

    public function placeOrder(
        $amount,
        $gateway,
        $gatewayCurrency
    ): ProductOrder {
        return ProductOrder::create([
            'user_id'            => auth()->id(),
            'amount'             => $amount,
            'system_currency'    => Currency::where('current_currency', 'on')->first()->currency_code,
            'gateway_id'         => $gateway->id,
            'gateway_currency'   => $gatewayCurrency->currency,
            'subtotal'           => $amount,
            'total'              => $amount,
            'transaction_amount' => $amount * $gatewayCurrency->conversion_rate,
            'conversion_rate'    => $gatewayCurrency->conversion_rate,
            'payment_status'     => ORDER_PAYMENT_STATUS_PENDING,
            'order_status'       => ORDER_STATUS_PENDING,
        ]);
    }
}