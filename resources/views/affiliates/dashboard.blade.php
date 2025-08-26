
@extends('affiliates.layouts.app')

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
                        <div class="col-md-3">
                            <div class="card shadow-sm border-0 rounded-3">
                                <div class="card-body">
                                    <small class="text-muted">Total Earnings</small>
                                    <h4 class="fw-bold text-success mb-0">Ksh {{ number_format($summary['total_commissions'], 2) }}</h4>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="card shadow-sm border-0 rounded-3">
                                <div class="card-body">
                                    <small class="text-muted">This Month</small>
                                    <h4 class="fw-bold text-primary mb-0">Ksh {{ number_format($summary['total_payouts'], 2) }}</h4>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="card shadow-sm border-0 rounded-3">
                                <div class="card-body">
                                    <small class="text-muted">Active Referrals</small>
                                    <h4 class="fw-bold text-dark mb-0">{{ $summary['recurring_clients'] }}</h4>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="card shadow-sm border-0 rounded-3">
                                <div class="card-body">
                                    <small class="text-muted">Tier Level</small>
                                    <h4 class="fw-bold text-warning mb-0">{{ $summary['tier'] }}</h4>
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
                                        <th>Subscription ID</th>
                                        <th>Type</th>
                                        <th>Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recentCommissions as $commission)
                                        <tr>
                                            <td>{{ $commission['date'] }}</td>
                                            <td>{{ $commission['owner'] }}</td>
                                            <td>{{ $commission['package'] }}</td>
                                            <td>
                                                <span class="badge {{ $commission['type'] == 'New' ? 'bg-success' : 'bg-secondary' }}">
                                                    {{ $commission['type'] }}
                                                </span>
                                            </td>
                                            <td>Ksh {{ number_format($commission['amount'], 2) }}</td>
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
    var ctx = document.getElementById('earningsChart').getContext('2d');
    var earningsChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: {!! json_encode(array_column($commissionTrends, 'month')) !!},
            datasets: [{
                label: 'Monthly Earnings (Ksh)',
                data: {!! json_encode(array_column($commissionTrends, 'amount')) !!},
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

</script>
@endpush

