@extends('owner.layouts.app')

@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="dep-wrap">

            {{-- Header --}}
            <div class="dep-head">
                <div>
                    <h1 class="dep-title">{{ __('Deposits Held') }}</h1>
                    <p class="dep-sub">{{ __('Security deposits you are currently holding for your tenants.') }}</p>
                </div>
            </div>

            {{-- Liability summary + honest Model-A framing --}}
            <div class="dep-summary">
                <div class="dep-summary__main">
                    <span class="dep-summary__label">{{ __('You are holding') }}</span>
                    <span class="dep-summary__amt">{{ currencyPrice($totalHeld) }}</span>
                    <span class="dep-summary__meta">
                        {{ trans_choice('across :count tenant|across :count tenants', $heldCount, ['count' => $heldCount]) }}
                    </span>
                </div>
                <div class="dep-summary__note">
                    <i class="ri-information-line"></i>
                    <span>{{ __('This is your tenants\' money, held by you and returned at move-out — not income. Centresidence keeps the record.') }}</span>
                </div>
            </div>

            {{-- Status filter --}}
            @php
                $filters = [
                    ''         => __('All'),
                    'held'     => __('Held'),
                    'settled'  => __('Settled'),
                    'refunded' => __('Refunded'),
                    'applied'  => __('Applied'),
                ];
            @endphp
            <div class="dep-filters">
                @foreach ($filters as $val => $label)
                    <a href="{{ route('owner.deposit.index', $val ? ['status' => $val] : []) }}"
                       class="dep-chip {{ (string) ($statusFilter ?? '') === (string) $val ? 'is-active' : '' }}">{{ $label }}</a>
                @endforeach
            </div>

            {{-- Register --}}
            <div class="dep-card">
                @if ($deposits->count())
                    <div class="table-responsive">
                        <table class="dep-table">
                            <thead>
                                <tr>
                                    <th>{{ __('Tenant') }}</th>
                                    <th>{{ __('Unit') }}</th>
                                    <th class="dep-num">{{ __('Amount') }}</th>
                                    <th>{{ __('Held On') }}</th>
                                    <th>{{ __('Status') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($deposits as $d)
                                    @php
                                        $tName = trim(optional($d->tenant->user)->first_name . ' ' . optional($d->tenant->user)->last_name) ?: __('Tenant');
                                        $unit  = optional($d->unit)->unit_name ?: ($d->property_unit_id ? '#' . $d->property_unit_id : '—');
                                        $badge = ['held' => 'dep-badge--held', 'settled' => 'dep-badge--refunded', 'refunded' => 'dep-badge--refunded', 'applied' => 'dep-badge--applied'][$d->status] ?? 'dep-badge--held';
                                        $sLabel = ['held' => __('Held'), 'settled' => __('Settled'), 'refunded' => __('Refunded'), 'applied' => __('Applied')][$d->status] ?? ucfirst($d->status);
                                    @endphp
                                    <tr>
                                        <td>
                                            <span class="dep-tenant">{{ $tName }}</span>
                                        </td>
                                        <td><span class="dep-muted">{{ $unit }}</span></td>
                                        <td class="dep-num"><span class="dep-amt">{{ currencyPrice($d->amount) }}</span></td>
                                        <td><span class="dep-muted">{{ $d->held_at ? $d->held_at->format('d M Y') : '—' }}</span></td>
                                        <td><span class="dep-badge {{ $badge }}">{{ $sLabel }}</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="text-center mt-3">{{ $deposits->links() }}</div>
                @else
                    <div class="dep-empty">
                        <div class="dep-empty__icon"><i class="ri-safe-2-line"></i></div>
                        <h3>{{ __('No deposits held yet') }}</h3>
                        <p>{{ __('When you collect a security deposit at move-in, it will appear here as a held liability — tracked separately from your rent income, and returned at move-out.') }}</p>
                    </div>
                @endif
                </div>

                </div>{{-- /.dep-wrap --}}
            </div>{{-- /.container-fluid --}}
        </div>{{-- /.page-content --}}
    </div>{{-- /.main-content --}}
@endsection

@push('style')
<style>
    .dep-wrap { padding: 4px 2px 40px; }
    .dep-head { margin-bottom: 18px; }
    .dep-title { font-size: 20px; font-weight: 700; color: #111827; margin: 0; }
    .dep-sub { font-size: 13px; color: #6b7280; margin: 4px 0 0; }

    .dep-summary {
        display: flex; align-items: center; justify-content: space-between; gap: 20px; flex-wrap: wrap;
        background: linear-gradient(120deg, #0F2A4A 0%, #185FA5 100%); color: #fff;
        border-radius: 14px; padding: 22px 24px; margin-bottom: 20px; box-shadow: 0 8px 24px rgba(15,42,74,.16);
    }
    .dep-summary__main { display: flex; align-items: baseline; gap: 12px; flex-wrap: wrap; }
    .dep-summary__label { font-size: 12px; text-transform: uppercase; letter-spacing: .07em; color: #b8d3ee; font-weight: 600; }
    .dep-summary__amt { font-size: 30px; font-weight: 800; letter-spacing: -.01em; font-variant-numeric: tabular-nums; }
    .dep-summary__meta { font-size: 13px; color: #d6e6f7; }
    .dep-summary__note { display: flex; align-items: flex-start; gap: 8px; max-width: 46ch; font-size: 12px; line-height: 1.55; color: #e6f0fa; }
    .dep-summary__note i { font-size: 16px; flex: none; margin-top: 1px; color: #f6b64b; }

    .dep-filters { display: flex; gap: 8px; margin-bottom: 14px; flex-wrap: wrap; }
    .dep-chip {
        font-size: 12px; font-weight: 500; padding: 6px 14px; border-radius: 99px;
        background: #fff; border: 0.5px solid #e5e7eb; color: #374151; text-decoration: none; transition: all .13s;
    }
    .dep-chip:hover { border-color: #B5D4F4; color: #0C447C; }
    .dep-chip.is-active { background: #185FA5; border-color: #185FA5; color: #fff !important; }

    .dep-card { background: #fff; border: 0.5px solid #e5e7eb; border-radius: 12px; overflow: hidden; padding: 6px; }

    .dep-table { width: 100%; border-collapse: collapse; }
    .dep-table thead tr { background: #fafafa; border-bottom: 0.5px solid #e5e7eb; }
    .dep-table th { padding: .7rem 1rem; font-size: 10px; font-weight: 500; color: #6b7280; text-transform: uppercase; letter-spacing: .07em; text-align: left; }
    .dep-table td { padding: .85rem 1rem; border: none; vertical-align: middle; }
    .dep-table tbody tr { border-bottom: 0.5px solid #f3f4f6; }
    .dep-table tbody tr:last-child { border-bottom: none; }
    .dep-table tbody tr:hover { background: #f9fafb; }
    /* Right-align the Amount column on BOTH header and cells — the .dep-table th rule (higher
       specificity) would otherwise keep the header left while the value sits right. */
    .dep-table th.dep-num, .dep-table td.dep-num { text-align: right; }

    .dep-tenant { font-size: 13px; font-weight: 600; color: #111827; }
    .dep-muted { font-size: 12.5px; color: #6b7280; }
    .dep-amt { font-size: 13px; font-weight: 700; color: #0F4A84; font-variant-numeric: tabular-nums; }

    .dep-badge { display: inline-flex; align-items: center; font-size: 11px; font-weight: 500; padding: 3px 10px; border-radius: 99px; }
    .dep-badge--held { background: #FAEEDA; color: #854F0B; }
    .dep-badge--refunded { background: #E1F5EE; color: #0F6E56; }
    .dep-badge--applied { background: #f3f4f6; color: #4b5563; }

    .dep-empty { text-align: center; padding: 48px 20px; }
    .dep-empty__icon { font-size: 40px; color: #B5D4F4; margin-bottom: 6px; }
    .dep-empty h3 { font-size: 16px; font-weight: 600; color: #111827; margin: 6px 0; }
    .dep-empty p { font-size: 13px; color: #6b7280; max-width: 56ch; margin: 0 auto; line-height: 1.6; }
</style>
@endpush
