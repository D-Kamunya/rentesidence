<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\MaintenanceRequest;
use App\Models\Property;
use App\Models\Tenant;
use App\Models\Invoice;
use Illuminate\Support\Facades\DB;

/**
 * Server-side report export — builds the COMPLETE dataset (no pagination) for a
 * PDF/print, so exports are never truncated to the visible DataTables page.
 *
 * The base queries MIRROR App\Services\ReportService (the Yajra ajax source);
 * kept separate on purpose so the export can never affect the live table path.
 * Each method returns ['title', 'headers', 'rows', 'totals'] for the shared
 * owner.report.pdf.table blade. All queries are scoped to the authenticated owner.
 */
class ReportExportService
{
    /** Resolve the report name to its payload, or null if unknown. */
    public function build(string $report): ?array
    {
        $map = [
            'tenant'      => 'tenant',
            'earning'     => 'earning',
            'expenses'    => 'expenses',
            'lease'       => 'leases',
            'occupancy'   => 'occupancy',
            'maintenance' => 'maintenance',
        ];

        if (! isset($map[$report])) {
            return null;
        }

        $method = $map[$report];

        return $this->{$method}();
    }

    private function filters(): array
    {
        $r = request();

        return [
            'property_id' => $r->input('property_id') ?: null,
            'unit_id'     => $r->input('unit_id') ?: null,
            'start_date'  => $r->input('start_date') ?: null,
            'end_date'    => $r->input('end_date') ?: null,
        ];
    }

    private function tenant(): array
    {
        $rows = Tenant::query()
            ->leftJoin('users', 'tenants.user_id', '=', 'users.id')
            ->leftJoin('properties', 'tenants.property_id', '=', 'properties.id')
            ->leftJoin('property_units', 'tenants.unit_id', '=', 'property_units.id')
            ->leftJoin(DB::raw('(select tenant_id, SUM(amount) as paid from invoices where status = 1 group By tenant_id) as inv_paid'), ['inv_paid.tenant_id' => 'tenants.id'])
            ->leftJoin(DB::raw('(select tenant_id, SUM(amount) as due from invoices where status = 0 group By tenant_id) as inv_due'), ['inv_due.tenant_id' => 'tenants.id'])
            ->select(['tenants.*', 'inv_paid.paid', 'inv_due.due', 'users.first_name', 'users.last_name', 'users.contact_number', 'users.email', 'property_units.unit_name', 'properties.name as property_name'])
            ->where('tenants.owner_user_id', auth()->id())
            ->get();

        $statusText = [
            TENANT_STATUS_ACTIVE => __('Active'), TENANT_STATUS_INACTIVE => __('Inactive'),
            TENANT_STATUS_DRAFT => __('Draft'), TENANT_STATUS_CLOSE => __('Close'),
        ];

        $data = $rows->values()->map(fn ($t, $i) => [
            $i + 1,
            trim($t->first_name . ' ' . $t->last_name),
            $t->email,
            $t->contact_number,
            $t->property_name,
            $t->unit_name,
            currencyPrice($t->paid),
            currencyPrice($t->due),
            $statusText[$t->status] ?? '—',
        ])->all();

        return [
            'title'   => __('Tenant Report'),
            'headers' => ['SL', __('Name'), __('Email'), __('Contact'), __('Property'), __('Unit'), __('Paid'), __('Due'), __('Status')],
            'rows'    => $data,
            'totals'  => null,
        ];
    }

    private function earning(): array
    {
        $f = $this->filters();

        $q = Invoice::query()
            ->join('properties', 'invoices.property_id', '=', 'properties.id')
            ->join('property_units', 'invoices.property_unit_id', '=', 'property_units.id')
            ->select('invoices.invoice_no', 'invoices.amount', 'invoices.tax_amount', 'invoices.created_at', 'invoices.month as invoice_month', 'properties.name as property_name', 'property_units.unit_name')
            ->where('invoices.owner_user_id', auth()->id())
            ->where('invoices.status', INVOICE_STATUS_PAID);

        if ($f['property_id']) { $q->where('invoices.property_id', $f['property_id']); }
        if ($f['unit_id']) { $q->where('invoices.property_unit_id', $f['unit_id']); }
        if ($f['start_date'] && $f['end_date']) {
            $q->whereBetween('invoices.created_at', [
                date('Y-m-d H:i:s', strtotime($f['start_date'])),
                date('Y-m-d H:i:s', strtotime($f['end_date'] . ' 23:59:59')),
            ]);
        }

        $rows = $q->get();

        $data = $rows->values()->map(fn ($inv, $i) => [
            $i + 1,
            $inv->invoice_no,
            $inv->property_name,
            $inv->unit_name,
            optional($inv->created_at)->format('Y-m-d'),
            $inv->invoice_month ?: 'N/A',
            currencyPrice($inv->tax_amount),
            currencyPrice($inv->amount),
        ])->all();

        return [
            'title'   => __('Earning Report'),
            'headers' => ['SL', __('Invoice'), __('Property'), __('Unit'), __('Date'), __('Month'), __('Tax'), __('Amount')],
            'rows'    => $data,
            'totals'  => ['', '', '', '', '', __('Total'), currencyPrice($rows->sum('tax_amount')), currencyPrice($rows->sum('amount'))],
        ];
    }

    private function expenses(): array
    {
        $f = $this->filters();

        $q = Expense::query()
            ->join('properties', 'expenses.property_id', '=', 'properties.id')
            ->join('property_units', 'expenses.property_unit_id', '=', 'property_units.id')
            ->where('expenses.owner_user_id', auth()->id())
            ->select('expenses.name', 'expenses.total_amount', 'expenses.created_at', 'properties.name as property_name', 'property_units.unit_name');

        if ($f['property_id']) { $q->where('expenses.property_id', $f['property_id']); }
        if ($f['unit_id']) { $q->where('expenses.property_unit_id', $f['unit_id']); }
        if ($f['start_date'] && $f['end_date']) {
            $q->whereBetween('expenses.created_at', [
                date('Y-m-d H:i:s', strtotime($f['start_date'])),
                date('Y-m-d H:i:s', strtotime($f['end_date'] . ' 23:59:59')),
            ]);
        }

        $rows = $q->get();

        $data = $rows->values()->map(fn ($e, $i) => [
            $i + 1,
            $e->name,
            $e->property_name,
            $e->unit_name,
            optional($e->created_at)->format('Y-m-d'),
            currencyPrice($e->total_amount),
        ])->all();

        return [
            'title'   => __('Expense Report'),
            'headers' => ['SL', __('Name'), __('Property'), __('Unit'), __('Date'), __('Amount')],
            'rows'    => $data,
            'totals'  => ['', '', '', '', __('Total'), currencyPrice($rows->sum('total_amount'))],
        ];
    }

    private function leases(): array
    {
        $rows = Tenant::query()
            ->join('users', 'tenants.user_id', '=', 'users.id')
            ->join('properties', 'tenants.property_id', '=', 'properties.id')
            ->join('property_units', 'tenants.unit_id', '=', 'property_units.id')
            ->where('tenants.owner_user_id', auth()->id())
            ->select('tenants.*', 'users.first_name', 'users.last_name', 'properties.name as property_name', 'property_units.unit_name')
            ->get();

        $data = $rows->values()->map(fn ($t, $i) => [
            $i + 1,
            trim($t->first_name . ' ' . $t->last_name),
            $t->property_name,
            $t->unit_name,
            $t->lease_start_date,
            $t->lease_end_date,
        ])->all();

        return [
            'title'   => __('Lease Report'),
            'headers' => ['SL', __('Name'), __('Property'), __('Unit'), __('Start Date'), __('End Date')],
            'rows'    => $data,
            'totals'  => null,
        ];
    }

    private function occupancy(): array
    {
        $rows = Property::query()
            ->leftJoin('tenants', ['properties.id' => 'tenants.property_id', 'tenants.status' => (DB::raw(TENANT_STATUS_ACTIVE))])
            ->selectRaw('properties.number_of_unit - (COUNT(tenants.id)) as available_unit,properties.*')
            ->groupBy('properties.id')
            ->where('properties.owner_user_id', auth()->id())
            ->get();

        $data = $rows->values()->map(function ($p, $i) {
            $turnOver = $p->number_of_unit ? round(($p->available_unit / $p->number_of_unit) * 100, 2) : 0;

            return [
                $i + 1,
                $p->name,
                optional($p->propertyDetail)->address,
                $p->number_of_unit,
                $p->available_unit,
                $turnOver . '%',
            ];
        })->all();

        return [
            'title'   => __('Occupancy Report'),
            'headers' => ['SL', __('Property'), __('Address'), __('Units'), __('Available'), __('Turn Over')],
            'rows'    => $data,
            'totals'  => null,
        ];
    }

    private function maintenance(): array
    {
        $rows = MaintenanceRequest::query()
            ->join('properties', 'maintenance_requests.property_id', '=', 'properties.id')
            ->join('property_units', 'maintenance_requests.unit_id', '=', 'property_units.id')
            ->join('tenants', 'maintenance_requests.unit_id', '=', 'tenants.unit_id')
            ->join('users', 'tenants.user_id', '=', 'users.id')
            ->join('maintenance_issues', 'maintenance_requests.issue_id', '=', 'maintenance_issues.id')
            ->where('maintenance_requests.owner_user_id', auth()->id())
            ->select('maintenance_requests.*', 'properties.name as property_name', 'maintenance_issues.name as issue_name', 'property_units.unit_name', 'users.first_name', 'users.last_name')
            ->get();

        $statusText = [
            MAINTENANCE_REQUEST_STATUS_COMPLETE => __('Completed'),
            MAINTENANCE_REQUEST_STATUS_INPROGRESS => __('In Progress'),
        ];

        $data = $rows->values()->map(fn ($m, $i) => [
            $i + 1,
            trim($m->first_name . ' ' . $m->last_name),
            $m->property_name,
            $m->unit_name,
            $m->issue_name,
            $statusText[$m->status] ?? __('Pending'),
        ])->all();

        return [
            'title'   => __('Maintenance Report'),
            'headers' => ['SL', __('Tenant'), __('Property'), __('Unit'), __('Issue'), __('Status')],
            'rows'    => $data,
            'totals'  => null,
        ];
    }
}
