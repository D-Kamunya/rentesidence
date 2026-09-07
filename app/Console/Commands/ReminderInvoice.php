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
            $this->ensureDefaults();

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

    /**
     * Self-healing defaults for the tenant payment-reminder cadence. Seeds a sensible platform
     * schedule ONLY for keys an admin hasn't set (checked by presence in config('settings'), so a
     * deliberate admin "off"/custom value is never clobbered). Runs on every scheduler tick so a
     * fresh/updated install reminds out-of-box without a manual deploy step ([[plug-and-play-defaults]]).
     *
     * Cadence is intentionally BOUNDED (fixed days, not "everyday"): a gentle nudge 3 days before
     * due, then three overdue nudges at 1/3/7 days, then it stops. Reminder cadence is a PLATFORM
     * decision, not an owner one — an owner set to "remind every day" would spam tenants (harassment
     * risk + shared-shortcode/domain deliverability damage) and the reputational cost lands on us.
     */
    private function ensureDefaults(): void
    {
        $settings = config('settings', []);
        $defaults = [
            // Pre-due: one gentle nudge, 3 days before the due date.
            'remainder_status'                  => REMAINDER_STATUS_ACTIVE,
            'reminder_days'                     => '3',
            'remainder_everyday_status'         => REMAINDER_EVERYDAY_STATUS_DEACTIVATE,
            // Overdue: three nudges (1, 3, 7 days past due), then stop — NOT everyday.
            'OVERDUE_REMAINDER_STATUS'          => REMAINDER_STATUS_ACTIVE,
            'OVERDUE_REMAINDER_DAYS'            => '1,3,7',
            'OVERDUE_REMAINDER_EVERYDAY_STATUS' => REMAINDER_EVERYDAY_STATUS_DEACTIVATE,
        ];

        foreach ($defaults as $key => $value) {
            if (! array_key_exists($key, $settings)) {
                setOption($key, (string) $value);
            }
        }
    }

    private function sendReminder($mailService, $invoice, $overDue=false)
    {
        $appName = getOption('app_name') ?: 'Centresidence';
        $link    = route('instant.invoice.pay', ['token' => $invoice->payment_token]);

        $emailData = (object) [
            'subject'   => $overDue
                ? __('Payment reminder: invoice :no is overdue', ['no' => $invoice->invoice_no])
                : __('Payment reminder: invoice :no is due', ['no' => $invoice->invoice_no]),
            'title'     => __('Payment reminder'),
            'message'   => $overDue ? __('You have an overdue invoice.') : __('You have an invoice due.'),
        ];
        $notificationData = (object) [
            'title'   => __('Payment reminder'),
            'body'    => $overDue
                ? __('Invoice :no is overdue (was due :date).', ['no' => $invoice->invoice_no, 'date' => $invoice->due_date])
                : __('Invoice :no is due on :date.', ['no' => $invoice->invoice_no, 'date' => $invoice->due_date]),
            'url'     => route('tenant.invoice.index'),
        ];
        SendInvoiceNotificationAndEmailJob::dispatch($invoice,$emailData,$notificationData);

        $message = $overDue
            ? __('Reminder: :month rent from :app is overdue (due :date). Pay instantly: :url', ['month' => $invoice->month, 'app' => $appName, 'date' => $invoice->due_date, 'url' => $link])
            : __('Reminder: :month rent from :app is due on :date. Pay instantly: :url', ['month' => $invoice->month, 'app' => $appName, 'date' => $invoice->due_date, 'url' => $link]);
        SendSmsJob::dispatch([$invoice->tenant->user->contact_number], $message, $invoice->owner_user_id);
    }
}
