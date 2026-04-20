@extends('admin.layouts.app')

@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="page-content-wrapper bg-white p-30 radius-20">

                    {{-- Page Title --}}
                    @php
                        $pageTitle = 'Marketplace Create';
                    @endphp
                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box d-sm-flex align-items-center justify-content-between border-bottom mb-4 pb-2">
                                <div class="page-title-left">
                                    <h3 class="mb-sm-0">Lead Marketplace</h3>
                                </div>
                                <div class="page-title-right">
                                    <ol class="breadcrumb mb-0">
                                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                                        <li class="breadcrumb-item"><a href="{{ route('admin.marketplace.index') }}">Marketplace</a></li>
                                        <li class="breadcrumb-item active">Add Lead</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Back link --}}
                    <a href="{{ route('admin.marketplace.index') }}" class="mod-back-link mb-4 d-inline-flex align-items-center gap-2">
                        <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
                            <path d="M10 3L5 8l5 5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Back to Marketplace
                    </a>

                    {{-- Error flash --}}
                    @if(session('error'))
                        <div class="mod-alert mod-alert--danger mb-4">
                            <svg width="15" height="15" viewBox="0 0 16 16" fill="none" style="flex-shrink:0;">
                                <circle cx="8" cy="8" r="7" stroke="currentColor" stroke-width="1.5"/>
                                <path d="M8 5v4M8 11v.5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                            </svg>
                            <div>{{ session('error') }}</div>
                        </div>
                    @endif

                    {{-- Context banner --}}
                    <div class="mp-info-banner mb-4">
                        <div class="mp-info-banner__icon">
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                <path d="M3 3h10a1 1 0 0 1 1 1v6a1 1 0 0 1-1 1H9l-3 2v-2H3a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1z" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <div>
                            <div class="mp-info-banner__title">Loading a lead into the marketplace</div>
                            <div class="mp-info-banner__text">
                                This lead will be visible to all affiliates. Once claimed, the affiliate owns it for <strong>60 days</strong>. If it expires unclaimed or is returned, it cycles back here automatically.
                                The more complete the lead profile, the faster it gets claimed.
                            </div>
                        </div>
                    </div>

                    {{-- Live completeness meter --}}
                    <div class="mp-completeness-card mb-4" id="completenessCard">
                        <div class="mp-completeness-card__left">
                            <span class="mp-completeness-label">Profile completeness</span>
                            <span class="mp-completeness-hint">Fill in more fields to improve claim rate</span>
                        </div>
                        <div class="mp-completeness-card__right">
                            <div class="mp-completeness-bar-wrap">
                                <div class="mp-completeness-bar" id="completenessBar" style="width:0%"></div>
                            </div>
                            <span class="mp-completeness-pct" id="completenessPct">0%</span>
                        </div>
                    </div>

                    {{-- Form card --}}
                    <div class="lc-card mt-3">
                        <div class="lc-card__head">
                            <div>
                                <h5 class="mb-0" style="font-weight:500;">Lead Details</h5>
                                <p class="mb-0 mt-1" style="font-size:13px;color:#9ca3af;">
                                    Fields marked <span style="color:#E24B4A;">*</span> are required. All others improve lead quality.
                                </p>
                            </div>
                        </div>

                        <div class="lc-card__body">
                            <form method="POST" action="{{ route('admin.marketplace.store') }}" id="marketplaceForm">
                                @csrf

                                {{-- ── COMPANY INFO ─────────────────────────────── --}}
                                <div class="lc-divider mb-4"><span>Company Info</span></div>

                                <div class="row g-3 mb-4">
                                    <div class="col-md-6">
                                        <label class="lc-label">Company Name <span class="lc-required">*</span></label>
                                        <input type="text"
                                               name="company_name"
                                               class="lc-input mp-tracked"
                                               data-field="company_name"
                                               placeholder="e.g. Sunrise Apartments Ltd"
                                               value="{{ old('company_name') }}"
                                               required>
                                        @error('company_name')<p class="lc-error">{{ $message }}</p>@enderror
                                    </div>

                                    <div class="col-md-3">
                                        <label class="lc-label">Country <span class="lc-required">*</span></label>
                                        <select name="country" id="countrySelect" class="lc-input lc-select mp-tracked" data-field="country" required>
                                            <option value="">Select country</option>
                                            @foreach(country() as $code => $name)
                                                <option value="{{ $name }}" data-iso="{{ $code }}"
                                                    {{ old('country') === $name ? 'selected' : '' }}>
                                                    {{ $name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('country')
                                            <p class="lc-error">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div class="col-md-3">
                                        <label class="lc-label">City</label>
                                        <input type="text"
                                               name="city"
                                               class="lc-input mp-tracked"
                                               data-field="city"
                                               placeholder="e.g. Nairobi"
                                               value="{{ old('city') }}">
                                    </div>
                                </div>

                                <div class="row g-3 mb-4">
                                    <div class="col-md-6">
                                        <label class="lc-label">Phone Number <span class="lc-required">*</span></label>
                                        <div class="lc-phone-wrap">
                                            <span class="lc-dial-code" id="dialCodeDisplay">+254</span>
                                            <input type="tel"
                                                   id="phoneInput"
                                                   class="lc-input lc-phone-input mp-tracked"
                                                   data-field="phone"
                                                   placeholder="712 345 678"
                                                   value="{{ old('phone') }}">
                                        </div>
                                        <input type="hidden" name="phone" id="phoneHidden" value="{{ old('phone') }}">
                                        @error('phone')<p class="lc-error">{{ $message }}</p>@enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label class="lc-label">Estimated Units</label>
                                        <input type="number"
                                               name="estimated_units"
                                               class="lc-input mp-tracked"
                                               data-field="estimated_units"
                                               placeholder="e.g. 48"
                                               min="1"
                                               value="{{ old('estimated_units') }}">
                                    </div>
                                </div>

                                <div class="row g-3 mb-4">
                                    <div class="col-md-6">
                                        <label class="lc-label">Email</label>
                                        <input type="email"
                                               name="email"
                                               class="lc-input mp-tracked"
                                               data-field="email"
                                               placeholder="e.g. info@sunrise.co.ke"
                                               value="{{ old('email') }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="lc-label">Website</label>
                                        <input type="url"
                                               name="website"
                                               class="lc-input mp-tracked"
                                               data-field="website"
                                               placeholder="e.g. https://sunrise.co.ke"
                                               value="{{ old('website') }}">
                                    </div>
                                </div>

                                <div class="row g-3 mb-4">
                                    <div class="col-md-6">
                                        <label class="lc-label">Property Type</label>
                                        <select name="property_type" class="lc-input lc-select mp-tracked" data-field="property_type">
                                            <option value="">Select property type</option>
                                            <option value="apartment"       {{ old('property_type') == 'apartment'       ? 'selected' : '' }}>Apartment</option>
                                            <option value="commercial"      {{ old('property_type') == 'commercial'      ? 'selected' : '' }}>Commercial</option>
                                            <option value="mixed_use"       {{ old('property_type') == 'mixed_use'       ? 'selected' : '' }}>Mixed Use</option>
                                            <option value="student_housing" {{ old('property_type') == 'student_housing' ? 'selected' : '' }}>Student Housing</option>
                                            <option value="other"           {{ old('property_type') == 'other'           ? 'selected' : '' }}>Other</option>
                                        </select>
                                    </div>
                                </div>

                                {{-- ── CONTACT PERSON ───────────────────────────── --}}
                                <div class="lc-divider mb-4"><span>Contact Person</span></div>

                                <div class="row g-3 mb-4">
                                    <div class="col-md-6">
                                        <label class="lc-label">Contact Person Name <span class="lc-required">*</span></label>
                                        <input type="text"
                                               name="contact_person_name"
                                               class="lc-input mp-tracked"
                                               data-field="contact_person_name"
                                               placeholder="Full name"
                                               value="{{ old('contact_person_name') }}"
                                               required>
                                        @error('contact_person_name')<p class="lc-error">{{ $message }}</p>@enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="lc-label">Contact Person Role <span class="lc-required">*</span></label>
                                        <select name="contact_person_role" class="lc-input lc-select mp-tracked" data-field="contact_person_role" required>
                                            <option value="">Select role</option>
                                            <option value="owner"            {{ old('contact_person_role') == 'owner'            ? 'selected' : '' }}>Owner</option>
                                            <option value="property_manager" {{ old('contact_person_role') == 'property_manager' ? 'selected' : '' }}>Property Manager</option>
                                            <option value="caretaker"        {{ old('contact_person_role') == 'caretaker'        ? 'selected' : '' }}>Caretaker</option>
                                            <option value="unknown"          {{ old('contact_person_role') == 'unknown'          ? 'selected' : '' }}>Unknown</option>
                                        </select>
                                        @error('contact_person_role')<p class="lc-error">{{ $message }}</p>@enderror
                                    </div>
                                </div>

                                {{-- ── LEAD DETAILS ─────────────────────────────── --}}
                                <div class="lc-divider mb-4"><span>Lead Details</span></div>

                                <div class="row g-3 mb-4">
                                    <div class="col-md-4">
                                        <label class="lc-label">Temperature</label>
                                        <div class="lc-temp-group">
                                            @foreach(['cold' => ['label'=>'Cold','color'=>'blue'], 'warm' => ['label'=>'Warm','color'=>'amber'], 'hot' => ['label'=>'Hot','color'=>'coral']] as $val => $meta)
                                                <label class="lc-temp-option lc-temp-option--{{ $meta['color'] }}">
                                                    <input type="radio" name="temperature" value="{{ $val }}"
                                                           {{ old('temperature', 'cold') == $val ? 'checked' : '' }}>
                                                    <span>{{ $meta['label'] }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>

                                    <div class="col-md-8">
                                        <label class="lc-label">Admin Notes (internal — not shown to affiliates)</label>
                                        <textarea name="admin_notes"
                                                  class="lc-input lc-textarea"
                                                  rows="3"
                                                  placeholder="Any context that might help the affiliate — e.g. source of lead, prior contact, known objections…">{{ old('admin_notes') }}</textarea>
                                    </div>
                                </div>

                                {{-- Divider + Actions --}}
                                <div class="lc-divider mb-4"></div>

                                <div class="d-flex align-items-center gap-3">
                                    <button type="submit" class="lc-btn-primary">
                                        <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
                                            <path d="M3 3h10a1 1 0 0 1 1 1v6a1 1 0 0 1-1 1H9l-3 2v-2H3a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                        Publish to Marketplace
                                    </button>
                                    <a href="{{ route('admin.marketplace.index') }}" class="lc-btn-ghost">Cancel</a>
                                </div>

                            </form>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <style>
        /* ── Back link ───────────────────────────────────────── */
        .mod-back-link { font-size:13px;font-weight:500;color:#6b7280;text-decoration:none;transition:color .15s; }
        .mod-back-link:hover { color:#111827; }

        /* ── Alerts ──────────────────────────────────────────── */
        .mod-alert { display:flex;align-items:center;gap:10px;padding:.85rem 1.1rem;border-radius:10px;font-size:14px; }
        .mod-alert--danger { background:#FCEBEB;color:#A32D2D; }

        /* ── Info banner ─────────────────────────────────────── */
        .mp-info-banner {
            display:flex;align-items:flex-start;gap:14px;
            background:#EEF5FD;border:0.5px solid #B5D4F4;border-radius:12px;
            padding:1rem 1.25rem;color:#185FA5;
        }
        .mp-info-banner__icon {
            width:32px;height:32px;border-radius:8px;background:#E6F1FB;
            display:flex;align-items:center;justify-content:center;flex-shrink:0;
        }
        .mp-info-banner__title { font-size:13px;font-weight:500;margin-bottom:3px; }
        .mp-info-banner__text  { font-size:12px;line-height:1.6;opacity:.85; }

        /* ── Completeness card ───────────────────────────────── */
        .mp-completeness-card {
            display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap;
            background:#fafafa;border:0.5px solid #e5e7eb;border-radius:12px;padding:1rem 1.25rem;
        }
        .mp-completeness-card__left { display:flex;flex-direction:column;gap:2px; }
        .mp-completeness-label { font-size:12px;font-weight:500;color:#374151;text-transform:uppercase;letter-spacing:.05em; }
        .mp-completeness-hint  { font-size:12px;color:#9ca3af; }
        .mp-completeness-card__right { display:flex;align-items:center;gap:12px;flex:1;max-width:420px; }
        .mp-completeness-bar-wrap {
            flex:1;height:8px;background:#e5e7eb;border-radius:99px;overflow:hidden;
        }
        .mp-completeness-bar {
            height:100%;border-radius:99px;background:#185FA5;
            transition:width .4s cubic-bezier(.4,0,.2,1), background .3s;
        }
        .mp-completeness-pct {
            font-size:13px;font-weight:500;color:#374151;min-width:36px;text-align:right;
        }

        /* ── Form card ───────────────────────────────────────── */
        .lc-card { background:#fff;border:0.5px solid #e5e7eb;border-radius:14px;overflow:hidden; }
        .lc-card__head { padding:1.1rem 1.5rem;border-bottom:0.5px solid #e5e7eb;background:#fafafa; }
        .lc-card__body { padding:1.5rem; }

        /* ── Labels ──────────────────────────────────────────── */
        .lc-label { display:block;font-size:12px;font-weight:500;color:#374151;text-transform:uppercase;letter-spacing:.05em;margin-bottom:6px; }
        .lc-required { color:#E24B4A; }
        .lc-error { font-size:12px;color:#A32D2D;margin:4px 0 0; }

        /* ── Inputs ──────────────────────────────────────────── */
        .lc-input {
            width:100%;padding:9px 12px;font-size:14px;color:#111827;
            background:#fff;border:0.5px solid #d1d5db;border-radius:8px;
            outline:none;transition:border-color .15s,box-shadow .15s;appearance:none;
        }
        .lc-input:focus { border-color:#185FA5;box-shadow:0 0 0 3px rgba(24,95,165,.1); }
        .lc-select {
            background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 16 16' fill='none'%3E%3Cpath d='M4 6l4 4 4-4' stroke='%236b7280' stroke-width='1.5' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
            background-repeat:no-repeat;background-position:right 12px center;padding-right:36px;cursor:pointer;
        }
        .lc-textarea { resize:vertical;min-height:80px; }

        /* ── Divider ─────────────────────────────────────────── */
        .lc-divider { display:flex;align-items:center;gap:10px;font-size:11px;font-weight:500;color:#9ca3af;text-transform:uppercase;letter-spacing:.06em; }
        .lc-divider::before,.lc-divider::after { content:'';flex:1;height:0.5px;background:#e5e7eb; }
        .lc-divider:empty::after { display:none; }

        /* ── Temperature ─────────────────────────────────────── */
        .lc-temp-group { display:flex;gap:8px;flex-wrap:wrap; }
        .lc-temp-option { display:inline-flex;align-items:center;gap:6px;padding:7px 14px;border-radius:8px;border:0.5px solid #e5e7eb;font-size:13px;font-weight:500;cursor:pointer;background:#fafafa;color:#6b7280;transition:background .15s,border-color .15s; }
        .lc-temp-option input[type="radio"] { display:none; }
        .lc-temp-option--blue:has(input:checked)  { background:#E6F1FB;border-color:#B5D4F4;color:#185FA5; }
        .lc-temp-option--amber:has(input:checked) { background:#FAEEDA;border-color:#FAC775;color:#854F0B; }
        .lc-temp-option--coral:has(input:checked) { background:#FAECE7;border-color:#F5C4B3;color:#993C1D; }
        .lc-temp-option--blue:hover  { background:#EEF5FD;border-color:#B5D4F4;color:#185FA5; }
        .lc-temp-option--amber:hover { background:#FEF9EE;border-color:#FAC775;color:#854F0B; }
        .lc-temp-option--coral:hover { background:#FDF4F1;border-color:#F5C4B3;color:#993C1D; }

        /* ── Buttons ─────────────────────────────────────────── */
        .lc-btn-primary { display:inline-flex;align-items:center;gap:7px;padding:9px 20px;background:#185FA5;color:#fff;font-size:13px;font-weight:500;border-radius:8px;border:none;cursor:pointer;transition:background .2s,transform .2s,box-shadow .2s; }
        .lc-btn-primary:hover { background:#0C447C;transform:translateY(-1px);box-shadow:0 5px 14px rgba(24,95,165,.22); }
        .lc-btn-ghost { display:inline-flex;align-items:center;padding:9px 18px;font-size:13px;font-weight:500;color:#6b7280;background:transparent;border:0.5px solid #d1d5db;border-radius:8px;text-decoration:none;transition:background .15s,color .15s; }
        .lc-btn-ghost:hover { background:#f3f4f6;color:#111827; }

        /* ── Phone prefix ────────────────────────────────────── */
        .lc-phone-wrap { display:flex;align-items:stretch;border:0.5px solid #d1d5db;border-radius:8px;overflow:hidden;transition:border-color .15s,box-shadow .15s; }
        .lc-phone-wrap:focus-within { border-color:#185FA5;box-shadow:0 0 0 3px rgba(24,95,165,.1); }
        .lc-dial-code { display:flex;align-items:center;padding:0 10px;font-size:14px;font-weight:500;color:#374151;background:#f9fafb;border-right:0.5px solid #d1d5db;white-space:nowrap;min-width:52px;justify-content:center; }
        .lc-phone-input { border:none !important;border-radius:0 !important;box-shadow:none !important;flex:1; }
        .lc-phone-input:focus { box-shadow:none !important;border-color:transparent !important; }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {

            // ── Dial code map ─────────────────────────────────────
            const dialCodes = {
                'af':'+93','al':'+355','dz':'+213','ad':'+376','ao':'+244','ag':'+1','ar':'+54','am':'+374','au':'+61','at':'+43','az':'+994','bs':'+1','bh':'+973','bd':'+880','bb':'+1','by':'+375','be':'+32','bz':'+501','bj':'+229','bt':'+975','bo':'+591','ba':'+387','bw':'+267','br':'+55','bn':'+673','bg':'+359','bf':'+226','bi':'+257','kh':'+855','cm':'+237','ca':'+1','cv':'+238','cf':'+236','td':'+235','cl':'+56','cn':'+86','co':'+57','km':'+269','cr':'+506','hr':'+385','cu':'+53','cy':'+357','cz':'+420','cg':'+242','dk':'+45','dj':'+253','dm':'+1','ec':'+593','eg':'+20','sv':'+503','gq':'+240','er':'+291','ee':'+372','et':'+251','fj':'+679','fi':'+358','fr':'+33','ga':'+241','gm':'+220','ge':'+995','de':'+49','gh':'+233','gr':'+30','gd':'+1','gt':'+502','gn':'+224','gy':'+592','ht':'+509','hn':'+504','hk':'+852','hu':'+36','is':'+354','in':'+91','id':'+62','ir':'+98','iq':'+964','ie':'+353','il':'+972','it':'+39','jm':'+1','jp':'+81','jo':'+962','kz':'+7','ke':'+254','ki':'+686','kp':'+850','kr':'+82','kw':'+965','kg':'+996','la':'+856','lv':'+371','lb':'+961','ls':'+266','lr':'+231','ly':'+218','li':'+423','lt':'+370','lu':'+352','mo':'+853','mk':'+389','mg':'+261','mw':'+265','my':'+60','mv':'+960','ml':'+223','mt':'+356','mr':'+222','mu':'+230','mx':'+52','fm':'+691','md':'+373','mc':'+377','mn':'+976','me':'+382','ma':'+212','mz':'+258','mm':'+95','na':'+264','nr':'+674','np':'+977','nl':'+31','nz':'+64','ni':'+505','ne':'+227','ng':'+234','no':'+47','om':'+968','pk':'+92','pw':'+680','ps':'+970','pa':'+507','pg':'+675','py':'+595','pe':'+51','ph':'+63','pl':'+48','pt':'+351','qa':'+974','ro':'+40','ru':'+7','rw':'+250','sa':'+966','sn':'+221','rs':'+381','sc':'+248','sg':'+65','sk':'+421','si':'+386','sb':'+677','so':'+252','za':'+27','es':'+34','lk':'+94','sd':'+249','sr':'+597','sz':'+268','se':'+46','ch':'+41','sy':'+963','tw':'+886','tj':'+992','tz':'+255','th':'+66','tg':'+228','to':'+676','tt':'+1','tn':'+216','tr':'+90','tm':'+993','tv':'+688','ug':'+256','ua':'+380','ae':'+971','gb':'+44','uy':'+598','uz':'+998','vu':'+678','ve':'+58','vn':'+84','ye':'+967','zm':'+260','zw':'+263',
            };

            const dialDisplay = document.getElementById('dialCodeDisplay');
            const phoneHidden = document.getElementById('phoneHidden');
            const phoneInput  = document.getElementById('phoneInput');
            const nameToIso   = {};

            document.querySelectorAll('#countrySelect option[data-iso]').forEach(opt => {
                nameToIso[opt.value] = opt.dataset.iso.toLowerCase();
            });

            document.getElementById('countrySelect').addEventListener('change', function () {
                const iso = nameToIso[this.value];
                dialDisplay.textContent = iso && dialCodes[iso] ? dialCodes[iso] : '+?';
                updateCompleteness();
            });

            const initial = document.getElementById('countrySelect').value;
            if (initial && nameToIso[initial]) {
                dialDisplay.textContent = dialCodes[nameToIso[initial]] || '+?';
            }

            document.getElementById('marketplaceForm').addEventListener('submit', function () {
                let raw = phoneInput.value.trim();
                if (raw.startsWith('0')) raw = raw.substring(1);
                raw = raw.replace(/\D/g, '');
                phoneHidden.value = raw ? dialDisplay.textContent + raw : '';
            });

            // ── Live completeness meter ───────────────────────────
            const tracked = [
                'company_name', 'country', 'city', 'phone',
                'email', 'website', 'property_type', 'estimated_units',
                'contact_person_name', 'contact_person_role'
            ];
            const bar = document.getElementById('completenessBar');
            const pct = document.getElementById('completenessPct');

            function updateCompleteness() {
                let filled = 0;
                tracked.forEach(field => {
                    // phone uses the visible input
                    const el = field === 'phone'
                        ? document.getElementById('phoneInput')
                        : document.querySelector(`[data-field="${field}"]`);
                    if (el && el.value && el.value.trim() !== '') filled++;
                });

                const score = Math.round((filled / tracked.length) * 100);
                bar.style.width = score + '%';
                pct.textContent = score + '%';

                // Color the bar based on score
                if (score < 40)      bar.style.background = '#E24B4A';
                else if (score < 70) bar.style.background = '#FAC775';
                else                 bar.style.background = '#1D9E75';
            }

            // Attach listeners to all tracked fields
            document.querySelectorAll('.mp-tracked').forEach(el => {
                el.addEventListener('input',  updateCompleteness);
                el.addEventListener('change', updateCompleteness);
            });

            // Initial run (handles old() repopulation)
            updateCompleteness();
        });
    </script>
@endsection