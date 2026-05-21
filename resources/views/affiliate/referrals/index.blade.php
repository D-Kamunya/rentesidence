@extends('affiliate.layouts.app')

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="page-content-wrapper bg-white p-30 radius-20">
                <div class="container">

                    {{-- Page Header --}}
                    <div class="ref-header">
                        <div>
                            <nav aria-label="breadcrumb">
                                <ol class="ref-breadcrumb">
                                    <li><a href="{{ route('affiliate.dashboard') }}">{{ __('Dashboard') }}</a></li>
                                    <li aria-current="page">{{ __('My Referrals') }}</li>
                                </ol>
                            </nav>
                            <h1 class="ref-page-title">{{ __('My Referrals') }}</h1>
                            <p class="ref-page-sub">{{ __('Track your referred owners and their earnings') }}</p>
                        </div>
                    </div>

                    {{-- Stats Row --}}
                    <div class="ref-stats-grid">
                        <div class="ref-stat-card ref-stat-card--blue">
                            <div class="ref-stat-card__icon">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><circle cx="9" cy="7" r="4" stroke="currentColor" stroke-width="2"/></svg>
                            </div>
                            <div>
                                <span class="ref-stat-card__label">{{ __('Total Referrals') }}</span>
                                <span class="ref-stat-card__value">{{ $totalReferrals }}</span>
                            </div>
                        </div>
                        <div class="ref-stat-card ref-stat-card--green">
                            <div class="ref-stat-card__icon">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none"><path d="M22 11.08V12a10 10 0 11-5.93-9.14" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><polyline points="22 4 12 14.01 9 11.01" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </div>
                            <div>
                                <span class="ref-stat-card__label">{{ __('Active (30d)') }}</span>
                                <span class="ref-stat-card__value">{{ $activeReferrals }}</span>
                            </div>
                        </div>
                        <div class="ref-stat-card ref-stat-card--amber">
                            <div class="ref-stat-card__icon">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </div>
                            <div>
                                <span class="ref-stat-card__label">{{ __('Total Earned') }}</span>
                                <span class="ref-stat-card__value">KSh {{ number_format($totalEarned, 2) }}</span>
                            </div>
                        </div>
                        <div class="ref-stat-card ref-stat-card--purple">
                            <div class="ref-stat-card__icon">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3z" fill="currentColor"/></svg>
                            </div>
                            <div>
                                <span class="ref-stat-card__label">{{ __('New This Month') }}</span>
                                <span class="ref-stat-card__value">{{ $newThisMonth }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Referrals Table --}}
                    <div class="ref-panel">
                        <div class="ref-panel__head">
                            <div class="ref-panel__head-left">
                                <div class="ref-panel__icon">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><circle cx="9" cy="7" r="4" stroke="currentColor" stroke-width="2"/></svg>
                                </div>
                                <h4 class="ref-panel__title">{{ __('All Referrals') }}</h4>
                            </div>
                            <div class="ref-filter-wrap">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                <select id="refStatusFilter" class="ref-filter-select">
                                    <option value="">{{ __('All') }}</option>
                                    <option value="active">{{ __('Active') }}</option>
                                    <option value="new">{{ __('New Clients') }}</option>
                                    <option value="recurring">{{ __('Recurring Clients') }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="ref-table" id="refTable">
                                <thead>
                                    <tr>
                                        <th>{{ __('Owner') }}</th>
                                        <th>{{ __('Client Type') }}</th>
                                        <th>{{ __('Since') }}</th>
                                        <th>{{ __('Last Activity') }}</th>
                                        <th>{{ __('Subscriptions') }}</th>
                                        <th>{{ __('Rent') }}</th>
                                        <th>{{ __('Marketplace') }}</th>
                                        <th>{{ __('Total Earned') }}</th>
                                        <th>{{ __('Details') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($referrals as $referral)
                                    <tr data-status="{{ $referral->is_active ? 'active' : 'inactive' }}"
                                        data-type="{{ $referral->client_type }}">
                                        <td>
                                            <div class="ref-owner-cell">
                                                <div class="ref-avatar ref-avatar--{{ $referral->is_active ? 'active' : 'inactive' }}">
                                                    {{ strtoupper(substr($referral->user->name ?? 'O', 0, 1)) }}
                                                </div>
                                                <div>
                                                    <div class="ref-owner-name">{{ $referral->user->name ?? '—' }}</div>
                                                    <div class="ref-owner-email">{{ $referral->user->email ?? '' }}</div>
                                                </div>
                                                @if($referral->is_active)
                                                <span class="ref-active-dot" title="{{ __('Active') }}">
                                                    <span class="ref-pulse"></span>
                                                </span>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            @if($referral->client_type === 'new_client')
                                                <span class="ref-type-badge ref-type-badge--new">
                                                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none"><path d="M12 2l2.9 6 6.6.9-4.8 4.6 1.1 6.5L12 17l-5.8 3 1.1-6.5L2.5 9l6.6-.9L12 2z" fill="currentColor"/></svg>
                                                    {{ __('New Client') }}
                                                </span>
                                            @elseif($referral->client_type === 'recurring_client')
                                                <span class="ref-type-badge ref-type-badge--recurring">
                                                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none"><path d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                                    {{ __('Recurring') }}
                                                </span>
                                            @else
                                                <span class="ref-type-badge ref-type-badge--other">{{ __('Other') }}</span>
                                            @endif
                                        </td>
                                        <td class="ref-date">
                                            {{ $referral->first_commission_date?->format('M Y') ?? '—' }}
                                        </td>
                                        <td class="ref-date">
                                            {{ $referral->last_commission_date?->diffForHumans() ?? '—' }}
                                        </td>
                                        <td class="ref-amount">
                                            KSh {{ number_format($referral->subscription_earned ?? 0, 2) }}
                                        </td>
                                        <td class="ref-amount">
                                            KSh {{ number_format($referral->rent_earned ?? 0, 2) }}
                                        </td>
                                        <td class="ref-amount">
                                            KSh {{ number_format($referral->marketplace_earned ?? 0, 2) }}
                                        </td>
                                        <td class="ref-amount ref-amount--total">
                                            KSh {{ number_format($referral->total_earned ?? 0, 2) }}
                                        </td>
                                        <td>
                                            <button type="button"
                                                    class="ref-view-btn"
                                                    data-id="{{ $referral->id }}"
                                                    data-name="{{ $referral->user->name ?? 'Owner' }}"
                                                    title="{{ __('View breakdown') }}">
                                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"/></svg>
                                                {{ __('View') }}
                                            </button>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="9" class="ref-empty">
                                            <div class="ref-empty__icon">
                                                <svg width="32" height="32" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.5"/><line x1="8" y1="12" x2="16" y2="12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                                            </div>
                                            <p>{{ __('No referrals yet.') }}</p>
                                            <p style="font-size:12px;margin-top:4px;color:var(--ref-gray-400);">{{ __('Start by sharing your referral link with property owners.') }}</p>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @if($referrals->hasPages())
                        <div class="ref-pagination">{{ $referrals->links() }}</div>
                        @endif
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

{{-- Referral Detail Modal --}}
<div id="refDetailModal" class="ref-modal-overlay" data-state="closed">
    <div class="ref-modal-box">
        <div class="ref-modal-box__head">
            <div class="ref-modal-box__head-left">
                <div class="ref-modal-box__icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><circle cx="9" cy="7" r="4" stroke="currentColor" stroke-width="2"/></svg>
                </div>
                <div>
                    <p class="ref-modal-eyebrow">{{ __('Referral Breakdown') }}</p>
                    <h5 class="ref-modal-title" id="refDetailName">—</h5>
                </div>
            </div>
            <button type="button" class="ref-modal-close" id="refCloseDetail">&times;</button>
        </div>

        <div id="refDetailLoading" class="ref-detail-loading">
            <div class="ref-detail-loading__spinner"></div>
            <p>{{ __('Loading…') }}</p>
        </div>

        <div id="refDetailContent" class="ref-modal-body" style="display:none;">
            {{-- Stats --}}
            <div class="ref-detail-stats" id="refDetailStats"></div>

            {{-- Monthly Earnings --}}
            <div class="ref-detail-section">
                <h5 class="ref-detail-section__title">{{ __('Monthly Earnings') }}</h5>
                <div class="table-responsive" style="max-height:200px;">
                    <table class="ref-detail-table">
                        <thead>
                            <tr>
                                <th>{{ __('Period') }}</th>
                                <th>{{ __('Subscriptions') }}</th>
                                <th>{{ __('Rent') }}</th>
                                <th>{{ __('Marketplace') }}</th>
                                <th>{{ __('Total') }}</th>
                            </tr>
                        </thead>
                        <tbody id="refMonthlyBody"></tbody>
                    </table>
                </div>
            </div>

            {{-- Recent Commissions --}}
            <div class="ref-detail-section">
                <h5 class="ref-detail-section__title">{{ __('Recent Commissions') }}</h5>
                <div class="table-responsive" style="max-height:200px;">
                    <table class="ref-detail-table">
                        <thead>
                            <tr>
                                <th>{{ __('Date') }}</th>
                                <th>{{ __('Source') }}</th>
                                <th>{{ __('Type') }}</th>
                                <th>{{ __('Rate') }}</th>
                                <th>{{ __('Amount') }}</th>
                            </tr>
                        </thead>
                        <tbody id="refCommissionsBody"></tbody>
                    </table>
                </div>
            </div>
        </div>

        <div id="refDetailError" class="ref-detail-error" style="display:none;">
            <p>{{ __('Could not load referral details.') }}</p>
        </div>
    </div>
</div>

@endsection

@push('style')
<style>
:root {
    --ref-blue: #185FA5;
    --ref-blue-light: #E6F1FB;
    --ref-green: #1D9E75;
    --ref-green-dark: #0F6E56;
    --ref-green-light: #E1F5EE;
    --ref-amber: #854F0B;
    --ref-amber-light: #FAEEDA;
    --ref-purple: #534AB7;
    --ref-purple-light: #EEEDF9;
    --ref-red: #DC2626;
    --ref-red-light: #FEE2E2;
    --ref-gray-900: #111827;
    --ref-gray-700: #374151;
    --ref-gray-500: #6b7280;
    --ref-gray-400: #9ca3af;
    --ref-gray-200: #e5e7eb;
    --ref-gray-100: #f3f4f6;
    --ref-gray-50: #fafafa;
    --ref-white: #ffffff;
}

.ref-header { margin-bottom: 24px; }
.ref-breadcrumb { display: flex; align-items: center; gap: 6px; list-style: none; padding: 0; margin: 0 0 8px; font-size: 12px; color: var(--ref-gray-400); }
.ref-breadcrumb li:not(:last-child)::after { content: '›'; margin-left: 6px; color: #d1d5db; }
.ref-breadcrumb a { color: var(--ref-blue); font-weight: 500; text-decoration: none; }
.ref-page-title { font-size: 22px; font-weight: 500; color: var(--ref-gray-900); margin: 0 0 4px; }
.ref-page-sub { font-size: 13px; color: var(--ref-gray-500); margin: 0; }

/* Stats */
.ref-stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; margin-bottom: 22px; }
.ref-stat-card {
    background: var(--ref-white);
    border: 0.5px solid rgba(24,95,165,.25);
    border-radius: 12px;
    padding: 18px 20px;
    display: flex; align-items: center; gap: 14px;
    transition: all .2s ease;
    box-shadow: 0 2px 8px rgba(0,0,0,.03);
}
.ref-stat-card:hover { transform: translateY(-2px); box-shadow: 0 6px 18px rgba(0,0,0,.06); }
.ref-stat-card__icon {
    width: 42px; height: 42px; border-radius: 10px;
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.ref-stat-card--blue .ref-stat-card__icon { background: var(--ref-blue-light); color: var(--ref-blue); }
.ref-stat-card--green .ref-stat-card__icon { background: var(--ref-green-light); color: var(--ref-green); }
.ref-stat-card--amber .ref-stat-card__icon { background: var(--ref-amber-light); color: var(--ref-amber); }
.ref-stat-card--purple .ref-stat-card__icon { background: var(--ref-purple-light); color: var(--ref-purple); }
.ref-stat-card__label { font-size: 10px; font-weight: 500; text-transform: uppercase; letter-spacing: .06em; color: var(--ref-gray-400); display: block; margin-bottom: 2px; }
.ref-stat-card__value { font-size: 22px; font-weight: 700; color: var(--ref-gray-900); }

/* Panel */
.ref-panel {
    background: var(--ref-white);
    border: 0.5px solid rgba(24,95,165,.25);
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,.03);
}
.ref-panel__head {
    display: flex; align-items: center; gap: 10px;
    padding: 16px 20px;
    border-bottom: 0.5px solid var(--ref-gray-200);
    background: var(--ref-gray-50);
    flex-wrap: wrap;
}
.ref-panel__head-left { display: flex; align-items: center; gap: 10px; flex: 1; }
.ref-panel__icon {
    width: 34px; height: 34px; border-radius: 8px;
    background: var(--ref-blue-light); color: var(--ref-blue);
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.ref-panel__title { font-size: 14px; font-weight: 600; color: var(--ref-gray-900); margin: 0; }
.ref-filter-wrap {
    display: flex; align-items: center; gap: 6px;
    border: 0.5px solid var(--ref-gray-200);
    border-radius: 7px; padding: 5px 10px;
    background: var(--ref-white); color: var(--ref-gray-500);
}
.ref-filter-select { border: none; outline: none; background: transparent; font-size: 12px; color: var(--ref-gray-700); cursor: pointer; }

/* Table */
.ref-table { width: 100%; border-collapse: collapse; min-width: 900px; }
.ref-table thead th {
    padding: .65rem .9rem; font-size: 10px; font-weight: 500;
    text-transform: uppercase; letter-spacing: .07em;
    color: var(--ref-gray-500); background: var(--ref-gray-50);
    border-bottom: 0.5px solid var(--ref-gray-200);
    text-align: left; white-space: nowrap;
}
.ref-table tbody td {
    padding: .75rem .9rem; font-size: 13px;
    color: var(--ref-gray-700); border-bottom: 0.5px solid var(--ref-gray-100);
    vertical-align: middle;
}
.ref-table tbody tr:last-child td { border-bottom: none; }
.ref-table tbody tr:hover td { background: var(--ref-gray-50); }

.ref-owner-cell { display: flex; align-items: center; gap: 10px; }
.ref-avatar {
    width: 34px; height: 34px; border-radius: 9px;
    display: flex; align-items: center; justify-content: center;
    font-size: 13px; font-weight: 700; flex-shrink: 0;
}
.ref-avatar--active { background: var(--ref-green-light); color: var(--ref-green-dark); }
.ref-avatar--inactive { background: var(--ref-gray-100); color: var(--ref-gray-400); }
.ref-owner-name { font-size: 13px; font-weight: 600; color: var(--ref-gray-900); }
.ref-owner-email { font-size: 11px; color: var(--ref-gray-400); }
.ref-active-dot { flex-shrink: 0; }
.ref-pulse {
    display: block; width: 8px; height: 8px;
    border-radius: 50%; background: #22c55e;
    animation: refPulse 2s infinite;
}
@keyframes refPulse {
    0%, 100% { opacity: 1; }
    50% { opacity: .4; }
}

.ref-type-badge {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 3px 9px; border-radius: 99px;
    font-size: 11px; font-weight: 500; white-space: nowrap;
}
.ref-type-badge--new { background: #FEF3C7; color: #92400E; }
.ref-type-badge--recurring { background: var(--ref-purple-light); color: var(--ref-purple); }
.ref-type-badge--other { background: var(--ref-gray-100); color: var(--ref-gray-500); }

.ref-date { color: var(--ref-gray-500); font-size: 12px; white-space: nowrap; }
.ref-amount { font-variant-numeric: tabular-nums; white-space: nowrap; font-size: 13px; }
.ref-amount--total { font-weight: 700; color: var(--ref-green-dark); }

.ref-view-btn {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 5px 11px; background: var(--ref-blue-light); color: var(--ref-blue);
    border: 0.5px solid #B5D4F4; border-radius: 6px;
    font-size: 11px; font-weight: 500; cursor: pointer; transition: all .13s; white-space: nowrap;
}
.ref-view-btn:hover { background: var(--ref-blue); color: #fff; }
.ref-empty { text-align: center; padding: 3rem 1rem !important; color: var(--ref-gray-400); }
.ref-empty__icon { display: flex; justify-content: center; margin-bottom: 10px; color: var(--ref-gray-200); }
.ref-pagination { padding: 14px 20px; border-top: 0.5px solid var(--ref-gray-200); background: var(--ref-gray-50); display: flex; justify-content: flex-end; }

/* Modal */
.ref-modal-overlay {
    position: fixed; inset: 0; background: rgba(17,24,39,.45);
    z-index: 99999; display: flex; align-items: center; justify-content: center;
}
.ref-modal-overlay[data-state="closed"] { display: none; }
.ref-modal-box {
    background: #fff; border-radius: 14px; width: 100%; max-width: 600px;
    max-height: 90vh; overflow: hidden; display: flex; flex-direction: column;
    box-shadow: 0 20px 50px rgba(0,0,0,.18);
}
.ref-modal-box__head {
    display: flex; align-items: center; gap: 12px;
    padding: 18px 20px; border-bottom: 0.5px solid var(--ref-gray-200);
    background: var(--ref-gray-50); flex-shrink: 0;
}
.ref-modal-box__head-left { display: flex; align-items: center; gap: 10px; flex: 1; }
.ref-modal-box__icon {
    width: 38px; height: 38px; border-radius: 10px;
    background: var(--ref-blue-light); color: var(--ref-blue);
    display: flex; align-items: center; justify-content: center;
}
.ref-modal-eyebrow { font-size: 10px; font-weight: 500; text-transform: uppercase; letter-spacing: .07em; color: var(--ref-gray-400); margin: 0; }
.ref-modal-title { font-size: 15px; font-weight: 600; color: var(--ref-gray-900); margin: 2px 0 0; }
.ref-modal-close {
    background: var(--ref-gray-100); border: 0.5px solid var(--ref-gray-200);
    font-size: 18px; color: var(--ref-gray-500); cursor: pointer;
    width: 30px; height: 30px; border-radius: 7px;
    display: flex; align-items: center; justify-content: center;
}
.ref-modal-close:hover { background: var(--ref-gray-200); }
.ref-modal-body { padding: 20px; overflow-y: auto; flex: 1; }

.ref-detail-loading {
    display: flex; flex-direction: column; align-items: center;
    justify-content: center; gap: 14px; padding: 60px 20px; color: var(--ref-gray-400);
}
.ref-detail-loading__spinner {
    width: 32px; height: 32px; border: 3px solid var(--ref-gray-200);
    border-top-color: var(--ref-blue); border-radius: 50%; animation: refSpin .7s linear infinite;
}
@keyframes refSpin { to { transform: rotate(360deg); } }
.ref-detail-error { text-align: center; padding: 60px 20px; color: var(--ref-gray-400); }

.ref-detail-stats {
    display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 18px;
}
.ref-detail-stat {
    background: var(--ref-gray-50); border: 0.5px solid var(--ref-gray-200);
    border-radius: 10px; padding: 14px;
    display: flex; flex-direction: column; gap: 3px;
}
.ref-detail-stat__label { font-size: 10px; font-weight: 500; text-transform: uppercase; letter-spacing: .05em; color: var(--ref-gray-400); }
.ref-detail-stat__value { font-size: 17px; font-weight: 700; color: var(--ref-gray-900); }
.ref-detail-stat__value--green { color: var(--ref-green-dark); }

.ref-detail-section { margin-bottom: 18px; }
.ref-detail-section__title { font-size: 12px; font-weight: 600; color: var(--ref-gray-900); margin: 0 0 8px; }

.ref-detail-table { width: 100%; border-collapse: collapse; font-size: 12px; min-width: 450px; }
.ref-detail-table thead th {
    padding: .5rem .75rem; font-size: 10px; font-weight: 500;
    text-transform: uppercase; letter-spacing: .05em; color: var(--ref-gray-400);
    background: var(--ref-gray-50); border-bottom: 0.5px solid var(--ref-gray-200);
    text-align: left; white-space: nowrap;
}
.ref-detail-table tbody td { padding: .55rem .75rem; color: var(--ref-gray-700); border-bottom: 0.5px solid var(--ref-gray-100); }
.ref-detail-table tbody tr:hover td { background: var(--ref-gray-50); }

@media(max-width: 768px) {
    .ref-stats-grid { grid-template-columns: 1fr 1fr; }
    .ref-modal-box { max-width: 100% !important; border-radius: 16px 16px 0 0 !important; margin-top: auto; }
    .ref-detail-stats { grid-template-columns: 1fr 1fr; }
}
@media(max-width: 480px) {
    .ref-stats-grid { grid-template-columns: 1fr; }
    .ref-detail-stats { grid-template-columns: 1fr; }
}
</style>
@endpush

@push('scripts')
<script>
(function() {
    'use strict';

    const modal = document.getElementById('refDetailModal');
    const loading = document.getElementById('refDetailLoading');
    const content = document.getElementById('refDetailContent');
    const error = document.getElementById('refDetailError');

    function openModal() {
        modal.removeAttribute('data-state');
        modal.style.setProperty('display', 'flex', 'important');
        document.body.style.overflow = 'hidden';
    }

    function closeModal() {
        modal.setAttribute('data-state', 'closed');
        modal.style.setProperty('display', 'none', 'important');
        document.body.style.overflow = '';
    }

    function showState(s) {
        loading.style.display = s === 'loading' ? 'flex' : 'none';
        content.style.display = s === 'content' ? 'block' : 'none';
        error.style.display = s === 'error' ? 'block' : 'none';
    }

    function fmt(val) {
        return parseFloat(val || 0).toLocaleString('en-KE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function esc(str) {
        var d = document.createElement('div');
        d.appendChild(document.createTextNode(str));
        return d.innerHTML;
    }

    document.addEventListener('click', function(e) {
        var btn = e.target.closest('.ref-view-btn');
        if (!btn) return;
        e.preventDefault();
        
        var id = btn.getAttribute('data-id');
        var name = btn.getAttribute('data-name');
        document.getElementById('refDetailName').textContent = name;
        
        openModal();
        showState('loading');
        
        fetch('{{ route("affiliate.referrals.show", ":id") }}'.replace(':id', id), {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(function(r) { return r.json(); })
        .then(function(res) {
            if (res.success) {
                populateDetail(res.data);
                showState('content');
            } else {
                showState('error');
            }
        })
        .catch(function() { showState('error'); });
    });

    function populateDetail(data) {
        // Stats
        document.getElementById('refDetailStats').innerHTML =
            '<div class="ref-detail-stat">' +
                '<span class="ref-detail-stat__label">Total Earned</span>' +
                '<span class="ref-detail-stat__value ref-detail-stat__value--green">KSh ' + fmt(data.stats.total_earned) + '</span>' +
            '</div>' +
            '<div class="ref-detail-stat">' +
                '<span class="ref-detail-stat__label">Subscriptions</span>' +
                '<span class="ref-detail-stat__value">KSh ' + fmt(data.stats.subscription_total) + '</span>' +
            '</div>' +
            '<div class="ref-detail-stat">' +
                '<span class="ref-detail-stat__label">Rent</span>' +
                '<span class="ref-detail-stat__value">KSh ' + fmt(data.stats.rent_total) + '</span>' +
            '</div>' +
            '<div class="ref-detail-stat">' +
                '<span class="ref-detail-stat__label">Marketplace</span>' +
                '<span class="ref-detail-stat__value">KSh ' + fmt(data.stats.marketplace_total) + '</span>' +
            '</div>';

        // Monthly earnings
        var monthlyBody = document.getElementById('refMonthlyBody');
        monthlyBody.innerHTML = '';
        if (data.monthly_earnings && data.monthly_earnings.length) {
            data.monthly_earnings.forEach(function(r) {
                monthlyBody.innerHTML += '<tr>' +
                    '<td style="font-weight:500;">' + esc(r.period) + '</td>' +
                    '<td>' + (r.subscription > 0 ? 'KSh ' + fmt(r.subscription) : '—') + '</td>' +
                    '<td>' + (r.rent > 0 ? 'KSh ' + fmt(r.rent) : '—') + '</td>' +
                    '<td>' + (r.marketplace > 0 ? 'KSh ' + fmt(r.marketplace) : '—') + '</td>' +
                    '<td style="font-weight:600;color:var(--ref-green-dark);">KSh ' + fmt(r.total) + '</td>' +
                '</tr>';
            });
        } else {
            monthlyBody.innerHTML = '<tr><td colspan="5" style="text-align:center;padding:20px;color:var(--ref-gray-400);">No earnings data</td></tr>';
        }

        // Recent commissions
        var commBody = document.getElementById('refCommissionsBody');
        commBody.innerHTML = '';
        if (data.recent_commissions && data.recent_commissions.length) {
            data.recent_commissions.forEach(function(c) {
                commBody.innerHTML += '<tr>' +
                    '<td>' + esc(c.date) + '</td>' +
                    '<td>' + esc(c.source) + '</td>' +
                    '<td>' + esc(c.type) + '</td>' +
                    '<td>' + esc(c.rate) + '</td>' +
                    '<td style="font-weight:600;color:var(--ref-green-dark);">KSh ' + fmt(c.amount) + '</td>' +
                '</tr>';
            });
        } else {
            commBody.innerHTML = '<tr><td colspan="5" style="text-align:center;padding:20px;color:var(--ref-gray-400);">No recent commissions</td></tr>';
        }
    }

    document.getElementById('refCloseDetail').addEventListener('click', closeModal);
    modal.addEventListener('click', function(e) { if (e.target === modal) closeModal(); });
    document.addEventListener('keydown', function(e) { if (e.key === 'Escape') closeModal(); });

    // Filter
    var filter = document.getElementById('refStatusFilter');
    if (filter) {
        filter.addEventListener('change', function() {
            var val = this.value;
            document.querySelectorAll('#refTable tbody tr').forEach(function(row) {
                if (!val) { row.style.display = ''; return; }
                if (val === 'active') { row.style.display = row.dataset.status === 'active' ? '' : 'none'; }
                else { row.style.display = row.dataset.type === val ? '' : 'none'; }
            });
        });
    }
})();
</script>
@endpush