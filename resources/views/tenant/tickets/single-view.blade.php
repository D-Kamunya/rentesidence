@forelse ($tickets as $ticket)
    <div class="col-md-6 col-lg-6 col-xl-6 col-xxl-3 ticket-column status{{ $ticket->status }} d-flex">

        <div class="tkt-card mb-0">

            {{-- Card head: ticket no · status · actions --}}
            <div class="tkt-card-head">
                <span class="tkt-ticket-no">#{{ $ticket->ticket_no }}</span>

                @if ($ticket->status == TICKET_STATUS_OPEN)
                    <span class="tkt-badge tkt-badge-open">
                        <span class="tkt-badge-dot"></span>{{ __('Open') }}
                    </span>
                @elseif ($ticket->status == TICKET_STATUS_INPROGRESS)
                    <span class="tkt-badge tkt-badge-inprog">
                        <span class="tkt-badge-dot"></span>{{ __('In Progress') }}
                    </span>
                @elseif ($ticket->status == TICKET_STATUS_REOPEN)
                    <span class="tkt-badge tkt-badge-reopen">
                        <span class="tkt-badge-dot"></span>{{ __('Reopen') }}
                    </span>
                @elseif ($ticket->status == TICKET_STATUS_RESOLVED)
                    <span class="tkt-badge tkt-badge-resolved">
                        <span class="tkt-badge-dot"></span>{{ __('Resolved') }}
                    </span>
                @else
                    <span class="tkt-badge tkt-badge-close">
                        <span class="tkt-badge-dot"></span>{{ __('Closed') }}
                    </span>
                @endif

                <div class="dropdown">
                    <a class="dropdown-toggle dropdown-toggle-nocaret" href="#"
                       data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="ri-more-2-fill"></i>
                    </a>
                    <ul class="dropdown-menu {{ selectedLanguage()->rtl == 1 ? 'dropdown-menu-start' : 'dropdown-menu-end' }}">
                        @if ($ticket->status == TICKET_STATUS_OPEN)
                            <li>
                                <a class="dropdown-item font-13 edit" data-id="{{ $ticket->id }}"
                                   href="javascript:;" title="{{ __('Edit') }}">{{ __('Edit') }}</a>
                            </li>
                            <li>
                                <a class="dropdown-item font-13 deleteItem" href="javascript:;"
                                   data-formid="delete_row_form_{{ $ticket->id }}"
                                   title="{{ __('Delete') }}">{{ __('Delete') }}</a>
                                <form action="{{ route('tenant.ticket.delete', [$ticket->id]) }}" method="post"
                                      id="delete_row_form_{{ $ticket->id }}">
                                    {{ method_field('DELETE') }}
                                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                </form>
                            </li>
                        @endif
                        <li>
                            <a class="dropdown-item font-13 statusChange"
                               data-url="{{ route('tenant.ticket.status.change') }}"
                               data-id="{{ $ticket->id }}" data-status="3"
                               href="javascript:;" title="{{ __('Close') }}">{{ __('Close') }}</a>
                        </li>
                        <li>
                            <a class="dropdown-item font-13 statusChange"
                               data-url="{{ route('tenant.ticket.status.change') }}"
                               data-id="{{ $ticket->id }}" data-status="5"
                               href="javascript:;" title="{{ __('Resolved') }}">{{ __('Resolved') }}</a>
                        </li>
                    </ul>
                </div>
            </div>

            {{-- Card body --}}
            <div class="tkt-card-body">

                <div class="tkt-info-row">
                    <p class="tkt-info-label">{{ __('Title') }}</p>
                    <p class="tkt-info-value ticketTitle">{{ Str::limit($ticket->title, 50, '…') }}</p>
                </div>

                <div class="tkt-info-row">
                    <p class="tkt-info-label">{{ __('Details') }}</p>
                    <p class="tkt-info-value">{{ Str::limit($ticket->details, 80, '…') }}</p>
                </div>

                @if ($ticket->attachments->count() > 0)
                    <div class="tkt-info-row">
                        <p class="tkt-info-label">{{ __('Attachments') }}</p>
                        <div class="tkt-attach-gallery">
                            @foreach ($ticket->attachments->take(3) as $attachment)
                                @if (in_array(pathinfo($attachment->file_name, PATHINFO_EXTENSION), imageExtensionList()))
                                    <a href="{{ $attachment->FileUrl }}" class="venobox tickets-attachment-item"
                                       data-gall="attach{{ $attachment->id }}">
                                        <img src="{{ $attachment->FileUrl }}" alt="">
                                    </a>
                                @else
                                    <a href="{{ $attachment->FileUrl }}" download>{{ $attachment->file_name }}</a>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endif

            </div>

            {{-- Card footer --}}
            <div class="tkt-card-footer">
                @if ($ticket->status == TICKET_STATUS_OPEN || $ticket->status == TICKET_STATUS_INPROGRESS || $ticket->status == TICKET_STATUS_RESOLVED)
                    <a href="{{ route('tenant.ticket.details', $ticket->id) }}"
                       class="tkt-btn-details"
                       title="{{ __('View Details') }}">
                        <svg viewBox="0 0 24 24" width="13" height="13" stroke="currentColor" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                        </svg>
                        {{ __('View Details') }}
                    </a>
                @else
                    <a href="{{ route('tenant.ticket.details', $ticket->id) }}"
                       class="tkt-btn-details tkt-btn-details-close"
                       title="{{ __('View Details') }}">
                        <svg viewBox="0 0 24 24" width="13" height="13" stroke="currentColor" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                        </svg>
                        {{ __('View Details') }}
                    </a>
                @endif
            </div>

        </div>{{-- /tkt-card --}}

    </div>
@empty
    <div class="col-12">
        <div class="tkt-empty">
            <img src="{{ asset('assets/images/empty-img.png') }}" alt="" class="img-fluid">
            <h3>{{ __('No tickets yet') }}</h3>
            <a href="{{ route('tenant.ticket.index') }}" class="tkt-btn-details" style="width:auto;padding:8px 20px;">
                {{ __('My Tickets') }}
            </a>
        </div>
    </div>
@endforelse