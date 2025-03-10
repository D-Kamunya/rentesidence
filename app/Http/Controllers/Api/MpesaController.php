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

class MpesaController extends Controller
{

    public function MpesaPaymentConfirm (Request $request){
        $response=json_decode($request->getContent(),true);
        $resultCode = $response['Body']['stkCallback']['ResultCode'];
        $paymentId= $response['Body']['stkCallback']['CheckoutRequestID'];
        $orderId=$request->get('id', '');
        $paymentType=$request->get('type', '');

        $originalQueueConnection = config('queue.default');

        try {
            if($paymentType=='subscription'){
                $order = SubscriptionOrder::findOrFail($orderId);
                $gateway = Gateway::find($order->gateway_id);
                if($resultCode==0){
                    if ($order->payment_status == ORDER_PAYMENT_STATUS_PENDING) {
                        DB::beginTransaction();
                        $order->payment_id=$paymentId;
                        $order->payment_status = ORDER_PAYMENT_STATUS_PAID;
                        $order->transaction_id = str_replace('-', '', uuid_create());
                        $order->save();

                        $package = Package::find($order->package_id);
                        $duration = 0;
                        if ($order->duration_type == PACKAGE_DURATION_TYPE_MONTHLY) {
                            $duration = 30;
                        } elseif ($order->duration_type == PACKAGE_DURATION_TYPE_YEARLY) {
                            $duration = 365;
                        }

                        setUserPackage($order->user_id, $package, $duration, $order->quantity, $order->id);

                        DB::commit();

                        config(['queue.default' => 'sync']);

                        $success=true;
                        MpesaTransactionProcessed::dispatch($order,$success);

                        config(['queue.default' => $originalQueueConnection]);

                        $title = __("You have a new invoice");
                        $body = __("Subscription payment verify successfully");
                        $adminUser = User::where('role', USER_ROLE_ADMIN)->first();
                        addNotification($title, $body, null, null,$order->user_id,$adminUser->id);

                        if (getOption('send_email_status', 0) == ACTIVE) {
                            $emails = [$order->user->email];
                            $subject = __('Subscription Payment Successful!');
                            $title = __('Congratulations!');
                            $message = __('You have successfully made the payment');
                            $ownerUserId =$order->user_id;
                            $method = $gateway->slug;
                            $status = 'Paid';
                            $amount = $order->amount;

                            SendPaymentsSuccessEmailJob::dispatch(
                                $emails, $subject, $message, $title, $method, 
                                $status, $amount,$paymentType, $order, $duration
                            );
                        }
                    }
                }elseif ($resultCode==1032) {
                    DB::beginTransaction();
                    $order->payment_id=$paymentId;
                    $order->payment_status = ORDER_PAYMENT_STATUS_CANCELLED;
                    $order->transaction_id = str_replace('-', '', uuid_create());
                    $order->save();
                    DB::commit();

                    config(['queue.default' => 'sync']);

                    $success=false;
                    MpesaTransactionProcessed::dispatch($order,$success);

                    config(['queue.default' => $originalQueueConnection]);
                }else{
                    DB::beginTransaction();
                    $order->payment_id=$paymentId;
                    $order->transaction_id = str_replace('-', '', uuid_create());
                    $order->save();
                    DB::commit();
                    config(['queue.default' => 'sync']);

                    $success=false;
                    MpesaTransactionProcessed::dispatch($order,$success);

                    config(['queue.default' => $originalQueueConnection]);
                }
            }elseif($paymentType=="RentPayment"){
                $order = Order::findOrFail($orderId);
                $gateway = Gateway::find($order->gateway_id);
                $invoice = Invoice::find($order->invoice_id);
                if($resultCode==0){
                    if ($order->payment_status == ORDER_PAYMENT_STATUS_PENDING) {
                        DB::beginTransaction();
                        $order->payment_id=$paymentId;
                        $order->payment_status = ORDER_PAYMENT_STATUS_PAID;
                        $order->transaction_id = str_replace('-', '', uuid_create());
                        $order->save();
                        $invoice->status = INVOICE_STATUS_PAID;
                        $invoice->order_id = $order->id;
                        $invoice->save();
                        DB::commit();

                        config(['queue.default' => 'sync']);

                        $success=true;
                        MpesaTransactionProcessed::dispatch($order,$success);

                        config(['queue.default' => $originalQueueConnection]);

                        $emailData = (object) [
                            'subject'   => __("Rent payment verify successfully"),
                            'title'     =>  __("You have a new invoice"),
                            'message'   => $invoice->invoice_no . ' ' . __('paid successfully'),
                        ];
                        $notificationData = (object) [
                            'title'   => __('Rent Payment successful!'),
                            'body'     =>  $invoice->invoice_no . ' ' . __('paid successfully'),
                        ];
                        SendInvoiceNotificationAndEmailJob::dispatch($invoice,$emailData,$notificationData);
                    }
                }elseif($resultCode==1032) {
                    DB::beginTransaction();
                    $order->payment_id=$paymentId;
                    $order->payment_status = ORDER_PAYMENT_STATUS_CANCELLED;
                    $order->transaction_id = str_replace('-', '', uuid_create());
                    $order->save();
                    DB::commit();
                    
                    config(['queue.default' => 'sync']);

                    $success=false;
                    MpesaTransactionProcessed::dispatch($order,$success);

                    config(['queue.default' => $originalQueueConnection]);
                }else{
                    DB::beginTransaction();
                    $order->payment_id=$paymentId;
                    $order->transaction_id = str_replace('-', '', uuid_create());
                    $order->save();
                    DB::commit();
                    
                    config(['queue.default' => 'sync']);

                    $success=false;
                    MpesaTransactionProcessed::dispatch($order,$success);

                    config(['queue.default' => $originalQueueConnection]);
                }
            }elseif($paymentType=="ProductOrder"){
                $order = ProductOrder::findOrFail($orderId);
                $gateway = Gateway::find($order->gateway_id);
                $ownerNumber = $order->gateway->owner->contact_number;
                if($resultCode==0){
                    if ($order->payment_status == ORDER_PAYMENT_STATUS_PENDING) {
                        DB::beginTransaction();
                        $order->payment_id=$paymentId;
                        $order->payment_status = ORDER_PAYMENT_STATUS_PAID;
                        $order->transaction_id = str_replace('-', '', uuid_create());
                        $order->save();
                        DB::commit();

                        config(['queue.default' => 'sync']);

                        $success=true;
                        MpesaTransactionProcessed::dispatch($order,$success);

                        config(['queue.default' => $originalQueueConnection]);

                        $title = __("You have a new invoice");
                        $body = __("Products payment verify successfully");
                        $ownerUserID = $gateway->owner_user_id;
                        addNotification($title, $body, null, null,  $order->user_id,$ownerUserID);

                        if (getOption('send_email_status', 0) == ACTIVE) {
                            $emails = [$order->user->email];
                            $subject = __('Product Payment Successful!');
                            $title = __('Congratulations!');
                            $message = __('You have successfully made the product order payment');
                            $tenantUserId = $order->user_id;
                            $method = $gateway->slug;
                            $status = 'Paid';
                            $amount = $order->amount;

                            SendPaymentsSuccessEmailJob::dispatch(
                                $emails, $subject, $message, $title, $method, 
                                $status, $amount,$paymentType, $order
                            );
                        }

                        $message = __('New product order '.$order->order_id.' from Centresidence. Kindly Dispatch');
                        SendSmsJob::dispatch([$ownerNumber], $message, $tenantUserId);
                    }
                }elseif($resultCode==1032) {
                    DB::beginTransaction();
                    $order->payment_id=$paymentId;
                    $order->payment_status = ORDER_PAYMENT_STATUS_CANCELLED;
                    $order->transaction_id = str_replace('-', '', uuid_create());
                    $order->save();
                    DB::commit();
                    
                    config(['queue.default' => 'sync']);

                    $success=false;
                    MpesaTransactionProcessed::dispatch($order,$success);

                    config(['queue.default' => $originalQueueConnection]);
                }else{
                    DB::beginTransaction();
                    $order->payment_id=$paymentId;
                    $order->transaction_id = str_replace('-', '', uuid_create());
                    $order->save();
                    DB::commit();
                    
                    config(['queue.default' => 'sync']);

                    $success=false;
                    MpesaTransactionProcessed::dispatch($order,$success);

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
}
