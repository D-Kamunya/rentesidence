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
                            <h2 class="dash-title">{{ __('Dashboard') }}</h2>
                            <p class="dash-subtitle">
                                {{ __('Welcome back') }}, <strong>{{ auth()->user()->name }}</strong>
                                <span class="iconify font-24" data-icon="openmoji:waving-hand"></span>
                            </p>
                        </div>
                        <a href="{{ route('tenant.maintenance-request.index') }}" class="theme-btn-primary">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none">
                                <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/>
                            </svg>
                            {{ __('Maintenance Request') }}
                        </a>
                    </div>

                    {{-- Summary Cards --}}
                    <div class="row g-3 mb-4">

                        {{-- My Unit --}}
                        <div class="col-12 col-md-4">
                            <div class="glance-card glance-card--blue">
                                <div class="glance-card__icon-wrap">
                                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
                                        <path d="M3 10.5L12 3l9 7.5V21a1 1 0 01-1 1H5a1 1 0 01-1-1V10.5z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>
                                        <path d="M9 22V12h6v10" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>
                                    </svg>
                                </div>
                                <div class="glance-card__body">
                                    <p class="glance-card__label">My Unit</p>
                                    <p class="glance-card__value">{{ $unit->unit_name }}</p>
                                    <p class="glance-card__sub">{{ Str::limit($property->name, 30) }}</p>
                                </div>
                                <div class="glance-card__deco" aria-hidden="true">
                                    <svg viewBox="0 0 80 80" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <circle cx="60" cy="20" r="40" stroke="currentColor" stroke-width="18" opacity=".07"/>
                                        <circle cx="60" cy="20" r="22" stroke="currentColor" stroke-width="10" opacity=".06"/>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        {{-- Current Rent --}}
                        <div class="col-12 col-md-4">
                            <div class="glance-card glance-card--green">
                                <div class="glance-card__icon-wrap">
                                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
                                        <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.7"/>
                                        <path d="M12 7v1m0 8v1M9.5 9.5A2.5 2.5 0 0112 8a2.5 2.5 0 010 5 2.5 2.5 0 000 5 2.5 2.5 0 002.5-1.5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                                    </svg>
                                </div>
                                <div class="glance-card__body">
                                    <p class="glance-card__label">Monthly Rent</p>
                                    <p class="glance-card__value">{{ currencyPrice($tenant->general_rent) }}</p>
                                    <p class="glance-card__sub">Current rate</p>
                                </div>
                                <div class="glance-card__deco" aria-hidden="true">
                                    <svg viewBox="0 0 80 80" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <circle cx="60" cy="20" r="40" stroke="currentColor" stroke-width="18" opacity=".07"/>
                                        <circle cx="60" cy="20" r="22" stroke="currentColor" stroke-width="10" opacity=".06"/>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        {{-- Total Tickets --}}
                        <div class="col-12 col-md-4">
                            <a href="{{ route('tenant.ticket.index') }}" style="text-decoration:none; display:block;">
                                <div class="glance-card glance-card--amber">
                                    <div class="glance-card__icon-wrap">
                                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
                                            <path d="M15 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V9l-4-4z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>
                                            <path d="M15 5v4h4M9 13h6M9 17h4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                                        </svg>
                                    </div>
                                    <div class="glance-card__body">
                                        <p class="glance-card__label">Total Tickets</p>
                                        <p class="glance-card__value">{{ $totalTickets }}</p>
                                        <p class="glance-card__sub">All time</p>
                                    </div>
                                    <div class="glance-card__deco" aria-hidden="true">
                                        <svg viewBox="0 0 80 80" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <circle cx="60" cy="20" r="40" stroke="currentColor" stroke-width="18" opacity=".07"/>
                                            <circle cx="60" cy="20" r="22" stroke="currentColor" stroke-width="10" opacity=".06"/>
                                        </svg>
                                    </div>
                                </div>
                            </a>
                        </div>

                    </div>
                    {{-- End Summary Cards --}}

                    {{-- Invoices + Notice Board --}}
                    <div class="row g-3">

                        {{-- Invoice Card --}}
                        <div class="col-lg-8">
                            <div class="dash-card">

                                <div class="dash-card__head d-flex align-items-center justify-content-between">
                                    <span style="font-weight:500;font-size:14px;">Invoices</span>
                                    <div class="inv-tabs">
                                        <button class="inv-tab inv-tab--active" onclick="switchTab(this,'paid')">
                                            <span class="inv-tab__dot inv-tab__dot--paid"></span>Paid
                                        </button>
                                        <button class="inv-tab" onclick="switchTab(this,'unpaid')">
                                            <span class="inv-tab__dot inv-tab__dot--unpaid"></span>Unpaid
                                        </button>
                                    </div>
                                </div>

                                {{-- Paid --}}
                                <div id="tab-paid" class="inv-panel p-3">
                                    @php 
                                        $paidInvoices = $invoices->where('status', ACTIVE)->sortByDesc('created_at')->take(5);
                                    @endphp
                                    @forelse ($paidInvoices as $invoice)
                                        <div class="inv-row">
                                            <div class="inv-row__icon inv-row__icon--paid">
                                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none">
                                                    <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                                </svg>
                                            </div>
                                            <div class="inv-row__meta">
                                                <p class="inv-row__ref">{{ $invoice->name }}</p>
                                                @if ($invoice->item_types_label)
                                                    <p class="inv-row__types">{{ $invoice->item_types_label }}</p>
                                                @endif
                                                <p class="inv-row__date">{{ $invoice->created_at->format('d M Y') }}</p>
                                            </div>
                                            <div class="ms-auto text-end">
                                                <p class="inv-row__amount inv-row__amount--paid">{{ currencyPrice($invoice->amount) }}</p>
                                                <span class="inv-row__num">{{ $invoice->invoice_no }}</span>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="inv-empty">
                                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke="#1D9E75" stroke-width="1.5"/></svg>
                                            <p>No paid invoices yet</p>
                                        </div>
                                    @endforelse
                                    
                                    {{-- View all paid link --}}
                                    @if($invoices->where('status', ACTIVE)->count() > 5)
                                        <div class="inv-view-all">
                                            <a href="{{ route('tenant.invoice.index', ['status' => 'paid']) }}" class="view-all-link">
                                                View all paid invoices
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none">
                                                    <path d="M9 5l7 7-7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                            </a>
                                        </div>
                                    @endif
                                </div>

                                {{-- Unpaid --}}
                                <div id="tab-unpaid" class="inv-panel p-3" style="display:none;">
                                    @php 
                                        $unpaidInvoices = $invoices->where('status', '!=', ACTIVE)->sortByDesc('created_at')->take(5);
                                    @endphp
                                    @forelse ($unpaidInvoices as $invoice)
                                        @php
                                            $due      = \Carbon\Carbon::parse($invoice->due_date);
                                            $overdue  = $due->isPast();
                                            $daysLeft = (int) abs($due->diffInDays(now()));
                                        @endphp
                                        <a href="{{ route('tenant.invoice.pay', $invoice->id) }}">
                                            <div class="inv-row {{ $overdue ? 'inv-row--overdue' : '' }}">
                                                <div class="inv-row__icon {{ $overdue ? 'inv-row__icon--overdue' : 'inv-row__icon--unpaid' }}">
                                                    @if ($overdue)
                                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none">
                                                            <path d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                                        </svg>
                                                    @else
                                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none">
                                                            <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2"/>
                                                            <path d="M12 7v5l3 3" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                                        </svg>
                                                    @endif
                                                </div>
                                                <div class="inv-row__meta">
                                                    <p class="inv-row__ref">{{ $invoice->name }}</p>
                                                    @if ($invoice->item_types_label)
                                                        <p class="inv-row__types">{{ $invoice->item_types_label }}</p>
                                                    @endif
                                                    <p class="inv-row__date">
                                                        Due {{ $due->format('d M Y') }}
                                                        @if ($overdue)
                                                            &mdash; <span style="color:#993C1D;font-weight:500;">{{ $daysLeft }} days overdue</span>
                                                        @else
                                                            &mdash; <span style="color:#854F0B;">{{ $daysLeft }} days left</span>
                                                        @endif
                                                    </p>
                                                </div>
                                                <div class="ms-auto text-end inv-row__actions">
                                                    <div class="inv-row__actions-inner">
                                                        <p class="inv-row__amount {{ $overdue ? 'inv-row__amount--overdue' : 'inv-row__amount--unpaid' }}">
                                                            {{ currencyPrice($invoice->amount) }}
                                                        </p>
                                                        <span class="inv-row__num">{{ $invoice->invoice_no }}</span>
                                                        <svg class="inv-row__arrow" width="16" height="16" viewBox="0 0 24 24" fill="none">
                                                            <path d="M9 5l7 7-7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                        </svg>
                                                    </div>
                                                </div>
                                            </div>
                                        </a>
                                    @empty
                                        <div class="inv-empty">
                                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke="#1D9E75" stroke-width="1.5"/></svg>
                                            <p>All clear — no outstanding invoices!</p>
                                        </div>
                                    @endforelse
                                    
                                    {{-- View all unpaid link --}}
                                    @if($invoices->where('status', '!=', ACTIVE)->count() > 5)
                                        <div class="inv-view-all">
                                            <a href="{{ route('tenant.invoice.index', ['status' => 'unpaid']) }}" class="view-all-link">
                                                View all unpaid invoices
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none">
                                                    <path d="M9 5l7 7-7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                            </a>
                                        </div>
                                    @endif
                                </div>

                            </div>
                        </div>
                        {{-- End Invoice Card --}}

                        {{-- Notice Board --}}
                        @if (ownerCurrentPackage(auth()->user()->owner_user_id)?->notice_support == ACTIVE || isAddonInstalled('PROTYSAAS') < 1)
                            <div class="col-lg-4">
                                <div class="dash-card h-100">
                                    <div class="dash-card__head d-flex align-items-center justify-content-between">
                                        <span style="font-weight:500;font-size:14px;">Notice Board</span>
                                        @if (count($notices))
                                            <a href="{{ route('tenant.notices') }}" class="theme-link" style="font-size:12px;display:inline-flex;align-items:center;gap:4px;text-decoration:none;">
                                                See All
                                                <svg width="12" height="12" viewBox="0 0 16 16" fill="none"><path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                            </a>
                                        @endif
                                    </div>

                                    @php
                                        $activeNotices = collect($notices)->filter(
                                            fn($n) => $n->start_date <= $today && $n->end_date >= $today
                                        );
                                    @endphp

                                    @php
                                        $noticeColors = [
                                            ['bg' => '#F0F7FD', 'border' => '#B3D5F5', 'accent' => '#185FA5', 'icon_bg' => '#E6F1FB', 'icon' => '#185FA5', 'meta' => '#4A80B5'],
                                            ['bg' => '#F0FBF6', 'border' => '#9FE1CB', 'accent' => '#1D9E75', 'icon_bg' => '#E1F5EE', 'icon' => '#0F6E56', 'meta' => '#2E7D6A'],
                                            ['bg' => '#FDF6EC', 'border' => '#F5D9A8', 'accent' => '#854F0B', 'icon_bg' => '#FAEEDA', 'icon' => '#854F0B', 'meta' => '#7A4A10'],
                                            ['bg' => '#F5F3FF', 'border' => '#C9C4F0', 'accent' => '#534AB7', 'icon_bg' => '#ECEAF9', 'icon' => '#534AB7', 'meta' => '#4B43A8'],
                                        ];
                                    @endphp
                                    
                                    @forelse ($activeNotices as $notice)
                                        @php $c = $noticeColors[$loop->index % count($noticeColors)]; @endphp
                                        <button
                                            type="button"
                                            class="notice-item notice-item--btn"
                                            style="background:{{ $c['bg'] }};border:0.5px solid {{ $c['border'] }};"
                                            onclick="openNoticeModal(
                                                {{ json_encode($notice->title) }},
                                                {{ json_encode($notice->details) }},
                                                {{ json_encode(\Carbon\Carbon::parse($notice->start_date)->format('d M Y')) }},
                                                {{ json_encode(\Carbon\Carbon::parse($notice->end_date)->format('d M Y')) }},
                                                {{ json_encode($c['bg']) }},
                                                {{ json_encode($c['accent']) }}
                                            )"
                                        >
                                            <div class="notice-item__accent" style="background:{{ $c['accent'] }};"></div>
                                            <div class="notice-item__icon-wrap" style="background:{{ $c['icon_bg'] }};color:{{ $c['icon'] }};">
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none">
                                                    <rect x="3" y="4" width="18" height="16" rx="2" stroke="currentColor" stroke-width="1.8"/>
                                                    <path d="M8 2v4M16 2v4M3 10h18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                                </svg>
                                            </div>
                                            <div class="notice-item__body">
                                                <p class="notice-item__title" style="color:#111827;">{{ Str::limit($notice->title, 46, '...') }}</p>
                                                <p class="notice-item__meta" style="color:{{ $c['meta'] }};">
                                                    <svg width="10" height="10" viewBox="0 0 16 16" fill="none" style="display:inline;vertical-align:middle;margin-right:3px;">
                                                        <circle cx="8" cy="8" r="6" stroke="currentColor" stroke-width="1.4"/>
                                                        <path d="M8 5v3.5l2 1.5" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>
                                                    </svg>
                                                    Ends {{ \Carbon\Carbon::parse($notice->end_date)->format('d M Y') }}
                                                </p>
                                            </div>
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" style="flex-shrink:0;color:{{ $c['accent'] }};opacity:.5;margin-right:4px;">
                                                <path d="M9 5l7 7-7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </button>
                                    @empty
                                        <div class="notice-empty">
                                            <svg width="26" height="26" viewBox="0 0 24 24" fill="none"><rect x="3" y="4" width="18" height="16" rx="2" stroke="#9ca3af" stroke-width="1.5"/><path d="M8 2v4M16 2v4M3 10h18" stroke="#9ca3af" stroke-width="1.5" stroke-linecap="round"/></svg>
                                            <p>No active notices</p>
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        @endif

                    </div>
                    {{-- End Invoices + Notice --}}

                    @if(isset($featuredProducts) && $featuredProducts->isNotEmpty())
                    <div class="row g-3 mt-1">
                        <div class="col-12">
                            <div class="dash-card mkt-card">
                    
                                {{-- Section header --}}
                                <div class="dash-card__head d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="mkt-header-icon">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none">
                                                <path d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13l-1.5 6h13M7 13H5.4M9 19.5a.5.5 0 110-1 .5.5 0 010 1zm10 0a.5.5 0 110-1 .5.5 0 010 1z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <span style="font-weight:600;font-size:14px;color:#111827;">Marketplace</span>
                                            <span class="mkt-header-badge">New</span>
                                        </div>
                                    </div>
                                    <a href="{{ route('tenant.product.index') }}" class="mkt-view-all">
                                        Browse all
                                        <svg width="12" height="12" viewBox="0 0 16 16" fill="none">
                                            <path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </a>
                                </div>
                    
                                {{-- Nudge banner --}}
                                <div class="mkt-banner">
                                    <div class="mkt-banner__text">
                                        <p class="mkt-banner__title">Shop from your property's marketplace</p>
                                        <p class="mkt-banner__sub">Order these listed items &amp; services and have them delivered to your doorstep.</p>
                                    </div>
                                    <a href="{{ route('tenant.product.index') }}" class="mkt-banner__cta">
                                        Explore now
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none">
                                            <path d="M5 12h14M13 6l6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </a>
                                </div>
                    
                                {{-- Product grid --}}
                                <div class="mkt-grid">
                                    @foreach($featuredProducts as $product)
                                        <a href="{{ route('tenant.product.index') }}" class="mkt-product-card">
                    
                                            {{-- Image / placeholder --}}
                                            <div class="mkt-product-card__img-wrap">
                                                @if($product->first_image_url)
                                                    <img src="{{ $product->first_image_url }}"
                                                        alt="{{ $product->name }}"
                                                        class="mkt-product-card__img"
                                                        loading="lazy">
                                                @else
                                                    <div class="mkt-product-card__img-placeholder">
                                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                                                            <rect x="3" y="3" width="18" height="18" rx="3" stroke="currentColor" stroke-width="1.5"/>
                                                            <circle cx="8.5" cy="8.5" r="1.5" fill="currentColor"/>
                                                            <path d="M21 15l-6-6-4 4-2-2-4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                        </svg>
                                                    </div>
                                                @endif
                    
                                                {{-- Type badge --}}
                                                @if($product->type)
                                                    <span class="mkt-product-card__type-badge">{{ ucfirst($product->type) }}</span>
                                                @endif
                                            </div>
                    
                                            {{-- Info --}}
                                            <div class="mkt-product-card__body">
                                                @if($product->category)
                                                    <p class="mkt-product-card__cat">{{ $product->category }}</p>
                                                @endif
                                                <p class="mkt-product-card__name">{{ Str::limit($product->name, 32) }}</p>
                                                <p class="mkt-product-card__price">{{ currencyPrice($product->price) }}</p>
                                            </div>
                    
                                        </a>
                                    @endforeach
                                </div>
                    
                                {{-- Footer CTA --}}
                                <div class="mkt-footer">
                                    <a href="{{ route('tenant.product.index') }}" class="mkt-footer__link">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none">
                                            <path d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13l-1.5 6h13" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                        View full marketplace &rarr;
                                    </a>
                                </div>
                    
                            </div>
                        </div>
                    </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
    {{-- Notices Modal --}}
    <div id="noticeModal" class="nm-backdrop" onclick="closeNoticeModal(event)" role="dialog" aria-modal="true" aria-labelledby="nmTitle" style="display:none;">
        <div class="nm-modal" id="nmModalPanel">
    
            {{-- Header --}}
            <div class="nm-header" id="nmHeader">
                <div style="flex:1;min-width:0;">
                    <p class="nm-eyebrow">Notice</p>
                    <h4 class="nm-title" id="nmTitle"></h4>
                </div>
                <button type="button" class="nm-close" onclick="closeNoticeModal(null)" aria-label="Close">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none">
                        <path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/>
                    </svg>
                </button>
            </div>
    
            {{-- Body --}}
            <div class="nm-body">
                <div id="nmDetails" class="nm-details"></div>
            </div>
    
            {{-- Footer --}}
            <div class="nm-footer">
                <div class="nm-dates" id="nmDates"></div>
                <button type="button" class="nm-dismiss" id="nmDismissBtn" onclick="closeNoticeModal(null)">
                    {{ __('Dismiss') }}
                </button>
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
    .dash-title    { font-size: 22px; font-weight: 500; color: #111827; margin: 0 0 4px; }
    .dash-subtitle { font-size: 14px; color: #6b7280; margin: 0; }

    /* ── Primary button ──────────────────────────────────────── */
    .theme-btn-primary {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: #185FA5;
        color: #fff;
        padding: 10px 18px;
        border-radius: 8px;
        font-weight: 500;
        font-size: 13px;
        text-decoration: none;
        transition: background .15s, transform .15s, box-shadow .15s;
    }
    .theme-btn-primary:hover {
        background: #0F4A84;
        color: #fff;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(24,95,165,.25);
    }

    /* ── Glance cards ────────────────────────────────────────── */
    .glance-card {
        position: relative;
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 1.25rem 1.4rem;
        border-radius: 14px;
        border: 0.5px solid transparent;
        overflow: hidden;
        height: 100%;
        transition: transform .18s, box-shadow .18s;
    }
    .glance-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(0,0,0,.08);
    }
    .glance-card--coral { background: #FDF3F0; border-color: #F5C4B3; color: #7A2C10; }
    .glance-card--blue  { background: #F0F7FD; border-color: #B3D5F5; color: #0F3C7A; }
    .glance-card--green { background: #F0FBF6; border-color: #9FE1CB; color: #0B5940; }
    .glance-card--amber { background: #FDF6EC; border-color: #F5D9A8; color: #6B3E08; }

    .glance-card__icon-wrap {
        flex-shrink: 0;
        width: 46px;
        height: 46px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .glance-card--blue  .glance-card__icon-wrap { background: #E6F1FB; }
    .glance-card--coral .glance-card__icon-wrap { background: #FAECE7; }
    .glance-card--green .glance-card__icon-wrap { background: #E1F5EE; }
    .glance-card--amber .glance-card__icon-wrap { background: #FAEEDA; }

    .glance-card__body  { flex: 1; min-width: 0; position: relative; z-index: 1; }
    .glance-card__label {
        font-size: 11px;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: .07em;
        opacity: .65;
        margin: 0 0 3px;
    }
    .glance-card__value {
        font-size: 20px;
        font-weight: 600;
        margin: 0 0 2px;
        line-height: 1.2;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .glance-card__sub {
        font-size: 11px;
        opacity: .55;
        margin: 0;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .glance-card__deco {
        position: absolute;
        right: -10px;
        top: -10px;
        width: 90px;
        height: 90px;
        pointer-events: none;
    }
    .glance-card__deco svg { width: 100%; height: 100%; }
    .glance-card--coral .glance-card__deco { color: #993C1D; }
    .glance-card--blue  .glance-card__deco { color: #185FA5; }
    .glance-card--green .glance-card__deco { color: #1D9E75; }
    .glance-card--amber .glance-card__deco { color: #854F0B; }

    /* ── Shared dash card ────────────────────────────────────── */
    .dash-card {
        background: #fff;
        border: 0.5px solid #e5e7eb;
        border-radius: 12px;
        overflow: hidden;
    }
    .dash-card__head {
        padding: .85rem 1.25rem;
        border-bottom: 0.5px solid #e5e7eb;
        background: #fafafa;
    }

    /* ── Invoice tabs ────────────────────────────────────────── */
    .inv-tabs {
        display: flex;
        background: #f3f4f6;
        border-radius: 8px;
        padding: 3px;
        gap: 2px;
    }
    .inv-tab {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: transparent;
        border: none;
        font-size: 12px;
        font-weight: 500;
        color: #6b7280;
        padding: 4px 14px;
        border-radius: 6px;
        cursor: pointer;
        transition: background .15s, color .15s;
    }
    .inv-tab--active {
        background: #fff;
        color: #111827;
        box-shadow: 0 1px 3px rgba(0,0,0,.08);
    }
    .inv-tab__dot { width: 6px; height: 6px; border-radius: 50%; flex-shrink: 0; }
    .inv-tab__dot--paid   { background: #1D9E75; }
    .inv-tab__dot--unpaid { background: #854F0B; }

    /* ── Invoice rows ────────────────────────────────────────── */
    .inv-panel { display: flex; flex-direction: column; gap: 8px; }

    .inv-row {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 11px 14px;
        border-radius: 10px;
        background: #fafafa;
        border: 0.5px solid #f3f4f6;
        transition: background .15s, border-color .15s;
    }
    .inv-row:hover          { background: #f3f4f6; border-color: #e5e7eb; }
    .inv-row--overdue       { background: #FDF3F0; border-color: #F5C4B3; }
    .inv-row--overdue:hover { background: #FAECE7; }

    .inv-row__icon {
        width: 34px; height: 34px;
        border-radius: 9px;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .inv-row__icon--paid    { background: #E1F5EE; color: #0F6E56; }
    .inv-row__icon--unpaid  { background: #FAEEDA; color: #854F0B; }
    .inv-row__icon--overdue { background: #FAECE7; color: #993C1D; }

    .inv-row__meta { flex: 1; min-width: 0; }
    .inv-row__ref {
        font-size: 13px;
        font-weight: 500;
        color: #111827;
        margin: 0 0 2px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* ── Item types label ────────────────────────────────────── */
    .inv-row__types {
        font-size: 11px;
        color: #185FA5;
        background: #E6F1FB;
        display: inline-block;
        padding: 1px 7px;
        border-radius: 4px;
        margin: 0 0 3px;
        font-weight: 500;
    }

    .inv-row__date { font-size: 11px; color: #9ca3af; margin: 0; }

    .inv-row__amount { font-size: 13px; font-weight: 600; margin: 0 0 3px; }
    .inv-row__amount--paid    { color: #0F6E56; }
    .inv-row__amount--unpaid  { color: #854F0B; }
    .inv-row__amount--overdue { color: #993C1D; }

    .inv-row__num { font-size: 10px; color: #9ca3af; font-family: monospace; letter-spacing: .02em; }
    .inv-row__arrow { flex-shrink: 0; color: #999; }

    .inv-empty {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 2.5rem 1rem;
        color: #9ca3af;
        font-size: 13px;
        text-align: center;
    }
    .inv-empty p { margin: 0; }

    /* ── View all link ─────────────────────────────────────────── */
    .inv-view-all {
        margin-top: 12px;
        padding-top: 12px;
        border-top: 0.5px solid #e5e7eb;
        text-align: center;
    }

    .view-all-link {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 13px;
        font-weight: 500;
        color: #185FA5;
        text-decoration: none;
        padding: 6px 12px;
        border-radius: 6px;
        transition: background 0.15s, color 0.15s;
    }

    .view-all-link:hover {
        background: #E6F1FB;
        color: #0F4A84;
    }

    .view-all-link svg {
        transition: transform 0.15s;
    }

    .view-all-link:hover svg {
        transform: translateX(2px);
    }

    /* ── Notice board ────────────────────────────────────────── */
    .notice-item {
        display: flex;
        align-items: stretch;
        border-bottom: 0.5px solid #f3f4f6;
        transition: background .15s;
    }
    .notice-item:last-child  { border-bottom: none; }
    .notice-item:hover       { background: #fafafa; }

    .notice-item__accent {
        width: 3px;
        flex-shrink: 0;
        background: #185FA5;
    }
    .notice-item__body { padding: .9rem 1.25rem; flex: 1; }
    .notice-item__title {
        font-size: 13px;
        font-weight: 500;
        color: #111827;
        margin: 0 0 4px;
        line-height: 1.4;
    }
    .notice-item__meta { font-size: 11px; color: #9ca3af; margin: 0; }

    .notice-empty {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 2.5rem 1rem;
        color: #9ca3af;
        font-size: 13px;
        text-align: center;
    }
    .notice-empty p { margin: 0; }

    /* ── Theme link ──────────────────────────────────────────── */
    .theme-link { color: #185FA5; font-weight: 500; transition: color .15s; }
    .theme-link:hover { color: #0F4A84; }

    /* ── Responsive ──────────────────────────────────────────── */
    @media (max-width: 767px) {
        .glance-card            { padding: 1rem 1.1rem; }
        .glance-card__value     { font-size: 18px; }
        .glance-card__icon-wrap { width: 40px; height: 40px; }
    }

    /* ── Notice item: coloured card ─────────────────────────── */
    .notice-item--btn {
        width: 100%;
        border: none;
        text-align: left;
        cursor: pointer;
        padding: 0;
        display: flex;
        align-items: center;
        border-radius: 10px;
        overflow: hidden;
        transition: filter .15s, transform .15s;
        margin-bottom: 8px;
    }
    .notice-item--btn:last-of-type { margin-bottom: 0; }
    .notice-item--btn:hover {
        filter: brightness(.96);
        transform: translateY(-1px);
    }
    
    /* Override the original .notice-item divider since cards are self-contained */
    .notice-item--btn { border-bottom: none !important; }
    
    .notice-item__accent {
        width: 4px;
        align-self: stretch;
        flex-shrink: 0;
    }
    
    .notice-item__icon-wrap {
        flex-shrink: 0;
        width: 30px;
        height: 30px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 12px 0 12px 12px;
    }
    
    .notice-item__body {
        padding: 12px 10px;
        flex: 1;
        min-width: 0;
    }
    
    .notice-item__title {
        font-size: 13px;
        font-weight: 600;
        margin: 0 0 3px;
        line-height: 1.35;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    .notice-item__meta {
        font-size: 11px;
        font-weight: 500;
        margin: 0;
    }
    
    /* ── Notice modal backdrop ──────────────────────────────── */
    .nm-backdrop {
        position: fixed;
        inset: 0;
        background: rgba(17,24,39,.45);
        backdrop-filter: blur(2px);
        -webkit-backdrop-filter: blur(2px);
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 16px;
        animation: nmFadeIn .18s ease;
    }
    @keyframes nmFadeIn {
        from { opacity: 0; }
        to   { opacity: 1; }
    }
    
    /* ── Modal panel ────────────────────────────────────────── */
    .nm-modal {
        background: #fff;
        border-radius: 16px;
        width: 100%;
        max-width: 480px;
        max-height: 80vh;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        box-shadow:
            0 20px 60px rgba(0,0,0,.18),
            0 0 0 1px rgba(24,95,165,.08);
        animation: nmSlideIn .2s ease;
    }
    @keyframes nmSlideIn {
        from { transform: translateY(14px) scale(.97); opacity: 0; }
        to   { transform: translateY(0)    scale(1);   opacity: 1; }
    }
    
    /* ── Modal header ───────────────────────────────────────── */
    .nm-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
        padding: 18px 20px 14px;
        border-bottom: 0.5px solid #e5e7eb;
        flex-shrink: 0;
        /* bg and border-left injected by JS per notice colour */
    }
    .nm-eyebrow {
        font-size: 10px;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: .07em;
        color: #9ca3af;
        margin: 0 0 4px;
    }
    .nm-title {
        font-size: 15px;
        font-weight: 600;
        color: #111827;
        margin: 0;
        line-height: 1.35;
    }
    .nm-close {
        flex-shrink: 0;
        width: 30px;
        height: 30px;
        border-radius: 7px;
        background: rgba(0,0,0,.06);
        border: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #6b7280;
        transition: background .13s, color .13s;
        margin-top: 2px;
    }
    .nm-close:hover { background: rgba(0,0,0,.12); color: #111827; }
    
    /* ── Modal body ─────────────────────────────────────────── */
    .nm-body {
        padding: 20px;
        overflow-y: auto;
        flex: 1;
    }
    .nm-details {
        font-size: 14px;
        color: #374151;
        line-height: 1.75;
        white-space: pre-wrap;
    }
    .nm-details:empty::after {
        content: 'No additional details provided.';
        color: #9ca3af;
        font-style: italic;
    }
    
    /* ── Modal footer ───────────────────────────────────────── */
    .nm-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 14px 20px;
        background: #fafafa;
        border-top: 0.5px solid #e5e7eb;
        flex-shrink: 0;
        gap: 12px;
        flex-wrap: wrap;
    }
    .nm-dates {
        font-size: 11px;
        color: #9ca3af;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .nm-dismiss {
        display: inline-flex;
        align-items: center;
        background: #185FA5;
        color: #fff;
        border: none;
        border-radius: 7px;
        padding: 8px 20px;
        font-size: 13px;
        font-weight: 500;
        cursor: pointer;
        transition: background .13s, transform .13s;
    }
    .nm-dismiss:hover {
        background: #0F4A84;
        transform: translateY(-1px);
    }
    
    /* ── Mobile: centre-anchored sheet with safe bottom gap ─── */
    @media (max-width: 540px) {
        .nm-backdrop {
            align-items: flex-end;
            padding: 0;
        }
        .nm-modal {
            max-height: 78vh;          /* leaves ~22vh of backdrop visible above */
            border-radius: 16px 16px 0 0;
            /* Respect device safe-area (notched phones) */
            padding-bottom: env(safe-area-inset-bottom, 0px);
        }
        .nm-footer {
            /* Extra bottom padding so dismiss isn't flush with home bar */
            padding-bottom: max(16px, env(safe-area-inset-bottom, 16px));
        }
    }
    /* ── Marketplace card wrapper ───────────────────────────── */
    .mkt-card { overflow: visible; }  /* allow badge to overflow if needed */
    
    /* ── Header icon bubble ─────────────────────────────────── */
    .mkt-header-icon {
        width: 28px; height: 28px;
        border-radius: 7px;
        background: #E6F1FB;
        color: #185FA5;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    
    /* ── "New" badge ────────────────────────────────────────── */
    .mkt-header-badge {
        display: inline-flex;
        align-items: center;
        font-size: 10px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .06em;
        background: #534AB7;
        color: #fff;
        padding: 2px 7px;
        border-radius: 99px;
        margin-left: 6px;
        vertical-align: middle;
    }
    
    /* ── View all link ──────────────────────────────────────── */
    .mkt-view-all {
        display: inline-flex; align-items: center; gap: 5px;
        font-size: 12px; font-weight: 500;
        color: #185FA5; text-decoration: none;
        transition: color .13s;
    }
    .mkt-view-all:hover { color: #0F4A84; }
    
    /* ── Nudge banner ───────────────────────────────────────── */
    .mkt-banner {
        margin: 15px 20px 20px;
        padding: 16px 20px;
        background: linear-gradient(135deg, #185FA5 0%, #534AB7 100%);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        flex-wrap: wrap;
    }
    .mkt-banner__text { flex: 1; min-width: 0; }
    .mkt-banner__title {
        font-size: 14px; font-weight: 600;
        color: #fff; margin: 0 0 3px;
    }
    .mkt-banner__sub {
        font-size: 12px; color: rgba(255,255,255,.75); margin: 0;
    }
    .mkt-banner__cta {
        display: inline-flex; align-items: center; gap: 6px;
        background: #fff; color: #185FA5;
        font-size: 12px; font-weight: 600;
        padding: 8px 16px; border-radius: 8px;
        text-decoration: none; white-space: nowrap; flex-shrink: 0;
        transition: background .13s, transform .13s;
    }
    .mkt-banner__cta:hover {
        background: #f0f7fd; color: #0F4A84;
        transform: translateY(-1px);
    }
    
    /* ── Product grid ───────────────────────────────────────── */
    .mkt-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 14px;
        padding: 0 20px 0;
    }
    
    /* ── Product card ───────────────────────────────────────── */
    .mkt-product-card {
        border: 0.5px solid #e5e7eb;
        border-radius: 12px;
        overflow: hidden;
        text-decoration: none;
        background: #fff;
        display: flex;
        flex-direction: column;
        transition: transform .18s, box-shadow .18s, border-color .18s;
    }
    .mkt-product-card:hover {
        transform: translateY(-3px);
        border-color: #185FA5;
        box-shadow:
            0 8px 24px rgba(0,0,0,.07),
            0 0 0 1px rgba(24,95,165,.12);
    }
    
    /* Image area */
    .mkt-product-card__img-wrap {
        position: relative;
        width: 100%; aspect-ratio: 4/3;
        background: #f3f4f6;
        overflow: hidden;
    }
    .mkt-product-card__img {
        width: 100%; height: 100%;
        object-fit: cover;
        transition: transform .25s;
    }
    .mkt-product-card:hover .mkt-product-card__img { transform: scale(1.04); }
    
    .mkt-product-card__img-placeholder {
        width: 100%; height: 100%;
        display: flex; align-items: center; justify-content: center;
        color: #d1d5db;
    }
    
    /* Type badge on image */
    .mkt-product-card__type-badge {
        position: absolute; top: 8px; left: 8px;
        font-size: 10px; font-weight: 600;
        text-transform: uppercase; letter-spacing: .05em;
        background: rgba(255,255,255,.92);
        color: #374151;
        padding: 2px 7px; border-radius: 99px;
        backdrop-filter: blur(4px);
    }
    
    /* Card body */
    .mkt-product-card__body {
        padding: 10px 12px 12px;
        flex: 1; display: flex; flex-direction: column; gap: 2px;
    }
    .mkt-product-card__cat {
        font-size: 10px; font-weight: 500;
        text-transform: uppercase; letter-spacing: .07em;
        color: #9ca3af; margin: 0;
    }
    .mkt-product-card__name {
        font-size: 13px; font-weight: 600;
        color: #111827; margin: 0;
        line-height: 1.35;
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
    .mkt-product-card__price {
        font-size: 13px; font-weight: 700;
        color: #185FA5; margin: 4px 0 0;
    }
    
    /* ── Footer CTA ─────────────────────────────────────────── */
    .mkt-footer {
        margin-top: 16px;
        padding: 14px 20px;
        border-top: 0.5px solid #e5e7eb;
        background: #fafafa;
        text-align: center;
    }
    .mkt-footer__link {
        display: inline-flex; align-items: center; gap: 6px;
        font-size: 13px; font-weight: 500;
        color: #185FA5; text-decoration: none;
        transition: color .13s;
    }
    .mkt-footer__link:hover { color: #0F4A84; }
    
    /* ── Responsive ─────────────────────────────────────────── */
    @media (max-width: 1024px) {
        .mkt-grid { grid-template-columns: repeat(3, 1fr); }
    }
    @media (max-width: 768px) {
        .mkt-grid { grid-template-columns: repeat(2, 1fr); gap: 10px; }
        .mkt-banner { padding: 14px 16px; }
    }
    @media (max-width: 480px) {
        .mkt-grid { grid-template-columns: repeat(2, 1fr); gap: 8px; padding: 0 14px 0; }
        .mkt-banner { flex-direction: column; align-items: flex-start; gap: 12px; }
        .mkt-banner__cta { width: 100%; justify-content: center; }
        .mkt-product-card__name { font-size: 12px; }
    }
</style>

<script>
    function switchTab(btn, tab) {
        document.querySelectorAll('.inv-tab').forEach(t => t.classList.remove('inv-tab--active'));
        document.querySelectorAll('.inv-panel').forEach(p => p.style.display = 'none');
        btn.classList.add('inv-tab--active');
        document.getElementById('tab-' + tab).style.display = 'flex';
    }

    function openNoticeModal(title, details, startDate, endDate, headerBg, accentColor) {
        document.getElementById('nmTitle').textContent   = title;
        document.getElementById('nmDetails').textContent = details || '';
    
        // Tint the modal header to match the notice card colour
        const header = document.getElementById('nmHeader');
        header.style.background   = headerBg || '#fafafa';
        header.style.borderLeft   = '4px solid ' + (accentColor || '#185FA5');
    
        // Tint the dismiss button to match
        document.getElementById('nmDismissBtn').style.background = accentColor || '#185FA5';
    
        document.getElementById('nmDates').innerHTML =
            '<svg width="11" height="11" viewBox="0 0 16 16" fill="none" style="display:inline;vertical-align:middle;">' +
                '<rect x="2" y="3" width="12" height="11" rx="2" stroke="currentColor" stroke-width="1.4"/>' +
                '<path d="M5 2v2M11 2v2M2 7h12" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>' +
            '</svg>&nbsp;' + startDate + ' &mdash; ' + endDate;
    
        const modal = document.getElementById('noticeModal');
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
        modal.querySelector('.nm-close').focus();
    }
    
    function closeNoticeModal(event) {
        if (event && event.target !== document.getElementById('noticeModal')) return;
        document.getElementById('noticeModal').style.display = 'none';
        document.body.style.overflow = '';
    }
 
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeNoticeModal(null);
    });
</script>
@endsection