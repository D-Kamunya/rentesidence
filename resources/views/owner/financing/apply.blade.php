@extends('owner.layouts.app')

@section('content')
<div class="main-content"><div class="page-content"><div class="container-fluid">
    <div class="page-content-wrapper bg-white p-30 radius-20">
        @include('centresidence._design')
        <div class="cs-titlebar"><h1 class="cs-title">{{ __('Apply for financing') }}</h1></div>

        @if (session('error')) <div class="cs-alert is-danger">{{ session('error') }}</div> @endif

        <div class="cs-card" style="max-width:760px;">
            <div class="cs-card__head">
                <h2 class="cs-card__title">{{ optional($product->module)->name }} — {{ optional($product->partner)->company_name }}</h2>
            </div>
            <div class="cs-card__body">
                <p class="cs-muted" style="margin-bottom:16px;">
                    {{ number_format($product->interest_rate, 2) }}% {{ str_replace('_', ' ', $product->interest_rate_type) }} ·
                    {{ $product->min_repayment_months }}–{{ $product->max_repayment_months }} {{ __('months') }}
                    @if ($product->max_amount > 0) · {{ __('up to') }} KES {{ number_format($product->max_amount, 0) }} @endif
                </p>

                @if (! $catalogue)
                    <div class="cs-alert is-amber">{{ __('No pricing is configured for this module yet.') }}</div>
                @elseif ($properties->isEmpty())
                    <div class="cs-alert is-amber">{{ __('You need a property before you can apply.') }}</div>
                @else
                    <form method="POST" action="{{ route('owner.financing.store') }}" id="csApplyForm">
                        @csrf
                        <input type="hidden" name="finance_partner_module_id" value="{{ $product->id }}">
                        <input type="hidden" name="catalogue_item_id" value="{{ $catalogue->id }}">

                        <div class="cs-field">
                            <label class="cs-label">{{ __('Property') }}</label>
                            <select name="property_id" id="csProperty" class="cs-select" required>
                                @foreach ($properties as $property)
                                    <option value="{{ $property->id }}" data-units="{{ $property->property_units_count }}" data-rent="{{ (float) $property->property_units_sum_general_rent }}" data-existing-infra="{{ (float) (($existingInfra[$property->id]['cost'] ?? 0)) }}" data-existing-modules="{{ (int) (($existingInfra[$property->id]['modules'] ?? 0)) }}" data-show="{{ route('owner.property.show', $property->id) }}">
                                        {{ $property->name ?? ('Property #' . $property->id) }}
                                        ({{ $property->property_units_count }} {{ __('units') }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="cs-alert is-amber" id="csNoUnits" style="display:none;">
                            {{ __('This property has no units yet.') }}
                            <a id="csAddUnits" href="#" style="font-weight:600;text-decoration:underline;">{{ __('Add units first') }}</a>
                            {{ __('— a property needs units before you can finance modules.') }}
                        </div>

                        <div class="row">
                            <div class="col-md-6 cs-field">
                                <label class="cs-label">{{ __('Quantity (units)') }}</label>
                                <div style="display:flex;gap:8px;align-items:center;">
                                    <input type="number" name="quantity" id="csQty" class="cs-input" min="1" value="1" required>
                                    <button type="button" id="csAllUnits" class="cs-btn cs-btn--ghost cs-btn--sm" style="white-space:nowrap;">{{ __('All units') }}</button>
                                </div>
                                <small class="cs-muted" id="csUnitsHint"></small>
                            </div>
                            <div class="col-md-6 cs-field">
                                <label class="cs-label">{{ __('Repayment months') }}</label>
                                <input type="number" name="repayment_months" id="csMonths" class="cs-input"
                                       min="{{ $product->min_repayment_months }}" max="{{ $product->max_repayment_months }}"
                                       value="{{ $product->min_repayment_months }}" required>
                            </div>
                        </div>

                        <div class="cs-field">
                            <label class="cs-label">{{ __('Your down-payment (optional)') }}</label>
                            <div style="display:flex;gap:8px;align-items:center;">
                                <input type="number" name="owner_contribution" id="csDown" class="cs-input" min="0" step="0.01" value="0">
                                <button type="button" id="csHalf" class="cs-btn cs-btn--ghost cs-btn--sm" style="white-space:nowrap;">{{ __('Half') }}</button>
                            </div>
                            <small class="cs-muted">{{ __('Pay part now and finance the rest — you only pay interest on the financed portion.') }}</small>
                        </div>

                        {{-- Live cost breakdown — mirrors the server calculator exactly. --}}
                        <div class="cs-card" style="background:var(--gray-50);margin:6px 0 16px;">
                            <div class="cs-card__body" style="padding:14px 16px;">
                                <div class="cs-costline"><span>{{ __('Hardware') }} (<span id="csQtyL">1</span> × KES {{ number_format($catalogue->unit_price, 2) }})</span><b id="csHardware">—</b></div>
                                @if ($catalogue->installation_cost > 0)
                                    <div class="cs-costline"><span>{{ __('Installation') }} (<span id="csQtyL2">1</span> × KES {{ number_format($catalogue->installation_cost, 2) }})</span><b id="csInstall">—</b></div>
                                @endif
                                <div class="cs-costline"><span>{{ __('Centresidence platform fee') }} (<span id="csFeePct">{{ rtrim(rtrim(number_format($feePct, 2), '0'), '.') }}</span>%)</span><b id="csFee">—</b></div>
                                <div class="cs-costline"><span>{{ __('Total deployment cost') }}</span><b id="csTotal">—</b></div>
                                <div class="cs-costline" id="csDownRow" style="display:none;"><span>{{ __('− Your down-payment') }}</span><b id="csDownL">—</b></div>
                                <div class="cs-costline cs-costline--total"><span>{{ __('Financed by partner (you repay this)') }}</span><b id="csFinanced">—</b></div>
                                <div class="cs-costline"><span class="cs-muted">{{ __('Est. monthly repayment') }}</span><span class="cs-muted" id="csMonthly">—</span></div>
                                <div class="cs-costline" id="csAffordRow" style="display:none;"><span class="cs-muted">{{ __('Share of this property\'s rent') }}</span><b id="csAfford">—</b></div>
                            </div>
                        </div>

                        {{-- Existing-modules context: reassure the owner their meter income is untouched --}}
                        <p class="cs-muted" id="csExistingNote" style="display:none;margin:-8px 0 14px;font-size:12px;"></p>

                        {{-- Affordability: how the repayment sits against the rent-deduction ceiling --}}
                        <div class="cs-alert is-amber" id="csAffordWarn" style="display:none;font-size:12px;"></div>

                        {{-- Informed consent to a higher personal cap (only shown when over the default ceiling) --}}
                        <input type="hidden" name="consented_deduction_cap" id="csConsentValue" value="">
                        <label id="csConsent" style="display:none;align-items:flex-start;gap:8px;font-size:12px;color:var(--gray-700);background:var(--blue-faint);border:0.5px solid rgba(24,95,165,.18);border-radius:8px;padding:10px 12px;margin-bottom:14px;cursor:pointer;">
                            <input type="checkbox" id="csConsentCheck" style="margin-top:2px;">
                            <span id="csConsentLabel"></span>
                        </label>

                        @if (($product->module->settlement_target ?? 'centresidence') === 'centresidence')
                            <div class="cs-alert is-info" style="font-size:12px;">
                                <i class="ri-tools-line"></i>
                                {{ __('Centresidence is the official installer. The financier pays Centresidence directly to supply and install the units; any down-payment is also payable to Centresidence.') }}
                            </div>
                        @endif

                        <div class="cs-alert is-danger" id="csLimitWarn" style="display:none;"></div>

                        <p class="cs-muted" style="margin:6px 0 16px;font-size:12px;">
                            {{ __('The facility amount includes hardware, installation, and the Centresidence platform fee — the finance partner advances this total and you repay it from rent. On submission we run a soft eligibility check; the partner makes the final decision.') }}
                        </p>

                        <button type="submit" id="csSubmit" class="cs-btn cs-btn--primary">{{ __('Submit application') }}</button>
                        <a href="{{ route('owner.financing.index') }}" class="cs-btn cs-btn--ghost">{{ __('Cancel') }}</a>
                    </form>

                    <script>
                        (function () {
                            var P = {
                                unit: {{ (float) $catalogue->unit_price }},
                                install: {{ (float) $catalogue->installation_cost }},
                                feePct: {{ (float) $feePct }},
                                rate: {{ (float) $product->interest_rate }},
                                method: @json($product->interest_rate_type),
                                min: {{ (float) $product->min_amount }},
                                max: {{ (float) $product->max_amount }},
                                capPct: {{ (int) $rentCapPct }},
                                consentMax: {{ (int) $consentMaxPct }},
                                infraPerDevice: {{ (float) $infraPerDevice }},
                                infraFlat: {{ (float) $infraFlat }}
                            };
                            var qty = document.getElementById('csQty'), months = document.getElementById('csMonths'),
                                down = document.getElementById('csDown'), halfBtn = document.getElementById('csHalf'),
                                property = document.getElementById('csProperty'), allBtn = document.getElementById('csAllUnits'),
                                warn = document.getElementById('csLimitWarn'), submit = document.getElementById('csSubmit'),
                                noUnits = document.getElementById('csNoUnits'), addUnits = document.getElementById('csAddUnits'),
                                consent = document.getElementById('csConsent'), consentCheck = document.getElementById('csConsentCheck'),
                                consentValue = document.getElementById('csConsentValue'), consentLabel = document.getElementById('csConsentLabel');
                            var fmt = function (n) { return 'KES ' + n.toLocaleString('en-KE', {minimumFractionDigits: 2, maximumFractionDigits: 2}); };

                            function units() { var o = property.options[property.selectedIndex]; return parseInt(o && o.dataset.units || '0', 10); }
                            function propertyRent() { var o = property.options[property.selectedIndex]; return parseFloat(o && o.dataset.rent || '0'); }
                            function existingInfra() { var o = property.options[property.selectedIndex]; return parseFloat(o && o.dataset.existingInfra || '0'); }
                            function existingModules() { var o = property.options[property.selectedIndex]; return parseInt(o && o.dataset.existingModules || '0', 10); }
                            function total() { var q = Math.max(1, parseInt(qty.value, 10) || 1); return (P.unit + P.install) * q * (1 + P.feePct / 100); }
                            function monthly(principal) {
                                var n = parseInt(months.value, 10) || 0, r = P.rate / 100;
                                if (n <= 0 || principal <= 0) return 0;
                                if (P.method === 'flat') return (principal + principal * r * (n / 12)) / n;
                                var m = r / 12;
                                return m <= 0 ? principal / n : principal * m / (1 - Math.pow(1 + m, -n));
                            }
                            function render() {
                                var maxU = units();
                                // No units → block financing and point to "add units".
                                if (maxU === 0) {
                                    noUnits.style.display = 'block';
                                    var o = property.options[property.selectedIndex];
                                    addUnits.href = (o && o.dataset.show) || '#';
                                    warn.style.display = 'none';
                                    submit.disabled = true; submit.style.opacity = 0.5;
                                    return;
                                }
                                noUnits.style.display = 'none';
                                var q = Math.max(1, parseInt(qty.value, 10) || 1);
                                // Can't deploy more units than the property physically has.
                                qty.max = maxU; if (q > maxU) { q = maxU; qty.value = maxU; }
                                var hw = P.unit * q, inst = P.install * q, base = hw + inst,
                                    fee = base * P.feePct / 100, gross = base + fee;
                                var contribution = Math.min(Math.max(parseFloat(down.value) || 0, 0), gross);
                                var financed = gross - contribution;

                                document.getElementById('csQtyL').textContent = q;
                                if (document.getElementById('csQtyL2')) document.getElementById('csQtyL2').textContent = q;
                                document.getElementById('csHardware').textContent = fmt(hw);
                                if (document.getElementById('csInstall')) document.getElementById('csInstall').textContent = fmt(inst);
                                document.getElementById('csFee').textContent = fmt(fee);
                                document.getElementById('csTotal').textContent = fmt(gross);
                                document.getElementById('csDownRow').style.display = contribution > 0 ? 'flex' : 'none';
                                document.getElementById('csDownL').textContent = '− ' + fmt(contribution);
                                document.getElementById('csFinanced').textContent = fmt(financed);
                                document.getElementById('csMonthly').textContent = fmt(monthly(financed)) + ' × ' + (parseInt(months.value, 10) || 0);

                                // Affordability: facility monthly as a share of this property's rent.
                                var rent = propertyRent(), affordRow = document.getElementById('csAffordRow'),
                                    affordWarn = document.getElementById('csAffordWarn'),
                                    existingNote = document.getElementById('csExistingNote'),
                                    existing = existingInfra(), nMods = existingModules();
                                // Reassure: existing modules add infra cost, but their meter income is separate.
                                if (existing > 0) {
                                    existingNote.style.display = 'block';
                                    existingNote.innerHTML = 'You already run <b>' + nMods + '</b> module' + (nMods === 1 ? '' : 's') + ' on this property (~' + fmt(existing) + '/mo infrastructure), counted below. Your meter (token) income from them is separate and isn\'t touched by this.';
                                } else {
                                    existingNote.style.display = 'none';
                                }
                                if (rent > 0 && financed > 0) {
                                    // Match the server gate: facility monthly + this module's infra + existing infra.
                                    var moduleInfra = P.infraPerDevice * q + P.infraFlat;
                                    var sharePct = (monthly(financed) + moduleInfra + existing) / rent * 100;
                                    var over = sharePct > P.capPct;
                                    affordRow.style.display = 'flex';
                                    document.getElementById('csAfford').textContent = sharePct.toFixed(0) + '% ' + (over ? '⚠' : '✓');
                                    affordWarn.style.display = over ? 'block' : 'none';
                                    if (over) {
                                        affordWarn.innerHTML = 'Your monthly cost (repayment + module fees) is <b>' + sharePct.toFixed(0) + '%</b> of your rent — above the ' + P.capPct + '% deduction ceiling that protects your cashflow. To keep your term, accept a higher limit below; otherwise reduce the amount, add a down-payment, or pick a longer term.';
                                        // Offer informed consent to a higher personal cap to keep the term.
                                        var newCap = Math.min(P.consentMax, Math.ceil(sharePct));
                                        consent.style.display = 'flex';
                                        consentLabel.innerHTML = 'Keep my ' + (parseInt(months.value, 10) || 0) + '-month term — I accept deductions up to <b>' + newCap + '%</b> of my rent (I\'ll still keep at least ' + (100 - newCap) + '%).';
                                        consentValue.value = consentCheck.checked ? newCap : '';
                                    } else {
                                        consent.style.display = 'none';
                                        consentCheck.checked = false;
                                        consentValue.value = '';
                                    }
                                } else {
                                    affordRow.style.display = 'none';
                                    affordWarn.style.display = 'none';
                                    consent.style.display = 'none';
                                    consentValue.value = '';
                                }

                                var msg = '';
                                if (financed <= 0) msg = 'Your down-payment covers the whole cost — use the self-finance option instead.';
                                else if (P.max > 0 && financed > P.max + 0.01) msg = 'You would finance ' + fmt(financed) + ', above the financier ceiling of ' + fmt(P.max) + '. Add a down-payment, reduce units, or pick another financier.';
                                else if (P.min > 0 && financed < P.min - 0.01) msg = 'You would finance only ' + fmt(financed) + ', below the financier minimum of ' + fmt(P.min) + '. Lower your down-payment.';
                                warn.style.display = msg ? 'block' : 'none';
                                warn.textContent = msg;
                                submit.disabled = !!msg;
                                submit.style.opacity = msg ? 0.5 : 1;
                            }
                            function syncHint() {
                                document.getElementById('csUnitsHint').textContent = 'This property has ' + units() + ' units — that is the most you can deploy here.';
                            }
                            allBtn.addEventListener('click', function () { qty.value = Math.max(1, units()); render(); });
                            halfBtn.addEventListener('click', function () { down.value = (total() / 2).toFixed(2); render(); });
                            property.addEventListener('change', function () { syncHint(); render(); });
                            consentCheck.addEventListener('change', render);
                            [qty, months, down].forEach(function (el) { el.addEventListener('input', render); });
                            syncHint(); render();
                        })();
                    </script>
                @endif
            </div>
        </div>
    </div>
</div></div></div>
@endsection
