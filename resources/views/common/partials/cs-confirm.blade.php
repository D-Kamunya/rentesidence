{{--
    cs-confirm — the system's own confirmation dialog, replacing native browser confirm().
    Included globally via common.layouts.script, so it's available on every page.

    Use declaratively:
        <form ... data-cs-confirm="Message" data-cs-confirm-title="Title"
                  data-cs-confirm-ok="Yes, do it" data-cs-confirm-tone="danger">
        <a href="..." data-cs-confirm="Delete this?">Delete</a>

    Or programmatically:
        csConfirm({ title, message, confirmText, cancelText, tone }).then(ok => { if (ok) … });
--}}
<div id="csConfirmOverlay" class="cs-cf-overlay" aria-hidden="true">
    <div class="cs-cf" role="alertdialog" aria-modal="true" aria-labelledby="csCfTitle" aria-describedby="csCfMsg">
        <div class="cs-cf__icon" id="csCfIcon"></div>
        <h3 class="cs-cf__title" id="csCfTitle"></h3>
        <p class="cs-cf__msg" id="csCfMsg"></p>
        <div class="cs-cf__actions">
            <button type="button" class="cs-cf__btn cs-cf__btn--ghost" id="csCfCancel"></button>
            <button type="button" class="cs-cf__btn cs-cf__btn--go" id="csCfOk"></button>
        </div>
    </div>
</div>

<style>
    .cs-cf-overlay { position:fixed; inset:0; z-index:100000; display:none; align-items:center; justify-content:center;
        background:rgba(17,24,39,.5); backdrop-filter:blur(2px); padding:20px; }
    .cs-cf-overlay.is-open { display:flex; }
    .cs-cf { background:#fff; border-radius:14px; width:100%; max-width:400px; padding:26px 24px 20px;
        box-shadow:0 24px 48px rgba(0,0,0,.22); text-align:center;
        animation:csCfPop .18s cubic-bezier(.2,.8,.3,1.15) both; }
    .cs-cf__icon { width:52px; height:52px; border-radius:14px; margin:0 auto 14px; display:flex; align-items:center; justify-content:center; }
    .cs-cf__icon svg { width:26px; height:26px; }
    .cs-cf__icon--primary { background:#E6F1FB; color:#185FA5; }
    .cs-cf__icon--danger  { background:#FAECE7; color:#C2410C; }
    .cs-cf__icon--warning { background:#FEF3E7; color:#B45309; }
    .cs-cf__title { font-size:17px; font-weight:600; color:#111827; margin:0 0 6px; }
    .cs-cf__msg { font-size:13.5px; color:#6b7280; line-height:1.6; margin:0 0 22px; }
    .cs-cf__actions { display:flex; gap:10px; }
    .cs-cf__btn { flex:1; border:none; border-radius:9px; font-size:13.5px; font-weight:600; padding:11px 16px; cursor:pointer; transition:background .13s, transform .1s; }
    .cs-cf__btn:active { transform:translateY(1px); }
    .cs-cf__btn--ghost { background:#f3f4f6; color:#374151; }
    .cs-cf__btn--ghost:hover { background:#e5e7eb; }
    .cs-cf__btn--go { color:#fff; background:#185FA5; }
    .cs-cf__btn--go:hover { background:#0F4A84; }
    .cs-cf__btn--go:focus-visible { outline:2px solid #185FA5; outline-offset:2px; }
    .cs-cf[data-tone="danger"] .cs-cf__btn--go { background:#C2410C; }
    .cs-cf[data-tone="danger"] .cs-cf__btn--go:hover { background:#9A3412; }
    .cs-cf[data-tone="warning"] .cs-cf__btn--go { background:#B45309; }
    .cs-cf[data-tone="warning"] .cs-cf__btn--go:hover { background:#92400E; }
    @keyframes csCfPop { from { opacity:0; transform:scale(.92) translateY(6px); } to { opacity:1; transform:scale(1) translateY(0); } }
    @media (prefers-reduced-motion: reduce) { .cs-cf { animation:none; } }
</style>

<script>
(function () {
    if (window.csConfirm) return; // init once

    var ICONS = {
        primary: '<svg viewBox="0 0 24 24" fill="none"><path d="M12 8v5m0 3h.01M12 3l9 16H3L12 3z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        danger:  '<svg viewBox="0 0 24 24" fill="none"><path d="M3 6h18M8 6V4a2 2 0 012-2h4a2 2 0 012 2v2m2 0v14a2 2 0 01-2 2H8a2 2 0 01-2-2V6h12z" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        warning: '<svg viewBox="0 0 24 24" fill="none"><path d="M12 9v4m0 4h.01M10.3 3.9L1.8 18a2 2 0 001.7 3h17a2 2 0 001.7-3L14.7 3.9a2 2 0 00-3.4 0z" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>'
    };

    var overlay = document.getElementById('csConfirmOverlay');
    var box     = overlay.querySelector('.cs-cf');
    var iconEl  = document.getElementById('csCfIcon');
    var titleEl = document.getElementById('csCfTitle');
    var msgEl   = document.getElementById('csCfMsg');
    var okBtn   = document.getElementById('csCfOk');
    var cancelBtn = document.getElementById('csCfCancel');

    var current = null;      // active resolver
    var lastFocus = null;    // element to restore focus to

    function close(result) {
        overlay.classList.remove('is-open');
        overlay.setAttribute('aria-hidden', 'true');
        document.removeEventListener('keydown', onKey, true);
        var r = current; current = null;
        if (lastFocus && lastFocus.focus) { try { lastFocus.focus(); } catch (e) {} }
        if (r) r(result);
    }
    function onKey(e) {
        if (!current) return;
        if (e.key === 'Escape') { e.preventDefault(); close(false); }
        else if (e.key === 'Enter') { e.preventDefault(); close(true); }
    }

    window.csConfirm = function (opts) {
        opts = opts || {};
        return new Promise(function (resolve) {
            // If a dialog is somehow open, resolve it as cancelled first.
            if (current) { close(false); }
            current = resolve;
            lastFocus = document.activeElement;

            var tone = opts.tone || 'primary';
            if (!ICONS[tone]) tone = 'primary';
            box.setAttribute('data-tone', tone);
            iconEl.className = 'cs-cf__icon cs-cf__icon--' + tone;
            iconEl.innerHTML = ICONS[tone];
            titleEl.textContent = opts.title || 'Please confirm';
            msgEl.textContent   = opts.message || 'Are you sure you want to continue?';
            okBtn.textContent     = opts.confirmText || 'Confirm';
            cancelBtn.textContent = opts.cancelText || 'Cancel';

            overlay.classList.add('is-open');
            overlay.setAttribute('aria-hidden', 'false');
            document.addEventListener('keydown', onKey, true);
            setTimeout(function () { okBtn.focus(); }, 30);
        });
    };

    // Single-button notice, a drop-in for alert().
    window.csAlert = function (opts) {
        if (typeof opts === 'string') opts = { message: opts };
        opts = opts || {};
        var p = window.csConfirm({
            title: opts.title || 'Notice',
            message: opts.message || '',
            tone: opts.tone || 'primary',
            confirmText: opts.confirmText || 'OK'
        });
        cancelBtn.style.display = 'none';
        return p.then(function () { cancelBtn.style.display = ''; });
    };

    okBtn.addEventListener('click', function () { close(true); });
    cancelBtn.addEventListener('click', function () { close(false); });
    overlay.addEventListener('mousedown', function (e) { if (e.target === overlay) close(false); });

    function opts(el) {
        return {
            message: el.getAttribute('data-cs-confirm') || undefined,
            title: el.getAttribute('data-cs-confirm-title') || undefined,
            confirmText: el.getAttribute('data-cs-confirm-ok') || undefined,
            cancelText: el.getAttribute('data-cs-confirm-cancel') || undefined,
            tone: el.getAttribute('data-cs-confirm-tone') || undefined
        };
    }

    // Declarative: forms — intercept submit, confirm, then submit for real.
    document.addEventListener('submit', function (e) {
        var form = e.target;
        if (!(form instanceof HTMLFormElement) || !form.hasAttribute('data-cs-confirm')) return;
        if (form.dataset.csConfirmed === '1') { delete form.dataset.csConfirmed; return; } // let it through
        e.preventDefault();
        e.stopImmediatePropagation(); // beat jQuery's delegated submit handler until confirmed
        window.csConfirm(opts(form)).then(function (ok) {
            if (!ok) return;
            form.dataset.csConfirmed = '1';
            if (form.requestSubmit) { form.requestSubmit(); } else { form.submit(); }
        });
    }, true);

    // Declarative: links / buttons — intercept click, confirm, then proceed.
    document.addEventListener('click', function (e) {
        var el = e.target.closest('[data-cs-confirm]');
        if (!el || el instanceof HTMLFormElement) return;
        if (el.type === 'submit') return; // handled by the form's submit above
        if (el.dataset.csConfirmed === '1') { delete el.dataset.csConfirmed; return; }
        e.preventDefault();
        e.stopImmediatePropagation();
        window.csConfirm(opts(el)).then(function (ok) {
            if (!ok) return;
            var href = el.getAttribute('href');
            if (href && href !== '#') { window.location.href = href; return; }
            el.dataset.csConfirmed = '1';
            el.click();
        });
    }, true);
})();
</script>
