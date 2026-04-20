@extends('admin.layouts.app')

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="page-content-wrapper bg-white p-30 radius-20">

                {{-- Page Title --}}
                @php
                    $pageTitle = 'Suggestion Details';
                @endphp
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between border-bottom mb-4 pb-2">
                            <div class="page-title-left">
                                <h3 class="mb-sm-0">Lead Suggestions</h3>
                            </div>
                            <div class="page-title-right">
                                <ol class="breadcrumb mb-0">
                                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                                    <li class="breadcrumb-item"><a href="{{ route('admin.suggestions.index') }}">Suggestions</a></li>
                                    <li class="breadcrumb-item active">{{ $lead->company->company_name }}</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <a href="{{ route('admin.suggestions.index') }}" class="at-back-link mb-4 d-inline-flex align-items-center gap-2">
                    <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
                        <path d="M10 3L5 8l5 5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Back to All Suggestions
                </a>

                {{-- ═══════════════════════════════════════════════ --}}
                {{-- HERO CARD                                       --}}
                {{-- ═══════════════════════════════════════════════ --}}
                <div class="sg-hero mb-4">
                    <div class="sg-hero__left">
                        <div class="sg-company-avatar">
                            {{ strtoupper(substr($lead->company->company_name, 0, 2)) }}
                        </div>
                        <div>
                            <h4 class="sg-company-name">{{ $lead->company->company_name }}</h4>
                            <p class="sg-company-meta">
                                {{ $lead->company->city }}{{ $lead->company->city && $lead->company->country ? ', ' : '' }}{{ $lead->company->country }}
                                @if($lead->company->estimated_units)
                                    &middot; {{ $lead->company->estimated_units }} units
                                @endif
                            </p>
                            <div class="d-flex align-items-center gap-2 mt-2 flex-wrap">
                                <span class="sg-lead-status sg-lead-status--{{ $lead->status }}">
                                    {{ ucfirst(str_replace('_', ' ', $lead->status)) }}
                                </span>
                                <span class="sg-temp-badge sg-temp-badge--{{ $lead->temperature }}">
                                    {{ ucfirst($lead->temperature) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Affiliate info --}}
                    @if($lead->affiliate)
                        <div class="sg-affiliate-chip">
                            <div class="sg-avatar sg-avatar--sm">
                                {{ strtoupper(substr($lead->affiliate->first_name, 0, 1)) }}{{ strtoupper(substr($lead->affiliate->last_name ?? '', 0, 1)) }}
                            </div>
                            <div>
                                <div style="font-size:11px;color:#9ca3af;font-weight:500;text-transform:uppercase;letter-spacing:.05em;">Affiliate</div>
                                <div style="font-size:13px;font-weight:500;color:#111827;">{{ $lead->affiliate->first_name }} {{ $lead->affiliate->last_name }}</div>
                                <div style="font-size:12px;color:#9ca3af;">{{ $lead->affiliate->email }}</div>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Summary strip --}}
                @php
                    $totalSugg    = $suggestions->count();
                    $completedSugg = $suggestions->where('status', 'completed')->count();
                    $pendingSugg  = $suggestions->where('status', 'pending')->count();
                    $completionRate = $totalSugg > 0 ? round(($completedSugg / $totalSugg) * 100) : 0;
                @endphp
                <div class="sg-summary-strip mb-4">
                    <div class="sg-summary-item">
                        <div class="sg-summary-val">{{ $totalSugg }}</div>
                        <div class="sg-summary-label">Total Suggestions</div>
                    </div>
                    <div class="sg-summary-divider"></div>
                    <div class="sg-summary-item">
                        <div class="sg-summary-val" style="color:#0F6E56;">{{ $completedSugg }}</div>
                        <div class="sg-summary-label">Completed</div>
                    </div>
                    <div class="sg-summary-divider"></div>
                    <div class="sg-summary-item">
                        <div class="sg-summary-val" style="color:#854F0B;">{{ $pendingSugg }}</div>
                        <div class="sg-summary-label">Pending</div>
                    </div>
                    <div class="sg-summary-divider"></div>
                    <div class="sg-summary-item">
                        <div class="sg-summary-val" style="color:{{ $completionRate >= 70 ? '#0F6E56' : ($completionRate >= 40 ? '#854F0B' : '#A32D2D') }};">{{ $completionRate }}%</div>
                        <div class="sg-summary-label">Completion Rate</div>
                    </div>
                </div>

                <div class="row g-4">

                    {{-- ═══════════════════════════════════════════ --}}
                    {{-- LEFT — Suggestions                          --}}
                    {{-- ═══════════════════════════════════════════ --}}
                    <div class="col-lg-7">
                        <div class="sg-card mb-4">
                            <div class="sg-card__head">
                                <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
                                    <path d="M8 2l2 4h4l-3 3 1 4-4-2-4 2 1-4-3-3h4z" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                Suggestions
                                @if($pendingSugg > 0)
                                    <span class="sg-head-count">{{ $pendingSugg }} pending</span>
                                @endif
                            </div>
                            <div class="sg-card__body">
                                @forelse($suggestions as $s)
                                    <div class="sg-suggestion-block {{ $s->priority === 'high' && $s->status === 'pending' ? 'sg-suggestion-block--urgent' : '' }}">

                                        {{-- Header row --}}
                                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                                            <div class="d-flex align-items-center gap-2">

                                                {{-- Priority --}}
                                                @if($s->priority === 'high')
                                                    <span class="sg-priority sg-priority--high">
                                                        <svg width="9" height="9" viewBox="0 0 16 16" fill="none">
                                                            <path d="M8 2l6 12H2L8 2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                            <path d="M8 7v3M8 12v.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                                        </svg>
                                                        Urgent
                                                    </span>
                                                @elseif($s->priority === 'medium')
                                                    <span class="sg-priority sg-priority--medium">Medium</span>
                                                @else
                                                    <span class="sg-priority sg-priority--low">Low</span>
                                                @endif

                                                {{-- Type --}}
                                                @if($s->action_type === 'whatsapp')
                                                    <span class="at-badge at-badge--whatsapp" style="font-size:10px;">
                                                        <svg width="9" height="9" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L0 24l6.304-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                                                        WhatsApp
                                                    </span>
                                                @elseif($s->action_type === 'email')
                                                    <span class="at-badge at-badge--email" style="font-size:10px;">
                                                        <svg width="9" height="9" viewBox="0 0 16 16" fill="none"><rect x="2" y="4" width="12" height="9" rx="1.5" stroke="currentColor" stroke-width="1.5"/><path d="M2 5l6 4 6-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                                        Email
                                                    </span>
                                                @elseif($s->action_type === 'call')
                                                    <span class="at-badge at-badge--call" style="font-size:10px;">
                                                        <svg width="9" height="9" viewBox="0 0 16 16" fill="none"><path d="M3.5 1h3l1.5 3.5-2 1.5a9 9 0 0 0 4 4l1.5-2L15 9.5v3a2 2 0 0 1-2 2A12 12 0 0 1 1 3a2 2 0 0 1 2-2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                                        Call
                                                    </span>
                                                @endif

                                                {{-- Category --}}
                                                <span style="font-size:11px;color:#9ca3af;">{{ ucfirst(str_replace('_', ' ', $s->category)) }}</span>
                                            </div>

                                            {{-- Status chip --}}
                                            @if($s->status === 'completed')
                                                <span class="at-badge at-badge--active" style="font-size:10px;">
                                                    <svg width="9" height="9" viewBox="0 0 16 16" fill="none"><circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.5"/><path d="M4.5 8.5l2.5 2.5 4.5-5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                                    Completed
                                                </span>
                                            @elseif($s->status === 'dismissed')
                                                <span class="at-badge" style="background:#f3f4f6;color:#5F5E5A;font-size:10px;">Dismissed</span>
                                            @elseif($s->status === 'expired')
                                                <span class="at-badge" style="background:#FCEBEB;color:#A32D2D;font-size:10px;">Expired</span>
                                            @else
                                                <span class="at-badge" style="background:#FAEEDA;color:#854F0B;font-size:10px;">
                                                    <span style="width:5px;height:5px;border-radius:50%;background:currentColor;display:inline-block;"></span>
                                                    Pending
                                                </span>
                                            @endif
                                        </div>

                                        {{-- Message --}}
                                        <p class="sg-suggestion-msg">{{ $s->message }}</p>

                                        {{-- Meta row --}}
                                        <div class="sg-suggestion-meta">
                                            <span>
                                                <svg width="10" height="10" viewBox="0 0 16 16" fill="none">
                                                    <circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.5"/>
                                                    <path d="M8 5v3.5l2 1.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                                Suggested {{ $s->created_at->diffForHumans() }}
                                            </span>
                                            @if($s->expires_at)
                                                @php $exp = \Carbon\Carbon::parse($s->expires_at); @endphp
                                                <span style="color:{{ $exp->isPast() ? '#A32D2D' : '#9ca3af' }};">
                                                    {{ $exp->isPast() ? 'Expired ' : 'Expires ' }}{{ $exp->diffForHumans() }}
                                                </span>
                                            @endif
                                        </div>

                                        {{-- Execution info --}}
                                        @if($s->executed_at)
                                            <div class="sg-executed-info">
                                                <svg width="12" height="12" viewBox="0 0 16 16" fill="none" style="flex-shrink:0;">
                                                    <circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.5"/>
                                                    <path d="M4.5 8.5l2.5 2.5 4.5-5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                                <div>
                                                    <span>Completed {{ $s->executed_at->diffForHumans() }}</span>
                                                    @if($s->executor)
                                                        <span style="color:#9ca3af;"> · by {{ $s->executor->first_name }} {{ $s->executor->last_name }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                        @endif

                                        {{-- Linked activities --}}
                                        @if($s->activities->count())
                                            <div class="sg-activities">
                                                <p class="sg-activities__label">Linked Activities</p>
                                                @foreach($s->activities as $act)
                                                    <div class="sg-activity-item">
                                                        <div class="sg-activity-dot"></div>
                                                        <div class="sg-activity-content">
                                                            <div class="d-flex align-items-center gap-2 mb-1">
                                                                <span class="sg-activity-type">{{ ucfirst(str_replace('_', ' ', $act->type)) }}</span>
                                                                <span class="sg-activity-time">{{ $act->created_at->diffForHumans() }}</span>
                                                                @if($act->user)
                                                                    <span class="sg-activity-time">· {{ $act->user->first_name }}</span>
                                                                @else
                                                                    <span class="sg-activity-time">· System</span>
                                                                @endif
                                                            </div>
                                                            @if($act->description)
                                                                <p class="sg-activity-desc">{{ $act->description }}</p>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif

                                    </div>
                                @empty
                                    <div style="text-align:center;padding:2rem 1rem;">
                                        <svg width="36" height="36" viewBox="0 0 24 24" fill="none" style="color:#d1d5db;margin-bottom:10px;display:block;margin-left:auto;margin-right:auto;">
                                            <path d="M12 2l3 6h6l-4.5 4.5 1.5 6-6-3-6 3 1.5-6L3 8h6z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                        <p style="color:#9ca3af;font-size:14px;margin:0;">No suggestions for this lead yet.</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    {{-- ═══════════════════════════════════════════ --}}
                    {{-- RIGHT — Lead details + Timeline             --}}
                    {{-- ═══════════════════════════════════════════ --}}
                    <div class="col-lg-5">

                        {{-- Lead details card --}}
                        <div class="sg-card mb-4">
                            <div class="sg-card__head">
                                <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
                                    <path d="M9 5H7a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2M9 5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v0a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                </svg>
                                Lead Details
                            </div>
                            <div class="sg-card__body">
                                <div class="sg-detail-row">
                                    <span class="sg-detail-label">Contact</span>
                                    <span class="sg-detail-val">{{ $lead->contact_person_name ?: '—' }}</span>
                                </div>
                                <div class="sg-detail-row">
                                    <span class="sg-detail-label">Phone</span>
                                    <span class="sg-detail-val">{{ $lead->company->phone ?: '—' }}</span>
                                </div>
                                <div class="sg-detail-row">
                                    <span class="sg-detail-label">Email</span>
                                    <span class="sg-detail-val">{{ $lead->company->email ?: '—' }}</span>
                                </div>
                                <div class="sg-detail-row">
                                    <span class="sg-detail-label">Units</span>
                                    <span class="sg-detail-val">{{ $lead->company->estimated_units ?: '—' }}</span>
                                </div>
                                <div class="sg-detail-row">
                                    <span class="sg-detail-label">Property Type</span>
                                    <span class="sg-detail-val">{{ $lead->company->property_type ? ucfirst(str_replace('_', ' ', $lead->company->property_type)) : '—' }}</span>
                                </div>
                                <div class="sg-detail-row" style="border-bottom:none;">
                                    <span class="sg-detail-label">Lead Created</span>
                                    <span class="sg-detail-val">{{ $lead->created_at->format('d M Y') }}</span>
                                </div>
                            </div>
                        </div>

                        {{-- Activity Timeline --}}
                        <div class="sg-card">
                            <div class="sg-card__head">
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
                            <div class="sg-card__body">
                                @forelse($lead->activities as $index => $activity)
                                    <div class="sg-timeline-item activity-entry {{ $index >= 5 ? 'activity-entry--hidden' : '' }}"
                                        style="{{ $index >= 5 ? 'display:none;' : '' }}">
                                        <div class="sg-timeline-dot"></div>
                                        <div class="sg-timeline-content">
                                            <div class="d-flex align-items-center gap-2 mb-1">
                                                <span class="sg-activity-type">{{ ucfirst(str_replace('_', ' ', $activity->type)) }}</span>
                                                @if($activity->user)
                                                    <span class="sg-activity-time">· {{ $activity->user->first_name }}</span>
                                                @else
                                                    <span class="sg-activity-time">· System</span>
                                                @endif
                                            </div>
                                            <p class="sg-timeline-time">{{ $activity->created_at->diffForHumans() }}</p>
                                            @if($activity->description)
                                                <p class="sg-timeline-desc">{{ $activity->description }}</p>
                                            @endif
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
                </div>
            </div>
        </div>
    </div>
</div>

@include('admin.affiliates.suggestions._suggestion_styles')

<style>
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