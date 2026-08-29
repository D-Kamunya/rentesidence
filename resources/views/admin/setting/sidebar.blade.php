<div class="col-md-12 col-lg-12 col-xl-4 col-xxl-3">
    <div class="cs-card stg-nav">
        <a href="{{ route('admin.setting.general-setting') }}" class="stg-nav__item {{ @$subGeneralSettingActiveClass ? 'is-active' : '' }}">
            <span class="iconify" data-icon="carbon:settings"></span><span>{{ __('Basic Setting') }}</span>
        </a>
        <a href="{{ route('admin.setting.color-setting') }}" class="stg-nav__item {{ @$subColorSettingActiveClass ? 'is-active' : '' }}">
            <span class="iconify" data-icon="fluent:color-background-24-regular"></span><span>{{ __('Color Setting') }}</span>
        </a>
        <a href="{{ route('admin.language.index') }}" class="stg-nav__item {{ @$subLanguageActiveClass ? 'is-active' : '' }}">
            <span class="iconify" data-icon="clarity:language-line"></span><span>{{ __('Language') }}</span>
        </a>
        <a href="{{ route('admin.setting.currency.index') }}" class="stg-nav__item {{ @$subCurrencyActiveClass ? 'is-active' : '' }}">
            <span class="iconify" data-icon="heroicons:currency-dollar"></span><span>{{ __('Currency') }}</span>
        </a>
        @if (isAddonInstalled('PROTYSAAS') > 1)
            <a href="{{ route('admin.setting.gateway.index') }}" class="stg-nav__item {{ @$subGatewaySettingActiveClass ? 'is-active' : '' }}">
                <span class="iconify" data-icon="fluent:payment-16-regular"></span><span>{{ __('Payment Gateway') }}</span>
            </a>
            <a href="{{ route('admin.setting.marketplaceaccounts.setting') }}" class="stg-nav__item {{ @$subMarketplaceAccountsSettingActiveClass ? 'is-active' : '' }}">
                <span class="iconify" data-icon="mdi:account-cog-outline"></span><span>{{ __('Marketplace, Subs & SMS Accounts') }}</span>
            </a>
            <a href="{{ route('admin.setting.rentaccounts.setting') }}" class="stg-nav__item {{ @$subRentAccountSettingActiveClass ? 'is-active' : '' }}">
                <span class="iconify" data-icon="mdi:account-cog-outline"></span><span>{{ __('Rent & Disbursement Accounts') }}</span>
            </a>
            <a href="{{ route('admin.setting.frontend.setting') }}" class="stg-nav__item {{ @$subFrontendSettingActiveClass ? 'is-active' : '' }}">
                <span class="iconify" data-icon="icon-park-outline:setting-laptop"></span><span>{{ __('Frontend Setting') }}</span>
            </a>
        @endif
        <a href="{{ route('admin.setting.affiliate.setting') }}" class="stg-nav__item {{ @$subAffiliateSettingActiveClass ? 'is-active' : '' }}">
            <span class="iconify" data-icon="fluent:payment-16-regular"></span><span>{{ __('Affiliate Commissions') }}</span>
        </a>
        <a href="{{ route('admin.agreement.settings.index') }}" class="stg-nav__item {{ @$subAgreementSettingActiveClass ? 'is-active' : '' }}">
            <span class="iconify" data-icon="mdi:file-sign"></span><span>{{ __('Agreement Settings') }}</span>
        </a>
        <a href="{{ route('admin.setting.smtp.setting') }}" class="stg-nav__item {{ @$subSmtpSettingActiveClass ? 'is-active' : '' }}">
            <span class="iconify" data-icon="mdi:git-issue"></span><span>{{ __('SMTP Setting') }}</span>
        </a>
        <a href="{{ route('admin.setting.recaptcha.setting') }}" class="stg-nav__item {{ @$subRecaptchaSettingActiveClass ? 'is-active' : '' }}">
            <span class="iconify" data-icon="logos:recaptcha"></span><span>{{ __('reCaptcha Setting') }}</span>
        </a>
        @if (isAddonInstalled('PROTYSMS', 0) > 0)
            <a href="{{ route('admin.setting.sms.setting') }}" class="stg-nav__item {{ @$subSmsSettingActiveClass ? 'is-active' : '' }}">
                <span class="iconify" data-icon="icon-park-outline:setting-web"></span><span>{{ __('Sms/Mail Setting') }}</span>
            </a>
            <a href="{{ route('admin.setting.reminder.setting') }}" class="stg-nav__item {{ @$subReminderSettingActiveClass ? 'is-active' : '' }}">
                <span class="iconify" data-icon="carbon:reminder"></span><span>{{ __('Invoice Reminder Setting') }}</span>
            </a>
            <a href="{{ route('admin.setting.subscription.reminder.setting') }}" class="stg-nav__item {{ @$subSubscriptionReminderSettingActiveClass ? 'is-active' : '' }}">
                <span class="iconify" data-icon="carbon:reminder"></span><span>{{ __('Subscriptions Reminder Setting') }}</span>
            </a>
        @endif
        @if (isAddonInstalled('PROTYTENANCY', 0) > 0)
            <a href="{{ route('admin.setting.tenancy.setting') }}" class="stg-nav__item {{ @$subTenancySettingActiveClass ? 'is-active' : '' }}">
                <span class="iconify" data-icon="material-symbols:tenancy-outline"></span><span>{{ __('Tenancy Setting') }}</span>
            </a>
        @endif
        @if (isAddonInstalled('PROTYLISTING', 0) > 0)
            <a href="{{ route('admin.setting.listing.setting') }}" class="stg-nav__item {{ @$subListingSettingActiveClass ? 'is-active' : '' }}">
                <span class="iconify" data-icon="ri:threads-fill"></span><span>{{ __('Listing Setting') }}</span>
            </a>
            <a href="{{ route('admin.setting.map-box.setting') }}" class="stg-nav__item {{ @$subMapBoxSettingActiveClass ? 'is-active' : '' }}">
                <span class="iconify" data-icon="bx:map"></span><span>{{ __('Mapbox Setting') }}</span>
            </a>
        @endif
        <a href="{{ route('admin.setting.cron.setting') }}" class="stg-nav__item {{ @$subCronSettingActiveClass ? 'is-active' : '' }}">
            <span class="iconify" data-icon="carbon:batch-job"></span><span>{{ __('Cron Setting') }}</span>
        </a>
        @if (isAddonInstalled('PROTYSAAS') > 1)
            <div class="stg-nav__label">{{ __('Landing Page Setting') }}</div>
            <a href="{{ route('admin.home-setting.section') }}" class="stg-nav__item {{ @$subHomeSectionSettingActiveClass ? 'is-active' : '' }}">
                <span class="iconify" data-icon="carbon:settings"></span><span>{{ __('Section Show/Hide') }}</span>
            </a>
            <a href="{{ route('admin.feature.index') }}" class="stg-nav__item {{ @$subFeatureActiveClass ? 'is-active' : '' }}">
                <span class="iconify" data-icon="carbon:settings"></span><span>{{ __('Features') }}</span>
            </a>
            <a href="{{ route('admin.how-it-work.index') }}" class="stg-nav__item {{ @$subHowItWorkActiveClass ? 'is-active' : '' }}">
                <span class="iconify" data-icon="carbon:settings"></span><span>{{ __('How It Work') }}</span>
            </a>
            <a href="{{ route('admin.core-page.index') }}" class="stg-nav__item {{ @$subCorePageActiveClass ? 'is-active' : '' }}">
                <span class="iconify" data-icon="carbon:settings"></span><span>{{ __('Core Page') }}</span>
            </a>
            <a href="{{ route('admin.testimonials.index') }}" class="stg-nav__item {{ @$subTestimonialsActiveClass ? 'is-active' : '' }}">
                <span class="iconify" data-icon="carbon:settings"></span><span>{{ __('Testimonials') }}</span>
            </a>
            <a href="{{ route('admin.faq.index') }}" class="stg-nav__item {{ @$subFaqActiveClass ? 'is-active' : '' }}">
                <span class="iconify" data-icon="carbon:settings"></span><span>{{ __('Faq') }}</span>
            </a>
        @endif
    </div>
</div>

<style>
    .stg-nav { padding:8px; margin-bottom:18px; }
    .stg-nav__item { display:flex; align-items:center; gap:10px; padding:10px 12px; border-radius:8px;
        font-size:13px; font-weight:500; color:var(--gray-700); text-decoration:none; transition:all .13s; }
    .stg-nav__item + .stg-nav__item { margin-top:2px; }
    .stg-nav__item .iconify, .stg-nav__item i { font-size:17px; color:var(--gray-400); flex:none; }
    .stg-nav__item:hover { background:var(--gray-50); color:var(--gray-900); }
    .stg-nav__item:hover .iconify { color:var(--gray-700); }
    .stg-nav__item.is-active { background:var(--blue-light); color:#0C447C; }
    .stg-nav__item.is-active .iconify { color:var(--blue); }
    .stg-nav__label { font-size:10px; font-weight:600; text-transform:uppercase; letter-spacing:.07em;
        color:var(--gray-400); padding:14px 12px 6px; }
</style>
