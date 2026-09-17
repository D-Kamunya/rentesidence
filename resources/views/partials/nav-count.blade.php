{{-- Sidebar count badge — renders only when there's something to act on ($n > 0).
     Usage: @include('partials.nav-count', ['n' => $navBadges['key'] ?? 0]) --}}
@if (($n ?? 0) > 0)
<span style="display:inline-block;min-width:18px;height:18px;padding:0 5px;margin-left:6px;
    background:#B42318;color:#fff;font-size:10.5px;font-weight:700;line-height:18px;
    border-radius:999px;text-align:center;vertical-align:middle;">{{ $n > 99 ? '99+' : $n }}</span>
@endif
