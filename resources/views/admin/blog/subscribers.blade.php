@extends('admin.layouts.app')

@php
    $pageTitle = 'Centresidence Blog Subscribers';
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
                                <h4 class="mb-0" style="font-size: 22px; font-weight: 500; color: #111827;">Blog Subscribers</h4>
                                </br>
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb m-0" style="display: flex; gap: 6px; font-size: 12px; color: #9ca3af; list-style: none; padding: 0;">
                                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" style="color: #185FA5; font-weight: 500;">Dashboard</a></li>
                                        <li class="breadcrumb-item"><a href="{{ route('admin.blog.dashboard') }}" style="color: #185FA5; font-weight: 500;">Blog</a></li>
                                        <li class="breadcrumb-item active" aria-current="page" style="color: #9ca3af;">Subscribers</li>
                                    </ol>
                                </nav>
                            </div>
                            <div>
                                <a href="{{ route('admin.blog.dashboard') }}" class="btn btn-ghost" 
                                style="background: #f3f4f6; color: #374151; display: inline-flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 500; padding: 7px 15px; border-radius: 7px; border: 0.5px solid #e5e7eb; cursor: pointer; text-decoration: none;">
                                    <i class="fa fa-arrow-left"></i>
                                    Back to Dashboard
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Stats --}}
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="ow-card" style="background: #fff; border: 0.5px solid #e5e7eb; border-radius: 12px; padding: 20px; box-shadow: 0 4px 12px rgba(0,0,0,0.04);">
                            <div style="font-size: 10px; font-weight: 500; text-transform: uppercase; letter-spacing: .07em; color: #9ca3af; margin-bottom: 8px;">Total Subscribers</div>
                            <div style="font-size: 28px; font-weight: 600; color: #111827;">{{ $subscribers->total() }}</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="ow-card" style="background: #fff; border: 0.5px solid #e5e7eb; border-radius: 12px; padding: 20px; box-shadow: 0 4px 12px rgba(0,0,0,0.04);">
                            <div style="font-size: 10px; font-weight: 500; text-transform: uppercase; letter-spacing: .07em; color: #9ca3af; margin-bottom: 8px;">Active Subscribers</div>
                            <div style="font-size: 28px; font-weight: 600; color: #0F6E56;">{{ \App\Models\BlogSubscriber::active()->count() }}</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="ow-card" style="background: #fff; border: 0.5px solid #e5e7eb; border-radius: 12px; padding: 20px; box-shadow: 0 4px 12px rgba(0,0,0,0.04);">
                            <div style="font-size: 10px; font-weight: 500; text-transform: uppercase; letter-spacing: .07em; color: #9ca3af; margin-bottom: 8px;">This Month</div>
                            <div style="font-size: 28px; font-weight: 600; color: #185FA5;">{{ \App\Models\BlogSubscriber::whereMonth('created_at', now()->month)->count() }}</div>
                        </div>
                    </div>
                </div>

                {{-- Subscribers Table --}}
                <div class="row">
                    <div class="col-12">
                        <div class="card" style="background: #fff; border: 0.5px solid #e5e7eb; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.04);">
                            <div class="table-responsive">
                                <table class="table mb-0">
                                    <thead style="background: #fafafa; border-bottom: 0.5px solid #e5e7eb;">
                                        <tr>
                                            <th style="padding: .65rem 1rem; font-size: 10px; font-weight: 500; text-transform: uppercase; letter-spacing: .07em; color: #6b7280;">Email</th>
                                            <th style="padding: .65rem 1rem; font-size: 10px; font-weight: 500; text-transform: uppercase; letter-spacing: .07em; color: #6b7280;">Name</th>
                                            <th style="padding: .65rem 1rem; font-size: 10px; font-weight: 500; text-transform: uppercase; letter-spacing: .07em; color: #6b7280;">Status</th>
                                            <th style="padding: .65rem 1rem; font-size: 10px; font-weight: 500; text-transform: uppercase; letter-spacing: .07em; color: #6b7280;">Subscribed At</th>
                                            <th style="padding: .65rem 1rem; font-size: 10px; font-weight: 500; text-transform: uppercase; letter-spacing: .07em; color: #6b7280; text-align: center;">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($subscribers as $subscriber)
                                        <tr style="border-bottom: 0.5px solid #f3f4f6; {{ $loop->even ? 'background: #fafafa;' : '' }}"
                                            onmouseover="this.style.background='#f3f4f6';" 
                                            onmouseout="this.style.background='{{ $loop->even ? '#fafafa' : '#ffffff' }}';">
                                            <td style="padding: .8rem 1rem;">
                                                <span style="font-size: 13px; font-weight: 500; color: #374151;">{{ $subscriber->email }}</span>
                                            </td>
                                            <td style="padding: .8rem 1rem;">
                                                <span style="font-size: 13px; color: #374151;">{{ $subscriber->name ?? '—' }}</span>
                                            </td>
                                            <td style="padding: .8rem 1rem;">
                                                @if($subscriber->is_active)
                                                <span class="badge" style="background: #E1F5EE; color: #0F6E56; font-size: 11px; padding: 3px 9px; border-radius: 99px;">
                                                    Active
                                                </span>
                                                @else
                                                <span class="badge" style="background: #F3F4F6; color: #6b7280; font-size: 11px; padding: 3px 9px; border-radius: 99px; border: 0.5px solid #e5e7eb;">
                                                    Unsubscribed
                                                </span>
                                                @endif
                                            </td>
                                            <td style="padding: .8rem 1rem;">
                                                <span style="font-size: 12px; color: #9ca3af;">{{ $subscriber->created_at->format('M d, Y') }}</span>
                                            </td>
                                            <td style="padding: .8rem 1rem; text-align: center;">
                                                <form action="{{ route('admin.blog.subscribers.destroy', $subscriber) }}" method="POST" onsubmit="return confirm('Remove this subscriber?');" style="display: inline;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm" 
                                                            style="background: #185ea51c; color: #374151; border: none; font-size: 11px; padding: 5px 10px; border-radius: 6px; cursor: pointer;"
                                                            onmouseover="this.style.background='#fee2e2'; this.style.color='#b91c1c';" 
                                                            onmouseout="this.style.background='#185ea51c'; this.style.color='#374151';">
                                                        <i class="fa fa-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="5" style="padding: 60px 20px; text-align: center;">
                                                <p style="font-size: 15px; color: #6b7280;">No subscribers yet</p>
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            @if($subscribers->hasPages())
                            <div style="border-top: 0.5px solid #e5e7eb; background: #fafafa; padding: 12px 20px; display: flex; justify-content: flex-end;">
                                {{ $subscribers->links() }}
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