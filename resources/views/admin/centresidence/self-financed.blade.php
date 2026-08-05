@extends('admin.layouts.app')

@section('content')
<div class="main-content"><div class="page-content"><div class="container-fluid">
    <div class="page-content-wrapper bg-white p-30 radius-20">
        @include('admin.centresidence._nav', ['active' => 'self-financed'])
        <p class="cs-muted" style="margin-bottom:18px;">
            {{ __('Owners funding module deployments themselves (no finance partner, no rent deduction).') }}
        </p>
        <div class="cs-card">
            <div class="cs-card__head"><h2 class="cs-card__title">{{ __('Self-financed modules') }}</h2></div>
            <div class="cs-tablewrap">
                <table class="cs-table">
                    <thead><tr>
                        <th>{{ __('Reference') }}</th><th>{{ __('Owner') }}</th><th>{{ __('Property') }}</th>
                        <th>{{ __('Module') }}</th><th>{{ __('Qty') }}</th><th>{{ __('Hardware') }}</th>
                        <th>{{ __('Installation') }}</th><th>{{ __('Total') }}</th><th>{{ __('Status') }}</th><th></th>
                    </tr></thead>
                    <tbody>
                        @forelse ($orders as $o)
                            <tr>
                                <td>{{ $o->reference ?? ('#' . $o->id) }}</td>
                                <td>{{ optional($o->owner)->name ?? '—' }}</td>
                                <td>{{ optional($o->property)->name ?? ('#' . $o->property_id) }}</td>
                                <td>{{ optional($o->module)->name ?? '—' }}</td>
                                <td>{{ $o->quantity }}</td>
                                <td>KES {{ number_format($o->hardware_cost, 2) }}</td>
                                <td>KES {{ number_format($o->installation_cost, 2) }}</td>
                                <td class="cs-amt">KES {{ number_format($o->total_cost, 2) }}</td>
                                <td>@include('admin.centresidence._status', ['status' => $o->status])</td>
                                <td>
                                    @if ($o->status !== 'deployed')
                                        <a class="cs-btn cs-btn--ghost cs-btn--sm" href="{{ route('admin.centresidence.deploy', ['property_id' => $o->property_id, 'module_id' => $o->module_id, 'quantity' => $o->quantity, 'self_financed_id' => $o->id]) }}">{{ __('Deploy') }}</a>
                                    @else
                                        <span class="cs-muted">{{ __('Deployed') }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="10" class="cs-empty">{{ __('No self-financed orders yet') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if (method_exists($orders, 'links')) <div class="cs-card__body">{!! $orders->links() !!}</div> @endif
        </div>
    </div>
</div></div></div>
@endsection
