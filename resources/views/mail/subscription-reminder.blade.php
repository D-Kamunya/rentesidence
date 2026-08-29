@extends('mail.layouts.cs')

@section('subject', $content['subject'])
@section('preheader', $content['title'])

@section('content')
    @include('mail.parts.heading', ['eyebrow' => __('Subscription'), 'eyebrowColor' => '#B45309', 'title' => $content['title']])
    @include('mail.parts.text', ['html' => $content['message']])
    @include('mail.parts.button', ['url' => route('owner.subscription.index', ['current_plan' => 'no']), 'label' => __('Renew subscription')])
@endsection

@section('footnote', __('You received this because you subscribe to :app.', ['app' => getOption('app_name') ?: 'Centresidence']))
