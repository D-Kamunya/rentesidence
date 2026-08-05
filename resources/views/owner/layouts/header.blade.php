<header id="page-topbar">
    <div class="navbar-header">
        <div class="d-flex">
            <div class="navbar-brand-box">
                <a href="{{ route('owner.dashboard') }}" class="logo logo-light">
                    <span class="logo-sm">
                        <img src="{{ getSettingImage('app_logo') }}" alt="logo-sm-light">
                    </span>
                    <span class="logo-lg">
                        <img src="{{ getSettingImage('app_logo') }}" alt="logo-light">
                    </span>
                </a>
            </div>
            <button type="button" class="btn-sm px-3 font-24 header-item" id="vertical-menu-btn">
                <i class="ri-indent-decrease"></i>
            </button>
        </div>

        @php
            $subscriptionService = app(\App\Services\SubscriptionService::class);
            $subscriptionState = $subscriptionService->getSubscriptionState();
            $state = $subscriptionState['state'] ?? null;
            $daysLeft = $subscriptionState['days_left'] ?? null;
            $infraDue = $subscriptionState['infra']['amount_due'] ?? 0;

            // CTA target: infra states → subscription page (pay infra); expired/expiring
            // → direct-renew the previous plan (pre-filled checkout) when we have one,
            // else the choose-plan modal; none → choose-plan.
            $renew = $subscriptionState['renew'] ?? null;
            $directRenewUrl = $renew ? route('owner.subscription.index', ['id' => $renew['package_id'], 'duration_type' => $renew['duration_type'], 'quantity' => $renew['quantity']]) : null;
            $bannerActionUrl = match (true) {
                in_array($state, ['infra_due', 'restricted']) => route('owner.subscription.index'),
                in_array($state, ['expired', 'expiring']) && $directRenewUrl => $directRenewUrl,
                default => route('owner.subscription.index', ['current_plan' => 'no']),
            };

            $showSubscriptionAlert = isAddonInstalled('PROTYSAAS') && $state && $state !== 'active';
        @endphp

        {{-- Mobile Alert Icon --}}
        @if($showSubscriptionAlert)
            <div class="subscription-alert-toggle subscription-alert-toggle--{{ $state }}" onclick="toggleSubscriptionBanner()">
                @if($state === 'none')
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/>
                        <path d="M12 8v4M12 16h.01" stroke-linecap="round"/>
                    </svg>
                @elseif($state === 'expired')
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/>
                        <path d="M12 8v4M12 16h.01" stroke-linecap="round"/>
                    </svg>
                @elseif($state === 'expiring')
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/>
                        <polyline points="12 6 12 12 16 14" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                @elseif($state === 'infra_due' || $state === 'restricted')
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 3h16v18l-3-2-2 2-3-2-3 2-2-2-3 2z"/>
                        <path d="M8 8h8M8 12h5"/>
                    </svg>
                @endif
            </div>
        @endif

        {{-- Subscription Banner --}}
        @if($showSubscriptionAlert)
            <div id="subscription-banner" class="subscription-banner subscription-banner--{{ $state }}">
                <div class="subscription-banner__container">
                    {{-- Left accent line --}}
                    <div class="subscription-banner__accent"></div>
                    
                    <div class="subscription-banner__body">
                        <div class="subscription-banner__left">
                            <div class="subscription-banner__icon-wrapper">
                                @if($state === 'none')
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="12" r="10"/>
                                        <path d="M12 8v4M12 16h.01"/>
                                    </svg>
                                @elseif($state === 'expired')
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="12" r="10"/>
                                        <path d="M12 8v4M12 16h.01"/>
                                    </svg>
                                @elseif($state === 'expiring')
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="12" r="10"/>
                                        <polyline points="12 6 12 12 16 14"/>
                                    </svg>
                                @elseif($state === 'infra_due' || $state === 'restricted')
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M4 3h16v18l-3-2-2 2-3-2-3 2-2-2-3 2z"/>
                                        <path d="M8 8h8M8 12h5"/>
                                    </svg>
                                @endif
                            </div>
                        </div>

                        <div class="subscription-banner__content">
                            <div class="subscription-banner__message">
                                @if($state === 'none')
                                    <span class="subscription-banner__title">{{ __('No Active Subscription') }}</span>
                                    <span class="subscription-banner__subtitle">{{ __('Choose a plan to unlock all features') }}</span>
                                @elseif($state === 'expired')
                                    <span class="subscription-banner__title">{{ __('Subscription Expired') }}</span>
                                    <span class="subscription-banner__subtitle">{{ __('Your access has been limited. Renew now.') }}</span>
                                @elseif($state === 'expiring')
                                    <span class="subscription-banner__title">{{ __('Subscription Expiring Soon') }}</span>
                                    <div class="subscription-banner__countdown">
                                        <span class="subscription-banner__days">{{ $daysLeft }}</span>
                                        <span class="subscription-banner__days-label">{{ Str::plural('day', $daysLeft) }} remaining</span>
                                    </div>
                                @elseif($state === 'infra_due')
                                    <span class="subscription-banner__title">{{ __('Infrastructure Bill Due') }}</span>
                                    <span class="subscription-banner__subtitle">{{ __('Pay :amount to keep full access to your modules.', ['amount' => 'KES ' . number_format($infraDue, 2)]) }}</span>
                                @elseif($state === 'restricted')
                                    <span class="subscription-banner__title">{{ __('Access Restricted — Infrastructure Overdue') }}</span>
                                    <span class="subscription-banner__subtitle">{{ __('Pay :amount to restore full access.', ['amount' => 'KES ' . number_format($infraDue, 2)]) }}</span>
                                @endif
                            </div>

                            <a href="{{ $bannerActionUrl }}" class="subscription-banner__action">
                                @if($state === 'none')
                                    {{ __('Choose Plan') }}
                                @elseif($state === 'expired')
                                    {{ __('Renew Now') }}
                                @elseif($state === 'infra_due' || $state === 'restricted')
                                    {{ __('Pay Now') }}
                                @else
                                    {{ __('View Plans') }}
                                @endif
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M5 12h14M13 6l6 6-6 6"/>
                                </svg>
                            </a>
                            @if(in_array($state, ['expired', 'expiring']))
                                <a href="{{ route('owner.subscription.index', ['current_plan' => 'no']) }}" class="subscription-banner__alt">{{ __('choose another plan') }}</a>
                            @endif
                        </div>
                    </div>

                    <button id="close-banner" class="subscription-banner__close" aria-label="Close notification">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                            <path d="M18 6L6 18M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                {{-- Progress bar for expiring state --}}
                @if($state === 'expiring' && isset($daysLeft))
                    <div class="subscription-banner__progress">
                        <div class="subscription-banner__progress-bar subscription-banner__progress-bar--{{ $daysLeft <= 7 ? 'critical' : ($daysLeft <= 14 ? 'warning' : 'normal') }}" 
                             style="width: {{ min(100, ((30 - $daysLeft) / 30) * 100) }}%">
                        </div>
                    </div>
                @endif
            </div>
        @endif
       
        <div class="d-flex">
            <div class="dropdown d-inline-block">
                <button type="button" class="header-item noti-icon" id="page-header-languages-dropdown"
                    data-bs-toggle="dropdown" aria-expanded="false">
                    <img src="{{ asset(selectedLanguage()->icon) }}" alt="{{ selectedLanguage()->name ?? 'English' }}"
                        title="{{ selectedLanguage()->name ?? 'English' }}" class="rounded-circle avatar-xs fit-image">
                </button>
                <div class="dropdown-menu {{ selectedLanguage()->rtl == 1 ? 'dropdown-menu-start' : 'dropdown-menu-end' }}"
                    aria-labelledby="page-header-languages-dropdown">
                    <div>
                        @foreach (languages() as $language)
                            <a href="{{ route('local', $language->code) }}" class="dropdown-item"
                                title="{{ $language->code }}">
                                <div class="d-flex">
                                    <img src="{{ $language->icon }}" class="me-3 rounded-circle avatar-xs"
                                        alt="user-pic">
                                    <div class="flex-1">{{ $language->name }}</div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="dropdown d-inline-block">
                <button type="button" class="header-item noti-icon" id="page-header-notifications-dropdown"
                    data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="ri-notification-2-fill"></i>
                    @if (count(getNotificationLimit(auth()->id())) > 0)
                        <span class="noti-dot pulse"></span>
                    @endif
                </button>
                <div class="dropdown-menu dropdown-menu-lg {{ selectedLanguage()->rtl == 1 ? 'dropdown-menu-start' : 'dropdown-menu-end' }} p-0"
                    aria-labelledby="page-header-notifications-dropdown">
                    <div class="p-3">
                        <div class="row align-items-center">
                            <div class="col">
                                <h5 class="m-0 text-start">{{ __('Notifications') }}</h5>
                            </div>
                            <div class="col-auto">
                            </div>
                        </div>
                    </div>
                    <div data-simplebar>
                        @foreach (getNotificationLimit(auth()->id()) as $notification)
                            @php
                                $url = $notification->url ?? route('owner.notification');
                            @endphp
                            <a href="{{ route('notification.status', ['id' => $notification->id,'role' => auth()->user()->role]) }}?url={{ urlencode($url) }}" class="notification-item">
                                <div class="d-flex">
                                    <img src="{{ getFileUrl($notification->folder_name, $notification->file_name) }}"
                                        class="me-3 rounded-circle avatar-xs" alt="user-pic">

                                    <div class="flex-1">
                                        <h6 class="mb-1">{{ $notification->first_name }} {{ $notification->last_name }} </h6>
                                        <div class="">
                                            <p class="mb-1">{{ $notification->title }}</p>
                                            <p class="mb-0 font-12"><i class="mdi mdi-clock-outline"></i>
                                                {{ $notification->created_at->diffForHumans() }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                    <div class="p-2 border-top">
                        <div class="d-grid">
                            <a class="btn-sm theme-link font-size-14 d-flex align-items-center justify-content-center"
                                href="{{ route('owner.notification') }}">
                                {{ __('See All') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="dropdown d-inline-block user-dropdown">
                <button type="button" class="header-item" id="page-header-user-dropdown" data-bs-toggle="dropdown"
                    aria-haspopup="true" aria-expanded="false">
                    <img class="rounded-circle avatar-xs fit-image header-profile-user"
                        src="{{ auth()->user()->image }}" alt="Header Avatar">
                    <span class="d-none d-xl-inline-block ms-1 font-medium">{{ auth()->user()->name }}</span>
                    <i class="mdi mdi-chevron-down d-xl-inline-block"></i>
                </button>
                <div class="dropdown-menu {{ selectedLanguage()->rtl == 1 ? 'dropdown-menu-start' : 'dropdown-menu-end' }}"
                    aria-labelledby="page-header-user-dropdown">
                    <a class="dropdown-item" href="{{ route('profile') }}">
                        <i class="ri-user-line align-middle me-1"></i> {{ __('Profile') }}
                    </a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="{{ route('logout') }}">
                        <i class="ri-shut-down-line align-middle me-1"></i> {{ __('Logout') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</header>

<style>
    /* ── Subscription Banner ──────────────────────────────────── */
    .subscription-banner {
        --accent: #185FA5;   /* state-driven; overridden per modifier below */
        --tint:   #E6F1FB;
        width: 50%;
        max-width: 620px;
        margin: 0 auto;
        background: #ffffff;
        border: 0.5px solid #e5e7eb;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 6px 22px rgba(24, 95, 165, 0.10), 0 1px 3px rgba(16, 24, 40, 0.06);
        animation: bannerSlideIn 0.45s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        position: relative;
    }

    .subscription-banner__container {
        display: flex;
        align-items: center;
        padding: 0;
        position: relative;
        min-height: 52px;
    }

    .subscription-banner__accent {
        width: 3px;
        align-self: stretch;
        background: var(--accent);
        flex-shrink: 0;
    }

    .subscription-banner__body {
        display: flex;
        align-items: center;
        flex: 1;
        padding: 12px 16px;
        gap: 14px;
    }

    .subscription-banner__left {
        flex-shrink: 0;
    }

    .subscription-banner__icon-wrapper {
        width: 38px;
        height: 38px;
        border-radius: 9px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--tint);
        color: var(--accent);
    }

    .subscription-banner__content {
        display: flex;
        align-items: center;
        flex: 1;
        gap: 16px;
        flex-wrap: wrap;
    }

    .subscription-banner__message {
        display: flex;
        flex-direction: column;
        gap: 2px;
        flex: 1;
        min-width: 0;
    }

    .subscription-banner__title {
        font-size: 13px;
        font-weight: 600;
        letter-spacing: -0.01em;
        line-height: 1.3;
        color: #111827;
    }

    .subscription-banner__subtitle {
        font-size: 11px;
        font-weight: 400;
        color: #6b7280;
        line-height: 1.4;
    }

    .subscription-banner__countdown {
        display: flex;
        align-items: baseline;
        gap: 6px;
    }

    .subscription-banner__days {
        font-size: 18px;
        font-weight: 700;
        letter-spacing: -0.02em;
        line-height: 1;
        padding: 3px 10px;
        border-radius: 8px;
        background: var(--tint);
        color: var(--accent);
    }

    .subscription-banner__days-label {
        font-size: 11px;
        font-weight: 500;
        color: #6b7280;
    }

    .subscription-banner__action {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 16px;
        border-radius: 7px;
        font-size: 12px;
        font-weight: 600;
        text-decoration: none;
        color: #ffffff;
        background: var(--accent);
        border: 0.5px solid var(--accent);
        transition: filter 0.15s, transform 0.15s, box-shadow 0.15s;
        white-space: nowrap;
        flex-shrink: 0;
    }

    .subscription-banner__action:hover {
        filter: brightness(0.94);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(24, 95, 165, 0.18);
        color: #ffffff;
    }

    .subscription-banner__action:active {
        transform: translateY(0);
    }

    .subscription-banner__alt {
        font-size: 11px;
        font-weight: 500;
        color: #6b7280;
        text-decoration: underline;
        text-underline-offset: 2px;
        white-space: nowrap;
        align-self: center;
        flex-shrink: 0;
    }
    .subscription-banner__alt:hover { color: var(--accent); }

    .subscription-banner__close {
        position: absolute;
        right: 8px;
        top: 8px;
        width: 24px;
        height: 24px;
        border-radius: 6px;
        border: none;
        background: transparent;
        color: #9ca3af;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0;
        transition: background 0.15s, color 0.15s;
        flex-shrink: 0;
    }

    .subscription-banner__close:hover {
        color: #6b7280;
        background: #f3f4f6;
    }

    .subscription-banner__progress {
        height: 3px;
        background: #f3f4f6;
        width: 100%;
    }

    .subscription-banner__progress-bar {
        height: 100%;
        transition: width 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        border-radius: 0 2px 2px 0;
        background: var(--accent);
    }

    .subscription-banner__progress-bar--normal   { background: var(--accent); opacity: 0.5; }
    .subscription-banner__progress-bar--warning  { background: var(--accent); opacity: 0.8; }
    .subscription-banner__progress-bar--critical { background: var(--accent); animation: progressPulse 1.5s infinite; }

    /* ── State tokens (grey · pending/amber · overdue/red) ─────── */
    .subscription-banner--none       { --accent: #6b7280; --tint: #f3f4f6; }
    .subscription-banner--expiring   { --accent: #854F0B; --tint: #FAEEDA; }
    .subscription-banner--infra_due  { --accent: #854F0B; --tint: #FAEEDA; }
    .subscription-banner--expired    { --accent: #A32D2D; --tint: #FCEBEB; }
    .subscription-banner--restricted { --accent: #A32D2D; --tint: #FCEBEB; }

    /* Urgent states keep a subtle icon pulse to draw the eye. */
    .subscription-banner--expired .subscription-banner__icon-wrapper,
    .subscription-banner--restricted .subscription-banner__icon-wrapper {
        animation: iconPulse 2s infinite;
    }

    /* ── Mobile Alert Toggle ──────────────────────────────────── */
    .subscription-alert-toggle {
        display: none;
        width: 36px;
        height: 36px;
        border-radius: 10px;
        color: white;
        cursor: pointer;
        align-items: center;
        justify-content: center;
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        position: relative;
    }

    .subscription-alert-toggle::after {
        content: '';
        position: absolute;
        top: 6px;
        right: 6px;
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #fff;
        animation: dotPulse 1.5s infinite;
    }

    .subscription-alert-toggle:hover {
        transform: scale(1.05);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    }

    .subscription-alert-toggle--none       { background: #6b7280; }
    .subscription-alert-toggle--expiring   { background: #854F0B; }
    .subscription-alert-toggle--infra_due  { background: #854F0B; }
    .subscription-alert-toggle--expired    { background: #A32D2D; }
    .subscription-alert-toggle--restricted { background: #A32D2D; }

    /* ── Animations ───────────────────────────────────────────── */
    @keyframes bannerSlideIn {
        from { 
            transform: translateY(-8px); 
            opacity: 0; 
        }
        to { 
            transform: translateY(0); 
            opacity: 1; 
        }
    }

    @keyframes iconPulse {
        0%, 100% { opacity: 0.7; }
        50% { opacity: 1; }
    }

    @keyframes dotPulse {
        0%, 100% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.5; transform: scale(1.3); }
    }

    @keyframes progressPulse {
        0%, 100% { opacity: 0.8; }
        50% { opacity: 1; }
    }

    /* ── Mobile Responsive ────────────────────────────────────── */
    @media (max-width: 768px) {
        .subscription-banner {
            width: calc(100% - 32px);
            max-width: none;
            display: none;
            position: fixed;
            top: 72px;
            left: 16px;
            right: 16px;
            z-index: 1000;
            margin: 0;
            border-radius: 14px;
        }

        .subscription-banner__body {
            padding: 14px 44px 14px 14px;
            gap: 12px;
        }

        .subscription-banner__icon-wrapper {
            width: 36px;
            height: 36px;
            border-radius: 8px;
        }

        .subscription-banner__content {
            flex-direction: column;
            align-items: flex-start;
            gap: 10px;
        }

        .subscription-banner__title {
            font-size: 12px;
        }

        .subscription-banner__subtitle {
            font-size: 10px;
        }

        .subscription-banner__days {
            font-size: 18px;
        }

        .subscription-banner__action {
            font-size: 11px;
            padding: 7px 14px;
            width: 100%;
            justify-content: center;
        }

        .subscription-alert-toggle {
            display: flex;
        }

        .subscription-banner__close {
            right: 6px;
            top: 6px;
            width: 28px;
            height: 28px;
        }
    }

    @media (max-width: 480px) {
        .subscription-banner {
            width: calc(100% - 20px);
            left: 10px;
            right: 10px;
            top: 68px;
        }

        .subscription-banner__body {
            padding: 12px 40px 12px 12px;
            gap: 10px;
        }

        .subscription-banner__icon-wrapper {
            width: 32px;
            height: 32px;
            border-radius: 8px;
        }

        .subscription-banner__icon-wrapper svg {
            width: 16px;
            height: 16px;
        }

        .subscription-banner__title {
            font-size: 11px;
        }

        .subscription-banner__days {
            font-size: 16px;
            padding: 2px 8px;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const banner = document.getElementById('subscription-banner');
        const closeBtn = document.getElementById('close-banner');

        if (banner && closeBtn) {
            closeBtn.addEventListener('click', function () {
                banner.style.transition = "all 0.3s cubic-bezier(0.16, 1, 0.3, 1)";
                banner.style.opacity = "0";
                banner.style.transform = "translateY(-8px) scale(0.98)";
                setTimeout(() => {
                    banner.style.display = 'none';
                    banner.remove();
                }, 300);
            });
        }

        // Countdown update for expiring state
        @if($state === 'expiring' && isset($daysLeft))
            const countdownEl = document.getElementById('countdown-days');
            if (countdownEl) {
                let daysLeft = {{ $daysLeft }};
                updateProgressBar(daysLeft);
            }
        @endif
    });

    function toggleSubscriptionBanner() {
        const banner = document.getElementById('subscription-banner');
        if (banner) {
            if (banner.style.display === 'none' || banner.style.display === '') {
                banner.style.display = 'block';
                banner.style.animation = 'none';
                banner.offsetHeight;
                banner.style.animation = 'bannerSlideIn 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards';
            } else {
                banner.style.transition = "all 0.3s cubic-bezier(0.16, 1, 0.3, 1)";
                banner.style.opacity = "0";
                banner.style.transform = "translateY(-8px) scale(0.98)";
                setTimeout(() => {
                    banner.style.display = 'none';
                }, 300);
            }
        }
    }

    function updateProgressBar(daysLeft) {
        const progressBar = document.querySelector('.subscription-banner__progress-bar');
        if (progressBar) {
            const percentage = Math.min(100, ((30 - daysLeft) / 30) * 100);
            progressBar.style.width = percentage + '%';
            
            // Update color class based on days left
            progressBar.classList.remove('normal', 'warning', 'critical');
            if (daysLeft <= 7) {
                progressBar.classList.add('critical');
            } else if (daysLeft <= 14) {
                progressBar.classList.add('warning');
            } else {
                progressBar.classList.add('normal');
            }
        }
    }
</script>