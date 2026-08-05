@extends('admin.layouts.app')

@section('content')
<div class="main-content"><div class="page-content"><div class="container-fluid">
    <div class="page-content-wrapper bg-white p-30 radius-20">

        @include('admin.centresidence._nav', ['active' => 'index'])

        @php
            $cards = [
                ['Active facilities', number_format($metrics['active_facilities']), 'var(--blue)'],
                ['Outstanding principal', 'KES ' . number_format($metrics['outstanding_principal'], 2), 'var(--green)'],
                ['Expected monthly', 'KES ' . number_format($metrics['expected_monthly'], 2), 'var(--purple)'],
                ['Facilities in default', number_format($metrics['facilities_in_default']), 'var(--red)'],
                ['Pending applications', number_format($metrics['pending_applications']), 'var(--amber)'],
                ['Finance partners', number_format($metrics['partners']), 'var(--blue)'],
                ['Platform fees (total)', 'KES ' . number_format($metrics['platform_fees'], 2), 'var(--green)'],
                ['Commission billed', 'KES ' . number_format($metrics['commission_metered'] + $metrics['commission_non_metered'], 2), 'var(--purple)'],
                ['Fallbacks active', number_format($metrics['fallback_active']), 'var(--red)'],
                ['Active modules', number_format($metrics['active_modules']), 'var(--blue)'],
                ['Active devices', number_format($metrics['active_devices']), 'var(--green)'],
                ['Gateways', number_format($metrics['gateways']), 'var(--purple)'],
            ];
        @endphp

        <div class="cs-stats">
            @foreach ($cards as [$label, $value, $color])
                <div class="cs-stat">
                    <span class="cs-stat__dot" style="background:{{ $color }};"></span>
                    <div class="cs-stat__value">{{ $value }}</div>
                    <div class="cs-stat__label">{{ __($label) }}</div>
                </div>
            @endforeach
        </div>

        <div class="row">
            <div class="col-lg-6">
                <div class="cs-card">
                    <div class="cs-card__head"><h2 class="cs-card__title">{{ __('Recent facilities') }}</h2></div>
                    <div class="cs-tablewrap">
                        <table class="cs-table">
                            <thead><tr><th>{{ __('Facility') }}</th><th>{{ __('Partner') }}</th><th>{{ __('Outstanding') }}</th><th>{{ __('Status') }}</th></tr></thead>
                            <tbody>
                                @forelse ($recentFacilities as $f)
                                    <tr>
                                        <td>{{ $f->facility_number ?? ('#' . $f->id) }}</td>
                                        <td>{{ optional($f->partner)->company_name ?? '—' }}</td>
                                        <td class="cs-amt">KES {{ number_format($f->outstanding_principal, 2) }}</td>
                                        <td>@include('admin.centresidence._status', ['status' => $f->status])</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="cs-empty">{{ __('No facilities yet') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="cs-card">
                    <div class="cs-card__head"><h2 class="cs-card__title">{{ __('Recent applications') }}</h2></div>
                    <div class="cs-tablewrap">
                        <table class="cs-table">
                            <thead><tr><th>{{ __('Reference') }}</th><th>{{ __('Partner') }}</th><th>{{ __('Requested') }}</th><th>{{ __('Status') }}</th></tr></thead>
                            <tbody>
                                @forelse ($recentApplications as $a)
                                    <tr>
                                        <td>{{ $a->application_number ?? ('#' . $a->id) }}</td>
                                        <td>{{ optional($a->partner)->company_name ?? '—' }}</td>
                                        <td class="cs-amt">KES {{ number_format($a->requested_amount, 2) }}</td>
                                        <td>@include('admin.centresidence._status', ['status' => $a->status])</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="cs-empty">{{ __('No applications yet') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div></div></div>
@endsection
