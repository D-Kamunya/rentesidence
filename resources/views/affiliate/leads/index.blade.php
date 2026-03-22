@extends('affiliate.layouts.app')

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
                                    <h3 class="mb-sm-0">My Leads</h3>
                                </div>
                                <div class="page-title-right">
                                    <ol class="breadcrumb mb-0">
                                        <li class="breadcrumb-item">
                                            <a href="{{ route('affiliate.dashboard') }}">Dashboard</a>
                                        </li>
                                        <li class="breadcrumb-item active">My Leads</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{-- Success flash --}}
                    @if(session('success'))
                        <div class="mod-alert mod-alert--success mb-4">
                            <svg width="15" height="15" viewBox="0 0 16 16" fill="none" style="flex-shrink:0;">
                                <circle cx="8" cy="8" r="7" stroke="currentColor" stroke-width="1.5"/>
                                <path d="M4.5 8.5l2.5 2.5 4.5-5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <div>{{ session('success') }}</div>
                        </div>
                    @endif
                    {{-- Summary cards --}}
                    <div class="row g-3 mb-4">
                        <div class="col-6 col-md">
                            <div class="leads-stat">
                                <div class="leads-stat__icon" style="background:#f3f4f6;color:#444441;">
                                    <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
                                        <path d="M9 5H7a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2M9 5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v0a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                    </svg>
                                </div>
                                <div class="leads-stat__label">Total</div>
                                <div class="leads-stat__val">{{ $leads->total() }}</div>
                            </div>
                        </div>
                        <div class="col-6 col-md">
                            <div class="leads-stat" style="background:#FDF4F1;border-color:#F5C4B3;">
                                <div class="leads-stat__icon" style="background:#FAECE7;color:#993C1D;">
                                    <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
                                        <path d="M8 2c0 3-3 4-3 7a3 3 0 0 0 6 0c0-3-3-4-3-7z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M6.5 11.5c.4.7 1.5 1 2.5.5" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/>
                                    </svg>
                                </div>
                                <div class="leads-stat__label" style="color:#993C1D;">Hot</div>
                                <div class="leads-stat__val" style="color:#993C1D;">{{ $leadSummary->hot ?? 0 }}</div>
                            </div>
                        </div>
                        <div class="col-6 col-md">
                            <div class="leads-stat" style="background:#FEF9EE;border-color:#FAC775;">
                                <div class="leads-stat__icon" style="background:#FAEEDA;color:#854F0B;">
                                    <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
                                        <circle cx="8" cy="8" r="5.5" stroke="currentColor" stroke-width="1.5"/>
                                        <path d="M8 5v3.5l2 1.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </div>
                                <div class="leads-stat__label" style="color:#854F0B;">Warm</div>
                                <div class="leads-stat__val" style="color:#854F0B;">{{ $leadSummary->warm ?? 0 }}</div>
                            </div>
                        </div>
                        <div class="col-6 col-md">
                            <div class="leads-stat" style="background:#EEF5FD;border-color:#B5D4F4;">
                                <div class="leads-stat__icon" style="background:#E6F1FB;color:#185FA5;">
                                    <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
                                        <path d="M8 2a6 6 0 0 0-6 6c0 1.5.5 2.8 1.4 3.9L2 14h4.5A6 6 0 1 0 8 2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </div>
                                <div class="leads-stat__label" style="color:#185FA5;">Cold</div>
                                <div class="leads-stat__val" style="color:#185FA5;">{{ $leadSummary->cold ?? 0 }}</div>
                            </div>
                        </div>
                        <div class="col-6 col-md">
                            <div class="leads-stat" style="background:#FEF2F2;border-color:#F7C1C1;">
                                <div class="leads-stat__icon" style="background:#FCEBEB;color:#A32D2D;">
                                    <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
                                        <circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.5"/>
                                        <path d="M8 5v4M8 11v.5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                                    </svg>
                                </div>
                                <div class="leads-stat__label" style="color:#A32D2D;">Expired</div>
                                <div class="leads-stat__val" style="color:#A32D2D;">{{ $leadSummary->expired ?? 0 }}</div>
                            </div>
                        </div>
                    </div>
                    {{-- Header --}}
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
                        <a href="{{ route('affiliate.leads.create') }}" class="leads-btn-primary">
                            <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
                                <path d="M8 3v10M3 8h10" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                            </svg>
                            Submit Lead
                        </a>
                    </div>

                    {{-- Table card --}}
                    <div class="leads-card">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr style="border-bottom:0.5px solid #e5e7eb;background:#fafafa;">
                                        <th class="leads-th">Company</th>
                                        <th class="leads-th">Location</th>
                                        <th class="leads-th">Temperature</th>
                                        <th class="leads-th">Status</th>
                                        <th class="leads-th">Expires</th>
                                        <th class="leads-th">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($leads as $lead)
                                        <tr style="border-bottom:0.5px solid #f3f4f6;">

                                            {{-- Company --}}
                                            <td class="leads-td">
                                                <span style="font-weight:500;font-size:14px;color:#111827;">
                                                    <div class="company-name">{{ $lead->company->company_name }}</div>
                                                    <div class="company-meta">{{ $lead->contact_person_name }}</div>
                                                </span>
                                            </td>

                                            {{-- Location (country + city merged) --}}
                                            <td class="leads-td">
                                                <span style="font-size:13px;color:#374151;">{{ $lead->company->city }}</span>
                                                <span style="font-size:12px;color:#9ca3af;display:block;">{{ $lead->company->country }}</span>
                                            </td>

                                            {{-- Temperature --}}
                                            <td class="leads-td">
                                                @if($lead->isClosed())
                                                    <span class="leads-temp-badge leads-temp-badge--closed">
                                                        <svg width="10" height="10" viewBox="0 0 16 16" fill="none">
                                                            <rect x="3" y="7" width="10" height="7" rx="1.5" stroke="currentColor" stroke-width="1.6"/>
                                                            <path d="M5 7V5a3 3 0 0 1 6 0v2" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                                                        </svg>
                                                        Closed
                                                    </span>
                                                @else
                                                    @php $temp = strtolower($lead->temperature); @endphp
                                                    <span class="leads-temp-badge leads-temp-badge--{{ $temp }}">
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
                                            
                                            {{-- Status --}}
                                            <td class="leads-td">
                                                @php
                                                    $status = strtolower($lead->status);
                                                   
                                                    $latestTrialActivity = $lead->latestTrialActivity();
                                                    $isExpiredTrial = $lead->status === 'pending_conversion'
                                                        && $latestTrialActivity?->type === 'trial_expired';
                                                    $isPendingExtention = $lead->status === 'pending_conversion'
                                                        && $latestTrialActivity?->type === 'trial_extention';
                                                    $conversionRejectedByAdmin = $lead->status === 'demo_completed'
                                                        && $latestTrialActivity?->type === 'conversion_rejected';
                                                @endphp
                                                @if($conversionRejectedByAdmin)
                                                    <span class="leads-status-badge" style="background:#FAEEDA;border:0.5px solid #FAC775;color:#854F0B;">
                                                        <svg width="9" height="9" viewBox="0 0 16 16" fill="none">
                                                            <path d="M8 2l6 12H2L8 2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                            <path d="M8 7v3M8 12v.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                                        </svg>
                                                        Needs Attention
                                                    </span>
                                                @elseif($isExpiredTrial)
                                                    <span class="leads-status-badge" style= "background: #e7c6a5;border:0.5px solid #e88855;color:#854F0B;"> 
                                                        <svg width="9" height="9" viewBox="0 0 16 16" fill="none">
                                                            <circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.5"/>
                                                            <path d="M8 5v3.5l2 1.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                        </svg>
                                                        Trial Expired
                                                    </span>
                                                @elseif($lead->status === 'pending_conversion' && $isPendingExtention)
                                                <span class="leads-status-badge" style= "background: #e7c6a5;border:0.5px solid #e88855;color:#854F0B;"> 
                                                    <svg width="9" height="9" viewBox="0 0 16 16" fill="none">
                                                        <circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.5"/>
                                                        <path d="M8 5v3.5l2 1.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                    </svg>
                                                    Pending Trial Extension
                                                </span>
                                                @elseif($status === 'pending_conversion')
                                                    <span class="leads-status-badge leads-status-badge--pending_conversion"> 
                                                        <svg width="9" height="9" viewBox="0 0 16 16" fill="none">
                                                            <circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.5"/>
                                                            <path d="M8 5v3.5l2 1.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                        </svg>
                                                        Pending Approval
                                                    </span>
                                                @else
                                                    <span class="leads-status-badge leads-status-badge--{{ $status }}">
                                                        @if($status === 'trial')
                                                            <svg width="9" height="9" viewBox="0 0 16 16" fill="none">
                                                                <path d="M8 2l2 4h4l-3 3 1 4-4-2-4 2 1-4-3-3h4z" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
                                                            </svg>
                                                        @endif
                                                        {{ ucfirst(str_replace('_', ' ', $lead->status)) }}
                                                    </span>
                                                @endif
                                            </td>
                                            {{-- Expires --}}
                                            @php
                                                $now = now();
                                                $expiresAt = $lead->ownership_expires_at;
                                            @endphp
                                            <td class="leads-td">
                                                {{-- Closed leads --}}
                                                @if($lead->isClosed())
                                                    @if($lead->status === 'converted')
                                                        <span class="leads-expiry leads-expiry--converted">
                                                            <svg width="11" height="11" viewBox="0 0 16 16" fill="none">
                                                                <circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.5"/>
                                                                <path d="M4.5 8.5l2.5 2.5 4.5-5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                                                            </svg>
                                                            Converted
                                                        </span>
                                                    @elseif($lead->status === 'rejected')
                                                        <span class="leads-expiry leads-expiry--rejected">
                                                            <svg width="11" height="11" viewBox="0 0 16 16" fill="none">
                                                                <circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.5"/>
                                                                <path d="M5 5l6 6M11 5l-6 6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                                                            </svg>
                                                            Rejected
                                                        </span>
                                                    @elseif($lead->status === 'lost')
                                                        <span class="leads-expiry leads-expiry--lost">
                                                            <svg width="11" height="11" viewBox="0 0 16 16" fill="none">
                                                                <circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.5"/>
                                                                <path d="M8 5v4M8 11v.5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                                                            </svg>
                                                            Lost
                                                        </span>
                                                    @endif

                                                {{-- Trial status - show trial badge instead of expiry --}}
                                                @elseif($lead->status === 'trial')
                                                    <span class="leads-expiry leads-expiry--trial">
                                                        <svg width="11" height="11" viewBox="0 0 16 16" fill="none">
                                                            <path d="M8 2l2 4h4l-3 3 1 4-4-2-4 2 1-4-3-3h4z" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
                                                        </svg>
                                                        Active Trial
                                                    </span>

                                                {{-- Expired --}}
                                                @elseif($now->greaterThan($expiresAt))
                                                    <span class="leads-expiry leads-expiry--expired">
                                                        <svg width="11" height="11" viewBox="0 0 16 16" fill="none">
                                                            <circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.5"/>
                                                            <path d="M8 5v4M8 11v.5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                                                        </svg>
                                                        Expired
                                                    </span>

                                                {{-- Active countdown --}}
                                                @else
                                                    @php $diff = $now->diff($expiresAt); @endphp
                                                    <span class="leads-expiry {{ $diff->m === 0 && $diff->d <= 7 ? 'leads-expiry--warning' : 'leads-expiry--active' }}">
                                                        <svg width="11" height="11" viewBox="0 0 16 16" fill="none">
                                                            <circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.5"/>
                                                            <path d="M8 5v3.5l2 1.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                        </svg>
                                                        {{ $diff->m > 0 ? $diff->m . ' month' . ($diff->m > 1 ? 's' : '') . ' ' : '' }}{{ $diff->d }} day{{ $diff->d != 1 ? 's' : '' }} left
                                                    </span>
                                                @endif
                                            </td>

                                            {{-- Action --}}
                                            <td class="leads-td">
                                                <div class="d-flex align-items-center gap-2">

                                                    <a href="{{ route('affiliate.leads.show', $lead->id) }}" class="leads-action-btn leads-action-btn--view">
                                                        <svg width="12" height="12" viewBox="0 0 16 16" fill="none">
                                                            <path d="M1 8s3-5 7-5 7 5 7 5-3 5-7 5-7-5-7-5z" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                                                            <circle cx="8" cy="8" r="2" stroke="currentColor" stroke-width="1.6"/>
                                                        </svg>
                                                        View
                                                    </a>

                                                    @if($lead->isClosed())
                                                        <span class="leads-action-btn leads-action-btn--closed">
                                                            <svg width="12" height="12" viewBox="0 0 16 16" fill="none">
                                                                <rect x="3" y="7" width="10" height="7" rx="1.5" stroke="currentColor" stroke-width="1.6"/>
                                                                <path d="M5 7V5a3 3 0 0 1 6 0v2" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                                                            </svg>
                                                            Closed
                                                        </span>

                                                    @elseif($lead->affiliate_id === auth()->id() && $lead->ownership_expires_at > now())
                                                        <a href="{{ route('affiliate.leads.edit', $lead->id) }}" class="leads-action-btn leads-action-btn--edit">
                                                            <svg width="12" height="12" viewBox="0 0 16 16" fill="none">
                                                                <path d="M11.5 2.5a1.5 1.5 0 0 1 2 2L5 13H3v-2L11.5 2.5z" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                                                            </svg>
                                                            Edit
                                                        </a>

                                                    @elseif($lead->affiliate_id === auth()->id() && $lead->ownership_expires_at < now())
                                                        <form action="{{ route('affiliate.leads.renew', $lead->id) }}" method="POST" style="display:inline;">
                                                            @csrf
                                                            <button type="submit" class="leads-action-btn leads-action-btn--renew">
                                                                <svg width="12" height="12" viewBox="0 0 16 16" fill="none">
                                                                    <path d="M13 8A5 5 0 1 1 8 3" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                                                                    <path d="M8 1v4l2-2" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                                                                </svg>
                                                                Renew
                                                            </button>
                                                        </form>

                                                    @endif

                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" style="padding:3rem 1rem;text-align:center;">
                                                <svg width="36" height="36" viewBox="0 0 24 24" fill="none" style="color:#d1d5db;margin-bottom:10px;">
                                                    <path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2M9 5a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2M9 5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                                </svg>
                                                <p style="color:#9ca3af;font-size:14px;margin:0;">No leads submitted yet.</p>
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
        /* ── Primary button ──────────────────────────────────── */
        .leads-btn-primary {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 9px 18px;
            background: #185FA5;
            color: #fff;
            font-size: 13px;
            font-weight: 500;
            border-radius: 8px;
            text-decoration: none;
            border: none;
            transition: background .2s, transform .2s, box-shadow .2s;
        }
        .leads-btn-primary:hover {
            background: #0C447C;
            color: #fff;
            transform: translateY(-1px);
            box-shadow: 0 5px 14px rgba(24,95,165,.22);
        }

   
        /* ── Action buttons ──────────────────────────────────── */
        .leads-action-btn {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 12px;
            font-weight: 500;
            padding: 5px 12px;
            border-radius: 7px;
            border: 0.5px solid transparent;
            text-decoration: none;
            white-space: nowrap;
            transition: background .15s, border-color .15s, transform .15s;
        }
        .leads-action-btn:hover { transform: translateY(-1px); }

        .leads-action-btn--edit  { background:#EEF5FD; border-color:#B5D4F4; color:#185FA5; }
        .leads-action-btn--edit:hover  { background:#dbeeff; color:#185FA5; }

        .leads-action-btn--renew { background:#FAEEDA; border-color:#FAC775; color:#854F0B; }
        .leads-action-btn--renew:hover { background:#fde9b8; color:#854F0B; }

        .leads-action-btn--view  { background:#f3f4f6; border-color:#d1d5db; color:#5F5E5A; }
        .leads-action-btn--view:hover  { background:#e5e7eb; color:#111827; }
        .leads-action-btn--closed {
            background: #f3f4f6;
            border-color: #e5e7eb;
            color: #9ca3af;
            cursor: default;
        }

        /* ── Action column divider ───────────────────────────── */
        .leads-td .d-flex .leads-action-btn + .leads-action-btn,
        .leads-td .d-flex form + .leads-action-btn,
        .leads-td .d-flex .leads-action-btn + form {
            padding-left: 8px;
            border-left: 0.5px solid #e5e7eb;
            border-radius: 0;
        }
        .leads-td .d-flex .leads-action-btn + form button {
            border-radius: 0 7px 7px 0;
        }

        /* ── Table card ──────────────────────────────────────── */
        .leads-card {
            border: 0.5px solid #e5e7eb;
            border-radius: 12px;
            overflow: hidden;
            background: #fff;
        }
        .leads-th {
            padding: .8rem 1.1rem;
            font-size: 11px;
            font-weight: 500;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: .06em;
            border: none;
            white-space: nowrap;
        }
        .leads-td {
            padding: .85rem 1.1rem;
            border: none;
            vertical-align: middle;
        }

        /* ── Alert ───────────────────────────────────────────── */
        .mod-alert {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: .85rem 1.1rem;
            border-radius: 10px;
            font-size: 14px;
        }
        .mod-alert--success { background: #E1F5EE; color: #0F6E56; }

        /* ── Temperature badges ──────────────────────────────── */
        .leads-temp-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 11px;
            font-weight: 500;
            padding: 3px 10px;
            border-radius: 99px;
        }
        .leads-temp-badge--hot  { background: #FAECE7; color: #993C1D; }
        .leads-temp-badge--warm { background: #FAEEDA; color: #854F0B; }
        .leads-temp-badge--cold { background: #E6F1FB; color: #185FA5; }
        .leads-temp-badge--closed { background: #f3f4f6; color: #9ca3af; }


        /* ── Status badges ───────────────────────────────────── */
        .leads-status-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 11px;
            font-weight: 500;
            padding: 3px 10px;
            border-radius: 99px;
            white-space: nowrap;
        }
        .leads-status-badge--active           { background: #E1F5EE; color: #0F6E56; }
        .leads-status-badge--demo_scheduled   { background: #E6F1FB; color: #185FA5; }
        .leads-status-badge--demo_completed   { background: #EEEDFE; color: #534AB7; }
        .leads-status-badge--pending_conversion { background: #EEEDFE; color: #3C3489; }
        .leads-status-badge--trial            { background: #EEEDFE; color: #534AB7; }
        .leads-status-badge--converted        { background: #E1F5EE; color: #085041; }
        .leads-status-badge--rejected         { background: #FCEBEB; color: #A32D2D; }
        .leads-status-badge--expired          { background: #f3f4f6; color: #5F5E5A; }
        .leads-status-badge--lost             { background: #FAECE7; color: #993C1D; }

        /* ── Expiry badges ───────────────────────────────────── */
        .leads-expiry {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 11px;
            font-weight: 500;
            padding: 3px 10px;
            border-radius: 99px;
            white-space: nowrap;
        }
        .leads-expiry--active  { background: #E1F5EE; color: #0F6E56; }
        .leads-expiry--warning { background: #FAEEDA; color: #854F0B; }
        .leads-expiry--expired { background: #FCEBEB; color: #A32D2D; }
        .leads-expiry--converted { background: #E1F5EE; color: #085041; }
        .leads-expiry--rejected  { background: #FCEBEB; color: #A32D2D; }
        .leads-expiry--lost      { background: #FAECE7; color: #993C1D; }
        .leads-expiry--trial       { background:#EEEDFE;color:#534AB7; }

        /* ── Pending conversion ───────────────────────────────────── */
        .leads-status-badge--pending_conversion { background:#EEEDFE;color:#3C3489; }

        /* ── Summary stat cards ──────────────────────────────── */
        .leads-stat {
            background: #fafafa;
            border: 0.5px solid #e5e7eb;
            border-radius: 12px;
            padding: 1rem;
            height: 100%;
            transition: box-shadow .2s, transform .2s;
        }
        .leads-stat:hover {
            box-shadow: 0 4px 14px rgba(0,0,0,.06);
            transform: translateY(-2px);
        }
        .leads-stat__icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border-radius: 8px;
            margin-bottom: 10px;
        }
        .leads-stat__label {
            font-size: 11px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: #9ca3af;
            margin-bottom: 4px;
        }
        .leads-stat__val {
            font-size: 24px;
            font-weight: 500;
            color: #111827;
            line-height: 1;
        }

        /* ── Index page names ──────────────────────────────────────── */
        .company-name {
            font-size: 14px;
            font-weight: 500;
            color: #111827;
        }
        .company-meta {
            font-size: 12px;
            color: #9ca3af;
        }
    </style>
@endsection