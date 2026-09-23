@include('common.layouts._kb-nav-style')
<div class="vertical-menu">
    <div data-simplebar class="h-100">

        <!--- Sidemenu -->
        <div id="sidebar-menu">
            <!-- Left Menu Start -->
            <ul class="metismenu list-unstyled" id="side-menu">

                <li>
                    <a href="{{ route('affiliate.dashboard') }}">
                        <i class="ri-dashboard-line"></i>
                        <span>{{ __('Dashboard') }}</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('affiliate.marketplace.index') }}">
                        <i class="fa fa-shopping-basket" aria-hidden="true"></i>
                        <span>{{ __('Lead Marketplace') }}@include('partials.nav-count', ['n' => $navBadges['marketplace_leads'] ?? 0])</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('affiliate.leads') }}">
                        <i class="fa fa-cog" aria-hidden="true"></i>
                        <span>{{ __('My Leads') }}@include('partials.nav-count', ['n' => $navBadges['lead_suggestions'] ?? 0])</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('affiliate.academy.index') }}">
                        <i class="fa fa-graduation-cap"></i>
                        <span>{{ __('Academy') }}</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('affiliate.marketing-tools') }}">
                        <i class="ri-lightbulb-line"></i>
                        <span>{{ __('Marketing Tools') }}</span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('affiliate.materials.index') }}">
                        <i class="fa fa-shopping-bag" aria-hidden="true"></i>
                        <span>{{ __('Marketing Materials') }}</span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('affiliate.referrals.index') }}">
                        <i class="ri-group-line"></i>
                        <span>{{ __('My Referrals') }}</span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('affiliate.commissions.index') }}">
                        <i class="ri-money-dollar-circle-line"></i>
                        <span>{{ __('Earnings') }}</span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('affiliate.leaderboard') }}">
                        <i class="ri-trophy-line"></i>
                        <span>{{ __('Leaderboard') }}</span>
                    </a>
                </li>

                {{-- Support — reach our team (reusable support rail). --}}
                <li>
                    <a href="{{ route('support.index') }}">
                        <i class="ri-customer-service-2-line"></i>
                        <span>{{ __('Support') }}@include('partials.nav-count', ['n' => $navBadges['support'] ?? 0])</span>
                    </a>
                </li>
                {{-- Install app (auto-hides once installed). --}}
                <li>
                    <a href="#" data-cs-install onclick="return window.csPwaInstall ? (window.csPwaInstall(), false) : true;">
                        <i class="ri-smartphone-line"></i>
                        <span>{{ __('Install app') }}</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('affiliate.kb.index') }}" class="kb-nav-highlight">
                        <i class="fa fa-book"></i>
                        <span>{{ __('Knowledge Base') }}</span>
                    </a>
                </li>
            </ul>
        </div>

        <!-- Sidebar -->
    </div>
</div>
