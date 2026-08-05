@extends('admin.layouts.app')

@section('content')
<div class="main-content"><div class="page-content"><div class="container-fluid">
    <div class="page-content-wrapper bg-white p-30 radius-20">
        @include('admin.centresidence._nav', ['active' => 'infrastructure'])

        @if (session('success')) <div class="cs-alert is-success">{{ session('success') }}</div> @endif
        @if (session('error')) <div class="cs-alert is-danger">{{ session('error') }}</div> @endif

        {{-- Register a gateway --}}
        <div class="cs-card">
            <div class="cs-card__head"><h2 class="cs-card__title">{{ __('Register a gateway') }}</h2></div>
            <div class="cs-card__body">
                <form method="POST" action="{{ route('admin.centresidence.gateways.store') }}">
                    @csrf
                    <div class="row">
                        <div class="col-md-4 cs-field"><label class="cs-label">{{ __('Name') }}</label><input name="name" class="cs-input" required placeholder="Gateway · Riverside"></div>
                        <div class="col-md-4 cs-field"><label class="cs-label">{{ __('EUI') }} ({{ __('optional') }})</label><input name="eui" class="cs-input" placeholder="ChirpStack gateway EUI" style="font-family:monospace;"></div>
                        <div class="col-md-2 cs-field"><label class="cs-label">{{ __('Max devices') }}</label><input type="number" min="1" name="max_devices" class="cs-input" placeholder="∞"></div>
                        <div class="col-md-2 cs-field" style="display:flex;align-items:flex-end;"><button type="submit" class="cs-btn cs-btn--primary" style="width:100%;justify-content:center;">{{ __('Register') }}</button></div>
                        <div class="col-md-4 cs-field"><label class="cs-label">{{ __('Vendor') }} ({{ __('optional') }})</label><input name="vendor" class="cs-input"></div>
                        <div class="col-md-4 cs-field"><label class="cs-label">{{ __('Model') }} ({{ __('optional') }})</label><input name="model" class="cs-input"></div>
                    </div>
                    <small class="cs-muted">{{ __('A gateway is shared infrastructure that many devices bind to. Leave Max devices blank for no limit. EUI can be attached later.') }}</small>
                </form>
            </div>
        </div>

        <div class="cs-card">
            <div class="cs-card__head"><h2 class="cs-card__title">{{ __('Gateways') }}</h2></div>
            <p class="cs-muted" style="padding:0 1.1rem;">{{ __('Edit a gateway inline — attach or correct its EUI (e.g. once the hardware is in hand), set a device capacity, or change status.') }}</p>

            @foreach ($gateways as $g)
                <form id="gwform-{{ $g->id }}" method="POST" action="{{ route('admin.centresidence.gateways.update', $g->id) }}" class="d-none">@csrf @method('PUT')</form>
            @endforeach

            <div class="cs-tablewrap">
                <table class="cs-table">
                    <thead><tr>
                        <th>{{ __('Name') }}</th><th>{{ __('EUI') }}</th><th>{{ __('Devices') }}</th><th>{{ __('Capacity') }}</th>
                        <th>{{ __('Simulated') }}</th><th>{{ __('Status') }}</th><th></th>
                    </tr></thead>
                    <tbody>
                        @forelse ($gateways as $g)
                            <tr>
                                <td><input name="name" form="gwform-{{ $g->id }}" class="cs-input cs-input--sm" value="{{ $g->name }}" style="min-width:140px;"></td>
                                <td><input name="eui" form="gwform-{{ $g->id }}" class="cs-input cs-input--sm" value="{{ $g->eui }}" placeholder="—" style="min-width:160px;font-family:monospace;"></td>
                                <td>{{ $g->devices_count ?? 0 }}</td>
                                <td><input type="number" min="1" name="max_devices" form="gwform-{{ $g->id }}" class="cs-input cs-input--sm" value="{{ $g->max_devices }}" placeholder="∞" style="width:80px;"></td>
                                <td>{{ $g->is_simulated ? __('Yes') : __('No') }}</td>
                                <td>
                                    <select name="status" form="gwform-{{ $g->id }}" class="cs-input cs-input--sm">
                                        @foreach (['active', 'inactive', 'maintenance'] as $st)
                                            <option value="{{ $st }}" @selected($g->status === $st)>{{ ucfirst($st) }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td><button type="submit" form="gwform-{{ $g->id }}" class="cs-btn cs-btn--ghost cs-btn--sm">{{ __('Save') }}</button></td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="cs-empty">{{ __('No gateways yet') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="cs-card">
            <div class="cs-card__head"><h2 class="cs-card__title">{{ __('Infrastructure topology (cost allocation)') }}</h2></div>
            <div class="cs-tablewrap">
                <table class="cs-table">
                    <thead><tr>
                        <th>{{ __('Asset') }}</th><th>{{ __('Owner') }}</th><th>{{ __('Property') }}</th>
                        <th>{{ __('Allocation') }}</th><th>{{ __('Monthly base cost') }}</th><th>{{ __('Owner share (target)') }}</th>
                    </tr></thead>
                    <tbody>
                        @forelse ($topology as $t)
                            <tr>
                                <td>{{ ucfirst($t->infrastructure_type) }} #{{ $t->infrastructure_id }}</td>
                                <td>{{ optional($t->owner)->name ?? '—' }}</td>
                                <td>{{ optional($t->property)->name ?? ('#' . $t->property_id) }}</td>
                                <td>{{ number_format($t->allocation_percentage, 2) }}%</td>
                                <td>KES {{ number_format($t->monthly_base_cost, 2) }}</td>
                                <td class="cs-amt">KES {{ number_format($t->monthly_base_cost * $t->allocation_percentage / 100, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="cs-empty">{{ __('No topology records yet') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div></div></div>
@endsection
