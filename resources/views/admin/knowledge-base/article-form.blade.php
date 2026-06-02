@extends('admin.layouts.app')

@section('title', isset($article) ? 'Edit Article' : 'Create Article')

@php
    // Set active navigation classes for sidebar
    $navKBArticlesMMShowClass = 'mm-active';
    
    // Determine form action and method
    $isEdit = isset($article);
    $formAction = $isEdit 
        ? route('admin.kb.articles.update', $article) 
        : route('admin.kb.articles.store');
    $formMethod = $isEdit ? 'PUT' : 'POST';
@endphp

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="page-content-wrapper bg-white p-30 radius-20">
                {{-- Page Header --}}
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-flex align-items-center justify-content-between">
                            <div>
                                <h4 class="mb-0" style="font-size: 22px; font-weight: 500; color: #111827;">
                                    {{ $isEdit ? 'Edit Article' : 'Create New Article' }}
                                </h4>
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb m-0" style="display: flex; gap: 6px; font-size: 12px; color: #9ca3af; list-style: none; padding: 0;">
                                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" style="color: #185FA5; font-weight: 500;">Dashboard</a></li>
                                        <li class="breadcrumb-item"><a href="{{ route('admin.kb.articles') }}" style="color: #185FA5; font-weight: 500;">Knowledge Base</a></li>
                                        <li class="breadcrumb-item active" aria-current="page" style="color: #9ca3af;">
                                            {{ $isEdit ? 'Edit Article' : 'Create Article' }}
                                        </li>
                                    </ol>
                                </nav>
                            </div>
                            <div>
                                <a href="{{ route('admin.kb.articles') }}" class="btn btn-ghost" 
                                style="background: #f3f4f6; color: #374151; display: inline-flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 500; padding: 7px 15px; border-radius: 7px; border: 0.5px solid #e5e7eb; cursor: pointer; text-decoration: none; transition: all .13s;"
                                onmouseover="this.style.background='#e5e7eb';" 
                                onmouseout="this.style.background='#f3f4f6';">
                                    <i class="fa fa-arrow-left"></i>
                                    <span>Back to Articles</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Article Form --}}
                <div class="row">
                    <div class="col-12">
                        <form action="{{ $formAction }}" method="POST" enctype="multipart/form-data" id="articleForm">
                            @csrf
                            @if($isEdit)
                                @method('PUT')
                            @endif
                            
                            <div class="row">
                                {{-- Main Content Column --}}
                                <div class="col-lg-8">
                                    {{-- Basic Information Card --}}
                                    <div class="card mb-4" style="background: #fff; border: 0.5px solid #e5e7eb; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.04), 0 0 0 1px rgba(24,95,165,0.05), 0 6px 18px rgba(24,95,165,0.06);">
                                        <div class="card-header" style="background: #fafafa; border-bottom: 0.5px solid #e5e7eb; padding: 12px 20px;">
                                            <h5 style="font-size: 14px; font-weight: 600; color: #111827; margin: 0;">Basic Information</h5>
                                        </div>
                                        <div class="card-body" style="padding: 20px;">
                                            {{-- Title --}}
                                            <div class="mb-3">
                                                <label for="title" style="font-size: 10px; font-weight: 500; text-transform: uppercase; letter-spacing: .07em; color: #9ca3af; display: block; margin-bottom: 6px;">
                                                    Title <span style="color: #993C1D;">*</span>
                                                </label>
                                                <input type="text" 
                                                    name="title" 
                                                    id="title" 
                                                    value="{{ old('title', $article->title ?? '') }}" 
                                                    required
                                                    style="width: 100%; border: 0.5px solid #e5e7eb; border-radius: 7px; padding: 8px 12px; font-size: 13px; color: #374151; transition: all .15s;"
                                                    onfocus="this.style.borderColor='#185FA5'; this.style.boxShadow='0 0 0 3px rgba(24,95,165,.1)';"
                                                    onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none';"
                                                    placeholder="Enter article title">
                                                @error('title')
                                                    <div style="font-size: 11px; color: #993C1D; margin-top: 4px;">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            {{-- Slug --}}
                                            <div class="mb-3">
                                                <label for="slug" style="font-size: 10px; font-weight: 500; text-transform: uppercase; letter-spacing: .07em; color: #9ca3af; display: block; margin-bottom: 6px;">
                                                    Slug
                                                </label>
                                                <input type="text" 
                                                    name="slug" 
                                                    id="slug" 
                                                    value="{{ old('slug', $article->slug ?? '') }}"
                                                    style="width: 100%; border: 0.5px solid #e5e7eb; border-radius: 7px; padding: 8px 12px; font-size: 13px; color: #374151; background: #fafafa; transition: all .15s;"
                                                    placeholder="Auto-generated if left empty"
                                                    readonly>
                                                <div style="font-size: 11px; color: #9ca3af; margin-top: 4px;">
                                                    Auto-generated from title. You can edit it manually if needed.
                                                </div>
                                            </div>

                                            {{-- Excerpt --}}
                                            <div class="mb-3">
                                                <label for="excerpt" style="font-size: 10px; font-weight: 500; text-transform: uppercase; letter-spacing: .07em; color: #9ca3af; display: block; margin-bottom: 6px;">
                                                    Excerpt
                                                </label>
                                                <textarea name="excerpt" 
                                                        id="excerpt" 
                                                        rows="3"
                                                        style="width: 100%; border: 0.5px solid #e5e7eb; border-radius: 7px; padding: 8px 12px; font-size: 13px; color: #374151; resize: vertical; transition: all .15s;"
                                                        onfocus="this.style.borderColor='#185FA5'; this.style.boxShadow='0 0 0 3px rgba(24,95,165,.1)';"
                                                        onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none';"
                                                        placeholder="Brief description shown in article cards">{{ old('excerpt', $article->excerpt ?? '') }}</textarea>
                                                @error('excerpt')
                                                    <div style="font-size: 11px; color: #993C1D; margin-top: 4px;">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            {{-- Content Body (shown for article type) --}}
                                            <div class="mb-3" id="bodyField">
                                                <label for="body" style="font-size: 10px; font-weight: 500; text-transform: uppercase; letter-spacing: .07em; color: #9ca3af; display: block; margin-bottom: 6px;">
                                                    Content <span style="color: #993C1D;">*</span>
                                                </label>
                                                <textarea name="body" 
                                                        id="body" 
                                                        rows="15"
                                                        style="width: 100%; border: 0.5px solid #e5e7eb; border-radius: 7px; padding: 12px; font-size: 13px; color: #374151; font-family: monospace; transition: all .15s;"
                                                        onfocus="this.style.borderColor='#185FA5'; this.style.boxShadow='0 0 0 3px rgba(24,95,165,.1)';"
                                                        onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none';"
                                                        placeholder="Write your article content here... (HTML supported)">{{ old('body', $article->body ?? '') }}</textarea>
                                                @error('body')
                                                    <div style="font-size: 11px; color: #993C1D; margin-top: 4px;">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            {{-- Video URL Field (shown for video type) --}}
                                            <div class="mb-3" id="videoUrlField" style="display: none;">
                                                <label for="video_url" style="font-size: 10px; font-weight: 500; text-transform: uppercase; letter-spacing: .07em; color: #9ca3af; display: block; margin-bottom: 6px;">
                                                    Video URL <span style="color: #993C1D;">*</span>
                                                </label>
                                                <input type="url" 
                                                    name="video_url" 
                                                    id="video_url" 
                                                    value="{{ old('video_url', $article->video_url ?? '') }}"
                                                    style="width: 100%; border: 0.5px solid #e5e7eb; border-radius: 7px; padding: 8px 12px; font-size: 13px; color: #374151; transition: all .15s;"
                                                    onfocus="this.style.borderColor='#185FA5'; this.style.boxShadow='0 0 0 3px rgba(24,95,165,.1)';"
                                                    onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none';"
                                                    placeholder="https://www.youtube.com/watch?v=... or https://vimeo.com/...">
                                                <div style="font-size: 11px; color: #9ca3af; margin-top: 4px;">
                                                    Supports YouTube and Vimeo URLs
                                                </div>
                                                @error('video_url')
                                                    <div style="font-size: 11px; color: #993C1D; margin-top: 4px;">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            {{-- External URL Field (shown for link type) --}}
                                            <div class="mb-3" id="externalUrlField" style="display: none;">
                                                <label for="external_url" style="font-size: 10px; font-weight: 500; text-transform: uppercase; letter-spacing: .07em; color: #9ca3af; display: block; margin-bottom: 6px;">
                                                    External URL <span style="color: #993C1D;">*</span>
                                                </label>
                                                <input type="url" 
                                                    name="external_url" 
                                                    id="external_url" 
                                                    value="{{ old('external_url', $article->external_url ?? '') }}"
                                                    style="width: 100%; border: 0.5px solid #e5e7eb; border-radius: 7px; padding: 8px 12px; font-size: 13px; color: #374151; transition: all .15s;"
                                                    onfocus="this.style.borderColor='#185FA5'; this.style.boxShadow='0 0 0 3px rgba(24,95,165,.1)';"
                                                    onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none';"
                                                    placeholder="https://example.com">
                                                @error('external_url')
                                                    <div style="font-size: 11px; color: #993C1D; margin-top: 4px;">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            {{-- Document Upload Field (shown for document type) --}}
                                            <div class="mb-3" id="documentField" style="display: none;">
                                                <label for="document" style="font-size: 10px; font-weight: 500; text-transform: uppercase; letter-spacing: .07em; color: #9ca3af; display: block; margin-bottom: 6px;">
                                                    Document <span style="color: #993C1D;">*</span>
                                                </label>
                                                
                                                @if($isEdit && $article->document_path)
                                                <div class="mb-2" style="background: #E6F1FB; border: 0.5px solid #B5D4F4; border-radius: 7px; padding: 12px; display: flex; align-items: center; justify-content: space-between;">
                                                    <div style="display: flex; align-items: center; gap: 8px;">
                                                        <i class="fa fa-file-pdf-o" style="font-size: 20px; color: #185FA5;"></i>
                                                        <div>
                                                            <div style="font-size: 13px; font-weight: 500; color: #374151;">{{ $article->document_original_name }}</div>
                                                            <div style="font-size: 11px; color: #6b7280;">{{ number_format($article->document_size / 1024, 1) }} KB</div>
                                                        </div>
                                                    </div>
                                                    <span class="badge" style="background: #E1F5EE; color: #0F6E56; font-size: 11px; padding: 3px 9px; border-radius: 99px;">Current File</span>
                                                </div>
                                                @endif
                                                
                                                <div style="border: 2px dashed #e5e7eb; border-radius: 12px; padding: 30px; text-align: center; cursor: pointer; transition: all .15s;"
                                                    onclick="document.getElementById('document').click()"
                                                    onmouseover="this.style.borderColor='#185FA5'; this.style.background='#fafafa';"
                                                    onmouseout="this.style.borderColor='#e5e7eb'; this.style.background='#fff';"
                                                    id="documentDropZone">
                                                    <i class="fa fa-cloud-upload" style="font-size: 36px; color: #9ca3af; margin-bottom: 8px;"></i>
                                                    <div style="font-size: 13px; color: #374151; margin-bottom: 4px;">
                                                        Drag and drop your file here, or <span style="color: #185FA5; font-weight: 500;">click to browse</span>
                                                    </div>
                                                    <div style="font-size: 11px; color: #9ca3af;">
                                                        Supported formats: PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, TXT, ZIP (Max 10MB)
                                                    </div>
                                                </div>
                                                <input type="file" 
                                                    name="document" 
                                                    id="document" 
                                                    accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.zip"
                                                    style="display: none;"
                                                    onchange="updateDocumentName(this)">
                                                <div id="documentFileName" style="font-size: 12px; color: #185FA5; margin-top: 8px; display: none;"></div>
                                                @error('document')
                                                    <div style="font-size: 11px; color: #993C1D; margin-top: 4px;">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Sidebar Column --}}
                                <div class="col-lg-4">
                                    {{-- Publishing Settings Card --}}
                                    <div class="card mb-4" style="background: #fff; border: 0.5px solid #e5e7eb; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.04), 0 0 0 1px rgba(24,95,165,0.05), 0 6px 18px rgba(24,95,165,0.06);">
                                        <div class="card-header" style="background: #fafafa; border-bottom: 0.5px solid #e5e7eb; padding: 12px 20px;">
                                            <h5 style="font-size: 14px; font-weight: 600; color: #111827; margin: 0;">Publishing Settings</h5>
                                        </div>
                                        <div class="card-body" style="padding: 20px;">
                                            {{-- Status --}}
                                            <div class="mb-3">
                                                <label for="status" style="font-size: 10px; font-weight: 500; text-transform: uppercase; letter-spacing: .07em; color: #9ca3af; display: block; margin-bottom: 6px;">
                                                    Status
                                                </label>
                                                <select name="status" 
                                                        id="status"
                                                        style="width: 100%; border: 0.5px solid #e5e7eb; border-radius: 7px; padding: 8px 12px; font-size: 13px; color: #374151; background: #fff; transition: all .15s;"
                                                        onfocus="this.style.borderColor='#185FA5'; this.style.boxShadow='0 0 0 3px rgba(24,95,165,.1)';"
                                                        onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none';">
                                                    <option value="draft" {{ old('status', $article->status ?? 'draft') === 'draft' ? 'selected' : '' }}>Draft</option>
                                                    <option value="published" {{ old('status', $article->status ?? '') === 'published' ? 'selected' : '' }}>Published</option>
                                                    <option value="archived" {{ old('status', $article->status ?? '') === 'archived' ? 'selected' : '' }}>Archived</option>
                                                </select>
                                                @error('status')
                                                    <div style="font-size: 11px; color: #993C1D; margin-top: 4px;">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            {{-- Type --}}
                                            <div class="mb-3">
                                                <label for="type" style="font-size: 10px; font-weight: 500; text-transform: uppercase; letter-spacing: .07em; color: #9ca3af; display: block; margin-bottom: 6px;">
                                                    Content Type
                                                </label>
                                                <select name="type" 
                                                        id="type"
                                                        onchange="toggleContentFields()"
                                                        style="width: 100%; border: 0.5px solid #e5e7eb; border-radius: 7px; padding: 8px 12px; font-size: 13px; color: #374151; background: #fff; transition: all .15s;"
                                                        onfocus="this.style.borderColor='#185FA5'; this.style.boxShadow='0 0 0 3px rgba(24,95,165,.1)';"
                                                        onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none';">
                                                    <option value="article" {{ old('type', $article->type ?? 'article') === 'article' ? 'selected' : '' }}>📝 Article</option>
                                                    <option value="video" {{ old('type', $article->type ?? '') === 'video' ? 'selected' : '' }}>🎥 Video</option>
                                                    <option value="document" {{ old('type', $article->type ?? '') === 'document' ? 'selected' : '' }}>📄 Document</option>
                                                    <option value="link" {{ old('type', $article->type ?? '') === 'link' ? 'selected' : '' }}>🔗 External Link</option>
                                                </select>
                                                @error('type')
                                                    <div style="font-size: 11px; color: #993C1D; margin-top: 4px;">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            {{-- Audience --}}
                                            <div class="mb-3">
                                                <label for="audience" style="font-size: 10px; font-weight: 500; text-transform: uppercase; letter-spacing: .07em; color: #9ca3af; display: block; margin-bottom: 6px;">
                                                    Target Audience
                                                </label>
                                                <select name="audience" 
                                                        id="audience"
                                                        style="width: 100%; border: 0.5px solid #e5e7eb; border-radius: 7px; padding: 8px 12px; font-size: 13px; color: #374151; background: #fff; transition: all .15s;"
                                                        onfocus="this.style.borderColor='#185FA5'; this.style.boxShadow='0 0 0 3px rgba(24,95,165,.1)';"
                                                        onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none';">
                                                    <option value="both" {{ old('audience', $article->audience ?? 'both') === 'both' ? 'selected' : '' }}>Both (Owners & Affiliates)</option>
                                                    <option value="owners" {{ old('audience', $article->audience ?? '') === 'owners' ? 'selected' : '' }}>Owners Only</option>
                                                    <option value="affiliates" {{ old('audience', $article->audience ?? '') === 'affiliates' ? 'selected' : '' }}>Affiliates Only</option>
                                                </select>
                                                @error('audience')
                                                    <div style="font-size: 11px; color: #993C1D; margin-top: 4px;">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            {{-- Category --}}
                                            <div class="mb-3">
                                                <label for="kb_category_id" style="font-size: 10px; font-weight: 500; text-transform: uppercase; letter-spacing: .07em; color: #9ca3af; display: block; margin-bottom: 6px;">
                                                    Category
                                                </label>
                                                <select name="kb_category_id" 
                                                        id="kb_category_id"
                                                        style="width: 100%; border: 0.5px solid #e5e7eb; border-radius: 7px; padding: 8px 12px; font-size: 13px; color: #374151; background: #fff; transition: all .15s;"
                                                        onfocus="this.style.borderColor='#185FA5'; this.style.boxShadow='0 0 0 3px rgba(24,95,165,.1)';"
                                                        onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none';">
                                                    <option value="">No Category</option>
                                                    @foreach($categories as $category)
                                                    <option value="{{ $category->id }}" {{ old('kb_category_id', $article->kb_category_id ?? '') == $category->id ? 'selected' : '' }}>
                                                        {{ $category->name }} ({{ $category->audience }})
                                                    </option>
                                                    @endforeach
                                                </select>
                                                @error('kb_category_id')
                                                    <div style="font-size: 11px; color: #993C1D; margin-top: 4px;">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            {{-- Sort Order --}}
                                            <div class="mb-3">
                                                <label for="sort_order" style="font-size: 10px; font-weight: 500; text-transform: uppercase; letter-spacing: .07em; color: #9ca3af; display: block; margin-bottom: 6px;">
                                                    Sort Order
                                                </label>
                                                <input type="number" 
                                                    name="sort_order" 
                                                    id="sort_order" 
                                                    value="{{ old('sort_order', $article->sort_order ?? 0) }}" 
                                                    min="0"
                                                    style="width: 100%; border: 0.5px solid #e5e7eb; border-radius: 7px; padding: 8px 12px; font-size: 13px; color: #374151; transition: all .15s;"
                                                    onfocus="this.style.borderColor='#185FA5'; this.style.boxShadow='0 0 0 3px rgba(24,95,165,.1)';"
                                                    onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none';"
                                                    placeholder="0">
                                                <div style="font-size: 11px; color: #9ca3af; margin-top: 4px;">
                                                    Lower numbers appear first
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Article Preview Card --}}
                                    <div class="card mb-4" style="background: #fff; border: 0.5px solid #e5e7eb; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.04), 0 0 0 1px rgba(24,95,165,0.05), 0 6px 18px rgba(24,95,165,0.06);">
                                        <div class="card-header" style="background: #fafafa; border-bottom: 0.5px solid #e5e7eb; padding: 12px 20px;">
                                            <h5 style="font-size: 14px; font-weight: 600; color: #111827; margin: 0;">Quick Preview</h5>
                                        </div>
                                        <div class="card-body" style="padding: 20px;">
                                            <div id="previewBox" style="font-size: 13px; color: #6b7280; line-height: 1.6;">
                                                <p style="color: #9ca3af; font-style: italic;">Fill in the title and content to see a preview here...</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Form Actions --}}
                            <div class="row">
                                <div class="col-12">
                                    <div class="card" style="background: #fff; border: 0.5px solid #e5e7eb; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.04), 0 0 0 1px rgba(24,95,165,0.05), 0 6px 18px rgba(24,95,165,0.06);">
                                        <div class="card-body" style="padding: 20px; display: flex; justify-content: space-between; align-items: center;">
                                            <div>
                                                <a href="{{ route('admin.kb.articles') }}" class="btn btn-ghost" 
                                                style="background: #f3f4f6; color: #374151; display: inline-flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 500; padding: 7px 15px; border-radius: 7px; border: 0.5px solid #e5e7eb; cursor: pointer; text-decoration: none;">
                                                    <i class="fa fa-times"></i>
                                                    Cancel
                                                </a>
                                            </div>
                                            <div style="display: flex; gap: 8px;">
                                                <button type="submit" name="status" value="draft" class="btn btn-ghost" 
                                                        style="background: #f3f4f6; color: #374151; display: inline-flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 500; padding: 7px 15px; border-radius: 7px; border: 0.5px solid #e5e7eb; cursor: pointer;">
                                                    <i class="fa fa-save"></i>
                                                    Save as Draft
                                                </button>
                                                <button type="submit" class="btn btn-primary" 
                                                        style="background: #185FA5; color: #fff; display: inline-flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 500; padding: 7px 15px; border-radius: 7px; border: none; cursor: pointer; transition: all .13s;"
                                                        onmouseover="this.style.background='#0F4A84'; this.style.transform='translateY(-1px)';" 
                                                        onmouseout="this.style.background='#185FA5'; this.style.transform='translateY(0)';">
                                                    <i class="fa fa-check"></i>
                                                    {{ $isEdit ? 'Update Article' : 'Publish Article' }}
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- JavaScript for Dynamic Form Behavior --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize form with current type
    toggleContentFields();
    
    // Auto-generate slug from title
    const titleInput = document.getElementById('title');
    const slugInput = document.getElementById('slug');
    
    titleInput.addEventListener('input', function() {
        if (!slugInput.value || slugInput.dataset.autoGenerated === 'true') {
            slugInput.value = slugify(this.value);
            slugInput.dataset.autoGenerated = 'true';
        }
    });
    
    slugInput.addEventListener('input', function() {
        slugInput.dataset.autoGenerated = 'false';
    });
    
    // Live preview
    titleInput.addEventListener('input', updatePreview);
    document.getElementById('excerpt').addEventListener('input', updatePreview);
    document.getElementById('type').addEventListener('change', updatePreview);
    
    // Document drag and drop
    const dropZone = document.getElementById('documentDropZone');
    const fileInput = document.getElementById('document');
    
    if (dropZone && fileInput) {
        dropZone.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.style.borderColor = '#185FA5';
            this.style.background = '#f0f7ff';
        });
        
        dropZone.addEventListener('dragleave', function(e) {
            e.preventDefault();
            this.style.borderColor = '#e5e7eb';
            this.style.background = '#fff';
        });
        
        dropZone.addEventListener('drop', function(e) {
            e.preventDefault();
            this.style.borderColor = '#e5e7eb';
            this.style.background = '#fff';
            
            if (e.dataTransfer.files.length) {
                fileInput.files = e.dataTransfer.files;
                updateDocumentName(fileInput);
            }
        });
    }
});

function toggleContentFields() {
    const type = document.getElementById('type').value;
    
    // Hide all type-specific fields
    document.getElementById('bodyField').style.display = 'none';
    document.getElementById('videoUrlField').style.display = 'none';
    document.getElementById('externalUrlField').style.display = 'none';
    document.getElementById('documentField').style.display = 'none';
    
    // Show relevant field based on type
    switch(type) {
        case 'article':
            document.getElementById('bodyField').style.display = 'block';
            break;
        case 'video':
            document.getElementById('videoUrlField').style.display = 'block';
            break;
        case 'link':
            document.getElementById('externalUrlField').style.display = 'block';
            break;
        case 'document':
            document.getElementById('documentField').style.display = 'block';
            break;
    }
    
    updatePreview();
}

function updatePreview() {
    const previewBox = document.getElementById('previewBox');
    const title = document.getElementById('title').value;
    const excerpt = document.getElementById('excerpt').value;
    const type = document.getElementById('type').value;
    
    if (!title && !excerpt) {
        previewBox.innerHTML = '<p style="color: #9ca3af; font-style: italic;">Fill in the title and content to see a preview here...</p>';
        return;
    }
    
    let previewHtml = '';
    previewHtml += '<div style="margin-bottom: 12px;">';
    previewHtml += '<span style="display: inline-flex; align-items: center; gap: 4px; font-size: 11px; font-weight: 500; padding: 3px 9px; border-radius: 99px; background: #E6F1FB; color: #0C447C; border: 0.5px solid #B5D4F4;">';
    previewHtml += type.charAt(0).toUpperCase() + type.slice(1);
    previewHtml += '</span>';
    previewHtml += '</div>';
    
    if (title) {
        previewHtml += '<h3 style="font-size: 15px; font-weight: 600; color: #185FA5; margin-bottom: 8px;">' + escapeHtml(title) + '</h3>';
    }
    
    if (excerpt) {
        previewHtml += '<p style="font-size: 13px; color: #6b7280; line-height: 1.5;">' + escapeHtml(excerpt) + '</p>';
    }
    
    previewBox.innerHTML = previewHtml;
}

function updateDocumentName(input) {
    const fileNameDiv = document.getElementById('documentFileName');
    if (input.files && input.files[0]) {
        const file = input.files[0];
        const sizeKB = (file.size / 1024).toFixed(1);
        fileNameDiv.innerHTML = '<i class="fa fa-file"></i> ' + file.name + ' (' + sizeKB + ' KB)';
        fileNameDiv.style.display = 'block';
    } else {
        fileNameDiv.style.display = 'none';
    }
}

function slugify(text) {
    return text
        .toString()
        .toLowerCase()
        .trim()
        .replace(/\s+/g, '-')
        .replace(/[^\w\-]+/g, '')
        .replace(/\-\-+/g, '-')
        .replace(/^-+/, '')
        .replace(/-+$/, '');
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>

{{-- Additional Styles --}}
<style>
/* File upload button styling */
#document {
    opacity: 0;
    position: absolute;
    z-index: -1;
}

/* Select styling */
select {
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg width='10' height='6' viewBox='0 0 10 6' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M1 1L5 5L9 1' stroke='%236b7280' stroke-width='1.5' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 12px center;
    padding-right: 35px !important;
}

/* Remove default select arrow in IE */
select::-ms-expand {
    display: none;
}
</style>
@endsection