@extends('finance-partner.layouts.app')

@section('content')
    <div class="cs-titlebar"><h1 class="cs-title">{{ __('Facilities') }}</h1></div>

    <div class="cs-card">
        <div class="cs-tablewrap">
            <table class="cs-table">
                <thead><tr>
                    <th>{{ __('Facility') }}</th><th>{{ __('Owner') }}</th><th>{{ __('Disbursed') }}</th>
                    <th>{{ __('Out. principal') }}</th><th>{{ __('Out. interest') }}</th><th>{{ __('Monthly') }}</th><th>{{ __('Status') }}</th>
                </tr></thead>
                <tbody>
                    @forelse ($facilities as $f)
                        <tr>
                            <td>{{ $f->facility_number ?? ('#' . $f->id) }}</td>
                            <td>{{ optional($f->owner)->name ?? '—' }}</td>
                            <td class="cs-amt">KES {{ number_format($f->disbursed_amount, 2) }}</td>
                            <td class="cs-amt">KES {{ number_format($f->outstanding_principal, 2) }}</td>
                            <td>KES {{ number_format($f->outstanding_interest, 2) }}</td>
                            <td>KES {{ number_format($f->monthly_target, 2) }}</td>
                            <td>@include('admin.centresidence._status', ['status' => $f->status])</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="cs-empty">{{ __('No facilities yet') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if (method_exists($facilities, 'links')) <div class="cs-card__body">{!! $facilities->links() !!}</div> @endif
    </div>
@endsection
