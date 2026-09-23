@extends('admin.layouts.app')

@section('content')
<div class="main-content"><div class="page-content"><div class="container-fluid">
    <div class="page-content-wrapper bg-white p-30 radius-20">
        @include('admin.centresidence._nav', ['active' => 'field-studies'])

        @if (session('success')) <div class="cs-alert is-success">{{ session('success') }}</div> @endif
        @if (session('error')) <div class="cs-alert is-danger">{{ session('error') }}</div> @endif

        <div class="cs-card">
            <div class="cs-card__head"><h2 class="cs-card__title">{{ __('Site-survey requests') }}</h2></div>
            <p class="cs-muted" style="padding:0 16px;">{{ __('Custom installs (e.g. reticulated gas) that need a per-property survey before a quote. Record the installer quotation and the owner can take it into financing.') }}</p>
            <div class="cs-tablewrap">
                <table class="cs-table">
                    <thead><tr>
                        <th>{{ __('Owner') }}</th><th>{{ __('Installation') }}</th><th>{{ __('Property') }}</th>
                        <th>{{ __('Note') }}</th><th>{{ __('Status') }}</th><th>{{ __('Quote') }}</th><th></th>
                    </tr></thead>
                    <tbody>
                        @forelse ($requests as $r)
                            <tr>
                                <td>{{ optional($r->owner)->name ?? ('#' . $r->owner_id) }}</td>
                                <td>{{ optional($r->module)->name }}</td>
                                <td>{{ optional($r->property)->name ?? '—' }}@if ($r->units) <span class="cs-muted">· {{ $r->units }} {{ __('units') }}</span>@endif</td>
                                <td>{{ $r->note ?: '—' }}</td>
                                <td>{{ __(ucfirst($r->status)) }}</td>
                                <td>{{ $r->quoted_amount !== null ? 'KES ' . number_format((float) $r->quoted_amount, 2) : '—' }}</td>
                                <td style="text-align:right;">
                                    @if (! in_array($r->status, ['applied', 'cancelled'], true))
                                        <details>
                                            <summary style="cursor:pointer;color:#185FA5;font-weight:600;">{{ $r->quoted_amount !== null ? __('Update quote') : __('Record quote') }}</summary>
                                            <form method="POST" action="{{ route('admin.centresidence.field-studies.quote', $r->id) }}" style="margin-top:8px;display:flex;flex-direction:column;gap:6px;max-width:320px;text-align:left;">
                                                @csrf
                                                <label class="cs-label" style="font-size:12px;">{{ __('Quoted amount (KES)') }}</label>
                                                <input type="number" step="0.01" min="0.01" name="quoted_amount" class="cs-input" required value="{{ $r->quoted_amount }}">
                                                <label class="cs-label" style="font-size:12px;">{{ __('Note (optional)') }}</label>
                                                <input type="text" name="quote_note" class="cs-input" maxlength="1000" value="{{ $r->quote_note }}">
                                                <button type="submit" class="cs-btn cs-btn--primary cs-btn--sm">{{ __('Save quote & notify owner') }}</button>
                                            </form>
                                        </details>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="cs-muted" style="text-align:center;padding:24px;">{{ __('No site-survey requests yet.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if (! empty($requests) && method_exists($requests, 'links'))
            <div style="margin-top:16px;">{{ $requests->links() }}</div>
        @endif
    </div>
</div></div></div>
@endsection
