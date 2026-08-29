@extends('mail.layouts.cs')

@section('subject', $content['subject'])
@section('preheader', $content['title'])

@section('content')
    @include('mail.parts.heading', ['eyebrow' => __('Agreement'), 'title' => $content['title']])
    @include('mail.parts.text', ['html' => $content['message']])

    @include('mail.parts.panel', ['rows' => [
        ['k' => __('Agreement'), 'v' => $content['agreementTitle'] ?? ''],
        ['k' => __('From'),      'v' => $content['ownerName'] ?? ''],
    ]])

    @include('mail.parts.button', ['url' => $content['url'], 'label' => __('Review & sign')])
    @include('mail.parts.note', ['text' => __('You\'ll confirm a one-time code sent to your phone before signing.')])
@endsection

@section('footnote', __('You received this because your landlord sent you an agreement on :app.', ['app' => getOption('app_name') ?: 'Centresidence']))
