{{-- image.blade.php --}}
<style>
/* ── Image Step Styling ─────────────────────────────────── */
.img-wrap {
    font-size: 13px;
    color: var(--gray-700, #374151);
}

/* ── Section Card ───────────────────────────────────────── */
.img-card {
    background: #fff;
    border: 0.5px solid var(--blue-faint, #185ea56e);
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0,0,0,.04);
    margin-bottom: 20px;
}

.img-card__head {
    padding: 16px 20px;
    border-bottom: 0.5px solid #E5E7EB;
    background: #FAFAFA;
    display: flex;
    align-items: center;
    gap: 10px;
}

.img-card__head-icon {
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

.img-card__head h4 {
    font-size: 13px;
    font-weight: 600;
    color: var(--gray-900, #111827);
    margin: 0;
}

.img-card__head-badge {
    font-size: 10px;
    font-weight: 500;
    padding: 2px 8px;
    border-radius: 99px;
    background: #FEF3C7;
    color: #D97706;
    margin-left: auto;
}

.img-card__body {
    padding: 20px;
}

/* ── Thumbnail Section ──────────────────────────────────── */
.img-thumbnail-preview {
    position: relative;
    width: 160px;
    height: 120px;
    border-radius: 10px;
    overflow: hidden;
    border: 0.5px solid #E5E7EB;
    background: #F9FAFB;
}

.img-thumbnail-preview img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.img-thumbnail-preview__overlay {
    position: absolute;
    inset: 0;
    background: rgba(0,0,0,0.3);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity .2s ease;
    cursor: pointer;
}

.img-thumbnail-preview:hover .img-thumbnail-preview__overlay {
    opacity: 1;
}

.img-thumbnail-preview__overlay-icon {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: rgba(255,255,255,0.9);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--blue, #185FA5);
}

.img-thumbnail-preview__overlay input[type="file"] {
    position: absolute;
    inset: 0;
    opacity: 0;
    cursor: pointer;
}

/* ── Dropzone / Upload Area ─────────────────────────────── */
.dropzone {
    border: 1.5px dashed #D1D5DB !important;
    border-radius: 10px !important;
    padding: 20px !important;
    text-align: center;
    background: #FAFAFA !important;
    transition: all .2s ease;
    cursor: pointer;
    min-height: auto !important;
    display: flex !important;
    flex-wrap: wrap !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 12px !important;
}

.dropzone:hover {
    border-color: var(--blue, #185FA5) !important;
    background: #EFF6FF !important;
}

.dropzone.dz-drag-hover {
    border-color: var(--blue, #185FA5) !important;
    background: #DBEAFE !important;
    border-style: solid !important;
}

.dropzone .dz-message {
    margin: 8px 0 !important;
}

.dropzone .dz-preview {
    margin: 6px !important;
    position: relative !important;
}

.dropzone .dz-preview .dz-image {
    border-radius: 8px !important;
    overflow: hidden !important;
    width: 120px !important;
    height: 90px !important;
}

.dropzone .dz-preview .dz-image img {
    width: 100% !important;
    height: 100% !important;
    object-fit: cover !important;
}

.dropzone .dz-preview .dz-remove {
    position: absolute !important;
    top: -6px !important;
    right: -6px !important;
    width: 22px !important;
    height: 22px !important;
    border-radius: 50% !important;
    background: rgba(0, 0, 0, 0.6) !important;
    color: #fff !important;
    border: none !important;
    font-size: 11px !important;
    cursor: pointer !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    text-decoration: none !important;
    z-index: 2 !important;
    line-height: 1 !important;
}

.dropzone .dz-preview .dz-remove:hover {
    background: rgba(239, 68, 68, 0.85) !important;
}

.dropzone .dz-preview .dz-details {
    display: none !important;
}

.dropzone .dz-preview .dz-error-message,
.dropzone .dz-preview .dz-success-mark,
.dropzone .dz-preview .dz-error-mark {
    display: none !important;
}

.img-upload-area__icon {
    color: var(--gray-400, #9CA3AF);
    margin-bottom: 10px;
}

.img-upload-area__text {
    font-size: 13px;
    font-weight: 500;
    color: var(--gray-600, #4B5563);
    margin: 0;
}

.img-upload-area__hint {
    font-size: 11px;
    color: var(--gray-400, #9CA3AF);
    margin: 4px 0 0;
}

/* ── Images Grid (existing images) ──────────────────────── */
.img-grid {
    display: grid !important;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)) !important;
    gap: 12px !important;
    margin-top: 16px !important;
}

.img-grid-item {
    position: relative;
    border-radius: 10px;
    overflow: hidden;
    border: 0.5px solid #E5E7EB;
    background: #F9FAFB;
    aspect-ratio: 4/3;
}

.img-grid-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.img-grid-item__remove {
    position: absolute;
    top: 6px;
    right: 6px;
    width: 26px;
    height: 26px;
    border-radius: 50%;
    background: rgba(0, 0, 0, 0.55);
    color: #fff;
    border: none;
    font-size: 12px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all .15s ease;
    opacity: 0;
    z-index: 2;
}

.img-grid-item:hover .img-grid-item__remove {
    opacity: 1;
}

.img-grid-item__remove:hover {
    background: rgba(239, 68, 68, 0.85);
    transform: scale(1.1);
}

/* ── Empty State ────────────────────────────────────────── */
.img-empty-state {
    text-align: center;
    padding: 30px 20px;
    color: var(--gray-400, #9CA3AF);
    display: block;
}

.img-empty-state__icon {
    margin-bottom: 10px;
}

.img-empty-state__text {
    font-size: 12px;
    margin: 0;
}

/* ── Responsive ─────────────────────────────────────────── */
@media (max-width: 768px) {
    .img-grid {
        grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)) !important;
        gap: 8px !important;
    }
    
    .img-thumbnail-preview {
        width: 140px;
        height: 105px;
    }
    
    .dropzone .dz-preview .dz-image {
        width: 100px !important;
        height: 75px !important;
    }
}
</style>

<div class="img-wrap">
    <form class="ajax" action="{{ route('owner.property.image.store') }}" method="post" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="property_id" class="d-none property_id" value="{{ $property->id }}">

        {{-- ── Thumbnail Card ─────────────────────────────────── --}}
        <div class="img-card">
            <div class="img-card__head">
                <div class="img-card__head-icon">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="3" width="18" height="18" rx="2"/>
                        <circle cx="8.5" cy="8.5" r="1.5"/>
                        <path d="M21 15l-5-5L5 21"/>
                    </svg>
                </div>
                <h4>{{ __('Thumbnail Image') }}</h4>
                <span class="img-card__head-badge">{{ __('Main Display') }}</span>
            </div>
            <div class="img-card__body">
                <p class="font-12 color-gray mb-20">
                    {{ __('This is the main image that will represent your property in listings and search results.') }}
                </p>
                <div class="img-thumbnail-preview">
                    <img src="{{ $property->thumbnail_image ?? asset('assets/images/no-image.jpg') }}"
                        class="app-logo-user-profile-image" 
                        alt="{{ __('Property Thumbnail') }}"
                        id="thumbnail-preview-img">
                    <div class="img-thumbnail-preview__overlay">
                        <div class="img-thumbnail-preview__overlay-icon">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M23 19a2 2 0 01-2 2H3a2 2 0 01-2-2V8a2 2 0 012-2h4l2-3h6l2 3h4a2 2 0 012 2z"/>
                                <circle cx="12" cy="13" r="4"/>
                            </svg>
                        </div>
                        <input id="app-logo-profile-img-file-input" type="file"
                            class="thumbnailImage app-logo-profile-img-file-input"
                            data-route="{{ route('owner.property.thumbnailImage.update', $property->id) }}"
                            onchange="previewThumbnail(this)">
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Gallery Images Card ────────────────────────────── --}}
        <div class="img-card">
            <div class="img-card__head">
                <div class="img-card__head-icon">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="3" width="18" height="18" rx="2"/>
                        <path d="M3 9h18M9 3v18"/>
                    </svg>
                </div>
                <h4>{{ __('Gallery Images') }}</h4>
                <span class="img-card__head-badge" style="background:#EFF6FF;color:#1E40AF;">
                    {{ __('Optional') }}
                </span>
            </div>
            <div class="img-card__body">
                <p class="font-12 color-gray mb-20">
                    {{ __('Upload additional images to showcase your property. Drag & drop or click to browse.') }}
                </p>
                
                {{-- Dropzone Area --}}
                <div class="dropzone" id="property-dropzone">
                    <div class="fallback">
                        <input name="file" type="file" multiple="multiple">
                    </div>
                    <div class="dz-message needsclick">
                        <div class="img-upload-area__icon">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/>
                                <polyline points="17 8 12 3 7 8"/>
                                <path d="M12 3v12"/>
                            </svg>
                        </div>
                        <p class="img-upload-area__text">{{ __('Drag & drop images here or click to browse') }}</p>
                        <p class="img-upload-area__hint">{{ __('PNG, JPG, JPEG up to 5MB each') }}</p>
                    </div>
                </div>

                {{-- Existing Images Grid --}}
                @if(count(@$property->propertyImages) > 0)
                    <div class="img-grid" id="dropzone-preview">
                        @foreach(@$property->propertyImages as $propertyImage)
                            <div class="img-grid-item">
                                <img src="{{ $propertyImage->single_image }}" 
                                    alt="{{ $propertyImage->file?->file_name ?? __('Property Image') }}" 
                                    loading="lazy">
                                <button type="button" 
                                    class="img-grid-item__remove removeImage"
                                    data-route="{{ route('owner.property.image.delete', $propertyImage->id) }}"
                                    title="{{ __('Remove image') }}">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                        <path d="M18 6L6 18M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="img-empty-state" id="dropzone-preview">
                        <div class="img-empty-state__icon">
                            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2">
                                <rect x="3" y="3" width="18" height="18" rx="2"/>
                                <circle cx="8.5" cy="8.5" r="1.5"/>
                                <path d="M21 15l-5-5L5 21"/>
                            </svg>
                        </div>
                        <p class="img-empty-state__text">{{ __('No additional images yet. Upload some above!') }}</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- ── Navigation Buttons ─────────────────────────────── --}}
        <div class="d-flex gap-2 mt-3">
            <button type="button" class="imageBack action-button-previous theme-btn">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right:4px;">
                    <path d="M19 12H5M12 19l-7-7 7-7"/>
                </svg>
                {{ __('Back') }}
            </button>
            <a href="{{ route('owner.property.allProperty') }}" class="action-button theme-btn flex-1 text-center">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right:4px;">
                    <path d="M22 11.08V12a10 10 0 11-5.93-9.14"/>
                    <polyline points="22 4 12 14.01 9 11.01"/>
                </svg>
                {{ __('Finish & View Properties') }}
            </a>
        </div>
    </form>
</div>

<script>
// ── Thumbnail Preview ─────────────────────────────────────
function previewThumbnail(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            var preview = document.getElementById('thumbnail-preview-img');
            if (preview) {
                preview.src = e.target.result;
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// ── Hide any lingering preloader ───────────────────────────
(function() {
    setTimeout(function() {
        if (typeof $.LoadingOverlay !== 'undefined') {
            try { $.LoadingOverlay("hide"); } catch(e) {}
        }
        $('.preloader, .loader, .loading-overlay, #preloader, .spinner-overlay').hide();
        $('.pace, .pace-progress, .pace-inactive').hide();
        $('body').removeClass('loading');
    }, 300);
})();
</script>