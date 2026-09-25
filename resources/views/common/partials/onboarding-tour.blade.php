{{-- Reusable onboarding tour engine. Pass $tourKey (string), $tourSteps (array of
     ['ico','title','body','target'?]) and $tourAutoshow (bool). On DESKTOP a step with a resolvable
     target gets a spotlight walk-along (dim everything but the element, tooltip beside it, user can
     click along); on mobile / no target it falls back to a centered carousel card. Completion is
     recorded once via tour.complete; window.csOpenTour() replays it. --}}
@php $tourKey = $tourKey ?? 'intro'; $tourSteps = $tourSteps ?? []; $tourAutoshow = $tourAutoshow ?? false; @endphp
<div id="cstRoot" class="cst" style="display:none;" role="dialog" aria-modal="true" aria-label="{{ __('Guided tour') }}">
  <div class="cst__dim" id="cstDim"></div>
  <div class="cst__spot" id="cstSpot" hidden></div>
  <div class="cst__card" id="cstCard">
    <button type="button" class="cst__skip" id="cstSkip">{{ __('Skip') }}</button>
    <div class="cst__ico" id="cstIco">✨</div>
    <h3 class="cst__title" id="cstTitle"></h3>
    <p class="cst__body" id="cstBody"></p>
    <div class="cst__dots" id="cstDots"></div>
    <div class="cst__actions">
      <button type="button" class="cst__btn cst__btn--ghost" id="cstBack">{{ __('Back') }}</button>
      <button type="button" class="cst__btn cst__btn--primary" id="cstNext">{{ __('Next') }}</button>
    </div>
  </div>
</div>
<style>
  .cst{position:fixed;inset:0;z-index:1300;}
  .cst__dim{position:absolute;inset:0;background:rgba(17,24,34,.55);backdrop-filter:blur(2px);-webkit-backdrop-filter:blur(2px);}
  .cst__spot{position:absolute;border-radius:12px;box-shadow:0 0 0 9999px rgba(17,24,34,.55);pointer-events:none;
    transition:all .25s cubic-bezier(.2,.75,.3,1);outline:2px solid rgba(92,160,230,.9);outline-offset:2px;}
  .cst__card{position:absolute;left:50%;top:50%;transform:translate(-50%,-50%);background:#fff;border-radius:18px;
    width:340px;max-width:calc(100vw - 32px);padding:22px 22px 18px;text-align:center;box-shadow:0 18px 52px rgba(20,23,28,.28);}
  @media (prefers-reduced-motion:no-preference){ .cst__card{transition:top .25s,left .25s,transform .25s;} }
  .cst__skip{position:absolute;top:11px;right:13px;background:none;border:none;color:#9aa2ad;font-size:12.5px;font-weight:600;cursor:pointer;}
  .cst__skip:hover{color:#4a4f57;}
  .cst__ico{width:56px;height:56px;border-radius:15px;margin:2px auto 12px;display:grid;place-items:center;font-size:27px;background:linear-gradient(135deg,#E6F1FB,#EEEDFE);}
  .cst__title{font-size:18.5px;font-weight:800;color:#1b1e22;margin:0 0 8px;line-height:1.25;letter-spacing:-.01em;}
  .cst__body{font-size:14px;color:#4a4f57;line-height:1.55;margin:0 auto 4px;max-width:30ch;}
  .cst__dots{display:flex;gap:6px;justify-content:center;margin:16px 0 14px;}
  .cst__dot{width:6px;height:6px;border-radius:999px;background:#dfe3e8;transition:.2s;}
  .cst__dot.on{background:#185FA5;width:18px;}
  .cst__actions{display:flex;align-items:center;gap:9px;}
  .cst__btn{flex:1;border:none;border-radius:11px;padding:11px 15px;font-size:14px;font-weight:700;cursor:pointer;transition:.15s;}
  .cst__btn--primary{background:#185FA5;color:#fff;}
  .cst__btn--primary:hover{background:#0F4A84;}
  .cst__btn--ghost{background:#f3f4f6;color:#374151;flex:0 0 auto;padding:11px 16px;}
  .cst__btn--ghost:hover{background:#e5e7eb;}
</style>
<script>
  (function () {
    var STEPS = @json($tourSteps);
    var KEY = @json($tourKey);
    var root = document.getElementById('cstRoot');
    if (!root || !STEPS.length) return;
    var dim = document.getElementById('cstDim'), spot = document.getElementById('cstSpot'), card = document.getElementById('cstCard');
    var icoEl = document.getElementById('cstIco'), titleEl = document.getElementById('cstTitle'), bodyEl = document.getElementById('cstBody');
    var dotsEl = document.getElementById('cstDots'), back = document.getElementById('cstBack'), next = document.getElementById('cstNext'), skip = document.getElementById('cstSkip');
    var cur = 0, marked = false, DESKTOP = 900;

    dotsEl.innerHTML = STEPS.map(function (s, i) { return '<span class="cst__dot' + (i === 0 ? ' on' : '') + '"></span>'; }).join('');
    var dots = dotsEl.querySelectorAll('.cst__dot');

    function visible(el) { return el && el.offsetParent !== null && el.getBoundingClientRect().width > 0; }

    function place(i) {
      var s = STEPS[i];
      icoEl.textContent = s.ico || '✨';
      titleEl.textContent = s.title || '';
      bodyEl.textContent = s.body || '';
      dots.forEach(function (d, k) { d.classList.toggle('on', k === i); });
      back.style.visibility = i === 0 ? 'hidden' : 'visible';
      next.textContent = (i === STEPS.length - 1) ? '{{ __('Done') }}' : '{{ __('Next') }}';

      var el = s.target ? document.querySelector(s.target) : null;
      var useSpot = el && visible(el) && window.innerWidth >= DESKTOP;

      if (useSpot) {
        try { el.scrollIntoView({ block: 'center', inline: 'nearest' }); } catch (e) {}
        var r = el.getBoundingClientRect(), pad = 6;
        spot.hidden = false;
        spot.style.top = (r.top - pad) + 'px'; spot.style.left = (r.left - pad) + 'px';
        spot.style.width = (r.width + pad * 2) + 'px'; spot.style.height = (r.height + pad * 2) + 'px';
        dim.style.display = 'none';
        // Tooltip beside the element: to the right if room, else below, else centered.
        card.style.transform = 'none';
        var cw = card.offsetWidth || 340, ch = card.offsetHeight || 260, gap = 16;
        var top, left;
        if (r.right + gap + cw < window.innerWidth) { left = r.right + gap; top = Math.max(12, Math.min(r.top, window.innerHeight - ch - 12)); }
        else if (r.bottom + gap + ch < window.innerHeight) { top = r.bottom + gap; left = Math.max(12, Math.min(r.left, window.innerWidth - cw - 12)); }
        else { left = (window.innerWidth - cw) / 2; top = (window.innerHeight - ch) / 2; }
        card.style.left = left + 'px'; card.style.top = top + 'px';
      } else {
        spot.hidden = true; dim.style.display = 'block';
        card.style.transform = 'translate(-50%,-50%)'; card.style.left = '50%'; card.style.top = '50%';
      }
    }

    function show(i) { cur = Math.max(0, Math.min(STEPS.length - 1, i)); place(cur); }
    function markDone() {
      if (marked) return; marked = true;
      try {
        fetch('{{ route('tour.complete') }}', { method: 'POST',
          headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/x-www-form-urlencoded' },
          credentials: 'same-origin', body: 'key=' + encodeURIComponent(KEY) });
      } catch (e) {}
    }
    function close() { root.style.display = 'none'; markDone(); }

    next.addEventListener('click', function () { if (cur === STEPS.length - 1) close(); else show(cur + 1); });
    back.addEventListener('click', function () { show(cur - 1); });
    skip.addEventListener('click', close);
    dim.addEventListener('click', close);
    window.addEventListener('resize', function () { if (root.style.display !== 'none') place(cur); });

    window.csOpenTour = function () { marked = false; root.style.display = 'block'; show(0); };

    @if ($tourAutoshow)
      root.style.display = 'block'; show(0);
    @endif
  })();
</script>
