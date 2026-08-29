@extends('finance-partner.layouts.app')

@section('content')
    <div class="cs-titlebar"><h1 class="cs-title">{{ __('Notifications') }}</h1></div>

    <div class="cs-card">
        <div class="cs-tablewrap">
            @forelse ($notifications as $n)
                <a href="{{ $n->url ?? '#' }}" style="display:flex;gap:12px;align-items:flex-start;padding:14px 18px;border-bottom:0.5px solid var(--gray-100);text-decoration:none;">
                    <span style="flex:none;width:38px;height:38px;border-radius:10px;background:#E6F1FB;color:#185FA5;display:flex;align-items:center;justify-content:center;font-size:18px;"><i class="ri-notification-3-line"></i></span>
                    <span style="flex:1;min-width:0;">
                        <span style="display:block;font-size:13.5px;font-weight:600;color:var(--gray-900);">{{ $n->title }}</span>
                        @if ($n->body)<span style="display:block;font-size:12.5px;color:var(--gray-600);margin-top:2px;line-height:1.5;">{{ $n->body }}</span>@endif
                        <span style="display:block;font-size:11.5px;color:var(--gray-400);margin-top:4px;"><i class="ri-time-line"></i> {{ optional($n->created_at)->diffForHumans() }}</span>
                    </span>
                </a>
            @empty
                <div class="cs-empty" style="padding:48px 16px;text-align:center;">
                    <i class="ri-notification-off-line" style="font-size:34px;color:var(--gray-300);"></i>
                    <p class="cs-muted" style="margin-top:10px;">{{ __('No notifications yet.') }}</p>
                </div>
            @endforelse
        </div>
    </div>
@endsection
