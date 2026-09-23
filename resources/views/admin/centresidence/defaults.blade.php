@extends('admin.layouts.app')

@section('content')
<div class="main-content"><div class="page-content"><div class="container-fluid">
    <div class="page-content-wrapper bg-white p-30 radius-20">
        @include('admin.centresidence._nav', ['active' => 'defaults'])
        <div class="cs-card">
            <div class="cs-card__head"><h2 class="cs-card__title">{{ __('Defaults & collections') }}</h2></div>
            <div class="cs-tablewrap">
                <table class="cs-table">
                    <thead><tr>
                        <th>{{ __('Facility') }}</th><th>{{ __('Owner') }}</th><th>{{ __('Defaulted') }}</th>
                        <th>{{ __('Days past due') }}</th><th>{{ __('Total outstanding') }}</th>
                        <th>{{ __('Collections') }}</th><th>{{ __('Resolution') }}</th>
                    </tr></thead>
                    <tbody>
                        @forelse ($defaults as $d)
                            <tr>
                                <td>{{ optional($d->facility)->facility_number ?? ('#' . $d->finance_facility_id) }}</td>
                                <td>{{ optional(optional($d->facility)->owner)->name ?? '—' }}</td>
                                <td>{{ optional($d->defaulted_at)->format('Y-m-d') ?? '—' }}</td>
                                <td>{{ $d->days_past_due_at_default }}</td>
                                <td class="cs-amt">KES {{ number_format($d->total_outstanding_at_default, 2) }}</td>
                                <td>@include('admin.centresidence._status', ['status' => $d->collections_status])</td>
                                <td>{{ $d->resolution_type ? __(ucfirst(str_replace('_', ' ', $d->resolution_type))) : __('Open') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="cs-empty">{{ __('No defaults — healthy portfolio') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if (method_exists($defaults, 'links')) <div class="cs-card__body">{!! $defaults->links() !!}</div> @endif
        </div>
    </div>
</div></div></div>
@endsection
