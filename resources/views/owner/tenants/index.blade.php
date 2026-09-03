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
                            <form action="{{ route('owner.tenant.bulk-resend-logins') }}" method="POST" class="d-inline"
                                  data-cs-confirm="{{ __('Send login details (by email & SMS) to every tenant who hasn\'t signed in yet? Each gets a fresh password to set on first login. SMS uses your SMS credits.') }}"
                                  data-cs-confirm-title="{{ __('Send login details') }}"
                                  data-cs-confirm-ok="{{ __('Send logins') }}">
                                @csrf
                                <button type="submit" class="ow-btn ow-btn--ghost" title="{{ __('Send login details to tenants who haven\'t signed in') }}">
                                    <i class="ri-mail-send-line" style="font-size:13px;"></i>
                                    {{ __('Send logins') }}
                                </button>
                            </form>
                            <a href="{{ route('owner.tenant.import.index') }}" class="ow-btn ow-btn--ghost">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none">
                                    <path d="M12 3v12m0 0l-4-4m4 4l4-4M4 17v2a2 2 0 002 2h12a2 2 0 002-2v-2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                {{ __('Import') }}
                            </a>
                            <a href="{{ route('owner.tenant.create') }}" class="ow-btn ow-btn--primary">
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

    {{-- ── Move-in first-invoice modal (invoice-at-assignment) ─────────────────────
         Auto-opens right after a tenant is assigned (?first_invoice=<id>), letting the
         owner choose the first charge. Optional — closing = "bill on the next cycle". --}}
    <input type="hidden" id="firstInvoicePreviewRoute" value="{{ route('owner.tenant.first-invoice.preview', ['id' => '__TID__']) }}">
    <input type="hidden" id="firstInvoiceStoreRoute" value="{{ route('owner.tenant.first-invoice.store', ['id' => '__TID__']) }}">
    <div class="modal fade" id="firstInvoiceModal" tabindex="-1" aria-labelledby="firstInvoiceModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border:none;border-radius:14px;overflow:hidden;">
                <div class="modal-header" style="border-bottom:0.5px solid #e5e7eb;padding:18px 22px;">
                    <div>
                        <p style="font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:.08em;color:#185FA5;margin:0 0 3px;">{{ __('Move-in') }}</p>
                        <h4 id="firstInvoiceModalLabel" style="font-size:16px;font-weight:600;color:#111827;margin:0;">{{ __('Set up the first invoice') }}</h4>
                        <p id="fiSubhead" class="ow-muted" style="margin:4px 0 0;"></p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                </div>
                <div class="modal-body" style="padding:20px 22px;">
                    {{-- Already-invoiced info state --}}
                    <div id="fiAlreadyInvoiced" class="ow-badge ow-badge--blue" style="display:none;width:100%;justify-content:flex-start;padding:10px 12px;margin-bottom:6px;">
                        <span></span>
                    </div>

                    <div id="fiOptions">
                        <label class="fi-opt" data-mode="full">
                            <input type="radio" name="fi_mode" value="full" checked>
                            <span class="fi-opt__body">
                                <span class="fi-opt__title">{{ __('Full month') }}</span>
                                <span class="fi-opt__amt" data-fi="full">—</span>
                            </span>
                        </label>

                        <label class="fi-opt" data-mode="prorate" id="fiProrateOpt">
                            <input type="radio" name="fi_mode" value="prorate">
                            <span class="fi-opt__body">
                                <span class="fi-opt__title">{{ __('Pro-rated to month-end') }}
                                    <span class="fi-opt__note">{{ __('Charge only for the days left this month') }} <span id="fiProrateNote"></span></span>
                                </span>
                                <span class="fi-opt__amt" data-fi="prorate">—</span>
                            </span>
                        </label>

                        <label class="fi-opt" data-mode="custom">
                            <input type="radio" name="fi_mode" value="custom">
                            <span class="fi-opt__body">
                                <span class="fi-opt__title">{{ __('Custom amount') }}</span>
                                <span class="fi-opt__amt">
                                    <input type="number" min="1" step="any" id="fiCustomAmount" class="ow-select" style="min-width:120px;text-align:right;" placeholder="0.00" disabled>
                                </span>
                            </span>
                        </label>

                        <label class="fi-opt" data-mode="skip">
                            <input type="radio" name="fi_mode" value="skip">
                            <span class="fi-opt__body">
                                <span class="fi-opt__title">{{ __('No charge this period') }}
                                    <span class="fi-opt__note">{{ __('Rent bills on the next cycle') }}</span>
                                </span>
                            </span>
                        </label>
                    </div>

                    {{-- Optional security deposit — held as a refundable liability, not income --}}
                    <div id="fiDepositBlock" style="display:none;margin-top:14px;padding-top:14px;border-top:0.5px solid #e5e7eb;">
                        <label class="fi-dep">
                            <input type="checkbox" id="fiIncludeDeposit">
                            <span class="fi-opt__body">
                                <span class="fi-opt__title">{{ __('Also collect security deposit') }}
                                    <span class="fi-opt__note">{{ __('Held as a refundable deposit — returned at move-out, not income') }}</span>
                                </span>
                                <span class="fi-opt__amt">
                                    <input type="number" min="1" step="any" id="fiDepositAmount" class="ow-select" style="min-width:120px;text-align:right;" placeholder="0.00" disabled>
                                </span>
                            </span>
                        </label>
                        <div id="fiDepositExists" class="fi-opt__note" style="display:none;margin-top:8px;color:#0F6E56;">{{ __('A deposit is already recorded for this tenant.') }}</div>
                    </div>
                </div>
                <div class="modal-footer" style="border-top:0.5px solid #e5e7eb;padding:14px 22px;gap:8px;">
                    <button type="button" class="ow-btn ow-btn--clear" data-bs-dismiss="modal" style="width:auto;">{{ __('Not now') }}</button>
                    <button type="button" class="ow-btn ow-btn--primary" id="fiSubmit" style="width:auto;">{{ __('Create invoice') }}</button>
                </div>
            </div>
        </div>
    </div>
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

<script>
    // ── Move-in first-invoice modal ──────────────────────────────────────────────
    // Opens when we land on the list with ?first_invoice=<tenantId> straight after an
    // assignment (add-tenant wizard OR application-accept). Fetches the computed amounts,
    // lets the owner pick full / pro-rated / custom / skip, then generates the invoice.
    $(document).ready(function () {
        const params = new URLSearchParams(window.location.search);
        const tenantId = params.get('first_invoice');
        if (!tenantId || !/^\d+$/.test(tenantId)) return;

        const curSymbol = @json(getCurrencySymbol());
        const curPlace  = @json(getCurrencyPlacement());   // 'left' | 'right'
        const fmtMoney = function (n) {
            const v = Number(n || 0).toLocaleString(undefined, { minimumFractionDigits: 0, maximumFractionDigits: 2 });
            return curPlace === 'right' ? (v + ' ' + curSymbol) : (curSymbol + ' ' + v);
        };
        const rt = (tpl) => tpl.replace('__TID__', tenantId);
        const $modal = $('#firstInvoiceModal');
        const bsModal = new bootstrap.Modal($modal[0]);

        // Clean the query param so a refresh doesn't reopen the modal.
        const cleanUrl = () => {
            params.delete('first_invoice');
            const qs = params.toString();
            history.replaceState({}, '', window.location.pathname + (qs ? '?' + qs : ''));
        };

        $.get(rt($('#firstInvoicePreviewRoute').val()), function (res) {
            const ctx = res && res.data ? res.data.context : null;
            if (!ctx) { cleanUrl(); return; }   // not applicable → stay silent

            $('#fiSubhead').text(ctx.tenant_name + ' · ' + ctx.unit_label + ' · ' + ctx.period_label);
            $('[data-fi="full"]').text(fmtMoney(ctx.full_amount));

            if (ctx.prorate) {
                $('[data-fi="prorate"]').text(fmtMoney(ctx.prorate.amount));
                $('#fiProrateNote').text('(' + ctx.prorate.days_remaining + '/' + ctx.prorate.days_in_month + ' {{ __('days') }})');
                $('#fiProrateOpt').show();
            } else {
                $('#fiProrateOpt').hide();   // yearly rent or move-in on the 1st → no pro-rate
            }

            if (ctx.already_invoiced) {
                // The cron (or a prior choice) already billed this period — never double-bill.
                $('#fiAlreadyInvoiced span').text(
                    '{{ __('Already invoiced for') }} ' + ctx.period_label +
                    (ctx.existing_amount != null ? ' (' + fmtMoney(ctx.existing_amount) + ')' : '') + '. ' +
                    '{{ __('Choose “No charge” to keep it as is.') }}'
                );
                $('#fiAlreadyInvoiced').css('display', 'flex');
                // Default the choice to skip so a stray submit can't attempt a duplicate.
                $('input[name="fi_mode"][value="skip"]').prop('checked', true);
            }

            // Deposit option. Hidden entirely when one is already in play; otherwise pre-filled from
            // the unit's configured amount (and pre-checked when configured, so the common 2× first
            // payment is one tap — the owner can still uncheck or edit).
            if (ctx.deposit_exists) {
                $('#fiDepositExists').show();
                $('#fiIncludeDeposit').prop('checked', false).closest('.fi-dep').hide();
                $('#fiDepositBlock').show();
            } else {
                if (Number(ctx.deposit_amount) > 0) {
                    $('#fiDepositAmount').val(ctx.deposit_amount);
                    $('#fiIncludeDeposit').prop('checked', true);
                    $('#fiDepositAmount').prop('disabled', false);
                }
                $('#fiDepositBlock').show();
            }

            bsModal.show();
        });

        // Enable/disable the custom-amount input with its radio.
        $(document).on('change', 'input[name="fi_mode"]', function () {
            const isCustom = $('input[name="fi_mode"]:checked').val() === 'custom';
            $('#fiCustomAmount').prop('disabled', !isCustom);
            if (isCustom) $('#fiCustomAmount').focus();
        });

        // Enable/disable the deposit amount with its checkbox.
        $(document).on('change', '#fiIncludeDeposit', function () {
            const on = $(this).is(':checked');
            $('#fiDepositAmount').prop('disabled', !on);
            if (on) $('#fiDepositAmount').focus();
        });

        $('#fiSubmit').on('click', function () {
            const mode = $('input[name="fi_mode"]:checked').val();
            const $btn = $(this);
            const payload = { mode: mode };
            if (mode === 'custom') {
                const amt = parseFloat($('#fiCustomAmount').val());
                if (!amt || amt <= 0) { toastr.error('{{ __('Enter a valid custom amount.') }}'); return; }
                payload.custom_amount = amt;
            }
            // Optional deposit line (independent of the rent choice).
            if ($('#fiIncludeDeposit').is(':checked')) {
                const dep = parseFloat($('#fiDepositAmount').val());
                if (!dep || dep <= 0) { toastr.error('{{ __('Enter a valid deposit amount.') }}'); return; }
                payload.include_deposit = 1;
                payload.deposit_amount = dep;
            }
            // Guard: "No charge" + no deposit = nothing to create.
            if (mode === 'skip' && !$('#fiIncludeDeposit').is(':checked')) {
                // allowed — service returns a friendly "no invoice" and we just close.
            }
            $btn.prop('disabled', true);
            $.ajax({
                url: rt($('#firstInvoiceStoreRoute').val()),
                type: 'POST',
                data: payload,
                success: function (res) {
                    toastr.success(res.message || '{{ __('Done') }}');
                    bsModal.hide();
                    cleanUrl();
                },
                error: function (xhr) {
                    const msg = (xhr.responseJSON && (xhr.responseJSON.message || xhr.responseJSON.error)) || '{{ __('Something went wrong.') }}';
                    toastr.error(msg);
                    $btn.prop('disabled', false);
                }
            });
        });

        $modal.on('hidden.bs.modal', cleanUrl);
    });
</script>
@endpush

@push('style')
<style>
    .fi-opt { display:flex; align-items:center; gap:12px; padding:13px 14px; border:1px solid #e5e7eb; border-radius:10px; margin-bottom:10px; cursor:pointer; transition:border-color .13s, background .13s; }
    .fi-opt:last-child { margin-bottom:0; }
    .fi-opt:hover { border-color:#B5D4F4; background:#F7FBFF; }
    .fi-opt input[type="radio"] { accent-color:#185FA5; width:16px; height:16px; flex:none; }
    .fi-opt:has(input:checked) { border-color:#185FA5; background:#F2F8FF; box-shadow:0 0 0 3px rgba(24,95,165,.08); }
    .fi-opt__body { display:flex; align-items:center; justify-content:space-between; gap:10px; flex:1; min-width:0; }
    .fi-opt__title { font-size:13px; font-weight:500; color:#111827; }
    .fi-opt__note { display:block; font-size:11px; font-weight:400; color:#9ca3af; margin-top:2px; }
    .fi-opt__amt { font-size:14px; font-weight:600; color:#0F4A84; white-space:nowrap; }
    .fi-dep { display:flex; align-items:center; gap:12px; padding:13px 14px; border:1px dashed #d7c8a6; border-radius:10px; background:#FDFBF5; cursor:pointer; margin:0; }
    .fi-dep:has(input:checked) { border-color:#E7A339; background:#FCF4E4; }
    .fi-dep input[type="checkbox"] { accent-color:#B7791F; width:16px; height:16px; flex:none; }
    .fi-dep .fi-opt__amt { color:#854F0B; }
</style>
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
            .ow-toolbar__actions { display:flex; align-items:center; gap:8px; flex-wrap:wrap; }
            @media (max-width:575px) {
                .ow-toolbar__actions { width:100%; }
                .ow-toolbar__actions > form, .ow-toolbar__actions > a { flex:1 1 auto; }
                .ow-toolbar__actions .ow-btn { width:100%; justify-content:center; }
            }

            .ow-filter-group { display:flex; flex-direction:column; gap:5px; }
            .ow-filter-label { font-size:10px; font-weight:500; text-transform:uppercase; letter-spacing:.07em; color:#9ca3af; }

            .ow-select { border:0.5px solid #e5e7eb; border-radius:7px; padding:6px 10px; font-size:12px; color:#374151; background:#fff; outline:none; min-width:160px; }
            .ow-select:focus { border-color:#185FA5; box-shadow:0 0 0 3px rgba(24,95,165,.1); }

            .ow-btn { display:inline-flex; align-items:center; gap:6px; font-size:12px; font-weight:500; padding:7px 15px; border-radius:7px; cursor:pointer; border:none; white-space:nowrap; transition:all .13s; }
            .ow-btn--primary { background:#185FA5; color:#fff; }
            /* Re-assert white text on hover so a filled anchor-button never inherits the global blue link-hover. */
            .ow-btn--primary:hover { background:#0F4A84; color:#fff !important; transform:translateY(-1px); }
            .ow-btn--purple { background:#534AB7; color:#fff; box-shadow:0 2px 8px rgba(83,74,183,.2); }
            .ow-btn--purple:hover { background:#3C3489; color:#fff !important; transform:translateY(-1px); }
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
            .ow-attention { display:inline-flex; align-items:center; gap:6px; margin-top:6px; font-size:11px; font-weight:600; padding:3px 10px; border-radius:99px; background:#FAECE7; color:#993C1D; border:0.5px solid #F3C4BC; text-decoration:none; white-space:nowrap; }
            .ow-attention:hover { background:#F7DDD5; color:#7d2f16; }
            .ow-attention__dot { width:7px; height:7px; border-radius:50%; background:#C2410C; flex:none; animation:owPulse 1.6s ease-in-out infinite; }
            @keyframes owPulse { 0%,100%{opacity:1} 50%{opacity:.4} }

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