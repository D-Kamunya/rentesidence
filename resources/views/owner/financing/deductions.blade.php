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
            <style>
                .dedlegend { margin-top:18px; border:1px solid var(--line,#E6E1D8); border-radius:12px; background:#FBFCFD; padding:16px 18px; }
                .dedlegend__title { font-size:11px; font-weight:700; letter-spacing:.05em; text-transform:uppercase; color:#6b7280; margin:0 0 12px; }
                .dedlegend__grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:12px 22px; margin:0; }
                @media (max-width:560px) { .dedlegend__grid { grid-template-columns:1fr; } }
                .dedlegend__item { padding-left:12px; border-left:2px solid #185FA5; }
                .dedlegend__item dt { font-size:12.5px; font-weight:700; color:#111827; }
                .dedlegend__item dd { margin:2px 0 0; font-size:12.5px; color:#4b5563; line-height:1.5; }
                .dedlegend__note { display:flex; gap:9px; align-items:flex-start; margin:15px 0 0; padding:11px 13px; border-radius:9px;
                    background:#E8F0F9; color:#1F4B76; font-size:12px; line-height:1.55; }
                .dedlegend__note svg { flex:none; width:16px; height:16px; margin-top:1px; color:#185FA5; }
            </style>
            <div class="dedlegend">
                <p class="dedlegend__title">{{ __('What each deduction means') }}</p>
                <dl class="dedlegend__grid">
                    <div class="dedlegend__item"><dt>{{ __('Platform fee') }}</dt><dd>{{ __('The transaction-mode commission on your rent.') }}</dd></div>
                    <div class="dedlegend__item"><dt>{{ __('Module costs') }}</dt><dd>{{ __('Software & gateway for your smart modules.') }}</dd></div>
                    <div class="dedlegend__item"><dt>{{ __('Financing') }}</dt><dd>{{ __('Repayment of your active facilities.') }}</dd></div>
                    <div class="dedlegend__item"><dt>{{ __('Overdue recovery') }}</dt><dd>{{ __('Any past-due metered commission caught up.') }}</dd></div>
                </dl>
                <p class="dedlegend__note">
                    <svg viewBox="0 0 24 24" fill="none"><path d="M12 3l7 4v5c0 4-3 7-7 8-4-1-7-4-7-8V7l7-4z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><path d="M9 12l2 2 4-4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    <span>{{ __('Only the rent portion of an invoice is deducted from — late fees and other charges reach you in full — and deductions are capped so you always keep a protected share of every rent payment.') }}</span>
                </p>
            </div>
        @endif
    </div>
</div></div></div>
@endsection
