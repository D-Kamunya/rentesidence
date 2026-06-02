@extends('admin.layouts.app')

@section('title', 'Blog Posts')

@php
    $navBlogMMShowClass = 'mm-active';
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
                                <h4 class="mb-0" style="font-size: 22px; font-weight: 500; color: #111827;">Blog Posts</h4>
                                </br>
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb m-0" style="display: flex; gap: 6px; font-size: 12px; color: #9ca3af; list-style: none; padding: 0;">
                                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" style="color: #185FA5; font-weight: 500;">Dashboard</a></li>
                                        <li class="breadcrumb-item"><a href="{{ route('admin.blog.dashboard') }}" style="color: #185FA5; font-weight: 500;">Blog</a></li>
                                        <li class="breadcrumb-item active" aria-current="page" style="color: #9ca3af;">Posts</li>
                                    </ol>
                                </nav>
                            </div>
                            <div>
                                <a href="{{ route('admin.blog.posts.create') }}" class="btn btn-purple" style="background: #534AB7; color: #fff; display: inline-flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 500; padding: 7px 15px; border-radius: 7px; border: none; cursor: pointer; text-decoration: none; transition: all .13s;"
                                onmouseover="this.style.background='#3C3489'; this.style.transform='translateY(-1px)';" 
                                onmouseout="this.style.background='#534AB7'; this.style.transform='translateY(0)';">
                                    <i class="fa fa-plus"></i>
                                    New Post
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Filters --}}
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card" style="background: #fff; border: 0.5px solid #e5e7eb; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.04);">
                            <div class="card-body" style="padding: 20px;">
                                <form action="{{ route('admin.blog.posts') }}" method="GET">
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label style="font-size: 10px; font-weight: 500; text-transform: uppercase; letter-spacing: .07em; color: #9ca3af; display: block; margin-bottom: 6px;">Search</label>
                                            <div style="position: relative;">
                                                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search posts..."
                                                    style="width: 100%; border: 0.5px solid #e5e7eb; border-radius: 7px; padding: 7px 10px 7px 34px; font-size: 13px; color: #374151;"
                                                    onfocus="this.style.borderColor='#185FA5'; this.style.boxShadow='0 0 0 3px rgba(24,95,165,.1)';"
                                                    onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none';">
                                                <i class="fa fa-search" style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: #9ca3af;"></i>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <label style="font-size: 10px; font-weight: 500; text-transform: uppercase; letter-spacing: .07em; color: #9ca3af; display: block; margin-bottom: 6px;">Status</label>
                                            <select name="status" style="width: 100%; border: 0.5px solid #e5e7eb; border-radius: 7px; padding: 7px 10px; font-size: 13px; color: #374151;" onchange="this.form.submit()">
                                                <option value="">All</option>
                                                <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Published</option>
                                                <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                                                <option value="scheduled" {{ request('status') === 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label style="font-size: 10px; font-weight: 500; text-transform: uppercase; letter-spacing: .07em; color: #9ca3af; display: block; margin-bottom: 6px;">Category</label>
                                            <select name="category" style="width: 100%; border: 0.5px solid #e5e7eb; border-radius: 7px; padding: 7px 10px; font-size: 13px; color: #374151;" onchange="this.form.submit()">
                                                <option value="">All Categories</option>
                                                @foreach($categories as $category)
                                                <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-2 d-flex align-items-end">
                                            @if(request()->anyFilled(['search', 'status', 'category']))
                                            <a href="{{ route('admin.blog.posts') }}" class="btn btn-ghost" style="background: #f3f4f6; color: #374151; font-size: 12px; padding: 7px 15px; border-radius: 7px; border: 0.5px solid #e5e7eb; text-decoration: none; width: 100%; text-align: center;">Clear</a>
                                            @endif
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Posts Table --}}
                <div class="row">
                    <div class="col-12">
                        <div class="card" style="background: #fff; border: 0.5px solid #e5e7eb; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.04);">
                            <div class="table-responsive">
                                <table class="table mb-0">
                                    <thead style="background: #fafafa; border-bottom: 0.5px solid #e5e7eb;">
                                        <tr>
                                            <th style="padding: .65rem 1rem; font-size: 10px; font-weight: 500; text-transform: uppercase; letter-spacing: .07em; color: #6b7280;">Post</th>
                                            <th style="padding: .65rem 1rem; font-size: 10px; font-weight: 500; text-transform: uppercase; letter-spacing: .07em; color: #6b7280;">Category</th>
                                            <th style="padding: .65rem 1rem; font-size: 10px; font-weight: 500; text-transform: uppercase; letter-spacing: .07em; color: #6b7280;">Status</th>
                                            <th style="padding: .65rem 1rem; font-size: 10px; font-weight: 500; text-transform: uppercase; letter-spacing: .07em; color: #6b7280;">Views</th>
                                            <th style="padding: .65rem 1rem; font-size: 10px; font-weight: 500; text-transform: uppercase; letter-spacing: .07em; color: #6b7280;">Comments</th>
                                            <th style="padding: .65rem 1rem; font-size: 10px; font-weight: 500; text-transform: uppercase; letter-spacing: .07em; color: #6b7280;">Date</th>
                                            <th style="padding: .65rem 1rem; font-size: 10px; font-weight: 500; text-transform: uppercase; letter-spacing: .07em; color: #6b7280; text-align: center;">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($posts as $post)
                                        <tr style="border-bottom: 0.5px solid #f3f4f6; {{ $loop->even ? 'background: #fafafa;' : '' }}"
                                            onmouseover="this.style.background='#f3f4f6';" 
                                            onmouseout="this.style.background='{{ $loop->even ? '#fafafa' : '#ffffff' }}';">
                                            <td style="padding: .8rem 1rem;">
                                                <div class="d-flex align-items-center gap-3">
                                                    @if($post->featured_image)
                                                    <img src="{{ asset('storage/' . $post->featured_image) }}" alt="" style="width: 40px; height: 40px; border-radius: 6px; object-fit: cover;">
                                                    @endif
                                                    <div>
                                                        <div style="font-size: 13px; font-weight: 500; color: #185FA5; max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                                            {{ $post->title }}
                                                        </div>
                                                        <div style="font-size: 11px; color: #9ca3af;">{{ $post->author->name ?? 'Unknown' }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td style="padding: .8rem 1rem;">
                                                @if($post->category)
                                                <span class="badge" style="font-size: 11px; padding: 3px 9px; border-radius: 99px; background: {{ $post->category->color ?? '#185FA5' }}15; color: {{ $post->category->color ?? '#185FA5' }};">
                                                    {{ $post->category->name }}
                                                </span>
                                                @endif
                                            </td>
                                            <td style="padding: .8rem 1rem;">
                                                <span class="badge" style="font-size: 11px; padding: 3px 9px; border-radius: 99px;
                                                    @if($post->status === 'published') background: #E1F5EE; color: #0F6E56;
                                                    @elseif($post->status === 'draft') background: #FAEEDA; color: #854F0B; border: 0.5px solid #F5D9A8;
                                                    @else background: #E6F1FB; color: #0C447C; border: 0.5px solid #B5D4F4;
                                                    @endif">
                                                    {{ ucfirst($post->status) }}
                                                </span>
                                                @if($post->is_featured)
                                                <span style="font-size: 11px; margin-left: 4px;">⭐</span>
                                                @endif
                                            </td>
                                            <td style="padding: .8rem 1rem; font-size: 13px; color: #374151;">{{ number_format($post->views_count) }}</td>
                                            <td style="padding: .8rem 1rem; font-size: 13px; color: #374151;">{{ $post->comments_count }}</td>
                                            <td style="padding: .8rem 1rem; font-size: 12px; color: #9ca3af;">{{ $post->created_at->format('M d, Y') }}</td>
                                            <td style="padding: .8rem 1rem; text-align: center;">
                                                <div class="d-flex justify-content-center gap-2">
                                                    <a href="{{ route('blog.show', $post->slug) }}" target="_blank" class="btn btn-sm" style="background: #f3f4f6; color: #374151; font-size: 11px; padding: 5px 10px; border-radius: 6px; text-decoration: none;">
                                                        <i class="fa fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('admin.blog.posts.edit', $post) }}" class="btn btn-sm" style="background: #f0f4fa; color: #185FA5; font-size: 11px; padding: 5px 10px; border-radius: 6px; text-decoration: none;">
                                                        <i class="fa fa-edit"></i>
                                                    </a>
                                                    <form action="{{ route('admin.blog.posts.destroy', $post) }}" method="POST" onsubmit="return confirm('Delete this post?');" style="display: inline;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm" style="background: #185ea51c; color: #374151; border: none; font-size: 11px; padding: 5px 10px; border-radius: 6px; cursor: pointer;"
                                                                onmouseover="this.style.background='#fee2e2'; this.style.color='#b91c1c';" 
                                                                onmouseout="this.style.background='#185ea51c'; this.style.color='#374151';">
                                                            <i class="fa fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="7" style="padding: 60px 20px; text-align: center;">
                                                <p style="font-size: 15px; color: #6b7280; margin-bottom: 16px;">No posts found</p>
                                                <a href="{{ route('admin.blog.posts.create') }}" class="btn btn-purple" style="background: #534AB7; color: #fff; font-size: 12px; padding: 7px 15px; border-radius: 7px; text-decoration: none;">Create Your First Post</a>
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            @if($posts->hasPages())
                            <div style="border-top: 0.5px solid #e5e7eb; background: #fafafa; padding: 12px 20px; display: flex; justify-content: flex-end;">
                                {{ $posts->links() }}
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection