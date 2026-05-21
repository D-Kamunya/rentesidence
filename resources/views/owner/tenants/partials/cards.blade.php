@forelse ($tenants as $tenant)
    <div class="col-md-6 col-lg-6 col-xl-6 col-xxl-3 mb-4 single-tenant">
        <div class="ow-tenant-card">
            <div class="ow-tenant-header">
                <div class="d-flex align-items-center">
                    <div class="ow-tenant-avatar" style="background-image: url('{{ $tenant->image }}');"></div>
                    <div class="flex-grow-1 ms-3">
                        <h4 class="ow-tenant-name">{{ $tenant->first_name }} {{ $tenant->last_name }}</h4>
                        <p class="ow-tenant-email">{{ $tenant->email }}</p>
                    </div>
                    <a href="{{ route('owner.tenant.edit', $tenant->id) }}" class="ow-act ow-act--ghost" 
                        title="{{ __('Edit') }}">
                        <span class="iconify" data-icon="material-symbols:edit-square-outline"></span>
                    </a>
                </div>
            </div>

            <div class="ow-tenant-info">
                <div class="ow-info-row">
                    <span class="ow-info-label">{{ __('Contact No.') }}</span>
                    <a href="tel:{{ $tenant->contact_number }}" class="ow-info-value">{{ $tenant->contact_number }}</a>
                </div>
                <div class="ow-info-row">
                    <span class="ow-info-label">{{ __('Property') }}</span>
                    <span class="ow-info-value">{{ $tenant->property_name }}</span>
                </div>
                <div class="ow-info-row">
                    <span class="ow-info-label">{{ __('Unit') }}</span>
                    <span class="ow-info-value">{{ $tenant->unit_name }}</span>
                </div>
                <div class="ow-info-row">
                    <span class="ow-info-label">{{ __('Last Rent Paid') }}</span>
                    <span class="ow-info-value">{{ $tenant->last_payment ? date('Y-m-d', strtotime($tenant->last_payment)) : 'N/A' }}</span>
                </div>
                <div class="ow-info-row">
                    <span class="ow-info-label">{{ __('Current Rent') }}</span>
                    <span class="ow-info-value">{{ $tenant->general_rent }}</span>
                </div>
                <div class="ow-info-row">
                    <span class="ow-info-label">{{ __('Previous Due') }}</span>
                    @if ($tenant->due > 0)
                        <span class="ow-amt ow-amt--overdue">{{ currencyPrice($tenant->due) }}</span>
                    @else
                        <span class="ow-info-value">{{ currencyPrice(0) }}</span>
                    @endif
                </div>
                <div class="ow-info-row border-0">
                    <span class="ow-info-label">{{ __('Status') }}</span>
                    <div>
                        @if ($tenant->userStatus == USER_STATUS_DELETED)
                            <span class="ow-badge ow-badge--overdue">{{ __('Deleted') }}</span>
                        @else
                            @if ($tenant->status == TENANT_STATUS_ACTIVE)
                                <span class="ow-badge ow-badge--paid">{{ __('Active') }}</span>
                            @elseif($tenant->status == TENANT_STATUS_INACTIVE)
                                <span class="ow-badge ow-badge--overdue">{{ __('Inactive') }}</span>
                            @elseif($tenant->status == TENANT_STATUS_CLOSE)
                                <span class="ow-badge ow-badge--amber">{{ __('Close') }}</span>
                            @else
                                <span class="ow-badge ow-badge--blue">{{ __('Draft') }}</span>
                            @endif
                        @endif
                    </div>
                </div>
            </div>

            <div class="ow-tenant-footer">
                <a href="{{ route('owner.tenant.details', [$tenant->id, 'tab' => 'profile']) }}" 
                class="ow-btn ow-btn--primary w-100">
                    {{ __('View Details') }}
                </a>
            </div>
        </div>
    </div>
@empty
    <div class="col-12 text-center py-5">
        <img src="{{ asset('assets/images/empty-img.png') }}" alt="" class="img-fluid mb-3" style="max-height:160px;">
        <h4 class="ow-muted">{{ __('No tenants found') }}</h4>
    </div>
@endforelse