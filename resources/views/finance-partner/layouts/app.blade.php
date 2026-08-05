<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>{{ getOption('app_name') . ' — ' . ($pageTitle ?? 'Finance Partner') }}</title>
    @include('common.layouts.style')
    @stack('style')
    <style>
        .fp-shell { display:flex; min-height:100vh; background:#f6f8fb; }
        .fp-side { width:240px; flex:none; background:#fff; border-right:0.5px solid #e5e7eb; display:flex; flex-direction:column; position:sticky; top:0; height:100vh; }
        .fp-brand { padding:20px; font-size:16px; font-weight:700; color:#185FA5; border-bottom:0.5px solid #e5e7eb; display:flex; align-items:center; gap:8px; }
        .fp-nav { padding:12px; display:flex; flex-direction:column; gap:4px; flex:1; }
        .fp-nav a { display:flex; align-items:center; gap:10px; padding:10px 12px; border-radius:8px; font-size:13px; font-weight:500; color:#374151; text-decoration:none; transition:all .13s; }
        .fp-nav a:hover { background:#f3f4f6; color:#111827; }
        .fp-nav a.is-active { background:#E6F1FB; color:#185FA5; }
        .fp-nav a i { font-size:17px; }
        .fp-side__foot { padding:12px; border-top:0.5px solid #e5e7eb; }
        .fp-main { flex:1; min-width:0; display:flex; flex-direction:column; }
        .fp-topbar { display:flex; align-items:center; justify-content:space-between; gap:12px; background:#fff; border-bottom:0.5px solid #e5e7eb; padding:14px 24px; }
        .fp-topbar__title { font-size:15px; font-weight:600; color:#111827; }
        .fp-topbar__user { font-size:13px; color:#6b7280; }
        .fp-content { padding:24px; flex:1; }
        @media (max-width:991px) {
            .fp-shell { flex-direction:column; }
            .fp-side { width:100%; height:auto; position:static; flex-direction:row; flex-wrap:wrap; align-items:center; }
            .fp-nav { flex-direction:row; flex-wrap:wrap; flex:1; }
            .fp-side__foot { border-top:none; }
        }
    </style>
</head>
<body>
    <div class="fp-shell">
        <aside class="fp-side">
            <div class="fp-brand"><i class="ri-bank-line"></i> {{ __('Finance Partner') }}</div>
            <nav class="fp-nav">
                @php $r = Route::currentRouteName(); @endphp
                <a href="{{ route('finance-partner.dashboard') }}" class="{{ $r === 'finance-partner.dashboard' ? 'is-active' : '' }}"><i class="ri-dashboard-line"></i> {{ __('Dashboard') }}</a>
                <a href="{{ route('finance-partner.products.index') }}" class="{{ str_starts_with($r, 'finance-partner.products') ? 'is-active' : '' }}"><i class="ri-stack-line"></i> {{ __('My Products') }}</a>
                <a href="{{ route('finance-partner.applications.index') }}" class="{{ str_starts_with($r, 'finance-partner.applications') ? 'is-active' : '' }}"><i class="ri-file-list-3-line"></i> {{ __('Applications') }}</a>
                <a href="{{ route('finance-partner.facilities') }}" class="{{ $r === 'finance-partner.facilities' ? 'is-active' : '' }}"><i class="ri-funds-line"></i> {{ __('Facilities') }}</a>
                <a href="{{ route('finance-partner.learn.modules') }}" class="{{ str_starts_with($r, 'finance-partner.learn') ? 'is-active' : '' }}"><i class="ri-book-open-line"></i> {{ __('Modules') }}</a>
                <a href="{{ route('finance-partner.kb.index') }}" class="{{ str_starts_with($r, 'finance-partner.kb') ? 'is-active' : '' }}"><i class="ri-graduation-cap-line"></i> {{ __('Knowledge base') }}</a>
            </nav>
            <div class="fp-side__foot">
                <a href="{{ url('/logout') }}" class="fp-nav" style="display:flex;color:#993C1D;font-size:13px;text-decoration:none;padding:10px 12px;"><i class="ri-logout-box-line" style="margin-right:8px;"></i> {{ __('Logout') }}</a>
            </div>
        </aside>
        <div class="fp-main">
            <div class="fp-topbar">
                <span class="fp-topbar__title">{{ $pageTitle ?? 'Finance Partner' }}</span>
                <span class="fp-topbar__user">{{ optional(auth()->user())->name }}</span>
            </div>
            <div class="fp-content">
                @include('centresidence._design')
                @if (session('success')) <div class="cs-alert is-success">{{ session('success') }}</div> @endif
                @if (session('error')) <div class="cs-alert is-danger">{{ session('error') }}</div> @endif
                @if ($errors->any()) <div class="cs-alert is-danger">{{ $errors->first() }}</div> @endif
                @yield('content')
            </div>
        </div>
    </div>
    @include('common.layouts.script')
    @stack('script')
</body>
</html>
