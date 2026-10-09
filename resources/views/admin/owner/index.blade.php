@extends('admin.layouts.app')

@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="page-content-wrapper bg-white p-30 radius-20 cs-controls">
                    @include('centresidence._design')

                    <div class="row">
                        <div class="col-12">
                            <div
                                class="page-title-box d-sm-flex align-items-center justify-content-between border-bottom mb-20">
                                <div class="page-title-left">
                                    <h3 class="mb-sm-0">{{ $pageTitle }}</h3>
                                </div>
                                <div class="page-title-right">
                                    <ol class="breadcrumb mb-0">
                                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"
                                                title="{{ __('Dashboard') }}">{{ __('Dashboard') }}</a></li>
                                        <li class="breadcrumb-item active" aria-current="page">{{ $pageTitle }}</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xl-12 col-xxl-6">
                            <div class="row justify-content-end">
                                <div class="col-auto mb-25">
                                    <a href="{{ route('admin.owner.register.form') }}" class="theme-btn w-auto"
                                        title="{{ __('Add New Owner') }}">{{ __('Add New Owner') }}</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="billing-center-area cs-card cs-card--pad">
                            <table id="allOwnerDataTable" class="table responsive theme-border p-20 ">
                                <thead>
                                    <th>{{ __('SL') }}</th>
                                    <th data-priority="1">{{ __('Name') }}</th>
                                    <th>{{ __('Email') }}</th>
                                    <th>{{ __('Contact Number') }}</th>
                                    <th>{{ __('Affiliate') }}</th>
                                    <th>{{ __('Status') }}</th>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <input type="hidden" id="adminOwnerRoute" value="{{ route('admin.owner.index') }}">

    {{-- Assign-affiliate modal (shared; opened by the affiliate column buttons) --}}
    <div id="assignAffModal" class="aff-modal" role="dialog" aria-modal="true" aria-labelledby="affModalTitle">
        <div class="aff-modal__card">
            <h4 id="affModalTitle" class="aff-modal__title">{{ __('Affiliate for') }} <span id="affOwnerName"></span></h4>
            <p class="aff-modal__lead">{{ __('Choose who earns commission on this owner\'s activity. This takes effect going forward — past periods are not backfilled.') }}</p>
            <form id="assignAffForm" method="POST" action="">
                @csrf
                <label class="aff-modal__label">{{ __('Affiliate') }}</label>
                <select name="affiliate_id" id="affSelect" class="form-control">
                    <option value="">{{ __('— None (no affiliate) —') }}</option>
                    @foreach ($affiliates as $a)
                        <option value="{{ $a->id }}">{{ $a->name }}@if($a->email) — {{ $a->email }}@endif</option>
                    @endforeach
                </select>
                <div id="affWarn" class="aff-modal__warn" hidden></div>
                <div class="aff-modal__actions">
                    <button type="button" class="btn aff-modal__cancel" data-aff-cancel>{{ __('Cancel') }}</button>
                    <button type="submit" class="btn aff-modal__save">{{ __('Save') }}</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('style')
    @include('common.layouts.datatable-style')
@endpush

@push('style')
<style>
    .aff-modal{position:fixed;inset:0;z-index:1300;background:rgba(17,24,34,.3);backdrop-filter:blur(4px);
        -webkit-backdrop-filter:blur(4px);display:none;align-items:center;justify-content:center;padding:20px;}
    .aff-modal.open{display:flex;}
    .aff-modal__card{background:#fff;border-radius:16px;max-width:460px;width:100%;padding:24px 24px 20px;
        box-shadow:0 18px 52px rgba(20,23,28,.22);}
    .aff-modal__title{font-size:17px;font-weight:700;color:#1b1e22;margin:0 0 6px;}
    .aff-modal__title span{color:#185FA5;}
    .aff-modal__lead{font-size:13px;color:#6b7280;line-height:1.5;margin:0 0 16px;}
    .aff-modal__label{font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:#9ca3af;margin-bottom:6px;display:block;}
    .aff-modal__warn{background:#FCF3E2;border:1px solid #EFD8A8;color:#854F0B;border-radius:9px;
        padding:9px 12px;font-size:12.5px;line-height:1.5;margin-top:12px;}
    .aff-modal__actions{display:flex;align-items:center;gap:10px;margin-top:18px;}
    .aff-modal__save{background:#185FA5;color:#fff;border:1px solid #185FA5;border-radius:8px;
        padding:10px 22px;font-size:14px;font-weight:500;cursor:pointer;}
    .aff-modal__save:hover{background:#0F4A84;}
    .aff-modal__cancel{background:#F4F5F7;border:1px solid #D9DEE6;color:#4B5563;border-radius:8px;
        padding:10px 22px;font-size:14px;font-weight:500;cursor:pointer;}
</style>
@endpush

@push('script')
    @include('common.layouts.datatable-script')
    <script src="{{ asset('assets/js/custom/owner.js') }}"></script>
    <script>
        (function () {
            var modal  = document.getElementById('assignAffModal');
            var form   = document.getElementById('assignAffForm');
            var select = document.getElementById('affSelect');
            var warn   = document.getElementById('affWarn');
            var nameEl = document.getElementById('affOwnerName');
            if (!modal || !form) return;

            var actionTpl = '{{ route('admin.owner.assign-affiliate', ['owner' => 'OWNER_ID']) }}';
            var currentId = '', currentName = '';

            function close() { modal.classList.remove('open'); }

            function updateWarn() {
                var val = select.value || '';
                warn.hidden = true;
                // Only warn when we're CHANGING or CLEARING an existing attribution.
                if (currentId && val !== currentId) {
                    if (val === '') {
                        warn.textContent = @json(__('This removes :name as this owner\'s affiliate — they stop earning on this owner going forward.')).replace(':name', currentName);
                    } else {
                        warn.textContent = @json(__('This moves future commission from :old to the newly selected affiliate.')).replace(':old', currentName);
                    }
                    warn.hidden = false;
                }
            }

            // Delegated open (the table rows are rendered by DataTables after load).
            document.addEventListener('click', function (e) {
                var btn = e.target.closest('.js-assign-affiliate');
                if (!btn) return;
                var ownerId = btn.getAttribute('data-owner-id');
                currentId   = btn.getAttribute('data-current-id') || '';
                currentName = btn.getAttribute('data-current-name') || '{{ __('none') }}';
                nameEl.textContent = btn.getAttribute('data-owner-name') || '';
                form.action = actionTpl.replace('OWNER_ID', ownerId);
                select.value = currentId;
                updateWarn();
                modal.classList.add('open');
            });

            select.addEventListener('change', updateWarn);
            modal.addEventListener('click', function (e) { if (e.target === modal) close(); });
            modal.querySelectorAll('[data-aff-cancel]').forEach(function (el) { el.addEventListener('click', close); });
        })();
    </script>
@endpush
