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
                        <table id="leaseReportDataTable" class="table aaa dt-responsive" style="width:100%;">
                            <thead>
                                <tr>
                                    <th>{{ __('SL') }}</th>
                                    <th class="text-center" data-priority="1">{{ __('Tenant Name') }}</th>
                                    <th class="text-center">{{ __('Property') }}</th>
                                    <th class="text-center">{{ __('Unit') }}</th>
                                    <th class="text-center">{{ __('Start Date') }}</th>
                                    <th class="text-end">{{ __('End Date') }}</th>
                                </tr>
                            </thead>
                            <tfoot>
                                <tr>
                                    <th colspan="4"></th>
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
    <input type="hidden" id="leaseReportRoute" value="{{ route('owner.reports.lease') }}">
    <input type="hidden" id="reportExportRoute" value="{{ route('owner.reports.export', 'lease') }}">
@endsection
@push('style')
    @include('common.layouts.datatable-style')
@endpush
@push('script')
    @include('common.layouts.datatable-script')
    <script src="{{ asset('assets/js/custom/report-lease.js') }}"></script>
@endpush
