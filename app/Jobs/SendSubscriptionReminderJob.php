<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\OwnerPackage;
use App\Models\EmailTemplate;
use App\Models\User;
use App\Services\SmsMail\MailService;
use App\Mail\SubscriptionReminderMail;

class SendSubscriptionReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $subscription;
    protected $emailData;
    protected $notificationData;
    

    /**
     * Create a new job instance.
     *
     * @param mixed $subscription
     * @param mixed $emailData;
     * @param mixed $notificationData;
     */
    public function __construct($subscription,object $emailData, object $notificationData)
    {
        $this->subscription = $subscription;
        $this->emailData = $emailData;
        $this->notificationData = $notificationData;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Add Notification
        $titleNotification = $this-> notificationData->title;
        $bodyNotification  = $this->notificationData->body;
        $subscriptionUrl = $this->notificationData->url;
        $admin = User::where('role', USER_ROLE_ADMIN)->first();
        addNotification($titleNotification, $bodyNotification, $subscriptionUrl, null, $this->subscription->user_id, $admin->id);

        // If email sending is active, send the email
        if (getOption('send_email_status', 0) == ACTIVE) {
            $emails = [$this->subscription->owner->email];
            $subject = $this->emailData->subject;
            $title   = $this->emailData->title;
            $message = $this->emailData->message;
            $monthlyAmount  = $this->subscription->monthly_price;
            $yearlyAmount  = $this->subscription->yearly_price;
            $endDate = $this->subscription->end_date;
            $ownerUserId = $this->subscription->user_id;

            $mailService = new MailService;
            $template = EmailTemplate::where('owner_user_id', $admin->id)
                ->where('category', EMAIL_TEMPLATE_SUBSCRIPTION_REMINDER)
                ->where('status', ACTIVE)
                ->first();

            if ($template) {
                $customizedFieldsArray = [
                    '{{amount}}'    => $this->subscription->amount,
                    '{{due_date}}'  => $this->subscription->end_date,
                    '{{month}}'     => $this->subscription->month,
                    '{{invoice_no}}'=> $this->subscription->invoice_no,
                    '{{app_name}}'  => getOption('app_name')
                ];
                $content = getEmailTemplate($template->body, $customizedFieldsArray);
                $mailService->sendCustomizeMail($emails, $template->subject, $content);
            } else {
                 $mailService->sendSubscriptionReminderMail($ownerUserId, $emails, $subject, $message, $title, $this->subscription);
            }
        }
    }
}
