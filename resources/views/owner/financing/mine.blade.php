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
                        <th>{{ __('Payoff today') }}</th><th>{{ __('Mode') }}</th><th>{{ __('Status') }}</th><th></th>
                    </tr></thead>
                    <tbody>
                        @forelse ($facilities as $f)
                            <tr>
                                <td>
                                    {{ $f->facility_number ?? ('#' . $f->id) }}
                                    @if (in_array($f->down_payment_status ?? 'not_required', ['pending', 'failed']))
                                        <div><span class="cs-badge is-pending" style="margin-top:4px;">{{ __('Down-payment') }} KES {{ number_format($f->owner_contribution, 0) }} {{ $f->down_payment_status === 'failed' ? __('failed — check your phone') : __('pending') }}</span></div>
                                    @elseif (($f->down_payment_status ?? '') === 'collected' && $f->owner_contribution > 0)
                                        <div><span class="cs-badge is-paid" style="margin-top:4px;">{{ __('Down-payment paid') }}</span></div>
                                    @endif
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
                                <td style="white-space:nowrap;">
                                    @if ($f->status === 'active')
                                        <form method="POST" action="{{ route('owner.financing.accelerate', $f->id) }}" style="display:inline;">
                                            @csrf
                                            <button class="cs-btn cs-btn--ghost cs-btn--sm" type="submit">
                                                {{ $f->accelerated_repayment ? __('Set standard') : __('Accelerate') }}
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('owner.financing.settle-early', $f->id) }}" style="display:inline;"
                                              onsubmit="return confirm('{{ __('Settle this facility early for KES ') }}{{ number_format($f->payoff ?? 0, 2) }}?');">
                                            @csrf
                                            <button class="cs-btn cs-btn--complete cs-btn--sm" type="submit">{{ __('Settle early') }}</button>
                                        </form>
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
