@extends('saas.frontend.layouts.app')
@section('content')
@php $pageTitle = 'House Hunt'; @endphp

{{--
    House Hunt — public rentals + sales listing. Restyled 2026-08 onto the CS dark-premium
    language (matches the home page). All functionality preserved: GET filter form to
    route('house.hunt') with fields type/state/city/min_price/max_price, the AJAX id hooks
    (#listingType/#stateSelect/#citySelect/#priceFilter), card routes, and pagination.
--}}

<style>
  /* The frontend <body> has a light/grey background; paint the layout's content
     wrapper dark for this page so the gutters around the centered content stay on-brand
     (this <style> only loads on House Hunt, so it's effectively page-scoped). */
  main{background:#0E1218}
  .hh{
    --paper:#0E1218; --paper-2:#141922; --card:#161C26;
    --hero-dark:#0E1218;
    --stone-900:#EDEAE3; --stone-700:#C4C0B7; --stone-500:#9A958A; --stone-400:#7C776C;
    --line:#242B36;
    --cs-blue:#185FA5; --cs-blue-2:#1c72c2; --cs-blue-deep:#0F4A84; --cs-blue-tint:#12283F;
    --amber:#E7A339; --amber-2:#f0af49;
    --green:#1D9E75; --green-tint:#123027;
    --shadow:0 20px 46px -22px rgba(0,0,0,.6); --shadow-sm:0 10px 26px -16px rgba(0,0,0,.55);
    --serif:Georgia,'Iowan Old Style','Times New Roman',serif;
    --sans:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;
    color-scheme:dark;font-family:var(--sans);color:var(--stone-900);background:var(--paper)}
  .hh *{box-sizing:border-box}
  .hh a{text-decoration:none}
  .hh-wrap{max-width:1200px;margin:0 auto;padding:0 24px}

  /* Hero */
  .hh-hero{position:relative;overflow:hidden;min-height:440px;display:flex;align-items:center;
    background:linear-gradient(180deg,rgba(10,13,18,.62),rgba(10,13,18,.86)),
      url('{{ asset('assets/images/exterior.jpg') }}');
    background-size:cover;background-position:center}
  .hh-hero::after{content:"";position:absolute;inset:0;pointer-events:none;
    background:radial-gradient(760px 360px at 82% -10%, rgba(231,163,57,.16), transparent 60%),
               radial-gradient(760px 420px at 8% 6%, rgba(24,95,165,.28), transparent 55%)}
  .hh-hero__inner{position:relative;z-index:2;text-align:center;width:100%;padding:150px 24px 96px}
  .hh-hero .eyebrow{font-size:12.5px;font-weight:700;letter-spacing:.16em;text-transform:uppercase;color:var(--amber)}
  .hh-hero h1{font-family:var(--serif);font-weight:600;letter-spacing:-.015em;
    font-size:clamp(34px,5.4vw,58px);color:#F2EFEA;margin:14px 0 0;text-wrap:balance}
  .hh-hero p{margin:16px auto 0;max-width:560px;font-size:18px;line-height:1.55;color:#C7C0B4}

  /* Filter card — floats up over the hero base */
  .hh-filter{background:var(--card);border:1px solid var(--line);border-radius:18px;box-shadow:var(--shadow);
    padding:22px;margin:-52px auto 0;max-width:1100px;position:relative;z-index:5}
  .hh-filter form{display:flex;flex-direction:column;gap:16px}
  .hh-fields{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;align-items:end}
  @media(max-width:820px){.hh-fields{grid-template-columns:1fr 1fr}}
  @media(max-width:520px){.hh-fields{grid-template-columns:1fr}}
  .hh-field label{display:block;font-size:11.5px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;
    color:var(--stone-500);margin-bottom:7px}
  .hh-select,.hh-input{width:100%;font-family:inherit;font-size:14.5px;padding:11px 13px;border-radius:10px;
    border:1px solid var(--line);background:var(--paper-2);color:var(--stone-900)}
  .hh-select:focus,.hh-input:focus{outline:2px solid var(--cs-blue);outline-offset:1px;border-color:transparent}
  .hh-price{display:none}
  .hh-price.show{display:block}
  .hh-price .pair{display:flex;gap:10px}
  .hh-actions{display:flex;gap:12px;justify-content:center;flex-wrap:wrap}
  .hh-btn{display:inline-flex;align-items:center;gap:8px;font-weight:650;font-size:15px;padding:12px 26px;
    border-radius:11px;border:1px solid transparent;cursor:pointer;transition:.18s}
  .hh-btn--blue{background:var(--cs-blue);color:#fff !important;box-shadow:0 10px 24px -12px rgba(24,95,165,.7)}
  .hh-btn--blue:hover{background:var(--cs-blue-2);transform:translateY(-2px);color:#fff !important}
  .hh-btn--ghost{background:transparent;color:var(--stone-700) !important;border-color:var(--line)}
  .hh-btn--ghost:hover{border-color:var(--cs-blue);color:#fff !important}

  /* Listings */
  .hh-listings{padding:64px 0 80px}
  .hh-count{color:var(--stone-500);font-size:14.5px;margin-bottom:22px}
  .hh-count b{color:var(--stone-900)}
  .hh-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:24px}
  @media(max-width:900px){.hh-grid{grid-template-columns:repeat(2,1fr)}}
  @media(max-width:600px){.hh-grid{grid-template-columns:1fr}}
  .hh-card{background:var(--card);border:1px solid var(--line);border-radius:16px;overflow:hidden;
    box-shadow:var(--shadow-sm);transition:.2s;display:flex;flex-direction:column}
  .hh-card:hover{transform:translateY(-5px);border-color:color-mix(in srgb,var(--cs-blue) 45%,var(--line))}
  .hh-card__media{position:relative;aspect-ratio:16/10;overflow:hidden}
  .hh-card__media img{width:100%;height:100%;object-fit:cover;display:block;transition:.35s}
  .hh-card:hover .hh-card__media img{transform:scale(1.04)}
  .hh-badge{position:absolute;top:12px;left:12px;z-index:2;font-size:11.5px;font-weight:700;letter-spacing:.03em;
    padding:6px 12px;border-radius:99px;backdrop-filter:blur(6px)}
  .hh-badge--rent{background:rgba(24,95,165,.92);color:#fff}
  .hh-badge--sale{background:rgba(29,158,117,.92);color:#fff}
  .hh-card__body{padding:20px;display:flex;flex-direction:column;flex:1}
  .hh-card__title{font-size:18px;font-weight:700;color:var(--stone-900);line-height:1.3;margin:0}
  .hh-card__addr{display:flex;align-items:center;gap:7px;color:var(--stone-500);font-size:14px;margin-top:8px}
  .hh-card__addr svg{width:15px;height:15px;color:var(--cs-blue);flex:0 0 auto}
  .hh-meta{margin-top:16px;display:flex;flex-direction:column;gap:9px}
  .hh-meta__row{display:flex;align-items:center;gap:9px;font-size:14px;color:var(--stone-700)}
  .hh-meta__row svg{width:17px;height:17px;color:var(--stone-500);flex:0 0 auto}
  .hh-meta__row b{color:var(--stone-900);font-weight:650}
  .hh-price-tag{color:var(--amber);font-weight:750}
  .hh-card__cta{margin-top:auto;padding-top:18px}
  .hh-card__cta a{display:flex;align-items:center;justify-content:center;gap:8px;width:100%;
    background:var(--cs-blue);color:#fff !important;font-weight:650;font-size:14.5px;padding:12px;border-radius:11px;transition:.18s}
  .hh-card__cta a:hover{background:var(--cs-blue-2)}
  .hh-card--sale .hh-card__cta a{background:var(--green)}
  .hh-card--sale .hh-card__cta a:hover{background:#178a64}

  /* Empty + pagination */
  .hh-empty{text-align:center;padding:80px 20px;color:var(--stone-500)}
  .hh-empty svg{width:46px;height:46px;color:var(--stone-400);margin-bottom:14px}
  .hh .pagination{display:flex;gap:6px;justify-content:center;list-style:none;padding:0;margin:44px 0 0;flex-wrap:wrap}
  .hh .page-link{display:inline-flex;align-items:center;justify-content:center;min-width:40px;height:40px;padding:0 12px;
    border-radius:10px;border:1px solid var(--line);background:var(--card);color:var(--stone-700) !important;
    font-size:14px;text-decoration:none;transition:.15s}
  .hh .page-link:hover{border-color:var(--cs-blue);color:#fff !important}
  .hh .page-item.active .page-link{background:var(--cs-blue);border-color:var(--cs-blue);color:#fff !important}
  .hh .page-item.disabled .page-link{opacity:.4}
</style>

<div class="hh">

  {{-- Hero --}}
  <section class="hh-hero">
    <div class="hh-hero__inner">
      <span class="eyebrow">{{ __('For tenants and house hunters') }}</span>
      <h1>{{ __('Find your next home') }}</h1>
      <p>{{ __('Browse vacant homes in real time and connect straight with the landlord. No middlemen, no guesswork.') }}</p>
    </div>
  </section>

  {{-- Filter --}}
  <div class="hh-wrap">
    <div class="hh-filter">
      <form id="filterForm" method="GET" action="{{ route('house.hunt') }}">
        <div class="hh-fields">
          <div class="hh-field">
            <label>{{ __('Listing Type') }}</label>
            <select name="type" id="listingType" class="hh-select">
              <option value="">{{ __('All Types') }}</option>
              <option value="rental" {{ request('type') == 'rental' ? 'selected' : '' }}>{{ __('For Rent') }}</option>
              <option value="sale"   {{ request('type') == 'sale'   ? 'selected' : '' }}>{{ __('For Sale') }}</option>
            </select>
          </div>
          <div class="hh-field">
            <label>{{ __('State') }}</label>
            <select name="state" id="stateSelect" class="hh-select">
              <option value="">{{ __('All States') }}</option>
              @foreach($states as $state)
                <option value="{{ $state }}" {{ request('state') == $state ? 'selected' : '' }}>{{ $state }}</option>
              @endforeach
            </select>
          </div>
          <div class="hh-field">
            <label>{{ __('City') }}</label>
            <select name="city" id="citySelect" class="hh-select">
              <option value="">{{ __('All Cities') }}</option>
              @foreach($cities as $city)
                <option value="{{ $city }}" {{ request('city') == $city ? 'selected' : '' }}>{{ $city }}</option>
              @endforeach
            </select>
          </div>
          <div class="hh-field hh-price" id="priceFilter">
            <label>{{ __('Price Range') }}</label>
            <div class="pair">
              <input type="number" name="min_price" value="{{ request('min_price') }}" class="hh-input" placeholder="{{ __('Min') }}">
              <input type="number" name="max_price" value="{{ request('max_price') }}" class="hh-input" placeholder="{{ __('Max') }}">
            </div>
          </div>
        </div>
        <div class="hh-actions">
          <button type="submit" class="hh-btn hh-btn--blue">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="16" height="16"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
            {{ __('Search homes') }}
          </button>
          <a href="{{ route('house.hunt') }}" class="hh-btn hh-btn--ghost">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="16" height="16"><path d="M3 12a9 9 0 1 0 3-6.7L3 8"/><path d="M3 3v5h5"/></svg>
            {{ __('Reset') }}
          </a>
        </div>
      </form>
    </div>
  </div>

  {{-- Listings --}}
  <div class="hh-wrap hh-listings">
    @if($listings->count())
      <div class="hh-count"><b>{{ $listings->total() }}</b> {{ __('homes available') }}</div>
      <div class="hh-grid">
        @foreach($listings as $listing)
          <div class="hh-card {{ $listing->type == 'sale' ? 'hh-card--sale' : '' }}">
            <div class="hh-card__media">
              <img src="{{ $listing->image }}" alt="{{ $listing->name }}" loading="lazy"
                   onerror="this.onerror=null;this.src='{{ asset('assets/images/property.png') }}';">
              <span class="hh-badge {{ $listing->type == 'sale' ? 'hh-badge--sale' : 'hh-badge--rent' }}">
                {{ $listing->type == 'sale' ? __('For Sale') : __('For Rent') }}
              </span>
            </div>
            <div class="hh-card__body">
              <h3 class="hh-card__title">{{ $listing->name }}</h3>
              <div class="hh-card__addr">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                {{ $listing->type == 'rental' ? $listing->address : $listing->city . ', ' . $listing->state }}
              </div>

              <div class="hh-meta">
                @if($listing->type == 'rental')
                  <div class="hh-meta__row">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M3 11l9-8 9 8"/><path d="M5 10v10h14V10"/></svg>
                    <span><b>{{ $listing->units }}</b> {{ __('vacant units') }}</span>
                  </div>
                  @if($listing->price)
                    <div class="hh-meta__row">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                      <span class="hh-price-tag">{{ __('From') }} {{ $listing->price }}</span>
                    </div>
                  @endif
                @else
                  <div class="hh-meta__row">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="3.2"/><path d="M5 21c0-3.9 3.1-7 7-7s7 3.1 7 7"/></svg>
                    <span>{{ __('Owner') }}: <b>{{ $listing->agent }}</b></span>
                  </div>
                  <div class="hh-meta__row">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                    <span class="hh-price-tag">{{ currencyPrice($listing->price) }}</span>
                  </div>
                  <div class="hh-meta__row">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15 15 0 0 1 0 20 15 15 0 0 1 0-20z"/></svg>
                    <span>{{ $listing->country }}</span>
                  </div>
                @endif
              </div>

              <div class="hh-card__cta">
                <a href="{{ $listing->type == 'sale'
                            ? route('listing.details', $listing->slug)
                            : route('house-hunt.view', ['propertyId' => $listing->slug]) }}">
                  {{ $listing->type == 'sale' ? __('View details') : __('View home') }}
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" width="15" height="15"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                </a>
              </div>
            </div>
          </div>
        @endforeach
      </div>

      @if($listings->hasPages())
        {{ $listings->withQueryString()->links() }}
      @endif
    @else
      <div class="hh-empty">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M3 11l9-8 9 8"/><path d="M5 10v10h14V10"/><line x1="9" y1="21" x2="9" y2="13"/></svg>
        <p>{{ __('No homes available right now. Check back soon, or adjust your filters.') }}</p>
      </div>
    @endif
  </div>

</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const listingType = document.getElementById("listingType");
    const priceFilter = document.getElementById("priceFilter");
    const stateSelect = document.getElementById("stateSelect");
    const citySelect  = document.getElementById("citySelect");

    // Price filter shows only for sales
    function togglePriceFilter() {
        priceFilter.classList.toggle("show", listingType.value === "sale");
    }
    togglePriceFilter();
    listingType.addEventListener("change", togglePriceFilter);

    // Type → update states
    listingType.addEventListener("change", function () {
        fetch(`{{ route('get.filters') }}?type=${this.value}`)
            .then(r => r.json())
            .then(data => {
                stateSelect.innerHTML = '<option value="">{{ __("All States") }}</option>';
                data.states.forEach(s => {
                    const o = document.createElement("option");
                    o.value = o.text = s;
                    stateSelect.appendChild(o);
                });
                citySelect.innerHTML = '<option value="">{{ __("All Cities") }}</option>';
            });
    });

    // State → update cities
    stateSelect.addEventListener("change", function () {
        citySelect.innerHTML = '<option value="">{{ __("All Cities") }}</option>';
        if (!this.value) return;
        fetch(`{{ route('get.cities') }}?state=${this.value}&type=${listingType.value}`)
            .then(r => r.json())
            .then(data => {
                data.forEach(c => {
                    const o = document.createElement("option");
                    o.value = o.text = c;
                    citySelect.appendChild(o);
                });
            });
    });
});
</script>
@endsection
