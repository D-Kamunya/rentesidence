@extends('owner.layouts.app')

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="page-content-wrapper bg-white p-30 radius-20">

                {{-- ── Page header ─────────────────────────────────────── --}}
                <div class="prop-page-header mb-4">
                    <div>
                        <h2 class="prop-page-title">
                            {{ $pageTitle }}
                            <span class="prop-count-badge">{{ $properties->total() }}</span>
                        </h2>
                        <nav aria-label="breadcrumb">
                            <ol class="prop-breadcrumb">
                                <li>
                                    <a href="{{ route('owner.dashboard') }}">{{ __('Dashboard') }}</a>
                                    <svg width="8" height="8" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                        <polyline points="9 18 15 12 9 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </li>
                                <li aria-current="page">{{ __('Properties') }}</li>
                            </ol>
                        </nav>
                    </div>

                    {{-- ── Add / upgrade button ──────────────────────── --}}
                    <div>
                        @php
                            $subscriptionService = app(\App\Services\SubscriptionService::class);
                            $unitLimit      = $subscriptionService->getUnitLimit();
                            $remainingUnits = $unitLimit['remaining'] ?? 0;
                            $totalUnits     = $unitLimit['total'] ?? 0;
                            $hasReachedLimit = $remainingUnits <= 0 && $totalUnits > 0;
                            $isNearLimit     = $remainingUnits > 0 && $remainingUnits <= 3;
                        @endphp

                        @if($hasReachedLimit)
                            <a href="{{ route('owner.subscription.index', ['current_plan' => 'no']) }}"
                               class="prop-btn prop-btn--upgrade"
                               title="{{ __('You\'ve used all :total units. Upgrade to add more.', ['total' => $totalUnits]) }}">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>
                                </svg>
                                {{ __('Upgrade to Add Property') }}
                            </a>
                        @elseif($isNearLimit)
                            <a href="{{ route('owner.property.add') }}"
                               class="prop-btn prop-btn--primary prop-btn--near-limit"
                               title="{{ __('Only :remaining units remaining', ['remaining' => $remainingUnits]) }}">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none">
                                    <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/>
                                </svg>
                                {{ __('Add Property') }}
                                <span class="prop-remaining-badge">{{ $remainingUnits }} {{ __('left') }}</span>
                            </a>
                        @else
                            <a href="{{ route('owner.property.add') }}" class="prop-btn prop-btn--primary">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none">
                                    <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/>
                                </svg>
                                {{ __('Add Property') }}
                            </a>
                        @endif
                    </div>
                </div>

                {{-- ── Card grid or datatable ───────────────────────────── --}}
                @if (getOption('app_card_data_show', 1) == 1)

                    {{-- Search (card mode) --}}
                    <div class="prop-search-bar mb-4">
                        <div class="prop-search-wrap">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="1.8"/><path d="M16.5 16.5l4 4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                            <input type="text" id="propertySearch" placeholder="{{ __('Search properties by name…') }}" autocomplete="off">
                        </div>
                    </div>

                    <div id="propertyGridWrap">
                        @include('owner.property.partials.property-cards')
                    </div>

                @else
                    {{-- ── Datatable view (unchanged) ────────────────── --}}
                    <div class="ow-card" style="padding:0;">
                        <div class="account-settings-content-box p-20">
                            <div class="table-responsive">
                                <table id="allPropertiesDataTable" class="table responsive theme-border p-20">
                                    <thead>
                                        <tr>
                                            <th>{{ __('SL') }}</th>
                                            <th>{{ __('Image') }}</th>
                                            <th data-priority="1">{{ __('Name') }}</th>
                                            <th>{{ __('Address') }}</th>
                                            <th>{{ __('Rooms') }}</th>
                                            <th>{{ __('Unit') }}</th>
                                            <th>{{ __('available') }}</th>
                                            <th>{{ __('Action') }}</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                    {{ $properties->links() }}
                @endif

            </div>
        </div>
    </div>
</div>

<input type="hidden" id="getAllPropertyRoute" value="{{ route('owner.property.allProperty') }}">
@endsection

@push('style')
<style>
/* ── Design tokens ────────────────────────────────────────────── */
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
.prop-page-header {
    display:flex; align-items:flex-start; justify-content:space-between;
    flex-wrap:wrap; gap:1rem; margin-bottom:1.5rem;
}
.prop-page-title {
    font-size:22px; font-weight:500; color:var(--gray-900);
    margin:0 0 6px; display:flex; align-items:center; gap:10px;
}
.prop-count-badge {
    font-size:12px; font-weight:500; color:#0C447C;
    background:var(--blue-light); border:0.5px solid var(--blue-border);
    border-radius:99px; padding:2px 10px; font-family:monospace;
}

.prop-breadcrumb {
    display:flex; align-items:center; gap:6px; list-style:none;
    padding:0; margin:0; font-size:12px; color:var(--gray-400);
}
.prop-breadcrumb li { display:flex; align-items:center; gap:6px; }
.prop-breadcrumb a  { color:var(--blue); font-weight:500; text-decoration:none; }

/* ── Buttons ──────────────────────────────────────────────────── */
.prop-btn {
    display:inline-flex; align-items:center; gap:6px;
    font-size:13px; font-weight:500; padding:8px 16px;
    border-radius:7px; border:none; text-decoration:none;
    cursor:pointer; transition:all .13s; white-space:nowrap;
}
.prop-btn--primary {
    background:var(--blue); color:var(--white);
}
.prop-btn--primary:hover {
    background:var(--blue-hover); color:var(--white);
    transform:translateY(-1px); box-shadow:0 4px 12px rgba(24,95,165,.25);
}
.prop-btn--upgrade {
    background:linear-gradient(135deg,#F59E0B 0%,#D97706 100%);
    color:var(--white); border:0.5px solid #D97706; position:relative; overflow:hidden;
}
.prop-btn--upgrade:hover {
    background:linear-gradient(135deg,#D97706 0%,#B45309 100%);
    color:var(--white); transform:translateY(-1px);
    box-shadow:0 4px 12px rgba(245,158,11,.3);
}
.prop-btn--upgrade::after {
    content:''; position:absolute; top:-2px; right:-2px;
    width:8px; height:8px; border-radius:50%; background:#FEF3C7;
    animation:upgradePulse 1.5s infinite;
}
@keyframes upgradePulse {
    0%,100% { opacity:.6; transform:scale(1); }
    50%      { opacity:1;  transform:scale(1.3); }
}
.prop-btn--near-limit { position:relative; }
.prop-remaining-badge {
    display:inline-flex; align-items:center;
    font-size:10px; font-weight:600; padding:1px 7px;
    border-radius:99px; background:rgba(255,255,255,.25); margin-left:4px;
}

/* ── Property grid ────────────────────────────────────────────── */
.prop-grid {
    display:grid;
    grid-template-columns:repeat(4, 1fr);
    gap:1.25rem;
    margin-bottom:1.5rem;
}
@media(max-width:1400px){ .prop-grid { grid-template-columns:repeat(3, 1fr); } }
@media(max-width:992px) { .prop-grid { grid-template-columns:repeat(2, 1fr); } }
@media(max-width:540px) { .prop-grid { grid-template-columns:1fr; } }

/* ── Property card ────────────────────────────────────────────── */
.prop-card {
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
.prop-card:hover {
    border-color:var(--blue);
    transform:translateY(-3px);
    box-shadow: 0 10px 25px rgba(0,0,0,.06),
                0 0 0 1px rgba(24,95,165,.12),
                0 12px 30px rgba(24,95,165,.18);
}

/* Thumbnail */
.prop-card__img-wrap {
    display:block; overflow:hidden;
    height:160px; flex-shrink:0;
}
.prop-card__img {
    width:100%; height:100%; object-fit:cover;
    transition:transform .35s ease;
}
.prop-card:hover .prop-card__img { transform:scale(1.04); }

/* Body */
.prop-card__body { padding:14px 16px; flex:1; }

/* Title row */
.prop-card__title-row {
    display:flex; align-items:flex-start;
    justify-content:space-between; gap:8px; margin-bottom:8px;
}
.prop-card__name {
    font-size:15px; font-weight:600; color:var(--blue);
    text-decoration:none; line-height:1.3;
    white-space:nowrap; overflow:hidden; text-overflow:ellipsis;
    transition:color .13s;
}
.prop-card__name:hover { color:var(--blue-hover); }

.prop-card__more {
    flex-shrink:0; width:28px; height:28px; border-radius:7px;
    display:inline-flex; align-items:center; justify-content:center;
    color:var(--gray-500); background:var(--gray-100);
    font-size:14px; text-decoration:none;
    transition:background .13s, color .13s;
}
.prop-card__more:hover { background:var(--blue); color:var(--white); }

/* Address */
.prop-card__address {
    display:flex; align-items:flex-start; gap:5px;
    font-size:12px; color:var(--gray-500); margin-bottom:12px;
    line-height:1.4;
}
.prop-card__address svg { flex-shrink:0; margin-top:1px; color:var(--gray-400); }

/* Stats row */
.prop-card__stats {
    display:flex; align-items:center;
    background:var(--gray-50); border:0.5px solid var(--gray-200);
    border-radius:8px; overflow:hidden;
}
.prop-stat {
    display:flex; flex-direction:column; align-items:center;
    padding:8px 0; flex:1;
}
.prop-stat__divider { width:0.5px; align-self:stretch; background:var(--gray-200); }
.prop-stat__label {
    font-size:10px; font-weight:500; text-transform:uppercase;
    letter-spacing:.07em; color:var(--gray-400); margin-bottom:2px;
}
.prop-stat__value {
    font-size:14px; font-weight:600; color:var(--gray-800);
}
.prop-stat__value--green { color:var(--green); }

/* Footer */
.prop-card__footer {
    border-top:0.5px solid var(--gray-200);
    background:var(--gray-50);
}
.prop-card__cta {
    display:flex; align-items:center; justify-content:center; gap:5px;
    width:100%; padding:10px; font-size:12px; font-weight:500;
    color:var(--blue); text-decoration:none;
    transition:background .15s, color .15s;
}
.prop-card__cta:hover {
    background:var(--blue); color:var(--white);
}

/* ── Outer card (datatable view) ──────────────────────────────── */
.ow-card {
    background:var(--white);
    border:0.5px solid var(--blue-faint);
    border-radius:12px; overflow:hidden;
    box-shadow: 0 4px 12px rgba(0,0,0,.04),
                0 0 0 1px rgba(24,95,165,.05),
                0 6px 18px rgba(24,95,165,.06);
}

/* ── Empty state ──────────────────────────────────────────────── */
.prop-empty {
    display:flex; flex-direction:column; align-items:center;
    justify-content:center; padding:4rem 1rem; text-align:center;
}
.prop-empty__img  { max-width:200px; width:100%; opacity:.85; }
.prop-empty__text { margin-top:1.25rem; font-size:16px; font-weight:500; color:var(--gray-500); }

/* ── Pagination ───────────────────────────────────────────────── */
.prop-pagination { display:flex; justify-content:flex-end; margin-top:.5rem; }

/* ── Utilities ────────────────────────────────────────────────── */
.mb-4 { margin-bottom:1.5rem; }
.p-20 { padding:20px; }
</style>
@endpush

@if (getOption('app_card_data_show', 1) != 1)
    @push('style')
        @include('common.layouts.datatable-style')
    @endpush
    @push('script')
        @include('common.layouts.datatable-script')
        <script src="{{ asset('assets/js/custom/propery-datatable.js') }}"></script>
    @endpush
@endif
@if (getOption('app_card_data_show', 1) == 1)
    @push('style')
    <style>
        .prop-search-bar { display:flex; }
        .prop-search-wrap { position:relative; display:flex; align-items:center; }
        .prop-search-wrap svg { position:absolute; left:12px; color:#9ca3af; pointer-events:none; }
        .prop-search-wrap input { border:0.5px solid #e5e7eb; border-radius:8px; padding:9px 12px 9px 38px;
            font-size:13px; color:#374151; background:#fff; outline:none; width:320px; max-width:100%;
            transition:border-color .15s, box-shadow .15s; }
        .prop-search-wrap input:focus { border-color:#185FA5; box-shadow:0 0 0 3px rgba(24,95,165,.1); }
    </style>
    @endpush
    @push('script')
    <script>
        $(document).ready(function () {
            var $search = $('#propertySearch');
            var $wrap   = $('#propertyGridWrap');
            var baseUrl = $('#getAllPropertyRoute').val();
            var timer;
            function fetchProps(page) {
                $.ajax({
                    url: baseUrl, type: 'GET',
                    data: { page: page || 1, search: $search.val() },
                    success: function (res) { $wrap.html(res.cards); }
                });
            }
            $search.on('input', function () {
                clearTimeout(timer);
                timer = setTimeout(function () { fetchProps(1); }, 350);
            });
            // Delegated so it keeps working after AJAX re-render
            $(document).on('click', '#propertyGridWrap .prop-pagination a', function (e) {
                e.preventDefault();
                var href = $(this).attr('href');
                var page = new URL(href, window.location.origin).searchParams.get('page') || 1;
                fetchProps(page);
                $('html, body').animate({ scrollTop: 0 }, 200);
            });
        });
    </script>
    @endpush
@endif
