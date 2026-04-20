@extends('affiliate.layouts.app')

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="page-content-wrapper bg-white p-30 radius-20">

                {{-- Page Title --}}
                @php
                    $pageTitle = 'Call';
                @endphp
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between border-bottom mb-4 pb-2">
                            <div class="page-title-left">
                                <h3 class="mb-sm-0">Call Script</h3>
                            </div>
                            <div class="page-title-right">
                                <ol class="breadcrumb mb-0">
                                    <li class="breadcrumb-item"><a href="{{ route('affiliate.dashboard') }}">Dashboard</a></li>
                                    <li class="breadcrumb-item"><a href="{{ route('affiliate.leads') }}">Leads</a></li>
                                    <li class="breadcrumb-item"><a href="{{ route('affiliate.leads.show', $lead->id) }}">{{ $lead->company->company_name }}</a></li>
                                    <li class="breadcrumb-item active">Call Script</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <a href="{{ route('affiliate.leads.show', $lead->id) }}" class="cs-back-link mb-4 d-inline-flex align-items-center gap-2">
                    <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
                        <path d="M10 3L5 8l5 5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Back to Lead
                </a>
                @if(session('success'))
                    <div class="mod-alert mod-alert--success mb-4">
                        <svg width="15" height="15" viewBox="0 0 16 16" fill="none" style="flex-shrink:0;">
                            <circle cx="8" cy="8" r="7" stroke="currentColor" stroke-width="1.5"/>
                            <path d="M4.5 8.5l2.5 2.5 4.5-5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <div>{{ session('success') }}</div>
                    </div>
                @endif

                <div class="row g-4">

                    {{-- Left: script --}}
                    <div class="col-lg-8">

                        {{-- Call target card --}}
                        <div class="cs-target-card mb-4">
                            <div class="cs-target-left">
                                <div class="cs-company-avatar">
                                    {{ strtoupper(substr($lead->company->company_name, 0, 2)) }}
                                </div>
                                <div>
                                    <h5 class="cs-company-name">{{ $lead->company->company_name }}</h5>
                                    <p class="cs-contact-name">
                                        {{ $lead->contact_person_name ?: 'No contact name on file' }}
                                        @if($lead->contact_person_role)
                                            <span class="cs-role">· {{ ucfirst(str_replace('_', ' ', $lead->contact_person_role)) }}</span>
                                        @endif
                                    </p>
                                    <div class="d-flex align-items-center gap-2 mt-2 flex-wrap">
                                        <span class="cs-temp-badge cs-temp-badge--{{ strtolower($lead->temperature) }}">
                                            {{ ucfirst($lead->temperature) }}
                                        </span>
                                        <span class="cs-status-badge cs-status-badge--{{ $lead->status }}">
                                            {{ ucfirst(str_replace('_', ' ', $lead->status)) }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            {{-- Dial button --}}
                            <a href="{{ route('affiliate.action.call', $lead->id) }}" class="cs-dial-btn">
                                <svg width="18" height="18" viewBox="0 0 16 16" fill="none">
                                    <path d="M3.5 1h3l1.5 3.5-2 1.5a9 9 0 0 0 4 4l1.5-2L15 9.5v3a2 2 0 0 1-2 2A12 12 0 0 1 1 3a2 2 0 0 1 2-2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                <div>
                                    <div style="font-size:13px;font-weight:500;">Call Now</div>
                                    <div style="font-size:11px;opacity:.8;">{{ $lead->company->phone ?? 'No number on file' }}</div>
                                </div>
                            </a>
                        </div>

                        {{-- Script card --}}
                        @if($script)
                            <div class="cs-card mb-4">
                                <div class="cs-card__head">
                                    <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
                                        <path d="M2 2h12v10H9l-3 2v-2H2V2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    Call Script
                                    <span class="cs-card__hint">Read through before dialling</span>
                                    <button type="button" class="cs-copy-btn" onclick="copyScript()" id="copyBtn">
                                        <svg width="12" height="12" viewBox="0 0 16 16" fill="none">
                                            <rect x="5" y="5" width="9" height="9" rx="1.5" stroke="currentColor" stroke-width="1.5"/>
                                            <path d="M11 5V3a1 1 0 0 0-1-1H3a1 1 0 0 0-1 1v7a1 1 0 0 0 1 1h2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                        </svg>
                                        Copy
                                    </button>
                                </div>
                                <div class="cs-card__body">
                                    <div class="cs-script-body" id="scriptContent">{{ $script }}</div>
                                </div>
                            </div>

                            {{-- Log outcome --}}
                            <div class="cs-card">
                                <div class="cs-card__head">
                                    <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
                                        <path d="M9 5H7a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2M9 5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v0a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                    </svg>
                                    Log Call Outcome
                                    <span class="cs-card__hint">Optional — adds a note to the activity timeline</span>
                                </div>
                                <div class="cs-card__body">
                                    <form method="POST" action="{{ route('affiliate.leads.addNote', $lead) }}">
                                        @csrf

                                        {{-- Outcome quick-select --}}
                                        <div class="cs-outcome-group mb-3">
                                            @foreach([
                                                'answered'       => ['label' => 'Answered',        'class' => 'cs-outcome--green'],
                                                'voicemail'      => ['label' => 'Left Voicemail',  'class' => 'cs-outcome--amber'],
                                                'no_answer'      => ['label' => 'No Answer',       'class' => 'cs-outcome--gray'],
                                                'callback'       => ['label' => 'Requested Callback', 'class' => 'cs-outcome--blue'],
                                                'not_interested' => ['label' => 'Not Interested',  'class' => 'cs-outcome--red'],
                                            ] as $val => $opt)
                                                <label class="cs-outcome-btn {{ $opt['class'] }}">
                                                    <input type="radio" name="call_outcome" value="{{ $val }}"
                                                           onchange="handleOutcome(this)">
                                                    {{ $opt['label'] }}
                                                </label>
                                            @endforeach
                                        </div>

                                        <textarea name="note"
                                                  id="outcomeNote"
                                                  rows="3"
                                                  class="cs-textarea mb-3"
                                                  placeholder="Add any notes from the call…"></textarea>

                                        <div class="d-flex align-items-center gap-3">
                                            <button type="submit" class="cs-btn cs-btn--primary">
                                                <svg width="13" height="13" viewBox="0 0 16 16" fill="none">
                                                    <path d="M3 8.5l3 3 7-7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                                Save
                                            </button>
                                            <a href="{{ route('affiliate.leads.show', $lead->id) }}" class="cs-btn cs-btn--ghost">
                                                Back to lead
                                            </a>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        @else
                            {{-- No script — just log the call --}}
                            <div class="cs-card">
                                <div class="cs-card__head">
                                    <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
                                        <path d="M9 5H7a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2M9 5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v0a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                    </svg>
                                    Log Call Outcome
                                </div>
                                <div class="cs-card__body">
                                    <form method="POST" action="{{ route('affiliate.leads.addNote', $lead) }}">
                                        @csrf
                                        <div class="cs-outcome-group mb-3">
                                            @foreach([
                                                'answered'       => ['label' => 'Answered',           'class' => 'cs-outcome--green'],
                                                'voicemail'      => ['label' => 'Left Voicemail',     'class' => 'cs-outcome--amber'],
                                                'no_answer'      => ['label' => 'No Answer',          'class' => 'cs-outcome--gray'],
                                                'callback'       => ['label' => 'Requested Callback', 'class' => 'cs-outcome--blue'],
                                                'not_interested' => ['label' => 'Not Interested',     'class' => 'cs-outcome--red'],
                                            ] as $val => $opt)
                                                <label class="cs-outcome-btn {{ $opt['class'] }}">
                                                    <input type="radio" name="call_outcome" value="{{ $val }}"
                                                           onchange="handleOutcome(this)">
                                                    {{ $opt['label'] }}
                                                </label>
                                            @endforeach
                                        </div>
                                        <textarea name="note"
                                                  id="outcomeNote"
                                                  rows="3"
                                                  class="cs-textarea mb-3"
                                                  placeholder="Add any notes from the call…"></textarea>
                                        <div class="d-flex align-items-center gap-3">
                                            <button type="submit" class="cs-btn cs-btn--primary">
                                                <svg width="13" height="13" viewBox="0 0 16 16" fill="none">
                                                    <path d="M3 8.5l3 3 7-7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                                Save & Return to Lead
                                            </button>
                                            <a href="{{ route('affiliate.leads.show', $lead->id) }}" class="cs-btn cs-btn--ghost">Skip</a>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        @endif

                    </div>

                    {{-- Right: lead quick-ref --}}
                    <div class="col-lg-4">

                        {{-- Quick reference --}}
                        <div class="cs-card mb-4">
                            <div class="cs-card__head">
                                <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
                                    <path d="M9 5H7a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2M9 5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v0a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                </svg>
                                Quick Reference
                            </div>
                            <div class="cs-card__body">
                                <div class="cs-ref-row">
                                    <span class="cs-ref-label">Contact</span>
                                    <span class="cs-ref-val">{{ $lead->contact_person_name ?: '—' }}</span>
                                </div>
                                <div class="cs-ref-row">
                                    <span class="cs-ref-label">Phone</span>
                                    <span class="cs-ref-val">
                                        @if($lead->company->phone)
                                            <a href="tel:{{ $phone }}" style="color:#185FA5;text-decoration:none;">
                                                {{ $lead->company->phone }}
                                            </a>
                                        @else
                                            —
                                        @endif
                                    </span>
                                </div>
                                <div class="cs-ref-row">
                                    <span class="cs-ref-label">Units</span>
                                    <span class="cs-ref-val">{{ $lead->company->estimated_units ?: '—' }}</span>
                                </div>
                                <div class="cs-ref-row">
                                    <span class="cs-ref-label">Property Type</span>
                                    <span class="cs-ref-val">
                                        {{ $lead->company->property_type
                                            ? ucfirst(str_replace('_', ' ', $lead->company->property_type))
                                            : '—' }}
                                    </span>
                                </div>
                                <div class="cs-ref-row">
                                    <span class="cs-ref-label">Location</span>
                                    <span class="cs-ref-val">
                                        {{ collect([$lead->company->city, $lead->company->country])->filter()->implode(', ') ?: '—' }}
                                    </span>
                                </div>
                                <div class="cs-ref-row" style="border-bottom:none;">
                                    <span class="cs-ref-label">Temperature</span>
                                    <span class="cs-ref-val">
                                        <span class="cs-temp-badge cs-temp-badge--{{ strtolower($lead->temperature) }}">
                                            {{ ucfirst($lead->temperature) }}
                                        </span>
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- Tip card --}}
                        <div class="cs-tip-card">
                            <div class="cs-tip-card__head">
                                <svg width="13" height="13" viewBox="0 0 16 16" fill="none">
                                    <path d="M8 2l2 4h4l-3 3 1 4-4-2-4 2 1-4-3-3h4z" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                Before You Call
                            </div>
                            <ul class="cs-tip-list">
                                <li>Read through the full script before dialling</li>
                                <li>Find a quiet spot — background noise kills credibility</li>
                                <li>Have their details open in front of you</li>
                                <li>If no answer, leave a short voicemail and log it below</li>
                                <li>Always log the outcome — it keeps your pipeline accurate</li>
                            </ul>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<style>
    /* ── Back link ───────────────────────────────────────── */
    .cs-back-link { font-size:13px;font-weight:500;color:#6b7280;text-decoration:none;transition:color .15s; }
    .cs-back-link:hover { color:#111827; }

    /* ── Flash alerts ────────────────────────────────────── */
        .mod-alert { display:flex;align-items:center;gap:10px;padding:.85rem 1.1rem;border-radius:10px;font-size:14px; }
        .mod-alert--success { background:#E1F5EE;color:#0F6E56; }

    /* ── Target card ─────────────────────────────────────── */
    .cs-target-card {
        background:#fff;border:0.5px solid #e5e7eb;border-radius:14px;
        padding:1.25rem 1.5rem;display:flex;align-items:center;
        justify-content:space-between;flex-wrap:wrap;gap:1rem;
    }
    .cs-target-left { display:flex;align-items:center;gap:14px; }
    .cs-company-avatar {
        width:48px;height:48px;border-radius:12px;background:#E6F1FB;color:#185FA5;
        font-size:15px;font-weight:500;display:inline-flex;align-items:center;
        justify-content:center;flex-shrink:0;
    }
    .cs-company-name { font-size:16px;font-weight:500;color:#111827;margin:0 0 2px; }
    .cs-contact-name { font-size:13px;color:#6b7280;margin:0; }
    .cs-role { color:#9ca3af; }

    /* ── Dial button ─────────────────────────────────────── */
    .cs-dial-btn {
        display:inline-flex;align-items:center;gap:12px;
        background:#111827;color:#fff;
        padding:.85rem 1.5rem;border-radius:10px;text-decoration:none;
        transition:background .2s,transform .2s,box-shadow .2s;flex-shrink:0;
    }
    .cs-dial-btn:hover {
        background:#374151;color:#fff;
        transform:translateY(-1px);
        box-shadow:0 6px 18px rgba(0,0,0,.2);
    }

    /* ── Cards ───────────────────────────────────────────── */
    .cs-card { background:#fff;border:0.5px solid #e5e7eb;border-radius:12px;overflow:hidden; }
    .cs-card__head {
        display:flex;align-items:center;gap:8px;padding:.85rem 1.25rem;
        border-bottom:0.5px solid #e5e7eb;background:#fafafa;
        font-size:13px;font-weight:500;color:#374151;
    }
    .cs-card__hint { font-size:11px;font-weight:400;color:#9ca3af;margin-left:4px; }
    .cs-card__body { padding:1.25rem; }

    /* ── Script body ─────────────────────────────────────── */
    .cs-script-body {
        font-size:14px;color:#374151;line-height:1.8;
        white-space:pre-wrap;font-family:inherit;
    }

    /* ── Copy button ─────────────────────────────────────── */
    .cs-copy-btn {
        display:inline-flex;align-items:center;gap:5px;margin-left:auto;
        font-size:11px;font-weight:500;padding:4px 10px;border-radius:6px;
        border:0.5px solid #e5e7eb;background:#fff;color:#6b7280;cursor:pointer;
        transition:background .15s,color .15s;
    }
    .cs-copy-btn:hover { background:#f3f4f6;color:#374151; }
    .cs-copy-btn--copied { background:#E1F5EE;border-color:#9FE1CB;color:#0F6E56; }

    /* ── Outcome buttons ─────────────────────────────────── */
    .cs-outcome-group { display:flex;flex-wrap:wrap;gap:8px; }
    .cs-outcome-btn {
        display:inline-flex;align-items:center;gap:6px;padding:7px 14px;
        border-radius:8px;border:0.5px solid #e5e7eb;font-size:13px;font-weight:500;
        cursor:pointer;background:#fafafa;color:#6b7280;transition:background .15s,border-color .15s,color .15s;
    }
    .cs-outcome-btn input[type="radio"] { display:none; }

    .cs-outcome--green:hover,  .cs-outcome--green:has(input:checked)  { background:#E1F5EE;border-color:#9FE1CB;color:#0F6E56; }
    .cs-outcome--amber:hover,  .cs-outcome--amber:has(input:checked)  { background:#FAEEDA;border-color:#FAC775;color:#854F0B; }
    .cs-outcome--gray:hover,   .cs-outcome--gray:has(input:checked)   { background:#f3f4f6;border-color:#d1d5db;color:#374151; }
    .cs-outcome--blue:hover,   .cs-outcome--blue:has(input:checked)   { background:#E6F1FB;border-color:#B5D4F4;color:#185FA5; }
    .cs-outcome--red:hover,    .cs-outcome--red:has(input:checked)    { background:#FCEBEB;border-color:#F7C1C1;color:#A32D2D; }

    /* ── Textarea ────────────────────────────────────────── */
    .cs-textarea {
        width:100%;padding:9px 12px;font-size:14px;color:#111827;
        background:#fff;border:0.5px solid #d1d5db;border-radius:8px;
        outline:none;resize:vertical;min-height:90px;line-height:1.6;
        transition:border-color .15s,box-shadow .15s;
    }
    .cs-textarea:focus { border-color:#185FA5;box-shadow:0 0 0 3px rgba(24,95,165,.1); }

    /* ── Buttons ─────────────────────────────────────────── */
    .cs-btn {
        display:inline-flex;align-items:center;gap:7px;padding:9px 20px;
        font-size:13px;font-weight:500;border-radius:8px;border:none;
        cursor:pointer;text-decoration:none;transition:background .2s,transform .2s;
    }
    .cs-btn--primary { background:#185FA5;color:#fff; }
    .cs-btn--primary:hover { background:#0C447C;color:#fff;transform:translateY(-1px); }
    .cs-btn--ghost { background:#f3f4f6;color:#6b7280;border:0.5px solid #e5e7eb; }
    .cs-btn--ghost:hover { background:#e5e7eb;color:#374151; }

    /* ── Quick reference rows ────────────────────────────── */
    .cs-ref-row { display:flex;justify-content:space-between;align-items:center;padding:.55rem 0;border-bottom:0.5px solid #f3f4f6;font-size:13px; }
    .cs-ref-label { color:#9ca3af;font-weight:500; }
    .cs-ref-val   { color:#111827;font-weight:500;text-align:right; }

    /* ── Temperature + status badges ────────────────────── */
    .cs-temp-badge { display:inline-flex;align-items:center;gap:4px;font-size:11px;font-weight:500;padding:3px 10px;border-radius:99px; }
    .cs-temp-badge--hot  { background:#FAECE7;color:#993C1D; }
    .cs-temp-badge--warm { background:#FAEEDA;color:#854F0B; }
    .cs-temp-badge--cold { background:#E6F1FB;color:#185FA5; }
    .cs-status-badge { display:inline-flex;align-items:center;font-size:11px;font-weight:500;padding:3px 10px;border-radius:99px; }
    .cs-status-badge--active             { background:#E1F5EE;color:#0F6E56; }
    .cs-status-badge--demo_scheduled     { background:#E6F1FB;color:#185FA5; }
    .cs-status-badge--demo_completed     { background:#EEEDFE;color:#534AB7; }
    .cs-status-badge--pending_conversion { background:#EEEDFE;color:#3C3489; }
    .cs-status-badge--trial              { background:#EEEDFE;color:#534AB7; }

    /* ── Tip card ────────────────────────────────────────── */
    .cs-tip-card {
        background:#FAEEDA;border:0.5px solid #FAC775;
        border-radius:12px;overflow:hidden;
    }
    .cs-tip-card__head {
        display:flex;align-items:center;gap:8px;padding:.75rem 1rem;
        border-bottom:0.5px solid #FAC775;
        font-size:12px;font-weight:500;color:#854F0B;
    }
    .cs-tip-list {
        margin:0;padding:.85rem 1rem .85rem 1.75rem;
        font-size:12px;color:#854F0B;line-height:1.8;
    }
</style>

<script>
    // Pre-fill note textarea with selected outcome label
    function handleOutcome(input) {
        const labels = {
            'answered':       'Called {{ addslashes($lead->contact_person_name ?? $lead->company->company_name) }} — answered. ',
            'voicemail':      'Called {{ addslashes($lead->contact_person_name ?? $lead->company->company_name) }} — left voicemail. ',
            'no_answer':      'Called {{ addslashes($lead->contact_person_name ?? $lead->company->company_name) }} — no answer. ',
            'callback':       'Called {{ addslashes($lead->contact_person_name ?? $lead->company->company_name) }} — requested callback. ',
            'not_interested': 'Called {{ addslashes($lead->contact_person_name ?? $lead->company->company_name) }} — not interested. ',
        };
        const note = document.getElementById('outcomeNote');
        if (note.value === '' || Object.values(labels).some(l => note.value.startsWith(l.split('—')[0]))) {
            note.value = labels[input.value] || '';
        }
        note.focus();
        note.setSelectionRange(note.value.length, note.value.length);
    }

    // Copy script to clipboard
    function copyScript() {
        const text = document.getElementById('scriptContent')?.innerText;
        if (!text) return;
        navigator.clipboard.writeText(text).then(() => {
            const btn = document.getElementById('copyBtn');
            btn.classList.add('cs-copy-btn--copied');
            btn.innerHTML = `
                <svg width="12" height="12" viewBox="0 0 16 16" fill="none">
                    <path d="M3 8.5l3 3 7-7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                Copied!
            `;
            setTimeout(() => {
                btn.classList.remove('cs-copy-btn--copied');
                btn.innerHTML = `
                    <svg width="12" height="12" viewBox="0 0 16 16" fill="none">
                        <rect x="5" y="5" width="9" height="9" rx="1.5" stroke="currentColor" stroke-width="1.5"/>
                        <path d="M11 5V3a1 1 0 0 0-1-1H3a1 1 0 0 0-1 1v7a1 1 0 0 0 1 1h2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                    </svg>
                    Copy
                `;
            }, 2500);
        });
    }
</script>

@endsection