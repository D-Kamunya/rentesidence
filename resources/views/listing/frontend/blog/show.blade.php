@extends('saas.frontend.layouts.app')

@php
    $pageTitle = $post->meta_title ?? $post->title;
    $pageDescription = $post->meta_description ?? Str::limit(strip_tags($post->body), 160);
@endphp

@section('title', $pageTitle)
@section('meta_description', $pageDescription)
@section('meta_keywords', $post->meta_keywords ?? '')

@push('meta')
<meta property="og:title" content="{{ $post->title }}">
<meta property="og:description" content="{{ $post->excerpt ?? Str::limit(strip_tags($post->body), 200) }}">
<meta property="og:image" content="{{ $post->featured_image ? asset('storage/' . $post->featured_image) : '' }}">
<meta property="og:url" content="{{ route('blog.show', $post->slug) }}">
<meta name="twitter:card" content="summary_large_image">
@endpush

@section('content')
{{-- Article Hero --}}
<div style="padding-top: 80px;">

{{-- Article Hero --}}
<div style="background: linear-gradient(135deg, #111827 0%, #1f2937 100%); padding: 60px 0; color: #fff;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                {{-- Breadcrumb --}}
                <nav style="margin-bottom: 24px;">
                    <ol style="display: flex; gap: 6px; font-size: 12px; color: rgba(255,255,255,0.6); list-style: none; padding: 0; margin: 0;">
                        <li><a href="{{ route('blog.index') }}" style="color: rgba(255,255,255,0.8); font-weight: 500; text-decoration: none;">Blog</a></li>
                        <li>
                            <svg width="8" height="8" viewBox="0 0 8 8" fill="none">
                                <path d="M2.5 1.5L5.5 4L2.5 6.5" stroke="rgba(255,255,255,0.6)" stroke-width="1.5" stroke-linecap="round"/>
                            </svg>
                        </li>
                        @if($post->category)
                        <li><a href="{{ route('blog.index', ['category' => $post->category->slug]) }}" style="color: rgba(255,255,255,0.8); font-weight: 500; text-decoration: none;">{{ $post->category->name }}</a></li>
                        <li>
                            <svg width="8" height="8" viewBox="0 0 8 8" fill="none">
                                <path d="M2.5 1.5L5.5 4L2.5 6.5" stroke="rgba(255,255,255,0.6)" stroke-width="1.5" stroke-linecap="round"/>
                            </svg>
                        </li>
                        @endif
                        <li>{{ Str::limit($post->title, 50) }}</li>
                    </ol>
                </nav>

                {{-- Category Badge --}}
                <div class="d-flex align-items-center gap-2 mb-4">
                    @if($post->category)
                    <span style="background: {{ $post->category->color ?? '#185FA5' }}30; color: #fff; font-size: 11px; font-weight: 500; padding: 4px 12px; border-radius: 99px; border: 0.5px solid rgba(255,255,255,0.2);">
                        {{ $post->category->name }}
                    </span>
                    @endif
                    @if($post->is_featured)
                    <span style="background: rgba(255,255,255,0.1); color: #FFD700; font-size: 11px; font-weight: 500; padding: 4px 12px; border-radius: 99px; border: 0.5px solid rgba(255,255,255,0.2);">
                        ⭐ Featured
                    </span>
                    @endif
                </div>

                {{-- Title --}}
                <h1 style="font-size: 36px; font-weight: 700; line-height: 1.3; margin-bottom: 16px; color: #fff;">
                    {{ $post->title }}
                </h1>

                {{-- Excerpt --}}
                @if($post->excerpt)
                <p style="font-size: 16px; color: rgba(255,255,255,0.8); margin-bottom: 24px; line-height: 1.6;">
                    {{ $post->excerpt }}
                </p>
                @endif

                {{-- Author & Meta --}}
                <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div style="width: 44px; height: 44px; border-radius: 10px; background: linear-gradient(135deg, #185FA5, #534AB7); display: flex; align-items: center; justify-content: center; font-size: 18px; font-weight: 600; color: #fff;">
                            {{ substr($post->author->name ?? 'A', 0, 1) }}
                        </div>
                        <div>
                            <div style="font-size: 14px; font-weight: 500; color: #fff;">{{ $post->author->name ?? 'Admin' }}</div>
                            <div style="font-size: 12px; color: rgba(255,255,255,0.6);">
                                {{ $post->published_at->format('M d, Y') }} · {{ $post->reading_time_text }}
                            </div>
                        </div>
                    </div>
                    
                    <div style="display: flex; align-items: center; gap: 16px;">
                        {{-- Views --}}
                        <div style="display: flex; align-items: center; gap: 4px; font-size: 13px; color: rgba(255,255,255,0.8);">
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                <circle cx="8" cy="8" r="3" stroke="currentColor" stroke-width="1.5"/>
                                <path d="M2 8C3.5 4 12.5 4 14 8C12.5 12 3.5 12 2 8Z" stroke="currentColor" stroke-width="1.5"/>
                            </svg>
                            {{ number_format($post->views_count) }}
                        </div>
                        
                        {{-- Comments --}}
                        <div style="display: flex; align-items: center; gap: 4px; font-size: 13px; color: rgba(255,255,255,0.8);">
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                <path d="M14 10C14 10.5304 13.7893 11.0391 13.4142 11.4142C13.0391 11.7893 12.5304 12 12 12H5L2 15V4C2 3.46957 2.21071 2.96086 2.58579 2.58579C2.96086 2.21071 3.46957 2 4 2H12C12.5304 2 13.0391 2.21071 13.4142 2.58579C13.7893 2.96086 14 3.46957 14 4V10Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            {{ number_format($post->comments_count) }}
                        </div>
                        
                        {{-- Likes --}}
                        <div style="display: flex; align-items: center; gap: 4px; font-size: 13px; color: rgba(255,255,255,0.8);">
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                <path d="M8 14S1.5 10 1.5 5.5C1.5 3.5 3 2.5 4.5 2.5C6 2.5 8 4 8 4S10 2.5 11.5 2.5C13 2.5 14.5 3.5 14.5 5.5C14.5 10 8 14 8 14Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            {{ number_format($post->likes_count) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Article Content --}}
<div class="container py-5">
    <div class="row">
        {{-- Main Content --}}
        <div class="col-lg-8">
            {{-- Featured Image --}}
            @if($post->featured_image)
            <div style="margin-bottom: 32px; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.1);">
                <img src="{{ asset('storage/' . $post->featured_image) }}" 
                     alt="{{ $post->featured_image_alt ?? $post->title }}"
                     style="width: 100%; height: auto;">
            </div>
            @endif

            {{-- Article Body --}}
            <div class="ow-card mb-5" style="background: #fff; border: 0.5px solid #e5e7eb; border-radius: 12px; padding: 32px; box-shadow: 0 4px 12px rgba(0,0,0,0.04);">
                <div style="font-size: 16px; line-height: 1.8; color: #374151;">
                    {!! $post->body !!}
                </div>
                
                {{-- Tags --}}
                @if($post->tags && count($post->tags) > 0)
                <div style="margin-top: 32px; padding-top: 24px; border-top: 0.5px solid #e5e7eb;">
                    <div style="display: flex; flex-wrap: wrap; gap: 8px; align-items: center;">
                        <span style="font-size: 12px; color: #6b7280; margin-right: 4px;">Tags:</span>
                        @foreach($post->tags as $tag)
                        <a href="{{ route('blog.index', ['tag' => trim($tag)]) }}" 
                           style="background: #f3f4f6; color: #6b7280; font-size: 12px; padding: 4px 12px; border-radius: 99px; border: 0.5px solid #e5e7eb; text-decoration: none; transition: all .13s;"
                           onmouseover="this.style.background='#185FA5'; this.style.color='#fff'; this.style.borderColor='#185FA5';"
                           onmouseout="this.style.background='#f3f4f6'; this.style.color='#6b7280'; this.style.borderColor='#e5e7eb';">
                            {{ trim($tag) }}
                        </a>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Engagement Bar --}}
                <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px; margin-top: 24px; padding-top: 24px; border-top: 0.5px solid #e5e7eb;">
                    {{-- Like & Share --}}
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <button onclick="likePost({{ $post->id }})" 
                                id="likeButton"
                                style="display: inline-flex; align-items: center; gap: 6px; font-size: 13px; padding: 8px 16px; border-radius: 7px; border: 0.5px solid #e5e7eb; background: #fff; color: #6b7280; cursor: pointer; transition: all .13s;"
                                onmouseover="this.style.borderColor='#993C1D'; this.style.color='#993C1D';"
                                onmouseout="this.style.borderColor='#e5e7eb'; this.style.color='#6b7280';">
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" id="likeIcon">
                                <path d="M8 14S1.5 10 1.5 5.5C1.5 3.5 3 2.5 4.5 2.5C6 2.5 8 4 8 4S10 2.5 11.5 2.5C13 2.5 14.5 3.5 14.5 5.5C14.5 10 8 14 8 14Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <span id="likesCount">{{ $post->likes_count }}</span> Likes
                        </button>
                        
                        <div style="position: relative;">
                            <button onclick="document.getElementById('shareDropdown').classList.toggle('d-none')" 
                                    style="display: inline-flex; align-items: center; gap: 6px; font-size: 13px; padding: 8px 16px; border-radius: 7px; border: 0.5px solid #e5e7eb; background: #fff; color: #6b7280; cursor: pointer; transition: all .13s;"
                                    onmouseover="this.style.borderColor='#185FA5'; this.style.color='#185FA5';"
                                    onmouseout="this.style.borderColor='#e5e7eb'; this.style.color='#6b7280';">
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                    <path d="M5 7L3 8.5V12.5C3 13.0523 3.44772 13.5 4 13.5H12C12.5523 13.5 13 13.0523 13 12.5V8.5L11 7M8 1.5V10.5M4.5 5L8 1.5L11.5 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                Share
                            </button>
                            <div id="shareDropdown" class="d-none" style="position: absolute; bottom: 100%; left: 0; margin-bottom: 8px; background: #fff; border: 0.5px solid #e5e7eb; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); padding: 8px; min-width: 200px;">
                                @foreach($post->social_share_links as $platform => $url)
                                <a href="{{ $url }}" target="_blank" onclick="sharePost({{ $post->id }}, '{{ $platform }}')"
                                   style="display: flex; align-items: center; gap: 8px; padding: 8px 12px; border-radius: 7px; text-decoration: none; color: #374151; font-size: 13px; transition: all .15s;"
                                   onmouseover="this.style.background='#f3f4f6';"
                                   onmouseout="this.style.background='transparent';">
                                    {{ ucfirst($platform) }}
                                </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    
                    {{-- Reading Time --}}
                    <div style="display: flex; align-items: center; gap: 4px; font-size: 13px; color: #9ca3af;">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                            <circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.5"/>
                            <path d="M8 5V8L10 10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                        </svg>
                        {{ $post->reading_time_text }}
                    </div>
                </div>
            </div>

            {{-- Previous / Next Navigation --}}
            <div class="row mb-5">
                <div class="col-6">
                    @if($previousPost)
                    <a href="{{ route('blog.show', $previousPost->slug) }}" style="text-decoration: none;">
                        <div style="font-size: 12px; color: #9ca3af; margin-bottom: 4px;">← Previous Article</div>
                        <div style="font-size: 14px; font-weight: 500; color: #185FA5;">{{ Str::limit($previousPost->title, 50) }}</div>
                    </a>
                    @endif
                </div>
                <div class="col-6 text-end">
                    @if($nextPost)
                    <a href="{{ route('blog.show', $nextPost->slug) }}" style="text-decoration: none;">
                        <div style="font-size: 12px; color: #9ca3af; margin-bottom: 4px;">Next Article →</div>
                        <div style="font-size: 14px; font-weight: 500; color: #185FA5;">{{ Str::limit($nextPost->title, 50) }}</div>
                    </a>
                    @endif
                </div>
            </div>

            {{-- Comments Section --}}
            <div id="comments-section" class="ow-card mb-5" style="background: #fff; border: 0.5px solid #e5e7eb; border-radius: 12px; padding: 32px; box-shadow: 0 4px 12px rgba(0,0,0,0.04);">
                {{-- Pending Comment Success Message --}}
                @if(session('comment_success'))
                <div style="background: #E6F1FB; color: #0C447C; padding: 16px; border-radius: 8px; margin-bottom: 20px; font-size: 13px; display: flex; align-items: flex-start; gap: 12px; border: 0.5px solid #B5D4F4;">
                    <div style="flex-shrink: 0; margin-top: 2px;">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                            <circle cx="10" cy="10" r="8" stroke="currentColor" stroke-width="1.5"/>
                            <path d="M7.5 10L9 11.5L12.5 8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <div>
                        <div style="font-weight: 600; margin-bottom: 4px;">Comment Submitted!</div>
                        <div style="color: #0C447C; opacity: 0.9;">{{ session('comment_success') }}</div>
                    </div>
                </div>
                @endif

                {{-- Error Messages --}}
                @if($errors->any())
                <div style="background: #FAECE7; color: #993C1D; padding: 12px 16px; border-radius: 7px; margin-bottom: 20px; font-size: 13px; border: 0.5px solid #F5C6B3;">
                    <div style="font-weight: 600; margin-bottom: 8px;">Please fix the following errors:</div>
                    <ul style="margin: 0; padding-left: 20px;">
                        @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <h3 style="font-size: 18px; font-weight: 600; color: #111827; margin-bottom: 24px;">
                    Comments ({{ $post->comments_count }})
                </h3>

                {{-- Comment Form --}}
                <form action="{{ route('blog.comment', $post) }}" method="POST" class="mb-4" id="commentForm">
                    @csrf
                    <div class="row g-3">
                        @guest
                        <div class="col-md-6">
                            <label for="author_name" style="font-size: 10px; font-weight: 500; text-transform: uppercase; letter-spacing: .07em; color: #9ca3af; display: block; margin-bottom: 6px;">
                                Name <span style="color: #993C1D;">*</span>
                            </label>
                            <input type="text" 
                                name="author_name" 
                                id="author_name" 
                                value="{{ old('author_name') }}" 
                                required
                                style="width: 100%; border: 0.5px solid #e5e7eb; border-radius: 7px; padding: 10px 14px; font-size: 13px; color: #374151; transition: all .15s;"
                                onfocus="this.style.borderColor='#185FA5'; this.style.boxShadow='0 0 0 3px rgba(24,95,165,.1)';"
                                onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none';"
                                placeholder="Your name">
                            @error('author_name')
                            <div style="font-size: 11px; color: #993C1D; margin-top: 4px;">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="author_email" style="font-size: 10px; font-weight: 500; text-transform: uppercase; letter-spacing: .07em; color: #9ca3af; display: block; margin-bottom: 6px;">
                                Email <span style="color: #993C1D;">*</span>
                            </label>
                            <input type="email" 
                                name="author_email" 
                                id="author_email" 
                                value="{{ old('author_email') }}" 
                                required
                                style="width: 100%; border: 0.5px solid #e5e7eb; border-radius: 7px; padding: 10px 14px; font-size: 13px; color: #374151; transition: all .15s;"
                                onfocus="this.style.borderColor='#185FA5'; this.style.boxShadow='0 0 0 3px rgba(24,95,165,.1)';"
                                onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none';"
                                placeholder="your@email.com">
                            @error('author_email')
                            <div style="font-size: 11px; color: #993C1D; margin-top: 4px;">{{ $message }}</div>
                            @enderror
                        </div>
                        @else
                        <input type="hidden" name="author_name" value="{{ auth()->user()->name }}">
                        <input type="hidden" name="author_email" value="{{ auth()->user()->email }}">
                        @endguest
                        
                        <div class="col-12">
                            <label for="content" style="font-size: 10px; font-weight: 500; text-transform: uppercase; letter-spacing: .07em; color: #9ca3af; display: block; margin-bottom: 6px;">
                                Comment <span style="color: #993C1D;">*</span>
                            </label>
                            <textarea name="content" 
                                    id="content" 
                                    rows="4" 
                                    required
                                    style="width: 100%; border: 0.5px solid #e5e7eb; border-radius: 7px; padding: 10px 14px; font-size: 13px; color: #374151; resize: vertical; transition: all .15s;"
                                    onfocus="this.style.borderColor='#185FA5'; this.style.boxShadow='0 0 0 3px rgba(24,95,165,.1)';"
                                    onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none';"
                                    placeholder="Write your comment...">{{ old('content') }}</textarea>
                            @error('content')
                            <div style="font-size: 11px; color: #993C1D; margin-top: 4px;">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-12">
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <button type="submit" 
                                        style="background: #185FA5; color: #fff; border: none; border-radius: 7px; padding: 10px 24px; font-size: 13px; font-weight: 500; cursor: pointer; transition: all .13s; display: inline-flex; align-items: center; gap: 6px;"
                                        onmouseover="this.style.background='#0F4A84';"
                                        onmouseout="this.style.background='#185FA5';"
                                        id="submitCommentBtn">
                                    <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                                        <path d="M2 7L11 2L7 12L6 8L2 7Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    Post Comment
                                </button>
                                <span style="font-size: 12px; color: #9ca3af;">
                                    <svg width="12" height="12" viewBox="0 0 12 12" fill="none" style="vertical-align: middle;">
                                        <circle cx="6" cy="6" r="5" stroke="currentColor" stroke-width="1"/>
                                        <path d="M6 3.5V6.5M6 8.5H6.01" stroke="currentColor" stroke-width="1" stroke-linecap="round"/>
                                    </svg>
                                    Comments are moderated before being published
                                </span>
                            </div>
                        </div>
                    </div>
                </form>

                {{-- Comments List --}}
                <div id="commentsList">
                    @forelse($post->comments as $comment)
                    <div class="comment-item" style="padding: 16px 0; border-top: 0.5px solid #f3f4f6;">
                        <div style="display: flex; gap: 12px;">
                            <div style="width: 36px; height: 36px; border-radius: 8px; background: #E6F1FB; display: flex; align-items: center; justify-content: center; font-size: 14px; font-weight: 600; color: #185FA5; flex-shrink: 0;">
                                {{ strtoupper(substr($comment->author_name, 0, 1)) }}
                            </div>
                            <div style="flex: 1;">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                                    <span style="font-size: 14px; font-weight: 600; color: #111827;">{{ $comment->author_name }}</span>
                                    <span style="font-size: 12px; color: #9ca3af;">{{ $comment->created_at->diffForHumans() }}</span>
                                </div>
                                <p style="font-size: 14px; color: #374151; line-height: 1.6; margin: 0;">
                                    {{ $comment->content }}
                                </p>
                            </div>
                        </div>
                        
                        {{-- Replies --}}
                        @foreach($comment->replies as $reply)
                        <div style="display: flex; gap: 12px; margin-top: 12px; margin-left: 48px;">
                            <div style="width: 28px; height: 28px; border-radius: 6px; background: #f3f4f6; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 600; color: #6b7280; flex-shrink: 0;">
                                {{ strtoupper(substr($reply->author_name, 0, 1)) }}
                            </div>
                            <div style="flex: 1;">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                                    <span style="font-size: 13px; font-weight: 600; color: #111827;">{{ $reply->author_name }}</span>
                                    <span style="font-size: 11px; color: #9ca3af;">{{ $reply->created_at->diffForHumans() }}</span>
                                </div>
                                <p style="font-size: 13px; color: #374151; line-height: 1.6; margin: 0;">
                                    {{ $reply->content }}
                                </p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @empty
                    <div id="noCommentsMessage" style="text-align: center; padding: 32px; color: #9ca3af; font-size: 14px;">
                        <div style="margin-bottom: 8px;">
                            <svg width="48" height="48" viewBox="0 0 48 48" fill="none" style="opacity: 0.5;">
                                <path d="M42 30C42 31.0609 41.5786 32.0783 40.8284 32.8284C40.0783 33.5786 39.0609 34 38 34H17L8 42V14C8 12.9391 8.42143 11.9217 9.17157 11.1716C9.92172 10.4214 10.9391 10 12 10H38C39.0609 10 40.0783 10.4214 40.8284 11.1716C41.5786 11.9217 42 12.9391 42 14V30Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        No comments yet. Be the first to share your thoughts!
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="col-lg-4">
            {{-- Author Card --}}
            <div class="ow-card mb-4" style="background: #fff; border: 0.5px solid #e5e7eb; border-radius: 12px; padding: 24px; text-align: center; box-shadow: 0 4px 12px rgba(0,0,0,0.04);">
                <div style="width: 64px; height: 64px; border-radius: 12px; background: linear-gradient(135deg, #185FA5, #534AB7); display: flex; align-items: center; justify-content: center; font-size: 24px; font-weight: 600; color: #fff; margin: 0 auto 12px;">
                    {{ substr($post->author->name ?? 'A', 0, 1) }}
                </div>
                <h3 style="font-size: 16px; font-weight: 600; color: #111827; margin-bottom: 4px;">{{ $post->author->name ?? 'Admin' }}</h3>
                <p style="font-size: 13px; color: #6b7280;">Property Management Expert</p>
            </div>

            {{-- Table of Contents --}}
            <div class="ow-card mb-4" style="background: #fff; border: 0.5px solid #e5e7eb; border-radius: 12px; padding: 20px; position: sticky; top: 20px; box-shadow: 0 4px 12px rgba(0,0,0,0.04);">
                <h3 style="font-size: 14px; font-weight: 600; color: #111827; margin-bottom: 16px;">📑 In This Article</h3>
                <div id="tableOfContents" style="font-size: 13px; color: #6b7280; line-height: 2;">
                    <!-- Generated by JavaScript -->
                </div>
            </div>

            {{-- Related Posts --}}
            @if($relatedPosts->count() > 0)
            <div class="ow-card" style="background: #fff; border: 0.5px solid #e5e7eb; border-radius: 12px; padding: 20px; box-shadow: 0 4px 12px rgba(0,0,0,0.04);">
                <h3 style="font-size: 14px; font-weight: 600; color: #111827; margin-bottom: 16px;">Related Articles</h3>
                <div style="display: flex; flex-direction: column; gap: 12px;">
                    @foreach($relatedPosts as $related)
                    <a href="{{ route('blog.show', $related->slug) }}" style="text-decoration: none; display: flex; gap: 12px;">
                        @if($related->featured_image)
                        <img src="{{ asset('storage/' . $related->featured_image) }}" 
                             alt="{{ $related->title }}"
                             style="width: 60px; height: 60px; border-radius: 8px; object-fit: cover; flex-shrink: 0;">
                        @endif
                        <div>
                            <h4 style="font-size: 13px; font-weight: 500; color: #374151; margin-bottom: 4px; line-height: 1.4;">
                                {{ $related->title }}
                            </h4>
                            <span style="font-size: 11px; color: #9ca3af;">{{ $related->reading_time_text }}</span>
                        </div>
                    </a>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

{{-- Newsletter CTA --}}
<div id="newsletter-section" style="background: linear-gradient(135deg, #185FA5 0%, #0F4A84 100%); padding: 60px 0; margin-top: 40px;">
    <div class="container">
        <div class="row justify-content-center text-center">
            <div class="col-lg-6">
                {{-- Success Message --}}
                @if(session('subscribe_success'))
                <div style="background: rgba(255,255,255,0.2); color: #fff; padding: 16px; border-radius: 8px; margin-bottom: 24px; font-size: 13px; display: flex; align-items: flex-start; gap: 10px; border: 0.5px solid rgba(255,255,255,0.3); animation: fadeIn 0.3s ease;">
                    <svg width="18" height="18" viewBox="0 0 18 18" fill="none" style="flex-shrink: 0; margin-top: 2px;">
                        <circle cx="9" cy="9" r="7" stroke="currentColor" stroke-width="1.5"/>
                        <path d="M6 9L8 11L12 7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <div>
                        <div style="font-weight: 600; margin-bottom: 4px;">Subscribed Successfully!</div>
                        <div style="opacity: 0.95;">{{ session('subscribe_success') }}</div>
                    </div>
                </div>
                @endif

                <h2 style="font-size: 24px; font-weight: 600; color: #fff; margin-bottom: 12px;">
                    📧 Never Miss an Update
                </h2>
                <p style="font-size: 14px; color: #ffffff; opacity: 1; margin-bottom: 24px; line-height: 1.6;">
                    Get the latest property management tips and insights delivered straight to your inbox.
                </p>
                <form action="{{ route('blog.subscribe') }}" method="POST" id="subscribeForm" style="display: flex; gap: 8px; max-width: 400px; margin: 0 auto;">
                    @csrf
                    <input type="email" name="email" placeholder="Enter your email" required
                           style="flex: 1; border: none; border-radius: 7px; padding: 12px 16px; font-size: 14px;">
                    <button type="submit" 
                            id="subscribeBtn"
                            style="background: #fff; color: #185FA5; border: none; border-radius: 7px; padding: 12px 24px; font-size: 14px; font-weight: 600; cursor: pointer; transition: all .13s; white-space: nowrap;"
                            onmouseover="this.style.background='#f3f4f6';"
                            onmouseout="this.style.background='#fff';">
                        Subscribe
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Like functionality
async function likePost(postId) {
    try {
        const response = await fetch(`/blog/${postId}/like`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
            },
        });
        const data = await response.json();
        document.getElementById('likesCount').textContent = data.likes_count;
        
        const likeButton = document.getElementById('likeButton');
        const likeIcon = document.getElementById('likeIcon');
        if (data.liked) {
            likeButton.style.borderColor = '#993C1D';
            likeButton.style.color = '#993C1D';
            likeButton.style.background = '#FAECE7';
            likeIcon.innerHTML = '<path d="M8 14S1.5 10 1.5 5.5C1.5 3.5 3 2.5 4.5 2.5C6 2.5 8 4 8 4S10 2.5 11.5 2.5C13 2.5 14.5 3.5 14.5 5.5C14.5 10 8 14 8 14Z" fill="currentColor" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>';
        } else {
            likeButton.style.borderColor = '#e5e7eb';
            likeButton.style.color = '#6b7280';
            likeButton.style.background = '#fff';
            likeIcon.innerHTML = '<path d="M8 14S1.5 10 1.5 5.5C1.5 3.5 3 2.5 4.5 2.5C6 2.5 8 4 8 4S10 2.5 11.5 2.5C13 2.5 14.5 3.5 14.5 5.5C14.5 10 8 14 8 14Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>';
        }
    } catch (error) {
        console.error('Error liking post:', error);
    }
}

// Share functionality
async function sharePost(postId, platform) {
    try {
        await fetch(`/blog/${postId}/share`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ platform })
        });
        document.getElementById('shareDropdown').classList.add('d-none');
    } catch (error) {
        console.error('Error sharing post:', error);
    }
}

// Scroll to comments if needed
@if(session('scrollTo'))
document.addEventListener('DOMContentLoaded', function() {
    const commentsSection = document.getElementById('comments-section');
    if (commentsSection) {
        setTimeout(() => {
            commentsSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }, 300);
    }
});
@endif

// AJAX comment submission
const commentForm = document.getElementById('commentForm');
if (commentForm) {
    commentForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const submitBtn = document.getElementById('submitCommentBtn');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<span style="display: inline-flex; align-items: center; gap: 6px;"><span class="spinner-border spinner-border-sm" style="width: 14px; height: 14px; border-width: 2px;"></span> Submitting...</span>';
        submitBtn.disabled = true;
        
        try {
            const formData = new FormData(this);
            const response = await fetch(this.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: formData,
            });
            
            const data = await response.json();
            
            if (data.success) {
                // Clear the form
                document.getElementById('content').value = '';
                if (document.getElementById('author_name')) document.getElementById('author_name').value = '';
                if (document.getElementById('author_email')) document.getElementById('author_email').value = '';
                
                // Remove any existing success messages
                const existingMessages = document.querySelectorAll('.comment-success-message');
                existingMessages.forEach(msg => msg.remove());
                
                // Show pending approval message
                const successDiv = document.createElement('div');
                successDiv.className = 'comment-success-message';
                successDiv.style.cssText = 'background: #E6F1FB; color: #0C447C; padding: 16px; border-radius: 8px; margin-bottom: 20px; font-size: 13px; display: flex; align-items: flex-start; gap: 12px; border: 0.5px solid #B5D4F4; animation: fadeIn 0.3s ease;';
                successDiv.innerHTML = `
                    <div style="flex-shrink: 0; margin-top: 2px;">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                            <circle cx="10" cy="10" r="8" stroke="currentColor" stroke-width="1.5"/>
                            <path d="M7.5 10L9 11.5L12.5 8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <div>
                        <div style="font-weight: 600; margin-bottom: 4px;">Comment Submitted!</div>
                        <div style="color: #0C447C; opacity: 0.9;">${data.message}</div>
                    </div>
                `;
                
                const commentForm = document.getElementById('commentForm');
                commentForm.parentNode.insertBefore(successDiv, commentForm);
                
                // Remove success message after 8 seconds
                setTimeout(() => {
                    successDiv.style.opacity = '0';
                    successDiv.style.transition = 'opacity 0.3s ease';
                    setTimeout(() => successDiv.remove(), 300);
                }, 8000);
            } else if (data.errors) {
                // Show validation errors
                let errorHtml = '<div style="background: #FAECE7; color: #993C1D; padding: 12px 16px; border-radius: 7px; margin-bottom: 20px; font-size: 13px; border: 0.5px solid #F5C6B3;">';
                errorHtml += '<div style="font-weight: 600; margin-bottom: 8px;">Please fix the following errors:</div><ul style="margin: 0; padding-left: 20px;">';
                Object.values(data.errors).flat().forEach(error => {
                    errorHtml += `<li>${error}</li>`;
                });
                errorHtml += '</ul></div>';
                
                commentForm.insertAdjacentHTML('beforebegin', errorHtml);
            }
        } catch (error) {
            console.error('Error posting comment:', error);
            alert('Failed to post comment. Please try again.');
        } finally {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }
    });
}

// Add fade-in animation
const style = document.createElement('style');
style.textContent = `
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
`;
document.head.appendChild(style);

// Generate table of contents
document.addEventListener('DOMContentLoaded', function() {
    const articleBody = document.querySelector('.ow-card .font-size\\:16px');
    if (!articleBody) return;
    
    const headings = articleBody.querySelectorAll('h2, h3');
    const toc = document.getElementById('tableOfContents');
    
    if (headings.length === 0 || !toc) return;
    
    let html = '';
    headings.forEach((heading, index) => {
        const id = 'heading-' + index;
        heading.id = id;
        const indent = heading.tagName === 'H3' ? 'margin-left: 16px;' : '';
        html += `<a href="#${id}" style="display: block; ${indent} color: #6b7280; text-decoration: none; transition: all .15s;" 
                       onmouseover="this.style.color='#185FA5';" 
                       onmouseout="this.style.color='#6b7280';">${heading.textContent}</a>`;
    });
    
    toc.innerHTML = html;
});

// Close share dropdown when clicking outside
document.addEventListener('click', function(event) {
    const dropdown = document.getElementById('shareDropdown');
    const shareButton = event.target.closest('button');
    if (!shareButton || !shareButton.contains(event.target)) {
        if (dropdown && !dropdown.classList.contains('d-none')) {
            dropdown.classList.add('d-none');
        }
    }
});

// Newsletter subscription with AJAX
const subscribeForm = document.getElementById('subscribeForm');
if (subscribeForm) {
    subscribeForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const submitBtn = document.getElementById('subscribeBtn');
        const originalText = submitBtn.textContent;
        submitBtn.textContent = 'Subscribing...';
        submitBtn.disabled = true;
        
        try {
            const formData = new FormData(this);
            const response = await fetch(this.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: formData,
            });
            
            const data = await response.json();
            
            if (data.success) {
                // Remove any existing success messages
                const existingMessages = document.querySelectorAll('.subscribe-success-message');
                existingMessages.forEach(msg => msg.remove());
                
                // Show success message above the form
                const successDiv = document.createElement('div');
                successDiv.className = 'subscribe-success-message';
                successDiv.style.cssText = 'background: rgba(255,255,255,0.2); color: #fff; padding: 16px; border-radius: 8px; margin-bottom: 24px; font-size: 13px; display: flex; align-items: flex-start; gap: 10px; border: 0.5px solid rgba(255,255,255,0.3); animation: fadeIn 0.3s ease;';
                successDiv.innerHTML = `
                    <svg width="18" height="18" viewBox="0 0 18 18" fill="none" style="flex-shrink: 0; margin-top: 2px;">
                        <circle cx="9" cy="9" r="7" stroke="currentColor" stroke-width="1.5"/>
                        <path d="M6 9L8 11L12 7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <div>
                        <div style="font-weight: 600; margin-bottom: 4px;">Subscribed Successfully!</div>
                        <div style="opacity: 0.95;">${data.message}</div>
                    </div>
                `;
                
                subscribeForm.parentNode.insertBefore(successDiv, subscribeForm);
                
                // Clear the form
                subscribeForm.reset();
                
                // Scroll to the newsletter section to show the success message
                const newsletterSection = document.getElementById('newsletter-section');
                if (newsletterSection) {
                    newsletterSection.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
                
                // Remove success message after 8 seconds
                setTimeout(() => {
                    successDiv.style.opacity = '0';
                    successDiv.style.transition = 'opacity 0.3s ease';
                    setTimeout(() => successDiv.remove(), 300);
                }, 8000);
                
            } else if (data.errors) {
                const errorMsg = data.errors.email ? data.errors.email[0] : 'Something went wrong. Please try again.';
                
                // Show error message
                const errorDiv = document.createElement('div');
                errorDiv.className = 'subscribe-success-message';
                errorDiv.style.cssText = 'background: rgba(255,255,255,0.2); color: #fff; padding: 16px; border-radius: 8px; margin-bottom: 24px; font-size: 13px; display: flex; align-items: flex-start; gap: 10px; border: 0.5px solid rgba(255,255,255,0.3); animation: fadeIn 0.3s ease;';
                errorDiv.innerHTML = `
                    <svg width="18" height="18" viewBox="0 0 18 18" fill="none" style="flex-shrink: 0; margin-top: 2px;">
                        <circle cx="9" cy="9" r="7" stroke="currentColor" stroke-width="1.5"/>
                        <path d="M9 5.5V9.5M9 12.5H9.01" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                    </svg>
                    <div>
                        <div style="font-weight: 600; margin-bottom: 4px;">Error</div>
                        <div style="opacity: 0.95;">${errorMsg}</div>
                    </div>
                `;
                
                subscribeForm.parentNode.insertBefore(errorDiv, subscribeForm);
                
                // Scroll to show the error
                const newsletterSection = document.getElementById('newsletter-section');
                if (newsletterSection) {
                    newsletterSection.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
                
                // Remove error message after 5 seconds
                setTimeout(() => {
                    errorDiv.style.opacity = '0';
                    errorDiv.style.transition = 'opacity 0.3s ease';
                    setTimeout(() => errorDiv.remove(), 300);
                }, 5000);
            }
        } catch (error) {
            console.error('Error subscribing:', error);
            alert('Failed to subscribe. Please try again.');
        } finally {
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;
        }
    });
}

// Scroll to newsletter if redirected from non-AJAX submission
@if(session('scrollTo'))
document.addEventListener('DOMContentLoaded', function() {
    const newsletter = document.getElementById('newsletter-section');
    if (newsletter) {
        setTimeout(() => {
            newsletter.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }, 500);
    }
});
@endif

// Scroll to comments if redirected from comment submission
@if(session('scrollToComments'))
document.addEventListener('DOMContentLoaded', function() {
    const commentsSection = document.getElementById('comments-section');
    if (commentsSection) {
        setTimeout(() => {
            commentsSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }, 500);
    }
});
@endif
</script>
@endsection