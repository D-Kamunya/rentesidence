<?php

namespace App\Jobs;

use App\Models\EmailTemplate;
use App\Services\SmsMail\MailService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendInvoiceNotificationAndEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $invoice;
    protected $emailData;
    protected $notificationData;
    

    /**
     * Create a new job instance.
     *
     * @param mixed $invoice
     * @param mixed $emailData;
     * @param mixed $notificationData;
     */
    public function __construct($invoice,object $emailData, object $notificationData)
    {
        $this->invoice = $invoice;
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
        $invoiceUrl = $this->notificationData->url;
        addNotification($titleNotification, $bodyNotification, $invoiceUrl, null, $this->invoice->tenant->user->id, $this->invoice->owner_user_id);

        // If email sending is active, send the email
        if (getOption('send_email_status', 0) == ACTIVE) {
            $emails = [$this->invoice->tenant->user->email];
            $subject = $this->emailData->subject;
            $title   = $this->emailData->title;
            $message = $this->emailData->message;
            $ownerUserId = $this->invoice->owner_user_id;
            $amount  = $this->invoice->amount;
            $dueDate = $this->invoice->due_date;
            $month   = $this->invoice->month;
            $token   = $this->invoice->payment_token;
            $invoiceNo = $this->invoice->invoice_no;
            $status  = __('Pending');

            $mailService = new MailService;
            $template = EmailTemplate::where('owner_user_id', $ownerUserId)
                ->where('category', EMAIL_TEMPLATE_INVOICE)
                ->where('status', ACTIVE)
                ->first();

            if ($template) {
                $customizedFieldsArray = [
                    '{{amount}}'    => $this->invoice->amount,
                    '{{due_date}}'  => $this->invoice->due_date,
                    '{{month}}'     => $this->invoice->month,
                    '{{invoice_no}}'=> $this->invoice->invoice_no,
                    '{{app_name}}'  => getOption('app_name')
                ];
                $content = getEmailTemplate($template->body, $customizedFieldsArray);
                $mailService->sendCustomizeMail($emails, $template->subject, $content);
            } else {
                $mailService->sendInvoiceMail($ownerUserId, $status, $emails, $subject, $message, $title, $amount, $token, $dueDate, $month, $invoiceNo);
            }
        }
    }
}
