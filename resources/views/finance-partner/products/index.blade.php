@extends('finance-partner.layouts.app')

@section('content')
    <div class="cs-titlebar">
        <h1 class="cs-title">{{ __('My Products') }}</h1>
        <a href="{{ route('finance-partner.products.create') }}" class="cs-btn cs-btn--purple">{{ __('+ New product') }}</a>
    </div>

    <div class="cs-card">
        <div class="cs-tablewrap">
            <table class="cs-table">
                <thead><tr>
                    <th>{{ __('Product') }}</th><th>{{ __('Module') }}</th><th>{{ __('Interest') }}</th>
                    <th>{{ __('Tenor') }}</th><th>{{ __('Amount range') }}</th><th>{{ __('Status') }}</th><th></th>
                </tr></thead>
                <tbody>
                    @forelse ($products as $p)
                        <tr>
                            <td style="font-weight:600;color:var(--blue);">{{ $p->product_name }}</td>
                            <td>{{ optional($p->module)->name ?? '—' }}</td>
                            <td>{{ number_format($p->interest_rate, 2) }}% {{ str_replace('_', ' ', $p->interest_rate_type) }}</td>
                            <td>{{ $p->min_repayment_months }}–{{ $p->max_repayment_months }} {{ __('mo') }}</td>
                            <td>KES {{ number_format($p->min_amount, 0) }} – {{ number_format($p->max_amount, 0) }}</td>
                            <td>@include('admin.centresidence._status', ['status' => $p->status])</td>
                            <td><a href="{{ route('finance-partner.products.edit', $p->id) }}" class="cs-btn cs-btn--ghost cs-btn--sm">{{ __('Edit') }}</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="cs-empty">{{ __('No products yet — publish one so owners can apply.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
