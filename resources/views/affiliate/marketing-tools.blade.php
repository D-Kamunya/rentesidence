@extends('affiliate.layouts.app')

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="page-content-wrapper bg-white p-30 radius-20">
                <div class="container">

                    {{-- Page Header --}}
                    @php
                        $pageTitle = 'Marketing Tools';
                    @endphp
                    <div class="dash-header mb-4">
                        <div>
                            <h2 class="dash-title">{{ __('Marketing Tools') }}</h2>
                            <p class="dash-subtitle">Share your referral link and grow your network</p>
                        </div>
                    </div>

                    {{-- Referral Link Section --}}
                    <div class="mk-card mb-4">
                        <div class="mk-card__head">
                            <div class="mk-card__head-icon mk-card__head-icon--blue">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none">
                                    <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </div>
                            <span>{{ __('Your Referral Link') }}</span>
                        </div>
                        <div class="mk-card__body">

                            {{-- Link input — stacks vertically on mobile --}}
                            <div class="mk-copy-stack mb-4">
                                <input type="text" id="refLink" class="mk-copy-input" value="{{ $referralUrl }}" readonly>
                                <button class="mk-copy-btn" onclick="copyText('refLink', this)">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none">
                                        <rect x="9" y="9" width="13" height="13" rx="2" stroke="currentColor" stroke-width="2"/>
                                        <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                    </svg>
                                    <span>Copy Link</span>
                                </button>
                            </div>

                            {{-- Stats: side by side on md+, conversions drops full-width on mobile --}}
                            <div class="row g-3 mb-4">
                                <div class="col-6 col-md-4">
                                    <div class="mk-stat">
                                        <p class="mk-stat__val mk-stat__val--blue">{{ $stats['clicks'] ?? 0 }}</p>
                                        <p class="mk-stat__label">Clicks</p>
                                    </div>
                                </div>
                                <div class="col-6 col-md-4">
                                    <div class="mk-stat">
                                        <p class="mk-stat__val mk-stat__val--green">{{ $stats['signups'] ?? 0 }}</p>
                                        <p class="mk-stat__label">Signups</p>
                                    </div>
                                </div>
                                <div class="col-12 col-md-4">
                                    <div class="mk-stat mk-stat--wide">
                                        <p class="mk-stat__val mk-stat__val--purple">{{ $stats['conversions'] ?? 0 }}</p>
                                        <p class="mk-stat__label">Conversions</p>
                                    </div>
                                </div>
                            </div>

                            {{-- QR code + Social share --}}
                            <div class="row g-4">
                                <div class="col-12 col-md-5">
                                    <p class="mk-section-label">{{ __('QR Code') }}</p>
                                    <div class="mk-qr-wrap">
                                        {!! $qrImage !!}
                                    </div>
                                </div>
                                <div class="col-12 col-md-7">
                                    <p class="mk-section-label">{{ __('Share on Social Media') }}</p>
                                    <div class="mk-share-wrap">
                                        {!! $shareButtons !!}
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    {{-- Banners --}}
                    <div class="mk-card mb-4">
                        <div class="mk-card__head">
                            <div class="mk-card__head-icon mk-card__head-icon--amber">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none">
                                    <rect x="3" y="3" width="18" height="18" rx="2" stroke="currentColor" stroke-width="2"/>
                                    <circle cx="8.5" cy="8.5" r="1.5" fill="currentColor"/>
                                    <path d="M21 15l-5-5L5 21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </div>
                            <span>{{ __('Banner Images & Embed Code') }}</span>
                        </div>
                        <div class="mk-card__body">
                            <p class="mb-3" style="font-size:14px;color:#6b7280;">{{ __('Use these banners on your website or blog:') }}</p>
                            <div class="row g-4">
                                @foreach($banners as $banner)
                                    <div class="col-12 col-md-6">
                                        <a href="{{ $banner['image_url'] }}" target="_blank">
                                            <img src="{{ $banner['image_url'] }}" class="mk-banner-img" alt="Banner">
                                        </a>
                                        <div class="mk-copy-stack mk-copy-stack--row mt-2">
                                            <textarea id="banner-{{ $loop->index }}" class="mk-copy-input mk-copy-textarea" rows="2" readonly>{{ $banner['embed_code'] }}</textarea>
                                            <button class="mk-copy-btn mk-copy-btn--icon" onclick="copyText('banner-{{ $loop->index }}', this)" title="Copy embed code">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none">
                                                    <rect x="9" y="9" width="13" height="13" rx="2" stroke="currentColor" stroke-width="2"/>
                                                    <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- Templates --}}
                    <div class="mk-card mb-4">
                        <div class="mk-card__head">
                            <div class="mk-card__head-icon mk-card__head-icon--green">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none">
                                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" stroke="currentColor" stroke-width="2"/>
                                    <polyline points="22,6 12,13 2,6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                </svg>
                            </div>
                            <span>{{ __('Email & WhatsApp Templates') }}</span>
                        </div>
                        <div class="mk-card__body">

                            <div class="mk-template-block mb-4">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <span class="mk-tmpl-badge mk-tmpl-badge--blue">Email</span>
                                </div>
                                <div class="mk-copy-stack mk-copy-stack--row">
                                    <textarea id="emailTemplate" class="mk-copy-input mk-copy-textarea" rows="4" readonly>{{ $emailTemplate }}</textarea>
                                    <button class="mk-copy-btn mk-copy-btn--icon" onclick="copyText('emailTemplate', this)" title="Copy">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none">
                                            <rect x="9" y="9" width="13" height="13" rx="2" stroke="currentColor" stroke-width="2"/>
                                            <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <div class="mk-template-block">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <span class="mk-tmpl-badge mk-tmpl-badge--green">WhatsApp</span>
                                </div>
                                <div class="mk-copy-stack mk-copy-stack--row">
                                    <textarea id="whatsappTemplate" class="mk-copy-input mk-copy-textarea" rows="3" readonly>{{ $whatsappTemplate }}</textarea>
                                    <button class="mk-copy-btn mk-copy-btn--icon" onclick="copyText('whatsappTemplate', this)" title="Copy">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none">
                                            <rect x="9" y="9" width="13" height="13" rx="2" stroke="currentColor" stroke-width="2"/>
                                            <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>

                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* ── Page header ─────────────────────────────────────── */
    .dash-header   { display:flex; align-items:flex-start; justify-content:space-between; flex-wrap:wrap; gap:1rem; }
    .dash-title    { font-size:22px; font-weight:500; color:#111827; margin:0 0 4px; }
    .dash-subtitle { font-size:14px; color:#6b7280; margin:0; }

    /* ── Section card ────────────────────────────────────── */
    .mk-card {
        background: #fff;
        border: 0.5px solid #e5e7eb;
        border-radius: 14px;
        overflow: hidden;
    }
    .mk-card__head {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: .9rem 1.25rem;
        border-bottom: 0.5px solid #e5e7eb;
        background: #fafafa;
        font-size: 14px;
        font-weight: 500;
        color: #111827;
    }
    .mk-card__head-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 30px;
        height: 30px;
        border-radius: 7px;
        flex-shrink: 0;
    }
    .mk-card__head-icon--blue  { background:#E6F1FB; color:#185FA5; }
    .mk-card__head-icon--amber { background:#FAEEDA; color:#854F0B; }
    .mk-card__head-icon--green { background:#E1F5EE; color:#0F6E56; }
    .mk-card__body { padding:1.25rem; }

    /* ── Copy stack ──────────────────────────────────────── */
    .mk-copy-stack {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
    @media (min-width: 576px) {
        .mk-copy-stack { flex-direction: row; align-items: stretch; }
    }
    .mk-copy-stack--row {
        flex-direction: row;
        align-items: stretch;
    }

    /* ── Shared input/textarea style ─────────────────────── */
    .mk-copy-input {
        flex: 1;
        min-width: 0;
        width: 100%;
        padding: 9px 12px;
        font-size: 13px;
        color: #374151;
        background: #fafafa;
        border: 0.5px solid #d1d5db;
        border-radius: 8px;
        outline: none;
        font-family: var(--font-mono, monospace);
        resize: none;
        transition: border-color .15s;
    }
    .mk-copy-input:focus { border-color: #185FA5; }
    .mk-copy-textarea    { resize: vertical; }

    /* ── Copy buttons ────────────────────────────────────── */
    .mk-copy-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        padding: 11px 20px;
        background: #185FA5;
        color: #fff;
        font-size: 13px;
        font-weight: 500;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        white-space: nowrap;
        flex-shrink: 0;
        width: 100%;
        transition: background .2s, transform .15s;
    }
    @media (min-width: 576px) {
        .mk-copy-btn { width: auto; }
    }
    .mk-copy-btn:hover  { background: #0C447C; transform: translateY(-1px); }
    .mk-copy-btn.copied { background: #1D9E75; }

    .mk-copy-btn--icon {
        width: 40px;
        padding: 0;
        background: #f3f4f6;
        color: #6b7280;
        border: 0.5px solid #d1d5db;
    }
    .mk-copy-btn--icon:hover  { background: #e5e7eb; color: #111827; transform: none; }
    .mk-copy-btn--icon.copied { background: #E1F5EE; color: #0F6E56; border-color: #9FE1CB; }

    /* ── Mini stats ──────────────────────────────────────── */
    .mk-stat {
        background: #fafafa;
        border: 0.5px solid #e5e7eb;
        border-radius: 10px;
        padding: .85rem .75rem;
        text-align: center;
        height: 100%;
    }
    .mk-stat__val   { font-size:22px; font-weight:500; margin:0 0 3px; line-height:1; }
    .mk-stat__label { font-size:11px; font-weight:500; text-transform:uppercase; letter-spacing:.06em; color:#9ca3af; margin:0; }
    .mk-stat__val--blue   { color: #185FA5; }
    .mk-stat__val--green  { color: #0F6E56; }
    .mk-stat__val--purple { color: #534AB7; }

    /* Conversions — horizontal on mobile, normal on md+ */
    .mk-stat--wide {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-direction: row-reverse;
        text-align: left;
        padding: .85rem 1.25rem;
        gap: 12px;
    }
    .mk-stat--wide .mk-stat__val   { font-size: 22px; margin: 0; }
    .mk-stat--wide .mk-stat__label { margin: 0; }

    @media (min-width: 768px) {
        .mk-stat--wide {
            flex-direction: column;
            justify-content: center;
            text-align: center;
            padding: .85rem .75rem;
        }
        .mk-stat--wide .mk-stat__val   { margin: 0 0 3px; }
        .mk-stat--wide .mk-stat__label { margin: 0; }
    }

    /* ── QR wrap ─────────────────────────────────────────── */
    .mk-qr-wrap {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 14px;
        background: #fff;
        border: 0.5px solid #e5e7eb;
        border-radius: 10px;
    }
    .mk-qr-wrap img, .mk-qr-wrap svg { max-width: 160px; display: block; }

    /* ── Social share ────────────────────────────────────── */
    .mk-share-wrap { display: flex; flex-wrap: wrap; gap: 8px; }

    /* ── Section label ───────────────────────────────────── */
    .mk-section-label {
        font-size: 12px;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: .06em;
        color: #9ca3af;
        margin-bottom: 10px;
    }

    /* ── Banner ──────────────────────────────────────────── */
    .mk-banner-img {
        width: 100%;
        border-radius: 8px;
        border: 0.5px solid #e5e7eb;
        display: block;
        margin-bottom: 8px;
        transition: opacity .15s;
    }
    .mk-banner-img:hover { opacity: .9; }

    /* ── Template badges ─────────────────────────────────── */
    .mk-tmpl-badge {
        display: inline-flex;
        align-items: center;
        font-size: 11px;
        font-weight: 500;
        padding: 3px 10px;
        border-radius: 99px;
    }
    .mk-tmpl-badge--blue  { background: #E6F1FB; color: #185FA5; }
    .mk-tmpl-badge--green { background: #E1F5EE; color: #0F6E56; }
</style>

@push('scripts')
<script>
function copyText(elementId, btn) {
    const el = document.getElementById(elementId);
    el.select();
    el.setSelectionRange(0, 99999);
    try {
        navigator.clipboard.writeText(el.value).catch(() => document.execCommand('copy'));
    } catch {
        document.execCommand('copy');
    }
    if (btn) {
        btn.classList.add('copied');
        const span = btn.querySelector('span');
        if (span) {
            const orig = span.textContent;
            span.textContent = 'Copied!';
            setTimeout(() => { span.textContent = orig; btn.classList.remove('copied'); }, 2000);
        } else {
            setTimeout(() => btn.classList.remove('copied'), 2000);
        }
    }
}
</script>
@endpush

@push('style')
    <link rel="stylesheet" href="{{ asset('assets/css/affiliate.css') }}">
@endpush