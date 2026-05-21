@extends('tenant.layouts.app')

@section('content')
    {{-- M-Pesa Preloader --}}
    <div id="mpesa-preloader" style="display: none;">
        <div id="mpesa-preloaderInner">
            <img src="{{ asset('assets/images/gateway-icon/mpesa.jpg') }}" alt="M-PESA">
            <div>
                <p>{{ __('Please follow the instructions and do not refresh or leave this page.') }}</p>
                <p>{{ __('This may take up to') }} <span id="mpesa-timer">2:00</span> {{ __('minute(s).') }}</p>
                <p>{{ __('You will receive a prompt on your mobile number to enter your PIN to authorize payment.') }}</p>
                <p>{{ __('Please ensure your phone is on and unlocked. Thank you.') }}</p>
            </div>
            <img src="{{ asset('assets/images/loading.svg') }}" alt="Loading">
        </div>
    </div>

    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="page-content-wrapper bg-white p-30 radius-20">
                    <div class="container">

                        {{-- Page Header --}}
                        <div class="dash-header mb-4">
                            <div>
                                <h2 class="dash-title">{{ $pageTitle }}</h2>
                                <p class="dash-subtitle">{{ __('Review your invoice and complete payment') }}</p>
                            </div>
                            <nav aria-label="breadcrumb">
                                <ol class="mp-breadcrumb">
                                    <li><a href="{{ route('tenant.dashboard') }}">{{ __('Dashboard') }}</a></li>
                                    <li><a href="#">{{ __('Invoice') }}</a></li>
                                    <li aria-current="page">{{ $pageTitle }}</li>
                                </ol>
                            </nav>
                        </div>

                        <div class="checkout-layout">

                            {{-- LEFT: Invoice Details --}}
                            <div class="checkout-panel checkout-panel--cart">
                                <div class="panel-header">
                                    <div class="panel-header__icon panel-header__icon--blue">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                                            <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                            <polyline points="14 2 14 8 20 8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                            <line x1="16" y1="13" x2="8" y2="13" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                            <line x1="16" y1="17" x2="8" y2="17" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                        </svg>
                                    </div>
                                    <h4 class="panel-title">{{ __('Invoice Details') }}</h4>
                                </div>

                                <div class="invoice-detail-rows">
                                    <div class="invoice-detail-row">
                                        <span class="invoice-detail-label">{{ __('Invoice No.') }}</span>
                                        <span class="invoice-detail-value">{{ $invoice->invoice_no }}</span>
                                    </div>
                                    <div class="invoice-detail-row">
                                        <span class="invoice-detail-label">{{ __('Name') }}</span>
                                        <span class="invoice-detail-value">{{ $invoice->name }}</span>
                                    </div>
                                    <div class="invoice-detail-row">
                                        <span class="invoice-detail-label">{{ __('Issue Date') }}</span>
                                        <span class="invoice-detail-value">{{ $invoice->created_at->format('Y-m-d') }}</span>
                                    </div>
                                    <div class="invoice-detail-row">
                                        <span class="invoice-detail-label">{{ __('Due Date') }}</span>
                                        <span class="invoice-detail-value">{{ $invoice->due_date }}</span>
                                    </div>
                                </div>

                                <div class="cart-total-row">
                                    <span class="cart-total-label">{{ __('Amount Due') }}</span>
                                    <span class="cart-total-value">{{ currencyPrice($invoice->amount) }}</span>
                                </div>

                                {{-- Currency section — shown by JS after gateway selected --}}
                                {{-- Hidden entirely for transaction model (JS handles currency auto-fetch) --}}
                                @if(!$isTransactionModel)
                                <div id="currencySection" class="currency-section" style="display:none;">
                                    <div class="currency-section-header">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none">
                                            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.8"/>
                                            <path d="M12 6v6l4 2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                        </svg>
                                        {{ __('Select Currency') }}
                                    </div>
                                    <table class="table theme-border p-20 mb-0">
                                        <tbody id="currencyAppend"></tbody>
                                    </table>
                                </div>
                                @endif

                                {{-- Hidden currencyAppend always present so JS getCurrencyRes() can write to it --}}
                                @if($isTransactionModel)
                                <div style="display:none;"><table><tbody id="currencyAppend"></tbody></table></div>
                                @endif
                            </div>

                            {{-- RIGHT: Payment --}}
                            <div class="checkout-panel checkout-panel--payment">
                                <div class="panel-header">
                                    <div class="panel-header__icon panel-header__icon--green">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                                            <rect x="1" y="4" width="22" height="16" rx="2" ry="2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                            <line x1="1" y1="10" x2="23" y2="10" stroke="currentColor" stroke-width="1.8"/>
                                        </svg>
                                    </div>
                                    <h4 class="panel-title">{{ __('Payment Method') }}</h4>
                                </div>

                                <form id="pay-invoice-form"
                                      action="{{ route('payment.checkout') }}"
                                      method="POST"
                                      enctype="multipart/form-data">
                                    @csrf
                                    <input type="hidden" name="invoice_id"    value="{{ $invoice->id }}">
                                    <input type="hidden" id="selectGateway"   name="gateway"
                                           value="{{ $isTransactionModel ? 'mpesa' : '' }}">
                                    <input type="hidden" id="selectCurrency"  name="currency">
                                    <input type="hidden" id="getCurrencyByGatewayRoute"
                                           value="{{ route('tenant.invoice.get.currency') }}">

                                    @if($isTransactionModel)
                                        {{-- ── Transaction model: M-Pesa only, no selection needed ── --}}
                                        <div class="txn-mpesa-brand">
                                            <img src="{{ asset('assets/images/gateway-icon/mpesa.jpg') }}"
                                                 alt="M-Pesa"
                                                 style="width:52px;height:52px;border-radius:10px;object-fit:cover;flex-shrink:0;">
                                            <div>
                                                <p class="txn-mpesa-brand__title">{{ __('Pay via M-Pesa') }}</p>
                                                <p class="txn-mpesa-brand__sub">{{ __('You will receive an STK push on your registered Safaricom number to complete payment.') }}</p>
                                            </div>
                                        </div>
                                        <div class="txn-mpesa-note">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" style="flex-shrink:0">
                                                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.8"/>
                                                <line x1="12" y1="8" x2="12" y2="12" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                                <line x1="12" y1="16" x2="12.01" y2="16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                            </svg>
                                            <span>{{ __('Ensure your Safaricom line is active and your phone is unlocked before proceeding.') }}</span>
                                        </div>

                                    @else
                                        {{-- ── Standard flow: gateway selection ── --}}
                                        <div class="gateway-grid" id="gatewaySection" role="tablist">
                                            @foreach ($gateways as $gateway)
                                                <div class="gateway-option paymentGateway"
                                                     id="invoice{{ $gateway->slug }}"
                                                     data-bs-target="#{{ $gateway->slug }}"
                                                     data-gateway="{{ $gateway->slug }}"
                                                     data-bs-toggle="tab"
                                                     role="tab"
                                                     aria-controls="{{ $gateway->slug }}"
                                                     aria-selected="false">
                                                    <div class="gateway-option__radio">
                                                        <input type="radio" name="selectGateway" value="{{ $gateway->id }}">
                                                    </div>
                                                    <div class="gateway-option__body">
                                                        <img src="{{ $gateway->icon }}" alt="{{ $gateway->title }}" class="gateway-option__img">
                                                        <span class="gateway-option__label">{{ $gateway->title }}</span>
                                                    </div>
                                                    <div class="gateway-option__check">
                                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none">
                                                            <path d="M20 6L9 17l-5-5" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                        </svg>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>

                                        {{-- Gateway Detail Panels --}}
                                        <div class="tab-content gateway-tab-content" id="invoicePaymentTabContent">

                                            {{-- Bank --}}
                                            <div class="tab-pane fade" id="bank" role="tabpanel" aria-labelledby="invoicebank" tabindex="0">
                                                <div id="bankAppend">
                                                <div class="gateway-detail-panel">
                                                    <h5 class="gateway-detail-title">
                                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                                                            <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                                            <polyline points="9 22 9 12 15 12 15 22" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                                        </svg>
                                                        {{ __('Bank Deposit') }}
                                                    </h5>
                                                    <div class="form-field">
                                                        <label class="form-field__label">{{ __('Bank Name') }}</label>
                                                        <select name="bank_id" id="bank_id" class="form-select-custom">
                                                            <option value="">-- {{ __('Select Bank') }} --</option>
                                                            @foreach ($banks as $bank)
                                                                <option value="{{ $bank->id }}" data-details="{{ $bank->details }}">
                                                                    {{ $bank->name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        @error('bank_id')<span class="form-field__error">{{ $message }}</span>@enderror
                                                    </div>
                                                    <div class="col-md-12 mb-20 d-none" id="bankDetails">
                                                        <div class="info-box"><p class="my-2 ps-2"></p></div>
                                                    </div>
                                                    <div class="form-field">
                                                        <label class="form-field__label">{{ __('Upload Deposit Slip') }}</label>
                                                        <div class="file-upload-area">
                                                            <input type="file" name="bank_slip" id="bank_slip" class="file-upload-input">
                                                            <label for="bank_slip" class="file-upload-label">
                                                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                                                                    <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M17 8l-5-5-5 5M12 3v12" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                                                </svg>
                                                                <span>{{ __('Click to upload or drag & drop') }}</span>
                                                                <small>{{ __('PNG, JPG, PDF up to 5MB') }}</small>
                                                            </label>
                                                        </div>
                                                        @error('bank_slip')<span class="form-field__error">{{ $message }}</span>@enderror
                                                    </div>
                                                </div>
                                                </div>
                                            </div>

                                            {{-- Mpesa --}}
                                            <div class="tab-pane fade" id="mpesa" role="tabpanel" aria-labelledby="invoicempesa" tabindex="0">
                                                <div id="mpesaAccountAppend">
                                                <div class="gateway-detail-panel">
                                                    <h5 class="gateway-detail-title">
                                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                                                            <path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 9.81 19.79 19.79 0 01.08 1.22 2 2 0 012.08 0h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L6.09 7.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                                        </svg>
                                                        {{ __('M-Pesa Payment') }}
                                                    </h5>
                                                    <div class="form-field">
                                                        <label class="form-field__label">{{ __('M-Pesa Account') }}</label>
                                                        <select name="mpesa_account_id" id="mpesa_account_id" class="form-select-custom">
                                                            <option value="">{{ __('Select Option') }}</option>
                                                            @foreach ($mpesaAccounts as $mpesaAccount)
                                                                <option value="{{ $mpesaAccount->id }}"
                                                                        data-details="{{ nl2br($mpesaAccount->account_type) }}">
                                                                    {{ $mpesaAccount->account_type }}
                                                                    @if ($mpesaAccount->account_type === 'PAYBILL')
                                                                        — Paybill: {{ $mpesaAccount->paybill }}, Acc: {{ $mpesaAccount->account_name }}
                                                                    @elseif ($mpesaAccount->account_type === 'TILLNUMBER')
                                                                        — Till: {{ $mpesaAccount->till_number }}
                                                                    @endif
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        @error('mpesa_account_id')<span class="form-field__error">{{ $message }}</span>@enderror
                                                    </div>
                                                    <div class="mpesa-info-note">
                                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none">
                                                            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.8"/>
                                                            <line x1="12" y1="8" x2="12" y2="12" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                                            <line x1="12" y1="16" x2="12.01" y2="16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                                        </svg>
                                                        {{ __('You will receive an STK push on your registered M-Pesa number.') }}
                                                    </div>
                                                </div>
                                                </div>
                                            </div>

                                        </div>{{-- /.tab-content --}}
                                    @endif

                                    {{-- Payment Buttons --}}
                                    <div class="checkout-action-row">
                                        <button type="button" class="checkout-btn" id="payBtn">
                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                                                <rect x="1" y="4" width="22" height="16" rx="2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                                <line x1="1" y1="10" x2="23" y2="10" stroke="currentColor" stroke-width="1.8"/>
                                            </svg>
                                            {{ __('Pay Now') }}
                                            <span id="gatewayCurrencyAmount" class="checkout-btn__amount"></span>
                                        </button>
                                        @if(!$isTransactionModel)
                                        <button type="button" class="checkout-btn checkout-btn--mpesa d-none" id="mpesaPayBtn">
                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                                                <path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 9.81" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                            </svg>
                                            {{ __('Pay via M-Pesa Code') }}
                                            <span id="mpesaGatewayCurrencyAmount" class="checkout-btn__amount"></span>
                                        </button>
                                        @endif
                                    </div>

                                </form>
                            </div>

                        </div>{{-- /.checkout-layout --}}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- M-Pesa Transaction Code Modal — only needed for non-transaction owners --}}
    @if(!$isTransactionModel)
    <div class="modal fade" id="mpesaCodePaymentMethodModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-content-custom">
                <div class="modal-header-custom">
                    <h5 class="modal-title-custom">{{ __('M-Pesa Manual Payment') }}</h5>
                    <button type="button" class="modal-close-custom" data-bs-dismiss="modal" aria-label="Close">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                            <path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </button>
                </div>
                <div class="modal-body-custom">
                    <form id="pay-invoice-mpesa-code-form"
                          action="{{ route('payment.checkout') }}"
                          method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" id="mpesa_code_account_id" name="mpesa_account_id" value="">
                        <input type="hidden" name="invoice_id"          value="{{ $invoice->id }}">
                        <input type="hidden" id="mpesa_selectGateway"   name="gateway">
                        <input type="hidden" id="mpesa_selectCurrency"  name="currency">
                        <input type="hidden" id="mpesa_default-currency" name="default-currency" value="{{ json_encode(session('default_currency')) }}">

                        <div class="mpesa-modal-greeting">
                            <div class="mpesa-modal-greeting__avatar">
                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                            </div>
                            <div>
                                <p class="mpesa-modal-greeting__name">{{ __('Hello,') }} {{ auth()->user()->name }}</p>
                                <p class="mpesa-modal-greeting__sub">{{ __('Follow the steps below to complete your payment.') }}</p>
                            </div>
                        </div>

                        <div class="mpesa-steps">
                            <div class="mpesa-step"><div class="mpesa-step__num">1</div><p>{{ __('Go to the Safaricom SIM Tool Kit and select the M-Pesa menu.') }}</p></div>
                            <div class="mpesa-step"><div class="mpesa-step__num">2</div><p>{{ __('Select') }} <strong>Lipa na M-Pesa</strong>.</p></div>
                            <div id="mpesa-code-payment-paybill">
                                <div class="mpesa-step"><div class="mpesa-step__num">3</div><p>{{ __('Select') }} <strong>Pay Bill</strong> → {{ __('Enter Business No.') }} <strong id="bs-number" class="mpesa-highlight">111739</strong></p></div>
                                <div class="mpesa-step"><div class="mpesa-step__num">4</div><p>{{ __('Enter Account Number:') }} <strong id="acc-number" class="mpesa-highlight"></strong></p></div>
                            </div>
                            <div id="mpesa-code-payment-till">
                                <div class="mpesa-step"><div class="mpesa-step__num">3</div><p>{{ __('Select') }} <strong>Buy Goods & Services</strong> → {{ __('Till Number:') }} <strong id="till-number" class="mpesa-highlight"></strong></p></div>
                            </div>
                            <div class="mpesa-step">
                                <div class="mpesa-step__num" id="mpesa-final-step">5</div>
                                <p>{{ __('Enter amount:') }} <strong id="mpesa-amount" class="mpesa-highlight"></strong>, {{ __('then your M-Pesa PIN.') }}</p>
                            </div>
                        </div>

                        <div class="form-field mt-3">
                            <label class="form-field__label">{{ __('M-Pesa Transaction Code') }}</label>
                            <input type="text" id="mpesaTransactionCode" name="mpesa_transaction_code"
                                   class="form-input-custom" placeholder="{{ __('e.g. RGH7X2K1AB') }}">
                        </div>
                        <button type="button" class="checkout-btn w-100 mt-3" id="mpesaCodeSubmitBtn">{{ __('Submit Code') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif

    <input type="hidden" id="invoiceAmount" value="{{ $invoice->amount }}">

@endsection

@push('style')
<style>
    .dash-header{display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:1rem}
    .dash-title{font-size:22px;font-weight:500;color:#111827;margin:0 0 4px}
    .dash-subtitle{font-size:14px;color:#6b7280;margin:0}
    .mp-breadcrumb{display:flex;align-items:center;gap:6px;list-style:none;padding:0;margin:0;font-size:13px;color:#9ca3af}
    .mp-breadcrumb li:not(:last-child)::after{content:'/';margin-left:6px;color:#d1d5db}
    .mp-breadcrumb a{color:#185FA5;text-decoration:none}
    .mp-breadcrumb a:hover{text-decoration:underline}
    .checkout-layout{display:grid;grid-template-columns:1fr 1.2fr;gap:1.5rem;align-items:start}
    .checkout-panel{background:#fff;border:0.5px solid #e5e7eb;border-radius:14px;padding:1.5rem;position:relative;overflow:hidden}
    .checkout-panel--cart::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;background:linear-gradient(90deg,#185FA5,#2E86DE);border-radius:14px 14px 0 0}
    .checkout-panel--payment::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;background:linear-gradient(90deg,#1D9E75,#27C494);border-radius:14px 14px 0 0}
    .panel-header{display:flex;align-items:center;gap:10px;margin-bottom:1.25rem;padding-bottom:1rem;border-bottom:0.5px solid #f3f4f6}
    .panel-header__icon{width:36px;height:36px;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
    .panel-header__icon--blue{background:#E6F1FB;color:#185FA5}
    .panel-header__icon--green{background:#E1F5EE;color:#1D9E75}
    .panel-title{font-size:16px;font-weight:500;color:#111827;margin:0}
    .invoice-detail-rows{display:flex;flex-direction:column;gap:0;margin-bottom:1rem}
    .invoice-detail-row{display:flex;align-items:center;justify-content:space-between;padding:10px 4px;border-bottom:0.5px solid #f3f4f6;font-size:13px}
    .invoice-detail-row:last-child{border-bottom:none}
    .invoice-detail-label{color:#6b7280;font-weight:400}
    .invoice-detail-value{color:#111827;font-weight:500;text-align:right}
    .cart-total-row{display:flex;align-items:center;justify-content:space-between;padding:12px 14px;background:#f0f7ff;border-radius:10px;border:0.5px solid #d0e8fb;margin-bottom:1rem}
    .cart-total-label{font-size:13px;font-weight:500;color:#374151}
    .cart-total-value{font-size:18px;font-weight:700;color:#185FA5}
    /* Transaction model M-Pesa branding block */
    .txn-mpesa-brand{display:flex;align-items:center;gap:14px;background:#fafafa;border:0.5px solid #e5e7eb;border-radius:12px;padding:14px 16px;margin-bottom:1rem}
    .txn-mpesa-brand__title{font-size:15px;font-weight:600;color:#111827;margin:0 0 3px}
    .txn-mpesa-brand__sub{font-size:12px;color:#6b7280;margin:0;line-height:1.5}
    .txn-mpesa-note{display:flex;align-items:flex-start;gap:8px;background:#FFFBEB;border:0.5px solid #FDE68A;border-radius:8px;padding:10px 12px;font-size:12px;color:#92400E;margin-bottom:1rem}
    /* Standard gateway grid */
    .gateway-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:10px;margin-bottom:1.25rem}
    .gateway-option{position:relative;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:8px;padding:14px 10px;border:1.5px solid #e5e7eb;border-radius:12px;cursor:pointer;transition:border-color .15s,box-shadow .15s,background .15s;background:#fff;text-align:center}
    .gateway-option:hover{border-color:#185FA5;background:#f8fbff}
    .gateway-option.active{border-color:#185FA5;background:#EBF4FF;box-shadow:0 0 0 3px rgba(24,95,165,.1)}
    .gateway-option__radio{position:absolute;top:10px;left:10px}
    .gateway-option__radio input{accent-color:#185FA5}
    .gateway-option__body{display:flex;flex-direction:column;align-items:center;gap:6px}
    .gateway-option__img{height:32px;width:auto;object-fit:contain}
    .gateway-option__label{font-size:12px;font-weight:500;color:#374151}
    .gateway-option__check{position:absolute;top:8px;right:8px;width:20px;height:20px;background:#185FA5;border-radius:50%;display:none;align-items:center;justify-content:center;color:#fff}
    .gateway-option.active .gateway-option__check{display:flex}
    .gateway-tab-content{margin-bottom:1.25rem}
    .gateway-detail-panel{background:#fafafa;border:0.5px solid #e5e7eb;border-radius:12px;padding:1.25rem;margin-top:4px}
    .gateway-detail-title{display:flex;align-items:center;gap:8px;font-size:14px;font-weight:500;color:#111827;margin:0 0 1rem;padding-bottom:10px;border-bottom:0.5px solid #e5e7eb}
    .form-field{margin-bottom:1rem}
    .form-field__label{display:block;font-size:12px;font-weight:500;color:#6b7280;text-transform:uppercase;letter-spacing:.04em;margin-bottom:6px}
    .form-field__error{font-size:12px;color:#ef4444;margin-top:4px;display:block}
    .form-select-custom,.form-input-custom{width:100%;padding:9px 12px;font-size:13px;color:#374151;background-color:#fff;border:0.5px solid #e5e7eb;border-radius:8px;transition:border-color .15s,box-shadow .15s;outline:none}
    .form-select-custom{appearance:none;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='none'%3E%3Cpath d='M6 9l6 6 6-6' stroke='%236b7280' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 10px center;padding-right:32px}
    .form-select-custom:focus,.form-input-custom:focus{border-color:#185FA5;box-shadow:0 0 0 3px rgba(24,95,165,.1)}
    .info-box{background:#f0f7ff;border:0.5px solid #d0e8fb;border-radius:8px;padding:10px 14px;font-size:13px;color:#374151}
    .file-upload-area{position:relative}
    .file-upload-input{position:absolute;inset:0;width:100%;height:100%;opacity:0;cursor:pointer;z-index:2}
    .file-upload-label{display:flex;flex-direction:column;align-items:center;justify-content:center;gap:6px;padding:20px;border:1.5px dashed #d1d5db;border-radius:10px;background:#fafafa;cursor:pointer;transition:border-color .15s,background .15s;color:#6b7280;font-size:13px;text-align:center}
    .file-upload-label small{font-size:11px;color:#9ca3af}
    .file-upload-area:hover .file-upload-label{border-color:#185FA5;background:#f0f7ff;color:#185FA5}
    .mpesa-info-note{display:flex;align-items:flex-start;gap:8px;background:#FFFBEB;border:0.5px solid #FDE68A;border-radius:8px;padding:10px 12px;font-size:12px;color:#92400E;margin-top:4px}
    .checkout-action-row{display:flex;flex-direction:column;gap:10px}
    .checkout-btn{display:flex;align-items:center;justify-content:center;gap:10px;width:100%;padding:13px 20px;background:#185FA5;color:#fff;font-size:15px;font-weight:600;border:none;border-radius:10px;cursor:pointer;transition:background .15s,transform .15s,box-shadow .15s;letter-spacing:.01em}
    .checkout-btn:hover{background:#0F4A84;transform:translateY(-1px);box-shadow:0 6px 16px rgba(24,95,165,.3)}
    .checkout-btn--mpesa{background:#1D9E75}
    .checkout-btn--mpesa:hover{background:#178060;box-shadow:0 6px 16px rgba(29,158,117,.3)}
    .checkout-btn__amount{font-size:13px;opacity:.85;font-weight:500}
    .w-100{width:100%}
    .modal-content.modal-content-custom{background:#fff;border-radius:16px;border:none;overflow:hidden;box-shadow:0 20px 40px rgba(0,0,0,.15)}
    .modal-header-custom{display:flex;align-items:center;justify-content:space-between;padding:1.25rem 1.5rem;border-bottom:0.5px solid #e5e7eb}
    .modal-title-custom{font-size:17px;font-weight:500;color:#111827;margin:0}
    .modal-close-custom{display:flex;align-items:center;justify-content:center;width:32px;height:32px;border-radius:8px;border:none;background:transparent;color:#9ca3af;cursor:pointer;transition:background .15s,color .15s}
    .modal-close-custom:hover{background:#f3f4f6;color:#374151}
    .modal-body-custom{padding:1.5rem}
    .mpesa-modal-greeting{display:flex;align-items:center;gap:12px;background:#f0f7ff;border-radius:10px;padding:12px 14px;margin-bottom:1.25rem}
    .mpesa-modal-greeting__avatar{width:40px;height:40px;border-radius:50%;background:#185FA5;color:#fff;display:flex;align-items:center;justify-content:center;font-size:16px;font-weight:600;flex-shrink:0}
    .mpesa-modal-greeting__name{font-size:14px;font-weight:500;color:#111827;margin:0 0 2px}
    .mpesa-modal-greeting__sub{font-size:12px;color:#6b7280;margin:0}
    .mpesa-steps{display:flex;flex-direction:column;gap:10px;margin-bottom:6px}
    .mpesa-step{display:flex;align-items:flex-start;gap:12px;font-size:13px;color:#374151;line-height:1.5}
    .mpesa-step__num{width:24px;height:24px;border-radius:50%;background:#185FA5;color:#fff;font-size:11px;font-weight:600;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:1px}
    .mpesa-highlight{background:#E6F1FB;color:#185FA5;padding:1px 6px;border-radius:4px;font-weight:600}
    #mpesa-preloader{position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:9999;display:flex;align-items:center;justify-content:center}
    #mpesa-preloaderInner{background:#fff;border-radius:16px;padding:2rem;max-width:420px;width:90%;display:flex;flex-direction:column;align-items:center;gap:16px;text-align:center;box-shadow:0 20px 40px rgba(0,0,0,.2)}
    #mpesa-preloaderInner img:first-child{width:80px;height:80px;object-fit:contain;border-radius:12px}
    #mpesa-preloaderInner p{font-size:13px;color:#374151;margin:0;line-height:1.6}
    #mpesa-timer{font-weight:600;color:#185FA5}
    #mpesa-preloaderInner img:last-child{width:36px}
    .currency-section{background:#fafafa;border:0.5px solid #e5e7eb;border-radius:10px;overflow:hidden;margin-top:1rem}
    @keyframes currencyPulse{0%{box-shadow:0 0 0 0 rgba(24,95,165,.35);border-color:#185FA5}50%{box-shadow:0 0 0 6px rgba(24,95,165,0);border-color:#185FA5}100%{box-shadow:0 0 0 0 rgba(24,95,165,0);border-color:#e5e7eb}}
    .currency-section--pulse{animation:currencyPulse 1.2s ease-out 5}
    .currency-section-header{display:flex;align-items:center;gap:7px;font-size:12px;font-weight:500;color:#6b7280;text-transform:uppercase;letter-spacing:.04em;padding:10px 14px;border-bottom:0.5px solid #e5e7eb;background:#fff}
    .currency-section .table{margin:0}
    .currency-section .table td,.currency-section .table th{font-size:13px;color:#374151;padding:9px 14px;border-color:#f3f4f6}
    .toast-info{background-color:#e6f0fa !important;border-left:6px solid #3a7bd5 !important;color:#1a3d6d !important}
    .toast-title{font-weight:600;color:#1a3d6d !important}
    .toast-message{color:#1a3d6d !important}
    @media(max-width:900px){.checkout-layout{grid-template-columns:1fr}}
    @media(max-width:480px){.gateway-grid{grid-template-columns:1fr 1fr}}
</style>
@endpush

@push('script')
<script>
    // ── Pass server-side flags to JS ─────────────────────────────────────────
    window.isTransactionModel  = {{ $isTransactionModel ? 'true' : 'false' }};
    window.ownerMpesaGatewayId = {{ $ownerMpesaGatewayId ?? 'null' }};
    window.mpesaIconUrl        = "{{ asset('assets/images/gateway-icon/mpesa.jpg') }}";
</script>
<script src="{{ asset('assets/js/custom/tenant-invoice-pay.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {

        @if(!$isTransactionModel)
        // ── Standard flow only: gateway option highlight + currency section observer ──
        document.querySelectorAll('.gateway-option').forEach(function (option) {
            option.addEventListener('click', function () {
                document.querySelectorAll('.gateway-option').forEach(function (o) { o.classList.remove('active'); });
                option.classList.add('active');
                var radio = option.querySelector('input[type="radio"]');
                if (radio) radio.checked = true;
            });
        });

        var currencyAppend  = document.getElementById('currencyAppend');
        var currencySection = document.getElementById('currencySection');
        if (currencyAppend && currencySection) {
            var observer = new MutationObserver(function () {
                currencySection.style.display = currencyAppend.children.length > 0 ? 'block' : 'none';
            });
            observer.observe(currencyAppend, { childList: true });
        }
        @endif

    });
</script>
@endpush