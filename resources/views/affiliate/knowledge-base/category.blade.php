@extends('affiliate.layouts.app')

@php
    $pageTitle = 'Knowledge Base - ' . $category->name;
@endphp

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid px-4">
            <div class="page-content-wrapper bg-white p-30 radius-20">
                {{-- Page Header --}}
                <div class="mb-4">
                    <h1 style="font-size: 22px; font-weight: 500; color: #111827; margin-bottom: 4px;">{{ $category->name }}</h1>
                    <nav aria-label="breadcrumb">
                        <ol style="display: flex; gap: 6px; font-size: 12px; color: #9ca3af; list-style: none; padding: 0; margin: 0;">
                            <li><a href="{{ route('affiliate.dashboard') }}" style="color: #185FA5; font-weight: 500;">Dashboard</a></li>
                            <li>
                                <svg width="8" height="8" viewBox="0 0 8 8" fill="none">
                                    <path d="M2.5 1.5L5.5 4L2.5 6.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </li>
                            <li><a href="{{ route('affiliate.kb.index') }}" style="color: #185FA5; font-weight: 500;">Knowledge Base</a></li>
                            <li>
                                <svg width="8" height="8" viewBox="0 0 8 8" fill="none">
                                    <path d="M2.5 1.5L5.5 4L2.5 6.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </li>
                            <li>{{ $category->name }}</li>
                        </ol>
                    </nav>
                </div>

                {{-- Category Info --}}
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="ow-card" style="background: #fff; border: 0.5px solid #e5e7eb; border-radius: 12px; padding: 20px; box-shadow: 0 4px 12px rgba(0,0,0,0.04), 0 0 0 1px rgba(24,95,165,0.05), 0 6px 18px rgba(24,95,165,0.06);">
                            <div class="d-flex align-items-start justify-content-between">
                                <div class="d-flex align-items-center gap-3">
                                    @if($category->icon)
                                    <span style="font-size: 28px;">{!! $category->icon !!}</span>
                                    @endif
                                    <div>
                                        @if($category->description)
                                        <p style="font-size: 14px; color: #6b7280; margin: 0; line-height: 1.5;">
                                            {{ $category->description }}
                                        </p>
                                        @endif
                                        <div style="font-size: 12.5px; color: #6b7280; margin-top: 4px;">
                                            <strong>{{ $articles->total() }}</strong> articles
                                        </div>
                                    </div>
                                </div>
                                <a href="{{ route('affiliate.kb.index') }}" class="ow-btn" 
                                style="background: #f3f4f6; color: #374151; display: inline-flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 500; padding: 7px 15px; border-radius: 7px; border: 0.5px solid #e5e7eb; cursor: pointer; text-decoration: none; transition: all .13s;"
                                onmouseover="this.style.background='#e5e7eb';" 
                                onmouseout="this.style.background='#f3f4f6';">
                                    <svg width="13" height="13" viewBox="0 0 13 13" fill="none">
                                        <path d="M8.5 2.5L4.5 6.5L8.5 10.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    All Categories
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Search within category --}}
                <div class="row mb-4">
                    <div class="col-12">
                        <div style="position: relative; display: inline-block;">
                            <svg style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: #9ca3af;" width="14" height="14" viewBox="0 0 14 14" fill="none">
                                <circle cx="6" cy="6" r="4.5" stroke="currentColor" stroke-width="1.5"/>
                                <path d="M9.5 9.5L12.5 12.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            </svg>
                            <input type="search" 
                                id="categorySearch" 
                                placeholder="Search in {{ $category->name }}..." 
                                style="border: 0.5px solid #e5e7eb; border-radius: 7px; padding: 7px 10px 7px 34px; font-size: 13px; color: #374151; width: 260px; transition: all .15s;"
                                onfocus="this.style.borderColor='#185FA5'; this.style.boxShadow='0 0 0 3px rgba(24,95,165,.1)';"
                                onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none';">
                        </div>
                    </div>
                </div>

                {{-- Articles Grid --}}
                <div class="row" id="articlesGrid">
                    @forelse($articles as $article)
                    <div class="col-md-6 col-lg-4 mb-4 article-card" data-title="{{ strtolower($article->title) }}" data-excerpt="{{ strtolower($article->excerpt) }}">
                        <a href="{{ route('affiliate.kb.article', $article->slug) }}" style="text-decoration: none;">
                            <div class="ow-card" style="background: #fff; border: 0.5px solid #185ea56e; border-radius: 12px; overflow: hidden; padding: 20px; transition: all .25s ease; box-shadow: 0 4px 12px rgba(0,0,0,0.04), 0 0 0 1px rgba(24,95,165,0.05), 0 6px 18px rgba(24,95,165,0.06); cursor: pointer; height: 100%;"
                                onmouseover="this.style.borderColor='#185FA5'; this.style.transform='translateY(-3px)'; this.style.boxShadow='0 10px 25px rgba(0,0,0,0.06), 0 0 0 1px rgba(24,95,165,0.12), 0 12px 30px rgba(24,95,165,0.18)';"
                                onmouseout="this.style.borderColor='#185ea56e'; this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.04), 0 0 0 1px rgba(24,95,165,0.05), 0 6px 18px rgba(24,95,165,0.06)';">
                                
                                {{-- Article Type Badge --}}
                                <div class="mb-3">
                                    @if($article->type === 'video')
                                    <span class="ow-badge" style="background: #E6F1FB; color: #0C447C; display: inline-flex; align-items: center; gap: 4px; font-size: 11px; font-weight: 500; padding: 3px 9px; border-radius: 99px; border: 0.5px solid #B5D4F4;">
                                        <svg width="10" height="10" viewBox="0 0 10 10" fill="none">
                                            <path d="M2 2L8 5L2 8V2Z" fill="currentColor"/>
                                        </svg>
                                        Video
                                    </span>
                                    @elseif($article->type === 'document')
                                    <span class="ow-badge" style="background: #FAEEDA; color: #854F0B; display: inline-flex; align-items: center; gap: 4px; font-size: 11px; font-weight: 500; padding: 3px 9px; border-radius: 99px; border: 0.5px solid #F5D9A8;">
                                        Document
                                    </span>
                                    @elseif($article->type === 'link')
                                    <span class="ow-badge" style="background: #F3F4F6; color: #6b7280; display: inline-flex; align-items: center; gap: 4px; font-size: 11px; font-weight: 500; padding: 3px 9px; border-radius: 99px; border: 0.5px solid #e5e7eb;">
                                        Link
                                    </span>
                                    @else
                                    <span class="ow-badge" style="background: #E6F1FB; color: #0C447C; display: inline-flex; align-items: center; gap: 4px; font-size: 11px; font-weight: 500; padding: 3px 9px; border-radius: 99px; border: 0.5px solid #B5D4F4;">
                                        Article
                                    </span>
                                    @endif
                                </div>

                                {{-- Article Title --}}
                                <h3 style="font-size: 15px; font-weight: 600; color: #185FA5; margin-bottom: 8px; line-height: 1.4;">
                                    {{ $article->title }}
                                </h3>

                                {{-- Excerpt --}}
                                @if($article->excerpt)
                                <p style="font-size: 13px; color: #6b7280; margin-bottom: 12px; line-height: 1.5;">
                                    {{ Str::limit($article->excerpt, 120) }}
                                </p>
                                @endif

                                {{-- Meta Info --}}
                                <div style="display: flex; justify-content: space-between; align-items: center; font-size: 12px; color: #9ca3af; margin-top: auto;">
                                    <span>{{ $article->published_at->diffForHumans() }}</span>
                                    <span>{{ $article->views_affiliate }} views</span>
                                </div>
                            </div>
                        </a>
                    </div>
                    @empty
                    <div class="col-12">
                        <div class="ow-card text-center" style="background: #fff; border: 0.5px solid #e5e7eb; border-radius: 12px; padding: 60px 20px;">
                            <div style="margin-bottom: 16px;">
                                <svg width="48" height="48" viewBox="0 0 48 48" fill="none" style="color: #d1d5db;">
                                    <path d="M24 44C35.0457 44 44 35.0457 44 24C44 12.9543 35.0457 4 24 4C12.9543 4 4 12.9543 4 24C4 35.0457 12.9543 44 24 44Z" stroke="currentColor" stroke-width="2"/>
                                    <path d="M24 14V26M24 32H24.02" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                </svg>
                            </div>
                            <p style="font-size: 15px; color: #6b7280; margin-bottom: 8px;">No articles in this category yet</p>
                            <p style="font-size: 13px; color: #9ca3af;">Check back later for new content</p>
                        </div>
                    </div>
                    @endforelse
                </div>

                {{-- Pagination --}}
                @if($articles->hasPages())
                <div class="row mt-4">
                    <div class="col-12">
                        <div style="border-top: 0.5px solid #e5e7eb; background: #fafafa; padding: 12px 20px; border-radius: 12px; display: flex; justify-content: flex-end;">
                            {{ $articles->links() }}
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
</style>

{{-- Search Filter Script --}}
<script>
document.getElementById('categorySearch').addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase();
    const articles = document.querySelectorAll('.article-card');
    
    articles.forEach(article => {
        const title = article.dataset.title;
        const excerpt = article.dataset.excerpt;
        
        if (title.includes(searchTerm) || excerpt.includes(searchTerm)) {
            article.style.display = 'block';
        } else {
            article.style.display = 'none';
        }
    });
});
</script>
@endsection