{{--
    mpesa-stk-waiting — ONE shared "waiting for the M-Pesa prompt" overlay for every STK-push
    flow (credits, invoices, marketplace, subscriptions, Centresidence). Included globally via
    common.layouts.script. Replaces the per-page #mpesa-preloader duplicates.

    API:
        mpesaWait.show({ amount: 'KES 1,200', phone: '2547…' });  // amount/phone optional
        mpesaWait.hide();
    Purely visual: the 2-minute redirect + Pusher acceleration stay in each page's pay logic.
--}}
<div id="mpesaWaitOverlay" class="msw-overlay" aria-hidden="true">
    <div class="msw" role="dialog" aria-modal="true" aria-labelledby="mswTitle">
        <div class="msw__phone">
            <span class="msw__pulse"></span>
            <img src="{{ asset('assets/images/gateway-icon/mpesa.jpg') }}" alt="M-PESA" class="msw__logo">
        </div>
        <h3 class="msw__title" id="mswTitle">{{ __('Check your phone') }}</h3>
        <p class="msw__lead" id="mswLead">{{ __('We\'ve sent an M-Pesa request to your phone. Enter your PIN to approve.') }}</p>
        <div class="msw__amount" id="mswAmount" style="display:none;">
            <span class="msw__amount-cap">{{ __('Approve payment of') }}</span>
            <span class="msw__amount-val" id="mswAmountValue"></span>
        </div>
        <div class="msw__timer">
            <span class="msw__spin"></span>
            <span>{{ __('Waiting') }} · <span id="mswTimer">2:00</span></span>
        </div>
        <p class="msw__note">{{ __('Keep this page open — don\'t refresh or go back.') }}</p>
    </div>
</div>

<style>
    .msw-overlay { position:fixed; inset:0; z-index:99998; display:none; align-items:center; justify-content:center;
        background:rgba(17,24,39,.55); backdrop-filter:blur(3px); padding:20px; }
    .msw-overlay.is-open { display:flex; }
    .msw { background:#fff; border-radius:18px; width:100%; max-width:380px; padding:30px 26px 22px; text-align:center;
        box-shadow:0 26px 52px rgba(0,0,0,.24); animation:mswPop .2s cubic-bezier(.2,.8,.3,1.15) both; }
    .msw__phone { position:relative; width:70px; height:70px; margin:0 auto 18px; display:flex; align-items:center; justify-content:center; }
    .msw__logo { width:56px; height:56px; object-fit:contain; border-radius:14px; position:relative; z-index:1; box-shadow:0 4px 12px rgba(15,110,86,.25); }
    .msw__pulse { position:absolute; inset:0; border-radius:50%; background:#1D9E75; opacity:.25; animation:mswPulse 1.8s ease-out infinite; }
    .msw__title { font-size:18px; font-weight:700; color:#111827; margin:0 0 6px; }
    .msw__lead { font-size:13.5px; color:#6b7280; line-height:1.6; margin:0 0 14px; }
    .msw__amount { display:block; background:#E1F5EE; border:0.5px solid #A7DFC9; border-radius:12px; padding:12px 18px; margin:0 0 18px; }
    .msw__amount-cap { display:block; font-size:10.5px; font-weight:600; text-transform:uppercase; letter-spacing:.07em; color:#0F6E56; opacity:.75; margin-bottom:2px; }
    .msw__amount-val { display:block; font-size:30px; font-weight:800; color:#0F6E56; letter-spacing:-.01em; line-height:1.1; font-variant-numeric:tabular-nums; }
    .msw__timer { display:inline-flex; align-items:center; gap:9px; font-size:13px; font-weight:600; color:#185FA5;
        background:#E6F1FB; border-radius:99px; padding:7px 16px; }
    .msw__timer #mswTimer { font-variant-numeric:tabular-nums; }
    .msw__spin { width:14px; height:14px; border:2.5px solid #b9d4f0; border-top-color:#185FA5; border-radius:50%; animation:mswSpin .8s linear infinite; flex:none; }
    .msw__note { font-size:12px; color:#9ca3af; margin:16px 0 0; }
    @keyframes mswSpin { to { transform:rotate(360deg); } }
    @keyframes mswPop { from { opacity:0; transform:scale(.9) translateY(8px); } to { opacity:1; transform:scale(1) translateY(0); } }
    @keyframes mswPulse { 0% { transform:scale(.85); opacity:.3; } 100% { transform:scale(1.7); opacity:0; } }
    @media (prefers-reduced-motion: reduce) { .msw { animation:none; } .msw__pulse { animation:none; opacity:.15; } }
</style>

<script>
(function () {
    if (window.mpesaWait) return;
    var overlay = document.getElementById('mpesaWaitOverlay');
    var timerEl = document.getElementById('mswTimer');
    var amountEl = document.getElementById('mswAmount');
    var amountValEl = document.getElementById('mswAmountValue');
    var leadEl = document.getElementById('mswLead');
    var defaultLead = leadEl ? leadEl.textContent : '';
    var interval = null;

    window.mpesaWait = {
        show: function (opts) {
            opts = opts || {};
            if (amountEl) {
                if (opts.amount) { if (amountValEl) amountValEl.textContent = opts.amount; amountEl.style.display = 'block'; }
                else { amountEl.style.display = 'none'; }
            }
            if (leadEl) { leadEl.textContent = opts.message || defaultLead; }

            var n = 120;
            if (timerEl) timerEl.textContent = '2:00';
            clearInterval(interval);
            interval = setInterval(function () {
                var m = Math.floor(n / 60), s = n % 60;
                if (timerEl) timerEl.textContent = m + ':' + (s < 10 ? '0' + s : s);
                if (n-- <= 0) clearInterval(interval);
            }, 1000);

            overlay.classList.add('is-open');
            overlay.setAttribute('aria-hidden', 'false');
        },
        hide: function () {
            clearInterval(interval);
            overlay.classList.remove('is-open');
            overlay.setAttribute('aria-hidden', 'true');
        }
    };
})();
</script>
