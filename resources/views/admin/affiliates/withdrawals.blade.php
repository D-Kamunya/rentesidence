@extends('admin.layouts.app')

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="page-content-wrapper bg-white p-30 radius-20">
                <div class="container-fluid">
                    @php
                        $pageTitle = 'Accounts';
                    @endphp
                    {{-- ── Page Header ── --}}
                    <div class="aw-header">
                        <div>
                            <nav aria-label="breadcrumb">
                                <ol class="aw-breadcrumb">
                                    <li><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
                                    <li aria-current="page">{{ __('Affiliate Accounts and Withdrawals') }}</li>
                                </ol>
                            </nav>
                            <h1 class="aw-page-title">{{ __('Affiliate Accounts and Withdrawals') }}</h1>
                            <p class="aw-page-sub">{{ __('Review and process affiliate payout requests') }}</p>
                        </div>
                    </div>

                    {{-- ── KPI Strip ── --}}
                    <div class="aw-kpi-grid">
                        <div class="aw-kpi aw-kpi--amber">
                            <div class="aw-kpi__top">
                                <div class="aw-kpi__icon aw-kpi__icon--amber">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.8"/><polyline points="12 6 12 12 16 14" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                                </div>
                                <span class="aw-kpi__label">{{ __('Pending') }}</span>
                            </div>
                            <p class="aw-kpi__value">{{ $pendingCount }}</p>
                            <p class="aw-kpi__sub">KSh {{ number_format($pendingAmount, 2) }} {{ __('requested') }}</p>
                            <div class="aw-kpi__bar aw-kpi__bar--amber"></div>
                        </div>
                        <div class="aw-kpi aw-kpi--green">
                            <div class="aw-kpi__top">
                                <div class="aw-kpi__icon aw-kpi__icon--green">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none"><polyline points="20 6 9 17 4 12" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </div>
                                <span class="aw-kpi__label">{{ __('Approved') }}</span>
                            </div>
                            <p class="aw-kpi__value">{{ $approvedCount }}</p>
                            <p class="aw-kpi__sub">KSh {{ number_format($approvedAmount, 2) }} {{ __('paid out') }}</p>
                            <div class="aw-kpi__bar aw-kpi__bar--green"></div>
                        </div>
                        <div class="aw-kpi aw-kpi--red">
                            <div class="aw-kpi__top">
                                <div class="aw-kpi__icon aw-kpi__icon--red">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none"><line x1="18" y1="6" x2="6" y2="18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><line x1="6" y1="6" x2="18" y2="18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                                </div>
                                <span class="aw-kpi__label">{{ __('Rejected') }}</span>
                            </div>
                            <p class="aw-kpi__value">{{ $rejectedCount }}</p>
                            <p class="aw-kpi__sub">KSh {{ number_format($rejectedAmount, 2) }} {{ __('returned') }}</p>
                            <div class="aw-kpi__bar aw-kpi__bar--red"></div>
                        </div>
                        <div class="aw-kpi aw-kpi--blue">
                            <div class="aw-kpi__top">
                                <div class="aw-kpi__icon aw-kpi__icon--blue">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </div>
                                <span class="aw-kpi__label">{{ __('Total Affiliates') }}</span>
                            </div>
                            <p class="aw-kpi__value">{{ $totalAffiliates }}</p>
                            <p class="aw-kpi__sub">{{ __('with withdrawal activity') }}</p>
                            <div class="aw-kpi__bar aw-kpi__bar--blue"></div>
                        </div>
                    </div>

                    {{-- ── Affiliate Overview Cards ── --}}
                    @if($affiliateSummaries->isNotEmpty())
                    <div class="aw-section">
                        <div class="aw-section__head">
                            <h3 class="aw-section__title">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><circle cx="9" cy="7" r="4" stroke="currentColor" stroke-width="2"/></svg>
                                {{ __('Affiliate Overview') }}
                            </h3>
                            <span class="aw-section__count">{{ $affiliateSummaries->count() }} {{ __('affiliates') }}</span>
                        </div>
                        <div class="aw-affiliate-grid">
                            @foreach($affiliateSummaries->take(6) as $aff)
                            <div class="aw-affiliate-card @if($aff->pending_count > 0) aw-affiliate-card--attention @endif"
                                 data-affiliate-id="{{ $aff->id }}"
                                 data-affiliate-name="{{ $aff->user->name ?? 'Affiliate' }}"
                                 onclick="openEarningsPanel({{ $aff->id }}, '{{ addslashes($aff->user->name ?? 'Affiliate') }}')"
                                 style="cursor:pointer">
                                <div class="aw-affiliate-card__top">
                                    <div class="aw-avatar aw-avatar--lg">{{ strtoupper(substr($aff->user->name ?? 'A', 0, 1)) }}</div>
                                    <div class="aw-affiliate-card__info">
                                        <p class="aw-affiliate-card__name">{{ $aff->user->name ?? '—' }}</p>
                                        <p class="aw-affiliate-card__email">{{ $aff->user->email ?? '' }}</p>
                                    </div>
                                    @if($aff->pending_count > 0)
                                    <span class="aw-pending-dot" title="{{ __('Has pending withdrawals') }}">
                                        <svg width="8" height="8" viewBox="0 0 8 8"><circle cx="4" cy="4" r="4" fill="#F59E0B"/></svg>
                                    </span>
                                    @endif
                                </div>
                                <div class="aw-affiliate-card__stats">
                                    <div class="aw-affiliate-card__stat">
                                        <span class="aw-affiliate-card__stat-label">{{ __('Withdrawn') }}</span>
                                        <span class="aw-affiliate-card__stat-value">KSh {{ number_format($aff->total_withdrawn ?? 0, 2) }}</span>
                                    </div>
                                    <div class="aw-affiliate-card__stat">
                                        <span class="aw-affiliate-card__stat-label">{{ __('Pending') }}</span>
                                        <span class="aw-affiliate-card__stat-value aw-affiliate-card__stat-value--amber">KSh {{ number_format($aff->total_pending ?? 0, 2) }}</span>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @if($affiliateSummaries->count() > 6)
                        <div class="aw-show-more">
                            <button class="aw-show-more-btn" id="showAllAffiliates">
                                {{ __('Show all :count affiliates', ['count' => $affiliateSummaries->count()]) }}
                            </button>
                        </div>
                        @endif
                    </div>
                    @endif

                    {{-- ── Withdrawals Table ── --}}
                    <div class="aw-section">
                        <div class="aw-section__head">
                            <h3 class="aw-section__title">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M12 5v14M5 12l7 7 7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                {{ __('Withdrawal Requests') }}
                            </h3>
                            <div class="aw-section__actions">
                                @if($pendingCount)
                                <span class="aw-count-badge">{{ $pendingCount }} {{ __('pending') }}</span>
                                @endif
                                <div class="aw-filter__wrap">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    <select id="statusFilter" class="aw-filter__select">
                                        <option value="">{{ __('All Statuses') }}</option>
                                        <option value="0">{{ __('Pending') }}</option>
                                        <option value="1">{{ __('Approved') }}</option>
                                        <option value="2">{{ __('Rejected') }}</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="aw-table" id="awTable">
                                <thead>
                                    <tr>
                                        <th>{{ __('Affiliate') }}</th>
                                        <th>{{ __('Amount') }}</th>
                                        <th>{{ __('Phone') }}</th>
                                        <th>{{ __('Method') }}</th>
                                        <th>{{ __('Requested') }}</th>
                                        <th>{{ __('Status') }}</th>
                                        <th>{{ __('Notes') }}</th>
                                        <th>{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($withdrawals as $wd)
                                    <tr data-status="{{ $wd->status }}">
                                        <td>
                                            <div class="aw-owner-cell">
                                                <div class="aw-avatar">{{ strtoupper(substr($wd->affiliate->user->name ?? 'A', 0, 1)) }}</div>
                                                <div>
                                                    <a href="javascript:void(0)" 
                                                       class="aw-owner-name aw-owner-name--link"
                                                       onclick="openEarningsPanel({{ $wd->affiliate->id }}, '{{ addslashes($wd->affiliate->user->name ?? 'Affiliate') }}')"
                                                       title="{{ __('View earnings') }}">
                                                        {{ $wd->affiliate->user->name ?? '—' }}
                                                    </a>
                                                    <div class="aw-owner-email">{{ $wd->affiliate->user->email ?? '' }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td><span class="aw-amount-badge">KSh {{ number_format($wd->amount, 2) }}</span></td>
                                        <td class="aw-muted">{{ $wd->phone ?? '—' }}</td>
                                        <td>
                                            @if($wd->settlement_method === 'manual')
                                                <span class="aw-method-badge aw-method-badge--manual">{{ __('Manual') }}</span>
                                            @else
                                                <span class="aw-method-badge aw-method-badge--b2c">
                                                    <img src="{{ asset('assets/images/gateway-icon/mpesa.jpg') }}" alt="" style="width:14px;height:14px;border-radius:3px;object-fit:cover;">
                                                    {{ __('M-Pesa B2C') }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="aw-date">{{ $wd->created_at->diffForHumans() }}</td>
                                        <td>
                                            @if($wd->status == AFFILIATE_WITHDRAWAL_PENDING)
                                                <span class="aw-status-badge aw-status-badge--pending">{{ __('Pending') }}</span>
                                            @elseif($wd->status == AFFILIATE_WITHDRAWAL_APPROVED)
                                                <span class="aw-status-badge aw-status-badge--approved">{{ __('Approved') }}</span>
                                            @else
                                                <span class="aw-status-badge aw-status-badge--rejected">{{ __('Rejected') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($wd->notes)
                                            <span class="aw-notes-cell" title="{{ $wd->notes }}">
                                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                                {{ Str::limit($wd->notes, 30) }}
                                            </span>
                                            @else
                                            <span class="aw-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($wd->status == AFFILIATE_WITHDRAWAL_PENDING)
                                            <div class="aw-action-group">
                                                <button class="aw-btn aw-btn--approve"
                                                        data-id="{{ $wd->id }}"
                                                        data-name="{{ $wd->affiliate->user->name ?? 'Affiliate' }}"
                                                        data-amount="{{ number_format($wd->amount, 2) }}"
                                                        data-phone="{{ $wd->phone ?? '' }}">
                                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none"><polyline points="20 6 9 17 4 12" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                                    {{ __('Approve') }}
                                                </button>
                                                <button class="aw-btn aw-btn--reject"
                                                        data-id="{{ $wd->id }}"
                                                        data-name="{{ $wd->affiliate->user->name ?? 'Affiliate' }}"
                                                        data-amount="{{ number_format($wd->amount, 2) }}">
                                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none"><line x1="18" y1="6" x2="6" y2="18" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/><line x1="6" y1="6" x2="18" y2="18" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/></svg>
                                                    {{ __('Reject') }}
                                                </button>
                                            </div>
                                            @else
                                            <span class="aw-muted">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="8" class="aw-empty">
                                            <div class="aw-empty__icon">
                                                <svg width="28" height="28" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.5"/><line x1="8" y1="12" x2="16" y2="12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                                            </div>
                                            <p>{{ __('No withdrawal requests found.') }}</p>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @if($withdrawals->hasPages())
                        <div class="aw-pagination">{{ $withdrawals->links() }}</div>
                        @endif
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════
     EARNINGS SLIDE-OUT PANEL (NEW)
     ══════════════════════════════════════════════════════ --}}
<div id="earningsPanel" class="aep-overlay" data-state="closed">
    <div class="aep-panel">
        <div class="aep-panel__head">
            <div class="aep-panel__head-left">
                <div class="aw-avatar aw-avatar--lg" id="aepAvatar">A</div>
                <div>
                    <h3 class="aep-panel__title" id="aepName">—</h3>
                    <p class="aep-panel__sub" id="aepEmail">—</p>
                </div>
            </div>
            <button class="aep-panel__close" id="closeEarningsPanel">&times;</button>
        </div>

        <div class="aep-panel__body">
            {{-- Loading --}}
            <div id="aepLoading" class="aep-loading">
                <div class="aep-loading__spinner"></div>
                <p>{{ __('Loading earnings data…') }}</p>
            </div>

            {{-- Content --}}
            <div id="aepContent" style="display:none;">
                {{-- Stats --}}
                <div class="aep-stats-grid">
                    <div class="aep-stat">
                        <span class="aep-stat__label">{{ __('Available Balance') }}</span>
                        <span class="aep-stat__value aep-stat__value--blue" id="aepAvailable">—</span>
                    </div>
                    <div class="aep-stat">
                        <span class="aep-stat__label">{{ __('Lifetime Earned') }}</span>
                        <span class="aep-stat__value aep-stat__value--green" id="aepLifetime">—</span>
                    </div>
                    <div class="aep-stat">
                        <span class="aep-stat__label">{{ __('Total Withdrawn') }}</span>
                        <span class="aep-stat__value aep-stat__value--purple" id="aepWithdrawn">—</span>
                    </div>
                    <div class="aep-stat">
                        <span class="aep-stat__label">{{ __('This Month') }}</span>
                        <span class="aep-stat__value aep-stat__value--amber" id="aepMonth">—</span>
                    </div>
                </div>

                <!-- Pending Withdrawals -->
                <div id="aepPendingSection" style="display:none;" class="aep-alert aep-alert--warning">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/><polyline points="12 6 12 12 16 14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                    <div>
                        <strong id="aepPendingCount">0 {{ __('pending withdrawal(s)') }}</strong>
                        <span id="aepPendingAmount">—</span>
                    </div>
                </div>

                <!-- Monthly Commissions -->
                <div class="aep-subsection">
                    <h4 class="aep-subsection__title">{{ __('Monthly Commissions (Last 12 Months)') }}</h4>
                    <div class="table-responsive" style="max-height:200px;">
                        <table class="aep-table" id="aepMonthlyTable">
                            <thead>
                                <tr>
                                    <th>{{ __('Period') }}</th>
                                    <th>{{ __('Subscriptions') }}</th>
                                    <th>{{ __('Rent') }}</th>
                                    <th>{{ __('Marketplace') }}</th>
                                    <th>{{ __('Total') }}</th>
                                </tr>
                            </thead>
                            <tbody id="aepMonthlyBody"></tbody>
                        </table>
                    </div>
                </div>

                <!-- Recent Withdrawals  -->
                <div class="aep-subsection">
                    <h4 class="aep-subsection__title">{{ __('Recent Withdrawals') }}</h4>
                    <div class="table-responsive" style="max-height:200px;">
                        <table class="aep-table" id="aepWithdrawalTable">
                            <thead>
                                <tr>
                                    <th>{{ __('Date') }}</th>
                                    <th>{{ __('Amount') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Notes') }}</th>
                                </tr>
                            </thead>
                            <tbody id="aepWithdrawalBody"></tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Error -->
            <div id="aepError" style="display:none;" class="aep-error">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.5"/><line x1="12" y1="8" x2="12" y2="12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><line x1="12" y1="16" x2="12.01" y2="16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                <p>{{ __('Could not load earnings data.') }}</p>
            </div>
        </div>
    </div>
</div>

{{-- ── Approve Modal ── --}}
<div id="approveModal" class="apv-overlay" data-state="closed" aria-modal="true" role="dialog">
    <div class="apv-box">
        <div class="apv-box__head">
            <div class="apv-box__icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none"><path d="M12 5v14M5 12l7 7 7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </div>
            <div>
                <p class="apv-box__eyebrow">{{ __('Affiliate Payout') }}</p>
                <h5 class="apv-box__title">{{ __('Approve Withdrawal') }}</h5>
            </div>
            <button type="button" class="apv-box__close" id="closeApproveModal">&times;</button>
        </div>
        <div class="apv-box__body">
            <div class="apv-summary">
                <div class="apv-summary__row">
                    <span class="apv-summary__label">{{ __('Affiliate') }}</span>
                    <span class="apv-summary__value" id="apvName">—</span>
                </div>
                <div class="apv-summary__row">
                    <span class="apv-summary__label">{{ __('Phone') }}</span>
                    <span class="apv-summary__value" id="apvPhone">—</span>
                </div>
                <div class="apv-summary__row apv-summary__row--highlight">
                    <span class="apv-summary__label">{{ __('Amount') }}</span>
                    <strong class="apv-summary__amount" id="apvAmount">—</strong>
                </div>
            </div>

            {{-- Method toggle --}}
            <div class="apv-method-row">
                <span class="apv-method-label">{{ __('Settlement Method') }}</span>
                <div class="apv-method-toggle">
                    <button type="button" class="apv-method-btn apv-method-btn--active" id="methodB2c">
                        <img src="{{ asset('assets/images/gateway-icon/mpesa.jpg') }}" alt="" style="width:16px;height:16px;border-radius:3px;object-fit:cover;">
                        {{ __('M-Pesa B2C') }}
                    </button>
                    <button type="button" class="apv-method-btn" id="methodManual">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M9 11l3 3L22 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        {{ __('Manual') }}
                    </button>
                </div>
            </div>

            {{-- B2C warning --}}
            <div id="b2cWarning" class="apv-warning">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" style="flex-shrink:0;margin-top:1px"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><line x1="12" y1="9" x2="12" y2="13" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><line x1="12" y1="17" x2="12.01" y2="17" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                <span>{{ __('This will trigger an M-Pesa B2C transfer. This action cannot be reversed once sent.') }}</span>
            </div>

            {{-- Notes (required for manual, optional label changes) --}}
            <div class="apv-field">
                <label class="apv-field__label" id="notesLabel">{{ __('Settlement Notes') }} <span class="apv-required">*</span></label>
                <textarea id="apvNotes" class="apv-field__textarea" rows="3"
                          placeholder="{{ __('e.g. Paid via bank transfer ref #12345…') }}"></textarea>
                <p class="apv-field__hint" id="notesHint">{{ __('Required — document how the payment was made.') }}</p>
            </div>

            <div class="apv-box__footer">
                <button type="button" class="apv-btn apv-btn--cancel" id="cancelApproveModal">{{ __('Cancel') }}</button>
                <button type="button" class="apv-btn apv-btn--confirm" id="confirmApproveBtn">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><polyline points="20 6 9 17 4 12" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    {{ __('Confirm & Send') }}
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ── Reject Modal (NEW) ── --}}
<div id="rejectModal" class="apv-overlay" data-state="closed" aria-modal="true" role="dialog">
    <div class="apv-box">
        <div class="apv-box__head">
            <div class="apv-box__icon apv-box__icon--reject">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none"><line x1="18" y1="6" x2="6" y2="18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><line x1="6" y1="6" x2="18" y2="18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
            </div>
            <div>
                <p class="apv-box__eyebrow">{{ __('Reject Request') }}</p>
                <h5 class="apv-box__title">{{ __('Reject Withdrawal') }}</h5>
            </div>
            <button type="button" class="apv-box__close" id="closeRejectModal">&times;</button>
        </div>
        <div class="apv-box__body">
            <div class="apv-summary">
                <div class="apv-summary__row">
                    <span class="apv-summary__label">{{ __('Affiliate') }}</span>
                    <span class="apv-summary__value" id="rjName">—</span>
                </div>
                <div class="apv-summary__row apv-summary__row--highlight">
                    <span class="apv-summary__label">{{ __('Amount') }}</span>
                    <strong class="apv-summary__amount" id="rjAmount">—</strong>
                </div>
            </div>

            <div class="apv-warning">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" style="flex-shrink:0;margin-top:1px"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><line x1="12" y1="9" x2="12" y2="13" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><line x1="12" y1="17" x2="12.01" y2="17" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                <span>{{ __('The funds will be returned to the affiliate\'s available balance. This action cannot be reversed.') }}</span>
            </div>

            <div class="apv-field">
                <label class="apv-field__label">{{ __('Reason for Rejection') }} <span class="apv-required">*</span></label>
                <textarea id="rjNotes" class="apv-field__textarea" rows="3"
                          placeholder="{{ __('e.g. Invalid phone number, KYC not complete, duplicate request…') }}"></textarea>
                <p class="apv-field__hint">{{ __('The affiliate will see this reason. Be clear and helpful.') }}</p>
            </div>

            <div class="apv-box__footer">
                <button type="button" class="apv-btn apv-btn--cancel" id="cancelRejectModal">{{ __('Cancel') }}</button>
                <button type="button" class="apv-btn apv-btn--reject" id="confirmRejectBtn">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><line x1="18" y1="6" x2="6" y2="18" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/><line x1="6" y1="6" x2="18" y2="18" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/></svg>
                    {{ __('Confirm Rejection') }}
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('style')
<style>
:root{--blue:#185FA5;--blue-hover:#0F4A84;--blue-light:#E6F1FB;--blue-border:#B5D4F4;--blue-faint:#185ea56e;--green:#1D9E75;--green-dark:#0F6E56;--green-light:#E1F5EE;--amber:#854F0B;--amber-light:#FAEEDA;--amber-border:#F5D9A8;--red:#993C1D;--red-light:#FAECE7;--purple:#534AB7;--gray-900:#111827;--gray-700:#374151;--gray-500:#6b7280;--gray-400:#9ca3af;--gray-200:#e5e7eb;--gray-100:#f3f4f6;--gray-50:#fafafa;--white:#ffffff}

.aw-header{display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:1rem;margin-bottom:24px}
.aw-breadcrumb{display:flex;align-items:center;gap:6px;list-style:none;padding:0;margin:0 0 8px;font-size:12px;color:var(--gray-400)}
.aw-breadcrumb li:not(:last-child)::after{content:'›';margin-left:6px;color:var(--gray-300)}
.aw-breadcrumb a{color:var(--blue);font-weight:500;text-decoration:none}
.aw-page-title{font-size:22px;font-weight:500;color:var(--gray-900);margin:0 0 4px}
.aw-page-sub{font-size:13px;color:var(--gray-500);margin:0}

.aw-kpi-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:24px}
.aw-kpi{background:var(--white);border:0.5px solid var(--blue-faint);border-radius:14px;padding:20px;display:flex;flex-direction:column;gap:10px;position:relative;overflow:hidden;transition:all .25s ease;box-shadow:0 4px 12px rgba(0,0,0,.04),0 0 0 1px rgba(24,95,165,.05),0 6px 18px rgba(24,95,165,.06)}
.aw-kpi:hover{border-color:var(--blue);transform:translateY(-3px)}
.aw-kpi__top{display:flex;align-items:center;gap:10px}
.aw-kpi__icon{width:40px;height:40px;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.aw-kpi__icon--amber{background:var(--amber-light);color:var(--amber)}.aw-kpi__icon--green{background:var(--green-light);color:var(--green)}.aw-kpi__icon--red{background:var(--red-light);color:var(--red)}.aw-kpi__icon--blue{background:var(--blue-light);color:var(--blue)}
.aw-kpi__label{font-size:11px;font-weight:500;text-transform:uppercase;letter-spacing:.07em;color:var(--gray-400);margin:0}
.aw-kpi__value{font-size:28px;font-weight:700;color:var(--gray-900);margin:0;line-height:1}
.aw-kpi__sub{font-size:11px;color:var(--gray-400);margin:0}
.aw-kpi__bar{position:absolute;bottom:0;left:0;right:0;height:3px}
.aw-kpi__bar--amber{background:#F59E0B}.aw-kpi__bar--green{background:var(--green)}.aw-kpi__bar--red{background:var(--red)}.aw-kpi__bar--blue{background:var(--blue)}

/* ── Section ── */
.aw-section{margin-bottom:24px}
.aw-section__head{display:flex;align-items:center;gap:12px;margin-bottom:16px;flex-wrap:wrap}
.aw-section__title{font-size:16px;font-weight:600;color:var(--gray-900);margin:0;display:flex;align-items:center;gap:8px}
.aw-section__title svg{color:var(--blue);flex-shrink:0}
.aw-section__count{font-size:12px;color:var(--gray-400);font-weight:500}
.aw-section__actions{margin-left:auto;display:flex;align-items:center;gap:10px}

/* ── Affiliate Cards ── */
.aw-affiliate-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:12px}
.aw-affiliate-card{background:var(--white);border:0.5px solid var(--gray-200);border-radius:10px;padding:16px;transition:all .2s ease}
.aw-affiliate-card:hover{border-color:var(--blue-border);box-shadow:0 4px 12px rgba(0,0,0,.06)}
.aw-affiliate-card--attention{border-color:var(--amber-border);background:var(--amber-light)}
.aw-affiliate-card__top{display:flex;align-items:center;gap:10px;margin-bottom:12px}
.aw-avatar--lg{width:40px;height:40px;border-radius:10px;font-size:16px}
.aw-affiliate-card__info{flex:1;min-width:0}
.aw-affiliate-card__name{font-size:13px;font-weight:600;color:var(--gray-900);margin:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.aw-affiliate-card__email{font-size:11px;color:var(--gray-400);margin:2px 0 0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.aw-pending-dot{flex-shrink:0}
.aw-affiliate-card__stats{display:grid;grid-template-columns:1fr 1fr;gap:8px}
.aw-affiliate-card__stat{display:flex;flex-direction:column;gap:2px}
.aw-affiliate-card__stat-label{font-size:10px;font-weight:500;text-transform:uppercase;letter-spacing:.05em;color:var(--gray-400)}
.aw-affiliate-card__stat-value{font-size:14px;font-weight:700;color:var(--gray-900)}
.aw-affiliate-card__stat-value--amber{color:var(--amber)}
.aw-show-more{text-align:center;padding:8px}
.aw-show-more-btn{background:none;border:none;color:var(--blue);font-size:13px;font-weight:500;cursor:pointer;padding:6px 16px;border-radius:7px;transition:all .13s}
.aw-show-more-btn:hover{background:var(--blue-light)}

/* ── Panel / Table ── */
.aw-panel{background:var(--white);border:0.5px solid var(--blue-faint);border-radius:12px;overflow:hidden;box-shadow:0 4px 12px rgba(0,0,0,.04),0 0 0 1px rgba(24,95,165,.05),0 6px 18px rgba(24,95,165,.06)}
.aw-panel__head{display:flex;align-items:center;gap:10px;padding:16px 20px;border-bottom:0.5px solid var(--gray-200);background:var(--gray-50);flex-wrap:wrap}
.aw-panel__icon{width:34px;height:34px;border-radius:8px;background:var(--blue-light);color:var(--blue);display:flex;align-items:center;justify-content:center;flex-shrink:0}
.aw-panel__title{font-size:14px;font-weight:600;color:var(--gray-900);margin:0}
.aw-count-badge{display:inline-flex;align-items:center;justify-content:center;min-width:20px;height:20px;background:var(--amber);color:var(--white);font-size:11px;font-weight:700;border-radius:20px;padding:0 8px;white-space:nowrap}
.aw-filter__wrap{display:flex;align-items:center;gap:6px;border:0.5px solid var(--gray-200);border-radius:7px;padding:5px 10px;background:var(--white);color:var(--gray-500)}
.aw-filter__wrap:focus-within{border-color:var(--blue);box-shadow:0 0 0 3px rgba(24,95,165,.08)}
.aw-filter__select{border:none;outline:none;background:transparent;font-size:12px;color:var(--gray-700);cursor:pointer;padding:0}

.table-responsive{overflow-x:auto;-webkit-overflow-scrolling:touch}
.aw-table{width:100%;border-collapse:collapse;min-width:800px}
.aw-table thead th{padding:.65rem 1rem;font-size:10px;font-weight:500;text-transform:uppercase;letter-spacing:.07em;color:var(--gray-500);background:var(--gray-50);border-bottom:0.5px solid var(--gray-200);text-align:left;white-space:nowrap}
.aw-table tbody td{padding:.8rem 1rem;font-size:13px;color:var(--gray-700);border-bottom:0.5px solid var(--gray-100);vertical-align:middle}
.aw-table tbody tr:last-child td{border-bottom:none}
.aw-table tbody tr:nth-child(even) td{background:var(--gray-50)}
.aw-table tbody tr:hover td{background:var(--gray-100)}
.aw-owner-cell{display:flex;align-items:center;gap:9px}
.aw-avatar{width:30px;height:30px;border-radius:8px;background:var(--purple);color:var(--white);display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;flex-shrink:0}
.aw-owner-name{font-size:13px;font-weight:500;color:var(--gray-900)}.aw-owner-email{font-size:11px;color:var(--gray-400)}
.aw-amount-badge{display:inline-flex;padding:3px 10px;border-radius:99px;font-size:12px;font-weight:700;background:var(--blue-light);color:var(--blue);border:0.5px solid var(--blue-border);white-space:nowrap}
.aw-muted{color:var(--gray-500);font-size:12px}.aw-date{color:var(--gray-500);white-space:nowrap;font-size:12px}
.aw-method-badge{display:inline-flex;align-items:center;gap:5px;padding:2px 8px;border-radius:99px;font-size:11px;font-weight:500;white-space:nowrap}
.aw-method-badge--b2c{background:var(--green-light);color:var(--green-dark)}
.aw-method-badge--manual{background:var(--amber-light);color:var(--amber);border:0.5px solid var(--amber-border)}
.aw-status-badge{display:inline-flex;align-items:center;padding:3px 9px;border-radius:99px;font-size:11px;font-weight:500;white-space:nowrap}
.aw-status-badge--pending{background:var(--amber-light);color:var(--amber);border:0.5px solid var(--amber-border)}
.aw-status-badge--approved{background:var(--green-light);color:var(--green-dark)}
.aw-status-badge--rejected{background:var(--red-light);color:var(--red)}
.aw-notes-cell{display:inline-flex;align-items:center;gap:4px;font-size:11px;color:var(--gray-500);cursor:help;max-width:150px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.aw-action-group{display:flex;gap:6px}
.aw-btn{display:inline-flex;align-items:center;gap:5px;padding:5px 10px;border-radius:7px;font-size:11px;font-weight:500;border:0.5px solid transparent;cursor:pointer;transition:all .13s;white-space:nowrap}
.aw-btn--approve{background:var(--green-light);color:var(--green-dark);border-color:#A7DFC9}
.aw-btn--approve:hover{background:var(--green);color:var(--white)}
.aw-btn--reject{background:var(--red-light);color:var(--red);border-color:#f5c4b8}
.aw-btn--reject:hover{background:var(--red);color:var(--white)}
.aw-empty{text-align:center;padding:2.5rem 1rem !important;color:var(--gray-400);font-size:13px}
.aw-empty__icon{display:flex;justify-content:center;margin-bottom:8px;color:var(--gray-200)}
.aw-pagination{padding:14px 20px;border-top:0.5px solid var(--gray-200);background:var(--gray-50);display:flex;justify-content:flex-end}

/* Approve/Reject modals */
.apv-overlay{position:fixed !important;top:0 !important;left:0 !important;right:0 !important;bottom:0 !important;width:100vw !important;height:100vh !important;background:rgba(17,24,39,.45) !important;backdrop-filter:blur(2px) !important;z-index:99999 !important;display:flex !important;align-items:center !important;justify-content:center !important;visibility:visible !important;opacity:1 !important}
.apv-overlay[data-state="closed"]{display:none !important;visibility:hidden !important;pointer-events:none !important;opacity:0 !important}
.apv-box{background:var(--white) !important;border-radius:14px !important;width:100% !important;max-width:460px !important;box-shadow:0 20px 50px rgba(0,0,0,.18) !important;overflow:hidden !important;position:relative !important;z-index:100000 !important}
.apv-box__head{display:flex;align-items:center;gap:12px;padding:20px 20px 12px;border-bottom:0.5px solid var(--gray-200);background:var(--gray-50)}
.apv-box__icon{width:40px;height:40px;border-radius:10px;background:var(--green-light);color:var(--green);display:flex;align-items:center;justify-content:center;flex-shrink:0}
.apv-box__icon--reject{background:var(--red-light);color:var(--red)}
.apv-box__eyebrow{font-size:10px;font-weight:500;text-transform:uppercase;letter-spacing:.07em;color:var(--gray-400);margin:0 0 2px}
.apv-box__title{font-size:15px;font-weight:600;color:var(--gray-900);margin:0}
.apv-box__close{margin-left:auto;background:var(--gray-100);border:0.5px solid var(--gray-200);font-size:18px;color:var(--gray-500);cursor:pointer;width:30px;height:30px;border-radius:7px;display:flex;align-items:center;justify-content:center;transition:all .13s}
.apv-box__close:hover{background:var(--gray-200);color:var(--gray-900)}
.apv-box__body{padding:20px}
.apv-summary{background:var(--gray-50);border:0.5px solid var(--gray-200);border-radius:10px;overflow:hidden;margin-bottom:16px}
.apv-summary__row{display:flex;align-items:center;justify-content:space-between;padding:10px 14px;border-bottom:0.5px solid var(--gray-200)}
.apv-summary__row:last-child{border-bottom:none}
.apv-summary__row--highlight{background:var(--blue-light)}
.apv-summary__label{font-size:11px;font-weight:500;text-transform:uppercase;letter-spacing:.07em;color:var(--gray-400)}
.apv-summary__value{font-size:13px;font-weight:500;color:var(--gray-900)}
.apv-summary__amount{font-size:16px;font-weight:700;color:var(--blue)}
.apv-method-row{display:flex;align-items:center;justify-content:space-between;margin-bottom:14px}
.apv-method-label{font-size:11px;font-weight:500;text-transform:uppercase;letter-spacing:.07em;color:var(--gray-400)}
.apv-method-toggle{display:flex;gap:6px}
.apv-method-btn{display:inline-flex;align-items:center;gap:6px;padding:6px 12px;border-radius:7px;font-size:12px;font-weight:500;border:0.5px solid var(--gray-200);background:var(--white);color:var(--gray-500);cursor:pointer;transition:all .13s}
.apv-method-btn--active{background:var(--blue-light);color:var(--blue);border-color:var(--blue-border)}
.apv-warning{display:flex;align-items:flex-start;gap:8px;background:var(--amber-light);border:0.5px solid var(--amber-border);border-radius:8px;padding:10px 12px;font-size:12px;color:var(--amber);margin-bottom:14px}
.apv-field{margin-bottom:16px}
.apv-field__label{display:block;font-size:10px;font-weight:500;text-transform:uppercase;letter-spacing:.07em;color:var(--gray-400);margin-bottom:6px}
.apv-required{color:var(--red)}
.apv-field__textarea{width:100%;padding:9px 12px;font-size:13px;border:0.5px solid var(--gray-200);border-radius:7px;color:var(--gray-900);outline:none;resize:vertical;box-sizing:border-box;transition:border-color .15s}
.apv-field__textarea:focus{border-color:var(--blue);box-shadow:0 0 0 3px rgba(24,95,165,.1)}
.apv-field__hint{font-size:11px;color:var(--gray-400);margin:4px 0 0}
.apv-box__footer{display:flex;gap:8px}
.apv-btn{flex:1;display:inline-flex;align-items:center;justify-content:center;gap:7px;padding:10px 16px;border-radius:7px;font-size:13px;font-weight:500;border:0.5px solid transparent;cursor:pointer;transition:all .13s}
.apv-btn--cancel{background:var(--gray-100);color:var(--gray-700);border-color:var(--gray-200)}
.apv-btn--cancel:hover{background:var(--gray-200)}
.apv-btn--confirm{background:var(--green);color:var(--white)}
.apv-btn--confirm:hover{background:var(--green-dark);transform:translateY(-1px)}
.apv-btn--confirm:disabled{background:#a7dfc9;cursor:not-allowed;transform:none}
.apv-btn--reject{background:var(--red);color:var(--white)}
.apv-btn--reject:hover{background:#7a2e14;transform:translateY(-1px)}
.apv-btn--reject:disabled{background:#f5c4b8;cursor:not-allowed;transform:none}

/* ═══════════════════════════════════════════════════════
   EARNINGS PANEL (NEW)
   ═══════════════════════════════════════════════════════ */
.aep-overlay {
    position:fixed !important; top:0 !important; right:0 !important; bottom:0 !important; left:0 !important;
    width:100vw !important; height:100vh !important;
    background:rgba(17,24,39,.4) !important;
    z-index:99998 !important;
    display:flex !important; justify-content:flex-end !important;
}
.aep-overlay[data-state="closed"] { display:none !important; }
.aep-panel {
    background:var(--white) !important; width:100% !important; max-width:520px !important;
    height:100vh !important; overflow-y:auto;
    box-shadow:-8px 0 30px rgba(0,0,0,.12) !important;
    display:flex !important; flex-direction:column !important;
    animation:aepSlideIn .3s ease;
}
@keyframes aepSlideIn { from { transform:translateX(100%); } to { transform:translateX(0); } }
.aep-panel__head {
    display:flex; align-items:center; justify-content:space-between; gap:12px;
    padding:20px; border-bottom:0.5px solid var(--gray-200);
    background:var(--gray-50); flex-shrink:0;
}
.aep-panel__head-left { display:flex; align-items:center; gap:12px; }
.aep-panel__title { font-size:16px; font-weight:600; color:var(--gray-900); margin:0; }
.aep-panel__sub { font-size:12px; color:var(--gray-400); margin:2px 0 0; }
.aep-panel__close {
    background:var(--gray-100); border:0.5px solid var(--gray-200);
    font-size:20px; color:var(--gray-500); cursor:pointer;
    width:32px; height:32px; border-radius:8px; display:flex;
    align-items:center; justify-content:center; transition:all .13s;
}
.aep-panel__close:hover { background:var(--gray-200); color:var(--gray-900); }
.aep-panel__body { padding:20px; flex:1; overflow-y:auto; }

.aep-loading { display:flex; flex-direction:column; align-items:center; justify-content:center; gap:14px; padding:60px 20px; color:var(--gray-400); }
.aep-loading__spinner { width:32px; height:32px; border:3px solid var(--gray-200); border-top-color:var(--blue); border-radius:50%; animation:aepSpin .7s linear infinite; }
@keyframes aepSpin { to { transform:rotate(360deg); } }
.aep-error { display:flex; flex-direction:column; align-items:center; gap:12px; padding:60px 20px; color:var(--gray-400); text-align:center; }

.aep-stats-grid { display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:16px; }
.aep-stat { background:var(--gray-50); border:0.5px solid var(--gray-200); border-radius:10px; padding:14px; display:flex; flex-direction:column; gap:4px; }
.aep-stat__label { font-size:10px; font-weight:500; text-transform:uppercase; letter-spacing:.05em; color:var(--gray-400); }
.aep-stat__value { font-size:18px; font-weight:700; }
.aep-stat__value--blue { color:var(--blue); }
.aep-stat__value--green { color:var(--green); }
.aep-stat__value--amber { color:var(--amber); }
.aep-stat__value--purple { color:var(--purple); }

.aep-alert { display:flex; align-items:flex-start; gap:10px; padding:12px; border-radius:8px; margin-bottom:16px; font-size:12px; }
.aep-alert--warning { background:var(--amber-light); border:0.5px solid var(--amber-border); color:var(--amber); }

.aep-subsection { margin-bottom:20px; }
.aep-subsection__title { font-size:13px; font-weight:600; color:var(--gray-900); margin:0 0 10px; display:flex; align-items:center; gap:6px; }

.aep-table { width:100%; border-collapse:collapse; font-size:12px; min-width:400px; }
.aep-table thead th { padding:.5rem .75rem; font-size:10px; font-weight:500; text-transform:uppercase; letter-spacing:.05em; color:var(--gray-400); background:var(--gray-50); border-bottom:0.5px solid var(--gray-200); text-align:left; white-space:nowrap; position:sticky; top:0; z-index:2; }
.aep-table tbody td { padding:.55rem .75rem; color:var(--gray-700); border-bottom:0.5px solid var(--gray-100); }
.aep-table tbody tr:hover td { background:var(--gray-50); }
.aep-empty-row td { text-align:center; padding:2rem !important; color:var(--gray-300); }

.aep-status { display:inline-flex; padding:2px 8px; border-radius:99px; font-size:10px; font-weight:600; }
.aep-status--approved { background:var(--green-light); color:var(--green-dark); }
.aep-status--pending { background:var(--amber-light); color:var(--amber); }
.aep-status--rejected { background:var(--red-light); color:var(--red); }

/* Make affiliate names clickable */
.aw-owner-name--link { color:var(--blue); cursor:pointer; text-decoration:none; transition:color .13s; }
.aw-owner-name--link:hover { color:var(--blue-hover); text-decoration:underline; }

@media(max-width:640px) {
    .aep-panel { max-width:100% !important; }
    .aep-stats-grid { grid-template-columns:1fr 1fr; }
}
@media(max-width:1200px){.aw-kpi-grid{grid-template-columns:repeat(2,1fr)}.aw-affiliate-grid{grid-template-columns:repeat(2,1fr)}}
@media(max-width:768px){.aw-kpi-grid{grid-template-columns:repeat(2,1fr)}.aw-affiliate-grid{grid-template-columns:1fr}}
@media(max-width:600px){.aw-kpi-grid{grid-template-columns:1fr}.aw-affiliate-grid{grid-template-columns:1fr}.apv-box{max-width:100% !important;border-radius:16px 16px 0 0 !important;margin-top:auto}}
</style>
@endpush

@push('script')
<script>
(function() {
    'use strict';

    // ═══════════════════════════════════════════════════════
    // EARNINGS PANEL LOGIC
    // ═══════════════════════════════════════════════════════
    const earningsPanel = document.getElementById('earningsPanel');
    const aepLoading = document.getElementById('aepLoading');
    const aepContent = document.getElementById('aepContent');
    const aepError = document.getElementById('aepError');
    
    let currentAffiliateId = null;
    
    // ── Translation strings (pre-rendered to avoid JSX conflicts) ──
    const i18n = {
        pendingWithdrawals: @json(__('pending withdrawal(s)')),
        awaitingApproval: @json(__('awaiting approval')),
        noCommissionHistory: @json(__('No commission history')),
        noWithdrawalsYet: @json(__('No withdrawals yet')),
        couldNotLoad: @json(__('Could not load earnings data.')),
        loadingData: @json(__('Loading earnings data…')),
        ksh: 'KSh',
    };
    
    function openEarningsPanel(affiliateId, name) {
        currentAffiliateId = affiliateId;
        
        // Set name immediately
        document.getElementById('aepName').textContent = name;
        document.getElementById('aepAvatar').textContent = name.charAt(0).toUpperCase();
        document.getElementById('aepEmail').textContent = '';
        
        // Show loading
        aepLoading.style.display = 'flex';
        aepContent.style.display = 'none';
        aepError.style.display = 'none';
        
        // Open panel
        earningsPanel.removeAttribute('data-state');
        earningsPanel.style.setProperty('display', 'flex', 'important');
        document.body.style.overflow = 'hidden';
        
        // Fetch data
        var url = '{{ route("admin.affiliate.earnings", ":id") }}'.replace(':id', affiliateId);
        fetch(url, {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(function(r) { return r.json(); })
        .then(function(res) {
            if (res.success) {
                populateEarningsPanel(res.data);
                aepLoading.style.display = 'none';
                aepContent.style.display = 'block';
            } else {
                aepLoading.style.display = 'none';
                aepError.style.display = 'flex';
            }
        })
        .catch(function() {
            aepLoading.style.display = 'none';
            aepError.style.display = 'flex';
        });
    }
    
    function closeEarningsPanel() {
        earningsPanel.setAttribute('data-state', 'closed');
        earningsPanel.style.setProperty('display', 'none', 'important');
        document.body.style.overflow = '';
        currentAffiliateId = null;
    }
    
    function populateEarningsPanel(data) {
        // Affiliate info
        document.getElementById('aepName').textContent = data.affiliate.name;
        var emailText = data.affiliate.email;
        if (data.affiliate.referral_code && data.affiliate.referral_code !== '—') {
            emailText += ' · Ref: ' + data.affiliate.referral_code;
        }
        document.getElementById('aepEmail').textContent = emailText;
        
        // Stats
        document.getElementById('aepAvailable').textContent = i18n.ksh + ' ' + formatNum(data.stats.available_balance);
        document.getElementById('aepLifetime').textContent = i18n.ksh + ' ' + formatNum(data.stats.lifetime_earned);
        document.getElementById('aepWithdrawn').textContent = i18n.ksh + ' ' + formatNum(data.stats.total_withdrawn);
        document.getElementById('aepMonth').textContent = i18n.ksh + ' ' + formatNum(data.stats.current_month_payout);
        
        // Pending withdrawals alert
        var pendingSection = document.getElementById('aepPendingSection');
        if (data.pending_withdrawals && data.pending_withdrawals.length > 0) {
            pendingSection.style.display = 'flex';
            var totalPending = data.pending_withdrawals.reduce(function(sum, w) { 
                return sum + parseFloat(w.amount); 
            }, 0);
            document.getElementById('aepPendingCount').textContent = data.pending_withdrawals.length + ' ' + i18n.pendingWithdrawals;
            document.getElementById('aepPendingAmount').textContent = i18n.ksh + ' ' + formatNum(totalPending) + ' ' + i18n.awaitingApproval;
        } else {
            pendingSection.style.display = 'none';
        }
        
        // Monthly commissions table
        var monthlyBody = document.getElementById('aepMonthlyBody');
        monthlyBody.innerHTML = '';
        if (data.monthly_commissions && data.monthly_commissions.length > 0) {
            data.monthly_commissions.forEach(function(row) {
                monthlyBody.innerHTML += '<tr>'
                    + '<td style="font-weight:500;white-space:nowrap">' + esc(row.period) + '</td>'
                    + '<td>' + (row.subscription_payout > 0 ? i18n.ksh + ' ' + formatNum(row.subscription_payout) : '—') + '</td>'
                    + '<td>' + (row.rent_payout > 0 ? i18n.ksh + ' ' + formatNum(row.rent_payout) : '—') + '</td>'
                    + '<td>' + (row.marketplace_payout > 0 ? i18n.ksh + ' ' + formatNum(row.marketplace_payout) : '—') + '</td>'
                    + '<td style="font-weight:600;color:var(--green-dark)">' + i18n.ksh + ' ' + formatNum(row.total_payout) + '</td>'
                    + '</tr>';
            });
        } else {
            monthlyBody.innerHTML = '<tr><td colspan="5" class="aep-empty-row">' + i18n.noCommissionHistory + '</td></tr>';
        }
        
        // Recent withdrawals table
        var withdrawalBody = document.getElementById('aepWithdrawalBody');
        withdrawalBody.innerHTML = '';
        if (data.recent_withdrawals && data.recent_withdrawals.length > 0) {
            data.recent_withdrawals.forEach(function(w) {
                withdrawalBody.innerHTML += '<tr>'
                    + '<td style="white-space:nowrap">' + esc(w.requested_at || '—') + '</td>'
                    + '<td style="font-weight:600">' + i18n.ksh + ' ' + formatNum(w.amount) + '</td>'
                    + '<td><span class="aep-status aep-status--' + w.status + '">' + esc(w.status_label) + '</span></td>'
                    + '<td style="max-width:120px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="' + escAttr(w.notes || '') + '">' + esc(w.notes || '—') + '</td>'
                    + '</tr>';
            });
        } else {
            withdrawalBody.innerHTML = '<tr><td colspan="4" class="aep-empty-row">' + i18n.noWithdrawalsYet + '</td></tr>';
        }
    }
    
    function formatNum(val) {
        return parseFloat(val || 0).toLocaleString('en-KE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }
    
    function esc(str) {
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(str));
        return div.innerHTML;
    }
    
    function escAttr(str) {
        return String(str).replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/'/g, '&#39;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }
    
    // Make openEarningsPanel globally accessible
    window.openEarningsPanel = openEarningsPanel;
    
    // Close button
    var closeBtn = document.getElementById('closeEarningsPanel');
    if (closeBtn) {
        closeBtn.addEventListener('click', closeEarningsPanel);
    }
    
    // Click outside to close
    if (earningsPanel) {
        earningsPanel.addEventListener('click', function(e) {
            if (e.target === earningsPanel) closeEarningsPanel();
        });
    }
    
    // Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeEarningsPanel();
    });

    // ═══════════════════════════════════════════════════════
    // APPROVE / REJECT MODAL LOGIC
    // ═══════════════════════════════════════════════════════
    const approveModal = document.getElementById('approveModal');
    const rejectModal = document.getElementById('rejectModal');
    
    // Approve elements
    const confirmApproveBtn = document.getElementById('confirmApproveBtn');
    const cancelApproveBtn = document.getElementById('cancelApproveModal');
    const closeApproveBtn = document.getElementById('closeApproveModal');
    const notesEl = document.getElementById('apvNotes');
    const methodB2c = document.getElementById('methodB2c');
    const methodManual = document.getElementById('methodManual');
    const b2cWarning = document.getElementById('b2cWarning');
    const notesHint = document.getElementById('notesHint');
    
    // Reject elements
    const confirmRejectBtn = document.getElementById('confirmRejectBtn');
    const cancelRejectBtn = document.getElementById('cancelRejectModal');
    const closeRejectBtn = document.getElementById('closeRejectModal');
    const rjNotesEl = document.getElementById('rjNotes');
    
    // State
    let pendingUrl = null;
    let currentMethod = 'b2c';
    let currentId = null;
    
    // Status filter
    const statusFilter = document.getElementById('statusFilter');
    if (statusFilter) {
        statusFilter.addEventListener('change', function() {
            const val = this.value;
            document.querySelectorAll('#awTable tbody tr[data-status]').forEach(function(row) {
                row.style.display = (!val || row.dataset.status === val) ? '' : 'none';
            });
        });
    }
    
    // Modal open/close
    function openModal(modal) { 
        if (!modal) return;
        modal.removeAttribute('data-state'); 
        modal.style.setProperty('display', 'flex', 'important'); 
        document.body.style.overflow = 'hidden';
    }
    
    function closeModal(modal) {
        if (!modal) return;
        modal.setAttribute('data-state', 'closed');
        modal.style.setProperty('display', 'none', 'important');
        document.body.style.overflow = '';
    }
    
    function closeApprove() {
        closeModal(approveModal);
        if (confirmApproveBtn) {
            confirmApproveBtn.disabled = false;
            confirmApproveBtn.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none"><polyline points="20 6 9 17 4 12" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg> @json(__('Confirm & Send'))';
        }
        if (notesEl) notesEl.value = '';
        pendingUrl = null;
        currentId = null;
        setMethod('b2c');
    }
    
    function closeReject() {
        closeModal(rejectModal);
        if (confirmRejectBtn) {
            confirmRejectBtn.disabled = false;
            confirmRejectBtn.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none"><line x1="18" y1="6" x2="6" y2="18" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/><line x1="6" y1="6" x2="18" y2="18" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/></svg> @json(__('Confirm Rejection'))';
        }
        if (rjNotesEl) rjNotesEl.value = '';
        pendingUrl = null;
        currentId = null;
    }
    
    function setMethod(method) {
        currentMethod = method;
        if (methodB2c && methodManual) {
            methodB2c.classList.toggle('apv-method-btn--active', method === 'b2c');
            methodManual.classList.toggle('apv-method-btn--active', method === 'manual');
        }
        if (b2cWarning) {
            b2cWarning.style.display = method === 'b2c' ? 'flex' : 'none';
        }
        if (notesHint) {
            notesHint.textContent = method === 'manual'
                ? @json(__('Required — document how the payment was made.'))
                : @json(__('Optional — add any notes about this transfer.'));
        }
    }
    
    function triggerApprove(id, name, amount, phone) {
        document.getElementById('apvName').textContent = name;
        document.getElementById('apvPhone').textContent = phone || '—';
        document.getElementById('apvAmount').textContent = 'KSh ' + amount;
        pendingUrl = '{{ route("admin.affiliate.withdrawal.approve", ":id") }}'.replace(':id', id);
        currentId = id;
        openModal(approveModal);
    }
    
    function triggerReject(id, name, amount) {
        document.getElementById('rjName').textContent = name;
        document.getElementById('rjAmount').textContent = 'KSh ' + amount;
        pendingUrl = '{{ route("admin.affiliate.withdrawal.reject", ":id") }}'.replace(':id', id);
        currentId = id;
        openModal(rejectModal);
    }
    
    function doPost(url, extra) {
        const fd = new FormData();
        fd.append('_token', document.querySelector('meta[name="csrf-token"]').content);
        Object.keys(extra).forEach(function(k) { 
            fd.append(k, extra[k]); 
        });
        
        return fetch(url, { method: 'POST', body: fd })
            .then(function(r) { return r.json(); })
            .then(function(d) {
                if (d.success) {
                    if (typeof toastr !== 'undefined') {
                        toastr.success(d.message || @json(__('Done.')));
                    } else {
                        alert(d.message || @json(__('Done.')));
                    }
                    setTimeout(function() { location.reload(); }, 1500);
                } else {
                    if (typeof toastr !== 'undefined') {
                        toastr.error(d.error || @json(__('Failed.')));
                    } else {
                        alert(d.error || @json(__('Failed.')));
                    }
                    throw new Error(d.error || 'Failed');
                }
            });
    }
    
    // Action button listeners
    function initActionButtons() {
        document.querySelectorAll('.aw-btn--approve').forEach(function(btn) {
            btn.removeEventListener('click', handleApproveClick);
            btn.addEventListener('click', handleApproveClick);
        });
        
        document.querySelectorAll('.aw-btn--reject').forEach(function(btn) {
            btn.removeEventListener('click', handleRejectClick);
            btn.addEventListener('click', handleRejectClick);
        });
    }
    
    function handleApproveClick(e) {
        const btn = e.currentTarget;
        triggerApprove(
            btn.getAttribute('data-id'),
            btn.getAttribute('data-name'),
            btn.getAttribute('data-amount'),
            btn.getAttribute('data-phone')
        );
    }
    
    function handleRejectClick(e) {
        const btn = e.currentTarget;
        triggerReject(
            btn.getAttribute('data-id'),
            btn.getAttribute('data-name'),
            btn.getAttribute('data-amount')
        );
    }
    
    // Confirm approve
    if (confirmApproveBtn) {
        confirmApproveBtn.addEventListener('click', function() {
            if (!pendingUrl) return;
            const notes = notesEl ? notesEl.value.trim() : '';
            if (currentMethod === 'manual' && !notes) {
                const msg = @json(__('Settlement notes are required for manual payouts.'));
                if (typeof toastr !== 'undefined') toastr.error(msg);
                else alert(msg);
                if (notesEl) notesEl.focus();
                return;
            }
            confirmApproveBtn.disabled = true;
            confirmApproveBtn.textContent = @json(__('Processing…'));
            
            doPost(pendingUrl, { method: currentMethod, notes: notes })
                .catch(function() {
                    confirmApproveBtn.disabled = false;
                    confirmApproveBtn.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none"><polyline points="20 6 9 17 4 12" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg> @json(__('Confirm & Send'))';
                });
        });
    }
    
    // Confirm reject
    if (confirmRejectBtn) {
        confirmRejectBtn.addEventListener('click', function() {
            if (!pendingUrl) return;
            const notes = rjNotesEl ? rjNotesEl.value.trim() : '';
            if (!notes) {
                const msg = @json(__('Please provide a reason for rejection.'));
                if (typeof toastr !== 'undefined') toastr.error(msg);
                else alert(msg);
                if (rjNotesEl) rjNotesEl.focus();
                return;
            }
            confirmRejectBtn.disabled = true;
            confirmRejectBtn.textContent = @json(__('Processing…'));
            
            doPost(pendingUrl, { notes: notes })
                .catch(function() {
                    confirmRejectBtn.disabled = false;
                    confirmRejectBtn.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none"><line x1="18" y1="6" x2="6" y2="18" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/><line x1="6" y1="6" x2="18" y2="18" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/></svg> @json(__('Confirm Rejection'))';
                });
        });
    }
    
    // Modal close buttons
    if (cancelApproveBtn) cancelApproveBtn.addEventListener('click', closeApprove);
    if (closeApproveBtn) closeApproveBtn.addEventListener('click', closeApprove);
    if (cancelRejectBtn) cancelRejectBtn.addEventListener('click', closeReject);
    if (closeRejectBtn) closeRejectBtn.addEventListener('click', closeReject);
    
    if (approveModal) {
        approveModal.addEventListener('click', function(e) { 
            if (e.target === approveModal) closeApprove(); 
        });
    }
    if (rejectModal) {
        rejectModal.addEventListener('click', function(e) { 
            if (e.target === rejectModal) closeReject(); 
        });
    }
    
    document.addEventListener('keydown', function(e) { 
        if (e.key === 'Escape') { 
            closeApprove(); 
            closeReject(); 
            closeEarningsPanel();
        } 
    });
    
    // Method toggle
    if (methodB2c) methodB2c.addEventListener('click', function() { setMethod('b2c'); });
    if (methodManual) methodManual.addEventListener('click', function() { setMethod('manual'); });
    
    // Show all affiliates
    const showAllBtn = document.getElementById('showAllAffiliates');
    if (showAllBtn) {
        showAllBtn.addEventListener('click', function() {
            document.querySelectorAll('.aw-affiliate-card').forEach(function(card) {
                card.style.display = '';
            });
            showAllBtn.style.display = 'none';
        });
    }
    
    // Initialize
    function init() {
        closeApprove();
        closeReject();
        closeEarningsPanel();
        initActionButtons();
        setMethod('b2c');
        
        // Hide extra affiliate cards (show first 6)
        const cards = document.querySelectorAll('.aw-affiliate-card');
        if (cards.length > 6 && showAllBtn) {
            cards.forEach(function(card, index) {
                if (index >= 6) card.style.display = 'none';
            });
        }
    }
    
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
    
})();
</script>
@endpush