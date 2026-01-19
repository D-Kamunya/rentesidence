@php
    use Illuminate\Support\Str;
    $statusMap = [
        0 => ['label' => 'Pending', 'class' => 'warning'],
        1 => ['label' => 'Paid', 'class' => 'success'],
        2 => ['label' => 'Cancelled', 'class' => 'danger'],
    ];

    $status = $statusMap[$invoice->status] ?? ['label' => 'Unknown', 'class' => 'secondary'];

    $mpesaNumber = old('mpesa_number');

    if (!$mpesaNumber) {
        if (
            $invoice->status == 1 &&
            $invoice->paidOrder &&
            Str::startsWith($invoice->paidOrder->payment_id, 'ws_CO')
        ) {
            // PAID → derive from order
            $mpesaNumber = mpesaPhoneFromReference(
                $invoice->paidOrder->payment_id
            );
        } else {
            // NOT PAID → tenant contact
            $mpesaNumber = $invoice->tenant->user->contact_number ?? '';
        }
    }

@endphp
@extends('layouts.app')

@section('title', 'Pay Invoice')

@section('content')
<div class="main-content">
    <div id="mpesa-preloader" style="display: none;">
        <div id="mpesa-preloaderInner">
            <img src="{{asset('assets/images/gateway-icon/mpesa.jpg')}}" alt="M-PESA Image">
            <div>
                <p>Please follow the instructions and do not refresh or leave this page.</p><br>
                <p>This may take up to <span id="mpesa-timer">2:00 minute(s)</span>.</p><br>
                <p>You will receive a prompt on mobile number to enter your PIN to authorize your payment request.</p><br>
                <p> Please ensure your phone is on and unlocked to enable you to complete the process. Thank you.</p>
            </div>
            <img src="{{asset('assets/images/loading.svg')}}" alt="M-PESA Image">
        </div>
    </div>
    <div class="container-fluid">
        <div class="page-content-wrapper bg-white p-30 radius-20">
            <div class="row justify-content-center">
                <div class="col-6">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-primary text-white text-center">
                            <h4 class="mb-0">Centresidence Instanst M-pesa Invoice Payment</h4>
                        </div>

                        <div class="card-body">

                            {{-- Invoice summary --}}
                            <div class="border rounded p-3 mb-4 bg-light">

                                <div class="row mb-2">
                                    <div class="col-6 text-muted">Invoice No</div>
                                    <div class="col-6 text-end fw-semibold">
                                       INVOICE {{ $invoice->invoice_no }}
                                    </div>
                                </div>

                                <div class="row mb-2">
                                    <div class="col-6 text-muted">Rent Month</div>
                                    <div class="col-6 text-end fw-semibold">
                                        {{ $invoice->month }}
                                    </div>
                                </div>

                                <div class="row mb-2">
                                    <div class="col-6 text-muted">Issue Date</div>
                                    <div class="col-6 text-end fw-semibold">
                                        {{ $invoice->created_at->format('d M Y') }}
                                    </div>
                                </div>

                                <div class="row mb-2">
                                    <div class="col-6 text-muted">Status</div>
                                    <div class="col-6 text-end">
                                        <span class="badge bg-{{ $status['class'] }}">
                                            {{ $status['label'] }}
                                        </span>
                                    </div>
                                </div>
                                @if ($invoice->status != 1)
                                <div class="row mb-2">
                                    <div class="col-6 text-muted">Due Date</div>
                                    <div class="col-6 text-end fw-semibold">
                                        {{ \Carbon\Carbon::parse($invoice->due_date)->format('d M Y') }}
                                    </div>
                                </div>
                                @endif

                                @if ($invoice->status == 1 && $invoice->paidOrder)
                                    @if ( Str::startsWith($invoice->paidOrder->payment_id, 'ws_CO'))
                                        <div class="row mb-2">
                                            <div class="col-6 text-muted">Paid By</div>
                                            <div class="col-6 text-end fw-semibold">
                                                {{ mpesaPhoneFromReference($invoice->paidOrder->payment_id) }}
                                            </div>
                                        </div>
                                    @endif

                                    <div class="row">
                                        <div class="col-6 text-muted">Paid On</div>
                                        <div class="col-6 text-end fw-semibold">
                                            {{ $invoice->paidOrder->created_at->format('d M Y H:i') }}
                                        </div>
                                    </div>
                                @endif


                                <div class="row mb-2">
                                    <div class="col-6 text-muted">Amount</div>
                                    <div class="col-6 text-end fw-semibold text-primary">
                                        KES {{ number_format($invoice->amount, 2) }}
                                    </div>
                                </div>

                                <hr>

                                <div class="row mb-2">
                                    <div class="col-6 text-muted">Property</div>
                                    <div class="col-6 text-end fw-semibold">
                                        {{ $invoice->property->name }}
                                    </div>
                                </div>

                                <div class="row mb-2">
                                    <div class="col-6 text-muted">Unit</div>
                                    <div class="col-6 text-end fw-semibold">
                                        {{ $invoice->propertyUnit->unit_name }}
                                    </div>
                                </div>

                                <div class="row mb-2">
                                    <div class="col-6 text-muted">Tenant</div>
                                    <div class="col-6 text-end fw-semibold">
                                        {{ $invoice->tenant->user->name }}
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-6 text-muted">Landlord</div>
                                    <div class="col-6 text-end fw-semibold">
                                        {{ $invoice->landlord->name }}
                                    </div>
                                </div>

                            </div>

                            {{-- Payment form --}}
                            <form id="instant-invoice-pay-form"
                                action="{{ route('instant.payment.checkout', $invoice->payment_token) }}"
                                method="POST">
                                @csrf

                                {{-- Mpesa account --}}
                                <div class="mb-3">
                                    <label class="form-label">M-Pesa Number</label>
                                    <input type="tel"
                                        class="form-control"
                                        name="mpesa_number"
                                        id="mpesa_number"
                                        placeholder="07XXXXXXXX"
                                        required value="{{ $mpesaNumber }}"
                                        {{ $invoice->status == 1 ? 'readonly' : '' }}>
                                </div>

                                {{-- Hidden invoice data --}}
                                <input type="hidden" name="invoice_id" value="{{ $invoice->id }}">

                                <div class="d-grid mt-4">
                                    <button type="button"
                                            id="instantPayBtn"
                                            class="btn btn-primary btn-lg" {{ $invoice->status == 1 ? 'disabled' : '' }}>
                                         {{ $invoice->status == 1 ? 'Invoice Paid' : 'Pay Now' }}
                                    </button>
                                </div>
                                @guest
                                    <div class="text-center mt-3">
                                        <small class="text-muted">
                                            <a href="{{ route('login') }}">Login to view your full statement</a>
                                        </small>
                                    </div>
                                @endguest

                            </form>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('script')
    <script src="{{ asset('assets/js/custom/tenant-instant-invoice-pay.js') }}"></script>
@endpush
