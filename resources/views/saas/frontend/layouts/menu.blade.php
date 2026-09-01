<link href="https://fonts.googleapis.com/css2?family=Josefin+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<style>
    /* ── Centresidence public header ──────────────────────────────────────────
       Dark-premium brand chrome. Uses the two-tone WORDMARK (light variant) — the
       glossy dimensional lockup is reserved for big brand moments (login splash),
       not tiny nav chrome. On the home route the bar is TRANSPARENT so it blends
       into the dark hero; on other frontend pages it is solid dark. */
    .menu-section-area{background:transparent}
    #mainNav.navbar{border-bottom:1px solid transparent;transition:background .25s,border-color .25s}
    #mainNav.csnav--solid{background:rgba(14,18,24,.92)!important;backdrop-filter:blur(12px);
        -webkit-backdrop-filter:blur(12px);border-bottom-color:rgba(255,255,255,.08)}
    #mainNav.csnav--transparent{background:transparent!important;box-shadow:none!important}
    /* home: transparent over the hero, subtle dark blur once scrolled (no hard bar) */
    #mainNav.csnav--transparent.sticky{background:rgba(11,15,21,.88)!important;
        backdrop-filter:blur(12px);-webkit-backdrop-filter:blur(12px);
        box-shadow:0 10px 30px -20px rgba(0,0,0,.85)!important;border-bottom-color:rgba(255,255,255,.07)}

    /* Brand lockup: shiny square CS icon + two-tone wordmark */
    .cs-lockup{display:inline-flex;align-items:center;gap:11px}
    .cs-logo-icon{height:40px;width:auto;display:block;filter:drop-shadow(0 4px 10px rgba(24,95,165,.4))}
    /* Wordmark */
    .cs-wordmark{display:inline-flex;align-items:baseline;font-weight:800;letter-spacing:-.02em;
        font-size:23px;line-height:1;font-family:var(--sans,-apple-system,'Segoe UI',Roboto,Arial,sans-serif)}
    /* subtle shadow so the wordmark stays legible if it ever overlaps a bright image */
    .cs-wordmark{text-shadow:0 1px 5px rgba(0,0,0,.45)}
    .cs-wordmark .a{color:#F2EFEA}
    .cs-wordmark .b{color:#5AA0E0}

    /* Nav links + actions on dark. The nav is transparent over the hero, so links can
       fall over a BRIGHT part of the image → use white + a text-shadow (legible on dark
       AND bright); amber text would blend on warm/dusk imagery. Amber is used where it
       always wins: as a solid fill on the Sign In button (the hero CTA colour). */
    #mainNav .nav-link{color:#FFFFFF!important;font-weight:550;text-shadow:0 1px 6px rgba(0,0,0,.6)}
    #mainNav .nav-link:hover,#mainNav .nav-link.active{color:#F0AF49!important}
    #mainNav .theme-btn-outline{color:#20160A!important;border:1px solid #E7A339!important;
        background:#E7A339!important;border-radius:10px;padding:9px 22px;transition:.18s;font-weight:650;
        white-space:nowrap;display:inline-block;margin-right:2px;box-shadow:0 8px 20px -8px rgba(231,163,57,.6)}
    #mainNav .theme-btn-outline:hover{border-color:#f0af49!important;background:#f0af49!important;color:#20160A!important;transform:translateY(-1px)}
    #mainNav .navbar-nav-right{justify-content:flex-end;overflow:visible}
    #mainNav .container{overflow:visible}
    #mainNav .nav-dash-link{color:#FFFFFF!important;font-weight:600;text-shadow:0 1px 6px rgba(0,0,0,.6)}
    #mainNav .navbar-toggler{color:#EDE7DC;border-color:rgba(255,255,255,.25)}

    /* Offcanvas (mobile drawer) dark */
    /* Dark ONLY as a real mobile drawer (< lg). On desktop (navbar-expand-lg) the
       offcanvas renders INLINE as the nav content, so a background here painted a dark
       box over the transparent nav — that was the "black bar". Scope it to mobile. */
    @media (max-width:991.98px){
        #offcanvasNavbarDark{background:#0E1218!important;color:#EDE7DC}
        /* CRITICAL BUGFIX: the nav's glassy `backdrop-filter:blur()` establishes a CONTAINING
           BLOCK for fixed-position descendants, which traps THIS offcanvas — its top:0/bottom:0
           resolve against the ~59px nav bar instead of the viewport, collapsing the drawer and
           clipping the menu to a sliver (the "3 dots" bug on csnav--solid pages like House Hunt
           / Blog, and on Home once scrolled). On mobile the offcanvas is a real fixed drawer, so
           drop the blur here; the nav keeps its solid/tinted background. Desktop keeps the glass
           (there the offcanvas renders INLINE, not as a fixed drawer, so no trap). */
        #mainNav.csnav--solid,
        #mainNav.csnav--transparent,
        #mainNav.csnav--transparent.sticky{
            backdrop-filter:none!important; -webkit-backdrop-filter:none!important;
        }
    }
    @media (min-width:992px){
        #offcanvasNavbarDark,
        #offcanvasNavbarDark .offcanvas-body,
        #offcanvasNavbarDark .offcanvas-header{background:transparent!important}
    }
    #offcanvasNavbarDark .nav-link{color:#CFC8BC!important}
    #offcanvasNavbarDark .btn-close{filter:invert(1) grayscale(1) brightness(2)}
</style>
@if (env('LOGIN_HELP') == 'active')
    <div class="alert alert-danger text-center mb-0" role="alert">
        This page only for addon
        <button type="button" id="topBannerClose" class="close float-end" data-dismiss="alert" aria-label="Close"><span
                aria-hidden="true">&times;</span></button>
    </div>
@endif
<section class="menu-section-area" style="font-family: 'Josefin Sans', sans-serif;">
    <!-- Navigation -->
    <nav class="navbar sticky-header navbar-expand-lg {{ (request()->is('/') || request()->routeIs('frontend')) ? 'csnav--transparent' : 'csnav--solid' }}"
         aria-label="Centresidence navbar" id="mainNav">
        <div class="container">
            <a class="navbar-brand mobile-navbar-brand" href="{{ route('frontend') }}">
                <span class="cs-lockup"><img src="{{ asset('assets/images/cs-icon.png') }}" alt="Centresidence" class="cs-logo-icon"><span class="cs-wordmark"><span class="a">centre</span><span class="b">sidence</span></span></span>
            </a>
            <div class="navbar-right-mobile d-flex align-items-center">
                <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas"
                    data-bs-target="#offcanvasNavbarDark" aria-controls="offcanvasNavbarDark">
                    <span class="iconify" data-icon="heroicons-outline:menu"></span>
                </button>
            </div>
            <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasNavbarDark"
                aria-labelledby="offcanvasNavbarDarkLabel">
                <div class="offcanvas-header">
                    <h5 class="offcanvas-title" id="offcanvasNavbarDarkLabel">
                        <span class="cs-lockup"><img src="{{ asset('assets/images/cs-icon.png') }}" alt="Centresidence" class="cs-logo-icon"><span class="cs-wordmark"><span class="a">centre</span><span class="b">sidence</span></span></span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                </div>
                <div class="offcanvas-body">
                    <div class="col-lg-2 col-xl-2 navbar-nav-brand-box">
                        <a class="navbar-brand desktop-navbar-brand" href="{{ route('frontend') }}">
                            <span class="cs-lockup"><img src="{{ asset('assets/images/cs-icon.png') }}" alt="Centresidence" class="cs-logo-icon"><span class="cs-wordmark"><span class="a">centre</span><span class="b">sidence</span></span></span>
                        </a>
                    </div>
                    <ul class="navbar-nav mb-2 mb-lg-0 col-lg-6 col-xl-6 navbar-nav-middle">
                        @if (getOption('home_feature_section_status'))
                            <li class="nav-item">
                                    <a class="nav-link" href="{{ route('frontend') }}">Home</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->is('/') ? 'active' : '' }}" aria-current="page"
                                    href="{{ route('frontend') }}#features"><span>{{ __('Features') }}</span></a>
                            </li>
                            <li class="nav-item">
                                    <a class="nav-link" href="{{ route('house.hunt') }}">House Hunt</a>
                            </li>
                        @endif
                        <li class="nav-item">
                            <a class="nav-link"
                                href="{{ route('frontend') }}#contact-us"><span>{{ __('Contact Us') }}</span></a>
                        </li>
                        @if (getOption('home_how_it_word_section_status', 1) == ACTIVE)
                            <li class="nav-item">
                                <a class="nav-link"
                                href="{{ route('frontend') }}#howitworks"><span>{{ __('How It Works') }}</span></a>
                            </li>
                        @endif
                        <li class="nav-item">
                            <a class="nav-link"
                                href="{{ route('blog.index') }}"><span>{{ __('Blog') }}</span></a>
                        </li>
                    </ul>
                    <div class="navbar-nav mb-2 mb-lg-0 col-lg-4 col-xl-4 navbar-nav-right">
                        <div class="nav-dash login-menu-item">
                            @auth
                                @if (auth()->user()->role == USER_ROLE_ADMIN)
                                    <a href="{{ route('admin.dashboard') }}"
                                        class="nav-dash-link"><span>{{ __('Dashboard') }}</span></a>
                                @elseif (auth()->user()->role == USER_ROLE_OWNER)
                                    <a href="{{ route('owner.dashboard') }}"
                                        class="nav-dash-link"><span>{{ __('Dashboard') }}</span></a>
                                @elseif (auth()->user()->role == USER_ROLE_TENANT)
                                    <a href="{{ route('tenant.dashboard') }}"
                                        class="nav-dash-link"><span>{{ __('Dashboard') }}</span></a>
                                @elseif (auth()->user()->role == USER_ROLE_MAINTAINER)
                                    <a href="{{ route('maintainer.dashboard') }}"
                                        class="nav-dash-link"><span>{{ __('Dashboard') }}</span></a>
                                @elseif (auth()->user()->role == USER_ROLE_AFFILIATE)
                                    <a href="{{ route('affiliate.dashboard') }}"
                                        class="nav-dash-link"><span>{{ __('Dashboard') }}</span></a>
                                @endif
                            @else
                                <a href="{{ route('login') }}" class="theme-btn-outline"><span>{{ __('Sign In') }}</span></a>
                            @endauth
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>
    <!-- Navigation -->
</section>

<script>
    /* Mobile menu (Bootstrap offcanvas) hardening — fixes two reported phone bugs:
       1) Same-page anchor links (#features/#contact-us/#howitworks) left the drawer OPEN,
          blocking the content as it scrolled. → close the drawer after tapping ANY link.
       2) After navigating away and returning via the back/forward cache, Bootstrap could
          restore the offcanvas half-open with a stale backdrop, so the hamburger no longer
          revealed a usable menu. → on bfcache restore, force a clean state so it re-opens. */
    (function () {
        var oc = document.getElementById('offcanvasNavbarDark');
        if (!oc) return;

        function hideDrawer() {
            try {
                if (window.bootstrap && bootstrap.Offcanvas) {
                    bootstrap.Offcanvas.getOrCreateInstance(oc).hide();
                }
            } catch (e) {}
        }

        // (1) Auto-close on any link tap. For same-page #anchors we let the drawer's hide
        //     animation and the scroll run together — the drawer clears the view immediately.
        oc.querySelectorAll('a.nav-link, a.nav-dash-link, a.theme-btn-outline').forEach(function (a) {
            a.addEventListener('click', function () { hideDrawer(); });
        });

        // (2) Clean up any stale offcanvas state when restored from the back/forward cache.
        window.addEventListener('pageshow', function (e) {
            if (!e.persisted) return;
            hideDrawer();
            document.querySelectorAll('.offcanvas-backdrop').forEach(function (b) { b.remove(); });
            document.body.classList.remove('offcanvas-backdrop', 'modal-open');
            document.body.style.removeProperty('overflow');
            document.body.style.removeProperty('padding-right');
            oc.classList.remove('show');
            oc.style.removeProperty('visibility');
        });
    })();
</script>
