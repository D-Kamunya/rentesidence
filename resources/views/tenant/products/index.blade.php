@extends('tenant.layouts.app')

@section('content')
    @if (session('success'))
        <script>
            localStorage.removeItem('cartItems');
        </script>
    @endif

    <div class="main-content">
        <div class="page-content">
            <div class="page-content-wrapper">
                <div class="container-fluid">
                    <div class="page-content-wrapper bg-white p-30 radius-20">
                        <div class="container">
                            {{-- Page Title --}}
                            @php
                                $pageTitle = 'Marketplace';
                            @endphp

                            {{-- Page Header --}}
                            <div class="dash-header mb-4">
                                <div>
                                    <h2 class="dash-title">{{ __('Marketplace') }}</h2>
                                    <p class="dash-subtitle">
                                        {{ __('Browse products and services from your community and have them delivered to your doorstep!') }}
                                    </p>
                                </div>
                                {{-- Floating cart is handled below, but breadcrumb goes here --}}
                                <nav aria-label="breadcrumb">
                                    <ol class="mp-breadcrumb">
                                        <li><a href="{{ route('tenant.dashboard') }}">{{ __('Dashboard') }}</a></li>
                                        <li aria-current="page">Marketplace</li>
                                    </ol>
                                </nav>
                            </div>

                            {{-- Filter Bar --}}
                            <div class="filter-bar mb-4">
                                <form method="GET" action="{{ route('tenant.product.index') }}" class="filter-bar__form">
                                    <div class="filter-bar__filters">
                                        <div class="filter-select">
                                            <select name="type" class="form-select-custom">
                                                <option value="">{{ __('Select Type') }}</option>
                                                <option value="product" {{ request('type') == 'product' ? 'selected' : '' }}>{{ __('Product') }}</option>
                                                <option value="service" {{ request('type') == 'service' ? 'selected' : '' }}>{{ __('Service') }}</option>
                                            </select>
                                        </div>
                                        <div class="filter-select">
                                            <select name="category" class="form-select-custom">
                                                <option value="">{{ __('Select Category') }}</option>
                                                @foreach($categories as $category)
                                                    <option value="{{ $category }}" {{ request('category') == $category ? 'selected' : '' }}>
                                                        {{ ucfirst($category) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="filter-bar__actions">
                                        <button type="submit" class="filter-btn filter-btn--primary">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none">
                                                <path d="M3 6h18M6 12h12M10 18h4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                            </svg>
                                            {{ __('Filter') }}
                                        </button>
                                        @if(request('type') || request('category'))
                                            <a href="{{ route('tenant.product.index') }}" class="filter-btn filter-btn--secondary">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none">
                                                    <path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                                </svg>
                                                {{ __('Reset') }}
                                            </a>
                                        @endif
                                    </div>
                                </form>
                            </div>

                            {{-- Products Grid --}}
                            @if($products->count() > 0)
                                <div class="products-grid">
                                    @foreach ($products as $product)
                                        @php
                                            $images = json_decode($product->images, true);
                                            $firstImage = (is_array($images) && count($images) > 0) ? $images[0] : null;
                                        @endphp

                                        <div class="product-card">
                                            <div class="product-card__image-wrapper">
                                                <a href="{{ route('tenant.product.details', $product->id) }}">
                                                    @if($firstImage)
                                                        <img src="{{ asset('storage/' . $firstImage) }}"
                                                             alt="{{ $product->name }}"
                                                             class="product-card__image">
                                                    @else
                                                        <div class="product-card__image-placeholder">
                                                            <svg width="40" height="40" viewBox="0 0 24 24" fill="none">
                                                                <rect x="3" y="3" width="18" height="18" rx="2" stroke="currentColor" stroke-width="1.5"/>
                                                                <circle cx="8.5" cy="8.5" r="1.5" fill="currentColor"/>
                                                                <path d="M21 15l-5-5L8 21h13v-6z" fill="currentColor" opacity="0.5"/>
                                                            </svg>
                                                        </div>
                                                    @endif
                                                </a>
                                                <span class="product-card__type-badge product-card__type-badge--{{ $product->type }}">
                                                    {{ ucfirst($product->type) }}
                                                </span>
                                            </div>

                                            <div class="product-card__content">
                                                {{-- .product-title is what JS reads via querySelector('.product-title') --}}
                                                <h3 class="product-card__title product-title">
                                                    <a href="{{ route('tenant.product.details', $product->id) }}" class="product-card__title-link">
                                                        {{ $product->name }}
                                                    </a>
                                                </h3>
                                                <p class="product-card__category">{{ $product->category }}</p>
                                                {{-- .product-price is what JS reads for price — keep this class --}}
                                                <p class="product-card__price product-price">Ksh {{ number_format($product->price, 2) }}</p>

                                                {{-- Hidden fields for JS cart logic --}}
                                                <span class="product-id d-none">{{ $product->id }}</span>

                                                <div class="product-card__actions">
                                                    <button class="product-action-btn product-action-btn--cart add-to-cart-button"
                                                            title="{{ __('Add to Cart') }}">
                                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                                                            <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4zM3 6h18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                                            <line x1="16" y1="10" x2="8" y2="10" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                                        </svg>
                                                    </button>
                                                    <button class="product-action-btn product-action-btn--view more-button"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#product-details-{{ $product->id }}"
                                                            title="{{ __('Quick View') }}">
                                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                                            <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.8"/>
                                                        </svg>
                                                    </button>
                                                    <a href="{{ route('tenant.product.details', $product->id) }}"
                                                       class="product-action-btn product-action-btn--details"
                                                       title="{{ __('View Details') }}">
                                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                                                            <path d="M5 12h14M12 5l7 7-7 7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                                        </svg>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Quick View Modal --}}
                                        <div class="modal fade" id="product-details-{{ $product->id }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered modal-lg">
                                                <div class="modal-content modal-content-custom">
                                                    <div class="modal-header-custom">
                                                        <h5 class="modal-title-custom">{{ $product->name }}</h5>
                                                        <button type="button" class="modal-close-custom" data-bs-dismiss="modal" aria-label="Close">
                                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                                                                <path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                                            </svg>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body-custom">
                                                        <div class="product-modal-layout">

                                                            {{-- LEFT: MEDIA --}}
                                                            <div class="product-media">
                                                                <div class="modal-product-image">
                                                                    @if($firstImage)
                                                                        <img id="mainImage-{{ $product->id }}"
                                                                             src="{{ asset('storage/' . $firstImage) }}"
                                                                             alt="{{ $product->name }}">
                                                                    @else
                                                                        <div class="product-card__image-placeholder">
                                                                            <svg width="48" height="48" viewBox="0 0 24 24" fill="none">
                                                                                <rect x="3" y="3" width="18" height="18" rx="2" stroke="currentColor" stroke-width="1.5"/>
                                                                                <circle cx="8.5" cy="8.5" r="1.5" fill="currentColor"/>
                                                                                <path d="M21 15l-5-5L8 21h13v-6z" fill="currentColor" opacity="0.5"/>
                                                                            </svg>
                                                                        </div>
                                                                    @endif
                                                                </div>

                                                                @if(is_array($images) && count($images) > 1)
                                                                    <div class="image-gallery-wrapper">
                                                                        <button class="gallery-scroll-btn gallery-scroll-btn--left" type="button">‹</button>
                                                                        <div class="image-gallery">
                                                                            @foreach($images as $index => $image)
                                                                                <div class="gallery-item {{ $index === 0 ? 'active' : '' }}">
                                                                                    <img src="{{ asset('storage/' . $image) }}"
                                                                                         data-full="{{ asset('storage/' . $image) }}"
                                                                                         alt="{{ $product->name }}">
                                                                                </div>
                                                                            @endforeach
                                                                        </div>
                                                                        <button class="gallery-scroll-btn gallery-scroll-btn--right" type="button">›</button>
                                                                    </div>
                                                                @endif
                                                            </div>

                                                            {{-- RIGHT: INFO --}}
                                                            {{-- .product-card class added so JS closest('.product-card') works from modal button --}}
                                                            <div class="product-info product-card">
                                                                {{-- .product-title: JS reads textContent of this --}}
                                                                <h3 class="product-title">{{ $product->name }}</h3>
                                                                {{-- .product-price: JS reads textContent for price --}}
                                                                <div class="product-price product-price-display">
                                                                    Ksh {{ number_format($product->price, 2) }}
                                                                </div>
                                                                <div class="product-description">
                                                                    {{ $product->description ?: __('No description available.') }}
                                                                </div>
                                                                <div class="product-meta">
                                                                    <span class="badge badge--category">{{ $product->category }}</span>
                                                                    <span class="badge badge--{{ $product->type }}">{{ ucfirst($product->type) }}</span>
                                                                </div>
                                                                <div class="modal-cart-actions">
                                                                    <button class="theme-btn-primary add-to-cart-button" style="width: 100%; justify-content: center;">
                                                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                                                                            <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4zM3 6h18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                                                            <line x1="16" y1="10" x2="8" y2="10" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                                                        </svg>
                                                                        {{ __('Add to Cart') }}
                                                                    </button>
                                                                    <a href="{{ route('tenant.product.details', $product->id) }}" class="modal-details-link">
                                                                        {{ __('View Full Details') }} →
                                                                    </a>
                                                                </div>
                                                                {{-- Hidden fields for JS cart --}}
                                                                <span class="product-id d-none">{{ $product->id }}</span>
                                                                {{-- img needed by JS: productElement.querySelector('img').src --}}
                                                                @if($firstImage)
                                                                    <img src="{{ asset('storage/' . $firstImage) }}" alt="{{ $product->name }}" class="d-none">
                                                                @endif
                                                            </div>

                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    @endforeach
                                </div>

                                {{-- Pagination --}}
                                <div class="pagination-wrapper">
                                    {{ $products->links() }}
                                </div>

                            @else
                                <div class="empty-state">
                                    <div class="empty-state__icon">
                                        <svg width="64" height="64" viewBox="0 0 24 24" fill="none">
                                            <rect x="3" y="3" width="18" height="18" rx="2" stroke="currentColor" stroke-width="1.5"/>
                                            <circle cx="8.5" cy="8.5" r="1.5" fill="currentColor"/>
                                            <path d="M21 15l-5-5L8 21h13v-6z" fill="currentColor" opacity="0.5"/>
                                        </svg>
                                    </div>
                                    <h4 class="empty-state__title">{{ __('No products found') }}</h4>
                                    <p class="empty-state__text">{{ __('Check back later for new products and services.') }}</p>
                                </div>
                            @endif

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Floating Cart Button --}}
    <div id="floating-cart-button" class="floating-cart" data-url="{{ route('tenant.product.pay') }}">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
            <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4zM3 6h18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            <line x1="16" y1="10" x2="8" y2="10" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
        </svg>
        <span id="cart-counter" class="cart-counter">0</span>
    </div>

    <input type="hidden" id="getAllTenantRoute" value="{{ route('owner.tenant.index', ['type' => 'all']) }}">
    <input type="hidden" id="getPropertyUnitsRoute" value="{{ route('owner.property.getPropertyUnits') }}">

@endsection

@push('script')
    <script src="{{ asset('assets/js/custom/tenant-product.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.image-gallery-wrapper').forEach(wrapper => {
                const gallery   = wrapper.querySelector('.image-gallery');
                const leftBtn   = wrapper.querySelector('.gallery-scroll-btn--left');
                const rightBtn  = wrapper.querySelector('.gallery-scroll-btn--right');
                const modal     = wrapper.closest('.modal');
                const mainImage = modal.querySelector('.modal-product-image img');

                leftBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    gallery.scrollBy({ left: -120, behavior: 'smooth' });
                });
                rightBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    gallery.scrollBy({ left: 120, behavior: 'smooth' });
                });
                wrapper.querySelectorAll('.gallery-item').forEach(item => {
                    item.addEventListener('click', (e) => {
                        e.stopPropagation();
                        const img = item.querySelector('img');
                        if (mainImage) mainImage.src = img.dataset.full;
                        wrapper.querySelectorAll('.gallery-item').forEach(i => i.classList.remove('active'));
                        item.classList.add('active');
                    });
                });
            });
        });
    </script>
@endpush

@push('style')
<style>
    /* ── Page Header ─────────────────────────────────────── */
    .dash-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 1rem;
    }
    .dash-title {
        font-size: 22px;
        font-weight: 500;
        color: #111827;
        margin: 0 0 4px;
    }
    .dash-subtitle {
        font-size: 14px;
        color: #6b7280;
        margin: 0;
    }
    .mp-breadcrumb {
        display: flex;
        align-items: center;
        gap: 6px;
        list-style: none;
        padding: 0;
        margin: 0;
        font-size: 13px;
        color: #9ca3af;
    }
    .mp-breadcrumb li:not(:last-child)::after {
        content: '/';
        margin-left: 6px;
        color: #d1d5db;
    }
    .mp-breadcrumb a {
        color: #185FA5;
        text-decoration: none;
    }
    .mp-breadcrumb a:hover { text-decoration: underline; }

    /* ── Primary button ──────────────────────────────────── */
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
        border: none;
        cursor: pointer;
    }
    .theme-btn-primary:hover {
        background: #0F4A84;
        color: #fff;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(24, 95, 165, 0.25);
    }

    /* ── Filter Bar ──────────────────────────────────────── */
    .filter-bar {
        background: #fafafa;
        border: 0.5px solid #e5e7eb;
        border-radius: 12px;
        padding: 1rem 1.25rem;
    }
    .filter-bar__form {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 1rem;
    }
    .filter-bar__filters {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        flex-wrap: wrap;
    }
    .filter-select { min-width: 160px; }
    .form-select-custom {
        width: 100%;
        padding: 8px 32px 8px 12px;
        font-size: 13px;
        color: #374151;
        background-color: #fff;
        border: 0.5px solid #e5e7eb;
        border-radius: 8px;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='none'%3E%3Cpath d='M6 9l6 6 6-6' stroke='%236b7280' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 10px center;
        cursor: pointer;
        transition: border-color .15s, box-shadow .15s;
    }
    .form-select-custom:focus {
        outline: none;
        border-color: #185FA5;
        box-shadow: 0 0 0 3px rgba(24, 95, 165, 0.1);
    }
    .filter-bar__actions {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .filter-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 14px;
        font-size: 13px;
        font-weight: 500;
        border-radius: 8px;
        text-decoration: none;
        transition: all .15s;
        border: none;
        cursor: pointer;
    }
    .filter-btn--primary  { background: #185FA5; color: #fff; }
    .filter-btn--primary:hover  { background: #0F4A84; }
    .filter-btn--secondary { background: #fff; color: #6b7280; border: 0.5px solid #e5e7eb; }
    .filter-btn--secondary:hover { background: #f3f4f6; color: #374151; }

    /* ── Products Grid ───────────────────────────────────── */
    .products-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        gap: 1.25rem;
        margin-bottom: 2rem;
    }
    .product-card {
        background: #fff;
        border: 0.5px solid #e5e7eb;
        border-radius: 12px;
        overflow: hidden;
        transition: box-shadow .2s, transform .2s;
    }
    .product-card:hover {
        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.07);
        transform: translateY(-2px);
    }
    .product-card__image-wrapper {
        position: relative;
        aspect-ratio: 1 / 1;
        background: #f9fafb;
        overflow: hidden;
        display: block;
    }
    .product-card__image-wrapper a { display: block; width: 100%; height: 100%; }
    .product-card__image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform .3s;
    }
    .product-card:hover .product-card__image { transform: scale(1.05); }
    .product-card__image-placeholder {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #d1d5db;
    }
    .product-card__type-badge {
        position: absolute;
        top: 10px;
        right: 10px;
        padding: 4px 10px;
        font-size: 10px;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: .03em;
        border-radius: 99px;
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(4px);
    }
    .product-card__type-badge--product { color: #185FA5; border: 0.5px solid rgba(24, 95, 165, 0.2); }
    .product-card__type-badge--service { color: #1D9E75; border: 0.5px solid rgba(29, 158, 117, 0.2); }
    .product-card__content { padding: 1rem; }
    .product-card__title {
        font-size: 15px;
        font-weight: 500;
        color: #111827;
        margin: 0 0 4px;
        line-height: 1.3;
        display: -webkit-box;
        -webkit-line-clamp: 1;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .product-card__title-link { text-decoration: none; color: inherit; }
    .product-card__title-link:hover { color: #185FA5; }
    .product-card__category { font-size: 12px; color: #6b7280; margin: 0 0 8px; }
    .product-card__price { font-size: 16px; font-weight: 600; color: #0F6E56; margin: 0 0 12px; }
    .product-card__actions {
        display: flex;
        align-items: center;
        gap: 6px;
        border-top: 0.5px solid #f3f4f6;
        padding-top: 12px;
    }
    .product-action-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        border-radius: 8px;
        border: none;
        background: transparent;
        cursor: pointer;
        transition: background .15s, color .15s;
        color: #6b7280;
        text-decoration: none;
    }
    .product-action-btn--cart:hover    { background: #E1F5EE; color: #1D9E75; }
    .product-action-btn--view:hover    { background: #f3f4f6; color: #374151; }
    .product-action-btn--details:hover { background: #E6F1FB; color: #185FA5; }

    /* ── Modal ───────────────────────────────────────────── */
    .modal-content.modal-content-custom {
        background: #fff;
        border-radius: 16px;
        border: none;
        overflow: hidden;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
    }
    .modal-header-custom {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1.25rem 1.5rem;
        border-bottom: 0.5px solid #e5e7eb;
    }
    .modal-title-custom {
        font-size: 18px;
        font-weight: 500;
        color: #111827;
        margin: 0;
    }
    .modal-close-custom {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        border-radius: 8px;
        border: none;
        background: transparent;
        color: #9ca3af;
        cursor: pointer;
        transition: background .15s, color .15s;
    }
    .modal-close-custom:hover { background: #f3f4f6; color: #374151; }
    .modal-body-custom { padding: 1.5rem; }

    .product-modal-layout {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }
    .product-media, .product-info { display: flex; flex-direction: column; }

    /* Main image */
    .modal-product-image {
        height: 280px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f6f7f9;
        border-radius: 14px;
        margin-bottom: 12px;
        overflow: hidden;
    }
    .modal-product-image img {
        max-height: 100%;
        max-width: 100%;
        object-fit: contain;
        transition: transform .3s ease;
    }

    /* Gallery */
    .image-gallery-wrapper { display: flex; align-items: center; gap: 8px; }
    .image-gallery {
        display: flex;
        gap: 10px;
        overflow-x: auto;
        scroll-snap-type: x mandatory;
        padding: 6px;
    }
    .image-gallery::-webkit-scrollbar { display: none; }
    .gallery-item {
        flex: 0 0 auto;
        width: 68px;
        height: 68px;
        border-radius: 10px;
        overflow: hidden;
        cursor: pointer;
        opacity: 0.6;
        transition: all .2s ease;
        scroll-snap-align: start;
        border: 2px solid transparent;
    }
    .gallery-item img { width: 100%; height: 100%; object-fit: cover; }
    .gallery-item.active { opacity: 1; border-color: #185FA5; transform: scale(1.05); }
    .gallery-item:hover  { opacity: 1; }
    .gallery-scroll-btn {
        flex-shrink: 0;
        border: none;
        background: #fff;
        width: 30px;
        height: 30px;
        border-radius: 50%;
        box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        cursor: pointer;
        font-size: 16px;
        line-height: 1;
        transition: background .15s, box-shadow .15s;
    }
    .gallery-scroll-btn:hover { background: #f9fafb; box-shadow: 0 4px 10px rgba(0,0,0,0.08); }

    /* Info panel */
    .product-info.product-card {
        padding: 1.25rem !important;
        background: #fafafa !important;
        border: 0.5px solid #e5e7eb !important;
        border-radius: 12px !important;
        box-shadow: none !important;
        transform: none !important;
        overflow: visible !important;
    }
    .product-title   { font-size: 20px; font-weight: 600; color: #111827; margin-bottom: 6px; }
    /* .product-price is reused by JS — style it appropriately in each context */
    .product-info .product-price { font-size: 22px; font-weight: 700; color: #0F6E56; margin-bottom: 12px; }
    .product-description { font-size: 14px; color: #4b5563; line-height: 1.6; margin-bottom: 14px; }
    .product-meta { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 20px; }

    .modal-cart-actions {
        display: flex;
        flex-direction: column;
        gap: 10px;
        margin-top: auto;
    }
    .modal-details-link {
        font-size: 13px;
        color: #185FA5;
        text-decoration: none;
        text-align: center;
    }
    .modal-details-link:hover { text-decoration: underline; }

    /* ── Badges ──────────────────────────────────────────── */
    .badge {
        display: inline-block;
        padding: 4px 10px;
        font-size: 12px;
        font-weight: 500;
        border-radius: 99px;
    }
    .badge--category { background: #f3f4f6; color: #5F5E5A; }
    .badge--product  { background: #E6F1FB; color: #185FA5; }
    .badge--service  { background: #E1F5EE; color: #1D9E75; }

    /* ── Empty State ─────────────────────────────────────── */
    .empty-state { text-align: center; padding: 3rem 1.5rem; }
    .empty-state__icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 100px;
        height: 100px;
        border-radius: 50%;
        background: #f3f4f6;
        color: #9ca3af;
        margin-bottom: 1.5rem;
    }
    .empty-state__title { font-size: 18px; font-weight: 500; color: #374151; margin: 0 0 8px; }
    .empty-state__text  { font-size: 14px; color: #9ca3af; margin: 0 0 1.5rem; }

    /* ── Pagination ──────────────────────────────────────── */
    .pagination-wrapper { display: flex; justify-content: center; margin-top: 1rem; }
    .pagination-wrapper nav { display: flex; gap: 4px; }
    .pagination-wrapper .page-link {
        padding: 8px 12px;
        font-size: 13px;
        color: #6b7280;
        background: #fff;
        border: 0.5px solid #e5e7eb;
        border-radius: 6px;
        text-decoration: none;
        transition: all .15s;
    }
    .pagination-wrapper .page-link:hover { background: #f3f4f6; color: #374151; }
    .pagination-wrapper .active .page-link { background: #185FA5; border-color: #185FA5; color: #fff; }

    /* ── Floating Cart ───────────────────────────────────── */
    .floating-cart {
        position: fixed;
        bottom: 28px;
        right: 28px;
        width: 52px;
        height: 52px;
        background: #185FA5;
        color: #fff;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 16px rgba(24, 95, 165, 0.35);
        cursor: pointer;
        z-index: 1000;
        transition: background .15s, transform .15s, box-shadow .15s;
    }
    .floating-cart:hover {
        background: #0F4A84;
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(24, 95, 165, 0.45);
    }
    .cart-counter {
        position: absolute;
        top: -4px;
        right: -4px;
        background: #ef4444;
        color: #fff;
        font-size: 10px;
        font-weight: 600;
        width: 18px;
        height: 18px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        line-height: 1;
    }

    /* ── Responsive ──────────────────────────────────────── */
    @media (max-width: 768px) {
        .products-grid { grid-template-columns: repeat(2, 1fr); gap: 1rem; }
        .filter-bar__form { flex-direction: column; align-items: stretch; }
        .filter-bar__filters { flex-direction: column; }
        .filter-select { width: 100%; }
        .filter-bar__actions { justify-content: flex-end; }
        .product-modal-layout { grid-template-columns: 1fr; }
        .gallery-scroll-btn { display: none; }
    }
    @media (max-width: 480px) {
        .products-grid { grid-template-columns: 1fr; }
    }
</style>
@endpush