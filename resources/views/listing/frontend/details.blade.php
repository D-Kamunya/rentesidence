@extends('saas.frontend.layouts.app')
@section('content')
{{--
    Sale listing detail. Restyled 2026-08 to CS dark-premium (matches house hunt).
    Preserved: owl-carousel (.property-slider-area), the mapbox map (#map + mapData),
    the contact modal (form .ajax → listing.contact.store, data-handler getShowMessage),
    related-listing routes, and every $listing/$images/$information/$relatedListings field.
--}}
<style>
  main{background:#0E1218}
  .sd{
    --paper:#0E1218; --paper-2:#141922; --card:#161C26; --card-2:#1B222E;
    --stone-900:#EDEAE3; --stone-700:#C4C0B7; --stone-500:#9A958A; --stone-400:#7C776C;
    --line:#242B36;
    --cs-blue:#185FA5; --cs-blue-2:#1c72c2; --cs-blue-tint:#12283F;
    --amber:#E7A339; --green:#1D9E75;
    --shadow:0 20px 46px -22px rgba(0,0,0,.6); --shadow-sm:0 10px 26px -16px rgba(0,0,0,.55);
    --serif:Georgia,'Iowan Old Style','Times New Roman',serif;
    --sans:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;
    color-scheme:dark;font-family:var(--sans);color:var(--stone-900)}
  .sd *{box-sizing:border-box}
  .sd a{text-decoration:none}
  .sd-wrap{max-width:1200px;margin:0 auto;padding:0 24px}
  .sd-top{padding-top:140px}

  /* Carousel */
  .sd .property-slider-area{margin-bottom:28px}
  .sd .single-property-slider img{width:100%;height:420px;object-fit:cover;border-radius:16px;border:1px solid var(--line)}
  .sd .owl-nav button{width:44px;height:44px;border-radius:50%!important;background:rgba(11,15,21,.75)!important;
    border:1px solid var(--line)!important;color:#fff!important;font-size:16px!important}
  .sd .owl-nav button:hover{background:var(--cs-blue)!important;border-color:var(--cs-blue)!important}
  .sd .owl-dots .owl-dot span{background:var(--stone-400)!important}
  .sd .owl-dots .owl-dot.active span{background:var(--cs-blue)!important}

  /* Back */
  .sd-back{display:inline-flex;align-items:center;gap:8px;color:var(--stone-500);font-size:14px;font-weight:600;margin-bottom:18px;transition:.15s}
  .sd-back:hover{color:#fff}.sd-back svg{width:16px;height:16px}

  /* Header */
  .sd-badge{display:inline-flex;background:rgba(29,158,117,.15);color:#4fd1a5;border:1px solid rgba(29,158,117,.4);
    font-size:11.5px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;padding:6px 13px;border-radius:99px}
  .sd-head{display:flex;justify-content:space-between;align-items:flex-end;gap:16px;flex-wrap:wrap;margin-top:14px}
  .sd-head h1{font-family:var(--serif);font-weight:600;letter-spacing:-.015em;font-size:clamp(28px,4vw,44px);color:#F2EFEA;margin:0;text-wrap:balance}
  .sd-price{color:var(--amber);font-weight:800;font-size:clamp(22px,3vw,32px);white-space:nowrap}
  .sd-meta{display:flex;flex-wrap:wrap;gap:22px;margin-top:16px;padding-bottom:22px;border-bottom:1px solid var(--line);color:var(--stone-500);font-size:14px}
  .sd-meta span{display:inline-flex;align-items:center;gap:8px}
  .sd-meta svg{width:16px;height:16px;color:var(--cs-blue)}

  /* Layout */
  .sd-grid{display:grid;grid-template-columns:1.6fr 1fr;gap:28px;margin-top:32px}
  @media(max-width:900px){.sd-grid{grid-template-columns:1fr}}
  .sd-block{margin-bottom:30px}
  .sd-block h2{font-size:20px;font-weight:750;color:var(--stone-900);margin:0 0 14px}
  .sd-desc{color:var(--stone-500);font-size:15.5px;line-height:1.75}
  .sd-overview{display:grid;grid-template-columns:repeat(3,1fr);gap:14px}
  @media(max-width:560px){.sd-overview{grid-template-columns:1fr 1fr}}
  .sd-ov{display:flex;align-items:center;gap:10px;background:var(--card);border:1px solid var(--line);border-radius:12px;padding:14px;color:var(--stone-700);font-size:14px}
  .sd-ov svg{width:20px;height:20px;color:var(--cs-blue);flex:0 0 auto}
  .sd-amenities{display:grid;grid-template-columns:repeat(3,1fr);gap:10px}
  @media(max-width:560px){.sd-amenities{grid-template-columns:1fr 1fr}}
  .sd-am{display:flex;align-items:center;gap:9px;color:var(--stone-700);font-size:14px}
  .sd-am svg{width:16px;height:16px;color:var(--green);flex:0 0 auto}

  /* Agent + map card */
  .sd-side{background:var(--card);border:1px solid var(--line);border-radius:16px;padding:22px;box-shadow:var(--shadow-sm);position:sticky;top:100px}
  .sd-agent{display:flex;align-items:center;gap:14px}
  .sd-agent img{width:64px;height:64px;border-radius:50%;object-fit:cover;border:1px solid var(--line)}
  .sd-agent h3{font-size:17px;font-weight:750;color:var(--stone-900);margin:0}
  .sd-agent a{display:flex;align-items:center;gap:7px;color:var(--stone-500);font-size:13.5px;margin-top:5px}
  .sd-agent a:hover{color:var(--cs-blue)}
  .sd-agent svg{width:14px;height:14px}
  .sd-side hr{border:0;border-top:1px solid var(--line);margin:18px 0}
  #map{width:100%;height:300px;border-radius:12px;border:1px solid var(--line)}
  .sd-contact-btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;width:100%;margin-top:18px;
    background:var(--cs-blue);color:#fff!important;font-weight:650;font-size:15px;padding:13px;border-radius:11px;border:none;cursor:pointer;transition:.18s}
  .sd-contact-btn:hover{background:var(--cs-blue-2)}

  /* Nearby + related cards */
  .sd-sec{padding:34px 0}
  .sd-sec > h2{font-size:22px;font-weight:750;color:var(--stone-900);margin:0 0 22px}
  .sd-cards{display:grid;grid-template-columns:repeat(3,1fr);gap:22px}
  @media(max-width:900px){.sd-cards{grid-template-columns:repeat(2,1fr)}}
  @media(max-width:600px){.sd-cards{grid-template-columns:1fr}}
  .sd-card{background:var(--card);border:1px solid var(--line);border-radius:16px;overflow:hidden;box-shadow:var(--shadow-sm);transition:.2s;display:flex;flex-direction:column}
  .sd-card:hover{transform:translateY(-4px);border-color:color-mix(in srgb,var(--cs-blue) 45%,var(--line))}
  .sd-card__media{position:relative;aspect-ratio:16/10;overflow:hidden}
  .sd-card__media img{width:100%;height:100%;object-fit:cover;transition:.35s}
  .sd-card:hover .sd-card__media img{transform:scale(1.04)}
  .sd-card__tag{position:absolute;top:10px;right:10px;background:rgba(231,163,57,.95);color:#20160A;font-weight:750;font-size:12.5px;padding:5px 11px;border-radius:99px}
  .sd-card__body{padding:18px;display:flex;flex-direction:column;flex:1}
  .sd-card__body h3{font-size:16.5px;font-weight:700;color:var(--stone-900);margin:0}
  .sd-card__loc{display:flex;align-items:center;gap:7px;color:var(--stone-500);font-size:13.5px;margin-top:7px}
  .sd-card__loc svg{width:14px;height:14px;color:var(--cs-blue)}
  .sd-card__row{display:flex;justify-content:space-between;gap:8px;color:var(--stone-700);font-size:13px;margin-top:14px}
  .sd-card__cta{margin-top:auto;padding-top:16px}
  .sd-card__cta a{display:flex;align-items:center;justify-content:center;gap:7px;width:100%;background:var(--cs-blue);color:#fff!important;font-weight:650;font-size:14px;padding:11px;border-radius:10px;transition:.18s}
  .sd-card__cta a:hover{background:var(--cs-blue-2)}
  .sd-nearby__meta{color:var(--stone-500);font-size:13px;margin-top:8px;line-height:1.7}
  .sd-nearby__meta b{color:var(--stone-900)}
  .sd-advantages{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:6px}
  @media(max-width:600px){.sd-advantages{grid-template-columns:1fr}}
  .sd-adv{display:flex;align-items:center;gap:9px;color:var(--stone-700);font-size:14.5px}
  .sd-adv svg{width:16px;height:16px;color:var(--amber);flex:0 0 auto}

  /* Contact modal (dark) */
  .sd-modal .modal-content{background:var(--card);border:1px solid var(--line);border-radius:18px;color:var(--stone-900);box-shadow:var(--shadow)}
  .sd-modal-head{padding:20px 24px;border-bottom:1px solid var(--line);display:flex;align-items:center;justify-content:space-between}
  .sd-modal-head h5{margin:0;font-size:18px;font-weight:750;color:var(--stone-900)}
  .sd-modal-head .btn-close{filter:invert(1) grayscale(1) brightness(2)}
  .sd-modal-body{padding:22px 24px}
  .sd-fgrid{display:grid;grid-template-columns:1fr 1fr;gap:14px}
  @media(max-width:560px){.sd-fgrid{grid-template-columns:1fr}}
  .sd-f-full{grid-column:1/-1}
  .sd-modal label{display:block;font-size:11.5px;font-weight:700;letter-spacing:.04em;text-transform:uppercase;color:var(--stone-500);margin-bottom:6px}
  .sd-modal .form-control{width:100%;font-family:inherit;font-size:14.5px;padding:11px 13px;border-radius:10px;border:1px solid var(--line);background:var(--paper-2);color:var(--stone-900)}
  .sd-modal .form-control:focus{outline:2px solid var(--cs-blue);outline-offset:1px;border-color:transparent;box-shadow:none}
  .sd-modal-foot{padding:8px 24px 22px;display:flex;gap:12px}
  .sd-submit{flex:1;background:var(--cs-blue);color:#fff!important;font-weight:650;font-size:15px;padding:12px;border:none;border-radius:11px;cursor:pointer;transition:.18s}
  .sd-submit:hover{background:var(--cs-blue-2)}
  .sd-cancel{background:transparent;color:var(--stone-500)!important;border:1px solid var(--line);border-radius:11px;padding:12px 22px;font-weight:600;cursor:pointer}
  .sd-cancel:hover{border-color:var(--stone-400);color:var(--stone-900)!important}
</style>

<div class="sd">
  <div class="sd-wrap sd-top">
    <a href="{{ route('house.hunt') }}" class="sd-back">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M11 18l-6-6 6-6"/></svg>
      {{ __('Back to House Hunt') }}
    </a>

    {{-- Carousel --}}
    <div class="property-slider-area owl-carousel">
      @foreach ($images as $image)
        <div class="single-property-slider">
          <img src="{{ assetUrl($image->folder_name . '/' . $image->file_name) }}" alt="{{ $listing->name }}">
        </div>
      @endforeach
    </div>

    {{-- Header --}}
    <span class="sd-badge">{{ __('For Sale') }}</span>
    <div class="sd-head">
      <h1>{{ $listing->name }}</h1>
      <span class="sd-price">{{ currencyPrice($listing->price) }}</span>
    </div>
    <div class="sd-meta">
      <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>{{ $listing->created_at->format('M d, Y') }}</span>
      <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>{{ $listing->city }} - {{ $listing->state }}, {{ $listing->country }}</span>
      <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 11l9-8 9 8"/><path d="M5 10v10h14V10"/></svg>{{ $listing->type }}</span>
      @if($listing->interior)<span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M9 3v18M3 9h18"/></svg>{{ $listing->interior }}</span>@endif
    </div>

    <div class="sd-grid">
      {{-- Left --}}
      <div>
        <div class="sd-block">
          <h2>{{ __('Description') }}</h2>
          <p class="sd-desc">{{ $listing->details }}</p>
        </div>

        <div class="sd-block">
          <h2>{{ __('Overview') }}</h2>
          <div class="sd-overview">
            @if($listing->bed_room)<div class="sd-ov"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M2 20v-6a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v6"/><path d="M4 12V7a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v5"/><path d="M2 20h20"/></svg>{{ $listing->bed_room }} {{ __('Bedrooms') }}</div>@endif
            @if($listing->bath_room)<div class="sd-ov"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M4 12V6a2 2 0 0 1 4 0M2 12h20v3a5 5 0 0 1-5 5H7a5 5 0 0 1-5-5z"/></svg>{{ $listing->bath_room }} {{ __('Bathrooms') }}</div>@endif
            @if($listing->kitchen_room)<div class="sd-ov"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M6 3h12v18H6z"/><path d="M6 9h12M9 3v6"/></svg>{{ $listing->kitchen_room }} {{ __('Kitchen') }}</div>@endif
            @if($listing->dining_room)<div class="sd-ov"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18M8 3v6a3 3 0 0 1-5 0M18 3c-1.5 0-3 2-3 5s1.5 4 3 4v9"/></svg>{{ $listing->dining_room }} {{ __('Dining Room') }}</div>@endif
            @if($listing->living_room)<div class="sd-ov"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M4 12v-2a2 2 0 0 1 4 0M4 12h16M4 12v5m16-7v-2a2 2 0 0 0-4 0m4 4v5M2 12a2 2 0 0 1 4 0v3h12v-3a2 2 0 0 1 4 0"/></svg>{{ $listing->living_room }} {{ __('Living Room') }}</div>@endif
            @if($listing->storage_room)<div class="sd-ov"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M4 8l8-5 8 5v12H4z"/><path d="M4 12h16M9 20v-6h6v6"/></svg>{{ $listing->storage_room }} {{ __('Storage Room') }}</div>@endif
            @foreach (explode(',', $listing->other_room) as $room)
              @if(trim($room) !== '')<div class="sd-ov"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/></svg>{{ trim($room) }}</div>@endif
            @endforeach
          </div>
        </div>

        @if(count(json_decode($listing->amenities) ?? []))
          <div class="sd-block">
            <h2>{{ __('Amenities') }}</h2>
            <div class="sd-amenities">
              @foreach (json_decode($listing->amenities) ?? [] as $amenity)
                <div class="sd-am"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>{{ getPropertyAmenities($amenity) }}</div>
              @endforeach
            </div>
          </div>
        @endif
      </div>

      {{-- Right: agent + map --}}
      <div>
        <div class="sd-side">
          <div class="sd-agent">
            <img src="{{ assetUrl($listing->folder_name . '/' . $listing->file_name) }}" alt="{{ $listing->first_name }} {{ $listing->last_name }}"
                 onerror="this.onerror=null;this.src='https://ui-avatars.com/api/?name={{ urlencode(trim($listing->first_name.' '.$listing->last_name)) }}&background=185FA5&color=fff';">
            <div>
              <h3>{{ trim($listing->first_name . ' ' . $listing->last_name) ?: __('Agent') }}</h3>
              @if($listing->contact_number)<a href="tel:{{ $listing->contact_number }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3 19.5 19.5 0 0 1-6-6 19.8 19.8 0 0 1-3-8.6A2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1.9.3 1.8.6 2.6a2 2 0 0 1-.5 2.1L8.1 9.9a16 16 0 0 0 6 6l1.5-1.1a2 2 0 0 1 2.1-.5c.8.3 1.7.5 2.6.6a2 2 0 0 1 1.7 2z"/></svg>{{ $listing->contact_number }}</a>@endif
              @if($listing->email)<a href="mailto:{{ $listing->email }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-10 6L2 7"/></svg>{{ $listing->email }}</a>@endif
            </div>
          </div>
          <hr>
          <div id="map"></div>
          <button class="sd-contact-btn" data-bs-toggle="modal" data-bs-target="#contactModal" data-bs-whatever="@mdo">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="16" height="16"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
            {{ __('Contact Agency') }}
          </button>
        </div>
      </div>
    </div>

    {{-- Advantages --}}
    @if(count(json_decode($listing->advantage) ?? []))
      <div class="sd-sec">
        <h2>{{ __('Advantages') }}</h2>
        <div class="sd-advantages">
          @foreach (json_decode($listing->advantage) ?? [] as $advantage)
            <div class="sd-adv"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>{{ getPropertyAdvantages($advantage) }}</div>
          @endforeach
        </div>
      </div>
    @endif

    {{-- Nearby --}}
    @if(count($information))
      <div class="sd-sec">
        <h2>{{ __('Nearby Information') }}</h2>
        <div class="sd-cards">
          @foreach ($information as $info)
            <div class="sd-card">
              <div class="sd-card__media"><img src="{{ assetUrl($info->folder_name . '/' . $info->file_name) }}" alt="{{ $info->name }}" loading="lazy"></div>
              <div class="sd-card__body">
                <h3>{{ $info->name }}</h3>
                <p class="sd-nearby__meta">{!! Str::limit(strip_tags($info->details), 100, '…') !!}</p>
                <p class="sd-nearby__meta"><b>{{ __('Distance') }}:</b> {{ $info->distance }}<br>
                  <b>{{ __('Contact') }}:</b> <a href="tel:{{ $info->contact_number }}" style="color:var(--cs-blue)">{{ $info->contact_number }}</a></p>
              </div>
            </div>
          @endforeach
        </div>
      </div>
    @endif

    {{-- Related --}}
    @if(count($relatedListings))
      <div class="sd-sec">
        <h2>{{ __('Related Properties') }}</h2>
        <div class="sd-cards">
          @foreach ($relatedListings as $relatedListing)
            <div class="sd-card">
              <div class="sd-card__media">
                <img src="{{ assetUrl($relatedListing->folder_name . '/' . $relatedListing->file_name) }}" alt="{{ $relatedListing->name }}" loading="lazy">
                <span class="sd-card__tag">{{ currencyPrice($relatedListing->price) }}@if($relatedListing->price_duration_type == DURATION_TYPE_MONTHLY)/{{ __('Month') }}@elseif($relatedListing->price_duration_type == DURATION_TYPE_YEARLY)/{{ __('Year') }}@endif</span>
              </div>
              <div class="sd-card__body">
                <h3>{{ $relatedListing->name }}</h3>
                <div class="sd-card__loc"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>{{ $relatedListing->address }}</div>
                <div class="sd-card__row">
                  <span>{{ $relatedListing->interior }} {{ __('Sqft') }}</span>
                  <span>{{ $relatedListing->bed_room }} {{ __('Rooms') }}</span>
                  <span>{{ $relatedListing->type }}</span>
                </div>
                <div class="sd-card__cta"><a href="{{ route('listing.details', $relatedListing->slug) }}">{{ __('View details') }}<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" width="14" height="14"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a></div>
              </div>
            </div>
          @endforeach
        </div>
      </div>
    @endif
  </div>

  {{-- Contact modal --}}
  <div class="modal fade sd-modal" id="contactModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content">
        <div class="sd-modal-head">
          <h5>{{ __('Contact about') }} {{ $listing->name }}</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <form class="ajax" action="{{ route('listing.contact.store') }}" method="POST" data-handler="getShowMessage">
          @csrf
          <input type="hidden" name="id" value="{{ $listing->id }}">
          <div class="sd-modal-body">
            <div class="sd-fgrid">
              <div><label>{{ __('Full Name') }}</label><input class="form-control" type="text" name="name" placeholder="{{ __('Full Name') }}"></div>
              <div><label>{{ __('Email') }}</label><input class="form-control" type="email" name="email" placeholder="{{ __('Email') }}"></div>
              <div><label>{{ __('Phone Number') }}</label><input class="form-control" type="text" name="phone" placeholder="{{ __('Phone Number') }}"></div>
              <div><label>{{ __('Profession') }}</label><input class="form-control" type="text" name="profession" placeholder="{{ __('Profession') }}"></div>
              <div class="sd-f-full"><label>{{ __('Details') }}</label><textarea name="details" class="form-control" rows="5" placeholder="{{ __('Details') }}"></textarea></div>
            </div>
          </div>
          <div class="sd-modal-foot">
            <button type="submit" class="sd-submit">{{ __('Submit') }}</button>
            <button type="button" class="sd-cancel" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection

@push('style')
    <link href='https://api.mapbox.com/mapbox.js/v3.3.1/mapbox.css' rel='stylesheet' />
    <link href='https://api.mapbox.com/mapbox.js/plugins/leaflet-markercluster/v1.0.0/MarkerCluster.css' rel='stylesheet' />
    <link href='https://api.mapbox.com/mapbox.js/plugins/leaflet-markercluster/v1.0.0/MarkerCluster.Default.css' rel='stylesheet' />
@endpush
@push('script')
    <script src='https://api.mapbox.com/mapbox.js/v3.3.1/mapbox.js'></script>
    <script src='https://api.mapbox.com/mapbox.js/plugins/leaflet-markercluster/v1.0.0/leaflet.markercluster.js'></script>
    <script>
        $('.property-slider-area').owlCarousel({
            loop: true, margin: 24, nav: true,
            navText: ['<i class="fas fa-chevron-left"></i>', '<i class="fas fa-chevron-right"></i>'],
            responsive: { 0: { items: 1 }, 600: { items: 2 }, 1000: { items: 2 } }
        });

        L.mapbox.accessToken = "{{ getOption('map_api_key') }}";
        const mapData = @json($mapData);
        window.map = L.mapbox.map('map')
            .setView([-37.82, 175.215], 14)
            .addLayer(L.mapbox.styleLayer('mapbox://styles/mapbox/dark-v11'));

        window.markers = L.markerClusterGroup({
            iconCreateFunction: function(cluster) {
                var childCount = cluster.getChildCount();
                return new L.DivIcon({
                    html: '<div><span>' + childCount + '</span></div>',
                    className: 'd-flex justify-content-center align-items-center bg-theme font-bold rounded-5 text-white',
                    iconSize: [40, 40]
                });
            }
        });
        if (mapData.length > 0) {
            mapData.forEach(feature => {
                var popupContent = feature.properties.popup;
                var marker = L.marker(new L.LatLng(feature.coordinates.lat, feature.coordinates.long), {
                    icon: L.icon({ iconUrl: feature.properties.image, iconSize: [40, 40], className: 'border border-3 border-light rounded-5' }),
                    title: feature.properties.name
                });
                marker.bindPopup(popupContent);
                window.markers.addLayer(marker);
            });
            window.map.addLayer(window.markers);
            window.map.fitBounds(window.markers.getBounds());
        }
    </script>
@endpush
