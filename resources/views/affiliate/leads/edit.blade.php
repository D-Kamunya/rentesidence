@extends('affiliate.layouts.app')

@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="page-content-wrapper bg-white p-30 radius-20">

                    {{-- Page Title --}}
                    @php
                        $pageTitle = 'Lead Edit';
                    @endphp
                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box d-sm-flex align-items-center justify-content-between border-bottom mb-4 pb-2">
                                <div class="page-title-left">
                                    <h3 class="mb-sm-0">Edit Lead</h3>
                                </div>
                                <div class="page-title-right">
                                    <ol class="breadcrumb mb-0">
                                        <li class="breadcrumb-item">
                                            <a href="{{ route('affiliate.dashboard') }}">Dashboard</a>
                                        </li>
                                        <li class="breadcrumb-item">
                                            <a href="{{ route('affiliate.leads') }}">Leads</a>
                                        </li>
                                        <li class="breadcrumb-item active">Edit Lead</li>
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
                    {{-- Form card --}}
                    <div class="lc-card mt-3">
                        <div class="lc-card__head">
                            <div>
                                <h5 class="mb-0" style="font-weight:500;">Edit Lead</h5>
                                <p class="mb-0 mt-1" style="font-size:13px;color:#9ca3af;">{{ $lead->company->company_name }}</p>
                            </div>
                            @php 
                                $status = strtolower($lead->status); 
                                
                                $latestTrialActivity = $lead->latestTrialActivity();
                                $isExpiredTrial = $lead->status === 'pending_conversion'
                                    && $latestTrialActivity?->type === 'trial_expired';
                                $isPendingExtention = $lead->status === 'pending_conversion'
                                    && $latestTrialActivity?->type === 'trial_extention';
                                $isTrialRequested = $lead->status === 'pending_conversion'
                                    && $latestTrialActivity?->type === 'trial_request';
                                $conversionRejectedByAdmin = $lead->status === 'demo_completed'
                                    && $latestTrialActivity?->type === 'conversion_rejected';
                                $rejectedByAffiliate = $lead->status === 'rejected';
                            @endphp
                            @if($conversionRejectedByAdmin)
                                <span class="leads-status-badge leads-status-badge--{{ $status }} ms-auto">
                                    <svg width="9" height="9" viewBox="0 0 16 16" fill="none">
                                        <path d="M8 2l6 12H2L8 2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M8 7v3M8 12v.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                    </svg>
                                    Needs Attention
                                </span>
                            @elseif($isExpiredTrial)
                                <span class="leads-status-badge leads-status-badge--{{ $status }} ms-auto"> 
                                    <svg width="9" height="9" viewBox="0 0 16 16" fill="none">
                                        <circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.5"/>
                                        <path d="M8 5v3.5l2 1.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    Trial Expired
                                </span>
                            @elseif($lead->status === 'pending_conversion' && $isPendingExtention)
                                <span class="leads-status-badge leads-status-badge--{{ $status }} ms-auto"> 
                                    <svg width="9" height="9" viewBox="0 0 16 16" fill="none">
                                        <circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.5"/>
                                        <path d="M8 5v3.5l2 1.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    Pending Trial Extension
                                </span>
                            @elseif($status === 'pending_conversion')
                                <span class="leads-status-badge ms-auto leads-status-badge--pending_conversion">
                                    <svg width="9" height="9" viewBox="0 0 16 16" fill="none">
                                        <circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.5"/>
                                        <path d="M8 5v3.5l2 1.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    Pending Approval
                                </span>
                            @elseif($status === 'trial')
                                <span class="leads-status-badge leads-status-badge--{{ $status }} ms-auto">
                                    <svg width="9" height="9" viewBox="0 0 16 16" fill="none">
                                        <path d="M8 2l2 4h4l-3 3 1 4-4-2-4 2 1-4-3-3h4z" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    Trial
                                </span>
                            @else
                                <span class="leads-status-badge leads-status-badge--{{ $status }} ms-auto">
                                    {{ ucfirst(str_replace('_', ' ', $lead->status)) }}
                                </span>
                            @endif
                        </div>

                        <div class="lc-card__body">
                            <form method="POST" action="{{ route('affiliate.leads.update', $lead->id) }}">
                                @csrf

                                {{-- Contact Person --}}
                                <div class="lc-divider mb-4"><span>Contact Person</span></div>

                                <div class="row g-3 mb-4">
                                    <div class="col-md-6">
                                        <label class="lc-label">Contact Person Name</label>
                                        <input type="text"
                                               name="contact_person_name"
                                               class="lc-input"
                                               placeholder="Full name"
                                               value="{{ old('contact_person_name', $lead->contact_person_name) }}">
                                        @error('contact_person_name')
                                            <p class="lc-error">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="lc-label">Contact Person Role</label>
                                        <select name="contact_person_role" class="lc-input lc-select">
                                            <option value="owner"            {{ old('contact_person_role', $lead->contact_person_role) == 'owner'            ? 'selected' : '' }}>Owner</option>
                                            <option value="property_manager" {{ old('contact_person_role', $lead->contact_person_role) == 'property_manager' ? 'selected' : '' }}>Property Manager</option>
                                            <option value="caretaker"        {{ old('contact_person_role', $lead->contact_person_role) == 'caretaker'        ? 'selected' : '' }}>Caretaker</option>
                                            <option value="unknown"          {{ old('contact_person_role', $lead->contact_person_role) == 'unknown'          ? 'selected' : '' }}>Unknown</option>
                                        </select>
                                    </div>
                                </div>

                                {{-- Lead Details --}}
                                <div class="lc-divider mb-4"><span>Lead Details</span></div>

                                <div class="row g-3 mb-4">
                                    {{-- Property Type --}}
                                    <div class="col-md-6">
                                        <label class="lc-label">Property Type</label>
                                        <select name="property_type" class="lc-input lc-select">
                                            <option value="">Select property type</option>
                                            <option value="apartment"       {{ old('property_type', $lead->company->property_type) == 'apartment'       ? 'selected' : '' }}>Apartment</option>
                                            <option value="commercial"      {{ old('property_type', $lead->company->property_type) == 'commercial'      ? 'selected' : '' }}>Commercial</option>
                                            <option value="mixed_use"       {{ old('property_type', $lead->company->property_type) == 'mixed_use'       ? 'selected' : '' }}>Mixed Use</option>
                                            <option value="student_housing" {{ old('property_type', $lead->company->property_type) == 'student_housing' ? 'selected' : '' }}>Student Housing</option>
                                            <option value="other"           {{ old('property_type', $lead->company->property_type) == 'other'           ? 'selected' : '' }}>Other</option>
                                        </select>
                                    </div>
                                    {{-- Property Type --}}
                                    <div class="col-md-6">
                                        <label class="lc-label">Estimated Units</label>
                                        <input type="number"
                                               name="estimated_units"
                                               class="lc-input"
                                               placeholder="e.g. 24"
                                               min="1"
                                               value="{{ old('estimated_units', $lead->company->estimated_units) }}">
                                        @error('estimated_units')
                                            <p class="lc-error">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                                {{-- Email + Website --}}
                                    <div class="row g-3 mb-4">
                                        <div class="col-md-6">
                                            <label class="lc-label">Email</label>
                                            <input type="email"
                                                name="email"
                                                class="lc-input"
                                                placeholder="e.g. info@acme.com"
                                                value="{{ old('email', $lead->company->email) }}">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="lc-label">Website</label>
                                            <input type="url"
                                                name="website"
                                                class="lc-input"
                                                placeholder="e.g. https://acme.com"
                                                value="{{ old('website', $lead->company->website) }}">
                                        </div>
                                    </div>
                                {{-- Divider + Actions --}}
                                <div class="lc-divider mb-4"></div>

                                <div class="d-flex align-items-center gap-3">
                                    <button type="submit" class="lc-btn-primary">
                                        <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
                                            <path d="M2 11.5V14h2.5l7.4-7.4-2.5-2.5L2 11.5zM13.7 4.3a.7.7 0 0 0 0-1L12 1.6a.7.7 0 0 0-1 0l-1.3 1.3 2.5 2.5 1.5-1.1z" fill="currentColor"/>
                                        </svg>
                                        Update Lead
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
            display: flex;
            align-items: center;
            gap: 12px;
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

        /* ── Status badge (header chip) ──────────────────────── */
        .leads-status-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 11px;
            font-weight: 500;
            padding: 4px 12px;
            border-radius: 99px;
            border: 0.5px solid transparent;
            white-space: nowrap;
        }
        .leads-status-badge--active             { background:#E1F5EE;border-color:#9FE1CB;color:#0F6E56; }
        .leads-status-badge--demo_scheduled     { background:#E6F1FB;border-color:#B5D4F4;color:#185FA5; }
        .leads-status-badge--demo_completed     { background:#EEEDFE;border-color:#AFA9EC;color:#534AB7; }
        .leads-status-badge--trial              { background:#EEEDFE;border-color:#AFA9EC;color:#534AB7; }
        .leads-status-badge--converted          { background:#E1F5EE;border-color:#9FE1CB;color:#085041; }
        .leads-status-badge--rejected           { background:#FCEBEB;border-color:#F7C1C1;color:#A32D2D; }
        .leads-status-badge--expired            { background:#f3f4f6;border-color:#e5e7eb;color:#5F5E5A; }
        .leads-status-badge--lost               { background:#FAECE7;border-color:#F5C4B3;color:#993C1D; }
        .leads-status-badge--pending_conversion { background:#EEEDFE;color:#3C3489; }

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
    </style>
@endsection