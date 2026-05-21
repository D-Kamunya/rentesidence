@extends('owner.layouts.app')

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="page-content-wrapper bg-white p-30 radius-20">

                {{-- ── Page header ──────────────────────────────────────── --}}
                <div class="ps-page-header mb-4">
                    <div>
                        <h2 class="ps-page-title">{{ $pageTitle }}</h2>
                        <nav aria-label="breadcrumb">
                            <ol class="ps-breadcrumb">
                                <li>
                                    <a href="{{ route('owner.dashboard') }}">{{ __('Dashboard') }}</a>
                                    <svg width="8" height="8" viewBox="0 0 24 24" fill="none" aria-hidden="true"><polyline points="9 18 15 12 9 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </li>
                                <li>
                                    <a href="{{ route('owner.property.allProperty') }}">{{ __('Properties') }}</a>
                                    <svg width="8" height="8" viewBox="0 0 24 24" fill="none" aria-hidden="true"><polyline points="9 18 15 12 9 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </li>
                                <li aria-current="page">{{ $pageTitle }}</li>
                            </ol>
                        </nav>
                    </div>

                    <a href="{{ route('owner.property.edit', $property->id) }}" class="ps-btn ps-btn--ghost" title="{{ __('Edit Info') }}">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        {{ __('Edit Info') }}
                    </a>
                </div>

                {{-- ── Property hero ────────────────────────────────────── --}}
                <div class="ps-hero mb-4">
                    <div class="ps-hero__img-wrap">
                        <img src="{{ $property->thumbnail_image }}" alt="" class="ps-hero__img">
                    </div>

                    <div class="ps-hero__meta">
                        <div class="ps-hero__name-row">
                            <h3 class="ps-hero__name">{{ $property->name }}</h3>
                        </div>

                        <div class="ps-hero__address">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                            </svg>
                            <span>{{ $property->address }}</span>
                        </div>

                        {{-- Stats strip --}}
                        <div class="ps-stat-strip">
                            <div class="ps-stat">
                                <span class="ps-stat__dot ps-stat__dot--blue"></span>
                                <div>
                                    <div class="ps-stat__label">{{ __('Total Units') }}</div>
                                    <div class="ps-stat__value ps-stat__value--blue">{{ count($property->propertyUnits) }}</div>
                                </div>
                            </div>
                            <div class="ps-stat__divider"></div>
                            <div class="ps-stat">
                                <span class="ps-stat__dot ps-stat__dot--green"></span>
                                <div>
                                    <div class="ps-stat__label">{{ __('Available') }}</div>
                                    <div class="ps-stat__value ps-stat__value--green">{{ $property->available_unit }}</div>
                                </div>
                            </div>
                            <div class="ps-stat__divider"></div>
                            <div class="ps-stat">
                                <span class="ps-stat__dot ps-stat__dot--amber"></span>
                                <div>
                                    <div class="ps-stat__label">{{ __('Tenants') }}</div>
                                    <div class="ps-stat__value ps-stat__value--amber">{{ $property->number_of_unit - $property->available_unit }}</div>
                                </div>
                            </div>
                            <div class="ps-stat__divider"></div>
                            <div class="ps-stat">
                                <span class="ps-stat__dot ps-stat__dot--purple"></span>
                                <div>
                                    <div class="ps-stat__label">{{ __('Avg Rent') }}</div>
                                    <div class="ps-stat__value ps-stat__value--purple">{{ currencyPrice($property->avg_general_rent) }}</div>
                                </div>
                            </div>
                        </div>

                        @if($property->description)
                        <p class="ps-hero__desc">{{ $property->description }}</p>
                        @endif
                    </div>
                </div>

                {{-- ── Image gallery ────────────────────────────────────── --}}
                <div class="ow-card ps-section mb-4">
                    <div class="dash-card__head">
                        <div class="ps-panel-icon ps-panel-icon--blue">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none">
                                <rect x="3" y="3" width="18" height="18" rx="2" stroke="currentColor" stroke-width="1.8"/>
                                <circle cx="8.5" cy="8.5" r="1.5" stroke="currentColor" stroke-width="1.8"/>
                                <polyline points="21 15 16 10 5 21" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <span class="ps-panel-title">{{ __('Image Gallery') }}</span>
                    </div>
                    <div class="ps-section__body">
                        <div class="gallery-slider-carousel owl-carousel owl-theme">
                            @forelse (@$property->propertyImages as $propertyImage)
                                <div class="ps-gallery-item">
                                    <a href="{{ @$propertyImage->single_image }}" class="venobox" data-gall="gallery01">
                                        <img src="{{ @$propertyImage->single_image }}" alt="" class="ps-gallery-img">
                                    </a>
                                </div>
                            @empty
                                <div class="ps-gallery-item">
                                    <a href="#" class="venobox" data-gall="gallery01">
                                        <img src="{{ asset('assets/images/users/empty-user.jpg') }}" alt="" class="ps-gallery-img">
                                    </a>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                {{-- ── Property details table ───────────────────────────── --}}
                <div class="ow-card ps-section mb-4">
                    <div class="dash-card__head">
                        <div class="ps-panel-icon ps-panel-icon--blue">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                <polyline points="14 2 14 8 20 8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                            </svg>
                        </div>
                        <span class="ps-panel-title">{{ __('Property Details') }}</span>
                    </div>

                    <div class="ps-details-grid">
                        <div class="ps-detail-row">
                            <span class="ps-detail-label">{{ __('Total Unit') }}</span>
                            <span class="ps-detail-value">{{ count($property->propertyUnits) }}</span>
                        </div>
                        <div class="ps-detail-row">
                            <span class="ps-detail-label">{{ __('Available for Lease') }}</span>
                            <span class="ps-detail-value ps-detail-value--green">{{ $property->available_unit }}</span>
                        </div>
                        <div class="ps-detail-row">
                            <span class="ps-detail-label">{{ __('Current Tenants') }}</span>
                            <span class="ps-detail-value">{{ $property->number_of_unit - $property->available_unit }}</span>
                        </div>
                        <div class="ps-detail-row">
                            <span class="ps-detail-label">{{ __('Average Rent') }}</span>
                            <span class="ps-detail-value ps-detail-value--blue">{{ currencyPrice($property->avg_general_rent) }}</span>
                        </div>
                        <div class="ps-detail-row">
                            <span class="ps-detail-label">{{ __('Security Deposit') }}</span>
                            <span class="ps-detail-value">{{ currencyPrice($property->total_security_deposit) }}</span>
                        </div>
                        <div class="ps-detail-row">
                            <span class="ps-detail-label">{{ __('Late Fee') }}</span>
                            <span class="ps-detail-value">{{ currencyPrice($property->total_late_fee) }}</span>
                        </div>
                        <div class="ps-detail-row">
                            <span class="ps-detail-label">{{ __('Maintainer Name') }}</span>
                            <span class="ps-detail-value">{{ $property->first_name }} {{ $property->last_name }}</span>
                        </div>
                    </div>
                </div>

                {{-- ── All units table ──────────────────────────────────── --}}
                <div class="ow-card ps-section">
                    <div class="dash-card__head">
                        <div class="ps-panel-icon ps-panel-icon--green">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none">
                                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                <polyline points="9 22 9 12 15 12 15 22" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <span class="ps-panel-title">{{ __('All Unit Details') }}</span>
                    </div>

                    <div class="table-responsive">
                        <table class="ps-table">
                            <thead>
                                <tr>
                                    <th>{{ __('SL') }}</th>
                                    <th>{{ __('Unit Name') }}</th>
                                    <th>{{ __('Bedroom') }}</th>
                                    <th>{{ __('Baths') }}</th>
                                    <th>{{ __('Kitchen') }}</th>
                                    <th>{{ __('Sq Ft') }}</th>
                                    <th>{{ __('Amenities') }}</th>
                                    <th>{{ __('Parking') }}</th>
                                    <th>{{ __('Condition') }}</th>
                                    <th>{{ __('Description') }}</th>
                                    <th>{{ __('Image') }}</th>
                                    <th>{{ __('Availability') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($units as $propertyUnit)
                                <tr>
                                    <td>{{ ($units->currentPage() - 1) * $units->perPage() + $loop->iteration }}</td>
                                    <td style="font-weight:600;color:var(--gray-800);">{{ $propertyUnit->unit_name }}</td>
                                    <td>{{ $propertyUnit->bedroom }}</td>
                                    <td>{{ $propertyUnit->bath }}</td>
                                    <td>{{ $propertyUnit->kitchen }}</td>
                                    <td>{{ $propertyUnit->square_feet }}</td>
                                    <td>{{ $propertyUnit->amenities }}</td>
                                    <td>{{ $propertyUnit->parking }}</td>
                                    <td>{{ $propertyUnit->condition }}</td>
                                    <td class="ps-table__desc">{{ Str::limit($propertyUnit->description, 100, '...') }}</td>
                                    <td>
                                        <img class="ps-unit-thumb"
                                             src="{{ $propertyUnit->first_image
                                                     ? asset('storage/' . $propertyUnit->first_image->folder_name . '/' . $propertyUnit->first_image->file_name)
                                                     : asset('images/default-unit.png') }}"
                                             alt="">
                                    </td>
                                    <td>
                                        @if (@$propertyUnit->first_name != null)
                                            <span class="ow-badge ow-badge--danger">{{ __('Not Available') }}</span>
                                        @else
                                            <span class="ow-badge ow-badge--paid">{{ __('Available') }}</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="12" class="ps-empty">{{ __('No Unit Found') }}</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="ps-pagination">{{ $units->links() }}</div>
                </div>

            </div>
        </div>
    </div>
</div>

{{-- ── Tenant assign modal (logic untouched) ───────────────────── --}}
<div class="modal fade" id="tenantAssignModal" tabindex="-1" aria-labelledby="tenantAssignModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content ps-modal">
            <div class="ps-modal__head">
                <div>
                    <div class="ps-modal__eyebrow">{{ __('Property') }}</div>
                    <h4 class="ps-modal__title" id="tenantAssignModalLabel">{{ __('Tenant Assign') }}</h4>
                </div>
                <button type="button" class="ps-modal__close" data-bs-dismiss="modal" aria-label="Close">
                    <span class="iconify" data-icon="akar-icons:cross"></span>
                </button>
            </div>
            <form class="ajax" action="{{ route('owner.invoice.store') }}" method="post" data-handler="getShowMessage">
                @csrf
                <input type="hidden" name="property_id" value="{{ $property->id }}">
                <div class="modal-body ps-modal__body">
                    <div class="ps-field">
                        <label class="ps-field__label">{{ __('Unit') }}</label>
                        <select class="ps-field__input propertyUnitSelectOption" name="property_unit_id">
                            <option value="">--{{ __('Select Unit') }}--</option>
                            @foreach ($units as $unit)
                                <option value="{{ $unit->id }}">{{ $propertyUnit->unit_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="ps-modal__foot">
                    <button type="button" class="ps-btn ps-btn--ghost" data-bs-dismiss="modal" title="{{ __('Back') }}">{{ __('Back') }}</button>
                    <button type="submit" class="ps-btn ps-btn--primary" title="{{ __('Assign') }}">{{ __('Assign') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

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
.ps-page-header {
    display:flex; align-items:flex-start; justify-content:space-between;
    flex-wrap:wrap; gap:1rem; margin-bottom:1.5rem;
}
.ps-page-title { font-size:22px; font-weight:500; color:var(--gray-900); margin:0 0 6px; }
.ps-breadcrumb {
    display:flex; align-items:center; gap:6px; list-style:none;
    padding:0; margin:0; font-size:12px; color:var(--gray-400);
}
.ps-breadcrumb li { display:flex; align-items:center; gap:6px; }
.ps-breadcrumb a  { color:var(--blue); font-weight:500; text-decoration:none; }

/* ── Buttons ──────────────────────────────────────────────────── */
.ps-btn {
    display:inline-flex; align-items:center; gap:6px;
    font-size:12px; font-weight:500; padding:7px 15px;
    border-radius:7px; border:none; cursor:pointer;
    text-decoration:none; transition:all .13s; white-space:nowrap;
}
.ps-btn--primary { background:var(--blue); color:var(--white); }
.ps-btn--primary:hover { background:var(--blue-hover); color:var(--white); transform:translateY(-1px); }
.ps-btn--ghost {
    background:var(--gray-100); color:var(--gray-700);
    border:0.5px solid var(--gray-200);
}
.ps-btn--ghost:hover { background:var(--blue); color:var(--white); border-color:var(--blue); transform:translateY(-1px); }

/* ── Hero ─────────────────────────────────────────────────────── */
.ps-hero {
    display:grid; grid-template-columns:420px 1fr; gap:1.5rem;
    background:var(--white); border:0.5px solid var(--blue-faint);
    border-radius:14px; overflow:hidden;
    box-shadow: 0 4px 12px rgba(0,0,0,.04),
                0 0 0 1px rgba(24,95,165,.05),
                0 6px 18px rgba(24,95,165,.06);
    margin-bottom:1.5rem;
}
@media(max-width:900px) { .ps-hero { grid-template-columns:1fr; } }

.ps-hero__img-wrap { overflow:hidden; max-height:340px; }
@media(max-width:900px) { .ps-hero__img-wrap { max-height:220px; } }
.ps-hero__img { width:100%; height:100%; object-fit:cover; display:block; }

.ps-hero__meta { padding:24px; display:flex; flex-direction:column; gap:16px; }

.ps-hero__name { font-size:20px; font-weight:600; color:var(--gray-900); margin:0; }

.ps-hero__address {
    display:flex; align-items:flex-start; gap:6px;
    font-size:13px; color:var(--gray-500); line-height:1.5;
}
.ps-hero__address svg { flex-shrink:0; margin-top:2px; color:var(--gray-400); }

.ps-hero__desc { font-size:13px; color:var(--gray-500); line-height:1.7; margin:0; }

/* Stat strip inside hero */
.ps-stat-strip {
    display:flex; align-items:center; flex-wrap:wrap;
    background:var(--gray-50); border:0.5px solid var(--gray-200);
    border-radius:10px; overflow:hidden;
}
.ps-stat { display:flex; align-items:center; gap:8px; padding:10px 16px; flex:1; min-width:100px; }
.ps-stat__divider { width:0.5px; align-self:stretch; background:var(--gray-200); }
.ps-stat__dot { width:8px; height:8px; border-radius:50%; flex-shrink:0; }
.ps-stat__dot--blue   { background:var(--blue); }
.ps-stat__dot--green  { background:var(--green); }
.ps-stat__dot--amber  { background:#F59E0B; }
.ps-stat__dot--purple { background:var(--purple); }
.ps-stat__label {
    font-size:10px; font-weight:500; text-transform:uppercase;
    letter-spacing:.07em; color:var(--gray-400); margin-bottom:2px;
}
.ps-stat__value { font-size:16px; font-weight:600; }
.ps-stat__value--blue   { color:var(--blue); }
.ps-stat__value--green  { color:var(--green); }
.ps-stat__value--amber  { color:var(--amber); }
.ps-stat__value--purple { color:var(--purple); }

/* ── Shared card ──────────────────────────────────────────────── */
.ow-card {
    background:var(--white);
    border:0.5px solid var(--blue-faint);
    border-radius:12px; overflow:hidden;
    box-shadow: 0 4px 12px rgba(0,0,0,.04),
                0 0 0 1px rgba(24,95,165,.05),
                0 6px 18px rgba(24,95,165,.06);
    transition:all .25s ease;
}
.ps-section { margin-bottom:1.25rem; }

/* Card head */
.dash-card__head {
    display:flex; align-items:center; gap:10px;
    padding:.75rem 1.1rem; border-bottom:0.5px solid var(--gray-200);
    background:var(--gray-50);
}
.ps-panel-icon {
    width:28px; height:28px; border-radius:7px;
    display:inline-flex; align-items:center; justify-content:center; flex-shrink:0;
}
.ps-panel-icon--blue  { background:var(--blue-light);  color:var(--blue); }
.ps-panel-icon--green { background:var(--green-light); color:var(--green); }
.ps-panel-title { font-size:14px; font-weight:500; color:var(--gray-900); }

/* Gallery */
.ps-section__body { padding:16px 20px; }
.ps-gallery-item { border-radius:8px; overflow:hidden; }
.ps-gallery-img  { width:100%; height:140px; object-fit:cover; display:block; border-radius:8px; }

/* ── Property details key-value grid ─────────────────────────── */
.ps-details-grid { padding:4px 0 8px; }
.ps-detail-row {
    display:flex; align-items:center; justify-content:space-between;
    padding:11px 20px; border-bottom:0.5px solid var(--gray-100);
}
.ps-detail-row:last-child { border-bottom:none; }
.ps-detail-row:hover { background:var(--gray-50); }
.ps-detail-label {
    font-size:13px; color:var(--gray-500); font-weight:500;
}
.ps-detail-value {
    font-size:13px; color:var(--gray-800); font-weight:600; text-align:right;
}
.ps-detail-value--green  { color:var(--green); }
.ps-detail-value--blue   { color:var(--blue); }

/* ── Units table ──────────────────────────────────────────────── */
.ps-table { width:100%; border-collapse:collapse; font-size:13px; }
.ps-table thead { background:var(--gray-50); border-bottom:0.5px solid var(--gray-200); }
.ps-table th {
    padding:.65rem 1rem; font-size:10px; font-weight:500;
    text-transform:uppercase; letter-spacing:.07em;
    color:var(--gray-500); white-space:nowrap;
}
.ps-table td {
    padding:.8rem 1rem; border-bottom:0.5px solid var(--gray-100);
    color:var(--gray-700); vertical-align:middle;
}
.ps-table tr:last-child td { border-bottom:none; }
.ps-table tbody tr:nth-child(even) td { background:var(--gray-50); }
.ps-table tbody tr:hover td { background:var(--gray-100); }
.ps-table__desc { max-width:200px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; color:var(--gray-500); }
.ps-unit-thumb { width:36px; height:36px; border-radius:8px; object-fit:cover; border:0.5px solid var(--gray-200); }
.ps-empty { text-align:center; color:var(--gray-400); padding:1.5rem 1rem; }

/* ── Badges ───────────────────────────────────────────────────── */
.ow-badge {
    display:inline-flex; align-items:center; gap:4px;
    font-size:11px; font-weight:500; padding:3px 9px;
    border-radius:99px; white-space:nowrap;
}
.ow-badge--paid   { background:var(--green-light); color:var(--green-dark); }
.ow-badge--danger { background:var(--red-light);   color:var(--red); }

/* ── Pagination ───────────────────────────────────────────────── */
.ps-pagination {
    padding:12px 20px; border-top:0.5px solid var(--gray-200);
    background:var(--gray-50); display:flex; justify-content:flex-end;
}

/* ── Modal ────────────────────────────────────────────────────── */
.ps-modal { border-radius:14px; overflow:hidden; border:none; }
.ps-modal__head {
    display:flex; align-items:center; justify-content:space-between;
    padding:16px 20px; background:var(--gray-50);
    border-bottom:0.5px solid var(--gray-200);
}
.ps-modal__eyebrow {
    font-size:10px; font-weight:500; text-transform:uppercase;
    letter-spacing:.07em; color:var(--gray-400); margin-bottom:2px;
}
.ps-modal__title { font-size:15px; font-weight:600; color:var(--gray-900); margin:0; }
.ps-modal__close {
    width:30px; height:30px; border-radius:7px;
    background:var(--gray-100); border:none; cursor:pointer;
    display:flex; align-items:center; justify-content:center;
    color:var(--gray-500); transition:background .13s;
}
.ps-modal__close:hover { background:var(--gray-200); }
.ps-modal__body { padding:20px; }
.ps-modal__foot {
    display:flex; align-items:center; gap:10px;
    padding:14px 20px; background:var(--gray-50);
    border-top:0.5px solid var(--gray-200);
}

/* Modal field */
.ps-field { display:flex; flex-direction:column; gap:5px; }
.ps-field__label {
    font-size:10px; font-weight:500; text-transform:uppercase;
    letter-spacing:.07em; color:var(--gray-400);
}
.ps-field__input {
    width:100%; padding:7px 10px;
    border:0.5px solid var(--gray-200); border-radius:7px;
    font-size:13px; color:var(--gray-700); outline:none;
    transition:border-color .15s, box-shadow .15s;
}
.ps-field__input:focus { border-color:var(--blue); box-shadow:0 0 0 3px rgba(24,95,165,.1); }

/* ── Utilities ────────────────────────────────────────────────── */
.mb-4 { margin-bottom:1.5rem; }
</style>
@endpush