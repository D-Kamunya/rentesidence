@extends('admin.layouts.app')

@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="page-content-wrapper bg-white p-30 radius-20">

                    @php $affName = trim(($affiliate->first_name ?? '') . ' ' . ($affiliate->last_name ?? '')) ?: 'Affiliate'; @endphp
                    @php $pageTitle = $affName . ' — Leads'; @endphp

                    <div class="row">
                        <div class="col-12">
                            <a href="{{ route('admin.leads.index') }}" class="admin-back-link">
                                <svg width="14" height="14" viewBox="0 0 16 16" fill="none"><path d="M10 3L5 8l5 5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                All affiliates
                            </a>
                            <div class="page-title-box d-sm-flex align-items-center justify-content-between border-bottom mb-4 pb-2">
                                <div class="page-title-left">
                                    <h3 class="mb-sm-0">{{ $affName }}</h3>
                                    <div style="font-size:12.5px;color:#9ca3af;margin-top:2px;">#{{ $affiliate->id }}@if($affiliate->email) · {{ $affiliate->email }}@endif</div>
                                </div>
                                <div class="page-title-right">
                                    <ol class="breadcrumb mb-0">
                                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                                        <li class="breadcrumb-item"><a href="{{ route('admin.leads.index') }}">Affiliate Leads</a></li>
                                        <li class="breadcrumb-item active">{{ $affName }}</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Summary Cards (this affiliate) --}}
                    <div class="row g-3 mb-4">
                        <div class="col-6 col-lg">
                            <div class="admin-stat-card">
                                <div class="admin-stat-card__icon" style="background:#f3f4f6;color:#444441;">
                                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M9 5H7a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2M9 5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v0a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                                </div>
                                <div class="admin-stat-card__label">Total Leads</div>
                                <div class="admin-stat-card__val">{{ $leads->total() }}</div>
                            </div>
                        </div>
                        <div class="col-6 col-lg">
                            <div class="admin-stat-card" style="background:#FAEEDA;border-color:#FAC775;">
                                <div class="admin-stat-card__icon" style="background:#FEF9EE;color:#854F0B;">
                                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.5"/><path d="M8 5v3.5l2 1.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </div>
                                <div class="admin-stat-card__label" style="color:#854F0B;">Pending Approval</div>
                                <div class="admin-stat-card__val" style="color:#854F0B;">{{ $pendingCount ?? 0 }}</div>
                            </div>
                        </div>
                        <div class="col-6 col-lg">
                            <div class="admin-stat-card" style="background:#EEEDFE;border-color:#AFA9EC;">
                                <div class="admin-stat-card__icon" style="background:#E5E3FB;color:#534AB7;">
                                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M8 2l2 4h4l-3 3 1 4-4-2-4 2 1-4-3-3h4z" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </div>
                                <div class="admin-stat-card__label" style="color:#534AB7;">Active Trials</div>
                                <div class="admin-stat-card__val" style="color:#534AB7;">{{ $trialCount ?? 0 }}</div>
                            </div>
                        </div>
                        <div class="col-6 col-lg">
                            <div class="admin-stat-card" style="background:#E1F5EE;border-color:#9FE1CB;">
                                <div class="admin-stat-card__icon" style="background:#D1F0E5;color:#0F6E56;">
                                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.5"/><path d="M5 8.5l2 2 4-4.5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </div>
                                <div class="admin-stat-card__label" style="color:#0F6E56;">Converted</div>
                                <div class="admin-stat-card__val" style="color:#0F6E56;">{{ $convertedCount ?? 0 }}</div>
                            </div>
                        </div>
                        <div class="col-6 col-lg">
                            <div class="admin-stat-card" style="background:#EEF5FD;border-color:#B5D4F4;">
                                <div class="admin-stat-card__icon" style="background:#E6F1FB;color:#185FA5;">
                                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M2 10l3-3 2 2 5-5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><circle cx="13" cy="3" r="1.5" fill="currentColor"/></svg>
                                </div>
                                <div class="admin-stat-card__label" style="color:#185FA5;">Conv. Rate</div>
                                <div class="admin-stat-card__val" style="color:#185FA5;">{{ $conversionRate ?? '0' }}%</div>
                            </div>
                        </div>
                    </div>

                    {{-- Filters --}}
                    <div class="admin-filters-bar mb-4">
                        <form method="GET" action="{{ route('admin.leads.affiliate', $affiliate->id) }}" class="d-flex align-items-center gap-3 flex-wrap">
                            <div class="admin-filter-group" style="flex:1;min-width:200px;">
                                <input type="text" name="search" class="admin-filter-input" placeholder="Search company..." value="{{ request('search') }}">
                            </div>
                            <div class="admin-filter-group">
                                <select name="status" class="admin-filter-select">
                                    <option value="">All Statuses</option>
                                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="pending_conversion" {{ request('status') === 'pending_conversion' ? 'selected' : '' }}>Pending Approval</option>
                                    <option value="demo_scheduled" {{ request('status') === 'demo_scheduled' ? 'selected' : '' }}>Demo Scheduled</option>
                                    <option value="demo_completed" {{ request('status') === 'demo_completed' ? 'selected' : '' }}>Demo Completed</option>
                                    <option value="trial" {{ request('status') === 'trial' ? 'selected' : '' }}>Trial</option>
                                    <option value="converted" {{ request('status') === 'converted' ? 'selected' : '' }}>Converted</option>
                                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                                    <option value="lost" {{ request('status') === 'lost' ? 'selected' : '' }}>Lost</option>
                                    <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expired</option>
                                </select>
                            </div>
                            <div class="admin-filter-group">
                                <select name="temperature" class="admin-filter-select">
                                    <option value="">All Temps</option>
                                    <option value="hot" {{ request('temperature') === 'hot' ? 'selected' : '' }}>🔥 Hot</option>
                                    <option value="warm" {{ request('temperature') === 'warm' ? 'selected' : '' }}>☀️ Warm</option>
                                    <option value="cold" {{ request('temperature') === 'cold' ? 'selected' : '' }}>❄️ Cold</option>
                                </select>
                            </div>
                            <button type="submit" class="admin-btn-filter">
                                <svg width="14" height="14" viewBox="0 0 16 16" fill="none"><path d="M2 4h12M4 8h8M6 12h4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                                Filter
                            </button>
                            @if(request()->hasAny(['search', 'status', 'temperature']))
                                <a href="{{ route('admin.leads.affiliate', $affiliate->id) }}" class="admin-btn-clear">
                                    <svg width="14" height="14" viewBox="0 0 16 16" fill="none"><path d="M4 4l8 8M12 4l-8 8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                                    Clear
                                </a>
                            @endif
                        </form>
                        @if(($pendingCount ?? 0) > 0)
                            <a href="{{ route('admin.leads.affiliate', $affiliate->id) }}?status=pending_conversion" class="admin-pending-badge">
                                <svg width="14" height="14" viewBox="0 0 16 16" fill="none"><circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.5"/><path d="M8 5v4M8 11v.5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
                                {{ $pendingCount }} Pending Approval
                            </a>
                        @endif
                    </div>

                    {{-- Leads table --}}
                    <div class="admin-table-card">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr style="border-bottom:0.5px solid #e5e7eb;background:#fafafa;">
                                        <th class="admin-th">Company</th>
                                        <th class="admin-th">Status</th>
                                        <th class="admin-th">Temperature</th>
                                        <th class="admin-th">Last Activity</th>
                                        <th class="admin-th">Expires</th>
                                        <th class="admin-th">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($leads as $lead)
                                        <tr style="border-bottom:0.5px solid #f3f4f6;">
                                            {{-- Company --}}
                                            <td class="admin-td">
                                                <div class="admin-company-cell">
                                                    <div class="admin-company-avatar">{{ strtoupper(substr($lead->company->company_name, 0, 2)) }}</div>
                                                    <div>
                                                        <div class="admin-company-name">{{ $lead->company->company_name }}</div>
                                                        <div class="admin-company-meta">{{ $lead->contact_person_name }}</div>
                                                    </div>
                                                </div>
                                            </td>

                                            {{-- Status --}}
                                            <td class="admin-td">
                                                @php
                                                    $status = strtolower($lead->status);
                                                    $latestTrialActivity = $lead->latestTrialActivity();
                                                    $isExpiredTrial = $lead->status === 'pending_conversion' && $latestTrialActivity?->type === 'trial_expired';
                                                    $isPendingExtention = $lead->status === 'pending_conversion' && $latestTrialActivity?->type === 'trial_extention';
                                                    $conversionRejectedByAdmin = $lead->status === 'demo_completed' && $latestTrialActivity?->type === 'conversion_rejected';
                                                @endphp
                                                @if($conversionRejectedByAdmin)
                                                    <span class="admin-status-badge" style="background: #e7c6a5;border:0.5px solid #e88855;color:#854F0B;">
                                                        <svg width="9" height="9" viewBox="0 0 16 16" fill="none"><path d="M8 2l6 12H2L8 2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M8 7v3M8 12v.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                                                        Trial Correction
                                                    </span>
                                                @elseif($isExpiredTrial)
                                                    <span class="admin-status-badge" style="background: #e7c6a5;border:0.5px solid #e88855;color:#854F0B;">
                                                        <svg width="9" height="9" viewBox="0 0 16 16" fill="none"><circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.5"/><path d="M8 5v3.5l2 1.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                                        Trial expired
                                                    </span>
                                                @elseif($lead->status === 'pending_conversion' && $isPendingExtention)
                                                    <span class="admin-status-badge" style="background: #e7c6a5;border:0.5px solid #e88855;color:#854F0B;">
                                                        <svg width="9" height="9" viewBox="0 0 16 16" fill="none"><circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.5"/><path d="M8 5v3.5l2 1.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                                        Pending Trial Extension
                                                    </span>
                                                @elseif($status === 'pending_conversion')
                                                    <span class="admin-status-badge" style="background:#FAEEDA;border-color:#FAC775;color:#854F0B;">
                                                        <svg width="9" height="9" viewBox="0 0 16 16" fill="none"><circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.5"/><path d="M8 5v3.5l2 1.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                                        Pending Approval
                                                    </span>
                                                @elseif($status === 'trial')
                                                    <span class="admin-status-badge admin-status-badge--trial">
                                                        <svg width="9" height="9" viewBox="0 0 16 16" fill="none"><path d="M8 2l2 4h4l-3 3 1 4-4-2-4 2 1-4-3-3h4z" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                                        Trial
                                                    </span>
                                                @else
                                                    <span class="admin-status-badge admin-status-badge--{{ $status }}">{{ ucfirst(str_replace('_', ' ', $lead->status)) }}</span>
                                                @endif
                                            </td>

                                            {{-- Temperature --}}
                                            <td class="admin-td">
                                                @if($lead->isClosed())
                                                    <span class="admin-temp-badge admin-temp-badge--closed">
                                                        <svg width="10" height="10" viewBox="0 0 16 16" fill="none"><rect x="3" y="7" width="10" height="7" rx="1.5" stroke="currentColor" stroke-width="1.6"/><path d="M5 7V5a3 3 0 0 1 6 0v2" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
                                                        Closed
                                                    </span>
                                                @else
                                                    @php $temp = strtolower($lead->temperature); @endphp
                                                    <span class="admin-temp-badge admin-temp-badge--{{ $temp }}">
                                                        @if($temp === 'hot')
                                                            <svg width="10" height="10" viewBox="0 0 16 16" fill="currentColor"><path d="M8 1a5 5 0 0 1 3 9V2a3 3 0 0 0-6 0v8A5 5 0 0 1 8 1z"/></svg>
                                                        @elseif($temp === 'warm')
                                                            <svg width="10" height="10" viewBox="0 0 16 16" fill="none"><circle cx="8" cy="8" r="5" stroke="currentColor" stroke-width="1.8"/></svg>
                                                        @else
                                                            <svg width="10" height="10" viewBox="0 0 16 16" fill="currentColor"><path d="M8 2a6 6 0 1 0 0 12A6 6 0 0 0 8 2zm0 10a4 4 0 1 1 0-8 4 4 0 0 1 0 8z"/></svg>
                                                        @endif
                                                        {{ ucfirst($lead->temperature) }}
                                                    </span>
                                                @endif
                                            </td>

                                            {{-- Last Activity --}}
                                            <td class="admin-td"><span style="font-size:12px;color:#6b7280;">{{ $lead->updated_at->diffForHumans() }}</span></td>

                                            {{-- Expiry --}}
                                            <td class="admin-td">
                                                @if($lead->isClosed())
                                                    @if($lead->status === 'converted')
                                                        <span class="admin-expiry-badge admin-expiry-badge--converted">
                                                            <svg width="10" height="10" viewBox="0 0 16 16" fill="none"><circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.5"/><path d="M4.5 8.5l2.5 2.5 4.5-5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                                            Converted
                                                        </span>
                                                    @elseif($lead->status === 'rejected')
                                                        <span class="admin-expiry-badge admin-expiry-badge--rejected">
                                                            <svg width="10" height="10" viewBox="0 0 16 16" fill="none"><circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.5"/><path d="M5 5l6 6M11 5l-6 6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
                                                            Rejected
                                                        </span>
                                                    @elseif($lead->status === 'lost')
                                                        <span class="admin-expiry-badge admin-expiry-badge--lost">
                                                            <svg width="10" height="10" viewBox="0 0 16 16" fill="none"><circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.5"/><path d="M8 5v4M8 11v.5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
                                                            Lost
                                                        </span>
                                                    @elseif($lead->status === 'expired')
                                                        <span class="admin-expiry-badge admin-expiry-badge--expired">
                                                            <svg width="10" height="10" viewBox="0 0 16 16" fill="none"><circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.5"/><path d="M8 5v4M8 11v.5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
                                                            Expired
                                                        </span>
                                                    @endif
                                                @elseif($lead->status === 'trial')
                                                    <span class="admin-expiry-badge admin-expiry-badge--trial">
                                                        <svg width="10" height="10" viewBox="0 0 16 16" fill="none"><path d="M8 2l2 4h4l-3 3 1 4-4-2-4 2 1-4-3-3h4z" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                                        Active Trial
                                                    </span>
                                                @else
                                                    @php $now = now(); $expiresAt = $lead->ownership_expires_at; $diff = $expiresAt ? $now->diff($expiresAt) : null; @endphp
                                                    @if($expiresAt && $now->greaterThan($expiresAt))
                                                        <span class="admin-expiry-badge admin-expiry-badge--expired">
                                                            <svg width="10" height="10" viewBox="0 0 16 16" fill="none"><circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.5"/><path d="M8 5v4M8 11v.5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
                                                            Expired
                                                        </span>
                                                    @elseif($expiresAt)
                                                        <span class="admin-expiry-badge {{ $diff->m === 0 && $diff->d <= 7 ? 'admin-expiry-badge--warning' : 'admin-expiry-badge--active' }}">
                                                            <svg width="10" height="10" viewBox="0 0 16 16" fill="none"><circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.5"/><path d="M8 5v3.5l2 1.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                                            {{ $diff->m > 0 ? $diff->m . 'm ' : '' }}{{ $diff->d }}d
                                                        </span>
                                                    @else
                                                        <span style="font-size:12px;color:#c3c6cb;">—</span>
                                                    @endif
                                                @endif
                                            </td>

                                            {{-- Action --}}
                                            <td class="admin-td">
                                                <a href="{{ route('admin.leads.show', $lead->id) }}" class="admin-action-btn">
                                                    <svg width="12" height="12" viewBox="0 0 16 16" fill="none"><path d="M1 8s3-5 7-5 7 5 7 5-3 5-7 5-7-5-7-5z" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/><circle cx="8" cy="8" r="2" stroke="currentColor" stroke-width="1.6"/></svg>
                                                    View
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" style="padding:3rem 1rem;text-align:center;">
                                                <svg width="36" height="36" viewBox="0 0 24 24" fill="none" style="color:#d1d5db;margin-bottom:10px;"><path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2M9 5a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2M9 5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                                                <p style="color:#9ca3af;font-size:14px;margin:0;">No leads found for this affiliate{{ request()->hasAny(['search','status','temperature']) ? ' with these filters' : '' }}.</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    @if($leads->hasPages())
                        <div class="mt-4">{{ $leads->withQueryString()->links() }}</div>
                    @endif

                </div>
            </div>
        </div>
    </div>

    @include('admin.affiliates.leads._styles')
@endsection
