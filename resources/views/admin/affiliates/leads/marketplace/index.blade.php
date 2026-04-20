@extends('admin.layouts.app')

@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="page-content-wrapper bg-white p-30 radius-20">

                    {{-- Page Title --}}
                    @php
                        $pageTitle = 'Leads Marketplace';
                    @endphp
                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box d-sm-flex align-items-center justify-content-between border-bottom mb-4 pb-2">
                                <div class="page-title-left">
                                    <h3 class="mb-sm-0">Lead Marketplace</h3>
                                </div>
                                <div class="page-title-right">
                                    <ol class="breadcrumb mb-0">
                                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                                        <li class="breadcrumb-item active">Marketplace</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Flash messages --}}
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
                    {{--
                        Controller should pass:
                        $totalMarketplace   = Lead::where('marketplace_status','marketplace')->count()
                        $totalClaimed       = Lead::where('marketplace_status','claimed')->count()
                        $claimedThisMonth   = Lead::where('marketplace_status','claimed')->whereMonth('claimed_at', now()->month)->count()
                        $avgCompleteness    = average completeness across marketplace leads (compute in controller)
                    --}}
                    <div class="row g-3 mb-4">
                        <div class="col-6 col-md-3">
                            <div class="mp-stat">
                                <div class="mp-stat__icon" style="background:#E6F1FB;color:#185FA5;">
                                    <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
                                        <path d="M3 3h10a1 1 0 0 1 1 1v6a1 1 0 0 1-1 1H9l-3 2v-2H3a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </div>
                                <div class="mp-stat__label">Available</div>
                                <div class="mp-stat__val" style="color:#185FA5;">{{ $totalMarketplace ?? 0 }}</div>
                                <div class="mp-stat__sub">Unclaimed leads</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="mp-stat" style="background:#F0FDF4;border-color:#9FE1CB;">
                                <div class="mp-stat__icon" style="background:#E1F5EE;color:#0F6E56;">
                                    <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
                                        <path d="M3 8.5l3.5 3.5 6.5-7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </div>
                                <div class="mp-stat__label" style="color:#0F6E56;">Claimed</div>
                                <div class="mp-stat__val" style="color:#0F6E56;">{{ $totalClaimed ?? 0 }}</div>
                                <div class="mp-stat__sub" style="color:#0F6E56;">All time</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="mp-stat" style="background:#FEF9EE;border-color:#FAC775;">
                                <div class="mp-stat__icon" style="background:#FAEEDA;color:#854F0B;">
                                    <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
                                        <circle cx="8" cy="8" r="5.5" stroke="currentColor" stroke-width="1.5"/>
                                        <path d="M8 5v3.5l2 1.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </div>
                                <div class="mp-stat__label" style="color:#854F0B;">This Month</div>
                                <div class="mp-stat__val" style="color:#854F0B;">{{ $claimedThisMonth ?? 0 }}</div>
                                <div class="mp-stat__sub" style="color:#854F0B;">Claimed</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="mp-stat" style="background:#EEEDFE;border-color:#AFA9EC;">
                                <div class="mp-stat__icon" style="background:#E5E3FD;color:#534AB7;">
                                    <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
                                        <circle cx="8" cy="8" r="5.5" stroke="currentColor" stroke-width="1.5"/>
                                        <path d="M8 5v3.5l2 1.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </div>
                                <div class="mp-stat__label" style="color:#534AB7;">Avg. Completeness</div>
                                <div class="mp-stat__val" style="color:#534AB7;">{{ $avgCompleteness ?? 0 }}%</div>
                                <div class="mp-stat__sub" style="color:#534AB7;">Across all leads</div>
                            </div>
                        </div>
                    </div>

                    {{-- Header: filter + add button --}}
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
                        {{-- Filters --}}
                        <form method="GET" action="{{ route('admin.marketplace.index') }}" class="d-flex align-items-center gap-2 flex-wrap">
                            <select name="status" class="lc-input lc-select" style="width:auto;min-width:160px;" onchange="this.form.submit()">
                                <option value="">All status</option>
                                <option value="marketplace" {{ request('status') === 'marketplace' ? 'selected' : '' }}>Available</option>
                                <option value="claimed"     {{ request('status') === 'claimed'     ? 'selected' : '' }}>Claimed</option>
                            </select>
                            <select name="temperature" class="lc-input lc-select" style="width:auto;min-width:140px;" onchange="this.form.submit()">
                                <option value="">All temps</option>
                                <option value="hot"  {{ request('temperature') === 'hot'  ? 'selected' : '' }}>🔥 Hot</option>
                                <option value="warm" {{ request('temperature') === 'warm' ? 'selected' : '' }}>🌡 Warm</option>
                                <option value="cold" {{ request('temperature') === 'cold' ? 'selected' : '' }}>❄ Cold</option>
                            </select>
                            <input type="text" name="search" value="{{ request('search') }}"
                                   class="lc-input" style="width:220px;" placeholder="Search company, country…"
                                   oninput="debounceSearch(this.form)">
                        </form>

                        <a href="{{ route('admin.marketplace.create') }}" class="mp-btn-primary">
                            <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
                                <path d="M8 3v10M3 8h10" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                            </svg>
                            Add Lead to Marketplace
                        </a>
                    </div>

                    {{-- Table --}}
                    <div class="mp-card">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr style="border-bottom:0.5px solid #e5e7eb;background:#fafafa;">
                                        <th class="mp-th">Company</th>
                                        <th class="mp-th">Location</th>
                                        <th class="mp-th">Completeness</th>
                                        <th class="mp-th">Temperature</th>
                                        <th class="mp-th">Status</th>
                                        <th class="mp-th">Claimed By</th>
                                        <th class="mp-th">Cycles</th>
                                        <th class="mp-th">Added</th>
                                        <th class="mp-th">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($leads as $lead)
                                        @php
                                            // Completeness — mirrors the Lead::completenessScore() method
                                            $fields = [
                                                $lead->company->company_name    ?? null,
                                                $lead->company->country         ?? null,
                                                $lead->company->city            ?? null,
                                                $lead->company->phone           ?? null,
                                                $lead->company->email           ?? null,
                                                $lead->company->website         ?? null,
                                                $lead->company->property_type   ?? null,
                                                $lead->company->estimated_units ?? null,
                                                $lead->contact_person_name      ?? null,
                                                $lead->contact_person_role      ?? null,
                                            ];
                                            $filled  = count(array_filter($fields, fn($v) => !is_null($v) && $v !== ''));
                                            $score   = (int) round(($filled / count($fields)) * 100);
                                            $scoreColor = $score < 40 ? '#A32D2D' : ($score < 70 ? '#854F0B' : '#0F6E56');
                                            $scoreBg    = $score < 40 ? '#FCEBEB' : ($score < 70 ? '#FAEEDA' : '#E1F5EE');
                                            $barColor   = $score < 40 ? '#E24B4A' : ($score < 70 ? '#FAC775' : '#1D9E75');
                                            $temp = strtolower($lead->temperature);
                                        @endphp
                                        <tr style="border-bottom:0.5px solid #f3f4f6;">

                                            {{-- Company --}}
                                            <td class="mp-td">
                                                <div style="font-weight:500;font-size:14px;color:#111827;">{{ $lead->company->company_name }}</div>
                                                <div style="font-size:12px;color:#9ca3af;">{{ $lead->contact_person_name }}</div>
                                            </td>

                                            {{-- Location --}}
                                            <td class="mp-td">
                                                <span style="font-size:13px;color:#374151;">{{ $lead->company->city ?: '—' }}</span>
                                                <span style="font-size:12px;color:#9ca3af;display:block;">{{ $lead->company->country }}</span>
                                            </td>

                                            {{-- Completeness --}}
                                            <td class="mp-td">
                                                <div class="d-flex align-items-center gap-8" style="gap:8px;">
                                                    <div style="width:64px;height:5px;background:#e5e7eb;border-radius:99px;overflow:hidden;flex-shrink:0;">
                                                        <div style="width:{{ $score }}%;height:100%;background:{{ $barColor }};border-radius:99px;"></div>
                                                    </div>
                                                    <span style="font-size:11px;font-weight:500;color:{{ $scoreColor }};background:{{ $scoreBg }};padding:2px 7px;border-radius:99px;">
                                                        {{ $score }}%
                                                    </span>
                                                </div>
                                            </td>

                                            {{-- Temperature --}}
                                            <td class="mp-td">
                                                <span class="mp-temp-badge mp-temp-badge--{{ $temp }}">
                                                    @if($temp === 'hot') 🔥
                                                    @elseif($temp === 'warm') 🌡
                                                    @else ❄ @endif
                                                    {{ ucfirst($lead->temperature) }}
                                                </span>
                                            </td>

                                            {{-- Status --}}
                                            <td class="mp-td">
                                                @if($lead->marketplace_status === 'marketplace')
                                                    <span class="mp-status-badge mp-status-badge--available">
                                                        <span class="mp-status-dot mp-status-dot--pulse"></span>
                                                        Available
                                                    </span>
                                                @elseif($lead->marketplace_status === 'claimed')
                                                    <span class="mp-status-badge mp-status-badge--claimed">
                                                        <svg width="8" height="8" viewBox="0 0 16 16" fill="none">
                                                            <path d="M3 8.5l3.5 3.5 6.5-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                        </svg>
                                                        Claimed
                                                    </span>
                                                @else
                                                    <span class="mp-status-badge" style="background:#f3f4f6;color:#9ca3af;">—</span>
                                                @endif
                                            </td>

                                            {{-- Claimed By --}}
                                            <td class="mp-td">
                                                @if($lead->affiliate_id && $lead->affiliate)
                                                    <div style="font-size:13px;font-weight:500;color:#111827;">
                                                        {{ $lead->affiliate->first_name }} {{ $lead->affiliate->last_name }}
                                                    </div>
                                                    @if($lead->claimed_at)
                                                        <div style="font-size:11px;color:#9ca3af;">{{ $lead->claimed_at->diffForHumans() }}</div>
                                                    @endif
                                                @else
                                                    <span style="font-size:12px;color:#9ca3af;">—</span>
                                                @endif
                                            </td>

                                            {{-- Cycles --}}
                                            <td class="mp-td">
                                                @if(($lead->marketplace_cycles ?? 0) > 0)
                                                    <span style="font-size:12px;font-weight:500;color:#854F0B;background:#FAEEDA;padding:2px 8px;border-radius:99px;">
                                                        × {{ $lead->marketplace_cycles }}
                                                    </span>
                                                @else
                                                    <span style="font-size:12px;color:#9ca3af;">New</span>
                                                @endif
                                            </td>

                                            {{-- Added --}}
                                            <td class="mp-td">
                                                <span style="font-size:12px;color:#6b7280;">
                                                    {{ optional($lead->marketplace_at)->format('d M Y') ?? $lead->created_at->format('d M Y') }}
                                                </span>
                                            </td>

                                            {{-- Actions --}}
                                            <td class="mp-td">
                                                <div class="d-flex align-items-center gap-2">
                                                    <a href="{{ route('admin.marketplace.show', $lead->id) }}"
                                                       class="mp-action-btn mp-action-btn--view">
                                                        <svg width="12" height="12" viewBox="0 0 16 16" fill="none">
                                                            <path d="M1 8s3-5 7-5 7 5 7 5-3 5-7 5-7-5-7-5z" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                                                            <circle cx="8" cy="8" r="2" stroke="currentColor" stroke-width="1.6"/>
                                                        </svg>
                                                        View
                                                    </a>
                                                    {{-- Only allow pull if not yet claimed --}}
                                                    @if($lead->marketplace_status === 'marketplace')
                                                        <form method="POST" action="{{ route('admin.marketplace.destroy', $lead->id) }}" style="display:inline;"
                                                              onsubmit="return confirm('Remove this lead from the marketplace?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="mp-action-btn mp-action-btn--remove">
                                                                <svg width="12" height="12" viewBox="0 0 16 16" fill="none">
                                                                    <path d="M4 4l8 8M12 4l-8 8" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                                                                </svg>
                                                                Pull
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </td>

                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" style="padding:3rem 1rem;text-align:center;">
                                                <svg width="36" height="36" viewBox="0 0 24 24" fill="none" style="color:#d1d5db;margin-bottom:10px;display:block;margin-inline:auto;">
                                                    <path d="M3 3h18a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1z" stroke="currentColor" stroke-width="1.5"/>
                                                    <path d="M12 9v6M9 12h6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                                </svg>
                                                <p style="color:#9ca3af;font-size:14px;margin:0;">No marketplace leads yet. Add one to get started.</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Pagination --}}
                    @if($leads->hasPages())
                        <div class="mt-4">{{ $leads->links() }}</div>
                    @endif

                </div>
            </div>
        </div>
    </div>

    <style>
        /* ── Alerts ──────────────────────────────────────────── */
        .mod-alert { display:flex;align-items:center;gap:10px;padding:.85rem 1.1rem;border-radius:10px;font-size:14px; }
        .mod-alert--success { background:#E1F5EE;color:#0F6E56; }

        /* ── Stat cards ──────────────────────────────────────── */
        .mp-stat {
            background:#fafafa;border:0.5px solid #e5e7eb;border-radius:12px;
            padding:1rem;height:100%;transition:box-shadow .2s,transform .2s;
        }
        .mp-stat:hover { box-shadow:0 4px 14px rgba(0,0,0,.06);transform:translateY(-2px); }
        .mp-stat__icon { display:inline-flex;align-items:center;justify-content:center;width:32px;height:32px;border-radius:8px;margin-bottom:10px; }
        .mp-stat__label { font-size:11px;font-weight:500;text-transform:uppercase;letter-spacing:.06em;color:#9ca3af;margin-bottom:4px; }
        .mp-stat__val { font-size:24px;font-weight:500;color:#111827;line-height:1; }
        .mp-stat__sub { font-size:11px;color:#9ca3af;margin-top:4px; }

        /* ── Table card ──────────────────────────────────────── */
        .mp-card { border:0.5px solid #e5e7eb;border-radius:12px;overflow:hidden;background:#fff; }
        .mp-th { padding:.8rem 1.1rem;font-size:11px;font-weight:500;color:#6b7280;text-transform:uppercase;letter-spacing:.06em;border:none;white-space:nowrap; }
        .mp-td { padding:.85rem 1.1rem;border:none;vertical-align:middle; }

        /* ── Temperature badges ──────────────────────────────── */
        .mp-temp-badge { display:inline-flex;align-items:center;gap:5px;font-size:11px;font-weight:500;padding:3px 10px;border-radius:99px; }
        .mp-temp-badge--hot  { background:#FAECE7;color:#993C1D; }
        .mp-temp-badge--warm { background:#FAEEDA;color:#854F0B; }
        .mp-temp-badge--cold { background:#E6F1FB;color:#185FA5; }

        /* ── Status badges ───────────────────────────────────── */
        .mp-status-badge { display:inline-flex;align-items:center;gap:5px;font-size:11px;font-weight:500;padding:3px 10px;border-radius:99px;white-space:nowrap; }
        .mp-status-badge--available { background:#E1F5EE;color:#0F6E56; }
        .mp-status-badge--claimed   { background:#E6F1FB;color:#185FA5; }

        /* ── Pulsing availability dot ────────────────────────── */
        .mp-status-dot {
            width:7px;height:7px;border-radius:50%;background:#0F6E56;
            display:inline-block;flex-shrink:0;
        }
        .mp-status-dot--pulse {
            animation: mp-pulse 1.8s ease-in-out infinite;
        }
        @keyframes mp-pulse {
            0%,100% { opacity:1; transform:scale(1); }
            50%      { opacity:.5; transform:scale(1.4); }
        }

        /* ── Action buttons ──────────────────────────────────── */
        .mp-action-btn { display:inline-flex;align-items:center;gap:5px;font-size:12px;font-weight:500;padding:5px 12px;border-radius:7px;border:0.5px solid transparent;text-decoration:none;white-space:nowrap;cursor:pointer;background:none;transition:background .15s,border-color .15s,transform .15s; }
        .mp-action-btn:hover { transform:translateY(-1px); }
        .mp-action-btn--view   { background:#f3f4f6;border-color:#d1d5db;color:#5F5E5A; }
        .mp-action-btn--view:hover { background:#e5e7eb;color:#111827; }
        .mp-action-btn--remove { background:#FCEBEB;border-color:#F7C1C1;color:#A32D2D; }
        .mp-action-btn--remove:hover { background:#f9d8d8; }

        /* ── Primary button ──────────────────────────────────── */
        .mp-btn-primary { display:inline-flex;align-items:center;gap:7px;padding:9px 18px;background:#185FA5;color:#fff;font-size:13px;font-weight:500;border-radius:8px;text-decoration:none;border:none;transition:background .2s,transform .2s,box-shadow .2s; }
        .mp-btn-primary:hover { background:#0C447C;color:#fff;transform:translateY(-1px);box-shadow:0 5px 14px rgba(24,95,165,.22); }

        /* ── Form inputs ─────────────────────────────────────── */
        .lc-input { width:100%;padding:9px 12px;font-size:14px;color:#111827;background:#fff;border:0.5px solid #d1d5db;border-radius:8px;outline:none;transition:border-color .15s;appearance:none; }
        .lc-input:focus { border-color:#185FA5;box-shadow:0 0 0 3px rgba(24,95,165,.1); }
        .lc-select { background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 16 16' fill='none'%3E%3Cpath d='M4 6l4 4 4-4' stroke='%236b7280' stroke-width='1.5' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 12px center;padding-right:36px;cursor:pointer; }
    </style>

    <script>
        let searchTimer;
        function debounceSearch(form) {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(() => form.submit(), 400);
        }
    </script>
@endsection