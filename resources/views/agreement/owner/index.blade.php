@extends('owner.layouts.app')

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="page-content-wrapper bg-white p-30 radius-20 cs-controls cs-modal">
                @include('centresidence._design')

                {{-- Header --}}
                <div class="ag-header mb-4">
                    <div>
                        <h2 class="ag-title">{{ $pageTitle }}</h2>
                        <p class="ag-sub">{{ __('Send tenancy agreements for e-signature and track their status.') }}</p>
                    </div>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <a href="{{ route('agreement.verify') }}" target="_blank" class="ag-btn ag-btn--ghost">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M12 2l7 3v6c0 4.5-3 8-7 9-4-1-7-4.5-7-9V5l7-3z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><path d="M9 12l2 2 4-4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            {{ __('Verify a certificate') }}
                        </a>
                        <a href="{{ route('owner.agreement.templates') }}" class="ag-btn ag-btn--ghost">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M4 5h16M4 12h16M4 19h10" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                            {{ __('Template') }}
                        </a>
                        <button type="button" class="ag-btn ag-btn--primary" data-bs-toggle="modal" data-bs-target="#sendAgreementModal">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                            {{ __('Send Agreement') }}
                        </button>
                    </div>
                </div>

                {{-- Plan / quota banner --}}
                @if (($eligibility['plan'] ?? '') !== 'free')
                    <div class="ag-note ag-note--green mb-4">
                        {{ __('Unlimited agreements are included on your current plan.') }}
                    </div>
                @else
                    @php $credits = $eligibility['credits'] ?? 0; $price = $eligibility['price'] ?? 0; @endphp
                    <div class="ag-note {{ ($eligibility['requiresPayment'] ?? false) ? 'ag-note--amber' : 'ag-note--blue' }} mb-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <span>
                            {{ __(':r of :q free agreements left this month', ['r' => $eligibility['remaining'] ?? 0, 'q' => $eligibility['quota'] ?? 10]) }}
                            @if ($credits > 0)
                                &middot; {{ __(':c purchased credit(s)', ['c' => $credits]) }}
                            @endif
                            @if (($eligibility['requiresPayment'] ?? false) && $price > 0)
                                — {{ __('top up to send more (:p each)', ['p' => currencyPrice($price)]) }}
                            @endif
                        </span>
                        @if ($price > 0)
                            <button type="button" class="ag-btn ag-btn--ghost" style="padding:5px 12px;" data-bs-toggle="modal" data-bs-target="#buyCreditsModal">
                                {{ __('Buy credits') }}
                            </button>
                        @endif
                    </div>
                @endif

                {{-- List --}}
                <div class="ag-card">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0 ag-table">
                            <thead>
                                <tr>
                                    <th>{{ __('Tenant') }}</th>
                                    <th>{{ __('Agreement') }}</th>
                                    <th>{{ __('Sent') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th style="text-align:right;">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($agreements as $a)
                                    <tr>
                                        <td>{{ optional($a->tenant)->name ?? '—' }}</td>
                                        <td>{{ $a->title }}</td>
                                        <td class="ag-muted">{{ optional($a->sent_at)->format('d M Y') }}</td>
                                        <td>@include('agreement.partials.status-badge', ['status' => $a->status])</td>
                                        <td style="text-align:right;">
                                            <a href="{{ route('owner.agreement.show', $a->id) }}" class="ag-link">{{ __('View') }}</a>
                                            @if ($a->status === 'signed' && $a->signed_file_id)
                                                &middot; <a href="{{ route('owner.agreement.download', $a->id) }}" class="ag-link">{{ __('Download') }}</a>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="ag-empty">{{ __('No agreements yet. Send one to a tenant to get started.') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Send modal --}}
<div class="modal fade" id="sendAgreementModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:14px;overflow:hidden;">
            <form action="{{ route('owner.agreement.send') }}" method="POST">
                @csrf
                <div class="modal-header" style="background:#fafafa;border-bottom:0.5px solid #e5e7eb;">
                    <h5 class="modal-title" style="font-size:15px;font-weight:600;">{{ __('Send Agreement') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" style="padding:18px 20px;">
                    <div class="mb-3">
                        <label class="ag-label">{{ __('Tenant') }}</label>
                        <select name="user_id" class="form-control cs-select" required>
                            <option value="">{{ __('Select a tenant…') }}</option>
                            @foreach ($tenants as $t)
                                <option value="{{ $t->user_id }}">{{ trim($t->first_name . ' ' . $t->last_name) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-1">
                        <label class="ag-label">{{ __('Template') }}</label>
                        <select name="template_id" class="form-control cs-select" required>
                            @foreach ($templates as $tpl)
                                <option value="{{ $tpl->id }}">{{ $tpl->name }}</option>
                            @endforeach
                        </select>
                        <p class="ag-hint">{{ __('The template is autofilled with the tenant\'s unit, rent and details, then sent for signing.') }}</p>
                    </div>
                </div>
                <div class="modal-footer" style="border-top:0.5px solid #e5e7eb;">
                    <button type="button" class="ag-btn ag-btn--ghost" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="ag-btn ag-btn--primary">{{ __('Send for Signing') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
{{-- Buy credits modal (STK) --}}
@if (($eligibility['price'] ?? 0) > 0)
<div class="modal fade" id="buyCreditsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:14px;overflow:hidden;">
            <div class="modal-header" style="background:#fafafa;border-bottom:0.5px solid #e5e7eb;">
                <div class="d-flex align-items-center gap-2">
                    <span class="agc-panel-icon"><svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/></svg></span>
                    <h5 class="modal-title" style="font-size:15px;font-weight:600;margin:0;">{{ __('Buy Agreement Credits') }}</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="padding:20px;">
                <p class="agc-price-note">
                    {{ __('Price:') }} <strong>{{ currencyPrice($eligibility['price']) }}</strong> {{ __('per credit — each credit sends one agreement. Purchased credits never expire.') }}
                </p>

                {{-- Quick-pick packs --}}
                <div class="agc-quickpick mb-3">
                    @foreach ([5, 10, 20, 50, 100] as $pack)
                        <button type="button" class="agc-pack-btn {{ $pack === 10 ? 'active' : '' }}" data-qty="{{ $pack }}">
                            <span class="agc-pack-qty">{{ $pack }}</span>
                            <span class="agc-pack-sub">{{ __('credits') }}</span>
                            <span class="agc-pack-price">{{ currencyPrice($pack * $eligibility['price']) }}</span>
                        </button>
                    @endforeach
                </div>

                {{-- Custom qty --}}
                <div class="agc-custom-row">
                    <input type="number" id="bcQty" class="agc-custom-input" min="1" max="1000" value="10" placeholder="{{ __('Custom qty') }}">
                    <span class="agc-custom-total">{{ __('Total') }}: <span id="bcTotal">{{ currencyPrice($eligibility['price'] * 10) }}</span></span>
                </div>

                {{-- Phone --}}
                <div class="agc-phone-row">
                    <label class="agc-field-label">{{ __('M-Pesa number') }} <span class="agc-field-hint">— {{ __('edit if different') }}</span></label>
                    <div class="agc-phone-wrap">
                        <span class="agc-phone-flag">🇰🇪</span>
                        <input type="tel" id="bcPhone" class="agc-phone-input" value="{{ auth()->user()->contact_number }}" placeholder="07XXXXXXXX">
                    </div>
                </div>

                <p id="bcMsg" class="ag-hint" style="margin:12px 0 0;"></p>
            </div>
            <div class="modal-footer" style="border-top:0.5px solid #e5e7eb;">
                <button type="button" class="ag-btn ag-btn--ghost" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                <button type="button" id="bcPay" class="ag-btn ag-btn--primary">
                    <img src="{{ asset('assets/images/gateway-icon/mpesa.jpg') }}" alt="" style="width:16px;height:16px;border-radius:3px;object-fit:cover;">
                    {{ __('Buy via M-Pesa') }}
                </button>
            </div>
        </div>
    </div>
</div>
<script>
(function () {
    var price   = {{ (float) $eligibility['price'] }};
    var symbol  = "{{ getCurrencySymbol() }}";
    var url     = "{{ route('owner.agreement.credits.checkout') }}";
    var csrf    = "{{ csrf_token() }}";
    var qtyEl   = document.getElementById('bcQty');
    var totalEl = document.getElementById('bcTotal');
    var payBtn  = document.getElementById('bcPay');
    var msgEl   = document.getElementById('bcMsg');
    var packs   = document.querySelectorAll('.agc-pack-btn');

    function fmt(n){ return symbol + Number(n).toLocaleString(undefined,{minimumFractionDigits:2,maximumFractionDigits:2}); }
    function recalc(){ var q = Math.max(1, parseInt(qtyEl.value||'0',10)); totalEl.textContent = fmt(q * price); }
    function highlight(q){ packs.forEach(function(p){ p.classList.toggle('active', parseInt(p.getAttribute('data-qty'),10) === q); }); }

    // Money-safety: the Pay button requires a second, amount-showing confirmation before it
    // ever fires an STK charge, and any change to the quantity cancels a pending confirm so
    // a stale amount can never be confirmed.
    var origBtnHtml = payBtn ? payBtn.innerHTML : '';
    var confirming = false, confirmReset;
    function resetConfirm(){ confirming = false; clearTimeout(confirmReset); if (payBtn){ payBtn.innerHTML = origBtnHtml; payBtn.classList.remove('ag-btn--confirm'); } }

    packs.forEach(function (p) {
        p.addEventListener('click', function () {
            var q = parseInt(p.getAttribute('data-qty'), 10);
            qtyEl.value = q; recalc(); highlight(q); resetConfirm();
        });
    });
    if (qtyEl) {
        qtyEl.addEventListener('focus', function(){ this.select(); }); // avoid fat-fingering the pre-filled value
        qtyEl.addEventListener('input', function(){ recalc(); highlight(parseInt(qtyEl.value||'0',10)); resetConfirm(); });
    }

    // Shared countdown preloader — Pusher accelerates it, but it also resolves on its own
    // after 2 min (redirect to verify), so it works even without Pusher configured.
    function showPreloader(amount) { mpesaWait.show(amount ? { amount: amount } : {}); }
    function hidePreloader() { mpesaWait.hide(); }

    if (payBtn) payBtn.addEventListener('click', function () {
        var qty = Math.max(1, parseInt(qtyEl.value||'0',10));
        var phone = (document.getElementById('bcPhone').value||'').trim();
        if (!phone) { msgEl.textContent = "{{ __('Enter your M-Pesa number.') }}"; return; }

        // First tap arms an explicit confirmation showing the exact total; only the second
        // tap actually charges. Prevents an accidental/mis-typed quantity being paid unseen.
        if (!confirming) {
            confirming = true;
            var totalStr = fmt(qty * price);
            msgEl.style.color = '#b45309';
            msgEl.textContent = "{{ __('You will pay') }} " + totalStr + " {{ __('for') }} " + qty + " {{ __('credit(s). Tap again to confirm.') }}";
            payBtn.innerHTML = "{{ __('Confirm') }} " + totalStr + " →";
            payBtn.classList.add('ag-btn--confirm');
            confirmReset = setTimeout(resetConfirm, 8000);
            return;
        }
        clearTimeout(confirmReset); confirming = false;

        payBtn.disabled = true; msgEl.style.color = ''; msgEl.textContent = '';
        showPreloader(fmt(qty * price));

        fetch(url, {
            method:'POST',
            headers:{'X-CSRF-TOKEN':csrf,'Content-Type':'application/json','Accept':'application/json'},
            body: JSON.stringify({ quantity: qty, phone: phone })
        })
        .then(function(r){ return r.json(); })
        .then(function(d){
            if (d && d.success && d.redirect_url) {
                // Baseline: after 2 min, redirect to verify (confirms from persisted status).
                var timeout = setTimeout(function () { window.location.href = d.redirect_url; }, 120000);
                // Accelerant: the callback fires a Pusher event on transaction.{id}.
                if (window.Pusher && window.Laravel && window.Laravel.pusher_key) {
                    var pusher  = new Pusher(window.Laravel.pusher_key, { cluster: window.Laravel.pusher_cluster });
                    var channel = pusher.subscribe('transaction.' + d.transaction_id);
                    channel.bind('MpesaTransactionProcessed', function () {
                        clearTimeout(timeout);
                        window.location.href = d.redirect_url + '&callback=true&stk_success=true';
                    });
                    channel.bind('MpesaTransactionDeclined', function () {
                        clearTimeout(timeout); hidePreloader(); payBtn.disabled = false;
                        msgEl.textContent = "{{ __('Payment was declined. Please try again.') }}";
                    });
                }
            } else {
                hidePreloader(); resetConfirm(); payBtn.disabled = false;
                msgEl.style.color = ''; msgEl.textContent = (d && d.error) ? d.error : "{{ __('Could not start payment.') }}";
            }
        })
        .catch(function(){ hidePreloader(); resetConfirm(); payBtn.disabled = false; msgEl.style.color = ''; msgEl.textContent = "{{ __('Something went wrong. Try again.') }}"; });
    });
})();
</script>
@endif

@endsection

@push('style')
    @include('agreement.partials.styles')
    <style>
        .ag-btn--confirm { background:#B45309 !important; border-color:#B45309 !important; color:#fff !important; }
        .ag-btn--confirm:hover { background:#92400E !important; }
        .agc-panel-icon { width:28px; height:28px; border-radius:8px; background:#E1F5EE; color:#0F6E56; display:flex; align-items:center; justify-content:center; }
        .agc-price-note { font-size:13px; color:#6b7280; margin:0 0 16px; }
        .agc-price-note strong { color:#111827; }
        .agc-quickpick { display:flex; flex-wrap:wrap; gap:10px; }
        .agc-pack-btn { display:flex; flex-direction:column; align-items:center; gap:2px; padding:10px 16px; border:0.5px solid #e5e7eb; border-radius:10px; background:#fff; cursor:pointer; transition:all .15s; }
        .agc-pack-btn:hover, .agc-pack-btn.active { border-color:#185FA5; background:#E6F1FB; }
        .agc-pack-qty { font-size:18px; font-weight:700; color:#111827; }
        .agc-pack-sub { font-size:10px; font-weight:500; text-transform:uppercase; letter-spacing:.07em; color:#9ca3af; }
        .agc-pack-price { font-size:12px; font-weight:500; color:#185FA5; margin-top:2px; }
        .agc-custom-row { display:flex; align-items:center; gap:12px; margin:16px 0 0; flex-wrap:wrap; }
        .agc-custom-input { width:160px; padding:8px 11px; border:0.5px solid #e5e7eb; border-radius:8px; font-size:14px; color:#374151; outline:none; transition:border-color .15s, box-shadow .15s; }
        .agc-custom-input:focus { border-color:#185FA5; box-shadow:0 0 0 3px rgba(24,95,165,.1); }
        .agc-custom-total { font-size:14px; color:#6b7280; } .agc-custom-total span { font-weight:700; color:#185FA5; }
        .agc-phone-row { margin-top:16px; }
        .agc-field-label { display:block; font-size:10px; font-weight:500; text-transform:uppercase; letter-spacing:.07em; color:#9ca3af; margin-bottom:6px; }
        .agc-field-hint { font-weight:400; text-transform:none; letter-spacing:0; }
        .agc-phone-wrap { display:flex; align-items:center; border:0.5px solid #e5e7eb; border-radius:8px; overflow:hidden; max-width:280px; transition:border-color .15s, box-shadow .15s; }
        .agc-phone-wrap:focus-within { border-color:#185FA5; box-shadow:0 0 0 3px rgba(24,95,165,.1); }
        .agc-phone-flag { padding:0 10px; height:38px; display:flex; align-items:center; background:#fafafa; border-right:0.5px solid #e5e7eb; }
        .agc-phone-input { flex:1; padding:8px 11px; border:none; outline:none; font-size:14px; color:#374151; }

        /* M-Pesa STK waiting overlay is now the shared common.partials.mpesa-stk-waiting component. */
    </style>
@endpush
