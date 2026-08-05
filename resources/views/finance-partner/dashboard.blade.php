@extends('finance-partner.layouts.app')

@section('content')
    <div class="cs-titlebar"><h1 class="cs-title">{{ __('Welcome') }}, {{ $partner->company_name }}</h1></div>

    @php
        $cards = [
            ['Products', number_format($metrics['products']), 'var(--blue)'],
            ['Pending applications', number_format($metrics['pending']), 'var(--amber)'],
            ['Active facilities', number_format($metrics['active_facilities']), 'var(--green)'],
            ['Outstanding principal', 'KES ' . number_format($metrics['outstanding'], 2), 'var(--purple)'],
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

    <div class="cs-card">
        <div class="cs-card__head">
            <h2 class="cs-card__title">{{ __('Recent applications') }}</h2>
            <a href="{{ route('finance-partner.applications.index') }}" class="cs-btn cs-btn--ghost cs-btn--sm">{{ __('View all') }}</a>
        </div>
        <div class="cs-tablewrap">
            <table class="cs-table">
                <thead><tr>
                    <th>{{ __('Reference') }}</th><th>{{ __('Owner') }}</th><th>{{ __('Module') }}</th>
                    <th>{{ __('Requested') }}</th><th>{{ __('Status') }}</th><th></th>
                </tr></thead>
                <tbody>
                    @forelse ($recentApplications as $a)
                        <tr>
                            <td>{{ $a->application_number ?? ('#' . $a->id) }}</td>
                            <td>{{ optional($a->owner)->name ?? '—' }}</td>
                            <td>{{ optional($a->module)->name ?? '—' }}</td>
                            <td class="cs-amt">KES {{ number_format($a->requested_amount, 2) }}</td>
                            <td>@include('admin.centresidence._status', ['status' => $a->status])</td>
                            <td><a href="{{ route('finance-partner.applications.show', $a->id) }}" class="cs-btn cs-btn--primary cs-btn--sm">{{ __('Review') }}</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="cs-empty">{{ __('No applications yet') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
