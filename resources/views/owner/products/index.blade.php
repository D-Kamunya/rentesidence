@extends('owner.layouts.app')

@section('content')
<div class="main-content">
    <div class="page-content">
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
                            <h2 class="dash-title">{{ __('My Shop') }}</h2>
                            <p class="dash-subtitle">
                                {{ __('Manage your products and services') }}
                            </p>
                        </div>

                        @php
                            $owner = \App\Models\Owner::where('user_id', Auth::id())->firstOrFail();
                            $ownerPackage = \App\Models\OwnerPackage::where('user_id', $owner->user_id)
                                ->where('status', 1)
                                ->latest()
                                ->first();
                            $package = \App\Models\Package::find($ownerPackage?->package_id);
                            $maxListings = $package?->max_marketplace_listings ?? 0;
                            $currentListings = \App\Models\Product::where('owner_user_id', $owner->id)->count();

                            $hasReachedLimit = $maxListings > 0 && $currentListings >= $maxListings;
                            $isNearLimit     = $maxListings > 0 && ($maxListings - $currentListings) <= 3 && !$hasReachedLimit;
                        @endphp

                        @if($hasReachedLimit)
                            {{-- Limit reached: Upgrade CTA --}}
                            <a href="{{ route('owner.subscription.index', ['current_plan' => 'no']) }}"
                            class="d-block text-decoration-none theme-btn-primary upgrade-btn"
                            title="{{ __('You\'ve used all :max listings. Upgrade to add more.', ['max' => $maxListings]) }}">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                                    <polyline points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>
                                </svg>
                                {{ __('Upgrade to Add Product/Service') }}
                            </a>
                        @elseif($isNearLimit)
                            {{-- Near limit: Add with warning --}}
                            <a href="{{ route('owner.products.create') }}"
                            class="d-block text-decoration-none theme-btn-primary near-limit-btn"
                            title="{{ __('Only :remaining listings remaining', ['remaining' => $maxListings - $currentListings]) }}">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                                    <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                </svg>
                                {{ __('Add Product/Service') }}
                                <span class="remaining-badge">{{ $maxListings - $currentListings }} left</span>
                            </a>
                        @else
                            {{-- Normal: plenty of listings --}}
                            <a href="{{ route('owner.products.create') }}"
                            class="d-block text-decoration-none theme-btn-primary"
                            title="{{ __('Add Product/Service') }}">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                                    <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                </svg>
                                {{ __('Add Product/Service') }}
                            </a>
                        @endif
                    </div>

                    {{-- Filter Bar --}}
                    <div class="filter-bar mb-4">
                        <form method="GET" action="{{ route('owner.products.index') }}" class="filter-bar__form">
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
                                        <option value="foods" {{ request('category') == 'foods' ? 'selected' : '' }}>{{ __('Foods') }}</option>
                                        <option value="electronics" {{ request('category') == 'electronics' ? 'selected' : '' }}>{{ __('Electronics') }}</option>
                                        <option value="furniture" {{ request('category') == 'furniture' ? 'selected' : '' }}>{{ __('Furniture') }}</option>
                                        <option value="clothing" {{ request('category') == 'clothing' ? 'selected' : '' }}>{{ __('Clothing') }}</option>
                                        <option value="services" {{ request('category') == 'services' ? 'selected' : '' }}>{{ __('Services') }}</option>
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
                                    <a href="{{ route('owner.products.index') }}" class="filter-btn filter-btn--secondary">
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
                                    
                                    // Earnings Calculation
                                    $baseCommission  = optional($product->productCategory)->base_commission ?? 0;
                                    $packageMarkup   = $packageMarkup ?? 0;
                                    $packageDiscount = $packageDiscount ?? 0;
                                    $minCommission   = 3.0;

                                    $effectiveRate   = max($baseCommission + $packageMarkup - $packageDiscount, $minCommission);
                                    $commissionAmount = $product->price * ($effectiveRate / 100);
                                    $youEarn         = $product->price - $commissionAmount;
                                @endphp

                                <div class="product-card">
                                    <div class="product-card__image-wrapper">
                                        @if(is_array($images) && count($images) > 0)
                                            <img src="{{ asset('storage/' . $images[0]) }}" 
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
                                        @endif>
                                        <span class="product-card__type-badge product-card__type-badge--{{ $product->type }}">
                                            {{ ucfirst($product->type) }}
                                        </span>
                                    </div>
                                    
                                    <div class="product-card__content">
                                        <h3 class="product-card__title">{{ $product->name }}</h3>
                                        <p class="product-card__category">{{ $product->category }}</p>
                                        
                                        <p class="product-card__price">Ksh {{ number_format($product->price, 2) }}</p>

                                        <!-- Earnings + Effective Rate -->
                                        <div class="product-earnings-row">
                                            <div class="you-earn-box">
                                                <span class="you-earn-label">You Earn</span>
                                                <span class="you-earn-amount">Ksh {{ number_format($youEarn, 2) }}</span>
                                            </div>
                                            
                                            <div class="effective-rate-badge">
                                                <span class="rate-value">{{ number_format($effectiveRate, 1) }}%</span>
                                                <span class="rate-label">Rate</span>
                                            </div>
                                        </div>

                                        <div class="product-card__actions">
                                            <a href="{{ route('owner.products.edit', $product->id) }}" 
                                            class="product-action-btn product-action-btn--edit"
                                            title="{{ __('Edit') }}">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                                                    <path d="M16 3l5 5L8 21H3v-5L16 3z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                            </a>
                                            <form action="{{ route('owner.products.destroy', $product->id) }}" 
                                                method="POST" 
                                                class="delete-form"
                                                onsubmit="return confirm('{{ __('Are you sure you want to delete this product?') }}')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="product-action-btn product-action-btn--delete" title="{{ __('Delete') }}">
                                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                                                        <path d="M4 7h16M10 11v6M14 11v6M5 7l1 13a2 2 0 002 2h8a2 2 0 002-2l1-13M9 4V3a1 1 0 011-1h4a1 1 0 011 1v1" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                                    </svg>
                                                </button>
                                            </form>
                                            <button class="product-action-btn product-action-btn--view"
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#product-details-{{ $product->id }}"
                                                    title="{{ __('View Details') }}">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                                                    <circle cx="12" cy="12" r="2" fill="currentColor"/>
                                                    <circle cx="20" cy="12" r="2" fill="currentColor"/>
                                                    <circle cx="4" cy="12" r="2" fill="currentColor"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                {{-- ==================== PRODUCT DETAILS MODAL ==================== --}}
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

                                                    <!-- LEFT: MEDIA -->
                                                    <div class="product-media">
                                                        <div class="modal-product-image">
                                                            <img id="mainImage-{{ $product->id }}" 
                                                                src="{{ asset('storage/' . ($images[0] ?? '')) }}" 
                                                                alt="{{ $product->name }}">
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

                                                    <!-- RIGHT: INFO + EARNINGS -->
                                                    <div class="product-info">
                                                        <h3 class="product-title">{{ $product->name }}</h3>

                                                        <div class="product-price">
                                                            Ksh {{ number_format($product->price, 2) }}
                                                        </div>

                                                        <!-- Earnings Breakdown -->
                                                        <div class="earnings-breakdown">
                                                            <div class="earnings-breakdown__header">
                                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none">
                                                                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.8"/>
                                                                    <path d="M12 8v4m0 4h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                                                </svg>
                                                                Your Earnings Per Sale
                                                            </div>
                                                            
                                                            <div class="earnings-grid">
                                                                <div class="earning-item">
                                                                    <span class="earning-label">Listed Price</span>
                                                                    <span class="earning-value">Ksh {{ number_format($product->price, 2) }}</span>
                                                                </div>
                                                                <div class="earning-item">
                                                                    <span class="earning-label">Effective Rate</span>
                                                                    <span class="earning-value commission-rate">{{ number_format($effectiveRate, 1) }}%</span>
                                                                </div>
                                                                <div class="earning-item">
                                                                    <span class="earning-label">Platform Fee</span>
                                                                    <span class="earning-value fee">Ksh {{ number_format($commissionAmount, 2) }}</span>
                                                                </div>
                                                                <div class="earning-item earning-item--highlight">
                                                                    <span class="earning-label">You Earn</span>
                                                                    <span class="earning-value you-earn">Ksh {{ number_format($youEarn, 2) }}</span>
                                                                </div>
                                                            </div>
                                                            
                                                            <p class="earnings-note">
                                                                This is what you will receive after commission for each unit sold.<br>
                                                                Upgrade your plan to lower the effective rate.
                                                            </p>
                                                        </div>

                                                        <div class="product-description">
                                                            {{ $product->description ?: 'No description available.' }}
                                                        </div>

                                                        <div class="product-meta">
                                                            <span class="badge badge--category">{{ $product->category }}</span>
                                                            <span class="badge badge--{{ $product->type }}">{{ ucfirst($product->type) }}</span>
                                                        </div>
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
                            <p class="empty-state__text">{{ __('Get started by adding your first product or service.') }}</p>
                            <a href="{{ route('owner.products.create') }}" class="theme-btn-primary">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                                    <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                </svg>
                                {{ __('Add Product/Service') }}
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('style')
<style>
    /* ── Page header ─────────────────────────────────────── */
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
    }
    .theme-btn-primary:hover {
        background: #0F4A84;
        color: #fff;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(24, 95, 165, 0.25);
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
    .filter-select {
        min-width: 160px;
    }
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
    .filter-btn--primary {
        background: #185FA5;
        color: #fff;
    }
    .filter-btn--primary:hover {
        background: #0F4A84;
    }
    .filter-btn--secondary {
        background: #fff;
        color: #6b7280;
        border: 0.5px solid #e5e7eb;
    }
    .filter-btn--secondary:hover {
        background: #f3f4f6;
        color: #374151;
    }

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
    }
    .product-card__image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform .3s;
    }
    .product-card:hover .product-card__image {
        transform: scale(1.05);
    }
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
    .product-card__type-badge--product {
        color: #185FA5;
        border: 0.5px solid rgba(24, 95, 165, 0.2);
    }
    .product-card__type-badge--service {
        color: #1D9E75;
        border: 0.5px solid rgba(29, 158, 117, 0.2);
    }
    .product-card__content {
        padding: 1rem;
    }
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
    .product-card__category {
        font-size: 12px;
        color: #6b7280;
        margin: 0 0 8px;
    }
    .product-card__price {
        font-size: 16px;
        font-weight: 600;
        color: #0F6E56;
        margin: 0 0 12px;
    }
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
    }
    .product-action-btn--edit:hover {
        background: #E6F1FB;
        color: #185FA5;
    }
    .product-action-btn--delete:hover {
        background: #FDF4F1;
        color: #993C1D;
    }
    .product-action-btn--view:hover {
        background: #f3f4f6;
        color: #374151;
    }

    /* Product Card Earnings */
    .product-earnings-row {
        display: flex;
        gap: 8px;
        margin: 10px 0 14px 0;
    }

    .you-earn-box {
        flex: 1;
        background: #E1F5EE;
        border: 0.5px solid #0F6E56;
        border-radius: 8px;
        padding: 8px 10px;
        text-align: center;
    }

    .you-earn-label {
        font-size: 10px;
        font-weight: 500;
        color: #0F6E56;
        text-transform: uppercase;
        display: block;
        margin-bottom: 2px;
    }

    .you-earn-amount {
        font-size: 15px;
        font-weight: 700;
        color: #0F6E56;
    }

    .effective-rate-badge {
        background: #F3F4F6;
        border: 0.5px solid #D1D5DB;
        border-radius: 8px;
        padding: 8px 10px;
        text-align: center;
        min-width: 70px;
    }

    .rate-value {
        font-size: 15px;
        font-weight: 700;
        color: #854F0B;
        display: block;
    }

    .rate-label {
        font-size: 9px;
        font-weight: 500;
        color: #6b7280;
        text-transform: uppercase;
    }

    /* Modal Earnings Breakdown */
    .earnings-breakdown {
        background: #F0F7FF;
        border: 0.5px solid #B8D4F0;
        border-radius: 12px;
        padding: 16px;
        margin: 16px 0 20px 0;
    }

    .earnings-breakdown__header {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 13px;
        font-weight: 600;
        color: #185FA5;
        margin-bottom: 12px;
    }

    .earnings-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
    }

    .earning-item {
        background: #fff;
        border: 0.5px solid #e5e7eb;
        border-radius: 8px;
        padding: 10px;
    }

    .earning-item--highlight {
        background: #E1F5EE;
        border-color: #0F6E56;
    }

    .earning-label {
        font-size: 11px;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        display: block;
        margin-bottom: 4px;
    }

    .earning-value {
        font-weight: 600;
        font-size: 15px;
    }

    .earning-value.commission-rate { color: #854F0B; }
    .earning-value.fee { color: #993C1D; }
    .earning-value.you-earn { 
        color: #0F6E56; 
        font-size: 17px; 
    }

    .earnings-note {
        font-size: 12.5px;
        color: #6b7280;
        margin-top: 12px;
        line-height: 1.45;
    }

    /* ── Modal ───────────────────────────────────────────── */

    /* Main image */
    .modal-product-image {
        height: 320px;
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
        transition: transform 0.3s ease;
    }

    /* Gallery */
    .image-gallery-wrapper {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .image-gallery {
        display: flex;
        gap: 10px;
        overflow-x: auto;
        scroll-snap-type: x mandatory;
        padding: 6px;
    }

    .image-gallery::-webkit-scrollbar {
        display: none;
    }

    /* Thumbnails */
    .gallery-item {
        flex: 0 0 auto;
        width: 70px;
        height: 70px;
        border-radius: 10px;
        overflow: hidden;
        cursor: pointer;
        opacity: 0.6;
        transition: all 0.2s ease;
        scroll-snap-align: start;
        border: 2px solid transparent;
    }

    .gallery-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    /* Active thumbnail */
    .gallery-item.active {
        opacity: 1;
        border-color: #3b82f6;
        transform: scale(1.05);
    }

    /* Hover effect */
    .gallery-item:hover {
        opacity: 1;
    }

    /* Buttons */
    .gallery-scroll-btn {
        border: none;
        background: #fff;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        cursor: pointer;
    }

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

    .modal-close-custom:hover {
        background: #f3f4f6;
        color: #374151;
    }

    .modal-body-custom {
        padding: 1.5rem;
    }

    .gallery-scroll-btn:hover {
        background: #f9fafb;
        color: #111827;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
    }
    
    .gallery-scroll-btn--left {
        left: -10px;
    }
    
    .gallery-scroll-btn--right {
        right: -10px;
    }

    /* ── NEW MODAL LAYOUT ───────────────────────── */

    .product-modal-layout {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }

    .product-media {
        display: flex;
        flex-direction: column;
    }

    .product-info {
        display: flex;
        flex-direction: column;
    }

    /* Title */
    .product-title {
        font-size: 20px;
        font-weight: 600;
        color: #111827;
        margin-bottom: 6px;
    }

    /* Price */
    .product-price {
        font-size: 22px;
        font-weight: 700;
        color: #0F6E56; /* keep your green */
        margin-bottom: 12px;
    }

    /* Description */
    .product-description {
        font-size: 14px;
        color: #4b5563;
        line-height: 1.6;
        margin-bottom: 14px;
    }

    /* Meta */
    .product-meta {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    /* Fix Bootstrap override bug */
    .modal-content.modal-content-custom {
        background: #fff;
        border-radius: 16px;
        border: none;
        overflow: hidden;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
    }

    /* Responsive */
    @media (max-width: 768px) {
        .product-modal-layout {
            grid-template-columns: 1fr;
        }
    }

    /* ── Product Details ─────────────────────────────────── */
    .product-details-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
    }
    .detail-item {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }
    .detail-label {
        font-size: 11px;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: .03em;
        color: #9ca3af;
    }
    .detail-value {
        font-size: 14px;
        color: #374151;
    }
    .detail-value--price {
        font-size: 18px;
        font-weight: 600;
        color: #0F6E56;
    }
    .badge {
        display: inline-block;
        padding: 4px 10px;
        font-size: 12px;
        font-weight: 500;
        border-radius: 99px;
    }
    .badge--category {
        background: #f3f4f6;
        color: #5F5E5A;
    }
    .badge--product {
        background: #E6F1FB;
        color: #185FA5;
    }
    .badge--service {
        background: #E1F5EE;
        color: #1D9E75;
    }

    /* ── Empty State ─────────────────────────────────────── */
    .empty-state {
        text-align: center;
        padding: 3rem 1.5rem;
    }
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
    .empty-state__title {
        font-size: 18px;
        font-weight: 500;
        color: #374151;
        margin: 0 0 8px;
    }
    .empty-state__text {
        font-size: 14px;
        color: #9ca3af;
        margin: 0 0 1.5rem;
    }

    /* ── Pagination ──────────────────────────────────────── */
    .pagination-wrapper {
        display: flex;
        justify-content: center;
        margin-top: 1rem;
    }
    .pagination-wrapper nav {
        display: flex;
        gap: 4px;
    }
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
    .pagination-wrapper .page-link:hover {
        background: #f3f4f6;
        color: #374151;
    }
    .pagination-wrapper .active .page-link {
        background: #185FA5;
        border-color: #185FA5;
        color: #fff;
    }

    /* ── Responsive ──────────────────────────────────────── */
    @media (max-width: 768px) {
        .products-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }
        .filter-bar__form {
            flex-direction: column;
            align-items: stretch;
        }
        .filter-bar__filters {
            flex-direction: column;
        }
        .filter-select {
            width: 100%;
        }
        .filter-bar__actions {
            justify-content: flex-end;
        }
        .product-details-grid {
            grid-template-columns: 1fr;
        }
        .gallery-scroll-btn {
            display: none;
        }
    }

    @media (max-width: 480px) {
        .products-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@push('script')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.image-gallery-wrapper').forEach(wrapper => {
            const gallery = wrapper.querySelector('.image-gallery');
            const leftBtn = wrapper.querySelector('.gallery-scroll-btn--left');
            const rightBtn = wrapper.querySelector('.gallery-scroll-btn--right');

            const modal = wrapper.closest('.modal');
            const mainImage = modal.querySelector('.modal-product-image img');

            // Scroll buttons
            leftBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                gallery.scrollBy({ left: -120, behavior: 'smooth' });
            });

            rightBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                gallery.scrollBy({ left: 120, behavior: 'smooth' });
            });

            // Thumbnail click
            wrapper.querySelectorAll('.gallery-item').forEach(item => {
                item.addEventListener('click', (e) => {
                    e.stopPropagation();

                    const img = item.querySelector('img');

                    // Change main image
                    mainImage.src = img.dataset.full;

                    // Active state
                    wrapper.querySelectorAll('.gallery-item').forEach(i => i.classList.remove('active'));
                    item.classList.add('active');
                });
            });
        });
    });
</script>
@endpush
