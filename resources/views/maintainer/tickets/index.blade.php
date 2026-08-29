@extends('maintainer.layouts.app')

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="page-content-wrapper bg-white p-30 radius-20">

                <div class="tk-head">
                    <div>
                        <nav aria-label="breadcrumb">
                            <ol class="tk-crumb">
                                <li><a href="{{ route('maintainer.dashboard') }}">{{ __('Dashboard') }}</a></li>
                                <li aria-current="page">{{ $pageTitle }}</li>
                            </ol>
                        </nav>
                        <h2 class="tk-title">{{ __('Tickets') }}</h2>
                        <p class="tk-sub">{{ __('Support tickets raised by tenants on the properties you manage.') }}</p>
                    </div>
                </div>

                <div class="tk-grid">
                    @forelse ($tickets as $ticket)
                        @php $st = (int) $ticket->status; @endphp
                        <div class="tk-card">
                            <div class="tk-card__top">
                                <span class="tk-no">{{ __('Ticket') }} #{{ $ticket->ticket_no }}</span>
                                <span class="tk-badge tk-badge--{{ $st === TICKET_STATUS_OPEN ? 'open' : ($st === TICKET_STATUS_INPROGRESS ? 'prog' : ($st === TICKET_STATUS_RESOLVED ? 'ok' : ($st === TICKET_STATUS_REOPEN ? 'due' : 'muted'))) }}">
                                    {{ $st === TICKET_STATUS_OPEN ? __('Open') : ($st === TICKET_STATUS_INPROGRESS ? __('In progress') : ($st === TICKET_STATUS_RESOLVED ? __('Resolved') : ($st === TICKET_STATUS_REOPEN ? __('Reopened') : __('Closed')))) }}
                                </span>
                                <div class="dropdown tk-menu">
                                    <a class="dropdown-toggle dropdown-toggle-nocaret" href="#" data-bs-toggle="dropdown" aria-expanded="false"><i class="ri-more-2-fill"></i></a>
                                    <ul class="dropdown-menu {{ selectedLanguage()->rtl == 1 ? 'dropdown-menu-start' : 'dropdown-menu-end' }}">
                                        <li><a class="dropdown-item font-13 statusChange" data-url="{{ route('maintainer.ticket.status.change') }}" data-id="{{ $ticket->id }}" data-status="2" href="javascript:;">{{ __('In progress') }}</a></li>
                                        <li><a class="dropdown-item font-13 statusChange" data-url="{{ route('maintainer.ticket.status.change') }}" data-id="{{ $ticket->id }}" data-status="5" href="javascript:;">{{ __('Resolved') }}</a></li>
                                        <li><a class="dropdown-item font-13 statusChange" data-url="{{ route('maintainer.ticket.status.change') }}" data-id="{{ $ticket->id }}" data-status="3" href="javascript:;">{{ __('Close') }}</a></li>
                                    </ul>
                                </div>
                            </div>

                            <div class="tk-body">
                                <p class="tk-label">{{ __('Title') }}</p>
                                <p class="tk-val">{{ Str::limit($ticket->title, 40, '…') }}</p>
                                <p class="tk-label">{{ __('Details') }}</p>
                                <p class="tk-val tk-val--muted">{{ Str::limit(strip_tags($ticket->details), 90, '…') }}</p>

                                @if ($ticket->attachments->count())
                                    <div class="tk-attach">
                                        @foreach ($ticket->attachments->take(3) as $attachment)
                                            @if (in_array(pathinfo($attachment->file_name, PATHINFO_EXTENSION), imageExtensionList()))
                                                <a href="{{ $attachment->FileUrl }}" class="venobox tk-thumb" data-gall="attach{{ $attachment->id }}"><img src="{{ $attachment->FileUrl }}" alt=""></a>
                                            @else
                                                <a href="{{ $attachment->FileUrl }}" class="tk-file" download><i class="ri-attachment-2"></i></a>
                                            @endif
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            <a href="{{ route('maintainer.ticket.details', $ticket->id) }}" class="tk-btn {{ $st === TICKET_STATUS_CLOSE ? 'tk-btn--ghost' : '' }}">
                                {{ __('View details') }}
                            </a>
                        </div>
                    @empty
                        <div class="tk-empty">
                            <i class="ri-bookmark-line"></i>
                            <h3>{{ __('No tickets') }}</h3>
                            <p>{{ __('Tickets from tenants on your properties will appear here.') }}</p>
                        </div>
                    @endforelse
                </div>

            </div>
        </div>
    </div>
</div>

<style>
    .tk-head { margin-bottom:20px; }
    .tk-crumb { display:flex; gap:6px; list-style:none; padding:0; margin:0 0 8px; font-size:12px; color:#9ca3af; }
    .tk-crumb li:not(:last-child)::after { content:''; display:inline-block; width:5px; height:5px; border-right:1.5px solid #d1d5db; border-top:1.5px solid #d1d5db; transform:rotate(45deg); margin-left:6px; opacity:.6; }
    .tk-crumb a { color:#185FA5; text-decoration:none; font-weight:500; }
    .tk-title { font-size:22px; font-weight:600; color:#111827; margin:0 0 5px; }
    .tk-sub { font-size:13.5px; color:#6b7280; margin:0; }
    .tk-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(280px, 1fr)); gap:18px; }
    .tk-card { border:0.5px solid #e5e7eb; border-radius:16px; padding:18px; display:flex; flex-direction:column; }
    .tk-card__top { display:flex; align-items:center; gap:8px; padding-bottom:14px; border-bottom:0.5px solid #f1f5f9; margin-bottom:14px; }
    .tk-no { font-size:14px; font-weight:700; color:#111827; }
    .tk-badge { font-size:11px; font-weight:600; padding:3px 10px; border-radius:99px; }
    .tk-badge--open { background:#FEF3E7; color:#B45309; }
    .tk-badge--prog { background:#E6F1FB; color:#185FA5; }
    .tk-badge--ok { background:#E1F5EE; color:#0F6E56; }
    .tk-badge--due { background:#FBE9E7; color:#B42318; }
    .tk-badge--muted { background:#f3f4f6; color:#6b7280; }
    .tk-menu { margin-left:auto; }
    .tk-menu .dropdown-toggle { color:#9ca3af; font-size:18px; }
    .tk-body { flex:1; }
    .tk-label { font-size:10.5px; text-transform:uppercase; letter-spacing:.05em; color:#9ca3af; font-weight:600; margin:0 0 3px; }
    .tk-val { font-size:13px; color:#374151; margin:0 0 12px; }
    .tk-val--muted { color:#6b7280; line-height:1.5; }
    .tk-attach { display:flex; gap:8px; margin-bottom:6px; }
    .tk-thumb img { width:44px; height:44px; object-fit:cover; border-radius:8px; }
    .tk-file { width:44px; height:44px; border-radius:8px; background:#f3f4f6; color:#6b7280; display:flex; align-items:center; justify-content:center; font-size:18px; }
    .tk-btn { display:block; text-align:center; margin-top:14px; background:#185FA5; color:#fff; border-radius:10px; font-size:13px; font-weight:600; padding:10px; text-decoration:none; }
    .tk-btn, .tk-btn:hover { color:#fff !important; }
    .tk-btn:hover { background:#0F4A84; }
    .tk-btn--ghost { background:#f3f4f6; color:#374151; }
    .tk-btn--ghost, .tk-btn--ghost:hover { color:#374151 !important; }
    .tk-btn--ghost:hover { background:#e5e7eb; }
    .tk-empty { grid-column:1/-1; text-align:center; padding:48px 20px; color:#6b7280; }
    .tk-empty i { font-size:44px; color:#cbd5e1; }
    .tk-empty h3 { font-size:17px; color:#111827; margin:12px 0 6px; }
</style>
@endsection
@push('script')
    <script src="{{ asset('assets/js/custom/ticket.js') }}"></script>
@endpush
