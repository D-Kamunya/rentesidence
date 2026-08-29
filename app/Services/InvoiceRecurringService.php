<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InvoiceRecurringSetting;
use App\Models\InvoiceRecurringSettingItem;
use App\Models\InvoiceType;
use App\Models\PropertyUnit;
use App\Models\Tenant;
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
     * @return Invoice|null the existing (if the period was already billed) or newly-created invoice
     */
    public function generateRentInvoiceForPeriod($tenant, InvoiceRecurringSetting $setting, Carbon $periodStart): ?Invoice
    {
        $periodStart = $periodStart->copy()->startOfMonth();

        $existing = Invoice::where('property_unit_id', $setting->property_unit_id)
            ->where('tenant_id', $tenant->id)
            ->where('billing_period', $periodStart->toDateString())
            ->first();
        if ($existing) {
            return $existing;
        }

        return DB::transaction(function () use ($tenant, $setting, $periodStart) {
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
            foreach ($setting->items as $item) {
                $ii = new InvoiceItem();
                $ii->invoice_id      = $invoice->id;
                $ii->invoice_type_id = $item->invoice_type_id;
                $ii->amount          = $item->amount;
                $ii->description     = $item->description;
                $ii->save();
                $total += $ii->amount;
            }
            $invoice->amount = $total;
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
