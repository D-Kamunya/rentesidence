@extends('layouts.app')
@push('title')
    {{ __('Create your free account') }} -
@endpush
@section('content')
@php
    // Register-specific brand copy (the shared panel falls back to the global sign-in copy otherwise).
    $brandHeadline = __('Get paid on time. Finance the essentials.');
    $brandSub      = __('Create your free Centresidence account — free for your first 30 units, no card needed.');
@endphp
<div class="cs-auth">
    @include('auth.partials._auth-bg')

    <div class="cs-auth__inner">
        @include('auth.partials._auth-brand')

        <main class="cs-auth__panel">
            <div class="cs-auth__card">
                @include('auth.partials._auth-cardlogo')

                <h2 class="cs-auth__title">{{ __('Create your free account') }}</h2>
                <p class="cs-auth__hint">{{ __('Free for your first 30 units — no card needed.') }}</p>

                <form action="{{ route('owner.register.store') }}" method="post" class="cs-auth__form" autocomplete="off">
                    @csrf
                    {{-- Honeypot: hidden from humans, irresistible to bots. Must stay empty. --}}
                    <input type="text" name="website" tabindex="-1" autocomplete="off" aria-hidden="true"
                        style="position:absolute;left:-9999px;top:-9999px;height:0;width:0;opacity:0;" value="">

                    <div class="cs-fld">
                        <label class="cs-fld__label">{{ __('First name') }}</label>
                        <div class="cs-fld__wrap">
                            <span class="cs-fld__ic">{!! '<svg width="17" height="17" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="8" r="4" stroke="currentColor" stroke-width="1.6"/><path d="M4 20c0-3.3 3.6-5 8-5s8 1.7 8 5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>' !!}</span>
                            <input type="text" name="first_name" value="{{ old('first_name') }}" class="cs-fld__input @error('first_name') is-bad @enderror" placeholder="{{ __('First name') }}">
                        </div>
                        @error('first_name')<span class="cs-fld__err">{{ $message }}</span>@enderror
                    </div>

                    <div class="cs-fld">
                        <label class="cs-fld__label">{{ __('Last name') }}</label>
                        <div class="cs-fld__wrap">
                            <span class="cs-fld__ic">{!! '<svg width="17" height="17" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="8" r="4" stroke="currentColor" stroke-width="1.6"/><path d="M4 20c0-3.3 3.6-5 8-5s8 1.7 8 5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>' !!}</span>
                            <input type="text" name="last_name" value="{{ old('last_name') }}" class="cs-fld__input @error('last_name') is-bad @enderror" placeholder="{{ __('Last name') }}">
                        </div>
                        @error('last_name')<span class="cs-fld__err">{{ $message }}</span>@enderror
                    </div>

                    <div class="cs-fld">
                        <label class="cs-fld__label">{{ __('Phone number') }}</label>
                        <div class="cs-fld__wrap">
                            <span class="cs-fld__ic">{!! '<svg width="17" height="17" viewBox="0 0 24 24" fill="none"><path d="M6 3h4l2 5-3 2a12 12 0 006 6l2-3 5 2v4a2 2 0 01-2 2A18 18 0 014 7a2 2 0 012-4z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/></svg>' !!}</span>
                            <input type="text" name="contact_number" value="{{ old('contact_number') }}" class="cs-fld__input @error('contact_number') is-bad @enderror" placeholder="{{ __('07XX XXX XXX') }}">
                        </div>
                        @error('contact_number')<span class="cs-fld__err">{{ $message }}</span>@enderror
                    </div>

                    <div class="cs-fld">
                        <label class="cs-fld__label">{{ __('Email') }}</label>
                        <div class="cs-fld__wrap">
                            <span class="cs-fld__ic">{!! '<svg width="17" height="17" viewBox="0 0 24 24" fill="none"><path d="M4 6h16v12H4zM4 7l8 6 8-6" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>' !!}</span>
                            <input type="text" name="email" value="{{ old('email') }}" class="cs-fld__input email @error('email') is-bad @enderror" placeholder="{{ __('you@company.com') }}">
                        </div>
                        @error('email')<span class="cs-fld__err">{{ $message }}</span>@enderror
                    </div>

                    <div class="cs-fld">
                        <label class="cs-fld__label">{{ __('Password') }}</label>
                        <div class="cs-fld__wrap">
                            <span class="cs-fld__ic">{!! '<svg width="17" height="17" viewBox="0 0 24 24" fill="none"><rect x="5" y="10" width="14" height="10" rx="2" stroke="currentColor" stroke-width="1.6"/><path d="M8 10V7a4 4 0 018 0v3" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>' !!}</span>
                            <input type="password" name="password" class="cs-fld__input password @error('password') is-bad @enderror" placeholder="{{ __('At least 8 characters') }}">
                            <button type="button" class="cs-fld__eye" aria-label="{{ __('Show password') }}">{!! '<svg width="17" height="17" viewBox="0 0 24 24" fill="none"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z" stroke="currentColor" stroke-width="1.6"/><circle cx="12" cy="12" r="2.6" stroke="currentColor" stroke-width="1.6"/></svg>' !!}</button>
                        </div>
                        @error('password')<span class="cs-fld__err">{{ $message }}</span>@enderror
                    </div>

                    <div class="cs-fld">
                        <label class="cs-fld__label">{{ __('Confirm password') }}</label>
                        <div class="cs-fld__wrap">
                            <span class="cs-fld__ic">{!! '<svg width="17" height="17" viewBox="0 0 24 24" fill="none"><rect x="5" y="10" width="14" height="10" rx="2" stroke="currentColor" stroke-width="1.6"/><path d="M8 10V7a4 4 0 018 0v3" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>' !!}</span>
                            <input type="password" name="password_confirmation" class="cs-fld__input" placeholder="{{ __('Re-enter your password') }}">
                            <button type="button" class="cs-fld__eye" aria-label="{{ __('Show password') }}">{!! '<svg width="17" height="17" viewBox="0 0 24 24" fill="none"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z" stroke="currentColor" stroke-width="1.6"/><circle cx="12" cy="12" r="2.6" stroke="currentColor" stroke-width="1.6"/></svg>' !!}</button>
                        </div>
                    </div>

                    @if (getOption('GOOGLE_RECAPTCHA_MAIL_STATUS', 0) == ACTIVE)
                        <div class="cs-fld">
                            <div class="g-recaptcha" data-sitekey="{{ getOption('GOOGLE_RECAPTCHA_KEY') }}"></div>
                            @if ($errors->has('g-recaptcha-response'))
                                <span class="cs-fld__err">{{ $errors->first('g-recaptcha-response') }}</span>
                            @endif
                        </div>
                    @endif

                    <button type="submit" class="cs-auth__submit">
                        {{ __('Start free') }}
                        {!! '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" style="margin-left:7px"><path d="M5 12h14M13 6l6 6-6 6" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"/></svg>' !!}
                    </button>
                </form>

                <p class="cs-auth__foot" style="margin-top:18px;text-align:center;font-size:13.5px;color:var(--muted);">
                    {{ __('Already have an account?') }} <a href="{{ route('login') }}" class="cs-auth__link">{{ __('Sign in') }}</a>
                </p>
            </div>
        </main>
    </div>
</div>

@include('auth.partials._auth-styles')
@endsection

@push('script')
@if (getOption('GOOGLE_RECAPTCHA_MAIL_STATUS', 0) == ACTIVE)
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
@endif
@include('auth.partials._auth-scripts')
@endpush
