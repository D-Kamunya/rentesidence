@extends('maintainer.layouts.app')

@section('content')
@php
    $st = (int) $ticket->status;
    $badge = $st === TICKET_STATUS_OPEN ? ['open', __('Open')]
        : ($st === TICKET_STATUS_INPROGRESS ? ['prog', __('In progress')]
        : ($st === TICKET_STATUS_RESOLVED ? ['ok', __('Resolved')]
        : ($st === TICKET_STATUS_REOPEN ? ['due', __('Reopened')] : ['muted', __('Closed')])));
    $roleLabel = fn ($role) => $role == USER_ROLE_OWNER ? __('Owner') : ($role == USER_ROLE_TENANT ? __('Tenant') : ($role == USER_ROLE_MAINTAINER ? __('Maintainer') : ''));
@endphp

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="page-content-wrapper bg-white p-30 radius-20">

                <div class="td-head">
                    <nav aria-label="breadcrumb">
                        <ol class="td-crumb">
                            <li><a href="{{ route('maintainer.dashboard') }}">{{ __('Dashboard') }}</a></li>
                            <li><a href="{{ route('maintainer.ticket.index') }}">{{ __('Tickets') }}</a></li>
                            <li aria-current="page">#{{ $ticket->ticket_no }}</li>
                        </ol>
                    </nav>
                    <h2 class="td-title">{{ __('Ticket') }} #{{ $ticket->ticket_no }}</h2>
                </div>

                <div class="td-grid">
                    {{-- Conversation --}}
                    <div class="td-main">
                        <div class="td-msg">
                            <div class="td-msg__head">
                                <span class="td-msg__who">{{ $ticket->user->name }}</span>
                                <span class="td-msg__role">{{ $roleLabel($ticket->user->role) }}</span>
                            </div>
                            <p class="td-msg__body">{{ $ticket->details }}</p>
                            @if ($ticket->attachments->count())
                                <div class="td-attach">
                                    @foreach ($ticket->attachments as $attachment)
                                        @if (in_array(pathinfo($attachment->file_name, PATHINFO_EXTENSION), imageExtensionList()))
                                            <a href="{{ $attachment->FileUrl }}" class="venobox td-thumb" data-gall="attach{{ $attachment->id }}"><img src="{{ $attachment->FileUrl }}" alt=""></a>
                                        @else
                                            <a href="{{ $attachment->FileUrl }}" class="td-file" download><i class="ri-attachment-2"></i> {{ Str::limit($attachment->file_name, 18) }}</a>
                                        @endif
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        @foreach ($replies as $reply)
                            <div class="td-msg td-msg--reply {{ $reply->user_id == auth()->id() ? 'td-msg--mine' : '' }}">
                                <div class="td-msg__head">
                                    <span class="td-msg__who">{{ $reply->user_id == auth()->id() ? __('You') : trim($reply->first_name . ' ' . $reply->last_name) }}</span>
                                    <span class="td-msg__role">{{ $roleLabel($reply->role) }}</span>
                                </div>
                                <p class="td-msg__body">{{ $reply->reply }}</p>
                                @if (count($reply->attachments) > 0)
                                    <div class="td-attach">
                                        @foreach ($reply->attachments as $attachment)
                                            @if (in_array(pathinfo($attachment->file_name, PATHINFO_EXTENSION), imageExtensionList()))
                                                <a href="{{ $attachment->FileUrl }}" class="venobox td-thumb" data-gall="gallery01"><img src="{{ $attachment->FileUrl }}" alt=""></a>
                                            @else
                                                <a href="{{ $attachment->FileUrl }}" class="td-file" download><i class="ri-attachment-2"></i> {{ Str::limit($attachment->file_name, 18) }}</a>
                                            @endif
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endforeach

                        {{-- Reply --}}
                        <div class="td-reply">
                            <h4 class="td-reply__t">{{ __('Write a reply') }}</h4>
                            <form class="ajax" action="{{ route('maintainer.ticket.reply') }}" method="POST" data-handler="getShowMessage">
                                <input type="hidden" name="ticket_id" value="{{ $ticket->id }}">
                                <textarea class="form-control td-textarea" placeholder="{{ __('Type your reply…') }}" name="reply"></textarea>
                                <label class="td-reply__lbl">{{ __('Attachments') }}</label>
                                <input type="file" id="attachments" name="attachments[]" class="dropify" data-height="180" multiple />
                                <div class="td-reply__actions">
                                    <button type="submit" class="td-btn">{{ __('Send reply') }}</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    {{-- Info sidebar --}}
                    <div class="td-side">
                        <div class="td-side__head">
                            <span>{{ __('Ticket info') }}</span>
                            <span class="td-badge td-badge--{{ $badge[0] }}">{{ $badge[1] }}</span>
                        </div>
                        <div class="td-info"><span class="td-info__k">{{ __('Title') }}</span><span class="td-info__v">{{ $ticket->title }}</span></div>
                        <div class="td-info"><span class="td-info__k">{{ __('Topic') }}</span><span class="td-info__v">{{ optional($ticket->topic)->name ?: '—' }}</span></div>
                        <div class="td-info"><span class="td-info__k">{{ __('Property') }}</span><span class="td-info__v">{{ optional($ticket->property)->name ?: '—' }}</span></div>
                        <div class="td-info"><span class="td-info__k">{{ __('Unit') }}</span><span class="td-info__v">{{ optional($ticket->unit)->unit_name ?: '—' }}</span></div>
                        <div class="td-info"><span class="td-info__k">{{ __('Opened') }}</span><span class="td-info__v">{{ $ticket->created_at->format('Y-m-d') }}</span></div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<style>
    .td-head { margin-bottom:20px; }
    .td-crumb { display:flex; gap:6px; list-style:none; padding:0; margin:0 0 8px; font-size:12px; color:#9ca3af; }
    .td-crumb li:not(:last-child)::after { content:''; display:inline-block; width:5px; height:5px; border-right:1.5px solid #d1d5db; border-top:1.5px solid #d1d5db; transform:rotate(45deg); margin-left:6px; opacity:.6; }
    .td-crumb a { color:#185FA5; text-decoration:none; font-weight:500; }
    .td-title { font-size:22px; font-weight:600; color:#111827; margin:0; }
    .td-grid { display:grid; grid-template-columns:1fr 300px; gap:20px; align-items:start; }
    @media (max-width:900px){ .td-grid { grid-template-columns:1fr; } }
    .td-main { display:flex; flex-direction:column; gap:16px; }
    .td-msg { border:0.5px solid #e5e7eb; border-radius:14px; padding:18px; }
    .td-msg--reply { background:#FAFBFC; }
    .td-msg--mine { background:#F5F9FD; border-color:#d7e3f2; }
    .td-msg__head { display:flex; align-items:center; gap:8px; padding-bottom:12px; border-bottom:0.5px solid #f1f5f9; margin-bottom:12px; }
    .td-msg__who { font-size:14px; font-weight:700; color:#111827; }
    .td-msg__role { font-size:11px; font-weight:600; color:#6b7280; background:#f3f4f6; padding:2px 9px; border-radius:99px; }
    .td-msg__body { font-size:13.5px; color:#374151; line-height:1.6; margin:0; white-space:pre-wrap; }
    .td-attach { display:flex; gap:8px; flex-wrap:wrap; margin-top:12px; }
    .td-thumb img { width:52px; height:52px; object-fit:cover; border-radius:8px; }
    .td-file { display:inline-flex; align-items:center; gap:6px; font-size:12px; color:#185FA5; text-decoration:none; background:#f3f4f6; padding:8px 12px; border-radius:8px; }
    .td-reply { border:0.5px solid #e5e7eb; border-radius:14px; padding:18px; }
    .td-reply__t { font-size:15px; font-weight:600; color:#111827; margin:0 0 12px; }
    .td-textarea { width:100%; border:0.5px solid #d1d5db; border-radius:10px; padding:11px 14px; font-size:13.5px; outline:none; min-height:90px; resize:vertical; }
    .td-textarea:focus { border-color:#185FA5; box-shadow:0 0 0 3px rgba(24,95,165,.1); }
    .td-reply__lbl { display:block; font-size:12px; font-weight:600; color:#374151; margin:14px 0 6px; }
    .td-reply__actions { margin-top:14px; }
    .td-btn { background:#185FA5; color:#fff; border:none; border-radius:10px; font-size:13.5px; font-weight:600; padding:11px 24px; cursor:pointer; }
    .td-btn:hover { background:#0F4A84; }
    .td-side { border:0.5px solid #e5e7eb; border-radius:16px; padding:20px; }
    .td-side__head { display:flex; align-items:center; justify-content:space-between; padding-bottom:12px; border-bottom:0.5px solid #f1f5f9; margin-bottom:6px; font-size:14px; font-weight:600; color:#111827; }
    .td-badge { font-size:11px; font-weight:600; padding:3px 10px; border-radius:99px; }
    .td-badge--open { background:#FEF3E7; color:#B45309; }
    .td-badge--prog { background:#E6F1FB; color:#185FA5; }
    .td-badge--ok { background:#E1F5EE; color:#0F6E56; }
    .td-badge--due { background:#FBE9E7; color:#B42318; }
    .td-badge--muted { background:#f3f4f6; color:#6b7280; }
    .td-info { padding:11px 0; border-bottom:0.5px solid #f1f5f9; }
    .td-info:last-child { border-bottom:0; }
    .td-info__k { display:block; font-size:11px; text-transform:uppercase; letter-spacing:.05em; color:#9ca3af; font-weight:600; margin-bottom:3px; }
    .td-info__v { font-size:13.5px; color:#374151; }
</style>
@endsection

@push('script')
    <script src="{{ asset('assets/js/custom/ticket.js') }}"></script>
@endpush
