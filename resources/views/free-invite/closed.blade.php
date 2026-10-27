@extends('saas.frontend.layouts.app')
@section('content')
@php $pageTitle = $pageTitle ?? __('Invite'); @endphp

{{-- A neutral terminal state for a "Set up on Free" invite link: invalid, expired, or already used. --}}

<style>
  .inv-page{background:#FAF9F6;}
  .inv-wrap{max-width:520px;margin:0 auto;padding:160px 20px 90px;text-align:center;}
  .inv-card{background:#FFFFFF;border:1px solid #E6E1D8;border-radius:18px;padding:40px 30px;
    box-shadow:0 1px 2px rgba(20,23,28,.04),0 10px 30px rgba(20,23,28,.05);}
  .inv-card .mark{width:56px;height:56px;border-radius:16px;display:grid;place-items:center;margin:0 auto 18px;
    background:#E8F0F9;color:#185FA5;font-size:26px;}
  .inv-card h1{font-size:23px;font-weight:750;color:#1B1E22;margin:0 0 10px;}
  .inv-card p{font-size:15px;line-height:1.6;color:#6B7280;margin:0;}
  .inv-login{display:inline-block;margin-top:22px;background:linear-gradient(180deg,#2074c4,#185FA5);border:none;border-radius:11px;
    padding:12px 24px;color:#fff;font-size:14.5px;font-weight:650;text-decoration:none;box-shadow:0 10px 24px -12px rgba(24,95,165,.7);}
</style>

<div class="inv-page">
<div class="inv-wrap">
  <div class="inv-card">
    <div class="mark">✉️</div>
    <h1>{{ $heading }}</h1>
    <p>{{ $message }}</p>
    @if (! empty($showLogin))
      <a href="{{ route('login') }}" class="inv-login">{{ __('Sign in') }}</a>
    @endif
  </div>
</div>
</div>
@endsection
