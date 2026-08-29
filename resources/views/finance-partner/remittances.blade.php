@extends('finance-partner.layouts.app')

@section('content')
    <div class="cs-titlebar"><h1 class="cs-title">{{ __('Remittances') }}</h1></div>

    {{-- Flash alerts are rendered once by the layout; no per-page duplicate. --}}

    <div class="cs-card">
        <div class="cs-card__body cs-muted" style="font-size:13px;">
            {{ __('Collected repayments Centresidence settles to you. When a bank remittance is marked sent, confirm receipt so it’s reconciled.') }}
        </div>
        <div class="cs-tablewrap">
            <table class="cs-table">
                <thead><tr>
                    <th>{{ __('Batch') }}</th><th>{{ __('Date') }}</th><th>{{ __('Amount') }}</th>
                    <th>{{ __('Facilities') }}</th><th>{{ __('Method') }}</th><th>{{ __('Reference') }}</th><th>{{ __('Status') }}</th><th></th>
                </tr></thead>
                <tbody>
                    @forelse ($batches as $b)
                        <tr>
                            <td>{{ $b->batch_number ?? ('#' . $b->id) }}</td>
                            <td>{{ optional($b->remittance_date)->format('Y-m-d') ?? optional($b->created_at)->format('Y-m-d') }}</td>
                            <td class="cs-amt">KES {{ number_format($b->total_amount, 2) }}</td>
                            <td>
                                <button type="button" class="rm-toggle" data-target="rmd-{{ $b->id }}" aria-expanded="false">
                                    <span>{{ $b->facility_count ?? $b->items->count() }}</span>
                                    <svg class="rm-chev" width="12" height="12" viewBox="0 0 24 24" fill="none"><path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </button>
                            </td>
                            <td>{{ str_replace('_', ' ', $b->settlement_method ?? '—') }}</td>
                            <td style="font-family:monospace;font-size:12px;">{{ $b->reference ?? '—' }}</td>
                            <td>
                                @php $sb = ['prepared' => 'is-grey', 'sent' => 'is-pending', 'confirmed' => 'is-paid', 'failed' => 'is-danger'][$b->status] ?? 'is-grey'; @endphp
                                <span class="cs-badge {{ $sb }}">{{ ucfirst($b->status) }}</span>
                            </td>
                            <td>
                                @if ($b->status === 'sent')
                                    <form method="POST" action="{{ route('finance-partner.remittances.confirm', $b->id) }}"
                                          data-cs-confirm="{{ __('Confirm you received this remittance of KES :amt?', ['amt' => number_format($b->total_amount, 2)]) }}" data-cs-confirm-title="{{ __('Confirm receipt?') }}" data-cs-confirm-ok="{{ __('Yes, received') }}">
                                        @csrf
                                        <button class="cs-btn cs-btn--pending cs-btn--sm" type="submit">{{ __('Confirm receipt') }}</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                        <tr id="rmd-{{ $b->id }}" class="rm-detail" hidden>
                            <td colspan="8" style="background:#fff;padding:0 12px 12px;">@include('centresidence._remittance-items', ['batch' => $b])</td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="cs-empty">{{ __('No remittances yet') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if (method_exists($batches, 'links')) <div class="cs-card__body">{!! $batches->links() !!}</div> @endif
    </div>

    @include('centresidence._remittance-toggle')
@endsection
