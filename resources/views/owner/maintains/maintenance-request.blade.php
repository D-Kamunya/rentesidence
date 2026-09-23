@extends('owner.layouts.app')

@section('content')
    <div class="main-content">

        <div class="page-content">
            <div class="container-fluid">
                <!-- Page Content Wrapper Start -->
                <div class="page-content-wrapper bg-white p-30 radius-20">
                    @include('centresidence._design')

                    <div class="cs-titlebar">
                        <div>
                            <h1 class="cs-title">{{ $pageTitle }}</h1>
                            <ol class="cs-crumb"><li><a href="{{ route('owner.dashboard') }}">{{ __('Dashboard') }}</a></li><li>›</li><li>{{ $pageTitle }}</li></ol>
                        </div>
                        <button type="button" class="cs-btn cs-btn--primary" id="add"
                            title="{{ __('Add Maintenance Request') }}">
                            <i class="ri-add-line"></i> {{ __('Add Maintenance Request') }}
                        </button>
                    </div>

                    <div class="cs-card cs-card--pad" style="margin-bottom:16px;">
                        <label class="cs-label">{{ __('Filter by property') }}</label>
                        <div style="max-width:320px;">
                            <select class="cs-select" id="search_property">
                                <option value="" selected>{{ __('All properties') }}</option>
                                @foreach ($properties as $property)
                                    <option value="{{ $property->name }}">{{ $property->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="cs-card"><div class="cs-card__body">
                        <table id="allMaintenanceDataTable" class="table aaa dt-responsive" style="width:100%;">
                            <thead>
                                <tr>
                                    <th>{{ __('SL') }}</th>
                                    <th>{{ __('Property') }}</th>
                                    <th>{{ __('Unit Name') }}</th>
                                    <th data-priority="1">{{ __('Issue Name') }}</th>
                                    <th>{{ __('Details') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Action') }}</th>
                                </tr>
                            </thead>
                        </table>
                    </div></div>
                </div>
                <!-- Page Content Wrapper End -->
            </div>
        </div>
        <!-- End Page-content -->
    </div>

    <!-- Add Information Modal Start -->
    <div class="modal fade cs-modal" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="addModalLabel">{{ __('Add Maintenance Request') }}</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span
                            class="iconify" data-icon="akar-icons:cross"></span></button>
                </div>
                <form class="ajax" action="{{ route('owner.maintenance-request.store') }}" method="POST"
                    data-handler="getShowMessage">
                    <div class="modal-body">
                        <!-- Modal Inner Form Box Start -->
                        <div class="modal-inner-form-box">
                            <div class="row">
                                <div class="col-md-6 mb-25">
                                    <label
                                        class="label-text-title color-heading font-medium mb-2">{{ __('Property') }}</label>
                                    <select class="form-select flex-shrink-0 property_id" name="property_id">
                                        <option value="" selected>--{{ __('Select Property') }}--</option>
                                        @foreach ($properties as $property)
                                            <option value="{{ $property->id }}">{{ $property->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6 mb-25">
                                    <label
                                        class="label-text-title color-heading font-medium mb-2">{{ __('Unit') }}</label>
                                    <select class="form-select flex-shrink-0 unit_id" name="unit_id">
                                        <option value="">--{{ __('Select Unit') }}--</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-25">
                                    <label
                                        class="label-text-title color-heading font-medium mb-2">{{ __('Issue') }}</label>
                                    <select class="form-select flex-shrink-0 issue_id" name="issue_id">
                                        <option value="">--{{ __('Select Issue') }}--</option>
                                        @foreach ($issues as $issue)
                                            <option value="{{ $issue->id }}">{{ $issue->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6 mb-25">
                                    <label
                                        class="label-text-title color-heading font-medium mb-2">{{ __('Issue') }}</label>
                                    <select class="form-select flex-shrink-0 status" name="status">
                                        <option value="1">{{ __('Completed') }}</option>
                                        <option value="2">{{ __('In Progress') }}</option>
                                        <option value="3">{{ __('Pending') }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12 mb-25">
                                    <label
                                        class="label-text-title color-heading font-medium mb-2">{{ __('Details') }}</label>
                                    <textarea class="form-control details" name="details" placeholder="{{ __('Details') }}"></textarea>
                                </div>
                                <div class="col-md-12">
                                    <label
                                        class="label-text-title color-heading font-medium mb-2">{{ __('Attach') }}</label>
                                    <input type="file" class="form-control" name="attach">
                                </div>
                            </div>
                        </div>
                        <!-- Modal Inner Form Box End -->
                    </div>

                    <div class="modal-footer justify-content-start">
                        <button type="button" class="theme-btn-back me-3" data-bs-dismiss="modal"
                            title="{{ __('Back') }}">{{ __('Back') }}</button>
                        <button type="submit" class="theme-btn me-3"
                            title="{{ __('Add Request') }}">{{ __('Add Request') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade cs-modal" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="editModalLabel">{{ __('Edit Maintenance Request') }}</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span
                            class="iconify" data-icon="akar-icons:cross"></span></button>
                </div>
                <form class="ajax" action="{{ route('owner.maintenance-request.store') }}" method="POST"
                    data-handler="getShowMessage">
                    <input type="hidden" name="id" id="id">
                    <div class="modal-body">
                        <!-- Modal Inner Form Box Start -->
                        <div class="modal-inner-form-box">
                            <div class="row">
                                <div class="col-md-6 mb-25">
                                    <label
                                        class="label-text-title color-heading font-medium mb-2">{{ __('Property') }}</label>
                                    <select class="form-select flex-shrink-0 property_id" name="property_id">
                                        <option value="" selected>--{{ __('Select Property') }}--</option>
                                        @foreach ($properties as $property)
                                            <option value="{{ $property->id }}">{{ $property->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6 mb-25">
                                    <label
                                        class="label-text-title color-heading font-medium mb-2">{{ __('Unit') }}</label>
                                    <select class="form-select flex-shrink-0 unit_id" name="unit_id">
                                        <option value="">--{{ __('Select Unit') }}--</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-25">
                                    <label
                                        class="label-text-title color-heading font-medium mb-2">{{ __('Issue') }}</label>
                                    <select class="form-select flex-shrink-0 issue_id" name="issue_id">
                                        <option value="">--{{ __('Select Issue') }}--</option>
                                        @foreach ($issues as $issue)
                                            <option value="{{ $issue->id }}">{{ $issue->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6 mb-25">
                                    <label
                                        class="label-text-title color-heading font-medium mb-2">{{ __('Issue') }}</label>
                                    <select class="form-select flex-shrink-0 status" name="status">
                                        <option value="1">{{ __('Completed') }}</option>
                                        <option value="2">{{ __('In Progress') }}</option>
                                        <option value="3">{{ __('Pending') }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12 mb-25">
                                    <label
                                        class="label-text-title color-heading font-medium mb-2">{{ __('Details') }}</label>
                                    <textarea class="form-control details" name="details" placeholder="{{ __('Details') }}"></textarea>
                                </div>
                                <div class="col-md-12">
                                    <label
                                        class="label-text-title color-heading font-medium mb-2">{{ __('Attach') }}</label>
                                    <input type="file" class="form-control" name="attach">
                                </div>
                            </div>
                        </div>
                        <!-- Modal Inner Form Box End -->
                    </div>

                    <div class="modal-footer justify-content-start">
                        <button type="button" class="theme-btn-back me-3" data-bs-dismiss="modal"
                            title="{{ __('Back') }}">{{ __('Back') }}</button>
                        <button type="submit" class="theme-btn me-3"
                            title="{{ __('Update') }}">{{ __('Update') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade cs-modal" id="viewModal" tabindex="-1" aria-labelledby="viewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="viewModalLabel">{{ __('Details') }}</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span
                            class="iconify" data-icon="akar-icons:cross"></span></button>
                </div>
                <form class="ajax" action="{{ route('owner.maintenance-request.status.change') }}" method="POST"
                    data-handler="getShowMessage">
                    <input type="hidden" name="id" id="viewId">
                    <div class="modal-body">
                        <div class="view-information-page-modal-content">
                            <div class="maintenance-request-view-top-box mb-25">
                                <div class="row align-items-start">
                                    <div class="col-md-8">
                                        <div class="view-information-page-box mb-25">
                                            <label
                                                class="label-text-title color-heading font-medium mb-2">{{ __('Issue') }}</label>
                                            <p class="issue_name"></p>
                                        </div>
                                    </div>
                                    <div class="col-sm-4 col-md-4 text-start text-lg-end">
                                        <select class="form-select status" name="status">
                                            <option value="1">{{ __('Completed') }}</option>
                                            <option value="2">{{ __('In Progress') }}</option>
                                            <option value="3">{{ __('Pending') }}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row align-items-start">
                                    <div class="col-md-6">
                                        <div class="view-information-page-box ">
                                            <label
                                                class="label-text-title color-heading font-medium mb-2">{{ __('Property') }}
                                                : </label>
                                            <span class="property_name"></span>
                                        </div>

                                    </div>
                                    <div class="col-md-6">
                                        <div class="view-information-page-box ">
                                            <label
                                                class="label-text-title color-heading font-medium mb-2">{{ __('Unit') }}
                                                : </label>
                                            <span class="unit_name"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="view-information-page-box mb-25">
                                <label class="label-text-title color-heading font-medium mb-2">{{ __('Details') }}</label>
                                <p class="view_details"></p>
                            </div>
                            <div class="view-information-page-box mb-25">
                                <label class="label-text-title color-heading font-medium mb-2">{{ __('Attach') }} :
                                </label>
                                <a href="" class="attach" target="_blank"></a>
                            </div>
                            <div class="view-information-page-box mb-25">
                                <label class="label-text-title color-heading font-medium mb-2">{{ __('Amount') }}</label>
                                <input type="number" class="form-control amount" name="amount" step="any"
                                    value="0" placeholder="{{ __('Amount') }}">
                            </div>
                            <div class="view-information-page-box mb-25">
                                <label class="label-text-title color-heading font-medium mb-2">{{ __('Invoice') }}</label>
                                <input type="file" class="form-control" name="invoice">
                                <small><a href="" target="_blank" class="invoice"></a></small>
                            </div>

                        </div>
                    </div>
                    <div class="modal-footer justify-content-start">
                        <button type="button" class="theme-btn-back me-3" data-bs-dismiss="modal"
                            title="{{ __('Back') }}">{{ __('Back') }}</button>
                        <button type="submit" class="theme-btn me-3"
                            title="{{ __('Update') }}">{{ __('Update') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- Add Information Modal End -->
    <input type="hidden" id="getInfoRoute" value="{{ route('owner.maintenance-request.get.info') }}">
    <input type="hidden" id="route" value="{{ route('owner.maintenance-request.index') }}">
    <input type="hidden" id="getPropertyUnitsRoute" value="{{ route('owner.property.getPropertyUnits') }}">
@endsection
@push('style')
    @include('common.layouts.datatable-style')
@endpush

@push('script')
    @include('common.layouts.datatable-script')
    <script src="{{ asset('assets/js/custom/maintenance-request.js') }}"></script>
@endpush
