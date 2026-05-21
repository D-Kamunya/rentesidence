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
                                $pageTitle = 'Product View';
                            @endphp
                            {{-- Back Nav --}}
                            <div class="detail-back-nav mb-4">
                                <a href="{{ route('tenant.product.index') }}" class="back-link">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                                        <path d="M19 12H5M12 5l-7 7 7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    {{ __('Back to Marketplace') }}
                                </a>
                            </div>

                            {{-- Product Detail Layout --}}
                            @php
                                $images = json_decode($product->images, true);
                                $firstImage = (is_array($images) && count($images) > 0) ? $images[0] : null;
                            @endphp

                            <div class="detail-layout">

                                {{-- LEFT: Images --}}
                                <div class="detail-media">
                                    <div class="detail-main-image">
                                        @if($firstImage)
                                            <img id="mainImage"
                                                 src="{{ asset('storage/' . $firstImage) }}"
                                                 alt="{{ $product->name }}">
                                        @else
                                            <div class="detail-image-placeholder">
                                                <svg width="56" height="56" viewBox="0 0 24 24" fill="none">
                                                    <rect x="3" y="3" width="18" height="18" rx="2" stroke="currentColor" stroke-width="1.5"/>
                                                    <circle cx="8.5" cy="8.5" r="1.5" fill="currentColor"/>
                                                    <path d="M21 15l-5-5L8 21h13v-6z" fill="currentColor" opacity="0.5"/>
                                                </svg>
                                                <p>No image available</p>
                                            </div>
                                        @endif
                                    </div>

                                    {{-- Thumbnails --}}
                                    @if(is_array($images) && count($images) > 1)
                                        <div class="detail-thumbnails">
                                            @foreach($images as $index => $image)
                                                <div class="detail-thumb {{ $index === 0 ? 'active' : '' }}"
                                                     data-full="{{ asset('storage/' . $image) }}">
                                                    <img src="{{ asset('storage/' . $image) }}"
                                                         alt="{{ $product->name }}">
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>

                                {{-- RIGHT: Info --}}
                                {{-- .product-card needed so JS closest('.product-card') works from the add-to-cart button --}}
                                <div class="detail-info product-card" style="border:none; box-shadow:none; border-radius:0;">

                                    {{-- Badges --}}
                                    <div class="detail-badges mb-3">
                                        <span class="badge badge--category">{{ $product->category }}</span>
                                        <span class="badge badge--{{ $product->type }}">{{ ucfirst($product->type) }}</span>
                                    </div>

                                    {{-- .product-title: JS reads textContent --}}
                                    <h1 class="detail-title product-title">{{ $product->name }}</h1>

                                    {{-- .product-price: JS reads textContent for price --}}
                                    <div class="detail-price product-price">
                                        Ksh {{ number_format($product->price, 2) }}
                                    </div>

                                    <div class="detail-divider"></div>

                                    <div class="detail-description">
                                        <h4 class="detail-section-label">{{ __('Description') }}</h4>
                                        <p>{{ $product->description ?: __('No description available.') }}</p>
                                    </div>

                                    <div class="detail-divider"></div>

                                    {{-- Hidden fields for JS --}}
                                    <span class="product-id d-none">{{ $product->id }}</span>
                                    {{-- img needed by JS: productElement.querySelector('img').src --}}
                                    @if($firstImage)
                                        <img src="{{ asset('storage/' . $firstImage) }}"
                                             alt="{{ $product->name }}"
                                             class="d-none product-image">
                                    @endif

                                    <div class="detail-cart-area">
                                        <button type="button" class="theme-btn-primary add-to-cart-button detail-cart-btn">
                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                                                <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4zM3 6h18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                                <line x1="16" y1="10" x2="8" y2="10" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                            </svg>
                                            {{ __('Add to Cart') }}
                                        </button>

                                        <button type="button" class="theme-btn-secondary buy-now-btn detail-cart-btn" id="buy-now-btn"
                                            data-url="{{ route('tenant.product.pay') }}">
                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                                                <path d="M5 12h14M12 5l7 7-7 7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                            {{ __('Buy Now') }}
                                        </button>
                                    </div>

                                </div>
                            </div>

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

@endsection

@push('script')
    <script src="{{ asset('assets/js/custom/tenant-product.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const mainImage  = document.getElementById('mainImage');
            const thumbs     = document.querySelectorAll('.detail-thumb');

            thumbs.forEach(function (thumb) {
                thumb.addEventListener('click', function () {
                    if (mainImage) mainImage.src = this.dataset.full;
                    thumbs.forEach(t => t.classList.remove('active'));
                    this.classList.add('active');
                });
            });
        });
        
        document.getElementById('buy-now-btn').addEventListener('click', function () {
            const payUrl = this.dataset.url;
            window._buyingNow = true;
            document.querySelector('.add-to-cart-button').click();

            window.location.href = payUrl;
        });
    </script>
@endpush

@push('style')
<style>
    /* ── Back Nav ────────────────────────────────────────── */
    .back-link {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 13px;
        color: #185FA5;
        text-decoration: none;
        font-weight: 500;
        transition: color .15s;
    }
    .back-link:hover { color: #0F4A84; }

    /* ── Detail Layout ───────────────────────────────────── */
    .detail-layout {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 2.5rem;
        align-items: start;
    }

    /* ── Media (Left) ────────────────────────────────────── */
    .detail-media { display: flex; flex-direction: column; gap: 14px; }

    .detail-main-image {
        aspect-ratio: 4 / 3;
        max-height: 380px;
        background: #f6f7f9;
        border-radius: 16px;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 0.5px solid #e5e7eb;
    }
    .detail-main-image img {
        width: auto;
        height: auto;
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
        transition: transform .3s ease;
    }
    .detail-main-image:hover img { transform: scale(1.04); }
    .detail-image-placeholder {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 10px;
        color: #d1d5db;
        font-size: 13px;
    }

    /* Thumbnails */
    .detail-thumbnails {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }
    .detail-thumb {
        width: 72px;
        height: 72px;
        border-radius: 10px;
        overflow: hidden;
        cursor: pointer;
        opacity: 0.6;
        border: 2px solid transparent;
        transition: all .2s ease;
        flex-shrink: 0;
    }
    .detail-thumb img { width: 100%; height: 100%; object-fit: cover; }
    .detail-thumb.active { opacity: 1; border-color: #185FA5; transform: scale(1.05); }
    .detail-thumb:hover  { opacity: 1; }

    /* ── Cart Area Layout ────────────────────────────────────── */
    .detail-cart-area {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
    }

    /* ── Secondary Button (Buy Now) ──────────────────────────── */
    .theme-btn-secondary {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: #fff;
        color: #185FA5;
        padding: 10px 18px;
        border-radius: 8px;
        font-weight: 500;
        font-size: 13px;
        text-decoration: none;
        transition: background .15s, transform .15s, box-shadow .15s;
        border: 1.5px solid #185FA5;
        cursor: pointer;
    }
    .theme-btn-secondary:hover {
        background: #f0f6ff;
        color: #0F4A84;
        border-color: #0F4A84;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(24, 95, 165, 0.15);
    }

    /* ── Info (Right) ────────────────────────────────────── */
    /* .product-card is added for JS compatibility; strip its visual styles here */
    .detail-info.product-card {
        border: none !important;
        box-shadow: none !important;
        border-radius: 0 !important;
        padding: 0 !important;
        background: transparent !important;
        transform: none !important;
        overflow: visible !important;
    }
    .detail-info { display: flex; flex-direction: column; }

    .detail-badges { display: flex; gap: 8px; flex-wrap: wrap; }

    .detail-title {
        font-size: 26px;
        font-weight: 600;
        color: #111827;
        margin: 0 0 12px;
        line-height: 1.3;
    }

    .detail-price {
        font-size: 28px;
        font-weight: 700;
        color: #0F6E56;
        margin-bottom: 20px;
    }

    .detail-divider {
        height: 0.5px;
        background: #e5e7eb;
        margin: 16px 0;
    }

    .detail-section-label {
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: #9ca3af;
        margin: 0 0 8px;
    }
    .detail-description p {
        font-size: 14px;
        color: #4b5563;
        line-height: 1.7;
        margin: 0;
    }

    .detail-cart-area { margin-top: 24px; }
    .detail-cart-btn  { font-size: 15px; padding: 12px 24px; }

    /* ── Shared: Badges ──────────────────────────────────── */
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

    /* ── Shared: Primary Button ──────────────────────────── */
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
        .detail-layout { grid-template-columns: 1fr; gap: 1.5rem; }
        .detail-title  { font-size: 22px; }
        .detail-price  { font-size: 24px; }
    }
</style>
@endpush