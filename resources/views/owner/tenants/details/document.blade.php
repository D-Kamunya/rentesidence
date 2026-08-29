@extends('owner.layouts.app')

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="page-content-wrapper bg-white p-30 radius-20">

                <div class="td-header">
                    <div>
                        <h2 class="td-title">{{ $pageTitle }}</h2>
                        <ol class="td-crumb">
                            <li><a href="{{ route('owner.dashboard') }}">{{ __('Dashboard') }}</a></li>
                            <li>›</li>
                            <li><a href="{{ route('owner.tenant.index') }}">{{ __('Tenants') }}</a></li>
                            <li>›</li>
                            <li>{{ __('Documents') }}</li>
                        </ol>
                    </div>
                </div>

                <div class="td-layout">
                    <aside class="td-rail">
                        @include('owner.tenants.details._hero')
                        @include('owner.tenants.details.sidenav')
                    </aside>

                    <div class="td-content">
                        {{-- Requested from THIS tenant (per-tenant requests, distinct from the tenant-wide policy in Settings) --}}
                        <div class="td-card">
                            <div class="td-card__head">
                                <span class="td-card__ic"><i class="ri-file-list-3-line"></i></span>
                                <h3 class="td-card__title">{{ __('Requested Documents') }}</h3>
                                <button type="button" class="td-card__action" data-bs-toggle="modal" data-bs-target="#requestDocModal" style="margin-left:auto;background:none;border:none;cursor:pointer;">
                                    <i class="ri-add-line"></i> {{ __('Request a Document') }}
                                </button>
                            </div>
                            <div class="td-card__body" style="padding:6px 18px 14px;">
                                @php
                                    $statusMap = [
                                        KYC_STATUS_ACCEPTED => ['label' => __('Accepted'), 'cls' => 'td-badge--active'],
                                        KYC_STATUS_PENDING  => ['label' => __('Pending review'), 'cls' => 'td-badge--grey'],
                                        KYC_STATUS_REJECTED => ['label' => __('Rejected'), 'cls' => 'td-badge--closed'],
                                    ];
                                @endphp
                                @forelse ($requests as $req)
                                    <div class="td-req">
                                        <span class="td-req__ic"><i class="ri-file-text-line"></i></span>
                                        <div class="td-req__body">
                                            <p class="td-req__name">{{ $req->name }}</p>
                                            @if ($req->details)<p class="td-req__detail">{{ $req->details }}</p>@endif
                                            @if ($req->verification_status === KYC_STATUS_REJECTED && $req->reject_reason)
                                                <p class="td-req__detail" style="color:#A32D2D;">{{ __('Reason') }}: {{ $req->reject_reason }}</p>
                                            @endif
                                        </div>
                                        @if ($req->verification_id)
                                            <span class="td-req__thumbs">
                                                {!! app(\App\Services\KycVerificationService::class)->docThumb($req->front) !!}
                                                @if ($req->back) {!! app(\App\Services\KycVerificationService::class)->docThumb($req->back) !!} @endif
                                            </span>
                                        @endif
                                        @if (is_null($req->verification_status))
                                            <span class="td-badge td-badge--amber">{{ __('Awaiting upload') }}</span>
                                        @elseif ($req->verification_status === KYC_STATUS_PENDING)
                                            <span class="td-req__review">
                                                <a href="javascript:void(0)" class="td-req__act td-req__act--ok reviewAccept"
                                                   data-url="{{ route('owner.documents.status', $req->verification_id) }}"><i class="ri-check-line"></i> {{ __('Accept') }}</a>
                                                <a href="javascript:void(0)" class="td-req__act td-req__act--no reviewReject"
                                                   data-id="{{ $req->verification_id }}"><i class="ri-close-line"></i> {{ __('Reject') }}</a>
                                            </span>
                                        @else
                                            @php $s = $statusMap[$req->verification_status] ?? ['label' => __('Uploaded'), 'cls' => 'td-badge--grey']; @endphp
                                            <span class="td-badge {{ $s['cls'] }}">{{ $s['label'] }}</span>
                                        @endif
                                    </div>
                                @empty
                                    <div class="td-empty" style="padding:28px 20px;">
                                        <i class="ri-file-list-3-line"></i>
                                        <p>{{ __('No specific documents requested from this tenant yet.') }}</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>

                        {{-- Uploaded documents --}}
                        <div class="td-card">
                            <div class="td-card__head">
                                <span class="td-card__ic"><i class="ri-folder-2-line"></i></span>
                                <h3 class="td-card__title">{{ __('Uploaded Documents') }}</h3>
                            </div>
                            <div class="td-card__body" style="padding:14px 18px 18px;">
                                @forelse ($tenant->documents as $document)
                                    <div class="td-doc">
                                        {!! app(\App\Services\KycVerificationService::class)->docThumb($document->FileUrl) !!}
                                        <span class="td-doc__name">{{ $document->file_name }}</span>
                                        <span class="td-doc__actions">
                                            <a href="{{ $document->FileUrl }}" target="_blank" rel="noopener" class="td-doc__dl" title="{{ __('View') }}">
                                                <i class="ri-eye-line"></i> {{ __('View') }}
                                            </a>
                                            <a href="{{ $document->FileUrl }}" class="td-doc__dl td-doc__dl--muted" title="{{ __('Download') }}" download>
                                                <i class="ri-download-2-line"></i> {{ __('Download') }}
                                            </a>
                                        </span>
                                    </div>
                                @empty
                                    <div class="td-empty">
                                        <i class="ri-folder-open-line"></i>
                                        <p>{{ __('No documents uploaded for this tenant.') }}</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

{{-- Request a document from this specific tenant (creates a tenant-scoped config) --}}
<div class="modal fade" id="requestDocModal" tabindex="-1" aria-labelledby="requestDocModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content pf-modal">
            <div class="modal-header pf-modal__head">
                <h4 class="modal-title" id="requestDocModalLabel">{{ __('Request a Document') }}</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="requestDocForm" class="ajax" action="{{ route('owner.setting.document-config.store') }}" method="POST" data-handler="getShowMessage">
                @csrf
                <input type="hidden" name="tenant_id" value="{{ $tenant->id }}">
                <input type="hidden" name="status" value="{{ ACTIVE }}">
                <div class="modal-body">
                    <p class="pf-modal__warn">{{ __('This request goes only to') }} <span class="fw-bold">{{ optional($tenant->user)->first_name ?? __('this tenant') }}</span>. {{ __('It appears on their documents page for upload.') }}</p>
                    <div class="pf-field mb-3">
                        <label class="pf-label">{{ __('Document Name') }}</label>
                        <input type="text" name="name" class="pf-input" placeholder="{{ __('e.g. Signed Lease Addendum') }}" required>
                    </div>
                    <div class="pf-field mb-3">
                        <label class="pf-label">{{ __('Details / Instructions') }}</label>
                        <textarea name="details" class="pf-input" rows="3" placeholder="{{ __('What exactly should the tenant provide?') }}" required></textarea>
                    </div>
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13px;color:#374151;">
                        <input type="checkbox" name="is_both" value="on"> {{ __('Requires both a front and back side') }}
                    </label>
                </div>
                <div class="modal-footer">
                    <button type="button" class="pf-btn pf-btn--ghost" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="pf-btn pf-btn--primary">{{ __('Send Request') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Reject a submitted document (reason required) — posts to the same endpoint the KYC page uses --}}
<div class="modal fade" id="rejectReqModal" tabindex="-1" aria-labelledby="rejectReqModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content pf-modal">
            <div class="modal-header pf-modal__head">
                <h4 class="modal-title" id="rejectReqModalLabel">{{ __('Reject Document') }}</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form class="ajax" action="{{ route('owner.documents.reject.reason.store') }}" method="POST" data-handler="getShowMessage" id="rejectReqForm">
                @csrf
                <input type="hidden" name="id" class="rejectReqId">
                <div class="modal-body">
                    <div class="pf-field">
                        <label class="pf-label">{{ __('Reason for rejection') }}</label>
                        <textarea name="reason" class="pf-input" rows="3" placeholder="{{ __('Tell the tenant what to fix') }}" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="pf-btn pf-btn--ghost" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="pf-btn pf-btn--danger">{{ __('Reject') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .td-doc { display:flex; align-items:center; gap:14px; padding:12px 0; border-bottom:0.5px solid #f3f4f6; }
    .td-doc:last-child { border-bottom:none; }
    .td-doc__name { flex:1; min-width:0; font-size:13.5px; color:#374151; word-break:break-word; }
    .td-doc__actions { display:inline-flex; align-items:center; gap:14px; flex:none; }
    .td-doc__dl { display:inline-flex; align-items:center; gap:5px; font-size:12.5px; font-weight:600; color:#185FA5; text-decoration:none; white-space:nowrap; }
    .td-doc__dl:hover { color:#0F4A84; }
    .td-doc__dl--muted { color:#6b7280; }
    .td-doc__dl--muted:hover { color:#374151; }

    .td-req { display:flex; align-items:center; gap:12px; padding:12px 0; border-bottom:0.5px solid #f3f4f6; }
    .td-req:last-child { border-bottom:none; }
    .td-req__ic { width:34px; height:34px; border-radius:9px; flex:none; display:inline-flex; align-items:center; justify-content:center; background:#E6F1FB; color:#185FA5; font-size:16px; }
    .td-req__body { flex:1; min-width:0; }
    .td-req__name { font-size:13.5px; font-weight:600; color:#111827; margin:0; }
    .td-req__detail { font-size:12px; color:#9ca3af; margin:2px 0 0; }
    .td-req__thumbs { display:inline-flex; gap:6px; flex:none; }
    .td-req__thumbs .doc-thumb { width:40px; height:40px; }
    .td-req__review { display:inline-flex; gap:8px; flex:none; }
    .td-req__act { display:inline-flex; align-items:center; gap:4px; font-size:12px; font-weight:600; padding:5px 11px; border-radius:7px; text-decoration:none; white-space:nowrap; cursor:pointer; }
    .td-req__act--ok { background:#E1F5EE; color:#0F6E56; } .td-req__act--ok:hover { background:#0F6E56; color:#fff; }
    .td-req__act--no { background:#FAECE7; color:#A32D2D; } .td-req__act--no:hover { background:#A32D2D; color:#fff; }
    .td-badge { display:inline-flex; align-items:center; font-size:11px; font-weight:600; padding:3px 10px; border-radius:99px; white-space:nowrap; }
    .td-badge--active { background:#E1F5EE; color:#0F6E56; }
    .td-badge--closed { background:#FAECE7; color:#A32D2D; }
    .td-badge--grey   { background:#f3f4f6; color:#6b7280; }
    .td-badge--amber  { background:#FDF6EC; color:#854F0B; }

    .td-empty { text-align:center; padding:44px 20px; color:#9ca3af; }
    .td-empty i { font-size:38px; color:#C9A24B; }
    .td-empty p { margin:12px 0 0; font-size:13.5px; }
</style>

@push('script')
<script>
    // Request-a-document: refresh so the new request shows in the list after the ajax create.
    document.getElementById('requestDocForm').addEventListener('submit', function () {
        setTimeout(function () { location.reload(); }, 1500);
    });

    // Inline review — Accept (ajax GET to the existing KYC accept endpoint, then refresh).
    $(document).on('click', '.reviewAccept', function () {
        var url = $(this).data('url');
        csConfirm({ title: "{{ __('Accept this document?') }}", message: "{{ __('This marks the document as verified.') }}", confirmText: "{{ __('Accept') }}" })
            .then(function (ok) { if (ok) $.get(url).always(function () { location.reload(); }); });
    });

    // Inline review — Reject: open the reason modal with the verification id.
    $(document).on('click', '.reviewReject', function () {
        $('.rejectReqId').val($(this).data('id'));
        (new bootstrap.Modal(document.getElementById('rejectReqModal'))).show();
    });
    document.getElementById('rejectReqForm').addEventListener('submit', function () {
        setTimeout(function () { location.reload(); }, 1500);
    });
</script>
@endpush
@endsection
