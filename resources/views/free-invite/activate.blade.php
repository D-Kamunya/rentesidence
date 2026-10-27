@extends('saas.frontend.layouts.app')
@section('content')
@php $pageTitle = $pageTitle ?? __('Activate your free account'); @endphp

{{--
    "Set up on Free" activation landing — the page an invited owner opens from the
    /free/activate/{token} link. On the light CS frontend language (matches the home page /
    invite-a-landlord). Submitting the set-password form CREATES the free owner account on the
    owner's own consent (the click + password); no account exists until then.
--}}

<style>
  .inv-page{background:#FAF9F6;}
  .inv-wrap{max-width:560px;margin:0 auto;padding:148px 20px 80px;}
  .inv-hero{text-align:center;max-width:520px;margin:0 auto 28px;}
  .inv-eyebrow{display:inline-block;font-size:12px;font-weight:700;letter-spacing:.14em;text-transform:uppercase;
    color:#185FA5;background:#E8F0F9;border:1px solid #CBDDF1;padding:6px 14px;border-radius:999px;margin-bottom:18px;}
  .inv-hero h1{font-size:clamp(26px,5vw,38px);line-height:1.14;font-weight:750;margin:0 0 14px;color:#1B1E22;letter-spacing:-.01em;}
  .inv-hero h1 .amp{color:#E7A339;}
  .inv-hero p{font-size:16px;line-height:1.6;color:#6B7280;margin:0;}
  .inv-card{background:#FFFFFF;border:1px solid #E6E1D8;border-radius:18px;padding:26px 26px 30px;
    box-shadow:0 1px 2px rgba(20,23,28,.04),0 10px 30px rgba(20,23,28,.05);}
  .inv-acctline{display:flex;align-items:center;gap:10px;background:#F6F8FB;border:1px solid #E3EBF4;border-radius:12px;
    padding:12px 14px;margin-bottom:20px;font-size:13.5px;color:#3A3F47;}
  .inv-acctline .ic{flex:none;width:32px;height:32px;border-radius:9px;display:grid;place-items:center;background:#E8F0F9;color:#185FA5;font-size:16px;}
  .inv-acctline b{color:#1B1E22;}
  .inv-field{margin-bottom:15px;}
  .inv-field label{display:block;font-size:12.5px;font-weight:600;color:#3A3F47;margin-bottom:6px;}
  .inv-field label .req{color:#E7A339;}
  .inv-field input{width:100%;background:#FFFFFF;border:1px solid #E6E1D8;border-radius:10px;
    padding:11px 13px;color:#1B1E22;font-size:14.5px;outline:none;transition:border-color .15s,box-shadow .15s;}
  .inv-field input:focus{border-color:#185FA5;box-shadow:0 0 0 3px rgba(24,95,165,.15);}
  .inv-submit{width:100%;margin-top:8px;background:linear-gradient(180deg,#2074c4,#185FA5);border:none;border-radius:11px;
    padding:14px;color:#fff;font-size:15.5px;font-weight:650;cursor:pointer;transition:filter .15s;box-shadow:0 10px 24px -12px rgba(24,95,165,.7);}
  .inv-submit:hover{filter:brightness(1.06);}
  .inv-fineprint{margin-top:14px;font-size:12px;color:#9AA0A8;line-height:1.5;text-align:center;}
  .inv-err{background:#FBE9E6;border:1px solid #F0B8B0;color:#B42318;border-radius:10px;padding:11px 14px;font-size:13.5px;margin-bottom:16px;}
  .inv-err ul{margin:6px 0 0;padding-left:18px;}
</style>

<div class="inv-page">
<div class="inv-wrap">
  <div class="inv-hero">
    <span class="inv-eyebrow">{{ __('Your free account') }}</span>
    <h1>{{ __('Activate your free account on') }} <span class="amp">{{ $appName }}</span></h1>
    <p>{{ __('Choose a password below and your account is live — free, with nothing to pay and no card required.') }}</p>
  </div>

  <div class="inv-card">
    @if (session('error'))
      <div class="inv-err">{{ session('error') }}</div>
    @endif
    @if ($errors->any())
      <div class="inv-err">
        {{ __('Please check the form:') }}
        <ul>@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
      </div>
    @endif

    @if ($companyName || $email)
      <div class="inv-acctline">
        <span class="ic">🏢</span>
        <div>
          @if ($companyName)<b>{{ $companyName }}</b>@endif
          @if ($email)<div style="color:#6B7280;">{{ $email }}</div>@endif
        </div>
      </div>
    @endif

    <form method="POST" action="{{ route('free.invite.accept', $token) }}">
      @csrf
      <div class="inv-field">
        <label>{{ __('Choose a password') }} <span class="req">*</span></label>
        <input type="password" name="password" required minlength="8" autocomplete="new-password" placeholder="{{ __('At least 8 characters') }}">
      </div>
      <div class="inv-field">
        <label>{{ __('Confirm password') }} <span class="req">*</span></label>
        <input type="password" name="password_confirmation" required minlength="8" autocomplete="new-password" placeholder="{{ __('Re-enter your password') }}">
      </div>
      <button type="submit" class="inv-submit">{{ __('Activate my free account') }}</button>
      <p class="inv-fineprint">{{ __('By activating you agree to the terms of service. Your plan is free — you only pay for extras you choose to switch on.') }}</p>
    </form>
  </div>
</div>
</div>
@endsection
