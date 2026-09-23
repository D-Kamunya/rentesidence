@extends('owner.layouts.app')

@php $pageTitle = __('Import Progress'); @endphp

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="page-content-wrapper bg-white p-30 radius-20">

                <div class="ow-page-header mb-4">
                    <div>
                        <h2 class="ow-title">{{ $pageTitle }}</h2>
                        <nav aria-label="breadcrumb">
                            <ol class="ow-breadcrumb">
                                <li><a href="{{ route('owner.tenant.import.index') }}">{{ __('Import Tenants') }}</a></li>
                                <li aria-current="page">
                                    <svg width="8" height="8" viewBox="0 0 16 16" fill="none"><path d="M6 4l4 4-4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    {{ __('Progress') }}
                                </li>
                            </ol>
                        </nav>
                    </div>
                    <a href="{{ route('owner.tenant.index') }}" class="ti-btn ti-btn--ghost" id="tsDoneLink" style="display:none;"><i class="ri-team-line"></i> {{ __('Go to Tenants') }}</a>
                </div>

                <div class="ts-card" data-progress-url="{{ route('owner.tenant.import.progress', $import->id) }}"
                     data-errors-url="{{ route('owner.tenant.import.errors', $import->id) }}">

                    <div class="ts-state" id="tsState">
                        <span class="ts-spinner" id="tsSpinner"></span>
                        <span id="tsStateText">{{ __('Starting import…') }}</span>
                    </div>

                    {{-- Rows --}}
                    <div class="ts-block">
                        <div class="ts-block__head">
                            <span>{{ __('Rows') }}</span>
                            <span id="tsRowsCount">0 / {{ number_format($import->valid_rows) }}</span>
                        </div>
                        <div class="ts-bar"><div class="ts-bar__fill" id="tsRowsBar" style="width:0%"></div></div>
                        <div class="ts-metrics">
                            <span class="ts-metric ts-metric--ok"><b id="tsCreated">0</b> {{ __('created') }}</span>
                            <span class="ts-metric ts-metric--blue"><b id="tsUpdated">0</b> {{ __('updated') }}</span>
                            <span class="ts-metric ts-metric--warn"><b id="tsSkipped">0</b> {{ __('skipped') }}</span>
                        </div>
                    </div>

                    {{-- Invites --}}
                    <div class="ts-block" id="tsInviteBlock" style="display:none;">
                        <div class="ts-block__head">
                            <span>{{ __('Login invites') }}</span>
                            <span id="tsInviteCount">0 / 0</span>
                        </div>
                        <div class="ts-bar"><div class="ts-bar__fill ts-bar__fill--green" id="tsInviteBar" style="width:0%"></div></div>
                        <div class="ts-metrics">
                            <span class="ts-metric ts-metric--ok"><b id="tsInvSent">0</b> {{ __('sent') }}</span>
                            <span class="ts-metric ts-metric--warn"><b id="tsInvFailed">0</b> {{ __('failed') }}</span>
                        </div>
                    </div>

                    {{-- Errors / done --}}
                    <div class="ts-foot" id="tsFoot" style="display:none;">
                        <div id="tsErrorNote" class="ts-errnote" style="display:none;">
                            <i class="ri-error-warning-line"></i>
                            <span id="tsErrorText"></span>
                            <a href="{{ route('owner.tenant.import.errors', $import->id) }}" class="ti-btn ti-btn--ghost ts-errbtn"><i class="ri-download-2-line"></i> {{ __('Download error report') }}</a>
                        </div>
                        <div id="tsFailNote" class="ts-errnote" style="display:none; color:#993C1D;">
                            <i class="ri-close-circle-line"></i> <span id="tsFailText"></span>
                        </div>
                    </div>
                </div>

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
    .ts-card { border:0.5px solid #e5e7eb; border-radius:14px; padding:24px; max-width:640px; }
    .ts-state { display:flex; align-items:center; gap:12px; font-size:15px; font-weight:600; color:#111827; margin-bottom:22px; }
    .ts-state.is-done { color:#0F6E56; }
    .ts-state.is-fail { color:#993C1D; }
    .ts-spinner { width:18px; height:18px; border:2.5px solid #d7e3f2; border-top-color:#185FA5; border-radius:50%; animation:tsspin .8s linear infinite; flex:none; }
    @keyframes tsspin { to { transform:rotate(360deg); } }
    .ts-block { margin-bottom:20px; }
    .ts-block__head { display:flex; justify-content:space-between; font-size:13px; font-weight:600; color:#374151; margin-bottom:7px; }
    .ts-bar { height:9px; background:#eef2f6; border-radius:99px; overflow:hidden; }
    .ts-bar__fill { height:100%; background:#185FA5; width:0; transition:width .4s ease; }
    .ts-bar__fill--green { background:#1D9E75; }
    .ts-metrics { display:flex; gap:16px; margin-top:9px; font-size:12.5px; color:#6b7280; }
    .ts-metric b { color:#111827; }
    .ts-metric--ok b { color:#0F6E56; }
    .ts-metric--blue b { color:#185FA5; }
    .ts-metric--warn b { color:#B45309; }
    .ts-foot { border-top:0.5px solid #f0f2f5; padding-top:16px; }
    .ts-errnote { display:flex; align-items:center; gap:8px; flex-wrap:wrap; font-size:13px; color:#92400E; }
    .ts-errbtn { margin-left:auto; }
    .ti-btn { display:inline-flex; align-items:center; gap:7px; border-radius:9px; font-size:13px; font-weight:600; padding:8px 14px; cursor:pointer; border:0.5px solid #cdd5df; text-decoration:none; color:#185FA5; background:#fff; }
</style>

<script>
(function () {
    var card = document.querySelector('.ts-card');
    if (!card) return;
    var url = card.getAttribute('data-progress-url');

    var el = function (id) { return document.getElementById(id); };
    function pct(n, d) { return d > 0 ? Math.min(100, Math.round(n / d * 100)) : (n > 0 ? 100 : 0); }

    function render(d) {
        // Rows
        el('tsRowsCount').textContent = d.processed.toLocaleString() + ' / ' + d.total.toLocaleString();
        el('tsRowsBar').style.width = pct(d.processed, d.total) + '%';
        el('tsCreated').textContent = d.created.toLocaleString();
        el('tsUpdated').textContent = d.updated.toLocaleString();
        el('tsSkipped').textContent = d.skipped.toLocaleString();

        // Invites
        if (d.invites_queued > 0) {
            el('tsInviteBlock').style.display = 'block';
            el('tsInviteCount').textContent = (d.invites_sent + d.invites_failed).toLocaleString() + ' / ' + d.invites_queued.toLocaleString();
            el('tsInviteBar').style.width = pct(d.invites_sent + d.invites_failed, d.invites_queued) + '%';
            el('tsInvSent').textContent = d.invites_sent.toLocaleString();
            el('tsInvFailed').textContent = d.invites_failed.toLocaleString();
        }

        var state = el('tsState'), txt = el('tsStateText'), spin = el('tsSpinner');

        if (d.status === 'failed') {
            spin.style.display = 'none';
            state.classList.add('is-fail');
            txt.textContent = '{{ __('Import failed') }}';
            el('tsFoot').style.display = 'block';
            el('tsFailNote').style.display = 'flex';
            el('tsFailText').textContent = d.failure || '{{ __('Something went wrong.') }}';
            el('tsDoneLink').style.display = 'inline-flex';
            return true;
        }

        if (d.all_done) {
            spin.style.display = 'none';
            state.classList.add('is-done');
            txt.textContent = '{{ __('Import complete') }}';
            el('tsDoneLink').style.display = 'inline-flex';
            if (d.skipped > 0 || d.error_rows > 0) {
                el('tsFoot').style.display = 'block';
                el('tsErrorNote').style.display = 'flex';
                el('tsErrorText').textContent = (d.skipped + d.error_rows).toLocaleString() + ' {{ __('row(s) were skipped and need fixing.') }}';
            }
            return true;
        }

        // Still working
        if (d.rows_done && d.invites_queued > 0) {
            txt.textContent = '{{ __('Rows done — sending invites…') }}';
        } else {
            txt.textContent = '{{ __('Importing rows…') }}';
        }
        return false;
    }

    function poll() {
        fetch(url, { headers: { 'Accept': 'application/json' } })
            .then(function (r) { return r.json(); })
            .then(function (d) {
                var done = render(d);
                if (!done) { setTimeout(poll, 2000); }
            })
            .catch(function () { setTimeout(poll, 4000); });
    }
    poll();
})();
</script>
@endsection
