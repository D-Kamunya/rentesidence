<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckoutRequest;
use App\Http\Requests\InstantCheckoutRequest;
use App\Models\Bank;
use App\Models\MpesaAccount;
use App\Models\Currency;
use App\Models\FileManager;
use App\Models\Gateway;
use App\Models\GatewayCurrency;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\WalletTransaction;
use App\Services\CommissionService;
use App\Services\Payment\Payment;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    protected CommissionService $commissionService;

    public function __construct()
    {
        $this->commissionService = new CommissionService();
    }

    // ──────────────────────────────────────────────────────────────────────────
    // CHECKOUT (authenticated tenant)
    // ──────────────────────────────────────────────────────────────────────────

    public function checkout(CheckoutRequest $request)
    {
        $invoice = Invoice::where('owner_user_id', auth()->user()->owner_user_id)
            ->where('tenant_id', auth()->user()->tenant->id)
            ->findOrFail($request->invoice_id);

        // ── Transaction model: resolve company gateway and return early ────────
        // STK push fires to the company's M-Pesa account.
        // Completely bypasses owner gateway lookup — nothing below this block
        // runs for transaction-model tenants.
        $isRentTransaction = $this->ownerIsTransactionModel($invoice);


        if ($isRentTransaction) {
            DB::beginTransaction();
            try {
                $rentGateway                 = $this->centresidenceRentGateway();
                $gateway                     = $rentGateway['gateway'];
                $gatewayCurrency             = $rentGateway['gatewayCurrency'];
                $paymentData['mpesaAccount'] = $rentGateway['mpesaAccount'];
                $order                       = $this->placeOrder($invoice, $gateway, $gatewayCurrency);
                $invoice->order_id = $order->id;
                $invoice->save();
                
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Checkout transaction model failed: ' . $e->getMessage());
                return response()->json([
                    'success' => false,
                    'error'   => $e->getMessage(),
                ]);
            }

            // Fire STK push to company account
            $object = [
                'id'                  => $order->id,
                'gateway'             => $gateway->slug,
                'callback_url'        => route('payment.verify'),
                'currency'            => $gatewayCurrency->currency,
                'type'                => 'RentPayment',
                'is_rent_transaction' => true,
            ];

            $payment               = new Payment($gateway->slug, $object);
            $paymentData['amount'] = $order->total;
            $responseData          = $payment->makePayment($paymentData);

            if ($responseData['success']) {
                $order->payment_id = $responseData['payment_id'];
                $order->save();

                $url = $responseData['redirect_url']
                     . '&merchant_id='         . $responseData['merchant_request_id']
                     . '&checkout_id='         . $responseData['checkout_request_id']
                     . '&is_rent_transaction=1';

                return response()->json([
                    'success'             => true,
                    'redirect_url'        => $url,
                    'transaction_id'      => $responseData['checkout_request_id'],
                    'is_rent_transaction' => true,
                ]);
            }

            return response()->json([
                'success' => false,
                'error'   => $responseData['message'],
            ]);
        }

        // ── Standard path: owner's own gateway ────────────────────────────────
        // Only reached for subscription-model owners.
        // STK push fires to the owner's own M-Pesa account.
        $gateway = Gateway::where([
            'owner_user_id' => auth()->user()->owner_user_id,
            'slug'          => $request->gateway,
            'status'        => ACTIVE,
        ])->firstOrFail();

        $gatewayCurrency = GatewayCurrency::where([
            'owner_user_id' => auth()->user()->owner_user_id,
            'gateway_id'    => $gateway->id,
            'currency'      => $request->currency,
        ])->firstOrFail();

        // ── Bank ──────────────────────────────────────────────────────────────
        if ($gateway->slug == 'bank') {
            DB::beginTransaction();
            try {
                $bank                = Bank::where(['gateway_id' => $gateway->id, 'id' => $request->bank_id])->firstOrFail();
                $bank_id             = $bank->id;
                $bank_name           = $bank->name;
                $bank_account_number = $bank->bank_account_number;
                $deposit_by          = $request->deposit_by;
                $deposit_slip_id     = null;

                if ($request->hasFile('bank_slip')) {
                    $newFile = new FileManager();
                    $upload  = $newFile->upload('Order', $request->bank_slip);
                    if ($upload['status']) {
                        $deposit_slip_id             = $upload['file']->id;
                        $upload['file']->origin_type = "App\Models\Order";
                        $upload['file']->save();
                    } else {
                        throw new Exception($upload['message']);
                    }
                } else {
                    throw new Exception('The Bank slip is required');
                }

                $order                  = $this->placeOrder($invoice, $gateway, $gatewayCurrency, $bank_id, $bank_name, $bank_account_number, $deposit_by, $deposit_slip_id);
                $order->deposit_slip_id = $deposit_slip_id;
                $order->save();
                $invoice->order_id      = $order->id;
                $invoice->save();
                DB::commit();

                return redirect()->route('tenant.invoice.index')
                    ->with('success', __('Bank Details Sent Successfully! Wait for approval'));
            } catch (\Exception $e) {
                DB::rollBack();
                return redirect()->route('tenant.invoice.index')->with('error', __('Payment Failed!'));
            }

        // ── M-Pesa (subscription model — STK push to owner's own account) ────
        } elseif ($gateway->slug == 'mpesa') {

            // Manual transaction code submission — no STK push
            if ($request->has('mpesa_transaction_code')) {
                $order = $this->placeOrder($invoice, $gateway, $gatewayCurrency, null, null, null, null, null, $request->mpesa_transaction_code);
                $order->save();
                return redirect()->route('tenant.invoice.index')
                    ->with('success', __('Mpesa Transaction Code Submitted Successfully! Wait for approval'));
            }

            // STK push to owner's own M-Pesa account
            DB::beginTransaction();
            try {
                $mpesaAccount = MpesaAccount::where([
                    'gateway_id' => $gateway->id,
                    'id'         => $request->mpesa_account_id,
                ])->first();

                if (is_null($mpesaAccount)) {
                    throw new Exception('Mpesa Account not found');
                }

                $paymentData['mpesaAccount'] = $mpesaAccount;
                $order                       = $this->placeOrder($invoice, $gateway, $gatewayCurrency);
                
                $invoice->order_id = $order->id;
                $invoice->save();
                
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Checkout mpesa setup failed: ' . $e->getMessage());
                return response()->json([
                    'success' => false,
                    'error'   => __('Payment Failed. Please try again.'),
                ]);
            }

        // ── Cash ──────────────────────────────────────────────────────────────
        } elseif ($gateway->slug == 'cash') {
            $order             = $this->placeOrder($invoice, $gateway, $gatewayCurrency);
            $invoice->order_id = $order->id;
            $invoice->save();
            return redirect()->route('tenant.invoice.index')
                ->with('success', __('Cash Payment Request Sent Successfully! Wait for approval'));

        // ── Other gateways ────────────────────────────────────────────────────
        } else {
            $order = $this->placeOrder($invoice, $gateway, $gatewayCurrency);
        }

        // ── Fire payment for subscription-model owners ─────────────────────────
        // Reached by: mpesa STK push and other non-bank/cash gateways.
        // Bank and cash return early above; transaction model returns early at top.
        $object = [
            'id'                  => $order->id,
            'gateway'             => $gateway->slug,
            'callback_url'        => route('payment.verify'),
            'currency'            => $gatewayCurrency->currency,
            'type'                => 'RentPayment',
            'is_rent_transaction' => false,
        ];

        $payment               = new Payment($gateway->slug, $object);
        $paymentData['amount'] = $order->total;
        $responseData          = $payment->makePayment($paymentData);

        if ($responseData['success']) {
            $order->payment_id = $responseData['payment_id'];
            $order->save();

            if ($gateway->slug == 'mpesa') {
                $url = $responseData['redirect_url']
                     . '&merchant_id='         . $responseData['merchant_request_id']
                     . '&checkout_id='         . $responseData['checkout_request_id']
                     . '&is_rent_transaction=0';

                return response()->json([
                    'success'             => true,
                    'redirect_url'        => $url,
                    'transaction_id'      => $responseData['checkout_request_id'],
                    'is_rent_transaction' => false,
                ]);
            }

            return redirect($responseData['redirect_url']);

        } else {
            if ($gateway->slug == 'mpesa') {
                return response()->json([
                    'success' => false,
                    'error'   => $responseData['message'],
                ]);
            }
            return redirect()->back()->with('error', $responseData['message']);
        }
    }

    // ──────────────────────────────────────────────────────────────────────────
    // INSTANT CHECKOUT (unauthenticated external link)
    // ──────────────────────────────────────────────────────────────────────────

    public function instantCheckout(InstantCheckoutRequest $request, string $token)
    {
        $invoice = Invoice::where('payment_token', $token)
            ->where('payment_token_expires_at', '>', now())
            ->where('status', [INVOICE_STATUS_PENDING, INVOICE_STATUS_OVER_DUE])
            ->firstOrFail();

        // ── Transaction model: resolve company gateway and return early ────────
        // Mirrors the same pattern as checkout(). Owner may have no M-Pesa
        // gateway of their own — we must not look it up before checking this.
        $isRentTransaction = $this->ownerIsTransactionModel($invoice);

        if ($isRentTransaction) {
            try {
                $rentGateway                 = $this->centresidenceRentGateway();
                $gateway                     = $rentGateway['gateway'];
                $gatewayCurrency             = $rentGateway['gatewayCurrency'];
                $paymentData['mpesaAccount'] = $rentGateway['mpesaAccount'];
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'error'   => $e->getMessage(),
                ], 422);
            }
        } else {
            // ── Standard: owner's own M-Pesa gateway ──────────────────────────
            $gateway = Gateway::where([
                'owner_user_id' => $invoice->owner_user_id,
                'slug'          => 'mpesa',
                'status'        => ACTIVE,
            ])->first();

            if (!$gateway) {
                return response()->json([
                    'success' => false,
                    'error'   => 'M-Pesa payment is currently unavailable. Please contact your landlord.',
                ], 422);
            }

            $gatewayCurrency = GatewayCurrency::where([
                'owner_user_id' => $invoice->owner_user_id,
                'gateway_id'    => $gateway->id,
                'currency'      => 'KES',
            ])->first();

            if (!$gatewayCurrency) {
                return response()->json([
                    'success' => false,
                    'error'   => 'M-Pesa payment is currently unavailable. Please contact your landlord.',
                ], 422);
            }

            $mpesaAccount = MpesaAccount::where(['gateway_id' => $gateway->id])->first();
            if (is_null($mpesaAccount)) {
                return response()->json([
                    'success' => false,
                    'error'   => 'Mpesa Account not found',
                ], 422);
            }
            $paymentData['mpesaAccount'] = $mpesaAccount;
        }

        $paymentData['mpesaNumber'] = $request->mpesa_number;
        $order = $this->placeOrder($invoice, $gateway, $gatewayCurrency);

        $object = [
            'id'                  => $order->id,
            'owner_id'            => $invoice->owner_user_id,
            'gateway'             => $gateway->slug,
            'callback_url'        => route('payment.verify'),
            'currency'            => $gatewayCurrency->currency,
            'type'                => 'RentPayment',
            'is_rent_transaction' => $isRentTransaction,
        ];

        $payment               = new Payment($gateway->slug, $object);
        $paymentData['amount'] = $order->total;
        $responseData          = $payment->makePayment($paymentData);

        if ($responseData['success']) {
            $order->payment_id = $responseData['payment_id'];
            $order->save();

            $url = $responseData['redirect_url']
                 . '&merchant_id='         . $responseData['merchant_request_id']
                 . '&checkout_id='         . $responseData['checkout_request_id']
                 . '&payment_token='       . $invoice->payment_token
                 . '&is_rent_transaction=' . ($isRentTransaction ? '1' : '0');

            return response()->json([
                'success'             => true,
                'redirect_url'        => $url,
                'transaction_id'      => $responseData['checkout_request_id'],
                'is_rent_transaction' => $isRentTransaction,
            ]);
        }

        return response()->json([
            'success' => false,
            'error'   => $responseData['message'],
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // VERIFY (STK callback redirect)
    // ──────────────────────────────────────────────────────────────────────────

    public function verify(Request $request)
    {
        $order_id          = $request->get('id');
        $callback          = $request->get('callback', false);
        $stkSuccess        = $request->get('stk_success', false);
        $payerId           = $request->get('PayerID', null);
        $payment_id        = $request->get('paymentId', null);
        $gateway_slug      = $request->get('gateway', null);
        $merchant_id       = $request->get('merchant_id', null);
        $checkout_id       = $request->get('checkout_id', null);
        $formattedGateway  = ucfirst(strtolower($gateway_slug));
        $payment_token     = $request->get('payment_token', null);
        $isRentTransaction = filter_var($request->get('is_rent_transaction', false), FILTER_VALIDATE_BOOLEAN);

        $redirect = auth()->check()
            ? route('tenant.invoice.index')
            : route('instant.invoice.pay', ['token' => $payment_token]);

        // ── Guard: missing order ID ────────────────────────────────────────────
        // Prevents findOrFail('') throwing an unhandled ModelNotFoundException
        // when someone hits the verify URL with no id parameter.
        if (!$order_id) {
            return redirect($redirect)->with('error', __('Invalid payment reference.'));
        }

        // ── Pusher callback branch ─────────────────────────────────────────────
        if (filter_var($callback, FILTER_VALIDATE_BOOLEAN) === true) {

            if (filter_var($stkSuccess, FILTER_VALIDATE_BOOLEAN) === true) {

                $order = Order::find($order_id);
                if ($order && $order->payment_status !== INVOICE_STATUS_PAID) {
                    DB::beginTransaction();
                    try {
                        $order->payment_status = INVOICE_STATUS_PAID;
                        $order->save();

                        // Commission guard — idempotent, safe to call from
                        // multiple code paths (Pusher + polling fallback).
                        if ($isRentTransaction) {
                            $alreadyProcessed = WalletTransaction::where('invoice_order_id', $order->id)->exists();
                            if (!$alreadyProcessed) {
                                $this->commissionService->processRentCommission($order);
                            }
                        }

                        DB::commit();
                    } catch (\Exception $e) {
                        DB::rollBack();
                        Log::error('Rent commission failed in verify (Pusher branch)', [
                            'order_id' => $order_id,
                            'error'    => $e->getMessage(),
                            'trace'    => $e->getTraceAsString(),
                        ]);
                        // Do NOT return error — payment succeeded, commission is secondary
                    }
                }

                return redirect($redirect)
                    ->with('success', __($formattedGateway . ' STK Payment Successful. Rent Paid!'));

            } else {
                return redirect($redirect)
                    ->with('error', __($formattedGateway . ' STK Payment Declined! Rent Not Paid'));
            }
        }

        // ── Non-callback branch (polling fallback) ─────────────────────────────
        // handlePaymentConfirmation() already handles commission with its own
        // idempotency guard, so no additional commission call is needed here.
        $order = Order::findOrFail($order_id);

        if ($order->payment_status == INVOICE_STATUS_PAID) {
            return redirect($redirect)
                ->with('success', __($formattedGateway . ' Payment Successful. Rent Paid!'));
        }

        if ($order->payment_status == ORDER_PAYMENT_STATUS_CANCELLED) {
            return redirect($redirect)
                ->with('error', __($formattedGateway . ' Payment Declined! Rent Not Paid'));
        }

        return handlePaymentConfirmation($order, $payment_token, $payerId, $gateway_slug, null, $isRentTransaction);
    }

    public function verifyRedirect($type = 'error')
    {
        $data['type']    = $type;
        $data['message'] = $type === 'success' ? __('Payment Successful!') : __('Payment Failed!');
        return view('common.verify-redirect', $data);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // PRIVATE HELPERS
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Place an invoice order.
     */
    public function placeOrder(
        $invoice,
        $gateway,
        $gatewayCurrency,
        $bank_id = null,
        $bank_name = null,
        $bank_account_number = null,
        $deposit_by = null,
        $deposit_slip_id = null,
        $mpesa_transaction_code = null
    ) {
        return Order::create([
            'user_id'                => auth()->id() ?? $invoice->tenant->user_id,
            'invoice_id'             => $invoice->id,
            'amount'                 => $invoice->amount,
            'system_currency'        => Currency::where('current_currency', 'on')->first()->currency_code,
            'gateway_id'             => $gateway->id,
            'gateway_currency'       => $gatewayCurrency->currency,
            'conversion_rate'        => $gatewayCurrency->conversion_rate,
            'subtotal'               => $invoice->amount,
            'total'                  => $invoice->amount,
            'transaction_amount'     => $invoice->amount * $gatewayCurrency->conversion_rate,
            'payment_status'         => INVOICE_STATUS_PENDING,
            'bank_id'                => $bank_id,
            'bank_name'              => $bank_name,
            'bank_account_number'    => $bank_account_number,
            'deposit_by'             => $deposit_by,
            'deposit_slip_id'        => $deposit_slip_id,
            'mpesa_transaction_code' => $mpesa_transaction_code,
        ]);
    }

    /**
     * Check if the owner of this invoice is on a transaction pricing model.
     * Looks up the owner's active subscription in owner_packages.
     */
    private function ownerIsTransactionModel(Invoice $invoice): bool
    {
        $subscription = DB::table('owner_packages')
            ->where('user_id', $invoice->owner_user_id)
            ->where('status', 1)
            ->latest()
            ->first();

        $pricingModel = $subscription?->pricing_model ?? 'free';
        return $pricingModel === 'transaction';
    }

    /**
     * Resolve the centresidence rent M-Pesa account, gateway, and currency.
     * Throws a descriptive Exception if not configured — caller must handle.
     *
     * @return array{mpesaAccount: MpesaAccount, gateway: Gateway, gatewayCurrency: GatewayCurrency}
     * @throws Exception
     */
    private function centresidenceRentGateway(): array
    {
        $accountId = getOption('centresidence_rent_mpesa_account_id');

        if (!$accountId) {
            throw new Exception(
                'Rent transaction payments are not configured. Please contact support.'
            );
        }

        $mpesaAccount    = MpesaAccount::findOrFail($accountId);
        $gateway         = Gateway::findOrFail($mpesaAccount->gateway_id);
        $gatewayCurrency = GatewayCurrency::where('gateway_id', $gateway->id)->first();

        if (!$gatewayCurrency) {
            throw new Exception(
                'Rent payment currency not configured. Please contact support.'
            );
        }

        return compact('mpesaAccount', 'gateway', 'gatewayCurrency');
    }
}