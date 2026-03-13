@extends('admin.layouts.app')

@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <!-- Page Content Wrapper Start -->
                <div class="page-content-wrapper bg-white p-30 radius-20">

                    {{-- Page Title --}}
                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box d-sm-flex align-items-center justify-content-between border-bottom mb-20">
                                <div class="page-title-left">
                                    <h3 class="mb-sm-0">Centresidence Academy</h3>
                                </div>
                                <div class="page-title-right">
                                    <ol class="breadcrumb mb-0">
                                        <li class="breadcrumb-item">
                                            <a href="{{ route('affiliate.dashboard') }}" title="{{ __('Dashboard') }}">
                                                {{ __('Dashboard') }}
                                            </a>
                                        </li>
                                        <li class="breadcrumb-item active" aria-current="page">
                                            Affiliates Academy Performance
                                        </li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Flash Message --}}
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    {{-- Summary Stat Cards --}}
                    <div class="row g-3 mb-4">
                        @php
                            $total      = count($affiliates);
                            $certified  = collect($affiliates)->where('certified', true)->count();
                            $inProgress = collect($affiliates)->filter(fn($a) => !$a['certified'] && $a['completed_modules'] > 0 && !$a['needs_review'])->count();
                            $needsReview = collect($affiliates)->where('needs_review', true)->count();
                        @endphp

                        <div class="col-6 col-md-3">
                            <div class="p-3 rounded-3" style="background: #f8f9fa;">
                                <div class="text-muted mb-1" style="font-size:11px;text-transform:uppercase;letter-spacing:.06em;font-weight:500;">Total Affiliates</div>
                                <div style="font-size:24px;font-weight:500;">{{ $total }}</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="p-3 rounded-3" style="background: #f0faf5;">
                                <div class="mb-1" style="font-size:11px;text-transform:uppercase;letter-spacing:.06em;font-weight:500;color:#0F6E56;">Certified</div>
                                <div style="font-size:24px;font-weight:500;color:#0F6E56;">{{ $certified }}</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="p-3 rounded-3" style="background: #eef5fd;">
                                <div class="mb-1" style="font-size:11px;text-transform:uppercase;letter-spacing:.06em;font-weight:500;color:#185FA5;">In Progress</div>
                                <div style="font-size:24px;font-weight:500;color:#185FA5;">{{ $inProgress }}</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="p-3 rounded-3" style="background: #fef2f2;">
                                <div class="mb-1" style="font-size:11px;text-transform:uppercase;letter-spacing:.06em;font-weight:500;color:#A32D2D;">Needs Review</div>
                                <div style="font-size:24px;font-weight:500;color:#A32D2D;">{{ $needsReview }}</div>
                            </div>
                        </div>
                    </div>

                    {{-- Main Table Card --}}
                    <div class="card border-0 shadow-none" style="border: 0.5px solid #e5e7eb !important; border-radius: 12px; overflow: hidden;">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead>
                                        <tr style="border-bottom: 0.5px solid #e5e7eb; background: #fafafa;">
                                            <th style="padding:.85rem 1.25rem;font-size:11px;font-weight:500;color:#6b7280;text-transform:uppercase;letter-spacing:.06em;">Affiliate</th>
                                            <th style="padding:.85rem 1.25rem;font-size:11px;font-weight:500;color:#6b7280;text-transform:uppercase;letter-spacing:.06em;width:220px;">Progress</th>
                                            <th style="padding:.85rem 1.25rem;font-size:11px;font-weight:500;color:#6b7280;text-transform:uppercase;letter-spacing:.06em;">Attempts</th>
                                            <th style="padding:.85rem 1.25rem;font-size:11px;font-weight:500;color:#6b7280;text-transform:uppercase;letter-spacing:.06em;">Status</th>
                                            <th style="padding:.85rem 1.25rem;font-size:11px;font-weight:500;color:#6b7280;text-transform:uppercase;letter-spacing:.06em;">Last Activity</th>
                                            <th style="padding:.85rem 1.25rem;font-size:11px;font-weight:500;color:#6b7280;text-transform:uppercase;letter-spacing:.06em;">Review</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($affiliates as $affiliate)
                                        <tr style="border-bottom: 0.5px solid #f3f4f6;">

                                            {{-- Affiliate Name --}}
                                            <td style="padding:.85rem 1.25rem;">
                                                <span style="font-weight:500;font-size:14px;">{{ $affiliate['name'] }}</span>
                                            </td>

                                            {{-- Progress Bar --}}
                                            <td style="padding:.85rem 1.25rem;">
                                                <div style="height:6px;background:#e5e7eb;border-radius:99px;overflow:hidden;margin-bottom:5px;">
                                                    <div style="height:100%;width:{{ $affiliate['progress_percent'] }}%;background:#1D9E75;border-radius:99px;"></div>
                                                </div>
                                                <span style="font-size:11px;color:#9ca3af;">
                                                    {{ $affiliate['completed_modules'] }} / {{ $affiliate['total_modules'] }} modules
                                                </span>
                                            </td>

                                            {{-- Attempts --}}
                                            <td style="padding:.85rem 1.25rem;">
                                                @if($affiliate['attempts'] >= 3)
                                                    <span style="color:#D85A30;font-weight:500;">
                                                        {{ $affiliate['attempts'] }}/3 &#9888;
                                                    </span>
                                                @else
                                                    <span style="color:#6b7280;">{{ $affiliate['attempts'] }}/3</span>
                                                @endif
                                            </td>

                                            {{-- Status Badge --}}
                                            <td style="padding:.85rem 1.25rem;">
                                                @if($affiliate['certified'])
                                                    <span style="display:inline-flex;align-items:center;gap:4px;font-size:11px;font-weight:500;padding:3px 10px;border-radius:99px;background:#E1F5EE;color:#0F6E56;">
                                                        &#10003; Certified
                                                    </span>
                                                @elseif($affiliate['needs_review'])
                                                    <span style="display:inline-flex;align-items:center;font-size:11px;font-weight:500;padding:3px 10px;border-radius:99px;background:#FCEBEB;color:#A32D2D;">
                                                        Needs Review
                                                    </span>
                                                @elseif($affiliate['completed_modules'] > 0)
                                                    <span style="display:inline-flex;align-items:center;font-size:11px;font-weight:500;padding:3px 10px;border-radius:99px;background:#E6F1FB;color:#185FA5;">
                                                        In Progress
                                                    </span>
                                                @else
                                                    <span style="display:inline-flex;align-items:center;font-size:11px;font-weight:500;padding:3px 10px;border-radius:99px;background:#F1EFE8;color:#5F5E5A;">
                                                        Not Started
                                                    </span>
                                                @endif
                                            </td>

                                            {{-- Last Activity --}}
                                            <td style="padding:.85rem 1.25rem;">
                                                @if($affiliate['last_activity'])
                                                    <span style="font-size:13px;color:#6b7280;">
                                                        {{ \Carbon\Carbon::parse($affiliate['last_activity'])->diffForHumans() }}
                                                    </span>
                                                @else
                                                    <span style="font-size:13px;color:#d1d5db;">No activity</span>
                                                @endif
                                            </td>

                                            {{-- Review / Reset --}}
                                            <td style="padding:.85rem 1.25rem;">
                                                @if($affiliate['needs_review'] && $affiliate['module_id'])
                                                    <form method="POST"
                                                        action="{{ route('admin.reset-module', [$affiliate['id'], $affiliate['module_id']]) }}">
                                                        @csrf
                                                        <button type="submit"
                                                            style="font-size:12px;font-weight:500;padding:5px 12px;border-radius:8px;border:0.5px solid #d1d5db;background:transparent;color:#374151;cursor:pointer;transition:background .15s;"
                                                            onmouseover="this.style.background='#f3f4f6'"
                                                            onmouseout="this.style.background='transparent'">
                                                            &#8635; Allow Retake
                                                        </button>
                                                    </form>
                                                @else
                                                    <span style="color:#1D9E75;font-size:15px;">&#10003;</span>
                                                @endif
                                            </td>

                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-5" style="color:#9ca3af;">
                                                No affiliate academy data found
                                            </td>
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