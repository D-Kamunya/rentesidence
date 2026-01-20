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
            $mpesaNumber = mpesaPhoneFromReference($invoice->paidOrder->payment_id);
        } else {
            $mpesaNumber = $invoice->tenant->user->contact_number ?? '';
        }
    }
@endphp

@extends('layouts.app')

@section('title', 'Pay Invoice')

@section('content')
<style>
    
    /* Header: Deep Gradient with High Contrast Text */
    .custom-card-header {
        background: linear-gradient(135deg, #1a237e 0%, #0d47a1 100%);
        border-bottom: 4px solid #3ab54a; /* M-Pesa Green Border */
    }

    /* Authentic M-Pesa Green Button */
    .btn-mpesa {
        background-color: #3ab54a;
        border-color: #3ab54a;
        color: white;
        transition: all 0.3s ease;
    }

    .btn-mpesa:hover {
        background-color: #2e8f3b;
        border-color: #2e8f3b;
        color: white;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(58, 181, 74, 0.3);
    }

    .btn-mpesa:disabled {
        background-color: #a5d6a7;
        border-color: #a5d6a7;
    }
</style>

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

    <div class="container-fluid py-4">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-5">
                <div class="card shadow-lg border-0 radius-20 overflow-hidden">
                    
                    {{--  Card Header --}}
                    <div class="card-header custom-card-header text-center py-4">
                        <div class="mb-2">
                            <i class="fas fa-lock text-white-50 fa-lg"></i>
                        </div>
                        <h5 class="mb-1 fw-bold text-uppercase text-white" style="letter-spacing: 1px;">
                            Secure Checkout
                        </h5>
                        <p class="mb-0 small text-white-50">
                            Centresidence Property Management Technologies
                        </p>
                    </div>

                    <div class="card-body p-4">
                        {{-- Invoice summary --}}
                        <div class="border rounded-3 p-3 mb-4 bg-light shadow-sm">
                            <div class="row mb-2">
                                <div class="col-6 text-muted">Invoice No</div>
                                <div class="col-6 text-end fw-bold">#{{ $invoice->invoice_no }}</div>
                            </div>

                            <div class="row mb-2">
                                <div class="col-6 text-muted">Rent Month</div>
                                <div class="col-6 text-end fw-semibold">{{ $invoice->month }}</div>
                            </div>

                            <div class="row mb-2">
                                <div class="col-6 text-muted">Status</div>
                                <div class="col-6 text-end">
                                    <span class="badge rounded-pill bg-{{ $status['class'] }}">
                                        {{ $status['label'] }}
                                    </span>
                                </div>
                            </div>

                            <div class="row mb-2 border-top pt-2 mt-2">
                                <div class="col-6 text-muted">Amount Due</div>
                                <div class="col-6 text-end fw-bold text-primary fs-5">
                                    KES {{ number_format($invoice->amount, 2) }}
                                </div>
                            </div>

                            <hr class="opacity-50">

                            <div class="row mb-1 small">
                                <div class="col-5 text-muted">Property</div>
                                <div class="col-7 text-end text-truncate">{{ $invoice->property->name }}</div>
                            </div>

                            <div class="row mb-1 small">
                                <div class="col-5 text-muted">Unit</div>
                                <div class="col-7 text-end fw-semibold text-truncate">{{ $invoice->propertyUnit->unit_name }}</div>
                            </div>
                        </div>

                        {{-- Payment form --}}
                        <form id="instant-invoice-pay-form"
                            action="{{ route('instant.payment.checkout', $invoice->payment_token) }}"
                            method="POST">
                            @csrf

                            <div class="mb-3">
                                <label class="form-label fw-bold">M-Pesa Phone Number</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white"><i class="fas fa-phone-alt text-muted"></i></span>
                                    <input type="tel"
                                        class="form-control form-control-lg"
                                        name="mpesa_number"
                                        id="mpesa_number"
                                        placeholder="07XXXXXXXX"
                                        required value="{{ $mpesaNumber }}"
                                        {{ $invoice->status == 1 ? 'readonly' : '' }}>
                                </div>
                                <small class="text-muted">Enter the number to receive the M-Pesa prompt.</small>
                            </div>

                            <input type="hidden" name="invoice_id" value="{{ $invoice->id }}">

                            <div class="d-grid mt-4">
                                <button type="button"
                                        id="instantPayBtn"
                                        class="btn btn-mpesa btn-lg shadow-sm fw-bold py-3" 
                                        {{ $invoice->status == 1 ? 'disabled' : '' }}>
                                     {{ $invoice->status == 1 ? 'Invoice Already Paid' : 'PAY WITH MPESA' }}
                                </button>
                            </div>
                            @guest
                                <div class="text-center mt-4">
                                    <p class="mb-0 small text-muted">Want to see your full history?</p>
                                    <a href="{{ route('login') }}" class="fw-bold text-decoration-none small">Login to Your Tenant Portal</a>
                                </div>
                            @endguest
                        </form>
                    </div>
                </div>
                <p class="text-center mt-4 text-muted small">© {{ date('Y') }} Centresidence. All Rights Reserved.</p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('script')
    <script src="{{ asset('assets/js/custom/tenant-instant-invoice-pay.js') }}"></script>
@endpush