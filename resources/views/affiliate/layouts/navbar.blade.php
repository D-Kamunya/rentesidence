<header id="page-topbar">
    <div class="navbar-header">
        <div class="d-flex">
            <!-- LOGO -->
            <div class="navbar-brand-box">
                @include('common.layouts._brand', ['brandHref' => route('affiliate.dashboard')])
            </div>
            <button type="button" class="btn-sm px-3 font-24 header-item" id="vertical-menu-btn">
                <i class="ri-indent-decrease"></i>
            </button>
        </div>

        <div class="d-flex">

            <div class="dropdown d-inline-block">
                <button type="button" class="header-item noti-icon" id="page-header-languages-dropdown"
                    data-bs-toggle="dropdown" aria-expanded="false">
                    @php $langIcon = selectedLanguage()->icon; $langName = selectedLanguage()->name ?? "English"; @endphp
                    @if ($langIcon && ! \Illuminate\Support\Str::contains($langIcon, ["no-image", "empty-user"]))
                        <img src="{{ $langIcon }}" alt="{{ $langName }}" title="{{ $langName }}" class="rounded-circle avatar-xs fit-image">
                    @else
                        <i class="ri-global-line cs-lang-globe" title="{{ $langName }}"></i>
                    @endif
                </button>
                <div class="dropdown-menu {{ selectedLanguage()->rtl == 1 ? 'dropdown-menu-start' : 'dropdown-menu-end' }}"
                    aria-labelledby="page-header-languages-dropdown">
                    <div>
                        @foreach (languages() as $language)
                            <a href="{{ route('local', $language->code) }}" class="dropdown-item" title="EN">
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
                        @forelse (getNotificationLimit(auth()->id()) as $notification)
                            @php $url = $notification->url ?? route('affiliate.notification'); @endphp
                            <a href="{{ route('notification.status', ['id' => $notification->id, 'role' => auth()->user()->role]) }}?url={{ urlencode($url) }}" class="notification-item">
                                <div class="d-flex">
                                    <img src="{{ getFileUrl($notification->folder_name, $notification->file_name) }}"
                                        class="me-3 rounded-circle avatar-xs" alt="pic">
                                    <div class="flex-1">
                                        <h6 class="mb-1">{{ $notification->title }}</h6>
                                        <div class="">
                                            @if ($notification->body)<p class="mb-1 font-12">{{ \Illuminate\Support\Str::limit($notification->body, 80) }}</p>@endif
                                            <p class="mb-0 font-12"><i class="mdi mdi-clock-outline"></i>
                                                {{ optional($notification->created_at)->diffForHumans() }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        @empty
                            <div class="p-4 text-center text-muted" style="font-size:13px;">{{ __('No new notifications') }}</div>
                        @endforelse
                    </div>
                    <div class="p-2 border-top">
                        <div class="d-grid">
                            <a class="btn-sm theme-link font-size-14 d-flex align-items-center justify-content-center"
                                href="{{ route('affiliate.notification') }}">
                                {{ __('See All') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="dropdown d-inline-block user-dropdown">
                <button type="button" class="header-item" id="page-header-user-dropdown" data-bs-toggle="dropdown"
                    aria-haspopup="true" aria-expanded="false">
                    @include('common.layouts._avatar', ['name' => auth()->user()->name, 'image' => auth()->user()->image, 'avatarClass' => 'avatar-xs header-profile-user'])
                    <span class="d-none d-xl-inline-block ms-1 font-medium">{{ auth()->user()->name }}</span>
                    <i class="mdi mdi-chevron-down d-xl-inline-block"></i>
                </button>
                <div class="dropdown-menu {{ selectedLanguage()->rtl == 1 ? 'dropdown-menu-start' : 'dropdown-menu-end' }}"
                    aria-labelledby="page-header-user-dropdown">
                    <!-- item-->
                    <a class="dropdown-item" href="{{ route('profile') }}"><i
                            class="ri-user-line align-middle me-1"></i> {{ __('Profile') }}</a>
                    <a class="dropdown-item" href="{{ route('change-password') }}"><i
                            class="ri-lock-password-line align-middle me-1"></i> {{ __('Change Password') }}</a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="{{ route('logout') }}"><i
                            class="ri-shut-down-line align-middle me-1"></i> {{ __('Logout') }}</a>
                </div>
            </div>

        </div>
    </div>
</header>
