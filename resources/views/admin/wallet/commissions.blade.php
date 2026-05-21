@extends('admin.layouts.app')

@section('content')

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="page-content-wrapper bg-white p-30 radius-20">
                <div class="container-fluid">

                    {{-- ── Page Header ── --}}
                    <div class="cm-header">
                        <div>
                            <nav aria-label="breadcrumb">
                                <ol class="cm-breadcrumb">
                                    <li>
                                        <a href="{{ route('admin.dashboard') }}">
                                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                            {{ __('Dashboard') }}
                                        </a>
                                    </li>
                                    <li aria-current="page">{{ __('Commissions') }}</li>
                                </ol>
                            </nav>
                            <h1 class="cm-page-title">{{ __('Commissions & Wallets') }}</h1>
                            <p class="cm-page-sub">{{ __('Platform earnings, owner payouts, and withdrawal management') }}</p>
                        </div>
                    </div>

                    {{-- ── KPI Strip ── --}}
                    <div class="cm-kpi-grid">

                        <div class="cm-kpi cm-kpi--blue">
                            <div class="cm-kpi__top">
                                <div class="cm-kpi__icon cm-kpi__icon--blue">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </div>
                                <span class="cm-kpi__label">{{ __('Total GMV') }}</span>
                            </div>
                            <p class="cm-kpi__value">KSh {{ number_format($totalGmv, 2) }}</p>
                            <p class="cm-kpi__sub">{{ __('All transactions') }}</p>
                            <div class="cm-kpi__bar cm-kpi__bar--blue"></div>
                        </div>

                        <div class="cm-kpi cm-kpi--amber">
                            <div class="cm-kpi__top">
                                <div class="cm-kpi__icon cm-kpi__icon--amber">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.8"/><path d="M15 9H9.5a2.5 2.5 0 000 5h5a2.5 2.5 0 010 5H8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                                </div>
                                <span class="cm-kpi__label">{{ __('Platform Commission') }}</span>
                            </div>
                            <p class="cm-kpi__value">KSh {{ number_format($totalCommission, 2) }}</p>
                            <p class="cm-kpi__sub">{{ __('Earned across all owners') }}</p>
                            <div class="cm-kpi__bar cm-kpi__bar--amber"></div>
                        </div>

                        <div class="cm-kpi cm-kpi--green">
                            <div class="cm-kpi__top">
                                <div class="cm-kpi__icon cm-kpi__icon--green">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><circle cx="9" cy="7" r="4" stroke="currentColor" stroke-width="1.8"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                                </div>
                                <span class="cm-kpi__label">{{ __('Owner Wallet Total') }}</span>
                            </div>
                            <p class="cm-kpi__value">KSh {{ number_format($totalOwnerBalance, 2) }}</p>
                            <p class="cm-kpi__sub">{{ __('Pending payout to owners') }}</p>
                            <div class="cm-kpi__bar cm-kpi__bar--green"></div>
                        </div>

                        <div class="cm-kpi cm-kpi--purple">
                            <div class="cm-kpi__top">
                                <div class="cm-kpi__icon cm-kpi__icon--purple">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none"><path d="M12 5v14M5 12l7 7 7-7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </div>
                                <span class="cm-kpi__label">{{ __('Total Withdrawn') }}</span>
                            </div>
                            <p class="cm-kpi__value">KSh {{ number_format($totalWithdrawn, 2) }}</p>
                            <p class="cm-kpi__sub">{{ __('Paid out to owners') }}</p>
                            <div class="cm-kpi__bar cm-kpi__bar--purple"></div>
                        </div>

                    </div>

                    {{-- ── Two-col: Owner Wallets + Pending Withdrawals ── --}}
                    <div class="cm-two-col">

                        {{-- Owner wallet balances --}}
                        <div class="cm-panel">
                            <div class="cm-panel__head">
                                <div class="cm-panel__icon cm-panel__icon--blue">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><rect x="1" y="4" width="22" height="16" rx="2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><line x1="1" y1="10" x2="23" y2="10" stroke="currentColor" stroke-width="1.8"/></svg>
                                </div>
                                <h4 class="cm-panel__title">{{ __('Owner Wallet Balances') }}</h4>
                            </div>
                            <div class="table-responsive">
                                <table class="cm-table">
                                    <thead>
                                        <tr>
                                            <th>{{ __('Owner') }}</th>
                                            <th>{{ __('Balance') }}</th>
                                            <th>{{ __('Earned') }}</th>
                                            <th>{{ __('Commission') }}</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($wallets as $wallet)
                                        <tr>
                                            <td>
                                                <div class="cm-owner-cell">
                                                    <div class="cm-avatar">{{ strtoupper(substr($wallet->user->name ?? 'O', 0, 1)) }}</div>
                                                    <div>
                                                        <div class="cm-owner-name">{{ $wallet->user->name ?? '—' }}</div>
                                                        <div class="cm-owner-email">{{ $wallet->user->email ?? '' }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><span class="cm-badge cm-badge--blue">KSh {{ number_format($wallet->balance, 2) }}</span></td>
                                            <td class="cm-muted">KSh {{ number_format($wallet->transactions()->where('type','credit')->sum('net_amount'), 2) }}</td>
                                            <td class="cm-muted">KSh {{ number_format($wallet->transactions()->sum('commission_amount'), 2) }}</td>
                                            <td>
                                                <a href="{{ route('admin.wallet.owner', $wallet->id) }}" class="cm-link">
                                                    {{ __('View') }}
                                                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none"><path d="M5 12h14M12 5l7 7-7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                                </a>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="5" class="cm-empty">
                                                <div class="cm-empty__icon"><svg width="28" height="28" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.5"/></svg></div>
                                                <p>{{ __('No wallets found.') }}</p>
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Pending withdrawals --}}
                        <div class="cm-panel">
                            <div class="cm-panel__head">
                                <div class="cm-panel__icon cm-panel__icon--amber">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.8"/><polyline points="12 6 12 12 16 14" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                                </div>
                                <h4 class="cm-panel__title">{{ __('Pending Withdrawals') }}</h4>
                                @if($pendingWithdrawals->count())
                                <span class="cm-count-badge">{{ $pendingWithdrawals->count() }}</span>
                                @endif
                            </div>
                            <div class="table-responsive">
                                <table class="cm-table">
                                    <thead>
                                        <tr>
                                            <th>{{ __('Owner') }}</th>
                                            <th>{{ __('Amount') }}</th>
                                            <th>{{ __('Phone') }}</th>
                                            <th>{{ __('Requested') }}</th>
                                            <th>{{ __('Actions') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($pendingWithdrawals as $wd)
                                        <tr>
                                            <td>
                                                <div class="cm-owner-cell">
                                                    <div class="cm-avatar cm-avatar--amber">{{ strtoupper(substr($wd->wallet->user->name ?? 'O', 0, 1)) }}</div>
                                                    <span class="cm-owner-name">{{ $wd->wallet->user->name ?? '—' }}</span>
                                                </div>
                                            </td>
                                            <td><span class="cm-badge cm-badge--amber">KSh {{ number_format($wd->amount, 2) }}</span></td>
                                            <td class="cm-muted">{{ $wd->phone }}</td>
                                            <td class="cm-date">{{ $wd->created_at->diffForHumans() }}</td>
                                            <td>
                                                <div class="cm-action-group">
                                                    <button class="cm-btn cm-btn--approve"
                                                            onclick="triggerApprove({{ $wd->id }}, '{{ $wd->wallet->user->name ?? 'Owner' }}', '{{ number_format($wd->amount, 2) }}', '{{ $wd->phone }}')">
                                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none"><polyline points="20 6 9 17 4 12" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                                        {{ __('Approve') }}
                                                    </button>
                                                    <button class="cm-btn cm-btn--reject" onclick="rejectWithdrawal({{ $wd->id }})">
                                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none"><line x1="18" y1="6" x2="6" y2="18" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/><line x1="6" y1="6" x2="18" y2="18" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/></svg>
                                                        {{ __('Reject') }}
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="5" class="cm-empty">
                                                <p>{{ __('No pending withdrawals.') }}</p>
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>

                    {{-- ── All Transactions ── --}}
                    <div class="cm-panel">
                        <div class="cm-panel__head">
                            <div class="cm-panel__icon cm-panel__icon--green">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </div>
                            <h4 class="cm-panel__title">{{ __('All Transactions') }}</h4>

                            {{-- Filters --}}
                            <div class="cm-filters">
                                <div class="cm-filter">
                                    <label class="cm-filter__label">{{ __('Type') }}</label>
                                    <div class="cm-filter__wrap">
                                        <select id="adminTypeFilter" class="cm-filter__select">
                                            <option value="">{{ __('All Types') }}</option>
                                            <option value="credit">{{ __('Credit') }}</option>
                                            <option value="debit">{{ __('Debit') }}</option>
                                            <option value="withdrawal">{{ __('Withdrawal') }}</option>
                                            <option value="refund">{{ __('Refund') }}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="cm-filter">
                                    <label class="cm-filter__label">{{ __('Source') }}</label>
                                    <div class="cm-filter__wrap">
                                        <select id="adminSourceFilter" class="cm-filter__select">
                                            <option value="">{{ __('All Sources') }}</option>
                                            <option value="marketplace">{{ __('Marketplace') }}</option>
                                            <option value="rent">{{ __('Rent') }}</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="cm-table" id="adminTxnTable">
                                <thead>
                                    <tr>
                                        <th>{{ __('Date') }}</th>
                                        <th>{{ __('Owner') }}</th>
                                        <th>{{ __('Description') }}</th>
                                        <th>{{ __('Source') }}</th>
                                        <th>{{ __('Gross') }}</th>
                                        <th>{{ __('Rate') }}</th>
                                        <th>{{ __('Commission') }}</th>
                                        <th>{{ __('Net to Owner') }}</th>
                                        <th>{{ __('Type') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($allTransactions as $txn)
                                    <tr data-type="{{ $txn->type }}" data-source="{{ $txn->transaction_source }}">
                                        <td class="cm-date">{{ $txn->created_at->format('d M Y, H:i') }}</td>
                                        <td><span class="cm-owner-name">{{ $txn->wallet->user->name ?? '—' }}</span></td>
                                        <td class="cm-desc">{{ $txn->description }}</td>
                                        <td>
                                            @if($txn->transaction_source === 'rent')
                                                <span class="cm-source-badge cm-source-badge--rent">
                                                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                                    {{ __('Rent') }}
                                                </span>
                                            @else
                                                <span class="cm-source-badge cm-source-badge--marketplace">
                                                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4zM3 6h18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                                    {{ __('Marketplace') }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="cm-num">
                                            @if($txn->gross_amount) KSh {{ number_format($txn->gross_amount, 2) }}
                                            @else <span class="cm-na">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($txn->commission_rate)
                                                <span class="cm-rate-pill">{{ number_format($txn->commission_rate, 1) }}%</span>
                                            @else <span class="cm-na">—</span>
                                            @endif
                                        </td>
                                        <td class="cm-num cm-num--commission">
                                            @if($txn->commission_amount) KSh {{ number_format($txn->commission_amount, 2) }}
                                            @else <span class="cm-na">—</span>
                                            @endif
                                        </td>
                                        <td class="cm-num cm-num--net">KSh {{ number_format($txn->net_amount, 2) }}</td>
                                        <td>
                                            <span class="cm-txn-badge cm-txn-badge--{{ $txn->type }}">
                                                <span class="cm-txn-badge__dot"></span>
                                                {{ ucfirst($txn->type) }}
                                            </span>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="9" class="cm-empty"><p>{{ __('No transactions found.') }}</p></td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @if($allTransactions->hasPages())
                        <div class="cm-pagination">{{ $allTransactions->links() }}</div>
                        @endif
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── Approve Withdrawal Confirmation Modal ── --}}
<div id="approveModal" class="apv-overlay" data-state="closed" aria-modal="true" role="dialog">
    <div class="apv-box">
        <div class="apv-box__head">
            <div class="apv-box__icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none"><path d="M12 5v14M5 12l7 7 7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </div>
            <div>
                <p class="apv-box__eyebrow">{{ __('M-Pesa B2C Payout') }}</p>
                <h5 class="apv-box__title">{{ __('Approve Withdrawal') }}</h5>
            </div>
            <button type="button" class="apv-box__close" id="closeApproveModal">&times;</button>
        </div>
        <div class="apv-box__body">
            <div class="apv-summary">
                <div class="apv-summary__row">
                    <span class="apv-summary__label">{{ __('Owner') }}</span>
                    <span class="apv-summary__value" id="apvOwnerName">—</span>
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
            <div class="apv-warning">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" style="flex-shrink:0;margin-top:1px"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><line x1="12" y1="9" x2="12" y2="13" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><line x1="12" y1="17" x2="12.01" y2="17" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                <span>{{ __('This will trigger an M-Pesa B2C transfer. This action cannot be reversed once sent.') }}</span>
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

@endsection

@push('style')
<style>
:root {
    --blue:#185FA5;--blue-hover:#0F4A84;--blue-light:#E6F1FB;--blue-border:#B5D4F4;--blue-faint:#185ea56e;
    --green:#1D9E75;--green-dark:#0F6E56;--green-light:#E1F5EE;
    --amber:#854F0B;--amber-light:#FAEEDA;--amber-border:#F5D9A8;
    --red:#993C1D;--red-light:#FAECE7;--purple:#534AB7;
    --gray-900:#111827;--gray-700:#374151;--gray-500:#6b7280;--gray-400:#9ca3af;
    --gray-200:#e5e7eb;--gray-100:#f3f4f6;--gray-50:#fafafa;--white:#ffffff;
}
.cm-header{display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:1rem;margin-bottom:24px}
.cm-breadcrumb{display:flex;align-items:center;gap:5px;list-style:none;padding:0;margin:0 0 8px;font-size:12px;color:var(--gray-400)}
.cm-breadcrumb li:not(:last-child)::after{content:'';display:inline-block;width:5px;height:5px;border-right:1.5px solid #d1d5db;border-top:1.5px solid #d1d5db;transform:rotate(45deg);margin-left:5px;opacity:.5}
.cm-breadcrumb a{display:inline-flex;align-items:center;gap:4px;color:var(--blue);text-decoration:none;font-weight:500}
.cm-page-title{font-size:22px;font-weight:500;color:var(--gray-900);margin:0 0 4px}
.cm-page-sub{font-size:13px;color:var(--gray-500);margin:0}
.cm-kpi-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:20px}
.cm-kpi{background:var(--white);border:0.5px solid var(--blue-faint);border-radius:14px;padding:20px;display:flex;flex-direction:column;gap:10px;position:relative;overflow:hidden;transition:all .25s ease;box-shadow:0 4px 12px rgba(0,0,0,.04),0 0 0 1px rgba(24,95,165,.05),0 6px 18px rgba(24,95,165,.06)}
.cm-kpi:hover{border-color:var(--blue);transform:translateY(-3px);box-shadow:0 10px 25px rgba(0,0,0,.06),0 0 0 1px rgba(24,95,165,.12),0 12px 30px rgba(24,95,165,.18)}
.cm-kpi__top{display:flex;align-items:center;gap:10px}
.cm-kpi__icon{width:40px;height:40px;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.cm-kpi__icon--blue{background:var(--blue-light);color:var(--blue)}
.cm-kpi__icon--amber{background:var(--amber-light);color:var(--amber)}
.cm-kpi__icon--green{background:var(--green-light);color:var(--green)}
.cm-kpi__icon--purple{background:#EEEDF9;color:var(--purple)}
.cm-kpi__label{font-size:11px;font-weight:500;text-transform:uppercase;letter-spacing:.07em;color:var(--gray-400);margin:0}
.cm-kpi__value{font-size:22px;font-weight:700;color:var(--gray-900);margin:0;line-height:1}
.cm-kpi__sub{font-size:11px;color:var(--gray-400);margin:0}
.cm-kpi__bar{position:absolute;bottom:0;left:0;right:0;height:3px}
.cm-kpi__bar--blue{background:var(--blue)}.cm-kpi__bar--amber{background:#F59E0B}.cm-kpi__bar--green{background:var(--green)}.cm-kpi__bar--purple{background:var(--purple)}
.cm-two-col{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px}
.cm-panel{background:var(--white);border:0.5px solid var(--blue-faint);border-radius:12px;overflow:hidden;margin-bottom:20px;box-shadow:0 4px 12px rgba(0,0,0,.04),0 0 0 1px rgba(24,95,165,.05),0 6px 18px rgba(24,95,165,.06)}
.cm-panel:last-child{margin-bottom:0}.cm-two-col .cm-panel{margin-bottom:0}
.cm-panel__head{display:flex;align-items:center;gap:10px;padding:16px 20px;border-bottom:0.5px solid var(--gray-200);background:var(--gray-50);flex-wrap:wrap}
.cm-panel__icon{width:34px;height:34px;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.cm-panel__icon--blue{background:var(--blue-light);color:var(--blue)}
.cm-panel__icon--green{background:var(--green-light);color:var(--green)}
.cm-panel__icon--amber{background:var(--amber-light);color:var(--amber)}
.cm-panel__title{font-size:14px;font-weight:600;color:var(--gray-900);margin:0}
.cm-count-badge{display:inline-flex;align-items:center;justify-content:center;min-width:20px;height:20px;background:var(--red);color:var(--white);font-size:11px;font-weight:700;border-radius:20px;padding:0 5px}

/* Multiple filters side by side */
.cm-filters{display:flex;align-items:center;gap:10px;margin-left:auto}
.cm-filter{display:flex;align-items:center;gap:8px}
.cm-filter__label{font-size:10px;font-weight:500;text-transform:uppercase;letter-spacing:.07em;color:var(--gray-400)}
.cm-filter__wrap{display:flex;align-items:center;gap:6px;border:0.5px solid var(--gray-200);border-radius:7px;padding:5px 10px;background:var(--white);color:var(--gray-500)}
.cm-filter__select{border:none;outline:none;background:transparent;font-size:12px;color:var(--gray-700);cursor:pointer;padding:0}
.cm-filter__wrap:focus-within{border-color:var(--blue);box-shadow:0 0 0 3px rgba(24,95,165,.08)}

.cm-table{width:100%;border-collapse:collapse}
.cm-table thead th{padding:.65rem 1rem;font-size:10px;font-weight:500;text-transform:uppercase;letter-spacing:.07em;color:var(--gray-500);background:var(--gray-50);border-bottom:0.5px solid var(--gray-200);text-align:left;white-space:nowrap}
.cm-table tbody td{padding:.8rem 1rem;font-size:13px;color:var(--gray-700);border-bottom:0.5px solid var(--gray-100);vertical-align:middle}
.cm-table tbody tr:last-child td{border-bottom:none}
.cm-table tbody tr:nth-child(even) td{background:var(--gray-50)}
.cm-table tbody tr:hover td{background:var(--gray-100)}
.cm-muted{color:var(--gray-500);font-size:12px}.cm-date{color:var(--gray-500);white-space:nowrap;font-size:12px}
.cm-desc{max-width:180px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.cm-num{font-variant-numeric:tabular-nums;white-space:nowrap}
.cm-num--net{font-weight:600;color:var(--green-dark)}.cm-num--commission{color:var(--amber)}.cm-na{color:var(--gray-200)}
.cm-empty{text-align:center;padding:2.5rem 1rem !important;color:var(--gray-400)}
.cm-empty__icon{display:flex;justify-content:center;margin-bottom:8px;color:var(--gray-200)}.cm-empty p{font-size:13px;margin:0}
.cm-pagination{padding:14px 20px;border-top:0.5px solid var(--gray-200);background:var(--gray-50);display:flex;justify-content:flex-end}
.cm-owner-cell{display:flex;align-items:center;gap:9px}
.cm-avatar{width:30px;height:30px;border-radius:8px;background:var(--blue);color:var(--white);display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;flex-shrink:0;border:2px solid var(--blue-light)}
.cm-avatar--amber{background:#B45309;border-color:var(--amber-light)}
.cm-owner-name{font-size:13px;font-weight:500;color:var(--gray-900)}.cm-owner-email{font-size:11px;color:var(--gray-400)}
.cm-badge{display:inline-flex;padding:3px 10px;border-radius:99px;font-size:12px;font-weight:700;border:0.5px solid transparent}
.cm-badge--blue{background:var(--blue-light);color:var(--blue);border-color:var(--blue-border)}
.cm-badge--amber{background:var(--amber-light);color:var(--amber);border-color:var(--amber-border)}
.cm-rate-pill{display:inline-flex;align-items:center;padding:2px 7px;border-radius:99px;background:var(--amber-light);color:var(--amber);border:0.5px solid var(--amber-border);font-size:10px;font-weight:500}
.cm-txn-badge{display:inline-flex;align-items:center;gap:5px;padding:3px 9px;border-radius:99px;font-size:11px;font-weight:500;border:0.5px solid transparent}
.cm-txn-badge__dot{width:6px;height:6px;border-radius:50%;background:currentColor;opacity:.7}
.cm-txn-badge--credit{background:var(--green-light);color:var(--green-dark)}
.cm-txn-badge--debit{background:var(--red-light);color:var(--red)}
.cm-txn-badge--withdrawal{background:#EEEDF9;color:var(--purple)}
.cm-txn-badge--refund{background:var(--amber-light);color:var(--amber);border-color:var(--amber-border)}

/* ── Source badges ── */
.cm-source-badge{display:inline-flex;align-items:center;gap:5px;padding:2px 8px;border-radius:99px;font-size:10px;font-weight:600;white-space:nowrap}
.cm-source-badge--rent{background:#EDE9FE;color:#5B21B6;border:0.5px solid #C4B5FD}
.cm-source-badge--marketplace{background:var(--blue-light);color:var(--blue);border:0.5px solid var(--blue-border)}

.cm-action-group{display:flex;gap:6px}
.cm-btn{display:inline-flex;align-items:center;gap:5px;padding:5px 10px;border-radius:7px;font-size:11px;font-weight:500;border:0.5px solid transparent;cursor:pointer;transition:all .13s}
.cm-btn--approve{background:var(--green-light);color:var(--green-dark);border-color:#A7DFC9}
.cm-btn--approve:hover{background:var(--green);color:var(--white);border-color:var(--green)}
.cm-btn--reject{background:var(--red-light);color:var(--red);border-color:#f5c4b8}
.cm-btn--reject:hover{background:var(--red);color:var(--white);border-color:var(--red)}
.cm-link{display:inline-flex;align-items:center;gap:4px;font-size:12px;font-weight:500;color:var(--blue);text-decoration:none;transition:gap .13s}
.cm-link:hover{gap:7px;color:var(--blue-hover)}

/* Approve modal */
.apv-overlay{position:fixed !important;top:0 !important;left:0 !important;right:0 !important;bottom:0 !important;width:100vw !important;height:100vh !important;background:rgba(17,24,39,.45) !important;backdrop-filter:blur(2px) !important;z-index:99999 !important;display:flex !important;align-items:center !important;justify-content:center !important;visibility:visible !important;opacity:1 !important}
.apv-overlay[data-state="closed"]{display:none !important;visibility:hidden !important;pointer-events:none !important;opacity:0 !important}
.apv-box{background:var(--white) !important;border-radius:14px !important;width:100% !important;max-width:420px !important;box-shadow:0 20px 50px rgba(0,0,0,.18) !important;overflow:hidden !important;position:relative !important;z-index:100000 !important}
.apv-box__head{display:flex;align-items:center;gap:12px;padding:20px 20px 12px;border-bottom:0.5px solid var(--gray-200);background:var(--gray-50)}
.apv-box__icon{width:40px;height:40px;border-radius:10px;background:var(--green-light);color:var(--green);display:flex;align-items:center;justify-content:center;flex-shrink:0}
.apv-box__eyebrow{font-size:10px;font-weight:500;text-transform:uppercase;letter-spacing:.07em;color:var(--gray-400);margin:0 0 2px}
.apv-box__title{font-size:15px;font-weight:600;color:var(--gray-900);margin:0}
.apv-box__close{margin-left:auto;background:var(--gray-100);border:0.5px solid var(--gray-200);font-size:18px;color:var(--gray-500);cursor:pointer;width:30px;height:30px;border-radius:7px;display:flex;align-items:center;justify-content:center;line-height:1;transition:all .13s}
.apv-box__close:hover{background:var(--gray-200);color:var(--gray-900)}
.apv-box__body{padding:20px}
.apv-summary{background:var(--gray-50);border:0.5px solid var(--gray-200);border-radius:10px;overflow:hidden;margin-bottom:16px}
.apv-summary__row{display:flex;align-items:center;justify-content:space-between;padding:10px 14px;border-bottom:0.5px solid var(--gray-200)}
.apv-summary__row:last-child{border-bottom:none}
.apv-summary__row--highlight{background:var(--blue-light)}
.apv-summary__label{font-size:11px;font-weight:500;text-transform:uppercase;letter-spacing:.07em;color:var(--gray-400)}
.apv-summary__value{font-size:13px;font-weight:500;color:var(--gray-900)}
.apv-summary__amount{font-size:16px;font-weight:700;color:var(--blue)}
.apv-warning{display:flex;align-items:flex-start;gap:8px;background:var(--amber-light);border:0.5px solid var(--amber-border);border-radius:8px;padding:10px 12px;font-size:12px;color:var(--amber);margin-bottom:20px}
.apv-box__footer{display:flex;gap:8px}
.apv-btn{flex:1;display:inline-flex;align-items:center;justify-content:center;gap:7px;padding:10px 16px;border-radius:7px;font-size:13px;font-weight:500;border:0.5px solid transparent;cursor:pointer;transition:all .13s}
.apv-btn--cancel{background:var(--gray-100);color:var(--gray-700);border-color:var(--gray-200)}
.apv-btn--cancel:hover{background:var(--gray-200)}
.apv-btn--confirm{background:var(--green);color:var(--white);border-color:var(--green)}
.apv-btn--confirm:hover{background:var(--green-dark);transform:translateY(-1px)}
.apv-btn--confirm:disabled{background:#a7dfc9;cursor:not-allowed;transform:none}
@media(max-width:1200px){.cm-kpi-grid{grid-template-columns:repeat(2,1fr)}}
@media(max-width:860px){.cm-two-col{grid-template-columns:1fr}}
@media(max-width:600px){.cm-kpi-grid{grid-template-columns:1fr}}
</style>
@endpush

@push('script')
<script>
(function () {
    'use strict';

    /* ── Type + Source filters ───────────────────────────────── */
    function applyFilters() {
        var typeVal   = document.getElementById('adminTypeFilter').value;
        var sourceVal = document.getElementById('adminSourceFilter').value;
        document.querySelectorAll('#adminTxnTable tbody tr[data-type]').forEach(function (row) {
            var typeMatch   = !typeVal   || row.dataset.type   === typeVal;
            var sourceMatch = !sourceVal || row.dataset.source === sourceVal;
            row.style.display = (typeMatch && sourceMatch) ? '' : 'none';
        });
    }
    document.getElementById('adminTypeFilter').addEventListener('change', applyFilters);
    document.getElementById('adminSourceFilter').addEventListener('change', applyFilters);

    /* ── Approve modal ───────────────────────────────────────── */
    var approveModal = document.getElementById('approveModal');
    var confirmBtn   = document.getElementById('confirmApproveBtn');
    var pendingUrl   = null;

    function openApprove()  { approveModal.removeAttribute('data-state'); approveModal.style.setProperty('display','flex','important'); }
    function closeApprove() {
        approveModal.setAttribute('data-state','closed');
        approveModal.style.setProperty('display','none','important');
        confirmBtn.disabled = false;
        confirmBtn.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none"><polyline points="20 6 9 17 4 12" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg> {{ __("Confirm & Send") }}';
        pendingUrl = null;
    }
    closeApprove();

    window.triggerApprove = function (id, name, amount, phone) {
        document.getElementById('apvOwnerName').textContent = name;
        document.getElementById('apvPhone').textContent     = phone;
        document.getElementById('apvAmount').textContent    = 'KSh ' + amount;
        pendingUrl = '{{ route("admin.wallet.withdrawal.approve", ":id") }}'.replace(':id', id);
        openApprove();
    };

    confirmBtn.addEventListener('click', function () {
        if (!pendingUrl) return;
        confirmBtn.disabled    = true;
        confirmBtn.textContent = '{{ __("Processing…") }}';
        doAction(pendingUrl);
    });

    document.getElementById('closeApproveModal').addEventListener('click', closeApprove);
    document.getElementById('cancelApproveModal').addEventListener('click', closeApprove);
    approveModal.addEventListener('click', function (e) { if (e.target === approveModal) closeApprove(); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeApprove(); });

    window.rejectWithdrawal = function (id) {
        if (!confirm('{{ __("Reject this withdrawal? The amount will be returned to the owner\'s wallet.") }}')) return;
        doAction('{{ route("admin.wallet.withdrawal.reject", ":id") }}'.replace(':id', id));
    };

    function doAction(url) {
        var fd = new FormData();
        fd.append('_token', '{{ csrf_token() }}');
        fetch(url, { method: 'POST', body: fd })
            .then(function (r) { return r.json(); })
            .then(function (d) {
                if (d.success) { closeApprove(); toastr.success(d.message || '{{ __("Done.") }}'); setTimeout(function () { location.reload(); }, 1500); }
                else { toastr.error(d.error || '{{ __("Failed.") }}'); confirmBtn.disabled = false; }
            })
            .catch(function () { toastr.error('{{ __("Something went wrong.") }}'); confirmBtn.disabled = false; });
    }
})();
</script>
@endpush