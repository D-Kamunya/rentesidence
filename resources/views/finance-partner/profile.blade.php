@extends('finance-partner.layouts.app')

@section('content')
    <div class="cs-titlebar"><h1 class="cs-title">{{ __('My profile') }}</h1></div>

    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:16px;align-items:start;">

        {{-- Account details --}}
        <div class="cs-card">
            <div class="cs-card__head"><h2 class="cs-card__title">{{ __('Account details') }}</h2></div>
            <form method="POST" action="{{ route('finance-partner.profile.update') }}" class="cs-card__body">
                @csrf
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                    <div class="cs-field">
                        <label class="cs-label">{{ __('First name') }}</label>
                        <input name="first_name" class="cs-input" value="{{ old('first_name', $user->first_name) }}" required>
                    </div>
                    <div class="cs-field">
                        <label class="cs-label">{{ __('Last name') }}</label>
                        <input name="last_name" class="cs-input" value="{{ old('last_name', $user->last_name) }}" required>
                    </div>
                </div>
                <div class="cs-field">
                    <label class="cs-label">{{ __('Email') }}</label>
                    <input type="email" name="email" class="cs-input" value="{{ old('email', $user->email) }}" required>
                </div>
                <div class="cs-field">
                    <label class="cs-label">{{ __('Contact number') }}</label>
                    <input name="contact_number" class="cs-input" value="{{ old('contact_number', $user->contact_number) }}" placeholder="{{ __('e.g. 254700000000') }}">
                </div>

                <div style="border-top:0.5px solid var(--gray-200);margin:8px 0 4px;padding-top:14px;">
                    <div class="cs-muted" style="font-size:12px;margin-bottom:10px;">{{ __('Company (shown to owners and on remittances)') }}</div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                        <div class="cs-field">
                            <label class="cs-label">{{ __('Company name') }}</label>
                            <input name="company_name" class="cs-input" value="{{ old('company_name', $partner->company_name) }}">
                        </div>
                        <div class="cs-field">
                            <label class="cs-label">{{ __('Trading name') }}</label>
                            <input name="trading_name" class="cs-input" value="{{ old('trading_name', $partner->trading_name) }}">
                        </div>
                    </div>
                </div>

                <button type="submit" class="cs-btn cs-btn--primary" style="margin-top:8px;">{{ __('Save changes') }}</button>
            </form>
        </div>

        {{-- Change password --}}
        <div class="cs-card">
            <div class="cs-card__head"><h2 class="cs-card__title">{{ __('Change password') }}</h2></div>
            <form method="POST" action="{{ route('finance-partner.profile.password') }}" class="cs-card__body">
                @csrf
                <div class="cs-field">
                    <label class="cs-label">{{ __('Current password') }}</label>
                    <input type="password" name="current_password" class="cs-input" required>
                </div>
                <div class="cs-field">
                    <label class="cs-label">{{ __('New password') }}</label>
                    <input type="password" name="password" class="cs-input" required minlength="6">
                </div>
                <div class="cs-field">
                    <label class="cs-label">{{ __('Confirm new password') }}</label>
                    <input type="password" name="password_confirmation" class="cs-input" required minlength="6">
                </div>
                <button type="submit" class="cs-btn cs-btn--primary" style="margin-top:8px;">{{ __('Update password') }}</button>
            </form>
        </div>
    </div>
@endsection
