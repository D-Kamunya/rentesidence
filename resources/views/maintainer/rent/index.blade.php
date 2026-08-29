@extends('maintainer.layouts.app')

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="page-content-wrapper bg-white p-30 radius-20">

                <div class="mr-head">
                    <div>
                        <h2 class="mr-title">{{ __('Rent & Payments') }}</h2>
                        <p class="mr-sub">{{ $canConfirm
                            ? __('See who has paid and who is in arrears across the properties you manage. Your owner has let you confirm rent received in cash — only confirm cash you have actually collected.')
                            : __('See who has paid and who is in arrears across the properties you manage. This is view-only — you can\'t change any payment status.') }}</p>
                    </div>
                    <div class="mr-stats">
                        <div class="mr-stat"><span class="mr-stat__n">{{ number_format($totalCount) }}</span><span class="mr-stat__l">{{ __('Active tenants') }}</span></div>
                        <div class="mr-stat mr-stat--warn"><span class="mr-stat__n">{{ number_format($unpaidCount) }}</span><span class="mr-stat__l">{{ __('In arrears') }}</span></div>
                    </div>
                </div>

                <form method="GET" class="mr-filter">
                    <input type="text" name="search" value="{{ request('search') }}" class="mr-input" placeholder="{{ __('Search tenant, property or unit…') }}">
                    <select name="status" class="mr-input mr-input--sel" onchange="this.form.submit()">
                        <option value="">{{ __('All') }}</option>
                        <option value="unpaid" @selected(request('status')==='unpaid')>{{ __('In arrears') }}</option>
                        <option value="paid" @selected(request('status')==='paid')>{{ __('Up to date') }}</option>
                    </select>
                    <button type="submit" class="mr-btn">{{ __('Search') }}</button>
                    @if (request()->filled('search') || request()->filled('status'))
                        <a href="{{ route('maintainer.rent.index') }}" class="mr-clear">{{ __('Clear') }}</a>
                    @endif
                </form>

                <div class="table-responsive">
                    <table class="mr-tbl">
                        <thead>
                            <tr>
                                <th>{{ __('Tenant') }}</th>
                                <th>{{ __('Property') }}</th>
                                <th>{{ __('Unit') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th class="mr-right">{{ __('Arrears') }}</th>
                                <th>{{ __('Last payment') }}</th>
                                @if ($canConfirm)<th class="mr-right">{{ __('Action') }}</th>@endif
                            </tr>
                        </thead>
                        <tbody>
                        @forelse ($tenants as $t)
                            @php $inArrears = (float) ($t->due ?? 0) > 0; @endphp
                            <tr>
                                <td>
                                    <span class="mr-name">{{ trim($t->first_name . ' ' . $t->last_name) }}</span>
                                    @if ($t->contact_number)<a href="tel:{{ $t->contact_number }}" class="mr-phone">{{ $t->contact_number }}</a>@endif
                                </td>
                                <td>{{ $t->property_name ?: '—' }}</td>
                                <td>{{ $t->unit_name ?: '—' }}</td>
                                <td>
                                    @if ($inArrears)
                                        <span class="mr-badge mr-badge--due">{{ __('In arrears') }}</span>
                                    @else
                                        <span class="mr-badge mr-badge--ok">{{ __('Up to date') }}</span>
                                    @endif
                                </td>
                                <td class="mr-right">{{ $inArrears ? currencyPrice($t->due) : currencyPrice(0) }}</td>
                                <td>{{ $t->last_payment ? date('Y-m-d', strtotime($t->last_payment)) : __('N/A') }}</td>
                                @if ($canConfirm)
                                    <td class="mr-right">
                                        @if ($inArrears)
                                            <button type="button" class="mr-confirm" data-tenant="{{ $t->id }}" data-name="{{ trim($t->first_name . ' ' . $t->last_name) }}">{{ __('Confirm payment') }}</button>
                                        @else
                                            <span class="mr-dash">—</span>
                                        @endif
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr><td colspan="{{ $canConfirm ? 7 : 6 }}" class="mr-empty">{{ __('No tenants on your assigned properties yet.') }}</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">{{ $tenants->links() }}</div>
            </div>
        </div>
    </div>
</div>

@if ($canConfirm)
<div class="mrc-backdrop" id="mrcBackdrop" aria-hidden="true">
    <div class="mrc-modal" role="dialog" aria-modal="true" aria-labelledby="mrcName">
        <div class="mrc-modal__head">
            <div>
                <span class="mrc-modal__eyebrow">{{ __('Confirm cash rent') }}</span>
                <h3 class="mrc-modal__title" id="mrcName">—</h3>
            </div>
            <button type="button" class="mrc-close" id="mrcClose" aria-label="{{ __('Close') }}">✕</button>
        </div>
        <div class="mrc-modal__body">
            <p class="mrc-hint">{{ __('Only confirm rent you have actually received in cash. Your owner is notified of every confirmation.') }}</p>
            <div id="mrcList"><div class="mrc-state">{{ __('Loading…') }}</div></div>
        </div>
    </div>
</div>
<input type="hidden" id="mrcInvoicesUrl" value="{{ route('maintainer.rent.invoices') }}">
<input type="hidden" id="mrcConfirmUrl" value="{{ route('maintainer.rent.confirm') }}">
@endif

<style>
    .mr-head { display:flex; align-items:flex-start; justify-content:space-between; gap:20px; flex-wrap:wrap; margin-bottom:18px; }
    .mr-title { font-size:22px; font-weight:600; color:#111827; margin:0 0 5px; }
    .mr-sub { font-size:13.5px; color:#6b7280; margin:0; max-width:64ch; line-height:1.6; }
    .mr-stats { display:flex; gap:12px; }
    .mr-stat { border:0.5px solid #e5e7eb; border-radius:12px; padding:12px 16px; text-align:center; min-width:96px; }
    .mr-stat--warn { background:#FEF3E7; border-color:#F5D9A8; }
    .mr-stat__n { display:block; font-size:22px; font-weight:800; color:#111827; }
    .mr-stat--warn .mr-stat__n { color:#B45309; }
    .mr-stat__l { font-size:11px; color:#6b7280; }
    .mr-filter { display:flex; gap:8px; margin-bottom:16px; flex-wrap:wrap; }
    .mr-input { padding:9px 12px; border:0.5px solid #d1d5db; border-radius:9px; font-size:13.5px; outline:none; }
    .mr-input:focus { border-color:#185FA5; box-shadow:0 0 0 3px rgba(24,95,165,.1); }
    .mr-input:not(.mr-input--sel) { flex:1; min-width:200px; }
    .mr-btn { background:#185FA5; color:#fff; border:none; border-radius:9px; font-size:13.5px; font-weight:600; padding:9px 18px; cursor:pointer; }
    .mr-btn:hover { background:#0F4A84; }
    .mr-tbl { width:100%; border-collapse:collapse; font-size:13px; }
    .mr-tbl th { text-align:left; font-size:10.5px; text-transform:uppercase; letter-spacing:.05em; color:#9ca3af; font-weight:600; padding:10px 12px; border-bottom:0.5px solid #e5e7eb; }
    .mr-tbl td { padding:12px; border-bottom:0.5px solid #f1f5f9; color:#374151; vertical-align:top; }
    /* th needs the element+class selector to beat `.mr-tbl th { text-align:left }`. */
    .mr-right, .mr-tbl th.mr-right, .mr-tbl td.mr-right { text-align:right; }
    .mr-clear { display:inline-flex; align-items:center; font-size:13px; color:#6b7280; text-decoration:none; padding:9px 6px; }
    .mr-clear:hover { color:#B42318 !important; }
    .mr-name { display:block; font-weight:600; color:#111827; }
    .mr-phone { font-size:11.5px; color:#185FA5; text-decoration:none; }
    .mr-badge { font-size:11px; font-weight:600; padding:3px 10px; border-radius:99px; }
    .mr-badge--ok { background:#E1F5EE; color:#0F6E56; }
    .mr-badge--due { background:#FAECE7; color:#993C1D; }
    .mr-empty { text-align:center; color:#9ca3af; padding:36px 12px; }
    .mr-dash { color:#cbd5e1; }
    .mr-confirm { background:#E1F5EE; color:#0F6E56; border:0.5px solid #A7DFC9; border-radius:8px; font-size:12.5px; font-weight:600; padding:7px 13px; cursor:pointer; white-space:nowrap; }
    .mr-confirm:hover { background:#0F6E56; color:#fff !important; }

    /* Confirm-cash modal */
    .mrc-backdrop { position:fixed; inset:0; background:rgba(17,24,39,.45); backdrop-filter:blur(2px); z-index:1050; display:flex; align-items:center; justify-content:center; padding:1rem; opacity:0; pointer-events:none; transition:opacity .18s; }
    .mrc-backdrop.is-open { opacity:1; pointer-events:all; }
    .mrc-modal { background:#fff; border-radius:14px; width:100%; max-width:440px; box-shadow:0 20px 60px rgba(17,24,39,.2); transform:translateY(10px) scale(.98); transition:transform .2s, opacity .2s; opacity:0; overflow:hidden; }
    .mrc-backdrop.is-open .mrc-modal { transform:translateY(0) scale(1); opacity:1; }
    .mrc-modal__head { display:flex; align-items:flex-start; justify-content:space-between; padding:16px 20px; border-bottom:0.5px solid #f1f5f9; background:#fafafa; }
    .mrc-modal__eyebrow { font-size:10px; font-weight:600; text-transform:uppercase; letter-spacing:.06em; color:#9ca3af; }
    .mrc-modal__title { font-size:16px; font-weight:600; color:#111827; margin:3px 0 0; }
    .mrc-close { background:#f3f4f6; border:none; border-radius:7px; width:30px; height:30px; cursor:pointer; color:#6b7280; flex:none; }
    .mrc-close:hover { background:#e5e7eb; color:#111827; }
    .mrc-modal__body { padding:18px 20px; }
    .mrc-hint { font-size:12.5px; color:#6b7280; line-height:1.5; margin:0 0 14px; }
    .mrc-state { text-align:center; color:#9ca3af; font-size:13px; padding:24px 0; }
    .mrc-inv { display:flex; align-items:center; justify-content:space-between; gap:12px; padding:12px 0; border-bottom:0.5px solid #f1f5f9; }
    .mrc-inv:last-child { border-bottom:0; }
    .mrc-inv__label { font-size:13px; font-weight:600; color:#111827; }
    .mrc-inv__meta { font-size:11.5px; color:#9ca3af; margin-top:2px; }
    .mrc-inv__amt { font-size:13px; font-weight:700; color:#0F6E56; }
    .mrc-mark { background:#185FA5; color:#fff; border:none; border-radius:8px; font-size:12px; font-weight:600; padding:7px 12px; cursor:pointer; white-space:nowrap; }
    .mrc-mark:hover { background:#0F4A84; }
    .mrc-mark:disabled { opacity:.6; cursor:not-allowed; }
</style>
@endsection

@if ($canConfirm)
@push('script')
<script>
(function () {
    'use strict';
    const backdrop   = document.getElementById('mrcBackdrop');
    const listEl     = document.getElementById('mrcList');
    const nameEl     = document.getElementById('mrcName');
    const invoicesUrl = document.getElementById('mrcInvoicesUrl').value;
    const confirmUrl  = document.getElementById('mrcConfirmUrl').value;
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

    function open()  { backdrop.classList.add('is-open'); backdrop.setAttribute('aria-hidden', 'false'); document.body.style.overflow = 'hidden'; }
    function close() { backdrop.classList.remove('is-open'); backdrop.setAttribute('aria-hidden', 'true'); document.body.style.overflow = ''; }

    document.getElementById('mrcClose').addEventListener('click', close);
    backdrop.addEventListener('click', e => { if (e.target === backdrop) close(); });
    document.addEventListener('keydown', e => { if (e.key === 'Escape' && backdrop.classList.contains('is-open')) close(); });

    function render(invoices) {
        if (!invoices.length) { listEl.innerHTML = '<div class="mrc-state">{{ __('No unpaid invoices for this tenant.') }}</div>'; return; }
        listEl.innerHTML = invoices.map(i => `
            <div class="mrc-inv" data-id="${i.id}">
                <div>
                    <div class="mrc-inv__label">${i.label ?? i.month ?? ''}</div>
                    <div class="mrc-inv__meta">${i.invoice_no ?? ''}</div>
                </div>
                <div style="display:flex;align-items:center;gap:12px;">
                    <span class="mrc-inv__amt">${i.amount}</span>
                    <button type="button" class="mrc-mark">{{ __('Mark received') }}</button>
                </div>
            </div>`).join('');
    }

    document.querySelectorAll('.mr-confirm').forEach(btn => {
        btn.addEventListener('click', function () {
            nameEl.textContent = btn.dataset.name || '';
            listEl.innerHTML = '<div class="mrc-state">{{ __('Loading…') }}</div>';
            open();
            fetch(`${invoicesUrl}?tenant_id=${encodeURIComponent(btn.dataset.tenant)}`, { headers: { 'Accept': 'application/json' } })
                .then(r => r.json())
                .then(d => render(d.invoices || []))
                .catch(() => { listEl.innerHTML = '<div class="mrc-state">{{ __('Could not load invoices.') }}</div>'; });
        });
    });

    listEl.addEventListener('click', function (e) {
        const mark = e.target.closest('.mrc-mark');
        if (!mark) return;
        const row = mark.closest('.mrc-inv');
        const id = row?.dataset.id;
        if (!id) return;
        mark.disabled = true; mark.textContent = '{{ __('Saving…') }}';
        fetch(confirmUrl, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'Content-Type': 'application/json' },
            body: JSON.stringify({ invoice_id: id }),
        })
        .then(r => r.json())
        .then(d => {
            if (d.success) {
                if (typeof toastr !== 'undefined') toastr.success(d.message || '{{ __('Payment confirmed.') }}');
                row.remove();
                if (!listEl.querySelector('.mrc-inv')) setTimeout(() => location.reload(), 900);
            } else {
                mark.disabled = false; mark.textContent = '{{ __('Mark received') }}';
                if (typeof toastr !== 'undefined') toastr.error(d.message || '{{ __('Could not confirm.') }}');
            }
        })
        .catch(() => {
            mark.disabled = false; mark.textContent = '{{ __('Mark received') }}';
            if (typeof toastr !== 'undefined') toastr.error('{{ __('Request failed. Please try again.') }}');
        });
    });
})();
</script>
@endpush
@endif
