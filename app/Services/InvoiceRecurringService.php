<?php

namespace App\Services;

use App\Models\InvoiceRecurringSetting;
use App\Models\InvoiceRecurringSettingItem;
use App\Models\InvoiceType;
use App\Models\Tenant;
use App\Traits\ResponseTrait;
use Exception;
use Illuminate\Support\Facades\DB;

class InvoiceRecurringService
{
    use ResponseTrait;

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
                    return '<div class="status-btn status-btn-orange font-13 radius-4">Deactivate</div>';
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
        $invoiceRecurring->tenant_id = $tenant->id;
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
