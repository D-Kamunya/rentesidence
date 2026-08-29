@extends('owner.layouts.app')

@section('content')
<div class="main-content"><div class="page-content"><div class="container-fluid">
    <div class="page-content-wrapper bg-white p-30 radius-20">
        @include('centresidence._design')

        <div class="cs-titlebar">
            <div>
                <h1 class="cs-title">{{ __('Rent & deductions') }}</h1>
                <ol class="cs-crumb"><li><a href="{{ route('owner.financing.mine') }}">{{ __('My Financing') }}</a></li><li>›</li><li>{{ __('Rent & deductions') }}</li></ol>
            </div>
            <a href="{{ route('owner.financing.mine') }}" class="cs-btn cs-btn--ghost">{{ __('My Financing') }}</a>
        </div>

        <p class="cs-muted" style="margin-bottom:18px;max-width:720px;">
            {{ __('Each rent payment collected for you, and exactly what was applied before the balance reached your wallet — module costs, financing repayment, and any overdue recovery. Full transparency, nothing hidden.') }}
        </p>

        @if ($rows->isEmpty())
            <div class="cs-card"><div class="cs-card__body cs-empty">
                {{ __('No deductions yet. Once rent is collected through Centresidence and you have module costs or an active facility, every split will appear here.') }}
            </div></div>
        @else
            <div class="cs-tablewrap">
                <table class="cs-table">
                    <thead><tr>
                        <th>{{ __('Date') }}</th><th>{{ __('Property') }}</th><th>{{ __('Rent') }}</th>
                        <th>{{ __('Platform fee') }}</th><th>{{ __('Module costs') }}</th><th>{{ __('Financing') }}</th><th>{{ __('Overdue recovery') }}</th>
                        <th>{{ __('To your wallet') }}</th>
                    </tr></thead>
                    <tbody>
                        @foreach ($rows as $r)
                            <tr>
                                <td style="white-space:nowrap;">{{ optional($r['date'])->format('M j, Y') }}</td>
                                <td>{{ optional($r['property'])->name ?? '—' }}</td>
                                <td class="cs-amt">{{ $r['gross'] > 0 ? 'KES ' . number_format($r['gross'], 2) : '—' }}</td>
                                <td>{{ ($r['platform_fee'] ?? 0) > 0 ? 'KES ' . number_format($r['platform_fee'], 2) : '—' }}</td>
                                <td>{{ $r['infra'] > 0 ? 'KES ' . number_format($r['infra'], 2) : '—' }}</td>
                                <td>{{ $r['facility'] > 0 ? 'KES ' . number_format($r['facility'], 2) : '—' }}</td>
                                <td>{{ $r['commission'] > 0 ? 'KES ' . number_format($r['commission'], 2) : '—' }}</td>
                                <td class="cs-amt" style="font-weight:600;color:var(--green-dark);">{{ $r['net'] !== null ? 'KES ' . number_format($r['net'], 2) : '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <p class="cs-muted" style="margin-top:12px;font-size:11.5px;">
                {{ __('“Platform fee” = the transaction-mode commission on your rent. “Module costs” = software & gateway for your smart modules. “Financing” = repayment of your active facilities. “Overdue recovery” = any past-due metered commission caught up. Only the rent portion of an invoice is deducted from — late fees and other charges reach you in full — and deductions are capped so you always keep a protected share of every rent payment.') }}
            </p>
        @endif
    </div>
</div></div></div>
@endsection
