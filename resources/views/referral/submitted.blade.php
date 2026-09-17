@extends('saas.frontend.layouts.app')
@section('content')
@php $pageTitle = $pageTitle ?? 'Thank you'; @endphp

{{-- Post-submit thank-you for the invite-a-landlord intake. No account is created here —
     the request enters the vetting pipeline and the team reaches out. --}}

<style>
  /* Light design language — matches the marketing site. */
  .invok-page{background:#FAF9F6;}
  .invok-wrap{max-width:640px;margin:0 auto;padding:90px 20px 110px;text-align:center;}
  .invok-badge{width:72px;height:72px;border-radius:20px;margin:0 auto 24px;display:grid;place-items:center;
    background:#E3F3EC;border:1px solid #9AD9C4;font-size:34px;}
  .invok-wrap h1{font-size:clamp(26px,5vw,36px);font-weight:750;color:#1B1E22;margin:0 0 14px;}
  .invok-wrap p{font-size:16.5px;line-height:1.65;color:#6B7280;margin:0 auto 26px;max-width:52ch;}
  .invok-actions{display:flex;gap:12px;justify-content:center;flex-wrap:wrap;}
  .invok-btn{padding:12px 22px;border-radius:11px;font-size:14.5px;font-weight:650;text-decoration:none;}
  .invok-btn--p{background:linear-gradient(180deg,#2074c4,#185FA5);color:#fff;box-shadow:0 10px 24px -12px rgba(24,95,165,.7);}
  .invok-btn--g{background:#FFFFFF;border:1px solid #E6E1D8;color:#3A3F47;}
</style>

<div class="invok-page">
<div class="invok-wrap">
  <div class="invok-badge">✅</div>
  <h1>{{ __('Request received') }}</h1>
  <p>{{ __('Thanks — we\'ve got your details. Our team reviews every request and will reach out shortly to set up your Centresidence account and walk you through collecting rent. No further action needed for now.') }}</p>
  <div class="invok-actions">
    <a href="{{ route('frontend') }}" class="invok-btn invok-btn--p">{{ __('Explore Centresidence') }}</a>
    <a href="{{ route('house.hunt') }}" class="invok-btn invok-btn--g">{{ __('Browse listings') }}</a>
  </div>
</div>
</div>
@endsection
