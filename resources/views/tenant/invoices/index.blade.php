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
                        <div class="inv-header-actions">
                            @if (!empty($activeNotice))
                                @php $vnAck = $activeNotice->status === \App\Models\VacationNotice::STATUS_ACKNOWLEDGED; @endphp
                                <span class="inv-notice-pill {{ $vnAck ? 'inv-notice-pill--ack' : '' }}"
                                      title="{{ $vnAck ? __('Your landlord has acknowledged your notice to vacate') : __('You have given notice to vacate') }}">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none"><path d="M9 12l2 2 4-4M12 3l7 4v5c0 4-3 7-7 8-4-1-7-4-7-8V7l7-4z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    {{ $vnAck ? __('Vacation notice acknowledged') : __('Vacation notice given') }} · {{ \Carbon\Carbon::parse($activeNotice->intended_move_out_date)->format('d M Y') }}
                                </span>
                            @elseif (!empty($canGiveNotice))
                                <button type="button" class="inv-btn inv-btn--ghost" data-bs-toggle="modal" data-bs-target="#vacateModal">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4M16 17l5-5-5-5M21 12H9" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    {{ __('Give notice to vacate') }}
                                </button>
                            @endif
                            <button type="button" class="inv-btn inv-btn--pay" data-bs-toggle="modal" data-bs-target="#payUpcomingModal">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none">
                                    <rect x="3" y="5" width="18" height="16" rx="2" stroke="currentColor" stroke-width="1.8"/>
                                    <path d="M3 9h18M8 3v4M16 3v4M12 13v4M10 15h4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                </svg>
                                {{ __('Pay Upcoming Rent') }}
                            </button>
                        </div>
                    </div>

                    {{-- ── Notice-to-vacate modal ── --}}
                    @if (!empty($canGiveNotice) && empty($activeNotice))
                        <div class="modal fade" id="vacateModal" tabindex="-1" aria-labelledby="vacateModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content" style="border:none;border-radius:14px;overflow:hidden;">
                                    <div class="modal-header" style="border-bottom:0.5px solid #e5e7eb;padding:18px 22px;">
                                        <div>
                                            <p style="font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:.08em;color:#185FA5;margin:0 0 3px;">{{ __('Move out') }}</p>
                                            <h4 id="vacateModalLabel" style="font-size:16px;font-weight:600;color:#111827;margin:0;">{{ __('Give notice to vacate') }}</h4>
                                        </div>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                                    </div>
                                    <div class="modal-body" style="padding:20px 22px;">
                                        <p class="vac-lead">
                                            {{ __('Your tenancy requires') }} <strong>{{ $noticeDays }} {{ __('days') }}</strong> {{ __('notice.') }}
                                            {{ __('The earliest move-out date is') }}
                                            <strong>{{ \Carbon\Carbon::parse($noticeEarliest)->format('d M Y') }}</strong>.
                                        </p>
                                        <label class="vac-label">{{ __('Intended move-out date') }}</label>
                                        <input type="date" id="vacateDate" class="vac-input"
                                               min="{{ \Carbon\Carbon::today()->toDateString() }}"
                                               value="{{ $noticeEarliest }}"
                                               data-earliest="{{ $noticeEarliest }}">
                                        <div id="vacateWarn" class="vac-warn" style="display:none;">
                                            {{ __('This is earlier than the required') }} {{ $noticeDays }} {{ __('days\' notice — your landlord may need to approve it, or charge rent through the notice period.') }}
                                        </div>
                                        <label class="vac-label" style="margin-top:14px;">{{ __('Message to your landlord (optional)') }}</label>
                                        <textarea id="vacateMsg" class="vac-input" rows="3" maxlength="1000" placeholder="{{ __('e.g. reason for moving, preferred inspection time…') }}"></textarea>
                                    </div>
                                    <div class="modal-footer" style="border-top:0.5px solid #e5e7eb;padding:14px 22px;gap:8px;">
                                        <button type="button" class="inv-btn inv-btn--ghost" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                                        <button type="button" class="inv-btn inv-btn--pay" id="vacateSubmit">{{ __('Send notice') }}</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Deposit settlement — confirm receipt / dispute --}}
                    @if (!empty($depositSettlement))
                        @php $ds = $depositSettlement; @endphp
                        <div class="inv-settle mb-4 {{ $ds->status === 'disputed' ? 'inv-settle--disputed' : ($ds->status === 'confirmed' ? 'inv-settle--confirmed' : '') }}">
                            <div class="inv-settle__head">
                                <span class="inv-settle__title">{{ __('Deposit settlement') }}</span>
                                @if ($ds->status === 'confirmed')
                                    <span class="inv-settle__badge inv-settle__badge--ok">{{ __('You confirmed receipt') }}</span>
                                @elseif ($ds->status === 'disputed')
                                    <span class="inv-settle__badge inv-settle__badge--warn">{{ $ds->owner_responded_at ? __('Landlord responded') : __('Issue reported') }}</span>
                                @endif
                            </div>
                            <div class="inv-settle__rows">
                                <div><span>{{ __('Deposit held') }}</span><span>{{ currencyPrice($ds->deposit_held) }}</span></div>
                                @if ((float) $ds->total_deductions > 0)
                                    <div><span>{{ __('Deductions') }}</span><span>− {{ currencyPrice($ds->total_deductions) }}</span></div>
                                    @foreach ($ds->items as $it)
                                        <div class="inv-settle__ded"><span>· {{ $it->description }}</span><span>{{ currencyPrice($it->amount) }}</span></div>
                                    @endforeach
                                @endif
                                <div class="inv-settle__refund"><span>{{ __('Refunded to you') }}</span><strong>{{ currencyPrice($ds->refund_amount) }}</strong></div>
                                @if ($ds->refund_method)
                                    <div class="inv-settle__meta"><span>{{ __('Method') }}</span><span>{{ ucfirst($ds->refund_method) }}{{ $ds->refund_reference ? ' · ' . $ds->refund_reference : '' }}{{ $ds->refund_date ? ' · ' . \Carbon\Carbon::parse($ds->refund_date)->format('d M Y') : '' }}</span></div>
                                @endif
                            </div>
                            @if (in_array($ds->status, ['recorded', 'disputed']))
                                @if ($ds->status === 'disputed')
                                    @if ($ds->tenant_response_note)
                                        <p class="inv-settle__note">{{ __('You reported:') }} “{{ $ds->tenant_response_note }}”</p>
                                    @endif
                                    @if ($ds->owner_responded_at)
                                        <p class="inv-settle__ownerresp">{{ __('Your landlord responded:') }} “{{ $ds->owner_response_note ?: __('(no message)') }}”</p>
                                    @endif
                                @endif
                                <div class="inv-settle__actions" data-respond-url="{{ route('tenant.deposit-settlement.respond', $ds->id) }}">
                                    <p class="inv-settle__prompt">
                                        {{ $ds->status === 'recorded' ? __('Have you received this refund?') : __('Received it now? Confirm to close this.') }}
                                    </p>
                                    <button type="button" class="inv-btn inv-btn--pay" id="dsConfirm">{{ __('Confirm receipt') }}</button>
                                    @if ($ds->status === 'recorded')
                                        <button type="button" class="inv-btn inv-btn--ghost" data-bs-toggle="modal" data-bs-target="#disputeModal">{{ __('Report an issue') }}</button>
                                    @endif
                                </div>

                                @if ($ds->status === 'recorded')
                                    {{-- Report-an-issue modal (cs modal, no native prompt) --}}
                                    <div class="modal fade" id="disputeModal" tabindex="-1" aria-labelledby="disputeModalLabel" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content" style="border:none;border-radius:14px;overflow:hidden;">
                                                <div class="modal-header" style="border-bottom:0.5px solid #e5e7eb;padding:18px 22px;">
                                                    <h4 id="disputeModalLabel" style="font-size:16px;font-weight:600;color:#111827;margin:0;">{{ __('Report an issue') }}</h4>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                                                </div>
                                                <div class="modal-body" style="padding:20px 22px;">
                                                    <p style="font-size:13px;color:#4b5563;line-height:1.55;margin:0 0 12px;">{{ __('What\'s wrong? For example: you haven\'t received the refund yet, or the amount looks off. Your landlord will get this and can put it right.') }}</p>
                                                    <textarea id="disputeNote" rows="4" maxlength="1000" class="vac-input" placeholder="{{ __('Describe the issue…') }}"></textarea>
                                                </div>
                                                <div class="modal-footer" style="border-top:0.5px solid #e5e7eb;padding:14px 22px;gap:8px;">
                                                    <button type="button" class="inv-btn inv-btn--ghost" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                                                    <button type="button" class="inv-btn inv-btn--pay" id="disputeSubmit">{{ __('Send report') }}</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endif
                        </div>
                    @endif

                    {{-- Tenant-facing deposit reassurance (Model A: landlord holds it, we keep the record) --}}
                    @if (($depositHeld ?? 0) > 0)
                        <div class="inv-deposit-note mb-4">
                            <span class="inv-deposit-note__ic">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><rect x="3" y="4" width="18" height="16" rx="2" stroke="currentColor" stroke-width="1.8"/><circle cx="12" cy="12" r="2.5" stroke="currentColor" stroke-width="1.8"/><path d="M7 4v16M17 4v16" stroke="currentColor" stroke-width="1.4"/></svg>
                            </span>
                            <span class="inv-deposit-note__text">
                                {{ __('Your landlord is holding a refundable security deposit of') }}
                                <strong>{{ currencyPrice($depositHeld) }}</strong>
                                {{ __('for you — returned when you move out, less any agreed deductions. Centresidence keeps the record.') }}
                            </span>
                        </div>
                    @endif

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
    .inv-header-actions { display:flex; align-items:center; gap:8px; flex-wrap:wrap; }
    .inv-btn--ghost { background:#fff; color:#374151; border:0.5px solid #e5e7eb; }
    .inv-btn--ghost:hover { background:#f3f4f6; color:#111827 !important; border-color:#d1d5db; }
    .inv-notice-pill { display:inline-flex; align-items:center; gap:6px; font-size:12px; font-weight:600; padding:8px 14px; border-radius:99px;
        background:#FAEEDA; color:#854F0B; border:0.5px solid #F5D9A8; white-space:nowrap; }
    .inv-notice-pill--ack { background:#E1F5EE; color:#0F6E56; border-color:#B6E3D3; }
    .vac-lead { font-size:13px; color:#374151; line-height:1.6; margin:0 0 16px; }
    .vac-lead strong { color:#111827; }
    .vac-label { display:block; font-size:11px; font-weight:600; text-transform:uppercase; letter-spacing:.05em; color:#6b7280; margin-bottom:6px; }
    .vac-input { width:100%; border:0.5px solid #d1d5db; border-radius:8px; padding:10px 12px; font-size:14px; color:#111827; outline:none; }
    .vac-input:focus { border-color:#185FA5; box-shadow:0 0 0 3px rgba(24,95,165,.1); }
    .vac-warn { margin-top:8px; font-size:12px; line-height:1.5; color:#854F0B; background:#FAEEDA; border:0.5px solid #F5D9A8; border-radius:8px; padding:9px 12px; }
    .inv-settle { background:#fff; border:0.5px solid #e5e7eb; border-radius:12px; padding:16px 18px; }
    .inv-settle--confirmed { border-color:#B6E3D3; background:#F3FBF8; }
    .inv-settle--disputed { border-color:#F3C4BC; background:#FDF6F4; }
    .inv-settle__head { display:flex; align-items:center; justify-content:space-between; gap:10px; margin-bottom:12px; }
    .inv-settle__title { font-size:14px; font-weight:600; color:#111827; }
    .inv-settle__badge { font-size:11px; font-weight:600; padding:3px 10px; border-radius:99px; }
    .inv-settle__badge--ok { background:#E1F5EE; color:#0F6E56; }
    .inv-settle__badge--warn { background:#FAECE7; color:#993C1D; }
    .inv-settle__rows { display:flex; flex-direction:column; gap:5px; }
    .inv-settle__rows > div { display:flex; justify-content:space-between; font-size:13px; color:#4b5563; }
    .inv-settle__ded { font-size:12px !important; color:#9ca3af !important; padding-left:6px; }
    .inv-settle__refund { border-top:0.5px solid #eef2f6; margin-top:6px; padding-top:8px; font-size:15px !important; color:#111827 !important; }
    .inv-settle__refund strong { color:#0F6E56; }
    .inv-settle__meta { font-size:12px !important; color:#9ca3af !important; }
    .inv-settle__actions { margin-top:14px; padding-top:12px; border-top:0.5px solid #eef2f6; display:flex; align-items:center; gap:8px; flex-wrap:wrap; }
    .inv-settle__prompt { font-size:13px; color:#374151; margin:0 auto 0 0; font-weight:500; }
    .inv-settle__note { font-size:12.5px; color:#993C1D; font-style:italic; margin:12px 0 0; }
    .inv-settle__ownerresp { font-size:12.5px; color:#0C447C; margin:6px 0 0; background:#E6F1FB; border:0.5px solid #B5D4F4; border-radius:8px; padding:8px 11px; }
    .inv-deposit-note {
        display: flex; align-items: flex-start; gap: 10px;
        background: #E1F5EE; border: 0.5px solid #B6E3D3; border-radius: 12px; padding: 13px 16px;
    }
    .inv-deposit-note__ic { color: #0F6E56; flex: none; margin-top: 1px; }
    .inv-deposit-note__text { font-size: 12.5px; color: #0F6E56; line-height: 1.55; }
    .inv-deposit-note__text strong { font-weight: 700; }
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
        color: #fff !important;
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

{{-- Pay Upcoming / Advance Rent --}}
<style>
    .pur-list { display:flex; flex-direction:column; border:0.5px solid #e5e7eb; border-radius:10px; overflow:hidden; }
    .pur-row { display:flex; align-items:center; justify-content:space-between; gap:10px; margin:0;
        padding:11px 14px; border-bottom:0.5px solid #f3f4f6; cursor:pointer; font-size:13px; }
    .pur-row:last-child { border-bottom:none; }
    .pur-row--available:hover { background:#f7f9fc; }
    .pur-row--paid, .pur-row--invoiced { cursor:default; background:#fafafa; }
    .pur-row__left { display:flex; align-items:center; gap:10px; min-width:0; }
    .pur-check { width:16px; height:16px; accent-color:#185FA5; flex:none; }
    .pur-check-spacer { width:16px; flex:none; }
    .pur-row__month { font-weight:500; color:#1f2937; }
    .pur-row--paid .pur-row__month, .pur-row--invoiced .pur-row__month { color:#9ca3af; }
    .pur-row__right { display:flex; align-items:center; gap:10px; flex:none; }
    .pur-row__amt { font-variant-numeric:tabular-nums; color:#374151; font-weight:500; }
    .pur-badge { font-size:10px; font-weight:600; padding:2px 9px; border-radius:99px; text-decoration:none; white-space:nowrap; }
    .pur-badge--paid { background:#E1F5EE; color:#0F6E56; }
    .pur-badge--pay { background:#E6F1FB; color:#0C447C; border:0.5px solid #B5D4F4; }
    .pur-badge--pay:hover { background:#185FA5; color:#fff !important; }
    .pur-total { display:flex; align-items:center; justify-content:space-between; margin-top:14px;
        padding-top:12px; border-top:0.5px solid #e5e7eb; font-size:13px; color:#6b7280; }
    .pur-total strong { font-size:15px; color:#111827; font-variant-numeric:tabular-nums; }
</style>
<div class="modal fade" id="payUpcomingModal" tabindex="-1" aria-labelledby="payUpcomingLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:14px;overflow:hidden;">
            <form action="{{ route('tenant.invoice.pay.upcoming') }}" method="POST">
                @csrf
                <div class="modal-header" style="background:#fafafa;border-bottom:0.5px solid #e5e7eb;">
                    <h5 class="modal-title" id="payUpcomingLabel" style="font-size:15px;font-weight:600;color:#111827;">{{ __('Pay Upcoming Rent') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" style="padding:18px 20px;">
                    @if (empty($upcomingRentMonths))
                        <p style="font-size:13px;color:#6b7280;line-height:1.6;margin:0;">
                            {{ __('Advance rent payment isn\'t available for your unit — it applies to monthly-rent units. Please contact your landlord.') }}
                        </p>
                    @else
                        <p style="font-size:13px;color:#6b7280;line-height:1.6;margin-bottom:14px;">
                            {{ __('Tick the months you\'d like to pay — you can settle ahead even before an invoice is generated. Each selected month becomes a payable invoice below.') }}
                        </p>
                        <div class="pur-list">
                            @foreach ($upcomingRentMonths as $month)
                                <label class="pur-row pur-row--{{ $month['state'] }}">
                                    <span class="pur-row__left">
                                        @if ($month['state'] === 'available')
                                            <input type="checkbox" name="periods[]" value="{{ $month['period'] }}" class="pur-check" data-amount="{{ $month['amount'] }}">
                                        @else
                                            <span class="pur-check-spacer"></span>
                                        @endif
                                        <span class="pur-row__month">{{ $month['label'] }}</span>
                                    </span>
                                    <span class="pur-row__right">
                                        <span class="pur-row__amt">{{ currencyPrice($month['amount']) }}</span>
                                        @if ($month['state'] === 'paid')
                                            <span class="pur-badge pur-badge--paid">{{ __('Paid') }}</span>
                                        @elseif ($month['state'] === 'invoiced')
                                            <a href="{{ route('tenant.invoice.pay', $month['invoice_id']) }}" class="pur-badge pur-badge--pay">{{ __('Pay') }}</a>
                                        @endif
                                    </span>
                                </label>
                            @endforeach
                        </div>
                        <div class="pur-total">
                            <span>{{ __('Selected total') }}</span>
                            <strong id="purTotal"
                                    data-symbol="{{ getCurrencySymbol() }}"
                                    data-placement="{{ getCurrencyPlacement() }}">{{ currencyPrice(0) }}</strong>
                        </div>
                    @endif
                </div>
                <div class="modal-footer" style="border-top:0.5px solid #e5e7eb;">
                    <button type="button" class="inv-btn inv-btn--ghost" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    @if (!empty($upcomingRentMonths))
                        <button type="submit" class="inv-btn inv-btn--pay">{{ __('Prepare Invoices') }}</button>
                    @endif
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('style')
    @include('common.layouts.datatable-style')
@endpush

@push('script')
    @include('common.layouts.datatable-script')
    <script src="{{ asset('/') }}assets/js/pages/alldatatables.init.js"></script>
    <script>
        (function () {
            var totalEl = document.getElementById('purTotal');
            if (!totalEl) return;
            var symbol    = totalEl.getAttribute('data-symbol') || '';
            var placement = totalEl.getAttribute('data-placement') || 'before';
            function fmt(n) {
                var s = Number(n).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                return placement === 'after' ? (s + ' ' + symbol) : (symbol + s);
            }
            function recalc() {
                var total = 0;
                document.querySelectorAll('.pur-check:checked').forEach(function (c) {
                    total += parseFloat(c.getAttribute('data-amount')) || 0;
                });
                totalEl.textContent = fmt(total);
            }
            document.querySelectorAll('.pur-check').forEach(function (c) {
                c.addEventListener('change', recalc);
            });
        })();
    </script>

    {{-- Notice-to-vacate: flag an early date + submit --}}
    <script>
        (function () {
            var dateEl = document.getElementById('vacateDate');
            if (!dateEl) return;
            var earliest = dateEl.getAttribute('data-earliest');
            var warnEl   = document.getElementById('vacateWarn');
            var submitEl = document.getElementById('vacateSubmit');
            var storeUrl = @json(route('tenant.vacation-notice.store'));

            function checkEarly() {
                warnEl.style.display = (dateEl.value && earliest && dateEl.value < earliest) ? 'block' : 'none';
            }
            dateEl.addEventListener('change', checkEarly);
            dateEl.addEventListener('input', checkEarly);

            submitEl.addEventListener('click', function () {
                var date = dateEl.value;
                if (!date) { if (window.toastr) toastr.error('{{ __('Choose a move-out date.') }}'); return; }
                submitEl.disabled = true;
                $.ajax({
                    url: storeUrl,
                    type: 'POST',
                    data: { intended_move_out_date: date, message: document.getElementById('vacateMsg').value || '' },
                    success: function (res) {
                        if (window.toastr) toastr.success(res.message || '{{ __('Notice sent.') }}');
                        setTimeout(function () { window.location.reload(); }, 900);
                    },
                    error: function (xhr) {
                        submitEl.disabled = false;
                        var msg = (xhr.responseJSON && (xhr.responseJSON.message || xhr.responseJSON.error)) || '{{ __('Could not send your notice.') }}';
                        if (window.toastr) toastr.error(msg);
                    }
                });
            });
        })();
    </script>

    {{-- Deposit settlement: confirm receipt / raise a concern --}}
    <script>
        (function () {
            var actions = document.querySelector('.inv-settle__actions');
            if (!actions) return;
            var url = actions.getAttribute('data-respond-url');

            function send(action, note) {
                $.ajax({
                    url: url, type: 'POST', data: { action: action, note: note || '' },
                    success: function (res) {
                        if (window.toastr) toastr.success(res.message || '{{ __('Done') }}');
                        setTimeout(function () { window.location.reload(); }, 900);
                    },
                    error: function (xhr) {
                        var m = (xhr.responseJSON && (xhr.responseJSON.message || xhr.responseJSON.error)) || '{{ __('Something went wrong.') }}';
                        if (window.toastr) toastr.error(m);
                    }
                });
            }

            document.getElementById('dsConfirm').addEventListener('click', function () {
                if (window.csConfirm) {
                    csConfirm({
                        title: '{{ __('Confirm receipt?') }}',
                        message: '{{ __('Confirm you have received this deposit refund from your landlord.') }}',
                        confirmText: '{{ __('Yes, I received it') }}', cancelText: '{{ __('Cancel') }}'
                    }).then(function (ok) { if (ok) send('confirm'); });
                } else if (confirm('{{ __('Confirm you received this refund?') }}')) { send('confirm'); }
            });

            var disputeSubmit = document.getElementById('disputeSubmit');
            if (disputeSubmit) {
                disputeSubmit.addEventListener('click', function () {
                    var note = (document.getElementById('disputeNote').value || '').trim();
                    if (!note) { if (window.toastr) toastr.error('{{ __('Please describe your concern.') }}'); return; }
                    disputeSubmit.disabled = true;
                    send('dispute', note);
                });
            }
        })();
    </script>
@endpush