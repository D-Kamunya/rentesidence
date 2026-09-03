<?php

namespace App\Services;

use App\Jobs\SendInvoiceNotificationAndEmailJob;
use App\Jobs\SendSmsJob;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InvoiceRecurringSetting;
use App\Models\InvoiceRecurringSettingItem;
use App\Models\InvoiceType;
use App\Models\PropertyUnit;
use App\Models\Tenant;
use App\Services\DepositService;
use App\Traits\ResponseTrait;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class InvoiceRecurringService
{
    use ResponseTrait;

    /**
     * Generate a rent invoice for a SPECIFIC billing period from a recurring setting, idempotently.
     * Keyed off billing_period (first-of-covered-month) so a period is never double-billed, even
     * across a year boundary. Pure generation — no SMS/email (the caller decides notifications).
     *
     * When $overrideAmount is provided (pro-rated or custom first invoice at move-in), the invoice
     * carries a SINGLE line at that amount against the setting's rent invoice-type, using
     * $overrideDescription — instead of copying the setting's recurring items at full amount. The
     * override is null for the cron / advance-rent callers, so their behaviour is unchanged.
     *
     * @return Invoice|null the existing (if the period was already billed) or newly-created invoice
     */
    public function generateRentInvoiceForPeriod($tenant, InvoiceRecurringSetting $setting, Carbon $periodStart, ?float $overrideAmount = null, ?string $overrideDescription = null): ?Invoice
    {
        $periodStart = $periodStart->copy()->startOfMonth();

        $existing = Invoice::where('property_unit_id', $setting->property_unit_id)
            ->where('tenant_id', $tenant->id)
            ->where('billing_period', $periodStart->toDateString())
            ->first();
        if ($existing) {
            return $existing;
        }

        return DB::transaction(function () use ($tenant, $setting, $periodStart, $overrideAmount, $overrideDescription) {
            $invoice = new Invoice();
            $invoice->name = $setting->invoice_prefix;
            $invoice->tenant_id = $tenant->id;
            $invoice->owner_user_id = $setting->owner_user_id;
            $invoice->invoice_recurring_setting_id = $setting->id;
            $invoice->property_id = $setting->property_id;
            $invoice->property_unit_id = $setting->property_unit_id;
            $invoice->month = month((int) $periodStart->format('n'));
            $invoice->billing_period = $periodStart->toDateString();
            // Due on the owner's chosen day-of-month within the covered period (clamped safe).
            $dueDay = max(1, min(28, (int) $setting->due_day_after ?: 5));
            $invoice->due_date = $periodStart->copy()->day($dueDay)->endOfDay();
            $invoice->payment_token = Str::uuid();
            $invoice->payment_token_expires_at = invoicePayTokenExpiry($invoice->due_date);
            $invoice->save();

            $total = 0;
            if ($overrideAmount !== null) {
                // Single override line (pro-rated / custom first invoice). Bill it against the
                // setting's rent invoice-type so it still reads as RENT (kept out of commission,
                // since Invoice::rentPortion() keys off the 'rent' type name).
                $rentTypeId = optional($setting->items->first())->invoice_type_id;
                $ii = new InvoiceItem();
                $ii->invoice_id      = $invoice->id;
                $ii->invoice_type_id = $rentTypeId;
                $ii->amount          = round($overrideAmount, 2);
                $ii->description     = $overrideDescription ?: __('Rent');
                $ii->save();
                $total = $ii->amount;
            } else {
                foreach ($setting->items as $item) {
                    $ii = new InvoiceItem();
                    $ii->invoice_id      = $invoice->id;
                    $ii->invoice_type_id = $item->invoice_type_id;
                    $ii->amount          = $item->amount;
                    $ii->description     = $item->description;
                    $ii->save();
                    $total += $ii->amount;
                }
            }
            $invoice->amount = $total;
            $invoice->save();

            return $invoice;
        });
    }

    /**
     * Resolve the tenant's active recurring rent setting (the one ensureUnitRecurringSetting created
     * at assignment). Read-only lookup — never creates. Null when the unit has no monthly/yearly
     * auto-setting (e.g. custom-rent units), which is the signal that move-in invoicing doesn't apply.
     */
    private function activeSettingForTenant($tenant): ?InvoiceRecurringSetting
    {
        if (!$tenant || !$tenant->unit_id) {
            return null;
        }
        return InvoiceRecurringSetting::with('items')
            ->where('property_unit_id', $tenant->unit_id)
            ->where('status', ACTIVE)
            ->first();
    }

    /**
     * Pro-rated rent for a mid-period move-in: monthly_rent ÷ days_in_that_month × days_remaining,
     * counting the move-in day itself as chargeable. E.g. 30,000 rent, move-in on the 20th of a
     * 30-day month → 11 days → 30000/30×11 = 11,000.
     *
     * @return array{days_in_month:int,days_remaining:int,amount:float}
     */
    public function proratedRent(float $monthlyRent, Carbon $moveIn): array
    {
        $daysInMonth   = $moveIn->daysInMonth;
        $daysRemaining = max(1, $daysInMonth - $moveIn->day + 1);
        $amount        = round($monthlyRent / $daysInMonth * $daysRemaining, 2);
        return [
            'days_in_month'  => $daysInMonth,
            'days_remaining' => $daysRemaining,
            'amount'         => $amount,
        ];
    }

    /**
     * Context for the move-in "first invoice" modal: what to charge for the CURRENT period, the
     * full vs pro-rated figures, and whether the period is already invoiced. Returns null when
     * move-in invoicing doesn't apply (no active monthly/yearly setting) so the caller stays silent.
     *
     * @return array<string,mixed>|null
     */
    public function firstInvoiceContext($tenant): ?array
    {
        $setting = $this->activeSettingForTenant($tenant);
        if (!$setting) {
            return null;
        }

        $period    = now()->startOfMonth();
        $isMonthly = (int) $setting->recurring_type === INVOICE_RECURRING_TYPE_MONTHLY;
        $fullAmount = (float) $setting->amount;

        // Already billed for this period? (cron ran, or the owner already chose here.)
        $existing = Invoice::where('property_unit_id', $setting->property_unit_id)
            ->where('tenant_id', $tenant->id)
            ->where('billing_period', $period->toDateString())
            ->first();

        // Pro-rate only makes sense for monthly rent AND when the lease starts within this period.
        $prorate = null;
        $lease = $tenant->lease_start_date ? Carbon::parse($tenant->lease_start_date) : null;
        $moveIn = ($lease && $lease->isSameMonth($period)) ? $lease : now();
        if ($isMonthly && $moveIn->day > 1) {
            $prorate = $this->proratedRent($fullAmount, $moveIn);
        }

        // Deposit config for the "also collect deposit" option — the unit's configured amount
        // resolved to an absolute figure, and whether one is already in play (so we never re-collect).
        $depSvc         = app(DepositService::class);
        $depositAmount  = $depSvc->configuredDepositAmount($tenant, $fullAmount);
        $depositExists  = $depSvc->tenantHasDeposit($tenant->id);

        return [
            'tenant_id'        => $tenant->id,
            'tenant_name'      => trim(optional($tenant->user)->first_name . ' ' . optional($tenant->user)->last_name) ?: __('Tenant'),
            'unit_label'       => optional($tenant->unit)->unit_name ?: ('#' . $tenant->unit_id),
            'period_label'     => $period->format('F Y'),
            'is_monthly'       => $isMonthly,
            'full_amount'      => $fullAmount,
            'prorate'          => $prorate,   // null when not applicable
            'already_invoiced' => (bool) $existing,
            'existing_amount'  => $existing ? (float) $existing->amount : null,
            // Deposit
            'deposit_amount'   => $depositAmount,          // resolved (0 when not configured)
            'deposit_configured' => $depositAmount > 0,
            'deposit_exists'   => $depositExists,          // already held / on a pending invoice
        ];
    }

    /**
     * Generate the tenant's FIRST invoice at move-in per the owner's chosen mode. Interactive-only
     * (add-tenant + application-accept) — NOT called from bulk import or the cron. Idempotent: if the
     * current period is already billed, the existing invoice is returned untouched (never double-bills).
     *
     *   full   → the setting's normal recurring items at full amount
     *   prorate→ a single pro-rated rent line for the days left in the current month (monthly only)
     *   custom → a single rent line at the owner-typed amount
     *   skip   → nothing generated now; the cron bills the next cycle
     *
     * @return array{ok:bool,message:string,invoice_id:int|null}
     */
    public function generateFirstInvoice($tenant, string $mode, ?float $customAmount = null, ?float $depositAmount = null): array
    {
        $setting = $this->activeSettingForTenant($tenant);
        if (!$setting) {
            return ['ok' => false, 'message' => __('Move-in invoicing is not available for this unit.'), 'invoice_id' => null];
        }

        $depSvc = app(DepositService::class);
        // Never collect a deposit twice for the same tenancy (already held, or already on a pending
        // invoice). A deposit is one-time per tenancy, independent of billing period.
        $includeDeposit = $depositAmount !== null && $depositAmount > 0 && !$depSvc->tenantHasDeposit($tenant->id);

        if ($mode === 'skip' && !$includeDeposit) {
            return ['ok' => true, 'message' => __('No move-in invoice created. Rent will bill on the next cycle.'), 'invoice_id' => null];
        }

        $period = now()->startOfMonth();

        try {
            if ($mode === 'skip') {
                // Deposit only — a one-time, non-period invoice (billing_period NULL) so it never
                // occupies a rent-period slot (which would make the cron skip that month's rent).
                $invoice = $this->createDepositOnlyInvoice($tenant, $setting, $depositAmount, $depSvc);
            } elseif ($mode === 'full') {
                $invoice = $this->generateRentInvoiceForPeriod($tenant, $setting, $period);
            } elseif ($mode === 'prorate') {
                if ((int) $setting->recurring_type !== INVOICE_RECURRING_TYPE_MONTHLY) {
                    return ['ok' => false, 'message' => __('Pro-rating only applies to monthly rent.'), 'invoice_id' => null];
                }
                $lease  = $tenant->lease_start_date ? Carbon::parse($tenant->lease_start_date) : null;
                $moveIn = ($lease && $lease->isSameMonth($period)) ? $lease : now();
                $pr     = $this->proratedRent((float) $setting->amount, $moveIn);
                $desc   = __('Rent') . ' — ' . __('pro-rated') . ' (' . $pr['days_remaining'] . '/' . $pr['days_in_month'] . ' ' . __('days') . ')';
                $invoice = $this->generateRentInvoiceForPeriod($tenant, $setting, $period, $pr['amount'], $desc);
            } elseif ($mode === 'custom') {
                if ($customAmount === null || $customAmount <= 0) {
                    return ['ok' => false, 'message' => __('Enter a valid custom amount.'), 'invoice_id' => null];
                }
                $invoice = $this->generateRentInvoiceForPeriod($tenant, $setting, $period, $customAmount, __('Rent'));
            } else {
                return ['ok' => false, 'message' => __('Invalid option.'), 'invoice_id' => null];
            }

            // Combined "rent + deposit" (the 2× first payment): append the deposit line to a FRESHLY
            // created rent invoice. Skipped if the rent invoice already existed (idempotent return)
            // or on the deposit-only path (which already carries the line).
            if ($mode !== 'skip' && $includeDeposit && $invoice && $invoice->wasRecentlyCreated) {
                $this->appendDepositLine($invoice, (int) $tenant->owner_user_id, $depositAmount, $depSvc);
            }
        } catch (\Throwable $e) {
            Log::error('generateFirstInvoice failed for tenant ' . $tenant->id . ' — ' . $e->getMessage());
            return ['ok' => false, 'message' => __('Could not create the invoice. Please try again.'), 'invoice_id' => null];
        }

        if (!$invoice) {
            return ['ok' => false, 'message' => __('Could not create the invoice. Please try again.'), 'invoice_id' => null];
        }

        // Fire the same SMS (offline pay link) + bell/email as the recurring cron — but ONLY for a
        // freshly-created invoice. If generateRentInvoiceForPeriod returned an EXISTING one (the
        // cron already billed this period), wasRecentlyCreated is false → we don't re-notify/spam.
        if ($invoice->wasRecentlyCreated) {
            $this->notifyInvoiceGenerated($tenant, $invoice);
        }

        return ['ok' => true, 'message' => __('First invoice created.'), 'invoice_id' => $invoice->id];
    }

    /**
     * Generate the FINAL rent invoice at move-out — the mirror of the move-in pro-rate: rent for the
     * days the tenant OCCUPIED the final month (month-start → move-out day). Monthly rent only.
     *
     * SAFE against the cron: if the move-out month is already invoiced (the cron billed the full
     * month), we DON'T silently return that full invoice — we tell the owner to adjust manually
     * (no credit-note machinery here). When it isn't yet billed, the pro-rated invoice takes the
     * billing_period slot so the cron won't double-bill it.
     *
     * @return array{ok:bool,message:string,invoice_id:int|null}
     */
    /**
     * Context for the final-invoice modal (mirrors firstInvoiceContext): the pro-rated figure for
     * the occupied days + whether the move-out month is already billed. Null when not applicable.
     *
     * @return array<string,mixed>|null
     */
    public function finalInvoiceContext($tenant, $moveOutDate): ?array
    {
        $setting = $this->activeSettingForTenant($tenant);
        if (!$setting || (int) $setting->recurring_type !== INVOICE_RECURRING_TYPE_MONTHLY) {
            return null;
        }
        try {
            $moveOut = $moveOutDate instanceof Carbon ? $moveOutDate->copy() : Carbon::parse($moveOutDate);
        } catch (\Throwable $e) {
            return null;
        }
        $period       = $moveOut->copy()->startOfMonth();
        $daysInMonth  = $moveOut->daysInMonth;
        $occupiedDays = max(1, min($daysInMonth, $moveOut->day));
        $existing = Invoice::where('property_unit_id', $setting->property_unit_id)
            ->where('tenant_id', $tenant->id)
            ->where('billing_period', $period->toDateString())
            ->first();

        return [
            'applicable'         => true,
            'already_billed'     => (bool) $existing,
            'move_out'           => $moveOut->format('d M Y'),
            'period_label'       => $period->format('F Y'),
            'occupied_days'      => $occupiedDays,
            'days_in_month'      => $daysInMonth,
            'full_amount'        => (float) $setting->amount,
            'prorated_amount'    => round((float) $setting->amount / $daysInMonth * $occupiedDays, 2),
            'currency_symbol'    => getCurrencySymbol(),
            'currency_placement' => getCurrencyPlacement(),
        ];
    }

    /**
     * @param float|null $customAmount when set (>0), bills this agreed figure instead of the
     *        auto pro-rate — for the off-system move-out deals owners routinely strike.
     */
    public function generateFinalInvoice($tenant, $moveOutDate, ?float $customAmount = null): array
    {
        $setting = $this->activeSettingForTenant($tenant);
        if (!$setting) {
            return ['ok' => false, 'message' => __('Move-out invoicing is not available for this unit.'), 'invoice_id' => null];
        }
        if ((int) $setting->recurring_type !== INVOICE_RECURRING_TYPE_MONTHLY) {
            return ['ok' => false, 'message' => __('Pro-rating only applies to monthly rent.'), 'invoice_id' => null];
        }

        try {
            $moveOut = $moveOutDate instanceof Carbon ? $moveOutDate->copy() : Carbon::parse($moveOutDate);
        } catch (\Throwable $e) {
            return ['ok' => false, 'message' => __('Invalid move-out date.'), 'invoice_id' => null];
        }
        $period = $moveOut->copy()->startOfMonth();

        // Already billed for the move-out month? Don't double-bill; leave adjustment to the owner.
        $existing = Invoice::where('property_unit_id', $setting->property_unit_id)
            ->where('tenant_id', $tenant->id)
            ->where('billing_period', $period->toDateString())
            ->first();
        if ($existing) {
            return [
                'ok'         => false,
                'message'    => __(':month rent is already invoiced. Adjust that invoice manually if a pro-rated final amount is needed.', ['month' => $period->format('F Y')]),
                'invoice_id' => $existing->id,
            ];
        }

        $daysInMonth  = $moveOut->daysInMonth;
        $occupiedDays = max(1, min($daysInMonth, $moveOut->day)); // month-start → move-out day
        if ($customAmount !== null && $customAmount > 0) {
            $amount = round($customAmount, 2);
            $desc   = __('Rent') . ' — ' . __('final') . ' (' . __('agreed') . ')';
        } else {
            $amount = round((float) $setting->amount / $daysInMonth * $occupiedDays, 2);
            $desc   = __('Rent') . ' — ' . __('final') . ' (' . $occupiedDays . '/' . $daysInMonth . ' ' . __('days') . ')';
        }

        try {
            $invoice = $this->generateRentInvoiceForPeriod($tenant, $setting, $period, $amount, $desc);
        } catch (\Throwable $e) {
            Log::error('generateFinalInvoice failed for tenant ' . $tenant->id . ' — ' . $e->getMessage());
            return ['ok' => false, 'message' => __('Could not create the invoice. Please try again.'), 'invoice_id' => null];
        }

        if (!$invoice) {
            return ['ok' => false, 'message' => __('Could not create the invoice. Please try again.'), 'invoice_id' => null];
        }
        if ($invoice->wasRecentlyCreated) {
            $this->notifyInvoiceGenerated($tenant, $invoice);
        }
        return ['ok' => true, 'message' => __('Final rent invoice created.'), 'invoice_id' => $invoice->id];
    }

    /**
     * Notify the tenant of a newly-generated invoice — SMS with the instant/offline pay link, plus
     * the in-app bell + email. Mirrors the recurring cron (GenerateInvoice::generateInvoice) so the
     * move-in first invoice reaches the tenant exactly like the monthly ones. Best-effort: a comms
     * failure never rolls back the (already-committed) invoice.
     */
    private function notifyInvoiceGenerated($tenant, Invoice $invoice): void
    {
        try {
            $phone = optional($tenant->user)->contact_number;
            if ($phone) {
                $message = __('New :month invoice from :app, due :date. Pay instantly: :url', [
                    'month' => $invoice->month,
                    'app'   => getOption('app_name') ?: 'Centresidence',
                    'date'  => $invoice->due_date,
                    'url'   => route('instant.invoice.pay', ['token' => $invoice->payment_token]),
                ]);
                SendSmsJob::dispatch([$phone], $message, $invoice->owner_user_id);
            }

            $emailData = (object) [
                'subject' => __('Invoice') . ' ' . $invoice->invoice_no . ' ' . __('due on') . ' ' . $invoice->due_date,
                'title'   => __('A new invoice was generated!'),
                'message' => __('You have a new invoice'),
            ];
            $notificationData = (object) [
                'title' => __('You have a new invoice'),
                'body'  => __('Please check the invoice and respond as soon as possible.'),
                'url'   => route('tenant.invoice.index'),
            ];
            SendInvoiceNotificationAndEmailJob::dispatch($invoice, $emailData, $notificationData);
        } catch (\Throwable $e) {
            Log::error('notifyInvoiceGenerated failed for invoice ' . $invoice->id . ' — ' . $e->getMessage());
        }
    }

    /**
     * Append a "Security Deposit" line (owner's self-healed Deposit invoice-type) to a just-created
     * invoice and bump its total. The deposit stays OUT of commission automatically — rentPortion()
     * only sums 'rent' lines — and only becomes a HELD liability when the invoice is PAID.
     */
    private function appendDepositLine(Invoice $invoice, int $ownerUserId, float $amount, DepositService $depSvc): void
    {
        $type = $depSvc->ensureDepositType($ownerUserId);
        if (!$type) {
            return;
        }
        $ii = new InvoiceItem();
        $ii->invoice_id      = $invoice->id;
        $ii->invoice_type_id = $type->id;
        $ii->amount          = round($amount, 2);
        $ii->description     = __('Security Deposit');
        $ii->save();

        $invoice->amount = (float) $invoice->amount + $ii->amount;
        $invoice->save();
    }

    /**
     * A one-time deposit-only invoice (owner chose "no rent this period" but is collecting the
     * deposit). billing_period is NULL so it never occupies a rent-period slot — the cron still bills
     * that month's rent normally.
     */
    private function createDepositOnlyInvoice($tenant, InvoiceRecurringSetting $setting, float $amount, DepositService $depSvc): ?Invoice
    {
        $type = $depSvc->ensureDepositType((int) $setting->owner_user_id);
        if (!$type) {
            return null;
        }

        return DB::transaction(function () use ($tenant, $setting, $amount, $type) {
            $invoice = new Invoice();
            $invoice->name = $setting->invoice_prefix;
            $invoice->tenant_id = $tenant->id;
            $invoice->owner_user_id = $setting->owner_user_id;
            $invoice->invoice_recurring_setting_id = $setting->id;
            $invoice->property_id = $setting->property_id;
            $invoice->property_unit_id = $setting->property_unit_id;
            $invoice->month = month((int) now()->format('n'));
            $invoice->billing_period = null;                     // one-time, not a rent period
            $invoice->due_date = now()->endOfMonth();            // deposit due within the move-in month
            $invoice->payment_token = Str::uuid();
            $invoice->payment_token_expires_at = invoicePayTokenExpiry($invoice->due_date);
            $invoice->save();

            $ii = new InvoiceItem();
            $ii->invoice_id      = $invoice->id;
            $ii->invoice_type_id = $type->id;
            $ii->amount          = round($amount, 2);
            $ii->description     = __('Security Deposit');
            $ii->save();

            $invoice->amount = $ii->amount;
            $invoice->save();

            return $invoice;
        });
    }

    /**
     * The next $count monthly rent periods for a tenant, each annotated with its state so the
     * "Pay Upcoming Rent" UI can show real month names + whether each is already Paid / Invoiced /
     * still Available to prepare. Returns [] when advance rent doesn't apply (no monthly setting).
     *
     * @return array<int,array{period:string,label:string,amount:float,state:string,invoice_id:int|null}>
     */
    public function upcomingRentMonths($tenant, int $count = 10): array
    {
        $setting = $this->ensureUnitRecurringSetting($tenant);
        if (!$setting || (int) $setting->recurring_type !== INVOICE_RECURRING_TYPE_MONTHLY) {
            return [];
        }
        $amount = (float) $setting->amount;

        $start = now()->startOfMonth();
        $end   = $start->copy()->addMonths($count - 1)->endOfMonth();

        $existing = Invoice::where('property_unit_id', $setting->property_unit_id)
            ->where('tenant_id', $tenant->id)
            ->whereBetween('billing_period', [$start->toDateString(), $end->toDateString()])
            ->get()
            ->keyBy(fn ($inv) => Carbon::parse($inv->billing_period)->toDateString());

        $months = [];
        for ($i = 0; $i < $count; $i++) {
            $period = $start->copy()->addMonths($i);
            $key    = $period->toDateString();
            $inv    = $existing->get($key);
            $state  = $inv ? ((int) $inv->status === INVOICE_STATUS_PAID ? 'paid' : 'invoiced') : 'available';
            $months[] = [
                'period'     => $key,
                'label'      => $period->format('F Y'),
                'amount'     => $amount,
                'state'      => $state,
                'invoice_id' => $inv?->id,
            ];
        }
        return $months;
    }

    /**
     * Plug-and-play auto-recurring rent. Ensure a unit that has an ACTIVE tenant on a recurring
     * rent type (monthly/yearly) has an active recurring rent setting DERIVED FROM THE UNIT — so
     * owners never have to configure "recurring settings" manually and rent auto-bills.
     *
     * - Idempotent: only creates when the unit has NO active recurring setting (never clobbers a
     *   manual / multi-item setting an owner built).
     * - Custom (date-based) rent units are skipped — their fixed-term schedules don't fit a simple
     *   monthly/yearly default; those keep using the manual/custom path.
     * - Uses $tenant->owner_user_id (works inside the cron — no auth() dependency).
     * - Reuses the owner's default "Rent" InvoiceType so unit-rent edits (updateRecurringRentAmounts)
     *   keep this setting in sync — All Units stays the single source of truth for rent.
     *
     * @return InvoiceRecurringSetting|null the (existing or newly-created) setting, or null if N/A.
     */
    public function ensureUnitRecurringSetting($tenant): ?InvoiceRecurringSetting
    {
        if (!$tenant || (int) $tenant->status !== TENANT_STATUS_ACTIVE || !$tenant->unit_id) {
            return null;
        }

        $existing = InvoiceRecurringSetting::where('property_unit_id', $tenant->unit_id)
            ->where('status', ACTIVE)
            ->first();
        if ($existing) {
            return $existing;
        }

        $unit = PropertyUnit::find($tenant->unit_id);
        if (!$unit) {
            return null;
        }

        $recurringType = match ((int) $unit->rent_type) {
            PROPERTY_UNIT_RENT_TYPE_MONTHLY => INVOICE_RECURRING_TYPE_MONTHLY,
            PROPERTY_UNIT_RENT_TYPE_YEARLY  => INVOICE_RECURRING_TYPE_YEARLY,
            default                         => null,
        };
        if ($recurringType === null) {
            return null;
        }

        $rent = (float) ($tenant->general_rent ?: $unit->general_rent);
        if ($rent <= 0) {
            return null;
        }

        $ownerUserId = $tenant->owner_user_id;

        // Self-heal the owner's default invoice types, then grab "Rent".
        ensureOwnerDefaults($ownerUserId, InvoiceType::class, 'setOwnerInvoiceType');
        $rentType = InvoiceType::where('owner_user_id', $ownerUserId)->where('name', 'Rent')->first();
        if (!$rentType) {
            return null;
        }

        // Honour the unit's chosen due day (absolute day-of-month) as the relative days-after —
        // the cron runs near the 1st, so this lands close to the owner's intended due day.
        $dueDay = (int) ($recurringType === INVOICE_RECURRING_TYPE_YEARLY ? $unit->yearly_due_day : $unit->monthly_due_day);
        $dueDayAfter = ($dueDay >= 1 && $dueDay <= 31) ? $dueDay : 5;

        try {
            return DB::transaction(function () use ($tenant, $ownerUserId, $recurringType, $rent, $rentType, $dueDayAfter) {
                $setting = new InvoiceRecurringSetting();
                $setting->invoice_prefix   = 'INV';
                $setting->owner_user_id    = $ownerUserId;
                $setting->property_id      = $tenant->property_id;
                $setting->property_unit_id = $tenant->unit_id;
                $setting->start_date       = now();
                $setting->recurring_type   = $recurringType;
                $setting->cycle_day        = null;
                $setting->due_day_after    = $dueDayAfter;
                $setting->status           = ACTIVE;
                $setting->amount           = $rent;
                $setting->save();

                $item = new InvoiceRecurringSettingItem();
                $item->invoice_recurring_setting_id = $setting->id;
                $item->invoice_type_id = $rentType->id;
                $item->amount          = $rent;
                $item->description     = __('Rent');
                $item->save();

                return $setting;
            });
        } catch (\Throwable $e) {
            Log::error('ensureUnitRecurringSetting failed for tenant ' . $tenant->id . ' — ' . $e->getMessage());
            return null;
        }
    }

    public function getAllData()
    {
        $invoiceRecurring = InvoiceRecurringSetting::query()
            ->where('invoice_recurring_settings.owner_user_id', auth()->id())
            ->join('properties', 'invoice_recurring_settings.property_id', '=', 'properties.id')
            ->join('property_units', 'invoice_recurring_settings.property_unit_id', '=', 'property_units.id')
            ->select(['invoice_recurring_settings.*', 'properties.name as propertyName', 'property_units.unit_name']);

        return datatables($invoiceRecurring)
            ->addColumn('prefix', function ($invoiceRecurring) {
                return '<h6>' . $invoiceRecurring->invoice_prefix . '</h6>';
            })
            ->addColumn('property', function ($invoiceRecurring) {
                return '<h6>' . @$invoiceRecurring->propertyName . '</h6>
                        <p class="font-13">' . @$invoiceRecurring->unit_name . '</p>';
            })
            ->addColumn('type', function ($invoiceRecurring) {
                $type = '';
                if ($invoiceRecurring->recurring_type == INVOICE_RECURRING_TYPE_MONTHLY) {
                    $type = '<h6>Monthly</h6>';
                } elseif ($invoiceRecurring->recurring_type == INVOICE_RECURRING_TYPE_YEARLY) {
                    $type = '<h6>Yearly</h6>';
                } elseif ($invoiceRecurring->recurring_type == INVOICE_RECURRING_TYPE_CUSTOM) {
                    $type = '<h6>Custom</h6><p>' . $invoiceRecurring->cycle_day . ' Days</p>';
                }
                return $type;
            })
            ->addColumn('amount', function ($invoiceRecurring) {
                return currencyPrice($invoiceRecurring->amount);
            })
            ->addColumn('status', function ($invoiceRecurring) {
                if ($invoiceRecurring->status == ACTIVE) {
                    return '<div class="status-btn status-btn-blue font-13 radius-4">Active</div>';
                } else {
                    return '<div class="status-btn status-btn-orange font-13 radius-4">Inactive</div>';
                }
            })
            ->addColumn('action', function ($invoiceRecurring) {
                $html = '<div class="tbl-action-btns d-inline-flex">';
                $html .= '<button type="button" class="p-1 tbl-action-btn edit" data-detailsurl="' . route('owner.invoice.recurring-setting.details', $invoiceRecurring->id) . '" title="' . __('Edit') . '"><span class="iconify" data-icon="clarity:note-edit-solid"></span></button>';
                $html .= '<button type="button" class="p-1 tbl-action-btn view" data-detailsurl="' . route('owner.invoice.recurring-setting.details', $invoiceRecurring->id) . '" title="' . __('View') . '"><span class="iconify" data-icon="carbon:view-filled"></span></button>';
                $html .= '<button type="button" onclick="deleteItem(\'' . route('owner.invoice.recurring-setting.destroy', $invoiceRecurring->id) . '\', \'allInvoiceDatatable\')" class="p-1 tbl-action-btn" title="' . __('Delete') . '"><span class="iconify" data-icon="ep:delete-filled"></span></button>';
                $html .= '</div>';
                return $html;
            })
            ->rawColumns(['prefix', 'property', 'type', 'status', 'action'])
            ->make(true);
    }

    public function getById($id)
    {
        return InvoiceRecurringSetting::where('owner_user_id', auth()->id())->findOrFail($id);
    }

    public function getItemsByInvoiceRecurringId($id)
    {
        return InvoiceRecurringSettingItem::where('invoice_recurring_setting_id', $id)->get();
    }

    public function store($request)
    {
        DB::beginTransaction();
        try {
            $id = $request->get('id', '');
            if ($request->property_id !== 'All' && $request->property_unit_id !== 'All') {
                $this->storeSingleRecurringSetting($request, $id);
            } elseif ($request->property_id === 'All') {
                $this->storeRecurringSettingForAllProperties($request, $id);
            } elseif ($request->property_unit_id === 'All') {
                $this->storeRecurringSettingForAllUnits($request, $id);
            }

            DB::commit();
            $message = $request->id ? __(UPDATED_SUCCESSFULLY) : __(CREATED_SUCCESSFULLY);
            return $this->success([], $message);
        } catch (\Exception $e) {
            DB::rollBack();
            $message = getErrorMessage($e, $e->getMessage());
            return $this->error([],  $message);
        }
    }

    private function storeSingleRecurringSetting($request, $id, $tenant=null)
    {
        if ($tenant==null){
            $tenant = $this->getTenant($request->property_unit_id);
        }else{
            $tenant=$tenant;
        }

        $invoiceRecurring = $this->getOrCreateRecurringSetting($request, $id, $tenant);
        $totalAmount = $this->calculateTotalAmount($request, $invoiceRecurring);
        $this->saveInvoiceRecurring($request, $invoiceRecurring, $totalAmount['totalAmount']);
    }

    private function storeRecurringSettingForAllProperties($request, $id)
    {
        $tenantsToInvoice = $this->getTenantsToInvoice($request);

        foreach ($tenantsToInvoice as $tenant) {
            $this->storeSingleRecurringSetting($request, $id, $tenant);
        }
    }

    private function storeRecurringSettingForAllUnits($request, $id)
    {
        $tenantsToInvoice = $this->getTenantsToInvoice($request, true);

        foreach ($tenantsToInvoice as $tenant) {
            $this->storeSingleRecurringSetting($request, $id, $tenant);
        }
    }

    private function getTenant($unitId)
    {
        $tenant = Tenant::where('owner_user_id', auth()->id())
            ->where('unit_id', $unitId)
            ->where('status', TENANT_STATUS_ACTIVE)
            ->first();
        if (!$tenant) {
            throw new Exception(__('Tenant Not Found'));
        }
        return $tenant;
    }

    private function getOrCreateRecurringSetting($request, $id, $tenant)
    {
        if ($id != '') {
            $invoiceRecurring = $invoiceRecurring = InvoiceRecurringSetting::where('owner_user_id', auth()->id())->findOrFail($request->id);
        } else {
            if (!getOwnerLimit(RULES_AUTO_INVOICE) > 0) {
                throw new Exception('Your Auto Invoice Settings Limit is Finished. Choose or Renew Package Plan');
            }
            $invoiceRecurring = new InvoiceRecurringSetting();
        }

        $invoiceRecurring->invoice_prefix = $request->invoice_prefix;
        $invoiceRecurring->owner_user_id = auth()->id();
        $invoiceRecurring->property_id = $tenant->property_id;
        $invoiceRecurring->property_unit_id = $tenant->unit_id;
        $invoiceRecurring->start_date = $request->start_date ?? now();
        $invoiceRecurring->recurring_type = $request->recurring_type;
        $invoiceRecurring->cycle_day = $request->cycle_day;
        $invoiceRecurring->due_day_after = $request->due_day_after;
        $invoiceRecurring->status = $request->status;
        $invoiceRecurring->save();

        return $invoiceRecurring;
    }

    private function calculateTotalAmount($request, $invoiceRecurring)
    {
        $totalAmount = 0;
        $now = now();

        if (is_null($request->invoiceItem)) {
            throw new Exception(__('No Item Add'));
        }

        foreach ($request->invoiceItem['invoice_type_id'] as $index => $invoiceTypeId) {
            $invoiceRecurringItem = $this->getOrCreateInvoiceRecurringItem($request, $invoiceRecurring, $index);
            $totalAmount += $invoiceRecurringItem->amount;
        }

        InvoiceRecurringSettingItem::where('invoice_recurring_setting_id', $invoiceRecurring->id)->where('updated_at', '!=', $now)->get()->map(function ($q) {
            $q->delete();
        });

        return ['totalAmount'=>$totalAmount];
    }
    

    private function getOrCreateInvoiceRecurringItem($request, $invoiceRecurring, $index)
    {
        if ($request->invoiceItem['id'][$index]) {
            $invoiceRecurringItem = InvoiceRecurringSettingItem::findOrFail($request->invoiceItem['id'][$index]);
        } else {
            $invoiceRecurringItem = new InvoiceRecurringSettingItem();
        }

        $invoiceRecurringItem->invoice_recurring_setting_id = $invoiceRecurring->id;
        $invoiceRecurringItem->invoice_type_id = $request->invoiceItem['invoice_type_id'][$index];
        $invoiceRecurringItem->description = $request->invoiceItem['description'][$index];
        $invoiceRecurringItem->updated_at = now();
        $invoiceType = InvoiceType::findOrFail($request->invoiceItem['invoice_type_id'][$index]);

        if ($invoiceType->name == 'Rent'){
            $invoiceRecurringItem->amount = $invoiceRecurring->propertyUnit->general_rent;
        }else{
            $invoiceRecurringItem->amount = $request->invoiceItem['amount'][$index];
        }

        $invoiceRecurringItem->save();

        return $invoiceRecurringItem;
    }

    private function saveInvoiceRecurring($request, $invoiceRecurring, $totalAmount)
    {
        $invoiceRecurring->amount = $totalAmount;
        $invoiceRecurring->save();
    }

    private function getTenantsToInvoice($request, $units=false)
    {
        if ($units){
            $tenants = Tenant::query()
                    ->leftJoin('users', 'tenants.user_id', '=', 'users.id')
                    ->select(['tenants.*', 'users.first_name', 'users.last_name', 'users.contact_number', 'users.email'])
                    ->where('tenants.status', TENANT_STATUS_ACTIVE)
                    ->where('tenants.property_id', $request->property_id)
                    ->where('tenants.owner_user_id', auth()->id())
                    ->get();
        }else{
            $tenantService = new TenantService;
            $tenants = $tenantService->getActiveAll();
        }

        if (count($tenants) === 0) {
            throw new Exception(__('No Active Tenants Found for All Properties'));
        }
        $tenantsToInvoice = $tenants;

        return $tenantsToInvoice;
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $invoice = InvoiceRecurringSetting::where('owner_user_id', auth()->id())->findOrFail($id);
            $invoice->delete();
            DB::commit();
            $message = __(DELETED_SUCCESSFULLY);
            return $this->success([], $message);
        } catch (\Exception $e) {
            DB::rollBack();
            $message = getErrorMessage($e, $e->getMessage());
            return $this->error([],  $message);
        }
    }
}
