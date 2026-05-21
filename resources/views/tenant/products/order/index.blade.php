@extends('tenant.layouts.app')

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="page-content-wrapper bg-white p-30 radius-20">
                <div class="container">

                    {{-- Page Header --}}
                    <div class="dash-header mb-4">
                        <div>
                            <h2 class="dash-title">{{ $pageTitle }}</h2>
                            <nav aria-label="breadcrumb">
                                <ol class="inv-breadcrumb">
                                    <li>
                                        <a href="{{ route('tenant.dashboard') }}">{{ __('Dashboard') }}</a>
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

                    {{-- Summary Strip --}}
                    <div class="inv-strip mb-4">
                        <div class="inv-strip__item">
                            <span class="inv-strip__dot inv-strip__dot--blue"></span>
                            <div>
                                <p class="inv-strip__label">{{ __('Total') }}</p>
                                <p class="inv-strip__val inv-strip__val--blue">{{ $totalProductOrders }}</p>
                            </div>
                        </div>
                        <div class="inv-strip__divider"></div>
                        <div class="inv-strip__item">
                            <span class="inv-strip__dot inv-strip__dot--green"></span>
                            <div>
                                <p class="inv-strip__label">{{ __('Completed') }}</p>
                                <p class="inv-strip__val inv-strip__val--green">{{ $totalCompleteProductOrders }}</p>
                            </div>
                        </div>
                        <div class="inv-strip__divider"></div>
                        <div class="inv-strip__item">
                            <span class="inv-strip__dot inv-strip__dot--amber"></span>
                            <div>
                                <p class="inv-strip__label">{{ __('Pending') }}</p>
                                <p class="inv-strip__val inv-strip__val--amber">{{ $totalPendingProductOrders }}</p>
                            </div>
                        </div>
                        <div class="inv-strip__divider"></div>
                        <div class="inv-strip__item">
                            <span class="inv-strip__dot inv-strip__dot--coral"></span>
                            <div>
                                <p class="inv-strip__label">{{ __('Cancelled') }}</p>
                                <p class="inv-strip__val inv-strip__val--coral">{{ $totalCancelledProductOrders }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- Table Card --}}
                    <div class="dash-card">
                        <div class="dash-card__head d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <div class="d-flex align-items-center gap-2">
                                <span style="font-weight:500;font-size:14px;">{{ __('All Orders') }}</span>
                                <span class="inv-count-pill">{{ $totalProductOrders }} {{ __('total') }}</span>
                            </div>
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <div class="inv-filter-tabs">
                                    @foreach ([
                                        'all'              => __('All'),
                                        'order_completed'  => __('Completed'),
                                        'order_pending'    => __('Pending'),
                                        'order_cancelled'  => __('Cancelled'),
                                    ] as $value => $label)
                                        <a href="{{ request()->fullUrlWithQuery(['status' => $value, 'search' => $search, 'page' => 1]) }}"
                                        class="inv-filter-tab {{ $activeStatus === $value ? 'inv-filter-tab--active' : '' }}">
                                            @if ($value !== 'all')
                                                <span class="inv-filter-tab__dot inv-filter-tab__dot--{{ str_contains($value,'cancelled') ? 'cancelled' : (str_contains($value,'paid') ? 'paid' : 'unpaid') }}"></span>
                                            @endif
                                            {{ $label }}
                                        </a>
                                    @endforeach
                                </div>
                                <form method="GET" action="" class="inv-search-wrap" id="orderSearchForm">
                                    <input type="hidden" name="status" value="{{ $activeStatus }}">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none">
                                        <circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="1.8"/>
                                        <path d="M16.5 16.5l4 4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                    </svg>
                                    <input type="text" name="search" id="orderSearch"
                                           value="{{ $search }}" placeholder="{{ __('Search orders…') }}" autocomplete="off">
                                    @if ($search)
                                        <a href="{{ request()->fullUrlWithQuery(['search' => '', 'page' => 1]) }}"
                                           class="inv-search-clear" title="{{ __('Clear search') }}">✕</a>
                                    @endif
                                </form>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table id="allOrderDataTable" class="table inv-table dt-responsive align-middle mb-0">
                                <thead>
                                    <tr class="inv-table__head">
                                        <th class="inv-th" style="width:44px;">#</th>
                                        <th class="inv-th all">{{ __('Tracking No.') }}</th>
                                        <th class="inv-th all">{{ __('Product(s)') }}</th>
                                        <th class="inv-th desktop">{{ __('Qty') }}</th>
                                        <th class="inv-th desktop">{{ __('Order Date') }}</th>
                                        <th class="inv-th desktop">{{ __('Amount') }}</th>
                                        <th class="inv-th all">{{ __('Payment') }}</th>
                                        <th class="inv-th all">{{ __('Order Status') }}</th>
                                        <th class="inv-th desktop" style="text-align:right; width:120px; min-width:120px;">{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($productOrders as $order)
                                        @php
                                            $isPaid        = $order->payment_status === PRODUCT_ORDER_STATUS_PAID;
                                            $isCancelled   = $order->payment_status === PRODUCT_ORDER_STATUS_CANCELLED;
                                            $isOrderCancelled = $order->order_status === ORDER_STATUS_CANCELLED;
                                            $isCompleted   = $order->order_status   === ORDER_STATUS_COMPLETED;
                                            $isRefund      = $order->payment_status   === PRODUCT_ORDER_STATUS_REFUND_PENDING; 
                                            $statusClass   = $isCompleted ? 'completed' : ($isCancelled ? 'cancelled' : 'pending');

                                           
                                            $allItems    = $order->orderItems;
                                            $firstItem   = $allItems->first();
                                            $firstProduct = $firstItem?->product;
                                            $images      = $firstProduct?->images ?? null;
                                            $images      = is_string($images) ? json_decode($images, true) : $images;
                                            $firstImage  = (is_array($images) && count($images) > 0) ? $images[0] : null;
                                            $imageUrl    = $firstImage ? asset('storage/' . ltrim($firstImage, '/')) : '';
                                            $productNames = $allItems->map(fn($i) => $i->product?->name)->filter()->join(', ');
                                            $totalQty    = $allItems->sum('quantity');
                                            $gateway = $order->gateway;
                                        @endphp

                                        <tr class="inv-table__row po-row {{ $isCompleted ? 'po-row--completed' : '' }}"
                                            data-status="{{ $statusClass }}">

                                            <td class="inv-td" style="color:#9ca3af;font-size:11px;">{{ $loop->iteration }}</td>

                                            <td class="inv-td">
                                                <span class="inv-no">{{ $order->order_id }}</span>
                                            </td>

                                            <td class="inv-td">
                                                <div class="d-flex align-items-center gap-3">
                                                    @if ($imageUrl)
                                                        <img src="{{ $imageUrl }}" alt="{{ $firstProduct?->name }}"
                                                             style="width:45px;height:45px;object-fit:cover;border-radius:8px;border:1px solid #e5e7eb;flex-shrink:0;">
                                                    @endif
                                                    <span style="font-size:13px;font-weight:500;color:#111827;">
                                                        {{ $productNames ?: '—' }}
                                                        @if ($allItems->count() > 1)
                                                            <span style="font-size:11px;color:#9ca3af;font-weight:400;">
                                                                ({{ $allItems->count() }} {{ __('items') }})
                                                            </span>
                                                        @endif
                                                    </span>
                                                </div>
                                            </td>

                                            <td class="inv-td desktop">
                                                <span class="inv-secondary-text">{{ $totalQty }}</span>
                                            </td>

                                            <td class="inv-td desktop">
                                                <span class="inv-secondary-text">{{ $order->created_at->format('d M Y') }}</span>
                                            </td>

                                            <td class="inv-td desktop">
                                                <span class="inv-amount {{ $isPaid ? 'inv-amount--paid' : ($isCancelled ? 'inv-amount--cancelled' : 'inv-amount--pending') }}">
                                                    {{ number_format($order->transaction_amount, 2) }}
                                                </span>
                                            </td>

                                            <!-- payment status -->
                                            <td class="inv-td all">
                                                @if ($isPaid)
                                                    <span class="inv-badge inv-badge--paid">
                                                        <svg width="9" height="9" viewBox="0 0 16 16" fill="none"><path d="M3 8l4 4 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                                        {{ __('Paid') }}
                                                    </span>
                                                @elseif ($isCancelled)
                                                    <span class="inv-badge inv-badge--cancelled">
                                                        <svg width="9" height="9" viewBox="0 0 16 16" fill="none"><path d="M4 4l8 8M12 4l-8 8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                                                        {{ __('Cancelled') }}
                                                    </span>
                                                @elseif ($isRefund)
                                                    <span class="inv-badge inv-badge--pending">
                                                        <svg width="9" height="9" viewBox="0 0 16 16" fill="none"><path d="M4 4l8 8M12 4l-8 8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                                                        {{ __('In Refund') }}
                                                    </span>
                                                @else
                                                    <span class="inv-badge inv-badge--pending">
                                                        <svg width="9" height="9" viewBox="0 0 16 16" fill="none"><circle cx="8" cy="8" r="5.5" stroke="currentColor" stroke-width="1.6"/><path d="M8 5v3.5l2 1.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
                                                        {{ __('Pending') }}
                                                    </span>
                                                @endif
                                            </td>

                                            <!-- order status -->
                                            <td class="inv-td all">
                                                @if ($isCompleted)
                                                    <span class="inv-badge inv-badge--order-completed">
                                                        <svg width="9" height="9" viewBox="0 0 16 16" fill="none"><path d="M3 8l4 4 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                                        {{ __('Completed') }}
                                                    </span>
                                                @elseif ($isOrderCancelled)
                                                    <span class="inv-badge inv-badge--cancelled">
                                                        <svg width="9" height="9" viewBox="0 0 16 16" fill="none"><path d="M4 4l8 8M12 4l-8 8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                                                        {{ __('Cancelled') }}
                                                    </span>
                                                @else
                                                    <span class="inv-badge inv-badge--order-pending">
                                                        <svg width="9" height="9" viewBox="0 0 16 16" fill="none"><circle cx="8" cy="8" r="5.5" stroke="currentColor" stroke-width="1.6"/><path d="M8 5v3.5l2 1.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
                                                        {{ __('Processing') }}
                                                    </span>
                                                @endif
                                            </td>

                                            <td class="inv-td desktop">
                                                <div class="inv-actions">
                                                    <button
                                                        type="button"
                                                        class="inv-btn inv-btn--ghost po-view-btn"
                                                        data-order-id="{{ $order->id }}"
                                                        data-order-no="{{ $order->order_id }}"
                                                        data-product="{{ $productNames ?: '—' }}"
                                                        data-qty="{{ $totalQty }}"
                                                        data-amount="{{ number_format($order->transaction_amount, 2) }}"
                                                        data-date="{{ $order->created_at->format('d M Y') }}"
                                                        data-payment-status="{{ $order->payment_status }}"
                                                        data-order-status="{{ $order->order_status }}"
                                                        data-image="{{ $imageUrl }}"
                                                        data-item-count="{{ $allItems->count() }}"
                                                        data-gateway="{{ $order->gateway?->title ?? '—' }}"
                                                        data-cancel-url="{{ $order->payment_status == PRODUCT_ORDER_STATUS_PAID && $order->order_status != ORDER_STATUS_COMPLETED && $order->order_status != ORDER_STATUS_CANCELLED ? route('tenant.product_order.cancel', $order->id) : '' }}"
                                                        data-receipt-url="{{ route('tenant.product.order.receipt', $order->id) }}"
                                                        title="{{ __('View Order') }}">
                                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none">
                                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" stroke="currentColor" stroke-width="1.8"/>
                                                            <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.8"/>
                                                        </svg>
                                                        {{ __('View') }}
                                                    </button>
                                                    @if ($order->payment_status === PRODUCT_ORDER_STATUS_PAID && $order->order_status !== ORDER_STATUS_COMPLETED && $order->order_status != ORDER_STATUS_CANCELLED)
                                                        <button type="button"
                                                                class="inv-btn inv-btn--cancel po-cancel-btn"
                                                                data-order-id="{{ $order->id }}"
                                                                data-cancel-url="{{ route('tenant.product_order.cancel', $order->id) }}"
                                                                title="{{ __('Cancel Order') }}">
                                                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none">
                                                                <path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
                                                            </svg>
                                                            {{ __('Cancel') }}
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>

                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="inv-empty">
                                                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" style="color:#d1d5db">
                                                    <rect x="3" y="3" width="18" height="18" rx="2" stroke="currentColor" stroke-width="1.5"/>
                                                    <path d="M9 9h6M9 12h6M9 15h4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                                </svg>
                                                <p style="margin:8px 0 4px;font-weight:500;color:#374151;font-size:14px;">{{ __('No orders yet') }}</p>
                                                <p style="font-size:13px;color:#9ca3af;">{{ __('Your product orders will appear here once they are placed.') }}</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>

                            @if ($productOrders->hasPages())
                                <div class="inv-pagination-wrap">
                                    {{ $productOrders->links('vendor.pagination.inv-pagination') }}
                                </div>
                            @endif
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

{{-- Order Detail Modal --}}
<div class="po-modal-backdrop" id="poModalBackdrop" aria-hidden="true">
    <div class="po-modal" role="dialog" aria-modal="true" aria-labelledby="poModalTitle">

        <div class="po-modal__header">
            <div>
                <p class="po-modal__eyebrow">{{ __('Order Details') }}</p>
                <h3 class="po-modal__title" id="poModalTitle">—</h3>
            </div>
            <button type="button" class="po-modal__close" id="poModalClose" aria-label="{{ __('Close') }}">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                    <path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
            </button>
        </div>

        <div class="po-modal__body">
            <div class="po-modal__status-row">
                <div class="po-modal__status-item">
                    <span class="po-modal__status-label">{{ __('Payment') }}</span>
                    <span class="po-modal__status-badge" id="poModalPaymentBadge"></span>
                </div>
                <div class="po-modal__status-item">
                    <span class="po-modal__status-label">{{ __('Order Status') }}</span>
                    <span class="po-modal__status-badge" id="poModalOrderBadge"></span>
                </div>
            </div>

            <div class="po-modal__product-preview mb-3">
                <div class="po-modal__image-container">
                    <img id="poModalProductImage" src="" alt="Product Image"
                         class="po-modal__product-image" onerror="this.style.display='none'">
                </div>
            </div>

            <div class="po-modal__grid">
                <div class="po-modal__field po-modal__field--full">
                    <span class="po-modal__label">{{ __('Product(s)') }}</span>
                    <span class="po-modal__value" id="poModalProduct">—</span>
                </div>
                <div class="po-modal__field">
                    <span class="po-modal__label">{{ __('Quantity') }}</span>
                    <span class="po-modal__value" id="poModalQty">—</span>
                </div>
                <div class="po-modal__field">
                    <span class="po-modal__label">{{ __('Order Date') }}</span>
                    <span class="po-modal__value" id="poModalDate">—</span>
                </div>
                <div class="po-modal__field">
                    <span class="po-modal__label">{{ __('Total Amount') }}</span>
                    <span class="po-modal__value po-modal__value--amount" id="poModalAmount">—</span>
                </div>
                <div class="po-modal__field">
                    <span class="po-modal__label">{{ __('Payment Method') }}</span>
                    <span class="po-modal__value" id="poModalGateway">—</span>
                </div>
                <div class="po-modal__field" id="poModalItemCountField" style="display:none;">
                    <span class="po-modal__label">{{ __('Line Items') }}</span>
                    <span class="po-modal__value" id="poModalItemCount">—</span>
                </div>
            </div>
            <div id="poModalCancelWrap" style="display:none; margin-top:1rem;">
                <button type="button" class="po-modal__cancel-btn" id="poModalCancelBtn">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none">
                        <path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
                    </svg>
                    {{ __('Cancel This Order') }}
                </button>
                <p class="po-modal__cancel-hint">{{ __('This action cannot be undone.') }}</p>
            </div>
        </div>

        <div class="po-modal__footer">
            <button type="button" class="inv-btn inv-btn--ghost" id="poModalCloseBtn">
                {{ __('Close') }}
            </button>
            <a href="#" id="poModalReceiptLink" class="inv-btn inv-btn--receipt" target="_blank">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none">
                    <polyline points="6 9 6 2 18 2 18 9" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    <rect x="6" y="14" width="12" height="8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                {{ __('View Receipt') }}
            </a>
        </div>

    </div>
</div>
<div class="confirm-backdrop" id="confirmBackdrop" aria-hidden="true">
    <div class="confirm-modal" role="dialog" aria-modal="true">
        <div class="confirm-modal__header">
            <div class="confirm-modal__icon confirm-modal__icon--red">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                    <path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
                </svg>
            </div>
            <p class="confirm-modal__title" id="confirmTitle">{{ __('Cancel This Order?') }}</p>
        </div>
        <div class="confirm-modal__body" id="confirmBody">
            {{ __('Are you sure you want to cancel this order? This cannot be undone.') }}
        </div>
        <div class="confirm-modal__footer">
            <button type="button" class="confirm-btn confirm-btn--ghost" id="confirmCancelBtn">
                {{ __('Go Back') }}
            </button>
            <button type="button" class="confirm-btn confirm-btn--red" id="confirmOkBtn">
                {{ __('Yes, Cancel Order') }}
            </button>
        </div>
    </div>
</div>
@endsection

@push('style')
    @include('common.layouts.datatable-style')
    <style>
        .dash-header { display:flex; align-items:flex-start; justify-content:space-between; flex-wrap:wrap; gap:1rem; }
        .dash-title  { font-size:22px; font-weight:500; color:#111827; margin:0 0 6px; }
        .inv-breadcrumb { list-style:none; display:flex; align-items:center; gap:6px; margin:0; padding:0; font-size:12px; color:#9ca3af; }
        .inv-breadcrumb a { color:#185FA5; text-decoration:none; font-weight:500; }
        .inv-breadcrumb a:hover { color:#0F4A84; }
        .inv-breadcrumb li { display:flex; align-items:center; gap:6px; }

        .inv-strip { display:flex; align-items:center; background:#fff; border:0.5px solid #e5e7eb; border-radius:12px; overflow:hidden; flex-wrap:wrap; }
        .inv-strip__item { display:flex; align-items:center; gap:10px; padding:.9rem 1.35rem; flex:1; min-width:100px; max-width:220px; }
        .inv-strip__divider { width:0.5px; align-self:stretch; background:#e5e7eb; flex-shrink:0; }
        .inv-strip__dot { width:8px; height:8px; border-radius:50%; flex-shrink:0; }
        .inv-strip__dot--blue  { background:#185FA5; }
        .inv-strip__dot--green { background:#1D9E75; }
        .inv-strip__dot--amber { background:#854F0B; }
        .inv-strip__dot--coral { background:#993C1D; }
        .inv-strip__label { font-size:10px; font-weight:500; text-transform:uppercase; letter-spacing:.07em; color:#9ca3af; margin:0 0 3px; }
        .inv-strip__val { font-size:18px; font-weight:500; margin:0; line-height:1; }
        .inv-strip__val--blue  { color:#185FA5; }
        .inv-strip__val--green { color:#0F6E56; }
        .inv-strip__val--amber { color:#854F0B; }
        .inv-strip__val--coral { color:#993C1D; }

        .dash-card { background:#fff; border:0.5px solid #e5e7eb; border-radius:12px; overflow:hidden; }
        .dash-card__head { padding:.75rem 1.1rem; border-bottom:0.5px solid #e5e7eb; background:#fafafa; }
        .inv-count-pill { display:inline-block; background:#f3f4f6; color:#6b7280; font-size:11px; font-weight:500; padding:3px 10px; border-radius:99px; }

        .inv-search-wrap { position:relative; display:flex; align-items:center; }
        .inv-search-wrap svg { position:absolute; left:8px; color:#9ca3af; pointer-events:none; }
        .inv-search-wrap input { border:0.5px solid #e5e7eb; border-radius:7px; padding:5px 10px 5px 28px; font-size:12px; color:#374151; background:#fff; outline:none; width:180px; transition:border-color .15s, box-shadow .15s; }
        .inv-search-wrap input:focus { border-color:#185FA5; box-shadow:0 0 0 3px rgba(24,95,165,.1); }
        .inv-search-wrap input::placeholder { color:#c4c4c4; }
        .inv-search-clear { position:absolute; right:8px; color:#9ca3af; font-size:11px; text-decoration:none; line-height:1; transition:color .15s; }
        .inv-search-clear:hover { color:#374151; }

        .inv-table { width:100%; border-collapse:collapse; }
        .inv-table__head { background:#fafafa; border-bottom:0.5px solid #e5e7eb; }
        .inv-th { padding:.65rem 1rem; font-size:10px; font-weight:500; color:#6b7280; text-transform:uppercase; letter-spacing:.07em; border:none; white-space:nowrap; }
        .inv-td { padding:.8rem 1rem; border:none; vertical-align:middle; }
        .inv-table__row { border-bottom:0.5px solid #f3f4f6; transition:background .12s; }
        .inv-table__row:last-child { border-bottom:none; }
        .inv-table__row:nth-child(even) { background:#fafafa; }
        .inv-table__row:hover { background:#f3f4f6; }

        .po-row--completed { opacity: 0.62; }
        .po-row--completed:hover { opacity: 1; }
        .po-row--completed .inv-no { background:#f3f4f6; color:#9ca3af; }

        .inv-no { display:inline-block; font-size:11px; font-weight:500; font-family:monospace; letter-spacing:.04em; background:#E6F1FB; color:#0C447C; padding:3px 9px; border-radius:6px; }
        .inv-secondary-text { font-size:12px; color:#6b7280; }

        .inv-amount { font-size:13px; font-weight:500; padding:3px 10px; border-radius:99px; white-space:nowrap; display:inline-block; }
        .inv-amount--paid      { background:#E1F5EE; color:#0F6E56; }
        .inv-amount--pending   { background:#FAEEDA; color:#854F0B; }
        .inv-amount--cancelled { background:#F3F4F6; color:#6b7280; }

        .inv-badge { display:inline-flex; align-items:center; gap:4px; font-size:11px; font-weight:500; padding:3px 9px; border-radius:99px; white-space:nowrap; }
        .inv-badge--paid      { background:#E1F5EE; color:#0F6E56; }
        .inv-badge--pending   { background:#FAEEDA; color:#854F0B; border:0.5px solid #F5D9A8; }
        .inv-badge--cancelled { background:#FDECEC; color:#6b7280; border:0.5px solid #e5e7eb; }

        .inv-badge--order-completed { background:#E1F5EE; color:#0F6E56; }
        .inv-badge--order-pending   { background:#EEF2FF; color:#3730A3; border:0.5px solid #C7D2FE; }
        .inv-badge--order-cancelled { background:#FDECEC; color:#6b7280; border:0.5px solid #e5e7eb; }

        .inv-filter-tabs { display:flex; background:#f3f4f6; border-radius:8px; padding:3px; gap:2px; }
        .inv-filter-tab { display:inline-flex; align-items:center; gap:5px; background:transparent; border:none; font-size:12px; font-weight:500; color:#6b7280; padding:4px 12px; border-radius:6px; cursor:pointer; transition:background .15s, color .15s; white-space:nowrap; text-decoration:none; }
        .inv-filter-tab--active { background:#fff; color:#111827; box-shadow:0 1px 3px rgba(0,0,0,.08); }
        .inv-filter-tab__dot { width:6px; height:6px; border-radius:50%; flex-shrink:0; }
        .inv-filter-tab__dot--paid      { background:#1D9E75; }
        .inv-filter-tab__dot--unpaid    { background:#854F0B; }
        .inv-filter-tab__dot--cancelled { background:#9ca3af; }

        .inv-actions { display: flex; flex-direction: column; align-items: flex-end;gap: 5px;}
        .inv-btn { display:inline-flex; align-items:center; justify-content:center; gap:5px; font-size:12px; font-weight:500; padding:5px 12px; border-radius:7px; text-decoration:none; white-space:nowrap; transition:background .15s, transform .12s, box-shadow .12s; cursor:pointer; border:none; }
        .inv-btn--ghost { background:#f3f4f6; color:#374151; border:0.5px solid #e5e7eb; }
        .inv-btn--ghost:hover { background:#e5e7eb; color:#111827; text-decoration:none; }
        .inv-actions .inv-btn { width: 100%; justify-content: flex-start;}
        .inv-btn--receipt {background: #E6F1FB; color: #185FA5; border: 0.5px solid #B8D4F0; margin-left:15px; }
        .inv-btn--receipt:hover { background: #185FA5; color: #fff;border-color: #185FA5; text-decoration: none;}

        .inv-empty { text-align:center; padding:3rem 1rem; color:#9ca3af; }

        div.dataTables_wrapper { padding:0; }
        div.dataTables_wrapper div.dataTables_filter { display:none; }
        div.dataTables_wrapper div.dataTables_length { padding:.75rem 1.25rem; }
        div.dataTables_wrapper div.dataTables_length select { border:0.5px solid #e5e7eb; border-radius:7px; padding:5px 10px; font-size:13px; color:#374151; outline:none; background:#fff; }
        div.dataTables_wrapper div.dataTables_paginate { padding:.75rem 1.25rem; }
        div.dataTables_wrapper div.dataTables_paginate .paginate_button { border-radius:7px !important; border:0.5px solid transparent !important; font-size:13px !important; padding:4px 10px !important; color:#374151 !important; }
        div.dataTables_wrapper div.dataTables_paginate .paginate_button:hover { background:#f3f4f6 !important; color:#111827 !important; border-color:#e5e7eb !important; }
        div.dataTables_wrapper div.dataTables_paginate .paginate_button.current,
        div.dataTables_wrapper div.dataTables_paginate .paginate_button.current:hover { background:#185FA5 !important; border-color:#185FA5 !important; color:#fff !important; }
        div.dataTables_wrapper div.dataTables_info { padding:.75rem 1.25rem; font-size:12px; color:#9ca3af; }

        .inv-pagination-wrap { padding:.75rem 1.1rem; border-top:0.5px solid #e5e7eb; background:#fafafa; display:flex; justify-content:flex-end; }
        .inv-pagination-wrap nav { display:flex; align-items:center; gap:4px; }
        .inv-pagination-wrap span[aria-disabled],
        .inv-pagination-wrap a { display:inline-flex; align-items:center; justify-content:center; min-width:32px; height:32px; padding:0 8px; border-radius:7px; font-size:12px; font-weight:500; border:0.5px solid #e5e7eb; color:#374151; background:#fff; text-decoration:none; transition:background .12s, border-color .12s; }
        .inv-pagination-wrap a:hover { background:#f3f4f6; border-color:#d1d5db; color:#111827; }
        .inv-pagination-wrap span[aria-current="page"] > span { display:inline-flex; align-items:center; justify-content:center; min-width:32px; height:32px; padding:0 8px; border-radius:7px; font-size:12px; font-weight:600; background:#185FA5; border:0.5px solid #185FA5; color:#fff; }
        .inv-pagination-wrap span[aria-disabled] > span { color:#d1d5db; border-color:#f3f4f6; background:#fafafa; cursor:default; }

        .po-modal-backdrop { position:fixed; inset:0; background:rgba(17,24,39,.45); backdrop-filter:blur(2px); -webkit-backdrop-filter:blur(2px); z-index:1050; display:flex; align-items:center; justify-content:center; padding:1rem; opacity:0; pointer-events:none; transition:opacity .2s ease; }
        .po-modal-backdrop.is-open { opacity:1; pointer-events:all; }
        .po-modal { background:#fff; border-radius:14px; width:100%; max-width:500px; box-shadow:0 20px 60px rgba(17,24,39,.18),0 4px 16px rgba(17,24,39,.08); transform:translateY(12px) scale(.98); transition:transform .22s ease,opacity .22s ease; opacity:0; overflow:hidden; }
        .po-modal-backdrop.is-open .po-modal { transform:translateY(0) scale(1); opacity:1; }
        .po-modal__header { display:flex; align-items:flex-start; justify-content:space-between; padding:1.1rem 1.35rem 1rem; border-bottom:0.5px solid #e5e7eb; background:#fafafa; }
        .po-modal__eyebrow { font-size:10px; font-weight:500; text-transform:uppercase; letter-spacing:.07em; color: #9ca3af; margin:0 0 4px; }
        .po-modal__title { font-size:15px; font-weight:600; color:#111827; margin:0; font-family:monospace; letter-spacing:.04em; }
        .po-modal__close { background:#f3f4f6; border:none; border-radius:7px; width:30px; height:30px; display:flex; align-items:center; justify-content:center; cursor:pointer; color:#6b7280; flex-shrink:0; transition:background .15s,color .15s; }
        .po-modal__close:hover { background:#e5e7eb; color:#111827; }

        .po-modal__status-row { display:flex; gap:1rem; padding:.85rem 1.35rem; border-bottom:0.5px solid #f3f4f6; flex-wrap:wrap; }
        .po-modal__status-item { display:flex; flex-direction:column; gap:5px; }
        .po-modal__status-label { font-size:10px; font-weight:500; text-transform:uppercase; letter-spacing:.07em; color:#9ca3af; }

        .po-modal__body { padding:1.1rem 1.35rem; }
        .po-modal__grid { display:grid; grid-template-columns:1fr 1fr; gap:1rem .75rem; }
        .po-modal__field { display:flex; flex-direction:column; gap:4px; }
        .po-modal__field--full { grid-column:1 / -1; }
        .po-modal__label { font-size:10px; font-weight:500; text-transform:uppercase; letter-spacing:.07em; color:#9ca3af; }
        .po-modal__value { font-size:13px; font-weight:500; color:#111827; }
        .po-modal__value--amount { font-size:15px; font-weight:600; color:#185FA5; }
        .po-modal__footer { padding:.85rem 1.35rem; border-top:0.5px solid #e5e7eb; display:flex; justify-content:flex-end; background:#fafafa; }
        .po-modal__product-preview { text-align:center; }
        .po-modal__image-container { display:inline-block; padding:8px; background:#f8fafc; border-radius:12px; border:1px solid #e5e7eb; }
        .po-modal__product-image { width:90px; height:90px; object-fit:cover; border-radius:10px; box-shadow:0 2px 8px rgba(0,0,0,.06); border:1px solid #e5e7eb; }

        /* Cancel button (inline) */
        .inv-btn--cancel {
            background: #FDF4F1;
            color: #993C1D;
            border: 0.5px solid #F5C6B8;
        }
        .inv-btn--cancel:hover {
            background: #993C1D;
            color: #fff;
            border-color: #993C1D;
        }
        
        /* Cancel button (modal) */
        .po-modal__cancel-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            width: 100%;
            padding: 10px 16px;
            background: #FDF4F1;
            color: #993C1D;
            font-size: 13px;
            font-weight: 500;
            border: 0.5px solid #F5C6B8;
            border-radius: 9px;
            cursor: pointer;
            margin-bottom: 6px;
            transition: background .15s, color .15s;
        }
        .po-modal__cancel-btn:hover {
            background: #993C1D;
            color: #fff;
            border-color: #993C1D;
        }
        .po-modal__cancel-hint {
            font-size: 11px;
            color: #9ca3af;
            text-align: center;
            margin: 0;
        }
        
        @media (max-width: 768px) {
            .inv-strip__item { padding:.75rem 1rem; }
            .inv-strip__val  { font-size:16px; }
            .inv-search-wrap input { width:130px; }
            .po-modal__grid  { grid-template-columns:1fr; }
            .po-modal__field--full { grid-column:1; }
        }
        @media (max-width: 540px) {
            .dash-card__head { flex-wrap:wrap; gap:8px; }
            .inv-search-wrap { width:100%; }
            .inv-search-wrap input { width:100%; }
            .inv-filter-tabs { width:100%; }
        }
        /* ── Toastr colour fix ───────────────────────────────────────── */
        /* Placed here to load after common layout CSS */
        #toast-container > .toast-success {
        background-color: #1D9E75 !important;
        border-left: 4px solid #0F6E56 !important;
        color: #fff !important;
        }
        #toast-container > .toast-error {
            background-color: #dc2626 !important;
            border-left: 4px solid #991b1b !important;
            color: #fff !important;
        }
        #toast-container > div {
            box-shadow: 0 4px 12px rgba(0,0,0,.15) !important;
            border-radius: 8px !important;
            opacity: 1 !important;
        }
    
        /* ── Confirmation modal ──────────────────────────────────────── */
        .confirm-backdrop {
            position: fixed; inset: 0;
            background: rgba(17,24,39,.5);
            backdrop-filter: blur(2px);
            z-index: 2000;
            display: flex; align-items: center; justify-content: center;
            padding: 1rem;
            opacity: 0; pointer-events: none;
            transition: opacity .18s ease;
        }
        .confirm-backdrop.is-open { opacity: 1; pointer-events: all; }
        .confirm-modal {
            background: #fff; border-radius: 14px; width: 100%; max-width: 400px;
            box-shadow: 0 20px 60px rgba(17,24,39,.2);
            transform: translateY(10px) scale(.97);
            transition: transform .2s ease, opacity .2s ease;
            opacity: 0; overflow: hidden;
        }
        .confirm-backdrop.is-open .confirm-modal { transform: translateY(0) scale(1); opacity: 1; }
        .confirm-modal__header {
            padding: 1.1rem 1.35rem .9rem;
            border-bottom: 0.5px solid #e5e7eb;
            background: #fafafa;
        }
        .confirm-modal__icon {
            width: 36px; height: 36px; border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            margin-bottom: 10px;
        }
        .confirm-modal__icon--red   { background: #FDF4F1; color: #993C1D; }
        .confirm-modal__title { font-size: 15px; font-weight: 600; color: #111827; margin: 0 0 4px; }
        .confirm-modal__body { padding: 1rem 1.35rem; font-size: 13px; color: #6b7280; line-height: 1.55; }
        .confirm-modal__footer {
            padding: .85rem 1.35rem;
            border-top: 0.5px solid #e5e7eb;
            display: flex; justify-content: flex-end; gap: 8px;
            background: #fafafa;
        }
        .confirm-btn {
            display: inline-flex; align-items: center; gap: 6px;
            font-size: 13px; font-weight: 500; padding: 8px 16px;
            border-radius: 8px; cursor: pointer; border: none;
            transition: background .15s;
        }
        .confirm-btn--ghost { background: #f3f4f6; color: #374151; border: 0.5px solid #e5e7eb; }
        .confirm-btn--ghost:hover { background: #e5e7eb; }
        .confirm-btn--red   { background: #993C1D; color: #fff; }
        .confirm-btn--red:hover { background: #7a2e14; }
    </style>
@endpush

@push('script')
    @include('common.layouts.datatable-script')
    {{-- Toastr override — must load after common layout CSS --}}
    <style>
    #toast-container > .toast-success {
        background-color: #1D9E75 !important;
        border-left: 4px solid #0F6E56 !important;
        color: #fff !important;
    }
    #toast-container > .toast-error {
        background-color: #dc2626 !important;
        border-left: 4px solid #991b1b !important;
        color: #fff !important;
    }
    #toast-container > div {
        box-shadow: 0 4px 12px rgba(0,0,0,.15) !important;
        border-radius: 8px !important;
        opacity: 1 !important;
    }
    </style>
    <script>
    (function () {
        'use strict';
 
        localStorage.removeItem('cartItems');
 
        /* ── Search debounce ──────────────────────────────────── */
        const searchInput = document.getElementById('orderSearch');
        const searchForm  = document.getElementById('orderSearchForm');
        let debounceTimer;
        if (searchInput && searchForm) {
            searchInput.addEventListener('input', function () {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => searchForm.submit(), 400);
            });
        }
 
        /* ── Confirmation modal ───────────────────────────────── */
        const confirmBackdrop  = document.getElementById('confirmBackdrop');
        const confirmOkBtn     = document.getElementById('confirmOkBtn');
        const confirmCancelBtn = document.getElementById('confirmCancelBtn');
        let pendingCancelUrl = null;
 
        function openConfirm(url) {
            pendingCancelUrl = url;
            confirmBackdrop.setAttribute('aria-hidden', 'false');
            confirmBackdrop.classList.add('is-open');
        }
 
        function closeConfirm() {
            confirmBackdrop.classList.remove('is-open');
            confirmBackdrop.setAttribute('aria-hidden', 'true');
            pendingCancelUrl = null;
        }
 
        confirmCancelBtn?.addEventListener('click', closeConfirm);
        confirmBackdrop?.addEventListener('click', e => {
            if (e.target === confirmBackdrop) closeConfirm();
        });
 
        confirmOkBtn?.addEventListener('click', function () {
            if (!pendingCancelUrl) return;
            const url = pendingCancelUrl;
            closeConfirm();
            doCancel(url);
        });
 
        /* ── Cancel fetch ─────────────────────────────────────── */
        function doCancel(url) {
            fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === true || data.status === 'success' || data.success) {
                    if (typeof toastr !== 'undefined') {
                        toastr.success(data.message || '{{ __("Order cancelled successfully.") }}');
                    }
                    setTimeout(() => location.reload(), 1200);
                } else {
                    if (typeof toastr !== 'undefined') {
                        toastr.error(data.message || '{{ __("Could not cancel order.") }}');
                    }
                }
            })
            .catch(() => {
                if (typeof toastr !== 'undefined') {
                    toastr.error('{{ __("Request failed. Please try again.") }}');
                }
            });
        }
 
        /* ── Inline cancel button ─────────────────────────────── */
        document.addEventListener('click', function (e) {
            const btn = e.target.closest('.po-cancel-btn');
            if (!btn) return;
            openConfirm(btn.dataset.cancelUrl);
        });
 
        /* ── Modal cancel button ──────────────────────────────── */
        document.getElementById('poModalCancelBtn')?.addEventListener('click', function () {
            if (!currentCancelUrl) return;
            closeModal();
            openConfirm(currentCancelUrl);
        });
 
        /* ── Order detail modal ───────────────────────────────── */
        const backdrop  = document.getElementById('poModalBackdrop');
        const closeBtn  = document.getElementById('poModalClose');
        const closeBtn2 = document.getElementById('poModalCloseBtn');
 
        const PAID      = '{{ PRODUCT_ORDER_STATUS_PAID }}';
        const CANCELLED = '{{ PRODUCT_ORDER_STATUS_CANCELLED }}';
        const INREFUND = '{{ PRODUCT_ORDER_STATUS_REFUND_PENDING }}';
        const ORDER_COMPLETED = '{{ ORDER_STATUS_COMPLETED }}';
 
        let currentCancelUrl = '';
 
        function openModal(data) {
            document.getElementById('poModalTitle').textContent   = data.orderNo || '—';
            document.getElementById('poModalProduct').textContent = data.product || '—';
            document.getElementById('poModalQty').textContent     = data.qty     || '—';
            document.getElementById('poModalDate').textContent    = data.date    || '—';
            document.getElementById('poModalAmount').textContent  = data.amount  || '—';
            document.getElementById('poModalGateway').textContent = data.gateway || '—';
 
            const itemCountField = document.getElementById('poModalItemCountField');
            const itemCount = parseInt(data.itemCount || 1);
            if (itemCount > 1) {
                document.getElementById('poModalItemCount').textContent = itemCount + ' {{ __("products") }}';
                itemCountField.style.display = 'flex';
            } else {
                itemCountField.style.display = 'none';
            }
 
            const imageEl = document.getElementById('poModalProductImage');
            if (data.image && data.image.trim()) {
                imageEl.src = data.image;
                imageEl.style.display = 'block';
            } else {
                imageEl.style.display = 'none';
            }
 
            // Payment status badge
            const payBadge = document.getElementById('poModalPaymentBadge');
            const ps = String(data.paymentStatus);
            if (ps === PAID) {
                payBadge.className = 'po-modal__status-badge inv-badge inv-badge--paid';
                payBadge.innerHTML = checkIcon() + '{{ __("Paid") }}';
            } else if (ps === CANCELLED) {
                payBadge.className = 'po-modal__status-badge inv-badge inv-badge--cancelled';
                payBadge.innerHTML = crossIcon() + '{{ __("Cancelled") }}';
            } else if (ps === INREFUND) {
                payBadge.className = 'po-modal__status-badge inv-badge inv-badge--pending';
                payBadge.innerHTML = crossIcon() + '{{ __("In Refund") }}';
            } else {
                payBadge.className = 'po-modal__status-badge inv-badge inv-badge--pending';
                payBadge.innerHTML = clockIcon() + '{{ __("Pending") }}';
            }
 
            // Order status badge
            const orderBadge = document.getElementById('poModalOrderBadge');
            if (String(data.orderStatus) === ORDER_COMPLETED) {
                orderBadge.className = 'po-modal__status-badge inv-badge inv-badge--order-completed';
                orderBadge.innerHTML = checkIcon() + '{{ __("Completed") }}';
            } else if (String(data.orderStatus) === CANCELLED) {
                orderBadge.className = 'po-modal__status-badge inv-badge inv-badge--cancelled';
                orderBadge.innerHTML = checkIcon() + '{{ __("Cancelled") }}';
            } else {
                orderBadge.className = 'po-modal__status-badge inv-badge inv-badge--order-pending';
                orderBadge.innerHTML = clockIcon() + '{{ __("Processing") }}';
            }
 
            // Receipt link
            const receiptLink = document.getElementById('poModalReceiptLink');
            if (receiptLink) receiptLink.href = data.receiptUrl || '#';
 
            // Cancel button in modal
            currentCancelUrl = data.cancelUrl || '';
            const cancelWrap = document.getElementById('poModalCancelWrap');
            if (cancelWrap) {
                cancelWrap.style.display = (currentCancelUrl && ps !== CANCELLED && String(data.orderStatus) !== ORDER_COMPLETED)
                    ? 'block' : 'none';
            }
 
            backdrop.setAttribute('aria-hidden', 'false');
            backdrop.classList.add('is-open');
            document.body.style.overflow = 'hidden';
        }
 
        function closeModal() {
            backdrop.classList.remove('is-open');
            backdrop.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
        }
 
        document.addEventListener('click', function (e) {
            const btn = e.target.closest('.po-view-btn');
            if (!btn) return;
            openModal({
                orderNo       : btn.dataset.orderNo,
                product       : btn.dataset.product,
                qty           : btn.dataset.qty,
                amount        : btn.dataset.amount,
                date          : btn.dataset.date,
                paymentStatus : btn.dataset.paymentStatus,
                orderStatus   : btn.dataset.orderStatus,
                image         : btn.dataset.image || '',
                itemCount     : btn.dataset.itemCount || 1,
                gateway       : btn.dataset.gateway || '—',
                cancelUrl     : btn.dataset.cancelUrl || '',
                receiptUrl    : btn.dataset.receiptUrl || '#',
            });
        });
 
        if (closeBtn)  closeBtn.addEventListener('click',  closeModal);
        if (closeBtn2) closeBtn2.addEventListener('click', closeModal);
        backdrop.addEventListener('click', e => { if (e.target === backdrop) closeModal(); });
        document.addEventListener('keydown', e => {
            if (e.key === 'Escape') {
                if (confirmBackdrop.classList.contains('is-open')) { closeConfirm(); return; }
                if (backdrop.classList.contains('is-open')) closeModal();
            }
        });
 
        function checkIcon() { return '<svg width="9" height="9" viewBox="0 0 16 16" fill="none" style="margin-right:3px"><path d="M3 8l4 4 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>'; }
        function crossIcon() { return '<svg width="9" height="9" viewBox="0 0 16 16" fill="none" style="margin-right:3px"><path d="M4 4l8 8M12 4l-8 8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>'; }
        function clockIcon() { return '<svg width="9" height="9" viewBox="0 0 16 16" fill="none" style="margin-right:3px"><circle cx="8" cy="8" r="5.5" stroke="currentColor" stroke-width="1.6"/><path d="M8 5v3.5l2 1.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>'; }
    })();
    </script>
@endpush