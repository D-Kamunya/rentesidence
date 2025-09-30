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
                                                title="{{ __('Dashboard') }}">{{ __('Dashboard') }}</a></li>
                                        <li class="breadcrumb-item active" aria-current="page">{{ $pageTitle }}</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="property-top-search-bar">
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <div class="property-top-search-bar-left">
                                        <div class="row">
                                            <div class="col-md-6 col-lg-6 col-xl-4 mb-25">
                                                <select class="form-select flex-shrink-0" id="search_property">
                                                    <option value="" selected>{{ __('Select Property') }}</option>
                                                    @foreach ($properties as $property)
                                                        <option value="{{ $property->name }}">{{ $property->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="month-top-search-bar-right">
                                        <div class="row">
                                            <div class="col-md-6 col-lg-6 col-xl-4 mb-25">
                                                <select class="form-select flex-shrink-0" id="search_month">
                                                    <option value="" selected>{{ __('Select Month') }}</option>
                                                    @foreach ($invoiceMonths as $month)
                                                        <option value="{{ $month->formatted }}">{{ $month->formatted }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="property-top-search-bar-right text-end">
                                        <button type="button" class="theme-btn-purple me-2 mb-25" id="reminderGroup"
                                            title="{{ __('Send Group Reminder') }}">
                                            <span class="iconify font-12 me-2"
                                                data-icon="clarity:notification-solid"></span>{{ __('Send Group Reminder') }}
                                        </button>
                                        <button type="button" class="theme-btn mb-25" id="add"
                                            title="{{ __('New Invoice') }}">{{ __('New Invoice') }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="billing-center-area bg-off-white theme-border radius-4 p-25">
                            <div class="tbl-tab-wrap border-bottom pb-25 mb-25">
                                <ul class="nav nav-tabs billing-center-nav-tabs" id="myTab" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link active" id="table1-tab" data-bs-toggle="tab"
                                            data-bs-target="#table1-tab-pane" type="button" role="tab"
                                            aria-controls="table1-tab-pane" aria-selected="true">
                                            {{ __('All') }} (<span id="allCount">{{ $totalInvoice }}</span>)
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="table2-tab" data-bs-toggle="tab"
                                            data-bs-target="#table2-tab-pane" type="button" role="tab"
                                            aria-controls="table2-tab-pane" aria-selected="false">
                                            {{ __('Paid') }} (<span id="paidCount">{{ $totalPaidInvoice }}</span>)
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="table3-tab" data-bs-toggle="tab"
                                            data-bs-target="#table3-tab-pane" type="button" role="tab"
                                            aria-controls="table3-tab-pane" aria-selected="false">
                                            {{ __('Pending') }} (<span id="pendingCount">{{ $totalPendingInvoice }}</span>)
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="tableBank-tab" data-bs-toggle="tab"
                                            data-bs-target="#tableBank-tab-pane" type="button" role="tab"
                                            aria-controls="tableBank-tab-pane" aria-selected="false">
                                            {{ __('Bank Pending') }} (<span id="bankPendingCount">{{ $totalBankPendingInvoice }}</span>)
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="table4-tab" data-bs-toggle="tab"
                                            data-bs-target="#table4-tab-pane" type="button" role="tab"
                                            aria-controls="table4-tab-pane" aria-selected="false">
                                            {{ __('OverDue') }} (<span id="overdueCount">{{ $totalOverDueInvoice }}</span>)
                                        </button>
                                    </li>
                                </ul>
                            </div>

                            <div class="tab-content" id="myTabContent">
                                <div class="tab-pane fade show active" id="table1-tab-pane" role="tabpanel"
                                    aria-labelledby="table1-tab" tabindex="0">
                                    <table id="allInvoiceDataTable" class="table theme-border dt-responsive">
                                        <thead>
                                            <tr>
                                                <th>{{ __('Invoice') }}</th>
                                                <th>{{ __('Property') }}</th>
                                                <th>{{ __('Invoice Month') }}</th>
                                                <th>{{ __('Due Date') }}</th>
                                                <th class="tablet-l tablet-p">{{ __('Amount') }}</th>
                                                <th class="tablet-l tablet-p">{{ __('Payment Status') }}</th>
                                                <th class="tablet-l">{{ __('Gateway') }}</th>
                                                <th>{{ __('Action') }}</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                                <div class="tab-pane fade" id="table2-tab-pane" role="tabpanel" aria-labelledby="table2-tab"
                                    tabindex="0">
                                    <table id="paidInvoiceDataTable" class="table theme-border dt-responsive">
                                        <thead>
                                            <tr>
                                                <th>{{ __('Invoice') }}</th>
                                                <th>{{ __('Property') }}</th>
                                                <th>{{ __('Invoice Month') }}</th>
                                                <th>{{ __('Due Date') }}</th>
                                                <th class="tablet-l tablet-p">{{ __('Amount') }}</th>
                                                <th class="tablet-l tablet-p">{{ __('Payment Status') }}</th>
                                                <th class="tablet-l tablet-p">{{ __('Gateway') }}</th>
                                                <th>{{ __('Action') }}</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                                <div class="tab-pane fade" id="table3-tab-pane" role="tabpanel"
                                    aria-labelledby="table3-tab" tabindex="0">
                                    <table id="pendingInvoiceDataTable" class="table theme-border dt-responsive">
                                        <thead>
                                            <tr>
                                                <th>{{ __('Invoice') }}</th>
                                                <th>{{ __('Property') }}</th>
                                                <th>{{ __('Invoice Month') }}</th>
                                                <th>{{ __('Due Date') }}</th>
                                                <th class="tablet-l tablet-p">{{ __('Amount') }}</th>
                                                <th class="tablet-l tablet-p">{{ __('Payment Status') }}</th>
                                                <th>{{ __('Action') }}</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                                <div class="tab-pane fade" id="tableBank-tab-pane" role="tabpanel"
                                    aria-labelledby="tableBank-tab" tabindex="0">
                                    <table id="bankPendingInvoiceDataTable" class="table theme-border dt-responsive">
                                        <thead>
                                            <tr>
                                                <th>{{ __('Invoice') }}</th>
                                                <th>{{ __('Property') }}</th>
                                                <th>{{ __('Invoice Month') }}</th>
                                                <th>{{ __('Due Date') }}</th>
                                                <th class="tablet-l tablet-p">{{ __('Amount') }}</th>
                                                <th class="tablet-l tablet-p">{{ __('Payment Status') }}</th>
                                                <th class="tablet-l tablet-p">{{ __('Gateway') }}</th>
                                                <th>{{ __('Action') }}</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                                <div class="tab-pane fade" id="table4-tab-pane" role="tabpanel"
                                    aria-labelledby="table4-tab" tabindex="0">
                                    <!-- datatable Start -->
                                    <table id="overdueInvoiceDataTable" class="table theme-border dt-responsive">
                                        <thead>
                                            <tr>
                                                <th>{{ __('Invoice') }}</th>
                                                <th>{{ __('Property') }}</th>
                                                <th>{{ __('Invoice Month') }}</th>
                                                <th>{{ __('Due Date') }}</th>
                                                <th class="tablet-l tablet-p">{{ __('Amount') }}</th>
                                                <th class="tablet-l tablet-p">{{ __('Payment Status') }}</th>
                                                <th>{{ __('Action') }}</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal  --}}
    <div class="modal fade" id="createNewInvoiceModal" tabindex="-1" aria-labelledby="createNewInvoiceModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="createNewInvoiceModalLabel">{{ __('New Invoice') }}</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <span class="iconify" data-icon="akar-icons:cross"></span>
                    </button>
                </div>
                <form class="ajax" action="{{ route('owner.invoice.store') }}" method="post"
                    data-handler="getShowMessage">
                    @csrf
                    <div class="modal-body">
                        <div class="modal-inner-form-box bg-off-white theme-border radius-4 p-20 mb-20 pb-0">
                            <div class="row">
                                <div class="col-md-12 mb-25">
                                    <label
                                        class="label-text-title color-heading font-medium mb-2">{{ __('Invoice Prefix') }}</label>
                                    <input type="text" name="name" value="INV" class="form-control">
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
                                        class="label-text-title color-heading font-medium mb-2">{{ __('Month') }}</label>
                                    <select class="form-select flex-shrink-0" name="month">
                                        <option value="">--{{ __('Select Month') }}--</option>
                                        @foreach (month() as $month)
                                            <option value="{{ $month }}">{{ $month }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6 mb-25">
                                    <label
                                        class="label-text-title color-heading font-medium mb-2">{{ __('Due Date') }}</label>
                                    <div class="custom-datepicker">
                                        <div class="custom-datepicker-inner position-relative">
                                            <input type="text" name="due_date" class="datepicker form-control"
                                                autocomplete="off" placeholder="{{ __('Due Date') }}">
                                            <i class="ri-calendar-2-line"></i>
                                        </div>
                                    </div>
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
                            title="{{ __('Create Invoice') }}">{{ __('Create Invoice') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade edit_modal" id="editInvoiceModal" tabindex="-1" aria-labelledby="editInvoiceModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="editInvoiceModalLabel">{{ __('Edit Invoice') }}</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <span class="iconify" data-icon="akar-icons:cross"></span>
                    </button>
                </div>
                <form class="ajax" action="{{ route('owner.invoice.store') }}" method="post"
                    data-handler="getShowMessage">
                    @csrf
                    <input type="hidden" id="id" name="id">
                    <div class="modal-body">
                        <div class="modal-inner-form-box bg-off-white theme-border radius-4 p-20 mb-20 pb-0">
                            <div class="row">
                                <div class="col-md-12 mb-25">
                                    <label
                                        class="label-text-title color-heading font-medium mb-2">{{ __('Invoice Prefix') }}</label>
                                    <input type="text" name="name" value="INV" class="form-control">
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
                                        class="label-text-title color-heading font-medium mb-2">{{ __('Month') }}</label>
                                    <select class="form-select flex-shrink-0" name="month">
                                        <option value="">--{{ __('Select Month') }}--</option>
                                        @foreach (month() as $month)
                                            <option value="{{ $month }}">{{ $month }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6 mb-25">
                                    <label
                                        class="label-text-title color-heading font-medium mb-2">{{ __('Due Date') }}</label>
                                    <div class="custom-datepicker">
                                        <div class="custom-datepicker-inner position-relative">
                                            <input type="text" name="due_date" class="datepicker form-control"
                                                autocomplete="off" placeholder="{{ __('Due Date') }}">
                                            <i class="ri-calendar-2-line"></i>
                                        </div>
                                    </div>
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

    <div class="modal fade" id="payStatusChangeModal" tabindex="-1" aria-labelledby="payStatusChangeModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="payStatusChangeModalLabel">{{ __('Payment Status Change') }}</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <span class="iconify" data-icon="akar-icons:cross"></span>
                    </button>
                </div>
                <form class="ajax" action="{{ route('owner.invoice.payment.status') }}" method="post"
                    data-handler="getShowMessage">
                    <input type="hidden" name="id">
                    <div class="modal-body">
                        <div class="modal-inner-form-box bg-off-white theme-border radius-4 p-20 mb-20 pb-0">
                            <div class="row">
                                <div class="col-md-12 mb-25">
                                    <label
                                        class="label-text-title color-heading font-medium mb-2">{{ __('Status') }}</label>
                                    <select class="form-select flex-shrink-0" name="status">
                                        <option value="">--{{ __('Select Option') }}--</option>
                                        <option value="0">{{ __('Pending') }}</option>
                                        <option value="1">{{ __('Paid') }}</option>
                                    </select>
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

    <div class="modal fade" id="invoicePreviewModal" tabindex="-1" aria-labelledby="invoicePreviewModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title theme-link pointer-auto" id="invoicePreviewModalLabel" data-bs-dismiss="modal"
                        aria-label="Close">
                        <span class="iconify me-2" data-icon="eva:arrow-back-fill"></span>{{ __('Back') }}
                    </h4>
                    <a href="" id="downloadInvoice" class="download-invoice theme-btn-green"
                        target="_blank">{{ __('Print') }}<span class="iconify ms-2" data-icon="fa:print"></span></a>
                </div>
                <div class="modal-body">
                    <div class="invoice-preview-wrap">
                        <div class="invoice-heading-part">
                            <div class="invoice-heading-left">
                                <img src="{{ getSettingImage('app_logo') }}" 
                                    alt="App Logo" 
                                    class="img-fluid invoice-logo shadow-sm rounded">
                                <h4 class="invoiceNo"></h4>
                                <p class="invoicePayDate"></p>
                                <p class="invoiceMonth"></p>
                            </div>
                            <div class="invoice-heading-right">
                                <div class="invoice-heading-right-status-btn invoiceStatus"></div>
                            </div>
                        </div>
                        <div class="invoice-address-part">
                            <div class="invoice-address-part-left">
                                <h4 class="invoice-generate-title">{{ __('Invoice To') }}</h4>
                                <div class="invoice-address">
                                    <h5 class="tenantName"></h5>
                                    <small class="tenantEmail d-block"></small>
                                    <small class="tenantPhone d-block"></small>
                                    <h6 class="propertyName"></h6>
                                    <small class="unitName"></small>
                                </div>
                            </div>
                            <div class="invoice-address-part-right">
                                <h4 class="invoice-generate-title">{{ __('Pay To') }}</h4>
                                <div class="pay-invoice-address">
                            
                                </div>
                            </div>
                        </div>
                        <div class="invoice-table-part">
                            <h4 class="invoice-generate-title invoice-heading-color">{{ __('Invoice Items') }}</h4>
                            <div class="">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th class="invoice-heading-color">{{ __('Type') }}</th>
                                            <th class="invoice-heading-color">{{ __('Description') }}</th>
                                            <th class="invoice-tbl-last-field invoice-heading-color">{{ __('Amount') }}
                                            </th>
                                            <th class="invoice-tbl-last-field invoice-heading-color">{{ __('Tax') }}
                                            </th>
                                            <th class="invoice-tbl-last-field invoice-heading-color">{{ __('Total') }}
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody id="invoiceItems">
                                    </tbody>
                                </table>
                            </div>
                            <div class="show-total-box">
                                <div class="invoice-tbl-last-field">{{ __('Total') }}: <span
                                        class="invoice-heading-color total"></span></div>
                            </div>
                        </div>
                        <div class="transaction-table-part">
                            <h4 class="invoice-generate-title invoice-heading-color">{{ __('Transaction Details') }}</h4>
                            <div class="">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th class="invoice-heading-color">{{ __('Date') }}</th>
                                            <th class="invoice-heading-color">{{ __('Gateway') }}</th>
                                            <th class="invoice-heading-color">{{ __('Transaction ID') }}</th>
                                            <th class="invoice-tbl-last-field invoice-heading-color">{{ __('Amount') }}
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="orderDate"></td>
                                            <td class="orderPaymentTitle"></td>
                                            <td class="orderPaymentId"></td>
                                            <td class="orderTotal invoice-tbl-last-field"></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- notification modal  --}}
    <div class="modal fade" id="reminderModal" tabindex="-1" aria-labelledby="reminderModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="reminderModalLabel">{{ __('Send Reminder') }}</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <span class="iconify" data-icon="akar-icons:cross"></span>
                    </button>
                </div>
                <form class="ajax" action="{{ route('owner.invoice.send.notification') }}" method="post"
                    data-handler="getShowMessage">
                    <input type="hidden" name="invoice_id" class="" value="">
                    <input type="hidden" name="notification_type" value="2">
                    <div class="modal-body">

                        <div class="modal-inner-form-box bg-off-white theme-border radius-4 p-20">
                            <div class="row">
                                <div class="col-md-12 mb-25">
                                    <label
                                        class="label-text-title color-heading font-medium mb-2">{{ __('Title') }}</label>
                                    <input name="title" class="form-control" placeholder="{{ __('Title') }}">
                                </div>
                                <div class="col-md-12">
                                    <label
                                        class="label-text-title color-heading font-medium mb-2">{{ __('Body') }}</label>
                                    <textarea class="form-control" name="body" placeholder="{{ __('Description') }}"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer justify-content-start">
                        <button type="button" class="theme-btn-back me-3" data-bs-dismiss="modal"
                            title="{{ __('Back') }}">{{ __('Back') }}</button>
                        <button type="submit" class="theme-btn me-3"
                            title="{{ __('Send') }}">{{ __('Send') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="reminderGroupModal" tabindex="-1" aria-labelledby="reminderGroupModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="reminderGroupModalLabel">{{ __('Send Group Reminder') }}</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span
                            class="iconify" data-icon="akar-icons:cross"></span></button>
                </div>
                <form class="ajax" action="{{ route('owner.invoice.send.notification') }}" method="POST"
                    enctype="multipart/form-data" data-handler="getShowMessage">
                    <input type="hidden" name="notification_type" value="1">
                    <div class="modal-body">
                        <div class="modal-inner-form-box">

                            <div class="row">
                                <div class="col-md-6 mb-25">
                                    <label
                                        class="label-text-title color-heading font-medium mb-2">{{ __('Property Name') }}</label>
                                    <select class="form-select flex-shrink-0 property_id" name="property_id" required>
                                        <option value="" selected>--{{ __('Select Property') }}--</option>
                                        @foreach ($properties as $property)
                                            <option value="{{ $property->id }}">{{ $property->name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="mt-20">
                                        <div class="form-group custom-checkbox" title="{{ __('All Property') }}">
                                            <input type="checkbox" id="checkNoticeBoardAllProperty" name="all_property">
                                            <label class="color-heading font-medium"
                                                for="checkNoticeBoardAllProperty">{{ __('All Property') }}</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-25">
                                    <label
                                        class="label-text-title color-heading font-medium mb-2">{{ __('Unit Name') }}</label>
                                    <select class="form-select flex-shrink-0 propertyUnitSelectOption unit_id"
                                        name="unit_id" required>
                                        <option value="" selected>--{{ __('Select Unit') }}--</option>
                                    </select>
                                    <div class="mt-20">
                                        <div class="form-group custom-checkbox" title="{{ __('All Unit') }}">
                                            <input type="checkbox" id="checkNoticeBoardAllUnit" name="all_unit">
                                            <label class="color-heading font-medium"
                                                for="checkNoticeBoardAllUnit">{{ __('All Unit') }}</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12 mb-25">
                                    <label
                                        class="label-text-title color-heading font-medium mb-2">{{ __('Title') }}</label>
                                    <input name="title" class="form-control" placeholder="{{ __('Title') }}">
                                </div>
                                <div class="col-md-12">
                                    <label
                                        class="label-text-title color-heading font-medium mb-2">{{ __('Body') }}</label>
                                    <textarea class="form-control" name="body" placeholder="{{ __('Description') }}"></textarea>
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

    <input type="hidden" class="invoiceTypes" value="{{ $invoiceTypes }}">
    <input type="hidden" id="getPropertyUnitsRoute" value="{{ route('owner.property.getPropertyUnits') }}">
    <input type="hidden" id="invoiceIndex" value="{{ route('owner.invoice.index') }}">
    <input type="hidden" id="invoicePaid" value="{{ route('owner.invoice.paid') }}">
    <input type="hidden" id="invoicePending" value="{{ route('owner.invoice.pending') }}">
    <input type="hidden" id="invoiceBankPending" value="{{ route('owner.invoice.bank.pending') }}">
    <input type="hidden" id="invoiceOverdue" value="{{ route('owner.invoice.overdue') }}">
    <input type="hidden" id="invoicePrint" value="{{ route('owner.invoice.print', '@') }}">
@endsection

@push('style')
    @include('common.layouts.datatable-style')
@endpush

@push('script')
    @include('common.layouts.datatable-script')

    <!-- Datatable init js -->
    <script src="{{ asset('/') }}assets/js/pages/billing-center-datatables.init.js"></script>
    <script src="{{ asset('assets/js/custom/invoice.js') }}"></script>
    @if (request('id') && request('tab') == 'view')
        <script>
            view("{{ route('owner.invoice.details', request('id')) }}");
        </script>
    @endif
@endpush
