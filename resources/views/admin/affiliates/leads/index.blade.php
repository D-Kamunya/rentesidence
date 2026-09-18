@extends('admin.layouts.app')

@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="page-content-wrapper bg-white p-30 radius-20">

                    @php $pageTitle = 'Affiliate Leads'; @endphp
                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box d-sm-flex align-items-center justify-content-between border-bottom mb-4 pb-2">
                                <div class="page-title-left">
                                    <h3 class="mb-sm-0">Affiliate Leads</h3>
                                </div>
                                <div class="page-title-right">
                                    <ol class="breadcrumb mb-0">
                                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                                        <li class="breadcrumb-item active">Affiliate Leads</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Summary Cards (platform-wide) --}}
                    <div class="row g-3 mb-4">
                        <div class="col-6 col-lg">
                            <div class="admin-stat-card">
                                <div class="admin-stat-card__icon" style="background:#f3f4f6;color:#444441;">
                                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M9 5H7a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2M9 5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v0a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                                </div>
                                <div class="admin-stat-card__label">Total Leads</div>
                                <div class="admin-stat-card__val">{{ $totalLeads ?? 0 }}</div>
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

                    {{-- Search (by affiliate) --}}
                    <div class="admin-filters-bar mb-4">
                        <form method="GET" action="{{ route('admin.leads.index') }}" class="d-flex align-items-center gap-3 flex-wrap">
                            <div class="admin-filter-group" style="flex:1;min-width:220px;">
                                <input type="text" name="search" class="admin-filter-input" placeholder="Search affiliate by name or email..." value="{{ request('search') }}">
                            </div>
                            <button type="submit" class="admin-btn-filter">
                                <svg width="14" height="14" viewBox="0 0 16 16" fill="none"><circle cx="7" cy="7" r="5" stroke="currentColor" stroke-width="1.6"/><path d="M11 11l3 3" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
                                Search
                            </button>
                            @if(request()->filled('search'))
                                <a href="{{ route('admin.leads.index') }}" class="admin-btn-clear">
                                    <svg width="14" height="14" viewBox="0 0 16 16" fill="none"><path d="M4 4l8 8M12 4l-8 8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                                    Clear
                                </a>
                            @endif
                        </form>
                    </div>

                    {{-- Affiliates table --}}
                    <div class="admin-table-card">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr style="border-bottom:0.5px solid #e5e7eb;background:#fafafa;">
                                        <th class="admin-th">Affiliate</th>
                                        <th class="admin-th">Leads</th>
                                        <th class="admin-th">Last Activity</th>
                                        <th class="admin-th">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($affiliates as $aff)
                                        <tr style="border-bottom:0.5px solid #f3f4f6;">
                                            <td class="admin-td">
                                                <div class="admin-company-cell">
                                                    <div class="admin-company-avatar admin-aff-avatar">
                                                        {{ strtoupper(substr($aff->first_name ?? 'A', 0, 1) . substr($aff->last_name ?? '', 0, 1)) }}
                                                    </div>
                                                    <div>
                                                        <div class="admin-company-name">{{ trim(($aff->first_name ?? '') . ' ' . ($aff->last_name ?? '')) ?: 'Affiliate' }}</div>
                                                        <div class="admin-company-meta">#{{ $aff->id }}@if($aff->email) · {{ $aff->email }}@endif</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="admin-td">
                                                <div class="aff-counts">
                                                    <span class="aff-count aff-count--total"><b>{{ $aff->total_leads }}</b> total</span>
                                                    @if($aff->pending_leads > 0)<span class="aff-count aff-count--pending"><b>{{ $aff->pending_leads }}</b> pending</span>@endif
                                                    @if($aff->trial_leads > 0)<span class="aff-count aff-count--trial"><b>{{ $aff->trial_leads }}</b> trial</span>@endif
                                                    @if($aff->converted_leads > 0)<span class="aff-count aff-count--converted"><b>{{ $aff->converted_leads }}</b> converted</span>@endif
                                                </div>
                                            </td>
                                            <td class="admin-td">
                                                <span style="font-size:12px;color:#6b7280;">
                                                    {{ $aff->last_activity ? \Carbon\Carbon::parse($aff->last_activity)->diffForHumans() : '—' }}
                                                </span>
                                            </td>
                                            <td class="admin-td">
                                                <a href="{{ route('admin.leads.affiliate', $aff->id) }}" class="admin-action-btn admin-action-btn--primary">
                                                    <svg width="12" height="12" viewBox="0 0 16 16" fill="none"><path d="M1 8s3-5 7-5 7 5 7 5-3 5-7 5-7-5-7-5z" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/><circle cx="8" cy="8" r="2" stroke="currentColor" stroke-width="1.6"/></svg>
                                                    View leads
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" style="padding:3rem 1rem;text-align:center;">
                                                <svg width="36" height="36" viewBox="0 0 24 24" fill="none" style="color:#d1d5db;margin-bottom:10px;"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8zM23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                                <p style="color:#9ca3af;font-size:14px;margin:0;">{{ request('search') ? 'No affiliates match your search.' : 'No affiliates have leads yet.' }}</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    @if($affiliates->hasPages())
                        <div class="mt-4">{{ $affiliates->withQueryString()->links() }}</div>
                    @endif

                </div>
            </div>
        </div>
    </div>

    @include('admin.affiliates.leads._styles')
@endsection
