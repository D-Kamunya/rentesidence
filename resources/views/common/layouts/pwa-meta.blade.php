{{--
    Centresidence PWA wiring — installability + service worker registration + a
    dismissible, brand-styled install prompt. Included once in every app layout's
    <head>. The install banner is created in JS and appended to <body>, so this
    single head-include is all a layout needs.

    Dev note: the SW is network-first for navigations (never serves stale HTML), so
    it's safe on shared localhost — but still run each app on its OWN port to avoid
    one origin's SW shadowing another (see the dev-env service-worker gotcha).
--}}
<link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
<link rel="apple-touch-icon" href="{{ asset('assets/pwa/icon-192.png') }}">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="Centresidence">
<meta name="application-name" content="Centresidence">

<script>
(function () {
    // --- Service worker registration (secure context only: https or localhost) ---
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function () {
            navigator.serviceWorker.register('{{ asset('service-worker.js') }}').catch(function (e) {
                // Non-fatal — the app works without the SW.
                console.warn('SW registration failed:', e);
            });
        });
    }

    // --- Install prompt (Android/Chromium beforeinstallprompt) ---
    var deferred = null;
    var DISMISS_KEY = 'cs_pwa_install_dismissed';

    function alreadyStandalone() {
        return window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
    }

    function showBanner() {
        if (document.getElementById('cs-pwa-install')) return;
        if (localStorage.getItem(DISMISS_KEY) === '1') return;
        if (alreadyStandalone()) return;

        var css = ''
          + '#cs-pwa-install{position:fixed;left:16px;right:16px;bottom:16px;z-index:2147483000;'
          + 'max-width:440px;margin:0 auto;display:flex;align-items:center;gap:12px;padding:12px 14px;'
          + 'border-radius:16px;color:#e7eefb;background:linear-gradient(160deg,#0d1b3a,#0B1220);'
          + 'box-shadow:0 16px 44px rgba(0,0,0,.45),0 0 0 1px rgba(46,166,230,.22);'
          + 'font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Helvetica,Arial,sans-serif;'
          + 'animation:csPwaUp .28s ease both;}'
          + '@keyframes csPwaUp{from{transform:translateY(14px);opacity:0}to{transform:none;opacity:1}}'
          + '#cs-pwa-install img{width:40px;height:40px;border-radius:10px;flex:0 0 auto}'
          + '#cs-pwa-install .cs-pwa-txt{flex:1 1 auto;min-width:0}'
          + '#cs-pwa-install .cs-pwa-t{font-weight:700;font-size:14px;line-height:1.2}'
          + '#cs-pwa-install .cs-pwa-s{font-size:12px;color:#8fa3c4;margin-top:2px}'
          + '#cs-pwa-install button{appearance:none;border:0;cursor:pointer;font:inherit}'
          + '#cs-pwa-install .cs-pwa-go{font-weight:600;font-size:13px;color:#061018;padding:9px 16px;border-radius:10px;'
          + 'background:linear-gradient(135deg,#1CE0F5,#2EA6E6);box-shadow:0 8px 20px rgba(46,166,230,.35);flex:0 0 auto}'
          + '#cs-pwa-install .cs-pwa-x{background:transparent;color:#8fa3c4;font-size:20px;line-height:1;padding:4px 6px;flex:0 0 auto}'
          + '@media (prefers-reduced-motion:reduce){#cs-pwa-install{animation:none}}';
        var style = document.createElement('style'); style.textContent = css; document.head.appendChild(style);

        var el = document.createElement('div');
        el.id = 'cs-pwa-install';
        el.setAttribute('role', 'dialog');
        el.setAttribute('aria-label', 'Install Centresidence');
        el.innerHTML =
            '<img src="{{ asset('assets/pwa/icon-192.png') }}" alt="">' +
            '<div class="cs-pwa-txt"><div class="cs-pwa-t">Install Centresidence</div>' +
            '<div class="cs-pwa-s">Add to your home screen for quick, app-like access.</div></div>' +
            '<button type="button" class="cs-pwa-go">Install</button>' +
            '<button type="button" class="cs-pwa-x" aria-label="Dismiss">&times;</button>';
        document.body.appendChild(el);

        el.querySelector('.cs-pwa-go').addEventListener('click', function () {
            el.remove();
            if (!deferred) return;
            deferred.prompt();
            deferred.userChoice.finally(function () { deferred = null; });
        });
        el.querySelector('.cs-pwa-x').addEventListener('click', function () {
            el.remove();
            localStorage.setItem(DISMISS_KEY, '1'); // don't nag again on this device
        });
    }

    window.addEventListener('beforeinstallprompt', function (e) {
        e.preventDefault();
        deferred = e;
        showBanner();
    });
    window.addEventListener('appinstalled', function () {
        deferred = null;
        var b = document.getElementById('cs-pwa-install'); if (b) b.remove();
    });
})();
</script>
