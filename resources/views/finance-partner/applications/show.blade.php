@extends('finance-partner.layouts.app')

@section('content')
    @php $uw = $application->underwriting_result_json; $pending = in_array($application->status, ['submitted', 'under_review']); @endphp
    <div class="cs-titlebar">
        <div>
            <h1 class="cs-title">{{ $application->application_number ?? ('#' . $application->id) }}</h1>
            <ol class="cs-crumb"><li><a href="{{ route('finance-partner.applications.index') }}">{{ __('Applications') }}</a></li><li>›</li><li>{{ __('Review') }}</li></ol>
        </div>
        @include('admin.centresidence._status', ['status' => $application->status])
    </div>

    <div class="row">
        <div class="col-lg-7">
            <div class="cs-card">
                <div class="cs-card__head"><h2 class="cs-card__title">{{ __('Application details') }}</h2></div>
                <div class="cs-card__body">
                    <div class="row">
                        <div class="col-6 cs-field"><div class="cs-label">{{ __('Owner') }}</div>{{ optional($application->owner)->name ?? '—' }}</div>
                        <div class="col-6 cs-field"><div class="cs-label">{{ __('Property') }}</div>{{ optional($application->property)->name ?? ('#' . $application->property_id) }}</div>
                        <div class="col-6 cs-field"><div class="cs-label">{{ __('Module') }}</div>{{ optional($application->module)->name ?? '—' }}</div>
                        <div class="col-6 cs-field"><div class="cs-label">{{ __('Quantity') }}</div>{{ $application->quantity }}</div>
                        <div class="col-6 cs-field"><div class="cs-label">{{ __('Base cost') }}</div>KES {{ number_format($application->base_cost, 2) }}</div>
                        <div class="col-6 cs-field"><div class="cs-label">{{ __('Platform fee') }}</div>KES {{ number_format($application->platform_fee_amount, 2) }}</div>
                        <div class="col-6 cs-field"><div class="cs-label">{{ __('Total deployment cost') }}</div>KES {{ number_format($application->requested_amount, 2) }}</div>
                        @if ($application->owner_contribution > 0)
                            <div class="col-6 cs-field"><div class="cs-label">{{ __('Owner down-payment') }}</div>KES {{ number_format($application->owner_contribution, 2) }}</div>
                        @endif
                        <div class="col-6 cs-field"><div class="cs-label">{{ __('You finance') }}</div><span class="cs-amt">KES {{ number_format($application->financed_amount > 0 ? $application->financed_amount : $application->requested_amount, 2) }}</span></div>
                        <div class="col-6 cs-field"><div class="cs-label">{{ __('Est. monthly') }}</div>KES {{ number_format($application->estimated_monthly_repayment, 2) }}</div>
                        <div class="col-6 cs-field"><div class="cs-label">{{ __('Tenor') }}</div>{{ $application->repayment_months }} {{ __('months') }}</div>

                        @php
                            $pFinanced = $application->financed_amount > 0 ? $application->financed_amount : $application->requested_amount;
                            $pTotal    = (float) $application->estimated_monthly_repayment * (int) $application->repayment_months;
                            $pProfit   = $pTotal - $pFinanced;
                            $pReturnPct = $pFinanced > 0 ? $pProfit / $pFinanced * 100 : 0;
                        @endphp
                        @if ($application->estimated_monthly_repayment > 0)
                            <div class="col-12">
                                <div style="display:flex;flex-wrap:wrap;gap:24px;align-items:center;justify-content:space-between;margin-top:8px;padding:14px 18px;background:#F0FBF6;border:0.5px solid #9FE1CB;border-radius:12px;">
                                    <div>
                                        <div class="cs-label">{{ __('Total you receive') }}</div>
                                        <span class="cs-amt" style="font-size:18px;">KES {{ number_format($pTotal, 2) }}</span>
                                        <span class="cs-muted" style="font-size:11px;">{{ __('over :n months', ['n' => $application->repayment_months]) }}</span>
                                    </div>
                                    <div>
                                        <div class="cs-label">{{ __('Your interest (profit)') }}</div>
                                        <span class="cs-amt" style="font-size:18px;color:#0B5940;">+KES {{ number_format($pProfit, 2) }}</span>
                                        <span class="cs-muted" style="font-size:11px;">({{ number_format($pReturnPct, 1) }}% {{ __('on') }} KES {{ number_format($pFinanced, 0) }})</span>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="cs-card">
                <div class="cs-card__head"><h2 class="cs-card__title">{{ __('Eligibility check') }}</h2></div>
                <div class="cs-card__body">
                    @if (!$uw)
                        <p class="cs-muted">{{ __('No underwriting result recorded.') }}</p>
                    @else
                        <p>
                            {{ __('Overall') }}:
                            <span class="cs-badge {{ ($uw['passed'] ?? false) ? 'is-paid' : 'is-danger' }}">{{ ($uw['passed'] ?? false) ? __('Passed soft check') : __('Hard rule failed') }}</span>
                        </p>
                        @if (!empty($uw['results']))
                            <div class="cs-tablewrap">
                                <table class="cs-table">
                                    <thead><tr><th>{{ __('Rule') }}</th><th>{{ __('Required') }}</th><th>{{ __('Actual') }}</th><th>{{ __('Type') }}</th><th>{{ __('Result') }}</th></tr></thead>
                                    <tbody>
                                        @foreach ($uw['results'] as $r)
                                            <tr>
                                                <td>{{ $r['rule_name'] ?? $r['parameter'] }}</td>
                                                <td>{{ $r['operator'] }} {{ $r['value'] }}</td>
                                                <td>{{ $r['actual'] ?? '—' }}</td>
                                                <td>{{ ($r['is_hard_rule'] ?? false) ? __('Hard') : __('Soft') }}</td>
                                                <td><span class="cs-badge {{ ($r['passed'] ?? false) ? 'is-paid' : 'is-danger' }}">{{ ($r['passed'] ?? false) ? __('Pass') : __('Fail') }}</span></td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            @if ($pending)
                <div class="cs-card">
                    <div class="cs-card__head"><h2 class="cs-card__title">{{ __('Decision') }}</h2></div>
                    <div class="cs-card__body">
                        <form method="POST" action="{{ route('finance-partner.applications.approve', $application->id) }}" style="margin-bottom:18px;">
                            @csrf
                            <div class="cs-field">
                                <label class="cs-label">{{ __('Approved amount (KES)') }}</label>
                                <input type="number" step="0.01" name="approved_amount" class="cs-input" value="{{ $application->financed_amount > 0 ? $application->financed_amount : $application->requested_amount }}" required>
                            </div>
                            <button type="submit" class="cs-btn cs-btn--complete" style="width:100%;justify-content:center;">{{ __('Approve & create facility') }}</button>
                        </form>
                        <form method="POST" action="{{ route('finance-partner.applications.reject', $application->id) }}">
                            @csrf
                            <div class="cs-field">
                                <label class="cs-label">{{ __('Rejection reason') }}</label>
                                <textarea name="rejection_reason" class="cs-input" rows="3" required></textarea>
                            </div>
                            <button type="submit" class="cs-btn cs-btn--ghost" style="width:100%;justify-content:center;">{{ __('Reject application') }}</button>
                        </form>
                    </div>
                </div>
            @else
                <div class="cs-card"><div class="cs-card__body">
                    <p class="cs-muted">{{ __('This application has been decided.') }}</p>
                    @if ($application->rejection_reason)
                        <p>{{ __('Reason') }}: {{ $application->rejection_reason }}</p>
                    @endif
                    @if ($application->approved_amount)
                        <p>{{ __('Approved') }}: <span class="cs-amt">KES {{ number_format($application->approved_amount, 2) }}</span></p>
                    @endif
                </div></div>
            @endif

            {{-- Disbursement — same pipeline as approval, no trip to Facilities --}}
            @if ($facility)
                <div class="cs-card" id="disburse-card">
                    <div class="cs-card__head"><h2 class="cs-card__title">{{ __('Disbursement') }}</h2></div>
                    <div class="cs-card__body">
                        @php $pds = $facility->disbursement_status ?? 'disbursed'; @endphp
                        @if ($pds === 'disbursed')
                            <p><span class="cs-badge is-paid">{{ __('Disbursed') }}</span></p>
                            <p class="cs-muted" style="font-size:12.5px;margin-top:6px;">
                                {{ __('Released via') }} <strong>{{ $facility->disbursement_channel === 'mpesa' ? 'M-Pesa' : __('Bank / manual') }}</strong>
                                @if ($facility->disbursed_at) · {{ optional($facility->disbursed_at)->format('d M Y H:i') }} @endif
                                @if ($facility->disbursement_reference) · {{ __('Ref') }} {{ $facility->disbursement_reference }} @endif
                            </p>
                        @elseif ($pds === 'pending_confirmation')
                            <p><span class="cs-badge is-pending">{{ __('Awaiting payee confirmation') }}</span></p>
                            <p class="cs-muted" style="font-size:12.5px;margin-top:6px;">
                                {{ __('Recorded via') }} <strong>{{ $facility->disbursement_channel === 'mpesa' ? 'M-Pesa' : __('Bank / manual') }}</strong>
                                @if ($facility->disbursement_reference) · {{ __('Ref') }} {{ $facility->disbursement_reference }} @endif.
                                {{ __('The payee confirms receipt to release the facility for repayment.') }}
                            </p>
                        @else
                            <p class="cs-muted" style="font-size:12.5px;margin-bottom:12px;">
                                {{ __('You send the facility funds to the payee yourself (M-Pesa or bank) — this just records that you did, and how. The payee then confirms receipt to release the facility for repayment.') }}
                            </p>

                            @if ($payee)
                                @php $hasMpesa = (bool) $payee['mpesa']; $hasBank = (bool) $payee['bank']; @endphp
                                <div style="background:#F0F7FD;border:0.5px solid #B3D5F5;border-radius:11px;padding:14px 16px;margin-bottom:14px;">
                                    <div style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:#0F3C7A;opacity:.8;margin-bottom:10px;">{{ __('Where to send this disbursal') }}</div>
                                    <div style="display:flex;justify-content:space-between;gap:12px;margin-bottom:6px;">
                                        <span style="font-size:12.5px;color:#374151;">{{ __('Payee') }}</span>
                                        <strong style="font-size:12.5px;color:#0F3C7A;">{{ $payee['name'] }}</strong>
                                    </div>
                                    <div style="display:flex;justify-content:space-between;gap:12px;margin-bottom:12px;">
                                        <span style="font-size:12.5px;color:#374151;">{{ __('Amount to send') }}</span>
                                        <strong style="font-size:15px;color:#0B5940;">KES {{ number_format($payee['amount'], 2) }}</strong>
                                    </div>

                                    <div style="display:flex;flex-direction:column;gap:0;">
                                        @if ($hasMpesa)
                                            <div style="background:#fff;border:0.5px solid #CFE3F7;border-radius:9px;padding:11px 13px;">
                                                <div style="font-size:10.5px;font-weight:700;letter-spacing:.08em;color:#185FA5;margin-bottom:7px;">{{ __('PAY BY M-PESA') }}</div>
                                                <div style="display:flex;justify-content:space-between;gap:12px;margin-bottom:4px;">
                                                    <span style="font-size:12px;color:#6b7280;">{{ $payee['mpesa']->account_type === 'PAYBILL' ? __('Paybill') : __('Till number') }}</span>
                                                    <strong style="font-size:13px;color:#0F3C7A;font-family:monospace;">{{ $payee['mpesa']->account_type === 'PAYBILL' ? $payee['mpesa']->paybill : $payee['mpesa']->till_number }}</strong>
                                                </div>
                                                @if ($payee['mpesa']->account_type === 'PAYBILL')
                                                    <div style="display:flex;justify-content:space-between;gap:12px;">
                                                        <span style="font-size:12px;color:#6b7280;">{{ __('Account / Reference') }}</span>
                                                        <strong style="font-size:13px;color:#0F3C7A;font-family:monospace;">{{ $payee['reference'] }}</strong>
                                                    </div>
                                                @endif
                                            </div>
                                        @endif

                                        @if ($hasMpesa && $hasBank)
                                            <div style="display:flex;align-items:center;gap:10px;margin:8px 2px;">
                                                <span style="flex:1;height:1px;background:#CFE3F7;"></span>
                                                <span style="font-size:10.5px;font-weight:700;letter-spacing:.08em;color:#8aa6c4;">{{ __('OR') }}</span>
                                                <span style="flex:1;height:1px;background:#CFE3F7;"></span>
                                            </div>
                                        @endif

                                        @if ($hasBank)
                                            <div style="background:#fff;border:0.5px solid #CFE3F7;border-radius:9px;padding:11px 13px;">
                                                <div style="font-size:10.5px;font-weight:700;letter-spacing:.08em;color:#185FA5;margin-bottom:7px;">{{ __('PAY BY BANK') }}</div>
                                                <div style="font-size:13px;font-weight:600;color:#0F3C7A;white-space:pre-line;line-height:1.55;">{{ $payee['bank'] }}</div>
                                                <div style="font-size:11.5px;color:#6b7280;margin-top:6px;">{{ __('Reference') }}: <strong style="font-family:monospace;color:#0F3C7A;">{{ $payee['reference'] }}</strong></div>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="cs-muted" style="font-size:11px;margin-top:10px;line-height:1.5;">{{ __('Send the exact amount and quote the reference so we can match your payment. Disbursements are always paid to Centresidence as the installer.') }}</div>
                                </div>
                            @endif
                            <form method="POST" action="{{ route('finance-partner.facilities.record-disbursement', $facility->id) }}"
                                  data-cs-confirm="{{ __('Record that you have released these funds to the payee? They confirm receipt to release the facility for repayment.') }}" data-cs-confirm-title="{{ __('Record disbursement?') }}" data-cs-confirm-ok="{{ __('Yes, record') }}">
                                @csrf
                                <div class="cs-field">
                                    <label class="cs-label">{{ __('How you sent it') }}</label>
                                    <select name="disbursement_channel" class="cs-input">
                                        <option value="mpesa">{{ __('M-Pesa') }}</option>
                                        <option value="bank">{{ __('Bank / manual') }}</option>
                                    </select>
                                </div>
                                <div class="cs-field">
                                    <label class="cs-label">{{ __('Reference') }} <span class="cs-muted">({{ __('optional') }})</span></label>
                                    <input name="disbursement_reference" class="cs-input" placeholder="DISB-{{ $facility->facility_number }}">
                                    <div class="cs-muted" style="font-size:11px;margin-top:4px;">{{ __('Leave blank to auto-number from the facility. Enter the real M-Pesa / bank code if you have it.') }}</div>
                                </div>
                                <button type="submit" class="cs-btn cs-btn--primary" style="width:100%;justify-content:center;">{{ __('Record disbursement') }}</button>
                            </form>
                        @endif
                    </div>
                </div>
            @endif

            <div class="cs-card">
                <div class="cs-card__head"><h2 class="cs-card__title">{{ __('History') }}</h2></div>
                <div class="cs-card__body">
                    @forelse ($application->statusHistory as $h)
                        <div style="font-size:12.5px;color:var(--gray-700);padding:6px 0;border-bottom:0.5px solid var(--gray-100);">
                            <strong>{{ ucfirst(str_replace('_', ' ', $h->to_status)) }}</strong>
                            <span class="cs-muted">— {{ optional($h->created_at)->format('d M Y H:i') }}</span>
                            @if ($h->change_reason) <div class="cs-muted">{{ $h->change_reason }}</div> @endif
                        </div>
                    @empty
                        <p class="cs-muted">{{ __('No history.') }}</p>
                    @endforelse

                    {{-- Disbursement is a separate status line from approval — surface it here too --}}
                    @if ($facility)
                        @php $dch = $facility->disbursement_channel === 'mpesa' ? 'M-Pesa' : __('Bank / manual'); @endphp
                        @if (($facility->disbursement_status ?? null) === 'pending_confirmation')
                            <div style="font-size:12.5px;color:var(--gray-700);padding:6px 0;border-bottom:0.5px solid var(--gray-100);">
                                <strong>{{ __('Disbursement recorded') }}</strong>
                                <span class="cs-muted">— {{ optional($facility->updated_at)->format('d M Y H:i') }}</span>
                                <div class="cs-muted">{{ $dch }} · {{ __('awaiting payee confirmation') }}@if ($facility->disbursement_reference) · {{ __('Ref') }} {{ $facility->disbursement_reference }}@endif</div>
                            </div>
                        @elseif (($facility->disbursement_status ?? null) === 'disbursed')
                            <div style="font-size:12.5px;color:var(--gray-700);padding:6px 0;border-bottom:0.5px solid var(--gray-100);">
                                <strong>{{ __('Disbursed') }}</strong>
                                <span class="cs-muted">— {{ optional($facility->disbursed_at)->format('d M Y H:i') }}</span>
                                <div class="cs-muted">{{ $dch }}@if ($facility->disbursement_reference) · {{ __('Ref') }} {{ $facility->disbursement_reference }}@endif</div>
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if (session('scroll_to_disburse'))
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var el = document.getElementById('disburse-card');
                if (el) {
                    el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    el.style.transition = 'box-shadow .3s';
                    el.style.boxShadow = '0 0 0 3px rgba(29,158,117,.35)';
                    setTimeout(function () { el.style.boxShadow = ''; }, 1800);
                }
            });
        </script>
    @endif
@endsection
