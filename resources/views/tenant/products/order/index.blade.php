@extends('tenant.layouts.app')

@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="page-content-wrapper bg-white p-30 radius-20">
                    <div class="row">
                        <div class="col-12">
                            <div
                                class="page-title-box d-sm-flex align-items-center justify-content-between border-bottom mb-20">
                                <div class="page-title-left">
                                    <h3 class="mb-sm-0">{{ $pageTitle }}</h3>
                                </div>
                                <div class="page-title-right">
                                    <ol class="breadcrumb mb-0">
                                        <li class="breadcrumb-item"><a href="{{ route('owner.dashboard') }}"
                                                title="{{ __('Dashboard') }}">{{ __('Dashboard') }}</a></li>
                                        <li class="breadcrumb-item active" aria-current="page">{{ $pageTitle }}</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="billing-center-area bg-off-white theme-border radius-4 p-25">
                            <div class="tbl-tab-wrap border-bottom pb-25 mb-25">
                                <ul class="nav nav-tabs billing-center-nav-tabs" id="myTab" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link active" id="table1-tab" data-bs-toggle="tab"
                                            data-bs-target="#table1-tab-pane" type="button" role="tab"
                                            aria-controls="table1-tab-pane" aria-selected="true">
                                            {{ __('All Orders') }} ({{ $totalProductOrders }})
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="table2-tab" data-bs-toggle="tab"
                                            data-bs-target="#table2-tab-pane" type="button" role="tab"
                                            aria-controls="table2-tab-pane" aria-selected="false">
                                            {{ __('Completed') }} ({{ $totalPaidProductOrders }})
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="table3-tab" data-bs-toggle="tab"
                                            data-bs-target="#table3-tab-pane" type="button" role="tab"
                                            aria-controls="table3-tab-pane" aria-selected="false">
                                            {{ __('Pending') }} ({{ $totalPendingProductOrders }})
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="tableBank-tab" data-bs-toggle="tab"
                                            data-bs-target="#tableBank-tab-pane" type="button" role="tab"
                                            aria-controls="tableBank-tab-pane" aria-selected="false">
                                            {{ __('Cancelled') }} ({{ $totalCancelledProductOrders }})
                                        </button>
                                    </li>
                                </ul>
                            </div>

                            <div class="tab-content" id="myTabContent">
                                <div class="tab-pane fade show active" id="table1-tab-pane" role="tabpanel"
                                    aria-labelledby="table1-tab" tabindex="0">
                                    <table id="allOrderDataTable" class="table theme-border dt-responsive">
                                        <thead>
                                            <tr>
                                                <th>{{ __('Order ID') }}</th>
                                                <th>{{ __('Product Name') }}</th>
                                                <th>{{ __('Quantity') }}</th>
                                                <th>{{ __('Total Amount') }}</th>
                                                <th>{{ __('Order Date') }}</th>
                                                <th>{{ __('Payment Status') }}</th>
                                                <th>{{ __('Action') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($productOrders as $order)
                                                <tr>
                                                    <td>{{ $order->order_id }}</td>
                                                    <td>{{ $order->product_name }}</td>
                                                    <td>{{ $order->quantity }}</td>
                                                    <td>{{ number_format($order->transaction_amount, 2) }}</td>
                                                    <td>{{ $order->created_at->format('d M, Y') }}</td>
                                                    <td>{{ $order->payment_status }}</td>
                                                    <td>
                                                        <a href="" class="btn btn-info btn-sm">{{ __('View') }}</a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <div class="tab-pane fade" id="table2-tab-pane" role="tabpanel" aria-labelledby="table2-tab"
                                    tabindex="0">
                                    <table id="completedOrderDataTable" class="table theme-border dt-responsive">
                                        <thead>
                                            <tr>
                                                <th>{{ __('Order ID') }}</th>
                                                <th>{{ __('Product Name') }}</th>
                                                <th>{{ __('Quantity') }}</th>
                                                <th>{{ __('Total Amount') }}</th>
                                                <th>{{ __('Order Date') }}</th>
                                                <th>{{ __('Action') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($paidProductOrders as $order)
                                                <tr>
                                                    <td>{{ $order->id }}</td>
                                                    <td>{{ $order->product_name }}</td>
                                                    <td>{{ $order->quantity }}</td>
                                                    <td>{{ number_format($order->total_amount, 2) }}</td>
                                                    <td>{{ $order->created_at->format('d M, Y') }}</td>
                                                    <td>
                                                        <a href="" class="btn btn-info btn-sm">{{ __('View') }}</a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <div class="tab-pane fade" id="table3-tab-pane" role="tabpanel"
                                    aria-labelledby="table3-tab" tabindex="0">
                                    <table id="pendingOrderDataTable" class="table theme-border dt-responsive">
                                        <thead>
                                            <tr>
                                                <th>{{ __('Order ID') }}</th>
                                                <th>{{ __('Product Name') }}</th>
                                                <th>{{ __('Quantity') }}</th>
                                                <th>{{ __('Total Amount') }}</th>
                                                <th>{{ __('Order Date') }}</th>
                                                <th>{{ __('Action') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($pendingProductOrders as $order)
                                                <tr>
                                                    <td>{{ $order->id }}</td>
                                                    <td>{{ $order->product_name }}</td>
                                                    <td>{{ $order->quantity }}</td>
                                                    <td>{{ number_format($order->total_amount, 2) }}</td>
                                                    <td>{{ $order->created_at->format('d M, Y') }}</td>
                                                    <td>
                                                        <a href="" class="btn btn-info btn-sm">{{ __('View') }}</a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <div class="tab-pane fade" id="tableBank-tab-pane" role="tabpanel"
                                    aria-labelledby="tableBank-tab" tabindex="0">
                                    <table id="cancelledOrderDataTable" class="table theme-border dt-responsive">
                                        <thead>
                                            <tr>
                                                <th>{{ __('Order ID') }}</th>
                                                <th>{{ __('Product Name') }}</th>
                                                <th>{{ __('Quantity') }}</th>
                                                <th>{{ __('Total Amount') }}</th>
                                                <th>{{ __('Order Date') }}</th>
                                                <th>{{ __('Action') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($cancelledProductOrders as $order)
                                                <tr>
                                                    <td>{{ $order->id }}</td>
                                                    <td>{{ $order->product_name }}</td>
                                                    <td>{{ $order->quantity }}</td>
                                                    <td>{{ number_format($order->total_amount, 2) }}</td>
                                                    <td>{{ $order->created_at->format('d M, Y') }}</td>
                                                    <td>
                                                        <a href="" class="btn btn-info btn-sm">{{ __('View') }}</a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('style')
    @include('common.layouts.datatable-style')
@endpush

@push('script')
    @include('common.layouts.datatable-script')

    <!-- Datatable init js -->
    <script src="{{ asset('/') }}assets/js/pages/billing-center-datatables.init.js"></script>
    <script src="{{ asset('assets/js/custom/invoice.js') }}"></script>
@endpush
