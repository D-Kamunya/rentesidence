@extends('admin.layouts.app')

@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="page-content-wrapper bg-white p-30 radius-20">
                    @include('centresidence._design')

                    <div class="row">
                        <div class="col-12">
                            <div
                                class="page-title-box d-sm-flex align-items-center justify-content-between border-bottom mb-20">
                                <div class="page-title-left">
                                    <h3 class="mb-sm-0">{{ __('Settings') }}</h3>
                                </div>
                                <div class="page-title-right">
                                    <ol class="breadcrumb mb-0">
                                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"
                                                title="{{ __('Dashboard') }}">{{ __('Dashboard') }}</a></li>
                                        <li class="breadcrumb-item"><a href="#"
                                                title="{{ __('Settings') }}">{{ __('Settings') }}</a></li>
                                        <li class="breadcrumb-item active" aria-current="page">{{ $pageTitle }}
                                        </li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="settings-page-layout-wrap position-relative">
                        <div class="row">
                            @include('admin.setting.sidebar')
                            <div class="col-md-12 col-lg-12 col-xl-8 col-xxl-9">
                                <div class="account-settings-rightside cs-card cs-card--pad cs-controls">
                                    <div class="language-settings-page-area">
                                        <div class="account-settings-content-box">
                                            <div class="account-settings-title border-bottom mb-20 pb-20">
                                                <div class="row align-items-center">
                                                    <div class="col-md-6">
                                                        <h4>{{ $pageTitle }}</h4>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="alert alert-info radius-4 mb-25" role="note"
                                                style="font-size:13px;line-height:1.6;">
                                                <strong>{{ __('These rates apply across every affiliate income line.') }}</strong>
                                                {{ __('The first-time rate is paid on an owner\'s first activity in each line; the recurring rate applies for the number of months set below (measured per line, from that line\'s first commission). Income lines:') }}
                                                <span class="d-block mt-1">
                                                    {{ __('Subscription (of the plan fee) · Rent (a set share of our rent fee) · Marketplace (a cut of our sale commission) · Tenant screening · Agreements ·') }}@if(config('centresidence.gas_live', false)) {{ __('Gas tokens (gas only) ·') }}@endif {{ __('Financing (origination fee).') }}
                                                </span>
                                                <span class="d-block mt-1 text-muted">
                                                    {{ __('For the usage lines (screening, agreements,') }}@if(config('centresidence.gas_live', false)) {{ __('gas tokens,') }}@endif {{ __('financing) the rate is applied to OUR take on each event, so a payout never exceeds what we earned — the affiliate earns from what their owners do, not just the plan they are on.') }}
                                                </span>
                                            </div>
                                            <form action="{{ route('admin.setting.general-setting.update') }}"
                                                method="post" enctype="multipart/form-data">
                                                @csrf
                                                <div class="settings-inner-box bg-white theme-border radius-4 mb-25">
                                                    <div class="settings-inner-box-fields p-20 pb-0">
                                                        <div class="row">

                                                            <!-- First-time commission rate -->
                                                            <div class="col-md-6 mb-25">
                                                                <label
                                                                    class="label-text-title color-heading font-medium mb-2">{{ __('First-time Commission Rate (%)') }}</label>
                                                                <input type="number" name="FIRST_TIME_COMMISSION_RATE"
                                                                    class="form-control"
                                                                    value="{{ getOption('FIRST_TIME_COMMISSION_RATE', 0) }}"
                                                                    min="0" max="100" step="0.01">
                                                            </div>

                                                            <!-- Recurring commission rate -->
                                                            <div class="col-md-6 mb-25">
                                                                <label
                                                                    class="label-text-title color-heading font-medium mb-2">{{ __('Recurring Commission Rate (%)') }}</label>
                                                                <input type="number" name="RECURRING_COMMISSION_RATE"
                                                                    class="form-control"
                                                                    value="{{ getOption('RECURRING_COMMISSION_RATE', 0) }}"
                                                                    min="0" max="100" step="0.01">
                                                            </div>

                                                            <!-- Number of recurring commission months -->
                                                            <div class="col-md-6 mb-25">
                                                                <label
                                                                    class="label-text-title color-heading font-medium mb-2">{{ __('Number of Recurring Commission Months') }}</label>
                                                                <input type="number" name="RECURRING_COMMISSION_MONTHS"
                                                                    class="form-control"
                                                                    value="{{ getOption('RECURRING_COMMISSION_MONTHS', 0) }}"
                                                                    min="1" step="1">
                                                            </div>

                                                        </div>
                                                    </div>
                                                </div>
                                                <button class="theme-btn"
                                                    title="{{ __('Update') }}">{{ __('Update') }}</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
