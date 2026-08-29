@extends('tenant.layouts.app')

@section('content')
@php
    $isPaid    = $invoice->status == INVOICE_STATUS_PAID;
    $isOverdue = !$isPaid && $invoice->due_date < date('Y-m-d');
    $paidAt    = $order?->updated_at ?? $order?->created_at;
@endphp
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="page-content-wrapper bg-white p-30 radius-20">
                <div class="container">

                    {{-- Success Banner --}}
                    @if (session('success'))
                        <div class="receipt-alert receipt-alert--success mb-4">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                                <path d="M22 11.08V12a10 10 0 11-5.93-9.14" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M22 4L12 14.01l-3-3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            {{ session('success') }}
                        </div>
                    @endif

                    {{-- Page Header --}}
                    <div class="dash-header mb-4">
                        <div>
                            <h2 class="dash-title">{{ __('Payment Receipt') }}</h2>
                            <p class="dash-subtitle">{{ __('Thank you. Here is your payment receipt.') }}</p>
                        </div>
                        <nav aria-label="breadcrumb">
                            <ol class="mp-breadcrumb">
                                <li><a href="{{ route('tenant.dashboard') }}">{{ __('Dashboard') }}</a></li>
                                <li><a href="{{ route('tenant.invoice.index') }}">{{ __('Invoices') }}</a></li>
                                <li aria-current="page">{{ __('Receipt') }}</li>
                            </ol>
                        </nav>
                    </div>

                    {{-- Receipt Card --}}
                    <div class="receipt-wrapper" id="receiptCard">

                        {{-- Receipt Header --}}
                        <div class="receipt-head">
                            <div class="receipt-head__left">
                                <div class="receipt-head__icon">
                                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none">
                                        <path d="M9 11l3 3L22 4" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="receipt-head__eyebrow">{{ __('Invoice Payment') }}</p>
                                    <h3 class="receipt-head__order-id">{{ $invoice->invoice_no }}</h3>
                                </div>
                            </div>

                            {{-- Right side: status pill + date --}}
                            <div class="receipt-head__right">
                                <div>
                                    @if ($isPaid)
                                        <span class="receipt-status receipt-status--paid">
                                            <svg width="10" height="10" viewBox="0 0 16 16" fill="none">
                                                <path d="M3 8l4 4 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                            {{ __('Paid') }}
                                        </span>
                                    @elseif ($isOverdue)
                                        <span class="receipt-status receipt-status--cancelled">
                                            <svg width="10" height="10" viewBox="0 0 16 16" fill="none">
                                                <path d="M8 5v4M8 11v.5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                            </svg>
                                            {{ __('Overdue') }}
                                        </span>
                                    @else
                                        <span class="receipt-status receipt-status--pending">
                                            <svg width="10" height="10" viewBox="0 0 16 16" fill="none">
                                                <circle cx="8" cy="8" r="5.5" stroke="currentColor" stroke-width="1.6"/>
                                                <path d="M8 5v3.5l2 1.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                                            </svg>
                                            {{ __('Pending') }}
                                        </span>
                                    @endif
                                </div>
                                <p class="receipt-head__date">
                                    {{ ($paidAt ?? $invoice->created_at)->format('d M Y, g:i A') }}
                                </p>
                            </div>
                        </div>
                        {{-- /receipt-head --}}

                        {{-- What happens next banner --}}
                        @if ($isPaid)
                            <div class="receipt-next-steps receipt-next-steps--success">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" style="flex-shrink:0">
                                    <path d="M22 11.08V12a10 10 0 11-5.93-9.14" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M22 4L12 14.01l-3-3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                <div>
                                    <p class="receipt-next-steps__title">{{ __('Payment Confirmed') }}</p>
                                    <p class="receipt-next-steps__body">{{ __('Your payment was received successfully. Your landlord has been notified — no further action is needed.') }}</p>
                                </div>
                            </div>
                        @else
                            <div class="receipt-next-steps">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" style="flex-shrink:0">
                                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.8"/>
                                    <path d="M12 8v4m0 4h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                </svg>
                                <div>
                                    <p class="receipt-next-steps__title">{{ __('Payment Processing') }}</p>
                                    <p class="receipt-next-steps__body">{{ __('This invoice is not settled yet. If you have just paid, it may take a few moments to confirm. You can also pay it below.') }}</p>
                                </div>
                            </div>
                        @endif

                        {{-- Invoice details strip --}}
                        <div class="receipt-section">
                            <div class="receipt-facts">
                                <div class="receipt-fact">
                                    <span class="receipt-fact__label">{{ __('Billing Month') }}</span>
                                    <span class="receipt-fact__val">{{ $invoice->month }} {{ ($paidAt ?? $invoice->created_at)->format('Y') }}</span>
                                </div>
                                <div class="receipt-fact">
                                    <span class="receipt-fact__label">{{ __('Due Date') }}</span>
                                    <span class="receipt-fact__val">{{ \Carbon\Carbon::parse($invoice->due_date)->format('d M Y') }}</span>
                                </div>
                                @if ($owner)
                                    <div class="receipt-fact">
                                        <span class="receipt-fact__label">{{ __('Billed By') }}</span>
                                        <span class="receipt-fact__val">{{ $owner->print_name ?: trim($owner->first_name . ' ' . $owner->last_name) }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Line Items --}}
                        <div class="receipt-section">
                            <h4 class="receipt-section__title">{{ __('Charges') }}</h4>
                            <div class="receipt-items">
                                @forelse ($items as $item)
                                    <div class="receipt-item receipt-item--line">
                                        <div class="receipt-item__info">
                                            {{-- Show the actual invoice type (Rent, Repair, …); description is secondary --}}
                                            <p class="receipt-item__name">{{ $item->invoiceType?->name ?: ($item->description ?: __('Charge')) }}</p>
                                            @if ($item->invoiceType?->name && $item->description)
                                                <p class="receipt-item__meta">{{ $item->description }}</p>
                                            @endif
                                        </div>
                                        <div class="receipt-item__price">{{ currencyPrice($item->amount) }}</div>
                                    </div>
                                @empty
                                    <div class="receipt-item receipt-item--line">
                                        <div class="receipt-item__info">
                                            <p class="receipt-item__name">{{ __('Charge') }}</p>
                                        </div>
                                        <div class="receipt-item__price">{{ currencyPrice($invoice->amount) }}</div>
                                    </div>
                                @endforelse
                            </div>
                        </div>

                        {{-- Totals + Payment Info --}}
                        <div class="receipt-meta-grid">

                            {{-- Payment Summary --}}
                            <div class="receipt-section">
                                <h4 class="receipt-section__title">{{ __('Payment Summary') }}</h4>
                                <div class="receipt-summary">
                                    <div class="receipt-summary__row receipt-summary__row--total">
                                        <span>{{ __('Total') }}</span>
                                        <span>{{ currencyPrice($invoice->amount) }}</span>
                                    </div>
                                </div>
                            </div>

                            {{-- Payment Method --}}
                            <div class="receipt-section">
                                <h4 class="receipt-section__title">{{ __('Payment Method') }}</h4>
                                <div class="receipt-payment-method">
                                    @if ($order)
                                        <div>
                                            <p class="receipt-payment-method__name">{{ $order->gatewayTitle ?? __('Payment') }}</p>
                                            @if ($order->bank_name)
                                                <p class="receipt-payment-method__detail">{{ $order->bank_name }}</p>
                                            @endif
                                            @if ($order->mpesa_transaction_code)
                                                <p class="receipt-payment-method__detail">
                                                    {{ __('Code:') }} <strong>{{ $order->mpesa_transaction_code }}</strong>
                                                </p>
                                            @endif
                                        </div>
                                    @else
                                        <p class="receipt-payment-method__detail">{{ __('Awaiting payment.') }}</p>
                                    @endif
                                </div>
                            </div>

                        </div>

                        {{-- Footer Actions --}}
                        <div class="receipt-actions">
                            <button type="button" onclick="window.print()" class="receipt-btn receipt-btn--outline">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none">
                                    <polyline points="6 9 6 2 18 2 18 9" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                    <rect x="6" y="14" width="12" height="8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                {{ __('Print Receipt') }}
                            </button>
                            <a href="{{ route('tenant.invoice.print', $invoice->id) }}" target="_blank" class="receipt-btn receipt-btn--ghost">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none">
                                    <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                                    <path d="M14 2v6h6M9 13h6M9 17h4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                </svg>
                                {{ __('View Invoice') }}
                            </a>
                            @if (!$isPaid)
                                <a href="{{ route('tenant.invoice.pay', $invoice->id) }}" class="receipt-btn receipt-btn--primary">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none">
                                        <rect x="2" y="6" width="20" height="14" rx="2" stroke="currentColor" stroke-width="1.8"/>
                                        <path d="M2 10h20" stroke="currentColor" stroke-width="1.8"/>
                                    </svg>
                                    {{ __('Pay Now') }}
                                </a>
                            @else
                                <a href="{{ route('tenant.invoice.index') }}" class="receipt-btn receipt-btn--primary">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none">
                                        <path d="M9 11l3 3L22 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    {{ __('My Invoices') }}
                                </a>
                            @endif
                        </div>

                    </div>
                    {{-- /receipt-wrapper --}}

                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('style')
    <style>
        /* ── Page chrome ─────────────────────────────────────────────── */
        .dash-header   { display:flex; align-items:flex-start; justify-content:space-between; flex-wrap:wrap; gap:1rem; }
        .dash-title    { font-size:22px; font-weight:500; color:#111827; margin:0 0 4px; }
        .dash-subtitle { font-size:14px; color:#6b7280; margin:0; }
        .mp-breadcrumb { display:flex; align-items:center; gap:6px; list-style:none; padding:0; margin:0; font-size:13px; color:#9ca3af; }
        .mp-breadcrumb li:not(:last-child)::after { content:'/'; margin-left:6px; color:#d1d5db; }
        .mp-breadcrumb a { color:#185FA5; text-decoration:none; }
        .mp-breadcrumb a:hover { text-decoration:underline; }

        /* ── Success alert ───────────────────────────────────────────── */
        .receipt-alert { display:flex; align-items:center; gap:10px; padding:12px 16px; border-radius:10px; font-size:13px; font-weight:500; }
        .receipt-alert--success { background:#E1F5EE; color:#0F6E56; border:0.5px solid #A7DFC9; }

        /* ── Receipt card ────────────────────────────────────────────── */
        .receipt-wrapper {
            background:#fff; border:0.5px solid #e5e7eb; border-radius:16px;
            overflow:hidden; max-width:860px; margin:0 auto;
        }

        /* ── Receipt header ──────────────────────────────────────────── */
        .receipt-head {
            display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:1rem;
            padding:1.5rem 1.75rem; background:linear-gradient(135deg,#185FA5 0%,#1D9E75 100%); color:#fff;
        }
        .receipt-head__left  { display:flex; align-items:center; gap:14px; }
        .receipt-head__icon  {
            width:52px; height:52px; border-radius:14px; background:rgba(255,255,255,.2);
            display:flex; align-items:center; justify-content:center; flex-shrink:0;
        }
        .receipt-head__eyebrow { font-size:11px; font-weight:500; color:#c9ccd1b9; text-transform:uppercase; letter-spacing:.07em; opacity:.8; margin:0 0 4px; }
        .receipt-head__order-id { font-size:20px; font-weight:700; font-family:monospace; letter-spacing:.04em; margin:0; }
        .receipt-head__date { font-size:12px; opacity:.8; margin:0 0 8px; text-align:right; color:#c9ccd1; padding-top:10px; }
        .receipt-head__right { display:flex; flex-direction:column; align-items:flex-end; }

        /* ── Status badges ───────────────────────────────────────────── */
        .receipt-status { display:inline-flex; align-items:center; gap:5px; padding:4px 12px; border-radius:99px; font-size:11px; font-weight:600; }
        .receipt-status--pending   { background:rgba(255,255,255,.2); color:#fff; border:1px solid rgba(255,255,255,.3); }
        .receipt-status--paid      { background:rgba(255,255,255,.2); color:#fff; border:1px solid rgba(255,255,255,.3); }
        .receipt-status--cancelled { background:rgba(255,80,80,.2); color:#fff; border:1px solid rgba(255,80,80,.3); }

        /* ── Next steps banner ───────────────────────────────────────── */
        .receipt-next-steps {
            display:flex; align-items:flex-start; gap:12px;
            background:#FFF9EC; border-bottom:0.5px solid #FDE68A;
            padding:14px 1.75rem; color:#92400E; font-size:13px;
        }
        .receipt-next-steps__title { font-weight:600; margin:0 0 3px; }
        .receipt-next-steps__body  { margin:0; line-height:1.55; }
        .receipt-next-steps--success { background:#E1F5EE; border-bottom-color:#A7DFC9; color:#0F6E56; }

        /* ── Sections ────────────────────────────────────────────────── */
        .receipt-section { padding:1.25rem 1.75rem; border-bottom:0.5px solid #f3f4f6; }
        .receipt-section:last-child { border-bottom:none; }
        .receipt-section__title { font-size:11px; font-weight:600; text-transform:uppercase; letter-spacing:.08em; color:#9ca3af; margin:0 0 1rem; }

        /* ── Facts strip ─────────────────────────────────────────────── */
        .receipt-facts { display:flex; flex-wrap:wrap; gap:1.5rem 2.5rem; }
        .receipt-fact { display:flex; flex-direction:column; gap:4px; }
        .receipt-fact__label { font-size:10px; text-transform:uppercase; letter-spacing:.07em; color:#9ca3af; font-weight:500; }
        .receipt-fact__val   { font-size:14px; font-weight:500; color:#111827; }

        /* ── Line items ──────────────────────────────────────────────── */
        .receipt-items { display:flex; flex-direction:column; gap:0; }
        .receipt-item--line {
            display:flex; align-items:center; justify-content:space-between; gap:14px;
            padding:10px 0; border-bottom:0.5px solid #f9fafb;
        }
        .receipt-item--line:last-child { border-bottom:none; }
        .receipt-item__name  { font-size:13px; font-weight:500; color:#111827; margin:0; }
        .receipt-item__meta  { font-size:11px; color:#9ca3af; margin:3px 0 0; }
        .receipt-item__price { font-size:14px; font-weight:600; color:#111827; white-space:nowrap; }

        /* ── Meta grid (summary + payment method side by side) ──────── */
        .receipt-meta-grid { display:grid; grid-template-columns:1fr 1fr; }
        .receipt-meta-grid .receipt-section { border-right:0.5px solid #f3f4f6; }
        .receipt-meta-grid .receipt-section:last-child { border-right:none; }

        /* ── Summary table ───────────────────────────────────────────── */
        .receipt-summary { display:flex; flex-direction:column; gap:8px; }
        .receipt-summary__row { display:flex; justify-content:space-between; font-size:13px; color:#6b7280; }
        .receipt-summary__row--total { font-size:15px; font-weight:700; color:#111827; }

        /* ── Payment method ──────────────────────────────────────────── */
        .receipt-payment-method { display:flex; align-items:center; gap:12px; }
        .receipt-payment-method__name   { font-size:13px; font-weight:500; color:#111827; margin:0 0 2px; }
        .receipt-payment-method__detail { font-size:12px; color:#6b7280; margin:0; }

        /* ── Actions ─────────────────────────────────────────────────── */
        .receipt-actions { display:flex; align-items:center; justify-content:flex-end; gap:10px; flex-wrap:wrap; padding:1.25rem 1.75rem; background:#fafafa; border-top:0.5px solid #e5e7eb; }
        .receipt-btn {
            display:inline-flex; align-items:center; gap:6px;
            font-size:13px; font-weight:500; padding:8px 16px; border-radius:8px;
            text-decoration:none; cursor:pointer; border:none; transition:background .15s, transform .12s;
        }
        .receipt-btn--primary { background:#185FA5; color:#fff; }
        .receipt-btn--primary:hover { background:#0F4A84; color:#fff !important; transform:translateY(-1px); }
        .receipt-btn--ghost   { background:#f3f4f6; color:#374151; border:0.5px solid #e5e7eb; }
        .receipt-btn--ghost:hover { background:#e5e7eb; color:#111827 !important; }
        .receipt-btn--outline { background:#fff; color:#374151; border:0.5px solid #e5e7eb; }
        .receipt-btn--outline:hover { background:#f3f4f6; }

        /* ── Print styles ────────────────────────────────────────────── */
        @media print {
            .main-content > *:not(.page-content) { display:none; }
            .receipt-actions { display:none; }
            .receipt-next-steps { display:none; }
            .receipt-head { -webkit-print-color-adjust:exact; print-color-adjust:exact; }
            .receipt-wrapper { border:none; border-radius:0; max-width:100%; }
            body, .page-content, .page-content-wrapper { background:#fff !important; padding:0 !important; }
        }

        /* ── Responsive ──────────────────────────────────────────────── */
        @media (max-width: 640px) {
            .receipt-head { padding:1.25rem; }
            .receipt-head__order-id { font-size:16px; }
            .receipt-section { padding:1rem 1.25rem; }
            .receipt-meta-grid { grid-template-columns:1fr; }
            .receipt-meta-grid .receipt-section { border-right:none; border-bottom:0.5px solid #f3f4f6; }
            .receipt-actions { justify-content:stretch; flex-direction:column; }
            .receipt-btn { justify-content:center; width:100%; }
        }
    </style>
@endpush
