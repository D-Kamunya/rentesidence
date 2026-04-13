@extends('affiliate.layouts.app')

@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="page-content-wrapper bg-white p-30 radius-20">

                    {{-- Page Title --}}
                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box d-sm-flex align-items-center justify-content-between border-bottom mb-20">
                                <div class="page-title-left">
                                    <h3 class="mb-sm-0">Centresidence Academy</h3>
                                </div>
                                <div class="page-title-right">
                                    <ol class="breadcrumb mb-0">
                                        <li class="breadcrumb-item">
                                            <a href="{{ route('affiliate.dashboard') }}" title="{{ __('Dashboard') }}">{{ __('Dashboard') }}</a>
                                        </li>
                                        <li class="breadcrumb-item active" aria-current="page">Centresidence Academy</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="container mt-4">

                        {{-- Back link --}}
                        <a href="{{ route('affiliate.academy.index') }}" class="mod-back-link mb-4 d-inline-flex align-items-center gap-2">
                            <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
                                <path d="M10 3L5 8l5 5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            Back to Academy
                        </a>

                        {{-- Module meta --}}
                        <div class="mb-3 d-flex align-items-center gap-3 flex-wrap">
                            <h4 class="mb-0" style="font-weight:500;">
                                {{ $module->module_order }}. {{ $module->title }}
                            </h4>
                            @if($module->duration_minutes)
                                <span class="mod-meta-pill">
                                    <svg width="12" height="12" viewBox="0 0 16 16" fill="none">
                                        <circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.5"/>
                                        <path d="M8 5v3.5l2 1.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    {{ $module->duration_minutes }} min training
                                </span>
                            @endif
                        </div>

                        {{-- Video --}}
                        @if($module->youtube_url)
                            <div class="mod-video-wrap mb-4">
                                <div class="ratio ratio-16x9">
                                    <iframe
                                        id="academy-video"
                                        src="https://www.youtube.com/embed/{{ getYoutubeId($module->youtube_url) }}?enablejsapi=1"
                                        allowfullscreen
                                        style="border-radius:10px;">
                                    </iframe>
                                </div>
                            </div>
                        @endif

                        {{-- Module Content Card --}}
                        <div class="mod-card mb-4">
                            <div class="mod-card__body">
                                <div class="mod-content">
                                    {!! $module->content !!}
                                </div>
                            </div>
                        </div>

                        @php
                            $moduleProgress = $progress[$module->id] ?? null;
                            $attempts       = $moduleProgress->attempts ?? 0;
                            $completed      = $moduleProgress && $moduleProgress->completed_at;
                            $needsReview    = $moduleProgress && $moduleProgress->needs_review;
                            $pageTitle      = 'Partner Academy';
                        @endphp

                        {{-- Status Alerts --}}
                        @if($completed)
                            <div class="mod-alert mod-alert--success mb-3">
                                <div class="mod-alert__icon">
                                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                        <circle cx="8" cy="8" r="7" stroke="currentColor" stroke-width="1.5"/>
                                        <path d="M4.5 8.5l2.5 2.5 4.5-5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </div>
                                <div>
                                    <span style="font-weight:500;">Module passed!</span>
                                    Your score: <strong>{{ number_format($moduleProgress->score ?? 0, 0) }}%</strong>
                                </div>
                            </div>
                        @elseif($needsReview)
                            <div class="mod-alert mod-alert--danger mb-3">
                                <div class="mod-alert__icon">
                                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                        <circle cx="8" cy="8" r="7" stroke="currentColor" stroke-width="1.5"/>
                                        <path d="M8 5v4M8 11v.5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                                    </svg>
                                </div>
                                <div>
                                    <span style="font-weight:500;">Maximum attempts reached.</span>
                                    This module is pending admin review.
                                </div>
                            </div>
                        @endif

                        @if(!$completed && !$needsReview)
                            <div id="video-warning" class="mod-alert mod-alert--info mb-3">
                                <div class="mod-alert__icon">
                                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                        <circle cx="8" cy="8" r="7" stroke="currentColor" stroke-width="1.5"/>
                                        <path d="M8 7v4M8 5v.5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                                    </svg>
                                </div>
                                <div>Watch the training video above to unlock the quiz.</div>
                            </div>
                        @endif

                        {{-- Quiz Section --}}
                        <div id="quiz-section" style="display:none;">
                            <div class="mod-card">
                                <div class="mod-card__body">

                                    @if(!$completed && !$needsReview)
                                        {{-- Quiz Header --}}
                                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
                                            <h5 class="mb-0" style="font-weight:500;">Module Quiz</h5>
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="mod-meta-pill">
                                                    Attempts: {{ $attempts }} / 3
                                                </span>
                                                <span class="mod-meta-pill mod-meta-pill--green">
                                                    Pass: 80%
                                                </span>
                                            </div>
                                        </div>

                                        <form method="POST" action="{{ route('affiliate.academy.submit', $module->id) }}">
                                            @csrf

                                            @foreach($module->questions as $question)
                                                <div class="quiz-question mb-4">
                                                    <p class="quiz-question__text">
                                                        {{ $loop->iteration }}. {{ $question->question }}
                                                    </p>

                                                    <div class="quiz-options">
                                                        @foreach($question->options as $option)
                                                            <label class="quiz-option">
                                                                <input
                                                                    type="radio"
                                                                    name="question_{{ $question->id }}"
                                                                    value="{{ $option->id }}"
                                                                    required>
                                                                <span class="quiz-option__label">{{ $option->option_text }}</span>
                                                            </label>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endforeach

                                            <button type="submit" class="quiz-submit-btn">
                                                Submit Quiz
                                                <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
                                                    <path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                            </button>
                                        </form>
                                    @endif

                                </div>
                            </div>
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

        /* ── Meta pill ───────────────────────────────────────── */
        .mod-meta-pill {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 12px;
            font-weight: 500;
            color: #6b7280;
            background: #f3f4f6;
            padding: 4px 10px;
            border-radius: 99px;
        }
        .mod-meta-pill--green {
            background: #E1F5EE;
            color: #0F6E56;
        }

        /* ── Video wrapper ───────────────────────────────────── */
        .mod-video-wrap {
            border-radius: 12px;
            overflow: hidden;
            border: 0.5px solid #e5e7eb;
        }

        /* ── Card ────────────────────────────────────────────── */
        .mod-card {
            background: #fff;
            border: 0.5px solid #e5e7eb;
            border-radius: 12px;
            overflow: hidden;
        }
        .mod-card__body { padding: 1.5rem; }

        /* ── Module content typography ───────────────────────── */
        .mod-content { font-size: 15px; line-height: 1.75; color: #374151; }
        .mod-content h1,.mod-content h2,.mod-content h3 { font-weight: 500; margin-top: 1.5rem; margin-bottom: .5rem; }
        .mod-content p { margin-bottom: 1rem; }
        .mod-content ul,.mod-content ol { padding-left: 1.25rem; margin-bottom: 1rem; }
        .mod-content li { margin-bottom: .35rem; }

        /* ── Alerts ──────────────────────────────────────────── */
        .mod-alert {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: .85rem 1.1rem;
            border-radius: 10px;
            font-size: 14px;
        }
        .mod-alert__icon {
            flex-shrink: 0;
            margin-top: 1px;
        }
        .mod-alert--success { background: #E1F5EE; color: #0F6E56; }
        .mod-alert--danger  { background: #FCEBEB; color: #A32D2D; }
        .mod-alert--info    { background: #E6F1FB; color: #185FA5; }

        /* ── Quiz question ───────────────────────────────────── */
        .quiz-question__text {
            font-size: 14px;
            font-weight: 500;
            color: #111827;
            margin-bottom: .75rem;
        }
        .quiz-options { display: flex; flex-direction: column; gap: 8px; }

        .quiz-option {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: .65rem 1rem;
            border: 0.5px solid #e5e7eb;
            border-radius: 8px;
            cursor: pointer;
            transition: border-color .15s, background .15s;
        }
        .quiz-option:hover {
            border-color: #185FA5;
            background: #EEF5FD;
        }
        .quiz-option input[type="radio"] {
            accent-color: #185FA5;
            width: 15px;
            height: 15px;
            flex-shrink: 0;
        }
        .quiz-option input[type="radio"]:checked + .quiz-option__label {
            color: #185FA5;
            font-weight: 500;
        }
        .quiz-option:has(input:checked) {
            border-color: #185FA5;
            background: #EEF5FD;
        }
        .quiz-option__label { font-size: 14px; color: #374151; }

        /* ── Submit button ───────────────────────────────────── */
        .quiz-submit-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-top: .5rem;
            padding: 10px 22px;
            background: #185FA5;
            color: #fff;
            font-size: 14px;
            font-weight: 500;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background .2s, transform .2s, box-shadow .2s;
        }
        .quiz-submit-btn:hover {
            background: #0C447C;
            transform: translateY(-1px);
            box-shadow: 0 5px 14px rgba(24,95,165,.25);
        }
        .quiz-submit-btn:active { transform: scale(.98); }
    </style>

    <script src="https://www.youtube.com/iframe_api"></script>
    <script>
        var player;
        function onYouTubeIframeAPIReady() {
            player = new YT.Player('academy-video', {
                events: { 'onStateChange': onPlayerStateChange }
            });
        }
        function onPlayerStateChange(event) {
            if (event.data == YT.PlayerState.ENDED) {
                document.getElementById('quiz-section').style.display = 'block';
                document.getElementById('video-warning').style.display = 'none';
            }
        }
    </script>
@endsection