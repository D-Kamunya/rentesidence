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
                                                title="Dashboard">{{ __('Dashboard') }}</a></li>
                                        <li class="breadcrumb-item active" aria-current="page">{{ $pageTitle }}</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="property-top-search-bar">
                            <div class="row align-items-center">
                                @if (count($templates) < count(emailTemplates()))
                                    <div class="col-md-12 d-flex justify-content-end">
                                        <div class="property-top-search-bar-right">
                                            <button type="button" class="theme-btn mb-25" id="add"
                                                title="{{ __('Add New Template') }}">{{ __('Add New Template') }}</button>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="notice-board-table-area">
                            <div class="table-responsive bg-off-white theme-border radius-4 p-25">
                                <table id="allDataTable" class="table bg-off-white aaa theme-border p-20 dt-responsive">
                                    <thead>
                                        <tr>
                                            <th class="all">{{ __('SL') }}</th>
                                            <th class="all">{{ __('Subject') }}</th>
                                            <th class="all">{{ __('Body') }}</th>
                                            <th class="all">{{ __('Category') }}</th>
                                            <th class="all">{{ __('Status') }}</th>
                                            <th class="all">{{ __('Action') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($templates as $template)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{!! $template->subject !!}</td>
                                                <td>{!! strip_tags(Str::limit($template->body, 100, '...')) !!}</td>
                                                <td>
                                                    {{ emailTemplates($template->category) }}
                                                </td>
                                                <td>
                                                    @if ($template->status == ACTIVE)
                                                        <div class="status-btn status-btn-blue font-13 radius-4">
                                                            {{ __('Active') }}</div>
                                                    @else
                                                        <div class="status-btn status-btn-orange font-13 radius-4">
                                                            {{ __('Default') }}</div>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div class="tbl-action-btns d-inline-flex">
                                                        <button type="button" class="p-1 tbl-action-btn edit"
                                                            data-id="{{ $template->id }}"
                                                            title="{{ __('Edit') }}"><span class="iconify"
                                                                data-icon="clarity:note-edit-solid"></span></button>
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

    <!-- Add New Template Modal Start -->
    <div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="addModalLabel">{{ __('Add New Template') }}</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span
                            class="iconify" data-icon="akar-icons:cross"></span></button>
                </div>
                <form class="ajax" action="{{ route('sms-mail.template.store') }}" method="POST"
                    enctype="multipart/form-data" data-handler="getShowMessage">
                    <div class="modal-body">
                        <div class="modal-inner-form-box">
                            <div class="row">
                                <div class="col-md-12 mb-25">
                                    <table class="table">
                                        <tr>
                                            <th>{{ __('Body Key') }}</th>
                                            <td>
                                                @foreach (emailTemplateFields() as $key => $item)
                                                    <span>{{ $key }}</span>
                                                @endforeach
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-25">
                                    <label
                                        class="label-text-title color-heading font-medium mb-2">{{ __('Category') }}</label>
                                    <select class="form-select flex-shrink-0" name="category">
                                        <option value="" selected>--{{ __('Select') }}--</option>
                                        @foreach (emailTemplates() as $key => $category)
                                            <option value="{{ $key }}">{{ $category }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6 mb-25">
                                    <label
                                        class="label-text-title color-heading font-medium mb-2">{{ __('Status') }}</label>
                                    <select class="form-select flex-shrink-0" name="status">
                                        <option value="0">{{ __('Default') }}</option>
                                        <option value="1">{{ __('Active') }}</option>
                                    </select>
                                </div>
                                <div class="col-md-12 mb-25">
                                    <label
                                        class="label-text-title color-heading font-medium mb-2">{{ __('Subject') }}</label>
                                    <input type="text" name="subject" class="form-control"
                                        placeholder="{{ __('Subject') }}">
                                </div>
                                <div class="col-md-12 mb-25">
                                    <label
                                        class="label-text-title color-heading font-medium mb-2">{{ __('Body') }}</label>
                                    <textarea class="form-control summernote" name="body" placeholder="{{ __('Body') }}"></textarea>
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

    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="editModalLabel">{{ __('Edit Template') }}</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span
                            class="iconify" data-icon="akar-icons:cross"></span></button>
                </div>
                <form class="ajax" action="{{ route('sms-mail.template.store') }}" method="POST"
                    enctype="multipart/form-data" data-handler="getShowMessage">
                    <input type="hidden" name="id" id="id">
                    <div class="modal-body">
                        <div class="modal-inner-form-box">
                            <div class="row">
                                <div class="col-md-12 mb-25">
                                    <table class="table">
                                        <tr>
                                            <th>{{ __('Body Key') }}</th>
                                            <td>
                                                @foreach (emailTemplateFields() as $key => $item)
                                                    <span>{{ $key }}</span>
                                                @endforeach
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-25">
                                    <label
                                        class="label-text-title color-heading font-medium mb-2">{{ __('Category') }}</label>
                                    <select class="form-select flex-shrink-0" name="category">
                                        <option value="" selected>--{{ __('Select') }}--</option>
                                        @foreach (emailTemplates() as $key => $category)
                                            <option value="{{ $key }}">{{ $category }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6 mb-25">
                                    <label
                                        class="label-text-title color-heading font-medium mb-2">{{ __('Status') }}</label>
                                    <select class="form-select flex-shrink-0" name="status">
                                        <option value="0">{{ __('Default') }}</option>
                                        <option value="1" >{{ __('Active') }}</option>
                                    </select>
                                </div>
                                <div class="col-md-12 mb-25">
                                    <label
                                        class="label-text-title color-heading font-medium mb-2">{{ __('Subject') }}</label>
                                    <input type="text" name="subject" class="form-control"
                                        placeholder="{{ __('Subject') }}">
                                </div>
                                <div class="col-md-12 mb-25">
                                    <label
                                        class="label-text-title color-heading font-medium mb-2">{{ __('Body') }}</label>
                                    <textarea class="form-control summernote" name="body" placeholder="{{ __('Body') }}"></textarea>
                                </div>
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

    <input type="hidden" id="getInfoRoute" value="{{ route('sms-mail.template.details') }}">
@endsection
@push('style')
    @include('common.layouts.datatable-style')
    <link rel="stylesheet" href="{{ asset('assets/libs/summernote/summernote.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/libs/summernote/summernote-lite.min.css') }}">
@endpush

@push('script')
    @include('common.layouts.datatable-script')
    <script src="{{ asset('assets/js/pages/alldatatables.init.js') }}"></script>
    <script src="{{ asset('assets/js/custom/sms-mail/email-template.js') }}"></script>
    <script src="{{ asset('assets/libs/summernote/summernote-lite.min.js') }}"></script>

    <script>
        $(document).ready(function() {
            $(".summernote").summernote({
                placeholder: "{{ __('Description') }}",
                minHeight: 300,
                focus: true,
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'underline', 'clear']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['table', ['table']],
                    ['insert', ['link']],
                    ['view', ['fullscreen', 'help']]
                ]
            });
        });
    </script>
@endpush
