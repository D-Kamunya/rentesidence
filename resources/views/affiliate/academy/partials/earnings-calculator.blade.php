@php
    // Live figures — the SAME source the academy content tokens read, so the calculator
    // can never quote a rate the course contradicts. All auto-update when admin edits settings.
    $calc = \App\Services\AcademyContentService::numericData();
    $t = \App\Services\AcademyContentService::tokens(); // pre-formatted display strings
    $curSym = trim(getCurrencySymbol()) ?: 'KSh';
    $curAfter = getCurrencyPlacement() === 'after';
@endphp

<div class="earn-calc mb-4" id="earnCalc">
    <div class="earn-calc__head">
        <div class="earn-calc__head-icon">
            <svg width="18" height="18" viewBox="0 0 20 20" fill="none">
                <rect x="3" y="2.5" width="14" height="15" rx="2" stroke="currentColor" stroke-width="1.5"/>
                <path d="M6.5 6h7M6.5 9.5h7M6.5 13h4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
            </svg>
        </div>
        <div>
            <h5 class="mb-1" style="font-weight:600;">What you could earn</h5>
            <p class="mb-0" style="font-size:12.5px;color:#6b7280;">
                Live rates, straight from the platform. You earn differently depending on each owner's mode — enter how many you expect in each.
            </p>
        </div>
    </div>

    <div class="earn-calc__body">
        {{-- Inputs, grouped by the mode that earns --}}
        <div class="earn-calc__inputs">
            <div class="earn-calc__group">
                <div class="earn-calc__group-head">
                    <span class="earn-calc__tag earn-calc__tag--txn">Transaction mode</span>
                    <span class="earn-calc__group-note">You earn {{ $t['rent_commission'] }} of all rent they collect, each month for {{ $t['recurring_months'] }} months.</span>
                </div>
                <div class="earn-calc__row2">
                    <div class="earn-calc__field">
                        <label>Owners on Transaction</label>
                        <input type="number" id="ecTxnOwners" min="0" step="1" value="8">
                    </div>
                    <div class="earn-calc__field">
                        <label>Avg monthly rent each collects</label>
                        <div class="earn-calc__money"><span>{{ $curSym }}</span><input type="number" id="ecRent" min="0" step="1000" value="60000"></div>
                    </div>
                </div>
            </div>

            <div class="earn-calc__group">
                <div class="earn-calc__group-head">
                    <span class="earn-calc__tag earn-calc__tag--sub">Subscription</span>
                    <span class="earn-calc__group-note">You earn {{ $t['first_rate'] }} of the first payment, then {{ $t['recurring_rate'] }} for {{ $t['recurring_months'] }} months.</span>
                </div>
                <div class="earn-calc__row2">
                    <div class="earn-calc__field">
                        <label>Owners on a paid plan</label>
                        <input type="number" id="ecSubOwners" min="0" step="1" value="4">
                    </div>
                    <div class="earn-calc__field">
                        <label>Avg plan price (monthly)</label>
                        <div class="earn-calc__money"><span>{{ $curSym }}</span><input type="number" id="ecPlan" min="0" step="500" value="3000"></div>
                    </div>
                </div>
            </div>

            <p class="earn-calc__free-note">
                <b>Free-plan owners</b> don't collect a rent fee or a subscription — but they still earn you on the
                <b>usage</b> lines below (and Free owners pay the most on the marketplace, so your cut there is the biggest).
                Move an active rent-collector onto <b>Transaction</b> and you start earning {{ $t['rent_commission'] }} of their rent.
            </p>
        </div>

        {{-- Results --}}
        <div class="earn-calc__results">
            <div class="earn-calc__result-row">
                <span class="earn-calc__result-label">Rent commission / month
                    <em>Transaction owners · {{ $t['rent_commission'] }} of rent collected</em>
                </span>
                <span class="earn-calc__result-value" id="ecRentMonthly">—</span>
            </div>
            <div class="earn-calc__result-row">
                <span class="earn-calc__result-label">Subscription commission ({{ $t['recurring_months'] }} months)
                    <em>{{ $t['first_rate'] }} first month, then {{ $t['recurring_rate'] }} for {{ $t['recurring_months'] }} months</em>
                </span>
                <span class="earn-calc__result-value" id="ecSubsYearly">—</span>
            </div>
            <div class="earn-calc__result-total">
                <span>Projected income · first {{ $t['recurring_months'] }} months</span>
                <span id="ecTotal">—</span>
            </div>
            <p class="earn-calc__footnote">From rent + subscription only, over each owner's {{ $t['recurring_months'] }}-month commission window. Your usage earnings below are on top of this.</p>

            {{-- The usage lines: real rates, no fabricated totals (they vary with how much each owner uses them). --}}
            <div class="earn-calc__plus">
                <div class="earn-calc__plus-head">Plus, on top — on every owner (Free included):</div>
                <ul class="earn-calc__plus-list">
                    <li><span>Marketplace sales</span><em>{{ $t['marketplace_cut'] }} of our fee per sale</em></li>
                    <li><span>Tenant screening</span><em>{{ $t['first_rate'] }} then {{ $t['recurring_rate'] }} of our fee</em></li>
                    <li><span>Lease agreements</span><em>{{ $t['first_rate'] }} then {{ $t['recurring_rate'] }} of our fee</em></li>
                    <li><span>Infrastructure financing</span><em>{{ $t['first_rate'] }} then {{ $t['recurring_rate'] }} of our fee</em></li>
                </ul>
                <p class="earn-calc__plus-note">These vary with how much each owner uses them, so they're not totalled above — they're extra.</p>
            </div>
        </div>
    </div>
</div>

<script>
    (function () {
        var C = @json($calc);
        var curSym = @json($curSym);
        var curAfter = @json($curAfter);

        function money(n) {
            n = Math.round(n || 0);
            var s = n.toLocaleString('en-US');
            return curAfter ? (s + ' ' + curSym) : (curSym + ' ' + s);
        }

        var ids = ['ecTxnOwners', 'ecRent', 'ecSubOwners', 'ecPlan'];

        function num(id) {
            var el = document.getElementById(id);
            var v = el ? parseFloat(el.value) : 0;
            return isNaN(v) || v < 0 ? 0 : v;
        }

        function recompute() {
            var txnOwners = num('ecTxnOwners');
            var rent      = num('ecRent');
            var subOwners = num('ecSubOwners');
            var planPrice = num('ecPlan');

            // Rent: transaction-mode owners only — a flat % of all rent collected, every month.
            var rentMonthly = txnOwners * rent * (C.rent_commission / 100);

            // Subscription: first-month bounty + the recurring share for the rest of the window.
            var subsFirst     = subOwners * planPrice * (C.first_rate / 100);
            var subsRecurring = subOwners * planPrice * (C.recurring_rate / 100) * Math.max(C.recurring_months - 1, 0);
            var subsYearly    = subsFirst + subsRecurring;

            // Rent is a flat % each month for the whole commission window (RECURRING_COMMISSION_MONTHS,
            // read live from settings) — not forever, and not a fixed 12.
            var total = (rentMonthly * C.recurring_months) + subsYearly;

            document.getElementById('ecRentMonthly').textContent = money(rentMonthly);
            document.getElementById('ecSubsYearly').textContent  = money(subsYearly);
            document.getElementById('ecTotal').textContent       = money(total);
        }

        ids.forEach(function (id) {
            var el = document.getElementById(id);
            if (el) { el.addEventListener('input', recompute); }
        });
        recompute();
    })();
</script>

<style>
    .earn-calc {
        background: linear-gradient(180deg, #F3F9FF 0%, #ffffff 60%);
        border: 0.5px solid #cfe2f6;
        border-radius: 14px;
        overflow: hidden;
    }
    .earn-calc__head {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 1.1rem 1.4rem;
        border-bottom: 0.5px solid #e2eefb;
    }
    .earn-calc__head-icon {
        width: 38px; height: 38px; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center;
        background: #185FA5; color: #fff; border-radius: 10px;
    }
    .earn-calc__body {
        display: grid;
        grid-template-columns: 1.1fr 1fr;
        gap: 1.4rem;
        padding: 1.4rem;
    }
    .earn-calc__inputs { display: flex; flex-direction: column; gap: 1.1rem; }
    .earn-calc__group {
        background: #fff; border: 0.5px solid #e2eefb; border-radius: 11px; padding: .9rem 1rem;
    }
    .earn-calc__group-head { display: flex; flex-direction: column; gap: 3px; margin-bottom: .7rem; }
    .earn-calc__tag {
        align-self: flex-start; font-size: 11px; font-weight: 700; letter-spacing: .02em;
        padding: 2px 9px; border-radius: 99px;
    }
    .earn-calc__tag--txn { background: #E1F5EE; color: #0F6E56; }
    .earn-calc__tag--sub { background: #EAF0FB; color: #185FA5; }
    .earn-calc__group-note { font-size: 11.5px; color: #6b7280; line-height: 1.4; }
    .earn-calc__row2 { display: grid; grid-template-columns: 1fr 1fr; gap: .8rem; }
    .earn-calc__field { display: flex; flex-direction: column; }
    .earn-calc__field label { font-size: 12px; font-weight: 600; color: #374151; margin-bottom: 5px; }
    .earn-calc__field input {
        width: 100%; padding: 9px 11px; font-size: 14px; color: #111827;
        background: #fff; border: 0.5px solid #cbd5e1; border-radius: 8px; outline: none;
        transition: border-color .15s, box-shadow .15s; appearance: none;
    }
    .earn-calc__field input:focus { border-color: #185FA5; box-shadow: 0 0 0 3px rgba(24,95,165,.1); }
    .earn-calc__money { position: relative; display: flex; align-items: center; }
    .earn-calc__money > span { position: absolute; left: 11px; font-size: 13px; color: #9ca3af; pointer-events: none; }
    .earn-calc__money input { padding-left: 42px; }
    .earn-calc__free-note {
        font-size: 11.5px; color: #4b5563; line-height: 1.55; margin: 0;
        background: #FEF9EE; border: 0.5px solid #F5E4C3; border-radius: 9px; padding: 9px 11px;
    }

    .earn-calc__results {
        background: #0C2A47; border-radius: 12px; padding: 1.25rem 1.3rem;
        display: flex; flex-direction: column; gap: .5rem; align-self: start;
    }
    .earn-calc__result-row {
        display: flex; align-items: flex-start; justify-content: space-between; gap: 12px;
        padding: .55rem 0; border-bottom: 1px solid rgba(255,255,255,.08);
    }
    .earn-calc__result-label { font-size: 13px; color: #cdddee; display: flex; flex-direction: column; gap: 2px; }
    .earn-calc__result-label em { font-style: normal; font-size: 11px; color: #7fa8cf; }
    .earn-calc__result-value { font-size: 15px; font-weight: 600; color: #fff; white-space: nowrap; font-variant-numeric: tabular-nums; }
    .earn-calc__result-total {
        display: flex; align-items: center; justify-content: space-between; gap: 12px;
        margin-top: .4rem; padding-top: .7rem;
    }
    .earn-calc__result-total > span:first-child { font-size: 13px; font-weight: 600; color: #9FE1CB; }
    .earn-calc__result-total > span:last-child { font-size: 22px; font-weight: 700; color: #fff; white-space: nowrap; font-variant-numeric: tabular-nums; }
    .earn-calc__footnote { font-size: 11px; color: #7fa8cf; line-height: 1.5; margin: .5rem 0 0; }

    .earn-calc__plus { margin-top: .9rem; padding-top: .9rem; border-top: 1px dashed rgba(255,255,255,.14); }
    .earn-calc__plus-head { font-size: 12px; font-weight: 700; color: #9FE1CB; margin-bottom: .5rem; }
    .earn-calc__plus-list { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 5px; }
    .earn-calc__plus-list li { display: flex; align-items: baseline; justify-content: space-between; gap: 10px; }
    .earn-calc__plus-list li span { font-size: 12.5px; color: #e4eefb; }
    .earn-calc__plus-list li em { font-style: normal; font-size: 11px; color: #7fa8cf; white-space: nowrap; }
    .earn-calc__plus-note { font-size: 10.5px; color: #6f97bd; line-height: 1.5; margin: .55rem 0 0; }

    @media (max-width: 820px) {
        .earn-calc__body { grid-template-columns: 1fr; }
    }
    @media (max-width: 480px) {
        .earn-calc__row2 { grid-template-columns: 1fr; }
    }
</style>
