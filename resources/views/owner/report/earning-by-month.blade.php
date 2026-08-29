@extends('owner.layouts.app')

@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="page-content-wrapper bg-white p-30 radius-20">
                    @include('centresidence._design')

                    <div class="cs-titlebar">
                        <div>
                            <h1 class="cs-title">{{ $pageTitle }}</h1>
                            <ol class="cs-crumb"><li><a href="{{ route('owner.dashboard') }}">{{ __('Dashboard') }}</a></li><li>›</li><li>{{ __('Report') }}</li><li>›</li><li>{{ $pageTitle }}</li></ol>
                        </div>
                    </div>

                    <div class="cs-card"><div class="cs-tablewrap">
                        <table class="cs-table">
                            <thead>
                                <tr>
                                    <th>{{ __('SL') }}</th>
                                    <th>{{ __('Month') }}</th>
                                    <th style="text-align:right;">{{ __('Income') }}</th>
                                    <th style="text-align:right;">{{ __('Expense') }}</th>
                                    <th style="text-align:right;">{{ __('Profit/Loss') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($lossProfits as $lossProfit)
                                    @php $net = $lossProfit['income'] - $lossProfit['expense']; @endphp
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $lossProfit['month'] }}</td>
                                        <td style="text-align:right;">{{ currencyPrice($lossProfit['income']) }}</td>
                                        <td style="text-align:right;">{{ currencyPrice($lossProfit['expense']) }}</td>
                                        <td style="text-align:right;color:{{ $net < 0 ? 'var(--red)' : 'var(--green-dark)' }};font-weight:600;">
                                            {{ currencyPrice($net) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="cs-empty">{{ __('No data yet') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div></div>
                </div>
            </div>
        </div>
    </div>
@endsection
