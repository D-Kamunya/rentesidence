@extends('saas.frontend.layouts.app')

@section('content')
    @if (getOption('home_hero_section_status', 1) == 1)
        <header id="home" class="hero-area position-relative">
            <div class="hero-area-top-part position-relative">
                <div class="container">
                    <div class="row align-items-center justify-content-center">
                        <div class="col-md-12 col-lg-12 col-xl-8">
                            <div class="hero-content text-center position-relative">
                                <h1 class="hero-main-title">
                                    {!! nl2br(getOption('home_hero_title', 'Property Management Software Made Easy')) !!}</h1>
                                <p class="hero-sub-title">
                                    {!! getOption('home_hero_summery') !!}
                                </p>
                                <!-- <a href="{{ route('owner.register.form') }}" class="theme-btn position-relative"
                                    title="{{ __('Request a Demo') }}">{{ __('Request a Demo') }}</a> -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="hero-area-bottom-part position-relative">
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-md-12 col-lg-12 col-xl-12">
                            <div class="hero-area-img position-relative">
                                <img src="{{ asset('frontend/assets/img/hero-round-strock.png') }}"
                                    alt="{!! nl2br(getOption('app_name')) !!}" class="img-fluid hero-strock-img position-absolute">
                                <div class="hero-backdrop-strock-wrap position-absolute">
                                </div>
                                <div class="hero-main-big-img-wrap position-relative text-center">
                                    <img src="{{ getSettingImage('home_hero_image') }}" alt="{!! nl2br(getOption('app_name')) !!}"
                                        class="img-fluid">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>
    @endif

    @if (getOption('home_feature_section_status'))
        <section id="feature" class="generate-content-area section-t-space">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <div class="section-title">
                            <h6 class="section-tag-heading green-color">{!! nl2br(getOption('home_features_name')) !!}</h6>
                            <h2 class="section-heading">{!! nl2br(getOption('home_features_title')) !!}</h2>
                            <p class="section-sub-heading">{!! nl2br(getOption('home_features_summery')) !!}</p>
                        </div>
                    </div>
                </div>
                <div class="generate-content-row-wrap">
                    <div class="row">
                        <div class="col-md-12 col-lg-6 col-xl-6 col-xxl-6">
                            <div class="faq-content-box faq-left-part generate-content-faq">
                                <div class="accordion" id="accordionExample3">
                                    @foreach ($features as $key => $feature)
                                        <div class="accordion-item">
                                            <h4 class="accordion-header" id="headingEleven{{ $key }}">
                                                <button
                                                    class="accordion-button font-semibold {{ $key != 0 ? 'collapsed' : '' }}"
                                                    type="button" data-bs-toggle="collapse"
                                                    data-bs-target="#collapseEleven{{ $key }}" aria-expanded="true"
                                                    aria-controls="collapseOne">
                                                    {{ $feature->title }}
                                                </button>
                                            </h4>
                                            <div id="collapseEleven{{ $key }}"
                                                class="accordion-collapse collapse {{ $key == 0 ? 'show' : '' }}"
                                                aria-labelledby="headingEleven{{ $key }}"
                                                data-bs-parent="#accordionExample3">
                                                <div class="accordion-body">
                                                    {!! nl2br($feature->summary) !!}
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12 col-lg-6 col-xl-6 col-xxl-6">
                            <div
                                class="generate-content-right feature-content-right bg-secondary text-center position-relative">
                                <img src="{{ getSettingImage('home_features_image') }}" alt="{!! nl2br(getOption('app_name')) !!}"
                                    class="img-fluid">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif

    @if (getOption('home_about_us_section_status', 1) == ACTIVE)
        <section id="about" class="about-us-area section-t-space">
            <div class="container position-relative">
                <img src="{{ asset('frontend/assets/img/about-us-bg-strock.png') }}" alt="{!! nl2br(getOption('app_name')) !!}"
                    class="img-fluid about-us-bg-strock-img position-absolute">

                <div class="row">
                    <div class="col-md-12">
                        <div class="section-title">
                            <h6 class="section-tag-heading green-color">{!! nl2br(getOption('home_about_us_name')) !!}</h6>
                            <h2 class="section-heading">{!! nl2br(getOption('home_about_us_title')) !!}</h2>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12 col-md-12">
                        <div class="about-top-img-wrap radius-20 position-relative mb-25">
                            <img src="{{ getSettingImage('home_about_us_image') }}" alt="{!! nl2br(getOption('app_name')) !!}"
                                class="img-fluid w-100">
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif

    @if (getOption('home_how_it_word_section_status', 1) == ACTIVE)
        <!-- How It Works Area Start -->
        <section id="how-it-works" class="how-it-works-area section-t-small-space section-b-space position-relative">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <div class="section-title">
                            <h6 class="section-tag-heading green-color">{!! nl2br(getOption('home_how_it_word_section_name')) !!}
                            </h6>
                            <h2 class="section-heading">{!! nl2br(getOption('home_how_it_word_section_title')) !!}</h2>
                            <p class="section-sub-heading">{!! nl2br(getOption('home_how_it_word_section_summery')) !!}</p>
                        </div>
                    </div>
                </div>
                <div class="how-it-works-wrap position-relative">
                    @foreach ($howItWorks as $howItWork)
                        <div class="how-it-works-item">
                            <div class="row">
                                <div class="col-12">
                                    <div
                                        class="how-it-works-area row align-items-center justify-content-center position-relative">
                                        <div class="col-md-6">
                                            <div class="how-it-works-item-left position-relative">
                                                <img src="{{ asset('frontend/assets/img/how-it-works-strock-bg1.png') }}"
                                                    alt="{!! nl2br(getOption('app_name')) !!}"
                                                    class="img-fluid position-absolute how-it-works-strock-bg">
                                                <img src="{{ $howItWork->image }}" alt="{!! nl2br(getOption('app_name')) !!}"
                                                    class="img-fluid position-relative how-it-works-main-img">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="how-it-works-item-right">
                                                <h3 class="how-it-works-item-title font-semi-bold">{{ $howItWork->title }}
                                                </h3>
                                                <p class="mt-25">{{ $howItWork->summary }}</p>
                                                @if ($howItWork->content)
                                                    <ul class="how-it-works-features mt-25">
                                                        @foreach (explode(',', $howItWork->content) as $content)
                                                            <li class="d-flex mb-3">
                                                                <span
                                                                    class="price-check-icon flex-shrink-0 d-inline-flex align-items-center justify-content-center status-btn-blue radius-50 me-2">
                                                                    <span class="iconify font-16"
                                                                        data-icon="material-symbols:check-small-rounded"></span>
                                                                </span>
                                                                <span class="flex-grow-1">{{ $content }}</span>
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="timeline-number position-absolute">
                                            {{ $loop->iteration >= 10 ? $loop->iteration : '0' . $loop->iteration }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

            </div>
        </section>
        <!-- How It Works Area End -->
    @endif

    @if (getOption('home_core_pages_section_status', 1) == ACTIVE)
        <!-- Core Pages Area Start -->
        <section id="core-pages" class="core-pages-area bg-secondary section-t-space section-b-space">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <div class="section-title">
                            <h6 class="section-tag-heading green-color">{!! nl2br(getOption('home_core_pages_section_name')) !!}
                            </h6>
                            <h2 class="section-heading">{!! nl2br(getOption('home_core_pages_section_title')) !!}</h2>
                            <p class="section-sub-heading">
                                {!! nl2br(getOption('home_core_pages_section_summery')) !!}
                            </p>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="core-pages-tab">
                            <div class="core-pages-nav-tabs-wrap">
                                <ul class="nav nav-tabs core-pages-nav-tabs justify-content-center mx-auto" id="myTab"
                                    role="tablist">
                                    @foreach ($corePages as $key => $corePage)
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link {{ $key == 0 ? 'active' : '' }}"
                                                id="dashboard-tab{{ $key }}" data-bs-toggle="tab"
                                                data-bs-target="#dashboard-tab-pane{{ $key }}" type="button"
                                                role="tab" aria-controls="dashboard-tab-pane{{ $key }}"
                                                aria-selected="true">{{ $corePage->name }}</button>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                            <div class="tab-content core-pages-tab-content" id="myTabContent">
                                @foreach ($corePages as $key => $corePage)
                                    <div class="tab-pane fade {{ $key == 0 ? 'show active' : '' }}"
                                        id="dashboard-tab-pane{{ $key }}" role="tabpanel"
                                        aria-labelledby="dashboard-tab{{ $key }}" tabindex="0">
                                        <div class="core-pages-tab-content">
                                            <div class="row align-items-center">
                                                <div class="col-md-6 col-lg-7">
                                                    <div class="core-pages-content-left position-relative">
                                                        <img src="{{ $corePage->image }}" alt="{!! nl2br(getOption('app_name')) !!}"
                                                            class="img-fluid core-pages-main-img position-relative">
                                                    </div>
                                                </div>
                                                <div class="col-md-6 col-lg-5">
                                                    <div class="core-pages-content-right">
                                                        <h3 class="mb-20 font-semi-bold">{{ $corePage->title }}</h3>
                                                        <p>{{ $corePage->summary }}</p>
                                                        <ul class="pricing-features">
                                                            @if ($corePage->content)
                                                                @foreach (explode(',', $corePage->content) as $content)
                                                                    <li class="d-flex align-items-center mb-3">
                                                                        <span
                                                                            class="price-check-icon flex-shrink-0 d-inline-flex align-items-center justify-content-center status-btn-blue radius-50 me-2">
                                                                            <span class="iconify font-16"
                                                                                data-icon="material-symbols:check-small-rounded"></span>
                                                                        </span>
                                                                        <span
                                                                            class="flex-grow-1">{{ $content }}</span>
                                                                    </li>
                                                                @endforeach
                                                            @endif
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- Core Pages Area End -->
    @endif
    @if (getOption('home_pricing_section_status', 1) == ACTIVE)
        <!-- Pricing Area Start -->
        <section id="pricing" class="pricing-area section-t-space bg-white">
            <div class="pricing-area-top-part text-center position-relative">
                <div class="container position-relative">
                    <div class="row">
                        <div class="col-md-12 position-relative">
                            <div class="pricing-top-part-content-wrap">
                                <div class="pricing-top-part-content position-relative">
                                    <div class="section-title">
                                        <h6 class="section-tag-heading green-color">
                                            {!! nl2br(getOption('home_price_section_name')) !!}</h6>
                                        <h2 class="section-heading text-white">{!! nl2br(getOption('home_price_section_title')) !!}
                                        </h2>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="pricing-area-bottom-part position-relative">
                <div class="container">
                    <div class="row">
                        <div class="col-md-12">
                            <!-- Choose a plan content Start -->
                            <div class="choose-plan-area">
                                <div class="pricing-plan-area">
                                    <div class="row price-table-wrap">
                                        @foreach ($packages as $package)
                                            <div class="col-md-6 col-lg-4 mb-25 singlePackage">
                                                <form action="{{ route('owner.subscription.index') }}" method="GET">
                                                    <input type="hidden" name="id" value="{{ $package->id }}">
                                                    <input type="hidden" class="plan_type" name="duration_type"
                                                        value="1">
                                                    <input type="hidden" name="per_monthly_price"
                                                        value="{{ $package->per_monthly_price }}">
                                                    <input type="hidden" name="per_yearly_price"
                                                        value="{{ $package->per_yearly_price }}">
                                                    <div
                                                        class="price-card-item h-100 p-30 {{ $package->is_default == 1 ? 'active' : '' }}">
                                                        <h4 class="font-medium price-table-title">{{ $package->name }}
                                                        </h4>
                                                        <hr>
                                                        <h2 class="price-title mb-4">
                                                            {{ currencyPrice($package->monthly_price) }}<span
                                                                class="font-18 font-semi-bold">/{{ __('monthly') }}</span>
                                                        </h2>
                                                        @if (in_array($package->type, [PACKAGE_TYPE_PROPERTY, PACKAGE_TYPE_UNIT, PACKAGE_TYPE_TENANT]))
                                                            <p class="font-13 font-medium price-monthly per_monthly_price">
                                                                {{ currencyPrice($package->per_monthly_price) }}*1={{ $package->per_monthly_price * 1 }}
                                                            </p>
                                                            <p
                                                                class="font-13 font-medium d-none price-yearly per_yearly_price">
                                                                {{ currencyPrice($package->per_yearly_price) }}*1={{ $package->per_yearly_price * 1 }}
                                                            </p>
                                                        @endif
                                                        <ul class="pricing-features set-auto-height">
                                                            @if ($package->type == PACKAGE_TYPE_PROPERTY)
                                                                <li class="d-flex align-items-center mb-3">
                                                                    <span
                                                                        class="price-check-icon flex-shrink-0 d-inline-flex align-items-center justify-content-center status-btn-blue radius-50 me-2">
                                                                        <span class="iconify font-16"
                                                                            data-icon="material-symbols:check-small-rounded"></span>
                                                                    </span>
                                                                    <span
                                                                        class="flex-grow-1">{{ __('Add ' . currencyPrice($package->per_monthly_price) . ' Per Properties') }}</span>
                                                                </li>
                                                            @elseif ($package->type == PACKAGE_TYPE_UNIT)
                                                                <li class="d-flex align-items-center mb-3">
                                                                    <span
                                                                        class="price-check-icon flex-shrink-0 d-inline-flex align-items-center justify-content-center status-btn-blue radius-50 me-2">
                                                                        <span class="iconify font-16"
                                                                            data-icon="material-symbols:check-small-rounded"></span>
                                                                    </span>
                                                                    <span
                                                                        class="flex-grow-1">{{ __('Add ' . currencyPrice($package->per_monthly_price) . ' Per Units') }}</span>
                                                                </li>
                                                            @elseif ($package->type == PACKAGE_TYPE_TENANT)
                                                                <li class="d-flex align-items-center mb-3">
                                                                    <span
                                                                        class="price-check-icon flex-shrink-0 d-inline-flex align-items-center justify-content-center status-btn-blue radius-50 me-2">
                                                                        <span class="iconify font-16"
                                                                            data-icon="material-symbols:check-small-rounded"></span>
                                                                    </span>
                                                                    <span
                                                                        class="flex-grow-1">{{ __('Add ' . currencyPrice($package->per_monthly_price) . ' Per Tenants') }}</span>
                                                                </li>
                                                            @endif

                                                            <li class="d-flex align-items-center mb-3">
                                                                <span
                                                                    class="price-check-icon flex-shrink-0 d-inline-flex align-items-center justify-content-center status-btn-blue radius-50 me-2">
                                                                    <span class="iconify font-16"
                                                                        data-icon="material-symbols:check-small-rounded"></span>
                                                                </span>
                                                                <span>
                                                                    {{ $package->max_maintainer == -1 ? __('Add Unlimited Maintainers') : __('Add ' . $package->max_maintainer . ' Maintainers') }}</span>
                                                            </li>
                                                            <li class="d-flex align-items-center mb-3">
                                                                <span
                                                                    class="price-check-icon flex-shrink-0 d-inline-flex align-items-center justify-content-center status-btn-blue radius-50 me-2">
                                                                    <span class="iconify font-16"
                                                                        data-icon="material-symbols:check-small-rounded"></span>
                                                                </span>
                                                                <span>
                                                                    {{ $package->max_invoice == -1 ? __('Unlimited Invoice Generate') : __('Add ' . $package->max_invoice . ' Invoices') }}</span>
                                                            </li>
                                                            <li class="d-flex align-items-center mb-3">
                                                                <span
                                                                    class="price-check-icon flex-shrink-0 d-inline-flex align-items-center justify-content-center status-btn-blue radius-50 me-2">
                                                                    <span class="iconify font-16"
                                                                        data-icon="material-symbols:check-small-rounded"></span>
                                                                </span>
                                                                <span>{{ $package->max_auto_invoice == -1 ? __('Unlimited Auto Invoices Generate') : __($package->max_auto_invoice . ' Auto Invoices Generate') }}</span>
                                                            </li>
                                                            @if ($package->ticket_support == 1)
                                                                <li class="d-flex align-items-center mb-3">
                                                                    <span
                                                                        class="price-check-icon flex-shrink-0 d-inline-flex align-items-center justify-content-center status-btn-blue radius-50 me-2">
                                                                        <span class="iconify font-16"
                                                                            data-icon="material-symbols:check-small-rounded"></span>
                                                                    </span>
                                                                    <span
                                                                        class="flex-grow-1">{{ __('Ticket Support') }}</span>
                                                                </li>
                                                            @endif
                                                            @if ($package->notice_support == 1)
                                                                <li class="d-flex align-items-center mb-3">
                                                                    <span
                                                                        class="price-check-icon flex-shrink-0 d-inline-flex align-items-center justify-content-center status-btn-blue radius-50 me-2">
                                                                        <span class="iconify font-16"
                                                                            data-icon="material-symbols:check-small-rounded"></span>
                                                                    </span>
                                                                    <span
                                                                        class="flex-grow-1">{{ __('Notice Support') }}</span>
                                                                </li>
                                                            @endif
                                                            @if (in_array($package->type, [PACKAGE_TYPE_PROPERTY, PACKAGE_TYPE_UNIT, PACKAGE_TYPE_TENANT]))
                                                                <li class="d-flex align-items-center">
                                                                    <div class="input-group">
                                                                        <input type="number" min="0"
                                                                            step="any"
                                                                            class="form-control rounded-0 rounded-start quantity"
                                                                            name="quantity" value="1"
                                                                            placeholder="{{ __('Quantity') }}">
                                                                        <span
                                                                            class="input-group-text">{{ __('Quantity') }}</span>
                                                                    </div>
                                                                </li>
                                                            @endif
                                                        </ul>
                                                        <button type="submit" href="?id={{ $package->id }}"
                                                            class="theme-btn-outline mt-1"
                                                            title="{{ __('Subscribe Now') }}">
                                                            {{ __('Subscribe Now') }}
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            <!-- Choose a plan content End -->
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- Pricing Area End -->
    @endif
    @if (getOption('home_integration_section_status', 1) == ACTIVE)
        <!-- Integrations Area Start -->
        <section id="integrations" class="integrations-area section-t-small-space">
            <div class="container position-relative">
                <div class="row">
                    <div class="col-md-12">
                        <div class="section-title">
                            <h6 class="section-tag-heading green-color">{!! nl2br(getOption('home_integration_section_name')) !!}
                            </h6>
                            <h2 class="section-heading">{!! nl2br(getOption('home_integration_section_title')) !!}</h2>
                            <p class="section-sub-heading">{!! nl2br(getOption('home_integration_section_summary')) !!}</p>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="integrations-logo-list">
                            <div class="integrations-logo-wrap position-relative">
                                <div class="logo-item">
                                    <img src="{{ getSettingImage('home_integration_section_image') }}"
                                        alt="{{ getOption('app_name') }}" class="img-fluid">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- Integrations Area End -->
    @endif
    @if (getOption('home_testimonial_section_status', 1) == ACTIVE)
        <!-- Customer Testimonial Area Start -->
        <section id="testimonial" class="customer-testimonial-area section-t-small-space">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <div class="section-title">
                            <h6 class="section-tag-heading green-color">{!! nl2br(getOption('home_testimonial_section_name')) !!}
                            </h6>
                            <h2 class="section-heading">{!! nl2br(getOption('home_testimonial_section_title')) !!}</h2>
                        </div>
                    </div>
                </div>
                <div class="row justify-content-center">
                    <div class="col-md-12">
                        <div class="owl-big owl-carousel owl-theme customer-testimonial-slider">
                            <!-- Testimonial Item Start -->
                            @foreach ($testimonials as $testimonial)
                                <div class="customer-testimonial-item position-relative bg-secondary p-25">
                                    <div
                                        class="testimonial-top-part d-flex justify-content-between align-content-center mb-25">
                                        <div class="customer-testimonial-img d-inline-flex align-items-center">
                                            <img src="{{ asset($testimonial->image) }}" alt="{!! nl2br(getOption('app_name')) !!}"
                                                class="fit-image rounded-circle">
                                            <div>
                                                <h4 class="testimonial-client-name font-semi-bold">
                                                    {{ $testimonial->name }}</h4>
                                                <p class="font-normal theme-text-color font-15">
                                                    {{ $testimonial->designation }}</p>
                                            </div>
                                        </div>
                                        <div class="testimonial-quote">
                                            <span class="iconify" data-icon="clarity:block-quote-line"></span>
                                        </div>
                                    </div>
                                    <div class="testimonial-text-wrap">
                                        <p class="customer-testimonial-text theme-text-color">
                                            {{ $testimonial->comment }}
                                        </p>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="customer-testimonial-rating">
                                            {!! reviewStar($testimonial->star) !!}
                                        </div>
                                        <div class="testimonial-bottom-line"></div>
                                    </div>
                                </div>
                            @endforeach
                            <!-- Testimonial Item End -->
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- Customer Testimonial Area End -->
    @endif
    @if (getOption('home_faq_section_status', 1) == ACTIVE)
        <!-- FAQ Area Start -->
        <section id="faqs" class="faqs-area section-t-small-space section-b-small-space position-relative">
            <div class="container position-relative">
                <div class="row">
                    <div class="col-md-12">
                        <div class="section-title">
                            <h6 class="section-tag-heading green-color">{!! nl2br(getOption('home_faq_section_name')) !!}</h6>
                            <h2 class="section-heading">{!! nl2br(getOption('home_faq_section_title')) !!}</h2>
                        </div>
                    </div>
                </div>
                <div class="row faq-accordion-wrap position-relative">
                    <img src="{{ asset('frontend/assets/img/faq-strock-bg.png') }}" alt="{!! nl2br(getOption('app_name')) !!}"
                        class="img-fluid faq-floating-img position-absolute">
                    <div class="col-md-12 col-lg-6 position-relative">
                        <div class="faq-content-box faq-left-part">
                            <!-- Accordion Start -->
                            <div class="accordion" id="accordionExample1">
                                @php
                                    $i = 1;
                                    $take = count($faqs);
                                @endphp
                                @foreach ($faqs->take($take / 2) as $faq)
                                    <div class="accordion-item">
                                        <h5 class="accordion-header" id="headingOne{{ $i }}">
                                            <button class="accordion-button font-medium {{ $i == 1 ? '' : 'collapsed' }}"
                                                type="button" data-bs-toggle="collapse"
                                                data-bs-target="#collapseOne{{ $i }}"
                                                aria-expanded="{{ $i == 1 ? true : false }}"
                                                aria-controls="collapseOne{{ $i }}">
                                                {{ $i }}. {{ $faq->question }}
                                            </button>
                                        </h5>
                                        <div id="collapseOne{{ $i }}"
                                            class="accordion-collapse collapse {{ $i == 1 ? 'show' : '' }}"
                                            aria-labelledby="headingOne{{ $i }}"
                                            data-bs-parent="#accordionExample1">
                                            <div class="accordion-body">
                                                {{ $faq->answer }}
                                            </div>
                                        </div>
                                    </div>
                                    @php $i++; @endphp
                                @endforeach
                            </div>
                            <!-- Accordion End -->
                        </div>
                    </div>
                    <div class="col-md-12 col-lg-6 position-relative">
                        <div class="faq-content-box faq-right-part">
                            <!-- Accordion Start -->
                            <div class="accordion" id="accordionExample2">
                                @foreach ($faqs->skip($take / 2)->take($take / 2) as $faq)
                                    <div class="accordion-item">
                                        <h5 class="accordion-header" id="headingSix">
                                            <button class="accordion-button font-medium collapsed" type="button"
                                                data-bs-toggle="collapse"
                                                data-bs-target="#collapseTwo{{ $i }}" aria-expanded="false"
                                                aria-controls="collapseTwo{{ $i }}">
                                                {{ $i }}. {{ $faq->question }}
                                            </button>
                                        </h5>
                                        <div id="collapseTwo{{ $i }}" class="accordion-collapse collapse"
                                            aria-labelledby="headingSix" data-bs-parent="#accordionExample2">
                                            <div class="accordion-body">
                                                {{ $faq->answer }}
                                            </div>
                                        </div>
                                    </div>
                                    @php $i++; @endphp
                                @endforeach
                            </div>
                            <!-- Accordion End -->
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- FAQ Area End -->
    @endif
    <section id="support" class="py-5">
        <div class="container mt-5 p-5">
            <div class="support-area p-5">
                <div class="text-center">
                    <h2 class=" fw-bold mb-3 text-white">{{ __('Contact Us') }}</h2>
                    <p class="text-white">
                        {{ __('Lets get the conversation started. Provide feedback, request support, and pricing quotes for bulk purchases.') }}
                    </p>
                </div>
                <div>
                    <form class="ajax mt-5" action="{{ route('contact.message.store') }}" method="POST"
                        data-handler="getShowMessage">
                        @csrf
                        <div class="row my-3">
                            <div class="col-md-6">
                                <label class="text-white">{{ __('First Name') }}</label>
                                <input type="text" name="first_name" class="form-control"
                                    placeholder="{{ __('First Name') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="text-white">{{ __('Last Name') }}</label>
                                <input type="text" name="last_name" class="form-control"
                                    placeholder="{{ __('Last Name') }}">
                            </div>
                        </div>
                        <div class="row my-3">
                            <div class="col-md-6">
                                <label class="text-white">{{ __('Email') }}</label>
                                <input type="email" name="email" class="form-control"
                                    placeholder="{{ __('Email') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="text-white">{{ __('Phone Number') }}</label>
                                <input type="text" name="phone" class="form-control"
                                    placeholder="{{ __('Phone Number') }}">
                            </div>
                        </div>
                        <div class="row my-3">
                            <div class="col-md-12 mb-3">
                                <label class="text-white">{{ __('Subject') }}</label>
                                <input type="text" name="subject" class="form-control"
                                    placeholder="{{ __('Subject') }}">
                            </div>
                            <div class="col-md-12">
                                <label class="text-white">{{ __('Message') }}</label>
                                <textarea name="message" id="" cols="30" rows="5" class="form-control"
                                    placeholder="{{ __('Message') }}"></textarea>
                            </div>
                        </div>
                        <input type="submit" class="btn btn-primary w-100 py-3 mt-3" value="send">
                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection
@push('script')
    <script src="{{ asset('assets/js/custom/frontend-index.js') }}"></script>
@endpush
