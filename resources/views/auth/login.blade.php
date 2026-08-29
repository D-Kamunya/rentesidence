@extends('layouts.app')
@push('title')
    {{ __('Login') }} -
@endpush
@section('content')
@php
    $authTitle = getOption('sign_in_text_title');
    $authSub   = getOption('sign_in_text_subtitle');
    // Login slideshow — cross-fades through whichever of the 4 image slots the admin has set.
    $authSlides = collect(['sign_in_image', 'sign_in_image_2', 'sign_in_image_3', 'sign_in_image_4'])
        ->filter(fn ($k) => !empty(getOption($k)))
        ->map(fn ($k) => getSettingImage($k))
        ->values();
    // Real logo only — getSettingImage() falls back to a generic avatar placeholder when
    // app_logo isn't set, so guard on that (and on a null when the file is missing) and
    // show the wordmark instead of a broken/placeholder image.
    $appLogo = getSettingImage('app_logo');
    $hasLogo = $appLogo && !\Illuminate\Support\Str::contains($appLogo, 'empty-user');
@endphp
<div class="cs-auth">
    {{-- Ambient techy background: grid + accent glows --}}
    <div class="cs-auth__bg" aria-hidden="true">
        <div class="cs-auth__grid"></div>
        <div class="cs-auth__glow cs-auth__glow--blue"></div>
        <div class="cs-auth__glow cs-auth__glow--amber"></div>
    </div>

    <div class="cs-auth__inner">
        {{-- Brand panel --}}
        <aside class="cs-auth__brand">
            @if ($authSlides->isNotEmpty())
                <div class="cs-auth__slides" aria-hidden="true">
                    @foreach ($authSlides as $img)
                        <div class="cs-auth__slide" style="background-image:url('{{ $img }}')"></div>
                    @endforeach
                </div>
                <div class="cs-auth__veil" aria-hidden="true"></div>
            @endif

            <div class="cs-auth__brandtop">
                <span class="cs-auth__logo">
                    @if ($hasLogo)
                        <img src="{{ $appLogo }}" alt="{{ getOption('app_name') }}" onerror="this.style.display='none';this.nextElementSibling.style.display='inline';">
                        <span class="cs-auth__wordmark" style="display:none;">{{ getOption('app_name') }}</span>
                    @else
                        <span class="cs-auth__wordmark">{{ getOption('app_name') }}</span>
                    @endif
                </span>
            </div>

            <div class="cs-auth__brandmid">
                <span class="cs-auth__eyebrow">{{ __('Infrastructure & Finance OS') }}</span>
                <h1 class="cs-auth__headline">{{ $authTitle ? __($authTitle) : __('Run properties. Collect rent. Finance the essentials.') }}</h1>
                <p class="cs-auth__sub">{{ $authSub ? __($authSub) : __('One secure platform for owners, tenants and partners — payments, agreements and infrastructure, end to end.') }}</p>

                <ul class="cs-auth__feats">
                    <li><span class="cs-auth__featic">{!! '<svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M3 9.5L12 4l9 5.5M5 11v8h14v-8" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>' !!}</span>{{ __('Properties & tenants, managed end-to-end') }}</li>
                    <li><span class="cs-auth__featic">{!! '<svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M4 7h16v10H4zM4 10h16M8 14h4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>' !!}</span>{{ __('Payments & M-Pesa, built in') }}</li>
                    <li><span class="cs-auth__featic">{!! '<svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M12 3v18M5 8l7-5 7 5M5 8v8l7 5 7-5V8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>' !!}</span>{{ __('Infrastructure financing, repaid at source') }}</li>
                </ul>
            </div>

            <div class="cs-auth__brandfoot">
                <span class="cs-auth__dot"></span> {{ __('Secure • Encrypted • Trusted') }}
            </div>
        </aside>

        {{-- Form panel --}}
        <main class="cs-auth__panel">
            <div class="cs-auth__card">
                {{-- Mobile logo (brand panel hides on small screens) --}}
                <div class="cs-auth__cardlogo">
                    @if ($hasLogo)
                        <img src="{{ $appLogo }}" alt="{{ getOption('app_name') }}" onerror="this.style.display='none';this.nextElementSibling.style.display='inline';">
                        <span class="cs-auth__wordmark cs-auth__wordmark--dark" style="display:none;">{{ getOption('app_name') }}</span>
                    @else
                        <span class="cs-auth__wordmark cs-auth__wordmark--dark">{{ getOption('app_name') }}</span>
                    @endif
                </div>

                <h2 class="cs-auth__title">{{ __('Welcome back') }}</h2>
                <p class="cs-auth__hint">{{ __('Sign in to your account to continue.') }}</p>

                <form action="{{ route('login') }}" method="post" class="cs-auth__form" autocomplete="off">
                    @csrf

                    <div class="cs-fld">
                        <label class="cs-fld__label">{{ __('Email') }}</label>
                        <div class="cs-fld__wrap">
                            <span class="cs-fld__ic">{!! '<svg width="17" height="17" viewBox="0 0 24 24" fill="none"><path d="M4 6h16v12H4zM4 7l8 6 8-6" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>' !!}</span>
                            <input type="text" name="email" value="{{ old('email') }}" class="cs-fld__input email @error('email') is-bad @enderror" placeholder="{{ __('you@company.com') }}" autofocus>
                        </div>
                        @error('email')<span class="cs-fld__err">{{ $message }}</span>@enderror
                    </div>

                    <div class="cs-fld">
                        <label class="cs-fld__label">{{ __('Password') }}</label>
                        <div class="cs-fld__wrap">
                            <span class="cs-fld__ic">{!! '<svg width="17" height="17" viewBox="0 0 24 24" fill="none"><rect x="5" y="10" width="14" height="10" rx="2" stroke="currentColor" stroke-width="1.6"/><path d="M8 10V7a4 4 0 018 0v3" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>' !!}</span>
                            <input type="password" name="password" class="cs-fld__input password @error('password') is-bad @enderror" placeholder="{{ __('••••••••') }}">
                            <button type="button" class="cs-fld__eye" id="csPwToggle" aria-label="{{ __('Show password') }}">
                                {!! '<svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7-10-7-10-7z" stroke="currentColor" stroke-width="1.6"/><circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.6"/></svg>' !!}
                            </button>
                        </div>
                        @error('password')<span class="cs-fld__err">{{ $message }}</span>@enderror
                    </div>

                    @if (getOption('GOOGLE_RECAPTCHA_MAIL_STATUS', 0) == ACTIVE)
                        <div class="cs-fld">
                            <div class="g-recaptcha" data-sitekey="{{ getOption('GOOGLE_RECAPTCHA_KEY') }}"></div>
                            @if ($errors->has('g-recaptcha-response'))
                                <span class="cs-fld__err">{{ $errors->first('g-recaptcha-response') }}</span>
                            @endif
                        </div>
                    @endif

                    <div class="cs-auth__row">
                        <label class="cs-check">
                            <input type="checkbox" id="rememberMe" name="remember" value="1">
                            <span>{{ __('Remember me') }}</span>
                        </label>
                        <a href="{{ route('password.request') }}" class="cs-auth__link">{{ __('Forgot password?') }}</a>
                    </div>

                    <button type="submit" class="cs-auth__submit">
                        <span>{{ __('Sign in') }}</span>
                        {!! '<svg width="17" height="17" viewBox="0 0 24 24" fill="none"><path d="M5 12h14M13 6l6 6-6 6" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"/></svg>' !!}
                    </button>
                </form>

                @if (env('LOGIN_HELP') == 'active')
                    <div class="cs-auth__demo">
                        <span class="cs-auth__demolabel">{{ __('Demo accounts — click to fill') }}</span>
                        <div class="cs-auth__demogrid">
                            <button type="button" id="adminCredentialShow" class="cs-auth__demobtn">Admin</button>
                            <button type="button" id="ownerCredentialShow" class="cs-auth__demobtn">Owner</button>
                            <button type="button" id="tenantCredentialShow" class="cs-auth__demobtn">Tenant</button>
                            <button type="button" id="maintainerCredentialShow" class="cs-auth__demobtn">Maintainer</button>
                        </div>
                    </div>
                @endif
            </div>
        </main>
    </div>
</div>

<style>
    .cs-auth { --ink:#e6ebf3; --muted:#94a3b8; --faint:#5b6b82; --line:rgba(255,255,255,.09);
        --blue:#3b82f6; --blue-deep:#185FA5; --card:rgba(255,255,255,.045);
        position:fixed; inset:0; overflow:auto; background:#080b12;
        font-family:system-ui,-apple-system,'Segoe UI',Roboto,'Poppins',sans-serif; color:var(--ink); z-index:1; }

    /* Ambient background */
    .cs-auth__bg { position:fixed; inset:0; overflow:hidden; z-index:0; }
    .cs-auth__grid { position:absolute; inset:-2px;
        background-image:linear-gradient(rgba(255,255,255,.035) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,.035) 1px, transparent 1px);
        background-size:46px 46px; mask-image:radial-gradient(ellipse 90% 80% at 30% 20%, #000 40%, transparent 90%);
        animation:csGridDrift 26s linear infinite; }
    .cs-auth__glow { position:absolute; border-radius:50%; filter:blur(90px); opacity:.5; }
    /* Cool cs-blue anchor (top-left) + warm marble amber (bottom-right) = upscale duotone ambient. */
    .cs-auth__glow--blue { width:520px; height:520px; left:-120px; top:-140px; background:radial-gradient(circle, #1f6fd6 0%, transparent 70%); animation:csFloat 16s ease-in-out infinite; }
    .cs-auth__glow--amber { width:500px; height:500px; right:-110px; bottom:-160px; background:radial-gradient(circle, #d59a35 0%, transparent 70%); opacity:.32; animation:csFloat 20s ease-in-out infinite reverse; }
    @keyframes csGridDrift { to { background-position:46px 46px, 46px 46px; } }
    @keyframes csFloat { 50% { transform:translate(24px, 20px); } }

    .cs-auth__inner { position:relative; z-index:1; min-height:100%; display:grid; grid-template-columns:1.05fr .95fr; }

    /* Brand panel */
    .cs-auth__brand { position:relative; overflow:hidden; display:flex; flex-direction:column; justify-content:space-between; padding:54px 60px; border-right:0.5px solid var(--line); }
    .cs-auth__brandtop, .cs-auth__brandmid, .cs-auth__brandfoot { position:relative; z-index:2; }

    /* Login slideshow (behind the dark veil) */
    .cs-auth__slides { position:absolute; inset:0; z-index:0; }
    .cs-auth__slide { position:absolute; inset:0; background-size:cover; background-position:center; opacity:0;
        transform:scale(1.06); transition:opacity 1.6s ease; }
    .cs-auth__slide.is-active { opacity:1; animation:csKenBurns 9s ease-out forwards; }
    @keyframes csKenBurns { from { transform:scale(1.06); } to { transform:scale(1.14); } }
    .cs-auth__veil { position:absolute; inset:0; z-index:1;
        background:
            linear-gradient(rgba(255,255,255,.03) 1px, transparent 1px) 0 0/46px 46px,
            linear-gradient(90deg, rgba(255,255,255,.03) 1px, transparent 1px) 0 0/46px 46px,
            linear-gradient(120deg, rgba(8,11,18,.92) 0%, rgba(8,11,18,.60) 52%, rgba(8,11,18,.88) 100%); }
    .cs-auth__logo img { height:40px; width:auto; max-width:190px; object-fit:contain; }
    .cs-auth__wordmark { font-size:22px; font-weight:700; letter-spacing:-.01em; color:#fff; }
    .cs-auth__wordmark--dark { color:#0f172a; }
    .cs-auth__eyebrow { display:inline-block; font-size:11px; font-weight:600; letter-spacing:.18em; text-transform:uppercase; color:#7cc0ff;
        font-family:ui-monospace,'SFMono-Regular',Menlo,monospace; margin-bottom:18px; }
    .cs-auth__headline { font-size:38px; line-height:1.12; font-weight:700; letter-spacing:-.02em; color:#fff; margin:0 0 16px; max-width:15ch; text-wrap:balance; }
    .cs-auth__sub { font-size:15px; line-height:1.7; color:var(--muted); margin:0 0 30px; max-width:44ch; }
    .cs-auth__feats { list-style:none; padding:0; margin:0; display:flex; flex-direction:column; gap:14px; }
    .cs-auth__feats li { display:flex; align-items:center; gap:12px; font-size:13.5px; color:#cbd5e1; }
    .cs-auth__featic { width:34px; height:34px; flex:none; border-radius:10px; display:flex; align-items:center; justify-content:center;
        background:rgba(59,130,246,.12); border:0.5px solid rgba(59,130,246,.25); color:#7cc0ff; }
    .cs-auth__brandfoot { font-size:12px; color:var(--faint); letter-spacing:.02em; display:flex; align-items:center; gap:8px; }
    .cs-auth__dot { width:7px; height:7px; border-radius:50%; background:#1D9E75; box-shadow:0 0 10px #1D9E75; }

    /* Form panel */
    .cs-auth__panel { display:flex; align-items:center; justify-content:center; padding:48px 40px; }
    .cs-auth__card { width:100%; max-width:500px; background:var(--card); border:0.5px solid var(--line); border-radius:22px;
        padding:48px 46px; backdrop-filter:blur(14px); box-shadow:0 30px 60px rgba(0,0,0,.4);
        animation:csRise .5s cubic-bezier(.2,.8,.3,1) both; }
    @keyframes csRise { from { opacity:0; transform:translateY(14px); } }
    .cs-auth__cardlogo { display:none; margin-bottom:24px; }
    .cs-auth__cardlogo img { height:36px; }
    .cs-auth__title { font-size:34px; font-weight:700; letter-spacing:-.02em; color:#fff; margin:0 0 8px; }
    .cs-auth__hint { font-size:15px; color:var(--muted); margin:0 0 30px; }
    .cs-auth__form { display:flex; flex-direction:column; gap:19px; }

    .cs-fld { display:flex; flex-direction:column; gap:8px; }
    .cs-fld__label { font-size:12px; font-weight:600; letter-spacing:.06em; text-transform:uppercase; color:var(--faint); }
    .cs-fld__wrap { position:relative; display:flex; align-items:center; }
    .cs-fld__ic { position:absolute; left:15px; color:#5b6b82; display:flex; pointer-events:none; }
    .cs-fld__input { width:100%; background:#0c111b; border:0.5px solid #1e293b; border-radius:12px; color:var(--ink);
        font-size:15.5px; padding:14px 16px 14px 44px; outline:none; transition:border-color .15s, box-shadow .15s, background .15s; }
    .cs-fld__input::placeholder { color:#475569; }
    .cs-fld__input:focus { border-color:var(--blue-deep); background:#0d1420; box-shadow:0 0 0 3px rgba(59,130,246,.18); }
    .cs-fld__input.is-bad { border-color:#b4471f; }
    .cs-fld__eye { position:absolute; right:8px; width:32px; height:32px; display:flex; align-items:center; justify-content:center;
        background:transparent; border:none; color:#5b6b82; cursor:pointer; border-radius:8px; transition:color .13s, background .13s; }
    .cs-fld__eye:hover { color:#93c5fd; background:rgba(255,255,255,.05); }
    .cs-fld__err { font-size:12.5px; color:#f0906b; }

    .cs-auth__row { display:flex; align-items:center; justify-content:space-between; gap:12px; margin-top:4px; }
    .cs-check { display:flex; align-items:center; gap:9px; cursor:pointer; font-size:14px; color:var(--muted); margin:0; }
    .cs-check input { width:17px; height:17px; accent-color:var(--blue-deep); cursor:pointer; }
    .cs-auth__link { font-size:14px; color:#7cc0ff; text-decoration:none; font-weight:500; }
    .cs-auth__link:hover { color:#a9d5ff; }

    .cs-auth__submit { margin-top:8px; display:flex; align-items:center; justify-content:center; gap:10px; width:100%;
        background:linear-gradient(135deg,#2b7fe0 0%, #185FA5 100%); color:#fff; border:none; border-radius:12px;
        font-size:16px; font-weight:600; padding:15px 18px; cursor:pointer;
        box-shadow:0 8px 24px rgba(24,95,165,.4); transition:transform .12s, box-shadow .15s, filter .15s; }
    .cs-auth__submit:hover { filter:brightness(1.08); box-shadow:0 12px 30px rgba(24,95,165,.55); transform:translateY(-1px); }
    .cs-auth__submit:active { transform:translateY(0); }

    .cs-auth__demo { margin-top:24px; padding-top:20px; border-top:0.5px solid var(--line); }
    .cs-auth__demolabel { font-size:11px; color:var(--faint); letter-spacing:.04em; }
    .cs-auth__demogrid { display:grid; grid-template-columns:1fr 1fr; gap:8px; margin-top:10px; }
    .cs-auth__demobtn { font-size:12px; font-weight:500; color:#a9b6c8; background:rgba(255,255,255,.04); border:0.5px solid var(--line);
        border-radius:8px; padding:8px; cursor:pointer; transition:all .13s; font-family:ui-monospace,Menlo,monospace; }
    .cs-auth__demobtn:hover { color:#fff; border-color:rgba(59,130,246,.4); background:rgba(59,130,246,.1); }

    @media (max-width: 900px) {
        .cs-auth__inner { grid-template-columns:1fr; }
        .cs-auth__brand { display:none; }
        .cs-auth__cardlogo { display:block; }
        .cs-auth__panel { padding:32px 20px; min-height:100%; }
        .cs-auth__card { background:rgba(255,255,255,.05); }
    }
    @media (prefers-reduced-motion: reduce) {
        .cs-auth__grid, .cs-auth__glow, .cs-auth__card { animation:none; }
    }
</style>
@endsection
@push('script')
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<script>
    "use strict";
    (function () {
        // Login slideshow — cross-fade cycle (static first image if reduced-motion / single image)
        var slides = document.querySelectorAll('.cs-auth__slide');
        if (slides.length) {
            slides[0].classList.add('is-active');
            var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            if (slides.length > 1 && !reduce) {
                var i = 0;
                setInterval(function () {
                    slides[i].classList.remove('is-active');
                    i = (i + 1) % slides.length;
                    slides[i].classList.add('is-active');
                }, 7000);
            }
        }

        // Password show/hide
        var toggle = document.getElementById('csPwToggle');
        if (toggle) {
            toggle.addEventListener('click', function () {
                var input = document.querySelector('.cs-fld__input.password');
                if (!input) return;
                input.type = input.type === 'password' ? 'text' : 'password';
                toggle.style.color = input.type === 'text' ? '#93c5fd' : '';
            });
        }
        // Demo credential fill
        var demos = {
            adminCredentialShow: ['admin@gmail.com', '123456'],
            ownerCredentialShow: ['owner@gmail.com', '123456'],
            tenantCredentialShow: ['tenant@gmail.com', '123456'],
            maintainerCredentialShow: ['maintainer@gmail.com', '123456']
        };
        Object.keys(demos).forEach(function (id) {
            var el = document.getElementById(id);
            if (el) el.addEventListener('click', function () {
                document.querySelector('.cs-fld__input.email').value = demos[id][0];
                document.querySelector('.cs-fld__input.password').value = demos[id][1];
            });
        });
    })();
</script>
@endpush
