@extends('admin.layouts.app')

@section('content')
<div class="main-content"><div class="page-content"><div class="container-fluid">
    <div class="page-content-wrapper bg-white p-30 radius-20">
        @include('admin.centresidence._nav', ['active' => 'devices'])

        <div class="cs-titlebar"><h1 class="cs-title" style="font-size:18px;">{{ __('Deploy a module') }}</h1></div>
        @if (session('error')) <div class="cs-alert is-danger">{{ session('error') }}</div> @endif

        <p class="cs-muted" style="margin-bottom:18px;">
            {{ __('Provision the hardware for a funded facility or self-financed order. This creates the module on the property, a shared gateway (for metered modules) and one device per unit. With the simulated driver, devices activate immediately so billing runs; with the live driver they wait for the ChirpStack join.') }}
        </p>

        <div class="cs-card" style="max-width:640px;">
            <div class="cs-card__body">
                <form method="POST" action="{{ route('admin.centresidence.deploy.store') }}">
                    @csrf
                    @if ($prefill['self_financed_id'])
                        <input type="hidden" name="self_financed_id" value="{{ $prefill['self_financed_id'] }}">
                    @endif

                    <div class="cs-field">
                        <label class="cs-label">{{ __('Property') }}</label>
                        <select name="property_id" id="dpProperty" class="cs-select" required>
                            <option value="">{{ __('Select property') }}</option>
                            @foreach ($properties as $p)
                                <option value="{{ $p->id }}" data-units="{{ $p->property_units_count }}"
                                    @selected($prefill['property_id'] === $p->id)>
                                    {{ $p->name ?? ('Property #' . $p->id) }} ({{ $p->property_units_count }} {{ __('units') }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="cs-field">
                        <label class="cs-label">{{ __('Module') }}</label>
                        <select name="module_id" class="cs-select" required>
                            <option value="">{{ __('Select module') }}</option>
                            @foreach ($modules as $m)
                                <option value="{{ $m->id }}" @selected($prefill['module_id'] === $m->id)>
                                    {{ $m->name }} ({{ $m->is_metered ? __('metered') : __('non-metered') }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="cs-field" style="max-width:260px;">
                        <label class="cs-label">{{ __('Quantity (units / devices)') }}</label>
                        <div style="display:flex;gap:8px;align-items:center;">
                            <input type="number" name="quantity" id="dpQty" class="cs-input" min="1" value="{{ $prefill['quantity'] }}" required>
                            <button type="button" id="dpAll" class="cs-btn cs-btn--ghost cs-btn--sm" style="white-space:nowrap;">{{ __('All units') }}</button>
                        </div>
                        <small class="cs-muted" id="dpHint"></small>
                    </div>

                    <div class="cs-field">
                        <label class="cs-label">{{ __('Gateway (metered modules)') }}</label>
                        <select name="gateway_id" class="cs-select">
                            <option value="">{{ __('Auto — create / reuse one per property') }}</option>
                            @foreach ($gateways as $g)
                                <option value="{{ $g->id }}">
                                    {{ $g->name }} — {{ $g->devices_count }} {{ __('devices') }}@if ($g->max_devices) / {{ $g->max_devices }} {{ __('max') }}@endif
                                </option>
                            @endforeach
                        </select>
                        <small class="cs-muted">{{ __('Bind the meters to a specific shared gateway, or let the system manage one per property. Ignored for non-metered modules.') }}</small>
                    </div>

                    <button type="submit" class="cs-btn cs-btn--primary">{{ __('Deploy') }}</button>
                    <a href="{{ route('admin.centresidence.devices') }}" class="cs-btn cs-btn--ghost">{{ __('Cancel') }}</a>
                </form>
            </div>
        </div>

        <script>
            (function () {
                var property = document.getElementById('dpProperty'), qty = document.getElementById('dpQty'),
                    all = document.getElementById('dpAll'), hint = document.getElementById('dpHint');
                function units(){ var o = property.options[property.selectedIndex]; return parseInt(o && o.dataset.units || '0', 10); }
                function sync(){
                    var u = units();
                    if (u >= 1) { qty.max = u; if ((parseInt(qty.value,10)||1) > u) qty.value = u; hint.textContent = 'This property has ' + u + ' units.'; }
                    else hint.textContent = property.value ? 'This property has no units — add units before deploying.' : '';
                }
                all.addEventListener('click', function(){ qty.value = Math.max(1, units()); sync(); });
                property.addEventListener('change', sync); qty.addEventListener('input', sync); sync();
            })();
        </script>
    </div>
</div></div></div>
@endsection
