@extends('owner.layouts.app')

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="page-content-wrapper bg-white p-30 radius-20">
                <div class="container">

                    {{-- Page Header --}}
                    <div class="dash-header mb-4">
                        <div>
                            <h2 class="dash-title">{{ __('Dashboard') }}</h2>
                            <p class="dash-subtitle">
                                {{ __('Welcome back') }}, <strong>{{ auth()->user()->name }}</strong>
                                <span class="iconify font-24" data-icon="openmoji:waving-hand"></span>
                            </p>
                        </div>
                        <a href="{{ route('owner.property.add') }}" class="theme-btn-primary">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none">
                                <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/>
                            </svg>
                            {{ __('Add Property') }}
                        </a>
                    </div>

                    {{-- Pending Tickets Nudge --}}
                    @if(isset($pendingTickets) && $pendingTickets > 0)
                        <div class="notice-bar notice-bar--warning mb-4">
                            <div class="notice-bar__left">
                                <div class="notice-bar__icon">
                                    <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
                                        <path d="M8 1.5a6.5 6.5 0 100 13 6.5 6.5 0 000-13z" stroke="currentColor" stroke-width="1.4"/>
                                        <path d="M8 7v4M8 5v.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                    </svg>
                                </div>
                                <div>
                                    <div class="notice-bar__text">
                                        You have <strong>{{ $pendingTickets }} pending {{ Str::plural('ticket', $pendingTickets) }}</strong> requiring attention
                                    </div>
                                    <div class="notice-bar__sub">Respond quickly to maintain tenant satisfaction</div>
                                </div>
                            </div>
                            <a href="{{ route('owner.ticket.index') }}" class="notice-bar__action">
                                <svg width="10" height="10" viewBox="0 0 16 16" fill="none">
                                    <path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                View Tickets
                            </a>
                        </div>
                    @endif

                    {{-- Summary Cards --}}
                    <div class="row g-3 mb-4">

                        {{-- Total Properties --}}
                        <div class="col-6 col-md-4 col-lg-3">
                            <div class="stat-card stat-card--illustrated">
                                <div class="stat-card__illustration stat-card__illustration--coral">
                                    <svg viewBox="0 0 120 72" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <rect x="6"  y="38" width="16" height="30" fill="#F5C4B3" rx="2"/>
                                        <rect x="26" y="28" width="14" height="40" fill="#F5C4B3" rx="2" opacity=".7"/>
                                        <rect x="68" y="12" width="44" height="56" fill="#FAECE7" rx="4"/>
                                        <rect x="76" y="20" width="9" height="11" fill="#993C1D" rx="1.5" opacity=".55"/>
                                        <rect x="90" y="20" width="9" height="11" fill="#993C1D" rx="1.5" opacity=".55"/>
                                        <rect x="76" y="36" width="9" height="11" fill="#993C1D" rx="1.5" opacity=".55"/>
                                        <rect x="90" y="36" width="9" height="11" fill="#993C1D" rx="1.5" opacity=".55"/>
                                        <rect x="76" y="52" width="9" height="16" fill="#993C1D" rx="1.5" opacity=".45"/>
                                        <rect x="87" y="52" width="12" height="16" fill="#993C1D" rx="1.5" opacity=".3"/>
                                        <path d="M68 12 L90 2 L112 12" stroke="#993C1D" stroke-width="1.8" fill="none" opacity=".5" stroke-linecap="round"/>
                                    </svg>
                                </div>
                                <div class="stat-card__content">
                                    <p class="stat-card__label">Total Properties</p>
                                    <p class="stat-card__value stat-card__value--coral">{{ $totalProperties }}</p>
                                </div>
                            </div>
                        </div>

                        {{-- Total Units --}}
                        <div class="col-6 col-md-4 col-lg-3">
                            <div class="stat-card stat-card--illustrated">
                                <div class="stat-card__illustration stat-card__illustration--blue">
                                    <svg viewBox="0 0 120 72" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <rect x="10" y="18" width="100" height="50" fill="#E6F1FB" rx="4"/>
                                        <rect x="22" y="28" width="76" height="40" fill="#185FA5" rx="2" opacity=".1"/>
                                        <rect x="30" y="34" width="60" height="34" fill="#185FA5" rx="2" opacity=".18"/>
                                        <circle cx="83" cy="52" r="3" fill="#185FA5" opacity=".6"/>
                                        <rect x="18" y="26" width="8" height="18" fill="#185FA5" rx="1" opacity=".18"/>
                                        <rect x="94" y="26" width="8" height="18" fill="#185FA5" rx="1" opacity=".18"/>
                                        <text x="60" y="53" font-size="9" fill="#185FA5" text-anchor="middle" font-weight="bold" opacity=".45" font-family="system-ui,sans-serif">101</text>
                                    </svg>
                                </div>
                                <div class="stat-card__content">
                                    <p class="stat-card__label">Total Units</p>
                                    <p class="stat-card__value stat-card__value--blue">{{ $totalUnits }}</p>
                                </div>
                            </div>
                        </div>

                        {{-- Total Tenants --}}
                        <div class="col-6 col-md-4 col-lg-3">
                            <div class="stat-card stat-card--illustrated">
                                <div class="stat-card__illustration stat-card__illustration--teal">
                                    <svg viewBox="0 0 120 72" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <circle cx="28" cy="28" r="11" fill="#1D9E75" opacity=".18"/>
                                        <circle cx="28" cy="25" r="7"  fill="#1D9E75" opacity=".45"/>
                                        <rect   x="20" y="38" width="16" height="20" fill="#1D9E75" rx="3" opacity=".28"/>
                                        <circle cx="60" cy="24" r="11" fill="#1D9E75" opacity=".18"/>
                                        <circle cx="60" cy="21" r="7"  fill="#1D9E75" opacity=".5"/>
                                        <rect   x="52" y="34" width="16" height="20" fill="#1D9E75" rx="3" opacity=".32"/>
                                        <circle cx="92" cy="30" r="11" fill="#1D9E75" opacity=".13"/>
                                        <circle cx="92" cy="27" r="7"  fill="#1D9E75" opacity=".38"/>
                                        <rect   x="84" y="40" width="16" height="20" fill="#1D9E75" rx="3" opacity=".22"/>
                                    </svg>
                                </div>
                                <div class="stat-card__content">
                                    <p class="stat-card__label">Total Tenants</p>
                                    <p class="stat-card__value stat-card__value--teal">{{ $totalTenants }}</p>
                                </div>
                            </div>
                        </div>

                        {{-- Total Maintainers --}}
                        <div class="col-6 col-md-4 col-lg-3">
                            <div class="stat-card stat-card--illustrated">
                                <div class="stat-card__illustration stat-card__illustration--purple">
                                    <svg viewBox="0 0 120 72" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <circle cx="48" cy="26" r="9"  fill="#534AB7" opacity=".2"/>
                                        <circle cx="48" cy="23" r="6"  fill="#534AB7" opacity=".5"/>
                                        <rect   x="42" y="36" width="12" height="20" fill="#534AB7" rx="3" opacity=".28"/>
                                        <rect   x="38" y="42" width="20" height="4"  fill="#534AB7" rx="1" opacity=".4"/>
                                        <circle cx="72" cy="32" r="7"  stroke="#534AB7" stroke-width="2.5" fill="none" opacity=".4"/>
                                        <rect   x="70" y="37" width="4" height="18" fill="#534AB7" rx="1" opacity=".38" transform="rotate(12 72 46)"/>
                                        <rect   x="32" y="36" width="3" height="14" fill="#534AB7" rx="1" opacity=".32" transform="rotate(-18 33 43)"/>
                                        <rect   x="86" y="50" width="22" height="14" fill="#534AB7" rx="2" opacity=".12"/>
                                        <rect   x="90" y="52" width="5" height="3"  fill="#534AB7" rx=".5" opacity=".22"/>
                                    </svg>
                                </div>
                                <div class="stat-card__content">
                                    <p class="stat-card__label">Maintainers</p>
                                    <p class="stat-card__value stat-card__value--purple">{{ $totalMaintainers }}</p>
                                </div>
                            </div>
                        </div>

                        {{-- Available Units --}}
                        <div class="col-6 col-md-4 col-lg-3">
                            <div class="stat-card stat-card--illustrated">
                                <div class="stat-card__illustration stat-card__illustration--amber">
                                    <svg viewBox="0 0 120 72" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <rect x="14" y="16" width="46" height="54" fill="#FAEEDA" rx="3"/>
                                        <rect x="24" y="30" width="26" height="40" fill="#854F0B" rx="2" opacity=".1"/>
                                        <circle cx="50" cy="45" r="3"   fill="#854F0B" opacity=".35"/>
                                        <rect   x="48.5" y="48" width="3" height="5" fill="#854F0B" rx=".5" opacity=".35"/>
                                        <circle cx="84" cy="38" r="9"   stroke="#854F0B" stroke-width="2.5" fill="none" opacity=".45"/>
                                        <rect   x="82" y="45" width="4" height="18" fill="#854F0B" rx="1" opacity=".45" transform="rotate(-12 84 54)"/>
                                        <rect   x="89" y="55" width="9" height="3"  fill="#854F0B" rx="1" opacity=".45" transform="rotate(-12 93 56)"/>
                                        <rect   x="86" y="61" width="9" height="3"  fill="#854F0B" rx="1" opacity=".45" transform="rotate(-12 90 62)"/>
                                    </svg>
                                </div>
                                <div class="stat-card__content">
                                    <p class="stat-card__label">Available Units</p>
                                    <p class="stat-card__value stat-card__value--amber">{{ $totalUnits - $totalTenants }}</p>
                                </div>
                            </div>
                        </div>

                        {{-- Occupancy Rate --}}
                        <div class="col-6 col-md-4 col-lg-3">
                            <div class="stat-card stat-card--illustrated">
                                <div class="stat-card__illustration stat-card__illustration--green">
                                    <svg viewBox="0 0 120 80" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <!-- Pie chart / occupancy indicator -->
                                        <circle cx="45" cy="40" r="28" stroke="#E1F5EE" stroke-width="12" fill="none"/>
                                        <circle cx="45" cy="40" r="28" stroke="#0F6E56" stroke-width="12" fill="none" 
                                                stroke-dasharray="{{ round(($totalTenants / max($totalUnits, 1)) * 176) }} 176" 
                                                stroke-linecap="round" opacity="0.7"
                                                transform="rotate(-90 45 40)"/>
                                        <!-- Home icon inside -->
                                        <path d="M35 48 L35 38 L45 30 L55 38 L55 48 L50 48 L50 42 L40 42 L40 48 L35 48Z" fill="#0F6E56" opacity="0.5"/>
                                        <!-- Upward trend arrow -->
                                        <path d="M80 55 L80 30 L95 30" stroke="#0F6E56" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" opacity="0.6" fill="none"/>
                                        <path d="M88 25 L95 30 L88 35" stroke="#0F6E56" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" opacity="0.6" fill="none"/>
                                    </svg>
                                </div>
                                <div class="stat-card__content">
                                    <p class="stat-card__label">Occupancy Rate</p>
                                    <p class="stat-card__value stat-card__value--green">
                                        {{ $totalUnits > 0 ? round(($totalTenants / $totalUnits) * 100) : 0 }}%
                                    </p>
                                </div>
                            </div>
                        </div>

                        {{-- Open Tickets --}}
                        <div class="col-6 col-md-4 col-lg-3">
                            <div class="stat-card stat-card--illustrated">
                                <div class="stat-card__illustration stat-card__illustration--coral">
                                    <svg viewBox="0 0 120 72" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <rect x="14" y="18" width="68" height="46" fill="#FAECE7" rx="4"/>
                                        <circle cx="14" cy="41" r="4" fill="#fff"/>
                                        <circle cx="82" cy="41" r="4" fill="#fff"/>
                                        <rect x="24" y="28" width="48" height="14" fill="#993C1D" rx="2" opacity=".12"/>
                                        <line x1="29" y1="33" x2="67" y2="33" stroke="#993C1D" stroke-width="2" stroke-linecap="round" opacity=".3"/>
                                        <line x1="29" y1="38" x2="52" y2="38" stroke="#993C1D" stroke-width="2" stroke-linecap="round" opacity=".2"/>
                                        <rect x="28" y="48" width="40" height="10" fill="#993C1D" rx="2" opacity=".08"/>
                                        <line x1="33" y1="52" x2="63" y2="52" stroke="#993C1D" stroke-width="1.5" stroke-linecap="round" opacity=".18"/>
                                        <rect x="70" y="16" width="24" height="10" fill="#993C1D" rx="2" opacity=".55"/>
                                        <text x="82" y="23" font-size="5.5" fill="#fff" text-anchor="middle" font-weight="bold" font-family="system-ui,sans-serif">OPEN</text>
                                        <circle cx="100" cy="48" r="10" fill="#993C1D" opacity=".08"/>
                                        <path d="M100 43 L100 49 M100 52.5 L100 53" stroke="#993C1D" stroke-width="1.8" stroke-linecap="round" opacity=".45"/>
                                    </svg>
                                </div>
                                <div class="stat-card__content">
                                    <p class="stat-card__label">Open Tickets</p>
                                    <p class="stat-card__value stat-card__value--coral">{{ $tickets->count() }}</p>
                                </div>
                            </div>
                        </div>

                    </div>

                    {{-- Chart --}}
                    <div class="dash-card mb-4">
                        <div class="dash-card__head d-flex align-items-center justify-content-between">
                            <span style="font-weight:500;font-size:14px;">Rent Overview</span>
                            <span style="font-size:12px;color:#9ca3af;">Annual Revenue</span>
                        </div>
                        <div class="dash-card__body" style="padding:1.25rem 1.5rem;">
                            <div class="d-flex align-items-center mb-3">
                                <h3 class="mb-0" style="font-size:24px;font-weight:500;color:#111827;">{{ currencyPrice($yearlyTotalAmount) }}</h3>
                                <span class="ms-3" style="background:#E1F5EE;color:#0F6E56;border-radius:99px;font-size:11px;font-weight:500;padding:3px 10px;">Annual Total</span>
                            </div>
                            <div id="chart1"></div>
                        </div>
                    </div>

                    {{-- Properties & Tickets --}}
                    <div class="row g-3">
                        <div class="col-lg-7">
                            <div class="dash-card h-100">
                                <div class="dash-card__head d-flex align-items-center justify-content-between">
                                    <span style="font-weight:500;font-size:14px;">My Properties</span>
                                    <a href="{{ route('owner.property.allProperty') }}" class="theme-link" style="font-size:12px;display:inline-flex;align-items:center;gap:4px;text-decoration:none;">
                                        View All
                                        <svg width="12" height="12" viewBox="0 0 16 16" fill="none"><path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    </a>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead>
                                            <tr style="border-bottom:0.5px solid #e5e7eb;background:#fafafa;">
                                                <th class="dash-th">Property</th>
                                                <th class="dash-th text-center">Units</th>
                                                <th class="dash-th text-center">Available</th>
                                                <th class="dash-th text-center">Tenants</th>
                                                <th class="dash-th text-center">Maint.</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($properties as $property)
                                                @php $vacant = $property->number_of_unit - $property->total_tenant; @endphp
                                                <tr style="border-bottom:0.5px solid #f3f4f6;">
                                                    <td class="dash-td">
                                                        <p style="font-weight:500;font-size:14px;margin:0 0 2px;color:#111827;">{{ $property->name }}</p>
                                                        <p style="font-size:12px;color:#9ca3af;margin:0;">{{ Str::limit($property->address, 38) }}</p>
                                                    </td>
                                                    <td class="dash-td text-center">
                                                        <span class="prop-pill prop-pill--unit">{{ $property->number_of_unit }}</span>
                                                    </td>
                                                    <td class="dash-td text-center">
                                                        <span class="prop-pill {{ $vacant > 0 ? 'prop-pill--vacant' : 'prop-pill--full' }}">{{ $vacant }}</span>
                                                    </td>
                                                    <td class="dash-td text-center">
                                                        <span class="prop-pill prop-pill--tenant">{{ $property->total_tenant }}</span>
                                                    </td>
                                                    <td class="dash-td text-center">
                                                        <span class="prop-pill prop-pill--maint">{{ $property->total_maintainers }}</span>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="5" class="text-center py-5" style="color:#9ca3af;font-size:14px;">
                                                        No properties yet.
                                                        <a href="{{ route('owner.property.add') }}" class="theme-link">Add your first property</a>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-5">
                            <div class="dash-card h-100">
                                <div class="dash-card__head d-flex align-items-center justify-content-between">
                                    <span style="font-weight:500;font-size:14px;">Recent Tickets</span>
                                    <a href="{{ route('owner.ticket.index') }}" class="theme-link" style="font-size:12px;display:inline-flex;align-items:center;gap:4px;text-decoration:none;">
                                        View All
                                        <svg width="12" height="12" viewBox="0 0 16 16" fill="none"><path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    </a>
                                </div>
                                @forelse ($tickets->take(5) as $ticket)
                                    <a href="{{ route('owner.ticket.details', $ticket->id) }}" class="ticket-item text-decoration-none">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0">
                                                @if($ticket->user?->image)
                                                    <div class="ticket-avatar">
                                                        <img src="{{ $ticket->user->image }}" alt="" class="w-100 h-100 object-fit-cover">
                                                    </div>
                                                @else
                                                    <div class="ticket-avatar ticket-avatar--initials">
                                                        {{ strtoupper(substr($ticket->user?->name ?? 'T', 0, 2)) }}
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="flex-grow-1 ms-3">
                                                <p class="ticket-title">{{ Str::limit($ticket->title, 38, '...') }}</p>
                                                <div class="d-flex gap-2 mt-1">
                                                    <span class="ticket-badge ticket-badge--cat">{{ $ticket->topic->name ?? 'General' }}</span>
                                                    <span class="ticket-badge ticket-badge--open">Open</span>
                                                </div>
                                            </div>
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" style="color:#d1d5db;flex-shrink:0;margin-left:8px;">
                                                <path d="M9 18l6-6-6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </div>
                                    </a>
                                @empty
                                    <div class="text-center py-5" style="color:#9ca3af;font-size:14px;">No tickets found</div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* ── Page header ──────────────────────────────────────── */
    .dash-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 1rem;
    }
    .dash-title   { font-size: 22px; font-weight: 500; color: #111827; margin: 0 0 4px; }
    .dash-subtitle { font-size: 14px; color: #6b7280; margin: 0; }

    /* ── Primary button ───────────────────────────────────── */
    .theme-btn-primary {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: #185FA5;
        color: #fff;
        padding: 10px 18px;
        border-radius: 8px;
        font-weight: 500;
        font-size: 13px;
        text-decoration: none;
        transition: background .15s, transform .15s, box-shadow .15s;
    }
    .theme-btn-primary:hover {
        background: #0F4A84;
        color: #fff;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(24,95,165,.25);
    }

    /* ── Illustrated stat cards ───────────────────────────── */
    .stat-card {
        background: #fff;
        border: 0.5px solid #e5e7eb;
        border-radius: 12px;
        overflow: hidden;
        height: 100%;
        display: flex;
        flex-direction: column;
        transition: box-shadow .2s, transform .2s;
    }
    .stat-card:hover {
        box-shadow: 0 6px 18px rgba(0,0,0,.07);
        transform: translateY(-2px);
    }
    .stat-card__illustration {
        height: 64px;
        display: flex;
        align-items: flex-end;
        justify-content: center;
        overflow: hidden;
    }
    .stat-card__illustration svg { width: 120px; height: 72px; flex-shrink: 0; }
    .stat-card__illustration--coral  { background: #FAECE7; }
    .stat-card__illustration--blue   { background: #E6F1FB; }
    .stat-card__illustration--teal   { background: #E1F5EE; }
    .stat-card__illustration--purple { background: #EEEDFE; }
    .stat-card__illustration--amber  { background: #FAEEDA; }
    .stat-card__illustration--green  { background: #E1F5EE; }

    .stat-card__content { padding: .75rem 1.1rem 1rem; }
    .stat-card__label {
        font-size: 11px;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: .06em;
        color: #9ca3af;
        margin: 0 0 4px;
    }
    .stat-card__value {
        font-size: 22px;
        font-weight: 500;
        margin: 0;
        line-height: 1.2;
    }
    .stat-card__value--coral  { color: #993C1D; }
    .stat-card__value--blue   { color: #185FA5; }
    .stat-card__value--teal   { color: #1D9E75; }
    .stat-card__value--purple { color: #534AB7; }
    .stat-card__value--amber  { color: #854F0B; }
    .stat-card__value--green  { color: #0F6E56; }

    /* ── Shared card ──────────────────────────────────────── */
    .dash-card {
        background: #fff;
        border: 0.5px solid #e5e7eb;
        border-radius: 12px;
        overflow: hidden;
    }
    .dash-card__head {
        padding: .85rem 1.25rem;
        border-bottom: 0.5px solid #e5e7eb;
        background: #fafafa;
    }

    /* ── Table ────────────────────────────────────────────── */
    .dash-th {
        padding: .75rem 1.1rem;
        font-size: 11px;
        font-weight: 500;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: .06em;
        border: none;
        white-space: nowrap;
    }
    .dash-td { padding: .8rem 1.1rem; border: none; vertical-align: middle; }

    /* ── Property table pills ─────────────────────────────── */
    .prop-pill {
        display: inline-block;
        font-size: 12px;
        font-weight: 500;
        padding: 2px 10px;
        border-radius: 99px;
        min-width: 32px;
        text-align: center;
    }
    .prop-pill--unit   { background: #E6F1FB; color: #185FA5; }
    .prop-pill--tenant { background: #E1F5EE; color: #0F6E56; }
    .prop-pill--maint  { background: #EEEDFE; color: #534AB7; }
    .prop-pill--vacant { background: #FAEEDA; color: #854F0B; }
    .prop-pill--full   { background: #f3f4f6; color: #6b7280; }

    /* ── Theme link ───────────────────────────────────────── */
    .theme-link { color: #185FA5; font-weight: 500; transition: color .15s; }
    .theme-link:hover { color: #0F4A84; }

    /* ── Notice bar ───────────────────────────────────────── */
    .notice-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 10px;
        background: #F0F9F4;
        border: 0.5px solid #9FE1CB;
        border-radius: 10px;
        padding: 10px 16px;
    }
    .notice-bar--warning { background: #FDF4F1; border-color: #F5C4B3; }
    .notice-bar__left { display: flex; align-items: center; gap: 12px; }
    .notice-bar__icon {
        width: 30px; height: 30px; border-radius: 8px;
        background: #E1F5EE; color: #0F6E56;
        display: flex; align-items: center; justify-content: center; flex-shrink: 0;
    }
    .notice-bar--warning .notice-bar__icon { background: #FAECE7; color: #993C1D; }
    .notice-bar__text  { font-size: 13px; font-weight: 500; color: #085041; }
    .notice-bar--warning .notice-bar__text { color: #712B13; }
    .notice-bar__sub   { font-size: 12px; color: #0F6E56; margin-top: 2px; }
    .notice-bar--warning .notice-bar__sub  { color: #993C1D; }
    .notice-bar__action {
        display: inline-flex; align-items: center; gap: 5px;
        background: #E1F5EE; border: 0.5px solid #9FE1CB;
        color: #0F6E56; border-radius: 99px;
        font-size: 11px; font-weight: 500; padding: 4px 11px;
        white-space: nowrap; text-decoration: none; transition: background .15s;
    }
    .notice-bar--warning .notice-bar__action { background: #FAECE7; border-color: #F5C4B3; color: #993C1D; }
    .notice-bar__action:hover { background: #9FE1CB; }
    .notice-bar--warning .notice-bar__action:hover { background: #F5C4B3; }

    /* ── Ticket items ─────────────────────────────────────── */
    .ticket-item {
        display: block;
        padding: .95rem 1.25rem;
        border-bottom: 0.5px solid #f3f4f6;
        transition: background .15s;
    }
    .ticket-item:last-child { border-bottom: none; }
    .ticket-item:hover { background: #fafafa; }
    .ticket-avatar {
        width: 38px; height: 38px;
        border-radius: 50%;
        overflow: hidden;
        border: 0.5px solid #e5e7eb;
        flex-shrink: 0;
    }
    .ticket-avatar--initials {
        background: #E6F1FB;
        color: #185FA5;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        font-weight: 500;
        letter-spacing: .02em;
    }
    .ticket-title {
        font-size: 13px;
        font-weight: 500;
        color: #111827;
        margin: 0;
        transition: color .15s;
        line-height: 1.4;
    }
    .ticket-item:hover .ticket-title { color: #185FA5; }
    .ticket-badge {
        display: inline-flex;
        align-items: center;
        font-size: 10px;
        font-weight: 500;
        padding: 2px 8px;
        border-radius: 99px;
        white-space: nowrap;
    }
    .ticket-badge--cat  { background: #f3f4f6; color: #6b7280; }
    .ticket-badge--open { background: #FAEEDA; color: #854F0B; }

    /* ── Responsive ───────────────────────────────────────── */
    @media (max-width: 768px) {
        .stat-card__illustration { height: 52px; }
        .stat-card__illustration svg { width: 100px; height: 60px; }
        .stat-card__value { font-size: 20px; }
    }
</style>

@push('script')
    <script>
        const MONTHS = @json($months);
        const INVOICEMONTLYAMOUNT = @json($invoiceMonthlyAmount);
    </script>
    <script src="{{ asset('assets/libs/apexcharts/apexcharts.min.js') }}"></script>
    <script src="{{ asset('assets/js/pages/index-charts.js') }}"></script>
@endpush
@endsection