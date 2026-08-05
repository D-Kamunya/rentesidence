@extends('owner.layouts.app')

@section('content')

{{-- M-PESA Preloader (unchanged, hidden by default) --}}
<div id="mpesa-preloader" style="display:none;">
    <div id="mpesa-preloaderInner">
        <img src="{{ asset('assets/images/gateway-icon/mpesa.jpg') }}" alt="M-PESA">
        <div>
            <p>Please follow the instructions and do not refresh or leave this page.</p>
            <p>This may take up to <span id="mpesa-timer">2:00 minute(s)</span>.</p><br>
            <p>You will receive a prompt on your mobile to enter your PIN.</p><br>
            <p>Please ensure your phone is on and unlocked. Thank you.</p>
        </div>
        <img src="{{ asset('assets/images/loading.svg') }}" alt="Loading">
    </div>
</div>

<style>
/* ─── Design Tokens ─────────────────────────────── */
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

/* ─── Page Shell ─────────────────────────────────── */
.sub-page { padding: 0; }

.sub-page-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-bottom: 16px;
    margin-bottom: 28px;
    border-bottom: 0.5px solid var(--gray-200);
}

.sub-page-header h1 {
    font-size: 22px;
    font-weight: 500;
    color: var(--gray-900);
    margin: 0 0 4px;
}

.sub-breadcrumb {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    color: var(--gray-400);
    list-style: none;
    margin: 0;
    padding: 0;
}

.sub-breadcrumb a {
    color: var(--blue);
    font-weight: 500;
    text-decoration: none;
}

.sub-breadcrumb-sep {
    display: inline-flex;
    opacity: .5;
}

/* ─── Layout Grid ─────────────────────────────────── */
.sub-layout {
    display: grid;
    grid-template-columns: 1fr 320px;
    gap: 20px;
    align-items: start;
}

@media (max-width: 1024px) {
    .sub-layout { grid-template-columns: 1fr; }
}

/* ─── Card Base ───────────────────────────────────── */
.sub-card {
    background: var(--white);
    border: 0.5px solid var(--blue-faint);
    border-radius: 12px;
    overflow: hidden;
    box-shadow:
        0 4px 12px rgba(0,0,0,.04),
        0 0 0 1px rgba(24,95,165,.05),
        0 6px 18px rgba(24,95,165,.06);
    transition: all .25s ease;
}

.sub-card:hover {
    border-color: var(--blue);
    transform: translateY(-2px);
    box-shadow:
        0 10px 25px rgba(0,0,0,.06),
        0 0 0 1px rgba(24,95,165,.12),
        0 12px 30px rgba(24,95,165,.18);
}

/* ─── Current Plan Card ───────────────────────────── */
.sub-plan-head {
    padding: 20px 20px 0;
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 16px;
}

.sub-plan-eyebrow {
    font-size: 10px;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: .07em;
    color: var(--gray-400);
    margin: 0 0 5px;
}

.sub-plan-name {
    font-size: 22px;
    font-weight: 600;
    color: var(--gray-900);
    margin: 0;
    display: flex;
    align-items: baseline;
    gap: 6px;
}

.sub-plan-cadence {
    font-size: 12px;
    font-weight: 400;
    color: var(--gray-500);
}

/* Status badge */
.sub-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 11px;
    font-weight: 500;
    padding: 3px 9px;
    border-radius: 99px;
    white-space: nowrap;
}

.sub-badge--active  { background: var(--green-light); color: var(--green-dark); }
.sub-badge--free    { background: var(--blue-light);  color: #0C447C; border: 0.5px solid var(--blue-border); }
.sub-badge--expired { background: var(--red-light);   color: var(--red); }

/* ─── Progress Bars (Usage) ─────────────────────────── */
.sub-divider {
    border: none;
    border-top: 0.5px solid var(--gray-200);
    margin: 16px 0 0;
}

.sub-usage {
    padding: 20px;
}

.sub-usage-title {
    font-size: 10px;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: .07em;
    color: var(--gray-400);
    margin: 0 0 16px;
}

.sub-usage-list {
    display: flex;
    flex-direction: column;
    gap: 14px;
}

.sub-usage-row {}

.sub-usage-row-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 5px;
}

.sub-usage-label {
    font-size: 12px;
    font-weight: 500;
    color: var(--gray-700);
}

.sub-usage-count {
    font-size: 11px;
    font-weight: 500;
    color: var(--gray-500);
    font-family: monospace;
}

.sub-progress {
    height: 5px;
    background: var(--gray-100);
    border-radius: 99px;
    overflow: hidden;
}

.sub-progress-bar {
    height: 100%;
    border-radius: 99px;
    background: var(--blue);
    transition: width .6s ease;
}

.sub-progress-bar--warn  { background: #F59E0B; }
.sub-progress-bar--full  { background: var(--red); }
.sub-progress-bar--unlim { background: var(--green); width: 100% !important; }

/* Text items (no bar — ticket support, notice support) */
.sub-feature-row {
    display: flex;
    align-items: center;
    gap: 7px;
    font-size: 12px;
    font-weight: 500;
    color: var(--gray-700);
    padding: 5px 0;
}

.sub-feature-row svg {
    flex-shrink: 0;
    color: var(--green);
}

/* Dates strip */
.sub-dates {
    display: flex;
    gap: 0;
    border-top: 0.5px solid var(--gray-200);
}

.sub-date-item {
    flex: 1;
    padding: 14px 20px;
    border-right: 0.5px solid var(--gray-200);
}

.sub-date-item:last-child { border-right: none; }

.sub-date-label {
    font-size: 10px;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: .07em;
    color: var(--gray-400);
    margin: 0 0 3px;
}

.sub-date-value {
    font-size: 13px;
    font-weight: 500;
    color: var(--gray-800);
}

/* ─── Card Footer / Actions ─────────────────────────── */
.sub-card-footer {
    padding: 16px 20px;
    background: var(--gray-50);
    border-top: 0.5px solid var(--gray-200);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
}

/* ─── Buttons ─────────────────────────────────────── */
.sub-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    font-weight: 500;
    padding: 7px 15px;
    border-radius: 7px;
    border: none;
    cursor: pointer;
    transition: all .13s;
    text-decoration: none;
}

.sub-btn--primary {
    background: var(--blue);
    color: var(--white);
}

.sub-btn--primary:hover {
    background: var(--blue-hover);
    transform: translateY(-1px);
    color: var(--white);
}

.sub-btn--ghost {
    background: var(--gray-100);
    color: var(--gray-700);
    border: 0.5px solid var(--gray-200);
}

.sub-btn--ghost:hover {
    background: var(--gray-200);
}

.sub-btn--danger {
    background: var(--red-light);
    color: var(--red);
    border: 0.5px solid #f5c6b8;
}

.sub-btn--danger:hover {
    background: #f5d5cc;
}

/* ─── No Plan State ───────────────────────────────── */
.sub-empty {
    padding: 48px 20px;
    text-align: center;
}

.sub-empty-icon {
    width: 52px;
    height: 52px;
    background: var(--blue-light);
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 16px;
    color: var(--blue);
}

.sub-empty h3 {
    font-size: 16px;
    font-weight: 600;
    color: var(--gray-900);
    margin: 0 0 6px;
}

.sub-empty p {
    font-size: 13px;
    color: var(--gray-500);
    margin: 0 0 20px;
    line-height: 1.6;
}

/* ─── Side Info Card ──────────────────────────────── */
.sub-info-card {
    background: var(--white);
    border: 0.5px solid var(--gray-200);
    border-radius: 12px;
    overflow: hidden;
}

.sub-info-head {
    padding: 14px 20px;
    background: var(--gray-50);
    border-bottom: 0.5px solid var(--gray-200);
}

.sub-info-head-title {
    font-size: 12px;
    font-weight: 600;
    color: var(--gray-800);
    margin: 0;
}

.sub-info-body {
    padding: 20px;
}

.sub-info-row {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    padding: 10px 0;
    border-bottom: 0.5px solid var(--gray-100);
    font-size: 12px;
    color: var(--gray-700);
    line-height: 1.5;
}

.sub-info-row:last-child { border-bottom: none; }

.sub-info-row svg {
    flex-shrink: 0;
    margin-top: 1px;
    color: var(--blue);
}

/* ─── Cancel Zone ─────────────────────────────────── */
.sub-cancel-zone {
    margin-top: 12px;
    background: var(--white);
    border: 0.5px solid #f5c6b8;
    border-radius: 12px;
    padding: 20px;
}

.sub-cancel-zone-head {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    font-weight: 600;
    color: var(--red);
    margin: 0 0 8px;
}

.sub-cancel-zone p {
    font-size: 12px;
    color: var(--gray-500);
    margin: 0 0 16px;
    line-height: 1.6;
}
</style>

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid sub-page">

            {{-- ── Page Header ── --}}
            <div class="sub-page-header">
                <div>
                    <h1>{{ $pageTitle }}</h1>
                    <ol class="sub-breadcrumb">
                        <li><a href="{{ route('owner.dashboard') }}">Dashboard</a></li>
                        <li class="sub-breadcrumb-sep">
                            <svg width="8" height="8" viewBox="0 0 8 8" fill="none">
                                <path d="M2.5 1.5L5.5 4L2.5 6.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </li>
                        <li>{{ $pageTitle }}</li>
                    </ol>
                </div>
            </div>

            <div class="sub-layout">

                {{-- ── LEFT: Main Plan Card ── --}}
                <div>
                    @if (!is_null($userPlan))

                        {{-- ── Active Plan Card ── --}}
                        <div class="sub-card">

                            {{-- Head --}}
                            <div class="sub-plan-head">
                                <div>
                                    <p class="sub-plan-eyebrow">Current Plan</p>
                                    <h2 class="sub-plan-name">
                                        {{ $userPlan->name }}
                                        <span class="sub-plan-cadence">
                                            / {{ $userPlan->duration_type == PACKAGE_DURATION_TYPE_MONTHLY ? 'Monthly' : 'Yearly' }}
                                        </span>
                                    </h2>
                                </div>
                                <span class="sub-badge sub-badge--active">
                                    <svg width="7" height="7" viewBox="0 0 7 7" fill="currentColor">
                                        <circle cx="3.5" cy="3.5" r="3.5"/>
                                    </svg>
                                    Active
                                </span>
                            </div>

                            <hr class="sub-divider">

                            {{-- Usage --}}
                            <div class="sub-usage">
                                <p class="sub-usage-title">Usage</p>
                                <div class="sub-usage-list">

                                    @if ($userPlan->package_type == PACKAGE_TYPE_PROPERTY)
                                        @php $used = getExistingProperty(auth()->id()); $max = $userPlan->quantity; $pct = $max > 0 ? min(100, round($used/$max*100)) : 0; @endphp
                                        <div class="sub-usage-row">
                                            <div class="sub-usage-row-head">
                                                <span class="sub-usage-label">Properties</span>
                                                <span class="sub-usage-count">{{ $used }} / {{ $max }}</span>
                                            </div>
                                            <div class="sub-progress">
                                                <div class="sub-progress-bar {{ $pct >= 100 ? 'sub-progress-bar--full' : ($pct >= 80 ? 'sub-progress-bar--warn' : '') }}" style="width:{{ $pct }}%"></div>
                                            </div>
                                        </div>

                                    @elseif ($userPlan->package_type == PACKAGE_TYPE_UNIT)
                                        @php $used = getExistingUnit(auth()->id()); $max = $userPlan->max_unit; $pct = $max > 0 ? min(100, round($used/$max*100)) : 0; @endphp
                                        <div class="sub-usage-row">
                                            <div class="sub-usage-row-head">
                                                <span class="sub-usage-label">Units</span>
                                                <span class="sub-usage-count">{{ $used }} / {{ $max }}</span>
                                            </div>
                                            <div class="sub-progress">
                                                <div class="sub-progress-bar {{ $pct >= 100 ? 'sub-progress-bar--full' : ($pct >= 80 ? 'sub-progress-bar--warn' : '') }}" style="width:{{ $pct }}%"></div>
                                            </div>
                                        </div>

                                    @elseif ($userPlan->package_type == PACKAGE_TYPE_TENANT)
                                        @php $used = getExistingTenant(auth()->id()); $max = $userPlan->quantity; $pct = $max > 0 ? min(100, round($used/$max*100)) : 0; @endphp
                                        <div class="sub-usage-row">
                                            <div class="sub-usage-row-head">
                                                <span class="sub-usage-label">Tenants</span>
                                                <span class="sub-usage-count">{{ $used }} / {{ $max }}</span>
                                            </div>
                                            <div class="sub-progress">
                                                <div class="sub-progress-bar {{ $pct >= 100 ? 'sub-progress-bar--full' : ($pct >= 80 ? 'sub-progress-bar--warn' : '') }}" style="width:{{ $pct }}%"></div>
                                            </div>
                                        </div>

                                    @else
                                        @php $usedProp = getExistingProperty(auth()->id()); $maxProp = $userPlan->max_property; $pctProp = $maxProp > 0 ? min(100, round($usedProp/$maxProp*100)) : 0; @endphp
                                        <div class="sub-usage-row">
                                            <div class="sub-usage-row-head">
                                                <span class="sub-usage-label">Properties</span>
                                                <span class="sub-usage-count">{{ $usedProp }} / {{ $maxProp }}</span>
                                            </div>
                                            <div class="sub-progress">
                                                <div class="sub-progress-bar {{ $pctProp >= 100 ? 'sub-progress-bar--full' : ($pctProp >= 80 ? 'sub-progress-bar--warn' : '') }}" style="width:{{ $pctProp }}%"></div>
                                            </div>
                                        </div>
                                        @php $usedTen = getExistingTenant(auth()->id()); $maxTen = $userPlan->max_tenant; $pctTen = $maxTen > 0 ? min(100, round($usedTen/$maxTen*100)) : 0; @endphp
                                        <div class="sub-usage-row">
                                            <div class="sub-usage-row-head">
                                                <span class="sub-usage-label">Tenants</span>
                                                <span class="sub-usage-count">{{ $usedTen }} / {{ $maxTen }}</span>
                                            </div>
                                            <div class="sub-progress">
                                                <div class="sub-progress-bar {{ $pctTen >= 100 ? 'sub-progress-bar--full' : ($pctTen >= 80 ? 'sub-progress-bar--warn' : '') }}" style="width:{{ $pctTen }}%"></div>
                                            </div>
                                        </div>
                                    @endif

                                    {{-- Maintainers --}}
                                    @if ($userPlan->max_maintainer == -1)
                                        <div class="sub-usage-row">
                                            <div class="sub-usage-row-head">
                                                <span class="sub-usage-label">Maintainers</span>
                                                <span class="sub-usage-count">{{ getExistingMaintainers(auth()->id()) }} / ∞</span>
                                            </div>
                                            <div class="sub-progress"><div class="sub-progress-bar sub-progress-bar--unlim"></div></div>
                                        </div>
                                    @else
                                        @php $usedM = getExistingMaintainers(auth()->id()); $maxM = $userPlan->max_maintainer; $pctM = $maxM > 0 ? min(100, round($usedM/$maxM*100)) : 0; @endphp
                                        <div class="sub-usage-row">
                                            <div class="sub-usage-row-head">
                                                <span class="sub-usage-label">Maintainers</span>
                                                <span class="sub-usage-count">{{ $usedM }} / {{ $maxM }}</span>
                                            </div>
                                            <div class="sub-progress">
                                                <div class="sub-progress-bar {{ $pctM >= 100 ? 'sub-progress-bar--full' : ($pctM >= 80 ? 'sub-progress-bar--warn' : '') }}" style="width:{{ $pctM }}%"></div>
                                            </div>
                                        </div>
                                    @endif

                                    {{-- Invoices --}}
                                    @if ($userPlan->max_invoice == -1)
                                        <div class="sub-usage-row">
                                            <div class="sub-usage-row-head">
                                                <span class="sub-usage-label">Invoices</span>
                                                <span class="sub-usage-count">{{ getExistingInvoice(auth()->id()) }} / ∞</span>
                                            </div>
                                            <div class="sub-progress"><div class="sub-progress-bar sub-progress-bar--unlim"></div></div>
                                        </div>
                                    @else
                                        @php $usedI = getExistingInvoice(auth()->id()); $maxI = $userPlan->max_invoice; $pctI = $maxI > 0 ? min(100, round($usedI/$maxI*100)) : 0; @endphp
                                        <div class="sub-usage-row">
                                            <div class="sub-usage-row-head">
                                                <span class="sub-usage-label">Invoices</span>
                                                <span class="sub-usage-count">{{ $usedI }} / {{ $maxI }}</span>
                                            </div>
                                            <div class="sub-progress">
                                                <div class="sub-progress-bar {{ $pctI >= 100 ? 'sub-progress-bar--full' : ($pctI >= 80 ? 'sub-progress-bar--warn' : '') }}" style="width:{{ $pctI }}%"></div>
                                            </div>
                                        </div>
                                    @endif

                                    {{-- Auto Invoices --}}
                                    @if ($userPlan->max_auto_invoice == -1)
                                        <div class="sub-usage-row">
                                            <div class="sub-usage-row-head">
                                                <span class="sub-usage-label">Auto Invoices</span>
                                                <span class="sub-usage-count">{{ getExistingAutoInvoice(auth()->id()) }} / ∞</span>
                                            </div>
                                            <div class="sub-progress"><div class="sub-progress-bar sub-progress-bar--unlim"></div></div>
                                        </div>
                                    @else
                                        @php $usedA = getExistingAutoInvoice(auth()->id()); $maxA = $userPlan->max_auto_invoice; $pctA = $maxA > 0 ? min(100, round($usedA/$maxA*100)) : 0; @endphp
                                        <div class="sub-usage-row">
                                            <div class="sub-usage-row-head">
                                                <span class="sub-usage-label">Auto Invoices</span>
                                                <span class="sub-usage-count">{{ $usedA }} / {{ $maxA }}</span>
                                            </div>
                                            <div class="sub-progress">
                                                <div class="sub-progress-bar {{ $pctA >= 100 ? 'sub-progress-bar--full' : ($pctA >= 80 ? 'sub-progress-bar--warn' : '') }}" style="width:{{ $pctA }}%"></div>
                                            </div>
                                        </div>
                                    @endif
                                    {{-- Marketplace Listings --}}
                                    @php
                                        $owner = \App\Models\Owner::where('user_id', Auth::id())->firstOrFail();
                                        $usedProds = \App\Models\Product::where('owner_user_id', $owner->id)->count(); $maxProds = $userPlan->max_marketplace_listings; $pctProds = $maxProds > 0 ? min(100, round($usedProds/$maxProds*100)) : 0; 
                                    @endphp
                                    @if ($userPlan->max_marketplace_listings== 0)
                                        <div class="sub-usage-row">
                                            <div class="sub-usage-row-head">
                                                <span class="sub-usage-label">Markeplace Listings</span>
                                                <span class="sub-usage-count"> {{ $usedProds }} / ∞</span>
                                            </div>
                                            <div class="sub-progress"><div class="sub-progress-bar sub-progress-bar--unlim"></div></div>
                                        </div>
                                    @else
                                        <div class="sub-usage-row">
                                            <div class="sub-usage-row-head">
                                                <span class="sub-usage-label">Marketplace Listings</span>
                                                <span class="sub-usage-count">{{ $usedProds }} / {{ $maxProds }}</span>
                                            </div>
                                            <div class="sub-progress">
                                                <div class="sub-progress-bar {{ $pctProds >= 100 ? 'sub-progress-bar--full' : ($pctProds >= 80 ? 'sub-progress-bar--warn' : '') }}" style="width:{{ $pctProds }}%"></div>
                                            </div>
                                        </div>
                                    @endif
                                    {{-- Feature flags --}}
                                    @if ($userPlan->ticket_support == ACTIVE)
                                        <div class="sub-feature-row">
                                            <svg width="13" height="13" viewBox="0 0 13 13" fill="none">
                                                <path d="M2.5 6.5L5.2 9.5L10.5 3.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                            Ticket Support included
                                        </div>
                                    @endif

                                    @if ($userPlan->notice_support == ACTIVE)
                                        <div class="sub-feature-row">
                                            <svg width="13" height="13" viewBox="0 0 13 13" fill="none">
                                                <path d="M2.5 6.5L5.2 9.5L10.5 3.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                            Notice Support included
                                        </div>
                                    @endif

                                </div>
                            </div>

                            {{-- Dates --}}
                            <div class="sub-dates">
                                <div class="sub-date-item">
                                    <p class="sub-date-label">Plan Started</p>
                                    <span class="sub-date-value">{{ $userPlan->start_date }}</span>
                                </div>
                                <div class="sub-date-item">
                                    <p class="sub-date-label">Plan Expires</p>
                                    <span class="sub-date-value">{{ $userPlan->end_date }}</span>
                                </div>
                            </div>

                            {{-- Footer --}}
                            <div class="sub-card-footer">
                                <span style="font-size:12px; color:var(--gray-500);">{{ __('Renew your plan, or switch to another.') }}</span>
                                <div style="display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
                                    {{-- Renew: reuse the current plan → straight to the (bundled) checkout,
                                         skipping plan selection. Only for paid subscription plans, and only
                                         enabled once the plan is close to / past expiry (greyed while there's
                                         still plenty of time — nothing to renew yet). --}}
                                    @if (($userPlan->pricing_model ?? '') === 'subscription')
                                        @php
                                            $renewExpiry   = \Carbon\Carbon::parse($userPlan->end_date)->startOfDay();
                                            $renewDaysLeft = now()->startOfDay()->diffInDays($renewExpiry, false);
                                            $canRenew      = $renewDaysLeft <= (int) getOption('plan_expiry_notice_days', 3);
                                        @endphp
                                        @if ($canRenew)
                                            <a href="{{ route('owner.subscription.index', ['id' => $userPlan->package_id, 'duration_type' => $userPlan->duration_type ?? 1, 'quantity' => $userPlan->quantity ?? 1]) }}"
                                               class="sub-btn sub-btn--primary" style="text-decoration:none;">
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <polyline points="23 4 23 10 17 10"/>
                                                    <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/>
                                                </svg>
                                                {{ __('Renew') }}
                                            </a>
                                        @else
                                            <button type="button" class="sub-btn sub-btn--primary" disabled
                                                    style="opacity:.45; cursor:not-allowed;"
                                                    title="{{ __('Available when your plan is close to expiring.') }}">
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <polyline points="23 4 23 10 17 10"/>
                                                    <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/>
                                                </svg>
                                                {{ __('Renew') }}
                                            </button>
                                        @endif
                                    @endif
                                    <button type="button" class="sub-btn sub-btn--ghost" id="chooseAPlan">
                                        <svg width="13" height="13" viewBox="0 0 13 13" fill="none">
                                            <path d="M6.5 2V11M2 6.5H11" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                        </svg>
                                        {{ __('Choose another plan') }}
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- ── Billing breakdown — branches on the owner's pricing model ── --}}
                        @php
                            $isTx = $isTransactionMode ?? false;
                            $st = $moduleInvoiceStatus ?? null;
                            $stMap = [
                                'overdue' => ['Overdue', '#fef2f2', '#b91c1c'],
                                'partially_paid' => ['Partially recovered', '#fffbeb', '#b45309'],
                                'pending' => [$isTx ? 'Recovers from rent' : 'Due this month', '#eff6ff', '#1d4ed8'],
                                'paid' => [$isTx ? 'Recovered' : 'Paid', '#f0fdf4', '#15803d'],
                            ];
                            $rate = rtrim(rtrim(number_format($rentCommissionRate ?? 1, 2), '0'), '.');
                        @endphp
                        <div class="sub-card" style="margin-top:20px;padding:20px;">
                            <div style="display:flex;justify-content:space-between;align-items:center;">
                                <h3 style="font-size:15px;font-weight:600;color:#111827;margin:0;">{{ $isTx ? __('Billing') : __('Monthly billing') }}</h3>
                                @if ($st && isset($stMap[$st]))
                                    <span style="font-size:11px;font-weight:600;padding:3px 10px;border-radius:99px;background:{{ $stMap[$st][1] }};color:{{ $stMap[$st][2] }};">{{ __($stMap[$st][0]) }}</span>
                                @else
                                    <span style="font-size:11px;color:#6b7280;">{{ now()->format('F Y') }}</span>
                                @endif
                            </div>

                            @if ($isTx)
                                {{-- Transaction pricing: no monthly subscription; fee per rent payment; infra recovered from rent. --}}
                                <p style="font-size:12px;color:#6b7280;margin:6px 0 14px;">{{ __('You\'re on transaction pricing — Centresidence takes a small fee from each rent payment, with no fixed monthly subscription.') }}</p>

                                <div style="display:flex;justify-content:space-between;font-size:13.5px;color:#374151;padding:8px 0;border-bottom:0.5px solid #f3f4f6;">
                                    <span>{{ __('Transaction fee') }} <span style="color:#9ca3af;">· {{ __('per rent payment') }}</span></span>
                                    <b style="color:#111827;">{{ $rate }}%</b>
                                </div>

                                @if ($moduleCosts['has_modules'])
                                    <div style="padding:10px 0;border-bottom:0.5px solid #f3f4f6;">
                                        <div style="font-size:11px;text-transform:uppercase;letter-spacing:.06em;color:#9ca3af;margin-bottom:8px;">{{ __('Infrastructure costs · recovered from rent') }}</div>
                                        @foreach ($moduleCosts['lines'] as $line)
                                            <div style="display:flex;justify-content:space-between;font-size:12.5px;color:#6b7280;padding:4px 0;">
                                                <span>{{ $line['module_name'] }} · {{ ucwords(str_replace('_', ' ', $line['component_name'])) }} <span style="color:#cbd5e1;">(× {{ $line['device_count'] }})</span></span>
                                                <span>KES {{ number_format((float) $line['subtotal'], 2) }}</span>
                                            </div>
                                        @endforeach
                                        <div style="display:flex;justify-content:space-between;font-size:13px;color:#374151;padding-top:8px;font-weight:500;">
                                            <span>{{ __('Recovered from rent / month') }}</span>
                                            <b style="color:#111827;">KES {{ number_format((float) $moduleCosts['total'], 2) }}</b>
                                        </div>
                                    </div>
                                    <p style="font-size:11px;color:#9ca3af;margin:8px 0 0;">{{ __('These infrastructure costs are deducted from your rent within your deduction cap — not billed as a monthly fee.') }} <a href="{{ route('owner.financing.deductions') }}" style="color:#1d4ed8;font-weight:500;">{{ __('See Rent & deductions') }}</a></p>
                                @else
                                    <p style="font-size:11px;color:#9ca3af;margin:10px 0 0;">{{ __('No smart modules deployed yet. When you add them, their infrastructure cost is recovered from rent — shown here and on Rent & deductions.') }}</p>
                                @endif
                            @else
                                {{-- Subscription pricing: plan + subscription-billed module costs = monthly total. --}}
                                <p style="font-size:12px;color:#6b7280;margin:6px 0 14px;">{{ __('What makes up your monthly total — your plan plus any smart modules deployed on your properties.') }}</p>

                                <div style="display:flex;justify-content:space-between;font-size:13.5px;color:#374151;padding:8px 0;border-bottom:0.5px solid #f3f4f6;">
                                    <span>{{ __('Subscription plan') }} <span style="color:#9ca3af;">· {{ $userPlan->name }}</span></span>
                                    <b style="color:#111827;">KES {{ number_format($packageAmount, 2) }}</b>
                                </div>

                                @if ($moduleCosts['has_modules'])
                                    <div style="padding:10px 0;border-bottom:0.5px solid #f3f4f6;">
                                        <div style="font-size:11px;text-transform:uppercase;letter-spacing:.06em;color:#9ca3af;margin-bottom:8px;">{{ __('Smart module costs') }}</div>
                                        @foreach ($moduleCosts['lines'] as $line)
                                            <div style="display:flex;justify-content:space-between;font-size:12.5px;color:#6b7280;padding:4px 0;">
                                                <span>{{ $line['module_name'] }} · {{ ucwords(str_replace('_', ' ', $line['component_name'])) }} <span style="color:#cbd5e1;">(× {{ $line['device_count'] }})</span></span>
                                                <span>KES {{ number_format((float) $line['subtotal'], 2) }}</span>
                                            </div>
                                        @endforeach
                                        <div style="display:flex;justify-content:space-between;font-size:13px;color:#374151;padding-top:8px;font-weight:500;">
                                            <span>{{ __('Modules subtotal') }}</span>
                                            <b style="color:#111827;">KES {{ number_format((float) $moduleCosts['total'], 2) }}</b>
                                        </div>
                                    </div>
                                @endif

                                <div style="display:flex;justify-content:space-between;align-items:baseline;font-weight:700;color:#111827;padding:14px 0 4px;">
                                    <span style="font-size:15px;">{{ __('Estimated monthly total') }}</span>
                                    <span style="font-size:19px;">KES {{ number_format($packageAmount + (float) $moduleCosts['total'], 2) }}</span>
                                </div>
                                @if ($moduleCosts['has_modules'])
                                    <p style="font-size:11px;color:#9ca3af;margin:6px 0 0;">{{ __('A projection of your current setup’s monthly cost — it updates with the number of active devices. It is not a charge; your issued bills are shown below.') }}</p>
                                @else
                                    <p style="font-size:11px;color:#9ca3af;margin:6px 0 0;">{{ __('Deploy smart modules (water, gas, locks) to earn more from your units — any subscription-billed module costs will appear here.') }}</p>
                                @endif

                                {{-- Outstanding module-infrastructure bill — payable now via M-Pesa. --}}
                                @if (($infraOutstanding ?? 0) > 0)
                                    @php $ovd = ($moduleInvoiceStatus ?? null) === 'overdue'; $cnt = (int) ($infraOutstandingCount ?? 0); @endphp
                                    <div style="margin-top:16px;padding:14px;border:0.5px solid {{ $ovd ? '#F5C4B3' : '#FAD9A0' }};background:{{ $ovd ? '#FCEBEB' : '#FEF9EE' }};border-radius:10px;">
                                        <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;flex-wrap:wrap;">
                                            <div style="min-width:0;">
                                                <div style="font-size:13px;font-weight:600;color:{{ $ovd ? '#A32D2D' : '#854F0B' }};">
                                                    {{ $ovd ? __('Infrastructure bill overdue') : __('Infrastructure bill due') }}
                                                </div>
                                                <p style="font-size:11.5px;color:#6b7280;margin:4px 0 0;max-width:50ch;">
                                                    @if($cnt > 0){{ __('Outstanding from') }} {{ $cnt }} {{ $cnt === 1 ? __('issued bill') : __('issued bills') }} — @endif{{ __('payable now. This is your deployed smart-module infrastructure, billed separately from your plan; it can differ from the estimate above, which reflects your current devices.') }}
                                                    {{ $ovd ? __('Settle to restore full access.') : __('Settle to keep full access.') }}
                                                </p>
                                            </div>
                                            <div style="font-size:16px;font-weight:700;color:{{ $ovd ? '#A32D2D' : '#854F0B' }};white-space:nowrap;">KES {{ number_format((float) $infraOutstanding, 2) }}</div>
                                        </div>
                                        @if (empty($infraBundlesWithPlan))
                                            {{-- Yearly / plan-less owners pay infra standalone. --}}
                                            <form method="POST" action="{{ route('owner.infra-bill.pay') }}" style="margin-top:12px;">
                                                @csrf
                                                <button type="submit" style="display:inline-flex;align-items:center;gap:6px;padding:9px 18px;border:none;border-radius:7px;background:{{ $ovd ? '#A32D2D' : '#854F0B' }};color:#fff;font-size:13px;font-weight:600;cursor:pointer;">
                                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/></svg>
                                                    {{ __('Pay via M-Pesa') }}
                                                </button>
                                            </form>
                                        @else
                                            {{-- Monthly owners: this is bundled into the plan renewal — one payment. --}}
                                            <p style="font-size:11.5px;color:#6b7280;margin:12px 0 0;display:inline-flex;align-items:center;gap:6px;">
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="{{ $ovd ? '#A32D2D' : '#854F0B' }}" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
                                                {{ __('Included automatically when you renew your plan — no separate payment needed.') }}
                                            </p>
                                        @endif
                                    </div>
                                @endif
                            @endif
                        </div>

                        {{-- ── Cancel Zone ── --}}
                        <div class="sub-cancel-zone">
                            <p class="sub-cancel-zone-head">
                                <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                                    <path d="M7 1L13 12H1L7 1Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
                                    <path d="M7 5.5V8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                    <circle cx="7" cy="10" r=".6" fill="currentColor"/>
                                </svg>
                                Cancel Subscription
                            </p>
                            <p>Cancelling will permanently remove access to all saved content and features tied to this plan. This cannot be undone.</p>
                            <form action="{{ route('owner.subscription.cancel') }}" method="post">
                                @csrf
                                <button type="button" class="sub-btn sub-btn--danger subscriptionCancel">
                                    Cancel my subscription
                                </button>
                            </form>
                        </div>

                    @else

                        {{-- ── No Plan State ── --}}
                        <div class="sub-card">
                            <div class="sub-empty">
                                <div class="sub-empty-icon">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z" fill="currentColor"/>
                                    </svg>
                                </div>
                                <h3>No Active Subscription</h3>
                                <p>Choose a plan to unlock properties, tenants,<br>invoicing, and more.</p>
                                <button type="button" class="sub-btn sub-btn--primary" id="chooseAPlan">
                                    <svg width="13" height="13" viewBox="0 0 13 13" fill="none">
                                        <path d="M6.5 2V11M2 6.5H11" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                    </svg>
                                    Choose a Plan
                                </button>
                            </div>
                        </div>

                    @endif
                </div>

                {{-- ── RIGHT: Help/Info Sidebar ── --}}
                <div>
                    <div class="sub-info-card">
                        <div class="sub-info-head">
                            <p class="sub-info-head-title">About Your Subscription</p>
                        </div>
                        <div class="sub-info-body">
                            <div class="sub-info-row">
                                <svg width="13" height="13" viewBox="0 0 13 13" fill="none">
                                    <circle cx="6.5" cy="6.5" r="5.5" stroke="currentColor" stroke-width="1.5"/>
                                    <path d="M6.5 5.5V9.5M6.5 3.5V4.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                </svg>
                                <span>Progress bars turn <strong style="color:#F59E0B">amber</strong> at 80% and <strong style="color:var(--red)">red</strong> when a limit is reached.</span>
                            </div>
                            <div class="sub-info-row">
                                <svg width="13" height="13" viewBox="0 0 13 13" fill="none">
                                    <path d="M1.5 6.5L4.5 9.5L11.5 2.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                <span>Unlimited items (∞) are shown with a solid green bar.</span>
                            </div>
                            <div class="sub-info-row">
                                <svg width="13" height="13" viewBox="0 0 13 13" fill="none">
                                    <rect x="1.5" y="3" width="10" height="7.5" rx="1.5" stroke="currentColor" stroke-width="1.5"/>
                                    <path d="M1.5 5.5H11.5" stroke="currentColor" stroke-width="1.5"/>
                                </svg>
                                <span>Upgrades take effect immediately. Your billing date stays the same.</span>
                            </div>
                            <div class="sub-info-row">
                                <svg width="13" height="13" viewBox="0 0 13 13" fill="none">
                                    <path d="M6.5 1.5v2M6.5 9.5v2M1.5 6.5h2M9.5 6.5h2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                    <circle cx="6.5" cy="6.5" r="3" stroke="currentColor" stroke-width="1.5"/>
                                </svg>
                                <span>Need help choosing? Contact support and we'll recommend the right plan for your portfolio size.</span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            {{-- /sub-layout --}}

        </div>
    </div>
</div>

@if (!is_null(request()->id))
    <input type="hidden" id="requestPlanId" value="{{ request()->id }}">
    <input type="text" id="gatewayResponse" value="{{ $gateways }}">
@endif
<input type="hidden" id="requestCurrentPlan" value="{{ request()->current_plan }}">

@endsection

@push('script')
    <script src="{{ asset('assets/js/custom/owner-subscription.js') }}"></script>
@endpush