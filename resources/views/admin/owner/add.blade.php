@extends('admin.layouts.app')

@section('content')
    <div class="main-content">

        <div class="page-content">
            <div class="container-fluid">
                <!-- Page Content Wrapper Start -->
                <div class="page-content-wrapper bg-white p-30 radius-20">
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
                                    <form action="{{ route('admin.owner.register.store') }}" method="POST">
                                        @csrf
                                        <div
                                            class="form-card bg-off-white theme-border radius-4 p-20">
                                            <div
                                                class="bg-white theme-border radius-4 p-20 pb-0 mb-25">
                                                <div class="owners-inner-box-block">
                                                    <div class="add-property-title border-bottom pb-25 mb-25">
                                                        <h4>{{ __('Personal Information') }}</h4>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-6 mb-25">
                                                            <label
                                                                class="label-text-title color-heading font-medium mb-2">{{ __('First Name') }}
                                                                <span class="text-danger">*</span></label>
                                                            <input type="text" name="first_name"
                                                                class="form-control" role="alert"
                                                                placeholder="{{ __('First Name') }}" value="{{ old('first_name') }}">
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
                                                                placeholder="{{ __('Last Name') }}" value="{{ old('last_name') }}">
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
                                                                name="contact_number" class="form-control"
                                                                placeholder="{{ __('Contact Number') }}" value="{{ old('contact_number') }}">
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
                                                                placeholder="{{ __('Email') }}"  value="{{ old('email') }}">
                                                        @error('email')
                                                            <span class="text-danger">{{ $message }}</span>
                                                        @enderror
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-6 mb-25">
                                                            <label
                                                                class="label-text-title color-heading font-medium mb-2">{{ __('Password') }}
                                                                <span class="text-danger">*</span></label>
                                                            <input type="password" name="password"
                                                                class="form-control"
                                                                placeholder="{{ __('Password') }}">
                                                            @error('password')
                                                                <span class="text-danger">{{ $message }}</span>
                                                            @enderror
                                                        </div>
                                                        <div class="col-md-6 mb-25">
                                                            <label
                                                                class="label-text-title color-heading font-medium mb-2">{{ __('Password Confirmation') }}
                                                                <span class="text-danger">*</span></label>
                                                            <input type="password" name="password_confirmation"
                                                                class="form-control"
                                                                placeholder="{{ __('Confirmation Password') }}">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                                @if (getOption('GOOGLE_RECAPTCHA_MAIL_STATUS', 0) == ACTIVE)
                                                <div class="row mb-25">
                                                    <div class="col-md-12">
                                                        <div class="g-recaptcha"
                                                            data-sitekey="{{ getOption('GOOGLE_RECAPTCHA_KEY') }}">
                                                        </div>
                                                        @if ($errors->has('g-recaptcha-response'))
                                                            <span
                                                                class="text-danger">{{ $errors->first('g-recaptcha-response') }}</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                        <!-- Next/Previous Button Start -->
                                        <button type="submit"
                                            class="action-button theme-btn mt-25" title="{{ __('Sign Up') }}">{{ __('Sign Up') }}</button>
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

@push('script')
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
@endpush
