<div class="vertical-menu">
    <div data-simplebar class="h-100">
        <!--- Sidemenu -->
        <div id="sidebar-menu">
            <!-- Left Menu Start -->
            <ul class="metismenu list-unstyled" id="side-menu">

                <li>
                    <a href="{{ route('owner.dashboard') }}">
                        <i class="ri-dashboard-line"></i>
                        <span>{{ __('Dashboard') }}</span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('owner.products.index') }}">
                        <i class="ri-store-2-line"></i>
                        <span>{{ __('My Shop') }}</span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('owner.order.index') }}" >
                        <i class="ri-bill-line"></i>
                        <span>{{ __('Product Orders') }}</span>
                    </a>
                </li>

                <li>
                    <a href="javascript: void(0);" class="has-arrow">
                        <i class="ri-building-line"></i>
                        <span>{{ __('Properties') }}</span>
                    </a>
                    <ul class="sub-menu {{ @$navPropertyMMShowClass }}" aria-expanded="false">
                        <li class="{{ @$subNavAllPropertyMMActiveClass }}">
                            <a href="{{ route('owner.property.allProperty') }}"
                                class="{{ @$subNavAllPropertyActiveClass }}">{{ __('All Property') }}</a>
                        </li>
                        <li class="{{ @$subNavAllUnitMMActiveClass }}">
                            <a href="{{ route('owner.property.allUnit') }}"
                                class="{{ @$subNavAllUnitActiveClass }}">{{ __('All Unit') }}</a>
                        </li>
                        <li class="{{ @$subNavOwnPropertyActiveClass }}">
                            <a href="{{ route('owner.property.ownProperty') }}"
                                class="{{ @$subNavOwnPropertyActiveClass }}">{{ __('Own Property') }}</a>
                        </li>
                        <li class="{{ @$subNavLeasePropertyActiveClass }}">
                            <a href="{{ route('owner.property.leaseProperty') }}"
                                class="{{ @$subNavLeasePropertyActiveClass }}">{{ __('Lease Property') }}</a>
                        </li>
                    </ul>
                </li>

                <li>
                    <a href="javascript: void(0);" class="has-arrow">
                        <i class="ri-user-3-line"></i>
                        <span>{{ __('Tenants') }}</span>
                    </a>
                    <ul class="sub-menu {{ @$navTenantMMShowClass }}" aria-expanded="false">
                        <li class="{{ @$subNavAllTenantMMActiveClass }}">
                            <a href="{{ route('owner.tenant.index', ['type' => 'all']) }}"
                                class="{{ @$subNavAllTenantActiveClass }}">{{ __('All Tenants') }}</a>
                        </li>
                        <li class="{{ @$subNavTenantHistoryMMActiveClass }}">
                            <a href="{{ route('owner.tenant.index', ['type' => 'history']) }}"
                                class="{{ @$subNavTenantHistoryActiveClass }}">{{ __('Tenant History') }}</a>
                        </li>
                        <li class="{{ @$subNavTenantHistoryMMActiveClass }}">
                            <a href="{{ route('owner.tenants.applications') }}"
                                class="{{ @$subNavTenantHistoryActiveClass }}">{{ __('Tenant Applications') }}</a>
                        </li>
                    </ul>
                </li>

                <li>
                    <a href="javascript: void(0);" class="has-arrow">
                        <i class="ri-wallet-line"></i>
                        <span>{{ __('Billing Center') }}</span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li class="">
                            <a href="{{ route('owner.invoice.index') }}" class="">{{ __('All Invoices') }}</a>
                        </li>
                        <li class="">
                            <a href="{{ route('owner.invoice.recurring-setting.index') }}"
                                class="">{{ __('Recurring Setting') }}</a>
                        </li>
                    </ul>
                </li>

                <li>
                    <a href="{{ route('owner.expense.index') }}">
                        <i class="ri-file-list-line"></i>
                        <span>{{ __('Expenses') }}</span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('owner.documents.index') }}">
                        <i class="ri-article-line"></i>
                        <span>{{ __('Documents') }}</span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('owner.information.index') }}">
                        <i class="ri-folder-info-line"></i>
                        <span>{{ __('Information') }}</span>
                    </a>
                </li>

                @if (isAddonInstalled('PROTYLISTING') > 0)
                    @if (getOption('LISTING_STATUS', 0) == ACTIVE)
                        <li>
                            <a href="javascript: void(0);" class="has-arrow">
                                <i class="ri-information-line"></i>
                                <span>{{ __('My Listing') }}</span>
                            </a>
                            <ul class="sub-menu" aria-expanded="false">
                                <li>
                                    <a href="{{ route('owner.listing.create') }}">{{ __('Upload List') }}</a>
                                </li>
                                <li>
                                    <a href="{{ route('owner.listing.index') }}">{{ __('All List') }}</a>
                                </li>
                                <li>
                                    <a href="{{ route('owner.listing.contact') }}">{{ __('Contact List') }}</a>
                                </li>
                            </ul>
                        </li>
                    @endif
                @endif

                <li>
                    <a href="javascript: void(0);" class="has-arrow">
                        <i class="ri-account-circle-line"></i>
                        <span>{{ __('Maintains') }}</span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li><a href="{{ route('owner.maintainer.index') }}">{{ __('Maintainers') }}</a></li>
                        <li><a
                                href="{{ route('owner.maintenance-request.index') }}">{{ __('Maintenance Request') }}</a>
                        </li>
                    </ul>
                </li>

                @if (isAddonInstalled('PROTYSAAS') < 1 || ownerCurrentPackage(auth()->id())?->ticket_support == ACTIVE)
                    <li>
                        <a href="{{ route('owner.ticket.index') }}">
                            <i class="ri-bookmark-2-line"></i>
                            <span>{{ __('Tickets') }}</span>
                        </a>
                    </li>
                @endif
                @if (isAddonInstalled('PROTYSAAS') < 1 || ownerCurrentPackage(auth()->id())?->notice_support == ACTIVE)
                    <li>
                        <a href="{{ route('owner.noticeboard.index') }}">
                            <i class="ri-artboard-line"></i>
                            <span>{{ __('Notice Board') }}</span>
                        </a>
                    </li>
                @endif

                <li>
                    <a href="javascript: void(0);" class="has-arrow">
                        <i class="ri-folder-chart-line"></i>
                        <span>{{ __('Report') }}</span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li>
                            <a href="{{ route('owner.reports.earning') }}">{{ __('Earning') }}</a>
                        </li>
                        <li>
                            <a
                                href="{{ route('owner.reports.loss-profit.by.month') }}">{{ __('Loss / Profit By Month') }}</a>
                        </li>
                        <li>
                            <a href="{{ route('owner.reports.expenses') }}">{{ __('Expenses') }}</a>
                        </li>
                        {{-- <li>
                            <a href="{{ route('owner.reports.lease') }}">{{ __('Lease') }}</a>
                        </li> --}}
                        <li>
                            <a href="{{ route('owner.reports.occupancy') }}">{{ __('Occupancy') }}</a>
                        </li>
                        <li>
                            <a href="{{ route('owner.reports.maintenance') }}">{{ __('Maintenance') }}</a>
                        </li>
                        <li>
                            <a href="{{ route('owner.reports.tenant') }}">{{ __('Tenant') }}</a>
                        </li>
                    </ul>
                </li>
                <li>
                    <a href="{{ route('owner.setting.gateway.index') }}">
                        <i class="ri-settings-3-line"></i>
                        <span>{{ __('Settings') }}</span>
                    </a>
                </li>

                @if (isAddonInstalled('PROTYSMS') > 0)
                    <li>
                        <a href="javascript: void(0);" class="has-arrow">
                            <i class="ri-mail-send-line"></i>
                            <span>{{ __('Mail') }}</span>
                        </a>
                        <ul class="sub-menu" aria-expanded="false">
                            
                            <li>
                                <a href="{{ route('sms-mail.mail') }}">{{ __('Email') }}</a>
                            </li>
                            @if (isAddonInstalled('PROTYSMS') > 2)
                                <li>
                                    <a href="{{ route('sms-mail.template.index') }}">{{ __('Email Template') }}</a>
                                </li>
                            @endif
                        </ul>
                    </li>
                @endif
                @if (isAddonInstalled('PROTYAGREEMENT') > 0)
                    <li>
                        <a href="{{ route('owner.agreement.index') }}">
                            <i class="ri-contacts-line"></i>
                            <span>{{ __('Agreement') }}</span>
                        </a>
                    </li>
                @endif
                @if (isAddonInstalled('PROTYTENANCY') > 0)
                    @if (env('TENANCY_STATUS') == ACTIVE)
                        <li>
                            <a href="{{ route('owner.domain.index') }}">
                                <i class="ri-cloud-line"></i>
                                <span>{{ __('Domain Config') }}</span>
                            </a>
                        </li>
                    @endif
                @endif
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
                @if (isAddonInstalled('PROTYSAAS') > 1)
                    <li>
                        <a href="{{ route('owner.subscription.index') }}">
                            <i class="ri-exchange-dollar-fill"></i>
                            <span>{{ __('My Subscription') }}</span>
                        </a>
                    </li>
                @endif
            </ul>
        </div>
        <!-- Sidebar -->
    </div>
</div>
