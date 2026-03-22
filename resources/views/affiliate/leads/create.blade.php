@extends('affiliate.layouts.app')

@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="page-content-wrapper bg-white p-30 radius-20">

                    {{-- Page Title --}}
                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box d-sm-flex align-items-center justify-content-between border-bottom mb-4 pb-2">
                                <div class="page-title-left">
                                    <h3 class="mb-sm-0">Leads</h3>
                                </div>
                                <div class="page-title-right">
                                    <ol class="breadcrumb mb-0">
                                        <li class="breadcrumb-item">
                                            <a href="{{ route('affiliate.dashboard') }}">Dashboard</a>
                                        </li>
                                        <li class="breadcrumb-item active">Create a Lead</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Back link --}}
                    <a href="{{ route('affiliate.leads') }}" class="mod-back-link mb-4 d-inline-flex align-items-center gap-2">
                        <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
                            <path d="M10 3L5 8l5 5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Back to Leads
                    </a>
                    {{-- Duplicate lead error --}}
                    @if(session('error'))
                        <div class="mod-alert mod-alert--danger mb-4">
                            <svg width="15" height="15" viewBox="0 0 16 16" fill="none" style="flex-shrink:0;">
                                <circle cx="8" cy="8" r="7" stroke="currentColor" stroke-width="1.5"/>
                                <path d="M8 5v4M8 11v.5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                            </svg>
                            <div>{{ session('error') }}</div>
                        </div>
                    @endif
                    {{-- Form card --}}
                    <div class="lc-card mt-3">
                        <div class="lc-card__head">
                            <div>
                                <h5 class="mb-0" style="font-weight:500;">Submit a Lead</h5>
                                <p class="mb-0 mt-1" style="font-size:13px;color:#9ca3af;">Fill in the details below to register a new lead.</p>
                            </div>
                        </div>

                        <div class="lc-card__body">
                            <form method="POST" action="{{ route('affiliate.leads.store') }}">
                                @csrf

                                {{-- Section: Company Info --}}
                                <div class="lc-divider mb-4"><span>Company Info</span></div>

                                <div class="row g-3 mb-4">
                                    <div class="col-md-6">
                                        <label class="lc-label">Company Name <span class="lc-required">*</span></label>
                                        <input type="text"
                                               name="company_name"
                                               class="lc-input"
                                               placeholder="e.g. Acme Properties Ltd"
                                               value="{{ old('company_name') }}"
                                               required>
                                        @error('company_name')
                                            <p class="lc-error">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div class="col-md-3">
                                        <label class="lc-label">Country <span class="lc-required">*</span></label>
                                        <select name="country" class="lc-input lc-select" required>
                                            <option value="">Select country</option>
                                            <option value="Kenya"        {{ old('country') == 'Kenya'        ? 'selected' : '' }}>Kenya</option>
                                            <option value="Uganda"       {{ old('country') == 'Uganda'       ? 'selected' : '' }}>Uganda</option>
                                            <option value="Tanzania"     {{ old('country') == 'Tanzania'     ? 'selected' : '' }}>Tanzania</option>
                                            <option value="Nigeria"      {{ old('country') == 'Nigeria'      ? 'selected' : '' }}>Nigeria</option>
                                            <option value="South Africa" {{ old('country') == 'South Africa' ? 'selected' : '' }}>South Africa</option>
                                            <option value="United Kingdom" {{ old('country') == 'United Kingdom' ? 'selected' : '' }}>United Kingdom</option>
                                            <option value="United States"  {{ old('country') == 'United States'  ? 'selected' : '' }}>United States</option>
                                        </select>
                                        @error('country')
                                            <p class="lc-error">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div class="col-md-3">
                                        <label class="lc-label">City</label>
                                        <input type="text"
                                               name="city"
                                               class="lc-input"
                                               placeholder="e.g. Nairobi"
                                               value="{{ old('city') }}">
                                    </div>
                                </div>

                                <div class="row g-3 mb-4">
                                    <div class="col-md-6">
                                        <label class="lc-label">Phone Number <span class="lc-required">*</span></label>
                                        <input type="tel"
                                               name="phone"
                                               class="lc-input"
                                               placeholder="+254 712 345 678"
                                               value="{{ old('phone') }}"
                                               required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="lc-label">Estimated Units</label>
                                        <input type="number"
                                               name="estimated_units"
                                               class="lc-input"
                                               placeholder="e.g. 24"
                                               min="1"
                                               value="{{ old('estimated_units') }}">
                                    </div>
                                </div>
                                {{-- Email + Website --}}
                                <div class="row g-3 mb-4">
                                    <div class="col-md-6">
                                        <label class="lc-label">Email (optional)</label>
                                        <input type="email"
                                            name="email"
                                            class="lc-input"
                                            placeholder="e.g. info@acme.com"
                                            value="{{ old('email') }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="lc-label">Website (optional)</label>
                                        <input type="url"
                                            name="website"
                                            class="lc-input"
                                            placeholder="e.g. https://acme.com"
                                            value="{{ old('website') }}">
                                    </div>
                                </div>

                                {{-- Property Type --}}
                                <div class="row g-3 mb-4">
                                    <div class="col-md-6">
                                        <label class="lc-label">Property Type</label>
                                        <select name="property_type" class="lc-input lc-select">
                                            <option value="">Select property type</option>
                                            <option value="apartment"       {{ old('property_type') == 'apartment'       ? 'selected' : '' }}>Apartment</option>
                                            <option value="commercial"      {{ old('property_type') == 'commercial'      ? 'selected' : '' }}>Commercial</option>
                                            <option value="mixed_use"       {{ old('property_type') == 'mixed_use'       ? 'selected' : '' }}>Mixed Use</option>
                                            <option value="student_housing" {{ old('property_type') == 'student_housing' ? 'selected' : '' }}>Student Housing</option>
                                            <option value="other"           {{ old('property_type') == 'other'           ? 'selected' : '' }}>Other</option>
                                        </select>
                                    </div>
                                </div>

                                {{-- Section: Contact Person --}}
                                <div class="lc-divider mb-4"><span>Contact Person</span></div>

                                <div class="row g-3 mb-4">
                                    <div class="col-md-6">
                                        <label class="lc-label">Contact Person Name <span class="lc-required">*</span></label>
                                        <input type="text"
                                               name="contact_person_name"
                                               class="lc-input"
                                               placeholder="Full name"
                                               value="{{ old('contact_person_name') }}"
                                               required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="lc-label">Contact Person Role <span class="lc-required">*</span></label>
                                        <select name="contact_person_role" class="lc-input lc-select" required>
                                            <option value="owner"            {{ old('contact_person_role') == 'owner'            ? 'selected' : '' }}>Owner</option>
                                            <option value="property_manager" {{ old('contact_person_role') == 'property_manager' ? 'selected' : '' }}>Property Manager</option>
                                            <option value="caretaker"        {{ old('contact_person_role') == 'caretaker'        ? 'selected' : '' }}>Caretaker</option>
                                            <option value="unknown"          {{ old('contact_person_role') == 'unknown'          ? 'selected' : '' }}>Unknown</option>
                                        </select>
                                    </div>
                                </div>

                                {{-- Section: Lead Details --}}
                                <div class="lc-divider mb-4"><span>Lead Details</span></div>

                                <div class="row g-3 mb-4">
                                    <div class="col-md-4">
                                        <label class="lc-label">Temperature</label>
                                        <div class="lc-temp-group">
                                            @foreach(['cold' => ['label'=>'Cold','color'=>'blue'], 'warm' => ['label'=>'Warm','color'=>'amber'], 'hot' => ['label'=>'Hot','color'=>'coral']] as $val => $meta)
                                                <label class="lc-temp-option lc-temp-option--{{ $meta['color'] }}">
                                                    <input type="radio"
                                                           name="temperature"
                                                           value="{{ $val }}"
                                                           {{ old('temperature', 'cold') == $val ? 'checked' : '' }}>
                                                    <span>{{ $meta['label'] }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>

                                {{-- Divider + Actions --}}
                                <div class="lc-divider mb-4"></div>

                                <div class="d-flex align-items-center gap-3">
                                    <button type="submit" class="lc-btn-primary">
                                        <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
                                            <path d="M3 8.5l3.5 3.5 6.5-7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                        Submit Lead
                                    </button>
                                    <a href="{{ route('affiliate.leads') }}" class="lc-btn-ghost">Cancel</a>
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
        .mod-back-link {
            font-size: 13px;
            font-weight: 500;
            color: #6b7280;
            text-decoration: none;
            transition: color .15s;
        }
        .mod-back-link:hover { color: #111827; }

        /* ── Form card ───────────────────────────────────────── */
        .lc-card {
            background: #fff;
            border: 0.5px solid #e5e7eb;
            border-radius: 14px;
            overflow: hidden;
        }
        .lc-card__head {
            padding: 1.1rem 1.5rem;
            border-bottom: 0.5px solid #e5e7eb;
            background: #fafafa;
        }
        .lc-card__body { padding: 1.5rem; }

        /* ── Labels ──────────────────────────────────────────── */
        .lc-label {
            display: block;
            font-size: 12px;
            font-weight: 500;
            color: #374151;
            text-transform: uppercase;
            letter-spacing: .05em;
            margin-bottom: 6px;
        }
        .lc-required { color: #E24B4A; }

        /* ── Inputs ──────────────────────────────────────────── */
        .lc-input {
            width: 100%;
            padding: 9px 12px;
            font-size: 14px;
            color: #111827;
            background: #fff;
            border: 0.5px solid #d1d5db;
            border-radius: 8px;
            outline: none;
            transition: border-color .15s, box-shadow .15s;
            appearance: none;
        }
        .lc-input:focus {
            border-color: #185FA5;
            box-shadow: 0 0 0 3px rgba(24,95,165,.1);
        }
        .lc-select {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 16 16' fill='none'%3E%3Cpath d='M4 6l4 4 4-4' stroke='%236b7280' stroke-width='1.5' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 12px center;
            padding-right: 36px;
            cursor: pointer;
        }
        .lc-error    { font-size: 12px; color: #A32D2D; margin: 4px 0 0; }

        /* ── Divider ─────────────────────────────────────────── */
        .lc-divider {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 11px;
            font-weight: 500;
            color: #9ca3af;
            text-transform: uppercase;
            letter-spacing: .06em;
        }
        .lc-divider::before,
        .lc-divider::after {
            content: '';
            flex: 1;
            height: 0.5px;
            background: #e5e7eb;
        }
        .lc-divider:empty::after { display: none; }

        /* ── Temperature radio group ─────────────────────────── */
        .lc-temp-group {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        .lc-temp-option {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 14px;
            border-radius: 8px;
            border: 0.5px solid #e5e7eb;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            transition: background .15s, border-color .15s;
            background: #fafafa;
            color: #6b7280;
        }
        .lc-temp-option input[type="radio"] { display: none; }

        /* Selected states */
        .lc-temp-option--blue:has(input:checked)  { background:#E6F1FB; border-color:#B5D4F4; color:#185FA5; }
        .lc-temp-option--amber:has(input:checked) { background:#FAEEDA; border-color:#FAC775; color:#854F0B; }
        .lc-temp-option--coral:has(input:checked) { background:#FAECE7; border-color:#F5C4B3; color:#993C1D; }

        /* Hover states */
        .lc-temp-option--blue:hover  { background:#EEF5FD; border-color:#B5D4F4; color:#185FA5; }
        .lc-temp-option--amber:hover { background:#FEF9EE; border-color:#FAC775; color:#854F0B; }
        .lc-temp-option--coral:hover { background:#FDF4F1; border-color:#F5C4B3; color:#993C1D; }

        /* ── Submit button ───────────────────────────────────── */
        .lc-btn-primary {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 9px 20px;
            background: #185FA5;
            color: #fff;
            font-size: 13px;
            font-weight: 500;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            transition: background .2s, transform .2s, box-shadow .2s;
        }
        .lc-btn-primary:hover {
            background: #0C447C;
            transform: translateY(-1px);
            box-shadow: 0 5px 14px rgba(24,95,165,.22);
        }

        /* ── Cancel ghost button ─────────────────────────────── */
        .lc-btn-ghost {
            display: inline-flex;
            align-items: center;
            padding: 9px 18px;
            font-size: 13px;
            font-weight: 500;
            color: #6b7280;
            background: transparent;
            border: 0.5px solid #d1d5db;
            border-radius: 8px;
            text-decoration: none;
            transition: background .15s, color .15s;
        }
        .lc-btn-ghost:hover { background: #f3f4f6; color: #111827; }
        
        /* ── Alert ─────────────────────────────── */
        .mod-alert {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: .85rem 1.1rem;
            border-radius: 10px;
            font-size: 14px;
        }
        .mod-alert--danger { background: #FCEBEB; color: #A32D2D; }
    </style>
@endsection