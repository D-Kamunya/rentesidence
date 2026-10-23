{{-- Guided-demo chrome: shown only in a demo_mode session. A slim fixed pill (bottom-centre, so it
     never fights a fixed header) marking the demo as read-only with Start-free + Exit actions, plus
     a client write-block: form submits are intercepted with a friendly toast instead of posting.
     The DemoReadonly middleware is the hard server backstop; this is the nice UX layer. --}}
<div id="demoBar" role="status">
  <span class="demoBar__dot"></span>
  <span class="demoBar__txt">{{ __('Live demo — read-only') }}</span>
  <a href="{{ route('frontend') }}" class="demoBar__btn demoBar__btn--cta" data-demo-allow>{{ __('Start free') }}</a>
  <a href="{{ route('demo.exit') }}" class="demoBar__btn" data-demo-allow>{{ __('Exit demo') }}</a>
</div>
<div id="demoToast" aria-live="polite"></div>
<style>
  #demoBar{position:fixed;left:50%;bottom:18px;transform:translateX(-50%);z-index:2147483000;
    display:flex;align-items:center;gap:12px;background:#111826;color:#fff;border-radius:999px;
    padding:9px 10px 9px 16px;box-shadow:0 10px 34px rgba(16,20,28,.34);font-size:13.5px;font-weight:600;
    max-width:calc(100vw - 24px);}
  .demoBar__dot{width:9px;height:9px;border-radius:50%;background:#ef4444;box-shadow:0 0 0 0 rgba(239,68,68,.6);
    animation:demoPulse 2s infinite;flex:0 0 auto;}
  @keyframes demoPulse{0%{box-shadow:0 0 0 0 rgba(239,68,68,.55)}70%{box-shadow:0 0 0 7px rgba(239,68,68,0)}100%{box-shadow:0 0 0 0 rgba(239,68,68,0)}}
  .demoBar__txt{white-space:nowrap;}
  .demoBar__btn{color:#fff;text-decoration:none;border-radius:999px;padding:7px 14px;font-size:13px;font-weight:700;
    background:rgba(255,255,255,.12);transition:.15s;white-space:nowrap;}
  .demoBar__btn:hover{background:rgba(255,255,255,.22);color:#fff;text-decoration:none;}
  .demoBar__btn--cta{background:#185FA5;}
  .demoBar__btn--cta:hover{background:#2f78c4;}
  #demoToast{position:fixed;left:50%;bottom:74px;transform:translateX(-50%);z-index:2147483000;
    display:flex;flex-direction:column;gap:8px;align-items:center;pointer-events:none;}
  .demoToast__item{background:#1b1e22;color:#fff;border-radius:12px;padding:11px 16px;font-size:13.5px;font-weight:600;
    box-shadow:0 10px 30px rgba(20,23,28,.3);max-width:min(92vw,420px);text-align:center;
    opacity:0;transform:translateY(8px);transition:opacity .2s,transform .2s;}
  .demoToast__item.on{opacity:1;transform:translateY(0);}
  @media (max-width:560px){ #demoBar{font-size:12.5px;gap:8px;padding:8px 8px 8px 13px;} .demoBar__btn{padding:6px 11px;} }
</style>
<script>
(function(){
  var MSG = @json(__('This is a read-only live demo — changes are switched off here. Start free to use it for real.'));
  var box = document.getElementById('demoToast');
  function toast(text){
    if(!box) return;
    var el = document.createElement('div');
    el.className = 'demoToast__item';
    el.textContent = text || MSG;
    box.appendChild(el);
    requestAnimationFrame(function(){ el.classList.add('on'); });
    setTimeout(function(){ el.classList.remove('on'); setTimeout(function(){ el.remove(); }, 250); }, 2600);
  }
  // Block any state-changing form submit (capture phase so it beats other handlers).
  document.addEventListener('submit', function(e){
    var f = e.target;
    if(!f || f.hasAttribute('data-demo-allow')) return;
    var m = (f.getAttribute('method') || 'get').toUpperCase();
    if(m === 'GET') return;
    e.preventDefault(); e.stopImmediatePropagation();
    toast();
  }, true);
  // Surface a server-side block notice if one was flashed.
  @if(session('demo_notice')) toast(@json(session('demo_notice'))); @endif
})();
</script>
