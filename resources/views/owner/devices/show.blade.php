@extends('owner.layouts.app')

@section('content')
@php
    $mod   = optional($device->propertyModule)->module;
    $pm    = $device->propertyModule;
    $color = $mod ? $mod->displayColor() : '#185FA5';
    $icon  = $mod ? $mod->displayIcon() : 'ri-dashboard-3-line';
    $unitFmt = fn ($v) => (rtrim(rtrim(number_format((float) $v, 2), '0'), '.') ?: '0');

    if ($device->status === \App\Centresidence\Models\Device::STATUS_ACTIVE) {
        $devPill = $isOnline ? ['is-on', __('Online')] : ['is-warn', __('Offline')];
    } elseif ($device->status === \App\Centresidence\Models\Device::STATUS_PROVISIONING) {
        $devPill = ['is-info', __('Provisioning')];
    } elseif ($device->status === \App\Centresidence\Models\Device::STATUS_DECOMMISSIONED) {
        $devPill = ['is-off', __('Decommissioned')];
    } else {
        $devPill = ['is-off', __('Inactive')];
    }
@endphp
<div class="main-content"><div class="page-content"><div class="container-fluid">
    <div class="page-content-wrapper bg-white p-30 radius-20">
        @include('centresidence._design')

        <div class="cs-titlebar">
            <div>
                <h1 class="cs-title">{{ $device->cleanName() }}</h1>
                <ol class="cs-crumb">
                    <li><a href="{{ route('owner.dashboard') }}">{{ __('Dashboard') }}</a></li>
                    <li>›</li>
                    <li><a href="{{ route('owner.devices.index') }}">{{ __('My Devices') }}</a></li>
                    <li>›</li>
                    <li>{{ $device->cleanName() }}</li>
                </ol>
            </div>
            <a href="{{ route('owner.devices.index') }}" class="cs-btn cs-btn--ghost">{{ __('Back to devices') }}</a>
        </div>

        {{-- Device header --}}
        <div class="cs-card" style="margin-bottom:16px;">
            <div class="cs-card__body dvs-head">
                <span class="dvs-head__icon" style="background:{{ $color }}1a;color:{{ $color }};"><i class="{{ $icon }}"></i></span>
                <div class="dvs-head__id">
                    <div class="dvs-head__top">
                        <p class="dvs-head__name">{{ $device->cleanName() }}</p>
                        @if ($device->unitName())
                            <span class="dv-unit">{{ __('Unit') }} {{ $device->unitName() }}</span>
                        @else
                            <span class="dv-unit dv-unit--none">{{ __('Unassigned unit') }}</span>
                        @endif
                        <span class="dv-pill {{ $devPill[0] }}">{{ $devPill[1] }}</span>
                    </div>
                    <p class="cs-muted" style="margin:2px 0 0;">
                        {{ optional($mod)->name ?? __('Module') }} · {{ optional($pm)->property->name ?? '' }}
                        @if ($device->dev_eui) · <span class="dvs-head__eui">{{ $device->dev_eui }}</span>@endif
                    </p>
                    <p class="cs-muted" style="margin:6px 0 0;font-size:12px;">
                        @if ($device->last_seen_at)
                            {{ __('Last reported') }} {{ $device->last_seen_at->diffForHumans() }} ({{ $device->last_seen_at->format('d M Y, H:i') }})
                        @else
                            {{ __('Has not reported yet') }}
                        @endif
                    </p>
                </div>
            </div>
        </div>

        {{-- Per-device summary --}}
        <div class="cs-statgrid" style="margin-bottom:16px;">
            <div class="cs-statcard cs-statcard--amber">
                <span class="cs-statcard__ic"><svg width="22" height="22" viewBox="0 0 24 24"><path d="M13 3L4 14h6l-1 7 9-11h-6z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round" fill="none"/></svg></span>
                <span class="cs-statcard__body">
                    <span class="cs-statcard__value">{{ $unitFmt($summary['consumed']) }}</span>
                    <span class="cs-statcard__label">{{ __('Consumed') }} ({{ $unitLabel }})</span>
                </span>
            </div>
            <div class="cs-statcard cs-statcard--green">
                <span class="cs-statcard__ic"><svg width="22" height="22" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg></span>
                <span class="cs-statcard__body">
                    <span class="cs-statcard__value">{{ $unitFmt($summary['units_loaded']) }}</span>
                    <span class="cs-statcard__label">{{ __('Units loaded') }} ({{ $unitLabel }})</span>
                </span>
            </div>
            <div class="cs-statcard cs-statcard--blue">
                <span class="cs-statcard__ic"><svg width="22" height="22" viewBox="0 0 24 24"><path d="M4 8h16v9H4zM4 8l3-4h10l3 4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" fill="none"/></svg></span>
                <span class="cs-statcard__body">
                    <span class="cs-statcard__value">{{ number_format($summary['load_count']) }}</span>
                    <span class="cs-statcard__label">{{ __('Token loads') }}</span>
                </span>
            </div>
        </div>

        {{-- Activity timeline --}}
        <div class="cs-card">
            <div class="cs-card__head" style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;">
                <h2 class="cs-card__title">{{ __('Activity') }}</h2>
                <span class="cs-muted" style="font-size:11.5px;">{{ __('Token loads, consumption and meter readings for this device') }}</span>
            </div>
            <div class="cs-card__body">
                @if ($timeline->isEmpty())
                    <div style="text-align:center;padding:44px 20px;">
                        <i class="ri-pulse-line" style="font-size:30px;color:var(--gray-400);"></i>
                        <p style="font-size:14px;font-weight:600;color:var(--gray-700);margin:12px 0 4px;">{{ __('No activity recorded yet') }}</p>
                        <p class="cs-muted" style="max-width:440px;margin:0 auto;">{{ __('Token loads, usage and meter readings for this device will appear here as it reports in.') }}</p>
                    </div>
                @else
                    <div class="dvs-feed">
                        @foreach ($timeline as $e)
                            @php
                                $kc = ['load' => 'is-on', 'use' => 'is-warn', 'reading' => 'is-info'][$e['kind']] ?? 'is-off';
                                $ki = ['load' => 'ri-add-circle-line', 'use' => 'ri-drop-line', 'reading' => 'ri-signal-tower-line'][$e['kind']] ?? 'ri-circle-line';
                            @endphp
                            <div class="dvs-feed__row">
                                <span class="dvs-feed__ic {{ $kc }}"><i class="{{ $ki }}"></i></span>
                                <div class="dvs-feed__body">
                                    <p class="dvs-feed__title">{{ $e['sign'] }}{{ $e['title'] }}</p>
                                    <p class="dvs-feed__sub">{{ $e['sub'] }}</p>
                                </div>
                                <div class="dvs-feed__meta">
                                    @if (!empty($e['status']))<span class="dv-pill {{ in_array($e['status'], ['acked','sent']) ? 'is-on' : ($e['status'] === 'failed' ? 'is-warn' : 'is-info') }}">{{ ucfirst($e['status']) }}</span>@endif
                                    <span class="dvs-feed__time">{{ optional($e['at'])->diffForHumans() ?? '—' }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <p class="cs-muted" style="margin:14px 0 0;font-size:11.5px;">{{ __('Showing the most recent activity.') }}</p>
                @endif
            </div>
        </div>
    </div>
</div></div></div>

<style>
    .dvs-head { display:flex; align-items:center; gap:16px; }
    .dvs-head__icon { width:52px; height:52px; flex-shrink:0; border-radius:14px; display:flex; align-items:center; justify-content:center; font-size:26px; }
    .dvs-head__id { flex:1; min-width:0; }
    .dvs-head__top { display:flex; align-items:center; gap:10px; flex-wrap:wrap; }
    .dvs-head__name { font-size:17px; font-weight:700; color:var(--gray-900); margin:0; }
    .dvs-head__eui { font-family:ui-monospace, SFMono-Regular, Menlo, monospace; }
    .dv-unit { font-size:11px; font-weight:600; letter-spacing:.3px; padding:2px 9px; border-radius:6px; background:#EEF4FE; color:#1D5FB8; }
    .dv-unit--none { background:#FEF3F2; color:#B4432E; }

    .dvs-feed { display:flex; flex-direction:column; }
    .dvs-feed__row { display:flex; align-items:center; gap:13px; padding:13px 2px; border-bottom:1px solid var(--gray-100, #eef1f6); }
    .dvs-feed__row:last-child { border-bottom:none; }
    .dvs-feed__ic { width:34px; height:34px; flex-shrink:0; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:17px; }
    .dvs-feed__ic.is-on   { background:#F0F9F4; color:#0B7A55; }
    .dvs-feed__ic.is-warn { background:#FEF6E7; color:#8A5A00; }
    .dvs-feed__ic.is-info { background:#EEF4FE; color:#1D5FB8; }
    .dvs-feed__ic.is-off  { background:#F3F4F6; color:#5B6472; }
    .dvs-feed__body { flex:1; min-width:0; }
    .dvs-feed__title { font-size:13.5px; font-weight:600; color:var(--gray-900); margin:0; }
    .dvs-feed__sub { font-size:12px; color:var(--gray-500); margin:2px 0 0; }
    .dvs-feed__meta { display:flex; align-items:center; gap:10px; flex-shrink:0; }
    .dvs-feed__time { font-size:11.5px; color:var(--gray-500); white-space:nowrap; }

    .dv-pill { display:inline-flex; align-items:center; font-size:11px; font-weight:600; padding:3px 10px; border-radius:99px; white-space:nowrap; }
    .dv-pill.is-on   { background:#F0F9F4; color:#0B7A55; border:1px solid #9FE1CB; }
    .dv-pill.is-warn { background:#FEF6E7; color:#8A5A00; border:1px solid #F3D48A; }
    .dv-pill.is-info { background:#EEF4FE; color:#1D5FB8; border:1px solid #BAD3F5; }
    .dv-pill.is-off  { background:#F3F4F6; color:#5B6472; border:1px solid #DEE2E8; }

    @media (max-width: 620px) {
        .dvs-feed__row { flex-wrap:wrap; }
        .dvs-feed__meta { width:100%; justify-content:flex-end; padding-left:47px; }
    }
</style>
@endsection
