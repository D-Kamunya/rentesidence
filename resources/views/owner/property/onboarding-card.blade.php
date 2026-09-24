<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Onboarding card') }} · {{ $property->name }}</title>
    <style>
        *{box-sizing:border-box}
        body{margin:0;background:#EDEBE6;font-family:system-ui,-apple-system,"Segoe UI",Roboto,Helvetica,Arial,sans-serif;
            color:#1B1E22;-webkit-print-color-adjust:exact;print-color-adjust:exact;}
        .toolbar{display:flex;gap:10px;justify-content:center;padding:18px;}
        .toolbar button,.toolbar a{border:none;border-radius:10px;padding:11px 20px;font-size:14px;font-weight:700;cursor:pointer;text-decoration:none;}
        .toolbar .print{background:#185FA5;color:#fff;}
        .toolbar .back{background:#fff;color:#374151;border:1px solid #d9d5cd;}
        .sheet{max-width:520px;margin:0 auto 40px;background:#fff;border-radius:20px;overflow:hidden;
            box-shadow:0 20px 60px rgba(20,23,28,.14);}
        .card__top{background:linear-gradient(135deg,#0F2A4A,#185FA5);color:#fff;padding:34px 32px 26px;text-align:center;}
        .card__logo{width:66px;height:66px;border-radius:16px;margin:0 auto 14px;display:block;box-shadow:0 8px 22px rgba(0,0,0,.3);}
        .card__prop{font-size:13px;font-weight:600;letter-spacing:.04em;text-transform:uppercase;color:#BBD4EE;margin:0 0 6px;}
        .card__h1{font-size:25px;font-weight:800;margin:0;line-height:1.15;letter-spacing:-.01em;}
        .card__body{padding:28px 32px 32px;text-align:center;}
        .qr{width:240px;height:240px;margin:0 auto 8px;display:flex;align-items:center;justify-content:center;}
        .qr svg{width:100%;height:100%;}
        .card__scan{font-size:15px;font-weight:700;color:#185FA5;margin:8px 0 2px;}
        .card__url{font-size:12px;color:#7C828C;font-family:ui-monospace,Menlo,Consolas,monospace;margin:0 0 22px;word-break:break-all;}
        .steps{text-align:left;display:flex;flex-direction:column;gap:12px;max-width:340px;margin:0 auto;}
        .step{display:flex;gap:12px;align-items:flex-start;font-size:14px;color:#4A4F57;line-height:1.5;}
        .step .n{flex:none;width:24px;height:24px;border-radius:50%;background:#E8F0F9;color:#185FA5;font-size:12.5px;font-weight:800;display:grid;place-items:center;}
        .step b{color:#1B1E22;}
        .card__foot{margin-top:24px;padding-top:16px;border-top:1px solid #eee;font-size:12px;color:#9aa2ad;}
        @media print{ body{background:#fff;} .toolbar{display:none;} .sheet{box-shadow:none;margin:0 auto;border-radius:0;max-width:100%;} }
    </style>
</head>
<body>
    <div class="toolbar">
        <button type="button" class="print" onclick="window.print()">{{ __('Print') }}</button>
        <a href="{{ route('owner.property.show', $property->id) }}" class="back">{{ __('Back') }}</a>
    </div>

    <div class="sheet">
        <div class="card__top">
            <img src="{{ asset('assets/images/cs-icon.png') }}" alt="{{ $appName }}" class="card__logo">
            <p class="card__prop">{{ $property->name }}</p>
            <h1 class="card__h1">{{ __('Set up your rent app') }}</h1>
        </div>
        <div class="card__body">
            <div class="qr">{!! $qrSvg !!}</div>
            <p class="card__scan">{{ __('Scan with your phone camera') }}</p>
            <p class="card__url">{{ $url }}</p>

            <div class="steps">
                <div class="step"><span class="n">1</span><span>{{ __('Scan the code, then') }} <b>{{ __('install the app') }}</b> {{ __('(or just sign in).') }}</span></div>
                <div class="step"><span class="n">2</span><span>{{ __('Sign in with the details in your') }} <b>{{ __('welcome SMS/email') }}</b> {{ __('and set your own password.') }}</span></div>
                <div class="step"><span class="n">3</span><span>{{ __('Open your rent invoice and') }} <b>{{ __('pay over M-Pesa') }}</b> {{ __('— your receipt is saved automatically.') }}</span></div>
            </div>

            <p class="card__foot">{{ __('Powered by') }} {{ $appName }} · {{ __('Trouble? Contact your property manager.') }}</p>
        </div>
    </div>
</body>
</html>
