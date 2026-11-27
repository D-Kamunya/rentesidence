@extends('saas.frontend.layouts.app')

@section('content')
@php
    $pageTitle = $headline ?? __('Vacancies');
    $user      = Auth::check() ? Auth::user() : null;
    $currentUrl = url()->current();
    $notified  = session('storefront_notified');
    $notifyEmail = session('notify_email');
@endphp

{{--
    Vacancy Storefront (Layer 1). House Hunt SCOPED to one owner or one property, wrapped in a
    shareable surface with a "notify me → free tenant / House Watch" funnel on the empty state.
    Reuses the CS dark-premium styling + the exact apply flow (tenant.house-hunt.application.submit)
    from the House Hunt detail page — same engine, scoped.
--}}

<style>
  main{background:#FAF9F6}
  .sf{
    --paper:#FAF9F6; --paper-2:#EFF4FA; --card:#FFFFFF; --card-2:#F4F1EC;
    --stone-900:#1B1E22; --stone-700:#3A3F47; --stone-500:#6B7280; --stone-400:#9AA0A8;
    --line:#E6E1D8;
    --cs-blue:#185FA5; --cs-blue-2:#1c72c2; --cs-blue-tint:#E8F0F9;
    --amber:#E7A339; --amber-2:#f0af49;
    --shadow:0 20px 46px -24px rgba(20,23,28,.16); --shadow-sm:0 10px 26px -18px rgba(20,23,28,.10);
    --serif:Georgia,'Iowan Old Style','Times New Roman',serif;
    --sans:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;
    color-scheme:light;font-family:var(--sans);color:var(--stone-900)}
  .sf *{box-sizing:border-box}
  .sf a{text-decoration:none}
  .sf-wrap{max-width:1200px;margin:0 auto;padding:0 24px}

  /* Unified hero — a CLEAN (text-free) brand scene fills the hero; OUR own crisp lockup + the live
     details are overlaid on the dark-sky left over a left-weighted scrim, leaving the lit house clear
     on the right. No baked text = no ghosting. Swap the scene via assets/images/storefront-cover.jpg. */
  /* The content is TOP-aligned (flex-start), not centered — centering pushed any content taller
     than the band out the top, and overflow:hidden then ate the logo. Top-align pins the lockup to
     padding-top no matter how tall the content grows; the band just extends below if needed. */
  .sf-hero{position:relative;width:100%;min-height:clamp(360px,38vw,540px);overflow:hidden;
    background:#06101F url('{{ asset('assets/images/storefront-cover.jpg') }}') center center/cover no-repeat}
  @media(max-width:640px){.sf-hero{background-position:72% center;min-height:420px}}
  .sf-hero__scrim{position:absolute;inset:0;z-index:1;pointer-events:none;background:
      linear-gradient(90deg, rgba(5,11,22,.92) 0%, rgba(5,11,22,.74) 26%, rgba(5,11,22,.36) 48%, rgba(5,11,22,.06) 70%, rgba(5,11,22,0) 86%),
      linear-gradient(180deg, rgba(5,11,22,.34) 0%, rgba(5,11,22,0) 28%, rgba(5,11,22,0) 68%, rgba(5,11,22,.55) 100%)}
  .sf-hero__inner{position:relative;z-index:2;width:100%;max-width:1200px;margin:0 auto;
    padding:52px 24px 46px;box-sizing:border-box;
    display:flex;flex-direction:column;justify-content:flex-start;align-items:flex-start}
  .sf-hero__inner > *{max-width:600px}
  /* Our OWN brand lockup (crisp, not baked into the photo) */
  .sf-lockup{display:flex;align-items:center;gap:13px;margin-bottom:24px}
  .sf-lockup__icon{height:50px;width:auto;display:block;filter:drop-shadow(0 6px 16px rgba(0,0,0,.45))}
  .sf-lockup__wm{display:flex;flex-direction:column;line-height:1}
  .sf-lockup__name{font-size:25px;font-weight:700;letter-spacing:-.01em;color:#fff}
  .sf-lockup__tag{font-size:10px;font-weight:700;letter-spacing:.15em;text-transform:uppercase;color:#7FB2E6;margin-top:6px}
  .sf-eyebrow{display:inline-flex;align-items:center;gap:8px;font-size:12px;font-weight:700;letter-spacing:.12em;
    text-transform:uppercase;color:var(--amber);margin-bottom:12px}
  .sf-eyebrow svg{width:15px;height:15px}
  .sf-hero h1{font-family:var(--serif);font-weight:600;letter-spacing:-.015em;color:#FBF8F2;
    font-size:clamp(28px,4.4vw,48px);margin:0;text-wrap:balance;text-shadow:0 3px 26px rgba(0,0,0,.6)}
  .sf-hero__sub{margin-top:11px;color:#E6ECF4;font-size:16.5px;max-width:560px;text-shadow:0 1px 12px rgba(0,0,0,.6)}
  /* Overlaid info row — the per-page specifics (property / owner / location / count) */
  .sf-inforow{display:flex;flex-wrap:wrap;align-items:center;gap:14px 26px;margin-top:22px;
    padding-top:20px;border-top:1px solid rgba(255,255,255,.16);max-width:900px}
  .sf-info{display:flex;align-items:center;gap:11px;min-width:0}
  .sf-info__ic{width:36px;height:36px;border-radius:10px;background:rgba(255,255,255,.12);
    border:1px solid rgba(255,255,255,.20);display:flex;align-items:center;justify-content:center;color:#CFE1F5;flex:0 0 auto}
  .sf-info__ic svg{width:18px;height:18px}
  /* label stacks ABOVE value (was jamming inline → "Managed byMr Owner") */
  .sf-info__txt{display:flex;flex-direction:column;min-width:0}
  .sf-info__k{font-size:10.5px;font-weight:800;letter-spacing:.11em;text-transform:uppercase;color:#A9C0DB;line-height:1.1}
  .sf-info__v{font-size:14.5px;font-weight:750;color:#fff;line-height:1.25;margin-top:4px;
    white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:240px}

  /* Section head */
  .sf-sec-head{margin:38px 0 4px}
  .sf-sec-head h2{font-size:clamp(20px,2.6vw,26px);font-weight:750;color:var(--stone-900)}
  .sf-sec-head p{margin-top:6px;color:var(--stone-500);font-size:14.5px}

  /* Units grid */
  .sf-units{display:grid;grid-template-columns:repeat(3,1fr);gap:24px;padding:22px 0 18px}
  @media(max-width:900px){.sf-units{grid-template-columns:repeat(2,1fr)}}
  @media(max-width:600px){.sf-units{grid-template-columns:1fr}}
  .sf-card{background:var(--card);border:1px solid var(--line);border-radius:16px;overflow:hidden;
    box-shadow:var(--shadow-sm);display:flex;flex-direction:column;transition:.2s}
  .sf-card:hover{transform:translateY(-5px);border-color:color-mix(in srgb,var(--cs-blue) 45%,var(--line))}
  .sf-card__media{position:relative;aspect-ratio:16/10;overflow:hidden;cursor:pointer;background:var(--card-2)}
  .sf-card__media img{width:100%;height:100%;object-fit:cover;display:block;transition:.35s}
  .sf-card__media:hover img{transform:scale(1.04)}
  .sf-noimg{position:absolute;inset:0;display:flex;align-items:center;justify-content:center;flex-direction:column;
    gap:6px;color:var(--stone-400);font-size:13px;font-weight:600}
  .sf-noimg svg{width:34px;height:34px;opacity:.6}
  .sf-photos{position:absolute;bottom:10px;right:10px;z-index:2;background:rgba(0,0,0,.55);backdrop-filter:blur(4px);
    color:#fff;font-size:11.5px;font-weight:600;padding:5px 10px;border-radius:99px;display:inline-flex;align-items:center;gap:6px}
  .sf-photos svg{width:13px;height:13px}
  .sf-card__body{padding:20px;display:flex;flex-direction:column;flex:1}
  .sf-card__title{font-size:18px;font-weight:750;color:var(--stone-900);margin:0}
  .sf-card__prop{display:flex;align-items:center;gap:7px;color:var(--stone-500);font-size:13.5px;margin-top:6px}
  .sf-card__prop svg{width:14px;height:14px;color:var(--cs-blue);flex:0 0 auto}
  .sf-specs{display:flex;gap:16px;margin-top:16px;flex-wrap:wrap}
  .sf-spec{display:inline-flex;align-items:center;gap:7px;color:var(--stone-700);font-size:14px}
  .sf-spec svg{width:18px;height:18px;color:var(--stone-500)}
  .sf-amenities{margin-top:14px;display:flex;flex-wrap:wrap;gap:6px}
  .sf-amenity{font-size:11.5px;color:var(--stone-700);background:var(--card-2);border:1px solid var(--line);
    padding:4px 10px;border-radius:99px}
  .sf-foot{margin-top:auto;padding-top:16px;border-top:1px solid var(--line);
    display:flex;align-items:center;justify-content:space-between;gap:12px;margin-top:16px}
  .sf-rent{color:var(--amber);font-weight:750;font-size:15.5px;white-space:nowrap;text-align:right}
  .sf-rent small{display:block;color:var(--stone-500);font-weight:500;font-size:11px}
  .sf-apply{margin-top:16px;width:100%;display:inline-flex;align-items:center;justify-content:center;gap:8px;
    background:var(--cs-blue);color:#fff !important;font-weight:650;font-size:15px;padding:13px;border-radius:11px;
    border:none;cursor:pointer;transition:.18s}
  .sf-apply:hover{background:var(--cs-blue-2);transform:translateY(-1px)}

  .sf-propgroup{margin-top:10px}
  .sf-propgroup > h3{font-size:18px;font-weight:800;color:var(--stone-900);margin:26px 0 2px;
    display:flex;align-items:center;gap:9px}
  .sf-propgroup > h3 svg{width:18px;height:18px;color:var(--cs-blue)}

  /* Property banner — the building photo partitions one property's units from the next */
  .sf-propbanner{position:relative;border-radius:16px;overflow:hidden;margin:38px 0 18px;min-height:156px;
    display:flex;align-items:flex-end;background:#13263d;box-shadow:var(--shadow-sm)}
  .sf-propbanner img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover}
  .sf-propbanner__scrim{position:absolute;inset:0;background:linear-gradient(180deg,rgba(10,20,32,.12),rgba(10,20,32,.84))}
  .sf-propbanner__noimg{position:absolute;inset:0;background:
      radial-gradient(600px 220px at 85% -30%, rgba(231,163,57,.20), transparent 60%),
      linear-gradient(135deg,#17487A,#0E2E52);display:flex;align-items:center;justify-content:center}
  .sf-propbanner__noimg svg{width:56px;height:56px;color:rgba(255,255,255,.20)}
  .sf-propbanner__body{position:relative;z-index:2;padding:22px 24px;color:#fff}
  .sf-propbanner__name{font-size:clamp(20px,3vw,24px);font-weight:800;margin:0;color:#fff;text-shadow:0 2px 14px rgba(0,0,0,.5);
    display:flex;align-items:center;gap:9px}
  .sf-propbanner__name svg{width:19px;height:19px;color:var(--amber)}
  .sf-propbanner__loc{display:block;margin-top:7px;font-size:13.5px;color:#e4eaf2;text-shadow:0 1px 8px rgba(0,0,0,.5)}
  .sf-propbanner__meta{display:inline-flex;align-items:center;gap:7px;margin-top:11px;font-size:12.5px;font-weight:700;
    background:rgba(255,255,255,.16);border:1px solid rgba(255,255,255,.24);border-radius:99px;padding:5px 13px;color:#fff}

  /* Funnel panels */
  .sf-panel{background:var(--card);border:1px solid var(--line);border-radius:18px;box-shadow:var(--shadow-sm);
    padding:30px;margin:26px 0 70px}
  .sf-empty{text-align:center;max-width:640px;margin:46px auto 70px}
  .sf-empty__icon{width:66px;height:66px;border-radius:50%;background:var(--cs-blue-tint);display:flex;
    align-items:center;justify-content:center;margin:0 auto 18px;color:var(--cs-blue)}
  .sf-empty__icon svg{width:30px;height:30px}
  .sf-empty h2{font-size:clamp(22px,3vw,28px);font-weight:800;color:var(--stone-900);margin:0}
  .sf-empty p{color:var(--stone-500);font-size:15.5px;margin-top:10px;line-height:1.6}

  .sf-notify{margin-top:24px;text-align:left}
  .sf-notify h4{font-size:14px;font-weight:800;color:var(--stone-900);margin:0 0 4px}
  .sf-notify p.hint{color:var(--stone-500);font-size:13px;margin:0 0 16px}
  .sf-grid2{display:grid;grid-template-columns:1fr 1fr;gap:14px}
  @media(max-width:560px){.sf-grid2{grid-template-columns:1fr}}
  .sf-fld label{display:block;font-size:11.5px;font-weight:700;letter-spacing:.04em;text-transform:uppercase;
    color:var(--stone-500);margin-bottom:6px}
  .sf-fld input{width:100%;font-family:inherit;font-size:14.5px;padding:11px 13px;border-radius:10px;
    border:1px solid var(--line);background:var(--paper-2);color:var(--stone-900)}
  .sf-fld input:focus{outline:2px solid var(--cs-blue);outline-offset:1px;border-color:transparent}
  .sf-fld.full{grid-column:1/-1}
  .sf-btn{margin-top:16px;display:inline-flex;align-items:center;justify-content:center;gap:8px;
    background:var(--cs-blue);color:#fff !important;font-weight:650;font-size:15px;padding:13px 22px;border-radius:11px;
    border:none;cursor:pointer;transition:.18s}
  .sf-btn:hover{background:var(--cs-blue-2)}
  .sf-err{color:#e5484d;font-size:12.5px;margin-top:6px;display:block}

  .sf-watch{display:flex;gap:18px;align-items:flex-start;flex-wrap:wrap;
    background:linear-gradient(180deg,#F7FAFE,#EEF4FB);border:1px solid var(--cs-blue-tint);
    border-radius:16px;padding:24px;margin:10px 0 70px}
  .sf-watch__icon{width:46px;height:46px;border-radius:12px;background:var(--cs-blue);color:#fff;display:flex;
    align-items:center;justify-content:center;flex:0 0 auto}
  .sf-watch__icon svg{width:22px;height:22px}
  .sf-watch__body{flex:1;min-width:260px}
  .sf-watch__body h4{font-size:16px;font-weight:800;color:var(--stone-900);margin:0 0 4px}
  .sf-watch__body p{color:var(--stone-500);font-size:13.5px;margin:0}
  .sf-watch details{margin-top:12px}
  .sf-watch summary{cursor:pointer;color:var(--cs-blue);font-weight:700;font-size:14px;list-style:none}
  .sf-watch summary::-webkit-details-marker{display:none}

  /* Success */
  .sf-success{background:linear-gradient(180deg,#F1FBF4,#E6F7EC);border:1px solid #BfE8CB;border-radius:18px;
    padding:28px;margin:26px 0 20px;text-align:center}
  .sf-success h3{font-size:20px;font-weight:800;color:#166534;margin:0 0 6px}
  .sf-success p{color:#3A3F47;font-size:14.5px;margin:0 0 16px}
  .sf-success .sf-btn{background:var(--amber);color:#10131a !important}
  .sf-success .sf-btn:hover{background:var(--amber-2)}

  /* Modals */
  .sf-modal .modal-content{background:var(--card);border:1px solid var(--line);border-radius:18px;color:var(--stone-900);box-shadow:var(--shadow)}
  .sf-modal .modal-header{border-bottom:1px solid var(--line);padding:20px 24px}
  .sf-modal .modal-title{color:var(--stone-900);font-weight:750;font-size:19px}
  .sf-modal .modal-body{padding:22px 24px}
  .sf-modal .btn-close{filter:none}
  .sf-unitinfo{background:var(--cs-blue-tint);border:1px solid color-mix(in srgb,var(--cs-blue) 40%,var(--line));
    border-radius:12px;padding:14px 16px;color:var(--stone-700);font-size:14px;line-height:1.7;margin-bottom:22px}
  .sf-unitinfo strong{color:var(--stone-900)}
  .sf-formsec{font-size:12px;font-weight:750;letter-spacing:.08em;text-transform:uppercase;color:var(--cs-blue);margin:0 0 14px}
  .hd-grid2{display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:22px}
  .hd-grid3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:14px}
  @media(max-width:600px){.hd-grid2,.hd-grid3{grid-template-columns:1fr}}
  .hd-col-full{grid-column:1/-1}
  .sf-field label{display:block;font-size:11.5px;font-weight:700;letter-spacing:.04em;text-transform:uppercase;
    color:var(--stone-500);margin-bottom:6px}
  .sf-field label .req{color:var(--amber)}
  .sf-modal .form-control{width:100%;font-family:inherit;font-size:14.5px;padding:11px 13px;border-radius:10px;
    border:1px solid var(--line);background:var(--paper-2);color:var(--stone-900)}
  .sf-modal .form-control:focus{outline:2px solid var(--cs-blue);outline-offset:1px;border-color:transparent}
  .sf-modal .form-control.is-invalid{border-color:#e5484d}
  .sf-modal .modal-footer{border-top:1px solid var(--line);padding:18px 24px;flex-direction:column;gap:10px}
  .sf-submit{width:100%;background:var(--cs-blue);color:#fff !important;font-weight:650;font-size:15px;padding:13px;
    border:none;border-radius:11px;cursor:pointer;transition:.18s}
  .sf-submit:hover{background:var(--cs-blue-2)}
  .sf-close{width:100%;background:transparent;color:var(--stone-500) !important;font-weight:600;font-size:14.5px;
    padding:12px;border:1px solid var(--line);border-radius:11px;cursor:pointer}
  .sf-close:hover{border-color:var(--stone-400);color:var(--stone-900) !important}
  #imageLightboxModal .modal-content{background:#0B0F15;border:1px solid var(--line);border-radius:16px}
</style>

<div class="sf">

  {{-- Unified hero — the brand image fills it; the live details are overlaid on a blended scrim --}}
  <div class="sf-hero">
    <div class="sf-hero__scrim"></div>
    <div class="sf-hero__inner">
      <div class="sf-lockup">
        <img class="sf-lockup__icon" src="{{ asset('assets/images/cs-icon.png') }}" alt="Centresidence"
             onerror="this.style.display='none'">
        <span class="sf-lockup__wm">
          <span class="sf-lockup__name">{{ getOption('app_name') ?: 'centresidence' }}</span>
          <span class="sf-lockup__tag">{{ __('Real Estate. Simplified. Connected.') }}</span>
        </span>
      </div>
      <span class="sf-eyebrow">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 11l9-8 9 8"/><path d="M5 10v10h14V10"/></svg>
        {{ __('Available to rent') }}
      </span>
      <h1>{{ $headline }}</h1>
      <p class="sf-hero__sub">{{ $subheadline }}</p>

      <div class="sf-inforow">
        @if(!empty($propertyName))
          <div class="sf-info">
            <span class="sf-info__ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 11l9-8 9 8"/><path d="M5 10v10h14V10"/></svg></span>
            <span class="sf-info__txt"><span class="sf-info__k">{{ __('Property') }}</span><span class="sf-info__v" title="{{ $propertyName }}">{{ $propertyName }}</span></span>
          </div>
        @endif
        <div class="sf-info">
          <span class="sf-info__ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 7a4 4 0 1 1-8 0 4 4 0 0 1 8 0zM4 21v-1a6 6 0 0 1 6-6h4a6 6 0 0 1 6 6v1"/></svg></span>
          <span class="sf-info__txt"><span class="sf-info__k">{{ __('Managed by') }}</span><span class="sf-info__v" title="{{ $ownerName }}">{{ $ownerName }}</span></span>
        </div>
        @if(!empty($location))
          <div class="sf-info">
            <span class="sf-info__ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg></span>
            <span class="sf-info__txt"><span class="sf-info__k">{{ __('Location') }}</span><span class="sf-info__v" title="{{ $location }}">{{ $location }}</span></span>
          </div>
        @endif
        @if($totalUnits > 0)
          <div class="sf-info">
            <span class="sf-info__ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg></span>
            <span class="sf-info__txt"><span class="sf-info__k">{{ __('Available now') }}</span><span class="sf-info__v">{{ $totalUnits }} {{ trans_choice('vacancy|vacancies', $totalUnits) }}</span></span>
          </div>
        @endif
      </div>
    </div>
  </div>

  <div class="sf-wrap">

    @if($notified)
      {{-- Notify-me captured → convert to a free tenant account + House Watch --}}
      <div class="sf-success">
        <h3>{{ __('You’re on the list ✅') }}</h3>
        <p>{{ __('We’ll let you know as soon as a home opens up. Want it even faster? Create a free account and track openings in your area.') }}</p>
        <a class="sf-btn" href="{{ route('tenant.join', array_filter(['for' => 'moving', 'email' => $notifyEmail])) }}">
          {{ __('Create your free account') }}
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" width="15" height="15"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
        </a>
      </div>
    @endif

    @if($totalUnits > 0)

      @foreach($groups as $group)
        @php $property = $group->property; @endphp
        <div class="sf-propgroup">
          @if($scope === 'owner')
            @php
                $thumb    = trim($property->thumbnail_image ?? '');
                $noImg    = asset('assets/images/no-image.jpg');
                $hasBanner = $thumb && $thumb !== $noImg;
                $detail   = $property->propertyDetail;
                $loc      = collect([optional($detail)->city_id, optional($detail)->state_id])
                                ->filter()->map(fn($x) => ucfirst(strtolower($x)))->implode(', ');
                $vCount   = $group->units->count();
            @endphp
            <div class="sf-propbanner">
              @if($hasBanner)
                <img src="{{ $thumb }}" alt="{{ $property->name }}" loading="lazy" onerror="this.style.display='none'">
                <div class="sf-propbanner__scrim"></div>
              @else
                <div class="sf-propbanner__noimg">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M3 11l9-8 9 8"/><path d="M5 10v10h14V10"/><path d="M9 21V13h6v8"/></svg>
                </div>
                <div class="sf-propbanner__scrim"></div>
              @endif
              <div class="sf-propbanner__body">
                <h3 class="sf-propbanner__name">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 11l9-8 9 8"/><path d="M5 10v10h14V10"/></svg>
                  {{ $property->name }}
                </h3>
                @if($loc)<span class="sf-propbanner__loc">{{ $loc }}</span>@endif
                <span class="sf-propbanner__meta">{{ $vCount }} {{ trans_choice('vacancy|vacancies', $vCount) }} {{ __('available') }}</span>
              </div>
            </div>
          @else
            {{-- Property-scope: show the building's OWN main photo over its units (the name is already
                 in the hero strip, so this banner leads with "Available units"). --}}
            @php
                $thumb    = trim($property->thumbnail_image ?? '');
                $noImg    = asset('assets/images/no-image.jpg');
                $hasBanner = $thumb && $thumb !== $noImg;
                $vCount   = $group->units->count();
            @endphp
            <div class="sf-propbanner">
              @if($hasBanner)
                <img src="{{ $thumb }}" alt="{{ $property->name }}" loading="lazy" onerror="this.style.display='none'">
                <div class="sf-propbanner__scrim"></div>
              @else
                <div class="sf-propbanner__noimg">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M3 11l9-8 9 8"/><path d="M5 10v10h14V10"/><path d="M9 21V13h6v8"/></svg>
                </div>
                <div class="sf-propbanner__scrim"></div>
              @endif
              <div class="sf-propbanner__body">
                <h3 class="sf-propbanner__name">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                  {{ __('Available units') }}
                </h3>
                <span class="sf-propbanner__meta">{{ $vCount }} {{ trans_choice('vacancy|vacancies', $vCount) }} {{ __('available') }}</span>
              </div>
            </div>
          @endif

          <div class="sf-units">
            @foreach($group->units as $unit)
              @php
                  $unitImages = $unit->images;
                  $hasImages  = $unitImages->isNotEmpty();
                  $firstImage = $hasImages
                      ? asset('storage/' . $unitImages->first()->folder_name . '/' . $unitImages->first()->file_name)
                      : null;
                  $imageUrls  = $unitImages->map(fn($img) =>
                      asset('storage/' . $img->folder_name . '/' . $img->file_name)
                  )->toJson();
                  $unitLabel  = $unit->unit_name ?? ('Unit ' . $unit->id);
              @endphp

              <div class="sf-card">
                <div class="sf-card__media" @if($hasImages) onclick="openImageModal({{ $unit->id }}, {{ $imageUrls }})" @endif>
                  @if($hasImages)
                    <img src="{{ $firstImage }}" alt="{{ $unitLabel }}" loading="lazy"
                         onerror="this.onerror=null;this.src='{{ asset('assets/images/unit.png') }}';">
                    @if($unitImages->count() > 1)
                      <span class="sf-photos">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg>
                        {{ $unitImages->count() }} {{ __('photos') }}
                      </span>
                    @endif
                  @else
                    <div class="sf-noimg">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg>
                      {{ __('Photos coming soon') }}
                    </div>
                  @endif
                </div>

                <div class="sf-card__body">
                  <h3 class="sf-card__title">{{ __('Unit') }} {{ $unitLabel }}</h3>
                  <div class="sf-card__prop">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 11l9-8 9 8"/><path d="M5 10v10h14V10"/></svg>
                    {{ $property->name }}
                  </div>

                  <div class="sf-specs">
                    <span class="sf-spec">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M2 20v-6a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v6"/><path d="M4 12V7a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v5"/><path d="M2 20h20"/></svg>
                      {{ $unit->bedroom ?? 'N/A' }} {{ __('bed') }}
                    </span>
                    <span class="sf-spec">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M4 12V6a2 2 0 0 1 4 0M2 12h20v3a5 5 0 0 1-5 5H7a5 5 0 0 1-5-5z"/></svg>
                      {{ $unit->bath ?? 'N/A' }} {{ __('bath') }}
                    </span>
                    <span class="sf-spec">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M9 17V7h4a3 3 0 0 1 0 6H9"/></svg>
                      {{ $unit->parking ?? 'N/A' }} {{ __('parking') }}
                    </span>
                  </div>

                  @if(!empty($unit->amenities))
                    <div class="sf-amenities">
                      @foreach(array_slice(explode(',', $unit->amenities), 0, 6) as $amenity)
                        @if(trim($amenity) !== '')<span class="sf-amenity">{{ trim($amenity) }}</span>@endif
                      @endforeach
                    </div>
                  @endif

                  <div class="sf-foot">
                    <div class="sf-rent">KES {{ number_format($unit->general_rent) }}<small>{{ __('per month') }}</small></div>
                  </div>

                  <button type="button" class="sf-apply"
                          onclick="openApplyModal(
                              {{ $unit->id }},
                              {{ json_encode($unitLabel) }},
                              {{ json_encode($property->name) }},
                              {{ json_encode($ownerName) }},
                              {{ (float) $unit->general_rent }},
                              {{ $property->id }}
                          )">
                    {{ __('Apply for this unit') }}
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" width="15" height="15"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                  </button>
                </div>
              </div>
            @endforeach
          </div>
        </div>
      @endforeach

      {{-- House Watch strip — even when there are vacancies, let people in this area subscribe --}}
      @unless($notified)
      <div class="sf-watch">
        <div class="sf-watch__icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
        </div>
        <div class="sf-watch__body">
          <h4>{{ __('Don’t see the right fit? Set up a House Watch.') }}</h4>
          <p>{{ __('Tell us the area you’re after and we’ll let you know the moment a matching home opens up — no account needed to start.') }}</p>
          <details>
            <summary>{{ __('Notify me of new homes →') }}</summary>
            @include('storefront.partials.notify-form', ['variant' => 'watch'])
          </details>
        </div>
      </div>
      @endunless

    @else

      {{-- EMPTY STATE = THE FUNNEL --}}
      @unless($notified)
      <div class="sf-empty">
        <div class="sf-empty__icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 11l9-8 9 8"/><path d="M5 10v10h14V10"/><path d="M9 21V13h6v8"/></svg>
        </div>
        <h2>{{ __('No vacancies right now') }}</h2>
        <p>{{ __('Everything here is currently occupied. Leave your details and we’ll be the first to tell you when a home opens up — and you can watch your whole area, not just this one.') }}</p>

        <div class="sf-panel sf-notify">
          @include('storefront.partials.notify-form', ['variant' => 'empty'])
        </div>
      </div>
      @endunless

    @endif
  </div>

  {{-- Shared image lightbox modal --}}
  <div class="modal fade" id="imageLightboxModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
      <div class="modal-content border-0">
        <div class="modal-header border-0 pb-0">
          <h6 class="modal-title text-white" id="lightboxTitle"></h6>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body p-2">
          <div id="lightboxCarousel" class="carousel slide" data-bs-ride="false">
            <div class="carousel-inner" id="lightboxInner"></div>
            <button class="carousel-control-prev" type="button" data-bs-target="#lightboxCarousel" data-bs-slide="prev"><span class="carousel-control-prev-icon"></span></button>
            <button class="carousel-control-next" type="button" data-bs-target="#lightboxCarousel" data-bs-slide="next"><span class="carousel-control-next-icon"></span></button>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- Shared application modal (identical flow to House Hunt) --}}
  <div class="modal fade sf-modal" id="sharedApplyModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <form id="sharedApplyForm" class="ajaxx" method="POST"
            action="{{ route('tenant.house-hunt.application.submit') }}" data-handler="applicationHandler">
        @csrf
        <input type="hidden" name="property_unit_id" id="applyUnitId">
        <input type="hidden" name="property_id" id="applyPropertyId">

        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="applyModalTitle">{{ __('Apply for Unit') }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="sf-unitinfo" id="applyUnitInfo"></div>

            <p class="sf-formsec">{{ __('Personal Information') }}</p>
            <div class="hd-grid2">
              <div class="sf-field"><label>{{ __('First Name') }} <span class="req">*</span></label>
                <input type="text" name="first_name" class="form-control" value="{{ old('first_name', $user?->first_name) }}" required></div>
              <div class="sf-field"><label>{{ __('Last Name') }} <span class="req">*</span></label>
                <input type="text" name="last_name" class="form-control" value="{{ old('last_name', $user?->last_name) }}" required></div>
              <div class="sf-field"><label>{{ __('Phone Number') }} <span class="req">*</span></label>
                <input type="text" name="contact_number" class="form-control" value="{{ old('contact_number', $user?->contact_number) }}" required></div>
              <div class="sf-field"><label>{{ __('Email') }} <span class="req">*</span></label>
                <input type="email" name="email" class="form-control" value="{{ old('email', $user?->email) }}" required></div>
              <div class="sf-field"><label>{{ __('Job') }}</label>
                <input type="text" name="job" class="form-control" value="{{ old('job', $user?->tenant?->job) }}"></div>
              <div class="sf-field"><label>{{ __('Age') }}</label>
                <input type="number" name="age" class="form-control" value="{{ old('age', $user?->tenant?->age) }}"></div>
              <div class="sf-field hd-col-full"><label>{{ __('Family Members') }}</label>
                <input type="number" name="family_member" class="form-control" value="{{ old('family_member', $user?->tenant?->family_member) }}"></div>
            </div>

            <p class="sf-formsec">{{ __('Address Information') }}</p>
            <div class="hd-grid3">
              <div class="sf-field hd-col-full"><label>{{ __('Address') }} <span class="req">*</span></label>
                <input type="text" name="permanent_address" class="form-control" required></div>
              <div class="sf-field"><label>{{ __('Country') }} <span class="req">*</span></label>
                <input type="text" name="permanent_country_id" class="form-control" required></div>
              <div class="sf-field"><label>{{ __('State') }} <span class="req">*</span></label>
                <input type="text" name="permanent_state_id" class="form-control" required></div>
              <div class="sf-field"><label>{{ __('City') }} <span class="req">*</span></label>
                <input type="text" name="permanent_city_id" class="form-control" required></div>
              <div class="sf-field hd-col-full"><label>{{ __('Zip Code') }} <span class="req">*</span></label>
                <input type="text" name="permanent_zip_code" class="form-control" required></div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="submit" class="sf-submit">{{ __('Submit Application') }}</button>
            <button type="button" class="sf-close" data-bs-dismiss="modal">{{ __('Close') }}</button>
          </div>
        </div>
      </form>
    </div>
  </div>

</div>
@endsection

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
function openImageModal(unitId, imageUrls) {
    if (!imageUrls || imageUrls.length === 0) return;
    const inner = document.getElementById('lightboxInner');
    inner.innerHTML = imageUrls.map((url, i) => `
        <div class="carousel-item ${i === 0 ? 'active' : ''}">
            <img src="${url}" class="d-block w-100" style="max-height:75vh;object-fit:contain;" alt="Unit image ${i + 1}" loading="lazy">
        </div>
    `).join('');
    document.getElementById('lightboxTitle').textContent = 'Unit photos (' + imageUrls.length + ')';
    const carousel = bootstrap.Carousel.getOrCreateInstance(document.getElementById('lightboxCarousel'));
    carousel.to(0);
    new bootstrap.Modal(document.getElementById('imageLightboxModal')).show();
}

function openApplyModal(unitId, unitName, propertyName, agentName, rent, propertyId) {
    document.getElementById('applyUnitId').value     = unitId;
    document.getElementById('applyPropertyId').value = propertyId;
    document.getElementById('applyModalTitle').textContent = 'Apply for Unit ' + unitName;
    document.getElementById('applyUnitInfo').innerHTML =
        '<strong>' + propertyName + '</strong><br>' +
        'Unit: ' + unitName + '<br>' +
        'Owner: ' + agentName + '<br>' +
        'Rent: KES ' + Number(rent).toLocaleString() + ' / Month';

    const form = document.getElementById('sharedApplyForm');
    ['permanent_address','permanent_country_id','permanent_state_id',
     'permanent_city_id','permanent_zip_code'].forEach(name => {
        const el = form.querySelector('[name="' + name + '"]');
        if (el) el.value = '';
    });

    new bootstrap.Modal(document.getElementById('sharedApplyModal')).show();
}

$(document).ready(function () {
    $('form.ajaxx').on('submit', function (e) {
        e.preventDefault();
        const $form = $(this);
        const handlerName = $form.data('handler');
        const handler = window[handlerName];
        $.ajax({
            url:    $form.attr('action'),
            method: $form.attr('method'),
            data:   $form.serialize(),
            success: function (response) { if (typeof handler === 'function') handler(response, $form); },
            error: function (xhr) { if (typeof commonHandler === 'function') commonHandler(xhr.responseJSON, $form); }
        });
    });

    const applyModalEl = document.getElementById('sharedApplyModal');
    if (applyModalEl) {
        applyModalEl.addEventListener('hidden.bs.modal', function () {
            const form = this.querySelector('form');
            if (form) form.reset();
        });
    }
});

function applicationHandler(data, $form) {
    $(".error-message").remove();
    $(".is-invalid").removeClass("is-invalid");
    if (data["status"] == true) {
        alertAjaxMessage("success", data["message"]);
        $form[0].reset();
        const bsModal = bootstrap.Modal.getInstance(document.getElementById('sharedApplyModal'));
        if (bsModal) bsModal.hide();
    } else {
        commonHandler(data);
    }
}
</script>
