<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>{{ __('Get paid on time — free for your first 30 units') }} · {{ getOption('app_name') ?: 'Centresidence' }}</title>
    <meta name="description" content="{{ __('Collect rent over M-Pesa automatically. Automatic reminders, instant receipts, one dashboard. Free for your first 30 units — no card, start with one building.') }}">
    @include('common.layouts.pwa-meta')
    @include('common.partials.meta-pixel')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Sora:wght@700;800&family=Inter:wght@400;500;600;700&display=swap">
    @php
        $appName    = getOption('app_name') ?: 'Centresidence';
        $startUrl   = \Illuminate\Support\Facades\Route::has('owner.register.form') ? route('owner.register.form') : url('/');
        $signInUrl  = \Illuminate\Support\Facades\Route::has('login') ? route('login') : url('/login');
        $rentAppUrl = \Illuminate\Support\Facades\Route::has('rent-app') ? route('rent-app') : url('/');
        $siteUrl    = \Illuminate\Support\Facades\Route::has('frontend') ? route('frontend') : url('/');
    @endphp
    <style>
        :root{
            --blue:#185FA5; --blue-2:#2E7FC7; --blue-deep:#0F4A84; --ink:#0F1E2E; --ink-2:#44515F;
            --ink-3:#7A8796; --amber:#E6A43B; --green:#1D9E75; --paper:#F6F8FB; --card:#FFFFFF;
            --line:#E4E9F0; --mint:#E7F6F0;
        }
        *{box-sizing:border-box;margin:0;padding:0}
        html{scroll-behavior:smooth}
        body{font-family:"Inter",system-ui,-apple-system,"Segoe UI",Roboto,sans-serif;color:var(--ink-2);
            background:var(--paper);line-height:1.6;-webkit-font-smoothing:antialiased;overflow-x:hidden}
        h1,h2,h3{font-family:"Sora","Inter",system-ui,sans-serif;color:var(--ink);line-height:1.15;letter-spacing:-.015em}
        a{text-decoration:none;color:inherit}
        .wrap{max-width:1040px;margin:0 auto;padding-inline:20px}
        .btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;font-weight:700;border:none;
            border-radius:13px;cursor:pointer;text-decoration:none;transition:transform .14s,box-shadow .14s,background .14s;font-size:15px}
        .btn--primary{background:var(--blue);color:#fff;padding:14px 26px;box-shadow:0 14px 30px -14px rgba(24,95,165,.85)}
        .btn--primary:hover{background:var(--blue-deep);transform:translateY(-2px)}
        .btn--ghost{background:transparent;color:var(--ink);padding:13px 22px;border:1.5px solid var(--line)}
        .btn--ghost:hover{border-color:var(--blue);color:var(--blue)}
        .eyebrow{font-size:12px;font-weight:700;letter-spacing:.14em;text-transform:uppercase;color:var(--blue)}

        /* Top bar */
        .bar{position:sticky;top:0;z-index:40;background:rgba(246,248,251,.82);backdrop-filter:saturate(180%) blur(10px);
            border-bottom:1px solid var(--line)}
        .bar .wrap{display:flex;align-items:center;justify-content:space-between;height:62px}
        .brand{display:flex;align-items:center;gap:10px;font-family:"Sora",sans-serif;font-weight:700;color:var(--ink);font-size:17px}
        .brand img{width:34px;height:34px;border-radius:9px}

        /* Hero */
        .hero{padding:58px 0 20px;position:relative}
        .hero::before{content:"";position:absolute;inset:0;z-index:-1;
            background:radial-gradient(90% 60% at 80% -10%,rgba(46,127,199,.14),transparent 60%),
                       radial-gradient(70% 50% at 0% 0%,rgba(230,164,59,.10),transparent 55%)}
        .hero .grid{display:grid;grid-template-columns:1.05fr .95fr;gap:40px;align-items:center}
        .hero h1{font-size:clamp(30px,5.2vw,50px);font-weight:800;margin:14px 0 16px}
        .hero h1 .hl{color:var(--blue)}
        .hero .lead{font-size:clamp(16px,2.2vw,19px);color:var(--ink-2);max-width:40ch;margin-bottom:12px}
        .freepill{display:inline-flex;align-items:center;gap:8px;background:var(--mint);color:#0F6E56;
            font-weight:600;font-size:13.5px;padding:7px 14px;border-radius:999px;margin-bottom:22px}
        .hero .cta-row{display:flex;gap:12px;flex-wrap:wrap;align-items:center}
        .hero .microcopy{font-size:12.5px;color:var(--ink-3);margin-top:14px}

        /* Hero visual — an M-Pesa 'rent paid' card mock (echoes the ad) */
        .mock{background:var(--card);border:1px solid var(--line);border-radius:22px;padding:22px;
            box-shadow:0 30px 60px -30px rgba(15,30,46,.4);position:relative}
        .mock .row{display:flex;align-items:center;gap:12px}
        .mock .tick{width:46px;height:46px;border-radius:50%;background:var(--mint);display:grid;place-items:center;flex:none}
        .mock .amt{font-family:"Sora",sans-serif;font-weight:800;color:var(--ink);font-size:26px;line-height:1.1}
        .mock .sub{font-size:12.5px;color:var(--ink-3)}
        .mock hr{border:none;border-top:1px dashed var(--line);margin:18px 0}
        .mock .line{display:flex;justify-content:space-between;font-size:13px;margin-bottom:8px}
        .mock .line b{color:var(--ink)}
        .mock .paid{display:inline-flex;align-items:center;gap:6px;background:var(--mint);color:#0F6E56;font-weight:700;
            font-size:11.5px;padding:3px 10px;border-radius:999px}
        .mock .float{position:absolute;right:-14px;top:-16px;background:var(--blue);color:#fff;font-weight:700;font-size:12px;
            padding:9px 13px;border-radius:12px;box-shadow:0 12px 24px -10px rgba(24,95,165,.9);display:flex;align-items:center;gap:7px}

        /* Sections */
        section{padding:52px 0}
        .sec-head{text-align:center;max-width:46ch;margin:0 auto 34px}
        .sec-head h2{font-size:clamp(23px,3.6vw,32px);font-weight:800;margin:8px 0 10px}
        .sec-head p{color:var(--ink-2)}

        .cards{display:grid;grid-template-columns:repeat(4,1fr);gap:16px}
        .vcard{background:var(--card);border:1px solid var(--line);border-radius:16px;padding:22px 18px}
        .vcard .ic{width:44px;height:44px;border-radius:12px;background:#E6F1FB;display:grid;place-items:center;margin-bottom:14px}
        .vcard h3{font-size:16px;font-weight:700;margin-bottom:6px}
        .vcard p{font-size:13.5px;color:var(--ink-3)}

        .band{background:var(--ink);color:#DCE6F0}
        .band .sec-head h2{color:#fff}
        .band .sec-head p{color:#AEBECE}
        .steps{display:grid;grid-template-columns:repeat(3,1fr);gap:20px}
        .step{position:relative;padding-left:0}
        .step .n{font-family:"Sora",sans-serif;font-weight:800;font-size:15px;color:var(--ink);background:var(--amber);
            width:34px;height:34px;border-radius:10px;display:grid;place-items:center;margin-bottom:14px}
        .step h3{color:#fff;font-size:17px;margin-bottom:7px}
        .step p{color:#AEBECE;font-size:14px}

        .free{display:grid;grid-template-columns:1.1fr 1fr;gap:36px;align-items:center}
        .free .checks{list-style:none;display:flex;flex-direction:column;gap:14px}
        .free .checks li{display:flex;gap:12px;align-items:flex-start;font-size:15px;color:var(--ink-2)}
        .free .checks .ck{flex:none;width:26px;height:26px;border-radius:50%;background:var(--mint);display:grid;place-items:center;margin-top:1px}
        .free .checks b{color:var(--ink)}
        .freebox{background:linear-gradient(160deg,#185FA5,#0F4A84);color:#fff;border-radius:22px;padding:30px;text-align:center;
            box-shadow:0 30px 60px -30px rgba(24,95,165,.8)}
        .freebox .big{font-family:"Sora",sans-serif;font-weight:800;font-size:40px;line-height:1}
        .freebox .cap{color:#BBD7F0;font-size:13px;margin:8px 0 20px}

        .trust{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;text-align:center}
        .trust .t{background:var(--card);border:1px solid var(--line);border-radius:16px;padding:22px 16px}
        .trust .t .ic{width:40px;height:40px;border-radius:11px;background:#E6F1FB;display:grid;place-items:center;margin:0 auto 12px}
        .trust .t h3{font-size:15px;margin-bottom:5px}
        .trust .t p{font-size:13px;color:var(--ink-3)}

        .final{text-align:center;background:
            radial-gradient(70% 90% at 50% 0%,rgba(46,127,199,.12),transparent 60%)}
        .final h2{font-size:clamp(25px,4vw,36px);font-weight:800;margin-bottom:12px}
        .final p{color:var(--ink-2);max-width:40ch;margin:0 auto 24px}

        /* Sticky mobile CTA */
        .sticky{position:fixed;left:0;right:0;bottom:0;z-index:50;padding:12px 16px calc(12px + env(safe-area-inset-bottom));
            background:rgba(255,255,255,.92);backdrop-filter:blur(10px);border-top:1px solid var(--line);
            transform:translateY(130%);transition:transform .25s ease;display:flex;gap:10px;align-items:center;justify-content:center}
        .sticky.show{transform:translateY(0)}
        .sticky .btn--primary{flex:1;max-width:420px}

        footer{border-top:1px solid var(--line);padding:30px 0 90px;color:var(--ink-3);font-size:13px}
        footer .wrap{display:flex;flex-wrap:wrap;gap:14px;align-items:center;justify-content:space-between}
        footer a{color:var(--blue);font-weight:600}

        @media(max-width:860px){
            .hero .grid{grid-template-columns:1fr;gap:30px}
            .mock{max-width:420px}
            .cards{grid-template-columns:1fr 1fr}
            .steps{grid-template-columns:1fr}
            .free{grid-template-columns:1fr;gap:24px}
            .trust{grid-template-columns:1fr}
        }
        @media(max-width:460px){ .cards{grid-template-columns:1fr} }
    </style>
</head>
<body>
    <div class="bar">
        <div class="wrap">
            <a class="brand" href="{{ $siteUrl }}">
                <img src="{{ asset('assets/images/cs-icon.png') }}" alt="{{ $appName }}">{{ $appName }}
            </a>
            <a href="{{ $signInUrl }}" class="btn btn--ghost" style="padding:9px 16px;font-size:13.5px">{{ __('Sign in') }}</a>
        </div>
    </div>

    {{-- HERO --}}
    <section class="hero">
        <div class="wrap grid">
            <div>
                <span class="eyebrow">{{ __('For landlords') }}</span>
                <h1>{{ __('Get paid on time —') }} <span class="hl">{{ __('without the chasing.') }}</span></h1>
                <p class="lead">{{ __('Centresidence collects your rent over M-Pesa automatically, reminds your tenants for you, and keeps every record in sync. You just watch it come in.') }}</p>
                <span class="freepill">✓ {{ __('Free for your first 30 units — no card needed') }}</span>
                <div class="cta-row">
                    <a href="{{ $startUrl }}" class="btn btn--primary">{{ __('Start free') }} →</a>
                    <a href="#how" class="btn btn--ghost">{{ __('See how it works') }}</a>
                </div>
                <p class="microcopy">{{ __('Set up in minutes · start with one building · your tenants don’t need to learn anything.') }}</p>
            </div>
            <div class="mock">
                <div class="float">⚡ {{ __('Auto-reconciled') }}</div>
                <div class="row">
                    <div class="tick">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M5 12.5l4 4 10-10" stroke="#1D9E75" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </div>
                    <div>
                        <div class="amt">KSh 12,000</div>
                        <div class="sub">{{ __('Rent received · M-Pesa') }}</div>
                    </div>
                </div>
                <hr>
                <div class="line"><span>{{ __('Tenant') }}</span><b>Jane W.</b></div>
                <div class="line"><span>{{ __('Unit') }}</span><b>Riverside · B4</b></div>
                <div class="line"><span>{{ __('Status') }}</span><span class="paid">● {{ __('Paid') }}</span></div>
                <div class="line" style="margin-top:4px"><span>{{ __('Receipt') }}</span><b>{{ __('Sent automatically') }}</b></div>
            </div>
        </div>
    </section>

    {{-- THE RELIEF --}}
    <section>
        <div class="wrap">
            <div class="sec-head">
                <span class="eyebrow">{{ __('The end of rent-chasing') }}</span>
                <h2>{{ __('The admin that ate your evenings — gone.') }}</h2>
                <p>{{ __('No more notebooks, no more “did you get my rent?”, no more squinting at M-Pesa messages.') }}</p>
            </div>
            <div class="cards">
                <div class="vcard">
                    <div class="ic">📲</div>
                    <h3>{{ __('M-Pesa, reconciled') }}</h3>
                    <p>{{ __('Every payment matched to the right unit automatically. You always know who’s paid.') }}</p>
                </div>
                <div class="vcard">
                    <div class="ic">🔔</div>
                    <h3>{{ __('Reminders on autopilot') }}</h3>
                    <p>{{ __('Tenants get nudged before and after the due date — so you never have to.') }}</p>
                </div>
                <div class="vcard">
                    <div class="ic">🧾</div>
                    <h3>{{ __('Instant receipts') }}</h3>
                    <p>{{ __('The moment rent clears, a receipt goes out. No one asks for proof again.') }}</p>
                </div>
                <div class="vcard">
                    <div class="ic">📊</div>
                    <h3>{{ __('One clear dashboard') }}</h3>
                    <p>{{ __('Paid, pending and overdue across every property — in real time, on your phone.') }}</p>
                </div>
            </div>
        </div>
    </section>

    {{-- HOW IT WORKS --}}
    <section class="band" id="how">
        <div class="wrap">
            <div class="sec-head">
                <span class="eyebrow" style="color:var(--amber)">{{ __('How it works') }}</span>
                <h2>{{ __('Up and collecting — the same day.') }}</h2>
                <p>{{ __('No installers, no training course. Three steps and you’re running.') }}</p>
            </div>
            <div class="steps">
                <div class="step">
                    <div class="n">1</div>
                    <h3>{{ __('Add your property & tenants') }}</h3>
                    <p>{{ __('Pop in your building and units, add your tenants — each gets their own login.') }}</p>
                </div>
                <div class="step">
                    <div class="n">2</div>
                    <h3>{{ __('Turn on M-Pesa rent') }}</h3>
                    <p>{{ __('Set the rent and due date once. Invoices then generate on their own, every cycle.') }}</p>
                </div>
                <div class="step">
                    <div class="n">3</div>
                    <h3>{{ __('Relax — it runs itself') }}</h3>
                    <p>{{ __('Tenants pay from their phone, you get reconciled payments and receipts. Done.') }}</p>
                </div>
            </div>
        </div>
    </section>

    {{-- ACTUALLY FREE --}}
    <section>
        <div class="wrap free">
            <div>
                <span class="eyebrow">{{ __('Yes, it’s actually free') }}</span>
                <h2 style="font-size:clamp(23px,3.6vw,32px);margin:8px 0 18px">{{ __('Free is free — not a crippled demo.') }}</h2>
                <ul class="checks">
                    <li><span class="ck">✓</span><span><b>{{ __('Free for your first 30 units.') }}</b> {{ __('The real work — properties, tenants, rent, M-Pesa, receipts.') }}</span></li>
                    <li><span class="ck">✓</span><span><b>{{ __('No card, no commitment.') }}</b> {{ __('Start with a single building and grow only when it pays you to.') }}</span></li>
                    <li><span class="ck">✓</span><span><b>{{ __('Your tenants don’t learn anything new.') }}</b> {{ __('They pay the way they already pay everything — M-Pesa.') }}</span></li>
                </ul>
            </div>
            <div class="freebox">
                <div class="big">{{ __('Free') }}</div>
                <div class="cap">{{ __('for your first 30 units') }}</div>
                <a href="{{ $startUrl }}" class="btn btn--primary" style="background:#fff;color:var(--blue-deep);width:100%">{{ __('Create my free account') }} →</a>
            </div>
        </div>
    </section>

    {{-- CREDIBILITY --}}
    <section style="padding-top:8px">
        <div class="wrap">
            <div class="trust">
                <div class="t">
                    <div class="ic">🇰🇪</div>
                    <h3>{{ __('Built for Kenya') }}</h3>
                    <p>{{ __('M-Pesa-native, priced in shillings, made for how renting actually works here.') }}</p>
                </div>
                <div class="t">
                    <div class="ic">🔒</div>
                    <h3>{{ __('Your data is safe') }}</h3>
                    <p>{{ __('Your records and your money flow stay yours — private and secure.') }}</p>
                </div>
                <div class="t">
                    <div class="ic">⏱️</div>
                    <h3>{{ __('Minutes, not months') }}</h3>
                    <p>{{ __('Most owners are set up and collecting the day they sign in.') }}</p>
                </div>
            </div>
        </div>
    </section>

    {{-- FINAL CTA --}}
    <section class="final">
        <div class="wrap">
            <h2>{{ __('Stop chasing rent. Start this evening.') }}</h2>
            <p>{{ __('Your first 30 units are free. Set it up once, and let the rent come to you.') }}</p>
            <div class="cta-row" style="justify-content:center">
                <a href="{{ $startUrl }}" class="btn btn--primary">{{ __('Start free') }} →</a>
                <a href="{{ $signInUrl }}" class="btn btn--ghost">{{ __('I already have an account') }}</a>
            </div>
        </div>
    </section>

    <footer>
        <div class="wrap">
            <span>© {{ date('Y') }} {{ $appName }}. {{ __('Real Estate. Simplified. Connected.') }}</span>
            <span><a href="{{ $rentAppUrl }}">{{ __('Paying your rent? Go here →') }}</a></span>
        </div>
    </footer>

    {{-- Sticky Start-free (appears after the hero scrolls away) --}}
    <div class="sticky" id="stickyCta">
        <a href="{{ $startUrl }}" class="btn btn--primary">{{ __('Start free — 30 units free') }} →</a>
    </div>

    <script>
        (function(){
            var sticky = document.getElementById('stickyCta');
            var hero = document.querySelector('.hero');
            if(!sticky || !hero || !('IntersectionObserver' in window)) return;
            new IntersectionObserver(function(entries){
                // show the sticky CTA once the hero's primary CTA is out of view
                sticky.classList.toggle('show', !entries[0].isIntersecting);
            }, {rootMargin:'-120px 0px 0px 0px'}).observe(hero);
        })();
    </script>
</body>
</html>
