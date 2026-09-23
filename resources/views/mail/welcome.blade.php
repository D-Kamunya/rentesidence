@extends('mail.layouts.cs')

@php $appName = getOption('app_name') ?: 'Centresidence'; @endphp
@section('subject', __('Welcome to :app', ['app' => $appName]))
@section('preheader', __('Welcome to :app', ['app' => $appName]))

@section('content')
    @include('mail.parts.heading', ['eyebrow' => __('Welcome'), 'title' => __('Welcome to :app', ['app' => $appName])])
    @include('mail.parts.text', ['html' => $content['message']])
    @include('mail.parts.button', ['url' => route('login'), 'label' => __('Go to your account')])
@endsection
