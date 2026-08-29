@php
    // Self-contained screening report — rendered on the full page AND returned as the AJAX
    // body for the in-flow modal on tenant-create. Expects $result (status|profile|phone).
    $sym     = getCurrencySymbol();
    $status  = $result['status'] ?? null;
    $profile = $result['profile'] ?? null;

    $bandMeta = [
        'excellent' => ['#0F6E56', '#E1F5EE', __('Excellent')],
        'good'      => ['#185FA5', '#E6F1FB', __('Good')],
        'fair'      => ['#B45309', '#FEF3E7', __('Fair')],
        'poor'      => ['#C2410C', '#FBEAE1', __('Poor')],
        'high_risk' => ['#B42318', '#FBE9E7', __('High risk')],
        'unrated'   => ['#6b7280', '#f3f4f6', __('Not yet rated')],
    ];
    $compLabels = [
        'punctuality' => __('On-time payments'),
        'completion'  => __('Bills cleared'),
        'arrears'     => __('Low arrears'),
        'lateness'    => __('Payment timeliness'),
        'reputation'  => __('Landlord ratings'),
    ];
    if ($profile) {
        $band = $profile->score_band ?? 'unrated';
        [$bandColor, $bandBg, $bandLabel] = $bandMeta[$band] ?? $bandMeta['unrated'];
        $score      = $profile->score !== null ? (float) $profile->score : null;
        $components = $profile->score_factors['components'] ?? [];
    }
@endphp

@if ($status === 'no_record')
    <div class="sc-report sc-report--empty">
        <i class="ri-user-search-line"></i>
        <h3>{{ __('No rental record found') }}</h3>
        <p>{{ __('No tenancy on record anywhere in the system for :phone. This isn\'t a red flag on its own — they may simply be new to the platform. You were not charged.', ['phone' => $result['phone']]) }}</p>
    </div>
@elseif ($profile)
    <div class="sc-report">
        <div class="sc-report__top">
            <div class="sc-report__id">
                <span class="sc-report__phone">{{ $result['phone'] }}</span>
                @if ($profile->activated_at)
                    <span class="sc-badge sc-badge--claimed"><i class="ri-verified-badge-line"></i> {{ __('Claimed ID') }}</span>
                @else
                    <span class="sc-badge sc-badge--unclaimed">{{ __('Unclaimed') }}</span>
                @endif
            </div>
            <span class="sc-report__meta">{{ __('Screened just now') }} · {{ __('logged for the tenant') }}</span>
        </div>

        <div class="sc-grid">
            <div class="sc-gauge-card">
                @if ($score === null)
                    <div class="sc-gauge sc-gauge--unrated"><span class="sc-gauge__grade">—</span></div>
                    <p class="sc-band" style="color:{{ $bandColor }};background:{{ $bandBg }};">{{ $bandLabel }}</p>
                @else
                    <div class="sc-gauge" style="background:conic-gradient({{ $bandColor }} {{ $score }}%, #eef2f6 0);">
                        <div class="sc-gauge__inner">
                            <span class="sc-gauge__num">{{ rtrim(rtrim(number_format($score, 1), '0'), '.') }}</span>
                            <span class="sc-gauge__of">/ 100</span>
                        </div>
                    </div>
                    <p class="sc-band" style="color:{{ $bandColor }};background:{{ $bandBg }};">{{ $bandLabel }} · {{ __('Grade') }} {{ $profile->score_grade }}</p>
                    @if ($profile->is_thin_file)
                        <p class="sc-gauge__hint">{{ __('Provisional — limited history so far.') }}</p>
                    @endif
                @endif
            </div>

            <div class="sc-detail">
                <div class="sc-stats">
                    <div class="sc-stat"><span class="sc-stat__n">{{ $profile->on_time_rate !== null ? number_format($profile->on_time_rate, 0) . '%' : '—' }}</span><span class="sc-stat__l">{{ __('On-time') }}</span></div>
                    <div class="sc-stat"><span class="sc-stat__n">{{ number_format($profile->invoices_paid) }}/{{ number_format($profile->invoices_total) }}</span><span class="sc-stat__l">{{ __('Invoices paid') }}</span></div>
                    <div class="sc-stat"><span class="sc-stat__n">{{ number_format($profile->tenancies_count) }}</span><span class="sc-stat__l">{{ __('Tenancies') }}</span></div>
                    <div class="sc-stat"><span class="sc-stat__n">{{ $profile->outstanding > 0 ? $sym . number_format($profile->outstanding, 0) : $sym.'0' }}</span><span class="sc-stat__l">{{ __('Outstanding') }}</span></div>
                    <div class="sc-stat"><span class="sc-stat__n">{{ $profile->avg_days_late !== null ? number_format($profile->avg_days_late, 1) : '—' }}</span><span class="sc-stat__l">{{ __('Avg days late') }}</span></div>
                    <div class="sc-stat"><span class="sc-stat__n">{{ $profile->ratings_count ? number_format($profile->landlord_rating_avg, 1) . '★' : '—' }}</span><span class="sc-stat__l">{{ __('Landlord rating') }}@if($profile->ratings_count) ({{ $profile->ratings_count }})@endif</span></div>
                </div>

                @if (! empty($components))
                    <p class="sc-detail__label">{{ __('What shapes this score') }}</p>
                    <div class="sc-bars">
                        @foreach ($components as $key => $val)
                            <div class="sc-bar">
                                <span class="sc-bar__label">{{ $compLabels[$key] ?? ucfirst($key) }}</span>
                                <span class="sc-bar__track"><span class="sc-bar__fill" style="width:{{ max(2, min(100, $val)) }}%;background:{{ $bandColor }};"></span></span>
                                <span class="sc-bar__val">{{ number_format($val, 0) }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif

                @if (! empty($profile->score_factors['notes']))
                    <ul class="sc-notes">
                        @foreach ($profile->score_factors['notes'] as $note)<li>{{ $note }}</li>@endforeach
                    </ul>
                @endif
            </div>
        </div>

        <p class="sc-report__foot">
            <i class="ri-scales-3-line"></i>
            {{ __('This is an objective, aggregated score built from payment behaviour across all of this person\'s tenancies — not any single landlord\'s opinion. The tenant can see that you viewed it and can dispute any figure.') }}
        </p>
    </div>
@endif
