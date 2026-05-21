<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Gateway;
use App\Models\GatewayCurrency;
use App\Models\MpesaAccount;
use App\Models\SmsCreditTransaction;
use App\Services\Payment\Payment;
use App\Services\Sms\SmsCreditsService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SmsCreditsPaymentController extends Controller
{
    public function checkout(Request $request)
    {
        $request->validate([
            'quantity'  => 'required|integer|min:10|max:10000',
            'cartTotal' => 'required|numeric|min:1',
            'phone'     => 'required|string|min:9|max:15',
        ]);

        $phone = preg_replace('/\D/', '', $request->phone); // strip non-digits
        if (str_starts_with($phone, '0')) {
            $phone = '254' . substr($phone, 1);  // 07XX → 2547XX
        } elseif (str_starts_with($phone, '+')) {
            $phone = ltrim($phone, '+');          // +254 → 254
        }

        DB::beginTransaction();
        try {
            $accountId = getOption('centresidence_mpesa_account_id');
            if (!$accountId) throw new Exception('Payments are not configured. Please contact support.');

            $mpesaAccount    = MpesaAccount::findOrFail($accountId);
            $gateway         = Gateway::findOrFail($mpesaAccount->gateway_id);
            $gatewayCurrency = GatewayCurrency::where('gateway_id', $gateway->id)->first();
            if (!$gatewayCurrency) throw new Exception('Payment currency not configured.');

            $quantity    = (int) $request->quantity;
            $amountPaid  = (float) $request->cartTotal;
            $ownerUserId = auth()->id();

            $pending = SmsCreditTransaction::create([
                'owner_user_id'  => $ownerUserId,
                'type'           => 'purchase',
                'quantity'       => $quantity,
                'amount_paid'    => $amountPaid,
                'balance_before' => SmsCreditsService::balance($ownerUserId),
                'balance_after'  => SmsCreditsService::balance($ownerUserId),
                'description'    => "Purchase of {$quantity} SMS credits",
                'status'         => 'pending',
            ]);

            DB::commit();

            $object = [
                'id'           => $pending->id,
                'gateway'      => $gateway->slug,
                'callback_url' => route('owner.sms.credits.verify'),
                'currency'     => $gatewayCurrency->currency,
                'type'         => 'SmsCreditTransaction',
                'phone'        => $phone,
            ];

            $payment     = new Payment($gateway->slug, $object);
            $paymentData = [
                'mpesaAccount' => $mpesaAccount,
                'amount'       => $amountPaid * $gatewayCurrency->conversion_rate,
                'phone' => $phone,
            ];
           
            $responseData = $payment->makePayment($paymentData);

            if ($responseData['success']) {
                $pending->update(['reference' => $responseData['payment_id']]);

                $url = $responseData['redirect_url']
                    . '&merchant_id=' . $responseData['merchant_request_id']
                    . '&checkout_id=' . $responseData['checkout_request_id']
                    . '&id=' . $pending->id;

                return response()->json([
                    'success'        => true,
                    'redirect_url'   => $url,
                    'transaction_id' => $responseData['checkout_request_id'],
                    'pending_id'     => $pending->id,
                ]);
            } else {
                $pending->update(['status' => 'failed']);
                return response()->json(['success' => false, 'error' => $responseData['message']]);
            }

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('SMS credits checkout failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => __('Payment failed. Please try again.')]);
        }
    }

    public function verify(Request $request)
    {
        $pending_id = $request->get('id', '');
        $callback   = $request->get('callback', false);
        $stkSuccess = $request->get('stk_success', false);

        if (filter_var($callback, FILTER_VALIDATE_BOOLEAN) === true) {

            $pending = SmsCreditTransaction::find($pending_id);

            if (filter_var($stkSuccess, FILTER_VALIDATE_BOOLEAN) === true) {

                if (!$pending) {
                    return redirect()->route('owner.sms.credits.index')
                        ->with('error', __('Transaction not found.'));
                }

                // Give the Safaricom callback a moment to complete first
                sleep(3);
                $pending->refresh();

                // Already handled by the callback — just report success
                if ($pending->status === 'success') {
                    return redirect()->route('owner.sms.credits.index')
                    ->with('success', 'Payment successful! ' . number_format($pending->quantity) . ' SMS credits added to your account.');
                }

                // Callback hasn't applied credits yet — use atomic lock as fallback
                if ($pending->status === 'pending') {
                    DB::beginTransaction();
                    try {
                        // Lock the row so if callback is also running,
                        // one will wait and find status !== 'pending' after lock releases
                        $locked = SmsCreditTransaction::where('id', $pending->id)
                            ->where('status', 'pending')
                            ->lockForUpdate()
                            ->first();

                        if ($locked) {
                            SmsCreditsService::addCredits(
                                $locked->owner_user_id,
                                $locked->quantity,
                                'purchase',
                                $locked->amount_paid,
                                $locked->reference ?? '',
                                "Purchase of {$locked->quantity} SMS credits",
                                $locked->id  
                            );
                            
                            DB::commit();

                            return redirect()->route('owner.sms.credits.index')
                                ->with('success', 'Payment successful! ' . number_format($locked->quantity) . ' SMS credits added to your account.');
                        }

                        // Lock acquired but status was no longer pending —
                        // callback completed while we were waiting
                        DB::commit();
                        $pending->refresh();

                        if ($pending->status === 'success') {
                            return redirect()->route('owner.sms.credits.index')
                                ->with('success', 'Payment successful! ' . number_format($pending->quantity) . ' SMS credits added to your account.');
                        }
                           

                    } catch (Exception $e) {
                        DB::rollBack();
                        Log::error('SMS credits verify fallback failed: ' . $e->getMessage());
                        return redirect()->route('owner.sms.credits.index')
                            ->with('error', __('Payment confirmed but credits could not be applied. Please contact support.'));
                    }
                }

                return redirect()->route('owner.sms.credits.index')
                    ->with('error', __('Payment could not be confirmed. Please try again.'));

            } else {
                if ($pending && $pending->status === 'pending') {
                    $pending->update(['status' => 'failed']);
                }
                return redirect()->route('owner.sms.credits.index')
                    ->with('error', __('M-Pesa payment was declined. Please try again.'));
            }
        }

        $pending = SmsCreditTransaction::findOrFail($pending_id);

        if ($pending->status === 'success') {
            return redirect()->route('owner.sms.credits.index')
                ->with('success', 'Payment successful! ' . number_format($pending->quantity) . ' SMS credits added to your account.');
        }

        if ($pending->status === 'failed') {
            return redirect()->route('owner.sms.credits.index')
                ->with('error', __('This payment did not go through. Please try purchasing again.'));
        }

        // Still pending — no callback, no STK confirmation
        if ($pending->status === 'success') {
            return redirect()->route('owner.sms.credits.index')
                ->with('success', 'Payment successful! ' . number_format($pending->quantity) . ' SMS credits added to your account.');
        }

        return redirect()->route('owner.sms.credits.index')
            ->with('error', __('Payment is still being confirmed. Please wait a moment and refresh.'));
    }
}