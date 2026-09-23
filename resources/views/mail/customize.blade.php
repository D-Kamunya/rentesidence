@extends('mail.layouts.cs')

@section('subject', $content['subject'] ?? (getOption('app_name') ?: 'Centresidence'))

@section('content')
    {{-- Owner-customized template content, framed by the shared brand shell. --}}
    <div style="font-family:-apple-system,'Segoe UI',Roboto,Helvetica,Arial,sans-serif; font-size:14.5px; line-height:1.65; color:#48566A;">{!! $content['content'] !!}</div>
@endsection
