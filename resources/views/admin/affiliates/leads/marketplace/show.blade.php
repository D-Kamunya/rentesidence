@extends('admin.layouts.app')

@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="page-content-wrapper bg-white p-30 radius-20">

                    {{-- Page Title --}}
                    @php
                        $pageTitle = 'Marketplace Detail';
                    @endphp
                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box d-sm-flex align-items-center justify-content-between border-bottom mb-4 pb-2">
                                <div class="page-title-left">
                                    <h3 class="mb-sm-0">Marketplace Lead</h3>
                                </div>
                                <div class="page-title-right">
                                    <ol class="breadcrumb mb-0">
                                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                                        <li class="breadcrumb-item"><a href="{{ route('admin.marketplace.index') }}">Marketplace</a></li>
                                        <li class="breadcrumb-item active">{{ $lead->company->company_name }}</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="container">

                        {{-- Back link --}}
                        <a href="{{ route('admin.marketplace.index') }}" class="mod-back-link mb-4 d-inline-flex align-items-center gap-2">
                            <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
                                <path d="M10 3L5 8l5 5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            Back to Marketplace
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

                        @php
                            $temp  = strtolower($lead->temperature);
                            $isClaimed   = $lead->marketplace_status === 'claimed';
                            $isAvailable = $lead->marketplace_status === 'marketplace';

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
                        <div class="mps-hero mb-4">
                            <div class="mps-hero__left">
                                <div class="mps-avatar">
                                    {{ strtoupper(substr($lead->company->company_name, 0, 2)) }}
                                </div>
                                <div>
                                    <h4 class="mps-company-name">{{ $lead->company->company_name }}</h4>
                                    <p class="mps-company-meta">
                                        {{ $lead->company->city }}{{ $lead->company->city && $lead->company->country ? ', ' : '' }}{{ $lead->company->country }}
                                        @if($lead->company->estimated_units)
                                            &middot; {{ $lead->company->estimated_units }} units
                                        @endif
                                    </p>

                                    <div class="d-flex align-items-center gap-2 mt-2 flex-wrap">

                                        {{-- Marketplace status --}}
                                        @if($isAvailable)
                                            <span class="mps-badge mps-badge--available">
                                                <span class="mps-pulse-dot"></span>
                                                Available
                                            </span>
                                        @elseif($isClaimed)
                                            <span class="mps-badge mps-badge--claimed">
                                                <svg width="9" height="9" viewBox="0 0 16 16" fill="none">
                                                    <path d="M3 8.5l3.5 3.5 6.5-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                                Claimed
                                            </span>
                                        @endif

                                        {{-- Temperature --}}
                                        <span class="mps-temp-badge mps-temp-badge--{{ $temp }}">
                                            @if($temp === 'hot') 🔥
                                            @elseif($temp === 'warm') 🌡
                                            @else ❄ @endif
                                            {{ ucfirst($lead->temperature) }}
                                        </span>

                                        {{-- Cycle count --}}
                                        @if(($lead->marketplace_cycles ?? 0) > 0)
                                            <span class="mps-badge" style="background:#FAEEDA;color:#854F0B;border-color:#FAC775;">
                                                ×{{ $lead->marketplace_cycles }} {{ Str::plural('cycle', $lead->marketplace_cycles) }}
                                            </span>
                                        @else
                                            <span class="mps-badge" style="background:#E1F5EE;color:#0F6E56;border-color:#9FE1CB;">
                                                New listing
                                            </span>
                                        @endif

                                        {{-- Completeness chip --}}
                                        <span style="font-size:11px;font-weight:500;padding:3px 10px;border-radius:99px;background:{{ $scoreBg }};color:{{ $scoreColor }};">
                                            {{ $score }}% complete
                                        </span>
                                    </div>
                                </div>
                            </div>

                            {{-- Hero right: admin actions --}}
                            <div class="d-flex align-items-start gap-2 flex-wrap">
                                @if($isAvailable)
                                    <form method="POST" action="{{ route('admin.marketplace.destroy', $lead->id) }}"
                                          onsubmit="return confirm('Remove this lead from the marketplace?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="mps-btn mps-btn--red">
                                            <svg width="13" height="13" viewBox="0 0 16 16" fill="none">
                                                <path d="M4 4l8 8M12 4l-8 8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                            </svg>
                                            Pull from Marketplace
                                        </button>
                                    </form>
                                @endif
                                <a href="{{ route('admin.marketplace.create') }}" class="mps-btn mps-btn--blue">
                                    <svg width="13" height="13" viewBox="0 0 16 16" fill="none">
                                        <path d="M8 3v10M3 8h10" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                    </svg>
                                    Add Another Lead
                                </a>
                            </div>
                        </div>
                        {{-- END HERO --}}

                        {{-- ── Claimed banner ───────────────────────────── --}}
                        @if($isClaimed && $lead->affiliate)
                            <div class="mps-claimed-banner mb-4">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" style="flex-shrink:0;">
                                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.5"/>
                                    <path d="M7 12.5l3.5 3.5 6.5-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                <div>
                                    <p style="font-weight:500;font-size:14px;margin:0 0 3px;">
                                        Claimed by {{ $lead->affiliate->first_name }} {{ $lead->affiliate->last_name }}
                                    </p>
                                    <p style="font-size:12px;margin:0;opacity:.85;">
                                        Claimed {{ optional($lead->claimed_at)->diffForHumans() }}
                                        &middot; Ownership expires {{ optional($lead->ownership_expires_at)->format('d M Y') }}
                                        &middot; {{ optional($lead->ownership_expires_at)->isPast() ? 'Expired' : optional(now()->diff($lead->ownership_expires_at))->days . ' days remaining' }}
                                    </p>
                                </div>
                            </div>
                        @endif

                        <div class="row g-4">

                            {{-- ═══════════════════════════════════════════ --}}
                            {{-- LEFT COLUMN                                 --}}
                            {{-- ═══════════════════════════════════════════ --}}
                            <div class="col-md-6">

                                {{-- ── Company Details ─────────────────── --}}
                                <div class="mps-card mb-4">
                                    <div class="mps-card__head">
                                        <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
                                            <rect x="2" y="2" width="12" height="12" rx="2" stroke="currentColor" stroke-width="1.5"/>
                                            <path d="M5 8h6M5 5h6M5 11h4" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>
                                        </svg>
                                        Company Details
                                    </div>
                                    <div class="mps-card__body">
                                        <div class="mps-detail-row">
                                            <span class="mps-detail-label">Company Name</span>
                                            <span class="mps-detail-val">{{ $lead->company->company_name }}</span>
                                        </div>
                                        <div class="mps-detail-row">
                                            <span class="mps-detail-label">Country</span>
                                            <span class="mps-detail-val">{{ $lead->company->country ?: '—' }}</span>
                                        </div>
                                        <div class="mps-detail-row">
                                            <span class="mps-detail-label">City</span>
                                            <span class="mps-detail-val">{{ $lead->company->city ?: '—' }}</span>
                                        </div>
                                        <div class="mps-detail-row">
                                            <span class="mps-detail-label">Phone</span>
                                            <span class="mps-detail-val">{{ $lead->company->phone ?: '—' }}</span>
                                        </div>
                                        <div class="mps-detail-row">
                                            <span class="mps-detail-label">Email</span>
                                            <span class="mps-detail-val">
                                                @if($lead->company->email)
                                                    <a href="mailto:{{ $lead->company->email }}" style="color:#185FA5;">{{ $lead->company->email }}</a>
                                                @else —
                                                @endif
                                            </span>
                                        </div>
                                        <div class="mps-detail-row">
                                            <span class="mps-detail-label">Website</span>
                                            <span class="mps-detail-val">
                                                @if($lead->company->website)
                                                    <a href="{{ $lead->company->website }}" target="_blank" style="color:#185FA5;font-size:13px;">{{ $lead->company->website }}</a>
                                                @else —
                                                @endif
                                            </span>
                                        </div>
                                        <div class="mps-detail-row">
                                            <span class="mps-detail-label">Property Type</span>
                                            <span class="mps-detail-val">{{ $lead->company->property_type ? ucfirst(str_replace('_', ' ', $lead->company->property_type)) : '—' }}</span>
                                        </div>
                                        <div class="mps-detail-row">
                                            <span class="mps-detail-label">Est. Units</span>
                                            <span class="mps-detail-val">{{ $lead->company->estimated_units ?: '—' }}</span>
                                        </div>
                                        <div class="mps-detail-row" style="border-bottom:none;">
                                            <span class="mps-detail-label">Contact</span>
                                            <span class="mps-detail-val">
                                                {{ $lead->contact_person_name ?: '—' }}
                                                @if($lead->contact_person_role)
                                                    <span style="color:#9ca3af;font-weight:400;"> · {{ ucfirst(str_replace('_', ' ', $lead->contact_person_role)) }}</span>
                                                @endif
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                {{-- ── Completeness Breakdown ───────────── --}}
                                <div class="mps-card mb-4">
                                    <div class="mps-card__head">
                                        <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
                                            <circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.5"/>
                                            <path d="M8 5v3.5l2 1.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                        Profile Completeness
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
                                                Missing fields reduce claim rate. Consider editing this lead to fill them in.
                                            </p>
                                        @endif
                                    </div>
                                </div>

                                {{-- ── Marketplace Metadata ─────────────── --}}
                                <div class="mps-card">
                                    <div class="mps-card__head">
                                        <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
                                            <path d="M3 3h10a1 1 0 0 1 1 1v6a1 1 0 0 1-1 1H9l-3 2v-2H3a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1z" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                        Marketplace Info
                                    </div>
                                    <div class="mps-card__body">
                                        <div class="mps-detail-row">
                                            <span class="mps-detail-label">Source</span>
                                            <span class="mps-detail-val">
                                                <span style="font-size:11px;font-weight:500;padding:2px 8px;border-radius:99px;background:#E6F1FB;color:#185FA5;">
                                                    Admin
                                                </span>
                                            </span>
                                        </div>
                                        <div class="mps-detail-row">
                                            <span class="mps-detail-label">Listed</span>
                                            <span class="mps-detail-val">
                                                {{ optional($lead->marketplace_at)->format('d M Y, H:i') ?? '—' }}
                                            </span>
                                        </div>
                                        <div class="mps-detail-row">
                                            <span class="mps-detail-label">Cycles</span>
                                            <span class="mps-detail-val">
                                                @if(($lead->marketplace_cycles ?? 0) === 0)
                                                    <span style="color:#0F6E56;">First listing</span>
                                                @else
                                                    {{ $lead->marketplace_cycles }} {{ Str::plural('time', $lead->marketplace_cycles) }} through marketplace
                                                @endif
                                            </span>
                                        </div>
                                        @if($isClaimed)
                                            <div class="mps-detail-row">
                                                <span class="mps-detail-label">Claimed</span>
                                                <span class="mps-detail-val">{{ optional($lead->claimed_at)->format('d M Y, H:i') ?? '—' }}</span>
                                            </div>
                                            <div class="mps-detail-row">
                                                <span class="mps-detail-label">Expires</span>
                                                <span class="mps-detail-val">
                                                    @if($lead->ownership_expires_at)
                                                        @php
                                                            $exp  = $lead->ownership_expires_at;
                                                            $diff = now()->diff($exp);
                                                        @endphp
                                                        @if($exp->isPast())
                                                            <span style="color:#A32D2D;">Expired</span>
                                                        @elseif($diff->days <= 7)
                                                            <span style="color:#854F0B;">{{ $diff->days }} days left</span>
                                                        @else
                                                            <span style="color:#0F6E56;">{{ $diff->days }} days left</span>
                                                        @endif
                                                    @else —
                                                    @endif
                                                </span>
                                            </div>
                                            <div class="mps-detail-row" style="border-bottom:none;">
                                                <span class="mps-detail-label">Claimed By</span>
                                                <span class="mps-detail-val">
                                                    {{ $lead->affiliate->first_name ?? '—' }} {{ $lead->affiliate->last_name ?? '' }}
                                                </span>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                            </div>
                            {{-- END LEFT COLUMN --}}

                            {{-- ═══════════════════════════════════════════ --}}
                            {{-- RIGHT COLUMN                                --}}
                            {{-- ═══════════════════════════════════════════ --}}
                            <div class="col-md-6">

                                {{-- ── Admin Notes ──────────────────────── --}}
                                @if($lead->notes)
                                    <div class="mps-card mb-4">
                                        <div class="mps-card__head">
                                            <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
                                                <path d="M2 2h12v10H9l-3 2v-2H2V2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                            Admin Notes
                                            <span style="margin-left:8px;font-size:10px;font-weight:500;padding:2px 7px;border-radius:99px;background:#FAEEDA;color:#854F0B;">Internal only</span>
                                        </div>
                                        <div class="mps-card__body">
                                            <div class="mps-notes-block">{{ $lead->notes }}</div>
                                        </div>
                                    </div>
                                @endif

                                {{-- ── Activity Timeline ────────────────── --}}
                                <div class="mps-card">
                                    <div class="mps-card__head">
                                        <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
                                            <circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.5"/>
                                            <path d="M8 5v3.5l2 1.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                        Activity Timeline
                                        <span style="margin-left:auto;font-size:11px;color:#9ca3af;">{{ $lead->activities->count() }} {{ Str::plural('event', $lead->activities->count()) }}</span>
                                    </div>
                                    <div class="mps-card__body">
                                        @forelse($lead->activities as $index => $activity)
                                            <div class="mps-timeline-item activity-entry {{ $index >= 5 ? 'activity-entry--hidden' : '' }}"
                                                style="{{ $index >= 5 ? 'display:none;' : '' }}">
                                                <div class="mps-timeline-dot {{ is_null($activity->user_id) ? 'mps-timeline-dot--anon' : '' }}"></div>
                                                <div class="mps-timeline-content">
                                                    <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                                                        <span class="mps-timeline-time">{{ $activity->created_at->diffForHumans() }}</span>

                                                        {{-- Actor attribution — admin sees real names --}}
                                                        @if(!is_null($activity->user_id) && $activity->user)
                                                            <span class="mps-timeline-actor">
                                                                <svg width="9" height="9" viewBox="0 0 16 16" fill="none">
                                                                    <circle cx="8" cy="6" r="3" stroke="currentColor" stroke-width="1.5"/>
                                                                    <path d="M2 14c0-3 2.7-5 6-5s6 2 6 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                                                </svg>
                                                                {{ $activity->user->first_name }} {{ $activity->user->last_name }}
                                                            </span>
                                                        @elseif(is_null($activity->user_id))
                                                            {{--
                                                                Admin sees "Previous affiliate" for anonymized entries.
                                                                Unlike the affiliate view, admin could theoretically see
                                                                more — but we keep it consistent and privacy-respecting.
                                                            --}}
                                                            <span class="mps-timeline-actor mps-timeline-actor--anon">
                                                                <svg width="9" height="9" viewBox="0 0 16 16" fill="none">
                                                                    <circle cx="8" cy="6" r="3" stroke="currentColor" stroke-width="1.5"/>
                                                                    <path d="M2 14c0-3 2.7-5 6-5s6 2 6 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                                                </svg>
                                                                Previous affiliate
                                                            </span>
                                                        @endif

                                                        {{-- Activity type chip --}}
                                                        <span class="mps-activity-type">
                                                            {{ ucfirst(str_replace('_', ' ', $activity->type)) }}
                                                        </span>
                                                    </div>
                                                    <p class="mps-timeline-desc">{{ $activity->description }}</p>
                                                </div>
                                            </div>
                                        @empty
                                            <p style="font-size:13px;color:#9ca3af;margin:0;">No activity recorded yet.</p>
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
                            {{-- END RIGHT COLUMN --}}

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

        /* ── Alerts ──────────────────────────────────────────── */
        .mod-alert { display:flex;align-items:center;gap:10px;padding:.85rem 1.1rem;border-radius:10px;font-size:14px; }
        .mod-alert--success { background:#E1F5EE;color:#0F6E56; }
        .mod-alert--danger  { background:#FCEBEB;color:#A32D2D; }

        /* ── Hero card ───────────────────────────────────────── */
        .mps-hero {
            background:#fff;border:0.5px solid #e5e7eb;border-radius:14px;
            padding:1.25rem 1.5rem;display:flex;align-items:flex-start;
            justify-content:space-between;flex-wrap:wrap;gap:1rem;
        }
        .mps-hero__left { display:flex;align-items:flex-start;gap:14px; }
        .mps-avatar {
            width:48px;height:48px;border-radius:12px;background:#E6F1FB;color:#185FA5;
            font-size:15px;font-weight:500;display:inline-flex;align-items:center;
            justify-content:center;flex-shrink:0;
        }
        .mps-company-name { font-size:17px;font-weight:500;color:#111827;margin:0 0 2px; }
        .mps-company-meta { font-size:13px;color:#9ca3af;margin:0; }

        /* ── Badges ──────────────────────────────────────────── */
        .mps-badge { display:inline-flex;align-items:center;gap:5px;font-size:11px;font-weight:500;padding:3px 10px;border-radius:99px;border:0.5px solid transparent;white-space:nowrap; }
        .mps-badge--available { background:#E1F5EE;color:#0F6E56;border-color:#9FE1CB; }
        .mps-badge--claimed   { background:#E6F1FB;color:#185FA5;border-color:#B5D4F4; }

        /* ── Pulsing dot ─────────────────────────────────────── */
        .mps-pulse-dot {
            width:7px;height:7px;border-radius:50%;background:#0F6E56;
            display:inline-block;flex-shrink:0;
            animation:mps-pulse 1.8s ease-in-out infinite;
        }
        @keyframes mps-pulse {
            0%,100% { opacity:1;transform:scale(1); }
            50%      { opacity:.45;transform:scale(1.5); }
        }

        /* ── Temperature badges ──────────────────────────────── */
        .mps-temp-badge { display:inline-flex;align-items:center;gap:4px;font-size:11px;font-weight:500;padding:3px 10px;border-radius:99px; }
        .mps-temp-badge--hot  { background:#FAECE7;color:#993C1D; }
        .mps-temp-badge--warm { background:#FAEEDA;color:#854F0B; }
        .mps-temp-badge--cold { background:#E6F1FB;color:#185FA5; }

        /* ── Hero buttons ────────────────────────────────────── */
        .mps-btn { display:inline-flex;align-items:center;gap:6px;padding:8px 16px;font-size:13px;font-weight:500;border-radius:8px;border:none;cursor:pointer;text-decoration:none;white-space:nowrap;transition:opacity .15s,transform .15s; }
        .mps-btn:hover { opacity:.88;transform:translateY(-1px); }
        .mps-btn--blue { background:#185FA5;color:#fff; }
        .mps-btn--red  { background:#FCEBEB;color:#A32D2D;border:0.5px solid #F7C1C1; }

        /* ── Claimed banner ──────────────────────────────────── */
        .mps-claimed-banner {
            display:flex;align-items:flex-start;gap:14px;
            background:#E1F5EE;border:0.5px solid #9FE1CB;color:#0F6E56;
            border-radius:12px;padding:1.1rem 1.25rem;
        }

        /* ── Section cards ───────────────────────────────────── */
        .mps-card { background:#fff;border:0.5px solid #e5e7eb;border-radius:12px;overflow:hidden; }
        .mps-card__head { display:flex;align-items:center;gap:8px;padding:.8rem 1.1rem;border-bottom:0.5px solid #e5e7eb;background:#fafafa;font-size:13px;font-weight:500;color:#374151; }
        .mps-card__body { padding:1.1rem; }

        /* ── Detail rows ─────────────────────────────────────── */
        .mps-detail-row { display:flex;justify-content:space-between;align-items:center;padding:.5rem 0;border-bottom:0.5px solid #f3f4f6;font-size:13px; }
        .mps-detail-label { color:#9ca3af;font-weight:500;flex-shrink:0;margin-right:16px; }
        .mps-detail-val { color:#111827;font-weight:500;text-align:right; }

        /* ── Completeness overall bar ────────────────────────── */
        .mps-overall-bar-wrap { height:8px;background:#e5e7eb;border-radius:99px;overflow:hidden; }
        .mps-overall-bar { height:100%;border-radius:99px;transition:width .4s cubic-bezier(.4,0,.2,1); }

        /* ── Field checklist ─────────────────────────────────── */
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

        /* ── Notes block ─────────────────────────────────────── */
        .mps-notes-block { background:#fafafa;border:0.5px solid #e5e7eb;border-radius:8px;padding:.85rem 1rem;font-size:13px;color:#374151;line-height:1.65;white-space:pre-wrap; }

        /* ── Activity timeline ───────────────────────────────── */
        .mps-timeline-item { display:flex;gap:12px;padding-bottom:1rem;position:relative; }
        .mps-timeline-item:last-child { padding-bottom:0; }
        .mps-timeline-item:not(:last-child)::before { content:'';position:absolute;left:5px;top:14px;bottom:0;width:1px;background:#e5e7eb; }
        .mps-timeline-dot { width:11px;height:11px;border-radius:50%;background:#185FA5;border:2px solid #E6F1FB;flex-shrink:0;margin-top:3px; }
        .mps-timeline-dot--anon { background:#d1d5db;border-color:#f3f4f6; }
        .mps-timeline-time { font-size:11px;color:#9ca3af;font-weight:500; }
        .mps-timeline-desc { font-size:13px;color:#374151;margin:0;line-height:1.5; }
        .mps-timeline-content { flex:1; }

        /* ── Actor chip ──────────────────────────────────────── */
        .mps-timeline-actor {
            display:inline-flex;align-items:center;gap:4px;
            font-size:10px;font-weight:500;padding:2px 7px;border-radius:99px;
            background:#E6F1FB;color:#185FA5;white-space:nowrap;
        }
        .mps-timeline-actor--anon { background:#f3f4f6;color:#6b7280; }

        /* ── Activity type chip ──────────────────────────────── */
        .mps-activity-type {
            font-size:10px;font-weight:500;padding:2px 7px;border-radius:99px;
            background:#fafafa;border:0.5px solid #e5e7eb;color:#9ca3af;white-space:nowrap;
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
    </style>
    <script>
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
    </script>
@endsection