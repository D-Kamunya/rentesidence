@extends('owner.layouts.app')

@section('content')

<style>
/* ── Design Tokens ──────────────────────────────────────────────────── */
:root {
    --blue:         #185FA5;
    --blue-hover:   #0F4A84;
    --blue-light:   #E6F1FB;
    --blue-border:  #B5D4F4;
    --blue-faint:   #185ea56e;
    --blue-ghost:   #185ea51c;
    --green:        #1D9E75;
    --green-dark:   #0F6E56;
    --green-light:  #E1F5EE;
    --amber:        #854F0B;
    --amber-light:  #FAEEDA;
    --amber-border: #F5D9A8;
    --red:          #993C1D;
    --red-light:    #FAECE7;
    --red-border:   #f0b5a5;
    --gray-900:     #111827;
    --gray-800:     #1f2937;
    --gray-700:     #374151;
    --gray-500:     #6b7280;
    --gray-400:     #9ca3af;
    --gray-200:     #e5e7eb;
    --gray-100:     #f3f4f6;
    --gray-50:      #fafafa;
    --white:        #ffffff;
}

/* ── Buttons ────────────────────────────────────────────────────────── */
.ta-btn {
    display: inline-flex; align-items: center; justify-content: center;
    gap: 6px; font-size: 12px; font-weight: 500; padding: 8px 16px;
    border-radius: 7px; border: none; cursor: pointer;
    transition: all .13s; text-decoration: none; line-height: 1; width: 100%;
}
.ta-btn-primary { background: var(--blue); color: var(--white); }
.ta-btn-primary:hover { background: var(--blue-hover); transform: translateY(-1px); color: var(--white); }
.ta-btn-danger  { background: var(--red-light); color: var(--red); border: 0.5px solid var(--red-border); }
.ta-btn-danger:hover  { background: #fee2e2; color: #b91c1c; border-color: #b91c1c; }
.ta-btn-ghost   { background: var(--gray-100); color: var(--gray-700); border: 0.5px solid var(--gray-200); }
.ta-btn-ghost:hover   { background: var(--gray-200); }
.ta-btn-sm { padding: 6px 12px; font-size: 11px; }

/* ── Application Card ───────────────────────────────────────────────── */
.ta-card {
    background: var(--white);
    border: 0.5px solid var(--blue-faint);
    border-radius: 12px; overflow: hidden;
    box-shadow: 0 4px 12px rgba(0,0,0,.04), 0 0 0 1px rgba(24,95,165,.05), 0 6px 18px rgba(24,95,165,.06);
    transition: all .25s ease;
    display: flex; flex-direction: column; height: 100%;
}
.ta-card:hover {
    border-color: var(--blue); transform: translateY(-3px);
    box-shadow: 0 10px 25px rgba(0,0,0,.06), 0 0 0 1px rgba(24,95,165,.12), 0 12px 30px rgba(24,95,165,.18);
}
.ta-card-head {
    display: flex; align-items: center; gap: 12px;
    padding: 16px 20px 14px; border-bottom: 0.5px solid var(--gray-200);
    background: var(--gray-50);
}
.ta-avatar {
    width: 44px; height: 44px; min-width: 44px; border-radius: 10px;
    background: var(--blue-light); border: 2px solid var(--blue-border);
    display: flex; align-items: center; justify-content: center;
    font-size: 15px; font-weight: 600; color: var(--blue); text-transform: uppercase;
}
.ta-applicant-name {
    font-size: 15px; font-weight: 600; color: var(--blue); margin: 0 0 2px;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.ta-applicant-meta { font-size: 11px; color: var(--gray-400); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

.ta-badge {
    display: inline-flex; align-items: center; gap: 4px;
    font-size: 11px; font-weight: 500; padding: 3px 9px;
    border-radius: 99px; white-space: nowrap; margin-left: auto; flex-shrink: 0;
}
.ta-badge-pending  { background: var(--amber-light); color: var(--amber); border: 0.5px solid var(--amber-border); }
.ta-badge-accepted { background: var(--green-light); color: var(--green-dark); }
.ta-badge-rejected { background: var(--red-light);   color: var(--red); }

.ta-card-body { padding: 16px 20px; flex: 1; }

.ta-section-label {
    font-size: 10px; font-weight: 500; text-transform: uppercase;
    letter-spacing: .07em; color: var(--gray-400); margin-bottom: 10px; margin-top: 4px;
    display: flex; align-items: center; gap: 6px;
}
.ta-section-label::after { content: ''; flex: 1; height: 0.5px; background: var(--gray-200); }

.ta-info-row {
    display: flex; align-items: flex-start; gap: 8px;
    padding: 7px 0; border-bottom: 0.5px solid var(--gray-100); font-size: 12.5px;
}
.ta-info-row:last-child { border-bottom: none; }
.ta-info-label { font-size: 11px; font-weight: 500; color: var(--gray-400); min-width: 90px; flex-shrink: 0; }
.ta-info-value { font-size: 12.5px; font-weight: 500; color: var(--gray-700); word-break: break-word; }
.ta-info-value.mono {
    font-family: monospace; font-size: 11px; color: #0C447C;
    background: var(--blue-light); border: 0.5px solid var(--blue-border);
    padding: 2px 7px; border-radius: 5px;
}
.ta-rent-pill {
    display: inline-block; font-size: 13px; font-weight: 600; padding: 3px 10px;
    border-radius: 99px; background: var(--green-light); color: var(--green-dark); white-space: nowrap;
}
.ta-card-footer {
    padding: 14px 20px 18px; border-top: 0.5px solid var(--gray-200);
    background: var(--gray-50); display: flex; flex-direction: column; gap: 8px;
}

/* ── Summary Strip ──────────────────────────────────────────────────── */
.ta-summary-strip {
    display: flex; align-items: stretch; border: 0.5px solid var(--gray-200);
    border-radius: 12px; background: var(--white); overflow: hidden;
    margin-bottom: 24px; flex-wrap: wrap;
}
.ta-strip-item {
    flex: 1; min-width: 100px; padding: 14px 20px;
    display: flex; align-items: center; gap: 10px;
    border-right: 0.5px solid var(--gray-200);
}
.ta-strip-item:last-child { border-right: none; }
.ta-strip-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
.ta-strip-label { font-size: 10px; font-weight: 500; text-transform: uppercase; letter-spacing: .07em; color: var(--gray-400); margin-bottom: 2px; }
.ta-strip-value { font-size: 18px; font-weight: 600; color: var(--gray-900); line-height: 1; }

/* ── Filter Tabs ────────────────────────────────────────────────────── */
.ta-filter-bar { display: flex; align-items: center; flex-wrap: wrap; gap: 10px; margin-bottom: 24px; }
.ta-filter-tabs { display: flex; background: var(--gray-100); border-radius: 8px; padding: 3px; gap: 2px; }
.ta-tab {
    font-size: 12px; font-weight: 500; color: var(--gray-500);
    padding: 5px 14px; border-radius: 6px; cursor: pointer;
    border: none; background: transparent; transition: all .13s;
    display: flex; align-items: center; gap: 5px;
}
.ta-tab.active { background: var(--white); color: var(--gray-900); box-shadow: 0 1px 3px rgba(0,0,0,.08); }
.ta-tab-dot { width: 6px; height: 6px; border-radius: 50%; flex-shrink: 0; }

/* ── Empty State ────────────────────────────────────────────────────── */
.ta-empty { text-align: center; padding: 60px 20px; color: var(--gray-400); }
.ta-empty-icon {
    width: 64px; height: 64px; border-radius: 16px; background: var(--gray-100);
    display: flex; align-items: center; justify-content: center; margin: 0 auto 16px;
}
.ta-empty h5 { font-size: 15px; font-weight: 600; color: var(--gray-700); margin-bottom: 6px; }
.ta-empty p  { font-size: 13px; color: var(--gray-400); }

/* ── Modals ─────────────────────────────────────────────────────────── */
.ta-modal .modal-content {
    border-radius: 14px; border: none;
    box-shadow: 0 20px 60px rgba(0,0,0,.18); overflow: hidden;
}
.ta-modal .modal-header {
    background: var(--gray-50); border-bottom: 0.5px solid var(--gray-200);
    padding: 16px 20px 14px; display: flex; align-items: flex-start; gap: 12px;
}
.ta-modal .modal-eyebrow {
    font-size: 10px; font-weight: 500; text-transform: uppercase;
    letter-spacing: .07em; color: var(--gray-400); margin-bottom: 3px;
}
.ta-modal .modal-title { font-size: 15px; font-weight: 600; color: var(--gray-900); margin: 0; }
.ta-modal .ta-modal-close {
    width: 30px; height: 30px; border-radius: 7px; background: var(--gray-100);
    border: 0.5px solid var(--gray-200); display: flex; align-items: center;
    justify-content: center; cursor: pointer; transition: all .13s; color: var(--gray-500);
    padding: 0; margin-left: auto; flex-shrink: 0;
}
.ta-modal .ta-modal-close:hover { background: var(--gray-200); }
.ta-modal .modal-body  { padding: 20px; }
.ta-modal .modal-footer {
    background: var(--gray-50); border-top: 0.5px solid var(--gray-200);
    padding: 14px 20px; display: flex; align-items: center; gap: 10px;
}

/* ── Form elements inside modals ────────────────────────────────────── */
.ta-form-label {
    display: block; font-size: 10px; font-weight: 500;
    text-transform: uppercase; letter-spacing: .07em; color: var(--gray-400); margin-bottom: 8px;
}
.ta-form-control {
    width: 100%; border: 0.5px solid var(--gray-200); border-radius: 7px;
    padding: 8px 12px; font-size: 13px; color: var(--gray-700); outline: none;
    background: var(--white); transition: all .15s; font-family: inherit;
    appearance: none; -webkit-appearance: none;
}
.ta-form-control:focus { border-color: var(--blue); box-shadow: 0 0 0 3px rgba(24,95,165,.1); }
.ta-form-control::placeholder { color: var(--gray-400); }
.ta-form-control:disabled { background: var(--gray-50); color: var(--gray-500); cursor: not-allowed; }
select.ta-form-control {
    background-image: url("data:image/svg+xml,%3Csvg width='12' height='12' viewBox='0 0 12 12' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M2 4L6 8L10 4' stroke='%239ca3af' stroke-width='1.5' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
    background-repeat: no-repeat; background-position: right 10px center; padding-right: 30px;
}
.ta-form-group { margin-bottom: 16px; }
.ta-form-row   { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }

/* Info block inside assign modal */
.ta-assign-info {
    background: var(--blue-light); border: 0.5px solid var(--blue-border);
    border-radius: 8px; padding: 12px 14px; margin-bottom: 18px;
    font-size: 12.5px; color: var(--gray-800);
}
.ta-assign-info strong { color: var(--blue); }

/* Optional fields toggle */
.ta-optional-toggle {
    display: flex; align-items: center; gap: 6px; cursor: pointer;
    font-size: 11px; font-weight: 500; color: var(--blue);
    background: none; border: none; padding: 0; margin-bottom: 14px;
}
.ta-optional-section { display: none; }
.ta-optional-section.open { display: block; }

/* ── Responsive ─────────────────────────────────────────────────────── */
@media (max-width: 768px) {
    .ta-summary-strip  { flex-direction: column; }
    .ta-strip-item     { border-right: none; border-bottom: 0.5px solid var(--gray-200); }
    .ta-strip-item:last-child { border-bottom: none; }
    .ta-info-label     { min-width: 80px; }
    .ta-form-row       { grid-template-columns: 1fr; }
}
@media (max-width: 540px) {
    .ta-filter-bar  { flex-direction: column; align-items: stretch; }
    .ta-filter-tabs { width: 100%; }
}
</style>

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="page-content-wrapper bg-white p-30 radius-20">

                {{-- Page Title --}}
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between border-bottom mb-20">
                            <div class="page-title-left">
                                <h3 class="mb-sm-0">{{ __('Tenant Applications') }}</h3>
                            </div>
                            <div class="page-title-right">
                                <ol class="breadcrumb mb-0">
                                    <li class="breadcrumb-item">
                                        <a href="{{ route('owner.dashboard') }}" title="{{ __('Dashboard') }}">{{ __('Dashboard') }}</a>
                                    </li>
                                    <li class="breadcrumb-item">
                                        <a href="{{ route('owner.tenant.index') }}" title="{{ __('Tenants') }}">{{ __('Tenants') }}</a>
                                    </li>
                                    <li class="breadcrumb-item active" aria-current="page">{{ __('Applications') }}</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                @php
                    $pageTitle     = 'Tenant Applications';
                    $totalCount    = $applications->count();
                    $pendingCount  = $applications->where('status', 2)->count();
                    $acceptedCount = $applications->where('status', 1)->count();
                @endphp

                {{-- Summary Strip --}}
                <div class="ta-summary-strip">
                    <div class="ta-strip-item">
                        <div class="ta-strip-dot" style="background:var(--blue)"></div>
                        <div>
                            <div class="ta-strip-label">{{ __('Total') }}</div>
                            <div class="ta-strip-value" style="color:var(--blue)">{{ $totalCount }}</div>
                        </div>
                    </div>
                    <div class="ta-strip-item">
                        <div class="ta-strip-dot" style="background:var(--amber)"></div>
                        <div>
                            <div class="ta-strip-label">{{ __('Pending') }}</div>
                            <div class="ta-strip-value" style="color:var(--amber)">{{ $pendingCount }}</div>
                        </div>
                    </div>
                    <div class="ta-strip-item">
                        <div class="ta-strip-dot" style="background:var(--green)"></div>
                        <div>
                            <div class="ta-strip-label">{{ __('Assigned') }}</div>
                            <div class="ta-strip-value" style="color:var(--green)">{{ $acceptedCount }}</div>
                        </div>
                    </div>
                </div>

                {{-- Filter Tabs --}}
                <div class="ta-filter-bar">
                    <div class="ta-filter-tabs">
                        <button class="ta-tab active" data-filter="all">
                            <span class="ta-tab-dot" style="background:var(--blue)"></span>{{ __('All') }}
                        </button>
                        <button class="ta-tab" data-filter="pending">
                            <span class="ta-tab-dot" style="background:var(--amber)"></span>{{ __('Pending') }}
                        </button>
                        <button class="ta-tab" data-filter="accepted">
                            <span class="ta-tab-dot" style="background:var(--green)"></span>{{ __('Assigned') }}
                        </button>
                    </div>
                </div>

                {{-- Cards Grid --}}
                <div class="row g-4" id="applicationsGrid">

                    @forelse ($applications as $app)
                        @php
                            $unit     = $app->propertyUnit;
                            $property = $unit?->property;

                            $statusKey = match((int)$app->status) {
                                1 => 'accepted',
                                default                          => 'pending',
                            };
                            $statusText = match((int)$app->status) {
                                1 => __('Assigned'),
                                default                          => __('Pending'),
                            };
                            $initials = strtoupper(substr($app->first_name, 0, 1) . substr($app->last_name, 0, 1));
                        @endphp

                        <div class="col-12 col-md-6 col-xl-4 ta-card-col" data-status="{{ $statusKey }}">
                            <div class="ta-card">

                                {{-- Card Head --}}
                                <div class="ta-card-head">
                                    <div class="ta-avatar">{{ $initials }}</div>
                                    <div style="min-width:0;flex:1">
                                        <p class="ta-applicant-name">{{ $app->first_name }} {{ $app->last_name }}</p>
                                        <p class="ta-applicant-meta">{{ $app->email }}</p>
                                    </div>
                                    <span class="ta-badge ta-badge-{{ $statusKey }}">
                                        <span style="width:5px;height:5px;border-radius:50%;display:inline-block;
                                            background:{{ $statusKey === 'accepted' ? 'var(--green-dark)' : 'var(--amber)' }}"></span>
                                        {{ $statusText }}
                                    </span>
                                </div>

                                {{-- Card Body --}}
                                <div class="ta-card-body">

                                    <div class="ta-section-label">{{ __('Personal') }}</div>
                                    <div class="ta-info-row">
                                        <span class="ta-info-label">{{ __('Phone') }}</span>
                                        <span class="ta-info-value mono">{{ $app->contact_number }}</span>
                                    </div>
                                    @if($app->job)
                                    <div class="ta-info-row">
                                        <span class="ta-info-label">{{ __('Job') }}</span>
                                        <span class="ta-info-value">{{ $app->job }}</span>
                                    </div>
                                    @endif
                                    @if($app->age)
                                    <div class="ta-info-row">
                                        <span class="ta-info-label">{{ __('Age') }}</span>
                                        <span class="ta-info-value">{{ $app->age }}</span>
                                    </div>
                                    @endif
                                    @if($app->family_member)
                                    <div class="ta-info-row">
                                        <span class="ta-info-label">{{ __('Family') }}</span>
                                        <span class="ta-info-value">{{ $app->family_member }} {{ __('members') }}</span>
                                    </div>
                                    @endif

                                    <div class="ta-section-label" style="margin-top:12px">{{ __('Property & Unit') }}</div>
                                    <div class="ta-info-row">
                                        <span class="ta-info-label">{{ __('Property') }}</span>
                                        <span class="ta-info-value" style="color:var(--blue);font-weight:600">{{ $property?->name ?? '—' }}</span>
                                    </div>
                                    <div class="ta-info-row">
                                        <span class="ta-info-label">{{ __('Unit') }}</span>
                                        <span class="ta-info-value">{{ $unit?->unit_name ?? ('Unit ' . ($unit?->id ?? '—')) }}</span>
                                    </div>
                                    <div class="ta-info-row">
                                        <span class="ta-info-label">{{ __('Listed Rent') }}</span>
                                        <span class="ta-rent-pill">KES {{ number_format($unit?->general_rent ?? 0) }} / {{ __('mo') }}</span>
                                    </div>

                                    <div class="ta-section-label" style="margin-top:12px">{{ __('Address') }}</div>
                                    <div class="ta-info-row">
                                        <span class="ta-info-label">{{ __('Street') }}</span>
                                        <span class="ta-info-value">{{ $app->permanent_address }}</span>
                                    </div>
                                    <div class="ta-info-row">
                                        <span class="ta-info-label">{{ __('Location') }}</span>
                                        <span class="ta-info-value">{{ $app->permanent_city_id }}, {{ $app->permanent_state_id }}, {{ $app->permanent_country_id }}</span>
                                    </div>
                                    <div class="ta-info-row">
                                        <span class="ta-info-label">{{ __('ZIP') }}</span>
                                        <span class="ta-info-value mono">{{ $app->permanent_zip_code }}</span>
                                    </div>

                                </div>{{-- /.ta-card-body --}}

                                {{-- Card Footer --}}
                                <div class="ta-card-footer">
                                    @if((int)$app->status === 2)

                                        {{-- Assign → opens assign modal --}}
                                        <button type="button"
                                                class="ta-btn ta-btn-primary"
                                                data-bs-toggle="modal"
                                                data-bs-target="#assignModal{{ $app->id }}">
                                            <svg width="13" height="13" viewBox="0 0 13 13" fill="none" aria-hidden="true">
                                                <path d="M2 6.5L5.5 10L11 3" stroke="#fff" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                            {{ __('Assign as Tenant') }}
                                        </button>

                                        {{-- Reject + Delete --}}
                                        <button type="button"
                                                class="ta-btn ta-btn-danger"
                                                data-bs-toggle="modal"
                                                data-bs-target="#rejectModal{{ $app->id }}">
                                            <svg width="13" height="13" viewBox="0 0 13 13" fill="none" aria-hidden="true">
                                                <path d="M3 3l7 7M10 3l-7 7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                            </svg>
                                            {{ __('Reject & Remove') }}
                                        </button>

                                    @else
                                        <span class="ta-badge ta-badge-accepted w-100 justify-content-center py-2" style="margin-left:0">
                                            ✓ {{ __('Tenant Assigned') }}
                                        </span>
                                    @endif
                                </div>

                            </div>{{-- /.ta-card --}}
                        </div>{{-- /.col --}}

                        {{-- ── ASSIGN MODAL ─────────────────────────────────── --}}
                        @if((int)$app->status === 2)
                        <div class="modal fade ta-modal" id="assignModal{{ $app->id }}" tabindex="-1"
                             aria-labelledby="assignModalLabel{{ $app->id }}" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-lg">
                                <div class="modal-content">

                                    <div class="modal-header">
                                        <div>
                                            <p class="modal-eyebrow">{{ __('Tenant Application') }}</p>
                                            <h4 class="modal-title" id="assignModalLabel{{ $app->id }}">
                                                {{ __('Assign') }} {{ $app->first_name }} {{ $app->last_name }}
                                            </h4>
                                        </div>
                                        <button type="button" class="ta-modal-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}">
                                            <span class="iconify" data-icon="akar-icons:cross" style="font-size:13px"></span>
                                        </button>
                                    </div>

                                    <form class="ajax"
                                          action="{{ route('owner.tenant.applications.assign') }}"
                                          method="POST"
                                          data-handler="getShowMessage">
                                        @csrf

                                        {{-- Hidden: application id, property & unit pre-filled from the record --}}
                                        <input type="hidden" name="application_id" value="{{ $app->id }}">
                                        <input type="hidden" name="property_id"    value="{{ $property?->id }}">

                                        <div class="modal-body">

                                            {{-- Applicant summary --}}
                                            <div class="ta-assign-info">
                                                <strong>{{ $app->first_name }} {{ $app->last_name }}</strong>
                                                &nbsp;·&nbsp; {{ $app->email }}
                                                &nbsp;·&nbsp; {{ $app->contact_number }}<br>
                                                <span style="color:var(--gray-700)">
                                                    {{ __('Applying for') }}: <strong>{{ $property?->name ?? '—' }}</strong>
                                                    &mdash; {{ $unit?->unit_name ?? 'Unit ' . ($unit?->id ?? '—') }}
                                                    &nbsp;|&nbsp; KES {{ number_format($unit?->general_rent ?? 0) }} / {{ __('mo') }}
                                                </span>
                                            </div>

                                            {{-- Unit (pre-filled, owner can change if needed) --}}
                                            <div class="ta-form-group">
                                                <label class="ta-form-label" for="unit_id_{{ $app->id }}">
                                                    {{ __('Unit to Assign') }} <span style="color:var(--red)">*</span>
                                                </label>
                                                <select class="ta-form-control" id="unit_id_{{ $app->id }}" name="unit_id" required>
                                                    {{--
                                                        Ideally load all vacant units for this property via a relationship.
                                                        We pre-select the applied unit; owner can pick another if needed.
                                                    --}}
                                                    <option value="{{ $unit?->id }}" selected>
                                                        {{ $unit?->unit_name ?? 'Unit ' . ($unit?->id ?? '') }}
                                                        — KES {{ number_format($unit?->general_rent ?? 0) }} / mo
                                                    </option>
                                                    @foreach ($property?->propertyUnits ?? [] as $pu)
                                                        @if($pu->id !== $unit?->id)
                                                        <option value="{{ $pu->id }}">
                                                            {{ $pu->unit_name ?? 'Unit ' . $pu->id }}
                                                            — KES {{ number_format($pu->general_rent ?? 0) }} / mo
                                                        </option>
                                                        @endif
                                                    @endforeach
                                                </select>
                                            </div>

                                            {{-- Lease dates + rent + due date --}}
                                            <div class="ta-form-row">
                                                <div class="ta-form-group">
                                                    <label class="ta-form-label" for="lease_start_{{ $app->id }}">
                                                        {{ __('Lease Start Date') }} <span style="color:var(--red)">*</span>
                                                    </label>
                                                    <div class="custom-datepicker" style="position:relative">
                                                        <input type="text"
                                                               id="lease_start_{{ $app->id }}"
                                                               class="ta-form-control datepicker"
                                                               name="lease_start_date"
                                                               autocomplete="off"
                                                               placeholder="dd-mm-yyyy"
                                                               required>
                                                        <i class="ri-calendar-2-line" style="position:absolute;right:10px;top:50%;transform:translateY(-50%);color:var(--gray-400);pointer-events:none"></i>
                                                    </div>
                                                </div>
                                                <div class="ta-form-group">
                                                    <label class="ta-form-label" for="lease_end_{{ $app->id }}">
                                                        {{ __('Lease End Date') }}
                                                        <span style="color:var(--gray-400);font-weight:400;text-transform:none;letter-spacing:0">({{ __('optional') }})</span>
                                                    </label>
                                                    <div class="custom-datepicker" style="position:relative">
                                                        <input type="text"
                                                               id="lease_end_{{ $app->id }}"
                                                               class="ta-form-control datepicker"
                                                               name="lease_end_date"
                                                               autocomplete="off"
                                                               placeholder="dd-mm-yyyy">
                                                        <i class="ri-calendar-2-line" style="position:absolute;right:10px;top:50%;transform:translateY(-50%);color:var(--gray-400);pointer-events:none"></i>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="ta-form-row">
                                                <div class="ta-form-group">
                                                    <label class="ta-form-label" for="general_rent_{{ $app->id }}">
                                                        {{ __('Monthly Rent (KES)') }} <span style="color:var(--red)">*</span>
                                                    </label>
                                                    <input type="number"
                                                           id="general_rent_{{ $app->id }}"
                                                           class="ta-form-control"
                                                           name="general_rent"
                                                           value="{{ $unit?->general_rent ?? '' }}"
                                                           min="1"
                                                           required>
                                                </div>
                                                <div class="ta-form-group">
                                                    <label class="ta-form-label" for="due_date_{{ $app->id }}">
                                                        {{ __('Rent Due Day of Month') }} <span style="color:var(--red)">*</span>
                                                    </label>
                                                    <input type="number"
                                                           id="due_date_{{ $app->id }}"
                                                           class="ta-form-control"
                                                           name="due_date"
                                                           placeholder="e.g. 5"
                                                           min="1" max="31"
                                                           required>
                                                </div>
                                            </div>

                                            {{-- Optional: security deposit + late fee --}}
                                            <button type="button"
                                                    class="ta-optional-toggle"
                                                    data-target="optionals_{{ $app->id }}">
                                                <svg width="12" height="12" viewBox="0 0 12 12" fill="none" aria-hidden="true">
                                                    <path d="M6 1v10M1 6h10" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                                </svg>
                                                {{ __('Add Security Deposit & Late Fee (optional)') }}
                                            </button>

                                            <div class="ta-optional-section" id="optionals_{{ $app->id }}">
                                                <div class="ta-form-row">
                                                    <div class="ta-form-group">
                                                        <label class="ta-form-label">{{ __('Security Deposit Type') }}</label>
                                                        <select class="ta-form-control" name="security_deposit_type">
                                                            <option value="">— {{ __('Select') }} —</option>
                                                            <option value="fixed">{{ __('Fixed') }}</option>
                                                            <option value="percentage">{{ __('Percentage') }}</option>
                                                        </select>
                                                    </div>
                                                    <div class="ta-form-group">
                                                        <label class="ta-form-label">{{ __('Security Deposit Amount') }}</label>
                                                        <input type="number" class="ta-form-control" name="security_deposit" min="0" placeholder="0">
                                                    </div>
                                                </div>
                                                <div class="ta-form-row">
                                                    <div class="ta-form-group">
                                                        <label class="ta-form-label">{{ __('Late Fee Type') }}</label>
                                                        <select class="ta-form-control" name="late_fee_type">
                                                            <option value="">— {{ __('Select') }} —</option>
                                                            <option value="fixed">{{ __('Fixed') }}</option>
                                                            <option value="percentage">{{ __('Percentage') }}</option>
                                                        </select>
                                                    </div>
                                                    <div class="ta-form-group">
                                                        <label class="ta-form-label">{{ __('Late Fee Amount') }}</label>
                                                        <input type="number" class="ta-form-control" name="late_fee" min="0" placeholder="0">
                                                    </div>
                                                </div>
                                                <div class="ta-form-group">
                                                    <label class="ta-form-label">{{ __('Incident Receipt') }}</label>
                                                    <input type="number" class="ta-form-control" name="incident_receipt" min="0" placeholder="0">
                                                </div>
                                            </div>

                                        </div>{{-- /.modal-body --}}

                                        <div class="modal-footer">
                                            <button type="submit" class="ta-btn ta-btn-primary" style="width:auto;padding:8px 20px">
                                                <svg width="13" height="13" viewBox="0 0 13 13" fill="none" aria-hidden="true">
                                                    <path d="M2 6.5L5.5 10L11 3" stroke="#fff" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                                {{ __('Confirm & Assign Tenant') }}
                                            </button>
                                            <button type="button" class="ta-btn ta-btn-ghost" data-bs-dismiss="modal" style="width:auto;padding:8px 20px">
                                                {{ __('Cancel') }}
                                            </button>
                                        </div>

                                    </form>
                                </div>
                            </div>
                        </div>

                        {{-- ── REJECT / DELETE MODAL ────────────────────────── --}}
                        <div class="modal fade ta-modal" id="rejectModal{{ $app->id }}" tabindex="-1"
                             aria-labelledby="rejectModalLabel{{ $app->id }}" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-sm">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <div>
                                            <p class="modal-eyebrow">{{ __('Application') }}</p>
                                            <h4 class="modal-title" id="rejectModalLabel{{ $app->id }}">{{ __('Reject Application') }}</h4>
                                        </div>
                                        <button type="button" class="ta-modal-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}">
                                            <span class="iconify" data-icon="akar-icons:cross" style="font-size:13px"></span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <p style="font-size:13px;color:var(--gray-700);margin:0;line-height:1.6">
                                            {{ __('This will notify') }}
                                            <strong>{{ $app->first_name }} {{ $app->last_name }}</strong>
                                            {{ __('of the rejection and permanently remove their application. This cannot be undone.') }}
                                        </p>
                                    </div>
                                    <div class="modal-footer">
                                        <form action="{{ route('owner.tenant.applications.destroy', $app->id) }}"
                                              method="POST"
                                              class="ajax"
                                              data-handler="getShowMessage"
                                              style="width:100%;display:flex;gap:8px">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="ta-btn ta-btn-danger" style="flex:1">
                                                {{ __('Reject & Remove') }}
                                            </button>
                                            <button type="button" class="ta-btn ta-btn-ghost" data-bs-dismiss="modal" style="flex:1">
                                                {{ __('Cancel') }}
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                        {{-- ── END MODALS ───────────────────────────────────── --}}

                    @empty
                        <div class="col-12">
                            <div class="ta-empty">
                                <div class="ta-empty-icon">
                                    <svg width="28" height="28" viewBox="0 0 28 28" fill="none" aria-hidden="true">
                                        <path d="M14 3C8 3 3 8 3 14s5 11 11 11 11-5 11-11S20 3 14 3zm0 6v5m0 4v1"
                                              stroke="#9ca3af" stroke-width="1.8" stroke-linecap="round"/>
                                    </svg>
                                </div>
                                <h5>{{ __('No Applications Yet') }}</h5>
                                <p>{{ __('Tenant applications for your properties will appear here.') }}</p>
                            </div>
                        </div>
                    @endforelse

                </div>{{-- /#applicationsGrid --}}

            </div><!-- /.page-content-wrapper -->
        </div><!-- /.container-fluid -->
    </div><!-- /.page-content -->
</div><!-- /.main-content -->

<script>
document.addEventListener('DOMContentLoaded', function () {

    // ── Filter tabs ────────────────────────────────────────────────────
    var tabs  = document.querySelectorAll('.ta-tab');
    var cards = document.querySelectorAll('.ta-card-col');

    tabs.forEach(function (tab) {
        tab.addEventListener('click', function () {
            tabs.forEach(function (t) { t.classList.remove('active'); });
            tab.classList.add('active');
            var filter = tab.dataset.filter;
            cards.forEach(function (card) {
                card.style.display = (filter === 'all' || card.dataset.status === filter) ? '' : 'none';
            });
        });
    });

    // ── Optional section toggle (per-card, inside assign modal) ───────
    document.querySelectorAll('.ta-optional-toggle').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var target = document.getElementById(btn.dataset.target);
            if (target) target.classList.toggle('open');
        });
    });

    // ── Reset modal forms on close ─────────────────────────────────────
    document.querySelectorAll('.modal').forEach(function (modalEl) {
        modalEl.addEventListener('hidden.bs.modal', function () {
            var form = modalEl.querySelector('form');
            if (form) form.reset();
            // Also close any open optional sections inside this modal
            modalEl.querySelectorAll('.ta-optional-section').forEach(function (s) {
                s.classList.remove('open');
            });
        });
    });
});
</script>

@endsection

@push('style')
    @include('common.layouts.datatable-style')
@endpush

@push('script')
    @include('common.layouts.datatable-script')
@endpush