@extends('admin.layouts.app')

@section('content')
<div class="main-content"><div class="page-content"><div class="container-fluid">
    <div class="page-content-wrapper bg-white p-30 radius-20">
        @include('admin.centresidence._nav', ['active' => 'remittances'])

        @if (session('success')) <div class="cs-alert is-success">{{ session('success') }}</div> @endif
        @if (session('error')) <div class="cs-alert is-danger">{{ session('error') }}</div> @endif

        <div class="cs-card">
            <div class="cs-card__head" style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;">
                <h2 class="cs-card__title">{{ __('Partner remittances') }}</h2>
                @if (count($pendingPartnerIds ?? []))
                    <form method="POST" action="{{ route('admin.centresidence.remittances.prepare') }}"
                          data-cs-confirm="{{ __('Prepare remittance batches for :n partner(s) with collected repayments?', ['n' => count($pendingPartnerIds)]) }}" data-cs-confirm-ok="{{ __('Prepare') }}">
                        @csrf
                        <button class="cs-btn cs-btn--primary cs-btn--sm" type="submit">{{ __('Prepare pending (:n)', ['n' => count($pendingPartnerIds)]) }}</button>
                    </form>
                @endif
            </div>
            <div class="cs-card__body cs-muted" style="font-size:13px;">
                {{ __('Prepare batches from collected repayments, then pay each partner. Automated M-Pesa payouts confirm via callback; bank payouts are marked sent here and the partner confirms receipt.') }}
            </div>
            <div class="cs-tablewrap">
                <table class="cs-table">
                    <thead><tr>
                        <th>{{ __('Batch') }}</th><th>{{ __('Partner') }}</th><th>{{ __('Amount') }}</th>
                        <th>{{ __('Method') }}</th><th>{{ __('Reference') }}</th><th>{{ __('Status') }}</th><th>{{ __('Action') }}</th>
                    </tr></thead>
                    <tbody>
                        @forelse ($batches as $b)
                            <tr>
                                <td>
                                    <button type="button" class="rm-toggle" data-target="rmd-{{ $b->id }}" aria-expanded="false">
                                        <span>{{ $b->batch_number ?? ('#' . $b->id) }}</span>
                                        <svg class="rm-chev" width="12" height="12" viewBox="0 0 24 24" fill="none"><path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    </button>
                                </td>
                                <td>{{ optional($b->partner)->company_name ?? '—' }}</td>
                                <td class="cs-amt">KES {{ number_format($b->total_amount, 2) }}</td>
                                <td>{{ str_replace('_', ' ', $b->settlement_method ?? '—') }}</td>
                                <td style="font-family:monospace;font-size:12px;">{{ $b->reference ?? '—' }}</td>
                                <td>
                                    @php $sb = ['prepared' => 'is-grey', 'sent' => 'is-pending', 'confirmed' => 'is-paid', 'failed' => 'is-danger'][$b->status] ?? 'is-grey'; @endphp
                                    <span class="cs-badge {{ $sb }}">{{ ucfirst($b->status) }}</span>
                                </td>
                                <td>
                                    @if (in_array($b->status, ['prepared', 'failed']))
                                        <form method="POST" action="{{ route('admin.centresidence.remittances.mark-sent', $b->id) }}" style="display:flex;gap:4px;align-items:center;"
                                              data-cs-confirm="{{ __('Mark this batch paid to the partner by bank? They will confirm receipt.') }}" data-cs-confirm-ok="{{ __('Yes, mark sent') }}">
                                            @csrf
                                            <input name="reference" class="cs-input cs-input--sm" placeholder="{{ __('Bank ref') }}" style="width:100px;">
                                            <button class="cs-btn cs-btn--ghost cs-btn--sm" type="submit">{{ __('Mark sent (bank)') }}</button>
                                        </form>
                                    @elseif ($b->status === 'sent')
                                        <span class="cs-muted" style="font-size:12px;">{{ __('Awaiting partner confirmation') }}</span>
                                    @elseif ($b->status === 'confirmed')
                                        <span class="cs-muted" style="font-size:12px;">{{ __('Confirmed') }} {{ optional($b->confirmation_received_at)->format('Y-m-d') }}</span>
                                    @endif
                                </td>
                            </tr>
                            <tr id="rmd-{{ $b->id }}" class="rm-detail" hidden>
                                <td colspan="7" style="background:#fff;padding:0 12px 12px;">@include('centresidence._remittance-items', ['batch' => $b])</td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="cs-empty">{{ __('No remittance batches yet.') }}
                                @if (count($pendingPartnerIds ?? [])) {{ __('Use “Prepare pending” to create them.') }} @endif
                            </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div style="margin-top:16px;">{{ $batches->links() }}</div>
    </div>

    @include('centresidence._remittance-toggle')
</div></div></div>
@endsection
