@extends('saas.frontend.layouts.app')
@section('content')
@php $pageTitle = 'Become a Centresidence Affiliate'; @endphp

<style>
  .aa-wrap { max-width:1040px; margin:0 auto; padding:150px 20px 70px; }
  .aa-grid { display:grid; grid-template-columns:1fr 1.05fr; gap:44px; align-items:start; }
  @media (max-width:880px){ .aa-grid{ grid-template-columns:1fr; gap:28px; } .aa-wrap{ padding-top:120px; } }
  .aa-eyebrow { font-size:12.5px; font-weight:700; letter-spacing:.16em; text-transform:uppercase; color:#185FA5; margin:0 0 12px; }
  .aa-title { font-size:clamp(28px,4vw,40px); font-weight:800; letter-spacing:-.02em; color:#1b1e22; line-height:1.1; margin:0 0 16px; }
  .aa-lede { font-size:16px; color:#4a4f57; line-height:1.65; margin:0 0 24px; max-width:46ch; }
  .aa-points { list-style:none; padding:0; margin:0 0 24px; display:flex; flex-direction:column; gap:14px; }
  .aa-points li { display:flex; gap:12px; align-items:flex-start; font-size:14.5px; color:#3a3f47; line-height:1.5; }
  .aa-points .ic { width:30px; height:30px; border-radius:9px; background:#E6F1FB; color:#185FA5; display:grid; place-items:center; flex:none; }

  /* The honest terms — deliberately plain and up-front. Reaching the form means you saw this. */
  .aa-terms { border:1px solid #e6e1d8; border-radius:14px; padding:18px 20px; background:#FBFAF7; }
  .aa-terms h3 { font-size:13px; font-weight:750; letter-spacing:.02em; color:#1b1e22; margin:0 0 4px; display:flex; align-items:center; gap:8px; }
  .aa-terms h3 .dot { width:7px; height:7px; border-radius:50%; background:#C7891F; flex:none; }
  .aa-terms .cap { font-size:11.5px; color:#9aa2ad; margin:0 0 12px; }
  .aa-terms ul { list-style:none; padding:0; margin:0; display:flex; flex-direction:column; gap:10px; }
  .aa-terms li { display:flex; gap:10px; align-items:flex-start; font-size:13px; color:#44474d; line-height:1.55; }
  .aa-terms li svg { flex:none; margin-top:2px; color:#185FA5; }
  .aa-terms li b { color:#1b1e22; font-weight:700; }

  .aa-card { background:#fff; border:1px solid #e6e1d8; border-radius:18px; padding:28px 28px 30px; box-shadow:0 1px 2px rgba(20,23,28,.04),0 18px 44px rgba(20,23,28,.07); }
  .aa-card h2 { font-size:19px; font-weight:750; color:#1b1e22; margin:0 0 4px; }
  .aa-card .sub { font-size:13.5px; color:#6b7280; margin:0 0 20px; }
  .aa-field { margin-bottom:15px; }
  .aa-row2 { display:grid; grid-template-columns:1fr 1fr; gap:12px; }
  @media (max-width:460px){ .aa-row2{ grid-template-columns:1fr; } }
  .aa-field label { display:block; font-size:12.5px; font-weight:650; color:#4a4f57; margin-bottom:6px; }
  .aa-field .hint { font-weight:500; color:#9aa2ad; }
  .aa-field input, .aa-field textarea { width:100%; background:#fff; border:1px solid #e6e1d8; border-radius:11px; padding:12px 14px; font-size:14.5px; color:#1b1e22; outline:none; transition:.15s; font-family:inherit; }
  .aa-field input:focus, .aa-field textarea:focus { border-color:#185FA5; box-shadow:0 0 0 3px rgba(24,95,165,.14); }
  .aa-field .err { color:#B42318; font-size:12px; margin-top:6px; }
  .aa-consent { display:flex; align-items:flex-start; gap:10px; font-size:13px; color:#44474d; line-height:1.55; margin:8px 0 18px; border:1px solid #e6e1d8; border-radius:12px; padding:13px 14px; background:#FBFAF7; }
  .aa-consent input { margin-top:2px; flex:none; width:16px; height:16px; accent-color:#185FA5; }
  .aa-consent b { color:#1b1e22; }
  .aa-submit { width:100%; border:none; border-radius:12px; background:#185FA5; color:#fff; font-size:15.5px; font-weight:700; padding:14px; cursor:pointer; transition:.15s; box-shadow:0 10px 24px -12px rgba(24,95,165,.8); }
  .aa-submit:hover { background:#0F4A84; transform:translateY(-1px); }
  .aa-note { font-size:12px; color:#9aa2ad; margin-top:12px; text-align:center; line-height:1.5; }

  .aa-result { text-align:center; padding:14px 10px; }
  .aa-result .badge { width:64px; height:64px; border-radius:50%; display:grid; place-items:center; margin:0 auto 18px; }
  .aa-result h2 { font-size:22px; font-weight:800; color:#1b1e22; margin:0 0 8px; }
  .aa-result p { font-size:15px; color:#4a4f57; line-height:1.6; margin:0 auto 20px; max-width:40ch; }
  .aa-result .lnk { display:inline-flex; align-items:center; gap:7px; color:#185FA5; font-weight:700; text-decoration:none; font-size:14.5px; }
</style>

<div class="aa-wrap">
  <div class="aa-grid">

    {{-- Pitch + the honest terms --}}
    <div>
      <p class="aa-eyebrow">Centresidence Affiliate Program</p>
      <h1 class="aa-title">Earn by bringing landlords onto Centresidence.</h1>
      <p class="aa-lede">Introduce property owners to Centresidence and earn <b>recurring commission</b> on what they do on the platform — with the tools, training and support to actually make it happen.</p>
      <ul class="aa-points">
        <li><span class="ic"><svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M4 19V5a2 2 0 012-2h9l5 5v11a2 2 0 01-2 2H6a2 2 0 01-2-2z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/><path d="M8 12h8M8 16h5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg></span><span><b>A full toolkit</b> — lead tools, marketing materials and a partner academy that certifies you.</span></li>
        <li><span class="ic"><svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M12 2l7 3v6c0 4.5-3 8-7 9-4-1-7-4.5-7-9V5l7-3z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/><path d="M9 12l2 2 4-4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg></span><span><b>Paid straight to M-Pesa</b>, with a clear dashboard of everything your owners generate.</span></li>
      </ul>

      {{-- Up-front, plain-language terms. This is the structural fix for "I didn't read the JD". --}}
      <div class="aa-terms">
        <h3><span class="dot"></span> How the pay actually works — please read</h3>
        <p class="cap">We'd rather you know this now than be surprised later.</p>
        <ul>
          <li><svg width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M20 6L9 17l-5-5" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg><span><b>Commission-only.</b> There is no salary and no retainer. You earn when the owners you bring on use Centresidence.</span></li>
          <li><svg width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M20 6L9 17l-5-5" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg><span>You earn <b>recurring commission on what owners do</b> — subscriptions, rent, marketplace, screening, agreements and financing — not just for signing them up.</span></li>
          <li><svg width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M20 6L9 17l-5-5" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg><span>Commission on each owner runs for a <b>set period (about 12 months)</b> per earning type — it is ongoing, not forever.</span></li>
          <li><svg width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M20 6L9 17l-5-5" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg><span>You cover <b>your own costs</b> — your time, transport and any airtime or ads you choose to run.</span></li>
          <li><svg width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M20 6L9 17l-5-5" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg><span>Your earnings and every owner's activity are <b>fully visible in your dashboard</b>, and you withdraw to M-Pesa.</span></li>
        </ul>
      </div>
    </div>

    {{-- Form / result --}}
    <div class="aa-card">
      @if (session('applied'))
        <div class="aa-result">
          <div class="badge" style="background:#E1F5EE;color:#0F6E56;"><svg width="30" height="30" viewBox="0 0 24 24" fill="none"><path d="M20 6L9 17l-5-5" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg></div>
          <h2>Application received</h2>
          <p>Thanks for applying! Our team reviews applications and will get in touch. If approved, your login details arrive by email and SMS.</p>
          <a href="{{ route('frontend') }}" class="lnk">Back to Centresidence <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M5 12h14M13 6l6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></a>
        </div>
      @elseif (session('already'))
        <div class="aa-result">
          <div class="badge" style="background:#E6F1FB;color:#185FA5;"><svg width="30" height="30" viewBox="0 0 24 24" fill="none"><path d="M12 2l7 3v6c0 4.5-3 8-7 9-4-1-7-4.5-7-9V5l7-3z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/></svg></div>
          <h2>You're already an affiliate</h2>
          <p>An affiliate account already exists for this email. Just sign in to reach your dashboard.</p>
          <a href="{{ route('login') }}" class="lnk">Sign in <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M5 12h14M13 6l6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></a>
        </div>
      @else
        <h2>Apply to join</h2>
        <p class="sub">Tell us a little about you — it takes a minute.</p>
        <form method="POST" action="{{ route('affiliate.apply.store') }}">
          @csrf
          <div class="aa-row2">
            <div class="aa-field">
              <label>First name</label>
              <input type="text" name="first_name" value="{{ old('first_name') }}" maxlength="60" required>
              @error('first_name')<div class="err">{{ $message }}</div>@enderror
            </div>
            <div class="aa-field">
              <label>Last name</label>
              <input type="text" name="last_name" value="{{ old('last_name') }}" maxlength="60" required>
              @error('last_name')<div class="err">{{ $message }}</div>@enderror
            </div>
          </div>
          <div class="aa-field">
            <label>Email</label>
            <input type="email" name="email" value="{{ old('email') }}" maxlength="120" required placeholder="you@email.com">
            @error('email')<div class="err">{{ $message }}</div>@enderror
          </div>
          <div class="aa-field">
            <label>Phone</label>
            <input type="text" name="phone" value="{{ old('phone') }}" maxlength="20" required placeholder="0700 000 000">
            @error('phone')<div class="err">{{ $message }}</div>@enderror
          </div>
          <div class="aa-row2">
            <div class="aa-field">
              <label>Where are you based? <span class="hint">(optional)</span></label>
              <input type="text" name="location" value="{{ old('location') }}" maxlength="120" placeholder="Town / county">
            </div>
            <div class="aa-field">
              <label>Which areas will you cover?</label>
              <input type="text" name="coverage_area" value="{{ old('coverage_area') }}" maxlength="160" required placeholder="e.g. Ruaka, Kiambu Rd">
              @error('coverage_area')<div class="err">{{ $message }}</div>@enderror
            </div>
          </div>
          <div class="aa-field">
            <label>Any sales or real-estate experience? <span class="hint">(optional)</span></label>
            <textarea name="experience" rows="2" maxlength="2000" placeholder="e.g. 2 years with an estate agent, or new to this — both are welcome.">{{ old('experience') }}</textarea>
          </div>
          <div class="aa-field">
            <label>Contacts in banking or lending? <span class="hint">(optional)</span></label>
            <textarea name="finance_contacts" rows="2" maxlength="2000" placeholder="Banks, SACCOs or lenders you know — useful for our financing side.">{{ old('finance_contacts') }}</textarea>
          </div>
          <div class="aa-field">
            <label>How will you promote Centresidence? <span class="hint">(optional)</span></label>
            <textarea name="pitch" rows="2" maxlength="2000" placeholder="e.g. I work with landlords / estate agents in my area…">{{ old('pitch') }}</textarea>
          </div>
          <label class="aa-consent">
            <input type="checkbox" name="agree" value="1" {{ old('agree') ? 'checked' : '' }}>
            <span>I have read and accept the <b>Centresidence Partner Terms</b>. I understand this is a <b>commission-only</b> role — no salary or retainer — and that I earn recurring commission on what the owners I introduce do on the platform.</span>
          </label>
          @error('agree')<div class="err" style="color:#B42318;font-size:12px;margin:-10px 0 12px;">{{ $message }}</div>@enderror
          <button type="submit" class="aa-submit">Submit application</button>
          <p class="aa-note">No account is created yet. Our team reviews every application; if approved, your login details are sent to you automatically by email and SMS.</p>
        </form>
      @endif
    </div>

  </div>
</div>
@endsection
