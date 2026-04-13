@extends('affiliate.layouts.app')

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
                                    <h3 class="mb-sm-0">Leads Marketplace</h3>
                                </div>
                                <div class="page-title-right">
                                    <ol class="breadcrumb mb-0">
                                        <li class="breadcrumb-item"><a href="{{ route('affiliate.dashboard') }}">Dashboard</a></li>
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
                    @if(session('error'))
                        <div class="mod-alert mod-alert--danger mb-4">
                            <svg width="15" height="15" viewBox="0 0 16 16" fill="none" style="flex-shrink:0;">
                                <circle cx="8" cy="8" r="7" stroke="currentColor" stroke-width="1.5"/>
                                <path d="M8 5v4M8 11v.5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                            </svg>
                            <div>{{ session('error') }}</div>
                        </div>
                    @endif
                    
                    {{-- First Lead Banner - show to affiliate with zero leads --}}
                    {{-- $hasNoLeads = auth()->user()->affiliate->leads()->count() === 0; --}}
                    @if(isset($hasNoLeads) && $hasNoLeads && $leads->count() > 0)
                        <div class="mp-first-lead-banner mb-4">
                            <div class="mp-first-lead-banner__pulse"></div>
                            <div class="mp-first-lead-banner__content">
                                <div class="mp-first-lead-banner__title">
                                    🎉 Welcome to the Leads Marketplace!
                                </div>
                                <div class="mp-first-lead-banner__text">
                                    To get started, pick your first lead below. It will be added to your account with a full 60‑day window to work it.
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Intro bar --}}
                    <div class="mp-intro-bar mb-4">
                        <div class="mp-intro-bar__left">
                            <div class="mp-intro-bar__icon">
                                <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
                                    <path d="M3 3h10a1 1 0 0 1 1 1v6a1 1 0 0 1-1 1H9l-3 2v-2H3a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1z" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </div>
                            <div>
                                <div class="mp-intro-bar__text">
                                    <strong>{{ $leads->total() }} lead{{ $leads->total() != 1 ? 's' : '' }}</strong> available to claim
                                </div>
                                <div class="mp-intro-bar__sub">
                                    Claim a lead to own it for 60 days. Expired unclaimed leads cycle back automatically.
                                </div>
                            </div>
                        </div>

                        {{-- Filters --}}
                        <form method="GET" action="{{ route('affiliate.marketplace.index') }}" class="d-flex align-items-center gap-2 flex-wrap">
                            <select name="temperature" class="lc-input lc-select" style="width:auto;min-width:130px;" onchange="this.form.submit()">
                                <option value="">All temps</option>
                                <option value="hot"  {{ request('temperature') === 'hot'  ? 'selected' : '' }}>🔥 Hot</option>
                                <option value="warm" {{ request('temperature') === 'warm' ? 'selected' : '' }}>🌡 Warm</option>
                                <option value="cold" {{ request('temperature') === 'cold' ? 'selected' : '' }}>❄ Cold</option>
                            </select>
                            <select name="property_type" class="lc-input lc-select" style="width:auto;min-width:160px;" onchange="this.form.submit()">
                                <option value="">All types</option>
                                <option value="apartment"       {{ request('property_type') === 'apartment'       ? 'selected' : '' }}>Apartment</option>
                                <option value="commercial"      {{ request('property_type') === 'commercial'      ? 'selected' : '' }}>Commercial</option>
                                <option value="mixed_use"       {{ request('property_type') === 'mixed_use'       ? 'selected' : '' }}>Mixed Use</option>
                                <option value="student_housing" {{ request('property_type') === 'student_housing' ? 'selected' : '' }}>Student Housing</option>
                            </select>
                            <input type="text" name="search" value="{{ request('search') }}"
                                   class="lc-input" style="width:200px;"
                                   placeholder="Search country, city…"
                                   oninput="debounceSearch(this.form)">
                        </form>
                    </div>

                    {{-- ── LEAD CARDS GRID ──────────────────────────────── --}}
                    @if($leads->count() > 0)
                        <div class="mp-grid">
                            @foreach($leads as $lead)
                                @php
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
                                    $temp   = strtolower($lead->temperature);
                                    $isNew  = ($lead->marketplace_cycles ?? 0) === 0;

                                    // Mask company name — show first word + asterisks until claimed
                                    $nameParts  = explode(' ', $lead->company->company_name);
                                    $maskedName = $nameParts[0] . (count($nameParts) > 1 ? ' ' . str_repeat('·', strlen(implode(' ', array_slice($nameParts, 1)))) : '');

                                    // Avatar initials from visible portion
                                    $initials = strtoupper(substr($lead->company->company_name, 0, 2));
                                @endphp

                                <div class="mp-lead-card {{ $hasNoLeads ?? false ? 'mp-lead-card--first' : '' }}">

                                    {{-- Card header: avatar + name + badges --}}
                                    <div class="mp-lead-card__header">
                                        <div class="mp-lead-avatar">{{ $initials }}</div>
                                        <div style="flex:1;min-width:0;">
                                            <div class="mp-lead-name">{{ $maskedName }}</div>
                                            <div class="mp-lead-location">
                                                @if($lead->company->city)
                                                    {{ $lead->company->city }},
                                                @endif
                                                {{ $lead->company->country }}
                                            </div>
                                        </div>
                                        <div class="d-flex flex-column align-items-end gap-1">
                                            <span class="mp-temp-badge mp-temp-badge--{{ $temp }}">
                                                @if($temp === 'hot') 🔥
                                                @elseif($temp === 'warm') 🌡
                                                @else ❄ @endif
                                                {{ ucfirst($lead->temperature) }}
                                            </span>
                                            @if($isNew)
                                                <span class="mp-new-badge">New</span>
                                            @else
                                                <span class="mp-cycle-badge">× {{ $lead->marketplace_cycles }} cycles</span>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Completeness bar --}}
                                    <div class="mp-completeness-row">
                                        <div class="mp-completeness-row__bar-wrap">
                                            <div class="mp-completeness-row__bar" style="width:{{ $score }}%;background:{{ $barColor }};"></div>
                                        </div>
                                        <span class="mp-completeness-row__pct" style="color:{{ $scoreColor }};background:{{ $scoreBg }};">{{ $score }}% complete</span>
                                    </div>

                                    {{-- Lead attributes --}}
                                    <div class="mp-lead-attrs">
                                        @if($lead->company->property_type)
                                            <div class="mp-lead-attr">
                                                <span class="mp-lead-attr__label">Type</span>
                                                <span class="mp-lead-attr__val">{{ ucfirst(str_replace('_', ' ', $lead->company->property_type)) }}</span>
                                            </div>
                                        @endif
                                        @if($lead->company->estimated_units)
                                            <div class="mp-lead-attr">
                                                <span class="mp-lead-attr__label">Est. Units</span>
                                                <span class="mp-lead-attr__val">{{ $lead->company->estimated_units }}</span>
                                            </div>
                                        @endif
                                        <div class="mp-lead-attr">
                                            <span class="mp-lead-attr__label">Contact</span>
                                            <span class="mp-lead-attr__val">
                                                {{ $lead->contact_person_name ? $lead->contact_person_name : '—' }}
                                                @if($lead->contact_person_role)
                                                    <span style="color:#9ca3af;font-weight:400;"> · {{ ucfirst(str_replace('_', ' ', $lead->contact_person_role)) }}</span>
                                                @endif
                                            </span>
                                        </div>
                                        <div class="mp-lead-attr">
                                            <span class="mp-lead-attr__label">Phone</span>
                                            <span class="mp-lead-attr__val mp-lead-attr__val--masked">
                                                {{-- Phone masked: show prefix + dots --}}
                                                @php
                                                    $phone = $lead->company->phone ?? '';
                                                    $maskedPhone = strlen($phone) > 6
                                                        ? substr($phone, 0, 6) . str_repeat('·', strlen($phone) - 6)
                                                        : str_repeat('·', strlen($phone));
                                                @endphp
                                                {{ $maskedPhone ?: '—' }}
                                            </span>
                                        </div>
                                        @if($lead->company->email)
                                            <div class="mp-lead-attr">
                                                <span class="mp-lead-attr__label">Email</span>
                                                <span class="mp-lead-attr__val mp-lead-attr__val--masked">
                                                    @php
                                                        $email = $lead->company->email;
                                                        $parts = explode('@', $email);
                                                        $maskedEmail = substr($parts[0], 0, 2) . str_repeat('·', max(0, strlen($parts[0]) - 2)) . '@' . ($parts[1] ?? '');
                                                    @endphp
                                                    {{ $maskedEmail }}
                                                </span>
                                            </div>
                                        @endif
                                        @if($lead->company->website)
                                            <div class="mp-lead-attr">
                                                <span class="mp-lead-attr__label">Website</span>
                                                <span class="mp-lead-attr__val">
                                                    <svg width="10" height="10" viewBox="0 0 16 16" fill="none">
                                                        <circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.5"/>
                                                        <path d="M1.5 8h13M8 1.5a10 5 0 0 1 0 13M8 1.5a10 5 0 0 0 0 13" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                                    </svg>
                                                    Has website
                                                </span>
                                            </div>
                                        @endif
                                    </div>

                                    {{-- Added timestamp --}}
                                    <div class="mp-lead-meta">
                                        <svg width="10" height="10" viewBox="0 0 16 16" fill="none">
                                            <circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.5"/>
                                            <path d="M8 5v3.5l2 1.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                        Added {{ optional($lead->marketplace_at)->diffForHumans() ?? $lead->created_at->diffForHumans() }}
                                    </div>

                                    {{-- Claim button --}}
                                    <form method="POST" action="{{ route('affiliate.marketplace.claim', $lead->id) }}" class="mt-3">
                                        @csrf
                                        <button type="submit" class="mp-claim-btn {{ ($hasNoLeads?? false) ? 'mp-claim-btn--first' : '' }}">
                                            @if($hasNoLeads ?? false)
                                                <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
                                                    <path d="M8 2l2 4h4l-3 3 1 4-4-2-4 2 1-4-3-3h4z" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                                Claim Your First Lead
                                            @else
                                                <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
                                                    <path d="M8 2a5.5 5.5 0 1 0 0 11A5.5 5.5 0 0 0 8 2z" stroke="currentColor" stroke-width="1.5"/>
                                                    <path d="M8 5v3.5l2 1.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                                Claim Lead — 60 days
                                            @endif
                                        </button>
                                    </form>

                                </div>
                            @endforeach
                        </div>

                        {{-- Pagination --}}
                        @if($leads->hasPages())
                            <div class="mt-4">{{ $leads->links() }}</div>
                        @endif

                    @else
                        {{-- Empty state --}}
                        <div class="mp-empty">
                            <div class="mp-empty__icon">
                                <svg width="32" height="32" viewBox="0 0 24 24" fill="none">
                                    <path d="M3 3h18a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1z" stroke="currentColor" stroke-width="1.5"/>
                                    <path d="M12 9v6M9 12h6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                </svg>
                            </div>
                            <p class="mp-empty__title">No leads available right now</p>
                            <p class="mp-empty__sub">Check back soon — the admin regularly loads new leads into the marketplace.</p>
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>

    <style>
        /* ── Alerts ──────────────────────────────────────────── */
        .mod-alert { display:flex;align-items:center;gap:10px;padding:.85rem 1.1rem;border-radius:10px;font-size:14px; }
        .mod-alert--success { background:#E1F5EE;color:#0F6E56; }
        .mod-alert--danger  { background:#FCEBEB;color:#A32D2D; }

        /* ── First-lead banner ───────────────────────────────── */
        .mp-first-lead-banner {
            position:relative;overflow:hidden;
            background:linear-gradient(135deg,#185FA5 0%,#0C447C 100%);
            border-radius:14px;padding:1.25rem 1.5rem;color:#fff;
            display:flex;align-items:flex-start;gap:16px;
        }
        .mp-first-lead-banner__pulse {
            position:absolute;top:-30px;right:-30px;
            width:120px;height:120px;border-radius:50%;
            background:rgba(255,255,255,.06);pointer-events:none;
        }
        .mp-first-lead-banner__pulse::after {
            content:'';position:absolute;inset:20px;border-radius:50%;
            background:rgba(255,255,255,.06);
        }
        .mp-first-lead-banner__title { font-size:15px;font-weight:600;margin-bottom:4px; }
        .mp-first-lead-banner__text  { font-size:13px;line-height:1.6;opacity:.85; }

        /* ── Intro bar ───────────────────────────────────────── */
        .mp-intro-bar {
            display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;
            background:#fafafa;border:0.5px solid #e5e7eb;border-radius:12px;padding:1rem 1.25rem;
        }
        .mp-intro-bar__left { display:flex;align-items:center;gap:12px; }
        .mp-intro-bar__icon {
            width:32px;height:32px;border-radius:8px;background:#E6F1FB;color:#185FA5;
            display:flex;align-items:center;justify-content:center;flex-shrink:0;
        }
        .mp-intro-bar__text { font-size:13px;font-weight:500;color:#111827; }
        .mp-intro-bar__sub  { font-size:12px;color:#9ca3af;margin-top:1px; }

        /* ── Cards grid ──────────────────────────────────────── */
        .mp-grid {
            display:grid;
            grid-template-columns:repeat(auto-fill, minmax(320px, 1fr));
            gap:16px;
        }

        /* ── Lead card ───────────────────────────────────────── */
        .mp-lead-card {
            background:#fff;border:0.5px solid #e5e7eb;border-radius:14px;
            padding:1.25rem;display:flex;flex-direction:column;gap:12px;
            transition:box-shadow .2s,transform .2s,border-color .2s;
        }
        .mp-lead-card:hover {
            box-shadow:0 6px 20px rgba(0,0,0,.07);
            transform:translateY(-2px);
            border-color:#d1d5db;
        }
        /* First-lead highlight */
        .mp-lead-card--first {
            border-color:#185FA5;
            box-shadow:0 0 0 2px rgba(24,95,165,.12), 0 4px 16px rgba(24,95,165,.1);
        }
        .mp-lead-card--first:hover {
            box-shadow:0 0 0 2px rgba(24,95,165,.2), 0 8px 24px rgba(24,95,165,.14);
        }

        /* ── Card header ─────────────────────────────────────── */
        .mp-lead-card__header { display:flex;align-items:flex-start;gap:10px; }
        .mp-lead-avatar {
            width:42px;height:42px;border-radius:10px;background:#E6F1FB;color:#185FA5;
            font-size:14px;font-weight:500;display:flex;align-items:center;justify-content:center;flex-shrink:0;
        }
        .mp-lead-name { font-size:15px;font-weight:500;color:#111827;margin-bottom:2px; }
        .mp-lead-location { font-size:12px;color:#9ca3af; }

        /* ── Temperature badges ──────────────────────────────── */
        .mp-temp-badge { display:inline-flex;align-items:center;gap:4px;font-size:11px;font-weight:500;padding:3px 9px;border-radius:99px;white-space:nowrap; }
        .mp-temp-badge--hot  { background:#FAECE7;color:#993C1D; }
        .mp-temp-badge--warm { background:#FAEEDA;color:#854F0B; }
        .mp-temp-badge--cold { background:#E6F1FB;color:#185FA5; }

        /* ── New / cycle badges ──────────────────────────────── */
        .mp-new-badge   { font-size:10px;font-weight:500;padding:2px 7px;border-radius:99px;background:#E1F5EE;color:#0F6E56;white-space:nowrap; }
        .mp-cycle-badge { font-size:10px;font-weight:500;padding:2px 7px;border-radius:99px;background:#FAEEDA;color:#854F0B;white-space:nowrap; }

        /* ── Completeness row ────────────────────────────────── */
        .mp-completeness-row { display:flex;align-items:center;gap:8px; }
        .mp-completeness-row__bar-wrap { flex:1;height:5px;background:#e5e7eb;border-radius:99px;overflow:hidden; }
        .mp-completeness-row__bar      { height:100%;border-radius:99px;transition:width .4s; }
        .mp-completeness-row__pct      { font-size:10px;font-weight:500;padding:2px 7px;border-radius:99px;white-space:nowrap; }

        /* ── Attributes grid ─────────────────────────────────── */
        .mp-lead-attrs { display:flex;flex-direction:column;gap:6px; }
        .mp-lead-attr  { display:flex;justify-content:space-between;align-items:center;font-size:12px; }
        .mp-lead-attr__label { color:#9ca3af;font-weight:500; }
        .mp-lead-attr__val   { color:#111827;font-weight:500;text-align:right; }
        .mp-lead-attr__val--masked { color:#6b7280;font-family:monospace;letter-spacing:.05em; }

        /* ── Meta line ───────────────────────────────────────── */
        .mp-lead-meta {
            display:flex;align-items:center;gap:5px;
            font-size:11px;color:#9ca3af;margin-top:auto;
            padding-top:8px;border-top:0.5px solid #f3f4f6;
        }

        /* ── Claim button ────────────────────────────────────── */
        .mp-claim-btn {
            display:flex;align-items:center;justify-content:center;gap:7px;
            width:100%;padding:10px 16px;
            background:#185FA5;color:#fff;
            font-size:13px;font-weight:500;border-radius:10px;border:none;
            cursor:pointer;transition:background .2s,transform .2s,box-shadow .2s;
            text-align:center;
        }
        .mp-claim-btn:hover {
            background:#0C447C;
            transform:translateY(-1px);
            box-shadow:0 5px 14px rgba(24,95,165,.25);
        }
        .mp-claim-btn--first {
            background:linear-gradient(135deg,#185FA5 0%,#0C447C 100%);
            box-shadow:0 4px 12px rgba(24,95,165,.25);
        }
        .mp-claim-btn--first:hover {
            box-shadow:0 6px 18px rgba(24,95,165,.35);
        }

        /* ── Empty state ─────────────────────────────────────── */
        .mp-empty {
            text-align:center;padding:4rem 2rem;
            background:#fafafa;border:0.5px solid #e5e7eb;border-radius:14px;
        }
        .mp-empty__icon {
            width:64px;height:64px;border-radius:16px;background:#f3f4f6;
            display:flex;align-items:center;justify-content:center;
            margin:0 auto 16px;color:#d1d5db;
        }
        .mp-empty__title { font-size:15px;font-weight:500;color:#374151;margin:0 0 6px; }
        .mp-empty__sub   { font-size:13px;color:#9ca3af;margin:0; }

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