@include('centresidence._design')

@php
    $tabs = [
        'index'          => ['Overview', 'ri-dashboard-line'],
        'partners'       => ['Finance Partners', 'ri-bank-line'],
        'applications'   => ['Applications', 'ri-file-list-3-line'],
        'facilities'     => ['Facilities', 'ri-funds-line'],
        'defaults'       => ['Defaults', 'ri-error-warning-line'],
        'revenue'        => ['Commission & Revenue', 'ri-coins-line'],
        'modules'        => ['Modules & Costs', 'ri-stack-line'],
        'self-financed'  => ['Self-financed', 'ri-hand-coin-line'],
        'devices'        => ['Devices', 'ri-cpu-line'],
        'infrastructure' => ['Infrastructure', 'ri-router-line'],
        'integrations'   => ['Integrations', 'ri-plug-line'],
    ];
    $active = $active ?? 'index';
@endphp

<div class="cs-titlebar">
    <div>
        <h1 class="cs-title">{{ $pageTitle ?? 'Centresidence' }}</h1>
        <ol class="cs-crumb">
            <li><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
            <li>›</li>
            <li>{{ __('Centresidence') }}</li>
        </ol>
    </div>
</div>

<div class="cs-tabs">
    @foreach ($tabs as $key => [$label, $icon])
        <a href="{{ route('admin.centresidence.' . $key) }}" class="cs-tab {{ $active === $key ? 'is-active' : '' }}">
            <i class="{{ $icon }}"></i> {{ __($label) }}
        </a>
    @endforeach
</div>
