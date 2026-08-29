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

                    <div class="cs-card"><div class="cs-card__body">
                        <table id="maintenanceReportDataTable" class="table aaa dt-responsive" style="width:100%;">
                            <thead>
                                <tr>
                                    <th>{{ __('SL') }}</th>
                                    <th class="text-center" data-priority="1">{{ __('Property Name') }}</th>
                                    <th class="text-center">{{ __('Unit') }}</th>
                                    <th class="text-center">{{ __('Tenant Name') }}</th>
                                    <th class="text-center">{{ __('Issue') }}</th>
                                    <th class="text-center">{{ __('Status') }}</th>
                                </tr>
                            </thead>
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
    <input type="hidden" id="maintenanceReportRoute" value="{{ route('owner.reports.maintenance') }}">
    <input type="hidden" id="reportExportRoute" value="{{ route('owner.reports.export', 'maintenance') }}">
@endsection
@push('style')
    @include('common.layouts.datatable-style')
@endpush
@push('script')
    @include('common.layouts.datatable-script')
    <script src="{{ asset('assets/js/custom/report-maintenance.js') }}"></script>
@endpush
