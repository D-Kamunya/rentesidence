@extends('owner.layouts.app')

@section('content')
<style>
    /* ── Tokens ─────────────────────────────────────────── */
    :root {
        --blue:          #185FA5;
        --blue-hover:    #0F4A84;
        --blue-light:    #E6F1FB;
        --blue-border:   #B5D4F4;
        --blue-faint:    #185ea56e;
        --blue-ghost:    #185ea51c;
        --green:         #1D9E75;
        --green-dark:    #0F6E56;
        --green-light:   #E1F5EE;
        --amber:         #854F0B;
        --amber-light:   #FAEEDA;
        --amber-border:  #F5D9A8;
        --red:           #993C1D;
        --red-light:     #FAECE7;
        --purple:        #534AB7;
        --purple-hover:  #3C3489;
        --gray-900:      #111827;
        --gray-800:      #1f2937;
        --gray-700:      #374151;
        --gray-500:      #6b7280;
        --gray-400:      #9ca3af;
        --gray-200:      #e5e7eb;
        --gray-100:      #f3f4f6;
        --gray-50:       #fafafa;
        --white:         #ffffff;
    }

    /* ── Page wrapper ───────────────────────────────────── */
    .th-page-wrapper {
        background: var(--white);
        border-radius: 12px;
        border: 0.5px solid var(--gray-200);
        box-shadow:
            0 4px 12px rgba(0,0,0,0.04),
            0 0 0 1px rgba(24,95,165,0.05),
            0 6px 18px rgba(24,95,165,0.06);
        overflow: hidden;
    }

    /* ── Page header ────────────────────────────────────── */
    .th-page-header {
        padding: 20px 24px 16px;
        border-bottom: 0.5px solid var(--gray-200);
        background: var(--gray-50);
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
    }
    .th-page-title {
        font-size: 22px;
        font-weight: 500;
        color: var(--gray-900);
        margin: 0;
        line-height: 1.2;
    }

    /* ── Breadcrumb ─────────────────────────────────────── */
    .th-breadcrumb {
        display: flex;
        align-items: center;
        gap: 6px;
        list-style: none;
        margin: 4px 0 0;
        padding: 0;
        font-size: 12px;
        color: var(--gray-400);
    }
    .th-breadcrumb li { display: flex; align-items: center; gap: 6px; }
    .th-breadcrumb a {
        color: var(--blue);
        font-weight: 500;
        text-decoration: none;
        transition: color .13s;
    }
    .th-breadcrumb a:hover { color: var(--blue-hover); }
    .th-breadcrumb-sep { display: inline-flex; color: var(--gray-400); }
    .th-breadcrumb-sep svg { width: 8px; height: 8px; }

    /* ── Table card body ────────────────────────────────── */
    .th-table-body { padding: 20px; }

    /* ── DataTable toolbar ──────────────────────────────── */

    /* Cancel Bootstrap's negative row margins that cause edge-hugging */
    #allClosingTenantDataTable_wrapper > .row:first-child {
        margin-left: 0 !important;
        margin-right: 0 !important;
        display: flex !important;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 0 0 16px;
        border-bottom: 0.5px solid var(--gray-100);
        margin-bottom: 16px !important;
    }

    /* Cancel Bootstrap col padding that adds extra inset */
    #allClosingTenantDataTable_wrapper > .row:first-child > [class*="col-"] {
        padding-left: 0 !important;
        padding-right: 0 !important;
        width: auto !important;
        flex: 0 1 auto !important;
        max-width: 100% !important;
    }

    /* Search col grows to fill remaining space */
    #allClosingTenantDataTable_wrapper > .row:first-child > [class*="col-"]:last-child {
        flex: 1 1 auto !important;
        display: flex;
        justify-content: flex-end;
    }

    /* Labels — inline with their control */
    #allClosingTenantDataTable_wrapper .dataTables_length label,
    #allClosingTenantDataTable_wrapper .dataTables_filter label {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 12px;
        font-weight: 500;
        color: var(--gray-500);
        margin: 0;
        white-space: nowrap;
    }

    /* Search label takes full width of its flex-end container */
    #allClosingTenantDataTable_wrapper .dataTables_filter {
        display: flex;
        justify-content: flex-end;
        width: 100%;
    }

    /* Inputs & selects */
    #allClosingTenantDataTable_wrapper .dataTables_length select,
    #allClosingTenantDataTable_wrapper .dataTables_filter input {
        border: 0.5px solid var(--gray-200);
        border-radius: 7px;
        padding: 7px 10px;
        font-size: 13px;
        color: var(--gray-700);
        background: var(--white);
        outline: none;
        transition: border-color .15s, box-shadow .15s;
    }
    #allClosingTenantDataTable_wrapper .dataTables_length select {
        width: 72px !important;
    }
    #allClosingTenantDataTable_wrapper .dataTables_filter input {
        width: 260px !important;
    }
    #allClosingTenantDataTable_wrapper .dataTables_length select:focus,
    #allClosingTenantDataTable_wrapper .dataTables_filter input:focus {
        border-color: var(--blue);
        box-shadow: 0 0 0 3px rgba(24,95,165,.1);
    }

    /* ── Table ──────────────────────────────────────────── */
    #allClosingTenantDataTable {
        border: none !important;
        border-collapse: collapse;
        width: 100% !important;
    }
    #allClosingTenantDataTable thead tr {
        background: var(--gray-50);
        border-bottom: 0.5px solid var(--gray-200);
    }
    #allClosingTenantDataTable thead th {
        font-size: 10px !important;
        font-weight: 500 !important;
        text-transform: uppercase !important;
        letter-spacing: .07em !important;
        color: var(--gray-500) !important;
        padding: .65rem 1rem !important;
        border-bottom: 0.5px solid var(--gray-200) !important;
        white-space: nowrap;
        background: var(--gray-50) !important;
    }
    #allClosingTenantDataTable tbody tr {
        border-bottom: 0.5px solid var(--gray-100);
        transition: background .15s;
    }
    #allClosingTenantDataTable tbody tr:hover        { background: var(--gray-100) !important; }
    #allClosingTenantDataTable tbody tr:nth-child(even)       { background: var(--gray-50); }
    #allClosingTenantDataTable tbody tr:nth-child(even):hover { background: var(--gray-100) !important; }
    #allClosingTenantDataTable tbody td {
        padding: .8rem 1rem !important;
        font-size: 13px !important;
        color: var(--gray-700) !important;
        border-bottom: none !important;
        vertical-align: middle;
    }
    #allClosingTenantDataTable thead .sorting:after,
    #allClosingTenantDataTable thead .sorting_asc:after,
    #allClosingTenantDataTable thead .sorting_desc:after { opacity: .4; }

    /* ── Pagination ─────────────────────────────────────── */
    #allClosingTenantDataTable_wrapper .dataTables_paginate { padding: 12px 0 4px; }
    #allClosingTenantDataTable_wrapper .paginate_button {
        min-width: 32px;
        height: 32px;
        border-radius: 7px !important;
        font-size: 12px !important;
        font-weight: 500 !important;
        padding: 0 10px !important;
        border: 0.5px solid var(--gray-200) !important;
        color: var(--gray-700) !important;
        margin: 0 2px !important;
        background: var(--white) !important;
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
        transition: all .13s;
    }
    #allClosingTenantDataTable_wrapper .paginate_button:hover:not(.disabled):not(.current) {
        background: var(--blue-light) !important;
        border-color: var(--blue-border) !important;
        color: var(--blue) !important;
        transform: translateY(-1px);
    }
    #allClosingTenantDataTable_wrapper .paginate_button.current {
        background: var(--blue) !important;
        border-color: var(--blue) !important;
        color: var(--white) !important;
    }
    #allClosingTenantDataTable_wrapper .paginate_button.disabled {
        color: #d1d5db !important;
        background: var(--gray-50) !important;
        cursor: not-allowed;
    }

    /* ── Footer row (info + pagination) ────────────────── */
    #allClosingTenantDataTable_wrapper .dataTables_info {
        font-size: 12px;
        color: var(--gray-500);
        padding-top: 14px;
    }
    #allClosingTenantDataTable_wrapper .row:last-child {
        margin-left: 0 !important;
        margin-right: 0 !important;
        padding-left: 0 !important;
        padding-right: 0 !important;
        border-top: 0.5px solid var(--gray-200);
        background: var(--gray-50);
        margin: 16px -20px -20px !important;
        padding: 0 20px !important;
        border-radius: 0 0 12px 12px;
    }
    #allClosingTenantDataTable_wrapper .row:last-child > [class*="col-"] {
        padding-left: 0 !important;
        padding-right: 0 !important;
    }

    /* ── Responsive ─────────────────────────────────────── */
    @media (max-width: 768px) {
        .th-page-header { padding: 16px; }
        .th-page-title  { font-size: 18px; }
        .th-table-body  { padding: 14px; }

        #allClosingTenantDataTable_wrapper .dataTables_filter input {
            width: 180px !important;
        }
    }

    @media (max-width: 540px) {
        .th-page-header { flex-direction: column; align-items: flex-start; }

        /* Stack toolbar vertically, full width */
        #allClosingTenantDataTable_wrapper > .row:first-child {
            flex-direction: column;
            align-items: stretch;
            gap: 10px;
        }
        #allClosingTenantDataTable_wrapper > .row:first-child > [class*="col-"] {
            width: 100% !important;
            flex: 1 1 100% !important;
        }
        #allClosingTenantDataTable_wrapper .dataTables_filter {
            justify-content: flex-start;
        }
        #allClosingTenantDataTable_wrapper .dataTables_filter label,
        #allClosingTenantDataTable_wrapper .dataTables_length label {
            width: 100%;
        }
        #allClosingTenantDataTable_wrapper .dataTables_filter input {
            flex: 1;
            width: 100% !important;
            min-width: 0;
            max-width: none;
        }
        #allClosingTenantDataTable_wrapper .dataTables_length select {
            width: 80px !important;
        }
    }
</style>

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">

            <div class="th-page-wrapper">

                <!-- Page Header -->
                <div class="th-page-header">
                    <div>
                        <h3 class="th-page-title">{{ $pageTitle }}</h3>
                        <ol class="th-breadcrumb">
                            <li>
                                <a href="{{ route('owner.dashboard') }}" title="{{ __('Dashboard') }}">{{ __('Dashboard') }}</a>
                            </li>
                            <li>
                                <span class="th-breadcrumb-sep">
                                    <svg viewBox="0 0 8 8" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M2.5 1.5L5.5 4L2.5 6.5" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </span>
                                <a href="{{ route('owner.tenant.index') }}" title="{{ __('Tenants') }}">{{ __('Tenants') }}</a>
                            </li>
                            <li>
                                <span class="th-breadcrumb-sep">
                                    <svg viewBox="0 0 8 8" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M2.5 1.5L5.5 4L2.5 6.5" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </span>
                                <span>{{ $pageTitle }}</span>
                            </li>
                        </ol>
                    </div>
                </div>
                <!-- /Page Header -->

                <!-- Table Body -->
                <div class="th-table-body">
                    <table id="allClosingTenantDataTable" class="table responsive theme-border p-20">
                        <thead>
                            <tr>
                                <th>{{ __('SL') }}</th>
                                <th data-priority="1">{{ __('Name') }}</th>
                                <th class="d-none"></th>
                                <th>{{ __('Property') }}</th>
                                <th>{{ __('Unit') }}</th>
                                <th>{{ __('General Rent') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Action') }}</th>
                            </tr>
                        </thead>
                    </table>
                </div>
                <!-- /Table Body -->

            </div>

        </div>
    </div>
</div>

<input type="hidden" id="route" value="{{ route('owner.tenant.index', ['type' => 'history']) }}">
@endsection

@push('style')
    @include('common.layouts.datatable-style')
@endpush

@push('script')
    @include('common.layouts.datatable-script')
    <script src="{{ asset('assets/js/custom/tenant-history.js') }}"></script>
@endpush