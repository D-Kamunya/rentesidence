@extends('admin.layouts.app')

@section('content')
<div class="main-content"><div class="page-content"><div class="container-fluid">
    <div class="page-content-wrapper bg-white p-30 radius-20">
        @include('admin.centresidence._nav', ['active' => 'facilities'])
        <div class="cs-card">
            <div class="cs-card__head"><h2 class="cs-card__title">{{ __('Finance facilities') }}</h2></div>
            <div class="cs-tablewrap">
                <table class="cs-table">
                    <thead><tr>
                        <th>{{ __('Facility') }}</th><th>{{ __('Owner') }}</th><th>{{ __('Partner') }}</th>
                        <th>{{ __('Disbursed') }}</th><th>{{ __('Down-pmt') }}</th><th>{{ __('Out. principal') }}</th><th>{{ __('Out. interest') }}</th>
                        <th>{{ __('Monthly') }}</th><th>{{ __('Mode') }}</th><th>{{ __('Status') }}</th><th></th>
                    </tr></thead>
                    <tbody>
                        @forelse ($facilities as $f)
                            <tr>
                                <td>{{ $f->facility_number ?? ('#' . $f->id) }}</td>
                                <td>{{ optional($f->owner)->name ?? '—' }}</td>
                                <td>{{ optional($f->partner)->company_name ?? '—' }}</td>
                                <td class="cs-amt">KES {{ number_format($f->disbursed_amount, 2) }}</td>
                                <td>
                                    @if (($f->down_payment_status ?? 'not_required') === 'not_required')
                                        <span class="cs-muted">—</span>
                                    @else
                                        <span class="cs-badge {{ $f->down_payment_status === 'collected' ? 'is-paid' : ($f->down_payment_status === 'failed' ? 'is-danger' : 'is-pending') }}">
                                            KES {{ number_format($f->owner_contribution, 0) }} · {{ ucfirst($f->down_payment_status) }}
                                        </span>
                                    @endif
                                </td>
                                <td class="cs-amt">KES {{ number_format($f->outstanding_principal, 2) }}</td>
                                <td>KES {{ number_format($f->outstanding_interest, 2) }}</td>
                                <td>KES {{ number_format($f->monthly_target, 2) }}</td>
                                <td>
                                    <span class="cs-badge {{ $f->accelerated_repayment ? 'is-purple' : 'is-grey' }}">
                                        {{ $f->accelerated_repayment ? __('Accelerated') : __('Standard') }}
                                    </span>
                                </td>
                                <td>@include('admin.centresidence._status', ['status' => $f->status])</td>
                                <td>
                                    <a class="cs-btn cs-btn--ghost cs-btn--sm" href="{{ route('admin.centresidence.deploy', ['property_id' => $f->property_id, 'module_id' => $f->module_id]) }}">{{ __('Deploy') }}</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="10" class="cs-empty">{{ __('No facilities yet') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if (method_exists($facilities, 'links')) <div class="cs-card__body">{!! $facilities->links() !!}</div> @endif
        </div>
    </div>
</div></div></div>
@endsection
