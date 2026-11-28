<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Vacancies poster') }} · {{ $title }}</title>
    <style>
        *{box-sizing:border-box}
        body{margin:0;background:#EDEBE6;font-family:system-ui,-apple-system,"Segoe UI",Roboto,Helvetica,Arial,sans-serif;
            color:#1B1E22;-webkit-print-color-adjust:exact;print-color-adjust:exact;}
        .toolbar{display:flex;gap:10px;justify-content:center;padding:18px;}
        .toolbar button,.toolbar a{border:none;border-radius:10px;padding:11px 20px;font-size:14px;font-weight:700;cursor:pointer;text-decoration:none;}
        .toolbar .print{background:#185FA5;color:#fff;}
        .toolbar .back{background:#fff;color:#374151;border:1px solid #d9d5cd;}
        .sheet{max-width:600px;margin:0 auto 40px;background:#fff;border-radius:22px;overflow:hidden;
            box-shadow:0 20px 60px rgba(20,23,28,.14);}
        .top{background:linear-gradient(135deg,#0F3D6B,#185FA5);color:#fff;padding:40px 34px 30px;text-align:center;}
        .brand{font-size:13px;font-weight:800;letter-spacing:.16em;text-transform:uppercase;color:#CFE3F7;margin:0 0 8px;}
        .h1{font-size:30px;font-weight:800;margin:0;line-height:1.12;letter-spacing:-.01em;}
        .tagline{font-size:12.5px;letter-spacing:.12em;text-transform:uppercase;color:#AFD0F0;margin:10px 0 0;}
        .body{padding:34px 34px 38px;text-align:center;}
        .scan-h{font-size:22px;font-weight:800;color:#0F3D6B;margin:0 0 20px;}
        .qr{width:340px;max-width:82%;margin:0 auto 10px;}
        .qr svg{width:100%;height:auto;display:block;}
        .url{font-size:12.5px;color:#7C828C;font-family:ui-monospace,Menlo,Consolas,monospace;margin:4px 0 26px;word-break:break-all;}
        .steps{text-align:left;display:flex;flex-direction:column;gap:13px;max-width:360px;margin:0 auto;}
        .step{display:flex;gap:12px;align-items:flex-start;font-size:14.5px;color:#4A4F57;line-height:1.5;}
        .step .n{flex:none;width:26px;height:26px;border-radius:50%;background:#E7F0F9;color:#185FA5;font-size:13px;font-weight:800;display:grid;place-items:center;}
        .step b{color:#1B1E22;}
        .foot{margin-top:26px;padding-top:16px;border-top:1px solid #eee;font-size:12px;color:#9aa2ad;}
        @media print{ body{background:#fff;} .toolbar{display:none;} .sheet{box-shadow:none;margin:0 auto;border-radius:0;max-width:100%;} }
    </style>
</head>
<body>
    <div class="toolbar">
        <button type="button" class="print" onclick="window.print()">{{ __('Print') }}</button>
        <a href="{{ route('owner.vacancy.share') }}" class="back">{{ __('Back') }}</a>
    </div>

    <div class="sheet">
        <div class="top">
            <p class="brand">{{ $appName }}</p>
            <h1 class="h1">{{ $title }}</h1>
            <p class="tagline">{{ __('Real Estate. Simplified. Connected.') }}</p>
        </div>
        <div class="body">
            <h2 class="scan-h">{{ $subtitle }}</h2>
            <div class="qr">{!! $qrSvg !!}</div>
            <p class="url">{{ $url }}</p>
            <div class="steps">
                <div class="step"><span class="n">1</span><span>{{ __('Open your phone camera and point it at the code.') }}</span></div>
                <div class="step"><span class="n">2</span><span>{{ __('Tap the link that appears.') }}</span></div>
                <div class="step"><span class="n">3</span><span><b>{{ __('Browse the available homes and apply online') }}</b> — {{ __('no app to install.') }}</span></div>
            </div>
            <p class="foot">{{ __('This code always shows the current vacancies. Post it once — it stays up to date.') }}</p>
        </div>
    </div>
</body>
</html>
