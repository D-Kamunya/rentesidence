@extends('affiliate.layouts.app')

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">

            <div class="page-content-wrapper bg-white p-4 p-md-5 rounded-4 shadow-sm">

                <!-- Header -->
                <div class="row mb-4 text-center text-md-start">
                    <div class="col-12">
                        <h2 class="fw-bold mb-1">{{ __('Marketing Dashboard') }}</h2>
                    </div>
                </div>

                <!-- Referral Link & Tools -->
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-light fw-bold text-primary py-3">
                        <i class="bi bi-link-45deg me-2"></i> {{ __('Your Referral Link') }}
                    </div>
                    <div class="card-body">
                        <div class="input-group mb-3">
                            <input type="text" id="refLink" class="form-control form-control-lg" value="{{ $referralUrl }}" readonly>
                            <button class="btn btn-primary px-4" onclick="copyText('refLink')">
                                <i class="bi bi-clipboard-check me-1"></i> {{ __('Copy') }}
                            </button>
                        </div>

                        <!-- Stats -->
                        <div class="row text-center my-4">
                            <div class="col-6 col-md-4">
                                <div class="p-3 bg-light rounded shadow-sm">
                                    <h4 class="fw-bold text-primary">{{ $stats['clicks'] ?? 0 }}</h4>
                                    <p class="text-muted small mb-0">Clicks</p>
                                </div>
                            </div>
                            <div class="col-6 col-md-4">
                                <div class="p-3 bg-light rounded shadow-sm">
                                    <h4 class="fw-bold text-success">{{ $stats['signups'] ?? 0 }}</h4>
                                    <p class="text-muted small mb-0">Signups</p>
                                </div>
                            </div>
                            <div class="col-6 col-md-4 mt-3 mt-md-0">
                                <div class="p-3 bg-light rounded shadow-sm">
                                    <h4 class="fw-bold text-info">{{ $stats['conversions'] ?? 0 }}</h4>
                                    <p class="text-muted small mb-0">Conversions</p>
                                </div>
                            </div>
                        </div>

                        <!-- QR Code -->
                        <div class="mt-4 text-center">
                            <p class="fw-bold mb-2">{{ __('QR Code') }}</p>
                            <div class="d-inline-block p-3 border rounded bg-light">
                                {!! $qrImage !!}
                            </div>
                        </div>

                        <!-- Social Share Buttons -->
                        <div class="mt-4">
                            <p class="fw-bold">{{ __('Share on Social Media') }}</p>
                            <div class="share-buttons">
                                {!! $shareButtons !!}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Banner & Embed Code -->
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-light fw-bold text-primary py-3">
                        <i class="bi bi-image me-2"></i> {{ __('Banner Images & Embed Code') }}
                    </div>
                    <div class="card-body">
                        <p class="mb-3">{{ __('Use these banners on your website or blog:') }}</p>
                        <div class="row">
                            @foreach($banners as $banner)
                                <div class="col-md-6 mb-4">
                                    <!-- Lightbox banner -->
                                    <a href="{{ $banner['image_url'] }}" target="_blank">
                                        <img src="{{ $banner['image_url'] }}" class="img-fluid rounded shadow-sm border mb-2" alt="Banner">
                                    </a>
                                    <!-- Embed code -->
                                    <div class="input-group">
                                        <textarea id="banner-{{ $loop->index }}" class="form-control" rows="2" readonly>{{ $banner['embed_code'] }}</textarea>
                                        <button class="btn btn-outline-secondary" onclick="copyText('banner-{{ $loop->index }}')">
                                            <i class="bi bi-clipboard"></i>
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Email & WhatsApp Templates -->
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-light fw-bold text-primary py-3">
                        <i class="bi bi-envelope me-2"></i> {{ __('Email & WhatsApp Templates') }}
                    </div>
                    <div class="card-body">
                        <p class="fw-bold">{{ __('Email:') }}</p>
                        <div class="input-group mb-3">
                            <textarea id="emailTemplate" class="form-control" rows="3" readonly>{{ $emailTemplate }}</textarea>
                            <button class="btn btn-outline-secondary" onclick="copyText('emailTemplate')">
                                <i class="bi bi-clipboard"></i>
                            </button>
                        </div>

                        <p class="fw-bold">{{ __('WhatsApp:') }}</p>
                        <div class="input-group">
                            <textarea id="whatsappTemplate" class="form-control" rows="2" readonly>{{ $whatsappTemplate }}</textarea>
                            <button class="btn btn-outline-secondary" onclick="copyText('whatsappTemplate')">
                                <i class="bi bi-clipboard"></i>
                            </button>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>
</div>
@endsection
@push('style')
    <link rel="stylesheet" href="{{ asset('assets/css/affiliate.css') }}">
@endpush

@push('scripts')
<script>
function copyText(elementId) {
    let input = document.getElementById(elementId);
    input.select();
    input.setSelectionRange(0, 99999); // For mobile
    document.execCommand("copy");
    alert("Copied to clipboard!");
}
</script>
@endpush
