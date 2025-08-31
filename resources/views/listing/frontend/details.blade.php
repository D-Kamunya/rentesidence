@extends('saas.frontend.layouts.app')
<link href="https://fonts.googleapis.com/css2?family=Josefin+Sans:wght@400;500;600&display=swap" rel="stylesheet">
@section('content')
    <div class="property-details-area">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="property-slider-area owl-carousel">
                        @foreach ($images as $image)
                            <div class="single-property-slider">
                                <img src="{{ assetUrl($image->folder_name . '/' . $image->file_name) }}"
                                    alt="{{ $listing->name }}">
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            {{-- QUICK HEADER (badge + title/price) --}}
            <div class="d-flex align-items-center mb-3">
                <!-- Sale badge -->
                <span class="badge bg-success text-uppercase">For Sale</span>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap">
                <!-- Property name -->
                <span class="fw-semibold mb-0" 
                    style="font-family: 'Josefin Sans', sans-serif; font-size: 1.75rem">
                    {{ $listing->name }}
                </span>

                <!-- Price -->
                <span class="fw-bold text-success mb-0" 
                    style="font-family: 'Josefin Sans', sans-serif; font-size: 1.5rem">
                    {{ currencyPrice($listing->price) }}
                </span>
            </div>
            <hr>
            <!-- Meta section -->
            <div class="property-meta mt-3 d-flex flex-wrap text-muted small">
                <span class="me-3 d-flex align-items-center">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24">
                        <path fill="blue" d="M19 19H5V8h14m0-5h-1V1h-2v2H8V1H6v2H5a2 2 0 0 0-2 2v14a2 
                        2 0 0 0 2 2h14a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2m-2.47 8.06L15.47 
                        10l-4.88 4.88l-2.12-2.12l-1.06 1.06L10.59 17z"/>
                    </svg>
                    {{ $listing->created_at->format('M d, Y') }}
                </span>
                <!-- Divider -->
                <div class="d-none d-sm-block">
                    <span class="mx-2">|</span>
                </div>
                <span class="me-3 d-flex align-items-center">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24">
                        <path fill="blue" d="M12 6.5A2.5 2.5 0 0 1 14.5 9a2.5 2.5 0 0 1-2.5 
                        2.5A2.5 2.5 0 0 1 9.5 9A2.5 2.5 0 0 1 12 6.5M12 
                        2a7 7 0 0 1 7 7c0 5.25-7 13-7 
                        13S5 14.25 5 9a7 7 0 0 1 7-7m0 
                        2a5 5 0 0 0-5 5c0 1 0 3 
                        5 9.71C17 12 17 10 17 9a5 5 
                        0 0 0-5-5"/>
                    </svg>
                    {{ $listing->city }} - {{ $listing->state }}, {{ $listing->country }}
                </span>
                <!-- Divider -->
                <div class="d-none d-sm-block">
                    <span class="mx-2">|</span>
                </div>
                <span class="me-3 d-flex align-items-center">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="blue" d="m5 9.5l7-5.27l7 5.27zM5 20v-4.25h8.5V20zm9.5 0v-4.25H19V20zM5 14.75V10.5h4.5v4.25zm5.5 0V10.5H19v4.25z"/></svg>
                    <span class="ms-1 small">{{ $listing->type }}</span>
                </span>
                <!-- Divider -->
                <div class="d-none d-sm-block">
                    <span class="mx-2">|</span>
                </div>
                <span class="d-flex align-items-center">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="blue" d="M6 14h2q0-1.475 1.075-2.488T11.65 10.5q.9 0 1.675.413T14.6 12H13v2h5V9h-2v1.55q-.8-.95-1.912-1.5T11.65 8.5q-2.375 0-4.012 1.6T6 14m6 8q-2.075 0-3.9-.788t-3.175-2.137T2.788 15.9T2 12t.788-3.9t2.137-3.175T8.1 2.788T12 2t3.9.788t3.175 2.137T21.213 8.1T22 12t-.788 3.9t-2.137 3.175t-3.175 2.138T12 22"/></svg>
                    <span class="ms-1 small">{{ $listing->interior }}</span>
                </span>
            </div>
            <div class="row">
                <div class="col-lg-7">
                    <div class="property-description-area">
                        <!-- Property Description -->
                        <div class="mt-4">
                            <h4 class="fw-bold mb-3" style="font-family: 'Josefin Sans', sans-serif;">Description</h4>
                            <p class="text-muted" style="line-height: 1.7; font-size: 1rem;">
                                {{ $listing->details }}
                            </p>
                        </div>
                        <hr>
                        <div class="single-property-overview mt-4" style="font-family: 'Josefin Sans', sans-serif;">
                            <h4 class="fw-bold mb-3">{{ __('Overview') }}</h4>
                            <div class="row g-3">
                                @if ($listing->bed_room)
                                    <div class="col-6 col-md-4">
                                        <div class="d-flex align-items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="blue" d="M18.35 11.45V9c0-1.1-.9-2-2-2H13c-.37 0-.72.12-1 .32c-.28-.2-.63-.32-1-.32H7.65c-1.1 0-2 .9-2 2v2.45c-.4.46-.65 1.06-.65 1.72V17h1.5v-1.5h11V17H19v-3.83c0-.66-.25-1.26-.65-1.72m-1.6-.95h-4v-2h4zm-9.5-2h4v2h-4zM17.5 14h-11v-1c0-.55.45-1 1-1h9c.55 0 1 .45 1 1zM20 4v16H4V4zm0-2H4c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2"/></svg>
                                            <span>{{ $listing->bed_room }} {{ __('Bedrooms') }}</span>
                                        </div>
                                    </div>
                                @endif

                                @if ($listing->bath_room)
                                    <div class="col-6 col-md-4">
                                        <div class="d-flex align-items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="blue" d="M21 14v1c0 1.91-1.07 3.57-2.65 4.41L19 22h-2l-.5-2h-9L7 22H5l.65-2.59A4.99 4.99 0 0 1 3 15v-1H2v-2h18V5a1 1 0 0 0-1-1c-.5 0-.88.34-1 .79c.63.54 1 1.34 1 2.21h-6a3 3 0 0 1 3-3h.17c.41-1.16 1.52-2 2.83-2a3 3 0 0 1 3 3v9zm-2 0H5v1a3 3 0 0 0 3 3h8a3 3 0 0 0 3-3z"/></svg>
                                            <span>{{ $listing->bath_room }} {{ __('Bathrooms') }}</span>
                                        </div>
                                    </div>
                                @endif

                                @if ($listing->kitchen_room)
                                    <div class="col-6 col-md-4">
                                        <div class="d-flex align-items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="blue" d="M22 10h-4V7c0-1.66-1.34-3-3-3s-3 1.34-3 3h2c0-.55.45-1 1-1s1 .45 1 1v3H8c1.1 0 2-.9 2-2V4H4v4c0 1.1.9 2 2 2H2v2h2v8h16v-8h2zM6 6h2v2H6zm0 12v-6h5v6zm12 0h-5v-6h5z"/></svg>
                                            <span>{{ $listing->kitchen_room }} {{ __('Kitchen') }}</span>
                                        </div>
                                    </div>
                                @endif

                                @if ($listing->dining_room)
                                    <div class="col-6 col-md-4">
                                        <div class="d-flex align-items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="blue" d="M8 19h1.5v-6.75q.65-.2 1.075-.712T11 10.3V6.5q0-.2-.15-.35T10.5 6t-.35.15t-.15.35V9h-.75V6.5q0-.2-.15-.35T8.75 6t-.35.15t-.15.35V9H7.5V6.5q0-.2-.15-.35T7 6t-.35.15t-.15.35v3.8q0 .725.425 1.238T8 12.25zm6 0h1.5v-6.35q.825-.4 1.288-1.275t.462-2.05q0-1.425-.712-2.375T14.75 6t-1.787.95t-.713 2.375q0 1.175.463 2.05T14 12.65zM4 22q-.825 0-1.412-.587T2 20V4q0-.825.588-1.412T4 2h16q.825 0 1.413.588T22 4v16q0 .825-.587 1.413T20 22z"/></svg>
                                            <span>{{ $listing->dining_room }} {{ __('Dining Room') }}</span>
                                        </div>
                                    </div>
                                @endif

                                @if ($listing->living_room)
                                    <div class="col-6 col-md-4">
                                        <div class="d-flex align-items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="blue" d="M12.5 7c0-1.11.89-2 2-2H18c1.1 0 2 .9 2 2v2.16c-1.16.41-2 1.51-2 2.81V14h-5.5zM6 11.96V14h5.5V7c0-1.11-.89-2-2-2H6c-1.1 0-2 .9-2 2v2.15c1.16.41 2 1.52 2 2.81m14.66-1.93c-.98.16-1.66 1.09-1.66 2.09V15H5v-3a2 2 0 1 0-4 0v5c0 1.1.9 2 2 2v2h2v-2h14v2h2v-2c1.1 0 2-.9 2-2v-5c0-1.21-1.09-2.18-2.34-1.97"/></svg>
                                            <span>{{ $listing->living_room }} {{ __('Living Room') }}</span>
                                        </div>
                                    </div>
                                @endif

                                @if ($listing->storage_room)
                                    <div class="col-6 col-md-4">
                                        <div class="d-flex align-items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="blue" d="M5 21L3 9h18l-2 12zm5-6h4q.425 0 .713-.288T15 14t-.288-.712T14 13h-4q-.425 0-.712.288T9 14t.288.713T10 15M6 8q-.425 0-.712-.288T5 7t.288-.712T6 6h12q.425 0 .713.288T19 7t-.288.713T18 8zm2-3q-.425 0-.712-.288T7 4t.288-.712T8 3h8q.425 0 .713.288T17 4t-.288.713T16 5z"/></svg>
                                            <span>{{ $listing->storage_room }} {{ __('Storage Room') }}</span>
                                        </div>
                                    </div>
                                @endif

                                @foreach (explode(',', $listing->other_room) as $room)
                                    @if ($room)
                                        <div class="col-6 col-md-4">
                                            <div class="d-flex align-items-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="blue" d="M5.808 20q-.343 0-.576-.232T5 19.192v-2.634q0-.344.232-.576t.576-.232h6.884q.343 0 .576.232t.232.576v2.634q0 .343-.232.576t-.576.232zm9.5 0q-.343 0-.576-.232t-.232-.576v-2.634q0-.33.24-.569q.239-.239.568-.239h2.884q.344 0 .576.232t.232.576v2.634q0 .343-.232.576t-.576.232zm-9.5-5.25q-.343 0-.576-.232T5 13.942v-2.634q0-.343.232-.576t.576-.232h2.884q.343 0 .576.232t.232.576v2.634q0 .344-.232.576t-.576.232zm5.5 0q-.343 0-.576-.232t-.232-.576v-2.634q0-.343.232-.576t.576-.232h6.884q.344 0 .576.232t.232.576v2.634q0 .344-.232.576t-.576.232zM6.212 9.5q-.293 0-.376-.27t.133-.457l5.062-3.815q.223-.162.466-.243t.507-.08t.504.08t.461.243l5.062 3.815q.217.187.134.457q-.084.27-.377.27z"/></svg>
                                                <span>{{ $room }}</span>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                        <hr>

                        <div class="single-property-specie mt-4" style="font-family: 'Josefin Sans', sans-serif;">
                            <h5 class="specie-catagories">{{ __('Amenities') }}</h5>
                            <div class="row">
                                @foreach (json_decode($listing->amenities) ?? [] as $amenity)
                                    <div class="col-6 col-md-4 mb-2 d-flex align-items-center">
                                        <i class="me-2 text-success">
                                            <!-- You can swap this with Bootstrap icons -->
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" 
                                                class="bi bi-check-circle" viewBox="0 0 16 16">
                                                <path d="M2.5 8a5.5 5.5 0 1111 0 5.5 5.5 0 01-11 0z"/>
                                                <path d="M10.97 6.03a.75.75 0 00-1.08-1.04L7.477 7.417 6.384 6.323a.75.75 0 10-1.06 1.06l1.646 1.647a.75.75 0 001.06 0l3.94-3.94z"/>
                                            </svg>
                                        </i>
                                        <span class="small">{{ getPropertyAmenities($amenity) }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="card shadow-sm border-0 rounded-3">
                        <div class="card-body">
                            <!-- Owner Info -->
                            <div class="d-flex align-items-center mb-3">
                                <!-- Owner Image -->
                                <div class="flex-shrink-0">
                                    <img src="{{ assetUrl($listing->folder_name . '/' . $listing->file_name) }}"
                                        alt="{{ $listing->first_name }} {{ $listing->last_name }}"
                                        class="rounded-circle border"
                                        style="width: 70px; height: 70px; object-fit: cover;">
                                </div>
                                
                                <!-- Owner Details -->
                                <div class="ms-3">
                                    <h5 class="mb-1 fw-semibold">{{ $listing->first_name }} {{ $listing->last_name }}</h5>
                                    
                                    <p class="mb-1 small text-muted d-flex align-items-center">
                                        <i class="fas fa-phone-alt me-2 text-success"></i>
                                        <a href="tel:{{ $listing->contact_number }}" class="text-decoration-none">
                                            {{ $listing->contact_number }}
                                        </a>
                                    </p>
                                    
                                    <p class="mb-0 small text-muted d-flex align-items-center">
                                        <i class="far fa-envelope me-2 text-primary"></i>
                                        <a href="mailto:{{ $listing->email }}" class="text-decoration-none">
                                            {{ $listing->email }}
                                        </a>
                                    </p>
                                </div>
                            </div>

                            <!-- Divider -->
                            <hr>

                            <!-- Map -->
                            <div class="property-location-area">
                                <div id="map" class="w-100 rounded-3" style="height: 300px;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row" style="font-family: 'Josefin Sans', sans-serif;">
                <div class="col-md-12">
                    <h5 class="nearby-title fw-semibold mb-4">{{ __('Nearby Information') }}</h5>
                </div>
                @foreach ($information as $info)
                    <div class="col-md-6 col-lg-6 col-xl-4">
                        <div class="single-nearby-info card h-100 border-0 shadow-sm rounded-3 overflow-hidden">
                            <div class="nearby-image">
                                <img src="{{ assetUrl($info->folder_name . '/' . $info->file_name) }}" 
                                    alt="{{ $info->name }}" 
                                    class="img-fluid w-100" 
                                    style="height: 180px; object-fit: cover;">
                            </div>
                            <div class="nearby-info p-3">
                                <h6 class="nearby-name fw-semibold mb-2">{{ $info->name }}</h6>
                                <p class="nearby-details text-muted small mb-2">
                                    {!! Str::limit($info->details, 100, '...') !!}
                                </p>
                                <p class="distance-nearby mb-1">
                                    <strong>{{ __('Distance') }}:</strong> <span>{{ $info->distance }}</span>
                                </p>
                                <p class="nearby-contact mb-0">
                                    <strong>{{ __('Contact Number') }}:</strong> 
                                    <a href="tel:{{ $info->contact_number }}" class="text-decoration-none theme-text">
                                        {{ $info->contact_number }}
                                    </a>
                                </p>
                            </div>
                        </div>
                    </div>
                @endforeach
                <div class="col-lg-12 mt-5">
                <div class="single-property-specie">
                    @if (count(json_decode($listing->advantage) ?? []))
                        <h5 class="specie-catagories fw-semibold mb-3">{{ __('Advantages') }}</h5>
                        <div class="specie-list row g-2">
                            @foreach (json_decode($listing->advantage) ?? [] as $advantage)
                                <div class="col-md-6">
                                    <p class="specie-list-text d-flex align-items-center mb-2">
                                        <i class="me-2">
                                            <img src="{{ asset('assets/images/properties-img/arrow.png') }}" alt="">
                                        </i>
                                        {{ getPropertyAdvantages($advantage) }}
                                    </p>
                                </div>
                            @endforeach
                        </div>
                    @endif
                    <button class="btn rounded theme-btn px-4 mt-2" 
                            data-bs-toggle="modal"
                            data-bs-target="#contactModal"
                            data-bs-whatever="@mdo">
                        {{ __('Contact Agency') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="related-properties py-5" style="font-family: 'Josefin Sans', sans-serif;">
        <div class="container">
            <div class="row mb-4">
                <div class="col-lg-12 text-center">
                    <span class="fw-bold fs-5">{{ __('Related Properties') }}</span>
                    <div class="divider mx-auto"></div>
                </div>
            </div>

            <div class="row g-4">
                @foreach ($relatedListings as $relatedListing)
                    <div class="col-md-6 col-lg-6 col-xl-4">
                        <div class="property-card h-100">
                            <div class="property-image position-relative">
                                <img src="{{ assetUrl($relatedListing->folder_name . '/' . $relatedListing->file_name) }}"
                                    alt="{{ $relatedListing->name }}">
                                <span class="price-tag">
                                    {{ currencyPrice($relatedListing->price) }}
                                    @if ($relatedListing->price_duration_type == DURATION_TYPE_MONTHLY)
                                        /{{ __('Month') }}
                                    @elseif($relatedListing->price_duration_type == DURATION_TYPE_YEARLY)
                                        /{{ __('Year') }}
                                    @endif
                                </span>
                            </div>

                            <div class="property-info p-3">
                                <h5 class="mb-2">{{ $relatedListing->name }}</h5>
                                <p class="text-muted small mb-3">
                                    <i class="fas fa-map-marker-alt me-2"></i>{{ $relatedListing->address }}
                                </p>

                                <div class="d-flex justify-content-between mb-3">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-cube me-2 text-primary"></i>
                                        <span>{{ $relatedListing->interior }} {{ __('Sqft') }}</span>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <i class="ri-layout-2-fill me-2 text-primary"></i>
                                        <span>{{ $relatedListing->bed_room }} {{ __('Rooms') }}</span>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <i class="ri-user-2-fill me-2 text-primary"></i>
                                        <span>{{ $relatedListing->type }}</span>
                                    </div>
                                </div>

                                <a href="{{ route('listing.details', $relatedListing->slug) }}"
                                    class="btn theme-btn w-100 rounded-pill">View Details</a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- contact modal  --}}
    <div class="modal fade bd-example-modal-lg" id="contactModal" tabindex="-1" aria-labelledby="contactModalLabel"
        aria-hidden="true" style="font-family: 'Josefin Sans', sans-serif;">
        <div class="modal-dialog modal-dialog-centered modal-lg customs-model">
            <div class="modal-content">
                <div class="customs-model-content">
                    <div class="contact-us">
                        <p>{{ $listing->name }}</p>
                    </div>
                    <div class="contact-us-from-properties">
                        <form class="ajax" action="{{ route('listing.contact.store') }}" method="POST"
                            data-handler="getShowMessage">
                            @csrf
                            <input type="hidden" name="id" value="{{ $listing->id }}">
                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="single-contact-us-from">
                                        <label for="name">{{ __('Full Name') }}</label>
                                        <input class="form-control" type="text" placeholder="{{ __('Full Name') }}"
                                            id="name" name="name">
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="single-contact-us-from">
                                        <label for="email">{{ __('Email') }}</label>
                                        <input class="form-control" type="email" placeholder="{{ __('Email') }}"
                                            id="email" name="email">
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="single-contact-us-from">
                                        <label for="phone">{{ __('Phone Number') }}</label>
                                        <input class="form-control" type="text"
                                            placeholder="{{ __('Phone Number') }}" id="phone" name="phone">
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="single-contact-us-from">
                                        <label for="profession">{{ __('Profession') }}</label>
                                        <input class="form-control" type="text" placeholder="{{ __('Profession') }}"
                                            id="profession" name="profession">
                                    </div>
                                </div>
                                <div class="col-lg-12">
                                    <div class="single-contact-us-from">
                                        <label for="details">{{ __('Details') }}</label>
                                        <textarea name="details" id="details" class="form-control" rows="10" placeholder="{{ __('Details') }}"></textarea>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="submit-box-text">
                                        <button type="submit"
                                            class="btn rounded theme-btn py-3 px-4 mt-3 me-4">{{ __('Submit') }}</button>
                                        <button type="button" data-bs-dismiss="modal"
                                            class="btn theme-btn-outline py-3 px-4 mt-3">{{ __('Cancel') }}</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('style')
    <link rel="stylesheet" href="{{ asset('assets/properties/css/properties.css') }}">
    <link href='https://api.mapbox.com/mapbox.js/v3.3.1/mapbox.css' rel='stylesheet' />
    <link href='https://api.mapbox.com/mapbox.js/plugins/leaflet-markercluster/v1.0.0/MarkerCluster.css'
        rel='stylesheet' />
    <link href='https://api.mapbox.com/mapbox.js/plugins/leaflet-markercluster/v1.0.0/MarkerCluster.Default.css'
        rel='stylesheet' />
    <style>
        #map {
            width: 100%;
            height: 454px;
            border-radius: 5px;
            border: 2px solid #75cff0;
        }
    </style>
@endpush
@push('script')
    <script src='https://api.mapbox.com/mapbox.js/v3.3.1/mapbox.js'></script>
    <script src='https://api.mapbox.com/mapbox.js/plugins/leaflet-markercluster/v1.0.0/leaflet.markercluster.js'></script>
    <script>
        $('.property-slider-area').owlCarousel({
            loop: true,
            margin: 24,
            nav: true,
            navText: ['<i class="fas fa-chevron-left"></i>', '<i class="fas fa-chevron-right"></i>'],
            responsive: {
                0: {
                    items: 1
                },
                600: {
                    items: 2
                },
                1000: {
                    items: 2
                }
            }
        })

        L.mapbox.accessToken =
            "{{ getOption('map_api_key') }}";

        const mapData = @json($mapData);
        //map data prepare
        window.map = L.mapbox.map('map')
            .setView([-37.82, 175.215], 14)
            .addLayer(L.mapbox.styleLayer('mapbox://styles/mapbox/streets-v11'));

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
                    icon: L.icon({
                        iconUrl: feature.properties.image,
                        iconSize: [40, 40],
                        className: 'border border-3 border-light rounded-5',
                    }),
                    title: feature.properties.name
                });
                marker.bindPopup(popupContent);
                window.markers.addLayer(marker);
            });

            window.map.addLayer(window.markers);
            window.map.fitBounds(window.markers.getBounds())
        }
    </script>
@endpush
