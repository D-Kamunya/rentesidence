@extends('owner.layouts.app')

@section('content')
@php
    $sym   = getCurrencySymbol();
    $cover = $eligibility['cover'] ?? null;              // plan | free | credit | null
    // Will this owner be charged a purchased credit for the next lookup?
    $willCharge = (! $unlimited) && $cover === 'credit';
    $freeLeft   = $allowance['remaining'] ?? null;
@endphp

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="page-content-wrapper bg-white p-30 radius-20">

                <div class="sc-head">
                    <div>
                        <nav aria-label="breadcrumb">
                            <ol class="sc-breadcrumb">
                                <li><a href="{{ route('owner.dashboard') }}">{{ __('Dashboard') }}</a></li>
                                <li><a href="{{ route('owner.tenant.index', ['type' => 'all']) }}">{{ __('Tenants') }}</a></li>
                                <li aria-current="page">{{ __('Screening') }}</li>
                            </ol>
                        </nav>
                        <h2 class="sc-title">{{ __('Tenant Screening') }}</h2>
                        <p class="sc-sub">{{ __('Look up a prospective tenant\'s objective rental record — how they\'ve actually paid across their tenancies — before you hand over the keys.') }}</p>
                    </div>
                </div>

                @if (session('error'))<div class="sc-flash sc-flash--err">{{ session('error') }}</div>@endif
                @if (session('success'))<div class="sc-flash sc-flash--ok">{{ session('success') }}</div>@endif

                {{-- Coverage / balance bar --}}
                <div class="sc-cover">
                    @if ($unlimited)
                        <div class="sc-cover__item">
                            <i class="ri-shield-check-line"></i>
                            <div><strong>{{ __('Included in your plan') }}</strong><span>{{ __('Unlimited tenant screenings') }}</span></div>
                        </div>
                    @else
                        <div class="sc-cover__item">
                            <i class="ri-gift-line"></i>
                            <div>
                                <strong>{{ $freeLeft !== null ? $freeLeft : 0 }} {{ __('free left') }}</strong>
                                <span>{{ __('this month') }}@if(isset($allowance['quota'])) · {{ __('of') }} {{ $allowance['quota'] }}@endif</span>
                            </div>
                        </div>
                        <div class="sc-cover__item">
                            <i class="ri-coin-line"></i>
                            <div><strong>{{ number_format($balance) }} {{ __('credits') }}</strong><span>{{ $sym }}{{ rtrim(rtrim(number_format($price, 2), '0'), '.') }} {{ __('per lookup') }}</span></div>
                        </div>
                        <a href="#sc-topup" class="sc-cover__buy">{{ __('Top up') }} <i class="ri-arrow-down-line"></i></a>
                    @endif
                </div>

                {{-- Lookup (primary action) --}}
                <div class="sc-lookcard">
                    <label class="sc-lookcard__label">{{ __('Enter a tenant\'s phone to screen') }}</label>
                    <form action="{{ route('owner.screening.lookup') }}" method="POST" class="sc-lookup" id="scLookupForm">
                        @csrf
                        <div class="sc-lookup__field">
                            <span class="sc-lookup__flag">🇰🇪</span>
                            <input type="tel" name="phone" id="scPhone" class="sc-lookup__input" inputmode="tel"
                                   value="{{ old('phone', $result['phone'] ?? request('phone', '')) }}"
                                   placeholder="{{ __('Tenant phone e.g. 0712 345 678') }}" required minlength="9" autocomplete="off">
                        </div>
                        <button type="submit" class="sc-lookup__btn" id="scLookupBtn">
                            <i class="ri-search-eye-line"></i> {{ __('Screen tenant') }}
                        </button>
                    </form>
                    <p class="sc-lookup__hint">
                        @if ($unlimited)
                            {{ __('Screening is included in your plan — screen as many prospective tenants as you like.') }}
                        @elseif (($freeLeft ?? 0) > 0)
                            {{ trans_choice('{1}:n free lookup left this month.|[2,*]:n free lookups left this month.', $freeLeft, ['n' => $freeLeft]) }}
                            @if ($balance > 0)
                                {{ trans_choice('{1}After that, you have :m purchased credit in reserve.|[2,*]After that, you have :m purchased credits in reserve.', $balance, ['m' => number_format($balance)]) }}
                            @endif
                        @elseif ($balance > 0)
                            {{ trans_choice('{1}This lookup will use your last screening credit.|[2,*]This lookup will use 1 of your :m screening credits.', $balance, ['m' => number_format($balance)]) }}
                        @else
                            {{ __('You\'re out of free lookups and credits — top up below to screen.') }}
                        @endif
                        <span class="sc-lookup__hintmute">{{ __('You\'re only charged when there\'s a record to show.') }}</span>
                    </p>
                </div>

                {{-- Result --}}
                @if ($result)
                    <div class="sc-resultwrap">
                        @include('owner.screening.partials.report', ['result' => $result])
                    </div>
                @endif

                {{-- Top-up (metered owners only) --}}
                @unless ($unlimited)
                    <div class="sc-topup" id="sc-topup">
                        <div class="sc-topup__head">
                            <h3>{{ __('Need more? Top up screening credits') }}</h3>
                            <p>{{ $sym }}{{ rtrim(rtrim(number_format($price, 2), '0'), '.') }} {{ __('per lookup · non-expiring · paid via M-Pesa') }}</p>
                        </div>
                        <div class="sc-topup__row">
                            <div class="sc-topup__qty">
                                <label>{{ __('Credits') }}</label>
                                <input type="number" id="scQty" min="1" max="1000" value="5" class="sc-topup__qtyinput">
                            </div>
                            <div class="sc-topup__phonewrap">
                                <label>{{ __('M-Pesa number') }}</label>
                                <div class="sc-topup__phone">
                                    <span class="sc-topup__flag">🇰🇪</span>
                                    <input type="tel" id="scBuyPhone" inputmode="tel" placeholder="0712 345 678" class="sc-topup__phoneinput">
                                </div>
                            </div>
                            <div class="sc-topup__total">
                                <label>{{ __('Total') }}</label>
                                <span id="scTotal">{{ $sym }}{{ rtrim(rtrim(number_format($price * 5, 2), '0'), '.') }}</span>
                            </div>
                            <button type="button" id="scBuyBtn" class="sc-topup__btn">{{ __('Buy credits') }}</button>
                        </div>
                    </div>
                @endunless

                {{-- Recent lookups --}}
                @if ($history->count())
                    <div class="sc-hist">
                        <h3 class="sc-hist__title">{{ __('Your recent screenings') }}</h3>
                        <div class="table-responsive">
                            <table class="sc-tbl">
                                <thead><tr>
                                    <th>{{ __('Phone') }}</th><th>{{ __('Result') }}</th><th>{{ __('Score') }}</th><th>{{ __('Billed') }}</th><th>{{ __('When') }}</th>
                                </tr></thead>
                                <tbody>
                                @foreach ($history as $h)
                                    <tr>
                                        <td>{{ $h->phone }}</td>
                                        <td>
                                            @if ($h->billed_as === 'none')
                                                <span class="sc-pill sc-pill--muted">{{ __('No record') }}</span>
                                            @else
                                                <span class="sc-pill">{{ ucfirst($h->score_band ?? 'rated') }}</span>
                                            @endif
                                        </td>
                                        <td>{{ $h->score !== null ? rtrim(rtrim(number_format($h->score, 1), '0'), '.') : '—' }}</td>
                                        <td>
                                            @switch($h->billed_as)
                                                @case('plan') <span class="sc-billed">{{ __('Plan') }}</span> @break
                                                @case('free') <span class="sc-billed">{{ __('Free') }}</span> @break
                                                @case('credit') <span class="sc-billed">1 {{ __('credit') }}</span> @break
                                                @default <span class="sc-billed sc-billed--muted">—</span>
                                            @endswitch
                                        </td>
                                        <td>{{ $h->created_at->diffForHumans() }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                        {{ $history->links() }}
                    </div>
                @endif

            </div>
        </div>
    </div>
</div>

<style>
    .sc-head { margin-bottom:20px; }
    .sc-breadcrumb { display:flex; align-items:center; gap:6px; list-style:none; padding:0; margin:0 0 8px; font-size:12px; color:#9ca3af; }
    .sc-breadcrumb li:not(:last-child)::after { content:''; display:inline-block; width:5px; height:5px; border-right:1.5px solid #d1d5db; border-top:1.5px solid #d1d5db; transform:rotate(45deg); margin-left:6px; opacity:.6; }
    .sc-breadcrumb a { color:#185FA5; text-decoration:none; font-weight:500; }
    .sc-title { font-size:22px; font-weight:600; color:#111827; margin:0 0 5px; }
    .sc-sub { font-size:13.5px; color:#6b7280; margin:0; max-width:70ch; line-height:1.6; }
    .sc-flash { padding:11px 15px; border-radius:10px; font-size:13.5px; margin-bottom:16px; }
    .sc-flash--ok { background:#E1F5EE; color:#0F6E56; border:0.5px solid #A7DFC9; }
    .sc-flash--err { background:#FBE9E7; color:#B42318; border:0.5px solid #F3C4BC; }

    .sc-cover { display:flex; align-items:center; gap:26px; flex-wrap:wrap; background:#F8FAFC; border:0.5px solid #e5e7eb; border-radius:14px; padding:16px 20px; margin-bottom:18px; }
    .sc-cover__item { display:flex; align-items:center; gap:11px; }
    .sc-cover__item i { font-size:22px; color:#185FA5; }
    .sc-cover__item strong { display:block; font-size:15px; color:#111827; font-weight:700; line-height:1.2; }
    .sc-cover__item span { font-size:11.5px; color:#6b7280; }
    .sc-cover__buy { margin-left:auto; font-size:12.5px; font-weight:600; color:#185FA5; text-decoration:none; display:inline-flex; align-items:center; gap:4px; }
    .sc-cover__buy:hover { color:#0F4A84; }

    .sc-lookcard { border:0.5px solid #d7e3f2; background:#fff; border-radius:14px; padding:20px 22px; box-shadow:0 1px 2px rgba(16,24,40,.04); }
    .sc-lookcard__label { display:block; font-size:12px; font-weight:600; color:#374151; margin-bottom:9px; }
    .sc-lookup { display:flex; gap:10px; align-items:stretch; max-width:560px; }
    .sc-lookup__field { flex:1; min-width:0; display:flex; align-items:center; border:0.5px solid #d1d5db; border-radius:11px; overflow:hidden; background:#fff; }
    .sc-lookup__field:focus-within { border-color:#185FA5; box-shadow:0 0 0 3px rgba(24,95,165,.1); }
    .sc-lookup__flag { padding:0 12px; font-size:15px; background:#f9fafb; border-right:0.5px solid #e5e7eb; align-self:stretch; display:flex; align-items:center; }
    .sc-lookup__input { flex:1; min-width:0; padding:12px 14px; border:none; outline:none; font-size:14.5px; color:#111827; }
    .sc-lookup__btn { display:inline-flex; align-items:center; justify-content:center; gap:7px; background:#185FA5; color:#fff; border:none; border-radius:11px; font-size:14px; font-weight:600; padding:0 22px; cursor:pointer; white-space:nowrap; }
    /* On narrow widths the input + button share a row too tightly (placeholder truncates) — stack them. */
    @media (max-width:620px){
        .sc-lookup { flex-direction:column; max-width:none; }
        .sc-lookup__btn { padding:13px 22px; }
    }
    .sc-lookup__btn:hover { background:#0F4A84; }
    .sc-lookup__btn:disabled { opacity:.5; cursor:not-allowed; }
    .sc-lookup__hint { font-size:12px; color:#374151; margin:11px 0 0; }
    .sc-lookup__hintmute { color:#9ca3af; }

    .sc-report { border:0.5px solid #e5e7eb; border-radius:16px; padding:24px; margin-top:22px; }
    .sc-report--empty { text-align:center; padding:44px 24px; color:#6b7280; }
    .sc-report--empty i { font-size:42px; color:#cbd5e1; }
    .sc-report--empty h3 { font-size:17px; color:#111827; margin:12px 0 6px; }
    .sc-report--empty p { font-size:13px; max-width:60ch; margin:0 auto; line-height:1.6; }
    .sc-report__top { display:flex; align-items:center; justify-content:space-between; gap:12px; margin-bottom:18px; flex-wrap:wrap; }
    .sc-report__id { display:flex; align-items:center; gap:10px; }
    .sc-report__phone { font-size:16px; font-weight:700; color:#111827; }
    .sc-badge { font-size:11px; font-weight:600; padding:3px 10px; border-radius:99px; display:inline-flex; align-items:center; gap:4px; }
    .sc-badge--claimed { background:#E1F5EE; color:#0F6E56; }
    .sc-badge--unclaimed { background:#f3f4f6; color:#6b7280; }
    .sc-report__meta { font-size:11.5px; color:#9ca3af; }

    .sc-grid { display:grid; grid-template-columns:260px 1fr; gap:24px; }
    @media (max-width:800px){ .sc-grid { grid-template-columns:1fr; } }
    .sc-gauge-card { border:0.5px solid #e5e7eb; border-radius:14px; padding:24px; text-align:center; display:flex; flex-direction:column; align-items:center; gap:13px; }
    .sc-gauge { width:170px; height:170px; border-radius:50%; display:flex; align-items:center; justify-content:center; }
    .sc-gauge--unrated { background:#f3f4f6; }
    .sc-gauge__inner { width:132px; height:132px; background:#fff; border-radius:50%; display:flex; flex-direction:column; align-items:center; justify-content:center; box-shadow:inset 0 0 0 0.5px #eef2f6; }
    .sc-gauge__num { font-size:42px; font-weight:800; color:#111827; line-height:1; }
    .sc-gauge__of { font-size:12px; color:#9ca3af; margin-top:2px; }
    .sc-gauge__grade { font-size:38px; font-weight:800; color:#9ca3af; }
    .sc-band { font-size:13px; font-weight:600; padding:5px 14px; border-radius:99px; margin:0; }
    .sc-gauge__hint { font-size:11.5px; color:#9ca3af; margin:0; }

    .sc-detail { display:flex; flex-direction:column; gap:18px; }
    .sc-stats { display:grid; grid-template-columns:repeat(3,1fr); gap:12px; }
    @media (max-width:560px){ .sc-stats { grid-template-columns:repeat(2,1fr); } }
    .sc-stat { border:0.5px solid #e5e7eb; border-radius:12px; padding:13px; display:flex; flex-direction:column; gap:3px; }
    .sc-stat__n { font-size:19px; font-weight:700; color:#111827; }
    .sc-stat__l { font-size:11px; color:#6b7280; }
    .sc-detail__label { font-size:11px; text-transform:uppercase; letter-spacing:.06em; color:#9ca3af; font-weight:600; margin:0; }
    .sc-bars { display:flex; flex-direction:column; gap:10px; }
    .sc-bar { display:grid; grid-template-columns:130px 1fr 30px; align-items:center; gap:10px; font-size:12.5px; }
    .sc-bar__label { color:#374151; }
    .sc-bar__track { height:7px; background:#eef2f6; border-radius:99px; overflow:hidden; }
    .sc-bar__fill { display:block; height:100%; border-radius:99px; }
    .sc-bar__val { text-align:right; color:#6b7280; font-variant-numeric:tabular-nums; }
    .sc-notes { margin:0; padding-left:18px; font-size:12.5px; color:#6b7280; line-height:1.8; }
    .sc-report__foot { display:flex; gap:9px; font-size:12px; color:#6b7280; line-height:1.6; margin:20px 0 0; padding-top:16px; border-top:0.5px solid #eef2f6; }
    .sc-report__foot i { font-size:16px; color:#185FA5; flex:none; margin-top:1px; }

    .sc-topup { border:0.5px solid #e5e7eb; border-radius:14px; padding:20px 22px; margin-top:16px; background:#FAFBFC; }
    .sc-topup__head h3 { font-size:15px; font-weight:600; color:#111827; margin:0 0 3px; }
    .sc-topup__head p { font-size:12px; color:#6b7280; margin:0 0 16px; }
    .sc-topup__row { display:flex; align-items:flex-end; gap:16px; flex-wrap:wrap; }
    .sc-topup__row label { display:block; font-size:11px; color:#6b7280; margin-bottom:5px; font-weight:600; }
    .sc-topup__qtyinput { width:90px; padding:9px 11px; border:0.5px solid #d1d5db; border-radius:9px; font-size:14px; outline:none; }
    .sc-topup__qtyinput:focus { border-color:#185FA5; box-shadow:0 0 0 3px rgba(24,95,165,.1); }
    .sc-topup__phone { display:flex; align-items:center; border:0.5px solid #d1d5db; border-radius:9px; overflow:hidden; }
    .sc-topup__phone:focus-within { border-color:#185FA5; box-shadow:0 0 0 3px rgba(24,95,165,.1); }
    .sc-topup__flag { padding:0 10px; background:#f9fafb; border-right:0.5px solid #e5e7eb; align-self:stretch; display:flex; align-items:center; font-size:13px; }
    .sc-topup__phoneinput { padding:9px 11px; border:none; outline:none; font-size:14px; width:150px; }
    .sc-topup__total span { font-size:18px; font-weight:700; color:#111827; }
    .sc-topup__btn { background:#185FA5; color:#fff; border:none; border-radius:9px; font-size:13.5px; font-weight:600; padding:11px 20px; cursor:pointer; white-space:nowrap; }
    @media (max-width:520px){
        .sc-topup__row { flex-direction:column; align-items:stretch; }
        .sc-topup__phone, .sc-topup__phoneinput, .sc-topup__qtyinput, .sc-topup__btn { width:100%; }
    }
    .sc-topup__btn:hover { background:#0F4A84; }
    .sc-topup__btn.sc-btn--confirm { background:#B45309; }
    .sc-topup__btn.sc-btn--confirm:hover { background:#92400E; }

    .sc-hist { margin-top:28px; }
    .sc-hist__title { font-size:15px; font-weight:600; color:#111827; margin:0 0 12px; }
    .sc-tbl { width:100%; border-collapse:collapse; font-size:12.5px; }
    .sc-tbl th { text-align:left; font-size:10.5px; text-transform:uppercase; letter-spacing:.05em; color:#9ca3af; font-weight:600; padding:8px 10px; border-bottom:0.5px solid #e5e7eb; }
    .sc-tbl td { padding:11px 10px; border-bottom:0.5px solid #f1f5f9; color:#374151; }
    .sc-pill { font-size:11px; font-weight:600; padding:2px 9px; border-radius:99px; background:#E6F1FB; color:#185FA5; }
    .sc-pill--muted { background:#f3f4f6; color:#6b7280; }
    .sc-billed { font-size:11.5px; color:#6b7280; }
    .sc-billed--muted { color:#cbd5e1; }
</style>

@push('script')
<script>
(function () {
    // ── Lookup: confirm a credit spend before submitting ─────────────
    var form = document.getElementById('scLookupForm');
    var willCharge = @json($willCharge);
    var creditCost = '{{ $sym }}{{ rtrim(rtrim(number_format($price, 2), '0'), '.') }}';
    if (form && willCharge && window.csConfirm) {
        form.addEventListener('submit', function (e) {
            if (form.dataset.confirmed === '1') return;
            e.preventDefault();
            csConfirm({
                title: '{{ __("Run this screening?") }}',
                message: '{{ __("This will use 1 screening credit") }} (' + creditCost + ').',
                confirmText: '{{ __("Screen tenant") }}',
                cancelText: '{{ __("Cancel") }}'
            }).then(function (ok) {
                if (ok) { form.dataset.confirmed = '1'; form.submit(); }
            });
        });
    }

    // ── Top-up: STK flow (reuses the unified credit rail) ────────────
    var qtyEl   = document.getElementById('scQty');
    var phoneEl = document.getElementById('scBuyPhone');
    var totalEl = document.getElementById('scTotal');
    var buyBtn  = document.getElementById('scBuyBtn');
    if (!buyBtn) return;

    var price = {{ $price }};
    var sym   = '{{ $sym }}';
    function fmt(n){ return sym + Number(n).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }); }
    function qty(){ return Math.max(1, Math.min(1000, parseInt(qtyEl.value || '0', 10) || 0)); }
    function refresh(){ totalEl.textContent = fmt(qty() * price); }
    qtyEl.addEventListener('input', function(){ refresh(); resetConfirm(); });
    phoneEl.addEventListener('input', resetConfirm);
    qtyEl.addEventListener('focus', function(){ this.select(); });
    refresh();

    var origHtml = buyBtn.innerHTML, confirming = false, confirmReset;
    function resetConfirm(){ confirming = false; clearTimeout(confirmReset); buyBtn.innerHTML = origHtml; buyBtn.classList.remove('sc-btn--confirm'); }

    buyBtn.addEventListener('click', function () {
        if (phoneEl.value.trim().length < 9) { toastr.warning('{{ __("Enter the M-Pesa number to charge.") }}'); return; }
        var total = (qty() * price).toFixed(2);

        // Two-tap confirm — first tap shows the exact total, second charges.
        if (!confirming) {
            confirming = true;
            buyBtn.innerHTML = '{{ __("Confirm") }} ' + fmt(total) + ' →';
            buyBtn.classList.add('sc-btn--confirm');
            confirmReset = setTimeout(resetConfirm, 8000);
            return;
        }
        clearTimeout(confirmReset); confirming = false;

        if (window.mpesaWait) mpesaWait.show({ amount: fmt(total) });

        var fd = new FormData();
        fd.append('_token', '{{ csrf_token() }}');
        fd.append('quantity', qty());
        fd.append('phone', phoneEl.value.trim());

        fetch('{{ route("owner.screening.credits.checkout") }}', { method: 'POST', body: fd })
            .then(function(r){ return r.json(); })
            .then(function(data){
                if (data.success) {
                    var pusher  = new Pusher(window.Laravel.pusher_key, { cluster: window.Laravel.pusher_cluster });
                    var channel = pusher.subscribe('transaction.' + data.transaction_id);
                    var timeout = setTimeout(function(){ window.location.href = data.redirect_url; }, 120000);
                    channel.bind('MpesaTransactionProcessed', function(){ clearTimeout(timeout); window.location.href = data.redirect_url + '&callback=true&stk_success=true'; });
                    channel.bind('MpesaTransactionDeclined', function(){ clearTimeout(timeout); if (window.mpesaWait) mpesaWait.hide(); resetConfirm(); toastr.error('{{ __("Payment was declined. Please try again.") }}'); });
                } else {
                    if (window.mpesaWait) mpesaWait.hide(); resetConfirm();
                    toastr.error(data.error || '{{ __("Payment failed. Please try again.") }}');
                }
            })
            .catch(function(){ if (window.mpesaWait) mpesaWait.hide(); resetConfirm(); toastr.error('{{ __("Something went wrong. Please try again.") }}'); });
    });
})();
</script>
@endpush
@endsection
