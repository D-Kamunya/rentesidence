<div class="col-md-5">
    <div class="tenant-portal-invoice-details-leftside bg-off-white theme-border p-20 radius-4 mb-25">
        <div class="row align-items-center">
            <div class="col-12">
                <div class="d-flex align-items-center justify-content-between mb-25">
                    <h4 class="mb-0">{{ __('Invoice Details') }}</h4>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="table-responsive">
                    <table class="table theme-border p-20">
                        <tbody>
                            <tr>
                                <td>{{ __('Name') }}</td>
                                <td>
                                    <h6 class="tenant-invoice-tbl-right-text text-end">
                                        {{ $plan->name }}</h6>
                                </td>
                            </tr>
                            <tr>
                                <td>{{ __('Duration') }}</td>
                                <td>
                                    <h6 class="tenant-invoice-tbl-right-text text-end">
                                        {{ getDurationName($durationType) }}</h6>
                                </td>
                            </tr>
                            @if ($durationType == PACKAGE_DURATION_TYPE_MONTHLY)
                                <tr>
                                    <td>{{ __('Amount') }}</td>
                                    <td>
                                        <h6 class="tenant-invoice-tbl-right-text text-end">
                                            <input type="hidden" id="planAmount" value="{{ $plan->monthly_price }}">
                                            {{ currencyPrice($plan->monthly_price) }}
                                        </h6>
                                    </td>
                                </tr>
                            @else
                                <tr>
                                    <td>{{ __('Amount') }}</td>
                                    <td>
                                        <h6 class="tenant-invoice-tbl-right-text text-end">
                                            <input type="hidden" id="planAmount" value="{{ $plan->yearly_price }}">
                                            {{ currencyPrice($plan->yearly_price) }}
                                        </h6>
                                    </td>
                                </tr>
                            @endif
                            <tr>
                                <td>{{ __('Start Date') }}</td>
                                <td>
                                    <h6 class="tenant-invoice-tbl-right-text text-end">{{ $startDate }}</h6>
                                </td>
                            </tr>
                            <tr>
                                <td>{{ __('End Date') }}</td>
                                <td>
                                    <h6 class="tenant-invoice-tbl-right-text text-end">{{ $endDate }}</h6>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <table class="table theme-border p-20">
                        <tbody id="currencyAppend"></tbody>
                    </table>
                    <table class="table theme-border p-20 d-none" id="bankAppend">
                        <tbody>
                            <tr>
                                <td>{{ __('Bank Deposit') }}</td>
                            </tr>
                            <tr>
                                <td>
                                    <label
                                        class="label-text-title color-heading font-medium mb-2">{{ __('Bank Name') }}</label>
                                    <select name="bank_id" id="bank_id" class="form-control mb-2">
                                        <option value="">{{ __('Select Option') }}</option>
                                        @foreach ($banks as $bank)
                                            <option value="{{ $bank->id }}"
                                                data-details="{{ nl2br($bank->details) }}">{{ $bank->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="topic-content-item d-block bg-white theme-border radius-12 my-2 d-none"
                                        id="bankDetails">
                                        <div
                                            class="topic-content-item-btns d-flex align-content-center justify-content-between">
                                            <p class="font-12 my-2 ps-2"></p>
                                        </div>
                                    </div>
                                    <label
                                        class="label-text-title color-heading font-medium mb-2">{{ __('Upload Deposit Slip') }}
                                        (png, jpg)</label>
                                    <input type="file" name="bank_slip" id="bank_slip" class="form-control"
                                        accept="image/png, image/jpg">
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <table class="table theme-border p-20 d-none" id="mpesaAccountAppend">
                        <tbody>
                            <tr>
                                <td>{{ __('Make Mpesa Payment') }}</td>
                            </tr>
                            <tr>
                                <td>
                                    <label
                                        class="label-text-title color-heading font-medium mb-2">{{ __('Mpesa Account') }}</label>
                                    <select name="mpesa_account_id" id="mpesa_account_id" class="form-control mb-2">
                                        <option value="">{{ __('Select Option') }}</option>
                                        @foreach ($mpesaAccounts as $mpesaAccount)
                                            <option value="{{ $mpesaAccount->id }}"
                                                data-details="{{ nl2br($mpesaAccount->account_type) }}">
                                                    {{ $mpesaAccount->account_type }}

                                                    @if ($mpesaAccount->account_type === 'PAYBILL')
                                                        - Paybill: {{ $mpesaAccount->paybill }}, Account Name: {{ $mpesaAccount->account_name }}
                                                    @elseif ($mpesaAccount->account_type === 'TILLNUMBER')
                                                        - Till Number: {{ $mpesaAccount->till_number }}
                                                    @endif
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="col-md-7">
    <div class="row justify-content-center" id="gatewaySection">
        @foreach ($gateways as $gateway)
            <div class="col-sm-6 col-md-6 col-lg-4 col-xl-4 mb-25">
                <div class="payment-method-item text-center h-100 theme-border radius-10">
                    <div class="payment-method-item-title bg-light">
                        <h6 class="font-11 font-semi-bold text-center">{{ $gateway->title }}</h6>
                    </div>
                    <div class="payment-method-img">
                        <img src="{{ asset($gateway->image) }}" alt="" class="img-fluid">
                    </div>
                    <button type="button" data-gateway="{{ $gateway->slug }}" data-id={{ $gateway->id }}
                        data-plan_id="{{ $plan->id }}" data-duration_type="{{ $durationType }}"
                        data-quantity="{{ $quantity }}"
                        class="theme-btn-outline w-100 mt-25 select-payment-gateway paymentGateway">{{ __('Select') }}</button>
                </div>
            </div>
        @endforeach
    </div>
</div>
