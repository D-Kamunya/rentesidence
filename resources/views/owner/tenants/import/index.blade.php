@extends('owner.layouts.app')

@php $pageTitle = __('Import Tenants'); @endphp

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="page-content-wrapper bg-white p-30 radius-20">

                {{-- Header --}}
                <div class="ow-page-header mb-4">
                    <div>
                        <h2 class="ow-title">{{ $pageTitle }}</h2>
                        <nav aria-label="breadcrumb">
                            <ol class="ow-breadcrumb">
                                <li><a href="{{ route('owner.dashboard') }}">{{ __('Dashboard') }}</a></li>
                                <li><a href="{{ route('owner.tenant.index') }}">{{ __('Tenants') }}</a></li>
                                <li aria-current="page">
                                    <svg width="8" height="8" viewBox="0 0 16 16" fill="none"><path d="M6 4l4 4-4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    {{ __('Import') }}
                                </li>
                            </ol>
                        </nav>
                    </div>
                    <a href="{{ route('owner.tenant.import.template') }}" class="ti-btn ti-btn--ghost">
                        <i class="ri-download-2-line"></i> {{ __('Download CSV template') }}
                    </a>
                </div>

                @if (session('error'))
                    <div class="ti-alert ti-alert--error">{{ session('error') }}</div>
                @endif

                <div class="ti-grid">
                    {{-- Upload --}}
                    <div class="ti-card">
                        <div class="ti-card__head">
                            <span class="ti-card__ic"><i class="ri-upload-cloud-2-line"></i></span>
                            <div>
                                <h3 class="ti-card__title">{{ __('Upload your spreadsheet') }}</h3>
                                <p class="ti-card__sub">{{ __('Onboard many tenants and units at once. We\'ll check everything before anything is saved.') }}</p>
                            </div>
                        </div>
                        <div class="ti-card__body">
                            <ol class="ti-steps">
                                <li>{{ __('Download the CSV template and fill one row per tenant-in-a-unit.') }}</li>
                                <li>{{ __('Upload it here — we validate every row and show you a preview.') }}</li>
                                <li>{{ __('Review the preview, then confirm to import. Nothing is saved until you confirm.') }}</li>
                            </ol>

                            <form action="{{ route('owner.tenant.import.preview') }}" method="POST" enctype="multipart/form-data" class="ti-upload">
                                @csrf
                                <label class="ti-drop" id="tiDrop">
                                    <input type="file" name="file" accept=".csv,text/csv" required id="tiFile" hidden>
                                    <i class="ri-file-excel-2-line"></i>
                                    <span class="ti-drop__main" id="tiDropText">{{ __('Choose a .csv file') }}</span>
                                    <span class="ti-drop__hint">{{ __('Exported from Excel or Google Sheets · up to 10 MB') }}</span>
                                </label>
                                <button type="submit" class="ti-btn ti-btn--primary" id="tiSubmit">
                                    <i class="ri-search-eye-line"></i> {{ __('Validate & preview') }}
                                </button>
                            </form>
                        </div>
                    </div>

                    {{-- Column guide --}}
                    <div class="ti-card">
                        <div class="ti-card__head">
                            <span class="ti-card__ic"><i class="ri-list-check-2"></i></span>
                            <div>
                                <h3 class="ti-card__title">{{ __('Columns') }}</h3>
                                <p class="ti-card__sub">{{ __('Required columns are marked. Extra columns are ignored.') }}</p>
                            </div>
                        </div>
                        <div class="ti-card__body">
                            <div class="ti-cols">
                                @foreach ($columns as $key => $meta)
                                    <div class="ti-col">
                                        <div class="ti-col__name">
                                            {{ __($meta['label']) }}
                                            @if ($meta['required'])
                                                <span class="ti-req">{{ __('required') }}</span>
                                            @endif
                                        </div>
                                        <div class="ti-col__hint">{{ __($meta['hint']) }}</div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Recent imports --}}
                @if ($imports->count())
                    <div class="ti-card mt-4">
                        <div class="ti-card__head">
                            <span class="ti-card__ic"><i class="ri-history-line"></i></span>
                            <h3 class="ti-card__title">{{ __('Recent imports') }}</h3>
                        </div>
                        <div class="ti-card__body">
                            <div class="table-responsive">
                                <table class="ti-table">
                                    <thead>
                                        <tr>
                                            <th>{{ __('File') }}</th>
                                            <th>{{ __('When') }}</th>
                                            <th>{{ __('Rows') }}</th>
                                            <th>{{ __('Imported') }}</th>
                                            <th>{{ __('Skipped') }}</th>
                                            <th>{{ __('Status') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($imports as $imp)
                                            @php
                                                $problems = (int) $imp->error_rows + (int) $imp->skipped_count;
                                                $imported = (int) $imp->created_count + (int) $imp->updated_count;
                                            @endphp
                                            <tr>
                                                <td>{{ $imp->original_filename }}</td>
                                                <td>{{ $imp->created_at->diffForHumans() }}</td>
                                                <td>{{ number_format($imp->total_rows) }}</td>
                                                <td class="ti-ok">{{ number_format($imported) }}</td>
                                                <td class="{{ $problems ? 'ti-bad' : '' }}">
                                                    @if ($problems)
                                                        <a href="{{ route('owner.tenant.import.errors', $imp->id) }}" class="ti-errlink" title="{{ __('Download the list of skipped rows') }}">
                                                            {{ number_format($problems) }} <i class="ri-download-2-line"></i>
                                                        </a>
                                                    @else
                                                        0
                                                    @endif
                                                </td>
                                                <td><span class="ti-status ti-status--{{ $imp->status }}">{{ str_replace('_', ' ', $imp->status) }}</span></td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif

            </div>
        </div>
    </div>
</div>

<style>
    .ow-page-header { display:flex; align-items:flex-start; justify-content:space-between; gap:16px; flex-wrap:wrap; margin-bottom:4px; }
    .ow-title { font-size:22px; font-weight:500; color:#111827; margin:0 0 6px; }
    .ow-breadcrumb { list-style:none; display:flex; align-items:center; gap:6px; margin:0; padding:0; font-size:12px; color:#9ca3af; }
    .ow-breadcrumb a { color:#185FA5; text-decoration:none; font-weight:500; }
    .ow-breadcrumb li { display:flex; align-items:center; gap:6px; }
    .ti-grid { display:grid; grid-template-columns:1.2fr 1fr; gap:20px; }
    @media (max-width:900px){ .ti-grid { grid-template-columns:1fr; } }
    .ti-card { border:0.5px solid #e5e7eb; border-radius:14px; overflow:hidden; background:#fff; }
    .ti-card__head { display:flex; align-items:center; gap:12px; padding:16px 20px; border-bottom:0.5px solid #f0f2f5; background:#fafbfc; }
    .ti-card__ic { width:36px; height:36px; border-radius:9px; flex:none; display:inline-flex; align-items:center; justify-content:center; background:#E6F1FB; color:#185FA5; font-size:18px; }
    .ti-card__title { font-size:15px; font-weight:600; color:#111827; margin:0; }
    .ti-card__sub { font-size:12.5px; color:#6b7280; margin:2px 0 0; }
    .ti-card__body { padding:20px; }
    .ti-steps { margin:0 0 18px; padding-left:18px; color:#374151; font-size:13.5px; line-height:1.9; }
    .ti-upload { display:flex; flex-direction:column; gap:14px; }
    .ti-drop { display:flex; flex-direction:column; align-items:center; gap:6px; padding:26px; border:1.5px dashed #cdd5df; border-radius:12px; cursor:pointer; text-align:center; transition:all .15s; }
    .ti-drop:hover, .ti-drop.drag { border-color:#185FA5; background:#F5F9FE; }
    .ti-drop i { font-size:30px; color:#185FA5; }
    .ti-drop__main { font-size:14px; font-weight:600; color:#111827; }
    .ti-drop__hint { font-size:12px; color:#9ca3af; }
    .ti-cols { display:flex; flex-direction:column; gap:12px; max-height:420px; overflow:auto; }
    .ti-col__name { font-size:13px; font-weight:600; color:#111827; display:flex; align-items:center; gap:8px; }
    .ti-col__hint { font-size:12px; color:#6b7280; margin-top:1px; }
    .ti-req { font-size:10px; font-weight:600; text-transform:uppercase; letter-spacing:.04em; color:#B45309; background:#FEF3E7; border-radius:6px; padding:1px 7px; }
    .ti-btn { display:inline-flex; align-items:center; gap:7px; border-radius:9px; font-size:13.5px; font-weight:600; padding:10px 18px; cursor:pointer; border:0.5px solid transparent; text-decoration:none; }
    .ti-btn--primary { background:#185FA5; color:#fff; }
    .ti-btn--primary:hover { background:#0F4A84; }
    .ti-btn--ghost { background:#fff; color:#185FA5; border-color:#cdd5df; }
    .ti-btn--ghost:hover { border-color:#185FA5; }
    .ti-alert { padding:12px 16px; border-radius:10px; font-size:13.5px; margin-bottom:18px; }
    .ti-alert--error { background:#FAECE7; color:#993C1D; border:0.5px solid #F5C4B3; }
    .ti-table { width:100%; border-collapse:collapse; font-size:13px; }
    .ti-table th { text-align:left; font-size:11px; text-transform:uppercase; letter-spacing:.05em; color:#9ca3af; padding:8px 12px; border-bottom:0.5px solid #eef2f6; }
    .ti-table td { padding:10px 12px; border-bottom:0.5px solid #f5f7f9; color:#374151; }
    .ti-ok { color:#0F6E56; font-weight:600; }
    .ti-bad { color:#B45309; font-weight:600; }
    .ti-errlink { color:#B45309; font-weight:600; text-decoration:none; display:inline-flex; align-items:center; gap:4px; }
    .ti-errlink:hover { color:#92400E; text-decoration:underline; }
    .ti-status { font-size:11px; font-weight:600; padding:2px 9px; border-radius:99px; background:#eef2f6; color:#4b5563; text-transform:capitalize; }
    .ti-status--completed { background:#E1F5EE; color:#0F6E56; }
    .ti-status--completed_with_errors { background:#FEF3E7; color:#B45309; }
    .ti-status--failed { background:#FAECE7; color:#993C1D; }
    .ti-status--processing { background:#E6F1FB; color:#185FA5; }
</style>

<script>
(function () {
    var drop = document.getElementById('tiDrop');
    var file = document.getElementById('tiFile');
    var text = document.getElementById('tiDropText');
    if (!drop || !file) return;
    file.addEventListener('change', function () {
        text.textContent = file.files.length ? file.files[0].name : '{{ __('Choose a .csv file') }}';
    });
    ['dragover','dragenter'].forEach(function(e){ drop.addEventListener(e, function(ev){ ev.preventDefault(); drop.classList.add('drag'); }); });
    ['dragleave','drop'].forEach(function(e){ drop.addEventListener(e, function(ev){ ev.preventDefault(); drop.classList.remove('drag'); }); });
    drop.addEventListener('drop', function (ev) {
        if (ev.dataTransfer && ev.dataTransfer.files.length) { file.files = ev.dataTransfer.files; text.textContent = ev.dataTransfer.files[0].name; }
    });
})();
</script>
@endsection
