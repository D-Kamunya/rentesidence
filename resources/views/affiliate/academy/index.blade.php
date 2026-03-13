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
                                    <h3 class="mb-sm-0">Centresidence Academy</h3>
                                </div>
                                <div class="page-title-right">
                                    <ol class="breadcrumb mb-0">
                                        <li class="breadcrumb-item">
                                            <a href="{{ route('affiliate.dashboard') }}">Dashboard</a>
                                        </li>
                                        <li class="breadcrumb-item active">Academy</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="container mt-4">

                        {{-- Course Header Card --}}
                        @php
                            $total = $modules->count();
                            $completedCount = collect($progress)->whereNotNull('completed_at')->count();
                            $percentage = $total > 0 ? ($completedCount / $total) * 100 : 0;
                        @endphp

                        <div class="mb-4" style="background:#fff;border:0.5px solid #e5e7eb;border-radius:14px;padding:1.5rem;">
                            <div class="d-flex align-items-start justify-content-between flex-wrap gap-3 mb-3">
                                <div>
                                    <h4 class="mb-1" style="font-weight:500;">Centresidence Partner Certification</h4>
                                    <p class="mb-0" style="color:#6b7280;font-size:14px;max-width:560px;">
                                        Complete all modules to become a certified Centresidence partner and unlock lucrative recurring income generating opportunities.
                                    </p>
                                </div>
                                {{-- Circular-ish progress chip --}}
                                <div style="text-align:center;min-width:64px;">
                                    <div style="font-size:22px;font-weight:500;color:#0F6E56;line-height:1;">{{ round($percentage) }}%</div>
                                    <div style="font-size:11px;color:#9ca3af;margin-top:2px;">complete</div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span style="font-size:13px;font-weight:500;color:#374151;">Course Progress</span>
                                <span style="font-size:12px;color:#9ca3af;">{{ $completedCount }} / {{ $total }} modules</span>
                            </div>
                            <div style="height:6px;background:#e5e7eb;border-radius:99px;overflow:hidden;">
                                <div style="height:100%;width:{{ $percentage }}%;background:#1D9E75;border-radius:99px;transition:width .4s ease;"></div>
                            </div>
                        </div>

                        {{-- Flash Messages --}}
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif
                        @if(session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        {{-- Module List --}}
                        @forelse($modules as $module)
                            @php
                                $moduleProgress = $progress[$module->id] ?? null;
                                $completed      = $moduleProgress && $moduleProgress->completed_at;
                                $needsReview    = $moduleProgress && $moduleProgress->needs_review;
                                $locked         = !$completed && !$loop->first &&
                                                  empty($progress[$modules[$loop->index - 1]->id]?->completed_at);
                            @endphp

                            <div class="module-card mb-3 {{ $locked ? 'module-card--locked' : '' }}">
                                <div class="module-card__body d-flex justify-content-between align-items-center flex-wrap gap-3">

                                    {{-- Left: Icon + Info --}}
                                    <div class="d-flex align-items-center gap-3">

                                        {{-- Status Icon --}}
                                        <div class="module-icon
                                            @if($completed) module-icon--done
                                            @elseif($needsReview) module-icon--review
                                            @elseif($locked) module-icon--locked
                                            @else module-icon--ready
                                            @endif">
                                            @if($completed)
                                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M3 8.5l3.5 3.5 6.5-7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                            @elseif($needsReview)
                                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M8 5v4M8 11v.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                                            @elseif($locked)
                                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><rect x="4" y="7" width="8" height="6" rx="1.5" stroke="currentColor" stroke-width="1.6"/><path d="M5.5 7V5.5a2.5 2.5 0 0 1 5 0V7" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
                                            @else
                                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M5.5 3.5l7 4.5-7 4.5V3.5z" fill="currentColor"/></svg>
                                            @endif
                                        </div>

                                        {{-- Text --}}
                                        <div>
                                            <div style="font-weight:500;font-size:15px;color:{{ $locked ? '#9ca3af' : '#111827' }};">
                                                {{ $module->module_order }}. {{ $module->title }}
                                            </div>

                                            @if($module->duration_minutes)
                                                <div style="font-size:12px;color:#9ca3af;margin-top:2px;">
                                                    {{ $module->duration_minutes }} min training
                                                </div>
                                            @endif

                                            {{-- Status badge --}}
                                            <div class="mt-2">
                                                @if($completed)
                                                    <span class="mod-badge mod-badge--done">Completed</span>
                                                    @if($moduleProgress->score)
                                                        <span class="mod-badge mod-badge--score">
                                                            Score: {{ number_format($moduleProgress->score, 0) }}%
                                                        </span>
                                                    @endif
                                                @elseif($needsReview)
                                                    <span class="mod-badge mod-badge--review">Under Review</span>
                                                @elseif($locked)
                                                    <span class="mod-badge mod-badge--locked">Locked</span>
                                                @else
                                                    <span class="mod-badge mod-badge--ready">Ready to Start</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Right: Action Button --}}
                                    <div>
                                        @if($completed)
                                            <a href="{{ route('affiliate.academy.show', $module->id) }}?review=1"
                                               class="acad-btn acad-btn--review">
                                                <svg width="13" height="13" viewBox="0 0 16 16" fill="none"><path d="M13 8A5 5 0 1 1 8 3" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/><path d="M8 1v4l2-2" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                                Review
                                            </a>
                                        @elseif($locked)
                                            <button class="acad-btn acad-btn--locked" disabled>
                                                <svg width="13" height="13" viewBox="0 0 16 16" fill="none"><rect x="4" y="7" width="8" height="6" rx="1.5" stroke="currentColor" stroke-width="1.6"/><path d="M5.5 7V5.5a2.5 2.5 0 0 1 5 0V7" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
                                                Locked
                                            </button>
                                        @else
                                            <a href="{{ route('affiliate.academy.show', $module->id) }}"
                                               class="acad-btn acad-btn--start">
                                                <svg width="13" height="13" viewBox="0 0 16 16" fill="none"><path d="M4.5 3l8 5-8 5V3z" fill="currentColor"/></svg>
                                                Start
                                            </a>
                                        @endif
                                    </div>

                                </div>
                            </div>

                        @empty
                            <div class="alert alert-info">No academy modules available.</div>
                        @endforelse

                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* ── Module card ─────────────────────────────────────── */
        .module-card {
            background: #fff;
            border: 0.5px solid #e5e7eb;
            border-radius: 12px;
            overflow: hidden;
            transition: box-shadow .2s ease, transform .2s ease;
        }
        .module-card:hover {
            box-shadow: 0 6px 18px rgba(0,0,0,.07);
            transform: translateY(-2px);
        }
        .module-card--locked {
            opacity: .55;
            pointer-events: none;
        }
        .module-card__body {
            padding: 1.1rem 1.25rem;
        }

        /* ── Status icon circle ──────────────────────────────── */
        .module-icon {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .module-icon--done   { background: #E1F5EE; color: #0F6E56; }
        .module-icon--review { background: #FCEBEB; color: #A32D2D; }
        .module-icon--locked { background: #f3f4f6; color: #9ca3af; }
        .module-icon--ready  { background: #EEF5FD; color: #185FA5; }

        /* ── Status badges ───────────────────────────────────── */
        .mod-badge {
            display: inline-flex;
            align-items: center;
            font-size: 11px;
            font-weight: 500;
            padding: 3px 10px;
            border-radius: 99px;
            margin-right: 4px;
        }
        .mod-badge--done   { background: #E1F5EE; color: #0F6E56; }
        .mod-badge--score  { background: #E6F1FB; color: #185FA5; }
        .mod-badge--review { background: #FCEBEB; color: #A32D2D; }
        .mod-badge--locked { background: #f3f4f6; color: #9ca3af; }
        .mod-badge--ready  { background: #FEF9EE; color: #854F0B; }

        /* ── Action buttons ──────────────────────────────────── */
        .acad-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            min-width: 110px;
            padding: 8px 18px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 500;
            border: none;
            cursor: pointer;
            text-decoration: none;
            transition: transform .2s ease, box-shadow .2s ease;
        }
        .acad-btn--start {
            background: #185FA5;
            color: #fff;
        }
        .acad-btn--start:hover {
            background: #0C447C;
            color: #fff;
            transform: translateY(-1px);
            box-shadow: 0 5px 12px rgba(24,95,165,.25);
        }
        .acad-btn--review {
            background: #0F6E56;
            color: #fff;
        }
        .acad-btn--review:hover {
            background: #085041;
            color: #fff;
            transform: translateY(-1px);
            box-shadow: 0 5px 12px rgba(15,110,86,.25);
        }
        .acad-btn--locked {
            background: #f3f4f6;
            color: #9ca3af;
            cursor: not-allowed;
        }

        /* ── Mobile ──────────────────────────────────────────── */
        @media (max-width: 576px) {
            .acad-btn {
                width: 100%;
            }
        }
    </style>
@endsection