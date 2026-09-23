@extends('admin.layouts.app')

@section('content')
<div class="main-content"><div class="page-content"><div class="container-fluid">
    <div class="page-content-wrapper bg-white p-30 radius-20">
        @include('admin.centresidence._nav', ['active' => 'revenue'])

        <div class="cs-card">
            <div class="cs-card__head"><h2 class="cs-card__title">{{ __('Commission invoices (subscription owners)') }}</h2></div>
            <div class="cs-tablewrap">
                <table class="cs-table">
                    <thead><tr>
                        <th>{{ __('Owner') }}</th><th>{{ __('Month') }}</th><th>{{ __('Subscription') }}</th>
                        <th>{{ __('Metered') }}</th><th>{{ __('Non-metered') }}</th><th>{{ __('Total') }}</th>
                        <th>{{ __('Fallback') }}</th><th>{{ __('Status') }}</th>
                    </tr></thead>
                    <tbody>
                        @forelse ($commissionInvoices as $c)
                            <tr>
                                <td>{{ optional($c->owner)->name ?? '—' }}</td>
                                <td>{{ optional($c->billing_month)->format('M Y') }}</td>
                                <td>KES {{ number_format($c->subscription_amount, 2) }}</td>
                                <td>KES {{ number_format($c->metered_commission_total, 2) }}</td>
                                <td>KES {{ number_format($c->non_metered_commission_total, 2) }}</td>
                                <td class="cs-amt">KES {{ number_format($c->total_amount, 2) }}</td>
                                <td>
                                    @if ($c->fallback_deduction_active)
                                        <span class="cs-badge is-danger">{{ __('Active') }}</span>
                                    @elseif ($c->fallback_metered_cleared_at)
                                        <span class="cs-badge is-paid">{{ __('Cleared') }}</span>
                                    @else — @endif
                                </td>
                                <td>@include('admin.centresidence._status', ['status' => $c->status])</td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="cs-empty">{{ __('No commission invoices yet') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if (method_exists($commissionInvoices, 'links')) <div class="cs-card__body">{!! $commissionInvoices->links() !!}</div> @endif
        </div>

        <div class="cs-card">
            <div class="cs-card__head"><h2 class="cs-card__title">{{ __('Infrastructure invoices (transaction owners — non-metered)') }}</h2></div>
            <div class="cs-tablewrap">
                <table class="cs-table">
                    <thead><tr><th>{{ __('Owner') }}</th><th>{{ __('Month') }}</th><th>{{ __('Total') }}</th><th>{{ __('Status') }}</th></tr></thead>
                    <tbody>
                        @forelse ($infraInvoices as $i)
                            <tr>
                                <td>{{ optional($i->owner)->name ?? '—' }}</td>
                                <td>{{ optional($i->billing_month)->format('M Y') }}</td>
                                <td class="cs-amt">KES {{ number_format($i->total_amount, 2) }}</td>
                                <td>@include('admin.centresidence._status', ['status' => $i->status])</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="cs-empty">{{ __('No infrastructure invoices yet') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div></div></div>
@endsection
