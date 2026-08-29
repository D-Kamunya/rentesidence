<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>{{ __('Verify Agreement') }} — {{ getOption('app_name') ?: config('app.name') }}</title>
    <style>
        * { box-sizing: border-box; }
        body { margin:0; background:#f3f4f6; color:#111827; font-family: system-ui, -apple-system, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; -webkit-font-smoothing: antialiased; }
        .vf-wrap { max-width:640px; margin:0 auto; padding:40px 18px; }
        .vf-brand { text-align:center; margin-bottom:22px; }
        .vf-brand img { height:40px; }
        .vf-brand .vf-name { font-size:16px; font-weight:600; color:#111827; margin-top:8px; }
        .vf-card { background:#fff; border:0.5px solid #e5e7eb; border-radius:16px; overflow:hidden; box-shadow:0 1px 3px rgba(0,0,0,.04); }
        .vf-head { padding:22px 24px; color:#fff; display:flex; align-items:center; gap:14px; }
        .vf-head--ok  { background:linear-gradient(135deg,#0F6E56 0%,#1D9E75 100%); }
        .vf-head--no  { background:linear-gradient(135deg,#993C1D 0%,#C2410C 100%); }
        .vf-head__icon { width:46px; height:46px; border-radius:12px; background:rgba(255,255,255,.2); display:flex; align-items:center; justify-content:center; flex:none; }
        .vf-head__title { font-size:18px; font-weight:600; margin:0; }
        .vf-head__sub { font-size:12.5px; opacity:.9; margin:2px 0 0; }
        .vf-sec { padding:20px 24px; border-bottom:0.5px solid #f3f4f6; }
        .vf-sec:last-child { border-bottom:none; }
        .vf-sec__label { font-size:10px; text-transform:uppercase; letter-spacing:.07em; color:#9ca3af; font-weight:600; margin:0 0 12px; }
        table.vf-kv { width:100%; border-collapse:collapse; }
        table.vf-kv td { padding:6px 0; vertical-align:top; font-size:13px; }
        table.vf-kv td.k { color:#6b7280; width:40%; }
        table.vf-kv td.v { color:#111827; font-weight:500; }
        .vf-hash { font-family: ui-monospace, 'DejaVu Sans Mono', monospace; font-size:11px; word-break:break-all; color:#374151; background:#f9fafb; border:0.5px solid #e5e7eb; border-radius:8px; padding:8px 10px; }
        .vf-input { width:100%; border:0.5px solid #e5e7eb; border-radius:9px; padding:10px 12px; font-size:14px; outline:none; background:#fff; }
        .vf-input:focus { border-color:#185FA5; box-shadow:0 0 0 3px rgba(24,95,165,.1); }
        .vf-btn { display:inline-flex; align-items:center; gap:6px; background:#185FA5; color:#fff; border:none; border-radius:9px; font-size:13.5px; font-weight:500; padding:10px 20px; cursor:pointer; }
        .vf-btn:hover { background:#0F4A84; }
        .vf-note { padding:11px 14px; border-radius:10px; font-size:13px; line-height:1.5; }
        .vf-note--ok { background:#E1F5EE; color:#0F6E56; border:0.5px solid #A7DFC9; }
        .vf-note--no { background:#FAECE7; color:#993C1D; border:0.5px solid #F5C4B3; }
        .vf-note--info { background:#E6F1FB; color:#0C447C; border:0.5px solid #B5D4F4; }
        .vf-muted { font-size:12px; color:#6b7280; line-height:1.6; }
        .vf-badge { display:inline-flex; align-items:center; font-size:11px; font-weight:600; padding:3px 10px; border-radius:99px; background:#E1F5EE; color:#0F6E56; }
        @media (prefers-color-scheme: dark) {
            body { background:#0b0f17; color:#e5e7eb; }
            .vf-card { background:#111827; border-color:#1f2937; }
            .vf-sec { border-color:#1f2937; }
            .vf-sec__label, table.vf-kv td.k, .vf-muted { color:#9ca3af; }
            table.vf-kv td.v { color:#f3f4f6; }
            .vf-hash { background:#0b0f17; border-color:#1f2937; color:#cbd5e1; }
            .vf-input { background:#0b0f17; border-color:#1f2937; color:#e5e7eb; }
        }
    </style>
</head>
<body>
    <div class="vf-wrap">
        <div class="vf-brand">
            @if (getSettingImage('app_logo'))
                <img src="{{ getSettingImage('app_logo') }}" alt="">
            @endif
            <div class="vf-name">{{ __('Agreement Verification') }}</div>
        </div>

        <div class="vf-card">
            @if ($agreement)
                <div class="vf-head vf-head--ok">
                    <span class="vf-head__icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                    </span>
                    <div>
                        <p class="vf-head__title">{{ __('Genuine signed agreement') }}</p>
                        <p class="vf-head__sub">{{ __('Reference') }} #{{ $agreement->id }} &middot; {{ __('recorded in our system') }}</p>
                    </div>
                </div>

                <div class="vf-sec">
                    <p class="vf-sec__label">{{ __('Details') }}</p>
                    <table class="vf-kv">
                        <tr><td class="k">{{ __('Agreement') }}</td><td class="v">{{ $agreement->title }} (Ref #{{ $agreement->id }})</td></tr>
                        <tr><td class="k">{{ __('Landlord') }}</td><td class="v">{{ optional($agreement->owner)->name ?? '—' }}</td></tr>
                        <tr><td class="k">{{ __('Signed by') }}</td><td class="v">{{ $agreement->signer_full_name }}</td></tr>
                        <tr><td class="k">{{ __('Signed on') }}</td><td class="v">{{ optional($agreement->signed_at)->format('d M Y, g:i A') }}</td></tr>
                        <tr><td class="k">{{ __('Method') }}</td><td class="v">{{ ucfirst($agreement->signature_method ?? 'typed') }} + SMS one-time passcode</td></tr>
                        <tr><td class="k">{{ __('Status') }}</td><td class="v"><span class="vf-badge">{{ __('Signed') }}</span></td></tr>
                    </table>
                </div>

                @if ($agreement->document_hash)
                    <div class="vf-sec">
                        <p class="vf-sec__label">{{ __('Document fingerprint (SHA-256)') }}</p>
                        <div class="vf-hash">{{ $agreement->document_hash }}</div>

                        @isset($check)
                            <div class="vf-note {{ $check['match'] ? 'vf-note--ok' : 'vf-note--no' }}" style="margin-top:14px;">
                                @if ($check['match'])
                                    @if (($check['kind'] ?? null) === 'certificate')
                                        ✓ {{ __('Match. This is the genuine signature certificate for this agreement, unaltered.') }}
                                    @else
                                        ✓ {{ __('Match. The document you uploaded is exactly this signed agreement, unaltered.') }}
                                    @endif
                                @else
                                    ✗ {{ __('No match. The document you uploaded does not match this agreement or its certificate (wrong file, or it has been altered).') }}
                                @endif
                                <div class="vf-hash" style="margin-top:8px;">{{ __('Your file:') }} {{ $check['uploadedHash'] }}</div>
                            </div>
                        @endisset

                        <form action="{{ route('agreement.verify.document', ['code' => $code]) }}" method="POST" enctype="multipart/form-data" style="margin-top:14px;">
                            @csrf
                            <p class="vf-muted" style="margin:0 0 8px;">{{ __('Have the agreement document or its certificate? Upload it and we\'ll confirm it matches — you don\'t need to compute anything.') }}</p>
                            <div style="display:flex; gap:10px; flex-wrap:wrap; align-items:center;">
                                <input type="file" name="document" accept="application/pdf" class="vf-input" style="flex:1; min-width:200px;" required>
                                <button type="submit" class="vf-btn">{{ __('Check document') }}</button>
                            </div>
                        </form>
                    </div>
                @endif

            @else
                <div class="vf-head" style="background:#fafafa; color:#111827; border-bottom:0.5px solid #e5e7eb;">
                    <span class="vf-head__icon" style="background:#E6F1FB; color:#185FA5;">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                    </span>
                    <div>
                        <p class="vf-head__title">{{ __('Verify an agreement') }}</p>
                        <p class="vf-head__sub">{{ __('Enter the verification code from the certificate') }}</p>
                    </div>
                </div>
                <div class="vf-sec">
                    @if (! empty($notFound))
                        <div class="vf-note vf-note--no" style="margin-bottom:14px;">
                            {{ __('No signed agreement matches that code. Check the code on the certificate and try again.') }}
                        </div>
                    @endif
                    @if (! empty($docNotFound))
                        <div class="vf-note vf-note--no" style="margin-bottom:14px;">
                            {{ __('That document does not match any signed agreement on record (wrong file, or it has been altered).') }}
                        </div>
                    @endif
                    <form action="{{ route('agreement.verify') }}" method="GET">
                        <p class="vf-sec__label">{{ __('Verification code') }}</p>
                        <div style="display:flex; gap:10px; flex-wrap:wrap;">
                            <input type="text" name="code" value="{{ $code }}" class="vf-input" style="flex:1; min-width:200px; letter-spacing:.08em; text-transform:uppercase;" placeholder="XXXXXXXXXXXXXXXX" required>
                            <button type="submit" class="vf-btn">{{ __('Verify') }}</button>
                        </div>
                    </form>
                </div>

                {{-- Or verify by the document itself (no code needed) --}}
                <div class="vf-sec">
                    <p class="vf-sec__label">{{ __('Or — verify by the document') }}</p>
                    <p class="vf-muted" style="margin:0 0 10px;">{{ __('Have the agreement document or its certificate but not the code? Upload it and we\'ll find and confirm it — you don\'t need to compute anything.') }}</p>
                    <form action="{{ route('agreement.verify.by-document') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div style="display:flex; gap:10px; flex-wrap:wrap; align-items:center;">
                            <input type="file" name="document" accept="application/pdf" class="vf-input" style="flex:1; min-width:200px;" required>
                            <button type="submit" class="vf-btn">{{ __('Check document') }}</button>
                        </div>
                    </form>
                </div>
            @endif
        </div>

        <p class="vf-muted" style="text-align:center; margin-top:16px;">
            {{ __('This page confirms an agreement was electronically signed and recorded. The verification code is on the signature certificate.') }}
        </p>
    </div>
</body>
</html>
