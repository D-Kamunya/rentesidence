@extends('mail.layouts.cs')

@section('subject', $content['subject'] ?? ($content['title'] ?? __('Thank you')))

@section('content')
    @include('mail.parts.heading', ['eyebrow' => __('Thank you'), 'title' => $content['title']])
    @include('mail.parts.text', ['html' => $content['message']])
@endsection
