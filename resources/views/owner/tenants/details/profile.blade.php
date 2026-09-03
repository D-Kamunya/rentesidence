@extends('owner.layouts.app')

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="page-content-wrapper bg-white p-30 radius-20">

                <div class="td-header">
                    <div>
                        <h2 class="td-title">{{ $pageTitle }}</h2>
                        <ol class="td-crumb">
                            <li><a href="{{ route('owner.dashboard') }}">{{ __('Dashboard') }}</a></li>
                            <li>›</li>
                            <li><a href="{{ route('owner.tenant.index') }}">{{ __('Tenants') }}</a></li>
                            <li>›</li>
                            <li>{{ __('Profile') }}</li>
                        </ol>
                    </div>
                </div>

                <div class="td-layout">
                    {{-- Left rail --}}
                    <aside class="td-rail">
                        @include('owner.tenants.details._hero')
                        @include('owner.tenants.details.sidenav')

                        @if ($tenant->status != TENANT_STATUS_CLOSE)
                            <button type="button" class="td-nav__item td-nav__item--danger" data-bs-toggle="modal"
                                data-bs-target="#tenantCloseModal" title="{{ __('Close Tenant') }}">
                                <i class="ri-logout-box-r-line"></i><span>{{ __('Close Tenant') }}</span>
                            </button>
                        @endif
                    </aside>

                    {{-- Content --}}
                    <div class="td-content">
                        <div class="td-hero">
                            <img src="{{ $tenant->image }}" class="td-hero__img" alt="">
                            <div>
                                <h3 class="td-hero__name">{{ $tenant->first_name }} {{ $tenant->last_name }}</h3>
                                <p class="td-hero__sub">{{ $tenant->email }}@if($tenant->contact_number) · {{ $tenant->contact_number }}@endif</p>
                            </div>
                            <div class="td-hero__spacer"></div>
                            <form action="{{ route('owner.tenant.resend-login') }}" method="POST" class="d-inline"
                                  data-cs-confirm="{{ __('Reset this tenant\'s password and send new login details by email & SMS? They\'ll set their own on first login. SMS uses your SMS credits.') }}"
                                  data-cs-confirm-title="{{ __('Resend login details') }}"
                                  data-cs-confirm-ok="{{ __('Reset & send') }}">
                                @csrf
                                <input type="hidden" name="id" value="{{ $tenant->id }}">
                                <button type="submit" class="td-hero__edit" title="{{ __('Reset & resend login details') }}">
                                    <i class="ri-mail-send-line"></i> {{ __('Resend Login') }}
                                </button>
                            </form>
                            @if (config('app.debug') && session('dev_pw_' . $tenant->id))
                                <div class="td-devpw" title="{{ __('Development only — never shown in production') }}">
                                    <span class="td-devpw__tag">{{ __('DEV PW') }}</span>
                                    <code id="devpw-{{ $tenant->id }}">{{ session('dev_pw_' . $tenant->id) }}</code>
                                    <button type="button" class="td-devpw__copy" onclick="(function(b){navigator.clipboard.writeText(document.getElementById('devpw-{{ $tenant->id }}').textContent).then(function(){b.textContent='{{ __('Copied') }}';setTimeout(function(){b.textContent='{{ __('Copy') }}';},1500);});})(this)">{{ __('Copy') }}</button>
                                </div>
                            @endif
                            <a href="{{ route('owner.tenant.edit', $tenant->id) }}" class="td-hero__edit" title="{{ __('Edit Info') }}">
                                <i class="ri-edit-line"></i> {{ __('Edit Info') }}
                            </a>
                        </div>
                        @if (config('app.debug') && session('dev_pw_' . $tenant->id))
                            <style>
                                .td-devpw { display:inline-flex; align-items:center; gap:8px; background:#111827; color:#e5e7eb; border-radius:8px; padding:6px 8px 6px 10px; font-size:12px; }
                                .td-devpw__tag { font-size:9.5px; font-weight:700; letter-spacing:.06em; color:#f6b64b; }
                                .td-devpw code { font-family:ui-monospace,Menlo,monospace; font-size:12.5px; color:#fff; }
                                .td-devpw__copy { background:#374151; color:#fff; border:none; border-radius:6px; font-size:11px; font-weight:600; padding:4px 9px; cursor:pointer; }
                                .td-devpw__copy:hover { background:#4b5563; }
                            </style>
                        @endif

                        {{-- Personal Information --}}
                        <div class="td-card">
                            <div class="td-card__head">
                                <span class="td-card__ic"><i class="ri-user-3-line"></i></span>
                                <h3 class="td-card__title">{{ __('Personal Information') }}</h3>
                            </div>
                            <div class="td-card__body">
                                <dl class="td-info">
                                    <dt>{{ __('Name') }}</dt><dd>{{ $tenant->first_name }} {{ $tenant->last_name }}</dd>
                                    <dt>{{ __('Contact Number') }}</dt><dd>{{ $tenant->contact_number ?: '—' }}</dd>
                                    <dt>{{ __('Email') }}</dt><dd>{{ $tenant->email ?: '—' }}</dd>
                                    <dt>{{ __('Age') }}</dt><dd>{{ $tenant->age ?: '—' }}</dd>
                                    <dt>{{ __('Family Members') }}</dt><dd>{{ $tenant->family_member ?: '—' }}</dd>
                                    <dt>{{ __('Job') }}</dt><dd>{{ $tenant->job ?: '—' }}</dd>
                                </dl>
                            </div>
                        </div>

                        {{-- Previous Address --}}
                        <div class="td-card">
                            <div class="td-card__head">
                                <span class="td-card__ic"><i class="ri-map-pin-line"></i></span>
                                <h3 class="td-card__title">{{ __('Previous Address') }}</h3>
                            </div>
                            <div class="td-card__body">
                                <dl class="td-info">
                                    <dt>{{ __('Address') }}</dt><dd>{{ $tenant->previous_address ?: '—' }}</dd>
                                    <dt>{{ __('City') }}</dt><dd>{{ $tenant->previous_city_id ?: '—' }}</dd>
                                    <dt>{{ __('State') }}</dt><dd>{{ $tenant->previous_state_id ?: '—' }}</dd>
                                    <dt>{{ __('Zip Code') }}</dt><dd>{{ $tenant->previous_zip_code ?: '—' }}</dd>
                                    <dt>{{ __('Country') }}</dt><dd>{{ $tenant->previous_country_id ?: '—' }}</dd>
                                </dl>
                            </div>
                        </div>

                        {{-- Permanent Address --}}
                        <div class="td-card">
                            <div class="td-card__head">
                                <span class="td-card__ic"><i class="ri-home-4-line"></i></span>
                                <h3 class="td-card__title">{{ __('Permanent Address') }}</h3>
                            </div>
                            <div class="td-card__body">
                                <dl class="td-info">
                                    <dt>{{ __('Address') }}</dt><dd>{{ $tenant->permanent_address ?: '—' }}</dd>
                                    <dt>{{ __('City') }}</dt><dd>{{ $tenant->permanent_city_id ?: '—' }}</dd>
                                    <dt>{{ __('State') }}</dt><dd>{{ $tenant->permanent_state_id ?: '—' }}</dd>
                                    <dt>{{ __('Zip Code') }}</dt><dd>{{ $tenant->permanent_zip_code ?: '—' }}</dd>
                                    <dt>{{ __('Country') }}</dt><dd>{{ $tenant->permanent_country_id ?: '—' }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

{{-- Close Tenant modal (captures the tenant-screening ratings) --}}
<div class="modal fade" id="tenantCloseModal" tabindex="-1" aria-labelledby="tenantCloseModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content pf-modal">
            <div class="modal-header pf-modal__head">
                <h4 class="modal-title" id="tenantCloseModalLabel">{{ __('Close Tenant') }}</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form class="ajax" action="{{ route('owner.tenant.close.history.store', $tenant->id) }}" method="POST" data-handler="closeStatusChange">
                @csrf
                <div class="modal-body">
                    <div class="td-close-summary">
                        <div class="td-close-summary__who">
                            <img src="{{ $tenant->image }}" class="rounded-circle" width="42" height="42" alt="">
                            <div>
                                <h6 class="mb-0">{{ $tenant->first_name }} {{ $tenant->last_name }}</h6>
                                <p class="font-13 text-muted mb-0">{{ $tenant->email }}</p>
                            </div>
                        </div>
                        <div class="td-close-summary__facts">
                            <span><b>{{ $tenant->property_name }}</b> · {{ $tenant->unit_name }}</span>
                            <span class="{{ ($paymentDueInvoiceCount ?? 0) > 0 ? 'text-danger' : 'text-muted' }}">
                                {{ $paymentDueInvoiceCount ?? 0 }} {{ __('due invoice(s)') }}
                            </span>
                        </div>
                    </div>

                    @if (!empty($depositSettlement))
                        <p class="pf-hint" style="font-size:12px;color:#0F6E56;margin:0 0 10px;">
                            <i class="ri-checkbox-circle-line"></i>
                            {{ __('Pre-filled from the recorded deposit settlement') }}{{ $depositSettlement->refund_date ? ' (' . \Carbon\Carbon::parse($depositSettlement->refund_date)->format('d M Y') . ')' : '' }}.
                        </p>
                    @endif
                    <div class="pf-grid" style="grid-template-columns:1fr 1fr;">
                        <div class="pf-field">
                            <label class="pf-label">{{ __('Refund Amount') }}</label>
                            <input type="number" step="any" value="{{ !empty($depositSettlement) ? $depositSettlement->refund_amount : 0 }}" min="0" name="close_refund_amount" class="form-control pf-input" placeholder="{{ __('Refund Amount') }}">
                        </div>
                        <div class="pf-field">
                            <label class="pf-label">{{ __('Closing Charge') }}</label>
                            <input type="number" step="any" value="{{ !empty($depositSettlement) ? $depositSettlement->total_deductions : 0 }}" min="0" name="close_charge" class="form-control pf-input" placeholder="{{ __('Closing Charge') }}">
                        </div>
                        <div class="pf-field">
                            <label class="pf-label">{{ __('Closing Date') }}</label>
                            <div class="custom-datepicker"><div class="custom-datepicker-inner position-relative">
                                <input type="text" class="datepicker form-control pf-input" autocomplete="off" placeholder="dd-mm-yy" name="close_date">
                                <i class="ri-calendar-2-line"></i>
                            </div></div>
                        </div>
                        <div class="pf-field">
                            <label class="pf-label">{{ __('Lease End Date') }}</label>
                            <div class="custom-datepicker"><div class="custom-datepicker-inner position-relative">
                                <input type="text" class="datepicker form-control pf-input" autocomplete="off" placeholder="dd-mm-yy" value="{{ $tenant->lease_end_date }}" disabled>
                                <i class="ri-calendar-2-line"></i>
                            </div></div>
                        </div>
                        <div class="pf-field" style="grid-column:1 / -1;">
                            <label class="pf-label">{{ __('Closing Reason') }}</label>
                            <textarea name="close_reason" id="close_reason" class="form-control pf-input" placeholder="{{ __('Reason') }}"></textarea>
                        </div>
                    </div>

                    <div class="td-screen">
                        <div class="td-screen__head"><i class="ri-shield-star-line"></i> {{ __('Tenant Screening') }}</div>
                        <div class="pf-grid" style="grid-template-columns:1fr 1fr;">
                            <div class="pf-field">
                                <label class="pf-label">{{ __('Rent Payment Rating') }}</label>
                                <select id="rent_payment_rating" name="rent_payment_rating" class="form-control pf-input" required>
                                    <option value="">{{ __('Select Rating') }}</option>
                                    <option value="1 - Worst">1 - {{ __('Worst') }}</option>
                                    <option value="2 - Poor">2 - {{ __('Poor') }}</option>
                                    <option value="3 - Average">3 - {{ __('Average') }}</option>
                                    <option value="4 - Good">4 - {{ __('Good') }}</option>
                                    <option value="5 - Excellent">5 - {{ __('Excellent') }}</option>
                                </select>
                            </div>
                            <div class="pf-field">
                                <label class="pf-label">{{ __('Discipline Rating') }}</label>
                                <select id="discipline_rating" name="discipline_rating" class="form-control pf-input" required>
                                    <option value="">{{ __('Select Rating') }}</option>
                                    <option value="1 - Worst">1 - {{ __('Worst') }}</option>
                                    <option value="2 - Poor">2 - {{ __('Poor') }}</option>
                                    <option value="3 - Average">3 - {{ __('Average') }}</option>
                                    <option value="4 - Good">4 - {{ __('Good') }}</option>
                                    <option value="5 - Excellent">5 - {{ __('Excellent') }}</option>
                                </select>
                            </div>
                            <div class="pf-field" style="grid-column:1 / -1;">
                                <label class="pf-label">{{ __('Remarks (Optional)') }}</label>
                                <textarea name="closing_remarks" id="closing_remarks" rows="3" class="form-control pf-input"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="pf-btn pf-btn--ghost" data-bs-dismiss="modal">{{ __('Back') }}</button>
                    <button type="submit" class="pf-btn pf-btn--primary">{{ __('Submit') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Delete Tenant modal --}}
<div class="modal fade" id="tenantDeleteModal" tabindex="-1" aria-labelledby="tenantDeleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content pf-modal">
            <div class="modal-header pf-modal__head">
                <h4 class="modal-title" id="tenantDeleteModalLabel">{{ __('Delete Tenant') }}</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form class="ajax" action="{{ route('owner.tenant.delete') }}" method="POST" data-handler="deleteShowResponse">
                @csrf
                <input type="hidden" name="tenant_id" value="{{ $tenant->id }}">
                <div class="modal-body">
                    <p class="pf-modal__warn">{{ __('This Tenant has') }}
                        <span class="fw-bold text-danger">{{ $paymentDueInvoiceCount ?? 0 }}</span> {{ __('due invoice(s)') }}.</p>
                    <div class="pf-field">
                        <label class="pf-label">{{ __("Type the tenant's email to confirm deletion") }}</label>
                        <input type="email" name="email" class="form-control pf-input" placeholder="{{ __('Email') }}">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="pf-btn pf-btn--ghost" data-bs-dismiss="modal">{{ __('Back') }}</button>
                    <button type="submit" class="pf-btn pf-btn--danger">{{ __('Delete') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<input type="hidden" id="tenantListRoute" value="{{ route('owner.tenant.index') }}">

<style>
    .td-close-summary { display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap;
        background:#fafafa; border:0.5px solid #e5e7eb; border-radius:10px; padding:12px 14px; margin-bottom:18px; }
    .td-close-summary__who { display:flex; align-items:center; gap:10px; }
    .td-close-summary__facts { display:flex; flex-direction:column; align-items:flex-end; gap:2px; font-size:12.5px; }
    .td-screen { margin-top:18px; padding-top:16px; border-top:0.5px dashed #e5e7eb; }
    .td-screen__head { display:flex; align-items:center; gap:8px; font-size:13px; font-weight:600; color:#854F0B; margin-bottom:12px; }
</style>
@endsection

@push('script')
    <script src="{{ asset('assets/js/custom/tenant.js') }}"></script>
@endpush
