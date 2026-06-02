@extends('admin.layouts.app')

@php
    $pageTitle = 'Knowledge Base Articles';
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
                                <h4 class="mb-0" style="font-size: 22px; font-weight: 500; color: #111827;">Knowledge Base Articles</h4>
                                </br>
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb m-0" style="display: flex; gap: 6px; font-size: 12px; color: #9ca3af; list-style: none; padding: 0;">
                                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" style="color: #185FA5; font-weight: 500;">Dashboard</a></li>
                                        <li class="breadcrumb-item active" aria-current="page" style="color: #9ca3af;">Knowledge Base</li>
                                    </ol>
                                </nav>
                            </div>
                            <div>
                                <a href="{{ route('admin.kb.articles.create') }}" class="btn btn-purple" style="background: #534AB7; color: #fff; display: inline-flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 500; padding: 7px 15px; border-radius: 7px; border: none; cursor: pointer; text-decoration: none; transition: all .13s;"
                                onmouseover="this.style.background='#3C3489'; this.style.transform='translateY(-1px)';" 
                                onmouseout="this.style.background='#534AB7'; this.style.transform='translateY(0)';">
                                    <i class="fa fa-plus"></i>
                                    <span>New Article</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Filters Section --}}
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card" style="background: #fff; border: 0.5px solid #e5e7eb; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.04), 0 0 0 1px rgba(24,95,165,0.05), 0 6px 18px rgba(24,95,165,0.06);">
                            <div class="card-body" style="padding: 20px;">
                                <form action="{{ route('admin.kb.articles') }}" method="GET" id="filterForm">
                                    <div class="row g-3 align-items-end">
                                        {{-- Search --}}
                                        <div class="col-md-3">
                                            <label style="font-size: 10px; font-weight: 500; text-transform: uppercase; letter-spacing: .07em; color: #9ca3af; display: block; margin-bottom: 6px;">Search</label>
                                            <div style="position: relative;">
                                                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search articles..."
                                                    style="border: 0.5px solid #e5e7eb; border-radius: 7px; padding: 7px 10px 7px 34px; font-size: 13px; color: #374151; width: 100%; transition: all .15s;"
                                                    onfocus="this.style.borderColor='#185FA5'; this.style.boxShadow='0 0 0 3px rgba(24,95,165,.1)';"
                                                    onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none';">
                                                <i class="fa fa-search" style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: #9ca3af; font-size: 13px;"></i>
                                            </div>
                                        </div>

                                        {{-- Status Filter --}}
                                        <div class="col-md-2">
                                            <label style="font-size: 10px; font-weight: 500; text-transform: uppercase; letter-spacing: .07em; color: #9ca3af; display: block; margin-bottom: 6px;">Status</label>
                                            <select name="status" 
                                                    style="width: 100%; border: 0.5px solid #e5e7eb; border-radius: 7px; padding: 7px 10px; font-size: 13px; color: #374151; background: #fff; transition: all .15s;"
                                                    onfocus="this.style.borderColor='#185FA5'; this.style.boxShadow='0 0 0 3px rgba(24,95,165,.1)';"
                                                    onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none';"
                                                    onchange="this.form.submit()">
                                                <option value="">All Status</option>
                                                <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Published</option>
                                                <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                                                <option value="archived" {{ request('status') === 'archived' ? 'selected' : '' }}>Archived</option>
                                            </select>
                                        </div>

                                        {{-- Audience Filter --}}
                                        <div class="col-md-2">
                                            <label style="font-size: 10px; font-weight: 500; text-transform: uppercase; letter-spacing: .07em; color: #9ca3af; display: block; margin-bottom: 6px;">Audience</label>
                                            <select name="audience" 
                                                    style="width: 100%; border: 0.5px solid #e5e7eb; border-radius: 7px; padding: 7px 10px; font-size: 13px; color: #374151; background: #fff; transition: all .15s;"
                                                    onfocus="this.style.borderColor='#185FA5'; this.style.boxShadow='0 0 0 3px rgba(24,95,165,.1)';"
                                                    onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none';"
                                                    onchange="this.form.submit()">
                                                <option value="">All Audience</option>
                                                <option value="owners" {{ request('audience') === 'owners' ? 'selected' : '' }}>Owners Only</option>
                                                <option value="affiliates" {{ request('audience') === 'affiliates' ? 'selected' : '' }}>Affiliates Only</option>
                                                <option value="both" {{ request('audience') === 'both' ? 'selected' : '' }}>Both</option>
                                            </select>
                                        </div>

                                        {{-- Type Filter --}}
                                        <div class="col-md-2">
                                            <label style="font-size: 10px; font-weight: 500; text-transform: uppercase; letter-spacing: .07em; color: #9ca3af; display: block; margin-bottom: 6px;">Type</label>
                                            <select name="type" 
                                                    style="width: 100%; border: 0.5px solid #e5e7eb; border-radius: 7px; padding: 7px 10px; font-size: 13px; color: #374151; background: #fff; transition: all .15s;"
                                                    onfocus="this.style.borderColor='#185FA5'; this.style.boxShadow='0 0 0 3px rgba(24,95,165,.1)';"
                                                    onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none';"
                                                    onchange="this.form.submit()">
                                                <option value="">All Types</option>
                                                <option value="article" {{ request('type') === 'article' ? 'selected' : '' }}>Article</option>
                                                <option value="video" {{ request('type') === 'video' ? 'selected' : '' }}>Video</option>
                                                <option value="document" {{ request('type') === 'document' ? 'selected' : '' }}>Document</option>
                                                <option value="link" {{ request('type') === 'link' ? 'selected' : '' }}>External Link</option>
                                            </select>
                                        </div>

                                        {{-- Category Filter --}}
                                        <div class="col-md-2">
                                            <label style="font-size: 10px; font-weight: 500; text-transform: uppercase; letter-spacing: .07em; color: #9ca3af; display: block; margin-bottom: 6px;">Category</label>
                                            <select name="category" 
                                                    style="width: 100%; border: 0.5px solid #e5e7eb; border-radius: 7px; padding: 7px 10px; font-size: 13px; color: #374151; background: #fff; transition: all .15s;"
                                                    onfocus="this.style.borderColor='#185FA5'; this.style.boxShadow='0 0 0 3px rgba(24,95,165,.1)';"
                                                    onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none';"
                                                    onchange="this.form.submit()">
                                                <option value="">All Categories</option>
                                                @foreach($categories as $category)
                                                <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                                                    {{ $category->name }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        {{-- Clear Filters --}}
                                        @if(request()->anyFilled(['search', 'status', 'audience', 'type', 'category']))
                                        <div class="col-md-1">
                                            <a href="{{ route('admin.kb.articles') }}" class="btn btn-ghost" 
                                            style="background: #f3f4f6; color: #374151; display: inline-flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 500; padding: 7px 15px; border-radius: 7px; border: 0.5px solid #e5e7eb; cursor: pointer; text-decoration: none; width: 100%;">
                                                Clear
                                            </a>
                                        </div>
                                        @endif
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Articles Table --}}
                <div class="row">
                    <div class="col-12">
                        <div class="card" style="background: #fff; border: 0.5px solid #e5e7eb; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.04), 0 0 0 1px rgba(24,95,165,0.05), 0 6px 18px rgba(24,95,165,0.06);">
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table mb-0" style="width: 100%; border-collapse: collapse;">
                                        <thead style="background: #fafafa; border-bottom: 0.5px solid #e5e7eb;">
                                            <tr>
                                                <th style="padding: .65rem 1rem; font-size: 10px; font-weight: 500; text-transform: uppercase; letter-spacing: .07em; color: #6b7280; border: none;">Title</th>
                                                <th style="padding: .65rem 1rem; font-size: 10px; font-weight: 500; text-transform: uppercase; letter-spacing: .07em; color: #6b7280; border: none;">Category</th>
                                                <th style="padding: .65rem 1rem; font-size: 10px; font-weight: 500; text-transform: uppercase; letter-spacing: .07em; color: #6b7280; border: none;">Type</th>
                                                <th style="padding: .65rem 1rem; font-size: 10px; font-weight: 500; text-transform: uppercase; letter-spacing: .07em; color: #6b7280; border: none;">Audience</th>
                                                <th style="padding: .65rem 1rem; font-size: 10px; font-weight: 500; text-transform: uppercase; letter-spacing: .07em; color: #6b7280; border: none;">Status</th>
                                                <th style="padding: .65rem 1rem; font-size: 10px; font-weight: 500; text-transform: uppercase; letter-spacing: .07em; color: #6b7280; border: none;">Views</th>
                                                <th style="padding: .65rem 1rem; font-size: 10px; font-weight: 500; text-transform: uppercase; letter-spacing: .07em; color: #6b7280; border: none;">Author</th>
                                                <th style="padding: .65rem 1rem; font-size: 10px; font-weight: 500; text-transform: uppercase; letter-spacing: .07em; color: #6b7280; border: none;">Date</th>
                                                <th style="padding: .65rem 1rem; font-size: 10px; font-weight: 500; text-transform: uppercase; letter-spacing: .07em; color: #6b7280; border: none; text-align: center;">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($articles as $article)
                                            <tr style="border-bottom: 0.5px solid #f3f4f6; {{ $loop->even ? 'background: #fafafa;' : '' }} {{ $article->status === 'archived' ? 'opacity: 0.62;' : '' }} transition: all .15s;"
                                                onmouseover="this.style.background='#f3f4f6'; {{ $article->status === 'archived' ? 'this.style.opacity=\'1\';' : '' }}"
                                                onmouseout="this.style.background='{{ $loop->even ? '#fafafa' : '#ffffff' }}'; {{ $article->status === 'archived' ? 'this.style.opacity=\'0.62\';' : '' }}">
                                                
                                                {{-- Title --}}
                                                <td style="padding: .8rem 1rem;">
                                                    <div style="font-size: 13px; font-weight: 500; color: #185FA5; max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                                        {{ $article->title }}
                                                    </div>
                                                </td>

                                                {{-- Category --}}
                                                <td style="padding: .8rem 1rem;">
                                                    @if($article->category)
                                                    <span class="badge" style="background: #E6F1FB; color: #0C447C; display: inline-flex; align-items: center; gap: 4px; font-size: 11px; font-weight: 500; padding: 3px 9px; border-radius: 99px; border: 0.5px solid #B5D4F4;">
                                                        {{ $article->category->name }}
                                                    </span>
                                                    @else
                                                    <span style="font-size: 12.5px; color: #9ca3af;">—</span>
                                                    @endif
                                                </td>

                                                {{-- Type --}}
                                                <td style="padding: .8rem 1rem;">
                                                    <span class="badge" style="display: inline-flex; align-items: center; gap: 4px; font-size: 11px; font-weight: 500; padding: 3px 9px; border-radius: 99px;
                                                        @if($article->type === 'article')
                                                            background: #E6F1FB; color: #0C447C; border: 0.5px solid #B5D4F4;
                                                        @elseif($article->type === 'video')
                                                            background: #FAEEDA; color: #854F0B; border: 0.5px solid #F5D9A8;
                                                        @elseif($article->type === 'document')
                                                            background: #E1F5EE; color: #0F6E56; border: 0.5px solid #A7DFC9;
                                                        @else
                                                            background: #F3F4F6; color: #6b7280; border: 0.5px solid #e5e7eb;
                                                        @endif
                                                    ">
                                                        <i class="fa {{ 
                                                            $article->type === 'article' ? 'fa-file-text-o' : 
                                                            ($article->type === 'video' ? 'fa-play' : 
                                                            ($article->type === 'document' ? 'fa-file-pdf-o' : 'fa-external-link'))
                                                        }}" style="font-size: 9px;"></i>
                                                        {{ $article->type_label }}
                                                    </span>
                                                </td>

                                                {{-- Audience --}}
                                                <td style="padding: .8rem 1rem;">
                                                    <span class="badge" style="display: inline-flex; align-items: center; gap: 4px; font-size: 11px; font-weight: 500; padding: 3px 9px; border-radius: 99px;
                                                        @if($article->audience === 'owners')
                                                            background: #E6F1FB; color: #0C447C; border: 0.5px solid #B5D4F4;
                                                        @elseif($article->audience === 'affiliates')
                                                            background: #FAEEDA; color: #854F0B; border: 0.5px solid #F5D9A8;
                                                        @else
                                                            background: #E1F5EE; color: #0F6E56; border: 0.5px solid #A7DFC9;
                                                        @endif
                                                    ">
                                                        <i class="fa {{ $article->audience === 'owners' ? 'fa-user' : ($article->audience === 'affiliates' ? 'fa-users' : 'fa-globe') }}" style="font-size: 9px;"></i>
                                                        {{ $article->audience_label }}
                                                    </span>
                                                </td>

                                                {{-- Status --}}
                                                <td style="padding: .8rem 1rem;">
                                                    <span class="badge" style="display: inline-flex; align-items: center; gap: 4px; font-size: 11px; font-weight: 500; padding: 3px 9px; border-radius: 99px;
                                                        @if($article->status === 'published')
                                                            background: #E1F5EE; color: #0F6E56;
                                                        @elseif($article->status === 'draft')
                                                            background: #FAEEDA; color: #854F0B; border: 0.5px solid #F5D9A8;
                                                        @else
                                                            background: #F3F4F6; color: #6b7280; border: 0.5px solid #e5e7eb;
                                                        @endif
                                                    ">
                                                        {{ ucfirst($article->status) }}
                                                    </span>
                                                </td>

                                                {{-- Views --}}
                                                <td style="padding: .8rem 1rem;">
                                                    <div style="font-size: 12px; color: #6b7280;">
                                                        <div>Owners: <strong style="color: #374151;">{{ $article->views_owner }}</strong></div>
                                                        <div>Affiliates: <strong style="color: #374151;">{{ $article->views_affiliate }}</strong></div>
                                                    </div>
                                                </td>

                                                {{-- Author --}}
                                                <td style="padding: .8rem 1rem;">
                                                    <span style="font-size: 13px; color: #374151;">{{ $article->creator->name ?? 'Unknown' }}</span>
                                                </td>

                                                {{-- Date --}}
                                                <td style="padding: .8rem 1rem;">
                                                    <span style="font-size: 12.5px; color: #6b7280;">
                                                        {{ $article->created_at->format('M d, Y') }}
                                                    </span>
                                                </td>

                                                {{-- Actions --}}
                                                <td style="padding: .8rem 1rem; text-align: center;">
                                                    <div class="d-flex justify-content-center gap-2">
                                                        <a href="{{ route('admin.kb.articles.edit', $article) }}" 
                                                        class="btn btn-sm" 
                                                        style="background: #f0f4fa; color: #185FA5; display: inline-flex; align-items: center; gap: 4px; font-size: 11px; font-weight: 500; padding: 5px 10px; border-radius: 6px; border: none; cursor: pointer; text-decoration: none; transition: all .13s;"
                                                        onmouseover="this.style.background='#185FA5'; this.style.color='#fff';" 
                                                        onmouseout="this.style.background='#f0f4fa'; this.style.color='#185FA5';">
                                                            <i class="fa fa-edit"></i>
                                                            Edit
                                                        </a>
                                                        <form action="{{ route('admin.kb.articles.destroy', $article) }}" method="POST" 
                                                            onsubmit="return confirm('Are you sure you want to delete this article?');"
                                                            style="display: inline;">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" 
                                                                    class="btn btn-sm" 
                                                                    style="background: #185ea51c; color: #374151; display: inline-flex; align-items: center; gap: 4px; font-size: 11px; font-weight: 500; padding: 5px 10px; border-radius: 6px; border: none; cursor: pointer; transition: all .13s;"
                                                                    onmouseover="this.style.background='#fee2e2'; this.style.color='#b91c1c';" 
                                                                    onmouseout="this.style.background='#185ea51c'; this.style.color='#374151';">
                                                                <i class="fa fa-trash"></i>
                                                                Delete
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="9" style="padding: 60px 20px; text-align: center;">
                                                    <div style="margin-bottom: 16px;">
                                                        <i class="fa fa-book" style="font-size: 48px; color: #d1d5db;"></i>
                                                    </div>
                                                    <p style="font-size: 15px; color: #6b7280; margin-bottom: 16px;">No articles found</p>
                                                    <a href="{{ route('admin.kb.articles.create') }}" class="btn btn-purple" 
                                                    style="background: #534AB7; color: #fff; display: inline-flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 500; padding: 7px 15px; border-radius: 7px; border: none; cursor: pointer; text-decoration: none;">
                                                        <i class="fa fa-plus"></i>
                                                        Create Your First Article
                                                    </a>
                                                </td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>

                                {{-- Pagination --}}
                                @if($articles->hasPages())
                                <div style="border-top: 0.5px solid #e5e7eb; background: #fafafa; padding: 12px 20px; display: flex; justify-content: flex-end;">
                                    {{ $articles->links() }}
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
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
@endsection