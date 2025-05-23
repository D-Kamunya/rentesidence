@extends('saas.frontend.layouts.app')
@section('content')
    <div class="property-details-area">
        <div class="row">
            <div class="col-md-12">
                <div class="property-bander-text">
                    <h5 class="property-bander-header">{{ __('Find Your Next Home') }}</h5>
                </div>
            </div>
        </div>
        <div class="row">
            @if($properties->count())
                <div class="col-lg-8 pe-lg-5 margin-top-1 px-5">
                    <div class="row">
                        @foreach($properties as $property)
                            <div class="col-md-6 col-lg-6 col-xl-4">
                                <div class="all-single-properties">
                                    <div class="single-properties">
                                        <div class="properties-img">
                                            <img 
                                                src="{{ $property->thumbnail_url ?? asset('storage/images/placeholder.png') }}" 
                                                onerror="this.onerror=null; this.src='{{ asset('storage/images/placeholder.png') }}';" 
                                                alt="Thumbnail">
                                        </div>
                                        <div class="properties-text-info">
                                             <h5 class="property-item-title position-relative"><strong>{{ $property->property_name }}</strong></h5>
                                             <div class="property-item-address d-flex mt-15 mb-15 fs-6">
                                                <div class="properties-icon-type-location">
                                                    <i class="fas fa-map-marker-alt"></i>
                                                </div>
                                                 {{ $property->city }}, {{ $property->state }} 
                                            </div>
                                            <div class="room-details">
                                                <div class="single-option d-flex align-items-center mb-3">
                                                    <i class="bi bi-door-open me-2 text-primary"></i>
                                                    <div>
                                                        <strong>{{ __('Vacant Units:') }}</strong> {{ $property->empty_units_count }}
                                                    </div>
                                                </div>
                                                <div class="single-option d-flex align-items-center mb-3">
                                                    <i class="bi bi-person-badge me-2 text-primary"></i>
                                                    <div>
                                                        <strong>{{ __('Agent:') }}</strong> {{ $property->owner_first_name }} {{ $property->owner_last_name }}
                                                    </div>
                                                </div>
                                                <div class="single-option d-flex align-items-center mb-3">
                                                    <i class="bi bi-geo-alt me-2 text-primary"></i>
                                                    <div>
                                                        <strong>{{ __('Country:') }}</strong> {{ $property->country }}
                                                    </div>
                                                </div>
                                                @if($property->map_link)
                                                    <div class="mt-3">
                                                        <button class="btn btn-outline-primary w-100" type="button" data-bs-toggle="collapse" data-bs-target="#map-{{ $property->property_id }}">
                                                            <i class="bi bi-map"></i> View Map
                                                        </button>
                                                        <div class="collapse mt-2" id="map-{{ $property->property_id }}">
                                                            <iframe
                                                                src="{{ $property->map_link }}"
                                                                width="100%"
                                                                height="250"
                                                                class="rounded shadow-sm"
                                                                style="border:0;"
                                                                allowfullscreen=""
                                                                loading="lazy"
                                                                referrerpolicy="no-referrer-when-downgrade">
                                                            </iframe>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                             <a href="{{ route('house-hunt.view', ['propertyId' => $property->property_id]) }}" class="btn mt-3 py-3 rounded  theme-btn w-100">{{ __('View Now') }}</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <p class="text-center">No vacant Properties available at the moment.</p>
            @endif
        </div>
    </div>
@endsection
@push('style')
    <link rel="stylesheet" href="{{ asset('assets/properties/css/properties.css') }}">
    
@endpush
    
