@extends('tenant.layouts.app')

@section('content')
@php
    $bandMeta = [
        'excellent' => ['#0F6E56', '#E1F5EE', __('Excellent')],
        'good'      => ['#185FA5', '#E6F1FB', __('Good')],
        'fair'      => ['#B45309', '#FEF3E7', __('Fair')],
        'poor'      => ['#C2410C', '#FBEAE1', __('Poor')],
        'high_risk' => ['#B42318', '#FBE9E7', __('High risk')],
        'unrated'   => ['#6b7280', '#f3f4f6', __('Not yet rated')],
    ];
    $band = $profile->score_band ?? 'unrated';
    [$bandColor, $bandBg, $bandLabel] = $bandMeta[$band] ?? $bandMeta['unrated'];
    $score = $profile && $profile->score !== null ? (float) $profile->score : null;
    $compLabels = [
        'punctuality' => __('On-time payments'),
        'completion'  => __('Bills cleared'),
        'arrears'     => __('Low arrears'),
        'lateness'    => __('Payment timeliness'),
        'reputation'  => __('Landlord ratings'),
    ];
    $components = $profile->score_factors['components'] ?? [];
@endphp

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="page-content-wrapper bg-white p-30 radius-20">

                <div class="rs-head">
                    <div>
                        <nav aria-label="breadcrumb">
                            <ol class="rs-breadcrumb">
                                <li><a href="{{ route('tenant.dashboard') }}">{{ __('Dashboard') }}</a></li>
                                <li aria-current="page">{{ __('My Rental Score') }}</li>
                            </ol>
                        </nav>
                        <h2 class="rs-title">{{ __('My Rental Score') }}</h2>
                        <p class="rs-sub">{{ __('Your rental reputation, built from how you pay — it travels with you to help you rent faster and unlock better offers.') }}</p>
                    </div>
                </div>

                @if (session('success'))<div class="rs-flash rs-flash--ok">{{ session('success') }}</div>@endif
                @if (session('error'))<div class="rs-flash rs-flash--err">{{ session('error') }}</div>@endif

                @if (! $profile)
                    <div class="rs-empty">
                        <i class="ri-line-chart-line"></i>
                        <h3>{{ __('No rental history yet') }}</h3>
                        <p>{{ __('As soon as your first rent invoice is on record, your Rental Score will start to build here.') }}</p>
                    </div>
                @else
                    <div class="rs-grid">
                        {{-- Gauge --}}
                        <div class="rs-gauge-card">
                            @if ($score === null)
                                <div class="rs-gauge rs-gauge--unrated"><span class="rs-gauge__grade">—</span></div>
                                <p class="rs-band" style="color:{{ $bandColor }};background:{{ $bandBg }};">{{ $bandLabel }}</p>
                                <p class="rs-gauge__hint">{{ __('Keep paying rent on record and your score will appear here.') }}</p>
                            @else
                                <div class="rs-gauge" style="background:conic-gradient({{ $bandColor }} {{ $score }}%, #eef2f6 0);">
                                    <div class="rs-gauge__inner">
                                        <span class="rs-gauge__num">{{ rtrim(rtrim(number_format($score, 1), '0'), '.') }}</span>
                                        <span class="rs-gauge__of">/ 100</span>
                                    </div>
                                </div>
                                <p class="rs-band" style="color:{{ $bandColor }};background:{{ $bandBg }};">
                                    {{ $bandLabel }} · {{ __('Grade') }} {{ $profile->score_grade }}
                                </p>
                                @if ($profile->is_thin_file)
                                    <p class="rs-gauge__hint">{{ __('Provisional — firms up as your history grows.') }}</p>
                                @endif
                            @endif
                        </div>

                        {{-- Breakdown --}}
                        <div class="rs-detail">
                            <div class="rs-stats">
                                <div class="rs-stat"><span class="rs-stat__n">{{ $profile->on_time_rate !== null ? number_format($profile->on_time_rate, 0) . '%' : '—' }}</span><span class="rs-stat__l">{{ __('On-time') }}</span></div>
                                <div class="rs-stat"><span class="rs-stat__n">{{ number_format($profile->invoices_paid) }}/{{ number_format($profile->invoices_total) }}</span><span class="rs-stat__l">{{ __('Invoices paid') }}</span></div>
                                <div class="rs-stat"><span class="rs-stat__n">{{ number_format($profile->tenancies_count) }}</span><span class="rs-stat__l">{{ __('Tenancies') }}</span></div>
                                <div class="rs-stat"><span class="rs-stat__n">{{ $profile->overdue_count ? number_format($profile->overdue_count) : '0' }}</span><span class="rs-stat__l">{{ __('Overdue now') }}</span></div>
                            </div>

                            @if (! empty($components))
                                <p class="rs-detail__label">{{ __('What shapes your score') }}</p>
                                <div class="rs-bars">
                                    @foreach ($components as $key => $val)
                                        <div class="rs-bar">
                                            <span class="rs-bar__label">{{ $compLabels[$key] ?? ucfirst($key) }}</span>
                                            <span class="rs-bar__track"><span class="rs-bar__fill" style="width:{{ max(2, min(100, $val)) }}%;background:{{ $bandColor }};"></span></span>
                                            <span class="rs-bar__val">{{ number_format($val, 0) }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            @if (! empty($profile->score_factors['notes']))
                                <ul class="rs-notes">
                                    @foreach ($profile->score_factors['notes'] as $note)
                                        <li>{{ $note }}</li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    </div>

                    {{-- Activate + Dispute --}}
                    <div class="rs-actions">
                        <div class="rs-activate {{ $profile->activated_at ? 'is-on' : '' }}">
                            <div>
                                @if ($profile->activated_at)
                                    <div class="rs-idmark" aria-hidden="true">
                                        <svg width="64" height="64" viewBox="0 0 60 60" fill="none">
                                            <rect x="1" y="1" width="58" height="58" rx="14" fill="#DFF3EA" stroke="#A7DFC9"/>
                                            <circle cx="30" cy="24" r="9" fill="#0F6E56"/>
                                            <path d="M15 46c1.5-8 8-12 15-12s13.5 4 15 12" fill="#0F6E56"/>
                                            <circle cx="44" cy="44" r="11" fill="#fff"/>
                                            <circle cx="44" cy="44" r="9" fill="#0F6E56"/>
                                            <path d="M40 44.3l2.7 2.7 5-5.4" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </div>
                                @endif
                                <h4>{{ $profile->activated_at ? __('Your Rental ID is active') : __('Activate your Rental ID') }}</h4>
                                <p>{{ $profile->activated_at
                                    ? __('You can share your rental reputation with landlords when applying, and become eligible for loan offers.')
                                    : __('Turn it on to share your reputation with landlords for faster approvals and to unlock loan offers.') }}</p>
                            </div>
                            <form action="{{ route('tenant.rental-score.activate') }}" method="POST">
                                @csrf
                                <button type="submit" class="rs-btn {{ $profile->activated_at ? 'rs-btn--ghost' : 'rs-btn--primary' }}">
                                    {{ $profile->activated_at ? __('Turn off') : __('Activate') }}
                                </button>
                            </form>
                        </div>

                        <div class="rs-dispute">
                            <h4>{{ __('Something look wrong?') }}</h4>
                            <p>{{ __('If any part of your score doesn\'t reflect your record, raise a dispute and our team will review it.') }}</p>
                            <form action="{{ route('tenant.rental-score.dispute') }}" method="POST">
                                @csrf
                                <textarea name="message" rows="3" class="rs-textarea" placeholder="{{ __('Describe what looks incorrect…') }}" required minlength="10"></textarea>
                                <button type="submit" class="rs-btn rs-btn--ghost">{{ __('Raise a dispute') }}</button>
                            </form>

                            @if ($disputes->count())
                                <div class="rs-disputes">
                                    @foreach ($disputes as $d)
                                        <div class="rs-disputes__row">
                                            <span class="rs-disputes__status rs-disputes__status--{{ $d->status }}">{{ ucfirst($d->status) }}</span>
                                            <span class="rs-disputes__msg">{{ \Illuminate\Support\Str::limit($d->message, 90) }}</span>
                                            <span class="rs-disputes__date">{{ $d->created_at->diffForHumans() }}</span>
                                        </div>
                                        @if ($d->resolution)
                                            <div class="rs-disputes__reply">
                                                <i class="ri-chat-check-line"></i>
                                                <span><strong>{{ __('Our reply:') }}</strong> {{ $d->resolution }}</span>
                                            </div>
                                        @endif
                                        @if ($d->tenant_reply)
                                            <div class="rs-disputes__myreply"><i class="ri-user-line"></i> <span><strong>{{ __('You replied:') }}</strong> {{ $d->tenant_reply }}</span></div>
                                        @endif
                                        @if ($d->resolution && ! $d->tenant_ack_at)
                                            <div class="rs-disputes__actions">
                                                <form action="{{ route('tenant.rental-score.dispute.reply') }}" method="POST">
                                                    @csrf
                                                    <input type="hidden" name="dispute_id" value="{{ $d->id }}">
                                                    <input type="hidden" name="action" value="ack">
                                                    <button type="submit" class="rs-btn rs-btn--sm rs-btn--primary">{{ __('This resolved it') }}</button>
                                                </form>
                                                <details class="rs-replywrap">
                                                    <summary class="rs-btn rs-btn--sm rs-btn--ghost">{{ __('Still an issue') }}</summary>
                                                    <form action="{{ route('tenant.rental-score.dispute.reply') }}" method="POST" class="rs-replyform">
                                                        @csrf
                                                        <input type="hidden" name="dispute_id" value="{{ $d->id }}">
                                                        <input type="hidden" name="action" value="reply">
                                                        <textarea name="message" rows="2" class="rs-textarea" placeholder="{{ __('Tell us what\'s still wrong…') }}" required minlength="5"></textarea>
                                                        <button type="submit" class="rs-btn rs-btn--sm rs-btn--ghost">{{ __('Send follow-up') }}</button>
                                                    </form>
                                                </details>
                                            </div>
                                        @elseif ($d->tenant_ack_at)
                                            <div class="rs-disputes__acked"><i class="ri-check-line"></i> {{ __('You confirmed this helped.') }}</div>
                                        @endif
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Transparency: who has looked up this record --}}
                    <div class="rs-views">
                        <div class="rs-views__head">
                            <h4>{{ __('Who\'s viewed your Rental ID') }}</h4>
                            <p>{{ __('For your transparency — every owner who screens your record shows up here.') }}</p>
                        </div>
                        @if ($lookups->count())
                            <div class="rs-views__list">
                                @foreach ($lookups as $lk)
                                    <div class="rs-views__row">
                                        <span class="rs-views__who"><i class="ri-eye-line"></i> {{ optional($lk->owner)->first_name ? trim($lk->owner->first_name . ' ' . $lk->owner->last_name) : __('A property owner') }}</span>
                                        <span class="rs-views__when">{{ $lk->created_at->diffForHumans() }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="rs-views__empty">{{ __('No owners have screened your record yet.') }}</p>
                        @endif
                    </div>
                @endif

            </div>
        </div>
    </div>
</div>

<style>
    .rs-head { margin-bottom:22px; }
    .rs-breadcrumb { display:flex; align-items:center; gap:6px; list-style:none; padding:0; margin:0 0 8px; font-size:12px; color:#9ca3af; }
    .rs-breadcrumb li:not(:last-child)::after { content:''; display:inline-block; width:5px; height:5px; border-right:1.5px solid #d1d5db; border-top:1.5px solid #d1d5db; transform:rotate(45deg); margin-left:6px; opacity:.6; }
    .rs-breadcrumb a { color:#185FA5; text-decoration:none; font-weight:500; }
    .rs-title { font-size:22px; font-weight:600; color:#111827; margin:0 0 5px; }
    .rs-sub { font-size:13.5px; color:#6b7280; margin:0; max-width:64ch; line-height:1.6; }
    .rs-flash { padding:11px 15px; border-radius:10px; font-size:13.5px; margin-bottom:16px; }
    .rs-flash--ok { background:#E1F5EE; color:#0F6E56; border:0.5px solid #A7DFC9; }
    .rs-flash--err { background:#FBE9E7; color:#B42318; border:0.5px solid #F3C4BC; }
    .rs-empty { text-align:center; padding:60px 20px; color:#6b7280; }
    .rs-empty i { font-size:44px; color:#cbd5e1; }
    .rs-empty h3 { font-size:17px; color:#111827; margin:12px 0 6px; }

    .rs-grid { display:grid; grid-template-columns:280px 1fr; gap:24px; margin-bottom:22px; }
    @media (max-width:800px){ .rs-grid { grid-template-columns:1fr; } }
    .rs-gauge-card { border:0.5px solid #e5e7eb; border-radius:16px; padding:26px; text-align:center; display:flex; flex-direction:column; align-items:center; gap:14px; }
    .rs-gauge { width:180px; height:180px; border-radius:50%; display:flex; align-items:center; justify-content:center; }
    .rs-gauge--unrated { background:#f3f4f6; }
    .rs-gauge__inner { width:140px; height:140px; background:#fff; border-radius:50%; display:flex; flex-direction:column; align-items:center; justify-content:center; box-shadow:inset 0 0 0 0.5px #eef2f6; }
    .rs-gauge__num { font-size:44px; font-weight:800; color:#111827; line-height:1; }
    .rs-gauge__of { font-size:12px; color:#9ca3af; margin-top:2px; }
    .rs-gauge__grade { font-size:40px; font-weight:800; color:#9ca3af; }
    .rs-band { font-size:13px; font-weight:600; padding:5px 14px; border-radius:99px; margin:0; }
    .rs-gauge__hint { font-size:11.5px; color:#9ca3af; margin:0; }

    .rs-detail { display:flex; flex-direction:column; gap:18px; }
    .rs-stats { display:grid; grid-template-columns:repeat(4,1fr); gap:12px; }
    @media (max-width:560px){ .rs-stats { grid-template-columns:repeat(2,1fr); } }
    .rs-stat { border:0.5px solid #e5e7eb; border-radius:12px; padding:14px; display:flex; flex-direction:column; gap:3px; }
    .rs-stat__n { font-size:20px; font-weight:700; color:#111827; }
    .rs-stat__l { font-size:11.5px; color:#6b7280; }
    .rs-detail__label { font-size:11px; text-transform:uppercase; letter-spacing:.06em; color:#9ca3af; font-weight:600; margin:0; }
    .rs-bars { display:flex; flex-direction:column; gap:10px; }
    .rs-bar { display:grid; grid-template-columns:130px 1fr 30px; align-items:center; gap:10px; font-size:12.5px; }
    .rs-bar__label { color:#374151; }
    .rs-bar__track { height:7px; background:#eef2f6; border-radius:99px; overflow:hidden; }
    .rs-bar__fill { display:block; height:100%; border-radius:99px; }
    .rs-bar__val { text-align:right; color:#6b7280; font-variant-numeric:tabular-nums; }
    .rs-notes { margin:0; padding-left:18px; font-size:12.5px; color:#6b7280; line-height:1.8; }

    .rs-actions { display:grid; grid-template-columns:1fr 1fr; gap:20px; }
    @media (max-width:800px){ .rs-actions { grid-template-columns:1fr; } }
    .rs-activate { border:0.5px solid #e5e7eb; border-radius:14px; padding:20px; display:flex; align-items:center; justify-content:space-between; gap:16px; }
    .rs-activate.is-on { background:#F2FCF8; border-color:#A7DFC9; }
    .rs-idmark { margin-bottom:12px; line-height:0; }
    .rs-activate h4, .rs-dispute h4 { font-size:15px; font-weight:600; color:#111827; margin:0 0 4px; }
    .rs-activate p, .rs-dispute p { font-size:12.5px; color:#6b7280; margin:0; line-height:1.6; }
    .rs-dispute { border:0.5px solid #e5e7eb; border-radius:14px; padding:20px; }
    .rs-textarea { width:100%; border:0.5px solid #e5e7eb; border-radius:10px; padding:10px 12px; font-size:13px; margin:12px 0 10px; outline:none; resize:vertical; }
    .rs-textarea:focus { border-color:#185FA5; box-shadow:0 0 0 3px rgba(24,95,165,.1); }
    .rs-btn { display:inline-flex; align-items:center; gap:6px; border:none; border-radius:9px; font-size:13px; font-weight:600; padding:9px 18px; cursor:pointer; white-space:nowrap; }
    .rs-btn--primary { background:#185FA5; color:#fff; }
    .rs-btn--primary:hover { background:#0F4A84; }
    .rs-btn--ghost { background:#f3f4f6; color:#374151; border:0.5px solid #e5e7eb; }
    .rs-btn--ghost:hover { background:#e5e7eb; }
    .rs-disputes { margin-top:16px; display:flex; flex-direction:column; gap:8px; }
    .rs-disputes__row { display:flex; align-items:center; gap:10px; font-size:12px; color:#6b7280; }
    .rs-disputes__status { font-size:10.5px; font-weight:600; padding:2px 8px; border-radius:99px; background:#eef2f6; color:#4b5563; text-transform:capitalize; flex:none; }
    .rs-disputes__status--open { background:#FEF3E7; color:#B45309; }
    .rs-disputes__status--resolved { background:#E1F5EE; color:#0F6E56; }
    .rs-disputes__msg { flex:1; color:#374151; }
    .rs-disputes__date { flex:none; }
    .rs-disputes__reply { display:flex; gap:7px; align-items:flex-start; background:#F2FCF8; border:0.5px solid #A7DFC9; border-radius:9px; padding:9px 11px; font-size:12px; color:#0F6E56; line-height:1.55; margin:-2px 0 4px; }
    .rs-disputes__reply i { font-size:14px; flex:none; margin-top:1px; }
    .rs-disputes__myreply { display:flex; gap:7px; align-items:flex-start; background:#F8FAFC; border:0.5px solid #e5e7eb; border-radius:9px; padding:9px 11px; font-size:12px; color:#374151; line-height:1.55; margin:-2px 0 4px; }
    .rs-disputes__myreply i { font-size:14px; flex:none; margin-top:1px; color:#6b7280; }
    .rs-disputes__actions { display:flex; align-items:flex-start; gap:10px; flex-wrap:wrap; margin:2px 0 6px; }
    .rs-btn--sm { padding:6px 13px; font-size:12px; }
    .rs-replywrap summary { list-style:none; display:inline-flex; }
    .rs-replywrap summary::-webkit-details-marker { display:none; }
    .rs-replyform { margin-top:9px; display:flex; flex-direction:column; gap:8px; max-width:420px; }
    .rs-replyform .rs-textarea { margin:0; }
    .rs-disputes__acked { display:flex; gap:6px; align-items:center; font-size:12px; color:#0F6E56; margin:2px 0 6px; }

    .rs-views { margin-top:22px; border:0.5px solid #e5e7eb; border-radius:14px; padding:20px; }
    .rs-views__head h4 { font-size:15px; font-weight:600; color:#111827; margin:0 0 4px; }
    .rs-views__head p { font-size:12.5px; color:#6b7280; margin:0 0 14px; line-height:1.6; }
    .rs-views__list { display:flex; flex-direction:column; gap:8px; }
    .rs-views__row { display:flex; align-items:center; justify-content:space-between; gap:12px; font-size:12.5px; padding:9px 12px; background:#F8FAFC; border-radius:9px; }
    .rs-views__who { color:#374151; display:flex; align-items:center; gap:7px; }
    .rs-views__who i { color:#185FA5; }
    .rs-views__when { color:#9ca3af; flex:none; }
    .rs-views__empty { font-size:12.5px; color:#9ca3af; margin:0; }
</style>
@endsection
