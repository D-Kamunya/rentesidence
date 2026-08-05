@extends('owner.layouts.app')

@section('content')
<div class="main-content"><div class="page-content"><div class="container-fluid">
    <div class="page-content-wrapper bg-white p-30 radius-20">
        @include('centresidence._design')
        <div class="cs-titlebar"><h1 class="cs-title">{{ __('Self-finance a module') }}</h1></div>

        <div class="cs-card" style="max-width:720px;">
            <div class="cs-card__head"><h2 class="cs-card__title">{{ optional($item->module)->name }}</h2></div>
            <div class="cs-card__body">
                <p class="cs-muted">
                    {{ __('Hardware') }}: KES {{ number_format($item->unit_price, 2) }} / {{ $item->unit_label ?? __('unit') }} ·
                    {{ __('Installation') }}: KES {{ number_format($item->installation_cost, 2) }} / {{ $item->unit_label ?? __('unit') }}
                </p>
                <p style="font-size:13px;color:var(--gray-700);margin:8px 0 18px;">
                    {{ __('Self-financing means you pay for the module and installation yourself — no finance partner, no rent deduction. Good if you do not qualify for partner financing or prefer to own it outright from day one.') }}
                </p>

                @if (session('error')) <div class="cs-alert is-danger">{{ session('error') }}</div> @endif

                @if (! $hasModuleBillingRail)
                    <div class="cs-alert is-amber">
                        {{ __('Smart modules carry a monthly platform & gateway cost. A free plan can\'t bill that, so you\'ll need a subscription or transaction plan before deploying one — even when you fund the hardware yourself.') }}
                        <a href="{{ route('owner.subscription.index') }}" style="font-weight:600;text-decoration:underline;">{{ __('Choose a plan') }}</a>
                    </div>
                @elseif ($properties->isEmpty())
                    <div class="cs-alert is-amber">{{ __('You need a property before you can self-finance a module.') }}</div>
                @else
                    <form method="POST" action="{{ route('owner.financing.self-finance.store') }}">
                        @csrf
                        <input type="hidden" name="catalogue_item_id" value="{{ $item->id }}">
                        <div class="cs-field">
                            <label class="cs-label">{{ __('Property') }}</label>
                            <select name="property_id" id="sfProperty" class="cs-select" required>
                                @foreach ($properties as $property)
                                    <option value="{{ $property->id }}" data-units="{{ $property->property_units_count }}" data-show="{{ route('owner.property.show', $property->id) }}">
                                        {{ $property->name ?? ('Property #' . $property->id) }}
                                        ({{ $property->property_units_count }} {{ __('units') }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="cs-alert is-amber" id="sfNoUnits" style="display:none;">
                            {{ __('This property has no units yet.') }}
                            <a id="sfAddUnits" href="#" style="font-weight:600;text-decoration:underline;">{{ __('Add units first') }}</a>
                            {{ __('— a property needs units before you can deploy modules.') }}
                        </div>
                        <div class="cs-field" style="max-width:320px;">
                            <label class="cs-label">{{ __('Quantity (units)') }}</label>
                            <div style="display:flex;gap:8px;align-items:center;">
                                <input type="number" name="quantity" id="sfQty" class="cs-input" min="1" value="1" required
                                       data-unit="{{ $item->unit_price }}" data-install="{{ $item->installation_cost }}">
                                <button type="button" id="sfAllUnits" class="cs-btn cs-btn--ghost cs-btn--sm" style="white-space:nowrap;">{{ __('All units') }}</button>
                            </div>
                            <small class="cs-muted" id="sfUnitsHint"></small>
                        </div>

                        <div style="background:var(--blue-light);border:0.5px solid var(--blue-border);border-radius:10px;padding:14px;margin-bottom:18px;">
                            <div class="d-flex justify-content-between" style="font-size:13px;color:var(--gray-700);"><span>{{ __('Hardware') }}</span><span id="sfHardware">—</span></div>
                            <div class="d-flex justify-content-between" style="font-size:13px;color:var(--gray-700);"><span>{{ __('Installation') }}</span><span id="sfInstall">—</span></div>
                            <div class="d-flex justify-content-between mt-1" style="font-weight:700;color:var(--gray-900);"><span>{{ __('Total') }}</span><span id="sfTotal">—</span></div>
                        </div>

                        <button type="submit" id="sfSubmit" class="cs-btn cs-btn--primary">{{ __('Create self-financing order') }}</button>
                        <a href="{{ route('owner.financing.index') }}" class="cs-btn cs-btn--ghost">{{ __('Cancel') }}</a>
                    </form>

                    <script>
                        (function () {
                            var q = document.getElementById('sfQty'), property = document.getElementById('sfProperty'),
                                allBtn = document.getElementById('sfAllUnits'), hint = document.getElementById('sfUnitsHint'),
                                noUnits = document.getElementById('sfNoUnits'), addUnits = document.getElementById('sfAddUnits'),
                                submit = document.getElementById('sfSubmit');
                            function fmt(n){ return 'KES ' + n.toLocaleString('en-KE', {minimumFractionDigits:2, maximumFractionDigits:2}); }
                            function opt(){ return property.options[property.selectedIndex]; }
                            function units(){ var o = opt(); return parseInt(o && o.dataset.units || '0', 10); }
                            function calc(){
                                var maxU = units();
                                // No units → block deployment and point to "add units".
                                if (maxU === 0) {
                                    noUnits.style.display = 'block';
                                    addUnits.href = (opt() && opt().dataset.show) || '#';
                                    submit.disabled = true; submit.style.opacity = 0.5;
                                    hint.textContent = '';
                                    return;
                                }
                                noUnits.style.display = 'none';
                                submit.disabled = false; submit.style.opacity = 1;
                                var qty = Math.max(1, parseInt(q.value || '1', 10));
                                q.max = maxU; if (qty > maxU) { qty = maxU; q.value = maxU; }
                                var hw = parseFloat(q.dataset.unit) * qty, ins = parseFloat(q.dataset.install) * qty;
                                document.getElementById('sfHardware').textContent = fmt(hw);
                                document.getElementById('sfInstall').textContent = fmt(ins);
                                document.getElementById('sfTotal').textContent = fmt(hw + ins);
                                hint.textContent = 'This property has ' + maxU + ' units — that is the most you can deploy here.';
                            }
                            allBtn.addEventListener('click', function(){ q.value = Math.max(1, units()); calc(); });
                            property.addEventListener('change', calc);
                            q.addEventListener('input', calc); calc();
                        })();
                    </script>
                @endif
            </div>
        </div>
    </div>
</div></div></div>
@endsection
