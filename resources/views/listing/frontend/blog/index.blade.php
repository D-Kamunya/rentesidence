@extends('saas.frontend.layouts.app')

@php 
    $pageTitle = 'Centresidence Blog - Insights & Resources for Property Management';
    $pageDescription = 'Explore expert tips, industry insights, and best practices for property management';
@endphp

@section('content')
{{-- Add spacing from the menu/navbar --}}
<div style="padding-top: 80px;">
    
{{-- Hero Section with Search --}}
<div style="background: linear-gradient(135deg, #E6F1FB 0%, #FFFFFF 50%, #E6F1FB 100%); border-bottom: 0.5px solid #e5e7eb; padding: 60px 0;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center">
                <h1 style="font-size: 36px; font-weight: 600; color: #111827; margin-bottom: 12px;">
                    Insights & Resources
                </h1>
                <p style="font-size: 16px; color: #6b7280; margin-bottom: 32px;">
                    Expert tips, industry insights, and best practices for property management
                </p>
                
                {{-- Search Bar --}}
                <form action="{{ route('blog.index') }}" method="GET" class="mb-4">
                    <div style="position: relative; max-width: 500px; margin: 0 auto;">
                        <svg style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: #9ca3af;" width="18" height="18" viewBox="0 0 18 18" fill="none">
                            <circle cx="8" cy="8" r="6" stroke="currentColor" stroke-width="1.5"/>
                            <path d="M12.5 12.5L16 16" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                        </svg>
                        <input type="search" 
                               name="search" 
                               value="{{ request('search') }}"
                               placeholder="Search articles..." 
                               style="width: 100%; border: 0.5px solid #e5e7eb; border-radius: 12px; padding: 14px 20px 14px 46px; font-size: 15px; color: #374151; background: #fff; box-shadow: 0 4px 12px rgba(0,0,0,0.04); transition: all .15s;"
                               onfocus="this.style.borderColor='#185FA5'; this.style.boxShadow='0 0 0 3px rgba(24,95,165,.1), 0 4px 12px rgba(0,0,0,0.04)';"
                               onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.04)';">
                    </div>
                </form>

                {{-- Popular Tags --}}
                <div style="display: flex; flex-wrap: wrap; gap: 8px; justify-content: center;">
                    <span style="font-size: 12px; color: #9ca3af; margin-right: 4px;">Popular:</span>
                    @php
                        $popularTags = ['Property Management', 'Investment', 'Maintenance', 'Technology', 'Marketing'];
                    @endphp
                    @foreach($popularTags as $tag)
                    <a href="{{ route('blog.index', ['tag' => $tag]) }}" 
                       style="background: #fff; color: #6b7280; font-size: 12px; padding: 4px 12px; border-radius: 99px; border: 0.5px solid #e5e7eb; text-decoration: none; transition: all .13s;"
                       onmouseover="this.style.background='#185FA5'; this.style.color='#fff'; this.style.borderColor='#185FA5';"
                       onmouseout="this.style.background='#fff'; this.style.color='#6b7280'; this.style.borderColor='#e5e7eb';">
                        {{ $tag }}
                    </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container py-5">
    {{-- Featured Posts --}}
    @if($featuredPosts->count() > 0 && !request()->has('search'))
    <div class="mb-5">
        <h2 style="font-size: 20px; font-weight: 600; color: #111827; margin-bottom: 24px;">Featured Articles</h2>
        <div class="row">
            @foreach($featuredPosts as $featured)
            <div class="col-lg-4 mb-4">
                <a href="{{ route('blog.show', $featured->slug) }}" style="text-decoration: none;">
                    <div class="ow-card" style="background: #fff; border: 0.5px solid #185ea56e; border-radius: 12px; overflow: hidden; transition: all .25s ease; box-shadow: 0 4px 12px rgba(0,0,0,0.04), 0 0 0 1px rgba(24,95,165,0.05), 0 6px 18px rgba(24,95,165,0.06); cursor: pointer; height: 100%;"
                         onmouseover="this.style.borderColor='#185FA5'; this.style.transform='translateY(-3px)'; this.style.boxShadow='0 10px 25px rgba(0,0,0,0.06), 0 0 0 1px rgba(24,95,165,0.12), 0 12px 30px rgba(24,95,165,0.18)';"
                         onmouseout="this.style.borderColor='#185ea56e'; this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.04), 0 0 0 1px rgba(24,95,165,0.05), 0 6px 18px rgba(24,95,165,0.06)';">
                        
                        @if($featured->featured_image)
                        <div style="height: 200px; overflow: hidden;">
                            <img src="{{ asset('storage/' . $featured->featured_image) }}" 
                                 alt="{{ $featured->featured_image_alt ?? $featured->title }}"
                                 style="width: 100%; height: 100%; object-fit: cover;">
                        </div>
                        @endif
                        
                        <div style="padding: 20px;">
                            <div class="d-flex align-items-center gap-2 mb-3">
                                @if($featured->category)
                                <span style="background: {{ $featured->category->color ?? '#185FA5' }}15; color: {{ $featured->category->color ?? '#185FA5' }}; font-size: 11px; font-weight: 500; padding: 3px 9px; border-radius: 99px;">
                                    {{ $featured->category->name }}
                                </span>
                                @endif
                                <span style="font-size: 11px; color: #9ca3af;">{{ $featured->reading_time_text }}</span>
                            </div>
                            
                            <h3 style="font-size: 16px; font-weight: 600; color: #111827; margin-bottom: 8px; line-height: 1.4;">
                                {{ $featured->title }}
                            </h3>
                            
                            @if($featured->excerpt)
                            <p style="font-size: 13px; color: #6b7280; margin-bottom: 16px; line-height: 1.5;">
                                {{ Str::limit($featured->excerpt, 100) }}
                            </p>
                            @endif
                            
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <div style="width: 28px; height: 28px; border-radius: 8px; background: #E6F1FB; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 600; color: #185FA5;">
                                        {{ substr($featured->author->name ?? 'A', 0, 1) }}
                                    </div>
                                    <span style="font-size: 12px; color: #6b7280;">{{ $featured->author->name ?? 'Admin' }}</span>
                                </div>
                                <span style="font-size: 12px; color: #9ca3af;">{{ $featured->published_at->format('M d, Y') }}</span>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <div class="row">
        {{-- Main Content --}}
        <div class="col-lg-8">
            {{-- Filter Tabs --}}
            <div style="display: flex; gap: 4px; background: #f3f4f6; border-radius: 8px; padding: 3px; margin-bottom: 24px; overflow-x: auto;">
                <a href="{{ route('blog.index') }}" 
                   class="filter-tab {{ !request()->has('category') ? 'active' : '' }}"
                   style="padding: 6px 16px; border-radius: 6px; font-size: 13px; font-weight: 500; text-decoration: none; white-space: nowrap; transition: all .15s;
                          {{ !request()->has('category') ? 'background: #fff; color: #111827; box-shadow: 0 1px 3px rgba(0,0,0,.08);' : 'color: #6b7280;' }}">
                    All Posts
                </a>
                @foreach($categories as $category)
                <a href="{{ route('blog.index', ['category' => $category->slug]) }}" 
                   class="filter-tab {{ request('category') == $category->slug ? 'active' : '' }}"
                   style="padding: 6px 16px; border-radius: 6px; font-size: 13px; font-weight: 500; text-decoration: none; white-space: nowrap; transition: all .15s;
                          {{ request('category') == $category->slug ? 'background: #fff; color: #111827; box-shadow: 0 1px 3px rgba(0,0,0,.08);' : 'color: #6b7280;' }}">
                    {{ $category->name }}
                </a>
                @endforeach
            </div>

            {{-- Posts Grid --}}
            @forelse($posts as $post)
            <div class="mb-4">
                <a href="{{ route('blog.show', $post->slug) }}" style="text-decoration: none;">
                    <div class="ow-card" style="background: #fff; border: 0.5px solid #185ea56e; border-radius: 12px; overflow: hidden; transition: all .25s ease; box-shadow: 0 4px 12px rgba(0,0,0,0.04), 0 0 0 1px rgba(24,95,165,0.05), 0 6px 18px rgba(24,95,165,0.06); cursor: pointer;"
                         onmouseover="this.style.borderColor='#185FA5'; this.style.transform='translateY(-3px)'; this.style.boxShadow='0 10px 25px rgba(0,0,0,0.06), 0 0 0 1px rgba(24,95,165,0.12), 0 12px 30px rgba(24,95,165,0.18)';"
                         onmouseout="this.style.borderColor='#185ea56e'; this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.04), 0 0 0 1px rgba(24,95,165,0.05), 0 6px 18px rgba(24,95,165,0.06)';">
                        
                        <div class="row g-0">
                            @if($post->featured_image)
                            <div class="col-md-4">
                                <div style="height: 100%; min-height: 200px;">
                                    <img src="{{ asset('storage/' . $post->featured_image) }}" 
                                         alt="{{ $post->featured_image_alt ?? $post->title }}"
                                         style="width: 100%; height: 100%; object-fit: cover;">
                                </div>
                            </div>
                            @endif
                            
                            <div class="{{ $post->featured_image ? 'col-md-8' : 'col-12' }}">
                                <div style="padding: 24px;">
                                    <div class="d-flex align-items-center gap-2 mb-3">
                                        @if($post->category)
                                        <span style="background: {{ $post->category->color ?? '#185FA5' }}15; color: {{ $post->category->color ?? '#185FA5' }}; font-size: 11px; font-weight: 500; padding: 3px 9px; border-radius: 99px;">
                                            {{ $post->category->name }}
                                        </span>
                                        @endif
                                        @if($post->is_featured)
                                        <span style="background: #FFF3CD; color: #854F0B; font-size: 11px; font-weight: 500; padding: 3px 9px; border-radius: 99px;">
                                            ⭐ Featured
                                        </span>
                                        @endif
                                    </div>
                                    
                                    <h3 style="font-size: 18px; font-weight: 600; color: #111827; margin-bottom: 8px; line-height: 1.4;">
                                        {{ $post->title }}
                                    </h3>
                                    
                                    @if($post->excerpt)
                                    <p style="font-size: 14px; color: #6b7280; margin-bottom: 16px; line-height: 1.6;">
                                        {{ $post->excerpt }}
                                    </p>
                                    @endif
                                    
                                    <div style="display: flex; justify-content: space-between; align-items: center;">
                                        <div style="display: flex; align-items: center; gap: 12px;">
                                            <div style="display: flex; align-items: center; gap: 6px;">
                                                <div style="width: 24px; height: 24px; border-radius: 6px; background: #E6F1FB; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 600; color: #185FA5;">
                                                    {{ substr($post->author->name ?? 'A', 0, 1) }}
                                                </div>
                                                <span style="font-size: 12px; color: #6b7280;">{{ $post->author->name ?? 'Admin' }}</span>
                                            </div>
                                            <span style="font-size: 12px; color: #9ca3af;">•</span>
                                            <span style="font-size: 12px; color: #9ca3af;">{{ $post->reading_time_text }}</span>
                                        </div>
                                        
                                        <div style="display: flex; align-items: center; gap: 16px; font-size: 12px; color: #9ca3af;">
                                            <span style="display: flex; align-items: center; gap: 4px;">
                                                <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                                                    <circle cx="7" cy="7" r="2.5" stroke="currentColor" stroke-width="1.2"/>
                                                    <path d="M1.5 7C3 3.5 11 3.5 12.5 7C11 10.5 3 10.5 1.5 7Z" stroke="currentColor" stroke-width="1.2"/>
                                                </svg>
                                                {{ $post->views_count }}
                                            </span>
                                            <span style="display: flex; align-items: center; gap: 4px;">
                                                <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                                                    <path d="M4 7L6 9L10 5" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
                                                    <rect x="1" y="1" width="12" height="12" rx="2.5" stroke="currentColor" stroke-width="1.2"/>
                                                </svg>
                                                {{ $post->comments_count }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            @empty
            <div class="ow-card text-center" style="background: #fff; border: 0.5px solid #e5e7eb; border-radius: 12px; padding: 60px 20px;">
                <h3 style="font-size: 18px; color: #111827; margin-bottom: 8px;">No posts found</h3>
                <p style="font-size: 14px; color: #6b7280;">Check back soon for new content!</p>
            </div>
            @endforelse
            
            {{ $posts->links() }}
        </div>

        {{-- Sidebar --}}
        <div class="col-lg-4">
            {{-- Newsletter Signup --}}
            <div id="newsletter-section" class="ow-card mb-4" style="background: linear-gradient(135deg, #185FA5, #0F4A84); border: none; border-radius: 12px; padding: 24px; color: #fff; box-shadow: 0 4px 12px rgba(24,95,165,0.2);">
                {{-- Success Message --}}
                @if(session('subscribe_success'))
                <div style="background: rgba(255,255,255,0.2); color: #fff; padding: 12px 16px; border-radius: 7px; margin-bottom: 16px; font-size: 13px; display: flex; align-items: flex-start; gap: 8px; border: 0.5px solid rgba(255,255,255,0.3);">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" style="flex-shrink: 0; margin-top: 2px;">
                        <circle cx="8" cy="8" r="6" stroke="currentColor" stroke-width="1.5"/>
                        <path d="M5.5 8L7 9.5L10.5 6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    {{ session('subscribe_success') }}
                </div>
                @endif
                
                <h3 style="font-size: 16px; font-weight: 600; margin-bottom: 8px; color: #fff;">📧 Stay Updated</h3>
                <p style="font-size: 13px; color: #ffffff; margin-bottom: 16px; opacity: 1; font-weight: 400; line-height: 1.6;">
                    Get the latest property management tips delivered to your inbox.
                </p>
                <form action="{{ route('blog.subscribe') }}" method="POST" id="subscribeForm">
                    @csrf
                    <input type="email" name="email" placeholder="Enter your email" required
                        style="width: 100%; border: none; border-radius: 7px; padding: 10px 14px; font-size: 13px; margin-bottom: 8px;">
                    <button type="submit" 
                            style="width: 100%; background: #fff; color: #185FA5; border: none; border-radius: 7px; padding: 10px; font-size: 13px; font-weight: 600; cursor: pointer; transition: all .13s;"
                            onmouseover="this.style.background='#f3f4f6';"
                            onmouseout="this.style.background='#fff';"
                            id="subscribeBtn">
                        Subscribe Now
                    </button>
                </form>
            </div>

            {{-- Categories --}}
            <div class="ow-card mb-4" style="background: #fff; border: 0.5px solid #e5e7eb; border-radius: 12px; padding: 20px; box-shadow: 0 4px 12px rgba(0,0,0,0.04);">
                <h3 style="font-size: 14px; font-weight: 600; color: #111827; margin-bottom: 16px;">Categories</h3>
                <div style="display: flex; flex-direction: column; gap: 2px;">
                    @foreach($categories as $category)
                    <a href="{{ route('blog.index', ['category' => $category->slug]) }}" 
                       style="display: flex; justify-content: space-between; align-items: center; padding: 10px 12px; border-radius: 7px; text-decoration: none; transition: all .15s;"
                       onmouseover="this.style.background='#f3f4f6';"
                       onmouseout="this.style.background='transparent';">
                        <span style="display: flex; align-items: center; gap: 8px; font-size: 13px; color: #374151;">
                            <span style="width: 8px; height: 8px; border-radius: 50%; background: {{ $category->color ?? '#185FA5' }};"></span>
                            {{ $category->name }}
                        </span>
                        <span style="background: #f3f4f6; color: #6b7280; font-size: 11px; padding: 2px 8px; border-radius: 99px;">
                            {{ $category->posts_count }}
                        </span>
                    </a>
                    @endforeach
                </div>
            </div>

            {{-- Popular Posts --}}
            <div class="ow-card" style="background: #fff; border: 0.5px solid #e5e7eb; border-radius: 12px; padding: 20px; box-shadow: 0 4px 12px rgba(0,0,0,0.04);">
                <h3 style="font-size: 14px; font-weight: 600; color: #111827; margin-bottom: 16px;">🔥 Popular Posts</h3>
                <div style="display: flex; flex-direction: column; gap: 16px;">
                    @foreach($popularPosts as $popular)
                    <a href="{{ route('blog.show', $popular->slug) }}" style="text-decoration: none; display: flex; gap: 12px;">
                        <div style="flex-shrink: 0; font-size: 20px; font-weight: 700; color: #185FA5; opacity: 0.3; width: 24px;">
                            {{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}
                        </div>
                        <div>
                            <h4 style="font-size: 13px; font-weight: 500; color: #374151; margin-bottom: 4px; line-height: 1.4;">
                                {{ $popular->title }}
                            </h4>
                            <span style="font-size: 11px; color: #9ca3af;">{{ $popular->views_count }} views</span>
                        </div>
                    </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
</div>

<style>
.pagination {
    display: flex;
    gap: 4px;
    list-style: none;
    margin: 0;
    padding: 20px 0;
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

.filter-tab:hover:not(.active) {
    color: #374151;
}
</style>

<script>
// AJAX subscription
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
                // Show success message
                const successDiv = document.createElement('div');
                successDiv.style.cssText = 'background: rgba(255,255,255,0.2); color: #fff; padding: 12px 16px; border-radius: 7px; margin-bottom: 16px; font-size: 13px; display: flex; align-items: flex-start; gap: 8px; border: 0.5px solid rgba(255,255,255,0.3); animation: fadeIn 0.3s ease;';
                successDiv.innerHTML = `
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" style="flex-shrink: 0; margin-top: 2px;">
                        <circle cx="8" cy="8" r="6" stroke="currentColor" stroke-width="1.5"/>
                        <path d="M5.5 8L7 9.5L10.5 6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    ${data.message}
                `;
                
                subscribeForm.parentNode.insertBefore(successDiv, subscribeForm);
                
                // Clear the form
                subscribeForm.reset();
                
                // Remove success message after 8 seconds
                setTimeout(() => {
                    successDiv.style.opacity = '0';
                    successDiv.style.transition = 'opacity 0.3s ease';
                    setTimeout(() => successDiv.remove(), 300);
                }, 8000);
            } else if (data.errors) {
                const errorMsg = data.errors.email ? data.errors.email[0] : 'Something went wrong.';
                alert(errorMsg);
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

// Scroll to newsletter if redirected
@if(session('scrollTo'))
document.addEventListener('DOMContentLoaded', function() {
    const newsletter = document.getElementById('newsletter-section');
    if (newsletter) {
        setTimeout(() => {
            newsletter.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }, 300);
    }
});
@endif
</script>
@endsection