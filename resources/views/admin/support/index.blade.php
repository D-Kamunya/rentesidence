@extends('admin.layouts.app')

@section('content')
@php
    $pillClass = ['open' => 'sup-pill--open', 'answered' => 'sup-pill--answered', 'resolved' => 'sup-pill--resolved', 'closed' => 'sup-pill--closed'];
    $roleLabel = [
        USER_ROLE_OWNER => __('Owner'), USER_ROLE_TENANT => __('Tenant'), USER_ROLE_MAINTAINER => __('Maintainer'),
        USER_ROLE_AFFILIATE => __('Affiliate'), USER_ROLE_FINANCE_PARTNER => __('Finance partner'),
    ];
    $roleSlug = [
        USER_ROLE_OWNER => 'owner', USER_ROLE_TENANT => 'tenant', USER_ROLE_MAINTAINER => 'maintainer',
        USER_ROLE_AFFILIATE => 'affiliate', USER_ROLE_FINANCE_PARTNER => 'finance',
    ];
    $tabs = ['' => __('All'), 'open' => __('Awaiting reply'), 'answered' => __('Replied'), 'resolved' => __('Resolved'), 'closed' => __('Closed')];
@endphp
<div class="main-content">
  <div class="page-content">
    <div class="container-fluid">
      <div class="page-content-wrapper p-30 radius-20" style="background:#f6f7f9;">

        <div class="sup-head">
          <div>
            <h1>{{ __('Support') }}</h1>
            <p>{{ __('Requests from owners, affiliates, finance partners and tenants.') }}</p>
          </div>
        </div>

        @if (session('success'))<div class="sup-flash sup-flash--ok">{{ session('success') }}</div>@endif
        @if (session('error'))<div class="sup-flash sup-flash--err">{{ session('error') }}</div>@endif

        {{-- Filter tabs --}}
        <div style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:18px;">
          @foreach ($tabs as $val => $label)
            @php $active = (string) $status === (string) $val; $badge = $val === 'open' ? ($counts['awaiting'] ?? 0) : ($counts[$val] ?? null); @endphp
            <a href="{{ route('admin.support.index', array_filter(['status' => $val])) }}"
               class="sup-btn {{ $active ? 'sup-btn--primary' : 'sup-btn--ghost' }}" style="padding:7px 14px;font-size:12.5px;">
              {{ $label }}@if ($val && ($counts[$val] ?? 0) > 0) <span style="opacity:.8;">({{ $counts[$val] }})</span>@endif
            </a>
          @endforeach
        </div>

        @if ($tickets->isEmpty())
          <div class="sup-empty">{{ __('No support tickets') }}{{ $status ? ' ' . __('in this state') : '' }}.</div>
        @else
          <div class="sup-list">
            @foreach ($tickets as $t)
              @php $slug = $roleSlug[$t->requester_role] ?? ''; @endphp
              <a href="{{ route('admin.support.show', $t->id) }}" class="sup-item sup-item--role {{ $slug ? 'sup-item--' . $slug : '' }}">
                <div class="sup-item__body">
                  <div class="sup-item__subject">
                    @if ($t->admin_unread && $t->status !== 'closed')<span class="sup-unread-dot" title="{{ __('Needs a reply') }}"></span>@endif
                    {{ $t->subject }}
                  </div>
                  <div class="sup-item__meta">
                    <span class="sup-role sup-role--{{ $slug ?: 'maintainer' }}">{{ $roleLabel[$t->requester_role] ?? __('User') }}</span>
                    {{ trim(optional($t->requester)->first_name . ' ' . optional($t->requester)->last_name) ?: __('User') }}
                    <span style="color:#c3c6cb;">·</span> {{ optional($t->last_reply_at)->diffForHumans() ?? $t->created_at->diffForHumans() }}
                  </div>
                </div>
                <span class="sup-pill {{ $pillClass[$t->status] ?? 'sup-pill--closed' }}">{{ ucfirst($t->status) }}</span>
                <span class="sup-item__go"><svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M9 6l6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
              </a>
            @endforeach
          </div>
          @if ($tickets->hasPages())<div class="mt-4">{{ $tickets->links() }}</div>@endif
        @endif

      </div>
    </div>
  </div>
</div>
@include('support._styles')
@endsection
