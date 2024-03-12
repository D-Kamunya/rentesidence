@extends('owner.layouts.app')

@section('content')
    <!-- Right Content Start -->
    <div class="main-content">
        <div id="mpesa-preloader" style="display: none;">
            <div id="mpesa-preloaderInner">
                <img src="{{asset('assets/images/gateway-icon/mpesa.jpg')}}" alt="M-PESA Image">
                <div>
                    <p>Sending M-PESA payment notification to your phone. <br>
                        Dear Customer, <br> Shortly you will receive an M-PESA prompt on your phone <br> requesting you to enter your M-PESA PIN to complete your payment. <br> Please ensure your phone is on and unlocked to enable you to complete the process. Thank you.</p>
                    <p id="countdown">Make payment in <span id="countdownTimer">50</span> seconds.</p>
                </div>
                <img src="{{asset('assets/images/loading.svg')}}" alt="M-PESA Image">
            </div>
        </div>
        <div class="page-content">
            <div class="container-fluid">

                <!-- Page Content Wrapper Start -->
                <div class="page-content-wrapper bg-white p-30 radius-20">

                    <!-- start page title -->
                    <div class="row">
                        <div class="col-12">
                            <div
                                class="page-title-box d-sm-flex align-items-center justify-content-between border-bottom mb-20">
                                <div class="page-title-left">
                                    <h3 class="mb-sm-0">{{ $pageTitle }}</h3>
                                </div>
                                <div class="page-title-right">
                                    <ol class="breadcrumb mb-0">
                                        <li class="breadcrumb-item"><a href="{{ route('owner.dashboard') }}"
                                                title="{{ __('Dashboard') }}">{{ __('Dashboard') }}</a></li>
                                        <li class="breadcrumb-item active" aria-current="page">{{ $pageTitle }}</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- end page title -->

                    <!-- Subscription Page Plan Box row -->
                    <div class="row">
                        <div class="col-md-12 col-xl-8 col-xxl-6">
                            @if (!is_null($userPlan))
                                <div class="card">
                                    <div class="card-header border-bottom-0">
                                        <div class="current-plan d-flex align-items-center">
                                            <div class="flex-grow-1">
                                                <p>{{ __('Current Plan') }}</p>
                                                <h4>
                                                    {{ $userPlan->name }}
                                                    <small
                                                        class="small">/{{ $userPlan->duration_type == PACKAGE_DURATION_TYPE_MONTHLY ? __('Monthly') : __('Yearly') }}</small>
                                                </h4>
                                            </div>
                                            <div class="flex-shrink-0 ms-3">
                                                <button type="button" class="theme-btn" id="chooseAPlan"
                                                    title="{{ __('Upgrade Plan') }}">{{ __('Upgrade Plan') }}</button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="subscription-plan-box bg-white theme-border radius-4 p-20 mb-3">
                                        <div class="card-body">
                                            <div class="flex-grow-1">
                                                <p class="h6">{{ __('Usage') }}</p>
                                                <div class="plan-usage-list">
                                                    @if ($userPlan->package_type == PACKAGE_TYPE_PROPERTY)
                                                        <div class="d-flex mb-2">
                                                            <i class="ri-checkbox-circle-line"></i>
                                                            <h4 class="flex-grow-1 ms-1">
                                                                {{ __(getExistingProperty(auth()->id())) }} /
                                                                {{ __($userPlan->quantity . ' Properties') }}
                                                            </h4>
                                                        </div>
                                                    @elseif ($userPlan->package_type == PACKAGE_TYPE_UNIT)
                                                        <div class="d-flex mb-2">
                                                            <i class="ri-checkbox-circle-line"></i>
                                                            <h4 class="flex-grow-1 ms-1">
                                                                {{ getExistingUnit(auth()->id()) }} /
                                                                {{ __($userPlan->quantity . ' Units') }}
                                                            </h4>
                                                        </div>
                                                    @elseif ($userPlan->package_type == PACKAGE_TYPE_TENANT)
                                                        <div class="d-flex mb-2">
                                                            <i class="ri-checkbox-circle-line"></i>
                                                            <h4 class="flex-grow-1 ms-1">
                                                                {{ getExistingTenant(auth()->id()) }} /
                                                                {{ __($userPlan->quantity . ' Tenants') }}
                                                            </h4>
                                                        </div>
                                                    @else
                                                        <div class="d-flex mb-2">
                                                            <i class="ri-checkbox-circle-line"></i>
                                                            <h4 class="flex-grow-1 ms-1">
                                                                {{ getExistingProperty(auth()->id()) }} /
                                                                {{ __($userPlan->max_property . ' Properties') }}
                                                            </h4>
                                                        </div>
                                                        <div class="d-flex mb-2">
                                                            <i class="ri-checkbox-circle-line"></i>
                                                            <h4 class="flex-grow-1 ms-1">
                                                                {{ getExistingTenant(auth()->id()) }} /
                                                                {{ __($userPlan->max_tenant . ' Tenants') }}
                                                            </h4>
                                                        </div>
                                                    @endif

                                                    <div class="d-flex mb-2">
                                                        <i class="ri-checkbox-circle-line"></i>
                                                        <h4 class="flex-grow-1 ms-1">
                                                            {{ getExistingMaintainers(auth()->id()) }} /
                                                            {{ $userPlan->max_maintainer == -1 ? __('Unlimited Maintainers') : __($userPlan->max_maintainer . ' Maintainers') }}
                                                        </h4>
                                                    </div>

                                                    <div class="d-flex mb-2">
                                                        <i class="ri-checkbox-circle-line"></i>
                                                        <h4 class="flex-grow-1 ms-1">
                                                            {{ getExistingInvoice(auth()->id()) }} /
                                                            {{ $userPlan->max_invoice == -1 ? __('Unlimited Invoices') : __($userPlan->max_invoice . ' Invoices') }}
                                                        </h4>
                                                    </div>

                                                    <div class="d-flex mb-2">
                                                        <i class="ri-checkbox-circle-line"></i>
                                                        <h4 class="flex-grow-1 ms-1">
                                                            {{ getExistingAutoInvoice(auth()->id()) }} /
                                                            {{ $userPlan->max_auto_invoice == -1 ? __('Unlimited Auto Invoices') : __($userPlan->max_auto_invoice . ' Auto Invoices') }}
                                                        </h4>
                                                    </div>

                                                    @if ($userPlan->ticket_support == ACTIVE)
                                                        <div class="d-flex mb-2">
                                                            <i class="ri-checkbox-circle-line"></i>
                                                            <h4 class="flex-grow-1 ms-1">
                                                                {{ __('Ticket Support') }}
                                                            </h4>
                                                        </div>
                                                    @endif

                                                    @if ($userPlan->notice_support == ACTIVE)
                                                        <div class="d-flex mb-2">
                                                            <i class="ri-checkbox-circle-line"></i>
                                                            <h4 class="flex-grow-1 ms-1">
                                                                {{ __('Notice Support') }}
                                                            </h4>
                                                        </div>
                                                    @endif

                                                    <div class="d-flex mb-2">
                                                        <i class="ri-checkbox-circle-line"></i>
                                                        <h4 class="flex-grow-1 ms-1">
                                                            <span class="h6">
                                                                {{ __('Plan started at ') }}
                                                            </span>
                                                            {{ $userPlan->start_date }}
                                                        </h4>
                                                    </div>

                                                    <div class="d-flex mb-2">
                                                        <i class="ri-checkbox-circle-line"></i>
                                                        <h4 class="flex-grow-1 ms-1">
                                                            <span class="h6">{{ __('Plan end in ') }}</span>
                                                            {{ $userPlan->end_date }}
                                                        </h4>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="card">
                                    <div class="card-body">
                                        <h4 class="mb-20">{{ __("Currently you doesn't have any subscription") }}</h4>
                                        <button type="button" class="theme-btn" id="chooseAPlan"
                                            title="{{ __('Choose a plan') }}">{{ __('Choose a plan') }}</button>
                                    </div>
                                </div>
                            @endif

                            @if (!is_null($userPlan))
                                <div class="card">
                                    <div class="card-body">
                                        <form action="{{ route('owner.subscription.cancel') }}" method="post">
                                            @csrf
                                            <button type="button" class="theme-btn-red subscriptionCancel"
                                                title="Cancel your subscription">{{ __('Cancel your subscription') }}</button>
                                        </form>
                                    </div>
                                    <p class="mt-3">
                                        {{ __('Please be aware that cancelling your subscription will cause you to lose all your saved content and earned words on your subscription.') }}
                                    </p>
                                </div>
                            @endif
                        </div>
                    </div>
                    <!-- Page Content Wrapper End -->
                </div>
            </div>
            <!-- End Page-content -->
        </div>
    </div>
    <!-- Right Content End -->
    @if (!is_null(request()->id))
        <input type="hidden" id="requestPlanId" value="{{ request()->id }}">
        <input type="text" id="gatewayResponse" value="{{ $gateways }}">
    @endif
    <input type="hidden" id="requestCurrentPlan" value="{{ request()->current_plan }}">
@endsection

@push('script')
    <script src="{{ asset('assets/js/custom/owner-subscription.js') }}"></script>
@endpush
