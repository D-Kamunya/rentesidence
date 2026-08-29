@extends('maintainer.layouts.app')

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="page-content-wrapper bg-white p-30 radius-20">

                <div class="md-head">
                    <div>
                        <h2 class="md-title">{{ __('Welcome back') }}, {{ auth()->user()->first_name ?: auth()->user()->name }} 👋</h2>
                        <p class="md-sub">{{ __('Here\'s what\'s happening across the properties you look after.') }}</p>
                    </div>
                </div>

                {{-- Stat cards --}}
                <div class="md-stats">
                    <div class="md-stat">
                        <div class="md-stat__ic md-stat__ic--blue"><i class="ri-building-line"></i></div>
                        <div><span class="md-stat__n">{{ count($properties) }}</span><span class="md-stat__l">{{ __('Properties') }}</span></div>
                    </div>
                    <div class="md-stat">
                        <div class="md-stat__ic md-stat__ic--amber"><i class="ri-error-warning-line"></i></div>
                        <div><span class="md-stat__n">{{ number_format($totalOpenTickets) }}</span><span class="md-stat__l">{{ __('Open tickets') }}</span></div>
                    </div>
                    <div class="md-stat">
                        <div class="md-stat__ic md-stat__ic--green"><i class="ri-checkbox-circle-line"></i></div>
                        <div><span class="md-stat__n">{{ number_format($totalResolvedTickets) }}</span><span class="md-stat__l">{{ __('Resolved') }}</span></div>
                    </div>
                    <div class="md-stat">
                        <div class="md-stat__ic md-stat__ic--gray"><i class="ri-archive-line"></i></div>
                        <div><span class="md-stat__n">{{ number_format($totalCloseTickets) }}</span><span class="md-stat__l">{{ __('Closed') }}</span></div>
                    </div>
                </div>

                {{-- Quick actions --}}
                <div class="md-quick">
                    <a href="{{ route('maintainer.rent.index') }}" class="md-qa"><i class="ri-money-dollar-circle-line"></i> {{ __('Rent & Payments') }}</a>
                    <a href="{{ route('maintainer.dispatch.index') }}" class="md-qa"><i class="ri-truck-line"></i> {{ __('Dispatch') }}</a>
                    <a href="{{ route('maintainer.maintenance-request.index') }}" class="md-qa"><i class="ri-tools-line"></i> {{ __('Maintenance') }}</a>
                    <a href="{{ route('maintainer.ticket.index') }}" class="md-qa"><i class="ri-bookmark-line"></i> {{ __('Tickets') }}</a>
                </div>

                <div class="md-grid">
                    {{-- Recent tickets --}}
                    <div class="md-panel">
                        <div class="md-panel__head">
                            <h3>{{ __('Recent tickets') }}</h3>
                            <a href="{{ route('maintainer.ticket.index') }}">{{ __('View all') }}</a>
                        </div>
                        @forelse ($tickets as $ticket)
                            <a href="{{ route('maintainer.ticket.details', $ticket->id) }}" class="md-row">
                                <span class="md-row__main">{{ \Illuminate\Support\Str::limit($ticket->title ?: ($ticket->ticket_no ?: __('Ticket #') . $ticket->id), 48) }}</span>
                                @php $st = (int) $ticket->status; @endphp
                                <span class="md-pill md-pill--{{ $st === TICKET_STATUS_OPEN ? 'open' : ($st === TICKET_STATUS_RESOLVED ? 'ok' : 'muted') }}">
                                    {{ $st === TICKET_STATUS_OPEN ? __('Open') : ($st === TICKET_STATUS_RESOLVED ? __('Resolved') : __('Closed')) }}
                                </span>
                            </a>
                        @empty
                            <p class="md-empty">{{ __('No tickets yet.') }}</p>
                        @endforelse
                    </div>

                    {{-- Active notices --}}
                    <div class="md-panel">
                        <div class="md-panel__head"><h3>{{ __('Notice board') }}</h3></div>
                        @forelse ($notices as $notice)
                            <div class="md-notice">
                                <i class="ri-megaphone-line"></i>
                                <div>
                                    <span class="md-notice__t">{{ $notice->title ?? __('Notice') }}</span>
                                    @if (!empty($notice->details))<span class="md-notice__d">{{ \Illuminate\Support\Str::limit(strip_tags($notice->details), 90) }}</span>@endif
                                </div>
                            </div>
                        @empty
                            <p class="md-empty">{{ __('No active notices.') }}</p>
                        @endforelse
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<style>
    .md-head { margin-bottom:22px; }
    .md-title { font-size:23px; font-weight:600; color:#111827; margin:0 0 5px; }
    .md-sub { font-size:13.5px; color:#6b7280; margin:0; }
    .md-stats { display:grid; grid-template-columns:repeat(4,1fr); gap:16px; margin-bottom:20px; }
    @media (max-width:900px){ .md-stats { grid-template-columns:repeat(2,1fr); } }
    @media (max-width:520px){ .md-stats { grid-template-columns:1fr; } }
    .md-stat { display:flex; align-items:center; gap:14px; border:0.5px solid #e5e7eb; border-radius:14px; padding:18px; }
    .md-stat__ic { width:46px; height:46px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:22px; flex:none; }
    .md-stat__ic--blue { background:#E6F1FB; color:#185FA5; }
    .md-stat__ic--amber { background:#FEF3E7; color:#B45309; }
    .md-stat__ic--green { background:#E1F5EE; color:#0F6E56; }
    .md-stat__ic--gray { background:#f3f4f6; color:#6b7280; }
    .md-stat__n { display:block; font-size:24px; font-weight:800; color:#111827; line-height:1.1; }
    .md-stat__l { font-size:12px; color:#6b7280; }
    .md-quick { display:flex; gap:12px; flex-wrap:wrap; margin-bottom:24px; }
    .md-qa { display:inline-flex; align-items:center; gap:8px; background:#F5F9FD; border:0.5px solid #d7e3f2; border-radius:10px; padding:11px 18px; font-size:13.5px; font-weight:600; color:#185FA5; text-decoration:none; }
    .md-qa:hover { background:#185FA5; color:#fff !important; }
    .md-grid { display:grid; grid-template-columns:1fr 1fr; gap:20px; }
    @media (max-width:800px){ .md-grid { grid-template-columns:1fr; } }
    .md-panel { border:0.5px solid #e5e7eb; border-radius:16px; padding:20px; }
    .md-panel__head { display:flex; align-items:center; justify-content:space-between; margin-bottom:12px; }
    .md-panel__head h3 { font-size:15px; font-weight:600; color:#111827; margin:0; }
    .md-panel__head a { font-size:12.5px; color:#185FA5; text-decoration:none; font-weight:500; }
    .md-row { display:flex; align-items:center; justify-content:space-between; gap:12px; padding:11px 0; border-bottom:0.5px solid #f1f5f9; text-decoration:none; }
    .md-row:last-child { border-bottom:0; }
    .md-row__main { font-size:13px; color:#374151; }
    .md-pill { font-size:11px; font-weight:600; padding:3px 10px; border-radius:99px; flex:none; }
    .md-pill--open { background:#FEF3E7; color:#B45309; }
    .md-pill--ok { background:#E1F5EE; color:#0F6E56; }
    .md-pill--muted { background:#f3f4f6; color:#6b7280; }
    .md-notice { display:flex; gap:10px; align-items:flex-start; padding:11px 0; border-bottom:0.5px solid #f1f5f9; }
    .md-notice:last-child { border-bottom:0; }
    .md-notice i { color:#185FA5; font-size:16px; flex:none; margin-top:2px; }
    .md-notice__t { display:block; font-size:13px; font-weight:600; color:#111827; }
    .md-notice__d { font-size:12px; color:#6b7280; line-height:1.5; }
    .md-empty { font-size:13px; color:#9ca3af; margin:6px 0; }
</style>
@endsection
