{{-- Client-side avatar/logo downscaler. Phone photos are often 15–20 MP / many MB, which blows
     past PHP's upload_max_filesize and used to 500 the profile save. This shrinks the chosen
     image in-browser (canvas) BEFORE upload — memory-safe, no server GD — so any photo just works.
     Attach by giving a file input the class `js-image-resize`. Falls back silently (server still
     guards oversize uploads) where DataTransfer isn't supported. --}}
<script>
(function () {
    var MAX_EDGE = 1280;   // longest side after resize
    var QUALITY  = 0.85;   // JPEG quality
    var SKIP_UNDER = 1.5 * 1024 * 1024; // leave small files untouched

    if (typeof DataTransfer === 'undefined') return; // old Safari — rely on server-side guard

    function attach(input) {
        input.addEventListener('change', function () {
            var file = input.files && input.files[0];
            if (!file || !/^image\//.test(file.type)) return;
            if (input.dataset.resized === '1') { input.dataset.resized = ''; return; } // our own re-dispatch
            if (file.type === 'image/gif' || file.type === 'image/svg+xml') return;     // don't rasterize these

            var url = URL.createObjectURL(file);
            var img = new Image();
            img.onload = function () {
                URL.revokeObjectURL(url);
                var w = img.naturalWidth, h = img.naturalHeight;
                if (Math.max(w, h) <= MAX_EDGE && file.size <= SKIP_UNDER) return; // already small enough

                var scale = Math.min(1, MAX_EDGE / Math.max(w, h));
                var cw = Math.max(1, Math.round(w * scale)), ch = Math.max(1, Math.round(h * scale));
                var canvas = document.createElement('canvas');
                canvas.width = cw; canvas.height = ch;
                canvas.getContext('2d').drawImage(img, 0, 0, cw, ch);

                canvas.toBlob(function (blob) {
                    if (!blob) return;
                    try {
                        var name = (file.name.replace(/\.[^.]+$/, '') || 'photo') + '.jpg';
                        var resized = new File([blob], name, { type: 'image/jpeg' });
                        var dt = new DataTransfer();
                        dt.items.add(resized);
                        input.dataset.resized = '1';
                        input.files = dt.files;
                        input.dispatchEvent(new Event('change', { bubbles: true })); // let the preview JS refresh
                    } catch (e) { /* leave original; server guards oversize */ }
                }, 'image/jpeg', QUALITY);
            };
            img.onerror = function () { URL.revokeObjectURL(url); };
            img.src = url;
        });
    }

    document.querySelectorAll('input[type="file"].js-image-resize').forEach(attach);
})();
</script>
