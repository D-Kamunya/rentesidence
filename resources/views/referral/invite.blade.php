@extends('saas.frontend.layouts.app')
@section('content')
@php $pageTitle = $pageTitle ?? 'You\'re invited to Centresidence'; @endphp

{{--
    Invite-a-landlord landing — the page an invited landlord opens from a tenant's /invite/{code}
    link. On the CS dark-premium frontend language (matches the home page / House Hunt). Submits
    the intake form to route('referral.invite.submit', code); owners never self-register, so this
    only ever creates a vetted LEAD in the pipeline.
--}}

<style>
  /* Light design language — matches the marketing site (paper ground, stone ink, amber accent).
     The frontend layout paints a light background, so hero text must be dark to be readable. */
  .inv-page{background:#FAF9F6;}
  /* Top padding clears the sticky public header (House Hunt uses ~132px); 104px gives the
     hero room to breathe below the solid nav without feeling detached. */
  .inv-wrap{max-width:1080px;margin:0 auto;padding:104px 20px 80px;}
  .inv-hero{text-align:center;max-width:740px;margin:0 auto 40px;}
  .inv-eyebrow{display:inline-block;font-size:12px;font-weight:700;letter-spacing:.14em;text-transform:uppercase;
    color:#185FA5;background:#E8F0F9;border:1px solid #CBDDF1;
    padding:6px 14px;border-radius:999px;margin-bottom:18px;}
  .inv-hero h1{font-size:clamp(28px,5vw,44px);line-height:1.12;font-weight:750;margin:0 0 16px;color:#1B1E22;letter-spacing:-.01em;}
  .inv-hero h1 .amp{color:#E7A339;}
  .inv-hero p{font-size:17px;line-height:1.65;color:#6B7280;margin:0;}
  .inv-grid{display:grid;grid-template-columns:1.05fr .95fr;gap:28px;align-items:start;}
  @media (max-width:820px){.inv-grid{grid-template-columns:1fr;}}
  .inv-card{background:#FFFFFF;border:1px solid #E6E1D8;border-radius:18px;padding:26px 26px 30px;
    box-shadow:0 1px 2px rgba(20,23,28,.04),0 10px 30px rgba(20,23,28,.05);}
  .inv-benefits li{list-style:none;display:flex;gap:12px;align-items:flex-start;padding:13px 0;border-top:1px solid #EFEBE3;color:#3A3F47;font-size:15px;line-height:1.5;}
  .inv-benefits li:first-child{border-top:none;}
  .inv-benefits .ic{flex:none;width:34px;height:34px;border-radius:10px;display:grid;place-items:center;
    background:#E8F0F9;color:#185FA5;font-size:17px;}
  .inv-benefits b{color:#1B1E22;font-weight:700;}
  .inv-formhead{margin:0 0 4px;font-size:20px;font-weight:750;color:#1B1E22;}
  .inv-formsub{margin:0 0 20px;font-size:13.5px;color:#6B7280;}
  .inv-field{margin-bottom:15px;}
  .inv-field label{display:block;font-size:12.5px;font-weight:600;color:#3A3F47;margin-bottom:6px;}
  .inv-field label .req{color:#E7A339;}
  .inv-field input,.inv-field select{width:100%;background:#FFFFFF;border:1px solid #E6E1D8;border-radius:10px;
    padding:11px 13px;color:#1B1E22;font-size:14.5px;outline:none;transition:border-color .15s,box-shadow .15s;}
  .inv-field input::placeholder{color:#9AA0A8;}
  .inv-field input:focus,.inv-field select:focus{border-color:#185FA5;box-shadow:0 0 0 3px rgba(24,95,165,.15);}
  .inv-row{display:grid;grid-template-columns:1fr 1fr;gap:13px;}
  @media (max-width:480px){.inv-row{grid-template-columns:1fr;}}
  .inv-submit{width:100%;margin-top:8px;background:linear-gradient(180deg,#2074c4,#185FA5);border:none;border-radius:11px;
    padding:14px;color:#fff;font-size:15.5px;font-weight:650;cursor:pointer;transition:filter .15s;box-shadow:0 10px 24px -12px rgba(24,95,165,.7);}
  .inv-submit:hover{filter:brightness(1.06);}
  .inv-fineprint{margin-top:14px;font-size:12px;color:#9AA0A8;line-height:1.5;text-align:center;}
  .inv-err{background:#FBE9E6;border:1px solid #F0B8B0;color:#B42318;border-radius:10px;
    padding:11px 14px;font-size:13.5px;margin-bottom:16px;}
  .inv-err ul{margin:6px 0 0;padding-left:18px;}
</style>

<div class="inv-page">
<div class="inv-wrap">
  <div class="inv-hero">
    <span class="inv-eyebrow">{{ __('Invitation') }}</span>
    <h1>
      @if ($referrerName)
        {{ $referrerName }} {{ __('invited you to run your property on') }} <span class="amp">Centresidence</span>
      @else
        {{ __('Run your property the modern way with') }} <span class="amp">Centresidence</span>
      @endif
    </h1>
    <p>{{ __('Collect rent from your tenants\' phones, send receipts automatically, and see every unit at a glance — set up in a day, free to start. Tell us a little about your properties and our team will get you going.') }}</p>
  </div>

  <div class="inv-grid">
    <div class="inv-card">
      <ul class="inv-benefits" style="margin:0;padding:0;">
        <li><span class="ic">📲</span><div><b>{{ __('Rent that collects itself') }}</b> — {{ __('tenants pay from their phone, M-Pesa included, and every payment is matched to the right invoice with a receipt sent instantly.') }}</div></li>
        <li><span class="ic">🏢</span><div><b>{{ __('Every property, one dashboard') }}</b> — {{ __('units, tenants, invoices, deposits and move-outs across all your buildings, in real time.') }}</div></li>
        <li><span class="ic">🧾</span><div><b>{{ __('Free to start') }}</b> — {{ __('the essentials cost nothing; add smart meters, a marketplace or financing only when you\'re ready.') }}</div></li>
        <li><span class="ic">🔒</span><div><b>{{ __('Your records, kept straight') }}</b> — {{ __('deposits tracked, agreements signed in-app, and a clean paper trail for every tenancy.') }}</div></li>
      </ul>
    </div>

    <div class="inv-card">
      <h2 class="inv-formhead">{{ __('Get started') }}</h2>
      <p class="inv-formsub">{{ __('No account needed yet — our team reviews every request and reaches out to set you up.') }}</p>

      @if ($errors->any())
        <div class="inv-err">
          {{ __('Please check the form:') }}
          <ul>@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
      @endif

      <form method="POST" action="{{ route('referral.invite.submit', $code) }}">
        @csrf
        <div class="inv-field">
          <label>{{ __('Your name') }} <span class="req">*</span></label>
          <input type="text" name="contact_person_name" value="{{ old('contact_person_name') }}" required maxlength="120" placeholder="{{ __('e.g. Jane Wanjiru') }}">
        </div>
        <div class="inv-field">
          <label>{{ __('Company / property name') }} <span class="req">*</span></label>
          <input type="text" name="company_name" value="{{ old('company_name') }}" required maxlength="160" placeholder="{{ __('e.g. Wanjiru Apartments') }}">
        </div>
        <div class="inv-row">
          <div class="inv-field">
            <label>{{ __('Phone') }} <span class="req">*</span></label>
            <input type="text" name="phone" value="{{ old('phone') }}" required maxlength="32" placeholder="0700 000 000">
          </div>
          <div class="inv-field">
            <label>{{ __('Email') }}</label>
            <input type="email" name="email" value="{{ old('email') }}" maxlength="160" placeholder="you@example.com">
          </div>
        </div>
        <div class="inv-row">
          <div class="inv-field">
            <label>{{ __('City') }}</label>
            <input type="text" name="city" value="{{ old('city') }}" maxlength="120" placeholder="{{ __('e.g. Nairobi') }}">
          </div>
          <div class="inv-field">
            <label>{{ __('Number of units') }}</label>
            <input type="number" name="estimated_units" value="{{ old('estimated_units') }}" min="0" max="100000" placeholder="{{ __('e.g. 12') }}">
          </div>
        </div>
        <div class="inv-field">
          <label>{{ __('Property type') }}</label>
          <select name="property_type">
            @php $ptypes = ['Residential' => __('Residential'), 'Commercial' => __('Commercial'), 'Mixed-use' => __('Mixed-use'), 'Other' => __('Other')]; @endphp
            <option value="">{{ __('Select (optional)') }}</option>
            @foreach ($ptypes as $val => $label)
              <option value="{{ $val }}" @selected(old('property_type') === $val)>{{ $label }}</option>
            @endforeach
          </select>
        </div>
        <button type="submit" class="inv-submit">{{ __('Request my setup') }}</button>
        <p class="inv-fineprint">{{ __('By submitting, you agree to be contacted about setting up your Centresidence account. We never share your details.') }}</p>
      </form>
    </div>
  </div>
</div>
</div>
@endsection
