<!-- Choose a plan Modal Start -->
@if (isAddonInstalled('PROTYSAAS') > 1)
    <div class="modal fade big-modal" id="choosePlanModal" tabindex="-1" aria-hidden="true">
        <input type="hidden" id="chooseAPanRoute" value="{{ route('owner.subscription.get_plan') }}">
        <input type="hidden" id="getCurrencyByGatewayRoute" value="{{ route('owner.subscription.get.currency') }}">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0 p-0">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span
                            class="iconify" data-icon="akar-icons:cross"></span></button>
                </div>
                <div class="modal-body">
                    <!-- Choose a plan content Start -->
                    <div class="choose-plan-area">
                        <h2 class="choose-plan-area-title text-center">{{ __('Choose A Plan') }}</h2>
                        <div class="d-flex justify-content-center align-items-center pb-30">
                            <span class="mx-3">{{ __('Monthly') }}</span>
                            <div class="payment-subscription-switch form-check form-switch">
                                <input class="form-check-input" type="checkbox" value="1"
                                    id="monthly-yearly-button">
                                <label class="form-check-label" for="monthly-yearly-button"></label>
                            </div>
                            <span class="mx-3">{{ __('Yearly') }}</span>
                        </div>
                        <div class="pricing-plan-area px-5">
                            <div class="row price-table-wrap" id="planListBlock">
                            </div>
                        </div>
                    </div>
                    <!-- Choose a plan content End -->
                </div>
            </div>
        </div>
    </div>
    <!-- Choose a plan Modal End -->

    <!-- Payment Method Modal Start -->
    <div class="modal fade big-modal" id="paymentMethodModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0 p-0">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span
                            class="iconify" data-icon="akar-icons:cross"></span></button>
                </div>
                <div class="modal-body">
                    <!-- Choose a plan content Start -->
                    <div class="payment-method-area">
                        <h2 class="text-center payment-method-area-title">{{ __('Select Payment Method') }}</h2>
                        <div class="payment-method-wrap px-5">
                            <form id="pay-subscription-form" class="" action="{{ route('payment.subscription.checkout') }}" method="POST"
                                enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" id="plan_id" name="package_id" value="">
                                <input type="hidden" id="selectGateway" name="gateway">
                                <input type="hidden" id="selectCurrency" name="currency">
                                <input type="hidden" id="duration_type" name="duration_type">
                                <input type="hidden" id="quantity" name="quantity">
                                <input type="hidden" id="default-currency" name="default-currency" value="{{ json_encode(session('default_currency')) }}">
                                <div class="row" id="gatewayListBlock">
                                </div>
                                <div class="row">
                                    <div class="col-md-12 text-center">
                                        <button type="button" class="theme-btn me-2 mb-1 w-75"
                                            id="payBtn">{{ __('Pay Now') }}
                                            <span class="ms-1" id="gatewayCurrencyAmount"></span></button>
                                        <button type="button" class="d-none theme-btn me-2 mb-1 w-75"
                                            id="mpesaPayBtn">{{ __('Pay Now Via Mpesa Code') }}
                                            <span class="ms-1" id="mpesaGatewayCurrencyAmount"></span></button>
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
    <!-- Payment Method Modal End -->

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
                            <form id="pay-subscription-form" class="" action="{{ route('payment.subscription.checkout') }}" method="POST"
                                enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" id="mpesa_plan_id" name="package_id" value="">
                                <input type="hidden" id="mpesa_selectGateway" name="gateway">
                                <input type="hidden" id="mpesa_selectCurrency" name="currency">
                                <input type="hidden" id="mpesa_duration_type" name="duration_type">
                                <input type="hidden" id="mpesa_quantity" name="quantity">
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
                                    <input type="text" name="mpesa-transaction-code" class="form-control"
                                        value="" placeholder="Mpesa Transaction Code">
                                </div>
                                <div class="row">
                                    <div class="col-md-12 text-center">
                                        <button type="button" class="theme-btn me-2 mb-1 w-75"
                                            id="mpesaCodeBtn">{{ __('Submit Code') }}
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
@endif
