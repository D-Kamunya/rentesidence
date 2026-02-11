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
                                                                <select name="subscription_remainder_status" class="form-control">
                                                                    <option value="0"
                                                                        {{ getOption('subscription_remainder_status', 0) != SUBSCRIPTION_REMAINDER_STATUS_ACTIVE ? 'selected' : '' }}>
                                                                        {{ __('Disable') }}</option>
                                                                    <option value="1"
                                                                        {{ getOption('subscription_remainder_status', 0) == SUBSCRIPTION_REMAINDER_STATUS_ACTIVE ? 'selected' : '' }}>
                                                                        {{ __('Enable') }}</option>
                                                                </select>
                                                            </div>
                                                            <div class="col-md-4 mb-25">
                                                                <label
                                                                    class="label-text-title color-heading font-medium mb-2">{{ __('Remind day') }}</label>
                                                                <select name="subscription_remainder_everyday_status" id=""
                                                                    class="form-control">
                                                                    <option value="0"
                                                                        {{ getOption('subscription_remainder_everyday_status', 0) != SUBSCRIPTION_REMAINDER_EVERYDAY_STATUS_ACTIVE ? 'selected' : '' }}>
                                                                        {{ __('Custom') }}</option>
                                                                    <option value="1"
                                                                        {{ getOption('subscription_remainder_everyday_status', 0) == SUBSCRIPTION_REMAINDER_EVERYDAY_STATUS_ACTIVE ? 'selected' : '' }}>
                                                                        {{ __('Every Day') }}</option>
                                                                </select>
                                                            </div>
                                                            <div
                                                                class="col-md-4 mb-25 subscription_reminder_day {{ getOption('subscription_remainder_everyday_status', 0) == SUBSCRIPTION_REMAINDER_EVERYDAY_STATUS_ACTIVE ? 'd-none' : '' }}">
                                                                <label
                                                                    class="label-text-title color-heading font-medium mb-2">{{ __('Reminder before expiration') }}</label>
                                                                <input type="text" name="subscription_reminder_days"
                                                                    value="{{ getOption('subscription_reminder_days') }}"
                                                                    class="form-control" placeholder="3">
                                                                <small>{{ __('Remind Days before expiration, separated by comma(,). Set 0 for due date reminder') }}</small>
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
                                                                <select name="SUBSCRIPTION_OVERDUE_REMAINDER_STATUS" class="form-control">
                                                                    <option value="0"
                                                                        {{ getOption('SUBSCRIPTION_OVERDUE_REMAINDER_STATUS', 0) != SUBSCRIPTION_REMAINDER_STATUS_ACTIVE ? 'selected' : '' }}>
                                                                        {{ __('Disable') }}</option>
                                                                    <option value="1"
                                                                        {{ getOption('SUBSCRIPTION_OVERDUE_REMAINDER_STATUS', 0) == SUBSCRIPTION_REMAINDER_STATUS_ACTIVE ? 'selected' : '' }}>
                                                                        {{ __('Enable') }}</option>
                                                                </select>
                                                            </div>
                                                            <div class="col-md-4 mb-25">
                                                                <label
                                                                    class="label-text-title color-heading font-medium mb-2">{{ __('Remind day') }}</label>
                                                                <select name="SUBSCRIPTION_OVERDUE_REMAINDER_EVERYDAY_STATUS" id=""
                                                                    class="form-control">
                                                                    <option value="0"
                                                                        {{ getOption('SUBSCRIPTION_OVERDUE_REMAINDER_EVERYDAY_STATUS', 0) != SUBSCRIPTION_REMAINDER_EVERYDAY_STATUS_ACTIVE ? 'selected' : '' }}>
                                                                        {{ __('Custom') }}</option>
                                                                    <option value="1"
                                                                        {{ getOption('SUBSCRIPTION_OVERDUE_REMAINDER_EVERYDAY_STATUS', 0) == SUBSCRIPTION_REMAINDER_EVERYDAY_STATUS_ACTIVE ? 'selected' : '' }}>
                                                                        {{ __('Every Day') }}</option>
                                                                </select>
                                                            </div>
                                                            <div
                                                                class="col-md-4 mb-25 subscription_overdue_reminder_day {{ getOption('SUBSCRIPTION_OVERDUE_REMAINDER_EVERYDAY_STATUS', 0) == SUBSCRIPTION_REMAINDER_EVERYDAY_STATUS_ACTIVE ? 'd-none' : '' }}">
                                                                <label
                                                                    class="label-text-title color-heading font-medium mb-2">{{ __('Reminder after expiration') }}</label>
                                                                <input type="text" name="SUBSCRIPTION_OVERDUE_REMAINDER_DAYS" 
                                                                    value="{{ getOption('SUBSCRIPTION_OVERDUE_REMAINDER_DAYS') }}"
                                                                    class="form-control" placeholder="3">
                                                                <small>{{ __('Remind Days after expiration, separate by comma(,). Set 0 for due date reminder') }}</small>
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
        $('select[name=subscription_remainder_everyday_status]').on('change', function() {
            var subscription_remainder_everyday_status = $(this).val();
            if (subscription_remainder_everyday_status == 1) {
                $('.subscription_reminder_day').addClass('d-none');
            } else {
                $('.subscription_reminder_day').removeClass('d-none');
            }
        });

        $('select[name=SUBSCRIPTION_OVERDUE_REMAINDER_EVERYDAY_STATUS]').on('change', function() {
            
            var overdue_remainder_everyday_status = $(this).val();
            if (overdue_remainder_everyday_status == 1) {
                $('.subscription_overdue_reminder_day').addClass('d-none');
            } else {
                $('.subscription_overdue_reminder_day').removeClass('d-none');
            }
        });
    </script>
@endpush
