@extends('mail.layouts.cs')

@php $appName = getOption('app_name') ?: 'Centresidence'; @endphp
@section('subject', __('Welcome to :app', ['app' => $appName]))
@section('preheader', __('Welcome to :app', ['app' => $appName]))

@section('content')
    @include('mail.parts.heading', ['eyebrow' => __('Welcome'), 'title' => __('Welcome to :app', ['app' => $appName])])
    @include('mail.parts.text', ['html' => __('Your account is ready — welcome aboard. Everything\'s set up for you to sign in and get started.')])
    @include('mail.parts.button', ['url' => route('login'), 'label' => __('Go to your account')])
    @include('mail.parts.note', ['text' => __('Need a hand getting going? Our team is here to help — reach us anytime from the Support section once you\'re signed in.')])
@endsection
