@extends('tenant.layouts.app')

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="page-content-wrapper bg-white p-30 radius-20">
                <div class="container">

                    <div class="ag-header mb-4">
                        <div>
                            <h2 class="ag-title">{{ $pageTitle }}</h2>
                            <p class="ag-sub">{{ __('Agreements from your landlord to review and sign.') }}</p>
                        </div>
                        <a href="{{ route('agreement.verify') }}" target="_blank" class="ag-link" style="font-size:12.5px;display:inline-flex;align-items:center;gap:5px;">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none"><path d="M12 2l7 3v6c0 4.5-3 8-7 9-4-1-7-4.5-7-9V5l7-3z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><path d="M9 12l2 2 4-4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            {{ __('Verify a certificate') }}
                        </a>
                    </div>

                    <div class="ag-card">
                        <div class="table-responsive">
                            <table class="table align-middle mb-0 ag-table">
                                <thead>
                                    <tr>
                                        <th>{{ __('Agreement') }}</th>
                                        <th>{{ __('From') }}</th>
                                        <th>{{ __('Received') }}</th>
                                        <th>{{ __('Status') }}</th>
                                        <th style="text-align:right;">{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($agreements as $a)
                                        <tr>
                                            <td>{{ $a->title }}</td>
                                            <td class="ag-muted">{{ optional($a->owner)->name ?? '—' }}</td>
                                            <td class="ag-muted">{{ optional($a->sent_at)->format('d M Y') }}</td>
                                            <td>@include('agreement.partials.status-badge', ['status' => $a->status])</td>
                                            <td style="text-align:right;">
                                                @if ($a->status === 'signed')
                                                    @if ($a->source === 'upload')
                                                        <a href="{{ route('tenant.agreement.document', $a->id) }}" target="_blank" class="ag-link">{{ __('Agreement') }}</a>
                                                        @if ($a->signed_file_id)
                                                            &middot; <a href="{{ route('tenant.agreement.download', $a->id) }}" class="ag-link">{{ __('Certificate') }}</a>
                                                        @endif
                                                    @elseif ($a->signed_file_id)
                                                        <a href="{{ route('tenant.agreement.download', $a->id) }}" class="ag-link">{{ __('Download') }}</a>
                                                    @else
                                                        <span class="ag-muted">{{ __('Signed') }}</span>
                                                    @endif
                                                @elseif ($a->status === 'declined')
                                                    <span class="ag-muted">{{ __('Declined') }}</span>
                                                @else
                                                    <a href="{{ route('tenant.agreement.show', $a->id) }}" class="ag-link">{{ __('Review & Sign') }}</a>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="5" class="ag-empty">{{ __('No agreements yet.') }}</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('style')
    @include('agreement.partials.styles')
@endpush
