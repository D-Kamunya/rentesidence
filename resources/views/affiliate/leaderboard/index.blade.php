@extends('affiliate.layouts.app')

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="page-content-wrapper bg-white p-30 radius-20">
                <div class="container">

                    {{-- Page Header --}}
                    <div class="lb-header">
                        <div>
                            <nav aria-label="breadcrumb">
                                <ol class="lb-breadcrumb">
                                    <li><a href="{{ route('affiliate.dashboard') }}">{{ __('Dashboard') }}</a></li>
                                    <li aria-current="page">{{ __('Leaderboard') }}</li>
                                </ol>
                            </nav>
                            <h1 class="lb-page-title">{{ __('Affiliate Leaderboard') }}</h1>
                            <p class="lb-page-sub">{{ __('See how you rank among fellow affiliates') }}</p>
                        </div>
                    </div>

                    {{-- Period Selector --}}
                    <div class="lb-period-selector">
                        <a href="?period=all_time" class="lb-period-btn {{ $period === 'all_time' ? 'lb-period-btn--active' : '' }}">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/><polyline points="12 6 12 12 16 14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                            {{ __('All Time') }}
                        </a>
                        <a href="?period=this_year" class="lb-period-btn {{ $period === 'this_year' ? 'lb-period-btn--active' : '' }}">
                            {{ __('This Year') }}
                        </a>
                        <a href="?period=this_month" class="lb-period-btn {{ $period === 'this_month' ? 'lb-period-btn--active' : '' }}">
                            {{ __('This Month') }}
                        </a>
                        <a href="?period=last_month" class="lb-period-btn {{ $period === 'last_month' ? 'lb-period-btn--active' : '' }}">
                            {{ __('Last Month') }}
                        </a>
                        <a href="?period=last_3_months" class="lb-period-btn {{ $period === 'last_3_months' ? 'lb-period-btn--active' : '' }}">
                            {{ __('Last 3 Months') }}
                        </a>
                    </div>

                    {{-- Platform Stats --}}
                    <div class="lb-platform-stats">
                        <div class="lb-platform-stat">
                            <span class="lb-platform-stat__icon">💰</span>
                            <div>
                                <span class="lb-platform-stat__value">KSh {{ number_format($totalPlatformEarnings, 0) }}</span>
                                <span class="lb-platform-stat__label">{{ __('Total Paid to Affiliates') }}</span>
                            </div>
                        </div>
                        <div class="lb-platform-stat">
                            <span class="lb-platform-stat__icon">👥</span>
                            <div>
                                <span class="lb-platform-stat__value">{{ $totalAffiliates }}</span>
                                <span class="lb-platform-stat__label">{{ __('Active Affiliates') }}</span>
                            </div>
                        </div>
                        <div class="lb-platform-stat">
                            <span class="lb-platform-stat__icon">🏠</span>
                            <div>
                                <span class="lb-platform-stat__value">{{ $totalReferrals }}</span>
                                <span class="lb-platform-stat__label">{{ __('Owners Referred') }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="lb-grid">
                        {{-- Top Earners --}}
                        <div class="lb-card">
                            <div class="lb-card__head">
                                <div class="lb-card__head-left">
                                    <span class="lb-card__trophy">🏆</span>
                                    <h3 class="lb-card__title">{{ __('Top Earners') }}</h3>
                                </div>
                                <span class="lb-card__badge">{{ $periodLabel }}</span>
                            </div>
                            <div class="lb-card__body">
                                @forelse($topEarners as $index => $earner)
                                <div class="lb-row {{ $earner->id == $currentAffiliateId ? 'lb-row--me' : '' }}">
                                    <div class="lb-row__rank">
                                        @if($index == 0)
                                            <span class="lb-rank lb-rank--gold">🥇</span>
                                        @elseif($index == 1)
                                            <span class="lb-rank lb-rank--silver">🥈</span>
                                        @elseif($index == 2)
                                            <span class="lb-rank lb-rank--bronze">🥉</span>
                                        @else
                                            <span class="lb-rank-num">{{ $index + 1 }}</span>
                                        @endif
                                    </div>
                                    <div class="lb-row__avatar">
                                        {{ strtoupper(substr($earner->name ?? 'A', 0, 1)) }}
                                    </div>
                                    <div class="lb-row__info">
                                        <span class="lb-row__name">
                                            {{ $earner->name ?? '—' }}
                                            @if($earner->id == $currentAffiliateId)
                                                <span class="lb-you-badge">{{ __('YOU') }}</span>
                                            @endif
                                        </span>
                                        <span class="lb-row__sub">{{ $earner->total_referrals }} {{ __('referrals') }}</span>
                                    </div>
                                    <div class="lb-row__earnings">
                                        KSh {{ number_format($earner->total_earned, 2) }}
                                    </div>
                                </div>
                                @empty
                                <div class="lb-empty">{{ __('No data for this period') }}</div>
                                @endforelse
                            </div>
                        </div>

                        {{-- Top Recruiters --}}
                        <div class="lb-card">
                            <div class="lb-card__head">
                                <div class="lb-card__head-left">
                                    <span class="lb-card__trophy">🤝</span>
                                    <h3 class="lb-card__title">{{ __('Top Recruiters') }}</h3>
                                </div>
                                <span class="lb-card__badge">{{ $periodLabel }}</span>
                            </div>
                            <div class="lb-card__body">
                                @forelse($topRecruiters as $index => $recruiter)
                                <div class="lb-row {{ $recruiter->id == $currentAffiliateId ? 'lb-row--me' : '' }}">
                                    <div class="lb-row__rank">
                                        @if($index == 0)
                                            <span class="lb-rank lb-rank--gold">🥇</span>
                                        @elseif($index == 1)
                                            <span class="lb-rank lb-rank--silver">🥈</span>
                                        @elseif($index == 2)
                                            <span class="lb-rank lb-rank--bronze">🥉</span>
                                        @else
                                            <span class="lb-rank-num">{{ $index + 1 }}</span>
                                        @endif
                                    </div>
                                    <div class="lb-row__avatar">
                                        {{ strtoupper(substr($recruiter->name ?? 'A', 0, 1)) }}
                                    </div>
                                    <div class="lb-row__info">
                                        <span class="lb-row__name">
                                            {{ $recruiter->name ?? '—' }}
                                            @if($recruiter->id == $currentAffiliateId)
                                                <span class="lb-you-badge">{{ __('YOU') }}</span>
                                            @endif
                                        </span>
                                        <span class="lb-row__sub">KSh {{ number_format($recruiter->total_earned, 0) }} {{ __('earned') }}</span>
                                    </div>
                                    <div class="lb-row__referrals">
                                        {{ $recruiter->total_referrals }}
                                        <span style="font-size:10px;color:var(--lb-gray-400);">refs</span>
                                    </div>
                                </div>
                                @empty
                                <div class="lb-empty">{{ __('No data for this period') }}</div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    {{-- Your Position Card --}}
                    <div class="lb-my-position">
                        <div class="lb-my-position__inner">
                            <span class="lb-my-position__icon">📊</span>
                            <div class="lb-my-position__stats">
                                <div class="lb-my-position__stat">
                                    <span class="lb-my-position__stat-label">{{ __('Your Earnings') }}</span>
                                    <span class="lb-my-position__stat-value">KSh {{ number_format($myEarnings, 2) }}</span>
                                </div>
                                <div class="lb-my-position__divider"></div>
                                <div class="lb-my-position__stat">
                                    <span class="lb-my-position__stat-label">{{ __('Your Referrals') }}</span>
                                    <span class="lb-my-position__stat-value">{{ $myReferrals }}</span>
                                </div>
                            </div>
                            <span class="lb-my-position__period">{{ $periodLabel }}</span>
                        </div>
                    </div>

                    {{-- Recent Big Wins --}}
                    @if($recentBigWins->isNotEmpty())
                    <div class="lb-wins-section">
                        <h3 class="lb-wins-title">
                            <span>🎉</span> {{ __('Recent Big Wins') }}
                            <span style="font-size:12px;font-weight:400;color:var(--lb-gray-400);">({{ __('Last 7 days') }})</span>
                        </h3>
                        <div class="lb-wins-grid">
                            @foreach($recentBigWins as $win)
                            <div class="lb-win-card">
                                <div class="lb-win-card__top">
                                    <span class="lb-win-card__affiliate">{{ $win['affiliate_name'] }}</span>
                                    <span class="lb-win-card__amount">+KSh {{ number_format($win['amount'], 2) }}</span>
                                </div>
                                <div class="lb-win-card__bottom">
                                    <span class="lb-win-card__detail">
                                        {{ $win['source'] }} · {{ $win['owner_name'] }}
                                    </span>
                                    <span class="lb-win-card__time">{{ $win['when'] }}</span>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('style')
<style>
:root {
    --lb-blue: #185FA5;
    --lb-blue-light: #E6F1FB;
    --lb-green: #1D9E75;
    --lb-green-dark: #0F6E56;
    --lb-green-light: #E1F5EE;
    --lb-amber: #D97706;
    --lb-amber-light: #FEF3C7;
    --lb-purple: #534AB7;
    --lb-purple-light: #EEEDF9;
    --lb-gray-900: #111827;
    --lb-gray-700: #374151;
    --lb-gray-500: #6b7280;
    --lb-gray-400: #9ca3af;
    --lb-gray-200: #e5e7eb;
    --lb-gray-100: #f3f4f6;
    --lb-gray-50: #fafafa;
    --lb-white: #ffffff;
}

.lb-header { margin-bottom: 20px; }
.lb-breadcrumb { display: flex; align-items: center; gap: 6px; list-style: none; padding: 0; margin: 0 0 8px; font-size: 12px; color: var(--lb-gray-400); }
.lb-breadcrumb li:not(:last-child)::after { content: '›'; margin-left: 6px; color: #d1d5db; }
.lb-breadcrumb a { color: var(--lb-blue); font-weight: 500; text-decoration: none; }
.lb-page-title { font-size: 22px; font-weight: 500; color: var(--lb-gray-900); margin: 0 0 4px; }
.lb-page-sub { font-size: 13px; color: var(--lb-gray-500); margin: 0; }

/* Period Selector */
.lb-period-selector {
    display: flex; gap: 6px; margin-bottom: 20px;
    overflow-x: auto; -webkit-overflow-scrolling: touch;
    padding-bottom: 4px;
}
.lb-period-btn {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 7px 14px; border-radius: 8px;
    font-size: 12px; font-weight: 500; color: var(--lb-gray-500);
    background: var(--lb-gray-50); border: 0.5px solid var(--lb-gray-200);
    text-decoration: none; white-space: nowrap; transition: all .15s;
    flex-shrink: 0;
}
.lb-period-btn:hover { background: var(--lb-gray-100); color: var(--lb-gray-700); }
.lb-period-btn--active {
    background: var(--lb-blue); color: #fff; border-color: var(--lb-blue);
}
.lb-period-btn--active:hover { background: #0F4A84; color: #fff; }

/* Platform Stats */
.lb-platform-stats {
    display: flex; gap: 14px; margin-bottom: 20px; flex-wrap: wrap;
}
.lb-platform-stat {
    display: flex; align-items: center; gap: 10px;
    background: var(--lb-white); border: 0.5px solid rgba(24,95,165,.15);
    border-radius: 10px; padding: 14px 18px; flex: 1; min-width: 180px;
}
.lb-platform-stat__icon { font-size: 24px; }
.lb-platform-stat__value { font-size: 16px; font-weight: 700; color: var(--lb-gray-900); display: block; }
.lb-platform-stat__label { font-size: 11px; color: var(--lb-gray-400); }

/* Grid */
.lb-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 20px; }
@media(max-width: 768px) { .lb-grid { grid-template-columns: 1fr; } }

.lb-card {
    background: var(--lb-white); border: 0.5px solid rgba(24,95,165,.15);
    border-radius: 14px; overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,.03);
}
.lb-card__head {
    display: flex; align-items: center; justify-content: space-between;
    padding: 16px 18px; border-bottom: 0.5px solid var(--lb-gray-200);
    background: linear-gradient(135deg, #fafafa 0%, #f5f5f5 100%);
}
.lb-card__head-left { display: flex; align-items: center; gap: 8px; }
.lb-card__trophy { font-size: 18px; }
.lb-card__title { font-size: 14px; font-weight: 600; color: var(--lb-gray-900); margin: 0; }
.lb-card__badge {
    font-size: 10px; font-weight: 500; text-transform: uppercase; letter-spacing: .06em;
    color: var(--lb-gray-400); background: var(--lb-gray-100);
    padding: 3px 8px; border-radius: 99px;
}
.lb-card__body { max-height: 500px; overflow-y: auto; }

/* Leaderboard Row */
.lb-row {
    display: flex; align-items: center; gap: 10px;
    padding: 12px 18px; border-bottom: 0.5px solid var(--lb-gray-100);
    transition: background .15s;
}
.lb-row:last-child { border-bottom: none; }
.lb-row:hover { background: var(--lb-gray-50); }
.lb-row--me {
    background: var(--lb-blue-light) !important;
    border-left: 3px solid var(--lb-blue);
}
.lb-row__rank { width: 32px; text-align: center; flex-shrink: 0; }
.lb-rank { font-size: 20px; }
.lb-rank-num { font-size: 13px; font-weight: 600; color: var(--lb-gray-400); }
.lb-row__avatar {
    width: 32px; height: 32px; border-radius: 8px;
    background: var(--lb-purple); color: #fff;
    display: flex; align-items: center; justify-content: center;
    font-size: 12px; font-weight: 700; flex-shrink: 0;
}
.lb-row__info { flex: 1; min-width: 0; }
.lb-row__name { font-size: 13px; font-weight: 600; color: var(--lb-gray-900); display: flex; align-items: center; gap: 6px; }
.lb-row__sub { font-size: 11px; color: var(--lb-gray-400); }
.lb-you-badge {
    display: inline-flex; padding: 1px 6px; border-radius: 99px;
    font-size: 9px; font-weight: 700; letter-spacing: .05em;
    background: var(--lb-blue); color: #fff;
}
.lb-row__earnings { font-size: 14px; font-weight: 700; color: var(--lb-green-dark); white-space: nowrap; }
.lb-row__referrals { font-size: 14px; font-weight: 700; color: var(--lb-purple); white-space: nowrap; }
.lb-empty { text-align: center; padding: 40px 20px; color: var(--lb-gray-400); font-size: 13px; }

/* My Position */
.lb-my-position {
    background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
    border-radius: 14px; padding: 20px 24px; margin-bottom: 20px;
    color: #fff;
}
.lb-my-position__inner { display: flex; align-items: center; gap: 16px; flex-wrap: wrap; }
.lb-my-position__icon { font-size: 28px; }
.lb-my-position__stats { display: flex; align-items: center; gap: 20px; flex: 1; }
.lb-my-position__stat { display: flex; flex-direction: column; gap: 2px; }
.lb-my-position__stat-label { font-size: 10px; font-weight: 500; text-transform: uppercase; letter-spacing: .06em; color: rgba(255,255,255,.5); }
.lb-my-position__stat-value { font-size: 20px; font-weight: 700; }
.lb-my-position__divider { width: 1px; height: 32px; background: rgba(255,255,255,.15); }
.lb-my-position__period {
    font-size: 11px; color: rgba(255,255,255,.5);
    background: rgba(255,255,255,.08); padding: 5px 10px; border-radius: 99px;
}

/* Recent Big Wins */
.lb-wins-section { margin-bottom: 20px; }
.lb-wins-title {
    font-size: 14px; font-weight: 600; color: var(--lb-gray-900);
    margin: 0 0 12px; display: flex; align-items: center; gap: 6px;
}
.lb-wins-grid {
    display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 10px;
}
.lb-win-card {
    background: var(--lb-white); border: 0.5px solid var(--lb-gray-200);
    border-radius: 10px; padding: 14px 16px;
    transition: all .15s;
}
.lb-win-card:hover { border-color: var(--lb-green); box-shadow: 0 4px 12px rgba(0,0,0,.06); }
.lb-win-card__top {
    display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px;
}
.lb-win-card__affiliate { font-size: 13px; font-weight: 600; color: var(--lb-gray-900); }
.lb-win-card__amount { font-size: 14px; font-weight: 700; color: var(--lb-green-dark); }
.lb-win-card__bottom { display: flex; align-items: center; justify-content: space-between; }
.lb-win-card__detail { font-size: 11px; color: var(--lb-gray-400); }
.lb-win-card__time { font-size: 11px; color: var(--lb-gray-400); }

/* ── Responsive fixes ── */
@media(max-width: 900px) {
    .lb-platform-stats {
        gap: 10px;
    }
    .lb-platform-stat {
        min-width: 140px;
        padding: 12px 14px;
    }
    .lb-platform-stat__value {
        font-size: 14px;
    }
}

@media(max-width: 768px) {
    .lb-grid {
        grid-template-columns: 1fr;
    }
    
    .lb-card__body {
        max-height: 400px;
    }
    
    /* Stack "YOU" badge below name on small screens */
    .lb-row__name {
        flex-wrap: wrap;
        gap: 4px;
    }
    .lb-you-badge {
        font-size: 8px;
        padding: 1px 5px;
        line-height: 1.4;
    }
    
    /* Allow earnings to wrap instead of being pushed */
    .lb-row {
        flex-wrap: wrap;
        gap: 6px;
    }
    .lb-row__info {
        flex: 1 1 100%;
        order: 3;
    }
    .lb-row__earnings {
        order: 4;
        font-size: 13px;
    }
    .lb-row__referrals {
        order: 4;
        font-size: 13px;
    }
    .lb-row__rank {
        order: 1;
    }
    .lb-row__avatar {
        order: 2;
    }
    
    /* Wins grid - single column */
    .lb-wins-grid {
        grid-template-columns: 1fr;
    }
    
    /* Period selector - full width scroll */
    .lb-period-selector {
        gap: 4px;
    }
    .lb-period-btn {
        padding: 6px 10px;
        font-size: 11px;
    }
}

@media(max-width: 480px) {
    .lb-page-title {
        font-size: 18px;
    }
    
    .lb-platform-stats {
        flex-direction: column;
    }
    .lb-platform-stat {
        min-width: 100%;
    }
    
    .lb-row {
        padding: 10px 12px;
    }
    .lb-row__earnings {
        font-size: 12px;
    }
    .lb-row__referrals {
        font-size: 12px;
    }
    
    .lb-my-position__inner {
        gap: 10px;
    }
    .lb-my-position__stats {
        gap: 12px;
    }
    .lb-my-position__stat-value {
        font-size: 16px;
    }
    
    .lb-win-card__top {
        flex-direction: column;
        align-items: flex-start;
        gap: 4px;
    }
    .lb-win-card__bottom {
        flex-direction: column;
        align-items: flex-start;
        gap: 2px;
    }
}
</style>
@endpush