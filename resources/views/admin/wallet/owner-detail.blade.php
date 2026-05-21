{{--
    FILE: resources/views/admin/wallet/owner-detail.blade.php
    ACTION: Replace your existing file entirely.
    CHANGE FROM ORIGINAL: Added "Source" column to transactions table + source filter dropdown.
--}}
@extends('admin.layouts.app')

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="page-content-wrapper bg-white p-30 radius-20">
                <div class="container-fluid">

                    {{-- ── Page Header ── --}}
                    <div class="ow-header">
                        <div>
                            <nav aria-label="breadcrumb">
                                <ol class="ow-breadcrumb">
                                    <li>
                                        <a href="{{ route('admin.dashboard') }}">
                                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                            {{ __('Dashboard') }}
                                        </a>
                                    </li>
                                    <li><a href="{{ route('admin.wallet.commissions') }}">{{ __('Commissions') }}</a></li>
                                    <li aria-current="page">{{ $wallet->user->name ?? __('Owner') }}</li>
                                </ol>
                            </nav>
                            <div class="ow-hero">
                                <div class="ow-hero__avatar">{{ strtoupper(substr($wallet->user->name ?? 'O', 0, 1)) }}</div>
                                <div>
                                    <h1 class="ow-page-title">{{ $wallet->user->name ?? '—' }}</h1>
                                    <p class="ow-page-sub">{{ $wallet->user->email ?? '' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ── Stat Cards ── --}}
                    <div class="ow-stats">
                        <div class="ow-stat ow-stat--blue">
                            <div class="ow-stat__top">
                                <div class="ow-stat__icon ow-stat__icon--blue">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none"><rect x="1" y="4" width="22" height="16" rx="3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" stroke="currentColor" stroke-width="1.8"/></svg>
                                </div>
                                <span class="ow-stat__label">{{ __('Current Balance') }}</span>
                            </div>
                            <p class="ow-stat__value">KSh {{ number_format($wallet->balance, 2) }}</p>
                            <div class="ow-stat__bar ow-stat__bar--blue"></div>
                        </div>
                        <div class="ow-stat ow-stat--green">
                            <div class="ow-stat__top">
                                <div class="ow-stat__icon ow-stat__icon--green">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><polyline points="17 6 23 6 23 12" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </div>
                                <span class="ow-stat__label">{{ __('Total Earned (Net)') }}</span>
                            </div>
                            <p class="ow-stat__value">KSh {{ number_format($totalEarned, 2) }}</p>
                            <div class="ow-stat__bar ow-stat__bar--green"></div>
                        </div>
                        <div class="ow-stat ow-stat--amber">
                            <div class="ow-stat__top">
                                <div class="ow-stat__icon ow-stat__icon--amber">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.8"/><path d="M15 9H9.5a2.5 2.5 0 000 5h5a2.5 2.5 0 010 5H8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                                </div>
                                <span class="ow-stat__label">{{ __('Commission to Platform') }}</span>
                            </div>
                            <p class="ow-stat__value">KSh {{ number_format($totalCommission, 2) }}</p>
                            <div class="ow-stat__bar ow-stat__bar--amber"></div>
                        </div>
                        <div class="ow-stat ow-stat--purple">
                            <div class="ow-stat__top">
                                <div class="ow-stat__icon ow-stat__icon--purple">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none"><path d="M12 5v14M5 12l7 7 7-7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </div>
                                <span class="ow-stat__label">{{ __('Total Withdrawn') }}</span>
                            </div>
                            <p class="ow-stat__value">KSh {{ number_format($totalWithdrawn, 2) }}</p>
                            <div class="ow-stat__bar ow-stat__bar--purple"></div>
                        </div>
                    </div>

                    {{-- ── Transactions Panel ── --}}
                    <div class="ow-panel">
                        <div class="ow-panel__head">
                            <div class="ow-panel__head-left">
                                <div class="ow-panel__icon">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </div>
                                <h4 class="ow-panel__title">{{ __('Transaction History') }}</h4>
                            </div>
                            {{-- Filters --}}
                            <div class="ow-filters">
                                <div class="ow-filter">
                                    <label class="ow-filter__label">{{ __('Type') }}</label>
                                    <div class="ow-filter__wrap">
                                        <select id="typeFilter" class="ow-filter__select">
                                            <option value="">{{ __('All Types') }}</option>
                                            <option value="credit">{{ __('Credit') }}</option>
                                            <option value="debit">{{ __('Debit') }}</option>
                                            <option value="withdrawal">{{ __('Withdrawal') }}</option>
                                            <option value="refund">{{ __('Refund') }}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="ow-filter">
                                    <label class="ow-filter__label">{{ __('Source') }}</label>
                                    <div class="ow-filter__wrap">
                                        <select id="sourceFilter" class="ow-filter__select">
                                            <option value="">{{ __('All Sources') }}</option>
                                            <option value="marketplace">{{ __('Marketplace') }}</option>
                                            <option value="rent">{{ __('Rent') }}</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="ow-table" id="txnTable">
                                <thead>
                                    <tr>
                                        <th>{{ __('Date') }}</th>
                                        <th>{{ __('Description') }}</th>
                                        <th>{{ __('Source') }}</th>
                                        <th>{{ __('Gross') }}</th>
                                        <th>{{ __('Commission Rate') }}</th>
                                        <th>{{ __('Commission (KSh)') }}</th>
                                        <th>{{ __('Net Amount') }}</th>
                                        <th>{{ __('Type') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($transactions as $txn)
                                    <tr data-type="{{ $txn->type }}" data-source="{{ $txn->transaction_source }}">
                                        <td class="ow-td-date">{{ $txn->created_at->format('d M Y, H:i') }}</td>
                                        <td class="ow-td-desc">{{ $txn->description }}</td>
                                        <td>
                                            @if($txn->transaction_source === 'rent')
                                                <span class="ow-source-badge ow-source-badge--rent">
                                                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                                    {{ __('Rent') }}
                                                </span>
                                            @else
                                                <span class="ow-source-badge ow-source-badge--marketplace">
                                                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4zM3 6h18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                                    {{ __('Marketplace') }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="ow-td-num">
                                            @if($txn->gross_amount) KSh {{ number_format($txn->gross_amount, 2) }}
                                            @else <span class="ow-na">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($txn->commission_rate)
                                                <span class="ow-rate-pill">{{ number_format($txn->commission_rate, 1) }}%</span>
                                            @else <span class="ow-na">—</span>
                                            @endif
                                        </td>
                                        <td class="ow-td-num ow-td-num--commission">
                                            @if($txn->commission_amount) KSh {{ number_format($txn->commission_amount, 2) }}
                                            @else <span class="ow-na">—</span>
                                            @endif
                                        </td>
                                        <td class="ow-td-num ow-td-num--net">KSh {{ number_format($txn->net_amount, 2) }}</td>
                                        <td>
                                            <span class="ow-badge ow-badge--{{ $txn->type }}">
                                                <span class="ow-badge__dot"></span>
                                                {{ ucfirst($txn->type) }}
                                            </span>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="8" class="ow-empty">
                                            <div class="ow-empty__icon">
                                                <svg width="28" height="28" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.5"/></svg>
                                            </div>
                                            <p>{{ __('No transactions found for this owner.') }}</p>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @if($transactions->hasPages())
                        <div class="ow-pagination">{{ $transactions->links() }}</div>
                        @endif
                    </div>

                    <div style="margin-top:20px">
                        <a href="{{ route('admin.wallet.commissions') }}" class="ow-back-link">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none"><path d="M19 12H5M12 5l-7 7 7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            {{ __('Back to Commissions Dashboard') }}
                        </a>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('style')
<style>
:root{--blue:#185FA5;--blue-hover:#0F4A84;--blue-light:#E6F1FB;--blue-border:#B5D4F4;--blue-faint:#185ea56e;--green:#1D9E75;--green-dark:#0F6E56;--green-light:#E1F5EE;--amber:#854F0B;--amber-light:#FAEEDA;--amber-border:#F5D9A8;--red:#993C1D;--red-light:#FAECE7;--purple:#534AB7;--gray-900:#111827;--gray-700:#374151;--gray-500:#6b7280;--gray-400:#9ca3af;--gray-200:#e5e7eb;--gray-100:#f3f4f6;--gray-50:#fafafa;--white:#ffffff}
.ow-header{margin-bottom:24px}
.ow-breadcrumb{display:flex;align-items:center;gap:5px;list-style:none;padding:0;margin:0 0 14px;font-size:12px;color:var(--gray-400)}
.ow-breadcrumb li:not(:last-child)::after{content:'';display:inline-block;width:5px;height:5px;border-right:1.5px solid #d1d5db;border-top:1.5px solid #d1d5db;transform:rotate(45deg);margin-left:5px;opacity:.5}
.ow-breadcrumb a{display:inline-flex;align-items:center;gap:4px;color:var(--blue);text-decoration:none;font-weight:500}
.ow-hero{display:flex;align-items:center;gap:14px}
.ow-hero__avatar{width:48px;height:48px;border-radius:12px;background:var(--blue);color:var(--white);display:flex;align-items:center;justify-content:center;font-size:20px;font-weight:700;flex-shrink:0;border:2px solid var(--blue-light);box-shadow:0 4px 12px rgba(24,95,165,.2)}
.ow-page-title{font-size:22px;font-weight:500;color:var(--gray-900);margin:0 0 3px}
.ow-page-sub{font-size:13px;color:var(--gray-500);margin:0}
.ow-stats{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:20px}
.ow-stat{background:var(--white);border:0.5px solid var(--blue-faint);border-radius:14px;padding:20px;display:flex;flex-direction:column;gap:12px;position:relative;overflow:hidden;transition:all .25s ease;box-shadow:0 4px 12px rgba(0,0,0,.04),0 0 0 1px rgba(24,95,165,.05),0 6px 18px rgba(24,95,165,.06)}
.ow-stat:hover{border-color:var(--blue);transform:translateY(-3px);box-shadow:0 10px 25px rgba(0,0,0,.06),0 0 0 1px rgba(24,95,165,.12),0 12px 30px rgba(24,95,165,.18)}
.ow-stat__top{display:flex;align-items:center;gap:12px}
.ow-stat__icon{width:40px;height:40px;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.ow-stat__icon--blue{background:var(--blue-light);color:var(--blue)}.ow-stat__icon--green{background:var(--green-light);color:var(--green)}.ow-stat__icon--amber{background:var(--amber-light);color:var(--amber)}.ow-stat__icon--purple{background:#EEEDF9;color:var(--purple)}
.ow-stat__label{font-size:11px;font-weight:500;text-transform:uppercase;letter-spacing:.07em;color:var(--gray-400);margin:0}
.ow-stat__value{font-size:24px;font-weight:700;color:var(--gray-900);margin:0;line-height:1}
.ow-stat__bar{position:absolute;bottom:0;left:0;right:0;height:3px}
.ow-stat__bar--blue{background:var(--blue)}.ow-stat__bar--green{background:var(--green)}.ow-stat__bar--amber{background:#F59E0B}.ow-stat__bar--purple{background:var(--purple)}
.ow-panel{background:var(--white);border:0.5px solid var(--blue-faint);border-radius:12px;overflow:hidden;box-shadow:0 4px 12px rgba(0,0,0,.04),0 0 0 1px rgba(24,95,165,.05),0 6px 18px rgba(24,95,165,.06)}
.ow-panel__head{display:flex;align-items:center;gap:10px;padding:16px 20px;border-bottom:0.5px solid var(--gray-200);background:var(--gray-50);flex-wrap:wrap}
.ow-panel__head-left{display:flex;align-items:center;gap:10px;flex:1}
.ow-panel__icon{width:34px;height:34px;border-radius:8px;background:var(--blue-light);color:var(--blue);display:flex;align-items:center;justify-content:center;flex-shrink:0}
.ow-panel__title{font-size:14px;font-weight:600;color:var(--gray-900);margin:0}
.ow-filters{display:flex;align-items:center;gap:10px}
.ow-filter{display:flex;align-items:center;gap:8px}
.ow-filter__label{font-size:10px;font-weight:500;text-transform:uppercase;letter-spacing:.07em;color:var(--gray-400)}
.ow-filter__wrap{display:flex;align-items:center;gap:6px;border:0.5px solid var(--gray-200);border-radius:7px;padding:5px 10px;background:var(--white);color:var(--gray-500)}
.ow-filter__select{border:none;outline:none;background:transparent;font-size:12px;color:var(--gray-700);cursor:pointer;padding:0}
.ow-filter__wrap:focus-within{border-color:var(--blue);box-shadow:0 0 0 3px rgba(24,95,165,.08)}
.ow-table{width:100%;border-collapse:collapse}
.ow-table thead th{padding:.65rem 1rem;font-size:10px;font-weight:500;text-transform:uppercase;letter-spacing:.07em;color:var(--gray-500);background:var(--gray-50);border-bottom:0.5px solid var(--gray-200);text-align:left;white-space:nowrap}
.ow-table tbody td{padding:.8rem 1rem;font-size:13px;color:var(--gray-700);border-bottom:0.5px solid var(--gray-100);vertical-align:middle}
.ow-table tbody tr:last-child td{border-bottom:none}
.ow-table tbody tr:nth-child(even) td{background:var(--gray-50)}
.ow-table tbody tr:hover td{background:var(--gray-100)}
.ow-td-date{color:var(--gray-500);white-space:nowrap;font-size:12px}
.ow-td-desc{max-width:240px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.ow-td-num{font-variant-numeric:tabular-nums;white-space:nowrap}
.ow-td-num--net{font-weight:600;color:var(--green-dark)}.ow-td-num--commission{color:var(--amber)}.ow-na{color:var(--gray-200)}
.ow-rate-pill{display:inline-flex;align-items:center;padding:2px 7px;border-radius:99px;background:var(--amber-light);color:var(--amber);border:0.5px solid var(--amber-border);font-size:10px;font-weight:500}
.ow-badge{display:inline-flex;align-items:center;gap:5px;padding:3px 9px;border-radius:99px;font-size:11px;font-weight:500;border:0.5px solid transparent}
.ow-badge__dot{width:6px;height:6px;border-radius:50%;background:currentColor;opacity:.7}
.ow-badge--credit{background:var(--green-light);color:var(--green-dark)}.ow-badge--debit{background:var(--red-light);color:var(--red)}.ow-badge--withdrawal{background:#EEEDF9;color:var(--purple)}.ow-badge--refund{background:var(--amber-light);color:var(--amber);border-color:var(--amber-border)}
/* Source badges */
.ow-source-badge{display:inline-flex;align-items:center;gap:5px;padding:2px 8px;border-radius:99px;font-size:10px;font-weight:600;white-space:nowrap}
.ow-source-badge--rent{background:#EDE9FE;color:#5B21B6;border:0.5px solid #C4B5FD}
.ow-source-badge--marketplace{background:var(--blue-light);color:var(--blue);border:0.5px solid var(--blue-border)}
.ow-empty{text-align:center;padding:3rem 1rem !important;color:var(--gray-400)}
.ow-empty__icon{display:flex;justify-content:center;margin-bottom:10px;color:var(--gray-200)}.ow-empty p{font-size:13px;margin:0}
.ow-pagination{padding:14px 20px;border-top:0.5px solid var(--gray-200);background:var(--gray-50);display:flex;justify-content:flex-end}
.ow-back-link{display:inline-flex;align-items:center;gap:6px;font-size:12px;font-weight:500;color:var(--gray-500);text-decoration:none;transition:color .15s;border:0.5px solid var(--gray-200);border-radius:7px;padding:6px 12px;background:var(--gray-50)}
.ow-back-link:hover{color:var(--blue);border-color:var(--blue-border);background:var(--blue-light)}
@media(max-width:1100px){.ow-stats{grid-template-columns:repeat(2,1fr)}}
@media(max-width:600px){.ow-stats{grid-template-columns:1fr}}
</style>
@endpush

@push('script')
<script>
(function () {
    'use strict';
    function applyFilters() {
        var typeVal   = document.getElementById('typeFilter').value;
        var sourceVal = document.getElementById('sourceFilter').value;
        document.querySelectorAll('#txnTable tbody tr[data-type]').forEach(function (row) {
            var typeMatch   = !typeVal   || row.dataset.type   === typeVal;
            var sourceMatch = !sourceVal || row.dataset.source === sourceVal;
            row.style.display = (typeMatch && sourceMatch) ? '' : 'none';
        });
    }
    document.getElementById('typeFilter').addEventListener('change', applyFilters);
    document.getElementById('sourceFilter').addEventListener('change', applyFilters);
})();
</script>
@endpush