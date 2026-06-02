@extends('admin.layouts.app')

@php 
    $pageTitle = isset($post) ? 'Edit Post' : 'Create New Post';
@endphp

@php
    $navBlogMMShowClass = 'mm-active';
    $isEdit = isset($post);
    $formAction = $isEdit ? route('admin.blog.posts.update', $post) : route('admin.blog.posts.store');
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
                                    {{ $isEdit ? 'Edit Post' : 'Create New Post' }}
                                </h4>
                                </br>
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb m-0" style="display: flex; gap: 6px; font-size: 12px; color: #9ca3af; list-style: none; padding: 0;">
                                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" style="color: #185FA5; font-weight: 500;">Dashboard</a></li>
                                        <li class="breadcrumb-item"><a href="{{ route('admin.blog.dashboard') }}" style="color: #185FA5; font-weight: 500;">Blog</a></li>
                                        <li class="breadcrumb-item"><a href="{{ route('admin.blog.posts') }}" style="color: #185FA5; font-weight: 500;">Posts</a></li>
                                        <li class="breadcrumb-item active" aria-current="page" style="color: #9ca3af;">
                                            {{ $isEdit ? 'Edit' : 'Create' }}
                                        </li>
                                    </ol>
                                </nav>
                            </div>
                            <div>
                                <a href="{{ route('admin.blog.posts') }}" class="btn btn-ghost" 
                                style="background: #f3f4f6; color: #374151; display: inline-flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 500; padding: 7px 15px; border-radius: 7px; border: 0.5px solid #e5e7eb; cursor: pointer; text-decoration: none; transition: all .13s;"
                                onmouseover="this.style.background='#e5e7eb';" 
                                onmouseout="this.style.background='#f3f4f6';">
                                    <i class="fa fa-arrow-left"></i>
                                    <span>Back to Posts</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Post Form --}}
                <form action="{{ $formAction }}" method="POST" enctype="multipart/form-data" id="postForm">
                    @csrf
                    @if($isEdit)
                        @method('PUT')
                    @endif
                    
                    <div class="row">
                        {{-- Main Content Column --}}
                        <div class="col-lg-8">
                            {{-- Content Card --}}
                            <div class="card mb-4" style="background: #fff; border: 0.5px solid #e5e7eb; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.04), 0 0 0 1px rgba(24,95,165,0.05), 0 6px 18px rgba(24,95,165,0.06);">
                                <div class="card-header" style="background: #fafafa; border-bottom: 0.5px solid #e5e7eb; padding: 12px 20px;">
                                    <h5 style="font-size: 14px; font-weight: 600; color: #111827; margin: 0;">Content</h5>
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
                                            value="{{ old('title', $post->title ?? '') }}" 
                                            required
                                            style="width: 100%; border: 0.5px solid #e5e7eb; border-radius: 7px; padding: 8px 12px; font-size: 13px; color: #374151; transition: all .15s;"
                                            onfocus="this.style.borderColor='#185FA5'; this.style.boxShadow='0 0 0 3px rgba(24,95,165,.1)';"
                                            onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none';"
                                            placeholder="Enter post title">
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
                                            value="{{ old('slug', $post->slug ?? '') }}"
                                            style="width: 100%; border: 0.5px solid #e5e7eb; border-radius: 7px; padding: 8px 12px; font-size: 13px; color: #374151; background: #fafafa; transition: all .15s;"
                                            placeholder="Auto-generated if left empty">
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
                                                placeholder="Brief description shown in post cards">{{ old('excerpt', $post->excerpt ?? '') }}</textarea>
                                        @error('excerpt')
                                            <div style="font-size: 11px; color: #993C1D; margin-top: 4px;">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    {{-- Body --}}
                                    <div class="mb-3">
                                        <label for="body" style="font-size: 10px; font-weight: 500; text-transform: uppercase; letter-spacing: .07em; color: #9ca3af; display: block; margin-bottom: 6px;">
                                            Content <span style="color: #993C1D;">*</span>
                                        </label>
                                        <textarea name="body" 
                                                id="body" 
                                                rows="20"
                                                style="width: 100%; border: 0.5px solid #e5e7eb; border-radius: 7px; padding: 12px; font-size: 13px; color: #374151; font-family: monospace; resize: vertical; transition: all .15s;"
                                                onfocus="this.style.borderColor='#185FA5'; this.style.boxShadow='0 0 0 3px rgba(24,95,165,.1)';"
                                                onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none';"
                                                placeholder="Write your post content here... (HTML supported)">{{ old('body', $post->body ?? '') }}</textarea>
                                        @error('body')
                                            <div style="font-size: 11px; color: #993C1D; margin-top: 4px;">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    {{-- Featured Image --}}
                                    <div class="mb-3">
                                        <label style="font-size: 10px; font-weight: 500; text-transform: uppercase; letter-spacing: .07em; color: #9ca3af; display: block; margin-bottom: 6px;">
                                            Featured Image
                                        </label>
                                        
                                        @if($isEdit && $post->featured_image)
                                        <div class="mb-3" style="position: relative; display: inline-block;">
                                            <img src="{{ asset('storage/' . $post->featured_image) }}" 
                                                alt="Current featured image"
                                                style="max-width: 300px; border-radius: 8px; border: 0.5px solid #e5e7eb;">
                                            <div style="margin-top: 8px; font-size: 12px; color: #6b7280;">
                                                Current image
                                            </div>
                                        </div>
                                        @endif
                                        
                                        <div style="border: 2px dashed #e5e7eb; border-radius: 12px; padding: 30px; text-align: center; cursor: pointer; transition: all .15s;"
                                            onclick="document.getElementById('featured_image').click()"
                                            onmouseover="this.style.borderColor='#185FA5'; this.style.background='#fafafa';"
                                            onmouseout="this.style.borderColor='#e5e7eb'; this.style.background='#fff';"
                                            id="imageDropZone">
                                            <i class="fa fa-image" style="font-size: 36px; color: #9ca3af; margin-bottom: 8px;"></i>
                                            <div style="font-size: 13px; color: #374151; margin-bottom: 4px;">
                                                Drag and drop your image here, or <span style="color: #185FA5; font-weight: 500;">click to browse</span>
                                            </div>
                                            <div style="font-size: 11px; color: #9ca3af;">
                                                Recommended size: 1200x630px. Max 5MB. JPG, PNG, or WebP.
                                            </div>
                                        </div>
                                        <input type="file" 
                                            name="featured_image" 
                                            id="featured_image" 
                                            accept="image/jpeg,image/png,image/webp"
                                            style="display: none;"
                                            onchange="updateImagePreview(this)">
                                        <div id="imagePreviewContainer" style="margin-top: 12px; display: none;">
                                            <img id="imagePreview" src="" alt="Preview" style="max-width: 300px; border-radius: 8px; border: 0.5px solid #e5e7eb;">
                                            <div style="margin-top: 4px; font-size: 12px; color: #185FA5;" id="imageFileName"></div>
                                        </div>
                                        @error('featured_image')
                                            <div style="font-size: 11px; color: #993C1D; margin-top: 4px;">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    {{-- Image Alt Text --}}
                                    <div class="mb-3">
                                        <label for="featured_image_alt" style="font-size: 10px; font-weight: 500; text-transform: uppercase; letter-spacing: .07em; color: #9ca3af; display: block; margin-bottom: 6px;">
                                            Image Alt Text
                                        </label>
                                        <input type="text" 
                                            name="featured_image_alt" 
                                            id="featured_image_alt" 
                                            value="{{ old('featured_image_alt', $post->featured_image_alt ?? '') }}"
                                            style="width: 100%; border: 0.5px solid #e5e7eb; border-radius: 7px; padding: 8px 12px; font-size: 13px; color: #374151; transition: all .15s;"
                                            onfocus="this.style.borderColor='#185FA5'; this.style.boxShadow='0 0 0 3px rgba(24,95,165,.1)';"
                                            onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none';"
                                            placeholder="Description for accessibility and SEO">
                                    </div>
                                </div>
                            </div>

                            {{-- SEO Card --}}
                            <div class="card mb-4" style="background: #fff; border: 0.5px solid #e5e7eb; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.04), 0 0 0 1px rgba(24,95,165,0.05), 0 6px 18px rgba(24,95,165,0.06);">
                                <div class="card-header" style="background: #fafafa; border-bottom: 0.5px solid #e5e7eb; padding: 12px 20px;">
                                    <h5 style="font-size: 14px; font-weight: 600; color: #111827; margin: 0;">SEO Settings</h5>
                                </div>
                                <div class="card-body" style="padding: 20px;">
                                    {{-- Meta Title --}}
                                    <div class="mb-3">
                                        <label for="meta_title" style="font-size: 10px; font-weight: 500; text-transform: uppercase; letter-spacing: .07em; color: #9ca3af; display: block; margin-bottom: 6px;">
                                            Meta Title
                                        </label>
                                        <input type="text" 
                                            name="meta_title" 
                                            id="meta_title" 
                                            value="{{ old('meta_title', $post->meta_title ?? '') }}"
                                            style="width: 100%; border: 0.5px solid #e5e7eb; border-radius: 7px; padding: 8px 12px; font-size: 13px; color: #374151; transition: all .15s;"
                                            onfocus="this.style.borderColor='#185FA5'; this.style.boxShadow='0 0 0 3px rgba(24,95,165,.1)';"
                                            onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none';"
                                            placeholder="Leave empty to use post title">
                                        <div style="font-size: 11px; color: #9ca3af; margin-top: 4px;">
                                            Recommended: 50-60 characters
                                        </div>
                                    </div>

                                    {{-- Meta Description --}}
                                    <div class="mb-3">
                                        <label for="meta_description" style="font-size: 10px; font-weight: 500; text-transform: uppercase; letter-spacing: .07em; color: #9ca3af; display: block; margin-bottom: 6px;">
                                            Meta Description
                                        </label>
                                        <textarea name="meta_description" 
                                                id="meta_description" 
                                                rows="3"
                                                style="width: 100%; border: 0.5px solid #e5e7eb; border-radius: 7px; padding: 8px 12px; font-size: 13px; color: #374151; resize: vertical; transition: all .15s;"
                                                onfocus="this.style.borderColor='#185FA5'; this.style.boxShadow='0 0 0 3px rgba(24,95,165,.1)';"
                                                onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none';"
                                                placeholder="Brief description for search engines">{{ old('meta_description', $post->meta_description ?? '') }}</textarea>
                                        <div style="font-size: 11px; color: #9ca3af; margin-top: 4px;">
                                            Recommended: 150-160 characters
                                        </div>
                                    </div>

                                    {{-- Meta Keywords --}}
                                    <div class="mb-3">
                                        <label for="meta_keywords" style="font-size: 10px; font-weight: 500; text-transform: uppercase; letter-spacing: .07em; color: #9ca3af; display: block; margin-bottom: 6px;">
                                            Meta Keywords
                                        </label>
                                        <input type="text" 
                                            name="meta_keywords" 
                                            id="meta_keywords" 
                                            value="{{ old('meta_keywords', $post->meta_keywords ?? '') }}"
                                            style="width: 100%; border: 0.5px solid #e5e7eb; border-radius: 7px; padding: 8px 12px; font-size: 13px; color: #374151; transition: all .15s;"
                                            onfocus="this.style.borderColor='#185FA5'; this.style.boxShadow='0 0 0 3px rgba(24,95,165,.1)';"
                                            onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none';"
                                            placeholder="keyword1, keyword2, keyword3">
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
                                                style="width: 100%; border: 0.5px solid #e5e7eb; border-radius: 7px; padding: 8px 12px; font-size: 13px; color: #374151; background: #fff; transition: all .15s; appearance: none; background-image: url(\"data:image/svg+xml,%3Csvg width='10' height='6' viewBox='0 0 10 6' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M1 1L5 5L9 1' stroke='%236b7280' stroke-width='1.5' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E\"); background-repeat: no-repeat; background-position: right 12px center; padding-right: 35px;"
                                                onfocus="this.style.borderColor='#185FA5'; this.style.boxShadow='0 0 0 3px rgba(24,95,165,.1)';"
                                                onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none';">
                                            <option value="draft" {{ old('status', $post->status ?? 'draft') === 'draft' ? 'selected' : '' }}>💾 Draft</option>
                                            <option value="published" {{ old('status', $post->status ?? '') === 'published' ? 'selected' : '' }}>🚀 Published</option>
                                            <option value="scheduled" {{ old('status', $post->status ?? '') === 'scheduled' ? 'selected' : '' }}>📅 Scheduled</option>
                                        </select>
                                        @error('status')
                                            <div style="font-size: 11px; color: #993C1D; margin-top: 4px;">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    {{-- Category --}}
                                    <div class="mb-3">
                                        <label for="blog_category_id" style="font-size: 10px; font-weight: 500; text-transform: uppercase; letter-spacing: .07em; color: #9ca3af; display: block; margin-bottom: 6px;">
                                            Category
                                        </label>
                                        <select name="blog_category_id" 
                                                id="blog_category_id"
                                                style="width: 100%; border: 0.5px solid #e5e7eb; border-radius: 7px; padding: 8px 12px; font-size: 13px; color: #374151; background: #fff; transition: all .15s; appearance: none; background-image: url(\"data:image/svg+xml,%3Csvg width='10' height='6' viewBox='0 0 10 6' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M1 1L5 5L9 1' stroke='%236b7280' stroke-width='1.5' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E\"); background-repeat: no-repeat; background-position: right 12px center; padding-right: 35px;"
                                                onfocus="this.style.borderColor='#185FA5'; this.style.boxShadow='0 0 0 3px rgba(24,95,165,.1)';"
                                                onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none';">
                                            <option value="">No Category</option>
                                            @foreach($categories as $category)
                                            <option value="{{ $category->id }}" {{ old('blog_category_id', $post->blog_category_id ?? '') == $category->id ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                            @endforeach
                                        </select>
                                        @error('blog_category_id')
                                            <div style="font-size: 11px; color: #993C1D; margin-top: 4px;">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    {{-- Tags --}}
                                    <div class="mb-3">
                                        <label for="tags" style="font-size: 10px; font-weight: 500; text-transform: uppercase; letter-spacing: .07em; color: #9ca3af; display: block; margin-bottom: 6px;">
                                            Tags
                                        </label>
                                        <input type="text" 
                                            name="tags" 
                                            id="tags" 
                                            value="{{ old('tags', isset($post) && $post->tags ? implode(', ', $post->tags) : '') }}"
                                            style="width: 100%; border: 0.5px solid #e5e7eb; border-radius: 7px; padding: 8px 12px; font-size: 13px; color: #374151; transition: all .15s;"
                                            onfocus="this.style.borderColor='#185FA5'; this.style.boxShadow='0 0 0 3px rgba(24,95,165,.1)';"
                                            onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none';"
                                            placeholder="property management, rental tips, investment">
                                        <div style="font-size: 11px; color: #9ca3af; margin-top: 4px;">
                                            Separate tags with commas
                                        </div>
                                    </div>

                                    {{-- Featured Post --}}
                                    <div class="mb-3">
                                        <div style="display: flex; align-items: center; gap: 8px;">
                                            <input type="checkbox" 
                                                name="is_featured" 
                                                id="is_featured" 
                                                value="1" 
                                                {{ old('is_featured', $post->is_featured ?? false) ? 'checked' : '' }}
                                                style="width: 16px; height: 16px; accent-color: #185FA5;">
                                            <label for="is_featured" style="font-size: 13px; color: #374151; cursor: pointer;">
                                                ⭐ Feature this post
                                            </label>
                                        </div>
                                        <div style="font-size: 11px; color: #9ca3af; margin-top: 4px; margin-left: 24px;">
                                            Featured posts appear prominently on the blog homepage
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Post Preview Card --}}
                            <div class="card mb-4" style="background: #fff; border: 0.5px solid #e5e7eb; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.04), 0 0 0 1px rgba(24,95,165,0.05), 0 6px 18px rgba(24,95,165,0.06);">
                                <div class="card-header" style="background: #fafafa; border-bottom: 0.5px solid #e5e7eb; padding: 12px 20px;">
                                    <h5 style="font-size: 14px; font-weight: 600; color: #111827; margin: 0;">Quick Preview</h5>
                                </div>
                                <div class="card-body" style="padding: 16px;">
                                    <div id="previewBox" style="font-size: 13px; color: #6b7280; line-height: 1.6;">
                                        <p style="color: #9ca3af; font-style: italic;">Fill in the title and content to see a preview...</p>
                                    </div>
                                </div>
                            </div>

                            {{-- Post Stats (Edit Mode) --}}
                            @if($isEdit)
                            <div class="card" style="background: #fff; border: 0.5px solid #e5e7eb; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.04), 0 0 0 1px rgba(24,95,165,0.05), 0 6px 18px rgba(24,95,165,0.06);">
                                <div class="card-header" style="background: #fafafa; border-bottom: 0.5px solid #e5e7eb; padding: 12px 20px;">
                                    <h5 style="font-size: 14px; font-weight: 600; color: #111827; margin: 0;">Post Stats</h5>
                                </div>
                                <div class="card-body" style="padding: 20px;">
                                    <div style="font-size: 13px; color: #6b7280; line-height: 2;">
                                        <div style="display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 0.5px solid #f3f4f6;">
                                            <span>Views</span>
                                            <span style="font-weight: 500; color: #374151;">{{ number_format($post->views_count) }}</span>
                                        </div>
                                        <div style="display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 0.5px solid #f3f4f6;">
                                            <span>Likes</span>
                                            <span style="font-weight: 500; color: #374151;">{{ number_format($post->likes_count) }}</span>
                                        </div>
                                        <div style="display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 0.5px solid #f3f4f6;">
                                            <span>Comments</span>
                                            <span style="font-weight: 500; color: #374151;">{{ number_format($post->comments_count) }}</span>
                                        </div>
                                        <div style="display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 0.5px solid #f3f4f6;">
                                            <span>Shares</span>
                                            <span style="font-weight: 500; color: #374151;">{{ number_format($post->shares_count) }}</span>
                                        </div>
                                        <div style="display: flex; justify-content: space-between; padding: 6px 0;">
                                            <span>Published</span>
                                            <span style="font-weight: 500; color: #374151;">{{ $post->published_at ? $post->published_at->format('M d, Y') : 'Not published' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>

                    {{-- Form Actions --}}
                    <div class="row">
                        <div class="col-12">
                            <div class="card" style="background: #fff; border: 0.5px solid #e5e7eb; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.04), 0 0 0 1px rgba(24,95,165,0.05), 0 6px 18px rgba(24,95,165,0.06);">
                                <div class="card-body" style="padding: 20px; display: flex; justify-content: space-between; align-items: center;">
                                    <div>
                                        <a href="{{ route('admin.blog.posts') }}" class="btn btn-ghost" 
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
                                            {{ $isEdit ? 'Update Post' : 'Publish Post' }}
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

{{-- JavaScript for Dynamic Behavior --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
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
    document.getElementById('blog_category_id').addEventListener('change', updatePreview);
    
    // Image drag and drop
    const dropZone = document.getElementById('imageDropZone');
    const fileInput = document.getElementById('featured_image');
    
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
                updateImagePreview(fileInput);
            }
        });
    }
});

function updateImagePreview(input) {
    const preview = document.getElementById('imagePreview');
    const container = document.getElementById('imagePreviewContainer');
    const fileName = document.getElementById('imageFileName');
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            fileName.textContent = input.files[0].name + ' (' + (input.files[0].size / 1024).toFixed(1) + ' KB)';
            container.style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    } else {
        container.style.display = 'none';
    }
}

function updatePreview() {
    const previewBox = document.getElementById('previewBox');
    const title = document.getElementById('title').value;
    const excerpt = document.getElementById('excerpt').value;
    const categorySelect = document.getElementById('blog_category_id');
    const category = categorySelect.options[categorySelect.selectedIndex].text;
    
    if (!title && !excerpt) {
        previewBox.innerHTML = '<p style="color: #9ca3af; font-style: italic;">Fill in the title and content to see a preview...</p>';
        return;
    }
    
    let previewHtml = '';
    
    if (category && category !== 'No Category') {
        previewHtml += '<div style="margin-bottom: 8px;">';
        previewHtml += '<span style="background: #E6F1FB; color: #0C447C; font-size: 11px; font-weight: 500; padding: 3px 9px; border-radius: 99px; border: 0.5px solid #B5D4F4;">' + category + '</span>';
        previewHtml += '</div>';
    }
    
    if (title) {
        previewHtml += '<h3 style="font-size: 16px; font-weight: 600; color: #111827; margin-bottom: 8px; line-height: 1.4;">' + escapeHtml(title) + '</h3>';
    }
    
    if (excerpt) {
        previewHtml += '<p style="font-size: 13px; color: #6b7280; margin-bottom: 12px; line-height: 1.6;">' + escapeHtml(excerpt) + '</p>';
    }
    
    previewHtml += '<div style="font-size: 12px; color: #9ca3af;">Just now · 5 min read</div>';
    
    previewBox.innerHTML = previewHtml;
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
@endsection