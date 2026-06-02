@extends('admin.layouts.app')

@php
    $pageTitle = 'Centresidence Blog Dashboard';
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
                                <h4 class="mb-0" style="font-size: 22px; font-weight: 500; color: #111827;">Blog Dashboard</h4>
                                </br>
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb m-0" style="display: flex; gap: 6px; font-size: 12px; color: #9ca3af; list-style: none; padding: 0;">
                                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" style="color: #185FA5; font-weight: 500;">Dashboard</a></li>
                                        <li class="breadcrumb-item active" aria-current="page" style="color: #9ca3af;">Blog</li>
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

                {{-- Stats Cards --}}
                <div class="row mb-4">
                    <div class="col-xl-3 col-md-6">
                        <div class="ow-card" style="background: #fff; border: 0.5px solid #e5e7eb; border-radius: 12px; padding: 20px; box-shadow: 0 4px 12px rgba(0,0,0,0.04);">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div style="font-size: 10px; font-weight: 500; text-transform: uppercase; letter-spacing: .07em; color: #9ca3af; margin-bottom: 8px;">Total Posts</div>
                                    <div style="font-size: 28px; font-weight: 600; color: #111827;">{{ $stats['total_posts'] }}</div>
                                </div>
                                <div style="width: 40px; height: 40px; border-radius: 10px; background: #E6F1FB; display: flex; align-items: center; justify-content: center;">
                                    <i class="fa fa-file-text" style="color: #185FA5; font-size: 18px;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6">
                        <div class="ow-card" style="background: #fff; border: 0.5px solid #e5e7eb; border-radius: 12px; padding: 20px; box-shadow: 0 4px 12px rgba(0,0,0,0.04);">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div style="font-size: 10px; font-weight: 500; text-transform: uppercase; letter-spacing: .07em; color: #9ca3af; margin-bottom: 8px;">Total Views</div>
                                    <div style="font-size: 28px; font-weight: 600; color: #111827;">{{ number_format($stats['total_views']) }}</div>
                                </div>
                                <div style="width: 40px; height: 40px; border-radius: 10px; background: #E1F5EE; display: flex; align-items: center; justify-content: center;">
                                    <i class="fa fa-eye" style="color: #0F6E56; font-size: 18px;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6">
                        <div class="ow-card" style="background: #fff; border: 0.5px solid #e5e7eb; border-radius: 12px; padding: 20px; box-shadow: 0 4px 12px rgba(0,0,0,0.04);">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div style="font-size: 10px; font-weight: 500; text-transform: uppercase; letter-spacing: .07em; color: #9ca3af; margin-bottom: 8px;">Comments</div>
                                    <div style="font-size: 28px; font-weight: 600; color: #111827;">{{ number_format($stats['total_comments']) }}</div>
                                </div>
                                <div style="width: 40px; height: 40px; border-radius: 10px; background: #FAEEDA; display: flex; align-items: center; justify-content: center;">
                                    <i class="fa fa-comments" style="color: #854F0B; font-size: 18px;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6">
                        <div class="ow-card" style="background: #fff; border: 0.5px solid #e5e7eb; border-radius: 12px; padding: 20px; box-shadow: 0 4px 12px rgba(0,0,0,0.04);">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div style="font-size: 10px; font-weight: 500; text-transform: uppercase; letter-spacing: .07em; color: #9ca3af; margin-bottom: 8px;">Likes</div>
                                    <div style="font-size: 28px; font-weight: 600; color: #111827;">{{ number_format($stats['total_likes']) }}</div>
                                </div>
                                <div style="width: 40px; height: 40px; border-radius: 10px; background: #FAECE7; display: flex; align-items: center; justify-content: center;">
                                    <i class="fa fa-heart" style="color: #993C1D; font-size: 18px;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    {{-- Recent Posts --}}
                    <div class="col-lg-8">
                        <div class="ow-card mb-4" style="background: #fff; border: 0.5px solid #e5e7eb; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.04);">
                            <div class="dash-card__head" style="padding: .75rem 1.1rem; border-bottom: 0.5px solid #e5e7eb; background: #fafafa; display: flex; justify-content: space-between; align-items: center;">
                                <h5 style="font-size: 14px; font-weight: 600; color: #111827; margin: 0;">Recent Posts</h5>
                                <a href="{{ route('admin.blog.posts') }}" style="font-size: 12px; color: #185FA5; font-weight: 500; text-decoration: none;">View All →</a>
                            </div>
                            <div class="table-responsive">
                                <table class="table mb-0">
                                    <thead>
                                        <tr>
                                            <th style="padding: .65rem 1rem; font-size: 10px; font-weight: 500; text-transform: uppercase; letter-spacing: .07em; color: #6b7280;">Title</th>
                                            <th style="padding: .65rem 1rem; font-size: 10px; font-weight: 500; text-transform: uppercase; letter-spacing: .07em; color: #6b7280;">Status</th>
                                            <th style="padding: .65rem 1rem; font-size: 10px; font-weight: 500; text-transform: uppercase; letter-spacing: .07em; color: #6b7280;">Views</th>
                                            <th style="padding: .65rem 1rem; font-size: 10px; font-weight: 500; text-transform: uppercase; letter-spacing: .07em; color: #6b7280;">Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($recentPosts as $post)
                                        <tr style="border-bottom: 0.5px solid #f3f4f6;" onmouseover="this.style.background='#f3f4f6';" onmouseout="this.style.background='transparent';">
                                            <td style="padding: .8rem 1rem;">
                                                <a href="{{ route('admin.blog.posts.edit', $post) }}" style="font-size: 13px; font-weight: 500; color: #185FA5; text-decoration: none;">
                                                    {{ Str::limit($post->title, 50) }}
                                                </a>
                                            </td>
                                            <td style="padding: .8rem 1rem;">
                                                <span class="badge" style="font-size: 11px; padding: 3px 9px; border-radius: 99px;
                                                    @if($post->status === 'published') background: #E1F5EE; color: #0F6E56;
                                                    @elseif($post->status === 'draft') background: #FAEEDA; color: #854F0B; border: 0.5px solid #F5D9A8;
                                                    @else background: #E6F1FB; color: #0C447C; border: 0.5px solid #B5D4F4;
                                                    @endif">
                                                    {{ ucfirst($post->status) }}
                                                </span>
                                            </td>
                                            <td style="padding: .8rem 1rem; font-size: 13px; color: #374151;">{{ $post->views_count }}</td>
                                            <td style="padding: .8rem 1rem; font-size: 12px; color: #9ca3af;">{{ $post->created_at->format('M d, Y') }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    {{-- Pending Comments --}}
                    <div class="col-lg-4">
                        <div class="ow-card mb-4" style="background: #fff; border: 0.5px solid #e5e7eb; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.04);">
                            <div class="dash-card__head" style="padding: .75rem 1.1rem; border-bottom: 0.5px solid #e5e7eb; background: #fafafa;">
                                <h5 style="font-size: 14px; font-weight: 600; color: #111827; margin: 0;">Pending Comments</h5>
                            </div>
                            <div style="padding: 20px;">
                                @forelse($pendingComments as $comment)
                                <div style="padding: 12px 0; border-bottom: 0.5px solid #f3f4f6;">
                                    <div style="font-size: 13px; font-weight: 500; color: #374151; margin-bottom: 4px;">
                                        {{ $comment->author_name }}
                                    </div>
                                    <p style="font-size: 12px; color: #6b7280; margin-bottom: 8px;">
                                        {{ Str::limit($comment->content, 80) }}
                                    </p>
                                    <div style="display: flex; gap: 8px;">
                                        <form action="{{ route('admin.blog.comments.approve', $comment) }}" method="POST">
                                            @csrf
                                            <button type="submit" style="background: #E1F5EE; color: #0F6E56; border: none; font-size: 11px; padding: 4px 10px; border-radius: 6px; cursor: pointer;">Approve</button>
                                        </form>
                                        <form action="{{ route('admin.blog.comments.spam', $comment) }}" method="POST">
                                            @csrf
                                            <button type="submit" style="background: #FAECE7; color: #993C1D; border: none; font-size: 11px; padding: 4px 10px; border-radius: 6px; cursor: pointer;">Spam</button>
                                        </form>
                                    </div>
                                </div>
                                @empty
                                <p style="font-size: 13px; color: #9ca3af; text-align: center;">No pending comments</p>
                                @endforelse
                            </div>
                        </div>

                        {{-- Popular Posts --}}
                        <div class="ow-card" style="background: #fff; border: 0.5px solid #e5e7eb; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.04);">
                            <div class="dash-card__head" style="padding: .75rem 1.1rem; border-bottom: 0.5px solid #e5e7eb; background: #fafafa;">
                                <h5 style="font-size: 14px; font-weight: 600; color: #111827; margin: 0;">🔥 Popular Posts</h5>
                            </div>
                            <div style="padding: 16px;">
                                @foreach($popularPosts as $popular)
                                <div style="padding: 8px 0; border-bottom: 0.5px solid #f3f4f6;">
                                    <a href="{{ route('blog.show', $popular->slug) }}" target="_blank" style="font-size: 13px; font-weight: 500; color: #185FA5; text-decoration: none;">
                                        {{ Str::limit($popular->title, 40) }}
                                    </a>
                                    <div style="font-size: 11px; color: #9ca3af; margin-top: 2px;">{{ $popular->views_count }} views</div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection