@extends('owner.layouts.app')

@section('content')
<style>
    /* =============================================
       CENTRESIDENCE DESIGN SYSTEM — TENANT CREATE
       ============================================= */
    :root {
        --blue:          #185FA5;
        --blue-hover:    #0F4A84;
        --blue-light:    #E6F1FB;
        --blue-border:   #B5D4F4;
        --blue-faint:    #185ea56e;
        --blue-ghost:    #185ea51c;
        --green:         #1D9E75;
        --green-dark:    #0F6E56;
        --green-light:   #E1F5EE;
        --amber:         #854F0B;
        --amber-light:   #FAEEDA;
        --amber-border:  #F5D9A8;
        --red:           #993C1D;
        --red-light:     #FAECE7;
        --purple:        #534AB7;
        --purple-hover:  #3C3489;
        --gray-900:      #111827;
        --gray-800:      #1f2937;
        --gray-700:      #374151;
        --gray-500:      #6b7280;
        --gray-400:      #9ca3af;
        --gray-200:      #e5e7eb;
        --gray-100:      #f3f4f6;
        --gray-50:       #fafafa;
        --white:         #ffffff;
    }

    .ten-page-wrapper {
        background: var(--white);
        border-radius: 12px;
        padding: 28px 28px 36px;
    }

    /* ---- Page title + breadcrumb ---- */
    .ten-page-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding-bottom: 18px;
        border-bottom: 0.5px solid var(--gray-200);
        margin-bottom: 28px;
        flex-wrap: wrap;
        gap: 10px;
    }
    .ten-page-title {
        font-size: 22px;
        font-weight: 500;
        color: var(--gray-900);
        margin: 0;
    }
    .ten-breadcrumb {
        display: flex;
        align-items: center;
        gap: 6px;
        list-style: none;
        margin: 0;
        padding: 0;
        font-size: 12px;
        color: var(--gray-400);
    }
    .ten-breadcrumb li { display: flex; align-items: center; gap: 6px; }
    .ten-breadcrumb a  { color: var(--blue); font-weight: 500; text-decoration: none; }
    .ten-breadcrumb a:hover { color: var(--blue-hover); }
    .ten-breadcrumb-sep {
        display: inline-block;
        width: 8px; height: 8px;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 8 8' fill='none' stroke='%239ca3af' stroke-width='1.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='2,1 6,4 2,7'/%3E%3C/svg%3E");
        background-size: contain;
        background-repeat: no-repeat;
    }

    /* ---- Stepper ---- */
    .ten-stepper-wrap {
        background: var(--gray-50);
        border: 0.5px solid var(--gray-200);
        border-radius: 12px;
        padding: 28px 40px;
        margin-bottom: 28px;
    }
    .ten-progressbar {
        display: flex;
        align-items: flex-start;
        list-style: none;
        margin: 0;
        padding: 0;
        position: relative;
    }
    /* ── Nuke ALL theme-injected connector lines (dashed/solid) ── */
    #progressbar li::before,
    #progressbar li::after {
        display: none !important;
        content: none !important;
        border: none !important;
        background: none !important;
        width: 0 !important;
        height: 0 !important;
    }
    /* ── Single clean grey track on the <ul> itself ── */
    .ten-progressbar::before {
        content: '' !important;
        display: block !important;
        position: absolute !important;
        top: 21px;
        left: 10%;
        right: 10%;
        height: 2px;
        background: var(--gray-200);
        border-radius: 99px;
        z-index: 0;
        width: auto !important;
    }
    .ten-progressbar li {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 10px;
        position: relative;
        z-index: 1;
    }
    .ten-step-icon {
        width: 44px; height: 44px;
        border-radius: 50%;
        background: var(--white);
        border: 2px solid var(--gray-200);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        color: var(--gray-400);
        transition: all .3s ease;
        /* Halo punches the icon out of the track line cleanly */
        box-shadow: 0 0 0 6px var(--gray-50);
        position: relative;
        z-index: 2;
    }
    .ten-progressbar li.active .ten-step-icon,
    .ten-progressbar li.activated .ten-step-icon {
        background: var(--blue);
        border-color: var(--blue);
        color: var(--white);
        box-shadow: 0 0 0 6px var(--gray-50), 0 4px 16px rgba(24,95,165,.35);
    }
    .ten-step-label {
        font-size: 11px;
        font-weight: 500;
        color: var(--gray-400);
        text-align: center;
        line-height: 1.3;
        max-width: 110px;
        transition: color .3s ease;
    }
    .ten-progressbar li.active .ten-step-label,
    .ten-progressbar li.activated .ten-step-label {
        color: var(--blue);
        font-weight: 600;
    }

    /* ---- Section card (outer) ---- */
    .ten-section {
        background: var(--gray-50);
        border: 0.5px solid var(--gray-200);
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 20px;
    }
    .ten-section-title {
        font-size: 14px;
        font-weight: 600;
        color: var(--gray-900);
        padding-bottom: 14px;
        margin-bottom: 20px;
        border-bottom: 0.5px solid var(--gray-200);
    }

    /* ---- Inner white card ---- */
    .ten-inner-card {
        background: var(--white);
        border: 0.5px solid var(--gray-200);
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 20px;
    }
    .ten-inner-card:last-child { margin-bottom: 0; }
    .ten-inner-title {
        font-size: 13px;
        font-weight: 600;
        color: var(--gray-900);
        padding-bottom: 12px;
        margin-bottom: 18px;
        border-bottom: 0.5px solid var(--gray-200);
    }

    /* ---- Avatar upload ---- */
    .ten-avatar-upload {
        display: flex;
        align-items: center;
        gap: 16px;
        margin-bottom: 24px;
        padding: 16px 20px;
        background: var(--blue-light);
        border: 0.5px solid var(--blue-border);
        border-radius: 10px;
    }
    .ten-avatar-wrap {
        position: relative;
        display: inline-block;
        flex-shrink: 0;
    }
    .ten-avatar-wrap .user-profile-image {
        width: 72px; height: 72px;
        border-radius: 10px;
        object-fit: cover;
        border: 2px solid var(--blue-border);
    }
    .ten-avatar-edit {
        position: absolute;
        bottom: -4px; right: -4px;
        width: 24px; height: 24px;
        border-radius: 50%;
        background: var(--blue);
        border: 2px solid var(--white);
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: background .13s;
    }
    .ten-avatar-edit:hover { background: var(--blue-hover); }
    .ten-avatar-edit i { font-size: 11px; color: var(--white); }
    .ten-avatar-edit input { display: none; }
    .ten-avatar-hint { font-size: 12px; color: var(--gray-500); line-height: 1.5; }
    .ten-avatar-hint strong { display: block; font-size: 13px; color: var(--gray-800); font-weight: 600; margin-bottom: 2px; }

    /* ---- Form labels ---- */
    .ten-label {
        display: block;
        font-size: 10px;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: .07em;
        color: var(--gray-400);
        margin-bottom: 6px;
    }
    .ten-label .req { color: #e74c3c; }

    /* ---- Inputs / selects ---- */
    .ten-input,
    .ten-select {
        width: 100%;
        font-size: 13px;
        line-height: 1.5;
        color: var(--gray-700);
        background: var(--white);
        border: 0.5px solid var(--gray-200);
        border-radius: 7px;
        padding: 8px 12px;
        outline: none;
        transition: border-color .15s, box-shadow .15s;
        appearance: none;
        box-sizing: border-box;
    }
    .ten-input::placeholder { color: var(--gray-400); }
    .ten-input:focus,
    .ten-select:focus {
        border-color: var(--blue);
        box-shadow: 0 0 0 3px rgba(24,95,165,.1);
    }
    .ten-input[readonly],
    .ten-select[readonly] {
        background: var(--gray-50);
        color: var(--gray-500);
        cursor: not-allowed;
    }

    /* Input group (select + number side-by-side) */
    .ten-input-group {
        display: flex;
        gap: 0;
    }
    .ten-input-group .ten-select {
        border-radius: 7px 0 0 7px;
        border-right: none;
        width: auto;
        min-width: 100px;
        flex-shrink: 0;
    }
    .ten-input-group .ten-input {
        border-radius: 0 7px 7px 0;
        flex: 1;
    }

    /* Datepicker */
    .ten-datepicker-wrap { position: relative; }
    .ten-datepicker-wrap .ten-input { padding-right: 36px; }
    .ten-datepicker-wrap i {
        position: absolute;
        right: 10px; top: 50%;
        transform: translateY(-50%);
        color: var(--gray-400);
        font-size: 14px;
        pointer-events: none;
    }

    /* ---- Screening panel ---- */
    .ten-screening-section {
        background: var(--gray-50);
        border: 0.5px solid var(--gray-200);
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 20px;
    }
    .ten-screening-row {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 0;
        border-bottom: 0.5px solid var(--gray-100);
        font-size: 13px;
        color: var(--gray-700);
    }
    .ten-screening-row:last-of-type { border-bottom: none; }
    .ten-screening-row strong { color: var(--gray-800); font-weight: 600; }
    .ten-screening-alert {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        padding: 12px 14px;
        border-radius: 8px;
        font-size: 13px;
        margin-top: 14px;
    }
    .ten-screening-alert.positive {
        background: var(--green-light);
        border: 0.5px solid #A7DFC9;
        color: var(--green-dark);
    }
    .ten-screening-alert.caution {
        background: var(--amber-light);
        border: 0.5px solid var(--amber-border);
        color: var(--amber);
    }
    .ten-screening-alert i { font-size: 15px; flex-shrink: 0; margin-top: 1px; }
    .ten-screening-none {
        font-size: 13px;
        color: var(--gray-500);
        padding: 6px 0;
    }

    /* Rating badge */
    .ten-rating-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 11px;
        font-weight: 500;
        padding: 3px 9px;
        border-radius: 99px;
    }
    .ten-rating-badge.good { background: var(--green-light); color: var(--green-dark); }
    .ten-rating-badge.warn { background: var(--amber-light); color: var(--amber); border: 0.5px solid var(--amber-border); }

    /* ================================================================
       PROPERTY PREVIEW — large hero image left, info right
       ================================================================ */
    .ten-property-preview {
        display: flex;
        align-items: stretch;
        border-radius: 10px;
        overflow: hidden;
        border: 0.5px solid var(--gray-200);
        margin-bottom: 20px;
        min-height: 180px;
        background: var(--white);
    }
    /* Left image panel */
    .ten-property-thumb-wrap {
        width: 220px;
        flex-shrink: 0;
        position: relative;
        overflow: hidden;
        background: var(--gray-100);
    }
    .ten-property-thumb {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
        transition: transform .4s ease;
    }
    .ten-property-thumb-wrap:hover .ten-property-thumb {
        transform: scale(1.05);
    }
    /* Right info panel */
    .ten-property-info {
        flex: 1;
        padding: 20px 22px;
        min-width: 0;
        display: flex;
        flex-direction: column;
        justify-content: center;
        gap: 8px;
        border-left: 0.5px solid var(--gray-200);
    }
    .ten-property-name {
        font-size: 16px;
        font-weight: 600;
        color: var(--blue);
        text-decoration: none;
        line-height: 1.2;
    }
    .ten-property-name:hover { color: var(--blue-hover); }
    .ten-property-addr {
        display: flex;
        align-items: flex-start;
        gap: 5px;
        font-size: 12px;
        color: var(--gray-500);
    }
    .ten-property-addr i { font-size: 13px; margin-top: 1px; flex-shrink: 0; }
    .ten-property-meta {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
        margin-top: 2px;
    }
    .ten-property-tag {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-size: 11px;
        font-weight: 500;
        padding: 4px 11px;
        border-radius: 99px;
        background: var(--blue-light);
        color: #0C447C;
        border: 0.5px solid var(--blue-border);
    }
    .ten-property-tag i { font-size: 11px; }
    /* Availability badge states */
    .ten-property-tag.avail-yes {
        background: var(--green-light);
        color: var(--green-dark);
        border-color: #A7DFC9;
    }
    .ten-property-tag.avail-no {
        background: var(--red-light);
        color: var(--red);
        border-color: #F5C2B0;
    }

    /* ---- Buttons ---- */
    .ten-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 12px;
        font-weight: 500;
        padding: 8px 20px;
        border-radius: 7px;
        border: none;
        cursor: pointer;
        text-decoration: none;
        transition: all .13s;
    }
    .ten-btn-primary {
        background: var(--blue);
        color: var(--white);
    }
    .ten-btn-primary:hover {
        background: var(--blue-hover);
        transform: translateY(-1px);
        color: var(--white);
    }
    .ten-btn-ghost {
        background: var(--gray-100);
        color: var(--gray-700);
        border: 0.5px solid var(--gray-200);
    }
    .ten-btn-ghost:hover {
        background: var(--gray-200);
        transform: translateY(-1px);
    }
    /* Buttons are direct children of <form> — no wrapper div */
    form > .ten-btn,
    form > input.ten-btn {
        margin-top: 24px;
    }
    form > input.ten-btn + input.ten-btn,
    form > input.ten-btn + .ten-btn,
    form > .ten-btn + input.ten-btn {
        margin-left: 10px;
    }

    /* ---- Document list ---- */
    .ten-doc-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 12px;
        border-radius: 8px;
        border: 0.5px solid var(--gray-200);
        background: var(--gray-50);
        margin-top: 12px;
        transition: border-color .2s;
    }
    .ten-doc-item:hover { border-color: var(--blue-border); }
    .ten-doc-icon {
        width: 36px; height: 36px;
        border-radius: 8px;
        background: var(--blue-light);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .ten-doc-icon img { width: 20px; }
    .ten-doc-name {
        flex: 1;
        font-size: 13px;
        font-weight: 500;
        color: var(--gray-800);
    }
    .ten-doc-actions { display: flex; gap: 6px; }
    .ten-doc-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 30px; height: 30px;
        border-radius: 7px;
        border: none;
        background: var(--gray-100);
        color: var(--gray-500);
        cursor: pointer;
        font-size: 15px;
        transition: all .13s;
        text-decoration: none;
    }
    .ten-doc-btn:hover { background: var(--blue); color: var(--white); }
    .ten-doc-btn.danger:hover { background: var(--red-light); color: var(--red); }

    /* ---- Form field group ---- */
    .ten-field { margin-bottom: 20px; }

    /* Responsive */
    @media (max-width: 768px) {
        .ten-page-wrapper { padding: 16px; }
        .ten-stepper-wrap { padding: 20px 16px; }
        .ten-step-label { font-size: 10px; max-width: 80px; }
        .ten-property-preview { flex-direction: column; }
        .ten-property-thumb-wrap { width: 100%; height: 180px; }
    }
    @media (max-width: 540px) {
        .ten-page-header { flex-direction: column; align-items: flex-start; }
        .ten-input-group { flex-direction: column; }
        .ten-input-group .ten-select,
        .ten-input-group .ten-input { border-radius: 7px; border-right: 0.5px solid var(--gray-200); }
    }



    /* ── Readonly combo — mirrors .ten-input exactly so heights always match ── */
    .ten-readonly-combo {
        display: flex;
        align-items: center;
        gap: 8px;
        /* Identical to .ten-input so height is naturally the same */
        background: var(--gray-50);
        border: 0.5px solid var(--gray-200);
        border-radius: 7px;
        padding: 8px 12px;
        font-size: 13px;
        line-height: 1.5;
        color: var(--gray-500);
        box-sizing: border-box;
        cursor: not-allowed;
        width: 100%;
    }
    .ten-readonly-type {
        font-weight: 500;
        color: var(--gray-600, #4b5563);
        white-space: nowrap;
    }
    .ten-readonly-sep {
        color: var(--gray-300, #d1d5db);
        font-size: 14px;
        line-height: 1;
    }
    .ten-readonly-val {
        font-weight: 600;
        color: var(--gray-700);
    }
    /* ── Fix editable input-group on mobile — keep side by side ── */
    .ten-input-group {
        flex-direction: row !important;
    }
    @media (max-width: 540px) {
        .ten-input-group { flex-direction: row !important; }
        .ten-input-group .ten-select { min-width: 80px; }
    }
</style>

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="ten-page-wrapper">

                <!-- Page Header -->
                <div class="ten-page-header">
                    <h3 class="ten-page-title">{{ $pageTitle }}</h3>
                    <ol class="ten-breadcrumb">
                        <li>
                            <a href="{{ route('owner.dashboard') }}">{{ __('Dashboard') }}</a>
                            <span class="ten-breadcrumb-sep"></span>
                        </li>
                        <li>{{ $pageTitle }}</li>
                    </ol>
                </div>

                <!-- Stepper -->
                <div class="ten-stepper-wrap">
                    <ul class="ten-progressbar" id="progressbar">
                        <li class="active" id="accountInformationStep">
                            <div class="ten-step-icon">
                                <i class="ri-account-circle-fill"></i>
                            </div>
                            <span class="ten-step-label">{{ __('Tenant Information') }}</span>
                        </li>
                        <li id="locationStep">
                            <div class="ten-step-icon">
                                <i class="ri-user-search-line"></i>
                            </div>
                            <span class="ten-step-label">{{ __('Tenant Screening & Home Details') }}</span>
                        </li>
                        <li id="unitStep">
                            <div class="ten-step-icon">
                                <i class="ri-file-text-fill"></i>
                            </div>
                            <span class="ten-step-label">{{ __('Documents') }}</span>
                        </li>
                    </ul>
                </div>

                <!-- FORM WRAPPER -->
                <div id="msform">

                    <!-- ====== FIELDSET 1: Tenant Information ====== -->
                    <fieldset>
                        <form class="ajax" action="{{ route('owner.tenant.store') }}" method="POST" data-handler="stepChange">
                            @csrf
                            <input type="hidden" name="step" class="d-none" value="1">

                            <div class="ten-section">
                                <div class="ten-section-title">{{ __('Tenant Information') }}</div>

                                <!-- Avatar Upload -->
                                <div class="ten-avatar-upload">
                                    <div class="ten-avatar-wrap">
                                        <img src="{{ asset('assets/images/users/empty-user.jpg') }}"
                                            class="user-profile-image" alt="Profile Photo">
                                        <label class="ten-avatar-edit" title="{{ __('Upload Photo') }}">
                                            <i class="ri-camera-fill"></i>
                                            <input id="profile-img-file-input" name="image" type="file" class="profile-img-file-input">
                                        </label>
                                    </div>
                                    <div class="ten-avatar-hint">
                                        <strong>Profile Photo</strong>
                                        Click the camera icon to upload a photo.<br>
                                        <span style="font-size:11px;color:var(--gray-400)">JPG, PNG — Max 2MB</span>
                                    </div>
                                </div>

                                <!-- Personal Information -->
                                <div class="ten-inner-card">
                                    <div class="ten-inner-title">{{ __('Personal Information') }}</div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="ten-field">
                                                <label class="ten-label">{{ __('First Name') }} <span class="req">*</span></label>
                                                <input type="text" name="first_name" class="ten-input form-control" role="alert" placeholder="{{ __('First Name') }}">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="ten-field">
                                                <label class="ten-label">{{ __('Last Name') }} <span class="req">*</span></label>
                                                <input type="text" name="last_name" class="ten-input form-control" placeholder="{{ __('Last Name') }}">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="ten-field">
                                                <label class="ten-label">{{ __('Contact Number') }} <span class="req">*</span></label>
                                                <input type="text" name="contact_number" class="ten-input form-control" placeholder="{{ __('Contact Number') }}">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="ten-field">
                                                <label class="ten-label">{{ __('Job') }} <span class="req">*</span></label>
                                                <input type="text" name="job" class="ten-input form-control" placeholder="{{ __('Job') }}">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="ten-field">
                                                <label class="ten-label">{{ __('Age') }} <span class="req">*</span></label>
                                                <input type="number" name="age" class="ten-input form-control" placeholder="{{ __('Age') }}">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="ten-field">
                                                <label class="ten-label">{{ __('Family Members') }} <span class="req">*</span></label>
                                                <input type="number" name="family_member" class="ten-input form-control" placeholder="{{ __('Family Members') }}">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="ten-field">
                                                <label class="ten-label">{{ __('Email') }} <span class="req">*</span></label>
                                                <input type="email" name="email" class="ten-input form-control" placeholder="{{ __('Email') }}">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="ten-field">
                                                <label class="ten-label">{{ __('Password') }} <span class="req">*</span></label>
                                                <input type="password" name="password" class="ten-input form-control" placeholder="{{ __('Password') }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Previous Address -->
                                <div class="ten-inner-card">
                                    <div class="ten-inner-title">{{ __('Previous Address') }}</div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="ten-field">
                                                <label class="ten-label">{{ __('Address') }}</label>
                                                <input type="text" name="previous_address" class="ten-input form-control" placeholder="{{ __('Address') }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row location" id="previous">
                                        <div class="col-md-3">
                                            <div class="ten-field">
                                                <label class="ten-label">{{ __('Country') }}</label>
                                                <input type="text" name="previous_country_id" class="ten-input form-control" placeholder="{{ __('Country') }}">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="ten-field">
                                                <label class="ten-label">{{ __('State') }}</label>
                                                <input type="text" name="previous_state_id" class="ten-input form-control" placeholder="{{ __('State') }}">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="ten-field">
                                                <label class="ten-label">{{ __('City') }}</label>
                                                <input type="text" name="previous_city_id" class="ten-input form-control" placeholder="{{ __('City') }}">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="ten-field">
                                                <label class="ten-label">{{ __('Zip Code') }}</label>
                                                <input type="text" name="previous_zip_code" class="ten-input form-control" placeholder="{{ __('Zip Code') }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Permanent Address -->
                                <div class="ten-inner-card">
                                    <div class="ten-inner-title">{{ __('Permanent Address') }}</div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="ten-field">
                                                <label class="ten-label">{{ __('Address') }} <span class="req">*</span></label>
                                                <input type="text" name="permanent_address" class="ten-input form-control" placeholder="{{ __('Address') }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row location" id="permanent">
                                        <div class="col-md-3">
                                            <div class="ten-field">
                                                <label class="ten-label">{{ __('Country') }} <span class="req">*</span></label>
                                                <input type="text" name="permanent_country_id" class="ten-input form-control" placeholder="{{ __('Country') }}">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="ten-field">
                                                <label class="ten-label">{{ __('State') }} <span class="req">*</span></label>
                                                <input type="text" name="permanent_state_id" class="ten-input form-control" placeholder="{{ __('State') }}">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="ten-field">
                                                <label class="ten-label">{{ __('City') }} <span class="req">*</span></label>
                                                <input type="text" name="permanent_city_id" class="ten-input form-control" placeholder="{{ __('City') }}">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="ten-field">
                                                <label class="ten-label">{{ __('Zip Code') }} <span class="req">*</span></label>
                                                <input type="text" name="permanent_zip_code" class="ten-input form-control" placeholder="{{ __('Zip Code') }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="nextStep1 ten-btn ten-btn-primary">
                                {{ __('Next') }}
                                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg>
                            </button>
                        </form>
                    </fieldset>

                    <!-- ====== FIELDSET 2: Screening & Home Details ====== -->
                    <fieldset>
                        <!-- Tenant Screening (outside form, inside fieldset) -->
                        <div class="ten-screening-section">
                            <div class="ten-section-title">{{ __('Tenant Screening') }}</div>
                            @if(isset($tenant) && ($tenant->rent_payment_rating || $tenant->discipline_rating))
                                <div class="ten-screening-row">
                                    <span><strong>{{ __('Rent Payment Rating') }}:</strong></span>
                                    <span>{{ $tenant->rent_payment_rating }}</span>
                                    @if((int) $tenant->rent_payment_rating[0] >= 4)
                                        <span class="ten-rating-badge good"><i class="ri-check-line"></i> Good</span>
                                    @elseif((int) $tenant->rent_payment_rating[0] < 3)
                                        <span class="ten-rating-badge warn"><i class="ri-alert-line"></i> Low</span>
                                    @endif
                                </div>
                                <div class="ten-screening-row">
                                    <span><strong>{{ __('Discipline Rating') }}:</strong></span>
                                    <span>{{ $tenant->discipline_rating }}</span>
                                    @if((int) $tenant->discipline_rating[0] >= 4)
                                        <span class="ten-rating-badge good"><i class="ri-check-line"></i> Good</span>
                                    @elseif((int) $tenant->discipline_rating[0] < 3)
                                        <span class="ten-rating-badge warn"><i class="ri-alert-line"></i> Low</span>
                                    @endif
                                </div>
                                @if((int) $tenant->rent_payment_rating[0] >= 4 || (int) $tenant->discipline_rating[0] >= 4)
                                    <div class="ten-screening-alert positive">
                                        <i class="ri-shield-check-line"></i>
                                        <span><strong>Positive:</strong> This tenant is rated above average.</span>
                                    </div>
                                @endif
                                @if((int) $tenant->rent_payment_rating[0] < 3 || (int) $tenant->discipline_rating[0] < 3)
                                    <div class="ten-screening-alert caution">
                                        <i class="ri-error-warning-line"></i>
                                        <span><strong>Caution:</strong> This tenant is rated below average. Proceed with caution.</span>
                                    </div>
                                @endif
                            @else
                                <p class="ten-screening-none">There are no previous ratings available for this tenant. You may proceed with the unit allocation below.</p>
                            @endif
                        </div>

                        <form class="ajax" action="{{ route('owner.tenant.store') }}" method="POST" data-handler="stepChange">
                            @csrf
                            <input type="hidden" name="step" class="d-none" value="2">
                            <input type="hidden" name="id" value="">

                            <div class="ten-section">
                                <div class="ten-section-title">{{ __('Home Details') }}</div>

                                <div class="ten-inner-card">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="ten-field">
                                                <label class="ten-label">{{ __('Property') }} <span class="req">*</span></label>
                                                <select class="ten-select form-select flex-shrink-0 property_id" name="property_id">
                                                    <option value="" selected>--{{ __('Select Property') }}--</option>
                                                    @foreach ($properties as $property)
                                                        <option value="{{ $property->id }}">{{ $property->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="ten-field">
                                                <label class="ten-label">{{ __('Unit Name') }} <span class="req">*</span></label>
                                                <select class="ten-select form-select flex-shrink-0 unit_id" name="unit_id" id="unitId">
                                                    <option value="" selected>--{{ __('Select Unit') }}--</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="ten-field">
                                                <label class="ten-label">{{ __('Lease Start Date') }}</label>
                                                <div class="custom-datepicker ten-datepicker-wrap">
                                                    <div class="custom-datepicker-inner position-relative">
                                                        <input type="text" class="datepicker ten-input form-control" autocomplete="off" placeholder="yy-mm-dd" name="lease_start_date">
                                                        <i class="ri-calendar-2-line"></i>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="ten-field">
                                                <label class="ten-label">{{ __('Lease End Date') }}</label>
                                                <div class="custom-datepicker ten-datepicker-wrap">
                                                    <div class="custom-datepicker-inner position-relative">
                                                        <input type="text" class="datepicker ten-input form-control" autocomplete="off" placeholder="yy-mm-dd" name="lease_end_date">
                                                        <i class="ri-calendar-2-line"></i>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Property Preview — hidden until property selected -->
                                <div class="d-none" id="propertyInformation">

                                    <!-- Hero property card -->
                                    <div class="ten-property-preview">
                                        <div class="ten-property-thumb-wrap">
                                            <img src="{{ asset('assets/images/users/empty-user.jpg') }}"
                                                class="ten-property-thumb propertyImg fit-image" alt="Property">
                                        </div>
                                        <div class="ten-property-info">
                                            <h3 class="property-item-title" style="margin:0;">
                                                <a href="#" class="ten-property-name color-heading link-hover-effect">{{ __('N/A') }}</a>
                                            </h3>
                                            <div class="ten-property-addr property-item-address">
                                                <i class="ri-map-pin-2-fill"></i>
                                                <span>{{ __('N/A') }}</span>
                                            </div>
                                            <div class="ten-property-meta">
                                                <span class="ten-property-tag">
                                                    <i class="ri-home-5-fill"></i>
                                                    <span id="unit_name">{{ __('N/A') }}</span>
                                                </span>
                                                <span class="ten-property-tag avail-yes" id="unit-avail-badge">
                                                    <i class="ri-checkbox-circle-fill" id="unit-avail-icon"></i>
                                                    <span id="unit-avail-text">{{ __('Available For Tenant') }}</span>
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Rent Information -->
                                    <div class="ten-inner-card">
                                        <div class="ten-inner-title">{{ __('Rent Information') }}</div>
                                        <div class="row">
                                            <div class="col-md-6 col-lg-4 col-xl-3 col-xxl-2">
                                                <div class="ten-field">
                                                    <label class="ten-label">{{ __('General Rent') }} <span class="req">*</span></label>
                                                    <input type="number" step="any" class="ten-input form-control" id="general_rent" placeholder="{{ __('General Rent') }}" value="" name="general_rent">
                                                </div>
                                            </div>
                                            <div class="col-md-6 col-lg-4 col-xl-3 col-xxl-2">
                                                <div class="ten-field">
                                                    <label class="ten-label">{{ __('Security Deposit') }} <span class="req">*</span></label>
                                                    <div class="ten-input-group input-group custom-input-group">
                                                        <select name="security_deposit_type" class="ten-select form-control">
                                                            <option value="0">{{ __('Fixed') }}</option>
                                                            <option value="1">{{ __('Percentage') }}</option>
                                                        </select>
                                                        <input type="number" step="any" class="ten-input form-control" id="security_deposit" placeholder="{{ __('Security Deposit') }}" value="" name="security_deposit">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6 col-lg-4 col-xl-3 col-xxl-2">
                                                <div class="ten-field">
                                                    <label class="ten-label">{{ __('Late Fee') }} <span class="req">*</span></label>
                                                    <div class="ten-input-group input-group custom-input-group">
                                                        <select name="late_fee_type" class="ten-select form-control">
                                                            <option value="0">{{ __('Fixed') }}</option>
                                                            <option value="1">{{ __('Percentage') }}</option>
                                                        </select>
                                                        <input type="number" step="any" class="ten-input form-control" id="late_fee" placeholder="{{ __('Late Fee') }}" value="" name="late_fee">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6 col-lg-4 col-xl-3 col-xxl-2">
                                                <div class="ten-field">
                                                    <label class="ten-label">{{ __('Incident Receipt') }} <span class="req">*</span></label>
                                                    <input type="number" step="any" class="ten-input form-control" id="incident_receipt" placeholder="{{ __('Incident Receipt') }}" value="" name="incident_receipt">
                                                </div>
                                            </div>
                                            <div class="col-md-6 col-lg-4">
                                                <div class="ten-field">
                                                    <label class="ten-label">{{ __('Payment Due On Date') }} <span class="req">*</span></label>
                                                    <input type="number" id="payment_due_on_date" class="ten-input form-control" autocomplete="off" placeholder="{{ __('Due Date') }}" name="due_date">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div><!-- end #propertyInformation -->
                            </div>

                            <input type="button" name="previous" class="previousStep ten-btn ten-btn-ghost" value="{{ __('Back') }}">
                            <input type="submit" name="next" class="nextStep2 ten-btn ten-btn-primary" value="{{ __('Next') }}">
                        </form>
                    </fieldset>

                    <!-- ====== FIELDSET 3: Documents ====== -->
                    <fieldset>
                        <form class="ajax" action="{{ route('owner.tenant.store') }}" method="POST" data-handler="stepChange" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="step" class="d-none" value="3">
                            <input type="hidden" name="id" value="">

                            <div class="ten-section">
                                <div class="ten-section-title">{{ __('Personal Documents') }}</div>
                                <div class="ten-inner-card">
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <div class="row">
                                                <div class="col-12">
                                                    <input type="file" name="file" class="dropify"
                                                        data-allowed-file-extensions="jpeg jpg png pdf"
                                                        data-max-file-size-preview="3M" />
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <input type="button" name="previous" class="previousStep ten-btn ten-btn-ghost" value="{{ __('Back') }}">
                            <input type="submit" class="ten-btn ten-btn-primary" value="{{ __('Save') }}">
                        </form>
                    </fieldset>

                </div><!-- end #msform -->

            </div>
        </div>
    </div>
</div>

<input type="hidden" id="getStateListRoute" value="{{ route('owner.location.state.list') }}">
<input type="hidden" id="getCityListRoute" value="{{ route('owner.location.city.list') }}">
<input type="hidden" id="propertyShowRoute" value="{{ route('owner.property.show', 0) }}">
<input type="hidden" id="tenantStoreRoute" value="{{ route('owner.tenant.store') }}">
<input type="hidden" id="tenantListRoute" value="{{ route('owner.tenant.index') }}">
<input type="hidden" id="getPropertyWithUnitsByIdRoute" value="{{ route('owner.property.getPropertyWithUnitsById') }}">


@endsection

@push('script')
    <script src="{{ asset('/') }}assets/js/pages/profile-setting.init.js"></script>
    <script src="{{ asset('assets/js/custom/tenant.js') }}"></script>
    <script>
    // Availability badge — runs after jQuery + tenant.js are loaded
    // is_occupied flag is set server-side in getPropertyWithUnitsById
    // tenant.js updates unitsCollection via getPropertyWithUnitsById AJAX on property change,
    // so by the time #unitId changes, unitsCollection already has the fresh data with is_occupied.
    $(document).on("change", "#unitId", function () {
        var id    = $(this).val();
        var badge = $("#unit-avail-badge");
        var icon  = $("#unit-avail-icon");
        var text  = $("#unit-avail-text");
        if (!badge.length) return;
        if (!id) { badge.hide(); return; }
        badge.show();
        if (typeof unitsCollection === "undefined") return;
        var unit = unitsCollection.find(function(u) { return u.id == id; });
        if (!unit) return;
        badge.removeClass("avail-yes avail-no");
        if (unit.is_occupied) {
            badge.addClass("avail-no");
            icon.attr("class", "ri-close-circle-fill");
            text.text("Unit Already Occupied");
        } else {
            badge.addClass("avail-yes");
            icon.attr("class", "ri-checkbox-circle-fill");
            text.text("Available For Tenant");
        }
    });
    </script>
@endpush