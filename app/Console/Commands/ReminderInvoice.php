<?php

namespace App\Console\Commands;

use App\Models\EmailTemplate;
use App\Models\Invoice;
use App\Services\SmsMail\MailService;
use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Jobs\SendSmsJob;

class ReminderInvoice extends Command
{
    protected $signature = 'reminder:invoice';
    protected $description = 'Reminder invoice for tenant';

    public function handle()
    {
        try {
            if (getOption('remainder_status', 0) != REMAINDER_STATUS_ACTIVE && getOption('OVERDUE_REMAINDER_STATUS', 0) != REMAINDER_STATUS_ACTIVE) {
                throw new Exception('Remainder status inactive');
            }
            $mailService = new MailService;
            $invoices = Invoice::where('status', INVOICE_STATUS_PENDING)->get();

            $sendEveryday = getOption('remainder_everyday_status') == REMAINDER_EVERYDAY_STATUS_ACTIVE;
            $reminderDays = explode(',', getOption('reminder_days'));
            $sendEverydayOverDue = getOption('OVERDUE_REMAINDER_EVERYDAY_STATUS') == REMAINDER_EVERYDAY_STATUS_ACTIVE;
            $reminderDaysOverDue = explode(',', getOption('OVERDUE_REMAINDER_DAYS'));
            $sendEmail = getOption('send_email_status', 0) == ACTIVE;
            Log::info($invoices);
            foreach ($invoices as $invoice) {
                $dueDate = Carbon::parse($invoice->due_date);
                $diffDay = $dueDate->diffInDays(today());

                if (getOption('remainder_status', 0) == REMAINDER_STATUS_ACTIVE) {
                    if ($sendEveryday && $dueDate >= today()) {
                        $this->sendReminder($mailService, $invoice, $sendEmail);
                    } elseif (!$sendEveryday && in_array($diffDay, $reminderDays) && $dueDate >= today()) {
                        $this->sendReminder($mailService, $invoice, $sendEmail);
                    }
                }

                if (getOption('OVERDUE_REMAINDER_STATUS', 0) == REMAINDER_STATUS_ACTIVE) {
                    if ($sendEverydayOverDue && $dueDate <= today()) {
                        $this->sendReminder($mailService, $invoice, $sendEmail,true);
                    } elseif (!$sendEverydayOverDue && in_array($diffDay, $reminderDaysOverDue) && $dueDate <= today()) {
                        $this->sendReminder($mailService, $invoice, $sendEmail,true);
                    }
                }
            }
        } catch (Exception $e) {
            Log::info('Auto remainder error: ' . $e->getMessage());
        }
    }

    private function sendReminder($mailService, $invoice, $sendEmail,$overDue=false)
    {
        $ownerUserId = $invoice->owner_user_id;
        if ($sendEmail) {
            $emails = [$invoice->tenant->user->email];
            $subject = $overDue ? __('Payment remainder') . ' ' . $invoice->invoice_no . ' ' . __('overdue on date') . ' ' . $invoice->due_date :  __('Payment remainder') . ' ' . $invoice->invoice_no . ' ' . __('due on date') . ' ' . $invoice->due_date;
            $title = __('Payment remainder!');
            $message = $overDue ? __('You have an overdue invoice'): __('You have a due invoice');
            $amount = $invoice->amount;
            $dueDate = $invoice->due_date;
            $month = $invoice->month;
            $invoiceNo = $invoice->invoice_no;
            $status = __('Pending');

            $template = EmailTemplate::where('owner_user_id', $ownerUserId)
                ->where('category', EMAIL_TEMPLATE_INVOICE)
                ->where('status', ACTIVE)
                ->first();

            if ($template) {
                $customizedFieldsArray = [
                    '{{amount}}' => $amount,
                    '{{due_date}}' => $dueDate,
                    '{{month}}' => $month,
                    '{{invoice_no}}' => $invoiceNo,
                    '{{app_name}}' => getOption('app_name')
                ];
                $content = getEmailTemplate($template->body, $customizedFieldsArray);
                $mailService->sendCustomizeMail($emails, $template->subject, $content);
            } else {
                $mailService->sendInvoiceMail($ownerUserId, $status, $emails, $subject, $message, $title, $amount, $dueDate, $month, $invoiceNo);
            }
        }

        $message = $overDue ? __($invoice->month.' Payment Remainder from Centresidence . '.$invoice->invoice_no . ' ' . 'overdue on date' . ' ' . $invoice->due_date):
        __($invoice->month.' Payment Remainder from Centresidence . '.$invoice->invoice_no . ' ' . 'due on date' . ' ' . $invoice->due_date);
        SendSmsJob::dispatch([$invoice->tenant->user->contact_number], $message, $ownerUserId);
    }
}
