@extends('owner.layouts.app')

@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="page-content-wrapper bg-white p-30 radius-20">

                    {{-- Page Header --}}
                    <div class="ow-page-header mb-4">
                        <div>
                            <h2 class="ow-title">{{ $pageTitle }}</h2>
                            <nav aria-label="breadcrumb">
                                <ol class="ow-breadcrumb">
                                    <li>
                                        <a href="{{ route('owner.dashboard') }}">{{ __('Dashboard') }}</a>
                                    </li>
                                    <li aria-current="page">
                                        <svg width="8" height="8" viewBox="0 0 16 16" fill="none">
                                            <path d="M6 4l4 4-4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                        {{ $pageTitle }}
                                    </li>
                                </ol>
                            </nav>
                        </div>
                    </div>

                    {{-- Toolbar --}}
                    <div class="ow-toolbar mb-4">
                        <div class="ow-toolbar__filters">
                            <div class="ow-filter-group">
                                <label class="ow-filter-label">{{ __('Property') }}</label>
                                <select class="ow-select" id="search_property">
                                    <option value="">{{ __('All properties') }}</option>
                                    @foreach ($properties as $property)
                                        <option value="{{ $property->name }}">{{ $property->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="ow-filter-group">
                                <label class="ow-filter-label">{{ __('Month') }}</label>
                                <select class="ow-select" id="search_month">
                                    <option value="">{{ __('All months') }}</option>
                                    @foreach ($invoiceMonths as $month)
                                        <option value="{{ $month->formatted }}">{{ $month->formatted }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="ow-filter-group">
                                <label class="ow-filter-label">{{ __('Search') }}</label>
                                <div class="ow-search-wrap">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none">
                                        <circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="1.8"/>
                                        <path d="M16.5 16.5l4 4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                    </svg>
                                    <input type="text" id="invoiceSearch" placeholder="{{ __('Invoice no., tenant, property, unit…') }}">
                                </div>
                            </div>
                            <div class="ow-filter-group ow-filter-group--clear">
                                <label class="ow-filter-label">&nbsp;</label>
                                <button type="button" class="ow-btn ow-btn--ghost ow-btn--clear" id="clearFilters" style="display:none">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none">
                                        <path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                    </svg>
                                    {{ __('Clear') }}
                                </button>
                            </div>
                        </div>
                        <div class="ow-toolbar__actions">
                            <button type="button" class="ow-btn ow-btn--purple" id="reminderGroup" title="{{ __('Send Group Reminder') }}">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none">
                                    <path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9M13.73 21a2 2 0 01-3.46 0" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                {{ __('Group reminder') }}
                            </button>
                            <button type="button" class="ow-btn ow-btn--primary" id="add" title="{{ __('New Invoice') }}">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none">
                                    <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                </svg>
                                {{ __('New invoice') }}
                            </button>
                        </div>
                    </div>

                    {{-- Summary Strip --}}
                    <div class="ow-strip mb-4">
                        <div class="ow-strip__item">
                            <span class="ow-strip__dot ow-strip__dot--gray"></span>
                            <div>
                                <p class="ow-strip__label">{{ __('All') }}</p>
                                <p class="ow-strip__val ow-strip__val--gray">{{ $totalInvoice }}</p>
                            </div>
                        </div>
                        <div class="ow-strip__divider"></div>
                        <div class="ow-strip__item">
                            <span class="ow-strip__dot ow-strip__dot--green"></span>
                            <div>
                                <p class="ow-strip__label">{{ __('Paid') }}</p>
                                <p class="ow-strip__val ow-strip__val--green">{{ $totalPaidInvoice }}</p>
                            </div>
                        </div>
                        <div class="ow-strip__divider"></div>
                        <div class="ow-strip__item">
                            <span class="ow-strip__dot ow-strip__dot--amber"></span>
                            <div>
                                <p class="ow-strip__label">{{ __('Pending') }}</p>
                                <p class="ow-strip__val ow-strip__val--amber">{{ $totalPendingInvoice }}</p>
                            </div>
                        </div>
                        <div class="ow-strip__divider"></div>
                        <div class="ow-strip__item">
                            <span class="ow-strip__dot ow-strip__dot--purple"></span>
                            <div>
                                <p class="ow-strip__label">{{ __('Bank pending') }}</p>
                                <p class="ow-strip__val ow-strip__val--purple">{{ $totalBankPendingInvoice }}</p>
                            </div>
                        </div>
                        <div class="ow-strip__divider"></div>
                        <div class="ow-strip__item">
                            <span class="ow-strip__dot ow-strip__dot--coral"></span>
                            <div>
                                <p class="ow-strip__label">{{ __('Overdue') }}</p>
                                <p class="ow-strip__val ow-strip__val--coral">{{ $totalOverDueInvoice }}</p>
                            </div>
                        </div>
                        <div class="ow-strip__divider ow-strip__divider--amount"></div>
                        <div class="ow-strip__item ow-strip__item--amount">
                            <div>
                                <p class="ow-strip__label">{{ __('Total collected') }}</p>
                                <p class="ow-strip__val ow-strip__val--green">{{ currencyPrice($totalPaidAmount) }}</p>
                            </div>
                        </div>
                        <div class="ow-strip__divider ow-strip__divider--amount"></div>
                        <div class="ow-strip__item ow-strip__item--amount">
                            <div>
                                <p class="ow-strip__label">{{ __('Total Unpaid') }}</p>
                                <p class="ow-strip__val ow-strip__val--coral">{{ currencyPrice($totalUnpaidAmount) }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- Main Card --}}
                    <div class="ow-card">

                        {{-- Tab Bar --}}
                        <div class="ow-tab-bar" id="myTab" role="tablist">
                            <button class="ow-tab active" id="table1-tab" data-bs-toggle="tab"
                                data-bs-target="#table1-tab-pane" type="button" role="tab"
                                aria-controls="table1-tab-pane" aria-selected="true">
                                {{ __('All') }}
                                <span class="ow-tab__pill" id="allCount">{{ $totalInvoice }}</span>
                            </button>
                            <button class="ow-tab" id="table2-tab" data-bs-toggle="tab"
                                data-bs-target="#table2-tab-pane" type="button" role="tab"
                                aria-controls="table2-tab-pane" aria-selected="false">
                                {{ __('Paid') }}
                                <span class="ow-tab__pill ow-tab__pill--green" id="paidCount">{{ $totalPaidInvoice }}</span>
                            </button>
                            <button class="ow-tab" id="table3-tab" data-bs-toggle="tab"
                                data-bs-target="#table3-tab-pane" type="button" role="tab"
                                aria-controls="table3-tab-pane" aria-selected="false">
                                {{ __('Pending') }}
                                <span class="ow-tab__pill ow-tab__pill--amber" id="pendingCount">{{ $totalPendingInvoice }}</span>
                            </button>
                            <button class="ow-tab" id="tableBank-tab" data-bs-toggle="tab"
                                data-bs-target="#tableBank-tab-pane" type="button" role="tab"
                                aria-controls="tableBank-tab-pane" aria-selected="false">
                                {{ __('Bank pending') }}
                                <span class="ow-tab__pill ow-tab__pill--purple" id="bankPendingCount">{{ $totalBankPendingInvoice }}</span>
                            </button>
                            <button class="ow-tab" id="table4-tab" data-bs-toggle="tab"
                                data-bs-target="#table4-tab-pane" type="button" role="tab"
                                aria-controls="table4-tab-pane" aria-selected="false">
                                {{ __('Overdue') }}
                                <span class="ow-tab__pill ow-tab__pill--coral" id="overdueCount">{{ $totalOverDueInvoice }}</span>
                            </button>
                        </div>

                        {{-- Tab Panes --}}
                        <div class="tab-content" id="myTabContent">

                            {{-- All --}}
                            <div class="tab-pane fade show active" id="table1-tab-pane" role="tabpanel"
                                aria-labelledby="table1-tab" tabindex="0">
                                <div class="table-responsive">
                                    <table id="allInvoiceDataTable" class="table ow-table dt-responsive">
                                        <thead>
                                            <tr>
                                                <th>{{ __('Invoice') }}</th>
                                                <th>{{ __('Property / Unit') }}</th>
                                                <th>{{ __('Month') }}</th>
                                                <th>{{ __('Due date') }}</th>
                                                <th class="tablet-l tablet-p">{{ __('Amount') }}</th>
                                                <th class="tablet-l tablet-p">{{ __('Status') }}</th>
                                                <th class="tablet-l">{{ __('Gateway') }}</th>
                                                <th style="text-align:right">{{ __('Actions') }}</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>

                            {{-- Paid --}}
                            <div class="tab-pane fade" id="table2-tab-pane" role="tabpanel"
                                aria-labelledby="table2-tab" tabindex="0">
                                <div class="table-responsive">
                                    <table id="paidInvoiceDataTable" class="table ow-table dt-responsive">
                                        <thead>
                                            <tr>
                                                <th>{{ __('Invoice') }}</th>
                                                <th>{{ __('Property / Unit') }}</th>
                                                <th>{{ __('Month') }}</th>
                                                <th>{{ __('Due date') }}</th>
                                                <th class="tablet-l tablet-p">{{ __('Amount') }}</th>
                                                <th class="tablet-l tablet-p">{{ __('Status') }}</th>
                                                <th class="tablet-l tablet-p">{{ __('Gateway') }}</th>
                                                <th style="text-align:right">{{ __('Actions') }}</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>

                            {{-- Pending --}}
                            <div class="tab-pane fade" id="table3-tab-pane" role="tabpanel"
                                aria-labelledby="table3-tab" tabindex="0">
                                <div class="table-responsive">
                                    <table id="pendingInvoiceDataTable" class="table ow-table dt-responsive">
                                        <thead>
                                            <tr>
                                                <th>{{ __('Invoice') }}</th>
                                                <th>{{ __('Property / Unit') }}</th>
                                                <th>{{ __('Month') }}</th>
                                                <th>{{ __('Due date') }}</th>
                                                <th class="tablet-l tablet-p">{{ __('Amount') }}</th>
                                                <th class="tablet-l tablet-p">{{ __('Status') }}</th>
                                                <th style="text-align:right">{{ __('Actions') }}</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>

                            {{-- Bank Pending --}}
                            <div class="tab-pane fade" id="tableBank-tab-pane" role="tabpanel"
                                aria-labelledby="tableBank-tab" tabindex="0">
                                <div class="table-responsive">
                                    <table id="bankPendingInvoiceDataTable" class="table ow-table dt-responsive">
                                        <thead>
                                            <tr>
                                                <th>{{ __('Invoice') }}</th>
                                                <th>{{ __('Property / Unit') }}</th>
                                                <th>{{ __('Month') }}</th>
                                                <th>{{ __('Due date') }}</th>
                                                <th class="tablet-l tablet-p">{{ __('Amount') }}</th>
                                                <th class="tablet-l tablet-p">{{ __('Status') }}</th>
                                                <th class="tablet-l tablet-p">{{ __('Gateway') }}</th>
                                                <th style="text-align:right">{{ __('Actions') }}</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>

                            {{-- Overdue --}}
                            <div class="tab-pane fade" id="table4-tab-pane" role="tabpanel"
                                aria-labelledby="table4-tab" tabindex="0">
                                <div class="table-responsive">
                                    <table id="overdueInvoiceDataTable" class="table ow-table dt-responsive">
                                        <thead>
                                            <tr>
                                                <th>{{ __('Invoice') }}</th>
                                                <th>{{ __('Property / Unit') }}</th>
                                                <th>{{ __('Month') }}</th>
                                                <th>{{ __('Due date') }}</th>
                                                <th class="tablet-l tablet-p">{{ __('Amount') }}</th>
                                                <th class="tablet-l tablet-p">{{ __('Status') }}</th>
                                                <th style="text-align:right">{{ __('Actions') }}</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>

                        </div>
                    </div>
                    {{-- End Main Card --}}

                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════
         MODALS — unchanged functionality, cleaned up markup only
    ═══════════════════════════════════════════════════════════ --}}

    {{-- New Invoice --}}
    <div class="modal fade" id="createNewInvoiceModal" tabindex="-1" aria-labelledby="createNewInvoiceModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content ow-modal">
                <div class="modal-header ow-modal__header">
                    <h4 class="modal-title">{{ __('New Invoice') }}</h4>
                    <button type="button" class="ow-modal__close" data-bs-dismiss="modal" aria-label="Close">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                    </button>
                </div>
                <form class="ajax" action="{{ route('owner.invoice.store') }}" method="post" data-handler="getShowMessage">
                    @csrf
                    <div class="modal-body ow-modal__body">
                        <div class="ow-form-section mb-4">
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label class="ow-label">{{ __('Invoice prefix') }}</label>
                                    <input type="text" name="name" value="INV" class="form-control ow-input">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="ow-label">{{ __('Property') }}</label>
                                    <select class="form-select ow-input property_id" name="property_id">
                                        <option value="">-- {{ __('Select property') }} --</option>
                                        <option value="All">-- {{ __('All properties') }} --</option>
                                        @foreach ($properties as $property)
                                            <option value="{{ $property->id }}">{{ $property->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="ow-label">{{ __('Unit') }}</label>
                                    <select class="form-select ow-input propertyUnitSelectOption" name="property_unit_id">
                                        <option value="">-- {{ __('Select unit') }} --</option>
                                        <option value="All">-- {{ __('All units') }} --</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="ow-label">{{ __('Month') }}</label>
                                    <select class="form-select ow-input" name="month">
                                        <option value="">-- {{ __('Select month') }} --</option>
                                        @foreach (month() as $month)
                                            <option value="{{ $month }}">{{ $month }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="ow-label">{{ __('Due date') }}</label>
                                    <div class="custom-datepicker">
                                        <div class="custom-datepicker-inner position-relative">
                                            <input type="text" name="due_date" class="datepicker form-control ow-input" autocomplete="off" placeholder="{{ __('Select date') }}">
                                            <i class="ri-calendar-2-line"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="multi-field-wrapper">
                            <div class="multi-fields">
                                <div class="multi-field mb-3">
                                    <div class="ow-form-section mb-2">
                                        <input type="hidden" name="invoiceItem[id][]" value="">
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                    <label class="ow-label mb-0">{{ __('Invoice type') }}</label>
                                                    <button type="button" class="ow-link-btn" data-bs-toggle="modal" data-bs-target="#addInvoiceTypeModal">
                                                        + {{ __('Add type') }}
                                                    </button>
                                                </div>
                                                <select class="form-select ow-input invoiceItem-invoice_type_id" name="invoiceItem[invoice_type_id][]">
                                                    <option value="">-- {{ __('Select type') }} --</option>
                                                    @foreach ($invoiceTypes as $invoiceType)
                                                        <option value="{{ $invoiceType->id }}">{{ $invoiceType->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="ow-label amount-label">{{ __('Amount') }}</label>
                                                <input type="number" name="invoiceItem[amount][]" class="form-control ow-input invoiceItem-amount" placeholder="{{ __('0.00') }}">
                                            </div>
                                            <div class="col-md-12">
                                                <label class="ow-label">{{ __('Description') }}</label>
                                                <textarea class="form-control ow-input invoiceItem-description" name="invoiceItem[description][]" placeholder="{{ __('Optional notes…') }}" rows="2"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <button type="button" class="remove-field ow-remove-btn">{{ __('Remove item') }}</button>
                                </div>
                            </div>
                            <button type="button" class="add-field ow-btn ow-btn--purple mt-2">
                                + {{ __('Add item') }}
                            </button>
                        </div>
                    </div>
                    <div class="modal-footer ow-modal__footer">
                        <button type="button" class="ow-btn ow-btn--ghost" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="ow-btn ow-btn--primary">{{ __('Create invoice') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Edit Invoice --}}
    <div class="modal fade edit_modal" id="editInvoiceModal" tabindex="-1" aria-labelledby="editInvoiceModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content ow-modal">
                <div class="modal-header ow-modal__header">
                    <h4 class="modal-title">{{ __('Edit Invoice') }}</h4>
                    <button type="button" class="ow-modal__close" data-bs-dismiss="modal" aria-label="Close">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                    </button>
                </div>
                <form class="ajax" action="{{ route('owner.invoice.store') }}" method="post" data-handler="getShowMessage">
                    @csrf
                    <input type="hidden" id="id" name="id">
                    <div class="modal-body ow-modal__body">
                        <div class="ow-form-section mb-4">
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label class="ow-label">{{ __('Invoice prefix') }}</label>
                                    <input type="text" name="name" value="INV" class="form-control ow-input">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="ow-label">{{ __('Property') }}</label>
                                    <select class="form-select ow-input property_id" name="property_id">
                                        <option value="">-- {{ __('Select property') }} --</option>
                                        @foreach ($properties as $property)
                                            <option value="{{ $property->id }}">{{ $property->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="ow-label">{{ __('Unit') }}</label>
                                    <select class="form-select ow-input propertyUnitSelectOption" name="property_unit_id">
                                        <option value="">-- {{ __('Select unit') }} --</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="ow-label">{{ __('Month') }}</label>
                                    <select class="form-select ow-input" name="month">
                                        <option value="">-- {{ __('Select month') }} --</option>
                                        @foreach (month() as $month)
                                            <option value="{{ $month }}">{{ $month }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="ow-label">{{ __('Due date') }}</label>
                                    <div class="custom-datepicker">
                                        <div class="custom-datepicker-inner position-relative">
                                            <input type="text" name="due_date" class="datepicker form-control ow-input" autocomplete="off" placeholder="{{ __('Select date') }}">
                                            <i class="ri-calendar-2-line"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="multi-field-wrapper">
                            <div class="multi-fields"></div>
                            <button type="button" class="add-field ow-btn ow-btn--purple mt-2">
                                + {{ __('Add item') }}
                            </button>
                        </div>
                    </div>
                    <div class="modal-footer ow-modal__footer">
                        <button type="button" class="ow-btn ow-btn--ghost" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="ow-btn ow-btn--primary">{{ __('Update invoice') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Payment Status --}}
    <div class="modal fade" id="payStatusChangeModal" tabindex="-1" aria-labelledby="payStatusChangeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content ow-modal">
                <div class="modal-header ow-modal__header">
                    <h4 class="modal-title">{{ __('Payment status') }}</h4>
                    <button type="button" class="ow-modal__close" data-bs-dismiss="modal" aria-label="Close">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                    </button>
                </div>
                <form class="ajax" action="{{ route('owner.invoice.payment.status') }}" method="post" data-handler="getShowMessage">
                    <input type="hidden" name="id">
                    <div class="modal-body ow-modal__body">
                        <div class="ow-form-section">
                            <div class="row">
                                <div class="col-md-12">
                                    <label class="ow-label">{{ __('Status') }}</label>
                                    <select class="form-select ow-input" name="status">
                                        <option value="">-- {{ __('Select status') }} --</option>
                                        <option value="0">{{ __('Pending') }}</option>
                                        <option value="1">{{ __('Paid') }}</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer ow-modal__footer">
                        <button type="button" class="ow-btn ow-btn--ghost" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="ow-btn ow-btn--primary">{{ __('Update') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Invoice Preview --}}
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
                    <a href="" id="downloadInvoice" class="ow-btn ow-btn--green" target="_blank">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none">
                            <path d="M6 9V3h12v6M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                            <rect x="6" y="14" width="12" height="7" rx="1" stroke="currentColor" stroke-width="1.8"/>
                        </svg>
                        {{ __('Print') }}
                    </a>
                </div>

                <div class="modal-body" style="padding:0;overflow-y:auto;max-height:80vh;">

                    {{-- Invoice header band --}}
                    <div class="ipv-header">
                        <div class="ipv-header__left">
                            <img src="{{ getSettingImage('app_logo') }}" alt="Logo" class="ipv-logo">
                            <div class="ipv-number invoiceNo"></div>
                            <div class="ipv-meta">
                                <span class="invoicePayDate"></span>
                                <span class="invoiceMonth"></span>
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
                            <span class="ipv-line tenantPhone"></span>
                            <span class="ipv-property-chip">
                                <span class="propertyName"></span>
                                <span class="ipv-dot">·</span>
                                <span class="unitName"></span>
                            </span>
                        </div>
                        <div class="ipv-address-block">
                            <p class="ipv-label">{{ __('Pay To') }}</p>
                            <div class="pay-invoice-address"></div>
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
                                        <th class="text-end">{{ __('Tax') }}</th>
                                        <th class="text-end">{{ __('Total') }}</th>
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

                    {{-- Transaction details --}}
                    <div class="ipv-section">
                        <p class="ipv-section-title">{{ __('Transaction Details') }}</p>
                        <div class="table-responsive">
                            <table class="ipv-table">
                                <thead>
                                    <tr>
                                        <th>{{ __('Date') }}</th>
                                        <th>{{ __('Gateway') }}</th>
                                        <th class="ipv-col-txid">{{ __('Transaction ID') }}</th>
                                        <th class="text-end">{{ __('Amount') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="orderDate"></td>
                                        <td class="orderPaymentTitle"></td>
                                        <td class="orderPaymentId ipv-col-txid"></td>
                                        <td class="orderTotal text-end" style="font-weight:600;color:#0F6E56;"></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div class="ipv-footer">
                        <span class="ipv-footer__note">{{ __('Thank you for your business.') }}</span>
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- Send Reminder --}}
    <div class="modal fade" id="reminderModal" tabindex="-1" aria-labelledby="reminderModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content ow-modal">
                <div class="modal-header ow-modal__header">
                    <h4 class="modal-title">{{ __('Send reminder') }}</h4>
                    <button type="button" class="ow-modal__close" data-bs-dismiss="modal" aria-label="Close">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                    </button>
                </div>
                <form class="ajax" action="{{ route('owner.invoice.send.notification') }}" method="post" data-handler="getShowMessage">
                    <input type="hidden" name="invoice_id" value="">
                    <input type="hidden" name="notification_type" value="2">
                    <div class="modal-body ow-modal__body">
                        <div class="ow-form-section">
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label class="ow-label">{{ __('Title') }}</label>
                                    <input name="title" class="form-control ow-input" placeholder="{{ __('Reminder title…') }}">
                                </div>
                                <div class="col-md-12">
                                    <label class="ow-label">{{ __('Message') }}</label>
                                    <textarea class="form-control ow-input" name="body" placeholder="{{ __('Write your reminder message…') }}" rows="4"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer ow-modal__footer">
                        <button type="button" class="ow-btn ow-btn--ghost" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="ow-btn ow-btn--primary">{{ __('Send reminder') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Group Reminder --}}
    <div class="modal fade" id="reminderGroupModal" tabindex="-1" aria-labelledby="reminderGroupModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content ow-modal">
                <div class="modal-header ow-modal__header">
                    <h4 class="modal-title">{{ __('Send group reminder') }}</h4>
                    <button type="button" class="ow-modal__close" data-bs-dismiss="modal" aria-label="Close">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                    </button>
                </div>
                <form class="ajax" action="{{ route('owner.invoice.send.notification') }}" method="POST" enctype="multipart/form-data" data-handler="getShowMessage">
                    <input type="hidden" name="notification_type" value="1">
                    <div class="modal-body ow-modal__body">
                        <div class="ow-form-section">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="ow-label">{{ __('Property') }}</label>
                                    <select class="form-select ow-input property_id" name="property_id" required>
                                        <option value="">-- {{ __('Select property') }} --</option>
                                        @foreach ($properties as $property)
                                            <option value="{{ $property->id }}">{{ $property->name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="ow-checkbox mt-2">
                                        <input type="checkbox" id="checkNoticeBoardAllProperty" name="all_property">
                                        <label for="checkNoticeBoardAllProperty">{{ __('All properties') }}</label>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="ow-label">{{ __('Unit') }}</label>
                                    <select class="form-select ow-input propertyUnitSelectOption unit_id" name="unit_id" required>
                                        <option value="">-- {{ __('Select unit') }} --</option>
                                    </select>
                                    <div class="ow-checkbox mt-2">
                                        <input type="checkbox" id="checkNoticeBoardAllUnit" name="all_unit">
                                        <label for="checkNoticeBoardAllUnit">{{ __('All units') }}</label>
                                    </div>
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label class="ow-label">{{ __('Title') }}</label>
                                    <input name="title" class="form-control ow-input" placeholder="{{ __('Reminder title…') }}">
                                </div>
                                <div class="col-md-12">
                                    <label class="ow-label">{{ __('Message') }}</label>
                                    <textarea class="form-control ow-input" name="body" placeholder="{{ __('Write your reminder message…') }}" rows="4"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer ow-modal__footer">
                        <button type="button" class="ow-btn ow-btn--ghost" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="ow-btn ow-btn--primary">{{ __('Send reminder') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Add Invoice Type --}}
    <div class="modal fade" id="addInvoiceTypeModal" tabindex="-1" aria-labelledby="addInvoiceTypeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content ow-modal">
                <div class="modal-header ow-modal__header">
                    <h4 class="modal-title">{{ __('Add invoice type') }}</h4>
                    <button type="button" class="ow-modal__close" data-bs-dismiss="modal" aria-label="Close">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                    </button>
                </div>
                <form class="ajax" action="{{ route('owner.setting.invoice-type.store') }}" method="post" data-handler="invoiceTypeStoreDataRes">
                    <div class="modal-body ow-modal__body">
                        <div class="ow-form-section">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="ow-label">{{ __('Name') }}</label>
                                    <input type="text" name="name" class="form-control ow-input" placeholder="{{ __('e.g. Rent, Water…') }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="ow-label">
                                        {{ __('Tax') }}
                                        <span class="ow-label-hint">({{ taxSetting(auth()->id())->type == TAX_TYPE_PERCENTAGE ? '%' : 'Fixed' }})</span>
                                    </label>
                                    <input type="text" name="tax" class="form-control ow-input" placeholder="0">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer ow-modal__footer">
                        <button type="button" class="ow-btn ow-btn--ghost" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="ow-btn ow-btn--primary">{{ __('Add type') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Hidden route inputs (unchanged) --}}
    <input type="hidden" class="invoiceTypes" value="{{ $invoiceTypes }}">
    <input type="hidden" id="getPropertyUnitsRoute" value="{{ route('owner.property.getPropertyUnits') }}">
    <input type="hidden" id="invoiceIndex"       value="{{ route('owner.invoice.index') }}">
    <input type="hidden" id="invoicePaid"         value="{{ route('owner.invoice.paid') }}">
    <input type="hidden" id="invoicePending"      value="{{ route('owner.invoice.pending') }}">
    <input type="hidden" id="invoiceBankPending"  value="{{ route('owner.invoice.bank.pending') }}">
    <input type="hidden" id="invoiceOverdue"      value="{{ route('owner.invoice.overdue') }}">
    <input type="hidden" id="invoicePrint"        value="{{ route('owner.invoice.print', '@') }}">

<style>
    /* ── Page header ─────────────────────────────────────────── */
    .ow-page-header { display:flex; align-items:flex-start; justify-content:space-between; flex-wrap:wrap; gap:1rem; }
    .ow-title { font-size:22px; font-weight:500; color:#111827; margin:0 0 6px; }
    .ow-breadcrumb { list-style:none; display:flex; align-items:center; gap:6px; margin:0; padding:0; font-size:12px; color:#9ca3af; }
    .ow-breadcrumb a { color:#185FA5; text-decoration:none; font-weight:500; }
    .ow-breadcrumb a:hover { color:#0F4A84; }
    .ow-breadcrumb li { display:flex; align-items:center; gap:6px; }

    /* ── Toolbar ─────────────────────────────────────────────── */
    .ow-toolbar { display:flex; align-items:flex-end; justify-content:space-between; gap:12px; flex-wrap:wrap; }
    .ow-toolbar__filters { display:flex; align-items:flex-end; gap:10px; flex-wrap:wrap; }
    .ow-toolbar__actions { display:flex; align-items:center; gap:8px; flex-shrink:0; }

    .ow-filter-group { display:flex; flex-direction:column; gap:5px; }
    .ow-filter-label { font-size:10px; font-weight:500; text-transform:uppercase; letter-spacing:.07em; color:#9ca3af; }

    .ow-select {
        border:0.5px solid #e5e7eb; border-radius:7px; padding:6px 10px;
        font-size:12px; color:#374151; background:#fff; outline:none;
        min-width:160px; transition:border-color .15s, box-shadow .15s;
    }
    .ow-select:focus { border-color:#185FA5; box-shadow:0 0 0 3px rgba(24,95,165,.1); }

    /* ── Buttons ─────────────────────────────────────────────── */
    .ow-btn {
        display:inline-flex; align-items:center; gap:6px;
        font-size:12px; font-weight:500; padding:7px 15px;
        border-radius:7px; cursor:pointer; border:none;
        white-space:nowrap; text-decoration:none;
        transition:background .13s, transform .12s, box-shadow .12s;
    }
    .ow-btn--primary { background:#185FA5; color:#fff; box-shadow:0 2px 8px rgba(24,95,165,.2); }
    .ow-btn--primary:hover { background:#0F4A84; color:#fff; transform:translateY(-1px); box-shadow:0 4px 12px rgba(24,95,165,.3); text-decoration:none; }
    .ow-btn--purple { background:#534AB7; color:#fff; box-shadow:0 2px 8px rgba(83,74,183,.2); }
    .ow-btn--purple:hover { background:#3C3489; color:#fff; transform:translateY(-1px); text-decoration:none; }
    .ow-btn--green { background:#0F6E56; color:#fff; }
    .ow-btn--green:hover { background:#085041; color:#fff; text-decoration:none; }
    .ow-btn--ghost { background:#f3f4f6; color:#374151; border:0.5px solid #e5e7eb; }
    .ow-btn--ghost:hover { background:#e5e7eb; color:#111827; text-decoration:none; }

    /* ── Summary strip ───────────────────────────────────────── */
    .ow-strip { display:flex; align-items:center; background:#fff; border:0.5px solid #e5e7eb; border-radius:12px; overflow:hidden; flex-wrap:wrap; }
    .ow-strip__item { display:flex; align-items:center; gap:10px; padding:.85rem 1.25rem; flex:1; min-width:100px; }
    .ow-strip__divider { width:0.5px; align-self:stretch; background:#e5e7eb; flex-shrink:0; }
    .ow-strip__dot { width:8px; height:8px; border-radius:50%; flex-shrink:0; }
    .ow-strip__dot--gray   { background:#888780; }
    .ow-strip__dot--green  { background:#1D9E75; }
    .ow-strip__dot--amber  { background:#854F0B; }
    .ow-strip__dot--purple { background:#534AB7; }
    .ow-strip__dot--coral  { background:#993C1D; }
    .ow-strip__label { font-size:10px; font-weight:500; text-transform:uppercase; letter-spacing:.07em; color:#9ca3af; margin:0 0 3px; }
    .ow-strip__val { font-size:15px; font-weight:600; margin:0; line-height:1; }
    .ow-strip__val--gray   { color:#5F5E5A; }
    .ow-strip__val--green  { color:#0F6E56; }
    .ow-strip__val--amber  { color:#854F0B; }
    .ow-strip__val--purple { color:#534AB7; }
    .ow-strip__val--coral  { color:#993C1D; }

    /* ── Main card ───────────────────────────────────────────── */
    .ow-card { background:#fff; border:0.5px solid #e5e7eb; border-radius:12px; overflow:hidden; }

    /* ── Tab bar ─────────────────────────────────────────────── */
    .ow-tab-bar {
        display:flex; align-items:center; gap:4px;
        padding:.65rem .9rem; border-bottom:0.5px solid #e5e7eb;
        background:#fafafa; overflow-x:auto; flex-wrap:nowrap;
        scrollbar-width:none;
    }
    .ow-tab-bar::-webkit-scrollbar { display:none; }

    .ow-tab {
        display:inline-flex; align-items:center; gap:7px;
        font-size:12px; font-weight:500; padding:6px 13px;
        border-radius:7px; cursor:pointer; white-space:nowrap;
        color:#6b7280; background:transparent;
        border:0.5px solid transparent;
        transition:all .13s;
    }
    .ow-tab:hover:not(.active) { background:#f3f4f6; color:#374151; }
    .ow-tab.active { background:#fff; color:#111827; border-color:#e5e7eb; }

    .ow-tab__pill {
        display:inline-block; font-size:10px; font-weight:500;
        padding:2px 7px; border-radius:99px;
        background:#f3f4f6; color:#6b7280;
        transition:background .13s, color .13s;
    }
    .ow-tab.active .ow-tab__pill { background:#185FA5; color:#fff; }
    .ow-tab__pill--green  { background:#E1F5EE; color:#0F6E56; }
    .ow-tab__pill--amber  { background:#FAEEDA; color:#854F0B; }
    .ow-tab__pill--purple { background:#EEEDFE; color:#534AB7; }
    .ow-tab__pill--coral  { background:#FAECE7; color:#993C1D; }

    /* ── Table ───────────────────────────────────────────────── */
    .ow-table { width:100%; border-collapse:collapse; }
    .ow-table thead tr { background:#fafafa; border-bottom:0.5px solid #e5e7eb; }
    .ow-table th {
        padding:.65rem 1rem; font-size:10px; font-weight:500;
        color:#6b7280; text-transform:uppercase; letter-spacing:.07em;
        border:none; white-space:nowrap; text-align:left;
    }
    .ow-table td { padding:.8rem 1rem; border:none; vertical-align:middle; }
    .ow-table tbody tr { border-bottom:0.5px solid #f3f4f6; transition:background .12s; }
    .ow-table tbody tr:last-child { border-bottom:none; }
    .ow-table tbody tr:nth-child(even):not(.ow-row--overdue):not(.ow-row--paid) { background:#fafafa; }
    .ow-table tbody tr:hover:not(.ow-row--overdue):not(.ow-row--paid) { background:#f3f4f6; }
    .ow-table tbody tr.ow-row--overdue td { background:#FDF3F0; }
    .ow-table tbody tr.ow-row--overdue:hover td { background:#FAECE7; }
    .ow-table tbody tr.ow-row--paid td { background:#F0FAF5; }
    .ow-table tbody tr.ow-row--paid:hover td { background:#E1F5EE; }

    /* ── Invoice number chip ─────────────────────────────────── */
    .ow-inv-no {
        display:inline-block; font-size:11px; font-weight:500;
        font-family:monospace; letter-spacing:.04em;
        background:#E6F1FB; color:#0C447C;
        padding:3px 9px; border-radius:6px;
    }

    /* ── Property + unit cell ────────────────────────────────── */
    .ow-prop-name { font-size:13px; font-weight:500; color:#111827; display:block; }
    .ow-unit-name { font-size:11px; color:#9ca3af; display:block; margin-top:1px; }

    /* ── Secondary text ──────────────────────────────────────── */
    .ow-muted { font-size:12px; color:#6b7280; }
    .ow-muted--overdue { font-size:12px; color:#993C1D; font-weight:500; }

    /* ── Invoice cell sub-label ──────────────────────────────── */
    .ow-cell-sub { display:block; font-size:11px; color:#9ca3af; margin-top:2px; }

    /* ── Phone link inside property cell ─────────────────────── */
    .ow-cell-phone { font-size:11px; color:#185FA5; text-decoration:none; }
    .ow-cell-phone:hover { color:#0F4A84; text-decoration:underline; }

    /* ── Amount pills ────────────────────────────────────────── */
    .ow-amt {
        font-size:13px; font-weight:500; padding:3px 10px;
        border-radius:99px; white-space:nowrap; display:inline-block;
    }
    .ow-amt--paid    { background:#E1F5EE; color:#0F6E56; }
    .ow-amt--pending { background:#FAEEDA; color:#854F0B; }
    .ow-amt--overdue { background:#FAECE7; color:#993C1D; }
    .ow-amt--bank    { background:#EEEDFE; color:#534AB7; }

    /* ── Status badges ───────────────────────────────────────── */
    .ow-badge {
        display:inline-flex; align-items:center; gap:4px;
        font-size:11px; font-weight:500; padding:3px 9px;
        border-radius:99px; white-space:nowrap;
    }
    .ow-badge--paid    { background:#E1F5EE; color:#0F6E56; }
    .ow-badge--pending { background:#FAEEDA; color:#854F0B; border:0.5px solid #F5D9A8; }
    .ow-badge--overdue { background:#FAECE7; color:#993C1D; border:0.5px solid #F5C4B3; }
    .ow-badge--bank    { background:#EEEDFE; color:#534AB7; border:0.5px solid #CECBF6; }

    /* ── Row action buttons ──────────────────────────────────── */
    .ow-row-actions { display:flex; align-items:center; justify-content:flex-end; gap:6px; flex-wrap:nowrap; }

    .ow-act {
        display:inline-flex; align-items:center; gap:4px;
        font-size:11px; font-weight:500; padding:4px 10px;
        border-radius:6px; cursor:pointer; border:none;
        white-space:nowrap; text-decoration:none; transition:background .13s;
    }
    .ow-act--ghost  { background:#f3f4f6; color:#374151; border:0.5px solid #e5e7eb; }
    .ow-act--ghost:hover { background:#e5e7eb; color:#111827; text-decoration:none; }
    .ow-act--blue   { background:#E6F1FB; color:#0C447C; border:0.5px solid #B5D4F4; }
    .ow-act--blue:hover { background:#B5D4F4; color:#042C53; text-decoration:none; }
    .ow-act--coral  { background:#FAECE7; color:#993C1D; border:0.5px solid #F5C4B3; }
    .ow-act--coral:hover { background:#F5C4B3; color:#712B13; text-decoration:none; }
    .ow-act--green  { background:#E1F5EE; color:#0F6E56; border:0.5px solid #9FE1CB; }
    .ow-act--green:hover { background:#9FE1CB; color:#085041; text-decoration:none; }

    /* ── DataTables overrides ────────────────────────────────── */
    div.dataTables_wrapper { padding:0; }
    div.dataTables_wrapper div.dataTables_filter { display:none; }
    div.dataTables_wrapper div.dataTables_length { padding:.75rem 1.1rem; }
    div.dataTables_wrapper div.dataTables_length select {
        border:0.5px solid #e5e7eb; border-radius:7px;
        padding:5px 10px; font-size:12px; color:#374151;
        outline:none; background:#fff;
        transition:border-color .15s, box-shadow .15s;
    }
    div.dataTables_wrapper div.dataTables_length select:focus {
        border-color:#185FA5; box-shadow:0 0 0 3px rgba(24,95,165,.1);
    }
    div.dataTables_wrapper div.dataTables_paginate { padding:.75rem 1.1rem; }
    div.dataTables_wrapper div.dataTables_paginate .paginate_button {
        border-radius:7px !important; border:0.5px solid transparent !important;
        font-size:12px !important; padding:4px 10px !important;
        color:#374151 !important; transition:background .12s !important;
    }
    div.dataTables_wrapper div.dataTables_paginate .paginate_button:hover {
        background:#f3f4f6 !important; color:#111827 !important; border-color:#e5e7eb !important;
    }
    div.dataTables_wrapper div.dataTables_paginate .paginate_button.current,
    div.dataTables_wrapper div.dataTables_paginate .paginate_button.current:hover {
        background:#185FA5 !important; border-color:#185FA5 !important; color:#fff !important;
    }
    div.dataTables_wrapper div.dataTables_info { padding:.75rem 1.1rem; font-size:12px; color:#9ca3af; }
    table.dataTable thead th.sorting::before,
    table.dataTable thead th.sorting::after,
    table.dataTable thead th.sorting_asc::after,
    table.dataTable thead th.sorting_desc::after { opacity:.35; }

    /* ── Modal styles ────────────────────────────────────────── */
    .ow-modal { border:0.5px solid #e5e7eb; border-radius:12px; overflow:hidden; }
    .ow-modal__header {
        display:flex; align-items:center; justify-content:space-between;
        padding:.9rem 1.25rem; border-bottom:0.5px solid #e5e7eb;
        background:#fafafa;
    }
    .ow-modal__header .modal-title { font-size:15px; font-weight:500; color:#111827; margin:0; }
    .ow-modal__close {
        display:inline-flex; align-items:center; justify-content:center;
        width:30px; height:30px; border-radius:7px; border:none;
        background:transparent; color:#6b7280; cursor:pointer;
        transition:background .13s, color .13s;
    }
    .ow-modal__close:hover { background:#f3f4f6; color:#111827; }
    .ow-modal__body { padding:1.25rem; }
    .ow-modal__footer {
        display:flex; align-items:center; gap:8px;
        padding:.9rem 1.25rem; border-top:0.5px solid #e5e7eb;
        background:#fafafa; justify-content:flex-start;
    }

    /* ── Form elements ───────────────────────────────────────── */
    .ow-form-section {
        background:#f9fafb; border:0.5px solid #e5e7eb;
        border-radius:10px; padding:1.1rem;
    }
    .ow-label {
        display:block; font-size:12px; font-weight:500;
        color:#374151; margin-bottom:5px;
    }
    .ow-label-hint { font-weight:400; color:#9ca3af; font-size:11px; }
    .ow-input {
        font-size:13px !important; border:0.5px solid #e5e7eb !important;
        border-radius:7px !important; padding:7px 10px !important;
        transition:border-color .15s, box-shadow .15s !important;
    }
    .ow-input:focus {
        border-color:#185FA5 !important;
        box-shadow:0 0 0 3px rgba(24,95,165,.1) !important;
        outline:none !important;
    }

    .ow-link-btn {
        font-size:11px; font-weight:500; color:#185FA5;
        background:none; border:none; cursor:pointer; padding:0;
        text-decoration:none; transition:color .13s;
    }
    .ow-link-btn:hover { color:#0F4A84; }

    .ow-remove-btn {
        font-size:11px; font-weight:500; color:#993C1D;
        background:none; border:none; cursor:pointer; padding:0;
        transition:color .13s;
    }
    .ow-remove-btn:hover { color:#712B13; }

    .ow-checkbox { display:flex; align-items:center; gap:7px; }
    .ow-checkbox label { font-size:12px; font-weight:500; color:#374151; cursor:pointer; margin:0; }

    .ow-back-link { cursor:pointer; font-size:14px; font-weight:500; display:flex; align-items:center; }

    /* ── Search wrap ─────────────────────────────────────────── */
    .ow-search-wrap { position:relative; display:flex; align-items:center; }
    .ow-search-wrap svg { position:absolute; left:8px; color:#9ca3af; pointer-events:none; }
    .ow-search-wrap input {
        border:0.5px solid #e5e7eb; border-radius:7px;
        padding:6px 10px 6px 28px; font-size:12px; color:#374151;
        background:#fff; outline:none; width:260px;
        transition:border-color .15s, box-shadow .15s;
    }
    .ow-search-wrap input:focus { border-color:#185FA5; box-shadow:0 0 0 3px rgba(24,95,165,.1); }
    .ow-search-wrap input::placeholder { color:#c4c4c4; }

    /* ── Invoice preview modal ──────────────────────────────── */
    .ipv-header {
        display:flex; align-items:flex-start; justify-content:space-between;
        padding:1.5rem 1.5rem 1.25rem; border-bottom:0.5px solid #e5e7eb;
        gap:1rem;
    }
    .ipv-header__left { display:flex; flex-direction:column; gap:8px; }
    .ipv-logo { height:44px; width:auto; max-width:120px; object-fit:contain; border-radius:6px; }
    .ipv-number { font-size:17px; font-weight:600; color:#111827; letter-spacing:-.01em; }
    .ipv-meta { display:flex; flex-direction:column; gap:2px; }
    .ipv-meta span { font-size:12px; color:#6b7280; }
    .ipv-header__right { flex-shrink:0; }

    /* Status badges inside preview */
    .ipv-status-paid {
        display:inline-flex; align-items:center; gap:5px;
        font-size:11px; font-weight:600; padding:4px 12px; border-radius:99px;
        background:#E1F5EE; color:#0F6E56; border:0.5px solid #9FE1CB;
        text-transform:uppercase; letter-spacing:.04em;
    }
    .ipv-status-pending {
        display:inline-flex; align-items:center; gap:5px;
        font-size:11px; font-weight:600; padding:4px 12px; border-radius:99px;
        background:#FAEEDA; color:#854F0B; border:0.5px solid #F5D9A8;
        text-transform:uppercase; letter-spacing:.04em;
    }

    .ipv-addresses {
        display:grid; grid-template-columns:1fr 1fr;
        border-bottom:0.5px solid #e5e7eb;
    }
    .ipv-address-block { padding:1.25rem 1.5rem; }
    .ipv-address-block:first-child { border-right:0.5px solid #e5e7eb; }
    .ipv-label {
        font-size:10px; font-weight:600; text-transform:uppercase;
        letter-spacing:.08em; color:#9ca3af; margin-bottom:8px;
    }
    .ipv-name { font-size:13px; font-weight:600; color:#111827; margin-bottom:4px; }
    .ipv-line { display:block; font-size:12px; color:#6b7280; margin-bottom:2px; }
    .ipv-property-chip {
        display:inline-flex; align-items:center; gap:5px; margin-top:6px;
        font-size:11px; font-weight:500; color:#185FA5;
        background:#E6F1FB; padding:3px 9px; border-radius:6px;
    }
    .ipv-dot { color:#B5D4F4; }

    /* Pay-to address populated by JS */
    .pay-invoice-address h5 { font-size:13px; font-weight:600; color:#111827; margin-bottom:3px; }
    .pay-invoice-address h6 { font-size:12px; color:#6b7280; font-weight:400; margin-bottom:2px; }
    .pay-invoice-address small { font-size:12px; color:#6b7280; }

    .ipv-section { padding:1.25rem 1.5rem; border-top:0.5px solid #e5e7eb; }
    .ipv-section-title {
        font-size:10px; font-weight:600; text-transform:uppercase;
        letter-spacing:.08em; color:#9ca3af; margin-bottom:.85rem;
    }

    .ipv-table { width:100%; border-collapse:collapse; font-size:12px; }
    .ipv-table thead tr { background:#f9fafb; border-bottom:0.5px solid #e5e7eb; }
    .ipv-table th {
        padding:.55rem .75rem; font-size:10px; font-weight:600;
        text-transform:uppercase; letter-spacing:.07em; color:#6b7280;
        white-space:nowrap;
    }
    .ipv-table td {
        padding:.7rem .75rem; border-bottom:0.5px solid #f3f4f6;
        color:#374151; vertical-align:top;
    }
    .ipv-table tbody tr:last-child td { border-bottom:none; }
    .ipv-table .text-end { text-align:right; }

    /* Hide transaction ID on narrow modal */
    @media (max-width: 576px) {
        .ipv-col-txid { display:none; }
        .ipv-addresses { grid-template-columns:1fr; }
        .ipv-address-block:first-child { border-right:none; border-bottom:0.5px solid #e5e7eb; }
    }

    .ipv-total-row {
        display:flex; justify-content:flex-end;
        padding:.75rem 0 0;
    }
    .ipv-total-box {
        display:flex; align-items:baseline; gap:10px;
        background:#f9fafb; border:0.5px solid #e5e7eb;
        border-radius:8px; padding:.6rem 1rem;
    }
    .ipv-total-label { font-size:11px; font-weight:500; color:#6b7280; text-transform:uppercase; letter-spacing:.06em; }
    .ipv-total-amount { font-size:16px; font-weight:700; color:#111827; }

    .ipv-footer {
        padding:.85rem 1.5rem;
        border-top:0.5px solid #e5e7eb;
        background:#fafafa;
    }
    .ipv-footer__note { font-size:11px; color:#9ca3af; }

    /* ── Responsive ──────────────────────────────────────────── */
    @media (max-width: 768px) {
        .ow-strip__item  { padding:.7rem .9rem; }
        .ow-strip__val   { font-size:16px; }
        .ow-toolbar      { flex-direction:column; align-items:flex-start; }
        .ow-toolbar__filters { align-items:flex-start; width:100%; }
        .ow-toolbar__actions { align-self:flex-end; }
        /* Action buttons wrap and stack on small screens */
        .ow-row-actions  { flex-wrap:wrap; justify-content:flex-end; gap:4px; }
        .ow-act          { font-size:11px; padding:4px 8px; }
        /* Amount totals: hide side dividers, wrap full-width below the counts */
        .ow-strip__divider--amount { display:none; }
        .ow-strip__item--amount {
            flex-basis:50%;
            max-width:50%;
            border-top:0.5px solid #e5e7eb;
        }
        /* Search fills available width on tablet */
        .ow-filter-group { width:100%; }
        .ow-search-wrap { width:100%; }
        .ow-search-wrap input { width:100%; }
        .ow-select { width:100%; min-width:100%; }
    }
    @media (max-width: 540px) {
        .ow-toolbar__filters { flex-direction:column; }
        /* Stack amount items full width on very small screens */
        .ow-strip__item--amount {
            flex-basis:100%;
            max-width:100%;
        }
        .ow-strip__item--amount + .ow-strip__item--amount {
            border-top:none;
        }
    }
</style>

@endsection

@push('style')
    @include('common.layouts.datatable-style')
@endpush

@push('script')
    @include('common.layouts.datatable-script')
    <script src="{{ asset('/') }}assets/js/pages/billing-center-datatables.init.js"></script>
    <script src="{{ asset('assets/js/custom/invoice.js') }}"></script>
    <script src="{{ asset('assets/js/custom/invoice-type2.js') }}"></script>
    @if (request('id') && request('tab') == 'view')
        <script>
            view("{{ route('owner.invoice.details', request('id')) }}");
        </script>
    @endif
@endpush