@extends('mail.layouts.cs')

@section('subject', __('A reminder from :app', ['app' => getOption('app_name') ?: 'Centresidence']))

@section('content')
    @include('mail.parts.heading', ['eyebrow' => __('Reminder'), 'eyebrowColor' => '#B45309', 'title' => __('You have a reminder')])
    @include('mail.parts.text', ['html' => $content['message']])
    @include('mail.parts.button', ['url' => route('login'), 'label' => __('Go to your account')])
@endsection
