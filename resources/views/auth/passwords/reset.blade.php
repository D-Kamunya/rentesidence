@extends('layouts.app')
@push('title')
    {{ __('Reset Password') }} -
@endpush
@section('content')
<div class="cs-auth">
    @include('auth.partials._auth-bg')

    <div class="cs-auth__inner">
        @include('auth.partials._auth-brand')

        {{-- Form panel --}}
        <main class="cs-auth__panel">
            <div class="cs-auth__card">
                @include('auth.partials._auth-cardlogo')

                <h2 class="cs-auth__title">{{ __('Set a new password') }}</h2>
                <p class="cs-auth__hint">{{ __('Choose a new password for your account.') }}</p>

                <form method="POST" action="{{ route('password.update') }}" class="cs-auth__form" autocomplete="off">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">

                    <div class="cs-fld">
                        <label class="cs-fld__label">{{ __('Email') }}</label>
                        <div class="cs-fld__wrap">
                            <span class="cs-fld__ic">{!! '<svg width="17" height="17" viewBox="0 0 24 24" fill="none"><path d="M4 6h16v12H4zM4 7l8 6 8-6" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>' !!}</span>
                            <input type="text" name="email" value="{{ $email ?? old('email') }}" class="cs-fld__input email @error('email') is-bad @enderror" placeholder="{{ __('you@company.com') }}">
                        </div>
                        @error('email')<span class="cs-fld__err">{{ $message }}</span>@enderror
                    </div>

                    <div class="cs-fld">
                        <label class="cs-fld__label">{{ __('Password') }}</label>
                        <div class="cs-fld__wrap">
                            <span class="cs-fld__ic">{!! '<svg width="17" height="17" viewBox="0 0 24 24" fill="none"><rect x="5" y="10" width="14" height="10" rx="2" stroke="currentColor" stroke-width="1.6"/><path d="M8 10V7a4 4 0 018 0v3" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>' !!}</span>
                            <input type="password" name="password" class="cs-fld__input password @error('password') is-bad @enderror" placeholder="{{ __('••••••••') }}">
                            <button type="button" class="cs-fld__eye" aria-label="{{ __('Show password') }}">
                                {!! '<svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7-10-7-10-7z" stroke="currentColor" stroke-width="1.6"/><circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.6"/></svg>' !!}
                            </button>
                        </div>
                        @error('password')<span class="cs-fld__err">{{ $message }}</span>@enderror
                    </div>

                    <div class="cs-fld">
                        <label class="cs-fld__label">{{ __('Confirm Password') }}</label>
                        <div class="cs-fld__wrap">
                            <span class="cs-fld__ic">{!! '<svg width="17" height="17" viewBox="0 0 24 24" fill="none"><rect x="5" y="10" width="14" height="10" rx="2" stroke="currentColor" stroke-width="1.6"/><path d="M8 10V7a4 4 0 018 0v3" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>' !!}</span>
                            <input type="password" name="password_confirmation" class="cs-fld__input password" placeholder="{{ __('••••••••') }}">
                            <button type="button" class="cs-fld__eye" aria-label="{{ __('Show password') }}">
                                {!! '<svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7-10-7-10-7z" stroke="currentColor" stroke-width="1.6"/><circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.6"/></svg>' !!}
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="cs-auth__submit" title="{{ __('Reset Password') }}">
                        <span>{{ __('Reset Password') }}</span>
                        {!! '<svg width="17" height="17" viewBox="0 0 24 24" fill="none"><path d="M5 12h14M13 6l6 6-6 6" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"/></svg>' !!}
                    </button>
                </form>

                <a href="{{ route('login') }}" class="cs-auth__back">
                    {!! '<svg width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M19 12H5M11 6l-6 6 6 6" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"/></svg>' !!}
                    {{ __('Back to sign in') }}
                </a>
            </div>
        </main>
    </div>
</div>

@include('auth.partials._auth-styles')
@endsection
@push('script')
@include('auth.partials._auth-scripts')
@endpush
