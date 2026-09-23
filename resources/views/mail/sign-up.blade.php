@extends('mail.layouts.cs')

@section('subject', __('Your :app login details', ['app' => getOption('app_name') ?: 'Centresidence']))
@section('preheader', __('Your account is ready — sign in with the details inside.'))

@section('content')
    @include('mail.parts.heading', ['eyebrow' => __('Welcome'), 'title' => __('Your login details are ready')])
    @include('mail.parts.text', ['html' => $content['message']])

    @include('mail.parts.panel', ['rows' => [
        ['k' => __('Email'),              'v' => $content['email']],
        ['k' => __('Temporary password'), 'v' => $content['password'], 'mono' => true],
    ]])

    @include('mail.parts.button', ['url' => route('login'), 'label' => __('Sign in to your account')])
    @include('mail.parts.note', ['text' => __('<b>For your security</b>, you\'ll be asked to set your own password the first time you sign in.')])
@endsection

@section('footnote', __('You received this because an account was created for you on :app.', ['app' => getOption('app_name') ?: 'Centresidence']))
