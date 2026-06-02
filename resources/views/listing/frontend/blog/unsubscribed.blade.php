@extends('layouts.public')

@section('title', 'Unsubscribed')

@section('content')
<div style="padding-top: 80px;">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="ow-card text-center" style="background: #fff; border: 0.5px solid #e5e7eb; border-radius: 12px; padding: 60px 40px; box-shadow: 0 4px 12px rgba(0,0,0,0.04), 0 0 0 1px rgba(24,95,165,0.05), 0 6px 18px rgba(24,95,165,0.06);">
                    <div style="margin-bottom: 20px;">
                        <div style="width: 64px; height: 64px; border-radius: 50%; background: #FAECE7; display: flex; align-items: center; justify-content: center; margin: 0 auto;">
                            <svg width="32" height="32" viewBox="0 0 32 32" fill="none" style="color: #993C1D;">
                                <circle cx="16" cy="16" r="12" stroke="currentColor" stroke-width="2"/>
                                <path d="M12 12L20 20M20 12L12 20" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                        </div>
                    </div>
                    
                    <h1 style="font-size: 24px; font-weight: 600; color: #111827; margin-bottom: 12px;">
                        You've Been Unsubscribed
                    </h1>
                    
                    <p style="font-size: 14px; color: #6b7280; margin-bottom: 8px; line-height: 1.6;">
                        <strong>{{ $subscriber->email }}</strong> has been removed from our mailing list.
                    </p>
                    
                    <p style="font-size: 14px; color: #6b7280; margin-bottom: 24px; line-height: 1.6;">
                        You will no longer receive email updates when new articles are published.
                    </p>
                    
                    <div style="display: flex; gap: 12px; justify-content: center; flex-wrap: wrap;">
                        <a href="{{ route('blog.index') }}" 
                           style="background: #185FA5; color: #fff; display: inline-flex; align-items: center; gap: 6px; font-size: 13px; font-weight: 500; padding: 10px 20px; border-radius: 7px; text-decoration: none; transition: all .13s;"
                           onmouseover="this.style.background='#0F4A84';" 
                           onmouseout="this.style.background='#185FA5';">
                            Browse Blog
                        </a>
                        <a href="{{ route('home') }}" 
                           style="background: #f3f4f6; color: #374151; display: inline-flex; align-items: center; gap: 6px; font-size: 13px; font-weight: 500; padding: 10px 20px; border-radius: 7px; text-decoration: none; border: 0.5px solid #e5e7eb; transition: all .13s;"
                           onmouseover="this.style.background='#e5e7eb';" 
                           onmouseout="this.style.background='#f3f4f6';">
                            Go to Homepage
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection