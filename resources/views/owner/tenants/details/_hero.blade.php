{{-- Tenant rail hero — robust across both data shapes: getDetailsById (flat
     first_name/property_name…) used by profile/home, and getById (raw model w/
     user/property/unit relations) used by payment/document. --}}
@php
    $hName = trim(($tenant->first_name ?? optional($tenant->user)->first_name ?? '') . ' ' . ($tenant->last_name ?? optional($tenant->user)->last_name ?? ''));
    $hProp = $tenant->property_name ?? optional($tenant->property)->name;
    $hUnit = $tenant->unit_name ?? optional($tenant->unit)->unit_name;
@endphp
<div class="td-rail__hero">
    <img src="{{ $tenant->image }}" class="td-rail__img" alt="">
    <p class="td-rail__name">{{ $hName ?: __('Tenant') }}</p>
    <p class="td-rail__meta">{{ $hProp }}@if($hUnit) · {{ $hUnit }}@endif</p>
    <span class="td-badge {{ $tenant->status == TENANT_STATUS_CLOSE ? 'td-badge--closed' : ($tenant->status == TENANT_STATUS_ACTIVE ? 'td-badge--active' : 'td-badge--grey') }}">
        {{ $tenant->status == TENANT_STATUS_CLOSE ? __('Closed') : ($tenant->status == TENANT_STATUS_ACTIVE ? __('Active') : __('Pending')) }}
    </span>
</div>
