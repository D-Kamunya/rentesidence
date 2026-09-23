@extends('tenant.layouts.app')

@section('content')
<div class="main-content"><div class="page-content"><div class="container-fluid">
    <div class="page-content-wrapper bg-white p-30 radius-20">
        @include('centresidence._design')

        <div class="cs-titlebar">
            <div>
                <h1 class="cs-title">{{ __('Utilities') }}</h1>
                <ol class="cs-crumb"><li><a href="{{ route('tenant.dashboard') }}">{{ __('Dashboard') }}</a></li><li>›</li><li>{{ __('Utilities') }}</li></ol>
            </div>
        </div>

        <p class="cs-muted" style="margin-bottom:20px;max-width:640px;">
            {{ __('Top up prepaid units for the metered utilities on your unit. Units are credited to your wallet as soon as payment is confirmed.') }}
        </p>

        @if ($modules->isEmpty())
            <div class="cs-card"><div class="cs-card__body" style="text-align:center;padding:56px 20px;">
                <i class="ri-flashlight-line" style="font-size:38px;color:var(--amber);"></i>
                <p style="font-size:15px;font-weight:600;color:var(--gray-700);margin:14px 0 4px;">{{ __('No prepaid utilities on your unit yet') }}</p>
                <p class="cs-muted" style="max-width:420px;margin:0 auto;">{{ __('When your landlord activates a metered utility (water, power, gas…), you will be able to buy tokens here.') }}</p>
            </div></div>
        @else
            <div class="util-grid">
                @foreach ($modules as $module)
                    @php
                        $label   = optional($module->tokenConfig)->token_unit_label ?: __('units');
                        $price   = number_format((float) $module->price_per_unit, 2);
                        $balance = rtrim(rtrim(number_format((float) $module->wallet_balance, 4, '.', ','), '0'), '.');
                    @endphp
                    <div class="cs-card util-card">
                        <div class="cs-card__body">
                            <div class="util-card__head">
                                <span class="util-card__icon"><i class="{{ optional($module->module)->icon ?: 'ri-dashboard-3-line' }}"></i></span>
                                <div>
                                    <p class="util-card__name">{{ optional($module->module)->name ?? __('Utility') }}</p>
                                    <p class="cs-muted" style="margin:2px 0 0;">
                                        {{ optional($module->property)->name }}@if ($module->propertyUnit) · {{ optional($module->propertyUnit)->name }}@endif
                                    </p>
                                </div>
                            </div>

                            <div class="util-card__balance">
                                <span class="cs-label" style="margin:0;">{{ __('Balance') }}</span>
                                <span class="util-card__balance-val">{{ $balance === '' ? '0' : $balance }} <small>{{ $label }}</small></span>
                            </div>

                            <p class="cs-muted" style="margin:0;">{{ __('Rate') }}: KES {{ $price }} / {{ \Illuminate\Support\Str::singular($label) }}</p>

                            <form method="POST" action="{{ route('tenant.utilities.purchase') }}" class="util-card__form">
                                @csrf
                                <input type="hidden" name="property_module_id" value="{{ $module->id }}">
                                <div class="util-card__input">
                                    <span>KES</span>
                                    <input type="number" name="amount" min="1" step="any" required
                                           placeholder="{{ __('Amount') }}" inputmode="numeric">
                                </div>
                                <button type="submit" class="cs-btn cs-btn--primary">
                                    <i class="ri-secure-payment-line"></i> {{ __('Buy tokens') }}
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div></div></div>

<style>
    .util-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(300px, 1fr)); gap:18px; }
    .util-card { margin-bottom:0; }
    .util-card .cs-card__body { display:flex; flex-direction:column; gap:14px; }
    .util-card__head { display:flex; align-items:center; gap:12px; }
    .util-card__icon { width:44px; height:44px; border-radius:12px; flex:none; display:inline-flex; align-items:center; justify-content:center;
        background:var(--amber-light); color:var(--amber); font-size:20px; }
    .util-card__name { font-size:14px; font-weight:600; color:var(--gray-900); margin:0; }
    .util-card__balance { display:flex; align-items:baseline; justify-content:space-between; gap:10px;
        padding:12px 14px; border-radius:10px; background:var(--gray-50); border:0.5px solid var(--gray-200); }
    .util-card__balance-val { font-size:20px; font-weight:700; color:var(--gray-900); font-variant-numeric:tabular-nums; }
    .util-card__balance-val small { font-size:12px; font-weight:500; color:var(--gray-500); }
    .util-card__form { display:flex; gap:8px; margin-top:2px; }
    .util-card__input { display:flex; align-items:center; gap:6px; flex:1; border:0.5px solid var(--gray-200); border-radius:7px; padding:0 11px; background:#fff; }
    .util-card__input:focus-within { border-color:var(--blue); box-shadow:0 0 0 3px rgba(24,95,165,.1); }
    .util-card__input span { font-size:11px; color:var(--gray-400); font-weight:600; }
    .util-card__input input { border:none; outline:none; width:100%; padding:9px 0; font-size:13px; color:var(--gray-700); background:transparent; }
    .util-card__form .cs-btn { white-space:nowrap; }
    @media (max-width:540px) { .util-grid { grid-template-columns:1fr; } }
</style>
@endsection
