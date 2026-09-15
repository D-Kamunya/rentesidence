<div class="vertical-menu">
    <div data-simplebar class="h-100">
        <div id="sidebar-menu">
            <ul class="metismenu list-unstyled" id="side-menu">

                <li>
                    <a href="{{ route('maintainer.dashboard') }}">
                        <i class="ri-dashboard-line"></i>
                        <span>{{ __('Dashboard') }}</span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('maintainer.ticket.index') }}">
                        <i class="ri-bookmark-2-line"></i>
                        <span>{{ __('Tickets') }}</span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('maintainer.information.index') }}">
                        <i class="ri-folder-info-line"></i>
                        <span>{{ __('Information') }}</span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('maintainer.maintenance-request.index') }}">
                        <i class="ri-tools-fill"></i>
                        <span>{{ __('Maintenance Request') }}</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('maintainer.rent.index') }}">
                        <i class="ri-money-dollar-circle-line"></i>
                        <span>{{ __('Rent & Payments') }}</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('maintainer.dispatch.index') }}">
                        <i class="ri-truck-line"></i>
                        <span>{{ __('Dispatch') }}</span>
                    </a>
                </li>

                <li>
                    <a href="javascript: void(0);" class="has-arrow">
                        <i class="ri-account-circle-line"></i>
                        <span>{{ __('Profile') }}</span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li><a href="{{ route('profile') }}">{{ __('My Profile') }}</a></li>
                        <li><a href="{{ route('change-password') }}">{{ __('Change Password') }}</a></li>
                    </ul>
                </li>
                {{-- Install app (auto-hides once installed). --}}
                <li>
                    <a href="#" data-cs-install onclick="return window.csPwaInstall ? (window.csPwaInstall(), false) : true;">
                        <i class="ri-smartphone-line"></i>
                        <span>{{ __('Install app') }}</span>
                    </a>
                </li>

            </ul>
        </div>
    </div>
</div>
