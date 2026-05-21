{{-- location.blade.php --}}
<style>
/* ── Location Step Styling ───────────────────────────────── */
.loc-wrap {
    font-size: 13px;
    color: var(--gray-700, #374151);
}

/* ── Location Type Toggle ────────────────────────────────── */
.loc-type-toggle {
    display: flex;
    gap: 8px;
    margin-bottom: 20px;
    background: #F3F4F6;
    border-radius: 10px;
    padding: 4px;
}

.loc-type-option {
    flex: 1;
    text-align: center;
    padding: 8px 16px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 500;
    cursor: pointer;
    transition: all .18s ease;
    color: var(--gray-600, #4B5563);
    background: transparent;
    border: none;
}

.loc-type-option.is-active {
    background: #fff;
    color: var(--gray-900, #111827);
    box-shadow: 0 1px 3px rgba(0,0,0,.08);
    font-weight: 600;
}

.loc-type-option:hover:not(.is-active) {
    color: var(--gray-900, #111827);
}

/* ── Form Card ───────────────────────────────────────────── */
.loc-form-card {
    background: #fff;
    border: 0.5px solid var(--blue-faint, #185ea56e);
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0,0,0,.04);
    margin-bottom: 20px;
}

.loc-form-card__head {
    padding: 16px 20px;
    border-bottom: 0.5px solid #E5E7EB;
    background: #FAFAFA;
    display: flex;
    align-items: center;
    gap: 10px;
}

.loc-form-card__head-icon {
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

.loc-form-card__head h4 {
    font-size: 13px;
    font-weight: 600;
    color: var(--gray-900, #111827);
    margin: 0;
}

.loc-form-card__head-badge {
    font-size: 10px;
    font-weight: 500;
    padding: 2px 8px;
    border-radius: 99px;
    background: #EFF6FF;
    color: #1E40AF;
    margin-left: auto;
}

.loc-form-card__body {
    padding: 20px;
}

/* ── Map Card ────────────────────────────────────────────── */
.loc-map-card {
    background: #fff;
    border: 0.5px solid var(--blue-faint, #185ea56e);
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0,0,0,.04);
    margin-bottom: 20px;
}

.loc-map-card__head {
    padding: 14px 20px;
    border-bottom: 0.5px solid #E5E7EB;
    background: #FAFAFA;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
}

.loc-map-card__head-left {
    display: flex;
    align-items: center;
    gap: 8px;
}

.loc-map-card__head-icon {
    width: 24px;
    height: 24px;
    border-radius: 6px;
    background: #FEF3C7;
    color: #D97706;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.loc-map-card__head h4 {
    font-size: 12px;
    font-weight: 600;
    color: var(--gray-900, #111827);
    margin: 0;
}

.loc-map-card__head-hint {
    font-size: 10px;
    color: var(--gray-400, #9CA3AF);
}

.loc-map-card__body {
    padding: 0;
    position: relative;
}

.loc-map-container {
    width: 100%;
    height: 300px;
    background: #F3F4F6;
    position: relative;
}

.loc-map-container iframe {
    width: 100%;
    height: 100%;
    border: none;
}

.loc-map-help {
    padding: 12px 20px;
    border-top: 0.5px solid #E5E7EB;
    background: #FFFBEB;
}

.loc-map-help__text {
    font-size: 11px;
    color: #92400E;
    line-height: 1.5;
    margin: 0;
}

.loc-map-help__link {
    color: #D97706;
    font-weight: 600;
    text-decoration: underline;
    cursor: pointer;
}

.loc-map-help__link:hover {
    color: #92400E;
}

/* ── Address Search ──────────────────────────────────────── */
.loc-search-wrapper {
    position: relative;
}

.loc-search-icon {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--gray-400, #9CA3AF);
    pointer-events: none;
}

.loc-search-input {
    padding-left: 36px !important;
}

.loc-search-btn {
    position: absolute;
    right: 4px;
    top: 4px;
    bottom: 4px;
    padding: 0 12px;
    border-radius: 6px;
    border: none;
    background: var(--blue, #185FA5);
    color: #fff;
    font-size: 11px;
    font-weight: 500;
    cursor: pointer;
    transition: all .15s ease;
    display: flex;
    align-items: center;
    gap: 4px;
}

.loc-search-btn:hover {
    background: var(--blue-hover, #0F4A84);
}

.loc-search-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

/* ── Coordinate Inputs ───────────────────────────────────── */
.loc-coords {
    display: flex;
    gap: 12px;
    align-items: center;
}

.loc-coords__input {
    flex: 1;
    position: relative;
}

.loc-coords__label {
    font-size: 10px;
    font-weight: 500;
    color: var(--gray-400, #9CA3AF);
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: 4px;
    display: block;
}

.loc-coords__value {
    font-size: 12px;
    font-weight: 500;
    color: var(--gray-700, #374151);
    padding: 7px 10px;
    background: #F9FAFB;
    border: 0.5px solid #E5E7EB;
    border-radius: 6px;
    width: 100%;
}

.loc-coords__divider {
    color: var(--gray-300, #D1D5DB);
    font-weight: 500;
    padding-top: 18px;
}

/* ── Responsive ──────────────────────────────────────────── */
@media (max-width: 768px) {
    .loc-map-container {
        height: 250px;
    }
    
    .loc-coords {
        flex-direction: column;
        gap: 8px;
    }
    
    .loc-coords__divider {
        display: none;
    }
}
</style>

<div class="loc-wrap">
    <form class="ajax" action="{{ route('owner.property.location.store') }}" method="post" data-handler="stepChange">
        @csrf
        <input type="hidden" name="property_id" class="d-none property_id" value="{{ $property->id }}">
        <input type="hidden" name="latitude" id="location_latitude" value="{{ @$property->propertyDetail->latitude }}">
        <input type="hidden" name="longitude" id="location_longitude" value="{{ @$property->propertyDetail->longitude }}">
        <input type="hidden" name="map_link" id="map_link" value="{{ @$property->propertyDetail->map_link }}">

        {{-- ── Location Input Type Toggle ──────────────────────── --}}
        <div class="loc-type-toggle">
            <button type="button" class="loc-type-option is-active" data-type="address" id="address-type-btn">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right:4px;">
                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/>
                    <circle cx="12" cy="10" r="3"/>
                </svg>
                {{ __('Search Address') }}
            </button>
            <button type="button" class="loc-type-option" data-type="coordinates" id="coordinates-type-btn">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right:4px;">
                    <circle cx="12" cy="12" r="10"/>
                    <path d="M12 2v20M2 12h20"/>
                </svg>
                {{ __('Enter Coordinates') }}
            </button>
        </div>

        {{-- ── Address Search Mode ─────────────────────────────── --}}
        <div id="address-mode">
            <div class="loc-form-card">
                <div class="loc-form-card__head">
                    <div class="loc-form-card__head-icon">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/>
                            <circle cx="12" cy="10" r="3"/>
                        </svg>
                    </div>
                    <h4>{{ __('Property Address') }}</h4>
                    <span class="loc-form-card__head-badge">{{ __('Recommended') }}</span>
                </div>
                <div class="loc-form-card__body">
                    {{-- Search bar --}}
                    <div class="loc-search-wrapper mb-25">
                        <div class="loc-search-icon">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="11" cy="11" r="8"/>
                                <path d="M21 21l-4.35-4.35"/>
                            </svg>
                        </div>
                        <input type="text" 
                            class="form-control loc-search-input" 
                            id="address_search" 
                            placeholder="{{ __('Search for an address or place...') }}"
                            value="{{ @$property->propertyDetail->address }}">
                        <button type="button" class="loc-search-btn" id="search-address-btn">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="11" cy="11" r="8"/>
                                <path d="M21 21l-4.35-4.35"/>
                            </svg>
                            {{ __('Find') }}
                        </button>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-25">
                            <label class="label-text-title color-heading font-medium mb-2">
                                {{ __('Country') }} <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="country_id" class="form-control" 
                                id="country_input"
                                placeholder="{{ __('Country') }}" 
                                value="{{ @$property->propertyDetail->country_id }}" required>
                        </div>
                        <div class="col-md-4 mb-25">
                            <label class="label-text-title color-heading font-medium mb-2">
                                {{ __('State') }} <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="state_id" class="form-control" 
                                id="state_input"
                                placeholder="{{ __('State') }}" 
                                value="{{ @$property->propertyDetail->state_id }}" required>
                        </div>
                        <div class="col-md-4 mb-25">
                            <label class="label-text-title color-heading font-medium mb-2">
                                {{ __('City') }} <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="city_id" class="form-control" 
                                id="city_input"
                                placeholder="{{ __('City') }}" 
                                value="{{ @$property->propertyDetail->city_id }}" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-25">
                            <label class="label-text-title color-heading font-medium mb-2">
                                {{ __('Zip Code') }}
                            </label>
                            <input type="text" name="zip_code" 
                                id="zip_input"
                                value="{{ @$property->propertyDetail->zip_code }}"
                                class="form-control" placeholder="{{ __('Zip Code') }}">
                        </div>
                        <div class="col-md-8 mb-25">
                            <label class="label-text-title color-heading font-medium mb-2">
                                {{ __('Address') }} <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="address" 
                                id="address_input"
                                value="{{ @$property->propertyDetail->address }}"
                                class="form-control" placeholder="{{ __('Full address') }}" required>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Coordinates Mode ────────────────────────────────── --}}
        <div id="coordinates-mode" style="display:none;">
            <div class="loc-form-card">
                <div class="loc-form-card__head">
                    <div class="loc-form-card__head-icon">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/>
                            <path d="M12 2v20M2 12h20"/>
                        </svg>
                    </div>
                    <h4>{{ __('GPS Coordinates') }}</h4>
                </div>
                <div class="loc-form-card__body">
                    <p class="font-12 color-gray mb-20">{{ __('Enter the exact latitude and longitude of the property.') }}</p>
                    <div class="loc-coords mb-25">
                        <div class="loc-coords__input">
                            <span class="loc-coords__label">{{ __('Latitude') }}</span>
                            <input type="text" class="loc-coords__value" id="lat_display" 
                                value="{{ @$property->propertyDetail->latitude }}"
                                placeholder="e.g. -1.2921">
                        </div>
                        <span class="loc-coords__divider">,</span>
                        <div class="loc-coords__input">
                            <span class="loc-coords__label">{{ __('Longitude') }}</span>
                            <input type="text" class="loc-coords__value" id="lng_display" 
                                value="{{ @$property->propertyDetail->longitude }}"
                                placeholder="e.g. 36.8219">
                        </div>
                        <button type="button" class="loc-search-btn" id="update-coords-btn" style="position:static;margin-top:18px;">
                            {{ __('Update Map') }}
                        </button>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mb-25">
                            <label class="label-text-title color-heading font-medium mb-2">{{ __('Address (Optional)') }}</label>
                            <input type="text" name="address_manual" class="form-control" 
                                placeholder="{{ __('Enter address manually') }}"
                                value="{{ @$property->propertyDetail->address }}">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Map Preview ─────────────────────────────────────── --}}
        <div class="loc-map-card">
            <div class="loc-map-card__head">
                <div class="loc-map-card__head-left">
                    <div class="loc-map-card__head-icon">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="3" width="18" height="18" rx="2"/>
                            <path d="M3 9h18M9 3v18"/>
                        </svg>
                    </div>
                    <h4>{{ __('Map Preview') }}</h4>
                </div>
                <span class="loc-map-card__head-hint">
                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right:3px;">
                        <circle cx="12" cy="12" r="10"/>
                        <path d="M12 16v-4M12 8h.01"/>
                    </svg>
                    {{ __('Click to load map') }}
                </span>
            </div>
            <div class="loc-map-card__body">
                <div class="loc-map-container" id="map-container">
                    {{-- Show clickable overlay instead of auto-loading --}}
                    <div id="map-placeholder" 
                        style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;flex-direction:column;color:#9CA3AF;cursor:pointer;"
                        onclick="loadMapIframe()">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin-bottom:12px;">
                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/>
                            <circle cx="12" cy="10" r="3"/>
                        </svg>
                        <span style="font-size:13px;font-weight:500;">
                            @if(@$property->propertyDetail->map_link)
                                {{ __('Click to load map') }}
                            @else
                                {{ __('Search an address to see the map') }}
                            @endif
                        </span>
                        <span style="font-size:11px;margin-top:4px;color:var(--blue, #185FA5);font-weight:500;">
                            {{ __('Click here to view') }}
                        </span>
                    </div>
                </div>
                <div class="loc-map-help">
                    <p class="loc-map-help__text">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right:4px;vertical-align:-2px;">
                            <circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/>
                        </svg>
                        {{ __('Map loads on click to improve performance.') }} 
                        <span class="loc-map-help__link" onclick="document.getElementById('map_link_manual').classList.toggle('d-none')">
                            {{ __('Paste Google Maps iframe link manually') }}
                        </span>
                    </p>
                    <div id="map_link_manual" class="d-none mt-2">
                        <input type="text" class="form-control" id="map_link_fallback" 
                            placeholder="{{ __('Paste Google Maps embed link here...') }}"
                            value="{{ @$property->propertyDetail->map_link }}">
                        <button type="button" class="theme-btn-outline mt-2" id="apply-fallback-link">
                            {{ __('Apply Link') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Navigation Buttons ─────────────────────────────── --}}
        <div class="d-flex gap-2">
            <button type="button" class="locationBack action-button-previous theme-btn mt-25">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right:4px;">
                    <path d="M19 12H5M12 19l-7-7 7-7"/>
                </svg>
                {{ __('Back') }}
            </button>
            <button type="submit" class="action-button theme-btn mt-25 flex-1">
                {{ __('Save & Go to Next') }}
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-left:4px;">
                    <path d="M5 12h14M13 6l6 6-6 6"/>
                </svg>
            </button>
        </div>
    </form>
</div>

<script>
// Defer all init out of jQuery's synchronous .html() / globalEval() call.
setTimeout(function () {
(function () {
 
    // ── Kill preloader ──────────────────────────────────────────
    function killPreloader() {
        if (typeof $.LoadingOverlay !== 'undefined') {
            try { $.LoadingOverlay('hide'); } catch (e) {}
        }
        document.querySelectorAll(
            '.preloader,.loader,.loading-overlay,#preloader,.spinner-overlay'
        ).forEach(function (el) { 
            if (el) el.style.display = 'none'; 
        });
        if (document.body) document.body.classList.remove('loading');
    }
    killPreloader();
 
    // ── Build iframe helper ─────────────────────────────────────
    function buildIframe(container, src) {
        if (!container) return;
        
        var placeholder = document.getElementById('map-placeholder');
        if (placeholder && placeholder.parentNode) placeholder.remove();
 
        var existing = document.getElementById('map_link_iframe');
        if (existing) existing.remove();
 
        var iframe = document.createElement('iframe');
        iframe.id = 'map_link_iframe';
        iframe.src = src;
        iframe.width = '100%';
        iframe.height = '100%';
        iframe.style.border = '0';
        iframe.setAttribute('allowfullscreen', '');
        iframe.setAttribute('loading', 'lazy');
        iframe.setAttribute('referrerpolicy', 'no-referrer-when-downgrade');
        container.appendChild(iframe);
    }
 
    // ── Tap-to-view map ─────────────────────────────────────────
    window.loadMapIframe = function () {
        console.log('loadMapIframe called');
        var mapContainer = document.getElementById('map-container');
        if (!mapContainer) {
            console.error('map-container not found');
            return;
        }
        if (document.getElementById('map_link_iframe')) {
            console.log('iframe already exists');
            return;
        }
 
        var mapLinkInput = document.getElementById('map_link');
        var savedLink = mapLinkInput ? mapLinkInput.value.trim() : '';
 
        var placeholder = document.getElementById('map-placeholder');
 
        if (savedLink && savedLink.length >= 10) {
            // Edit mode — saved link exists, load it
            if (placeholder) {
                placeholder.style.cursor = 'wait';
                placeholder.innerHTML =
                    '<span style="font-size:13px;font-weight:500;color:#9CA3AF;">Loading map…</span>';
            }
            setTimeout(function () { 
                buildIframe(mapContainer, savedLink); 
            }, 100);
        } else {
            // Create mode — no saved link yet
            if (placeholder) {
                placeholder.innerHTML =
                    '<svg width="40" height="40" viewBox="0 0 24 24" fill="none" ' +
                    'stroke="#9CA3AF" stroke-width="1.5" style="margin-bottom:10px;">' +
                    '<path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/>' +
                    '<circle cx="12" cy="10" r="3"/></svg>' +
                    '<span style="font-size:13px;font-weight:500;color:#6B7280;">' +
                    'Search an address above to see the map here</span>';
            }
        }
    };
    
    // Keep old reference for safety
    window.__locLoadMap = window.loadMapIframe;
 
    // ── updateMap — only called after explicit user action ──────
    function updateMap(lat, lng) {
        var latInput = document.getElementById('location_latitude');
        var lngInput = document.getElementById('location_longitude');
        var mapLink  = document.getElementById('map_link');
        var latDisp  = document.getElementById('lat_display');
        var lngDisp  = document.getElementById('lng_display');
 
        if (latInput) latInput.value = lat;
        if (lngInput) lngInput.value = lng;
        if (latDisp)  latDisp.value  = lat;
        if (lngDisp)  lngDisp.value  = lng;
 
        var embedUrl =
            'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3988.0!2d' +
            lng + '!3d' + lat +
            '!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2z' +
            '!5e0!3m2!1sen!2ske!4v1';
 
        if (mapLink) mapLink.value = embedUrl;
 
        var mapContainer = document.getElementById('map-container');
        if (mapContainer) {
            buildIframe(mapContainer, embedUrl);
        }
    }
 
    // ── reverseGeocode ──────────────────────────────────────────
    function reverseGeocode(lat, lng) {
        var controller = new AbortController();
        var tid = setTimeout(function () { controller.abort(); }, 5000);
 
        fetch(
            'https://nominatim.openstreetmap.org/reverse?format=json&lat=' +
            lat + '&lon=' + lng + '&addressdetails=1',
            { signal: controller.signal }
        )
        .then(function (r) { return r.json(); })
        .then(function (data) {
            clearTimeout(tid);
            if (!data || !data.address) return;
            var addr = data.address;
            var c  = document.getElementById('country_input');
            var s  = document.getElementById('state_input');
            var ci = document.getElementById('city_input');
            var z  = document.getElementById('zip_input');
            var a  = document.getElementById('address_input');
            if (c  && !c.value)  c.value  = addr.country || '';
            if (s  && !s.value)  s.value  = addr.state || addr.region || '';
            if (ci && !ci.value) ci.value = addr.city || addr.town || addr.village || '';
            if (z  && !z.value)  z.value  = addr.postcode || '';
            if (a  && !a.value)  a.value  = data.display_name || '';
        })
        .catch(function () { clearTimeout(tid); });
    }
 
    // ── Mode toggle ─────────────────────────────────────────────
    var addressModeBtn     = document.getElementById('address-type-btn');
    var coordinatesModeBtn = document.getElementById('coordinates-type-btn');
    var addressMode        = document.getElementById('address-mode');
    var coordinatesMode    = document.getElementById('coordinates-mode');
 
    if (addressModeBtn) {
        addressModeBtn.onclick = function () {
            addressModeBtn.classList.add('is-active');
            if (coordinatesModeBtn) coordinatesModeBtn.classList.remove('is-active');
            if (addressMode)    addressMode.style.display    = 'block';
            if (coordinatesMode) coordinatesMode.style.display = 'none';
        };
    }
    if (coordinatesModeBtn) {
        coordinatesModeBtn.onclick = function () {
            coordinatesModeBtn.classList.add('is-active');
            if (addressModeBtn) addressModeBtn.classList.remove('is-active');
            if (addressMode)    addressMode.style.display    = 'none';
            if (coordinatesMode) coordinatesMode.style.display = 'block';
        };
    }
 
    // ── Address search ──────────────────────────────────────────
    var searchBtn = document.getElementById('search-address-btn');
    if (searchBtn) {
        searchBtn.onclick = function () {
            var query = (document.getElementById('address_search') || {}).value || '';
            if (!query.trim()) {
                if (typeof toastr !== 'undefined') toastr.warning('Please enter an address to search');
                return;
            }
 
            var btn = this;
            btn.disabled = true;
            var origHtml = btn.innerHTML;
            btn.innerHTML =
                '<span class="spinner-border spinner-border-sm" ' +
                'style="width:12px;height:12px;"></span> Searching…';
 
            var controller = new AbortController();
            var tid = setTimeout(function () { controller.abort(); }, 15000);
 
            // FIXED: Use the SEARCH endpoint (not reverse) with the query string
            fetch(
                'https://nominatim.openstreetmap.org/search?format=json&q=' +
                encodeURIComponent(query) + '&limit=1&addressdetails=1',
                { signal: controller.signal }
            )
            .then(function (r) { return r.json(); })
            .then(function (data) {
                clearTimeout(tid);
                btn.disabled = false;
                btn.innerHTML = origHtml;
 
                if (data && data.length > 0) {
                    var result = data[0];
                    var addr   = result.address || {};
                    var c  = document.getElementById('country_input');
                    var s  = document.getElementById('state_input');
                    var ci = document.getElementById('city_input');
                    var z  = document.getElementById('zip_input');
                    var a  = document.getElementById('address_input');
 
                    if (c)  c.value  = addr.country || '';
                    if (s)  s.value  = addr.state || addr.region || '';
                    if (ci) ci.value = addr.city || addr.town || addr.village || '';
                    if (z)  z.value  = addr.postcode || '';
                    if (a)  a.value  = result.display_name || '';
 
                    updateMap(result.lat, result.lon);
                    if (typeof toastr !== 'undefined') toastr.success('Location found!');
                } else {
                    if (typeof toastr !== 'undefined') toastr.error('No results found. Try a different search.');
                }
            })
            .catch(function (err) {
                clearTimeout(tid);
                btn.disabled = false;
                btn.innerHTML = origHtml;
                var msg = err.name === 'AbortError'
                    ? 'Search timed out. Please try again.'
                    : 'Search failed. Please try again.';
                if (typeof toastr !== 'undefined') toastr.error(msg);
            });
        };
    }
 
    // Enter key triggers search
    var addressSearch = document.getElementById('address_search');
    if (addressSearch) {
        addressSearch.onkeypress = function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                var btn = document.getElementById('search-address-btn');
                if (btn) btn.click();
            }
        };
    }
 
    // ── Manual coordinate update ────────────────────────────────
    var updateCoordsBtn = document.getElementById('update-coords-btn');
    if (updateCoordsBtn) {
        updateCoordsBtn.onclick = function () {
            var lat = (document.getElementById('lat_display') || {}).value;
            var lng = (document.getElementById('lng_display') || {}).value;
            if (lat && lng) {
                updateMap(lat, lng);
                reverseGeocode(lat, lng);
                if (typeof toastr !== 'undefined') toastr.success('Map updated!');
            } else {
                if (typeof toastr !== 'undefined') toastr.warning('Please enter both latitude and longitude');
            }
        };
    }
 
    // ── Manual map link fallback ────────────────────────────────
    var applyFallbackBtn = document.getElementById('apply-fallback-link');
    if (applyFallbackBtn) {
        applyFallbackBtn.onclick = function () {
            var link        = (document.getElementById('map_link_fallback') || {}).value || '';
            var mapContainer = document.getElementById('map-container');
            var mapLinkInput = document.getElementById('map_link');
 
            if (link && mapContainer) {
                if (mapLinkInput) mapLinkInput.value = link;
                buildIframe(mapContainer, link);
                if (typeof toastr !== 'undefined') toastr.success('Map link applied!');
            }
        };
    }

    // Address search with retry
    var searchBtn = document.getElementById('search-address-btn');
    if (searchBtn) {
        searchBtn.onclick = function () {
            var query = (document.getElementById('address_search') || {}).value || '';
            if (!query.trim()) {
                if (typeof toastr !== 'undefined') toastr.warning('Please enter an address to search');
                return;
            }

            var btn = this;
            btn.disabled = true;
            var origHtml = btn.innerHTML;
            btn.innerHTML =
                '<span class="spinner-border spinner-border-sm" ' +
                'style="width:12px;height:12px;"></span> Searching…';

            // Attempt search with retry
            doNominatimSearch(query, btn, origHtml, 0);
        };
    }

    function doNominatimSearch(query, btn, origHtml, attempt) {
        var maxAttempts = 2;
        var controller = new AbortController();
        var tid = setTimeout(function () { controller.abort(); }, 15000); // 15 second timeout

        fetch(
            'https://nominatim.openstreetmap.org/search?format=json&q=' +
            encodeURIComponent(query) + '&limit=1&addressdetails=1',
            { signal: controller.signal }
        )
        .then(function (r) { return r.json(); })
        .then(function (data) {
            clearTimeout(tid);
            btn.disabled = false;
            btn.innerHTML = origHtml;

            if (data && data.length > 0) {
                var result = data[0];
                var addr = result.address || {};
                var c = document.getElementById('country_input');
                var s = document.getElementById('state_input');
                var ci = document.getElementById('city_input');
                var z = document.getElementById('zip_input');
                var a = document.getElementById('address_input');

                if (c) c.value = addr.country || '';
                if (s) s.value = addr.state || addr.region || '';
                if (ci) ci.value = addr.city || addr.town || addr.village || '';
                if (z) z.value = addr.postcode || '';
                if (a) a.value = result.display_name || '';

                updateMap(result.lat, result.lon);
                if (typeof toastr !== 'undefined') toastr.success('Location found!');
            } else {
                if (typeof toastr !== 'undefined') toastr.error('No results found. Try a different search.');
            }
        })
        .catch(function (err) {
            clearTimeout(tid);
            if (attempt < maxAttempts) {
                // Retry once
                console.log('Search attempt ' + (attempt + 1) + ' failed, retrying...');
                doNominatimSearch(query, btn, origHtml, attempt + 1);
            } else {
                btn.disabled = false;
                btn.innerHTML = origHtml;
                var msg = err.name === 'AbortError'
                    ? 'Search timed out. Please try again or enter coordinates manually.'
                    : 'Search failed. Please check your internet connection and try again.';
                if (typeof toastr !== 'undefined') toastr.error(msg);
            }
        });
    }
 
    // Belt-and-suspenders preloader kill after paint
    setTimeout(killPreloader, 200);
    
    console.log('Location script initialized successfully');
 
})();
}, 0); // end setTimeout
</script>