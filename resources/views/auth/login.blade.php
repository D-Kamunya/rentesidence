@extends('layouts.app')
@push('title')
    {{ __('Login') }} -
@endpush
@section('content')
@php
    $appLogo = getSettingImage('app_logo');
    $hasLogo = $appLogo && !\Illuminate\Support\Str::contains($appLogo, 'empty-user');
@endphp
<div class="cs-auth">
    @include('auth.partials._auth-bg')

    <div class="cs-auth__inner">
        @include('auth.partials._auth-brand')

        {{-- Form panel --}}
        <main class="cs-auth__panel">
            <div class="cs-auth__card">
                @include('auth.partials._auth-cardlogo')

                <h2 class="cs-auth__title">{{ __('Welcome back') }}</h2>
                <p class="cs-auth__hint">{{ __('Sign in to your account to continue.') }}</p>

                <form action="{{ route('login') }}" method="post" class="cs-auth__form" autocomplete="off">
                    @csrf

                    <div class="cs-fld">
                        <label class="cs-fld__label">{{ __('Email') }}</label>
                        <div class="cs-fld__wrap">
                            <span class="cs-fld__ic">{!! '<svg width="17" height="17" viewBox="0 0 24 24" fill="none"><path d="M4 6h16v12H4zM4 7l8 6 8-6" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>' !!}</span>
                            <input type="text" name="email" value="{{ old('email') }}" class="cs-fld__input email @error('email') is-bad @enderror" placeholder="{{ __('you@company.com') }}" autofocus>
                        </div>
                        @error('email')<span class="cs-fld__err">{{ $message }}</span>@enderror
                    </div>

                    <div class="cs-fld">
                        <label class="cs-fld__label">{{ __('Password') }}</label>
                        <div class="cs-fld__wrap">
                            <span class="cs-fld__ic">{!! '<svg width="17" height="17" viewBox="0 0 24 24" fill="none"><rect x="5" y="10" width="14" height="10" rx="2" stroke="currentColor" stroke-width="1.6"/><path d="M8 10V7a4 4 0 018 0v3" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>' !!}</span>
                            <input type="password" name="password" class="cs-fld__input password @error('password') is-bad @enderror" placeholder="{{ __('••••••••') }}">
                            <button type="button" class="cs-fld__eye" id="csPwToggle" aria-label="{{ __('Show password') }}">
                                {!! '<svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7-10-7-10-7z" stroke="currentColor" stroke-width="1.6"/><circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.6"/></svg>' !!}
                            </button>
                        </div>
                        @error('password')<span class="cs-fld__err">{{ $message }}</span>@enderror
                    </div>

                    @if (getOption('GOOGLE_RECAPTCHA_MAIL_STATUS', 0) == ACTIVE)
                        <div class="cs-fld">
                            <div class="g-recaptcha" data-sitekey="{{ getOption('GOOGLE_RECAPTCHA_KEY') }}"></div>
                            @if ($errors->has('g-recaptcha-response'))
                                <span class="cs-fld__err">{{ $errors->first('g-recaptcha-response') }}</span>
                            @endif
                        </div>
                    @endif

                    <div class="cs-auth__row">
                        <label class="cs-check">
                            <input type="checkbox" id="rememberMe" name="remember" value="1">
                            <span>{{ __('Remember me') }}</span>
                        </label>
                        <a href="{{ route('password.request') }}" class="cs-auth__link">{{ __('Forgot password?') }}</a>
                    </div>

                    <button type="submit" class="cs-auth__submit">
                        <span>{{ __('Sign in') }}</span>
                        {!! '<svg width="17" height="17" viewBox="0 0 24 24" fill="none"><path d="M5 12h14M13 6l6 6-6 6" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"/></svg>' !!}
                    </button>
                </form>

                @if (env('LOGIN_HELP') == 'active')
                    <div class="cs-auth__demo">
                        <span class="cs-auth__demolabel">{{ __('Demo accounts — click to fill') }}</span>
                        <div class="cs-auth__demogrid">
                            <button type="button" id="adminCredentialShow" class="cs-auth__demobtn">Admin</button>
                            <button type="button" id="ownerCredentialShow" class="cs-auth__demobtn">Owner</button>
                            <button type="button" id="tenantCredentialShow" class="cs-auth__demobtn">Tenant</button>
                            <button type="button" id="maintainerCredentialShow" class="cs-auth__demobtn">Maintainer</button>
                        </div>
                    </div>
                @endif
            </div>
        </main>
    </div>
</div>

@include('auth.partials._auth-styles')
@endsection
@push('script')
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
@include('auth.partials._auth-scripts')
@endpush
