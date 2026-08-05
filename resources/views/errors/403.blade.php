<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Access Denied') }} · {{ getOption('app_name', 'Centresidence') }}</title>
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
        .code { font-size:13px; font-weight:600; letter-spacing:.14em; text-transform:uppercase; color:var(--brand); margin-bottom:6px; }
        h1 { font-size:24px; margin:0 0 10px; font-weight:700; }
        p { color:var(--muted); font-size:15px; line-height:1.6; margin:0 0 28px; }
        .actions { display:flex; gap:12px; justify-content:center; flex-wrap:wrap; }
        .btn { display:inline-flex; align-items:center; gap:8px; padding:11px 22px; border-radius:10px; font-size:14px; font-weight:600;
               text-decoration:none; cursor:pointer; border:1px solid transparent; transition:transform .15s, box-shadow .15s, background .15s; }
        .btn:hover { transform:translateY(-1px); }
        .btn--primary { background:var(--brand); color:#fff; }
        .btn--primary:hover { background:var(--brand-dark); box-shadow:0 6px 16px rgba(24,95,165,.28); }
        .btn--ghost { background:transparent; color:var(--ink); border-color:var(--line); }
        .brand { margin-top:26px; font-size:12px; color:var(--muted); }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon">
            <svg width="34" height="34" viewBox="0 0 24 24" fill="none">
                <rect x="4" y="10" width="16" height="11" rx="2.5" stroke="currentColor" stroke-width="1.8"/>
                <path d="M8 10V7a4 4 0 0 1 8 0v3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                <circle cx="12" cy="15.5" r="1.4" fill="currentColor"/>
            </svg>
        </div>
        <div class="code">{{ __('Error 403') }}</div>
        <h1>{{ __('Access Denied') }}</h1>
        <p>{{ $exception && $exception->getMessage() ? $exception->getMessage() : __("You don't have permission to view this page. If you believe this is a mistake, contact your administrator.") }}</p>
        <div class="actions">
            <a href="#" onclick="history.length > 1 ? history.back() : window.location.assign('{{ url('/') }}'); return false;" class="btn btn--ghost">
                {{ __('Go Back') }}
            </a>
            <a href="{{ url('/') }}" class="btn btn--primary">{{ __('Back to Safety') }}</a>
        </div>
        <div class="brand">{{ getOption('app_name', 'Centresidence') }}</div>
    </div>
</body>
</html>
