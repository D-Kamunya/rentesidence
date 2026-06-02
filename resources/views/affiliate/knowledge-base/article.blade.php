@extends('affiliate.layouts.app')

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
                        <li><a href="{{ route('affiliate.dashboard') }}" style="color: #185FA5; font-weight: 500;">Dashboard</a></li>
                        <li>
                            <svg width="8" height="8" viewBox="0 0 8 8" fill="none">
                                <path d="M2.5 1.5L5.5 4L2.5 6.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </li>
                        <li><a href="{{ route('affiliate.kb.index') }}" style="color: #185FA5; font-weight: 500;">Knowledge Base</a></li>
                        @if($article->category)
                        <li>
                            <svg width="8" height="8" viewBox="0 0 8 8" fill="none">
                                <path d="M2.5 1.5L5.5 4L2.5 6.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </li>
                        <li><a href="{{ route('affiliate.kb.category', $article->category->slug) }}" style="color: #185FA5; font-weight: 500;">{{ $article->category->name }}</a></li>
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
                        <div class="ow-card mb-4" style="background: #fff; border: 0.5px solid #e5e7eb; border-radius: 12px; overflow: hidden; padding: 30px; box-shadow: 0 4px 12px rgba(0,0,0,0.04), 0 0 0 1px rgba(24,95,165,0.05), 0 6px 18px rgba(24,95,165,0.06);">
                            {{-- Article Header --}}
                            <div class="mb-4">
                                <div class="d-flex align-items-center gap-3 mb-3">
                                    {{-- Type Badge --}}
                                    @if($article->type === 'video')
                                    <span class="ow-badge" style="background: #E6F1FB; color: #0C447C; display: inline-flex; align-items: center; gap: 4px; font-size: 11px; font-weight: 500; padding: 3px 9px; border-radius: 99px; border: 0.5px solid #B5D4F4;">
                                        <svg width="10" height="10" viewBox="0 0 10 10" fill="none">
                                            <path d="M2 2L8 5L2 8V2Z" fill="currentColor"/>
                                        </svg>
                                        Video
                                    </span>
                                    @elseif($article->type === 'document')
                                    <span class="ow-badge" style="background: #FAEEDA; color: #854F0B; display: inline-flex; align-items: center; gap: 4px; font-size: 11px; font-weight: 500; padding: 3px 9px; border-radius: 99px; border: 0.5px solid #F5D9A8;">
                                        <svg width="10" height="10" viewBox="0 0 10 10" fill="none">
                                            <path d="M3 2H7L8 3.5V8.5C8 8.77614 7.77614 9 7.5 9H2.5C2.22386 9 2 8.77614 2 8.5V2.5C2 2.22386 2.22386 2 2.5 2H3Z" stroke="currentColor" stroke-width="1.2" fill="none"/>
                                        </svg>
                                        Document
                                    </span>
                                    @elseif($article->type === 'link')
                                    <span class="ow-badge" style="background: #F3F4F6; color: #6b7280; display: inline-flex; align-items: center; gap: 4px; font-size: 11px; font-weight: 500; padding: 3px 9px; border-radius: 99px; border: 0.5px solid #e5e7eb;">
                                        <svg width="10" height="10" viewBox="0 0 10 10" fill="none">
                                            <path d="M6 1H9V4M9 1L5.5 4.5M4 1H2.5C2.22386 1 2 1.22386 2 1.5V7.5C2 7.77614 2.22386 8 2.5 8H8.5C8.77614 8 9 7.77614 9 7.5V6" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                        Link
                                    </span>
                                    @else
                                    <span class="ow-badge" style="background: #E6F1FB; color: #0C447C; display: inline-flex; align-items: center; gap: 4px; font-size: 11px; font-weight: 500; padding: 3px 9px; border-radius: 99px; border: 0.5px solid #B5D4F4;">
                                        <svg width="10" height="10" viewBox="0 0 10 10" fill="none">
                                            <path d="M2.5 3H7.5M2.5 5H7.5M2.5 7H5" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/>
                                        </svg>
                                        Article
                                    </span>
                                    @endif
                                    
                                    {{-- Category Badge --}}
                                    @if($article->category)
                                    <span class="ow-badge" style="background: #F3F4F6; color: #6b7280; display: inline-flex; align-items: center; gap: 4px; font-size: 11px; font-weight: 500; padding: 3px 9px; border-radius: 99px; border: 0.5px solid #e5e7eb;">
                                        <svg width="10" height="10" viewBox="0 0 10 10" fill="none">
                                            <path d="M2 2.5H4L4.5 3.5H8C8.27614 3.5 8.5 3.72386 8.5 4V7.5C8.5 7.77614 8.27614 8 8 8H2C1.72386 8 1.5 7.77614 1.5 7.5V3C1.5 2.72386 1.72386 2.5 2 2.5Z" stroke="currentColor" stroke-width="1.2" fill="none"/>
                                        </svg>
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
                                <div style="margin-bottom: 16px;">
                                    <svg width="64" height="64" viewBox="0 0 64 64" fill="none" style="color: #185FA5;">
                                        <path d="M36 8H20C17.7909 8 16 9.79086 16 12V52C16 54.2091 17.7909 56 20 56H44C46.2091 56 48 54.2091 48 52V20L36 8Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M36 8V20H48" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M22 36H42M22 44H34" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                    </svg>
                                </div>
                                <h3 style="font-size: 16px; font-weight: 600; color: #111827; margin-bottom: 8px;">
                                    {{ $article->document_original_name }}
                                </h3>
                                <p style="font-size: 13px; color: #6b7280; margin-bottom: 24px;">
                                    {{ number_format($article->document_size / 1024, 1) }} KB
                                </p>
                                <a href="{{ route('affiliate.kb.document.download', $article) }}" 
                                class="ow-btn" 
                                style="background: #185FA5; color: #fff; display: inline-flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 500; padding: 10px 20px; border-radius: 7px; border: none; cursor: pointer; text-decoration: none; transition: all .13s;"
                                onmouseover="this.style.background='#0F4A84'; this.style.transform='translateY(-1px)';" 
                                onmouseout="this.style.background='#185FA5'; this.style.transform='translateY(0)';">
                                    <svg width="13" height="13" viewBox="0 0 13 13" fill="none">
                                        <path d="M2.5 9V10.5C2.5 11.0523 2.94772 11.5 3.5 11.5H9.5C10.0523 11.5 10.5 11.0523 10.5 10.5V9M6.5 9V2M6.5 2L4 4.5M6.5 2L9 4.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    Download Document
                                </a>
                            </div>
                            @if($article->excerpt)
                            <p style="font-size: 14px; color: #6b7280; line-height: 1.6; margin-top: 20px;">
                                {{ $article->excerpt }}
                            </p>
                            @endif

                            @elseif($article->type === 'link')
                            <div style="text-align: center; padding: 40px 20px;">
                                <div style="margin-bottom: 16px;">
                                    <svg width="64" height="64" viewBox="0 0 64 64" fill="none" style="color: #185FA5;">
                                        <path d="M32 20H20C17.7909 20 16 21.7909 16 24V44C16 46.2091 17.7909 48 20 48H40C42.2091 48 44 46.2091 44 44V32" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                        <path d="M48 16L28 36M48 16H36M48 16V28" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </div>
                                <p style="font-size: 14px; color: #6b7280; margin-bottom: 24px;">
                                    {{ $article->excerpt }}
                                </p>
                                <a href="{{ $article->external_url }}" target="_blank" 
                                class="ow-btn" 
                                style="background: #185FA5; color: #fff; display: inline-flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 500; padding: 10px 20px; border-radius: 7px; border: none; cursor: pointer; text-decoration: none; transition: all .13s;"
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
                                    <a href="{{ route('affiliate.kb.article', $related->slug) }}" style="text-decoration: none;">
                                        <div class="ow-card" style="background: #fff; border: 0.5px solid #185ea56e; border-radius: 12px; padding: 16px; transition: all .25s ease; box-shadow: 0 4px 12px rgba(0,0,0,0.04), 0 0 0 1px rgba(24,95,165,0.05), 0 6px 18px rgba(24,95,165,0.06); cursor: pointer;"
                                            onmouseover="this.style.borderColor='#185FA5'; this.style.transform='translateY(-3px)'; this.style.boxShadow='0 10px 25px rgba(0,0,0,0.06), 0 0 0 1px rgba(24,95,165,0.12), 0 12px 30px rgba(24,95,165,0.18)';"
                                            onmouseout="this.style.borderColor='#185ea56e'; this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.04), 0 0 0 1px rgba(24,95,165,0.05), 0 6px 18px rgba(24,95,165,0.06)';">
                                            <div class="d-flex align-items-center gap-2 mb-2">
                                                @if($related->type === 'video')
                                                <span class="ow-badge" style="background: #E6F1FB; color: #0C447C; font-size: 10px; padding: 2px 6px; border-radius: 99px; border: 0.5px solid #B5D4F4;">Video</span>
                                                @elseif($related->type === 'document')
                                                <span class="ow-badge" style="background: #FAEEDA; color: #854F0B; font-size: 10px; padding: 2px 6px; border-radius: 99px; border: 0.5px solid #F5D9A8;">Doc</span>
                                                @endif
                                            </div>
                                            <h4 style="font-size: 14px; font-weight: 500; color: #185FA5; margin-bottom: 4px;">{{ $related->title }}</h4>
                                            <span style="font-size: 12px; color: #9ca3af;">{{ $related->published_at->diffForHumans() }}</span>
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
                        {{-- Article Info --}}
                        <div class="ow-card mb-4" style="background: #fff; border: 0.5px solid #e5e7eb; border-radius: 12px; padding: 20px; box-shadow: 0 4px 12px rgba(0,0,0,0.04), 0 0 0 1px rgba(24,95,165,0.05), 0 6px 18px rgba(24,95,165,0.06);">
                            <h3 style="font-size: 14px; font-weight: 600; color: #111827; margin-bottom: 12px;">Article Info</h3>
                            <div style="font-size: 13px; color: #6b7280; line-height: 2;">
                                <div style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 0.5px solid #f3f4f6;">
                                    <span>Type</span>
                                    <span style="font-weight: 500; color: #374151;">{{ $article->type_label }}</span>
                                </div>
                                @if($article->category)
                                <div style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 0.5px solid #f3f4f6;">
                                    <span>Category</span>
                                    <a href="{{ route('affiliate.kb.category', $article->category->slug) }}" style="font-weight: 500; color: #185FA5; text-decoration: none;">
                                        {{ $article->category->name }}
                                    </a>
                                </div>
                                @endif
                                <div style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 0.5px solid #f3f4f6;">
                                    <span>Published</span>
                                    <span style="font-weight: 500; color: #374151;">{{ $article->published_at->format('M d, Y') }}</span>
                                </div>
                                <div style="display: flex; justify-content: space-between; padding: 8px 0;">
                                    <span>Views</span>
                                    <span style="font-weight: 500; color: #374151;">{{ $article->views_affiliate }}</span>
                                </div>
                            </div>
                        </div>

                        {{-- More in Category --}}
                        @if($article->category)
                        <div class="ow-card mb-4" style="background: #fff; border: 0.5px solid #e5e7eb; border-radius: 12px; padding: 20px; box-shadow: 0 4px 12px rgba(0,0,0,0.04), 0 0 0 1px rgba(24,95,165,0.05), 0 6px 18px rgba(24,95,165,0.06);">
                            <h3 style="font-size: 14px; font-weight: 600; color: #111827; margin-bottom: 8px;">
                                More in {{ $article->category->name }}
                            </h3>
                            <a href="{{ route('affiliate.kb.category', $article->category->slug) }}" 
                            style="color: #185FA5; font-size: 13px; font-weight: 500; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;"
                            onmouseover="this.style.color='#0F4A84';" 
                            onmouseout="this.style.color='#185FA5';">
                                View all articles in this category
                                <svg width="12" height="12" viewBox="0 0 12 12" fill="none">
                                    <path d="M4.5 2.5L8 6L4.5 9.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </a>
                        </div>
                        @endif

                        {{-- Need Help --}}
                        <div class="ow-card" style="background: linear-gradient(135deg, #E6F1FB 0%, #FFFFFF 100%); border: 0.5px solid #B5D4F4; border-radius: 12px; padding: 20px; box-shadow: 0 4px 12px rgba(0,0,0,0.04), 0 0 0 1px rgba(24,95,165,0.05), 0 6px 18px rgba(24,95,165,0.06);">
                            <div style="text-align: center;">
                                <div style="margin-bottom: 12px;">
                                    <svg width="40" height="40" viewBox="0 0 40 40" fill="none" style="color: #185FA5;">
                                        <circle cx="20" cy="20" r="15" stroke="currentColor" stroke-width="1.5"/>
                                        <path d="M20 25V25.02M20 15V21" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                    </svg>
                                </div>
                                <h3 style="font-size: 14px; font-weight: 600; color: #111827; margin-bottom: 8px;">Need More Help?</h3>
                                <p style="font-size: 13px; color: #6b7280; margin-bottom: 16px; line-height: 1.5;">
                                    Can't find what you're looking for? Browse our complete knowledge base or contact support.
                                </p>
                                <div style="display: flex; gap: 8px; justify-content: center;">
                                    <a href="{{ route('affiliate.kb.index') }}" 
                                    style="background: #f3f4f6; color: #374151; display: inline-flex; align-items: center; gap: 4px; font-size: 12px; font-weight: 500; padding: 7px 15px; border-radius: 7px; border: 0.5px solid #e5e7eb; cursor: pointer; text-decoration: none; transition: all .13s;"
                                    onmouseover="this.style.background='#e5e7eb';" 
                                    onmouseout="this.style.background='#f3f4f6';">
                                        Browse All
                                    </a>
                                    <a href="{{ route('affiliate.dashboard') }}" 
                                    style="background: #185FA5; color: #fff; display: inline-flex; align-items: center; gap: 4px; font-size: 12px; font-weight: 500; padding: 7px 15px; border-radius: 7px; border: none; cursor: pointer; text-decoration: none; transition: all .13s;"
                                    onmouseover="this.style.background='#0F4A84';" 
                                    onmouseout="this.style.background='#185FA5';">
                                        Contact Support
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Additional Styles --}}
<style>
/* Link hover states */
a:hover {
    opacity: 0.85;
}

/* Smooth transitions for card hover */
.ow-card {
    transition: all .25s ease;
}
</style>
@endsection