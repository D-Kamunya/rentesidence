@extends('owner.layouts.app')

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="page-content-wrapper bg-white p-30 radius-20">

                {{-- ── Page header ──────────────────────────────────────── --}}
                <div class="tk-page-header mb-4">
                    <div>
                        <h2 class="tk-page-title">{{ $pageTitle }}</h2>
                        <nav aria-label="breadcrumb">
                            <ol class="tk-breadcrumb">
                                <li>
                                    <a href="{{ route('owner.dashboard') }}">{{ __('Dashboard') }}</a>
                                    <svg width="8" height="8" viewBox="0 0 24 24" fill="none" aria-hidden="true"><polyline points="9 18 15 12 9 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </li>
                                <li aria-current="page">{{ $pageTitle }}</li>
                            </ol>
                        </nav>
                    </div>
                </div>

                @if (getOption('app_card_data_show', 1) == 1)

                    @if ($tickets->isEmpty())
                        <div class="tk-empty">
                            <img src="{{ asset('assets/images/empty-img.png') }}" alt="" class="tk-empty__img">
                            <p class="tk-empty__text">{{ __('No tickets yet') }}</p>
                        </div>
                    @else
                        <div class="tk-grid">
                            @foreach ($tickets as $ticket)
                            <div class="tk-card">

                                {{-- Card head: ticket no + status + dropdown --}}
                                <div class="tk-card__head">
                                    <div class="tk-card__head-left">
                                        <span class="tk-ticket-no">#{{ $ticket->ticket_no }}</span>
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
                                    </div>

                                    <div class="dropdown">
                                        <a class="tk-card__more dropdown-toggle dropdown-toggle-nocaret"
                                           href="#" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="ri-more-2-fill"></i>
                                        </a>
                                        <ul class="dropdown-menu {{ selectedLanguage()->rtl == 1 ? 'dropdown-menu-start' : 'dropdown-menu-end' }}">
                                            <li><a class="dropdown-item font-13 statusChange"
                                                   data-url="{{ route('owner.ticket.status.change') }}"
                                                   data-id="{{ $ticket->id }}" data-status="2"
                                                   href="javascript:;"
                                                   title="{{ __('Inprocessing') }}">{{ __('Inprocessing') }}</a></li>
                                            <li><a class="dropdown-item font-13 statusChange"
                                                   data-url="{{ route('owner.ticket.status.change') }}"
                                                   data-id="{{ $ticket->id }}" data-status="3"
                                                   href="javascript:;"
                                                   title="{{ __('Close') }}">{{ __('Close') }}</a></li>
                                            <li><a class="dropdown-item font-13 statusChange"
                                                   data-url="{{ route('owner.ticket.status.change') }}"
                                                   data-id="{{ $ticket->id }}" data-status="5"
                                                   href="javascript:;"
                                                   title="{{ __('Resolved') }}">{{ __('Resolved') }}</a></li>
                                        </ul>
                                    </div>
                                </div>

                                {{-- Body --}}
                                <div class="tk-card__body">
                                    <div class="tk-card__field">
                                        <span class="tk-card__field-label">{{ __('Title') }}</span>
                                        <p class="tk-card__field-value">{{ Str::limit($ticket->title, 30, '...') }}</p>
                                    </div>
                                    <div class="tk-card__field">
                                        <span class="tk-card__field-label">{{ __('Details') }}</span>
                                        <p class="tk-card__field-value tk-card__field-value--muted">{{ Str::limit($ticket->details, 60, '...') }}</p>
                                    </div>

                                    @if($ticket->attachments->isNotEmpty())
                                    <div class="tk-card__field tk-card__field--attachments">
                                        <span class="tk-card__field-label">{{ __('Attachments') }}</span>
                                        <div class="tk-attachments">
                                            @foreach ($ticket->attachments->take(3) as $attachment)
                                                @if (in_array(pathinfo($attachment->file_name, PATHINFO_EXTENSION), imageExtensionList()))
                                                    <a href="{{ $attachment->FileUrl }}"
                                                       class="venobox tk-attachment-thumb"
                                                       data-gall="attach{{ $attachment->id }}">
                                                        <img src="{{ $attachment->FileUrl }}" alt="">
                                                    </a>
                                                @else
                                                    <a href="{{ $attachment->FileUrl }}" class="tk-attachment-file" download>
                                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><polyline points="14 2 14 8 20 8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                                                        {{ $attachment->file_name }}
                                                    </a>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                    @endif
                                </div>

                                {{-- Footer CTA --}}
                                <div class="tk-card__footer">
                                    @if (in_array($ticket->status, [TICKET_STATUS_OPEN, TICKET_STATUS_INPROGRESS, TICKET_STATUS_RESOLVED]))
                                        <a href="{{ route('owner.ticket.details', $ticket->id) }}"
                                           class="tk-card__cta"
                                           title="{{ __('Details') }}">
                                            {{ __('View Details') }}
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none"><polyline points="9 18 15 12 9 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                        </a>
                                    @else
                                        <a href="{{ route('owner.ticket.details', $ticket->id) }}"
                                           class="tk-card__cta tk-card__cta--closed"
                                           title="{{ __('Close') }}">
                                            {{ __('View Details') }}
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none"><polyline points="9 18 15 12 9 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                        </a>
                                    @endif
                                </div>

                            </div>
                            @endforeach
                        </div>
                    @endif

                @else
                    {{-- ── Datatable view (unchanged) ────────────────── --}}
                    <div class="ow-card" style="padding:0;">
                        <div class="table-responsive">
                            <table id="allDataTable" class="table dt-responsive theme-border p-20">
                                <thead>
                                    <tr>
                                        <th>{{ __('SL') }}</th>
                                        <th data-priority="1">{{ __('Ticket') }}</th>
                                        <th>{{ __('Title') }}</th>
                                        <th>{{ __('details') }}</th>
                                        <th>{{ __('Status') }}</th>
                                        <th>{{ __('Action') }}</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                @endif

            </div>
        </div>
    </div>
</div>

<input type="hidden" id="getAllTicketRoute" value="{{ route('owner.ticket.index') }}">
@endsection

@if (getOption('app_card_data_show', 1) != 1)
    @push('style')
        @include('common.layouts.datatable-style')
    @endpush
    @push('script')
        @include('common.layouts.datatable-script')
        <script src="{{ asset('assets/js/custom/ticket-datatable.js') }}"></script>
    @endpush
@endif

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
.tk-page-header {
    display:flex; align-items:flex-start; justify-content:space-between;
    flex-wrap:wrap; gap:1rem;
}
.tk-page-title { font-size:22px; font-weight:500; color:var(--gray-900); margin:0 0 6px; }
.tk-breadcrumb {
    display:flex; align-items:center; gap:6px; list-style:none;
    padding:0; margin:0; font-size:12px; color:var(--gray-400);
}
.tk-breadcrumb li { display:flex; align-items:center; gap:6px; }
.tk-breadcrumb a  { color:var(--blue); font-weight:500; text-decoration:none; }

/* ── Grid ─────────────────────────────────────────────────────── */
.tk-grid {
    display:grid;
    grid-template-columns:repeat(4, 1fr);
    gap:1.25rem;
}
@media(max-width:1400px) { .tk-grid { grid-template-columns:repeat(3, 1fr); } }
@media(max-width:992px)  { .tk-grid { grid-template-columns:repeat(2, 1fr); } }
@media(max-width:540px)  { .tk-grid { grid-template-columns:1fr; } }

/* ── Ticket card ──────────────────────────────────────────────── */
.tk-card {
    background:var(--white);
    border:0.5px solid var(--blue-faint);
    border-radius:14px;
    overflow:hidden;
    display:flex;
    flex-direction:column;
    box-shadow: 0 4px 12px rgba(0,0,0,.04),
                0 0 0 1px rgba(24,95,165,.05),
                0 6px 18px rgba(24,95,165,.06);
    transition:all .25s ease;
}
.tk-card:hover {
    border-color:var(--blue);
    transform:translateY(-3px);
    box-shadow: 0 10px 25px rgba(0,0,0,.06),
                0 0 0 1px rgba(24,95,165,.12),
                0 12px 30px rgba(24,95,165,.18);
}

/* Card head */
.tk-card__head {
    display:flex; align-items:center; justify-content:space-between;
    padding:14px 16px;
    border-bottom:0.5px solid var(--gray-100);
    background:var(--gray-50);
}
.tk-card__head-left { display:flex; align-items:center; gap:8px; }
.tk-ticket-no {
    font-size:11px; font-weight:500; font-family:monospace;
    color:#0C447C; background:var(--blue-light);
    border:0.5px solid var(--blue-border);
    border-radius:99px; padding:2px 9px;
}
.tk-card__more {
    width:28px; height:28px; border-radius:7px;
    display:inline-flex; align-items:center; justify-content:center;
    color:var(--gray-500); background:var(--gray-100);
    font-size:14px; text-decoration:none;
    transition:background .13s, color .13s;
}
.tk-card__more:hover { background:var(--blue); color:var(--white); }

/* Card body */
.tk-card__body { padding:14px 16px; flex:1; display:flex; flex-direction:column; gap:10px; }
.tk-card__field { display:flex; flex-direction:column; gap:3px; }
.tk-card__field--attachments { border-top:0.5px solid var(--gray-100); padding-top:10px; margin-top:2px; }
.tk-card__field-label {
    font-size:10px; font-weight:500; text-transform:uppercase;
    letter-spacing:.07em; color:var(--gray-400);
}
.tk-card__field-value { font-size:13px; color:var(--gray-700); font-weight:500; margin:0; line-height:1.5; }
.tk-card__field-value--muted { color:var(--gray-500); font-weight:400; }

/* Attachments */
.tk-attachments { display:flex; flex-wrap:wrap; gap:6px; margin-top:4px; }
.tk-attachment-thumb {
    width:40px; height:40px; border-radius:6px; overflow:hidden;
    border:0.5px solid var(--gray-200); display:block; flex-shrink:0;
}
.tk-attachment-thumb img { width:100%; height:100%; object-fit:cover; display:block; }
.tk-attachment-file {
    display:inline-flex; align-items:center; gap:4px;
    font-size:11px; color:var(--blue); text-decoration:none;
    background:var(--blue-light); border:0.5px solid var(--blue-border);
    border-radius:6px; padding:3px 8px;
}

/* Card footer */
.tk-card__footer {
    border-top:0.5px solid var(--gray-200);
    background:var(--gray-50);
}
.tk-card__cta {
    display:flex; align-items:center; justify-content:center; gap:5px;
    width:100%; padding:10px; font-size:12px; font-weight:500;
    color:var(--blue); text-decoration:none;
    transition:background .15s, color .15s;
}
.tk-card__cta:hover { background:var(--blue); color:var(--white); }
.tk-card__cta--closed { color:var(--red); }
.tk-card__cta--closed:hover { background:var(--red); color:var(--white); }

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

/* ── Outer card (datatable fallback) ──────────────────────────── */
.ow-card {
    background:var(--white);
    border:0.5px solid var(--blue-faint);
    border-radius:12px; overflow:hidden;
    box-shadow: 0 4px 12px rgba(0,0,0,.04),
                0 0 0 1px rgba(24,95,165,.05),
                0 6px 18px rgba(24,95,165,.06);
}

/* ── Empty state ──────────────────────────────────────────────── */
.tk-empty {
    display:flex; flex-direction:column; align-items:center;
    justify-content:center; padding:4rem 1rem; text-align:center;
}
.tk-empty__img  { max-width:200px; width:100%; opacity:.85; }
.tk-empty__text { margin-top:1.25rem; font-size:16px; font-weight:500; color:var(--gray-500); }

/* ── Utilities ────────────────────────────────────────────────── */
.mb-4 { margin-bottom:1.5rem; }
</style>
@endpush