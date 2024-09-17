@extends('tenant.layouts.app')

@section('content')
    <div class="main-content">
        <div id="mpesa-preloader" style="display: none;">
            <div id="mpesa-preloaderInner">
                <img src="{{ asset('assets/images/gateway-icon/mpesa.jpg') }}" alt="M-PESA Image">
                <div>
                    <p>Sending M-PESA payment notification to your phone...</p>
                    <p id="countdown">Make payment in <span id="countdownTimer">50</span> seconds.</p>
                    <p id="trans-message" style="display:none">Please wait for a few seconds for the transaction to be verified.</p>
                </div>
                <img src="{{ asset('assets/images/loading.svg') }}" alt="M-PESA Image">
            </div>
        </div>
        <div class="page-content">
            <div class="container-fluid">
                <div class="page-content-wrapper bg-white p-30 radius-20">
                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box d-sm-flex align-items-center justify-content-between border-bottom mb-20">
                                <div class="page-title-left">
                                    <h3 class="mb-sm-0">{{ __('Product Order Details') }}</h3>
                                </div>
                                <div class="page-title-right">
                                    <ol class="breadcrumb mb-0">
                                        <li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}" title="{{ __('Dashboard') }}">{{ __('Dashboard') }}</a></li>
                                        <li class="breadcrumb-item"><a href="{{ route('tenant.products') }}" title="{{ __('Market Place') }}">{{ __('Market Place') }}</a></li>
                                        <li class="breadcrumb-item active">{{ __('Checkout') }}</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Product Cart Items Start -->
                        <div class="col-lg-6">
                            <div class="tenant-portal-order-details-leftside bg-off-white theme-border p-20 radius-4 mb-25">
                                <div class="row align-items-center">
                                    <div class="col-12">
                                        <div class="d-flex align-items-center justify-content-between mb-25">
                                            <h4 class="mb-0">{{ __('Cart Details') }}</h4>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-12">
                                        <div id="cartItems" class="cart-items-container">
                                            <!-- Cart Items will be populated here by JavaScript -->
                                        </div>
                                        <div class="text-end mt-4">
                                            <h5>{{ __('Total Amount:')}} <span id="totalAmount">0.00</span></h5>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Product Cart Items End -->

                        <!-- Payment Gateway Section Start -->
                        <div class="col-lg-6">
                            <div class="tenant-portal-checkout-rightside bg-off-white theme-border p-20 radius-4 mb-25">
                                <form id="checkout-form" action="{{ route('payment.checkout') }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <input type="hidden" id="cartTotal" name="cartTotal">
                                    <input type="hidden" id="selectGateway" name="gateway">
                                    <input type="hidden" id="selectCurrency" name="currency">

                                    <div class="row align-items-center">
                                        <div class="col-12">
                                            <div class="d-flex align-items-center justify-content-between mb-25">
                                                <h4 class="mb-0">{{ __('Select Payment Method') }}</h4>
                                            </div>
                                        </div>
                                    </div>

                                    <ul class="nav nav-tabs invoice-payment-nav-tabs row" id="paymentGatewayTab" role="tablist">
                                        @foreach ($gateways as $gateway)
                                            <li class="nav-item col-md-12 col-xl-12 col-xxl-6 mb-25" role="presentation">
                                                <div class="cursor nav-link paymentGateway" id="invoice{{ $gateway->slug }}" data-gateway="{{ $gateway->slug }}">
                                                    <div class="custom-radiobox">
                                                        <input type="radio" value="{{ $gateway->id }}" name="gatewayOption">
                                                        <label class="fs-5">{{ $gateway->title }}</label>
                                                    </div>
                                                    <div class="invoice-payment-img">
                                                        <img src="{{ $gateway->icon }}" alt="">
                                                    </div>
                                                </div>
                                            </li>
                                        @endforeach
                                    </ul>

                                    <div class="col-md-12">
                                        <button type="button" class="theme-btn me-2 mb-1 w-100" id="checkoutBtn">
                                            {{ __('Checkout') }} <span id="checkoutAmount">0.00</span>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <!-- Payment Gateway Section End -->
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('style')
    <link rel="stylesheet" href="{{ asset('assets/css/product-pay.css') }}">
@endpush

@push('script')
    <script src="{{ asset('assets/js/custom/tenant-product-pay.js') }}"></script>
@endpush

