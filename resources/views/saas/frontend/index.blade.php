@extends('saas.frontend.layouts.app')
@section('content')

{{--
    Centresidence public home page. Redesigned 2026-08 onto the CS brand language:
    warm-marble light body with committed-dark brand bands (hero + finance), cs-blue +
    warm-amber accents. Positioning is deliberately classic-PMS with the finance/
    infrastructure moat kept as an outcome, never a mechanism. No prices (competitors
    undercut); the free tier is the loud hook. All CSS is namespaced `csh-` so it never
    collides with Bootstrap / the frontend theme. Section IDs (features / howitworks /
    contact-us) match the layout menu anchors.
--}}

<style>
  /* Declare the page already uses a dark scheme so a browser's "auto dark mode"
     doesn't force-invert backgrounds (which would turn the transparent nav black). */
  html{color-scheme:dark}
  .csh{
    /* Committed DARK-premium palette (brand surface — landing/marketing). */
    --paper:#0E1218; --paper-2:#141922; --card:#161C26;
    --ink:#12161D; --ink-2:#1B212C; --hero-dark:#12161D;
    --stone-900:#EDEAE3; --stone-700:#C4C0B7; --stone-500:#9A958A; --stone-400:#7C776C;
    --line:#242B36;
    --cs-blue:#185FA5; --cs-blue-2:#1c72c2; --cs-blue-deep:#0F4A84; --cs-blue-tint:#12283F;
    --amber:#E7A339; --amber-2:#f0af49; --amber-soft:#F6E4C4; --amber-tint:#241d10;
    --shadow:0 20px 46px -22px rgba(0,0,0,.6);
    --shadow-sm:0 10px 26px -16px rgba(0,0,0,.55);
    --maxw:1160px;
    --serif:Georgia,'Iowan Old Style','Times New Roman',serif;
    --sans:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;
    font-family:var(--sans);color:var(--stone-900);line-height:1.6;background:var(--paper)}
  .csh *{box-sizing:border-box}
  .csh .csh-wrap{max-width:var(--maxw);margin:0 auto;padding:0 24px}
  .csh h1,.csh h2,.csh h3{margin:0;line-height:1.12;text-wrap:balance;letter-spacing:-.01em}
  .csh p{margin:0}
  .csh a{text-decoration:none}
  .csh-eyebrow{font-size:12.5px;font-weight:700;letter-spacing:.16em;text-transform:uppercase;color:var(--cs-blue)}

  .csh-btn{display:inline-flex;align-items:center;gap:9px;font-weight:650;font-size:15.5px;
    padding:13px 22px;border-radius:11px;border:1px solid transparent;cursor:pointer;transition:.18s;text-decoration:none}
  .csh-btn svg{width:17px;height:17px}
  .csh-btn--amber{background:var(--amber);color:#20160A !important;box-shadow:0 10px 24px -12px rgba(231,163,57,.7)}
  .csh-btn--amber:hover{transform:translateY(-2px);background:var(--amber-2);color:#20160A !important}
  .csh-btn--blue{background:var(--cs-blue);color:#fff !important;box-shadow:0 10px 24px -12px rgba(24,95,165,.7)}
  .csh-btn--blue:hover{transform:translateY(-2px);background:var(--cs-blue-2);color:#fff !important}
  .csh-btn--ghost-light{background:transparent;color:#EDE7DC !important;border-color:rgba(237,231,220,.28)}
  .csh-btn--ghost-light:hover{border-color:rgba(237,231,220,.6);background:rgba(255,255,255,.05);color:#fff !important}

  /* hero */
  .csh-hero{position:relative;overflow:hidden;
    background:
      radial-gradient(1000px 520px at 82% -10%, rgba(231,163,57,.16), transparent 60%),
      radial-gradient(900px 600px at 6% 14%, rgba(24,95,165,.30), transparent 55%),
      linear-gradient(180deg,#12161D 0%,#151B24 100%);
    color:#EDE7DC}
  .csh-hero__photo{position:absolute;right:0;bottom:0;width:64%;height:100%;z-index:1;pointer-events:none}
  .csh-hero__photo .csh-shot{position:absolute;inset:0;z-index:1;background-size:cover;background-position:center;
    animation:cshCross 16s ease-in-out infinite}
  .csh-hero__photo .csh-shot--2{animation-delay:8s;opacity:0}
  @keyframes cshCross{0%{opacity:1}44%{opacity:1}50%{opacity:0}94%{opacity:0}100%{opacity:1}}
  .csh-hero__photo::before{content:"";position:absolute;inset:0;z-index:2;
    background:linear-gradient(90deg,var(--hero-dark) 6%,rgba(18,22,29,.5) 38%,transparent 74%)}
  .csh-hero__photo::after{content:"";position:absolute;inset:0;z-index:2;
    background:linear-gradient(0deg,rgba(18,22,29,.62),transparent 46%)}
  @media (prefers-reduced-motion:reduce){.csh-hero__photo .csh-shot{animation:none}.csh-hero__photo .csh-shot--2{opacity:0}}
  .csh-hero .csh-wrap{position:relative;z-index:3;padding:150px 24px 110px}
  .csh-hero__col{max-width:600px}
  .csh-hero__eye{color:var(--amber)}
  .csh-hero h1{font-family:var(--serif);font-weight:600;letter-spacing:-.015em;
    font-size:clamp(40px,6.2vw,72px);margin:20px 0 0;color:#EDE7DC}
  .csh-hero h1 .csh-con{color:#5AA0E0;font-style:italic}
  .csh-hero__sub{margin-top:22px;max-width:540px;font-size:19px;line-height:1.55;color:#C7C0B4}
  .csh-hero__cta{display:flex;gap:14px;flex-wrap:wrap;margin-top:34px}
  .csh-chips{display:flex;gap:10px;flex-wrap:wrap;margin-top:40px}
  .csh-chip{display:inline-flex;align-items:center;gap:8px;font-size:13.5px;font-weight:600;color:#D9D2C6;
    background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.11);padding:8px 14px;border-radius:99px}
  .csh-chip .csh-dot{width:7px;height:7px;border-radius:50%;background:var(--amber)}
  @media(max-width:820px){.csh-hero__photo{width:100%;height:46%;opacity:.5}
    .csh-hero__photo::before{background:linear-gradient(90deg,var(--hero-dark) 10%,transparent 90%)}
    .csh-hero__sub{max-width:none}}

  .csh-band{padding:82px 0;background:var(--paper)}
  .csh-band--paper2{background:var(--paper-2)}
  .csh-sec-head{max-width:680px}
  .csh-sec-head h2{font-size:clamp(27px,3.4vw,40px);font-weight:750;margin-top:12px;color:var(--stone-900)}
  .csh-sec-head p{margin-top:14px;color:var(--stone-500);font-size:18px}
  .csh-center{margin-left:auto;margin-right:auto;text-align:center}

  /* free band */
  .csh-free{background:
      radial-gradient(600px 260px at 88% -40%, rgba(231,163,57,.35), transparent 60%),
      linear-gradient(135deg,var(--amber-tint), var(--paper-2));
    border:1px solid var(--line);border-radius:22px;padding:52px 48px;box-shadow:var(--shadow-sm);
    display:grid;grid-template-columns:1.3fr .7fr;gap:34px;align-items:center}
  .csh-free h2{font-size:clamp(26px,3.4vw,40px);font-weight:800;color:var(--stone-900)}
  .csh-free h2 em{font-style:normal;color:var(--cs-blue)}
  .csh-free p{margin-top:14px;color:var(--stone-700);font-size:18px;max-width:560px}
  .csh-free .csh-cta{text-align:right}
  .csh-free .csh-badge{display:inline-flex;align-items:center;gap:8px;background:var(--cs-blue);color:#fff;
    font-weight:700;font-size:13px;letter-spacing:.04em;text-transform:uppercase;padding:8px 15px;border-radius:99px}
  @media(max-width:820px){.csh-free{grid-template-columns:1fr;padding:36px 26px}.csh-free .csh-cta{text-align:left}}

  /* features */
  .csh-grid{display:grid;gap:20px;margin-top:46px;grid-template-columns:repeat(3,1fr)}
  @media(max-width:900px){.csh-grid{grid-template-columns:repeat(2,1fr)}}
  @media(max-width:560px){.csh-grid{grid-template-columns:1fr}}
  .csh-fcard{background:var(--card);border:1px solid var(--line);border-radius:16px;padding:26px 24px;
    box-shadow:var(--shadow-sm);transition:.2s}
  .csh-fcard:hover{transform:translateY(-4px);border-color:color-mix(in srgb,var(--cs-blue) 40%,var(--line))}
  .csh-ficon{width:46px;height:46px;border-radius:12px;display:grid;place-items:center;
    background:var(--cs-blue-tint);color:var(--cs-blue);margin-bottom:16px}
  .csh-ficon svg{width:23px;height:23px}
  .csh-fcard h3{font-size:18.5px;font-weight:700;color:var(--stone-900)}
  .csh-rule{width:26px;height:3px;border-radius:2px;background:var(--amber);margin:12px 0}
  .csh-fcard p{color:var(--stone-500);font-size:15.3px}

  /* split */
  .csh-split{display:grid;grid-template-columns:1.05fr .95fr;gap:54px;align-items:center}
  @media(max-width:860px){.csh-split{grid-template-columns:1fr;gap:30px}}
  .csh-split__media{border-radius:18px;overflow:hidden;box-shadow:var(--shadow);border:1px solid var(--line);
    aspect-ratio:4/3;position:relative}
  .csh-split__img{position:absolute;inset:0;background-size:cover;background-position:center}
  .csh-split__cap{position:absolute;left:14px;bottom:12px;z-index:2;color:rgba(255,255,255,.92);
    font-size:12px;font-weight:600;background:rgba(0,0,0,.4);padding:5px 11px;border-radius:99px}
  .csh-split h2{font-size:clamp(26px,3.2vw,38px);font-weight:750;color:var(--stone-900)}
  .csh-split p{margin-top:16px;color:var(--stone-500);font-size:17.5px}

  /* how it works */
  .csh-hiw{display:grid;grid-template-columns:1fr 1fr;gap:26px;margin-top:46px}
  @media(max-width:820px){.csh-hiw{grid-template-columns:1fr}}
  .csh-track{background:var(--card);border:1px solid var(--line);border-radius:18px;padding:28px;box-shadow:var(--shadow-sm)}
  .csh-track h3{font-size:15px;letter-spacing:.04em;text-transform:uppercase;color:var(--cs-blue);font-weight:750}
  .csh-track.csh-amberhead h3{color:#C6851E}
  .csh-steps{margin-top:20px;display:flex;flex-direction:column;gap:2px}
  .csh-step{display:flex;gap:16px;padding:14px 0;border-top:1px solid var(--line)}
  .csh-step:first-child{border-top:0}
  .csh-step .csh-num{flex:0 0 30px;height:30px;border-radius:9px;display:grid;place-items:center;font-weight:750;
    font-size:14px;background:var(--cs-blue-tint);color:var(--cs-blue)}
  .csh-track.csh-amberhead .csh-num{background:var(--amber-soft);color:#9A6512}
  .csh-step b{display:block;color:var(--stone-900);font-size:16px}
  .csh-step span{color:var(--stone-500);font-size:14.5px}

  /* who */
  .csh-who{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-top:44px}
  @media(max-width:900px){.csh-who{grid-template-columns:repeat(2,1fr)}}
  @media(max-width:520px){.csh-who{grid-template-columns:1fr}}
  .csh-wcard{background:var(--card);border:1px solid var(--line);border-radius:13px;padding:18px;
    display:flex;align-items:center;gap:13px}
  .csh-wcard svg{width:22px;height:22px;color:var(--cs-blue);flex:0 0 auto}
  .csh-wcard b{font-size:15px;font-weight:650;color:var(--stone-900)}

  /* grow / finance band */
  .csh-grow{background:
      radial-gradient(680px 300px at 88% -30%, rgba(231,163,57,.30), transparent 60%),
      linear-gradient(135deg,var(--cs-blue) 0%,var(--cs-blue-deep) 100%);
    color:#fff;border-radius:22px;padding:52px 50px;box-shadow:var(--shadow);position:relative;overflow:hidden;
    display:grid;grid-template-columns:1.12fr .88fr;gap:44px;align-items:center}
  .csh-grow .csh-eyebrow{color:var(--amber)}
  .csh-grow h2{font-size:clamp(27px,3.4vw,40px);font-weight:780;margin-top:12px;color:#fff}
  .csh-grow p{margin-top:16px;color:rgba(255,255,255,.88);font-size:18px}
  .csh-grow .csh-btn{margin-top:28px}
  .csh-grow__img{align-self:stretch;min-height:320px;border-radius:16px;background-size:cover;background-position:center;
    box-shadow:0 22px 44px -18px rgba(0,0,0,.55);border:1px solid rgba(255,255,255,.16)}
  @media(max-width:820px){.csh-grow{grid-template-columns:1fr;padding:42px 30px}.csh-grow__img{min-height:220px}}

  /* partners */
  .csh-part{display:grid;grid-template-columns:repeat(3,1fr);gap:20px;margin-top:44px}
  @media(max-width:820px){.csh-part{grid-template-columns:1fr}}
  .csh-pcard{background:var(--card);border:1px solid var(--line);border-radius:16px;padding:28px;box-shadow:var(--shadow-sm);
    display:flex;flex-direction:column}
  .csh-pcard .csh-picon{width:44px;height:44px;border-radius:12px;display:grid;place-items:center;margin-bottom:16px}
  .csh-pcard .csh-picon svg{width:22px;height:22px}
  .csh-pcard.csh-p-blue .csh-picon{background:var(--cs-blue-tint);color:var(--cs-blue)}
  .csh-pcard.csh-p-amber .csh-picon{background:var(--amber-soft);color:#9A6512}
  .csh-pcard.csh-p-soft{border-style:dashed;opacity:.9}
  .csh-pcard.csh-p-soft .csh-picon{background:var(--paper);color:var(--stone-400)}
  .csh-pcard h3{font-size:19px;font-weight:750;color:var(--stone-900)}
  .csh-pcard p{margin-top:10px;color:var(--stone-500);font-size:15.3px;flex:1}
  .csh-lnk{margin-top:18px;font-weight:700;font-size:14.5px;color:var(--cs-blue) !important;display:inline-flex;align-items:center;gap:7px}
  .csh-pcard.csh-p-amber .csh-lnk{color:#B4780F !important}
  .csh-pcard.csh-p-soft .csh-lnk{color:var(--stone-400) !important}

  /* trust */
  .csh-trust{display:grid;grid-template-columns:repeat(3,1fr);gap:20px;margin-top:44px}
  @media(max-width:760px){.csh-trust{grid-template-columns:1fr}}
  .csh-tcard{background:var(--card);border:1px solid var(--line);border-radius:16px;padding:26px;box-shadow:var(--shadow-sm)}
  .csh-tcard .csh-k{font-family:var(--serif);font-size:30px;color:var(--cs-blue);font-weight:600}
  .csh-tcard b{display:block;margin-top:8px;color:var(--stone-900);font-size:16.5px}
  .csh-tcard p{margin-top:6px;color:var(--stone-500);font-size:14.8px}
  .csh-note{margin-top:26px;font-size:13.5px;color:var(--stone-400);text-align:center}

  /* faq */
  .csh-faq{max-width:820px;margin:44px auto 0;display:flex;flex-direction:column;gap:12px}
  .csh-qa{background:var(--card);border:1px solid var(--line);border-radius:14px;padding:22px 24px}
  .csh-qa b{color:var(--stone-900);font-size:16.5px;display:flex;gap:10px;align-items:flex-start}
  .csh-qa b .csh-q{color:var(--cs-blue);font-weight:800}
  .csh-qa p{margin-top:8px;color:var(--stone-500);font-size:15px;padding-left:24px}

  /* contact */
  .csh-contact{display:grid;grid-template-columns:1fr 1fr;gap:44px;align-items:center}
  @media(max-width:820px){.csh-contact{grid-template-columns:1fr;gap:28px}}
  .csh-contact h2{font-size:clamp(26px,3.4vw,40px);font-weight:750;margin-top:12px;color:var(--stone-900)}
  .csh-contact .csh-lead{margin-top:16px;color:var(--stone-500);font-size:18px}
  .csh-form{background:var(--card);border:1px solid var(--line);border-radius:18px;padding:30px;box-shadow:var(--shadow)}
  .csh-form h3{font-size:22px;font-weight:750;color:var(--stone-900)}
  .csh-form p{margin-top:6px;color:var(--stone-500);font-size:15px}
  .csh-frow{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-top:16px}
  .csh-form input,.csh-form textarea{width:100%;font-family:inherit;font-size:15px;padding:12px 14px;border-radius:10px;
    border:1px solid var(--line);background:var(--paper);color:var(--stone-900);margin-top:12px}
  .csh-form input:focus,.csh-form textarea:focus{outline:2px solid var(--cs-blue);outline-offset:1px;border-color:transparent}
  .csh-frow input{margin-top:0}
  /* NOTE: this element is `.csh-wrap.csh-cta-final`, and `.csh .csh-wrap{padding:0 24px}`
     (2 classes) outranks a bare `.csh-cta-final` (1 class), zeroing the vertical padding.
     Scope to `.csh .csh-cta-final` (2 classes, later in source) so it wins. */
  .csh .csh-cta-final{text-align:center;padding:118px 24px 108px}
  .csh-cta-final h2{font-family:var(--serif);font-weight:600;font-size:clamp(28px,4vw,46px);color:var(--stone-900)}
  .csh-cta-final p{margin-top:14px;color:var(--stone-500);font-size:18px}
</style>

<div class="csh">

  {{-- HERO --}}
  <section class="csh-hero">
    <div class="csh-hero__photo">
      <div class="csh-shot csh-shot--1" style="background-image:url('{{ asset('assets/images/frontend/hero-dusk.jpg') }}')"></div>
      <div class="csh-shot csh-shot--2" style="background-image:url('{{ asset('assets/images/frontend/hero-interior.jpg') }}')"></div>
    </div>
    <div class="csh-wrap">
      <div class="csh-hero__col">
        <span class="csh-eyebrow csh-hero__eye">{{ __('Property management, the modern way') }}</span>
        <h1>Real Estate. Simplified.<br><span class="csh-con">Connected.</span></h1>
        <p class="csh-hero__sub">{{ __('Run every property from one place. Automated rent, happier tenants, fewer empty units, and every shilling accounted for. Built for how Kenya rents.') }}</p>
        <div class="csh-hero__cta">
          <a href="#contact-us" data-intent="trial" class="csh-btn csh-btn--amber">{{ __('Get started free') }}
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a>
          <a href="{{ route('house.hunt') }}" class="csh-btn csh-btn--ghost-light">{{ __('Browse vacant homes') }}</a>
        </div>
        <div class="csh-chips">
          <span class="csh-chip"><span class="csh-dot"></span>{{ __('Free to start') }}</span>
          <span class="csh-chip"><span class="csh-dot"></span>{{ __('M-Pesa payments') }}</span>
          <span class="csh-chip"><span class="csh-dot"></span>{{ __('Automatic SMS reminders') }}</span>
          <span class="csh-chip"><span class="csh-dot"></span>{{ __('Access anywhere') }}</span>
        </div>
      </div>
    </div>
  </section>

  {{-- FREE TO START --}}
  <section class="csh-band" id="free">
    <div class="csh-wrap">
      <div class="csh-free">
        <div>
          <span class="csh-badge">{{ __('Free to start') }}</span>
          <h2 style="margin-top:16px">{{ __('Start for') }} <em>{{ __('free') }}</em>. {{ __('Grow when you are ready.') }}</h2>
          <p>{{ __('Most platforms charge from the very first day. Centresidence does not. Create your account, add your properties, and run the essentials at no cost. You only pay when you choose to do more.') }}</p>
        </div>
        <div class="csh-cta">
          <a href="#contact-us" data-intent="trial" class="csh-btn csh-btn--blue">{{ __('Create your free account') }}
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a>
        </div>
      </div>
    </div>
  </section>

  {{-- FEATURES --}}
  <section class="csh-band csh-band--paper2" id="features">
    <div class="csh-wrap">
      <div class="csh-sec-head csh-center">
        <span class="csh-eyebrow">{{ __('What you get') }}</span>
        <h2>{{ __('Everything that runs a rental, in one place') }}</h2>
        <p>{{ __('Less admin, fewer phone calls, and a clear view of your money. Centresidence handles the busywork so you can focus on your properties.') }}</p>
      </div>
      <div class="csh-grid">
        <div class="csh-fcard">
          <div class="csh-ficon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="6" width="20" height="12" rx="2"/><circle cx="12" cy="12" r="2.5"/></svg></div>
          <h3>{{ __('Rent that collects itself') }}</h3><div class="csh-rule"></div>
          <p>{{ __('Invoices, reminders and receipts go out automatically. Get paid faster and spend less time chasing.') }}</p>
        </div>
        <div class="csh-fcard">
          <div class="csh-ficon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M3 11l9-8 9 8"/><path d="M5 10v10h14V10"/><circle cx="11" cy="14" r="2.5"/><path d="M17 20l-2.2-2.2"/></svg></div>
          <h3>{{ __('Fewer empty units') }}</h3><div class="csh-rule"></div>
          <p>{{ __('The moment a unit falls vacant it lists to your public house hunt page, so it fills faster.') }}</p>
        </div>
        <div class="csh-fcard">
          <div class="csh-ficon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M14.7 6.3a4 4 0 00-5.4 5.4L3 18v3h3l6.3-6.3a4 4 0 005.4-5.4l-2.3 2.3-2.4-.6-.6-2.4z"/></svg></div>
          <h3>{{ __('Maintenance without the calls') }}</h3><div class="csh-rule"></div>
          <p>{{ __('Tenants raise tickets with photos from their own account. You track every request through to done.') }}</p>
        </div>
        <div class="csh-fcard">
          <div class="csh-ficon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3l7 3v5c0 4.5-3 8-7 10-4-2-7-5.5-7-10V6l7-3z"/><path d="M9 12l2 2 4-4"/></svg></div>
          <h3>{{ __('Confident tenant decisions') }}</h3><div class="csh-rule"></div>
          <p>{{ __('Screen applicants and keep a clean, organised record for every tenancy, so you decide with confidence.') }}</p>
        </div>
        <div class="csh-fcard">
          <div class="csh-ficon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19V5"/><path d="M4 19h16"/><path d="M8 15l3.5-4 3 2.5L20 7"/></svg></div>
          <h3>{{ __('All the money, one view') }}</h3><div class="csh-rule"></div>
          <p>{{ __('Rent, deposits and marketplace sales, tracked and reconciled in real time. No spreadsheets.') }}</p>
        </div>
        <div class="csh-fcard">
          <div class="csh-ficon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M3 17l6-6 4 4 8-8"/><path d="M14 7h7v7"/></svg></div>
          <h3>{{ __('Built to grow with you') }}</h3><div class="csh-rule"></div>
          <p>{{ __('Clear reports and insights today, with the headroom to expand your portfolio when the time is right.') }}</p>
        </div>
      </div>
    </div>
  </section>

  {{-- HOUSE HUNT --}}
  <section class="csh-band" id="househunt">
    <div class="csh-wrap csh-split">
      <div class="csh-split__media">
        <div class="csh-split__img" style="background-image:url('{{ asset('assets/images/frontend/house-hunt.jpg') }}')"></div>
        <span class="csh-split__cap">{{ __('Live vacant listings') }}</span>
      </div>
      <div>
        <span class="csh-eyebrow">{{ __('For tenants and house hunters') }}</span>
        <h2>{{ __('Find your next home') }}</h2>
        <p>{{ __('Browse available units in real time and connect straight with the landlord. No middlemen, no guesswork. Every vacant unit on Centresidence is listed the moment it is free.') }}</p>
        <a href="{{ route('house.hunt') }}" class="csh-btn csh-btn--blue" style="margin-top:24px">{{ __('Browse vacant properties') }}
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a>
      </div>
    </div>
  </section>

  {{-- HOW IT WORKS --}}
  <section class="csh-band csh-band--paper2" id="howitworks">
    <div class="csh-wrap">
      <div class="csh-sec-head csh-center">
        <span class="csh-eyebrow">{{ __('How it works') }}</span>
        <h2>{{ __('Two journeys, one platform') }}</h2>
        <p>{{ __('Whether you own the property or you are looking for one, getting started takes minutes.') }}</p>
      </div>
      <div class="csh-hiw">
        <div class="csh-track">
          <h3>{{ __('For property owners') }}</h3>
          <div class="csh-steps">
            <div class="csh-step"><span class="csh-num">1</span><div><b>{{ __('List your properties') }}</b><span>{{ __('Add units, rent and terms in a few clicks.') }}</span></div></div>
            <div class="csh-step"><span class="csh-num">2</span><div><b>{{ __('Invite your tenants') }}</b><span>{{ __('They get a login and their own account instantly.') }}</span></div></div>
            <div class="csh-step"><span class="csh-num">3</span><div><b>{{ __('Collect rent automatically') }}</b><span>{{ __('Invoices and reminders send themselves, and payments reconcile.') }}</span></div></div>
            <div class="csh-step"><span class="csh-num">4</span><div><b>{{ __('Grow your portfolio') }}</b><span>{{ __('Track performance and expand when the time is right.') }}</span></div></div>
          </div>
        </div>
        <div class="csh-track csh-amberhead">
          <h3>{{ __('For tenants') }}</h3>
          <div class="csh-steps">
            <div class="csh-step"><span class="csh-num">1</span><div><b>{{ __('Search rentals') }}</b><span>{{ __('Find available units near you in real time.') }}</span></div></div>
            <div class="csh-step"><span class="csh-num">2</span><div><b>{{ __('Apply online') }}</b><span>{{ __('Submit your application in a few clicks.') }}</span></div></div>
            <div class="csh-step"><span class="csh-num">3</span><div><b>{{ __('Get approved') }}</b><span>{{ __('Landlords review and approve quickly.') }}</span></div></div>
            <div class="csh-step"><span class="csh-num">4</span><div><b>{{ __('Move in and pay easily') }}</b><span>{{ __('Pay rent, raise issues and get receipts from one account.') }}</span></div></div>
          </div>
        </div>
      </div>
    </div>
  </section>

  {{-- WHO IT'S FOR --}}
  <section class="csh-band" id="who">
    <div class="csh-wrap">
      <div class="csh-sec-head csh-center">
        <span class="csh-eyebrow">{{ __('Who it is for') }}</span>
        <h2>{{ __('One platform, many kinds of property') }}</h2>
      </div>
      <div class="csh-who">
        <div class="csh-wcard"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"><path d="M3 21V7l7-4 7 4v14"/><path d="M3 21h18M9 21v-5h4v5"/></svg><b>{{ __('Property owners') }}</b></div>
        <div class="csh-wcard"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"><circle cx="12" cy="8" r="3.2"/><path d="M5 21c0-3.9 3.1-7 7-7s7 3.1 7 7"/></svg><b>{{ __('Property managers') }}</b></div>
        <div class="csh-wcard"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"><path d="M4 21V5l8-2v18M12 21V9l6 2v10M2 21h20"/></svg><b>{{ __('Real estate agencies') }}</b></div>
        <div class="csh-wcard"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"><path d="M3 21V8l6-3v16M9 21V4l8 3v14M3 21h18M13 10h1M13 14h1"/></svg><b>{{ __('Developers') }}</b></div>
        <div class="csh-wcard"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"><path d="M22 10L12 5 2 10l10 5 10-5z"/><path d="M6 12v5c0 1 2.7 2.5 6 2.5S18 18 18 17v-5"/></svg><b>{{ __('Student housing') }}</b></div>
        <div class="csh-wcard"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"><path d="M4 21V6a2 2 0 012-2h9a2 2 0 012 2v15M8 8h1M8 12h1M13 8h1M13 12h1M17 21V11h3v10"/></svg><b>{{ __('Serviced apartments') }}</b></div>
        <div class="csh-wcard"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"><path d="M3 9l1-5h16l1 5M4 9v11h16V9M9 20v-6h6v6"/></svg><b>{{ __('Malls and retail') }}</b></div>
        <div class="csh-wcard"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"><path d="M3 21V10l9-6 9 6v11M3 21h18M9 21v-6h6v6"/></svg><b>{{ __('Estates and HOAs') }}</b></div>
      </div>
    </div>
  </section>

  {{-- BEYOND MANAGEMENT (finance, reserved) --}}
  <section class="csh-band csh-band--paper2">
    <div class="csh-wrap">
      <div class="csh-grow">
        <div class="csh-grow__text">
          <span class="csh-eyebrow">{{ __('More than management') }}</span>
          <h2>{{ __('Your track record is worth more than you think') }}</h2>
          <p>{{ __('Every rent you collect and every unit you keep full builds proof of a business worth backing. Centresidence turns that track record into access that has always been out of reach for property owners. The foundation is already built. This is only the beginning of what it opens up.') }}</p>
          <a href="#contact-us" class="csh-btn csh-btn--amber">{{ __('See where this is going') }}
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a>
        </div>
        <div class="csh-grow__img" style="background-image:url('{{ asset('assets/images/frontend/finance.jpg') }}')"></div>
      </div>
    </div>
  </section>

  {{-- WORK WITH US --}}
  <section class="csh-band" id="partners">
    <div class="csh-wrap">
      <div class="csh-sec-head csh-center">
        <span class="csh-eyebrow">{{ __('Work with us') }}</span>
        <h2>{{ __('Grow with Centresidence') }}</h2>
        <p>{{ __('We are building this with partners who move the market. There is a place for you.') }}</p>
      </div>
      <div class="csh-part">
        <div class="csh-pcard csh-p-blue">
          <div class="csh-picon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18M4 21V10l8-5 8 5v11M9 21v-6h6v6"/><path d="M4 10h16"/></svg></div>
          <h3>{{ __('Financial institutions') }}</h3>
          <p>{{ __('Reach and serve property owners through a platform they use every day. Let us build products that move the market together.') }}</p>
          <a href="#contact-us" data-intent="partner" class="csh-lnk">{{ __('Partner with us') }}
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" width="14" height="14"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a>
        </div>
        <div class="csh-pcard csh-p-amber">
          <div class="csh-picon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="8" r="3"/><path d="M2 21c0-3.5 3-6 7-6s7 2.5 7 6"/><path d="M17 8h5M19.5 5.5v5"/></svg></div>
          <h3>{{ __('Affiliates and agents') }}</h3>
          <p>{{ __('Earn by bringing property owners onboard. Simple tools, real training, and commissions you can count on.') }}</p>
          <a href="#contact-us" data-intent="partner" class="csh-lnk">{{ __('Join the program') }}
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" width="14" height="14"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a>
        </div>
        <div class="csh-pcard csh-p-soft">
          <div class="csh-picon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 8v8M8 12h8"/></svg></div>
          <h3>{{ __('More ways, soon') }}</h3>
          <p>{{ __('We are opening new ways to build with Centresidence as the platform grows. Tell us where you fit.') }}</p>
          <a href="#contact-us" class="csh-lnk">{{ __('Start a conversation') }}
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" width="14" height="14"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a>
        </div>
      </div>
    </div>
  </section>

  {{-- TRUST --}}
  <section class="csh-band csh-band--paper2">
    <div class="csh-wrap">
      <div class="csh-sec-head csh-center">
        <span class="csh-eyebrow">{{ __('Why landlords switch') }}</span>
        <h2>{{ __('Manage less. Earn more.') }}</h2>
      </div>
      <div class="csh-trust">
        <div class="csh-tcard"><div class="csh-k">01</div><b>{{ __('Stop chasing rent') }}</b><p>{{ __('Automatic invoices, reminders and M-Pesa payments mean rent arrives without the phone calls.') }}</p></div>
        <div class="csh-tcard"><div class="csh-k">02</div><b>{{ __('Stop losing track') }}</b><p>{{ __('Every payment, deposit and receipt in one reconciled ledger you can trust.') }}</p></div>
        <div class="csh-tcard"><div class="csh-k">03</div><b>{{ __('Stop leaving units empty') }}</b><p>{{ __('Vacancies list themselves and fill faster from your public house hunt page.') }}</p></div>
      </div>
      @if(!empty($testimonials) && count($testimonials))
        <p class="csh-note">{{ __('Trusted by a growing community of landlords across Kenya.') }}</p>
      @endif
    </div>
  </section>

  {{-- FAQ --}}
  <section class="csh-band" id="faq">
    <div class="csh-wrap">
      <div class="csh-sec-head csh-center">
        <span class="csh-eyebrow">{{ __('Good to know') }}</span>
        <h2>{{ __('Questions, answered') }}</h2>
      </div>
      <div class="csh-faq">
        @forelse(($faqs ?? []) as $faq)
          <div class="csh-qa"><b><span class="csh-q">Q.</span>{{ $faq->question }}</b><p>{{ $faq->answer }}</p></div>
        @empty
          <div class="csh-qa"><b><span class="csh-q">Q.</span>{{ __('Is Centresidence really free to start?') }}</b><p>{{ __('Yes. You can create an account and run the essentials at no cost. You only pay when you choose to do more.') }}</p></div>
          <div class="csh-qa"><b><span class="csh-q">Q.</span>{{ __('Do I need any technical skills?') }}</b><p>{{ __('None. If you can use WhatsApp, you can use Centresidence.') }}</p></div>
          <div class="csh-qa"><b><span class="csh-q">Q.</span>{{ __('How do tenants pay rent?') }}</b><p>{{ __('Straight from their phone, including M-Pesa, with receipts sent automatically.') }}</p></div>
          <div class="csh-qa"><b><span class="csh-q">Q.</span>{{ __('Can I manage more than one property?') }}</b><p>{{ __('Yes, as many as you like, all from one dashboard.') }}</p></div>
        @endforelse
      </div>
    </div>
  </section>

  {{-- CONTACT --}}
  <section class="csh-band csh-band--paper2" id="contact-us">
    <div class="csh-wrap csh-contact">
      <div>
        <span class="csh-eyebrow">{{ __('Get started') }}</span>
        <h2>{{ __('Ready to run your properties the easy way?') }}</h2>
        <p class="csh-lead">{{ __('Tell us about your properties and we will show you exactly how Centresidence works for you. No pressure, no jargon.') }}</p>
        <div class="csh-chips" style="margin-top:24px">
          <span class="csh-chip" style="color:var(--stone-700);background:var(--paper);border-color:var(--line)"><span class="csh-dot"></span>{{ __('Free to start') }}</span>
          <span class="csh-chip" style="color:var(--stone-700);background:var(--paper);border-color:var(--line)"><span class="csh-dot"></span>{{ __('Set up in a day') }}</span>
        </div>
      </div>
      <form class="csh-form ajax" action="{{ route('contact.message.store') }}" method="POST" data-handler="getShowMessage">
        @csrf
        <input type="hidden" name="intent" id="cshIntent" value="general">
        <h3>{{ __('Talk to us') }}</h3>
        <p>{{ __('We will get back to you shortly.') }}</p>
        <div class="csh-frow">
          <input type="text" name="first_name" placeholder="{{ __('First name') }}">
          <input type="text" name="last_name" placeholder="{{ __('Last name') }}">
        </div>
        <input type="email" name="email" placeholder="{{ __('Email') }}">
        <input type="tel" name="phone" placeholder="{{ __('Phone number') }}">
        <input type="text" name="subject" placeholder="{{ __('Subject') }}">
        <textarea name="message" rows="4" placeholder="{{ __('Tell us about your properties') }}"></textarea>
        <button type="submit" class="csh-btn csh-btn--blue" style="width:100%;justify-content:center;margin-top:14px">{{ __('Send inquiry') }}</button>
      </form>
    </div>
  </section>

  {{-- FINAL CTA --}}
  <section class="csh-wrap csh-cta-final">
    <h2>{{ __('Real estate, simplified.') }}</h2>
    <p>{{ __('Join the landlords running calmer, more profitable properties on Centresidence.') }}</p>
    <div style="margin-top:26px"><a href="#contact-us" data-intent="trial" class="csh-btn csh-btn--amber">{{ __('Get started free') }}</a></div>
  </section>

</div>
@endsection
@push('script')
    <script src="{{ asset('assets/js/custom/frontend-index.js') }}"></script>
    <script>
        /* Tag the contact form with the visitor's intent based on which CTA they clicked, so a
           genuine free-trial/signup enquiry is distinguishable from a general contact (the admin
           gets a flagged notification). Also pre-fills the subject so the lead is self-describing. */
        (function () {
            var intentEl = document.getElementById('cshIntent');
            var subjectEl = document.querySelector('.csh-form input[name="subject"]');
            var labels = {
                trial:   '{{ __('Free trial / Get started') }}',
                partner: '{{ __('Partnership enquiry') }}'
            };
            document.querySelectorAll('a[href="#contact-us"][data-intent]').forEach(function (a) {
                a.addEventListener('click', function () {
                    var intent = a.getAttribute('data-intent') || 'general';
                    if (intentEl) intentEl.value = intent;
                    // Only pre-fill the subject if the visitor hasn't typed one.
                    if (subjectEl && !subjectEl.value.trim() && labels[intent]) {
                        subjectEl.value = labels[intent];
                    }
                });
            });
        })();
    </script>
@endpush
