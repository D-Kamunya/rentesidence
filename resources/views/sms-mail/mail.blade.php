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
                                        <li class="breadcrumb-item active" aria-current="page">{{ $pageTitle }}</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="property-top-search-bar">
                            <div class="row align-items-end">
                                <div class="col-md-12">
                                    <div class="property-top-search-bar-right text-end">
                                        <button type="button" class="theme-btn mb-25" id="add"
                                            title="{{ __('New Send') }}">{{ __('New Send') }}</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="information-table-area">
                            <div class="table-responsive bg-off-white theme-border radius-4 p-25">
                                <table id="allDataTable" class="table bg-off-white theme-border dt-responsive">
                                    <thead>
                                        <tr>
                                            <th class="all">{{ __('SL') }}</th>
                                            <th class="all">{{ __('Date') }}</th>
                                            <th class="all">{{ __('Subject') }}</th>
                                            <th class="all">{{ __('Message') }}</th>
                                            <th class="all">{{ __('Email') }}</th>
                                            <th class="all">{{ __('Status') }}</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="mailSendModal" tabindex="-1" aria-labelledby="mailSendModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="mailSendModalLabel">{{ __('Send Mail') }}</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span
                            class="iconify" data-icon="akar-icons:cross"></span></button>
                </div>
                <form class="ajax" action="{{ route('sms-mail.mail.send') }}" method="POST"
                    data-handler="getShowMessage">
                    <div class="modal-body">
                        @csrf
                        <div class="modal-inner-form-box">
                            <div class="row">
                                <div class="col-md-12 mb-25">
                                    <label
                                        class="label-text-title color-heading font-medium mb-2">{{ __('Target Audience') }}
                                        <strong class="text-danger">*</strong></label>
                                    <select class="form-select flex-shrink-0 target_audience" name="target_audience">
                                        <option value="" selected>--{{ __('Target Audience') }}--</option>
                                        <option value="1">{{ __('By property') }}</option>
                                        <option value="2">{{ __('By user type') }}</option>
                                        <option value="3">{{ __('Custom') }}</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="bg-off-white modal-inner-form-box p-20 radius-4 theme-border by-property d-none">
                            <div class="row">
                                <div class="col-md-6 mb-25">
                                    <label
                                        class="label-text-title color-heading font-medium mb-2">{{ __('Property') }}</label>
                                    <div class="my-custom-select-box">
                                        <select name="property_id[]" data-selected-text-format="count" multiple
                                            class="my-custom-select form-select selectpicker w-100 property_id">
                                            <option value="all">{{ __('All') }}</option>
                                            @foreach ($properties as $property)
                                                <option value="{{ $property->id }}">{{ $property->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-25">
                                    <label
                                        class="label-text-title color-heading font-medium mb-2">{{ __('Unit') }}</label>
                                    <div class="my-custom-select-box">
                                        <select name="unit_id[]" data-selected-text-format="count" multiple
                                            class="my-custom-select form-select w-100 unit_id">
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-off-white modal-inner-form-box p-20 radius-4 theme-border by-user-type d-none">
                            <div class="row">
                                <div class="col-md-6 mb-25">
                                    <label
                                        class="label-text-title color-heading font-medium mb-2">{{ __('Type') }}</label>
                                    <div class="my-custom-select-box">
                                        <select name="user_type" class="form-select w-100 user_type">
                                            <option value="">{{ __('Select User') }}</option>
                                            <option value="1">{{ __('Tenant') }}</option>
                                            <option value="2">{{ __('Maintainer') }}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-25 user-type-tenant d-none">
                                    <label
                                        class="label-text-title color-heading font-medium mb-2">{{ __('Tenant') }}</label>
                                    <div class="my-custom-select-box">
                                        <select name="tenant_id[]" data-selected-text-format="count" multiple
                                            class="my-custom-select form-select selectpicker w-100">
                                            <option value="all">{{ __('All') }}</option>
                                            @foreach ($tenants as $tenant)
                                                <option value="{{ $tenant->user_id }}">{{ $tenant->first_name }}
                                                    {{ $tenant->last_name }} ({{ $tenant->email }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-25 user-type-maintainer d-none">
                                    <label
                                        class="label-text-title color-heading font-medium mb-2">{{ __('Maintainer') }}</label>
                                    <div class="my-custom-select-box">
                                        <select name="maintainer_id[]" data-selected-text-format="count" multiple
                                            class="my-custom-select form-select selectpicker w-100">
                                            <option value="all">{{ __('All') }}</option>
                                            @foreach ($maintainers as $maintainer)
                                                <option value="{{ $maintainer->user_id }}">{{ $maintainer->first_name }}
                                                    {{ $maintainer->last_name }} ({{ $maintainer->email }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-off-white modal-inner-form-box p-20 radius-4 theme-border audience-custom d-none">
                            <div class="row">
                                <div class="col-md-12 mb-25">
                                    <label
                                        class="label-text-title color-heading font-medium mb-2">{{ __('User') }}</label>
                                    <div class="my-custom-select-box">
                                        <select name="custom_user_id[]" data-selected-text-format="count" multiple
                                            class="my-custom-select form-select selectpicker w-100">
                                            <option value="all">{{ __('All') }}</option>
                                            @foreach ($tenants as $tenant)
                                                <option value="{{ $tenant->user_id }}">{{ $tenant->first_name }}
                                                    {{ $tenant->last_name }} ({{ $tenant->email }}-{{ __('Tenant') }})
                                                </option>
                                            @endforeach
                                            @foreach ($maintainers as $maintainer)
                                                <option value="{{ $maintainer->user_id }}">{{ $maintainer->first_name }}
                                                    {{ $maintainer->last_name }}
                                                    ({{ $maintainer->email }}-{{ __('Maintainer') }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-off-white modal-inner-form-box mt-15 p-20 radius-4 theme-border message d-none">
                            <div class="row">
                                <div class="col-md-12 mb-25">
                                    <label class="label-text-title color-heading font-medium mb-2">{{ __('Subject') }}
                                        <strong class="text-danger">*</strong> </label>
                                    <input name="subject" id="subject" class="form-control"
                                        placeholder="{{ __('Subject') }}" />
                                </div>
                                <div class="col-md-12 mb-25">
                                    <label class="label-text-title color-heading font-medium mb-2">{{ __('Message') }}
                                        <strong class="text-danger">*</strong> </label>
                                    <textarea name="message" id="message" class="form-control" placeholder="{{ __('Message') }}"></textarea>
                                </div>
                            </div>
                        </div>
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

    <input type="hidden" id="route" value="{{ route('sms-mail.mail') }}">
    <input type="hidden" id="getPropertyUnitsRoute" value="{{ route('owner.property.getUnitByPropertyIds') }}">
@endsection
@push('style')
    @include('common.layouts.datatable-style')
    <link rel="stylesheet" href="{{ asset('assets/libs/bootstrap-select/bootstrap-select.min.css') }}">
@endpush

@push('script')
    @include('common.layouts.datatable-script')
    <script src="{{ asset('assets/libs/bootstrap-select/bootstrap-select.min.js') }}"></script>
    <script src="{{ asset('assets/js/custom/sms-mail/mail.js') }}"></script>
@endpush
