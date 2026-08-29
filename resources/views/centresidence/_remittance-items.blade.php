{{-- Batch drill-down: the constituent repayments + the prepared→sent→confirmed
     timeline. Shared by the partner and admin remittance pages. Expects $batch
     with items.facility + items.settlementTransaction eager-loaded. --}}
<div style="padding:12px 14px;background:#F8FAFC;border:0.5px solid #EEF2F7;border-radius:10px;">
    <div style="display:flex;flex-wrap:wrap;gap:18px;font-size:11.5px;color:#6b7280;margin-bottom:10px;">
        <span><strong style="color:#374151;">{{ __('Prepared') }}:</strong> {{ optional($batch->remittance_date)->format('d M Y') ?? '—' }}</span>
        <span><strong style="color:#374151;">{{ __('Sent') }}:</strong> {{ optional($batch->sent_at)->format('d M Y H:i') ?? '—' }}</span>
        <span><strong style="color:#374151;">{{ __('Confirmed') }}:</strong> {{ optional($batch->confirmation_received_at)->format('d M Y H:i') ?? '—' }}</span>
    </div>
    <div class="cs-tablewrap">
        <table class="cs-table" style="font-size:12px;margin:0;">
            <thead><tr>
                <th>{{ __('Facility') }}</th><th>{{ __('Repayment') }}</th><th>{{ __('From rent payment') }}</th><th style="text-align:right;">{{ __('Amount') }}</th>
            </tr></thead>
            <tbody>
                @php
                    $typeLabels = [
                        'rent_deduction_principal' => __('Principal'),
                        'rent_deduction_interest'  => __('Interest'),
                        'rent_deduction_penalty'   => __('Penalty / fee'),
                        'infrastructure_recovery'  => __('Infrastructure'),
                        'commission_recovery'      => __('Overdue recovery'),
                    ];
                @endphp
                @forelse ($batch->items as $it)
                    @php
                        $st = $it->settlementTransaction;
                        $type = $typeLabels[optional($st)->transaction_type] ?? ucfirst(str_replace('_', ' ', optional($st)->transaction_type ?? '—'));
                    @endphp
                    <tr>
                        <td>{{ optional($it->facility)->facility_number ?? ('#' . $it->facility_id) }}</td>
                        <td>{{ $type }}</td>
                        <td>{{ optional($st)->rent_transaction_id ? ('#' . $st->rent_transaction_id) : '—' }}</td>
                        <td style="text-align:right;font-variant-numeric:tabular-nums;">KES {{ number_format($it->amount, 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="cs-empty">{{ __('No line items recorded.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
