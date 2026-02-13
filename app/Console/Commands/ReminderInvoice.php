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
use App\Jobs\SendInvoiceNotificationAndEmailJob;

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
            foreach ($invoices as $invoice) {
                $dueDate = Carbon::parse($invoice->due_date)->startOfDay();
                $diffDay = $dueDate->diffInDays(today());

                if (getOption('remainder_status', 0) == REMAINDER_STATUS_ACTIVE) {
                    if ($sendEveryday && $dueDate >= today()) {
                        $this->sendReminder($mailService, $invoice);
                    } elseif (!$sendEveryday && in_array($diffDay, $reminderDays) && $dueDate >= today()) {
                        $this->sendReminder($mailService, $invoice);
                    }
                }

                if (getOption('OVERDUE_REMAINDER_STATUS', 0) == REMAINDER_STATUS_ACTIVE) {
                    if ($sendEverydayOverDue && $dueDate <= today()) {
                        $this->sendReminder($mailService, $invoice,true);
                    } elseif (!$sendEverydayOverDue && in_array($diffDay, $reminderDaysOverDue) && $dueDate <= today()) {
                        $this->sendReminder($mailService, $invoice,true);
                    }
                }
            }
        } catch (Exception $e) {
            Log::info('Auto remainder error: ' . $e->getMessage());
        }
    }

    private function sendReminder($mailService, $invoice, $overDue=false)
    {
        $emailData = (object) [
            'subject'   => $overDue ? __('Payment remainder') . ' ' . $invoice->invoice_no . ' ' . __('overdue on') . ' ' . $invoice->due_date :  __('Payment remainder') . ' ' . $invoice->invoice_no . ' ' . __('due on') . ' ' . $invoice->due_date,
            'title'     => __('Payment remainder!'),
            'message'   => $overDue ? __('You have an overdue invoice'): __('You have a due invoice'),
        ];
        $notificationData = (object) [
            'title'   => __('Payment remainder!'),
            'body'     => $overDue ? __('Payment remainder') . ' ' . $invoice->invoice_no . ' ' . __('overdue on') . ' ' . $invoice->due_date :  __('Payment remainder') . ' ' . $invoice->invoice_no . ' ' . __('due on') . ' ' . $invoice->due_date,
            'url'     => route('tenant.invoice.index')
        ];
        SendInvoiceNotificationAndEmailJob::dispatch($invoice,$emailData,$notificationData);

        $message = $overDue ? __($invoice->month.' Payment Remainder from Centresidence overdue on' . ' ' . $invoice->due_date.'. Pay instantly: ').route('instant.invoice.pay', ['token' => $invoice->payment_token]):
        __($invoice->month.' Payment Remainder from Centresidence due on' . ' ' . $invoice->due_date.'. Pay instantly: ').route('instant.invoice.pay', ['token' => $invoice->payment_token]);
        SendSmsJob::dispatch([$invoice->tenant->user->contact_number], $message, $invoice->owner_user_id);
    }
}
