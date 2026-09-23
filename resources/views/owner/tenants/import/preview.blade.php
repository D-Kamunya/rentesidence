@extends('owner.layouts.app')

@php
    $pageTitle = __('Import Preview');
    $summary   = $result['summary'];
    // Show all error rows first, then valid rows, capped so a huge file can't blow up the page.
    $rows = collect($result['rows'])->sortByDesc(fn ($r) => empty($r['errors']) ? 0 : 1)->values();
    $cap  = 300;
    $shown = $rows->take($cap);
    $confirmable = $result['valid'] > 0 && \Illuminate\Support\Facades\Route::has('owner.tenant.import.confirm');
@endphp

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
                                    {{ __('Preview') }}
                                </li>
                            </ol>
                        </nav>
                    </div>
                    <a href="{{ route('owner.tenant.import.index') }}" class="ti-btn ti-btn--ghost"><i class="ri-arrow-left-line"></i> {{ __('Upload another') }}</a>
                </div>

                {{-- Verdict banner — eye-catching pass / needs-attention notice --}}
                <div class="tp-hero {{ $result['errors'] ? 'tp-hero--warn' : 'tp-hero--ok' }}">
                    <div class="tp-hero__art">
                        @if ($result['errors'])
                            <svg viewBox="0 0 96 96" width="78" height="78" fill="none" aria-hidden="true">
                                <circle cx="48" cy="48" r="40" fill="#FEF0DC"/>
                                <circle cx="48" cy="48" r="27" fill="#F59E0B"/>
                                <path d="M48 35v16" stroke="#fff" stroke-width="5" stroke-linecap="round"/>
                                <circle cx="48" cy="61.5" r="2.9" fill="#fff"/>
                                <circle cx="83" cy="27" r="3" fill="#F59E0B" opacity=".45"/>
                                <circle cx="15" cy="60" r="2.4" fill="#F59E0B" opacity=".4"/>
                                <circle cx="80" cy="68" r="2" fill="#F59E0B" opacity=".35"/>
                            </svg>
                        @else
                            <svg viewBox="0 0 96 96" width="78" height="78" fill="none" aria-hidden="true">
                                <circle cx="48" cy="48" r="40" fill="#DCF3E9"/>
                                <circle cx="48" cy="48" r="27" fill="#1D9E75"/>
                                <path d="M37 48.5l7.5 7.5L60 41" stroke="#fff" stroke-width="5" stroke-linecap="round" stroke-linejoin="round"/>
                                <circle cx="83" cy="26" r="3" fill="#1D9E75" opacity=".5"/>
                                <circle cx="15" cy="61" r="2.5" fill="#1D9E75" opacity=".4"/>
                                <circle cx="80" cy="67" r="2" fill="#1D9E75" opacity=".35"/>
                            </svg>
                        @endif
                    </div>
                    <div class="tp-hero__body">
                        @if ($result['errors'])
                            <h3 class="tp-hero__title">{{ __('A few rows need your attention') }}</h3>
                            <p class="tp-hero__sub">{{ __(':valid row(s) are ready to import, but :errors need a quick fix. Scroll down to see exactly what to change — you can still import the good ones now.', ['valid' => $result['valid'], 'errors' => $result['errors']]) }}</p>
                        @else
                            <h3 class="tp-hero__title">{{ __('Everything checks out') }}</h3>
                            <p class="tp-hero__sub">{{ __('All :valid row(s) passed validation with no problems. Review the details below and confirm when you\'re ready.', ['valid' => $result['valid']]) }}</p>
                        @endif
                    </div>
                </div>

                {{-- Summary --}}
                <div class="tp-stats">
                    <div class="tp-stat"><span class="tp-stat__n">{{ number_format($summary['total']) }}</span><span class="tp-stat__l">{{ __('Rows') }}</span></div>
                    <div class="tp-stat tp-stat--ok"><span class="tp-stat__n">{{ number_format($result['valid']) }}</span><span class="tp-stat__l">{{ __('Ready to import') }}</span></div>
                    <div class="tp-stat {{ $result['errors'] ? 'tp-stat--bad' : '' }}"><span class="tp-stat__n">{{ number_format($result['errors']) }}</span><span class="tp-stat__l">{{ __('Need fixing') }}</span></div>
                    <div class="tp-stat"><span class="tp-stat__n">{{ number_format($summary['new_tenants']) }}</span><span class="tp-stat__l">{{ __('New tenants') }}</span></div>
                    <div class="tp-stat"><span class="tp-stat__n">{{ number_format($summary['new_props']) }}</span><span class="tp-stat__l">{{ __('New properties') }}</span></div>
                    <div class="tp-stat"><span class="tp-stat__n">{{ number_format($summary['new_units']) }}</span><span class="tp-stat__l">{{ __('New units') }}</span></div>
                </div>

                @foreach ($summary['warnings'] ?? [] as $warn)
                    <div class="ti-alert ti-alert--warn">{{ $warn }}</div>
                @endforeach

                @if (!empty($unmatched))
                    <div class="ti-alert ti-alert--warn">
                        {{ __('These columns weren\'t recognised and will be ignored:') }} <strong>{{ implode(', ', $unmatched) }}</strong>
                    </div>
                @endif

                @if (session('error'))
                    <div class="ti-alert ti-alert--error">
                        {{ session('error') }}
                        @if (session('sms_topup'))
                            <a href="{{ route('owner.sms.credits.index') }}" class="tp-topup-link">{{ __('Top up SMS credits') }} →</a>
                        @endif
                    </div>
                @endif

                {{-- Invite options + confirm --}}
                @php
                    $smsNeed = (int) ($summary['sms_invites'] ?? 0);
                    $emailNeed = (int) ($summary['email_invites'] ?? 0);
                    $newTenants = (int) ($summary['new_tenants'] ?? 0);
                    $smsShort = max(0, $smsNeed - (int) $smsBalance);
                    $emailMissing = max(0, $newTenants - $emailNeed); // new tenants with no email
                @endphp
                <div class="tp-confirm">
                    <div class="tp-confirm__head">
                        @if ($result['errors'])
                            <div class="tp-confirm__errline">
                                <span><i class="ri-error-warning-line" style="color:#B45309;"></i>
                                    {{ __(':valid row(s) will import. :errors row(s) have problems and will be skipped.', ['valid' => $result['valid'], 'errors' => $result['errors']]) }}</span>
                                <a href="{{ route('owner.tenant.import.errors', $import->id) }}" class="ti-btn ti-btn--ghost tp-errdl"><i class="ri-download-2-line"></i> {{ __('Download error list') }}</a>
                            </div>
                            <p class="tp-confirm__fixhint">
                                {{ __('To fix them: open your original spreadsheet, correct the rows listed above (the row numbers match your file), then upload it again with') }}
                                <a href="{{ route('owner.tenant.import.index') }}">{{ __('Upload another') }}</a>.
                                {{ __('You can import the good rows now and add the rest later — nothing is lost.') }}
                            </p>
                        @else
                            <i class="ri-checkbox-circle-line" style="color:#0F6E56;"></i>
                            {{ __('All :valid row(s) passed validation and are ready to import.', ['valid' => $result['valid']]) }}
                        @endif
                    </div>

                    @if ($confirmable)
                        <form action="{{ route('owner.tenant.import.confirm', $import->id) }}" method="POST" id="tpConfirmForm"
                              data-cs-confirm="{{ __('You\'re about to import :n row(s). Accounts will be created and login invites sent by your chosen channel.', ['n' => $result['valid']]) }}"
                              data-cs-confirm-title="{{ __('Start the import?') }}"
                              data-cs-confirm-ok="{{ __('Yes, import') }}">
                            @csrf
                            <p class="tp-confirm__label">{{ __('Welcome your tenants to the system') }}</p>
                            <p class="tp-confirm__sublabel">{{ __('Each new tenant gets a login and a short message explaining that you now manage their rent here. Choose how to reach them:') }}</p>
                            <div class="tp-channels">
                                <label class="tp-channel"><input type="radio" name="invite_channel" value="email" checked> <span><i class="ri-mail-line"></i> {{ __('Email only') }} <small>{{ __('free') }}</small></span></label>
                                <label class="tp-channel"><input type="radio" name="invite_channel" value="sms"> <span><i class="ri-message-2-line"></i> {{ __('SMS only') }} <small>{{ __('uses credits') }}</small></span></label>
                                <label class="tp-channel"><input type="radio" name="invite_channel" value="both"> <span><i class="ri-notification-badge-line"></i> {{ __('SMS + Email') }}</span></label>
                                <label class="tp-channel"><input type="radio" name="invite_channel" value="none"> <span><i class="ri-close-circle-line"></i> {{ __('Don\'t notify yet') }}</span></label>
                            </div>

                            {{-- SMS budget check (only relevant when SMS is chosen) --}}
                            <div class="tp-smscheck" id="tpSmsCheck" style="display:none;">
                                <div class="tp-smscheck__row">
                                    <span>{{ __('SMS invites needed') }}</span><strong>{{ number_format($smsNeed) }}</strong>
                                </div>
                                <div class="tp-smscheck__row">
                                    <span>{{ __('Your SMS credits') }}</span><strong>{{ number_format($smsBalance) }}</strong>
                                </div>
                                @if ($smsShort > 0)
                                    <div class="tp-smscheck__warn">
                                        <i class="ri-error-warning-line"></i>
                                        {{ __('You are :short SMS credit(s) short. Top up before importing, or choose Email.', ['short' => $smsShort]) }}
                                        <a href="{{ route('owner.sms.credits.index') }}" class="tp-topup-link">{{ __('Top up') }} →</a>
                                    </div>
                                @else
                                    <div class="tp-smscheck__ok"><i class="ri-checkbox-circle-line"></i> {{ __('You have enough SMS credits for these invites.') }}</div>
                                @endif
                            </div>

                            {{-- Email coverage note — some new tenants may have no email address --}}
                            <div class="tp-emailcheck" id="tpEmailCheck" style="display:none;"></div>

                            <button type="submit" class="ti-btn ti-btn--primary tp-confirm__btn" id="tpConfirmBtn">
                                <i class="ri-check-double-line"></i> {{ __('Confirm & import :n row(s)', ['n' => $result['valid']]) }}
                            </button>
                        </form>
                    @else
                        <button type="button" class="ti-btn ti-btn--primary" disabled>
                            <i class="ri-check-double-line"></i> {{ __('Nothing to import') }}
                        </button>
                    @endif
                </div>

                {{-- Rows --}}
                <div class="table-responsive tp-table-wrap">
                    <table class="ti-table tp-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>{{ __('Property / Unit') }}</th>
                                <th>{{ __('Tenant') }}</th>
                                <th>{{ __('Phone') }}</th>
                                <th>{{ __('Rent') }}</th>
                                <th>{{ __('Opening bal.') }}</th>
                                <th>{{ __('Result') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($shown as $r)
                                @php $d = $r['data']; $bad = !empty($r['errors']); @endphp
                                <tr class="{{ $bad ? 'tp-row--bad' : '' }}">
                                    <td>{{ $r['line'] }}</td>
                                    <td>{{ $d['property_name'] ?? '' }} <span class="tp-muted">/ {{ $d['unit_name'] ?? '' }}</span></td>
                                    <td>{{ trim(($d['tenant_first_name'] ?? '') . ' ' . ($d['tenant_last_name'] ?? '')) }}</td>
                                    <td>{{ $d['tenant_phone'] ?? '' }}</td>
                                    <td>{{ $d['rent_amount'] ?? '' }}</td>
                                    <td>{{ $d['opening_balance'] ?? '' }}</td>
                                    <td>
                                        @if ($bad)
                                            <span class="tp-badge tp-badge--error">{{ __('Error') }}</span>
                                            <ul class="tp-errs">
                                                @foreach ($r['errors'] as $err)<li>{{ $err }}</li>@endforeach
                                            </ul>
                                        @elseif ($r['action'] === 'update')
                                            <span class="tp-badge tp-badge--update">{{ __('Update existing') }}</span>
                                        @else
                                            <span class="tp-badge tp-badge--create">{{ __('Create') }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if ($rows->count() > $cap)
                    <p class="tp-muted mt-2">{{ __('Showing the first :cap of :total rows (all errors are shown first).', ['cap' => $cap, 'total' => $rows->count()]) }}</p>
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
    .tp-hero { display:flex; align-items:center; gap:20px; padding:20px 24px; border-radius:16px; margin-bottom:20px; border:0.5px solid; }
    .tp-hero--ok { background:linear-gradient(135deg,#F2FCF8 0%,#E1F5EE 100%); border-color:#A7DFC9; }
    .tp-hero--warn { background:linear-gradient(135deg,#FFFBF4 0%,#FEF3E7 100%); border-color:#F6D9AE; }
    .tp-hero__art { flex:none; line-height:0; animation:tpPop .5s cubic-bezier(.2,.8,.3,1.25) both; }
    .tp-hero__title { font-size:18px; font-weight:700; margin:0 0 4px; }
    .tp-hero--ok .tp-hero__title { color:#0F6E56; }
    .tp-hero--warn .tp-hero__title { color:#92400E; }
    .tp-hero__sub { font-size:13.5px; color:#4b5563; margin:0; line-height:1.6; max-width:640px; }
    @keyframes tpPop { from { transform:scale(.55); opacity:0; } to { transform:scale(1); opacity:1; } }
    @media (max-width:600px){ .tp-hero { flex-direction:column; text-align:center; } }
    @media (prefers-reduced-motion: reduce){ .tp-hero__art { animation:none; } }
    .tp-stats { display:grid; grid-template-columns:repeat(6, 1fr); gap:12px; margin-bottom:18px; }
    @media (max-width:900px){ .tp-stats { grid-template-columns:repeat(3, 1fr); } }
    .tp-stat { border:0.5px solid #e5e7eb; border-radius:12px; padding:14px 16px; display:flex; flex-direction:column; gap:2px; background:#fff; }
    .tp-stat__n { font-size:22px; font-weight:700; color:#111827; }
    .tp-stat__l { font-size:11.5px; color:#6b7280; }
    .tp-stat--ok .tp-stat__n { color:#0F6E56; }
    .tp-stat--bad .tp-stat__n { color:#B45309; }
    .ti-alert { padding:12px 16px; border-radius:10px; font-size:13.5px; margin-bottom:14px; }
    .ti-alert--warn { background:#FEF3E7; color:#92400E; border:0.5px solid #F6D9AE; }
    .tp-actions { display:flex; align-items:center; justify-content:space-between; gap:16px; flex-wrap:wrap; padding:16px; border:0.5px solid #e5e7eb; border-radius:12px; background:#fafbfc; margin-bottom:18px; }
    .tp-actions__note { font-size:13.5px; color:#374151; display:flex; align-items:center; gap:8px; }
    .ti-btn { display:inline-flex; align-items:center; gap:7px; border-radius:9px; font-size:13.5px; font-weight:600; padding:10px 18px; cursor:pointer; border:0.5px solid transparent; text-decoration:none; }
    .ti-btn--primary { background:#185FA5; color:#fff; }
    .ti-btn--primary:hover { background:#0F4A84; }
    .ti-btn--primary:disabled { background:#9db8d6; cursor:not-allowed; }
    .ti-btn--ghost { background:#fff; color:#185FA5; border:0.5px solid #cdd5df; }
    .tp-table-wrap { border:0.5px solid #eef2f6; border-radius:12px; }
    .ti-table { width:100%; border-collapse:collapse; font-size:13px; }
    .ti-table th { text-align:left; font-size:11px; text-transform:uppercase; letter-spacing:.05em; color:#9ca3af; padding:9px 12px; border-bottom:0.5px solid #eef2f6; white-space:nowrap; }
    .ti-table td { padding:10px 12px; border-bottom:0.5px solid #f5f7f9; color:#374151; vertical-align:top; }
    .tp-row--bad { background:#FEFAF7; }
    .tp-muted { color:#9ca3af; font-size:12px; }
    .tp-badge { font-size:11px; font-weight:600; padding:2px 9px; border-radius:99px; }
    .tp-badge--create { background:#E1F5EE; color:#0F6E56; }
    .tp-badge--update { background:#E6F1FB; color:#185FA5; }
    .tp-badge--error  { background:#FAECE7; color:#993C1D; }
    .tp-errs { margin:6px 0 0; padding-left:16px; color:#993C1D; font-size:12px; line-height:1.6; }
    .ti-alert--error { background:#FAECE7; color:#993C1D; border:0.5px solid #F5C4B3; }
    .tp-topup-link { font-weight:600; color:#185FA5; text-decoration:none; white-space:nowrap; margin-left:6px; }
    .tp-confirm { border:0.5px solid #e5e7eb; border-radius:12px; padding:18px; background:#fafbfc; margin-bottom:18px; }
    .tp-confirm__head { font-size:13.5px; color:#374151; margin-bottom:14px; }
    .tp-confirm__errline { display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap; }
    .tp-confirm__errline > span { display:flex; align-items:center; gap:8px; }
    .tp-errdl { flex:none; }
    .tp-confirm__fixhint { font-size:12.5px; color:#6b7280; margin:10px 0 0; line-height:1.7; background:#f9fafb; border:0.5px solid #eef2f6; border-radius:8px; padding:10px 12px; }
    .tp-confirm__fixhint a { color:#185FA5; font-weight:600; text-decoration:none; }
    .tp-confirm__label { font-size:14px; font-weight:600; color:#111827; margin:0 0 3px; }
    .tp-confirm__sublabel { font-size:12.5px; color:#6b7280; margin:0 0 10px; line-height:1.6; }
    .tp-channels { display:flex; flex-wrap:wrap; gap:10px; margin-bottom:12px; }
    .tp-channel { cursor:pointer; }
    .tp-channel input { position:absolute; opacity:0; }
    .tp-channel span { display:inline-flex; align-items:center; gap:7px; padding:8px 14px; border:0.5px solid #cdd5df; border-radius:9px; font-size:13px; color:#374151; background:#fff; transition:all .15s; }
    .tp-channel span small { color:#9ca3af; font-size:11px; }
    .tp-channel input:checked + span { border-color:#185FA5; background:#E6F1FB; color:#0C447C; font-weight:600; }
    .tp-smscheck { border:0.5px solid #e5e7eb; border-radius:9px; padding:12px 14px; background:#fff; margin-bottom:14px; max-width:420px; }
    .tp-smscheck__row { display:flex; justify-content:space-between; font-size:13px; color:#4b5563; padding:2px 0; }
    .tp-smscheck__warn { margin-top:8px; font-size:12.5px; color:#92400E; background:#FEF3E7; border-radius:8px; padding:8px 10px; display:flex; align-items:center; gap:6px; flex-wrap:wrap; }
    .tp-smscheck__ok { margin-top:8px; font-size:12.5px; color:#0F6E56; display:flex; align-items:center; gap:6px; }
    .tp-emailcheck { font-size:12.5px; border-radius:9px; padding:10px 12px; margin-bottom:14px; max-width:460px; display:flex; align-items:center; gap:7px; line-height:1.5; }
    .tp-emailcheck--warn { background:#FEF3E7; color:#92400E; border:0.5px solid #F6D9AE; }
    .tp-emailcheck--info { background:#E6F1FB; color:#0C447C; border:0.5px solid #B5D4F4; }
    .tp-confirm__btn:disabled { background:#9db8d6; cursor:not-allowed; }
</style>

<script>
(function () {
    var form = document.getElementById('tpConfirmForm');
    if (!form) return;
    var smsCheck = document.getElementById('tpSmsCheck');
    var emailCheck = document.getElementById('tpEmailCheck');
    var btn      = document.getElementById('tpConfirmBtn');
    var short    = {{ $smsShort ?? 0 }};
    var emailMissing = {{ $emailMissing ?? 0 }};
    var msgEmailOnly = @json(__('new tenant(s) have no email address and will NOT be notified. Choose SMS or SMS + Email to reach them by phone (phone is always present).'));
    var msgBoth = @json(__("tenant(s) have no email — they'll be invited by SMS instead."));

    function sync() {
        var val = (form.querySelector('input[name="invite_channel"]:checked') || {}).value;
        var usesSms   = (val === 'sms' || val === 'both');
        var usesEmail = (val === 'email' || val === 'both');
        smsCheck.style.display = usesSms ? 'block' : 'none';

        // Email coverage: warn when the chosen channel relies on email but some tenants have none.
        if (usesEmail && emailMissing > 0) {
            emailCheck.style.display = 'block';
            if (val === 'email') {
                emailCheck.className = 'tp-emailcheck tp-emailcheck--warn';
                emailCheck.innerHTML = '<i class="ri-error-warning-line"></i> ' + emailMissing.toLocaleString() + ' ' + msgEmailOnly;
            } else {
                emailCheck.className = 'tp-emailcheck tp-emailcheck--info';
                emailCheck.innerHTML = '<i class="ri-information-line"></i> ' + emailMissing.toLocaleString() + ' ' + msgBoth;
            }
        } else {
            emailCheck.style.display = 'none';
        }

        // Block confirm only when SMS is chosen but credits are short — forces a top-up or Email.
        // (Missing emails are a warning, not a block: the owner may notify those tenants later.)
        btn.disabled = usesSms && short > 0;
    }
    form.querySelectorAll('input[name="invite_channel"]').forEach(function (r) {
        r.addEventListener('change', sync);
    });
    sync();
})();
</script>
@endsection
