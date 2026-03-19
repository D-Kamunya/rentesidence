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
                    <a href="{{ route('admin.academy.index') }}" class="mod-back-link mb-4 d-inline-flex align-items-center gap-2">
                        <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
                            <path d="M10 3L5 8l5 5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Back to Modules
                    </a>

                    {{-- Alerts --}}
                    @if(session('success'))
                        <div class="mod-alert mod-alert--success mb-4">
                            <svg width="15" height="15" viewBox="0 0 16 16" fill="none" style="flex-shrink:0;">
                                <circle cx="8" cy="8" r="7" stroke="currentColor" stroke-width="1.5"/>
                                <path d="M4.5 8.5l2.5 2.5 4.5-5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <div>{{ session('success') }}</div>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="mod-alert mod-alert--danger mb-4">
                            <svg width="15" height="15" viewBox="0 0 16 16" fill="none" style="flex-shrink:0;margin-top:2px;">
                                <circle cx="8" cy="8" r="7" stroke="currentColor" stroke-width="1.5"/>
                                <path d="M8 5v4M8 11v.5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                            </svg>
                            <ul class="mb-0 ps-3" style="font-size:13px;">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- Form card --}}
                    <div class="qc-card mt-2">

                        <div class="qc-card__head">
                            <div>
                                <h5 class="mb-0" style="font-weight:500;">Edit Module</h5>
                                <p class="mb-0 mt-1" style="font-size:13px;color:#9ca3af;">{{ $academy->title }}</p>
                            </div>
                        </div>

                        <div class="qc-card__body">
                            <form method="POST" action="{{ route('admin.academy.update', $academy) }}">
                                @csrf
                                @method('PUT')

                                {{-- Title --}}
                                <div class="mb-4">
                                    <label class="qc-label">Title</label>
                                    <input type="text"
                                           name="title"
                                           class="qc-input @error('title') qc-input--error @enderror"
                                           value="{{ old('title', $academy->title) }}"
                                           placeholder="Module title"
                                           required>
                                    @error('title')
                                        <p class="qc-error-msg">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Existing video preview --}}
                                @if($academy->youtube_url)
                                    <div class="mb-3">
                                        <label class="qc-label">Current Video</label>
                                        <div class="qc-video-wrap">
                                            <div class="ratio ratio-16x9">
                                                <iframe
                                                    src="https://www.youtube.com/embed/{{ getYoutubeId($academy->youtube_url) }}"
                                                    allowfullscreen
                                                    style="border-radius:8px;">
                                                </iframe>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                {{-- YouTube URL + Duration row --}}
                                <div class="row g-3 mb-4">
                                    <div class="col-md-9">
                                        <label class="qc-label">YouTube Training Video</label>
                                        <input type="text"
                                               name="youtube_url"
                                               class="qc-input"
                                               value="{{ old('youtube_url', $academy->youtube_url) }}"
                                               placeholder="Paste YouTube link">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="qc-label">Duration (mins)</label>
                                        <input type="number"
                                               name="duration_minutes"
                                               class="qc-input"
                                               value="{{ old('duration_minutes', $academy->duration_minutes) }}"
                                               placeholder="e.g. 12"
                                               min="1">
                                    </div>
                                </div>

                                {{-- Divider --}}
                                <div class="qc-divider mb-4"><span>Content</span></div>

                                {{-- Content --}}
                                <div class="mb-4">
                                    <label class="qc-label">Module Content</label>
                                    <textarea name="content"
                                              rows="8"
                                              class="qc-input qc-textarea @error('content') qc-input--error @enderror"
                                              placeholder="Write the module content…"
                                              required>{{ old('content', $academy->content) }}</textarea>
                                    @error('content')
                                        <p class="qc-error-msg">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Divider --}}
                                <div class="qc-divider mb-4"><span>Settings</span></div>

                                {{-- Order + Active row --}}
                                <div class="row g-3 mb-4 align-items-center">
                                    <div class="col-md-3">
                                        <label class="qc-label">Module Order</label>
                                        <input type="number"
                                               name="module_order"
                                               class="qc-input @error('module_order') qc-input--error @enderror"
                                               value="{{ old('module_order', $academy->module_order) }}"
                                               placeholder="e.g. 1"
                                               min="1"
                                               required>
                                        @error('module_order')
                                            <p class="qc-error-msg">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div class="col-md-9 d-flex align-items-center" style="padding-top:1.6rem;">
                                        <label class="qc-toggle">
                                            <input type="checkbox"
                                                   name="is_active"
                                                   value="1"
                                                   id="isActiveSwitch"
                                                   {{ old('is_active', $academy->is_active) ? 'checked' : '' }}>
                                            <span class="qc-toggle__track">
                                                <span class="qc-toggle__thumb"></span>
                                            </span>
                                            <span class="qc-toggle__label">Module Active</span>
                                        </label>
                                    </div>
                                </div>

                                {{-- Divider --}}
                                <div class="qc-divider mb-4"></div>

                                {{-- Actions --}}
                                <div class="d-flex align-items-center gap-3">
                                    <button type="submit" class="adm-btn-primary">
                                        <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
                                            <path d="M2 11.5V14h2.5l7.4-7.4-2.5-2.5L2 11.5zM13.7 4.3a.7.7 0 0 0 0-1L12 1.6a.7.7 0 0 0-1 0l-1.3 1.3 2.5 2.5 1.5-1.1z" fill="currentColor"/>
                                        </svg>
                                        Update Module
                                    </button>
                                    <a href="{{ route('admin.academy.index') }}" class="adm-btn-ghost">
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

    /* ── Alerts ──────────────────────────────────────────── */
    .mod-alert {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        padding: .85rem 1.1rem;
        border-radius: 10px;
        font-size: 14px;
    }
    .mod-alert--success { background: #E1F5EE; color: #0F6E56; }
    .mod-alert--danger  { background: #FCEBEB; color: #A32D2D; }

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

    /* ── Labels ──────────────────────────────────────────── */
    .qc-label {
        display: block;
        font-size: 12px;
        font-weight: 500;
        color: #374151;
        text-transform: uppercase;
        letter-spacing: .05em;
        margin-bottom: 6px;
    }

    /* ── Inputs ──────────────────────────────────────────── */
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
    .qc-input--error { border-color: #F09595; }
    .qc-input--error:focus { box-shadow: 0 0 0 3px rgba(226,75,74,.1); }
    .qc-textarea { resize: vertical; min-height: 160px; }
    .qc-error-msg { font-size: 12px; color: #A32D2D; margin-top: 4px; margin-bottom: 0; }

    /* ── Video wrap ──────────────────────────────────────── */
    .qc-video-wrap {
        border: 0.5px solid #e5e7eb;
        border-radius: 10px;
        overflow: hidden;
        margin-bottom: 1rem;
    }

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
    .qc-divider:not(:has(span))::after { display: none; }

    /* ── Toggle switch ───────────────────────────────────── */
    .qc-toggle {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        cursor: pointer;
        user-select: none;
    }
    .qc-toggle input { display: none; }
    .qc-toggle__track {
        position: relative;
        width: 40px;
        height: 22px;
        background: #d1d5db;
        border-radius: 99px;
        transition: background .2s;
        flex-shrink: 0;
    }
    .qc-toggle input:checked ~ .qc-toggle__track { background: #1D9E75; }
    .qc-toggle__thumb {
        position: absolute;
        top: 3px;
        left: 3px;
        width: 16px;
        height: 16px;
        background: #fff;
        border-radius: 50%;
        transition: transform .2s;
        box-shadow: 0 1px 3px rgba(0,0,0,.15);
    }
    .qc-toggle input:checked ~ .qc-toggle__track .qc-toggle__thumb { transform: translateX(18px); }
    .qc-toggle__label { font-size: 14px; font-weight: 500; color: #374151; }

    /* ── Primary button ──────────────────────────────────── */
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

    /* ── Ghost / cancel button ───────────────────────────── */
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
@endsection