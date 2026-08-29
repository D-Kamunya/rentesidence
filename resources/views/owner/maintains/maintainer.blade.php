@extends('owner.layouts.app')

@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="page-content-wrapper bg-white p-30 radius-20">
                    @include('centresidence._design')

                    <div class="cs-titlebar">
                        <div>
                            <h1 class="cs-title">{{ $pageTitle }}</h1>
                            <ol class="cs-crumb"><li><a href="{{ route('owner.dashboard') }}">{{ __('Dashboard') }}</a></li><li>›</li><li>{{ $pageTitle }}</li></ol>
                        </div>
                        <button type="button" class="cs-btn cs-btn--primary add" title="{{ __('Add Maintainer') }}">
                            <i class="ri-add-line"></i> {{ __('Add Maintainer') }}
                        </button>
                    </div>

                    <div class="cs-card cs-card--pad" style="margin-bottom:16px;">
                        <div class="cr-perm">
                            <div class="cr-perm__text">
                                <h3 class="cs-card__title">{{ __('Let caretakers confirm cash rent') }}</h3>
                                <p class="cr-perm__desc">{{ __('Off by default. When on, a caretaker can confirm that a tenant paid rent in cash — it records the payment against the invoice, is logged against the caretaker, and notifies you every time. Leave it off to keep rent confirmation to yourself.') }}</p>
                            </div>
                            <label class="cr-switch" title="{{ __('Toggle caretaker cash confirmation') }}">
                                <input type="checkbox" id="caretakerConfirmToggle" {{ !empty($canConfirmRent) ? 'checked' : '' }}>
                                <span class="cr-switch__slider"></span>
                            </label>
                        </div>
                    </div>

                    <div class="cs-card cs-card--pad cs-controls" style="margin-bottom:16px;">
                        <label class="cs-label">{{ __('Filter by property') }}</label>
                        <div style="max-width:320px;">
                            <select class="form-select" id="search_property">
                                <option value="" selected>{{ __('All properties') }}</option>
                                @foreach ($properties as $property)
                                    <option value="{{ $property->name }}">{{ $property->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="cs-card"><div class="cs-card__body">
                        <table id="allMaintainerDataTable" class="table aaa dt-responsive" style="width:100%;">
                            <thead>
                                <tr>
                                    <th>{{ __('SL') }}</th>
                                    <th>{{ __('Image') }}</th>
                                    <th data-priority="1">{{ __('Name') }}</th>
                                    <th class="d-none">{{ __('Name') }}</th>
                                    <th>{{ __('Email') }}</th>
                                    <th>{{ __('Contact Number') }}</th>
                                    <th>{{ __('Property') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Action') }}</th>
                                </tr>
                            </thead>
                        </table>
                    </div></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Information Modal Start -->
    <div class="modal fade cs-modal" id="addMaintainerModal" tabindex="-1" aria-labelledby="addMaintainerModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form class="ajax" action="{{ route('owner.maintainer.store') }}" method="POST"
                    enctype="multipart/form-data" data-handler="getShowMessage">
                    <input type="hidden" id="id" name="id" value="">
                    <input type="hidden" id="user_id" name="user_id">
                    <div class="modal-header">
                        <h4 class="modal-title" id="addMaintainerModalLabel">{{ __('Add Maintainer') }}</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span
                                class="iconify" data-icon="akar-icons:cross"></span></button>
                    </div>
                    <div class="modal-body">
                        <!-- Modal Inner Form Box Start -->
                        <div class="modal-inner-form-box">
                            <div class="row">
                                <!-- Upload Profile Photo Box Start -->
                                <div class="upload-profile-photo-box mb-25">
                                    <div class="profile-user position-relative d-inline-block">
                                        <img src="{{ asset('assets/images/users/empty-user.jpg') }}"
                                            class="rounded-circle avatar-xl maintainer-user-profile-image image"
                                            alt="">
                                        <div class="avatar-xs p-0 rounded-circle maintainer-profile-photo-edit">
                                            <input id="maintainer-profile-img-file-input" type="file"
                                                class="maintainer-profile-img-file-input" name="image">
                                            <label for="maintainer-profile-img-file-input"
                                                class="maintainer-profile-photo-edit avatar-xs">
                                                <span class="avatar-title rounded-circle" title="Upload Image">
                                                    <i class="ri-camera-fill"></i>
                                                </span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <!-- Upload Profile Photo Box End -->
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-25">
                                    <label
                                        class="label-text-title color-heading font-medium mb-2">{{ __('First Name') }}</label>
                                    <input type="text" name="first_name" class="form-control first_name"
                                        placeholder="{{ __('First Name') }}">
                                </div>
                                <div class="col-md-6 mb-25">
                                    <label
                                        class="label-text-title color-heading font-medium mb-2">{{ __('Last Name') }}</label>
                                    <input type="text" name="last_name" class="form-control last_name"
                                        placeholder="{{ __('Last Name') }}">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-25">
                                    <label
                                        class="label-text-title color-heading font-medium mb-2">{{ __('Email') }}</label>
                                    <input type="email" name="email" class="form-control email"
                                        placeholder="{{ __('Email') }}">
                                </div>
                                <div class="col-md-6 mb-25">
                                    <label
                                        class="label-text-title color-heading font-medium mb-2">{{ __('Contact Number') }}</label>
                                    <input type="text" name="contact_number" class="form-control contact_number"
                                        placeholder="{{ __('Contact Number') }}">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-25">
                                    <label
                                        class="label-text-title color-heading font-medium mb-2">{{ __('Password') }}</label>
                                    <input type="password" name="password" class="form-control"
                                        placeholder="{{ __('Password') }}">
                                </div>
                                <div class="col-md-6 mb-25">
                                    <label
                                        class="label-text-title color-heading font-medium mb-2">{{ __('Assign Property') }}</label>
                                    <div class="my-custom-select-box">
                                        <select name="property_id[]" data-selected-text-format="count" multiple
                                            class="my-custom-select form-select selectpicker w-100 property_id">
                                            @foreach ($properties as $property)
                                                <option value="{{ $property->id }}">{{ $property->name }}</option>
                                            @endforeach
                                        </select>
                                        <small>{{ __('You can selected multiple properties') }}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Modal Inner Form Box End -->
                    </div>

                    <div class="modal-footer justify-content-start">
                        <button type="button" class="theme-btn-back me-3" data-bs-dismiss="modal"
                            title="{{ __('Back') }}">{{ __('Back') }}</button>
                        <button type="submit" class="theme-btn me-3"
                            title="{{ __('Submit') }}">{{ __('Submit') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- Add Information Modal End -->
    <input type="hidden" id="getInfoRoute" value="{{ route('owner.maintainer.get.info') }}">
    <input type="hidden" id="route" value="{{ route('owner.maintainer.index') }}">
@endsection
@push('style')
    @include('common.layouts.datatable-style')
    <link rel="stylesheet" href="{{ asset('assets/libs/bootstrap-select/bootstrap-select.min.css') }}">
    <style>
        .cr-perm { display:flex; align-items:flex-start; justify-content:space-between; gap:18px; flex-wrap:wrap; }
        .cr-perm__text { max-width:640px; }
        .cr-perm__desc { font-size:13px; color:#6b7280; margin:4px 0 0; line-height:1.55; }
        .cr-switch { position:relative; display:inline-block; width:46px; height:26px; flex:none; cursor:pointer; }
        .cr-switch input { opacity:0; width:0; height:0; }
        .cr-switch__slider { position:absolute; inset:0; background:#cbd5e1; border-radius:99px; transition:background .2s; }
        .cr-switch__slider::before { content:''; position:absolute; height:20px; width:20px; left:3px; top:3px; background:#fff; border-radius:50%; transition:transform .2s; box-shadow:0 1px 3px rgba(0,0,0,.2); }
        .cr-switch input:checked + .cr-switch__slider { background:#185FA5; }
        .cr-switch input:checked + .cr-switch__slider::before { transform:translateX(20px); }
        .cr-switch input:disabled + .cr-switch__slider { opacity:.6; cursor:not-allowed; }
    </style>
@endpush

@push('script')
    @include('common.layouts.datatable-script')
    <script src="{{ asset('assets/libs/bootstrap-select/bootstrap-select.min.js') }}"></script>
    <script src="{{ asset('assets/js/pages/maintainer-profile-photo.init.js') }}"></script>
    <script src="{{ asset('assets/js/custom/maintainer.js') }}"></script>
    <script>
        (function () {
            const toggle = document.getElementById('caretakerConfirmToggle');
            if (!toggle) return;
            toggle.addEventListener('change', function () {
                const on = toggle.checked;
                toggle.disabled = true;
                fetch("{{ route('owner.maintainer.permissions') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ caretaker_can_confirm_rent: on ? 1 : 0 }),
                })
                .then(r => r.json())
                .then(data => {
                    if (typeof toastr !== 'undefined') {
                        (data.status ? toastr.success : toastr.info)(data.message || '{{ __('Saved.') }}');
                    }
                })
                .catch(() => {
                    toggle.checked = !on; // revert on failure
                    if (typeof toastr !== 'undefined') toastr.error('{{ __('Could not update. Please try again.') }}');
                })
                .finally(() => { toggle.disabled = false; });
            });
        })();
    </script>
@endpush
