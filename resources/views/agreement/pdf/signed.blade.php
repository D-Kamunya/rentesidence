@php
    $ctx = (array) ($agreement->template_data ?? []);
    $ownerName = $ctx['owner_name'] ?? optional($agreement->owner)->name ?? '—';
@endphp
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        * { font-family: DejaVu Sans, sans-serif; }
        body { color: #1f2937; font-size: 12px; line-height: 1.55; }
        h2 { font-size: 18px; margin: 0 0 4px; }
        h3 { font-size: 14px; margin: 0 0 8px; }
        .muted { color: #6b7280; }
        .agreement-body { margin-bottom: 24px; }
        .sig-block { margin-top: 18px; padding-top: 14px; border-top: 1px solid #e5e7eb; }
        .sig-name-typed { font-family: 'Times New Roman', serif; font-style: italic; font-size: 26px; color: #111827; }
        .sig-img { height: 70px; }
        .sig-meta { font-size: 11px; color: #6b7280; margin-top: 4px; }
        .page-break { page-break-before: always; }
        .cert-card { border: 1px solid #e5e7eb; border-radius: 6px; padding: 14px 16px; }
        table.kv { width: 100%; border-collapse: collapse; }
        table.kv td { padding: 4px 0; vertical-align: top; font-size: 11.5px; }
        table.kv td.k { color: #6b7280; width: 38%; }
        table.audit { width: 100%; border-collapse: collapse; margin-top: 6px; }
        table.audit th, table.audit td { text-align: left; font-size: 10.5px; padding: 4px 6px; border-bottom: 0.5px solid #eee; }
        table.audit th { color: #6b7280; text-transform: uppercase; letter-spacing: .04em; font-size: 9px; }
        .badge { display: inline-block; background: #E1F5EE; color: #0F6E56; padding: 2px 8px; border-radius: 99px; font-size: 10px; }
    </style>
</head>
<body>

    {{-- ── Agreement terms (frozen, autofilled) — template source only ── --}}
    @if ($agreement->body)
        <div class="agreement-body">
            {!! $agreement->body !!}
        </div>
    @else
        {{-- Upload source: the terms live in the owner's separate PDF; this is its certificate. --}}
        <h2>Certificate of Electronic Signature</h2>
        <p class="muted" style="margin:0 0 6px;">
            This certificate accompanies the separately-provided agreement document
            <strong>{{ $agreement->title }}</strong> (Ref #{{ $agreement->id }}) and records how it was signed.
        </p>
    @endif

    {{-- ── Signature block ── --}}
    <div class="sig-block">
        <p class="muted" style="margin:0 0 6px;">Tenant signature:</p>
        @if ($agreement->signature_method === 'drawn' && $agreement->signature_data)
            <img src="{{ $agreement->signature_data }}" class="sig-img" alt="signature">
        @else
            <div class="sig-name-typed">{{ $agreement->signer_full_name }}</div>
        @endif
        <p class="sig-meta">
            {{ $agreement->signer_full_name }} &middot;
            signed {{ optional($agreement->signed_at)->format('d M Y, g:i A') }}
        </p>
    </div>

    {{-- ── Certificate of electronic signature ── --}}
    {{-- Template source breaks to a fresh page after the terms; upload source has no terms
         page, so it stays inline (the heading was already shown above). --}}
    @if ($agreement->body)
        <div class="page-break"></div>
        <h2>Certificate of Electronic Signature</h2>
        <p class="muted" style="margin:0 0 14px;">
            This certificate records how the attached agreement was signed electronically.
        </p>
    @endif

    <div class="cert-card">
        <table class="kv">
            <tr><td class="k">Agreement</td><td>{{ $agreement->title }} (Ref #{{ $agreement->id }})</td></tr>
            <tr><td class="k">Landlord</td><td>{{ $ownerName }}</td></tr>
            <tr><td class="k">Tenant (signer)</td><td>{{ $agreement->signer_full_name }}</td></tr>
            <tr><td class="k">Signature method</td><td>{{ ucfirst($agreement->signature_method ?? 'typed') }} + SMS one-time passcode</td></tr>
            <tr><td class="k">Identity verification</td><td>SMS OTP verified {{ optional($agreement->otp_verified_at)->format('d M Y, g:i A') }}</td></tr>
            <tr><td class="k">Signed at</td><td>{{ optional($agreement->signed_at)->format('d M Y, g:i A') }}</td></tr>
            <tr><td class="k">Signer IP</td><td>{{ $agreement->signed_ip ?: '—' }}</td></tr>
            <tr><td class="k">Status</td><td><span class="badge">Signed</span></td></tr>
        </table>
    </div>

    @if ($agreement->verification_code)
        {{-- Anyone can confirm this certificate is genuine online (no tools needed). --}}
        <div class="cert-card" style="margin-top:12px;">
            <table class="kv">
                <tr><td class="k">Verify online</td><td>{{ rtrim(config('app.url'), '/') }}/agreement/verify/{{ $agreement->verification_code }}</td></tr>
                <tr><td class="k">Reference</td><td>#{{ $agreement->id }}</td></tr>
                <tr><td class="k">Verification code</td><td style="font-family:'DejaVu Sans Mono',monospace;">{{ $agreement->verification_code }}</td></tr>
            </table>
            <p class="muted" style="margin:8px 0 0;font-size:10.5px;">
                Visit the link above (or go to the verification page and enter the code) to confirm this
                agreement was signed and recorded — and to check a copy of the document against its fingerprint.
            </p>
        </div>
    @endif

    @if (! $agreement->body && $agreement->document_hash)
        {{-- Upload source: print the original's hash so this certificate can be matched to
             (and verify the integrity of) the separate agreement document. --}}
        <div class="cert-card" style="margin-top:14px;">
            <p class="muted" style="margin:0 0 4px;font-size:10px;text-transform:uppercase;letter-spacing:.06em;">Agreement document — SHA-256</p>
            <p style="margin:0;font-family:'DejaVu Sans Mono',monospace;font-size:10px;word-break:break-all;color:#111827;">{{ $agreement->document_hash }}</p>
            <p class="muted" style="margin:8px 0 0;font-size:10.5px;">
                To verify: compute the SHA-256 of the agreement document you were given and confirm it
                matches the value above. A match proves this certificate belongs to that exact document and
                that it has not been altered.
            </p>
        </div>
    @else
        <p class="muted" style="margin:14px 0 4px;font-size:11px;">
            Integrity: a SHA-256 hash of this signed document is recorded in the Centresidence audit log at
            the time of signing; any later alteration of the file will not match that record.
        </p>
    @endif

    <h3 style="margin-top:16px;">Audit trail</h3>
    <table class="audit">
        <thead>
            <tr><th>Event</th><th>When</th><th>IP</th></tr>
        </thead>
        <tbody>
            @foreach ($agreement->events as $ev)
                <tr>
                    <td>{{ ucfirst(str_replace('_', ' ', $ev->event)) }}</td>
                    <td>{{ optional($ev->created_at)->format('d M Y, g:i A') }}</td>
                    <td>{{ $ev->ip_address ?: '—' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

</body>
</html>
