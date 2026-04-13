@extends('admin.layouts.app')

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="page-content-wrapper bg-white p-30 radius-20">

                {{-- Page Title --}}
                @php
                    $pageTitle = 'Material Create';
                @endphp
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between border-bottom mb-4 pb-2">
                            <div class="page-title-left">
                                <h3 class="mb-sm-0">Marketing Materials</h3>
                            </div>
                            <div class="page-title-right">
                                <ol class="breadcrumb mb-0">
                                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                                    <li class="breadcrumb-item"><a href="{{ route('admin.materials.index') }}">Materials</a></li>
                                    <li class="breadcrumb-item active">Create</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <a href="{{ route('admin.materials.index') }}" class="at-back-link mb-4 d-inline-flex align-items-center gap-2">
                    <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
                        <path d="M10 3L5 8l5 5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Back to Materials
                </a>

                @if($errors->any())
                    <div class="at-alert at-alert--danger mb-4">
                        <svg width="15" height="15" viewBox="0 0 16 16" fill="none" style="flex-shrink:0;">
                            <circle cx="8" cy="8" r="7" stroke="currentColor" stroke-width="1.5"/>
                            <path d="M8 5v4M8 11v.5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                        </svg>
                        <div>@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
                    </div>
                @endif

                <div class="row g-4">

                    {{-- Left: form --}}
                    <div class="col-lg-8">
                        <form method="POST" action="{{ route('admin.materials.store') }}" enctype="multipart/form-data">
                            @csrf

                            {{-- Core details --}}
                            <div class="at-form-card mb-4">
                                <div class="at-form-card__head">
                                    <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
                                        <path d="M4 2h6l4 4v8H4V2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M10 2v4h4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                    </svg>
                                    Material Details
                                </div>
                                <div class="at-form-card__body">

                                    {{-- Title --}}
                                    <div class="at-field mb-4">
                                        <label class="at-label">Title <span class="at-required">*</span></label>
                                        <input type="text" name="title" class="at-input {{ $errors->has('title') ? 'at-input--error' : '' }}"
                                               value="{{ old('title') }}" placeholder="e.g. Property Management Brochure 2025" required>
                                        @error('title')<p class="at-field-error">{{ $message }}</p>@enderror
                                    </div>

                                    {{-- Type toggle --}}
                                    <div class="at-field mb-4">
                                        <label class="at-label">Material Type <span class="at-required">*</span></label>
                                        <div class="at-type-group" id="typeGroup">
                                            @foreach([
                                                'pdf'  => ['label' => 'PDF',   'class' => 'at-type-btn--pdf'],
                                                'png'  => ['label' => 'Image', 'class' => 'at-type-btn--image'],
                                                'link' => ['label' => 'Link',  'class' => 'at-type-btn--link'],
                                                'text' => ['label' => 'Text',  'class' => 'at-type-btn--text'],
                                            ] as $val => $opt)
                                                <label class="at-type-btn {{ $opt['class'] }} {{ old('type') === $val ? 'at-type-btn--active' : '' }}">
                                                    <input type="radio" name="type" value="{{ $val }}"
                                                           {{ old('type') === $val ? 'checked' : '' }} required
                                                           onchange="handleMaterialType(this)">
                                                    {{ $opt['label'] }}
                                                </label>
                                            @endforeach
                                        </div>
                                        @error('type')<p class="at-field-error">{{ $message }}</p>@enderror
                                    </div>

                                    {{-- Category + Priority row --}}
                                    <div class="row g-3 mb-4">
                                        <div class="col-md-8">
                                            <div class="at-field">
                                                <label class="at-label">Category</label>
                                                <p class="at-field-hint">Used to match materials with lead temperature or stage.</p>
                                                <input type="text" name="category" class="at-input"
                                                       value="{{ old('category') }}" placeholder="e.g. cold, warm, hot, intro, trial">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="at-field">
                                                <label class="at-label">Priority</label>
                                                <p class="at-field-hint">Lower = shown first.</p>
                                                <input type="number" name="priority" class="at-input"
                                                       value="{{ old('priority', 1) }}" min="1" max="10">
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Active toggle --}}
                                    <div class="at-field">
                                        <label class="mm-toggle-label">
                                            <input type="checkbox" name="is_active" value="1"
                                                   {{ old('is_active', true) ? 'checked' : '' }}
                                                   class="mm-toggle-input">
                                            <span class="mm-toggle-track">
                                                <span class="mm-toggle-thumb"></span>
                                            </span>
                                            <span class="mm-toggle-text">Active — visible to affiliates via templates</span>
                                        </label>
                                    </div>

                                </div>
                            </div>

                            {{-- Dynamic content card --}}
                            <div class="at-form-card mb-4" id="contentCard" style="display:none;">
                                <div class="at-form-card__head" id="contentCardHead">
                                    <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
                                        <path d="M2 2h12v10H9l-3 2v-2H2V2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    Content
                                </div>
                                <div class="at-form-card__body">

                                    {{-- Text / Link field --}}
                                    <div id="contentField" style="display:none;">
                                        <div class="at-field">
                                            <label class="at-label" id="contentLabel">Content</label>
                                            <textarea name="content" class="at-textarea" rows="5"
                                                      id="contentArea" placeholder="">{{ old('content') }}</textarea>
                                            @error('content')<p class="at-field-error">{{ $message }}</p>@enderror
                                        </div>
                                    </div>

                                    {{-- File upload --}}
                                    <div id="fileField" style="display:none;">
                                        <div class="at-field">
                                            <label class="at-label" id="fileLabel">Upload File <span class="at-required">*</span></label>
                                            <div class="mm-upload-zone" id="uploadZone"
                                                 onclick="document.getElementById('fileInput').click()">
                                                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" style="color:#9ca3af;margin-bottom:8px;">
                                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                                    <path d="M17 8l-5-5-5 5M12 3v12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                                <p class="mm-upload-zone__text" id="uploadText">Click to upload or drag and drop</p>
                                                <p class="mm-upload-zone__hint" id="uploadHint">PDF up to 20MB</p>
                                                <input type="file" name="file" id="fileInput" style="display:none;"
                                                       onchange="handleFileSelect(this)">
                                            </div>
                                            @error('file')<p class="at-field-error">{{ $message }}</p>@enderror
                                        </div>
                                    </div>

                                </div>
                            </div>

                            {{-- Submit --}}
                            <div class="d-flex align-items-center gap-3">
                                <button type="submit" class="at-btn-primary">
                                    <svg width="13" height="13" viewBox="0 0 16 16" fill="none">
                                        <path d="M3 8.5l3 3 7-7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    Create Material
                                </button>
                                <a href="{{ route('admin.materials.index') }}" class="at-btn-ghost">Cancel</a>
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
                                Material Types
                            </div>
                            <div class="at-guide-card__body">
                                <div class="at-guide-step">
                                    <div class="at-guide-step__num mm-guide-num--pdf">P</div>
                                    <div>
                                        <p class="at-guide-step__title">PDF</p>
                                        <p class="at-guide-step__desc">Brochures, pitch decks, feature sheets. Affiliates can download and forward to prospects.</p>
                                    </div>
                                </div>
                                <div class="at-guide-step">
                                    <div class="at-guide-step__num mm-guide-num--image">I</div>
                                    <div>
                                        <p class="at-guide-step__title">Image (PNG)</p>
                                        <p class="at-guide-step__desc">Infographics, banners, or visual aids affiliates can share via WhatsApp or email.</p>
                                    </div>
                                </div>
                                <div class="at-guide-step">
                                    <div class="at-guide-step__num mm-guide-num--link">L</div>
                                    <div>
                                        <p class="at-guide-step__title">Link</p>
                                        <p class="at-guide-step__desc">YouTube demos, landing pages, or case study URLs pre-filled into affiliate messages.</p>
                                    </div>
                                </div>
                                <div class="at-guide-step">
                                    <div class="at-guide-step__num mm-guide-num--text">T</div>
                                    <div>
                                        <p class="at-guide-step__title">Text</p>
                                        <p class="at-guide-step__desc">Talking points, objection handlers, or scripts affiliates can read during a call.</p>
                                    </div>
                                </div>
                                <div class="at-guide-tip">
                                    <svg width="12" height="12" viewBox="0 0 16 16" fill="none" style="flex-shrink:0;margin-top:1px;">
                                        <path d="M8 2l2 4h4l-3 3 1 4-4-2-4 2 1-4-3-3h4z" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    <p>Priority 1 materials appear first when multiple materials are attached to a template. Use lower numbers for your most important assets.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

@include('admin.affiliates.materials._material_styles')

<script>
    // Restore type selection after validation failure
    const oldType = '{{ old('type') }}';
    if (oldType) { handleMaterialType({ value: oldType }); }

    function handleMaterialType(input) {
        const type = input.value || input;

        // Update active button state
        document.querySelectorAll('.at-type-btn').forEach(btn => btn.classList.remove('at-type-btn--active'));
        if (input.closest) input.closest('.at-type-btn').classList.add('at-type-btn--active');

        const contentCard = document.getElementById('contentCard');
        const contentField = document.getElementById('contentField');
        const fileField = document.getElementById('fileField');
        const contentLabel = document.getElementById('contentLabel');
        const contentArea = document.getElementById('contentArea');
        const uploadHint = document.getElementById('uploadHint');
        const fileInput = document.getElementById('fileInput');
        const cardHead = document.getElementById('contentCardHead');

        contentCard.style.display = 'block';
        contentField.style.display = 'none';
        fileField.style.display = 'none';

        if (type === 'text') {
            contentField.style.display = 'block';
            contentLabel.textContent = 'Text Content';
            contentArea.placeholder = 'Enter talking points, scripts, or supporting text...';
            cardHead.childNodes[cardHead.childNodes.length - 1].textContent = ' Text Content';
        } else if (type === 'link') {
            contentField.style.display = 'block';
            contentLabel.textContent = 'URL';
            contentArea.placeholder = 'https://...';
            cardHead.childNodes[cardHead.childNodes.length - 1].textContent = ' Link URL';
        } else if (type === 'pdf') {
            fileField.style.display = 'block';
            uploadHint.textContent = 'PDF up to 20MB';
            fileInput.accept = '.pdf';
            cardHead.childNodes[cardHead.childNodes.length - 1].textContent = ' PDF Upload';
        } else if (type === 'png') {
            fileField.style.display = 'block';
            uploadHint.textContent = 'PNG, JPG up to 10MB';
            fileInput.accept = '.png,.jpg,.jpeg,.webp';
            cardHead.childNodes[cardHead.childNodes.length - 1].textContent = ' Image Upload';
        }
    }

    function handleFileSelect(input) {
        if (input.files && input.files[0]) {
            const file = input.files[0];
            document.getElementById('uploadText').textContent = file.name;
            document.getElementById('uploadHint').textContent = (file.size / 1024 / 1024).toFixed(2) + ' MB';
            document.getElementById('uploadZone').style.borderColor = '#185FA5';
            document.getElementById('uploadZone').style.background = '#EEF5FD';
        }
    }
</script>
@endsection