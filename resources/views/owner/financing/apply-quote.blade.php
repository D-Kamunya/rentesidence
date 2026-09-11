@extends('owner.layouts.app')

@section('content')
<div class="main-content"><div class="page-content"><div class="container-fluid">
    <div class="page-content-wrapper bg-white p-30 radius-20">
        @include('centresidence._design')
        <div class="cs-titlebar">
            <div>
                <h1 class="cs-title">{{ __('Apply for financing') }}</h1>
                <ol class="cs-crumb"><li><a href="{{ route('owner.financing.surveys') }}">{{ __('Site surveys') }}</a></li><li>›</li><li>{{ optional($product->module)->name }}</li></ol>
            </div>
        </div>

        @if (session('error')) <div class="cs-alert is-danger">{{ session('error') }}</div> @endif
        @if ($errors->any()) <div class="cs-alert is-danger">{{ $errors->first() }}</div> @endif

        <div class="row">
            <div class="col-lg-7">
                <div class="cs-card"><div class="cs-card__body">
                    <p class="cs-muted" style="margin:0 0 16px;">{{ __('Financing your surveyed quotation with :partner. You pay the financed amount back from rent over the term you choose.', ['partner' => optional($product->partner)->trading_name ?? optional($product->partner)->company_name]) }}</p>

                    <form method="POST" action="{{ route('owner.financing.store') }}">
                        @csrf
                        <input type="hidden" name="field_study_request_id" value="{{ $fsr->id }}">
                        <input type="hidden" name="finance_partner_module_id" value="{{ $product->id }}">

                        <div class="cs-field">
                            <label class="cs-label">{{ __('Quoted amount') }}</label>
                            <input type="text" class="cs-input" value="KES {{ number_format((float) $fsr->quoted_amount, 2) }}" disabled>
                            <small class="cs-muted">{{ optional($product->module)->name }} · {{ optional($property)->name }}@if ($fsr->units) · {{ $fsr->units }} {{ __('units') }}@endif</small>
                        </div>

                        <div class="cs-field">
                            <label class="cs-label">{{ __('Down-payment (optional)') }}</label>
                            <input type="number" name="owner_contribution" id="q_down" class="cs-input" min="0" step="0.01" value="0"
                                   oninput="var f=Math.max(0,{{ (float) $fsr->quoted_amount }}-(parseFloat(this.value)||0));document.getElementById('q_fin').textContent='KES '+f.toLocaleString(undefined,{minimumFractionDigits:2,maximumFractionDigits:2});">
                            <small class="cs-muted">{{ __('Pay part now and finance the rest — you only pay interest on the financed portion.') }}</small>
                        </div>

                        <div class="cs-field">
                            <label class="cs-label">{{ __('Repayment term (months)') }}</label>
                            <input type="number" name="repayment_months" class="cs-input" required
                                   min="{{ $product->min_repayment_months }}" max="{{ $product->max_repayment_months }}" value="{{ $product->min_repayment_months }}">
                            <small class="cs-muted">{{ $product->min_repayment_months }}–{{ $product->max_repayment_months }} {{ __('months') }} · {{ number_format($product->interest_rate, 2) }}% {{ str_replace('_', ' ', $product->interest_rate_type) }}</small>
                        </div>

                        <button type="submit" class="cs-btn cs-btn--primary">{{ __('Submit application') }}</button>
                    </form>
                </div></div>
            </div>

            <div class="col-lg-5">
                <div class="cs-card"><div class="cs-card__head"><h2 class="cs-card__title">{{ __('Summary') }}</h2></div>
                <div class="cs-card__body">
                    <div class="d-flex justify-content-between" style="font-size:13px;color:var(--gray-700);padding:4px 0;"><span>{{ __('Quoted amount') }}</span><span>KES {{ number_format((float) $fsr->quoted_amount, 2) }}</span></div>
                    <div class="d-flex justify-content-between" style="font-weight:700;color:var(--gray-900);padding:6px 0;border-top:0.5px solid var(--gray-200);margin-top:4px;"><span>{{ __('To finance') }}</span><span id="q_fin">KES {{ number_format((float) $fsr->quoted_amount, 2) }}</span></div>
                    <p class="cs-muted" style="margin-top:10px;font-size:12px;">{{ __('The exact monthly repayment is confirmed on your application once submitted.') }}</p>
                </div></div>
            </div>
        </div>
    </div>
</div></div></div>
@endsection
