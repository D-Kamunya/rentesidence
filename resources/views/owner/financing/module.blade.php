@extends('owner.layouts.app')

@section('content')
@php $color = $module->displayColor(); @endphp
<div class="main-content"><div class="page-content"><div class="container-fluid">
    <div class="page-content-wrapper bg-white p-30 radius-20">
        @include('centresidence._design')

        <ol class="cs-crumb" style="margin-bottom:16px;">
            <li><a href="{{ route('owner.dashboard') }}">{{ __('Dashboard') }}</a></li><li>›</li>
            <li><a href="{{ route('owner.financing.index') }}">{{ __('Financing') }}</a></li><li>›</li>
            <li>{{ $module->name }}</li>
        </ol>

        @if (session('error')) <div class="cs-alert is-danger">{{ session('error') }}</div> @endif

        {{-- Hero --}}
        <div class="cs-hero" style="background:linear-gradient(135deg, {{ $color }}, {{ $color }}cc);">
            <div class="cs-hero__icon"><i class="{{ $module->displayIcon() }}"></i></div>
            <div>
                <h1 class="cs-hero__title">{{ $module->name }}</h1>
                <div class="cs-hero__tag">{{ $module->tagline ?? $module->description }}</div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-7">
                {{-- Cashflow benefit --}}
                @if ($module->cashflow_benefit)
                    <div class="cs-card" style="border-left:3px solid {{ $color }};">
                        <div class="cs-card__body">
                            <div class="cs-section__label">{{ __('How it grows your cashflow') }}</div>
                            <p style="font-size:14px;color:var(--gray-700);line-height:1.6;margin:0;">{{ $module->cashflow_benefit }}</p>
                        </div>
                    </div>
                @endif

                {{-- How it works --}}
                @if ($module->how_it_works)
                    <div class="cs-section">
                        <div class="cs-section__label">{{ __('How it works') }}</div>
                        <div class="cs-steps">{{ $module->how_it_works }}</div>
                    </div>
                @endif

                {{-- Benefits --}}
                @if (!empty($module->benefits))
                    <div class="cs-section">
                        <div class="cs-section__label">{{ __('Why owners love it') }}</div>
                        <ul class="cs-benefits">
                            @foreach ($module->benefits as $b)
                                <li><i class="ri-checkbox-circle-fill"></i> <span>{{ $b }}</span></li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>

            {{-- Self-finance summary --}}
            <div class="col-lg-5">
                @if ($catalogue)
                    <div class="cs-card">
                        <div class="cs-card__head"><h2 class="cs-card__title">{{ __('What it costs') }}</h2></div>
                        <div class="cs-card__body">
                            <div class="d-flex justify-content-between" style="font-size:13px;color:var(--gray-700);padding:4px 0;"><span>{{ __('Hardware') }}</span><span>KES {{ number_format($catalogue->unit_price, 2) }}</span></div>
                            <div class="d-flex justify-content-between" style="font-size:13px;color:var(--gray-700);padding:4px 0;"><span>{{ __('Installation') }}</span><span>KES {{ number_format($catalogue->installation_cost, 2) }}</span></div>
                            <div class="d-flex justify-content-between" style="font-weight:700;color:var(--gray-900);padding:6px 0;border-top:0.5px solid var(--gray-200);margin-top:4px;"><span>{{ __('Per unit') }}</span><span>KES {{ number_format($catalogue->unit_price + $catalogue->installation_cost, 2) }}</span></div>
                            <a href="{{ route('owner.financing.self-finance', $catalogue->id) }}" class="cs-btn cs-btn--ghost" style="width:100%;justify-content:center;margin-top:10px;">{{ __('Self-finance (pay it yourself)') }}</a>
                            @if (($module->settlement_target ?? 'centresidence') === 'centresidence')
                                <p class="cs-muted" style="margin-top:10px;font-size:11.5px;"><i class="ri-tools-line"></i> {{ __('Supplied & installed by Centresidence. You can also pay part now and finance the rest.') }}</p>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Financiers --}}
        <div class="cs-section" style="margin-top:8px;">
            <div class="cs-titlebar">
                <h2 class="cs-title" style="font-size:18px;">{{ __('Finance this module') }}</h2>
            </div>

            @unless ($isTransactionMode)
                <div class="cs-alert is-amber">{{ __('Partner financing requires transaction pricing mode (so rent can service the facility automatically). You will be prompted to switch when you apply.') }}</div>
            @endunless

            @if ($products->isEmpty())
                <div class="cs-card">
                    <div class="cs-card__body">
                        <p style="margin:0 0 10px;color:var(--gray-700);">{{ __('No finance partners are offering this module yet — but you can still deploy it by self-financing above and own it outright from day one.') }}</p>
                        @if ($catalogue)
                            <a href="{{ route('owner.financing.self-finance', $catalogue->id) }}" class="cs-btn cs-btn--primary">{{ __('Self-finance this module') }}</a>
                        @endif
                    </div>
                </div>
            @else
                <div class="cs-card">
                    <div class="cs-tablewrap">
                        <table class="cs-table">
                            <thead><tr>
                                <th>{{ __('Partner') }}</th><th>{{ __('Interest') }}</th><th>{{ __('Tenor') }}</th>
                                <th>{{ __('Amount range') }}</th><th>{{ __('Max rent deduction') }}</th><th></th>
                            </tr></thead>
                            <tbody>
                                @foreach ($products as $p)
                                    <tr>
                                        <td style="font-weight:600;color:var(--blue);">{{ optional($p->partner)->trading_name ?? optional($p->partner)->company_name }}</td>
                                        <td>{{ number_format($p->interest_rate, 2) }}% {{ str_replace('_', ' ', $p->interest_rate_type) }}</td>
                                        <td>{{ $p->min_repayment_months }}–{{ $p->max_repayment_months }} {{ __('mo') }}</td>
                                        <td>KES {{ number_format($p->min_amount, 0) }} – {{ number_format($p->max_amount, 0) }}</td>
                                        <td>{{ number_format($p->max_rent_deduction_percentage, 0) }}%</td>
                                        <td><a href="{{ route('owner.financing.apply', $p->id) }}" class="cs-btn cs-btn--primary cs-btn--sm">{{ __('Apply') }}</a></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div></div></div>
@endsection
