{{-- First-login tenant welcome tour — a short, mobile-first carousel that orients a new tenant
     (pay rent → receipts + rental score → get help). Auto-opens once on first login; replayable via
     window.csOpenTenantTour(). Marked complete server-side (user_tours) so it never nags again. --}}
@php
    $csTourDone = app(\App\Services\OnboardingTourService::class)->completed((int) auth()->id(), 'tenant_intro');
    $csTourSlides = [
        ['ico' => '👋', 'title' => __('Welcome to :app', ['app' => (getOption('app_name') ?: 'Centresidence')]), 'body' => __('Your home account, in one place. Here is how it works — it takes about 30 seconds.')],
        ['ico' => '💳', 'title' => __('Pay rent over M-Pesa'),        'body' => __('Open your rent invoice, tap Pay, and finish on M-Pesa. No queues, no cash — and your receipt is saved automatically.')],
        ['ico' => '🧾', 'title' => __('Receipts & your rental score'), 'body' => __('Every payment is receipted, and builds your portable rental score — proof of a good tenant that you can carry to any future home.')],
        ['ico' => '💬', 'title' => __('Help is one tap away'),         'body' => __('Reach Centresidence support any time from your menu. Tip: you set your own password on first login — keep it private.')],
    ];
@endphp
<div id="csTourModal" class="cs-tour" role="dialog" aria-modal="true" aria-label="{{ __('Welcome tour') }}" style="display:none;">
  <div class="cs-tour__card">
    <button type="button" class="cs-tour__skip" aria-label="{{ __('Skip') }}" data-cs-tour-done>{{ __('Skip') }}</button>
    <div class="cs-tour__track">
      @foreach ($csTourSlides as $i => $slide)
        <div class="cs-tour__slide" data-slide="{{ $i }}" @if($i) hidden @endif>
          <div class="cs-tour__ico">{{ $slide['ico'] }}</div>
          <h3 class="cs-tour__title">{{ $slide['title'] }}</h3>
          <p class="cs-tour__body">{{ $slide['body'] }}</p>
        </div>
      @endforeach
    </div>
    <div class="cs-tour__dots">
      @foreach ($csTourSlides as $i => $slide)
        <span class="cs-tour__dot @if(!$i) is-active @endif" data-dot="{{ $i }}"></span>
      @endforeach
    </div>
    <div class="cs-tour__actions">
      <button type="button" class="cs-tour__btn cs-tour__btn--ghost" id="csTourBack" style="visibility:hidden;">{{ __('Back') }}</button>
      <button type="button" class="cs-tour__btn cs-tour__btn--primary" id="csTourNext">{{ __('Next') }}</button>
      <a href="{{ route('tenant.invoice.index') }}" class="cs-tour__btn cs-tour__btn--primary" id="csTourFinish" data-cs-tour-done style="display:none;">{{ __('View my rent') }} ›</a>
    </div>
  </div>
</div>
<style>
  .cs-tour{position:fixed;inset:0;z-index:1250;background:rgba(17,24,34,.30);backdrop-filter:blur(5px);-webkit-backdrop-filter:blur(5px);
    display:flex;align-items:center;justify-content:center;padding:18px;animation:csTourFade .4s ease both;}
  .cs-tour__card{background:#fff;border-radius:20px;max-width:400px;width:100%;padding:26px 24px 20px;text-align:center;position:relative;
    box-shadow:0 18px 52px rgba(20,23,28,.22);animation:csTourIn .45s cubic-bezier(.2,.75,.3,1) both;}
  @keyframes csTourFade{from{opacity:0;}}
  @keyframes csTourIn{from{opacity:0;transform:translateY(10px) scale(.99);}}
  @media (prefers-reduced-motion:reduce){.cs-tour,.cs-tour__card{animation:none;}}
  .cs-tour__skip{position:absolute;top:12px;right:14px;background:none;border:none;color:#9aa2ad;font-size:12.5px;font-weight:600;cursor:pointer;}
  .cs-tour__skip:hover{color:#4a4f57;}
  .cs-tour__track{min-height:190px;display:flex;align-items:center;justify-content:center;}
  .cs-tour__slide{width:100%;}
  .cs-tour__ico{width:66px;height:66px;border-radius:18px;margin:6px auto 16px;display:grid;place-items:center;font-size:32px;
    background:linear-gradient(135deg,#E6F1FB,#EEEDFE);}
  .cs-tour__title{font-size:20px;font-weight:800;color:#1b1e22;margin:0 0 10px;line-height:1.25;letter-spacing:-.01em;}
  .cs-tour__body{font-size:14.5px;color:#4a4f57;line-height:1.6;margin:0 auto;max-width:34ch;}
  .cs-tour__dots{display:flex;gap:7px;justify-content:center;margin:20px 0 16px;}
  .cs-tour__dot{width:7px;height:7px;border-radius:999px;background:#dfe3e8;transition:.2s;}
  .cs-tour__dot.is-active{background:#185FA5;width:20px;}
  .cs-tour__actions{display:flex;align-items:center;justify-content:space-between;gap:10px;}
  .cs-tour__btn{flex:1;display:inline-flex;align-items:center;justify-content:center;gap:6px;border:none;border-radius:12px;
    padding:12px 16px;font-size:14.5px;font-weight:700;cursor:pointer;text-decoration:none;transition:.15s;}
  .cs-tour__btn--primary{background:#185FA5;color:#fff !important;box-shadow:0 10px 24px -12px rgba(24,95,165,.8);}
  .cs-tour__btn--primary:hover{background:#0F4A84;}
  .cs-tour__btn--ghost{background:#f3f4f6;color:#374151 !important;flex:0 0 auto;padding:12px 18px;}
  .cs-tour__btn--ghost:hover{background:#e5e7eb;}
</style>
<script>
  (function () {
    var modal = document.getElementById('csTourModal');
    if (!modal) return;
    var slides = modal.querySelectorAll('.cs-tour__slide');
    var dots   = modal.querySelectorAll('.cs-tour__dot');
    var back   = document.getElementById('csTourBack');
    var next   = document.getElementById('csTourNext');
    var finish = document.getElementById('csTourFinish');
    var total  = slides.length, cur = 0, marked = false;

    function show(i) {
      cur = Math.max(0, Math.min(total - 1, i));
      slides.forEach(function (s, k) { s.hidden = (k !== cur); });
      dots.forEach(function (d, k) { d.classList.toggle('is-active', k === cur); });
      back.style.visibility = cur === 0 ? 'hidden' : 'visible';
      var last = cur === total - 1;
      next.style.display   = last ? 'none' : '';
      finish.style.display = last ? '' : 'none';
    }
    function markDone() {
      if (marked) return; marked = true;
      try {
        fetch('{{ route('tour.complete') }}', {
          method: 'POST',
          headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/x-www-form-urlencoded' },
          credentials: 'same-origin', body: 'key=tenant_intro'
        });
      } catch (e) {}
    }
    function close() { modal.style.display = 'none'; markDone(); }

    next.addEventListener('click', function () { show(cur + 1); });
    back.addEventListener('click', function () { show(cur - 1); });
    modal.querySelectorAll('[data-cs-tour-done]').forEach(function (el) {
      el.addEventListener('click', function (e) {
        // The finish CTA is a real link — let it navigate, just record completion first.
        if (el.id !== 'csTourFinish') { e.preventDefault(); modal.style.display = 'none'; }
        markDone();
      });
    });
    modal.addEventListener('click', function (e) { if (e.target === modal) close(); });

    window.csOpenTenantTour = function () { marked = false; show(0); modal.style.display = 'flex'; };

    @if (! $csTourDone)
      show(0); modal.style.display = 'flex';
    @endif
  })();
</script>
