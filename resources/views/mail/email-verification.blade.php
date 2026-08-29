@extends('mail.layouts.cs')

@section('subject', __('Verify your email address'))
@section('preheader', __('Confirm your email to activate your account.'))

@section('content')
    @include('mail.parts.heading', ['eyebrow' => __('Account verification'), 'title' => __('Verify your email address')])
    @include('mail.parts.text', ['html' => $content['message']])

    @include('mail.parts.button', ['url' => route('user.email.verified', $content['user']->verify_token), 'label' => __('Verify my account')])

    @include('mail.parts.panel', ['rows' => [
        ['k' => __('Your OTP'), 'v' => $content['user']->otp, 'mono' => true],
    ]])

    @include('mail.parts.note', ['text' => __('If you didn\'t create this account, you can safely ignore this email.')])
@endsection
