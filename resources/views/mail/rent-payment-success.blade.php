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

    @include('mail.parts.button', ['url' => $content['receiptUrl'] ?? route('login'), 'label' => __('View my receipt'), 'color' => '#0F6E56'])

    <p style="font-size:13px;color:#6b7280;margin:14px 0 0;">{{ __('Your receipt is also attached to this email as a PDF for your records.') }}</p>
@endsection

@section('footnote', __('This receipt confirms a payment on your :app account. Keep it for your records.', ['app' => getOption('app_name') ?: 'Centresidence']))
