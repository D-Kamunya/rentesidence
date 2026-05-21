<style>
/* ── Gateway Modal Layout ──────────────────────────────────── */
.gw-modal-wrap {
    font-size: 13px;
    color: var(--gray-700, #374151);
}

/* ── Order Summary Card ────────────────────────────────────── */
.gw-summary-card {
    background: #fff;
    border: 0.5px solid var(--blue-faint, #185ea56e);
    border-radius: 12px;
    overflow: hidden;
    box-shadow:
        0 4px 12px rgba(0,0,0,.04),
        0 0 0 1px rgba(24,95,165,.05),
        0 6px 18px rgba(24,95,165,.06);
    margin-bottom: 16px;
}

.gw-summary-card:last-child {
    margin-bottom: 0;
}

.gw-summary-head {
    padding: 16px 20px;
    border-bottom: 0.5px solid var(--gray-200, #e5e7eb);
    background: var(--gray-50, #fafafa);
    display: flex;
    align-items: center;
    gap: 10px;
}

.gw-summary-head-icon {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    background: var(--blue-light, #E6F1FB);
    color: var(--blue, #185FA5);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.gw-summary-head h4 {
    font-size: 14px;
    font-weight: 600;
    color: var(--gray-900, #111827);
    margin: 0;
}

.gw-summary-body {
    padding: 16px 20px;
}

.gw-summary-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 0;
    border-bottom: 0.5px solid var(--gray-100, #f3f4f6);
}

.gw-summary-row:last-child {
    border-bottom: none;
    padding-bottom: 0;
}

.gw-summary-label {
    font-size: 12px;
    font-weight: 500;
    color: var(--gray-500, #6b7280);
    display: flex;
    align-items: center;
    gap: 6px;
}

.gw-summary-label svg {
    flex-shrink: 0;
    color: var(--gray-400, #9ca3af);
}

.gw-summary-value {
    font-size: 13px;
    font-weight: 600;
    color: var(--gray-900, #111827);
    text-align: right;
}

.gw-summary-value--highlight {
    font-size: 16px;
    color: var(--blue, #185FA5);
}

.gw-summary-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 10px;
    font-weight: 500;
    padding: 2px 8px;
    border-radius: 99px;
    background: var(--green-light, #E1F5EE);
    color: var(--green-dark, #0F6E56);
}

.gw-summary-total {
    margin-top: 12px;
    padding: 12px 16px;
    background: var(--blue-light, #E6F1FB);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.gw-summary-total-label {
    font-size: 13px;
    font-weight: 600;
    color: var(--blue, #185FA5);
}

.gw-summary-total-value {
    font-size: 20px;
    font-weight: 700;
    color: var(--blue, #185FA5);
}

/* ── Currency Selection Section ─────────────────────────── */
.gw-currency-section {
    background: #fff;
    border: 0.5px solid var(--gray-200, #e5e7eb);
    border-radius: 10px;
    overflow: hidden;
    margin-bottom: 16px;
}

.gw-currency-head {
    padding: 10px 16px;
    background: var(--gray-50, #fafafa);
    border-bottom: 0.5px solid var(--gray-200, #e5e7eb);
    display: flex;
    align-items: center;
    gap: 8px;
}

.gw-currency-head-text {
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--gray-400, #9ca3af);
}

.gw-currency-list {
    padding: 12px 16px;
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.gw-currency-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 14px;
    border: 0.5px solid var(--gray-200, #e5e7eb);
    border-radius: 8px;
    cursor: pointer;
    transition: all .18s ease;
    background: #fff;
}

.gw-currency-item:hover {
    border-color: var(--blue, #185FA5);
    background: var(--blue-light, #E6F1FB);
}

.gw-currency-item.is-selected {
    border-color: var(--blue, #185FA5);
    border-width: 1.5px;
    background: var(--blue-light, #E6F1FB);
    box-shadow: 0 0 0 1px rgba(24,95,165,.12);
}

.gw-currency-item-left {
    display: flex;
    align-items: center;
    gap: 10px;
}

.gw-currency-radio {
    width: 18px;
    height: 18px;
    border-radius: 50%;
    border: 1.5px solid var(--gray-300, #d1d5db);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    transition: all .18s ease;
}

.gw-currency-item.is-selected .gw-currency-radio {
    border-color: var(--blue, #185FA5);
    background: var(--blue, #185FA5);
}

.gw-currency-radio::after {
    content: '';
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #fff;
    opacity: 0;
    transition: opacity .18s ease;
}

.gw-currency-item.is-selected .gw-currency-radio::after {
    opacity: 1;
}

.gw-currency-name {
    font-size: 13px;
    font-weight: 500;
    color: var(--gray-900, #111827);
}

.gw-currency-amount {
    font-size: 13px;
    font-weight: 600;
    color: var(--gray-700, #374151);
    text-align: right;
}

.gw-currency-rate {
    font-size: 10px;
    color: var(--gray-400, #9ca3af);
}

/* ── Payment Details Section ─────────────────────────────── */
.gw-details-section {
    background: #fff;
    border: 0.5px solid var(--gray-200, #e5e7eb);
    border-radius: 10px;
    overflow: hidden;
    margin-bottom: 16px;
}

.gw-details-head {
    padding: 10px 16px;
    background: var(--gray-50, #fafafa);
    border-bottom: 0.5px solid var(--gray-200, #e5e7eb);
    display: flex;
    align-items: center;
    gap: 8px;
}

.gw-details-head-text {
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--gray-400, #9ca3af);
}

.gw-details-body {
    padding: 14px 16px;
}

.gw-details-body label {
    display: block;
    font-size: 11px;
    font-weight: 500;
    color: var(--gray-700, #374151);
    margin-bottom: 6px;
}

.gw-details-body select,
.gw-details-body input[type="file"] {
    width: 100%;
    padding: 8px 12px;
    font-size: 12px;
    border: 0.5px solid var(--gray-200, #e5e7eb);
    border-radius: 7px;
    background: #fff;
    color: var(--gray-900, #111827);
    transition: all .15s ease;
}

.gw-details-body select:focus,
.gw-details-body input[type="file"]:focus {
    outline: none;
    border-color: var(--blue, #185FA5);
    box-shadow: 0 0 0 3px rgba(24,95,165,.1);
}

.gw-bank-details-card {
    margin-top: 10px;
    padding: 12px;
    background: var(--blue-light, #E6F1FB);
    border-radius: 8px;
    font-size: 11px;
    color: var(--gray-700, #374151);
    line-height: 1.6;
}

/* ── Gateway Grid Section ────────────────────────────────── */
.gw-gateway-section {
    padding-right: 0;
}

.gw-gateway-section-label {
    font-size: 10px;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: .07em;
    color: var(--gray-400, #9ca3af);
    margin-bottom: 16px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.gw-gateway-section-label::after {
    content: '';
    flex: 1;
    height: 0.5px;
    background: var(--gray-200, #e5e7eb);
}

.gw-gateway-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 12px;
    margin-bottom: 24px;
}

.gw-gateway-card {
    background: #fff;
    border: 0.5px solid var(--blue-faint, #185ea56e);
    border-radius: 12px;
    padding: 16px;
    text-align: center;
    cursor: pointer;
    transition: all .22s ease;
    box-shadow:
        0 4px 12px rgba(0,0,0,.04),
        0 0 0 1px rgba(24,95,165,.05);
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 12px;
    position: relative;
}

.gw-gateway-card:hover {
    border-color: var(--blue, #185FA5);
    transform: translateY(-2px);
    box-shadow:
        0 10px 25px rgba(0,0,0,.06),
        0 0 0 1px rgba(24,95,165,.12),
        0 12px 30px rgba(24,95,165,.18);
}

.gw-gateway-card.is-selected {
    border-color: var(--blue, #185FA5);
    border-width: 1.5px;
    background: var(--blue-light, #E6F1FB);
    box-shadow:
        0 4px 12px rgba(0,0,0,.04),
        0 0 0 1px rgba(24,95,165,.2),
        0 8px 24px rgba(24,95,165,.15);
}

.gw-gateway-card.is-selected::before {
    content: '';
    position: absolute;
    top: 10px;
    right: 10px;
    width: 18px;
    height: 18px;
    border-radius: 50%;
    background: var(--blue, #185FA5);
}

.gw-gateway-card.is-selected::after {
    content: '✓';
    position: absolute;
    top: 10px;
    right: 10px;
    width: 18px;
    height: 18px;
    color: #fff;
    font-size: 10px;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    line-height: 1;
}

.gw-gateway-card-badge {
    font-size: 9px;
    font-weight: 600;
    padding: 3px 8px;
    border-radius: 99px;
    background: var(--gray-100, #f3f4f6);
    color: var(--gray-500, #6b7280);
    text-transform: uppercase;
    letter-spacing: 0.05em;
    width: 100%;
}

.gw-gateway-card-img {
    width: 64px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.gw-gateway-card-img img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
}

.gw-gateway-card-name {
    font-size: 12px;
    font-weight: 600;
    color: var(--gray-900, #111827);
    margin: 0;
}

.gw-gateway-card-btn {
    width: 100%;
    padding: 6px 14px;
    font-size: 11px;
    font-weight: 500;
    border-radius: 7px;
    border: 0.5px solid var(--blue-faint, #185ea56e);
    background: #fff;
    color: var(--blue, #185FA5);
    cursor: pointer;
    transition: all .15s ease;
}

.gw-gateway-card:hover .gw-gateway-card-btn {
    background: var(--blue, #185FA5);
    color: #fff;
    border-color: var(--blue, #185FA5);
}

.gw-gateway-card.is-selected .gw-gateway-card-btn {
    background: var(--blue, #185FA5);
    color: #fff;
    border-color: var(--blue, #185FA5);
}

/* ── Pay Button Area ─────────────────────────────────────── */
.gw-pay-section {
    margin-top: auto;
    padding: 20px 24px;
    border-top: 0.5px solid var(--gray-200, #e5e7eb);
    background: var(--gray-50, #fafafa);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
}

.gw-pay-amount {
    display: flex;
    flex-direction: column;
}

.gw-pay-amount-label {
    font-size: 10px;
    font-weight: 500;
    color: var(--gray-400, #9ca3af);
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.gw-pay-amount-value {
    font-size: 18px;
    font-weight: 700;
    color: var(--gray-900, #111827);
}

.gw-pay-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 10px 24px;
    font-size: 13px;
    font-weight: 600;
    border-radius: 8px;
    border: none;
    cursor: pointer;
    transition: all .15s ease;
    background: var(--blue, #185FA5);
    color: #fff;
    white-space: nowrap;
    min-width: 140px;
}

.gw-pay-btn:hover {
    background: var(--blue-hover, #0F4A84);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(24,95,165,.25);
}

.gw-pay-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
}

/* ── Modal Container Adjustments ─────────────────────────── */
#paymentMethodModal .modal-body {
    padding: 0;
}

#paymentMethodModal .modal-content {
    border-radius: 12px;
    overflow: hidden;
}

/* ── Responsive ─────────────────────────────────────────── */
@media (max-width: 768px) {
    .gw-gateway-grid {
        grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
    }
    
    .gw-pay-section {
        flex-direction: column;
        align-items: stretch;
        text-align: center;
    }
    
    .gw-pay-btn {
        width: 100%;
    }
}
</style>

<div class="gw-modal-wrap">
    <div class="row g-0">
        {{-- Left Column - Order Summary, Currency & Payment Details --}}
        <div class="col-md-5" style="padding-right: 12px;">
            <div style="padding: 20px 0 20px 24px;">
                {{-- Order Summary Card --}}
                <div class="gw-summary-card">
                    <div class="gw-summary-head">
                        <div class="gw-summary-head-icon">
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                <rect x="2" y="3" width="12" height="10" rx="2" stroke="currentColor" stroke-width="1.5"/>
                                <path d="M2 7h12" stroke="currentColor" stroke-width="1.5"/>
                                <circle cx="5" cy="10.5" r="1" fill="currentColor"/>
                                <circle cx="8" cy="10.5" r="1" fill="currentColor"/>
                            </svg>
                        </div>
                        <h4>{{ __('Order Summary') }}</h4>
                    </div>
                    <div class="gw-summary-body">
                        {{-- Plan Name --}}
                        <div class="gw-summary-row">
                            <span class="gw-summary-label">
                                <svg width="12" height="12" viewBox="0 0 12 12" fill="none">
                                    <rect x="1.5" y="1.5" width="9" height="9" rx="2" stroke="currentColor" stroke-width="1.2"/>
                                </svg>
                                {{ __('Plan') }}
                            </span>
                            <span class="gw-summary-value">{{ $plan->name }}</span>
                        </div>

                        {{-- Duration --}}
                        <div class="gw-summary-row">
                            <span class="gw-summary-label">
                                <svg width="12" height="12" viewBox="0 0 12 12" fill="none">
                                    <circle cx="6" cy="6" r="4.5" stroke="currentColor" stroke-width="1.2"/>
                                    <path d="M6 3.5V6L8 7.5" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/>
                                </svg>
                                {{ __('Duration') }}
                            </span>
                            <span class="gw-summary-value">
                                {{ getDurationName($durationType) }}
                                @if($durationType == PACKAGE_DURATION_TYPE_YEARLY)
                                    <span class="gw-summary-badge">
                                        <svg width="8" height="8" viewBox="0 0 8 8" fill="currentColor">
                                            <path d="M4 0L5 3L8 4L5 5L4 8L3 5L0 4L3 3L4 0Z"/>
                                        </svg>
                                        2 months free
                                    </span>
                                @endif
                            </span>
                        </div>

                        {{-- Amount --}}
                        <div class="gw-summary-row">
                            <span class="gw-summary-label">
                                <svg width="12" height="12" viewBox="0 0 12 12" fill="none">
                                    <circle cx="6" cy="6" r="4.5" stroke="currentColor" stroke-width="1.2"/>
                                    <text x="3.5" y="7.5" font-size="7" fill="currentColor" font-weight="600">$</text>
                                </svg>
                                {{ __('Amount') }}
                            </span>
                            <span class="gw-summary-value gw-summary-value--highlight">
                                @if ($durationType == PACKAGE_DURATION_TYPE_MONTHLY)
                                    <input type="hidden" id="planAmount" value="{{ $plan->monthly_price }}">
                                    {{ currencyPrice($plan->monthly_price) }}
                                @else
                                    <input type="hidden" id="planAmount" value="{{ $plan->yearly_price }}">
                                    {{ currencyPrice($plan->yearly_price) }}
                                @endif
                            </span>
                        </div>

                        {{-- Start Date --}}
                        <div class="gw-summary-row">
                            <span class="gw-summary-label">
                                <svg width="12" height="12" viewBox="0 0 12 12" fill="none">
                                    <rect x="1.5" y="2" width="9" height="8.5" rx="1.5" stroke="currentColor" stroke-width="1.2"/>
                                    <path d="M3.5 1v2M8.5 1v2" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/>
                                </svg>
                                {{ __('Start Date') }}
                            </span>
                            <span class="gw-summary-value">{{ $startDate }}</span>
                        </div>

                        {{-- End Date --}}
                        <div class="gw-summary-row">
                            <span class="gw-summary-label">
                                <svg width="12" height="12" viewBox="0 0 12 12" fill="none">
                                    <path d="M2 8.5L6 4L10 8.5" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/>
                                </svg>
                                {{ __('End Date') }}
                            </span>
                            <span class="gw-summary-value">{{ $endDate }}</span>
                        </div>

                        {{-- Total --}}
                        <div class="gw-summary-total">
                            <span class="gw-summary-total-label">{{ __('Total Due') }}</span>
                            <span class="gw-summary-total-value">
                                @if ($durationType == PACKAGE_DURATION_TYPE_MONTHLY)
                                    {{ currencyPrice($plan->monthly_price) }}
                                @else
                                    {{ currencyPrice($plan->yearly_price) }}
                                @endif
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Currency Selection Section --}}
                <div id="currencyAppend" class="gw-currency-section" style="display:none;">
                    <div class="gw-currency-head">
                        <svg width="12" height="12" viewBox="0 0 12 12" fill="none">
                            <circle cx="6" cy="6" r="4.5" stroke="currentColor" stroke-width="1.2"/>
                            <path d="M4.5 5.5L6 7L8.5 4.5" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <span class="gw-currency-head-text">{{ __('Select Currency') }}</span>
                    </div>
                    <div class="gw-currency-list" id="currencyList">
                        {{-- Currency items will be injected here by JS --}}
                    </div>
                </div>

                {{-- Bank Deposit Details --}}
                <div id="bankAppend" class="gw-details-section d-none">
                    <div class="gw-details-head">
                        <svg width="12" height="12" viewBox="0 0 12 12" fill="none">
                            <rect x="1.5" y="2.5" width="9" height="7" rx="1.5" stroke="currentColor" stroke-width="1.2"/>
                            <path d="M3 6h6" stroke="currentColor" stroke-width="1.2"/>
                        </svg>
                        <span class="gw-details-head-text">{{ __('Bank Deposit') }}</span>
                    </div>
                    <div class="gw-details-body">
                        <label>{{ __('Select Bank') }}</label>
                        <select name="bank_id" id="bank_id" class="form-control mb-3">
                            <option value="">{{ __('Select Option') }}</option>
                            @foreach ($banks as $bank)
                                <option value="{{ $bank->id }}"
                                    data-details="{{ nl2br($bank->details) }}">{{ $bank->name }}
                                </option>
                            @endforeach
                        </select>
                        <div class="gw-bank-details-card d-none" id="bankDetails">
                            <p class="font-12 my-0"></p>
                        </div>
                        <label class="mt-3">{{ __('Upload Deposit Slip') }} <span style="color:var(--gray-400);font-weight:400;">(png, jpg)</span></label>
                        <input type="file" name="bank_slip" id="bank_slip" class="form-control"
                            accept="image/png, image/jpg">
                    </div>
                </div>

                {{-- Mpesa Details --}}
                <div id="mpesaAccountAppend" class="gw-details-section d-none">
                    <div class="gw-details-head">
                        <svg width="12" height="12" viewBox="0 0 12 12" fill="none">
                            <path d="M7 2.5L3 6L7 9.5" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <span class="gw-details-head-text">{{ __('M-Pesa Payment') }}</span>
                    </div>
                    <div class="gw-details-body">
                        <label>{{ __('Select M-Pesa Account') }}</label>
                        <select name="mpesa_account_id" id="mpesa_account_id" class="form-control">
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
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Column - Payment Gateways & Pay Button --}}
        <div class="col-md-7" style="padding-left: 12px;">
            <div style="padding: 20px 24px 0 0; display: flex; flex-direction: column; height: 100%;">
                <div style="flex: 1;">
                    <div class="gw-gateway-section-label">
                        <svg width="10" height="10" viewBox="0 0 10 10" fill="none">
                            <rect x="1" y="2.5" width="8" height="5" rx="1" stroke="currentColor" stroke-width="1.2"/>
                            <path d="M1 5h8" stroke="currentColor" stroke-width="1.2"/>
                        </svg>
                        {{ __('Select Payment Method') }}
                    </div>
                    <div class="gw-gateway-grid" id="gatewaySection">
                        @foreach ($gateways as $gateway)
                            <div class="gw-gateway-card">
                                <div class="gw-gateway-card-badge">{{ __('Payment Gateway') }}</div>
                                <div class="gw-gateway-card-img">
                                    <img src="{{ asset($gateway->image) }}" alt="{{ $gateway->title }}" class="img-fluid">
                                </div>
                                <p class="gw-gateway-card-name">{{ $gateway->title }}</p>
                                <button type="button" 
                                    data-gateway="{{ $gateway->slug }}" 
                                    data-id={{ $gateway->id }}
                                    data-plan_id="{{ $plan->id }}" 
                                    data-duration_type="{{ $durationType }}"
                                    data-quantity="{{ $quantity }}"
                                    class="gw-gateway-card-btn select-payment-gateway paymentGateway">
                                    {{ __('Select') }}
                                    <svg width="10" height="10" viewBox="0 0 10 10" fill="none" style="margin-left:4px;">
                                        <path d="M2 5h6M5 2l3 3-3 3" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </button>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Gateway card selection with persistent visual feedback
$(document).on("click", ".paymentGateway", function (e) {
    e.preventDefault();

    // Remove selected state from all gateway cards
    $(this).closest("#gatewaySection").find(".gw-gateway-card").removeClass("is-selected");
    $(this).closest("#gatewaySection").find("button").removeClass("active");
    $(this).closest("#gatewaySection").find(".payment-method-item").removeClass("border border-primary");
    
    // Add selected state to the clicked card
    $(this).closest(".gw-gateway-card").addClass("is-selected");
    $(this).parent().addClass("border border-primary");
    $(this).addClass("active");
    
    var selectGateway = $(this).data("gateway").replace(/\s+/g, "");
    $("#selectGateway").val(selectGateway);
    $("#selectCurrency").val("");
    $("#plan_id").val($(this).data("plan_id"));
    $("#duration_type").val($(this).data("duration_type"));
    $("#quantity").val($(this).data("quantity"));

    commonAjax(
        "GET",
        $("#getCurrencyByGatewayRoute").val(),
        getCurrencyRes,
        getCurrencyRes,
        { id: $(this).data("id") }
    );
    if (selectGateway == "bank") {
        $("#bank_id").val("");
        $("#bankDetails").addClass("d-none");
        $("#bank_slip").val("");
        $("#bankAppend").removeClass("d-none");
        $("#payBtn").removeClass("d-none");
        $("#bank_slip").attr("required", true);
        $("#bank_id").attr("required", true);
        $("#gatewayCurrencyAmount").text("");
        $("#mpesaGatewayCurrencyAmount").text("");
        $("#mpesaPayBtn").addClass("d-none");
        $("#mpesa_account_id").attr("required", false);
        $("#mpesaAccountAppend").addClass("d-none");
    } else if (selectGateway == "mpesa") {
        // No account selection needed — platform account is auto-resolved server-side.
        // Just hide bank UI and show the Pay Now button directly.
        $("#bank_slip").attr("required", false);
        $("#bank_id").attr("required", false);
        $("#bankAppend").addClass("d-none");
        $("#mpesaAccountAppend").addClass("d-none");  // hide dropdown — not needed
        $("#mpesa_account_id").attr("required", false);
        $("#payBtn").removeClass("d-none");
        $("#mpesaPayBtn").addClass("d-none");
        $("#gatewayCurrencyAmount").text("");
        $("#mpesaGatewayCurrencyAmount").text("");
        // Mirror hidden mpesa fields so the form submits correctly
        $("#mpesa_selectGateway").val(selectGateway);
        $("#mpesa_selectCurrency").val("");
        $("#mpesa_plan_id").val($(this).data("plan_id"));
        $("#mpesa_duration_type").val($(this).data("duration_type"));
        $("#mpesa_quantity").val($(this).data("quantity"));
    } else {
        $("#bank_slip").attr("required", false);
        $("#payBtn").removeClass("d-none");
        $("#bank_id").attr("required", false);
        $("#mpesa_account_id").attr("required", false);
        $("#gatewayCurrencyAmount").text("");
        $("#mpesaGatewayCurrencyAmount").text("");
        $("#mpesaPayBtn").addClass("d-none");
        $("#bankAppend").addClass("d-none");
        $("#mpesaAccountAppend").addClass("d-none");
    }
});

// Override getCurrencyRes to inject styled currency items
var originalGetCurrencyRes = getCurrencyRes;
getCurrencyRes = function(response) {
    var defaultCurrency = JSON.parse(
        document.getElementById("default-currency").value
    );
    var planAmount = parseFloat($("#planAmount").val()).toFixed(2);
    var entries = Object.entries(response.data);
    
    var html = '';
    entries.forEach((currency) => {
        let currencyAmount = currency[1].conversion_rate * Number(planAmount);
        html += `
            <div class="gw-currency-item gatewayCurrencyAmount" data-currency="${currency[1].currency}" data-symbol="${currency[1].symbol}" data-rate="${currency[1].conversion_rate}">
                <div class="gw-currency-item-left">
                    <div class="gw-currency-radio">
                        <input type="radio" name="gateway_currency_amount" id="${currency[1].id}" value="${gatewayCurrencyPrice(
                            Number(currencyAmount).toFixed(2),
                            currency[1].symbol
                        )}" style="display:none;">
                    </div>
                    <span class="gw-currency-name">${currency[1].currency}</span>
                </div>
                <div>
                    <div class="gw-currency-amount">${gatewayCurrencyPrice(
                        Number(currencyAmount).toFixed(2),
                        currency[1].symbol
                    )}</div>
                    <div class="gw-currency-rate">${gatewayCurrencyPrice(
                        Number(planAmount).toFixed(2),
                        defaultCurrency
                    )} × ${currency[1].conversion_rate}</div>
                </div>
            </div>`;
    });
    
    $("#currencyList").html(html);
    $("#currencyAppend").show();
    
    // Auto-select first currency
    if (entries.length > 0) {
        var firstCurrency = entries[0][1];
        var firstRadio = $("#currencyList input[type=radio]:first");
        firstRadio.prop("checked", true);
        
        // Mark first item as selected
        $("#currencyList .gw-currency-item:first").addClass("is-selected");
        
        var currencyAmount = firstCurrency.conversion_rate * Number(planAmount);
        var displayAmount = "(" + gatewayCurrencyPrice(
            Number(currencyAmount).toFixed(2),
            firstCurrency.symbol
        ) + ")";
        
        $("#gatewayCurrencyAmount").text(displayAmount);
        $("#selectCurrency").val(firstCurrency.currency);
        $("#mpesa_selectCurrency").val(firstCurrency.currency);
    }
    
    // Handle currency item clicks
    $("#currencyList").off("click", ".gw-currency-item").on("click", ".gw-currency-item", function() {
        var $item = $(this);
        var $radio = $item.find("input[type=radio]");
        
        // Update visual selection
        $("#currencyList .gw-currency-item").removeClass("is-selected");
        $item.addClass("is-selected");
        
        // Check the radio
        $radio.prop("checked", true);
        
        // Get the currency amount value
        var getCurrencyAmount = "(" + $radio.val() + ")";
        $("#gatewayCurrencyAmount").text(getCurrencyAmount);
        $("#selectCurrency").val($item.data("currency"));
        $("#mpesa_selectCurrency").val($item.data("currency"));
    });
};
</script>