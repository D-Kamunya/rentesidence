@extends('affiliate.layouts.app')

@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">

                <div class="page-content-wrapper bg-white p-3 p-md-4 radius-20">

                    <!-- Page Title -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <div
                                class="page-title-box d-sm-flex align-items-center justify-content-between border-bottom pb-2">

                                <div class="page-title-left">
                                    <h3 class="mb-0 fs-5">Centresidence Academy</h3>
                                </div>

                                <div class="page-title-right">
                                    <ol class="breadcrumb mb-0 small">
                                        <li class="breadcrumb-item">
                                            <a href="{{ route('affiliate.dashboard') }}">Dashboard</a>
                                        </li>
                                        <li class="breadcrumb-item active">Academy</li>
                                    </ol>
                                </div>

                            </div>
                        </div>
                    </div>

                    <div class="container mt-4">

                        <div class="card shadow-lg result-card text-center">

                            <div class="card-body p-4 p-md-5">

                                {{-- Result Icon --}}
                                <div class="result-icon mb-3">

                                    @if ($passed)
                                        <div class="icon-success">✓</div>
                                    @else
                                        <div class="icon-fail">✕</div>
                                    @endif

                                </div>


                                <h3 class="fw-bold mb-3">
                                    {{ $module->title }} — Results
                                </h3>


                                <h1 class="result-score {{ $passed ? 'text-success' : 'text-danger' }}">
                                    {{ number_format($percentage, 0) }}%
                                </h1>


                                @if ($passed)
                                    <p class="lead text-success mb-4">
                                        🎉 Congratulations! You passed this module.
                                    </p>

                                    <div class="academy-actions">

                                        @if ($nextModule)
                                            <a href="{{ route('affiliate.academy.show', $nextModule->id) }}"
                                                class="academy-btn academy-btn-success">
                                                Continue to Next Module →
                                            </a>
                                        @endif

                                        <a href="{{ route('affiliate.academy.index') }}"
                                            class="academy-btn academy-btn-outline">
                                            Back to Academy
                                        </a>

                                    </div>
                                @else
                                    <p class="lead text-danger mb-1">
                                        You did not reach the 80% passing score.
                                    </p>

                                    <p class="text-muted mb-4">
                                        Attempt {{ $attempts }} of 3
                                    </p>

                                    <div class="academy-actions">

                                        <a href="{{ route('affiliate.academy.show', $module->id) }}"
                                            class="academy-btn academy-btn-warning">
                                            Try Again
                                        </a>

                                        <a href="{{ route('affiliate.academy.index') }}"
                                            class="academy-btn academy-btn-outline">
                                            Return to Academy
                                        </a>

                                    </div>
                                @endif
                                {{-- Certification Unlock --}}
                                @if ($passed && !$nextModule)
                                    <hr class="my-4">

                                    <div class="certification-box">

                                        <h4 class="fw-bold text-success mb-2">
                                            🏆 Centresidence Partner Certification Unlocked
                                        </h4>

                                        <p class="text-muted mb-4">
                                            You have successfully completed all academy modules and are now
                                            an officially certified <strong>Centresidence Marketing Partner</strong>.
                                        </p>

                                        <a href="{{ route('affiliate.academy.certificate') }}"
                                            class="academy-btn academy-btn-cert">
                                            Download Certification
                                        </a>

                                    </div>
                                @endif
                                <hr class="my-4">
                                <p class="text-muted small mb-0">
                                    Complete all modules to earn your
                                    <strong>Centresidence Partner Certification</strong>.
                                    Your progress is tracked automatically.
                                </p>

                            </div>
                        </div>

                    </div>

                </div>
            </div>
        </div>
    </div>
    <style>
        .result-card{
            border-radius:16px;
            }

            .result-score{
            font-size:3rem;
            font-weight:700;
            margin-bottom:20px;
            }

            .result-icon{
            display:flex;
            justify-content:center;
            }

            .icon-success{
            width:70px;
            height:70px;
            background:#dcfce7;
            color:#16a34a;
            font-size:32px;
            display:flex;
            align-items:center;
            justify-content:center;
            border-radius:50%;
            font-weight:bold;
            }

            .icon-fail{
            width:70px;
            height:70px;
            background:#fee2e2;
            color:#dc2626;
            font-size:32px;
            display:flex;
            align-items:center;
            justify-content:center;
            border-radius:50%;
            font-weight:bold;
            }

            .academy-actions{
            display:flex;
            gap:12px;
            justify-content:center;
            flex-wrap:wrap;
            }

            .academy-btn{
            display:inline-flex;
            align-items:center;
            justify-content:center;
            padding:12px 24px;
            border-radius:10px;
            font-weight:600;
            text-decoration:none;
            transition:all .25s ease;
            min-width:200px;
            }

            .academy-btn-success{
            background:linear-gradient(135deg,#22c55e,#16a34a);
            color:white;
            }

            .academy-btn-success:hover{
            transform:translateY(-2px);
            box-shadow:0 8px 20px rgba(22,163,74,0.25);
            }

            .academy-btn-warning{
            background:linear-gradient(135deg,#f59e0b,#d97706);
            color:white;
            }

            .academy-btn-warning:hover{
            transform:translateY(-2px);
            box-shadow:0 8px 20px rgba(217,119,6,0.25);
            }

            .academy-btn-outline{
            border:2px solid #d1d5db;
            background:white;
            color:#374151;
            }

            .academy-btn-outline:hover{
            background:#f9fafb;
            transform:translateY(-2px);
            }

            .academy-btn-cert{
            background:linear-gradient(135deg,#6366f1,#4f46e5);
            color:white;
            }

            .academy-btn-cert:hover{
            transform:translateY(-2px);
            box-shadow:0 8px 20px rgba(79,70,229,0.25);
            }

            .certification-box{
            background:#f9fafb;
            padding:25px;
            border-radius:12px;
            }

            @media(max-width:576px){

            .result-score{
            font-size:2.2rem;
            }

            .academy-actions{
            flex-direction:column;
            }

            .academy-btn{
            width:100%;
            }

            .icon-success,
            .icon-fail{
            width:60px;
            height:60px;
            font-size:26px;
            }
        }
    </style>
@endsection
