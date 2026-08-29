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
                            <ol class="cs-crumb"><li><a href="{{ route('owner.dashboard') }}">{{ __('Dashboard') }}</a></li><li>›</li><li>{{ $pageTitle }}</li></ol>
                        </div>
                        <button type="button" class="cs-btn cs-btn--primary" id="add" title="{{ __('New Recurring Setting') }}">
                            <i class="ri-add-line"></i> {{ __('New Recurring Setting') }}
                        </button>
                    </div>

                    <div class="cs-card cs-card--pad cs-controls" style="margin-bottom:16px;">
                        <label class="cs-label">{{ __('Filter by property') }}</label>
                        <div style="max-width:320px;">
                            <select class="form-select" id="search_property">
                                <option value="" selected>{{ __('All properties') }}</option>
                                @foreach ($properties as $property)
                                    <option value="{{ $property->name }}">{{ $property->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="cs-card"><div class="cs-card__body">
                        <table id="allInvoiceDataTable" class="table dt-responsive" style="width:100%;">
                            <thead>
                                <tr>
                                    <th>{{ __('Prefix') }}</th>
                                    <th>{{ __('Property') }}</th>
                                    <th>{{ __('Type') }}</th>
                                    <th>{{ __('Amount') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Action') }}</th>
                                </tr>
                            </thead>
                        </table>
                    </div></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal  --}}
    <div class="modal fade cs-modal" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="addModalLabel">{{ __('New Recurring Setting') }}</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <span class="iconify" data-icon="akar-icons:cross"></span>
                    </button>
                </div>
                <form class="ajax" action="{{ route('owner.invoice.recurring-setting.store') }}" method="post"
                    data-handler="getShowMessage">
                    <div class="modal-body">
                        <div class="modal-inner-form-box bg-off-white theme-border radius-4 p-20 mb-20 pb-0">
                            <div class="row">
                                <div class="col-md-12 mb-25">
                                    <label
                                        class="label-text-title color-heading font-medium mb-2">{{ __('Invoice Prefix') }}</label>
                                    <input type="text" name="invoice_prefix" value="INV" class="form-control"
                                        placeholder="{{ __('Invoice Prefix') }}">
                                </div>
                                <div class="col-md-6 mb-25">
                                    <label
                                        class="label-text-title color-heading font-medium mb-2">{{ __('Property') }}</label>
                                    <select class="form-select flex-shrink-0 property_id" name="property_id">
                                        <option value="">--{{ __('Select Property') }}--</option>
                                        <option value="All">--{{ __('All Properties') }}--</option>
                                        @foreach ($properties as $property)
                                            <option value="{{ $property->id }}">{{ $property->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6 mb-25">
                                    <label
                                        class="label-text-title color-heading font-medium mb-2">{{ __('Unit') }}</label>
                                    <select class="form-select flex-shrink-0 propertyUnitSelectOption"
                                        name="property_unit_id">
                                        <option value="">--{{ __('Select Unit') }}--</option>
                                        <option value="All">--{{ __('All Units') }}--</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-25">
                                    <label
                                        class="label-text-title color-heading font-medium mb-2">{{ __('Recurring Type') }}</label>
                                    <select class="form-select flex-shrink-0 recurring_type" name="recurring_type">
                                        <option value="">--{{ __('Select Type') }}--</option>
                                        <option value="1">{{ __('Monthly') }}</option>
                                        <option value="2">{{ __('Yearly') }}</option>
                                        <option value="3">{{ __('Custom') }}</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-25 d-none recurring_day">
                                    <label
                                        class="label-text-title color-heading font-medium mb-2">{{ __('Cycle Day') }}</label>
                                    <input type="number" name="cycle_day" class="form-control" autocomplete="off"
                                        placeholder="{{ __('Day') }}">
                                </div>
                                <div class="col-md-6 mb-25">
                                    <label
                                        class="label-text-title color-heading font-medium mb-2">{{ __('Due Date After Invoice Creation') }}</label>
                                    <input type="number" name="due_day_after" class="form-control" autocomplete="off"
                                        placeholder="{{ __('5') }}">
                                </div>
                                <div class="col-md-6 mb-25">
                                    <label
                                        class="label-text-title color-heading font-medium mb-2">{{ __('Status') }}</label>
                                    <select class="form-select flex-shrink-0" name="status">
                                        <option value="1">{{ __('Active') }}</option>
                                        <option value="0">{{ __('Inactive') }}</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="multi-field-wrapper">
                            <div class="multi-fields">
                                <div class="multi-field border-bottom pb-25 mb-25">
                                    <div class="modal-inner-form-box bg-off-white theme-border radius-4 p-20 mb-20">
                                        <input type="hidden" name="invoiceItem[id][]" class="" value="">
                                        <div class="row">
                                            <div class="col-md-6 mb-25">
                                                <label
                                                    class="label-text-title color-heading font-medium mb-2">{{ __('Invoice Type') }}</label>
                                                <select class="form-select flex-shrink-0 invoiceItem-invoice_type_id"
                                                    name="invoiceItem[invoice_type_id][]">
                                                    <option value="">--{{ __('Select Type') }}--</option>
                                                    @foreach ($invoiceTypes as $invoiceType)
                                                        <option value="{{ $invoiceType->id }}">{{ $invoiceType->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-6 mb-25">
                                                <label
                                                    class="amount-label label-text-title color-heading font-medium mb-2">{{ __('Amount') }}</label>
                                                <input type="number" name="invoiceItem[amount][]"
                                                    class="form-control invoiceItem-amount"
                                                    placeholder="{{ __('Amount') }}">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <label
                                                    class="label-text-title color-heading font-medium mb-2">{{ __('Description') }}</label>
                                                <textarea class="form-control invoiceItem-description" name="invoiceItem[description][]"
                                                    placeholder="{{ __('Description') }}"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <button type="button" class="remove-field red-color">{{ __('Remove') }}</button>
                                </div>
                            </div>
                            <button type="button" class="add-field theme-btn-purple pull-right">+
                                {{ __('Add Items') }}</button>
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

    <div class="modal fade cs-modal" id="editInvoiceModal" tabindex="-1" aria-labelledby="editInvoiceModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="editInvoiceModalLabel">{{ __('Edit Recurring Setting') }}</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <span class="iconify" data-icon="akar-icons:cross"></span>
                    </button>
                </div>
                <form class="ajax" action="{{ route('owner.invoice.recurring-setting.store') }}" method="post"
                    data-handler="getShowMessage">
                    <input type="hidden" name="id">
                    <div class="modal-body">
                        <div class="modal-inner-form-box bg-off-white theme-border radius-4 p-20 mb-20 pb-0">
                            <div class="row">
                                <div class="col-md-12 mb-25">
                                    <label
                                        class="label-text-title color-heading font-medium mb-2">{{ __('Invoice Prefix') }}</label>
                                    <input type="text" name="invoice_prefix" value="INV" class="form-control"
                                        placeholder="{{ __('Invoice Prefix') }}">
                                </div>
                                <div class="col-md-6 mb-25">
                                    <label
                                        class="label-text-title color-heading font-medium mb-2">{{ __('Property') }}</label>
                                    <select class="form-select flex-shrink-0 property_id" name="property_id">
                                        <option value="">--{{ __('Select Property') }}--</option>
                                        @foreach ($properties as $property)
                                            <option value="{{ $property->id }}">{{ $property->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6 mb-25">
                                    <label
                                        class="label-text-title color-heading font-medium mb-2">{{ __('Unit') }}</label>
                                    <select class="form-select flex-shrink-0 propertyUnitSelectOption"
                                        name="property_unit_id">
                                        <option value="">--{{ __('Select Option') }}--</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-25">
                                    <label
                                        class="label-text-title color-heading font-medium mb-2">{{ __('Recurring Type') }}</label>
                                    <select class="form-select flex-shrink-0 recurring_type" name="recurring_type">
                                        <option value="">--{{ __('Select Type') }}--</option>
                                        <option value="1">{{ __('Monthly') }}</option>
                                        <option value="2">{{ __('Yearly') }}</option>
                                        <option value="3">{{ __('Custom') }}</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-25 d-none recurring_day">
                                    <label
                                        class="label-text-title color-heading font-medium mb-2">{{ __('Cycle Day') }}</label>
                                    <input type="number" name="cycle_day" class="form-control" autocomplete="off"
                                        placeholder="{{ __('Day') }}">
                                </div>
                                <div class="col-md-6 mb-25">
                                    <label
                                        class="label-text-title color-heading font-medium mb-2">{{ __('Due Date After Invoice Creation') }}</label>
                                    <input type="number" name="due_day_after" class="form-control" autocomplete="off"
                                        placeholder="{{ __('5') }}">
                                </div>
                                <div class="col-md-6 mb-25">
                                    <label
                                        class="label-text-title color-heading font-medium mb-2">{{ __('Status') }}</label>
                                    <select class="form-select flex-shrink-0" name="status">
                                        <option value="1">{{ __('Active') }}</option>
                                        <option value="0">{{ __('Inactive') }}</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="multi-field-wrapper">
                            <div class="multi-fields">
                            </div>
                            <button type="button" class="add-field theme-btn-purple pull-right">+
                                {{ __('Add Items') }}</button>
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

    <div class="modal fade" id="invoicePreviewModal" tabindex="-1" aria-labelledby="invoicePreviewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content ow-modal" style="border-radius:12px;overflow:hidden;">

                {{-- Modal header --}}
                <div class="ow-modal__header">
                    <h4 class="modal-title ow-back-link" style="cursor:pointer;display:flex;align-items:center;gap:6px;font-size:14px;font-weight:500;color:#374151;" data-bs-dismiss="modal">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none">
                            <path d="M19 12H5M12 19l-7-7 7-7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        {{ __('Back') }}
                    </h4>
                    <span class="ipv-recurring-tag">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none"><path d="M17 2l4 4-4 4M3 11V9a4 4 0 014-4h14M7 22l-4-4 4-4M21 13v2a4 4 0 01-4 4H3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        {{ __('Recurring setup') }}
                    </span>
                </div>

                <div class="modal-body" style="padding:0;overflow-y:auto;max-height:80vh;">

                    {{-- Header band --}}
                    <div class="ipv-header">
                        <div class="ipv-header__left">
                            <img src="{{ getSettingImage('app_logo') }}" alt="Logo" class="ipv-logo">
                            <div class="ipv-number invoiceNo"></div>
                            <div class="ipv-meta">
                                <span>{{ __('Repeats') }}: <span class="recurring"></span></span>
                            </div>
                        </div>
                        <div class="ipv-header__right">
                            <div class="invoiceStatus"></div>
                        </div>
                    </div>

                    {{-- Addresses --}}
                    <div class="ipv-addresses">
                        <div class="ipv-address-block">
                            <p class="ipv-label">{{ __('Invoice To') }}</p>
                            <p class="ipv-name tenantName"></p>
                            <span class="ipv-line tenantEmail"></span>
                            <span class="ipv-property-chip">
                                <span class="propertyName"></span>
                                <span class="ipv-dot">·</span>
                                <span class="unitName"></span>
                            </span>
                        </div>
                        <div class="ipv-address-block">
                            <p class="ipv-label">{{ __('Pay To') }}</p>
                            <div class="pay-invoice-address">
                                <h5>{{ getOption('app_name') }}</h5>
                                <h6>{{ getOption('app_location') }}</h6>
                                <small>{{ getOption('app_contact_number') }}</small>
                            </div>
                        </div>
                    </div>

                    {{-- Invoice items --}}
                    <div class="ipv-section">
                        <p class="ipv-section-title">{{ __('Invoice Items') }}</p>
                        <div class="table-responsive">
                            <table class="ipv-table">
                                <thead>
                                    <tr>
                                        <th>{{ __('Type') }}</th>
                                        <th>{{ __('Description') }}</th>
                                        <th class="text-end">{{ __('Amount') }}</th>
                                    </tr>
                                </thead>
                                <tbody id="invoiceItems"></tbody>
                            </table>
                        </div>
                        <div class="ipv-total-row">
                            <div class="ipv-total-box">
                                <span class="ipv-total-label">{{ __('Total') }}</span>
                                <span class="ipv-total-amount total"></span>
                            </div>
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div class="ipv-footer">
                        <span class="ipv-footer__note">{{ __('This invoice is generated automatically on each cycle.') }}</span>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <input type="hidden" class="invoiceTypes" value="{{ $invoiceTypes }}">
    <input type="hidden" id="getPropertyUnitsRoute" value="{{ route('owner.property.getPropertyUnits') }}">
    <input type="hidden" id="invoiceRecurring" value="{{ route('owner.invoice.recurring-setting.index') }}">
@endsection

@push('style')
    @include('common.layouts.datatable-style')
    <style>
        /* Invoice preview modal — shared styling with the billing invoice view */
        .ow-modal { border:0.5px solid #e5e7eb; border-radius:12px; overflow:hidden; }
        .ow-modal__header { display:flex; align-items:center; justify-content:space-between; padding:.9rem 1.25rem; border-bottom:0.5px solid #e5e7eb; background:#fafafa; }
        .ow-modal__header .modal-title { margin:0; }
        .ow-back-link { cursor:pointer; font-size:14px; font-weight:500; display:flex; align-items:center; }

        .ipv-recurring-tag { display:inline-flex; align-items:center; gap:6px; font-size:11px; font-weight:600; padding:4px 11px; border-radius:99px; background:#E6F1FB; color:#185FA5; border:0.5px solid #B5D4F4; text-transform:uppercase; letter-spacing:.04em; }

        .ipv-header { display:flex; align-items:flex-start; justify-content:space-between; padding:1.5rem 1.5rem 1.25rem; border-bottom:0.5px solid #e5e7eb; gap:1rem; }
        .ipv-header__left { display:flex; flex-direction:column; gap:8px; }
        .ipv-logo { height:44px; width:auto; max-width:120px; object-fit:contain; border-radius:6px; }
        .ipv-number { font-size:17px; font-weight:600; color:#111827; letter-spacing:-.01em; }
        .ipv-meta { display:flex; flex-direction:column; gap:2px; }
        .ipv-meta span { font-size:12px; color:#6b7280; }
        .ipv-meta .recurring { color:#111827; font-weight:600; }
        .ipv-header__right { flex-shrink:0; }

        .ipv-status-paid { display:inline-flex; align-items:center; gap:5px; font-size:11px; font-weight:600; padding:4px 12px; border-radius:99px; background:#E1F5EE; color:#0F6E56; border:0.5px solid #9FE1CB; text-transform:uppercase; letter-spacing:.04em; }
        .ipv-status-off  { display:inline-flex; align-items:center; gap:5px; font-size:11px; font-weight:600; padding:4px 12px; border-radius:99px; background:#f3f4f6; color:#6b7280; border:0.5px solid #e5e7eb; text-transform:uppercase; letter-spacing:.04em; }

        .ipv-addresses { display:grid; grid-template-columns:1fr 1fr; border-bottom:0.5px solid #e5e7eb; }
        .ipv-address-block { padding:1.25rem 1.5rem; }
        .ipv-address-block:first-child { border-right:0.5px solid #e5e7eb; }
        .ipv-label { font-size:10px; font-weight:600; text-transform:uppercase; letter-spacing:.08em; color:#9ca3af; margin-bottom:8px; }
        .ipv-name { font-size:13px; font-weight:600; color:#111827; margin-bottom:4px; }
        .ipv-line { display:block; font-size:12px; color:#6b7280; margin-bottom:2px; }
        .ipv-property-chip { display:inline-flex; align-items:center; gap:5px; margin-top:6px; font-size:11px; font-weight:500; color:#185FA5; background:#E6F1FB; padding:3px 9px; border-radius:6px; }
        .ipv-dot { color:#B5D4F4; }
        .pay-invoice-address h5 { font-size:13px; font-weight:600; color:#111827; margin-bottom:3px; }
        .pay-invoice-address h6 { font-size:12px; color:#6b7280; font-weight:400; margin-bottom:2px; }
        .pay-invoice-address small { font-size:12px; color:#6b7280; }

        .ipv-section { padding:1.25rem 1.5rem; border-top:0.5px solid #e5e7eb; }
        .ipv-section-title { font-size:10px; font-weight:600; text-transform:uppercase; letter-spacing:.08em; color:#9ca3af; margin-bottom:.85rem; }
        .ipv-table { width:100%; border-collapse:collapse; font-size:12px; }
        .ipv-table thead tr { background:#f9fafb; border-bottom:0.5px solid #e5e7eb; }
        .ipv-table th { padding:.55rem .75rem; font-size:10px; font-weight:600; text-transform:uppercase; letter-spacing:.07em; color:#6b7280; white-space:nowrap; text-align:left; }
        .ipv-table td { padding:.7rem .75rem; border-bottom:0.5px solid #f3f4f6; color:#374151; vertical-align:top; }
        .ipv-table tbody tr:last-child td { border-bottom:none; }
        .ipv-table .text-end, .ipv-table th.text-end { text-align:right; }
        .ipv-table .invoice-tbl-last-field { text-align:right; }  /* JS-produced amount cell */

        .ipv-total-row { display:flex; justify-content:flex-end; padding:.75rem 0 0; }
        .ipv-total-box { display:flex; align-items:baseline; gap:10px; background:#f9fafb; border:0.5px solid #e5e7eb; border-radius:8px; padding:.6rem 1rem; }
        .ipv-total-label { font-size:11px; font-weight:500; color:#6b7280; text-transform:uppercase; letter-spacing:.06em; }
        .ipv-total-amount { font-size:16px; font-weight:700; color:#111827; }

        .ipv-footer { padding:.85rem 1.5rem; border-top:0.5px solid #e5e7eb; background:#fafafa; }
        .ipv-footer__note { font-size:11px; color:#9ca3af; }

        @media (max-width: 576px) {
            .ipv-addresses { grid-template-columns:1fr; }
            .ipv-address-block:first-child { border-right:none; border-bottom:0.5px solid #e5e7eb; }
        }
    </style>
@endpush

@push('script')
    @include('common.layouts.datatable-script')
    <script src="{{ asset('assets/js/custom/invoice-recurring.js') }}"></script>
@endpush
