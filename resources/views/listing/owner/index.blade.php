@extends('owner.layouts.app')
@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="page-content-wrapper bg-white p-30 radius-20">
                    <div class="row">
                        <div class="col-12">
                            <div
                                class="page-title-box d-sm-flex align-items-center justify-content-between border-bottom mb-20">
                                <div class="page-title-left">
                                    <h3 class="mb-sm-0">{{ $pageTitle }}</h3>
                                </div>
                                <div class="page-title-right">
                                    <ol class="breadcrumb mb-0">
                                        <li class="breadcrumb-item"><a href="{{ route('owner.dashboard') }}"
                                                title="{{ __('Dashboard') }}">{{ __('Dashboard') }}</a></li>
                                        <li class="breadcrumb-item" aria-current="page">{{ __('My Listing') }}</li>
                                        <li class="breadcrumb-item active" aria-current="page">{{ $pageTitle }}</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        @foreach ($listings as $listing)
                            <div class="col-md-6 col-lg-6 col-xl-4">
                                <div class="all-single-properties">
                                    <div class="single-properties">
                                        <div class="properties-img">
                                            <img src="{{ assetUrl($listing->folder_name . '/' . $listing->file_name) }}">
                                        </div>
                                        <div class="properties-text-info">
                                            <div class="d-flex position-relative">
                                                <h5 class="properties-name">{{ $listing->name }}</h5>
                                                <div
                                                    class="property-item-dropdown position-absolute radius-3 text-end ms-2">
                                                    <div class="dropdown">
                                                        <a class="dropdown-toggle dropdown-toggle-nocaret" href="#"
                                                            data-bs-toggle="dropdown" aria-expanded="false">
                                                            <i class="ri-more-2-fill"></i>
                                                        </a>
                                                        <ul class="dropdown-menu dropdown-menu-end">
                                                            <li><a href="{{ route('owner.listing.edit', $listing->id) }}"
                                                                    class="dropdown-item font-13"
                                                                    title="{{ __('Edit') }}">{{ __('Edit') }}</a>
                                                            </li>
                                                            <li>
                                                                <a href="#"
                                                                    data-url="{{ route('owner.listing.delete', $listing->id) }}"
                                                                    data-reload="true" class="dropdown-item font-13 delete"
                                                                    title="{{ __('Delete') }}">{{ __('Delete') }}</a>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="properties-location">
                                                <div>
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                                        viewBox="0 0 20 20" fill="none">
                                                        <path
                                                            d="M10 1.66666C6.775 1.66666 4.16666 4.27499 4.16666 7.49999C4.16666 11.875 10 18.3333 10 18.3333C10 18.3333 15.8333 11.875 15.8333 7.49999C15.8333 4.27499 13.225 1.66666 10 1.66666ZM10 9.58332C8.85 9.58332 7.91666 8.64999 7.91666 7.49999C7.91666 6.34999 8.85 5.41666 10 5.41666C11.15 5.41666 12.0833 6.34999 12.0833 7.49999C12.0833 8.64999 11.15 9.58332 10 9.58332Z"
                                                            fill="#A5ABBA" />
                                                    </svg>
                                                </div>
                                                <p>{{ Str::limit($listing->address, 40, '...') }}</p>
                                            </div>
                                            <div class="room-details">
                                                <div class="single-option">
                                                    <div>
                                                        <i class="fas fa-cube"></i>
                                                    </div>
                                                    <p>{{ $listing->interior }}</p>
                                                </div>
                                                <div class="single-option">
                                                    <div>
                                                        <i class="ri-layout-2-fill"></i>
                                                    </div>
                                                    <p>{{ $listing->bed_room }} {{ __('Bed Rooms') }}</p>
                                                </div>
                                                <div class="single-option">
                                                    <div>
                                                        <i class="ri-user-2-fill"></i>
                                                    </div>
                                                    <p>{{ $listing->type }}</p>
                                                </div>
                                                <p class="price-properties">{{ __('Price') }} :
                                                    {{ currencyPrice($listing->price) }}
                                                    / {{ getDurationTypes($listing->price_duration_type) }}</p>
                                            </div>
                                            <a href="{{ route('listing.details', $listing->slug) }}" target="_blank"
                                                class="btn theme-btn upload-count-plus w-100 py-3 mt-3">{{ __('View Details') }}</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('style')
    <link rel="stylesheet" href="{{ asset('assets/properties/css/properties.css') }}">
@endpush
