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
            {{-- <div class="header-item px-3">
                <input type="text" class="form-control" id="topSearch">
                <div class="bg-white position-absolute shadow-lg top-100 w-100">
                    <div id="topSearchContent" class="text-left"></div>
                </div>
            </div> --}}
        </div>
        @php
            $subscriptionService = app(\App\Services\SubscriptionService::class);
            $subscriptionState = $subscriptionService->getSubscriptionState();
            $state = $subscriptionState['state'] ?? null;
            $daysLeft = $subscriptionState['days_left'] ?? null;
        @endphp

        {{-- Mobile Alert Icon --}}
        <div class="subscription-alert-toggle" onclick="toggleSubscriptionBanner()">
            !
        </div>

        {{-- Subscription Banner --}}
        @if (isAddonInstalled('PROTYSAAS') > 1 && $state && $state !== 'active')
            <div id="subscription-banner"
                class="subscription-banner subscription-{{ $state }}">
                <div class="banner-content">
                    <div class="banner-icon">
                        @if($state === 'none')
                            <i class="fas fa-exclamation-circle"></i>
                        @elseif($state === 'expired')
                            <i class="fas fa-times-circle"></i>
                        @elseif($state === 'expiring')
                            <i class="fas fa-hourglass-half"></i>
                        @endif
                    </div>

                    <div class="banner-text">
                        @if($state === 'none')
                            {{ __('You currently have no subscription.') }}
                            <a href="{{ route('owner.subscription.index', ['current_plan' => 'no']) }}">
                                {{ __('Choose a plan') }}
                            </a>

                        @elseif($state === 'expired')
                            {{ __('Your subscription has expired.') }}
                            <a href="{{ route('owner.subscription.index', ['current_plan' => 'no']) }}">
                                {{ __('Renew now') }}
                            </a>

                        @elseif($state === 'expiring')
                            {{ __('Your subscription expires in') }}
                            <strong id="countdown-days">{{ $daysLeft }}</strong>
                            {{ __('day(s).') }}
                        @endif
                    </div>

                    <button id="close-banner" class="banner-close">&times;</button>
                </div>
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
                    <!-- item-->
                    <a class="dropdown-item" href="{{ route('profile') }}"><i
                            class="ri-user-line align-middle me-1"></i> {{ __('Profile') }}</a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="{{ route('logout') }}"><i
                            class="ri-shut-down-line align-middle me-1"></i> {{ __('Logout') }}</a>
                </div>
            </div>
        </div>
    </div>
    <style>
       .subscription-banner {
            width: 50%;
            margin: 8px auto 0 auto;
            animation: slideDown 0.4s ease forwards;
            font-size: 0.95rem;
            font-weight: 500;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }
        /* Alert circle (hidden on desktop) */
        .subscription-alert-toggle {
            display: none;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #ffb020;
            color: white;
            font-weight: bold;
            text-align: center;
            line-height: 32px;
            cursor: pointer;
        }
    
        /* Mobile */
        @media (max-width: 768px) {
            .subscription-banner {
                width: 70%;
                font-size: 0.85rem;
                display: none;
                position: absolute;
                top: 60px;
                left:50px;
                z-index: 1000;
            }

            .subscription-alert-toggle {
                display: block;
            }
        }

        .banner-content {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 8px 12px;
            position: relative;
        }
        

        .banner-icon {
            margin-right: 10px;
            font-size: 1.1rem;
        }

        .banner-text a {
            font-weight: 600;
            text-decoration: underline;
            margin-left: 6px;
            color: #fff;
        }

        .banner-close {
            position: absolute;
            right: 15px;
            border: none;
            background: transparent;
            color: white;
            font-size: 1.3rem;
            cursor: pointer;
        }
        

        /* States */
        .subscription-none {
            background: #d93025;
        }

        .subscription-expired {
            background: #b71c1c;
        }

        .subscription-expiring {
            background: linear-gradient(to right, #fbc02d, #f57c00);
        }

        /* Animations */
        @keyframes slideDown {
            from { transform: translateY(-100%); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .subscription-expiring .banner-icon {
            animation: pulse 1.5s infinite;
        }

        .subscription-expired .banner-icon {
            animation: shake 0.8s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.15); }
            100% { transform: scale(1); }
        }

        @keyframes shake {
            0%,100% { transform: translateX(0); }
            25% { transform: translateX(-3px); }
            50% { transform: translateX(3px); }
            75% { transform: translateX(-3px); }
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function () {

            const banner = document.getElementById('subscription-banner');
            const closeBtn = document.getElementById('close-banner');

            if (banner && closeBtn) {
                closeBtn.addEventListener('click', function () {
                    banner.style.transition = "all 0.4s ease";
                    banner.style.opacity = "0";
                    banner.style.transform = "translateY(-15px)";
                    setTimeout(() => banner.remove(), 400);
                });
            }

        });
        function toggleSubscriptionBanner() {
            const banner = document.getElementById('subscription-banner');
            banner.style.display =
                banner.style.display === 'block' ? 'none' : 'block';
        }
    </script>
</header>
