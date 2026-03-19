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

                        {{-- Back link --}}
                        <a href="{{ route('admin.academy.questions', $module->id) }}" class="mod-back-link mb-4 d-inline-flex align-items-center gap-2">
                            <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
                                <path d="M10 3L5 8l5 5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            Back to Questions
                        </a>

                        <div class="qc-card mt-3">
                            <div class="qc-card__head">
                                <div>
                                    <h5 class="mb-0" style="font-weight:500;">Add Question</h5>
                                    <p class="mb-0 mt-1" style="font-size:13px;color:#9ca3af;">{{ $module->title }}</p>
                                </div>
                            </div>

                            <div class="qc-card__body">
                                <form method="POST" action="{{ route('admin.academy.questions.store', $module->id) }}">
                                    @csrf

                                    {{-- Question + Order row --}}
                                    <div class="row g-3 mb-4">
                                        <div class="col-md-9">
                                            <label class="qc-label">Question</label>
                                            <textarea name="question"
                                                      class="qc-input qc-textarea"
                                                      rows="3"
                                                      placeholder="Enter the question text…"
                                                      required></textarea>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="qc-label">Order</label>
                                            <input type="number"
                                                   name="question_order"
                                                   class="qc-input"
                                                   placeholder="e.g. 1"
                                                   min="1"
                                                   required>
                                        </div>
                                    </div>

                                    {{-- Divider --}}
                                    <div class="qc-divider mb-4">
                                        <span>Answer Options</span>
                                    </div>

                                    {{-- Options --}}
                                    <div id="options-wrapper">
                                        @for($i = 0; $i < 4; $i++)
                                            <div class="option-group mb-3">
                                                <div class="option-group__index">{{ chr(65 + $i) }}</div>
                                                <input type="text"
                                                       name="options[{{ $i }}][option_text]"
                                                       class="qc-input option-group__input"
                                                       placeholder="Option text"
                                                       required>
                                                <label class="option-group__correct">
                                                    <input type="radio"
                                                           name="correct_option"
                                                           value="{{ $i }}"
                                                           required>
                                                    <span class="option-group__correct-label">Correct</span>
                                                </label>
                                                <button type="button" class="option-group__remove remove-option" title="Remove">
                                                    <svg width="13" height="13" viewBox="0 0 16 16" fill="none">
                                                        <path d="M3 3l10 10M13 3L3 13" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                                    </svg>
                                                </button>
                                            </div>
                                        @endfor
                                    </div>

                                    {{-- Add option --}}
                                    <button type="button" id="add-option" class="qc-btn-ghost mb-4">
                                        <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
                                            <path d="M8 3v10M3 8h10" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                        </svg>
                                        Add Option
                                    </button>

                                    {{-- Divider --}}
                                    <div class="qc-divider mb-4"></div>

                                    {{-- Actions --}}
                                    <div class="d-flex align-items-center gap-3">
                                        <button type="submit" class="adm-btn-primary">
                                            <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
                                                <path d="M3 8.5l3.5 3.5 6.5-7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                            Save Question
                                        </button>
                                        <a href="{{ route('admin.academy.questions', $module->id) }}" class="adm-btn-ghost">
                                            Cancel
                                        </a>
                                    </div>

                                </form>
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

        /* ── Form card ───────────────────────────────────────── */
        .qc-card {
            background: #fff;
            border: 0.5px solid #e5e7eb;
            border-radius: 14px;
            overflow: hidden;
        }
        .qc-card__head {
            padding: 1.1rem 1.5rem;
            border-bottom: 0.5px solid #e5e7eb;
            background: #fafafa;
        }
        .qc-card__body { padding: 1.5rem; }

        /* ── Form controls ───────────────────────────────────── */
        .qc-label {
            display: block;
            font-size: 12px;
            font-weight: 500;
            color: #374151;
            text-transform: uppercase;
            letter-spacing: .05em;
            margin-bottom: 6px;
        }
        .qc-input {
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
        .qc-input:focus {
            border-color: #185FA5;
            box-shadow: 0 0 0 3px rgba(24,95,165,.1);
        }
        .qc-textarea { resize: vertical; min-height: 90px; }

        /* ── Divider ─────────────────────────────────────────── */
        .qc-divider {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 11px;
            font-weight: 500;
            color: #9ca3af;
            text-transform: uppercase;
            letter-spacing: .06em;
        }
        .qc-divider::before,
        .qc-divider::after {
            content: '';
            flex: 1;
            height: 0.5px;
            background: #e5e7eb;
        }
        .qc-divider:empty::after { display: none; }

        /* ── Option row ──────────────────────────────────────── */
        .option-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .option-group__index {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 28px;
            height: 28px;
            background: #f3f4f6;
            color: #6b7280;
            font-size: 12px;
            font-weight: 500;
            border-radius: 6px;
            flex-shrink: 0;
        }
        .option-group__input { flex: 1; }

        /* ── Correct radio ───────────────────────────────────── */
        .option-group__correct {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
            flex-shrink: 0;
            padding: 6px 12px;
            border: 0.5px solid #e5e7eb;
            border-radius: 8px;
            transition: background .15s, border-color .15s;
        }
        .option-group__correct:has(input:checked) {
            background: #E1F5EE;
            border-color: #9FE1CB;
        }
        .option-group__correct input[type="radio"] {
            accent-color: #1D9E75;
            width: 14px;
            height: 14px;
        }
        .option-group__correct-label {
            font-size: 12px;
            font-weight: 500;
            color: #6b7280;
        }
        .option-group__correct:has(input:checked) .option-group__correct-label {
            color: #0F6E56;
        }

        /* ── Remove button ───────────────────────────────────── */
        .option-group__remove {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 30px;
            height: 30px;
            border-radius: 7px;
            border: 0.5px solid #F7C1C1;
            background: transparent;
            color: #A32D2D;
            cursor: pointer;
            flex-shrink: 0;
            transition: background .15s;
        }
        .option-group__remove:hover { background: #FCEBEB; }

        /* ── Ghost / add option button ───────────────────────── */
        .qc-btn-ghost {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 8px 16px;
            font-size: 13px;
            font-weight: 500;
            color: #185FA5;
            background: #EEF5FD;
            border: 0.5px solid #B5D4F4;
            border-radius: 8px;
            cursor: pointer;
            transition: background .15s, border-color .15s;
        }
        .qc-btn-ghost:hover { background: #dbeeff; border-color: #185FA5; }

        /* ── Primary submit button ───────────────────────────── */
        .adm-btn-primary {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 9px 20px;
            background: #185FA5;
            color: #fff;
            font-size: 13px;
            font-weight: 500;
            border-radius: 8px;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: background .2s, transform .2s, box-shadow .2s;
        }
        .adm-btn-primary:hover {
            background: #0C447C;
            color: #fff;
            transform: translateY(-1px);
            box-shadow: 0 5px 14px rgba(24,95,165,.22);
        }

        /* ── Cancel ghost button ─────────────────────────────── */
        .adm-btn-ghost {
            display: inline-flex;
            align-items: center;
            gap: 7px;
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
        .adm-btn-ghost:hover { background: #f3f4f6; color: #111827; }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {

            let optionIndex = 4;

            function getLabel(i) {
                return String.fromCharCode(65 + i);
            }

            function refreshLabels() {
                document.querySelectorAll('.option-group__index').forEach((el, idx) => {
                    el.textContent = getLabel(idx);
                });
            }

            document.getElementById('add-option').addEventListener('click', function () {
                const wrapper = document.getElementById('options-wrapper');
                const div = document.createElement('div');
                div.className = 'option-group mb-3';
                div.innerHTML = `
                    <div class="option-group__index">${getLabel(optionIndex)}</div>
                    <input type="text"
                           name="options[${optionIndex}][option_text]"
                           class="qc-input option-group__input"
                           placeholder="Option text"
                           required>
                    <label class="option-group__correct">
                        <input type="radio" name="correct_option" value="${optionIndex}" required>
                        <span class="option-group__correct-label">Correct</span>
                    </label>
                    <button type="button" class="option-group__remove remove-option" title="Remove">
                        <svg width="13" height="13" viewBox="0 0 16 16" fill="none">
                            <path d="M3 3l10 10M13 3L3 13" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                        </svg>
                    </button>
                `;
                wrapper.appendChild(div);
                optionIndex++;
                refreshLabels();
            });

            document.addEventListener('click', function (e) {
                if (e.target.closest('.remove-option')) {
                    e.target.closest('.option-group').remove();
                    refreshLabels();
                }
            });

        });
    </script>
@endsection