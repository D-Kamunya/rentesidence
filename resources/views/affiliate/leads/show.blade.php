@extends('affiliate.layouts.app')

@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="page-content-wrapper bg-white p-30 radius-20">

                    {{-- Page Title --}}
                    @php
                        $pageTitle = 'Lead View';
                    @endphp
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
                            $isPendingExtension = $lead->status === 'pending_conversion'
                                && $latestTrialActivity?->type === 'trial_extension';
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
                            // Completeness — passed from controller, also computed inline for field-level display
                            $fieldMap = [
                                'Company Name'   => $lead->company->company_name    ?? null,
                                'Country'        => $lead->company->country         ?? null,
                                'City'           => $lead->company->city            ?? null,
                                'Phone'          => $lead->company->phone           ?? null,
                                'Email'          => $lead->company->email           ?? null,
                                'Website'        => $lead->company->website         ?? null,
                                'Property Type'  => $lead->company->property_type   ?? null,
                                'Est. Units'     => $lead->company->estimated_units ?? null,
                                'Contact Name'   => $lead->contact_person_name      ?? null,
                                'Contact Role'   => $lead->contact_person_role      ?? null,
                            ];
                            $score      = $completeness;
                            $scoreColor = $score < 40 ? '#A32D2D' : ($score < 70 ? '#854F0B' : '#0F6E56');
                            $scoreBg    = $score < 40 ? '#FCEBEB' : ($score < 70 ? '#FAEEDA' : '#E1F5EE');
                            $barColor   = $score < 40 ? '#E24B4A' : ($score < 70 ? '#FAC775' : '#1D9E75');
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

                                        @elseif($lead->status === 'pending_conversion' && $isPendingExtension)
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
                                                    $rejection = $lead->activities->where('type', 'conversion_rejected')->first();
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
                            {{-- Claimed from marketplace Banner --}}
                            @php
                                $wasClaimedFromMarketplace = $lead->source === 'admin' && !is_null($lead->claimed_at);
                                $hasPriorHistory = $lead->marketplace_cycles > 1
                                    || $lead->activities->where('user_id', null)->count() > 0;
                            @endphp
                            
                            @if($wasClaimedFromMarketplace && $hasPriorHistory)
                                <div class="col-12">
                                    <div class="ls-marketplace-history-banner">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" style="flex-shrink:0;margin-top:1px;">
                                            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.5"/>
                                            <path d="M12 8v4M12 16v.5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                        </svg>
                                        <div>
                                            <p style="font-weight:500;font-size:14px;margin:0 0 4px;">This lead has prior history</p>
                                            <p style="font-size:12px;margin:0;line-height:1.65;opacity:.85;">
                                                This lead was worked by a previous affiliate before returning to the marketplace.
                                                Their activity is visible in the timeline below — use it to understand what was
                                                already tried and pick up where they left off.
                                                @if($lead->marketplace_cycles > 1)
                                                    This lead has cycled through the marketplace
                                                    <strong>{{ $lead->marketplace_cycles - 1 }} {{ Str::plural('time', $lead->marketplace_cycles - 1) }}</strong> before you claimed it.
                                                @endif
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
                                        @if($lead->source === 'admin')
                                            <div class="ls-detail-row" style="border-bottom:none;padding-top:.75rem;margin-top:.25rem;border-top:0.5px solid #f3f4f6;">
                                                <span class="ls-detail-label" style="display:flex;align-items:center;gap:5px;">
                                                    Origin
                                                </span>
                                                <div style="display:flex;flex-direction:column;align-items:flex-end;gap:4px;">
                                                    <span style="font-size:11px;font-weight:500;padding:3px 9px;border-radius:99px;background:#E6F1FB;color:#185FA5;border:0.5px solid #B5D4F4;">
                                                        Marketplace
                                                    </span>
                                                    <span style="font-size:11px;color:#9ca3af;text-align:right;line-height:1.5;">
                                                        If this lead expires unclaimed it returns<br>to the marketplace automatically.
                                                    </span>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                {{-- ── Completeness Breakdown ───────────── --}}
                                <div class="mps-card mb-4">
                                    <div class="mps-card__head">
                                        <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
                                            <circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.5"/>
                                            <path d="M8 5v3.5l2 1.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                        Lead Profile Completeness
                                        <span style="margin-left:auto;font-size:12px;font-weight:500;padding:2px 8px;border-radius:99px;background:{{ $scoreBg }};color:{{ $scoreColor }};">
                                            {{ $score }}%
                                        </span>
                                    </div>
                                    <div class="mps-card__body">

                                        {{-- Overall bar --}}
                                        <div class="mps-overall-bar-wrap mb-4">
                                            <div class="mps-overall-bar" style="width:{{ $score }}%;background:{{ $barColor }};"></div>
                                        </div>

                                        {{-- Per-field checklist --}}
                                        <div class="mps-field-list">
                                            @foreach($fieldMap as $label => $value)
                                                @php $filled = !is_null($value) && $value !== ''; @endphp
                                                <div class="mps-field-item">
                                                    @if($filled)
                                                        <span class="mps-field-check mps-field-check--done">
                                                            <svg width="10" height="10" viewBox="0 0 16 16" fill="none">
                                                                <path d="M3 8.5l3.5 3.5 6.5-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                            </svg>
                                                        </span>
                                                    @else
                                                        <span class="mps-field-check mps-field-check--missing">
                                                            <svg width="10" height="10" viewBox="0 0 16 16" fill="none">
                                                                <path d="M4 4l8 8M12 4l-8 8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                                            </svg>
                                                        </span>
                                                    @endif
                                                    <span class="mps-field-label {{ $filled ? '' : 'mps-field-label--missing' }}">
                                                        {{ $label }}
                                                    </span>
                                                    @if($filled)
                                                        <span class="mps-field-val">
                                                            {{ is_string($value) && strlen($value) > 30 ? substr($value, 0, 30) . '…' : $value }}
                                                        </span>
                                                    @else
                                                        <span class="mps-field-missing-tag">Missing</span>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>

                                        @if($score < 100)
                                            <p style="font-size:11px;color:#9ca3af;margin:12px 0 0;line-height:1.5;">
                                                Complete all lead details to benefit from system lead engagement automation.
                                            </p>
                                        @endif
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
                                            @if($lead->status === 'pending_conversion' && $isPendingExtension )
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
                                            @if($lead->status === 'pending_conversion' && !$isExpiredTrial && !$isPendingExtension)
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

                                    {{-- Demo Prep Card — only when demo is scheduled --}}
                                    @if($lead->status === 'demo_scheduled')
                                        @php
                                            $demoSections = \App\Models\DemoPrepSection::active()->get();
                                            $demoSettings = \App\Models\DemoSetting::current();
                                        @endphp

                                        <div class="ls-card mb-4">
                                            <div class="ls-card__head"
                                                style="cursor:pointer;user-select:none;"
                                                onclick="toggleDemoPrep()">
                                                <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
                                                    <rect x="2" y="3" width="12" height="11" rx="2" stroke="currentColor" stroke-width="1.5"/>
                                                    <path d="M5 1v3M11 1v3M2 7h12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                                </svg>
                                                Demo Preparation Guide
                                                @if($lead->demo_scheduled_at)
                                                    <span style="font-size:11px;font-weight:400;color:#9ca3af;margin-left:6px;">
                                                        {{ $lead->demo_scheduled_at->format('d M Y, H:i') }}
                                                    </span>
                                                @endif
                                                <svg width="12" height="12" viewBox="0 0 16 16" fill="none"
                                                    id="demoPrepChevron"
                                                    style="margin-left:auto;transition:transform .2s;flex-shrink:0;">
                                                    <path d="M4 6l4 4 4-4" stroke="currentColor" stroke-width="1.5"
                                                        stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                            </div>

                                            <div id="demoPrepBody" style="display:none;">

                                                {{-- Credentials block --}}
                                                @if($demoSettings->demo_email || $demoSettings->demo_login_url)
                                                    <div style="padding:1rem 1.1rem;border-bottom:0.5px solid #e5e7eb;
                                                                background:#EEF5FD;">
                                                        <p style="font-size:11px;font-weight:500;text-transform:uppercase;
                                                                letter-spacing:.05em;color:#185FA5;margin:0 0 10px;">
                                                            🔐 Demo Account
                                                        </p>
                                                        <div style="display:flex;flex-direction:column;gap:6px;">
                                                            @if($demoSettings->demo_login_url)
                                                                <div style="display:flex;justify-content:space-between;
                                                                            align-items:center;font-size:13px;">
                                                                    <span style="color:#6b7280;font-weight:500;">Login URL</span>
                                                                    <a href="{{ $demoSettings->demo_login_url }}" target="_blank"
                                                                    style="color:#185FA5;font-weight:500;text-decoration:none;
                                                                            font-size:12px;">
                                                                        {{ $demoSettings->demo_login_url }}
                                                                    </a>
                                                                </div>
                                                            @endif
                                                            @if($demoSettings->demo_email)
                                                                <div style="display:flex;justify-content:space-between;
                                                                            align-items:center;font-size:13px;">
                                                                    <span style="color:#6b7280;font-weight:500;">Email</span>
                                                                    <span style="font-weight:500;color:#111827;font-family:monospace;
                                                                                font-size:12px;">
                                                                        {{ $demoSettings->demo_email }}
                                                                    </span>
                                                                </div>
                                                            @endif
                                                            @if($demoSettings->demo_password)
                                                                <div style="display:flex;justify-content:space-between;
                                                                            align-items:center;font-size:13px;">
                                                                    <span style="color:#6b7280;font-weight:500;">Password</span>
                                                                    <span style="font-weight:500;color:#111827;font-family:monospace;
                                                                                font-size:12px;">
                                                                        {{ $demoSettings->demo_password }}
                                                                    </span>
                                                                </div>
                                                            @endif
                                                            @if($demoSettings->demo_notes)
                                                                <p style="font-size:11px;color:#185FA5;margin:6px 0 0;
                                                                        font-style:italic;">
                                                                    ℹ️ {{ $demoSettings->demo_notes }}
                                                                </p>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endif

                                                {{-- Prep sections --}}
                                                @if($demoSections->count() > 0)
                                                    <div style="padding:.75rem 1.1rem 1.1rem;">
                                                        @foreach($demoSections as $i => $section)
                                                            <div class="dp-affiliate-section"
                                                                id="demoSection{{ $i }}">
                                                                <button type="button"
                                                                        class="dp-affiliate-section__head"
                                                                        onclick="toggleDemoSection({{ $i }})">
                                                                    <span class="dp-affiliate-section__num">{{ $i + 1 }}</span>
                                                                    <span style="flex:1;text-align:left;">{{ $section->title }}</span>
                                                                    <svg width="11" height="11" viewBox="0 0 16 16" fill="none"
                                                                        id="demoSectionChevron{{ $i }}"
                                                                        style="transition:transform .2s;flex-shrink:0;">
                                                                        <path d="M4 6l4 4 4-4" stroke="currentColor" stroke-width="1.5"
                                                                            stroke-linecap="round" stroke-linejoin="round"/>
                                                                    </svg>
                                                                </button>
                                                                <div class="dp-affiliate-section__body"
                                                                    id="demoSectionBody{{ $i }}"
                                                                    style="display:none;">{{ trim($section->content) }}
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <div style="padding:1.1rem;">
                                                        <p style="font-size:13px;color:#9ca3af;margin:0;">
                                                            No prep guide sections configured yet.
                                                        </p>
                                                    </div>
                                                @endif

                                            </div>
                                        </div>
                                    @endif

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
                                        @if(!$lead->isClosed())
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
                                {{-- ═══════════════════════════════════════════════ --}}
                                {{-- SUGGESTED ACTIONS CARD                          --}}
                                {{-- ═══════════════════════════════════════════════ --}}
                                @if($lead->isClosed())
                                    <div class="ls-card mb-4">
                                        <div class="ls-card__head">
                                            <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
                                                <path d="M2 2h12v10H9l-3 2v-2H2V2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                            Suggested Next Actions
                                        </div>
                                        <div class="ls-card__body">
                                            <div class="ls-empty-state">
                                                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" style="color:#ef4444;margin-bottom:12px;">
                                                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.5"/>
                                                    <path d="M9 9l6 6M15 9l-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                                <p style="color:#9ca3af;font-size:14px;margin:0;font-weight:500;">Lead Closed</p>
                                                <p style="color:#9ca3af;font-size:13px;margin:4px 0 0;">
                                                    No suggested actions for expired, rejected or lost leads.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <div class="ls-card mb-4">
                                        <div class="ls-card__head">
                                            <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
                                                <path d="M8 2l2 4h4l-3 3 1 4-4-2-4 2 1-4-3-3h4z" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                            Suggested Next Actions
                                            @if($suggestions->where('priority', 'high')->count() > 0)
                                                <span class="ls-suggestion-count">{{ $suggestions->where('priority', 'high')->count() }} urgent</span>
                                            @endif
                                            @if($suggestions->count() > 0)
                                                <div class="ls-suggestion-stats">
                                                    <span class="ls-stat-item">
                                                        <svg width="10" height="10" viewBox="0 0 16 16" fill="none">
                                                            <circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.5"/>
                                                        </svg>
                                                        {{ $suggestions->count() }} total
                                                    </span>
                                                    @foreach(['call' => '📞', 'whatsapp' => '💬', 'email' => '📧'] as $type => $emoji)
                                                        @if($suggestions->where('action_type', $type)->count() > 0)
                                                            <span class="ls-stat-item">
                                                                {{ $emoji }} {{ $suggestions->where('action_type', $type)->count() }}
                                                            </span>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>

                                        {{-- Quick filters — only render if more than one suggestion --}}
                                        @if($suggestions->count() > 1)
                                            <div class="ls-quick-filters">
                                                <button class="ls-filter-btn active" onclick="filterSuggestions(event, 'all')">
                                                    All ({{ $suggestions->count() }})
                                                </button>
                                                @if($suggestions->where('priority', 'high')->count() > 0)
                                                    <button class="ls-filter-btn" onclick="filterSuggestions(event, 'high')">
                                                        🔥 Urgent ({{ $suggestions->where('priority', 'high')->count() }})
                                                    </button>
                                                @endif
                                                @if($suggestions->where('action_type', 'call')->count() > 0)
                                                    <button class="ls-filter-btn" onclick="filterSuggestions(event, 'call')">
                                                        📞 Calls ({{ $suggestions->where('action_type', 'call')->count() }})
                                                    </button>
                                                @endif
                                                @if($suggestions->where('action_type', 'whatsapp')->count() > 0)
                                                    <button class="ls-filter-btn" onclick="filterSuggestions(event, 'whatsapp')">
                                                        💬 WhatsApp ({{ $suggestions->where('action_type', 'whatsapp')->count() }})
                                                    </button>
                                                @endif
                                                @if($suggestions->where('action_type', 'email')->count() > 0)
                                                    <button class="ls-filter-btn" onclick="filterSuggestions(event, 'email')">
                                                        📧 Email ({{ $suggestions->where('action_type', 'email')->count() }})
                                                    </button>
                                                @endif
                                            </div>
                                        @endif

                                        <div class="ls-card__body">
                                            @forelse($suggestions as $suggestion)
                                                @php
                                                    // Use pre-loaded templates — zero extra queries
                                                    $categoryTemplates    = $templatesByCategory->get($suggestion->category, collect());
                                                    $whatsappTemplates    = $categoryTemplates->where('action_type', 'whatsapp')->values();
                                                    $emailTemplates       = $categoryTemplates->where('action_type', 'email')->values();
                                                    $callTemplates        = $categoryTemplates->where('action_type', 'call')->values();

                                                    // Ownership guard — affiliate should only act on their own lead's suggestions
                                                    $canAct = $lead->affiliate_id === auth()->id();
                                                @endphp

                                                <div class="ls-suggestion-item {{ $suggestion->priority === 'high' ? 'ls-suggestion-item--urgent' : '' }}"
                                                    data-action-type="{{ $suggestion->action_type }}"
                                                    data-priority="{{ $suggestion->priority }}">

                                                    {{-- Header: priority + time --}}
                                                    <div class="ls-suggestion-header">
                                                        @if($suggestion->priority === 'high')
                                                            <span class="ls-priority-badge ls-priority-badge--high">
                                                                <svg width="10" height="10" viewBox="0 0 16 16" fill="none">
                                                                    <path d="M8 2l6 12H2L8 2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                                    <path d="M8 7v3M8 12v.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                                                </svg>
                                                                Urgent
                                                            </span>
                                                        @elseif($suggestion->priority === 'medium')
                                                            <span class="ls-priority-badge ls-priority-badge--medium">
                                                                <svg width="10" height="10" viewBox="0 0 16 16" fill="none">
                                                                    <circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.5"/>
                                                                    <path d="M8 5v4M8 11v.5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                                                                </svg>
                                                                Medium
                                                            </span>
                                                        @else
                                                            <span class="ls-priority-badge ls-priority-badge--low">
                                                                <svg width="10" height="10" viewBox="0 0 16 16" fill="none">
                                                                    <circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.5"/>
                                                                </svg>
                                                                Low
                                                            </span>
                                                        @endif

                                                        {{-- Category label --}}
                                                        <span style="font-size:11px;color:#9ca3af;">
                                                            {{ ucfirst(str_replace('_', ' ', $suggestion->category)) }}
                                                        </span>

                                                        {{-- Expiry warning if close --}}
                                                        @if($suggestion->expires_at)
                                                            @php
                                                                $exp = \Carbon\Carbon::parse($suggestion->expires_at);
                                                                $expSoon = !$exp->isPast() && $exp->diffInHours(now()) <= 24;
                                                            @endphp
                                                            @if($expSoon)
                                                                <span style="font-size:10px;font-weight:500;background:#FCEBEB;color:#A32D2D;padding:2px 7px;border-radius:99px;">
                                                                    Expires {{ $exp->diffForHumans() }}
                                                                </span>
                                                            @endif
                                                        @endif

                                                        <span class="ls-time-badge" style="margin-left:auto;">
                                                            <svg width="9" height="9" viewBox="0 0 16 16" fill="none">
                                                                <circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.5"/>
                                                                <path d="M8 5v3.5l2 1.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                            </svg>
                                                            {{ $suggestion->created_at->diffForHumans() }}
                                                        </span>
                                                    </div>

                                                    {{-- Message --}}
                                                    <div class="ls-suggestion-message">{{ $suggestion->message }}</div>

                                                    {{-- Action buttons --}}
                                                    @if($canAct && !$isLocked)
                                                        <div class="ls-suggestion-actions">
                                                            <div class="ls-action-buttons">

                                                                {{-- WhatsApp --}}
                                                                @if($whatsappTemplates->count() === 1)
                                                                    <a href="{{ route('affiliate.action.whatsapp', [$lead->id, $whatsappTemplates->first()->id]) }}"
                                                                    class="ls-action-btn ls-action-btn--whatsapp">
                                                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor">
                                                                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L0 24l6.304-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                                                                        </svg>
                                                                        WhatsApp
                                                                    </a>
                                                                @elseif($whatsappTemplates->count() > 1)
                                                                    <div class="ls-action-dropdown">
                                                                        <button type="button" class="ls-action-btn ls-action-btn--whatsapp" onclick="toggleDropdown(this)">
                                                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor">
                                                                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L0 24l6.304-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                                                                            </svg>
                                                                            WhatsApp
                                                                            <svg width="10" height="10" viewBox="0 0 16 16" fill="none">
                                                                                <path d="M4 6l4 4 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                                            </svg>
                                                                        </button>
                                                                        <div class="ls-dropdown-menu">
                                                                            @foreach($whatsappTemplates as $tmpl)
                                                                                <a href="{{ route('affiliate.action.whatsapp', [$lead->id, $tmpl->id]) }}" class="ls-dropdown-item">
                                                                                    <div class="ls-dropdown-item__title">{{ $tmpl->name }}</div>
                                                                                    <div class="ls-dropdown-item__desc">{{ Str::limit($tmpl->description ?? 'WhatsApp template', 50) }}</div>
                                                                                </a>
                                                                            @endforeach
                                                                        </div>
                                                                    </div>
                                                                @endif

                                                                {{-- Email --}}
                                                                @if($emailTemplates->count() === 1)
                                                                    <a href="{{ route('affiliate.action.email', [$lead->id, $emailTemplates->first()->id]) }}"
                                                                    class="ls-action-btn ls-action-btn--email">
                                                                        <svg width="12" height="12" viewBox="0 0 16 16" fill="none">
                                                                            <rect x="2" y="4" width="12" height="9" rx="1.5" stroke="currentColor" stroke-width="1.5"/>
                                                                            <path d="M2 5l6 4 6-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                                        </svg>
                                                                        Email
                                                                    </a>
                                                                @elseif($emailTemplates->count() > 1)
                                                                    <div class="ls-action-dropdown">
                                                                        <button type="button" class="ls-action-btn ls-action-btn--email" onclick="toggleDropdown(this)">
                                                                            <svg width="12" height="12" viewBox="0 0 16 16" fill="none">
                                                                                <rect x="2" y="4" width="12" height="9" rx="1.5" stroke="currentColor" stroke-width="1.5"/>
                                                                                <path d="M2 5l6 4 6-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                                            </svg>
                                                                            Email
                                                                            <svg width="10" height="10" viewBox="0 0 16 16" fill="none">
                                                                                <path d="M4 6l4 4 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                                            </svg>
                                                                        </button>
                                                                        <div class="ls-dropdown-menu">
                                                                            @foreach($emailTemplates as $tmpl)
                                                                                <a href="{{ route('affiliate.action.email', [$lead->id, $tmpl->id]) }}" class="ls-dropdown-item">
                                                                                    <div class="ls-dropdown-item__title">{{ $tmpl->name }}</div>
                                                                                    <div class="ls-dropdown-item__desc">{{ Str::limit($tmpl->description ?? 'Email template', 50) }}</div>
                                                                                </a>
                                                                            @endforeach
                                                                        </div>
                                                                    </div>
                                                                @endif

                                                                {{-- Call — script view if template exists, direct dial if not --}}
                                                                @if($callTemplates->count() === 1)
                                                                    {{-- Has script: go to call blade first, activity logged when they actually dial --}}
                                                                    <a href="{{ route('affiliate.action.call.view', [$lead->id, $callTemplates->first()->id]) }}"
                                                                    class="ls-action-btn ls-action-btn--call">
                                                                        <svg width="12" height="12" viewBox="0 0 16 16" fill="none">
                                                                            <path d="M3.5 1h3l1.5 3.5-2 1.5a9 9 0 0 0 4 4l1.5-2L15 9.5v3a2 2 0 0 1-2 2A12 12 0 0 1 1 3a2 2 0 0 1 2-2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                                        </svg>
                                                                        Call
                                                                        <span style="font-size:10px;opacity:.75;">+ Script</span>
                                                                    </a>
                                                                @elseif($callTemplates->count() > 1)
                                                                    {{-- Multiple scripts: dropdown, all go to call blade first --}}
                                                                    <div class="ls-action-dropdown">
                                                                        <button type="button" class="ls-action-btn ls-action-btn--call" onclick="toggleDropdown(this)">
                                                                            <svg width="12" height="12" viewBox="0 0 16 16" fill="none">
                                                                                <path d="M3.5 1h3l1.5 3.5-2 1.5a9 9 0 0 0 4 4l1.5-2L15 9.5v3a2 2 0 0 1-2 2A12 12 0 0 1 1 3a2 2 0 0 1 2-2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                                            </svg>
                                                                            Call
                                                                            <svg width="10" height="10" viewBox="0 0 16 16" fill="none">
                                                                                <path d="M4 6l4 4 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                                            </svg>
                                                                        </button>
                                                                        <div class="ls-dropdown-menu">
                                                                            @foreach($callTemplates as $tmpl)
                                                                                <a href="{{ route('affiliate.action.call.view', [$lead->id, $tmpl->id]) }}" class="ls-dropdown-item">
                                                                                    <div class="ls-dropdown-item__title">{{ $tmpl->name }}</div>
                                                                                    <div class="ls-dropdown-item__desc">{{ Str::limit($tmpl->description ?? 'Call script', 50) }}</div>
                                                                                </a>
                                                                            @endforeach
                                                                        </div>
                                                                    </div>
                                                                @else
                                                                    {{-- No script: goes through controller, logs activity immediately --}}
                                                                    <a href="{{ route('affiliate.action.call', $lead->id) }}"
                                                                    class="ls-action-btn ls-action-btn--call">
                                                                        <svg width="12" height="12" viewBox="0 0 16 16" fill="none">
                                                                            <path d="M3.5 1h3l1.5 3.5-2 1.5a9 9 0 0 0 4 4l1.5-2L15 9.5v3a2 2 0 0 1-2 2A12 12 0 0 1 1 3a2 2 0 0 1 2-2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                                        </svg>
                                                                        Call Now
                                                                    </a>
                                                                @endif
                                                            </div>

                                                            {{-- Management actions --}}
                                                            <div class="ls-management-actions">
                                                                <form method="POST" action="{{ route('affiliate.suggestions.complete', $suggestion->id) }}" style="display:inline;">
                                                                    @csrf
                                                                    <button type="submit" class="ls-mgmt-btn ls-mgmt-btn--complete">
                                                                        <svg width="11" height="11" viewBox="0 0 16 16" fill="none">
                                                                            <circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.5"/>
                                                                            <path d="M4.5 8.5l2.5 2.5 4.5-5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                                                                        </svg>
                                                                        Done
                                                                    </button>
                                                                </form>
                                                                <form method="POST" action="{{ route('affiliate.suggestions.dismiss', $suggestion->id) }}" style="display:inline;">
                                                                    @csrf
                                                                    <button type="submit" class="ls-mgmt-btn ls-mgmt-btn--dismiss">
                                                                        <svg width="11" height="11" viewBox="0 0 16 16" fill="none">
                                                                            <path d="M4 4l8 8M12 4l-8 8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                                                        </svg>
                                                                        Dismiss
                                                                    </button>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    @elseif(!$canAct)
                                                        {{-- Admin or non-owning affiliate viewing this lead --}}
                                                        <p style="font-size:12px;color:#9ca3af;margin:8px 0 0;">
                                                            Actions are only available to the assigned affiliate.
                                                        </p>
                                                    @endif

                                                </div>
                                            @empty
                                                <div class="ls-empty-state">
                                                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" style="color:#d1d5db;margin-bottom:12px;">
                                                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.5"/>
                                                        <path d="M8 12l2 2 4-4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                    </svg>
                                                    <p style="color:#9ca3af;font-size:14px;margin:0;font-weight:500;">All caught up!</p>
                                                    <p style="color:#9ca3af;font-size:13px;margin:4px 0 0;">No suggested actions right now.</p>
                                                </div>
                                            @endforelse
                                        </div>
                                    </div>
                                @endif

                                {{-- Activity Timeline --}}
                                <div class="ls-card">
                                    <div class="ls-card__head">
                                        <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
                                            <circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.5"/>
                                            <path d="M8 5v3.5l2 1.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                        Activity Timeline
                                        @if($lead->activities->count() > 0)
                                            <span style="margin-left:auto;font-size:11px;color:#9ca3af;font-weight:400;">
                                                {{ $lead->activities->count() }} {{ Str::plural('event', $lead->activities->count()) }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="ls-card__body" style="padding-bottom:0;">
                                        @forelse($lead->activities as $index => $activity)
                                            <div class="ls-timeline-item activity-entry {{ $index >= 5 ? 'activity-entry--hidden' : '' }}"
                                                style="{{ $index >= 5 ? 'display:none;' : '' }}">
                                                <div class="ls-timeline-dot {{ is_null($activity->user_id) ? 'ls-timeline-dot--anon' : '' }}"></div>
                                                <div class="ls-timeline-content">
                                
                                                    {{-- Time + optional anon chip --}}
                                                    <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                                                        <p class="ls-timeline-time" style="margin:0;">
                                                            {{ $activity->created_at->diffForHumans() }}
                                                        </p>
                                                        @if(is_null($activity->user_id))
                                                            <span class="ls-timeline-anon-chip">
                                                                <svg width="9" height="9" viewBox="0 0 16 16" fill="none">
                                                                    <circle cx="8" cy="6" r="3" stroke="currentColor" stroke-width="1.5"/>
                                                                    <path d="M2 14c0-3 2.7-5 6-5s6 2 6 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                                                </svg>
                                                                Previous affiliate
                                                            </span>
                                                        @endif
                                                    </div>
                                
                                                    <p class="ls-timeline-desc">{{ $activity->description }}</p>
                                                </div>
                                            </div>
                                        @empty
                                            <p style="font-size:13px;color:#9ca3af;margin:0;padding-bottom:1.1rem;">
                                                No activity recorded yet.
                                            </p>
                                        @endforelse
                                
                                        {{-- Load more / collapse controls --}}
                                        @if($lead->activities->count() > 5)
                                            <div class="activity-controls" id="activityControls">
                                                {{-- Load more button --}}
                                                <button type="button"
                                                        class="activity-load-more"
                                                        id="activityLoadMore"
                                                        onclick="loadMoreActivities()">
                                                    <svg width="12" height="12" viewBox="0 0 16 16" fill="none" id="loadMoreIcon">
                                                        <path d="M8 3v10M3 8h10" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                                    </svg>
                                                    Show {{ min(5, $lead->activities->count() - 5) }} more
                                                    <span class="activity-load-more__count" id="remainingCount">
                                                        {{ $lead->activities->count() - 5 }} remaining
                                                    </span>
                                                </button>
                                
                                                {{-- Collapse button — hidden until expanded --}}
                                                <button type="button"
                                                        class="activity-collapse"
                                                        id="activityCollapse"
                                                        style="display:none;"
                                                        onclick="collapseActivities()">
                                                    <svg width="12" height="12" viewBox="0 0 16 16" fill="none">
                                                        <path d="M4 10l4-4 4 4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                                    </svg>
                                                    Collapse
                                                </button>
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
        /* ── Completeness overall bar ────────────────────────── */
        .mps-overall-bar-wrap { height:8px;background:#e5e7eb;border-radius:99px;overflow:hidden; }
        .mps-overall-bar { height:100%;border-radius:99px;transition:width .4s cubic-bezier(.4,0,.2,1); }
        
        /* ── Field checklist ─────────────────────────────────── */
        .mps-card { background:#fff;border:0.5px solid #e5e7eb;border-radius:12px;overflow:hidden; }
        .mps-card__head { display:flex;align-items:center;gap:8px;padding:.8rem 1.1rem;border-bottom:0.5px solid #e5e7eb;background:#fafafa;font-size:13px;font-weight:500;color:#374151; }
        .mps-card__body { padding:1.1rem; }
        .mps-field-list { display:flex;flex-direction:column;gap:6px; }
        .mps-field-item { display:flex;align-items:center;gap:8px;font-size:13px; }
        .mps-field-check {
            width:18px;height:18px;border-radius:50%;display:inline-flex;align-items:center;
            justify-content:center;flex-shrink:0;
        }
        .mps-field-check--done    { background:#E1F5EE;color:#0F6E56; }
        .mps-field-check--missing { background:#FCEBEB;color:#A32D2D; }
        .mps-field-label { flex:1;color:#374151; }
        .mps-field-label--missing { color:#9ca3af; }
        .mps-field-val { font-size:12px;color:#6b7280;text-align:right;max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap; }
        .mps-field-missing-tag { font-size:10px;font-weight:500;padding:2px 7px;border-radius:99px;background:#FCEBEB;color:#A32D2D;white-space:nowrap; }

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

        /* ── Suggestion Count Badge ──────────────────────────── */
        .ls-suggestion-count {
            display: inline-flex;
            align-items: center;
            font-size: 10px;
            font-weight: 500;
            padding: 2px 8px;
            border-radius: 99px;
            background: #FCEBEB;
            color: #A32D2D;
            margin-left: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* ── Suggestion Item ─────────────────────────────────── */
        .ls-suggestion-item {
            background: #fafafa;
            border: 0.5px solid #e5e7eb;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 12px;
            transition: box-shadow .2s, border-color .2s;
        }
        .ls-suggestion-item:last-child { margin-bottom: 0; }
        .ls-suggestion-item:hover {
            box-shadow: 0 2px 8px rgba(0,0,0,.06);
            border-color: #d1d5db;
        }

        /* Urgent highlight */
        .ls-suggestion-item--urgent {
            background: #FDF4F1;
            border-color: #F5C4B3;
        }

        /* ── Suggestion Header ───────────────────────────────── */
        .ls-suggestion-header {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 10px;
        }

        /* ── Priority Badges ─────────────────────────────────── */
        .ls-priority-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 10px;
            font-weight: 500;
            padding: 3px 8px;
            border-radius: 99px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .ls-priority-badge--high   { background: #FCEBEB; color: #A32D2D; }
        .ls-priority-badge--medium { background: #FAEEDA; color: #854F0B; }
        .ls-priority-badge--low    { background: #E6F1FB; color: #185FA5; }

        /* ── Time Badge ──────────────────────────────────────── */
        .ls-time-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 11px;
            color: #9ca3af;
        }

        /* ── Suggestion Message ──────────────────────────────── */
        .ls-suggestion-message {
            font-size: 14px;
            font-weight: 500;
            color: #111827;
            line-height: 1.5;
            margin-bottom: 12px;
        }

        /* ── Suggestion Actions ──────────────────────────────── */
        .ls-suggestion-actions {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
        }

        /* ── Action Buttons ──────────────────────────────────── */
        .ls-action-buttons {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .ls-action-btn {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 12px;
            font-weight: 500;
            padding: 6px 12px;
            border-radius: 7px;
            border: 0.5px solid transparent;
            text-decoration: none;
            white-space: nowrap;
            transition: background .15s, transform .15s, box-shadow .15s;
            cursor: pointer;
        }
        .ls-action-btn:hover { transform: translateY(-1px); }

        .ls-action-btn--whatsapp { background: #25D366; color: #fff; border-color: #20BA5A; }
        .ls-action-btn--whatsapp:hover { background: #20BA5A; color: #fff; box-shadow: 0 4px 12px rgba(37, 211, 102, 0.3); }

        .ls-action-btn--email { background: #185FA5; color: #fff; border-color: #0C447C; }
        .ls-action-btn--email:hover { background: #0C447C; color: #fff; box-shadow: 0 4px 12px rgba(24, 95, 165, 0.3); }

        .ls-action-btn--call { background: #374151; color: #fff; border-color: #1f2937; }
        .ls-action-btn--call:hover { background: #1f2937; color: #fff; box-shadow: 0 4px 12px rgba(55, 65, 81, 0.3); }

        /* ── Dropdown ────────────────────────────────────────── */
        .ls-action-dropdown {
            position: relative;
            display: inline-block;
        }

        .ls-dropdown-menu {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            margin-top: 4px;
            background: #fff;
            border: 0.5px solid #e5e7eb;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,.1);
            min-width: 200px;
            max-width: 280px;
            z-index: 1000;
            overflow: hidden;
        }

        .ls-action-dropdown.active .ls-dropdown-menu {
            display: block;
        }

        .ls-dropdown-item {
            display: block;
            padding: 10px 12px;
            text-decoration: none;
            border-bottom: 0.5px solid #f3f4f6;
            transition: background .15s;
        }
        .ls-dropdown-item:last-child { border-bottom: none; }
        .ls-dropdown-item:hover { background: #f9fafb; }

        .ls-dropdown-item__title {
            font-size: 13px;
            font-weight: 500;
            color: #111827;
            margin-bottom: 2px;
        }

        .ls-dropdown-item__desc {
            font-size: 11px;
            color: #6b7280;
            line-height: 1.4;
        }

        /* ── Management Actions ──────────────────────────────── */
        .ls-management-actions {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .ls-mgmt-btn {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 11px;
            font-weight: 500;
            padding: 5px 10px;
            border-radius: 6px;
            border: 0.5px solid;
            background: transparent;
            cursor: pointer;
            white-space: nowrap;
            transition: background .15s, color .15s;
        }

        .ls-mgmt-btn--complete {
            border-color: #9FE1CB;
            color: #0F6E56;
        }
        .ls-mgmt-btn--complete:hover {
            background: #E1F5EE;
            color: #085041;
        }

        .ls-mgmt-btn--dismiss {
            border-color: #d1d5db;
            color: #6b7280;
        }
        .ls-mgmt-btn--dismiss:hover {
            background: #f3f4f6;
            color: #374151;
        }

        /* ── Empty State ─────────────────────────────────────── */
        .ls-empty-state {
            text-align: center;
            padding: 2rem 1rem;
        }
        /* ── Suggestion Stats ────────────────────────────────── */
        .ls-suggestion-stats {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-left: auto;
            font-size: 11px;
            color: #6b7280;
        }

        .ls-stat-item {
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        /* ── Quick Filters ───────────────────────────────────── */
        .ls-quick-filters {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 12px;
            background: #fafafa;
            border-bottom: 0.5px solid #e5e7eb;
            flex-wrap: wrap;
        }

        .ls-filter-btn {
            font-size: 11px;
            font-weight: 500;
            padding: 5px 12px;
            border-radius: 6px;
            border: 0.5px solid #e5e7eb;
            background: #fff;
            color: #6b7280;
            cursor: pointer;
            transition: all .15s;
        }

        .ls-filter-btn:hover {
            background: #f3f4f6;
            color: #374151;
        }

        .ls-filter-btn.active {
            background: #185FA5;
            border-color: #185FA5;
            color: #fff;
        }
        /* ── Anonymous timeline dot ──────────────────────────────── */
        .ls-timeline-dot--anon {
            background: #d1d5db;
            border-color: #f3f4f6;
        }
    
        /* ── Anonymous activity chip ─────────────────────────────── */
        .ls-timeline-anon-chip {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 10px;
            font-weight: 500;
            padding: 2px 7px;
            border-radius: 99px;
            background: #f3f4f6;
            color: #6b7280;
            white-space: nowrap;
        }
    
        /* ── Marketplace history banner ──────────────────────────── */
        .ls-marketplace-history-banner {
            display: flex;
            align-items: flex-start;
            gap: 14px;
            background: #EEF5FD;
            border: 0.5px solid #B5D4F4;
            color: #185FA5;
            border-radius: 12px;
            padding: 1.1rem 1.25rem;
        }
        /* ── Hidden activity entries ─────────────────────────────── */
        .activity-entry--hidden { display: none; }
    
        /* ── Fade-in for newly revealed entries ──────────────────── */
        .activity-entry--revealed {
            animation: activityFadeIn .25s ease forwards;
        }
        @keyframes activityFadeIn {
            from { opacity: 0; transform: translateY(4px); }
            to   { opacity: 1; transform: translateY(0); }
        }
    
        /* ── Controls row ────────────────────────────────────────── */
        .activity-controls {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: .85rem 0 1.1rem;
            border-top: 0.5px solid #f3f4f6;
            margin-top: 4px;
        }
    
        /* ── Load more button ────────────────────────────────────── */
        .activity-load-more {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            font-weight: 500;
            color: #185FA5;
            background: #EEF5FD;
            border: 0.5px solid #B5D4F4;
            border-radius: 7px;
            padding: 6px 12px;
            cursor: pointer;
            transition: background .15s, transform .15s;
        }
        .activity-load-more:hover {
            background: #dbeeff;
            transform: translateY(-1px);
        }
        .activity-load-more__count {
            font-size: 10px;
            font-weight: 500;
            padding: 1px 6px;
            border-radius: 99px;
            background: #B5D4F4;
            color: #185FA5;
        }
    
        /* ── Collapse button ─────────────────────────────────────── */
        .activity-collapse {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            font-weight: 500;
            color: #6b7280;
            background: transparent;
            border: 0.5px solid #d1d5db;
            border-radius: 7px;
            padding: 6px 12px;
            cursor: pointer;
            transition: background .15s;
        }
        .activity-collapse:hover { background: #f3f4f6; }

        /* ── Marketplace origin icon (index) ─────────────────────── */
        .mp-origin-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 18px;
            height: 18px;
            border-radius: 5px;
            background: #E6F1FB;
            color: #185FA5;
            flex-shrink: 0;
            cursor: default;
            position: relative;
        }
    
        /* ── Tooltip ─────────────────────────────────────────────── */
        .mp-origin-icon::after {
            content: attr(data-tooltip);
            position: absolute;
            bottom: calc(100% + 6px);
            left: 50%;
            transform: translateX(-50%);
            background: #111827;
            color: #fff;
            font-size: 11px;
            font-weight: 400;
            line-height: 1.5;
            padding: 5px 9px;
            border-radius: 6px;
            white-space: nowrap;
            pointer-events: none;
            opacity: 0;
            transition: opacity .15s, transform .15s;
            transform: translateX(-50%) translateY(3px);
            z-index: 100;
        }
    
        /* Tooltip arrow */
        .mp-origin-icon::before {
            content: '';
            position: absolute;
            bottom: calc(100% + 1px);
            left: 50%;
            transform: translateX(-50%);
            border: 5px solid transparent;
            border-top-color: #111827;
            pointer-events: none;
            opacity: 0;
            transition: opacity .15s;
            z-index: 100;
        }
    
        .mp-origin-icon:hover::after,
        .mp-origin-icon:hover::before {
            opacity: 1;
            transform: translateX(-50%) translateY(0);
        }

        /* ── Demo prep affiliate card ────────────────────────── */
        .dp-affiliate-section {
            border: 0.5px solid #e5e7eb;
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 8px;
        }
        .dp-affiliate-section:last-child { margin-bottom: 0; }

        .dp-affiliate-section__head {
            display: flex;
            align-items: center;
            gap: 10px;
            width: 100%;
            padding: .7rem .9rem;
            background: #fafafa;
            border: none;
            font-size: 13px;
            font-weight: 500;
            color: #374151;
            cursor: pointer;
            text-align: left;
            transition: background .15s;
        }
        .dp-affiliate-section__head:hover { background: #f3f4f6; }

        .dp-affiliate-section__num {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: #E6F1FB;
            color: #185FA5;
            font-size: 11px;
            font-weight: 500;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .dp-affiliate-section__body {
            padding: .85rem .9rem;
            font-size: 13px;
            color: #374151;
            line-height: 1.7;
            white-space: pre-wrap;
            border-top: 0.5px solid #e5e7eb;
            background: #fff;
        }
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
        function toggleDropdown(button) {
            // Close all other dropdowns
            document.querySelectorAll('.ls-action-dropdown.active').forEach(dropdown => {
                if (!dropdown.contains(button)) {
                    dropdown.classList.remove('active');
                }
            });
            
            // Toggle this dropdown
            button.closest('.ls-action-dropdown').classList.toggle('active');
        }

        // Close dropdowns when clicking outside
        document.addEventListener('click', function(event) {
            if (!event.target.closest('.ls-action-dropdown')) {
                document.querySelectorAll('.ls-action-dropdown.active').forEach(dropdown => {
                    dropdown.classList.remove('active');
                });
            }
        });

        function filterSuggestions(event, type) {
            const items   = document.querySelectorAll('.ls-suggestion-item');
            const buttons = document.querySelectorAll('.ls-filter-btn');

            buttons.forEach(btn => btn.classList.remove('active'));
            event.currentTarget.classList.add('active');

            items.forEach(item => {
                if (type === 'all') {
                    item.style.display = 'block';
                } else if (type === 'high') {
                    item.style.display = item.dataset.priority === 'high' ? 'block' : 'none';
                } else {
                    item.style.display = item.dataset.actionType === type ? 'block' : 'none';
                }
            });
        }

        const BATCH = 5; // how many to reveal per click
        let visibleCount = 5;
    
        function loadMoreActivities() {
            const all      = document.querySelectorAll('.activity-entry');
            const total    = all.length;
            const nextEnd  = Math.min(visibleCount + BATCH, total);
    
            // Reveal the next batch
            for (let i = visibleCount; i < nextEnd; i++) {
                all[i].style.display = 'flex';
                all[i].classList.remove('activity-entry--hidden');
                all[i].classList.add('activity-entry--revealed');
            }
    
            visibleCount = nextEnd;
    
            const remaining = total - visibleCount;
    
            if (remaining <= 0) {
                // All shown — hide load-more, show collapse
                document.getElementById('activityLoadMore').style.display = 'none';
                document.getElementById('activityCollapse').style.display  = 'inline-flex';
            } else {
                // Update the button label and remaining count
                const nextBatch = Math.min(BATCH, remaining);
                document.getElementById('activityLoadMore').childNodes[2].textContent =
                    ' Show ' + nextBatch + ' more ';
                document.getElementById('remainingCount').textContent = remaining + ' remaining';
            }
        }
    
        function collapseActivities() {
            const all = document.querySelectorAll('.activity-entry');
    
            // Hide everything beyond the first 5
            for (let i = 5; i < all.length; i++) {
                all[i].style.display = 'none';
                all[i].classList.add('activity-entry--hidden');
                all[i].classList.remove('activity-entry--revealed');
            }
    
            visibleCount = 5;
            const remaining = all.length - 5;
    
            // Restore load-more button
            const loadMore = document.getElementById('activityLoadMore');
            loadMore.style.display = 'inline-flex';
            loadMore.childNodes[2].textContent = ' Show ' + Math.min(BATCH, remaining) + ' more ';
            document.getElementById('remainingCount').textContent = remaining + ' remaining';
    
            // Hide collapse button
            document.getElementById('activityCollapse').style.display = 'none';
    
            // Scroll back up to the timeline card so they can see the top
            document.querySelector('.ls-card__head svg[viewBox="0 0 16 16"] + svg, .ls-card__head')
                .closest('.ls-card')
                ?.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }

        function toggleDemoPrep() {
            const body    = document.getElementById('demoPrepBody');
            const chevron = document.getElementById('demoPrepChevron');
            const isOpen  = body.style.display !== 'none';
            body.style.display = isOpen ? 'none' : 'block';
            chevron.style.transform = isOpen ? '' : 'rotate(180deg)';
        }

        function toggleDemoSection(i) {
            const body    = document.getElementById('demoSectionBody' + i);
            const chevron = document.getElementById('demoSectionChevron' + i);
            const isOpen  = body.style.display !== 'none';
            body.style.display = isOpen ? 'none' : 'block';
            chevron.style.transform = isOpen ? '' : 'rotate(180deg)';
        }
    </script>

@endsection