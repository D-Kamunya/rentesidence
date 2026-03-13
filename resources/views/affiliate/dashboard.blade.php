@extends('affiliate.layouts.app')

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="page-content-wrapper bg-white p-30 radius-20">
                <div class="container">

                    {{-- Page Header --}}
                    <div class="dash-header mb-4">
                        <div>
                            <h2 class="dash-title">{{ __('Affiliate Dashboard') }}</h2>
                            <p class="dash-subtitle">
                                {{ __('Welcome back') }}, <strong>{{ auth()->user()->name }}</strong>
                                <span class="iconify font-24" data-icon="openmoji:waving-hand"></span>
                            </p>
                        </div>
                        @if($isCertified)
                            <a href="{{ route('affiliate.academy.certificate') }}" class="certified-badge">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none">
                                    <path d="M12 2l2.9 6 6.6.9-4.8 4.6 1.1 6.5L12 17l-5.8 3 1.1-6.5L2.5 9l6.6-.9L12 2z" fill="currentColor"/>
                                </svg>
                                Certified Centresidence Partner
                            </a>
                        @endif
                    </div>

                    {{-- Summary Cards --}}
                    <div class="row g-3 mb-4">

                        <div class="col-6 col-md-4 col-lg-3">
                            <div class="stat-card">
                                <div class="stat-card__icon stat-card__icon--green">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z" fill="currentColor"/></svg>
                                </div>
                                <p class="stat-card__label">Total Earnings</p>
                                <p class="stat-card__value stat-card__value--green">Ksh {{ number_format($summary['total_commissions'], 2) }}</p>
                            </div>
                        </div>

                        <div class="col-6 col-md-4 col-lg-3">
                            <div class="stat-card">
                                <div class="stat-card__icon stat-card__icon--blue">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><rect x="3" y="4" width="18" height="18" rx="2" stroke="currentColor" stroke-width="2"/><path d="M16 2v4M8 2v4M3 10h18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                                </div>
                                <p class="stat-card__label">This Month</p>
                                <p class="stat-card__value stat-card__value--blue">Ksh {{ number_format($summary['current_monthly_earning'], 2) }}</p>
                            </div>
                        </div>

                        <div class="col-6 col-md-4 col-lg-3">
                            <div class="stat-card">
                                <div class="stat-card__icon stat-card__icon--teal">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z" fill="currentColor"/></svg>
                                </div>
                                <p class="stat-card__label">New Clients</p>
                                <p class="stat-card__value stat-card__value--teal">{{ $summary['new_clients'] }}</p>
                            </div>
                        </div>

                        <div class="col-6 col-md-4 col-lg-3">
                            <div class="stat-card">
                                <div class="stat-card__icon stat-card__icon--purple">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z" fill="currentColor"/></svg>
                                </div>
                                <p class="stat-card__label">Active Referrals</p>
                                <p class="stat-card__value stat-card__value--purple">{{ $summary['recurring_clients'] }}</p>
                            </div>
                        </div>

                        <div class="col-6 col-md-4 col-lg-3">
                            <div class="stat-card">
                                <div class="stat-card__icon stat-card__icon--amber">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M21 18v1a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v1M16 12H8m4-4v8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                                </div>
                                <p class="stat-card__label">Available Balance</p>
                                <p class="stat-card__value stat-card__value--amber">Ksh {{ number_format($summary['available_balance'], 2) }}</p>
                            </div>
                        </div>

                        <div class="col-6 col-md-4 col-lg-3">
                            <div class="stat-card">
                                <div class="stat-card__icon stat-card__icon--gray">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </div>
                                <p class="stat-card__label">Total Payouts</p>
                                <p class="stat-card__value stat-card__value--gray">Ksh {{ number_format($summary['total_payouts'], 2) }}</p>
                            </div>
                        </div>

                        <div class="col-6 col-md-4 col-lg-3">
                            <div class="stat-card">
                                <div class="stat-card__icon stat-card__icon--coral">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </div>
                                <p class="stat-card__label">Total Referrals</p>
                                <p class="stat-card__value stat-card__value--coral">{{ $summary['total_referrals'] }}</p>
                            </div>
                        </div>

                    </div>

                    {{-- Chart --}}
                    <div class="dash-card mb-4">
                        <div class="dash-card__head">
                            <span style="font-weight:500;font-size:14px;">Earnings Overview</span>
                        </div>
                        <div class="dash-card__body" style="padding:1.25rem 1.5rem;">
                            <canvas id="earningsChart" height="90"></canvas>
                        </div>
                    </div>

                    {{-- Recent Commissions --}}
                    <div class="dash-card">
                        <div class="dash-card__head d-flex align-items-center justify-content-between">
                            <span style="font-weight:500;font-size:14px;">Recent Commissions</span>
                            <span style="font-size:12px;color:#9ca3af;">{{ count($recentCommissions) }} records</span>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr style="border-bottom:0.5px solid #e5e7eb;background:#fafafa;">
                                        <th class="dash-th">Date</th>
                                        <th class="dash-th">Client</th>
                                        <th class="dash-th">Subscription</th>
                                        <th class="dash-th">Type</th>
                                        <th class="dash-th">Amount</th>
                                        <th class="dash-th">Rate</th>
                                        <th class="dash-th">Commission</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recentCommissions as $commission)
                                        <tr style="border-bottom:0.5px solid #f3f4f6;">
                                            <td class="dash-td" style="color:#6b7280;font-size:13px;">{{ $commission['date'] }}</td>
                                            <td class="dash-td" style="font-weight:500;font-size:14px;">{{ $commission['owner'] }}</td>
                                            <td class="dash-td" style="font-size:13px;color:#374151;">{{ $commission['package'] }}</td>
                                            <td class="dash-td">
                                                @if($commission['type'] == NEW_CLIENT)
                                                    <span class="comm-badge comm-badge--new">New Client</span>
                                                @else
                                                    <span class="comm-badge comm-badge--recurring">Recurring</span>
                                                @endif
                                            </td>
                                            <td class="dash-td" style="font-size:13px;">Ksh {{ number_format($commission['amount'], 2) }}</td>
                                            <td class="dash-td" style="font-size:13px;color:#6b7280;">
                                                {{ $commission['type'] == NEW_CLIENT ? getOption('FIRST_TIME_COMMISSION_RATE') : getOption('RECURRING_COMMISSION_RATE') }}%
                                            </td>
                                            <td class="dash-td" style="font-weight:500;font-size:13px;color:#0F6E56;">
                                                Ksh {{ number_format($commission['type'] == NEW_CLIENT ? $commission['amount'] * (getOption('FIRST_TIME_COMMISSION_RATE') / 100) : $commission['amount'] * (getOption('RECURRING_COMMISSION_RATE') / 100), 2) }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-5" style="color:#9ca3af;font-size:14px;">
                                                No commissions yet.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
                {{-- Floating Register Lead Button --}}
                <a href="" class="fab-register-lead" id="fabBtn">
                    <span class="fab-register-lead__icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                            <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/>
                        </svg>
                    </span>
                    <span class="fab-register-lead__label">Register Lead</span>
                </a>
            </div>
        </div>
    </div>
</div>

<style>
    /* ── Page header ─────────────────────────────────────── */
    .dash-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 1rem;
    }
    .dash-title {
        font-size: 22px;
        font-weight: 500;
        color: #111827;
        margin: 0 0 4px;
    }
    .dash-subtitle {
        font-size: 14px;
        color: #6b7280;
        margin: 0;
    }

    /* ── Certified badge ─────────────────────────────────── */
    .certified-badge {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        background: #78350f;
        color: #fde68a;
        padding: 8px 16px;
        border-radius: 8px;
        font-weight: 500;
        font-size: 13px;
        letter-spacing: .03em;
        border: 1px solid #92400e;
        text-decoration: none;
        transition: background .15s;
    }
    .certified-badge:hover { background: #92400e; color: #fde68a; }

    /* ── Stat cards ──────────────────────────────────────── */
    .stat-card {
        background: #fff;
        border: 0.5px solid #e5e7eb;
        border-radius: 12px;
        padding: 1rem 1.1rem;
        height: 100%;
        transition: box-shadow .2s, transform .2s;
    }
    .stat-card:hover {
        box-shadow: 0 6px 18px rgba(0,0,0,.07);
        transform: translateY(-2px);
    }
    .stat-card__icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 34px;
        height: 34px;
        border-radius: 8px;
        margin-bottom: 10px;
    }
    .stat-card__label {
        font-size: 11px;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: .06em;
        color: #9ca3af;
        margin: 0 0 4px;
    }
    .stat-card__value {
        font-size: 20px;
        font-weight: 500;
        margin: 0;
        line-height: 1.2;
    }

    /* Icon + value colors */
    .stat-card__icon--green  { background: #E1F5EE; color: #0F6E56; }
    .stat-card__icon--blue   { background: #E6F1FB; color: #185FA5; }
    .stat-card__icon--teal   { background: #E1F5EE; color: #1D9E75; }
    .stat-card__icon--purple { background: #EEEDFE; color: #534AB7; }
    .stat-card__icon--amber  { background: #FAEEDA; color: #854F0B; }
    .stat-card__icon--gray   { background: #f3f4f6; color: #5F5E5A; }
    .stat-card__icon--coral  { background: #FAECE7; color: #993C1D; }

    .stat-card__value--green  { color: #0F6E56; }
    .stat-card__value--blue   { color: #185FA5; }
    .stat-card__value--teal   { color: #1D9E75; }
    .stat-card__value--purple { color: #534AB7; }
    .stat-card__value--amber  { color: #854F0B; }
    .stat-card__value--gray   { color: #444441; }
    .stat-card__value--coral  { color: #993C1D; }

    /* ── Shared card ─────────────────────────────────────── */
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

    /* ── Table ───────────────────────────────────────────── */
    .dash-th {
        padding: .75rem 1.1rem;
        font-size: 11px;
        font-weight: 500;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: .06em;
        border: none;
        white-space: nowrap;
    }
    .dash-td {
        padding: .8rem 1.1rem;
        border: none;
        vertical-align: middle;
    }

    /* ── Commission type badges ──────────────────────────── */
    .comm-badge {
        display: inline-flex;
        align-items: center;
        font-size: 11px;
        font-weight: 500;
        padding: 3px 10px;
        border-radius: 99px;
        white-space: nowrap;
    }
    .comm-badge--new       { background: #E1F5EE; color: #0F6E56; }
    .comm-badge--recurring { background: #f3f4f6; color: #5F5E5A; }
    /* ── Floating Action Button ──────────────────────────── */
    .fab-register-lead {
        position: fixed;
        bottom: 32px;
        right: 32px;
        z-index: 999;
        display: inline-flex;
        align-items: center;
        gap: 0;
        background: #185FA5;
        color: #fff;
        text-decoration: none;
        border-radius: 99px;
        box-shadow: 0 4px 20px rgba(24, 95, 165, 0.35);
        padding: 14px 16px;
        overflow: hidden;
        max-width: 52px;
        transition: max-width .35s cubic-bezier(.4,0,.2,1),
                    padding .35s cubic-bezier(.4,0,.2,1),
                    box-shadow .2s ease,
                    transform .2s ease;
        white-space: nowrap;
    }
    .fab-register-lead:hover {
        max-width: 200px;
        padding: 14px 22px 14px 18px;
        box-shadow: 0 8px 28px rgba(24, 95, 165, 0.4);
        transform: translateY(-2px);
        color: #fff;
    }
    .fab-register-lead__icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .fab-register-lead__label {
        font-size: 14px;
        font-weight: 500;
        max-width: 0;
        opacity: 0;
        overflow: hidden;
        margin-left: 0;
        transition: max-width .35s cubic-bezier(.4,0,.2,1),
                    opacity .25s ease,
                    margin-left .35s ease;
    }
    .fab-register-lead:hover .fab-register-lead__label {
        max-width: 140px;
        opacity: 1;
        margin-left: 10px;
    }

    /* Shrink to icon-only on mobile, keep it reachable */
    @media (max-width: 576px) {
        .fab-register-lead {
            bottom: 24px;
            right: 20px;
            padding: 14px;
        }
    }
</style>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var ctx = document.getElementById('earningsChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: @json(array_column($commissionTrends, 'month')),
            datasets: [{
                label: 'Monthly Earnings (Ksh)',
                data: @json(array_column($commissionTrends, 'amount')),
                borderColor: '#1D9E75',
                backgroundColor: 'rgba(29,158,117,0.08)',
                tension: 0.4,
                fill: true,
                borderWidth: 2,
                pointBackgroundColor: '#1D9E75',
                pointRadius: 4,
                pointHoverRadius: 6
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#111827',
                    titleColor: '#fff',
                    bodyColor: '#d1fae5',
                    padding: 10,
                    cornerRadius: 8,
                    callbacks: {
                        label: ctx => ' Ksh ' + ctx.parsed.y.toLocaleString()
                    }
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { color: '#9ca3af', font: { size: 12 } }
                },
                y: {
                    grid: { color: '#f3f4f6' },
                    ticks: {
                        color: '#9ca3af',
                        font: { size: 12 },
                        callback: v => 'Ksh ' + v.toLocaleString()
                    }
                }
            }
        }
    });
});
</script>
@endpush