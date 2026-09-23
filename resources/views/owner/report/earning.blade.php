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
                            <ol class="cs-crumb"><li><a href="{{ route('owner.dashboard') }}">{{ __('Dashboard') }}</a></li><li>›</li><li>{{ __('Report') }}</li><li>›</li><li>{{ $pageTitle }}</li></ol>
                        </div>
                    </div>

                    <div class="cs-card cs-card--pad cs-controls" style="margin-bottom:16px;">
                        <form action="">
                            <div class="row align-items-center">
                                <div class="col-md-3 mb-2">
                                    <select class="form-select" name="property_id" id="property_id">
                                        <option value="" selected>--{{ __('Select Property') }}--</option>
                                        @foreach ($properties as $property)
                                            <option value="{{ $property->id }}">{{ $property->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3 mb-2">
                                    <select class="form-select" name="unit_id" id="unit_id">
                                        <option value="" selected>--{{ __('Select Option') }}--</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <div class="input-group">
                                        <span class="input-group-text">{{ __('From') }}</span>
                                        <input type="date" class="form-control" id="start_date" name="start_date" aria-label="Start Date">
                                        <span class="input-group-text">{{ __('to') }}</span>
                                        <input type="date" class="form-control" id="end_date" name="end_date" aria-label="End Date">
                                    </div>
                                </div>
                                <div class="col-md-2 mb-2">
                                    <button type="button" class="default-btn theme-btn-purple" id="searchBtn" title="{{ __('Search') }}">{{ __('Search') }}</button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="cs-card"><div class="cs-card__body">
                        <table id="allReportEarningDataTable" class="table aaa dt-responsive" style="width:100%;">
                            <thead>
                                <tr>
                                    <th>{{ __('SL') }}</th>
                                    <th class="text-center" data-priority="1">{{ __('Invoice') }}</th>
                                    <th class="text-center">{{ __('Property') }}</th>
                                    <th class="text-center">{{ __('Unit') }}</th>
                                    <th class="text-center">{{ __('Invoice Month') }}</th>
                                    <th class="text-center">{{ __('Tax') }}</th>
                                    <th class="text-end">{{ __('Amount') }}</th>
                                </tr>
                            </thead>
                            <tfoot>
                                <tr>
                                    <th colspan="5"></th>
                                    <th></th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div></div>
                </div>
            </div>
        </div>
    </div>
    <input type="hidden" id="ownerName" value="{{ auth()->user()->getNameAttribute() }}">
    <input type="hidden" id="appName" value="{{ getOption('app_name') }}">
    <input type="hidden" id="userLogo" value="{{ $base64UserImage }}">
    <input type="hidden" id="appLogo" value="{{ $base64Image }}">
    <input type="hidden" id="earningReportRoute" value="{{ route('owner.reports.earning') }}">
    <input type="hidden" id="reportExportRoute" value="{{ route('owner.reports.export', 'earning') }}">
    <input type="hidden" id="getPropertyUnitsRoute" value="{{ route('owner.property.getPropertyUnits') }}">
@endsection
@push('style')
    @include('common.layouts.datatable-style')
@endpush
@push('script')
    @include('common.layouts.datatable-script')
    <script src="{{ asset('assets/js/custom/report-earning.js') }}"></script>
@endpush
