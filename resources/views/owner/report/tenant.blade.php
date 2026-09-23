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
                        <table id="tenantReportDataTable" class="table aaa dt-responsive" style="width:100%;">
                            <thead>
                                <tr>
                                    <th>{{ __('SL') }}</th>
                                    <th class="text-center" data-priority="1">{{ __('Name') }}</th>
                                    <th class="text-center">{{ __('Email') }}</th>
                                    <th class="text-center">{{ __('Contact') }}</th>
                                    <th class="text-center">{{ __('Property') }}</th>
                                    <th class="text-center">{{ __('Unit') }}</th>
                                    <th class="text-end">{{ __('Paid') }}</th>
                                    <th class="text-end">{{ __('Due') }}</th>
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
    <input type="hidden" id="tenantReportRoute" value="{{ route('owner.reports.tenant') }}">
    <input type="hidden" id="reportExportRoute" value="{{ route('owner.reports.export', 'tenant') }}">
@endsection
@push('style')
    @include('common.layouts.datatable-style')
@endpush
@push('script')
    @include('common.layouts.datatable-script')
    <script src="{{ asset('assets/js/custom/report-tenant.js') }}"></script>
@endpush
