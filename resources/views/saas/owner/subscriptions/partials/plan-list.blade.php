@foreach ($plans as $plan)
    <div class="col-md-6 col-lg-4 mb-25">
        <div class="price-card-item h-100 p-30">
            <form class="ajax" action="{{ route('owner.subscription.order') }}" method="post"
                enctype="multipart/form-data" data-handler="setPaymentModal">
                <input type="hidden" name="id" value="{{ $plan->id }}">
                <input type="hidden" class="plan_type" name="duration_type" value="1">
                <input type="hidden" name="per_monthly_price" value="{{ $plan->per_monthly_price }}">
                <input type="hidden" name="per_yearly_price" value="{{ $plan->per_yearly_price }}">
                <h4 class="font-medium">{{ $plan->name }}</h4>
                <p class="font-13 pb-1 pt-1">{{ $plan->description }}</p>
                <hr>
                <h2 class="price-title price-monthly">{{ currencyPrice($plan->monthly_price) }}<span
                        class="font-13 font-medium">/{{ __('monthly') }}</span></h2>
                <h2 class="price-title d-none price-yearly">{{ currencyPrice($plan->yearly_price) }}<span
                        class="font-13 font-medium">/{{ __('yearly') }}</span></h2>
                <h4 class="font-18 font-medium mt-2">{{ __('What’s included') }}</h4>
                <ul class="pricing-features">
                    @if ($plan->type == PACKAGE_TYPE_PROPERTY)
                        <li class="d-flex align-items-center mb-3">
                            <span
                                class="price-check-icon d-inline-flex align-items-center justify-content-center status-btn-purple radius-50 me-2">
                                <span class="iconify font-16" data-icon="material-symbols:check-small-rounded"></span>
                            </span>
                            <span
                                class="font-13 price-monthly">{{ __('Add ') . currencyPrice($plan->per_monthly_price) . __(' Per Properties') }}</span>
                            <span
                                class="font-13 price-yearly d-none">{{ __('Add ') . currencyPrice($plan->per_yearly_price) . __(' Per Properties') }}</span>
                        </li>
                    @endif
                    @if ($plan->type == PACKAGE_TYPE_UNIT)
                        <li class="d-flex align-items-center mb-3">
                            <span
                                class="price-check-icon d-inline-flex align-items-center justify-content-center status-btn-purple radius-50 me-2">
                                <span class="iconify font-16" data-icon="material-symbols:check-small-rounded"></span>
                            </span>
                            <span
                                class="font-13 price-monthly">{{ __('Add Upto ') . $plan->max_unit . __(' Units') }}</span>
                        </li>
                    @endif
                    @if ($plan->type == PACKAGE_TYPE_TENANT)
                        <li class="d-flex align-items-center mb-3">
                            <span
                                class="price-check-icon d-inline-flex align-items-center justify-content-center status-btn-purple radius-50 me-2">
                                <span class="iconify font-16" data-icon="material-symbols:check-small-rounded"></span>
                            </span>
                            <span
                                class="font-13 price-monthly">{{ __('Add ') . currencyPrice($plan->per_monthly_price) . __(' Per Tenant') }}</span>
                            <span
                                class="font-13 price-yearly d-none">{{ __('Add ') . currencyPrice($plan->per_yearly_price) . __(' Per Tenant') }}</span>
                        </li>
                    @endif
                    <li class="d-flex align-items-center mb-3">
                        <span
                            class="price-check-icon d-inline-flex align-items-center justify-content-center status-btn-purple radius-50 me-2">
                            <span class="iconify font-16" data-icon="material-symbols:check-small-rounded"></span>
                        </span>
                        <span
                            class="font-13">{{ $plan->max_maintainer == -1 ? __('Add Unlimited Maintainers') : __('Add ' . $plan->max_maintainer . ' Maintainers') }}</span>
                    </li>
                    <li class="d-flex align-items-center mb-3">
                        <span
                            class="price-check-icon d-inline-flex align-items-center justify-content-center status-btn-purple radius-50 me-2">
                            <span class="iconify font-16" data-icon="material-symbols:check-small-rounded"></span>
                        </span>
                        <span
                            class="font-13">{{ $plan->max_invoice == -1 ? __('Unlimited Invoice Generate') : $plan->max_invoice . __(' Invoice Generate') }}</span>
                    </li>
                    <li class="d-flex align-items-center mb-3">
                        <span
                            class="price-check-icon d-inline-flex align-items-center justify-content-center status-btn-purple radius-50 me-2">
                            <span class="iconify font-16" data-icon="material-symbols:check-small-rounded"></span>
                        </span>
                        <span
                            class="font-13">{{ $plan->max_auto_invoice == -1 ? __('Unlimited Auto Invoice Generate') : $plan->max_auto_invoice . __(' Auto Invoice Generate') }}</span>
                    </li>
                    @if ($plan->ticket_support == ACTIVE)
                        <li class="d-flex align-items-center mb-3">
                            <span
                                class="price-check-icon d-inline-flex align-items-center justify-content-center status-btn-purple radius-50 me-2">
                                <span class="iconify font-16" data-icon="material-symbols:check-small-rounded"></span>
                            </span>
                            <span class="font-13">{{ __('Ticket Support') }}</span>
                        </li>
                    @endif
                    @if ($plan->notice_support == ACTIVE)
                        <li class="d-flex align-items-center mb-3">
                            <span
                                class="price-check-icon d-inline-flex align-items-center justify-content-center status-btn-purple radius-50 me-2">
                                <span class="iconify font-16" data-icon="material-symbols:check-small-rounded"></span>
                            </span>
                            <span class="font-13">{{ __('Notice Support') }}</span>
                        </li>
                    @endif
                </ul>

                @if ($plan->id == $currentPlan?->package_id)
                    <button type="submit"
                        class="theme-btn-outline mt-15 payment-gateway-active price-monthly {{ $currentPlan->duration_type == PACKAGE_DURATION_TYPE_MONTHLY ? 'current-plan-button' : 'd-none' }}"
                        {{ $currentPlan->duration_type == PACKAGE_DURATION_TYPE_MONTHLY ? 'disabled' : '' }}
                        title="{{ $currentPlan->duration_type == PACKAGE_DURATION_TYPE_MONTHLY ? __('Current Plan') : __('Subscribe Now') }}">
                        {{ $currentPlan->duration_type == PACKAGE_DURATION_TYPE_MONTHLY ? __('Current Plan') : __('Subscribe Now') }}
                    </button>
                    <button type="submit"
                        class="theme-btn-outline mt-15 payment-gateway-active price-yearly {{ $currentPlan->duration_type == PACKAGE_DURATION_TYPE_YEARLY ? 'current-plan-button' : 'd-none' }}"
                        {{ $currentPlan->duration_type == PACKAGE_DURATION_TYPE_YEARLY ? 'disabled' : '' }}
                        title="{{ $currentPlan->duration_type == PACKAGE_DURATION_TYPE_YEARLY ? __('Current Plan') : __('Subscribe Now') }}">
                        {{ $currentPlan->duration_type == PACKAGE_DURATION_TYPE_YEARLY ? __('Current Plan') : __('Subscribe Now') }}
                    </button>
                @else
                    <button type="submit" id="subscribeBtn" class="theme-btn-outline mt-15" title="{{ __('Subscribe Now') }}">
                        {{ __('Subscribe Now') }}
                    </button>
                @endif
            </form>
        </div>
    </div>
@endforeach
