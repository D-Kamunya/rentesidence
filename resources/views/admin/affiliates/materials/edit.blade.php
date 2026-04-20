@extends('admin.layouts.app')

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="page-content-wrapper bg-white p-30 radius-20">

                {{-- Page Title --}}
                @php
                    $pageTitle = 'Material Edit';
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
                                    <li class="breadcrumb-item active">Edit</li>
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
                        <div>@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
                    </div>
                @endif

                <div class="row g-4">

                    {{-- Left: form --}}
                    <div class="col-lg-8">
                        <form method="POST" action="{{ route('admin.materials.update', $material->id) }}" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

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
                                               value="{{ old('title', $material->title) }}" required>
                                        @error('title')<p class="at-field-error">{{ $message }}</p>@enderror
                                    </div>

                                    {{-- Type toggle --}}
                                    <div class="at-field mb-4">
                                        <label class="at-label">Material Type <span class="at-required">*</span></label>
                                        <div class="at-type-group">
                                            @foreach([
                                                'pdf'  => ['label' => 'PDF',   'class' => 'at-type-btn--pdf'],
                                                'png'  => ['label' => 'Image', 'class' => 'at-type-btn--image'],
                                                'link' => ['label' => 'Link',  'class' => 'at-type-btn--link'],
                                                'text' => ['label' => 'Text',  'class' => 'at-type-btn--text'],
                                            ] as $val => $opt)
                                                @php $selected = old('type', $material->type) === $val; @endphp
                                                <label class="at-type-btn {{ $opt['class'] }} {{ $selected ? 'at-type-btn--active' : '' }}">
                                                    <input type="radio" name="type" value="{{ $val }}"
                                                           {{ $selected ? 'checked' : '' }} required
                                                           onchange="handleMaterialType(this)">
                                                    {{ $opt['label'] }}
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>

                                    {{-- Category + Priority --}}
                                    <div class="row g-3 mb-4">
                                        <div class="col-md-8">
                                            <div class="at-field">
                                                <label class="at-label">Category</label>
                                                <input type="text" name="category" class="at-input"
                                                       value="{{ old('category', $material->category) }}"
                                                       placeholder="e.g. cold, warm, hot, intro, trial">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="at-field">
                                                <label class="at-label">Priority</label>
                                                <input type="number" name="priority" class="at-input"
                                                       value="{{ old('priority', $material->priority) }}" min="1" max="10">
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Active toggle --}}
                                    <div class="at-field">
                                        <label class="mm-toggle-label">
                                            <input type="checkbox" name="is_active" value="1"
                                                   {{ old('is_active', $material->is_active) ? 'checked' : '' }}
                                                   class="mm-toggle-input">
                                            <span class="mm-toggle-track">
                                                <span class="mm-toggle-thumb"></span>
                                            </span>
                                            <span class="mm-toggle-text">Active — visible to affiliates via templates</span>
                                        </label>
                                    </div>

                                </div>
                            </div>

                            {{-- Content card (always visible on edit) --}}
                            <div class="at-form-card mb-4" id="contentCard">
                                <div class="at-form-card__head" id="contentCardHead">
                                    <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
                                        <path d="M2 2h12v10H9l-3 2v-2H2V2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    Content
                                </div>
                                <div class="at-form-card__body">

                                    {{-- Text / Link --}}
                                    <div id="contentField" style="{{ in_array($material->type, ['text','link']) ? '' : 'display:none;' }}">
                                        <div class="at-field">
                                            <label class="at-label" id="contentLabel">
                                                {{ $material->type === 'link' ? 'URL' : 'Text Content' }}
                                            </label>
                                            <textarea name="content" class="at-textarea" rows="5"
                                                      id="contentArea">{{ old('content', $material->content) }}</textarea>
                                        </div>
                                    </div>

                                    {{-- File --}}
                                    <div id="fileField" style="{{ in_array($material->type, ['pdf','png']) ? '' : 'display:none;' }}">
                                        <div class="at-field mb-3">
                                            <label class="at-label" id="fileLabel">Upload New File <span style="font-weight:400;color:#9ca3af;">(optional — leave blank to keep current)</span></label>
                                            <div class="mm-upload-zone" id="uploadZone"
                                                 onclick="document.getElementById('fileInput').click()">
                                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" style="color:#9ca3af;margin-bottom:6px;">
                                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                                    <path d="M17 8l-5-5-5 5M12 3v12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                                <p class="mm-upload-zone__text" id="uploadText">Click to choose a new file</p>
                                                <p class="mm-upload-zone__hint" id="uploadHint">
                                                    {{ $material->type === 'png' ? 'PNG, JPG up to 10MB' : 'PDF up to 20MB' }}
                                                </p>
                                                <input type="file" name="file" id="fileInput" style="display:none;"
                                                       accept="{{ $material->type === 'png' ? '.png,.jpg,.jpeg,.webp' : '.pdf' }}"
                                                       onchange="handleFileSelect(this)">
                                            </div>
                                        </div>

                                        {{-- Current file preview --}}
                                        @if($material->file_path)
                                            <div class="mm-current-file">
                                                @if($material->type === 'png')
                                                    <img src="{{ asset('storage/'.$material->file_path) }}"
                                                         alt="{{ $material->title }}" class="mm-current-image">
                                                @else
                                                    <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
                                                        <path d="M4 2h6l4 4v8H4V2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                        <path d="M10 2v4h4M6 9h4M6 11.5h2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                                    </svg>
                                                @endif
                                                <div class="mm-current-file__info">
                                                    <span class="mm-current-file__name">{{ $material->file_name ?? basename($material->file_path) }}</span>
                                                    <a href="{{ asset('storage/'.$material->file_path) }}" target="_blank" class="mm-current-file__link">
                                                        View current file
                                                        <svg width="10" height="10" viewBox="0 0 16 16" fill="none">
                                                            <path d="M7 3H3v10h10v-4M9 1h6v6M15 1l-8 8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                        </svg>
                                                    </a>
                                                </div>
                                            </div>
                                        @endif
                                    </div>

                                </div>
                            </div>

                            {{-- Submit + delete --}}
                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                                <div class="d-flex align-items-center gap-3">
                                    <form method="POST" action="{{ route('admin.materials.update', $material->id) }}">
                                        @csrf
                                        @method('PUT')
                                        <button type="submit" class="at-btn-primary">
                                            <svg width="13" height="13" viewBox="0 0 16 16" fill="none">
                                                <path d="M3 8.5l3 3 7-7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                            Save Changes
                                        </button>
                                    </form>

                                    <a href="{{ route('admin.materials.index') }}" class="at-btn-ghost">Cancel</a>
                                </div>
                                <form method="POST" action="{{ route('admin.materials.destroy', $material->id) }}"
                                      onsubmit="return confirm('Delete \'{{ addslashes($material->title) }}\'? It will be detached from all templates.')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="at-btn-danger">
                                        <svg width="13" height="13" viewBox="0 0 16 16" fill="none">
                                            <path d="M3 4h10M5 4V3a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v1M6 7v5M10 7v5M4 4l1 9h6l1-9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                        Delete Material
                                    </button>
                                </form>
                            </div>

                        </form>
                    </div>

                    {{-- Right: meta --}}
                    <div class="col-lg-4">
                        <div class="at-guide-card mb-4">
                            <div class="at-guide-card__head">
                                <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
                                    <circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.5"/>
                                    <path d="M8 7v5M8 5v.5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                                </svg>
                                Material Info
                            </div>
                            <div class="at-guide-card__body" style="gap:0;">
                                <div class="at-meta-row">
                                    <span class="at-meta-label">Created</span>
                                    <span class="at-meta-val">{{ $material->created_at->format('d M Y') }}</span>
                                </div>
                                <div class="at-meta-row">
                                    <span class="at-meta-label">Last updated</span>
                                    <span class="at-meta-val">{{ $material->updated_at->diffForHumans() }}</span>
                                </div>
                                <div class="at-meta-row">
                                    <span class="at-meta-label">Type</span>
                                    <span class="at-meta-val">{{ strtoupper($material->type) }}</span>
                                </div>
                                <div class="at-meta-row">
                                    <span class="at-meta-label">Used in</span>
                                    <span class="at-meta-val">{{ $material->usage_count ?? 0 }} {{ Str::plural('template', $material->usage_count ?? 0) }}</span>
                                </div>
                                <div class="at-meta-row" style="border-bottom:none;">
                                    <span class="at-meta-label">Status</span>
                                    <span class="at-meta-val">
                                        @if($material->is_active)
                                            <span class="at-badge at-badge--active" style="font-size:11px;">Active</span>
                                        @else
                                            <span class="at-badge at-badge--inactive" style="font-size:11px;">Inactive</span>
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>

                        @if(($material->usage_count ?? 0) > 0)
                            <div class="at-guide-tip" style="border-radius:10px;padding:12px 14px;">
                                <svg width="12" height="12" viewBox="0 0 16 16" fill="none" style="flex-shrink:0;margin-top:1px;">
                                    <circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.5"/>
                                    <path d="M8 7v5M8 5v.5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                                </svg>
                                <p>This material is attached to {{ $material->usage_count }} {{ Str::plural('template', $material->usage_count) }}. Changes will take effect immediately for all of them.</p>
                            </div>
                        @endif
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

@include('admin.affiliates.materials._material_styles')

<script>
    // Initialize correct state on page load
    handleMaterialType({ value: '{{ old('type', $material->type) }}', closest: () => null });

    function handleMaterialType(input) {
        const type = typeof input === 'string' ? input : input.value;

        document.querySelectorAll('.at-type-btn').forEach(btn => btn.classList.remove('at-type-btn--active'));
        if (input.closest && input.closest('.at-type-btn')) {
            input.closest('.at-type-btn').classList.add('at-type-btn--active');
        } else {
            // Page load — activate matching button
            document.querySelectorAll('.at-type-btn input[type=radio]').forEach(radio => {
                if (radio.value === type) radio.closest('.at-type-btn').classList.add('at-type-btn--active');
            });
        }

        const contentField = document.getElementById('contentField');
        const fileField    = document.getElementById('fileField');
        const contentLabel = document.getElementById('contentLabel');
        const contentArea  = document.getElementById('contentArea');
        const uploadHint   = document.getElementById('uploadHint');
        const fileInput    = document.getElementById('fileInput');

        contentField.style.display = 'none';
        fileField.style.display    = 'none';

        if (type === 'text') {
            contentField.style.display = 'block';
            if (contentLabel) contentLabel.textContent = 'Text Content';
            if (contentArea)  contentArea.placeholder  = 'Enter talking points, scripts, or supporting text...';
        } else if (type === 'link') {
            contentField.style.display = 'block';
            if (contentLabel) contentLabel.textContent = 'URL';
            if (contentArea)  contentArea.placeholder  = 'https://...';
        } else if (type === 'pdf') {
            fileField.style.display = 'block';
            if (uploadHint) uploadHint.textContent = 'PDF up to 20MB';
            if (fileInput)  fileInput.accept = '.pdf';
        } else if (type === 'png') {
            fileField.style.display = 'block';
            if (uploadHint) uploadHint.textContent = 'PNG, JPG up to 10MB';
            if (fileInput)  fileInput.accept = '.png,.jpg,.jpeg,.webp';
        }
    }

    function handleFileSelect(input) {
        if (input.files && input.files[0]) {
            const file = input.files[0];
            document.getElementById('uploadText').textContent = file.name;
            document.getElementById('uploadHint').textContent = (file.size / 1024 / 1024).toFixed(2) + ' MB';
            document.getElementById('uploadZone').style.borderColor = '#185FA5';
            document.getElementById('uploadZone').style.background  = '#EEF5FD';
        }
    }
</script>
@endsection