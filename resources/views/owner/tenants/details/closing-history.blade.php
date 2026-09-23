@extends('owner.layouts.app')

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="page-content-wrapper bg-white p-30 radius-20">

                <div class="td-header">
                    <div>
                        <h2 class="td-title">{{ $pageTitle }}</h2>
                        <ol class="td-crumb">
                            <li><a href="{{ route('owner.dashboard') }}">{{ __('Dashboard') }}</a></li>
                            <li>›</li>
                            <li><a href="{{ route('owner.tenant.index') }}">{{ __('Tenants') }}</a></li>
                            <li>›</li>
                            <li>{{ __('Closing History') }}</li>
                        </ol>
                    </div>
                </div>

                <div class="td-layout">
                    <aside class="td-rail">
                        @include('owner.tenants.details._hero')
                        @include('owner.tenants.details.sidenav')
                    </aside>

                    <div class="td-content">
                        {{-- Closing details --}}
                        <div class="td-card">
                            <div class="td-card__head">
                                <span class="td-card__ic"><i class="ri-logout-box-r-line"></i></span>
                                <h3 class="td-card__title">{{ __('Closing Details') }}</h3>
                            </div>
                            <div class="td-card__body">
                                <dl class="td-info">
                                    <dt>{{ __('Closing Refund Amount') }}</dt><dd>{{ $tenant->close_refund_amount ?: '—' }}</dd>
                                    <dt>{{ __('Closing Charge') }}</dt><dd>{{ $tenant->close_charge ?: '—' }}</dd>
                                    <dt>{{ __('Closing Date') }}</dt><dd>{{ $tenant->close_date ?: '—' }}</dd>
                                    <dt>{{ __('Closing Reason') }}</dt><dd>{{ $tenant->close_reason ?: '—' }}</dd>
                                </dl>
                            </div>
                        </div>

                        {{-- Tenant screening ratings --}}
                        <div class="td-card">
                            <div class="td-card__head">
                                <span class="td-card__ic" style="background:#FAEEDA; color:#854F0B;"><i class="ri-shield-star-line"></i></span>
                                <h3 class="td-card__title">{{ __('Tenant Screening') }}</h3>
                            </div>
                            <div class="td-card__body">
                                @php
                                    $rateVal = fn($r) => (int) filter_var((string) $r, FILTER_SANITIZE_NUMBER_INT);
                                    $rentR = $rateVal($tenant->rent_payment_rating);
                                    $discR = $rateVal($tenant->discipline_rating);
                                @endphp
                                <div class="td-rate">
                                    <div class="td-rate__label">{{ __('Rent Payment Rating') }}</div>
                                    <div class="td-rate__stars">
                                        @for ($i = 1; $i <= 5; $i++)<i class="ri-star-{{ $i <= $rentR ? 'fill' : 'line' }}"></i>@endfor
                                        <span class="td-rate__text">{{ $tenant->rent_payment_rating ?: __('Not rated') }}</span>
                                    </div>
                                </div>
                                <div class="td-rate">
                                    <div class="td-rate__label">{{ __('Discipline Rating') }}</div>
                                    <div class="td-rate__stars">
                                        @for ($i = 1; $i <= 5; $i++)<i class="ri-star-{{ $i <= $discR ? 'fill' : 'line' }}"></i>@endfor
                                        <span class="td-rate__text">{{ $tenant->discipline_rating ?: __('Not rated') }}</span>
                                    </div>
                                </div>
                                @if ($tenant->closing_remarks)
                                    <div class="td-rate__remarks">
                                        <div class="td-rate__label">{{ __('Remarks') }}</div>
                                        <p>{{ $tenant->closing_remarks }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<style>
    .td-rate { display:flex; align-items:center; justify-content:space-between; gap:12px; padding:12px 0; border-bottom:0.5px solid #f3f4f6; }
    .td-rate:last-child { border-bottom:none; }
    .td-rate__label { font-size:12.5px; color:#6b7280; font-weight:500; }
    .td-rate__stars { display:flex; align-items:center; gap:3px; color:#F2B01E; font-size:17px; }
    .td-rate__text { font-size:12.5px; color:#374151; margin-left:8px; font-weight:600; }
    .td-rate__remarks { padding-top:12px; }
    .td-rate__remarks p { font-size:13.5px; color:#374151; margin:6px 0 0; line-height:1.6; }
</style>
@endsection
