
@extends('affiliate.layouts.app')

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="page-content-wrapper bg-white p-30 radius-20">
                <div class="container">
                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                                <div class="page-title-left">
                                    <h2 class="mb-sm-0">{{ __('Affiliate Dashboard') }}</h2>
                                    <p>{{ __('Welcome back') }}, {{ auth()->user()->name }} <span class="iconify font-24"
                                            data-icon="openmoji:waving-hand"></span></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Summary Cards -->
                    <div class="row g-3 mb-4">

                        <!-- Total Earnings -->
                        <div class="col-md-3">
                            <div class="card shadow-sm border-0 rounded-3 bg-light">
                                <div class="card-body">
                                    <small class="text-muted">
                                        <i class="bi bi-cash-stack me-1 text-success"></i> Total Earnings
                                    </small>
                                    <h4 class="fw-bold text-success mb-0">
                                        Ksh {{ number_format($summary['total_commissions'], 2) }}
                                    </h4>
                                </div>
                            </div>
                        </div>

                        <!-- This Month -->
                        <div class="col-md-3">
                            <div class="card shadow-sm border-0 rounded-3 bg-light">
                                <div class="card-body">
                                    <small class="text-muted">
                                        <i class="bi bi-calendar-event me-1 text-primary"></i> This Month
                                    </small>
                                    <h4 class="fw-bold text-primary mb-0">
                                        Ksh {{ number_format($summary['current_monthly_earning'], 2) }}
                                    </h4>
                                </div>
                            </div>
                        </div>

                        <!-- New Clients (This Month) -->
                        <div class="col-md-3">
                            <div class="card shadow-sm border-0 rounded-3 bg-light">
                                <div class="card-body">
                                    <small class="text-muted">
                                        <i class="bi bi-person-plus me-1 text-info"></i> New Clients
                                    </small>
                                    <h4 class="fw-bold text-info mb-0">
                                        {{ $summary['new_clients'] }}
                                    </h4>
                                </div>
                            </div>
                        </div>

                        <!-- Active Referrals (Recurring Clients) -->
                        <div class="col-md-3">
                            <div class="card shadow-sm border-0 rounded-3 bg-light">
                                <div class="card-body">
                                    <small class="text-muted">
                                        <i class="bi bi-people-fill me-1 text-dark"></i> Active Referrals
                                    </small>
                                    <h4 class="fw-bold text-dark mb-0">
                                        {{ $summary['recurring_clients'] }}
                                    </h4>
                                </div>
                            </div>
                        </div>

                        <!-- Available Balance -->
                        <div class="col-md-3">
                            <div class="card shadow-sm border-0 rounded-3 bg-light">
                                <div class="card-body">
                                    <small class="text-muted">
                                        <i class="bi bi-wallet2 me-1 text-warning"></i> Available Balance
                                    </small>
                                    <h4 class="fw-bold text-warning mb-0">
                                        Ksh {{ number_format($summary['available_balance'], 2) }}
                                    </h4>
                                </div>
                            </div>
                        </div>

                        <!-- Total Payouts -->
                        <div class="col-md-3">
                            <div class="card shadow-sm border-0 rounded-3 bg-light">
                                <div class="card-body">
                                    <small class="text-muted">
                                        <i class="bi bi-bank me-1 text-secondary"></i> Total Payouts
                                    </small>
                                    <h4 class="fw-bold text-secondary mb-0">
                                        Ksh {{ number_format($summary['total_payouts'], 2) }}
                                    </h4>
                                </div>
                            </div>
                        </div>

                        <!-- Total Referrals -->
                        <div class="col-md-3">
                            <div class="card shadow-sm border-0 rounded-3 bg-light">
                                <div class="card-body">
                                    <small class="text-muted">
                                        <i class="bi bi-link-45deg me-1 text-danger"></i> Total Referrals
                                    </small>
                                    <h4 class="fw-bold text-danger mb-0">
                                        {{ $summary['total_referrals'] }}
                                    </h4>
                                </div>
                            </div>
                        </div>

                    </div>


                    <!-- Chart Section -->
                    <div class="card shadow-sm border-0 rounded-3 mb-4">
                        <div class="card-header bg-white fw-bold">Earnings Overview</div>
                        <div class="card-body">
                            <canvas id="earningsChart" height="100"></canvas>
                        </div>
                    </div>

                    <!-- Recent Commissions -->
                    <div class="card shadow-sm border-0 rounded-3">
                        <div class="card-header bg-white fw-bold">Recent Commissions</div>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Date</th>
                                        <th>Client</th>
                                        <th>Subscription</th>
                                        <th>Type</th>
                                        <th>Amount</th>
                                        <th>Commission Rate (%)</th>
                                        <th>Commission Payout (Ksh)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recentCommissions as $commission)
                                        <tr>
                                            <td>{{ $commission['date'] }}</td>
                                            <td>{{ $commission['owner'] }}</td>
                                            <td>{{ $commission['package'] }}</td>
                                            <td>
                                                <span class="badge {{ $commission['type'] == NEW_CLIENT ? 'bg-success' : 'bg-secondary' }}">
                                                    {{ $commission['type'] == NEW_CLIENT ? 'New Client' : 'Recurring Client' }}
                                                </span>
                                            </td>
                                            <td>Ksh {{ number_format($commission['amount'], 2) }}</td>
                                            <td> {{ $commission['type'] == NEW_CLIENT ? getOption('FIRST_TIME_COMMISSION_RATE') : getOption('RECURRING_COMMISSION_RATE') }}%</td>
                                            <td>Ksh {{ number_format($commission['type'] == NEW_CLIENT ? $commission['amount'] * (getOption('FIRST_TIME_COMMISSION_RATE') / 100) : $commission['amount'] * (getOption('RECURRING_COMMISSION_RATE') / 100), 2) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-3">No commissions yet.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
            
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var ctx = document.getElementById('earningsChart').getContext('2d');
    var earningsChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: @json(array_column($commissionTrends, 'month')),
            datasets: [{
                label: 'Monthly Earnings (Ksh)',
                data: @json(array_column($commissionTrends, 'amount')),
                borderColor: '#4CAF50',
                backgroundColor: 'rgba(76, 175, 80, 0.1)',
                tension: 0.4,
                fill: true,
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false }
            }
        }
    });
});
</script>
@endpush

