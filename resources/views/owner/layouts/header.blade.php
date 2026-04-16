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
            
            $showSubscriptionAlert = isAddonInstalled('PROTYSAAS') && $state && $state !== 'active';
        @endphp

        {{-- Mobile Alert Icon --}}
        @if($showSubscriptionAlert)
            <div class="subscription-alert-toggle subscription-alert-toggle--{{ $state }}" onclick="toggleSubscriptionBanner()">
                @if($state === 'none')
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z" fill="currentColor"/>
                    </svg>
                @elseif($state === 'expired')
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z" fill="currentColor"/>
                    </svg>
                @elseif($state === 'expiring')
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm.5-13h-1v7l5.25 3.15.75-1.23-4.5-2.67V7z" fill="currentColor"/>
                    </svg>
                @endif
            </div>
        @endif

        {{-- Subscription Banner --}}
        @if($showSubscriptionAlert)
            <div id="subscription-banner" class="subscription-banner subscription-banner--{{ $state }}">
                <div class="subscription-banner__content">
                    <div class="subscription-banner__icon">
                        @if($state === 'none')
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z" fill="currentColor"/>
                            </svg>
                        @elseif($state === 'expired')
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z" fill="currentColor"/>
                            </svg>
                        @elseif($state === 'expiring')
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm.5-13h-1v7l5.25 3.15.75-1.23-4.5-2.67V7z" fill="currentColor"/>
                            </svg>
                        @endif
                    </div>

                    <div class="subscription-banner__text">
                        @if($state === 'none')
                            <span>{{ __('No active subscription') }}</span>
                            <a href="{{ route('owner.subscription.index', ['current_plan' => 'no']) }}" class="subscription-banner__link">
                                {{ __('Choose a plan') }}
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none">
                                    <path d="M5 12h14M13 6l6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </a>
                        @elseif($state === 'expired')
                            <span>{{ __('Subscription expired') }}</span>
                            <a href="{{ route('owner.subscription.index', ['current_plan' => 'no']) }}" class="subscription-banner__link">
                                {{ __('Renew now') }}
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none">
                                    <path d="M5 12h14M13 6l6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </a>
                        @elseif($state === 'expiring')
                            <span>{{ __('Expires in') }}</span>
                            <strong id="countdown-days" class="subscription-banner__days">{{ $daysLeft }}</strong>
                            <span>{{ Str::plural('day', $daysLeft) }}</span>
                        @endif
                    </div>

                    <button id="close-banner" class="subscription-banner__close" aria-label="Close">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none">
                            <path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </button>
                </div>
                
                {{-- Progress bar for expiring state --}}
                @if($state === 'expiring' && isset($daysLeft))
                    <div class="subscription-banner__progress">
                        <div class="subscription-banner__progress-bar" style="width: {{ min(100, (30 - $daysLeft) / 30 * 100) }}%"></div>
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
        width: 50%;
        margin: 8px auto 0 auto;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        animation: slideDown 0.4s ease forwards;
        font-size: 0.9rem;
        font-weight: 500;
        border: 0.5px solid rgba(255, 255, 255, 0.1);
    }

    .subscription-banner__content {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 10px 16px;
        position: relative;
    }

    .subscription-banner__icon {
        margin-right: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .subscription-banner__text {
        display: flex;
        align-items: center;
        gap: 6px;
        flex-wrap: wrap;
        justify-content: center;
    }

    .subscription-banner__link {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-weight: 600;
        text-decoration: none;
        padding: 3px 10px;
        border-radius: 99px;
        background: rgba(255, 255, 255, 0.15);
        transition: background 0.15s, transform 0.15s;
        margin-left: 4px;
        color: inherit;
    }

    .subscription-banner__link:hover {
        background: rgba(255, 255, 255, 0.25);
        transform: translateY(-1px);
    }

    .subscription-banner__days {
        font-size: 1.1rem;
        font-weight: 700;
        padding: 2px 8px;
        border-radius: 99px;
        background: rgba(255, 255, 255, 0.2);
    }

    .subscription-banner__close {
        position: absolute;
        right: 12px;
        border: none;
        background: transparent;
        color: inherit;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 4px;
        border-radius: 4px;
        opacity: 0.7;
        transition: opacity 0.15s, background 0.15s;
    }

    .subscription-banner__close:hover {
        opacity: 1;
        background: rgba(255, 255, 255, 0.1);
    }

    .subscription-banner__progress {
        height: 3px;
        background: rgba(255, 255, 255, 0.15);
        width: 100%;
    }

    .subscription-banner__progress-bar {
        height: 100%;
        background: rgba(255, 255, 255, 0.5);
        transition: width 0.3s ease;
    }

    /* ── State Colors (matching the affiliate dashboard intensity) ── */
    
    /* None state - Coral/Orange (warm warning) */
    .subscription-banner--none {
        background: linear-gradient(135deg, #993C1D 0%, #B84A24 100%);
        color: #fff;
    }
    .subscription-banner--none .subscription-banner__icon {
        color: #FDE68A;
    }

    /* Expired state - Deep Red (urgent) */
    .subscription-banner--expired {
        background: linear-gradient(135deg, #991B1B 0%, #B91C1C 100%);
        color: #fff;
    }
    .subscription-banner--expired .subscription-banner__icon {
        color: #FCA5A5;
        animation: pulseWarning 1.5s infinite;
    }

    /* Expiring state - Amber (attention) */
    .subscription-banner--expiring {
        background: linear-gradient(135deg, #854F0B 0%, #A16207 100%);
        color: #fff;
    }
    .subscription-banner--expiring .subscription-banner__icon {
        color: #FDE68A;
        animation: pulseWarning 1.5s infinite;
    }

    /* ── Mobile Alert Toggle ──────────────────────────────────── */
    .subscription-alert-toggle {
        display: none;
        width: 32px;
        height: 32px;
        border-radius: 8px;
        color: white;
        font-weight: bold;
        text-align: center;
        line-height: 32px;
        cursor: pointer;
        align-items: center;
        justify-content: center;
        transition: transform 0.15s, box-shadow 0.15s;
    }

    .subscription-alert-toggle:hover {
        transform: scale(1.05);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
    }

    .subscription-alert-toggle--none {
        background: #993C1D;
    }

    .subscription-alert-toggle--expired {
        background: #991B1B;
        animation: pulseWarning 1.5s infinite;
    }

    .subscription-alert-toggle--expiring {
        background: #854F0B;
        animation: pulseWarning 1.5s infinite;
    }

    /* ── Animations ───────────────────────────────────────────── */
    @keyframes slideDown {
        from { 
            transform: translateY(-100%); 
            opacity: 0; 
        }
        to { 
            transform: translateY(0); 
            opacity: 1; 
        }
    }

    @keyframes pulseWarning {
        0% { opacity: 0.7; }
        50% { opacity: 1; transform: scale(1.05); }
        100% { opacity: 0.7; }
    }

    /* ── Mobile Responsive ────────────────────────────────────── */
    @media (max-width: 768px) {
        .subscription-banner {
            width: calc(100% - 32px);
            max-width: none;
            font-size: 0.8rem;
            display: none;
            position: fixed;
            top: 70px;
            left: 16px;
            right: 16px;
            z-index: 1000;
            margin: 0;
            transform: none;
        }

        .subscription-banner__content {
            padding: 10px 40px 10px 16px;  /* Extra right padding for close button */
        }

        .subscription-banner__text {
            gap: 4px;
            justify-content: flex-start;
            text-align: left;
        }

        .subscription-banner__days {
            font-size: 0.95rem;
            padding: 1px 6px;
        }

        .subscription-alert-toggle {
            display: flex;
        }

        .subscription-banner__close {
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
        }
    }

    @media (max-width: 480px) {
        .subscription-banner {
            width: calc(100% - 24px);
            left: 12px;
            right: 12px;
            top: 65px;
            font-size: 0.75rem;
        }

        .subscription-banner__content {
            padding: 8px 36px 8px 12px;
        }

        .subscription-banner__link {
            padding: 2px 8px;
        }

        .subscription-banner__text {
            flex-wrap: wrap;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const banner = document.getElementById('subscription-banner');
        const closeBtn = document.getElementById('close-banner');

        if (banner && closeBtn) {
            closeBtn.addEventListener('click', function () {
                banner.style.transition = "all 0.3s ease";
                banner.style.opacity = "0";
                banner.style.transform = "translateY(-15px)";
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
                // Update progress bar on load
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
                banner.offsetHeight; // Trigger reflow
                banner.style.animation = 'slideDown 0.3s ease forwards';
            } else {
                banner.style.transition = "all 0.3s ease";
                banner.style.opacity = "0";
                banner.style.transform = "translateY(-15px)";
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
        }
    }
</script>