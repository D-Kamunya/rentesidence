@extends('affiliate.layouts.app')

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="page-content-wrapper bg-white p-30 radius-20">

                @php $pageTitle = 'Marketing Materials'; @endphp

                {{-- Page Header --}}
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between border-bottom mb-4 pb-2">
                            <div class="page-title-left">
                                <h3 class="mb-sm-0">Marketing Materials</h3>
                            </div>
                            <div class="page-title-right">
                                <ol class="breadcrumb mb-0">
                                    <li class="breadcrumb-item"><a href="{{ route('affiliate.dashboard') }}">Dashboard</a></li>
                                    <li class="breadcrumb-item active">Materials</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Intro --}}
                <div class="d-flex align-items-start justify-content-between flex-wrap gap-3 mb-4">
                    <div>
                        <p class="at-subtitle">Browse and download brochures, pitch decks, images, and links to share with your prospects.</p>
                    </div>
                    {{-- Result count --}}
                    <div class="aml-result-count">
                        <span id="resultCount">{{ $materials->total() }}</span> material{{ $materials->total() !== 1 ? 's' : '' }}
                    </div>
                </div>

                {{-- Filter Bar --}}
                <div class="aml-filter-bar mb-4">

                    {{-- Search --}}
                    <div class="aml-search-wrap">
                        <svg class="aml-search-icon" width="13" height="13" viewBox="0 0 16 16" fill="none">
                            <circle cx="6.5" cy="6.5" r="5" stroke="currentColor" stroke-width="1.5"/>
                            <path d="M10.5 10.5l3 3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                        </svg>
                        <input
                            type="text"
                            id="searchInput"
                            class="aml-search"
                            placeholder="Search materials…"
                            value="{{ request('search') }}"
                            oninput="applyFilters()"
                        >
                    </div>

                    {{-- Type filter --}}
                    <div class="aml-filter-group" id="typeFilterGroup">
                        <button class="aml-filter-btn aml-filter-btn--all {{ !request('type') ? 'aml-filter-btn--active' : '' }}"
                                onclick="setTypeFilter(this, '')" data-type="">
                            All Types
                        </button>
                        <button class="aml-filter-btn aml-filter-btn--pdf {{ request('type') === 'pdf' ? 'aml-filter-btn--active' : '' }}"
                                onclick="setTypeFilter(this, 'pdf')" data-type="pdf">
                            <svg width="11" height="11" viewBox="0 0 16 16" fill="none">
                                <path d="M4 2h6l4 4v8H4V2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M10 2v4h4M6 9h4M6 11.5h2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            </svg>
                            PDFs
                        </button>
                        <button class="aml-filter-btn aml-filter-btn--png {{ request('type') === 'png' ? 'aml-filter-btn--active' : '' }}"
                                onclick="setTypeFilter(this, 'png')" data-type="png">
                            <svg width="11" height="11" viewBox="0 0 16 16" fill="none">
                                <rect x="1" y="2" width="14" height="12" rx="2" stroke="currentColor" stroke-width="1.5"/>
                                <circle cx="5.5" cy="5.5" r="1.5" stroke="currentColor" stroke-width="1.3"/>
                                <path d="M1 11l4-3 3 3 2-2 5 4" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            Images
                        </button>
                        <button class="aml-filter-btn aml-filter-btn--link {{ request('type') === 'link' ? 'aml-filter-btn--active' : '' }}"
                                onclick="setTypeFilter(this, 'link')" data-type="link">
                            <svg width="11" height="11" viewBox="0 0 16 16" fill="none">
                                <path d="M6.5 9.5a4 4 0 0 0 5.657 0l1.414-1.414a4 4 0 0 0-5.657-5.657L6.5 3.843" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                <path d="M9.5 6.5a4 4 0 0 0-5.657 0L2.43 7.914a4 4 0 0 0 5.657 5.657l1.414-1.414" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            </svg>
                            Links
                        </button>
                        <button class="aml-filter-btn aml-filter-btn--text {{ request('type') === 'text' ? 'aml-filter-btn--active' : '' }}"
                                onclick="setTypeFilter(this, 'text')" data-type="text">
                            <svg width="11" height="11" viewBox="0 0 16 16" fill="none">
                                <path d="M2 2h12v10H9l-3 2v-2H2V2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            Text
                        </button>
                    </div>

                    {{-- Category filter --}}
                    @if($categories->isNotEmpty())
                        <div class="aml-filter-group">
                            <select class="aml-category-select" onchange="setCategoryFilter(this)">
                                <option value="">All Categories</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }}>
                                        {{ ucfirst($cat) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                </div>

                {{-- Materials Grid --}}
                @if($materials->isEmpty())
                    <div class="aml-empty">
                        <div class="aml-empty__icon">
                            <svg width="40" height="40" viewBox="0 0 24 24" fill="none">
                                <path d="M7 3h10l4 4v14H3V3z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M14 3v5h7M8 13h8M8 17h5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            </svg>
                        </div>
                        <p class="aml-empty__title">No materials found</p>
                        <p class="aml-empty__sub">Try adjusting your filters or check back later.</p>
                    </div>
                @else
                    <div class="aml-grid" id="materialsGrid">
                        @foreach($materials as $m)
                            <div class="aml-card"
                                 data-type="{{ $m->type }}"
                                 data-category="{{ strtolower($m->category ?? '') }}"
                                 data-title="{{ strtolower($m->title) }}">

                                {{-- Card header: type icon + badges --}}
                                <div class="aml-card__header aml-card__header--{{ $m->type }}">
                                    <div class="aml-card__type-icon">
                                        @if($m->type === 'pdf')
                                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
                                                <path d="M6 2h9l6 6v14H6V2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                <path d="M15 2v7h7M9 13h6M9 17h4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                            </svg>
                                        @elseif($m->type === 'png')
                                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
                                                <rect x="2" y="3" width="20" height="18" rx="3" stroke="currentColor" stroke-width="1.5"/>
                                                <circle cx="8" cy="9" r="2" stroke="currentColor" stroke-width="1.3"/>
                                                <path d="M2 17l6-5 4 4 3-3 7 6" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        @elseif($m->type === 'link')
                                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
                                                <path d="M10 14a5 5 0 0 0 7.071 0l2.121-2.121a5 5 0 0 0-7.071-7.071l-1.06 1.06" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                                <path d="M14 10a5 5 0 0 0-7.071 0l-2.121 2.121a5 5 0 0 0 7.071 7.071l1.061-1.06" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                            </svg>
                                        @else
                                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
                                                <path d="M3 3h18v14h-7l-4 3v-3H3V3z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        @endif
                                    </div>
                                    <div class="d-flex flex-column align-items-end gap-1">
                                        <span class="at-badge mm-badge--{{ $m->type }}">{{ strtoupper($m->type) }}</span>
                                        @if($m->category)
                                            <span class="aml-cat-pill">{{ ucfirst($m->category) }}</span>
                                        @endif
                                    </div>
                                </div>

                                {{-- Card body --}}
                                <div class="aml-card__body">
                                    <h5 class="aml-card__title">{{ $m->title }}</h5>

                                    @if($m->type === 'link' && $m->content)
                                        <p class="aml-card__meta">
                                            <svg width="11" height="11" viewBox="0 0 16 16" fill="none" style="flex-shrink:0;">
                                                <circle cx="8" cy="8" r="6" stroke="currentColor" stroke-width="1.4"/>
                                                <path d="M5 8h6M8 5.5C6.5 7 6.5 9 8 10.5" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/>
                                            </svg>
                                            {{ parse_url($m->content, PHP_URL_HOST) ?? $m->content }}
                                        </p>
                                    @elseif($m->file_name)
                                        <p class="aml-card__meta">
                                            <svg width="11" height="11" viewBox="0 0 16 16" fill="none" style="flex-shrink:0;">
                                                <path d="M4 2h6l4 4v8H4V2z" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
                                                <path d="M10 2v4h4" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>
                                            </svg>
                                            {{ $m->file_name }}
                                        </p>
                                    @elseif($m->type === 'text' && $m->content)
                                        <p class="aml-card__snippet">{{ Str::limit(strip_tags($m->content), 90) }}</p>
                                    @endif
                                </div>

                                {{-- Card footer: action --}}
                                <div class="aml-card__footer">
                                    @if($m->type === 'link')
                                        <a href="{{ $m->content }}" target="_blank" rel="noopener noreferrer"
                                           class="aml-cta aml-cta--link">
                                            <svg width="12" height="12" viewBox="0 0 16 16" fill="none">
                                                <path d="M5 3h8v8M13 3L3 13" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                            Open Link
                                        </a>
                                        <button class="aml-cta-ghost" onclick="copyToClipboard('{{ addslashes($m->content) }}', this)">
                                            <svg width="12" height="12" viewBox="0 0 16 16" fill="none">
                                                <rect x="5" y="5" width="9" height="9" rx="1.5" stroke="currentColor" stroke-width="1.4"/>
                                                <path d="M11 5V3H3v8h2" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                            Copy
                                        </button>
                                    @elseif($m->type === 'text')
                                        <button class="aml-cta aml-cta--text" onclick="openTextModal('{{ addslashes($m->title) }}', `{{ addslashes($m->content) }}`)">
                                            <svg width="12" height="12" viewBox="0 0 16 16" fill="none">
                                                <path d="M2 2h12v10H9l-3 2v-2H2V2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                            View Content
                                        </button>
                                        <button class="aml-cta-ghost" onclick="copyToClipboard(`{{ addslashes($m->content) }}`, this)">
                                            <svg width="12" height="12" viewBox="0 0 16 16" fill="none">
                                                <rect x="5" y="5" width="9" height="9" rx="1.5" stroke="currentColor" stroke-width="1.4"/>
                                                <path d="M11 5V3H3v8h2" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                            Copy Text
                                        </button>
                                    @else
                                        {{-- PDF or Image --}}
                                        <a href="{{ asset('storage/'.$m->file_path) }}" download="{{ $m->file_name }}"
                                           class="aml-cta aml-cta--{{ $m->type }}">
                                            <svg width="12" height="12" viewBox="0 0 16 16" fill="none">
                                                <path d="M8 3v8M5 8l3 3 3-3" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                                                <path d="M3 13h10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                            </svg>
                                            Download
                                        </a>
                                        <a href="{{ asset('storage/'.$m->file_path) }}" target="_blank"
                                           class="aml-cta-ghost">
                                            <svg width="12" height="12" viewBox="0 0 16 16" fill="none">
                                                <path d="M8 3a5 5 0 1 0 0 10A5 5 0 0 0 8 3z" stroke="currentColor" stroke-width="1.4"/>
                                                <circle cx="8" cy="8" r="1.5" fill="currentColor"/>
                                            </svg>
                                            Preview
                                        </a>
                                    @endif
                                </div>

                            </div>
                        @endforeach
                    </div>

                    {{-- Pagination --}}
                    @if($materials->hasPages())
                        <div class="mt-4">{{ $materials->appends(request()->query())->links() }}</div>
                    @endif
                @endif

            </div>
        </div>
    </div>
</div>

{{-- Text Content Modal --}}
<div class="aml-modal-backdrop" id="textModalBackdrop" onclick="closeTextModal()" style="display:none;"></div>
<div class="aml-modal" id="textModal" style="display:none;">
    <div class="aml-modal__header">
        <h5 class="aml-modal__title" id="textModalTitle"></h5>
        <button class="aml-modal__close" onclick="closeTextModal()">
            <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
                <path d="M3 3l10 10M13 3L3 13" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
            </svg>
        </button>
    </div>
    <div class="aml-modal__body">
        <pre class="aml-modal__content" id="textModalContent"></pre>
    </div>
    <div class="aml-modal__footer">
        <button class="at-btn-primary" onclick="copyToClipboard(document.getElementById('textModalContent').textContent, this)">
            <svg width="12" height="12" viewBox="0 0 16 16" fill="none">
                <rect x="5" y="5" width="9" height="9" rx="1.5" stroke="currentColor" stroke-width="1.4"/>
                <path d="M11 5V3H3v8h2" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            Copy to Clipboard
        </button>
        <button class="at-btn-ghost" onclick="closeTextModal()">Close</button>
    </div>
</div>

{{-- Toast notification --}}
<div class="aml-toast" id="copyToast">Copied to clipboard!</div>

@include('admin.affiliates.materials._material_styles')

<style>
    /* ── Affiliate Materials Library ── */

    .aml-result-count {
        font-size:12px;font-weight:500;color:#9ca3af;
        background:#f3f4f6;padding:5px 12px;border-radius:99px;
        border:0.5px solid #e5e7eb;white-space:nowrap;
    }

    /* Filter bar */
    .aml-filter-bar {
        display:flex;align-items:center;flex-wrap:wrap;gap:10px;
        padding:14px 16px;background:#fafafa;
        border:0.5px solid #e5e7eb;border-radius:12px;
    }
    .aml-search-wrap { position:relative;flex:1;min-width:180px;max-width:280px; }
    .aml-search-icon {
        position:absolute;left:10px;top:50%;transform:translateY(-50%);
        color:#9ca3af;pointer-events:none;
    }
    .aml-search {
        width:100%;padding:7px 10px 7px 30px;font-size:13px;
        background:#fff;border:0.5px solid #d1d5db;border-radius:8px;
        outline:none;color:#111827;transition:border-color .15s,box-shadow .15s;
    }
    .aml-search:focus { border-color:#185FA5;box-shadow:0 0 0 3px rgba(24,95,165,.08); }

    .aml-filter-group { display:flex;align-items:center;flex-wrap:wrap;gap:6px; }
    .aml-filter-btn {
        display:inline-flex;align-items:center;gap:5px;padding:6px 12px;
        font-size:12px;font-weight:500;border-radius:7px;
        border:0.5px solid #e5e7eb;background:#fff;color:#6b7280;
        cursor:pointer;transition:background .15s,border-color .15s,color .15s;
    }
    .aml-filter-btn--active,
    .aml-filter-btn--all.aml-filter-btn--active { background:#111827;border-color:#111827;color:#fff; }
    .aml-filter-btn--pdf:not(.aml-filter-btn--all):hover,
    .aml-filter-btn--pdf.aml-filter-btn--active:not(.aml-filter-btn--all) { background:#FCEBEB;border-color:#F09595;color:#A32D2D; }
    .aml-filter-btn--png:not(.aml-filter-btn--all):hover,
    .aml-filter-btn--png.aml-filter-btn--active:not(.aml-filter-btn--all) { background:#E6F1FB;border-color:#85B7EB;color:#185FA5; }
    .aml-filter-btn--link:not(.aml-filter-btn--all):hover,
    .aml-filter-btn--link.aml-filter-btn--active:not(.aml-filter-btn--all) { background:#EEEDFE;border-color:#AFA9EC;color:#534AB7; }
    .aml-filter-btn--text:not(.aml-filter-btn--all):hover,
    .aml-filter-btn--text.aml-filter-btn--active:not(.aml-filter-btn--all) { background:#f3f4f6;border-color:#d1d5db;color:#374151; }

    .aml-category-select {
        padding:6px 28px 6px 10px;font-size:12px;font-weight:500;
        background:#fff url("data:image/svg+xml,%3Csvg width='10' height='6' viewBox='0 0 10 6' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M1 1l4 4 4-4' stroke='%236b7280' stroke-width='1.4' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E") no-repeat right 9px center;
        border:0.5px solid #e5e7eb;border-radius:7px;color:#374151;
        appearance:none;cursor:pointer;outline:none;transition:border-color .15s;
    }
    .aml-category-select:focus { border-color:#185FA5;box-shadow:0 0 0 3px rgba(24,95,165,.08); }

    /* Grid */
    .aml-grid {
        display:grid;
        grid-template-columns:repeat(auto-fill, minmax(280px, 1fr));
        gap:16px;
    }

    /* Card */
    .aml-card {
        background:#fff;border:0.5px solid #e5e7eb;border-radius:14px;
        overflow:hidden;display:flex;flex-direction:column;
        transition:box-shadow .2s,transform .2s;
    }
    .aml-card:hover { box-shadow:0 6px 20px rgba(0,0,0,.07);transform:translateY(-2px); }

    /* Card header area */
    .aml-card__header {
        display:flex;align-items:flex-start;justify-content:space-between;
        padding:18px 18px 14px;
    }
    .aml-card__header--pdf  { background:linear-gradient(135deg,#FCEBEB 0%,#fff5f5 100%); }
    .aml-card__header--png  { background:linear-gradient(135deg,#EEF5FD 0%,#f0f6ff 100%); }
    .aml-card__header--link { background:linear-gradient(135deg,#EEEDFE 0%,#f3f2ff 100%); }
    .aml-card__header--text { background:linear-gradient(135deg,#f3f4f6 0%,#fafafa 100%); }

    .aml-card__type-icon {
        width:48px;height:48px;border-radius:12px;
        display:flex;align-items:center;justify-content:center;
        background:rgba(255,255,255,.8);backdrop-filter:blur(4px);
        box-shadow:0 1px 4px rgba(0,0,0,.08);
    }
    .aml-card__header--pdf  .aml-card__type-icon { color:#A32D2D; }
    .aml-card__header--png  .aml-card__type-icon { color:#185FA5; }
    .aml-card__header--link .aml-card__type-icon { color:#534AB7; }
    .aml-card__header--text .aml-card__type-icon { color:#5F5E5A; }

    .aml-cat-pill {
        font-size:10px;font-weight:500;padding:2px 8px;border-radius:99px;
        background:rgba(255,255,255,.75);color:#6b7280;
        border:0.5px solid rgba(0,0,0,.08);
    }

    /* Card body */
    .aml-card__body { padding:14px 18px;flex:1;display:flex;flex-direction:column;gap:6px; }
    .aml-card__title { font-size:14px;font-weight:600;color:#111827;margin:0;line-height:1.4; }
    .aml-card__meta {
        display:flex;align-items:center;gap:5px;
        font-size:11px;color:#9ca3af;margin:0;word-break:break-all;
    }
    .aml-card__snippet {
        font-size:12px;color:#6b7280;margin:0;line-height:1.6;
        display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden;
    }

    /* Card footer */
    .aml-card__footer {
        display:flex;align-items:center;gap:8px;
        padding:12px 18px 14px;border-top:0.5px solid #f3f4f6;
    }

    /* CTA buttons */
    .aml-cta {
        display:inline-flex;align-items:center;gap:6px;padding:7px 14px;
        font-size:12px;font-weight:500;border-radius:8px;
        text-decoration:none;border:none;cursor:pointer;
        transition:background .15s,transform .15s;
    }
    .aml-cta:hover { transform:translateY(-1px); }
    .aml-cta--pdf  { background:#FCEBEB;color:#A32D2D;border:0.5px solid #F09595; }
    .aml-cta--pdf:hover  { background:#fad9d9;color:#A32D2D; }
    .aml-cta--png  { background:#E6F1FB;color:#185FA5;border:0.5px solid #85B7EB; }
    .aml-cta--png:hover  { background:#dbeeff;color:#185FA5; }
    .aml-cta--link { background:#EEEDFE;color:#534AB7;border:0.5px solid #AFA9EC; }
    .aml-cta--link:hover { background:#e4e3fd;color:#534AB7; }
    .aml-cta--text { background:#f3f4f6;color:#374151;border:0.5px solid #d1d5db; }
    .aml-cta--text:hover { background:#e9eaec;color:#111827; }

    .aml-cta-ghost {
        display:inline-flex;align-items:center;gap:5px;padding:7px 12px;
        font-size:12px;font-weight:500;color:#9ca3af;background:transparent;
        border:0.5px solid #e5e7eb;border-radius:8px;cursor:pointer;
        text-decoration:none;transition:background .15s,color .15s;
    }
    .aml-cta-ghost:hover { background:#f3f4f6;color:#374151; }

    /* Empty state */
    .aml-empty {
        padding:4rem 1rem;text-align:center;
        border:0.5px dashed #e5e7eb;border-radius:14px;
        background:#fafafa;
    }
    .aml-empty__icon { color:#d1d5db;margin-bottom:14px;display:flex;justify-content:center; }
    .aml-empty__title { font-size:15px;font-weight:500;color:#374151;margin:0 0 6px; }
    .aml-empty__sub   { font-size:13px;color:#9ca3af;margin:0; }

    /* Text modal */
    .aml-modal-backdrop {
        position:fixed;inset:0;background:rgba(0,0,0,.35);
        backdrop-filter:blur(2px);z-index:1040;
    }
    .aml-modal {
        position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);
        z-index:1050;background:#fff;border-radius:14px;
        width:min(600px, 95vw);max-height:80vh;
        display:flex;flex-direction:column;
        box-shadow:0 20px 60px rgba(0,0,0,.15);
    }
    .aml-modal__header {
        display:flex;align-items:center;justify-content:space-between;
        padding:16px 20px;border-bottom:0.5px solid #e5e7eb;
    }
    .aml-modal__title { font-size:15px;font-weight:600;color:#111827;margin:0; }
    .aml-modal__close {
        width:28px;height:28px;border-radius:7px;background:#f3f4f6;
        border:none;cursor:pointer;color:#6b7280;
        display:flex;align-items:center;justify-content:center;
        transition:background .15s;
    }
    .aml-modal__close:hover { background:#e5e7eb;color:#111827; }
    .aml-modal__body { padding:20px;overflow-y:auto;flex:1; }
    .aml-modal__content {
        font-size:13px;color:#374151;line-height:1.7;
        white-space:pre-wrap;word-break:break-word;
        font-family:inherit;margin:0;
        background:#f9fafb;border:0.5px solid #e5e7eb;
        border-radius:8px;padding:14px;
    }
    .aml-modal__footer {
        display:flex;align-items:center;gap:10px;
        padding:14px 20px;border-top:0.5px solid #e5e7eb;
    }

    /* Toast */
    .aml-toast {
        position:fixed;bottom:24px;right:24px;z-index:2000;
        background:#111827;color:#fff;font-size:13px;font-weight:500;
        padding:10px 18px;border-radius:9px;
        box-shadow:0 8px 24px rgba(0,0,0,.18);
        opacity:0;transform:translateY(8px);
        transition:opacity .25s,transform .25s;pointer-events:none;
    }
    .aml-toast--visible { opacity:1;transform:translateY(0); }

    /* Responsive */
    @media(max-width:576px) {
        .aml-filter-bar { flex-direction:column;align-items:flex-start; }
        .aml-search-wrap { max-width:100%;width:100%; }
        .aml-grid { grid-template-columns:1fr; }
    }
</style>

<script>
    // ── Client-side live search (supplements server-side) ──
    function applyFilters() {
        const q = document.getElementById('searchInput').value.toLowerCase().trim();
        const cards = document.querySelectorAll('.aml-card');
        let visible = 0;
        cards.forEach(card => {
            const title = card.dataset.title || '';
            const show = !q || title.includes(q);
            card.style.display = show ? '' : 'none';
            if (show) visible++;
        });
        const countEl = document.getElementById('resultCount');
        if (countEl) countEl.textContent = visible;
    }

    // ── Type filter (reloads page with query param) ──
    function setTypeFilter(btn, type) {
        const url = new URL(window.location.href);
        if (type) url.searchParams.set('type', type);
        else url.searchParams.delete('type');
        url.searchParams.delete('page');
        window.location.href = url.toString();
    }

    // ── Category filter ──
    function setCategoryFilter(select) {
        const url = new URL(window.location.href);
        if (select.value) url.searchParams.set('category', select.value);
        else url.searchParams.delete('category');
        url.searchParams.delete('page');
        window.location.href = url.toString();
    }

    // ── Copy to clipboard ──
    function copyToClipboard(text, btn) {
        navigator.clipboard.writeText(text).then(() => {
            showToast('Copied to clipboard!');
            if (btn) {
                const orig = btn.innerHTML;
                btn.innerHTML = btn.innerHTML.replace(/Copy.*/, 'Copied!');
                setTimeout(() => { btn.innerHTML = orig; }, 2000);
            }
        }).catch(() => {
            // Fallback
            const ta = document.createElement('textarea');
            ta.value = text;
            document.body.appendChild(ta);
            ta.select();
            document.execCommand('copy');
            document.body.removeChild(ta);
            showToast('Copied!');
        });
    }

    function showToast(msg) {
        const t = document.getElementById('copyToast');
        t.textContent = msg;
        t.classList.add('aml-toast--visible');
        setTimeout(() => t.classList.remove('aml-toast--visible'), 2400);
    }

    // ── Text modal ──
    function openTextModal(title, content) {
        document.getElementById('textModalTitle').textContent = title;
        document.getElementById('textModalContent').textContent = content;
        document.getElementById('textModalBackdrop').style.display = 'block';
        document.getElementById('textModal').style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    function closeTextModal() {
        document.getElementById('textModalBackdrop').style.display = 'none';
        document.getElementById('textModal').style.display = 'none';
        document.body.style.overflow = '';
    }

    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') closeTextModal();
    });
</script>
@endsection