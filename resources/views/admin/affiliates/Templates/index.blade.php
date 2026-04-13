@extends('admin.layouts.app')

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="page-content-wrapper bg-white p-30 radius-20">

                {{-- Page Title --}}
                @php
                    $pageTitle = 'Templates';
                @endphp
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between border-bottom mb-4 pb-2">
                            <div class="page-title-left">
                                <h3 class="mb-sm-0">Action Templates</h3>
                            </div>
                            <div class="page-title-right">
                                <ol class="breadcrumb mb-0">
                                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                                    <li class="breadcrumb-item active">Action Templates</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Header row --}}
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
                    <div>
                        <p class="at-subtitle">Manage reusable message templates for WhatsApp, email, and call guides used in the affiliate suggestion engine.</p>
                    </div>
                    <a href="{{ route('admin.templates.create') }}" class="at-btn-primary">
                        <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
                            <path d="M8 3v10M3 8h10" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                        </svg>
                        Add Template
                    </a>
                </div>

                {{-- Stats row --}}
                @php
                    $byType = $templates->groupBy('action_type');
                    $byCategory = $templates->groupBy('category');
                @endphp
                <div class="row g-3 mb-4">
                    <div class="col-6 col-md-3">
                        <div class="at-stat">
                            <div class="at-stat__icon" style="background:#f3f4f6;color:#444441;">
                                <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
                                    <path d="M9 5H7a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2M9 5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v0a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                </svg>
                            </div>
                            <div class="at-stat__label">Total</div>
                            <div class="at-stat__val">{{ $templates->total() }}</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="at-stat" style="background:#F0FDF6;border-color:#9FE1CB;">
                            <div class="at-stat__icon" style="background:#E1F5EE;color:#0F6E56;">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L0 24l6.304-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                                </svg>
                            </div>
                            <div class="at-stat__label" style="color:#0F6E56;">WhatsApp</div>
                            <div class="at-stat__val" style="color:#0F6E56;">{{ $byType->get('whatsapp', collect())->count() }}</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="at-stat" style="background:#EEF5FD;border-color:#B5D4F4;">
                            <div class="at-stat__icon" style="background:#E6F1FB;color:#185FA5;">
                                <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
                                    <rect x="2" y="4" width="12" height="9" rx="1.5" stroke="currentColor" stroke-width="1.5"/>
                                    <path d="M2 5l6 4 6-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </div>
                            <div class="at-stat__label" style="color:#185FA5;">Email</div>
                            <div class="at-stat__val" style="color:#185FA5;">{{ $byType->get('email', collect())->count() }}</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="at-stat" style="background:#F5F3FF;border-color:#AFA9EC;">
                            <div class="at-stat__icon" style="background:#EEEDFE;color:#534AB7;">
                                <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
                                    <path d="M3.5 1h3l1.5 3.5-2 1.5a9 9 0 0 0 4 4l1.5-2L15 9.5v3a2 2 0 0 1-2 2A12 12 0 0 1 1 3a2 2 0 0 1 2-2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </div>
                            <div class="at-stat__label" style="color:#534AB7;">Call Scripts</div>
                            <div class="at-stat__val" style="color:#534AB7;">{{ $byType->get('call', collect())->count() }}</div>
                        </div>
                    </div>
                </div>

                {{-- Flash --}}
                @if(session('success'))
                    <div class="at-alert at-alert--success mb-4">
                        <svg width="15" height="15" viewBox="0 0 16 16" fill="none" style="flex-shrink:0;">
                            <circle cx="8" cy="8" r="7" stroke="currentColor" stroke-width="1.5"/>
                            <path d="M4.5 8.5l2.5 2.5 4.5-5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        {{ session('success') }}
                    </div>
                @endif

                {{-- Table --}}
                <div class="at-card">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr style="border-bottom:0.5px solid #e5e7eb;background:#fafafa;">
                                    <th class="at-th">Template Name</th>
                                    <th class="at-th">Type</th>
                                    <th class="at-th">Category</th>
                                    <th class="at-th">Materials</th>
                                    <th class="at-th">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($templates as $t)
                                    <tr style="border-bottom:0.5px solid #f3f4f6;">

                                        {{-- Name --}}
                                        <td class="at-td">
                                            <div style="font-weight:500;font-size:14px;color:#111827;">{{ $t->name }}</div>
                                            @if($t->message_template)
                                                <div style="font-size:12px;color:#9ca3af;margin-top:2px;">
                                                    {{ Str::limit(strip_tags($t->message_template), 60) }}
                                                </div>
                                            @endif
                                        </td>

                                        {{-- Type badge --}}
                                        <td class="at-td">
                                            @if($t->action_type === 'whatsapp')
                                                <span class="at-badge at-badge--whatsapp">
                                                    <svg width="10" height="10" viewBox="0 0 24 24" fill="currentColor">
                                                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L0 24l6.304-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                                                    </svg>
                                                    WhatsApp
                                                </span>
                                            @elseif($t->action_type === 'email')
                                                <span class="at-badge at-badge--email">
                                                    <svg width="10" height="10" viewBox="0 0 16 16" fill="none">
                                                        <rect x="2" y="4" width="12" height="9" rx="1.5" stroke="currentColor" stroke-width="1.5"/>
                                                        <path d="M2 5l6 4 6-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                    </svg>
                                                    Email
                                                </span>
                                            @elseif($t->action_type === 'call')
                                                <span class="at-badge at-badge--call">
                                                    <svg width="10" height="10" viewBox="0 0 16 16" fill="none">
                                                        <path d="M3.5 1h3l1.5 3.5-2 1.5a9 9 0 0 0 4 4l1.5-2L15 9.5v3a2 2 0 0 1-2 2A12 12 0 0 1 1 3a2 2 0 0 1 2-2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                    </svg>
                                                    Call
                                                </span>
                                            @endif
                                        </td>

                                        {{-- Category badge --}}
                                        <td class="at-td">
                                            @php
                                                $catLabels = [
                                                    'intro'     => ['label' => 'Intro',     'class' => 'at-badge--cat-intro'],
                                                    'follow_up' => ['label' => 'Follow Up',  'class' => 'at-badge--cat-follow'],
                                                    'demo_complete' => ['label' => 'Demo Complete',  'class' => 'at-badge--cat-democomplete'],
                                                    'trial'     => ['label' => 'Trial',      'class' => 'at-badge--cat-trial'],
                                                    'trial_expired'     => ['label' => 'Trial Expired',      'class' => 'at-badge--cat-trialexpired'],
                                                    'retention'     => ['label' => 'Retention',  'class' => 'at-badge--cat-retention'],
                                                    'reengage'  => ['label' => 'Re-engage',  'class' => 'at-badge--cat-reengage'],
                                                    'reminder'  => ['label' => 'Reminder',   'class' => 'at-badge--cat-reminder'],
                                                ];
                                                $cat = $catLabels[$t->category] ?? ['label' => ucfirst($t->category), 'class' => ''];
                                            @endphp
                                            <span class="at-badge {{ $cat['class'] }}">{{ $cat['label'] }}</span>
                                        </td>

                                        {{-- Materials count --}}
                                        <td class="at-td">
                                            @if($t->materials && $t->materials->count() > 0)
                                                <span class="at-badge at-badge--material">
                                                    <svg width="10" height="10" viewBox="0 0 16 16" fill="none">
                                                        <path d="M4 2h6l4 4v8H4V2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                        <path d="M10 2v4h4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                                    </svg>
                                                    {{ $t->materials->count() }} file{{ $t->materials->count() > 1 ? 's' : '' }}
                                                </span>
                                            @else
                                                <span style="font-size:12px;color:#d1d5db;">—</span>
                                            @endif
                                        </td>

                                        {{-- Actions --}}
                                        <td class="at-td">
                                            <div class="d-flex align-items-center gap-2">
                                                <a href="{{ route('admin.templates.edit', $t->id) }}" class="at-action-btn at-action-btn--edit">
                                                    <svg width="12" height="12" viewBox="0 0 16 16" fill="none">
                                                        <path d="M11.5 2.5a1.5 1.5 0 0 1 2 2L5 13H3v-2L11.5 2.5z" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                                                    </svg>
                                                    Edit
                                                </a>
                                                <form method="POST" action="{{ route('admin.templates.destroy', $t->id) }}" style="display:inline;"
                                                      onsubmit="return confirm('Delete this template? This cannot be undone.')">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="at-action-btn at-action-btn--delete">
                                                        <svg width="12" height="12" viewBox="0 0 16 16" fill="none">
                                                            <path d="M3 4h10M5 4V3a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v1M6 7v5M10 7v5M4 4l1 9h6l1-9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                        </svg>
                                                        Delete
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" style="padding:3rem 1rem;text-align:center;">
                                            <svg width="36" height="36" viewBox="0 0 24 24" fill="none" style="color:#d1d5db;margin-bottom:10px;display:block;margin-left:auto;margin-right:auto;">
                                                <path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2M9 5a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2M9 5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                            </svg>
                                            <p style="color:#9ca3af;font-size:14px;margin:0;">No templates yet. Create your first one.</p>
                                            <a href="{{ route('admin.templates.create') }}" class="at-btn-primary" style="margin-top:12px;display:inline-flex;">
                                                <svg width="13" height="13" viewBox="0 0 16 16" fill="none">
                                                    <path d="M8 3v10M3 8h10" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                                </svg>
                                                Add Template
                                            </a>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                {{-- Pagination --}}
                @if($templates->hasPages())
                    <div class="mt-4">
                        {{ $templates->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
    /* ── Subtitle ────────────────────────────────────────── */
    .at-subtitle { font-size:13px;color:#6b7280;margin:0;max-width:560px; }

    /* ── Primary button ──────────────────────────────────── */
    .at-btn-primary {
        display:inline-flex;align-items:center;gap:7px;padding:9px 18px;
        background:#185FA5;color:#fff;font-size:13px;font-weight:500;
        border-radius:8px;text-decoration:none;border:none;cursor:pointer;
        transition:background .2s,transform .2s,box-shadow .2s;
    }
    .at-btn-primary:hover { background:#0C447C;color:#fff;transform:translateY(-1px);box-shadow:0 5px 14px rgba(24,95,165,.22); }

    /* ── Stats ───────────────────────────────────────────── */
    .at-stat {
        background:#fafafa;border:0.5px solid #e5e7eb;border-radius:12px;
        padding:1rem;height:100%;transition:box-shadow .2s,transform .2s;
    }
    .at-stat:hover { box-shadow:0 4px 14px rgba(0,0,0,.06);transform:translateY(-2px); }
    .at-stat__icon { display:inline-flex;align-items:center;justify-content:center;width:32px;height:32px;border-radius:8px;margin-bottom:10px; }
    .at-stat__label { font-size:11px;font-weight:500;text-transform:uppercase;letter-spacing:.06em;color:#9ca3af;margin-bottom:4px; }
    .at-stat__val { font-size:24px;font-weight:500;color:#111827;line-height:1; }

    /* ── Alert ───────────────────────────────────────────── */
    .at-alert { display:flex;align-items:center;gap:10px;padding:.85rem 1.1rem;border-radius:10px;font-size:14px; }
    .at-alert--success { background:#E1F5EE;color:#0F6E56; }

    /* ── Table card ──────────────────────────────────────── */
    .at-card { border:0.5px solid #e5e7eb;border-radius:12px;overflow:hidden;background:#fff; }
    .at-th { padding:.8rem 1.1rem;font-size:11px;font-weight:500;color:#6b7280;text-transform:uppercase;letter-spacing:.06em;border:none;white-space:nowrap; }
    .at-td { padding:.85rem 1.1rem;border:none;vertical-align:middle; }

    /* ── Badges ──────────────────────────────────────────── */
    .at-badge { display:inline-flex;align-items:center;gap:4px;font-size:11px;font-weight:500;padding:3px 10px;border-radius:99px;white-space:nowrap; }
    .at-badge--whatsapp  { background:#E1F5EE;color:#0F6E56; }
    .at-badge--email     { background:#E6F1FB;color:#185FA5; }
    .at-badge--call      { background:#EEEDFE;color:#534AB7; }
    .at-badge--material  { background:#f3f4f6;color:#5F5E5A; }

    .at-badge--cat-intro    { background:#E6F1FB;color:#185FA5; }
    .at-badge--cat-follow   { background:#EEEDFE;color:#534AB7; }
    .at-badge--cat-democomplete{ background:#EEEDFE;color:#534AB7;  }
    .at-badge--cat-trial    { background:#E1F5EE;color:#0F6E56; }
    .at-badge--cat-trialexpired { background:#FAEEDA;color:#854F0B; }
    .at-badge--cat-retention { background:#E1F5EE;color:#0F6E56; }
    .at-badge--cat-reengage { background:#FAEEDA;color:#854F0B; }
    .at-badge--cat-reminder { background:#FAECE7;color:#993C1D; }

    /* ── Action buttons ──────────────────────────────────── */
    .at-action-btn {
        display:inline-flex;align-items:center;gap:5px;font-size:12px;font-weight:500;
        padding:5px 12px;border-radius:7px;border:0.5px solid transparent;
        text-decoration:none;white-space:nowrap;cursor:pointer;background:transparent;
        transition:background .15s,transform .15s;
    }
    .at-action-btn:hover { transform:translateY(-1px); }
    .at-action-btn--edit   { background:#EEF5FD;border-color:#B5D4F4;color:#185FA5; }
    .at-action-btn--edit:hover { background:#dbeeff;color:#185FA5; }
    .at-action-btn--delete { background:#FCEBEB;border-color:#F7C1C1;color:#A32D2D; }
    .at-action-btn--delete:hover { background:#fad9d9;color:#A32D2D; }
</style>
@endsection