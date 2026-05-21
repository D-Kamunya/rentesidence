@extends('tenant.layouts.app')

@section('content')

    {{-- M-Pesa STK Preloader --}}
    <div id="mpesa-preloader" style="display:none;">
        <div id="mpesa-preloaderInner">
            <img src="{{ asset('assets/images/gateway-icon/mpesa.jpg') }}" alt="M-PESA">
            <div>
                <p>{{ __('Please follow the instructions and do not refresh or leave this page.') }}</p>
                <p>{{ __('This may take up to') }} <span id="mpesa-timer">2:00</span> {{ __('minute(s).') }}</p>
                <p>{{ __('You will receive a prompt on your mobile number to enter your PIN to authorize payment.') }}</p>
                <p>{{ __('Please ensure your phone is on and unlocked. Thank you.') }}</p>
            </div>
            <img src="{{ asset('assets/images/loading.svg') }}" alt="Loading">
        </div>
    </div>

    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="page-content-wrapper bg-white p-30 radius-20">
                    <div class="container">

                        {{-- Page Header --}}
                        <div class="dash-header mb-4">
                            <div>
                                <h2 class="dash-title">{{ __('Checkout') }}</h2>
                                <p class="dash-subtitle">{{ __('Review your order and complete payment') }}</p>
                            </div>
                            <nav aria-label="breadcrumb">
                                <ol class="mp-breadcrumb">
                                    <li><a href="{{ route('tenant.dashboard') }}">{{ __('Dashboard') }}</a></li>
                                    <li><a href="{{ route('tenant.product.index') }}">{{ __('Marketplace') }}</a></li>
                                    <li aria-current="page">{{ __('Checkout') }}</li>
                                </ol>
                            </nav>
                        </div>

                        {{-- Checkout Steps --}}
                        <div class="checkout-steps mb-4">
                            <div class="checkout-step checkout-step--done">
                                <div class="checkout-step__dot">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none">
                                        <path d="M20 6L9 17l-5-5" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </div>
                                <span>{{ __('Cart') }}</span>
                            </div>
                            <div class="checkout-step__line checkout-step__line--done"></div>
                            <div class="checkout-step checkout-step--active">
                                <div class="checkout-step__dot">2</div>
                                <span>{{ __('Review') }}</span>
                            </div>
                            <div class="checkout-step__line"></div>
                            <div class="checkout-step checkout-step--pending">
                                <div class="checkout-step__dot">3</div>
                                <span>{{ __('Payment') }}</span>
                            </div>
                        </div>

                        <div class="checkout-layout">

                            {{-- LEFT: Order Summary --}}
                            <div class="checkout-panel checkout-panel--cart">
                                <div class="panel-header">
                                    <div class="panel-header__icon panel-header__icon--blue">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                                            <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4zM3 6h18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                            <line x1="16" y1="10" x2="8" y2="10" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                        </svg>
                                    </div>
                                    <h4 class="panel-title">{{ __('Order Summary') }}</h4>
                                </div>

                                <div id="cartItems" class="cart-items-container">
                                    {{-- Populated by JS --}}
                                </div>

                                <div class="cart-total-row">
                                    <span class="cart-total-label">{{ __('Total Amount') }}</span>
                                    <span class="cart-total-value">KSh <span id="totalAmount">0.00</span></span>
                                </div>

                                <a href="{{ route('tenant.product.index') }}" class="back-to-shop-link">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none">
                                        <path d="M19 12H5M12 5l-7 7 7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    {{ __('Continue Shopping') }}
                                </a>
                            </div>

                            {{-- RIGHT: Payment --}}
                            <div class="checkout-panel checkout-panel--payment">
                                <div class="panel-header">
                                    <div class="panel-header__icon panel-header__icon--green">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                                            <rect x="1" y="4" width="22" height="16" rx="2" stroke="currentColor" stroke-width="1.8"/>
                                            <line x1="1" y1="10" x2="23" y2="10" stroke="currentColor" stroke-width="1.8"/>
                                        </svg>
                                    </div>
                                    <h4 class="panel-title">{{ __('Payment') }}</h4>
                                </div>

                                {{-- Mpesa branding block --}}
                                <div class="mpesa-brand-block">
                                    <img src="{{ asset('assets/images/gateway-icon/mpesa.jpg') }}"
                                         alt="M-Pesa" class="mpesa-brand-block__logo">
                                    <div>
                                        <p class="mpesa-brand-block__title">{{ __('Pay via M-Pesa') }}</p>
                                        <p class="mpesa-brand-block__sub">{{ __('You will receive an STK push on your registered Safaricom number to complete payment.') }}</p>
                                    </div>
                                </div>

                                {{-- What happens note --}}
                                <div class="mpesa-info-note mb-4">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" style="flex-shrink:0">
                                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.8"/>
                                        <line x1="12" y1="8" x2="12" y2="12" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                        <line x1="12" y1="16" x2="12.01" y2="16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                    </svg>
                                    <span>{{ __('Ensure your Safaricom line is active and your phone is unlocked before proceeding. The STK push will arrive within seconds.') }}</span>
                                </div>

                                {{-- Hidden form — submitted by JS --}}
                                <form id="pay-products-order-form"
                                      action="{{ route('payment.products.checkout') }}"
                                      method="POST">
                                    @csrf
                                    <input type="hidden" id="cartTotal" name="cartTotal">
                                    {{-- gateway and currency are resolved server-side now --}}
                                </form>

                                {{-- Pay button --}}
                                <button type="button" class="checkout-btn" id="checkoutBtn">
                                    <img src="{{ asset('assets/images/gateway-icon/mpesa.jpg') }}"
                                         alt="" style="width:22px;height:22px;border-radius:4px;object-fit:cover;">
                                    {{ __('Pay') }}
                                    <span id="checkoutAmount" class="checkout-btn__amount"></span>
                                    {{ __('via M-Pesa') }}
                                </button>

                            </div>

                        </div>{{-- /.checkout-layout --}}
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('style')
<style>
/* ── Page header & breadcrumb ────────────────────────────────── */
.dash-header   { display:flex; align-items:flex-start; justify-content:space-between; flex-wrap:wrap; gap:1rem; }
.dash-title    { font-size:22px; font-weight:500; color:#111827; margin:0 0 4px; }
.dash-subtitle { font-size:14px; color:#6b7280; margin:0; }
.mp-breadcrumb { display:flex; align-items:center; gap:6px; list-style:none; padding:0; margin:0; font-size:13px; color:#9ca3af; }
.mp-breadcrumb li:not(:last-child)::after { content:'/'; margin-left:6px; color:#d1d5db; }
.mp-breadcrumb a { color:#185FA5; text-decoration:none; }
.mp-breadcrumb a:hover { text-decoration:underline; }

/* ── Checkout steps ──────────────────────────────────────────── */
.checkout-steps { display:flex; align-items:center; }
.checkout-step  { display:flex; align-items:center; gap:8px; font-size:13px; font-weight:500; color:#9ca3af; }
.checkout-step__dot { width:28px; height:28px; border-radius:50%; background:#f3f4f6; border:1.5px solid #e5e7eb; display:flex; align-items:center; justify-content:center; font-size:12px; font-weight:600; flex-shrink:0; color:#9ca3af; transition:all .2s; }
.checkout-step--done .checkout-step__dot   { background:#E1F5EE; border-color:#1D9E75; color:#1D9E75; }
.checkout-step--active .checkout-step__dot { background:#185FA5; border-color:#185FA5; color:#fff; }
.checkout-step--active { color:#185FA5; }
.checkout-step--done   { color:#1D9E75; }
.checkout-step__line { flex:1; height:1.5px; background:#e5e7eb; margin:0 10px; min-width:40px; }
.checkout-step__line--done { background:#1D9E75; }

/* ── Layout ──────────────────────────────────────────────────── */
.checkout-layout { display:grid; grid-template-columns:1fr 1.2fr; gap:1.5rem; align-items:start; }

/* ── Panels ──────────────────────────────────────────────────── */
.checkout-panel { background:#fff; border:0.5px solid #e5e7eb; border-radius:14px; padding:1.5rem; position:relative; overflow:hidden; }
.checkout-panel--cart::before    { content:''; position:absolute; top:0; left:0; right:0; height:3px; background:linear-gradient(90deg,#185FA5,#2E86DE); border-radius:14px 14px 0 0; }
.checkout-panel--payment::before { content:''; position:absolute; top:0; left:0; right:0; height:3px; background:linear-gradient(90deg,#1D9E75,#27C494); border-radius:14px 14px 0 0; }
.panel-header { display:flex; align-items:center; gap:10px; margin-bottom:1.25rem; padding-bottom:1rem; border-bottom:0.5px solid #f3f4f6; }
.panel-header__icon { width:36px; height:36px; border-radius:10px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.panel-header__icon--blue  { background:#E6F1FB; color:#185FA5; }
.panel-header__icon--green { background:#E1F5EE; color:#1D9E75; }
.panel-title { font-size:16px; font-weight:500; color:#111827; margin:0; }

/* ── Cart items ──────────────────────────────────────────────── */
.cart-items-container { display:flex; flex-direction:column; gap:0; min-height:60px; margin-bottom:1rem; }
.cart-item-card { display:grid; grid-template-columns:1fr auto auto; align-items:center; gap:12px; padding:12px 4px; border-bottom:0.5px solid #f3f4f6; transition:background .15s; }
.cart-item-card:last-child { border-bottom:none; }
.cart-item-card:hover { background:#fafafa; border-radius:8px; }
.cart-item-details { display:flex; align-items:center; gap:12px; min-width:0; }
.cart-item-image  { width:46px; height:46px; object-fit:cover; border-radius:8px; border:0.5px solid #e5e7eb; flex-shrink:0; }
.cart-item-name   { font-size:13px; font-weight:500; color:#111827; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.cart-item-quantity { display:flex; align-items:center; gap:6px; background:#f3f4f6; border-radius:8px; padding:4px 6px; flex-shrink:0; }
.cart-item-quantity span { font-size:13px; font-weight:600; color:#111827; min-width:18px; text-align:center; }
.cart-item-actions { display:flex; flex-direction:column; align-items:flex-end; gap:4px; flex-shrink:0; }
.cart-item-actions > div { font-size:14px; font-weight:600; color:#0F6E56; white-space:nowrap; }
.cart-item-button { display:inline-flex; align-items:center; justify-content:center; border:none; cursor:pointer; font-weight:600; transition:background .15s,color .15s,transform .1s; line-height:1; }
.cart-item-button:active { transform:scale(0.93); }
.cart-item-button.btn-secondary { width:24px; height:24px; border-radius:6px; background:#fff; color:#374151; font-size:16px; box-shadow:0 1px 3px rgba(0,0,0,.08); }
.cart-item-button.btn-secondary:hover { background:#185FA5; color:#fff; }
.cart-item-button.btn-danger { padding:3px 8px; border-radius:6px; background:transparent; color:#9ca3af; font-size:11px; font-weight:500; }
.cart-item-button.btn-danger:hover { background:#FDF4F1; color:#993C1D; }

/* ── Cart total ──────────────────────────────────────────────── */
.cart-total-row { display:flex; align-items:center; justify-content:space-between; padding:12px 14px; background:#f0f7ff; border-radius:10px; border:0.5px solid #d0e8fb; margin-bottom:1rem; }
.cart-total-label { font-size:13px; font-weight:500; color:#374151; }
.cart-total-value { font-size:18px; font-weight:700; color:#185FA5; }
.back-to-shop-link { display:inline-flex; align-items:center; gap:6px; font-size:12px; color:#9ca3af; text-decoration:none; transition:color .15s; margin-top:4px; }
.back-to-shop-link:hover { color:#185FA5; }

/* ── Mpesa brand block ───────────────────────────────────────── */
.mpesa-brand-block { display:flex; align-items:center; gap:14px; background:#fafafa; border:0.5px solid #e5e7eb; border-radius:12px; padding:14px 16px; margin-bottom:1rem; }
.mpesa-brand-block__logo { width:52px; height:52px; object-fit:cover; border-radius:10px; flex-shrink:0; }
.mpesa-brand-block__title { font-size:15px; font-weight:600; color:#111827; margin:0 0 3px; }
.mpesa-brand-block__sub   { font-size:12px; color:#6b7280; margin:0; line-height:1.5; }

/* ── Mpesa info note ─────────────────────────────────────────── */
.mpesa-info-note { display:flex; align-items:flex-start; gap:8px; background:#FFFBEB; border:0.5px solid #FDE68A; border-radius:8px; padding:10px 12px; font-size:12px; color:#92400E; }
.mb-4 { margin-bottom:1.5rem; }

/* ── Pay button ──────────────────────────────────────────────── */
.checkout-btn { display:flex; align-items:center; justify-content:center; gap:10px; width:100%; padding:13px 20px; background:#185FA5; color:#fff; font-size:15px; font-weight:600; border:none; border-radius:10px; cursor:pointer; transition:background .15s,transform .15s,box-shadow .15s; }
.checkout-btn:hover { background:#0F4A84; transform:translateY(-1px); box-shadow:0 6px 16px rgba(24,95,165,.3); }
.checkout-btn__amount { font-size:13px; opacity:.85; font-weight:500; }

/* ── M-Pesa preloader ────────────────────────────────────────── */
#mpesa-preloader { position:fixed; inset:0; background:rgba(0,0,0,.55); z-index:9999; display:flex; align-items:center; justify-content:center; }
#mpesa-preloaderInner { background:#fff; border-radius:16px; padding:2rem; max-width:420px; width:90%; display:flex; flex-direction:column; align-items:center; gap:16px; text-align:center; box-shadow:0 20px 40px rgba(0,0,0,.2); }
#mpesa-preloaderInner img:first-child { width:80px; height:80px; object-fit:contain; border-radius:12px; }
#mpesa-preloaderInner p { font-size:13px; color:#374151; margin:0; line-height:1.6; }
#mpesa-timer { font-weight:600; color:#185FA5; }
#mpesa-preloaderInner img:last-child { width:36px; }

/* ── Responsive ──────────────────────────────────────────────── */
@media (max-width: 900px) {
    .checkout-layout { grid-template-columns:1fr; }
    .checkout-steps span { display:none; }
}
</style>
@endpush

@push('script')
<script>
(function () {
    'use strict';

    // ── Cart state ─────────────────────────────────────────────
    let cartItems = JSON.parse(localStorage.getItem('cartItems')) || [];

    const cartContainer      = document.getElementById('cartItems');
    const totalAmountEl      = document.getElementById('totalAmount');
    const checkoutAmountEl   = document.getElementById('checkoutAmount');
    const cartTotalInput     = document.getElementById('cartTotal');

    // ── Group duplicate items ──────────────────────────────────
    function groupCartItems(items) {
        const grouped = {};
        items.forEach(item => {
            if (!item.quantity) item.quantity = 1;
            if (grouped[item.name]) {
                grouped[item.name].quantity += item.quantity;
            } else {
                grouped[item.name] = { ...item };
            }
        });
        return Object.values(grouped);
    }

    // ── Render cart ────────────────────────────────────────────
    function renderCart() {
        cartItems = groupCartItems(cartItems);
        cartContainer.innerHTML = '';
        let total = 0;

        cartItems.forEach((item, index) => {
            const price     = parseFloat(item.price);
            const itemTotal = price * item.quantity;
            total += itemTotal;

            const card = document.createElement('div');
            card.className = 'cart-item-card';
            card.innerHTML = `
                <div class="cart-item-details">
                    <img class="cart-item-image" src="${item.image}" alt="${item.name}">
                    <div class="cart-item-info">
                        <div class="cart-item-name">${item.name}</div>
                    </div>
                </div>
                <div class="cart-item-quantity">
                    <button class="cart-item-button btn-secondary" onclick="decreaseQty(${index})">-</button>
                    <span>${item.quantity}</span>
                    <button class="cart-item-button btn-secondary" onclick="increaseQty(${index})">+</button>
                </div>
                <div class="cart-item-actions">
                    <div>KSh ${itemTotal.toFixed(2)}</div>
                    <button class="cart-item-button btn-danger" onclick="removeItem(${index})">Remove</button>
                </div>`;
            cartContainer.appendChild(card);
        });

        totalAmountEl.textContent    = total.toFixed(2);
        checkoutAmountEl.textContent = 'KSh ' + total.toFixed(2);
        cartTotalInput.value         = total.toFixed(2);

        localStorage.setItem('cartItems', JSON.stringify(cartItems));
    }

    window.increaseQty = function (index) {
        cartItems[index].quantity++;
        localStorage.setItem('cartItems', JSON.stringify(cartItems));
        renderCart();
    };

    window.decreaseQty = function (index) {
        if (cartItems[index].quantity > 1) {
            cartItems[index].quantity--;
            localStorage.setItem('cartItems', JSON.stringify(cartItems));
            renderCart();
        }
    };

    window.removeItem = function (index) {
        cartItems.splice(index, 1);
        localStorage.setItem('cartItems', JSON.stringify(cartItems));
        renderCart();
    };

    renderCart();

    // ── Checkout button ────────────────────────────────────────
    let timerInterval;

    function showPreloader() {
        let countdown = 120;
        const timerEl = document.getElementById('mpesa-timer');
        document.getElementById('mpesa-preloader').style.display = 'flex';
        timerInterval = setInterval(() => {
            const m = Math.floor(countdown / 60);
            const s = countdown % 60;
            timerEl.textContent = `${m}:${s < 10 ? '0' + s : s}`;
            if (countdown <= 0) clearInterval(timerInterval);
            countdown--;
        }, 1000);
    }

    function hidePreloader() {
        clearInterval(timerInterval);
        document.getElementById('mpesa-preloader').style.display = 'none';
    }

    document.getElementById('checkoutBtn').addEventListener('click', function () {
        const total = parseFloat(cartTotalInput.value);

        if (!cartItems.length) {
            toastr.error('{{ __("Your cart is empty.") }}');
            return;
        }

        if (!total || total <= 0) {
            toastr.error('{{ __("Invalid cart total.") }}');
            return;
        }

        showPreloader();

        const form     = document.getElementById('pay-products-order-form');
        const formData = new FormData(form);

        // Append cart items
        cartItems.forEach((item, index) => {
            formData.append(`products[${index}][id]`,       item.id);
            formData.append(`products[${index}][quantity]`, item.quantity);
        });

        fetch(form.action, { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    localStorage.removeItem('cartItems');

                    // Subscribe to Pusher for STK callback
                    const pusher  = new Pusher(window.Laravel.pusher_key, {
                        cluster: window.Laravel.pusher_cluster,
                    });
                    const channel = pusher.subscribe('transaction.' + data.transaction_id);

                    // Timeout fallback after 2 minutes
                    const redirectTimeout = setTimeout(() => {
                        window.location.href = data.redirect_url;
                    }, 120000);

                    channel.bind('MpesaTransactionProcessed', function () {
                        clearTimeout(redirectTimeout);
                        localStorage.removeItem('cartItems');
                        window.location.href = data.redirect_url + '&callback=true&stk_success=true';
                    });

                    channel.bind('MpesaTransactionDeclined', function () {
                        clearTimeout(redirectTimeout);
                        localStorage.removeItem('cartItems');
                        window.location.href = data.redirect_url + '&callback=true&stk_success=false';
                    });

                } else {
                    hidePreloader();
                    toastr.error(data.error || '{{ __("Payment failed. Please try again.") }}');
                }
            })
            .catch(() => {
                hidePreloader();
                toastr.error('{{ __("Something went wrong. Please try again.") }}');
            });
    });

})();
</script>
@endpush