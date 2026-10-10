{{-- Confirm-receipt nudge modal — shown once per login session when the buyer has delivered,
     still-held orders awaiting their confirmation. Confirming releases the held payment to the
     seller. $pendingConfirmOrders is provided by a composer (AppServiceProvider). --}}
@php $orders = $pendingConfirmOrders ?? collect(); @endphp
@if ($orders->isNotEmpty())
<div id="csCrModal" class="cs-cr" role="dialog" aria-modal="true" aria-labelledby="csCrTitle">
  <div class="cs-cr__card">
    <div class="cs-cr__badge">📦</div>
    <div class="cs-cr__eyebrow">{{ __('Action needed') }}</div>
    <h3 id="csCrTitle" class="cs-cr__title">{{ trans_choice('Confirm you received your order|Confirm you received your orders', $orders->count()) }}</h3>
    <p class="cs-cr__lead">{{ __('These were marked delivered. Once you have each one in good order, tap Confirm — that releases the payment to the seller. Nothing else is needed.') }}</p>

    <div class="cs-cr__list">
      @foreach ($orders as $order)
        @php
          $items = $order->orderItems->map(fn ($i) => $i->product->name ?? __('Item'))->filter()->take(3)->implode(', ');
        @endphp
        <div class="cs-cr__row" data-cr-row>
          <div class="cs-cr__info">
            <div class="cs-cr__ref">{{ __('Order') }} #{{ $order->order_id }}</div>
            @if ($items !== '')<div class="cs-cr__items">{{ $items }}</div>@endif
          </div>
          <button type="button" class="cs-cr__confirm"
                  data-cr-url="{{ route('tenant.product_order.confirm-receipt', $order->id) }}">{{ __('Confirm receipt') }}</button>
        </div>
      @endforeach
    </div>

    <div class="cs-cr__actions">
      <button type="button" class="cs-cr__later" data-cr-dismiss>{{ __('Later') }}</button>
    </div>
  </div>
</div>
<style>
  .cs-cr { position:fixed; inset:0; z-index:1300; background:rgba(17,24,34,.30);
    backdrop-filter:blur(5px); -webkit-backdrop-filter:blur(5px);
    display:none; align-items:center; justify-content:center; padding:20px; animation:csCrFade .4s ease both; }
  .cs-cr__card { background:#fff; border-radius:20px; max-width:440px; width:100%; padding:28px 26px 22px; text-align:center;
    box-shadow:0 18px 52px rgba(20,23,28,.22); animation:csCrIn .5s cubic-bezier(.2,.75,.3,1) .06s both; max-height:88vh; overflow:auto; }
  @keyframes csCrFade { from { opacity:0; } }
  @keyframes csCrIn { from { opacity:0; transform:translateY(10px) scale(.985); } }
  @media (prefers-reduced-motion: reduce) { .cs-cr, .cs-cr__card { animation:none; } }
  .cs-cr__badge { width:60px; height:60px; border-radius:17px; margin:0 auto 14px; display:grid; place-items:center; font-size:28px;
    background:linear-gradient(135deg,#E6F6EE,#E8F1FB); }
  .cs-cr__eyebrow { font-size:11px; font-weight:700; letter-spacing:.14em; text-transform:uppercase; color:#0F6E56; margin-bottom:7px; }
  .cs-cr__title { font-size:20px; font-weight:800; color:#1b1e22; margin:0 0 9px; line-height:1.25; letter-spacing:-.01em; }
  .cs-cr__lead { font-size:14px; color:#4a4f57; line-height:1.55; margin:0 0 18px; }
  .cs-cr__list { display:flex; flex-direction:column; gap:10px; text-align:left; margin:0 0 18px; }
  .cs-cr__row { display:flex; align-items:center; justify-content:space-between; gap:12px;
    border:1px solid #E4E9F0; border-radius:12px; padding:12px 14px; }
  .cs-cr__ref { font-size:13.5px; font-weight:700; color:#1F2A37; }
  .cs-cr__items { font-size:12px; color:#8A97A8; margin-top:2px; }
  .cs-cr__confirm { background:#0F6E56; color:#fff; border:none; border-radius:10px; padding:10px 14px;
    font-size:13px; font-weight:700; cursor:pointer; white-space:nowrap; transition:.15s; }
  .cs-cr__confirm:hover { background:#0B5945; }
  .cs-cr__confirm:disabled { opacity:.6; cursor:default; }
  .cs-cr__actions { display:flex; justify-content:center; }
  .cs-cr__later { background:#f3f4f6; color:#374151; border:none; border-radius:11px; padding:11px 22px;
    font-size:13.5px; font-weight:700; cursor:pointer; transition:.15s; }
  .cs-cr__later:hover { background:#e5e7eb; }
</style>
<script>
  (function () {
    var modal = document.getElementById('csCrModal');
    if (!modal) return;
    var token = '{{ csrf_token() }}';

    function closeModal() { modal.style.display = 'none'; }

    modal.querySelectorAll('[data-cr-dismiss]').forEach(function (el) {
      el.addEventListener('click', closeModal);
    });
    modal.addEventListener('click', function (e) { if (e.target === modal) closeModal(); });

    modal.querySelectorAll('.cs-cr__confirm').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var url = btn.getAttribute('data-cr-url');
        var row = btn.closest('[data-cr-row]');
        btn.disabled = true;
        btn.textContent = '{{ __('Confirming…') }}';
        fetch(url, {
          method: 'POST',
          headers: { 'X-CSRF-TOKEN': token, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
          credentials: 'same-origin'
        }).then(function (r) { return r.json().catch(function () { return {}; }); })
          .then(function (res) {
            if (res && res.success === false) {
              btn.disabled = false;
              btn.textContent = '{{ __('Confirm receipt') }}';
              return;
            }
            if (row) row.remove();
            btn.textContent = '{{ __('Confirmed') }}';
            if (!modal.querySelector('[data-cr-row]')) closeModal();
          }).catch(function () {
            btn.disabled = false;
            btn.textContent = '{{ __('Confirm receipt') }}';
          });
      });
    });

    // Auto-open once per browser session (not once per page), and only if the viewer hasn't
    // already dismissed/confirmed it this session. Reveal reliably whether or not 'load' has
    // already fired. Sits above the feature-announcement modal (z 1300 > 1200) if both exist.
    var SEEN = 'cs_cr_modal_seen';
    function markSeen() { try { sessionStorage.setItem(SEEN, '1'); } catch (e) {} }
    var alreadySeen = false;
    try { alreadySeen = sessionStorage.getItem(SEEN) === '1'; } catch (e) {}

    function reveal() {
        if (alreadySeen) return;
        modal.style.display = 'flex';
        markSeen();
    }
    // "Later" / confirm should also stop it re-opening this session.
    modal.querySelectorAll('[data-cr-dismiss]').forEach(function (el) { el.addEventListener('click', markSeen); });
    modal.querySelectorAll('.cs-cr__confirm').forEach(function (el) { el.addEventListener('click', markSeen); });

    if (!alreadySeen) {
        if (document.readyState === 'complete') { reveal(); }
        else { window.addEventListener('load', reveal); }
    }
  })();
</script>
@endif
