@extends('saas.frontend.layouts.app')

@section('content')
<!-- Hero section -->
<div class="property-details-area">
    <div class="row">
        <div class="col-md-12">
            <div class="property-bander-text text-center">
                <h5 class="property-bander-header">{{ $property['name'] }}</h5>
                <div class="d-inline-flex align-items-center justify-content-center gap-2">
                    <i class="fas fa-map-marker-alt text-danger fs-4"></i>
                    <h4 class="mb-0">{{ $property['city'] ?? '' }}, {{ $property['state'] ?? '' }}</h4>
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
                                            <strong>{{ __('Agent:') }}</strong> {{ $property['owner_name'] }}
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
                                            <form method="POST" action="">
                                                @csrf
                                                <input type="hidden" name="unit_id" value="{{ $unit->id }}">
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
                                                            {{ __('Agent') }}: {{ $property['owner_name'] }}<br>
                                                            {{ __('Rent') }}: KES {{ number_format($unit->general_rent) }} / {{ __('Month') }}
                                                        </div>

                                                        <!-- Section 1: Personal Info -->
                                                        <h6 class="fw-bold mb-3 text-primary">{{ __('Personal Information') }}</h6>
                                                        <div class="row g-3 mb-4">
                                                            <div class="col-md-6">
                                                                <label class="form-label fw-bold small">{{ __('First Name') }}</label>
                                                                <input type="text" name="first_name" class="form-control rounded-sm" required>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label fw-bold small">{{ __('Last Name') }}</label>
                                                                <input type="text" name="last_name" class="form-control rounded-sm" required>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label fw-bold small">{{ __('Phone Number') }}</label>
                                                                <input type="text" name="phone" class="form-control rounded-sm" required>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label fw-bold small">{{ __('Email') }}</label>
                                                                <input type="email" name="email" class="form-control rounded-sm" required>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label fw-bold small">{{ __('Job') }}</label>
                                                                <input type="text" name="job" class="form-control rounded-sm">
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label fw-bold small">{{ __('Age') }}</label>
                                                                <input type="number" name="age" class="form-control rounded-sm">
                                                            </div>
                                                        </div>

                                                        <!-- Section 2: Address Info -->
                                                        <h6 class="fw-bold mb-3 text-primary">{{ __('Address Information') }}</h6>
                                                        <div class="row g-3 mb-3">
                                                            <div class="col-md-12">
                                                                <label class="form-label fw-bold small">{{ __('Address') }}</label>
                                                                <input type="text" name="address" class="form-control rounded-sm">
                                                            </div>
                                                            <div class="col-md-4">
                                                                <label class="form-label fw-bold small">{{ __('Country') }}</label>
                                                                <input type="text" name="country" class="form-control rounded-sm">
                                                            </div>
                                                            <div class="col-md-4">
                                                                <label class="form-label fw-bold small">{{ __('State') }}</label>
                                                                <input type="text" name="state" class="form-control rounded-sm">
                                                            </div>
                                                            <div class="col-md-4">
                                                                <label class="form-label fw-bold small">{{ __('City') }}</label>
                                                                <input type="text" name="city" class="form-control rounded-sm">
                                                            </div>
                                                            <div class="col-md-12">
                                                                <label class="form-label fw-bold small">{{ __('Zip Code') }}</label>
                                                                <input type="text" name="zip_code" class="form-control rounded-sm">
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
