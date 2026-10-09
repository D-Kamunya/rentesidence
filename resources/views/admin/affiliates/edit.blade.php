@extends('admin.layouts.app')

@section('content')
    <div class="main-content">

        <div class="page-content">
            <div class="container-fluid">
                <!-- Page Content Wrapper Start -->
                <div class="page-content-wrapper bg-white p-30 radius-20 cs-controls">
                    @include('centresidence._design')

                    <!-- start page title -->
                    <div class="row">
                        <div class="col-12">
                            <div
                                class="page-title-box d-sm-flex align-items-center justify-content-between border-bottom mb-20">
                                <div class="page-title-left">
                                    <h3 class="mb-sm-0">{{ $pageTitle }}</h3>
                                </div>
                                <div class="page-title-right">
                                    <ol class="breadcrumb mb-0">
                                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"
                                                title="{{ __('Dashboard') }}">{{ __('Dashboard') }}</a></li>
                                        <li class="breadcrumb-item"><a href="{{ route('admin.affiliates.index') }}"
                                                title="{{ __('Affiliates') }}">{{ __('Affiliates') }}</a></li>
                                        <li class="breadcrumb-item active" aria-current="page">{{ $pageTitle }}</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- end page title -->
                    <div class="row">
                        <div class="col-12">
                            <div id="msform">
                                <fieldset>
                                    <form action="{{ route('admin.affiliates.update', $affiliate->id) }}" method="POST">
                                        @csrf
                                        <div class="form-card cs-card cs-card--pad">
                                            <div class="pb-0 mb-25">
                                                <div class="owners-inner-box-block">
                                                    <div class="add-property-title border-bottom pb-25 mb-25">
                                                        <h4>{{ __('Contact Details') }}</h4>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-6 mb-25">
                                                            <label
                                                                class="label-text-title color-heading font-medium mb-2">{{ __('First Name') }}
                                                                <span class="text-danger">*</span></label>
                                                            <input type="text" name="first_name"
                                                                class="form-control" role="alert"
                                                                placeholder="{{ __('First Name') }}"
                                                                value="{{ old('first_name', $user->first_name) }}">
                                                            @error('first_name')
                                                                <span class="text-danger">{{ $message }}</span>
                                                            @enderror
                                                        </div>
                                                        <div class="col-md-6 mb-25">
                                                            <label
                                                                class="label-text-title color-heading font-medium mb-2">{{ __('Last Name') }}
                                                                <span class="text-danger">*</span></label>
                                                            <input type="text" name="last_name"
                                                                class="form-control"
                                                                placeholder="{{ __('Last Name') }}"
                                                                value="{{ old('last_name', $user->last_name) }}">
                                                            @error('last_name')
                                                                <span class="text-danger">{{ $message }}</span>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-6 mb-25">
                                                            <label
                                                                class="label-text-title color-heading font-medium mb-2">{{ __('Contact Number') }}
                                                                <span class="text-danger">*</span></label>
                                                            <input type="text" name="contact_number"
                                                                class="form-control"
                                                                placeholder="{{ __('Contact Number') }}"
                                                                value="{{ old('contact_number', $user->contact_number) }}">
                                                            @error('contact_number')
                                                                <span class="text-danger">{{ $message }}</span>
                                                            @enderror
                                                        </div>
                                                        <div class="col-md-6 mb-25">
                                                            <label
                                                                class="label-text-title color-heading font-medium mb-2">{{ __('Email') }}
                                                                <span class="text-danger">*</span></label>
                                                            <input type="email" name="email"
                                                                class="form-control"
                                                                placeholder="{{ __('Email') }}"
                                                                value="{{ old('email', $user->email) }}">
                                                            @error('email')
                                                                <span class="text-danger">{{ $message }}</span>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-12 mb-25">
                                                            <div class="alert alert-info" style="margin:0;font-size:13.5px;">
                                                                {{ __('You can correct the name, email and phone here. The password is not shown or set from here — it stays the affiliate\'s own: they set it on first login and reset it from the sign-in page if forgotten.') }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center flex-wrap mt-25" style="gap:12px;">
                                            <button type="submit"
                                                class="action-button theme-btn"
                                                title="{{ __('Save changes') }}">{{ __('Save changes') }}</button>
                                            <a href="{{ route('admin.affiliates.index') }}"
                                               class="btn"
                                               style="display:inline-flex; align-items:center; justify-content:center; background:#F4F5F7; border:1px solid #D9DEE6; color:#4B5563; border-radius:8px; font-size:14px; font-weight:500; padding:10px 22px; text-decoration:none; line-height:1.4;">{{ __('Cancel') }}</a>
                                        </div>
                                    </form>
                                </fieldset>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
