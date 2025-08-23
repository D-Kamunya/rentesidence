@extends('saas.frontend.layouts.app')
@section('content')
<link href="https://fonts.googleapis.com/css2?family=Josefin+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Poppins:wght@400;600&display=swap" rel="stylesheet">
<div class="row">
    <div class="col-md-12 property-banner text-white position-relative" 
        style="position: relative; background-image: url('https://picsum.photos/1200/400?random={{ rand(1, 1000) }}'); background-size: cover; background-position: center; height: 500px; border-radius: 12px; overflow: hidden;">
        
        <!-- Black overlay -->
         h-100  position-relative
        <div style="position: absolute; inset: 0; background: rgba(0, 0, 0, 0.45); display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; padding: 20px;">
            <h5 style="color: #fff; font-size: 2rem; font-family: 'Poppins', sans-serif; font-weight: bold; letter-spacing: 1px; text-shadow: 0 2px 8px rgba(0,0,0,0.7); margin-bottom: 5px;">
                {{ __('Find Your Next Home') }}
            </h5>
            <p style="color: #fff; font-size: 1.1rem; font-family: 'Poppins', sans-serif; text-shadow: 0 2px 6px rgba(0,0,0,0.6); margin: 0;">
                {{ __('Browse vacant properties and discover your perfect match') }}
            </p>
        </div>
    </div>
</div>
<div class="property-details mt-5">
    <div class="row">
        @if($properties->count())
            <div class="container-fluid row px-md-4 px-lg-5">
                @foreach($properties as $property)
                    <div class="col-12 col-md-6 col-lg-4 mb-4"> 
                        <div class="property-item rounded overflow-hidden">
                            <div class="single-properties">
                                <div class="properties-img position-relative">
                                    <img 
                                        src="{{ $property->thumbnail_url ?? 'https://picsum.photos/600/400?random=' . $property->property_id }}" 
                                        onerror="this.onerror=null; this.src='https://picsum.photos/600/400?random={{ $property->property_id }}';" 
                                        alt="Thumbnail" 
                                        style="width:100%; height:250px; object-fit:cover; border-radius:12px;">
                                        <span class="badge bg-primary position-absolute top-0 start-0 m-2 px-3 py-2">
                                {{ $unit->listing_type ?? __('For Rent') }}
                            </span>
                                    
                                    {{-- No Image Provided Tag --}}
                                    @if(!$property->thumbnail_url)
                                        <span style="position:absolute; bottom:8px; right:12px; background:rgba(0,0,0,0.7); color:#fff; font-size:12px; padding:3px 8px; border-radius:6px;">
                                            No Image Provided
                                        </span>
                                    @endif
                                </div>
                                <div class="properties-text-info p-3" style="font-family: 'Josefin Sans', sans-serif;">
                                    <h4 class="property-item-title position-relative mb-3">
                                        <strong>{{ $property->property_name }}</strong>
                                    </h4>
                                    <div class="property-item-address d-flex align-items-center mt-15 mb-15 fs-6 text-muted">
                                        <div class="properties-icon-type-location me-2">
                                            <i class="fas fa-map-marker-alt"></i>
                                        </div>
                                        {{ $property->city }}, {{ $property->state }} 
                                    </div>
                                    <div class="single-option d-flex align-items-center mb-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="currentColor" d="M19.31 18.9c.44-.69.69-1.52.69-2.4c0-2.5-2-4.5-4.5-4.5S11 14 11 16.5s2 4.5 4.5 4.5c.87 0 1.69-.25 2.38-.68L21 23.39L22.39 22zm-3.81.1a2.5 2.5 0 0 1 0-5a2.5 2.5 0 0 1 0 5M5 20v-8H2l10-9l10 9h-1.82c-1.18-1.23-2.84-2-4.68-2c-3.58 0-6.5 2.92-6.5 6.5c0 1.29.38 2.5 1.03 3.5z"/></svg>
                                        <div>
                                            <strong>{{ __('Vacant Units:') }}</strong> {{ $property->empty_units_count }}
                                        </div>
                                    </div>
                                    <div class="single-option d-flex align-items-center mb-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="currentColor" d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12c5.16-1.26 9-6.45 9-12V5zm0 4a3 3 0 0 1 3 3a3 3 0 0 1-3 3a3 3 0 0 1-3-3a3 3 0 0 1 3-3m5.13 12A9.7 9.7 0 0 1 12 20.92A9.7 9.7 0 0 1 6.87 17c-.34-.5-.63-1-.87-1.53c0-1.65 2.71-3 6-3s6 1.32 6 3c-.24.53-.53 1.03-.87 1.53"/></svg>
                                        <div>
                                            <strong>{{ __('Agent:') }}</strong> {{ $property->owner_first_name }} {{ $property->owner_last_name }}
                                        </div>
                                    </div>
                                    <div class="single-option d-flex align-items-center mb-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="currentColor" d="M17.9 17.39c-.26-.8-1.01-1.39-1.9-1.39h-1v-3a1 1 0 0 0-1-1H8v-2h2a1 1 0 0 0 1-1V7h2a2 2 0 0 0 2-2v-.41a7.984 7.984 0 0 1 2.9 12.8M11 19.93c-3.95-.49-7-3.85-7-7.93c0-.62.08-1.22.21-1.79L9 15v1a2 2 0 0 0 2 2m1-16A10 10 0 0 0 2 12a10 10 0 0 0 10 10a10 10 0 0 0 10-10A10 10 0 0 0 12 2"/></svg>
                                        <div>
                                            <strong>{{ __('Country:') }}</strong> {{ $property->country }}
                                        </div>
                                    </div>
                                    @if($property->map_link)
                                        <div class="mt-3">
                                            <button 
                                                class="btn w-100" 
                                                type="button" 
                                                data-bs-toggle="collapse" 
                                                data-bs-target="#map-{{ $property->property_id }}"
                                                style="background: #f8f9fa; 
                                                    color: #0d6efd; 
                                                    font-weight: 500; 
                                                    border: 1px solid #0d6efd; 
                                                    border-radius: 10px; 
                                                    padding: 10px 0; 
                                                    transition: all 0.3s ease;"
                                                onmouseover="this.style.background='#0d6efd'; this.style.color='white';"
                                                onmouseout="this.style.background='#f8f9fa'; this.style.color='#0d6efd';">
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
                                    <a href="{{ route('house-hunt.view', ['propertyId' => $property->property_id]) }}" class="btn mt-3 py-3 rounded theme-btn w-100">
                                        {{ __('View Now') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
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
    
