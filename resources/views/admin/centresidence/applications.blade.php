@extends('admin.layouts.app')

@section('content')
<div class="main-content"><div class="page-content"><div class="container-fluid">
    <div class="page-content-wrapper bg-white p-30 radius-20">
        @include('admin.centresidence._nav', ['active' => 'applications'])
        <div class="cs-card">
            <div class="cs-card__head"><h2 class="cs-card__title">{{ __('Finance applications') }}</h2></div>
            <div class="cs-tablewrap">
                <table class="cs-table">
                    <thead><tr>
                        <th>{{ __('Reference') }}</th><th>{{ __('Owner') }}</th><th>{{ __('Partner') }}</th>
                        <th>{{ __('Module') }}</th><th>{{ __('Qty') }}</th><th>{{ __('Requested') }}</th>
                        <th>{{ __('Approved') }}</th><th>{{ __('Status') }}</th>
                    </tr></thead>
                    <tbody>
                        @forelse ($applications as $a)
                            <tr>
                                <td>{{ $a->application_number ?? ('#' . $a->id) }}</td>
                                <td>{{ optional($a->owner)->name ?? '—' }}</td>
                                <td>{{ optional($a->partner)->company_name ?? '—' }}</td>
                                <td>{{ optional($a->module)->name ?? '—' }}</td>
                                <td>{{ $a->quantity }}</td>
                                <td class="cs-amt">KES {{ number_format($a->requested_amount, 2) }}</td>
                                <td>{{ $a->approved_amount ? 'KES ' . number_format($a->approved_amount, 2) : '—' }}</td>
                                <td>@include('admin.centresidence._status', ['status' => $a->status])</td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="cs-empty">{{ __('No applications yet') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if (method_exists($applications, 'links')) <div class="cs-card__body">{!! $applications->links() !!}</div> @endif
        </div>
    </div>
</div></div></div>
@endsection
