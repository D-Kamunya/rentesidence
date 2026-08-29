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
                        <th>{{ __('Facility') }}</th><th>{{ __('Owner') }}</th><th>{{ __('Property') }}</th><th>{{ __('Partner') }}</th>
                        <th>{{ __('Disbursed') }}</th><th>{{ __('Down-pmt') }}</th><th>{{ __('Out. principal') }}</th><th>{{ __('Out. interest') }}</th>
                        <th>{{ __('Monthly') }}</th><th>{{ __('Mode') }}</th><th>{{ __('Status') }}</th><th>{{ __('Setup') }}</th>
                    </tr></thead>
                    <tbody>
                        @forelse ($facilities as $f)
                            <tr>
                                <td>
                                    {{ $f->facility_number ?? ('#' . $f->id) }}
                                    @php $costOfFinance = (float) ($f->total_repayable ?? 0) - (float) $f->principal_amount; @endphp
                                    <div class="cs-muted" style="font-size:11px;margin-top:4px;line-height:1.5;white-space:nowrap;">
                                        {{ __('Payable') }} <strong>KES {{ number_format($f->total_repayable ?? $f->principal_amount, 0) }}</strong>
                                        @if ($costOfFinance > 0)<span title="{{ __('Cost of finance — interest over the term') }}">(+{{ number_format($costOfFinance, 0) }} {{ __('int.') }})</span>@endif
                                    </div>
                                </td>
                                <td>{{ optional($f->owner)->name ?? '—' }}</td>
                                <td>{{ optional($f->property)->name ?? '—' }}</td>
                                <td>{{ optional($f->partner)->company_name ?? '—' }}</td>
                                @php
                                    $ds = $f->disbursement_status ?? 'disbursed';
                                    $dsBadge = $ds === 'disbursed' ? 'is-paid' : ($ds === 'pending_confirmation' ? 'is-pending' : 'is-grey');
                                    $dsLabel = $ds === 'disbursed' ? __('Disbursed') : ($ds === 'pending_confirmation' ? __('Pending confirm') : __('Awaiting'));
                                @endphp
                                <td>
                                    <div class="cs-amt">KES {{ number_format($f->disbursed_amount, 2) }}</div>
                                    <span class="cs-badge {{ $dsBadge }}" style="margin-top:3px;white-space:nowrap;">{{ $dsLabel }}@if ($f->disbursement_channel && $ds !== 'awaiting') · {{ ucfirst($f->disbursement_channel) }}@endif</span>
                                </td>
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
                                    @if ($ds !== 'disbursed')
                                        @if ($ds === 'pending_confirmation')
                                            <form method="POST" action="{{ route('admin.centresidence.facilities.confirm-disbursement', $f->id) }}" style="margin-bottom:6px;"
                                                  data-cs-confirm="{{ __('Confirm these funds were received? This releases the facility and repayment begins.') }}" data-cs-confirm-title="{{ __('Confirm disbursement?') }}" data-cs-confirm-ok="{{ __('Yes, confirm') }}">
                                                @csrf
                                                <button class="cs-btn cs-btn--pending cs-btn--sm" type="submit">{{ __('Confirm receipt') }}</button>
                                            </form>
                                        @endif
                                        <form method="POST" action="{{ route('admin.centresidence.facilities.force-disburse', $f->id) }}" style="display:flex;gap:4px;flex-wrap:wrap;align-items:center;margin-bottom:6px;"
                                              data-cs-confirm="{{ __('Manually mark this facility disbursed (funds released outside the system)? Repayment will begin.') }}" data-cs-confirm-title="{{ __('Mark disbursed?') }}" data-cs-confirm-ok="{{ __('Yes, mark disbursed') }}">
                                            @csrf
                                            <select name="disbursement_channel" class="cs-input cs-input--sm" style="min-width:78px;">
                                                <option value="manual">{{ __('Manual') }}</option>
                                                <option value="mpesa">M-Pesa</option>
                                                <option value="bank">{{ __('Bank') }}</option>
                                            </select>
                                            <input name="disbursement_reference" class="cs-input cs-input--sm" placeholder="{{ __('Ref') }}" style="width:78px;">
                                            <button class="cs-btn cs-btn--ghost cs-btn--sm" type="submit" title="{{ __('Manual lever: record an out-of-system disbursement and release the facility') }}">{{ __('Mark disbursed') }}</button>
                                        </form>
                                    @endif
                                    <a class="cs-btn cs-btn--ghost cs-btn--sm" href="{{ route('admin.centresidence.deploy', ['property_id' => $f->property_id, 'module_id' => $f->module_id]) }}">{{ __('Deploy') }}</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="12" class="cs-empty">{{ __('No facilities yet') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if (method_exists($facilities, 'links')) <div class="cs-card__body">{!! $facilities->links() !!}</div> @endif
        </div>
    </div>
</div></div></div>
@endsection
