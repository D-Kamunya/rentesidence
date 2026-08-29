@extends('maintainer.layouts.app')

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="page-content-wrapper bg-white p-30 radius-20">

                <div class="in-head">
                    <nav aria-label="breadcrumb">
                        <ol class="in-crumb">
                            <li><a href="{{ route('maintainer.dashboard') }}">{{ __('Dashboard') }}</a></li>
                            <li aria-current="page">{{ $pageTitle }}</li>
                        </ol>
                    </nav>
                    <h2 class="in-title">{{ __('Information') }}</h2>
                    <p class="in-sub">{{ __('Useful places and contacts around the properties you manage.') }}</p>
                </div>

                <div class="in-grid">
                    @forelse ($information as $info)
                        <div class="in-card">
                            <div class="in-card__img" style="background-image:url('{{ $info->image }}');"></div>
                            <div class="in-card__body">
                                <h4 class="in-card__name">{{ $info->name }}</h4>
                                <p class="in-card__desc">{{ Str::limit($info->additional_information, 60, '…') }}</p>
                                <div class="in-meta">
                                    <span><i class="ri-map-pin-line"></i> {{ $info->distance ?: '—' }}</span>
                                    <span><i class="ri-phone-line"></i> {{ $info->contact_number ?: '—' }}</span>
                                </div>
                                <button type="button" class="in-btn view" data-id="{{ $info->id }}"
                                        data-bs-toggle="modal" data-bs-target="#viewTenantInformationModal">
                                    {{ __('View details') }}
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="in-empty">
                            <i class="ri-information-line"></i>
                            <h3>{{ __('No information yet') }}</h3>
                            <p>{{ __('Nearby places and contacts added by the owner will appear here.') }}</p>
                        </div>
                    @endforelse
                </div>

            </div>
        </div>
    </div>
</div>

{{-- View modal (populated by information-view.js) --}}
<div class="modal fade" id="viewTenantInformationModal" tabindex="-1" aria-labelledby="viewTenantInformationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="viewTenantInformationModalLabel">{{ __('Information') }}</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span class="iconify" data-icon="akar-icons:cross"></span></button>
            </div>
            <div class="modal-body">
                <div class="view-information-page-modal-content">
                    <div class="information-details-img radius-4 mb-25" style="border-radius:12px;overflow:hidden;"><img class="fit-image image" style="width:100%;border-radius:12px;"></div>
                    <div class="in-modal-row"><span class="in-modal-k">{{ __('Name') }}</span><span class="name"></span></div>
                    <div class="in-modal-row"><span class="in-modal-k">{{ __('Property') }}</span><span class="property"></span></div>
                    <div class="in-modal-row"><span class="in-modal-k">{{ __('Distance') }}</span><span class="distance"></span></div>
                    <div class="in-modal-row"><span class="in-modal-k">{{ __('Contact Number') }}</span><span class="contact_number"></span></div>
                    <div class="in-modal-row in-modal-row--col"><span class="in-modal-k">{{ __('Additional Information') }}</span><span class="additional_information"></span></div>
                </div>
            </div>
        </div>
    </div>
</div>
<input type="hidden" id="getInfoRoute" value="{{ route('maintainer.information.get.info') }}">

<style>
    .in-head { margin-bottom:20px; }
    .in-crumb { display:flex; gap:6px; list-style:none; padding:0; margin:0 0 8px; font-size:12px; color:#9ca3af; }
    .in-crumb li:not(:last-child)::after { content:''; display:inline-block; width:5px; height:5px; border-right:1.5px solid #d1d5db; border-top:1.5px solid #d1d5db; transform:rotate(45deg); margin-left:6px; opacity:.6; }
    .in-crumb a { color:#185FA5; text-decoration:none; font-weight:500; }
    .in-title { font-size:22px; font-weight:600; color:#111827; margin:0 0 5px; }
    .in-sub { font-size:13.5px; color:#6b7280; margin:0; }
    .in-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(240px, 1fr)); gap:18px; }
    .in-card { border:0.5px solid #e5e7eb; border-radius:16px; overflow:hidden; }
    .in-card__img { height:130px; background:#f3f4f6 center/cover no-repeat; }
    .in-card__body { padding:16px; }
    .in-card__name { font-size:15px; font-weight:600; color:#111827; margin:0 0 5px; }
    .in-card__desc { font-size:12.5px; color:#6b7280; line-height:1.5; margin:0 0 12px; }
    .in-meta { display:flex; flex-direction:column; gap:5px; font-size:12.5px; color:#374151; margin-bottom:14px; }
    .in-meta i { color:#185FA5; margin-right:4px; }
    .in-btn { display:block; width:100%; text-align:center; background:#185FA5; color:#fff; border:none; border-radius:10px; font-size:13px; font-weight:600; padding:9px; cursor:pointer; }
    .in-btn:hover { background:#0F4A84; }
    .in-empty { grid-column:1/-1; text-align:center; padding:48px 20px; color:#6b7280; }
    .in-empty i { font-size:44px; color:#cbd5e1; }
    .in-empty h3 { font-size:17px; color:#111827; margin:12px 0 6px; }
    .in-modal-row { display:flex; justify-content:space-between; gap:14px; padding:10px 0; border-bottom:0.5px solid #f1f5f9; font-size:13.5px; color:#374151; }
    .in-modal-row--col { flex-direction:column; gap:4px; }
    .in-modal-k { font-size:12px; color:#9ca3af; font-weight:600; }
</style>
@endsection

@push('script')
    <script src="{{ asset('assets/js/custom/information-view.js') }}"></script>
@endpush
