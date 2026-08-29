@extends('mail.layouts.cs')

@section('subject', $content['subject'] ?? __('Payment received'))
@section('preheader', $content['title'] ?? __('Payment received'))

@section('content')
    @include('mail.parts.heading', ['eyebrow' => __('Payment received'), 'eyebrowColor' => '#0F6E56', 'title' => $content['title']])
    @include('mail.parts.text', ['html' => $content['message']])

    @include('mail.parts.panel', ['variant' => 'green', 'rows' => [
        ['k' => __('Amount paid'), 'v' => currencyPrice($content['amount']), 'amount' => true],
        ['k' => __('For'),         'v' => $content['month'] ?? ''],
        ['k' => __('Invoice'),     'v' => $content['invoiceNo'] ?? '', 'mono' => true],
        ['k' => __('Method'),      'v' => !empty($content['method']) ? Str::ucfirst($content['method']) : ''],
        ['k' => __('M-Pesa code'), 'v' => $content['code'] ?? '', 'mono' => true],
        ['k' => __('Status'),      'v' => !empty($content['status']) ? Str::ucfirst($content['status']) : ''],
    ]])

    @include('mail.parts.button', ['url' => route('login'), 'label' => __('View my invoices'), 'color' => '#0F6E56'])
@endsection

@section('footnote', __('This receipt confirms a payment on your :app account. Keep it for your records.', ['app' => getOption('app_name') ?: 'Centresidence']))
