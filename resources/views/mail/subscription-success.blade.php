@extends('mail.layouts.cs')

@section('subject', $content['subject'])
@section('preheader', $content['title'])

@section('content')
    @include('mail.parts.heading', ['eyebrow' => __('Subscription'), 'eyebrowColor' => '#0F6E56', 'title' => $content['title']])
    @include('mail.parts.text', ['html' => $content['message']])

    @include('mail.parts.panel', ['variant' => 'green', 'rows' => [
        ['k' => __('Amount'),   'v' => currencyPrice($content['amount']), 'amount' => true],
        ['k' => __('Duration'), 'v' => $content['duration']],
        ['k' => __('Method'),   'v' => !empty($content['method']) ? Str::ucfirst($content['method']) : ''],
        ['k' => __('Status'),   'v' => !empty($content['status']) ? Str::ucfirst($content['status']) : ''],
    ]])

    @include('mail.parts.button', ['url' => route('owner.subscription.index'), 'label' => __('View subscription'), 'color' => '#0F6E56'])
@endsection

@section('footnote', __('You received this because you subscribe to :app.', ['app' => getOption('app_name') ?: 'Centresidence']))
