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
                            <li>{{ __('Home Details') }}</li>
                        </ol>
                    </div>
                </div>

                <div class="td-layout">
                    <aside class="td-rail">
                        @include('owner.tenants.details._hero')
                        @include('owner.tenants.details.sidenav')
                    </aside>

                    <div class="td-content">
                        {{-- Property --}}
                        <div class="td-card">
                            @if ($tenant->property?->thumbnail_image)
                                <img src="{{ $tenant->property->thumbnail_image }}" alt="" style="width:100%; height:190px; object-fit:cover;">
                            @endif
                            <div class="td-card__body" style="padding-top:16px;">
                                <h3 class="td-hero__name" style="color:#111827; font-size:17px; margin:0 0 4px;">{{ $tenant->property_name }}</h3>
                                <p class="td-rail__meta" style="margin-bottom:14px;"><i class="ri-map-pin-2-line"></i> {{ $tenant->property_address ?: '—' }}</p>
                                <dl class="td-info">
                                    <dt>{{ __('Unit') }}</dt><dd>{{ $tenant->unit_name ?: '—' }}</dd>
                                    <dt>{{ __('Status') }}</dt><dd>{{ $tenant->status == TENANT_STATUS_ACTIVE ? __('Active Tenant') : __('Deactivated Tenant') }}</dd>
                                    <dt>{{ __('Lease Start Date') }}</dt><dd>{{ $tenant->lease_start_date ?: '—' }}</dd>
                                    <dt>{{ __('Lease End Date') }}</dt><dd>{{ $tenant->lease_end_date ?? __('Unlimited') }}</dd>
                                </dl>
                            </div>
                        </div>

                        {{-- Rent Information --}}
                        <div class="td-card">
                            <div class="td-card__head">
                                <span class="td-card__ic"><i class="ri-money-dollar-circle-line"></i></span>
                                <h3 class="td-card__title">{{ __('Rent Information') }}</h3>
                            </div>
                            <div class="td-card__body">
                                <dl class="td-info">
                                    <dt>{{ __('General Rent') }}</dt><dd>{{ currencyPrice($tenant->general_rent) }}</dd>
                                    <dt>{{ __('Security Deposit') }}</dt><dd>{{ currencyPrice($tenant->security_deposit_type == TYPE_FIXED ? $tenant->security_deposit : $tenant->general_rent + $tenant->general_rent * $tenant->security_deposit * 0.01) }}</dd>
                                    <dt>{{ __('Late Fee') }}</dt><dd>{{ currencyPrice($tenant->late_fee_type == TYPE_FIXED ? $tenant->late_fee : $tenant->general_rent + $tenant->general_rent * $tenant->late_fee * 0.01) }}</dd>
                                    <dt>{{ __('Incident Receipt') }}</dt><dd>{{ $tenant->incident_receipt ?: '—' }}</dd>
                                    <dt>{{ __('Payment due on date') }}</dt><dd>{{ $tenant->due_date ?: '—' }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection
