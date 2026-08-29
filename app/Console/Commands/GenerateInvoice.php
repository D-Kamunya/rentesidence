<?php

namespace App\Console\Commands;

use App\Centresidence\Services\OwnerBillingStandingService;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InvoiceRecurringSetting;
use App\Models\Property;
use App\Models\Tenant;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Jobs\SendSmsJob;
use App\Jobs\SendInvoiceNotificationAndEmailJob;
use Illuminate\Support\Str;

class GenerateInvoice extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:invoice';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate invoice by invoice recurring setting';

    /**
     * Execute the console command.
     *
     * @return int
     */

    public function handle()
    {
        // Plug-and-play auto-recurring: before generating, make sure every active tenant on a
        // recurring (monthly/yearly) unit has an auto-recurring rent setting derived from the
        // unit — so rent bills even when the owner never configured "recurring settings".
        // Idempotent (ensureUnitRecurringSetting no-ops when a setting already exists).
        $recurringService = app(\App\Services\InvoiceRecurringService::class);
        Tenant::where('status', TENANT_STATUS_ACTIVE)->get()->each(function ($tenant) use ($recurringService) {
            $recurringService->ensureUnitRecurringSetting($tenant);
        });

        $invoiceRecurringSettings =  InvoiceRecurringSetting::query()
            ->with('items')
            ->where('status', ACTIVE)
            ->get();

        $standing     = app(OwnerBillingStandingService::class);
        $overdueCache = []; // owner_user_id => bool (computed once per owner per run)

        foreach ($invoiceRecurringSettings as $invoiceRecurring) {
            // Enforcement: pause auto-invoicing for owners whose module-infra bill is
            // overdue — auto-invoicing is money-making too, so it mustn't keep earning
            // for an owner who's withholding infra (mirrors the readonly HTTP gate).
            // Fail-open: any evaluation error leaves invoicing untouched.
            $ownerUserId = Property::where('id', $invoiceRecurring->property_id)->value('owner_user_id');
            if ($ownerUserId) {
                if (! array_key_exists($ownerUserId, $overdueCache)) {
                    try {
                        $overdueCache[$ownerUserId] = $standing->infraStanding((int) $ownerUserId)['state'] === 'overdue';
                    } catch (\Throwable $e) {
                        $overdueCache[$ownerUserId] = false;
                    }
                }
                if ($overdueCache[$ownerUserId]) {
                    echo "Skipped (owner infrastructure bill overdue) \n";
                    continue;
                }
            }

            $tenant = Tenant::where('unit_id', $invoiceRecurring->property_unit_id)->where('status', TENANT_STATUS_ACTIVE)->first();
            if (!is_null($tenant)) {
                if ($invoiceRecurring->recurring_type == INVOICE_RECURRING_TYPE_MONTHLY) {
                    // Idempotency by billing_period (first-of-month) — unambiguous and year-safe,
                    // and shared with the on-demand advance-pay flow so they never double-bill.
                    $invoiceExist = Invoice::query()
                        ->where('property_id', $invoiceRecurring->property_id)
                        ->where('property_unit_id', $invoiceRecurring->property_unit_id)
                        ->where('tenant_id', $tenant->id)
                        ->where('billing_period', now()->startOfMonth()->toDateString())
                        ->exists();
                    if (!$invoiceExist) {
                        $this->generateInvoice($tenant,$invoiceRecurring);
                        echo "Created \n";
                    } else {
                        echo "Already Created \n";
                    }
                } elseif ($invoiceRecurring->recurring_type == INVOICE_RECURRING_TYPE_YEARLY) {
                    $invoiceExist = Invoice::query()
                        ->where('property_id', $invoiceRecurring->property_id)
                        ->where('property_unit_id', $invoiceRecurring->property_unit_id)
                        ->where('tenant_id', $tenant->id)
                        ->whereYear('created_at', '=', now()->format('Y'))
                        ->exists();
                    if (!$invoiceExist) {
                        $this->generateInvoice($tenant,$invoiceRecurring);
                        echo "Created \n";
                    } else {
                        echo "Already Created \n";
                    }
                } elseif ($invoiceRecurring->recurring_type == INVOICE_RECURRING_TYPE_CUSTOM) {
                    $invoiceExist = Invoice::query()
                        ->where('property_id', $invoiceRecurring->property_id)
                        ->where('property_unit_id', $invoiceRecurring->property_unit_id)
                        ->where('tenant_id', $tenant->id)
                        ->whereDate('created_at', '>', now()->subDays($invoiceRecurring->cycle_day))
                        ->exists();
                    if (!$invoiceExist) {
                        $this->generateInvoice($tenant,$invoiceRecurring);
                        echo "Created \n";
                    } else {
                        echo "Already Created \n";
                    }
                }
            }
        }
    }

    public function generateInvoice($tenant,$invoiceRecurring)
    {
        DB::beginTransaction();
        try {
            $now = now();
            $invoice = new Invoice();
            $invoice->name = $invoiceRecurring->invoice_prefix;
            $invoice->tenant_id = $tenant->id;
            $invoice->owner_user_id = $invoiceRecurring->owner_user_id;
            $invoice->invoice_recurring_setting_id = $invoiceRecurring->id;
            $invoice->property_id = $invoiceRecurring->property_id;
            $invoice->property_unit_id = $invoiceRecurring->property_unit_id;
            $invoice->month = month($now->format('n'));
            $invoice->billing_period = now()->startOfMonth()->toDateString();
            $invoice->due_date = $now->addDays($invoiceRecurring->due_day_after)->endOfDay();
            $invoice->payment_token = Str::uuid();
            $invoice->payment_token_expires_at = invoicePayTokenExpiry($invoice->due_date);
            $invoice->save();
            $totalAmount = 0;
            foreach ($invoiceRecurring->items as $item) {
                $invoiceItem = new InvoiceItem();
                $invoiceItem->invoice_id = $invoice->id;
                $invoiceItem->invoice_type_id = $item->invoice_type_id;
                $invoiceItem->amount = $item->amount;
                $invoiceItem->description = $item->description;
                $invoiceItem->save();
                $totalAmount += $invoiceItem->amount;
            }
            $invoice->amount = $totalAmount;
            $invoice->save();
            DB::commit();

            $message = __('New :month invoice from :app, due :date. Pay instantly: :url', [
                'month' => $invoice->month,
                'app'   => getOption('app_name') ?: 'Centresidence',
                'date'  => $invoice->due_date,
                'url'   => route('instant.invoice.pay', ['token' => $invoice->payment_token]),
            ]);
            SendSmsJob::dispatch([$tenant->user->contact_number], $message, $invoice->owner_user_id);
            
            $emailData = (object) [
                'subject'   => __('Invoice') . ' ' . $invoice->invoice_no . ' ' . __('due on') . ' ' . $invoice->due_date,
                'title'     => __('A new invoice was generated!'),
                'message'   => __('You have a new invoice'),
            ];
            $notificationData = (object) [
                'title'   => __("You have a new invoice"),
                'body'    => __("Please check the invoice and respond as soon as possible."),
                'url'     => route('tenant.invoice.index')
            ];
            SendInvoiceNotificationAndEmailJob::dispatch($invoice,$emailData,$notificationData);
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            return false;
        }
    }
}
