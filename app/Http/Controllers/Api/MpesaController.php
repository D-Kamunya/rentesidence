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

class MpesaController extends Controller
{

    public function MpesaPaymentConfirm (Request $request){
        $response=json_decode($request->getContent(),true);
        $resultCode = $response['Body']['stkCallback']['ResultCode'];
        $paymentId= $response['Body']['stkCallback']['CheckoutRequestID'];
        $orderId=$request->get('id', '');
        $paymentType=$request->get('type', '');
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

                        $title = __("You have a new invoice");
                        $body = __("Subscription payment verify successfully");
                        $adminUser = User::where('role', USER_ROLE_ADMIN)->first();
                        addNotification($title, $body, null, null, $adminUser->id,$order->user_id);

                        if (getOption('send_email_status', 0) == ACTIVE) {
                            $emails = [$order->user->email];
                            $subject = __('Payment Successful!');
                            $title = __('Congratulations!');
                            $message = __('You have successfully made the payment');
                            $ownerUserId =$order->user_id;
                            $method = $gateway->slug;
                            $status = 'Paid';
                            $amount = $order->amount;

                            $mailService = new MailService;
                            $template = EmailTemplate::where('owner_user_id', $ownerUserId)->where('category', EMAIL_TEMPLATE_SUBSCRIPTION_SUCCESS)->where('status', ACTIVE)->first();

                            if ($template) {
                                $customizedFieldsArray = [
                                    '{{amount}}' => $order->total,
                                    '{{status}}' => $status,
                                    '{{duration}}' => $duration,
                                    '{{gateway}}' => $method,
                                    '{{app_name}}' => getOption('app_name')
                                ];
                                $content = getEmailTemplate($template->body, $customizedFieldsArray);
                                $mailService->sendCustomizeMail($emails, $template->subject, $content);
                            } else {
                                $mailService->sendSubscriptionSuccessMail($ownerUserId, $emails, $subject, $message, $title, $method, $status, $amount, $duration);
                            }
                        }
                        Log::info("Mpesa Callback Completed subscription ok");
                    }
                }elseif ($resultCode!=0) {
                    DB::beginTransaction();
                    $order->payment_id=$paymentId;
                    $order->payment_status = ORDER_PAYMENT_STATUS_CANCELLED;
                    $order->transaction_id = str_replace('-', '', uuid_create());
                    $order->save();
                    DB::commit();
                    Log::info("Mpesa Callback Completed subscription declined");
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
                        Log::info("Mpesa Callback Completed rent ok");
                    }
                }elseif($resultCode!=0) {
                    DB::beginTransaction();
                    $order->payment_id=$paymentId;
                    $order->payment_status = ORDER_PAYMENT_STATUS_CANCELLED;
                    $order->transaction_id = str_replace('-', '', uuid_create());
                    $order->save();
                    DB::commit();
                    Log::info("Mpesa Callback Completed rent declined");
                }
            }elseif($paymentType=="ProductOrder"){
                $order = ProductOrder::findOrFail($orderId);
                $gateway = Gateway::find($order->gateway_id);
                if($resultCode==0){
                    if ($order->payment_status == ORDER_PAYMENT_STATUS_PENDING) {
                        DB::beginTransaction();
                        $order->payment_id=$paymentId;
                        $order->payment_status = ORDER_PAYMENT_STATUS_PAID;
                        $order->transaction_id = str_replace('-', '', uuid_create());
                        $order->save();
                        DB::commit();
                        $title = __("You have a new invoice");
                        $body = __("Products payment verify successfully");
                        $ownerUserID = $gateway->owner_user_id;;
                        addNotification($title, $body, null, null, $ownerUserID, $order->user_id);

                        if (getOption('send_email_status', 0) == ACTIVE) {
                            $emails = [$order->user->email];
                            $subject = __('Product Payment Successful!');
                            $title = __('Congratulations!');
                            $message = __('You have successfully made the product order payment');
                            $tenantUserId = $order->user_id;
                            $method = $gateway->slug;
                            $status = 'Paid';
                            $amount = $order->amount;

                            $mailService = new MailService;
                            
                            $mailService->sendProductOrderSuccessMail($tenantUserId,$emails, $subject, $message, $title, $method, $status, $amount);
                        }
                        Log::info("Mpesa Callback Completed product payment ok");
                    }
                }elseif($resultCode!=0) {
                    DB::beginTransaction();
                    $order->payment_id=$paymentId;
                    $order->payment_status = ORDER_PAYMENT_STATUS_CANCELLED;
                    $order->transaction_id = str_replace('-', '', uuid_create());
                    $order->save();
                    DB::commit();
                    Log::info("Mpesa Callback Completed product payment declined");
                }
            }
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());
        }
    }
}
