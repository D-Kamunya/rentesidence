@extends('owner.layouts.app')

@php
    $pageTitle = 'Knowledge Base Article - ' . $article->title;
@endphp

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid px-4">
            <div class="page-content-wrapper bg-white p-30 radius-20">
                {{-- Breadcrumb --}}
                <nav aria-label="breadcrumb" class="mb-4">
                    <ol style="display: flex; gap: 6px; font-size: 12px; color: #9ca3af; list-style: none; padding: 0; margin: 0;">
                        <li><a href="{{ route('owner.dashboard') }}" style="color: #185FA5; font-weight: 500;">Dashboard</a></li>
                        <li>
                            <svg width="8" height="8" viewBox="0 0 8 8" fill="none">
                                <path d="M2.5 1.5L5.5 4L2.5 6.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </li>
                        <li><a href="{{ route('owner.kb.index') }}" style="color: #185FA5; font-weight: 500;">Knowledge Base</a></li>
                        @if($article->category)
                        <li>
                            <svg width="8" height="8" viewBox="0 0 8 8" fill="none">
                                <path d="M2.5 1.5L5.5 4L2.5 6.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </li>
                        <li><a href="{{ route('owner.kb.category', $article->category->slug) }}" style="color: #185FA5; font-weight: 500;">{{ $article->category->name }}</a></li>
                        @endif
                        <li>
                            <svg width="8" height="8" viewBox="0 0 8 8" fill="none">
                                <path d="M2.5 1.5L5.5 4L2.5 6.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </li>
                        <li>{{ $article->title }}</li>
                    </ol>
                </nav>

                {{-- Article Content --}}
                <div class="row">
                    <div class="col-lg-8">
                        <div class="ow-card mb-4" style="background: #fff; border: 0.5px solid #e5e7eb; border-radius: 12px; overflow: hidden; padding: 30px;">
                            {{-- Article Header --}}
                            <div class="mb-4">
                                <div class="d-flex align-items-center gap-3 mb-3">
                                    <span class="ow-badge" style="background: #E6F1FB; color: #0C447C; display: inline-flex; align-items: center; gap: 4px; font-size: 11px; font-weight: 500; padding: 3px 9px; border-radius: 99px; border: 0.5px solid #B5D4F4;">
                                        {{ $article->type_label }}
                                    </span>
                                    @if($article->category)
                                    <span class="ow-badge" style="background: #F3F4F6; color: #6b7280; display: inline-flex; align-items: center; gap: 4px; font-size: 11px; font-weight: 500; padding: 3px 9px; border-radius: 99px; border: 0.5px solid #e5e7eb;">
                                        {{ $article->category->name }}
                                    </span>
                                    @endif
                                </div>
                                
                                <h1 style="font-size: 22px; font-weight: 500; color: #111827; margin-bottom: 8px;">
                                    {{ $article->title }}
                                </h1>
                                
                                <div style="font-size: 12px; color: #9ca3af;">
                                    Last updated {{ $article->updated_at->diffForHumans() }}
                                </div>
                            </div>

                            {{-- Article Body --}}
                            @if($article->type === 'article')
                            <div style="font-size: 14px; line-height: 1.8; color: #374151;">
                                {!! $article->body !!}
                            </div>
                            
                            @elseif($article->type === 'video')
                            <div class="mb-4" style="position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden; border-radius: 12px;">
                                @php
                                    $embedUrl = $article->video_url;
                                    if (str_contains($article->video_url, 'youtube.com/watch')) {
                                        parse_str(parse_url($article->video_url, PHP_URL_QUERY), $params);
                                        $embedUrl = 'https://www.youtube.com/embed/' . ($params['v'] ?? '');
                                    } elseif (str_contains($article->video_url, 'youtu.be/')) {
                                        $videoId = explode('/', parse_url($article->video_url, PHP_URL_PATH))[1] ?? '';
                                        $embedUrl = 'https://www.youtube.com/embed/' . $videoId;
                                    }
                                @endphp
                                <iframe src="{{ $embedUrl }}" 
                                        style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: none; border-radius: 12px;" 
                                        allowfullscreen>
                                </iframe>
                            </div>
                            @if($article->excerpt)
                            <p style="font-size: 14px; color: #6b7280; line-height: 1.6;">
                                {{ $article->excerpt }}
                            </p>
                            @endif

                            @elseif($article->type === 'document')
                            <div style="text-align: center; padding: 40px 20px;">
                                <svg width="48" height="48" viewBox="0 0 48 48" fill="none" style="color: #185FA5; margin-bottom: 16px;">
                                    <path d="M28 4H12C9.79086 4 8 5.79086 8 8V40C8 42.2091 9.79086 44 12 44H36C38.2091 44 40 42.2091 40 40V16L28 4Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M28 4V16H40" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                <h3 style="font-size: 16px; font-weight: 600; color: #111827; margin-bottom: 8px;">{{ $article->document_original_name }}</h3>
                                <p style="font-size: 13px; color: #6b7280; margin-bottom: 16px;">
                                    {{ number_format($article->document_size / 1024, 1) }} KB
                                </p>
                                <a href="{{ route('owner.kb.document.download', $article) }}" 
                                class="ow-btn" style="background: #185FA5; color: #fff; display: inline-flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 500; padding: 7px 15px; border-radius: 7px; border: none; cursor: pointer; text-decoration: none; transition: all .13s;"
                                onmouseover="this.style.background='#0F4A84'; this.style.transform='translateY(-1px)';" 
                                onmouseout="this.style.background='#185FA5'; this.style.transform='translateY(0)';">
                                    <svg width="13" height="13" viewBox="0 0 13 13" fill="none">
                                        <path d="M2.5 9V10.5C2.5 11.0523 2.94772 11.5 3.5 11.5H9.5C10.0523 11.5 10.5 11.0523 10.5 10.5V9M6.5 9V2M6.5 2L4 4.5M6.5 2L9 4.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    Download Document
                                </a>
                            </div>
                            @if($article->excerpt)
                            <p style="font-size: 14px; color: #6b7280; line-height: 1.6;">
                                {{ $article->excerpt }}
                            </p>
                            @endif

                            @elseif($article->type === 'link')
                            <div style="text-align: center; padding: 40px 20px;">
                                <p style="font-size: 14px; color: #6b7280; margin-bottom: 16px;">
                                    {{ $article->excerpt }}
                                </p>
                                <a href="{{ $article->external_url }}" target="_blank" 
                                class="ow-btn" style="background: #185FA5; color: #fff; display: inline-flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 500; padding: 7px 15px; border-radius: 7px; border: none; cursor: pointer; text-decoration: none; transition: all .13s;"
                                onmouseover="this.style.background='#0F4A84'; this.style.transform='translateY(-1px)';" 
                                onmouseout="this.style.background='#185FA5'; this.style.transform='translateY(0)';">
                                    <svg width="13" height="13" viewBox="0 0 13 13" fill="none">
                                        <path d="M9.5 3.5V9.5M9.5 3.5H3.5M9.5 3.5L3.5 9.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    Visit External Link
                                </a>
                            </div>
                            @endif
                        </div>

                        {{-- Related Articles --}}
                        @if($relatedArticles->count() > 0)
                        <div class="mb-4">
                            <h3 style="font-size: 15px; font-weight: 600; color: #111827; margin-bottom: 16px;">Related Articles</h3>
                            <div class="row">
                                @foreach($relatedArticles as $related)
                                <div class="col-md-6 mb-3">
                                    <a href="{{ route('owner.kb.article', $related->slug) }}" style="text-decoration: none;">
                                        <div class="ow-card" style="background: #fff; border: 0.5px solid #185ea56e; border-radius: 12px; padding: 16px; transition: all .25s ease; box-shadow: 0 4px 12px rgba(0,0,0,0.04), 0 0 0 1px rgba(24,95,165,0.05), 0 6px 18px rgba(24,95,165,0.06); cursor: pointer;"
                                            onmouseover="this.style.borderColor='#185FA5'; this.style.transform='translateY(-3px)'; this.style.boxShadow='0 10px 25px rgba(0,0,0,0.06), 0 0 0 1px rgba(24,95,165,0.12), 0 12px 30px rgba(24,95,165,0.18)';"
                                            onmouseout="this.style.borderColor='#185ea56e'; this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.04), 0 0 0 1px rgba(24,95,165,0.05), 0 6px 18px rgba(24,95,165,0.06)';">
                                            <h4 style="font-size: 14px; font-weight: 500; color: #185FA5; margin-bottom: 4px;">{{ $related->title }}</h4>
                                            <span style="font-size: 12px; color: #9ca3af;">{{ $related->type_label }}</span>
                                        </div>
                                    </a>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>

                    {{-- Sidebar --}}
                    <div class="col-lg-4">
                        <div class="ow-card mb-4" style="background: #fff; border: 0.5px solid #e5e7eb; border-radius: 12px; padding: 20px;">
                            <h3 style="font-size: 14px; font-weight: 600; color: #111827; margin-bottom: 12px;">Article Info</h3>
                            <div style="font-size: 13px; color: #6b7280; line-height: 2;">
                                <div><strong>Type:</strong> {{ $article->type_label }}</div>
                                <div><strong>Category:</strong> {{ $article->category->name ?? 'None' }}</div>
                                <div><strong>Published:</strong> {{ $article->published_at->format('M d, Y') }}</div>
                                <div><strong>Views:</strong> {{ $article->views_owner }}</div>
                            </div>
                        </div>

                        @if($article->category)
                        <div class="ow-card" style="background: #fff; border: 0.5px solid #e5e7eb; border-radius: 12px; padding: 20px;">
                            <h3 style="font-size: 14px; font-weight: 600; color: #111827; margin-bottom: 8px;">More in {{ $article->category->name }}</h3>
                            <a href="{{ route('owner.kb.category', $article->category->slug) }}" 
                            style="color: #185FA5; font-size: 13px; font-weight: 500; text-decoration: none;">
                                View all articles →
                            </a>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection