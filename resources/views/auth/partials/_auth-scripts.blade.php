<script>
    "use strict";
    (function () {
        // Brand slideshow — cross-fade cycle (static first image if reduced-motion / single image)
        var slides = document.querySelectorAll('.cs-auth__slide');
        if (slides.length) {
            slides[0].classList.add('is-active');
            var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            if (slides.length > 1 && !reduce) {
                var i = 0;
                setInterval(function () {
                    slides[i].classList.remove('is-active');
                    i = (i + 1) % slides.length;
                    slides[i].classList.add('is-active');
                }, 7000);
            }
        }

        // Password show/hide — each eye toggles the input inside its own field wrap
        // (login has one, the reset page has two: password + confirm).
        document.querySelectorAll('.cs-fld__eye').forEach(function (eye) {
            eye.addEventListener('click', function () {
                var input = eye.parentElement.querySelector('.cs-fld__input');
                if (!input) return;
                input.type = input.type === 'password' ? 'text' : 'password';
                eye.style.color = input.type === 'text' ? '#93c5fd' : '';
            });
        });

        // Demo credential fill (login only — no-ops elsewhere)
        var demos = {
            adminCredentialShow: ['admin@gmail.com', '123456'],
            ownerCredentialShow: ['owner@gmail.com', '123456'],
            tenantCredentialShow: ['tenant@gmail.com', '123456'],
            maintainerCredentialShow: ['maintainer@gmail.com', '123456']
        };
        Object.keys(demos).forEach(function (id) {
            var el = document.getElementById(id);
            if (el) el.addEventListener('click', function () {
                var em = document.querySelector('.cs-fld__input.email');
                var pw = document.querySelector('.cs-fld__input.password');
                if (em) em.value = demos[id][0];
                if (pw) pw.value = demos[id][1];
            });
        });
    })();
</script>
