{{-- property-information.blade.php --}}
<style>
/* ── Property Information Step Styling ────────────────────── */
.pis-wrap {
    font-size: 13px;
    color: var(--gray-700, #374151);
}

/* ── Limit Alert Banner ──────────────────────────────────── */
.pis-limit-alert {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px 18px;
    border-radius: 10px;
    margin-bottom: 20px;
    border: 0.5px solid;
}

.pis-limit-alert--warning {
    background: #FFFBEB;
    border-color: #FDE68A;
    color: #92400E;
}

.pis-limit-alert--danger {
    background: #FEF2F2;
    border-color: #FECACA;
    color: #991B1B;
}

.pis-limit-alert--info {
    background: #EFF6FF;
    border-color: #BFDBFE;
    color: #1E40AF;
}

.pis-limit-alert__icon {
    width: 36px;
    height: 36px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.pis-limit-alert--warning .pis-limit-alert__icon {
    background: #FDE68A;
    color: #92400E;
}

.pis-limit-alert--danger .pis-limit-alert__icon {
    background: #FECACA;
    color: #991B1B;
}

.pis-limit-alert--info .pis-limit-alert__icon {
    background: #BFDBFE;
    color: #1E40AF;
}

.pis-limit-alert__content {
    flex: 1;
    min-width: 0;
}

.pis-limit-alert__title {
    font-size: 13px;
    font-weight: 600;
    margin: 0 0 2px;
    line-height: 1.3;
}

.pis-limit-alert__text {
    font-size: 11px;
    margin: 0;
    opacity: 0.85;
    line-height: 1.4;
}

.pis-limit-alert__action {
    flex-shrink: 0;
}

.pis-upgrade-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 7px 14px;
    font-size: 11px;
    font-weight: 600;
    border-radius: 7px;
    text-decoration: none;
    transition: all .15s ease;
    white-space: nowrap;
}

.pis-upgrade-btn--warning {
    background: #FDE68A;
    color: #92400E;
    border: 0.5px solid #FCD34D;
}

.pis-upgrade-btn--warning:hover {
    background: #FCD34D;
    color: #78350F;
}

.pis-upgrade-btn--danger {
    background: #FECACA;
    color: #991B1B;
    border: 0.5px solid #FCA5A5;
}

.pis-upgrade-btn--danger:hover {
    background: #FCA5A5;
    color: #7F1D1D;
}

/* ── Unit Input with Counter ─────────────────────────────── */
.pis-unit-input-wrapper {
    position: relative;
}

.pis-unit-counter {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-top: 8px;
    padding: 8px 12px;
    border-radius: 7px;
    font-size: 11px;
    font-weight: 500;
}

.pis-unit-counter--safe {
    background: #F0FDF4;
    color: #166534;
    border: 0.5px solid #BBF7D0;
}

.pis-unit-counter--warning {
    background: #FFFBEB;
    color: #92400E;
    border: 0.5px solid #FDE68A;
}

.pis-unit-counter--danger {
    background: #FEF2F2;
    color: #991B1B;
    border: 0.5px solid #FECACA;
}

.pis-unit-counter__bar {
    flex: 1;
    height: 4px;
    border-radius: 2px;
    background: #E5E7EB;
    overflow: hidden;
}

.pis-unit-counter__fill {
    height: 100%;
    border-radius: 2px;
    transition: width .3s ease, background .3s ease;
}

.pis-unit-counter--safe .pis-unit-counter__fill {
    background: #22C55E;
}

.pis-unit-counter--warning .pis-unit-counter__fill {
    background: #F59E0B;
}

.pis-unit-counter--danger .pis-unit-counter__fill {
    background: #EF4444;
}

.pis-unit-counter__text {
    white-space: nowrap;
    font-weight: 600;
}

/* ── Property Type Cards ──────────────────────────────────── */
.pis-type-cards {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
}

.pis-type-card {
    background: #fff;
    border: 0.5px solid var(--blue-faint, #185ea56e);
    border-radius: 10px;
    padding: 16px;
    cursor: pointer;
    transition: all .18s ease;
    position: relative;
    display: flex;
    align-items: flex-start;
    gap: 12px;
}

.pis-type-card:hover {
    border-color: var(--blue, #185FA5);
    box-shadow: 0 4px 12px rgba(0,0,0,.06);
}

.pis-type-card.is-active {
    border-color: var(--blue, #185FA5);
    border-width: 1.5px;
    background: var(--blue-light, #E6F1FB);
    box-shadow: 0 0 0 1px rgba(24,95,165,.15);
}

.pis-type-card__radio {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    border: 1.5px solid #D1D5DB;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    margin-top: 1px;
    transition: all .18s ease;
}

.pis-type-card.is-active .pis-type-card__radio {
    border-color: var(--blue, #185FA5);
    background: var(--blue, #185FA5);
}

.pis-type-card.is-active .pis-type-card__radio::after {
    content: '';
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #fff;
}

.pis-type-card__content {
    flex: 1;
    min-width: 0;
}

.pis-type-card__title {
    font-size: 13px;
    font-weight: 600;
    color: var(--gray-900, #111827);
    margin: 0 0 2px;
}

.pis-type-card__desc {
    font-size: 11px;
    color: var(--gray-500, #6b7280);
    margin: 0;
    line-height: 1.4;
}

/* ── Form Card ───────────────────────────────────────────── */
.pis-form-card {
    background: #fff;
    border: 0.5px solid var(--blue-faint, #185ea56e);
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0,0,0,.04);
}

.pis-form-card__head {
    padding: 16px 20px;
    border-bottom: 0.5px solid #E5E7EB;
    background: #FAFAFA;
    display: flex;
    align-items: center;
    gap: 10px;
}

.pis-form-card__head-icon {
    width: 28px;
    height: 28px;
    border-radius: 7px;
    background: var(--blue-light, #E6F1FB);
    color: var(--blue, #185FA5);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.pis-form-card__head h4 {
    font-size: 13px;
    font-weight: 600;
    color: var(--gray-900, #111827);
    margin: 0;
}

.pis-form-card__body {
    padding: 20px;
}

/* ── Responsive ──────────────────────────────────────────── */
@media (max-width: 768px) {
    .pis-type-cards {
        grid-template-columns: 1fr;
    }
    
    .pis-limit-alert {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .pis-limit-alert__action {
        width: 100%;
    }
    
    .pis-upgrade-btn {
        width: 100%;
        justify-content: center;
    }
}
</style>

<div class="pis-wrap">
    <form class="ajax" action="{{ route('owner.property.property-information.store') }}" method="post"
        data-handler="stepChange">
        @csrf
        <input type="hidden" name="property_id" class="d-none property_id" value="{{ @$property->id }}">
        <input type="hidden" name="property_type" class="d-none" id="property_type"
            value="{{ @$property->property_type ?? 1 }}">

        {{-- ── Unit Limit Calculations ────────────────────────── --}}
        @php
            $subscriptionService = app(\App\Services\SubscriptionService::class);
            $unitLimit = $subscriptionService->getUnitLimit();
            $remainingUnits = $unitLimit['remaining'] ?? 999;
            $totalUnits = $unitLimit['total'] ?? 0;
            $usedUnits = $unitLimit['used'] ?? 0;
            $hasReachedLimit = $remainingUnits <= 0;
            $isNearLimit = $remainingUnits > 0 && $remainingUnits <= 3;
            $isEditing = isset($property) && $property->id;
            $currentPropertyUnits = $isEditing ? ($property->number_of_unit ?? 0) : 0;
            
            // For edit mode: available units = remaining + current property units
            $editableUnits = $isEditing ? ($remainingUnits + $currentPropertyUnits) : $remainingUnits;
            $editHasReachedLimit = $isEditing && $editableUnits <= 0;
            $editIsNearLimit = $isEditing && $editableUnits > 0 && $editableUnits <= 3;
        @endphp

        {{-- ── Unit Limit Alert Banner ────────────────────────── --}}
        @if(!$isEditing)
            {{-- ADD MODE ALERTS --}}
            @if($hasReachedLimit)
                <div class="pis-limit-alert pis-limit-alert--danger">
                    <div class="pis-limit-alert__icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                            <circle cx="12" cy="12" r="10"/>
                            <path d="M12 8v4M12 16h.01"/>
                        </svg>
                    </div>
                    <div class="pis-limit-alert__content">
                        <p class="pis-limit-alert__title">{{ __('Unit Limit Reached') }}</p>
                        <p class="pis-limit-alert__text">
                            You have used all <strong>{{ $usedUnits }}</strong> of <strong>{{ $totalUnits }}</strong> allowed units. Upgrade your plan to add more properties.
                        </p>
                    </div>
                    <div class="pis-limit-alert__action">
                        <a href="{{ route('owner.subscription.index', ['current_plan' => 'no']) }}" class="pis-upgrade-btn pis-upgrade-btn--danger">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                                <polyline points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>
                            </svg>
                            {{ __('Upgrade Plan') }}
                        </a>
                    </div>
                </div>
            @elseif($isNearLimit)
                <div class="pis-limit-alert pis-limit-alert--warning">
                    <div class="pis-limit-alert__icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                            <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                            <path d="M12 9v4M12 17h.01"/>
                        </svg>
                    </div>
                    <div class="pis-limit-alert__content">
                        <p class="pis-limit-alert__title">{{ __('Limited Units Remaining') }}</p>
                        <p class="pis-limit-alert__text">
                            You only have <strong>{{ $remainingUnits }}</strong> unit(s) left from your <strong>{{ $totalUnits }}</strong> unit limit. Consider upgrading soon.
                        </p>
                    </div>
                    <div class="pis-limit-alert__action">
                        <a href="{{ route('owner.subscription.index', ['current_plan' => 'no']) }}" class="pis-upgrade-btn pis-upgrade-btn--warning">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                                <polyline points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>
                            </svg>
                            {{ __('View Plans') }}
                        </a>
                    </div>
                </div>
            @else
                <div class="pis-limit-alert pis-limit-alert--info">
                    <div class="pis-limit-alert__icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                            <circle cx="12" cy="12" r="10"/>
                            <path d="M12 16v-4M12 8h.01"/>
                        </svg>
                    </div>
                    <div class="pis-limit-alert__content">
                        <p class="pis-limit-alert__title">{{ __('Units Available') }}</p>
                        <p class="pis-limit-alert__text">
                            You can add up to <strong>{{ $remainingUnits }}</strong> more unit(s). Your plan allows <strong>{{ $totalUnits }}</strong> units total.
                        </p>
                    </div>
                </div>
            @endif
        @else
            {{-- EDIT MODE ALERTS --}}
            @if($editHasReachedLimit)
                <div class="pis-limit-alert pis-limit-alert--danger">
                    <div class="pis-limit-alert__icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                            <circle cx="12" cy="12" r="10"/>
                            <path d="M12 8v4M12 16h.01"/>
                        </svg>
                    </div>
                    <div class="pis-limit-alert__content">
                        <p class="pis-limit-alert__title">{{ __('Unit Limit Exceeded') }}</p>
                        <p class="pis-limit-alert__text">
                            This property has <strong>{{ $currentPropertyUnits }}</strong> units but your plan only allows <strong>{{ $totalUnits }}</strong> total. You cannot add more units. Upgrade your plan or reduce existing units.
                        </p>
                    </div>
                    <div class="pis-limit-alert__action">
                        <a href="{{ route('owner.subscription.index', ['current_plan' => 'no']) }}" class="pis-upgrade-btn pis-upgrade-btn--danger">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                                <polyline points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>
                            </svg>
                            {{ __('Upgrade Plan') }}
                        </a>
                    </div>
                </div>
            @elseif($editIsNearLimit)
                <div class="pis-limit-alert pis-limit-alert--warning">
                    <div class="pis-limit-alert__icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                            <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                            <path d="M12 9v4M12 17h.01"/>
                        </svg>
                    </div>
                    <div class="pis-limit-alert__content">
                        <p class="pis-limit-alert__title">{{ __('Editing — Limited Units Available') }}</p>
                        <p class="pis-limit-alert__text">
                            This property currently has <strong>{{ $currentPropertyUnits }}</strong> units. You can add up to <strong>{{ $editableUnits }}</strong> units total ({{ $remainingUnits }} remaining in your plan + {{ $currentPropertyUnits }} existing).
                        </p>
                    </div>
                    <div class="pis-limit-alert__action">
                        <a href="{{ route('owner.subscription.index', ['current_plan' => 'no']) }}" class="pis-upgrade-btn pis-upgrade-btn--warning">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                                <polyline points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>
                            </svg>
                            {{ __('View Plans') }}
                        </a>
                    </div>
                </div>
            @else
                <div class="pis-limit-alert pis-limit-alert--info">
                    <div class="pis-limit-alert__icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                            <circle cx="12" cy="12" r="10"/>
                            <path d="M12 16v-4M12 8h.01"/>
                        </svg>
                    </div>
                    <div class="pis-limit-alert__content">
                        <p class="pis-limit-alert__title">{{ __('Editing Property') }}</p>
                        <p class="pis-limit-alert__text">
                            This property has <strong>{{ $currentPropertyUnits }}</strong> units. You can increase up to <strong>{{ $editableUnits }}</strong> units ({{ $remainingUnits }} remaining + {{ $currentPropertyUnits }} existing). Plan limit: <strong>{{ $totalUnits }}</strong>.
                        </p>
                    </div>
                </div>
            @endif
        @endif

        {{-- ── Property Type Selection ─────────────────────────── --}}
        <div class="pis-form-card mb-25">
            <div class="pis-form-card__head">
                <div class="pis-form-card__head-icon">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="3" width="18" height="18" rx="3"/>
                        <path d="M9 3v18M3 9h18"/>
                    </svg>
                </div>
                <h4>{{ __('Property Type') }}</h4>
            </div>
            <div class="pis-form-card__body">
                <div class="pis-type-cards">
                    <div class="pis-type-card {{ @$property->property_type ? ($property->property_type == 1 ? 'is-active' : '') : 'is-active' }}"
                        data-property_type="1" id="own-property-card">
                        <div class="pis-type-card__radio"></div>
                        <div class="pis-type-card__content">
                            <p class="pis-type-card__title">{{ __('Own Property') }}</p>
                            <p class="pis-type-card__desc">{{ __('You own this property outright') }}</p>
                        </div>
                    </div>
                    <div class="pis-type-card {{ @$property->property_type == 2 ? 'is-active' : '' }}"
                        data-property_type="2" id="lease-property-card">
                        <div class="pis-type-card__radio"></div>
                        <div class="pis-type-card__content">
                            <p class="pis-type-card__title">{{ __('Lease Property') }}</p>
                            <p class="pis-type-card__desc">{{ __('You lease this property from someone else') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Property Details Form ───────────────────────────── --}}
        <div class="pis-form-card">
            <div class="pis-form-card__head">
                <div class="pis-form-card__head-icon">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 2L2 7l10 5 10-5-10-5z"/>
                        <path d="M2 17l10 5 10-5"/>
                        <path d="M2 12l10 5 10-5"/>
                    </svg>
                </div>
                <h4>{{ __('Property Details') }}</h4>
            </div>
            <div class="pis-form-card__body">
                {{-- Own Property Form --}}
                <div class="{{ @$property->property_type == 2 ? 'd-none' : '' }}" id="own-property-form">
                    <div class="row">
                        <div class="col-md-6 mb-25">
                            <label class="label-text-title color-heading font-medium mb-2">
                                {{ __('Property Name') }} <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" name="own_property_name"
                                placeholder="{{ __('Enter property name') }}"
                                value="{{ @$property->property_type ? ($property->property_type == 1 ? $property->name : '') : '' }}">
                        </div>
                        <div class="col-md-6 mb-25">
                            <label class="label-text-title color-heading font-medium mb-2">
                                {{ __('Number of Units') }} <span class="text-danger">*</span>
                            </label>
                            @if(!$isEditing && $hasReachedLimit)
                                <input type="number" min="1" class="form-control" name="own_number_of_unit"
                                    value="0" disabled
                                    placeholder="{{ __('No units available') }}">
                            @elseif($isEditing && $editHasReachedLimit)
                                <input type="number" min="{{ $currentPropertyUnits }}" max="{{ $currentPropertyUnits }}" 
                                    class="form-control" name="own_number_of_unit"
                                    value="{{ $currentPropertyUnits }}" disabled
                                    placeholder="{{ __('Cannot add more units') }}">
                                <small class="text-danger mt-1 d-block">
                                    {{ __('Unit limit reached. You cannot add more units to this property.') }}
                                </small>
                            @else
                                <div class="pis-unit-input-wrapper">
                                    <input type="number" min="1" 
                                        max="{{ $isEditing ? $editableUnits : $remainingUnits }}"
                                        class="form-control unit-count-input" 
                                        name="own_number_of_unit"
                                        id="own_number_of_unit"
                                        value="{{ @$property->property_type ? ($property->property_type == 1 ? $property->number_of_unit : '') : '' }}"
                                        placeholder="{{ __('Enter number of units') }}"
                                        data-remaining="{{ $isEditing ? $editableUnits : $remainingUnits }}"
                                        data-used="{{ $isEditing ? ($usedUnits - $currentPropertyUnits) : $usedUnits }}"
                                        data-total="{{ $totalUnits }}"
                                        data-is-editing="{{ $isEditing ? 'true' : 'false' }}"
                                        data-current-units="{{ $currentPropertyUnits }}">
                                    <div class="pis-unit-counter pis-unit-counter--safe" id="unit-counter">
                                        <div class="pis-unit-counter__bar">
                                            <div class="pis-unit-counter__fill" style="width: {{ $totalUnits > 0 ? (($isEditing ? ($usedUnits - $currentPropertyUnits) : $usedUnits) / $totalUnits * 100) : 0 }}%"></div>
                                        </div>
                                        <span class="pis-unit-counter__text">
                                            @if($isEditing)
                                                {{ $editableUnits }} {{ __('max allowed') }}
                                            @else
                                                {{ $remainingUnits }} {{ __('remaining') }}
                                            @endif
                                        </span>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mb-25">
                            <label class="label-text-title color-heading font-medium mb-2">
                                {{ __('Description') }}
                            </label>
                            <textarea class="form-control" name="own_description" rows="3" 
                                placeholder="{{ __('Describe the property...') }}">{{ @$property->property_type ? ($property->property_type == 1 ? $property->description : '') : '' }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- Lease Property Form --}}
                <div class="{{ @$property->property_type != 2 ? 'd-none' : '' }}" id="lease-property-form">
                    <div class="row">
                        <div class="col-md-4 mb-25">
                            <label class="label-text-title color-heading font-medium mb-2">
                                {{ __('Property Name') }} <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" name="lease_property_name"
                                placeholder="{{ __('Enter property name') }}"
                                value="{{ @$property->property_type ? ($property->property_type == 2 ? @$property->name : '') : '' }}">
                        </div>
                        <div class="col-md-4 mb-25">
                            <label class="label-text-title color-heading font-medium mb-2">
                                {{ __('Number of Units') }} <span class="text-danger">*</span>
                            </label>
                            @if(!$isEditing && $hasReachedLimit)
                                <input type="number" min="0" class="form-control" name="lease_number_of_unit"
                                    value="0" disabled
                                    placeholder="{{ __('No units available') }}">
                            @elseif($isEditing && $editHasReachedLimit)
                                <input type="number" min="{{ $currentPropertyUnits }}" max="{{ $currentPropertyUnits }}" 
                                    class="form-control" name="lease_number_of_unit"
                                    value="{{ $currentPropertyUnits }}" disabled
                                    placeholder="{{ __('Cannot add more units') }}">
                                <small class="text-danger mt-1 d-block">
                                    {{ __('Unit limit reached. You cannot add more units to this property.') }}
                                </small>
                            @else
                                <div class="pis-unit-input-wrapper">
                                    <input type="number" min="0" 
                                        max="{{ $isEditing ? $editableUnits : $remainingUnits }}"
                                        class="form-control unit-count-input" 
                                        name="lease_number_of_unit"
                                        id="lease_number_of_unit"
                                        value="{{ @$property->property_type ? ($property->property_type == 2 ? $property->number_of_unit : '') : '' }}"
                                        placeholder="{{ __('Enter number of units') }}"
                                        data-remaining="{{ $isEditing ? $editableUnits : $remainingUnits }}"
                                        data-used="{{ $isEditing ? ($usedUnits - $currentPropertyUnits) : $usedUnits }}"
                                        data-total="{{ $totalUnits }}"
                                        data-is-editing="{{ $isEditing ? 'true' : 'false' }}"
                                        data-current-units="{{ $currentPropertyUnits }}">
                                    <div class="pis-unit-counter pis-unit-counter--safe" id="unit-counter-lease">
                                        <div class="pis-unit-counter__bar">
                                            <div class="pis-unit-counter__fill" style="width: {{ $totalUnits > 0 ? (($isEditing ? ($usedUnits - $currentPropertyUnits) : $usedUnits) / $totalUnits * 100) : 0 }}%"></div>
                                        </div>
                                        <span class="pis-unit-counter__text">
                                            @if($isEditing)
                                                {{ $editableUnits }} {{ __('max allowed') }}
                                            @else
                                                {{ $remainingUnits }} {{ __('remaining') }}
                                            @endif
                                        </span>
                                    </div>
                                </div>
                            @endif
                        </div>
                        <div class="col-md-4 mb-25">
                            <label class="label-text-title color-heading font-medium mb-2">
                                {{ __('Lease Amount') }} <span class="text-danger">*</span>
                            </label>
                            <input type="number" min="0" step="any" class="form-control"
                                name="lease_amount" value="{{ @$property->propertyDetail->lease_amount }}"
                                placeholder="{{ __('Enter lease amount') }}">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-25">
                            <label class="label-text-title color-heading font-medium mb-2">
                                {{ __('Lease Start Date') }}
                            </label>
                            <div class="custom-datepicker">
                                <div class="custom-datepicker-inner position-relative">
                                    <input type="text" class="datepicker form-control" name="lease_start_date"
                                        value="{{ @$property->propertyDetail->lease_start_date }}" autocomplete="off"
                                        placeholder="dd-mm-yy">
                                    <i class="ri-calendar-2-line"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-25">
                            <label class="label-text-title color-heading font-medium mb-2">
                                {{ __('Lease End Date') }}
                            </label>
                            <div class="custom-datepicker">
                                <div class="custom-datepicker-inner position-relative">
                                    <input type="text" class="datepicker form-control" name="lease_end_date"
                                        value="{{ @$property->propertyDetail->lease_end_date }}" autocomplete="off"
                                        placeholder="dd-mm-yy">
                                    <i class="ri-calendar-2-line"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mb-25">
                            <label class="label-text-title color-heading font-medium mb-2">
                                {{ __('Description') }}
                            </label>
                            <textarea class="form-control" name="lease_description" rows="3"
                                placeholder="{{ __('Describe the property...') }}">{{ @$property->property_type ? ($property->property_type == 2 ? $property->description : '') : '' }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Submit Button ───────────────────────────────────── --}}
        @if(!$isEditing && $hasReachedLimit)
            <button type="button" class="action-button theme-btn mt-25" disabled 
                style="opacity: 0.5; cursor: not-allowed;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right:6px;">
                    <circle cx="12" cy="12" r="10"/>
                    <path d="M12 8v4M12 16h.01"/>
                </svg>
                {{ __('Unit Limit Reached — Upgrade to Continue') }}
            </button>
        @elseif($isEditing && $editHasReachedLimit)
            <button type="button" class="action-button theme-btn mt-25" disabled 
                style="opacity: 0.5; cursor: not-allowed;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right:6px;">
                    <circle cx="12" cy="12" r="10"/>
                    <path d="M12 8v4M12 16h.01"/>
                </svg>
                {{ __('Unit Limit Exceeded — Upgrade to Edit Units') }}
            </button>
        @else
            <button type="submit" class="action-button theme-btn mt-25" id="submit-property-info">
                {{ __('Save & Go to Next') }}
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-left:6px;">
                    <path d="M5 12h14M13 6l6 6-6 6"/>
                </svg>
            </button>
        @endif
    </form>
</div>

<script>
// ── Property Type Card Toggle & Unit Validation ───────────────
(function() {
    function initPropertyTypeCards() {
        const freshCards = document.querySelectorAll('.pis-type-card');
        
        freshCards.forEach(card => {
            const newCard = card.cloneNode(true);
            card.parentNode.replaceChild(newCard, card);
        });
        
        document.querySelectorAll('.pis-type-card').forEach(card => {
            card.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                const propertyType = this.getAttribute('data-property_type');
                
                document.querySelectorAll('.pis-type-card').forEach(c => c.classList.remove('is-active'));
                this.classList.add('is-active');
                
                const ptInput = document.getElementById('property_type');
                if (ptInput) ptInput.value = propertyType;
                
                const ownF = document.getElementById('own-property-form');
                const leaseF = document.getElementById('lease-property-form');
                
                if (propertyType == 1) {
                    if (ownF) ownF.classList.remove('d-none');
                    if (leaseF) leaseF.classList.add('d-none');
                } else if (propertyType == 2) {
                    if (ownF) ownF.classList.add('d-none');
                    if (leaseF) leaseF.classList.remove('d-none');
                }
            });
        });
        
        // Legacy tab button support
        document.querySelectorAll('.select_property_type').forEach(btn => {
            const newBtn = btn.cloneNode(true);
            btn.parentNode.replaceChild(newBtn, btn);
        });
        
        document.querySelectorAll('.select_property_type').forEach(btn => {
            btn.addEventListener('click', function(e) {
                const propertyType = this.getAttribute('data-property_type');
                const ptInput = document.getElementById('property_type');
                if (ptInput) ptInput.value = propertyType;
                
                document.querySelectorAll('.pis-type-card').forEach(card => {
                    card.classList.remove('is-active');
                    if (card.getAttribute('data-property_type') === propertyType) {
                        card.classList.add('is-active');
                    }
                });
                
                const ownF = document.getElementById('own-property-form');
                const leaseF = document.getElementById('lease-property-form');
                
                if (propertyType == 1) {
                    if (ownF) ownF.classList.remove('d-none');
                    if (leaseF) leaseF.classList.add('d-none');
                } else if (propertyType == 2) {
                    if (ownF) ownF.classList.add('d-none');
                    if (leaseF) leaseF.classList.remove('d-none');
                }
            });
        });
    }
    
    // ── Unit Count Validation ──────────────────────────────────
    function initUnitCountValidation() {
        const unitInputs = document.querySelectorAll('.unit-count-input');
        
        unitInputs.forEach(input => {
            if (input.dataset.initialized === 'true') return;
            input.dataset.initialized = 'true';
            
            const remaining = parseInt(input.getAttribute('data-remaining')) || 999;
            const used = parseInt(input.getAttribute('data-used')) || 0;
            const total = parseInt(input.getAttribute('data-total')) || 0;
            const isEditing = input.getAttribute('data-is-editing') === 'true';
            const currentUnits = parseInt(input.getAttribute('data-current-units')) || 0;
            
            const counterId = input.id === 'own_number_of_unit' ? 'unit-counter' : 'unit-counter-lease';
            const counter = document.getElementById(counterId);
            
            if (input && counter) {
                input.addEventListener('input', function() {
                    let value = parseInt(this.value) || 0;
                    const fillBar = counter.querySelector('.pis-unit-counter__fill');
                    const textEl = counter.querySelector('.pis-unit-counter__text');
                    
                    counter.classList.remove('pis-unit-counter--safe', 'pis-unit-counter--warning', 'pis-unit-counter--danger');
                    
                    // Enforce max limit
                    if (value > remaining) {
                        value = remaining;
                        this.value = remaining;
                    }
                    
                    // Enforce min limit for editing
                    if (isEditing && value < currentUnits && value > 0) {
                        // Allow but show warning via CSS class
                        counter.classList.add('pis-unit-counter--warning');
                    }
                    
                    const newRemaining = remaining - value;
                    const usedAfterChange = used + value;
                    const percentage = total > 0 ? (usedAfterChange / total * 100) : 0;
                    
                    if (newRemaining <= 0) {
                        counter.classList.add('pis-unit-counter--danger');
                        if (textEl) textEl.textContent = '0 remaining';
                    } else if (newRemaining <= 3) {
                        counter.classList.add('pis-unit-counter--warning');
                        if (textEl) textEl.textContent = newRemaining + ' remaining';
                    } else {
                        counter.classList.add('pis-unit-counter--safe');
                        if (textEl) textEl.textContent = newRemaining + ' remaining';
                    }
                    
                    if (fillBar) fillBar.style.width = percentage + '%';
                });
                
                // Trigger initial state
                input.dispatchEvent(new Event('input'));
            }
        });
    }
    
    // ── Form Submission Guard ──────────────────────────────────
    function initFormGuard() {
        const submitBtn = document.getElementById('submit-property-info');
        if (submitBtn && !submitBtn.dataset.guardInitialized) {
            submitBtn.dataset.guardInitialized = 'true';
            const form = submitBtn.closest('form');
            if (form) {
                form.addEventListener('submit', function(e) {
                    const activeInput = document.querySelector('.unit-count-input:not([disabled])');
                    if (activeInput) {
                        const value = parseInt(activeInput.value) || 0;
                        const remaining = parseInt(activeInput.getAttribute('data-remaining')) || 999;
                        const isEditing = activeInput.getAttribute('data-is-editing') === 'true';
                        const currentUnits = parseInt(activeInput.getAttribute('data-current-units')) || 0;
                        
                        if (value > remaining) {
                            e.preventDefault();
                            activeInput.value = remaining;
                            if (typeof toastr !== 'undefined') {
                                toastr.error('Unit count exceeds your plan limit (' + remaining + ' max). Please reduce the number of units.');
                            }
                            return false;
                        }
                        
                        if (value <= 0) {
                            e.preventDefault();
                            if (typeof toastr !== 'undefined') {
                                toastr.error('Please enter at least 1 unit.');
                            }
                            return false;
                        }
                        
                        // For edit mode: warn if reducing units
                        if (isEditing && value < currentUnits) {
                            const confirmed = activeInput.getAttribute('data-confirmed-reduce') === 'true';
                            if (!confirmed) {
                                e.preventDefault();
                                activeInput.setAttribute('data-confirmed-reduce', 'true');
                                if (typeof toastr !== 'undefined') {
                                    toastr.warning('You are reducing units from ' + currentUnits + ' to ' + value + '. Existing units may be deleted. Click Save again to confirm.');
                                }
                                return false;
                            }
                        }
                    }
                });
            }
        }
    }
    
    // ── Initialize everything ──────────────────────────────────
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            initPropertyTypeCards();
            initUnitCountValidation();
            initFormGuard();
        });
    } else {
        initPropertyTypeCards();
        initUnitCountValidation();
        initFormGuard();
    }
})();
</script>