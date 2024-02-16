<div class="vertical-menu">
    <div data-simplebar class="h-100">
        <div id="sidebar-menu">
            <ul class="metismenu list-unstyled" id="side-menu">
                <li>
                    <a href="{{ route('admin.dashboard') }}">
                        <i class="ri-dashboard-line"></i>
                        <span>{{ __('Dashboard') }}</span>
                    </a>
                </li>
                @if (isAddonInstalled('PROTYSAAS') > 3)
                    <li>
                        <a href="{{ route('admin.packages.index') }}">
                            <i class="ri-bookmark-2-line"></i>
                            <span>{{ __('Packages') }}</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.subscriptions.orders') }}">
                            <i class="ri-list-check-2"></i>
                            <span>{{ __('All Orders') }}</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.packages.owner') }}">
                            <i class="ri-file-list-line"></i>
                            <span>{{ __('Owner Packages') }}</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.message.index') }}">
                            <i class="ri-message-fill"></i>
                            <span>{{ __('Message') }}</span>
                        </a>
                    </li>
                    <li>
                        <a href="javascript: void(0);" class="has-arrow">
                            <i class="ri-lock-2-line"></i>
                            <span>{{ __('Manage Policy') }}</span>
                        </a>
                        <ul class="sub-menu" aria-expanded="false">
                            <li>
                                <a
                                    href="{{ route('admin.setting.terms-conditions') }}">{{ __('Terms & Conditions') }}</a>
                            </li>
                            <li>
                                <a href="{{ route('admin.setting.privacy-policy') }}">{{ __('Privacy Policy') }}</a>
                            </li>
                            <li>
                                <a href="{{ route('admin.setting.cookie-policy') }}">{{ __('Cookie Policy') }}</a>
                            </li>
                        </ul>
                    </li>
                @endif
                <li>
                    <a href="{{ route('admin.owner.index') }}">
                        <i class="ri-user-line"></i>
                        <span>{{ __('Owner') }}</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.setting.general-setting') }}">
                        <i class="ri-settings-3-line"></i>
                        <span>{{ __('Settings') }}</span>
                    </a>
                </li>
                <li>
                    <a href="javascript: void(0);" class="has-arrow">
                        <i class="ri-account-circle-line"></i>
                        <span>{{ __('Profile') }}</span>
                    </a>
                    <ul class="sub-menu {{ @$navProfileMMShowClass }}" aria-expanded="false">
                        <li class="{{ @$subNavProfileMMActiveClass }}"><a class="{{ @$subNavProfileActiveClass }}"
                                href="{{ route('profile') }}">{{ __('My Profile') }}</a></li>
                        <li><a href="{{ route('change-password') }}">{{ __('Change Password') }}</a></li>
                    </ul>
                </li>
                <li class="{{ @$subNavVersionUpdateActiveClass }}">
                    <a href="{{ route('admin.file-version-update') }}"
                        class="{{ @$subNavVersionUpdateActiveClass ? 'active' : '' }}">
                        <i class="ri-refresh-line"></i>
                        <span>{{ __('Version Update') }}</span>
                    </a>
                </li>
                <li class="font-semi-bold mt-20 text-center text-info">
                    <a href="">
                        <span>
                            {{ __('Current Version') }} :
                        </span>
                        {{ getOption('current_version', 'v1.0') }}
                    </a>
                </li>
            </ul>
        </div>
    </div>
</div>
