@extends('owner.layouts.app')

@php
    $pageTitle = 'Search Results - Knowledge Base';
@endphp

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid px-4">
            <div class="page-content-wrapper bg-white p-30 radius-20">
                {{-- Page Header --}}
                <div class="mb-4">
                    <h1 style="font-size: 22px; font-weight: 500; color: #111827; margin-bottom: 4px;">Search Knowledge Base</h1>
                    <nav aria-label="breadcrumb">
                        <ol style="display: flex; gap: 6px; font-size: 12px; color: #9ca3af; list-style: none; padding: 0; margin: 0;">
                            <li><a href="{{ route('owner.dashboard') }}" style="color: #185FA5; font-weight: 500;">Dashboard</a></li>
                            <li>
                                <svg width="8" height="8" viewBox="0 0 8 8" fill="none">
                                    <path d="M2.5 1.5L5.5 4L2.5 6.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </li>
                            <li><a href="{{ route('owner.kb.index') }}" style="color: #185FA5; font-weight: 500;">Knowledge Base</a></li>
                            <li>
                                <svg width="8" height="8" viewBox="0 0 8 8" fill="none">
                                    <path d="M2.5 1.5L5.5 4L2.5 6.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </li>
                            <li>Search Results</li>
                        </ol>
                    </nav>
                </div>

                {{-- Search Bar --}}
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="ow-card" style="background: #fff; border: 0.5px solid #e5e7eb; border-radius: 12px; padding: 20px; box-shadow: 0 4px 12px rgba(0,0,0,0.04), 0 0 0 1px rgba(24,95,165,0.05), 0 6px 18px rgba(24,95,165,0.06);">
                            <form action="{{ route('owner.kb.search') }}" method="GET">
                                <div style="position: relative; display: flex; gap: 12px; align-items: center;">
                                    <div style="position: relative; flex: 1;">
                                        <svg style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #9ca3af;" width="16" height="16" viewBox="0 0 16 16" fill="none">
                                            <circle cx="7" cy="7" r="5.5" stroke="currentColor" stroke-width="1.5"/>
                                            <path d="M11 11L14.5 14.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                        </svg>
                                        <input type="search" 
                                            name="q" 
                                            value="{{ $search ?? '' }}" 
                                            placeholder="Search articles, videos, documents..." 
                                            style="border: 0.5px solid #e5e7eb; border-radius: 7px; padding: 10px 15px 10px 40px; font-size: 14px; color: #374151; width: 100%; transition: all .15s;"
                                            onfocus="this.style.borderColor='#185FA5'; this.style.boxShadow='0 0 0 3px rgba(24,95,165,.1)';"
                                            onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none';">
                                    </div>
                                    <button type="submit" 
                                            style="background: #185FA5; color: #fff; display: inline-flex; align-items: center; gap: 6px; font-size: 13px; font-weight: 500; padding: 10px 20px; border-radius: 7px; border: none; cursor: pointer; transition: all .13s; white-space: nowrap;"
                                            onmouseover="this.style.background='#0F4A84'; this.style.transform='translateY(-1px)';" 
                                            onmouseout="this.style.background='#185FA5'; this.style.transform='translateY(0)';">
                                        <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                                            <circle cx="6" cy="6" r="4.5" stroke="currentColor" stroke-width="1.5"/>
                                            <path d="M9.5 9.5L12.5 12.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                        </svg>
                                        Search
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- Search Results Info --}}
                @if(isset($search) && $search)
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h2 style="font-size: 15px; font-weight: 600; color: #111827; margin: 0;">
                                    {{ $articles->total() }} {{ Str::plural('result', $articles->total()) }} for "<span style="color: #185FA5;">{{ $search }}</span>"
                                </h2>
                            </div>
                            @if($articles->total() > 0)
                            <div style="display: flex; gap: 8px;">
                                <select id="sortBy" 
                                        style="border: 0.5px solid #e5e7eb; border-radius: 7px; padding: 7px 10px; font-size: 13px; color: #374151; background: #fff; cursor: pointer; transition: all .15s;"
                                        onchange="window.location.href = '{{ route('owner.kb.search') }}?q={{ $search }}&sort=' + this.value;"
                                        onfocus="this.style.borderColor='#185FA5'; this.style.boxShadow='0 0 0 3px rgba(24,95,165,.1)';"
                                        onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none';">
                                    <option value="relevance" {{ request('sort') == 'relevance' ? 'selected' : '' }}>Most Relevant</option>
                                    <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>Newest First</option>
                                    <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>Oldest First</option>
                                    <option value="popular" {{ request('sort') == 'popular' ? 'selected' : '' }}>Most Viewed</option>
                                </select>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Search Results --}}
                <div class="row">
                    <div class="col-lg-8">
                        @forelse($articles as $article)
                        <div class="mb-3">
                            <a href="{{ route('owner.kb.article', $article->slug) }}" style="text-decoration: none;">
                                <div class="ow-card" style="background: #fff; border: 0.5px solid #185ea56e; border-radius: 12px; padding: 20px; transition: all .25s ease; box-shadow: 0 4px 12px rgba(0,0,0,0.04), 0 0 0 1px rgba(24,95,165,0.05), 0 6px 18px rgba(24,95,165,0.06); cursor: pointer;"
                                    onmouseover="this.style.borderColor='#185FA5'; this.style.transform='translateY(-3px)'; this.style.boxShadow='0 10px 25px rgba(0,0,0,0.06), 0 0 0 1px rgba(24,95,165,0.12), 0 12px 30px rgba(24,95,165,0.18)';"
                                    onmouseout="this.style.borderColor='#185ea56e'; this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.04), 0 0 0 1px rgba(24,95,165,0.05), 0 6px 18px rgba(24,95,165,0.06)';">
                                    
                                    <div class="d-flex align-items-start justify-content-between mb-3">
                                        <div class="d-flex align-items-center gap-2 flex-wrap">
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
                                        
                                        {{-- View Count --}}
                                        <div style="display: flex; align-items: center; gap: 4px; font-size: 12px; color: #9ca3af;">
                                            <svg width="12" height="12" viewBox="0 0 12 12" fill="none">
                                                <circle cx="6" cy="6" r="2" stroke="currentColor" stroke-width="1.2"/>
                                                <path d="M1 6C2.5 2.5 9.5 2.5 11 6C9.5 9.5 2.5 9.5 1 6Z" stroke="currentColor" stroke-width="1.2"/>
                                            </svg>
                                            {{ $article->views_owner }}
                                        </div>
                                    </div>

                                    {{-- Article Title with highlighted search terms --}}
                                    <h3 style="font-size: 16px; font-weight: 600; color: #185FA5; margin-bottom: 8px; line-height: 1.4;">
                                        {!! highlightSearchTerm($article->title, $search) !!}
                                    </h3>

                                    {{-- Excerpt with highlighted search terms --}}
                                    @if($article->excerpt)
                                    <p style="font-size: 13px; color: #6b7280; margin-bottom: 12px; line-height: 1.6;">
                                        {!! highlightSearchTerm($article->excerpt, $search) !!}
                                    </p>
                                    @endif

                                    {{-- Content Preview for article type --}}
                                    @if($article->type === 'article' && $article->body)
                                    <p style="font-size: 13px; color: #6b7280; margin-bottom: 12px; line-height: 1.6;">
                                        {!! highlightSearchTerm(Str::limit(strip_tags($article->body), 200), $search) !!}
                                    </p>
                                    @endif

                                    {{-- Meta Info --}}
                                    <div style="display: flex; justify-content: space-between; align-items: center; font-size: 12px; color: #9ca3af;">
                                        <span style="display: flex; align-items: center; gap: 4px;">
                                            <svg width="12" height="12" viewBox="0 0 12 12" fill="none">
                                                <circle cx="6" cy="6" r="4" stroke="currentColor" stroke-width="1.2"/>
                                                <path d="M6 3.5V6L7.5 7.5" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/>
                                            </svg>
                                            {{ $article->published_at->diffForHumans() }}
                                        </span>
                                        <span style="display: flex; align-items: center; gap: 4px;">
                                            <svg width="12" height="12" viewBox="0 0 12 12" fill="none">
                                                <path d="M8 4L5.5 6.5L4 5" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
                                                <rect x="1" y="1" width="10" height="10" rx="2" stroke="currentColor" stroke-width="1.2"/>
                                            </svg>
                                            Read Article →
                                        </span>
                                    </div>
                                </div>
                            </a>
                        </div>
                        @empty
                        <div class="ow-card text-center" style="background: #fff; border: 0.5px solid #e5e7eb; border-radius: 12px; padding: 60px 20px;">
                            <div style="margin-bottom: 16px;">
                                <svg width="64" height="64" viewBox="0 0 64 64" fill="none" style="color: #d1d5db;">
                                    <circle cx="27" cy="27" r="15" stroke="currentColor" stroke-width="2"/>
                                    <path d="M38 38L52 52" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                    <path d="M32 27H22M27 22V32" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                </svg>
                            </div>
                            <h3 style="font-size: 18px; font-weight: 600; color: #111827; margin-bottom: 8px;">No results found</h3>
                            <p style="font-size: 14px; color: #6b7280; margin-bottom: 20px;">
                                We couldn't find any articles matching "<strong style="color: #374151;">{{ $search }}</strong>"
                            </p>
                            <div style="font-size: 14px; color: #6b7280; margin-bottom: 20px;">
                                <p style="margin-bottom: 8px;">Suggestions:</p>
                                <ul style="list-style: none; padding: 0; margin: 0;">
                                    <li style="margin-bottom: 4px;">• Check your spelling</li>
                                    <li style="margin-bottom: 4px;">• Try more general keywords</li>
                                    <li style="margin-bottom: 4px;">• Try different keywords</li>
                                    <li>• Browse categories instead</li>
                                </ul>
                            </div>
                            <a href="{{ route('owner.kb.index') }}" class="ow-btn" 
                            style="background: #185FA5; color: #fff; display: inline-flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 500; padding: 7px 15px; border-radius: 7px; border: none; cursor: pointer; text-decoration: none; transition: all .13s;"
                            onmouseover="this.style.background='#0F4A84'; this.style.transform='translateY(-1px)';" 
                            onmouseout="this.style.background='#185FA5'; this.style.transform='translateY(0)';">
                                <svg width="13" height="13" viewBox="0 0 13 13" fill="none">
                                    <path d="M8.5 2.5L4.5 6.5L8.5 10.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                Browse Knowledge Base
                            </a>
                        </div>
                        @endforelse

                        {{-- Pagination --}}
                        @if($articles->hasPages())
                        <div class="mt-4">
                            <div style="border-top: 0.5px solid #e5e7eb; background: #fafafa; padding: 12px 20px; border-radius: 12px; display: flex; justify-content: flex-end;">
                                {{ $articles->appends(['q' => $search, 'sort' => request('sort')])->links() }}
                            </div>
                        </div>
                        @endif
                    </div>

                    {{-- Sidebar with Categories --}}
                    <div class="col-lg-4">
                        {{-- Quick Links --}}
                        <div class="ow-card mb-4" style="background: #fff; border: 0.5px solid #e5e7eb; border-radius: 12px; padding: 20px; box-shadow: 0 4px 12px rgba(0,0,0,0.04), 0 0 0 1px rgba(24,95,165,0.05), 0 6px 18px rgba(24,95,165,0.06);">
                            <h3 style="font-size: 14px; font-weight: 600; color: #111827; margin-bottom: 12px;">Browse Categories</h3>
                            @php
                                $categories = App\Models\KnowledgeBaseCategory::active()
                                    ->forAudience('owners')
                                    ->orderBy('name')
                                    ->limit(10)
                                    ->get();
                            @endphp
                            <div style="display: flex; flex-direction: column; gap: 8px;">
                                @foreach($categories as $category)
                                <a href="{{ route('owner.kb.category', $category->slug) }}" 
                                style="display: flex; align-items: center; gap: 8px; padding: 8px 12px; border-radius: 7px; text-decoration: none; transition: all .15s; font-size: 13px; color: #374151;"
                                onmouseover="this.style.background='#f3f4f6';" 
                                onmouseout="this.style.background='transparent';">
                                    @if($category->icon)
                                    <span style="font-size: 16px;">{!! $category->icon !!}</span>
                                    @endif
                                    <span>{{ $category->name }}</span>
                                    <svg width="12" height="12" viewBox="0 0 12 12" fill="none" style="margin-left: auto; color: #9ca3af;">
                                        <path d="M4.5 2.5L8 6L4.5 9.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </a>
                                @endforeach
                                @if($categories->count() >= 10)
                                <a href="{{ route('owner.kb.index') }}" 
                                style="display: block; text-align: center; padding: 8px; color: #185FA5; font-size: 13px; font-weight: 500; text-decoration: none; transition: all .15s;"
                                onmouseover="this.style.color='#0F4A84';" 
                                onmouseout="this.style.color='#185FA5';">
                                    View all categories →
                                </a>
                                @endif
                            </div>
                        </div>

                        {{-- Popular Articles --}}
                        <div class="ow-card" style="background: #fff; border: 0.5px solid #e5e7eb; border-radius: 12px; padding: 20px; box-shadow: 0 4px 12px rgba(0,0,0,0.04), 0 0 0 1px rgba(24,95,165,0.05), 0 6px 18px rgba(24,95,165,0.06);">
                            <h3 style="font-size: 14px; font-weight: 600; color: #111827; margin-bottom: 12px;">Popular Articles</h3>
                            @php
                                $popularArticles = App\Models\KnowledgeBaseArticle::published()
                                    ->forAudience('owners')
                                    ->orderBy('views_owner', 'desc')
                                    ->limit(5)
                                    ->get();
                            @endphp
                            <div style="display: flex; flex-direction: column; gap: 12px;">
                                @foreach($popularArticles as $popular)
                                <a href="{{ route('owner.kb.article', $popular->slug) }}" 
                                style="text-decoration: none; display: block;"
                                class="hover-card">
                                    <div style="font-size: 13px; font-weight: 500; color: #185FA5; margin-bottom: 4px; line-height: 1.4;">
                                        {{ $popular->title }}
                                    </div>
                                    <div style="font-size: 11px; color: #9ca3af; display: flex; align-items: center; gap: 4px;">
                                        <svg width="10" height="10" viewBox="0 0 10 10" fill="none">
                                            <circle cx="5" cy="5" r="3.5" stroke="currentColor" stroke-width="1.2"/>
                                            <path d="M5 3V5L6 6" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/>
                                        </svg>
                                        {{ $popular->views_owner }} views
                                    </div>
                                </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                @else
                <div class="row">
                    <div class="col-12">
                        <div class="ow-card text-center" style="background: #fff; border: 0.5px solid #e5e7eb; border-radius: 12px; padding: 60px 20px;">
                            <div style="margin-bottom: 16px;">
                                <svg width="48" height="48" viewBox="0 0 48 48" fill="none" style="color: #d1d5db;">
                                    <circle cx="20" cy="20" r="12" stroke="currentColor" stroke-width="2"/>
                                    <path d="M29 29L42 42" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                </svg>
                            </div>
                            <h3 style="font-size: 16px; font-weight: 500; color: #6b7280;">Enter a search term to find articles</h3>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Custom Pagination Styling --}}
<style>
.pagination {
    display: flex;
    gap: 4px;
    list-style: none;
    margin: 0;
    padding: 0;
}

.pagination .page-link {
    min-width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 7px;
    border: 0.5px solid #e5e7eb;
    background: #fff;
    color: #374151;
    font-size: 13px;
    font-weight: 500;
    text-decoration: none;
    transition: all .15s;
    padding: 0 8px;
}

.pagination .page-link:hover {
    background: #f3f4f6;
    border-color: #185FA5;
}

.pagination .active .page-link {
    background: #185FA5;
    color: #fff;
    border-color: #185FA5;
}

.pagination .disabled .page-link {
    color: #d1d5db;
    background: #fafafa;
    pointer-events: none;
}

.highlight {
    background: #FFF3CD;
    padding: 1px 3px;
    border-radius: 2px;
    font-weight: 500;
}

.hover-card:hover {
    opacity: 0.8;
}
</style>
@endsection

@php
function highlightSearchTerm($text, $search) {
    if (!$search) return e($text);
    
    $searchTerms = explode(' ', $search);
    $text = e($text);
    
    foreach ($searchTerms as $term) {
        if (strlen($term) > 1) {
            $text = preg_replace('/(' . preg_quote($term, '/') . ')/i', '<span class="highlight">$1</span>', $text);
        }
    }
    
    return $text;
}
@endphp