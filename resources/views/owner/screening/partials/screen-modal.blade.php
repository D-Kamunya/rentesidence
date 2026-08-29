@php
    // In-flow tenant screening for the add/edit-tenant wizard. Renders an attention-grabbing
    // CTA card + a modal that runs the lookup over AJAX and shows the report inline, so the
    // owner never leaves their half-filled form. Self-contained: computes its own coverage so
    // the host controllers need no changes.
    $scSvc         = app(\App\Services\Screening\ScreeningLookupService::class);
    $scElig        = $scSvc->eligibility(auth()->id());
    $scUnlimited   = \App\Services\Screening\ScreeningLookupService::ownerHasUnlimited(auth()->id());
    $scWillCharge  = (! $scUnlimited) && (($scElig['cover'] ?? null) === 'credit');
    $scPrice       = \App\Services\Credit\CreditService::pricePerUnit('screening');
    $scPrefill     = isset($tenant) && optional($tenant->user)->contact_number ? $tenant->user->contact_number : '';
@endphp

{{-- Attention-grabbing CTA — this is the value/advertising moment --}}
<div class="scm-cta">
    <div class="scm-cta__glow" aria-hidden="true"></div>
    <div class="scm-cta__badge"><i class="ri-shield-star-line"></i></div>
    <div class="scm-cta__body">
        <span class="scm-cta__eyebrow">{{ __('Before you hand over the keys') }}</span>
        <h3 class="scm-cta__title">{{ __('Screen this tenant across the network') }}</h3>
        <p class="scm-cta__text">{{ __('See how they\'ve actually paid rent across every past tenancy — an objective score, not one landlord\'s word. Your protection against a costly bad tenant.') }}</p>
    </div>
    <button type="button" class="scm-cta__btn" onclick="scmOpen()">
        <i class="ri-search-eye-line"></i> {{ __('Screen now') }}
    </button>
</div>

{{-- Modal --}}
<div class="scm-overlay" id="scmOverlay" role="dialog" aria-modal="true" aria-labelledby="scmHeading" hidden>
    <div class="scm-modal">
        <button type="button" class="scm-close" onclick="scmClose()" aria-label="{{ __('Close') }}"><i class="ri-close-line"></i></button>
        <h3 class="scm-heading" id="scmHeading"><i class="ri-shield-user-line"></i> {{ __('Tenant Screening') }}</h3>
        <p class="scm-sub">{{ __('Enter the tenant\'s phone to see their objective rental record.') }}</p>

        <div class="scm-inputrow">
            <div class="scm-field">
                <span class="scm-flag">🇰🇪</span>
                <input type="tel" id="scmPhone" class="scm-input" inputmode="tel" value="{{ $scPrefill }}"
                       placeholder="{{ __('e.g. 0712 345 678') }}" autocomplete="off">
            </div>
            <button type="button" class="scm-go" id="scmGo" onclick="scmRun()">
                <i class="ri-search-eye-line"></i> <span>{{ __('Screen') }}</span>
            </button>
        </div>
        <p class="scm-cost">
            @if ($scUnlimited)
                {{ __('Included in your plan.') }}
            @elseif (($scElig['remaining'] ?? 0) > 0)
                {{ __('Free — :n left this month.', ['n' => $scElig['remaining']]) }}
            @elseif (($scElig['credits'] ?? 0) > 0)
                {{ __('Uses 1 screening credit.') }}
            @else
                {{ __('You\'re out of screening credits.') }}
            @endif
            {{ __('Only charged when there\'s a record.') }}
        </p>

        <div class="scm-result" id="scmResult"></div>

        <div class="scm-foot">
            @if (($scmContext ?? 'add') === 'add')
                <button type="button" class="scm-reject" onclick="scmDiscard()">
                    <i class="ri-close-circle-line"></i> {{ __('Don\'t proceed with this tenant') }}
                </button>
            @endif
            <button type="button" class="scm-continue" onclick="scmClose()">{{ __('Continue adding tenant') }} <i class="ri-arrow-right-line"></i></button>
        </div>
    </div>
</div>

@if (($scmContext ?? 'add') === 'add')
    {{-- Discards the half-built draft so declined tenants don't pile up as orphan data. --}}
    <form id="scmDiscardForm" action="{{ route('owner.tenant.draft.discard') }}" method="POST" class="d-none">
        @csrf
        <input type="hidden" name="id" id="scmDraftId" value="">
    </form>
@endif

<style>
    /* CTA card */
    .scm-cta { position:relative; overflow:hidden; display:flex; align-items:center; gap:18px; border-radius:16px; padding:22px 24px; margin-bottom:6px;
        background:linear-gradient(120deg,#0F2A4A 0%,#185FA5 60%,#1E6FBF 100%); color:#fff; box-shadow:0 8px 24px rgba(15,42,74,.18); }
    .scm-cta__glow { position:absolute; right:-40px; top:-60px; width:220px; height:220px; border-radius:50%;
        background:radial-gradient(circle,#f6b64b 0%,transparent 70%); opacity:.28; pointer-events:none; }
    .scm-cta__badge { flex:none; width:52px; height:52px; border-radius:14px; background:rgba(255,255,255,.14); display:flex; align-items:center; justify-content:center; }
    .scm-cta__badge i { font-size:26px; color:#fff; }
    .scm-cta__body { flex:1; position:relative; z-index:1; }
    .scm-cta__eyebrow { font-size:11px; text-transform:uppercase; letter-spacing:.08em; font-weight:600; color:#9fd0ff; }
    .scm-cta__title { font-size:18px; font-weight:700; margin:3px 0 5px; color:#fff; }
    .scm-cta__text { font-size:12.5px; line-height:1.55; color:#d6e6f7; margin:0; max-width:62ch; }
    .scm-cta__btn { flex:none; position:relative; z-index:1; display:inline-flex; align-items:center; gap:7px; background:#fff; color:#0F2A4A;
        border:none; border-radius:11px; font-size:14px; font-weight:700; padding:13px 22px; cursor:pointer; white-space:nowrap; transition:transform .1s; }
    .scm-cta__btn:hover { transform:translateY(-1px); }
    @media (max-width:720px){ .scm-cta { flex-direction:column; align-items:flex-start; text-align:left; } .scm-cta__btn { width:100%; justify-content:center; } }

    /* Modal */
    .scm-overlay { position:fixed; inset:0; background:rgba(15,23,42,.55); backdrop-filter:blur(2px); z-index:11000; display:flex; align-items:flex-start; justify-content:center; padding:40px 16px; overflow-y:auto; }
    .scm-overlay[hidden] { display:none; }
    .scm-modal { position:relative; width:100%; max-width:720px; background:#fff; border-radius:18px; padding:28px 28px 22px; box-shadow:0 24px 60px rgba(15,23,42,.3); animation:scmIn .16s ease-out; }
    @keyframes scmIn { from { opacity:0; transform:translateY(10px); } to { opacity:1; transform:none; } }
    .scm-close { position:absolute; top:16px; right:16px; width:34px; height:34px; border:none; background:#f3f4f6; border-radius:9px; cursor:pointer; font-size:18px; color:#6b7280; display:flex; align-items:center; justify-content:center; }
    .scm-close:hover { background:#e5e7eb; }
    .scm-heading { font-size:18px; font-weight:700; color:#111827; margin:0 0 4px; display:flex; align-items:center; gap:8px; }
    .scm-heading i { color:#185FA5; }
    .scm-sub { font-size:13px; color:#6b7280; margin:0 0 16px; }
    .scm-inputrow { display:flex; gap:10px; }
    .scm-field { flex:1; display:flex; align-items:center; border:0.5px solid #d1d5db; border-radius:11px; overflow:hidden; }
    .scm-field:focus-within { border-color:#185FA5; box-shadow:0 0 0 3px rgba(24,95,165,.1); }
    .scm-flag { padding:0 12px; font-size:15px; background:#f9fafb; border-right:0.5px solid #e5e7eb; align-self:stretch; display:flex; align-items:center; }
    .scm-input { flex:1; padding:12px 14px; border:none; outline:none; font-size:14.5px; color:#111827; }
    .scm-go { display:inline-flex; align-items:center; gap:7px; background:#185FA5; color:#fff; border:none; border-radius:11px; font-size:14px; font-weight:600; padding:0 22px; cursor:pointer; white-space:nowrap; }
    .scm-go:hover { background:#0F4A84; }
    .scm-go:disabled { opacity:.7; cursor:progress; }
    .scm-go.is-done { background:#0F6E56; opacity:1; cursor:default; }
    @media (max-width:560px){ .scm-inputrow { flex-direction:column; } .scm-go { width:100%; justify-content:center; padding:13px; } }
    .scm-cost { font-size:12px; color:#6b7280; margin:9px 2px 0; }
    .scm-result { margin-top:18px; }
    .scm-result:empty { margin-top:0; }
    .scm-topup { text-align:center; padding:26px; border:0.5px dashed #d1d5db; border-radius:14px; color:#6b7280; }
    .scm-topup a { display:inline-block; margin-top:10px; background:#185FA5; color:#fff; border-radius:9px; padding:9px 18px; font-size:13px; font-weight:600; text-decoration:none; }
    .scm-foot { margin-top:20px; padding-top:16px; border-top:0.5px solid #eef2f6; display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap; }
    .scm-continue { display:inline-flex; align-items:center; gap:6px; background:#185FA5; color:#fff; border:none; border-radius:10px; font-size:13.5px; font-weight:600; padding:11px 20px; cursor:pointer; margin-left:auto; }
    .scm-continue:hover { background:#0F4A84; }
    .scm-reject { display:inline-flex; align-items:center; gap:6px; background:#fff; color:#B42318; border:0.5px solid #F3C4BC; border-radius:10px; font-size:13px; font-weight:600; padding:10px 16px; cursor:pointer; }
    .scm-reject:hover { background:#FBE9E7; }

    /* Report (mirrors the screening page — this page doesn't load that CSS) */
    .sc-report { border:0.5px solid #e5e7eb; border-radius:16px; padding:20px; }
    .sc-report--empty { text-align:center; padding:36px 20px; color:#6b7280; }
    .sc-report--empty i { font-size:40px; color:#cbd5e1; }
    .sc-report--empty h3 { font-size:16px; color:#111827; margin:10px 0 6px; }
    .sc-report--empty p { font-size:13px; max-width:56ch; margin:0 auto; line-height:1.6; }
    .sc-report__top { display:flex; align-items:center; justify-content:space-between; gap:12px; margin-bottom:16px; flex-wrap:wrap; }
    .sc-report__id { display:flex; align-items:center; gap:10px; }
    .sc-report__phone { font-size:15px; font-weight:700; color:#111827; }
    .sc-badge { font-size:11px; font-weight:600; padding:3px 10px; border-radius:99px; display:inline-flex; align-items:center; gap:4px; }
    .sc-badge--claimed { background:#E1F5EE; color:#0F6E56; }
    .sc-badge--unclaimed { background:#f3f4f6; color:#6b7280; }
    .sc-report__meta { font-size:11.5px; color:#9ca3af; }
    .sc-grid { display:grid; grid-template-columns:220px 1fr; gap:20px; }
    @media (max-width:640px){ .sc-grid { grid-template-columns:1fr; } }
    .sc-gauge-card { border:0.5px solid #e5e7eb; border-radius:14px; padding:20px; text-align:center; display:flex; flex-direction:column; align-items:center; gap:12px; }
    .sc-gauge { width:150px; height:150px; border-radius:50%; display:flex; align-items:center; justify-content:center; }
    .sc-gauge--unrated { background:#f3f4f6; }
    .sc-gauge__inner { width:116px; height:116px; background:#fff; border-radius:50%; display:flex; flex-direction:column; align-items:center; justify-content:center; box-shadow:inset 0 0 0 0.5px #eef2f6; }
    .sc-gauge__num { font-size:36px; font-weight:800; color:#111827; line-height:1; }
    .sc-gauge__of { font-size:11px; color:#9ca3af; margin-top:2px; }
    .sc-gauge__grade { font-size:34px; font-weight:800; color:#9ca3af; }
    .sc-band { font-size:12.5px; font-weight:600; padding:5px 13px; border-radius:99px; margin:0; }
    .sc-gauge__hint { font-size:11px; color:#9ca3af; margin:0; }
    .sc-detail { display:flex; flex-direction:column; gap:16px; }
    .sc-stats { display:grid; grid-template-columns:repeat(3,1fr); gap:10px; }
    @media (max-width:520px){ .sc-stats { grid-template-columns:repeat(2,1fr); } }
    .sc-stat { border:0.5px solid #e5e7eb; border-radius:12px; padding:12px; display:flex; flex-direction:column; gap:3px; }
    .sc-stat__n { font-size:18px; font-weight:700; color:#111827; }
    .sc-stat__l { font-size:10.5px; color:#6b7280; }
    .sc-detail__label { font-size:11px; text-transform:uppercase; letter-spacing:.06em; color:#9ca3af; font-weight:600; margin:0; }
    .sc-bars { display:flex; flex-direction:column; gap:9px; }
    .sc-bar { display:grid; grid-template-columns:120px 1fr 28px; align-items:center; gap:10px; font-size:12px; }
    .sc-bar__label { color:#374151; }
    .sc-bar__track { height:7px; background:#eef2f6; border-radius:99px; overflow:hidden; }
    .sc-bar__fill { display:block; height:100%; border-radius:99px; }
    .sc-bar__val { text-align:right; color:#6b7280; font-variant-numeric:tabular-nums; }
    .sc-notes { margin:0; padding-left:18px; font-size:12px; color:#6b7280; line-height:1.75; }
    .sc-report__foot { display:flex; gap:9px; font-size:11.5px; color:#6b7280; line-height:1.55; margin:18px 0 0; padding-top:14px; border-top:0.5px solid #eef2f6; }
    .sc-report__foot i { font-size:16px; color:#185FA5; flex:none; margin-top:1px; }
</style>

<script>
    var scmWillCharge = @json($scWillCharge);
    var scmCreditCost = '{{ getCurrencySymbol() }}{{ rtrim(rtrim(number_format($scPrice, 2), '0'), '.') }}';
    var scmTopupUrl   = '{{ route('owner.screening.index') }}';
    var scmLookupUrl  = '{{ route('owner.screening.lookup') }}';
    var scmToken      = '{{ csrf_token() }}';

    function scmOpen() {
        // Pull the phone already typed into the create form so the owner rarely re-types.
        var src = document.querySelector('input[name="contact_number"]');
        var box = document.getElementById('scmPhone');
        if (src && src.value.trim() && !box.value.trim()) box.value = src.value.trim();
        document.getElementById('scmResult').innerHTML = '';
        document.getElementById('scmOverlay').hidden = false;
        document.body.style.overflow = 'hidden';
        box.focus();
    }
    function scmClose() {
        document.getElementById('scmOverlay').hidden = true;
        document.body.style.overflow = '';
    }
    document.getElementById('scmOverlay').addEventListener('click', function (e) {
        if (e.target === this) scmClose();
    });

    // Re-screening the SAME number just re-shows (and could re-charge) an identical result, so
    // the button locks after a lookup. Editing the number unlocks it for a fresh/corrected screen.
    function scmResetGo() {
        var go = document.getElementById('scmGo');
        go.disabled = false; go.classList.remove('is-done');
        go.querySelector('span').textContent = '{{ __("Screen") }}';
    }
    function scmMarkDone() {
        var go = document.getElementById('scmGo');
        go.disabled = true; go.classList.add('is-done');
        go.querySelector('span').textContent = '{{ __("Screened") }}';
    }
    document.getElementById('scmPhone').addEventListener('input', scmResetGo);

    // Owner decided not to proceed (e.g. screening looked bad). Discard the half-built draft so
    // declined tenants don't accumulate as orphan data, then return to the tenant list.
    function scmDiscard() {
        var inputs = document.querySelectorAll('form.ajax input[name="id"]');
        var draftId = '';
        for (var i = 0; i < inputs.length; i++) { if (inputs[i].value && inputs[i].value.trim()) { draftId = inputs[i].value.trim(); break; } }

        var proceed = function () {
            if (draftId) {
                document.getElementById('scmDraftId').value = draftId;
                document.getElementById('scmDiscardForm').submit();
            } else {
                // No draft saved yet — just leave the wizard, nothing to clean up.
                window.location.href = '{{ route('owner.tenant.index', ['type' => 'all']) }}';
            }
        };

        if (window.csConfirm) {
            csConfirm({
                title: '{{ __("Don\'t proceed with this tenant?") }}',
                message: '{{ __("This discards the tenant you\'ve started adding. Nothing is saved and you\'re not charged.") }}',
                confirmText: '{{ __("Yes, discard") }}', cancelText: '{{ __("Keep adding") }}', tone: 'danger'
            }).then(function (ok) { if (ok) proceed(); });
        } else if (confirm('{{ __("Discard this tenant?") }}')) { proceed(); }
    }

    function scmRun() {
        var box = document.getElementById('scmPhone');
        var phone = box.value.trim();
        if (phone.length < 9) { if (window.toastr) toastr.warning('{{ __("Enter a valid phone number.") }}'); box.focus(); return; }

        var fire = function () { scmDoLookup(phone); };
        if (scmWillCharge && window.csConfirm) {
            csConfirm({
                title: '{{ __("Run this screening?") }}',
                message: '{{ __("This will use 1 screening credit") }} (' + scmCreditCost + ').',
                confirmText: '{{ __("Screen tenant") }}', cancelText: '{{ __("Cancel") }}'
            }).then(function (ok) { if (ok) fire(); });
        } else { fire(); }
    }

    function scmDoLookup(phone) {
        var go = document.getElementById('scmGo');
        var out = document.getElementById('scmResult');
        go.disabled = true; go.querySelector('span').textContent = '{{ __("Checking…") }}';
        out.innerHTML = '';

        var fd = new FormData();
        fd.append('_token', scmToken);
        fd.append('phone', phone);

        fetch(scmLookupUrl, { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }, body: fd })
            .then(function (r) { return r.json().then(function (d) { return { status: r.status, d: d }; }); })
            .then(function (res) {
                if (res.status === 200 && res.d.ok) {
                    out.innerHTML = res.d.html;
                    scmMarkDone(); // lock — result is shown; changing the number unlocks it
                } else if (res.status === 422 && res.d.topup) {
                    out.innerHTML = '<div class="scm-topup">' + (res.d.message || '{{ __("You\'re out of screening credits.") }}')
                        + '<br><a href="' + scmTopupUrl + '" target="_blank" rel="noopener">{{ __("Top up screening credits") }}</a></div>';
                    scmResetGo(); // nothing was charged — let them retry after topping up
                } else {
                    if (window.toastr) toastr.error(res.d.message || '{{ __("Something went wrong. Please try again.") }}');
                    scmResetGo();
                }
            })
            .catch(function () { if (window.toastr) toastr.error('{{ __("Something went wrong. Please try again.") }}'); scmResetGo(); });
    }
</script>
