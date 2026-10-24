@extends('saas.frontend.layouts.app')
@section('content')
@php
  $pageTitle = 'Become a Centresidence Affiliate';
  // Pull the recurring-commission window from settings so this page never needs editing if the
  // length changes (same source the commission engine + academy read).
  $recurMonths = (int) (getOption('RECURRING_COMMISSION_MONTHS') ?: 12);
@endphp

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
  .aa-terms .glabel { font-size:10.5px; font-weight:700; letter-spacing:.1em; text-transform:uppercase; color:#9aa2ad; margin:14px 0 9px; }
  .aa-terms .glabel:first-of-type { margin-top:4px; }
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
  .aa-field input, .aa-field textarea, .aa-field select { width:100%; background:#fff; border:1px solid #e6e1d8; border-radius:11px; padding:12px 14px; font-size:14.5px; color:#1b1e22; outline:none; transition:.15s; font-family:inherit; }
  .aa-field select { appearance:none; -webkit-appearance:none; background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%239aa2ad' stroke-width='2.4' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E"); background-repeat:no-repeat; background-position:right 14px center; padding-right:36px; cursor:pointer; }
  .aa-field select:disabled { background-color:#f6f5f2; color:#9aa2ad; cursor:not-allowed; }
  .aa-field input:focus, .aa-field textarea:focus, .aa-field select:focus { border-color:#185FA5; box-shadow:0 0 0 3px rgba(24,95,165,.14); }
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
      <p class="aa-lede">Introduce property owners to Centresidence and earn <b>recurring commission</b> on what they do on the platform — working remotely, on your own schedule, with the tools, training and support to actually make it happen.</p>

      {{-- Up-front, plain-language terms — the honest part AND the good part, so the role is clear
           without reading as a list of warnings. Structural fix for V1's "I didn't read the JD". --}}
      <div class="aa-terms">
        <h3><span class="dot"></span> How it works — in plain words</h3>
        <p class="cap">The honest basics and the good parts, so nothing's a surprise later.</p>

        <div class="glabel">The honest part</div>
        <ul>
          <li><svg width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M20 6L9 17l-5-5" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg><span><b>Commission-only.</b> You join as an independent partner, not a salaried employee — there's no salary or retainer. You earn when the owners you bring on use Centresidence.</span></li>
          <li><svg width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M20 6L9 17l-5-5" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg><span>You earn <b>recurring commission on what your owners do</b> — subscriptions, rent, marketplace, screening, agreements and financing — not just for signing them up.</span></li>
          <li><svg width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M20 6L9 17l-5-5" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg><span>Commission on each owner runs for a <b>set period (about {{ $recurMonths }} months)</b> per earning type — steady, ongoing income rather than a one-off.</span></li>
          <li><svg width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M20 6L9 17l-5-5" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg><span>You cover <b>your own costs</b> — time, transport and any ads you choose to run — and you keep the full upside of your effort.</span></li>
        </ul>

        <div class="glabel">The good part</div>
        <ul>
          <li><svg width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M20 6L9 17l-5-5" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg><span><b>Fully remote.</b> Work from anywhere with an internet connection.</span></li>
          <li><svg width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M20 6L9 17l-5-5" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg><span><b>Flexible and self-paced.</b> You choose your own hours and schedule — fit it around your life.</span></li>
          <li><svg width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M20 6L9 17l-5-5" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg><span><b>Build real, recurring income.</b> It works beautifully as a side hustle — no need to quit your job — and because your commissions recur, it can grow into something you could one day do full-time.</span></li>
          <li><svg width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M20 6L9 17l-5-5" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg><span><b>No targets, no pressure, no one chasing you.</b> Your earnings grow with your own effort and consistency — on your terms.</span></li>
          <li><svg width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M20 6L9 17l-5-5" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg><span><b>We set you up to win.</b> A partner academy that certifies you, ready-made marketing materials and ongoing support — with your earnings and each owner's activity visible in your dashboard and paid to M-Pesa.</span></li>
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
          @php $marketCountries = csMarketCountries(); @endphp
          <div class="aa-row2">
            <div class="aa-field">
              <label>Country</label>
              <select name="country" id="aaCountry" required>
                <option value="">Select country</option>
                @foreach ($marketCountries as $c)
                  <option value="{{ $c }}" {{ old('country', count($marketCountries) === 1 ? $marketCountries[0] : '') === $c ? 'selected' : '' }}>{{ $c }}</option>
                @endforeach
              </select>
              @error('country')<div class="err">{{ $message }}</div>@enderror
            </div>
            <div class="aa-field">
              <label>County</label>
              <select name="county" id="aaCounty" required data-old="{{ old('county') }}" disabled>
                <option value="">Select county</option>
              </select>
              @error('county')<div class="err">{{ $message }}</div>@enderror
            </div>
          </div>
          <div class="aa-field">
            <label>Town / area <span class="hint">(optional)</span></label>
            <input type="text" name="town" value="{{ old('town') }}" maxlength="120" placeholder="e.g. Ruaka">
            @error('town')<div class="err">{{ $message }}</div>@enderror
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

@php
  $marketDivisions = collect(csMarkets())->mapWithKeys(fn ($m) => [$m['name'] => array_values($m['divisions'] ?? [])])->all();
@endphp
<script>
  (function () {
    var MARKETS = @json($marketDivisions);
    var country = document.getElementById('aaCountry');
    var county  = document.getElementById('aaCounty');
    if (!country || !county) return;

    function fillCounties(preselect) {
      var list = MARKETS[country.value] || [];
      county.innerHTML = '<option value="">Select county</option>';
      list.forEach(function (name) {
        var o = document.createElement('option');
        o.value = name; o.textContent = name;
        if (name === preselect) o.selected = true;
        county.appendChild(o);
      });
      county.disabled = list.length === 0;
    }

    // Prime on load (keeps a prefilled country + the old county after a validation error).
    fillCounties(county.getAttribute('data-old') || '');
    country.addEventListener('change', function () { fillCounties(''); });
  })();
</script>
@endsection
