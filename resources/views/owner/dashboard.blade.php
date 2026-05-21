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
                        @php
                            $subscriptionService = app(\App\Services\SubscriptionService::class);
                            $unitLimit = $subscriptionService->getUnitLimit();
                            $remainingUnits = $unitLimit['remaining'] ?? 0;
                            $totalUnits = $unitLimit['total'] ?? 0;
                            $hasReachedLimit = $remainingUnits <= 0 && $totalUnits > 0;
                            $isNearLimit = $remainingUnits > 0 && $remainingUnits <= 3;
                        @endphp

                        @if($hasReachedLimit)
                            {{-- Limit reached - Upgrade CTA --}}
                            <a href="{{ route('owner.subscription.index', ['current_plan' => 'no']) }}" 
                            class="theme-btn-primary upgrade-btn"
                            title="{{ __('You\'ve used all :total units. Upgrade to add more.', ['total' => $totalUnits]) }}">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>
                                </svg>
                                {{ __('Upgrade to Add Property') }}
                            </a>
                        @elseif($isNearLimit)
                            {{-- Near limit - Add with warning --}}
                            <a href="{{ route('owner.property.add') }}" 
                            class="theme-btn-primary near-limit-btn"
                            title="{{ __('Only :remaining units remaining', ['remaining' => $remainingUnits]) }}">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none">
                                    <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/>
                                </svg>
                                {{ __('Add Property') }}
                                <span class="remaining-badge">{{ $remainingUnits }} left</span>
                            </a>
                        @else
                            {{-- Normal - Plenty of units --}}
                            <a href="{{ route('owner.property.add') }}" class="theme-btn-primary">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none">
                                    <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/>
                                </svg>
                                {{ __('Add Property') }}
                            </a>
                        @endif
                    </div>

                    {{-- Pending Tickets Nudge --}}
                    @if (isset($pendingTickets) && $pendingTickets > 0)
                        <div class="notice-bar notice-bar--warning mb-4">
                            <div class="notice-bar__left">
                                <div class="notice-bar__icon">
                                    <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
                                        <path d="M8 1.5a6.5 6.5 0 100 13 6.5 6.5 0 000-13z" stroke="currentColor" stroke-width="1.4" />
                                        <path d="M8 7v4M8 5v.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                                    </svg>
                                </div>
                                <div>
                                    <div class="notice-bar__text">
                                        You have <strong>{{ $pendingTickets }} pending {{ Str::plural('ticket', $pendingTickets) }}</strong>
                                        requiring attention
                                    </div>
                                    <div class="notice-bar__sub">Respond quickly to maintain tenant satisfaction</div>
                                </div>
                            </div>
                            <a href="{{ route('owner.ticket.index') }}" class="notice-bar__action">
                                <svg width="10" height="10" viewBox="0 0 16 16" fill="none">
                                    <path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                        stroke-linejoin="round" />
                                </svg>
                                View Tickets
                            </a>
                        </div>
                    @endif

                    {{-- Use Marketplace Nudge --}}
                    @if($showMarketplacePrompt)
                        <div class="notice-bar notice-bar--marketplace mb-4 alert alert-dismissible fade show">
                            <div class="notice-bar__left">
                                <div class="notice-bar__icon">
                                    <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
                                        <path d="M8 1.5a6.5 6.5 0 100 13 6.5 6.5 0 000-13z" stroke="currentColor" stroke-width="1.4"/>
                                        <path d="M5.5 8h5M8 5.5v5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                    </svg>
                                </div>
                                <div>
                                    <div class="notice-bar__text">
                                        <strong>Friendly tip:</strong> Listing your products in the marketplace can help you reach more tenants and boost your income flow.
                                    </div>
                                    <div class="notice-bar__sub">It’s quick to get started and completely optional.</div>
                                </div>
                            </div>
                            <div class="notice-bar__actions">
                                <a href="{{ route('owner.products.create') }}" class="notice-bar__action">
                                    <svg width="10" height="10" viewBox="0 0 16 16" fill="none">
                                        <path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    List a Product
                                </a>
                                <button type="button" class="notice-bar__action notice-bar__dismiss" data-bs-dismiss="alert">
                                    Maybe later
                                </button>
                            </div>
                        </div>
                    @endif

                    {{-- Pending Product Orders --}}
                    @if($pendingProductOrdersCount > 0)
                        <div class="notice-bar notice-bar--orderpending mb-4">
                            <div class="notice-bar__left">
                                <div class="notice-bar__orderpendingicon">
                                    <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
                                        <path d="M8 1.5a6.5 6.5 0 100 13 6.5 6.5 0 000-13z" stroke="currentColor" stroke-width="1.4" />
                                        <path d="M8 7v4M8 5v.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                                    </svg>
                                </div>
                                <div>
                                    <div>
                                        <div class="notice-bar__text">
                                            You have <strong>{{ $pendingProductOrdersCount }} new pending {{ Str::plural('order', $pendingProductOrdersCount) }}</strong> awaiting dispatch
                                        </div>
                                        <div class="notice-bar__sub">Process these orders promptly to maintain tenant satisfaction</div>
                                    </div>
                                </div>
                            </div>
                            <a href="{{ route('owner.order.index') }}" class="notice-bar__action">
                                <svg width="10" height="10" viewBox="0 0 16 16" fill="none">
                                    <path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                        stroke-linejoin="round" />
                                </svg>
                                View Orders
                            </a>
                        </div>
                    @endif

                    {{-- Pending Tenant Appplications --}}
                    @if($pendingApplicationsCount > 0)
                        <div class="notice-bar notice-bar--orderpending mb-4">
                            <div class="notice-bar__left">
                                <div class="notice-bar__orderpendingicon">
                                    <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
                                        <path d="M8 1.5a6.5 6.5 0 100 13 6.5 6.5 0 000-13z" stroke="currentColor" stroke-width="1.4" />
                                        <path d="M8 7v4M8 5v.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                                    </svg>
                                </div>
                                <div>
                                    <div>
                                        <div class="notice-bar__text">
                                        You have <strong>{{ $pendingApplicationsCount }} pending tenant {{ Str::plural('application', $pendingApplicationsCount) }}</strong>.
                                        </div>
                                        <div class="notice-bar__sub">Review pending applications to welcome new tenants sooner.</div>
                                    </div>
                                </div>
                            </div>
                            <a href="{{ route('owner.tenant.applications.index') }}" class="notice-bar__action">
                                <svg width="10" height="10" viewBox="0 0 16 16" fill="none">
                                    <path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                        stroke-linejoin="round" />
                                </svg>
                                Review
                            </a>
                        </div>
                    @endif

                    {{-- SMS Credits Nudge --}}
                    @if($smsCredits <= $smsLowThreshold || $smsFailedCount > 0)
                        <div class="notice-bar notice-bar--warning mb-4">
                            <div class="notice-bar__left">
                                <div class="notice-bar__icon">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none">
                                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"
                                            stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </div>
                                <div>
                                    @if($smsCredits === 0)
                                        <div class="notice-bar__text">
                                            <strong>{{ __('SMS paused') }}</strong> — {{ __('you have 0 credits remaining. SMS Tenant notifications are on hold.') }}
                                        </div>
                                    @else
                                        <div class="notice-bar__text">
                                            <strong>{{ __('SMS credits running low') }}</strong> — {{ number_format($smsCredits) }} {{ __('credits remaining.') }}
                                        </div>
                                    @endif
                                    @if($smsFailedCount > 0)
                                        <div class="notice-bar__sub">
                                            {{ $smsFailedCount }} {{ __('message(s) unsent in the last 30 days.') }}
                                            <a href="{{ route('owner.sms.credits.index') }}" class="theme-link" style="font-size:12px;">
                                                {{ __('View & retry') }}
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="notice-bar__actions">
                                <a href="#" data-bs-toggle="modal" data-bs-target="#smsTopupModal"
                                class="notice-bar__action notice-bar--warning">
                                    {{ __('Top Up Credits') }}
                                </a>
                                <a href="{{ route('owner.sms.credits.index') }}" class="notice-bar__action notice-bar--warning">
                                    {{ __('Manage SMS') }}
                                </a>
                            </div>
                        </div>
                    @endif

                    {{-- Summary Cards --}}
                    <div class="row g-3 mb-4">

                        {{-- Total Properties --}}
                        <div class="col-6 col-md-4 col-lg-3">
                            <div class="stat-card stat-card--illustrated"
                            onclick="window.location='{{ route('owner.property.allProperty') }}'">
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
                            <div class="stat-card stat-card--illustrated"
                                onclick="window.location='{{ route('owner.property.allUnit') }}'">
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
                                    <p class="stat-card__value stat-card__value--blue">{{ $totalUnitsdash }}</p>
                                </div>
                            </div>
                        </div>

                        {{-- Total Tenants --}}
                        <div class="col-6 col-md-4 col-lg-3">
                            <div class="stat-card stat-card--illustrated"
                             onclick="window.location='{{ route('owner.tenant.index') }}'">
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
                                    <p class="stat-card__value stat-card__value--amber">{{ $totalUnitsdash - $totalTenants }}</p>
                                </div>
                            </div>
                        </div>

                        {{-- Total Maintainers --}}
                        <div class="col-6 col-md-4 col-lg-3">
                            <div class="stat-card stat-card--illustrated"
                             onclick="window.location='{{ route('owner.maintainer.index') }}'">
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

                        {{-- Total Tickets --}}
                        <div class="col-6 col-md-4 col-lg-3">
                            <div class="stat-card stat-card--illustrated"
                             onclick="window.location='{{ route('owner.ticket.index') }}'">
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
                                    <p class="stat-card__label">Total Tickets</p>
                                    <p class="stat-card__value stat-card__value--coral">{{ $tickets->count() }}</p>
                                </div>
                            </div>
                        </div>

                        {{-- Listed Products --}}
                        <div class="col-6 col-md-4 col-lg-3">
                            <div class="stat-card stat-card--illustrated"
                             onclick="window.location='{{ route('owner.products.index') }}'">
                                <div class="stat-card__illustration stat-card__illustration--blue">
                                    <svg viewBox="0 0 120 72" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <rect x="14" y="18" width="68" height="46" fill="#E7F0FA" rx="4"/>
                                        <circle cx="14" cy="41" r="4" fill="#fff"/>
                                        <circle cx="82" cy="41" r="4" fill="#fff"/>
                                        <rect x="24" y="28" width="48" height="14" fill="#1D3C99" rx="2" opacity=".12"/>
                                        <line x1="29" y1="33" x2="67" y2="33" stroke="#1D3C99" stroke-width="2" stroke-linecap="round" opacity=".3"/>
                                        <line x1="29" y1="38" x2="52" y2="38" stroke="#1D3C99" stroke-width="2" stroke-linecap="round" opacity=".2"/>
                                        <rect x="28" y="48" width="40" height="10" fill="#1D3C99" rx="2" opacity=".08"/>
                                        <line x1="33" y1="52" x2="63" y2="52" stroke="#1D3C99" stroke-width="1.5" stroke-linecap="round" opacity=".18"/>
                                        <rect x="70" y="16" width="24" height="10" fill="#1D3C99" rx="2" opacity=".55"/>
                                        <text x="82" y="23" font-size="5.5" fill="#fff" text-anchor="middle" font-weight="bold" font-family="system-ui,sans-serif">LISTED</text>
                                        <circle cx="100" cy="48" r="10" fill="#1D3C99" opacity=".08"/>
                                        <path d="M96 48h8M100 44v8" stroke="#1D3C99" stroke-width="1.8" stroke-linecap="round" opacity=".45"/>
                                    </svg>
                                </div>
                                <div class="stat-card__content">
                                    @if($listedProductsCount > 0)
                                        <p class="stat-card__label">Listed Products</p>
                                        <p class="stat-card__value stat-card__value--blue">{{ $listedProductsCount }}</p>
                                    @else
                                        <p class="stat-card__label">No Products Yet</p>
                                        <p class="stat-card__value stat-card__value--blue">0</p>
                                        <a href="{{ route('owner.products.create') }}" class="stat-card__cta">
                                            Add Your First Product
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- SMS Credits --}}
                        <div class="col-6 col-md-4 col-lg-3">
                            <div class="stat-card stat-card--illustrated">
                                <div class="stat-card__illustration stat-card__illustration--{{ $smsCredits <= $smsLowThreshold ? 'coral' : 'teal' }}">
                                    <svg viewBox="0 0 120 72" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <rect x="10" y="14" width="76" height="48" rx="6"
                                            fill="{{ $smsCredits <= $smsLowThreshold ? '#FAECE7' : '#E1F5EE' }}"/>
                                        <rect x="10" y="14" width="76" height="48" rx="6"
                                            stroke="{{ $smsCredits <= $smsLowThreshold ? '#993C1D' : '#1D9E75' }}"
                                            stroke-width="2" fill="none" opacity=".3"/>
                                        <line x1="24" y1="30" x2="72" y2="30"
                                            stroke="{{ $smsCredits <= $smsLowThreshold ? '#993C1D' : '#1D9E75' }}"
                                            stroke-width="2.5" stroke-linecap="round" opacity=".5"/>
                                        <line x1="24" y1="38" x2="60" y2="38"
                                            stroke="{{ $smsCredits <= $smsLowThreshold ? '#993C1D' : '#1D9E75' }}"
                                            stroke-width="2.5" stroke-linecap="round" opacity=".35"/>
                                        <line x1="24" y1="46" x2="50" y2="46"
                                            stroke="{{ $smsCredits <= $smsLowThreshold ? '#993C1D' : '#1D9E75' }}"
                                            stroke-width="2.5" stroke-linecap="round" opacity=".2"/>
                                        <polygon points="86,52 96,40 106,52"
                                                fill="{{ $smsCredits <= $smsLowThreshold ? '#993C1D' : '#1D9E75' }}"
                                                opacity=".6"/>
                                    </svg>
                                </div>
                                <div class="stat-card__content">
                                    <p class="stat-card__label">{{ __('SMS Credits') }}</p>
                                    <p class="stat-card__value stat-card__value--{{ $smsCredits <= $smsLowThreshold ? 'coral' : 'teal' }}">
                                        {{ number_format($smsCredits) }}
                                    </p>
                                    <a href="#" data-bs-toggle="modal" data-bs-target="#smsTopupModal"
                                    class="stat-card__cta"
                                    style="background:{{ $smsCredits <= $smsLowThreshold ? '#FAECE7' : '#E1F5EE' }};
                                            border-color:{{ $smsCredits <= $smsLowThreshold ? '#F5C4B3' : '#9FE1CB' }};
                                            color:{{ $smsCredits <= $smsLowThreshold ? '#993C1D' : '#0F6E56' }};">
                                        {{ $smsCredits <= $smsLowThreshold ? __('Top Up Now') : __('Buy More') }}
                                    </a>
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

{{-- ══ SMS Top-Up Modal ═══════════════════════════════════════════════ --}}
<div class="modal fade" id="smsTopupModal" tabindex="-1" aria-labelledby="smsTopupModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:460px;">
        <div class="modal-content" style="border-radius:16px;border:none;overflow:hidden;">

            {{-- Header --}}
            <div style="background:linear-gradient(135deg,#185FA5,#1D9E75);padding:1.25rem 1.5rem;display:flex;align-items:center;justify-content:space-between;">
                <div style="display:flex;align-items:center;gap:10px;">
                    <img src="{{ asset('assets/images/gateway-icon/mpesa.jpg') }}"
                         alt="M-Pesa" style="width:36px;height:36px;border-radius:8px;object-fit:cover;">
                    <div>
                        <div style="font-size:15px;font-weight:600;color:#fff;">{{ __('Buy SMS Credits') }}</div>
                        <div style="font-size:11px;color:rgba(255,255,255,.75);">
                            {{ __('KSh') }} {{ number_format($smsPricePerCredit, 2) }} {{ __('per credit') }}
                        </div>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            {{-- Body --}}
            <div style="padding:1.5rem;">

                {{-- Current balance --}}
                <div style="background:#f9fafb;border:0.5px solid #e5e7eb;border-radius:10px;padding:10px 14px;margin-bottom:1.25rem;display:flex;align-items:center;justify-content:space-between;">
                    <span style="font-size:13px;color:#6b7280;">{{ __('Current balance') }}</span>
                    <span style="font-size:15px;font-weight:700;color:{{ $smsCredits <= $smsLowThreshold ? '#993C1D' : '#1D9E75' }};">
                        {{ number_format($smsCredits) }} {{ __('credits') }}
                    </span>
                </div>

                {{-- Quick-pick packs --}}
                <p style="font-size:12px;font-weight:500;color:#374151;margin-bottom:8px;">{{ __('Select a pack') }}</p>
                <div style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:1rem;">
                    @foreach([50, 100, 250, 500, 1000] as $pack)
                        <button type="button" class="sms-pack-btn" data-qty="{{ $pack }}">
                            <span style="font-size:16px;font-weight:700;color:#111827;display:block;">{{ $pack }}</span>
                            <span style="font-size:10px;color:#9ca3af;display:block;">credits</span>
                            <span style="font-size:11px;font-weight:600;color:#185FA5;display:block;margin-top:2px;">
                                KSh {{ number_format($pack * $smsPricePerCredit, 2) }}
                            </span>
                        </button>
                    @endforeach
                </div>

                {{-- Custom qty --}}
                <div style="display:flex;align-items:center;gap:8px;margin-bottom:1.25rem;">
                    <input type="number" id="modalCustomQty" min="10"
                           placeholder="{{ __('Custom qty (min 10)') }}"
                           style="flex:1;padding:9px 12px;border:1.5px solid #e5e7eb;border-radius:8px;font-size:13px;outline:none;">
                    <span style="font-size:13px;font-weight:600;color:#185FA5;white-space:nowrap;">
                        KSh <span id="modalCustomTotal">0.00</span>
                    </span>
                </div>

                {{-- Phone number --}}
                <div style="margin-bottom:1.25rem;">
                    <label style="display:block;font-size:12px;font-weight:500;color:#374151;margin-bottom:5px;">
                        {{ __('M-Pesa Phone Number') }}
                        <span style="color:#9ca3af;font-weight:400;"> — {{ __('edit if different from account') }}</span>
                    </label>
                    <div style="display:flex;align-items:center;border:1.5px solid #e5e7eb;border-radius:8px;overflow:hidden;">
                        <span style="padding:0 10px;font-size:13px;color:#6b7280;background:#f9fafb;border-right:1.5px solid #e5e7eb;height:40px;display:flex;align-items:center;">🇰🇪</span>
                        <input type="tel" id="modalPhone"
                               value="{{ auth()->user()->contact_number ?? '' }}"
                               style="flex:1;padding:9px 12px;border:none;outline:none;font-size:13px;">
                    </div>
                </div>

                {{-- Info note --}}
                <div style="background:#FFFBEB;border:0.5px solid #FDE68A;border-radius:8px;padding:9px 12px;font-size:12px;color:#92400E;display:flex;gap:8px;margin-bottom:1.25rem;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" style="flex-shrink:0;margin-top:1px;">
                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.8"/>
                        <line x1="12" y1="8" x2="12" y2="12" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                        <line x1="12" y1="16" x2="12.01" y2="16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                    <span>{{ __('Ensure your Safaricom line is active and your phone is unlocked. The STK push will arrive within seconds.') }}</span>
                </div>

                {{-- Close button (mobile-friendly) --}}
                <button type="button" class="btn w-100 mb-3" data-bs-dismiss="modal"
                        style="background:#f3f4f6;color:#6b7280;font-size:13px;font-weight:500;border-radius:10px;padding:10px;border:none;">
                    {{ __('Cancel') }}
                </button>

                {{-- Pay button --}}
                <button type="button" id="modalBuyBtn" disabled
                        style="display:flex;align-items:center;justify-content:center;gap:10px;width:100%;padding:12px;background:#185FA5;color:#fff;font-size:14px;font-weight:600;border:none;border-radius:10px;cursor:pointer;opacity:.5;transition:all .15s;">
                    <img src="{{ asset('assets/images/gateway-icon/mpesa.jpg') }}"
                         alt="" style="width:20px;height:20px;border-radius:4px;object-fit:cover;">
                    {{ __('Pay') }}
                    <span id="modalBuyAmount"></span>
                    {{ __('via M-Pesa') }}
                </button>

            </div>

            {{-- M-Pesa waiting overlay (inside modal) --}}
            <div id="modalMpesaWait" style="display:none;position:absolute;inset:0;background:rgba(255,255,255,.96);border-radius:16px;flex-direction:column;align-items:center;justify-content:center;gap:16px;text-align:center;padding:2rem;">
                <img src="{{ asset('assets/images/gateway-icon/mpesa.jpg') }}"
                     alt="M-PESA" style="width:70px;height:70px;border-radius:12px;object-fit:cover;">
                <div>
                    <p style="font-size:14px;font-weight:600;color:#111827;margin:0 0 6px;">{{ __('Check your phone') }}</p>
                    <p style="font-size:13px;color:#6b7280;margin:0;">{{ __('Enter your M-Pesa PIN to complete payment.') }}</p>
                    <p style="font-size:13px;color:#6b7280;margin:6px 0 0;">
                        {{ __('Time remaining:') }} <strong id="modalTimer" style="color:#185FA5;">2:00</strong>
                    </p>
                </div>
                <img src="{{ asset('assets/images/loading.svg') }}" alt="Loading" style="width:32px;">
            </div>

        </div>
    </div>
</div>

{{-- Preloader (page-level, same as products checkout — used after Pusher redirect) --}}
<div id="mpesa-preloader" style="display:none;">
    <div id="mpesa-preloaderInner">
        <img src="{{ asset('assets/images/gateway-icon/mpesa.jpg') }}" alt="M-PESA">
        <div>
            <p>{{ __('Please follow the instructions and do not refresh or leave this page.') }}</p>
            <p>{{ __('This may take up to') }} <span id="mpesa-timer">2:00</span> {{ __('minute(s).') }}</p>
            <p>{{ __('You will receive a prompt on your mobile number to enter your PIN to authorize payment.') }}</p>
            <p>{{ __('Please ensure your phone is on and unlocked. Thank you.') }}</p>
        </div>
        <img src="{{ asset('assets/images/loading.svg') }}" alt="Loading">
    </div>
</div>

@push('style')
<style>
/* ── SMS pack buttons (in modal) ─────────────────────────── */
.sms-pack-btn {
    display:flex; flex-direction:column; align-items:center;
    padding:9px 14px; border:1.5px solid #e5e7eb; border-radius:10px;
    background:#fff; cursor:pointer; transition:all .15s; gap:1px;
}
.sms-pack-btn:hover, .sms-pack-btn.active {
    border-color:#185FA5; background:#E6F1FB;
}
/* ── Page-level preloader ─────────────────────────────────── */
#mpesa-preloader {
    position:fixed; inset:0; background:rgba(0,0,0,.55);
    z-index:9999; display:flex; align-items:center; justify-content:center;
}
#mpesa-preloaderInner {
    background:#fff; border-radius:16px; padding:2rem;
    max-width:420px; width:90%; display:flex; flex-direction:column;
    align-items:center; gap:16px; text-align:center;
    box-shadow:0 20px 40px rgba(0,0,0,.2);
}
#mpesa-preloaderInner img:first-child { width:80px; height:80px; object-fit:contain; border-radius:12px; }
#mpesa-preloaderInner p { font-size:13px; color:#374151; margin:0; line-height:1.6; }
#mpesa-timer { font-weight:600; color:#185FA5; }
#mpesa-preloaderInner img:last-child { width:36px; }
</style>
@endpush

@push('script')
<script>
(function () {
    'use strict';

    const pricePerSms = {{ $smsPricePerCredit }};
    let selectedQty   = 0;

    const customQtyEl   = document.getElementById('modalCustomQty');
    const customTotalEl = document.getElementById('modalCustomTotal');
    const buyBtn        = document.getElementById('modalBuyBtn');
    const buyAmountEl   = document.getElementById('modalBuyAmount');
    const phoneEl       = document.getElementById('modalPhone');

    function updateBuyBtn() {
        const valid = selectedQty >= 10 && phoneEl.value.trim().length >= 9;
        buyBtn.disabled = !valid;
        buyBtn.style.opacity = valid ? '1' : '.5';
        buyBtn.style.cursor  = valid ? 'pointer' : 'not-allowed';
        const total = (selectedQty * pricePerSms).toFixed(2);
        buyAmountEl.textContent = selectedQty >= 10 ? 'KSh ' + total : '';
    }

    // Quick-pick
    document.querySelectorAll('.sms-pack-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.sms-pack-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            selectedQty = parseInt(this.dataset.qty);
            customQtyEl.value = selectedQty;
            customTotalEl.textContent = (selectedQty * pricePerSms).toFixed(2);
            updateBuyBtn();
        });
    });

    // Custom qty
    customQtyEl.addEventListener('input', function () {
        selectedQty = parseInt(this.value) || 0;
        customTotalEl.textContent = (selectedQty * pricePerSms).toFixed(2);
        document.querySelectorAll('.sms-pack-btn').forEach(b => b.classList.remove('active'));
        updateBuyBtn();
    });

    // Phone change
    phoneEl.addEventListener('input', updateBuyBtn);

    // ── Modal waiting overlay timer ───────────────────────────
    let modalTimer;
    const modalWait  = document.getElementById('modalMpesaWait');
    const modalTimerEl = document.getElementById('modalTimer');

    function showModalWait() {
        let countdown = 120;
        modalWait.style.display = 'flex';
        modalTimer = setInterval(() => {
            const m = Math.floor(countdown / 60);
            const s = countdown % 60;
            modalTimerEl.textContent = `${m}:${s < 10 ? '0' + s : s}`;
            if (countdown-- <= 0) clearInterval(modalTimer);
        }, 1000);
    }

    function hideModalWait() {
        clearInterval(modalTimer);
        modalWait.style.display = 'none';
    }

    // ── Page-level preloader ──────────────────────────────────
    let pageTimer;
    function showPagePreloader() {
        let countdown = 120;
        const el = document.getElementById('mpesa-timer');
        document.getElementById('mpesa-preloader').style.display = 'flex';
        pageTimer = setInterval(() => {
            const m = Math.floor(countdown / 60);
            const s = countdown % 60;
            el.textContent = `${m}:${s < 10 ? '0' + s : s}`;
            if (countdown-- <= 0) clearInterval(pageTimer);
        }, 1000);
    }

    // ── Pay button ────────────────────────────────────────────
    buyBtn.addEventListener('click', function () {
        if (selectedQty < 10 || phoneEl.value.trim().length < 9) return;

        const total = (selectedQty * pricePerSms).toFixed(2);
        showModalWait();

        const formData = new FormData();
        formData.append('_token',    '{{ csrf_token() }}');
        formData.append('quantity',   selectedQty);
        formData.append('cartTotal',  total);
        formData.append('phone',      phoneEl.value.trim());

        fetch('{{ route("owner.sms.credits.checkout") }}', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const pusher  = new Pusher(window.Laravel.pusher_key, { cluster: window.Laravel.pusher_cluster });
                    const channel = pusher.subscribe('transaction.' + data.transaction_id);

                    const redirectTimeout = setTimeout(() => {
                        hideModalWait();
                        showPagePreloader();
                        window.location.href = data.redirect_url;
                    }, 120000);

                    channel.bind('MpesaTransactionProcessed', function () {
                        clearTimeout(redirectTimeout);
                        hideModalWait();
                        showPagePreloader();
                        window.location.href = data.redirect_url + '&callback=true&stk_success=true';
                    });

                    channel.bind('MpesaTransactionDeclined', function () {
                        clearTimeout(redirectTimeout);
                        hideModalWait();
                        if (typeof toastr !== 'undefined') {
                            toastr.error('{{ __("Payment was declined. Please try again.") }}');
                        }
                    });

                } else {
                    hideModalWait();
                    if (typeof toastr !== 'undefined') {
                        toastr.error(data.error || '{{ __("Payment failed. Please try again.") }}');
                    }
                }
            })
            .catch(() => {
                hideModalWait();
                if (typeof toastr !== 'undefined') {
                    toastr.error('{{ __("Something went wrong. Please try again.") }}');
                }
            });
    });

    // Reset modal state when closed
    document.getElementById('smsTopupModal').addEventListener('hidden.bs.modal', function () {
        hideModalWait();
        document.querySelectorAll('.sms-pack-btn').forEach(b => b.classList.remove('active'));
        customQtyEl.value = '';
        customTotalEl.textContent = '0.00';
        selectedQty = 0;
        updateBuyBtn();
    });

})();
</script>
@endpush

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

    /* Upgrade button */
    .upgrade-btn {
        background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%) !important;
        border: 0.5px solid #D97706 !important;
        position: relative;
        overflow: hidden;
    }
    
    .upgrade-btn:hover {
        background: linear-gradient(135deg, #D97706 0%, #B45309 100%) !important;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
    }
    
    .upgrade-btn::before {
        content: '';
        position: absolute;
        top: -2px;
        right: -2px;
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #FEF3C7;
        animation: upgradePulse 1.5s infinite;
    }
    
    @keyframes upgradePulse {
        0%, 100% { opacity: 0.6; transform: scale(1); }
        50% { opacity: 1; transform: scale(1.3); }
    }
    
    /* Near limit button */
    .near-limit-btn {
        position: relative;
    }
    
    .remaining-badge {
        display: inline-flex;
        align-items: center;
        font-size: 10px;
        font-weight: 600;
        padding: 1px 6px;
        border-radius: 99px;
        background: rgba(255,255,255,0.25);
        margin-left: 6px;
        letter-spacing: 0.02em;
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
    .stat-card__cta {
    display: inline-block;
    margin-top: .5rem;
    padding: .35rem .75rem;
    font-size: 10px;
    font-weight: 500;
    border-radius: 6px;
    background: #E7F0FA;   /* matches illustration background */
    border: 1px solid #B3D1F5;
    color: #1D3C99;
    transition: background .2s ease, color .2s ease;
    }
    .stat-card__cta:hover {
        background: #B3D1F5;
        color: #132B71;
        text-decoration: none;
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
    .notice-bar--orderpending .notice-bar-orderpending__icon {
        background: #E8EEF6;       /* soft blue-gray icon background */
        color: #2F5D9A;            /* calm blue icon color */
    }
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

    /* ── Marketplace Notice Bar ───────────────────────── */
        .notice-bar--marketplace { 
            background: #F0F9FF; 
            border-color: #B3D9F5; 
        }
        .notice-bar--marketplace .notice-bar__icon { 
            background: #E6F2FB; 
            color: #0F4C81; 
        }
        .notice-bar--marketplace .notice-bar__text { 
            color: #0F4C81; 
        }
        .notice-bar--marketplace .notice-bar__sub { 
            color: #0F4C81; 
        }
        .notice-bar--marketplace .notice-bar__action { 
            background: #E6F2FB; 
            border: 0.5px solid #B3D9F5; 
            color: #0F4C81; 
        }
        .notice-bar--marketplace .notice-bar__action:hover { 
            background: #B3D9F5; 
        }
        .notice-bar__dismiss { 
            background: transparent; 
            border: none; 
            color: #0F4C81; 
            font-size: 11px; 
            font-weight: 500; 
            cursor: pointer; 
        }
        .notice-bar__actions { 
            display: flex; 
            gap: 8px; 
        }


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