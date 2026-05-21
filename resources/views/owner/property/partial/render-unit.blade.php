{{-- unit.blade.php --}}
<style>
/* ── Unit Step Styling ──────────────────────────────────── */
.unt-wrap {
    font-size: 13px;
    color: var(--gray-700, #374151);
}

/* ── Unit Count Banner ──────────────────────────────────── */
.unt-count-banner {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 18px;
    border-radius: 10px;
    margin-bottom: 20px;
    background: #EFF6FF;
    border: 0.5px solid #BFDBFE;
    color: #1E40AF;
}

.unt-count-banner__icon {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    background: #BFDBFE;
    color: #1E40AF;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.unt-count-banner__text {
    font-size: 12px;
    font-weight: 500;
    margin: 0;
}

.unt-count-banner__text strong {
    font-weight: 700;
}

/* ── Unit Card ──────────────────────────────────────────── */
.unt-card {
    background: #fff;
    border: 0.5px solid var(--blue-faint, #185ea56e);
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0,0,0,.04);
    margin-bottom: 16px;
}

.unt-card__head {
    padding: 14px 20px;
    border-bottom: 0.5px solid #E5E7EB;
    background: #FAFAFA;
    display: flex;
    align-items: center;
    gap: 10px;
}

.unt-card__head-number {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    background: var(--blue-light, #E6F1FB);
    color: var(--blue, #185FA5);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    font-weight: 700;
    flex-shrink: 0;
}

.unt-card__head-title {
    font-size: 13px;
    font-weight: 600;
    color: var(--gray-900, #111827);
    margin: 0;
}

.unt-card__head-subtitle {
    font-size: 11px;
    color: var(--gray-400, #9CA3AF);
    margin-left: auto;
}

.unt-card__body {
    padding: 20px;
}

/* ── Unit Field Row ─────────────────────────────────────── */
.unt-field-row {
    margin-bottom: 20px;
}

.unt-field-row:last-child {
    margin-bottom: 0;
}

.unt-field-label {
    display: block;
    font-size: 11px;
    font-weight: 500;
    color: var(--gray-700, #374151);
    margin-bottom: 6px;
}

.unt-field-label .required {
    color: #EF4444;
}

.unt-field-input {
    width: 100%;
    padding: 8px 12px;
    font-size: 13px;
    border: 0.5px solid #E5E7EB;
    border-radius: 7px;
    background: #fff;
    color: var(--gray-900, #111827);
    transition: all .15s ease;
}

.unt-field-input:focus {
    outline: none;
    border-color: var(--blue, #185FA5);
    box-shadow: 0 0 0 3px rgba(24,95,165,.1);
}

/* ── Image Upload ───────────────────────────────────────── */
.unt-image-upload {
    padding: 16px;
    border: 1.5px dashed #D1D5DB;
    border-radius: 10px;
    text-align: center;
    cursor: pointer;
    transition: all .18s ease;
    background: #FAFAFA;
    position: relative;
}

.unt-image-upload:hover {
    border-color: var(--blue, #185FA5);
    background: #EFF6FF;
}

.unt-image-upload__icon {
    color: var(--gray-400, #9CA3AF);
    margin-bottom: 8px;
}

.unt-image-upload__text {
    font-size: 12px;
    font-weight: 500;
    color: var(--gray-500, #6B7280);
    margin: 0;
}

.unt-image-upload__hint {
    font-size: 10px;
    color: var(--gray-400, #9CA3AF);
    margin: 4px 0 0;
}

.unt-image-upload input[type="file"] {
    position: absolute;
    inset: 0;
    opacity: 0;
    cursor: pointer;
}

/* ── Existing Images Grid ───────────────────────────────── */
.unt-images-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 12px;
}

.unt-image-item {
    width: 100px;
    height: 100px;
    border-radius: 8px;
    overflow: hidden;
    position: relative;
    border: 0.5px solid #E5E7EB;
}

.unt-image-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.unt-image-item__remove {
    position: absolute;
    top: 4px;
    right: 4px;
    width: 22px;
    height: 22px;
    border-radius: 50%;
    background: rgba(0, 0, 0, 0.6);
    color: #fff;
    border: none;
    font-size: 12px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all .15s ease;
}

.unt-image-item__remove:hover {
    background: rgba(239, 68, 68, 0.8);
}

/* ── Responsive ─────────────────────────────────────────── */
@media (max-width: 768px) {
    .unt-image-item {
        width: 80px;
        height: 80px;
    }
}
</style>

<div class="unt-wrap">
    <form class="ajax" action="{{ route('owner.property.unit.store') }}" method="post" data-handler="stepChange" enctype="multipart/form-data">
        @csrf

        <input type="hidden" name="property_id" value="{{ $property->id }}">
        <input type="hidden" name="unit_type" id="unit_type" value="{{ $property->unit_type ?? 2 }}">

        {{-- ── Unit Count Banner ──────────────────────────────── --}}
        @php $total = $property->number_of_unit; @endphp
        <div class="unt-count-banner">
            <div class="unt-count-banner__icon">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="3" width="18" height="18" rx="3"/>
                    <path d="M3 9h18M9 3v18"/>
                </svg>
            </div>
            <p class="unt-count-banner__text">
                {{ __('Adding') }} <strong>{{ $total }}</strong> {{ __('unit(s) for this property. Fill in the details for each unit below.') }}
            </p>
        </div>

        {{-- ── Unit Cards ─────────────────────────────────────── --}}
        @for ($i = 0; $i < $total; $i++)
            @php
                $unit = $propertyUnits[$i] ?? null;
                $unitNumber = $i + 1;
            @endphp

            <div class="unt-card">
                {{-- Card Head --}}
                <div class="unt-card__head">
                    <div class="unt-card__head-number">{{ $unitNumber }}</div>
                    <h4 class="unt-card__head-title">
                        {{ $unit->unit_name ?? 'Unit ' . $unitNumber }}
                    </h4>
                    @if(isset($unit) && $unit && $unit->id)
                        <span class="unt-card__head-subtitle">
                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right:3px;vertical-align:-1px;">
                                <path d="M12 2L2 7l10 5 10-5-10-5z"/>
                            </svg>
                            {{ __('Existing') }}
                        </span>
                    @endif
                </div>

                {{-- Card Body --}}
                <div class="unt-card__body">
                    <input type="hidden" name="multiple[id][]" value="{{ $unit->id ?? '' }}">

                    <div class="row">
                        {{-- Unit Name --}}
                        <div class="col-md-4 mb-20">
                            <label class="unt-field-label">
                                {{ __('Unit Name') }} <span class="required">*</span>
                            </label>
                            <input type="text" name="multiple[unit_name][]" class="unt-field-input"
                                value="{{ $unit->unit_name ?? '' }}" 
                                placeholder="{{ __('e.g. Unit A, Ground Floor') }}" required>
                        </div>

                        {{-- Bedroom --}}
                        <div class="col-md-2 mb-20">
                            <label class="unt-field-label">
                                {{ __('Bedrooms') }} <span class="required">*</span>
                            </label>
                            <input type="number" min="0" name="multiple[bedroom][]" class="unt-field-input"
                                value="{{ $unit->bedroom ?? 0 }}" placeholder="0" required>
                        </div>

                        {{-- Baths --}}
                        <div class="col-md-2 mb-20">
                            <label class="unt-field-label">
                                {{ __('Baths') }} <span class="required">*</span>
                            </label>
                            <input type="number" min="0" name="multiple[bath][]" class="unt-field-input"
                                value="{{ $unit->bath ?? 0 }}" placeholder="0" required>
                        </div>

                        {{-- Kitchen --}}
                        <div class="col-md-2 mb-20">
                            <label class="unt-field-label">
                                {{ __('Kitchen') }} <span class="required">*</span>
                            </label>
                            <input type="number" min="0" name="multiple[kitchen][]" class="unt-field-input"
                                value="{{ $unit->kitchen ?? 0 }}" placeholder="0" required>
                        </div>

                        {{-- Square Feet --}}
                        <div class="col-md-2 mb-20">
                            <label class="unt-field-label">{{ __('Sq. Feet') }}</label>
                            <input type="text" name="multiple[square_feet][]" class="unt-field-input"
                                value="{{ $unit->square_feet ?? '' }}" placeholder="e.g. 1200">
                        </div>

                        {{-- Amenities --}}
                        <div class="col-md-3 mb-20">
                            <label class="unt-field-label">{{ __('Amenities') }}</label>
                            <input type="text" name="multiple[amenities][]" class="unt-field-input"
                                value="{{ $unit->amenities ?? '' }}" placeholder="e.g. WiFi, AC, Parking">
                        </div>

                        {{-- Condition --}}
                        <div class="col-md-3 mb-20">
                            <label class="unt-field-label">{{ __('Condition') }}</label>
                            <input type="text" name="multiple[condition][]" class="unt-field-input"
                                value="{{ $unit->condition ?? '' }}" placeholder="e.g. New, Good, Needs Repair">
                        </div>

                        {{-- Parking --}}
                        <div class="col-md-3 mb-20">
                            <label class="unt-field-label">{{ __('Parking') }}</label>
                            <input type="text" name="multiple[parking][]" class="unt-field-input"
                                value="{{ $unit->parking ?? '' }}" placeholder="e.g. 1 car, Street">
                        </div>

                        {{-- Description --}}
                        <div class="col-md-3 mb-20">
                            <label class="unt-field-label">{{ __('Description') }}</label>
                            <input type="text" name="multiple[description][]" class="unt-field-input"
                                value="{{ $unit->description ?? '' }}" placeholder="{{ __('Brief description') }}">
                        </div>

                        {{-- Images --}}
                        <div class="col-md-12 mb-20">
                            <label class="unt-field-label">{{ __('Images') }}</label>
                            <div class="unt-image-upload">
                                <div class="unt-image-upload__icon">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                        <rect x="3" y="3" width="18" height="18" rx="2"/>
                                        <circle cx="8.5" cy="8.5" r="1.5"/>
                                        <path d="M21 15l-5-5L5 21"/>
                                    </svg>
                                </div>
                                <p class="unt-image-upload__text">{{ __('Click or drag to upload images') }}</p>
                                <p class="unt-image-upload__hint">{{ __('PNG, JPG up to 5MB each') }}</p>
                                <input type="file"
                                    name="multiple[images][{{ $i }}][]"
                                    class="multiple-images"
                                    multiple
                                    accept="image/*">
                            </div>

                            {{-- Existing Images --}}
                            @if($unit && $unit->images->count())
                                <div class="unt-images-grid">
                                    @foreach($unit->images as $image)
                                        <div class="unt-image-item">
                                            <img src="{{ asset('storage/' . $image->folder_name . '/' . $image->file_name) }}" 
                                                 alt="Unit Image" loading="lazy">
                                            <button type="button"
                                                    class="unt-image-item__remove remove-existing-image"
                                                    data-image-id="{{ $image->id }}"
                                                    title="{{ __('Remove image') }}">
                                                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                                    <path d="M18 6L6 18M6 6l12 12"/>
                                                </svg>
                                            </button>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            {{-- New image preview container --}}
                            <div class="image-preview-container unt-images-grid" style="display:none;"></div>
                        </div>
                    </div>
                </div>
            </div>
        @endfor

        {{-- ── Navigation Buttons ─────────────────────────────── --}}
        <div class="d-flex gap-2 mt-3">
            <button type="button" class="unitBack action-button-previous theme-btn">
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

<style>
    .unt-field-input {
        width: 100%;
        padding: 8px 12px;
        font-size: 13px;
        border: 0.5px solid #E5E7EB;
        border-radius: 7px;
        background: #fff;
        color: var(--gray-900, #111827);
        transition: all .15s ease;
    }
    
    .unt-field-input:focus {
        outline: none;
        border-color: var(--blue, #185FA5);
        box-shadow: 0 0 0 3px rgba(24,95,165,.1);
    }
</style>

<script>
    window.deleteUnitImageBaseUrl = "{{ url('/owner/unit-image') }}/";
    
    // Initialize image preview for dynamically loaded content
    if (typeof initUnitImagePreviewer === 'function') {
        initUnitImagePreviewer();
    }
</script>