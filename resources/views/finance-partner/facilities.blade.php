@extends('finance-partner.layouts.app')

@section('content')
    <div class="cs-titlebar"><h1 class="cs-title">{{ __('Facilities') }}</h1></div>

    <div class="cs-card">
        <div class="cs-tablewrap">
            <table class="cs-table">
                <thead><tr>
                    <th>{{ __('Facility') }}</th><th>{{ __('Owner') }}</th><th>{{ __('Disbursed') }}</th>
                    <th>{{ __('Out. principal') }}</th><th>{{ __('Out. interest') }}</th><th>{{ __('Monthly') }}</th><th>{{ __('Status') }}</th><th>{{ __('Disbursement') }}</th>
                </tr></thead>
                <tbody>
                    @forelse ($facilities as $f)
                        <tr>
                            <td>
                                <a href="{{ route('finance-partner.facilities.overview', $f->id) }}" style="color:var(--blue);font-weight:600;">{{ $f->facility_number ?? ('#' . $f->id) }}</a>
                                <div><a href="{{ route('finance-partner.facilities.overview', $f->id) }}" class="cs-muted" style="font-size:11px;">{{ __('Overview →') }}</a></div>
                            </td>
                            <td>{{ optional($f->owner)->name ?? '—' }}</td>
                            <td class="cs-amt">KES {{ number_format($f->disbursed_amount, 2) }}</td>
                            <td class="cs-amt">KES {{ number_format($f->outstanding_principal, 2) }}</td>
                            <td>KES {{ number_format($f->outstanding_interest, 2) }}</td>
                            <td>KES {{ number_format($f->monthly_target, 2) }}</td>
                            <td>@include('admin.centresidence._status', ['status' => $f->status])</td>
                            <td>
                                @php $pds = $f->disbursement_status ?? 'disbursed'; @endphp
                                @if ($pds === 'disbursed')
                                    <span class="cs-badge is-paid">{{ __('Disbursed') }}</span>
                                @elseif ($pds === 'pending_confirmation')
                                    <span class="cs-badge is-pending">{{ __('Awaiting payee confirmation') }}</span>
                                @else
                                    {{-- Disbursement is done from the application (single touch point) --}}
                                    <span class="cs-badge is-grey">{{ __('Awaiting disbursement') }}</span>
                                    @if ($f->finance_application_id)
                                        <div style="margin-top:4px;"><a href="{{ route('finance-partner.applications.show', $f->finance_application_id) }}" style="font-size:11.5px;color:var(--blue);">{{ __('Disburse in the application →') }}</a></div>
                                    @endif
                                @endif
                                @if ($f->early_settlement_status === 'pending')
                                    <form method="POST" action="{{ route('finance-partner.facilities.confirm-settlement', $f->id) }}" style="margin-top:6px;"
                                          data-cs-confirm="{{ __('Confirm you received the owner’s early-settlement payoff? This closes the facility.') }}" data-cs-confirm-title="{{ __('Confirm settlement?') }}" data-cs-confirm-ok="{{ __('Yes, confirm') }}">
                                        @csrf
                                        <button class="cs-btn cs-btn--pending cs-btn--sm" type="submit">{{ __('Confirm settlement received') }}</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="cs-empty">{{ __('No facilities yet') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if (method_exists($facilities, 'links')) <div class="cs-card__body">{!! $facilities->links() !!}</div> @endif
    </div>
@endsection
