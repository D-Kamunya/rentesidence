@extends('mail.layouts.cs')

@section('subject', $content['subject'])
@section('preheader', $content['title'])

@section('content')
    @include('mail.parts.heading', ['eyebrow' => __('Order confirmed'), 'eyebrowColor' => '#0F6E56', 'title' => $content['title']])
    @include('mail.parts.text', ['html' => $content['message']])

    @include('mail.parts.panel', ['variant' => 'green', 'rows' => [
        ['k' => __('Amount paid'), 'v' => currencyPrice($content['amount']), 'amount' => true],
        ['k' => __('Method'),      'v' => !empty($content['method']) ? Str::ucfirst($content['method']) : ''],
        ['k' => __('Status'),      'v' => !empty($content['status']) ? Str::ucfirst($content['status']) : ''],
    ]])

    @include('mail.parts.button', ['url' => route('tenant.order.index'), 'label' => __('View my orders'), 'color' => '#0F6E56'])
@endsection

@section('footnote', __('You received this because you placed an order on :app.', ['app' => getOption('app_name') ?: 'Centresidence']))
