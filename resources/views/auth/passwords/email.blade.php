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

                <h2 class="cs-auth__title">{{ __('Reset password') }}</h2>
                <p class="cs-auth__hint">{{ __('Enter your email and we\'ll send you a link to reset your password.') }}</p>

                @if (session('status'))
                    <div class="cs-auth__note">
                        {!! '<svg width="17" height="17" viewBox="0 0 24 24" fill="none"><path d="M20 6L9 17l-5-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>' !!}
                        <span>{{ session('status') }}</span>
                    </div>
                @endif

                <form method="POST" action="{{ route('password.email') }}" class="cs-auth__form" autocomplete="off">
                    @csrf

                    <div class="cs-fld">
                        <label class="cs-fld__label">{{ __('Email') }}</label>
                        <div class="cs-fld__wrap">
                            <span class="cs-fld__ic">{!! '<svg width="17" height="17" viewBox="0 0 24 24" fill="none"><path d="M4 6h16v12H4zM4 7l8 6 8-6" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>' !!}</span>
                            <input type="text" name="email" value="{{ old('email') }}" class="cs-fld__input email @error('email') is-bad @enderror" placeholder="{{ __('you@company.com') }}" autofocus>
                        </div>
                        @error('email')<span class="cs-fld__err">{{ $message }}</span>@enderror
                    </div>

                    <button type="submit" class="cs-auth__submit" title="{{ __('Send Password Reset Link') }}">
                        <span>{{ __('Send Password Reset Link') }}</span>
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
