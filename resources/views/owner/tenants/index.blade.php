@extends('owner.layouts.app')

@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="page-content-wrapper bg-white p-30 radius-20">

                    {{-- Page Header --}}
                    <div class="ow-page-header mb-4">
                        <div>
                            <h2 class="ow-title">{{ $pageTitle }}</h2>
                            <nav aria-label="breadcrumb">
                                <ol class="ow-breadcrumb">
                                    <li>
                                        <a href="{{ route('owner.dashboard') }}">{{ __('Dashboard') }}</a>
                                    </li>
                                    <li aria-current="page">
                                        <svg width="8" height="8" viewBox="0 0 16 16" fill="none">
                                            <path d="M6 4l4 4-4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                        {{ $pageTitle }}
                                    </li>
                                </ol>
                            </nav>
                        </div>
                    </div>

                    {{-- Toolbar --}}
                    <div class="ow-toolbar mb-4">
                        <div class="ow-toolbar__filters">
                            @if (getOption('app_card_data_show', 1) == 1)
                                <div class="ow-filter-group">
                                    <label class="ow-filter-label">{{ __('Property') }}</label>
                                    <select class="ow-select property_id">
                                        <option value="0">-- {{ __('Select Property') }} --</option>
                                        @foreach ($properties as $property)
                                            <option value="{{ $property->id }}">{{ $property->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="ow-filter-group">
                                    <label class="ow-filter-label">{{ __('Unit') }}</label>
                                    <select class="ow-select unit_id">
                                        <option value="0" selected>-- {{ __('Select Unit') }} --</option>
                                    </select>
                                </div>
                            @endif

                            {{-- NEW: Search Box --}}
                            <div class="ow-filter-group">
                                <label class="ow-filter-label">{{ __('Search') }}</label>
                                <div class="ow-search-wrap">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none">
                                        <circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="1.8"/>
                                        <path d="M16.5 16.5l4 4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                    </svg>
                                    <input type="text" id="tenantSearch" placeholder="{{ __('Tenant name, property, unit…') }}">
                                </div>
                            </div>

                            {{--Clear Button --}}
                            <div class="ow-filter-group ow-filter-group--clear">
                                <label class="ow-filter-label">&nbsp;</label>
                                <button type="button" class="ow-btn ow-btn--ghost ow-btn--clear" id="clearTenantFilters" style="display:none">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none">
                                        <path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                    </svg>
                                    {{ __('Clear') }}
                                </button>
                            </div>

                            @if (getOption('app_card_data_show', 1) == 1)
                                <div class="ow-filter-group">
                                    <label class="ow-filter-label">&nbsp;</label>
                                </div>
                            @endif
                        </div>

                        <div class="ow-toolbar__actions">
                            <a href="{{ route('owner.tenant.create') }}" class="ow-btn ow-btn--purple">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none">
                                    <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                </svg>
                                {{ __('Add New Tenant') }}
                            </a>
                        </div>
                    </div>

                    {{-- Main Card --}}
                    <div class="ow-card" id="tenantContainer">
                        @if (getOption('app_card_data_show', 1) == 1)
                            {{-- Card View --}}
                            <div class="row" id="tenantCards">
                                @include('owner.tenants.partials.cards')
                            </div>
                        @else
                            {{-- Table View --}}
                            <div class="table-responsive">
                                <table id="allTenantDataTable" class="table ow-table dt-responsive">
                                    <thead>
                                        <tr>
                                            <th>{{ __('SL') }}</th>
                                            <th data-priority="1">{{ __('Name') }}</th>
                                            <th></th>
                                            <th>{{ __('Property') }}</th>
                                            <th>{{ __('Unit') }}</th>
                                            <th>{{ __('Contact No.') }}</th>
                                            <th>{{ __('Current Rent') }}</th>
                                            <th>{{ __('Last Rent Paid') }}</th>
                                            <th>{{ __('Previous Due') }}</th>
                                            <th>{{ __('Status') }}</th>
                                            <th style="text-align:right">{{ __('Action') }}</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        @endif
                        <div id="paginationLinks" class="text-center mt-3">  {{-- ← AJAX targets this --}}
                            {{ $tenants->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <input type="hidden" id="getAllTenantRoute" value="{{ route('owner.tenant.index', ['type' => 'all']) }}">
    <input type="hidden" id="getPropertyUnitsRoute" value="{{ route('owner.property.getPropertyUnits') }}">
@endsection

@if (getOption('app_card_data_show', 1) != 1)
    @push('style')
        @include('common.layouts.datatable-style')
    @endpush
    @push('script')
        @include('common.layouts.datatable-script')
        <script src="{{ asset('assets/js/custom/tenant-datatable.js') }}"></script>
    @endpush
@endif

@push('script')
<script src="{{ asset('assets/js/custom/tenant-list.js') }}"></script>
<script>
    $(document).ready(function () {
        const $search      = $('#tenantSearch');
        const $property    = $('.property_id');
        const $unit        = $('.unit_id');
        const $clear       = $('#clearTenantFilters');
        const $cards       = $('#tenantCards');       // wraps the forelse rows
        const $pagination  = $('#paginationLinks');
        const baseUrl      = $('#getAllTenantRoute').val();

        let debounceTimer;

        // ── Core fetch ────────────────────────────────────────────
        function fetchTenants(page = 1) {
            $.ajax({
                url: baseUrl,
                type: 'GET',
                data: {
                    page,
                    search:      $search.val(),
                    property_id: $property.val(),
                    unit_id:     $unit.val(),
                },
                success: function (res) {
                    $cards.html(res.cards);
                    $pagination.html(res.pagination);
                }
            });
        }

        // ── Events ────────────────────────────────────────────────
        $search.on('input', function () {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => fetchTenants(), 350); // debounce typing
        });

        $property.on('change', function () { fetchTenants(); });
        $unit.on('change',     function () { fetchTenants(); });

        // Pagination: delegated so it works after AJAX re-render
        $(document).on('click', '#paginationLinks a', function (e) {
            e.preventDefault();
            const url  = $(this).attr('href');
            const page = new URL(url).searchParams.get('page') || 1;
            fetchTenants(page);
            $('html, body').animate({ scrollTop: 0 }, 200);
        });

        // ── Clear ─────────────────────────────────────────────────
        $clear.on('click', function () {
            $search.val('');
            $property.val('0');
            $unit.html('<option value="0">-- {{ __("Select Unit") }} --</option>');
            $clear.hide();
            fetchTenants();
        });

        // Show/hide clear button reactively
        function toggleClear() {
            const active = $search.val() ||
                        ($property.val() && $property.val() !== '0') ||
                        ($unit.val()     && $unit.val()     !== '0');
            $clear.toggle(!!active);
        }
        $search.on('input', toggleClear);
        $property.on('change', toggleClear);
        $unit.on('change', toggleClear);
    });
</script>
@endpush
@push('style')
<style>
            /* ── Tenant Card ──────────────────────────────────────────── */
            .ow-tenant-card {
                background: #fff;
                border: 0.5px solid #185ea56e;
                border-radius: 14px;
                overflow: hidden;
                transition: all .25s ease;
                height: 100%;
                display: flex;
                flex-direction: column;
                position: relative;
                box-shadow:
                    0 4px 12px rgba(0,0,0,0.04),
                    0 0 0 1px rgba(24,95,165,0.05),
                    0 6px 18px rgba(24,95,165,0.06);
            }

            .ow-tenant-card::after {
                content: "";
                position: absolute;
                inset: 0;
                border-radius: 14px;
                pointer-events: none;
                transition: all .2s ease;
            }

            .ow-tenant-card:hover {
                border-color: #185FA5;
                transform: translateY(-3px);
                box-shadow:
                    0 10px 25px rgba(0,0,0,0.06),
                    0 0 0 1px rgba(24,95,165,0.12),
                    0 12px 30px rgba(24,95,165,0.18);
            }

            .ow-tenant-card:hover::after {
                box-shadow: inset 0 0 0 1px rgba(24,95,165,.15);
            }

            /* ── Card Header ──────────────────────────────────────────── */
            .ow-tenant-header {
                padding: 20px 20px 12px;
            }

            .ow-tenant-header .d-flex {
                gap: 10px;
            }

            .ow-tenant-header .flex-grow-1 {
                min-width: 0; /* prevents long names from squeezing the edit button */
            }

            .ow-tenant-header .ow-act {
                flex-shrink: 0;
                align-self: flex-start;
                opacity: 1;
                background: #f0f4fa;
                border: 0.5px solid #c7d9f0;
                color: #185FA5;
                border-radius: 8px;
                padding: 6px 8px;
                transition: background .15s, color .15s, border-color .15s;
            }

            .ow-tenant-header .ow-act:hover {
                background: #185FA5;
                color: #fff;
                border-color: #185FA5;
            }

            /* ── Avatar ───────────────────────────────────────────────── */
            .ow-tenant-avatar {
                width: 46px;
                height: 46px;
                border-radius: 10px;
                background-size: cover;
                background-position: center;
                border: 2px solid #e0eaf5;
                flex-shrink: 0;
            }

            /* ── Name & Email ─────────────────────────────────────────── */
            .ow-tenant-name {
                font-size: 15px;
                font-weight: 600;
                color: #185FA5;
                margin: 0 0 2px;
                transition: color .15s;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }

            .ow-tenant-email {
                font-size: 12px;
                color: #6b7280;
                margin: 0;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }

            /* ── Info Rows ────────────────────────────────────────────── */
            .ow-tenant-info {
                padding: 0 20px 16px;
                flex-grow: 1;
            }

            .ow-info-row {
                display: flex;
                justify-content: space-between;
                align-items: center;
                border-bottom: 0.5px solid #f3f4f6;
                padding: 11px 0;
                font-size: 13px;
                gap: 10px;
            }

            .ow-info-row:last-child,
            .ow-info-row.border-0 {
                border-bottom: none;
            }

            .ow-info-label {
                color: #9ca3af;
                font-weight: 500;
                font-size: 12px;
                flex-shrink: 0;
            }

            .ow-info-value {
                font-weight: 500;
                color: #1f2937;
                text-align: right;
                font-size: 12.5px;
            }

            .ow-info-value a { color: #185FA5; text-decoration: none; }
            .ow-info-value a:hover { text-decoration: underline; }

            /* ── Footer ───────────────────────────────────────────────── */
            .ow-tenant-footer {
                padding: 16px 20px 20px;
                border-top: 1px solid #f3f4f6;
            }

            .ow-tenant-footer .ow-btn {
                justify-content: center;
                font-weight: 600;
                letter-spacing: .02em;
                position: relative;
                overflow: hidden;
            }

            .ow-tenant-footer .ow-btn--primary {
                background: #185FA5;
                color: #fff;
            }

            .ow-tenant-footer .ow-btn--primary:hover {
                background: #0F4A84;
                color: #fff;
            }

            /* ── Search ───────────────────────────────────────────────── */
            .ow-search-wrap {
                position: relative;
                display: flex;
                align-items: center;
            }

            .ow-search-wrap svg {
                position: absolute;
                left: 10px;
                color: #9ca3af;
                pointer-events: none;
            }

            .ow-search-wrap input {
                border: 0.5px solid #e5e7eb;
                border-radius: 7px;
                padding: 7px 10px 7px 34px;
                font-size: 13px;
                color: #374151;
                background: #fff;
                outline: none;
                width: 260px;
                transition: border-color .15s, box-shadow .15s;
            }

            .ow-search-wrap input:focus {
                border-color: #185FA5;
                box-shadow: 0 0 0 3px rgba(24,95,165,.1);
            }

            /* ── Shared UI primitives ─────────────────────────────────── */
            .ow-page-header { display:flex; align-items:flex-start; justify-content:space-between; flex-wrap:wrap; gap:1rem; }
            .ow-title { font-size:22px; font-weight:500; color:#111827; margin:0 0 6px; }
            .ow-breadcrumb { list-style:none; display:flex; align-items:center; gap:6px; margin:0; padding:0; font-size:12px; color:#9ca3af; }
            .ow-breadcrumb a { color:#185FA5; text-decoration:none; font-weight:500; }

            .ow-toolbar { display:flex; align-items:flex-end; justify-content:space-between; gap:12px; flex-wrap:wrap; }
            .ow-toolbar__filters { display:flex; align-items:flex-end; gap:10px; flex-wrap:wrap; }
            .ow-toolbar__actions { display:flex; align-items:center; gap:8px; }

            .ow-filter-group { display:flex; flex-direction:column; gap:5px; }
            .ow-filter-label { font-size:10px; font-weight:500; text-transform:uppercase; letter-spacing:.07em; color:#9ca3af; }

            .ow-select { border:0.5px solid #e5e7eb; border-radius:7px; padding:6px 10px; font-size:12px; color:#374151; background:#fff; outline:none; min-width:160px; }
            .ow-select:focus { border-color:#185FA5; box-shadow:0 0 0 3px rgba(24,95,165,.1); }

            .ow-btn { display:inline-flex; align-items:center; gap:6px; font-size:12px; font-weight:500; padding:7px 15px; border-radius:7px; cursor:pointer; border:none; white-space:nowrap; transition:all .13s; }
            .ow-btn--primary { background:#185FA5; color:#fff; }
            .ow-btn--primary:hover { background:#0F4A84; transform:translateY(-1px); }
            .ow-btn--purple { background:#534AB7; color:#fff; box-shadow:0 2px 8px rgba(83,74,183,.2); }
            .ow-btn--purple:hover { background:#3C3489; transform:translateY(-1px); }
            .ow-btn--clear { background:#185ea51c; }
            .ow-btn--clear:hover { background:#fee2e2; color:#b91c1c; }

            .ow-card { background:#fff; border:0.5px solid #e5e7eb; border-radius:12px; overflow:hidden; padding:20px; }
            .ow-muted { font-size:12px; color:#6b7280; }

            .ow-amt { font-size:13px; font-weight:600; padding:3px 10px; border-radius:99px; white-space:nowrap; display:inline-block; }
            .ow-amt--overdue { background:#FAECE7; color:#993C1D; }

            .ow-badge { display:inline-flex; align-items:center; gap:4px; font-size:11px; font-weight:500; padding:3px 9px; border-radius:99px; white-space:nowrap; }
            .ow-badge--paid { background:#E1F5EE; color:#0F6E56; }
            .ow-badge--overdue { background:#FAECE7; color:#993C1D; }
            .ow-badge--amber { background:#FAEEDA; color:#854F0B; }
            .ow-badge--blue { background:#E6F1FB; color:#0C447C; border:0.5px solid #B5D4F4; }

            .ow-act { display:inline-flex; align-items:center; gap:4px; font-size:11px; font-weight:500; padding:4px 10px; border-radius:6px; cursor:pointer; border:none; transition:background .13s; }
            .ow-act--ghost { background:#f3f4f6; color:#374151; border:0.5px solid #e5e7eb; }
            .ow-act--ghost:hover { background:#e5e7eb; color:#111827; }

            .ow-table { width:100%; border-collapse:collapse; }
            .ow-table thead tr { background:#fafafa; border-bottom:0.5px solid #e5e7eb; }
            .ow-table th { padding:.65rem 1rem; font-size:10px; font-weight:500; color:#6b7280; text-transform:uppercase; letter-spacing:.07em; border:none; }
            .ow-table td { padding:.8rem 1rem; border:none; vertical-align:middle; }
            .ow-table tbody tr { border-bottom:0.5px solid #f3f4f6; }
            .ow-table tbody tr:hover { background:#f3f4f6; }
        </style>
@endpush