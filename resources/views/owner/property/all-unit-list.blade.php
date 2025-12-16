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
                                        <li class="breadcrumb-item">
                                            <a href="{{ route('owner.property.allUnit') }}"
                                                title="{{ __('Properties') }}">{{ __('Properties') }}</a>
                                        </li>
                                        <li class="breadcrumb-item active" aria-current="page">{{ $pageTitle }}</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="tenants-details-layout-wrap position-relative">
                        <div class="row">
                            <div class="row align-items-center">
                                <div class="col-md-12 mb-3">
                                    <div class="property-top-search-bar-right text-end">
                                        <button type="button" class="theme-btn p-1 tbl-action-btn add-unit" data-detailsurl="" title="{{ __('Add Unit')}}"> {{ __('Add Unit') }} &nbsp;<span class="iconify" data-icon="clarity:plus-circle-solid"></span></button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12 col-lg-12 col-xl-12 col-xxl-12">
                                <div class="account-settings-rightside bg-off-white theme-border radius-4 p-25">
                                    <div class="tenants-details-payment-history">
                                        <div class="account-settings-content-box">
                                            <div class="tenants-details-payment-history-table">
                                                <table id="allDataTable" class="table responsive theme-border p-20">
                                                    <thead>
                                                        <tr>
                                                            <th>{{ __('SL') }}</th>
                                                            <th data-priority="1">{{ __('Name') }}</th>
                                                            <th>{{ __('Image') }}</th>
                                                            <th>{{ __('Property') }}</th>
                                                            <th>{{ __('Tenant') }}</th>
                                                            <th class="text-center">{{ __('Action') }}</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($units as $unit)
                                                            <tr>
                                                                <td>{{ ($units->currentPage() - 1) * $units->perPage() + $loop->iteration }}</td>
                                                                <td>{{ $unit->unit_name }}</td>
                                                                <td>
                                                                    <img class="rounded-circle avatar-md tbl-user-image"
                                                                        src="{{ $unit->first_image_url }}">
                                                                </td>
                                                                <td>{{ $unit->property_name }}</td>
                                                                <td>
                                                                    @if ($unit->first_name)
                                                                        <span class="text-success">{{ $unit->first_name }}
                                                                            {{ $unit->last_name }}</span>
                                                                    @else
                                                                        <span
                                                                            class="text-danger">{{ __('Not Available') }}</span>
                                                                    @endif
                                                                </td>
                                                                <td class="text-center">
                                                                    @if (is_null($unit->first_name))
                                                                        <button class="p-1 tbl-action-btn deleteItem"
                                                                            data-formid="delete_row_form_{{ $unit->id }}">
                                                                            <span class="iconify"
                                                                                data-icon="ep:delete-filled"></span>
                                                                        </button>
                                                                        <form
                                                                            action="{{ route('owner.property.unit.delete', [$unit->id]) }}"
                                                                            method="post"
                                                                            id="delete_row_form_{{ $unit->id }}">
                                                                            {{ method_field('DELETE') }}
                                                                            <input type="hidden" name="_token"
                                                                                value="{{ csrf_token() }}">
                                                                        </form>
                                                                    @endif
                                                                    <button type="button" class="p-1 tbl-action-btn unit-edit" data-detailsurl="{{ route('owner.property.unit.details', $unit->id) }}" title="{{ __('Edit') }}"><span class="iconify" data-icon="clarity:note-edit-solid"></span></button>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                                <!-- Display pagination links -->
                                                {{ $units->links() }}
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
    </div>

    {{-- Modal  --}}
    <div class="modal fade edit_modal" id="editUnitModal" tabindex="-1" aria-labelledby="editUnitModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="editInvoiceModalLabel">{{ __('Edit Unit') }}</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <span class="iconify" data-icon="akar-icons:cross"></span>
                    </button>
                </div>
                

                <form class="ajax" action="{{ route('owner.property.unit.edit') }}" method="post" data-handler="getShowMessage">
                    @csrf
                    <input type="hidden" name="property_id" class="d-none property_id" value="">
                    <div class="form-card add-property-box bg-off-white theme-border radius-4 p-20">
                        <div class="add-property-inner-box bg-white theme-border radius-4 p-20">
                            <div class="tab-content" id="myTab1Content">
                                <div class="tab-pane fade show active" id="multi-unit-tab-pane" role="tabpanel"
                                    aria-labelledby="multi-unit-tab" tabindex="0">
                                    <!-- Multi field Wrapper Start -->
                                    <div class="multi-field-wrapper">
                                        <div class="multi-fields">
                                    
                                            <div class="multi-field border-bottom pb-25 mb-25">
                                                <input type="hidden" name="unit_id" value="">
                                                <div class="row">
                                                    <div class="col-12 d-flex justify-content-between">
                                                        <div
                                                            class="upload-profile-photo-box mb-25">
                                                            <div
                                                                class="profile-user position-relative d-inline-block">
                                                                <img id="unit-image" src=""
                                                                    class="rounded-circle avatar-xl default-user-profile-image">
                                                                <div
                                                                    class="avatar-xs p-0 rounded-circle default-profile-photo-edit">
                                                                    <input id="default-profile-img-file-input"
                                                                        type="file" name="unit_image"
                                                                        class="default-profile-img-file-input">
                                                                    <label for="default-profile-img-file-input"
                                                                        class="default-profile-photo-edit avatar-xs">
                                                                        <span class="avatar-title rounded-circle"
                                                                            title="Change Image">
                                                                            <i class="ri-camera-fill"></i>
                                                                        </span>
                                                                    </label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4 mb-25">
                                                        <label
                                                            class="label-text-title color-heading font-medium mb-2">{{ __('Unit Name') }}</label>
                                                        <input type="text" name="unit_name"
                                                            class="form-control multiple-unit_name"
                                                            placeholder="{{ __('Unit Name') }}">
                                                    </div>
                                                    <div class="col-md-4 mb-25">
                                                        <label
                                                            class="label-text-title color-heading font-medium mb-2">{{ __('Bedroom') }}</label>
                                                        <input type="number" min="0" name="bedroom"
                                                            value="" class="form-control multiple-bedroom"
                                                            placeholder="0">
                                                    </div>
                                                    <div class="col-md-4 mb-25">
                                                        <label
                                                            class="label-text-title color-heading font-medium mb-2">{{ __('Baths') }}</label>
                                                        <input type="number" min="0" name="bath"
                                                            value="" class="form-control multiple-bath"
                                                            placeholder="0">
                                                    </div>
                                                    <div class="col-md-4 mb-25">
                                                        <label
                                                            class="label-text-title color-heading font-medium mb-2">{{ __('Kitchen') }}</label>
                                                        <input type="number" min="0" name="kitchen"
                                                            value="" class="form-control multiple-kitchen"
                                                            placeholder="0">
                                                    </div>
                                                    <div class="col-md-4 mb-25">
                                                        <label
                                                            class="label-text-title color-heading font-medium mb-2">{{ __('Square Feet') }}</label>
                                                        <input type="text" name="square_feet" value=""
                                                            class="form-control multiple-square_feet"
                                                            placeholder="{{ __('Square Feet') }}">
                                                    </div>
                                                    <div class="col-md-4 mb-25">
                                                        <label
                                                            class="label-text-title color-heading font-medium mb-2">{{ __('Amenities') }}</label>
                                                        <input type="text" name="amenities" value=""
                                                            class="form-control multiple-amenities"
                                                            placeholder="{{ __('Amenities') }}">
                                                    </div>
                                                    <div class="col-md-4 mb-25">
                                                        <label
                                                            class="label-text-title color-heading font-medium mb-2">{{ __('Condition') }}</label>
                                                        <input type="text" name="condition" value=""
                                                            class="form-control multiple-condition"
                                                            placeholder="{{ __('Condition') }}">
                                                    </div>
                                                    <div class="col-md-4 mb-25">
                                                        <label
                                                            class="label-text-title color-heading font-medium mb-2">{{ __('Parking') }}</label>
                                                        <input type="text" name="parking" value=""
                                                            class="form-control multiple-parking"
                                                            placeholder="{{ __('Parking') }}">
                                                    </div>
                                                    <div class="col-md-4 mb-25">
                                                        <label
                                                            class="label-text-title color-heading font-medium mb-2">{{ __('General Rent') }}</label>
                                                        <input type="number" name="general_rent"
                                                            id="general_rent"
                                                            value="" class="form-control"
                                                            placeholder="">
                                                    </div>
                                                    <div class="col-md-4 mb-25">
                                                        <label
                                                            class="label-text-title color-heading font-medium mb-2">{{ __('Security deposit') }}</label>
                                                        <div class="input-group custom-input-group">
                                                            <select id="security_deposit_type" name="security_deposit_type"
                                                                class="form-control">
                                                                <option value="0">
                                                                    {{ __('Fixed') }}</option>
                                                                <option value="1">
                                                                    {{ __('Percentage') }}</option>
                                                            </select>
                                                            <input type="number" name="security_deposit"
                                                                id="security_deposit"
                                                                value="" class="form-control"
                                                                placeholder="">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4 mb-25">
                                                        <label
                                                            class="label-text-title color-heading font-medium mb-2">{{ __('Late fee') }}</label>
                                                        <div class="input-group custom-input-group">
                                                            <select id="late_fee_type" name="late_fee_type" class="form-control">
                                                                <option value="0">
                                                                    {{ __('Fixed') }}</option>
                                                                <option value="1">
                                                                    {{ __('Percentage') }}</option>
                                                            </select>
                                                            <input type="number" name="late_fee"
                                                                id="late_fee"
                                                                value="" class="form-control"
                                                                placeholder="">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4 mb-25">
                                                        <label
                                                            class="label-text-title color-heading font-medium mb-2">{{ __('Incident receipt') }}</label>
                                                        <input type="text" name="incident_receipt"
                                                            id="incident_receipt"
                                                            value="" class="form-control"
                                                            placeholder="">
                                                    </div>

                                                    <label
                                                        class="label-text-title color-heading font-medium mb-2">{{ __('Rent Type') }}</label>
                                                    <ul class="nav nav-tabs select-property-nav-tabs border-0 mb-20"
                                                        id="unitTypeDateChangeTab" role="tablist">
                                                        <li class="nav-item" role="presentation">
                                                            <button
                                                                class="p-0 me-4 mb-1 nav-link select_rent_type"
                                                                data-rent_type="1" data-id=""
                                                                id="monthly-unit-block-tab"
                                                                data-bs-toggle="tab"
                                                                data-bs-target="#monthly-unit-block-tab-pane"
                                                                type="button" role="tab"
                                                                aria-controls="monthly-unit-block-tab-pane"
                                                                aria-selected="true">
                                                                <span
                                                                    class="select-property-nav-text d-flex align-items-center position-relative">
                                                                    <span
                                                                        class="select-property-nav-text-box me-2"></span>{{ __('Monthly') }}
                                                                </span>
                                                            </button>
                                                        </li>
                                                        <li class="nav-item" role="presentation">
                                                            <button
                                                                class="p-0 me-4 mb-1 nav-link select_rent_type"
                                                                data-rent_type="2" data-id=""
                                                                id="yearly-unit-block-tab"
                                                                data-bs-toggle="tab"
                                                                data-bs-target="#yearly-unit-block-tab-pane"
                                                                type="button" role="tab"
                                                                aria-controls="yearly-unit-block-tab-pane"
                                                                aria-selected="false">
                                                                <span
                                                                    class="select-property-nav-text d-flex align-items-center position-relative">
                                                                    <span
                                                                        class="select-property-nav-text-box me-2"></span>{{ __('Yearly') }}
                                                                </span>
                                                            </button>
                                                        </li>
                                                        <li class="nav-item" role="presentation">
                                                            <button
                                                                class="p-0 me-4 mb-1 nav-link select_rent_type"
                                                                data-rent_type="3" data-id=""
                                                                id="custom-unit-block-tab"
                                                                data-bs-toggle="tab"
                                                                data-bs-target="#custom-unit-block-tab-pane"
                                                                type="button" role="tab"
                                                                aria-controls="custom-unit-block-tab-pane"
                                                                aria-selected="false">
                                                                <span
                                                                    class="select-property-nav-text d-flex align-items-center position-relative">
                                                                    <span
                                                                        class="select-property-nav-text-box me-2"></span>{{ __('Custom') }}
                                                                </span>
                                                            </button>
                                                        </li>
                                                    </ul>
                                                    <input type="hidden" name="rent_type"
                                                        value=""
                                                        id="rent_type">
                                                    <div class="tab-content"
                                                        id="unitTypeDateChangeTabContent">
                                                        <div class="tab-pane fade "
                                                            id="monthly-unit-block-tab-pane" role="tabpanel"
                                                            aria-labelledby="monthly-unit-block-tab"
                                                            tabindex="0">
                                                            <div class="row">
                                                                <div class="col-md-6 mb-25">
                                                                    <label
                                                                        class="label-text-title color-heading font-medium mb-2">{{ __('Due Day') }}</label>
                                                                    <input type="number" step="any" min="0"
                                                                        name="monthly_due_day"
                                                                        value=""
                                                                        class="form-control" placeholder="Type day of month: 1 to 30">
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="tab-pane fade"
                                                            id="yearly-unit-block-tab-pane" role="tabpanel"
                                                            aria-labelledby="yearly-unit-block-tab"
                                                            tabindex="0">
                                                            <div class="row">
                                                                <div class="col-md-6 mb-25">
                                                                    <label
                                                                        class="label-text-title color-heading font-medium mb-2">{{ __('Due Month') }}</label>
                                                                    <input type="number" step="any" min="0"
                                                                        name="yearly_due_day"
                                                                        value=""
                                                                        class="form-control"
                                                                        placeholder="{{ __('Type month of year: 1 to 12') }}">
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="tab-pane fade"
                                                            id="custom-unit-block-tab-pane" role="tabpanel"
                                                            aria-labelledby="custom-unit-block-tab"
                                                            tabindex="0">
                                                            <div class="row">
                                                                <div class="col-md-4 mb-25">
                                                                    <label
                                                                        class="label-text-title color-heading font-medium mb-2">{{ __('Lease Start date') }}</label>
                                                                    <div class="custom-datepicker">
                                                                        <div class="custom-datepicker-inner position-relative">
                                                                            <input type="text"
                                                                                name="lease_start_date"
                                                                                value=""
                                                                                class="datepicker form-control" autocomplete="off"
                                                                                placeholder="dd-mm-yy">
                                                                            <i class="ri-calendar-2-line"></i>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <div class="col-md-4 mb-25">
                                                                    <label
                                                                        class="label-text-title color-heading font-medium mb-2">{{ __('Lease End date') }}</label>
                                                                    <div class="custom-datepicker">
                                                                        <div class="custom-datepicker-inner position-relative">
                                                                            <input type="text"
                                                                                name="lease_end_date"
                                                                                value=""
                                                                                class="datepicker form-control" autocomplete="off"
                                                                                placeholder="dd-mm-yy">
                                                                            <i class="ri-calendar-2-line"></i>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <div class="col-md-4 mb-25">
                                                                    <label
                                                                        class="label-text-title color-heading font-medium mb-2">{{ __('Payment due on date') }}</label>
                                                                    <div class="custom-datepicker">
                                                                        <div class="custom-datepicker-inner position-relative">
                                                                            <input type="text"
                                                                                name="lease_payment_due_date"
                                                                                value=""
                                                                                class="datepicker form-control" autocomplete="off"
                                                                                placeholder="dd-mm-yy">
                                                                            <i class="ri-calendar-2-line"></i>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6 col-lg-6 col-xl-6 mb-25">
                                                        <label
                                                            class="label-text-title color-heading font-medium mb-2">{{ __('Description') }}</label>
                                                        <input type="text" name="description" value=""
                                                            class="form-control multiple-description"
                                                            placeholder="{{ __('Description') }}">
                                                    </div>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                    <!-- Multi field Wrapper End -->
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

    <div class="modal fade add_modal" id="addUnitModal" tabindex="-1" aria-labelledby="addUnitModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="editInvoiceModalLabel">{{ __('Add Unit') }}</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <span class="iconify" data-icon="akar-icons:cross"></span>
                    </button>
                </div>
                

                <form class="ajax" action="{{ route('owner.property.unit.edit') }}" method="post" data-handler="getShowMessage">
                    @csrf
                    <div class="form-card add-property-box bg-off-white theme-border radius-4 p-20">
                        <div class="add-property-inner-box bg-white theme-border radius-4 p-20">
                            <div class="tab-content" id="myTab1Content">
                                <div class="tab-pane fade show active" id="multi-unit-tab-pane" role="tabpanel"
                                    aria-labelledby="multi-unit-tab" tabindex="0">
                                    <!-- Multi field Wrapper Start -->
                                    <div class="multi-field-wrapper">
                                        <div class="multi-fields">
                                    
                                            <div class="multi-field border-bottom pb-25 mb-25">
                                                <input type="hidden" name="unit_id" value="">
                                                <div class="row">
                                                    <div class="col-12 d-flex justify-content-between">
                                                        <div
                                                            class="upload-profile-photo-box mb-25">
                                                            <div
                                                                class="profile-user position-relative d-inline-block">
                                                                <img id="unit-image" src="{{asset('assets/images/no-image.jpg')}}"
                                                                    class="rounded-circle avatar-xl default-unit-profile-image">
                                                                <div
                                                                    class="avatar-xs p-0 rounded-circle default-profile-photo-edit">
                                                                    <input id="default-unit-img-file-input"
                                                                        type="file" name="unit_image"
                                                                        class="default-unit-img-file-input">
                                                                    <label for="default-unit-img-file-input"
                                                                        class="default-profile-photo-edit avatar-xs">
                                                                        <span class="avatar-title rounded-circle"
                                                                            title="Change Image">
                                                                            <i class="ri-camera-fill"></i>
                                                                        </span>
                                                                    </label>
                                                                </div>
                                                            </div>
                                                        </div>
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
                                                    <div class="col-md-4 mb-25">
                                                        <label
                                                            class="label-text-title color-heading font-medium mb-2">{{ __('Unit Name') }}</label>
                                                        <input type="text" name="unit_name"
                                                            class="form-control multiple-unit_name"
                                                            placeholder="{{ __('Unit Name') }}">
                                                    </div>
                                                    <div class="col-md-4 mb-25">
                                                        <label
                                                            class="label-text-title color-heading font-medium mb-2">{{ __('Bedroom') }}</label>
                                                        <input type="number" min="0" name="bedroom"
                                                            value="" class="form-control multiple-bedroom"
                                                            placeholder="0">
                                                    </div>
                                                    <div class="col-md-4 mb-25">
                                                        <label
                                                            class="label-text-title color-heading font-medium mb-2">{{ __('Baths') }}</label>
                                                        <input type="number" min="0" name="bath"
                                                            value="" class="form-control multiple-bath"
                                                            placeholder="0">
                                                    </div>
                                                    <div class="col-md-4 mb-25">
                                                        <label
                                                            class="label-text-title color-heading font-medium mb-2">{{ __('Kitchen') }}</label>
                                                        <input type="number" min="0" name="kitchen"
                                                            value="" class="form-control multiple-kitchen"
                                                            placeholder="0">
                                                    </div>
                                                    <div class="col-md-4 mb-25">
                                                        <label
                                                            class="label-text-title color-heading font-medium mb-2">{{ __('Square Feet') }}</label>
                                                        <input type="text" name="square_feet" value=""
                                                            class="form-control multiple-square_feet"
                                                            placeholder="{{ __('Square Feet') }}">
                                                    </div>
                                                    <div class="col-md-4 mb-25">
                                                        <label
                                                            class="label-text-title color-heading font-medium mb-2">{{ __('Amenities') }}</label>
                                                        <input type="text" name="amenities" value=""
                                                            class="form-control multiple-amenities"
                                                            placeholder="{{ __('Amenities') }}">
                                                    </div>
                                                    <div class="col-md-4 mb-25">
                                                        <label
                                                            class="label-text-title color-heading font-medium mb-2">{{ __('Condition') }}</label>
                                                        <input type="text" name="condition" value=""
                                                            class="form-control multiple-condition"
                                                            placeholder="{{ __('Condition') }}">
                                                    </div>
                                                    <div class="col-md-4 mb-25">
                                                        <label
                                                            class="label-text-title color-heading font-medium mb-2">{{ __('Parking') }}</label>
                                                        <input type="text" name="parking" value=""
                                                            class="form-control multiple-parking"
                                                            placeholder="{{ __('Parking') }}">
                                                    </div>
                                                    <div class="col-md-4 mb-25">
                                                        <label
                                                            class="label-text-title color-heading font-medium mb-2">{{ __('General Rent') }}</label>
                                                        <input type="number" name="general_rent"
                                                            id="general_rent"
                                                            value="" class="form-control"
                                                            placeholder="">
                                                    </div>
                                                    <div class="col-md-4 mb-25">
                                                        <label
                                                            class="label-text-title color-heading font-medium mb-2">{{ __('Security deposit') }}</label>
                                                        <div class="input-group custom-input-group">
                                                            <select id="security_deposit_type" name="security_deposit_type"
                                                                class="form-control">
                                                                <option value="0">
                                                                    {{ __('Fixed') }}</option>
                                                                <option value="1">
                                                                    {{ __('Percentage') }}</option>
                                                            </select>
                                                            <input type="number" name="security_deposit"
                                                                id="security_deposit"
                                                                value="" class="form-control"
                                                                placeholder="">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4 mb-25">
                                                        <label
                                                            class="label-text-title color-heading font-medium mb-2">{{ __('Late fee') }}</label>
                                                        <div class="input-group custom-input-group">
                                                            <select id="late_fee_type" name="late_fee_type" class="form-control">
                                                                <option value="0">
                                                                    {{ __('Fixed') }}</option>
                                                                <option value="1">
                                                                    {{ __('Percentage') }}</option>
                                                            </select>
                                                            <input type="number" name="late_fee"
                                                                id="late_fee"
                                                                value="" class="form-control"
                                                                placeholder="">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4 mb-25">
                                                        <label
                                                            class="label-text-title color-heading font-medium mb-2">{{ __('Incident receipt') }}</label>
                                                        <input type="text" name="incident_receipt"
                                                            id="incident_receipt"
                                                            value="" class="form-control"
                                                            placeholder="">
                                                    </div>

                                                    <label
                                                        class="label-text-title color-heading font-medium mb-2">{{ __('Rent Type') }}</label>
                                                    <ul class="nav nav-tabs select-property-nav-tabs border-0 mb-20"
                                                        id="unitTypeDateChangeTab" role="tablist">
                                                        <li class="nav-item" role="presentation">
                                                            <button
                                                                class="p-0 me-4 mb-1 nav-link add-select_rent_type"
                                                                data-add-rent_type="1" data-id=""
                                                                id="monthly-add-unit-block-tab"
                                                                data-bs-toggle="tab"
                                                                data-bs-target="#monthly-add-unit-block-tab-pane"
                                                                type="button" role="tab"
                                                                aria-controls="monthly-add-unit-block-tab-pane"
                                                                aria-selected="true">
                                                                <span
                                                                    class="select-property-nav-text d-flex align-items-center position-relative">
                                                                    <span
                                                                        class="select-property-nav-text-box me-2"></span>{{ __('Monthly') }}
                                                                </span>
                                                            </button>
                                                        </li>
                                                        <li class="nav-item" role="presentation">
                                                            <button
                                                                class="p-0 me-4 mb-1 nav-link add-select_rent_type"
                                                                data-add-rent_type="2" data-id=""
                                                                id="yearly-add-unit-block-tab"
                                                                data-bs-toggle="tab"
                                                                data-bs-target="#yearly-add-unit-block-tab-pane"
                                                                type="button" role="tab"
                                                                aria-controls="yearly-add-unit-block-tab-pane"
                                                                aria-selected="false">
                                                                <span
                                                                    class="select-property-nav-text d-flex align-items-center position-relative">
                                                                    <span
                                                                        class="select-property-nav-text-box me-2"></span>{{ __('Yearly') }}
                                                                </span>
                                                            </button>
                                                        </li>
                                                        <li class="nav-item" role="presentation">
                                                            <button
                                                                class="p-0 me-4 mb-1 nav-link add-select_rent_type"
                                                                data-add-rent_type="3" data-id=""
                                                                id="custom-add-unit-block-tab"
                                                                data-bs-toggle="tab"
                                                                data-bs-target="#custom-add-unit-block-tab-pane"
                                                                type="button" role="tab"
                                                                aria-controls="custom-add-unit-block-tab-pane"
                                                                aria-selected="false">
                                                                <span
                                                                    class="select-property-nav-text d-flex align-items-center position-relative">
                                                                    <span
                                                                        class="select-property-nav-text-box me-2"></span>{{ __('Custom') }}
                                                                </span>
                                                            </button>
                                                        </li>
                                                    </ul>
                                                    <input type="hidden" name="rent_type"
                                                        value=""
                                                        id="add_rent_type">
                                                    <div class="tab-content"
                                                        id="unitTypeDateChangeTabContent">
                                                        <div class="tab-pane fade add-tab-pane"
                                                            id="monthly-add-unit-block-tab-pane" role="tabpanel"
                                                            aria-labelledby="monthly-add-unit-block-tab"
                                                            tabindex="0">
                                                            <div class="row">
                                                                <div class="col-md-6 mb-25">
                                                                    <label
                                                                        class="label-text-title color-heading font-medium mb-2">{{ __('Due Day') }}</label>
                                                                    <input type="number" step="any" min="0"
                                                                        name="monthly_due_day"
                                                                        value=""
                                                                        class="form-control" placeholder="Type day of month: 1 to 30">
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="tab-pane fade add-tab-pane"
                                                            id="yearly-add-unit-block-tab-pane" role="tabpanel"
                                                            aria-labelledby="yearly-add-unit-block-tab"
                                                            tabindex="0">
                                                            <div class="row">
                                                                <div class="col-md-6 mb-25">
                                                                    <label
                                                                        class="label-text-title color-heading font-medium mb-2">{{ __('Due Month') }}</label>
                                                                    <input type="number" step="any" min="0"
                                                                        name="yearly_due_day"
                                                                        value=""
                                                                        class="form-control"
                                                                        placeholder="{{ __('Type month of year: 1 to 12') }}">
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="tab-pane fade add-tab-pane"
                                                            id="custom-add-unit-block-tab-pane" role="tabpanel"
                                                            aria-labelledby="custom-add-unit-block-tab"
                                                            tabindex="0">
                                                            <div class="row">
                                                                <div class="col-md-4 mb-25">
                                                                    <label
                                                                        class="label-text-title color-heading font-medium mb-2">{{ __('Lease Start date') }}</label>
                                                                    <div class="custom-datepicker">
                                                                        <div class="custom-datepicker-inner position-relative">
                                                                            <input type="text"
                                                                                name="lease_start_date"
                                                                                value=""
                                                                                class="datepicker form-control" autocomplete="off"
                                                                                placeholder="dd-mm-yy">
                                                                            <i class="ri-calendar-2-line"></i>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <div class="col-md-4 mb-25">
                                                                    <label
                                                                        class="label-text-title color-heading font-medium mb-2">{{ __('Lease End date') }}</label>
                                                                    <div class="custom-datepicker">
                                                                        <div class="custom-datepicker-inner position-relative">
                                                                            <input type="text"
                                                                                name="lease_end_date"
                                                                                value=""
                                                                                class="datepicker form-control" autocomplete="off"
                                                                                placeholder="dd-mm-yy">
                                                                            <i class="ri-calendar-2-line"></i>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <div class="col-md-4 mb-25">
                                                                    <label
                                                                        class="label-text-title color-heading font-medium mb-2">{{ __('Payment due on date') }}</label>
                                                                    <div class="custom-datepicker">
                                                                        <div class="custom-datepicker-inner position-relative">
                                                                            <input type="text"
                                                                                name="lease_payment_due_date"
                                                                                value=""
                                                                                class="datepicker form-control" autocomplete="off"
                                                                                placeholder="dd-mm-yy">
                                                                            <i class="ri-calendar-2-line"></i>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6 col-lg-6 col-xl-6 mb-25">
                                                        <label
                                                            class="label-text-title color-heading font-medium mb-2">{{ __('Description') }}</label>
                                                        <input type="text" name="description" value=""
                                                            class="form-control multiple-description"
                                                            placeholder="{{ __('Description') }}">
                                                    </div>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                    <!-- Multi field Wrapper End -->
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer justify-content-start">
                        <button type="button" class="theme-btn-back me-3" data-bs-dismiss="modal"
                            title="{{ __('Back') }}">{{ __('Back') }}</button>
                        <button type="submit" class="theme-btn me-3"
                            title="{{ __('Update') }}">{{ __('Create Unit') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('style')
    @include('common.layouts.datatable-style')
@endpush

@push('script')
    @include('common.layouts.datatable-script')
    <script src="{{ asset('assets/js/pages/alldatatables.init.js') }}"></script>
    <script src="{{ asset('assets/js/custom/property.js') }}"></script>
    <script>
        document
            .querySelector("#default-profile-img-file-input")
            .addEventListener("change", function () {
                var o = document.querySelector(".default-user-profile-image"),
                    e = document.querySelector(".default-profile-img-file-input")
                        .files[0],
                    i = new FileReader();
                i.addEventListener(
                    "load",
                    function () {
                        o.src = i.result;
                    },
                    !1
                ),
                    e && i.readAsDataURL(e);
            });

        document
            .querySelector("#default-unit-img-file-input")
            .addEventListener("change", function () {
                var o = document.querySelector(".default-unit-profile-image"),
                    e = document.querySelector(".default-unit-img-file-input").files[0],
                    i = new FileReader();
                i.addEventListener(
                    "load",
                    function () {
                        o.src = i.result;
                    },
                    !1
                ),
                    e && i.readAsDataURL(e);
            });
    </script>
@endpush
