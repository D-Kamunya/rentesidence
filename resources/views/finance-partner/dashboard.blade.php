@extends('finance-partner.layouts.app')

@section('content')
    <div class="cs-titlebar"><h1 class="cs-title">{{ __('Welcome') }}, {{ $partner->company_name }}</h1></div>

    @unless ($partner->hasPayoutAccount())
        <div class="cs-alert is-pending" style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;">
            <span>{{ __('Set your payout account so we can settle your repayments — it’s required before you can publish a product.') }}</span>
            <a href="{{ route('finance-partner.payout-account') }}" class="cs-btn cs-btn--pending cs-btn--sm">{{ __('Set payout account') }}</a>
        </div>
    @endunless

    {{-- Headline metric cards --}}
    @php
        $cards = [
            ['Products', number_format($metrics['products']), 'blue', '<path d="M4 7l8-4 8 4-8 4-8-4zm0 5l8 4 8-4M4 17l8 4 8-4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" fill="none"/>', route('finance-partner.products.index')],
            ['Pending applications', number_format($metrics['pending']), 'amber', '<path d="M8 4h8l4 4v12H4V4h4zm0 0v4h8" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" fill="none"/><path d="M8 13h8M8 17h5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>', route('finance-partner.applications.index')],
            ['Active facilities', number_format($metrics['active_facilities']), 'green', '<path d="M4 19V9m5 10V5m5 14v-7m5 7V8" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" fill="none"/>', route('finance-partner.facilities')],
            ['Outstanding principal', 'KES ' . number_format($metrics['outstanding'], 0), 'purple', '<circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.7" fill="none"/><path d="M12 7v10M9.5 9.5A2.5 2.5 0 0112 8m0 8a2.5 2.5 0 01-2.5-2.5M12 8h2M10 16h2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>', route('finance-partner.facilities')],
        ];
    @endphp
    <div class="cs-statgrid">
        @foreach ($cards as [$label, $value, $tone, $icon, $href])
            <a href="{{ $href }}" class="cs-statcard cs-statcard--{{ $tone }}">
                <span class="cs-statcard__ic"><svg width="22" height="22" viewBox="0 0 24 24">{!! $icon !!}</svg></span>
                <span class="cs-statcard__body">
                    <span class="cs-statcard__value">{{ $value }}</span>
                    <span class="cs-statcard__label">{{ __($label) }}</span>
                </span>
            </a>
        @endforeach
    </div>

    {{-- Portfolio projections --}}
    <div class="cs-card">
        <div class="cs-card__head" style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;">
            <h2 class="cs-card__title">{{ __('Portfolio') }}</h2>
            <span class="cs-muted" style="font-size:11.5px;">{{ __('What you’ve lent, what it will return, and what’s been settled to you.') }}</span>
        </div>
        <div class="cs-card__body">
            <div class="pd-grid">
                <div class="pd-stat">
                    <span class="pd-stat__label">{{ __('Active principal lent') }}</span>
                    <span class="pd-stat__val">KES {{ number_format($portfolio['active_principal'], 0) }}</span>
                </div>
                <div class="pd-stat pd-stat--accent">
                    <span class="pd-stat__label">{{ __('Expected return') }}</span>
                    <span class="pd-stat__val">KES {{ number_format($portfolio['expected_return'], 0) }}</span>
                    <span class="pd-stat__sub">{{ __('incl. KES :i projected interest', ['i' => number_format($portfolio['expected_interest'], 0)]) }}</span>
                </div>
                <div class="pd-stat">
                    <span class="pd-stat__label">{{ __('Total disbursed') }}</span>
                    <span class="pd-stat__val">KES {{ number_format($portfolio['total_disbursed'], 0) }}</span>
                    <span class="pd-stat__sub">{{ $portfolio['completed'] }} {{ trans_choice('facility completed|facilities completed', $portfolio['completed']) }}</span>
                </div>
                <div class="pd-stat">
                    <span class="pd-stat__label">{{ __('Collected to date') }}</span>
                    <span class="pd-stat__val">KES {{ number_format($portfolio['collected'], 0) }}</span>
                </div>
                <div class="pd-stat">
                    <span class="pd-stat__label">{{ __('Remitted to you') }}</span>
                    <span class="pd-stat__val" style="color:#0B5940;">KES {{ number_format($portfolio['remitted'], 0) }}</span>
                </div>
                <div class="pd-stat {{ $portfolio['awaiting_remit'] > 0 ? 'pd-stat--pending' : '' }}">
                    <span class="pd-stat__label">{{ __('Awaiting remittance') }}</span>
                    <span class="pd-stat__val">KES {{ number_format($portfolio['awaiting_remit'], 0) }}</span>
                    <span class="pd-stat__sub"><a href="{{ route('finance-partner.remittances') }}" style="color:#185FA5;">{{ __('View remittances →') }}</a></span>
                </div>
            </div>
        </div>
    </div>

    {{-- Recent applications --}}
    <div class="cs-card">
        <div class="cs-card__head" style="display:flex;align-items:center;justify-content:space-between;gap:12px;">
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

    <style>
        /* Headline metric cards use the shared .cs-statcard component (in _design). */
        .pd-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:12px; }
        .pd-stat { background:#F8FAFC; border:0.5px solid #EEF2F7; border-radius:12px; padding:14px 16px; }
        .pd-stat--accent { background:#F0FBF6; border-color:#9FE1CB; }
        .pd-stat--pending { background:#FDF6EC; border-color:#F5D9A8; }
        .pd-stat__label { display:block; font-size:10.5px; font-weight:600; text-transform:uppercase; letter-spacing:.05em; color:#6b7280; margin-bottom:5px; }
        .pd-stat__val { display:block; font-size:19px; font-weight:700; color:#111827; line-height:1.15; font-variant-numeric:tabular-nums; }
        .pd-stat__sub { display:block; font-size:11px; color:#9ca3af; margin-top:3px; }
    </style>
@endsection
