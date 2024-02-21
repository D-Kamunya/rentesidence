@if (env('LOGIN_HELP') == 'active')
    <div class="alert alert-danger text-center mb-0" role="alert">
        This page only for addon
        <button type="button" id="topBannerClose" class="close float-end" data-dismiss="alert" aria-label="Close"><span
                aria-hidden="true">&times;</span></button>
    </div>
@endif
<section class="menu-section-area">
    <!-- Navigation -->
    <nav class="navbar sticky-header navbar-expand-lg" aria-label="Dark offcanvas navbar" id="mainNav">
        <div class="container">
            <a class="navbar-brand mobile-navbar-brand" href="{{ route('frontend') }}">
                <img class="img-fluid" src="{{ getSettingImage('app_logo') }}" alt="{{ getOption('app_name') }}">
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
                        <img class="light-logo img-fluid" src="{{ getSettingImage('app_logo') }}"
                            alt="{{ getOption('app_name') }}">
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                </div>
                <div class="offcanvas-body">
                    <div class="col-lg-2 col-xl-2 navbar-nav-brand-box">
                        <a class="navbar-brand desktop-navbar-brand" href="{{ route('frontend') }}">
                            <img class="light-logo img-fluid" src="{{ getSettingImage('app_logo') }}"
                                alt="{{ getOption('app_name') }}">
                        </a>
                    </div>
                    <ul class="navbar-nav mb-2 mb-lg-0 col-lg-6 col-xl-6 navbar-nav-middle">
                        @if (getOption('home_feature_section_status'))
                            <li class="nav-item">
                                <a class="nav-link {{ request()->is('/') ? 'active' : '' }}" aria-current="page"
                                    href="{{ route('frontend') }}#feature"><span>{{ __('Feature') }}</span></a>
                            </li>
                        @endif
                        @if (isAddonInstalled('PROTYLISTING') > 0)
                            @if (getOption('LISTING_STATUS', 0) == ACTIVE)
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->is('properties') || request()->is('properties/*') ? 'active' : '' }}"
                                        href="{{ route('listings') }}"><span>{{ __('Properties') }}</span></a>
                                </li>
                            @endif
                        @endif
                        @if (getOption('home_faq_section_status', 1) == ACTIVE)
                            <li class="nav-item">
                                <a class="nav-link"
                                    href="{{ route('frontend') }}#faqs"><span>{{ __('Faqs') }}</span></a>
                            </li>
                        @endif
                        @if (getOption('home_pricing_section_status', 1) == ACTIVE)
                            <li class="nav-item">
                                <a class="nav-link"
                                    href="{{ route('frontend') }}#pricing"><span>{{ __('Pricing') }}</span></a>
                            </li>
                        @endif
                        @if (getOption('home_how_it_word_section_status', 1) == ACTIVE)
                            <li class="nav-item">
                                <a class="nav-link"
                                    href="{{ route('frontend') }}#how-it-works"><span>{{ __('How Its Works') }}</span></a>
                            </li>
                        @endif
                        {{-- <li class="nav-item">
                            <a class="nav-link"
                                href="{{ route('frontend') }}#contact"><span>{{ __('Contact') }}</span></a>
                        </li> --}}
                    </ul>
                    <div class="navbar-nav mb-2 mb-lg-0 col-lg-4 col-xl-4 navbar-nav-right">
                        <div class="nav-item login-menu-item">
                            @auth
                                @if (auth()->user()->role == USER_ROLE_ADMIN)
                                    <a href="{{ route('admin.dashboard') }}"
                                        class="nav-link"><span>{{ __('Dashboard') }}</span></a>
                                @elseif (auth()->user()->role == USER_ROLE_OWNER)
                                    <a href="{{ route('owner.dashboard') }}"
                                        class="nav-link"><span>{{ __('Dashboard') }}</span></a>
                                @elseif (auth()->user()->role == USER_ROLE_TENANT)
                                    <a href="{{ route('tenant.dashboard') }}"
                                        class="nav-link"><span>{{ __('Dashboard') }}</span></a>
                                @elseif (auth()->user()->role == USER_ROLE_MAINTAINER)
                                    <a href="{{ route('maintainer.dashboard') }}"
                                        class="nav-link"><span>{{ __('Dashboard') }}</span></a>
                                @endif
                            @else
                                <a href="{{ route('login') }}" class="nav-link"><span>{{ __('Sign In') }}</span></a>
                            @endauth
                        </div>
                        <div class="nav-item">
                            <a href="{{ route('owner.register.form') }}" class="theme-btn-outline"
                                title="{{ __('Request a Demo') }}">{{ __('Request a Demo') }}</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>
    <!-- Navigation -->
</section>
