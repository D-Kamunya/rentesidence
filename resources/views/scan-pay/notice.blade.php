@extends('saas.frontend.layouts.app')
@section('content')
@php $pageTitle = $pageTitle ?? __('Rent payment'); @endphp

{{-- A neutral public notice for a scanned pay code: tenancy inactive, or nothing currently due. --}}

<style>
  .sp-page{background:#FAF9F6;}
  .sp-wrap{max-width:520px;margin:0 auto;padding:160px 20px 90px;text-align:center;}
  .sp-card{background:#FFFFFF;border:1px solid #E6E1D8;border-radius:18px;padding:40px 30px;
    box-shadow:0 1px 2px rgba(20,23,28,.04),0 10px 30px rgba(20,23,28,.05);}
  .sp-card .mark{width:56px;height:56px;border-radius:16px;display:grid;place-items:center;margin:0 auto 18px;
    background:#E8F0F9;color:#185FA5;font-size:26px;}
  .sp-card h1{font-size:22px;font-weight:750;color:#1B1E22;margin:0 0 10px;}
  .sp-card p{font-size:15px;line-height:1.6;color:#6B7280;margin:0;}
</style>

<div class="sp-page">
<div class="sp-wrap">
  <div class="sp-card">
    <div class="mark">💳</div>
    <h1>{{ $heading }}</h1>
    <p>{{ $message }}</p>
  </div>
</div>
</div>
@endsection
