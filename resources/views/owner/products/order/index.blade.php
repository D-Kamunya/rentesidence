@extends('owner.layouts.app')

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
                                    <li><a href="{{ route('owner.dashboard') }}">{{ __('Dashboard') }}</a></li>
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

                    {{-- Main Table Card --}}
                    <div class="dash-card">
                        <div class="dash-card__head d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <div class="d-flex align-items-center gap-2">
                                <span style="font-weight:500;font-size:14px;">{{ __('All Product Orders') }}</span>
                                <span class="inv-count-pill">{{ $totalProductOrders }} {{ __('total') }}</span>
                            </div>
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                        
                                {{-- Status filter tabs — submit as ?status= query param --}}
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
                        
                                {{-- Search form — preserves active status filter --}}
                                <form method="GET" action="" class="inv-search-wrap" id="orderSearchForm">
                                    <input type="hidden" name="status" value="{{ $activeStatus }}">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none">
                                        <circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="1.8"/>
                                        <path d="M16.5 16.5l4 4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                    </svg>
                                    <input type="text"
                                        name="search"
                                        id="orderSearch"
                                        value="{{ $search }}"
                                        placeholder="{{ __('Search orders…') }}"
                                        autocomplete="off">
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
                                        <th class="inv-th all">{{ __('Product') }}</th>
                                        <th class="inv-th desktop">{{ __('Qty') }}</th>
                                        <th class="inv-th desktop">{{ __('Order Date') }}</th>
                                        <th class="inv-th desktop">{{ __('Amount') }}</th>
                                        <th class="inv-th desktop">{{ __('Dispatch To') }}</th>
                                        <th class="inv-th all">{{ __('Payment') }}</th>
                                        <th class="inv-th desktop" style="text-align:right; width: 130px; min-width: 130px;">{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($productOrders as $order)
                                        @php
                                            $product = $order->orderItems->first()?->product;
                                            $images = $product?->images ?? null;
                                            $images = is_string($images) ? json_decode($images, true) : $images;
                                            $firstImage = (is_array($images) && count($images) > 0) ? $images[0] : null;
                                            $imageUrl = $firstImage ? asset('storage/' . ltrim($firstImage, '/')) : '';

                                            $isPaid          = $order->payment_status === PRODUCT_ORDER_STATUS_PAID;
                                            $isCompleted     = $order->order_status   === ORDER_STATUS_COMPLETED;
                                            $isCancelled     = $order->payment_status === PRODUCT_ORDER_STATUS_CANCELLED;
                                            $isRefundPending = $order->payment_status === PRODUCT_ORDER_STATUS_REFUND_PENDING;
                                            $statusClass     = $isPaid ? 'completed' : ($isCancelled ? 'cancelled' : 'pending');

                                            $tenant      = $order->user?->tenant;
                                            $dispatchStr = collect([
                                                $tenant?->property?->name,
                                                $tenant?->unit?->unit_number ? 'Unit ' . $tenant->unit->unit_number : null,
                                            ])->filter()->join(' · ') ?: '—';
                                        @endphp

                                        <tr class="inv-table__row po-row {{ $isCompleted ? 'po-row--completed' : '' }}" data-status="{{ $statusClass }}">
                                            <td class="inv-td" style="color:#9ca3af;font-size:11px;">{{ $loop->iteration }}</td>

                                            <td class="inv-td">
                                                <span class="inv-no">{{ $order->order_id ?? 'N/A' }}</span>
                                            </td>

                                            <td class="inv-td">
                                                <div class="d-flex align-items-center gap-3">
                                                    @if ($imageUrl)
                                                        <img src="{{ $imageUrl }}" alt="{{ $product?->name }}"
                                                             style="width:45px;height:45px;object-fit:cover;border-radius:8px;border:1px solid #e5e7eb;">
                                                    @endif
                                                    <span style="font-size:13px;font-weight:500;color:#111827;">
                                                        {{ $product?->name ?? '—' }}
                                                    </span>
                                                </div>
                                            </td>

                                            <td class="inv-td desktop">
                                                <span class="inv-secondary-text">{{ $order->orderItems->sum('quantity') }}</span>
                                            </td>

                                            <td class="inv-td desktop">
                                                <span class="inv-secondary-text">{{ $order->created_at->format('d M Y') }}</span>
                                            </td>

                                            <td class="inv-td desktop">
                                                <span class="inv-amount {{ $isPaid ? 'inv-amount--paid' : ($isCancelled ? 'inv-amount--cancelled' : 'inv-amount--pending') }}">
                                                    {{ number_format($order->transaction_amount ?? 0, 2) }}
                                                </span>
                                            </td>

                                            {{-- Dispatch To --}}
                                            <td class="inv-td desktop">
                                                @if($tenant)
                                                    <div style="font-size:12px;font-weight:500;color:#111827;line-height:1.5;">
                                                        {{ $order->user->name }}
                                                    </div>
                                                    <div style="font-size:11px;color:#6b7280;line-height:1.5;">
                                                        {{ $dispatchStr }}
                                                    </div>
                                                @else
                                                    <span style="font-size:12px;color:#9ca3af;">—</span>
                                                @endif
                                            </td>

                                            <td class="inv-td all">
                                                @if ($isPaid)
                                                    <span class="inv-badge inv-badge--paid">
                                                        <svg width="10" height="10" viewBox="0 0 16 16" fill="none">
                                                            <path d="M3 8l4 4 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                        </svg>
                                                        {{ __('Paid') }}
                                                    </span>
                                                @elseif ($isCancelled)
                                                    <span class="inv-badge inv-badge--cancelled">
                                                        <svg width="10" height="10" viewBox="0 0 16 16" fill="none">
                                                            <path d="M4 4l8 8M12 4l-8 8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                                        </svg>
                                                        {{ __('Cancelled') }}
                                                    </span>
                                                @elseif ($isRefundPending)
                                                    <span class="inv-badge inv-badge--refund ">
                                                        <svg width="10" height="10" viewBox="0 0 16 16" fill="none">
                                                            <path d="M4 4l8 8M12 4l-8 8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                                        </svg>
                                                        {{ __('Refund Pending') }}
                                                    </span>
                                                @else
                                                    <span class="inv-badge inv-badge--pending">
                                                        <svg width="10" height="10" viewBox="0 0 16 16" fill="none">
                                                            <circle cx="8" cy="8" r="5.5" stroke="currentColor" stroke-width="1.6"/>
                                                            <path d="M8 5v3.5l2 1.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                                                        </svg>
                                                        {{ __('Pending') }}
                                                    </span>
                                                @endif
                                            </td>

                                            @php
                                                $isCancelledByTenant  = in_array($order->payment_status, [PRODUCT_ORDER_STATUS_CANCELLED, PRODUCT_ORDER_STATUS_REFUND_PENDING]);
                                                $isRefundPending      = $order->payment_status === PRODUCT_ORDER_STATUS_REFUND_PENDING;
                                                $isOrderCancelled     = $order->order_status   === ORDER_STATUS_CANCELLED;
                                                $isOrderCompleted     = $order->order_status   === ORDER_STATUS_COMPLETED;
                                                $isPayCancelled       = $order->payment_status === PRODUCT_ORDER_STATUS_CANCELLED;
                                            @endphp
                                            
                                            <td class="inv-td desktop">
                                                <div class="inv-actions">
                                            
                                                    {{-- View --}}
                                                    <button type="button" class="inv-btn inv-btn--ghost po-view-btn"
                                                            data-order-no="{{ $order->order_id ?? $order->id }}"
                                                            data-order-db-id="{{ $order->id }}"
                                                            data-product="{{ $order->orderItems->pluck('product.name')->join(', ') ?: '—' }}"
                                                            data-qty="{{ $order->orderItems->sum('quantity') }}"
                                                            data-amount="{{ number_format($order->transaction_amount ?? 0, 2) }}"
                                                            data-date="{{ $order->created_at->format('d M Y') }}"
                                                            data-status="{{ $order->payment_status }}"
                                                            data-order-status="{{ $order->order_status }}"
                                                            data-gateway="{{ $order->gateway?->title ?? '—' }}"
                                                            data-tenant-name="{{ $order->user?->name ?? '—' }}"
                                                            data-dispatch="{{ $dispatchStr }}"
                                                            data-image="@php
                                                                $imgs = $product?->images ?? null;
                                                                $imgs = is_string($imgs) ? json_decode($imgs, true) : $imgs;
                                                                $first = is_array($imgs) && count($imgs) ? $imgs[0] : null;
                                                                echo $first ? asset('storage/' . ltrim($first, '/')) : '';
                                                            @endphp"
                                                            data-complete-url="{{ !$isOrderCompleted && !$isCancelledByTenant && !$isOrderCancelled ? route('owner.productOrder.markComplete', $order->id) : '' }}"
                                                            data-cancel-url="{{ !$isOrderCompleted && !$isOrderCancelled && !$isPayCancelled && !$isRefundPending ? route('owner.productOrder.cancel', $order->id) : '' }}"
                                                            data-confirm-cancel-url="{{ $isCancelledByTenant && !$isOrderCancelled ? route('owner.productOrder.cancel', $order->id) : '' }}"
                                                            data-refund-url="{{ $isRefundPending && $isOrderCancelled ? route('owner.productOrder.confirmRefund', $order->id) : '' }}">
                                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none">
                                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" stroke="currentColor" stroke-width="1.8"/>
                                                            <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.8"/>
                                                        </svg>
                                                        {{ __('View') }}
                                                    </button>
                                            
                                                    {{-- Complete — greyed/disabled if cancelled by tenant --}}
                                                    @if (!$isOrderCompleted && !$isOrderCancelled)
                                                        @if ($isCancelledByTenant)
                                                            <button type="button" class="inv-btn inv-btn--complete inv-btn--disabled" disabled title="{{ __('Cannot complete a cancelled order') }}">
                                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none">
                                                                    <path d="M20 6L9 17l-5-5" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                                </svg>
                                                                {{ __('Complete') }}
                                                            </button>
                                                        @else
                                                            <button type="button" class="inv-btn inv-btn--complete po-complete-btn"
                                                                    data-complete-url="{{ route('owner.productOrder.markComplete', $order->id) }}">
                                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none">
                                                                    <path d="M20 6L9 17l-5-5" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                                </svg>
                                                                {{ __('Complete') }}
                                                            </button>
                                                        @endif
                                                    @endif
                                            
                                                    {{-- Confirm Cancellation — tenant cancelled, owner hasn't confirmed yet --}}
                                                    @if ($isCancelledByTenant && !$isOrderCancelled)
                                                        <button type="button" class="inv-btn inv-btn--warn po-confirm-cancel-btn"
                                                                data-confirm-cancel-url="{{ route('owner.productOrder.cancel', $order->id) }}"
                                                                title="{{ __('Confirm this cancellation') }}">
                                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none">
                                                                <path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
                                                            </svg>
                                                            {{ __('Confirm Cancel') }}
                                                        </button>
                                                    @endif
                                            
                                                    {{-- Confirm Refund — order cancelled and refund is pending --}}
                                                    @if ($isRefundPending && $isOrderCancelled)
                                                        <button type="button" class="inv-btn inv-btn--refund po-confirm-refund-btn"
                                                                data-refund-url="{{ route('owner.productOrder.confirmRefund', $order->id) }}"
                                                                title="{{ __('Mark refund as issued') }}">
                                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none">
                                                                <path d="M3 10h10a8 8 0 018 8v2M3 10l6 6M3 10l6-6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                                            </svg>
                                                            {{ __('Refund Issued') }}
                                                        </button>
                                                    @endif
                                            
                                                    {{-- Final states --}}
                                                    @if ($isOrderCompleted)
                                                        <span class="inv-badge inv-badge--completed">
                                                            <svg width="10" height="10" viewBox="0 0 16 16" fill="none"><path d="M3 8l4 4 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                                            {{ __('Completed') }}
                                                        </span>
                                                    @endif
                                            
                                                    @if ($isOrderCancelled && !$isRefundPending)
                                                        <span class="inv-badge inv-badge--cancelled">
                                                            <svg width="10" height="10" viewBox="0 0 16 16" fill="none"><path d="M4 4l8 8M12 4l-8 8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                                                            {{ __('Cancelled') }}
                                                        </span>
                                                    @endif
                                            
                                                    {{-- Owner cancel — only when order is active and not tenant-cancelled --}}
                                                    @if (!$isOrderCompleted && !$isOrderCancelled && !$isCancelledByTenant && !$isPayCancelled && !$isRefundPending)
                                                        <button type="button" class="inv-btn inv-btn--cancel po-cancel-btn"
                                                                data-cancel-url="{{ route('owner.productOrder.cancel', $order->id) }}">
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
                                                <p style="margin:8px 0 4px;font-weight:500;color:#374151;font-size:14px;">{{ __('No product orders yet') }}</p>
                                                <p style="font-size:13px;color:#9ca3af;">{{ __('Orders from your tenants will appear here.') }}</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                            {{-- Pagination --}}
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

{{-- Modal --}}
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
            <div class="po-modal__status-wrap" id="poModalStatusWrap">
                <span class="po-modal__status-badge" id="poModalStatusBadge"></span>
            </div>
            <div class="po-modal__product-preview mb-4">
                <div class="po-modal__image-container">
                    <img id="poModalProductImage" src="" alt="Product" class="po-modal__product-image" onerror="this.style.display='none'">
                </div>
            </div>
            <div class="po-modal__grid">
                <div class="po-modal__field"><span class="po-modal__label">{{ __('Product') }}</span><span class="po-modal__value" id="poModalProduct">—</span></div>
                <div class="po-modal__field"><span class="po-modal__label">{{ __('Quantity') }}</span><span class="po-modal__value" id="poModalQty">—</span></div>
                <div class="po-modal__field"><span class="po-modal__label">{{ __('Order Date') }}</span><span class="po-modal__value" id="poModalDate">—</span></div>
                <div class="po-modal__field"><span class="po-modal__label">{{ __('Total Amount') }}</span><span class="po-modal__value po-modal__value--amount" id="poModalAmount">—</span></div>
                <div class="po-modal__field"><span class="po-modal__label">{{ __('Payment Method') }}</span><span class="po-modal__value" id="poModalGateway">—</span></div>
                <div class="po-modal__field po-modal__field--full">
                    <span class="po-modal__label">{{ __('Dispatch To') }}</span>
                    <span class="po-modal__value" id="poModalTenantName" style="font-weight:600;color:#111827;">—</span>
                    <span class="po-modal__value" id="poModalDispatch" style="font-size:12px;color:#6b7280;margin-top:2px;">—</span>
                </div>
            </div>
            <div class="po-modal__footer-actions" id="poModalCompleteWrap" style="display:none;">
                <button type="button" class="po-modal__complete-btn" id="poModalCompleteBtn">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none">
                        <path d="M20 6L9 17l-5-5" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    {{ __('Mark as Completed') }}
                </button>
                <p class="po-modal__action-hint">{{ __('Tenant will be notified that their order is ready.') }}</p>
            </div>
 
            {{-- Owner-initiated cancel --}}
            <div class="po-modal__footer-actions" id="poModalCancelWrap" style="display:none;">
                <button type="button" class="po-modal__cancel-btn" id="poModalCancelBtn">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none">
                        <path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
                    </svg>
                    {{ __('Cancel This Order') }}
                </button>
                <p class="po-modal__action-hint">{{ __('Tenant will be notified.') }}</p>
            </div>
 
            {{-- Confirm cancellation (tenant cancelled, owner confirms) --}}
            <div class="po-modal__footer-actions" id="poModalConfirmCancelWrap" style="display:none;">
                <button type="button" class="po-modal__warn-btn" id="poModalConfirmCancelBtn">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none">
                        <path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
                    </svg>
                    {{ __('Confirm Cancellation') }}
                </button>
                <p class="po-modal__action-hint">{{ __('The tenant has requested cancellation. Confirm to close this order.') }}</p>
            </div>
 
            {{-- Confirm refund --}}
            <div class="po-modal__footer-actions" id="poModalRefundWrap" style="display:none;">
                <button type="button" class="po-modal__refund-btn" id="poModalRefundBtn">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none">
                        <path d="M3 10h10a8 8 0 018 8v2M3 10l6 6M3 10l6-6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    {{ __('Confirm Refund Issued') }}
                </button>
                <p class="po-modal__action-hint">{{ __('Confirm that you have returned the payment to the tenant.') }}</p>
            </div>
        </div>

        <div class="po-modal__footer">
            <button type="button" class="inv-btn inv-btn--ghost" id="poModalCloseBtn">{{ __('Close') }}</button>
        </div>
    </div>
</div>
<div class="confirm-backdrop" id="confirmBackdrop" aria-hidden="true">
    <div class="confirm-modal" role="dialog" aria-modal="true">
        <div class="confirm-modal__header">
            <div class="confirm-modal__icon" id="confirmIcon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" id="confirmIconSvg">
                    <path d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <p class="confirm-modal__title" id="confirmTitle">Are you sure?</p>
        </div>
        <div class="confirm-modal__body" id="confirmBody">
            This action cannot be undone.
        </div>
        <div class="confirm-modal__footer">
            <button type="button" class="confirm-btn confirm-btn--ghost" id="confirmCancelBtn">
                {{ __('Cancel') }}
            </button>
            <button type="button" class="confirm-btn" id="confirmOkBtn">
                {{ __('Confirm') }}
            </button>
        </div>
    </div>
</div>
@endsection

@push('style')
    @include('common.layouts.datatable-style')
    <style>
        /* ── Page chrome ─────────────────────────────────────────── */
        .dash-header { display:flex; align-items:flex-start; justify-content:space-between; flex-wrap:wrap; gap:1rem; }
        .dash-title  { font-size:22px; font-weight:500; color:#111827; margin:0 0 6px; }
    
        .inv-breadcrumb { list-style:none; display:flex; align-items:center; gap:6px; margin:0; padding:0; font-size:12px; color:#9ca3af; }
        .inv-breadcrumb a { color:#185FA5; text-decoration:none; font-weight:500; }
        .inv-breadcrumb a:hover { color:#0F4A84; }
        .inv-breadcrumb li { display:flex; align-items:center; gap:6px; }
    
        /* ── Summary strip ───────────────────────────────────────── */
        .inv-strip { display:flex; align-items:center; background:#fff; border:0.5px solid #e5e7eb; border-radius:12px; overflow:hidden; flex-wrap:wrap; }
        .inv-strip__item { display:flex; align-items:center; gap:10px; padding:.9rem 1.35rem; flex:1; min-width:100px; max-width:220px; }
        .inv-strip__divider { width:0.5px; align-self:stretch; background:#e5e7eb; flex-shrink:0; }
        .inv-strip__dot { width:8px; height:8px; border-radius:50%; flex-shrink:0; }
        .inv-strip__dot--blue   { background:#185FA5; }
        .inv-strip__dot--green  { background:#1D9E75; }
        .inv-strip__dot--amber  { background:#854F0B; }
        .inv-strip__dot--coral  { background:#993C1D; }
        .inv-strip__label { font-size:10px; font-weight:500; text-transform:uppercase; letter-spacing:.07em; color:#9ca3af; margin:0 0 3px; }
        .inv-strip__val { font-size:18px; font-weight:500; margin:0; line-height:1; }
        .inv-strip__val--blue   { color:#185FA5; }
        .inv-strip__val--green  { color:#0F6E56; }
        .inv-strip__val--amber  { color:#854F0B; }
        .inv-strip__val--coral  { color:#993C1D; }
    
        /* ── Dash card ───────────────────────────────────────────── */
        .dash-card { background:#fff; border:0.5px solid #e5e7eb; border-radius:12px; overflow:hidden; }
        .dash-card__head { padding:.75rem 1.1rem; border-bottom:0.5px solid #e5e7eb; background:#fafafa; }
        .inv-count-pill { display:inline-block; background:#f3f4f6; color:#6b7280; font-size:11px; font-weight:500; padding:3px 10px; border-radius:99px; }
    
        /* ── Search ──────────────────────────────────────────────── */
        .inv-search-wrap { position:relative; display:flex; align-items:center; }
        .inv-search-wrap svg { position:absolute; left:8px; color:#9ca3af; pointer-events:none; }
        .inv-search-wrap input { border:0.5px solid #e5e7eb; border-radius:7px; padding:5px 10px 5px 28px; font-size:12px; color:#374151; background:#fff; outline:none; width:180px; transition:border-color .15s, box-shadow .15s; }
        .inv-search-wrap input:focus { border-color:#185FA5; box-shadow:0 0 0 3px rgba(24,95,165,.1); }
        .inv-search-wrap input::placeholder { color:#c4c4c4; }
        .inv-search-clear { position:absolute; right:8px; color:#9ca3af; font-size:11px; text-decoration:none; line-height:1; transition:color .15s; }
        .inv-search-clear:hover { color:#374151; }
    
        /* ── Table ───────────────────────────────────────────────── */
        .inv-table { width:100%; border-collapse:collapse; }
        .inv-table__head { background:#fafafa; border-bottom:0.5px solid #e5e7eb; }
        .inv-th { padding:.65rem 1rem; font-size:10px; font-weight:500; color:#6b7280; text-transform:uppercase; letter-spacing:.07em; border:none; white-space:nowrap; }
        .inv-td { padding:.8rem 1rem; border:none; vertical-align:middle; }
        .inv-table__row { border-bottom:0.5px solid #f3f4f6; transition:background .12s; }
        .inv-table__row:last-child { border-bottom:none; }
        .inv-table__row:nth-child(even) { background:#fafafa; }
        .inv-table__row:hover { background:#f3f4f6; }
        .po-row--completed { opacity:0.62; }
        .po-row--completed:hover { opacity:1; }
        .po-row--completed .inv-no { background:#f3f4f6; color:#9ca3af; }
    
        .inv-no { display:inline-block; font-size:11px; font-weight:500; font-family:monospace; letter-spacing:.04em; background:#E6F1FB; color:#0C447C; padding:3px 9px; border-radius:6px; }
        .inv-secondary-text { font-size:12px; color:#6b7280; }
    
        /* ── Amount chips ────────────────────────────────────────── */
        .inv-amount { font-size:13px; font-weight:500; padding:3px 10px; border-radius:99px; white-space:nowrap; display:inline-block; }
        .inv-amount--paid      { background:#E1F5EE; color:#0F6E56; }
        .inv-amount--pending   { background:#FAEEDA; color:#854F0B; }
        .inv-amount--cancelled { background:#F3F4F6; color:#6b7280; }
    
        /* ── Status badges ───────────────────────────────────────── */
        .inv-badge { display:inline-flex; align-items:center; gap:4px; font-size:11px; font-weight:500; padding:3px 9px; border-radius:99px; white-space:nowrap; }
        .inv-badge--paid      { background:#E1F5EE; color:#0F6E56; }
        .inv-badge--pending   { background:#FAEEDA; color:#854F0B; border:0.5px solid #F5D9A8; }
        .inv-badge--cancelled { background:#F3F4F6; color:#6b7280; border:0.5px solid #e5e7eb; }
        .inv-badge--completed { background:#E1F5EE; color:#0F6E56; }
        .inv-badge--refund    { background:#EEF2FF; color:#3730A3; border:0.5px solid #C7D2FE; }
        .inv-badge--order-completed { background:#E1F5EE; color:#0F6E56; }
        .inv-badge--order-pending   { background:#EEF2FF; color:#3730A3; border:0.5px solid #C7D2FE; }
    
        /* ── Filter tabs ─────────────────────────────────────────── */
        .inv-filter-tabs { display:flex; background:#f3f4f6; border-radius:8px; padding:3px; gap:2px; }
        .inv-filter-tab { display:inline-flex; align-items:center; gap:5px; background:transparent; border:none; font-size:12px; font-weight:500; color:#6b7280; padding:4px 12px; border-radius:6px; cursor:pointer; transition:background .15s, color .15s; white-space:nowrap; text-decoration:none; }
        .inv-filter-tab--active { background:#fff; color:#111827; box-shadow:0 1px 3px rgba(0,0,0,.08); }
        .inv-filter-tab__dot { width:6px; height:6px; border-radius:50%; flex-shrink:0; }
        .inv-filter-tab__dot--paid      { background:#1D9E75; }
        .inv-filter-tab__dot--unpaid    { background:#854F0B; }
        .inv-filter-tab__dot--cancelled { background:#9ca3af; }
    
        /* ── Action column ───────────────────────────────────────── */
        .inv-actions { display:flex; flex-direction:column; align-items:flex-end; gap:5px; }
        .inv-actions .inv-btn { width:100%; justify-content:flex-start; }
    
        /* ── Base button ─────────────────────────────────────────── */
        .inv-btn { display:inline-flex; align-items:center; justify-content:center; gap:5px; font-size:12px; font-weight:500; padding:5px 12px; border-radius:7px; text-decoration:none; white-space:nowrap; transition:background .15s, transform .12s, box-shadow .12s; cursor:pointer; border:none; }
    
        /* ── Inline button variants ──────────────────────────────── */
        .inv-btn--ghost    { background:#f3f4f6; color:#374151; border:0.5px solid #e5e7eb; }
        .inv-btn--ghost:hover { background:#e5e7eb; color:#111827; text-decoration:none; }
    
        .inv-btn--complete { background:#E1F5EE; color:#0F6E56; border:0.5px solid #A7DFC9; }
        .inv-btn--complete:hover { background:#1D9E75; color:#fff; border-color:#1D9E75; }
    
        .inv-btn--cancel   { background:#FDF4F1; color:#993C1D; border:0.5px solid #F5C6B8; }
        .inv-btn--cancel:hover { background:#993C1D; color:#fff; border-color:#993C1D; }
    
        .inv-btn--warn     { background:#FFFBEB; color:#92400E; border:0.5px solid #FDE68A; }
        .inv-btn--warn:hover { background:#D97706; color:#fff; border-color:#D97706; }
    
        .inv-btn--refund   { background:#EEF2FF; color:#3730A3; border:0.5px solid #C7D2FE; }
        .inv-btn--refund:hover { background:#4338CA; color:#fff; border-color:#4338CA; }
    
        .inv-btn--receipt  { background:#E6F1FB; color:#185FA5; border:0.5px solid #B8D4F0; }
        .inv-btn--receipt:hover { background:#185FA5; color:#fff; border-color:#185FA5; text-decoration:none; }
    
        .inv-btn--disabled { background:#f3f4f6 !important; color:#d1d5db !important; border-color:#e5e7eb !important; cursor:not-allowed !important; opacity:0.6; }
    
        /* ── Empty state ─────────────────────────────────────────── */
        .inv-empty { text-align:center; padding:3rem 1rem; color:#9ca3af; }
    
        /* ── Pagination ──────────────────────────────────────────── */
        .inv-pagination-wrap { padding:.75rem 1.1rem; border-top:0.5px solid #e5e7eb; background:#fafafa; display:flex; justify-content:flex-end; }
        .inv-pagination-wrap nav { display:flex; align-items:center; gap:4px; }
        .inv-pagination-wrap span[aria-disabled],
        .inv-pagination-wrap a { display:inline-flex; align-items:center; justify-content:center; min-width:32px; height:32px; padding:0 8px; border-radius:7px; font-size:12px; font-weight:500; border:0.5px solid #e5e7eb; color:#374151; background:#fff; text-decoration:none; transition:background .12s, border-color .12s; }
        .inv-pagination-wrap a:hover { background:#f3f4f6; border-color:#d1d5db; color:#111827; }
        .inv-pagination-wrap span[aria-current="page"] > span { display:inline-flex; align-items:center; justify-content:center; min-width:32px; height:32px; padding:0 8px; border-radius:7px; font-size:12px; font-weight:600; background:#185FA5; border:0.5px solid #185FA5; color:#fff; }
        .inv-pagination-wrap span[aria-disabled] > span { color:#d1d5db; border-color:#f3f4f6; background:#fafafa; cursor:default; }
    
        /* ── DataTables overrides ────────────────────────────────── */
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
    
        /* ── Modal shell ─────────────────────────────────────────── */
        .po-modal-backdrop { position:fixed; inset:0; background:rgba(17,24,39,.45); backdrop-filter:blur(2px); -webkit-backdrop-filter:blur(2px); z-index:1050; display:flex; align-items:center; justify-content:center; padding:1rem; opacity:0; pointer-events:none; transition:opacity .2s ease; }
        .po-modal-backdrop.is-open { opacity:1; pointer-events:all; }
        .po-modal { background:#fff; border-radius:14px; width:100%; max-width:480px; box-shadow:0 20px 60px rgba(17,24,39,.18),0 4px 16px rgba(17,24,39,.08); transform:translateY(12px) scale(.98); transition:transform .22s ease,opacity .22s ease; opacity:0; overflow:hidden; }
        .po-modal-backdrop.is-open .po-modal { transform:translateY(0) scale(1); opacity:1; }
    
        /* ── Modal header ────────────────────────────────────────── */
        .po-modal__header { display:flex; align-items:flex-start; justify-content:space-between; padding:1.1rem 1.35rem 1rem; border-bottom:0.5px solid #e5e7eb; background:#fafafa; }
        .po-modal__eyebrow { font-size:10px; font-weight:500; text-transform:uppercase; letter-spacing:.07em; color:#9ca3af; margin:0 0 4px; }
        .po-modal__title { font-size:15px; font-weight:600; color:#111827; margin:0; font-family:monospace; letter-spacing:.04em; }
        .po-modal__close { background:#f3f4f6; border:none; border-radius:7px; width:30px; height:30px; display:flex; align-items:center; justify-content:center; cursor:pointer; color:#6b7280; flex-shrink:0; transition:background .15s,color .15s; }
        .po-modal__close:hover { background:#e5e7eb; color:#111827; }
    
        /* ── Modal body ──────────────────────────────────────────── */
        .po-modal__status-wrap { padding:.85rem 1.35rem; border-bottom:0.5px solid #f3f4f6; }
        .po-modal__body { padding:1.1rem 1.35rem; }
        .po-modal__grid { display:grid; grid-template-columns:1fr 1fr; gap:1rem .75rem; }
        .po-modal__field { display:flex; flex-direction:column; gap:4px; }
        .po-modal__field--full { grid-column:1 / -1; }
        .po-modal__label { font-size:10px; font-weight:500; text-transform:uppercase; letter-spacing:.07em; color:#9ca3af; }
        .po-modal__value { font-size:13px; font-weight:500; color:#111827; }
        .po-modal__value--amount { font-size:15px; font-weight:600; color:#185FA5; }
        .po-modal__product-preview { text-align:center; }
        .po-modal__image-container { display:inline-block; padding:8px; background:#f8fafc; border-radius:12px; border:1px solid #e5e7eb; }
        .po-modal__product-image { width:90px; height:90px; object-fit:cover; border-radius:10px; box-shadow:0 2px 8px rgba(0,0,0,.06); border:1px solid #e5e7eb; }

        /* ── Dispatch to block inside modal ──────────────────────── */
        .po-modal__dispatch-block {
            background:#f8fafc;
            border:0.5px solid #e5e7eb;
            border-radius:8px;
            padding:10px 14px;
            display:flex;
            flex-direction:column;
            gap:3px;
        }
    
        /* ── Modal footer ────────────────────────────────────────── */
        .po-modal__footer { padding:.85rem 1.35rem; border-top:0.5px solid #e5e7eb; display:flex; justify-content:space-between; align-items:center; gap:8px; background:#fafafa; }
    
        /* ── Modal action blocks (complete / cancel / warn / refund) */
        .po-modal__footer-actions { padding:.6rem 1.35rem; border-top:0.5px solid #f3f4f6; }
        .po-modal__action-hint { font-size:11px; color:#9ca3af; margin:5px 0 0; text-align:center; }
    
        /* Shared modal action button base */
        .po-modal__complete-btn,
        .po-modal__cancel-btn,
        .po-modal__warn-btn,
        .po-modal__refund-btn {
            display:flex; align-items:center; justify-content:center;
            gap:8px; width:100%; padding:11px 16px;
            font-size:13px; font-weight:600;
            border-radius:9px; cursor:pointer;
            transition:background .15s, transform .12s;
            border:none;
        }
    
        /* Individual modal action button colours */
        .po-modal__complete-btn { background:#1D9E75; color:#fff; }
        .po-modal__complete-btn:hover { background:#178060; transform:translateY(-1px); }
        .po-modal__complete-btn:disabled { background:#9ca3af; cursor:not-allowed; transform:none; }
    
        .po-modal__cancel-btn { background:#FDF4F1; color:#993C1D; border:0.5px solid #F5C6B8; }
        .po-modal__cancel-btn:hover { background:#993C1D; color:#fff; border-color:#993C1D; }
    
        .po-modal__warn-btn { background:#FFFBEB; color:#92400E; border:0.5px solid #FDE68A; }
        .po-modal__warn-btn:hover { background:#D97706; color:#fff; border-color:#D97706; }
    
        .po-modal__refund-btn { background:#EEF2FF; color:#3730A3; border:0.5px solid #C7D2FE; }
        .po-modal__refund-btn:hover { background:#4338CA; color:#fff; border-color:#4338CA; }
        
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
        .confirm-modal__icon--green  { background: #E1F5EE; color: #0F6E56; }
        .confirm-modal__icon--red    { background: #FDF4F1; color: #993C1D; }
        .confirm-modal__icon--amber  { background: #FFFBEB; color: #92400E; }
        .confirm-modal__icon--indigo { background: #EEF2FF; color: #3730A3; }
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
        .confirm-btn--ghost  { background: #f3f4f6; color: #374151; border: 0.5px solid #e5e7eb; }
        .confirm-btn--ghost:hover { background: #e5e7eb; }
        .confirm-btn--green  { background: #1D9E75; color: #fff; }
        .confirm-btn--green:hover  { background: #178060; }
        .confirm-btn--red    { background: #993C1D; color: #fff; }
        .confirm-btn--red:hover    { background: #7a2e14; }
        .confirm-btn--amber  { background: #D97706; color: #fff; }
        .confirm-btn--amber:hover  { background: #b45309; }
        .confirm-btn--indigo { background: #4338CA; color: #fff; }
        .confirm-btn--indigo:hover { background: #3730a3; }

        /* ── Responsive ──────────────────────────────────────────── */
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
    </style>
@endpush

@push('script')
@include('common.layouts.datatable-script')
    <script>
        (function () {
            'use strict';
        
            const tbody = document.querySelector('#allOrderDataTable tbody');
            const rows  = () => Array.from(document.querySelectorAll('#allOrderDataTable tbody .po-row'));
        
            let activeFilter        = 'all';
            let searchTerm          = '';
            let currentCompleteUrl      = '';
            let currentCancelUrl        = '';
            let currentConfirmCancelUrl = '';
            let currentRefundUrl        = '';
        
            /* ── Filter & search ─────────────────────────────────────── */
            function applyFilters() {
                rows().forEach(row => {
                    const matchesFilter = activeFilter === 'all' || row.dataset.status === activeFilter;
                    const matchesSearch = searchTerm === '' || row.textContent.toLowerCase().includes(searchTerm);
                    row.style.display   = (matchesFilter && matchesSearch) ? '' : 'none';
                });
                const visible = rows().filter(r => r.style.display !== 'none');
                let emptyRow  = document.getElementById('poEmptyRow');
                if (visible.length === 0) {
                    if (!emptyRow) {
                        emptyRow = document.createElement('tr');
                        emptyRow.id = 'poEmptyRow';
                        emptyRow.innerHTML = `<td colspan="9" class="inv-empty">
                            <p style="margin:8px 0 4px;font-weight:500;color:#374151;font-size:14px;">{{ __('No orders found') }}</p>
                            <p style="font-size:13px;color:#9ca3af;">{{ __('Try adjusting your search or filter.') }}</p>
                        </td>`;
                        tbody.appendChild(emptyRow);
                    }
                    emptyRow.style.display = '';
                } else if (emptyRow) {
                    emptyRow.style.display = 'none';
                }
            }
        
            const searchInput = document.getElementById('orderSearch');
            if (searchInput) {
                searchInput.addEventListener('input', function () {
                    searchTerm = this.value.toLowerCase().trim();
                    applyFilters();
                });
            }
        
            const filterContainer = document.getElementById('statusFilter');
            if (filterContainer) {
                filterContainer.addEventListener('click', function (e) {
                    const btn = e.target.closest('.inv-filter-tab');
                    if (!btn) return;
                    filterContainer.querySelectorAll('.inv-filter-tab').forEach(b => b.classList.remove('inv-filter-tab--active'));
                    btn.classList.add('inv-filter-tab--active');
                    activeFilter = btn.dataset.filter;
                    applyFilters();
                });
            }
        
            /* ── Custom confirmation modal ───────────────────────────── */
            const confirmBackdrop = document.getElementById('confirmBackdrop');
            const confirmOkBtn    = document.getElementById('confirmOkBtn');
            const confirmCancelBtn = document.getElementById('confirmCancelBtn');
            let pendingAction = null;
        
            const actionConfigs = {
                complete: {
                    title:     '{{ __("Mark as Completed?") }}',
                    body:      '{{ __("This will notify the tenant that their order is ready. This action cannot be undone.") }}',
                    iconClass: 'confirm-modal__icon--green',
                    btnClass:  'confirm-btn--green',
                    btnLabel:  '{{ __("Yes, Complete") }}',
                    icon:      `<svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M20 6L9 17l-5-5" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>`,
                },
                cancel: {
                    title:     '{{ __("Cancel This Order?") }}',
                    body:      '{{ __("The tenant will be notified. This action cannot be undone.") }}',
                    iconClass: 'confirm-modal__icon--red',
                    btnClass:  'confirm-btn--red',
                    btnLabel:  '{{ __("Yes, Cancel") }}',
                    icon:      `<svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/></svg>`,
                },
                confirmCancel: {
                    title:     '{{ __("Confirm Cancellation?") }}',
                    body:      '{{ __("The tenant requested this cancellation. Confirming will close the order and notify the tenant.") }}',
                    iconClass: 'confirm-modal__icon--amber',
                    btnClass:  'confirm-btn--amber',
                    btnLabel:  '{{ __("Yes, Confirm") }}',
                    icon:      `<svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/></svg>`,
                },
                refund: {
                    title:     '{{ __("Confirm Refund Issued?") }}',
                    body:      '{{ __("Confirm that you have returned the payment to the tenant. They will be notified.") }}',
                    iconClass: 'confirm-modal__icon--indigo',
                    btnClass:  'confirm-btn--indigo',
                    btnLabel:  '{{ __("Yes, Refund Issued") }}',
                    icon:      `<svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M3 10h10a8 8 0 018 8v2M3 10l6 6M3 10l6-6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>`,
                },
            };
        
            function openConfirm(type, url, onSuccess) {
                const cfg = actionConfigs[type];
                if (!cfg) return;
        
                document.getElementById('confirmTitle').textContent = cfg.title;
                document.getElementById('confirmBody').textContent  = cfg.body;
                document.getElementById('confirmIcon').className    = 'confirm-modal__icon ' + cfg.iconClass;
                document.getElementById('confirmIconSvg').outerHTML = cfg.icon;
                confirmOkBtn.className   = 'confirm-btn ' + cfg.btnClass;
                confirmOkBtn.textContent = cfg.btnLabel;
        
                pendingAction = { url, onSuccess };
        
                confirmBackdrop.setAttribute('aria-hidden', 'false');
                confirmBackdrop.classList.add('is-open');
            }
        
            function closeConfirm() {
                confirmBackdrop.classList.remove('is-open');
                confirmBackdrop.setAttribute('aria-hidden', 'true');
                pendingAction = null;
            }
        
            confirmCancelBtn?.addEventListener('click', closeConfirm);
            confirmBackdrop?.addEventListener('click', e => { if (e.target === confirmBackdrop) closeConfirm(); });
        
            confirmOkBtn?.addEventListener('click', function () {
                if (!pendingAction) return;
                const { url, onSuccess } = pendingAction;
                closeConfirm();
                doFetch(url, onSuccess);
            });
        
            /* ── Fetch action ────────────────────────────────────────── */
            function doFetch(url, onSuccess) {
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
                        if (typeof toastr !== 'undefined') toastr.success(data.message || '{{ __("Done.") }}');
                        if (typeof onSuccess === 'function') onSuccess();
                        setTimeout(() => location.reload(), 1400);
                    } else {
                        if (typeof toastr !== 'undefined') toastr.error(data.message || '{{ __("Something went wrong.") }}');
                    }
                    console.log(data)
                })
                .catch(() => {
                    if (typeof toastr !== 'undefined') toastr.error('{{ __("Request failed. Please try again.") }}');
                });
            }
        
            /* ── Inline button handlers ──────────────────────────────── */
            document.addEventListener('click', function (e) {
                if (e.target.closest('.po-complete-btn')) {
                    const btn = e.target.closest('.po-complete-btn');
                    openConfirm('complete', btn.dataset.completeUrl);
                }
                if (e.target.closest('.po-cancel-btn')) {
                    const btn = e.target.closest('.po-cancel-btn');
                    openConfirm('cancel', btn.dataset.cancelUrl);
                }
                if (e.target.closest('.po-confirm-cancel-btn')) {
                    const btn = e.target.closest('.po-confirm-cancel-btn');
                    openConfirm('confirmCancel', btn.dataset.confirmCancelUrl);
                }
                if (e.target.closest('.po-confirm-refund-btn')) {
                    const btn = e.target.closest('.po-confirm-refund-btn');
                    openConfirm('refund', btn.dataset.refundUrl);
                }
            });
        
            /* ── Modal action buttons ────────────────────────────────── */
            document.getElementById('poModalCompleteBtn')?.addEventListener('click', function () {
                if (!currentCompleteUrl) return;
                closeModal();
                openConfirm('complete', currentCompleteUrl, () => {
                    updateModalBadge('completed');
                    hideModalActions();
                });
            });
        
            document.getElementById('poModalCancelBtn')?.addEventListener('click', function () {
                if (!currentCancelUrl) return;
                closeModal();
                openConfirm('cancel', currentCancelUrl, () => {
                    updateModalBadge('cancelled');
                    hideModalActions();
                });
            });
        
            document.getElementById('poModalConfirmCancelBtn')?.addEventListener('click', function () {
                if (!currentConfirmCancelUrl) return;
                closeModal();
                openConfirm('confirmCancel', currentConfirmCancelUrl, () => {
                    updateModalBadge('cancelled');
                    hideModalActions();
                });
            });
        
            document.getElementById('poModalRefundBtn')?.addEventListener('click', function () {
                if (!currentRefundUrl) return;
                closeModal();
                openConfirm('refund', currentRefundUrl, () => {
                    updateModalBadge('refund-resolved');
                    hideModalActions();
                });
            });
        
            function updateModalBadge(state) {
                const badge = document.getElementById('poModalStatusBadge');
                const map = {
                    'completed':       { cls: 'inv-badge inv-badge--completed', icon: checkIcon(), label: '{{ __("Completed") }}' },
                    'cancelled':       { cls: 'inv-badge inv-badge--cancelled',  icon: crossIcon(), label: '{{ __("Cancelled") }}' },
                    'refund-resolved': { cls: 'inv-badge inv-badge--cancelled',  icon: checkIcon(), label: '{{ __("Refund Confirmed") }}' },
                };
                const cfg = map[state] || map['cancelled'];
                badge.className = cfg.cls;
                badge.innerHTML = cfg.icon + cfg.label;
            }
        
            function hideModalActions() {
                ['poModalCompleteWrap','poModalCancelWrap','poModalConfirmCancelWrap','poModalRefundWrap']
                    .forEach(id => {
                        const el = document.getElementById(id);
                        if (el) el.style.display = 'none';
                    });
            }
        
            /* ── Order detail modal ──────────────────────────────────── */
            const backdrop  = document.getElementById('poModalBackdrop');
            const closeBtn  = document.getElementById('poModalClose');
            const closeBtn2 = document.getElementById('poModalCloseBtn');
        
            const PAY_CANCELLED   = {{ PRODUCT_ORDER_STATUS_CANCELLED }};
            const PAY_REFUND      = {{ PRODUCT_ORDER_STATUS_REFUND_PENDING }};
            const ORDER_COMPLETED = {{ ORDER_STATUS_COMPLETED }};
            const ORDER_CANCELLED = {{ ORDER_STATUS_CANCELLED }};
        
            function openModal(data) {
                document.getElementById('poModalTitle').textContent      = data.orderNo     || '—';
                document.getElementById('poModalProduct').textContent    = data.product     || '—';
                document.getElementById('poModalQty').textContent        = data.qty         || '—';
                document.getElementById('poModalDate').textContent       = data.date        || '—';
                document.getElementById('poModalAmount').textContent     = data.amount      || '—';
                document.getElementById('poModalGateway').textContent    = data.gateway     || '—';
                document.getElementById('poModalTenantName').textContent = data.tenantName  || '—';
                document.getElementById('poModalDispatch').textContent   = data.dispatch    || '—';
        
                const imageEl = document.getElementById('poModalProductImage');
                if (data.image && data.image.trim()) { imageEl.src = data.image; imageEl.style.display = 'block'; }
                else { imageEl.style.display = 'none'; }
        
                const badge       = document.getElementById('poModalStatusBadge');
                const payStatus   = parseInt(data.status);
                const orderStatus = parseInt(data.orderStatus);
        
                if (orderStatus === ORDER_COMPLETED) {
                    badge.className = 'inv-badge inv-badge--completed';
                    badge.innerHTML = checkIcon() + '{{ __("Completed") }}';
                } else if (orderStatus === ORDER_CANCELLED) {
                    badge.className = 'inv-badge inv-badge--cancelled';
                    badge.innerHTML = crossIcon() + '{{ __("Cancelled") }}';
                } else if (payStatus === PAY_REFUND) {
                    badge.className = 'inv-badge inv-badge--refund';
                    badge.innerHTML = clockIcon() + '{{ __("Refund Pending") }}';
                } else if (payStatus === PAY_CANCELLED) {
                    badge.className = 'inv-badge inv-badge--cancelled';
                    badge.innerHTML = crossIcon() + '{{ __("Cancelled by Tenant") }}';
                } else {
                    badge.className = 'inv-badge inv-badge--pending';
                    badge.innerHTML = clockIcon() + '{{ __("Pending") }}';
                }
        
                currentCompleteUrl      = data.completeUrl      || '';
                currentCancelUrl        = data.cancelUrl        || '';
                currentConfirmCancelUrl = data.confirmCancelUrl || '';
                currentRefundUrl        = data.refundUrl        || '';
        
                showHide('poModalCompleteWrap',
                    currentCompleteUrl && orderStatus !== ORDER_COMPLETED &&
                    payStatus !== PAY_CANCELLED && payStatus !== PAY_REFUND &&
                    orderStatus !== ORDER_CANCELLED
                );
                showHide('poModalCancelWrap',
                    currentCancelUrl && orderStatus !== ORDER_COMPLETED &&
                    orderStatus !== ORDER_CANCELLED && payStatus !== PAY_CANCELLED
                );
                showHide('poModalConfirmCancelWrap',
                    currentConfirmCancelUrl && orderStatus !== ORDER_CANCELLED
                );
                showHide('poModalRefundWrap',
                    currentRefundUrl && payStatus === PAY_REFUND && orderStatus === ORDER_CANCELLED
                );
        
                backdrop.setAttribute('aria-hidden', 'false');
                backdrop.classList.add('is-open');
                document.body.style.overflow = 'hidden';
            }
        
            function showHide(id, condition) {
                const el = document.getElementById(id);
                if (el) el.style.display = condition ? 'block' : 'none';
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
                    orderNo          : btn.dataset.orderNo,
                    product          : btn.dataset.product,
                    qty              : btn.dataset.qty,
                    amount           : btn.dataset.amount,
                    date             : btn.dataset.date,
                    status           : btn.dataset.status,
                    orderStatus      : btn.dataset.orderStatus,
                    image            : btn.dataset.image || '',
                    gateway          : btn.dataset.gateway || '—',
                    tenantName       : btn.dataset.tenantName || '—',
                    dispatch         : btn.dataset.dispatch || '—',
                    completeUrl      : btn.dataset.completeUrl || '',
                    cancelUrl        : btn.dataset.cancelUrl || '',
                    confirmCancelUrl : btn.dataset.confirmCancelUrl || '',
                    refundUrl        : btn.dataset.refundUrl || '',
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
        
            function checkIcon() { return '<svg width="10" height="10" viewBox="0 0 16 16" fill="none" style="margin-right:3px"><path d="M3 8l4 4 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>'; }
            function crossIcon() { return '<svg width="10" height="10" viewBox="0 0 16 16" fill="none" style="margin-right:3px"><path d="M4 4l8 8M12 4l-8 8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>'; }
            function clockIcon() { return '<svg width="10" height="10" viewBox="0 0 16 16" fill="none" style="margin-right:3px"><circle cx="8" cy="8" r="5.5" stroke="currentColor" stroke-width="1.6"/><path d="M8 5v3.5l2 1.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>'; }
        })();
    </script>
@endpush