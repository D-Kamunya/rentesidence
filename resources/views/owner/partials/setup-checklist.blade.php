{{-- Owner setup checklist — a live "get set up to collect rent" tracker for new owners. Each step's
     done state is computed from real data; auto-hides once complete, or when the owner dismisses it. --}}
@php
    $csSetup = app(\App\Services\OwnerAdvisor\OwnerSetupService::class)->checklist((int) auth()->id());
    $csSetupDismissed = app(\App\Services\OnboardingTourService::class)->completed((int) auth()->id(), 'owner_setup');
    $csSetupShow = ! $csSetup['complete'] && ! $csSetupDismissed && $csSetup['total'] > 0;
    $csSetupPct = $csSetup['total'] ? round($csSetup['done'] / $csSetup['total'] * 100) : 0;
@endphp
@if ($csSetupShow)
<div class="cs-setup" id="csSetup">
    <button type="button" class="cs-setup__x" aria-label="{{ __('Dismiss') }}" onclick="csSetupDismiss()">&times;</button>
    <div class="cs-setup__head">
        <div>
            <div class="cs-setup__eyebrow">{{ __('Get set up') }}</div>
            <h3 class="cs-setup__title">{{ __('A few steps to start collecting rent') }}</h3>
        </div>
        <div class="cs-setup__count">{{ $csSetup['done'] }}/{{ $csSetup['total'] }}</div>
    </div>
    <div class="cs-setup__bar"><span style="width:{{ $csSetupPct }}%"></span></div>
    <ul class="cs-setup__steps">
        @foreach ($csSetup['steps'] as $step)
            @php $csInlineMpesa = ! $step['done'] && (($step['inline'] ?? null) === 'mpesa'); @endphp
            <li class="cs-setup__step {{ $step['done'] ? 'is-done' : '' }}">
                <div class="cs-setup__row">
                    <span class="cs-setup__tick">
                        @if ($step['done'])
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none"><path d="M20 6L9 17l-5-5" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        @endif
                    </span>
                    <span class="cs-setup__label">{{ $step['label'] }}</span>
                    @unless ($step['done'])
                        @if ($csInlineMpesa)
                            <button type="button" class="cs-setup__cta" onclick="csMpesaToggle()">{{ $step['cta_label'] }} ›</button>
                        @else
                            <a href="{{ $step['cta_url'] }}" class="cs-setup__cta">{{ $step['cta_label'] }} ›</a>
                        @endif
                    @endunless
                </div>
                @if ($csInlineMpesa)
                    <div class="cs-mpesa" id="csMpesa" hidden>
                        <div class="cs-mpesa__types" role="tablist">
                            <label class="cs-mpesa__type is-on"><input type="radio" name="csMpesaType" value="TILLNUMBER" checked onchange="csMpesaSwitch(this)"> {{ __('Till number') }}</label>
                            <label class="cs-mpesa__type"><input type="radio" name="csMpesaType" value="PAYBILL" onchange="csMpesaSwitch(this)"> {{ __('Paybill') }}</label>
                        </div>
                        <div class="cs-mpesa__fields" data-cs-mpesa-fields="TILLNUMBER">
                            <input type="text" inputmode="numeric" autocomplete="off" id="csMpesaTill" class="cs-mpesa__input" placeholder="{{ __('Till number (e.g. 5203988)') }}">
                        </div>
                        <div class="cs-mpesa__fields" data-cs-mpesa-fields="PAYBILL" hidden>
                            <input type="text" inputmode="numeric" autocomplete="off" id="csMpesaPaybill" class="cs-mpesa__input" placeholder="{{ __('Paybill number') }}">
                            <input type="text" autocomplete="off" id="csMpesaAccName" class="cs-mpesa__input" placeholder="{{ __('Account / business name') }}">
                        </div>
                        <div class="cs-mpesa__err" id="csMpesaErr" hidden></div>
                        <div class="cs-mpesa__actions">
                            <button type="button" class="cs-mpesa__save" id="csMpesaSave" onclick="csMpesaSave()">{{ __('Save & activate') }}</button>
                            <a href="{{ $step['cta_url'] }}" class="cs-mpesa__adv">{{ __('More options') }}</a>
                        </div>
                        <p class="cs-mpesa__hint">{{ __('Rent is paid straight to this M-Pesa. You can change it anytime in Settings.') }}</p>
                    </div>
                @endif
            </li>
        @endforeach
    </ul>
</div>
<style>
    .cs-setup{position:relative;background:#fff;border:1px solid #E6E1D8;border-radius:16px;padding:20px 22px;margin-bottom:20px;box-shadow:0 1px 3px rgba(16,24,40,.04);}
    .cs-setup__x{position:absolute;top:12px;right:14px;background:none;border:none;color:#c3c6cb;font-size:20px;line-height:1;cursor:pointer;}
    .cs-setup__x:hover{color:#6b7280;}
    .cs-setup__head{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;margin-bottom:12px;}
    .cs-setup__eyebrow{font-size:11px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:#185FA5;margin-bottom:3px;}
    .cs-setup__title{font-size:16.5px;font-weight:800;color:#1b1e22;margin:0;letter-spacing:-.01em;}
    .cs-setup__count{font-family:ui-monospace,Menlo,monospace;font-size:13px;font-weight:700;color:#6b7280;background:#f3f4f6;border-radius:999px;padding:4px 11px;white-space:nowrap;}
    .cs-setup__bar{height:6px;background:#eef0f3;border-radius:999px;overflow:hidden;margin-bottom:16px;}
    .cs-setup__bar span{display:block;height:100%;background:linear-gradient(90deg,#185FA5,#3B82C4);border-radius:999px;transition:width .4s;}
    .cs-setup__steps{list-style:none;margin:0;padding:0;display:flex;flex-direction:column;gap:10px;}
    .cs-setup__step{font-size:14px;color:#1b1e22;}
    .cs-setup__row{display:flex;align-items:center;gap:11px;}
    .cs-setup__tick{flex:none;width:22px;height:22px;border-radius:50%;border:1.6px solid #d7dbe0;display:grid;place-items:center;color:#fff;}
    .cs-setup__step.is-done .cs-setup__tick{background:#0F6E56;border-color:#0F6E56;}
    .cs-setup__step.is-done .cs-setup__label{color:#8a9099;text-decoration:line-through;}
    .cs-setup__label{flex:1;}
    .cs-setup__cta{flex:none;font-size:13px;font-weight:700;color:#185FA5;text-decoration:none;white-space:nowrap;background:none;border:none;cursor:pointer;padding:0;font-family:inherit;}
    .cs-setup__cta:hover{color:#0F4A84;}
    /* Inline M-Pesa quick-setup */
    .cs-mpesa{margin:12px 0 2px 33px;padding:14px;background:#F7FAFD;border:1px solid #DCE7F2;border-radius:12px;}
    .cs-mpesa[hidden]{display:none;}
    .cs-mpesa__types{display:inline-flex;background:#eef1f5;border-radius:10px;padding:3px;gap:2px;margin-bottom:12px;}
    .cs-mpesa__type{font-size:12.5px;font-weight:700;color:#5a626d;padding:6px 14px;border-radius:8px;cursor:pointer;user-select:none;transition:.15s;}
    .cs-mpesa__type.is-on{background:#fff;color:#185FA5;box-shadow:0 1px 2px rgba(16,24,40,.08);}
    .cs-mpesa__type input{position:absolute;opacity:0;width:0;height:0;}
    .cs-mpesa__fields{display:flex;flex-wrap:wrap;gap:8px;}
    .cs-mpesa__fields[hidden]{display:none;}
    .cs-mpesa__input{flex:1;min-width:170px;font-size:14px;padding:10px 12px;border:1px solid #cfd6de;border-radius:9px;background:#fff;color:#1b1e22;}
    .cs-mpesa__input:focus{outline:none;border-color:#185FA5;box-shadow:0 0 0 3px rgba(24,95,165,.12);}
    .cs-mpesa__err{margin-top:10px;font-size:12.5px;color:#B42318;background:#FEF3F2;border:1px solid #FDA29B;border-radius:8px;padding:7px 10px;}
    .cs-mpesa__err[hidden]{display:none;}
    .cs-mpesa__actions{display:flex;align-items:center;gap:14px;margin-top:12px;}
    .cs-mpesa__save{font-size:13.5px;font-weight:700;color:#fff;background:#185FA5;border:none;border-radius:9px;padding:10px 18px;cursor:pointer;transition:.15s;font-family:inherit;}
    .cs-mpesa__save:hover{background:#0F4A84;}
    .cs-mpesa__save:disabled{opacity:.6;cursor:default;}
    .cs-mpesa__adv{font-size:12.5px;font-weight:600;color:#6b7280;text-decoration:none;}
    .cs-mpesa__adv:hover{color:#185FA5;}
    .cs-mpesa__hint{margin:10px 0 0;font-size:12px;color:#8a9099;line-height:1.5;}
    @media(max-width:520px){.cs-mpesa{margin-left:0;}}
</style>
<script>
    (function () {
        var el = document.getElementById('csSetup');
        if (!el) return;
        window.csSetupDismiss = function () {
            el.style.display = 'none';
            try {
                fetch('{{ route('tour.complete') }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/x-www-form-urlencoded' },
                    credentials: 'same-origin', body: 'key=owner_setup'
                });
            } catch (e) {}
        };

        // --- Inline M-Pesa quick setup ---
        var box = document.getElementById('csMpesa');
        window.csMpesaToggle = function () {
            if (!box) return;
            box.hidden = !box.hidden;
            if (!box.hidden) { var f = box.querySelector('.cs-mpesa__input'); if (f) f.focus(); }
        };
        window.csMpesaSwitch = function (radio) {
            if (!box) return;
            box.querySelectorAll('.cs-mpesa__type').forEach(function (l) { l.classList.remove('is-on'); });
            if (radio.closest('.cs-mpesa__type')) radio.closest('.cs-mpesa__type').classList.add('is-on');
            box.querySelectorAll('[data-cs-mpesa-fields]').forEach(function (g) {
                g.hidden = g.getAttribute('data-cs-mpesa-fields') !== radio.value;
            });
        };
        function csMpesaErr(msg) {
            var e = document.getElementById('csMpesaErr');
            if (!e) return;
            e.textContent = msg; e.hidden = !msg;
        }
        window.csMpesaSave = function () {
            if (!box) return;
            var type = (box.querySelector('input[name="csMpesaType"]:checked') || {}).value || 'TILLNUMBER';
            var body = { account_type: type };
            if (type === 'TILLNUMBER') {
                body.till_number = (document.getElementById('csMpesaTill').value || '').trim();
                if (!/^\d{5,10}$/.test(body.till_number)) { csMpesaErr('{{ __('Enter a valid Till number (5–10 digits).') }}'); return; }
            } else {
                body.paybill_number = (document.getElementById('csMpesaPaybill').value || '').trim();
                body.account_name = (document.getElementById('csMpesaAccName').value || '').trim();
                if (!/^\d{5,10}$/.test(body.paybill_number)) { csMpesaErr('{{ __('Enter a valid Paybill number (5–10 digits).') }}'); return; }
                if (!body.account_name) { csMpesaErr('{{ __('Enter the account / business name.') }}'); return; }
            }
            csMpesaErr('');
            var btn = document.getElementById('csMpesaSave');
            if (btn) { btn.disabled = true; btn.textContent = '{{ __('Saving…') }}'; }
            var params = new URLSearchParams(body);
            fetch('{{ route('owner.setting.gateway.quick-mpesa') }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/x-www-form-urlencoded', 'Accept': 'application/json' },
                credentials: 'same-origin', body: params.toString()
            }).then(function (r) { return r.json().then(function (j) { return { ok: r.ok, j: j }; }); })
              .then(function (res) {
                  if (res.ok && res.j && res.j.status) { window.location.reload(); return; }
                  csMpesaErr((res.j && res.j.message) || '{{ __('Could not save. Please try again.') }}');
                  if (btn) { btn.disabled = false; btn.textContent = '{{ __('Save & activate') }}'; }
              }).catch(function () {
                  csMpesaErr('{{ __('Network error. Please try again.') }}');
                  if (btn) { btn.disabled = false; btn.textContent = '{{ __('Save & activate') }}'; }
              });
        };
    })();
</script>
@endif
