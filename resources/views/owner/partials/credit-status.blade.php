{{--
    Owner credit status — one at-a-glance card for every prepaid bucket in
    config/credits.php (SMS, agreement, …). Config-driven, so a new bucket appears here
    automatically with no view change. Read-only summary + a link to each bucket's own page.
--}}
@php
    $creditBuckets = config('credits.buckets', []);
    $ownerUserId   = auth()->id();
@endphp

@if (!empty($creditBuckets))
    <div class="pf-card">
        <div class="pf-card__head">
            <span class="pf-card__ic"><i class="ri-coin-line"></i></span>
            <h3 class="pf-card__title">{{ __('Credits & Utilities') }}</h3>
            <span class="pf-card__sub">{{ __('Your prepaid balances') }}</span>
        </div>
        <div class="pf-card__body">
            <div class="cs-credit-grid">
                @foreach ($creditBuckets as $key => $cfg)
                    @php
                        $bd    = \App\Services\Credit\CreditService::breakdown($key, $ownerUserId);
                        $price = \App\Services\Credit\CreditService::pricePerUnit($key);
                        $hasIndex = !empty($cfg['index_route']) && \Illuminate\Support\Facades\Route::has($cfg['index_route']);
                        // A bucket may be plan-covered (unlimited) for some owners — show that
                        // instead of a misleading "0", but still surface any reserve credits.
                        $unlimited = !empty($cfg['unlimited_resolver'])
                            && is_callable($cfg['unlimited_resolver'])
                            && call_user_func($cfg['unlimited_resolver'], $ownerUserId);
                    @endphp
                    <div class="cs-credit-tile">
                        <div class="cs-credit-tile__top">
                            <span class="cs-credit-tile__ic"><i class="{{ $cfg['icon'] ?? 'ri-coin-line' }}"></i></span>
                            <span class="cs-credit-tile__label">{{ __($cfg['label'] ?? ucfirst($key)) }}</span>
                        </div>

                        @if ($unlimited)
                            <div class="cs-credit-tile__balance">
                                <span class="cs-credit-tile__inf"><i class="ri-infinity-line"></i> {{ __('Unlimited') }}</span>
                            </div>
                            <div class="cs-credit-tile__note">{{ __('Included in your plan') }}</div>
                            @if ($bd['total'] > 0)
                                <div class="cs-credit-tile__pools">
                                    <span title="{{ __('Purchased earlier — kept in reserve if you ever change plan') }}">
                                        <i class="ri-shopping-bag-3-line"></i>
                                        {{ number_format($bd['total']) }} {{ __('in reserve') }}
                                    </span>
                                </div>
                            @endif
                        @else
                            @php
                                // A computed monthly free allowance (e.g. the agreement free tier):
                                // ['quota','used','remaining'] or null.
                                $allowance = (!empty($cfg['allowance_resolver']) && is_callable($cfg['allowance_resolver']))
                                    ? call_user_func($cfg['allowance_resolver'], $ownerUserId)
                                    : null;
                            @endphp

                            @if ($allowance)
                                {{-- Free monthly allowance + any purchased credits in reserve --}}
                                @php $available = (int) $allowance['remaining'] + (int) $bd['total']; @endphp
                                <div class="cs-credit-tile__balance">
                                    {{ number_format($available) }}
                                    <span class="cs-credit-tile__unit">{{ __('available now') }}</span>
                                </div>
                                <div class="cs-credit-tile__pools">
                                    <span title="{{ __('Free each month — resets on the 1st') }}">
                                        <i class="ri-gift-line"></i>
                                        {{ number_format($allowance['remaining']) }} {{ __('of') }} {{ number_format($allowance['quota']) }} {{ __('free this month') }}
                                    </span>
                                    @if ((int) $bd['total'] > 0)
                                        <span title="{{ __('Purchased (never expires)') }}">
                                            <i class="ri-shopping-bag-3-line"></i> {{ number_format($bd['total']) }} {{ __('owned') }}
                                        </span>
                                    @endif
                                </div>
                            @else
                                <div class="cs-credit-tile__balance">
                                    {{ number_format($bd['total']) }}
                                    <span class="cs-credit-tile__unit">{{ \Illuminate\Support\Str::plural($cfg['unit'] ?? 'credit', $bd['total']) }}</span>
                                </div>

                                @if (!empty($cfg['pools']))
                                    <div class="cs-credit-tile__pools">
                                        <span title="{{ __('Monthly allowance (resets each period)') }}">
                                            <i class="ri-calendar-line"></i> {{ number_format($bd['granted']) }} {{ __('allowance') }}
                                        </span>
                                        <span title="{{ __('Purchased (never expires)') }}">
                                            <i class="ri-shopping-bag-3-line"></i> {{ number_format($bd['purchased']) }} {{ __('owned') }}
                                        </span>
                                    </div>
                                @endif
                            @endif
                        @endif

                        <div class="cs-credit-tile__foot">
                            <span class="cs-credit-tile__price">
                                @if ($unlimited)
                                    {{ __('No per-signature charge') }}
                                @else
                                    {{ __('KES') }} {{ rtrim(rtrim(number_format($price, 2), '0'), '.') }} / {{ __('each') }}
                                @endif
                            </span>
                            @if ($hasIndex)
                                <a href="{{ route($cfg['index_route']) }}" class="cs-credit-tile__link">
                                    {{ __('Manage') }} <i class="ri-arrow-right-line"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <style>
        .cs-credit-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(230px, 1fr)); gap:16px; }
        .cs-credit-tile { border:0.5px solid #e5e7eb; border-radius:12px; padding:16px; background:#fff; display:flex; flex-direction:column; gap:10px; }
        .cs-credit-tile__top { display:flex; align-items:center; gap:9px; }
        .cs-credit-tile__ic { width:32px; height:32px; border-radius:8px; flex:none; display:inline-flex; align-items:center; justify-content:center; background:#E6F1FB; color:#185FA5; font-size:16px; }
        .cs-credit-tile__label { font-size:13px; font-weight:600; color:#374151; }
        .cs-credit-tile__balance { font-size:26px; font-weight:700; color:#111827; line-height:1; display:flex; align-items:baseline; gap:6px; }
        .cs-credit-tile__unit { font-size:12px; font-weight:500; color:#9ca3af; }
        .cs-credit-tile__inf { display:inline-flex; align-items:center; gap:7px; font-size:20px; font-weight:700; color:#0F6E56; }
        .cs-credit-tile__inf i { font-size:22px; }
        .cs-credit-tile__note { font-size:11.5px; color:#0F6E56; font-weight:500; }
        .cs-credit-tile__pools { display:flex; flex-wrap:wrap; gap:6px 14px; font-size:11.5px; color:#6b7280; }
        .cs-credit-tile__pools i { color:#185FA5; }
        .cs-credit-tile__foot { display:flex; align-items:center; justify-content:space-between; margin-top:2px; padding-top:10px; border-top:0.5px solid #f3f4f6; }
        .cs-credit-tile__price { font-size:11.5px; color:#9ca3af; }
        .cs-credit-tile__link { font-size:12.5px; font-weight:600; color:#185FA5; text-decoration:none; white-space:nowrap; }
        .cs-credit-tile__link:hover { color:#0F4A84; }
    </style>
@endif
