@extends('admin.layouts.app')

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="page-content-wrapper bg-white p-30 radius-20">
                <div class="container">

<style>
    /* ── Centresidence Design Tokens ── */
    :root {
        --blue:          #185FA5;
        --blue-hover:    #0F4A84;
        --blue-light:    #E6F1FB;
        --blue-border:   #B5D4F4;
        --blue-faint:    #185ea56e;
        --blue-ghost:    #185ea51c;
        --green:         #1D9E75;
        --green-dark:    #0F6E56;
        --green-light:   #E1F5EE;
        --amber:         #854F0B;
        --amber-light:   #FAEEDA;
        --amber-border:  #F5D9A8;
        --red:           #993C1D;
        --red-light:     #FAECE7;
        --purple:        #534AB7;
        --purple-hover:  #3C3489;
        --gray-900:      #111827;
        --gray-800:      #1f2937;
        --gray-700:      #374151;
        --gray-500:      #6b7280;
        --gray-400:      #9ca3af;
        --gray-200:      #e5e7eb;
        --gray-100:      #f3f4f6;
        --gray-50:       #fafafa;
        --white:         #ffffff;
    }

    /* ── Page Shell ── */
    .dash-page { padding: 4px 0 0; }

    /* ── Page Header ── */
    .dash-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        margin-bottom: 24px;
        flex-wrap: wrap;
        gap: 12px;
    }
    .dash-header__title {
        font-size: 22px;
        font-weight: 500;
        color: var(--gray-900);
        margin: 0 0 4px;
        line-height: 1.2;
    }
    .dash-header__sub {
        font-size: 13px;
        color: var(--gray-500);
        margin: 0;
    }

    /* ── Alert Banners ── */
    .dash-alerts {
        display: flex;
        flex-direction: column;
        gap: 10px;
        margin-bottom: 22px;
    }
    .dash-alert {
        display: flex;
        align-items: center;
        gap: 12px;
        border-radius: 10px;
        padding: 12px 16px;
        animation: slideDown .3s ease;
        border: 0.5px solid;
    }
    @keyframes slideDown {
        from { opacity:0; transform:translateY(-8px); }
        to   { opacity:1; transform:translateY(0);    }
    }
    .dash-alert--owner {
        background: var(--amber-light);
        border-color: var(--amber-border);
    }
    .dash-alert--affiliate {
        background: #F0EFFF;
        border-color: #C4B5FD;
    }
    .dash-alert__icon {
        width: 32px; height: 32px;
        border-radius: 8px;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .dash-alert__icon svg { width: 16px; height: 16px; stroke-width: 2; fill: none; stroke-linecap: round; stroke-linejoin: round; }
    .dash-alert--owner .dash-alert__icon { background: #f5d9a8; }
    .dash-alert--owner .dash-alert__icon svg { stroke: var(--amber); }
    .dash-alert--affiliate .dash-alert__icon { background: #DDD6FE; }
    .dash-alert--affiliate .dash-alert__icon svg { stroke: var(--purple); }
    .dash-alert__body { flex: 1; min-width: 0; }
    .dash-alert__title { font-size: 13px; font-weight: 600; margin: 0 0 2px; }
    .dash-alert--owner .dash-alert__title { color: var(--amber); }
    .dash-alert--affiliate .dash-alert__title { color: var(--purple); }
    .dash-alert__desc { font-size: 12px; margin: 0; }
    .dash-alert--owner .dash-alert__desc { color: #a0620d; }
    .dash-alert--affiliate .dash-alert__desc { color: #5B3E9E; }
    .dash-alert__link {
        display: inline-flex; align-items: center; gap: 5px;
        font-size: 12px; font-weight: 500;
        border-radius: 7px; padding: 6px 12px;
        text-decoration: none; white-space: nowrap;
        transition: all .15s;
        border: 0.5px solid;
        flex-shrink: 0;
    }
    .dash-alert__link svg { width: 11px; height: 11px; stroke: currentColor; stroke-width: 2; fill: none; stroke-linecap: round; stroke-linejoin: round; }
    .dash-alert--owner .dash-alert__link { color: var(--amber); background: #f5d9a8; border-color: var(--amber-border); }
    .dash-alert--owner .dash-alert__link:hover { background: #edc98c; }
    .dash-alert--affiliate .dash-alert__link { color: var(--white); background: var(--purple); border-color: var(--purple); }
    .dash-alert--affiliate .dash-alert__link:hover { background: var(--purple-hover); }

    /* ── Alert count badge ── */
    .dash-alert__count {
        display: inline-flex; align-items: center; justify-content: center;
        min-width: 20px; height: 20px;
        background: var(--white);
        color: var(--purple);
        font-size: 11px; font-weight: 700;
        border-radius: 99px;
        padding: 0 6px;
        margin-left: 2px;
    }

    /* ── Stat Cards ── */
    .dash-stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 22px; }
    @media (max-width: 1100px) { .dash-stats { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 540px)  { .dash-stats { grid-template-columns: 1fr; } }

    .dash-stat {
        background: var(--white);
        border: 0.5px solid var(--blue-faint);
        border-radius: 14px;
        padding: 20px;
        display: flex;
        flex-direction: column;
        gap: 14px;
        transition: all .25s ease;
        box-shadow:
            0 4px 12px rgba(0,0,0,.04),
            0 0 0 1px rgba(24,95,165,.05),
            0 6px 18px rgba(24,95,165,.06);
        position: relative;
        overflow: hidden;
    }
    .dash-stat:hover {
        border-color: var(--blue);
        transform: translateY(-3px);
        box-shadow:
            0 10px 25px rgba(0,0,0,.06),
            0 0 0 1px rgba(24,95,165,.12),
            0 12px 30px rgba(24,95,165,.18);
    }
    .dash-stat__top { display: flex; align-items: center; justify-content: space-between; }
    .dash-stat__icon {
        width: 40px; height: 40px;
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
    }
    .dash-stat__icon svg { width: 20px; height: 20px; }
    .dash-stat__icon--blue   { background: var(--blue-light);  }
    .dash-stat__icon--blue svg   { stroke: var(--blue);  fill: none; stroke-width: 1.8; stroke-linecap: round; stroke-linejoin: round; }
    .dash-stat__icon--green  { background: var(--green-light); }
    .dash-stat__icon--green svg  { stroke: var(--green); fill: none; stroke-width: 1.8; stroke-linecap: round; stroke-linejoin: round; }
    .dash-stat__icon--amber  { background: var(--amber-light); }
    .dash-stat__icon--amber svg  { stroke: var(--amber); fill: none; stroke-width: 1.8; stroke-linecap: round; stroke-linejoin: round; }
    .dash-stat__icon--purple { background: #EEEDF9; }
    .dash-stat__icon--purple svg { stroke: var(--purple); fill: none; stroke-width: 1.8; stroke-linecap: round; stroke-linejoin: round; }
    .dash-stat__icon--teal { background: #D5F0EC; }
    .dash-stat__icon--teal svg { stroke: #0F766E; fill: none; stroke-width: 1.8; stroke-linecap: round; stroke-linejoin: round; }
    .dash-stat__icon--indigo { background: #E5E9FB; }
    .dash-stat__icon--indigo svg { stroke: #3B4CB8; fill: none; stroke-width: 1.8; stroke-linecap: round; stroke-linejoin: round; }

    /* ── Growth & uptake ── */
    .dash-growth { display: grid; grid-template-columns: 1.4fr 1fr; gap: 16px; margin-bottom: 22px; align-items: start; }
    @media (max-width: 900px) { .dash-growth { grid-template-columns: 1fr; } }
    .grow-body { padding: 18px 20px 20px; }
    .grow-delta { font-size: 12px; font-weight: 600; }
    .grow-delta.is-up { color: var(--green, #0F766E); }
    .grow-delta.is-down { color: #B42318; }
    .grow-head { display: flex; align-items: baseline; gap: 8px; margin: 6px 0 16px; }
    .grow-big { font-size: 30px; font-weight: 800; color: var(--gray-900); line-height: 1; }
    .grow-cap { font-size: 12px; color: var(--gray-400); text-transform: uppercase; letter-spacing: .05em; }
    .grow-bars { display: flex; align-items: flex-end; gap: 10px; height: 108px; }
    .grow-bar { flex: 1; display: flex; flex-direction: column; align-items: center; gap: 4px; }
    .grow-bar__track { width: 100%; flex: 1; display: flex; align-items: flex-end; background: var(--gray-100, #f1f2f4); border-radius: 6px; overflow: hidden; }
    .grow-bar__fill { width: 100%; background: linear-gradient(180deg, #2b7fe0, #185FA5); border-radius: 6px 6px 0 0; transition: height .4s ease; }
    .grow-bar__num { font-size: 11.5px; font-weight: 700; color: var(--gray-700, #3A3F47); font-variant-numeric: tabular-nums; }
    .grow-bar__lbl { font-size: 10.5px; color: var(--gray-400); text-transform: uppercase; letter-spacing: .03em; }
    .grow-roles { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 16px; padding-top: 14px; border-top: 0.5px solid var(--gray-100, #eee); }
    .grow-role { display: flex; align-items: center; gap: 6px; font-size: 12px; color: var(--gray-500); background: var(--gray-50, #f7f8fa); border: 0.5px solid var(--gray-100, #eee); border-radius: 999px; padding: 4px 11px; }
    .grow-role b { color: var(--gray-900); font-variant-numeric: tabular-nums; }
    .grow-plans { display: flex; flex-direction: column; gap: 13px; }
    .grow-plan__top { display: flex; justify-content: space-between; font-size: 13px; color: var(--gray-700, #3A3F47); margin-bottom: 5px; }
    .grow-plan__top b { color: var(--gray-900); font-variant-numeric: tabular-nums; }
    .grow-plan__track { height: 7px; background: var(--gray-100, #f1f2f4); border-radius: 999px; overflow: hidden; }
    .grow-plan__fill { height: 100%; background: linear-gradient(90deg, #1D9E75, #0F6E56); border-radius: 999px; transition: width .4s ease; }
    .grow-empty { font-size: 13px; color: var(--gray-400); padding: 14px 0; }

    .dash-stat__badge {
        font-size: 10px; font-weight: 500;
        text-transform: uppercase; letter-spacing: .07em;
        color: var(--gray-400);
        background: var(--gray-100);
        border: 0.5px solid var(--gray-200);
        border-radius: 99px;
        padding: 2px 8px;
    }
    .dash-stat__value {
        font-size: 32px; font-weight: 700; color: var(--gray-900);
        line-height: 1; margin: 0;
    }
    .dash-stat__label {
        font-size: 12.5px; font-weight: 500; color: var(--gray-500);
        margin: 0;
    }

    /* ── Cleanup Button ── */
    .btn-cleanup {
        display: inline-flex; align-items: center; gap: 6px;
        font-size: 12px; font-weight: 500;
        padding: 7px 15px; border-radius: 7px;
        border: 0.5px solid var(--gray-200);
        background: var(--gray-100); color: var(--gray-700);
        cursor: pointer; transition: all .13s;
        text-decoration: none;
    }
    .btn-cleanup svg { width: 13px; height: 13px; stroke: currentColor; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }
    .btn-cleanup:hover { background: var(--gray-200); color: var(--gray-900); transform: translateY(-1px); }

    /* ── Section grid (Orders + Packages) ── */
    .dash-grid { display: grid; grid-template-columns: 1fr 420px; gap: 16px; }
    @media (max-width: 992px) { .dash-grid { grid-template-columns: 1fr; } }

    /* ── Card ── */
    .dash-card {
        background: var(--white);
        border: 0.5px solid var(--blue-faint);
        border-radius: 12px;
        overflow: hidden;
        box-shadow:
            0 4px 12px rgba(0,0,0,.04),
            0 0 0 1px rgba(24,95,165,.05),
            0 6px 18px rgba(24,95,165,.06);
    }
    .dash-card__head {
        display: flex; align-items: center; justify-content: space-between;
        padding: 16px 20px;
        border-bottom: 0.5px solid var(--gray-200);
        background: var(--gray-50);
    }
    .dash-card__head-title {
        font-size: 14px; font-weight: 600; color: var(--gray-900); margin: 0;
    }
    .dash-card__view-all {
        display: inline-flex; align-items: center; gap: 4px;
        font-size: 12px; font-weight: 500; color: var(--blue);
        text-decoration: none; transition: gap .15s;
    }
    .dash-card__view-all svg { width: 12px; height: 12px; stroke: currentColor; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }
    .dash-card__view-all:hover { gap: 7px; color: var(--blue-hover); }

    /* ── Table ── */
    .dash-table { width: 100%; border-collapse: collapse; }
    .dash-table thead th {
        padding: .65rem 1rem;
        font-size: 10px; font-weight: 500;
        text-transform: uppercase; letter-spacing: .07em;
        color: var(--gray-500);
        background: var(--gray-50);
        border-bottom: 0.5px solid var(--gray-200);
        text-align: left; white-space: nowrap;
    }
    .dash-table tbody td {
        padding: .8rem 1rem;
        font-size: 13px; color: var(--gray-700);
        border-bottom: 0.5px solid var(--gray-100);
        vertical-align: middle;
    }
    .dash-table tbody tr:last-child td { border-bottom: none; }
    .dash-table tbody tr:hover td { background: var(--gray-100); }
    .dash-table-empty {
        text-align: center;
        color: var(--gray-400);
        font-size: 13px;
        padding: 32px 0 !important;
    }

    .dash-pkg-name { font-size: 14px; font-weight: 600; color: var(--gray-800); }

    /* ── Badges ── */
    .inv-badge {
        display: inline-flex; align-items: center; gap: 4px;
        font-size: 11px; font-weight: 500;
        padding: 3px 9px; border-radius: 99px; white-space: nowrap;
        border: 0.5px solid transparent;
    }
    .inv-badge--paid    { background: var(--green-light);  color: var(--green-dark); }
    .inv-badge--pending { background: var(--amber-light);  color: var(--amber);      border-color: var(--amber-border); }
    .inv-badge--cancel  { background: var(--gray-100);     color: var(--gray-500);   border-color: var(--gray-200); }

    .inv-badge .dot { width: 6px; height: 6px; border-radius: 50%; background: currentColor; opacity: .7; }

    /* ── View all footer row ── */
    .dash-card__footer {
        padding: 12px 20px;
        border-top: 0.5px solid var(--gray-200);
        background: var(--gray-50);
        text-align: center;
    }

    /* ── Price cell ── */
    .dash-price { font-weight: 600; color: var(--gray-800); }

    /* ── Platform earnings quick-view ── */
    .earn-section { margin-bottom: 22px; }
    .earn-hero {
        display: flex; align-items: center; justify-content: space-between; gap: 20px;
        background: linear-gradient(135deg, #185FA5 0%, #0F4A84 100%);
        border-radius: 16px; padding: 22px 26px; margin-bottom: 16px;
        box-shadow: 0 10px 30px rgba(24,95,165,.22);
    }
    .earn-hero__eyebrow { font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: .08em; color: rgba(255,255,255,.72); margin: 0 0 8px; display: block; }
    .earn-hero__amount { font-size: 34px; font-weight: 700; color: #fff; line-height: 1; }
    .earn-hero__period { font-size: 13px; font-weight: 500; color: rgba(255,255,255,.75); margin-left: 8px; }
    .earn-hero__all { font-size: 13px; color: rgba(255,255,255,.82); margin-top: 9px; }
    .earn-hero__icon { width: 60px; height: 60px; border-radius: 14px; background: rgba(255,255,255,.14); display: flex; align-items: center; justify-content: center; flex: none; }
    .earn-hero__icon svg { width: 30px; height: 30px; stroke: #fff; fill: none; stroke-width: 1.6; stroke-linecap: round; stroke-linejoin: round; }

    .earn-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; }
    @media (max-width: 1100px) { .earn-grid { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 540px)  { .earn-grid { grid-template-columns: 1fr; } }
    .earn-card {
        background: #fff; border: 0.5px solid var(--blue-faint); border-top: 3px solid var(--gray-200);
        border-radius: 14px; padding: 18px;
        box-shadow: 0 4px 12px rgba(0,0,0,.04), 0 6px 18px rgba(24,95,165,.06);
    }
    .earn-card--blue   { border-top-color: var(--blue);   }
    .earn-card--green  { border-top-color: var(--green);  }
    .earn-card--amber  { border-top-color: var(--amber);  }
    .earn-card--violet { border-top-color: var(--purple); }
    .earn-card--teal   { border-top-color: #0F766E;        }
    .earn-card__head { display: flex; align-items: center; gap: 9px; margin-bottom: 14px; }
    .earn-card__ic { width: 32px; height: 32px; border-radius: 9px; display: flex; align-items: center; justify-content: center; flex: none; }
    .earn-card__ic svg { width: 17px; height: 17px; fill: none; stroke-width: 1.8; stroke-linecap: round; stroke-linejoin: round; }
    .earn-card--blue   .earn-card__ic { background: var(--blue-light);  } .earn-card--blue   .earn-card__ic svg { stroke: var(--blue);       }
    .earn-card--green  .earn-card__ic { background: var(--green-light); } .earn-card--green  .earn-card__ic svg { stroke: var(--green-dark); }
    .earn-card--amber  .earn-card__ic { background: var(--amber-light); } .earn-card--amber  .earn-card__ic svg { stroke: var(--amber);      }
    .earn-card--violet .earn-card__ic { background: #EEEDF9;            } .earn-card--violet .earn-card__ic svg { stroke: var(--purple);     }
    .earn-card--teal   .earn-card__ic { background: #D5F0EC;            } .earn-card--teal   .earn-card__ic svg { stroke: #0F766E;           }
    .earn-card__label { font-size: 13px; font-weight: 600; color: var(--gray-800); }
    .earn-card__amt { font-size: 22px; font-weight: 700; color: var(--gray-900); line-height: 1; }
    .earn-card__sub { font-size: 10px; color: var(--gray-400); text-transform: uppercase; letter-spacing: .06em; margin-top: 5px; }
    .earn-card__all { font-size: 12px; color: var(--gray-500); margin-top: 9px; padding-top: 9px; border-top: 0.5px solid var(--gray-100); }
    .earn-card__note { font-size: 11.5px; color: var(--gray-400); margin-top: 6px; line-height: 1.45; }
    .earn-buckets { margin-top: 11px; display: flex; flex-direction: column; gap: 6px; }
    .earn-bucket { display: flex; align-items: center; justify-content: space-between; font-size: 11.5px; color: var(--gray-500); }
    .earn-bucket span:last-child { font-weight: 600; color: var(--gray-700); }
</style>

<div class="dash-page">

    {{-- ── Page Header ── --}}
    <div class="dash-header">
        <div>
            <h1 class="dash-header__title">{{ __('Dashboard') }}</h1>
            <p class="dash-header__sub">{{ __('Welcome back') }}, <strong>{{ auth()->user()->name }}</strong> 👋</p>
        </div>
        <button id="cleanup-btn" class="btn-cleanup">
            <svg viewBox="0 0 24 24"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 .49-3.51"/></svg>
            {{ __('Run Cleanup') }}
        </button>
    </div>

    {{-- ══════════════════════════════════════════════
         ALERT BANNERS
         ══════════════════════════════════════════════ --}}
    @if (($hasPendingOwnerWithdrawals ?? false) || ($affiliatePendingCount ?? 0) > 0)
    <div class="dash-alerts">

        {{-- Owner Withdrawal Alert --}}
        @if (!empty($hasPendingWithdrawals) && $hasPendingWithdrawals)
        <div class="dash-alert dash-alert--owner">
            <div class="dash-alert__icon">
                <svg viewBox="0 0 24 24"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            </div>
            <div class="dash-alert__body">
                <p class="dash-alert__title">{{ __('Pending Owner Withdrawal Requests') }}</p>
                <p class="dash-alert__desc">{{ __('One or more owner withdrawal requests are awaiting your review and approval.') }}</p>
            </div>
            <a href="{{ route('admin.wallet.commissions') }}" class="dash-alert__link">
                {{ __('Review Now') }}
                <svg viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
            </a>
        </div>
        @endif

        {{-- Affiliate Withdrawal Alert --}}
        @if (($affiliatePendingCount ?? 0) > 0)
        <div class="dash-alert dash-alert--affiliate">
            <div class="dash-alert__icon">
                <svg viewBox="0 0 24 24"><path d="M12 5v14M5 12l7 7 7-7"/></svg>
            </div>
            <div class="dash-alert__body">
                <p class="dash-alert__title">
                    {{ __('Pending Affiliate Withdrawals') }}
                    <span class="dash-alert__count">{{ $affiliatePendingCount }}</span>
                </p>
                <p class="dash-alert__desc">
                    {{ __(':count affiliate withdrawal request(s) totaling KSh :amount are awaiting your approval.', [
                        'count' => $affiliatePendingCount,
                        'amount' => number_format($affiliatePendingAmount ?? 0, 2)
                    ]) }}
                </p>
            </div>
            <a href="{{ route('admin.affiliate.withdrawals') }}" class="dash-alert__link">
                {{ __('Review Now') }}
                <svg viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
            </a>
        </div>
        @endif

    </div>
    @endif

    {{-- ══════════════════════════════════════════════
         PLATFORM EARNINGS — quick view across every revenue stream
         ══════════════════════════════════════════════ --}}
    @php $earn = $earnings ?? ['streams' => [], 'total_all' => 0, 'total_month' => 0]; @endphp
    <div class="earn-section">
        <div class="earn-hero">
            <div>
                <span class="earn-hero__eyebrow">{{ __('Platform earnings') }}</span>
                <div class="earn-hero__amount">{{ currencyPrice($earn['total_month']) }}<span class="earn-hero__period">{{ __('this month') }}</span></div>
                <div class="earn-hero__all">{{ currencyPrice($earn['total_all']) }} {{ __('all-time across every revenue stream') }}</div>
            </div>
            <div class="earn-hero__icon">
                <svg viewBox="0 0 24 24"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
            </div>
        </div>
        <div class="earn-grid">
            @foreach ($earn['streams'] as $key => $s)
            <div class="earn-card earn-card--{{ $s['accent'] }}">
                <div class="earn-card__head">
                    <span class="earn-card__ic">
                        @switch($key)
                            @case('subscriptions')<svg viewBox="0 0 24 24"><path d="M12 2l2.4 4.9 5.4.8-3.9 3.8.9 5.4-4.8-2.5-4.8 2.5.9-5.4L4.2 7.7l5.4-.8z"/></svg>@break
                            @case('marketplace')<svg viewBox="0 0 24 24"><path d="M3 9l1-5h16l1 5"/><path d="M4 9v11h16V9"/><path d="M9 20v-6h6v6"/></svg>@break
                            @case('infrastructure')<svg viewBox="0 0 24 24"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>@break
                            @case('financing')<svg viewBox="0 0 24 24"><line x1="3" y1="21" x2="21" y2="21"/><path d="M5 21V10M19 21V10M12 3L3 8h18z"/><line x1="9" y1="21" x2="9" y2="10"/><line x1="15" y1="21" x2="15" y2="10"/></svg>@break
                            @default<svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 7v10M9.5 10h4a2 2 0 0 1 0 4h-4"/></svg>
                        @endswitch
                    </span>
                    <span class="earn-card__label">{{ $s['label'] }}</span>
                </div>
                <div class="earn-card__amt">{{ currencyPrice($s['month']) }}</div>
                <div class="earn-card__sub">{{ __('this month') }}</div>
                <div class="earn-card__all">{{ currencyPrice($s['all']) }} {{ __('all-time') }}</div>
                <div class="earn-card__note">{{ $s['note'] }}</div>
                @if (!empty($s['buckets']))
                <div class="earn-buckets">
                    @foreach ($s['buckets'] as $b)
                    <div class="earn-bucket"><span>{{ $b['label'] }}</span><span>{{ currencyPrice($b['all']) }}</span></div>
                    @endforeach
                </div>
                @endif
            </div>
            @endforeach
        </div>
    </div>

    {{-- ── Stat Cards ── --}}
    <div class="dash-stats">

        <div class="dash-stat">
            <div class="dash-stat__top">
                <div class="dash-stat__icon dash-stat__icon--blue">
                    <svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                </div>
                <span class="dash-stat__badge">{{ __('Owners') }}</span>
            </div>
            <div>
                <p class="dash-stat__value">{{ $totalOwner }}</p>
                <p class="dash-stat__label">{{ __('Total Owners') }}</p>
            </div>
        </div>

        <div class="dash-stat">
            <div class="dash-stat__top">
                <div class="dash-stat__icon dash-stat__icon--purple">
                    <svg viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                </div>
                <span class="dash-stat__badge">{{ __('Properties') }}</span>
            </div>
            <div>
                <p class="dash-stat__value">{{ $totalProperty }}</p>
                <p class="dash-stat__label">{{ __('Total Properties') }}</p>
            </div>
        </div>

        <div class="dash-stat">
            <div class="dash-stat__top">
                <div class="dash-stat__icon dash-stat__icon--amber">
                    <svg viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M9 3v18M3 9h18M3 15h18M15 3v18"/></svg>
                </div>
                <span class="dash-stat__badge">{{ __('Units') }}</span>
            </div>
            <div>
                <p class="dash-stat__value">{{ $totalUnit }}</p>
                <p class="dash-stat__label">{{ __('Total Units') }}</p>
            </div>
        </div>

        <div class="dash-stat">
            <div class="dash-stat__top">
                <div class="dash-stat__icon dash-stat__icon--green">
                    <svg viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                </div>
                <span class="dash-stat__badge">{{ __('Tenants') }}</span>
            </div>
            <div>
                <p class="dash-stat__value">{{ $totalTenant }}</p>
                <p class="dash-stat__label">{{ __('Active Tenants') }}</p>
            </div>
        </div>

        {{-- Free "Tenant Helper" accounts (self-registered, no landlord) --}}
        <div class="dash-stat">
            <div class="dash-stat__top">
                <div class="dash-stat__icon dash-stat__icon--teal">
                    <svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 21v-1a6 6 0 0 1 12 0v1"/><path d="M19 8v6M22 11h-6"/></svg>
                </div>
                <span class="dash-stat__badge">{{ __('Free') }}</span>
            </div>
            <div>
                <p class="dash-stat__value">{{ $freeTenant }}</p>
                <p class="dash-stat__label">{{ __('Free Tenants (Helper)') }}</p>
            </div>
        </div>

        {{-- Affiliates --}}
        <div class="dash-stat">
            <div class="dash-stat__top">
                <div class="dash-stat__icon dash-stat__icon--purple">
                    <svg viewBox="0 0 24 24"><path d="M18 8a3 3 0 1 0-2.83-4M6 8a3 3 0 1 1 2.83-4M12 22v-6M9 13l3 3 3-3M12 2v11"/></svg>
                </div>
                <span class="dash-stat__badge">{{ __('Affiliates') }}</span>
            </div>
            <div>
                <p class="dash-stat__value">{{ $totalAffiliate }}</p>
                <p class="dash-stat__label">{{ __('Total Affiliates') }}</p>
            </div>
        </div>

        {{-- Maintainers --}}
        <div class="dash-stat">
            <div class="dash-stat__top">
                <div class="dash-stat__icon dash-stat__icon--amber">
                    <svg viewBox="0 0 24 24"><path d="M14.7 6.3a4 4 0 0 1-5.4 5.4L4 17l3 3 5.3-5.3a4 4 0 0 0 5.4-5.4l-2.3 2.3-2-2 2.3-2.3z"/></svg>
                </div>
                <span class="dash-stat__badge">{{ __('Maintainers') }}</span>
            </div>
            <div>
                <p class="dash-stat__value">{{ $totalMaintainer }}</p>
                <p class="dash-stat__label">{{ __('Total Maintainers') }}</p>
            </div>
        </div>

        {{-- Finance partners --}}
        <div class="dash-stat">
            <div class="dash-stat__top">
                <div class="dash-stat__icon dash-stat__icon--indigo">
                    <svg viewBox="0 0 24 24"><path d="M3 21h18M4 21V10l8-6 8 6v11M9 21v-6h6v6"/></svg>
                </div>
                <span class="dash-stat__badge">{{ __('Finance') }}</span>
            </div>
            <div>
                <p class="dash-stat__value">{{ $totalFinancePartner }}</p>
                <p class="dash-stat__label">{{ __('Finance Partners') }}</p>
            </div>
        </div>

    </div>

    {{-- ── Growth & uptake ── --}}
    @php $sg = $signups ?? ['this_month' => 0, 'last_month' => 0, 'by_role' => [], 'trend' => []]; @endphp
    @php
        $sgDelta = $sg['this_month'] - $sg['last_month'];
        $sgMax = max(1, collect($sg['trend'])->max('count') ?? 1);
        $obpMax = max(1, ($ownersByPackage ?? collect())->max('c') ?? 1);
    @endphp
    <div class="dash-growth">
        {{-- New accounts (trend) --}}
        <div class="dash-card">
            <div class="dash-card__head">
                <h4 class="dash-card__head-title">{{ __('New accounts') }}</h4>
                <span class="grow-delta {{ $sgDelta >= 0 ? 'is-up' : 'is-down' }}">
                    {{ $sgDelta >= 0 ? '▲' : '▼' }} {{ abs($sgDelta) }} {{ __('vs last month') }}
                </span>
            </div>
            <div class="grow-body">
                <div class="grow-head">
                    <span class="grow-big">{{ number_format($sg['this_month']) }}</span>
                    <span class="grow-cap">{{ __('this month') }}</span>
                </div>
                <div class="grow-bars">
                    @foreach ($sg['trend'] as $t)
                        <div class="grow-bar">
                            <div class="grow-bar__track">
                                <div class="grow-bar__fill" style="height:{{ max(4, round(($t['count'] / $sgMax) * 100)) }}%;" title="{{ $t['count'] }}"></div>
                            </div>
                            <span class="grow-bar__num">{{ $t['count'] }}</span>
                            <span class="grow-bar__lbl">{{ $t['label'] }}</span>
                        </div>
                    @endforeach
                </div>
                @if (!empty($sg['by_role']))
                    <div class="grow-roles">
                        @foreach ($sg['by_role'] as $r)
                            <div class="grow-role"><span>{{ $r['label'] }}</span><b>{{ $r['count'] }}</b></div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        {{-- Owners by package --}}
        <div class="dash-card">
            <div class="dash-card__head">
                <h4 class="dash-card__head-title">{{ __('Owners by plan') }}</h4>
            </div>
            <div class="grow-body">
            @if (($ownersByPackage ?? collect())->isEmpty())
                <p class="grow-empty">{{ __('No active owner plans yet.') }}</p>
            @else
                <div class="grow-plans">
                    @foreach ($ownersByPackage as $p)
                        <div class="grow-plan">
                            <div class="grow-plan__top"><span>{{ $p->name ?: __('Unnamed plan') }}</span><b>{{ $p->c }}</b></div>
                            <div class="grow-plan__track"><div class="grow-plan__fill" style="width:{{ max(3, round(($p->c / $obpMax) * 100)) }}%;"></div></div>
                        </div>
                    @endforeach
                </div>
            @endif
            </div>
        </div>
    </div>

    {{-- ── Orders + Packages ── --}}
    @if (isAddonInstalled('PROTYSAAS') > 1)
    <div class="dash-grid">

        {{-- Orders Table --}}
        <div class="dash-card">
            <div class="dash-card__head">
                <h4 class="dash-card__head-title">{{ __('Recent Orders') }}</h4>
                <a href="{{ route('admin.subscriptions.orders') }}" class="dash-card__view-all">
                    {{ __('View All') }}
                    <svg viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                </a>
            </div>
            <div class="table-responsive">
                <table class="dash-table">
                    <thead>
                        <tr>
                            <th>{{ __('Package') }}</th>
                            <th>{{ __('Total') }}</th>
                            <th>{{ __('Gateway') }}</th>
                            <th>{{ __('Status') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($orders as $order)
                        <tr>
                            <td>
                                <span style="font-size:14px;font-weight:600;color:var(--blue);">
                                    {{ $order->packageName }}
                                </span>
                            </td>
                            <td class="dash-price">{{ currencyPrice($order->total) }}</td>
                            <td>{{ $order->gatewayTitle }}</td>
                            <td>
                                @if ($order->payment_status == ORDER_PAYMENT_STATUS_PAID)
                                    <span class="inv-badge inv-badge--paid"><span class="dot"></span>{{ __('Paid') }}</span>
                                @elseif ($order->payment_status == ORDER_PAYMENT_STATUS_PENDING)
                                    <span class="inv-badge inv-badge--pending"><span class="dot"></span>{{ __('Pending') }}</span>
                                @else
                                    <span class="inv-badge inv-badge--cancel"><span class="dot"></span>{{ __('Cancelled') }}</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="dash-table-empty">{{ __('No orders found') }}</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Packages Table --}}
        <div class="dash-card">
            <div class="dash-card__head">
                <h4 class="dash-card__head-title">{{ __('Packages') }}</h4>
                <a href="{{ route('admin.packages.index') }}" class="dash-card__view-all">
                    {{ __('View All') }}
                    <svg viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                </a>
            </div>
            <div class="table-responsive">
                <table class="dash-table">
                    <thead>
                        <tr>
                            <th>{{ __('Name') }}</th>
                            <th>{{ __('Monthly') }}</th>
                            <th>{{ __('Yearly') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($packages as $package)
                        <tr>
                            <td>
                                <span class="dash-pkg-name">{{ Str::limit($package->name, 22, '…') }}</span>
                            </td>
                            <td class="dash-price">{{ currencyPrice($package->monthly_price) }}</td>
                            <td class="dash-price">{{ currencyPrice($package->yearly_price) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="dash-table-empty">{{ __('No packages found') }}</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
    @endif

</div>{{-- /.dash-page --}}

                </div>{{-- /.container --}}
            </div>{{-- /.page-content-wrapper --}}
        </div>{{-- /.container-fluid --}}
    </div>{{-- /.page-content --}}
</div>{{-- /.main-content --}}
@endsection

@push('script')
<script>
    document.getElementById("cleanup-btn").addEventListener("click", function () {
        Swal.fire({
            title: "Run system cleanup?",
            text: "This action cannot be undone.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#185FA5",
            cancelButtonColor: "#993C1D",
            confirmButtonText: "Yes, clean up!",
        }).then((result) => {
            if (result.value) {
                fetch("{{ route('admin.cleanup') }}")
                    .then(r => r.json())
                    .then(() => {
                        Swal.fire({ title: "Done!", text: "System cleanup completed.", icon: "success", timer: 4000, showConfirmButton: false });
                    })
                    .catch(err => {
                        Swal.fire({ title: "Error", text: err, icon: "error" });
                    });
            }
        });
    });
</script>
@endpush