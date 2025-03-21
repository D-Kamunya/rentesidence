@extends('admin.layouts.app')

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
                                    <h3 class="mb-sm-0">{{ __('Settings') }}</h3>
                                </div>
                                <div class="page-title-right">
                                    <ol class="breadcrumb mb-0">
                                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"
                                                title="{{ __('Dashboard') }}">{{ __('Dashboard') }}</a></li>
                                        <li class="breadcrumb-item"><a href="#"
                                                title="{{ __('Settings') }}">{{ __('Settings') }}</a></li>
                                        <li class="breadcrumb-item active" aria-current="page">{{ $pageTitle }}
                                        </li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="settings-page-layout-wrap position-relative">
                        <div class="row">
                            @include('admin.setting.sidebar')
                            <div class="col-md-12 col-lg-12 col-xl-8 col-xxl-9">
                                <div class="account-settings-rightside bg-off-white">
                                    <div class="language-settings-page-area theme-border radius-4 p-25">
                                        <div class="account-settings-content-box">
                                            <div class="account-settings-title border-bottom mb-20 pb-20">
                                                <div class="row align-items-center">
                                                    <div class="col-md-6">
                                                        <h4>{{ $pageTitle }}</h4>
                                                    </div>
                                                </div>
                                            </div>
                                            <form action="{{ route('admin.setting.general-setting.update') }}"
                                                method="post" enctype="multipart/form-data">
                                                @csrf
                                                <div class="settings-inner-box bg-white theme-border radius-4 mb-25">
                                                    <div class="settings-inner-box-fields p-20 pb-0">
                                                        <div class="row">
                                                            <div class="col-md-4 mb-25">
                                                                <label
                                                                    class="label-text-title color-heading font-medium mb-2">{{ __('Status') }}</label>
                                                                <select name="remainder_status" class="form-control">
                                                                    <option value="0"
                                                                        {{ getOption('remainder_status', 0) != REMAINDER_STATUS_ACTIVE ? 'selected' : '' }}>
                                                                        {{ __('Disable') }}</option>
                                                                    <option value="1"
                                                                        {{ getOption('remainder_status', 0) == REMAINDER_STATUS_ACTIVE ? 'selected' : '' }}>
                                                                        {{ __('Enable') }}</option>
                                                                </select>
                                                            </div>
                                                            <div class="col-md-4 mb-25">
                                                                <label
                                                                    class="label-text-title color-heading font-medium mb-2">{{ __('Remind day') }}</label>
                                                                <select name="remainder_everyday_status" id=""
                                                                    class="form-control">
                                                                    <option value="0"
                                                                        {{ getOption('remainder_everyday_status', 0) != REMAINDER_EVERYDAY_STATUS_ACTIVE ? 'selected' : '' }}>
                                                                        {{ __('Custom') }}</option>
                                                                    <option value="1"
                                                                        {{ getOption('remainder_everyday_status', 0) == REMAINDER_EVERYDAY_STATUS_ACTIVE ? 'selected' : '' }}>
                                                                        {{ __('Every Day') }}</option>
                                                                </select>
                                                            </div>
                                                            <div
                                                                class="col-md-4 mb-25 reminder_day {{ getOption('remainder_everyday_status', 0) == REMAINDER_EVERYDAY_STATUS_ACTIVE ? 'd-none' : '' }}">
                                                                <label
                                                                    class="label-text-title color-heading font-medium mb-2">{{ __('Reminder before due days') }}</label>
                                                                <input type="text" name="reminder_days"
                                                                    value="{{ getOption('reminder_days') }}"
                                                                    class="form-control" placeholder="3">
                                                                <small>{{ __('Day separate by comma(,)') }}</small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <button class="theme-btn"
                                                    title="{{ __('Update') }}">{{ __('Update') }}</button>
                                            </form>
                                        </div>
                                    </div>

                                    <div class="language-settings-page-area theme-border radius-4 p-25 mt-4">
                                        <div class="account-settings-content-box">
                                            <div class="account-settings-title border-bottom mb-20 pb-20">
                                                <div class="row align-items-center">
                                                    <div class="col-md-6">
                                                        <h4>{{ $pageTitle1 }}</h4>
                                                    </div>
                                                </div>
                                            </div>
                                            <form action="{{ route('admin.setting.general-setting.update') }}"
                                                method="post" enctype="multipart/form-data">
                                                @csrf
                                                <div class="settings-inner-box bg-white theme-border radius-4 mb-25">
                                                    <div class="settings-inner-box-fields p-20 pb-0">
                                                        <div class="row">
                                                            <div class="col-md-4 mb-25">
                                                                <label
                                                                    class="label-text-title color-heading font-medium mb-2">{{ __('Status') }}</label>
                                                                <select name="OVERDUE_REMAINDER_STATUS" class="form-control">
                                                                    <option value="0"
                                                                        {{ getOption('OVERDUE_REMAINDER_STATUS', 0) != REMAINDER_STATUS_ACTIVE ? 'selected' : '' }}>
                                                                        {{ __('Disable') }}</option>
                                                                    <option value="1"
                                                                        {{ getOption('OVERDUE_REMAINDER_STATUS', 0) == REMAINDER_STATUS_ACTIVE ? 'selected' : '' }}>
                                                                        {{ __('Enable') }}</option>
                                                                </select>
                                                            </div>
                                                            <div class="col-md-4 mb-25">
                                                                <label
                                                                    class="label-text-title color-heading font-medium mb-2">{{ __('Remind day') }}</label>
                                                                <select name="OVERDUE_REMAINDER_EVERYDAY_STATUS" id=""
                                                                    class="form-control">
                                                                    <option value="0"
                                                                        {{ getOption('OVERDUE_REMAINDER_EVERYDAY_STATUS', 0) != REMAINDER_EVERYDAY_STATUS_ACTIVE ? 'selected' : '' }}>
                                                                        {{ __('Custom') }}</option>
                                                                    <option value="1"
                                                                        {{ getOption('OVERDUE_REMAINDER_EVERYDAY_STATUS', 0) == REMAINDER_EVERYDAY_STATUS_ACTIVE ? 'selected' : '' }}>
                                                                        {{ __('Every Day') }}</option>
                                                                </select>
                                                            </div>
                                                            <div
                                                                class="col-md-4 mb-25 overdue_reminder_day {{ getOption('OVERDUE_REMAINDER_EVERYDAY_STATUS', 0) == REMAINDER_EVERYDAY_STATUS_ACTIVE ? 'd-none' : '' }}">
                                                                <label
                                                                    class="label-text-title color-heading font-medium mb-2">{{ __('Reminder after due days') }}</label>
                                                                <input type="text" name="OVERDUE_REMAINDER_DAYS" 
                                                                    value="{{ getOption('OVERDUE_REMAINDER_DAYS') }}"
                                                                    class="form-control" placeholder="3">
                                                                <small>{{ __('Day separate by comma(,)') }}</small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <button class="theme-btn"
                                                    title="{{ __('Update') }}">{{ __('Update') }}</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        $('select[name=remainder_everyday_status]').on('change', function() {
            var remainder_everyday_status = $(this).val();
            if (remainder_everyday_status == 1) {
                $('.reminder_day').addClass('d-none');
            } else {
                $('.reminder_day').removeClass('d-none');
            }
        });

        $('select[name=OVERDUE_REMAINDER_EVERYDAY_STATUS]').on('change', function() {
            var overdue_remainder_everyday_status = $(this).val();
            if (overdue_remainder_everyday_status == 1) {
                $('.overdue_reminder_day').addClass('d-none');
            } else {
                $('.overdue_reminder_day').removeClass('d-none');
            }
        });
    </script>
@endpush
