@extends('tenant.layouts.app')

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="page-content-wrapper bg-white p-30 radius-20">
                <div class="container">

                    {{-- Page Header --}}
                    <div class="dash-header mb-4">
                        <div>
                            <h2 class="dash-title">{{ $pageTitle }}</h2>
                            <nav aria-label="breadcrumb">
                                <ol class="inv-breadcrumb">
                                    <li>
                                        <a href="{{ route('tenant.dashboard') }}">{{ __('Dashboard') }}</a>
                                    </li>
                                    <li aria-current="page">
                                        <svg width="8" height="8" viewBox="0 0 16 16" fill="none">
                                            <path d="M6 4l4 4-4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                        {{ $pageTitle }}
                                    </li>
                                </ol>
                            </nav>
                        </div>
                    </div>

                    {{-- Invoice Summary Strip --}}
                    @php
                        $paidCount    = $invoices->where('status', INVOICE_STATUS_PAID)->count();
                        $pendingCount = $invoices->where('status', INVOICE_STATUS_PENDING)->count();
                        $overdueCount = $invoices->filter(fn($i) => $i->status != INVOICE_STATUS_PAID && $i->due_date < date('Y-m-d'))->count();
                        $totalOwed    = $invoices->where('status', INVOICE_STATUS_PENDING)->sum('amount');
                    @endphp

                    <div class="inv-strip mb-4">

                        <div class="inv-strip__item">
                            <span class="inv-strip__dot inv-strip__dot--green"></span>
                            <div>
                                <p class="inv-strip__label">Paid</p>
                                <p class="inv-strip__val inv-strip__val--green">{{ $paidCount }}</p>
                            </div>
                        </div>

                        <div class="inv-strip__divider"></div>

                        <div class="inv-strip__item">
                            <span class="inv-strip__dot inv-strip__dot--amber"></span>
                            <div>
                                <p class="inv-strip__label">Pending</p>
                                <p class="inv-strip__val inv-strip__val--amber">{{ $pendingCount }}</p>
                            </div>
                        </div>

                        <div class="inv-strip__divider"></div>

                        <div class="inv-strip__item">
                            <span class="inv-strip__dot inv-strip__dot--coral"></span>
                            <div>
                                <p class="inv-strip__label">Overdue</p>
                                <p class="inv-strip__val inv-strip__val--coral">{{ $overdueCount }}</p>
                            </div>
                        </div>

                        @if ($totalOwed > 0)
                            <div class="inv-strip__divider inv-strip__divider--hide-sm"></div>
                            <div class="inv-strip__item inv-strip__item--hide-sm">
                                <div>
                                    <p class="inv-strip__label">Outstanding</p>
                                    <p class="inv-strip__val inv-strip__val--coral">{{ currencyPrice($totalOwed) }}</p>
                                </div>
                            </div>
                        @endif

                    </div>

                    {{-- Table Card --}}
                    <div class="dash-card">
                        <div class="dash-card__head d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <div class="d-flex align-items-center gap-2">
                                <span style="font-weight:500;font-size:14px;">All Invoices</span>
                                <span class="inv-count-pill">{{ $invoices->count() }} total</span>
                            </div>
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                {{-- Status filter tabs --}}
                                <div class="inv-filter-tabs" id="statusFilter">
                                    <button class="inv-filter-tab inv-filter-tab--active" data-filter="all">All</button>
                                    <button class="inv-filter-tab" data-filter="paid">
                                        <span class="inv-filter-tab__dot inv-filter-tab__dot--paid"></span>Paid
                                    </button>
                                    <button class="inv-filter-tab" data-filter="unpaid">
                                        <span class="inv-filter-tab__dot inv-filter-tab__dot--unpaid"></span>Unpaid
                                    </button>
                                </div>
                                {{-- Search --}}
                                <div class="inv-search-wrap">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none">
                                        <circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="1.8"/>
                                        <path d="M16.5 16.5l4 4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                    </svg>
                                    <input type="text" id="invoiceSearch" placeholder="{{ __('Search invoices…') }}">
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table id="allDataTable" class="table inv-table dt-responsive align-middle mb-0">
                                <thead>
                                    <tr class="inv-table__head">
                                        <th class="inv-th" style="width:44px;">#</th>
                                        <th class="inv-th all">{{ __('Invoice No.') }}</th>
                                        <th class="inv-th all">{{ __('Name') }}</th>
                                        <th class="inv-th desktop">{{ __('Month') }}</th>
                                        <th class="inv-th desktop">{{ __('Due Date') }}</th>
                                        <th class="inv-th desktop">{{ __('Amount') }}</th>
                                        <th class="inv-th all">{{ __('Status') }}</th>
                                        <th class="inv-th desktop" style="text-align:right;">{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($invoices as $invoice)
                                        @php
                                            $isPaid    = $invoice->status == INVOICE_STATUS_PAID;
                                            $isPending = $invoice->status == INVOICE_STATUS_PENDING;
                                            $isOverdue = !$isPaid && $invoice->due_date < date('Y-m-d');
                                        @endphp
                                        <tr class="inv-table__row {{ $isOverdue ? 'inv-table__row--overdue' : '' }}">

                                            {{-- SL --}}
                                            <td class="inv-td" style="color:#9ca3af;font-size:11px;">
                                                {{ $loop->iteration }}
                                            </td>

                                            {{-- Invoice No --}}
                                            <td class="inv-td">
                                                <span class="inv-no">{{ $invoice->invoice_no }}</span>
                                            </td>

                                            {{-- Name --}}
                                            <td class="inv-td">
                                                <span style="font-size:13px;font-weight:500;color:#111827;">
                                                    {{ $invoice->name }}
                                                </span>
                                                @if($invoice->item_types_label)
                                                    <span class="inv-item-types">{{ $invoice->item_types_label }}</span>
                                                @endif
                                            </td>

                                            {{-- Month --}}
                                            <td class="inv-td desktop">
                                                <span class="inv-secondary-text">
                                                    {{ $invoice->month }} {{ $invoice->created_at->format('Y') }}
                                                </span>
                                            </td>

                                            {{-- Due Date --}}
                                            <td class="inv-td desktop">
                                                <span class="{{ $isOverdue ? 'inv-due--overdue' : 'inv-due' }}">
                                                    {{ \Carbon\Carbon::parse($invoice->due_date)->format('d M Y') }}
                                                </span>
                                            </td>

                                            {{-- Amount --}}
                                            <td class="inv-td desktop">
                                                <span class="inv-amount {{ $isPaid ? 'inv-amount--paid' : ($isOverdue ? 'inv-amount--overdue' : 'inv-amount--pending') }}">
                                                    {{ currencyPrice($invoice->amount) }}
                                                </span>
                                            </td>

                                            {{-- Status --}}
                                            <td class="inv-td all">
                                                @if ($isPaid)
                                                <span class="inv-badge inv-badge--paid">
                                                    <svg 
                                                        width="10" 
                                                        height="10" 
                                                        viewBox="0 0 16 16" 
                                                        fill="none"
                                                    >
                                                        <path 
                                                        d="M3 8l4 4 6-6" 
                                                        stroke="currentColor" 
                                                        stroke-width="2" 
                                                        stroke-linecap="round" 
                                                        stroke-linejoin="round"
                                                        />
                                                    </svg>
                                                    <span class="inv-badge__text">
                                                        Paid . {{ $invoice->paid_date_label }}
                                                    </span>
                                                </span>

                                                @elseif ($isOverdue)
                                                    <span class="inv-badge inv-badge--overdue">
                                                        <svg width="10" height="10" viewBox="0 0 16 16" fill="none">
                                                            <path d="M8 5v4M8 11v.5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                                        </svg>
                                                        Overdue
                                                    </span>
                                                @else
                                                    <span class="inv-badge inv-badge--pending">
                                                        <svg width="10" height="10" viewBox="0 0 16 16" fill="none">
                                                            <circle cx="8" cy="8" r="5.5" stroke="currentColor" stroke-width="1.6"/>
                                                            <path d="M8 5v3.5l2 1.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                                                        </svg>
                                                        Pending
                                                    </span>
                                                @endif
                                            </td>

                                            {{-- Actions --}}
                                            <td class="inv-td desktop">
                                                <div class="inv-actions">

                                                    {{-- Print --}}
                                                    <a href="{{ route('tenant.invoice.print', $invoice->id) }}"
                                                       target="_blank"
                                                       class="inv-btn inv-btn--ghost"
                                                       title="{{ __('Print Invoice') }}">
                                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none">
                                                            <path d="M6 9V3h12v6M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                                                            <rect x="6" y="14" width="12" height="7" rx="1" stroke="currentColor" stroke-width="1.8"/>
                                                        </svg>
                                                        Print
                                                    </a>

                                                    {{-- Pay Now --}}
                                                    @if (!$isPaid)
                                                        <a href="{{ route('tenant.invoice.pay', $invoice->id) }}"
                                                           class="inv-btn inv-btn--pay {{ $isOverdue ? 'inv-btn--pay-overdue' : '' }}"
                                                           title="{{ __('Pay Now') }}">
                                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none">
                                                                <rect x="2" y="6" width="20" height="14" rx="2" stroke="currentColor" stroke-width="1.8"/>
                                                                <path d="M2 10h20" stroke="currentColor" stroke-width="1.8"/>
                                                                <path d="M6 15h4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                                            </svg>
                                                            Pay Now
                                                        </a>
                                                    @else
                                                        <span class="inv-badge inv-badge--paid">
                                                            <svg width="10" height="10" viewBox="0 0 16 16" fill="none">
                                                                <path d="M3 8l4 4 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                            </svg>
                                                            Paid
                                                        </span>
                                                    @endif

                                                </div>
                                            </td>

                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="inv-empty">
                                                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" style="color:#d1d5db">
                                                    <rect x="3" y="4" width="18" height="17" rx="2" stroke="currentColor" stroke-width="1.5"/>
                                                    <path d="M8 2v4M16 2v4M3 10h18" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                                </svg>
                                                <p style="margin:8px 0 4px;font-weight:500;color:#374151;font-size:14px;">No invoices yet</p>
                                                <p style="font-size:13px;color:#9ca3af;">Your invoices will appear here once they are issued.</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    {{-- End Table Card --}}

                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* ── Page header ─────────────────────────────────────────── */
    .dash-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 1rem;
    }
    .dash-title { font-size: 22px; font-weight: 500; color: #111827; margin: 0 0 6px; }

    /* ── Breadcrumb ──────────────────────────────────────────── */
    .inv-breadcrumb {
        list-style: none;
        display: flex;
        align-items: center;
        gap: 6px;
        margin: 0;
        padding: 0;
        font-size: 12px;
        color: #9ca3af;
    }
    .inv-breadcrumb a { color: #185FA5; text-decoration: none; font-weight: 500; }
    .inv-breadcrumb a:hover { color: #0F4A84; }
    .inv-breadcrumb li { display: flex; align-items: center; gap: 6px; }

    /* ── Summary strip ───────────────────────────────────────── */
    .inv-strip {
        display: flex;
        align-items: center;
        background: #fff;
        border: 0.5px solid #e5e7eb;
        border-radius: 12px;
        overflow: hidden;
        flex-wrap: wrap;
    }
    .inv-strip__item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: .9rem 1.35rem;
        flex: 1;
        min-width: 100px;
        max-width: 220px;
    }
    .inv-strip__item--hide-sm { display: flex; }
    .inv-strip__divider {
        width: 0.5px;
        align-self: stretch;
        background: #e5e7eb;
        flex-shrink: 0;
    }
    .inv-strip__divider--hide-sm { display: block; }

    .inv-strip__dot {
        width: 8px; height: 8px;
        border-radius: 50%;
        flex-shrink: 0;
    }
    .inv-strip__dot--green { background: #1D9E75; }
    .inv-strip__dot--amber { background: #854F0B; }
    .inv-strip__dot--coral { background: #993C1D; }

    .inv-strip__label {
        font-size: 10px;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: .07em;
        color: #9ca3af;
        margin: 0 0 3px;
    }
    .inv-strip__val {
        font-size: 18px;
        font-weight: 500;
        margin: 0;
        line-height: 1;
    }
    .inv-strip__val--green { color: #0F6E56; }
    .inv-strip__val--amber { color: #854F0B; }
    .inv-strip__val--coral { color: #993C1D; }

    /* ── Shared dash card ────────────────────────────────────── */
    .dash-card {
        background: #fff;
        border: 0.5px solid #e5e7eb;
        border-radius: 12px;
        overflow: hidden;
    }
    .dash-card__head {
        padding: .75rem 1.1rem;
        border-bottom: 0.5px solid #e5e7eb;
        background: #fafafa;
    }

    .inv-count-pill {
        display: inline-block;
        background: #f3f4f6;
        color: #6b7280;
        font-size: 11px;
        font-weight: 500;
        padding: 3px 10px;
        border-radius: 99px;
    }

    /* ── Inline search ───────────────────────────────────────── */
    .inv-search-wrap {
        position: relative;
        display: flex;
        align-items: center;
    }
    .inv-search-wrap svg {
        position: absolute;
        left: 8px;
        color: #9ca3af;
        pointer-events: none;
    }
    .inv-search-wrap input {
        border: 0.5px solid #e5e7eb;
        border-radius: 7px;
        padding: 5px 10px 5px 28px;
        font-size: 12px;
        color: #374151;
        background: #fff;
        outline: none;
        width: 180px;
        transition: border-color .15s, box-shadow .15s;
    }
    .inv-search-wrap input:focus {
        border-color: #185FA5;
        box-shadow: 0 0 0 3px rgba(24,95,165,.1);
    }
    .inv-search-wrap input::placeholder { color: #c4c4c4; }

    @media (max-width: 540px) {
        .dash-card__head { flex-wrap: wrap; gap: 8px; }
        .inv-search-wrap { width: 100%; }
        .inv-search-wrap input { width: 100%; }
    }

    /* ── Table ───────────────────────────────────────────────── */
    .inv-table { width: 100%; border-collapse: collapse; }

    .inv-table__head { background: #fafafa; border-bottom: 0.5px solid #e5e7eb; }
    .inv-th {
        padding: .65rem 1rem;
        font-size: 10px;
        font-weight: 500;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: .07em;
        border: none;
        white-space: nowrap;
    }
    .inv-td {
        padding: .8rem 1rem;
        border: none;
        vertical-align: middle;
    }

    .inv-table__row { border-bottom: 0.5px solid #f3f4f6; transition: background .12s; }
    .inv-table__row:last-child { border-bottom: none; }

    /* Zebra stripe — even rows only, skip overdue rows */
    .inv-table__row:nth-child(even):not(.inv-table__row--overdue) { background: #fafafa; }
    .inv-table__row:hover:not(.inv-table__row--overdue) { background: #f3f4f6; }

    .inv-table__row--overdue { background: #FDF3F0; }
    .inv-table__row--overdue:hover { background: #FAECE7; }

    /* ── Invoice number chip ─────────────────────────────────── */
    .inv-no {
        display: inline-block;
        font-size: 11px;
        font-weight: 500;
        font-family: monospace;
        letter-spacing: .04em;
        background: #E6F1FB;
        color: #0C447C;
        padding: 3px 9px;
        border-radius: 6px;
    }

    /* ── Secondary text (month, etc.) ───────────────────────── */
    .inv-secondary-text {
        font-size: 12px;
        color: #6b7280;
    }

    /* ── Due date ────────────────────────────────────────────── */
    .inv-due         { font-size: 12px; color: #6b7280; }
    .inv-due--overdue { font-size: 12px; color: #993C1D; font-weight: 500; }

    /* ── Amount ──────────────────────────────────────────────── */
    .inv-amount {
        font-size: 13px;
        font-weight: 500;
        padding: 3px 10px;
        border-radius: 99px;
        white-space: nowrap;
        display: inline-block;
    }
    .inv-amount--paid    { background: #E1F5EE; color: #0F6E56; }
    .inv-amount--pending { background: #FAEEDA; color: #854F0B; }
    .inv-amount--overdue { background: #FAECE7; color: #993C1D; }

    /* ── Status badges ───────────────────────────────────────── */
    .inv-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 11px;
        font-weight: 500;
        padding: 3px 9px;
        border-radius: 99px;
        white-space: nowrap;
    }
    .inv-badge--paid    { background: #E1F5EE; color: #0F6E56; }
    .inv-badge--pending { background: #FAEEDA; color: #854F0B; border: 0.5px solid #F5D9A8; }
    .inv-badge--overdue { background: #FAECE7; color: #993C1D; border: 0.5px solid #F5C4B3; }

    /* ── Action buttons ──────────────────────────────────────── */
    .inv-actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 8px;
        flex-wrap: nowrap;
    }

    .inv-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        font-size: 12px;
        font-weight: 500;
        padding: 5px 12px;
        border-radius: 7px;
        text-decoration: none;
        white-space: nowrap;
        transition: background .15s, transform .12s, box-shadow .12s;
        cursor: pointer;
        border: none;
    }

    /* Ghost — Print */
    .inv-btn--ghost {
        background: #f3f4f6;
        color: #374151;
        border: 0.5px solid #e5e7eb;
    }
    .inv-btn--ghost:hover {
        background: #e5e7eb;
        color: #111827;
        text-decoration: none;
    }

    /* Pay Now — strong CTA */
    .inv-btn--pay {
        background: #185FA5;
        color: #fff;
        min-width: 84px;
        box-shadow: 0 2px 8px rgba(24,95,165,.2);
    }
    .inv-btn--pay:hover {
        background: #0F4A84;
        color: #fff;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(24,95,165,.3);
        text-decoration: none;
    }

    /* Pay Now — overdue variant (coral) */
    .inv-btn--pay-overdue {
        background: #993C1D;
        box-shadow: 0 2px 8px rgba(153,60,29,.2);
    }
    .inv-btn--pay-overdue:hover {
        background: #7A2C10;
        box-shadow: 0 4px 12px rgba(153,60,29,.3);
    }

    /* Paid label */
    .inv-paid-label {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-size: 12px;
        font-weight: 500;
        color: #0F6E56;
        min-width: 84px;
        justify-content: flex-end;
    }

    /* ── Invoice item types label ───────────────────────────── */
    .inv-item-types {
        display: block;
        font-size: 11px;
        color: #9ca3af;
        margin-top: 2px;
        font-weight: 400;
    }
    /* ── Status filter tabs ─────────────────────────────────── */
    .inv-filter-tabs {
        display: flex;
        background: #f3f4f6;
        border-radius: 8px;
        padding: 3px;
        gap: 2px;
    }
    .inv-filter-tab {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        background: transparent;
        border: none;
        font-size: 12px;
        font-weight: 500;
        color: #6b7280;
        padding: 4px 12px;
        border-radius: 6px;
        cursor: pointer;
        transition: background .15s, color .15s;
        white-space: nowrap;
    }
    .inv-filter-tab--active {
        background: #fff;
        color: #111827;
        box-shadow: 0 1px 3px rgba(0,0,0,.08);
    }
    .inv-filter-tab__dot {
        width: 6px; height: 6px;
        border-radius: 50%;
        flex-shrink: 0;
    }
    .inv-filter-tab__dot--paid   { background: #1D9E75; }
    .inv-filter-tab__dot--unpaid { background: #854F0B; }

    /* ── Empty state ─────────────────────────────────────────── */
    .inv-empty {
        text-align: center;
        padding: 3rem 1rem;
        color: #9ca3af;
    }

    /* ── DataTables overrides ────────────────────────────────── */
    div.dataTables_wrapper { padding: 0; }

    /* Hide the default DT search — we use our own */
    div.dataTables_wrapper div.dataTables_filter { display: none; }

    div.dataTables_wrapper div.dataTables_length {
        padding: .75rem 1.25rem;
    }
    div.dataTables_wrapper div.dataTables_length select {
        border: 0.5px solid #e5e7eb;
        border-radius: 7px;
        padding: 5px 10px;
        font-size: 13px;
        color: #374151;
        outline: none;
        background: #fff;
        transition: border-color .15s, box-shadow .15s;
    }
    div.dataTables_wrapper div.dataTables_length select:focus {
        border-color: #185FA5;
        box-shadow: 0 0 0 3px rgba(24,95,165,.1);
    }

    /* Pagination */
    div.dataTables_wrapper div.dataTables_paginate { padding: .75rem 1.25rem; }
    div.dataTables_wrapper div.dataTables_paginate .paginate_button {
        border-radius: 7px !important;
        border: 0.5px solid transparent !important;
        font-size: 13px !important;
        padding: 4px 10px !important;
        color: #374151 !important;
        transition: background .12s !important;
    }
    div.dataTables_wrapper div.dataTables_paginate .paginate_button:hover {
        background: #f3f4f6 !important;
        color: #111827 !important;
        border-color: #e5e7eb !important;
    }
    div.dataTables_wrapper div.dataTables_paginate .paginate_button.current,
    div.dataTables_wrapper div.dataTables_paginate .paginate_button.current:hover {
        background: #185FA5 !important;
        border-color: #185FA5 !important;
        color: #fff !important;
    }

    /* Info text */
    div.dataTables_wrapper div.dataTables_info {
        padding: .75rem 1.25rem;
        font-size: 12px;
        color: #9ca3af;
    }

    /* Sort icons */
    table.dataTable thead th.sorting::before,
    table.dataTable thead th.sorting::after,
    table.dataTable thead th.sorting_asc::after,
    table.dataTable thead th.sorting_desc::after {
        opacity: .35;
    }

    /* ── Responsive tweaks ───────────────────────────────────── */
    @media (max-width: 768px) {
        .inv-strip__divider--hide-sm   { display: none; }
        .inv-strip__item               { padding: .75rem 1rem; }
        .inv-strip__val                { font-size: 16px; }
        .inv-search-wrap input         { width: 130px; }
 
        /* Outstanding amount: show full-width below the three stats */
        .inv-strip__item--hide-sm {
            display: flex;
            flex-basis: 100%;
            max-width: 100%;
            border-top: 0.5px solid #e5e7eb;
        }
    }
</style>

@endsection

@push('style')
    @include('common.layouts.datatable-style')
@endpush

@push('script')
    @include('common.layouts.datatable-script')
    <script src="{{ asset('/') }}assets/js/pages/alldatatables.init.js"></script>
@endpush