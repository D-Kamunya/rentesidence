@extends('admin.layouts.app')

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="page-content-wrapper bg-white p-30 radius-20">
                @include('centresidence._design')

                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between border-bottom mb-20">
                            <div class="page-title-left">
                                <h3 class="mb-sm-0">{{ __('Marketplace Refunds') }}</h3>
                            </div>
                            <div class="page-title-right">
                                <ol class="breadcrumb mb-0">
                                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">{{ __('Refunds') }}</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <p style="font-size:13px;color:#6b7280;margin:0 0 18px;max-width:70ch;">
                    {{ __('Buyers are paid back from the platform (which holds marketplace funds), so each refund needs your green-light. Approving sends an M-Pesa payout to the buyer; the owner/affiliate ledger is reversed only if the sale had already been released to the owner.') }}
                </p>

                <div class="cs-tablewrap">
                    <table class="cs-table" style="font-size:13px;">
                        <thead><tr>
                            <th>{{ __('Order') }}</th>
                            <th>{{ __('Buyer') }}</th>
                            <th style="text-align:right;">{{ __('Refund') }}</th>
                            <th>{{ __('Proceeds') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th style="text-align:right;">{{ __('Action') }}</th>
                        </tr></thead>
                        <tbody>
                            @forelse($refunds as $order)
                                @php
                                    $amount = (float) ($order->refund_amount ?: $order->transaction_amount);
                                    $rBadge = [
                                        REFUND_STATUS_REQUESTED  => ['is-pending', __('Awaiting approval')],
                                        REFUND_STATUS_PROCESSING => ['is-grey', __('Payout in flight')],
                                        REFUND_STATUS_FAILED     => ['is-danger', __('Payout failed')],
                                    ][$order->refund_status] ?? ['is-grey', $order->refund_status];
                                    $released = $order->settlement_status === SETTLEMENT_STATUS_RELEASED;
                                @endphp
                                <tr>
                                    <td style="font-weight:600;color:#0F3C7A;">#{{ $order->order_id }}</td>
                                    <td>
                                        {{ optional($order->user)->name ?: '—' }}
                                        <span style="display:block;color:#9ca3af;font-size:11.5px;">{{ optional($order->user)->contact_number ?: __('no phone on file') }}</span>
                                    </td>
                                    <td style="text-align:right;font-variant-numeric:tabular-nums;">KES {{ number_format($amount, 2) }}</td>
                                    <td>
                                        <span class="cs-badge {{ $released ? 'is-paid' : 'is-grey' }}">
                                            {{ $released ? __('Released to owner') : __('Held (not released)') }}
                                        </span>
                                    </td>
                                    <td><span class="cs-badge {{ $rBadge[0] }}">{{ $rBadge[1] }}</span></td>
                                    <td style="text-align:right;">
                                        @if(in_array($order->refund_status, [REFUND_STATUS_REQUESTED, REFUND_STATUS_FAILED], true))
                                            <form method="POST" action="{{ route('admin.marketplace.refunds.approve', $order->id) }}" style="display:inline;">
                                                @csrf
                                                <button type="submit" class="cs-btn cs-btn--primary cs-btn--sm"
                                                    data-cs-confirm="{{ __('Send an M-Pesa refund of KES :amt to :buyer for order #:id? This moves real money.', ['amt' => number_format($amount, 2), 'buyer' => optional($order->user)->name ?: __('the buyer'), 'id' => $order->order_id]) }}">
                                                    {{ $order->refund_status === REFUND_STATUS_FAILED ? __('Retry refund') : __('Approve & Refund') }}
                                                </button>
                                            </form>
                                        @else
                                            <span style="color:#9ca3af;font-size:12px;">{{ __('awaiting M-Pesa') }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="cs-empty" style="text-align:center;padding:26px;color:#9ca3af;">{{ __('No refunds awaiting action.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($refunds->hasPages())
                    <div style="margin-top:16px;">{!! $refunds->links() !!}</div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
