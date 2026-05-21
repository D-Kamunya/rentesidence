@extends('admin.layouts.app')

@section('content')

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="page-content-wrapper bg-white p-30 radius-20">
                <div class="container-fluid">

                    {{-- Page Header --}}
                    @php
                        $pageTitle = 'Product categories';
                    @endphp
                    <div class="pc-page-header mb-4">
                        <div>
                            <h2 class="pc-page-title">{{ __('Product Categories') }}</h2>
                            <nav aria-label="breadcrumb">
                                <ol class="pc-breadcrumb">
                                    <li><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
                                    <svg width="8" height="8" viewBox="0 0 24 24" fill="none"><path d="M9 18l6-6-6-6" stroke="#d1d5db" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    <li aria-current="page">{{ __('Product Categories') }}</li>
                                </ol>
                            </nav>
                        </div>
                        <button class="pc-btn pc-btn--purple" id="openCreateModal">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none">
                                <line x1="12" y1="5" x2="12" y2="19" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                <line x1="5" y1="12" x2="19" y2="12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                            {{ __('New Category') }}
                        </button>
                    </div>

                    {{-- Summary Strip --}}
                    <div class="pc-strip mb-4">
                        <div class="pc-strip__item">
                            <span class="pc-strip__dot pc-strip__dot--blue"></span>
                            <span class="pc-strip__label">{{ __('Total') }}</span>
                            <span class="pc-strip__value" id="statTotal">{{ $categories->count() }}</span>
                        </div>
                        <div class="pc-strip__divider"></div>
                        <div class="pc-strip__item">
                            <span class="pc-strip__dot pc-strip__dot--green"></span>
                            <span class="pc-strip__label">{{ __('Active') }}</span>
                            <span class="pc-strip__value" id="statActive">{{ $categories->where('status', 1)->count() }}</span>
                        </div>
                        <div class="pc-strip__divider"></div>
                        <div class="pc-strip__item">
                            <span class="pc-strip__dot pc-strip__dot--amber"></span>
                            <span class="pc-strip__label">{{ __('Inactive') }}</span>
                            <span class="pc-strip__value" id="statInactive">{{ $categories->where('status', 0)->count() }}</span>
                        </div>
                        <div class="pc-strip__divider"></div>
                        <div class="pc-strip__item">
                            <span class="pc-strip__dot pc-strip__dot--blue"></span>
                            <span class="pc-strip__label">{{ __('Products') }}</span>
                            <span class="pc-strip__value">{{ $categories->where('type', 'product')->count() }}</span>
                        </div>
                        <div class="pc-strip__divider"></div>
                        <div class="pc-strip__item">
                            <span class="pc-strip__dot pc-strip__dot--purple"></span>
                            <span class="pc-strip__label">{{ __('Services') }}</span>
                            <span class="pc-strip__value">{{ $categories->where('type', 'service')->count() }}</span>
                        </div>
                    </div>

                    {{-- Filters & Search --}}
                    <div class="pc-toolbar mb-3">
                        <div class="pc-filter-tabs" id="filterTabs">
                            <button class="pc-filter-tab pc-filter-tab--active" data-filter="all">
                                <span class="pc-filter-tab__dot" style="background:#185FA5"></span>
                                {{ __('All') }}
                            </button>
                            <button class="pc-filter-tab" data-filter="product">
                                <span class="pc-filter-tab__dot" style="background:#1D9E75"></span>
                                {{ __('Products') }}
                            </button>
                            <button class="pc-filter-tab" data-filter="service">
                                <span class="pc-filter-tab__dot" style="background:#534AB7"></span>
                                {{ __('Services') }}
                            </button>
                            <button class="pc-filter-tab" data-filter="active">
                                <span class="pc-filter-tab__dot" style="background:#1D9E75"></span>
                                {{ __('Active') }}
                            </button>
                            <button class="pc-filter-tab" data-filter="inactive">
                                <span class="pc-filter-tab__dot" style="background:#F59E0B"></span>
                                {{ __('Inactive') }}
                            </button>
                        </div>

                        <div class="pc-search-wrap">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" class="pc-search-icon">
                                <circle cx="11" cy="11" r="8" stroke="currentColor" stroke-width="1.8"/>
                                <path d="M21 21l-4.35-4.35" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                            </svg>
                            <input type="text" id="searchInput" class="pc-search" placeholder="{{ __('Search categories…') }}">
                        </div>
                    </div>

                    {{-- Table --}}
                    <div class="pc-card">
                        <div class="table-responsive">
                            <table class="pc-table" id="categoriesTable">
                                <thead>
                                    <tr>
                                        <th>{{ __('Name') }}</th>
                                        <th>{{ __('Slug') }}</th>
                                        <th>{{ __('Type') }}</th>
                                        <th>{{ __('Base Commission') }}</th>
                                        <th>{{ __('Affiliate Commission') }}</th>
                                        <th>{{ __('Status') }}</th>
                                        <th>{{ __('Products') }}</th>
                                        <th>{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody id="categoriesBody">
                                    @forelse($categories as $category)
                                    <tr class="pc-row"
                                        data-type="{{ $category->type }}"
                                        data-status="{{ $category->status }}"
                                        data-name="{{ strtolower($category->name) }}">
                                        <td>
                                            <span class="pc-cat-name">{{ $category->name }}</span>
                                        </td>
                                        <td>
                                            <code class="pc-slug">{{ $category->slug }}</code>
                                        </td>
                                        <td>
                                            <span class="pc-badge pc-badge--{{ $category->type === 'product' ? 'green' : 'purple' }}">
                                                {{ ucfirst($category->type) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="pc-amt pc-amt--blue">{{ number_format($category->base_commission, 1) }}%</span>
                                        </td>
                                        <td>
                                            <span class="pc-amt pc-amt--teal">{{ number_format($category->affiliate_commission, 1) }}%</span>
                                        </td>
                                        <td>
                                            <span class="pc-badge pc-badge--{{ $category->status ? 'active' : 'inactive' }}">
                                                {{ $category->status ? __('Active') : __('Inactive') }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="pc-amt pc-amt--grey">{{ $category->products_count ?? $category->products()->count() }}</span>
                                        </td>
                                        <td>
                                            <div class="pc-actions">
                                                <button class="pc-btn pc-btn--ghost pc-edit-btn"
                                                        data-id="{{ $category->id }}"
                                                        data-name="{{ $category->name }}"
                                                        data-slug="{{ $category->slug }}"
                                                        data-type="{{ $category->type }}"
                                                        data-commission="{{ $category->base_commission }}"
                                                        data-affiliate-commission="{{ $category->affiliate_commission }}"
                                                        data-status="{{ $category->status }}">
                                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none">
                                                        <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                                        <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                                    </svg>
                                                    {{ __('Edit') }}
                                                </button>
                                                <button class="pc-btn pc-btn--clear pc-delete-btn"
                                                        data-id="{{ $category->id }}"
                                                        data-name="{{ $category->name }}"
                                                        data-count="{{ $category->products()->count() }}">
                                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none">
                                                        <polyline points="3 6 5 6 21 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                                        <path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                                        <path d="M10 11v6M14 11v6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                                        <path d="M9 6V4a1 1 0 011-1h4a1 1 0 011 1v2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                                    </svg>
                                                    {{ __('Delete') }}
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="8" class="pc-empty">
                                            <svg width="36" height="36" viewBox="0 0 24 24" fill="none">
                                                <rect x="2" y="7" width="20" height="14" rx="2" stroke="#d1d5db" stroke-width="1.5"/>
                                                <path d="M16 7V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2" stroke="#d1d5db" stroke-width="1.5" stroke-linecap="round"/>
                                            </svg>
                                            <p>{{ __('No categories yet.') }}</p>
                                            <button class="pc-btn pc-btn--purple" id="openCreateModalEmpty">
                                                {{ __('Create your first category') }}
                                            </button>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── Create Modal ─────────────────────────────────────────── --}}
<div id="createModal" class="pc-modal-backdrop" style="display:none;">
    <div class="pc-modal" role="dialog" aria-modal="true">
        <div class="pc-modal__header">
            <div>
                <p class="pc-modal__eyebrow">{{ __('Marketplace') }}</p>
                <h5 class="pc-modal__title">{{ __('New Category') }}</h5>
            </div>
            <button class="pc-modal__close" data-dismiss="createModal">&times;</button>
        </div>
        <form id="createForm">
            @csrf
            <div class="pc-modal__body">
                <div class="pc-form-row">
                    <div class="pc-form-group">
                        <label class="pc-label">{{ __('Name') }}</label>
                        <input type="text" name="name" id="createName" class="pc-input" placeholder="e.g. Electronics" required maxlength="100">
                    </div>
                    <div class="pc-form-group">
                        <label class="pc-label">{{ __('Slug') }}</label>
                        <input type="text" name="slug" id="createSlug" class="pc-input" placeholder="e.g. electronics" required maxlength="100">
                        <p class="pc-hint">{{ __('Auto-generated from name. Must be unique.') }}</p>
                    </div>
                </div>
                <div class="pc-form-row">
                    <div class="pc-form-group">
                        <label class="pc-label">{{ __('Type') }}</label>
                        <select name="type" id="createType" class="pc-input" required>
                            <option value="">{{ __('Select type') }}</option>
                            <option value="product">{{ __('Product') }}</option>
                            <option value="service">{{ __('Service') }}</option>
                        </select>
                    </div>
                    <div class="pc-form-group">
                        <label class="pc-label">{{ __('Base Commission (%)') }}</label>
                        <input type="number" name="base_commission" id="createCommission" class="pc-input"
                               placeholder="e.g. 10" min="0" max="100" step="0.1" required>
                        <p class="pc-hint">{{ __('Platform fee applied to every sale in this category.') }}</p>
                    </div>
                </div>
                <div class="pc-form-row">
                    <div class="pc-form-group">
                        <label class="pc-label">{{ __('Affiliate Commission (%)') }}</label>
                        <input type="number" name="affiliate_commission" id="createAffiliateCommission" class="pc-input"
                               placeholder="e.g. 2" min="0" max="100" step="0.1" value="2" required>
                        <p class="pc-hint">{{ __('Commission paid out to affiliates on sales in this category.') }}</p>
                    </div>
                </div>
            </div>
            <div class="pc-modal__footer">
                <button type="button" class="pc-btn pc-btn--ghost" data-dismiss="createModal">{{ __('Cancel') }}</button>
                <button type="submit" class="pc-btn pc-btn--purple" id="createSubmitBtn">
                    {{ __('Create Category') }}
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ── Edit Modal ───────────────────────────────────────────── --}}
<div id="editModal" class="pc-modal-backdrop" style="display:none;">
    <div class="pc-modal" role="dialog" aria-modal="true">
        <div class="pc-modal__header">
            <div>
                <p class="pc-modal__eyebrow">{{ __('Marketplace') }}</p>
                <h5 class="pc-modal__title">{{ __('Edit Category') }}</h5>
            </div>
            <button class="pc-modal__close" data-dismiss="editModal">&times;</button>
        </div>
        <form id="editForm">
            @csrf
            @method('PUT')
            <input type="hidden" id="editId">
            <div class="pc-modal__body">
                <div class="pc-form-row">
                    <div class="pc-form-group">
                        <label class="pc-label">{{ __('Name') }}</label>
                        <input type="text" name="name" id="editName" class="pc-input" required maxlength="100">
                    </div>
                    <div class="pc-form-group">
                        <label class="pc-label">{{ __('Slug') }}</label>
                        <input type="text" name="slug" id="editSlug" class="pc-input" required maxlength="100">
                    </div>
                </div>
                <div class="pc-form-row">
                    <div class="pc-form-group">
                        <label class="pc-label">{{ __('Type') }}</label>
                        <select name="type" id="editType" class="pc-input" required>
                            <option value="product">{{ __('Product') }}</option>
                            <option value="service">{{ __('Service') }}</option>
                        </select>
                    </div>
                    <div class="pc-form-group">
                        <label class="pc-label">{{ __('Base Commission (%)') }}</label>
                        <input type="number" name="base_commission" id="editCommission" class="pc-input"
                               min="0" max="100" step="0.1" required>
                    </div>
                </div>
                <div class="pc-form-row">
                    <div class="pc-form-group">
                        <label class="pc-label">{{ __('Affiliate Commission (%)') }}</label>
                        <input type="number" name="affiliate_commission" id="editAffiliateCommission" class="pc-input"
                               min="0" max="100" step="0.1" required>
                        <p class="pc-hint">{{ __('Commission paid out to affiliates on sales in this category.') }}</p>
                    </div>
                    <div class="pc-form-group">
                        <label class="pc-label">{{ __('Status') }}</label>
                        <div class="pc-toggle-row">
                            <label class="pc-toggle">
                                <input type="checkbox" name="status" id="editStatus" value="1">
                                <span class="pc-toggle__track"></span>
                            </label>
                            <span class="pc-toggle-label" id="editStatusLabel">{{ __('Active') }}</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="pc-modal__footer">
                <button type="button" class="pc-btn pc-btn--ghost" data-dismiss="editModal">{{ __('Cancel') }}</button>
                <button type="submit" class="pc-btn pc-btn--primary" id="editSubmitBtn">
                    {{ __('Save Changes') }}
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ── Delete Confirm Modal ─────────────────────────────────── --}}
<div id="deleteModal" class="pc-modal-backdrop" style="display:none;">
    <div class="pc-modal pc-modal--sm" role="dialog" aria-modal="true">
        <div class="pc-modal__header">
            <div>
                <p class="pc-modal__eyebrow">{{ __('Confirm Action') }}</p>
                <h5 class="pc-modal__title">{{ __('Delete Category') }}</h5>
            </div>
            <button class="pc-modal__close" data-dismiss="deleteModal">&times;</button>
        </div>
        <div class="pc-modal__body">
            <div class="pc-delete-warning">
                <div class="pc-delete-warning__icon">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
                        <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" stroke="currentColor" stroke-width="1.8"/>
                        <line x1="12" y1="9" x2="12" y2="13" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                        <line x1="12" y1="17" x2="12.01" y2="17" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </div>
                <div>
                    <p class="pc-delete-warning__text">
                        {{ __('You are about to delete') }} <strong id="deleteNameDisplay"></strong>.
                        {{ __('This action cannot be undone.') }}
                    </p>
                </div>
            </div>
        </div>
        <div class="pc-modal__footer">
            <input type="hidden" id="deleteId">
            <button type="button" class="pc-btn pc-btn--ghost" data-dismiss="deleteModal">{{ __('Cancel') }}</button>
            <button type="button" class="pc-btn pc-btn--danger" id="confirmDeleteBtn">
                {{ __('Yes, Delete') }}
            </button>
        </div>
    </div>
</div>

@endsection

@push('style')
<style>
/* ── Base ────────────────────────────────────────────────────── */
.pc-page-header  { display:flex; align-items:flex-start; justify-content:space-between; flex-wrap:wrap; gap:1rem; }
.pc-page-title   { font-size:22px; font-weight:500; color:#111827; margin:0 0 5px; }
.pc-breadcrumb   { display:flex; align-items:center; gap:5px; list-style:none; padding:0; margin:0; font-size:12px; color:#9ca3af; }
.pc-breadcrumb a { color:#185FA5; font-weight:500; text-decoration:none; }
.pc-breadcrumb a:hover { text-decoration:underline; }
.mb-4 { margin-bottom:1.5rem; }
.mb-3 { margin-bottom:1rem; }

/* ── Summary strip ───────────────────────────────────────────── */
.pc-strip { display:flex; align-items:center; background:#fff; border:0.5px solid #e5e7eb; border-radius:12px; padding:0 4px; overflow:hidden; }
.pc-strip__item   { display:flex; align-items:center; gap:8px; padding:14px 20px; flex:1; }
.pc-strip__divider{ width:0.5px; height:36px; background:#e5e7eb; flex-shrink:0; }
.pc-strip__dot    { width:8px; height:8px; border-radius:50%; flex-shrink:0; }
.pc-strip__dot--blue   { background:#185FA5; }
.pc-strip__dot--green  { background:#1D9E75; }
.pc-strip__dot--amber  { background:#F59E0B; }
.pc-strip__dot--purple { background:#534AB7; }
.pc-strip__label  { font-size:10px; font-weight:500; text-transform:uppercase; letter-spacing:.07em; color:#9ca3af; white-space:nowrap; }
.pc-strip__value  { font-size:18px; font-weight:700; color:#111827; margin-left:auto; }

/* ── Toolbar ─────────────────────────────────────────────────── */
.pc-toolbar { display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap; }
.pc-filter-tabs { display:flex; align-items:center; background:#f3f4f6; border-radius:8px; padding:3px; gap:2px; }
.pc-filter-tab  { display:inline-flex; align-items:center; gap:5px; font-size:12px; color:#6b7280; padding:4px 12px; border-radius:6px; border:none; background:transparent; cursor:pointer; transition:all .13s; white-space:nowrap; }
.pc-filter-tab--active { background:#fff; color:#111827; box-shadow:0 1px 3px rgba(0,0,0,.08); }
.pc-filter-tab__dot { width:6px; height:6px; border-radius:50%; flex-shrink:0; }

.pc-search-wrap { position:relative; }
.pc-search-icon { position:absolute; left:10px; top:50%; transform:translateY(-50%); color:#9ca3af; pointer-events:none; }
.pc-search { border:0.5px solid #e5e7eb; border-radius:7px; padding:7px 10px 7px 32px; font-size:13px; color:#374151; width:260px; outline:none; transition:border-color .13s, box-shadow .13s; }
.pc-search:focus { border-color:#185FA5; box-shadow:0 0 0 3px rgba(24,95,165,.1); }

/* ── Card / Table ────────────────────────────────────────────── */
.pc-card { background:#fff; border:0.5px solid #e5e7eb; border-radius:12px; overflow:hidden;
           box-shadow:0 4px 12px rgba(0,0,0,.04),0 0 0 1px rgba(24,95,165,.05),0 6px 18px rgba(24,95,165,.06); }
.table-responsive { overflow-x:auto; }
.pc-table { width:100%; border-collapse:collapse; font-size:13px; }
.pc-table thead th { padding:.65rem 1rem; text-align:left; font-size:10px; font-weight:500; text-transform:uppercase; letter-spacing:.07em; color:#6b7280; background:#fafafa; border-bottom:0.5px solid #e5e7eb; white-space:nowrap; }
.pc-table tbody .pc-row { border-bottom:0.5px solid #f3f4f6; transition:background .13s; }
.pc-table tbody .pc-row:nth-child(even) { background:#fafafa; }
.pc-table tbody .pc-row:hover { background:#f3f4f6; }
.pc-table tbody .pc-row:last-child { border-bottom:none; }
.pc-table td { padding:.8rem 1rem; vertical-align:middle; color:#374151; }
.pc-cat-name { font-size:13px; font-weight:500; color:#111827; }
.pc-slug { font-family:monospace; font-size:11px; font-weight:500; color:#0C447C; background:#E6F1FB; padding:2px 7px; border-radius:5px; }
.pc-empty { text-align:center; padding:3rem 1rem !important; color:#9ca3af; }
.pc-empty p { margin:.75rem 0 1rem; font-size:14px; }

/* ── Badges ──────────────────────────────────────────────────── */
.pc-badge { display:inline-flex; align-items:center; gap:4px; font-size:11px; font-weight:500; padding:3px 9px; border-radius:99px; white-space:nowrap; }
.pc-badge--green   { background:#E1F5EE; color:#0F6E56; }
.pc-badge--purple  { background:#EDEDFB; color:#534AB7; }
.pc-badge--active  { background:#E1F5EE; color:#0F6E56; }
.pc-badge--inactive{ background:#F3F4F6; color:#6b7280; border:0.5px solid #e5e7eb; }

/* ── Amount pills ────────────────────────────────────────────── */
.pc-amt { font-size:13px; font-weight:600; padding:3px 10px; border-radius:99px; white-space:nowrap; display:inline-block; }
.pc-amt--blue { background:#E6F1FB; color:#0C447C; border:0.5px solid #B5D4F4; }
.pc-amt--teal { background:#E6F7F4; color:#0F6E56; border:0.5px solid #A7DDD1; }
.pc-amt--grey { background:#F3F4F6; color:#6b7280; border:0.5px solid #e5e7eb; }

/* ── Actions ─────────────────────────────────────────────────── */
.pc-actions { display:flex; align-items:center; gap:6px; }

/* ── Buttons ─────────────────────────────────────────────────── */
.pc-btn { display:inline-flex; align-items:center; gap:6px; font-size:12px; font-weight:500; padding:7px 15px; border-radius:7px; border:none; cursor:pointer; transition:all .13s; }
.pc-btn--primary { background:#185FA5; color:#fff; }
.pc-btn--primary:hover { background:#0F4A84; transform:translateY(-1px); }
.pc-btn--purple  { background:#534AB7; color:#fff; }
.pc-btn--purple:hover  { background:#3C3489; transform:translateY(-1px); }
.pc-btn--ghost   { background:#f3f4f6; color:#374151; border:0.5px solid #e5e7eb; }
.pc-btn--ghost:hover   { background:#185FA5; color:#fff; border-color:#185FA5; }
.pc-btn--clear   { background:#185ea51c; color:#374151; }
.pc-btn--clear:hover   { background:#fee2e2; color:#b91c1c; }
.pc-btn--danger  { background:#FAECE7; color:#993C1D; border:0.5px solid #f5c6b8; }
.pc-btn--danger:hover  { background:#993C1D; color:#fff; }
.pc-btn:disabled { opacity:.55; cursor:not-allowed; transform:none !important; }

/* ── Modals ──────────────────────────────────────────────────── */
.pc-modal-backdrop { position:fixed; inset:0; background:rgba(17,24,39,.45); backdrop-filter:blur(2px); z-index:9999; display:flex; align-items:center; justify-content:center; opacity:0; transition:opacity .2s; }
.pc-modal-backdrop.pc-modal-backdrop--visible { opacity:1; }
.pc-modal { background:#fff; border-radius:14px; width:100%; max-width:520px; box-shadow:0 20px 50px rgba(0,0,0,.18); transform:translateY(12px) scale(.98); transition:transform .2s; }
.pc-modal-backdrop--visible .pc-modal { transform:translateY(0) scale(1); }
.pc-modal--sm { max-width:420px; }
.pc-modal__header { display:flex; align-items:flex-start; justify-content:space-between; padding:20px 20px 12px; background:#fafafa; border-bottom:0.5px solid #e5e7eb; border-radius:14px 14px 0 0; }
.pc-modal__eyebrow { font-size:10px; font-weight:500; text-transform:uppercase; letter-spacing:.07em; color:#9ca3af; margin:0 0 3px; }
.pc-modal__title  { font-size:15px; font-weight:600; color:#111827; margin:0; }
.pc-modal__close  { width:30px; height:30px; border-radius:7px; background:#f3f4f6; border:none; font-size:18px; color:#6b7280; cursor:pointer; display:flex; align-items:center; justify-content:center; transition:background .13s; flex-shrink:0; line-height:1; }
.pc-modal__close:hover { background:#e5e7eb; color:#111827; }
.pc-modal__body   { padding:20px; }
.pc-modal__footer { display:flex; align-items:center; justify-content:flex-end; gap:8px; padding:16px 20px 20px; background:#fafafa; border-top:0.5px solid #e5e7eb; border-radius:0 0 14px 14px; }

/* ── Form elements ───────────────────────────────────────────── */
.pc-form-row   { display:grid; grid-template-columns:1fr 1fr; gap:14px; margin-bottom:14px; }
.pc-form-group { display:flex; flex-direction:column; gap:5px; margin-bottom:0; }
.pc-label { font-size:10px; font-weight:500; text-transform:uppercase; letter-spacing:.07em; color:#9ca3af; }
.pc-input { padding:8px 10px; font-size:13px; border:0.5px solid #e5e7eb; border-radius:7px; color:#374151; outline:none; transition:border-color .13s, box-shadow .13s; background:#fff; width:100%; box-sizing:border-box; }
.pc-input:focus { border-color:#185FA5; box-shadow:0 0 0 3px rgba(24,95,165,.1); }
.pc-hint { font-size:11px; color:#9ca3af; margin:3px 0 0; }

/* ── Toggle ──────────────────────────────────────────────────── */
.pc-toggle-row   { display:flex; align-items:center; gap:10px; margin-top:4px; }
.pc-toggle       { position:relative; display:inline-flex; align-items:center; cursor:pointer; }
.pc-toggle input { position:absolute; opacity:0; width:0; height:0; }
.pc-toggle__track { display:block; width:40px; height:22px; border-radius:99px; background:#e5e7eb; transition:background .2s; position:relative; }
.pc-toggle__track::after { content:''; position:absolute; top:3px; left:3px; width:16px; height:16px; border-radius:50%; background:#fff; transition:transform .2s; box-shadow:0 1px 3px rgba(0,0,0,.2); }
.pc-toggle input:checked + .pc-toggle__track { background:#185FA5; }
.pc-toggle input:checked + .pc-toggle__track::after { transform:translateX(18px); }
.pc-toggle-label { font-size:13px; color:#374151; font-weight:500; }

/* ── Delete warning ──────────────────────────────────────────── */
.pc-delete-warning { display:flex; align-items:flex-start; gap:12px; background:#FAECE7; border:0.5px solid #f5c6b8; border-radius:10px; padding:14px; }
.pc-delete-warning__icon { color:#993C1D; flex-shrink:0; margin-top:1px; }
.pc-delete-warning__text { font-size:13px; color:#374151; margin:0; line-height:1.6; }

/* ── Responsive ──────────────────────────────────────────────── */
@media (max-width:768px) {
    .pc-strip { flex-wrap:wrap; }
    .pc-strip__divider { display:none; }
    .pc-search { width:130px; }
    .pc-toolbar { flex-direction:column; align-items:flex-start; }
    .pc-form-row { grid-template-columns:1fr; }
}
@media (max-width:540px) {
    .pc-filter-tabs { flex-wrap:wrap; }
    .pc-search { width:100%; }
}
</style>
@endpush

@push('script')
<script>
(function () {
    'use strict';

    // ── Helpers ───────────────────────────────────────────────
    function openModal(id) {
        const el = document.getElementById(id);
        el.style.display = 'flex';
        requestAnimationFrame(() => el.classList.add('pc-modal-backdrop--visible'));
    }

    function closeModal(id) {
        const el = document.getElementById(id);
        el.classList.remove('pc-modal-backdrop--visible');
        setTimeout(() => { el.style.display = 'none'; }, 200);
    }

    // Close on backdrop click or [data-dismiss]
    document.querySelectorAll('.pc-modal-backdrop').forEach(backdrop => {
        backdrop.addEventListener('click', e => {
            if (e.target === backdrop) closeModal(backdrop.id);
        });
    });
    document.querySelectorAll('[data-dismiss]').forEach(btn => {
        btn.addEventListener('click', () => closeModal(btn.dataset.dismiss));
    });

    // ── Slug auto-generation (create only) ───────────────────
    document.getElementById('createName').addEventListener('input', function () {
        document.getElementById('createSlug').value = this.value
            .toLowerCase().trim()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-+|-+$/g, '');
    });

    // ── Status toggle label ───────────────────────────────────
    const editStatusToggle = document.getElementById('editStatus');
    const editStatusLabel  = document.getElementById('editStatusLabel');
    editStatusToggle.addEventListener('change', function () {
        editStatusLabel.textContent = this.checked ? '{{ __("Active") }}' : '{{ __("Inactive") }}';
    });

    // ── Filter tabs ───────────────────────────────────────────
    document.getElementById('filterTabs').addEventListener('click', function (e) {
        const tab = e.target.closest('.pc-filter-tab');
        if (!tab) return;
        document.querySelectorAll('.pc-filter-tab').forEach(t => t.classList.remove('pc-filter-tab--active'));
        tab.classList.add('pc-filter-tab--active');
        filterRows();
    });

    document.getElementById('searchInput').addEventListener('input', filterRows);

    function filterRows() {
        const activeTab = document.querySelector('.pc-filter-tab--active')?.dataset.filter || 'all';
        const search    = document.getElementById('searchInput').value.toLowerCase().trim();
        const rows      = document.querySelectorAll('#categoriesBody .pc-row');

        rows.forEach(row => {
            const type   = row.dataset.type;
            const status = parseInt(row.dataset.status);
            const name   = row.dataset.name;

            let show = true;
            if (activeTab === 'product'  && type !== 'product')  show = false;
            if (activeTab === 'service'  && type !== 'service')  show = false;
            if (activeTab === 'active'   && status !== 1)        show = false;
            if (activeTab === 'inactive' && status !== 0)        show = false;
            if (search && !name.includes(search))                show = false;

            row.style.display = show ? '' : 'none';
        });
    }

    // ── Open create modal ─────────────────────────────────────
    ['openCreateModal', 'openCreateModalEmpty'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.addEventListener('click', () => {
            document.getElementById('createForm').reset();
            // Restore the default affiliate commission after reset
            document.getElementById('createAffiliateCommission').value = '2';
            openModal('createModal');
        });
    });

    // ── Create form submit ────────────────────────────────────
    document.getElementById('createForm').addEventListener('submit', function (e) {
        e.preventDefault();
        const btn = document.getElementById('createSubmitBtn');
        btn.disabled = true;
        btn.textContent = '{{ __("Creating…") }}';

        const formData = new FormData(this);

        fetch('{{ route("admin.product-categories.store") }}', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
        })
        .then(r => r.json())
        .then(data => {
            if (data.success !== false && !data.errors) {
                toastr.success('{{ __("Category created successfully.") }}');
                closeModal('createModal');
                setTimeout(() => location.reload(), 1000);
            } else {
                const msg = data.message || Object.values(data.errors || {})[0]?.[0] || '{{ __("Validation failed.") }}';
                toastr.error(msg);
            }
        })
        .catch(() => toastr.error('{{ __("Something went wrong.") }}'))
        .finally(() => {
            btn.disabled = false;
            btn.textContent = '{{ __("Create Category") }}';
        });
    });

    // ── Open edit modal ───────────────────────────────────────
    document.querySelectorAll('.pc-edit-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            document.getElementById('editId').value                      = this.dataset.id;
            document.getElementById('editName').value                    = this.dataset.name;
            document.getElementById('editSlug').value                    = this.dataset.slug;
            document.getElementById('editType').value                    = this.dataset.type;
            document.getElementById('editCommission').value              = this.dataset.commission;
            document.getElementById('editAffiliateCommission').value     = this.dataset.affiliateCommission;

            const isActive = parseInt(this.dataset.status) === 1;
            editStatusToggle.checked    = isActive;
            editStatusLabel.textContent = isActive ? '{{ __("Active") }}' : '{{ __("Inactive") }}';

            openModal('editModal');
        });
    });

    // ── Edit form submit ──────────────────────────────────────
    document.getElementById('editForm').addEventListener('submit', function (e) {
        e.preventDefault();
        const btn = document.getElementById('editSubmitBtn');
        btn.disabled = true;
        btn.textContent = '{{ __("Saving…") }}';

        const id       = document.getElementById('editId').value;
        const formData = new FormData(this);

        // status checkbox: send 0 if unchecked
        if (!editStatusToggle.checked) {
            formData.set('status', '0');
        } else {
            formData.set('status', '1');
        }

        fetch(`/admin/product-categories/${id}`, {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
        })
        .then(r => r.json())
        .then(data => {
            if (data.success !== false && !data.errors) {
                toastr.success('{{ __("Category updated successfully.") }}');
                closeModal('editModal');
                setTimeout(() => location.reload(), 1000);
            } else {
                const msg = data.message || Object.values(data.errors || {})[0]?.[0] || '{{ __("Validation failed.") }}';
                toastr.error(msg);
            }
        })
        .catch(() => toastr.error('{{ __("Something went wrong.") }}'))
        .finally(() => {
            btn.disabled = false;
            btn.textContent = '{{ __("Save Changes") }}';
        });
    });

    // ── Open delete modal ─────────────────────────────────────
    document.querySelectorAll('.pc-delete-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const count = parseInt(this.dataset.count);
            if (count > 0) {
                toastr.error('{{ __("Cannot delete a category that has products assigned to it.") }}');
                return;
            }
            document.getElementById('deleteId').value                    = this.dataset.id;
            document.getElementById('deleteNameDisplay').textContent     = this.dataset.name;
            openModal('deleteModal');
        });
    });

    // ── Confirm delete ────────────────────────────────────────
    document.getElementById('confirmDeleteBtn').addEventListener('click', function () {
        const id  = document.getElementById('deleteId').value;
        const btn = this;
        btn.disabled = true;
        btn.textContent = '{{ __("Deleting…") }}';

        const formData = new FormData();
        formData.append('_token', '{{ csrf_token() }}');
        formData.append('_method', 'DELETE');

        fetch(`/admin/product-categories/${id}`, {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
        })
        .then(r => r.json())
        .then(data => {
            if (data.success !== false) {
                toastr.success('{{ __("Category deleted.") }}');
                closeModal('deleteModal');
                setTimeout(() => location.reload(), 1000);
            } else {
                toastr.error(data.message || '{{ __("Delete failed.") }}');
            }
        })
        .catch(() => toastr.error('{{ __("Something went wrong.") }}'))
        .finally(() => {
            btn.disabled = false;
            btn.textContent = '{{ __("Yes, Delete") }}';
        });
    });

})();
</script>
@endpush