@extends('mail.layouts.cs')

@section('subject', $content['subject'])
@section('preheader', $content['title'])

@section('content')
    @include('mail.parts.heading', ['eyebrow' => __('Rent invoice'), 'title' => $content['title']])
    @include('mail.parts.text', ['html' => $content['message']])

    @include('mail.parts.panel', ['rows' => [
        ['k' => __('Amount'),   'v' => currencyPrice($content['amount']), 'amount' => true],
        ['k' => __('For'),      'v' => $content['month']],
        ['k' => __('Due date'), 'v' => $content['dueDate']],
        ['k' => __('Invoice'),  'v' => $content['invoiceNo'], 'mono' => true],
    ]])

    @if ($content['status'] != INVOICE_STATUS_PAID)
        @include('mail.parts.button', ['url' => route('instant.invoice.pay', ['token' => $content['token']]), 'label' => __('Pay now')])
        @include('mail.parts.note', ['text' => __('Pay securely via M-Pesa in a few taps — no login needed. A receipt is emailed the moment payment is confirmed.')])
    @else
        @include('mail.parts.button', ['url' => route('instant.invoice.pay', ['token' => $content['token']]), 'label' => __('View paid invoice')])
    @endif
@endsection

@section('footnote', __('You received this because you rent a unit managed on :app.', ['app' => getOption('app_name') ?: 'Centresidence']))
