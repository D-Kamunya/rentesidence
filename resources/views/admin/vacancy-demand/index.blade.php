@extends('admin.layouts.app')

@php $pageTitle = __('Vacancy Demand'); @endphp

@section('content')
<div class="main-content">
  <div class="page-content">
    <div class="container-fluid">

      <div class="row">
        <div class="col-12">
          <div class="page-title-box d-sm-flex align-items-center justify-content-between border-bottom mb-20">
            <div class="page-title-left"><h3 class="mb-sm-0">{{ $pageTitle }}</h3></div>
            <div class="page-title-right">
              <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
                <li class="breadcrumb-item active">{{ $pageTitle }}</li>
              </ol>
            </div>
          </div>
        </div>
      </div>

      <div class="page-content-wrapper bg-white p-3 p-md-4 radius-20">
        <style>
          .vd-intro{color:#6B7280;font-size:14px;max-width:820px;line-height:1.6;margin:0 0 20px}
          .vd-stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:14px;margin-bottom:24px}
          .vd-stat{border:1px solid #E6E1D8;border-radius:14px;padding:16px 18px}
          .vd-stat b{display:block;font-size:26px;font-weight:800;color:#185FA5;line-height:1}
          .vd-stat span{display:block;font-size:12.5px;color:#6B7280;margin-top:6px;font-weight:600}
          .vd-table{width:100%;border-collapse:collapse}
          .vd-table th{text-align:left;font-size:11px;font-weight:800;letter-spacing:.06em;text-transform:uppercase;color:#9AA0A8;padding:10px 12px;border-bottom:1px solid #E6E1D8}
          .vd-table td{padding:12px;border-bottom:1px solid #F0EEE9;font-size:13.5px;color:#1B1E22;vertical-align:top}
          .vd-pill{font-size:11px;font-weight:700;border-radius:99px;padding:2px 9px;background:#E8F0F9;color:#185FA5}
          .vd-pill--conv{background:#E7F6EC;color:#166534}
          .vd-muted{color:#9AA0A8}
        </style>

        <p class="vd-intro">
          {{ __('Everyone who asked to be told when a home opens up — captured from the storefront “notify me” / House Watch. This is aggregated demand that owners with vacancies want; a strategic pool we can route or offer later.') }}
        </p>

        @if(is_null($rows))
          <div class="alert alert-info">{{ __('No captures yet — the table will populate once the latest update is deployed and prospects start using the storefront.') }}</div>
        @else
          <div class="vd-stats">
            <div class="vd-stat"><b>{{ $stats['total'] }}</b><span>{{ __('Total captured') }}</span></div>
            <div class="vd-stat"><b>{{ $stats['last_7'] }}</b><span>{{ __('Last 7 days') }}</span></div>
            <div class="vd-stat"><b>{{ $stats['with_area'] }}</b><span>{{ __('With an area of interest') }}</span></div>
            <div class="vd-stat"><b>{{ $stats['converted'] }}</b><span>{{ __('Became free tenants') }}</span></div>
          </div>

          <div class="table-responsive">
            <table class="vd-table">
              <thead>
                <tr>
                  <th>{{ __('When') }}</th>
                  <th>{{ __('Name') }}</th>
                  <th>{{ __('Contact') }}</th>
                  <th>{{ __('Area of interest') }}</th>
                  <th>{{ __('Looking at') }}</th>
                  <th>{{ __('Status') }}</th>
                </tr>
              </thead>
              <tbody>
                @forelse($rows as $r)
                  <tr>
                    <td class="vd-muted">{{ $r->created_at?->diffForHumans() }}</td>
                    <td>{{ $r->name ?: '—' }}</td>
                    <td>
                      @if($r->email)<div>{{ $r->email }}</div>@endif
                      @if($r->contact_number)<div class="vd-muted">{{ $r->contact_number }}</div>@endif
                      @if(! $r->email && ! $r->contact_number)—@endif
                    </td>
                    <td>{{ trim(($r->area_city ? $r->area_city : '') . ($r->area_city && $r->area_state ? ', ' : '') . ($r->area_state ?: '')) ?: '—' }}</td>
                    <td>
                      @if($r->scope_type === 'property')
                        <span class="vd-pill">{{ __('Property') }}</span> {{ optional($r->property)->name }}
                      @elseif($r->scope_type === 'owner')
                        <span class="vd-pill">{{ __('Owner') }}</span> {{ trim(optional($r->owner)->first_name . ' ' . optional($r->owner)->last_name) }}
                      @else
                        <span class="vd-pill">{{ __('General') }}</span>
                      @endif
                    </td>
                    <td>
                      @if($r->converted_user_id)<span class="vd-pill vd-pill--conv">{{ __('Signed up') }}</span>
                      @elseif($r->notified_at)<span class="vd-muted">{{ __('Notified') }} {{ $r->notified_at->diffForHumans() }}</span>
                      @else <span class="vd-muted">{{ __('Watching') }}</span>@endif
                    </td>
                  </tr>
                @empty
                  <tr><td colspan="6" class="vd-muted" style="padding:30px;text-align:center">{{ __('No captures yet.') }}</td></tr>
                @endforelse
              </tbody>
            </table>
          </div>

          <div class="mt-3">{{ $rows->links() }}</div>
        @endif
      </div>
    </div>
  </div>
</div>
@endsection
