@extends('finance-partner.layouts.app')

@section('content')
    @php $editing = $product->exists; @endphp
    <div class="cs-titlebar"><h1 class="cs-title">{{ $editing ? __('Edit product') : __('New product') }}</h1></div>

    <form method="POST" action="{{ $editing ? route('finance-partner.products.update', $product->id) : route('finance-partner.products.store') }}">
        @csrf
        @if ($editing) @method('PUT') @endif

        <div class="cs-card"><div class="cs-card__body">
            <div class="row">
                <div class="col-md-6 cs-field">
                    <label class="cs-label">{{ __('Module') }}</label>
                    <select name="module_id" class="cs-select" required>
                        @foreach ($modules as $m)
                            <option value="{{ $m->id }}" @selected($product->module_id == $m->id)>{{ $m->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 cs-field">
                    <label class="cs-label">{{ __('Product name') }}</label>
                    <input name="product_name" class="cs-input" value="{{ old('product_name', $product->product_name) }}" required>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 cs-field">
                    <label class="cs-label">{{ __('Interest rate (% p.a.)') }}</label>
                    <input type="number" step="0.01" name="interest_rate" class="cs-input" value="{{ old('interest_rate', $product->interest_rate) }}" required>
                </div>
                <div class="col-md-4 cs-field">
                    <label class="cs-label">{{ __('Interest type') }}</label>
                    <select name="interest_rate_type" class="cs-select" required>
                        @foreach (['reducing_balance' => 'Reducing balance', 'flat' => 'Flat', 'fixed' => 'Fixed'] as $k => $v)
                            <option value="{{ $k }}" @selected($product->interest_rate_type == $k)>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 cs-field">
                    <label class="cs-label">{{ __('Calculation method') }}</label>
                    <select name="interest_calculation_method" class="cs-select">
                        @foreach (['monthly_rest' => 'Monthly rest', 'daily_rest' => 'Daily rest', 'flat_upfront' => 'Flat upfront'] as $k => $v)
                            <option value="{{ $k }}" @selected($product->interest_calculation_method == $k)>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-md-3 cs-field"><label class="cs-label">{{ __('Min amount') }}</label><input type="number" name="min_amount" class="cs-input" value="{{ old('min_amount', $product->min_amount) }}" required></div>
                <div class="col-md-3 cs-field"><label class="cs-label">{{ __('Max amount') }}</label><input type="number" name="max_amount" class="cs-input" value="{{ old('max_amount', $product->max_amount) }}" required></div>
                <div class="col-md-3 cs-field"><label class="cs-label">{{ __('Min months') }}</label><input type="number" name="min_repayment_months" class="cs-input" value="{{ old('min_repayment_months', $product->min_repayment_months ?? 12) }}" required></div>
                <div class="col-md-3 cs-field"><label class="cs-label">{{ __('Max months') }}</label><input type="number" name="max_repayment_months" class="cs-input" value="{{ old('max_repayment_months', $product->max_repayment_months ?? 36) }}" required></div>
            </div>

            <div class="row">
                <div class="col-md-4 cs-field">
                    <label class="cs-label">{{ __('Repayment frequency') }}</label>
                    <select name="repayment_frequency" class="cs-select" required>
                        @foreach (['monthly','weekly','biweekly','daily'] as $f)
                            <option value="{{ $f }}" @selected($product->repayment_frequency == $f)>{{ ucfirst($f) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 cs-field"><label class="cs-label">{{ __('Max rent deduction %') }}</label><input type="number" step="0.01" name="max_rent_deduction_percentage" class="cs-input" value="{{ old('max_rent_deduction_percentage', $product->max_rent_deduction_percentage ?? 30) }}" required></div>
                <div class="col-md-4 cs-field">
                    <label class="cs-label">{{ __('Status') }}</label>
                    <select name="status" class="cs-select" required>
                        @foreach (['active','inactive','suspended'] as $s)
                            <option value="{{ $s }}" @selected(($product->status ?? 'active') == $s)>{{ ucfirst($s) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div></div>

        <div class="cs-card">
            <div class="cs-card__head"><h2 class="cs-card__title">{{ __('Underwriting & terms') }}</h2></div>
            <div class="cs-card__body">
                <p class="cs-muted" style="margin-bottom:14px;">{{ __('These requirements are auto-applied as eligibility checks against the owner\'s property cashflow.') }}</p>
                <div class="row">
                    <div class="col-md-3 cs-field"><label class="cs-label">{{ __('Min occupancy %') }}</label><input type="number" step="0.01" name="min_occupancy_rate" class="cs-input" value="{{ old('min_occupancy_rate', $product->min_occupancy_rate ?? 0) }}"></div>
                    <div class="col-md-3 cs-field"><label class="cs-label">{{ __('Required cashflow months') }}</label><input type="number" name="required_cashflow_months" class="cs-input" value="{{ old('required_cashflow_months', $product->required_cashflow_months ?? 0) }}"></div>
                    <div class="col-md-3 cs-field"><label class="cs-label">{{ __('Grace period (days)') }}</label><input type="number" name="grace_period_days" class="cs-input" value="{{ old('grace_period_days', $product->grace_period_days ?? 5) }}"></div>
                    <div class="col-md-3 cs-field"><label class="cs-label">{{ __('Default threshold (days)') }}</label><input type="number" name="default_threshold_days" class="cs-input" value="{{ old('default_threshold_days', $product->default_threshold_days ?? 30) }}"></div>
                </div>
                <div class="row">
                    <div class="col-md-3 cs-field"><label class="cs-label">{{ __('Early repayment allowed') }}</label><select name="early_repayment_allowed" class="cs-select"><option value="1" @selected($product->early_repayment_allowed)>{{ __('Yes') }}</option><option value="0" @selected(!$product->early_repayment_allowed)>{{ __('No') }}</option></select></div>
                    <div class="col-md-3 cs-field"><label class="cs-label">{{ __('Early settlement fee %') }}</label><input type="number" step="0.01" name="early_repayment_penalty_percentage" class="cs-input" value="{{ old('early_repayment_penalty_percentage', $product->early_repayment_penalty_percentage ?? 0) }}"></div>
                    <div class="col-md-3 cs-field"><label class="cs-label">{{ __('Daily settlement') }}</label><select name="daily_settlement_enabled" class="cs-select"><option value="0" @selected(!$product->daily_settlement_enabled)>{{ __('No') }}</option><option value="1" @selected($product->daily_settlement_enabled)>{{ __('Yes') }}</option></select></div>
                    <div class="col-md-3 cs-field"><label class="cs-label">{{ __('Monthly settlement day') }}</label><input type="number" min="1" max="28" name="settlement_day" class="cs-input" value="{{ old('settlement_day', $product->settlement_day ?? 1) }}"></div>
                </div>
                <input type="hidden" name="monthly_settlement_enabled" value="1">
            </div>
        </div>

        <button type="submit" class="cs-btn cs-btn--primary">{{ $editing ? __('Save changes') : __('Publish product') }}</button>
        <a href="{{ route('finance-partner.products.index') }}" class="cs-btn cs-btn--ghost">{{ __('Cancel') }}</a>
    </form>
@endsection
