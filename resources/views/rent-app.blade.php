<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>{{ __('Get started') }} · {{ getOption('app_name') ?: 'Centresidence' }}</title>
    @include('common.layouts.pwa-meta')
    <style>
        :root{ --blue:#185FA5; --blue-deep:#0F4A84; --ink:#1B1E22; --ink-2:#4A4F57; --ink-3:#7C828C; --paper:#0E1116; }
        *{box-sizing:border-box}
        body{margin:0;min-height:100vh;font-family:system-ui,-apple-system,"Segoe UI",Roboto,Helvetica,Arial,sans-serif;
            color:#E8EBEF;background:radial-gradient(120% 90% at 50% 0%, #16324f 0%, #0E1116 60%);
            display:flex;align-items:center;justify-content:center;padding:24px;}
        .gs{max-width:400px;width:100%;text-align:center;}
        .gs__logo{width:96px;height:96px;border-radius:24px;margin:0 auto 22px;display:block;box-shadow:0 16px 44px rgba(0,0,0,.45);}
        .gs__eyebrow{font-size:12px;font-weight:700;letter-spacing:.16em;text-transform:uppercase;color:#5CA0E6;margin:0 0 10px;}
        .gs h1{font-size:26px;font-weight:800;line-height:1.15;margin:0 0 12px;letter-spacing:-.01em;}
        .gs p.sub{font-size:15px;color:#AEB6C0;line-height:1.6;margin:0 auto 26px;max-width:32ch;}
        .gs__btn{display:flex;align-items:center;justify-content:center;gap:9px;width:100%;border:none;border-radius:14px;
            padding:15px 18px;font-size:15.5px;font-weight:700;cursor:pointer;text-decoration:none;transition:.15s;margin-bottom:11px;}
        .gs__btn--primary{background:#185FA5;color:#fff;box-shadow:0 12px 28px -12px rgba(24,95,165,.9);}
        .gs__btn--primary:hover{background:#0F4A84;}
        .gs__btn--ghost{background:rgba(255,255,255,.08);color:#E8EBEF;border:1px solid rgba(255,255,255,.14);}
        .gs__btn--ghost:hover{background:rgba(255,255,255,.14);}
        .gs__ios{display:none;margin:6px auto 0;padding:13px 15px;border-radius:12px;background:rgba(255,255,255,.06);
            border:1px solid rgba(255,255,255,.12);font-size:13px;color:#C7CDD6;line-height:1.55;text-align:left;}
        .gs__ios b{color:#fff;}
        .gs__steps{margin:26px 0 0;text-align:left;display:flex;flex-direction:column;gap:11px;}
        .gs__step{display:flex;gap:11px;align-items:flex-start;font-size:13.5px;color:#AEB6C0;line-height:1.5;}
        .gs__step .n{flex:none;width:22px;height:22px;border-radius:50%;background:rgba(92,160,230,.18);color:#5CA0E6;
            font-size:12px;font-weight:800;display:grid;place-items:center;}
        .gs__foot{margin-top:26px;font-size:12px;color:#7C838D;}
    </style>
</head>
<body>
    <div class="gs">
        <img src="{{ asset('assets/images/cs-icon.png') }}" alt="{{ getOption('app_name') ?: 'Centresidence' }}" class="gs__logo">
        <p class="gs__eyebrow">{{ getOption('app_name') ?: 'Centresidence' }}</p>
        <h1>{{ __('Set up your rent app') }}</h1>
        <p class="sub">{{ __('Pay rent over M-Pesa, keep every receipt, and build your rental score — from your phone.') }}</p>

        <button type="button" class="gs__btn gs__btn--primary" id="gsInstall" onclick="if(window.csPwaInstall)csPwaInstall()">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M12 3v12m0 0l-4-4m4 4l4-4M5 21h14" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            {{ __('Install the app') }}
        </button>

        <div class="gs__ios" id="gsIos">
            {{ __('On iPhone/iPad: tap the') }} <b>{{ __('Share') }}</b> {{ __('button, then') }} <b>{{ __('“Add to Home Screen.”') }}</b>
        </div>

        <a href="{{ route('login') }}" class="gs__btn gs__btn--ghost">{{ __('Just sign in') }} ›</a>

        <div class="gs__steps">
            <div class="gs__step"><span class="n">1</span><span>{{ __('Sign in with the email/phone and password from your welcome message.') }}</span></div>
            <div class="gs__step"><span class="n">2</span><span>{{ __('Set your own password when asked.') }}</span></div>
            <div class="gs__step"><span class="n">3</span><span>{{ __('Open your rent invoice and pay over M-Pesa — done.') }}</span></div>
        </div>

        <p class="gs__foot">{{ __('Trouble signing in? Contact your property manager.') }}</p>
    </div>

    <script>
        (function () {
            // iOS Safari has no install prompt — show the Add-to-Home-Screen hint instead of the button.
            var isIos = /iphone|ipad|ipod/i.test(navigator.userAgent);
            var standalone = window.matchMedia && window.matchMedia('(display-mode: standalone)').matches;
            if (standalone) { // already installed → go straight to sign in
                window.location.replace('{{ route('login') }}');
                return;
            }
            if (isIos) {
                var btn = document.getElementById('gsInstall'); if (btn) btn.style.display = 'none';
                var ios = document.getElementById('gsIos'); if (ios) ios.style.display = 'block';
            }
        })();
    </script>
</body>
</html>
