@extends('tenant.layouts.app')

@section('content')
@php $signed = $agreement->status === 'signed'; $declined = $agreement->status === 'declined'; @endphp
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="page-content-wrapper bg-white p-30 radius-20">
                <div class="container" style="max-width:820px;">

                    <div class="ag-header mb-4">
                        <div>
                            <h2 class="ag-title">{{ $agreement->title }}</h2>
                            <p class="ag-sub">{{ __('From') }} {{ optional($agreement->owner)->name }} &middot;
                                @include('agreement.partials.status-badge', ['status' => $agreement->status])</p>
                        </div>
                        <a href="{{ route('tenant.agreement.index') }}" class="ag-btn ag-btn--ghost">{{ __('Back') }}</a>
                    </div>

                    {{-- Agreement document --}}
                    <div class="ag-doc">
                        @if ($agreement->source === 'upload')
                            {{-- Owner-uploaded PDF — served through a scoped route (auth + tenant match). --}}
                            <iframe class="ag-doc__frame" src="{{ route('tenant.agreement.document', $agreement->id) }}#toolbar=1"></iframe>
                        @else
                            {{-- Template HTML — sandboxed so owner-authored HTML can't run script here. --}}
                            <iframe class="ag-doc__frame" sandbox
                                srcdoc="{{ '<!doctype html><meta charset=utf-8><style>body{font-family:system-ui,Segoe UI,Roboto,sans-serif;color:#1f2937;font-size:14px;line-height:1.6;padding:22px;margin:0}h2{font-size:18px}h3{font-size:15px}</style>' . $agreement->body }}"></iframe>
                        @endif
                    </div>

                    @if ($signed)
                        <div class="ag-note ag-note--green mt-4">
                            <strong>{{ __('You signed this agreement') }}</strong>
                            {{ optional($agreement->signed_at)->format('d M Y, g:i A') }}.
                            @if ($agreement->source === 'upload')
                                <a href="{{ route('tenant.agreement.document', $agreement->id) }}" target="_blank" class="ag-link">{{ __('Agreement') }}</a>
                                @if ($agreement->signed_file_id)
                                    &middot; <a href="{{ route('tenant.agreement.download', $agreement->id) }}" class="ag-link">{{ __('Certificate') }}</a>
                                @endif
                            @elseif ($agreement->signed_file_id)
                                <a href="{{ route('tenant.agreement.download', $agreement->id) }}" class="ag-link">{{ __('Download your copy') }}</a>
                            @endif
                        </div>
                    @elseif ($declined)
                        <div class="ag-note ag-note--coral mt-4">{{ __('You declined this agreement.') }}</div>
                    @else
                        {{-- ── Signing form ── --}}
                        <form action="{{ route('tenant.agreement.sign', $agreement->id) }}" method="POST" id="signForm" class="ag-sign mt-4">
                            @csrf
                            <input type="hidden" name="signature_method" id="signature_method" value="typed">
                            <input type="hidden" name="signature_data" id="signature_data">

                            <h3 class="ag-sign__h">{{ __('Sign this agreement') }}</h3>

                            {{-- Name --}}
                            <div class="mb-3">
                                <label class="ag-label">{{ __('Your full name') }}</label>
                                <input type="text" name="signer_full_name" id="signer_full_name" class="ag-input"
                                       value="{{ auth()->user()->name }}" required autocomplete="name">
                            </div>

                            {{-- Signature --}}
                            <div class="mb-3">
                                <div class="ag-sig-tabs">
                                    <button type="button" class="ag-sig-tab is-active" data-mode="typed">{{ __('Type') }}</button>
                                    <button type="button" class="ag-sig-tab" data-mode="drawn">{{ __('Draw') }}</button>
                                </div>
                                <div id="sigTyped" class="ag-sig-preview" aria-hidden="false">{{ auth()->user()->name }}</div>
                                <div id="sigDrawn" class="ag-sig-canvas-wrap" style="display:none;">
                                    <canvas id="sigCanvas" class="ag-sig-canvas" width="600" height="150"></canvas>
                                    <button type="button" id="sigClear" class="ag-link" style="margin-top:6px;">{{ __('Clear') }}</button>
                                </div>
                            </div>

                            {{-- OTP --}}
                            <div class="mb-3">
                                <label class="ag-label">{{ __('Signing code (sent to your phone)') }}</label>
                                <div class="d-flex gap-2 flex-wrap align-items-center">
                                    <input type="text" name="otp" id="otp" class="ag-input" style="max-width:160px;letter-spacing:.3em;"
                                           inputmode="numeric" maxlength="6" placeholder="••••••" autocomplete="one-time-code">
                                    <button type="button" id="sendOtpBtn" class="ag-btn ag-btn--ghost">{{ __('Send code') }}</button>
                                    <span id="otpMsg" class="ag-hint" style="margin:0;"></span>
                                </div>
                            </div>

                            {{-- Consent --}}
                            <label class="ag-consent">
                                <input type="checkbox" name="consent" value="1" required>
                                <span>{{ __('I have read this agreement and I agree to sign it electronically. I understand this signature is legally binding.') }}</span>
                            </label>

                            <div class="mt-3">
                                <button type="submit" class="ag-btn ag-btn--primary">{{ __('Sign Agreement') }}</button>
                            </div>
                        </form>

                        {{-- Decline --}}
                        <div class="ag-decline mt-3">
                            <button type="button" class="ag-decline__toggle" id="declineToggle">{{ __('Not ready to sign? Decline this agreement') }}</button>
                            <form action="{{ route('tenant.agreement.decline', $agreement->id) }}" method="POST" id="declineForm" style="display:none; margin-top:12px;">
                                @csrf
                                <label class="ag-label">{{ __('Reason (optional)') }}</label>
                                <textarea name="reason" rows="2" class="ag-input" style="max-width:100%;" placeholder="{{ __('Let your landlord know why…') }}"></textarea>
                                <div class="mt-2 d-flex gap-2">
                                    <button type="button" class="ag-btn ag-btn--ghost" id="declineCancel">{{ __('Cancel') }}</button>
                                    <button type="submit" class="ag-btn" style="background:#993C1D;color:#fff;">{{ __('Decline Agreement') }}</button>
                                </div>
                            </form>
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
</div>

{{-- Inline (works across layouts) --}}
<script>
(function () {
    var otpUrl = "{{ route('tenant.agreement.otp', $agreement->id) }}";
    var csrf   = "{{ csrf_token() }}";

    // ── Signature tabs ──
    var methodEl = document.getElementById('signature_method');
    var typedEl  = document.getElementById('sigTyped');
    var drawnEl  = document.getElementById('sigDrawn');
    var nameEl   = document.getElementById('signer_full_name');
    if (nameEl && typedEl) {
        nameEl.addEventListener('input', function () { typedEl.textContent = nameEl.value || ''; });
    }
    document.querySelectorAll('.ag-sig-tab').forEach(function (tab) {
        tab.addEventListener('click', function () {
            document.querySelectorAll('.ag-sig-tab').forEach(function (t) { t.classList.remove('is-active'); });
            tab.classList.add('is-active');
            var mode = tab.getAttribute('data-mode');
            methodEl.value = mode;
            typedEl.style.display = mode === 'typed' ? '' : 'none';
            drawnEl.style.display = mode === 'drawn' ? '' : 'none';
        });
    });

    // ── Canvas signature ──
    var canvas = document.getElementById('sigCanvas');
    var drawn = false;
    if (canvas) {
        var ctx = canvas.getContext('2d');
        ctx.lineWidth = 2; ctx.lineCap = 'round'; ctx.strokeStyle = '#111827';
        var drawing = false, last = null;
        function pos(e) {
            var r = canvas.getBoundingClientRect();
            var p = e.touches ? e.touches[0] : e;
            return { x: (p.clientX - r.left) * (canvas.width / r.width), y: (p.clientY - r.top) * (canvas.height / r.height) };
        }
        function start(e) { drawing = true; last = pos(e); e.preventDefault(); }
        function move(e) { if (!drawing) return; var p = pos(e); ctx.beginPath(); ctx.moveTo(last.x, last.y); ctx.lineTo(p.x, p.y); ctx.stroke(); last = p; drawn = true; e.preventDefault(); }
        function end() { drawing = false; }
        canvas.addEventListener('mousedown', start); canvas.addEventListener('mousemove', move); document.addEventListener('mouseup', end);
        canvas.addEventListener('touchstart', start, {passive:false}); canvas.addEventListener('touchmove', move, {passive:false}); canvas.addEventListener('touchend', end);
        var clr = document.getElementById('sigClear');
        if (clr) clr.addEventListener('click', function () { ctx.clearRect(0, 0, canvas.width, canvas.height); drawn = false; });
    }

    // ── Request OTP ──
    var sendBtn = document.getElementById('sendOtpBtn');
    var otpMsg  = document.getElementById('otpMsg');
    if (sendBtn) {
        sendBtn.addEventListener('click', function () {
            sendBtn.disabled = true; otpMsg.textContent = "{{ __('Sending…') }}";
            fetch(otpUrl, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' } })
                .then(function (r) { return r.json(); })
                .then(function (d) {
                    otpMsg.textContent = (d && d.message) ? d.message : "{{ __('Code sent.') }}";
                    // DEV convenience: auto-fill the field when the server returns the code.
                    if (d && d.data && d.data.dev_otp) {
                        var otpInput = document.getElementById('otp');
                        if (otpInput) otpInput.value = d.data.dev_otp;
                    }
                })
                .catch(function () { otpMsg.textContent = "{{ __('Could not send the code. Try again.') }}"; })
                .finally(function () { setTimeout(function () { sendBtn.disabled = false; }, 8000); });
        });
    }

    // ── Decline toggle ──
    var dToggle = document.getElementById('declineToggle');
    var dForm   = document.getElementById('declineForm');
    var dCancel = document.getElementById('declineCancel');
    if (dToggle && dForm) {
        dToggle.addEventListener('click', function () { dForm.style.display = ''; dToggle.style.display = 'none'; });
        if (dCancel) dCancel.addEventListener('click', function () { dForm.style.display = 'none'; dToggle.style.display = ''; });
    }

    // ── On submit, capture the drawn signature ──
    var form = document.getElementById('signForm');
    if (form) {
        form.addEventListener('submit', function (e) {
            if (methodEl.value === 'drawn') {
                if (!drawn) { e.preventDefault(); (window.toastr ? toastr.error("{{ __('Please draw your signature, or switch to Type.') }}") : csAlert("{{ __('Please draw your signature, or switch to Type.') }}")); return; }
                document.getElementById('signature_data').value = canvas.toDataURL('image/png');
            }
        });
    }
})();
</script>
@endsection

@push('style')
    @include('agreement.partials.styles')
    <style>
        .ag-doc { border:0.5px solid #e5e7eb; border-radius:12px; overflow:hidden; background:#fff; }
        .ag-doc__frame { width:100%; height:420px; border:none; display:block; background:#fff; }
        .ag-note { padding:12px 16px; border-radius:10px; font-size:13px; }
        .ag-note--green { background:#E1F5EE; color:#0F6E56; border:0.5px solid #A7DFC9; }
        .ag-note--coral { background:#FAECE7; color:#993C1D; border:0.5px solid #F5C4B3; }
        .ag-sign { border:0.5px solid #e5e7eb; border-radius:12px; padding:20px; background:#fafafa; }
        .ag-sign__h { font-size:15px; font-weight:600; color:#111827; margin:0 0 16px; }
        .ag-input { width:100%; max-width:420px; border:0.5px solid #e5e7eb; border-radius:9px; padding:10px 13px; font-size:14px; outline:none; background:#fff; }
        .ag-input:focus { border-color:#185FA5; box-shadow:0 0 0 3px rgba(24,95,165,.1); }
        .ag-sig-tabs { display:inline-flex; background:#eef1f4; border-radius:8px; padding:3px; margin-bottom:10px; }
        .ag-sig-tab { background:transparent; border:none; font-size:12px; font-weight:500; color:#6b7280; padding:5px 14px; border-radius:6px; cursor:pointer; }
        .ag-sig-tab.is-active { background:#fff; color:#111827; box-shadow:0 1px 3px rgba(0,0,0,.08); }
        .ag-sig-preview { min-height:56px; display:flex; align-items:center; padding:6px 14px; background:#fff; border:0.5px solid #e5e7eb; border-radius:9px;
            font-family:'Brush Script MT','Segoe Script',cursive; font-size:30px; color:#111827; max-width:420px; }
        .ag-sig-canvas { width:100%; max-width:420px; height:150px; background:#fff; border:0.5px dashed #cbd5e1; border-radius:9px; touch-action:none; cursor:crosshair; }
        .ag-consent { display:flex; align-items:flex-start; gap:10px; font-size:12.5px; color:#374151; line-height:1.5; }
        .ag-consent input { margin-top:3px; width:16px; height:16px; accent-color:#185FA5; flex:none; }
        .ag-decline { text-align:center; }
        .ag-decline__toggle { background:none; border:none; color:#9ca3af; font-size:12.5px; cursor:pointer; text-decoration:underline; }
        .ag-decline__toggle:hover { color:#993C1D; }
        .ag-decline form { text-align:left; max-width:520px; margin:12px auto 0; }
    </style>
@endpush
