{{-- Centresidence public footer — CS dark styling (replaces the legacy Bootstrap
     footer). Flows seamlessly from the dark page (no navy mismatch, no glow seam);
     uses the two-tone wordmark to match the header. --}}
<style>
    .csft{background:#0B0F15;color:#8A93A1;border-top:1px solid rgba(255,255,255,.07);
        font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif}
    .csft__wrap{max-width:1160px;margin:0 auto;padding:64px 24px 28px}
    .csft__top{display:grid;grid-template-columns:1.6fr 1fr 1fr 1fr;gap:40px}
    @media(max-width:860px){.csft__top{grid-template-columns:1fr 1fr}}
    @media(max-width:520px){.csft__top{grid-template-columns:1fr 1fr;gap:28px}}
    .csft__brandcol{grid-column:span 1}
    @media(max-width:860px){.csft__brandcol{grid-column:1 / -1}}
    .csft-lockup{display:inline-flex;align-items:center;gap:11px}
    .csft-logo-icon{height:42px;width:auto;display:block;filter:drop-shadow(0 4px 12px rgba(24,95,165,.35))}
    .csft-wordmark{display:inline-flex;align-items:baseline;font-weight:800;letter-spacing:-.02em;
        font-size:26px;line-height:1}
    .csft-wordmark .a{color:#EDEAE3}.csft-wordmark .b{color:#5AA0E0}
    .csft__tagline{margin-top:8px;font-size:11px;letter-spacing:.22em;text-transform:uppercase;color:#5f6a78}
    .csft__about{margin-top:18px;font-size:14px;line-height:1.6;color:#7d8695;max-width:340px}
    .csft__social{margin-top:22px;display:flex;gap:12px}
    .csft__social a{width:38px;height:38px;border-radius:10px;display:grid;place-items:center;
        background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.08);color:#B9C0CB;
        font-size:18px;transition:.18s}
    .csft__social a:hover{background:#185FA5;border-color:#185FA5;color:#fff;transform:translateY(-2px)}
    .csft__col h5{color:#EDEAE3;font-size:13px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;
        margin:0 0 18px}
    .csft__col ul{list-style:none;margin:0;padding:0;display:flex;flex-direction:column;gap:11px}
    .csft__col a{color:#9098A5;font-size:14.5px;text-decoration:none;transition:.15s}
    .csft__col a:hover{color:#fff}
    .csft__divider{height:1px;background:rgba(255,255,255,.07);margin:44px 0 20px}
    .csft__bottom{display:flex;justify-content:space-between;align-items:center;gap:14px;flex-wrap:wrap;
        font-size:13px;color:#6b7484}
    .csft__bottom a{color:#8A93A1;text-decoration:none}
    .csft__bottom a:hover{color:#fff}
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
