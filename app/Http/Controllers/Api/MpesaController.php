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
use App\Models\SmsCreditTransaction;
use App\Services\Sms\SmsCreditsService;

class MpesaController extends Controller
{
    public function MpesaPaymentConfirm(Request $request)
    {
        
        $response    = json_decode($request->getContent(), true);
        $resultCode  = $response['Body']['stkCallback']['ResultCode'];
        $paymentId   = $response['Body']['stkCallback']['CheckoutRequestID'];
        $orderId     = $request->get('id', '');
        $paymentType = $request->get('type', '');

        $originalQueueConnection = config('queue.default');

        try {
            if ($paymentType == 'subscription') {
                $order   = SubscriptionOrder::findOrFail($orderId);
                $gateway = Gateway::find($order->gateway_id);
                if ($resultCode == 0) {
                    if ($order->payment_status == ORDER_PAYMENT_STATUS_PENDING) {
                        DB::beginTransaction();
                        $order->payment_id    = $paymentId;
                        $order->payment_status = ORDER_PAYMENT_STATUS_PAID;
                        $order->transaction_id = str_replace('-', '', uuid_create());
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
                        $title      = __("Subscription success");
                        $body       = __("Subscription payment complete");
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
                    $order->save();
                    DB::commit();

                    config(['queue.default' => 'sync']);
                    $success = false;
                    MpesaTransactionProcessed::dispatch($order, $success);
                    config(['queue.default' => $originalQueueConnection]);
                }

            } elseif ($paymentType == 'RentPayment') {
                $order   = Order::findOrFail($orderId);
                $gateway = Gateway::find($order->gateway_id);
                $invoice = Invoice::find($order->invoice_id);
                if ($resultCode == 0) {
                    if ($order->payment_status == ORDER_PAYMENT_STATUS_PENDING) {
                        DB::beginTransaction();
                        $order->payment_id    = $paymentId;
                        $order->payment_status = ORDER_PAYMENT_STATUS_PAID;
                        $order->transaction_id = str_replace('-', '', uuid_create());
                        $order->save();
                        $invoice->status   = INVOICE_STATUS_PAID;
                        $invoice->order_id = $order->id;
                        $invoice->save();
                        DB::commit();

                        // ── Rent commission ─────────────────────────────────
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
                        // ────────────────────────────────────────────────────

                        config(['queue.default' => 'sync']);
                        $success = true;
                        MpesaTransactionProcessed::dispatch($order, $success);
                        config(['queue.default' => $originalQueueConnection]);

                        $emailData = (object) [
                            'subject' => __("Rent payment complete"),
                            'title'   => __("INVOICE PAID SUCCESSFULLY"),
                            'message' => $invoice->invoice_no . ' ' . __('paid successfully'),
                        ];
                        $notificationData = (object) [
                            'title' => __('Rent Payment successful!'),
                            'body'  => $invoice->invoice_no . ' ' . __('paid successfully'),
                            'url'   => route('tenant.invoice.index'),
                        ];
                        SendInvoiceNotificationAndEmailJob::dispatch($invoice, $emailData, $notificationData);
                    }
                } elseif ($resultCode == 1032) {
                    DB::beginTransaction();
                    $order->payment_id    = $paymentId;
                    $order->payment_status = ORDER_PAYMENT_STATUS_CANCELLED;
                    $order->transaction_id = str_replace('-', '', uuid_create());
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
                    $order->save();
                    DB::commit();

                    config(['queue.default' => 'sync']);
                    $success = false;
                    MpesaTransactionProcessed::dispatch($order, $success);
                    config(['queue.default' => $originalQueueConnection]);
                }

            } elseif ($paymentType == 'ProductOrder') {
                $order       = ProductOrder::findOrFail($orderId);
                $gateway     = Gateway::find($order->gateway_id);
                $ownerNumber = $order->gateway->owner->contact_number;
                if ($resultCode == 0) {
                    if ($order->payment_status == ORDER_PAYMENT_STATUS_PENDING) {
                        DB::beginTransaction();
                        $order->payment_id    = $paymentId;
                        $order->payment_status = ORDER_PAYMENT_STATUS_PAID;
                        $order->transaction_id = str_replace('-', '', uuid_create());
                        $order->save();
                        DB::commit();
                        
                        // ── Product commission ───────────────────────────────
                        try {
                            $order->load('orderItems.product');
                            $commissionService = new \App\Services\CommissionService();
                            $commissionService->processOrderCommission($order);
                        } catch (\Exception $e) {
                            Log::error('Product commission failed in webhook', [
                                'order_id' => $order->id,
                                'error'    => $e->getMessage(),
                            ]);
                        }
                        // ────────────────────────────────────────────────────

                        config(['queue.default' => 'sync']);
                        $success = true;
                        MpesaTransactionProcessed::dispatch($order, $success);
                        config(['queue.default' => $originalQueueConnection]);

                        $invoiceUrl  = route('tenant.order.index');
                        $title       = __("Payment success");
                        $body        = __("Products payment complete");
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

                        $message = __('New product order ' . $order->order_id . ' from Centresidence. Kindly Dispatch');
                        SendSmsJob::dispatch([$ownerNumber], $message, $tenantUserId);
                    }
                } elseif ($resultCode == 1032) {
                    DB::beginTransaction();
                    $order->payment_id    = $paymentId;
                    $order->payment_status = ORDER_PAYMENT_STATUS_CANCELLED;
                    $order->transaction_id = str_replace('-', '', uuid_create());
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
                    $order->save();
                    DB::commit();

                    config(['queue.default' => 'sync']);
                    $success = false;
                    MpesaTransactionProcessed::dispatch($order, $success);
                    config(['queue.default' => $originalQueueConnection]);
                }

            } elseif ($paymentType === 'SmsCreditTransaction') {

                $pending = SmsCreditTransaction::findOrFail($orderId);

                if ($resultCode == 0) {
                    DB::beginTransaction();
                    try {
                        // Atomic lock — if verify() is also running, one waits
                        // and finds status !== 'pending' after lock releases
                        $locked = SmsCreditTransaction::where('id', $orderId)
                            ->where('status', 'pending')
                            ->lockForUpdate()
                            ->first();

                        if ($locked) {
                            SmsCreditsService::addCredits(
                                $locked->owner_user_id,
                                $locked->quantity,
                                'purchase',
                                $locked->amount_paid,
                                $paymentId,
                                "Purchase of {$locked->quantity} SMS credits",
                                $locked->id 
                            );
                            $locked->update([
                                'payment_id' => $paymentId,
                            ]);
                            DB::commit();

                            config(['queue.default' => 'sync']);
                            MpesaTransactionProcessed::dispatch($locked, true);
                            config(['queue.default' => $originalQueueConnection]);
                        } else {
                            // Already processed by verify() fallback
                            DB::commit();
                        }

                    } catch (\Exception $e) {
                        DB::rollBack();
                        Log::error('SMS credits callback failed: ' . $e->getMessage());

                        config(['queue.default' => 'sync']);
                        MpesaTransactionProcessed::dispatch($pending, false);
                        config(['queue.default' => $originalQueueConnection]);
                    }

                } elseif ($resultCode == 1032) {
                    $pending->update(['status' => 'failed', 'payment_id' => $paymentId]);

                    config(['queue.default' => 'sync']);
                    MpesaTransactionProcessed::dispatch($pending, false);
                    config(['queue.default' => $originalQueueConnection]);
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

    public function B2CResult(Request $request)
    {
        $response = json_decode($request->getContent(), true);
        $result   = $response['Result'] ?? [];

        $resultCode          = $result['ResultCode'] ?? -1;
        $conversationId      = $result['ConversationID'] ?? null;
        $originatorConvId    = $result['OriginatorConversationID'] ?? null;
        $transactionId       = $result['TransactionID'] ?? null;

        if ($resultCode == 0) {
            // Success — find the withdrawal by ConversationID stored as mpesa_reference
            $withdrawal = \App\Models\WithdrawalRequest::where('mpesa_reference', $conversationId)
                ->orWhere('mpesa_reference', $originatorConvId)
                ->first();

            if ($withdrawal && $withdrawal->status === 'approved') {
                $withdrawal->update([
                    'transaction_id' => $transactionId,
                    'confirmed_at'   => now(),
                ]);
            }
        } else {
            // Failed after approval — log for manual review
            Log::error('B2C transfer failed', [
                'conversation_id' => $conversationId,
                'result_code'     => $resultCode,
                'result_desc'     => $result['ResultDesc'] ?? null,
            ]);
        }

        // Safaricom expects a 200 response with this body
        return response()->json(['ResultDesc' => 'Accepted', 'ResultCode' => '00000000']);
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
