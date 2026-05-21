@extends('affiliate.layouts.app')

@section('content')

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="page-content-wrapper bg-white p-30 radius-20">
                <div class="container">
                    @php
                        $pageTitle = 'Earnings';
                    @endphp
                    {{-- ── Page Header ── --}}
                    <div class="cmx-header">
                        <div>
                            <nav aria-label="breadcrumb">
                                <ol class="cmx-breadcrumb">
                                    <li>
                                        <a href="{{ route('affiliate.dashboard') }}">
                                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                            {{ __('Dashboard') }}
                                        </a>
                                    </li>
                                    <li aria-current="page">{{ __('Commissions') }}</li>
                                </ol>
                            </nav>
                            <h1 class="cmx-page-title">{{ __('My Commissions') }}</h1>
                            <p class="cmx-page-sub">{{ __('Earnings from subscription referrals, rent payments, and marketplace sales') }}</p>
                        </div>
                    </div>

                    {{-- ── Stat Cards ── --}}
                    <div class="cmx-stats-grid">
                        <div class="cmx-stat-card cmx-stat-card--blue">
                            <div class="cmx-stat-card__top">
                                <div class="cmx-stat-card__icon cmx-stat-card__icon--blue">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none"><rect x="1" y="4" width="22" height="16" rx="3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" stroke="currentColor" stroke-width="1.8"/></svg>
                                </div>
                                <span class="cmx-stat-card__label">{{ __('Available Balance') }}</span>
                            </div>
                            <p class="cmx-stat-card__value">KSh {{ number_format($availableBalance, 2) }}</p>
                            <div class="cmx-stat-card__bar cmx-stat-card__bar--blue"></div>
                        </div>
                        <div class="cmx-stat-card cmx-stat-card--green">
                            <div class="cmx-stat-card__top">
                                <div class="cmx-stat-card__icon cmx-stat-card__icon--green">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><polyline points="17 6 23 6 23 12" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </div>
                                <span class="cmx-stat-card__label">{{ __('This Month') }}</span>
                            </div>
                            <p class="cmx-stat-card__value">KSh {{ number_format($currentMonthTotal, 2) }}</p>
                            <div class="cmx-stat-card__bar cmx-stat-card__bar--green"></div>
                        </div>
                        <div class="cmx-stat-card cmx-stat-card--amber">
                            <div class="cmx-stat-card__top">
                                <div class="cmx-stat-card__icon cmx-stat-card__icon--amber">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.8"/><path d="M15 9H9l2 3-2 3h6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </div>
                                <span class="cmx-stat-card__label">{{ __('Lifetime Earned') }}</span>
                            </div>
                            <p class="cmx-stat-card__value">KSh {{ number_format($lifeTimeGross, 2) }}</p>
                            <div class="cmx-stat-card__bar cmx-stat-card__bar--amber"></div>
                        </div>
                        <div class="cmx-stat-card cmx-stat-card--purple">
                            <div class="cmx-stat-card__top">
                                <div class="cmx-stat-card__icon cmx-stat-card__icon--purple">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none"><path d="M12 5v14M5 12l7 7 7-7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </div>
                                <span class="cmx-stat-card__label">{{ __('Total Withdrawn') }}</span>
                            </div>
                            <p class="cmx-stat-card__value">KSh {{ number_format($totalWithdrawals, 2) }}</p>
                            <div class="cmx-stat-card__bar cmx-stat-card__bar--purple"></div>
                        </div>
                    </div>

                    {{-- ── Withdraw Action Row ── --}}
                    <div class="cmx-action-row">
                        <button type="button"
                                class="cmx-withdraw-btn"
                                id="cmxOpenWithdrawModal"
                                data-balance="{{ $availableBalance }}">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M12 5v14M5 12l7 7 7-7" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            {{ __('Withdraw to M-Pesa') }}
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none"><path d="M5 12h14M12 5l7 7-7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </button>
                        <div class="cmx-action-hint">
                            <span class="cmx-action-hint__label">{{ __('Available') }}</span>
                            <span class="cmx-action-hint__value">KSh {{ number_format($availableBalance, 2) }}</span>
                        </div>
                    </div>

                    {{-- ════════════════════════════════════════════
                         TABS — Commissions | Withdrawals
                         ════════════════════════════════════════ --}}
                    <div class="cmx-tabs-wrapper">
                        <div class="cmx-tabs-nav">
                            <button class="cmx-tab-btn cmx-tab-btn--active" data-tab="commissions">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                {{ __('Commission History') }}
                            </button>
                            <button class="cmx-tab-btn" data-tab="withdrawals">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M12 5v14M5 12l7 7 7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                {{ __('Withdrawal History') }}
                            </button>
                        </div>

                        {{-- ── Tab: Commission History ── --}}
                        <div class="cmx-tab-panel cmx-tab-panel--active" id="cmxTabCommissions">
                            <div class="cmx-panel">
                                <div class="cmx-panel__head">
                                    <div class="cmx-panel__head-left">
                                        <div class="cmx-panel__icon">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><polyline points="14 2 14 8 20 8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                        </div>
                                        <h4 class="cmx-panel__title">{{ __('Monthly Commission History') }}</h4>
                                    </div>
                                    <div class="cmx-filter-wrap">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                        <select id="cmxSourceFilter" class="cmx-filter-select">
                                            <option value="">{{ __('All Sources') }}</option>
                                            <option value="subscription">{{ __('Subscriptions') }}</option>
                                            <option value="rent">{{ __('Rent') }}</option>
                                            <option value="marketplace">{{ __('Marketplace') }}</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table class="cmx-table" id="cmxTable">
                                        <thead>
                                            <tr>
                                                <th>{{ __('Period') }}</th>
                                                <th>{{ __('Subscriptions') }}</th>
                                                <th>{{ __('Rent') }}</th>
                                                <th>{{ __('Marketplace') }}</th>
                                                <th>{{ __('Total Payout') }}</th>
                                                <th>{{ __('Detail') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($monthlySummaries as $row)
                                            <tr data-month="{{ $row->period_month }}"
                                                data-year="{{ $row->period_year }}"
                                                data-has-subscription="{{ $row->new_commission_payout + $row->recurring_commission_payout > 0 ? 'subscription' : '' }}"
                                                data-has-rent="{{ $row->rent_commission_payout > 0 ? 'rent' : '' }}"
                                                data-has-marketplace="{{ $row->marketplace_commission_payout > 0 ? 'marketplace' : '' }}">
                                                <td class="cmx-td-date">
                                                    {{ \Carbon\Carbon::createFromDate($row->period_year, $row->period_month, 1)->format('M Y') }}
                                                </td>
                                                <td class="cmx-td-amount">
                                                    @if($row->new_commission_payout + $row->recurring_commission_payout > 0)
                                                        KSh {{ number_format($row->new_commission_payout + $row->recurring_commission_payout, 2) }}
                                                        <span class="cmx-pill cmx-pill--subscription">Sub</span>
                                                    @else
                                                        <span class="cmx-na">—</span>
                                                    @endif
                                                </td>
                                                <td class="cmx-td-amount">
                                                    @if($row->rent_commission_payout > 0)
                                                        KSh {{ number_format($row->rent_commission_payout, 2) }}
                                                        <span class="cmx-pill cmx-pill--rent">Rent</span>
                                                    @else
                                                        <span class="cmx-na">—</span>
                                                    @endif
                                                </td>
                                                <td class="cmx-td-amount">
                                                    @if($row->marketplace_commission_payout > 0)
                                                        KSh {{ number_format($row->marketplace_commission_payout, 2) }}
                                                        <span class="cmx-pill cmx-pill--marketplace">Market</span>
                                                    @else
                                                        <span class="cmx-na">—</span>
                                                    @endif
                                                </td>
                                                <td class="cmx-td-amount cmx-td-amount--total">
                                                    KSh {{ number_format($row->total_commission_payout, 2) }}
                                                </td>
                                                <td>
                                                    <button type="button"
                                                            class="cmx-view-btn"
                                                            data-url="{{ route('affiliate.commissions.detail', ['month' => $row->period_month, 'year' => $row->period_year]) }}"
                                                            title="{{ __('View breakdown') }}">
                                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"/></svg>
                                                        {{ __('View') }}
                                                    </button>
                                                </td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="6" class="cmx-empty">
                                                    <div class="cmx-empty__icon">
                                                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.5"/></svg>
                                                    </div>
                                                    <p>{{ __('No commission history yet.') }}</p>
                                                </td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>

                                @if($monthlySummaries->hasPages())
                                <div class="cmx-pagination">{{ $monthlySummaries->links() }}</div>
                                @endif
                            </div>
                        </div>

                        {{-- ── Tab: Withdrawal History ── --}}
                        <div class="cmx-tab-panel" id="cmxTabWithdrawals">
                            <div class="cmx-panel">
                                <div class="cmx-panel__head">
                                    <div class="cmx-panel__head-left">
                                        <div class="cmx-panel__icon">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M12 5v14M5 12l7 7 7-7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                        </div>
                                        <h4 class="cmx-panel__title">{{ __('Withdrawal History') }}</h4>
                                    </div>
                                    <div class="cmx-filter-wrap">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                        <select id="cmxWithdrawalStatusFilter" class="cmx-filter-select">
                                            <option value="">{{ __('All Statuses') }}</option>
                                            <option value="pending">{{ __('Pending') }}</option>
                                            <option value="approved">{{ __('Approved') }}</option>
                                            <option value="rejected">{{ __('Rejected') }}</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table class="cmx-table cmx-table--withdrawals" id="cmxWithdrawalTable">
                                        <thead>
                                            <tr>
                                                <th class="cmx-th-date">{{ __('Date Requested') }}</th>
                                                <th class="cmx-th-amount">{{ __('Amount') }}</th>
                                                <th class="cmx-th-phone">{{ __('Phone') }}</th>
                                                <th class="cmx-th-method">{{ __('Method') }}</th>
                                                <th class="cmx-th-status">{{ __('Status') }}</th>
                                                <th class="cmx-th-date">{{ __('Processed At') }}</th>
                                                <th class="cmx-th-ref">{{ __('Reference') }}</th>
                                                <th class="cmx-th-notes">{{ __('Notes') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($withdrawals as $withdrawal)
                                            <tr data-status="{{ $withdrawal->status }}">
                                                <td class="cmx-td-date">
                                                    {{ $withdrawal->created_at->format('M d, Y H:i') }}
                                                </td>
                                                <td class="cmx-td-amount cmx-td-amount--total">
                                                    KSh {{ number_format($withdrawal->amount, 2) }}
                                                </td>
                                                <td>
                                                    <span class="cmx-mono">{{ $withdrawal->phone }}</span>
                                                </td>
                                                <td>
                                                    @if($withdrawal->settlement_method === 'b2c')
                                                        <span class="cmx-method-badge cmx-method-badge--mpesa">
                                                            <img src="{{ asset('assets/images/gateway-icon/mpesa.jpg') }}" alt="M-Pesa" style="width:16px;height:16px;border-radius:4px;object-fit:cover;">
                                                            M-Pesa B2C
                                                        </span>
                                                    @else
                                                        <span class="cmx-method-badge">{{ __('Manual') }}</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="cmx-status-badge cmx-status-badge--{{ $withdrawal->status }}">
                                                        @if($withdrawal->status === AFFILIATE_WITHDRAWAL_PENDING)
                                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/><path d="M12 6v6l4 2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                                                        @elseif($withdrawal->status === AFFILIATE_WITHDRAWAL_APPROVED)
                                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none"><path d="M22 11.08V12a10 10 0 11-5.93-9.14" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><polyline points="22 4 12 14.01 9 11.01" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                                        @elseif($withdrawal->status === AFFILIATE_WITHDRAWAL_REJECTED)
                                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/><line x1="15" y1="9" x2="9" y2="15" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><line x1="9" y1="9" x2="15" y2="15" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                                                        @endif
                                                        {{ ucfirst($withdrawal->status) }}
                                                    </span>
                                                </td>
                                                <td class="cmx-td-date">
                                                    {{ $withdrawal->processed_at ? \Carbon\Carbon::parse($withdrawal->processed_at)->format('M d, Y H:i') : '—' }}
                                                </td>
                                                <td>
                                                    @if($withdrawal->mpesa_reference)
                                                        <span class="cmx-ref-badge" title="{{ $withdrawal->mpesa_reference }}">
                                                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" style="flex-shrink:0"><rect x="3" y="3" width="18" height="18" rx="3" stroke="currentColor" stroke-width="1.8"/><path d="M9 12l2 2 4-4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                                            {{ Str::limit($withdrawal->mpesa_reference, 16) }}
                                                        </span>
                                                    @else
                                                        <span class="cmx-na">—</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($withdrawal->notes)
                                                        <span class="cmx-notes-cell" title="{{ $withdrawal->notes }}">
                                                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" style="flex-shrink:0"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                                            {{ Str::limit($withdrawal->notes, 24) }}
                                                        </span>
                                                    @else
                                                        <span class="cmx-na">—</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="8" class="cmx-empty">
                                                    <div class="cmx-empty__icon">
                                                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.5"/></svg>
                                                    </div>
                                                    <p>{{ __('No withdrawal history yet.') }}</p>
                                                    <p style="font-size:12px;margin-top:4px;">{{ __('Click "Withdraw to M-Pesa" above to make your first withdrawal.') }}</p>
                                                </td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>

                                @if($withdrawals->hasPages())
                                <div class="cmx-pagination">{{ $withdrawals->links() }}</div>
                                @endif
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════
     MODALS — rendered at body level via JS (see @push script)
     These placeholder divs get moved to <body> on DOMContentLoaded
     to escape any stacking-context trap in the layout wrapper.
     ══════════════════════════════════════════════════════ --}}

{{-- ── Withdraw Modal ── --}}
<div id="cmxWithdrawModal" class="cmx-modal-overlay" aria-modal="true" role="dialog">
    <div class="cmx-modal-box cmx-modal-box--sm">
        <div class="cmx-modal-box__head">
            <div class="cmx-modal-box__head-icon">
                <img src="{{ asset('assets/images/gateway-icon/mpesa.jpg') }}" alt="M-Pesa"
                     style="width:36px;height:36px;border-radius:8px;object-fit:cover;">
            </div>
            <div>
                <p class="cmx-modal-eyebrow">{{ __('Payout') }}</p>
                <h5 class="cmx-modal-title">{{ __('Withdraw via M-Pesa') }}</h5>
                <p class="cmx-modal-sub">{{ __('Funds will be sent directly to your Safaricom number.') }}</p>
            </div>
            <button type="button" class="cmx-modal-close" id="cmxCloseWithdrawModal" aria-label="Close">&times;</button>
        </div>
        <div class="cmx-modal-body">
            <div class="cmx-balance-row">
                <span>{{ __('Available balance') }}</span>
                <strong>KSh {{ number_format($availableBalance, 2) }}</strong>
            </div>
            <form id="cmxWithdrawForm" action="{{ route('affiliate.withdraw') }}" method="POST">
                @csrf
                <div class="cmx-field">
                    <label class="cmx-field__label">{{ __('M-Pesa Phone Number') }}</label>
                    <div class="cmx-phone-wrap">
                        <span class="cmx-phone-prefix">+254</span>
                        <input type="text" name="phone" id="cmxWithdrawPhone" class="cmx-field__input"
                               placeholder="7XX XXX XXX"
                               value="{{ auth()->user()->contact_number ? ltrim(auth()->user()->contact_number, '+254') : '' }}"
                               maxlength="9" required>
                    </div>
                    <p class="cmx-field__hint">{{ __('Enter without country code. e.g. 712345678') }}</p>
                </div>
                <div class="cmx-field">
                    <label class="cmx-field__label">{{ __('Amount (KSh)') }}</label>
                    <input type="number" name="amount" id="cmxWithdrawAmount" class="cmx-field__input"
                           placeholder="0.00" min="1" step="0.01"
                           max="{{ $availableBalance }}" required>
                    <p class="cmx-field__hint">{{ __('Minimum withdrawal: KSh 1.00') }}</p>
                </div>
                <div class="cmx-warning-box">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" style="flex-shrink:0;margin-top:1px"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><line x1="12" y1="9" x2="12" y2="13" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><line x1="12" y1="17" x2="12.01" y2="17" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                    <span>{{ __('Withdrawals are processed within a few minutes. Ensure the number is correct — this cannot be reversed.') }}</span>
                </div>
                <button type="submit" class="cmx-submit-btn" id="cmxWithdrawSubmitBtn">
                    <img src="{{ asset('assets/images/gateway-icon/mpesa.jpg') }}" alt=""
                         style="width:20px;height:20px;border-radius:4px;object-fit:cover;">
                    {{ __('Send to M-Pesa') }}
                </button>
            </form>
        </div>
    </div>
</div>

{{-- ── Month Detail Modal ── --}}
<div id="cmxDetailModal" class="cmx-modal-overlay" aria-modal="true" role="dialog">
    <div class="cmx-modal-box cmx-modal-box--lg">
        <div class="cmx-modal-box__head">
            <div class="cmx-modal-box__head-left">
                <div class="cmx-modal-box__head-icon cmx-modal-box__head-icon--round">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><polyline points="14 2 14 8 20 8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </div>
                <div>
                    <p class="cmx-modal-eyebrow">{{ __('Commission Breakdown') }}</p>
                    <h5 class="cmx-modal-title" id="cmxDetailPeriodTitle">—</h5>
                </div>
            </div>
            <button type="button" class="cmx-modal-close" id="cmxCloseDetailModal" aria-label="Close">&times;</button>
        </div>

        <div id="cmxDetailLoading" class="cmx-detail-loading">
            <div class="cmx-detail-loading__spinner"></div>
            <p>{{ __('Loading breakdown…') }}</p>
        </div>

        <div id="cmxDetailContent" class="cmx-modal-body" style="display:none;">
            <div class="cmx-detail-tabs">
                <button class="cmx-detail-tab cmx-detail-tab--active" data-tab="subscription">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none"><path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    {{ __('Subscriptions') }}
                    <span class="cmx-detail-tab__count" id="cmxSubCount">0</span>
                </button>
                <button class="cmx-detail-tab" data-tab="rent">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    {{ __('Rent') }}
                    <span class="cmx-detail-tab__count" id="cmxRentCount">0</span>
                </button>
                <button class="cmx-detail-tab" data-tab="marketplace">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4zM3 6h18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    {{ __('Marketplace') }}
                    <span class="cmx-detail-tab__count" id="cmxMarketCount">0</span>
                </button>
            </div>

            <div id="cmx-tab-subscription" class="cmx-detail-panel">
                <table class="cmx-detail-table">
                    <thead><tr>
                        <th>{{ __('Date') }}</th>
                        <th>{{ __('Owner') }}</th>
                        <th>{{ __('Type') }}</th>
                        <th>{{ __('Amount') }}</th>
                        <th>{{ __('Rate') }}</th>
                        <th>{{ __('Payout') }}</th>
                    </tr></thead>
                    <tbody id="cmxSubRows"></tbody>
                </table>
                <p class="cmx-detail-empty" id="cmxSubEmpty" style="display:none;">{{ __('No subscription commissions this month.') }}</p>
            </div>

            <div id="cmx-tab-rent" class="cmx-detail-panel" style="display:none;">
                <table class="cmx-detail-table">
                    <thead><tr>
                        <th>{{ __('Date') }}</th>
                        <th>{{ __('Owner') }}</th>
                        <th>{{ __('Rate') }}</th>
                        <th>{{ __('Payout') }}</th>
                    </tr></thead>
                    <tbody id="cmxRentRows"></tbody>
                </table>
                <p class="cmx-detail-empty" id="cmxRentEmpty" style="display:none;">{{ __('No rent commissions this month.') }}</p>
            </div>

            <div id="cmx-tab-marketplace" class="cmx-detail-panel" style="display:none;">
                <table class="cmx-detail-table">
                    <thead><tr>
                        <th>{{ __('Date') }}</th>
                        <th>{{ __('Owner') }}</th>
                        <th>{{ __('Rate') }}</th>
                        <th>{{ __('Payout') }}</th>
                    </tr></thead>
                    <tbody id="cmxMarketRows"></tbody>
                </table>
                <p class="cmx-detail-empty" id="cmxMarketEmpty" style="display:none;">{{ __('No marketplace commissions this month.') }}</p>
            </div>
        </div>

        <div id="cmxDetailError" class="cmx-detail-error" style="display:none;">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.5"/><line x1="12" y1="8" x2="12" y2="12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><line x1="12" y1="16" x2="12.01" y2="16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
            <p>{{ __('Could not load breakdown.') }}</p>
        </div>
    </div>
</div>

@endsection

@push('style')
<style>
/* ═══════════════════════════════════════════════════════
   CMX — Commission page styles
   All prefixed with "cmx-" to avoid collisions with the
   affiliate layout's own classes (ac-, afwd-, afmd-, etc.)
   ═══════════════════════════════════════════════════════ */

:root {
    --cmx-blue:        #185FA5;
    --cmx-blue-hover:  #0F4A84;
    --cmx-blue-light:  #E6F1FB;
    --cmx-blue-border: #B5D4F4;
    --cmx-blue-faint:  rgba(24,95,165,.43);
    --cmx-green:       #1D9E75;
    --cmx-green-dark:  #0F6E56;
    --cmx-green-light: #E1F5EE;
    --cmx-amber:       #854F0B;
    --cmx-amber-light: #FAEEDA;
    --cmx-amber-bdr:   #F5D9A8;
    --cmx-purple:      #534AB7;
    --cmx-purple-light:#EEEDF9;
    --cmx-gray-900:    #111827;
    --cmx-gray-700:    #374151;
    --cmx-gray-500:    #6b7280;
    --cmx-gray-400:    #9ca3af;
    --cmx-gray-200:    #e5e7eb;
    --cmx-gray-100:    #f3f4f6;
    --cmx-gray-50:     #fafafa;
    --cmx-white:       #ffffff;
    --cmx-red:         #DC2626;
    --cmx-red-light:   #FEE2E2;
}

#cmxWithdrawModal,
#cmxDetailModal {
    display: none !important;
}

/* ── Header ── */
.cmx-header { display:flex; align-items:flex-start; justify-content:space-between; flex-wrap:wrap; gap:1rem; margin-bottom:24px; }
.cmx-breadcrumb { display:flex; align-items:center; gap:5px; list-style:none; padding:0; margin:0 0 8px; font-size:12px; color:var(--cmx-gray-400); }
.cmx-breadcrumb li:not(:last-child)::after { content:''; display:inline-block; width:5px; height:5px; border-right:1.5px solid #d1d5db; border-top:1.5px solid #d1d5db; transform:rotate(45deg); margin-left:5px; opacity:.5; }
.cmx-breadcrumb a { display:inline-flex; align-items:center; gap:4px; color:var(--cmx-blue); text-decoration:none; font-weight:500; }
.cmx-page-title { font-size:22px; font-weight:500; color:var(--cmx-gray-900); margin:0 0 4px; }
.cmx-page-sub   { font-size:13px; color:var(--cmx-gray-500); margin:0; }

/* ── Stat cards ── */
.cmx-stats-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:16px; margin-bottom:20px; }
.cmx-stat-card  { background:var(--cmx-white); border:0.5px solid var(--cmx-blue-faint); border-radius:14px; padding:20px; display:flex; flex-direction:column; gap:12px; position:relative; overflow:hidden; transition:all .25s ease; box-shadow:0 4px 12px rgba(0,0,0,.04),0 0 0 1px rgba(24,95,165,.05),0 6px 18px rgba(24,95,165,.06); }
.cmx-stat-card:hover { border-color:var(--cmx-blue); transform:translateY(-3px); box-shadow:0 10px 25px rgba(0,0,0,.06),0 0 0 1px rgba(24,95,165,.12),0 12px 30px rgba(24,95,165,.18); }
.cmx-stat-card__top   { display:flex; align-items:center; gap:12px; }
.cmx-stat-card__icon  { width:40px; height:40px; border-radius:10px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.cmx-stat-card__icon--blue   { background:var(--cmx-blue-light);   color:var(--cmx-blue); }
.cmx-stat-card__icon--green  { background:var(--cmx-green-light);  color:var(--cmx-green); }
.cmx-stat-card__icon--amber  { background:var(--cmx-amber-light);  color:var(--cmx-amber); }
.cmx-stat-card__icon--purple { background:var(--cmx-purple-light); color:var(--cmx-purple); }
.cmx-stat-card__label { font-size:11px; font-weight:500; text-transform:uppercase; letter-spacing:.07em; color:var(--cmx-gray-400); margin:0; }
.cmx-stat-card__value { font-size:24px; font-weight:700; color:var(--cmx-gray-900); margin:0; line-height:1; }
.cmx-stat-card__bar   { position:absolute; bottom:0; left:0; right:0; height:3px; }
.cmx-stat-card__bar--blue   { background:var(--cmx-blue); }
.cmx-stat-card__bar--green  { background:var(--cmx-green); }
.cmx-stat-card__bar--amber  { background:#F59E0B; }
.cmx-stat-card__bar--purple { background:var(--cmx-purple); }

/* ── Action row ── */
.cmx-action-row    { display:flex; align-items:center; gap:14px; margin-bottom:20px; }
.cmx-withdraw-btn  { display:inline-flex; align-items:center; gap:7px; padding:9px 18px; background:var(--cmx-blue); color:var(--cmx-white); font-size:13px; font-weight:500; border:none; border-radius:7px; cursor:pointer; transition:all .13s; }
.cmx-withdraw-btn:hover { background:var(--cmx-blue-hover); transform:translateY(-1px); }
.cmx-action-hint         { display:flex; align-items:center; gap:6px; }
.cmx-action-hint__label  { font-size:10px; font-weight:500; text-transform:uppercase; letter-spacing:.07em; color:var(--cmx-gray-400); }
.cmx-action-hint__value  { font-size:13px; font-weight:700; color:var(--cmx-blue); }

/* ── Tabs ── */
.cmx-tabs-wrapper { 
    margin-bottom:20px; 
    display:flex; 
    flex-direction:column;
}
.cmx-tabs-nav { 
    display:flex; 
    background:var(--cmx-white); 
    border-radius:12px 12px 0 0; 
    overflow:hidden;
    border:0.5px solid var(--cmx-blue-faint);
    border-bottom:none;
    box-shadow:0 4px 12px rgba(0,0,0,.04),0 0 0 1px rgba(24,95,165,.05);
}
.cmx-tab-btn { 
    display:inline-flex; 
    align-items:center; 
    gap:7px; 
    padding:14px 20px; 
    font-size:13px; 
    font-weight:500; 
    color:var(--cmx-gray-500); 
    background:none; 
    border:none; 
    border-bottom:2px solid transparent; 
    cursor:pointer; 
    transition:all .15s; 
    white-space:nowrap;
    position:relative;
}
.cmx-tab-btn svg  { stroke:currentColor; stroke-width:1.8; fill:none; flex-shrink:0; }
.cmx-tab-btn:hover { color:var(--cmx-blue); }
.cmx-tab-btn--active { 
    color:var(--cmx-blue); 
    border-bottom-color:var(--cmx-blue);
}
/* 👇 Fix: contain the underline inside the tab button */
.cmx-tab-btn::after {
    content:'';
    position:absolute;
    bottom:0;
    left:0;
    right:0;
    height:2px;
    background:transparent;
    transition:background .15s;
}
.cmx-tab-btn--active::after {
    background:var(--cmx-blue);
}
.cmx-tab-btn--active {
    border-bottom-color:transparent; /* Remove border-based underline */
}
/* Re-apply border only for visual consistency in the tab nav */
.cmx-tab-btn--active {
    color:var(--cmx-blue);
    border-bottom:2px solid var(--cmx-blue); /* Keep the border for layout */
}

.cmx-tab-panel { 
    display:none; 
    /* 👇 Allow the panel itself to scroll on small screens */
    overflow-x:auto;
    -webkit-overflow-scrolling:touch;
}
.cmx-tab-panel--active { display:block; }

/* ── Panel / table ── */
.cmx-panel { 
    background:var(--cmx-white); 
    border:0.5px solid var(--cmx-blue-faint); 
    border-radius:0 0 12px 12px; /* 👈 Only round bottom corners when inside tabs */
    overflow:hidden; 
    box-shadow:0 4px 12px rgba(0,0,0,.04),0 0 0 1px rgba(24,95,165,.05);
}
.cmx-panel__head { 
    display:flex; 
    align-items:center; 
    gap:10px; 
    padding:16px 20px; 
    border-bottom:0.5px solid var(--cmx-gray-200); 
    background:var(--cmx-gray-50); 
    flex-wrap:wrap; 
    /* 👇 Make header sticky on mobile for better UX */
    position:sticky;
    top:0;
    z-index:10;
}
.cmx-panel__head-left { display:flex; align-items:center; gap:10px; flex:1; min-width:0; }
.cmx-panel__icon { 
    width:34px; 
    height:34px; 
    border-radius:8px; 
    background:var(--cmx-blue-light); 
    color:var(--cmx-blue); 
    display:flex; 
    align-items:center; 
    justify-content:center; 
    flex-shrink:0; 
}
.cmx-panel__title { font-size:14px; font-weight:600; color:var(--cmx-gray-900); margin:0; white-space:nowrap; }
.cmx-filter-wrap { 
    display:flex; 
    align-items:center; 
    gap:6px; 
    border:0.5px solid var(--cmx-gray-200); 
    border-radius:7px; 
    padding:5px 10px; 
    background:var(--cmx-white); 
    color:var(--cmx-gray-500); 
}
.cmx-filter-wrap:focus-within { border-color:var(--cmx-blue); box-shadow:0 0 0 3px rgba(24,95,165,.08); }
.cmx-filter-select { 
    border:none; 
    outline:none; 
    background:transparent; 
    font-size:12px; 
    color:var(--cmx-gray-700); 
    cursor:pointer; 
    padding:0; 
    max-width:140px; /* 👈 Prevent filter from being too wide on mobile */
}
.cmx-table { 
    width:100%; 
    border-collapse:collapse; 
    /* 👇 Ensure table can scroll horizontally within panel */
    min-width:600px;
}
.cmx-table thead th { 
    padding:.65rem 1rem; 
    font-size:10px; 
    font-weight:500; 
    text-transform:uppercase; 
    letter-spacing:.07em; 
    color:var(--cmx-gray-500); 
    background:var(--cmx-gray-50); 
    border-bottom:0.5px solid var(--cmx-gray-200); 
    text-align:left; 
    white-space:nowrap; 
    /* 👇 Sticky header for vertical scroll */
    position:sticky;
    top:0;
    z-index:5;
}
.cmx-table tbody td { 
    padding:.8rem 1rem; 
    font-size:13px; 
    color:var(--cmx-gray-700); 
    border-bottom:0.5px solid var(--cmx-gray-100); 
    vertical-align:middle; 
}
.cmx-table tbody tr:last-child td { border-bottom:none; }
.cmx-table tbody tr:nth-child(even) td { background:var(--cmx-gray-50); }
.cmx-table tbody tr:hover td { background:var(--cmx-gray-100); }
.cmx-td-date { 
    color:var(--cmx-gray-500); 
    white-space:nowrap; 
    font-size:12px; 
    font-weight:500; 
}
.cmx-td-amount { 
    font-variant-numeric:tabular-nums; 
    white-space:nowrap; 
}
.cmx-td-amount--total { font-weight:700; color:var(--cmx-green-dark); }
.cmx-na { color:var(--cmx-gray-200); }
.cmx-mono { 
    font-family:'SF Mono','Fira Code','Fira Mono',monospace; 
    font-size:12px; 
    color:var(--cmx-gray-700); 
    white-space:nowrap; 
}
.cmx-notes { 
    font-size:12px; 
    color:var(--cmx-gray-500); 
    font-style:italic; 
    max-width:150px; 
    display:inline-block; 
    overflow:hidden; 
    text-overflow:ellipsis; 
    white-space:nowrap; 
}
.cmx-empty { 
    text-align:center; 
    padding:3rem 1rem !important; 
    color:var(--cmx-gray-400); 
}
.cmx-empty__icon { 
    display:flex; 
    justify-content:center; 
    margin-bottom:10px; 
    color:var(--cmx-gray-200); 
}
.cmx-pagination { 
    padding:14px 20px; 
    border-top:0.5px solid var(--cmx-gray-200); 
    background:var(--cmx-gray-50); 
    display:flex; 
    justify-content:flex-end; 
}

/* ── Table responsive wrapper ── */
.table-responsive {
    overflow-x:auto;
    -webkit-overflow-scrolling:touch;
    /* 👇 Add max-height with vertical scroll for mobile */
    max-height:60vh;
    overflow-y:auto;
}

/* ── Pills & Badges ── */
.cmx-pill { 
    display:inline-flex; 
    align-items:center; 
    padding:1px 7px; 
    border-radius:99px; 
    font-size:10px; 
    font-weight:600; 
    margin-left:5px; 
}
.cmx-pill--subscription { background:var(--cmx-purple-light); color:var(--cmx-purple); }
.cmx-pill--rent         { background:var(--cmx-green-light);  color:var(--cmx-green-dark); }
.cmx-pill--marketplace  { background:var(--cmx-blue-light);   color:var(--cmx-blue); }

.cmx-status-badge {
    display:inline-flex; align-items:center; gap:5px;
    padding:3px 10px; border-radius:99px;
    font-size:11px; font-weight:600; white-space:nowrap;
}
.cmx-status-badge--pending  { background:#FEF3C7; color:#92400E; }
.cmx-status-badge--approved { background:var(--cmx-green-light); color:var(--cmx-green-dark); }
.cmx-status-badge--rejected { background:var(--cmx-red-light); color:var(--cmx-red); }

.cmx-method-badge {
    display:inline-flex; align-items:center; gap:5px;
    padding:3px 10px; border-radius:99px;
    font-size:11px; font-weight:600; white-space:nowrap;
    background:var(--cmx-gray-100); color:var(--cmx-gray-700);
}
.cmx-method-badge--mpesa { background:#E6F1FB; color:var(--cmx-blue); }

.cmx-view-btn {
    display:inline-flex; align-items:center; gap:5px; 
    padding:5px 11px; background:var(--cmx-blue-light); color:var(--cmx-blue); 
    border:0.5px solid var(--cmx-blue-border); border-radius:6px; 
    font-size:11px; font-weight:500; cursor:pointer; transition:all .13s; white-space:nowrap;
}
.cmx-view-btn:hover { background:var(--cmx-blue); color:var(--cmx-white); border-color:var(--cmx-blue); }
.cmx-view-btn svg { stroke:currentColor; flex-shrink:0; }

/* ══════════════════════════════════════════════════════
   MODAL — shared overlay + box
   ══════════════════════════════════════════════════════ */
.cmx-modal-overlay {
    position: fixed !important;
    inset: 0 !important;
    width: 100vw !important;
    height: 100vh !important;
    background: rgba(17,24,39,.5) !important;
    z-index: 999999 !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
}

.cmx-modal-box {
    background: var(--cmx-white) !important;
    border-radius: 14px !important;
    width: 100% !important;
    box-shadow: 0 20px 50px rgba(0,0,0,.2) !important;
    overflow: hidden !important;
    position: relative !important;
    z-index: 1000000 !important;
    display: flex !important;
    flex-direction: column !important;
    max-height: 90vh !important;
    transform: none !important;
}
.cmx-modal-box--sm { max-width: 460px !important; }
.cmx-modal-box--lg { max-width: 640px !important; }

.cmx-modal-box__head {
    display:flex; align-items:center; gap:12px;
    padding:20px 20px 14px;
    border-bottom:0.5px solid var(--cmx-gray-200);
    background:var(--cmx-gray-50);
    flex-shrink:0;
}
.cmx-modal-box__head-left { display:flex; align-items:center; gap:12px; flex:1; min-width:0; }
.cmx-modal-box__head-icon { flex-shrink:0; }
.cmx-modal-box__head-icon--round {
    width:38px; height:38px; border-radius:10px;
    background:var(--cmx-blue-light); color:var(--cmx-blue);
    display:flex; align-items:center; justify-content:center;
}

.cmx-modal-eyebrow { font-size:10px; font-weight:500; text-transform:uppercase; letter-spacing:.07em; color:var(--cmx-gray-400); margin:0 0 2px; }
.cmx-modal-title   { font-size:15px; font-weight:600; color:var(--cmx-gray-900); margin:0 0 2px; }
.cmx-modal-sub     { font-size:12px; color:var(--cmx-gray-500); margin:0; }

.cmx-modal-close {
    margin-left:auto; background:var(--cmx-gray-100); border:0.5px solid var(--cmx-gray-200);
    font-size:18px; color:var(--cmx-gray-500); cursor:pointer;
    width:30px; height:30px; border-radius:7px;
    display:flex; align-items:center; justify-content:center;
    line-height:1; transition:all .13s; flex-shrink:0;
}
.cmx-modal-close:hover { background:var(--cmx-gray-200); color:var(--cmx-gray-900); }

.cmx-modal-body {
    padding:20px 20px 24px;
    overflow-y:auto;
    -webkit-overflow-scrolling:touch;
    flex:1;
    /* 👇 Allow modal body to scroll independently */
    max-height:calc(90vh - 80px);
}

/* ── Withdraw modal internals ── */
.cmx-balance-row {
    display:flex; align-items:center; justify-content:space-between;
    background:var(--cmx-blue-light); border:0.5px solid var(--cmx-blue-border);
    border-radius:10px; padding:10px 14px; margin-bottom:20px;
    font-size:13px; color:var(--cmx-gray-700);
    flex-wrap:wrap; gap:8px;
}
.cmx-balance-row strong { font-size:16px; font-weight:700; color:var(--cmx-blue); }

.cmx-field           { margin-bottom:16px; }
.cmx-field__label    { display:block; font-size:10px; font-weight:500; text-transform:uppercase; letter-spacing:.07em; color:var(--cmx-gray-400); margin-bottom:6px; }
.cmx-field__input    { width:100%; padding:9px 12px; font-size:13px; border:0.5px solid var(--cmx-gray-200); border-radius:7px; color:var(--cmx-gray-900); outline:none; background:#ffffff; transition:border-color .15s; box-sizing:border-box; }
.cmx-field__input:focus { border-color:var(--cmx-blue); box-shadow:0 0 0 3px rgba(24,95,165,.1); }
.cmx-field__hint     { font-size:11px; color:var(--cmx-gray-400); margin:4px 0 0; }

.cmx-phone-wrap      { display:flex; align-items:center; border:0.5px solid var(--cmx-gray-200); border-radius:7px; overflow:hidden; transition:border-color .15s; }
.cmx-phone-wrap:focus-within { border-color:var(--cmx-blue); box-shadow:0 0 0 3px rgba(24,95,165,.1); }
.cmx-phone-prefix    { padding:9px 10px; background:var(--cmx-gray-50); font-size:13px; color:var(--cmx-gray-500); font-weight:500; border-right:0.5px solid var(--cmx-gray-200); flex-shrink:0; }
.cmx-phone-wrap .cmx-field__input { border:none; box-shadow:none; border-radius:0; }
.cmx-phone-wrap .cmx-field__input:focus { box-shadow:none; }

.cmx-warning-box {
    display:flex; align-items:flex-start; gap:8px;
    background:var(--cmx-amber-light); border:0.5px solid var(--cmx-amber-bdr);
    border-radius:8px; padding:10px 12px;
    font-size:12px; color:var(--cmx-amber); margin-bottom:20px;
}
.cmx-submit-btn {
    display:flex; align-items:center; justify-content:center; gap:10px;
    width:100%; padding:11px 20px; background:var(--cmx-blue); color:#ffffff;
    font-size:14px; font-weight:500; border:none; border-radius:7px;
    cursor:pointer; transition:all .13s;
}
.cmx-submit-btn:hover    { background:var(--cmx-blue-hover); transform:translateY(-1px); }
.cmx-submit-btn:disabled { background:#93c5e8; cursor:not-allowed; transform:none; }

/* ── Detail modal internals ── */
.cmx-detail-loading {
    display:flex; flex-direction:column; align-items:center;
    justify-content:center; gap:14px; padding:48px 20px;
    color:var(--cmx-gray-400); font-size:13px;
}
.cmx-detail-loading__spinner {
    width:32px; height:32px;
    border:3px solid var(--cmx-gray-200);
    border-top-color:var(--cmx-blue);
    border-radius:50%;
    animation:cmxSpin .7s linear infinite;
}
@keyframes cmxSpin { to { transform:rotate(360deg); } }

.cmx-detail-error {
    display:flex; flex-direction:column; align-items:center;
    gap:12px; padding:48px 20px; color:var(--cmx-gray-400);
    font-size:13px; text-align:center;
}

/* 👇 Detail tabs - now scrollable horizontally */
.cmx-detail-tabs {
    display:flex; 
    border-bottom:0.5px solid var(--cmx-gray-200);
    padding:0 20px; 
    background:#ffffff;
    overflow-x:auto; 
    -webkit-overflow-scrolling:touch; 
    scrollbar-width:none;
    /* 👇 Make tabs sticky when scrolling detail content */
    position:sticky;
    top:0;
    z-index:5;
}
.cmx-detail-tabs::-webkit-scrollbar { display:none; }
.cmx-detail-tab {
    display:inline-flex; align-items:center; gap:7px;
    padding:12px 16px; font-size:13px; font-weight:500;
    color:var(--cmx-gray-500); background:none; border:none;
    border-bottom:2px solid transparent; cursor:pointer;
    transition:color .15s, border-color .15s;
    margin-bottom:-1px; white-space:nowrap;
    flex-shrink:0;
}
.cmx-detail-tab svg { stroke:currentColor; stroke-width:1.8; fill:none; }
.cmx-detail-tab:hover { color:var(--cmx-blue); }
.cmx-detail-tab--active { color:var(--cmx-blue); border-bottom-color:var(--cmx-blue); }
.cmx-detail-tab__count {
    display:inline-flex; align-items:center; justify-content:center;
    min-width:20px; height:20px; padding:0 6px;
    background:var(--cmx-gray-100); border-radius:99px;
    font-size:11px; font-weight:600; color:var(--cmx-gray-500);
}
.cmx-detail-tab--active .cmx-detail-tab__count { background:var(--cmx-blue-light); color:var(--cmx-blue); }

/* 👇 Detail panels - scrollable to prevent truncation */
.cmx-detail-panel { 
    padding:0; 
    overflow-x:auto;
    -webkit-overflow-scrolling:touch;
    max-height:50vh;
    overflow-y:auto;
}

.cmx-detail-table { width:100%; border-collapse:collapse; min-width:500px; }
.cmx-detail-table thead th { 
    padding:.6rem 1rem; font-size:10px; font-weight:500; 
    text-transform:uppercase; letter-spacing:.07em; 
    color:var(--cmx-gray-500); background:var(--cmx-gray-50); 
    border-bottom:0.5px solid var(--cmx-gray-200); text-align:left; white-space:nowrap;
    position:sticky; top:0; z-index:3;
}
.cmx-detail-table tbody td { 
    padding:.75rem 1rem; font-size:12px; color:var(--cmx-gray-700); 
    border-bottom:0.5px solid var(--cmx-gray-100); vertical-align:middle; 
}
.cmx-detail-table tbody tr:last-child td { border-bottom:none; }
.cmx-detail-table tbody tr:hover td { background:var(--cmx-gray-50); }

.cmx-detail-empty { text-align:center; padding:2rem 1rem; color:var(--cmx-gray-400); font-size:13px; }

.cmx-type-badge { 
    display:inline-flex; align-items:center; padding:2px 8px; 
    border-radius:99px; font-size:10px; font-weight:600; 
}
.cmx-type-badge--new        { background:var(--cmx-green-light);  color:var(--cmx-green-dark); }
.cmx-type-badge--recurring  { background:var(--cmx-purple-light); color:var(--cmx-purple); }

/* ── Withdrawal table column sizing ── */
.cmx-table--withdrawals { min-width: 900px; }
.cmx-th-date    { width: 130px; }
.cmx-th-amount  { width: 110px; }
.cmx-th-phone   { width: 120px; }
.cmx-th-method  { width: 130px; }
.cmx-th-status  { width: 110px; }
.cmx-th-ref     { width: 160px; }
.cmx-th-notes   { width: 140px; }

/* ── Reference badge ── */
.cmx-ref-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 3px 9px;
    background: var(--cmx-green-light);
    border: 0.5px solid #A7DFC9;
    border-radius: 6px;
    font-family: 'SF Mono', 'Fira Code', 'Fira Mono', monospace;
    font-size: 11px;
    font-weight: 500;
    color: var(--cmx-green-dark);
    cursor: help;
    white-space: nowrap;
    max-width: 100%;
    transition: all .13s;
}
.cmx-ref-badge:hover {
    background: var(--cmx-green);
    color: #ffffff;
    border-color: var(--cmx-green);
}

/* ── Notes cell ── */
.cmx-notes-cell {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 11px;
    color: var(--cmx-gray-500);
    font-style: italic;
    cursor: help;
    max-width: 130px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    transition: color .13s;
}
.cmx-notes-cell:hover {
    color: var(--cmx-gray-700);
}

/* Override old cmx-notes style */
.cmx-notes {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 11px;
    color: var(--cmx-gray-500);
    font-style: italic;
    cursor: help;
    max-width: 130px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

/* ── Responsive ── */
@media(max-width:1100px) { 
    .cmx-stats-grid { grid-template-columns:repeat(2,1fr); } 
}
@media(max-width:768px) {
    .cmx-stats-grid  { grid-template-columns:repeat(2,1fr); gap:12px; }
    .cmx-stat-card { padding:16px; }
    .cmx-stat-card__value { font-size:20px; }
}
@media(max-width:640px) {
    .cmx-stats-grid  { grid-template-columns:1fr; }
    .cmx-action-row  { flex-direction:column; align-items:flex-start; }
    
    /* 👇 Tabs - scrollable on mobile */
    .cmx-tabs-nav { 
        overflow-x:auto; 
        -webkit-overflow-scrolling:touch;
        scrollbar-width:none;
        border-radius:12px 12px 0 0;
    }
    .cmx-tabs-nav::-webkit-scrollbar { display:none; }
    .cmx-tab-btn { 
        padding:12px 16px; 
        font-size:12px; 
        flex-shrink:0;
    }
    
    /* 👇 Panel head - stack on mobile */
    .cmx-panel__head { 
        flex-direction:column; 
        align-items:flex-start; 
        gap:10px;
    }
    .cmx-panel__head-left { width:100%; }
    .cmx-filter-wrap { width:100%; }
    .cmx-filter-select { max-width:100%; width:100%; }
    
    /* 👇 Table responsive */
    .table-responsive { 
        max-height:50vh; 
        border-radius:0;
    }
    .cmx-table { min-width:550px; }
    
    /* 👇 Modal - bottom sheet on mobile */
    .cmx-modal-overlay { 
        align-items:flex-end !important; 
    }
    .cmx-modal-box { 
        border-radius:16px 16px 0 0 !important; 
        max-width:100% !important; 
        max-height:92vh !important; 
    }
    .cmx-modal-body { 
        max-height:calc(92vh - 80px); 
        padding:16px 16px 20px;
    }
    
    /* 👇 Detail modal - full width on mobile */
    .cmx-modal-box--lg { 
        max-width:100% !important; 
    }
    .cmx-detail-tabs { 
        padding:0 12px; 
    }
    .cmx-detail-panel { 
        max-height:40vh; 
    }
    .cmx-detail-table { 
        min-width:450px; 
    }
}

/* 👇 Extra small screens */
@media(max-width:380px) {
    .cmx-tab-btn { 
        padding:10px 12px; 
        font-size:11px; 
        gap:5px;
    }
    .cmx-tab-btn svg { width:12px; height:12px; }
    .cmx-stat-card__value { font-size:18px; }
}
</style>
@endpush

@push('scripts')
<script>
(function () {
    'use strict';

    /* ──────────────────────────────────────────────────────────────
       STEP 1 — Wait for DOM to be ready, THEN teleport modals
       This ensures they escape any stacking context from the layout
    ────────────────────────────────────────────────────────────── */
    function initModals() {
        var wdModal  = document.getElementById('cmxWithdrawModal');
        var dtModal  = document.getElementById('cmxDetailModal');

        if (wdModal && wdModal.parentNode !== document.body) {
            document.body.appendChild(wdModal);
            wdModal.style.setProperty('display', 'none', 'important');
        }
        if (dtModal && dtModal.parentNode !== document.body) {
            document.body.appendChild(dtModal);
            dtModal.style.setProperty('display', 'none', 'important');
        }

        // Now that modals are in body, initialize everything else
        initFunctionality();
    }

    /* ──────────────────────────────────────────────────────────────
       STEP 2 — Generic open / close helpers
    ────────────────────────────────────────────────────────────── */
    function openModal(el) {
        if (!el) return;
        el.style.removeProperty('display');
        el.style.setProperty('display', 'flex', 'important');
        document.body.style.overflow = 'hidden';
    }
    
    function closeModal(el) {
        if (!el) return;
        el.style.setProperty('display', 'none', 'important');
        document.body.style.overflow = '';
    }

    /* ──────────────────────────────────────────────────────────────
       STEP 3 — Initialize all functionality after teleport
    ────────────────────────────────────────────────────────────── */
    function initFunctionality() {
        var wdModal  = document.getElementById('cmxWithdrawModal');
        var dtModal  = document.getElementById('cmxDetailModal');
        
        /* ═════════════════════════════════════════════════════
           TAB SWITCHING
           ═════════════════════════════════════════════════════ */
        document.querySelectorAll('.cmx-tab-btn').forEach(function (tabBtn) {
            tabBtn.addEventListener('click', function () {
                // Deactivate all tabs
                document.querySelectorAll('.cmx-tab-btn').forEach(function (b) {
                    b.classList.remove('cmx-tab-btn--active');
                });
                document.querySelectorAll('.cmx-tab-panel').forEach(function (p) {
                    p.classList.remove('cmx-tab-panel--active');
                });
                // Activate clicked tab
                this.classList.add('cmx-tab-btn--active');
                var panelId = 'cmxTab' + this.dataset.tab.charAt(0).toUpperCase() + this.dataset.tab.slice(1);
                var panel = document.getElementById(panelId);
                if (panel) panel.classList.add('cmx-tab-panel--active');
            });
        });

        /* ═════════════════════════════════════════════════════
           COMMISSION SOURCE FILTER
           ═════════════════════════════════════════════════════ */
        var sourceFilter = document.getElementById('cmxSourceFilter');
        if (sourceFilter) {
            sourceFilter.addEventListener('change', function () {
                var val = this.value;
                document.querySelectorAll('#cmxTable tbody tr[data-month]').forEach(function (row) {
                    if (!val) { row.style.display = ''; return; }
                    var show = (val === 'subscription' && row.dataset.hasSubscription === 'subscription')
                            || (val === 'rent'         && row.dataset.hasRent         === 'rent')
                            || (val === 'marketplace'  && row.dataset.hasMarketplace  === 'marketplace');
                    row.style.display = show ? '' : 'none';
                });
            });
        }

        /* ═════════════════════════════════════════════════════
           WITHDRAWAL STATUS FILTER
           ═════════════════════════════════════════════════════ */
        var withdrawalFilter = document.getElementById('cmxWithdrawalStatusFilter');
        if (withdrawalFilter) {
            withdrawalFilter.addEventListener('change', function () {
                var val = this.value;
                document.querySelectorAll('#cmxWithdrawalTable tbody tr[data-status]').forEach(function (row) {
                    if (!val) { row.style.display = ''; return; }
                    row.style.display = row.dataset.status === val ? '' : 'none';
                });
            });
        }

        /* ═════════════════════════════════════════════════════
           WITHDRAW MODAL
           ═════════════════════════════════════════════════════ */
        var openWdBtn   = document.getElementById('cmxOpenWithdrawModal');
        var closeWdBtn  = document.getElementById('cmxCloseWithdrawModal');
        var amountInput = document.getElementById('cmxWithdrawAmount');
        var submitBtn   = document.getElementById('cmxWithdrawSubmitBtn');
        var wdForm      = document.getElementById('cmxWithdrawForm');
        var balance     = parseFloat((openWdBtn ? openWdBtn.getAttribute('data-balance') : 0) || 0);

        function doCloseWd() {
            closeModal(wdModal);
            if (amountInput) amountInput.value = '';
            if (submitBtn) { 
                submitBtn.disabled = false; 
                submitBtn.innerHTML = '<img src="{{ asset('assets/images/gateway-icon/mpesa.jpg') }}" alt="" style="width:20px;height:20px;border-radius:4px;object-fit:cover;"> {{ __("Send to M-Pesa") }}';
            }
        }

        if (openWdBtn) {
            openWdBtn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                openModal(wdModal);
                if (amountInput) { 
                    amountInput.value = ''; 
                    setTimeout(function() { amountInput.focus(); }, 100);
                }
            });
        }

        if (closeWdBtn) closeWdBtn.addEventListener('click', doCloseWd);
        if (wdModal) wdModal.addEventListener('click', function (e) { 
            if (e.target === wdModal) doCloseWd(); 
        });

        if (amountInput) {
            amountInput.addEventListener('input', function () {
                var val = parseFloat(this.value);
                if (isNaN(val)) val = 0;
                if (val > balance) {
                    this.setCustomValidity('{{ __("Amount exceeds available balance.") }}');
                    if (submitBtn) submitBtn.disabled = true;
                } else {
                    this.setCustomValidity('');
                    if (submitBtn) submitBtn.disabled = false;
                }
            });
        }

        if (wdForm) {
            wdForm.addEventListener('submit', function (e) {
                e.preventDefault();
                var phone  = document.getElementById('cmxWithdrawPhone').value.trim();
                var amount = parseFloat(amountInput ? amountInput.value : 0);
                
                if (!/^[71]\d{8}$/.test(phone)) { 
                    toastr.error('{{ __("Enter a valid Safaricom number (e.g. 712345678).") }}'); 
                    return; 
                }
                if (!amount || amount <= 0) { 
                    toastr.error('{{ __("Enter a valid withdrawal amount.") }}'); 
                    return; 
                }
                if (amount > balance) { 
                    toastr.error('{{ __("Amount exceeds your available balance.") }}'); 
                    return; 
                }
                
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.textContent = '{{ __("Processing…") }}';
                }
                
                fetch(wdForm.action, {
                    method: 'POST',
                    headers: { 
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: new FormData(wdForm),
                })
                .then(function (r) { return r.json(); })
                .then(function (d) {
                    if (d.success) {
                        doCloseWd();
                        toastr.success(d.message || '{{ __("Withdrawal request submitted.") }}');
                        setTimeout(function () { location.reload(); }, 2000);
                    } else {
                        toastr.error(d.error || '{{ __("Withdrawal failed. Please try again.") }}');
                        if (submitBtn) {
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = '<img src="{{ asset('assets/images/gateway-icon/mpesa.jpg') }}" alt="" style="width:20px;height:20px;border-radius:4px;object-fit:cover;"> {{ __("Send to M-Pesa") }}';
                        }
                    }
                })
                .catch(function () {
                    toastr.error('{{ __("Something went wrong. Please try again.") }}');
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = '<img src="{{ asset('assets/images/gateway-icon/mpesa.jpg') }}" alt="" style="width:20px;height:20px;border-radius:4px;object-fit:cover;"> {{ __("Send to M-Pesa") }}';
                    }
                });
            });
        }

        /* ═════════════════════════════════════════════════════
           DETAIL MODAL
           ═════════════════════════════════════════════════════ */
        var dtLoading = document.getElementById('cmxDetailLoading');
        var dtContent = document.getElementById('cmxDetailContent');
        var dtError   = document.getElementById('cmxDetailError');
        var closeDtBtn = document.getElementById('cmxCloseDetailModal');

        function doCloseDt() { closeModal(dtModal); }

        if (closeDtBtn) closeDtBtn.addEventListener('click', doCloseDt);
        if (dtModal) dtModal.addEventListener('click', function (e) { 
            if (e.target === dtModal) doCloseDt(); 
        });

        function showDtState(state) {
            if (dtLoading) dtLoading.style.display = state === 'loading' ? 'flex'  : 'none';
            if (dtContent) dtContent.style.display = state === 'content' ? 'block' : 'none';
            if (dtError)   dtError.style.display   = state === 'error'   ? 'flex'  : 'none';
        }

        /* Tab switching inside detail modal */
        document.querySelectorAll('.cmx-detail-tab').forEach(function (tab) {
            tab.addEventListener('click', function () {
                document.querySelectorAll('.cmx-detail-tab').forEach(function (t) { 
                    t.classList.remove('cmx-detail-tab--active'); 
                });
                document.querySelectorAll('.cmx-detail-panel').forEach(function (p) { 
                    p.style.display = 'none'; 
                });
                this.classList.add('cmx-detail-tab--active');
                var panel = document.getElementById('cmx-tab-' + this.dataset.tab);
                if (panel) panel.style.display = 'block';
            });
        });

        function fmt(val) {
            return 'KSh ' + parseFloat(val || 0).toLocaleString('en-KE', { 
                minimumFractionDigits: 2, 
                maximumFractionDigits: 2 
            });
        }

        function populateDetail(data) {
            var titleEl = document.getElementById('cmxDetailPeriodTitle');
            if (titleEl) titleEl.textContent = data.period || '—';

            /* Subscriptions */
            var subBody  = document.getElementById('cmxSubRows');
            var subEmpty = document.getElementById('cmxSubEmpty');
            if (subBody) {
                subBody.innerHTML = '';
                if (data.subscription && data.subscription.length) {
                    data.subscription.forEach(function (r) {
                        subBody.innerHTML += '<tr>'
                            + '<td class="cmx-td-date">' + (r.date || '—') + '</td>'
                            + '<td>' + (r.owner || '—') + '</td>'
                            + '<td><span class="cmx-type-badge cmx-type-badge--' + (r.type === 'New Client' ? 'new' : 'recurring') + '">' + (r.type || '—') + '</span></td>'
                            + '<td>' + fmt(r.subscription_amount) + '</td>'
                            + '<td>' + (r.rate || '—') + '%</td>'
                            + '<td style="font-weight:600;color:var(--cmx-green-dark)">' + fmt(r.commission_amount) + '</td>'
                            + '</tr>';
                    });
                    if (subEmpty) subEmpty.style.display = 'none';
                } else {
                    if (subEmpty) subEmpty.style.display = 'block';
                }
            }
            var subCount = document.getElementById('cmxSubCount');
            if (subCount) subCount.textContent = data.subscription ? data.subscription.length : 0;

            /* Rent */
            var rentBody  = document.getElementById('cmxRentRows');
            var rentEmpty = document.getElementById('cmxRentEmpty');
            if (rentBody) {
                rentBody.innerHTML = '';
                if (data.rent && data.rent.length) {
                    data.rent.forEach(function (r) {
                        rentBody.innerHTML += '<tr>'
                            + '<td class="cmx-td-date">' + (r.date || '—') + '</td>'
                            + '<td>' + (r.owner || '—') + '</td>'
                            + '<td>' + (r.rate || '—') + '%</td>'
                            + '<td style="font-weight:600;color:var(--cmx-green-dark)">' + fmt(r.commission_amount) + '</td>'
                            + '</tr>';
                    });
                    if (rentEmpty) rentEmpty.style.display = 'none';
                } else {
                    if (rentEmpty) rentEmpty.style.display = 'block';
                }
            }
            var rentCount = document.getElementById('cmxRentCount');
            if (rentCount) rentCount.textContent = data.rent ? data.rent.length : 0;

            /* Marketplace */
            var marketBody  = document.getElementById('cmxMarketRows');
            var marketEmpty = document.getElementById('cmxMarketEmpty');
            if (marketBody) {
                marketBody.innerHTML = '';
                if (data.marketplace && data.marketplace.length) {
                    data.marketplace.forEach(function (r) {
                        marketBody.innerHTML += '<tr>'
                            + '<td class="cmx-td-date">' + (r.date || '—') + '</td>'
                            + '<td>' + (r.owner || '—') + '</td>'
                            + '<td>' + (r.rate || '—') + '%</td>'
                            + '<td style="font-weight:600;color:var(--cmx-green-dark)">' + fmt(r.commission_amount) + '</td>'
                            + '</tr>';
                    });
                    if (marketEmpty) marketEmpty.style.display = 'none';
                } else {
                    if (marketEmpty) marketEmpty.style.display = 'block';
                }
            }
            var marketCount = document.getElementById('cmxMarketCount');
            if (marketCount) marketCount.textContent = data.marketplace ? data.marketplace.length : 0;

            /* Reset tabs to first */
            document.querySelectorAll('.cmx-detail-tab').forEach(function (t) { 
                t.classList.remove('cmx-detail-tab--active'); 
            });
            document.querySelectorAll('.cmx-detail-panel').forEach(function (p) { 
                p.style.display = 'none'; 
            });
            var firstTab = document.querySelector('.cmx-detail-tab[data-tab="subscription"]');
            if (firstTab) firstTab.classList.add('cmx-detail-tab--active');
            var firstPanel = document.getElementById('cmx-tab-subscription');
            if (firstPanel) firstPanel.style.display = 'block';
        }

        /* Delegate clicks on View buttons */
        document.addEventListener('click', function (e) {
            var btn = e.target.closest('.cmx-view-btn');
            if (!btn) return;
            e.preventDefault();
            var url = btn.getAttribute('data-url');
            if (!url) return;
            openModal(dtModal);
            showDtState('loading');
            fetch(url, { 
                headers: { 
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                } 
            })
            .then(function (r) { 
                if (!r.ok) throw new Error('HTTP ' + r.status); 
                return r.json(); 
            })
            .then(function (res) {
                if (res.success && res.data) { 
                    populateDetail(res.data); 
                    showDtState('content'); 
                } else { 
                    showDtState('error'); 
                }
            })
            .catch(function () { 
                showDtState('error'); 
            });
        });

        /* Global escape key handler */
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') { 
                doCloseWd();
                doCloseDt();
            }
        });
    }

    function doCloseWd() {
        closeModal(document.getElementById('cmxWithdrawModal'));
        var amountInput = document.getElementById('cmxWithdrawAmount');
        var submitBtn = document.getElementById('cmxWithdrawSubmitBtn');
        if (amountInput) amountInput.value = '';
        if (submitBtn) { 
            submitBtn.disabled = false; 
            submitBtn.innerHTML = '<img src="{{ asset('assets/images/gateway-icon/mpesa.jpg') }}" alt="" style="width:20px;height:20px;border-radius:4px;object-fit:cover;"> {{ __("Send to M-Pesa") }}';
        }
    }

    function doCloseDt() {
        closeModal(document.getElementById('cmxDetailModal'));
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initModals);
    } else {
        initModals();
    }

}());
</script>
@endpush