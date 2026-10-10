@extends('admin.layouts.app')

@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="page-content-wrapper bg-white p-30 radius-20 cs-controls">
                    @include('centresidence._design')

                    {{-- Title --}}
                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box d-sm-flex align-items-center justify-content-between border-bottom mb-20">
                                <div class="page-title-left">
                                    <h3 class="mb-sm-0">{{ $pageTitle }}</h3>
                                </div>
                                <div class="page-title-right">
                                    <ol class="breadcrumb mb-0">
                                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
                                        <li class="breadcrumb-item"><a href="{{ route('admin.affiliates.index') }}">{{ __('Affiliates') }}</a></li>
                                        <li class="breadcrumb-item active" aria-current="page">{{ $pageTitle }}</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Affiliate header --}}
                    <div class="d-flex align-items-center gap-3 mb-4" style="gap:14px;">
                        <div style="width:48px;height:48px;border-radius:12px;background:#185FA5;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:18px;flex:none;">
                            {{ strtoupper(substr($affiliate->user->first_name ?? 'A', 0, 1)) }}
                        </div>
                        <div>
                            <div style="font-size:16px;font-weight:700;color:#111827;">{{ trim(($affiliate->user->first_name ?? '') . ' ' . ($affiliate->user->last_name ?? '')) ?: '—' }}</div>
                            <div style="font-size:13px;color:#6b7280;">{{ $affiliate->user->email ?? '—' }} · {{ __('Ref') }}: {{ $affiliate->referral_code ?? '—' }}</div>
                        </div>
                    </div>

                    {{-- Stat cards --}}
                    <div class="row g-3 mb-4">
                        @php
                            $cards = [
                                [__('Available balance'), $availableBalance, '#0F6E56', '#E1F5EE'],
                                [__('Lifetime earned'), $lifetimeEarned, '#185FA5', '#E8F0F9'],
                                [__('Total withdrawn'), $totalWithdrawn, '#854F0B', '#FCF3E2'],
                                [__('This month'), $currentMonthPayout, '#5B4B9E', '#EDEAF7'],
                            ];
                        @endphp
                        @foreach($cards as [$label, $value, $ink, $bg])
                            <div class="col-6 col-lg-3">
                                <div style="background:{{ $bg }};border-radius:12px;padding:16px 18px;height:100%;">
                                    <div style="font-size:11.5px;font-weight:600;text-transform:uppercase;letter-spacing:.04em;color:{{ $ink }};opacity:.85;">{{ $label }}</div>
                                    <div style="font-size:22px;font-weight:800;color:{{ $ink }};margin-top:6px;">{{ currencyPrice($value) }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Monthly breakdown --}}
                    <div class="add-property-title border-bottom pb-15 mb-15"><h4>{{ __('Monthly earnings (last 12 months)') }}</h4></div>
                    <div class="table-responsive mb-4">
                        <table class="table theme-border">
                            <thead>
                                <tr>
                                    <th>{{ __('Period') }}</th>
                                    <th class="text-end">{{ __('Subscription') }}</th>
                                    <th class="text-end">{{ __('Rent') }}</th>
                                    <th class="text-end">{{ __('Marketplace') }}</th>
                                    <th class="text-end">{{ __('Other') }}</th>
                                    <th class="text-end">{{ __('Total') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($monthly as $m)
                                    <tr>
                                        <td>{{ $m->period }}</td>
                                        <td class="text-end">{{ currencyPrice($m->subscription) }}</td>
                                        <td class="text-end">{{ currencyPrice($m->rent) }}</td>
                                        <td class="text-end">{{ currencyPrice($m->marketplace) }}</td>
                                        <td class="text-end">{{ currencyPrice($m->other) }}</td>
                                        <td class="text-end" style="font-weight:700;">{{ currencyPrice($m->total) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" style="text-align:center;padding:24px;color:#9ca3af;">{{ __('No commissions earned yet.') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Recent withdrawals --}}
                    <div class="add-property-title border-bottom pb-15 mb-15"><h4>{{ __('Recent withdrawals') }}</h4></div>
                    <div class="table-responsive">
                        <table class="table theme-border">
                            <thead>
                                <tr>
                                    <th>{{ __('Requested') }}</th>
                                    <th class="text-end">{{ __('Amount') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('M-Pesa ref') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentWithdrawals as $w)
                                    <tr>
                                        <td>{{ $w->created_at->format('d M Y H:i') }}</td>
                                        <td class="text-end">{{ currencyPrice($w->amount) }}</td>
                                        <td>{{ ucfirst($w->status) }}</td>
                                        <td>{{ $w->mpesa_reference ?: '—' }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" style="text-align:center;padding:24px;color:#9ca3af;">{{ __('No withdrawals yet.') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <a href="{{ route('admin.affiliates.index') }}" class="btn mt-25"
                       style="display:inline-flex;align-items:center;background:#F4F5F7;border:1px solid #D9DEE6;color:#4B5563;border-radius:8px;padding:10px 22px;font-size:14px;font-weight:500;text-decoration:none;">{{ __('Back to affiliates') }}</a>
                </div>
            </div>
        </div>
    </div>
@endsection
