<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Gateway;
use App\Models\GatewayCurrency;
use App\Models\MpesaAccount;
use App\Models\OwnerCreditTransaction;
use App\Services\Credit\CreditService;
use App\Services\Payment\Payment;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * ONE M-Pesa STK top-up flow for every prepaid credit bucket (SMS, agreement, …). The
 * bucket comes from the route (->defaults('bucket', …)); all per-bucket differences —
 * price, quantity limits, redirect target — come from config/credits.php.
 *
 * Money safety (identical for every bucket): the price is SERVER-computed (quantity × the
 * admin unit price), never taken from the client; crediting happens only via the
 * authenticated M-Pesa callback or a server-side STK-confirmed fallback — never on the
 * client `stk_success` flag alone; and CreditService::addCredits is idempotent per
 * transaction id, so callback + fallback racing credit exactly once.
 */
class CreditTopUpController extends Controller
{
    public function checkout(Request $request, string $bucket)
    {
        $cfg = CreditService::config($bucket);

        $request->validate([
            'quantity' => "required|integer|min:{$cfg['min_quantity']}|max:{$cfg['max_quantity']}",
            'phone'    => 'required|string|min:9|max:15',
        ]);

        $phone = $this->normalizePhone($request->phone);

        DB::beginTransaction();
        try {
            $accountId = getOption('centresidence_mpesa_account_id');
            if (! $accountId) throw new Exception('Payments are not configured. Please contact support.');

            $mpesaAccount    = MpesaAccount::findOrFail($accountId);
            $gateway         = Gateway::findOrFail($mpesaAccount->gateway_id);
            $gatewayCurrency = GatewayCurrency::where('gateway_id', $gateway->id)->first();
            if (! $gatewayCurrency) throw new Exception('Payment currency not configured.');

            $quantity  = (int) $request->quantity;
            // SERVER-computed price — never trust a client-supplied amount.
            $unitPrice = CreditService::pricePerUnit($bucket);
            if ($unitPrice <= 0) throw new Exception($cfg['label'] . ' pricing is not configured.');
            $amountPaid  = round($quantity * $unitPrice, 2);
            $ownerUserId = auth()->id();

            $units   = Str::plural($cfg['unit'], $quantity);
            $pending = CreditService::openPendingPurchase(
                $bucket, $ownerUserId, $quantity, $amountPaid, "Purchase of {$quantity} {$units}"
            );

            DB::commit();

            $object = [
                'id'           => $pending->id,
                'gateway'      => $gateway->slug,
                'callback_url' => route($cfg['verify_route']),
                'currency'     => $gatewayCurrency->currency,
                'type'         => 'credit:' . $bucket,   // callback maps this back to the bucket
                'phone'        => $phone,
            ];

            $payment     = new Payment($gateway->slug, $object);
            $paymentData = [
                'mpesaAccount' => $mpesaAccount,
                'amount'       => $amountPaid * $gatewayCurrency->conversion_rate,
                'phone'        => $phone,
            ];

            $responseData = $payment->makePayment($paymentData);

            // Diagnostic: capture Safaricom's STK outcome so a "no prompt on my phone"
            // report is answerable from the log (success=true here means Daraja ACCEPTED the
            // push; if the prompt still never lands, the cause is Safaricom-side, not ours).
            Log::info(sprintf(
                'Credit top-up STK [%s]: qty=%d amount=%s success=%s checkout=%s msg=%s',
                $bucket, $quantity, $amountPaid,
                var_export($responseData['success'] ?? null, true),
                $responseData['checkout_request_id'] ?? '-',
                $responseData['message'] ?? '-'
            ));

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
            }

            $pending->update(['status' => 'failed']);
            return response()->json(['success' => false, 'error' => $responseData['message']]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error(ucfirst($bucket) . ' credits checkout failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => __('Payment failed. Please try again.')]);
        }
    }

    public function verify(Request $request, string $bucket)
    {
        $cfg        = CreditService::config($bucket);
        $indexRoute = $cfg['index_route'];
        $pending_id = $request->get('id', '');
        $callback   = $request->get('callback', false);
        $stkSuccess = $request->get('stk_success', false);

        // Scope to the caller's own purchase in this bucket (IDOR defence).
        $pending = OwnerCreditTransaction::where('owner_user_id', auth()->id())
            ->where('bucket', $bucket)
            ->find($pending_id);

        if (filter_var($callback, FILTER_VALIDATE_BOOLEAN) === true) {
            if (filter_var($stkSuccess, FILTER_VALIDATE_BOOLEAN) === true) {
                if (! $pending) {
                    return redirect()->route($indexRoute)->with('error', __('Transaction not found.'));
                }

                // Give the Safaricom callback a moment to land first.
                sleep(3);
                $pending->refresh();

                if ($pending->status === 'success') {
                    return redirect()->route($indexRoute)->with('success', $this->addedMessage($cfg, $pending->quantity));
                }

                if ($pending->status === 'pending') {
                    // SECURITY: the client stk_success flag cannot release credits — confirm
                    // the STK result server-side (Safaricom stkquery on the stored id) first.
                    if (! mpesaStkConfirmed($pending->reference)) {
                        return redirect()->route($indexRoute)
                            ->with('error', __('Payment is still being confirmed. Please wait a moment and refresh.'));
                    }

                    DB::beginTransaction();
                    try {
                        // Lock the row — if the callback is also running, one waits and finds
                        // status !== 'pending' after the lock releases.
                        $locked = OwnerCreditTransaction::where('id', $pending->id)
                            ->where('status', 'pending')
                            ->lockForUpdate()
                            ->first();

                        if ($locked) {
                            CreditService::addCredits($bucket, $locked->owner_user_id, (int) $locked->quantity, [
                                'type'                    => 'purchase',
                                'amount_paid'             => (float) $locked->amount_paid,
                                'reference'               => $locked->reference,
                                'payment_id'              => $locked->payment_id,
                                'description'             => $locked->description,
                                'existing_transaction_id' => $locked->id,
                            ]);
                            DB::commit();
                            return redirect()->route($indexRoute)->with('success', $this->addedMessage($cfg, $locked->quantity));
                        }

                        DB::commit();
                        $pending->refresh();
                        if ($pending->status === 'success') {
                            return redirect()->route($indexRoute)->with('success', $this->addedMessage($cfg, $pending->quantity));
                        }
                    } catch (Exception $e) {
                        DB::rollBack();
                        Log::error(ucfirst($bucket) . ' credits verify fallback failed: ' . $e->getMessage());
                        return redirect()->route($indexRoute)
                            ->with('error', __('Payment confirmed but credits could not be applied. Please contact support.'));
                    }
                }

                return redirect()->route($indexRoute)
                    ->with('error', __('Payment could not be confirmed. Please try again.'));
            }

            if ($pending && $pending->status === 'pending') {
                $pending->update(['status' => 'failed']);
            }
            return redirect()->route($indexRoute)->with('error', __('M-Pesa payment was declined. Please try again.'));
        }

        // Non-callback GET (owner returning to / refreshing the page).
        if (! $pending) {
            return redirect()->route($indexRoute)->with('error', __('Transaction not found.'));
        }
        if ($pending->status === 'success') {
            return redirect()->route($indexRoute)->with('success', $this->addedMessage($cfg, $pending->quantity));
        }
        if ($pending->status === 'failed') {
            return redirect()->route($indexRoute)->with('error', __('This payment did not go through.'));
        }

        // Still pending. With no reachable M-Pesa callback (local/dev, or a missed/late Daraja
        // callback) the credit would otherwise hang forever even though the buyer paid. Reconcile
        // it server-side: ask Safaricom whether this STK was actually paid, and if so credit now
        // (idempotent — a later callback finds it already success and no-ops).
        if ($this->applyConfirmedPending($cfg, $bucket, $pending)) {
            return redirect()->route($indexRoute)->with('success', $this->addedMessage($cfg, $pending->quantity));
        }
        return redirect()->route($indexRoute)
            ->with('error', __('Payment is still being confirmed. Please wait a moment and refresh.'));
    }

    /**
     * Confirm a pending purchase server-side (Safaricom STK status query) and credit it exactly
     * once under a lock. Returns true if the balance ended up credited (or was already success).
     * This is the callback-independent safety net — a genuinely-paid top-up lands on any
     * return/refresh, never depending on the async callback reaching us.
     */
    private function applyConfirmedPending(array $cfg, string $bucket, OwnerCreditTransaction $pending): bool
    {
        if ($pending->status === 'success') {
            return true;
        }
        if ($pending->status !== 'pending' || ! mpesaStkConfirmed($pending->reference)) {
            return false;
        }

        DB::beginTransaction();
        try {
            $locked = OwnerCreditTransaction::where('id', $pending->id)
                ->where('status', 'pending')
                ->lockForUpdate()
                ->first();

            if ($locked) {
                CreditService::addCredits($bucket, $locked->owner_user_id, (int) $locked->quantity, [
                    'type'                    => 'purchase',
                    'amount_paid'             => (float) $locked->amount_paid,
                    'reference'               => $locked->reference,
                    'payment_id'              => $locked->payment_id,
                    'description'             => $locked->description,
                    'existing_transaction_id' => $locked->id,
                ]);
            }
            DB::commit();
            $pending->refresh();
            return $pending->status === 'success';
        } catch (Exception $e) {
            DB::rollBack();
            Log::error(ucfirst($bucket) . ' credits reconcile failed: ' . $e->getMessage());
            return false;
        }
    }

    private function addedMessage(array $cfg, int $quantity): string
    {
        return number_format($quantity) . ' ' . Str::plural($cfg['unit'], $quantity) . ' ' . __('added.');
    }

    /** 07XX / +2547XX / 2547XX → 2547XX (digits only). */
    private function normalizePhone(string $raw): string
    {
        $phone = preg_replace('/\D/', '', $raw);
        if (str_starts_with($phone, '0')) {
            return '254' . substr($phone, 1);
        }
        if (str_starts_with($raw, '+')) {
            return ltrim($phone, '+');
        }
        return $phone;
    }
}
