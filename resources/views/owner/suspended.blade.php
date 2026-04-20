<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Suspended — Centresidence</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --brand:       #3685FC;
            --brand-dim:   rgba(54, 133, 252, 0.12);
            --brand-glow:  rgba(54, 133, 252, 0.35);
            --amber:       #F59E0B;
            --amber-dim:   rgba(245, 158, 11, 0.12);
            --red:         #EF4444;
            --ink:         #0F1623;
            --ink-2:       #374151;
            --ink-3:       #6B7280;
            --line:        rgba(15, 22, 35, 0.08);
            --surface:     #FFFFFF;
            --bg:          #F7F9FC;
        }

        html, body {
            height: 100%;
            background: var(--bg);
            font-family: 'DM Sans', sans-serif;
            color: var(--ink);
            -webkit-font-smoothing: antialiased;
        }

        /* ── Grain texture overlay ── */
        body::before {
            content: '';
            position: fixed; inset: 0;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.75' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.035'/%3E%3C/svg%3E");
            pointer-events: none;
            z-index: 0;
        }

        /* ── Ambient blobs ── */
        .blob {
            position: fixed;
            border-radius: 50%;
            filter: blur(90px);
            pointer-events: none;
            z-index: 0;
            animation: drift 18s ease-in-out infinite alternate;
        }
        .blob-1 {
            width: 520px; height: 520px;
            background: radial-gradient(circle, rgba(54,133,252,0.13) 0%, transparent 70%);
            top: -180px; right: -120px;
            animation-delay: 0s;
        }
        .blob-2 {
            width: 400px; height: 400px;
            background: radial-gradient(circle, rgba(245,158,11,0.10) 0%, transparent 70%);
            bottom: -120px; left: -100px;
            animation-delay: -7s;
        }
        @keyframes drift {
            from { transform: translate(0, 0) scale(1); }
            to   { transform: translate(30px, -20px) scale(1.05); }
        }

        /* ── Layout ── */
        .page {
            position: relative; z-index: 1;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* ── Top bar ── */
        .topbar {
            padding: 22px 40px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid var(--line);
            background: rgba(255,255,255,0.7);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }
        .topbar-logo {
            display: flex; align-items: center; gap: 10px;
            text-decoration: none;
        }
        .topbar-logo-mark {
            width: 36px; height: 36px;
            background: var(--brand);
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
        }
        .topbar-logo-mark svg { color: #fff; }
        .topbar-logo-name {
            font-family: 'DM Serif Display', serif;
            font-size: 18px;
            color: var(--ink);
            letter-spacing: -0.3px;
        }
        .topbar-logout {
            font-size: 13px;
            font-weight: 500;
            color: var(--ink-3);
            text-decoration: none;
            display: flex; align-items: center; gap: 6px;
            padding: 8px 16px;
            border-radius: 99px;
            border: 1px solid var(--line);
            background: var(--surface);
            transition: all 0.2s ease;
        }
        .topbar-logout:hover {
            color: var(--red);
            border-color: rgba(239,68,68,0.3);
            background: rgba(239,68,68,0.04);
        }

        /* ── Main content ── */
        .main {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 60px 24px;
        }

        .card {
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: 24px;
            box-shadow:
                0 2px 4px rgba(15,22,35,0.04),
                0 12px 40px rgba(15,22,35,0.07);
            max-width: 640px;
            width: 100%;
            overflow: hidden;
        }

        /* ── Card banner ── */
        .card-banner {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
            padding: 40px 48px 36px;
            position: relative;
            overflow: hidden;
        }
        .card-banner::after {
            content: '';
            position: absolute; inset: 0;
            background: repeating-linear-gradient(
                -45deg,
                transparent,
                transparent 12px,
                rgba(255,255,255,0.018) 12px,
                rgba(255,255,255,0.018) 24px
            );
        }
        .status-chip {
            display: inline-flex; align-items: center; gap: 6px;
            background: rgba(245,158,11,0.15);
            border: 1px solid rgba(245,158,11,0.35);
            color: #FCD34D;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 0.8px;
            text-transform: uppercase;
            padding: 5px 12px;
            border-radius: 99px;
            position: relative; z-index: 1;
            margin-bottom: 20px;
        }
        .status-chip-dot {
            width: 6px; height: 6px;
            background: #FCD34D;
            border-radius: 50%;
            animation: pulse-dot 2s ease-in-out infinite;
        }
        @keyframes pulse-dot {
            0%, 100% { opacity: 1; transform: scale(1); }
            50%       { opacity: 0.4; transform: scale(0.7); }
        }

        .banner-icon {
            width: 64px; height: 64px;
            background: rgba(245,158,11,0.15);
            border: 1px solid rgba(245,158,11,0.3);
            border-radius: 18px;
            display: flex; align-items: center; justify-content: center;
            margin-bottom: 20px;
            position: relative; z-index: 1;
        }

        .banner-title {
            font-family: 'DM Serif Display', serif;
            font-size: 30px;
            color: #FFFFFF;
            line-height: 1.2;
            margin-bottom: 10px;
            position: relative; z-index: 1;
        }
        .banner-title em {
            font-style: italic;
            color: rgba(255,255,255,0.55);
        }

        .banner-sub {
            font-size: 14px;
            color: rgba(255,255,255,0.5);
            line-height: 1.6;
            position: relative; z-index: 1;
        }

        /* ── Card body ── */
        .card-body {
            padding: 36px 48px 40px;
        }

        .info-block {
            background: var(--bg);
            border: 1px solid var(--line);
            border-radius: 14px;
            padding: 20px 22px;
            margin-bottom: 24px;
        }
        .info-block-label {
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 0.7px;
            text-transform: uppercase;
            color: var(--ink-3);
            margin-bottom: 12px;
        }
        .info-row {
            display: flex; align-items: flex-start; gap: 12px;
            padding: 10px 0;
            border-bottom: 1px solid var(--line);
        }
        .info-row:last-child { border-bottom: none; padding-bottom: 0; }
        .info-row:first-of-type { padding-top: 0; }
        .info-icon {
            width: 32px; height: 32px; flex-shrink: 0;
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
        }
        .info-icon.amber  { background: var(--amber-dim);  color: var(--amber); }
        .info-icon.blue   { background: var(--brand-dim);  color: var(--brand); }
        .info-icon.green  { background: rgba(16,185,129,0.1); color: #10B981; }
        .info-text strong {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: var(--ink);
            margin-bottom: 2px;
        }
        .info-text span {
            font-size: 12.5px;
            color: var(--ink-3);
            line-height: 1.5;
        }

        /* ── Divider ── */
        .divider {
            display: flex; align-items: center; gap: 12px;
            margin: 24px 0;
            color: var(--ink-3);
            font-size: 12px;
            font-weight: 500;
        }
        .divider::before, .divider::after {
            content: ''; flex: 1;
            height: 1px;
            background: var(--line);
        }

        /* ── Contact options ── */
        .contact-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 24px;
        }
        .contact-tile {
            display: flex; align-items: center; gap: 10px;
            padding: 14px 16px;
            border-radius: 12px;
            border: 1px solid var(--line);
            background: var(--bg);
            text-decoration: none;
            color: var(--ink);
            transition: all 0.18s ease;
            cursor: pointer;
        }
        .contact-tile:hover {
            border-color: var(--brand);
            background: var(--brand-dim);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px var(--brand-glow);
        }
        .contact-tile:hover .tile-icon { background: var(--brand); color: #fff; }
        .tile-icon {
            width: 36px; height: 36px; flex-shrink: 0;
            border-radius: 9px;
            background: rgba(15,22,35,0.06);
            color: var(--ink-2);
            display: flex; align-items: center; justify-content: center;
            transition: all 0.18s ease;
        }
        .tile-label {
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 0.4px;
            text-transform: uppercase;
            color: var(--ink-3);
            display: block;
        }
        .tile-value {
            font-size: 13px;
            font-weight: 500;
            color: var(--ink);
            display: block;
            margin-top: 1px;
        }

        /* ── Primary CTA ── */
        .cta-email {
            display: flex; align-items: center; justify-content: center; gap: 8px;
            width: 100%;
            padding: 15px;
            border-radius: 14px;
            background: var(--brand);
            color: #fff;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 4px 14px rgba(54,133,252,0.35);
        }
        .cta-email:hover {
            background: #2575eb;
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(54,133,252,0.45);
            color: #fff;
        }
        .cta-email:active { transform: translateY(0); }

        /* ── Footer note ── */
        .footer-note {
            text-align: center;
            margin-top: 20px;
            font-size: 12px;
            color: var(--ink-3);
            line-height: 1.6;
        }
        .footer-note a {
            color: var(--brand);
            text-decoration: none;
            font-weight: 500;
        }
        .footer-note a:hover { text-decoration: underline; }

        /* ── Responsive ── */
        @media (max-width: 640px) {
            .topbar { padding: 16px 20px; }
            .card-banner { padding: 28px 24px 24px; }
            .card-body { padding: 24px; }
            .contact-grid { grid-template-columns: 1fr; }
            .banner-title { font-size: 24px; }
        }
    </style>
</head>
<body>
<div class="blob blob-1"></div>
<div class="blob blob-2"></div>

<div class="page">

    {{-- Top bar --}}
    <header class="topbar">
        <a href="#" class="topbar-logo">
            <div class="col-md-6 mb-4 mb-md-0">
                <img src="{{ asset('assets/images/newlogo.png') }}"
                    alt="Centresidence Logo"
                    class="img-fluid shadow-lg"
                    style="max-width: 60px; border-radius: 20px;">
            </div>
            <span class="topbar-logo-name">Centresidence</span>
        </a>

        <a href="{{ route('logout') }}"
           onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
           class="topbar-logout">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                <polyline points="16 17 21 12 16 7"/>
                <line x1="21" y1="12" x2="9" y2="12"/>
            </svg>
            Sign out
        </a>
        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none;">@csrf</form>
    </header>

    {{-- Main --}}
    <main class="main">
        <div class="card">

            {{-- Banner --}}
            <div class="card-banner">
                <div class="status-chip">
                    <span class="status-chip-dot"></span>
                    Account Suspended
                </div>

                <div class="banner-icon">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#F59E0B" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="12" y1="8" x2="12" y2="12"/>
                        <line x1="12" y1="16" x2="12.01" y2="16"/>
                    </svg>
                </div>

                <h1 class="banner-title">Your account has been <em>temporarily suspended.</em></h1>
                <p class="banner-sub">Access to your owner dashboard is restricted. Our team can help you get back up and running quickly.</p>
            </div>

            {{-- Body --}}
            <div class="card-body">

                <div class="info-block">
                    <div class="info-block-label">What this means</div>

                    <div class="info-row">
                        <div class="info-icon amber">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                            </svg>
                        </div>
                        <div class="info-text">
                            <strong>Dashboard access blocked</strong>
                            <span>You cannot view properties, tenants, or financial data while suspended.</span>
                        </div>
                    </div>

                    <div class="info-row">
                        <div class="info-icon amber">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                                <circle cx="9" cy="7" r="4"/>
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                            </svg>
                        </div>
                        <div class="info-text">
                            <strong>Tenant interactions paused</strong>
                            <span>Active leases and tenants remain unaffected — only your management access is restricted.</span>
                        </div>
                    </div>

                    <div class="info-row">
                        <div class="info-icon green">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="20 6 9 17 4 12"/>
                            </svg>
                        </div>
                        <div class="info-text">
                            <strong>Your data is safe</strong>
                            <span>All your property data, records, and tenant history are preserved and intact.</span>
                        </div>
                    </div>
                </div>

                <div class="divider">Contact us to resolve</div>

                <div class="contact-grid">
                    <a href="mailto:support@centresidence.com" class="contact-tile">
                        <div class="tile-icon">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                                <polyline points="22,6 12,13 2,6"/>
                            </svg>
                        </div>
                        <div>
                            <span class="tile-label">Email</span>
                            <span class="tile-value">info@centresidence.com</span>
                        </div>
                    </a>

                    <a href="tel:+254700000000" class="contact-tile">
                        <div class="tile-icon">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 13a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.56 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 9.91a16 16 0 0 0 6.29 6.29l.91-.91a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/>
                            </svg>
                        </div>
                        <div>
                            <span class="tile-label">Phone</span>
                            <span class="tile-value">+254 714 389 005</span>
                        </div>
                    </a>
                </div>

                <a href="mailto:support@centresidence.com?subject=Account Suspension — Requesting Review&body=Hello Centresidence Support,%0A%0AMy account has been suspended and I would like to request a review.%0A%0AOwner Name: {{ Auth::user()->name ?? '' }}%0AEmail: {{ Auth::user()->email ?? '' }}%0A%0APlease advise on next steps.%0A%0AThank you."
                   class="cta-email">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                        <polyline points="22,6 12,13 2,6"/>
                    </svg>
                    Send a Resolution Request
                </a>

                <p class="footer-note">
                    Typical response time is <strong>under 24 hours</strong> on business days.<br>
                    For urgent matters, call us directly or visit our <a href="#">help centre</a>.
                </p>

            </div>
        </div>
    </main>

</div>
</body>
</html>