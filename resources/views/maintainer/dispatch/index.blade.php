@extends('maintainer.layouts.app')

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="page-content-wrapper bg-white p-30 radius-20">

                <div class="dp-head">
                    <h2 class="dp-title">{{ __('Dispatch') }}</h2>
                    <p class="dp-sub">{{ __('Paid orders from tenants on your properties. Dispatch them, then mark delivered once the tenant has them.') }}</p>
                </div>

                @if (session('success'))<div class="dp-flash dp-flash--ok">{{ session('success') }}</div>@endif
                @if (session('error'))<div class="dp-flash dp-flash--err">{{ session('error') }}</div>@endif

                @if (! $enabled)
                    <div class="dp-off">
                        <i class="ri-information-line"></i>
                        <div>
                            <strong>{{ __('Dispatch is handled by the owner') }}</strong>
                            <span>{{ __('The property owner has chosen to organise dispatch themselves. If that changes, orders will appear here.') }}</span>
                        </div>
                    </div>
                @elseif ($orders->count())
                    <div class="table-responsive">
                        <table class="dp-tbl">
                            <thead>
                                <tr>
                                    <th>{{ __('Order') }}</th>
                                    <th>{{ __('Tenant') }}</th>
                                    <th>{{ __('Items') }}</th>
                                    <th class="dp-right">{{ __('Amount') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th class="dp-right">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                            @foreach ($orders as $order)
                                <tr>
                                    <td><span class="dp-mono">#{{ $order->order_id }}</span></td>
                                    <td>{{ optional($order->user)->first_name }} {{ optional($order->user)->last_name }}</td>
                                    <td>{{ $order->orderItems->map(fn($i) => optional($i->product)->name)->filter()->take(3)->implode(', ') ?: '—' }}</td>
                                    <td class="dp-right">{{ currencyPrice($order->transaction_amount ?: $order->total) }}</td>
                                    <td>
                                        @if ((int) $order->fulfilment_status === FULFILMENT_DISPATCHED)
                                            <span class="dp-badge dp-badge--out">{{ __('On the way') }}</span>
                                        @else
                                            <span class="dp-badge dp-badge--new">{{ __('Awaiting dispatch') }}</span>
                                        @endif
                                    </td>
                                    <td class="dp-right">
                                        @if ((int) $order->fulfilment_status === FULFILMENT_DISPATCHED)
                                            <form action="{{ route('maintainer.dispatch.deliver', $order->id) }}" method="POST" class="d-inline"
                                                  data-cs-confirm="{{ __('Mark this order as delivered to the tenant?') }}" data-cs-confirm-ok="{{ __('Mark delivered') }}">
                                                @csrf
                                                <button type="submit" class="dp-btn dp-btn--green">{{ __('Mark delivered') }}</button>
                                            </form>
                                        @else
                                            <form action="{{ route('maintainer.dispatch.dispatch', $order->id) }}" method="POST" class="d-inline"
                                                  data-cs-confirm="{{ __('Mark this order as dispatched (out for delivery)?') }}" data-cs-confirm-ok="{{ __('Dispatch') }}">
                                                @csrf
                                                <button type="submit" class="dp-btn">{{ __('Dispatch') }}</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">{{ $orders->links() }}</div>
                @else
                    <div class="dp-empty">
                        <i class="ri-inbox-line"></i>
                        <h3>{{ __('Nothing to dispatch') }}</h3>
                        <p>{{ __('When a tenant on your properties pays for an order, it\'ll show up here to dispatch.') }}</p>
                    </div>
                @endif

            </div>
        </div>
    </div>
</div>

<style>
    .dp-head { margin-bottom:18px; }
    .dp-title { font-size:22px; font-weight:600; color:#111827; margin:0 0 5px; }
    .dp-sub { font-size:13.5px; color:#6b7280; margin:0; max-width:64ch; line-height:1.6; }
    .dp-flash { padding:11px 15px; border-radius:10px; font-size:13.5px; margin-bottom:16px; }
    .dp-flash--ok { background:#E1F5EE; color:#0F6E56; border:0.5px solid #A7DFC9; }
    .dp-flash--err { background:#FBE9E7; color:#B42318; border:0.5px solid #F3C4BC; }
    .dp-off { display:flex; gap:12px; align-items:flex-start; background:#F5F9FD; border:0.5px solid #d7e3f2; border-radius:12px; padding:16px 18px; }
    .dp-off i { font-size:22px; color:#185FA5; flex:none; }
    .dp-off strong { display:block; font-size:14px; color:#111827; }
    .dp-off span { font-size:12.5px; color:#6b7280; line-height:1.5; }
    .dp-tbl { width:100%; border-collapse:collapse; font-size:13px; }
    .dp-tbl th { text-align:left; font-size:10.5px; text-transform:uppercase; letter-spacing:.05em; color:#9ca3af; font-weight:600; padding:10px 12px; border-bottom:0.5px solid #e5e7eb; }
    .dp-tbl td { padding:12px; border-bottom:0.5px solid #f1f5f9; color:#374151; }
    .dp-right, .dp-tbl th.dp-right, .dp-tbl td.dp-right { text-align:right; }
    .dp-mono { font-family:ui-monospace,Menlo,monospace; font-size:12.5px; color:#111827; }
    .dp-badge { font-size:11px; font-weight:600; padding:3px 10px; border-radius:99px; }
    .dp-badge--new { background:#FEF3E7; color:#B45309; }
    .dp-badge--out { background:#E6F1FB; color:#185FA5; }
    .dp-btn { background:#185FA5; color:#fff; border:none; border-radius:8px; font-size:12.5px; font-weight:600; padding:8px 16px; cursor:pointer; }
    .dp-btn:hover { background:#0F4A84; }
    .dp-btn--green { background:#0F6E56; }
    .dp-btn--green:hover { background:#0B5744; }
    .dp-empty { text-align:center; padding:48px 20px; color:#6b7280; }
    .dp-empty i { font-size:44px; color:#cbd5e1; }
    .dp-empty h3 { font-size:17px; color:#111827; margin:12px 0 6px; }
</style>
@endsection
