@extends('layouts.app')
@push('title')
    {{ __('Verify') }} -
@endpush
@section('content')
    <div id="headless-wrapper">
        <section class="sign-up-page bg-white">
            <div class="container-fluid p-0">
                <div class="row sign-up-page-wrap-row">
                    <div class="col-md-6">
                        <div class="sign-up-right-content bg-white">
                            <form method="POST" action="{{ route('user.email.verify.resend', $token) }}">
                                @csrf
                                <div class="mb-25 sign-up-top-logo">
                                    <a href="{{ route('frontend') }}">
                                        <span class="logo-lg">
                                            <img src="{{ getSettingImage('app_logo') }}" alt="">
                                        </span>
                                    </a>
                                </div>
                                <h3 class="mb-2 font-bold">{{ __('Check your email to activate your account') }}</h3>
                                <p class="font-16 mb-25" style="color:#4A5768;">
                                    {{ __('You\'re almost in. We\'ve sent a verification link to') }}
                                    <strong style="color:#111827;">{{ $maskedEmail ?? __('your email') }}</strong>.
                                    {{ __('Open it to activate your account — then sign in and start setting up. It usually arrives within a minute.') }}
                                </p>
                                <div class="row mb-25">
                                    <div class="col-md-12 mb-3">
                                        @if (session('resent'))
                                            <div class="alert alert-success" role="alert">
                                                {{ __('A fresh verification link has been sent to your email address.') }}
                                            </div>
                                        @endif
                                        <p class="font-14 mb-2" style="color:#7A8796;">
                                            {{ __('Can\'t find it? Check your spam folder, or resend the link below.') }}
                                        </p>
                                    </div>
                                    <div class="col-md-12">
                                        <button type="submit"
                                            class="theme-btn theme-button1 theme-button3 font-15 fw-bold w-100"
                                            title="{{ __('Resend the verification link') }}">{{ __('Resend the verification link') }}</button>
                                    </div>
                                    <div class="col-md-12 mt-3 text-center">
                                        <a href="{{ route('login') }}" class="secondary-color font-medium font-14">{{ __('Back to sign in') }}</a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="sign-up-left-content position-relative text-center">
                            <div class="sign-up-bottom-img mb-25">
                                <img src="{{ getSettingImage('sign_in_image') }}" alt="{{ getOption('app_name') }}"
                                    class="img-fluid">
                            </div>
                            <h1 class="text-white">{{ __(getOption('sign_in_text_title')) }}</h1>
                            <p class="mt-25 w-75 mx-auto">{{ __(getOption('sign_in_text_subtitle')) }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
