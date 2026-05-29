@extends('owner.layouts.app')

@php
    $pageTitle = 'Knowledge Base';
@endphp

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid px-4">
            <div class="page-content-wrapper bg-white p-30 radius-20">
                {{-- Page Header --}}
                <div class="mb-4">
                    <h1 style="font-size: 22px; font-weight: 500; color: #111827; margin-bottom: 4px;">Knowledge Base</h1>
                    <nav aria-label="breadcrumb">
                        <ol style="display: flex; gap: 6px; font-size: 12px; color: #9ca3af; list-style: none; padding: 0; margin: 0;">
                            <li><a href="{{ route('owner.dashboard') }}" style="color: #185FA5; font-weight: 500;">Dashboard</a></li>
                            <li>
                                <svg width="8" height="8" viewBox="0 0 8 8" fill="none">
                                    <path d="M2.5 1.5L5.5 4L2.5 6.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </li>
                            <li>Knowledge Base</li>
                        </ol>
                    </nav>
                </div>

                {{-- Search Bar --}}
                <div class="mb-4">
                    <div style="position: relative; display: inline-block;">
                        <svg style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: #9ca3af;" width="14" height="14" viewBox="0 0 14 14" fill="none">
                            <circle cx="6" cy="6" r="4.5" stroke="currentColor" stroke-width="1.5"/>
                            <path d="M9.5 9.5L12.5 12.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                        </svg>
                        <input type="search" placeholder="Search articles..." 
                            style="border: 0.5px solid #e5e7eb; border-radius: 7px; padding: 7px 10px 7px 34px; font-size: 13px; color: #374151; width: 260px; transition: all .15s;"
                            onfocus="this.style.borderColor='#185FA5'; this.style.boxShadow='0 0 0 3px rgba(24,95,165,.1)';"
                            onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none';"
                            onkeypress="if(event.key === 'Enter') window.location.href='{{ route('owner.kb.search') }}?q=' + this.value;">
                    </div>
                </div>

                {{-- Recent Articles --}}
                @if($recentArticles->count() > 0)
                <div class="mb-5">
                    <h2 style="font-size: 15px; font-weight: 600; color: #111827; margin-bottom: 16px;">Recent Articles</h2>
                    <div class="row">
                        @foreach($recentArticles as $article)
                        <div class="col-md-6 col-lg-4 mb-4">
                            <a href="{{ route('owner.kb.article', $article->slug) }}" style="text-decoration: none;">
                                <div class="ow-card" style="background: #fff; border: 0.5px solid #185ea56e; border-radius: 12px; overflow: hidden; padding: 20px; transition: all .25s ease; box-shadow: 0 4px 12px rgba(0,0,0,0.04), 0 0 0 1px rgba(24,95,165,0.05), 0 6px 18px rgba(24,95,165,0.06); cursor: pointer;"
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
                                        @endif
                                    </div>

                                    {{-- Article Title --}}
                                    <h3 style="font-size: 15px; font-weight: 600; color: #185FA5; margin-bottom: 8px; line-height: 1.4;">
                                        {{ $article->title }}
                                    </h3>

                                    {{-- Excerpt --}}
                                    @if($article->excerpt)
                                    <p style="font-size: 13px; color: #6b7280; margin-bottom: 12px; line-height: 1.5;">
                                        {{ $article->excerpt }}
                                    </p>
                                    @endif

                                    {{-- Meta Info --}}
                                    <div style="display: flex; justify-content: space-between; align-items: center; font-size: 12px; color: #9ca3af;">
                                        <span>{{ $article->category->name ?? 'Uncategorized' }}</span>
                                        <span>{{ $article->published_at->diffForHumans() }}</span>
                                    </div>
                                </div>
                            </a>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Categories Grid --}}
                <h2 style="font-size: 15px; font-weight: 600; color: #111827; margin-bottom: 16px;">Browse by Category</h2>
                <div class="row">
                    @forelse($categories as $category)
                    <div class="col-md-6 col-lg-4 mb-4">
                        <a href="{{ route('owner.kb.category', $category->slug) }}" style="text-decoration: none;">
                            <div class="ow-card" style="background: #fff; border: 0.5px solid #185ea56e; border-radius: 12px; overflow: hidden; padding: 20px; transition: all .25s ease; box-shadow: 0 4px 12px rgba(0,0,0,0.04), 0 0 0 1px rgba(24,95,165,0.05), 0 6px 18px rgba(24,95,165,0.06); cursor: pointer; height: 100%;"
                                onmouseover="this.style.borderColor='#185FA5'; this.style.transform='translateY(-3px)'; this.style.boxShadow='0 10px 25px rgba(0,0,0,0.06), 0 0 0 1px rgba(24,95,165,0.12), 0 12px 30px rgba(24,95,165,0.18)';"
                                onmouseout="this.style.borderColor='#185ea56e'; this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.04), 0 0 0 1px rgba(24,95,165,0.05), 0 6px 18px rgba(24,95,165,0.06)';">
                                
                                <div class="d-flex align-items-center gap-3 mb-3">
                                    @if($category->icon)
                                    <span style="font-size: 24px;">{!! $category->icon !!}</span>
                                    @endif
                                    <h3 style="font-size: 15px; font-weight: 600; color: #111827; margin: 0;">{{ $category->name }}</h3>
                                </div>

                                @if($category->description)
                                <p style="font-size: 13px; color: #6b7280; margin-bottom: 12px; line-height: 1.5;">
                                    {{ $category->description }}
                                </p>
                                @endif

                                <div style="font-size: 12.5px; color: #6b7280;">
                                    <strong>{{ $category->articles_count }}</strong> articles
                                </div>
                            </div>
                        </a>
                    </div>
                    @empty
                    <div class="col-12">
                        <div class="ow-card text-center" style="background: #fff; border: 0.5px solid #e5e7eb; border-radius: 12px; padding: 60px 20px;">
                            <p style="font-size: 15px; color: #6b7280;">No categories available yet.</p>
                        </div>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection