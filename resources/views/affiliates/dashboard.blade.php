
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
                                    <h2 class="mb-sm-0">{{ __('Account Manager Dashboard') }}</h2>
                                    <p>{{ __('Welcome back') }}, Kelvin Brown <span class="iconify font-24"
                                            data-icon="openmoji:waving-hand"></span></p>
                                </div>
                            </div>
                        </div>
                    </div>
            
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Owner Name</th>
                                <th>Package</th>
                                <th>Package Cost</th>
                                <th>Affiliate Earning (7%)</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($owners as $owner)
                                <tr>
                                    <td>{{ $owner['name'] }}</td>
                                    <td>{{ $owner['package'] }}</td>
                                    <td>{{ number_format($owner['package_cost']) }} KES</td>
                                    <td>{{ number_format($owner['affiliate_earning'], 2) }} KES</td>
                                    <td>
                                        @if($owner['status'] == 'active')
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-danger">Dormant</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                <!-- Withdraw button -->
                    <div class="mt-4">
                        <h4>Total Earnings: {{ number_format(array_sum(array_column($owners, 'affiliate_earning')), 2) }} KES</h4>
                        <button class="action-button theme-btn mt-25" data-bs-toggle="modal" data-bs-target="#withdrawModal">Request Withdrawal</button>
                    </div>
                </div>

                <!-- Withdrawal Modal -->
                <div class="modal fade" id="withdrawModal" tabindex="-1" aria-labelledby="withdrawModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="withdrawModalLabel">Request Withdrawal</h5>
                                <button type="button" class="action-button theme-btn mt-25" data-bs-dismiss="modal" aria-label="Close">Close</button>
                            </div>
                            <div class="modal-body">
                                <p>Your current total earnings: {{ number_format(array_sum(array_column($owners, 'affiliate_earning')), 2) }} KES</p>
                                <form action="{{route('affiliates.withdraw') }}" method="POST">
                                        @csrf
                                    <div class="mb-3">
                                        <label for="withdrawAmount" class="form-label">Enter Amount to Withdraw</label>
                                        <input type="number" class="form-control" id="withdrawAmount" name="amount" required>
                                    </div>
                                        <button type="submit" class="action-button theme-btn mt-25">Submit Request</button>
                                    </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
