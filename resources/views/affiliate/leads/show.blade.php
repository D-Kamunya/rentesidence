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
                                    <h3 class="mb-sm-0">My Leads</h3>
                                </div>
                                <div class="page-title-right">
                                    <ol class="breadcrumb mb-0">
                                        <li class="breadcrumb-item">
                                            <a href="{{ route('affiliate.dashboard') }}">Dashboard</a>
                                        </li>
                                        <li class="breadcrumb-item">
                                            <a href="{{ route('affiliate.leads') }}">Leads</a>
                                        </li>
                                        <li class="breadcrumb-item active">{{ $lead->company->company_name }}</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="container">

                        {{-- Back link --}}
                        <a href="{{ route('affiliate.leads') }}" class="mod-back-link mb-4 d-inline-flex align-items-center gap-2">
                            <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
                                <path d="M10 3L5 8l5 5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            Back to Leads
                        </a>

                        {{-- Flash messages --}}
                        @if(session('success'))
                            <div class="mod-alert mod-alert--success mb-4">
                                <svg width="15" height="15" viewBox="0 0 16 16" fill="none" style="flex-shrink:0;">
                                    <circle cx="8" cy="8" r="7" stroke="currentColor" stroke-width="1.5"/>
                                    <path d="M4.5 8.5l2.5 2.5 4.5-5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                <div>{{ session('success') }}</div>
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="mod-alert mod-alert--danger mb-4">
                                <svg width="15" height="15" viewBox="0 0 16 16" fill="none" style="flex-shrink:0;">
                                    <circle cx="8" cy="8" r="7" stroke="currentColor" stroke-width="1.5"/>
                                    <path d="M8 5v4M8 11v.5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                                </svg>
                                <div>{{ session('error') }}</div>
                            </div>
                        @endif

                        {{-- Variables --}}
                        @php
                            $expiry      = \Carbon\Carbon::parse($lead->ownership_expires_at);
                            $now         = now();
                            $diff        = $now->diff($expiry);
                            $months      = $diff->m;
                            $days        = $diff->d;
                            $expiryText  = $expiry->isFuture()
                                ? (($months > 0 ? $months . ' month' . ($months > 1 ? 's ' : ' ') : '')
                                    . ($days > 0 ? $days . ' day' . ($days > 1 ? 's' : '') : '')
                                    . ' left')
                                : 'Expired';
                            $expiryClass = !$expiry->isFuture() ? 'danger' : ($months === 0 && $days <= 7 ? 'warning' : 'success');
                            $status      = strtolower($lead->status);
                            $temp        = strtolower($lead->temperature);

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
                            $isLocked    = $lead->isClosed() || $lead->status === 'expired';
                            $showExpiry  = !in_array($lead->status, ['converted', 'expired', 'rejected', 'lost', 'trial']);
                            $reasons     = [
                                'too_expensive'      => 'Too Expensive',
                                'using_other_system' => 'Using Another System',
                                'not_interested'     => 'Not Interested',
                                'no_response'        => 'No Response',
                                'timing_not_right'   => 'Timing Not Right',
                                'other'              => 'Other',
                            ];
                        @endphp

                        {{-- ═══════════════════════════════════════════════ --}}
                        {{-- HERO CARD                                       --}}
                        {{-- ═══════════════════════════════════════════════ --}}
                        <div class="ls-hero mb-4">
                            {{-- Company info --}}
                            <div class="ls-hero__left">
                                <div class="ls-company-avatar">
                                    {{ strtoupper(substr($lead->company->company_name, 0, 2)) }}
                                </div>
                                <div>
                                    <h4 class="ls-company-name">{{ $lead->company->company_name }}</h4>
                                    <p class="ls-company-meta">
                                        {{ $lead->company->city }}{{ $lead->company->city && $lead->company->country ? ', ' : '' }}{{ $lead->company->country }}
                                        @if($lead->company->estimated_units)
                                            &middot; {{ $lead->company->estimated_units }} units
                                        @endif
                                    </p>
                                    <div class="d-flex align-items-center gap-2 mt-2 flex-wrap">

                                        {{-- Status badge --}}
                                        @if($conversionRejectedByAdmin)
                                        <span class="leads-status-badge" style="background:#FAEEDA;border:0.5px solid #FAC775;color:#854F0B;">
                                            <svg width="9" height="9" viewBox="0 0 16 16" fill="none">
                                                <path d="M8 2l6 12H2L8 2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                <path d="M8 7v3M8 12v.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                            </svg>
                                            Needs Attention
                                        </span>

                                        @elseif($isExpiredTrial)
                                            <span class="leads-status-badge" style= "background: #e7c6a5;border:0.5px solid #e88855;color:#854F0B;"> 
                                                <svg width="9" height="9" viewBox="0 0 16 16" fill="none">
                                                    <circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.5"/>
                                                    <path d="M8 5v3.5l2 1.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                                Trial Expired
                                            </span>

                                        @elseif($lead->status === 'pending_conversion' && $isPendingExtention)
                                            <span class="leads-status-badge" style="background: #e7c6a5;border:0.5px solid #e88855;color:#854F0B;"> 
                                                <svg width="9" height="9" viewBox="0 0 16 16" fill="none">
                                                    <circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.5"/>
                                                    <path d="M8 5v3.5l2 1.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                                Pending Trial Extension
                                            </span>

                                        @elseif($lead->status === 'pending_conversion')
                                            <span class="leads-status-badge leads-status-badge--pending_conversion"> 
                                                <svg width="9" height="9" viewBox="0 0 16 16" fill="none">
                                                    <circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.5"/>
                                                    <path d="M8 5v3.5l2 1.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                                Pending Approval
                                            </span>

                                        @else
                                            <span class="leads-status-badge leads-status-badge--{{ $lead->status }}">
                                                @if($lead->status === 'trial')
                                                    <svg width="9" height="9" viewBox="0 0 16 16" fill="none">
                                                        <path d="M8 2l2 4h4l-3 3 1 4-4-2-4 2 1-4-3-3h4z" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
                                                    </svg>
                                                @endif
                                                {{ ucfirst(str_replace('_', ' ', $lead->status)) }}
                                            </span>
                                        @endif


                                        {{-- Temperature badge — hide for closed leads --}}
                                        @if(!$lead->isClosed())
                                            <span class="leads-temp-badge leads-temp-badge--{{ $temp }}">
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

                                        @if($showExpiry)
                                            <span class="ls-expiry-chip ls-expiry-chip--{{ $expiryClass }}">
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

                            {{-- Right side: demo chip + renew button --}}
                            <div class="d-flex align-items-start gap-3 flex-wrap">

                                {{-- Demo chip — only when demo_scheduled --}}
                                @if($lead->demo_scheduled_at && $lead->status === 'demo_scheduled')
                                    @if($lead->demo_scheduled_at->isPast())
                                        <div class="ls-demo-chip ls-demo-chip--missed">
                                            <svg width="13" height="13" viewBox="0 0 16 16" fill="none">
                                                <circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.5"/>
                                                <path d="M8 5v4M8 11v.5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                                            </svg>
                                            <div>
                                                <span style="font-size:11px;font-weight:500;text-transform:uppercase;letter-spacing:.05em;opacity:.75;">Demo Missed</span>
                                                <div style="font-weight:500;font-size:13px;">{{ $lead->demo_scheduled_at->format('d M Y, H:i') }}</div>
                                                <div style="font-size:11px;opacity:.75;">{{ $lead->demo_scheduled_at->diffForHumans() }}</div>
                                            </div>
                                        </div>
                                    @else
                                        <div class="ls-demo-chip">
                                            <svg width="13" height="13" viewBox="0 0 16 16" fill="none">
                                                <rect x="2" y="3" width="12" height="11" rx="2" stroke="currentColor" stroke-width="1.5"/>
                                                <path d="M5 1v3M11 1v3M2 7h12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                            </svg>
                                            <div>
                                                <span style="font-size:11px;font-weight:500;text-transform:uppercase;letter-spacing:.05em;opacity:.75;">Demo</span>
                                                <div style="font-weight:500;font-size:13px;">{{ $lead->demo_scheduled_at->format('d M Y, H:i') }}</div>
                                                <div style="font-size:11px;opacity:.75;">{{ $lead->demo_scheduled_at->diffForHumans() }}</div>
                                            </div>
                                        </div>
                                    @endif
                                @endif

                                {{-- Renew button — own expired leads only --}}
                                @if($lead->affiliate_id === auth()->id() && $expiry->isPast() && !$lead->isClosed())
                                    <form action="{{ route('affiliate.leads.renew', $lead->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="ls-renew-btn">
                                            <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
                                                <path d="M13 8A5 5 0 1 1 8 3" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                                                <path d="M8 1v4l2-2" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                            Renew Lead
                                        </button>
                                    </form>
                                @endif

                            </div>
                        </div>
                        {{-- END HERO CARD --}}

                        {{-- ═══════════════════════════════════════════════ --}}
                        {{-- FULL-WIDTH BANNERS                              --}}
                        {{-- ═══════════════════════════════════════════════ --}}
                        <div class="row g-4">
                            {{-- Trial Expired - Re-request Available --}}
                            @if($lead->status === 'pending_conversion' && optional($lead->activities->sortByDesc('created_at')->first())->type === 'trial_expired')
                                <div class="col-12">
                                    <div class="ls-trial-expired-banner">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" style="flex-shrink:0;">
                                            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.5"/>
                                            <path d="M12 8v4M12 16v.5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                        </svg>
                                        <div>
                                            <p style="font-weight:600;font-size:15px;margin:0;margin-bottom:6px;">⏰ Trial Period Has Ended</p>
                                            <p style="font-size:13px;margin:0;line-height:1.6;">
                                                The trial for {{ $lead->company->company_name }} has expired. Now is a great time to follow up with them!
                                            </p>
                                            <p style="font-size:13px;margin:8px 0 0;line-height:1.6;">
                                                <strong>Next Steps:</strong> Reach out to gather feedback, address any concerns and help them transition to a paid account. If they need more time to evaluate the platform, you can re-request trial extension below.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            {{-- Converted --}}
                            @if($lead->status === 'converted')
                                <div class="col-12">
                                    <div class="ls-converted-banner">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" style="flex-shrink:0;">
                                            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.5"/>
                                            <path d="M7 12.5l3.5 3.5 6.5-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                        <div style="flex:1;">
                                            <p style="font-weight:600;font-size:15px;margin:0;margin-bottom:6px;">🎉 Congratulations! Lead Successfully Converted</p>
                                            <p style="font-size:13px;margin:0;line-height:1.6;">
                                                {{ $lead->company->company_name }} is now a paying customer generating monthly recurring commissions for you!
                                            </p>
                                            
                                            <div style="background:rgba(255,255,255,0.5);border-radius:8px;padding:12px;margin-top:12px;">
                                                <p style="font-weight:600;font-size:12px;margin:0 0 6px;text-transform:uppercase;letter-spacing:0.5px;opacity:0.8;">💰 Maximize Your Passive Income:</p>
                                                <ul style="margin:0;padding-left:20px;font-size:12px;line-height:1.7;">
                                                    <li><strong>Check in monthly</strong> — A quick call ensures they're getting value</li>
                                                    <li><strong>Share tips & best practices</strong> — Help them succeed with the platform</li>
                                                    <li><strong>Demonstrate new features</strong> — New features deliver more value and increase loyalty</li>
                                                    <li><strong>Be their advocate</strong> — Happy customers renew, and you earn every month they stay!</li>
                                                </ul>
                                            </div>
                                            
                                            <p style="font-size:11px;margin:10px 0 0;opacity:.75;font-style:italic;">
                                                Remember: Your success is tied to theirs. Keep them engaged and watch your monthly commissions grow! 🚀
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            {{-- Conversion rejected by admin --}}
                            @if($conversionRejectedByAdmin)
                                <div class="col-12">
                                    <div class="ls-conversion-feedback-banner">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" style="flex-shrink:0;">
                                            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.5"/>
                                            <path d="M12 8v4M12 16v.5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                        </svg>
                                        <div>
                                            <p style="font-weight:500;font-size:14px;margin:0;">Your Trial Request Needs Attention</p>
                                            <p style="font-size:12px;margin:0;margin-top:3px;opacity:.9;">
                                                Your trial request was reviewed but could not be approved yet.
                                                @php
                                                    $rejection = $lead->activities->where('type', 'conversion_rejected')->last();
                                                @endphp

                                                @if($rejection)
                                                    <strong style="display:block;margin-top:4px;">
                                                        Admin feedback: {{ $rejection->description }}
                                                    </strong>
                                                @endif
                                            </p>
                                            <p style="font-size:12px;margin:4px 0 0;opacity:.75;">
                                                Please address the feedback above and re-submit your trial request.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            {{-- Rejected by affiliate --}}
                            @elseif($rejectedByAffiliate)
                                <div class="col-12">
                                    <div class="ls-rejected-banner">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" style="flex-shrink:0;">
                                            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.5"/>
                                            <path d="M15 9l-6 6M9 9l6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                        </svg>
                                        <div>
                                            <p style="font-weight:500;font-size:14px;margin:0;">Lead Rejected</p>
                                            <p style="font-size:12px;margin:0;margin-top:3px;opacity:.85;">
                                                You marked this lead as rejected.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            {{-- Trial Status Banner --}}
                            @if($lead->status === 'trial')
                                <div class="col-12">
                                    <div class="ls-trial-banner">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" style="flex-shrink:0;">
                                            <path d="M12 2l3 6h6l-4.5 4.5 1.5 6-6-3-6 3 1.5-6L3 8h6z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                        <div>
                                            <p style="font-weight:500;font-size:14px;margin:0;">Trial Account Active!</p>
                                            <p style="font-size:12px;margin:0;margin-top:3px;opacity:.85;">
                                                {{ $lead->company->company_name }} is now using a trial account. Keep in touch with them to ensure they upgrade to a paid plan. Your commission will be earned once they convert to a paying customer.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            {{-- ═══════════════════════════════════════════ --}}
                            {{-- LEFT COLUMN                                 --}}
                            {{-- ═══════════════════════════════════════════ --}}
                            <div class="col-md-6">

                                {{-- Lead details card --}}
                                <div class="ls-card mb-4">
                                    <div class="ls-card__head">
                                        <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
                                            <path d="M9 5H7a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2M9 5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v0a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                        </svg>
                                        Lead Details
                                    </div>
                                    <div class="ls-card__body">
                                        <div class="ls-detail-row">
                                            <span class="ls-detail-label">Contact</span>
                                            <span class="ls-detail-val">{{ $lead->contact_person_name ?: '—' }}</span>
                                        </div>
                                        <div class="ls-detail-row">
                                            <span class="ls-detail-label">Role</span>
                                            <span class="ls-detail-val">{{ ucfirst(str_replace('_', ' ', $lead->contact_person_role)) ?: '—' }}</span>
                                        </div>
                                        <div class="ls-detail-row">
                                            <span class="ls-detail-label">Phone</span>
                                            <span class="ls-detail-val">{{ $lead->company->phone ?: '—' }}</span>
                                        </div>
                                        <div class="ls-detail-row">
                                            <span class="ls-detail-label">Units</span>
                                            <span class="ls-detail-val">{{ $lead->company->estimated_units ?: '—' }}</span>
                                        </div>
                                        <div class="ls-detail-row">
                                            <span class="ls-detail-label">Email</span>
                                            <span class="ls-detail-val">{{ $lead->company->email ?: '—' }}</span>
                                        </div>
                                        <div class="ls-detail-row">
                                            <span class="ls-detail-label">Website</span>
                                            <span class="ls-detail-val">
                                                @if($lead->company->website)
                                                    <a href="{{ $lead->company->website }}" target="_blank" style="color:#185FA5;font-size:13px;">{{ $lead->company->website }}</a>
                                                @else
                                                    —
                                                @endif
                                            </span>
                                        </div>
                                        <div class="ls-detail-row">
                                            <span class="ls-detail-label">Property Type</span>
                                            <span class="ls-detail-val">{{ $lead->company->property_type ? ucfirst(str_replace('_', ' ', $lead->company->property_type)) : '—' }}</span>
                                        </div>
                                    </div>
                                </div>

                                {{-- Sales Actions + Temperature — hidden when locked --}}
                                @if(!$isLocked && $lead->status !== 'trial')

                                    <div class="ls-card mb-4">
                                        <div class="ls-card__head">
                                            <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
                                                <path d="M2 10l4-4 3 3 5-6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                            Sales Actions
                                        </div>
                                        <div class="ls-card__body">

                                            {{-- Schedule Demo --}}
                                            @if($lead->status === 'active')
                                                <div class="ls-action-block mb-3">
                                                    <p class="ls-action-label">Schedule a Demo</p>
                                                    <form method="POST" action="{{ route('affiliate.leads.scheduleDemo', $lead) }}" class="d-flex gap-2 flex-wrap">
                                                        @csrf
                                                        <input type="datetime-local" name="demo_date" class="lc-input" style="flex:1;min-width:180px;">
                                                        <button type="submit" class="ls-btn ls-btn--blue">
                                                            <svg width="13" height="13" viewBox="0 0 16 16" fill="none">
                                                                <rect x="2" y="3" width="12" height="11" rx="2" stroke="currentColor" stroke-width="1.5"/>
                                                                <path d="M5 1v3M11 1v3M2 7h12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                                            </svg>
                                                            Schedule Demo
                                                        </button>
                                                    </form>
                                                </div>
                                            @endif

                                            {{-- Demo Completed --}}
                                            @if($lead->status === 'demo_scheduled')
                                                <div class="ls-action-block mb-3">
                                                    <p class="ls-action-label">Mark demo as done</p>
                                                    <form method="POST" action="{{ route('affiliate.leads.demoCompleted', $lead) }}">
                                                        @csrf
                                                        <button type="submit" class="ls-btn ls-btn--purple">
                                                            <svg width="13" height="13" viewBox="0 0 16 16" fill="none">
                                                                <path d="M3 8.5l3 3 7-7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                                            </svg>
                                                            Demo Completed
                                                        </button>
                                                    </form>
                                                </div>
                                            @endif

                                        
                                            {{-- Request Conversion/Extension --}}
                                            
                                            @if($lead->status === 'demo_completed' || $isExpiredTrial)
                                            <div class="ls-action-block mb-3">
                                                <p class="ls-action-label">
                                                    @if($isExpiredTrial)
                                                        Request Trial Extension
                                                    @elseif($conversionRejectedByAdmin)
                                                        Made the corrections?
                                                    @else
                                                        Ready to convert this lead?
                                                    @endif
                                                </p>
                                                <p style="font-size:12px;color:#6b7280;margin-bottom:8px;">
                                                    @if($isExpiredTrial)
                                                        The previous trial has ended. If the client needs more time, request a trial extension.
                                                    @elseif($conversionRejectedByAdmin)
                                                        Ensure you've made the suggested corrections up above and resubmit below
                                                    @else
                                                        Submitting will notify the admin to create a trial account for this client.
                                                    @endif
                                                </p>

                                                <form method="POST" action="{{ route('affiliate.leads.requesttrial', $lead) }}">
                                                    @csrf

                                                    {{-- Only show extension reason when trial is expired --}}
                                                    @if($isExpiredTrial)
                                                        <textarea name="extension_reason"
                                                                class="lc-input lc-textarea mb-3"
                                                                rows="3"
                                                                placeholder="Why does the client need additional trial time? (e.g., needs to test more features, waiting for team approval, etc.)"
                                                                required></textarea>
                                                    @endif

                                                    <button type="submit" class="ls-btn ls-btn--green">
                                                        <svg width="13" height="13" viewBox="0 0 16 16" fill="none">
                                                            <path d="M8 2l2 4h4l-3 3 1 4-4-2-4 2 1-4-3-3h4z" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
                                                        </svg>
                                                        @if($isExpiredTrial)
                                                            Request Trial Extension
                                                        @elseif($conversionRejectedByAdmin)
                                                            Resubmit Trial Request
                                                        @else
                                                            Request Trial
                                                        @endif
                                                    </button>
                                                </form>
                                            </div>

                                            @endif

                                            {{-- Pending Conversion --}}
                                            @if($lead->status === 'pending_conversion' && $isPendingExtention )
                                                <div class="ls-action-block mb-3">
                                                    <div class="ls-pending-conversion">
                                                        <svg width="15" height="15" viewBox="0 0 16 16" fill="none" style="flex-shrink:0;">
                                                            <circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.5"/>
                                                            <path d="M8 5v3.5l2 1.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                        </svg>
                                                        <div>
                                                            <p style="font-weight:500;margin:0;font-size:13px;">Your Trial Extension Request Has been received!
                                                            <p style="font-size:12px;margin:0;margin-top:2px;opacity:.8;">Admin is reviewing your trial extension request.</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                            @if($lead->status === 'pending_conversion' && !$isExpiredTrial && !$isPendingExtention)
                                                <div class="ls-action-block mb-3">
                                                    <div class="ls-pending-conversion">
                                                        <svg width="15" height="15" viewBox="0 0 16 16" fill="none" style="flex-shrink:0;">
                                                            <circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.5"/>
                                                            <path d="M8 5v3.5l2 1.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                        </svg>
                                                        <div>
                                                            <p style="font-weight:500;margin:0;font-size:13px;">Trial access Pending</p>
                                                            <p style="font-size:12px;margin:0;margin-top:2px;opacity:.8;">Admin is reviewing and setting up the trial account.</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif

                                            @if($lead->status !== 'pending_conversion')
                                                {{-- Mark as divider --}}
                                                <div class="lc-divider mb-3"><span>Mark as</span></div>
                                                <div class="d-flex gap-2 flex-wrap align-items-start">

                                                    {{-- Reject block (button + expandable panel) --}}
                                                    <div>
                                                        <button type="button"
                                                                class="ls-btn ls-btn--red"
                                                                onclick="document.getElementById('rejectPanel').classList.toggle('ls-reject-panel--open')">
                                                            <svg width="13" height="13" viewBox="0 0 16 16" fill="none">
                                                                <path d="M4 4l8 8M12 4l-8 8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                                            </svg>
                                                            Rejected
                                                        </button>

                                                        <div class="ls-reject-panel" id="rejectPanel">
                                                            <form method="POST" action="{{ route('affiliate.leads.reject', $lead) }}">
                                                                @csrf
                                                                <p class="ls-reject-panel__title">What is the reason for rejection?</p>
                                                                <div class="ls-reject-reasons">
                                                                    @foreach($reasons as $value => $label)
                                                                        <label class="ls-reject-reason">
                                                                            <input type="radio"
                                                                                name="rejection_reason"
                                                                                value="{{ $value }}"
                                                                                onchange="handleRejectReason(this)"
                                                                                required>
                                                                            <span>{{ $label }}</span>
                                                                        </label>
                                                                    @endforeach
                                                                </div>
                                                                <div class="ls-reject-other" id="rejectOtherWrap">
                                                                    <input type="text"
                                                                        name="rejection_reason_text"
                                                                        id="rejectOtherText"
                                                                        class="lc-input mt-2"
                                                                        placeholder="Please describe the reason…">
                                                                </div>
                                                                <div class="d-flex gap-2 mt-3">
                                                                    <button type="submit" class="ls-btn ls-btn--red">
                                                                        <svg width="13" height="13" viewBox="0 0 16 16" fill="none">
                                                                            <path d="M4 4l8 8M12 4l-8 8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                                                        </svg>
                                                                        Confirm Rejection
                                                                    </button>
                                                                    <button type="button"
                                                                            class="ls-btn"
                                                                            style="background:#f3f4f6;color:#6b7280;"
                                                                            onclick="document.getElementById('rejectPanel').classList.remove('ls-reject-panel--open')">
                                                                        Cancel
                                                                    </button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                    {{-- end reject block --}}

                                                    {{-- Mark Lost --}}
                                                    <form method="POST" action="{{ route('affiliate.leads.lost', $lead) }}">
                                                        @csrf
                                                        <button type="submit" class="ls-btn ls-btn--amber">
                                                            <svg width="13" height="13" viewBox="0 0 16 16" fill="none">
                                                                <circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.5"/>
                                                                <path d="M8 5v4M8 11v.5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                                                            </svg>
                                                            Mark Lost
                                                        </button>
                                                    </form>

                                                </div>
                                            @endif  
                                            {{-- end mark-as row --}}

                                        </div>
                                    </div>
                                    {{-- end Sales Actions card --}}

                                    {{-- Temperature card --}}
                                    <div class="ls-card">
                                        <div class="ls-card__head">
                                            <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
                                                <path d="M8 1a5 5 0 0 1 3 9V2a3 3 0 0 0-6 0v8A5 5 0 0 1 8 1z" fill="currentColor"/>
                                            </svg>
                                            Update Temperature
                                        </div>
                                        <div class="ls-card__body">
                                            <form method="POST" action="{{ route('affiliate.leads.temperature', $lead) }}">
                                                @csrf
                                                <input type="hidden" name="temperature" id="tempValue" value="">
                                                <div class="lc-temp-group">
                                                    <button type="submit"
                                                            onclick="document.getElementById('tempValue').value='cold'"
                                                            class="lc-temp-option lc-temp-option--blue {{ $temp === 'cold' ? 'lc-temp-active--blue' : '' }}">
                                                        <svg width="10" height="10" viewBox="0 0 16 16" fill="currentColor"><path d="M8 2a6 6 0 1 0 0 12A6 6 0 0 0 8 2zm0 10a4 4 0 1 1 0-8 4 4 0 0 1 0 8z"/></svg>
                                                        Cold
                                                    </button>
                                                    <button type="submit"
                                                            onclick="document.getElementById('tempValue').value='warm'"
                                                            class="lc-temp-option lc-temp-option--amber {{ $temp === 'warm' ? 'lc-temp-active--amber' : '' }}">
                                                        <svg width="10" height="10" viewBox="0 0 16 16" fill="none"><circle cx="8" cy="8" r="5" stroke="currentColor" stroke-width="1.8"/></svg>
                                                        Warm
                                                    </button>
                                                    <button type="submit"
                                                            onclick="document.getElementById('tempValue').value='hot'"
                                                            class="lc-temp-option lc-temp-option--coral {{ $temp === 'hot' ? 'lc-temp-active--coral' : '' }}">
                                                        <svg width="10" height="10" viewBox="0 0 16 16" fill="currentColor"><path d="M8 1a5 5 0 0 1 3 9V2a3 3 0 0 0-6 0v8A5 5 0 0 1 8 1z"/></svg>
                                                        Hot
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                    {{-- end Temperature card --}}

                                @endif
                                {{-- end !isLocked --}}

                            </div>
                            {{-- ═══════════════════════════════════════════ --}}
                            {{-- END LEFT COLUMN                             --}}
                            {{-- ═══════════════════════════════════════════ --}}

                            {{-- ═══════════════════════════════════════════ --}}
                            {{-- RIGHT COLUMN                                --}}
                            {{-- ═══════════════════════════════════════════ --}}
                            <div class="col-md-6">

                                {{-- Activities Log --}}
                                <div class="ls-card mb-4">
                                    <div class="ls-card__head">
                                        <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
                                            <path d="M2 2h12v10H9l-3 2v-2H2V2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                        Activities Log
                                    </div>
                                    <div class="ls-card__body">

                                        {{-- Existing notes --}}
                                        @if($lead->notes)
                                            <div class="ls-notes-block mb-4">{{ $lead->notes }}</div>
                                        @endif

                                        {{-- Add note form — hidden for rejected, lost, expired --}}
                                        @if(!in_array($lead->status, ['rejected', 'lost', 'expired']))
                                            <form method="POST" action="{{ route('affiliate.leads.addNote', $lead) }}">
                                                @csrf
                                                <label class="lc-label">Add a Note</label>
                                                <textarea name="note"
                                                          rows="3"
                                                          class="lc-input lc-textarea mb-3"
                                                          placeholder="Log a call, meeting, or update…"></textarea>
                                                <button type="submit" class="ls-btn ls-btn--blue">
                                                    <svg width="13" height="13" viewBox="0 0 16 16" fill="none">
                                                        <path d="M2 2h12v10H9l-3 2v-2H2V2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                    </svg>
                                                    Add Note
                                                </button>
                                            </form>
                                        @else
                                            <p style="font-size:12px;color:#9ca3af;margin:0;">
                                                This lead is closed — no further notes can be added.
                                            </p>
                                        @endif

                                    </div>
                                </div>

                                {{-- Activity Timeline --}}
                                <div class="ls-card">
                                    <div class="ls-card__head">
                                        <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
                                            <circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.5"/>
                                            <path d="M8 5v3.5l2 1.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                        Activity Timeline
                                    </div>
                                    <div class="ls-card__body">
                                        @forelse($lead->activities as $activity)
                                            <div class="ls-timeline-item">
                                                <div class="ls-timeline-dot"></div>
                                                <div class="ls-timeline-content">
                                                    <p class="ls-timeline-time">{{ $activity->created_at->diffForHumans() }}</p>
                                                    <p class="ls-timeline-desc">{{ $activity->description }}</p>
                                                </div>
                                            </div>
                                        @empty
                                            <p style="font-size:13px;color:#9ca3af;margin:0;">No activity recorded yet.</p>
                                        @endforelse
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
        .mod-back-link { font-size:13px;font-weight:500;color:#6b7280;text-decoration:none;transition:color .15s; }
        .mod-back-link:hover { color:#111827; }

        /* ── Flash alerts ────────────────────────────────────── */
        .mod-alert { display:flex;align-items:center;gap:10px;padding:.85rem 1.1rem;border-radius:10px;font-size:14px; }
        .mod-alert--success { background:#E1F5EE;color:#0F6E56; }
        .mod-alert--danger  { background:#FCEBEB;color:#A32D2D; }

        /* ── Hero card ───────────────────────────────────────── */
        .ls-hero {
            background:#fff;border:0.5px solid #e5e7eb;border-radius:14px;
            padding:1.25rem 1.5rem;display:flex;align-items:flex-start;
            justify-content:space-between;flex-wrap:wrap;gap:1rem;
        }
        .ls-hero__left { display:flex;align-items:flex-start;gap:14px; }
        .ls-company-avatar {
            width:48px;height:48px;border-radius:12px;background:#E6F1FB;color:#185FA5;
            font-size:15px;font-weight:500;display:inline-flex;align-items:center;
            justify-content:center;flex-shrink:0;
        }
        .ls-company-name { font-size:17px;font-weight:500;color:#111827;margin:0 0 2px; }
        .ls-company-meta { font-size:13px;color:#9ca3af;margin:0; }

        /* ── Chips ───────────────────────────────────────────── */
        .ls-expiry-chip { display:inline-flex;align-items:center;gap:4px;font-size:11px;font-weight:500;padding:3px 10px;border-radius:99px; }
        .ls-expiry-chip--success { background:#E1F5EE;color:#0F6E56; }
        .ls-expiry-chip--warning { background:#FAEEDA;color:#854F0B; }
        .ls-expiry-chip--danger  { background:#FCEBEB;color:#A32D2D; }
        .ls-demo-chip { display:inline-flex;align-items:center;gap:10px;background:#EEEDFE;border:0.5px solid #AFA9EC;color:#3C3489;border-radius:10px;padding:.65rem 1rem; }
        .ls-demo-chip--missed { background:#FCEBEB;border-color:#F7C1C1;color:#A32D2D; }

        /* ── Renew button ────────────────────────────────────── */
        .ls-renew-btn {
            display:inline-flex;align-items:center;gap:7px;padding:9px 18px;
            background:#FAEEDA;color:#854F0B;font-size:13px;font-weight:500;
            border:0.5px solid #FAC775;border-radius:8px;cursor:pointer;
            white-space:nowrap;transition:background .15s,transform .15s;
        }
        .ls-renew-btn:hover { background:#fde9b8;transform:translateY(-1px); }

        /* ── Full-width banners ──────────────────────────────── */
        .ls-trial-expired-banner {
            display: flex;
            align-items: flex-start;
            gap: 14px;
            background: #FAEEDA;
            border: 0.5px solid #FAC775;
            color: #854F0B;
            border-radius: 12px;
            padding: 1.25rem 1.5rem;
        }

        .ls-converted-banner { 
            display: flex;
            align-items: flex-start;
            gap: 14px;
            background: linear-gradient(135deg, #E1F5EE 0%, #D1F0E5 100%);
            border: 0.5px solid #9FE1CB;
            color: #0F6E56;
            border-radius: 12px;
            padding: 1.25rem 1.5rem;
            box-shadow: 0 2px 8px rgba(15, 110, 86, 0.08);
        }

        .ls-trial-banner { 
            display: flex;
            align-items: flex-start;
            gap: 14px;
            background: #EEEDFE;
            border: 0.5px solid #AFA9EC;
            color: #534AB7;
            border-radius: 12px;
            padding: 1.1rem 1.25rem;
        }

        .ls-conversion-feedback-banner {
            display: flex;
            align-items: flex-start;
            gap: 14px;
            background: #FAEEDA;
            border: 0.5px solid #FAC775;
            color: #854F0B;
            border-radius: 12px;
            padding: 1.1rem 1.25rem;
        }

        .ls-rejected-banner { 
            display: flex;
            align-items: flex-start;
            gap: 14px;
            background: #FCEBEB;
            border: 0.5px solid #F7C1C1;
            color: #A32D2D;
            border-radius: 12px;
            padding: 1.1rem 1.25rem;
        }
        
        /* ── Section cards ───────────────────────────────────── */
        .ls-card { background:#fff;border:0.5px solid #e5e7eb;border-radius:12px;overflow:hidden; }
        .ls-card__head { display:flex;align-items:center;gap:8px;padding:.8rem 1.1rem;border-bottom:0.5px solid #e5e7eb;background:#fafafa;font-size:13px;font-weight:500;color:#374151; }
        .ls-card__body { padding:1.1rem; }

        /* ── Detail rows ─────────────────────────────────────── */
        .ls-detail-row { display:flex;justify-content:space-between;align-items:center;padding:.55rem 0;border-bottom:0.5px solid #f3f4f6;font-size:13px; }
        .ls-detail-row:last-child { border-bottom:none; }
        .ls-detail-label { color:#9ca3af;font-weight:500; }
        .ls-detail-val   { color:#111827;font-weight:500;text-align:right; }

        /* ── Action label ────────────────────────────────────── */
        .ls-action-label { font-size:12px;font-weight:500;color:#9ca3af;text-transform:uppercase;letter-spacing:.05em;margin-bottom:8px; }

        /* ── Pending conversion ──────────────────────────────── */
        .ls-pending-conversion { display:flex;align-items:flex-start;gap:10px;background:#EEEDFE;border:0.5px solid #AFA9EC;color:#3C3489;border-radius:10px;padding:.85rem 1rem;font-size:13px; }

        /* ── Action buttons ──────────────────────────────────── */
        .ls-btn { display:inline-flex;align-items:center;gap:6px;padding:8px 16px;font-size:13px;font-weight:500;border-radius:8px;border:none;cursor:pointer;text-decoration:none;white-space:nowrap;transition:opacity .15s,transform .15s; }
        .ls-btn:hover { opacity:.88;transform:translateY(-1px); }
        .ls-btn--blue   { background:#185FA5;color:#fff; }
        .ls-btn--green  { background:#1D9E75;color:#fff; }
        .ls-btn--purple { background:#534AB7;color:#fff; }
        .ls-btn--red    { background:#FCEBEB;color:#A32D2D;border:0.5px solid #F7C1C1; }
        .ls-btn--amber  { background:#FAEEDA;color:#854F0B;border:0.5px solid #FAC775; }

        /* ── Reject panel ────────────────────────────────────── */
        .ls-reject-panel { max-height:0;overflow:hidden;opacity:0;transition:max-height .3s ease,opacity .3s ease; }
        .ls-reject-panel--open { max-height:500px;opacity:1;margin-top:12px; }
        .ls-reject-panel__title { font-size:12px;font-weight:500;color:#374151;text-transform:uppercase;letter-spacing:.05em;margin-bottom:10px; }
        .ls-reject-reasons { display:flex;flex-direction:column;gap:6px; }
        .ls-reject-reason { display:flex;align-items:center;gap:8px;padding:7px 12px;border:0.5px solid #e5e7eb;border-radius:8px;cursor:pointer;font-size:13px;color:#374151;background:#fafafa;transition:background .15s,border-color .15s; }
        .ls-reject-reason:has(input:checked) { background:#FCEBEB;border-color:#F7C1C1;color:#A32D2D; }
        .ls-reject-reason input[type="radio"] { accent-color:#A32D2D;width:14px;height:14px;flex-shrink:0; }
        .ls-reject-other { display:none; }
        .ls-reject-other--visible { display:block; }

        /* ── Notes block ─────────────────────────────────────── */
        .ls-notes-block { background:#fafafa;border:0.5px solid #e5e7eb;border-radius:8px;padding:.85rem 1rem;font-size:13px;color:#374151;line-height:1.65;white-space:pre-wrap; }

        /* ── Timeline ────────────────────────────────────────── */
        .ls-timeline-item { display:flex;gap:12px;padding-bottom:1rem;position:relative; }
        .ls-timeline-item:last-child { padding-bottom:0; }
        .ls-timeline-item:not(:last-child)::before { content:'';position:absolute;left:5px;top:14px;bottom:0;width:1px;background:#e5e7eb; }
        .ls-timeline-dot { width:11px;height:11px;border-radius:50%;background:#185FA5;border:2px solid #E6F1FB;flex-shrink:0;margin-top:3px; }
        .ls-timeline-time { font-size:11px;color:#9ca3af;font-weight:500;margin:0 0 2px; }
        .ls-timeline-desc { font-size:13px;color:#374151;margin:0;line-height:1.5; }

        /* ── Form elements ───────────────────────────────────── */
        .lc-label { display:block;font-size:12px;font-weight:500;color:#374151;text-transform:uppercase;letter-spacing:.05em;margin-bottom:6px; }
        .lc-input { width:100%;padding:9px 12px;font-size:14px;color:#111827;background:#fff;border:0.5px solid #d1d5db;border-radius:8px;outline:none;transition:border-color .15s,box-shadow .15s;appearance:none; }
        .lc-input:focus { border-color:#185FA5;box-shadow:0 0 0 3px rgba(24,95,165,.1); }
        .lc-textarea { resize:vertical;min-height:90px; }

        /* ── Divider ─────────────────────────────────────────── */
        .lc-divider { display:flex;align-items:center;gap:10px;font-size:11px;font-weight:500;color:#9ca3af;text-transform:uppercase;letter-spacing:.06em; }
        .lc-divider::before,.lc-divider::after { content:'';flex:1;height:0.5px;background:#e5e7eb; }

        /* ── Temperature buttons ─────────────────────────────── */
        .lc-temp-group { display:flex;gap:8px;flex-wrap:wrap; }
        .lc-temp-option { display:inline-flex;align-items:center;gap:6px;padding:7px 14px;border-radius:8px;border:0.5px solid #e5e7eb;font-size:13px;font-weight:500;cursor:pointer;background:#fafafa;color:#6b7280;transition:background .15s,border-color .15s; }
        .lc-temp-option--blue:hover,  .lc-temp-active--blue  { background:#E6F1FB;border-color:#B5D4F4;color:#185FA5; }
        .lc-temp-option--amber:hover, .lc-temp-active--amber { background:#FAEEDA;border-color:#FAC775;color:#854F0B; }
        .lc-temp-option--coral:hover, .lc-temp-active--coral { background:#FAECE7;border-color:#F5C4B3;color:#993C1D; }

        /* ── Status + temp badges ────────────────────────────── */
        .leads-status-badge, .leads-temp-badge { display:inline-flex;align-items:center;gap:4px;font-size:11px;font-weight:500;padding:3px 10px;border-radius:99px;white-space:nowrap; }
        .leads-status-badge--active             { background:#E1F5EE;color:#0F6E56; }
        .leads-status-badge--demo_scheduled     { background:#E6F1FB;color:#185FA5; }
        .leads-status-badge--demo_completed     { background:#EEEDFE;color:#534AB7; }
        .leads-status-badge--pending_conversion { background:#EEEDFE;color:#3C3489; }
        .leads-status-badge--trial              { background:#EEEDFE;color:#534AB7; }
        .leads-status-badge--converted          { background:#E1F5EE;color:#085041; }
        .leads-status-badge--rejected           { background:#FCEBEB;color:#A32D2D; }
        .leads-status-badge--expired            { background:#f3f4f6;color:#5F5E5A; }
        .leads-status-badge--lost               { background:#FAECE7;color:#993C1D; }
        .leads-temp-badge--hot  { background:#FAECE7;color:#993C1D; }
        .leads-temp-badge--warm { background:#FAEEDA;color:#854F0B; }
        .leads-temp-badge--cold { background:#E6F1FB;color:#185FA5; }

    </style>

    <script>
        function handleRejectReason(input) {
            const otherWrap = document.getElementById('rejectOtherWrap');
            const otherText = document.getElementById('rejectOtherText');
            if (input.value === 'other') {
                otherWrap.classList.add('ls-reject-other--visible');
                otherText.required = true;
            } else {
                otherWrap.classList.remove('ls-reject-other--visible');
                otherText.required = false;
                otherText.value = '';
            }
        }
    </script>

@endsection