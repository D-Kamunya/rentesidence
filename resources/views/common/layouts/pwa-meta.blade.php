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

    // --- Install prompt ---
    // Dismiss no longer nags-forever: it SNOOZES for ~21 days, so a user who taps × still gets
    // reminded later. And window.csPwaInstall() is a PERMANENT trigger any "Install app" menu
    // item can call, so the option never disappears (the sticky part). iOS (no beforeinstallprompt)
    // gets an Add-to-Home-Screen hint instead.
    var deferred = null;
    var SNOOZE_KEY = 'cs_pwa_install_snooze';
    var SNOOZE_MS  = 21 * 24 * 60 * 60 * 1000; // ~21 days

    function ls(get, key, val) {
        try { return get ? localStorage.getItem(key) : localStorage.setItem(key, val); } catch (e) { return null; }
    }
    function alreadyStandalone() {
        return window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
    }
    function isIOS() {
        return /iphone|ipad|ipod/i.test(navigator.userAgent) && !window.MSStream;
    }
    function snoozed() {
        var t = parseInt(ls(true, SNOOZE_KEY) || '0', 10);
        return t && Date.now() < t;
    }
    function snooze() { ls(false, SNOOZE_KEY, String(Date.now() + SNOOZE_MS)); }

    function ensureStyles() {
        if (document.getElementById('cs-pwa-style')) return;
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
          + '#cs-pwa-ios{position:fixed;inset:0;z-index:2147483001;display:flex;align-items:flex-end;justify-content:center;'
          + 'background:rgba(4,10,20,.55);padding:0 14px 22px;animation:csPwaFade .2s ease both}'
          + '@keyframes csPwaFade{from{opacity:0}to{opacity:1}}'
          + '#cs-pwa-ios .cs-pwa-ioscard{max-width:420px;width:100%;text-align:center;color:#e7eefb;padding:24px 22px 22px;'
          + 'border-radius:20px;background:linear-gradient(160deg,#0d1b3a,#0B1220);box-shadow:0 20px 50px rgba(0,0,0,.5),0 0 0 1px rgba(46,166,230,.22);'
          + 'font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Helvetica,Arial,sans-serif}'
          + '#cs-pwa-ios img{width:52px;height:52px;border-radius:13px;margin:0 auto 12px;display:block}'
          + '#cs-pwa-ios .cs-pwa-t{font-weight:700;font-size:16px}'
          + '#cs-pwa-ios .cs-pwa-s{font-size:13.5px;color:#a9bbdc;margin:8px 0 18px;line-height:1.5}'
          + '#cs-pwa-ios .cs-pwa-s b{color:#e7eefb}'
          + '#cs-pwa-ios .cs-pwa-go{appearance:none;border:0;cursor:pointer;font:inherit;width:100%;font-weight:600;font-size:14px;'
          + 'color:#061018;padding:12px;border-radius:12px;background:linear-gradient(135deg,#1CE0F5,#2EA6E6)}'
          + '@media (prefers-reduced-motion:reduce){#cs-pwa-install,#cs-pwa-ios{animation:none}}';
        var style = document.createElement('style'); style.id = 'cs-pwa-style'; style.textContent = css; document.head.appendChild(style);
    }

    // iOS: instructional sheet (no programmatic install on iOS Safari).
    function iosHint() {
        if (document.getElementById('cs-pwa-ios')) return;
        ensureStyles();
        var el = document.createElement('div');
        el.id = 'cs-pwa-ios';
        el.setAttribute('role', 'dialog');
        el.innerHTML = '<div class="cs-pwa-ioscard">' +
            '<img src="{{ asset('assets/pwa/icon-192.png') }}" alt="">' +
            '<div class="cs-pwa-t">Install Centresidence</div>' +
            '<div class="cs-pwa-s">Tap the <b>Share</b> button in Safari, then choose <b>Add to Home Screen</b>.</div>' +
            '<button type="button" class="cs-pwa-go">Got it</button></div>';
        document.body.appendChild(el);
        el.querySelector('.cs-pwa-go').addEventListener('click', function () { el.remove(); });
        el.addEventListener('click', function (e) { if (e.target === el) el.remove(); });
    }

    function showBanner(force) {
        if (alreadyStandalone()) return;
        if (document.getElementById('cs-pwa-install')) return;
        if (!force && snoozed()) return;
        ensureStyles();

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
            if (deferred) {
                deferred.prompt();
                deferred.userChoice.finally(function () { deferred = null; });
            } else if (isIOS()) {
                iosHint();
            }
        });
        el.querySelector('.cs-pwa-x').addEventListener('click', function () {
            el.remove();
            snooze(); // quiet for ~21 days, not forever — the menu item stays available
        });
    }

    // PERMANENT entry point — wire an "Install app" menu item anywhere to call this.
    // Handles: already installed (no-op), Android/Chromium (native prompt), iOS (hint),
    // and desktop where the event hasn't fired (surfaces the banner).
    window.csPwaInstall = function () {
        if (alreadyStandalone()) return;
        if (deferred) {
            deferred.prompt();
            deferred.userChoice.finally(function () { deferred = null; });
        } else if (isIOS()) {
            iosHint();
        } else {
            showBanner(true);
        }
    };
    // So a menu item can hide itself once the app is installed / running standalone.
    window.csPwaInstallable = function () { return !alreadyStandalone(); };

    window.addEventListener('beforeinstallprompt', function (e) {
        e.preventDefault();
        deferred = e;
        showBanner(false);
    });
    window.addEventListener('appinstalled', function () {
        deferred = null;
        var b = document.getElementById('cs-pwa-install'); if (b) b.remove();
        ls(false, SNOOZE_KEY, ''); // clear snooze; it's installed now
    });

    // iOS has no beforeinstallprompt — gently surface the banner once (snooze-gated) so iOS
    // users learn they can install too; its Install button opens the Add-to-Home-Screen hint.
    if (isIOS() && !alreadyStandalone() && !snoozed()) {
        window.addEventListener('load', function () { setTimeout(function () { showBanner(false); }, 1400); });
    }

    // Hide any "Install app" menu entries ([data-cs-install]) once running standalone.
    window.addEventListener('load', function () {
        if (!alreadyStandalone()) return;
        document.querySelectorAll('[data-cs-install]').forEach(function (n) {
            var li = n.closest('li') || n; li.style.display = 'none';
        });
    });
})();

// --- Live connection status ---
// A small toast when the connection drops (stays while offline) and returns (auto-dismisses)
// — the native-app cue that the page still knows what's happening.
(function () {
    function toast(msg, off) {
        var t = document.getElementById('cs-net-toast');
        if (!t) {
            t = document.createElement('div'); t.id = 'cs-net-toast';
            t.setAttribute('role', 'status');
            t.style.cssText = 'position:fixed;left:50%;bottom:20px;transform:translateX(-50%) translateY(10px);'
              + 'z-index:2147483002;padding:10px 18px;border-radius:12px;color:#fff;opacity:0;'
              + 'font:600 13px/-1 -apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Helvetica,Arial,sans-serif;'
              + 'box-shadow:0 12px 30px rgba(20,23,28,.28);transition:opacity .25s,transform .25s;pointer-events:none;'
              + 'display:flex;align-items:center;gap:8px;max-width:calc(100vw - 32px)';
            document.body.appendChild(t);
        }
        t.textContent = msg;
        t.style.background = off ? '#B42318' : '#0F6E56';
        t.style.opacity = '1'; t.style.transform = 'translateX(-50%) translateY(0)';
        clearTimeout(t._h);
        if (!off) t._h = setTimeout(function () { t.style.opacity = '0'; t.style.transform = 'translateX(-50%) translateY(10px)'; }, 2600);
    }
    window.addEventListener('offline', function () { toast('{{ __("You’re offline — some actions may not save.") }}', true); });
    window.addEventListener('online',  function () { toast('{{ __("Back online.") }}', false); });
})();
</script>
