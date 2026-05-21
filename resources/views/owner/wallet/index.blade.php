@extends('owner.layouts.app')

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="page-content-wrapper bg-white p-30 radius-20">
                <div class="container">
                    @php
                        $pageTitle = 'Wallet';
                    @endphp
                    {{-- ── Page Header ── --}}
                    <div class="wl-header">
                        <div>
                            <nav aria-label="breadcrumb">
                                <ol class="wl-breadcrumb">
                                    <li>
                                        <a href="{{ route('owner.dashboard') }}">
                                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                            {{ __('Dashboard') }}
                                        </a>
                                    </li>
                                    <li aria-current="page">{{ __('Wallet') }}</li>
                                </ol>
                            </nav>
                            <h1 class="wl-page-title">{{ __('My Wallet') }}</h1>
                            <p class="wl-page-sub">{{ __('Earnings from marketplace sales and rent after platform deductions') }}</p>
                        </div>
                    </div>

                    {{-- ── Stat Cards ── --}}
                    <div class="wl-stats">
                        <div class="wl-stat wl-stat--blue">
                            <div class="wl-stat__top">
                                <div class="wl-stat__icon wl-stat__icon--blue">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none"><rect x="1" y="4" width="22" height="16" rx="3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" stroke="currentColor" stroke-width="1.8"/></svg>
                                </div>
                                <span class="wl-stat__label">{{ __('Available Balance') }}</span>
                            </div>
                            <p class="wl-stat__value">KSh {{ number_format($wallet->balance, 2) }}</p>
                            <div class="wl-stat__bar wl-stat__bar--blue"></div>
                        </div>
                        <div class="wl-stat wl-stat--green">
                            <div class="wl-stat__top">
                                <div class="wl-stat__icon wl-stat__icon--green">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><polyline points="17 6 23 6 23 12" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </div>
                                <span class="wl-stat__label">{{ __('Total Earned (Net)') }}</span>
                            </div>
                            <p class="wl-stat__value">KSh {{ number_format($totalEarned, 2) }}</p>
                            <div class="wl-stat__bar wl-stat__bar--green"></div>
                        </div>
                        <div class="wl-stat wl-stat--amber">
                            <div class="wl-stat__top">
                                <div class="wl-stat__icon wl-stat__icon--amber">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.8"/><path d="M15 9H9l2 3-2 3h6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </div>
                                <span class="wl-stat__label">{{ __('Commission Paid') }}</span>
                            </div>
                            <p class="wl-stat__value">KSh {{ number_format($totalCommission, 2) }}</p>
                            <div class="wl-stat__bar wl-stat__bar--amber"></div>
                        </div>
                        <div class="wl-stat wl-stat--purple">
                            <div class="wl-stat__top">
                                <div class="wl-stat__icon wl-stat__icon--purple">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none"><path d="M12 5v14M5 12l7 7 7-7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </div>
                                <span class="wl-stat__label">{{ __('Total Withdrawn') }}</span>
                            </div>
                            <p class="wl-stat__value">KSh {{ number_format($totalWithdrawn, 2) }}</p>
                            <div class="wl-stat__bar wl-stat__bar--purple"></div>
                        </div>
                    </div>

                    {{-- ── Withdraw Action Row ── --}}
                    <div class="wl-action-row">
                        <button type="button"
                                class="wl-withdraw-btn"
                                id="openWithdrawModal"
                                data-balance="{{ $wallet->balance }}">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M12 5v14M5 12l7 7 7-7" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            {{ __('Withdraw to M-Pesa') }}
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none"><path d="M5 12h14M12 5l7 7-7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </button>
                        <div class="wl-action-hint">
                            <span class="wl-action-hint__label">{{ __('Available') }}</span>
                            <span class="wl-action-hint__value">KSh {{ number_format($wallet->balance, 2) }}</span>
                        </div>
                    </div>

                    {{-- ── Transactions Panel with Tabs ── --}}
                    <div class="wl-panel">

                        <div class="wl-panel__head">
                            <div class="wl-panel__head-left">
                                <div class="wl-panel__icon">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><polyline points="14 2 14 8 20 8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </div>
                                <h4 class="wl-panel__title">{{ __('Transaction History') }}</h4>
                            </div>
                            <div class="wl-filter">
                                <div class="wl-filter__wrap">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    <select id="typeFilter" class="wl-filter__select">
                                        <option value="">{{ __('All Types') }}</option>
                                        <option value="credit">{{ __('Credit') }}</option>
                                        <option value="debit">{{ __('Debit') }}</option>
                                        <option value="withdrawal">{{ __('Withdrawal') }}</option>
                                        <option value="refund">{{ __('Refund') }}</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- ── Tabs ── --}}
                        <div class="wl-tabs">
                            <button class="wl-tab wl-tab--active" data-tab="marketplace">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none">
                                    <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4zM3 6h18"
                                        stroke="currentColor" stroke-width="1.8"
                                        stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                {{ __('Marketplace Sales') }}
                                <span class="wl-tab__count">{{ $marketplaceTransactions->total() }}</span>
                            </button>

                            @if($isTransactionModel)
                                <button class="wl-tab" data-tab="rent">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none">
                                        <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"
                                            stroke="currentColor" stroke-width="1.8"
                                            stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    {{ __('Rent & Other Payments') }}
                                    <span class="wl-tab__count">{{ $rentTransactions->total() }}</span>
                                </button>
                            @endif
                        </div>

                        {{-- ── Marketplace Tab ── --}}
                        <div id="tab-marketplace" class="wl-tab-panel">
                            <div class="table-responsive">
                                <table class="wl-table" id="mpTable">
                                    <thead>
                                        <tr>
                                            <th>{{ __('Date') }}</th>
                                            <th>{{ __('Description') }}</th>
                                            <th>{{ __('Gross') }}</th>
                                            <th>{{ __('Platform Fee') }}</th>
                                            <th>{{ __('Net') }}</th>
                                            <th>{{ __('Type') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($marketplaceTransactions as $txn)
                                        <tr data-type="{{ $txn->type }}">
                                            <td class="wl-td-date">{{ $txn->created_at->format('d M Y, H:i') }}</td>
                                            <td class="wl-td-desc">{{ $txn->description }}</td>
                                            <td class="wl-td-amount">
                                                @if($txn->gross_amount) KSh {{ number_format($txn->gross_amount, 2) }}
                                                @else <span class="wl-na">—</span>
                                                @endif
                                            </td>
                                            <td class="wl-td-amount wl-td-amount--commission">
                                                @if($txn->commission_amount)
                                                    <span class="wl-rate-pill">{{ number_format($txn->commission_rate, 1) }}%</span>
                                                    KSh {{ number_format($txn->commission_amount, 2) }}
                                                @else <span class="wl-na">—</span>
                                                @endif
                                            </td>
                                            <td class="wl-td-amount wl-td-amount--net">KSh {{ number_format($txn->net_amount, 2) }}</td>
                                            <td>
                                                <span class="wl-badge wl-badge--{{ $txn->type }}">
                                                    <span class="wl-badge__dot"></span>{{ ucfirst($txn->type) }}
                                                </span>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="6" class="wl-empty">
                                                <div class="wl-empty__icon"><svg width="28" height="28" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.5"/></svg></div>
                                                <p>{{ __('No marketplace transactions yet.') }}</p>
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            @if($marketplaceTransactions->hasPages())
                            <div class="wl-pagination">{{ $marketplaceTransactions->appends(['rent_page' => $rentTransactions->currentPage()])->links() }}</div>
                            @endif
                        </div>

                        {{-- ── Rent Tab ── --}}
                        @if($isTransactionModel)
                            <div id="tab-rent" class="wl-tab-panel" style="display:none;">
                                <div class="table-responsive">
                                    <table class="wl-table" id="rentTable">
                                        <thead>
                                            <tr>
                                                <th>{{ __('Date') }}</th>
                                                <th>{{ __('Description') }}</th>
                                                <th>{{ __('Gross') }}</th>
                                                <th>{{ __('Platform Fee (1%)') }}</th>
                                                <th>{{ __('Net') }}</th>
                                                <th>{{ __('Type') }}</th>
                                                <th>{{ __('Detail') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($rentTransactions as $txn)
                                            <tr data-type="{{ $txn->type }}">
                                                <td class="wl-td-date">{{ $txn->created_at->format('d M Y, H:i') }}</td>
                                                <td class="wl-td-desc">{{ $txn->description }}</td>
                                                <td class="wl-td-amount">
                                                    @if($txn->gross_amount) KSh {{ number_format($txn->gross_amount, 2) }}
                                                    @else <span class="wl-na">—</span>
                                                    @endif
                                                </td>
                                                <td class="wl-td-amount wl-td-amount--commission">
                                                    @if($txn->commission_amount)
                                                        <span class="wl-rate-pill">1%</span>
                                                        KSh {{ number_format($txn->commission_amount, 2) }}
                                                    @else <span class="wl-na">—</span>
                                                    @endif
                                                </td>
                                                <td class="wl-td-amount wl-td-amount--net">KSh {{ number_format($txn->net_amount, 2) }}</td>
                                                <td>
                                                    <span class="wl-badge wl-badge--{{ $txn->type }}">
                                                        <span class="wl-badge__dot"></span>{{ ucfirst($txn->type) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    {{-- Only show view button for credit rows that have an invoice order --}}
                                                    @if($txn->invoice_order_id)
                                                    <button type="button"
                                                            class="rd-view-btn"
                                                            data-url="{{ route('owner.wallet.rent.transaction', $txn->id) }}"
                                                            title="{{ __('View Payment Detail') }}">
                                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"/></svg>
                                                        {{ __('View') }}
                                                    </button>
                                                    @else
                                                    <span class="wl-na">—</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="7" class="wl-empty">
                                                    <div class="wl-empty__icon"><svg width="28" height="28" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.5"/></svg></div>
                                                    <p>{{ __('No rent transactions yet.') }}</p>
                                                </td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                                @if($rentTransactions->hasPages())
                                <div class="wl-pagination">{{ $rentTransactions->appends(['mp_page' => $marketplaceTransactions->currentPage()])->links() }}</div>
                                @endif
                            </div>
                        @endif

                    </div>{{-- /.wl-panel --}}

                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── Withdraw Modal ── --}}
<div id="withdrawModal"
     class="wd-overlay"
     data-state="closed"
     aria-modal="true"
     role="dialog">
    <div class="wd-box">
        <div class="wd-box__head">
            <div class="wd-box__icon">
                <img src="{{ asset('assets/images/gateway-icon/mpesa.jpg') }}" alt="M-Pesa"
                     style="width:36px;height:36px;border-radius:8px;object-fit:cover;">
            </div>
            <div>
                <p class="wd-box__eyebrow">{{ __('Payout') }}</p>
                <h5 class="wd-box__title">{{ __('Withdraw via M-Pesa') }}</h5>
                <p class="wd-box__sub">{{ __('Funds will be sent directly to your Safaricom number.') }}</p>
            </div>
            <button type="button" class="wd-box__close" id="closeWithdrawModal">&times;</button>
        </div>
        <div class="wd-box__body">
            <div class="wd-balance-row">
                <span>{{ __('Available balance') }}</span>
                <strong>KSh <span id="modalBalance">{{ number_format($wallet->balance, 2) }}</span></strong>
            </div>
            <form id="withdrawForm" action="{{ route('owner.wallet.withdraw') }}" method="POST">
                @csrf
                <div class="wd-field">
                    <label class="wd-label">{{ __('M-Pesa Phone Number') }}</label>
                    <div class="wd-phone-wrap">
                        <span class="wd-phone-prefix">+254</span>
                        <input type="text" name="phone" id="withdrawPhone" class="wd-input"
                               placeholder="7XX XXX XXX"
                               value="{{ auth()->user()->phone ? ltrim(auth()->user()->phone, '+254') : '' }}"
                               maxlength="9" required>
                    </div>
                    <p class="wd-hint">{{ __('Enter the number without country code. e.g. 712345678') }}</p>
                </div>
                <div class="wd-field">
                    <label class="wd-label">{{ __('Amount (KSh)') }}</label>
                    <input type="number" name="amount" id="withdrawAmount" class="wd-input"
                           placeholder="0.00" min="1" step="0.01"
                           max="{{ $wallet->balance }}" required>
                    <p class="wd-hint">{{ __('Minimum withdrawal: KSh 1.00') }}</p>
                </div>
                <div class="wd-warning">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" style="flex-shrink:0;margin-top:1px"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><line x1="12" y1="9" x2="12" y2="13" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><line x1="12" y1="17" x2="12.01" y2="17" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                    <span>{{ __('Withdrawals are processed within a few minutes. Ensure the number is correct — this cannot be reversed.') }}</span>
                </div>
                <button type="submit" class="wd-submit-btn" id="withdrawSubmitBtn">
                    <img src="{{ asset('assets/images/gateway-icon/mpesa.jpg') }}" alt=""
                         style="width:20px;height:20px;border-radius:4px;object-fit:cover;">
                    {{ __('Send to M-Pesa') }}
                </button>
            </form>
        </div>
    </div>
</div>

{{-- ── Rent Detail Modal (rd-) ─────────────────────────────────────────────
     Outside .main-content — same pattern as withdraw modal.
     Populated via AJAX from owner.wallet.rent.transaction route.
──────────────────────────────────────────────────────────────────────────── --}}
<div id="rentDetailModal"
     class="rd-overlay"
     data-state="closed"
     aria-modal="true"
     role="dialog">
    <div class="rd-box">

        {{-- Header --}}
        <div class="rd-box__head">
            <div class="rd-box__head-left">
                <div class="rd-box__icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><polyline points="9 22 9 12 15 12 15 22" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </div>
                <div>
                    <p class="rd-box__eyebrow">{{ __('Transaction Details') }}</p>
                    <h5 class="rd-box__title" id="rdInvoiceNo">—</h5>
                </div>
            </div>
            <button type="button" class="rd-box__close" id="closeRentDetailModal">&times;</button>
        </div>

        {{-- Loading state --}}
        <div id="rdLoading" class="rd-loading">
            <div class="rd-loading__spinner"></div>
            <p>{{ __('Loading payment details…') }}</p>
        </div>

        {{-- Content — hidden until loaded --}}
        <div id="rdContent" class="rd-box__body" style="display:none;">

            {{-- Tenant + Property row --}}
            <div class="rd-parties">
                <div class="rd-party">
                    <div class="rd-party__avatar" id="rdTenantAvatar">T</div>
                    <div>
                        <p class="rd-party__label">{{ __('Tenant') }}</p>
                        <p class="rd-party__name" id="rdTenantName">—</p>
                        <p class="rd-party__sub" id="rdTenantEmail">—</p>
                    </div>
                </div>
                <div class="rd-party-divider">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none"><path d="M5 12h14M12 5l7 7-7 7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </div>
                <div class="rd-party">
                    <div class="rd-party__avatar rd-party__avatar--green" id="rdUnitAvatar">U</div>
                    <div>
                        <p class="rd-party__label">{{ __('Property Unit') }}</p>
                        <p class="rd-party__name" id="rdUnitName">—</p>
                        <p class="rd-party__sub" id="rdPropertyName">—</p>
                    </div>
                </div>
            </div>

            {{-- Invoice meta --}}
            <div class="rd-meta-grid">
                <div class="rd-meta-item">
                    <span class="rd-meta-label">{{ __('Invoice No.') }}</span>
                    <span class="rd-meta-value" id="rdInvoiceNoDetail">—</span>
                </div>
                <div class="rd-meta-item">
                    <span class="rd-meta-label">{{ __('Invoice Type') }}</span>
                    <span class="rd-meta-value" id="rdInvoiceType">—</span>
                </div>
                <div class="rd-meta-item">
                    <span class="rd-meta-label">{{ __('Issue Date') }}</span>
                    <span class="rd-meta-value" id="rdIssueDate">—</span>
                </div>
                <div class="rd-meta-item">
                    <span class="rd-meta-label">{{ __('Due Date') }}</span>
                    <span class="rd-meta-value" id="rdDueDate">—</span>
                </div>
                <div class="rd-meta-item">
                    <span class="rd-meta-label">{{ __('Paid On') }}</span>
                    <span class="rd-meta-value" id="rdPaidOn">—</span>
                </div>
                <div class="rd-meta-item">
                    <span class="rd-meta-label">{{ __('Payment Method') }}</span>
                    <span class="rd-meta-value" id="rdPaymentMethod">—</span>
                </div>
            </div>

            {{-- Amount breakdown --}}
            <div class="rd-breakdown">
                <div class="rd-breakdown__head">{{ __('Payment Breakdown') }}</div>
                <div class="rd-breakdown__row">
                    <span>{{ __('Amount Paid') }}</span>
                    <span id="rdGross" class="rd-breakdown__val">—</span>
                </div>
                <div class="rd-breakdown__row rd-breakdown__row--commission">
                    <span>
                        {{ __('Platform Fee') }}
                        <span class="rd-rate-chip" id="rdRateChip">1%</span>
                    </span>
                    <span id="rdCommission" class="rd-breakdown__val rd-breakdown__val--deduct">—</span>
                </div>
                <div class="rd-breakdown__row rd-breakdown__row--net">
                    <span>{{ __('Your Net Earnings') }}</span>
                    <span id="rdNet" class="rd-breakdown__val rd-breakdown__val--net">—</span>
                </div>
            </div>

        </div>{{-- /#rdContent --}}

        {{-- Error state --}}
        <div id="rdError" class="rd-error" style="display:none;">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.5"/><line x1="12" y1="8" x2="12" y2="12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><line x1="12" y1="16" x2="12.01" y2="16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
            <p id="rdErrorMsg">{{ __('Could not load payment details.') }}</p>
        </div>

    </div>
</div>

@endsection

@push('style')
<style>
:root{--blue:#185FA5;--blue-hover:#0F4A84;--blue-light:#E6F1FB;--blue-border:#B5D4F4;--blue-faint:#185ea56e;--green:#1D9E75;--green-dark:#0F6E56;--green-light:#E1F5EE;--amber:#854F0B;--amber-light:#FAEEDA;--amber-border:#F5D9A8;--red:#993C1D;--red-light:#FAECE7;--purple:#534AB7;--gray-900:#111827;--gray-700:#374151;--gray-500:#6b7280;--gray-400:#9ca3af;--gray-200:#e5e7eb;--gray-100:#f3f4f6;--gray-50:#fafafa;--white:#ffffff}
.wl-header{display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:1rem;margin-bottom:24px}
.wl-breadcrumb{display:flex;align-items:center;gap:5px;list-style:none;padding:0;margin:0 0 8px;font-size:12px;color:var(--gray-400)}
.wl-breadcrumb li:not(:last-child)::after{content:'';display:inline-block;width:5px;height:5px;border-right:1.5px solid #d1d5db;border-top:1.5px solid #d1d5db;transform:rotate(45deg);margin-left:5px;opacity:.5}
.wl-breadcrumb a{display:inline-flex;align-items:center;gap:4px;color:var(--blue);text-decoration:none;font-weight:500}
.wl-page-title{font-size:22px;font-weight:500;color:var(--gray-900);margin:0 0 4px}
.wl-page-sub{font-size:13px;color:var(--gray-500);margin:0}
.wl-stats{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:20px}
.wl-stat{background:var(--white);border:0.5px solid var(--blue-faint);border-radius:14px;padding:20px;display:flex;flex-direction:column;gap:12px;position:relative;overflow:hidden;transition:all .25s ease;box-shadow:0 4px 12px rgba(0,0,0,.04),0 0 0 1px rgba(24,95,165,.05),0 6px 18px rgba(24,95,165,.06)}
.wl-stat:hover{border-color:var(--blue);transform:translateY(-3px);box-shadow:0 10px 25px rgba(0,0,0,.06),0 0 0 1px rgba(24,95,165,.12),0 12px 30px rgba(24,95,165,.18)}
.wl-stat__top{display:flex;align-items:center;gap:12px}
.wl-stat__icon{width:40px;height:40px;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.wl-stat__icon--blue{background:var(--blue-light);color:var(--blue)}.wl-stat__icon--green{background:var(--green-light);color:var(--green)}.wl-stat__icon--amber{background:var(--amber-light);color:var(--amber)}.wl-stat__icon--purple{background:#EEEDF9;color:var(--purple)}
.wl-stat__label{font-size:11px;font-weight:500;text-transform:uppercase;letter-spacing:.07em;color:var(--gray-400);margin:0}
.wl-stat__value{font-size:24px;font-weight:700;color:var(--gray-900);margin:0;line-height:1}
.wl-stat__bar{position:absolute;bottom:0;left:0;right:0;height:3px}
.wl-stat__bar--blue{background:var(--blue)}.wl-stat__bar--green{background:var(--green)}.wl-stat__bar--amber{background:#F59E0B}.wl-stat__bar--purple{background:var(--purple)}
.wl-action-row{display:flex;align-items:center;gap:14px;margin-bottom:20px;position:relative;z-index:1}
.wl-withdraw-btn{display:inline-flex;align-items:center;gap:7px;padding:9px 18px;background:var(--blue);color:var(--white);font-size:13px;font-weight:500;border:none;border-radius:7px;cursor:pointer;transition:all .13s}
.wl-withdraw-btn:hover{background:var(--blue-hover);transform:translateY(-1px)}
.wl-action-hint{display:flex;align-items:center;gap:6px}
.wl-action-hint__label{font-size:10px;font-weight:500;text-transform:uppercase;letter-spacing:.07em;color:var(--gray-400)}
.wl-action-hint__value{font-size:13px;font-weight:700;color:var(--blue)}
.wl-panel{background:var(--white);border:0.5px solid var(--blue-faint);border-radius:12px;overflow:hidden;box-shadow:0 4px 12px rgba(0,0,0,.04),0 0 0 1px rgba(24,95,165,.05),0 6px 18px rgba(24,95,165,.06)}
.wl-panel__head{display:flex;align-items:center;gap:10px;padding:16px 20px;border-bottom:0.5px solid var(--gray-200);background:var(--gray-50);flex-wrap:wrap}
.wl-panel__head-left{display:flex;align-items:center;gap:10px;flex:1}
.wl-panel__icon{width:34px;height:34px;border-radius:8px;background:var(--blue-light);color:var(--blue);display:flex;align-items:center;justify-content:center;flex-shrink:0}
.wl-panel__title{font-size:14px;font-weight:600;color:var(--gray-900);margin:0}
.wl-filter{display:flex;align-items:center;gap:8px}
.wl-filter__wrap{display:flex;align-items:center;gap:6px;border:0.5px solid var(--gray-200);border-radius:7px;padding:5px 10px;background:var(--white);color:var(--gray-500)}
.wl-filter__select{border:none;outline:none;background:transparent;font-size:12px;color:var(--gray-700);cursor:pointer;padding:0}
.wl-filter__wrap:focus-within{border-color:var(--blue);box-shadow:0 0 0 3px rgba(24,95,165,.08)}
.wl-tabs{display:flex;gap:0;border-bottom:0.5px solid var(--gray-200);padding:0 20px;background:var(--white);overflow-x:auto;-webkit-overflow-scrolling:touch;scrollbar-width:none}
.wl-tabs::-webkit-scrollbar{display:none}
.wl-tab{display:inline-flex;align-items:center;gap:7px;padding:12px 16px;font-size:13px;font-weight:500;color:var(--gray-500);background:none;border:none;border-bottom:2px solid transparent;cursor:pointer;transition:color .15s,border-color .15s;margin-bottom:-1px;white-space:nowrap;flex-shrink:0}
.wl-tab svg{stroke:currentColor;stroke-width:1.8;fill:none;stroke-linecap:round;stroke-linejoin:round}
.wl-tab:hover{color:var(--blue)}
.wl-tab--active{color:var(--blue);border-bottom-color:var(--blue)}
.wl-tab__count{display:inline-flex;align-items:center;justify-content:center;min-width:20px;height:20px;padding:0 6px;background:var(--gray-100);border-radius:99px;font-size:11px;font-weight:600;color:var(--gray-500)}
.wl-tab--active .wl-tab__count{background:var(--blue-light);color:var(--blue)}
.wl-tab-panel{padding:0}
.wl-table{width:100%;border-collapse:collapse}
.wl-table thead th{padding:.65rem 1rem;font-size:10px;font-weight:500;text-transform:uppercase;letter-spacing:.07em;color:var(--gray-500);background:var(--gray-50);border-bottom:0.5px solid var(--gray-200);text-align:left;white-space:nowrap}
.wl-table tbody td{padding:.8rem 1rem;font-size:13px;color:var(--gray-700);border-bottom:0.5px solid var(--gray-100);vertical-align:middle}
.wl-table tbody tr:last-child td{border-bottom:none}
.wl-table tbody tr:nth-child(even) td{background:var(--gray-50)}
.wl-table tbody tr:hover td{background:var(--gray-100)}
.wl-td-date{color:var(--gray-500);white-space:nowrap;font-size:12px}
.wl-td-desc{max-width:220px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.wl-td-amount{font-variant-numeric:tabular-nums;white-space:nowrap}
.wl-td-amount--net{font-weight:600;color:var(--green-dark)}.wl-td-amount--commission{color:var(--amber)}.wl-na{color:var(--gray-200)}
.wl-rate-pill{display:inline-flex;align-items:center;padding:2px 7px;border-radius:99px;background:var(--amber-light);color:var(--amber);border:0.5px solid var(--amber-border);font-size:10px;font-weight:500;margin-right:5px}
.wl-badge{display:inline-flex;align-items:center;gap:5px;padding:3px 9px;border-radius:99px;font-size:11px;font-weight:500;border:0.5px solid transparent}
.wl-badge__dot{width:6px;height:6px;border-radius:50%;background:currentColor;opacity:.7}
.wl-badge--credit{background:var(--green-light);color:var(--green-dark)}.wl-badge--debit{background:var(--red-light);color:var(--red)}.wl-badge--withdrawal{background:#EEEDF9;color:var(--purple)}.wl-badge--refund{background:var(--amber-light);color:var(--amber);border-color:var(--amber-border)}
.wl-empty{text-align:center;padding:3rem 1rem !important;color:var(--gray-400)}
.wl-empty__icon{display:flex;justify-content:center;margin-bottom:10px;color:var(--gray-200)}.wl-empty p{font-size:13px;margin:0}
.wl-pagination{padding:14px 20px;border-top:0.5px solid var(--gray-200);background:var(--gray-50);display:flex;justify-content:flex-end}

/* ── View button ── */
.rd-view-btn{display:inline-flex;align-items:center;gap:5px;padding:5px 11px;background:var(--blue-light);color:var(--blue);border:0.5px solid var(--blue-border);border-radius:6px;font-size:11px;font-weight:500;cursor:pointer;transition:all .13s;white-space:nowrap}
.rd-view-btn:hover{background:var(--blue);color:var(--white);border-color:var(--blue)}
.rd-view-btn svg{stroke:currentColor;flex-shrink:0}

/* ── Rent detail modal overlay ── */
.rd-overlay{position:fixed !important;top:0 !important;left:0 !important;right:0 !important;bottom:0 !important;width:100vw !important;height:100vh !important;background:rgba(17,24,39,.5) !important;backdrop-filter:blur(3px) !important;z-index:99999 !important;display:flex !important;align-items:center !important;justify-content:center !important;visibility:visible !important;opacity:1 !important;pointer-events:auto !important}
.rd-overlay[data-state="closed"]{display:none !important;visibility:hidden !important;pointer-events:none !important;opacity:0 !important}
.rd-box{background:var(--white) !important;border-radius:16px !important;width:100% !important;max-width:520px !important;box-shadow:0 24px 60px rgba(0,0,0,.18) !important;overflow:hidden !important;position:relative !important;z-index:100000 !important;display:flex !important;flex-direction:column !important;max-height:90vh !important}
.rd-box__body{overflow-y:auto;-webkit-overflow-scrolling:touch;flex:1}

/* Box head */
.rd-box__head{display:flex;align-items:center;gap:12px;padding:18px 20px 14px;border-bottom:0.5px solid var(--gray-200);background:var(--gray-50)}
.rd-box__head-left{display:flex;align-items:center;gap:12px;flex:1;min-width:0}
.rd-box__icon{width:38px;height:38px;border-radius:10px;background:var(--blue-light);color:var(--blue);display:flex;align-items:center;justify-content:center;flex-shrink:0}
.rd-box__icon svg{stroke:currentColor}
.rd-box__eyebrow{font-size:10px;font-weight:500;text-transform:uppercase;letter-spacing:.07em;color:var(--gray-400);margin:0 0 2px}
.rd-box__title{font-size:15px;font-weight:600;color:var(--gray-900);margin:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.rd-box__close{background:var(--gray-100);border:0.5px solid var(--gray-200);font-size:18px;color:var(--gray-500);cursor:pointer;width:30px;height:30px;border-radius:7px;display:flex;align-items:center;justify-content:center;transition:all .13s;flex-shrink:0}
.rd-box__close:hover{background:var(--gray-200);color:var(--gray-900)}

/* Body */
.rd-box__body{padding:20px}

/* Loading */
.rd-loading{display:flex;flex-direction:column;align-items:center;justify-content:center;gap:14px;padding:48px 20px;color:var(--gray-400);font-size:13px}
.rd-loading__spinner{width:32px;height:32px;border:3px solid var(--gray-200);border-top-color:var(--blue);border-radius:50%;animation:rdSpin .7s linear infinite}
@keyframes rdSpin{to{transform:rotate(360deg)}}

/* Error */
.rd-error{display:flex;flex-direction:column;align-items:center;gap:12px;padding:48px 20px;color:var(--gray-400);font-size:13px;text-align:center}
.rd-error svg{stroke:var(--gray-300)}

/* Parties row */
.rd-parties{display:flex;align-items:center;gap:12px;background:var(--gray-50);border:0.5px solid var(--gray-200);border-radius:12px;padding:16px;margin-bottom:16px}
.rd-party{display:flex;align-items:center;gap:10px;flex:1;min-width:0}
.rd-party__avatar{width:40px;height:40px;border-radius:50%;background:var(--blue);color:var(--white);font-size:16px;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.rd-party__avatar--green{background:var(--green)}
.rd-party__label{font-size:10px;font-weight:500;text-transform:uppercase;letter-spacing:.06em;color:var(--gray-400);margin:0 0 2px}
.rd-party__name{font-size:13px;font-weight:600;color:var(--gray-900);margin:0 0 1px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.rd-party__sub{font-size:11px;color:var(--gray-500);margin:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.rd-party-divider{color:var(--gray-300);flex-shrink:0}
.rd-party-divider svg{stroke:currentColor}

/* Meta grid */
.rd-meta-grid{display:grid;grid-template-columns:1fr 1fr;gap:1px;background:var(--gray-200);border:0.5px solid var(--gray-200);border-radius:10px;overflow:hidden;margin-bottom:16px}
.rd-meta-item{background:var(--white);padding:10px 14px}
.rd-meta-label{display:block;font-size:10px;font-weight:500;text-transform:uppercase;letter-spacing:.06em;color:var(--gray-400);margin-bottom:3px}
.rd-meta-value{font-size:13px;font-weight:500;color:var(--gray-900)}

/* Breakdown */
.rd-breakdown{border:0.5px solid var(--gray-200);border-radius:10px;overflow:hidden}
.rd-breakdown__head{padding:9px 14px;font-size:10px;font-weight:500;text-transform:uppercase;letter-spacing:.06em;color:var(--gray-500);background:var(--gray-50);border-bottom:0.5px solid var(--gray-200)}
.rd-breakdown__row{display:flex;align-items:center;justify-content:space-between;padding:11px 14px;font-size:13px;color:var(--gray-700);border-bottom:0.5px solid var(--gray-100)}
.rd-breakdown__row:last-child{border-bottom:none}
.rd-breakdown__row--commission{color:var(--amber)}
.rd-breakdown__row--net{background:var(--green-light);font-weight:600;color:var(--green-dark)}
.rd-breakdown__val{font-variant-numeric:tabular-nums;font-weight:500}
.rd-breakdown__val--deduct::before{content:'− '}
.rd-breakdown__val--net{font-weight:700;font-size:15px}
.rd-rate-chip{display:inline-flex;align-items:center;padding:1px 6px;border-radius:99px;background:var(--amber-light);color:var(--amber);border:0.5px solid var(--amber-border);font-size:10px;font-weight:600;margin-left:6px}

/* Withdraw modal — unchanged */
.wd-overlay{position:fixed !important;top:0 !important;left:0 !important;right:0 !important;bottom:0 !important;width:100vw !important;height:100vh !important;background:rgba(17,24,39,.45) !important;backdrop-filter:blur(2px) !important;z-index:99999 !important;display:flex !important;align-items:center !important;justify-content:center !important;visibility:visible !important;opacity:1 !important;pointer-events:auto !important}
.wd-overlay[data-state="closed"]{display:none !important;visibility:hidden !important;pointer-events:none !important;opacity:0 !important}
.wd-box{background:var(--white) !important;border-radius:14px !important;width:100% !important;max-width:460px !important;box-shadow:0 20px 50px rgba(0,0,0,.18) !important;overflow:hidden !important;position:relative !important;z-index:100000 !important;visibility:visible !important;opacity:1 !important}
.wd-box__head{display:flex;align-items:center;gap:12px;padding:20px 20px 12px;border-bottom:0.5px solid var(--gray-200);background:var(--gray-50)}
.wd-box__eyebrow{font-size:10px;font-weight:500;text-transform:uppercase;letter-spacing:.07em;color:var(--gray-400);margin:0 0 2px}
.wd-box__title{font-size:15px;font-weight:600;color:var(--gray-900);margin:0 0 2px}.wd-box__sub{font-size:12px;color:var(--gray-500);margin:0}
.wd-box__close{margin-left:auto;background:var(--gray-100);border:0.5px solid var(--gray-200);font-size:18px;color:var(--gray-500);cursor:pointer;width:30px;height:30px;border-radius:7px;display:flex;align-items:center;justify-content:center;line-height:1;transition:all .13s}
.wd-box__close:hover{background:var(--gray-200);color:var(--gray-900)}
.wd-box__body{padding:20px 20px 24px}
.wd-balance-row{display:flex;align-items:center;justify-content:space-between;background:var(--blue-light);border:0.5px solid var(--blue-border);border-radius:10px;padding:10px 14px;margin-bottom:20px;font-size:13px;color:var(--gray-700)}
.wd-balance-row strong{font-size:16px;font-weight:700;color:var(--blue)}
.wd-field{margin-bottom:16px}
.wd-label{display:block;font-size:10px;font-weight:500;text-transform:uppercase;letter-spacing:.07em;color:var(--gray-400);margin-bottom:6px}
.wd-input{width:100%;padding:9px 12px;font-size:13px;border:0.5px solid var(--gray-200);border-radius:7px;color:var(--gray-900);outline:none;background:var(--white);transition:border-color .15s;box-sizing:border-box}
.wd-input:focus{border-color:var(--blue);box-shadow:0 0 0 3px rgba(24,95,165,.1)}
.wd-hint{font-size:11px;color:var(--gray-400);margin:4px 0 0}
.wd-phone-wrap{display:flex;align-items:center;border:0.5px solid var(--gray-200);border-radius:7px;overflow:hidden;transition:border-color .15s}
.wd-phone-wrap:focus-within{border-color:var(--blue);box-shadow:0 0 0 3px rgba(24,95,165,.1)}
.wd-phone-prefix{padding:9px 10px;background:var(--gray-50);font-size:13px;color:var(--gray-500);font-weight:500;border-right:0.5px solid var(--gray-200);flex-shrink:0}
.wd-phone-wrap .wd-input{border:none;box-shadow:none;border-radius:0}
.wd-phone-wrap .wd-input:focus{box-shadow:none}
.wd-warning{display:flex;align-items:flex-start;gap:8px;background:var(--amber-light);border:0.5px solid var(--amber-border);border-radius:8px;padding:10px 12px;font-size:12px;color:var(--amber);margin-bottom:20px}
.wd-submit-btn{display:flex;align-items:center;justify-content:center;gap:10px;width:100%;padding:11px 20px;background:var(--blue);color:var(--white);font-size:14px;font-weight:500;border:none;border-radius:7px;cursor:pointer;transition:all .13s}
.wd-submit-btn:hover{background:var(--blue-hover);transform:translateY(-1px)}
.wd-submit-btn:disabled{background:#93c5e8;cursor:not-allowed;transform:none}
@media(max-width:1100px){.wl-stats{grid-template-columns:repeat(2,1fr)}}
@media(max-width:640px){
    .wl-stats{grid-template-columns:1fr}
    .wl-action-row{flex-direction:column;align-items:flex-start}
    /* Rent detail modal — full viewport on small screens */
    .rd-overlay{align-items:flex-end !important}
    .rd-box{border-radius:16px 16px 0 0 !important;max-height:92vh !important;width:100% !important;max-width:100% !important}
    .rd-box__head{padding:14px 16px 12px}
    .rd-box__body{padding:14px 16px}
    .rd-parties{flex-direction:column;align-items:flex-start}
    .rd-party-divider{transform:rotate(90deg);margin:0 0 0 14px}
    .rd-meta-grid{grid-template-columns:1fr}
}
</style>
@endpush

@push('script')
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ── Tab switching ─────────────────────────────────────────
    document.querySelectorAll('.wl-tab').forEach(function (tab) {
        tab.addEventListener('click', function () {
            document.querySelectorAll('.wl-tab').forEach(function (t) { t.classList.remove('wl-tab--active'); });
            document.querySelectorAll('.wl-tab-panel').forEach(function (p) { p.style.display = 'none'; });
            this.classList.add('wl-tab--active');
            document.getElementById('tab-' + this.dataset.tab).style.display = 'block';
            applyTypeFilter();
        });
    });

    // ── Type filter ───────────────────────────────────────────
    function applyTypeFilter() {
        var val = document.getElementById('typeFilter').value;
        ['mpTable', 'rentTable'].forEach(function (tableId) {
            var table = document.getElementById(tableId);
            if (!table) return;
            table.querySelectorAll('tbody tr[data-type]').forEach(function (row) {
                row.style.display = (!val || row.dataset.type === val) ? '' : 'none';
            });
        });
    }
    document.getElementById('typeFilter').addEventListener('change', applyTypeFilter);

    // ── Withdraw modal ────────────────────────────────────────
    var wdModal     = document.getElementById('withdrawModal');
    var openBtn     = document.getElementById('openWithdrawModal');
    var closeBtn    = document.getElementById('closeWithdrawModal');
    var amountInput = document.getElementById('withdrawAmount');
    var submitBtn   = document.getElementById('withdrawSubmitBtn');
    var form        = document.getElementById('withdrawForm');

    if (!wdModal || !openBtn) { console.error('[Wallet] Withdraw modal elements missing.'); return; }

    var balance = parseFloat(openBtn.getAttribute('data-balance') || 0);

    function openWithdrawModal() {
        wdModal.removeAttribute('data-state');
        wdModal.style.setProperty('display',        'flex',    'important');
        wdModal.style.setProperty('visibility',     'visible', 'important');
        wdModal.style.setProperty('opacity',        '1',       'important');
        wdModal.style.setProperty('pointer-events', 'auto',    'important');
        if (amountInput) { amountInput.value = ''; amountInput.focus(); }
    }
    function closeWithdrawModal() {
        wdModal.setAttribute('data-state', 'closed');
        wdModal.style.setProperty('display',        'none',   'important');
        wdModal.style.setProperty('visibility',     'hidden', 'important');
        wdModal.style.setProperty('opacity',        '0',      'important');
        wdModal.style.setProperty('pointer-events', 'none',   'important');
        if (amountInput) amountInput.value = '';
        if (submitBtn) { submitBtn.disabled = false; submitBtn.textContent = '{{ __("Send to M-Pesa") }}'; }
    }
    closeWithdrawModal();

    openBtn.addEventListener('click', function (e) { e.stopPropagation(); openWithdrawModal(); });
    if (closeBtn) closeBtn.addEventListener('click', closeWithdrawModal);
    wdModal.addEventListener('click', function (e) { if (e.target === wdModal) closeWithdrawModal(); });

    if (amountInput && submitBtn) {
        amountInput.addEventListener('input', function () {
            var val = parseFloat(this.value);
            if (val > balance) {
                this.setCustomValidity('{{ __("Amount exceeds available balance.") }}');
                submitBtn.disabled = true;
            } else {
                this.setCustomValidity('');
                submitBtn.disabled = false;
            }
        });
    }

    if (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            var phone  = document.getElementById('withdrawPhone').value.trim();
            var amount = parseFloat(amountInput.value);
            if (!/^[71]\d{8}$/.test(phone)) { toastr.error('{{ __("Enter a valid Safaricom number (e.g. 712345678).") }}'); return; }
            if (!amount || amount <= 0)      { toastr.error('{{ __("Enter a valid withdrawal amount.") }}'); return; }
            if (amount > balance)            { toastr.error('{{ __("Amount exceeds your available balance.") }}'); return; }
            submitBtn.disabled    = true;
            submitBtn.textContent = '{{ __("Processing…") }}';
            fetch(this.action, {
                method:  'POST',
                headers: { 'Accept': 'application/json' },
                body:    new FormData(this),
            })
            .then(function (r) { return r.json(); })
            .then(function (d) {
                if (d.success) {
                    closeWithdrawModal();
                    toastr.success(d.message || '{{ __("Withdrawal initiated successfully.") }}');
                    setTimeout(function () { location.reload(); }, 2000);
                } else {
                    toastr.error(d.error || '{{ __("Withdrawal failed. Please try again.") }}');
                    submitBtn.disabled    = false;
                    submitBtn.textContent = '{{ __("Send to M-Pesa") }}';
                }
            })
            .catch(function () {
                toastr.error('{{ __("Something went wrong. Please try again.") }}');
                submitBtn.disabled    = false;
                submitBtn.textContent = '{{ __("Send to M-Pesa") }}';
            });
        });
    }

    // ── Rent detail modal ─────────────────────────────────────
    var rdModal    = document.getElementById('rentDetailModal');
    var rdLoading  = document.getElementById('rdLoading');
    var rdContent  = document.getElementById('rdContent');
    var rdError    = document.getElementById('rdError');
    var rdErrorMsg = document.getElementById('rdErrorMsg');
    var rdCloseBtn = document.getElementById('closeRentDetailModal');

    function openRentModal() {
        rdModal.removeAttribute('data-state');
        rdModal.style.setProperty('display',        'flex',    'important');
        rdModal.style.setProperty('visibility',     'visible', 'important');
        rdModal.style.setProperty('opacity',        '1',       'important');
        rdModal.style.setProperty('pointer-events', 'auto',    'important');
    }
    function closeRentModal() {
        rdModal.setAttribute('data-state', 'closed');
        rdModal.style.setProperty('display',        'none',   'important');
        rdModal.style.setProperty('visibility',     'hidden', 'important');
        rdModal.style.setProperty('opacity',        '0',      'important');
        rdModal.style.setProperty('pointer-events', 'none',   'important');
    }
    function showRdState(state) {
        rdLoading.style.display = state === 'loading' ? 'flex'  : 'none';
        rdContent.style.display = state === 'content' ? 'block' : 'none';
        rdError.style.display   = state === 'error'   ? 'flex'  : 'none';
    }

    function populateRentModal(d) {
        // Header invoice no
        document.getElementById('rdInvoiceNo').textContent       = d.invoice_no || '—';
        // Tenant
        document.getElementById('rdTenantAvatar').textContent    = d.tenant_name ? d.tenant_name.charAt(0).toUpperCase() : 'T';
        document.getElementById('rdTenantName').textContent      = d.tenant_name  || '—';
        document.getElementById('rdTenantEmail').textContent     = d.tenant_email || '—';
        // Unit
        document.getElementById('rdUnitAvatar').textContent      = d.unit_name ? d.unit_name.charAt(0).toUpperCase() : 'U';
        document.getElementById('rdUnitName').textContent        = d.unit_name     || '—';
        document.getElementById('rdPropertyName').textContent    = d.property_name || '—';
        // Meta
        document.getElementById('rdInvoiceNoDetail').textContent = d.invoice_no    || '—';
        document.getElementById('rdInvoiceType').textContent     = d.invoice_type  || '—';
        document.getElementById('rdIssueDate').textContent       = d.issue_date    || '—';
        document.getElementById('rdDueDate').textContent         = d.due_date      || '—';
        document.getElementById('rdPaidOn').textContent          = d.paid_on       || '—';
        document.getElementById('rdPaymentMethod').textContent   = d.payment_method|| '—';
        // Breakdown
        document.getElementById('rdRateChip').textContent        = (d.commission_rate || 1) + '%';
        document.getElementById('rdGross').textContent           = 'KSh ' + formatNum(d.gross_amount);
        document.getElementById('rdCommission').textContent      = 'KSh ' + formatNum(d.commission_amount);
        document.getElementById('rdNet').textContent             = 'KSh ' + formatNum(d.net_amount);
    }

    function formatNum(val) {
        return parseFloat(val || 0).toLocaleString('en-KE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    // Delegate click on all view buttons — works for paginated rows too
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('.rd-view-btn');
        if (!btn) return;
        var url = btn.getAttribute('data-url');
        if (!url) return;

        openRentModal();
        showRdState('loading');

        fetch(url, { headers: { 'Accept': 'application/json' } })
            .then(function (r) {
                if (!r.ok) throw new Error('HTTP ' + r.status);
                return r.json();
            })
            .then(function (res) {
                if (res.success && res.data) {
                    populateRentModal(res.data);
                    showRdState('content');
                } else {
                    rdErrorMsg.textContent = res.message || '{{ __("Could not load payment details.") }}';
                    showRdState('error');
                }
            })
            .catch(function () {
                rdErrorMsg.textContent = '{{ __("Could not load payment details.") }}';
                showRdState('error');
            });
    });

    closeRentModal(); // ensure closed on page load
    if (rdCloseBtn) rdCloseBtn.addEventListener('click', closeRentModal);
    rdModal.addEventListener('click', function (e) { if (e.target === rdModal) closeRentModal(); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeRentModal(); });

});
</script>
@endpush