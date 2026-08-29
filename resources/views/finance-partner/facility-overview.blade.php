@extends('finance-partner.layouts.app')

@section('content')
    @php
        $statusBadge = ['active' => 'is-paid', 'completed' => 'is-paid', 'suspended' => 'is-pending', 'defaulted' => 'is-danger', 'written_off' => 'is-danger'][$facility->status] ?? 'is-grey';
        $pds = $facility->disbursement_status ?? 'disbursed';
    @endphp

    <div class="cs-titlebar">
        <div>
            <h1 class="cs-title">{{ $facility->facility_number ?? ('#' . $facility->id) }}</h1>
            <ol class="cs-crumb">
                <li><a href="{{ route('finance-partner.facilities') }}">{{ __('Facilities') }}</a></li>
                <li>›</li><li>{{ __('Overview') }}</li>
            </ol>
        </div>
        <span class="cs-badge {{ $statusBadge }}">{{ ucfirst(str_replace('_', ' ', $facility->status)) }}</span>
    </div>

    {{-- ── Principal drawdown — the lead: see the loan amortise ───────────────── --}}
    @php
        $principal = (float) $facility->principal_amount;
        $outstanding = (float) $facility->outstanding_principal;
    @endphp
    <div class="cs-card fo-hero">
        <div class="fo-hero__main">
            <div class="fo-hero__label">{{ __('Outstanding principal') }}</div>
            <div class="fo-hero__value">KES {{ number_format($outstanding, 2) }}</div>
            <div class="fo-bar" role="progressbar" aria-valuenow="{{ $pctRepaid }}" aria-valuemin="0" aria-valuemax="100">
                <div class="fo-bar__fill" style="width: {{ min($pctRepaid, 100) }}%;"></div>
            </div>
            <div class="fo-hero__foot">
                <span><strong>{{ $pctRepaid }}%</strong> {{ __('of principal repaid') }}</span>
                <span class="cs-muted">{{ __('KES :repaid of :principal', ['repaid' => number_format($principalRepaid, 2), 'principal' => number_format($principal, 2)]) }}</span>
            </div>
        </div>
        <div class="fo-hero__side">
            <div class="fo-mini">
                <span class="fo-mini__label">{{ __('Financed') }}</span>
                <span class="fo-mini__val">KES {{ number_format($principal, 0) }}</span>
            </div>
            <div class="fo-mini">
                <span class="fo-mini__label">{{ __('Total repayable') }}</span>
                <span class="fo-mini__val">KES {{ number_format($facility->total_repayable, 0) }}</span>
                <span class="fo-mini__sub">{{ __('incl. KES :int interest', ['int' => number_format($costOfFinance, 0)]) }}</span>
            </div>
        </div>
    </div>

    {{-- ── Servicing summary ─────────────────────────────────────────────────── --}}
    <div class="fo-grid">
        <div class="fo-stat">
            <span class="fo-stat__label">{{ __('Collected to date') }}</span>
            <span class="fo-stat__val">KES {{ number_format($totalCollected, 2) }}</span>
            <span class="fo-stat__sub">{{ __('KES :int interest · KES :pen penalty/fee', ['int' => number_format($interestCollected, 2), 'pen' => number_format($penaltyCollected, 2)]) }}</span>
        </div>
        <div class="fo-stat">
            <span class="fo-stat__label">{{ __('Remitted to you') }}</span>
            <span class="fo-stat__val">KES {{ number_format($totalRemitted, 2) }}</span>
            @php $awaiting = max($totalCollected - $totalRemitted, 0); @endphp
            <span class="fo-stat__sub">{{ $awaiting > 0 ? __('KES :amt awaiting remittance', ['amt' => number_format($awaiting, 2)]) : __('fully remitted') }}</span>
        </div>
        <div class="fo-stat">
            <span class="fo-stat__label">{{ __('Monthly target') }}</span>
            <span class="fo-stat__val">KES {{ number_format($facility->monthly_target, 2) }}</span>
            <span class="fo-stat__sub">{{ $facility->accelerated_repayment ? __('accelerated') : __('standard pace') }}</span>
        </div>
        <div class="fo-stat">
            <span class="fo-stat__label">{{ __('Next payment') }}</span>
            @if ($nextDue)
                <span class="fo-stat__val">{{ optional($nextDue->due_date)->format('d M Y') }}</span>
                <span class="fo-stat__sub">KES {{ number_format($nextDue->total_due, 2) }} · {{ __('period') }} {{ $nextDue->period_number }}/{{ $facility->repayment_months }}</span>
            @else
                <span class="fo-stat__val">—</span>
                <span class="fo-stat__sub">{{ $facility->status === 'completed' ? __('cleared') : __('no schedule') }}</span>
            @endif
        </div>
        <div class="fo-stat {{ $arrears->count() ? 'fo-stat--alert' : '' }}">
            <span class="fo-stat__label">{{ __('Arrears') }}</span>
            <span class="fo-stat__val">{{ $arrears->count() }}</span>
            <span class="fo-stat__sub">{{ $arrears->count() ? __('KES :amt overdue', ['amt' => number_format($arrears->sum(fn ($s) => (float) $s->total_due - (float) $s->total_paid), 2)]) : __('none — on track') }}</span>
        </div>
        <div class="fo-stat">
            <span class="fo-stat__label">{{ __('Disbursement') }}</span>
            <span class="fo-stat__val" style="font-size:15px;">
                @if ($pds === 'disbursed') <span class="cs-badge is-paid">{{ __('Disbursed') }}</span>
                @elseif ($pds === 'pending_confirmation') <span class="cs-badge is-pending">{{ __('Awaiting confirmation') }}</span>
                @else <span class="cs-badge is-grey">{{ __('Awaiting') }}</span> @endif
            </span>
            <span class="fo-stat__sub">{{ optional($facility->disbursed_at)->format('d M Y') ?? __('not yet released') }}</span>
        </div>
    </div>

    {{-- ── Repayment schedule (contract amortisation) ───────────────────────── --}}
    <div class="cs-card">
        <div class="cs-card__head" style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;">
            <h2 class="cs-card__title">{{ __('Repayment schedule') }}</h2>
            <span class="cs-muted" style="font-size:11.5px;">{{ __('Contract amortisation — the outstanding drops as principal is repaid each period. Actual pace follows rent collections.') }}</span>
        </div>
        <div class="cs-tablewrap" style="max-height:420px;overflow-y:auto;">
            <table class="cs-table" style="font-size:12.5px;">
                <thead><tr>
                    <th>#</th><th>{{ __('Due') }}</th><th style="text-align:right;">{{ __('Opening') }}</th>
                    <th style="text-align:right;">{{ __('Principal') }}</th><th style="text-align:right;">{{ __('Interest') }}</th>
                    <th style="text-align:right;">{{ __('Due') }}</th><th style="text-align:right;">{{ __('Paid') }}</th>
                    <th style="text-align:right;">{{ __('Closing') }}</th><th>{{ __('Status') }}</th>
                </tr></thead>
                <tbody>
                    @forelse ($facility->schedules as $s)
                        @php
                            $sb = ['paid' => 'is-paid', 'partial' => 'is-pending', 'pending' => 'is-grey', 'overdue' => 'is-danger'][$s->status] ?? 'is-grey';
                            $isNext = $nextDue && $nextDue->id === $s->id;
                            // One-line scannable status: coloured left accent + subtle tint.
                            $rowStyle = [
                                'paid'    => 'box-shadow:inset 3px 0 0 #1D9E75;background:#F6FCF9;',
                                'partial' => 'box-shadow:inset 3px 0 0 #D97706;background:#FFFBF0;',
                                'overdue' => 'box-shadow:inset 3px 0 0 #DC2626;background:#FEF4F2;',
                            ][$s->status] ?? ($isNext ? 'box-shadow:inset 3px 0 0 #185FA5;background:#F4F9FE;' : '');
                        @endphp
                        <tr style="{{ $rowStyle }}">
                            <td>{{ $s->period_number }}</td>
                            <td style="white-space:nowrap;">{{ optional($s->due_date)->format('d M Y') }}</td>
                            <td style="text-align:right;font-variant-numeric:tabular-nums;">{{ number_format($s->opening_balance, 2) }}</td>
                            <td style="text-align:right;font-variant-numeric:tabular-nums;">{{ number_format($s->principal_due, 2) }}</td>
                            <td style="text-align:right;font-variant-numeric:tabular-nums;">{{ number_format($s->interest_due, 2) }}</td>
                            <td style="text-align:right;font-variant-numeric:tabular-nums;">{{ number_format($s->total_due, 2) }}</td>
                            <td style="text-align:right;font-variant-numeric:tabular-nums;{{ $s->total_paid > 0 ? 'color:#0B5940;font-weight:600;' : 'color:#9ca3af;' }}">{{ number_format($s->total_paid, 2) }}</td>
                            <td style="text-align:right;font-variant-numeric:tabular-nums;">{{ number_format($s->closing_balance, 2) }}</td>
                            <td><span class="cs-badge {{ $sb }}">{{ ucfirst($s->status) }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="cs-empty">{{ __('No schedule generated.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ── Collections timeline (actual money in) ───────────────────────────── --}}
    @php
        $typeLabels = [
            'rent_deduction_principal' => __('Principal'),
            'rent_deduction_interest'  => __('Interest'),
            'rent_deduction_penalty'   => __('Penalty / fee'),
        ];
    @endphp
    <div class="cs-card">
        <div class="cs-card__head"><h2 class="cs-card__title">{{ __('Collections') }} <span class="cs-muted" style="font-weight:400;font-size:12px;">({{ $collections->count() }})</span></h2></div>
        <div class="cs-tablewrap">
            <table class="cs-table" style="font-size:12.5px;">
                <thead><tr>
                    <th>{{ __('Date') }}</th><th>{{ __('Type') }}</th><th>{{ __('From rent payment') }}</th>
                    <th>{{ __('Reconciliation') }}</th><th style="text-align:right;">{{ __('Amount') }}</th>
                </tr></thead>
                <tbody>
                    @forelse ($collections as $c)
                        @php $rb = ['reconciled' => 'is-paid', 'pending' => 'is-pending'][$c->reconciliation_status] ?? 'is-grey'; @endphp
                        <tr>
                            <td style="white-space:nowrap;">{{ optional($c->settled_at ?? $c->created_at)->format('d M Y H:i') }}</td>
                            <td>{{ $typeLabels[$c->transaction_type] ?? ucfirst(str_replace('_', ' ', $c->transaction_type)) }}</td>
                            <td>{{ $c->rent_transaction_id ? ('#' . $c->rent_transaction_id) : '—' }}</td>
                            <td><span class="cs-badge {{ $rb }}">{{ ucfirst($c->reconciliation_status) }}</span></td>
                            <td style="text-align:right;font-variant-numeric:tabular-nums;">KES {{ number_format($c->amount, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="cs-empty">{{ __('No collections yet — nothing has been serviced from rent.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ── Remittances that carried this facility ───────────────────────────── --}}
    <div class="cs-card">
        <div class="cs-card__head"><h2 class="cs-card__title">{{ __('Remittances for this facility') }}</h2></div>
        <div class="cs-card__body cs-muted" style="font-size:12px;">{{ __('When you were paid for this facility’s repayments.') }}</div>
        <div class="cs-tablewrap">
            <table class="cs-table" style="font-size:12.5px;">
                <thead><tr>
                    <th>{{ __('Batch') }}</th><th>{{ __('Date') }}</th><th>{{ __('Method') }}</th>
                    <th>{{ __('Status') }}</th><th style="text-align:right;">{{ __('This facility') }}</th>
                </tr></thead>
                <tbody>
                    @forelse ($remittanceItems as $it)
                        @php
                            $b = $it->batch; $bb = ['prepared' => 'is-grey', 'sent' => 'is-pending', 'confirmed' => 'is-paid', 'failed' => 'is-danger'][optional($b)->status] ?? 'is-grey';
                            $bFees = $b ? (float) $b->servicing_fee + (float) $b->origination_fee : 0;
                        @endphp
                        <tr>
                            <td>
                                {{ optional($b)->batch_number ?? '—' }}
                                @if ($bFees > 0)
                                    <span class="cs-muted" style="display:block;font-size:10.5px;">{{ __('batch net KES :net of :gross after fees', ['net' => number_format((float) $b->net_amount, 0), 'gross' => number_format((float) $b->gross_amount, 0)]) }}</span>
                                @endif
                            </td>
                            <td style="white-space:nowrap;">{{ optional(optional($b)->remittance_date)->format('d M Y') ?? '—' }}</td>
                            <td>{{ str_replace('_', ' ', optional($b)->settlement_method ?? '—') }}</td>
                            <td>@if ($b)<span class="cs-badge {{ $bb }}">{{ ucfirst($b->status) }}</span>@else — @endif</td>
                            <td style="text-align:right;font-variant-numeric:tabular-nums;">KES {{ number_format($it->amount, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="cs-empty">{{ __('Not yet remitted.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ── Centresidence fees on this facility (transparent deductions) ─────────── --}}
    <div class="cs-card">
        <div class="cs-card__head"><h2 class="cs-card__title">{{ __('Centresidence fees') }}</h2></div>
        <div class="cs-card__body cs-muted" style="font-size:12px;">{{ __('What Centresidence earns on this facility. Nothing is invoiced separately — fees are netted from your remittances.') }}</div>
        <div class="fo-grid" style="padding:0 20px 18px;">
            <div class="fo-stat">
                <span class="fo-stat__label">{{ __('Origination fee') }}</span>
                <span class="fo-stat__val">KES {{ number_format($originationFee, 2) }}</span>
                <span class="fo-stat__sub">{{ __('one-time, on the financed amount') }}</span>
            </div>
            <div class="fo-stat">
                <span class="fo-stat__label">{{ __('Origination collected') }}</span>
                <span class="fo-stat__val">KES {{ number_format($originationCollected, 2) }}</span>
                <span class="fo-stat__sub">{{ __('netted so far from remittances') }}</span>
            </div>
            <div class="fo-stat {{ $originationOutstanding > 0 ? 'fo-stat--alert' : '' }}">
                <span class="fo-stat__label">{{ __('Origination outstanding') }}</span>
                <span class="fo-stat__val">KES {{ number_format($originationOutstanding, 2) }}</span>
                <span class="fo-stat__sub">{{ $originationOutstanding > 0 ? __('spread over future remittances') : __('fully collected') }}</span>
            </div>
            <div class="fo-stat">
                <span class="fo-stat__label">{{ __('Servicing fee') }}</span>
                <span class="fo-stat__val">{{ rtrim(rtrim(number_format($servicingRate, 2), '0'), '.') }}%</span>
                <span class="fo-stat__sub">{{ __('of each remittance, at settlement') }}</span>
            </div>
        </div>
    </div>

    <style>
        .fo-hero { display:flex; flex-wrap:wrap; gap:24px; padding:22px 24px; align-items:center; }
        .fo-hero__main { flex:1; min-width:260px; }
        .fo-hero__label { font-size:11px; font-weight:600; text-transform:uppercase; letter-spacing:.07em; color:#185FA5; opacity:.85; margin-bottom:6px; }
        .fo-hero__value { font-size:32px; font-weight:700; color:#0F3C7A; line-height:1.05; margin-bottom:14px; font-variant-numeric:tabular-nums; }
        .fo-bar { height:10px; border-radius:6px; background:#E6EEF7; overflow:hidden; }
        .fo-bar__fill { height:100%; background:linear-gradient(90deg,#1D9E75,#0B5940); border-radius:6px; transition:width .4s; }
        .fo-hero__foot { display:flex; flex-wrap:wrap; justify-content:space-between; gap:8px; margin-top:8px; font-size:12.5px; color:#374151; }
        .fo-hero__side { display:flex; gap:16px; flex-wrap:wrap; }
        .fo-mini { background:#F8FAFC; border:0.5px solid #EEF2F7; border-radius:11px; padding:12px 16px; min-width:120px; }
        .fo-mini__label { display:block; font-size:10.5px; font-weight:600; text-transform:uppercase; letter-spacing:.05em; color:#6b7280; margin-bottom:3px; }
        .fo-mini__val { display:block; font-size:18px; font-weight:700; color:#0F3C7A; }
        .fo-mini__sub { display:block; font-size:11px; color:#9ca3af; margin-top:2px; }
        .fo-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:12px; margin-bottom:16px; }
        .fo-stat { background:#fff; border:0.5px solid var(--gray-200,#e5e7eb); border-radius:12px; padding:14px 16px; }
        .fo-stat--alert { border-color:#F5C4B3; background:#FEF6F3; }
        .fo-stat__label { display:block; font-size:10.5px; font-weight:600; text-transform:uppercase; letter-spacing:.05em; color:#6b7280; margin-bottom:5px; }
        .fo-stat__val { display:block; font-size:19px; font-weight:700; color:#111827; line-height:1.15; font-variant-numeric:tabular-nums; }
        .fo-stat__sub { display:block; font-size:11px; color:#9ca3af; margin-top:3px; }
    </style>
@endsection
