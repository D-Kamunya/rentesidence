<?php

namespace App\Jobs;

use App\Services\SmsMail\MailService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\EmailTemplate;

class SendPaymentsSuccessEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $emails, $subject, $message, $title, $method, $status, $amount, $paymentType, $duration, $order;

    /**
     * Create a new job instance.
     */
    public function __construct($emails, $subject, $message, $title, $method, $status, $amount,$paymentType, $order = null, $duration = null)
    {
        $this->emails = $emails;
        $this->subject = $subject;
        $this->message = $message;
        $this->title = $title;
        $this->method = $method;
        $this->status = $status;
        $this->amount = $amount;
        $this->duration = $duration;
        $this->order = $order;
        $this->paymentType = $paymentType;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $mailService = new MailService;

        if ($this->paymentType == 'subscription') {
            $ownerUserId = $this->order->user_id;
            $template = EmailTemplate::where('owner_user_id', $ownerUserId)
                ->where('category', EMAIL_TEMPLATE_SUBSCRIPTION_SUCCESS)
                ->where('status', ACTIVE)
                ->first();

            if ($template) {
                $customizedFieldsArray = [
                    '{{amount}}' => $this->amount,
                    '{{status}}' => $this->status,
                    '{{duration}}' => $this->duration,
                    '{{gateway}}' => $this->method,
                    '{{app_name}}' => getOption('app_name'),
                ];
                $content = getEmailTemplate($template->body, $customizedFieldsArray);
                $mailService->sendCustomizeMail($this->emails, $template->subject, $content);
            }else {
                $mailService->sendSubscriptionSuccessMail(
                    $this->order->user_id, $this->emails, $this->subject,
                    $this->message, $this->title, $this->method, 
                    $this->status, $this->amount, $this->duration
                );
            }
        }  elseif ($this->paymentType == 'ProductOrder') {
                $mailService->sendProductOrderSuccessMail(
                    $this->order->user_id, $this->emails, $this->subject,
                    $this->message, $this->title, $this->method, 
                    $this->status, $this->amount
                );
            }
    }
}
