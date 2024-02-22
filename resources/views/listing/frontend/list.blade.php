@extends('saas.frontend.layouts.app')
@section('content')
    <div class="properties-area">
        <div class="container-fluid p-0">
            <div class="row">
                <div class="col-lg-12">
                    <div class="properties-filter">
                        <form action="{{ route('listings') }}" method="GET" id="searchForm">
                            <div class="first-filter">
                                <div class="properties-search">
                                    <div class="search-icon">
                                        <i class="ri-search-line"></i>
                                    </div>
                                    <input type="search" placeholder="{{ __('Search') }}" name="q" id="search"
                                        value="{{ request('q') }}">
                                </div>
                                <div class="single-filter">
                                    <select name="country" id="country">
                                        <option value="">{{ __('Country') }}</option>
                                        @foreach ($countries as $country)
                                            @if ($country)
                                                <option value="{{ $country }}"
                                                    {{ request('country') == $country ? 'selected' : '' }}>
                                                    {{ Str::ucfirst($country) }}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>
                                <div class="single-filter">
                                    <select name="state" id="state">
                                        <option value="">{{ __('State') }}</option>
                                        @foreach ($states as $state)
                                            @if ($state)
                                                <option value="{{ $state }}"
                                                    {{ request('state') == $state ? 'selected' : '' }}>
                                                    {{ Str::ucfirst($state) }}
                                                </option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>
                                <div class="single-filter">
                                    <select name="city" id="city">
                                        <option value="">{{ __('City') }}</option>
                                        @foreach ($cities as $city)
                                            @if ($city)
                                                <option value="{{ $city }}"
                                                    {{ request('city') == $city ? 'selected' : '' }}>
                                                    {{ Str::ucfirst($city) }}
                                                </option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>
                                <div class="single-filter">
                                    <select name="bed_room" id="bed_room">
                                        <option value="">{{ __('Bed Room') }}</option>
                                        @foreach ($bedRooms as $bedRoom)
                                            @if ($bedRoom)
                                                <option value="{{ $bedRoom }}"
                                                    {{ request('bed_room') == $bedRoom ? 'selected' : '' }}>
                                                    {{ $bedRoom }}
                                                </option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>
                                <div class="single-filter">
                                    <select name="bath_room" id="bath_room">
                                        <option value="">{{ __('Bath Room') }}</option>
                                        @foreach ($bathRooms as $bathRoom)
                                            @if ($bathRoom)
                                                <option value="{{ $bathRoom }}"
                                                    {{ request('bath_room') == $bathRoom ? 'selected' : '' }}>
                                                    {{ $bathRoom }}
                                                </option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>
                                <div class="single-filter">
                                    <select name="kitchen" id="kitchen">
                                        <option value="">{{ __('Kitchen Room') }}</option>
                                        @foreach ($kitchens as $kitchen)
                                            @if ($kitchen)
                                                <option value="{{ $kitchen }}"
                                                    {{ request('kitchen') == $kitchen ? 'selected' : '' }}>
                                                    {{ $kitchen }}
                                                </option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="row">
                @if (count($listings))
                    <div class="col-lg-4">
                        <div class="map-area h-100">
                            <div id="map" class="w-100 map-property-h"></div>
                        </div>
                    </div>
                    <div class="col-lg-8 pe-lg-5 margin-top-1">
                        <div class="row">
                            @foreach ($listings as $listing)
                                <div class="col-md-6 col-lg-6 col-xl-4">
                                    <div class="all-single-properties">
                                        <div class="single-properties">
                                            <div class="properties-img">
                                                <img src="{{ assetUrl($listing->folder_name . '/' . $listing->file_name) }}"
                                                    alt="{{ $listing->name }}">
                                            </div>
                                            <div class="properties-text-info">
                                                <h5 class="properties-name">{{ $listing->name }}</h5>
                                                <div class="properties-location">
                                                    <div class="properties-icon-type-location">
                                                        <i class="fas fa-map-marker-alt"></i>
                                                    </div>
                                                    <p>{{ $listing->address }}</p>
                                                </div>
                                                <div class="room-details">
                                                    <div class="single-option">
                                                        <div class="properties-icon-type">
                                                            <i class="fas fa-cube"></i>
                                                        </div>
                                                        <p>{{ $listing->interior }}</p>
                                                    </div>
                                                    <div class="single-option">
                                                        <div class="properties-icon-type">
                                                            <i class="ri-layout-2-fill"></i>
                                                        </div>
                                                        <p>{{ $listing->bed_room }} {{ __('Bed Rooms') }}</p>
                                                    </div>
                                                    <div class="single-option">
                                                        <div class="properties-icon-type">
                                                            <i class="ri-user-2-fill"></i>
                                                        </div>
                                                        <p>{{ $listing->type }}</p>
                                                    </div>
                                                    <p class="price-properties">{{ __('Price') }} :
                                                        {{ currencyPrice($listing->price) }} /
                                                        @if ($listing->price_duration_type == DURATION_TYPE_MONTHLY)
                                                            {{ __('Month') }}
                                                        @elseif($listing->price_duration_type == DURATION_TYPE_YEARLY)
                                                            {{ __('Yearly') }}
                                                        @else
                                                            {{ __('Custom') }}
                                                        @endif
                                                    </p>
                                                </div>
                                                <a href="{{ route('listing.details', $listing->slug) }}"
                                                    class="btn mt-3 py-3 rounded  theme-btn w-100">{{ __('View Details') }}</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                            <div class="col-md-12">
                                {{ $listings->appends(request()->query())->links() }}
                            </div>
                        </div>
                    </div>
                @else
                    <div class="col-md-12 text-center mt-15">
                        {{ __('No Data Found') }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
@push('style')
    <link rel="stylesheet" href="{{ asset('assets/properties/css/properties.css') }}">
    <link href='https://api.mapbox.com/mapbox.js/v3.3.1/mapbox.css' rel='stylesheet' />
    <link href='https://api.mapbox.com/mapbox.js/plugins/leaflet-markercluster/v1.0.0/MarkerCluster.css' rel='stylesheet' />
    <link href='https://api.mapbox.com/mapbox.js/plugins/leaflet-markercluster/v1.0.0/MarkerCluster.Default.css'
        rel='stylesheet' />
    <style>
        #map {
            width: 100%;
            height: 100%;
            border-radius: 5px;
            border: 2px solid #75cff0;
        }
    </style>
@endpush
@push('script')
    <script>
        $(document).on('change', '#search,#country,#state,#city,#bed_room,#bath_room,#kitchen', function() {
            $('#searchForm').submit();
        });
    </script>

    <script src='https://api.mapbox.com/mapbox.js/v3.3.1/mapbox.js'></script>
    <script src='https://api.mapbox.com/mapbox.js/plugins/leaflet-markercluster/v1.0.0/leaflet.markercluster.js'></script>
    <script>
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
            window.map.fitBounds(window.markers.getBounds());
        }
    </script>
@endpush
