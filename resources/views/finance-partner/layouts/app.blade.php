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
        .fp-topbar { display:flex; align-items:center; justify-content:space-between; gap:12px; background:#fff; border-bottom:0.5px solid #e5e7eb; padding:10px 24px; }
        .fp-topbar__title { font-size:15px; font-weight:600; color:#111827; }
        .fp-topbar__right { display:flex; align-items:center; gap:10px; }
        .fp-content { padding:24px; flex:1; }

        /* Topbar dropdowns (bell + profile) — self-contained, no Bootstrap dep */
        .fp-dd { position:relative; }
        .fp-iconbtn { position:relative; width:38px; height:38px; border-radius:10px; border:0.5px solid #e5e7eb; background:#fff; color:#374151;
            display:flex; align-items:center; justify-content:center; font-size:19px; cursor:pointer; transition:background .13s, border-color .13s; }
        .fp-iconbtn:hover { background:#f3f4f6; border-color:#d1d5db; }
        .fp-badge { position:absolute; top:-5px; right:-5px; min-width:17px; height:17px; padding:0 4px; border-radius:9px; background:#DC2626; color:#fff;
            font-size:10px; font-weight:700; display:flex; align-items:center; justify-content:center; box-shadow:0 0 0 2px #fff; }
        .fp-userbtn { display:flex; align-items:center; gap:8px; background:#fff; border:0.5px solid #e5e7eb; border-radius:10px; padding:5px 10px 5px 5px; cursor:pointer; transition:background .13s; }
        .fp-userbtn:hover { background:#f9fafb; }
        .fp-avatar { width:30px; height:30px; border-radius:8px; background:#185FA5; color:#fff; font-weight:700; font-size:13px; display:flex; align-items:center; justify-content:center; }
        .fp-userbtn__name { font-size:13px; font-weight:500; color:#374151; max-width:140px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
        .fp-userbtn i { color:#9ca3af; font-size:16px; }
        .fp-dd__menu { position:absolute; right:0; top:calc(100% + 8px); width:320px; background:#fff; border:0.5px solid #e5e7eb; border-radius:12px;
            box-shadow:0 16px 40px rgba(0,0,0,.14); overflow:hidden; z-index:50; display:none; }
        .fp-dd__menu--sm { width:210px; }
        .fp-dd.is-open .fp-dd__menu { display:block; }
        .fp-dd__head { padding:12px 16px; font-size:13px; font-weight:600; color:#111827; border-bottom:0.5px solid #f1f5f9; }
        .fp-dd__scroll { max-height:340px; overflow-y:auto; }
        .fp-dd__empty { padding:26px 16px; text-align:center; font-size:12.5px; color:#9ca3af; }
        .fp-noti { display:flex; gap:11px; padding:11px 16px; text-decoration:none; border-bottom:0.5px solid #f6f8fb; transition:background .12s; }
        .fp-noti:hover { background:#F0F7FD; }
        .fp-noti__ic { flex:none; width:34px; height:34px; border-radius:9px; background:#E6F1FB; color:#185FA5; display:flex; align-items:center; justify-content:center; font-size:16px; }
        .fp-noti__body { display:flex; flex-direction:column; min-width:0; }
        .fp-noti__title { font-size:12.5px; color:#374151; line-height:1.4; }
        .fp-noti__time { font-size:11px; color:#9ca3af; margin-top:2px; }
        .fp-dd__foot { display:block; text-align:center; padding:11px; font-size:12.5px; font-weight:600; color:#185FA5; text-decoration:none; border-top:0.5px solid #f1f5f9; }
        .fp-dd__foot:hover { background:#F0F7FD; }
        .fp-dd__item { display:flex; align-items:center; gap:10px; padding:11px 14px; font-size:13px; color:#374151; text-decoration:none; transition:background .12s; }
        .fp-dd__item:hover { background:#f3f4f6; }
        .fp-dd__item i { font-size:16px; color:#6b7280; }
        .fp-dd__item--danger { color:#B42318; } .fp-dd__item--danger i { color:#B42318; }
        .fp-dd__div { height:0.5px; background:#f1f5f9; }

        /* Knowledge base — the standout entry, using the blog newsletter-card blue so
           it doesn't feel plain. (!important on colour beats the global a:hover rule.) */
        .fp-nav a[href*="knowledge-base"] {
            background:linear-gradient(135deg,#185FA5,#0F4A84); color:#fff !important;
            box-shadow:0 4px 12px rgba(24,95,165,.2); margin-top:2px;
        }
        .fp-nav a[href*="knowledge-base"] i { color:#fff; }
        .fp-nav a[href*="knowledge-base"]:hover,
        .fp-nav a[href*="knowledge-base"].is-active { background:linear-gradient(135deg,#1c72c2,#0F4A84); color:#fff !important; }
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
                <a href="{{ route('finance-partner.remittances') }}" class="{{ str_starts_with($r, 'finance-partner.remittances') ? 'is-active' : '' }}"><i class="ri-bank-line"></i> {{ __('Remittances') }}</a>
                <a href="{{ route('finance-partner.payout-account') }}" class="{{ str_starts_with($r, 'finance-partner.payout-account') ? 'is-active' : '' }}"><i class="ri-wallet-3-line"></i> {{ __('Payout account') }}</a>
                <a href="{{ route('finance-partner.profile') }}" class="{{ str_starts_with($r, 'finance-partner.profile') ? 'is-active' : '' }}"><i class="ri-user-settings-line"></i> {{ __('My profile') }}</a>
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
                @php $fpNotifs = getNotificationLimit(auth()->id()); @endphp
                <div class="fp-topbar__right">
                    {{-- Notification bell --}}
                    <div class="fp-dd">
                        <button type="button" class="fp-iconbtn" data-fp-dd="fpNotifMenu" aria-label="{{ __('Notifications') }}">
                            <i class="ri-notification-3-line"></i>
                            @if (count($fpNotifs) > 0)<span class="fp-badge">{{ count($fpNotifs) > 9 ? '9+' : count($fpNotifs) }}</span>@endif
                        </button>
                        <div class="fp-dd__menu" id="fpNotifMenu">
                            <div class="fp-dd__head">{{ __('Notifications') }}</div>
                            <div class="fp-dd__scroll">
                                @forelse ($fpNotifs as $n)
                                    <a href="{{ route('notification.status', ['id' => $n->id, 'role' => auth()->user()->role]) }}?url={{ urlencode($n->url ?? route('finance-partner.notification')) }}" class="fp-noti">
                                        <span class="fp-noti__ic"><i class="ri-notification-3-line"></i></span>
                                        <span class="fp-noti__body">
                                            <span class="fp-noti__title">{{ $n->title }}</span>
                                            <span class="fp-noti__time">{{ optional($n->created_at)->diffForHumans() }}</span>
                                        </span>
                                    </a>
                                @empty
                                    <div class="fp-dd__empty">{{ __('No new notifications') }}</div>
                                @endforelse
                            </div>
                            <a href="{{ route('finance-partner.notification') }}" class="fp-dd__foot">{{ __('See all') }}</a>
                        </div>
                    </div>
                    {{-- Profile --}}
                    <div class="fp-dd">
                        <button type="button" class="fp-userbtn" data-fp-dd="fpUserMenu">
                            <span class="fp-avatar">{{ strtoupper(substr(optional(auth()->user())->name ?? 'P', 0, 1)) }}</span>
                            <span class="fp-userbtn__name">{{ optional(auth()->user())->name }}</span>
                            <i class="ri-arrow-down-s-line"></i>
                        </button>
                        <div class="fp-dd__menu fp-dd__menu--sm" id="fpUserMenu">
                            <a href="{{ route('finance-partner.profile') }}" class="fp-dd__item"><i class="ri-user-line"></i> {{ __('My profile') }}</a>
                            <a href="{{ route('finance-partner.payout-account') }}" class="fp-dd__item"><i class="ri-wallet-3-line"></i> {{ __('Payout account') }}</a>
                            <div class="fp-dd__div"></div>
                            <a href="{{ url('/logout') }}" class="fp-dd__item fp-dd__item--danger"><i class="ri-logout-box-line"></i> {{ __('Logout') }}</a>
                        </div>
                    </div>
                </div>
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
    <script>
        (function () {
            document.addEventListener('click', function (e) {
                var btn = e.target.closest('[data-fp-dd]');
                var openDd = document.querySelector('.fp-dd.is-open');
                if (btn) {
                    var dd = btn.closest('.fp-dd');
                    var wasOpen = dd.classList.contains('is-open');
                    if (openDd && openDd !== dd) openDd.classList.remove('is-open');
                    dd.classList.toggle('is-open', !wasOpen);
                    e.stopPropagation();
                    return;
                }
                if (openDd && !e.target.closest('.fp-dd__menu')) openDd.classList.remove('is-open');
            });
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') { var o = document.querySelector('.fp-dd.is-open'); if (o) o.classList.remove('is-open'); }
            });
        })();
    </script>
    @include('common.layouts.script')
    @stack('script')
</body>
</html>
