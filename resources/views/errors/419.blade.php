<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Session Expired') }} · {{ getOption('app_name', 'Centresidence') }}</title>
    <style>
        :root { --brand:#185FA5; --brand-dark:#0C447C; --ink:#1f2937; --muted:#6b7280; --bg:#f3f5f9; --card:#ffffff; --line:#e5e7eb; }
        @media (prefers-color-scheme: dark) { :root { --ink:#e5e7eb; --muted:#9ca3af; --bg:#0f172a; --card:#1e293b; --line:#334155; } }
        * { box-sizing:border-box; }
        body { margin:0; min-height:100vh; display:flex; align-items:center; justify-content:center; padding:24px;
               font-family:'Poppins',-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif; background:var(--bg); color:var(--ink); }
        .card { background:var(--card); border:1px solid var(--line); border-radius:20px; max-width:460px; width:100%;
                padding:44px 36px; text-align:center; box-shadow:0 12px 40px rgba(0,0,0,.08); }
        .icon { width:76px; height:76px; margin:0 auto 22px; border-radius:50%; display:flex; align-items:center; justify-content:center;
                background:rgba(24,95,165,.10); color:var(--brand); }
        .icon svg { animation:spin 1.6s linear infinite; }
        @keyframes spin { to { transform:rotate(360deg); } }
        @media (prefers-reduced-motion: reduce) { .icon svg { animation:none; } }
        .code { font-size:13px; font-weight:600; letter-spacing:.14em; text-transform:uppercase; color:var(--brand); margin-bottom:6px; }
        h1 { font-size:24px; margin:0 0 10px; font-weight:700; }
        p { color:var(--muted); font-size:15px; line-height:1.6; margin:0 0 8px; }
        .count { font-size:13px; color:var(--muted); margin:0 0 26px; }
        .count b { color:var(--brand); }
        .btn { display:inline-flex; align-items:center; gap:8px; padding:11px 24px; border-radius:10px; font-size:14px; font-weight:600;
               text-decoration:none; cursor:pointer; border:none; background:var(--brand); color:#fff; transition:transform .15s, box-shadow .15s, background .15s; }
        .btn:hover { transform:translateY(-1px); background:var(--brand-dark); box-shadow:0 6px 16px rgba(24,95,165,.28); }
        .brand { margin-top:26px; font-size:12px; color:var(--muted); }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon">
            <svg width="34" height="34" viewBox="0 0 24 24" fill="none">
                <path d="M21 12a9 9 0 1 1-2.64-6.36" stroke="currentColor" stroke-width="1.9" stroke-linecap="round"/>
                <path d="M21 4v4h-4" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </div>
        <div class="code">{{ __('Session Expired') }}</div>
        <h1>{{ __('Let’s refresh things') }}</h1>
        <p>{{ __('For your security your session timed out. No action is needed — we’re taking you back to sign in.') }}</p>
        <p class="count">{{ __('Redirecting in') }} <b><span id="secs">5</span>s</b> …</p>
        <a href="{{ route('login') }}" class="btn" id="go">{{ __('Continue now') }}</a>
        <div class="brand">{{ getOption('app_name', 'Centresidence') }}</div>
    </div>

    <script>
        (function () {
            // Reload the page the user came from if we have it (that refreshes the CSRF
            // token and, if the session is truly gone, will itself route to sign-in);
            // otherwise go straight to the login page.
            var ref = document.referrer;
            var target = (ref && ref.indexOf(location.host) !== -1) ? ref : @json(route('login'));
            document.getElementById('go').setAttribute('href', target);

            var n = 5, el = document.getElementById('secs');
            var t = setInterval(function () {
                n -= 1;
                if (el) el.textContent = n;
                if (n <= 0) { clearInterval(t); window.location.assign(target); }
            }, 1000);
        })();
    </script>
</body>
</html>
