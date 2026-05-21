{{-- rent-charge.blade.php --}}
<style>
/* ── Rent & Charges Step Styling ─────────────────────────── */
.rnc-wrap {
    font-size: 13px;
    color: var(--gray-700, #374151);
}

/* ── Quick Actions Bar ──────────────────────────────────── */
.rnc-quick-bar {
    background: #fff;
    border: 0.5px solid var(--blue-faint, #185ea56e);
    border-radius: 12px;
    padding: 16px 20px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 16px;
    flex-wrap: wrap;
    box-shadow: 0 4px 12px rgba(0,0,0,.04);
}

.rnc-quick-bar__label {
    font-size: 12px;
    font-weight: 500;
    color: var(--gray-500, #6B7280);
    white-space: nowrap;
}

.rnc-quick-bar__select {
    padding: 8px 12px;
    font-size: 13px;
    border: 0.5px solid #E5E7EB;
    border-radius: 7px;
    background: #fff;
    color: var(--gray-900, #111827);
    min-width: 200px;
    flex: 1;
}

.rnc-quick-bar__select:focus {
    outline: none;
    border-color: var(--blue, #185FA5);
    box-shadow: 0 0 0 3px rgba(24,95,165,.1);
}

.rnc-quick-bar__checkbox {
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    font-size: 12px;
    font-weight: 500;
    color: var(--gray-700, #374151);
    white-space: nowrap;
    padding: 8px 14px;
    border-radius: 7px;
    border: 0.5px solid #E5E7EB;
    background: #FAFAFA;
    transition: all .15s ease;
}

.rnc-quick-bar__checkbox:hover {
    border-color: var(--blue, #185FA5);
    background: #EFF6FF;
}

.rnc-quick-bar__checkbox input[type="checkbox"] {
    accent-color: var(--blue, #185FA5);
}

/* ── Unit Accordion Card ────────────────────────────────── */
.rnc-unit-card {
    background: #fff;
    border: 0.5px solid var(--blue-faint, #185ea56e);
    border-radius: 12px;
    overflow: hidden;
    margin-bottom: 16px;
    box-shadow: 0 4px 12px rgba(0,0,0,.04);
    transition: all .18s ease;
}

.rnc-unit-card:hover {
    box-shadow: 0 8px 24px rgba(0,0,0,.06);
}

.rnc-unit-card__header {
    padding: 14px 20px;
    background: #FAFAFA;
    border-bottom: 0.5px solid transparent;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 12px;
    user-select: none;
    transition: all .15s ease;
}

.rnc-unit-card__header:hover {
    background: #F3F4F6;
}

.rnc-unit-card__header.is-expanded {
    background: #EFF6FF;
    border-bottom-color: var(--blue, #185FA5);
}

.rnc-unit-card__number {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    background: var(--blue-light, #E6F1FB);
    color: var(--blue, #185FA5);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
    font-weight: 700;
    flex-shrink: 0;
}

.rnc-unit-card__name {
    font-size: 14px;
    font-weight: 600;
    color: var(--gray-900, #111827);
    flex: 1;
    margin: 0;
}

.rnc-unit-card__rent-preview {
    font-size: 11px;
    color: var(--gray-500, #6B7280);
    text-align: right;
}

.rnc-unit-card__rent-preview strong {
    color: var(--gray-900, #111827);
    font-size: 13px;
}

.rnc-unit-card__chevron {
    color: var(--gray-400, #9CA3AF);
    transition: transform .25s ease;
    flex-shrink: 0;
}

.rnc-unit-card__header.is-expanded .rnc-unit-card__chevron {
    transform: rotate(180deg);
    color: var(--blue, #185FA5);
}

.rnc-unit-card__body {
    max-height: 0;
    overflow: hidden;
    transition: max-height .4s ease, padding .4s ease;
    padding: 0 20px;
}

.rnc-unit-card__body.is-expanded {
    max-height: 2000px;
    padding: 20px;
}

/* ── Rent Type Pills ────────────────────────────────────── */
.rnc-rent-type-pills {
    display: flex;
    gap: 6px;
    margin-bottom: 20px;
    background: #F3F4F6;
    border-radius: 10px;
    padding: 4px;
    width: fit-content;
}

.rnc-rent-type-pill {
    padding: 7px 16px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 500;
    cursor: pointer;
    transition: all .15s ease;
    background: transparent;
    border: none;
    color: var(--gray-600, #4B5563);
}

.rnc-rent-type-pill.is-active {
    background: #fff;
    color: var(--gray-900, #111827);
    box-shadow: 0 1px 3px rgba(0,0,0,.08);
    font-weight: 600;
}

.rnc-rent-type-pill:hover:not(.is-active) {
    color: var(--gray-900, #111827);
}

/* ── Form Fields ───────────────────────────────────────── */
.rnc-field {
    margin-bottom: 18px;
}

.rnc-field__label {
    display: block;
    font-size: 11px;
    font-weight: 500;
    color: var(--gray-700, #374151);
    margin-bottom: 6px;
}

.rnc-field__label .required {
    color: #EF4444;
}

.rnc-field__input {
    width: 100%;
    padding: 8px 12px;
    font-size: 13px;
    border: 0.5px solid #E5E7EB;
    border-radius: 7px;
    background: #fff;
    color: var(--gray-900, #111827);
    transition: all .15s ease;
}

.rnc-field__input:focus {
    outline: none;
    border-color: var(--blue, #185FA5);
    box-shadow: 0 0 0 3px rgba(24,95,165,.1);
}

.rnc-field__input-group {
    display: flex;
    gap: 8px;
}

.rnc-field__input-group select {
    width: 130px;
    flex-shrink: 0;
    padding: 8px 12px;
    font-size: 12px;
    border: 0.5px solid #E5E7EB;
    border-radius: 7px;
    background: #fff;
    color: var(--gray-900, #111827);
}

.rnc-field__input-group select:focus {
    outline: none;
    border-color: var(--blue, #185FA5);
    box-shadow: 0 0 0 3px rgba(24,95,165,.1);
}

.rnc-field__input-group input {
    flex: 1;
}

/* ── Rent Type Content ─────────────────────────────────── */
.rnc-rent-content {
    display: none;
}

.rnc-rent-content.is-active {
    display: block;
}

/* ── Datepicker Wrapper ────────────────────────────────── */
.rnc-datepicker {
    position: relative;
}

.rnc-datepicker .form-control {
    padding-right: 36px;
}

.rnc-datepicker i {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--gray-400, #9CA3AF);
    pointer-events: none;
}

/* ── Responsive ─────────────────────────────────────────── */
@media (max-width: 768px) {
    .rnc-quick-bar {
        flex-direction: column;
        align-items: stretch;
    }
    
    .rnc-quick-bar__select {
        width: 100%;
    }
    
    .rnc-rent-type-pills {
        width: 100%;
    }
    
    .rnc-rent-type-pill {
        flex: 1;
        text-align: center;
    }
    
    .rnc-field__input-group {
        flex-direction: column;
    }
    
    .rnc-field__input-group select {
        width: 100%;
    }
}
</style>

<div class="rnc-wrap">
    <form class="ajax" action="{{ route('owner.property.rentCharge.store') }}" method="post" data-handler="stepChange">
        @csrf
        <input type="hidden" name="ids[]" class="d-none" id="property_unit_ids" value="{{ @json_encode($propertyUnitIds) }}">
        <input type="hidden" name="property_id" class="d-none property_id" value="{{ $property->id }}">

        {{-- ── Quick Actions Bar ──────────────────────────────── --}}
        <div class="rnc-quick-bar">
            <span class="rnc-quick-bar__label">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right:4px;vertical-align:-2px;">
                    <path d="M6 9l6 6 6-6"/>
                </svg>
                {{ __('Jump to Unit') }}
            </span>
            <select class="rnc-quick-bar__select" id="select_unit_id">
                <option value="">{{ __('Select a unit...') }}</option>
                @foreach ($propertyUnits as $propertyUnit)
                    <option value="{{ $propertyUnit->id }}">{{ $propertyUnit->unit_name }}</option>
                @endforeach
            </select>
            
            <label class="rnc-quick-bar__checkbox" id="sameRentLabel">
                <input type="checkbox" id="sameUnitRent">
                <span>{{ __('Apply same rent to all units') }}</span>
            </label>
        </div>

        {{-- ── Unit Cards ─────────────────────────────────────── --}}
        @php $c = 1; @endphp
        @foreach ($propertyUnits as $propertyUnit)
            <input type="hidden" name="propertyUnit[id][]" value="{{ $propertyUnit->id }}">
            
            <div class="rnc-unit-card" id="unit-card-{{ $propertyUnit->id }}">
                {{-- Card Header --}}
                <div class="rnc-unit-card__header {{ $c == 1 ? 'is-expanded' : '' }}" 
                     data-unit-id="{{ $propertyUnit->id }}"
                     onclick="toggleUnitCard(this, {{ $propertyUnit->id }})">
                    <div class="rnc-unit-card__number">{{ $c }}</div>
                    <h4 class="rnc-unit-card__name">{{ $propertyUnit->unit_name }}</h4>
                    @if($propertyUnit->general_rent)
                        <div class="rnc-unit-card__rent-preview">
                            {{ __('Rent') }} <strong>{{ currencyPrice($propertyUnit->general_rent) }}</strong>
                        </div>
                    @endif
                    <svg class="rnc-unit-card__chevron" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M6 9l6 6 6-6"/>
                    </svg>
                </div>

                {{-- Card Body --}}
                <div id="collapse{{ $propertyUnit->id }}" 
                     class="rnc-unit-card__body {{ $c == 1 ? 'is-expanded' : '' }}">
                    <div class="row">
                        {{-- General Rent --}}
                        <div class="col-md-6 col-lg-3">
                            <div class="rnc-field">
                                <label class="rnc-field__label">
                                    {{ __('General Rent') }} <span class="required">*</span>
                                </label>
                                <input type="number" name="propertyUnit[general_rent][]"
                                    id="general_rent{{ $propertyUnit->id }}"
                                    value="{{ $propertyUnit->general_rent }}" 
                                    class="rnc-field__input"
                                    placeholder="{{ __('0.00') }}" required>
                            </div>
                        </div>

                        {{-- Security Deposit --}}
                        <div class="col-md-6 col-lg-3">
                            <div class="rnc-field">
                                <label class="rnc-field__label">
                                    {{ __('Security Deposit') }} <span class="required">*</span>
                                </label>
                                <div class="rnc-field__input-group">
                                    <select name="propertyUnit[security_deposit_type][]" required>
                                        <option value="0" {{ $propertyUnit->security_deposit_type == TYPE_FIXED ? 'selected' : '' }}>
                                            {{ __('Fixed') }}
                                        </option>
                                        <option value="1" {{ $propertyUnit->security_deposit_type == TYPE_PERCENTAGE ? 'selected' : '' }}>
                                            {{ __('%') }}
                                        </option>
                                    </select>
                                    <input type="number" name="propertyUnit[security_deposit][]"
                                        id="security_deposit{{ $propertyUnit->id }}"
                                        value="{{ $propertyUnit->security_deposit }}" 
                                        class="rnc-field__input"
                                        placeholder="{{ __('0.00') }}" required>
                                </div>
                            </div>
                        </div>

                        {{-- Late Fee --}}
                        <div class="col-md-6 col-lg-3">
                            <div class="rnc-field">
                                <label class="rnc-field__label">
                                    {{ __('Late Fee') }} <span class="required">*</span>
                                </label>
                                <div class="rnc-field__input-group">
                                    <select name="propertyUnit[late_fee_type][]" required>
                                        <option value="0" {{ $propertyUnit->late_fee_type == TYPE_FIXED ? 'selected' : '' }}>
                                            {{ __('Fixed') }}
                                        </option>
                                        <option value="1" {{ $propertyUnit->late_fee_type == TYPE_PERCENTAGE ? 'selected' : '' }}>
                                            {{ __('%') }}
                                        </option>
                                    </select>
                                    <input type="number" name="propertyUnit[late_fee][]"
                                        id="late_fee{{ $propertyUnit->id }}"
                                        value="{{ $propertyUnit->late_fee }}" 
                                        class="rnc-field__input"
                                        placeholder="{{ __('0.00') }}" required>
                                </div>
                            </div>
                        </div>

                        {{-- Incident Receipt --}}
                        <div class="col-md-6 col-lg-3">
                            <div class="rnc-field">
                                <label class="rnc-field__label">
                                    {{ __('Incident Receipt') }} <span class="required">*</span>
                                </label>
                                <input type="text" name="propertyUnit[incident_receipt][]"
                                    id="incident_receipt{{ $propertyUnit->id }}"
                                    value="{{ $propertyUnit->incident_receipt }}" 
                                    class="rnc-field__input"
                                    placeholder="{{ __('Receipt details') }}" required>
                            </div>
                        </div>
                    </div>

                    {{-- Rent Type Selection --}}
                    <div class="rnc-field">
                        <label class="rnc-field__label">{{ __('Rent Period') }}</label>
                        <div class="rnc-rent-type-pills" id="rentTypePills{{ $propertyUnit->id }}">
                            <button type="button" 
                                class="rnc-rent-type-pill select_rent_type {{ $propertyUnit->rent_type == PROPERTY_UNIT_RENT_TYPE_MONTHLY ? 'is-active' : '' }}"
                                data-rent_type="1" data-id="{{ $propertyUnit->id }}"
                                onclick="switchRentType(this, {{ $propertyUnit->id }}, 1)">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right:4px;">
                                    <rect x="3" y="4" width="18" height="18" rx="2"/>
                                    <path d="M16 2v4M8 2v4M3 10h18"/>
                                </svg>
                                {{ __('Monthly') }}
                            </button>
                            <button type="button" 
                                class="rnc-rent-type-pill select_rent_type {{ $propertyUnit->rent_type == PROPERTY_UNIT_RENT_TYPE_YEARLY ? 'is-active' : '' }}"
                                data-rent_type="2" data-id="{{ $propertyUnit->id }}"
                                onclick="switchRentType(this, {{ $propertyUnit->id }}, 2)">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right:4px;">
                                    <circle cx="12" cy="12" r="10"/>
                                    <path d="M12 6v6l4 2"/>
                                </svg>
                                {{ __('Yearly') }}
                            </button>
                            <button type="button" 
                                class="rnc-rent-type-pill select_rent_type {{ $propertyUnit->rent_type == PROPERTY_UNIT_RENT_TYPE_CUSTOM ? 'is-active' : '' }}"
                                data-rent_type="3" data-id="{{ $propertyUnit->id }}"
                                onclick="switchRentType(this, {{ $propertyUnit->id }}, 3)">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right:4px;">
                                    <path d="M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.91 6.91a2.12 2.12 0 01-3-3l6.91-6.91a6 6 0 017.94-7.94l-3.76 3.76z"/>
                                </svg>
                                {{ __('Custom') }}
                            </button>
                        </div>
                        
                        <input type="hidden" name="propertyUnit[rent_type][]"
                            value="{{ $propertyUnit->rent_type }}"
                            id="rent_type{{ $propertyUnit->id }}">

                        {{-- Monthly Content --}}
                        <div class="rnc-rent-content {{ $propertyUnit->rent_type == PROPERTY_UNIT_RENT_TYPE_MONTHLY ? 'is-active' : '' }}" 
                             id="monthly-content-{{ $propertyUnit->id }}">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="rnc-field">
                                        <label class="rnc-field__label">{{ __('Due Day of Month') }}</label>
                                        <input type="number" min="1" max="30"
                                            name="propertyUnit[monthly_due_day][]"
                                            value="{{ $propertyUnit->monthly_due_day }}"
                                            class="rnc-field__input"
                                            placeholder="{{ __('1 - 30') }}">
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Yearly Content --}}
                        <div class="rnc-rent-content {{ $propertyUnit->rent_type == PROPERTY_UNIT_RENT_TYPE_YEARLY ? 'is-active' : '' }}" 
                             id="yearly-content-{{ $propertyUnit->id }}">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="rnc-field">
                                        <label class="rnc-field__label">{{ __('Due Month of Year') }}</label>
                                        <input type="number" min="1" max="12"
                                            name="propertyUnit[yearly_due_day][]"
                                            value="{{ $propertyUnit->yearly_due_day }}"
                                            class="rnc-field__input"
                                            placeholder="{{ __('1 - 12') }}">
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Custom Content --}}
                        <div class="rnc-rent-content {{ $propertyUnit->rent_type == PROPERTY_UNIT_RENT_TYPE_CUSTOM ? 'is-active' : '' }}" 
                             id="custom-content-{{ $propertyUnit->id }}">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="rnc-field">
                                        <label class="rnc-field__label">{{ __('Lease Start Date') }}</label>
                                        <div class="rnc-datepicker">
                                            <input type="text" name="propertyUnit[lease_start_date][]"
                                                value="{{ $propertyUnit->lease_start_date }}"
                                                class="datepicker rnc-field__input" autocomplete="off"
                                                placeholder="dd-mm-yy">
                                            <i class="ri-calendar-2-line"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="rnc-field">
                                        <label class="rnc-field__label">{{ __('Lease End Date') }}</label>
                                        <div class="rnc-datepicker">
                                            <input type="text" name="propertyUnit[lease_end_date][]"
                                                value="{{ $propertyUnit->lease_end_date }}"
                                                class="datepicker rnc-field__input" autocomplete="off"
                                                placeholder="dd-mm-yy">
                                            <i class="ri-calendar-2-line"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="rnc-field">
                                        <label class="rnc-field__label">{{ __('Payment Due Date') }}</label>
                                        <div class="rnc-datepicker">
                                            <input type="text" name="propertyUnit[lease_payment_due_date][]"
                                                value="{{ $propertyUnit->lease_payment_due_date }}"
                                                class="datepicker rnc-field__input" autocomplete="off"
                                                placeholder="dd-mm-yy">
                                            <i class="ri-calendar-2-line"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @php $c++; @endphp
        @endforeach

        {{-- ── Navigation Buttons ─────────────────────────────── --}}
        <div class="d-flex gap-2 mt-3">
            <button type="button" class="rentChargeBack action-button-previous theme-btn">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right:4px;">
                    <path d="M19 12H5M12 19l-7-7 7-7"/>
                </svg>
                {{ __('Back') }}
            </button>
            <button type="submit" class="action-button theme-btn flex-1">
                {{ __('Save & Go to Next') }}
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-left:4px;">
                    <path d="M5 12h14M13 6l6 6-6 6"/>
                </svg>
            </button>
        </div>
    </form>
</div>

<script>
// ── Toggle Unit Card ───────────────────────────────────────
function toggleUnitCard(header, unitId) {
    const isExpanded = header.classList.contains('is-expanded');
    const body = document.getElementById('collapse' + unitId);
    
    if (isExpanded) {
        // Collapse
        header.classList.remove('is-expanded');
        if (body) body.classList.remove('is-expanded');
    } else {
        // Expand
        header.classList.add('is-expanded');
        if (body) body.classList.add('is-expanded');
    }
}

// ── Switch Rent Type ───────────────────────────────────────
function switchRentType(pill, unitId, rentType) {
    // Update pills
    const pillsContainer = pill.closest('.rnc-rent-type-pills');
    pillsContainer.querySelectorAll('.rnc-rent-type-pill').forEach(p => p.classList.remove('is-active'));
    pill.classList.add('is-active');
    
    // Update hidden input
    const rentTypeInput = document.getElementById('rent_type' + unitId);
    if (rentTypeInput) rentTypeInput.value = rentType;
    
    // Toggle content visibility
    const monthlyContent = document.getElementById('monthly-content-' + unitId);
    const yearlyContent = document.getElementById('yearly-content-' + unitId);
    const customContent = document.getElementById('custom-content-' + unitId);
    
    if (monthlyContent) monthlyContent.classList.toggle('is-active', rentType == 1);
    if (yearlyContent) yearlyContent.classList.toggle('is-active', rentType == 2);
    if (customContent) customContent.classList.toggle('is-active', rentType == 3);
}

// ── Jump to Unit ───────────────────────────────────────────
document.getElementById('select_unit_id')?.addEventListener('change', function() {
    const unitId = this.value;
    if (unitId) {
        const card = document.getElementById('unit-card-' + unitId);
        if (card) {
            card.scrollIntoView({ behavior: 'smooth', block: 'center' });
            // Expand the card if collapsed
            const header = card.querySelector('.rnc-unit-card__header');
            const body = card.querySelector('.rnc-unit-card__body');
            if (header && !header.classList.contains('is-expanded')) {
                header.classList.add('is-expanded');
                if (body) body.classList.add('is-expanded');
            }
            // Highlight briefly
            card.style.boxShadow = '0 0 0 3px rgba(24,95,165,.3)';
            setTimeout(() => {
                card.style.boxShadow = '';
            }, 1500);
        }
    }
});

// ── Initialize datepickers ─────────────────────────────────
if (typeof datePicker === 'function') {
    datePicker();
}
</script>