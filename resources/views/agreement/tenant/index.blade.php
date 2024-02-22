@extends('tenant.layouts.app')

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
                                        <li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}"
                                                title="{{ __('Dashboard') }}">{{ __('Dashboard') }}</a></li>
                                        <li class="breadcrumb-item active" aria-current="page">{{ $pageTitle }}</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="billing-center-area bg-off-white theme-border radius-4 p-25">
                            <table id="allDataTable" class="table responsive theme-border p-20 ">
                                <thead>
                                    <tr>
                                        <td>{{ __('SL') }}</td>
                                        <td>{{ __('Sender Name') }}</td>
                                        <td>{{ __('Date') }}</td>
                                        <td class="text-center">{{ __('Action') }}</td>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($agreements as $agreement)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $agreement->first_name }} {{ $agreement->last_name }}</td>
                                            <td>{{ date('Y-m-d', strtotime($agreement->status_date_time)) }}</td>
                                            <td class="text-center">
                                                <div class="tbl-action-btns d-inline-flex">
                                                    <a href="{{ route('tenant.agreement.download', $agreement->envelope_id) }}"
                                                        class="p-1 tbl-action-btn" title="{{ __('Download') }}"><span
                                                            class="iconify"
                                                            data-icon="material-symbols:download"></span></a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection


@push('script')
    <script src="{{ asset('assets/js/pages/alldatatables.init.js') }}"></script>
@endpush
