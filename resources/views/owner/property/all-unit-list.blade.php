@extends('owner.layouts.app')

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="page-content-wrapper bg-white p-30 radius-20">

                {{-- ── Page header ──────────────────────────────────────── --}}
                <div class="ul-page-header mb-4">
                    <div>
                        <h2 class="ul-page-title">{{ $pageTitle }}</h2>
                        <nav aria-label="breadcrumb">
                            <ol class="ul-breadcrumb">
                                <li>
                                    <a href="{{ route('owner.dashboard') }}">{{ __('Dashboard') }}</a>
                                    <svg width="8" height="8" viewBox="0 0 24 24" fill="none" aria-hidden="true"><polyline points="9 18 15 12 9 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </li>
                                <li>
                                    <a href="{{ route('owner.property.allUnit') }}">{{ __('Properties') }}</a>
                                    <svg width="8" height="8" viewBox="0 0 24 24" fill="none" aria-hidden="true"><polyline points="9 18 15 12 9 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </li>
                                <li aria-current="page">{{ $pageTitle }}</li>
                            </ol>
                        </nav>
                    </div>

                    @php
                    $subscriptionService = app(\App\Services\SubscriptionService::class);
                    $unitLimit = $subscriptionService->getUnitLimit();
                    $remainingUnits = $unitLimit['remaining'] ?? 0;
                    $totalUnits = $unitLimit['total'] ?? 0;
                    $hasReachedLimit = $remainingUnits <= 0 && $totalUnits > 0;
                    $isNearLimit = $remainingUnits > 0 && $remainingUnits <= 3;
                    @endphp

                    @if($hasReachedLimit)
                    {{-- Limit reached: Upgrade CTA (link, not modal) --}}
                    <a href="{{ route('owner.subscription.index', ['current_plan' => 'no']) }}"
                    class="d-block text-decoration-none theme-btn-primary upgrade-btn"
                    title="{{ __('You\'ve used all :total units. Upgrade to add more.', ['total' => $totalUnits]) }}">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round">
                            <polyline points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>
                        </svg>
                        {{ __('Upgrade to Add Unit') }}
                    </a>
                    @elseif($isNearLimit)
                    {{-- Near limit: Add with warning (modal trigger) --}}
                    <button type="button"
                            class="d-block text-decoration-none theme-btn-primary near-limit-btn"
                            data-bs-toggle="modal"
                            data-bs-target="#addUnitModal"
                            title="{{ __('Only :remaining units remaining', ['remaining' => $remainingUnits]) }}">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none">
                            <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/>
                        </svg>
                        {{ __('Add Unit') }}
                        <span class="remaining-badge">{{ $remainingUnits }} left</span>
                    </button>
                    @else
                    {{-- Normal: plenty of units (modal trigger) --}}
                    <button type="button"
                            class="d-block text-decoration-none theme-btn-primary"
                            data-bs-toggle="modal"
                            data-bs-target="#addUnitModal"
                            title="{{ __('Add Unit') }}">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none">
                            <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/>
                        </svg>
                        {{ __('Add Unit') }}
                    </button>
                    @endif
                </div>

                {{-- ── Units table ──────────────────────────────────────── --}}
                <div class="ow-card">
                    <div class="dash-card__head">
                        <div class="ul-panel-icon ul-panel-icon--blue">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none">
                                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                <polyline points="9 22 9 12 15 12 15 22" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <span class="ul-panel-title">{{ __('All Units') }}</span>
                    </div>

                    <div class="table-responsive">
                        <table id="allDataTable" class="ul-table table responsive theme-border p-20">
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
                                    <td style="font-weight:600;color:var(--gray-800);">{{ $unit->unit_name }}</td>
                                    <td>
                                        <img class="ul-unit-thumb" src="{{ $unit->first_image_url }}" alt="">
                                    </td>
                                    <td>{{ $unit->property_name }}</td>
                                    <td>
                                        @if ($unit->first_name)
                                            <span class="ow-badge ow-badge--paid">{{ $unit->first_name }} {{ $unit->last_name }}</span>
                                        @else
                                            <span class="ow-badge ow-badge--grey">{{ __('Not Available') }}</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="ul-actions">
                                            @if (is_null($unit->first_name))
                                                <button class="ul-action-btn ul-action-btn--delete deleteItem"
                                                        data-formid="delete_row_form_{{ $unit->id }}"
                                                        title="{{ __('Delete') }}">
                                                    <span class="iconify" data-icon="ep:delete-filled"></span>
                                                </button>
                                                <form action="{{ route('owner.property.unit.delete', [$unit->id]) }}"
                                                      method="post"
                                                      id="delete_row_form_{{ $unit->id }}">
                                                    {{ method_field('DELETE') }}
                                                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                                </form>
                                            @endif
                                            <button type="button"
                                                    class="ul-action-btn ul-action-btn--edit unit-edit"
                                                    data-detailsurl="{{ route('owner.property.unit.details', $unit->id) }}"
                                                    title="{{ __('Edit') }}">
                                                <span class="iconify" data-icon="clarity:note-edit-solid"></span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="ul-pagination">{{ $units->links() }}</div>
                </div>

            </div>
        </div>
    </div>
</div>

{{-- ── Edit Unit Modal ──────────────────────────────────────────── --}}
<div class="modal fade edit_modal" id="editUnitModal" tabindex="-1" aria-labelledby="editUnitModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content ul-modal">

            <div class="ul-modal__head">
                <div>
                    <div class="ul-modal__eyebrow">{{ __('Unit') }}</div>
                    <h4 class="ul-modal__title" id="editUnitModalLabel">{{ __('Edit Unit') }}</h4>
                </div>
                <button type="button" class="ul-modal__close" data-bs-dismiss="modal" aria-label="Close">
                    <span class="iconify" data-icon="akar-icons:cross"></span>
                </button>
            </div>

            <form class="ajax" action="{{ route('owner.property.unit.edit') }}" method="post" data-handler="getShowMessage">
                @csrf
                <input type="hidden" name="property_id" class="d-none property_id" value="">

                <div class="ul-modal__body">

                    {{-- Image upload --}}
                    <div class="ul-img-upload-wrap mb-4">
                        <div class="profile-user position-relative d-inline-block">
                            <img id="unit-image" src="" class="ul-unit-avatar rounded-circle avatar-xl default-user-profile-image">
                            <div class="avatar-xs p-0 rounded-circle default-profile-photo-edit">
                                <input id="default-profile-img-file-input" type="file" name="unit_image" class="default-profile-img-file-input">
                                <label for="default-profile-img-file-input" class="default-profile-photo-edit avatar-xs">
                                    <span class="avatar-title rounded-circle" title="Change Image">
                                        <i class="ri-camera-fill"></i>
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <input type="hidden" name="unit_id" value="">

                    {{-- Field grid --}}
                    <div class="ul-field-grid">
                        <div class="ul-field">
                            <label class="ul-field__label">{{ __('Unit Name') }}</label>
                            <input type="text" name="unit_name" class="ul-field__input multiple-unit_name" placeholder="{{ __('Unit Name') }}">
                        </div>
                        <div class="ul-field">
                            <label class="ul-field__label">{{ __('Bedroom') }}</label>
                            <input type="number" min="0" name="bedroom" class="ul-field__input multiple-bedroom" placeholder="0">
                        </div>
                        <div class="ul-field">
                            <label class="ul-field__label">{{ __('Baths') }}</label>
                            <input type="number" min="0" name="bath" class="ul-field__input multiple-bath" placeholder="0">
                        </div>
                        <div class="ul-field">
                            <label class="ul-field__label">{{ __('Kitchen') }}</label>
                            <input type="number" min="0" name="kitchen" class="ul-field__input multiple-kitchen" placeholder="0">
                        </div>
                        <div class="ul-field">
                            <label class="ul-field__label">{{ __('Square Feet') }}</label>
                            <input type="text" name="square_feet" class="ul-field__input multiple-square_feet" placeholder="{{ __('Square Feet') }}">
                        </div>
                        <div class="ul-field">
                            <label class="ul-field__label">{{ __('Amenities') }}</label>
                            <input type="text" name="amenities" class="ul-field__input multiple-amenities" placeholder="{{ __('Amenities') }}">
                        </div>
                        <div class="ul-field">
                            <label class="ul-field__label">{{ __('Condition') }}</label>
                            <input type="text" name="condition" class="ul-field__input multiple-condition" placeholder="{{ __('Condition') }}">
                        </div>
                        <div class="ul-field">
                            <label class="ul-field__label">{{ __('Parking') }}</label>
                            <input type="text" name="parking" class="ul-field__input multiple-parking" placeholder="{{ __('Parking') }}">
                        </div>
                        <div class="ul-field">
                            <label class="ul-field__label">{{ __('General Rent') }}</label>
                            <input type="number" name="general_rent" id="general_rent" class="ul-field__input" placeholder="">
                        </div>
                        <div class="ul-field">
                            <label class="ul-field__label">{{ __('Security Deposit') }}</label>
                            <div class="ul-split-input">
                                <select id="security_deposit_type" name="security_deposit_type" class="ul-split-input__select">
                                    <option value="0">{{ __('Fixed') }}</option>
                                    <option value="1">{{ __('Percentage') }}</option>
                                </select>
                                <input type="number" name="security_deposit" id="security_deposit" class="ul-split-input__input" placeholder="">
                            </div>
                        </div>
                        <div class="ul-field">
                            <label class="ul-field__label">{{ __('Late Fee') }}</label>
                            <div class="ul-split-input">
                                <select id="late_fee_type" name="late_fee_type" class="ul-split-input__select">
                                    <option value="0">{{ __('Fixed') }}</option>
                                    <option value="1">{{ __('Percentage') }}</option>
                                </select>
                                <input type="number" name="late_fee" id="late_fee" class="ul-split-input__input" placeholder="">
                            </div>
                        </div>
                        <div class="ul-field">
                            <label class="ul-field__label">{{ __('Incident Receipt') }}</label>
                            <input type="text" name="incident_receipt" id="incident_receipt" class="ul-field__input" placeholder="">
                        </div>
                    </div>

                    {{-- Rent type tabs --}}
                    <div class="ul-rent-section">
                        <div class="ul-field__label mb-2">{{ __('Rent Type') }}</div>
                        <div class="ul-tab-bar mb-3" id="unitTypeDateChangeTab" role="tablist">
                            <button class="ul-tab nav-link select_rent_type" data-rent_type="1" data-id=""
                                    id="monthly-unit-block-tab" data-bs-toggle="tab"
                                    data-bs-target="#monthly-unit-block-tab-pane"
                                    type="button" role="tab" aria-controls="monthly-unit-block-tab-pane" aria-selected="true">
                                <span class="select-property-nav-text d-flex align-items-center position-relative">
                                    <span class="select-property-nav-text-box me-2"></span>{{ __('Monthly') }}
                                </span>
                            </button>
                            <button class="ul-tab nav-link select_rent_type" data-rent_type="2" data-id=""
                                    id="yearly-unit-block-tab" data-bs-toggle="tab"
                                    data-bs-target="#yearly-unit-block-tab-pane"
                                    type="button" role="tab" aria-controls="yearly-unit-block-tab-pane" aria-selected="false">
                                <span class="select-property-nav-text d-flex align-items-center position-relative">
                                    <span class="select-property-nav-text-box me-2"></span>{{ __('Yearly') }}
                                </span>
                            </button>
                            <button class="ul-tab nav-link select_rent_type" data-rent_type="3" data-id=""
                                    id="custom-unit-block-tab" data-bs-toggle="tab"
                                    data-bs-target="#custom-unit-block-tab-pane"
                                    type="button" role="tab" aria-controls="custom-unit-block-tab-pane" aria-selected="false">
                                <span class="select-property-nav-text d-flex align-items-center position-relative">
                                    <span class="select-property-nav-text-box me-2"></span>{{ __('Custom') }}
                                </span>
                            </button>
                        </div>
                        <input type="hidden" name="rent_type" value="" id="rent_type">

                        <div class="tab-content" id="unitTypeDateChangeTabContent">
                            <div class="tab-pane fade" id="monthly-unit-block-tab-pane" role="tabpanel" aria-labelledby="monthly-unit-block-tab" tabindex="0">
                                <div class="ul-field" style="max-width:260px;">
                                    <label class="ul-field__label">{{ __('Due Day') }}</label>
                                    <input type="number" step="any" min="0" name="monthly_due_day" class="ul-field__input" placeholder="{{ __('Day of month: 1 to 30') }}">
                                </div>
                            </div>
                            <div class="tab-pane fade" id="yearly-unit-block-tab-pane" role="tabpanel" aria-labelledby="yearly-unit-block-tab" tabindex="0">
                                <div class="ul-field" style="max-width:260px;">
                                    <label class="ul-field__label">{{ __('Due Month') }}</label>
                                    <input type="number" step="any" min="0" name="yearly_due_day" class="ul-field__input" placeholder="{{ __('Month of year: 1 to 12') }}">
                                </div>
                            </div>
                            <div class="tab-pane fade" id="custom-unit-block-tab-pane" role="tabpanel" aria-labelledby="custom-unit-block-tab" tabindex="0">
                                <div class="ul-field-grid ul-field-grid--3">
                                    <div class="ul-field">
                                        <label class="ul-field__label">{{ __('Lease Start Date') }}</label>
                                        <div class="custom-datepicker">
                                            <div class="custom-datepicker-inner position-relative">
                                                <input type="text" name="lease_start_date" class="datepicker ul-field__input" autocomplete="off" placeholder="dd-mm-yy">
                                                <i class="ri-calendar-2-line"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="ul-field">
                                        <label class="ul-field__label">{{ __('Lease End Date') }}</label>
                                        <div class="custom-datepicker">
                                            <div class="custom-datepicker-inner position-relative">
                                                <input type="text" name="lease_end_date" class="datepicker ul-field__input" autocomplete="off" placeholder="dd-mm-yy">
                                                <i class="ri-calendar-2-line"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="ul-field">
                                        <label class="ul-field__label">{{ __('Payment Due Date') }}</label>
                                        <div class="custom-datepicker">
                                            <div class="custom-datepicker-inner position-relative">
                                                <input type="text" name="lease_payment_due_date" class="datepicker ul-field__input" autocomplete="off" placeholder="dd-mm-yy">
                                                <i class="ri-calendar-2-line"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="ul-field mt-3" style="max-width:420px;">
                        <label class="ul-field__label">{{ __('Description') }}</label>
                        <input type="text" name="description" class="ul-field__input multiple-description" placeholder="{{ __('Description') }}">
                    </div>
                </div>

                <div class="ul-modal__foot">
                    <button type="button" class="ul-btn ul-btn--ghost" data-bs-dismiss="modal" title="{{ __('Back') }}">{{ __('Back') }}</button>
                    <button type="submit" class="ul-btn ul-btn--primary" title="{{ __('Update') }}">{{ __('Update') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ── Add Unit Modal ───────────────────────────────────────────── --}}
<div class="modal fade add_modal" id="addUnitModal" tabindex="-1" aria-labelledby="addUnitModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content ul-modal">

            <div class="ul-modal__head">
                <div>
                    <div class="ul-modal__eyebrow">{{ __('Property') }}</div>
                    <h4 class="ul-modal__title" id="addUnitModalLabel">{{ __('Add Unit') }}</h4>
                </div>
                <button type="button" class="ul-modal__close" data-bs-dismiss="modal" aria-label="Close">
                    <span class="iconify" data-icon="akar-icons:cross"></span>
                </button>
            </div>

            <form class="ajax" action="{{ route('owner.property.unit.edit') }}" method="post" data-handler="getShowMessage">
                @csrf

                <div class="ul-modal__body">

                    {{-- Image upload --}}
                    <div class="ul-img-upload-wrap mb-4">
                        <div class="profile-user position-relative d-inline-block">
                            <img id="unit-image" src="{{ asset('assets/images/no-image.jpg') }}" class="ul-unit-avatar rounded-circle avatar-xl default-unit-profile-image">
                            <div class="avatar-xs p-0 rounded-circle default-profile-photo-edit">
                                <input id="default-unit-img-file-input" type="file" name="unit_image" class="default-unit-img-file-input">
                                <label for="default-unit-img-file-input" class="default-profile-photo-edit avatar-xs">
                                    <span class="avatar-title rounded-circle" title="Change Image">
                                        <i class="ri-camera-fill"></i>
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <input type="hidden" name="unit_id" value="">

                    {{-- Property select (add only) --}}
                    <div class="ul-field mb-4" style="max-width:340px;">
                        <label class="ul-field__label">{{ __('Property') }}</label>
                        <select class="ul-field__input property_id" name="property_id">
                            <option value="">--{{ __('Select Property') }}--</option>
                            @foreach ($properties as $property)
                                <option value="{{ $property->id }}">{{ $property->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Field grid --}}
                    <div class="ul-field-grid">
                        <div class="ul-field">
                            <label class="ul-field__label">{{ __('Unit Name') }}</label>
                            <input type="text" name="unit_name" class="ul-field__input multiple-unit_name" placeholder="{{ __('Unit Name') }}">
                        </div>
                        <div class="ul-field">
                            <label class="ul-field__label">{{ __('Bedroom') }}</label>
                            <input type="number" min="0" name="bedroom" class="ul-field__input multiple-bedroom" placeholder="0">
                        </div>
                        <div class="ul-field">
                            <label class="ul-field__label">{{ __('Baths') }}</label>
                            <input type="number" min="0" name="bath" class="ul-field__input multiple-bath" placeholder="0">
                        </div>
                        <div class="ul-field">
                            <label class="ul-field__label">{{ __('Kitchen') }}</label>
                            <input type="number" min="0" name="kitchen" class="ul-field__input multiple-kitchen" placeholder="0">
                        </div>
                        <div class="ul-field">
                            <label class="ul-field__label">{{ __('Square Feet') }}</label>
                            <input type="text" name="square_feet" class="ul-field__input multiple-square_feet" placeholder="{{ __('Square Feet') }}">
                        </div>
                        <div class="ul-field">
                            <label class="ul-field__label">{{ __('Amenities') }}</label>
                            <input type="text" name="amenities" class="ul-field__input multiple-amenities" placeholder="{{ __('Amenities') }}">
                        </div>
                        <div class="ul-field">
                            <label class="ul-field__label">{{ __('Condition') }}</label>
                            <input type="text" name="condition" class="ul-field__input multiple-condition" placeholder="{{ __('Condition') }}">
                        </div>
                        <div class="ul-field">
                            <label class="ul-field__label">{{ __('Parking') }}</label>
                            <input type="text" name="parking" class="ul-field__input multiple-parking" placeholder="{{ __('Parking') }}">
                        </div>
                        <div class="ul-field">
                            <label class="ul-field__label">{{ __('General Rent') }}</label>
                            <input type="number" name="general_rent" id="general_rent" class="ul-field__input" placeholder="">
                        </div>
                        <div class="ul-field">
                            <label class="ul-field__label">{{ __('Security Deposit') }}</label>
                            <div class="ul-split-input">
                                <select id="security_deposit_type" name="security_deposit_type" class="ul-split-input__select">
                                    <option value="0">{{ __('Fixed') }}</option>
                                    <option value="1">{{ __('Percentage') }}</option>
                                </select>
                                <input type="number" name="security_deposit" id="security_deposit" class="ul-split-input__input" placeholder="">
                            </div>
                        </div>
                        <div class="ul-field">
                            <label class="ul-field__label">{{ __('Late Fee') }}</label>
                            <div class="ul-split-input">
                                <select id="late_fee_type" name="late_fee_type" class="ul-split-input__select">
                                    <option value="0">{{ __('Fixed') }}</option>
                                    <option value="1">{{ __('Percentage') }}</option>
                                </select>
                                <input type="number" name="late_fee" id="late_fee" class="ul-split-input__input" placeholder="">
                            </div>
                        </div>
                        <div class="ul-field">
                            <label class="ul-field__label">{{ __('Incident Receipt') }}</label>
                            <input type="text" name="incident_receipt" id="incident_receipt" class="ul-field__input" placeholder="">
                        </div>
                    </div>

                    {{-- Rent type tabs --}}
                    <div class="ul-rent-section">
                        <div class="ul-field__label mb-2">{{ __('Rent Type') }}</div>
                        <div class="ul-tab-bar mb-3" id="unitTypeDateChangeTab" role="tablist">
                            <button class="ul-tab nav-link add-select_rent_type" data-add-rent_type="1" data-id=""
                                    id="monthly-add-unit-block-tab" data-bs-toggle="tab"
                                    data-bs-target="#monthly-add-unit-block-tab-pane"
                                    type="button" role="tab" aria-controls="monthly-add-unit-block-tab-pane" aria-selected="true">
                                <span class="select-property-nav-text d-flex align-items-center position-relative">
                                    <span class="select-property-nav-text-box me-2"></span>{{ __('Monthly') }}
                                </span>
                            </button>
                            <button class="ul-tab nav-link add-select_rent_type" data-add-rent_type="2" data-id=""
                                    id="yearly-add-unit-block-tab" data-bs-toggle="tab"
                                    data-bs-target="#yearly-add-unit-block-tab-pane"
                                    type="button" role="tab" aria-controls="yearly-add-unit-block-tab-pane" aria-selected="false">
                                <span class="select-property-nav-text d-flex align-items-center position-relative">
                                    <span class="select-property-nav-text-box me-2"></span>{{ __('Yearly') }}
                                </span>
                            </button>
                            <button class="ul-tab nav-link add-select_rent_type" data-add-rent_type="3" data-id=""
                                    id="custom-add-unit-block-tab" data-bs-toggle="tab"
                                    data-bs-target="#custom-add-unit-block-tab-pane"
                                    type="button" role="tab" aria-controls="custom-add-unit-block-tab-pane" aria-selected="false">
                                <span class="select-property-nav-text d-flex align-items-center position-relative">
                                    <span class="select-property-nav-text-box me-2"></span>{{ __('Custom') }}
                                </span>
                            </button>
                        </div>
                        <input type="hidden" name="rent_type" value="" id="add_rent_type">

                        <div class="tab-content" id="unitTypeDateChangeTabContent">
                            <div class="tab-pane fade add-tab-pane" id="monthly-add-unit-block-tab-pane" role="tabpanel" aria-labelledby="monthly-add-unit-block-tab" tabindex="0">
                                <div class="ul-field" style="max-width:260px;">
                                    <label class="ul-field__label">{{ __('Due Day') }}</label>
                                    <input type="number" step="any" min="0" name="monthly_due_day" class="ul-field__input" placeholder="{{ __('Day of month: 1 to 30') }}">
                                </div>
                            </div>
                            <div class="tab-pane fade add-tab-pane" id="yearly-add-unit-block-tab-pane" role="tabpanel" aria-labelledby="yearly-add-unit-block-tab" tabindex="0">
                                <div class="ul-field" style="max-width:260px;">
                                    <label class="ul-field__label">{{ __('Due Month') }}</label>
                                    <input type="number" step="any" min="0" name="yearly_due_day" class="ul-field__input" placeholder="{{ __('Month of year: 1 to 12') }}">
                                </div>
                            </div>
                            <div class="tab-pane fade add-tab-pane" id="custom-add-unit-block-tab-pane" role="tabpanel" aria-labelledby="custom-add-unit-block-tab" tabindex="0">
                                <div class="ul-field-grid ul-field-grid--3">
                                    <div class="ul-field">
                                        <label class="ul-field__label">{{ __('Lease Start Date') }}</label>
                                        <div class="custom-datepicker">
                                            <div class="custom-datepicker-inner position-relative">
                                                <input type="text" name="lease_start_date" class="datepicker ul-field__input" autocomplete="off" placeholder="dd-mm-yy">
                                                <i class="ri-calendar-2-line"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="ul-field">
                                        <label class="ul-field__label">{{ __('Lease End Date') }}</label>
                                        <div class="custom-datepicker">
                                            <div class="custom-datepicker-inner position-relative">
                                                <input type="text" name="lease_end_date" class="datepicker ul-field__input" autocomplete="off" placeholder="dd-mm-yy">
                                                <i class="ri-calendar-2-line"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="ul-field">
                                        <label class="ul-field__label">{{ __('Payment Due Date') }}</label>
                                        <div class="custom-datepicker">
                                            <div class="custom-datepicker-inner position-relative">
                                                <input type="text" name="lease_payment_due_date" class="datepicker ul-field__input" autocomplete="off" placeholder="dd-mm-yy">
                                                <i class="ri-calendar-2-line"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="ul-field mt-3" style="max-width:420px;">
                        <label class="ul-field__label">{{ __('Description') }}</label>
                        <input type="text" name="description" class="ul-field__input multiple-description" placeholder="{{ __('Description') }}">
                    </div>
                </div>

                <div class="ul-modal__foot">
                    <button type="button" class="ul-btn ul-btn--ghost" data-bs-dismiss="modal" title="{{ __('Back') }}">{{ __('Back') }}</button>
                    <button type="submit" class="ul-btn ul-btn--primary" title="{{ __('Create Unit') }}">{{ __('Create Unit') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('style')
<style>
/* ── Tokens ───────────────────────────────────────────────────── */
:root {
    --blue:        #185FA5;
    --blue-hover:  #0F4A84;
    --blue-light:  #E6F1FB;
    --blue-border: #B5D4F4;
    --blue-faint:  #185ea56e;
    --green:       #1D9E75;
    --green-dark:  #0F6E56;
    --green-light: #E1F5EE;
    --amber:       #854F0B;
    --amber-light: #FAEEDA;
    --amber-border:#F5D9A8;
    --red:         #993C1D;
    --red-light:   #FAECE7;
    --purple:      #534AB7;
    --gray-900:    #111827;
    --gray-800:    #1f2937;
    --gray-700:    #374151;
    --gray-500:    #6b7280;
    --gray-400:    #9ca3af;
    --gray-200:    #e5e7eb;
    --gray-100:    #f3f4f6;
    --gray-50:     #fafafa;
    --white:       #ffffff;
}

/* ── Page header ──────────────────────────────────────────────── */
.ul-page-header {
    display:flex; align-items:flex-start; justify-content:space-between;
    flex-wrap:wrap; gap:1rem; margin-bottom:1.5rem;
}
.ul-page-title { font-size:22px; font-weight:500; color:var(--gray-900); margin:0 0 6px; }
.ul-breadcrumb {
    display:flex; align-items:center; gap:6px; list-style:none;
    padding:0; margin:0; font-size:12px; color:var(--gray-400);
}
.ul-breadcrumb li { display:flex; align-items:center; gap:6px; }
.ul-breadcrumb a  { color:var(--blue); font-weight:500; text-decoration:none; }

/* ── Buttons ──────────────────────────────────────────────────── */
.ul-btn {
    display:inline-flex; align-items:center; gap:6px;
    font-size:12px; font-weight:500; padding:7px 15px;
    border-radius:7px; border:none; cursor:pointer;
    text-decoration:none; transition:all .13s; white-space:nowrap;
}
.ul-btn--primary { background:var(--blue); color:var(--white); }
.ul-btn--primary:hover { background:var(--blue-hover); color:var(--white); transform:translateY(-1px); }
.ul-btn--ghost {
    background:var(--gray-100); color:var(--gray-700);
    border:0.5px solid var(--gray-200);
}
.ul-btn--ghost:hover { background:var(--gray-200); }

/* ── Primary button ───────────────────────────────────── */
    .theme-btn-primary {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: #185FA5;
        color: #fff;
        padding: 10px 18px;
        border-radius: 8px;
        font-weight: 500;
        font-size: 13px;
        text-decoration: none;
        transition: background .15s, transform .15s, box-shadow .15s;
    }
    .theme-btn-primary:hover {
        background: #0F4A84;
        color: #fff;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(24,95,165,.25);
    }

    /* Upgrade button */
    .upgrade-btn {
        background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%) !important;
        border: 0.5px solid #D97706 !important;
        position: relative;
        overflow: hidden;
    }
    
    .upgrade-btn:hover {
        background: linear-gradient(135deg, #D97706 0%, #B45309 100%) !important;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
    }
    
    .upgrade-btn::before {
        content: '';
        position: absolute;
        top: -2px;
        right: -2px;
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #FEF3C7;
        animation: upgradePulse 1.5s infinite;
    }
    
    @keyframes upgradePulse {
        0%, 100% { opacity: 0.6; transform: scale(1); }
        50% { opacity: 1; transform: scale(1.3); }
    }
    
    /* Near limit button */
    .near-limit-btn {
        position: relative;
    }
    
    .remaining-badge {
        display: inline-flex;
        align-items: center;
        font-size: 10px;
        font-weight: 600;
        padding: 1px 6px;
        border-radius: 99px;
        background: rgba(255,255,255,0.25);
        margin-left: 6px;
        letter-spacing: 0.02em;
    }

/* ── Outer card ───────────────────────────────────────────────── */
.ow-card {
    background:var(--white);
    border:0.5px solid var(--blue-faint);
    border-radius:12px; overflow:hidden;
    box-shadow: 0 4px 12px rgba(0,0,0,.04),
                0 0 0 1px rgba(24,95,165,.05),
                0 6px 18px rgba(24,95,165,.06);
}

/* ── Card head ────────────────────────────────────────────────── */
.dash-card__head {
    display:flex; align-items:center; gap:10px;
    padding:.75rem 1.1rem; border-bottom:0.5px solid var(--gray-200);
    background:var(--gray-50);
}
.ul-panel-icon {
    width:28px; height:28px; border-radius:7px;
    display:inline-flex; align-items:center; justify-content:center; flex-shrink:0;
}
.ul-panel-icon--blue { background:var(--blue-light); color:var(--blue); }
.ul-panel-title { font-size:14px; font-weight:500; color:var(--gray-900); }

/* ── Table ────────────────────────────────────────────────────── */
.ul-table { width:100%; border-collapse:collapse; font-size:13px; }
.ul-table thead { background:var(--gray-50); border-bottom:0.5px solid var(--gray-200); }
.ul-table th {
    padding:.65rem 1rem; font-size:10px; font-weight:500;
    text-transform:uppercase; letter-spacing:.07em;
    color:var(--gray-500); white-space:nowrap;
}
.ul-table td {
    padding:.8rem 1rem; border-bottom:0.5px solid var(--gray-100);
    color:var(--gray-700); vertical-align:middle;
}
.ul-table tr:last-child td { border-bottom:none; }
.ul-table tbody tr:nth-child(even) td { background:var(--gray-50); }
.ul-table tbody tr:hover td { background:var(--gray-100); }

.ul-unit-thumb {
    width:36px; height:36px; border-radius:8px;
    object-fit:cover; border:0.5px solid var(--gray-200); display:block;
}

/* ── Action buttons ───────────────────────────────────────────── */
.ul-actions { display:inline-flex; align-items:center; gap:6px; }
.ul-action-btn {
    width:28px; height:28px; border-radius:7px; border:none;
    display:inline-flex; align-items:center; justify-content:center;
    font-size:13px; cursor:pointer; transition:all .13s;
}
.ul-action-btn--edit { background:var(--blue-light); color:var(--blue); }
.ul-action-btn--edit:hover { background:var(--blue); color:var(--white); }
.ul-action-btn--delete { background:var(--red-light); color:var(--red); }
.ul-action-btn--delete:hover { background:var(--red); color:var(--white); }

/* ── Badges ───────────────────────────────────────────────────── */
.ow-badge {
    display:inline-flex; align-items:center; gap:4px;
    font-size:11px; font-weight:500; padding:3px 9px;
    border-radius:99px; white-space:nowrap;
}
.ow-badge--paid { background:var(--green-light); color:var(--green-dark); }
.ow-badge--grey { background:var(--gray-100); color:var(--gray-500); border:0.5px solid var(--gray-200); }

/* ── Pagination ───────────────────────────────────────────────── */
.ul-pagination {
    padding:12px 20px; border-top:0.5px solid var(--gray-200);
    background:var(--gray-50); display:flex; justify-content:flex-end;
}

/* ── Modal shell ──────────────────────────────────────────────── */
.ul-modal { border-radius:14px; overflow:hidden; border:none; }
.ul-modal__head {
    display:flex; align-items:center; justify-content:space-between;
    padding:16px 20px; background:var(--gray-50);
    border-bottom:0.5px solid var(--gray-200);
}
.ul-modal__eyebrow {
    font-size:10px; font-weight:500; text-transform:uppercase;
    letter-spacing:.07em; color:var(--gray-400); margin-bottom:2px;
}
.ul-modal__title { font-size:15px; font-weight:600; color:var(--gray-900); margin:0; }
.ul-modal__close {
    width:30px; height:30px; border-radius:7px; border:none;
    background:var(--gray-100); cursor:pointer;
    display:flex; align-items:center; justify-content:center;
    color:var(--gray-500); transition:background .13s;
}
.ul-modal__close:hover { background:var(--gray-200); }
.ul-modal__body { padding:20px; max-height:70vh; overflow-y:auto; }
.ul-modal__foot {
    display:flex; align-items:center; gap:10px;
    padding:14px 20px; background:var(--gray-50);
    border-top:0.5px solid var(--gray-200);
}

/* ── Image upload ─────────────────────────────────────────────── */
.ul-img-upload-wrap { display:flex; align-items:center; gap:14px; }
.ul-unit-avatar { width:72px; height:72px; object-fit:cover; border:2px solid var(--gray-200); }

/* ── Field grid ───────────────────────────────────────────────── */
.ul-field-grid {
    display:grid; grid-template-columns:repeat(3, 1fr); gap:14px 16px;
    margin-bottom:20px;
}
.ul-field-grid--3 { grid-template-columns:repeat(3, 1fr); }
@media(max-width:640px){
    .ul-field-grid { grid-template-columns:1fr 1fr; }
    .ul-field-grid--3 { grid-template-columns:1fr; }
}
@media(max-width:420px){
    .ul-field-grid { grid-template-columns:1fr; }
}

/* ── Individual field ─────────────────────────────────────────── */
.ul-field { display:flex; flex-direction:column; gap:5px; }
.ul-field__label {
    font-size:10px; font-weight:500; text-transform:uppercase;
    letter-spacing:.07em; color:var(--gray-400);
}
.ul-field__input {
    width:100%; padding:7px 10px;
    border:0.5px solid var(--gray-200); border-radius:7px;
    font-size:13px; color:var(--gray-700); outline:none;
    background:var(--white);
    transition:border-color .15s, box-shadow .15s;
}
.ul-field__input:focus { border-color:var(--blue); box-shadow:0 0 0 3px rgba(24,95,165,.1); }

/* ── Split input (select + number) ───────────────────────────── */
.ul-split-input {
    display:flex; border:0.5px solid var(--gray-200); border-radius:7px;
    overflow:hidden; transition:border-color .15s, box-shadow .15s;
}
.ul-split-input:focus-within { border-color:var(--blue); box-shadow:0 0 0 3px rgba(24,95,165,.1); }
.ul-split-input__select {
    border:none; border-right:0.5px solid var(--gray-200);
    background:var(--gray-50); padding:7px 8px;
    font-size:12px; color:var(--gray-700); outline:none;
    flex-shrink:0; width:90px;
}
.ul-split-input__input {
    border:none; outline:none; flex:1;
    padding:7px 10px; font-size:13px; color:var(--gray-700);
    background:var(--white);
}

/* ── Rent type tab bar ────────────────────────────────────────── */
.ul-rent-section {
    background:var(--gray-50); border:0.5px solid var(--gray-200);
    border-radius:10px; padding:14px 16px; margin-bottom:16px;
}
.ul-tab-bar {
    display:flex; background:var(--gray-100);
    border-radius:8px; padding:3px; gap:2px;
}
.ul-tab {
    font-size:12px; color:var(--gray-500); padding:4px 14px;
    border-radius:6px; border:none; background:transparent;
    cursor:pointer; transition:all .13s; font-weight:500;
}
.ul-tab.active, .ul-tab:focus {
    background:var(--white); color:var(--gray-900);
    box-shadow:0 1px 3px rgba(0,0,0,.08);
}

/* ── Utilities ────────────────────────────────────────────────── */
.mb-4 { margin-bottom:1.5rem; }
.mb-3 { margin-bottom:1rem; }
.mb-2 { margin-bottom:.5rem; }
.mt-3 { margin-top:1rem; }
.text-center { text-align:center; }

/* ── DataTables: search bar ───────────────────────────────────── */

/* Wrapper row: full width, sits between card head and thead */
div.dataTables_filter {
    float: none !important;
    text-align: left !important;
    padding: 10px 16px;
    border-bottom: 0.5px solid var(--gray-200);
    background: var(--white);
}

/* Label becomes a flex row; hide the "Search:" text node via font-size 0
   then re-set size on the input so only the input is visible */
div.dataTables_filter label {
    display: flex !important;
    align-items: center !important;
    margin: 0 !important;
    font-size: 0 !important; /* hides "Search:" text */
    width: 100%;
}

/* The actual input — full-width, icon embedded as bg-image */
div.dataTables_filter input {
    display: block !important;
    width: 100% !important;
    margin: 0 !important;
    padding: 7px 10px 7px 34px !important;
    font-size: 13px !important;
    color: var(--gray-700) !important;
    border: 0.5px solid var(--gray-200) !important;
    border-radius: 7px !important;
    outline: none !important;
    background-color: var(--white) !important;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='%239ca3af' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Ccircle cx='11' cy='11' r='8'/%3E%3Cline x1='21' y1='21' x2='16.65' y2='16.65'/%3E%3C/svg%3E") !important;
    background-repeat: no-repeat !important;
    background-position: 10px center !important;
    background-size: 14px !important;
    transition: border-color .15s, box-shadow .15s;
}
div.dataTables_filter input::placeholder { color: var(--gray-400); }
div.dataTables_filter input:focus {
    border-color: var(--blue) !important;
    box-shadow: 0 0 0 3px rgba(24,95,165,.1) !important;
}

/* Hide duplicate info text and length selector */
div.dataTables_info,
div.dataTables_length { display: none !important; }
</style>
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
                    e = document.querySelector(".default-profile-img-file-input").files[0],
                    i = new FileReader();
                i.addEventListener("load", function () { o.src = i.result; }, !1);
                e && i.readAsDataURL(e);
            });

        document
            .querySelector("#default-unit-img-file-input")
            .addEventListener("change", function () {
                var o = document.querySelector(".default-unit-profile-image"),
                    e = document.querySelector(".default-unit-img-file-input").files[0],
                    i = new FileReader();
                i.addEventListener("load", function () { o.src = i.result; }, !1);
                e && i.readAsDataURL(e);
            });
    </script>
@endpush