@extends('maintainer.layouts.app')

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

                        <!-- All Maintainer Table Area Start -->
                        <div class="maintaince-request-table-area">
                            <!-- datatable Start -->
                            <div class="bg-off-white theme-border radius-4 p-25">
                                <table id="allDataTable" class="table bg-off-white aaa theme-border p-20 dt-responsive">
                                    <thead>
                                        <tr>
                                            <th>{{ __('SL') }}</th>
                                            <th class="all">{{ __('Issue') }}</th>
                                            <th class="desktop">{{ __('Details') }}</th>
                                            <th class="desktop">{{ __('Status') }}</th>
                                            <th class="all">{{ __('Action') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($requests as $request)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $request->issue_name }}</td>
                                                <td>{{ Str::limit($request->details, 50, '...') }}</td>
                                                <td>
                                                    @if ($request->status == MAINTENANCE_REQUEST_STATUS_COMPLETE)
                                                        <div class="status-btn status-btn-green font-13 radius-4">
                                                            {{ __('Completed') }}</div>
                                                    @elseif($request->status == MAINTENANCE_REQUEST_STATUS_INPROGRESS)
                                                        <div class="status-btn status-btn-orange font-13 radius-4">
                                                            {{ __('In Progress') }}</div>
                                                    @else
                                                        <div class="status-btn status-btn-red font-13 radius-4">
                                                            {{ __('Pending') }}</div>
                                                    @endif

                                                </td>
                                                <td>
                                                    <div class="tbl-action-btns d-inline-flex">
                                                        <button type="button" class="p-1 tbl-action-btn view"
                                                            data-id="{{ $request->id }}" data-bs-toggle="modal"
                                                            data-bs-target="#viewModal" title="View">
                                                            <span class="iconify" data-icon="carbon:view-filled"></span>
                                                        </button>
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
    </div>

    {{-- Modal  --}}
    <div class="modal fade" id="viewModal" tabindex="-1" aria-labelledby="viewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="viewModalLabel">{{ __('Details') }}</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span
                            class="iconify" data-icon="akar-icons:cross"></span></button>
                </div>
                <form class="ajax" action="{{ route('maintainer.maintenance-request.status.change') }}" method="POST"
                    data-handler="getShowMessage">
                    <input type="hidden" name="id" id="id">
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
    <input type="hidden" id="getInfoRoute" value="{{ route('maintainer.maintenance-request.get.info') }}">
@endsection
@push('style')
    @include('common.layouts.datatable-style')
@endpush

@push('script')
    @include('common.layouts.datatable-script')
    <script src="{{ asset('/') }}assets/js/pages/alldatatables.init.js"></script>
    <script src="{{ asset('assets/js/custom/maintainer-maintenance-request.js') }}"></script>
@endpush
