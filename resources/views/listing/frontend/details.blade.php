@extends('saas.frontend.layouts.app')
@section('content')
    <div class="property-details-area">
        <div class="row">
            <div class="col-md-12">
                <div class="property-bander-text">
                    <h5 class="property-bander-header">{{ __('Property Details') }}</h5>
                    <p class="property-home-part">
                        <a href="{{ route('frontend') }}">{{ __('Home') }} </a> / <a
                            href="{{ route('listings') }}">{{ __('Properties') }}</a> /
                        {{ $listing->name }}
                    </p>
                </div>
            </div>
        </div>
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
            <div class="row">
                <div class="col-lg-7">
                    <div class="property-description-area">
                        <h4 class="property-description-title">{{ __('Description') }}</h4>
                        <p class="property-description-info">{!! $listing->details !!}</p>
                        <div class="location-property-area">
                            <p class="location-name">{{ __('Location') }}</p>
                            <h4 class="location-title">{{ $listing->address }}</h4>
                            <h6 class="property-price">
                                {{ __('Price') }} : <span> {{ currencyPrice($listing->price) }}/
                                    @if ($listing->price_duration_type == DURATION_TYPE_MONTHLY)
                                        {{ __('Month') }}
                                    @elseif($listing->price_duration_type == DURATION_TYPE_YEARLY)
                                        {{ __('Yearly') }}
                                    @else
                                        {{ __('Custom') }}
                                    @endif
                                </span>
                            </h6>
                        </div>
                        <div class="single-property-specie">
                            <h5 class="specie-catagories">{{ __('Specification') }}</h5>
                            <div class="specie-list">
                                @if ($listing->bed_room)
                                    <p class="specie-list-text">
                                        <i>
                                            <img src="{{ asset('assets/images/properties-img/arrow.png') }}">
                                        </i>
                                        {{ __('Bed Rooms') }} : <span>{{ $listing->bed_room }}</span>
                                    </p>
                                @endif
                                @if ($listing->bath_room)
                                    <p class="specie-list-text">
                                        <i>
                                            <img src="{{ asset('assets/images/properties-img/arrow.png') }}">
                                        </i>
                                        {{ __('Bath Rooms') }} : <span>{{ $listing->bath_room }}</span>
                                    </p>
                                @endif
                                @if ($listing->kitchen_room)
                                    <p class="specie-list-text">
                                        <i>
                                            <img src="{{ asset('assets/images/properties-img/arrow.png') }}">
                                        </i>
                                        {{ __('Kitchen Rooms') }} : <span>{{ $listing->kitchen_room }}</span>
                                    </p>
                                @endif
                                @if ($listing->dining_room)
                                    <p class="specie-list-text">
                                        <i>
                                            <img src="{{ asset('assets/images/properties-img/arrow.png') }}">
                                        </i>
                                        {{ __('Dining Rooms') }} : <span>{{ $listing->dining_room }}</span>
                                    </p>
                                @endif
                                @if ($listing->living_room)
                                    <p class="specie-list-text">
                                        <i>
                                            <img src="{{ asset('assets/images/properties-img/arrow.png') }}">
                                        </i>
                                        {{ __('Living Rooms') }} : <span>{{ $listing->living_room }}</span>
                                    </p>
                                @endif
                                @if ($listing->storage_room)
                                    <p class="specie-list-text">
                                        <i>
                                            <img src="{{ asset('assets/images/properties-img/arrow.png') }}">
                                        </i>
                                        {{ __('Storage Rooms') }} : <span>{{ $listing->storage_room }}</span>
                                    </p>
                                @endif
                                @foreach (explode(',', $listing->other_room) as $room)
                                    @if ($room)
                                        <p class="specie-list-text">
                                            <i>
                                                <img src="{{ asset('assets/images/properties-img/arrow.png') }}">
                                            </i>
                                            <span>{{ $room }}</span>
                                        </p>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                        <div class="single-property-specie">
                            <h5 class="specie-catagories">{{ __('Property Details') }}</h5>
                            <div class="specie-list">
                                <p class="specie-list-text">
                                    <i>
                                        <img src="{{ asset('assets/images/properties-img/arrow.png') }}">
                                    </i>
                                    {{ __('Interior') }} : <span>{{ $listing->interior }}</span>
                                </p>
                                <p class="specie-list-text">
                                    <i>
                                        <img src="{{ asset('assets/images/properties-img/arrow.png') }}">
                                    </i>
                                    {{ __('Type') }} : <span>{{ $listing->type }}</span>
                                </p>
                            </div>
                        </div>
                        <div class="single-property-specie">
                            <h5 class="specie-catagories">{{ __('Amenities') }}</h5>
                            <div class="specie-list">
                                @foreach (json_decode($listing->amenities) ?? [] as $amenity)
                                    <p class="specie-list-text">
                                        <i>
                                            <img src="{{ asset('assets/images/properties-img/arrow.png') }}"
                                                alt="">
                                        </i>
                                        {{ getPropertyAmenities($amenity) }}
                                    </p>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="property-woner-location">
                        <div class="property-woner-info">
                            <div class="property-woner-image">
                                <img src="{{ assetUrl($listing->folder_name . '/' . $listing->file_name) }}"
                                    alt="{{ $listing->first_name }} {{ $listing->last_name }}">
                            </div>
                            <div class="property-woner-name-location">
                                <h5 class="property-woner-name">
                                    {{ $listing->first_name }} {{ $listing->last_name }}
                                </h5>
                                <p>
                                    <i class="fas fa-phone-alt"></i>
                                    <a href="tel:{{ $listing->contact_number }}">{{ $listing->contact_number }}</a>
                                </p>
                                <p>
                                    <i class="far fa-envelope"></i>
                                    <a href="mailto:{{ $listing->email }}">{{ $listing->email }}</a>
                                </p>
                            </div>
                        </div>
                        <div class="property-location-area">
                            <div id="map" class="w-100"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <h5 class="nearby-title">{{ __('Nearby Information') }}</h5>
                </div>
                @foreach ($information as $info)
                    <div class="col-md-6  col-lg-6 col-xl-4">
                        <div class="single-nearby-info">
                            <div class="nearby-image">
                                <img src="{{ assetUrl($info->folder_name . '/' . $info->file_name) }}" alt="">
                            </div>
                            <div class="nearby-info">
                                <h6 class="nearby-name">{{ $info->name }}</h6>
                                <p class="nearby-details">{!! Str::limit($info->details, 100, '...') !!}</p>
                                <p class="distance-nearby">{{ __('Distance') }}: <span> {{ $info->distance }}</span></p>
                                <p class="nearby-contact">{{ __('Contact Number') }}: <a
                                        href="tel:{{ $info->contact_number }}">{{ $info->contact_number }}</a> </p>
                            </div>
                        </div>
                    </div>
                @endforeach
                <div class="col-lg-12">
                    <div class="single-property-specie">
                        @if (count(json_decode($listing->advantage) ?? []))
                            <h5 class="specie-catagories">{{ __('Advantages') }}</h5>
                            <div class="specie-list">
                                @foreach (json_decode($listing->advantage) ?? [] as $advantage)
                                    <p class="specie-list-text">
                                        <i>
                                            <img src="{{ asset('assets/images/properties-img/arrow.png') }}"
                                                alt="">
                                        </i>
                                        {{ getPropertyAdvantages($advantage) }}
                                    </p>
                                @endforeach
                            </div>
                        @endif
                        <button class="btn rounded  theme-btn px-4 mt-5" data-bs-toggle="modal"
                            data-bs-target="#contactModal" data-bs-whatever="@mdo">{{ __('Contact Us') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="related-properties">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <h4 class="related-title">{{ __('Related Properties') }}</h4>
                </div>
            </div>
            <div class="row">
                @foreach ($relatedListings as $relatedListing)
                    <div class="col-md-6 col-lg-6 col-xl-4">
                        <div class="all-single-properties">
                            <div class="single-properties">
                                <div class="properties-img">
                                    <img src="{{ assetUrl($relatedListing->folder_name . '/' . $relatedListing->file_name) }}"
                                        alt="{{ $relatedListing->name }}">
                                </div>
                                <div class="properties-text-info">
                                    <h5 class="properties-name">{{ $relatedListing->name }}</h5>
                                    <div class="properties-location">
                                        <div class="properties-icon-type-location">
                                            <i class="fas fa-map-marker-alt"></i>
                                        </div>
                                        <p>{{ $relatedListing->address }}</p>
                                    </div>
                                    <div class="room-details">
                                        <div class="single-option">
                                            <div class="properties-icon-type">
                                                <i class="fas fa-cube"></i>
                                            </div>
                                            <p>{{ $relatedListing->interior }} {{ __('Sqft') }}</p>
                                        </div>
                                        <div class="single-option">
                                            <div class="properties-icon-type">
                                                <i class="ri-layout-2-fill"></i>
                                            </div>
                                            <p>{{ $relatedListing->bed_room }} {{ __('Rooms') }}</p>
                                        </div>
                                        <div class="single-option">
                                            <div class="properties-icon-type">
                                                <i class="ri-user-2-fill"></i>
                                            </div>
                                            <p>{{ $relatedListing->type }}</p>
                                        </div>
                                        <p class="price-properties">{{ __('Price') }} :
                                            {{ currencyPrice($listing->price) }}/
                                            @if ($listing->price_duration_type == DURATION_TYPE_MONTHLY)
                                                {{ __('Month') }}
                                            @elseif($listing->price_duration_type == DURATION_TYPE_YEARLY)
                                                {{ __('Yearly') }}
                                            @else
                                                {{ __('Custom') }}
                                            @endif
                                        </p>
                                    </div>
                                    <a href="{{ route('listing.details', $relatedListing->slug) }}"
                                        class="btn mt-3 py-3 rounded  theme-btn w-100">{{ __('View Details') }}</a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- contact modal  --}}
    <div class="modal fade bd-example-modal-lg" id="contactModal" tabindex="-1" aria-labelledby="contactModalLabel"
        aria-hidden="true">
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
