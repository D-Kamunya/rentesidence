@extends('owner.layouts.app')

@section('content')

<div id="mpesa-preloader" style="display:none;">
    <div id="mpesa-preloaderInner">
        <img src="{{ asset('assets/images/gateway-icon/mpesa.jpg') }}" alt="M-PESA">
        <div>
            <p>{{ __('Please follow the instructions and do not refresh or leave this page.') }}</p>
            <p>{{ __('This may take up to') }} <span id="mpesa-timer">2:00</span> {{ __('minute(s).') }}</p>
            <p>{{ __('You will receive a prompt on your mobile number to enter your PIN to authorize payment.') }}</p>
            <p>{{ __('Please ensure your phone is on and unlocked. Thank you.') }}</p>
        </div>
        <img src="{{ asset('assets/images/loading.svg') }}" alt="Loading">
    </div>
</div>

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="page-content-wrapper bg-white p-30 radius-20">
                <div class="container">
                    @php
                        $pageTitle = 'SMS Credits';
                    @endphp
                    {{-- Page header --}}
                    <div class="sms-page-header mb-4">
                        <div>
                            <h2 class="sms-page-title">{{ __('SMS Credits') }}</h2>
                            <nav aria-label="breadcrumb">
                                <ol class="sms-breadcrumb">
                                    <li>
                                        <a href="{{ route('owner.dashboard') }}">{{ __('Dashboard') }}</a>
                                        <svg width="8" height="8" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                            <polyline points="9 18 15 12 9 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </li>
                                    <li aria-current="page">{{ __('SMS Credits') }}</li>
                                </ol>
                            </nav>
                        </div>
                    </div>

                    @foreach(['success','error','info'] as $type)
                        @if(session($type))
                            <div class="sms-alert sms-alert--{{ $type }} mb-4">{{ session($type) }}</div>
                        @endif
                    @endforeach

                    {{-- Summary strip --}}
                    <div class="sms-strip mb-4">
                        <div class="sms-strip__item">
                            <span class="sms-strip__dot sms-strip__dot--{{ $balance <= $lowThreshold ? 'red' : 'blue' }}"></span>
                            <div>
                                <div class="sms-strip__label">{{ __('Credits Remaining') }}</div>
                                <div class="sms-strip__value sms-strip__value--{{ $balance <= $lowThreshold ? 'red' : 'blue' }}">{{ number_format($balance) }}</div>
                            </div>
                        </div>
                        <div class="sms-strip__divider"></div>
                        <div class="sms-strip__item">
                            <span class="sms-strip__dot sms-strip__dot--green"></span>
                            <div>
                                <div class="sms-strip__label">{{ __('SMS Sent') }}</div>
                                <div class="sms-strip__value sms-strip__value--green">{{ number_format($stats->total_sent ?? 0) }}</div>
                            </div>
                        </div>
                        <div class="sms-strip__divider"></div>
                        <div class="sms-strip__item">
                            <span class="sms-strip__dot sms-strip__dot--red"></span>
                            <div>
                                <div class="sms-strip__label">{{ __('Failed / Blocked') }}</div>
                                <div class="sms-strip__value sms-strip__value--red">{{ number_format($stats->total_failed ?? 0) }}</div>
                            </div>
                        </div>
                        <div class="sms-strip__divider"></div>
                        <div class="sms-strip__item">
                            <span class="sms-strip__dot sms-strip__dot--purple"></span>
                            <div>
                                <div class="sms-strip__label">{{ __('Total Received') }}</div>
                                <div class="sms-strip__value sms-strip__value--purple">{{ number_format($stats->total_purchased ?? 0) }}</div>
                            </div>
                        </div>
                    </div>

                    {{-- Low-balance warning badge --}}
                    @if($balance <= $lowThreshold)
                    <div class="sms-alert sms-alert--warn mb-4">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" style="flex-shrink:0;">
                            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.8"/>
                            <line x1="12" y1="8" x2="12" y2="12" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                            <line x1="12" y1="16" x2="12.01" y2="16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                        {{ __('Low balance — top up now to avoid missed messages.') }}
                    </div>
                    @endif

                    {{-- Purchase panel --}}
                    <div class="ow-card sms-topup-card mb-4">
                        <div class="sms-panel-head">
                            <div class="sms-panel-icon sms-panel-icon--green">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none">
                                    <line x1="12" y1="5" x2="12" y2="19" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                    <line x1="5" y1="12" x2="19" y2="12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                </svg>
                            </div>
                            <span class="sms-panel-title">{{ __('Purchase Credits') }}</span>
                        </div>

                        <p class="sms-price-note">
                            {{ __('Price:') }} <strong>KSh {{ number_format($pricePerSms, 2) }}</strong> {{ __('per SMS credit') }}
                        </p>

                        <div class="sms-quickpick mb-3">
                            @foreach([50, 100, 250, 500, 1000, 1500, 2000] as $pack)
                                <button type="button" class="sms-pack-btn" data-qty="{{ $pack }}">
                                    <span class="sms-pack-qty">{{ $pack }}</span>
                                    <span class="sms-pack-sub">{{ __('credits') }}</span>
                                    <span class="sms-pack-price">KSh {{ number_format($pack * $pricePerSms, 2) }}</span>
                                </button>
                            @endforeach
                        </div>

                        <div class="sms-custom-row">
                            <input type="number" id="pageCustomQty" class="sms-custom-input"
                                   placeholder="{{ __('Custom qty (min 10)') }}" min="10">
                            <span class="sms-custom-total">KSh <span id="pageCustomTotal">0.00</span></span>
                        </div>

                        <div class="sms-phone-row">
                            <label class="sms-field-label">
                                {{ __('M-Pesa Phone Number') }}
                                <span class="sms-field-hint"> — {{ __('edit if different from account') }}</span>
                            </label>
                            <div class="sms-phone-input-wrap">
                                <span class="sms-phone-flag">🇰🇪</span>
                                <input type="tel" id="pagePhone"
                                       value="{{ auth()->user()->contact_number ?? '' }}"
                                       class="sms-phone-input">
                            </div>
                        </div>

                        <button type="button" class="ow-btn ow-btn--primary sms-buy-btn" id="pageBuyBtn" disabled>
                            <img src="{{ asset('assets/images/gateway-icon/mpesa.jpg') }}"
                                 alt="" style="width:16px;height:16px;border-radius:3px;object-fit:cover;">
                            {{ __('Buy via M-Pesa') }}
                        </button>
                    </div>

                    {{-- Failed messages --}}
                    @if($failedMessages->isNotEmpty())
                    <div class="ow-card sms-section-card mb-4">
                        <div class="dash-card__head">
                            <div style="display:flex;align-items:center;gap:10px;">
                                <div class="sms-panel-icon sms-panel-icon--red">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none">
                                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.8"/>
                                        <line x1="12" y1="8" x2="12" y2="12" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                        <line x1="12" y1="16" x2="12.01" y2="16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                    </svg>
                                </div>
                                <span class="sms-panel-title">
                                    {{ __('Failed Messages') }}
                                    <span class="ow-badge ow-badge--danger" style="margin-left:6px;">{{ $failedMessages->count() }}</span>
                                </span>
                            </div>
                            <form action="{{ route('owner.sms.credits.retry.all') }}" method="POST" style="margin-left:auto;">
                                @csrf
                                <button type="submit" class="ow-btn ow-btn--danger">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none">
                                        <polyline points="23 4 23 10 17 10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    {{ __('Retry All') }}
                                </button>
                            </form>
                        </div>

                        <p class="sms-price-note" style="padding:12px 20px 0;">{{ __('These messages were blocked because your credits ran out. Retry them individually or all at once.') }}</p>

                        <div class="table-responsive">
                            <table class="sms-table">
                                <thead>
                                    <tr>
                                        <th>{{ __('Date') }}</th>
                                        <th>{{ __('Mobile') }}</th>
                                        <th>{{ __('Message') }}</th>
                                        <th>{{ __('Action') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($failedMessages as $msg)
                                    <tr>
                                        <td style="white-space:nowrap;">{{ $msg->created_at->format('d M Y H:i') }}</td>
                                        <td>{{ $msg->mobile }}</td>
                                        <td class="sms-desc">{{ $msg->message }}</td>
                                        <td>
                                            <form action="{{ route('owner.sms.credits.retry.one') }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="sms_history_id" value="{{ $msg->id }}">
                                                <button type="submit" class="ow-btn ow-btn--primary">
                                                    {{ __('Retry') }}
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif

                    {{-- Credit history --}}
                    <div class="ow-card sms-section-card">
                        <div class="dash-card__head">
                            <div class="sms-panel-icon sms-panel-icon--blue">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"
                                          stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                    <polyline points="14 2 14 8 20 8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                </svg>
                            </div>
                            <span class="sms-panel-title">{{ __('Credit History') }}</span>
                        </div>

                        <div class="table-responsive">
                            <table class="sms-table">
                                <thead>
                                    <tr>
                                        <th>{{ __('Date') }}</th>
                                        <th>{{ __('Type') }}</th>
                                        <th>{{ __('Credits') }}</th>
                                        <th>{{ __('Amount Paid') }}</th>
                                        <th>{{ __('Balance After') }}</th>
                                        <th>{{ __('Description') }}</th>
                                        <th>{{ __('Status') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($transactions as $tx)
                                    <tr>
                                        <td style="white-space:nowrap;">{{ $tx->created_at->format('d M Y H:i') }}</td>
                                        <td>
                                            <span class="ow-badge ow-badge--type-{{ $tx->type }}">
                                                {{ ucfirst(str_replace('_', ' ', $tx->type)) }}
                                            </span>
                                        </td>
                                        <td class="{{ $tx->type === 'deduct' ? 'sms-col--deduct' : 'sms-col--credit' }}">
                                            {{ $tx->type === 'deduct' ? '-' : '+' }}{{ $tx->quantity }}
                                        </td>
                                        <td>{{ $tx->amount_paid ? 'KSh '.number_format($tx->amount_paid,2) : '—' }}</td>
                                        <td>{{ number_format($tx->balance_after) }}</td>
                                        <td class="sms-desc">{{ $tx->description ?? '—' }}</td>
                                        <td>
                                            <span class="ow-badge ow-badge--{{ $tx->status === 'success' ? 'paid' : ($tx->status === 'pending' ? 'pending' : 'danger') }}">
                                                {{ ucfirst($tx->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7" class="sms-empty">{{ __('No transactions yet') }}</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="sms-pagination">{{ $transactions->links() }}</div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('style')
<style>
/* ── Design system tokens ─────────────────────────────────────── */
:root {
    --blue:          #185FA5;
    --blue-hover:    #0F4A84;
    --blue-light:    #E6F1FB;
    --blue-border:   #B5D4F4;
    --blue-faint:    #185ea56e;
    --blue-ghost:    #185ea51c;
    --green:         #1D9E75;
    --green-dark:    #0F6E56;
    --green-light:   #E1F5EE;
    --amber:         #854F0B;
    --amber-light:   #FAEEDA;
    --amber-border:  #F5D9A8;
    --red:           #993C1D;
    --red-light:     #FAECE7;
    --purple:        #534AB7;
    --purple-hover:  #3C3489;
    --gray-900:      #111827;
    --gray-800:      #1f2937;
    --gray-700:      #374151;
    --gray-500:      #6b7280;
    --gray-400:      #9ca3af;
    --gray-200:      #e5e7eb;
    --gray-100:      #f3f4f6;
    --gray-50:       #fafafa;
    --white:         #ffffff;
}

/* ── Page header ──────────────────────────────────────────────── */
.sms-page-header { display:flex; align-items:flex-start; justify-content:space-between; flex-wrap:wrap; gap:1rem; }
.sms-page-title  { font-size:22px; font-weight:500; color:var(--gray-900); margin:0 0 6px; }

.sms-breadcrumb  { display:flex; align-items:center; gap:6px; list-style:none; padding:0; margin:0; font-size:12px; color:var(--gray-400); }
.sms-breadcrumb li { display:flex; align-items:center; gap:6px; }
.sms-breadcrumb a { color:var(--blue); font-weight:500; text-decoration:none; }
.sms-breadcrumb svg { color:var(--gray-400); }

/* ── Alerts ───────────────────────────────────────────────────── */
.sms-alert { display:flex; align-items:center; gap:8px; padding:12px 16px; border-radius:8px; font-size:13px; font-weight:500; border:0.5px solid transparent; }
.sms-alert--success { background:var(--green-light);  color:var(--green-dark); border-color:#A7DFC9; }
.sms-alert--error   { background:var(--red-light);    color:var(--red);        border-color:#F5B9A8; }
.sms-alert--info    { background:var(--blue-light);   color:var(--blue);       border-color:var(--blue-border); }
.sms-alert--warn    { background:var(--amber-light);  color:var(--amber);      border-color:var(--amber-border); }

/* ── Summary strip ────────────────────────────────────────────── */
.sms-strip { display:flex; align-items:center; background:var(--white); border:0.5px solid var(--gray-200); border-radius:12px; overflow:hidden; flex-wrap:wrap; }
.sms-strip__item { display:flex; align-items:center; gap:10px; padding:14px 20px; flex:1; min-width:140px; }
.sms-strip__divider { width:0.5px; align-self:stretch; background:var(--gray-200); }
.sms-strip__dot { width:8px; height:8px; border-radius:50%; flex-shrink:0; }
.sms-strip__dot--blue   { background:var(--blue); }
.sms-strip__dot--green  { background:var(--green); }
.sms-strip__dot--red    { background:var(--red); }
.sms-strip__dot--purple { background:var(--purple); }
.sms-strip__label { font-size:10px; font-weight:500; text-transform:uppercase; letter-spacing:.07em; color:var(--gray-400); margin-bottom:4px; }
.sms-strip__value { font-size:18px; font-weight:600; }
.sms-strip__value--blue   { color:var(--blue); }
.sms-strip__value--green  { color:var(--green); }
.sms-strip__value--red    { color:var(--red); }
.sms-strip__value--purple { color:var(--purple); }

/* ── Outer card (shared) ──────────────────────────────────────── */
.ow-card {
    background:var(--white);
    border:0.5px solid var(--blue-faint);
    border-radius:12px;
    overflow:hidden;
    box-shadow: 0 4px 12px rgba(0,0,0,.04), 0 0 0 1px rgba(24,95,165,.05), 0 6px 18px rgba(24,95,165,.06);
    transition:all .25s ease;
}
.ow-card:hover {
    border-color:var(--blue);
    transform:translateY(-2px);
    box-shadow: 0 10px 25px rgba(0,0,0,.06), 0 0 0 1px rgba(24,95,165,.12), 0 12px 30px rgba(24,95,165,.18);
}

/* ── Card head (table panels) ─────────────────────────────────── */
.dash-card__head { display:flex; align-items:center; gap:10px; padding:.75rem 1.1rem; border-bottom:0.5px solid var(--gray-200); background:var(--gray-50); }

/* ── Panel icon ───────────────────────────────────────────────── */
.sms-panel-icon { width:28px; height:28px; border-radius:7px; display:inline-flex; align-items:center; justify-content:center; flex-shrink:0; }
.sms-panel-icon--green  { background:var(--green-light); color:var(--green); }
.sms-panel-icon--blue   { background:var(--blue-light);  color:var(--blue); }
.sms-panel-icon--red    { background:var(--red-light);   color:var(--red); }
.sms-panel-title { font-size:14px; font-weight:500; color:var(--gray-900); }

/* ── Top-up card extras ───────────────────────────────────────── */
.sms-topup-card { padding:20px; }
.sms-panel-head { display:flex; align-items:center; gap:10px; padding-bottom:12px; border-bottom:0.5px solid var(--gray-200); margin-bottom:16px; }
.sms-price-note { font-size:13px; color:var(--gray-500); margin:0 0 14px; }
.sms-price-note strong { color:var(--gray-800); }

/* ── Quick-pick pack buttons ──────────────────────────────────── */
.sms-quickpick { display:flex; flex-wrap:wrap; gap:10px; }
.sms-pack-btn {
    display:flex; flex-direction:column; align-items:center;
    padding:10px 16px; border:0.5px solid var(--gray-200); border-radius:10px;
    background:var(--white); cursor:pointer; transition:all .15s; gap:2px;
}
.sms-pack-btn:hover, .sms-pack-btn.active {
    border-color:var(--blue); background:var(--blue-light);
}
.sms-pack-qty   { font-size:18px; font-weight:700; color:var(--gray-900); }
.sms-pack-sub   { font-size:10px; font-weight:500; text-transform:uppercase; letter-spacing:.07em; color:var(--gray-400); }
.sms-pack-price { font-size:12px; font-weight:500; color:var(--blue); margin-top:2px; }

/* ── Custom quantity row ──────────────────────────────────────── */
.sms-custom-row { display:flex; align-items:center; gap:10px; margin-bottom:14px; flex-wrap:wrap; }
.sms-custom-input {
    width:180px; padding:7px 10px; border:0.5px solid var(--gray-200); border-radius:7px;
    font-size:13px; color:var(--gray-700); outline:none; transition:border-color .15s, box-shadow .15s;
}
.sms-custom-input:focus { border-color:var(--blue); box-shadow:0 0 0 3px rgba(24,95,165,.1); }
.sms-custom-total { font-size:14px; font-weight:600; color:var(--blue); white-space:nowrap; }
@media(max-width:480px){
    .sms-custom-input { width:100%; }
    .sms-custom-total { width:100%; }
}

/* ── Phone field ──────────────────────────────────────────────── */
.sms-phone-row { margin:14px 0 18px; }
.sms-field-label { display:block; font-size:10px; font-weight:500; text-transform:uppercase; letter-spacing:.07em; color:var(--gray-400); margin-bottom:6px; }
.sms-field-hint { font-size:10px; font-weight:400; color:var(--gray-400); text-transform:none; letter-spacing:0; }
.sms-phone-input-wrap {
    display:flex; align-items:center; border:0.5px solid var(--gray-200); border-radius:7px; overflow:hidden; max-width:280px;
    transition:border-color .15s, box-shadow .15s;
}
.sms-phone-input-wrap:focus-within { border-color:var(--blue); box-shadow:0 0 0 3px rgba(24,95,165,.1); }
.sms-phone-flag { padding:0 10px; font-size:13px; color:var(--gray-500); background:var(--gray-50); border-right:0.5px solid var(--gray-200); height:36px; display:flex; align-items:center; }
.sms-phone-input { flex:1; padding:7px 10px; border:none; outline:none; font-size:13px; color:var(--gray-700); }

/* ── Buttons ──────────────────────────────────────────────────── */
.ow-btn {
    display:inline-flex; align-items:center; gap:6px;
    font-size:12px; font-weight:500; padding:7px 15px;
    border-radius:7px; border:none; cursor:pointer;
    transition:all .13s;
}
.ow-btn--primary { background:var(--blue); color:var(--white); }
.ow-btn--primary:hover:not(:disabled) { background:var(--blue-hover); transform:translateY(-1px); }
.ow-btn--danger  { background:var(--red-light); color:var(--red); border:0.5px solid #F5B9A8; }
.ow-btn--danger:hover  { background:var(--red); color:var(--white); transform:translateY(-1px); }
.ow-btn:disabled { opacity:.5; cursor:not-allowed; }

.sms-buy-btn { font-size:13px; font-weight:600; padding:10px 22px; border-radius:7px; }

/* ── Section card (panels with table) ────────────────────────── */
.sms-section-card { padding:0; }

/* ── Tables ───────────────────────────────────────────────────── */
.sms-table { width:100%; border-collapse:collapse; font-size:13px; }
.sms-table thead { background:var(--gray-50); border-bottom:0.5px solid var(--gray-200); }
.sms-table th { padding:.65rem 1rem; font-size:10px; font-weight:500; text-transform:uppercase; letter-spacing:.07em; color:var(--gray-500); white-space:nowrap; }
.sms-table td { padding:.8rem 1rem; border-bottom:0.5px solid var(--gray-100); color:var(--gray-700); vertical-align:middle; }
.sms-table tr:last-child td { border-bottom:none; }
.sms-table tbody tr:nth-child(even) td { background:var(--gray-50); }
.sms-table tbody tr:hover td { background:var(--gray-100); }
.sms-desc { max-width:260px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; color:var(--gray-500); }
.sms-col--credit { color:var(--green); font-weight:600; }
.sms-col--deduct { color:var(--red);   font-weight:600; }
.sms-empty { text-align:center; color:var(--gray-400); padding:1.5rem 1rem; }

/* ── Badges ───────────────────────────────────────────────────── */
.ow-badge {
    display:inline-flex; align-items:center; gap:4px;
    font-size:11px; font-weight:500; padding:3px 9px;
    border-radius:99px; white-space:nowrap;
}
.ow-badge--paid,
.ow-badge--success      { background:var(--green-light);  color:var(--green-dark); }
.ow-badge--danger,
.ow-badge--failed       { background:var(--red-light);    color:var(--red); }
.ow-badge--pending      { background:var(--amber-light);  color:var(--amber); border:0.5px solid var(--amber-border); }
.ow-badge--blue         { background:var(--blue-light);   color:#0C447C; border:0.5px solid var(--blue-border); }
.ow-badge--grey         { background:var(--gray-100);     color:var(--gray-500); border:0.5px solid var(--gray-200); }

/* Transaction type badges */
.ow-badge--type-purchase      { background:var(--green-light);  color:var(--green-dark); }
.ow-badge--type-package_grant { background:#EDE9FF; color:var(--purple); }
.ow-badge--type-deduct        { background:var(--red-light);    color:var(--red); }
.ow-badge--type-refund        { background:var(--amber-light);  color:var(--amber); border:0.5px solid var(--amber-border); }
.ow-badge--type-manual_topup  { background:var(--blue-light);   color:#0C447C; border:0.5px solid var(--blue-border); }

/* ── Pagination ───────────────────────────────────────────────── */
.sms-pagination { padding:12px 20px; border-top:0.5px solid var(--gray-200); background:var(--gray-50); display:flex; justify-content:flex-end; }

/* ── Utilities ────────────────────────────────────────────────── */
.mb-4 { margin-bottom:1.5rem; }
.mb-3 { margin-bottom:1rem; }
.mt-3 { margin-top:1rem; }

/* ── Responsive ───────────────────────────────────────────────── */
@media(max-width:768px){
    .sms-strip { flex-direction:column; }
    .sms-strip__divider { width:100%; height:0.5px; align-self:auto; }
    .sms-strip__item { width:100%; }
}
@media(max-width:540px){
    .sms-quickpick { gap:8px; }
    .sms-pack-btn  { padding:8px 12px; }
    .sms-pack-qty  { font-size:15px; }
}

/* ── M-Pesa preloader ─────────────────────────────────────────── */
#mpesa-preloader { position:fixed; inset:0; background:rgba(17,24,39,.45); backdrop-filter:blur(2px); z-index:9999; display:flex; align-items:center; justify-content:center; }
#mpesa-preloaderInner { background:var(--white); border-radius:14px; padding:2rem; max-width:420px; width:90%; display:flex; flex-direction:column; align-items:center; gap:16px; text-align:center; box-shadow:0 20px 40px rgba(0,0,0,.18); }
#mpesa-preloaderInner img:first-child { width:72px; height:72px; object-fit:contain; border-radius:10px; }
#mpesa-preloaderInner p { font-size:13px; color:var(--gray-700); margin:0; line-height:1.6; }
#mpesa-timer { font-weight:600; color:var(--blue); }
#mpesa-preloaderInner img:last-child { width:32px; }
</style>
@endpush

@push('script')
<script>
(function () {
    'use strict';

    const pricePerSms  = {{ $pricePerSms }};
    let selectedQty    = 0;
    const customQtyEl  = document.getElementById('pageCustomQty');
    const customTotEl  = document.getElementById('pageCustomTotal');
    const buyBtn       = document.getElementById('pageBuyBtn');
    const phoneEl      = document.getElementById('pagePhone');

    function updateBtn() {
        const ok = selectedQty >= 10 && phoneEl.value.trim().length >= 9;
        buyBtn.disabled = !ok;
    }

    document.querySelectorAll('.sms-pack-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.sms-pack-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            selectedQty = parseInt(this.dataset.qty);
            customQtyEl.value = selectedQty;
            customTotEl.textContent = (selectedQty * pricePerSms).toFixed(2);
            updateBtn();
        });
    });

    customQtyEl.addEventListener('input', function () {
        selectedQty = parseInt(this.value) || 0;
        customTotEl.textContent = (selectedQty * pricePerSms).toFixed(2);
        document.querySelectorAll('.sms-pack-btn').forEach(b => b.classList.remove('active'));
        updateBtn();
    });

    phoneEl.addEventListener('input', updateBtn);

    let timerInterval;
    function showPreloader() {
        let countdown = 120;
        const el = document.getElementById('mpesa-timer');
        document.getElementById('mpesa-preloader').style.display = 'flex';
        timerInterval = setInterval(() => {
            const m = Math.floor(countdown / 60);
            const s = countdown % 60;
            el.textContent = `${m}:${s < 10 ? '0' + s : s}`;
            if (countdown-- <= 0) clearInterval(timerInterval);
        }, 1000);
    }

    function hidePreloader() {
        clearInterval(timerInterval);
        document.getElementById('mpesa-preloader').style.display = 'none';
    }

    buyBtn.addEventListener('click', function () {
        if (selectedQty < 10 || phoneEl.value.trim().length < 9) return;

        const total = (selectedQty * pricePerSms).toFixed(2);
        showPreloader();

        const formData = new FormData();
        formData.append('_token',   '{{ csrf_token() }}');
        formData.append('quantity',  selectedQty);
        formData.append('cartTotal', total);
        formData.append('phone',     phoneEl.value.trim());

        fetch('{{ route("owner.sms.credits.checkout") }}', { method: 'POST', body: formData })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    const pusher  = new Pusher(window.Laravel.pusher_key, { cluster: window.Laravel.pusher_cluster });
                    const channel = pusher.subscribe('transaction.' + data.transaction_id);

                    const timeout = setTimeout(() => {
                        window.location.href = data.redirect_url;
                    }, 120000);

                    channel.bind('MpesaTransactionProcessed', () => {
                        clearTimeout(timeout);
                        window.location.href = data.redirect_url + '&callback=true&stk_success=true';
                    });

                    channel.bind('MpesaTransactionDeclined', () => {
                        clearTimeout(timeout);
                        hidePreloader();
                        toastr.error('{{ __("Payment was declined. Please try again.") }}');
                    });
                } else {
                    hidePreloader();
                    toastr.error(data.error || '{{ __("Payment failed. Please try again.") }}');
                }
            })
            .catch(() => {
                hidePreloader();
                toastr.error('{{ __("Something went wrong. Please try again.") }}');
            });
    });

})();
</script>
@endpush