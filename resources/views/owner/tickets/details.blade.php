@extends('owner.layouts.app')

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="page-content-wrapper bg-white p-30 radius-20">

                {{-- ── Page header ──────────────────────────────────────── --}}
                <div class="td-page-header mb-4">
                    <div>
                        <h2 class="td-page-title">{{ $pageTitle }}</h2>
                        <nav aria-label="breadcrumb">
                            <ol class="td-breadcrumb">
                                <li>
                                    <a href="{{ route('owner.dashboard') }}">{{ __('Dashboard') }}</a>
                                    <svg width="8" height="8" viewBox="0 0 24 24" fill="none" aria-hidden="true"><polyline points="9 18 15 12 9 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </li>
                                <li>
                                    <a href="javascript:history.back()">{{ __('Tickets') }}</a>
                                    <svg width="8" height="8" viewBox="0 0 24 24" fill="none" aria-hidden="true"><polyline points="9 18 15 12 9 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </li>
                                <li aria-current="page">{{ $pageTitle }}</li>
                            </ol>
                        </nav>
                    </div>
                </div>

                {{-- ── Two-column layout ────────────────────────────────── --}}
                <div class="td-layout">

                    {{-- ── LEFT: thread + reply ──────────────────────────── --}}
                    <div class="td-main">

                        {{-- Original ticket --}}
                        <div class="ow-card td-section mb-4">
                            <div class="dash-card__head">
                                <div class="td-panel-icon td-panel-icon--blue">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none">
                                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </div>
                                <div>
                                    <span class="td-panel-title">{{ $ticket->user->name }}</span>
                                    <span class="td-role-badge">
                                        @if ($ticket->user->role == USER_ROLE_OWNER)
                                            {{ __('Owner') }}
                                        @elseif($ticket->user->role == USER_ROLE_TENANT)
                                            {{ __('Tenant') }}
                                        @elseif($ticket->user->role == USER_ROLE_MAINTAINER)
                                            {{ __('Maintainer') }}
                                        @endif
                                    </span>
                                </div>
                            </div>

                            <div class="td-section__body">
                                <div class="td-field mb-3">
                                    <span class="td-field__label">{{ __('Details') }}</span>
                                    <p class="td-field__value">{{ $ticket->details }}</p>
                                </div>

                                @if($ticket->attachments->isNotEmpty())
                                <div class="td-field">
                                    <span class="td-field__label">{{ __('Attachments') }}</span>
                                    <div class="td-attachments">
                                        @foreach ($ticket->attachments as $attachment)
                                            @if (in_array(pathinfo($attachment->file_name, PATHINFO_EXTENSION), imageExtensionList()))
                                                <a href="{{ $attachment->FileUrl }}"
                                                   class="venobox td-attachment-thumb"
                                                   data-gall="attach{{ $attachment->id }}">
                                                    <img src="{{ $attachment->FileUrl }}" alt="">
                                                </a>
                                            @else
                                                <a href="{{ $attachment->FileUrl }}" class="td-attachment-file" download>
                                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><polyline points="14 2 14 8 20 8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                                                    {{ $attachment->file_name }}
                                                </a>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>

                        {{-- Replies thread --}}
                        @if (count($replies) > 0)
                        <div class="ow-card td-section mb-4">
                            <div class="dash-card__head">
                                <div class="td-panel-icon td-panel-icon--purple">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none">
                                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </div>
                                <span class="td-panel-title">{{ __('Ticket Replies') }}</span>
                                <span class="ow-badge ow-badge--blue" style="margin-left:auto;">{{ count($replies) }}</span>
                            </div>

                            <div class="td-replies">
                                @foreach ($replies as $reply)
                                <div class="td-reply {{ $reply->user_id == auth()->id() ? 'td-reply--mine' : '' }}">
                                    <div class="td-reply__head">
                                        <div>
                                            <span class="td-reply__name">
                                                @if ($reply->user_id == auth()->id()){{ __('You — ') }}@endif
                                                {{ $reply->first_name }} {{ $reply->last_name }}
                                            </span>
                                            <span class="td-role-badge">
                                                @if ($reply->role == USER_ROLE_OWNER)
                                                    {{ __('Owner') }}
                                                @elseif ($reply->role == USER_ROLE_TENANT)
                                                    {{ __('Tenant') }}
                                                @elseif ($reply->role == USER_ROLE_MAINTAINER)
                                                    {{ __('Maintainer') }}
                                                @endif
                                            </span>
                                        </div>
                                        <span class="td-reply__time">{{ $reply->created_at->diffForHumans() }}</span>
                                    </div>
                                    <p class="td-reply__body">{{ $reply->reply }}</p>

                                    @if (count($reply->attachments) > 0)
                                    <div class="td-field mt-2">
                                        <span class="td-field__label">{{ __('Attachments') }}</span>
                                        <div class="td-attachments">
                                            @foreach ($reply->attachments as $attachment)
                                                @if (in_array(pathinfo($attachment->file_name, PATHINFO_EXTENSION), imageExtensionList()))
                                                    <a href="{{ $attachment->FileUrl }}"
                                                       class="venobox td-attachment-thumb"
                                                       data-gall="gallery01">
                                                        <img src="{{ $attachment->FileUrl }}" alt="">
                                                    </a>
                                                @else
                                                    <a href="{{ $attachment->FileUrl }}" class="td-attachment-file" download>
                                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><polyline points="14 2 14 8 20 8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                                                        {{ $attachment->file_name }}
                                                    </a>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                    @endif
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        {{-- Write a reply --}}
                        <div class="ow-card td-section">
                            <div class="dash-card__head">
                                <div class="td-panel-icon td-panel-icon--green">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none">
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </div>
                                <span class="td-panel-title">{{ __('Write a Reply') }}</span>
                            </div>

                            <div class="td-section__body">
                                <form class="ajax" action="{{ route('owner.ticket.reply') }}" method="POST" data-handler="getShowMessage">
                                    <input type="hidden" name="ticket_id" value="{{ $ticket->id }}">

                                    <div class="td-field mb-3">
                                        <label class="td-field__label">{{ __('Reply') }}</label>
                                        <textarea class="td-textarea" placeholder="{{ __('Write your reply here…') }}" name="reply"></textarea>
                                    </div>

                                    <div class="td-field mb-4">
                                        <label class="td-field__label">{{ __('Upload Attachments') }}</label>
                                        <input type="file" id="attachments" name="attachments[]" class="dropify" data-height="160" multiple />
                                    </div>

                                    <button type="submit" class="td-btn td-btn--primary" title="{{ __('Submit') }}">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none">
                                            <line x1="22" y1="2" x2="11" y2="13" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                            <polygon points="22 2 15 22 11 13 2 9 22 2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                        {{ __('Submit Reply') }}
                                    </button>
                                </form>
                            </div>
                        </div>

                    </div>

                    {{-- ── RIGHT: ticket info sidebar ────────────────────── --}}
                    <div class="td-sidebar">
                        <div class="ow-card td-info-card">
                            <div class="dash-card__head">
                                <div class="td-panel-icon td-panel-icon--blue">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none">
                                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.8"/>
                                        <line x1="12" y1="8" x2="12" y2="12" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                        <line x1="12" y1="16" x2="12.01" y2="16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                    </svg>
                                </div>
                                <span class="td-panel-title">{{ __('Ticket Info') }}</span>
                                <span class="td-ticket-no" style="margin-left:auto;">#{{ $ticket->ticket_no }}</span>
                            </div>

                            <div class="td-info-rows">
                                <div class="td-info-row">
                                    <span class="td-info-row__label">{{ __('Title') }}</span>
                                    <span class="td-info-row__value">{{ $ticket->title }}</span>
                                </div>
                                <div class="td-info-row">
                                    <span class="td-info-row__label">{{ __('Topic') }}</span>
                                    <span class="td-info-row__value">{{ $ticket->topic->name }}</span>
                                </div>
                                <div class="td-info-row">
                                    <span class="td-info-row__label">{{ __('Property') }}</span>
                                    <span class="td-info-row__value">{{ $ticket->property->name }}</span>
                                </div>
                                <div class="td-info-row">
                                    <span class="td-info-row__label">{{ __('Unit') }}</span>
                                    <span class="td-info-row__value">{{ $ticket->unit->unit_name }}</span>
                                </div>
                                <div class="td-info-row">
                                    <span class="td-info-row__label">{{ __('Status') }}</span>
                                    <span>
                                        @if ($ticket->status == TICKET_STATUS_OPEN)
                                            <span class="ow-badge ow-badge--pending">{{ __('Open') }}</span>
                                        @elseif ($ticket->status == TICKET_STATUS_INPROGRESS)
                                            <span class="ow-badge ow-badge--blue">{{ __('In Progress') }}</span>
                                        @elseif ($ticket->status == TICKET_STATUS_REOPEN)
                                            <span class="ow-badge ow-badge--danger">{{ __('Reopen') }}</span>
                                        @elseif ($ticket->status == TICKET_STATUS_RESOLVED)
                                            <span class="ow-badge ow-badge--paid">{{ __('Resolved') }}</span>
                                        @else
                                            <span class="ow-badge ow-badge--grey">{{ __('Closed') }}</span>
                                        @endif
                                    </span>
                                </div>
                                <div class="td-info-row">
                                    <span class="td-info-row__label">{{ __('Opened') }}</span>
                                    <span class="td-info-row__value">{{ $ticket->created_at->format('Y-m-d') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </div>
</div>
@endsection

@push('script')
    <script src="{{ asset('assets/js/custom/ticket.js') }}"></script>
@endpush

@push('style')
<style>
/* ── Tokens ───────────────────────────────────────────────────── */
:root {
    --blue:        #185FA5;
    --blue-hover:  #0F4A84;
    --blue-light:  #E6F1FB;
    --blue-border: #B5D4F4;
    --blue-faint:  #185ea56e;
    --green:       #1D9E75;
    --green-dark:  #0F6E56;
    --green-light: #E1F5EE;
    --amber:       #854F0B;
    --amber-light: #FAEEDA;
    --amber-border:#F5D9A8;
    --red:         #993C1D;
    --red-light:   #FAECE7;
    --purple:      #534AB7;
    --purple-light:#EDE9FF;
    --gray-900:    #111827;
    --gray-800:    #1f2937;
    --gray-700:    #374151;
    --gray-500:    #6b7280;
    --gray-400:    #9ca3af;
    --gray-200:    #e5e7eb;
    --gray-100:    #f3f4f6;
    --gray-50:     #fafafa;
    --white:       #ffffff;
}

/* ── Page header ──────────────────────────────────────────────── */
.td-page-header {
    display:flex; align-items:flex-start; justify-content:space-between;
    flex-wrap:wrap; gap:1rem;
}
.td-page-title { font-size:22px; font-weight:500; color:var(--gray-900); margin:0 0 6px; }
.td-breadcrumb {
    display:flex; align-items:center; gap:6px; list-style:none;
    padding:0; margin:0; font-size:12px; color:var(--gray-400);
}
.td-breadcrumb li { display:flex; align-items:center; gap:6px; }
.td-breadcrumb a  { color:var(--blue); font-weight:500; text-decoration:none; }

/* ── Two-column layout ────────────────────────────────────────── */
.td-layout {
    display:grid;
    grid-template-columns:1fr 300px;
    gap:1.25rem;
    align-items:start;
}
@media(max-width:992px) { .td-layout { grid-template-columns:1fr; } }

/* Sidebar sticks on large screens */
@media(min-width:993px) {
    .td-sidebar { position:sticky; top:1.5rem; }
}

/* ── Shared card ──────────────────────────────────────────────── */
.ow-card {
    background:var(--white);
    border:0.5px solid var(--blue-faint);
    border-radius:12px; overflow:hidden;
    box-shadow: 0 4px 12px rgba(0,0,0,.04),
                0 0 0 1px rgba(24,95,165,.05),
                0 6px 18px rgba(24,95,165,.06);
}
.td-section { }

/* Card head */
.dash-card__head {
    display:flex; align-items:center; gap:10px;
    padding:.75rem 1.1rem; border-bottom:0.5px solid var(--gray-200);
    background:var(--gray-50);
}
.td-panel-icon {
    width:28px; height:28px; border-radius:7px;
    display:inline-flex; align-items:center; justify-content:center; flex-shrink:0;
}
.td-panel-icon--blue   { background:var(--blue-light);   color:var(--blue); }
.td-panel-icon--green  { background:var(--green-light);  color:var(--green); }
.td-panel-icon--purple { background:var(--purple-light); color:var(--purple); }
.td-panel-title { font-size:14px; font-weight:500; color:var(--gray-900); }
.td-role-badge {
    font-size:11px; font-weight:500; color:var(--blue);
    background:var(--blue-light); border:0.5px solid var(--blue-border);
    border-radius:99px; padding:2px 8px;
}
.td-ticket-no {
    font-size:11px; font-weight:500; font-family:monospace;
    color:#0C447C; background:var(--blue-light);
    border:0.5px solid var(--blue-border);
    border-radius:99px; padding:2px 9px;
}

/* ── Section body ─────────────────────────────────────────────── */
.td-section__body { padding:16px 20px; }
.td-field { display:flex; flex-direction:column; gap:5px; }
.td-field__label {
    font-size:10px; font-weight:500; text-transform:uppercase;
    letter-spacing:.07em; color:var(--gray-400);
}
.td-field__value { font-size:13px; color:var(--gray-700); line-height:1.65; margin:0; }

/* ── Attachments ──────────────────────────────────────────────── */
.td-attachments { display:flex; flex-wrap:wrap; gap:8px; margin-top:4px; }
.td-attachment-thumb {
    width:52px; height:52px; border-radius:8px; overflow:hidden;
    border:0.5px solid var(--gray-200); display:block; flex-shrink:0;
}
.td-attachment-thumb img { width:100%; height:100%; object-fit:cover; display:block; }
.td-attachment-file {
    display:inline-flex; align-items:center; gap:5px;
    font-size:11px; color:var(--blue); text-decoration:none;
    background:var(--blue-light); border:0.5px solid var(--blue-border);
    border-radius:6px; padding:4px 10px;
}

/* ── Replies thread ───────────────────────────────────────────── */
.td-replies { padding:0; }
.td-reply {
    padding:16px 20px;
    border-bottom:0.5px solid var(--gray-100);
}
.td-reply:last-child { border-bottom:none; }
.td-reply--mine { background:var(--blue-light); }
.td-reply__head {
    display:flex; align-items:center; justify-content:space-between;
    gap:10px; margin-bottom:8px;
}
.td-reply__name { font-size:13px; font-weight:600; color:var(--gray-900); }
.td-reply__time { font-size:11px; color:var(--gray-400); white-space:nowrap; }
.td-reply__body { font-size:13px; color:var(--gray-700); line-height:1.65; margin:0; }

/* ── Reply form ───────────────────────────────────────────────── */
.td-textarea {
    width:100%; padding:10px 12px;
    border:0.5px solid var(--gray-200); border-radius:7px;
    font-size:13px; color:var(--gray-700); outline:none;
    resize:vertical; min-height:100px;
    transition:border-color .15s, box-shadow .15s;
}
.td-textarea:focus { border-color:var(--blue); box-shadow:0 0 0 3px rgba(24,95,165,.1); }
.td-btn {
    display:inline-flex; align-items:center; gap:6px;
    font-size:12px; font-weight:500; padding:7px 16px;
    border-radius:7px; border:none; cursor:pointer; transition:all .13s;
}
.td-btn--primary { background:var(--blue); color:var(--white); }
.td-btn--primary:hover { background:var(--blue-hover); transform:translateY(-1px); }

/* ── Info sidebar rows ────────────────────────────────────────── */
.td-info-rows { }
.td-info-row {
    display:flex; align-items:flex-start; justify-content:space-between;
    gap:12px; padding:11px 20px;
    border-bottom:0.5px solid var(--gray-100);
}
.td-info-row:last-child { border-bottom:none; }
.td-info-row:hover { background:var(--gray-50); }
.td-info-row__label {
    font-size:10px; font-weight:500; text-transform:uppercase;
    letter-spacing:.07em; color:var(--gray-400); flex-shrink:0;
    padding-top:2px;
}
.td-info-row__value {
    font-size:13px; font-weight:500; color:var(--gray-800);
    text-align:right;
}

/* ── Badges ───────────────────────────────────────────────────── */
.ow-badge {
    display:inline-flex; align-items:center; gap:4px;
    font-size:11px; font-weight:500; padding:3px 9px;
    border-radius:99px; white-space:nowrap;
}
.ow-badge--paid    { background:var(--green-light);  color:var(--green-dark); }
.ow-badge--danger  { background:var(--red-light);    color:var(--red); }
.ow-badge--pending { background:var(--amber-light);  color:var(--amber); border:0.5px solid var(--amber-border); }
.ow-badge--blue    { background:var(--blue-light);   color:#0C447C; border:0.5px solid var(--blue-border); }
.ow-badge--grey    { background:var(--gray-100);     color:var(--gray-500); border:0.5px solid var(--gray-200); }

/* ── Utilities ────────────────────────────────────────────────── */
.mb-4 { margin-bottom:1.5rem; }
.mb-3 { margin-bottom:1rem; }
.mb-2 { margin-bottom:.5rem; }
.mt-2 { margin-top:.5rem; }
</style>
@endpush