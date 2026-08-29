@extends('owner.layouts.app')

@section('content')
@php $template = $templates->first(); @endphp
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="page-content-wrapper bg-white p-30 radius-20">
                <div class="container" style="max-width:820px;">

                    <div class="ag-header mb-4">
                        <div>
                            <h2 class="ag-title">{{ $pageTitle }}</h2>
                            <p class="ag-sub">{{ __('Edit your reusable agreement. New agreements use it; already-sent ones are unchanged.') }}</p>
                        </div>
                        <a href="{{ route('owner.agreement.index') }}" class="ag-btn ag-btn--ghost">{{ __('Back') }}</a>
                    </div>

                    @if ($template)
                        @php $isUpload = $template->source === 'upload'; @endphp
                        <form action="{{ route('owner.agreement.template.update', $template->id) }}" method="POST" enctype="multipart/form-data" class="ag-card" style="padding:20px;">
                            @csrf
                            <div class="mb-3">
                                <label class="ag-label">{{ __('Template name') }}</label>
                                <input type="text" name="name" class="ag-input" value="{{ $template->name }}" required>
                            </div>

                            {{-- Source mode --}}
                            <div class="mb-3">
                                <div class="ag-sig-tabs" id="tplModeTabs">
                                    <button type="button" class="ag-sig-tab {{ $isUpload ? '' : 'is-active' }}" data-mode="template">{{ __('Text template') }}</button>
                                    <button type="button" class="ag-sig-tab {{ $isUpload ? 'is-active' : '' }}" data-mode="upload">{{ __('Upload PDF') }}</button>
                                </div>
                                <input type="hidden" name="source" id="tplSource" value="{{ $isUpload ? 'upload' : 'template' }}">
                            </div>

                            {{-- Text template --}}
                            <div id="tplTextBlock" class="mb-1" style="{{ $isUpload ? 'display:none;' : '' }}">
                                <label class="ag-label">{{ __('Agreement body') }}</label>
                                <textarea name="body" rows="18" class="ag-input" style="max-width:100%;font-family:monospace;font-size:12.5px;line-height:1.6;">{{ $template->body }}</textarea>
                                <p class="ag-hint">
                                    {{ __('Basic HTML is supported. Use these placeholders — they autofill per tenant:') }}
                                    <code>@{{owner_name}}</code>, <code>@{{tenant_name}}</code>,
                                    <code>@{{property_name}}</code>, <code>@{{unit_name}}</code>,
                                    <code>@{{rent_amount}}</code>, <code>@{{deposit_amount}}</code>,
                                    <code>@{{lease_start}}</code>, <code>@{{today}}</code>.
                                </p>
                            </div>

                            {{-- Upload PDF --}}
                            <div id="tplUploadBlock" class="mb-1" style="{{ $isUpload ? '' : 'display:none;' }}">

                                {{-- Current uploaded PDF --}}
                                @if ($template->original_file_id)
                                    <div class="agc-pdf-current mb-3">
                                        <div class="agc-pdf-head">
                                            <span class="agc-pdf-icon">
                                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/><path d="M14 2v6h6" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/></svg>
                                            </span>
                                            <span class="agc-pdf-name">{{ __('A PDF agreement is uploaded') }}</span>
                                            <a href="{{ route('owner.agreement.template.document', $template->id) }}" target="_blank" class="ag-link" style="margin-left:auto;">{{ __('Open') }}</a>
                                        </div>
                                        <iframe class="agc-pdf-preview" src="{{ route('owner.agreement.template.document', $template->id) }}#toolbar=0&view=FitH" title="{{ __('Current agreement PDF') }}"></iframe>
                                    </div>
                                @endif

                                <label class="ag-label">{{ $template->original_file_id ? __('Replace PDF') : __('Agreement PDF') }}</label>
                                <input type="file" name="pdf" accept="application/pdf" class="ag-input" style="padding:8px;">
                                <p class="ag-hint">
                                    {{ __('Upload your own agreement (PDF, max 10MB). It stays exactly as provided; tenants review and e-sign it, and a signature certificate is generated alongside.') }}
                                    @if ($template->original_file_id)
                                        {{ __('Choose a file only to replace the current one.') }}
                                    @endif
                                </p>
                            </div>

                            <div class="mt-2">
                                <button type="submit" class="ag-btn ag-btn--primary">{{ __('Save Template') }}</button>
                            </div>
                        </form>

                        <script>
                        (function () {
                            var srcEl = document.getElementById('tplSource');
                            var textB = document.getElementById('tplTextBlock');
                            var upB   = document.getElementById('tplUploadBlock');
                            document.querySelectorAll('#tplModeTabs .ag-sig-tab').forEach(function (t) {
                                t.addEventListener('click', function () {
                                    document.querySelectorAll('#tplModeTabs .ag-sig-tab').forEach(function (x){ x.classList.remove('is-active'); });
                                    t.classList.add('is-active');
                                    var mode = t.getAttribute('data-mode');
                                    srcEl.value = mode;
                                    textB.style.display = mode === 'template' ? '' : 'none';
                                    upB.style.display   = mode === 'upload'   ? '' : 'none';
                                });
                            });
                        })();
                        </script>
                    @else
                        <div class="ag-card"><p class="ag-empty">{{ __('No template found.') }}</p></div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('style')
    @include('agreement.partials.styles')
    <style>
        .agc-pdf-current { border:0.5px solid #e5e7eb; border-radius:10px; overflow:hidden; background:#fff; }
        .agc-pdf-head { display:flex; align-items:center; gap:10px; padding:10px 14px; background:#fafafa; border-bottom:0.5px solid #e5e7eb; font-size:13px; color:#111827; }
        .agc-pdf-icon { width:30px; height:30px; border-radius:8px; background:#E6F1FB; color:#185FA5; display:flex; align-items:center; justify-content:center; flex:none; }
        .agc-pdf-name { font-weight:500; }
        .agc-pdf-preview { width:100%; height:340px; border:none; display:block; background:#fff; }
    </style>
@endpush
