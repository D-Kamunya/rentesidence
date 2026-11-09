@extends('layouts.app')
@push('title')
    {{ __('Verify your email') }} -
@endpush
@section('content')
@php
    $brandHeadline = __('One tap away from getting started.');
    $brandSub      = __('Confirm your email to activate your free Centresidence account.');
@endphp
<div class="cs-auth">
    @include('auth.partials._auth-bg')

    <div class="cs-auth__inner">
        @include('auth.partials._auth-brand')

        <main class="cs-auth__panel">
            <div class="cs-auth__card">
                @include('auth.partials._auth-cardlogo')

                <h2 class="cs-auth__title">{{ __('Check your email to activate your account') }}</h2>
                <p class="cs-auth__hint">
                    {{ __('You\'re almost in. We\'ve sent a verification link to') }}
                    <strong style="color:#dbeafe;">{{ $maskedEmail ?? __('your email') }}</strong>.
                    {{ __('Open it to activate your account, then sign in and start setting up. It usually arrives within a minute.') }}
                </p>

                @if (session('resent'))
                    <div style="background:rgba(29,158,117,.15);border:1px solid rgba(29,158,117,.35);color:#86efac;border-radius:10px;padding:11px 14px;font-size:13.5px;margin-bottom:16px;">
                        {{ __('A fresh verification link has been sent to your email address.') }}
                    </div>
                @endif

                <p style="font-size:13px;color:var(--muted);margin-bottom:16px;">
                    {{ __('Can\'t find it? Check your spam folder, or resend the link below.') }}
                </p>

                <form method="POST" action="{{ route('user.email.verify.resend', $token) }}" class="cs-auth__form">
                    @csrf
                    <button type="submit" class="cs-auth__submit">{{ __('Resend the verification link') }}</button>
                </form>

                <p class="cs-auth__foot" style="margin-top:18px;text-align:center;font-size:13.5px;color:var(--muted);">
                    <a href="{{ route('login') }}" class="cs-auth__link">{{ __('Back to sign in') }}</a>
                </p>
            </div>
        </main>
    </div>
</div>

@include('auth.partials._auth-styles')
@endsection

@push('script')
@include('auth.partials._auth-scripts')
@endpush
