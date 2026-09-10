@extends('owner.layouts.app')

@section('content')
<div class="main-content"><div class="page-content"><div class="container-fluid">
    <div class="page-content-wrapper bg-white p-30 radius-20">
        @include('centresidence._design')

        <div class="cs-titlebar">
            <div>
                <h1 class="cs-title">{{ __('My Devices') }}</h1>
                <ol class="cs-crumb">
                    <li><a href="{{ route('owner.dashboard') }}">{{ __('Dashboard') }}</a></li>
                    <li>›</li>
                    <li>{{ __('My Devices') }}</li>
                </ol>
            </div>
            <a href="{{ route('owner.financing.index') }}" class="cs-btn cs-btn--ghost">{{ __('Add a module') }}</a>
        </div>

        @if (session('success')) <div class="cs-alert is-success">{{ session('success') }}</div> @endif
        @if (session('error')) <div class="cs-alert is-danger">{{ session('error') }}</div> @endif

        <p class="cs-muted" style="margin-bottom:20px;max-width:700px;">
            {{ __('The smart infrastructure installed across your properties — meters and devices, their live status, and the prepaid token activity behind each metered utility. This view is read-only: prepaid supply regulates itself, so there is nothing to switch on or off here.') }}
        </p>

        @if ($modules->isEmpty())
            <div class="cs-card"><div class="cs-card__body" style="text-align:center;padding:56px 20px;">
                <i class="ri-router-line" style="font-size:34px;color:var(--gray-400);"></i>
                <p style="font-size:15px;font-weight:600;color:var(--gray-700);margin:14px 0 4px;">{{ __('No devices installed yet') }}</p>
                <p class="cs-muted" style="max-width:460px;margin:0 auto 18px;">{{ __('When you deploy a smart module — a prepaid water, power or gas meter — the installed devices and their token activity will appear here.') }}</p>
                <a href="{{ route('owner.financing.index') }}" class="cs-btn cs-btn--primary">{{ __('Explore modules') }}</a>
            </div></div>
        @else
            {{-- Headline metrics --}}
            <div class="cs-statgrid">
                <div class="cs-statcard cs-statcard--blue">
                    <span class="cs-statcard__ic"><svg width="22" height="22" viewBox="0 0 24 24"><path d="M4 7l8-4 8 4-8 4-8-4zm0 5l8 4 8-4M4 17l8 4 8-4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" fill="none"/></svg></span>
                    <span class="cs-statcard__body">
                        <span class="cs-statcard__value">{{ number_format($stats['modules_active']) }}</span>
                        <span class="cs-statcard__label">{{ __('Active modules') }}</span>
                    </span>
                </div>
                <div class="cs-statcard cs-statcard--green">
                    <span class="cs-statcard__ic"><svg width="22" height="22" viewBox="0 0 24 24"><path d="M5 12.5a10 10 0 0114 0M8 15.5a6 6 0 018 0M12 18.5h.01" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" fill="none"/></svg></span>
                    <span class="cs-statcard__body">
                        <span class="cs-statcard__value">{{ number_format($stats['devices_online']) }} / {{ number_format($stats['devices_total']) }}</span>
                        <span class="cs-statcard__label">{{ __('Devices online') }}</span>
                    </span>
                </div>
                <div class="cs-statcard cs-statcard--amber">
                    <span class="cs-statcard__ic"><svg width="22" height="22" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.7" fill="none"/><path d="M12 8v8M9.5 10a2.5 2.5 0 012.5-2M12 16a2.5 2.5 0 01-2.5-2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg></span>
                    <span class="cs-statcard__body">
                        <span class="cs-statcard__value">KES {{ number_format($stats['token_revenue'], 0) }}</span>
                        <span class="cs-statcard__label">{{ __('Token revenue (net)') }}</span>
                    </span>
                </div>
                <div class="cs-statcard cs-statcard--purple">
                    <span class="cs-statcard__ic"><svg width="22" height="22" viewBox="0 0 24 24"><path d="M4 8h16v9H4zM4 8l3-4h10l3 4M9 13h6" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" fill="none"/></svg></span>
                    <span class="cs-statcard__body">
                        <span class="cs-statcard__value">{{ rtrim(rtrim(number_format($stats['balance_units'], 2), '0'), '.') ?: '0' }}</span>
                        <span class="cs-statcard__label">{{ __('Prepaid units in circulation') }}</span>
                    </span>
                </div>
            </div>

            {{-- Per-module infrastructure --}}
            <div class="dv-list">
                @foreach ($modules as $module)
                    @php
                        $mod    = $module->module;
                        $color  = $mod ? $mod->displayColor() : '#185FA5';
                        $icon   = $mod ? $mod->displayIcon() : 'ri-dashboard-3-line';
                        $label  = optional($module->tokenConfig)->token_unit_label ?: __('units');
                        $unitFmt = fn ($v) => (rtrim(rtrim(number_format((float) $v, 2), '0'), '.') ?: '0');
                        $isMetered = optional($mod)->is_metered;
                    @endphp
                    <div class="cs-card dv-mod">
                        <div class="cs-card__body">
                            <div class="dv-mod__head">
                                <span class="dv-mod__icon" style="background:{{ $color }}1a;color:{{ $color }};"><i class="{{ $icon }}"></i></span>
                                <div class="dv-mod__id">
                                    <p class="dv-mod__name">{{ optional($mod)->name ?? __('Module') }}</p>
                                    <p class="cs-muted" style="margin:2px 0 0;">
                                        {{ optional($module->property)->name }}@if ($module->propertyUnit) · {{ optional($module->propertyUnit)->name }}@endif
                                    </p>
                                </div>
                                @php
                                    $ms = $module->status;
                                    $msClass = $ms === \App\Centresidence\Models\PropertyModule::STATUS_ACTIVE ? 'is-on' : ($ms === \App\Centresidence\Models\PropertyModule::STATUS_SUSPENDED ? 'is-warn' : 'is-off');
                                @endphp
                                <span class="dv-pill {{ $msClass }}">{{ __(ucfirst($ms)) }}</span>
                            </div>

                            {{-- Token economics (metered modules only) --}}
                            @if ($isMetered)
                                <div class="dv-econ">
                                    <div class="dv-econ__cell">
                                        <span class="dv-econ__label">{{ __('Balance') }}</span>
                                        <span class="dv-econ__val">{{ $unitFmt($module->view_balance_units) }} <small>{{ $label }}</small></span>
                                    </div>
                                    <div class="dv-econ__cell">
                                        <span class="dv-econ__label">{{ __('Purchased') }}</span>
                                        <span class="dv-econ__val">{{ $unitFmt($module->view_purchased_units) }} <small>{{ $label }}</small></span>
                                    </div>
                                    <div class="dv-econ__cell">
                                        <span class="dv-econ__label">{{ __('Consumed') }}</span>
                                        <span class="dv-econ__val">{{ $unitFmt($module->view_consumed_units) }} <small>{{ $label }}</small></span>
                                    </div>
                                    <div class="dv-econ__cell">
                                        <span class="dv-econ__label">{{ __('Your revenue') }}</span>
                                        <span class="dv-econ__val">KES {{ number_format($module->view_revenue_net, 0) }} <small>{{ trans_choice(':n top-up|:n top-ups', $module->view_purchase_count, ['n' => $module->view_purchase_count]) }}</small></span>
                                    </div>
                                </div>

                                {{-- Owner-set retail tariff. You set your own rate; the floor is enforced (it must
                                     cover our commission); the ceiling is advisory only — you are solely liable for
                                     regulatory compliance. --}}
                                @php $adv = $module->view_advisory ?? null; @endphp
                                <details class="dv-tariff" style="margin-top:12px;border-top:1px solid rgba(120,140,170,.16);padding-top:10px;">
                                    <summary style="cursor:pointer;font-size:13px;color:#48566A;">
                                        {{ __('Your tariff') }}: <strong>KES {{ number_format($module->view_price_per_unit ?? 0, 2) }}</strong> / {{ $label }}
                                        <span style="color:#185FA5;font-weight:600;">· {{ __('Edit') }}</span>
                                    </summary>
                                    <form method="POST" action="{{ route('owner.devices.tariff.update') }}" style="margin-top:12px;display:flex;flex-direction:column;gap:8px;max-width:360px;">
                                        @csrf
                                        <input type="hidden" name="property_module_id" value="{{ $module->id }}">
                                        <label class="cs-label" style="font-size:12px;">{{ __('Units per KES 1') }} — {{ $label }} {{ __('a tenant gets per KES 1') }}</label>
                                        <input type="number" step="0.0001" min="0.0001" name="units_per_kes" required
                                               value="{{ rtrim(rtrim(number_format((float) ($module->view_units_per_kes ?? 0), 4, '.', ''), '0'), '.') }}"
                                               class="cs-input"
                                               oninput="var p=this.form.querySelector('[data-preview]');p.textContent=this.value>0?('≈ KES '+(1/this.value).toFixed(2)+' / {{ $label }}'):'';">
                                        <p class="cs-muted" data-preview style="margin:0;font-size:12px;"></p>

                                        {{-- Cost-basis helper (new to metering): bulk cost + margin → suggested tariff. --}}
                                        <details style="margin:2px 0;">
                                            <summary style="cursor:pointer;font-size:12px;color:#185FA5;">{{ __('Help me price this') }}</summary>
                                            <div style="display:flex;flex-direction:column;gap:6px;margin-top:8px;padding:10px;background:#F7FAFE;border:1px solid #DCE6F1;border-radius:8px;">
                                                <label style="font-size:11.5px;color:#48566A;">{{ __('Your cost per :label (KES)', ['label' => $label]) }}
                                                    <input type="number" step="0.0001" min="0" data-cost class="cs-input" style="margin-top:3px;" placeholder="{{ __('e.g. bulk rate ÷ units') }}"></label>
                                                <label style="font-size:11.5px;color:#48566A;">{{ __('Desired margin (%)') }}
                                                    <input type="number" step="1" min="0" value="20" data-margin class="cs-input" style="margin-top:3px;"></label>
                                                @if (($module->view_commission ?? 0) > 0)
                                                    <p style="margin:0;font-size:11px;color:#8A97A8;">{{ __('Our commission: KES :c per :label (already deducted from your revenue).', ['c' => number_format($module->view_commission, 4), 'label' => $label]) }}</p>
                                                @endif
                                                <button type="button" style="align-self:flex-start;font-size:12px;padding:6px 12px;background:#0F2A4A;color:#fff;border:0;border-radius:7px;cursor:pointer;"
                                                    data-commission="{{ $module->view_commission ?? 0 }}"
                                                    onclick="(function(b){var f=b.closest('form');var cost=parseFloat(f.querySelector('[data-cost]').value)||0;var m=parseFloat(f.querySelector('[data-margin]').value)||0;var comm=parseFloat(b.getAttribute('data-commission'))||0;var price=(cost+comm)*(1+m/100);if(price>0){var u=f.querySelector('[name=units_per_kes]');u.value=(1/price).toFixed(4);u.dispatchEvent(new Event('input'));}})(this)">{{ __('Suggest tariff') }}</button>
                                                <p style="margin:0;font-size:10.5px;color:#8A97A8;">{{ __('A guide only. You set the final rate and are responsible for compliance.') }}</p>
                                            </div>
                                        </details>

                                        @if ($adv)
                                            <p style="margin:2px 0 0;font-size:11.5px;line-height:1.5;padding:8px 10px;border-radius:8px;{{ $adv['level'] === 'warning' ? 'background:#FEF9EE;border:1px solid #FAC775;color:#854F0B;' : 'background:#F4F6F8;border:1px solid #D8DEE6;color:#48566A;' }}">{{ $adv['message'] }}</p>
                                        @endif
                                        <div><button type="submit" style="font-size:13px;padding:8px 18px;background:#185FA5;color:#fff;border:0;border-radius:8px;cursor:pointer;font-weight:600;">{{ __('Save tariff') }}</button></div>
                                    </form>
                                </details>
                            @endif

                            {{-- Installed devices --}}
                            <div class="dv-devs">
                                <p class="dv-devs__title">{{ __('Installed devices') }} <span class="dv-devs__count">{{ $module->devices->count() }}</span></p>
                                @if ($module->devices->isEmpty())
                                    <p class="cs-muted" style="margin:0;font-size:12.5px;">{{ __('No devices provisioned on this module yet.') }}</p>
                                @else
                                    <div class="dv-dev-rows">
                                        @foreach ($module->devices as $device)
                                            @php
                                                $online = $device->status === \App\Centresidence\Models\Device::STATUS_ACTIVE
                                                    && $device->last_seen_at
                                                    && $device->last_seen_at->greaterThanOrEqualTo(now()->subMinutes($onlineWindow));
                                                if ($device->status === \App\Centresidence\Models\Device::STATUS_ACTIVE) {
                                                    $devPill = $online ? ['is-on', __('Online')] : ['is-warn', __('Offline')];
                                                } elseif ($device->status === \App\Centresidence\Models\Device::STATUS_PROVISIONING) {
                                                    $devPill = ['is-info', __('Provisioning')];
                                                } elseif ($device->status === \App\Centresidence\Models\Device::STATUS_DECOMMISSIONED) {
                                                    $devPill = ['is-off', __('Decommissioned')];
                                                } else {
                                                    $devPill = ['is-off', __('Inactive')];
                                                }
                                            @endphp
                                            <a href="{{ route('owner.devices.show', $device->id) }}" class="dv-dev" title="{{ __('View activity') }}">
                                                <span class="dv-dev__dot {{ $devPill[0] }}"></span>
                                                <div class="dv-dev__id">
                                                    <span class="dv-dev__name">
                                                        {{ $device->cleanName() }}
                                                        @if ($device->unitName())
                                                            <span class="dv-unit">{{ __('Unit') }} {{ $device->unitName() }}</span>
                                                        @else
                                                            <span class="dv-unit dv-unit--none">{{ __('Unassigned unit') }}</span>
                                                        @endif
                                                    </span>
                                                    @if ($device->dev_eui)<span class="dv-dev__eui">{{ $device->dev_eui }}</span>@endif
                                                </div>
                                                <div class="dv-dev__meta">
                                                    <span class="dv-pill {{ $devPill[0] }}">{{ $devPill[1] }}</span>
                                                    <span class="dv-dev__seen">
                                                        @if ($device->last_seen_at)
                                                            {{ __('Last seen') }} {{ $device->last_seen_at->diffForHumans() }}
                                                        @else
                                                            {{ __('Never reported') }}
                                                        @endif
                                                    </span>
                                                    <i class="ri-arrow-right-s-line dv-dev__chev"></i>
                                                </div>
                                            </a>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div></div></div>

<style>
    .dv-list { display:flex; flex-direction:column; gap:16px; }
    .dv-mod .cs-card__body { display:flex; flex-direction:column; gap:18px; }

    .dv-mod__head { display:flex; align-items:center; gap:14px; }
    .dv-mod__icon { width:44px; height:44px; flex-shrink:0; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:22px; }
    .dv-mod__id { flex:1; min-width:0; }
    .dv-mod__name { font-size:15px; font-weight:700; color:var(--gray-900); margin:0; }

    .dv-econ { display:grid; grid-template-columns:repeat(4, 1fr); gap:10px; }
    .dv-econ__cell { background:var(--gray-50, #F7F9FC); border:1px solid var(--gray-150, #eceff4); border-radius:12px; padding:12px 14px; display:flex; flex-direction:column; gap:4px; }
    .dv-econ__label { font-size:10.5px; font-weight:600; letter-spacing:.4px; text-transform:uppercase; color:var(--gray-500); }
    .dv-econ__val { font-size:16px; font-weight:700; color:var(--gray-900); font-variant-numeric:tabular-nums; }
    .dv-econ__val small { font-size:11px; font-weight:500; color:var(--gray-500); }

    .dv-devs__title { font-size:12px; font-weight:700; letter-spacing:.3px; text-transform:uppercase; color:var(--gray-600); margin:0 0 10px; display:flex; align-items:center; gap:8px; }
    .dv-devs__count { background:var(--gray-100, #eef1f6); color:var(--gray-600); border-radius:99px; font-size:11px; font-weight:600; padding:1px 8px; letter-spacing:0; }
    .dv-dev-rows { display:flex; flex-direction:column; gap:8px; }
    .dv-dev { display:flex; align-items:center; gap:12px; padding:10px 14px; border:1px solid var(--gray-150, #eceff4); border-radius:12px; background:#fff; text-decoration:none; color:inherit; transition:border-color .15s ease, box-shadow .15s ease, background .15s ease; }
    .dv-dev:hover { border-color:#BAD3F5; background:#FaFcff; box-shadow:0 2px 10px rgba(24,95,165,.06); color:inherit; }
    .dv-dev__chev { font-size:18px; color:var(--gray-400); flex-shrink:0; }
    .dv-dev:hover .dv-dev__chev { color:#1D5FB8; }
    .dv-dev__dot { width:9px; height:9px; border-radius:50%; flex-shrink:0; }
    .dv-dev__id { flex:1; min-width:0; display:flex; flex-direction:column; gap:1px; }
    .dv-dev__name { font-size:13.5px; font-weight:600; color:var(--gray-900); display:inline-flex; align-items:center; gap:8px; flex-wrap:wrap; }
    .dv-unit { font-size:10.5px; font-weight:600; letter-spacing:.3px; padding:2px 8px; border-radius:6px; background:#EEF4FE; color:#1D5FB8; }
    .dv-unit--none { background:#FEF3F2; color:#B4432E; }
    .dv-dev__eui { font-size:11px; color:var(--gray-500); font-family:ui-monospace, SFMono-Regular, Menlo, monospace; }
    .dv-dev__meta { display:flex; align-items:center; gap:12px; flex-shrink:0; }
    .dv-dev__seen { font-size:11.5px; color:var(--gray-500); white-space:nowrap; }

    .dv-pill { display:inline-flex; align-items:center; font-size:11px; font-weight:600; padding:3px 10px; border-radius:99px; white-space:nowrap; }
    .dv-pill.is-on   { background:#F0F9F4; color:#0B7A55; border:1px solid #9FE1CB; }
    .dv-pill.is-warn { background:#FEF6E7; color:#8A5A00; border:1px solid #F3D48A; }
    .dv-pill.is-info { background:#EEF4FE; color:#1D5FB8; border:1px solid #BAD3F5; }
    .dv-pill.is-off  { background:#F3F4F6; color:#5B6472; border:1px solid #DEE2E8; }
    .dv-dev__dot.is-on   { background:#12A56A; box-shadow:0 0 0 3px #12a56a26; }
    .dv-dev__dot.is-warn { background:#E7A100; box-shadow:0 0 0 3px #e7a10026; }
    .dv-dev__dot.is-info { background:#2E77DA; box-shadow:0 0 0 3px #2e77da26; }
    .dv-dev__dot.is-off  { background:#9AA3AF; }

    @media (max-width: 720px) {
        .dv-econ { grid-template-columns:repeat(2, 1fr); }
        .dv-dev { flex-wrap:wrap; }
        .dv-dev__meta { width:100%; justify-content:space-between; }
    }
</style>
@endsection
