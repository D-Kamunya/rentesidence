@extends('finance-partner.layouts.app')

@section('content')
    <div class="cs-titlebar"><h1 class="cs-title">{{ __('Applications') }}</h1></div>

    <div class="cs-card">
        <div class="cs-tablewrap">
            <table class="cs-table">
                <thead><tr>
                    <th>{{ __('Reference') }}</th><th>{{ __('Owner') }}</th><th>{{ __('Property') }}</th>
                    <th>{{ __('Module') }}</th><th>{{ __('Requested') }}</th><th>{{ __('Status') }}</th><th></th>
                </tr></thead>
                <tbody>
                    @forelse ($applications as $a)
                        <tr>
                            <td>{{ $a->application_number ?? ('#' . $a->id) }}</td>
                            <td>{{ optional($a->owner)->name ?? '—' }}</td>
                            <td>{{ optional($a->property)->name ?? ('#' . $a->property_id) }}</td>
                            <td>{{ optional($a->module)->name ?? '—' }}</td>
                            <td class="cs-amt">KES {{ number_format($a->requested_amount, 2) }}</td>
                            <td>@include('admin.centresidence._status', ['status' => $a->status])</td>
                            <td><a href="{{ route('finance-partner.applications.show', $a->id) }}" class="cs-btn cs-btn--primary cs-btn--sm">{{ __('Review') }}</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="cs-empty">{{ __('No applications yet') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if (method_exists($applications, 'links')) <div class="cs-card__body">{!! $applications->links() !!}</div> @endif
    </div>
@endsection
