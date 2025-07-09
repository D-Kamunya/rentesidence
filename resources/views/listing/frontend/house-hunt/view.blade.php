@extends('saas.frontend.layouts.app')

@section('content')

@php
    $user = Auth::check() ? Auth::user() : null;
@endphp

<!-- Hero section -->
<div class="property-details-area">
    <div class="row">
        <div class="col-md-12 property-banner text-white" style="background-image: url('{{ $property['thumbnail_image'] ?? asset('storage/images/placeholder.png') }}');">
            <div class="property-bander-text text-center overlay">
                <h5 class="property-bander-header">{{ $property['name'] }}</h5>
                <div class="d-inline-flex align-items-center justify-content-center gap-2">
                    <i class="fas fa-map-marker-alt text-danger fs-4"></i>
                    <h4 class="mb-0">{{ $property['city_id'] ?? '' }}, {{ $property['state_id'] ?? '' }}</h4>
                </div>
            </div>
        </div>
    </div>
    <div class="row mt-4">
        <div class="col-lg-8 pe-lg-5 px-5">
            <div class="row">
                @forelse ($emptyUnits as $unit)
                    @php
                        $unitImage = $unit->unit_image_file
                            ? asset("storage/{$unit->unit_image_folder}/{$unit->unit_image_file}")
                            : asset('storage/images/placeholder.png');
                    @endphp
                    <div class="col-md-6 col-lg-6 col-xl-4 mb-4">
                        <div class="all-single-properties">
                            <div class="single-properties">
                                <div class="properties-img">
                                    <img src="{{ $unitImage }}" class="card-img-top" alt="Unit Image">
                                </div>
                                <div class="properties-text-info">
                                    <h5 class="property-item-title"><strong>Unit {{ $unit->unit_name ?? 'Unit ' . $unit->id }}</strong></h5>
                                    <div class="property-item-address mb-2 text-muted">
                                        {{ $property['name'] }}
                                    </div>
                                    <div class="room-details">
                                        <div class="single-option mb-2">
                                            <i class="bi bi-door-open me-2 text-primary"></i>
                                            <strong>{{ __('Rent:') }}</strong> KES {{ number_format($unit->general_rent) }} / {{ __('Month') }}
                                        </div>
                                        <div class="single-option mb-2">
                                            <i class="bi bi-aspect-ratio me-2 text-primary"></i>
                                            <strong>{{ __('Bedrooms:') }}</strong> {{ $unit->bedroom ?? 'N/A' }}
                                        </div>
                                        <div class="single-option mb-2">
                                            <i class="bi bi-person-badge me-2 text-primary"></i>
                                            <strong>{{ __('Agent:') }}</strong> {{ $property['owner_first_name'] }} {{ $property['owner_last_name'] }}
                                        </div>
                                        <div class="single-option mb-2 d-flex align-items-start">
                                            <i class="bi bi-stars me-2 text-primary"></i>
                                            <div>
                                                <strong class="me-2">{{ __('Amenities:') }}</strong>
                                                @foreach (explode(',', $unit->amenities) as $amenity)
                                                    <span class="badge bg-primary me-1">{{ trim($amenity) }}</span>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                    <button type="button"
                                        class="btn mt-3 py-3 rounded theme-btn w-100"
                                        data-bs-toggle="modal"
                                        data-bs-target="#applyModal{{ $unit->id }}">
                                        {{ __('Apply') }}
                                    </button>
                                    <!-- Application Modal -->
                                    <div class="modal fade" id="applyModal{{ $unit->id }}" tabindex="-1" aria-labelledby="applyModalLabel{{ $unit->id }}" aria-hidden="true">
                                        <div class="modal-dialog modal-lg">
                                            <form class="ajaxx" action="{{ route('tenant.house-hunt.application.submit') }}" method="POST"
                                                data-handler="applicationHandler">
                                                @csrf
                                                <input type="hidden" name="property_unit_id" value="{{ $unit->id }}">
                                                <input type="hidden" name="property_id" value="{{ $property['id'] }}">

                                                <div class="modal-content rounded">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="applyModalLabel{{ $unit->id }}">
                                                            {{ __('Apply Tenancy for Unit') }} {{ $unit->unit_name ?? $unit->id }}
                                                        </h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                                                    </div>

                                                    <div class="modal-body px-4">
                                                        <!-- Property and Unit Info -->
                                                        <div class="alert alert-secondary mb-4">
                                                            <strong>{{ $property['name'] }}</strong><br>
                                                            {{ __('Unit') }}: {{ $unit->unit_name ?? $unit->id }}<br>
                                                            {{ __('Agent') }}: {{ $property['owner_first_name'] }} {{ $property['owner_last_name'] }}<br>
                                                            {{ __('Rent') }}: KES {{ number_format($unit->general_rent) }} / {{ __('Month') }}
                                                        </div>

                                                        <!-- Section 1: Personal Info -->
                                                        <h6 class="fw-bold mb-3 text-primary">{{ __('Personal Information') }}</h6>
                                                        <div class="row g-3 mb-4">
                                                            <div class="col-md-6">
                                                                <label class="form-label fw-bold small">{{ __('First Name') }} <span class="text-danger">*</span></label>
                                                                <input type="text" name="first_name" class="form-control rounded-sm" value="{{ old('first_name', $user?->first_name) }}" required>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label fw-bold small">{{ __('Last Name') }} <span class="text-danger">*</span></label>
                                                                <input type="text" name="last_name" class="form-control rounded-sm" value="{{ old('last_name', $user?->last_name) }}" required>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label fw-bold small">{{ __('Phone Number') }} <span class="text-danger">*</span></label>
                                                                <input type="text" name="contact_number" class="form-control rounded-sm" value="{{ old('contact_number', $user?->contact_number) }}" required>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label fw-bold small">{{ __('Email') }} <span class="text-danger">*</span></label>
                                                                <input type="email" name="email" class="form-control rounded-sm" value="{{ old('email', $user?->email) }}" required>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label fw-bold small">{{ __('Job') }}</label>
                                                                <input type="text" name="job" class="form-control rounded-sm" value="{{ old('job', $user?->tenant?->job) }}">
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label fw-bold small">{{ __('Age') }}</label>
                                                                <input type="number" name="age" class="form-control rounded-sm" value="{{ old('job', $user?->tenant?->age) }}">
                                                            </div>
                                                            <div class="col-md-6 mb-25">
                                                                <label
                                                                    class="form-label fw-bold small">{{ __('Family Members') }}
                                                                </label>
                                                                <input type="number" name="family_member"
                                                                    class="form-control"
                                                                    placeholder="{{ __('Family Members') }}" value="{{ old('job', $user?->tenant?->family_member) }}">
                                                            </div>
                                                        </div>

                                                        <!-- Section 2: Address Info -->
                                                        <h6 class="fw-bold mb-3 text-primary">{{ __('Address Information') }}</h6>
                                                        <div class="row g-3 mb-3">
                                                            <div class="col-md-12">
                                                                <label class="form-label fw-bold small">{{ __('Address') }} <span class="text-danger">*</span></label>
                                                                <input type="text" name="permanent_address" class="form-control rounded-sm" required>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <label class="form-label fw-bold small">{{ __('Country') }} <span class="text-danger">*</span></label>
                                                                <input type="text" name="permanent_country_id" class="form-control rounded-sm" required>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <label class="form-label fw-bold small">{{ __('State') }} <span class="text-danger">*</span></label>
                                                                <input type="text" name="permanent_state_id" class="form-control rounded-sm" required>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <label class="form-label fw-bold small">{{ __('City') }} <span class="text-danger">*</span></label>
                                                                <input type="text" name="permanent_city_id" class="form-control rounded-sm" required>
                                                            </div>
                                                            <div class="col-md-12">
                                                                <label class="form-label fw-bold small">{{ __('Zip Code') }} <span class="text-danger">*</span></label>
                                                                <input type="text" name="permanent_zip_code" class="form-control rounded-sm" required>
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
    </div>
</div>
@endsection

@push('style')
    <link rel="stylesheet" href="{{ asset('assets/properties/css/properties.css') }}">
@endpush
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    $(document).ready(function () {
        $('form.ajaxx').on('submit', function (e) {
            e.preventDefault();

            const $form = $(this);
            
            const handlerName = $form.data('handler'); // e.g., "applicationHandler"
            const handler = window[handlerName];       // resolve function from window

            $.ajax({
                url: $form.attr('action'),
                method: $form.attr('method'),
                data: $form.serialize(),
                success: function (response) {
                    if (typeof handler === 'function') {
                        handler(response, $form); // ✅ pass the form here
                    }
                },
                error: function (xhr) {
                    if (typeof commonHandler === 'function') {
                        commonHandler(xhr.responseJSON, $form); // optional: pass form to common error handler
                    }
                }
            });
        });

        document.querySelectorAll('.modal').forEach(modalEl => {
            modalEl.addEventListener('hidden.bs.modal', function () {
                const form = modalEl.querySelector('form');
                if (form) form.reset();
            });
        });
    });


    function applicationHandler(data, $form) {
        var output = "";
        var type = "error";
        $(".error-message").remove();
        $(".is-invalid").removeClass("is-invalid");
        if (data["status"] == true) {
            output = output + data["message"];
            type = "success";
            alertAjaxMessage(type, output);

            // ✅ Clear form
            $form[0].reset();

            // ✅ Close the modal
            const modal = $form.closest('.modal');
            const modalId = modal.attr('id');

            if (modalId) {
                const bsModal = bootstrap.Modal.getInstance(document.getElementById(modalId));
                if (bsModal) {
                    bsModal.hide();
                }
            }
        } else {
            commonHandler(data);
        }
    }
</script>