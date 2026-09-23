@extends('owner.layouts.app')

@section('content')
<div class="main-content"><div class="page-content"><div class="container-fluid">
    <div class="page-content-wrapper bg-white p-30 radius-20">
        @include('centresidence._design')
        <div class="cs-titlebar">
            <div>
                <h1 class="cs-title">{{ __('My Financing') }}</h1>
                <ol class="cs-crumb"><li><a href="{{ route('owner.dashboard') }}">{{ __('Dashboard') }}</a></li><li>›</li><li>{{ __('My Financing') }}</li></ol>
            </div>
            <div style="display:flex;gap:8px;">
                <a href="{{ route('owner.financing.deductions') }}" class="cs-btn cs-btn--ghost">{{ __('Rent & deductions') }}</a>
                <a href="{{ route('owner.financing.index') }}" class="cs-btn cs-btn--primary">{{ __('Browse offers') }}</a>
            </div>
        </div>

        @if (session('success')) <div class="cs-alert is-success">{{ session('success') }}</div> @endif
        @if (session('error')) <div class="cs-alert is-danger">{{ session('error') }}</div> @endif

        <div class="cs-card">
            <div class="cs-card__head"><h2 class="cs-card__title">{{ __('Applications') }}</h2></div>
            <div class="cs-tablewrap">
                <table class="cs-table">
                    <thead><tr>
                        <th>{{ __('Reference') }}</th><th>{{ __('Partner') }}</th><th>{{ __('Module') }}</th>
                        <th>{{ __('Requested') }}</th><th>{{ __('Monthly est.') }}</th><th>{{ __('Status') }}</th>
                    </tr></thead>
                    <tbody>
                        @forelse ($applications as $a)
                            <tr>
                                <td>{{ $a->application_number ?? ('#' . $a->id) }}</td>
                                <td>{{ optional($a->partner)->company_name ?? '—' }}</td>
                                <td>{{ optional($a->module)->name ?? '—' }}</td>
                                <td class="cs-amt">KES {{ number_format($a->requested_amount, 2) }}</td>
                                <td>KES {{ number_format($a->estimated_monthly_repayment, 2) }}</td>
                                <td>@include('admin.centresidence._status', ['status' => $a->status])</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="cs-empty">{{ __('No applications yet') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="cs-card">
            <div class="cs-card__head"><h2 class="cs-card__title">{{ __('Facilities') }}</h2></div>
            <div class="cs-tablewrap">
                <table class="cs-table">
                    <thead><tr>
                        <th>{{ __('Facility') }}</th><th>{{ __('Out. principal') }}</th><th>{{ __('Monthly') }}</th>
                        <th>{{ __('Payoff today') }}</th><th>{{ __('Mode') }}</th><th>{{ __('Status') }}</th><th>{{ __('Actions') }}</th>
                    </tr></thead>
                    <tbody>
                        @forelse ($facilities as $f)
                            <tr>
                                <td>
                                    {{ $f->facility_number ?? ('#' . $f->id) }}
                                    @if (($f->disbursement_status ?? 'disbursed') !== 'disbursed')
                                        <div><span class="cs-badge {{ ($f->disbursement_status ?? '') === 'pending_confirmation' ? 'is-pending' : 'is-grey' }}" style="margin-top:4px;">
                                            {{ ($f->disbursement_status ?? '') === 'pending_confirmation' ? __('Disbursement pending — awaiting confirmation') : __('Awaiting disbursement') }}
                                        </span></div>
                                    @endif
                                    @if (in_array($f->down_payment_status ?? 'not_required', ['pending', 'failed']))
                                        <div><span class="cs-badge is-pending" style="margin-top:4px;">{{ __('Down-payment') }} KES {{ number_format($f->owner_contribution, 0) }} {{ $f->down_payment_status === 'failed' ? __('failed — check your phone') : __('pending') }}</span></div>
                                    @elseif (($f->down_payment_status ?? '') === 'collected' && $f->owner_contribution > 0)
                                        <div><span class="cs-badge is-paid" style="margin-top:4px;">{{ __('Down-payment paid') }}</span></div>
                                    @endif
                                    {{-- Financed principal vs the total cost of finance, so the two are never confused --}}
                                    @php $costOfFinance = (float) ($f->total_repayable ?? 0) - (float) $f->principal_amount; @endphp
                                    <div class="cs-muted" style="font-size:11.5px;margin-top:5px;line-height:1.5;">
                                        {{ __('Financed') }} KES {{ number_format($f->principal_amount, 0) }}
                                        · {{ __('Total repayable') }} <strong style="color:var(--gray-800,#374151);">KES {{ number_format($f->total_repayable ?? $f->principal_amount, 0) }}</strong>
                                        @if ($costOfFinance > 0)
                                            <span title="{{ __('Cost of finance — interest across the term') }}">({{ __('incl.') }} KES {{ number_format($costOfFinance, 0) }} {{ __('interest') }})</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="cs-amt">KES {{ number_format($f->outstanding_principal, 2) }}</td>
                                <td>KES {{ number_format($f->monthly_target, 2) }}</td>
                                <td class="cs-amt">KES {{ number_format($f->payoff ?? 0, 2) }}</td>
                                <td>
                                    <span class="cs-badge {{ $f->accelerated_repayment ? 'is-purple' : 'is-grey' }}">
                                        {{ $f->accelerated_repayment ? __('Accelerated') : __('Standard') }}
                                    </span>
                                </td>
                                <td>@include('admin.centresidence._status', ['status' => $f->status])</td>
                                <td>
                                    @if ($f->status === 'active')
                                        @php
                                            $monthly = number_format($f->monthly_target, 0);
                                            $payoffNum = number_format($f->payoff ?? 0, 0);
                                            $accelMsg = __('Right now we take up to KES :monthly from each rent payment toward this facility. Accelerated repayment instead puts as much of your available rent as your agreed deduction limit allows toward it every cycle — until the KES :payoff balance is cleared. You finish sooner and pay less total interest; nothing extra is charged now and it stops automatically once the facility is paid off. You will keep less rent in hand each month while it runs.', ['monthly' => $monthly, 'payoff' => $payoffNum]);
                                        @endphp
                                        <div style="display:flex;flex-wrap:wrap;gap:6px;align-items:center;">
                                            @if (($f->accelerated_repayment_allowed ?? true) || $f->accelerated_repayment)
                                                <form method="POST" action="{{ route('owner.financing.accelerate', $f->id) }}" style="display:inline;"
                                                      data-cs-confirm="{{ $f->accelerated_repayment
                                                          ? __('Switch back to standard repayment? Each cycle only the capped KES :monthly is taken from your rent toward this facility.', ['monthly' => $monthly])
                                                          : $accelMsg }}"
                                                      data-cs-confirm-title="{{ $f->accelerated_repayment ? __('Set standard repayment?') : __('Enable accelerated repayment?') }}"
                                                      data-cs-confirm-ok="{{ $f->accelerated_repayment ? __('Yes, set standard') : __('Yes, accelerate') }}">
                                                    @csrf
                                                    <button class="cs-btn cs-btn--ghost cs-btn--sm" type="submit">
                                                        {{ $f->accelerated_repayment ? __('Set standard') : __('Accelerate') }}
                                                    </button>
                                                </form>
                                            @endif
                                            @if ($f->early_settlement_status === 'pending')
                                                <span class="cs-badge is-pending">{{ __('Settlement pending confirmation') }}</span>
                                            @elseif (! ($f->early_repayment_allowed ?? true))
                                                <span class="cs-badge is-grey" title="{{ __('Your financier does not allow early settlement on this product.') }}">{{ __('Early settlement not available') }}</span>
                                            @else
                                                <button type="button" class="cs-btn cs-btn--complete cs-btn--sm js-settle-open"
                                                        data-url="{{ route('owner.financing.settle-early', $f->id) }}"
                                                        data-facility="{{ $f->facility_number ?? ('#' . $f->id) }}"
                                                        data-payoff="{{ number_format($f->payoff ?? 0, 2) }}"
                                                        data-principal="{{ number_format($f->payoff_principal ?? $f->outstanding_principal, 2) }}"
                                                        data-interest="{{ number_format($f->payoff_interest ?? $f->outstanding_interest, 2) }}"
                                                        data-penalty="{{ number_format($f->payoff_penalty ?? 0, 2) }}"
                                                        data-fee="{{ number_format($f->payoff_fee ?? 0, 2) }}"
                                                        data-fee-pct="{{ rtrim(rtrim(number_format($f->early_repayment_fee_pct ?? 0, 2), '0'), '.') }}">
                                                    {{ __('Settle early') }}
                                                </button>
                                            @endif
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="cs-empty">{{ __('No facilities yet') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Settle-early modal: channel choice + payoff live here so it's unambiguous --}}
        <div id="settleModal" class="cs-settle-overlay" aria-hidden="true">
            <div class="cs-settle" role="dialog" aria-modal="true" aria-labelledby="settleTitle">
                <h3 id="settleTitle" class="cs-settle__title">{{ __('Settle facility early') }}</h3>
                <p class="cs-settle__sub">{{ __('Clear') }} <strong id="smFacility"></strong> {{ __('in full today.') }}</p>
                <div class="cs-settle__payoff">
                    <span class="cs-settle__payoff-label">{{ __('Payoff today') }}</span>
                    <span class="cs-settle__payoff-amt">KES <span id="smPayoff"></span></span>
                </div>
                <ul class="cs-settle__lines" id="smBreakdown"></ul>
                <p class="cs-settle__note">{{ __('This clears your outstanding principal plus interest accrued to today. The future interest you would have paid over the remaining term is waived — that is your saving. Any early-settlement fee or outstanding penalty set by your financier is already included in the payoff above.') }}</p>
                <form method="POST" id="settleForm">
                    @csrf
                    <div class="cs-settle__label">{{ __('How are you paying?') }}</div>
                    <label class="cs-settle__channel">
                        <input type="radio" name="channel" value="mpesa" checked>
                        <span><strong>{{ __('M-Pesa') }}</strong> — {{ __('pay now via an STK prompt to your phone') }}</span>
                    </label>
                    <label class="cs-settle__channel">
                        <input type="radio" name="channel" value="manual">
                        <span><strong>{{ __('Bank / manual') }}</strong> — {{ __('record a transfer your financier confirms receipt of') }}</span>
                    </label>
                    <div class="cs-settle__actions">
                        <button type="button" class="cs-btn cs-btn--ghost" id="settleCancel">{{ __('Cancel') }}</button>
                        <button type="submit" class="cs-btn cs-btn--complete">{{ __('Settle now') }}</button>
                    </div>
                </form>
            </div>
        </div>

        <style>
            .cs-settle-overlay { position:fixed; inset:0; z-index:100000; display:none; align-items:center; justify-content:center;
                background:rgba(17,24,39,.5); backdrop-filter:blur(2px); padding:20px; }
            .cs-settle-overlay.is-open { display:flex; }
            .cs-settle { background:#fff; border-radius:14px; width:100%; max-width:440px; padding:26px 24px 20px;
                box-shadow:0 24px 48px rgba(0,0,0,.22); animation:csCfPop .18s cubic-bezier(.2,.8,.3,1.15) both; }
            .cs-settle__title { font-size:17px; font-weight:600; color:#111827; margin:0 0 4px; }
            .cs-settle__sub { font-size:13px; color:#6b7280; margin:0 0 14px; }
            .cs-settle__payoff { display:flex; flex-direction:column; gap:2px; background:#F0FBF6; border:0.5px solid #9FE1CB;
                border-radius:11px; padding:12px 16px; margin-bottom:12px; }
            .cs-settle__payoff-label { font-size:11px; font-weight:500; text-transform:uppercase; letter-spacing:.06em; color:#0B5940; opacity:.75; }
            .cs-settle__payoff-amt { font-size:24px; font-weight:700; color:#0B5940; line-height:1.1; }
            .cs-settle__lines { list-style:none; margin:0 0 12px; padding:0; }
            .cs-settle__lines li { display:flex; justify-content:space-between; gap:12px; font-size:12.5px; color:#4b5563; padding:4px 2px; border-bottom:0.5px dashed #eef2f7; }
            .cs-settle__lines li:last-child { border-bottom:none; }
            .cs-settle__lines li .amt { font-variant-numeric:tabular-nums; color:#374151; font-weight:600; }
            .cs-settle__lines li.is-fee { color:#9A3412; }
            .cs-settle__lines li.is-fee .amt { color:#9A3412; }
            .cs-settle__note { font-size:12.5px; color:#6b7280; line-height:1.55; margin:0 0 16px; }
            .cs-settle__label { font-size:12px; font-weight:600; color:#374151; margin-bottom:8px; }
            .cs-settle__channel { display:flex; gap:9px; align-items:flex-start; padding:10px 12px; border:0.5px solid #e5e7eb;
                border-radius:10px; margin-bottom:8px; cursor:pointer; font-size:12.5px; color:#374151; line-height:1.4; transition:border-color .13s, background .13s; }
            .cs-settle__channel:hover { border-color:#9FE1CB; background:#F7FCFA; }
            .cs-settle__channel input { margin-top:2px; flex-shrink:0; }
            .cs-settle__actions { display:flex; gap:10px; margin-top:16px; }
            .cs-settle__actions .cs-btn { flex:1; justify-content:center; }
        </style>

        <script>
            (function () {
                var overlay = document.getElementById('settleModal');
                if (!overlay) return;
                var form = document.getElementById('settleForm');
                var L = {
                    prin: @json(__('Outstanding principal')),
                    int:  @json(__('Interest to date')),
                    pen:  @json(__('Outstanding penalty')),
                    fee:  @json(__('Early-settlement fee'))
                };
                function isPos(s) { return parseFloat((s || '0').replace(/,/g, '')) > 0; }
                function line(label, amt, cls) {
                    return '<li' + (cls ? ' class="' + cls + '"' : '') + '><span>' + label + '</span><span class="amt">KES ' + amt + '</span></li>';
                }
                function open(btn) {
                    form.setAttribute('action', btn.getAttribute('data-url'));
                    document.getElementById('smFacility').textContent = btn.getAttribute('data-facility');
                    document.getElementById('smPayoff').textContent = btn.getAttribute('data-payoff');
                    var prin = btn.getAttribute('data-principal'), intr = btn.getAttribute('data-interest'),
                        pen = btn.getAttribute('data-penalty'), fee = btn.getAttribute('data-fee'), feePct = btn.getAttribute('data-fee-pct');
                    var html = line(L.prin, prin) + line(L.int, intr);
                    if (isPos(pen)) html += line(L.pen, pen);
                    if (isPos(fee)) html += line(L.fee + (feePct && feePct !== '0' ? ' (' + feePct + '%)' : ''), fee, 'is-fee');
                    document.getElementById('smBreakdown').innerHTML = html;
                    overlay.classList.add('is-open'); overlay.setAttribute('aria-hidden', 'false');
                }
                function close() { overlay.classList.remove('is-open'); overlay.setAttribute('aria-hidden', 'true'); }
                document.addEventListener('click', function (e) {
                    var t = e.target.closest('.js-settle-open');
                    if (t) { e.preventDefault(); open(t); return; }
                    if (e.target === overlay || (e.target.closest && e.target.closest('#settleCancel'))) close();
                });
                document.addEventListener('keydown', function (e) { if (e.key === 'Escape') close(); });
            })();
        </script>

        <div class="cs-card">
            <div class="cs-card__head"><h2 class="cs-card__title">{{ __('Self-financed modules') }}</h2></div>
            <div class="cs-tablewrap">
                <table class="cs-table">
                    <thead><tr>
                        <th>{{ __('Reference') }}</th><th>{{ __('Module') }}</th><th>{{ __('Qty') }}</th>
                        <th>{{ __('Hardware') }}</th><th>{{ __('Installation') }}</th><th>{{ __('Total') }}</th><th>{{ __('Status') }}</th>
                    </tr></thead>
                    <tbody>
                        @forelse ($selfFinanced as $s)
                            <tr>
                                <td>{{ $s->reference ?? ('#' . $s->id) }}</td>
                                <td>{{ optional($s->module)->name ?? '—' }}</td>
                                <td>{{ $s->quantity }}</td>
                                <td>KES {{ number_format($s->hardware_cost, 2) }}</td>
                                <td>KES {{ number_format($s->installation_cost, 2) }}</td>
                                <td class="cs-amt">KES {{ number_format($s->total_cost, 2) }}</td>
                                <td>@include('admin.centresidence._status', ['status' => $s->status])</td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="cs-empty">{{ __('No self-financed modules yet') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div></div></div>
@endsection
