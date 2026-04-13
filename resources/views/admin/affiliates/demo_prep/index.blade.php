@extends('admin.layouts.app')

@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="page-content-wrapper bg-white p-30 radius-20">

                    @php $pageTitle = 'Demo Prep'; @endphp

                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box d-sm-flex align-items-center justify-content-between border-bottom mb-4 pb-2">
                                <div class="page-title-left">
                                    <h3 class="mb-sm-0">Demo Preparation</h3>
                                </div>
                                <div class="page-title-right">
                                    <ol class="breadcrumb mb-0">
                                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                                        <li class="breadcrumb-item active">Demo Prep</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Flash messages --}}
                    @if(session('success'))
                        <div class="at-alert at-alert--success mb-4">
                            <svg width="15" height="15" viewBox="0 0 16 16" fill="none" style="flex-shrink:0;">
                                <circle cx="8" cy="8" r="7" stroke="currentColor" stroke-width="1.5"/>
                                <path d="M4.5 8.5l2.5 2.5 4.5-5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <div>{{ session('success') }}</div>
                        </div>
                    @endif
                    @if(session('error'))
                        <div class="at-alert at-alert--danger mb-4">
                            <svg width="15" height="15" viewBox="0 0 16 16" fill="none" style="flex-shrink:0;">
                                <circle cx="8" cy="8" r="7" stroke="currentColor" stroke-width="1.5"/>
                                <path d="M8 5v4M8 11v.5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                            </svg>
                            <div>{{ session('error') }}</div>
                        </div>
                    @endif

                    <div class="row g-4">

                        {{-- ── LEFT COLUMN ───────────────────────── --}}
                        <div class="col-lg-8">

                            {{-- Demo Account Credentials --}}
                            <div class="at-form-card mb-4">
                                <div class="at-form-card__head">
                                    <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
                                        <rect x="3" y="7" width="10" height="7" rx="1.5" stroke="currentColor" stroke-width="1.5"/>
                                        <path d="M5 7V5a3 3 0 0 1 6 0v2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                    </svg>
                                    Demo Account Credentials
                                    <span class="at-form-card__hint">Shown to affiliates on the demo prep card</span>
                                </div>
                                <div class="at-form-card__body">
                                    <form method="POST" action="{{ route('admin.demo_prep.settings.update') }}">
                                        @csrf
                                        <div class="row g-3 mb-4">
                                            <div class="col-md-6">
                                                <div class="at-field">
                                                    <label class="at-label">Login URL</label>
                                                    <input type="url" name="demo_login_url" class="at-input"
                                                        value="{{ old('demo_login_url', $settings->demo_login_url) }}"
                                                        placeholder="https://demo.yourdomain.com/login">
                                                    @error('demo_login_url')<p class="at-field-error">{{ $message }}</p>@enderror
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="at-field">
                                                    <label class="at-label">Demo Email</label>
                                                    <input type="email" name="demo_email" class="at-input"
                                                        value="{{ old('demo_email', $settings->demo_email) }}"
                                                        placeholder="demo@yourdomain.com">
                                                    @error('demo_email')<p class="at-field-error">{{ $message }}</p>@enderror
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="at-field">
                                                    <label class="at-label">Demo Password</label>
                                                    <input type="text" name="demo_password" class="at-input"
                                                        value="{{ old('demo_password', $settings->demo_password) }}"
                                                        placeholder="••••••••">
                                                    @error('demo_password')<p class="at-field-error">{{ $message }}</p>@enderror
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="at-field">
                                                    <label class="at-label">Notes for Affiliates</label>
                                                    <input type="text" name="demo_notes" class="at-input"
                                                        value="{{ old('demo_notes', $settings->demo_notes) }}"
                                                        placeholder="e.g. Reset data after each session">
                                                    @error('demo_notes')<p class="at-field-error">{{ $message }}</p>@enderror
                                                </div>
                                            </div>
                                        </div>
                                        <button type="submit" class="at-btn-primary">
                                            <svg width="13" height="13" viewBox="0 0 16 16" fill="none">
                                                <path d="M3 8.5l3 3 7-7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                            Save Credentials
                                        </button>
                                    </form>
                                </div>
                            </div>

                            {{-- Sections list --}}
                            <div class="at-form-card mb-4">
                                <div class="at-form-card__head">
                                    <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
                                        <path d="M2 4h12M2 8h8M2 12h10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                    </svg>
                                    Prep Guide Sections
                                    <span class="at-form-card__hint">Shown in order to affiliates on the demo prep card</span>
                                </div>

                                @if($sections->count() > 0)
                                    <div class="dp-section-list">
                                        @foreach($sections as $s)
                                            <div class="dp-section-row {{ isset($section) && $section->id === $s->id ? 'dp-section-row--editing' : '' }}">
                                                <div class="dp-section-row__left">
                                                    <span class="dp-sort-handle">{{ $s->sort_order }}</span>
                                                    <div>
                                                        <p class="dp-section-row__title">{{ $s->title }}</p>
                                                        <p class="dp-section-row__preview">{{ Str::limit($s->content, 80) }}</p>
                                                    </div>
                                                </div>
                                                <div class="dp-section-row__right">
                                                    @if($s->is_active)
                                                        <span class="dp-status-badge dp-status-badge--active">Active</span>
                                                    @else
                                                        <span class="dp-status-badge dp-status-badge--inactive">Hidden</span>
                                                    @endif
                                                    <a href="{{ route('admin.demo_prep.sections.edit', $s->id) }}"
                                                    class="at-btn-ghost" style="padding:6px 12px;font-size:12px;">
                                                        Edit
                                                    </a>
                                                    <form method="POST"
                                                        action="{{ route('admin.demo_prep.sections.destroy', $s->id) }}"
                                                        onsubmit="return confirm('Delete this section?')">
                                                        @csrf @method('DELETE')
                                                        <button type="submit" class="at-btn-danger"
                                                                style="padding:6px 12px;font-size:12px;">
                                                            Delete
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="at-form-card__body">
                                        <p style="font-size:13px;color:#9ca3af;margin:0;">
                                            No sections yet. Add your first section below.
                                        </p>
                                    </div>
                                @endif
                            </div>

                            {{-- Add / Edit section form --}}
                            <div class="at-form-card">
                                <div class="at-form-card__head">
                                    <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
                                        <path d="M8 3v10M3 8h10" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                    </svg>
                                    {{ isset($section) ? 'Edit Section' : 'Add New Section' }}
                                </div>
                                <div class="at-form-card__body">

                                    @if(isset($section))
                                        {{-- Edit form --}}
                                        <form method="POST"
                                            action="{{ route('admin.demo_prep.sections.update', $section->id) }}">
                                            @csrf @method('PUT')
                                            @include('admin.affiliates.demo_prep._section_form', ['model' => $section])
                                            <div class="d-flex gap-3 mt-4">
                                                <button type="submit" class="at-btn-primary">
                                                    <svg width="13" height="13" viewBox="0 0 16 16" fill="none">
                                                        <path d="M3 8.5l3 3 7-7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                                    </svg>
                                                    Update Section
                                                </button>
                                                <a href="{{ route('admin.demo_prep.index') }}" class="at-btn-ghost">
                                                    Cancel
                                                </a>
                                            </div>
                                        </form>
                                    @else
                                        {{-- Create form --}}
                                        <form method="POST" action="{{ route('admin.demo_prep.sections.store') }}">
                                            @csrf
                                            @include('admin.affiliates.demo_prep._section_form')
                                            <div class="mt-4">
                                                <button type="submit" class="at-btn-primary">
                                                    <svg width="13" height="13" viewBox="0 0 16 16" fill="none">
                                                        <path d="M8 3v10M3 8h10" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                                    </svg>
                                                    Add Section
                                                </button>
                                            </div>
                                        </form>
                                    @endif

                                </div>
                            </div>

                        </div>

                        {{-- ── RIGHT COLUMN ──────────────────────── --}}
                        <div class="col-lg-4">
                            <div class="at-guide-card">
                                <div class="at-guide-card__head">
                                    <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
                                        <circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.5"/>
                                        <path d="M8 7v5M8 5v.5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                                    </svg>
                                    How This Works
                                </div>
                                <div class="at-guide-card__body">
                                    <div class="at-guide-step">
                                        <div class="at-guide-step__num">1</div>
                                        <div>
                                            <p class="at-guide-step__title">Set demo credentials</p>
                                            <p class="at-guide-step__desc">The login URL, email and password are shown to affiliates on the demo prep card when a demo is scheduled.</p>
                                        </div>
                                    </div>
                                    <div class="at-guide-step">
                                        <div class="at-guide-step__num">2</div>
                                        <div>
                                            <p class="at-guide-step__title">Add prep sections</p>
                                            <p class="at-guide-step__desc">Each section appears as a collapsible block. Use them for walkthrough order, talking points, objection handling, and closing tips.</p>
                                        </div>
                                    </div>
                                    <div class="at-guide-step">
                                        <div class="at-guide-step__num">3</div>
                                        <div>
                                            <p class="at-guide-step__title">Control visibility</p>
                                            <p class="at-guide-step__desc">Toggle sections active or hidden without deleting them. Sort order controls the sequence affiliates see them in.</p>
                                        </div>
                                    </div>
                                    <div class="at-guide-tip">
                                        <svg width="12" height="12" viewBox="0 0 16 16" fill="none" style="flex-shrink:0;margin-top:1px;">
                                            <path d="M8 2l2 4h4l-3 3 1 4-4-2-4 2 1-4-3-3h4z" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                        <p>Changes take effect immediately — no cache to clear. Affiliates see updated content the next time they open a demo-scheduled lead.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('admin.affiliates.demo_prep._styles')
@endsection