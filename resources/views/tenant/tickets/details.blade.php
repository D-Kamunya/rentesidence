@extends('tenant.layouts.app')

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">

            <style>
                /* ── Design tokens ── */
                :root {
                    --blue:          #185FA5;
                    --blue-hover:    #0F4A84;
                    --blue-light:    #E6F1FB;
                    --blue-border:   #B5D4F4;
                    --blue-faint:    #185ea56e;
                    --blue-ghost:    #185ea51c;
                    --green:         #1D9E75;
                    --green-dark:    #0F6E56;
                    --green-light:   #E1F5EE;
                    --amber:         #854F0B;
                    --amber-light:   #FAEEDA;
                    --amber-border:  #F5D9A8;
                    --red:           #993C1D;
                    --red-light:     #FAECE7;
                    --purple:        #534AB7;
                    --gray-900:      #111827;
                    --gray-800:      #1f2937;
                    --gray-700:      #374151;
                    --gray-500:      #6b7280;
                    --gray-400:      #9ca3af;
                    --gray-200:      #e5e7eb;
                    --gray-100:      #f3f4f6;
                    --gray-50:       #fafafa;
                    --white:         #ffffff;
                }

                /* ── Page shell ── */
                .tkd-page-wrapper {
                    background: var(--white);
                    border-radius: 16px;
                    padding: 28px 28px 36px;
                }

                /* ── Page header ── */
                .tkd-page-header {
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    padding-bottom: 18px;
                    border-bottom: 0.5px solid var(--gray-200);
                    margin-bottom: 28px;
                    flex-wrap: wrap;
                    gap: 12px;
                }
                .tkd-page-title {
                    font-size: 22px;
                    font-weight: 500;
                    color: var(--gray-900);
                    margin: 0;
                }
                .tkd-breadcrumb {
                    display: flex; align-items: center; gap: 6px;
                    list-style: none; margin: 0; padding: 0;
                    font-size: 12px; color: var(--gray-400);
                }
                .tkd-breadcrumb a {
                    color: var(--blue); font-weight: 500; text-decoration: none;
                }
                .tkd-breadcrumb a:hover { color: var(--blue-hover); }
                .tkd-breadcrumb svg {
                    width: 8px; height: 8px;
                    stroke: var(--gray-400); fill: none;
                    stroke-width: 2; stroke-linecap: round; stroke-linejoin: round;
                }

                /* ── Two-column layout ── */
                .tkd-layout {
                    display: grid;
                    grid-template-columns: 1fr 300px;
                    gap: 20px;
                    align-items: start;
                }
                @media (max-width: 991px) {
                    .tkd-layout { grid-template-columns: 1fr; }
                    .tkd-sidebar { order: -1; }
                }

                /* ── Shared card ── */
                .tkd-card {
                    background: var(--white);
                    border: 0.5px solid var(--blue-faint);
                    border-radius: 12px;
                    overflow: hidden;
                    box-shadow:
                        0 4px 12px rgba(0,0,0,0.04),
                        0 0 0 1px rgba(24,95,165,0.05),
                        0 6px 18px rgba(24,95,165,0.06);
                    margin-bottom: 16px;
                }
                .tkd-card:last-child { margin-bottom: 0; }

                .tkd-card-head {
                    padding: 16px 20px 14px;
                    background: var(--gray-50);
                    border-bottom: 0.5px solid var(--gray-200);
                    display: flex;
                    align-items: center;
                    gap: 10px;
                }
                .tkd-card-head-title {
                    font-size: 14px;
                    font-weight: 600;
                    color: var(--gray-900);
                    margin: 0;
                    flex: 1;
                }
                .tkd-card-head-sub {
                    font-size: 12px;
                    font-weight: 500;
                    color: var(--gray-500);
                    font-family: monospace;
                    background: var(--blue-light);
                    border: 0.5px solid var(--blue-border);
                    border-radius: 99px;
                    padding: 2px 9px;
                }

                .tkd-card-body {
                    padding: 20px;
                }

                /* ── Opener author strip ── */
                .tkd-author-strip {
                    display: flex;
                    align-items: center;
                    gap: 10px;
                    padding-bottom: 14px;
                    border-bottom: 0.5px solid var(--gray-100);
                    margin-bottom: 16px;
                }
                .tkd-author-avatar {
                    width: 38px; height: 38px;
                    border-radius: 10px;
                    background: var(--blue-light);
                    border: 2px solid var(--blue-border);
                    display: flex; align-items: center; justify-content: center;
                    font-size: 14px;
                    font-weight: 600;
                    color: var(--blue);
                    flex-shrink: 0;
                }
                .tkd-author-name {
                    font-size: 14px;
                    font-weight: 600;
                    color: var(--gray-800);
                    margin: 0;
                    line-height: 1.3;
                }
                .tkd-author-role {
                    font-size: 11px;
                    font-weight: 500;
                    color: var(--blue);
                }

                /* ── Section label / prose ── */
                .tkd-section-label {
                    font-size: 10px;
                    font-weight: 500;
                    text-transform: uppercase;
                    letter-spacing: .07em;
                    color: var(--gray-400);
                    margin-bottom: 6px;
                }
                .tkd-prose {
                    font-size: 13.5px;
                    color: var(--gray-700);
                    line-height: 1.65;
                    margin: 0;
                }
                .tkd-section { margin-bottom: 18px; }
                .tkd-section:last-child { margin-bottom: 0; }

                /* ── Attachments ── */
                .tkd-attach-gallery {
                    display: flex;
                    flex-wrap: wrap;
                    gap: 8px;
                    margin-top: 6px;
                }
                .tkd-attach-gallery .tickets-attachment-item img {
                    width: 52px; height: 52px;
                    object-fit: cover;
                    border-radius: 8px;
                    border: 0.5px solid var(--gray-200);
                    transition: opacity .15s;
                }
                .tkd-attach-gallery .tickets-attachment-item:hover img { opacity: .85; }
                .tkd-attach-gallery a.file-link {
                    font-size: 12px; color: var(--blue);
                    background: var(--blue-light);
                    border: 0.5px solid var(--blue-border);
                    border-radius: 6px;
                    padding: 4px 10px;
                    text-decoration: none;
                    display: inline-flex; align-items: center; gap: 5px;
                }
                .tkd-attach-gallery a.file-link:hover { background: var(--blue-border); }

                /* ── Replies ── */
                .tkd-reply-item {
                    border: 0.5px solid var(--gray-100);
                    border-radius: 10px;
                    padding: 16px;
                    margin-bottom: 12px;
                    background: var(--gray-50);
                }
                .tkd-reply-item:last-child { margin-bottom: 0; }

                .tkd-reply-author-strip {
                    display: flex;
                    align-items: center;
                    gap: 8px;
                    padding-bottom: 12px;
                    border-bottom: 0.5px solid var(--gray-200);
                    margin-bottom: 12px;
                }
                .tkd-reply-avatar {
                    width: 32px; height: 32px;
                    border-radius: 8px;
                    background: var(--blue-light);
                    border: 1.5px solid var(--blue-border);
                    display: flex; align-items: center; justify-content: center;
                    font-size: 12px; font-weight: 600; color: var(--blue);
                    flex-shrink: 0;
                }
                .tkd-reply-name {
                    font-size: 13px; font-weight: 600; color: var(--gray-800); margin: 0;
                }
                .tkd-reply-role {
                    font-size: 11px; font-weight: 500; color: var(--blue);
                }
                .tkd-reply-time {
                    font-size: 11px; color: var(--gray-400); margin-left: auto; white-space: nowrap;
                }

                /* ── Write reply form ── */
                .tkd-form-label {
                    font-size: 10px; font-weight: 500;
                    text-transform: uppercase; letter-spacing: .07em;
                    color: var(--gray-400); display: block; margin-bottom: 5px;
                }
                .tkd-form-control {
                    border: 0.5px solid var(--gray-200);
                    border-radius: 7px;
                    font-size: 13px;
                    color: var(--gray-700);
                    padding: 9px 12px;
                    width: 100%;
                    transition: all .15s;
                    font-family: inherit;
                    resize: vertical;
                    min-height: 100px;
                }
                .tkd-form-control:focus {
                    outline: none;
                    border-color: var(--blue);
                    box-shadow: 0 0 0 3px rgba(24,95,165,.1);
                }
                .tkd-form-control::placeholder { color: var(--gray-400); }
                .tkd-mb-field { margin-bottom: 16px; }

                .tkd-btn-submit {
                    display: inline-flex; align-items: center; gap: 6px;
                    background: var(--blue); color: var(--white);
                    font-size: 12px; font-weight: 500;
                    padding: 8px 18px; border-radius: 7px; border: none;
                    cursor: pointer; transition: all .13s;
                }
                .tkd-btn-submit:hover {
                    background: var(--blue-hover); transform: translateY(-1px);
                }
                .tkd-btn-submit svg {
                    width: 13px; height: 13px; stroke: currentColor; fill: none;
                    stroke-width: 2; stroke-linecap: round; stroke-linejoin: round;
                }

                /* ── Sidebar: ticket info ── */
                .tkd-info-row {
                    padding: 11px 0;
                    border-bottom: 0.5px solid var(--gray-100);
                    display: flex;
                    flex-direction: column;
                    gap: 3px;
                }
                .tkd-info-row:first-child { padding-top: 0; }
                .tkd-info-row:last-child { border-bottom: none; padding-bottom: 0; }
                .tkd-info-key {
                    font-size: 10px; font-weight: 500;
                    text-transform: uppercase; letter-spacing: .07em;
                    color: var(--gray-400);
                }
                .tkd-info-val {
                    font-size: 13px; font-weight: 500;
                    color: var(--gray-700);
                }

                /* Status badges (sidebar) */
                .tkd-badge {
                    display: inline-flex; align-items: center; gap: 4px;
                    font-size: 11px; font-weight: 500;
                    padding: 3px 9px; border-radius: 99px; white-space: nowrap;
                    width: fit-content;
                }
                .tkd-badge-dot {
                    width: 6px; height: 6px; border-radius: 50%; display: inline-block; flex-shrink: 0;
                }
                .tkd-badge-open     { background: var(--amber-light); color: var(--amber); border: 0.5px solid var(--amber-border); }
                .tkd-badge-open     .tkd-badge-dot { background: var(--amber); }
                .tkd-badge-inprog   { background: var(--blue-light);  color: #0C447C;       border: 0.5px solid var(--blue-border); }
                .tkd-badge-inprog   .tkd-badge-dot { background: var(--blue); }
                .tkd-badge-reopen   { background: var(--red-light);   color: var(--red); }
                .tkd-badge-reopen   .tkd-badge-dot { background: var(--red); }
                .tkd-badge-resolved { background: var(--green-light); color: var(--green-dark); }
                .tkd-badge-resolved .tkd-badge-dot { background: var(--green); }
                .tkd-badge-close    { background: var(--gray-100);    color: var(--gray-500); border: 0.5px solid var(--gray-200); }
                .tkd-badge-close    .tkd-badge-dot { background: var(--gray-400); }

                /* ── Back button ── */
                .tkd-btn-back {
                    display: inline-flex;
                    align-items: center;
                    gap: 6px;
                    background: var(--gray-100);
                    color: var(--gray-700);
                    font-size: 12px;
                    font-weight: 500;
                    padding: 7px 14px;
                    border-radius: 7px;
                    border: 0.5px solid var(--gray-200);
                    text-decoration: none;
                    transition: all .13s;
                    white-space: nowrap;
                }
                .tkd-btn-back:hover {
                    background: var(--blue-ghost);
                    border-color: var(--blue-border);
                    color: var(--blue);
                }
                .tkd-btn-back svg {
                    width: 13px; height: 13px;
                    stroke: currentColor; fill: none;
                    stroke-width: 2; stroke-linecap: round; stroke-linejoin: round;
                }
            </style>

            <div class="tkd-page-wrapper">

                {{-- Page header --}}
                <div class="tkd-page-header">
                    <div style="display:flex; align-items:center; gap:12px;">
                        <a href="{{ route('tenant.ticket.index') }}" class="tkd-btn-back" title="{{ __('Back to Tickets') }}">
                            <svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
                            {{ __('Back') }}
                        </a>
                        <h3 class="tkd-page-title">{{ $pageTitle }}</h3>
                    </div>
                    <ol class="tkd-breadcrumb">
                        <li><a href="{{ route('tenant.dashboard') }}">{{ __('Dashboard') }}</a></li>
                        <li><svg viewBox="0 0 8 8"><polyline points="2 1 6 4 2 7"/></svg></li>
                        <li><a href="{{ route('tenant.ticket.index') }}">{{ __('Tickets') }}</a></li>
                        <li><svg viewBox="0 0 8 8"><polyline points="2 1 6 4 2 7"/></svg></li>
                        <li>{{ $pageTitle }}</li>
                    </ol>
                </div>

                {{-- Two-column layout --}}
                <div class="tkd-layout">

                    {{-- ── LEFT: main content ── --}}
                    <div class="tkd-main">

                        {{-- Original message --}}
                        <div class="tkd-card">
                            <div class="tkd-card-head">
                                <h4 class="tkd-card-head-title">{{ __('Ticket Details') }}</h4>
                            </div>
                            <div class="tkd-card-body">
                                <div class="tkd-author-strip">
                                    <div class="tkd-author-avatar">
                                        {{ mb_substr($ticket->user->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <p class="tkd-author-name">{{ $ticket->user->name }}</p>
                                        <span class="tkd-author-role">
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

                                <div class="tkd-section">
                                    <p class="tkd-section-label">{{ __('Details') }}</p>
                                    <p class="tkd-prose">{{ $ticket->details }}</p>
                                </div>

                                @if ($ticket->attachments->count() > 0)
                                    <div class="tkd-section">
                                        <p class="tkd-section-label">{{ __('Attachments') }}</p>
                                        <div class="tkd-attach-gallery">
                                            @foreach ($ticket->attachments as $attachment)
                                                @if (in_array(pathinfo($attachment->file_name, PATHINFO_EXTENSION), imageExtensionList()))
                                                    <a href="{{ $attachment->FileUrl }}" class="venobox tickets-attachment-item"
                                                       data-gall="attach{{ $attachment->id }}">
                                                        <img src="{{ $attachment->FileUrl }}" alt="">
                                                    </a>
                                                @else
                                                    <a href="{{ $attachment->FileUrl }}" class="file-link" download>
                                                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                                        {{ $attachment->file_name }}
                                                    </a>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Replies --}}
                        @if (count($replies) > 0)
                            <div class="tkd-card">
                                <div class="tkd-card-head">
                                    <h4 class="tkd-card-head-title">{{ __('Replies') }}</h4>
                                    <span class="tkd-card-head-sub">{{ count($replies) }}</span>
                                </div>
                                <div class="tkd-card-body">
                                    @foreach ($replies as $reply)
                                        <div class="tkd-reply-item">
                                            <div class="tkd-reply-author-strip">
                                                <div class="tkd-reply-avatar">
                                                    {{ mb_substr($reply->first_name, 0, 1) }}
                                                </div>
                                                <div>
                                                    <p class="tkd-reply-name">
                                                        @if ($reply->user_id == auth()->id())
                                                            {{ __('You') }} ·
                                                        @endif
                                                        {{ $reply->first_name }} {{ $reply->last_name }}
                                                    </p>
                                                    <span class="tkd-reply-role">
                                                        @if ($reply->role == USER_ROLE_OWNER)
                                                            {{ __('Owner') }}
                                                        @elseif ($reply->role == USER_ROLE_TENANT)
                                                            {{ __('Tenant') }}
                                                        @elseif ($reply->role == USER_ROLE_MAINTAINER)
                                                            {{ __('Maintainer') }}
                                                        @endif
                                                    </span>
                                                </div>
                                                <span class="tkd-reply-time">{{ $reply->created_at->diffForHumans() }}</span>
                                            </div>

                                            <p class="tkd-prose">{{ $reply->reply }}</p>

                                            @if (count($reply->attachments) > 0)
                                                <div class="tkd-section" style="margin-top:12px; margin-bottom:0;">
                                                    <p class="tkd-section-label">{{ __('Attachments') }}</p>
                                                    <div class="tkd-attach-gallery">
                                                        @foreach ($reply->attachments as $attachment)
                                                            @if (in_array(pathinfo($attachment->file_name, PATHINFO_EXTENSION), imageExtensionList()))
                                                                <a href="{{ $attachment->FileUrl }}" class="venobox tickets-attachment-item"
                                                                   data-gall="gallery01">
                                                                    <img src="{{ $attachment->FileUrl }}" alt="">
                                                                </a>
                                                            @else
                                                                <a href="{{ $attachment->FileUrl }}" class="file-link" download>
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

                        {{-- Write reply --}}
                        <div class="tkd-card">
                            <div class="tkd-card-head">
                                <h4 class="tkd-card-head-title">{{ __('Write a Reply') }}</h4>
                            </div>
                            <div class="tkd-card-body">
                                <form class="ajax" action="{{ route('tenant.ticket.reply') }}" method="POST"
                                      data-handler="getShowMessage">
                                    <input type="hidden" name="ticket_id" value="{{ $ticket->id }}">

                                    <div class="tkd-mb-field">
                                        <label class="tkd-form-label">{{ __('Reply') }}</label>
                                        <textarea class="tkd-form-control" name="reply"
                                                  placeholder="{{ __('Write your reply here…') }}"></textarea>
                                    </div>

                                    <div class="tkd-mb-field">
                                        <label class="tkd-form-label">{{ __('Upload Attachments') }}</label>
                                        <input type="file" id="attachments" name="attachments[]"
                                               class="dropify" data-height="160" multiple />
                                    </div>

                                    <button type="submit" class="tkd-btn-submit">
                                        <svg viewBox="0 0 24 24"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                                        {{ __('Submit Reply') }}
                                    </button>
                                </form>
                            </div>
                        </div>

                    </div>{{-- /tkd-main --}}

                    {{-- ── RIGHT: sidebar ── --}}
                    <div class="tkd-sidebar">
                        <div class="tkd-card">
                            <div class="tkd-card-head">
                                <h4 class="tkd-card-head-title">{{ __('Ticket Info') }}</h4>
                                <span class="tkd-card-head-sub">#{{ $ticket->ticket_no }}</span>
                            </div>
                            <div class="tkd-card-body" style="padding:16px 20px;">

                                <div class="tkd-info-row">
                                    <span class="tkd-info-key">{{ __('Title') }}</span>
                                    <span class="tkd-info-val">{{ $ticket->title }}</span>
                                </div>

                                <div class="tkd-info-row">
                                    <span class="tkd-info-key">{{ __('Topic') }}</span>
                                    <span class="tkd-info-val">{{ $ticket->topic->name }}</span>
                                </div>

                                <div class="tkd-info-row">
                                    <span class="tkd-info-key">{{ __('Property') }}</span>
                                    <span class="tkd-info-val">{{ $ticket->property->name }}</span>
                                </div>

                                <div class="tkd-info-row">
                                    <span class="tkd-info-key">{{ __('Unit') }}</span>
                                    <span class="tkd-info-val">{{ $ticket->unit->unit_name }}</span>
                                </div>

                                <div class="tkd-info-row">
                                    <span class="tkd-info-key">{{ __('Status') }}</span>
                                    <div style="margin-top:2px;">
                                        @if ($ticket->status == TICKET_STATUS_OPEN)
                                            <span class="tkd-badge tkd-badge-open"><span class="tkd-badge-dot"></span>{{ __('Open') }}</span>
                                        @elseif ($ticket->status == TICKET_STATUS_INPROGRESS)
                                            <span class="tkd-badge tkd-badge-inprog"><span class="tkd-badge-dot"></span>{{ __('In Progress') }}</span>
                                        @elseif ($ticket->status == TICKET_STATUS_REOPEN)
                                            <span class="tkd-badge tkd-badge-reopen"><span class="tkd-badge-dot"></span>{{ __('Reopen') }}</span>
                                        @elseif ($ticket->status == TICKET_STATUS_RESOLVED)
                                            <span class="tkd-badge tkd-badge-resolved"><span class="tkd-badge-dot"></span>{{ __('Resolved') }}</span>
                                        @else
                                            <span class="tkd-badge tkd-badge-close"><span class="tkd-badge-dot"></span>{{ __('Closed') }}</span>
                                        @endif
                                    </div>
                                </div>

                                <div class="tkd-info-row">
                                    <span class="tkd-info-key">{{ __('Opened') }}</span>
                                    <span class="tkd-info-val">{{ $ticket->created_at->format('Y-m-d') }}</span>
                                </div>

                            </div>
                        </div>
                    </div>{{-- /tkd-sidebar --}}

                </div>{{-- /tkd-layout --}}

            </div>{{-- /tkd-page-wrapper --}}

        </div>
    </div>
</div>
@endsection

@push('script')
    <script src="{{ asset('assets/js/custom/ticket.js') }}"></script>
@endpush