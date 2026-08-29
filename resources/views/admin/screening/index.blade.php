@extends('admin.layouts.app')

@section('content')
@php $sym = getCurrencySymbol(); @endphp
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">

            <div class="asc-head">
                <nav aria-label="breadcrumb">
                    <ol class="asc-breadcrumb">
                        <li><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
                        <li aria-current="page">{{ __('Tenant Screening') }}</li>
                    </ol>
                </nav>
                <h2 class="asc-title">{{ __('Tenant Screening') }}</h2>
                <p class="asc-sub">{{ __('Tune the screening monetization and review tenant disputes — the fairness backstop for the Global Tenant ID.') }}</p>
            </div>

            @if (session('success'))<div class="asc-flash asc-flash--ok">{{ session('success') }}</div>@endif
            @if (session('error'))<div class="asc-flash asc-flash--err">{{ session('error') }}</div>@endif

            {{-- Stats --}}
            <div class="asc-stats">
                <div class="asc-stat"><span class="asc-stat__n">{{ number_format($stats['lookups_total']) }}</span><span class="asc-stat__l">{{ __('Lookups all-time') }}</span></div>
                <div class="asc-stat"><span class="asc-stat__n">{{ number_format($stats['lookups_month']) }}</span><span class="asc-stat__l">{{ __('Lookups this month') }}</span></div>
                <div class="asc-stat"><span class="asc-stat__n">{{ number_format($stats['disputes_open']) }}</span><span class="asc-stat__l">{{ __('Open disputes') }}</span></div>
            </div>

            <div class="asc-grid">
                {{-- Settings --}}
                <div class="asc-card">
                    <h3 class="asc-card__title">{{ __('Monetization') }}</h3>
                    <form action="{{ route('admin.screening.settings') }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="asc-field">
                            <label>{{ __('Price per lookup') }} ({{ $sym }})</label>
                            <input type="number" name="screening_price" step="0.01" min="0" value="{{ $price }}" class="asc-input" required>
                            <small>{{ __('What a free-plan owner pays for one screening credit.') }}</small>
                        </div>
                        <div class="asc-field">
                            <label>{{ __('Free lookups / month') }}</label>
                            <input type="number" name="screening_free_quota" min="0" max="1000" value="{{ $freeQuota }}" class="asc-input" required>
                            <small>{{ __('Monthly free allowance for free-plan owners before credits are used. Paid plans include screening.') }}</small>
                        </div>
                        <button type="submit" class="asc-btn asc-btn--primary">{{ __('Save settings') }}</button>
                    </form>
                </div>

                {{-- Disputes --}}
                <div class="asc-card">
                    <h3 class="asc-card__title">{{ __('Tenant disputes') }}</h3>
                    @if ($disputes->count())
                        <div class="asc-disputes">
                            @foreach ($disputes as $d)
                                <div class="asc-dispute">
                                    <div class="asc-dispute__top">
                                        <span class="asc-dispute__who">
                                            {{ optional($d->user)->first_name ? trim($d->user->first_name . ' ' . $d->user->last_name) : __('Tenant') }}
                                            @if ($d->profile)<span class="asc-dispute__phone">· {{ $d->profile->phone }}</span>@endif
                                        </span>
                                        <span class="asc-dispute__status asc-dispute__status--{{ $d->status }}">{{ ucfirst($d->status) }}</span>
                                    </div>
                                    <p class="asc-dispute__msg">{{ $d->message }}</p>

                                    @if ($d->tenant_reply)
                                        <div class="asc-dispute__reply"><i class="ri-reply-line"></i> <span><strong>{{ __('Tenant follow-up:') }}</strong> {{ $d->tenant_reply }}</span></div>
                                    @endif
                                    @if ($d->tenant_ack_at)
                                        <div class="asc-dispute__ack"><i class="ri-check-double-line"></i> {{ __('Tenant confirmed this was resolved') }} · {{ $d->tenant_ack_at->diffForHumans() }}</div>
                                    @endif
                                    @if ($d->profile)
                                        <p class="asc-dispute__ctx">
                                            {{ __('Current score') }}: <strong>{{ $d->profile->score !== null ? rtrim(rtrim(number_format($d->profile->score, 1), '0'), '.') : '—' }}</strong>
                                            ({{ ucfirst($d->profile->score_band ?? 'unrated') }}) · {{ number_format($d->profile->invoices_paid) }}/{{ number_format($d->profile->invoices_total) }} {{ __('paid') }}
                                            · {{ $d->profile->on_time_rate !== null ? number_format($d->profile->on_time_rate, 0) . '%' : '—' }} {{ __('on-time') }}
                                        </p>
                                    @endif

                                    <div class="asc-dispute__remedy">
                                        <i class="ri-lightbulb-flash-line"></i>
                                        <span>{{ __('If this is about payments made off-system (cash/other), the fix is to record them: the owner marks those invoices paid, then recompute below — we never hand-edit the score.') }}</span>
                                    </div>

                                    <form action="{{ route('admin.screening.disputes.update', $d->id) }}" method="POST" class="asc-dispute__form">
                                        @csrf
                                        @method('PUT')
                                        <div class="asc-dispute__row1">
                                            <select name="status" class="asc-input asc-input--sm">
                                                @foreach (['open','reviewing','resolved','rejected'] as $st)
                                                    <option value="{{ $st }}" @selected($d->status === $st)>{{ ucfirst($st) }}</option>
                                                @endforeach
                                            </select>
                                            <input type="text" name="admin_note" class="asc-input asc-input--sm" placeholder="{{ __('Internal note (not shown to tenant)') }}" value="{{ $d->admin_note }}">
                                        </div>
                                        <textarea name="resolution" class="asc-input" rows="2" placeholder="{{ __('Reply to the tenant (shown on their Rental Score page)…') }}">{{ $d->resolution }}</textarea>
                                        <div class="asc-dispute__actions">
                                            <button type="submit" class="asc-btn asc-btn--primary">{{ __('Save & reply') }}</button>
                                        </div>
                                    </form>
                                    @if ($d->profile)
                                        <div class="asc-dispute__tools">
                                            <form action="{{ route('admin.screening.recompute', $d->profile->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="asc-btn asc-btn--ghost">{{ __('Recompute score from records') }}</button>
                                            </form>
                                            <form action="{{ route('admin.screening.disputes.notify', $d->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="asc-btn asc-btn--ghost">
                                                    {{ $d->owner_notified_at ? __('Owner(s) notified — notify again') : __('Ask owner(s) to reconcile') }}
                                                </button>
                                            </form>
                                            @if ($d->owner_notified_at)
                                                <span class="asc-dispute__sent">{{ __('sent') }} {{ $d->owner_notified_at->diffForHumans() }}</span>
                                            @endif
                                        </div>
                                    @endif
                                    <span class="asc-dispute__date">{{ __('Raised') }} {{ $d->created_at->diffForHumans() }}@if($d->resolved_at) · {{ __('answered') }} {{ $d->resolved_at->diffForHumans() }}@endif</span>
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-3">{{ $disputes->links() }}</div>
                    @else
                        <p class="asc-empty">{{ __('No disputes have been raised.') }}</p>
                    @endif
                </div>
            </div>

        </div>
    </div>
</div>

<style>
    .asc-head { margin-bottom:20px; }
    .asc-breadcrumb { display:flex; align-items:center; gap:6px; list-style:none; padding:0; margin:0 0 8px; font-size:12px; color:#9ca3af; }
    .asc-breadcrumb li:not(:last-child)::after { content:''; display:inline-block; width:5px; height:5px; border-right:1.5px solid #d1d5db; border-top:1.5px solid #d1d5db; transform:rotate(45deg); margin-left:6px; opacity:.6; }
    .asc-breadcrumb a { color:#185FA5; text-decoration:none; font-weight:500; }
    .asc-title { font-size:22px; font-weight:600; color:#111827; margin:0 0 5px; }
    .asc-sub { font-size:13.5px; color:#6b7280; margin:0; max-width:70ch; line-height:1.6; }
    .asc-flash { padding:11px 15px; border-radius:10px; font-size:13.5px; margin-bottom:16px; }
    .asc-flash--ok { background:#E1F5EE; color:#0F6E56; border:0.5px solid #A7DFC9; }
    .asc-flash--err { background:#FBE9E7; color:#B42318; border:0.5px solid #F3C4BC; }
    .asc-stats { display:grid; grid-template-columns:repeat(3,1fr); gap:14px; margin-bottom:20px; }
    @media (max-width:600px){ .asc-stats { grid-template-columns:1fr; } }
    .asc-stat { background:#fff; border:0.5px solid #e5e7eb; border-radius:14px; padding:18px; }
    .asc-stat__n { display:block; font-size:26px; font-weight:800; color:#111827; }
    .asc-stat__l { font-size:12px; color:#6b7280; }
    .asc-grid { display:grid; grid-template-columns:340px 1fr; gap:20px; align-items:start; }
    @media (max-width:900px){ .asc-grid { grid-template-columns:1fr; } }
    .asc-card { background:#fff; border:0.5px solid #e5e7eb; border-radius:16px; padding:22px; }
    .asc-card__title { font-size:15px; font-weight:600; color:#111827; margin:0 0 16px; }
    .asc-field { margin-bottom:16px; }
    .asc-field label { display:block; font-size:12px; font-weight:600; color:#374151; margin-bottom:6px; }
    .asc-field small { display:block; font-size:11.5px; color:#9ca3af; margin-top:5px; line-height:1.5; }
    .asc-input { width:100%; padding:9px 12px; border:0.5px solid #d1d5db; border-radius:9px; font-size:14px; outline:none; }
    .asc-input:focus { border-color:#185FA5; box-shadow:0 0 0 3px rgba(24,95,165,.1); }
    .asc-input--sm { padding:7px 10px; font-size:13px; width:auto; }
    .asc-btn { border:none; border-radius:9px; font-size:13px; font-weight:600; padding:10px 18px; cursor:pointer; }
    .asc-btn--primary { background:#185FA5; color:#fff; }
    .asc-btn--primary:hover { background:#0F4A84; }
    .asc-btn--ghost { background:#f3f4f6; color:#374151; border:0.5px solid #e5e7eb; }
    .asc-btn--ghost:hover { background:#e5e7eb; }
    .asc-disputes { display:flex; flex-direction:column; gap:14px; }
    .asc-dispute { border:0.5px solid #eef2f6; border-radius:12px; padding:16px; }
    .asc-dispute__top { display:flex; align-items:center; justify-content:space-between; gap:10px; margin-bottom:8px; }
    .asc-dispute__who { font-size:13.5px; font-weight:600; color:#111827; }
    .asc-dispute__phone { font-weight:400; color:#9ca3af; font-size:12px; }
    .asc-dispute__status { font-size:11px; font-weight:600; padding:3px 10px; border-radius:99px; background:#eef2f6; color:#4b5563; }
    .asc-dispute__status--open { background:#FEF3E7; color:#B45309; }
    .asc-dispute__status--reviewing { background:#E6F1FB; color:#185FA5; }
    .asc-dispute__status--resolved { background:#E1F5EE; color:#0F6E56; }
    .asc-dispute__status--rejected { background:#FBE9E7; color:#B42318; }
    .asc-dispute__msg { font-size:13px; color:#374151; margin:0 0 8px; line-height:1.6; }
    .asc-dispute__ctx { font-size:12px; color:#6b7280; margin:0 0 12px; }
    .asc-dispute__remedy { display:flex; gap:8px; align-items:flex-start; background:#FEF9EC; border:0.5px solid #F5E4B8; border-radius:9px; padding:9px 11px; font-size:11.5px; color:#8A6D1B; line-height:1.5; margin-bottom:12px; }
    .asc-dispute__remedy i { font-size:14px; flex:none; margin-top:1px; }
    .asc-dispute__form { display:flex; flex-direction:column; gap:8px; }
    .asc-dispute__row1 { display:flex; gap:8px; flex-wrap:wrap; align-items:center; }
    .asc-dispute__row1 select { flex:none; }
    .asc-dispute__row1 input[type=text] { flex:1; min-width:180px; }
    .asc-dispute__actions { display:flex; }
    .asc-dispute__tools { display:flex; align-items:center; gap:8px; flex-wrap:wrap; margin-top:8px; }
    .asc-dispute__sent { font-size:11px; color:#9ca3af; }
    .asc-dispute__reply { display:flex; gap:7px; align-items:flex-start; background:#F5F9FD; border:0.5px solid #d7e3f2; border-radius:9px; padding:8px 11px; font-size:12px; color:#0C447C; line-height:1.5; margin:2px 0 8px; }
    .asc-dispute__reply i { font-size:14px; flex:none; margin-top:1px; }
    .asc-dispute__ack { display:flex; gap:6px; align-items:center; font-size:11.5px; color:#0F6E56; margin:0 0 8px; }
    .asc-dispute__date { display:block; font-size:11px; color:#9ca3af; margin-top:9px; }
    .asc-empty { font-size:13px; color:#9ca3af; margin:0; }
</style>
@endsection
