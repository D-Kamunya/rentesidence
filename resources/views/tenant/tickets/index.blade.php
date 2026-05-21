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
                    --purple-hover:  #3C3489;
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
                .tkt-page-wrapper {
                    background: var(--white);
                    border-radius: 16px;
                    padding: 28px 28px 36px;
                }

                /* ── Page header ── */
                .tkt-page-header {
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    padding-bottom: 18px;
                    border-bottom: 0.5px solid var(--gray-200);
                    margin-bottom: 24px;
                    flex-wrap: wrap;
                    gap: 12px;
                }
                .tkt-page-title {
                    font-size: 22px;
                    font-weight: 500;
                    color: var(--gray-900);
                    margin: 0;
                }
                .tkt-breadcrumb {
                    display: flex;
                    align-items: center;
                    gap: 6px;
                    list-style: none;
                    margin: 0;
                    padding: 0;
                    font-size: 12px;
                    color: var(--gray-400);
                }
                .tkt-breadcrumb a {
                    color: var(--blue);
                    font-weight: 500;
                    text-decoration: none;
                }
                .tkt-breadcrumb a:hover { color: var(--blue-hover); }
                .tkt-breadcrumb-sep {
                    width: 8px; height: 8px;
                    stroke: var(--gray-400);
                    fill: none;
                    stroke-width: 2;
                    stroke-linecap: round;
                    stroke-linejoin: round;
                    flex-shrink: 0;
                }

                /* ── Toolbar ── */
                .tkt-toolbar {
                    display: flex;
                    align-items: flex-end;
                    justify-content: space-between;
                    gap: 12px;
                    flex-wrap: wrap;
                    margin-bottom: 24px;
                }
                .tkt-toolbar-left {
                    display: flex;
                    align-items: flex-end;
                    gap: 12px;
                    flex-wrap: wrap;
                }

                /* Filter label */
                .tkt-field-label {
                    font-size: 10px;
                    font-weight: 500;
                    text-transform: uppercase;
                    letter-spacing: .07em;
                    color: var(--gray-400);
                    display: block;
                    margin-bottom: 5px;
                }

                /* Select */
                .tkt-select {
                    appearance: none;
                    -webkit-appearance: none;
                    background: var(--white) url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%239ca3af' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E") no-repeat right 10px center;
                    border: 0.5px solid var(--gray-200);
                    border-radius: 7px;
                    padding: 7px 32px 7px 11px;
                    font-size: 13px;
                    color: var(--gray-700);
                    min-width: 160px;
                    cursor: pointer;
                    transition: all .15s;
                }
                .tkt-select:focus {
                    outline: none;
                    border-color: var(--blue);
                    box-shadow: 0 0 0 3px rgba(24,95,165,.1);
                }

                /* Search input */
                .tkt-search-wrap {
                    position: relative;
                }
                .tkt-search-wrap svg {
                    position: absolute;
                    left: 10px;
                    top: 50%;
                    transform: translateY(-50%);
                    width: 13px; height: 13px;
                    stroke: var(--gray-400);
                    fill: none;
                    stroke-width: 2;
                    stroke-linecap: round;
                    stroke-linejoin: round;
                    pointer-events: none;
                }
                .tkt-search-input {
                    border: 0.5px solid var(--gray-200);
                    border-radius: 7px;
                    padding: 7px 10px 7px 34px;
                    font-size: 13px;
                    color: var(--gray-700);
                    width: 220px;
                    transition: all .15s;
                }
                .tkt-search-input::placeholder { color: var(--gray-400); }
                .tkt-search-input:focus {
                    outline: none;
                    border-color: var(--blue);
                    box-shadow: 0 0 0 3px rgba(24,95,165,.1);
                }

                /* Primary create button */
                .tkt-btn-primary {
                    display: inline-flex;
                    align-items: center;
                    gap: 6px;
                    background: var(--blue);
                    color: var(--white);
                    font-size: 12px;
                    font-weight: 500;
                    padding: 7px 15px;
                    border-radius: 7px;
                    border: none;
                    cursor: pointer;
                    transition: all .13s;
                    text-decoration: none;
                    white-space: nowrap;
                }
                .tkt-btn-primary:hover {
                    background: var(--blue-hover);
                    transform: translateY(-1px);
                    color: var(--white);
                }
                .tkt-btn-primary svg {
                    width: 13px; height: 13px;
                    stroke: currentColor; fill: none;
                    stroke-width: 2; stroke-linecap: round; stroke-linejoin: round;
                }

                /* ── Ticket card ── */
                /* The partial outputs Bootstrap col-* divs; g-4 on the row handles gutters */

                .tkt-card {
                    background: var(--white);
                    border: 0.5px solid var(--blue-faint);
                    border-radius: 14px;
                    overflow: visible; /* allow dropdown to escape */
                    display: flex;
                    flex-direction: column;
                    height: 100%;
                    width: 100%;
                    box-shadow:
                        0 4px 12px rgba(0,0,0,0.04),
                        0 0 0 1px rgba(24,95,165,0.05),
                        0 6px 18px rgba(24,95,165,0.06);
                    transition: all .25s ease;
                }
                .tkt-card:hover {
                    border-color: var(--blue);
                    transform: translateY(-3px);
                    box-shadow:
                        0 10px 25px rgba(0,0,0,0.06),
                        0 0 0 1px rgba(24,95,165,0.12),
                        0 12px 30px rgba(24,95,165,0.18);
                }

                .tkt-card-head {
                    padding: 14px 16px 12px;
                    display: flex;
                    align-items: center;
                    gap: 6px;
                    flex-wrap: nowrap;
                    border-bottom: 0.5px solid var(--gray-100);
                    border-radius: 14px 14px 0 0;
                    overflow: visible;
                    position: relative;
                }
                /* Lift dropdown above sibling cards */
                .tkt-card-head .dropdown { position: relative; }
                .tkt-card-head .dropdown.show { z-index: 1055; }
                .ticket-column { overflow: visible !important; }
                .tkt-ticket-no {
                    font-size: 11px;
                    font-weight: 500;
                    color: #0C447C;
                    font-family: monospace;
                    background: var(--blue-light);
                    border: 0.5px solid var(--blue-border);
                    border-radius: 99px;
                    padding: 2px 9px;
                    white-space: nowrap;
                }

                /* Status badges */
                .tkt-badge {
                    display: inline-flex;
                    align-items: center;
                    gap: 4px;
                    font-size: 11px;
                    font-weight: 500;
                    padding: 3px 9px;
                    border-radius: 99px;
                    white-space: nowrap;
                }
                .tkt-badge-open    { background: var(--amber-light); color: var(--amber); border: 0.5px solid var(--amber-border); }
                .tkt-badge-inprog  { background: var(--blue-light);  color: #0C447C;      border: 0.5px solid var(--blue-border); }
                .tkt-badge-reopen  { background: var(--red-light);   color: var(--red); }
                .tkt-badge-resolved{ background: var(--green-light); color: var(--green-dark); }
                .tkt-badge-close   { background: var(--gray-100);    color: var(--gray-500); border: 0.5px solid var(--gray-200); }

                /* Dot inside badge */
                .tkt-badge-dot {
                    width: 6px; height: 6px; border-radius: 50%;
                    display: inline-block; flex-shrink: 0;
                }
                .tkt-badge-open    .tkt-badge-dot { background: var(--amber); }
                .tkt-badge-inprog  .tkt-badge-dot { background: var(--blue); }
                .tkt-badge-reopen  .tkt-badge-dot { background: var(--red); }
                .tkt-badge-resolved .tkt-badge-dot { background: var(--green); }
                .tkt-badge-close   .tkt-badge-dot { background: var(--gray-400); }

                /* Dropdown (actions) */
                .tkt-card-head .dropdown { margin-left: auto; }
                .tkt-card-head .dropdown-toggle-nocaret {
                    width: 28px; height: 28px;
                    display: inline-flex; align-items: center; justify-content: center;
                    border-radius: 7px;
                    background: var(--gray-100);
                    color: var(--gray-500);
                    font-size: 16px;
                    transition: all .13s;
                }
                .tkt-card-head .dropdown-toggle-nocaret:hover {
                    background: var(--blue-ghost);
                    color: var(--blue);
                }
                .tkt-card-head .dropdown-menu {
                    font-size: 13px;
                    border: 0.5px solid var(--gray-200);
                    border-radius: 8px;
                    box-shadow: 0 8px 24px rgba(0,0,0,0.12), 0 2px 8px rgba(0,0,0,0.06);
                    padding: 4px;
                    z-index: 1055 !important;
                    min-width: 140px;
                }
                .tkt-card-head .dropdown-item {
                    border-radius: 5px;
                    padding: 7px 12px;
                    color: var(--gray-700);
                    font-size: 12.5px;
                }
                .tkt-card-head .dropdown-item:hover { background: var(--gray-100); }

                /* Card body */
                .tkt-card-body {
                    padding: 14px 18px;
                    flex: 1;
                    display: flex;
                    flex-direction: column;
                    gap: 10px;
                }
                .tkt-info-row {
                    border-bottom: 0.5px solid var(--gray-100);
                    padding-bottom: 10px;
                }
                .tkt-info-row:last-child { border-bottom: none; padding-bottom: 0; }
                .tkt-info-label {
                    font-size: 10px;
                    font-weight: 500;
                    text-transform: uppercase;
                    letter-spacing: .07em;
                    color: var(--gray-400);
                    margin-bottom: 3px;
                }
                .tkt-info-value {
                    font-size: 13px;
                    font-weight: 400;
                    color: var(--gray-700);
                    line-height: 1.45;
                }

                /* Attachments */
                .tkt-attach-gallery {
                    display: flex;
                    flex-wrap: wrap;
                    gap: 6px;
                    margin-top: 4px;
                }
                .tkt-attach-gallery a { font-size: 12px; color: var(--blue); }
                .tkt-attach-gallery .tickets-attachment-item img {
                    width: 44px; height: 44px;
                    object-fit: cover;
                    border-radius: 6px;
                    border: 0.5px solid var(--gray-200);
                }

                /* Card footer button */
                .tkt-card-footer {
                    padding: 12px 18px 16px;
                }
                .tkt-btn-details {
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    gap: 6px;
                    width: 100%;
                    background: var(--blue);
                    color: var(--white);
                    font-size: 12px;
                    font-weight: 500;
                    padding: 8px 15px;
                    border-radius: 7px;
                    text-decoration: none;
                    transition: all .13s;
                }
                .tkt-btn-details:hover {
                    background: var(--blue-hover);
                    color: var(--white);
                    transform: translateY(-1px);
                }
                .tkt-btn-details-close {
                    background: var(--gray-100);
                    color: var(--gray-700);
                    border: 0.5px solid var(--gray-200);
                }
                .tkt-btn-details-close:hover {
                    background: var(--red-light);
                    color: var(--red);
                    border-color: transparent;
                }
                .tkt-btn-details svg {
                    width: 13px; height: 13px;
                    stroke: currentColor; fill: none;
                    stroke-width: 2; stroke-linecap: round; stroke-linejoin: round;
                }

                /* ── Empty state ── */
                .tkt-empty {
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                    justify-content: center;
                    padding: 60px 24px;
                    text-align: center;
                }
                .tkt-empty img { max-width: 180px; opacity: .85; }
                .tkt-empty h3 {
                    font-size: 16px;
                    font-weight: 500;
                    color: var(--gray-700);
                    margin: 20px 0 24px;
                }

                /* ── MODAL ── */
                .tkt-modal .modal-content {
                    border-radius: 14px;
                    border: 0.5px solid var(--gray-200);
                    box-shadow: 0 20px 60px rgba(0,0,0,0.12);
                    overflow: hidden;
                }
                .tkt-modal .modal-header {
                    background: var(--gray-50);
                    border-bottom: 0.5px solid var(--gray-200);
                    padding: 16px 20px;
                    display: flex;
                    align-items: center;
                }
                .tkt-modal .modal-title {
                    font-size: 15px;
                    font-weight: 600;
                    color: var(--gray-900);
                    margin: 0;
                }
                .tkt-modal .modal-eyebrow {
                    font-size: 10px;
                    font-weight: 500;
                    text-transform: uppercase;
                    letter-spacing: .07em;
                    color: var(--gray-400);
                    display: block;
                    margin-bottom: 2px;
                }
                .tkt-modal .btn-close {
                    width: 30px; height: 30px;
                    border-radius: 7px;
                    background: var(--gray-100);
                    border: none;
                    display: flex; align-items: center; justify-content: center;
                    opacity: 1;
                    transition: background .13s;
                    margin-left: auto;
                    padding: 0;
                }
                .tkt-modal .btn-close:hover { background: var(--gray-200); }
                .tkt-modal .modal-body {
                    padding: 22px 20px;
                }
                .tkt-modal .modal-footer {
                    background: var(--gray-50);
                    border-top: 0.5px solid var(--gray-200);
                    padding: 14px 20px;
                    display: flex;
                    align-items: center;
                    gap: 8px;
                    justify-content: flex-start;
                }

                /* Modal form fields */
                .tkt-modal .form-label-sm {
                    font-size: 10px;
                    font-weight: 500;
                    text-transform: uppercase;
                    letter-spacing: .07em;
                    color: var(--gray-400);
                    display: block;
                    margin-bottom: 5px;
                }
                .tkt-modal .form-control,
                .tkt-modal .form-select {
                    border: 0.5px solid var(--gray-200);
                    border-radius: 7px;
                    font-size: 13px;
                    color: var(--gray-700);
                    padding: 8px 12px;
                    transition: all .15s;
                }
                .tkt-modal .form-control:focus,
                .tkt-modal .form-select:focus {
                    border-color: var(--blue);
                    box-shadow: 0 0 0 3px rgba(24,95,165,.1);
                }
                .tkt-modal textarea.form-control { min-height: 96px; resize: vertical; }
                .tkt-modal .mb-field { margin-bottom: 18px; }

                /* Modal buttons */
                .tkt-modal .btn-modal-submit {
                    display: inline-flex; align-items: center; gap: 6px;
                    background: var(--blue); color: var(--white);
                    font-size: 12px; font-weight: 500;
                    padding: 7px 16px; border-radius: 7px; border: none;
                    cursor: pointer; transition: all .13s;
                }
                .tkt-modal .btn-modal-submit:hover {
                    background: var(--blue-hover); transform: translateY(-1px);
                }
                .tkt-modal .btn-modal-back {
                    display: inline-flex; align-items: center; gap: 6px;
                    background: var(--gray-100); color: var(--gray-700);
                    font-size: 12px; font-weight: 500;
                    padding: 7px 16px; border-radius: 7px;
                    border: 0.5px solid var(--gray-200);
                    cursor: pointer; transition: all .13s;
                }
                .tkt-modal .btn-modal-back:hover { background: var(--gray-200); }

                @media (max-width: 768px) {
                    .tkt-search-input { width: 130px; }
                    .tkt-toolbar { flex-direction: column; align-items: stretch; }
                    .tkt-toolbar-left { flex-direction: column; }
                    .tkt-btn-primary { justify-content: center; }
                }
            </style>

            <div class="tkt-page-wrapper">

                {{-- Page header --}}
                <div class="tkt-page-header">
                    <div>
                        <h3 class="tkt-page-title">{{ $pageTitle }}</h3>
                    </div>
                    <ol class="tkt-breadcrumb">
                        <li><a href="{{ route('tenant.dashboard') }}">{{ __('Dashboard') }}</a></li>
                        <li>
                            <svg class="tkt-breadcrumb-sep" viewBox="0 0 8 8"><polyline points="2 1 6 4 2 7"/></svg>
                        </li>
                        <li>{{ $pageTitle }}</li>
                    </ol>
                </div>

                {{-- Toolbar --}}
                <div class="tkt-toolbar">
                    <div class="tkt-toolbar-left">
                        <div>
                            <span class="tkt-field-label">{{ __('Status') }}</span>
                            <select class="tkt-select statusSearch">
                                <option value="" selected>{{ __('All Statuses') }}</option>
                                <option value="1">{{ __('Open') }}</option>
                                <option value="2">{{ __('In Progress') }}</option>
                                <option value="3">{{ __('Close') }}</option>
                                <option value="4">{{ __('Reopen') }}</option>
                                <option value="5">{{ __('Resolved') }}</option>
                            </select>
                        </div>
                        <div>
                            <span class="tkt-field-label">{{ __('Search') }}</span>
                            <div class="tkt-search-wrap">
                                <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                                <input type="text" class="tkt-search-input textSearch" placeholder="{{ __('Search tickets…') }}">
                            </div>
                        </div>
                    </div>
                    <div>
                        <button type="button" class="tkt-btn-primary" data-bs-toggle="modal" data-bs-target="#addTicketModal">
                            <svg viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                            {{ __('Create Ticket') }}
                        </button>
                    </div>
                </div>

                {{-- Ticket cards --}}
                <div class="tickets-item-wrap">
                    <div class="row g-4" id="ticketAppend">
                        @include('tenant.tickets.single-view')
                    </div>
                </div>

            </div>{{-- /tkt-page-wrapper --}}
        </div>
    </div>
</div>

{{-- ═══════════════════════ ADD TICKET MODAL ═══════════════════════ --}}
<div class="modal fade tkt-modal" id="addTicketModal" tabindex="-1" aria-labelledby="addTicketModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <span class="modal-eyebrow">{{ __('Support') }}</span>
                    <h4 class="modal-title" id="addTicketModalLabel">{{ __('Create Ticket') }}</h4>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <span class="iconify" data-icon="akar-icons:cross"></span>
                </button>
            </div>
            <form class="ajax" action="{{ route('tenant.ticket.store') }}" method="POST" data-handler="getShowMessage">
                <div class="modal-body">
                    <div class="mb-field">
                        <label class="form-label-sm">{{ __('Title') }}</label>
                        <input type="text" class="form-control" name="title" placeholder="{{ __('Enter ticket title') }}">
                    </div>
                    <div class="mb-field">
                        <label class="form-label-sm">{{ __('Details') }}</label>
                        <textarea class="form-control" name="details" placeholder="{{ __('Describe the issue…') }}"></textarea>
                    </div>
                    <div class="mb-field">
                        <label class="form-label-sm">{{ __('Topic') }}</label>
                        <select class="form-select" name="topic_id">
                            <option value="">-- {{ __('Select Topic') }} --</option>
                            @foreach ($topics as $topic)
                                <option value="{{ $topic->id }}">{{ $topic->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-field">
                        <label class="form-label-sm">{{ __('Attachments') }}</label>
                        <input type="file" id="attachments" name="attachments[]" class="dropify" data-height="180" multiple />
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-modal-back" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn-modal-submit">{{ __('Create Ticket') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ═══════════════════════ EDIT TICKET MODAL ═══════════════════════ --}}
<div class="modal fade tkt-modal" id="editTicketModal" tabindex="-1" aria-labelledby="editTicketModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <span class="modal-eyebrow">{{ __('Support') }}</span>
                    <h4 class="modal-title" id="editTicketModalLabel">{{ __('Edit Ticket') }}</h4>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <span class="iconify" data-icon="akar-icons:cross"></span>
                </button>
            </div>
            <form class="ajax" action="{{ route('tenant.ticket.store') }}" method="POST" data-handler="getShowMessage">
                <input type="hidden" class="id" name="id">
                <div class="modal-body">
                    <div class="mb-field">
                        <label class="form-label-sm">{{ __('Title') }}</label>
                        <input type="text" class="form-control title" name="title" placeholder="{{ __('Enter ticket title') }}">
                    </div>
                    <div class="mb-field">
                        <label class="form-label-sm">{{ __('Details') }}</label>
                        <textarea class="form-control details" name="details" placeholder="{{ __('Describe the issue…') }}"></textarea>
                    </div>
                    <div class="mb-field">
                        <label class="form-label-sm">{{ __('Topic') }}</label>
                        <select class="form-select topic" name="topic_id">
                            <option value="">-- {{ __('Select Topic') }} --</option>
                            @foreach ($topics as $topic)
                                <option value="{{ $topic->id }}">{{ $topic->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-field">
                        <label class="form-label-sm">{{ __('Attachments') }}</label>
                        <input type="file" id="attachments" name="attachments[]" class="dropify" data-height="180" multiple />
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-modal-back" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn-modal-submit">{{ __('Update Ticket') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<input type="hidden" id="getInfoRoute" value="{{ route('tenant.ticket.get.info') }}">
<input type="hidden" id="searchRoute" value="{{ route('tenant.ticket.search') }}">
@endsection

@push('script')
    <script src="{{ asset('assets/js/custom/ticket.js') }}"></script>
    <script src="{{ asset('assets/js/custom/ticket-search.js') }}"></script>
@endpush