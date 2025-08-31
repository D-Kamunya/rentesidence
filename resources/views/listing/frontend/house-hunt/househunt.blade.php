@extends('saas.frontend.layouts.app')
@section('content')
<link href="https://fonts.googleapis.com/css2?family=Josefin+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Poppins:wght@400;600&display=swap" rel="stylesheet">
<div class="col-md-12 property-banner text-white position-relative" 
    style="position: relative; 
            background-image: url('{{ asset('assets/images/exterior.jpg') }}'); 
            background-size: cover; 
            background-position: center; 
            height: 600px; 
            border-radius: 12px; 
            overflow: hidden;">
        
    <!-- Black overlay -->
    <div style="position: absolute; inset: 0; background: rgba(0, 0, 0, 0.45); 
                display: flex; flex-direction: column; align-items: center; 
                justify-content: center; text-align: center; padding: 20px;">
        <h5 style="color: #fff; font-size: 2rem; font-family: 'Poppins', sans-serif; 
                font-weight: bold; letter-spacing: 1px; 
                text-shadow: 0 2px 8px rgba(0,0,0,0.7); margin-bottom: 5px;">
            {{ __('Find Your Next Home') }}
        </h5>
        <p style="color: #fff; font-size: 1.1rem; font-family: 'Poppins', sans-serif; 
                text-shadow: 0 2px 6px rgba(0,0,0,0.6); margin: 0;">
            {{ __('Browse vacant properties and discover your perfect match') }}
        </p>
    </div>
</div>
<div class="property-details mt-5">
    <div class="row">
        @if($listings->count())
            <div class="container-fluid row px-md-4 px-lg-5">
                @foreach($listings as $listing)
                    <div class="col-12 col-md-6 col-lg-4 mb-4"> 
                        <div class="property-item rounded overflow-hidden">
                            <div class="single-properties">
                                <div class="properties-img position-relative">
                                    <img 
                                        src="{{ $listing->image }}" 
                                        onerror="this.onerror=null; this.src='{{ asset('assets/images/property.png') }}';" 
                                        alt="Thumbnail" 
                                        style="width:100%; height:250px; object-fit:cover; border-radius:12px;">
                                    <span class="badge {{ $listing->type == 'sale' ? 'bg-success' : 'bg-primary' }} position-absolute top-0 start-0 m-2 px-3 py-2">
                                        {{ $listing->type == 'sale' ? 'For Sale' : 'For Rent' }}
                                    </span>
                                </div>
                                <div class="properties-text-info p-3" style="font-family: 'Josefin Sans', sans-serif;">
                                    <h4 class="property-item-title position-relative mb-3">
                                        <strong>{{ $listing->name }}</strong>
                                    </h4>
                                    @if($listing->type == 'rental')
                                        <div class="property-item-address d-flex align-items-center mt-15 mb-15 fs-6 text-muted">
                                            <div class="properties-icon-type-location me-2">
                                                <i class="fas fa-map-marker-alt"></i>
                                            </div>
                                            {{ $listing->address }}
                                        </div>
                                    @else
                                        <div class="property-item-address d-flex align-items-center mt-15 mb-15 fs-6 text-muted">
                                            <div class="properties-icon-type-location me-2">
                                                <i class="fas fa-map-marker-alt"></i>
                                            </div>
                                            {{ $listing->city }} , {{ $listing->state }}
                                        </div>
                                    @endif
                                    @if($listing->type == 'rental')
                                        <div class="single-option d-flex align-items-center mb-3">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="currentColor" d="M19.31 18.9c.44-.69.69-1.52.69-2.4c0-2.5-2-4.5-4.5-4.5S11 14 11 16.5s2 4.5 4.5 4.5c.87 0 1.69-.25 2.38-.68L21 23.39L22.39 22zm-3.81.1a2.5 2.5 0 0 1 0-5a2.5 2.5 0 0 1 0 5M5 20v-8H2l10-9l10 9h-1.82c-1.18-1.23-2.84-2-4.68-2c-3.58 0-6.5 2.92-6.5 6.5c0 1.29.38 2.5 1.03 3.5z"/></svg>
                                                <div>
                                                    <strong>Vacant Units:</strong> {{ $listing->units }}
                                                </div>
                                        </div>
                                        <div class="single-option d-flex align-items-center mb-3">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="currentColor" d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12c5.16-1.26 9-6.45 9-12V5zm0 4a3 3 0 0 1 3 3a3 3 0 0 1-3 3a3 3 0 0 1-3-3a3 3 0 0 1 3-3m5.13 12A9.7 9.7 0 0 1 12 20.92A9.7 9.7 0 0 1 6.87 17c-.34-.5-.63-1-.87-1.53c0-1.65 2.71-3 6-3s6 1.32 6 3c-.24.53-.53 1.03-.87 1.53"/></svg>
                                            <div>
                                                <strong>Agent:</strong> {{ $listing->agent }}
                                            </div>
                                        </div>
                                    @else
                                    <div class="single-option d-flex align-items-center mb-3">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="currentColor" d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12c5.16-1.26 9-6.45 9-12V5zm0 4a3 3 0 0 1 3 3a3 3 0 0 1-3 3a3 3 0 0 1-3-3a3 3 0 0 1 3-3m5.13 12A9.7 9.7 0 0 1 12 20.92A9.7 9.7 0 0 1 6.87 17c-.34-.5-.63-1-.87-1.53c0-1.65 2.71-3 6-3s6 1.32 6 3c-.24.53-.53 1.03-.87 1.53"/></svg>
                                        <div>
                                            <strong>Owner:</strong> {{ $listing->name }}
                                        </div>
                                    </div>
                                    <div class="single-option d-flex align-items-center mb-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="currentColor" d="M7 15h2c0 1.08 1.37 2 3 2s3-.92 3-2c0-1.1-1.04-1.5-3.24-2.03C9.64 12.44 7 11.78 7 9c0-1.79 1.47-3.31 3.5-3.82V3h3v2.18C15.53 5.69 17 7.21 17 9h-2c0-1.08-1.37-2-3-2s-3 .92-3 2c0 1.1 1.04 1.5 3.24 2.03C14.36 11.56 17 12.22 17 15c0 1.79-1.47 3.31-3.5 3.82V21h-3v-2.18C8.47 18.31 7 16.79 7 15"/></svg>
                                        <div>
                                            <strong>Price:</strong> {{ currencyPrice($listing->price) }}
                                        </div>
                                    </div>
                                    @endif
                                    <div class="single-option d-flex align-items-center mb-3">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="currentColor" d="M17.9 17.39c-.26-.8-1.01-1.39-1.9-1.39h-1v-3a1 1 0 0 0-1-1H8v-2h2a1 1 0 0 0 1-1V7h2a2 2 0 0 0 2-2v-.41a7.984 7.984 0 0 1 2.9 12.8M11 19.93c-3.95-.49-7-3.85-7-7.93c0-.62.08-1.22.21-1.79L9 15v1a2 2 0 0 0 2 2m1-16A10 10 0 0 0 2 12a10 10 0 0 0 10 10a10 10 0 0 0 10-10A10 10 0 0 0 12 2"/></svg>
                                            <div>
                                                <strong>Country:</strong> {{ $listing->country }}
                                            </div>
                                    </div>
                                    
                                    <a href="{{ $listing->type == 'sale' 
                                                ? route('listing.details', $listing->slug) 
                                                : route('house-hunt.view', ['propertyId' => $listing->slug]) }}"
                                    class="btn mt-3 py-3 rounded theme-btn w-100">
                                        {{ $listing->type == 'sale' ? 'View Details' : 'View Now' }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-center">No properties available at the moment.</p>
        @endif
    </div>
</div>
@endsection
@push('style')
    <link rel="stylesheet" href="{{ asset('assets/properties/css/properties.css') }}">
    
@endpush
    
