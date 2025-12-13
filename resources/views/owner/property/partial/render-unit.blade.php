<form class="ajax" action="{{ route('owner.property.unit.store') }}" method="post" data-handler="stepChange" enctype="multipart/form-data">
    @csrf

    <input type="hidden" name="property_id" value="{{ $property->id }}">
    <input type="hidden" name="unit_type" id="unit_type" value="{{ $property->unit_type ?? 2 }}">

    <div class="form-card add-property-box bg-off-white theme-border radius-4 p-20">
        <div class="add-property-title border-bottom pb-25 mb-25">
            <h4>{{ __('Add Unit') }}</h4>
        </div>

        <div class="add-property-inner-box bg-white theme-border radius-4 p-20">

            {{-- Always render exactly $property->number_of_unit rows --}}
            @php
                $total = $property->number_of_unit;
            @endphp

            @for ($i = 0; $i < $total; $i++)
                @php
                    $unit = $propertyUnits[$i] ?? null;
                @endphp

                <div class="multi-field border-bottom pb-25 mb-25">
                    <input type="hidden" name="multiple[id][]" value="{{ $unit->id ?? '' }}">

                    <div class="row">

                        {{-- Unit Name --}}
                        <div class="col-md-2 mb-25">
                            <label class="label-text-title color-heading font-medium mb-2">Unit Name</label>
                            <input type="text" name="multiple[unit_name][]" class="form-control"
                                   value="{{ $unit->unit_name ?? '' }}" placeholder="Unit Name" required>
                        </div>

                        {{-- Bedroom --}}
                        <div class="col-md-2 mb-25">
                            <label class="label-text-title color-heading font-medium mb-2">Bedroom</label>
                            <input type="number" min="0" name="multiple[bedroom][]" class="form-control"
                                   value="{{ $unit->bedroom ?? 0 }}" required>
                        </div>

                        {{-- Baths --}}
                        <div class="col-md-2 mb-25">
                            <label class="label-text-title color-heading font-medium mb-2">Baths</label>
                            <input type="number" min="0" name="multiple[bath][]" class="form-control"
                                   value="{{ $unit->bath ?? 0 }}" required>
                        </div>

                        {{-- Kitchen --}}
                        <div class="col-md-2 mb-25">
                            <label class="label-text-title color-heading font-medium mb-2">Kitchen</label>
                            <input type="number" min="0" name="multiple[kitchen][]" class="form-control"
                                   value="{{ $unit->kitchen ?? 0 }}" required>
                        </div>

                        {{-- Square Feet --}}
                        <div class="col-md-2 mb-25">
                            <label class="label-text-title color-heading font-medium mb-2">Square Feet</label>
                            <input type="text" name="multiple[square_feet][]" class="form-control"
                                   value="{{ $unit->square_feet ?? '' }}" placeholder="Square Feet">
                        </div>

                        {{-- Amenities --}}
                        <div class="col-md-2 mb-25">
                            <label class="label-text-title color-heading font-medium mb-2">Amenities</label>
                            <input type="text" name="multiple[amenities][]" class="form-control"
                                   value="{{ $unit->amenities ?? '' }}" placeholder="Amenities">
                        </div>

                        {{-- Condition --}}
                        <div class="col-md-2 mb-25">
                            <label class="label-text-title color-heading font-medium mb-2">Condition</label>
                            <input type="text" name="multiple[condition][]" class="form-control"
                                   value="{{ $unit->condition ?? '' }}" placeholder="Condition">
                        </div>

                        {{-- Parking --}}
                        <div class="col-md-2 mb-25">
                            <label class="label-text-title color-heading font-medium mb-2">Parking</label>
                            <input type="text" name="multiple[parking][]" class="form-control"
                                   value="{{ $unit->parking ?? '' }}" placeholder="Parking">
                        </div>

                        {{-- Description --}}
                        <div class="col-md-8 mb-25">
                            <label class="label-text-title color-heading font-medium mb-2">Description</label>
                            <input type="text" name="multiple[description][]" class="form-control"
                                   value="{{ $unit->description ?? '' }}" placeholder="Description">
                        </div>

                        {{-- Images --}}
                        <div class="row align-items-start">
                            <div class="col-md-2 col-lg-2 col-xl-2 mb-25">
                                <label class="label-text-title color-heading font-medium mb-2">{{ __('Images') }}</label>
                                <input type="file"
                                    name="multiple[images][{{ $i }}][]"
                                    class="form-control multiple-images"
                                    multiple
                                    accept="image/*">
                            </div>
                        </div>

                        {{-- Existing Images Styled in Flex Grid --}}
                        @if($unit && $unit->images->count())
                            <div class="existing-unit-images">
                            @foreach($unit->images as $image)
                                <div class="existing-unit-image-box">
                                    <img src="{{ asset('storage/' . $image->folder_name . '/' . $image->file_name) }}" alt="Unit Image">

                                    <button type="button"
                                            class="remove-existing-image"
                                            data-image-id="{{ $image->id }}">
                                        <i class="ri-delete-bin-5-line"></i>
                                    </button>
                                </div>
                            @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            @endfor
        </div>
    </div>

    <input type="button" name="previous" class="unitBack action-button-previous theme-btn mt-25" value="Back">

    <button type="submit" class="action-button theme-btn mt-25">Save & Go to Next</button>
</form>
<style>
    .existing-unit-images {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 10px;
}

.existing-unit-image-box {
    width: 120px;
    height: 120px;
    border-radius: 8px;
    overflow: hidden;
    position: relative;
    border: 1px solid #ccc;
}

.existing-unit-image-box img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.existing-unit-image-box .remove-existing-image {
    position: absolute;
    top: 3px;
    right: 3px;
    width: 22px;
    height: 22px;
    border-radius: 50%;
    background: rgba(0,0,0,0.6);
    color: #fff;
    border: none;
    font-size: 14px;
    line-height: 20px;
    cursor: pointer;
}
</style>
<script>
    window.deleteUnitImageBaseUrl = "{{ url('/owner/unit-image') }}/";
</script>


