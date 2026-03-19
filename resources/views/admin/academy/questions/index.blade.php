@extends('admin.layouts.app')

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
                                            <a href="{{ route('admin.dashboard') }}" title="{{ __('Dashboard') }}">{{ __('Dashboard') }}</a>
                                        </li>
                                        <li class="breadcrumb-item active" aria-current="page">Centresidence Academy</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="container mt-4">

                        {{-- Header --}}
                        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
                            <div>
                                <h5 class="mb-1" style="font-weight:500;">{{ $module->title }}</h5>
                                <p class="mb-0" style="font-size:13px;color:#9ca3af;">
                                    {{ $questions->count() }} {{ Str::plural('question', $questions->count()) }}
                                </p>
                            </div>
                            <a href="{{ route('admin.academy.questions.create', $module->id) }}" class="adm-btn-primary">
                                <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
                                    <path d="M8 3v10M3 8h10" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                </svg>
                                Add Question
                            </a>
                        </div>

                        {{-- Questions --}}
                        @forelse($questions as $question)
                            <div class="q-card mb-3">

                                {{-- Question row --}}
                                <div class="q-card__header">
                                    <span class="q-order">{{ $question->question_order }}</span>
                                    <p class="q-text">{{ $question->question }}</p>
                                </div>

                                {{-- Options --}}
                                @if($question->options->count())
                                    <div class="q-options">
                                        @foreach($question->options as $option)
                                            <div class="q-option {{ $option->is_correct ? 'q-option--correct' : '' }}">
                                                <span class="q-option__dot {{ $option->is_correct ? 'q-option__dot--correct' : '' }}"></span>
                                                <span class="q-option__text">{{ $option->option_text }}</span>
                                                @if($option->is_correct)
                                                    <span class="q-correct-badge">
                                                        <svg width="11" height="11" viewBox="0 0 16 16" fill="none">
                                                            <path d="M3 8.5l3.5 3.5 6.5-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                        </svg>
                                                        Correct
                                                    </span>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="q-empty-options">No options added yet.</div>
                                @endif

                            </div>
                        @empty
                            <div class="q-empty-state">
                                <svg width="36" height="36" viewBox="0 0 24 24" fill="none" style="color:#d1d5db;">
                                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.5"/>
                                    <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3M12 17h.01" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                </svg>
                                <p>No questions added yet for this module.</p>
                            </div>
                        @endforelse

                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* ── Primary button ──────────────────────────────────── */
        .adm-btn-primary {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 9px 18px;
            background: #185FA5;
            color: #fff;
            font-size: 13px;
            font-weight: 500;
            border-radius: 8px;
            text-decoration: none;
            border: none;
            transition: background .2s, transform .2s, box-shadow .2s;
        }
        .adm-btn-primary:hover {
            background: #0C447C;
            color: #fff;
            transform: translateY(-1px);
            box-shadow: 0 5px 14px rgba(24,95,165,.22);
        }

        /* ── Question card ───────────────────────────────────── */
        .q-card {
            background: #fff;
            border: 0.5px solid #e5e7eb;
            border-radius: 12px;
            overflow: hidden;
        }
        .q-card__header {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 1.1rem 1.25rem;
            border-bottom: 0.5px solid #f3f4f6;
        }

        /* ── Question order chip ─────────────────────────────── */
        .q-order {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 26px;
            height: 26px;
            background: #EEF5FD;
            color: #185FA5;
            font-size: 12px;
            font-weight: 500;
            border-radius: 6px;
            flex-shrink: 0;
            margin-top: 1px;
        }

        /* ── Question text ───────────────────────────────────── */
        .q-text {
            font-size: 14px;
            font-weight: 500;
            color: #111827;
            margin: 0;
            line-height: 1.55;
        }

        /* ── Options list ────────────────────────────────────── */
        .q-options {
            padding: .75rem 1.25rem;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        .q-option {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: .55rem .85rem;
            border-radius: 7px;
            border: 0.5px solid #f3f4f6;
            background: #fafafa;
            transition: background .15s;
        }
        .q-option--correct {
            background: #E1F5EE;
            border-color: #9FE1CB;
        }

        /* ── Option dot ──────────────────────────────────────── */
        .q-option__dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #d1d5db;
            flex-shrink: 0;
        }
        .q-option__dot--correct { background: #1D9E75; }

        /* ── Option text ─────────────────────────────────────── */
        .q-option__text {
            font-size: 13px;
            color: #374151;
            flex: 1;
        }
        .q-option--correct .q-option__text {
            color: #0F6E56;
            font-weight: 500;
        }

        /* ── Correct badge ───────────────────────────────────── */
        .q-correct-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 11px;
            font-weight: 500;
            color: #0F6E56;
            background: #fff;
            border: 0.5px solid #9FE1CB;
            padding: 2px 8px;
            border-radius: 99px;
        }

        /* ── No options yet ──────────────────────────────────── */
        .q-empty-options {
            padding: .65rem 1.25rem;
            font-size: 12px;
            color: #9ca3af;
        }

        /* ── Empty state ─────────────────────────────────────── */
        .q-empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
            padding: 3rem 1rem;
            border: 0.5px solid #e5e7eb;
            border-radius: 12px;
            background: #fafafa;
            color: #9ca3af;
            font-size: 14px;
        }
        .q-empty-state p { margin: 0; }
    </style>
@endsection