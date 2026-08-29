@extends('mail.layouts.cs')

@section('subject', $content['subject'] ?? (getOption('app_name') ?: 'Centresidence'))

@section('content')
    @include('mail.parts.text', ['html' => $content['message']])
@endsection
