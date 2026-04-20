@extends('admin.layouts.app')

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="page-content-wrapper bg-white p-30 radius-20">

                {{-- Page Title --}}
                @php
                        $pageTitle = 'Suggestions';
                    @endphp
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between border-bottom mb-4 pb-2">
                            <div class="page-title-left">
                                <h3 class="mb-sm-0">Suggestions</h3>
                            </div>
                            <div class="page-title-right">
                                <ol class="breadcrumb mb-0">
                                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                                    <li class="breadcrumb-item active">Suggestions</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Header --}}
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
                    <div>
                        <p class="at-subtitle">Actions suggested by the engine to affiliates based on lead status, temperature, and activity. Monitor compliance and outcomes here.</p>
                    </div>
                    <form method="POST" action="{{ route('admin.suggestions.generate') }}">
                        @csrf
                        <button type="submit" class="sg-generate-btn">
                            <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
                                <path d="M13 8A5 5 0 1 1 8 3" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                                <path d="M8 1v4l2-2" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            Generate Suggestions
                        </button>
                    </form>
                </div>

                {{-- Stats --}}
                @php
                    $byStatus   = $suggestions->groupBy('status');
                    $byPriority = $suggestions->groupBy('priority');
                @endphp
                <div class="row g-3 mb-4">
                    <div class="col-6 col-md-3">
                        <div class="at-stat">
                            <div class="at-stat__icon" style="background:#f3f4f6;color:#444441;">
                                <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
                                    <path d="M8 2l2 4h4l-3 3 1 4-4-2-4 2 1-4-3-3h4z" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </div>
                            <div class="at-stat__label">Total</div>
                            <div class="at-stat__val">{{ $suggestions->total() }}</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="at-stat" style="background:#FEF9EE;border-color:#FAC775;">
                            <div class="at-stat__icon" style="background:#FAEEDA;color:#854F0B;">
                                <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
                                    <circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.5"/>
                                    <path d="M8 5v3.5l2 1.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </div>
                            <div class="at-stat__label" style="color:#854F0B;">Pending</div>
                            <div class="at-stat__val" style="color:#854F0B;">{{ $byStatus->get('pending', collect())->count() }}</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="at-stat" style="background:#F0FDF6;border-color:#9FE1CB;">
                            <div class="at-stat__icon" style="background:#E1F5EE;color:#0F6E56;">
                                <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
                                    <circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.5"/>
                                    <path d="M4.5 8.5l2.5 2.5 4.5-5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </div>
                            <div class="at-stat__label" style="color:#0F6E56;">Completed</div>
                            <div class="at-stat__val" style="color:#0F6E56;">{{ $byStatus->get('completed', collect())->count() }}</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="at-stat" style="background:#FCEBEB;border-color:#F7C1C1;">
                            <div class="at-stat__icon" style="background:#FCEBEB;color:#A32D2D;">
                                <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
                                    <path d="M8 2l6 12H2L8 2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M8 7v3M8 12v.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                </svg>
                            </div>
                            <div class="at-stat__label" style="color:#A32D2D;">Urgent</div>
                            <div class="at-stat__val" style="color:#A32D2D;">{{ $byPriority->get('high', collect())->count() }}</div>
                        </div>
                    </div>
                </div>

                {{-- Flash --}}
                @if(session('success'))
                    <div class="at-alert at-alert--success mb-4">
                        <svg width="15" height="15" viewBox="0 0 16 16" fill="none" style="flex-shrink:0;">
                            <circle cx="8" cy="8" r="7" stroke="currentColor" stroke-width="1.5"/>
                            <path d="M4.5 8.5l2.5 2.5 4.5-5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        {{ session('success') }}
                    </div>
                @endif

                {{-- Table --}}
                <div class="at-card">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr style="border-bottom:0.5px solid #e5e7eb;background:#fafafa;">
                                    <th class="at-th">Lead</th>
                                    <th class="at-th">Affiliate</th>
                                    <th class="at-th">Suggested Action</th>
                                    <th class="at-th">Type</th>
                                    <th class="at-th">Priority</th>
                                    <th class="at-th">Status</th>
                                    <th class="at-th">Expires</th>
                                    <th class="at-th">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($suggestions as $s)
                                    <tr style="border-bottom:0.5px solid #f3f4f6;" class="{{ $s->priority === 'high' && $s->status === 'pending' ? 'sg-row--urgent' : '' }}">

                                        {{-- Lead --}}
                                        <td class="at-td">
                                            <a href="{{ route('admin.suggestions.lead', $s->lead_id) }}"
                                               style="font-weight:500;font-size:14px;color:#185FA5;text-decoration:none;">
                                                {{ $s->lead->company->company_name ?? 'Lead #'.$s->lead_id }}
                                            </a>
                                            @if($s->lead?->status)
                                                <div>
                                                    <span class="sg-lead-status sg-lead-status--{{ $s->lead->status }}">
                                                        {{ ucfirst(str_replace('_', ' ', $s->lead->status)) }}
                                                    </span>
                                                </div>
                                            @endif
                                        </td>

                                        {{-- Affiliate --}}
                                        <td class="at-td">
                                            @php
                                                $aff = $s->lead->affiliate ?? null;
                                            @endphp
                                            @if($aff)
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="sg-avatar">
                                                        {{ strtoupper(substr($aff->first_name, 0, 1)) }}{{ strtoupper(substr($aff->last_name ?? '', 0, 1)) }}
                                                    </div>
                                                    <div>
                                                        <div style="font-size:13px;font-weight:500;color:#111827;">{{ $aff->first_name }} {{ $aff->last_name }}</div>
                                                        <div style="font-size:11px;color:#9ca3af;">{{ $aff->email ?? '' }}</div>
                                                    </div>
                                                </div>
                                            @else
                                                <span style="font-size:12px;color:#d1d5db;">—</span>
                                            @endif
                                        </td>

                                        {{-- Message --}}
                                        <td class="at-td" style="max-width:280px;">
                                            <div style="font-size:13px;color:#374151;line-height:1.5;">
                                                {{ Str::limit($s->message, 80) }}
                                            </div>
                                            <div style="font-size:11px;color:#9ca3af;margin-top:3px;">
                                                {{ $s->created_at->diffForHumans() }}
                                            </div>
                                        </td>

                                        {{-- Type --}}
                                        <td class="at-td">
                                            @if($s->action_type === 'whatsapp')
                                                <span class="at-badge at-badge--whatsapp">
                                                    <svg width="10" height="10" viewBox="0 0 24 24" fill="currentColor">
                                                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L0 24l6.304-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                                                    </svg>
                                                    WhatsApp
                                                </span>
                                            @elseif($s->action_type === 'email')
                                                <span class="at-badge at-badge--email">
                                                    <svg width="10" height="10" viewBox="0 0 16 16" fill="none">
                                                        <rect x="2" y="4" width="12" height="9" rx="1.5" stroke="currentColor" stroke-width="1.5"/>
                                                        <path d="M2 5l6 4 6-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                    </svg>
                                                    Email
                                                </span>
                                            @elseif($s->action_type === 'call')
                                                <span class="at-badge at-badge--call">
                                                    <svg width="10" height="10" viewBox="0 0 16 16" fill="none">
                                                        <path d="M3.5 1h3l1.5 3.5-2 1.5a9 9 0 0 0 4 4l1.5-2L15 9.5v3a2 2 0 0 1-2 2A12 12 0 0 1 1 3a2 2 0 0 1 2-2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                    </svg>
                                                    Call
                                                </span>
                                            @endif
                                        </td>

                                        {{-- Priority --}}
                                        <td class="at-td">
                                            @if($s->priority === 'high')
                                                <span class="sg-priority sg-priority--high">
                                                    <svg width="9" height="9" viewBox="0 0 16 16" fill="none">
                                                        <path d="M8 2l6 12H2L8 2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                        <path d="M8 7v3M8 12v.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                                    </svg>
                                                    Urgent
                                                </span>
                                            @elseif($s->priority === 'medium')
                                                <span class="sg-priority sg-priority--medium">Medium</span>
                                            @else
                                                <span class="sg-priority sg-priority--low">Low</span>
                                            @endif
                                        </td>

                                        {{-- Status --}}
                                        <td class="at-td">
                                            @if($s->status === 'pending')
                                                <span class="at-badge" style="background:#FAEEDA;color:#854F0B;">
                                                    <span style="width:6px;height:6px;border-radius:50%;background:currentColor;display:inline-block;"></span>
                                                    Pending
                                                </span>
                                            @elseif($s->status === 'completed')
                                                <span class="at-badge at-badge--active">
                                                    <span style="width:6px;height:6px;border-radius:50%;background:currentColor;display:inline-block;"></span>
                                                    Completed
                                                </span>
                                            @elseif($s->status === 'dismissed')
                                                <span class="at-badge" style="background:#f3f4f6;color:#5F5E5A;">
                                                    <span style="width:6px;height:6px;border-radius:50%;background:currentColor;display:inline-block;"></span>
                                                    Dismissed
                                                </span>
                                            @elseif($s->status === 'expired')
                                                <span class="at-badge" style="background:#FCEBEB;color:#A32D2D;">
                                                    <span style="width:6px;height:6px;border-radius:50%;background:currentColor;display:inline-block;"></span>
                                                    Expired
                                                </span>
                                            @else
                                                <span class="at-badge" style="background:#f3f4f6;color:#5F5E5A;">{{ ucfirst($s->status) }}</span>
                                            @endif
                                        </td>

                                        {{-- Expires --}}
                                        <td class="at-td">
                                            @if($s->expires_at)
                                                @php
                                                    $exp = \Carbon\Carbon::parse($s->expires_at);
                                                    $isPast = $exp->isPast();
                                                    $isSoon = !$isPast && $exp->diffInHours(now()) <= 24;
                                                @endphp
                                                <span style="font-size:12px;font-weight:500;color:{{ $isPast ? '#A32D2D' : ($isSoon ? '#854F0B' : '#374151') }};">
                                                    {{ $isPast ? 'Expired ' : '' }}{{ $exp->diffForHumans() }}
                                                </span>
                                            @else
                                                <span style="font-size:12px;color:#d1d5db;">—</span>
                                            @endif
                                        </td>

                                        {{-- Actions --}}
                                        <td class="at-td">
                                            <div class="d-flex align-items-center gap-2">
                                                <a href="{{ route('admin.suggestions.lead', $s->lead_id) }}"
                                                   class="at-action-btn at-action-btn--view">
                                                    <svg width="12" height="12" viewBox="0 0 16 16" fill="none">
                                                        <path d="M1 8s3-5 7-5 7 5 7 5-3 5-7 5-7-5-7-5z" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                                                        <circle cx="8" cy="8" r="2" stroke="currentColor" stroke-width="1.6"/>
                                                    </svg>
                                                    View Lead
                                                </a>
                                                <form method="POST" action="{{ route('admin.suggestions.destroy', $s->id) }}" style="display:inline;"
                                                      onsubmit="return confirm('Delete this suggestion?')">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="at-action-btn at-action-btn--delete">
                                                        <svg width="12" height="12" viewBox="0 0 16 16" fill="none">
                                                            <path d="M3 4h10M5 4V3a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v1M6 7v5M10 7v5M4 4l1 9h6l1-9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                        </svg>
                                                        Delete
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" style="padding:3rem 1rem;text-align:center;">
                                            <svg width="36" height="36" viewBox="0 0 24 24" fill="none" style="color:#d1d5db;margin-bottom:10px;display:block;margin-left:auto;margin-right:auto;">
                                                <path d="M12 2l3 6h6l-4.5 4.5 1.5 6-6-3-6 3 1.5-6L3 8h6z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                            <p style="color:#9ca3af;font-size:14px;margin:0 0 12px;">No suggestions generated yet.</p>
                                            <form method="POST" action="{{ route('admin.suggestions.generate') }}" style="display:inline;">
                                                @csrf
                                                <button type="submit" class="sg-generate-btn">
                                                    <svg width="13" height="13" viewBox="0 0 16 16" fill="none">
                                                        <path d="M13 8A5 5 0 1 1 8 3" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                                                        <path d="M8 1v4l2-2" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                                                    </svg>
                                                    Generate Now
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Pagination --}}
                @if($suggestions->hasPages())
                    <div class="mt-4">{{ $suggestions->links() }}</div>
                @endif

            </div>
        </div>
    </div>
</div>

@include('admin.affiliates.suggestions._suggestion_styles')
@endsection