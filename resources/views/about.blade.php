<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>{{ __('About Centresidence — Property Management & Rent Collection in Kenya') }} · {{ getOption('app_name') ?: 'Centresidence' }}</title>
    <meta name="description" content="{{ __('Centresidence (also written Centre Residence or Center Residence) is Kenya\'s property management and rent-collection platform — collect rent on M-Pesa, automate reminders and run every unit from one dashboard. Real Estate. Simplified. Connected.') }}">
    <meta name="keywords" content="Centresidence, Centre Residence, Center Residence, property management Kenya, rent collection, collect rent M-Pesa, landlord software Kenya">
    <link rel="canonical" href="{{ \Illuminate\Support\Facades\Route::has('about') ? route('about') : url('/about') }}">
    @include('common.layouts.pwa-meta')
    @include('common.partials.meta-pixel')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Sora:wght@700;800&family=Inter:wght@400;500;600;700&display=swap">
    @php
        $appName   = getOption('app_name') ?: 'Centresidence';
        $startUrl  = \Illuminate\Support\Facades\Route::has('get-started') ? route('get-started') : url('/get-started');
        $signInUrl = \Illuminate\Support\Facades\Route::has('login') ? route('login') : url('/login');
        $blogUrl   = \Illuminate\Support\Facades\Route::has('blog.index') ? route('blog.index') : url('/blog');
        $siteUrl   = \Illuminate\Support\Facades\Route::has('frontend') ? route('frontend') : url('/');
    @endphp
    <style>
        :root{--blue:#185FA5;--blue-2:#2E7FC7;--blue-deep:#0F4A84;--ink:#0F1E2E;--ink-2:#44515F;--ink-3:#7A8796;
            --amber:#E6A43B;--green:#1D9E75;--paper:#F6F8FB;--card:#FFFFFF;--line:#E4E9F0;--mint:#E7F6F0}
        *{box-sizing:border-box;margin:0;padding:0}
        html{scroll-behavior:smooth}
        body{font-family:"Inter",system-ui,-apple-system,"Segoe UI",Roboto,sans-serif;color:var(--ink-2);background:var(--paper);line-height:1.65;-webkit-font-smoothing:antialiased;overflow-x:hidden}
        h1,h2,h3{font-family:"Sora","Inter",system-ui,sans-serif;color:var(--ink);line-height:1.15;letter-spacing:-.015em}
        a{text-decoration:none;color:inherit}
        .wrap{max-width:1000px;margin:0 auto;padding-inline:20px}
        .btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;font-weight:700;border:none;border-radius:13px;cursor:pointer;transition:transform .14s,box-shadow .14s,background .14s;font-size:15px}
        .btn--primary{background:var(--blue);color:#fff;padding:14px 26px;box-shadow:0 14px 30px -14px rgba(24,95,165,.85)}
        .btn--primary:hover{background:var(--blue-deep);transform:translateY(-2px)}
        .btn--ghost{background:transparent;color:var(--ink);padding:13px 22px;border:1.5px solid var(--line)}
        .btn--ghost:hover{border-color:var(--blue);color:var(--blue)}
        .eyebrow{font-size:12px;font-weight:700;letter-spacing:.14em;text-transform:uppercase;color:var(--blue)}
        .bar{position:sticky;top:0;z-index:40;background:rgba(246,248,251,.82);backdrop-filter:saturate(180%) blur(10px);border-bottom:1px solid var(--line)}
        .bar .wrap{display:flex;align-items:center;justify-content:space-between;height:62px}
        .brand{display:flex;align-items:center;gap:10px;font-family:"Sora",sans-serif;font-weight:700;color:var(--ink);font-size:17px}
        .brand span{width:30px;height:30px;border-radius:9px;background:linear-gradient(135deg,var(--blue),var(--blue-2));display:grid;place-items:center;color:#fff;font-size:15px}
        .bar__cta{display:flex;align-items:center;gap:10px}
        .bar__link{font-weight:600;font-size:14px;color:var(--ink-2)}
        .hero{background:linear-gradient(180deg,#0F1E2E 0%,#13293D 100%);color:#fff;padding:72px 0 80px}
        .hero h1{color:#fff;font-size:clamp(32px,6vw,52px);font-weight:800;max-width:16ch;text-wrap:balance}
        .hero .eyebrow{color:#8FC0F0}
        .hero p{color:#C7D3E0;font-size:clamp(16px,2.4vw,19px);max-width:62ch;margin-top:18px}
        .hero .btn{margin-top:30px}
        section{padding:60px 0}
        .lead-h{font-size:clamp(24px,4vw,34px);font-weight:800;max-width:20ch;text-wrap:balance}
        .muted{color:var(--ink-2);font-size:16.5px;max-width:66ch;margin-top:14px}
        .grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:18px;margin-top:34px}
        .card{background:var(--card);border:1px solid var(--line);border-radius:16px;padding:24px;box-shadow:0 1px 3px rgba(16,24,40,.03)}
        .card h3{font-size:18px;margin-bottom:8px}
        .card p{font-size:14.5px;color:var(--ink-2)}
        .band{background:var(--mint)}
        .cta-band{background:linear-gradient(135deg,var(--blue),var(--blue-deep));color:#fff;border-radius:22px;padding:46px 34px;text-align:center;margin:0 auto}
        .cta-band h2{color:#fff;font-size:clamp(24px,4vw,34px);font-weight:800}
        .cta-band p{color:#D7E6F6;margin:12px auto 26px;max-width:52ch}
        .cta-band .btn--primary{background:#fff;color:var(--blue-deep);box-shadow:0 14px 30px -14px rgba(0,0,0,.5)}
        footer{border-top:1px solid var(--line);padding:30px 0;color:var(--ink-3);font-size:14px}
        footer .wrap{display:flex;flex-wrap:wrap;gap:16px;justify-content:space-between;align-items:center}
        footer a{color:var(--ink-2);font-weight:600}
        .tagline{font-family:"Sora",sans-serif;font-weight:700;color:var(--blue)}
    </style>
</head>
<body>
    <div class="bar">
        <div class="wrap">
            <a href="{{ $siteUrl }}" class="brand"><span>C</span>{{ $appName }}</a>
            <div class="bar__cta">
                <a href="{{ $signInUrl }}" class="bar__link">{{ __('Sign in') }}</a>
                <a href="{{ $startUrl }}" class="btn btn--primary" style="padding:10px 18px;font-size:14px">{{ __('Get started') }}</a>
            </div>
        </div>
    </div>

    <header class="hero">
        <div class="wrap">
            <p class="eyebrow">{{ __('About us') }}</p>
            <h1>{{ __('Property management, simplified for Kenya.') }}</h1>
            <p>{{ __('Centresidence — sometimes written Centre Residence or Center Residence — is the property management and rent-collection platform built for Kenyan landlords. Collect rent on M-Pesa, automate reminders, issue instant receipts and run every unit from one dashboard.') }}</p>
            <a href="{{ $startUrl }}" class="btn btn--primary">{{ __('Start free — 30 units') }}</a>
        </div>
    </header>

    <section>
        <div class="wrap">
            <h2 class="lead-h">{{ __('What Centresidence does') }}</h2>
            <p class="muted">{{ __('We turn the monthly grind of collecting rent into something automatic and quiet — so landlords get paid on time and spend their evenings on anything but admin.') }}</p>
            <div class="grid">
                <div class="card"><h3>{{ __('Rent collection') }}</h3><p>{{ __('Automatic invoices, rent paid directly on M-Pesa, and every payment reconciled to the right unit in real time.') }}</p></div>
                <div class="card"><h3>{{ __('SMS reminders') }}</h3><p>{{ __('Polite, automatic SMS nudges before and after the due date — so late rent stops being a chasing problem.') }}</p></div>
                <div class="card"><h3>{{ __('One dashboard') }}</h3><p>{{ __('Properties, units, tenants, deposits, receipts and arrears for every property, in one place, on any phone.') }}</p></div>
            </div>
        </div>
    </section>

    <section class="band">
        <div class="wrap">
            <h2 class="lead-h">{{ __('Who it is for') }}</h2>
            <p class="muted">{{ __('From a landlord with one building to agents managing portfolios across towns. If you collect rent in Kenya, Centresidence is built for how you already work — M-Pesa, SMS and a phone in your pocket.') }}</p>
        </div>
    </section>

    <section>
        <div class="wrap">
            <h2 class="lead-h">{{ __('The name') }}</h2>
            <p class="muted">{{ __('People find us as Centresidence, Centre Residence, Center Residence, or just Centre. Whichever way you spell it, it means the same thing: the centre of your residences — one calm place where every property, tenant and payment lives. Our promise sits in three words:') }}</p>
            <p style="margin-top:18px;font-size:22px"><span class="tagline">{{ __('Real Estate. Simplified. Connected.') }}</span></p>
        </div>
    </section>

    <section class="band">
        <div class="wrap">
            <h2 class="lead-h">{{ __('Why landlords choose us') }}</h2>
            <div class="grid">
                <div class="card"><h3>{{ __('Free to start') }}</h3><p>{{ __('Free for your first 30 units — no card, start with one building. You pay for optional extras, not the basics of getting paid.') }}</p></div>
                <div class="card"><h3>{{ __('Built for M-Pesa') }}</h3><p>{{ __('Not bolted on. Rent is collected and reconciled on the rails your tenants already use every day.') }}</p></div>
                <div class="card"><h3>{{ __('Set up the same day') }}</h3><p>{{ __('Add a property, add your tenants, switch on automatic invoices — and let the next rent cycle run itself.') }}</p></div>
            </div>
        </div>
    </section>

    <section>
        <div class="wrap">
            <div class="cta-band">
                <h2>{{ __('See the modern way on your own units') }}</h2>
                <p>{{ __('Join the Kenyan landlords collecting rent the simple way with Centresidence.') }}</p>
                <a href="{{ $startUrl }}" class="btn btn--primary">{{ __('Get started free') }}</a>
            </div>
        </div>
    </section>

    <footer>
        <div class="wrap">
            <span>© {{ date('Y') }} {{ $appName }}. {{ __('Real Estate. Simplified. Connected.') }}</span>
            <span style="display:flex;gap:18px;flex-wrap:wrap">
                <a href="{{ $siteUrl }}">{{ __('Home') }}</a>
                <a href="{{ $blogUrl }}">{{ __('Blog') }}</a>
                <a href="{{ $startUrl }}">{{ __('Get started') }}</a>
            </span>
        </div>
    </footer>
</body>
</html>
