{{-- Expand/collapse for remittance batch detail rows. Shared by partner + admin. --}}
<style>
    .rm-toggle { display:inline-flex; align-items:center; gap:5px; background:none; border:none; cursor:pointer;
        color:var(--blue, #185FA5); font-weight:600; font-size:13px; padding:2px 4px; }
    .rm-toggle:hover { text-decoration:underline; }
    .rm-chev { transition:transform .18s; }
    .rm-toggle[aria-expanded="true"] .rm-chev { transform:rotate(180deg); }
</style>
<script>
    (function () {
        document.addEventListener('click', function (e) {
            var btn = e.target.closest('.rm-toggle');
            if (!btn) return;
            var row = document.getElementById(btn.getAttribute('data-target'));
            if (!row) return;
            var open = row.hasAttribute('hidden');
            if (open) { row.removeAttribute('hidden'); } else { row.setAttribute('hidden', ''); }
            btn.setAttribute('aria-expanded', open ? 'true' : 'false');
        });
    })();
</script>
