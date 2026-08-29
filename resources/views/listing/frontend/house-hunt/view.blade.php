@extends('saas.frontend.layouts.app')

@section('content')
@php
    $pageTitle = 'View';
    $user = Auth::check() ? Auth::user() : null;
@endphp

{{--
    House Hunt — single property detail. Restyled 2026-08 to CS dark-premium (matches
    the listing page). ALL behaviour preserved: property carousel, per-unit lightbox
    (openImageModal), the shared apply modal (openApplyModal) posting to
    tenant.house-hunt.application.submit, every field name, and the AJAX handlers.
--}}

<style>
  main{background:#0E1218}
  .hd{
    --paper:#0E1218; --paper-2:#141922; --card:#161C26; --card-2:#1B222E;
    --stone-900:#EDEAE3; --stone-700:#C4C0B7; --stone-500:#9A958A; --stone-400:#7C776C;
    --line:#242B36;
    --cs-blue:#185FA5; --cs-blue-2:#1c72c2; --cs-blue-tint:#12283F;
    --amber:#E7A339; --amber-2:#f0af49;
    --shadow:0 20px 46px -22px rgba(0,0,0,.6); --shadow-sm:0 10px 26px -16px rgba(0,0,0,.55);
    --serif:Georgia,'Iowan Old Style','Times New Roman',serif;
    --sans:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;
    color-scheme:dark;font-family:var(--sans);color:var(--stone-900)}
  .hd *{box-sizing:border-box}
  .hd a{text-decoration:none}
  .hd-wrap{max-width:1200px;margin:0 auto;padding:0 24px}

  /* Hero carousel */
  .hd-hero{position:relative;height:480px;overflow:hidden}
  .hd-hero .carousel,.hd-hero .carousel-inner,.hd-hero .carousel-item{height:100%}
  .hd-hero img{width:100%;height:100%;object-fit:cover}
  .hd-hero__scrim{position:absolute;inset:0;z-index:1;
    background:linear-gradient(180deg,rgba(10,13,18,.35),rgba(10,13,18,.82)),
      radial-gradient(700px 340px at 82% -10%, rgba(231,163,57,.14), transparent 60%)}
  .hd-hero__inner{position:absolute;inset:0;z-index:2;display:flex;flex-direction:column;
    align-items:center;justify-content:flex-end;text-align:center;padding:0 24px 64px}
  .hd-hero__inner h1{font-family:var(--serif);font-weight:600;letter-spacing:-.015em;
    font-size:clamp(30px,4.6vw,52px);color:#F2EFEA;margin:0;text-shadow:0 2px 14px rgba(0,0,0,.55);text-wrap:balance}
  .hd-loc{display:inline-flex;align-items:center;gap:8px;margin-top:12px;color:#E4DED2;font-size:17px;
    text-shadow:0 1px 8px rgba(0,0,0,.6)}
  .hd-loc svg{width:17px;height:17px;color:var(--amber)}
  .hd-hero__badge{position:absolute;top:130px;right:20px;z-index:2;background:rgba(24,95,165,.9);color:#fff;
    font-size:12px;font-weight:600;padding:7px 14px;border-radius:99px}

  /* Back link */
  .hd-back{display:inline-flex;align-items:center;gap:8px;color:var(--stone-500);font-size:14px;font-weight:600;
    margin:26px 0 4px;transition:.15s}
  .hd-back:hover{color:#fff}
  .hd-back svg{width:16px;height:16px}

  /* Units */
  .hd-sec-head{margin:28px 0 4px}
  .hd-sec-head h2{font-size:clamp(22px,3vw,30px);font-weight:750;color:var(--stone-900)}
  .hd-sec-head p{margin-top:6px;color:var(--stone-500);font-size:15px}
  .hd-units{display:grid;grid-template-columns:repeat(3,1fr);gap:24px;padding:26px 0 90px}
  @media(max-width:900px){.hd-units{grid-template-columns:repeat(2,1fr)}}
  @media(max-width:600px){.hd-units{grid-template-columns:1fr}}
  .hd-card{background:var(--card);border:1px solid var(--line);border-radius:16px;overflow:hidden;
    box-shadow:var(--shadow-sm);display:flex;flex-direction:column;transition:.2s}
  .hd-card:hover{transform:translateY(-5px);border-color:color-mix(in srgb,var(--cs-blue) 45%,var(--line))}
  .hd-card__media{position:relative;aspect-ratio:16/10;overflow:hidden;cursor:pointer}
  .hd-card__media img{width:100%;height:100%;object-fit:cover;display:block;transition:.35s}
  .hd-card__media:hover img{transform:scale(1.04)}
  .hd-photos{position:absolute;bottom:10px;right:10px;z-index:2;background:rgba(0,0,0,.55);backdrop-filter:blur(4px);
    color:#fff;font-size:11.5px;font-weight:600;padding:5px 10px;border-radius:99px;display:inline-flex;align-items:center;gap:6px}
  .hd-photos svg{width:13px;height:13px}
  .hd-card__body{padding:20px;display:flex;flex-direction:column;flex:1}
  .hd-card__title{font-size:18px;font-weight:750;color:var(--stone-900);margin:0}
  .hd-card__prop{display:flex;align-items:center;gap:7px;color:var(--stone-500);font-size:13.5px;margin-top:6px}
  .hd-card__prop svg{width:14px;height:14px;color:var(--cs-blue);flex:0 0 auto}
  .hd-specs{display:flex;gap:16px;margin-top:16px;flex-wrap:wrap}
  .hd-spec{display:inline-flex;align-items:center;gap:7px;color:var(--stone-700);font-size:14px}
  .hd-spec svg{width:18px;height:18px;color:var(--stone-500)}
  .hd-amenities{margin-top:14px;display:flex;flex-wrap:wrap;gap:6px}
  .hd-amenity{font-size:11.5px;color:var(--stone-700);background:var(--card-2);border:1px solid var(--line);
    padding:4px 10px;border-radius:99px}
  .hd-foot{margin-top:auto;padding-top:16px;border-top:1px solid var(--line);
    display:flex;align-items:center;justify-content:space-between;gap:12px;margin-top:16px}
  .hd-owner{display:flex;align-items:center;gap:10px;min-width:0}
  .hd-owner img{width:38px;height:38px;border-radius:50%;object-fit:cover;flex:0 0 auto}
  .hd-owner span{font-size:14px;font-weight:600;color:var(--stone-900);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
  .hd-rent{color:var(--amber);font-weight:750;font-size:15.5px;white-space:nowrap;text-align:right}
  .hd-rent small{display:block;color:var(--stone-500);font-weight:500;font-size:11px}
  .hd-apply{margin-top:16px;width:100%;display:inline-flex;align-items:center;justify-content:center;gap:8px;
    background:var(--cs-blue);color:#fff !important;font-weight:650;font-size:15px;padding:13px;border-radius:11px;
    border:none;cursor:pointer;transition:.18s}
  .hd-apply:hover{background:var(--cs-blue-2);transform:translateY(-1px)}
  .hd-empty{grid-column:1/-1;text-align:center;padding:60px 20px;color:var(--stone-500)}

  /* ── Modals (dark) ─────────────────────────────────────────────────── */
  .hd-modal .modal-content{background:var(--card);border:1px solid var(--line);border-radius:18px;color:var(--stone-900);box-shadow:var(--shadow)}
  .hd-modal .modal-header{border-bottom:1px solid var(--line);padding:20px 24px}
  .hd-modal .modal-title{color:var(--stone-900);font-weight:750;font-size:19px}
  .hd-modal .modal-body{padding:22px 24px}
  .hd-modal .btn-close{filter:invert(1) grayscale(1) brightness(2)}
  .hd-unitinfo{background:var(--cs-blue-tint);border:1px solid color-mix(in srgb,var(--cs-blue) 40%,var(--line));
    border-radius:12px;padding:14px 16px;color:var(--stone-700);font-size:14px;line-height:1.7;margin-bottom:22px}
  .hd-unitinfo strong{color:var(--stone-900)}
  .hd-formsec{font-size:12px;font-weight:750;letter-spacing:.08em;text-transform:uppercase;color:var(--cs-blue);
    margin:0 0 14px}
  .hd-grid2{display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:22px}
  .hd-grid3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:14px}
  @media(max-width:600px){.hd-grid2,.hd-grid3{grid-template-columns:1fr}}
  .hd-col-full{grid-column:1/-1}
  .hd-field label{display:block;font-size:11.5px;font-weight:700;letter-spacing:.04em;text-transform:uppercase;
    color:var(--stone-500);margin-bottom:6px}
  .hd-field label .req{color:var(--amber)}
  .hd-modal .form-control{width:100%;font-family:inherit;font-size:14.5px;padding:11px 13px;border-radius:10px;
    border:1px solid var(--line);background:var(--paper-2);color:var(--stone-900)}
  .hd-modal .form-control:focus{outline:2px solid var(--cs-blue);outline-offset:1px;border-color:transparent;background:var(--paper-2);color:var(--stone-900);box-shadow:none}
  .hd-modal .form-control.is-invalid{border-color:#e5484d}
  .hd-modal .modal-footer{border-top:1px solid var(--line);padding:18px 24px;flex-direction:column;gap:10px}
  .hd-submit{width:100%;background:var(--cs-blue);color:#fff !important;font-weight:650;font-size:15px;padding:13px;
    border:none;border-radius:11px;cursor:pointer;transition:.18s}
  .hd-submit:hover{background:var(--cs-blue-2)}
  .hd-close{width:100%;background:transparent;color:var(--stone-500) !important;font-weight:600;font-size:14.5px;
    padding:12px;border:1px solid var(--line);border-radius:11px;cursor:pointer;transition:.18s}
  .hd-close:hover{border-color:var(--stone-400);color:var(--stone-900) !important}
  /* lightbox stays dark */
  #imageLightboxModal .modal-content{background:#0B0F15;border:1px solid var(--line);border-radius:16px}
</style>

<div class="hd">
  @php
      $thumb              = trim($property->thumbnail_image ?? '');
      $systemPlaceholder  = asset('assets/images/no-image.jpg');
      $hasHeroImage       = $thumb && $thumb !== $systemPlaceholder;
      $heroImage          = $hasHeroImage ? $thumb : asset('assets/images/interior.jpg');
      $additionalImages   = $property->propertyImages ?? [];
  @endphp

  {{-- Hero carousel --}}
  <div class="hd-hero">
    <div id="propertyCarousel" class="carousel slide" data-bs-ride="carousel">
      <div class="carousel-inner">
        <div class="carousel-item active">
          <img src="{{ $heroImage }}" alt="{{ $property['name'] }}" loading="eager">
        </div>
        @foreach($additionalImages as $propertyImage)
          <div class="carousel-item">
            <img src="{{ $propertyImage->single_image ?? asset('assets/images/no-image.jpg') }}" alt="Property image" loading="lazy">
          </div>
        @endforeach
      </div>
    </div>
    <div class="hd-hero__scrim"></div>
    <div class="hd-hero__inner">
      <h1>{{ $property['name'] }}</h1>
      <span class="hd-loc">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
        {{ $property['city_id'] ?? '' }}{{ (!empty($property['city_id']) && !empty($property['state_id'])) ? ', ' : '' }}{{ $property['state_id'] ?? '' }}
      </span>
    </div>
    <span class="hd-hero__badge">{{ $units->count() }} {{ __('vacant units') }}</span>
  </div>

  <div class="hd-wrap">
    <a href="{{ route('house.hunt') }}" class="hd-back">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M11 18l-6-6 6-6"/></svg>
      {{ __('Back to House Hunt') }}
    </a>

    <div class="hd-sec-head">
      <h2>{{ __('Available units') }}</h2>
      <p>{{ __('Browse the vacant units below and apply in a couple of minutes.') }}</p>
    </div>

    {{-- Unit cards --}}
    <div class="hd-units">
      @forelse ($units as $unit)
        @php
            $unitImages = $unit->images;
            $hasImages  = $unitImages->isNotEmpty();
            $firstImage = $hasImages
                ? asset('storage/' . $unitImages->first()->folder_name . '/' . $unitImages->first()->file_name)
                : asset('assets/images/unit.png');
            $imageUrls  = $unitImages->map(fn($img) =>
                asset('storage/' . $img->folder_name . '/' . $img->file_name)
            )->toJson();
            $ownerName  = trim(($property['owner_first_name'] ?? 'Agent') . ' ' . ($property['owner_last_name'] ?? ''));
            $ownerPhoto = $unit->property?->owner?->profile_photo
                ? asset($unit->property->owner->profile_photo)
                : 'https://ui-avatars.com/api/?name=' . urlencode($ownerName) . '&background=185FA5&color=fff';
        @endphp

        <div class="hd-card">
          <div class="hd-card__media" onclick="openImageModal({{ $unit->id }}, {{ $imageUrls }})">
            <img src="{{ $firstImage }}" alt="Unit {{ $unit->unit_name ?? $unit->id }}" loading="lazy"
                 onerror="this.onerror=null;this.src='{{ asset('assets/images/unit.png') }}';">
            @if($hasImages && $unitImages->count() > 1)
              <span class="hd-photos">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg>
                {{ $unitImages->count() }} {{ __('photos') }}
              </span>
            @endif
          </div>

          <div class="hd-card__body">
            <h3 class="hd-card__title">{{ __('Unit') }} {{ $unit->unit_name ?? $unit->id }}</h3>
            <div class="hd-card__prop">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 11l9-8 9 8"/><path d="M5 10v10h14V10"/></svg>
              {{ $property['name'] }}
            </div>

            <div class="hd-specs">
              <span class="hd-spec">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M2 20v-6a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v6"/><path d="M4 12V7a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v5"/><path d="M2 20h20"/></svg>
                {{ $unit->bedroom ?? 'N/A' }} {{ __('bed') }}
              </span>
              <span class="hd-spec">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M4 12V6a2 2 0 0 1 4 0M2 12h20v3a5 5 0 0 1-5 5H7a5 5 0 0 1-5-5z"/></svg>
                {{ $unit->bath ?? 'N/A' }} {{ __('bath') }}
              </span>
              <span class="hd-spec">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M9 17V7h4a3 3 0 0 1 0 6H9"/></svg>
                {{ $unit->parking ?? 'N/A' }} {{ __('parking') }}
              </span>
            </div>

            @if(!empty($unit->amenities))
              <div class="hd-amenities">
                @foreach(array_slice(explode(',', $unit->amenities), 0, 6) as $amenity)
                  @if(trim($amenity) !== '')<span class="hd-amenity">{{ trim($amenity) }}</span>@endif
                @endforeach
              </div>
            @endif

            <div class="hd-foot">
              <div class="hd-owner">
                <img src="{{ $ownerPhoto }}" alt="Owner" loading="lazy">
                <span>{{ $ownerName ?: __('Agent') }}</span>
              </div>
              <div class="hd-rent">KES {{ number_format($unit->general_rent) }}<small>{{ __('per month') }}</small></div>
            </div>

            <button type="button" class="hd-apply"
                    onclick="openApplyModal(
                        {{ $unit->id }},
                        {{ json_encode($unit->unit_name ?? 'Unit ' . $unit->id) }},
                        {{ json_encode($property['name']) }},
                        {{ json_encode($property['owner_first_name'] . ' ' . $property['owner_last_name']) }},
                        {{ $unit->general_rent }},
                        {{ $property['id'] }}
                    )">
              {{ __('Apply for this unit') }}
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" width="15" height="15"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
            </button>
          </div>
        </div>
      @empty
        <div class="hd-empty"><p>{{ __('No vacant units available for this property.') }}</p></div>
      @endforelse
    </div>
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

  {{-- Shared application modal --}}
  <div class="modal fade hd-modal" id="sharedApplyModal" tabindex="-1" aria-hidden="true">
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
            <div class="hd-unitinfo" id="applyUnitInfo"></div>

            <p class="hd-formsec">{{ __('Personal Information') }}</p>
            <div class="hd-grid2">
              <div class="hd-field"><label>{{ __('First Name') }} <span class="req">*</span></label>
                <input type="text" name="first_name" class="form-control" value="{{ old('first_name', $user?->first_name) }}" required></div>
              <div class="hd-field"><label>{{ __('Last Name') }} <span class="req">*</span></label>
                <input type="text" name="last_name" class="form-control" value="{{ old('last_name', $user?->last_name) }}" required></div>
              <div class="hd-field"><label>{{ __('Phone Number') }} <span class="req">*</span></label>
                <input type="text" name="contact_number" class="form-control" value="{{ old('contact_number', $user?->contact_number) }}" required></div>
              <div class="hd-field"><label>{{ __('Email') }} <span class="req">*</span></label>
                <input type="email" name="email" class="form-control" value="{{ old('email', $user?->email) }}" required></div>
              <div class="hd-field"><label>{{ __('Job') }}</label>
                <input type="text" name="job" class="form-control" value="{{ old('job', $user?->tenant?->job) }}"></div>
              <div class="hd-field"><label>{{ __('Age') }}</label>
                <input type="number" name="age" class="form-control" value="{{ old('age', $user?->tenant?->age) }}"></div>
              <div class="hd-field hd-col-full"><label>{{ __('Family Members') }}</label>
                <input type="number" name="family_member" class="form-control" value="{{ old('family_member', $user?->tenant?->family_member) }}"></div>
            </div>

            <p class="hd-formsec">{{ __('Address Information') }}</p>
            <div class="hd-grid3">
              <div class="hd-field hd-col-full"><label>{{ __('Address') }} <span class="req">*</span></label>
                <input type="text" name="permanent_address" class="form-control" required></div>
              <div class="hd-field"><label>{{ __('Country') }} <span class="req">*</span></label>
                <input type="text" name="permanent_country_id" class="form-control" required></div>
              <div class="hd-field"><label>{{ __('State') }} <span class="req">*</span></label>
                <input type="text" name="permanent_state_id" class="form-control" required></div>
              <div class="hd-field"><label>{{ __('City') }} <span class="req">*</span></label>
                <input type="text" name="permanent_city_id" class="form-control" required></div>
              <div class="hd-field hd-col-full"><label>{{ __('Zip Code') }} <span class="req">*</span></label>
                <input type="text" name="permanent_zip_code" class="form-control" required></div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="submit" class="hd-submit">{{ __('Submit Application') }}</button>
            <button type="button" class="hd-close" data-bs-dismiss="modal">{{ __('Close') }}</button>
          </div>
        </div>
      </form>
    </div>
  </div>

</div>
@endsection

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
// ── Shared image lightbox ────────────────────────────────────────────────────
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

// ── Shared application modal ─────────────────────────────────────────────────
function openApplyModal(unitId, unitName, propertyName, agentName, rent, propertyId) {
    document.getElementById('applyUnitId').value    = unitId;
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

// ── AJAX form submission ─────────────────────────────────────────────────────
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

    document.getElementById('sharedApplyModal').addEventListener('hidden.bs.modal', function () {
        const form = this.querySelector('form');
        if (form) form.reset();
    });
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
