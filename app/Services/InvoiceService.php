<?php

namespace App\Services;

use App\Models\Currency;
use App\Models\EmailTemplate;
use App\Models\Gateway;
use App\Models\GatewayCurrency;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InvoiceType;
use App\Models\Order;
use App\Models\Owner;
use App\Models\Property;
use App\Models\Tenant;
use App\Services\SmsMail\MailService;
use App\Services\TenantService;
use App\Traits\ResponseTrait;
use Exception;
use Illuminate\Support\Facades\DB;
use App\Jobs\SendSmsJob;
use App\Jobs\SendInvoiceNotificationAndEmailJob;
use Carbon\Carbon;
use Illuminate\Support\Str;

class InvoiceService
{
    use ResponseTrait;

    // ── Shared HTML helpers ───────────────────────────────────

   
    private function invoiceCell($invoice)
    {
        $subtitle = $invoice->item_types_label 
            ? '<span class="ow-cell-sub">' . e($invoice->item_types_label) . '</span>'
            : '<span class="ow-cell-sub">—</span>';
        
        return '<span class="ow-inv-no">' . e($invoice->invoice_no) . '</span>' . $subtitle;
    }


    private function propertyCell($invoice)
    {
        $phone = $invoice->contact_number
            ? '<a href="tel:' . e($invoice->contact_number) . '" class="ow-cell-phone">' . e($invoice->contact_number) . '</a>'
            : '';

        return '<span class="ow-prop-name">' . e($invoice->property_name) . '</span>'
             . '<span class="ow-unit-name">' . e($invoice->unit_name) . '</span>'
             . '<span class="ow-unit-name">' . e($invoice->tenant_full_name) . '</span>'
             . ($phone ? '<span class="ow-unit-name">' . $phone . '</span>' : '');
    }

    private function statusBadge($invoice, $paidDate = null)
    {
        if ($invoice->status == INVOICE_STATUS_PAID) {
            $label = $paidDate
                ? __('Paid') . ' · ' . Carbon::parse($paidDate)->format('M d, Y \a\t g:i A')
                : __('Paid');
            return '<span class="ow-badge ow-badge--paid">'
                 . '<svg width="10" height="10" viewBox="0 0 16 16" fill="none"><path d="M3 8l4 4 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>'
                 . e($label) . '</span>';
        }

        if ($invoice->status == INVOICE_STATUS_OVER_DUE) {
            return '<span class="ow-badge ow-badge--overdue">'
                 . '<svg width="10" height="10" viewBox="0 0 16 16" fill="none"><path d="M8 5v4M8 11v.5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>'
                 . __('Overdue') . '</span>';
        }

        return '<span class="ow-badge ow-badge--pending">'
             . '<svg width="10" height="10" viewBox="0 0 16 16" fill="none"><circle cx="8" cy="8" r="5.5" stroke="currentColor" stroke-width="1.6"/><path d="M8 5v3.5l2 1.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>'
             . __('Pending') . '</span>';
    }

    private function bankPendingBadge($invoice)
    {
        if ($invoice->status == INVOICE_STATUS_OVER_DUE) {
            return '<span class="ow-badge ow-badge--overdue">'
                 . '<svg width="10" height="10" viewBox="0 0 16 16" fill="none"><path d="M8 5v4M8 11v.5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>'
                 . __('Overdue') . '</span>';
        }

        return '<span class="ow-badge ow-badge--bank">'
             . '<svg width="10" height="10" viewBox="0 0 16 16" fill="none"><rect x="1" y="4" width="14" height="10" rx="1" stroke="currentColor" stroke-width="1.6"/><path d="M1 7h14" stroke="currentColor" stroke-width="1.6"/></svg>'
             . __('Bank pending') . '</span>';
    }

    private function amountCell($invoice)
    {
        $amount = currencyPrice(invoiceItemTotalAmount($invoice->id));

        if ($invoice->status == INVOICE_STATUS_PAID) {
            return '<span class="ow-amt ow-amt--paid">' . $amount . '</span>';
        }
        if ($invoice->status == INVOICE_STATUS_OVER_DUE) {
            return '<span class="ow-amt ow-amt--overdue">' . $amount . '</span>';
        }
        return '<span class="ow-amt ow-amt--pending">' . $amount . '</span>';
    }

    private function dueDateCell($item, $showOverdueBadge = true)
    {
        $isOverdue = $item->status == INVOICE_STATUS_PENDING && $item->due_date < date('Y-m-d');
        $cls       = $isOverdue ? 'ow-muted--overdue' : 'ow-muted';
        $html      = '<span class="' . $cls . '" style="display:block">' . e($item->due_date) . '</span>';

        if ($isOverdue && $showOverdueBadge) {
            $html .= '<span class="ow-badge ow-badge--overdue" style="margin-top:4px;display:inline-flex">'
                   . '<svg width="10" height="10" viewBox="0 0 16 16" fill="none"><path d="M8 5v4M8 11v.5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>'
                   . __('Overdue') . '</span>';
        }

        return $html;
    }

    private function viewBtn($invoice)
    {
        return '<button type="button" class="ow-act ow-act--ghost view"'
             . ' data-detailsurl="' . route('owner.invoice.details', $invoice->id) . '"'
             . ' title="' . __('View') . '">'
             . '<svg width="11" height="11" viewBox="0 0 24 24" fill="none"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" stroke="currentColor" stroke-width="1.8"/><circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.8"/></svg>'
             . __('View') . '</button>';
    }

    private function editBtn($invoice)
    {
        return '<button type="button" class="ow-act ow-act--blue edit"'
             . ' data-detailsurl="' . route('owner.invoice.details', $invoice->id) . '"'
             . ' title="' . __('Edit') . '">'
             . '<svg width="11" height="11" viewBox="0 0 24 24" fill="none"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>'
             . __('Edit') . '</button>';
    }

    private function payStatusBtn($invoice)
    {
        return '<button type="button" class="ow-act ow-act--ghost payStatus"'
             . ' data-detailsurl="' . route('owner.invoice.details', $invoice->id) . '"'
             . ' title="' . __('Payment Status Change') . '">'
             . '<svg width="11" height="11" viewBox="0 0 24 24" fill="none"><path d="M9 12l2 2 4-4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.8"/></svg>'
             . __('Status') . '</button>';
    }

    private function deleteBtn($invoice, $tableId)
    {
        return '<button type="button" class="ow-act ow-act--ghost"'
             . ' onclick="deleteItem(\'' . route('owner.invoice.destroy', $invoice->id) . '\', \'' . $tableId . '\')"'
             . ' title="' . __('Delete') . '">'
             . '<svg width="11" height="11" viewBox="0 0 24 24" fill="none"><polyline points="3 6 5 6 21 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><path d="M19 6l-1 14H6L5 6M10 11v6M14 11v6M9 6V4h6v2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>'
             . __('Delete') . '</button>';
    }

    private function reminderBtn($invoice)
    {
        $isOverdue = $invoice->status == INVOICE_STATUS_OVER_DUE
                  || ($invoice->status == INVOICE_STATUS_PENDING && $invoice->due_date < date('Y-m-d'));
        $cls = $isOverdue ? 'ow-act ow-act--coral reminder' : 'ow-act ow-act--ghost reminder';

        return '<button type="button" class="' . $cls . '"'
             . ' data-id="' . $invoice->id . '"'
             . ' title="' . __('Send Reminder') . '">'
             . '<svg width="11" height="11" viewBox="0 0 24 24" fill="none"><path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9M13.73 21a2 2 0 01-3.46 0" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>'
             . __('Remind') . '</button>';
    }

    private function bankSlipBtn($invoice)
    {
        return '<a href="' . getFileUrl($invoice->folder_name, $invoice->file_name) . '"'
             . ' class="ow-act ow-act--ghost" title="' . __('Bank slip') . '" download>'
             . '<svg width="11" height="11" viewBox="0 0 24 24" fill="none"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M7 10l5 5 5-5M12 15V3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>'
             . __('Slip') . '</a>';
    }

    private function wrapActions(string $html): string
    {
        return '<div class="ow-row-actions">' . $html . '</div>';
    }

    private function rowClass($invoice): string
    {
        $isOverdue = $invoice->status == INVOICE_STATUS_OVER_DUE
                  || ($invoice->status == INVOICE_STATUS_PENDING && $invoice->due_date < date('Y-m-d'));
        if ($isOverdue) return 'ow-row--overdue';
        if ($invoice->status == INVOICE_STATUS_PAID) return 'ow-row--paid';
        return '';
    }

    // ── Public service methods (unchanged logic, new HTML) ────

    public function getAllInvoices()
    {
        $response['pageTitle']              = __('All Invoices');
        $response['invoices']               = Invoice::where('owner_user_id', auth()->id())->with(['property', 'propertyUnit', 'invoiceItems'])->latest()->get();
        $response['properties']             = Property::where('owner_user_id', auth()->id())->get();
        $response['invoiceTypes']           = InvoiceType::where('owner_user_id', auth()->id())->get();
        $response['pendingInvoices']        = Invoice::where('owner_user_id', auth()->id())->pending()->get();
        $response['paidInvoices']           = Invoice::where('owner_user_id', auth()->id())->paid()->get();
        $response['overDueInvoices']        = Invoice::where('owner_user_id', auth()->id())->overDue()->get();
        $response['totalInvoice']           = Invoice::where('owner_user_id', auth()->id())->count();
        $response['totalPendingInvoice']    = Invoice::where('owner_user_id', auth()->id())->pending()->count();
        $response['totalBankPendingInvoice']= Invoice::query()
            ->where('invoices.owner_user_id', auth()->id())
            ->join('orders', 'invoices.order_id', '=', 'orders.id')
            ->join('gateways', 'orders.gateway_id', '=', 'gateways.id')
            ->where('gateways.slug', 'bank')
            ->where('orders.payment_status', INVOICE_STATUS_PENDING)
            ->count();
        $response['totalPaidInvoice']       = Invoice::where('owner_user_id', auth()->id())->paid()->count();
        $response['totalOverDueInvoice']    = Invoice::where('owner_user_id', auth()->id())->overDue()->count();
        $response['totalPaidAmount']        = Invoice::where('owner_user_id', auth()->id())->paid()->sum('amount');
        $response['totalUnpaidAmount']      = Invoice::where('owner_user_id', auth()->id())->pending()->sum('amount');
        return $response;
    }

    public function getAll()
    {
        $data = Invoice::query()
            ->where('invoices.owner_user_id', auth()->id())
            ->leftJoin('orders', 'invoices.order_id', '=', 'orders.id')
            ->leftJoin('properties', 'invoices.property_id', '=', 'properties.id')
            ->leftJoin('gateways', 'orders.gateway_id', '=', 'gateways.id')
            ->leftJoin('file_managers', ['orders.deposit_slip_id' => 'file_managers.id', 'file_managers.origin_type' => (DB::raw("'App\\\Models\\\Order'"))])
            ->leftJoin('tenants', 'invoices.tenant_id', '=', 'tenants.id')
            ->leftJoin('users', 'tenants.user_id', '=', 'users.id')
            ->orderByDesc('invoices.id')
            ->select(['invoices.*', 'gateways.title as gatewayTitle', 'gateways.slug as gatewaySlug', 'file_managers.file_name', 'file_managers.folder_name', 'properties.name as property_name', 'property_units.unit_name', DB::raw("CONCAT(users.first_name, ' ', users.last_name) AS tenant_full_name")])
            ->get();
        return $data?->makeHidden(['created_at', 'updated_at', 'deleted_at']);
    }

    public function getAllInvoicesData($request)
    {
        $invoice = Invoice::query()
            ->where('invoices.owner_user_id', auth()->id())
            ->leftJoin('orders', 'invoices.order_id', '=', 'orders.id')
            ->leftJoin('properties', 'invoices.property_id', '=', 'properties.id')
            ->leftJoin('property_units', 'property_units.id', '=', 'invoices.property_unit_id')
            ->leftJoin('gateways', 'orders.gateway_id', '=', 'gateways.id')
            ->leftJoin('file_managers', ['orders.deposit_slip_id' => 'file_managers.id', 'file_managers.origin_type' => (DB::raw("'App\\\Models\\\Order'"))])
            ->leftJoin('tenants', 'invoices.tenant_id', '=', 'tenants.id')
            ->leftJoin('users', 'tenants.user_id', '=', 'users.id')
            ->orderByDesc('invoices.id');

        $this->applyRequestFilters($invoice, $request);

        $invoice->select(['invoices.*', 'gateways.title as gatewayTitle', 'gateways.slug as gatewaySlug', 'file_managers.file_name', 'file_managers.folder_name', 'properties.name as property_name', 'property_units.unit_name', 'users.contact_number', DB::raw("CONCAT(users.first_name, ' ', users.last_name) AS tenant_full_name, orders.updated_at as paidDate")]);

        return datatables($invoice)
            ->filterColumn('property', function ($query, $keyword) {
                $query->whereRaw("properties.name LIKE ?", ["%{$keyword}%"])
                    ->orWhereRaw("property_units.unit_name LIKE ?", ["%{$keyword}%"])
                    ->orWhereRaw("CONCAT(users.first_name, ' ', users.last_name) LIKE ?", ["%{$keyword}%"]);
            })
            ->addColumn('invoice',  fn($i) => $this->invoiceCell($i))
            ->addColumn('property', fn($i) => $this->propertyCell($i))
            ->addColumn('month', function ($item) {
                return '<span class="ow-muted">'
                     . e($item->month) . ' ' . Carbon::parse($item->created_at)->format('Y')
                     . '</span>';
            })
            ->addColumn('due_date', fn($i) => $this->dueDateCell($i))
            ->addColumn('amount',   fn($i) => $this->amountCell($i))
            ->addColumn('status',   fn($i) => $this->statusBadge($i, $i->paidDate))
            ->addColumn('gateway', function ($invoice) {
                if ($invoice->gatewaySlug == 'bank') {
                    return '<a href="' . getFileUrl($invoice->folder_name, $invoice->file_name) . '"'
                         . ' class="ow-muted" title="' . __('Bank slip download') . '" download>'
                         . e($invoice->gatewayTitle) . '</a>';
                }
                return '<span class="ow-muted">' . e($invoice->gatewayTitle ?? '—') . '</span>';
            })
            ->addColumn('action', function ($invoice) {
                $html = $this->viewBtn($invoice);

                if ($invoice->status == INVOICE_STATUS_PENDING) {
                    $html .= $this->editBtn($invoice);
                    $html .= $this->payStatusBtn($invoice);
                    $html .= $this->deleteBtn($invoice, 'allInvoiceDataTable');
                    $html .= $this->reminderBtn($invoice);
                    if ($invoice->gatewaySlug == 'bank') {
                        $html .= $this->bankSlipBtn($invoice);
                    }
                } elseif ($invoice->status == INVOICE_STATUS_PAID) {
                    if (in_array($invoice->gatewaySlug, ['cash', 'bank', '', null])) {
                        $html .= $this->payStatusBtn($invoice);
                        $html .= $this->deleteBtn($invoice, 'allInvoiceDataTable');
                    }
                    if ($invoice->gatewaySlug == 'bank') {
                        $html .= $this->bankSlipBtn($invoice);
                    }
                }

                return $this->wrapActions($html);
            })
            ->rawColumns(['invoice', 'property', 'month', 'due_date', 'amount', 'status', 'gateway', 'action'])
            ->setRowClass(fn($i) => $this->rowClass($i))
            ->make(true);
    }

    public function getPaidInvoicesData($request)
    {
        $invoice = Invoice::where('invoices.owner_user_id', auth()->id())
            ->leftJoin('orders', 'invoices.order_id', '=', 'orders.id')
            ->leftJoin('properties', 'invoices.property_id', '=', 'properties.id')
            ->leftJoin('property_units', 'property_units.id', '=', 'invoices.property_unit_id')
            ->leftJoin('gateways', 'orders.gateway_id', '=', 'gateways.id')
            ->leftJoin('file_managers', ['orders.deposit_slip_id' => 'file_managers.id', 'file_managers.origin_type' => (DB::raw("'App\\\Models\\\Order'"))])
            ->leftJoin('tenants', 'invoices.tenant_id', '=', 'tenants.id')
            ->leftJoin('users', 'tenants.user_id', '=', 'users.id')
            ->orderByDesc('invoices.id')
            ->where('invoices.status', INVOICE_STATUS_PAID);

        $this->applyRequestFilters($invoice, $request);

        $invoice->select(['invoices.*', 'gateways.title as gatewayTitle', 'gateways.slug as gatewaySlug', 'file_managers.file_name', 'file_managers.folder_name', 'properties.name as property_name', 'property_units.unit_name', 'users.contact_number', DB::raw("CONCAT(users.first_name, ' ', users.last_name) AS tenant_full_name, orders.updated_at as paidDate")]);

        return datatables($invoice)
            ->filterColumn('property', function ($query, $keyword) {
                $query->whereRaw("properties.name LIKE ?", ["%{$keyword}%"])
                    ->orWhereRaw("property_units.unit_name LIKE ?", ["%{$keyword}%"])
                    ->orWhereRaw("CONCAT(users.first_name, ' ', users.last_name) LIKE ?", ["%{$keyword}%"]);
            })
            ->addColumn('invoice',  fn($i) => $this->invoiceCell($i))
            ->addColumn('property', fn($i) => $this->propertyCell($i))
            ->addColumn('month', function ($item) {
                return '<span class="ow-muted">'
                     . e($item->month) . ' ' . Carbon::parse($item->created_at)->format('Y')
                     . '</span>';
            })
            ->addColumn('due_date', function ($item) {
                return '<span class="ow-muted">' . e($item->due_date) . '</span>';
            })
            ->addColumn('amount',  fn($i) => $this->amountCell($i))
            ->addColumn('status',  fn($i) => $this->statusBadge($i, $i->paidDate))
            ->addColumn('gateway', function ($invoice) {
                if ($invoice->gatewaySlug == 'bank') {
                    return '<a href="' . getFileUrl($invoice->folder_name, $invoice->file_name) . '"'
                         . ' class="ow-muted" title="' . __('Bank slip download') . '" download>'
                         . e($invoice->gatewayTitle) . '</a>';
                }
                return '<span class="ow-muted">' . e($invoice->gatewayTitle ?? '—') . '</span>';
            })
            ->addColumn('action', function ($invoice) {
                $html = $this->viewBtn($invoice);
                if (in_array($invoice->gatewaySlug, ['cash', 'bank', '', null])) {
                    $html .= $this->payStatusBtn($invoice);
                    $html .= $this->deleteBtn($invoice, 'paidInvoiceDataTable');
                }
                if ($invoice->gatewaySlug == 'bank') {
                    $html .= $this->bankSlipBtn($invoice);
                }
                return $this->wrapActions($html);
            })
            ->rawColumns(['invoice', 'property', 'month', 'due_date', 'amount', 'status', 'gateway', 'action'])
            ->setRowClass(fn($i) => $this->rowClass($i))
            ->make(true);
    }

    public function getPendingInvoicesData($request)
    {
        $invoice = Invoice::where('invoices.owner_user_id', auth()->id())
            ->leftJoin('properties', 'invoices.property_id', '=', 'properties.id')
            ->leftJoin('property_units', 'property_units.id', '=', 'invoices.property_unit_id')
            ->leftJoin('tenants', 'invoices.tenant_id', '=', 'tenants.id')
            ->leftJoin('users', 'tenants.user_id', '=', 'users.id')
            ->orderByDesc('invoices.id')
            ->where('invoices.status', INVOICE_STATUS_PENDING);

        $this->applyRequestFilters($invoice, $request);

        $invoice->select(['invoices.*', 'properties.name as property_name', 'property_units.unit_name', 'users.contact_number', DB::raw("CONCAT(users.first_name, ' ', users.last_name) AS tenant_full_name")]);

        return datatables($invoice)
            ->filterColumn('property', function ($query, $keyword) {
                $query->whereRaw("properties.name LIKE ?", ["%{$keyword}%"])
                    ->orWhereRaw("property_units.unit_name LIKE ?", ["%{$keyword}%"])
                    ->orWhereRaw("CONCAT(users.first_name, ' ', users.last_name) LIKE ?", ["%{$keyword}%"]);
            })
            ->addColumn('invoice',  fn($i) => $this->invoiceCell($i))
            ->addColumn('property', fn($i) => $this->propertyCell($i))
            ->addColumn('month', function ($item) {
                return '<span class="ow-muted">'
                     . e($item->month) . ' ' . Carbon::parse($item->created_at)->format('Y')
                     . '</span>';
            })
            ->addColumn('due_date', fn($i) => $this->dueDateCell($i))
            ->addColumn('amount',   fn($i) => $this->amountCell($i))
            ->addColumn('status',   fn($i) => $this->statusBadge($i))
            ->addColumn('action', function ($invoice) {
                $html = $this->viewBtn($invoice)
                      . $this->editBtn($invoice)
                      . $this->payStatusBtn($invoice)
                      . $this->deleteBtn($invoice, 'pendingInvoiceDataTable')
                      . $this->reminderBtn($invoice);
                if ($invoice->gatewaySlug == 'bank') {
                    $html .= $this->bankSlipBtn($invoice);
                }
                return $this->wrapActions($html);
            })
            ->rawColumns(['invoice', 'property', 'month', 'due_date', 'amount', 'status', 'action'])
            ->setRowClass(fn($i) => $this->rowClass($i))
            ->make(true);
    }

    public function getBankPendingInvoicesData()
    {
        $invoice = Invoice::query()
            ->join('orders', 'invoices.order_id', '=', 'orders.id')
            ->join('gateways', 'orders.gateway_id', '=', 'gateways.id')
            ->leftJoin('properties', 'invoices.property_id', '=', 'properties.id')
            ->leftJoin('property_units', 'property_units.id', '=', 'invoices.property_unit_id')
            ->join('file_managers', ['orders.deposit_slip_id' => 'file_managers.id', 'file_managers.origin_type' => (DB::raw("'App\\\Models\\\Order'"))])
            ->leftJoin('tenants', 'invoices.tenant_id', '=', 'tenants.id')
            ->leftJoin('users', 'tenants.user_id', '=', 'users.id')
            ->select(['invoices.*', 'properties.name as property_name', 'property_units.unit_name', 'gateways.title as gatewayTitle', 'gateways.slug as gatewaySlug', 'file_managers.file_name', 'file_managers.folder_name', 'users.contact_number', DB::raw("CONCAT(users.first_name, ' ', users.last_name) AS tenant_full_name")])
            ->where('gateways.slug', 'bank')
            ->where('invoices.owner_user_id', auth()->id())
            ->orderByDesc('invoices.id')
            ->where('orders.payment_status', INVOICE_STATUS_PENDING);

        $this->applyRequestFilters($invoice, request());

        return datatables($invoice)
            ->filterColumn('property', function ($query, $keyword) {
                $query->whereRaw("properties.name LIKE ?", ["%{$keyword}%"])
                    ->orWhereRaw("property_units.unit_name LIKE ?", ["%{$keyword}%"])
                    ->orWhereRaw("CONCAT(users.first_name, ' ', users.last_name) LIKE ?", ["%{$keyword}%"]);
            })
            ->addColumn('invoice',  fn($i) => $this->invoiceCell($i))
            ->addColumn('property', fn($i) => $this->propertyCell($i))
            ->addColumn('month', function ($item) {
                return '<span class="ow-muted">'
                     . e($item->month) . ' ' . Carbon::parse($item->created_at)->format('Y')
                     . '</span>';
            })
            ->addColumn('due_date', function ($item) {
                return '<span class="ow-muted">' . e($item->due_date) . '</span>';
            })
            ->addColumn('amount',  fn($i) => $this->amountCell($i))
            ->addColumn('status',  fn($i) => $this->bankPendingBadge($i))
            ->addColumn('gateway', function ($invoice) {
                return '<a href="' . getFileUrl($invoice->folder_name, $invoice->file_name) . '"'
                     . ' class="ow-muted" title="' . __('Bank slip download') . '" download>'
                     . e($invoice->gatewayTitle) . '</a>';
            })
            ->addColumn('action', function ($invoice) {
                $html = $this->viewBtn($invoice)
                      . $this->editBtn($invoice)
                      . $this->payStatusBtn($invoice)
                      . $this->deleteBtn($invoice, 'bankPendingInvoiceDataTable')
                      . $this->bankSlipBtn($invoice);
                return $this->wrapActions($html);
            })
            ->rawColumns(['invoice', 'property', 'month', 'due_date', 'amount', 'status', 'gateway', 'action'])
            ->setRowClass(fn($i) => $this->rowClass($i))
            ->make(true);
    }

    public function getOverDueInvoicesData($request)
    {
        $invoice = Invoice::query()
            ->where('invoices.owner_user_id', auth()->id())
            ->overDue()
            ->leftJoin('properties', 'invoices.property_id', '=', 'properties.id')
            ->leftJoin('property_units', 'property_units.id', '=', 'invoices.property_unit_id')
            ->leftJoin('tenants', 'invoices.tenant_id', '=', 'tenants.id')
            ->leftJoin('users', 'tenants.user_id', '=', 'users.id')
            ->select(['invoices.*', 'properties.name as property_name', 'property_units.unit_name', 'users.contact_number', DB::raw("CONCAT(users.first_name, ' ', users.last_name) AS tenant_full_name")]);

        $this->applyRequestFilters($invoice, $request);

        return datatables($invoice)
            ->filterColumn('property', function ($query, $keyword) {
                $query->whereRaw("properties.name LIKE ?", ["%{$keyword}%"])
                    ->orWhereRaw("property_units.unit_name LIKE ?", ["%{$keyword}%"])
                    ->orWhereRaw("CONCAT(users.first_name, ' ', users.last_name) LIKE ?", ["%{$keyword}%"]);
            })
            ->addColumn('invoice',  fn($i) => $this->invoiceCell($i))
            ->addColumn('property', fn($i) => $this->propertyCell($i))
            ->addColumn('month', function ($item) {
                return '<span class="ow-muted">'
                     . e($item->month) . ' ' . Carbon::parse($item->created_at)->format('Y')
                     . '</span>';
            })
            ->addColumn('due_date', fn($i) => $this->dueDateCell($i))
            ->addColumn('amount',   fn($i) => $this->amountCell($i))
            ->addColumn('status', function ($invoice) {
                return '<span class="ow-badge ow-badge--overdue">'
                     . '<svg width="10" height="10" viewBox="0 0 16 16" fill="none"><path d="M8 5v4M8 11v.5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>'
                     . __('Overdue') . '</span>';
            })
            ->addColumn('action', function ($invoice) {
                $html = $this->viewBtn($invoice)
                      . $this->editBtn($invoice)
                      . $this->payStatusBtn($invoice)
                      . $this->deleteBtn($invoice, 'overdueInvoiceDataTable')
                      . $this->reminderBtn($invoice);
                return $this->wrapActions($html);
            })
            ->rawColumns(['invoice', 'property', 'month', 'due_date', 'amount', 'status', 'action'])
            ->setRowClass(fn($i) => $this->rowClass($i))
            ->make(true);
    }

    public function store($request)
    {
        DB::beginTransaction();
        try {
            $id = $request->get('id', '');
            if ($request->property_id !== 'All' && $request->property_unit_id !== 'All') {
                $this->storeSingleInvoice($request, $id);
            } elseif ($request->property_id === 'All') {
                $this->storeInvoicesForAllProperties($request, $id);
            } elseif ($request->property_unit_id === 'All') {
                $this->storeInvoicesForAllUnits($request, $id);
            }
            DB::commit();
            $message = $request->id ? __(UPDATED_SUCCESSFULLY) : __(CREATED_SUCCESSFULLY);
            return $this->success([], $message);
        } catch (\Exception $e) {
            DB::rollBack();
            $message = getErrorMessage($e, $e->getMessage());
            return $this->error([], $message);
        }
    }

    private function storeSingleInvoice($request, $id, $tenant = null)
    {
        if ($tenant == null) {
            $tenant = $this->getTenant($request->property_unit_id);
            $this->validateInvoiceExistence($request, $id, $tenant);
        }
        $invoice = $this->getOrCreateInvoice($request, $id, $tenant);
        $totalAmountAndTax = $this->calculateTotalAmount($request, $invoice);
        $this->saveInvoiceItems($request, $invoice, $totalAmountAndTax['totalAmount'], $totalAmountAndTax['totalTax']);

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

        $message = __('New ' . $invoice->month . ' invoice  from Centresidence due on' . ' ' . $invoice->due_date . '. Pay instantly: ') . route('instant.invoice.pay', ['token' => $invoice->payment_token]);
        SendSmsJob::dispatch([$tenant->user->contact_number], $message, auth()->id());
    }

    private function storeInvoicesForAllProperties($request, $id)
    {
        foreach ($this->getTenantsToInvoice($request) as $tenant) {
            $this->storeSingleInvoice($request, $id, $tenant);
        }
    }

    private function storeInvoicesForAllUnits($request, $id)
    {
        foreach ($this->getTenantsToInvoice($request, true) as $tenant) {
            $this->storeSingleInvoice($request, $id, $tenant);
        }
    }

    private function saveInvoiceItems($request, $invoice, $totalAmount, $totalTax)
    {
        $invoice->amount     = $totalAmount;
        $invoice->tax_amount = $totalTax;
        $invoice->save();
    }

    private function getTenant($unitId)
    {
        $tenant = Tenant::where('owner_user_id', auth()->id())
            ->where('unit_id', $unitId)
            ->where('status', TENANT_STATUS_ACTIVE)
            ->firstOrFail();
        if (!$tenant) {
            throw new Exception(__('Tenant Not Found'));
        }
        return $tenant;
    }

    private function applyRequestFilters($query, $request)
    {
        if ($request->filled('filter_property')) {
            $keyword = $request->filter_property;
            $query->where(function ($q) use ($keyword) {
                $q->where('properties.name', 'LIKE', "%{$keyword}%")
                  ->orWhere('property_units.unit_name', 'LIKE', "%{$keyword}%")
                  ->orWhereRaw("CONCAT(users.first_name, ' ', users.last_name) LIKE ?", ["%{$keyword}%"]);
            });
        }

        if ($request->filled('filter_month')) {
            $keyword = $request->filter_month;
            // Month column is stored as e.g. "April" and year derived from
            // created_at, so we search both month name and full "April 2025"
            $parts = explode(' ', trim($keyword));
            $month = $parts[0] ?? '';
            $year  = $parts[1] ?? '';
            $query->where(function ($q) use ($month, $year) {
                $q->where('invoices.month', 'LIKE', "%{$month}%");
                if ($year) {
                    $q->whereYear('invoices.created_at', $year);
                }
            });
        }

        if ($request->filled('filter_search')) {
            $keyword = $request->filter_search;
            $query->where(function ($q) use ($keyword) {
                $q->where('invoices.invoice_no', 'LIKE', "%{$keyword}%")
                  ->orWhere('invoices.name', 'LIKE', "%{$keyword}%")
                  ->orWhere('properties.name', 'LIKE', "%{$keyword}%")
                  ->orWhere('property_units.unit_name', 'LIKE', "%{$keyword}%")
                  ->orWhereRaw("CONCAT(users.first_name, ' ', users.last_name) LIKE ?", ["%{$keyword}%"]);
            });
        }

        return $query;
    }

    private function requestContainsRent($request): bool
    {
        if (empty($request->invoiceItem['invoice_type_id'])) return false;
        foreach ($request->invoiceItem['invoice_type_id'] as $typeId) {
            $type = InvoiceType::find($typeId);
            if ($type && strtolower($type->name) === 'rent') return true;
        }
        return false;
    }

    private function validateInvoiceExistence($request, $id, $tenant)
    {
        // Only enforce the one-per-month rule when the new invoice contains
        // a Rent item. Supplementary charges (water, repairs, etc.) are always
        // allowed even if a rent invoice for that month already exists.
        if (!$this->requestContainsRent($request)) return;

        $exists = Invoice::query()
            ->where('property_id', $request->property_id)
            ->where('property_unit_id', $request->property_unit_id)
            ->where('owner_user_id', auth()->id())
            ->where('month', $request->month)
            ->where('tenant_id', $tenant->id)
            ->whereYear('created_at', '=', date('Y'))
            ->whereHas('invoiceItems.invoiceType', function ($q) {
                $q->whereRaw('LOWER(name) = ?', ['rent']);
            })
            ->where(function ($q) use ($id) {
                if ($id != '') $q->whereNot('id', $id);
            })
            ->exists();

        if ($exists) {
            throw new Exception(__('A rent invoice for this month already exists.'));
        }
    }

    private function getOrCreateInvoice($request, $id, $tenant)
    {
        if ($id != '') {
            $invoice = Invoice::findOrFail($request->id);

            // Paid invoices are immutable — creating a supplementary invoice
            // for the same month is the correct path for additional charges.
            if ($invoice->status == INVOICE_STATUS_PAID) {
                throw new Exception(__('Paid invoices cannot be edited. Please create a new invoice for any additional charges.'));
            }
        } else {
            if (!getOwnerLimit(RULES_INVOICE) > 0) {
                throw new Exception(__('Your Invoice Limit is Finished. Choose or Renew Package Plan'));
            }
            $invoice = new Invoice();
        }

        $invoice->name                    = $request->name;
        $invoice->tenant_id               = $tenant->id;
        $invoice->owner_user_id           = auth()->id();
        $invoice->property_id             = $tenant->property_id;
        $invoice->property_unit_id        = $tenant->unit_id;
        $invoice->month                   = $request->month;
        $invoice->due_date                = $request->due_date;
        $invoice->payment_token           = Str::uuid();
        $invoice->payment_token_expires_at = now()->addDays(7);
        $invoice->save();

        return $invoice;
    }

    private function calculateTotalAmount($request, $invoice)
    {
        $totalAmount = 0;
        $totalTax    = 0;
        $now         = now();
        $tax         = taxSetting(auth()->id());

        if (is_null($request->invoiceItem)) {
            throw new Exception(__('Add invoice item at least one'));
        }

        foreach ($request->invoiceItem['invoice_type_id'] as $index => $invoiceTypeId) {
            $invoiceItem  = $this->getOrCreateInvoiceItem($request, $invoice, $index);
            $totalAmount += $invoiceItem->amount + $invoiceItem->tax_amount;
            $totalTax    += $invoiceItem->tax_amount;
        }

        InvoiceItem::where('invoice_id', $invoice->id)
            ->where('updated_at', '!=', $now)
            ->get()
            ->map(fn($q) => $q->delete());

        return ['totalAmount' => $totalAmount, 'totalTax' => $totalTax];
    }

    private function getOrCreateInvoiceItem($request, $invoice, $index)
    {
        if ($request->invoiceItem['id'][$index]) {
            $invoiceItem = InvoiceItem::findOrFail($request->invoiceItem['id'][$index]);
        } else {
            $invoiceItem = new InvoiceItem();
        }

        $invoiceItem->invoice_id      = $invoice->id;
        $invoiceItem->invoice_type_id = $request->invoiceItem['invoice_type_id'][$index];
        $invoiceItem->description     = $request->invoiceItem['description'][$index];
        $invoiceItem->updated_at      = now();

        $invoiceType = InvoiceType::findOrFail($request->invoiceItem['invoice_type_id'][$index]);

        if ($invoiceType->name == 'Rent') {
            $invoiceItem->amount = $invoice->propertyUnit->general_rent;
        } else {
            $invoiceItem->amount = $request->invoiceItem['amount'][$index];
        }

        $tax = taxSetting(auth()->id());
        if (isset($tax) && $tax->type == TAX_TYPE_PERCENTAGE) {
            $invoiceItem->tax_amount = $invoiceItem->amount * $invoiceType->tax * 0.01;
        } else {
            $invoiceItem->tax_amount = $invoiceType->tax;
        }

        $invoiceItem->save();
        return $invoiceItem;
    }

    private function getTenantsToInvoice($request, $units = false)
    {
        if ($units) {
            $tenants = Tenant::query()
                ->leftJoin('users', 'tenants.user_id', '=', 'users.id')
                ->select(['tenants.*', 'users.first_name', 'users.last_name', 'users.contact_number', 'users.email'])
                ->where('tenants.status', TENANT_STATUS_ACTIVE)
                ->where('tenants.property_id', $request->property_id)
                ->where('tenants.owner_user_id', auth()->id())
                ->get();
        } else {
            $tenants = (new TenantService)->getActiveAll();
        }

        if (count($tenants) === 0) {
            throw new Exception(__('No Active Tenants Found for All Properties'));
        }

        $tenantsToInvoice = [];
        foreach ($tenants as $tenant) {
            // For Rent invoices, skip tenants who already have a rent invoice
            // this month. For supplementary types, always include all tenants.
            $hasRentInvoice = Invoice::where('property_id', $tenant->property_id)
                ->where('property_unit_id', $tenant->unit_id)
                ->where('owner_user_id', auth()->id())
                ->where('month', $request->month)
                ->whereYear('created_at', '=', date('Y'))
                ->whereHas('invoiceItems.invoiceType', function ($q) {
                    $q->whereRaw('LOWER(name) = ?', ['rent']);
                })
                ->exists();

            if (!$hasRentInvoice || !$this->requestContainsRent($request)) {
                $tenantsToInvoice[] = $tenant;
            }
        }

        if (empty($tenantsToInvoice)) {
            throw new Exception(__('Invoices Already Generated for that Month'));
        }

        return $tenantsToInvoice;
    }

    public function sendSingleNotification($request)
    {
        try {
            $invoice = Invoice::where('owner_user_id', auth()->id())->findOrFail($request->invoice_id);
            addNotification($request->title, $request->body, null, null, $invoice->tenant->user_id, auth()->id());

            if (getOption('send_email_status', 0) == ACTIVE) {
                $ownerUserId = auth()->id();
                $mailService = new MailService;
                $template    = EmailTemplate::where('owner_user_id', $ownerUserId)->where('category', EMAIL_TEMPLATE_REMINDER)->where('status', ACTIVE)->first();
                if ($template) {
                    $content = getEmailTemplate($template->body, ['{{app_name}}' => getOption('app_name')]);
                    $mailService->sendCustomizeMail([$invoice->tenant->user->email], $template->subject, $content);
                } else {
                    $mailService->sendReminderMail([$invoice->tenant->user->email], $request->title, $request->body, $ownerUserId);
                }
            }

            return $this->success([], __('Notification Sent Successfully'));
        } catch (\Exception $e) {
            return $this->error([], getErrorMessage($e, $e->getMessage()));
        }
    }

    public function sendMultiNotification($request)
    {
        try {
            $tenants = Tenant::query()
                ->where('owner_user_id', auth()->id())
                ->where('status', TENANT_STATUS_ACTIVE)
                ->when($request->property_id, fn($q) => $q->where('property_id', $request->property_id))
                ->when($request->unit_id,     fn($q) => $q->where('unit_id', $request->unit_id))
                ->select('user_id')
                ->get();

            $mailService = new MailService;
            foreach ($tenants as $tenant) {
                addNotification($request->title, $request->body, null, null, $tenant->user_id, auth()->id());
                if (getOption('send_email_status', 0) == ACTIVE) {
                    $ownerUserId = auth()->id();
                    $template    = EmailTemplate::where('owner_user_id', $ownerUserId)->where('category', EMAIL_TEMPLATE_REMINDER)->where('status', ACTIVE)->first();
                    if ($template) {
                        $content = getEmailTemplate($template->body, ['{{app_name}}' => getOption('app_name')]);
                        $mailService->sendCustomizeMail([$tenant->user->email], $template->subject, $content);
                    } else {
                        $mailService->sendReminderMail([$tenant->user->email], $request->title, $request->body, $ownerUserId);
                    }
                }
            }

            return $this->success([], __('Notification Sent Successfully'));
        } catch (\Exception $e) {
            return $this->error([], getErrorMessage($e, $e->getMessage()));
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            Invoice::where('owner_user_id', auth()->id())->findOrFail($id)->delete();
            DB::commit();
            return $this->success([], __(DELETED_SUCCESSFULLY));
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error([], getErrorMessage($e, $e->getMessage()));
        }
    }

    public function types()
    {
        return InvoiceType::where('owner_user_id', auth()->id())->get()
            ?->makeHidden(['created_at', 'updated_at', 'deleted_at']);
    }

    public function getById($id)
    {
        $userId = auth()->user()->role == USER_ROLE_OWNER ? auth()->id() : auth()->user()->owner_user_id;
        return Invoice::where('owner_user_id', $userId)->findOrFail($id)
            ?->makeHidden(['created_at', 'deleted_at', 'invoice_recurring_setting_id']);
    }

    public function getByIdCheckTenantAuthId($id)
    {
        return Invoice::query()
            ->where('owner_user_id', auth()->user()->owner_user_id)
            ->where('tenant_id', auth()->user()->tenant->id)
            ->findOrFail($id)
            ?->makeHidden(['created_at', 'updated_at', 'deleted_at', 'invoice_recurring_setting_id']);
    }

    public function getItemsByInvoiceId($id)
    {
        return InvoiceItem::where('invoice_id', $id)->get()
            ?->makeHidden(['created_at', 'updated_at', 'deleted_at']);
    }

    public function ownerInfo($ownerUserId)
    {
        return Owner::query()
            ->leftJoin('file_managers', 'owners.logo_id', '=', 'file_managers.id')
            ->where('user_id', $ownerUserId)
            ->select(['owners.print_name', 'owners.print_address', 'owners.print_contact', 'file_managers.folder_name', 'file_managers.file_name'])
            ->first();
    }

    public function getByTenantId($id)
    {
        $invoices = Invoice::where('invoices.owner_user_id', auth()->user()->owner_user_id)
            ->where('invoices.tenant_id', $id)
            ->leftJoin('orders', 'invoices.order_id', '=', 'orders.id')
            ->orderByDesc('invoices.id')
            ->select(['invoices.*', DB::raw('orders.updated_at as paidDate')])
            ->get();

        return $invoices->map(function ($invoice) {
            $invoice->makeHidden(['updated_at', 'deleted_at', 'invoice_recurring_setting_id']);
            $invoice->paid_date_label = $invoice->paidDate
                ? Carbon::parse($invoice->paidDate)->format('M d, Y \a\t g:i A')
                : null;
            return $invoice;
        });
    }

    public function getOrderById($id)
    {
        return Order::query()
            ->leftJoin('gateways', 'orders.gateway_id', '=', 'gateways.id')
            ->select(['orders.*', 'gateways.title as gatewayTitle'])
            ->where('orders.payment_status', INVOICE_STATUS_PAID)
            ->where('orders.id', $id)
            ->first()
            ?->makeHidden(['created_at', 'updated_at', 'deleted_at']);
    }

    public function getCurrencyByGatewayId($id)
    {
        // Check if this is the company's rent gateway (transaction model)
        $rentAccountId  = getOption('centresidence_rent_mpesa_account_id');
        $rentMpesaAccount = $rentAccountId ? \App\Models\MpesaAccount::find($rentAccountId) : null;
        $rentGatewayId  = $rentMpesaAccount?->gateway_id;

        $query = GatewayCurrency::where('gateway_id', $id);

        // Only scope to the tenant's owner if this is NOT the company rent gateway
        if ((int)$id !== (int)$rentGatewayId) {
            $query->where('owner_user_id', auth()->user()->owner_user_id);
        }

        return $query->get()
            ?->makeHidden(['created_at', 'updated_at', 'deleted_at', 'gateway_id', 'owner_user_id']);
    }

    public function paymentStatusChange($request)
    {
        DB::beginTransaction();
        try {
            $invoice        = Invoice::where('owner_user_id', auth()->id())->findOrFail($request->id);
            $order          = Order::find($invoice->order_id);
            $gateway        = Gateway::where(['owner_user_id' => auth()->id(), 'slug' => 'cash', 'status' => ACTIVE])->firstOrFail();
            $gatewayCurrency = GatewayCurrency::where(['owner_user_id' => auth()->id(), 'gateway_id' => $gateway->id, 'currency' => 'KES'])->firstOrFail();

            if (is_null($order)) {
                $order = Order::create([
                    'user_id'            => $invoice->tenant->user->id,
                    'invoice_id'         => $invoice->id,
                    'amount'             => $invoice->amount,
                    'system_currency'    => Currency::where('current_currency', 'on')->first()->currency_code,
                    'gateway_id'         => $gateway->id,
                    'gateway_currency'   => $gatewayCurrency->currency,
                    'conversion_rate'    => 1,
                    'subtotal'           => $invoice->amount,
                    'total'              => $invoice->amount,
                    'transaction_amount' => $invoice->amount * 1,
                    'payment_status'     => INVOICE_STATUS_PENDING,
                    'bank_id'            => null,
                    'bank_name'          => null,
                    'bank_account_number'=> null,
                    'deposit_by'         => null,
                    'deposit_slip_id'    => null,
                ]);
            }

            $order->payment_status  = $request->status;
            $order->transaction_id  = str_replace('-', '', uuid_create(UUID_TYPE_RANDOM));
            $order->save();

            $invoice->order_id = $order->id;
            $invoice->status   = $request->status;
            $invoice->save();

            DB::commit();
            return $this->success([], __(UPDATED_SUCCESSFULLY));
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error([], getErrorMessage($e, $e->getMessage()));
        }
    }

    public function getInvoiceMonths()
    {
        $data = Invoice::query()
            ->where('invoices.owner_user_id', auth()->id())
            ->selectRaw('month, YEAR(due_date) as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->orderByRaw("STR_TO_DATE(month, '%M')")
            ->groupBy('month', 'year')
            ->get();

        return $data->map(fn($item) => (object)['formatted' => "{$item->month} {$item->year}"]);
    }
}