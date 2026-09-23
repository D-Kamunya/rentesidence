@extends('owner.layouts.app')

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="page-content-wrapper bg-white p-30 radius-20">
                <div class="container" style="max-width:820px;">

                    <div class="ag-header mb-4">
                        <div>
                            <h2 class="ag-title">{{ $agreement->title }}</h2>
                            <p class="ag-sub">{{ __('To') }} {{ optional($agreement->tenant)->name }} &middot;
                                @include('agreement.partials.status-badge', ['status' => $agreement->status])</p>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('owner.agreement.index') }}" class="ag-btn ag-btn--ghost">{{ __('Back') }}</a>
                            @if ($agreement->source === 'upload')
                                <a href="{{ route('owner.agreement.document', $agreement->id) }}" target="_blank" class="ag-btn ag-btn--ghost">{{ __('Agreement') }}</a>
                                @if ($agreement->status === 'signed' && $agreement->signed_file_id)
                                    <a href="{{ route('owner.agreement.download', $agreement->id) }}" class="ag-btn ag-btn--primary">{{ __('Certificate') }}</a>
                                @endif
                            @elseif ($agreement->status === 'signed' && $agreement->signed_file_id)
                                <a href="{{ route('owner.agreement.download', $agreement->id) }}" class="ag-btn ag-btn--primary">{{ __('Download') }}</a>
                            @endif
                        </div>
                    </div>

                    {{-- Document --}}
                    <div class="ag-doc mb-4">
                        @if ($agreement->source === 'upload')
                            <iframe class="ag-doc__frame" src="{{ route('owner.agreement.document', $agreement->id) }}#toolbar=1"></iframe>
                        @else
                            <iframe class="ag-doc__frame" sandbox
                                srcdoc="{{ '<!doctype html><meta charset=utf-8><style>body{font-family:system-ui,Segoe UI,Roboto,sans-serif;color:#1f2937;font-size:14px;line-height:1.6;padding:22px;margin:0}h2{font-size:18px}h3{font-size:15px}</style>' . $agreement->body }}"></iframe>
                        @endif
                    </div>

                    {{-- Audit trail --}}
                    <div class="ag-card">
                        <div style="padding:.75rem 1.1rem;border-bottom:0.5px solid #e5e7eb;background:#fafafa;font-weight:500;font-size:14px;">{{ __('Audit trail') }}</div>
                        <div class="table-responsive">
                            <table class="table align-middle mb-0 ag-table">
                                <thead><tr><th>{{ __('Event') }}</th><th>{{ __('When') }}</th><th>{{ __('IP') }}</th></tr></thead>
                                <tbody>
                                    @forelse ($agreement->events as $ev)
                                        <tr>
                                            <td>{{ ucfirst(str_replace('_', ' ', $ev->event)) }}</td>
                                            <td class="ag-muted">{{ optional($ev->created_at)->format('d M Y, g:i A') }}</td>
                                            <td class="ag-muted">{{ $ev->ip_address ?: '—' }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="3" class="ag-empty">{{ __('No activity yet.') }}</td></tr>
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
    <style>
        .ag-doc { border:0.5px solid #e5e7eb; border-radius:12px; overflow:hidden; background:#fff; }
        .ag-doc__frame { width:100%; height:380px; border:none; display:block; background:#fff; }
    </style>
@endpush
