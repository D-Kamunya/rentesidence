@extends('saas.frontend.layouts.app')

@section('meta_description', __('Centresidence (also written Centre Residence or Center Residence) is Kenya\'s property management and rent-collection platform — collect rent on M-Pesa, automate reminders and run every unit from one dashboard. Real Estate. Simplified. Connected.'))
@section('meta_keywords', 'Centresidence, Centre Residence, Center Residence, property management Kenya, rent collection, collect rent M-Pesa, landlord software Kenya')
@section('canonical', route('about'))
@section('og_title', __('About Centresidence — Property Management & Rent Collection in Kenya'))
@section('og_description', __('Kenya\'s property management and rent-collection platform. Collect rent on M-Pesa, automate reminders, run every unit from one dashboard.'))
@section('og_image', asset('assets/images/about/building.jpg'))

@section('content')
@php $appName = getOption('app_name') ?: 'Centresidence'; $startUrl = \Illuminate\Support\Facades\Route::has('get-started') ? route('get-started') : url('/get-started'); @endphp
<div class="csab">
    <style>
        .csab{--paper:#FAF9F6;--paper2:#F2F0EA;--card:#FFFFFF;--line:#E6E1D8;--ink:#1B1E22;--ink-2:#3A3F47;--ink-3:#6B7280;
            --blue:#185FA5;--blue-2:#1c72c2;--blue-deep:#0F4A84;--amber:#E7A339;--green:#1D9E75;--mint:#E7F6F0;
            background:var(--paper);color:var(--ink-2);line-height:1.65}
        .csab *{box-sizing:border-box}
        .csab h1,.csab h2,.csab h3{color:var(--ink);line-height:1.14;letter-spacing:-.015em;font-weight:800}
        .csab a{text-decoration:none}
        .csab__wrap{max-width:1060px;margin:0 auto;padding-inline:20px}
        .csab__eyebrow{font-size:12px;font-weight:800;letter-spacing:.14em;text-transform:uppercase;color:var(--blue)}
        .csab__btn{display:inline-flex;align-items:center;gap:8px;font-weight:700;border-radius:13px;font-size:15px;padding:14px 26px;transition:transform .14s,background .14s}
        .csab__btn--primary{background:var(--blue);color:#fff;box-shadow:0 14px 30px -14px rgba(24,95,165,.85)}
        .csab__btn--primary:hover{background:var(--blue-deep);transform:translateY(-2px);color:#fff}
        /* Hero */
        .csab__hero{background:radial-gradient(1100px 340px at 12% -30%,rgba(24,95,165,.12),transparent 70%),radial-gradient(760px 300px at 100% 0%,rgba(231,163,57,.10),transparent 70%),var(--paper);padding:66px 0 40px}
        .csab__hero-grid{display:grid;grid-template-columns:1.05fr .95fr;gap:44px;align-items:center}
        .csab__hero h1{font-size:clamp(32px,5.2vw,50px);max-width:15ch;text-wrap:balance;margin:12px 0 0}
        .csab__hero p{font-size:clamp(16px,2.2vw,19px);max-width:52ch;margin-top:18px}
        .csab__hero-img{border-radius:20px;overflow:hidden;box-shadow:0 30px 60px -28px rgba(27,30,34,.5);aspect-ratio:16/11}
        .csab__hero-img img{width:100%;height:100%;object-fit:cover;display:block}
        .csab__hero .csab__btn{margin-top:28px}
        /* Bands */
        .csab__band{padding:62px 0}
        .csab__band--paper2{background:var(--paper2)}
        .csab__lead{font-size:clamp(24px,3.6vw,34px);max-width:22ch;text-wrap:balance}
        .csab__muted{font-size:16.5px;max-width:64ch;margin-top:14px;color:var(--ink-2)}
        .csab__cards{display:grid;grid-template-columns:repeat(auto-fit,minmax(230px,1fr));gap:18px;margin-top:34px}
        .csab__card{background:var(--card);border:1px solid var(--line);border-radius:16px;padding:24px;box-shadow:0 1px 3px rgba(16,24,40,.03)}
        .csab__card h3{font-size:18px;margin-bottom:8px}
        .csab__card p{font-size:14.5px;color:var(--ink-2)}
        /* Split feature */
        .csab__split{display:grid;grid-template-columns:1fr 1fr;gap:44px;align-items:center}
        .csab__split--rev .csab__split-media{order:2}
        .csab__split-media{border-radius:18px;overflow:hidden;box-shadow:0 24px 50px -26px rgba(27,30,34,.45);aspect-ratio:16/10}
        .csab__split-media img{width:100%;height:100%;object-fit:cover;display:block}
        .csab__split h2{font-size:clamp(22px,3.2vw,30px)}
        .csab__split p{margin-top:12px;font-size:16px}
        .csab__tag{font-weight:800;color:var(--blue);font-size:22px;margin-top:16px}
        /* CTA */
        .csab__cta{background:linear-gradient(135deg,var(--blue),var(--blue-deep));color:#fff;border-radius:24px;padding:48px 34px;text-align:center}
        .csab__cta h2{color:#fff;font-size:clamp(24px,4vw,34px)}
        .csab__cta p{color:#D7E6F6;margin:12px auto 26px;max-width:52ch}
        .csab__cta .csab__btn--primary{background:#fff;color:var(--blue-deep);box-shadow:0 14px 30px -14px rgba(0,0,0,.5)}
        .csab__cta .csab__btn--primary:hover{background:#fff;color:var(--blue-deep)}
        @media(max-width:820px){
            .csab__hero-grid,.csab__split{grid-template-columns:1fr}
            .csab__split--rev .csab__split-media{order:0}
        }
    </style>

    {{-- Hero --}}
    <section class="csab__hero">
        <div class="csab__wrap csab__hero-grid">
            <div>
                <p class="csab__eyebrow">{{ __('About us') }}</p>
                <h1>{{ __('Property management, simplified for Kenya.') }}</h1>
                <p>{{ __('Centresidence — sometimes written Centre Residence or Center Residence — is the property management and rent-collection platform built for Kenyan landlords. Collect rent on M-Pesa, automate reminders, issue instant receipts and run every unit from one dashboard.') }}</p>
                <a href="{{ $startUrl }}" class="csab__btn csab__btn--primary">{{ __('Start free — 30 units') }}</a>
            </div>
            <div class="csab__hero-img">
                <img src="{{ asset('assets/images/about/building.jpg') }}" alt="{{ __('Modern apartment buildings managed on Centresidence') }}" width="1280" height="760">
            </div>
        </div>
    </section>

    {{-- What we do --}}
    <section class="csab__band">
        <div class="csab__wrap">
            <h2 class="csab__lead">{{ __('What Centresidence does') }}</h2>
            <p class="csab__muted">{{ __('We turn the monthly grind of collecting rent into something automatic and quiet — so landlords get paid on time and spend their evenings on anything but admin.') }}</p>
            <div class="csab__cards">
                <div class="csab__card"><h3>{{ __('Rent collection') }}</h3><p>{{ __('Automatic invoices, rent paid directly on M-Pesa, and every payment reconciled to the right unit in real time.') }}</p></div>
                <div class="csab__card"><h3>{{ __('SMS reminders') }}</h3><p>{{ __('Polite, automatic SMS nudges before and after the due date — so late rent stops being a chasing problem.') }}</p></div>
                <div class="csab__card"><h3>{{ __('One dashboard') }}</h3><p>{{ __('Properties, units, tenants, deposits, receipts and arrears for every property, in one place, on any phone.') }}</p></div>
            </div>
        </div>
    </section>

    {{-- M-Pesa split --}}
    <section class="csab__band csab__band--paper2">
        <div class="csab__wrap csab__split">
            <div class="csab__split-media"><img src="{{ asset('assets/images/about/mpesa.jpg') }}" alt="{{ __('A tenant paying rent on M-Pesa from their phone') }}" width="1280" height="760" loading="lazy"></div>
            <div>
                <p class="csab__eyebrow">{{ __('Built for M-Pesa') }}</p>
                <h2>{{ __('Rent, on the rails your tenants already use') }}</h2>
                <p>{{ __('In Kenya, rent is paid on M-Pesa — so collection is built around it, not bolted on. Tenants pay their invoice from their phone, the payment matches itself to the right tenant and unit, and a receipt goes out instantly. No cash to bank, no squinting at confirmation messages.') }}</p>
            </div>
        </div>
    </section>

    {{-- Who it's for split --}}
    <section class="csab__band">
        <div class="csab__wrap csab__split csab__split--rev">
            <div class="csab__split-media"><img src="{{ asset('assets/images/about/landlord.jpg') }}" alt="{{ __('A landlord managing properties on Centresidence') }}" width="1280" height="760" loading="lazy"></div>
            <div>
                <p class="csab__eyebrow">{{ __('Who it is for') }}</p>
                <h2>{{ __('From one building to a whole portfolio') }}</h2>
                <p>{{ __('Whether you own a single plot or manage landlords across towns, Centresidence fits how you already work — M-Pesa, SMS and a phone in your pocket. Start with one property today; add the rest at your own pace.') }}</p>
            </div>
        </div>
    </section>

    {{-- The name --}}
    <section class="csab__band csab__band--paper2">
        <div class="csab__wrap">
            <p class="csab__eyebrow">{{ __('The name') }}</p>
            <h2 class="csab__lead">{{ __('Centresidence, Centre Residence, Center Residence') }}</h2>
            <p class="csab__muted">{{ __('However you spell it, it means the same thing: the centre of your residences — one calm place where every property, tenant and payment lives. Our promise sits in three words:') }}</p>
            <p class="csab__tag">{{ __('Real Estate. Simplified. Connected.') }}</p>
        </div>
    </section>

    {{-- CTA --}}
    <section class="csab__band">
        <div class="csab__wrap">
            <div class="csab__cta">
                <h2>{{ __('See the modern way on your own units') }}</h2>
                <p>{{ __('Join the Kenyan landlords collecting rent the simple way with Centresidence. Free for your first 30 units — no card, start with one building.') }}</p>
                <a href="{{ $startUrl }}" class="csab__btn csab__btn--primary">{{ __('Get started free') }}</a>
            </div>
        </div>
    </section>
</div>
@endsection
