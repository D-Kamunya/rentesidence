@extends('admin.layouts.app')

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
                                    <h3 class="mb-sm-0">Lead Details</h3>
                                </div>
                                <div class="page-title-right">
                                    <ol class="breadcrumb mb-0">
                                        <li class="breadcrumb-item">
                                            <a href="{{ route('admin.dashboard') }}">Dashboard</a>
                                        </li>
                                        <li class="breadcrumb-item">
                                            <a href="{{ route('admin.leads.index') }}">Leads</a>
                                        </li>
                                        <li class="breadcrumb-item active">{{ $lead->company->company_name }}</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="container">

                        {{-- Back link --}}
                        <a href="{{ route('admin.leads.index') }}" class="adm-back-link mb-4 d-inline-flex align-items-center gap-2">
                            <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
                                <path d="M10 3L5 8l5 5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            Back to Leads
                        </a>

                        {{-- Flash messages --}}
                        @if(session('success'))
                            <div class="adm-alert adm-alert--success mb-4">
                                <svg width="15" height="15" viewBox="0 0 16 16" fill="none" style="flex-shrink:0;">
                                    <circle cx="8" cy="8" r="7" stroke="currentColor" stroke-width="1.5"/>
                                    <path d="M4.5 8.5l2.5 2.5 4.5-5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                <div>{{ session('success') }}</div>
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="adm-alert adm-alert--danger mb-4">
                                <svg width="15" height="15" viewBox="0 0 16 16" fill="none" style="flex-shrink:0;">
                                    <circle cx="8" cy="8" r="7" stroke="currentColor" stroke-width="1.5"/>
                                    <path d="M8 5v4M8 11v.5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                                </svg>
                                <div>{{ session('error') }}</div>
                            </div>
                        @endif

                        {{-- Variables --}}
                        @php
                            $expiry = \Carbon\Carbon::parse($lead->ownership_expires_at);
                            $now = now();
                            $diff = $now->diff($expiry);
                            $months = $diff->m;
                            $days = $diff->d;
                            $expiryText = $expiry->isFuture()
                                ? (($months > 0 ? $months . ' month' . ($months > 1 ? 's ' : ' ') : '')
                                    . ($days > 0 ? $days . ' day' . ($days > 1 ? 's' : '') : '')
                                    . ' left')
                                : 'Expired';
                            $expiryClass = !$expiry->isFuture() ? 'danger' : ($months === 0 && $days <= 7 ? 'warning' : 'success');
                            $status = strtolower($lead->status);
                            $temp = strtolower($lead->temperature);
                            $latestTrialActivity = $lead->latestTrialActivity();
                            $isExpiredTrial = $lead->status === 'pending_conversion'
                                && $latestTrialActivity?->type === 'trial_expired';
                            $isPendingExtention = $lead->status === 'pending_conversion'
                                && $latestTrialActivity?->type === 'trial_extention';
                            $isTrialRequested = $lead->status === 'pending_conversion'
                                && $latestTrialActivity?->type === 'trial_request';
                            $conversionRejectedByAdmin = $lead->status === 'demo_completed'
                                && $latestTrialActivity?->type === 'conversion_rejected';
                        @endphp

                        {{-- ═══════════════════════════════════════════════ --}}
                        {{-- HERO CARD                                       --}}
                        {{-- ═══════════════════════════════════════════════ --}}
                        <div class="adm-hero mb-4">

                            {{-- Company info --}}
                            <div class="adm-hero__left">
                                <div class="adm-company-avatar">
                                    {{ strtoupper(substr($lead->company->company_name, 0, 2)) }}
                                </div>
                                <div>
                                    <h4 class="adm-company-name">{{ $lead->company->company_name }}</h4>
                                    <p class="adm-company-meta">
                                        {{ $lead->company->city }}{{ $lead->company->city && $lead->company->country ? ', ' : '' }}{{ $lead->company->country }}
                                        @if($lead->company->estimated_units)
                                            &middot; {{ $lead->company->estimated_units }} units
                                        @endif
                                    </p>
                                    <div class="d-flex align-items-center gap-2 mt-2 flex-wrap">

                                        {{-- Status badge --}}
                                        @if($conversionRejectedByAdmin)
                                            <span class="adm-status-badge" style="background: #e7c6a5;border:0.5px solid #e88855;color:#854F0B;">
                                                <svg width="9" height="9" viewBox="0 0 16 16" fill="none">
                                                    <path d="M8 2l6 12H2L8 2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                    <path d="M8 7v3M8 12v.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                                </svg>
                                                Trial Correction
                                            </span>
                                        @elseif($lead->status === 'pending_conversion' && $isExpiredTrial)
                                            <span class="adm-status-badge" style="background: #e7c6a5;border:0.5px solid #e88855;color:#854F0B;"> 
                                                <svg width="9" height="9" viewBox="0 0 16 16" fill="none">
                                                    <circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.5"/>
                                                    <path d="M8 5v3.5l2 1.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                                Trial expired
                                            </span>
                                        @elseif($lead->status === 'pending_conversion' && $isPendingExtention)
                                            <span class="adm-status-badge" style="background: #e7c6a5;border:0.5px solid #e88855;color:#854F0B;"> 
                                                <svg width="9" height="9" viewBox="0 0 16 16" fill="none">
                                                    <circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.5"/>
                                                    <path d="M8 5v3.5l2 1.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                                Pending Trial Extension
                                            </span>
                                        @elseif($status === 'pending_conversion')
                                            <span class="adm-status-badge" style="background:#FAEEDA;border:0.5px solid #FAC775;color:#854F0B;">
                                                <svg width="9" height="9" viewBox="0 0 16 16" fill="none">
                                                    <circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.5"/>
                                                    <path d="M8 5v3.5l2 1.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                                Pending Approval
                                            </span>
                                        @elseif($status === 'trial')
                                            <span class="adm-status-badge adm-status-badge--trial">
                                                <svg width="9" height="9" viewBox="0 0 16 16" fill="none">
                                                    <path d="M8 2l2 4h4l-3 3 1 4-4-2-4 2 1-4-3-3h4z" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                                Trial
                                            </span>
                                        @else
                                            <span class="adm-status-badge adm-status-badge--{{ $status }}">
                                                {{ ucfirst(str_replace('_', ' ', $lead->status)) }}
                                            </span>
                                        @endif

                                        {{-- Temperature badge --}}
                                        @if(!$lead->isClosed())
                                            <span class="adm-temp-badge adm-temp-badge--{{ $temp }}">
                                                @if($temp === 'hot')
                                                    <svg width="10" height="10" viewBox="0 0 16 16" fill="currentColor"><path d="M8 1a5 5 0 0 1 3 9V2a3 3 0 0 0-6 0v8A5 5 0 0 1 8 1z"/></svg>
                                                @elseif($temp === 'warm')
                                                    <svg width="10" height="10" viewBox="0 0 16 16" fill="none"><circle cx="8" cy="8" r="5" stroke="currentColor" stroke-width="1.8"/></svg>
                                                @else
                                                    <svg width="10" height="10" viewBox="0 0 16 16" fill="currentColor"><path d="M8 2a6 6 0 1 0 0 12A6 6 0 0 0 8 2zm0 10a4 4 0 1 1 0-8 4 4 0 0 1 0 8z"/></svg>
                                                @endif
                                                {{ ucfirst($lead->temperature) }}
                                            </span>
                                        @endif

                                        {{-- Expiry chip --}}
                                        @if(!in_array($lead->status, ['converted', 'expired', 'rejected', 'lost', 'trial']))
                                            <span class="adm-expiry-chip adm-expiry-chip--{{ $expiryClass }}">
                                                <svg width="10" height="10" viewBox="0 0 16 16" fill="none">
                                                    <circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.5"/>
                                                    <path d="M8 5v3.5l2 1.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                                {{ $expiryText }}
                                            </span>
                                        @endif

                                    </div>
                                </div>
                            </div>

                            {{-- Affiliate info chip --}}
                            <div class="adm-affiliate-chip">
                                <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
                                    <circle cx="8" cy="6" r="3" stroke="currentColor" stroke-width="1.5"/>
                                    <path d="M2 14a6 6 0 0 1 12 0" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                </svg>
                                <div>
                                    <span style="font-size:11px;font-weight:500;text-transform:uppercase;letter-spacing:.05em;opacity:.7;">Affiliate</span>
                                    <div style="font-weight:500;font-size:14px;">{{ $lead->affiliate->first_name }} {{ $lead->affiliate->last_name }}</div>
                                    <div style="font-size:11px;opacity:.7;">ID: #{{ $lead->affiliate->id }}</div>
                                </div>
                            </div>

                        </div>
                        {{-- END HERO CARD --}}

                        <div class="row g-4">

                            {{-- ═══════════════════════════════════════════ --}}
                            {{-- LEFT COLUMN                                 --}}
                            {{-- ═══════════════════════════════════════════ --}}
                            <div class="col-md-8">

                                {{-- Lead details card --}}
                                <div class="adm-card mb-4">
                                    <div class="adm-card__head">
                                        <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
                                            <path d="M9 5H7a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2M9 5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v0a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                        </svg>
                                        Company & Contact Information
                                    </div>
                                    <div class="adm-card__body">
                                        <div class="adm-detail-row">
                                            <span class="adm-detail-label">Contact Person</span>
                                            <span class="adm-detail-val">{{ $lead->contact_person_name ?: '—' }}</span>
                                        </div>
                                        <div class="adm-detail-row">
                                            <span class="adm-detail-label">Role</span>
                                            <span class="adm-detail-val">{{ ucfirst(str_replace('_', ' ', $lead->contact_person_role)) ?: '—' }}</span>
                                        </div>
                                        <div class="adm-detail-row">
                                            <span class="adm-detail-label">Email</span>
                                            <span class="adm-detail-val">
                                                @if($lead->company->email)
                                                    <a href="mailto:{{ $lead->company->email }}" style="color:#185FA5;">{{ $lead->company->email }}</a>
                                                @else
                                                    —
                                                @endif
                                            </span>
                                        </div>
                                        <div class="adm-detail-row">
                                            <span class="adm-detail-label">Phone</span>
                                            <span class="adm-detail-val">
                                                @if($lead->company->phone)
                                                    <a href="tel:{{ $lead->company->phone }}" style="color:#185FA5;">{{ $lead->company->phone }}</a>
                                                @else
                                                    —
                                                @endif
                                            </span>
                                        </div>
                                        <div class="adm-detail-row">
                                            <span class="adm-detail-label">Location</span>
                                            <span class="adm-detail-val">
                                                {{ $lead->company->city }}{{ $lead->company->city && $lead->company->country ? ', ' : '' }}{{ $lead->company->country }}
                                            </span>
                                        </div>
                                        <div class="adm-detail-row">
                                            <span class="adm-detail-label">Estimated Units</span>
                                            <span class="adm-detail-val">{{ $lead->company->estimated_units ?: '—' }}</span>
                                        </div>
                                        <div class="adm-detail-row">
                                            <span class="adm-detail-label">Property Type</span>
                                            <span class="adm-detail-val">{{ $lead->company->property_type ? ucfirst(str_replace('_', ' ', $lead->company->property_type)) : '—' }}</span>
                                        </div>
                                        <div class="adm-detail-row">
                                            <span class="adm-detail-label">Website</span>
                                            <span class="adm-detail-val">
                                                @if($lead->company->website)
                                                    <a href="{{ $lead->company->website }}" target="_blank" style="color:#185FA5;">{{ $lead->company->website }}</a>
                                                @else
                                                    —
                                                @endif
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                {{-- Demo Info (if scheduled) --}}
                                @if($lead->demo_scheduled_at)
                                    <div class="adm-card mb-4">
                                        <div class="adm-card__head">
                                            <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
                                                <rect x="2" y="3" width="12" height="11" rx="2" stroke="currentColor" stroke-width="1.5"/>
                                                <path d="M5 1v3M11 1v3M2 7h12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                            </svg>
                                            Demo Information
                                        </div>
                                        <div class="adm-card__body">
                                            <div class="adm-demo-info">
                                                @if($lead->demo_scheduled_at->isPast() && $lead->status === 'demo_scheduled')
                                                    <div class="adm-demo-badge adm-demo-badge--missed">
                                                        <svg width="13" height="13" viewBox="0 0 16 16" fill="none">
                                                            <circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.5"/>
                                                            <path d="M8 5v4M8 11v.5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                                                        </svg>
                                                        Demo Missed
                                                    </div>
                                                @elseif($lead->status === 'demo_scheduled')
                                                    <div class="adm-demo-badge">
                                                        <svg width="13" height="13" viewBox="0 0 16 16" fill="none">
                                                            <circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.5"/>
                                                            <path d="M8 5v3.5l2 1.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                        </svg>
                                                        Upcoming Demo
                                                    </div>
                                                @else
                                                    <div class="adm-demo-badge adm-demo-badge--completed">
                                                        <svg width="13" height="13" viewBox="0 0 16 16" fill="none">
                                                            <circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.5"/>
                                                            <path d="M4.5 8.5l2.5 2.5 4.5-5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                                                        </svg>
                                                        Demo Completed
                                                    </div>
                                                @endif
                                                <div style="margin-top:8px;">
                                                    <div style="font-size:15px;font-weight:500;color:#111827;">
                                                        {{ $lead->demo_scheduled_at->format('l, F j, Y') }}
                                                    </div>
                                                    <div style="font-size:14px;color:#6b7280;margin-top:2px;">
                                                        {{ $lead->demo_scheduled_at->format('g:i A') }} • {{ $lead->demo_scheduled_at->diffForHumans() }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                {{-- Activity Timeline --}}
                                <div class="adm-card">
                                    <div class="adm-card__head">
                                        <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
                                            <circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.5"/>
                                            <path d="M8 5v3.5l2 1.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                        Activity Timeline
                                    </div>
                                    <div class="adm-card__body">
                                        @forelse($lead->activities as $activity)
                                            <div class="adm-timeline-item">
                                                <div class="adm-timeline-dot"></div>
                                                <div class="adm-timeline-content">
                                                    <p class="adm-timeline-time">{{ $activity->created_at->diffForHumans() }}</p>
                                                    <p class="adm-timeline-desc">{{ $activity->description }}</p>
                                                </div>
                                            </div>
                                        @empty
                                            <p style="font-size:13px;color:#9ca3af;margin:0;">No activity recorded yet.</p>
                                        @endforelse
                                    </div>
                                </div>

                            </div>
                            {{-- ═══════════════════════════════════════════ --}}
                            {{-- END LEFT COLUMN                             --}}
                            {{-- ═══════════════════════════════════════════ --}}

                            {{-- ═══════════════════════════════════════════ --}}
                            {{-- RIGHT COLUMN - ADMIN ACTIONS               --}}
                            {{-- ═══════════════════════════════════════════ --}}
                            <div class="col-md-4">
                            {{-- PENDING CONVERSION ACTIONS --}}
                                @if($isExpiredTrial)
                                    <div class="adm-action-card mb-4">
                                        <div class="adm-action-card__header">
                                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                                <circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.5"/>
                                            </svg>
                                            <div>
                                                <div style="font-weight:600;font-size:14px;">Trial has expired for this lead</div>
                                                <div style="font-size:12px;opacity:.7;">Status: {{ ucfirst(str_replace('_', ' ', $lead->status)) }}</div>
                                            </div>
                                        </div>
                                        <p style="font-size:13px;color:#6b7280;margin:0;">
                                            The affiliate has been prompted to see whether the lead is ready to convert to a paid package or might require trial extension. No further admin action is required at this time.
                                        </p>
                                    </div>
                                @elseif($lead->status === 'pending_conversion')
                                    <div class="adm-action-card adm-action-card--warning mb-4">
                                        <div class="adm-action-card__header">
                                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                                <circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.5"/>
                                                <path d="M8 5v4M8 11v.5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                                            </svg>
                                            <div>
                                                <div style="font-weight:600;font-size:14px;">Pending Your Review</div>
                                                <div style="font-size:12px;opacity:.85;">
                                                    @if($isPendingExtention)
                                                        Affiliate has requested trial extension
                                                    @else
                                                        Affiliate has requested trial conversion
                                                    @endif
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Warning if this is a confirmation scenario (from controller redirect) --}}
                                        @if(session('warning_message'))
                                            <div class="adm-confirmation-notice mb-3">
                                                <svg width="15" height="15" viewBox="0 0 16 16" fill="none" style="flex-shrink:0;">
                                                    <circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.5"/>
                                                    <path d="M8 5v4M8 11v.5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                                                </svg>
                                                <div style="flex:1;">
                                                    <p style="font-weight:500;margin:0 0 4px;">⚠️ Confirmation Required</p>
                                                    <p style="margin:0 0 12px;">{{ session('warning_message') }}</p>
                                                    
                                                    {{-- Confirmation button inside warning notice --}}
                                                    @if(session('warning_data.show_confirm'))
                                                        <form method="POST" action="{{ route('admin.leads.approve', session('warning_data.lead_id') ?? $lead->id) }}">
                                                            @csrf
                                                            <input type="hidden" name="confirm_renewal" value="1">
                                                            <button type="submit" class="adm-btn adm-btn--success" style="width:100%;">
                                                                <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
                                                                    <circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.5"/>
                                                                    <path d="M4.5 8.5l2.5 2.5 4.5-5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                                                                </svg>
                                                                Confirm & Extend Trial
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </div>
                                        @endif
                                     
                                        {{-- Approve Form (ONLY shows when NO warning) --}}
                                        @if(!session('warning_message'))
                                            <form method="POST" action="{{ route('admin.leads.approve', $lead->id) }}" class="mb-3">
                                                @csrf
                                                {{-- Approve Trial Extension (existing user with expired trial) --}}
                                                @if($isPendingExtention)
                                                    <button type="submit" class="adm-btn adm-btn--success">
                                                        <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
                                                            <path d="M13 8A5 5 0 1 1 8 3" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                                                            <path d="M8 1v4l2-2" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"
                                                                stroke-linejoin="round"/>
                                                        </svg>
                                                        Approve Trial Extension Request
                                                    </button>
                                                @else
                                                    <button type="submit" class="adm-btn adm-btn--success">
                                                        <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
                                                            <circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.5"/>
                                                            <path d="M4.5 8.5l2.5 2.5 4.5-5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"
                                                                stroke-linejoin="round"/>
                                                        </svg>
                                                        Approve & Create Trial Account
                                                    </button>
                                                @endif
                                            </form>
                                        @endif

                                        {{-- Reject Form --}}
                                        <div class="adm-reject-section">
                                            <button type="button" 
                                                    class="adm-btn adm-btn--danger-outline"
                                                    onclick="document.getElementById('rejectForm').classList.toggle('adm-reject-form--open')">
                                                <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
                                                    <circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.5"/>
                                                    <path d="M5 5l6 6M11 5l-6 6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                                                </svg>
                                                Reject with Feedback
                                            </button>

                                            <div class="adm-reject-form" id="rejectForm">
                                                <form method="POST" action="{{ route('admin.leads.reject', $lead->id) }}">
                                                    @csrf
                                                    <label class="adm-label">Feedback for Affiliate</label>
                                                    <textarea name="rejection_reason"
                                                            class="adm-textarea"
                                                            rows="4"
                                                            placeholder="Explain why this trial cannot be approved yet..."
                                                            required></textarea>
                                                    <div class="d-flex gap-2 mt-3">
                                                        <button type="submit" class="adm-btn adm-btn--danger">
                                                            <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
                                                                <path d="M4 4l8 8M12 4l-8 8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                                            </svg>
                                                            Send Rejection
                                                        </button>
                                                        <button type="button"
                                                                class="adm-btn adm-btn--ghost"
                                                                onclick="document.getElementById('rejectForm').classList.remove('adm-reject-form--open')">
                                                            Cancel
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                {{-- TRIAL STATUS --}}
                                @elseif($lead->status === 'trial')
                                    <div class="adm-action-card adm-action-card--info mb-4">
                                        <div class="adm-action-card__header">
                                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                                <path d="M8 2l2 4h4l-3 3 1 4-4-2-4 2 1-4-3-3h4z" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                            <div>
                                                <div style="font-weight:600;font-size:14px;">Trial Active</div>
                                                <div style="font-size:12px;opacity:.85;">Customer is using a trial account</div>
                                            </div>
                                        </div>
                                        <p style="font-size:13px;color:#534AB7;margin:0;line-height:1.6;">
                                            This lead is currently on a trial. The affiliate will start earning commissions once they convert to a paying customer.
                                        </p>
                                    </div>

                                {{-- CONVERTED STATUS --}}
                                @elseif($lead->status === 'converted')
                                    <div class="adm-action-card adm-action-card--success mb-4">
                                        <div class="adm-action-card__header">
                                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                                <circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.5"/>
                                                <path d="M4.5 8.5l2.5 2.5 4.5-5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                            <div>
                                                <div style="font-weight:600;font-size:14px;">Paying Customer 💰</div>
                                                <div style="font-size:12px;opacity:.85;">Successfully converted from trial</div>
                                            </div>
                                        </div>
                                        <p style="font-size:13px;color:#0F6E56;margin:0;line-height:1.6;">
                                            This customer is generating monthly recurring commissions for the affiliate. Great work by {{ $lead->affiliate->first_name }}!
                                        </p>
                                    </div>

                                {{-- CONVERSION REJECTED --}}
                                @elseif($conversionRejectedByAdmin)
                                <div class="adm-action-card mb-4">
                                    <div class="adm-action-card__header">
                                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                            <circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.5"/>
                                            <path d="M8 5v4M8 11v.5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                                        </svg>
                                        <div>
                                            <div style="font-weight:600;font-size:14px;">Pending Correction</div>
                                            <div style="font-size:12px;opacity:.7;">Status: {{ ucfirst($lead->status) }}</div>
                                        </div>
                                    </div>
                                    <p style="font-size:13px;color:#6b7280;margin:0;">
                                        Affiliate is making suggested corrections and will re-request for Trial once they are done.
                                    </p>

                                    @php
                                        $rejection = $lead->activities->where('type', 'conversion_rejected')->first();
                                    @endphp

                                    @if($rejection)
                                        <div class="adm-rejection-reason mt-2" style="font-size:13px;color:#ef4444;">
                                            <strong>Rejection Reason:</strong> {{ $rejection->description }}
                                        </div>
                                    @endif
                                </div>

                                {{-- REJECTED/LOST/EXPIRED STATUS --}}
                                @elseif(in_array($lead->status, ['rejected', 'lost', 'expired']))
                                    <div class="adm-action-card mb-4">
                                        <div class="adm-action-card__header">
                                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                                <circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.5"/>
                                                <path d="M8 5v4M8 11v.5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                                            </svg>
                                            <div>
                                                <div style="font-weight:600;font-size:14px;">Lead Closed</div>
                                                <div style="font-size:12px;opacity:.7;">Status: {{ ucfirst($lead->status) }}</div>
                                            </div>
                                        </div>
                                        <p style="font-size:13px;color:#6b7280;margin:0;">
                                            This lead is no longer active. No further actions available.
                                        </p>
                                    </div>

                                {{-- OTHER STATUSES --}}
                                @else
                                    <div class="adm-action-card mb-4">
                                        <div class="adm-action-card__header">
                                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                                <circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.5"/>
                                            </svg>
                                            <div>
                                                <div style="font-weight:600;font-size:14px;">Lead in Progress</div>
                                                <div style="font-size:12px;opacity:.7;">Status: {{ ucfirst(str_replace('_', ' ', $lead->status)) }}</div>
                                            </div>
                                        </div>
                                        <p style="font-size:13px;color:#6b7280;margin:0;">
                                            The affiliate is working on this lead. No admin action required at this time.
                                        </p>
                                    </div>
                                @endif

                                {{-- Lead Meta Info --}}
                                <div class="adm-card">
                                    <div class="adm-card__head">
                                        <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
                                            <circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.5"/>
                                        </svg>
                                        Lead Information
                                    </div>
                                    <div class="adm-card__body">
                                        <div class="adm-meta-item">
                                            <span class="adm-meta-label">Submitted</span>
                                            <span class="adm-meta-val">{{ $lead->created_at->format('M d, Y') }}</span>
                                        </div>
                                        <div class="adm-meta-item">
                                            <span class="adm-meta-label">Last Updated</span>
                                            <span class="adm-meta-val">{{ $lead->updated_at->diffForHumans() }}</span>
                                        </div>
                                        @if(!in_array($lead->status, ['converted', 'expired', 'rejected', 'lost', 'trial']))
                                            <div class="adm-meta-item">
                                                <span class="adm-meta-label">Ownership Expires</span>
                                                <span class="adm-meta-val">
                                                    @if($expiry->isFuture())
                                                        {{ $expiry->format('M d, Y') }}
                                                    @else
                                                        <span style="color:#A32D2D;">Expired</span>
                                                    @endif
                                                </span>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                            </div>
                            {{-- ═══════════════════════════════════════════ --}}
                            {{-- END RIGHT COLUMN                            --}}
                            {{-- ═══════════════════════════════════════════ --}}

                        </div>
                        {{-- end .row --}}

                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* ── Back link ───────────────────────────────────────── */
        .adm-back-link { font-size:13px;font-weight:500;color:#6b7280;text-decoration:none;transition:color .15s; }
        .adm-back-link:hover { color:#111827; }

        /* ── Flash alerts ────────────────────────────────────── */
        .adm-alert { display:flex;align-items:center;gap:10px;padding:.85rem 1.1rem;border-radius:10px;font-size:14px; }
        .adm-alert--success { background:#E1F5EE;color:#0F6E56; }
        .adm-alert--danger  { background:#FCEBEB;color:#A32D2D; }

        /* ── Hero card ───────────────────────────────────────── */
        .adm-hero {
            background:#fff;border:0.5px solid #e5e7eb;border-radius:14px;
            padding:1.25rem 1.5rem;display:flex;align-items:flex-start;
            justify-content:space-between;flex-wrap:wrap;gap:1rem;
        }
        .adm-hero__left { display:flex;align-items:flex-start;gap:14px;flex:1; }
        .adm-company-avatar {
            width:48px;height:48px;border-radius:12px;background:#E6F1FB;color:#185FA5;
            font-size:15px;font-weight:500;display:inline-flex;align-items:center;
            justify-content:center;flex-shrink:0;
        }
        .adm-company-name { font-size:17px;font-weight:500;color:#111827;margin:0 0 2px; }
        .adm-company-meta { font-size:13px;color:#9ca3af;margin:0; }

        /* ── Affiliate chip ──────────────────────────────────── */
        .adm-affiliate-chip {
            display:inline-flex;align-items:center;gap:10px;background:#f3f4f6;
            border:0.5px solid #e5e7eb;color:#374151;border-radius:10px;padding:.65rem 1rem;
        }

        /* ── Chips ───────────────────────────────────────────── */
        .adm-expiry-chip { display:inline-flex;align-items:center;gap:4px;font-size:11px;font-weight:500;padding:3px 10px;border-radius:99px; }
        .adm-expiry-chip--success { background:#E1F5EE;color:#0F6E56; }
        .adm-expiry-chip--warning { background:#FAEEDA;color:#854F0B; }
        .adm-expiry-chip--danger  { background:#FCEBEB;color:#A32D2D; }

        /* ── Status badges ───────────────────────────────────── */
        .adm-status-badge { display:inline-flex;align-items:center;gap:5px;font-size:11px;font-weight:500;padding:3px 10px;border-radius:99px;border:0.5px solid transparent; }
        .adm-status-badge--active             { background:#E1F5EE;border-color:#9FE1CB;color:#0F6E56; }
        .adm-status-badge--demo_scheduled     { background:#E6F1FB;border-color:#B5D4F4;color:#185FA5; }
        .adm-status-badge--demo_completed     { background:#EEEDFE;border-color:#AFA9EC;color:#534AB7; }
        .adm-status-badge--trial              { background:#EEEDFE;border-color:#AFA9EC;color:#534AB7; }
        .adm-status-badge--converted          { background:#E1F5EE;border-color:#9FE1CB;color:#085041; }
        .adm-status-badge--rejected           { background:#FCEBEB;border-color:#F7C1C1;color:#A32D2D; }
        .adm-status-badge--expired            { background:#f3f4f6;border-color:#e5e7eb;color:#5F5E5A; }
        .adm-status-badge--lost               { background:#FAECE7;border-color:#F5C4B3;color:#993C1D; }

        /* ── Temperature badges ──────────────────────────────── */
        .adm-temp-badge { display:inline-flex;align-items:center;gap:5px;font-size:11px;font-weight:500;padding:3px 10px;border-radius:99px; }
        .adm-temp-badge--hot  { background:#FAECE7;color:#993C1D; }
        .adm-temp-badge--warm { background:#FAEEDA;color:#854F0B; }
        .adm-temp-badge--cold { background:#E6F1FB;color:#185FA5; }

        /* ── Section cards ───────────────────────────────────── */
        .adm-card { background:#fff;border:0.5px solid #e5e7eb;border-radius:12px;overflow:hidden; }
        .adm-card__head { display:flex;align-items:center;gap:8px;padding:.8rem 1.1rem;border-bottom:0.5px solid #e5e7eb;background:#fafafa;font-size:13px;font-weight:500;color:#374151; }
        .adm-card__body { padding:1.1rem; }

        /* ── Detail rows ─────────────────────────────────────── */
        .adm-detail-row { display:flex;justify-content:space-between;align-items:center;padding:.55rem 0;border-bottom:0.5px solid #f3f4f6;font-size:13px; }
        .adm-detail-row:last-child { border-bottom:none; }
        .adm-detail-label { color:#9ca3af;font-weight:500; }
        .adm-detail-val   { color:#111827;font-weight:500;text-align:right; }

        /* ── Demo info ───────────────────────────────────────── */
        .adm-demo-info { text-align:center; }
        .adm-demo-badge {
            display:inline-flex;align-items:center;gap:6px;font-size:12px;font-weight:500;
            padding:6px 14px;border-radius:99px;background:#E6F1FB;color:#185FA5;
        }
        .adm-demo-badge--missed { background:#FCEBEB;color:#A32D2D; }
        .adm-demo-badge--completed { background:#E1F5EE;color:#0F6E56; }

        /* ── Timeline ────────────────────────────────────────── */
        .adm-timeline-item { display:flex;gap:12px;padding-bottom:1rem;position:relative; }
        .adm-timeline-item:last-child { padding-bottom:0; }
        .adm-timeline-item:not(:last-child)::before { content:'';position:absolute;left:5px;top:14px;bottom:0;width:1px;background:#e5e7eb; }
        .adm-timeline-dot { width:11px;height:11px;border-radius:50%;background:#185FA5;border:2px solid #E6F1FB;flex-shrink:0;margin-top:3px; }
        .adm-timeline-time { font-size:11px;color:#9ca3af;font-weight:500;margin:0 0 2px; }
        .adm-timeline-desc { font-size:13px;color:#374151;margin:0;line-height:1.5; }

        /* ── Action cards ────────────────────────────────────── */
        .adm-action-card {
            background:#fff;border:0.5px solid #e5e7eb;border-radius:12px;padding:1.1rem;
        }
        .adm-action-card--warning { background:#FEF9EE;border-color:#FAC775; }
        .adm-action-card--info    { background:#EEEDFE;border-color:#AFA9EC; }
        .adm-action-card--success { background:#E1F5EE;border-color:#9FE1CB; }
        .adm-action-card__header {
            display:flex;align-items:flex-start;gap:10px;margin-bottom:12px;
        }

        /* ── Action buttons ──────────────────────────────────── */
        .adm-btn {
            display:flex;align-items:center;justify-content:center;gap:6px;width:100%;
            padding:9px 16px;font-size:13px;font-weight:500;border-radius:8px;
            border:none;cursor:pointer;text-decoration:none;transition:opacity .15s,transform .15s;
        }
        .adm-btn:hover { opacity:.88;transform:translateY(-1px); }
        .adm-btn--success { background:#1D9E75;color:#fff; }
        .adm-btn--danger  { background:#A32D2D;color:#fff; }
        .adm-btn--danger-outline { background:#fff;color:#A32D2D;border:0.5px solid #F7C1C1; }
        .adm-btn--ghost { background:#f3f4f6;color:#6b7280;border:0.5px solid #d1d5db; }

        /* ── Reject section ──────────────────────────────────── */
        .adm-reject-form {
            max-height:0;overflow:hidden;opacity:0;transition:max-height .3s ease,opacity .3s ease;
            margin-top:12px;
        }
        .adm-reject-form--open { max-height:400px;opacity:1; }
        .adm-label { display:block;font-size:12px;font-weight:500;color:#374151;margin-bottom:6px; }
        .adm-textarea {
            width:100%;padding:9px 12px;font-size:13px;color:#111827;background:#fff;
            border:0.5px solid #d1d5db;border-radius:8px;outline:none;resize:vertical;
            transition:border-color .15s,box-shadow .15s;font-family:inherit;
        }
        .adm-textarea:focus { border-color:#185FA5;box-shadow:0 0 0 3px rgba(24,95,165,.1); }

        /* ── Meta info ───────────────────────────────────────── */
        .adm-meta-item {
            display:flex;justify-content:space-between;align-items:center;
            padding:.5rem 0;border-bottom:0.5px solid #f3f4f6;font-size:12px;
        }
        .adm-meta-item:last-child { border-bottom:none; }
        .adm-meta-label { color:#9ca3af;font-weight:500; }
        .adm-meta-val { color:#111827;font-weight:500; }

        /* ── Confirmation notice ─────────────────────────────── */
        .adm-confirmation-notice {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            background: #FEF9EE;
            border: 0.5px solid #FAC775;
            color: #854F0B;
            border-radius: 8px;
            padding: 12px;
            font-size: 13px;
        }
    </style>

@endsection