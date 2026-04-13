@extends('admin.layouts.app')

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="page-content-wrapper bg-white p-30 radius-20">

                {{-- Page Title --}}
                @php
                    $pageTitle = 'Template Edit';
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
                                    <li class="breadcrumb-item active">Edit</li>
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

                @if(session('success'))
                    <div class="at-alert at-alert--success mb-4">
                        <svg width="15" height="15" viewBox="0 0 16 16" fill="none" style="flex-shrink:0;">
                            <circle cx="8" cy="8" r="7" stroke="currentColor" stroke-width="1.5"/>
                            <path d="M4.5 8.5l2.5 2.5 4.5-5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        {{ session('success') }}
                    </div>
                @endif

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
                        <form method="POST" action="{{ route('admin.templates.update', $template->id) }}">
                            @csrf
                            @method('PUT')

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
                                               value="{{ old('name', $template->name) }}" required>
                                        @error('name')<p class="at-field-error">{{ $message }}</p>@enderror
                                    </div>

                                    {{-- Type + Category row --}}
                                    <div class="row g-3 mb-4">
                                        <div class="col-md-6">
                                            <div class="at-field">
                                                <label class="at-label">Action Type <span class="at-required">*</span></label>
                                                <div class="at-type-group">
                                                    @foreach([
                                                        'whatsapp' => ['label' => 'WhatsApp',    'class' => 'at-type-btn--whatsapp'],
                                                        'email'    => ['label' => 'Email',        'class' => 'at-type-btn--email'],
                                                        'call'     => ['label' => 'Call Script',  'class' => 'at-type-btn--call'],
                                                    ] as $val => $opt)
                                                        @php $selected = old('action_type', $template->action_type) === $val; @endphp
                                                        <label class="at-type-btn {{ $opt['class'] }} {{ $selected ? 'at-type-btn--active' : '' }}">
                                                            <input type="radio" name="action_type" value="{{ $val }}"
                                                                   {{ $selected ? 'checked' : '' }} required
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
                                                    @foreach([
                                                        'intro'     => 'Intro',
                                                        'follow_up' => 'Follow Up',
                                                        'demo_complete' => 'Demo Complete',
                                                        'reminder'  => 'Reminder',
                                                        'trial'     => 'Trial',
                                                        'retention'     => 'Retention',
                                                        'trial_expired'     => 'Trial_expired',
                                                        'reengage'  => 'Re-engage',
                                                    ] as $val => $label)
                                                        <option value="{{ $val }}" {{ old('category', $template->category) === $val ? 'selected' : '' }}>{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                                @error('category')<p class="at-field-error">{{ $message }}</p>@enderror
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Message --}}
                                    <div class="at-field">
                                        <label class="at-label">Message Template</label>
                                        <p class="at-field-hint">Use <code class="at-code">&#123;&#123;company_name&#125;&#125;</code>, <code class="at-code">&#123;&#123;affiliate_name&#125;&#125;</code>, <code class="at-code">&#123;&#123;contact_name&#125;&#125;</code> as placeholders.</p>
                                        <textarea name="message_template" class="at-textarea" rows="7">{{ old('message_template', $template->message_template) }}</textarea>
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
                                    <span class="at-form-card__hint">Optional</span>
                                </div>
                                <div class="at-form-card__body">
                                    <div class="at-materials-grid">
                                        @foreach($materials as $m)
                                            <label class="at-material-item">
                                                <input type="checkbox" name="material_ids[]" value="{{ $m->id }}"
                                                       {{ $template->materials->contains($m->id) ? 'checked' : '' }}>
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

                            {{-- Submit + danger zone --}}
                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                                <div class="d-flex align-items-center gap-3">
                                    <!-- Update form -->
                                    <form method="POST" action="{{ route('admin.templates.update', $template->id) }}">
                                        @csrf
                                        @method('PUT')
                                        <button type="submit" class="at-btn-primary">
                                            <svg width="13" height="13" viewBox="0 0 16 16" fill="none">
                                                <path d="M3 8.5l3 3 7-7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                            Save Changes
                                        </button>
                                    </form>

                                    <a href="{{ route('admin.templates.index') }}" class="at-btn-ghost">Cancel</a>
                                </div>

                                <!-- Delete form -->
                                <form method="POST" action="{{ route('admin.templates.destroy', $template->id) }}"
                                    onsubmit="return confirm('Delete this template permanently?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="at-btn-danger">
                                        <svg width="13" height="13" viewBox="0 0 16 16" fill="none">
                                            <path d="M3 4h10M5 4V3a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v1M6 7v5M10 7v5M4 4l1 9h6l1-9"
                                                stroke="currentColor" stroke-width="1.5"
                                                stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                        Delete Template
                                    </button>
                                </form>
                            </div>


                        </form>
                    </div>

                    {{-- Right: meta info --}}
                    <div class="col-lg-4">
                        <div class="at-guide-card mb-4">
                            <div class="at-guide-card__head">
                                <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
                                    <circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.5"/>
                                    <path d="M8 7v5M8 5v.5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                                </svg>
                                Template Info
                            </div>
                            <div class="at-guide-card__body">
                                <div class="at-meta-row">
                                    <span class="at-meta-label">Created</span>
                                    <span class="at-meta-val">{{ $template->created_at->format('d M Y') }}</span>
                                </div>
                                <div class="at-meta-row">
                                    <span class="at-meta-label">Last updated</span>
                                    <span class="at-meta-val">{{ $template->updated_at->diffForHumans() }}</span>
                                </div>
                                <div class="at-meta-row">
                                    <span class="at-meta-label">Type</span>
                                    <span class="at-meta-val">{{ ucfirst($template->action_type) }}</span>
                                </div>
                                <div class="at-meta-row">
                                    <span class="at-meta-label">Category</span>
                                    <span class="at-meta-val">{{ ucfirst(str_replace('_', ' ', $template->category)) }}</span>
                                </div>
                                @if($template->materials->count() > 0)
                                    <div class="at-meta-row" style="border-bottom:none;">
                                        <span class="at-meta-label">Materials</span>
                                        <span class="at-meta-val">{{ $template->materials->count() }} attached</span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="at-guide-tip" style="border-radius:10px;padding:12px 14px;">
                            <svg width="12" height="12" viewBox="0 0 16 16" fill="none" style="flex-shrink:0;margin-top:1px;">
                                <path d="M8 2l6 12H2L8 2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M8 7v3M8 12v.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            </svg>
                            <p>Editing this template will affect future suggestions only — existing pending suggestions are not updated retroactively.</p>
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
</script>
@endsection