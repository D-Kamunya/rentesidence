@extends('admin.layouts.app')

@section('content')
<div class="main-content"><div class="page-content"><div class="container-fluid">
    <div class="page-content-wrapper bg-white p-30 radius-20">
        @include('admin.centresidence._nav', ['active' => 'devices'])

        @if (session('success')) <div class="cs-alert is-success">{{ session('success') }}</div> @endif
        @if (session('error')) <div class="cs-alert is-danger">{{ session('error') }}</div> @endif

        <div class="cs-titlebar" style="align-items:center;">
            <p class="cs-muted" style="margin:0;">{{ __('Provisioned devices. Attach each meter/lock\'s real DevEUI as it is physically fitted; report a fault by its Ref #.') }}</p>
            <a href="{{ route('admin.centresidence.deploy') }}" class="cs-btn cs-btn--primary cs-btn--sm">{{ __('Deploy a module') }}</a>
        </div>

        {{-- Search & filter — find a device fast among many (by Ref #, DevEUI, name; or filter by property/gateway/status). --}}
        <form method="GET" action="{{ route('admin.centresidence.devices') }}" class="cs-card" style="margin-bottom:16px;">
            <div class="cs-card__body" style="display:flex;flex-wrap:wrap;gap:10px;align-items:flex-end;">
                <div style="flex:2;min-width:200px;">
                    <label class="cs-label">{{ __('Search') }}</label>
                    <input type="text" name="q" class="cs-input" value="{{ $filters['q'] ?? '' }}" placeholder="{{ __('Ref #, DevEUI or device name…') }}">
                </div>
                <div style="flex:1;min-width:150px;">
                    <label class="cs-label">{{ __('Property') }}</label>
                    <select name="property_id" class="cs-select">
                        <option value="">{{ __('All properties') }}</option>
                        @foreach ($properties as $p)
                            <option value="{{ $p->id }}" @selected(($filters['property_id'] ?? null) == $p->id)>{{ $p->name ?? ('Property #' . $p->id) }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="flex:1;min-width:140px;">
                    <label class="cs-label">{{ __('Gateway') }}</label>
                    <select name="gateway_id" class="cs-select">
                        <option value="">{{ __('All gateways') }}</option>
                        @foreach ($gateways as $g)
                            <option value="{{ $g->id }}" @selected(($filters['gateway_id'] ?? null) == $g->id)>{{ $g->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="flex:1;min-width:130px;">
                    <label class="cs-label">{{ __('Status') }}</label>
                    <select name="status" class="cs-select">
                        <option value="">{{ __('Any status') }}</option>
                        @foreach (['provisioning', 'active', 'inactive', 'decommissioned'] as $st)
                            <option value="{{ $st }}" @selected(($filters['status'] ?? null) === $st)>{{ ucfirst($st) }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="display:flex;gap:6px;">
                    <button type="submit" class="cs-btn cs-btn--primary">{{ __('Search') }}</button>
                    <a href="{{ route('admin.centresidence.devices') }}" class="cs-btn cs-btn--ghost">{{ __('Reset') }}</a>
                </div>
            </div>
        </form>

        <p class="cs-muted" style="margin-bottom:10px;">{{ $devices->total() }} {{ trans_choice('device|devices', $devices->total()) }}</p>

        {{-- Per-row forms referenced by inputs via the HTML5 `form` attribute (valid inside tables). --}}
        @foreach ($devices as $d)
            <form id="devform-{{ $d->id }}" method="POST" action="{{ route('admin.centresidence.devices.update', $d->id) }}" class="d-none">@csrf @method('PUT')</form>
        @endforeach

        <div class="cs-tablewrap">
            <table class="cs-table">
                <thead><tr>
                    <th>{{ __('Ref #') }}</th><th>{{ __('Device') }}</th><th>{{ __('Module') }}</th><th>{{ __('Property') }}</th>
                    <th>{{ __('Unit') }}</th><th>{{ __('DevEUI') }}</th><th>{{ __('Gateway') }}</th><th>{{ __('Status') }}</th><th></th>
                </tr></thead>
                <tbody>
                    @forelse ($devices as $d)
                        <tr>
                            <td style="font-weight:600;color:var(--gray-900);white-space:nowrap;">#{{ $d->id }}</td>
                            <td><input type="text" name="name" form="devform-{{ $d->id }}" class="cs-input cs-input--sm" value="{{ $d->name }}" style="min-width:140px;"></td>
                            <td>{{ optional(optional($d->propertyModule)->module)->name ?? '—' }}</td>
                            <td>{{ optional(optional($d->propertyModule)->property)->name ?? '—' }}</td>
                            @php $pid = optional($d->propertyModule)->property_id; $units = $unitsByProperty[$pid] ?? collect(); @endphp
                            <td>
                                <select name="property_unit_id" form="devform-{{ $d->id }}" class="cs-input cs-input--sm" style="min-width:120px;" title="{{ __('Which unit this meter serves — drives token & consumption attribution') }}">
                                    <option value="">{{ __('— unassigned —') }}</option>
                                    @foreach ($units as $u)
                                        <option value="{{ $u->id }}" @selected($d->property_unit_id == $u->id)>{{ $u->unit_name }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td style="min-width:180px;">
                                <input type="text" name="dev_eui" form="devform-{{ $d->id }}" class="cs-input cs-input--sm" value="{{ $d->dev_eui }}" placeholder="{{ __('DevEUI') }}" style="width:100%;font-family:monospace;">
                                <input type="text" name="app_key" form="devform-{{ $d->id }}" class="cs-input cs-input--sm" value="" autocomplete="off" placeholder="{{ ($d->metadata['app_key'] ?? null) ? __('AppKey set — blank keeps it') : __('OTAA AppKey (32 hex)') }}" style="width:100%;font-family:monospace;margin-top:4px;" title="{{ __('Enter the meter’s OTAA AppKey to register it on the network. Never shown once saved.') }}">
                            </td>
                            <td>
                                <select name="gateway_id" form="devform-{{ $d->id }}" class="cs-input cs-input--sm" style="min-width:150px;">
                                    <option value="">{{ __('— none —') }}</option>
                                    @foreach ($gateways as $g)
                                        <option value="{{ $g->id }}" @selected($d->gateway_id == $g->id)>{{ $g->name }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <select name="status" form="devform-{{ $d->id }}" class="cs-input cs-input--sm">
                                    @foreach (['provisioning', 'active', 'inactive', 'decommissioned'] as $st)
                                        <option value="{{ $st }}" @selected($d->status === $st)>{{ ucfirst($st) }}</option>
                                    @endforeach
                                </select>
                                @if ($d->is_simulated)
                                    <div class="cs-muted" style="font-size:10.5px;margin-top:2px;" title="{{ __('Test device — auto-activated by the simulated ChirpStack driver, not real hardware. Unrelated to a SIM card.') }}">{{ __('simulated (test)') }}</div>
                                @endif
                            </td>
                            <td><button type="submit" form="devform-{{ $d->id }}" class="cs-btn cs-btn--ghost cs-btn--sm">{{ __('Save') }}</button></td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="cs-empty">{{ ($filters['q'] ?? $filters['property_id'] ?? $filters['gateway_id'] ?? $filters['status'] ?? null) ? __('No devices match your search.') : __('No devices provisioned yet.') }} <a href="{{ route('admin.centresidence.deploy') }}">{{ __('Deploy a module') }}</a>.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top:16px;">{{ $devices->withQueryString()->links() }}</div>
    </div>
</div></div></div>
@endsection
