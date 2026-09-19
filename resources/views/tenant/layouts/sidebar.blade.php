<div class="vertical-menu">
    <div data-simplebar class="h-100">

        <!--- Sidemenu -->
        <div id="sidebar-menu">
            <!-- Left Menu Start -->
            <ul class="metismenu list-unstyled" id="side-menu">

                <li>
                    <a href="{{ route('tenant.dashboard') }}">
                        <i class="ri-dashboard-line"></i>
                        <span>{{ __('Dashboard') }}</span>
                    </a>
                </li>
                <li class="{{ @$navInvoiceMMActiveClass }}">
                    <a href="{{ route('tenant.invoice.index') }}" class="{{ @$navInvoiceActiveClass }}">
                        <i class="ri-bill-line"></i>
                        <span>{{ __('Invoices') }}@include('partials.nav-count', ['n' => $navBadges['invoices_unpaid'] ?? 0])</span>
                    </a>
                </li>
                <li class="{{ @$navRentalScoreMMActiveClass }}">
                    <a href="{{ route('tenant.rental-score.index') }}" class="{{ @$navRentalScoreActiveClass }}">
                        <i class="ri-shield-star-line"></i>
                        <span>{{ __('My Rental Score') }}</span>
                    </a>
                </li>
                {{-- Invite-a-landlord — available to every tenant (linked or ownerless); it's a growth
                     surface, not owner-bound, so it stays outside the @if(empty($ownerless)) block. --}}
                @if (config('referrals.enabled'))
                <li class="{{ @$navInviteLandlordMMActiveClass }}">
                    <a href="{{ route('tenant.invite-landlord.index') }}" class="{{ @$navInviteLandlordActiveClass }}">
                        <i class="ri-user-shared-line"></i>
                        <span>{{ empty($ownerless) ? __('Refer a Landlord') : __('Invite Your Landlord') }}</span>
                    </a>
                </li>
                @endif
                {{-- Owner-bound surfaces — hidden for an ownerless (standalone Helper) tenant so nothing
                     links back to the former owner. Data is preserved; these return as history later. --}}
                @if (empty($ownerless))
                <li class="{{ @$navMarketPlaceMMActiveClass }}">
                    <a href="{{ route('tenant.product.index') }}" class="{{ @$navMarketPlaceActiveClass }}">
                        <i class="ri-store-2-fill "></i>
                        <span>{{ __('Market Place') }}</span>
                    </a>
                </li>
                <li class="{{ @$navProductOrdersMMActiveClass }}">
                    <a href="{{ route('tenant.order.index') }}" class="{{ @$navProductOrderActiveClass }}">
                        <i class="ri-bill-line"></i>
                        <span>{{ __('Product Orders') }}</span>
                    </a>
                </li>
                @if (\Illuminate\Support\Facades\Schema::hasTable('property_modules') &&
                        app(\App\Centresidence\Services\TokenPurchaseCollectionService::class)->hasUtilities(auth()->id()))
                    <li class="{{ @$navUtilitiesMMActiveClass }}">
                        <a href="{{ route('tenant.utilities.index') }}" class="{{ @$navUtilitiesActiveClass }}">
                            <i class="ri-flashlight-line"></i>
                            <span>{{ __('Utilities') }}</span>
                        </a>
                    </li>
                @endif
                @if (ownerCurrentPackage(auth()->user()->owner_user_id)?->ticket_support == ACTIVE || isAddonInstalled('PROTYSAAS') < 1)
                    <li class="{{ @$navTicketMMActiveClass }}">
                        <a href="{{ route('tenant.ticket.index') }}" class="{{ @$navTicketActiveClass }}">
                            <i class="ri-bookmark-2-line"></i>
                            <span>{{ __('My Tickets') }}</span>
                        </a>
                    </li>
                @endif
                <li>
                    <a href="{{ route('tenant.information.index') }}">
                        <i class="ri-folder-info-line"></i>
                        <span>{{ __('Information') }}</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('tenant.maintenance-request.index') }}">
                        <i class="ri-folder-info-line"></i>
                        <span>{{ __('Maintenance Request') }}</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('tenant.document.index') }}">
                        <i class="ri-article-line"></i>
                        <span>{{ __('Documents') }}@include('partials.nav-count', ['n' => $navBadges['documents'] ?? 0])</span>
                    </a>
                </li>
                @if (isAddonInstalled('PROTYAGREEMENT') > 0)
                    <li>
                        <a href="{{ route('tenant.agreement.index') }}">
                            <i class="ri-contacts-line"></i>
                            <span>{{ __('Agreement') }}@include('partials.nav-count', ['n' => $navBadges['agreements'] ?? 0])</span>
                        </a>
                    </li>
                @endif
                @endif {{-- /owner-bound surfaces (empty($ownerless)) --}}
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
                {{-- Support — reach our team (reusable support rail). --}}
                <li>
                    <a href="{{ route('support.index') }}">
                        <i class="ri-customer-service-2-line"></i>
                        <span>{{ __('Support') }}@include('partials.nav-count', ['n' => $navBadges['support'] ?? 0])</span>
                    </a>
                </li>
                {{-- Install app — app-like experience for tenants (auto-hides once installed). --}}
                <li>
                    <a href="#" data-cs-install onclick="return window.csPwaInstall ? (window.csPwaInstall(), false) : true;">
                        <i class="ri-smartphone-line"></i>
                        <span>{{ __('Install app') }}</span>
                    </a>
                </li>

            </ul>
        </div>
        <!-- Sidebar -->
    </div>
</div>
