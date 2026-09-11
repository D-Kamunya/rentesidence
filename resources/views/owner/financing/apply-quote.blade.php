@extends('owner.layouts.app')

@section('content')
<div class="main-content"><div class="page-content"><div class="container-fluid">
    <div class="page-content-wrapper bg-white p-30 radius-20">
        @include('centresidence._design')
        <div class="cs-titlebar">
            <div>
                <h1 class="cs-title">{{ __('Apply for financing') }}</h1>
                <ol class="cs-crumb"><li><a href="{{ route('owner.financing.surveys') }}">{{ __('Site surveys') }}</a></li><li>›</li><li>{{ optional($product->module)->name }}</li></ol>
            </div>
        </div>

        @if (session('error')) <div class="cs-alert is-danger">{{ session('error') }}</div> @endif
        @if ($errors->any()) <div class="cs-alert is-danger">{{ $errors->first() }}</div> @endif

        <div class="row">
            <div class="col-lg-7">
                <div class="cs-card"><div class="cs-card__body">
                    <p class="cs-muted" style="margin:0 0 16px;">{{ __('Financing your surveyed quotation with :partner. You repay the financed amount from rent over the term you choose.', ['partner' => optional($product->partner)->trading_name ?? optional($product->partner)->company_name]) }}</p>

                    <form method="POST" action="{{ route('owner.financing.store') }}">
                        @csrf
                        <input type="hidden" name="field_study_request_id" value="{{ $fsr->id }}">
                        <input type="hidden" name="finance_partner_module_id" value="{{ $product->id }}">
                        <input type="hidden" name="consented_deduction_cap" id="q_consentValue" value="">

                        <div class="cs-field">
                            <label class="cs-label">{{ __('Quoted amount') }}</label>
                            <input type="text" class="cs-input" value="KES {{ number_format((float) $fsr->quoted_amount, 2) }}" disabled>
                            <small class="cs-muted">{{ optional($product->module)->name }} · {{ optional($property)->name }}@if ($fsr->units) · {{ $fsr->units }} {{ __('units') }}@endif</small>
                        </div>

                        <div class="cs-field">
                            <label class="cs-label">{{ __('Down-payment (optional)') }}</label>
                            <input type="number" name="owner_contribution" id="q_down" class="cs-input" min="0" step="0.01" value="0">
                            <small class="cs-muted">{{ __('Pay part now and finance the rest — you only pay interest on the financed portion.') }}</small>
                        </div>

                        <div class="cs-field">
                            <label class="cs-label">{{ __('Repayment term (months)') }}</label>
                            <input type="number" name="repayment_months" id="q_months" class="cs-input" required
                                   min="{{ $product->min_repayment_months }}" max="{{ $product->max_repayment_months }}" value="{{ $product->min_repayment_months }}">
                            <small class="cs-muted">{{ $product->min_repayment_months }}–{{ $product->max_repayment_months }} {{ __('months') }} · {{ number_format($product->interest_rate, 2) }}% {{ str_replace('_', ' ', $product->interest_rate_type) }}</small>
                        </div>

                        <div id="q_consentBox" style="display:none;background:#FEF9EE;border:1px solid #FAC775;border-radius:10px;padding:12px 14px;margin:6px 0 14px;">
                            <label style="display:flex;gap:8px;align-items:flex-start;font-size:13px;color:#854F0B;">
                                <input type="checkbox" id="q_consentCheck" style="margin-top:3px;">
                                <span id="q_consentLabel"></span>
                            </label>
                        </div>

                        <button type="submit" id="q_submit" class="cs-btn cs-btn--primary">{{ __('Submit application') }}</button>
                    </form>
                </div></div>
            </div>

            <div class="col-lg-5">
                <div class="cs-card"><div class="cs-card__head"><h2 class="cs-card__title">{{ __('Summary') }}</h2></div>
                <div class="cs-card__body">
                    <div class="d-flex justify-content-between" style="font-size:13px;color:var(--gray-700);padding:4px 0;"><span>{{ __('Quoted amount') }}</span><span>KES {{ number_format((float) $fsr->quoted_amount, 2) }}</span></div>
                    <div class="d-flex justify-content-between" id="q_downRow" style="font-size:13px;color:var(--gray-700);padding:4px 0;display:none;"><span>{{ __('Down-payment') }}</span><b id="q_downL">—</b></div>
                    <div class="d-flex justify-content-between" style="font-weight:700;color:var(--gray-900);padding:6px 0;border-top:0.5px solid var(--gray-200);margin-top:4px;"><span>{{ __('To finance') }}</span><span id="q_fin">—</span></div>
                    <div class="d-flex justify-content-between" style="font-size:13px;color:var(--blue);padding:6px 0;"><span>{{ __('Est. monthly') }}</span><b id="q_monthly">—</b></div>
                    <div id="q_affordRow" style="font-size:12px;color:var(--gray-700);padding:6px 0;border-top:0.5px solid var(--gray-200);display:none;">{{ __('Rent used for repayment:') }} <b id="q_affordPct">—</b></div>
                    <p id="q_affordWarn" style="display:none;margin:8px 0 0;font-size:12px;color:#B42318;"></p>
                    <p class="cs-muted" style="margin-top:10px;font-size:12px;">{{ __('The exact monthly repayment is confirmed on your application once submitted.') }}</p>
                </div></div>
            </div>
        </div>
    </div>
</div></div></div>

<script>
    (function () {
        var Q = {
            quoted: {{ (float) $fsr->quoted_amount }},
            rate: {{ (float) $product->interest_rate }},
            method: @json($product->interest_rate_type),
            min: {{ (float) $product->min_amount }},
            max: {{ (float) $product->max_amount }},
            units: {{ (int) ($fsr->units ?: 1) }},
            rent: {{ (float) $propertyRent }},
            existingInfra: {{ (float) $existingInfra }},
            infraPerDevice: {{ (float) $infraPerDevice }},
            infraFlat: {{ (float) $infraFlat }},
            capPct: {{ (int) $rentCapPct }},
            consentMax: {{ (int) $consentMaxPct }}
        };
        var down = document.getElementById('q_down'), months = document.getElementById('q_months'),
            submit = document.getElementById('q_submit'),
            consentBox = document.getElementById('q_consentBox'), consentCheck = document.getElementById('q_consentCheck'),
            consentValue = document.getElementById('q_consentValue'), consentLabel = document.getElementById('q_consentLabel');
        var fmt = function (n) { return 'KES ' + n.toLocaleString('en-KE', {minimumFractionDigits: 2, maximumFractionDigits: 2}); };

        function monthly(principal) {
            var n = parseInt(months.value, 10) || 0, r = Q.rate / 100;
            if (n <= 0 || principal <= 0) return 0;
            if (Q.method === 'flat') return (principal + principal * r * (n / 12)) / n;
            var m = r / 12;
            return m <= 0 ? principal / n : principal * m / (1 - Math.pow(1 + m, -n));
        }

        function render() {
            var contribution = Math.min(Math.max(parseFloat(down.value) || 0, 0), Q.quoted);
            var financed = Q.quoted - contribution;
            document.getElementById('q_downRow').style.display = contribution > 0 ? 'flex' : 'none';
            document.getElementById('q_downL').textContent = '− ' + fmt(contribution);
            document.getElementById('q_fin').textContent = fmt(financed);
            var mo = monthly(financed);
            document.getElementById('q_monthly').textContent = fmt(mo) + ' × ' + (parseInt(months.value, 10) || 0);

            var ok = financed > 0;
            var msg = '';
            if (Q.max > 0 && financed > Q.max + 0.01) { ok = false; msg = 'This financier caps financing at ' + fmt(Q.max) + '. Add a larger down-payment.'; }
            else if (Q.min > 0 && financed < Q.min - 0.01) { ok = false; msg = 'This financier requires at least ' + fmt(Q.min) + ' financed. Lower your down-payment.'; }

            // Affordability vs rent (matches the server feasibility gate).
            var affordRow = document.getElementById('q_affordRow'), affordPct = document.getElementById('q_affordPct');
            consentBox.style.display = 'none'; consentValue.value = '';
            if (Q.rent > 0 && financed > 0 && ok) {
                var moduleInfra = Q.infraPerDevice * Q.units + Q.infraFlat;
                var pct = (mo + moduleInfra + Q.existingInfra) / Q.rent * 100;
                affordRow.style.display = 'block';
                affordPct.textContent = pct.toFixed(1) + '%';
                if (pct > Q.consentMax + 0.5) {
                    ok = false; msg = 'Repayment would use ' + pct.toFixed(1) + '% of rent, above the ' + Q.consentMax + '% ceiling. Add a down-payment or a longer term.';
                } else if (pct > Q.capPct + 0.5) {
                    // Between the default cap and the max → allowed only with explicit consent.
                    consentBox.style.display = 'block';
                    consentLabel.textContent = 'Repayment would use ' + pct.toFixed(1) + '% of rent, above the standard ' + Q.capPct + '%. I authorise up to ' + Q.consentMax + '% of rent to service this facility.';
                    if (consentCheck.checked) { consentValue.value = Q.consentMax; } else { ok = false; }
                }
            } else {
                affordRow.style.display = 'none';
            }

            var warn = document.getElementById('q_affordWarn');
            warn.style.display = msg ? 'block' : 'none'; warn.textContent = msg;
            submit.disabled = ! ok; submit.style.opacity = ok ? 1 : 0.5;
        }

        [down, months, consentCheck].forEach(function (el) { el.addEventListener('input', render); el.addEventListener('change', render); });
        render();
    })();
</script>
@endsection
