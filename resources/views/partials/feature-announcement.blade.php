{{-- "What's new" modal — reusable across every role. Shows the newest unseen announcement;
     "Got it" marks it seen (so it never nags again). $featureAnnouncements is shared by a composer. --}}
@php
  $fa = ($featureAnnouncements ?? collect())->first();
  $faLink = $fa ? $fa->resolvedLink((int) auth()->user()->role) : null;
@endphp
@if ($fa)
<div id="csFaModal" class="cs-fa" role="dialog" aria-modal="true" aria-labelledby="csFaTitle">
  <div class="cs-fa__card">
    <div class="cs-fa__badge">{{ $fa->icon ?: '✨' }}</div>
    <div class="cs-fa__new">{{ __("What's new") }}</div>
    <h3 id="csFaTitle" class="cs-fa__title">{{ $fa->title }}</h3>
    <div class="cs-fa__body">{!! nl2br(e($fa->body)) !!}</div>
    <div class="cs-fa__actions">
      @if ($faLink)
        <a href="{{ $faLink }}" target="_blank" rel="noopener" class="cs-fa__btn cs-fa__btn--ghost" data-cs-fa-dismiss>
          {{ $fa->link_label ?: __('Learn more') }}
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M5 12h14M13 6l6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </a>
      @endif
      <button type="button" class="cs-fa__btn cs-fa__btn--primary" data-cs-fa-dismiss>{{ __('Got it') }}</button>
    </div>
  </div>
</div>
<style>
  /* Softened: a light, blurred backdrop (frosted glass) instead of a heavy dark scrim, and a
     gentle fade + rise — keeps the guaranteed-see reach of a modal without the "slam". */
  .cs-fa { position:fixed; inset:0; z-index:1200; background:rgba(17,24,34,.28);
    backdrop-filter:blur(5px); -webkit-backdrop-filter:blur(5px);
    display:flex; align-items:center; justify-content:center; padding:20px; animation:csFaFade .45s ease both; }
  .cs-fa__card { background:#fff; border-radius:20px; max-width:420px; width:100%; padding:30px 28px 26px; text-align:center;
    box-shadow:0 18px 52px rgba(20,23,28,.20); animation:csFaIn .5s cubic-bezier(.2,.75,.3,1) .08s both; }
  @keyframes csFaFade { from { opacity:0; } }
  @keyframes csFaIn { from { opacity:0; transform:translateY(10px) scale(.985); } }
  @media (prefers-reduced-motion: reduce) { .cs-fa, .cs-fa__card { animation:none; } }
  .cs-fa__badge { width:64px; height:64px; border-radius:18px; margin:0 auto 16px; display:grid; place-items:center; font-size:32px;
    background:linear-gradient(135deg,#E6F1FB,#EEEDFE); }
  .cs-fa__new { font-size:11.5px; font-weight:700; letter-spacing:.14em; text-transform:uppercase; color:#185FA5; margin-bottom:8px; }
  .cs-fa__title { font-size:21px; font-weight:800; color:#1b1e22; margin:0 0 12px; line-height:1.25; letter-spacing:-.01em; }
  .cs-fa__body { font-size:14.5px; color:#4a4f57; line-height:1.6; margin:0 0 22px; }
  .cs-fa__actions { display:flex; flex-direction:column; gap:9px; }
  .cs-fa__btn { display:inline-flex; align-items:center; justify-content:center; gap:7px; border:none; border-radius:12px;
    padding:13px 18px; font-size:14.5px; font-weight:700; cursor:pointer; text-decoration:none; transition:.15s; }
  .cs-fa__btn--primary { background:#185FA5; color:#fff !important; box-shadow:0 10px 24px -12px rgba(24,95,165,.8); }
  .cs-fa__btn--primary:hover { background:#0F4A84; }
  .cs-fa__btn--ghost { background:#f3f4f6; color:#374151 !important; }
  .cs-fa__btn--ghost:hover { background:#e5e7eb; }
</style>
<script>
  (function () {
    var modal = document.getElementById('csFaModal');
    if (!modal) return;
    var url = '{{ route('feature-announcement.seen', $fa->id) }}';
    var token = '{{ csrf_token() }}';
    function dismiss() {
      modal.style.display = 'none';
      try {
        fetch(url, { method: 'POST', headers: { 'X-CSRF-TOKEN': token, 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' });
      } catch (e) {}
    }
    modal.querySelectorAll('[data-cs-fa-dismiss]').forEach(function (el) { el.addEventListener('click', dismiss); });
    modal.addEventListener('click', function (e) { if (e.target === modal) dismiss(); });
  })();
</script>
@endif
