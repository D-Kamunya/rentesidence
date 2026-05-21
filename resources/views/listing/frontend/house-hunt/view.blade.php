@extends('saas.frontend.layouts.app')

@section('content')
@php
    $pageTitle = 'View';
    $user = Auth::check() ? Auth::user() : null;
@endphp

<link href="https://fonts.googleapis.com/css2?family=Josefin+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Poppins:wght@400;600&display=swap" rel="stylesheet">

{{-- ── Hero carousel (property-level images) ──────────────────────────────── --}}
<div class="property-details-area">
    @php
        $thumb              = trim($property->thumbnail_image ?? '');
        $systemPlaceholder  = asset('assets/images/no-image.jpg');
        $hasHeroImage       = $thumb && $thumb !== $systemPlaceholder;
        $heroImage          = $hasHeroImage ? $thumb : asset('assets/images/interior.jpg');
        $isFallbackHero     = !$hasHeroImage;
        $additionalImages   = $property->propertyImages ?? [];
    @endphp

    <div class="row">
        <div class="col-md-12 position-relative" style="height:450px;">
            <div id="propertyCarousel" class="carousel slide h-100" data-bs-ride="carousel">
                <div class="carousel-inner h-100">
                    <div class="carousel-item active h-100">
                        <img src="{{ $heroImage }}" class="d-block w-100 h-100"
                             style="object-fit:cover;" alt="Hero Image" loading="eager">
                    </div>
                    @foreach($additionalImages as $propertyImage)
                        <div class="carousel-item h-100">
                            <img src="{{ $propertyImage->single_image ?? asset('assets/images/no-image.jpg') }}"
                                 class="d-block w-100 h-100"
                                 style="object-fit:cover;" alt="Property Image" loading="lazy">
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="position-absolute top-0 start-0 w-100 h-100"
                 style="background:rgba(0,0,0,0.45);z-index:1;"></div>
            <div class="d-flex flex-column justify-content-center align-items-center h-100 text-center position-absolute w-100"
                 style="z-index:2;top:0;left:0;">
                <h2 class="property-title fw-bold"
                    style="font-family:'Playfair Display',serif;color:#fff;text-shadow:2px 2px 8px rgba(0,0,0,0.7);">
                    {{ $property['name'] }}
                </h2>
                <div class="d-inline-flex align-items-center justify-content-center gap-2">
                    <i class="fas fa-map-marker-alt text-danger fs-5"></i>
                    <h4 class="mb-0"
                        style="font-family:'Poppins',sans-serif;color:#fff;text-shadow:1px 1px 6px rgba(0,0,0,0.6);">
                        {{ $property['city_id'] ?? '' }}, {{ $property['state_id'] ?? '' }}
                    </h4>
                </div>
            </div>
            @if($isFallbackHero)
                <span class="badge bg-primary position-absolute bottom-0 end-0 m-3 px-4 py-3 fs-6" style="z-index:2;">
                    No Image Provided
                </span>
            @endif
        </div>
    </div>

    {{-- ── Unit cards ── images and apply modal are now shared/lazy ─────────── --}}
    <div class="container-fluid row mt-5 px-md-4 px-lg-5">
        @forelse ($units as $unit)
            @php
                // Use the eager-loaded images relation — no extra queries
                $unitImages  = $unit->images;
                $hasImages   = $unitImages->isNotEmpty();
                $firstImage  = $hasImages
                    ? asset('storage/' . $unitImages->first()->folder_name . '/' . $unitImages->first()->file_name)
                    : asset('assets/images/unit.png');

                // Encode all image URLs once for JS — no per-render overhead
                $imageUrls = $unitImages->map(fn($img) =>
                    asset('storage/' . $img->folder_name . '/' . $img->file_name)
                )->toJson();
            @endphp

            <div class="col-12 col-md-6 col-lg-4 mb-4">
                <div class="property-item rounded overflow-hidden">
                    <div class="single-properties">

                        {{-- Image thumbnail → clicking opens shared lightbox modal --}}
                        <div class="properties-img position-relative" style="cursor:pointer;"
                             onclick="openImageModal({{ $unit->id }}, {{ $imageUrls }})">

                            <img src="{{ $firstImage }}"
                                 alt="Unit {{ $unit->unit_name ?? $unit->id }}"
                                 loading="lazy"
                                 style="width:100%;height:220px;object-fit:cover;border-radius:12px 12px 0 0;">

                            @if($hasImages && $unitImages->count() > 1)
                                <span class="badge bg-dark position-absolute bottom-0 end-0 m-2 px-2 py-1" style="font-size:11px;">
                                    <i class="fas fa-images me-1"></i>{{ $unitImages->count() }} photos
                                </span>
                            @endif

                            @if(!$hasImages)
                                <span class="badge bg-dark position-absolute bottom-0 end-0 m-2 px-3 py-2">
                                    No Image Provided
                                </span>
                            @endif
                        </div>

                        {{-- Card body --}}
                        <div class="card-body p-3" style="font-family:'Josefin Sans',sans-serif;">
                            <h4 class="property-item-title mb-2">
                                <strong>Unit {{ $unit->unit_name ?? 'Unit ' . $unit->id }}</strong>
                            </h4>
                            <div class="property-item-address mb-2 text-muted">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" class="me-1"><path fill="currentColor" d="M15.19 21C14.12 19.43 13 17.36 13 15.5c0-1.83.96-3.5 2.4-4.5H15V9h2v1.23c.5-.14 1-.23 1.5-.23c.17 0 .34 0 .5.03V3H5v18h6v-3.5h2V21zM15 5h2v2h-2zM9 19H7v-2h2zm0-4H7v-2h2zm0-4H7V9h2zm0-4H7V5h2zm2-2h2v2h-2zm0 4h2v2h-2zm0 6v-2h2v2zm7.5-3c-1.9 0-3.5 1.61-3.5 3.5c0 2.61 3.5 6.5 3.5 6.5s3.5-3.89 3.5-6.5c0-1.89-1.6-3.5-3.5-3.5m0 4.81c-.7 0-1.2-.6-1.2-1.2c0-.7.6-1.2 1.2-1.2s1.2.59 1.2 1.2c.1.6-.5 1.2-1.2 1.2"/></svg>
                                {{ $property['name'] }}
                            </div>

                            <div class="d-flex gap-2 align-items-center">
                                <div class="single-option mb-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"><path fill="currentColor" d="M19 7H5v7H3V5H1v15h2v-3h18v3h2v-9a4 4 0 0 0-4-4"/></svg>
                                    {{ $unit->bedroom ?? 'N/A' }}
                                </div>
                                <div class="single-option mb-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"><path fill="currentColor" d="M21 14v1c0 1.91-1.07 3.57-2.65 4.41L19 22h-2l-.5-2h-9L7 22H5l.65-2.59A4.99 4.99 0 0 1 3 15v-1H2v-2h18V5a1 1 0 0 0-1-1c-.5 0-.88.34-1 .79c.63.54 1 1.34 1 2.21h-6a3 3 0 0 1 3-3h.17c.41-1.16 1.52-2 2.83-2a3 3 0 0 1 3 3v9zm-2 0H5v1a3 3 0 0 0 3 3h8a3 3 0 0 0 3-3z"/></svg>
                                    {{ $unit->bath ?? 'N/A' }}
                                </div>
                                <div class="single-option mb-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"><path fill="currentColor" d="M13.2 11H10V7h3.2a2 2 0 0 1 2 2a2 2 0 0 1-2 2M13 3H6v18h4v-6h3a6 6 0 0 0 6-6c0-3.32-2.69-6-6-6"/></svg>
                                    {{ $unit->parking ?? 'N/A' }}
                                </div>
                            </div>

                            @if(!empty($unit->amenities))
                                <div class="single-option mb-2 mt-2">
                                    <i class="bi bi-stars me-2 text-primary"></i>
                                    <strong class="me-2">{{ __('Amenities:') }}</strong>
                                    <div class="d-flex flex-wrap mt-1">
                                        @foreach(explode(',', $unit->amenities) as $amenity)
                                            <span class="badge rounded-pill bg-light text-dark border me-1 mb-1">
                                                {{ trim($amenity) }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <hr>
                            <div class="lowercard d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center">
                                    <img src="{{ $unit->property?->owner?->profile_photo
                                        ? asset($unit->property->owner->profile_photo)
                                        : 'https://ui-avatars.com/api/?name=' . urlencode(($property['owner_first_name'] ?? 'Agent') . ' ' . ($property['owner_last_name'] ?? '')) . '&background=0D6EFD&color=fff' }}"
                                         class="rounded-circle me-2" width="40" height="40"
                                         alt="Agent" loading="lazy">
                                    <span class="fw-medium text-truncate" style="max-width:150px;">
                                        {{ $property['owner_first_name'] ?? 'Agent' }} {{ $property['owner_last_name'] ?? '' }}
                                    </span>
                                </div>
                                <div class="text-end">
                                    <span class="fs-6 fw-bold">
                                        KES {{ number_format($unit->general_rent) }} / {{ __('Month') }}
                                    </span>
                                </div>
                            </div>

                            {{-- Apply button — triggers single shared modal --}}
                            <button type="button"
                                    class="btn mt-3 py-3 rounded theme-btn w-100"
                                    onclick="openApplyModal(
                                        {{ $unit->id }},
                                        {{ json_encode($unit->unit_name ?? 'Unit ' . $unit->id) }},
                                        {{ json_encode($property['name']) }},
                                        {{ json_encode($property['owner_first_name'] . ' ' . $property['owner_last_name']) }},
                                        {{ $unit->general_rent }},
                                        {{ $property['id'] }}
                                    )">
                                {{ __('Apply') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center mt-4">
                <p>No vacant units available for this property.</p>
            </div>
        @endforelse
    </div>
</div>

{{-- ── Shared image lightbox modal (one instance, reused for all units) ─────── --}}
<div class="modal fade" id="imageLightboxModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content bg-dark border-0">
            <div class="modal-header border-0 pb-0">
                <h6 class="modal-title text-white" id="lightboxTitle"></h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-2">
                <div id="lightboxCarousel" class="carousel slide" data-bs-ride="false">
                    <div class="carousel-inner" id="lightboxInner"></div>
                    <button class="carousel-control-prev" type="button"
                            data-bs-target="#lightboxCarousel" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon"></span>
                    </button>
                    <button class="carousel-control-next" type="button"
                            data-bs-target="#lightboxCarousel" data-bs-slide="next">
                        <span class="carousel-control-next-icon"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── Shared application modal (one instance, reused for all units) ────────── --}}
<div class="modal fade" id="sharedApplyModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form id="sharedApplyForm" class="ajaxx" method="POST"
              action="{{ route('tenant.house-hunt.application.submit') }}"
              data-handler="applicationHandler">
            @csrf
            <input type="hidden" name="property_unit_id" id="applyUnitId">
            <input type="hidden" name="property_id"      id="applyPropertyId">

            <div class="modal-content rounded">
                <div class="modal-header">
                    <h5 class="modal-title" id="applyModalTitle">{{ __('Apply for Unit') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body px-4">
                    <div class="alert alert-secondary mb-4" id="applyUnitInfo"></div>

                    <h6 class="fw-bold mb-3 text-primary">{{ __('Personal Information') }}</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">{{ __('First Name') }} <span class="text-danger">*</span></label>
                            <input type="text" name="first_name" class="form-control"
                                   value="{{ old('first_name', $user?->first_name) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">{{ __('Last Name') }} <span class="text-danger">*</span></label>
                            <input type="text" name="last_name" class="form-control"
                                   value="{{ old('last_name', $user?->last_name) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">{{ __('Phone Number') }} <span class="text-danger">*</span></label>
                            <input type="text" name="contact_number" class="form-control"
                                   value="{{ old('contact_number', $user?->contact_number) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">{{ __('Email') }} <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control"
                                   value="{{ old('email', $user?->email) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">{{ __('Job') }}</label>
                            <input type="text" name="job" class="form-control"
                                   value="{{ old('job', $user?->tenant?->job) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">{{ __('Age') }}</label>
                            <input type="number" name="age" class="form-control"
                                   value="{{ old('age', $user?->tenant?->age) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">{{ __('Family Members') }}</label>
                            <input type="number" name="family_member" class="form-control"
                                   value="{{ old('family_member', $user?->tenant?->family_member) }}">
                        </div>
                    </div>

                    <h6 class="fw-bold mb-3 text-primary">{{ __('Address Information') }}</h6>
                    <div class="row g-3 mb-3">
                        <div class="col-md-12">
                            <label class="form-label fw-bold small">{{ __('Address') }} <span class="text-danger">*</span></label>
                            <input type="text" name="permanent_address" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">{{ __('Country') }} <span class="text-danger">*</span></label>
                            <input type="text" name="permanent_country_id" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">{{ __('State') }} <span class="text-danger">*</span></label>
                            <input type="text" name="permanent_state_id" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">{{ __('City') }} <span class="text-danger">*</span></label>
                            <input type="text" name="permanent_city_id" class="form-control" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-bold small">{{ __('Zip Code') }} <span class="text-danger">*</span></label>
                            <input type="text" name="permanent_zip_code" class="form-control" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer d-flex flex-column gap-2 px-4 pb-4">
                    <button type="submit" class="btn mt-2 py-3 rounded theme-btn w-100">
                        {{ __('Submit Application') }}
                    </button>
                    <button type="button" class="btn btn-danger mt-2 py-3 rounded w-100" data-bs-dismiss="modal">
                        {{ __('Close') }}
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection

@push('style')
    <link rel="stylesheet" href="{{ asset('assets/properties/css/properties.css') }}">
@endpush

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
// ── Shared image lightbox ────────────────────────────────────────────────────
function openImageModal(unitId, imageUrls) {
    if (!imageUrls || imageUrls.length === 0) return;

    const inner = document.getElementById('lightboxInner');
    inner.innerHTML = imageUrls.map((url, i) => `
        <div class="carousel-item ${i === 0 ? 'active' : ''}">
            <img src="${url}" class="d-block w-100"
                 style="max-height:75vh;object-fit:contain;" alt="Unit image ${i + 1}" loading="lazy">
        </div>
    `).join('');

    document.getElementById('lightboxTitle').textContent = 'Unit photos (' + imageUrls.length + ')';

    // Reset carousel to first slide
    const carousel = bootstrap.Carousel.getOrCreateInstance(document.getElementById('lightboxCarousel'));
    carousel.to(0);

    new bootstrap.Modal(document.getElementById('imageLightboxModal')).show();
}

// ── Shared application modal ─────────────────────────────────────────────────
function openApplyModal(unitId, unitName, propertyName, agentName, rent, propertyId) {
    document.getElementById('applyUnitId').value    = unitId;
    document.getElementById('applyPropertyId').value = propertyId;
    document.getElementById('applyModalTitle').textContent = 'Apply Tenancy for Unit ' + unitName;
    document.getElementById('applyUnitInfo').innerHTML =
        '<strong>' + propertyName + '</strong><br>' +
        'Unit: ' + unitName + '<br>' +
        'Agent: ' + agentName + '<br>' +
        'Rent: KES ' + Number(rent).toLocaleString() + ' / Month';

    // Reset the form fields (but keep pre-filled user data)
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
            success: function (response) {
                if (typeof handler === 'function') handler(response, $form);
            },
            error: function (xhr) {
                if (typeof commonHandler === 'function') commonHandler(xhr.responseJSON, $form);
            }
        });
    });

    // Reset shared modal form on close
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