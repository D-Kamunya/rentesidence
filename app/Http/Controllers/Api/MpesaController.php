<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Models\SubscriptionOrder;
use App\Models\Gateway;
use App\Models\Package;
use Illuminate\Support\Facades\DB;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\ProductOrder;
use App\Models\User;
use App\Services\SmsMail\MailService;
use App\Models\EmailTemplate;
use App\Events\MpesaTransactionProcessed;
use App\Jobs\SendSmsJob;
use App\Jobs\SendPaymentsSuccessEmailJob;
use App\Jobs\SendInvoiceNotificationAndEmailJob;
use App\Models\OwnerCreditTransaction;
use App\Services\Credit\CreditService;
use App\Models\AffiliateWithdrawal;
use App\Models\WithdrawalRequest;
use App\Models\WalletTransaction;
use App\Jobs\SendWalletNotificationJob;

class MpesaController extends Controller
{
    public function MpesaPaymentConfirm(Request $request)
    {
        
        $response    = json_decode($request->getContent(), true);
        $resultCode  = $response['Body']['stkCallback']['ResultCode'];
        $paymentId   = $response['Body']['stkCallback']['CheckoutRequestID'];
        $orderId     = $request->get('id', '');
        $paymentType = $request->get('type', '');

        // The real M-Pesa receipt code lives in the STK callback metadata (present only on a
        // successful ResultCode==0). Pull it out so it can be stored on the order — previously
        // only a random uuid was written to transaction_id and the actual code was discarded,
        // so the M-Pesa code never showed on the orders pages.
        $mpesaReceipt = null;
        foreach (($response['Body']['stkCallback']['CallbackMetadata']['Item'] ?? []) as $item) {
            if (($item['Name'] ?? null) === 'MpesaReceiptNumber') {
                $mpesaReceipt = $item['Value'] ?? null;
                break;
            }
        }

        $originalQueueConnection = config('queue.default');

        try {
            if ($paymentType == 'subscription') {
                $order   = SubscriptionOrder::findOrFail($orderId);
                $this->assertCallbackAuthentic($order->payment_id, $paymentId);
                $gateway = Gateway::find($order->gateway_id);
                if ($resultCode == 0) {
                    if ($order->payment_status == ORDER_PAYMENT_STATUS_PENDING) {
                        DB::beginTransaction();
                        $order->payment_id    = $paymentId;
                        $order->payment_status = ORDER_PAYMENT_STATUS_PAID;
                        $order->transaction_id = str_replace('-', '', uuid_create());
                        $order->mpesa_transaction_code = $mpesaReceipt;
                        $order->save();

                        $package  = Package::find($order->package_id);
                        $duration = 0;
                        if ($order->duration_type == PACKAGE_DURATION_TYPE_MONTHLY) {
                            $duration = 30;
                        } elseif ($order->duration_type == PACKAGE_DURATION_TYPE_YEARLY) {
                            $duration = 365;
                        }

                        setUserPackage($order->user_id, $package, $duration, $order->quantity, $order->id);
                        DB::commit();

                        config(['queue.default' => 'sync']);
                        $success = true;
                        MpesaTransactionProcessed::dispatch($order, $success);
                        config(['queue.default' => $originalQueueConnection]);

                        $invoiceUrl = route('owner.subscription.index');
                        $title      = __("Subscription activated");
                        $body       = __("Your subscription payment was received — your plan is now active.");
                        $adminUser  = User::where('role', USER_ROLE_ADMIN)->first();
                        addNotification($title, $body, $invoiceUrl, null, $order->user_id, $adminUser->id);

                        if (getOption('send_email_status', 0) == ACTIVE) {
                            $emails  = [$order->user->email];
                            $subject = __('Subscription Payment Successful!');
                            $title   = __('Congratulations!');
                            $message = __('You have successfully made the payment');
                            $method  = $gateway->slug;
                            $status  = 'Paid';
                            $amount  = $order->amount;

                            SendPaymentsSuccessEmailJob::dispatch(
                                $emails, $subject, $message, $title, $method,
                                $status, $amount, $paymentType, $order, $duration
                            );
                        }
                    }
                } elseif ($resultCode == 1032) {
                    DB::beginTransaction();
                    $order->payment_id    = $paymentId;
                    $order->payment_status = ORDER_PAYMENT_STATUS_CANCELLED;
                    $order->transaction_id = str_replace('-', '', uuid_create());
                    $order->mpesa_transaction_code = $mpesaReceipt;
                    $order->save();
                    DB::commit();

                    config(['queue.default' => 'sync']);
                    $success = false;
                    MpesaTransactionProcessed::dispatch($order, $success);
                    config(['queue.default' => $originalQueueConnection]);
                } else {
                    DB::beginTransaction();
                    $order->payment_id    = $paymentId;
                    $order->transaction_id = str_replace('-', '', uuid_create());
                    $order->mpesa_transaction_code = $mpesaReceipt;
                    $order->save();
                    DB::commit();

                    config(['queue.default' => 'sync']);
                    $success = false;
                    MpesaTransactionProcessed::dispatch($order, $success);
                    config(['queue.default' => $originalQueueConnection]);
                }

            } elseif ($paymentType == 'RentPayment') {
                $order   = Order::findOrFail($orderId);
                $this->assertCallbackAuthentic($order->payment_id, $paymentId);
                $gateway = Gateway::find($order->gateway_id);
                $invoice = Invoice::find($order->invoice_id);
                if ($resultCode == 0) {
                    if ($order->payment_status == ORDER_PAYMENT_STATUS_PENDING) {
                        DB::beginTransaction();
                        $order->payment_id    = $paymentId;
                        $order->payment_status = ORDER_PAYMENT_STATUS_PAID;
                        $order->transaction_id = str_replace('-', '', uuid_create());
                        $order->mpesa_transaction_code = $mpesaReceipt;
                        $order->save();
                        $invoice->status   = INVOICE_STATUS_PAID;
                        $invoice->order_id = $order->id;
                        $invoice->save();
                        DB::commit();

                        // ââ Rent commission âââââââââââââââââââââââââââââââââ
                        $isRentTransaction = $this->ownerIsTransactionModel($invoice);
                        if ($isRentTransaction) {
                            try {
                                $commissionService = new \App\Services\CommissionService();
                                $commissionService->processRentCommission($order);
                            } catch (\Exception $e) {
                                Log::error('Rent commission failed in webhook', [
                                    'order_id' => $order->id,
                                    'error'    => $e->getMessage(),
                                ]);
                            }
                        }

                        // Centresidence facility repayment / settlement: the webhook is
                        // the authoritative STK path, so it must deduct active facility
                        // repayments from this rent too (not just the polling fallback in
                        // handlePaymentConfirmation). Scoped to the RENT portion only —
                        // late fees, deposits and other charges pass through untouched.
                        // Idempotent per order; a no-op when there are no obligations.
                        $rentPortion = min((float) $invoice->rentPortion(), (float) $order->transaction_amount);
                        if (
                            $isRentTransaction && config('centresidence.enabled', true) && $rentPortion > 0
                            && app(\App\Centresidence\Services\PaymentModeService::class)
                                ->isTransactionMode((int) $invoice->owner_user_id)
                        ) {
                            try {
                                app(\App\Centresidence\Services\RentSettlementService::class)->handleRentPayment(
                                    (int) $invoice->property_id,
                                    (int) $invoice->owner_user_id,
                                    \App\Centresidence\Support\Money::fromDecimal((string) $rentPortion),
                                    ['rent_transaction_id' => (int) $order->id]
                                );
                            } catch (\Throwable $settlementException) {
                                Log::error('Centresidence rent settlement failed in webhook', [
                                    'order_id' => $order->id,
                                    'error'    => $settlementException->getMessage(),
                                ]);
                            }
                        }
                        // ââââââââââââââââââââââââââââââââââââââââââââââââââââ

                        config(['queue.default' => 'sync']);
                        $success = true;
                        MpesaTransactionProcessed::dispatch($order, $success);
                        config(['queue.default' => $originalQueueConnection]);

                        // In-app bell notification (mirrors the marketplace order flow).
                        addNotification(
                            __('Rent payment successful'),
                            $invoice->invoice_no . ' ' . __('paid successfully'),
                            route('tenant.invoice.receipt', $invoice->id),
                            null,
                            $order->user_id,
                            $invoice->owner_user_id
                        );

                        // Rent receipt email â the single tenant-facing payment email
                        // (mirrors the product order success email). Not paired with the
                        // generic invoice email, to avoid double-emailing on one payment.
                        if (getOption('send_email_status', 0) == ACTIVE) {
                            SendPaymentsSuccessEmailJob::dispatch(
                                [$order->user->email],
                                __('Invoice Payment Successful!'),
                                __('You have successfully made your payment.'),
                                __('Congratulations!'),
                                $gateway->slug,
                                'Paid',
                                $order->amount,
                                'RentPayment',
                                $order
                            );
                        }
                    }
                } elseif ($resultCode == 1032) {
                    DB::beginTransaction();
                    $order->payment_id    = $paymentId;
                    $order->payment_status = ORDER_PAYMENT_STATUS_CANCELLED;
                    $order->transaction_id = str_replace('-', '', uuid_create());
                    $order->mpesa_transaction_code = $mpesaReceipt;
                    $order->save();
                    DB::commit();

                    config(['queue.default' => 'sync']);
                    $success = false;
                    MpesaTransactionProcessed::dispatch($order, $success);
                    config(['queue.default' => $originalQueueConnection]);
                } else {
                    DB::beginTransaction();
                    $order->payment_id    = $paymentId;
                    $order->transaction_id = str_replace('-', '', uuid_create());
                    $order->mpesa_transaction_code = $mpesaReceipt;
                    $order->save();
                    DB::commit();

                    config(['queue.default' => 'sync']);
                    $success = false;
                    MpesaTransactionProcessed::dispatch($order, $success);
                    config(['queue.default' => $originalQueueConnection]);
                }

            } elseif ($paymentType == 'ProductOrder') {
                $order       = ProductOrder::findOrFail($orderId);
                $this->assertCallbackAuthentic($order->payment_id, $paymentId);
                $gateway     = Gateway::find($order->gateway_id);
                $ownerNumber = $order->gateway->owner->contact_number;
                if ($resultCode == 0) {
                    if ($order->payment_status == ORDER_PAYMENT_STATUS_PENDING) {
                        DB::beginTransaction();
                        $order->payment_id    = $paymentId;
                        $order->payment_status = ORDER_PAYMENT_STATUS_PAID;
                        $order->transaction_id = str_replace('-', '', uuid_create());
                        $order->mpesa_transaction_code = $mpesaReceipt;
                        $order->save();
                        DB::commit();
                        
                        // ââ Product commission âââââââââââââââââââââââââââââââ
                        try {
                            $order->load('orderItems.product');
                            $commissionService = new \App\Services\CommissionService();
                            $commissionService->holdOnPayment($order); // escrow: hold until delivered
                        } catch (\Exception $e) {
                            Log::error('Product commission failed in webhook', [
                                'order_id' => $order->id,
                                'error'    => $e->getMessage(),
                            ]);
                        }
                        // ââââââââââââââââââââââââââââââââââââââââââââââââââââ

                        config(['queue.default' => 'sync']);
                        $success = true;
                        MpesaTransactionProcessed::dispatch($order, $success);
                        config(['queue.default' => $originalQueueConnection]);

                        $invoiceUrl  = route('tenant.order.index');
                        $title       = __("Payment received");
                        $body        = __("Your product order payment was received successfully.");
                        $ownerUserID = $gateway->owner_user_id;
                        addNotification($title, $body, $invoiceUrl, null, $order->user_id, $ownerUserID);

                        if (getOption('send_email_status', 0) == ACTIVE) {
                            $emails       = [$order->user->email];
                            $subject      = __('Product Payment Successful!');
                            $title        = __('Congratulations!');
                            $message      = __('You have successfully made the product order payment');
                            $tenantUserId = $order->user_id;
                            $method       = $gateway->slug;
                            $status       = 'Paid';
                            $amount       = $order->amount;

                            SendPaymentsSuccessEmailJob::dispatch(
                                $emails, $subject, $message, $title, $method,
                                $status, $amount, $paymentType, $order
                            );
                        }

                        $message = __('New product order :id from :app. Please dispatch.', ['id' => $order->order_id, 'app' => getOption('app_name') ?: 'Centresidence']);
                        SendSmsJob::dispatch([$ownerNumber], $message, $tenantUserId);
                    }
                } elseif ($resultCode == 1032) {
                    DB::beginTransaction();
                    $order->payment_id    = $paymentId;
                    $order->payment_status = ORDER_PAYMENT_STATUS_CANCELLED;
                    $order->transaction_id = str_replace('-', '', uuid_create());
                    $order->mpesa_transaction_code = $mpesaReceipt;
                    $order->save();
                    DB::commit();

                    config(['queue.default' => 'sync']);
                    $success = false;
                    MpesaTransactionProcessed::dispatch($order, $success);
                    config(['queue.default' => $originalQueueConnection]);
                } else {
                    DB::beginTransaction();
                    $order->payment_id    = $paymentId;
                    $order->transaction_id = str_replace('-', '', uuid_create());
                    $order->mpesa_transaction_code = $mpesaReceipt;
                    $order->save();
                    DB::commit();

                    config(['queue.default' => 'sync']);
                    $success = false;
                    MpesaTransactionProcessed::dispatch($order, $success);
                    config(['queue.default' => $originalQueueConnection]);
                }

            } elseif (str_starts_with((string) $paymentType, 'credit:')) {

                // Unified prepaid-credit top-up (SMS, agreement, ...). The bucket is encoded
                // in the callback `type` (credit:<bucket>) that the checkout set. One
                // idempotent path credits any bucket exactly once, whether this callback or
                // the browser verify() fallback wins the race.
                $bucket = substr((string) $paymentType, 7);
                CreditService::config($bucket); // reject an unknown/forged bucket

                $pending = OwnerCreditTransaction::where('bucket', $bucket)->findOrFail($orderId);
                $this->assertCallbackAuthentic($pending->reference, $paymentId);

                if ($resultCode == 0) {
                    DB::beginTransaction();
                    try {
                        // Atomic lock - if verify() is also running, one waits and finds
                        // status !== 'pending' after the lock releases.
                        $locked = OwnerCreditTransaction::where('id', $orderId)
                            ->where('bucket', $bucket)
                            ->where('status', 'pending')
                            ->lockForUpdate()
                            ->first();

                        if ($locked) {
                            CreditService::addCredits($bucket, $locked->owner_user_id, (int) $locked->quantity, [
                                'type'                    => 'purchase',
                                'amount_paid'             => (float) $locked->amount_paid,
                                'reference'               => $locked->reference,
                                'payment_id'              => $paymentId,
                                'description'             => $locked->description,
                                'existing_transaction_id' => $locked->id,
                            ]);
                            DB::commit();

                            // Accelerate the buyer's countdown page (Pusher) - broadcasts on
                            // transaction.{payment_id}, which the buy JS subscribes to.
                            $locked->refresh();
                            config(['queue.default' => 'sync']);
                            MpesaTransactionProcessed::dispatch($locked, true);
                            config(['queue.default' => $originalQueueConnection]);
                        } else {
                            // Already finalized by the verify() fallback.
                            DB::commit();
                        }
                    } catch (\Exception $e) {
                        DB::rollBack();
                        Log::error(ucfirst($bucket) . ' credits callback failed: ' . $e->getMessage());

                        config(['queue.default' => 'sync']);
                        MpesaTransactionProcessed::dispatch($pending, false);
                        config(['queue.default' => $originalQueueConnection]);
                    }
                } else {
                    $pending->update(['status' => 'failed', 'payment_id' => $paymentId]);

                    config(['queue.default' => 'sync']);
                    MpesaTransactionProcessed::dispatch($pending, false);
                    config(['queue.default' => $originalQueueConnection]);
                }
            }

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            config(['queue.default' => $originalQueueConnection]);
        } finally {
            config(['queue.default' => $originalQueueConnection]);
        }
    }

    /**
     * Reject a forged / mismatched STK callback. M-Pesa echoes the CheckoutRequestID
     * it issued when the STK push was initiated; we stored that same value on the
     * order (`payment_id`) / SMS pending (`reference`) at init time, and a genuine
     * callback always carries it back. Requiring the match stops an attacker from
     * POSTing a fake `ResultCode: 0` against a guessable, sequential order id to
     * mark it paid without ever paying â this endpoint is unauthenticated by
     * necessity (public webhook), so the unguessable CheckoutRequestID is the proof
     * of authenticity. Throws (caught by the handler â rolled back, nothing settled).
     */
    private function assertCallbackAuthentic(?string $storedReference, ?string $callbackCheckoutId): void
    {
        if (empty($callbackCheckoutId)
            || empty($storedReference)
            || ! hash_equals((string) $storedReference, (string) $callbackCheckoutId)) {
            throw new \RuntimeException('M-Pesa callback rejected: CheckoutRequestID mismatch (possible forgery).');
        }
    }

    /**
     * B2C payout result callback (Daraja ResultURL).
     *
     * This is the authoritative confirmation that a B2C transfer actually completed.
     * The synchronous /paymentrequest response only says "accepted for processing",
     * so payouts are held in an in-flight state (affiliate: PROCESSING, owner:
     * 'processing') until this callback lands and flips them to the terminal state:
     *   - success -> APPROVED / 'approved'   (money delivered)
     *   - failure -> FAILED / 'failed'        (money never left -> reservation released
     *                                          / owner balance refunded)
     *
     * Both the affiliate (AffiliateWithdrawal) and owner-wallet (WithdrawalRequest)
     * payouts share this one ResultURL, correlated by the ConversationID we stored
     * as mpesa_reference at send time. Reconciliation is idempotent - only an
     * in-flight record is touched, so Safaricom retries are safe.
     */
    public function B2CResult(Request $request)
    {
        $response = json_decode($request->getContent(), true);
        $result   = $response['Result'] ?? [];

        $resultCode       = $result['ResultCode'] ?? -1;
        $conversationId   = $result['ConversationID'] ?? null;
        $originatorConvId = $result['OriginatorConversationID'] ?? null;
        $transactionId    = $result['TransactionID'] ?? null;
        $resultDesc       = $result['ResultDesc'] ?? null;
        $isSuccess        = (string) $resultCode === '0';

        try {
            $refs = array_values(array_filter([$conversationId, $originatorConvId]));

            if (empty($refs)) {
                Log::warning('B2C result with no correlation reference', ['result' => $result]);
                return $this->b2cAck();
            }

            // 1) Affiliate payout?
            $affiliate = AffiliateWithdrawal::whereIn('mpesa_reference', $refs)->first();
            if ($affiliate) {
                $this->reconcileAffiliateB2C($affiliate, $isSuccess, $transactionId, $resultDesc, $resultCode);
                return $this->b2cAck();
            }

            // 2) Owner-wallet payout?
            $owner = WithdrawalRequest::whereIn('mpesa_reference', $refs)->first();
            if ($owner) {
                $this->reconcileOwnerB2C($owner, $isSuccess, $resultDesc, $resultCode);
                return $this->b2cAck();
            }

            Log::warning('B2C result matched no withdrawal', ['refs' => $refs, 'result_code' => $resultCode]);
        } catch (\Throwable $e) {
            // Never let a reconciliation error bubble - Safaricom retries, and the
            // idempotency guards make a retry safe.
            Log::error('B2CResult reconciliation failed: ' . $e->getMessage());
        }

        return $this->b2cAck();
    }

    /** Standard 200 ACK body Safaricom expects for a B2C result. */
    private function b2cAck()
    {
        return response()->json(['ResultDesc' => 'Accepted', 'ResultCode' => '00000000']);
    }

    /**
     * Reconcile an affiliate B2C payout. Idempotent: only an in-flight (PROCESSING)
     * withdrawal is acted on. A FAILED payout needs no ledger reversal - the balance
     * simply stops reserving it (see getReservedWithdrawals).
     */
    private function reconcileAffiliateB2C(AffiliateWithdrawal $withdrawal, bool $success, $transactionId, $resultDesc, $resultCode): void
    {
        if ((int) $withdrawal->status !== AFFILIATE_WITHDRAWAL_PROCESSING) {
            return; // already reconciled, or was never in-flight
        }

        $recipient = $withdrawal->affiliate?->user;

        if ($success) {
            $withdrawal->update([
                'status'         => AFFILIATE_WITHDRAWAL_APPROVED,
                'transaction_id' => $transactionId,
                'processed_at'   => now(),
            ]);

            if ($recipient) {
                $emailData = (object) [
                    'subject' => __('Withdrawal completed — :amount', ['amount' => currencyPrice($withdrawal->amount)]),
                    'message' => __('Your withdrawal of :amount has been sent to your M-Pesa and confirmed. M-Pesa ref: :ref.', ['amount' => currencyPrice($withdrawal->amount), 'ref' => $transactionId ?: '—']),
                ];
                $notificationData = (object) [
                    'title' => __('Withdrawal completed'),
                    'body'  => __(':amount has been paid to your M-Pesa.', ['amount' => currencyPrice($withdrawal->amount)]),
                    'url'   => route('affiliate.dashboard'),
                ];
                SendWalletNotificationJob::dispatch($recipient, $emailData, $notificationData, $withdrawal, false);
            }
            return;
        }

        // Failure -> terminal FAILED; reservation releases automatically -> balance restored.
        $reason = $resultDesc ?: __('code :c', ['c' => $resultCode]);
        $withdrawal->update([
            'status'       => AFFILIATE_WITHDRAWAL_FAILED,
            'processed_at' => now(),
            'notes'        => trim(($withdrawal->notes ? $withdrawal->notes . "\n" : '') . 'M-Pesa failed: ' . $reason),
        ]);

        if ($recipient) {
            $emailData = (object) [
                'subject' => __('Withdrawal failed — :amount returned', ['amount' => currencyPrice($withdrawal->amount)]),
                'message' => __('Your withdrawal of :amount could not be completed by M-Pesa and the amount has been returned to your available balance. You can request it again.', ['amount' => currencyPrice($withdrawal->amount)]),
            ];
            $notificationData = (object) [
                'title' => __('Withdrawal failed'),
                'body'  => __(':amount was returned to your balance.', ['amount' => currencyPrice($withdrawal->amount)]),
                'url'   => route('affiliate.dashboard'),
            ];
            SendWalletNotificationJob::dispatch($recipient, $emailData, $notificationData, $withdrawal, false);
        }

        Log::warning('Affiliate B2C payout failed', [
            'withdrawal_id' => $withdrawal->id,
            'result_code'   => $resultCode,
            'result_desc'   => $resultDesc,
        ]);
    }

    /**
     * Reconcile an owner-wallet B2C payout. Idempotent on the 'processing' status.
     * The owner's balance was debited at request time, so a FAILED payout must refund
     * it (mirrors rejectWithdrawal): restore balance + a credit reversal ledger row.
     */
    private function reconcileOwnerB2C(WithdrawalRequest $withdrawal, bool $success, $resultDesc, $resultCode): void
    {
        if ($withdrawal->status !== 'processing') {
            return; // already reconciled, or was never in-flight
        }

        $recipient = $withdrawal->wallet?->user;

        if ($success) {
            $withdrawal->update(['status' => 'approved', 'processed_at' => now()]);

            WalletTransaction::where('owner_wallet_id', $withdrawal->owner_wallet_id)
                ->where('description', "Withdrawal request #{$withdrawal->id} — processing")
                ->update(['description' => "Withdrawal #{$withdrawal->id} — paid via M-Pesa B2C"]);

            if ($recipient) {
                $emailData = (object) [
                    'subject' => __('Withdrawal Completed — KSh ') . number_format($withdrawal->amount, 2),
                    'message' => __('Your withdrawal of KSh ') . number_format($withdrawal->amount, 2) . __(' has been sent to ') . $withdrawal->phone . __(' via M-Pesa and confirmed.'),
                ];
                $notificationData = (object) [
                    'title' => __('Withdrawal Completed'),
                    'body'  => __('KSh ') . number_format($withdrawal->amount, 2) . __(' has been paid to ') . $withdrawal->phone . '.',
                    'url'   => route('owner.wallet.index'),
                ];
                SendWalletNotificationJob::dispatch($recipient, $emailData, $notificationData, $withdrawal);
            }
            return;
        }

        // Failure -> refund the wallet and mark terminal 'failed'.
        DB::transaction(function () use ($withdrawal) {
            $withdrawal->wallet->increment('balance', $withdrawal->amount);

            $withdrawal->update(['status' => 'failed', 'processed_at' => now()]);

            WalletTransaction::where('owner_wallet_id', $withdrawal->owner_wallet_id)
                ->where('description', "Withdrawal request #{$withdrawal->id} — processing")
                ->update(['description' => "Withdrawal #{$withdrawal->id} — failed (refunded)"]);

            WalletTransaction::create([
                'owner_wallet_id'   => $withdrawal->owner_wallet_id,
                'product_order_id'  => null,
                'gross_amount'      => null,
                'commission_rate'   => null,
                'commission_amount' => null,
                'net_amount'        => $withdrawal->amount,
                'type'              => 'credit',
                'description'       => "Refund — failed withdrawal #{$withdrawal->id}",
            ]);
        });

        if ($recipient) {
            $emailData = (object) [
                'subject' => __('Withdrawal Failed — KSh ') . number_format($withdrawal->amount, 2) . __(' Returned'),
                'message' => __('Your withdrawal of KSh ') . number_format($withdrawal->amount, 2) . __(' could not be completed by M-Pesa. The full amount has been returned to your wallet balance. You can try again.'),
            ];
            $notificationData = (object) [
                'title' => __('Withdrawal Failed'),
                'body'  => __('KSh ') . number_format($withdrawal->amount, 2) . __(' has been returned to your wallet.'),
                'url'   => route('owner.wallet.index'),
            ];
            SendWalletNotificationJob::dispatch($recipient, $emailData, $notificationData, $withdrawal);
        }

        Log::warning('Owner B2C payout failed', [
            'withdrawal_id' => $withdrawal->id,
            'result_code'   => $resultCode,
            'result_desc'   => $resultDesc,
        ]);
    }
    
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
}
