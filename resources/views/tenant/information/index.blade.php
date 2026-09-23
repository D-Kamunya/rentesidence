@extends('tenant.layouts.app')

@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="page-content-wrapper bg-white p-30 radius-20">
                    @include('centresidence._design')

                    <div class="cs-titlebar">
                        <div>
                            <h1 class="cs-title">{{ $pageTitle }}</h1>
                            <ol class="cs-crumb"><li><a href="{{ route('tenant.dashboard') }}">{{ __('Dashboard') }}</a></li><li>›</li><li>{{ $pageTitle }}</li></ol>
                        </div>
                    </div>

                    <p class="cs-muted" style="margin-bottom:20px;max-width:640px;">
                        {{ __('Useful places near your home — shared by your landlord.') }}
                    </p>

                    @if ($information->isEmpty())
                        <div class="cs-card"><div class="cs-card__body" style="text-align:center;padding:56px 20px;">
                            <i class="ri-map-pin-2-line" style="font-size:38px;color:var(--blue);"></i>
                            <p style="font-size:15px;font-weight:600;color:var(--gray-700);margin:14px 0 4px;">{{ __('No nearby information yet') }}</p>
                            <p class="cs-muted" style="max-width:420px;margin:0 auto;">{{ __('When your landlord adds nearby amenities, they will show up here.') }}</p>
                        </div></div>
                    @else
                        <div class="info-grid">
                            @foreach ($information as $info)
                                <div class="cs-card info-card">
                                    <div class="info-card__img">
                                        <img src="{{ $info->image }}" alt="{{ $info->name }}">
                                    </div>
                                    <div class="cs-card__body">
                                        <p class="info-card__name">{{ $info->name }}</p>
                                        <p class="cs-muted" style="margin:6px 0 0;">{{ Str::limit($info->additional_information, 60, '…') }}</p>
                                        <div class="info-card__meta">
                                            <span><i class="ri-route-line"></i> {{ $info->distance }}</span>
                                            <span><i class="ri-phone-line"></i> <a href="tel:{{ preg_replace('/[^0-9+]/', '', $info->contact_number) }}" class="info-card__tel">{{ $info->contact_number }}</a></span>
                                        </div>
                                        <button type="button" class="cs-btn cs-btn--ghost view" style="width:100%;justify-content:center;margin-top:14px;"
                                            data-id="{{ $info->id }}" data-bs-toggle="modal"
                                            data-bs-target="#viewTenantInformationModal"
                                            title="{{ __('View Details') }}">{{ __('View Details') }}</button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <style>
        .info-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(280px, 1fr)); gap:18px; }
        .info-card { margin-bottom:0; display:flex; flex-direction:column; }
        .info-card__img { height:150px; overflow:hidden; background:var(--gray-100); }
        .info-card__img img { width:100%; height:100%; object-fit:cover; display:block; }
        .info-card .cs-card__body { display:flex; flex-direction:column; flex:1; }
        .info-card__name { font-size:14px; font-weight:600; color:var(--gray-900); margin:0; }
        .info-card__meta { display:flex; flex-direction:column; gap:6px; margin-top:12px; font-size:12.5px; color:var(--gray-700); }
        .info-card__meta i { color:var(--blue); margin-right:4px; }
        .info-card__tel { color:var(--blue); font-weight:500; text-decoration:none; }
        .info-card__tel:hover { text-decoration:underline; }
    </style>

    {{-- Modal  --}}
    <div class="modal fade cs-modal" id="viewTenantInformationModal" tabindex="-1" aria-labelledby="viewTenantInformationModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="viewTenantInformationModalLabel">{{ __('Information') }}</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span
                            class="iconify" data-icon="akar-icons:cross"></span></button>
                </div>
                <div class="modal-body">
                    <div class="view-information-page-modal-content">
                        <div class="view-information-page-box mb-25">
                            <label class="label-text-title color-heading font-medium mb-2">{{ __('Image') }}</label>
                            <div class="information-details-img radius-4 mb-25">
                                <img class="fit-image radius-4 image">
                            </div>
                        </div>
                        <div class="view-information-page-box mb-25">
                            <label class="label-text-title color-heading font-medium mb-2">{{ __('Name') }} :
                            </label> <span class="name"></span>
                        </div>
                        <div class="view-information-page-box mb-25">
                            <label class="label-text-title color-heading font-medium mb-2">{{ __('Property') }} : </label>
                            <span class="property"></span>
                        </div>

                        <div class="view-information-page-box mb-25">
                            <label class="label-text-title color-heading font-medium mb-2">{{ __('Distance') }} : </label>
                            <span class="distance"></span>
                        </div>

                        <div class="view-information-page-box mb-25">
                            <label class="label-text-title color-heading font-medium mb-2">{{ __('Contact Number') }} :
                            </label>
                            <span class="contact_number"></span>
                        </div>

                        <div class="view-information-page-box">
                            <label
                                class="label-text-title color-heading font-medium mb-2">{{ __('Additional Information') }}
                                : </label>
                            <span class="additional_information"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <input type="hidden" id="getInfoRoute" value="{{ route('tenant.information.get.info') }}">
@endsection

@push('script')
    <script src="{{ asset('assets/js/custom/information-view.js') }}"></script>
@endpush
