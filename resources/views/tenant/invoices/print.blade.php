<!DOCTYPE html>
<html lang="en">
<head>
    <title>{{ __('Invoice') }} · {{ $invoice->invoice_no }}</title>
    @include('common.layouts.style')
    <style>
        /* ── Reset & base ────────────────────────────────────── */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 13px;
            color: #111827;
            background: #f3f4f6;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* ── Page wrapper ────────────────────────────────────── */
        .inv-page {
            max-width: 780px;
            margin: 2rem auto;
            background: #fff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 24px rgba(0,0,0,.08);
        }

        /* ── Header band ─────────────────────────────────────── */
        .inv-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            padding: 2rem 2.25rem 1.5rem;
            border-bottom: 0.5px solid #e5e7eb;
            gap: 1.5rem;
        }

        .inv-header__left { display: flex; flex-direction: column; gap: 10px; }

        .inv-logo {
            height: 52px;
            width: auto;
            max-width: 140px;
            object-fit: contain;
            border-radius: 6px;
        }

        .inv-number {
            font-size: 20px;
            font-weight: 600;
            color: #111827;
            letter-spacing: -.01em;
        }

        .inv-meta {
            display: flex;
            flex-direction: column;
            gap: 3px;
        }
        .inv-meta span {
            font-size: 12px;
            color: #6b7280;
        }
        .inv-meta strong {
            font-weight: 500;
            color: #374151;
        }

        .inv-header__right {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 8px;
        }

        /* Status badge */
        .inv-status {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 12px;
            font-weight: 600;
            padding: 5px 14px;
            border-radius: 99px;
            letter-spacing: .03em;
            text-transform: uppercase;
        }
        .inv-status--paid    { background: #E1F5EE; color: #0F6E56; border: 0.5px solid #9FE1CB; }
        .inv-status--pending { background: #FAEEDA; color: #854F0B; border: 0.5px solid #F5D9A8; }

        /* ── Addresses ───────────────────────────────────────── */
        .inv-addresses {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0;
            border-bottom: 0.5px solid #e5e7eb;
        }

        .inv-address-block {
            padding: 1.5rem 2.25rem;
        }
        .inv-address-block:first-child {
            border-right: 0.5px solid #e5e7eb;
        }

        .inv-address-block__label {
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: #9ca3af;
            margin-bottom: 10px;
        }

        .inv-address-block__name {
            font-size: 14px;
            font-weight: 600;
            color: #111827;
            margin-bottom: 4px;
        }

        .inv-address-block__line {
            font-size: 12px;
            color: #6b7280;
            margin-bottom: 3px;
            display: block;
        }

        .inv-address-block__property {
            display: inline-block;
            margin-top: 8px;
            font-size: 12px;
            font-weight: 500;
            color: #185FA5;
            background: #E6F1FB;
            padding: 3px 10px;
            border-radius: 6px;
        }

        /* ── Section title ───────────────────────────────────── */
        .inv-section {
            padding: 1.5rem 2.25rem;
        }
        .inv-section + .inv-section {
            border-top: 0.5px solid #e5e7eb;
        }

        .inv-section__title {
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: #9ca3af;
            margin-bottom: 1rem;
        }

        /* ── Tables ──────────────────────────────────────────── */
        .inv-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }

        .inv-table thead tr {
            background: #f9fafb;
            border-bottom: 0.5px solid #e5e7eb;
        }

        .inv-table th {
            padding: .6rem .85rem;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .07em;
            color: #6b7280;
            text-align: left;
            white-space: nowrap;
        }

        .inv-table th.right,
        .inv-table td.right { text-align: right; }

        .inv-table td {
            padding: .8rem .85rem;
            border-bottom: 0.5px solid #f3f4f6;
            color: #374151;
            vertical-align: top;
        }

        .inv-table tbody tr:last-child td { border-bottom: none; }

        /* ── Total row ───────────────────────────────────────── */
        .inv-total-row {
            display: flex;
            justify-content: flex-end;
            padding: 1rem 2.25rem 1.5rem;
            border-top: 0.5px solid #e5e7eb;
        }

        .inv-total-box {
            display: flex;
            align-items: baseline;
            gap: 12px;
            background: #f9fafb;
            border: 0.5px solid #e5e7eb;
            border-radius: 10px;
            padding: .75rem 1.25rem;
        }

        .inv-total-box__label {
            font-size: 12px;
            font-weight: 500;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: .06em;
        }

        .inv-total-box__amount {
            font-size: 18px;
            font-weight: 700;
            color: #111827;
            letter-spacing: -.01em;
        }

        /* ── No data ─────────────────────────────────────────── */
        .inv-no-data {
            text-align: center;
            padding: 1.5rem;
            color: #9ca3af;
            font-size: 12px;
        }

        /* ── Footer ──────────────────────────────────────────── */
        .inv-footer {
            padding: 1rem 2.25rem;
            border-top: 0.5px solid #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #fafafa;
        }

        .inv-footer__note {
            font-size: 11px;
            color: #9ca3af;
        }

        .inv-footer__powered {
            font-size: 11px;
            color: #c4c4c4;
        }

        /* ── Print button (screen only) ──────────────────────── */
        .inv-print-btn {
            display: block;
            text-align: center;
            margin: 1.5rem auto;
            max-width: 780px;
        }

        .inv-print-btn button {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #185FA5;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 10px 24px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            transition: background .15s;
        }
        .inv-print-btn button:hover { background: #0F4A84; }

        /* ── Print media ─────────────────────────────────────── */
        @media print {
            body { background: #fff; }
            .inv-page {
                margin: 0;
                border-radius: 0;
                box-shadow: none;
                max-width: 100%;
            }
            .inv-print-btn { display: none; }
        }
    </style>
</head>
<body>

    {{-- Print button (hidden on print) --}}
    <div class="inv-print-btn">
        <button onclick="window.print()">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none">
                <path d="M6 9V3h12v6M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                <rect x="6" y="14" width="12" height="7" rx="1" stroke="currentColor" stroke-width="1.8"/>
            </svg>
            {{ __('Print Invoice') }}
        </button>
    </div>

    <div class="inv-page" id="printDiv1">

        {{-- ── Header ───────────────────────────────────────── --}}
        <div class="inv-header">
            <div class="inv-header__left">
                @if ($owner->print_name)
                    <img src="{{ assetUrl($owner->folder_name . '/' . $owner->file_name) }}"
                         alt="Logo" class="inv-logo">
                @else
                    <img src="{{ getSettingImage('app_logo') }}"
                         alt="Logo" class="inv-logo">
                @endif

                <div class="inv-number">{{ $invoice->invoice_no }}</div>

                <div class="inv-meta">
                    <span><strong>{{ __('Date') }}:</strong> {{ $invoice->updated_at->format('d M Y') }}</span>
                    <span><strong>{{ __('Period') }}:</strong> {{ $invoice->month }}</span>
                    <span><strong>{{ __('Due') }}:</strong> {{ \Carbon\Carbon::parse($invoice->due_date)->format('d M Y') }}</span>
                </div>
            </div>

            <div class="inv-header__right">
                @if ($invoice->status == INVOICE_STATUS_PAID)
                    <span class="inv-status inv-status--paid">
                        <svg width="11" height="11" viewBox="0 0 16 16" fill="none">
                            <path d="M3 8l4 4 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        {{ __('Paid') }}
                    </span>
                @else
                    <span class="inv-status inv-status--pending">
                        <svg width="11" height="11" viewBox="0 0 16 16" fill="none">
                            <circle cx="8" cy="8" r="5.5" stroke="currentColor" stroke-width="1.6"/>
                            <path d="M8 5v3.5l2 1.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                        </svg>
                        {{ __('Pending') }}
                    </span>
                @endif
            </div>
        </div>

        {{-- ── Addresses ─────────────────────────────────────── --}}
        <div class="inv-addresses">

            <div class="inv-address-block">
                <p class="inv-address-block__label">{{ __('Invoice To') }}</p>
                <p class="inv-address-block__name">{{ $tenant->first_name }} {{ $tenant->last_name }}</p>
                <span class="inv-address-block__line">{{ $tenant->email }}</span>
                @if ($tenant->contact_number ?? false)
                    <span class="inv-address-block__line">{{ $tenant->contact_number }}</span>
                @endif
                <span class="inv-address-block__property">
                    {{ $tenant->property_name }} &middot; {{ $tenant->unit_name }}
                </span>
            </div>

            <div class="inv-address-block">
                <p class="inv-address-block__label">{{ __('Pay To') }}</p>
                @if ($owner->print_name)
                    <p class="inv-address-block__name">{{ $owner->print_name }}</p>
                    <span class="inv-address-block__line">{{ $owner->print_address }}</span>
                    <span class="inv-address-block__line">{{ $owner->print_contact }}</span>
                @else
                    <p class="inv-address-block__name">{{ getOption('app_name') }}</p>
                    <span class="inv-address-block__line">{{ getOption('app_location') }}</span>
                    <span class="inv-address-block__line">{{ getOption('app_contact_number') }}</span>
                @endif
            </div>

        </div>

        {{-- ── Invoice Items ─────────────────────────────────── --}}
        <div class="inv-section">
            <p class="inv-section__title">{{ __('Invoice Items') }}</p>
            <table class="inv-table">
                <thead>
                    <tr>
                        <th>{{ __('Type') }}</th>
                        <th>{{ __('Description') }}</th>
                        <th>{{ __('Date') }}</th>
                        <th class="right">{{ __('Amount') }}</th>
                        <th class="right">{{ __('Tax') }}</th>
                        <th class="right">{{ __('Total') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($items as $item)
                        <tr>
                            <td style="font-weight:500;color:#111827;">{{ $item->invoiceType?->name }}</td>
                            <td style="color:#6b7280;">{{ $item->description }}</td>
                            <td style="color:#6b7280;white-space:nowrap;">{{ $item->created_at->format('d M Y') }}</td>
                            <td class="right">{{ currencyPrice($item->amount) }}</td>
                            <td class="right" style="color:#9ca3af;">{{ currencyPrice($item->tax_amount) }}</td>
                            <td class="right" style="font-weight:500;">{{ currencyPrice($item->amount + $item->tax_amount) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- ── Total ─────────────────────────────────────────── --}}
        <div class="inv-total-row">
            <div class="inv-total-box">
                <span class="inv-total-box__label">{{ __('Total') }}</span>
                <span class="inv-total-box__amount">{{ currencyPrice($invoice->amount) }}</span>
            </div>
        </div>

        {{-- ── Transaction Details ───────────────────────────── --}}
        <div class="inv-section">
            <p class="inv-section__title">{{ __('Transaction Details') }}</p>
            <table class="inv-table">
                <thead>
                    <tr>
                        <th>{{ __('Date') }}</th>
                        <th>{{ __('Gateway') }}</th>
                        <th>{{ __('Transaction ID') }}</th>
                        <th class="right">{{ __('Amount') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @isset($order)
                        <tr>
                            <td style="white-space:nowrap;">{{ $order?->created_at->format('d M Y') }}</td>
                            <td>
                                <span style="display:inline-block;background:#f3f4f6;color:#374151;font-size:11px;font-weight:500;padding:2px 9px;border-radius:5px;">
                                    {{ $order?->gatewayTitle ?? 'Cash' }}
                                </span>
                            </td>
                            <td style="font-family:monospace;font-size:11px;color:#6b7280;">
                                {{ $order?->payment_id ?? '—' }}
                            </td>
                            <td class="right" style="font-weight:600;color:#0F6E56;">
                                {{ currencyPrice($order?->total) }}
                            </td>
                        </tr>
                    @else
                        <tr>
                            <td colspan="4" class="inv-no-data">{{ __('No transaction recorded yet') }}</td>
                        </tr>
                    @endisset
                </tbody>
            </table>
        </div>

        {{-- ── Footer ───────────────────────────────────────── --}}
        <div class="inv-footer">
            <span class="inv-footer__note">
                {{ __('Thank you for your business.') }}
            </span>
            <span class="inv-footer__powered">
                {{ $invoice->invoice_no }} &middot; {{ now()->format('d M Y') }}
            </span>
        </div>

    </div>

    @include('common.layouts.script')
    <script>
        window.print();
    </script>
</body>
</html>