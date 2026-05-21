@extends('admin.layouts.app')

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="page-content-wrapper bg-white p-30 radius-20">

                {{-- Page Header --}}
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between border-bottom mb-20">
                            <div class="page-title-left">
                                <h3 class="mb-sm-0">{{ __('Settings') }}</h3>
                            </div>
                            <div class="page-title-right">
                                <ol class="breadcrumb mb-0">
                                    <li class="breadcrumb-item">
                                        <a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a>
                                    </li>
                                    <li class="breadcrumb-item">
                                        <a href="#">{{ __('Settings') }}</a>
                                    </li>
                                    <li class="breadcrumb-item active" aria-current="page">{{ $pageTitle }}</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="settings-page-layout-wrap position-relative">
                    <div class="row">
                        @include('admin.setting.sidebar')

                        <div class="col-md-12 col-lg-12 col-xl-8 col-xxl-9">
                            <div class="account-settings-rightside bg-off-white theme-border radius-4 p-25">

                                {{-- Section header --}}
                                <div class="account-settings-title border-bottom mb-20 pb-20">
                                    <div class="row align-items-center">
                                        <div class="col-md-8">
                                            <h4 class="mb-1">{{ $pageTitle }}</h4>
                                            <p class="text-muted mb-0" style="font-size:13px;">
                                                {{ __('Configure the Mpesa account that will receive all Subscription, marketplace and SMS payments. Commission deductions and owner wallet credits are processed through this account.') }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                {{-- Flash messages --}}
                                @if(session('success'))
                                    <div class="mkt-alert mkt-alert--success mb-20">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                                            <path d="M22 11.08V12a10 10 0 11-5.93-9.14" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M22 4L12 14.01l-3-3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                        {{ session('success') }}
                                    </div>
                                @endif

                                @if(session('error'))
                                    <div class="mkt-alert mkt-alert--error mb-20">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                                            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
                                            <path d="M12 8v4m0 4h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                        </svg>
                                        {{ session('error') }}
                                    </div>
                                @endif

                                <form action="{{ route('admin.setting.marketplaceaccounts.setting.save') }}" method="POST">
                                    @csrf

                                    {{-- Mpesa account card --}}
                                    <div class="mkt-setting-card">
                                        <div class="mkt-setting-card__header">
                                            <div class="mkt-setting-card__icon">
                                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                                                    <rect x="1" y="4" width="22" height="16" rx="2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                                    <line x1="1" y1="10" x2="23" y2="10" stroke="currentColor" stroke-width="1.8"/>
                                                </svg>
                                            </div>
                                            <div>
                                                <h5 class="mkt-setting-card__title">{{ __('Subscription, Marketplace and SMS Payment Account') }}</h5>
                                                <p class="mkt-setting-card__sub">{{ __('All owner subscriptions, tenant marketplace and SMS payments will be received by this account') }}</p>
                                            </div>
                                        </div>

                                        <div class="mkt-setting-card__body">
                                            @php
                                                $accounts = \App\Models\MpesaAccount::where('status', ACTIVE)->get();
                                                $current  = getOption('centresidence_mpesa_account_id');
                                            @endphp

                                            @if($accounts->isEmpty())
                                                <div class="mkt-empty-state">
                                                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none">
                                                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.5"/>
                                                        <path d="M12 8v4m0 4h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                                    </svg>
                                                    <p>{{ __('No active Mpesa accounts found.') }}</p>
                                                    <small>{{ __('Add and activate an Mpesa account in the Gateway settings first.') }}</small>
                                                </div>
                                            @else
                                                {{-- Account option cards --}}
                                                <div class="mkt-account-grid" id="accountGrid">
                                                    @foreach($accounts as $account)
                                                        @php
                                                            $isSelected = $current == $account->id;
                                                            $detail = $account->account_type === 'PAYBILL'
                                                                ? 'Paybill: ' . $account->paybill . ' · Acc: ' . $account->account_name
                                                                : 'Till No: ' . $account->till_number;
                                                        @endphp
                                                        <label class="mkt-account-option {{ $isSelected ? 'mkt-account-option--selected' : '' }}"
                                                               for="account_{{ $account->id }}">
                                                            <input type="radio"
                                                                   name="centresidence_mpesa_account_id"
                                                                   id="account_{{ $account->id }}"
                                                                   value="{{ $account->id }}"
                                                                   {{ $isSelected ? 'checked' : '' }}
                                                                   class="mkt-account-radio">
                                                            <div class="mkt-account-option__body">
                                                                <div class="mkt-account-option__type">
                                                                    @if($account->account_type === 'PAYBILL')
                                                                        <span class="mkt-badge mkt-badge--blue">Paybill</span>
                                                                    @else
                                                                        <span class="mkt-badge mkt-badge--green">Till Number</span>
                                                                    @endif
                                                                </div>
                                                                <p class="mkt-account-option__detail">{{ $detail }}</p>
                                                            </div>
                                                            <div class="mkt-account-option__check">
                                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none">
                                                                    <path d="M20 6L9 17l-5-5" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                                </svg>
                                                            </div>
                                                        </label>
                                                    @endforeach
                                                </div>

                                                @if($current)
                                                    <p class="mkt-current-note">
                                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" style="flex-shrink:0">
                                                            <path d="M22 11.08V12a10 10 0 11-5.93-9.14" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                            <path d="M22 4L12 14.01l-3-3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                        </svg>
                                                        {{ __('Currently active. All subscriptions, marketplace and SMS payments will route through the selected account.') }}
                                                    </p>
                                                @endif
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Save button --}}
                                    @if(!$accounts->isEmpty())
                                        <div class="mkt-form-actions">
                                            <button type="submit" class="mkt-save-btn">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                                                    <path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                    <polyline points="17 21 17 13 7 13 7 21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                    <polyline points="7 3 7 8 15 8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                                {{ __('Save Settings') }}
                                            </button>
                                        </div>
                                    @endif

                                </form>

                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection

@push('style')
<style>
/* ── Alerts ──────────────────────────────────────────────────── */
.mkt-alert {
    display: flex; align-items: center; gap: 10px;
    padding: 12px 16px; border-radius: 9px;
    font-size: 13px; font-weight: 500;
}
.mkt-alert--success { background: #E1F5EE; color: #0F6E56; border: 0.5px solid #A7DFC9; }
.mkt-alert--error   { background: #FDF4F1; color: #993C1D; border: 0.5px solid #F5C6B8; }

/* ── Setting card ────────────────────────────────────────────── */
.mkt-setting-card {
    background: #fff;
    border: 0.5px solid #e5e7eb;
    border-radius: 12px;
    overflow: hidden;
    margin-bottom: 1.5rem;
}
.mkt-setting-card__header {
    display: flex; align-items: flex-start; gap: 12px;
    padding: 1rem 1.25rem;
    background: #fafafa;
    border-bottom: 0.5px solid #e5e7eb;
}
.mkt-setting-card__icon {
    width: 38px; height: 38px; border-radius: 10px;
    background: #E6F1FB; color: #185FA5;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.mkt-setting-card__title { font-size: 14px; font-weight: 600; color: #111827; margin: 0 0 3px; }
.mkt-setting-card__sub   { font-size: 12px; color: #6b7280; margin: 0; }
.mkt-setting-card__body  { padding: 1.25rem; }

/* ── Account option cards ────────────────────────────────────── */
.mkt-account-grid {
    display: flex; flex-direction: column; gap: 10px;
}
.mkt-account-option {
    display: flex; align-items: center; gap: 14px;
    padding: 14px 16px;
    border: 1.5px solid #e5e7eb;
    border-radius: 10px;
    cursor: pointer;
    transition: border-color .15s, background .15s, box-shadow .15s;
    background: #fff;
    position: relative;
}
.mkt-account-option:hover { border-color: #185FA5; background: #f8fbff; }
.mkt-account-option--selected {
    border-color: #185FA5;
    background: #EBF4FF;
    box-shadow: 0 0 0 3px rgba(24,95,165,.08);
}
.mkt-account-radio { position: absolute; opacity: 0; width: 0; height: 0; }
.mkt-account-option__body { flex: 1; }
.mkt-account-option__type { margin-bottom: 4px; }
.mkt-account-option__detail { font-size: 13px; color: #374151; font-weight: 500; margin: 0; }
.mkt-account-option__check {
    width: 24px; height: 24px; border-radius: 50%;
    background: #185FA5; color: #fff;
    display: none; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.mkt-account-option--selected .mkt-account-option__check { display: flex; }

/* ── Badges ──────────────────────────────────────────────────── */
.mkt-badge {
    display: inline-block;
    font-size: 10px; font-weight: 600;
    padding: 2px 8px; border-radius: 99px;
    text-transform: uppercase; letter-spacing: .05em;
}
.mkt-badge--blue  { background: #E6F1FB; color: #185FA5; }
.mkt-badge--green { background: #E1F5EE; color: #0F6E56; }

/* ── Empty state ─────────────────────────────────────────────── */
.mkt-empty-state {
    text-align: center; padding: 2.5rem 1rem; color: #9ca3af;
}
.mkt-empty-state svg { margin-bottom: 10px; color: #d1d5db; }
.mkt-empty-state p   { font-size: 14px; font-weight: 500; color: #374151; margin: 0 0 4px; }
.mkt-empty-state small { font-size: 12px; }

/* ── Current note ────────────────────────────────────────────── */
.mkt-current-note {
    display: flex; align-items: center; gap: 7px;
    font-size: 12px; color: #0F6E56;
    background: #E1F5EE; border-radius: 8px;
    padding: 9px 12px; margin-top: 1rem;
}

/* ── Form actions ────────────────────────────────────────────── */
.mkt-form-actions { display: flex; justify-content: flex-end; }
.mkt-save-btn {
    display: inline-flex; align-items: center; gap: 8px;
    background: #185FA5; color: #fff;
    padding: 10px 22px; border-radius: 9px;
    font-size: 14px; font-weight: 500;
    border: none; cursor: pointer;
    transition: background .15s, transform .12s, box-shadow .15s;
}
.mkt-save-btn:hover {
    background: #0F4A84;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(24,95,165,.25);
}
</style>
@endpush

@push('script')
<script>
// Highlight selected card on click
document.querySelectorAll('.mkt-account-radio').forEach(function (radio) {
    radio.addEventListener('change', function () {
        document.querySelectorAll('.mkt-account-option').forEach(function (opt) {
            opt.classList.remove('mkt-account-option--selected');
        });
        this.closest('.mkt-account-option').classList.add('mkt-account-option--selected');
    });
});
</script>
@endpush