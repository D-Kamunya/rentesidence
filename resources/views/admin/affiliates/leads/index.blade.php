@extends('admin.layouts.app')

@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="page-content-wrapper bg-white p-30 radius-20">

                    {{-- Page Title --}}
                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box d-sm-flex align-items-center justify-content-between border-bottom mb-4 pb-2">
                                <div class="page-title-left">
                                    <h3 class="mb-sm-0">Affiliate Leads</h3>
                                </div>
                                <div class="page-title-right">
                                    <ol class="breadcrumb mb-0">
                                        <li class="breadcrumb-item">
                                            <a href="{{ route('admin.dashboard') }}">Dashboard</a>
                                        </li>
                                        <li class="breadcrumb-item active">Leads</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Summary Cards --}}
                    <div class="row g-3 mb-4">
                        <div class="col-6 col-lg">
                            <div class="admin-stat-card">
                                <div class="admin-stat-card__icon" style="background:#f3f4f6;color:#444441;">
                                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                        <path d="M9 5H7a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2M9 5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v0a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                    </svg>
                                </div>
                                <div class="admin-stat-card__label">Total Leads</div>
                                <div class="admin-stat-card__val">{{ $leads->total() }}</div>
                            </div>
                        </div>

                        <div class="col-6 col-lg">
                            <div class="admin-stat-card" style="background:#FAEEDA;border-color:#FAC775;">
                                <div class="admin-stat-card__icon" style="background:#FEF9EE;color:#854F0B;">
                                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                        <circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.5"/>
                                        <path d="M8 5v3.5l2 1.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </div>
                                <div class="admin-stat-card__label" style="color:#854F0B;">Pending Approval</div>
                                <div class="admin-stat-card__val" style="color:#854F0B;">{{ $pendingCount ?? 0 }}</div>
                            </div>
                        </div>

                        <div class="col-6 col-lg">
                            <div class="admin-stat-card" style="background:#EEEDFE;border-color:#AFA9EC;">
                                <div class="admin-stat-card__icon" style="background:#E5E3FB;color:#534AB7;">
                                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                        <path d="M8 2l2 4h4l-3 3 1 4-4-2-4 2 1-4-3-3h4z" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </div>
                                <div class="admin-stat-card__label" style="color:#534AB7;">Active Trials</div>
                                <div class="admin-stat-card__val" style="color:#534AB7;">{{ $trialCount ?? 0 }}</div>
                            </div>
                        </div>

                        <div class="col-6 col-lg">
                            <div class="admin-stat-card" style="background:#E1F5EE;border-color:#9FE1CB;">
                                <div class="admin-stat-card__icon" style="background:#D1F0E5;color:#0F6E56;">
                                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                        <circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.5"/>
                                        <path d="M5 8.5l2 2 4-4.5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </div>
                                <div class="admin-stat-card__label" style="color:#0F6E56;">Converted</div>
                                <div class="admin-stat-card__val" style="color:#0F6E56;">{{ $convertedCount ?? 0 }}</div>
                            </div>
                        </div>

                        <div class="col-6 col-lg">
                            <div class="admin-stat-card" style="background:#EEF5FD;border-color:#B5D4F4;">
                                <div class="admin-stat-card__icon" style="background:#E6F1FB;color:#185FA5;">
                                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                        <path d="M2 10l3-3 2 2 5-5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        <circle cx="13" cy="3" r="1.5" fill="currentColor"/>
                                    </svg>
                                </div>
                                <div class="admin-stat-card__label" style="color:#185FA5;">Conv. Rate</div>
                                <div class="admin-stat-card__val" style="color:#185FA5;">{{ $conversionRate ?? '0' }}%</div>
                            </div>
                        </div>
                    </div>

                    {{-- Filters & Actions --}}
                    <div class="admin-filters-bar mb-4">
                        <form method="GET" action="{{ route('admin.leads.index') }}" class="d-flex align-items-center gap-3 flex-wrap">
                            
                            {{-- Search --}}
                            <div class="admin-filter-group" style="flex:1;min-width:200px;">
                                <input type="text" 
                                       name="search" 
                                       class="admin-filter-input" 
                                       placeholder="Search company, affiliate..."
                                       value="{{ request('search') }}">
                            </div>

                            {{-- Status Filter --}}
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

                            {{-- Temperature Filter --}}
                            <div class="admin-filter-group">
                                <select name="temperature" class="admin-filter-select">
                                    <option value="">All Temps</option>
                                    <option value="hot" {{ request('temperature') === 'hot' ? 'selected' : '' }}>🔥 Hot</option>
                                    <option value="warm" {{ request('temperature') === 'warm' ? 'selected' : '' }}>☀️ Warm</option>
                                    <option value="cold" {{ request('temperature') === 'cold' ? 'selected' : '' }}>❄️ Cold</option>
                                </select>
                            </div>

                            {{-- Buttons --}}
                            <button type="submit" class="admin-btn-filter">
                                <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
                                    <path d="M2 4h12M4 8h8M6 12h4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                </svg>
                                Filter
                            </button>

                            @if(request()->hasAny(['search', 'status', 'temperature']))
                                <a href="{{ route('admin.leads.index') }}" class="admin-btn-clear">
                                    <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
                                        <path d="M4 4l8 8M12 4l-8 8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                    </svg>
                                    Clear
                                </a>
                            @endif

                        </form>

                        {{-- Pending Approvals Quick Link --}}
                        @if($pendingCount > 0)
                            <a href="{{ route('admin.leads.index') }}?status=pending_conversion" class="admin-pending-badge">
                                <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
                                    <circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.5"/>
                                    <path d="M8 5v4M8 11v.5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                                </svg>
                                {{ $pendingCount }} Pending Approval
                            </a>
                        @endif
                    </div>

                    {{-- Table Card --}}
                    <div class="admin-table-card">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr style="border-bottom:0.5px solid #e5e7eb;background:#fafafa;">
                                        <th class="admin-th">Company</th>
                                        <th class="admin-th">Affiliate</th>
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
                                                    <div class="admin-company-avatar">
                                                        {{ strtoupper(substr($lead->company->company_name, 0, 2)) }}
                                                    </div>
                                                    <div>
                                                        <div class="admin-company-name">{{ $lead->company->company_name }}</div>
                                                        <div class="admin-company-meta">{{ $lead->contact_person_name }}</div>
                                                    </div>
                                                </div>
                                            </td>

                                            {{-- Affiliate --}}
                                            <td class="admin-td">
                                                <div style="font-size:13px;font-weight:500;color:#374151;">
                                                    {{ $lead->affiliate->first_name }} {{ $lead->affiliate->last_name }}
                                                </div>
                                                <div style="font-size:11px;color:#9ca3af;">
                                                    ID: #{{ $lead->affiliate->id }}
                                                </div>
                                            </td>

                                            {{-- Status --}}
                                            <td class="admin-td">
                                                @php 
                                                    $status = strtolower($lead->status); 
                                                    
                                                    $latestTrialActivity = $lead->latestTrialActivity();
                                                    $isExpiredTrial = $lead->status === 'pending_conversion'
                                                        && $latestTrialActivity?->type === 'trial_expired';
                                                    $isPendingExtention = $lead->status === 'pending_conversion'
                                                        && $latestTrialActivity?->type === 'trial_extention';
                                                    $isTrialRequested = $lead->status === 'pending_conversion'
                                                        && $latestTrialActivity?->type === 'trial_request';
                                                    $conversionRejectedByAdmin = $lead->status === 'demo_completed'
                                                        && $latestTrialActivity?->type === 'conversion_rejected';
                                                @endphp
                                                @if($conversionRejectedByAdmin)
                                                    <span class="admin-status-badge" style="background: #e7c6a5;border:0.5px solid #e88855;color:#854F0B;">
                                                        <svg width="9" height="9" viewBox="0 0 16 16" fill="none">
                                                            <path d="M8 2l6 12H2L8 2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                            <path d="M8 7v3M8 12v.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                                        </svg>
                                                        Trial Correction
                                                    </span>
                                                @elseif($isExpiredTrial)
                                                    <span class="admin-status-badge" style="background: #e7c6a5;border:0.5px solid #e88855;color:#854F0B;"> 
                                                        <svg width="9" height="9" viewBox="0 0 16 16" fill="none">
                                                            <circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.5"/>
                                                            <path d="M8 5v3.5l2 1.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                        </svg>
                                                        Trial expired
                                                    </span>
                                                @elseif($lead->status === 'pending_conversion' && $isPendingExtention)
                                                    <span class="admin-status-badge" style="background: #e7c6a5;border:0.5px solid #e88855;color:#854F0B;"> 
                                                        <svg width="9" height="9" viewBox="0 0 16 16" fill="none">
                                                            <circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.5"/>
                                                            <path d="M8 5v3.5l2 1.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                        </svg>
                                                        Pending Trial Extension
                                                    </span>
                                                @elseif($status === 'pending_conversion')
                                                    <span class="admin-status-badge" style="background:#FAEEDA;border-color:#FAC775;color:#854F0B;">
                                                        <svg width="9" height="9" viewBox="0 0 16 16" fill="none">
                                                            <circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.5"/>
                                                            <path d="M8 5v3.5l2 1.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                        </svg>
                                                        Pending Approval
                                                    </span>
                                                @elseif($status === 'trial')
                                                    <span class="admin-status-badge admin-status-badge--trial">
                                                        <svg width="9" height="9" viewBox="0 0 16 16" fill="none">
                                                            <path d="M8 2l2 4h4l-3 3 1 4-4-2-4 2 1-4-3-3h4z" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
                                                        </svg>
                                                        Trial
                                                    </span>
                                                @else
                                                    <span class="admin-status-badge admin-status-badge--{{ $status }}">
                                                        {{ ucfirst(str_replace('_', ' ', $lead->status)) }}
                                                    </span>
                                                @endif
                                            </td>

                                            {{-- Temperature --}}
                                            <td class="admin-td">
                                                @if($lead->isClosed())
                                                    <span class="admin-temp-badge admin-temp-badge--closed">
                                                        <svg width="10" height="10" viewBox="0 0 16 16" fill="none">
                                                            <rect x="3" y="7" width="10" height="7" rx="1.5" stroke="currentColor" stroke-width="1.6"/>
                                                            <path d="M5 7V5a3 3 0 0 1 6 0v2" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                                                        </svg>
                                                        Closed
                                                    </span>
                                                @else
                                                    @php $temp = strtolower($lead->temperature); @endphp
                                                    <span class="admin-temp-badge admin-temp-badge--{{ $temp }}">
                                                        @if($temp === 'hot')
                                                            <svg width="10" height="10" viewBox="0 0 16 16" fill="currentColor">
                                                                <path d="M8 1a5 5 0 0 1 3 9V2a3 3 0 0 0-6 0v8A5 5 0 0 1 8 1z"/>
                                                            </svg>
                                                        @elseif($temp === 'warm')
                                                            <svg width="10" height="10" viewBox="0 0 16 16" fill="none">
                                                                <circle cx="8" cy="8" r="5" stroke="currentColor" stroke-width="1.8"/>
                                                            </svg>
                                                        @else
                                                            <svg width="10" height="10" viewBox="0 0 16 16" fill="currentColor">
                                                                <path d="M8 2a6 6 0 1 0 0 12A6 6 0 0 0 8 2zm0 10a4 4 0 1 1 0-8 4 4 0 0 1 0 8z"/>
                                                            </svg>
                                                        @endif
                                                        {{ ucfirst($lead->temperature) }}
                                                    </span>
                                                @endif
                                            </td>

                                            {{-- Last Activity --}}
                                            <td class="admin-td">
                                                <span style="font-size:12px;color:#6b7280;">
                                                    {{ $lead->updated_at->diffForHumans() }}
                                                </span>
                                            </td>

                                            {{-- Expiry --}}
                                            <td class="admin-td">
                                                @if($lead->isClosed())
                                                    @if($lead->status === 'converted')
                                                        <span class="admin-expiry-badge admin-expiry-badge--converted">
                                                            <svg width="10" height="10" viewBox="0 0 16 16" fill="none">
                                                                <circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.5"/>
                                                                <path d="M4.5 8.5l2.5 2.5 4.5-5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                                                            </svg>
                                                            Converted
                                                        </span>
                                                    @elseif($lead->status === 'rejected')
                                                        <span class="admin-expiry-badge admin-expiry-badge--rejected">
                                                            <svg width="10" height="10" viewBox="0 0 16 16" fill="none">
                                                                <circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.5"/>
                                                                <path d="M5 5l6 6M11 5l-6 6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                                                            </svg>
                                                            Rejected
                                                        </span>
                                                    @elseif($lead->status === 'lost')
                                                        <span class="admin-expiry-badge admin-expiry-badge--lost">
                                                            <svg width="10" height="10" viewBox="0 0 16 16" fill="none">
                                                                <circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.5"/>
                                                                <path d="M8 5v4M8 11v.5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                                                            </svg>
                                                            Lost
                                                        </span>
                                                    @elseif($lead->status === 'expired')
                                                        <span class="admin-expiry-badge admin-expiry-badge--expired">
                                                            <svg width="10" height="10" viewBox="0 0 16 16" fill="none">
                                                                <circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.5"/>
                                                                <path d="M8 5v4M8 11v.5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                                                            </svg>
                                                            Expired
                                                        </span>
                                                    @endif
                                                @elseif($lead->status === 'trial')
                                                    <span class="admin-expiry-badge admin-expiry-badge--trial">
                                                        <svg width="10" height="10" viewBox="0 0 16 16" fill="none">
                                                            <path d="M8 2l2 4h4l-3 3 1 4-4-2-4 2 1-4-3-3h4z" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
                                                        </svg>
                                                        Active Trial
                                                    </span>
                                                @else
                                                    @php
                                                        $now = now();
                                                        $expiresAt = $lead->ownership_expires_at;
                                                        $diff = $now->diff($expiresAt);
                                                    @endphp
                                                    @if($now->greaterThan($expiresAt))
                                                        <span class="admin-expiry-badge admin-expiry-badge--expired">
                                                            <svg width="10" height="10" viewBox="0 0 16 16" fill="none">
                                                                <circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.5"/>
                                                                <path d="M8 5v4M8 11v.5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                                                            </svg>
                                                            Expired
                                                        </span>
                                                    @else
                                                        <span class="admin-expiry-badge {{ $diff->m === 0 && $diff->d <= 7 ? 'admin-expiry-badge--warning' : 'admin-expiry-badge--active' }}">
                                                            <svg width="10" height="10" viewBox="0 0 16 16" fill="none">
                                                                <circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.5"/>
                                                                <path d="M8 5v3.5l2 1.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                            </svg>
                                                            {{ $diff->m > 0 ? $diff->m . 'm ' : '' }}{{ $diff->d }}d
                                                        </span>
                                                    @endif
                                                @endif
                                            </td>

                                            {{-- Action --}}
                                            <td class="admin-td">
                                                <a href="{{ route('admin.leads.show', $lead->id) }}" class="admin-action-btn">
                                                    <svg width="12" height="12" viewBox="0 0 16 16" fill="none">
                                                        <path d="M1 8s3-5 7-5 7 5 7 5-3 5-7 5-7-5-7-5z" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                                                        <circle cx="8" cy="8" r="2" stroke="currentColor" stroke-width="1.6"/>
                                                    </svg>
                                                    View
                                                </a>
                                            </td>

                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" style="padding:3rem 1rem;text-align:center;">
                                                <svg width="36" height="36" viewBox="0 0 24 24" fill="none" style="color:#d1d5db;margin-bottom:10px;">
                                                    <path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2M9 5a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2M9 5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                                </svg>
                                                <p style="color:#9ca3af;font-size:14px;margin:0;">No leads found.</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Pagination --}}
                    @if($leads->hasPages())
                        <div class="mt-4">
                            {{ $leads->links() }}
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>

    <style>
        /* ── Summary Stat Cards ──────────────────────────────── */
        .admin-stat-card {
            background: #fafafa;
            border: 0.5px solid #e5e7eb;
            border-radius: 12px;
            padding: 1rem;
            height: 100%;
            transition: box-shadow .2s, transform .2s;
        }
        .admin-stat-card:hover {
            box-shadow: 0 4px 14px rgba(0,0,0,.06);
            transform: translateY(-2px);
        }
        .admin-stat-card__icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            border-radius: 8px;
            margin-bottom: 10px;
        }
        .admin-stat-card__label {
            font-size: 11px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: #9ca3af;
            margin-bottom: 4px;
        }
        .admin-stat-card__val {
            font-size: 26px;
            font-weight: 600;
            color: #111827;
            line-height: 1;
        }

        /* ── Filters Bar ─────────────────────────────────────── */
        .admin-filters-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
            padding: 1rem;
            background: #fafafa;
            border: 0.5px solid #e5e7eb;
            border-radius: 12px;
        }
        .admin-filters-bar form {
            flex: 1;
        }
        .admin-filter-group {
            position: relative;
        }
        .admin-filter-input,
        .admin-filter-select {
            width: 100%;
            padding: 8px 12px;
            font-size: 13px;
            color: #374151;
            background: #fff;
            border: 0.5px solid #d1d5db;
            border-radius: 8px;
            outline: none;
            transition: border-color .15s, box-shadow .15s;
        }
        .admin-filter-input:focus,
        .admin-filter-select:focus {
            border-color: #185FA5;
            box-shadow: 0 0 0 3px rgba(24,95,165,.1);
        }
        .admin-filter-select {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 16 16' fill='none'%3E%3Cpath d='M4 6l4 4 4-4' stroke='%236b7280' stroke-width='1.5' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 10px center;
            padding-right: 32px;
            cursor: pointer;
        }
        .admin-btn-filter {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            background: #185FA5;
            color: #fff;
            font-size: 13px;
            font-weight: 500;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            white-space: nowrap;
            transition: background .15s, transform .15s;
        }
        .admin-btn-filter:hover {
            background: #0C447C;
            transform: translateY(-1px);
        }
        .admin-btn-clear {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            background: #fff;
            color: #6b7280;
            font-size: 13px;
            font-weight: 500;
            border: 0.5px solid #d1d5db;
            border-radius: 8px;
            text-decoration: none;
            white-space: nowrap;
            transition: background .15s;
        }
        .admin-btn-clear:hover {
            background: #f3f4f6;
            color: #374151;
        }
        .admin-pending-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 14px;
            background: #FAEEDA;
            color: #854F0B;
            font-size: 13px;
            font-weight: 500;
            border: 0.5px solid #FAC775;
            border-radius: 8px;
            text-decoration: none;
            white-space: nowrap;
            transition: background .15s;
        }
        .admin-pending-badge:hover {
            background: #fde9b8;
            color: #854F0B;
        }

        /* ── Table Card ──────────────────────────────────────── */
        .admin-table-card {
            border: 0.5px solid #e5e7eb;
            border-radius: 12px;
            overflow: hidden;
            background: #fff;
        }
        .admin-th {
            padding: .8rem 1.1rem;
            font-size: 11px;
            font-weight: 500;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: .06em;
            border: none;
            white-space: nowrap;
        }
        .admin-td {
            padding: .85rem 1.1rem;
            border: none;
            vertical-align: middle;
        }

        /* ── Company Cell ────────────────────────────────────── */
        .admin-company-cell {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .admin-company-avatar {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            background: #E6F1FB;
            color: #185FA5;
            font-size: 12px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .admin-company-name {
            font-size: 14px;
            font-weight: 500;
            color: #111827;
        }
        .admin-company-meta {
            font-size: 12px;
            color: #9ca3af;
        }

        /* ── Status Badges ───────────────────────────────────── */
        .admin-status-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 11px;
            font-weight: 500;
            padding: 3px 10px;
            border-radius: 99px;
            border: 0.5px solid transparent;
            white-space: nowrap;
        }
        .admin-status-badge--active             { background:#E1F5EE;border-color:#9FE1CB;color:#0F6E56; }
        .admin-status-badge--demo_scheduled     { background:#E6F1FB;border-color:#B5D4F4;color:#185FA5; }
        .admin-status-badge--demo_completed     { background:#EEEDFE;border-color:#AFA9EC;color:#534AB7; }
        .admin-status-badge--converted          { background:#E1F5EE;border-color:#9FE1CB;color:#085041; }
        .admin-status-badge--rejected           { background:#FCEBEB;border-color:#F7C1C1;color:#A32D2D; }
        .admin-status-badge--expired            { background:#f3f4f6;border-color:#e5e7eb;color:#5F5E5A; }
        .admin-status-badge--lost               { background:#FAECE7;border-color:#F5C4B3;color:#993C1D; }
        .admin-status-badge--trial              { background:#EEEDFE;border-color:#AFA9EC;color:#534AB7; }


        /* ── Temperature Badges ──────────────────────────────── */
        .admin-temp-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 11px;
            font-weight: 500;
            padding: 3px 10px;
            border-radius: 99px;
        }
        .admin-temp-badge--hot    { background:#FAECE7;color:#993C1D; }
        .admin-temp-badge--warm   { background:#FAEEDA;color:#854F0B; }
        .admin-temp-badge--cold   { background:#E6F1FB;color:#185FA5; }
        .admin-temp-badge--closed { background:#f3f4f6;color:#9ca3af; }

        /* ── Expiry Badges ───────────────────────────────────── */
        .admin-expiry-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 11px;
            font-weight: 500;
            padding: 3px 10px;
            border-radius: 99px;
            white-space: nowrap;
        }
        .admin-expiry-badge--active    { background:#E1F5EE;color:#0F6E56; }
        .admin-expiry-badge--warning   { background:#FAEEDA;color:#854F0B; }
        .admin-expiry-badge--expired   { background:#FCEBEB;color:#A32D2D; }
        .admin-expiry-badge--converted { background:#E1F5EE;color:#085041; }
        .admin-expiry-badge--rejected  { background:#FCEBEB;color:#A32D2D; }
        .admin-expiry-badge--lost      { background:#FAECE7;color:#993C1D; }
        .admin-expiry-badge--trial     { background:#EEEDFE;color:#534AB7; }

        /* ── Action Button ───────────────────────────────────── */
        .admin-action-btn {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 12px;
            font-weight: 500;
            padding: 5px 12px;
            border-radius: 7px;
            border: 0.5px solid #d1d5db;
            background: #f3f4f6;
            color: #5F5E5A;
            text-decoration: none;
            white-space: nowrap;
            transition: background .15s, color .15s, transform .15s;
        }
        .admin-action-btn:hover {
            background: #e5e7eb;
            color: #111827;
            transform: translateY(-1px);
        }
    </style>

@endsection