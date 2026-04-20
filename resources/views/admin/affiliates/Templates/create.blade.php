@extends('admin.layouts.app')

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="page-content-wrapper bg-white p-30 radius-20">

                {{-- Page Title --}}
                @php
                    $pageTitle = 'Template Create';
                @endphp
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between border-bottom mb-4 pb-2">
                            <div class="page-title-left">
                                <h3 class="mb-sm-0">Action Templates</h3>
                            </div>
                            <div class="page-title-right">
                                <ol class="breadcrumb mb-0">
                                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                                    <li class="breadcrumb-item"><a href="{{ route('admin.templates.index') }}">Templates</a></li>
                                    <li class="breadcrumb-item active">Create</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <a href="{{ route('admin.templates.index') }}" class="at-back-link mb-4 d-inline-flex align-items-center gap-2">
                    <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
                        <path d="M10 3L5 8l5 5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Back to Templates
                </a>

                @if($errors->any())
                    <div class="at-alert at-alert--danger mb-4">
                        <svg width="15" height="15" viewBox="0 0 16 16" fill="none" style="flex-shrink:0;">
                            <circle cx="8" cy="8" r="7" stroke="currentColor" stroke-width="1.5"/>
                            <path d="M8 5v4M8 11v.5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                        </svg>
                        <div>
                            @foreach($errors->all() as $error)
                                <div>{{ $error }}</div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="row g-4">

                    {{-- Left: form --}}
                    <div class="col-lg-8">
                        <form method="POST" action="{{ route('admin.templates.store') }}" id="templateForm">
                            @csrf

                            {{-- Core fields card --}}
                            <div class="at-form-card mb-4">
                                <div class="at-form-card__head">
                                    <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
                                        <path d="M9 5H7a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2M9 5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v0a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                    </svg>
                                    Template Details
                                </div>
                                <div class="at-form-card__body">

                                    {{-- Name --}}
                                    <div class="at-field mb-4">
                                        <label class="at-label">Template Name <span class="at-required">*</span></label>
                                        <input type="text" name="name" class="at-input {{ $errors->has('name') ? 'at-input--error' : '' }}"
                                               value="{{ old('name') }}" placeholder="e.g. WhatsApp Intro — Cold Lead" required>
                                        @error('name')<p class="at-field-error">{{ $message }}</p>@enderror
                                    </div>

                                    {{-- Type + Category row --}}
                                    <div class="row g-3 mb-4">
                                        <div class="col-md-6">
                                            <div class="at-field">
                                                <label class="at-label">Action Type <span class="at-required">*</span></label>
                                                <div class="at-type-group" id="typeGroup">
                                                    @foreach([
                                                        'whatsapp' => ['label' => 'WhatsApp', 'class' => 'at-type-btn--whatsapp'],
                                                        'email'    => ['label' => 'Email',    'class' => 'at-type-btn--email'],
                                                        'call'     => ['label' => 'Call Script', 'class' => 'at-type-btn--call'],
                                                    ] as $val => $opt)
                                                        <label class="at-type-btn {{ $opt['class'] }} {{ old('action_type') === $val ? 'at-type-btn--active' : '' }}">
                                                            <input type="radio" name="action_type" value="{{ $val }}"
                                                                   {{ old('action_type') === $val ? 'checked' : '' }} required
                                                                   onchange="handleTypeChange(this)">
                                                            {{ $opt['label'] }}
                                                        </label>
                                                    @endforeach
                                                </div>
                                                @error('action_type')<p class="at-field-error">{{ $message }}</p>@enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="at-field">
                                                <label class="at-label">Category <span class="at-required">*</span></label>
                                                <select name="category" class="at-select {{ $errors->has('category') ? 'at-input--error' : '' }}" required>
                                                    <option value="">— Select category —</option>
                                                    @foreach([
                                                        'intro'     => 'Intro',
                                                        'follow_up' => 'Follow Up',
                                                        'demo_complete' => 'Demo Complete',
                                                        'reminder'  => 'Reminder',
                                                        'trial'     => 'Trial',
                                                        'trial_expired'     => 'Trial Expired',
                                                        'retention'     => 'Retention',
                                                        'reengage'  => 'Re-engage',
                                                    ] as $val => $label)
                                                        <option value="{{ $val }}" {{ old('category') === $val ? 'selected' : '' }}>{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                                @error('category')<p class="at-field-error">{{ $message }}</p>@enderror
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Message template --}}
                                    <div class="at-field">
                                        <label class="at-label">Message Template</label>
                                        <p class="at-field-hint">Use <code class="at-code">&#123;&#123;company_name&#125;&#125;</code>, <code class="at-code">&#123;&#123;affiliate_name&#125;&#125;</code>, <code class="at-code">&#123;&#123;contact_name&#125;&#125;</code> as placeholders.</p>
                                        <textarea name="message_template" class="at-textarea" rows="7"
                                                  placeholder="Hi, I'm reaching out on behalf of...">{{ old('message_template') }}</textarea>
                                        @error('message_template')<p class="at-field-error">{{ $message }}</p>@enderror
                                    </div>
                                    {{-- Placeholder reference --}}
                                    @php
                                        $placeholders = (new \App\Services\TemplateSubstitutionService())->availablePlaceholders();
                                    @endphp
                                    <div class="at-placeholder-ref mt-3">
                                        <button type="button" class="at-placeholder-ref__toggle" onclick="togglePlaceholders()">
                                            <svg width="12" height="12" viewBox="0 0 16 16" fill="none">
                                                <circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.5"/>
                                                <path d="M8 7v5M8 5v.5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                                            </svg>
                                            Available placeholders
                                            <svg width="12" height="12" viewBox="0 0 16 16" fill="none" id="placeholderChevron">
                                                <path d="M4 6l4 4 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </button>
                                        <div class="at-placeholder-ref__body" id="placeholderBody" style="display:none;">
                                            @foreach($placeholders as $group => $items)
                                                <div class="at-placeholder-group">
                                                    <p class="at-placeholder-group__label">{{ $group }}</p>
                                                    <div class="at-placeholder-chips">
                                                        @foreach($items as $tag => $description)
                                                            <button type="button"
                                                                    class="at-placeholder-chip"
                                                                    title="{{ $description }}"
                                                                    onclick="insertPlaceholder('{{ $tag }}')">
                                                                {{ $tag }}
                                                            </button>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endforeach
                                            <p class="at-placeholder-ref__hint">Click any placeholder to insert it at the cursor position in your message.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Materials card --}}
                            @if($materials->count() > 0)
                            <div class="at-form-card mb-4">
                                <div class="at-form-card__head">
                                    <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
                                        <path d="M4 2h6l4 4v8H4V2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M10 2v4h4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                    </svg>
                                    Attach Materials
                                    <span class="at-form-card__hint">Optional — files attached here will be available to affiliates when using this template</span>
                                </div>
                                <div class="at-form-card__body">
                                    <div class="at-materials-grid">
                                        @foreach($materials as $m)
                                            <label class="at-material-item">
                                                <input type="checkbox" name="material_ids[]" value="{{ $m->id }}"
                                                       {{ in_array($m->id, old('material_ids', [])) ? 'checked' : '' }}>
                                                <div class="at-material-info">
                                                    <svg width="13" height="13" viewBox="0 0 16 16" fill="none" style="flex-shrink:0;color:#9ca3af;">
                                                        <path d="M4 2h6l4 4v8H4V2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                        <path d="M10 2v4h4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                                    </svg>
                                                    <span>{{ $m->title }}</span>
                                                </div>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            @endif

                            {{-- Submit --}}
                            <div class="d-flex align-items-center gap-3">
                                <button type="submit" class="at-btn-primary">
                                    <svg width="13" height="13" viewBox="0 0 16 16" fill="none">
                                        <path d="M3 8.5l3 3 7-7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    Create Template
                                </button>
                                <a href="{{ route('admin.templates.index') }}" class="at-btn-ghost">Cancel</a>
                            </div>

                        </form>
                    </div>

                    {{-- Right: guidance --}}
                    <div class="col-lg-4">
                        <div class="at-guide-card">
                            <div class="at-guide-card__head">
                                <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
                                    <circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.5"/>
                                    <path d="M8 7v5M8 5v.5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                                </svg>
                                How Templates Work
                            </div>
                            <div class="at-guide-card__body">
                                <div class="at-guide-step">
                                    <div class="at-guide-step__num">1</div>
                                    <div>
                                        <p class="at-guide-step__title">Choose a type and category</p>
                                        <p class="at-guide-step__desc">The suggestion engine matches templates to leads based on their status and temperature. Category determines when a template appears.</p>
                                    </div>
                                </div>
                                <div class="at-guide-step">
                                    <div class="at-guide-step__num">2</div>
                                    <div>
                                        <p class="at-guide-step__title">Write your message</p>
                                        <p class="at-guide-step__desc">Use placeholders like <code class="at-code">&#123;&#123;company_name&#125;&#125;</code> — they'll be filled in automatically when an affiliate uses the template.</p>
                                    </div>
                                </div>
                                <div class="at-guide-step">
                                    <div class="at-guide-step__num">3</div>
                                    <div>
                                        <p class="at-guide-step__title">Attach supporting materials</p>
                                        <p class="at-guide-step__desc">Brochures, pitch decks, or feature guides can be attached and will appear alongside the template for affiliates to share.</p>
                                    </div>
                                </div>

                                <div class="at-guide-tip">
                                    <svg width="12" height="12" viewBox="0 0 16 16" fill="none" style="flex-shrink:0;margin-top:1px;">
                                        <path d="M8 2l2 4h4l-3 3 1 4-4-2-4 2 1-4-3-3h4z" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    <p>Multiple templates per category are supported — affiliates will be shown a dropdown to choose between them.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </div>
</div>

@include('admin.affiliates.templates._form_styles')

<script>
    function handleTypeChange(input) {
        document.querySelectorAll('.at-type-btn').forEach(btn => btn.classList.remove('at-type-btn--active'));
        input.closest('.at-type-btn').classList.add('at-type-btn--active');
    }
    
    function togglePlaceholders() {
        const body     = document.getElementById('placeholderBody');
        const chevron  = document.getElementById('placeholderChevron');
        const isOpen   = body.style.display !== 'none';
        body.style.display = isOpen ? 'none' : 'block';
        chevron.style.transform = isOpen ? '' : 'rotate(180deg)';
        }

    function insertPlaceholder(tag) {
        const textarea = document.querySelector('textarea[name="message_template"]');
        if (!textarea) return;

        const start = textarea.selectionStart;
        const end   = textarea.selectionEnd;
        const value = textarea.value;

        textarea.value = value.slice(0, start) + tag + value.slice(end);

        // Move cursor to after the inserted tag
        const newPos = start + tag.length;
        textarea.setSelectionRange(newPos, newPos);
        textarea.focus();
    }
</script>
@endsection