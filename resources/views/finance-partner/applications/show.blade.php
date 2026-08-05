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
                </div>
            </div>
        </div>
    </div>
@endsection
