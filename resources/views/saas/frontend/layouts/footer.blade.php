{{-- Centresidence public footer — LIGHT (matches the light marketing front). Sits on a
     soft cool-paper ground so it separates from the white content above without going dark;
     two-tone wordmark (light variant), cs-blue as the accent. --}}
<style>
    .csft{background:#F2F4F8;color:#5A626D;border-top:1px solid #E4E7EC;
        font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif}
    .csft__wrap{max-width:1160px;margin:0 auto;padding:64px 24px 28px}
    .csft__top{display:grid;grid-template-columns:1.6fr 1fr 1fr 1fr;gap:40px}
    @media(max-width:860px){.csft__top{grid-template-columns:1fr 1fr}}
    @media(max-width:520px){.csft__top{grid-template-columns:1fr 1fr;gap:28px}}
    .csft__brandcol{grid-column:span 1}
    @media(max-width:860px){.csft__brandcol{grid-column:1 / -1}}
    .csft-lockup{display:inline-flex;align-items:center;gap:11px}
    .csft-logo-icon{height:42px;width:auto;display:block;filter:drop-shadow(0 4px 12px rgba(24,95,165,.22))}
    .csft-wordmark{display:inline-flex;align-items:baseline;font-weight:800;letter-spacing:-.02em;
        font-size:26px;line-height:1}
    .csft-wordmark .a{color:#1F2430}.csft-wordmark .b{color:#185FA5}
    .csft__tagline{margin-top:8px;font-size:11px;letter-spacing:.22em;text-transform:uppercase;color:#8A5A12}
    .csft__about{margin-top:18px;font-size:14px;line-height:1.6;color:#5A626D;max-width:340px}
    .csft__social{margin-top:22px;display:flex;gap:12px}
    .csft__social a{width:38px;height:38px;border-radius:10px;display:grid;place-items:center;
        background:#FFFFFF;border:1px solid #E4E7EC;color:#5A626D;
        font-size:18px;transition:.18s;box-shadow:0 1px 2px rgba(20,23,28,.04)}
    .csft__social a:hover{background:#185FA5;border-color:#185FA5;color:#fff;transform:translateY(-2px)}
    .csft__col h5{color:#1B1E22;font-size:13px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;
        margin:0 0 18px}
    .csft__col ul{list-style:none;margin:0;padding:0;display:flex;flex-direction:column;gap:11px}
    .csft__col a{color:#5A626D;font-size:14.5px;text-decoration:none;transition:.15s}
    .csft__col a:hover{color:#185FA5}
    .csft-install{display:inline-flex!important;align-items:center;gap:7px;margin-top:4px;
        padding:8px 14px;border-radius:99px;background:#E8F0F9;color:#185FA5!important;
        font-weight:650;font-size:13.5px;border:1px solid #CFE0F2;transition:.15s}
    .csft-install:hover{background:#185FA5;color:#fff!important;border-color:#185FA5}
    .csft-install svg{width:15px;height:15px}
    .csft__divider{height:1px;background:#E4E7EC;margin:44px 0 20px}
    .csft__bottom{display:flex;justify-content:space-between;align-items:center;gap:14px;flex-wrap:wrap;
        font-size:13px;color:#7C828C}
    .csft__bottom a{color:#5A626D;text-decoration:none}
    .csft__bottom a:hover{color:#185FA5}
</style>

<footer class="csft">
    <div class="csft__wrap">
        <div class="csft__top">
            {{-- Brand --}}
            <div class="csft__brandcol">
                <a href="{{ route('frontend') }}" style="text-decoration:none">
                    <span class="csft-lockup">
                        <img src="{{ asset('assets/images/cs-icon.png') }}" alt="Centresidence" class="csft-logo-icon">
                        <span class="csft-wordmark"><span class="a">centre</span><span class="b">sidence</span></span>
                    </span>
                </a>
                <div class="csft__tagline">Real Estate. Simplified. Connected.</div>
                <p class="csft__about">
                    {{ __('One platform to run rentals, payments and tenants. Built for how Kenya rents, free to start.') }}
                </p>
                <div class="csft__social">
                    <a href="#" aria-label="Facebook"><span class="iconify" data-icon="mdi:facebook"></span></a>
                    <a href="#" aria-label="Twitter"><span class="iconify" data-icon="mdi:twitter"></span></a>
                    <a href="#" aria-label="LinkedIn"><span class="iconify" data-icon="mdi:linkedin"></span></a>
                    <a href="#" aria-label="Instagram"><span class="iconify" data-icon="mdi:instagram"></span></a>
                </div>
            </div>

            {{-- Product --}}
            <div class="csft__col">
                <h5>{{ __('Product') }}</h5>
                <ul>
                    <li><a href="{{ route('login') }}">{{ __('Dashboard') }}</a></li>
                    <li><a href="{{ route('frontend') }}#features">{{ __('Features') }}</a></li>
                    <li><a href="{{ route('house.hunt') }}">{{ __('House Hunt') }}</a></li>
                    <li><a href="{{ route('frontend') }}#partners">{{ __('Work with us') }}</a></li>
                    <li><a href="#" class="csft-install" data-cs-install onclick="return window.csPwaInstall ? (window.csPwaInstall(), false) : true;"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="2" width="14" height="20" rx="2"></rect><line x1="12" y1="18" x2="12" y2="18"></line></svg>{{ __('Install the app') }}</a></li>
                </ul>
            </div>

            {{-- Pages --}}
            <div class="csft__col">
                <h5>{{ __('Pages') }}</h5>
                <ul>
                    <li><a href="{{ route('terms-conditions') }}">{{ __('Terms & Conditions') }}</a></li>
                    <li><a href="{{ route('privacy-policy') }}">{{ __('Privacy Policy') }}</a></li>
                    <li><a href="{{ route('cookie-policy') }}">{{ __('Cookie Policy') }}</a></li>
                </ul>
            </div>

            {{-- Support --}}
            <div class="csft__col">
                <h5>{{ __('Support') }}</h5>
                <ul>
                    <li><a href="{{ route('frontend') }}#faq">{{ __('FAQs') }}</a></li>
                    <li><a href="{{ route('frontend') }}#howitworks">{{ __('How it Works') }}</a></li>
                    <li><a href="{{ route('frontend') }}#contact-us">{{ __('Contact Us') }}</a></li>
                </ul>
            </div>
        </div>

        <div class="csft__divider"></div>

        <div class="csft__bottom">
            <span>&copy; {{ date('Y') }} Centresidence. {{ __('All rights reserved.') }}</span>
            <span>{{ __('Made for landlords and tenants across Kenya.') }}</span>
        </div>
    </div>
</footer>
