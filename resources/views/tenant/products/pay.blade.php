@extends('tenant.layouts.app')

@section('content')
    <div class="main-content">
        <div id="mpesa-preloader" style="display: none;">
            <div id="mpesa-preloaderInner">
                <img src="{{asset('assets/images/gateway-icon/mpesa.jpg')}}" alt="M-PESA Image">
                <div>
                    <p>Please follow the instructions and do not refresh or leave this page.</p><br>
                    <p>This may take up to <span id="mpesa-timer">2:00 minute(s)</span>.</p><br>
                    <p>You will receive a prompt on mobile number to enter your PIN to authorize your payment request.</p><br>
                    <p> Please ensure your phone is on and unlocked to enable you to complete the process. Thank you.</p>
                </div>
                <img src="{{asset('assets/images/loading.svg')}}" alt="M-PESA Image">
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
                                        <li class="breadcrumb-item"><a href="{{ route('tenant.product.index') }}" title="{{ __('Market Place') }}">{{ __('Market Place') }}</a></li>
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
                                        <table class="table theme-border p-20">
                                            <tbody id="currencyAppend"></tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Product Cart Items End -->

                        <!-- Payment Gateway Section Start -->
                        <div class="col-lg-6">
                            <div
                                class="tenant-portal-invoice-details-rightside bg-off-white theme-border p-20 radius-4 mb-25">
                                <form id="pay-products-order-form" class="" name="checkoutForm" action="{{ route('payment.products.checkout') }}" method="POST"
                                    enctype="multipart/form-data">
                                    @csrf
                                    <input type="hidden" id="cartTotal" name="cartTotal">
                                    <input type="hidden" id="selectGateway" name="gateway">
                                    <input type="hidden" id="selectCurrency" name="currency">
                                    <input type="hidden" id="getCurrencyByGatewayRoute" value="{{ route('tenant.invoice.get.currency') }}">

                                    <div class="row align-items-center">
                                        <div class="col-12">
                                            <div class="d-flex align-items-center justify-content-between mb-25">
                                                <h4 class="mb-0">{{ __('Select Payment Method') }}</h4>
                                            </div>
                                        </div>
                                    </div>

                                    <ul class="nav nav-tabs invoice-payment-nav-tabs row" id="gatewaySection"
                                        role="tablist">
                                        @foreach ($gateways as $gateway)
                                            <li class="nav-item col-md-12 col-xl-12 col-xxl-6 mb-25" role="presentation">
                                                <div data-bs-target="#{{ $gateway->slug }}"
                                                    class="cursor nav-link paymentGateway" id="invoice{{ $gateway->slug }}"
                                                    data-gateway="{{ $gateway->slug }}" data-bs-toggle="tab" role="tab"
                                                    aria-controls="{{ $gateway->slug }}" aria-selected="true">
                                                    <div class="custom-radiobox">
                                                        <input type="radio" name="selectGateway" value="{{ $gateway->id }}">
                                                        <label class="fs-5">{{ $gateway->title }}</label>
                                                    </div>
                                                    <div class="invoice-payment-img">
                                                        <img src="{{ $gateway->icon }}" alt="">
                                                    </div>
                                                </div>
                                            </li>
                                        @endforeach
                                    </ul>

                                    <div class="row">
                                        <div class="tab-content" id="invoicePaymentTabContent">

                                            <div class="tab-pane fade" id="bank" role="tabpanel"
                                                aria-labelledby="invoicebank" tabindex="0">
                                                <div class="invoice-payment-card-box bg-white radius-4 theme-border mb-25">
                                                    <div
                                                        class="d-flex justify-content-between align-items-center border-bottom p-20">
                                                        <h4>{{ __('Bank Deposit') }}</h4>
                                                    </div>
                                                    <div class="p-20 pb-0">
                                                        <div class="row">
                                                            <div class="col-md-12 mb-20">
                                                                <label
                                                                    class="label-text-title color-heading font-medium mb-2">{{ __('Bank Name') }}</label>
                                                                <select name="bank_id" class="form-control" id="bank_id">
                                                                    <option value="">--{{ __('Bank') }}--
                                                                    </option>
                                                                    @foreach ($banks as $bank)
                                                                        <option value="{{ $bank->id }}"
                                                                            data-details="{{ $bank->details }}">
                                                                            {{ $bank->name }}</option>
                                                                    @endforeach
                                                                </select>
                                                                @error('bank_id')
                                                                    <span class="text-danger">{{ $message }}</span>
                                                                @enderror
                                                            </div>
                                                            <div class="col-md-12 mb-20 d-none" id="bankDetails">
                                                                <p class="my-2 ps-2"></p>
                                                            </div>
                                                            <div class="col-md-12 mb-20">
                                                                <label
                                                                    class="label-text-title color-heading font-medium mb-2">{{ __('Upload Deposit Slip') }}</label>
                                                                <input type="file" class="form-control"
                                                                    name="bank_slip">
                                                                @error('bank_slip')
                                                                    <span class="text-danger">{{ $message }}</span>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="tab-pane fade" id="mpesa" role="tabpanel"
                                                aria-labelledby="invoicempesa" tabindex="0">
                                                <div class="invoice-payment-card-box bg-white radius-4 theme-border mb-25">
                                                    <div
                                                        class="d-flex justify-content-between align-items-center border-bottom p-20">
                                                        <h4>{{ __('Make Mpesa Payment') }}</h4>
                                                    </div>
                                                    <div class="p-20 pb-0">
                                                        <div class="row">
                                                            <div class="col-md-12 mb-20">
                                                                <label
                                                                    class="label-text-title color-heading font-medium mb-2">{{ __('Mpesa Account') }}</label>
                                                                <select name="mpesa_account_id" id="mpesa_account_id" class="form-control mb-2">
                                                                    <option value="">{{ __('Select Option') }}</option>
                                                                    @foreach ($mpesaAccounts as $mpesaAccount)
                                                                        <option value="{{ $mpesaAccount->id }}"
                                                                            data-details="{{ nl2br($mpesaAccount->account_type) }}">
                                                                                {{ $mpesaAccount->account_type }}

                                                                                @if ($mpesaAccount->account_type === 'PAYBILL')
                                                                                    - Paybill: {{ $mpesaAccount->paybill }}, Account Number: {{ $mpesaAccount->account_name }}
                                                                                @elseif ($mpesaAccount->account_type === 'TILLNUMBER')
                                                                                    - Till Number: {{ $mpesaAccount->till_number }}
                                                                                @endif
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                                @error('mpesa_account_id')
                                                                    <span class="text-danger">{{ $message }}</span>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12 text-center">
                                            <button type="button" class="theme-btn me-2 mb-1 w-75"
                                                id="checkoutBtn">{{ __('Checkout') }}
                                                <span class="ms-1" id="checkoutAmount"></span></button>
                                            <button type="button" class="d-none theme-btn me-2 mb-1 w-75"
                                                id="mpesaPayBtn">{{ __('Checkout Via Mpesa Code') }}
                                                <span class="ms-1" id="mpesaGatewayCurrencyAmount"></span></button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Mpesa Transaction Code Payment Method Modal Start -->
                        <div class="modal fade big-modal" id="mpesaCodePaymentMethodModal" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header border-0 p-0">
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span
                                                class="iconify" data-icon="akar-icons:cross"></span></button>
                                    </div>
                                    <div class="modal-body">
                                        <!-- Choose a plan content Start -->
                                        <div class="payment-method-area">
                                            <h2 class="text-center payment-method-area-title">{{ __('Mpesa Payment Details') }}</h2>
                                            <div class="payment-method-wrap px-5">
                                                <form id="pay-products-order-form" name="checkoutForm" class="" action="{{ route('payment.products.checkout') }}" method="POST"
                                                    enctype="multipart/form-data">
                                                    @csrf
                                                    <input type="hidden" id="mpesa_code_account_id" name="mpesa_account_id" value="">
                                                    <input type="hidden" id="mpesa_selectGateway" name="gateway">
                                                    <input type="hidden" id="mpesa_selectCurrency" name="currency">
                                                    <input type="hidden" id="mpesa_amount" name="mpesa_amount">
                                                    <input type="hidden"  id="mpesa_default-currency" name="default-currency" value="{{ json_encode(session('default_currency')) }}">
                                                    <div class="row">
                                                        <h4>Hello {{ auth()->user()->name }}</h4>
                                                        <b>Follow the instructions below to make payment from your MPESA account.</b>
                                                        <ol>
                                                            <li>Go to the Safaricom SIM Tool Kit</li>
                                                            <li>Select the MPESA menu</li>
                                                            <li>Select Lipa na MPESA</li>
                                                            <div class="" id="mpesa-code-payment-paybill">
                                                                <li>Select Pay bill</li>
                                                                <li>Select Enter Business Number, and enter <strong id="bs-number">111739</strong></li>
                                                                <li>Select Enter Account Number and type <strong id="acc-number"></strong></li>
                                                            </div>
                                                            <div class="" id="mpesa-code-payment-till">
                                                                <li>Select Buy Goods and Services</li>
                                                                <li>Enter Till Number <strong id="till-number"></strong></li>
                                                            </div>
                                                            
                                                            <li>Enter your subscription amount <strong id="mpesa-amount"></strong></li>
                                                            <li>Enter your MPESA pin number</li>
                                                        </ol>
                                                    </div>
                                                    <div class="col-md-12 mb-25">
                                                        <label
                                                            class="label-text-title color-heading font-medium mb-2">{{ __('Enter Mpesa Transaction Code') }}</label>
                                                        <input type="text" id="mpesaTransactionCode" name="mpesa_transaction_code" class="form-control" placeholder="{{__('Mpesa Transaction Code')}}">
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-12 text-center">
                                                            <button type="button" class="theme-btn me-2 mb-1 w-75"
                                                                id="mpesaCodeSubmitBtn">{{ __('Submit Code') }}
                                                                </button>
                                
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                        <!-- Choose a plan content End -->
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Mpesa Transaction Code Payment Method Modal End -->
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

