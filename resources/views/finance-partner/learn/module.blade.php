@extends('finance-partner.layouts.app')

@section('content')
    @php $color = $module->displayColor(); @endphp
    <div class="cs-titlebar">
        <div>
            <h1 class="cs-title">{{ $module->name }}</h1>
            <ol class="cs-crumb"><li><a href="{{ route('finance-partner.learn.modules') }}">{{ __('Modules') }}</a></li><li>›</li><li>{{ $module->name }}</li></ol>
        </div>
        <a href="{{ route('finance-partner.products.create') }}" class="cs-btn cs-btn--primary">{{ __('Create a product for this') }}</a>
    </div>

    <div class="cs-hero" style="background:linear-gradient(135deg, {{ $color }}, {{ $color }}cc);">
        <i class="{{ $module->displayIcon() }}"></i>
        <div>
            <div class="cs-hero__name">{{ $module->name }}</div>
            <div class="cs-hero__tag">{{ $module->tagline ?? $module->description }}</div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            @if ($module->description)
                <div class="cs-card"><div class="cs-card__head"><h2 class="cs-card__title">{{ __('What it is') }}</h2></div>
                    <div class="cs-card__body" style="font-size:13.5px;color:var(--gray-700);">{!! nl2br(e($module->description)) !!}</div>
                </div>
            @endif

            <div class="cs-card"><div class="cs-card__head"><h2 class="cs-card__title">{{ __('The financing opportunity') }}</h2></div>
                <div class="cs-card__body" style="font-size:13.5px;color:var(--gray-700);">
                    {!! $module->financier_overview ? nl2br(e($module->financier_overview)) : __('This module generates prepaid, rent-secured income for the owner — repayment is collected at source from rent before it reaches the owner, which lowers default risk for the financier.') !!}
                </div>
            </div>

            <div class="cs-card"><div class="cs-card__head"><h2 class="cs-card__title">{{ __('How a facility works') }}</h2></div>
                <div class="cs-card__body">
                    <ol class="cs-steps">
                        <li>{{ __('You publish a product for this module (interest rate, tenor, amount limits, max rent-deduction %).') }}</li>
                        <li>{{ __('An owner applies; soft underwriting runs against your rules and their property cashflow.') }}</li>
                        <li>{{ __('On approval, a facility + repayment schedule are created and Centresidence installs the hardware.') }}</li>
                        <li>{{ __('Repayment is deducted at source from the owner\'s rent each cycle and settled to you — pausing once the monthly target is met.') }}</li>
                        <li>{{ __('Interest follows your product type: reducing-balance accrues over time; flat is pre-booked.') }}</li>
                    </ol>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="cs-card"><div class="cs-card__head"><h2 class="cs-card__title">{{ __('Market intelligence') }}</h2></div>
                <div class="cs-card__body">
                    @if (!empty($leaders))
                        <div style="display:flex;flex-wrap:wrap;gap:5px;margin-bottom:12px;">
                            @foreach ($leaders as $lead)
                                <span class="cs-badge is-blue" style="font-size:10.5px;"><i class="{{ $lead['icon'] }}"></i> {{ __($lead['label']) }}</span>
                            @endforeach
                        </div>
                    @endif

                    @if (($stats['applications'] ?? 0) === 0 && ($stats['facilities_total'] ?? 0) === 0)
                        <p class="cs-muted" style="margin-bottom:14px;">{{ __('Gathering data — this module has no applications yet. Be early: publish a product and capture the demand as it builds.') }}</p>
                    @else
                        <div class="cs-costline"><span>{{ __('Owner demand') }}</span><b>{{ $stats['applications'] ?? 0 }} {{ trans_choice('application|applications', $stats['applications'] ?? 0) }}</b></div>
                        <div class="cs-costline"><span>{{ __('Uptake (share of demand)') }}</span><b>{{ isset($stats['uptake_pct']) ? $stats['uptake_pct'].'%' : '—' }}</b></div>
                        <div class="cs-costline"><span>{{ __('Approval rate') }}</span><b>{{ isset($stats['approval_rate']) ? $stats['approval_rate'].'%' : '—' }}</b></div>
                        <div class="cs-costline"><span>{{ __('Active facilities') }}</span><b>{{ $stats['facilities_active'] ?? 0 }}</b></div>
                        <div class="cs-costline"><span>{{ __('Repayment health') }}</span><b>{{ isset($stats['repayment_health']) ? $stats['repayment_health'].'%' : '—' }}</b></div>
                        @if (!empty($stats['outstanding']))
                            <div class="cs-costline"><span>{{ __('Outstanding financed') }}</span><b>KES {{ number_format($stats['outstanding'], 0) }}</b></div>
                        @endif
                        @if (!empty($stats['avg_interest']))
                            <div class="cs-costline"><span>{{ __('Avg rate offered') }}</span><b>{{ number_format($stats['avg_interest'], 1) }}%</b></div>
                        @endif
                        @if (!empty($stats['avg_ticket']))
                            <div class="cs-costline"><span>{{ __('Avg facility size') }}</span><b>KES {{ number_format($stats['avg_ticket'], 0) }}</b></div>
                        @endif
                    @endif
                    <p class="cs-muted" style="font-size:10.5px;margin-top:10px;">{{ __('Platform-wide figures across all partners. Repayment health = share of facilities not in default.') }}</p>
                </div>
            </div>

            <div class="cs-card"><div class="cs-card__head"><h2 class="cs-card__title">{{ __('Deployment cost') }}</h2></div>
                <div class="cs-card__body">
                    <div class="cs-costline"><span>{{ __('Type') }}</span><b>{{ $module->is_metered ? __('Metered') : __('Non-metered') }}</b></div>
                    @if ($catalogue)
                        <div class="cs-costline"><span>{{ __('Hardware') }}</span><b>KES {{ number_format($catalogue->unit_price, 2) }}</b></div>
                        <div class="cs-costline"><span>{{ __('Installation') }}</span><b>KES {{ number_format($catalogue->installation_cost, 2) }}</b></div>
                        <div class="cs-costline cs-costline--total"><span>{{ __('Per-unit deployment') }}</span><b>KES {{ number_format($catalogue->unit_price + $catalogue->installation_cost, 2) }}</b></div>
                    @endif
                    <div style="margin-top:14px;">
                        @if ($youFinance)
                            <span class="cs-badge is-paid">{{ __('You already offer a product for this') }}</span>
                        @else
                            <a href="{{ route('finance-partner.products.create') }}" class="cs-btn cs-btn--primary" style="width:100%;justify-content:center;">{{ __('Offer financing for this') }}</a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
