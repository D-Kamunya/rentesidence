@extends('admin.layouts.app')

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="page-content-wrapper bg-white p-30 radius-20">

                {{-- Page header --}}
                <div class="adm-page-header mb-4">
                    <div>
                        <h2 class="adm-page-title">{{ __('SMS Credit Settings') }}</h2>
                        <p class="adm-page-subtitle">{{ __('Set pricing, manage owner balances and view purchase history') }}</p>
                    </div>
                </div>

                @if(session('success'))
                    <div class="adm-alert adm-alert--success mb-4">{{ session('success') }}</div>
                @endif

                {{-- Two-column form panels --}}
                <div class="adm-form-grid mb-4">

                    {{-- Pricing configuration --}}
                    <div class="ow-card adm-form-card">
                        <div class="dash-card__head">
                            <div class="adm-panel-icon adm-panel-icon--blue">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none">
                                    <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.8"/>
                                    <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06-.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"
                                          stroke="currentColor" stroke-width="1.8"/>
                                </svg>
                            </div>
                            <span class="adm-panel-title">{{ __('Pricing Configuration') }}</span>
                        </div>

                        <div class="adm-form-body">
                            <form action="{{ route('admin.sms.credits.settings') }}" method="POST">
                                @csrf @method('PUT')

                                <div class="adm-field mb-3">
                                    <label class="adm-field-label">{{ __('Price per SMS Credit (KSh)') }}</label>
                                    <input type="number" step="0.01" min="0.01" name="sms_credit_price"
                                           class="adm-input"
                                           value="{{ old('sms_credit_price', $pricePerSms) }}">
                                    <span class="adm-field-hint">{{ __('Owners pay this amount per credit purchased.') }}</span>
                                </div>

                                <div class="adm-field mb-4">
                                    <label class="adm-field-label">{{ __('Low Credit Alert Threshold') }}</label>
                                    <input type="number" min="1" name="sms_low_credit_threshold"
                                           class="adm-input"
                                           value="{{ old('sms_low_credit_threshold', $lowThreshold) }}">
                                    <span class="adm-field-hint">{{ __('Alert owner when credits drop to or below this number.') }}</span>
                                </div>

                                <button type="submit" class="ow-btn ow-btn--primary">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none">
                                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                        <polyline points="17 21 17 13 7 13 7 21" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                        <polyline points="7 3 7 8 15 8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    {{ __('Save Settings') }}
                                </button>
                            </form>
                        </div>
                    </div>

                    {{-- Manual top-up --}}
                    <div class="ow-card adm-form-card">
                        <div class="dash-card__head">
                            <div class="adm-panel-icon adm-panel-icon--green">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none">
                                    <line x1="12" y1="5" x2="12" y2="19" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                    <line x1="5" y1="12" x2="19" y2="12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                </svg>
                            </div>
                            <span class="adm-panel-title">{{ __('Manual Top-Up') }}</span>
                        </div>

                        <div class="adm-form-body">
                            <form action="{{ route('admin.sms.credits.topup') }}" method="POST">
                                @csrf

                                <div class="adm-field mb-3">
                                    <label class="adm-field-label">{{ __('Owner') }}</label>
                                    <select name="owner_user_id" class="adm-input">
                                        @foreach(\App\Models\Owner::with('user')->get() as $o)
                                            <option value="{{ $o->user_id }}">
                                                {{ $o->user->name ?? $o->print_name }}
                                                ({{ $o->user->email ?? '' }}) — {{ $o->sms_credits }} {{ __('credits') }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="adm-field mb-3">
                                    <label class="adm-field-label">{{ __('Credits to Add') }}</label>
                                    <input type="number" min="1" name="quantity" class="adm-input"
                                           placeholder="e.g. 100">
                                </div>

                                <div class="adm-field mb-4">
                                    <label class="adm-field-label">{{ __('Note (optional)') }}</label>
                                    <input type="text" name="note" class="adm-input"
                                           placeholder="{{ __('e.g. Goodwill credit') }}">
                                </div>

                                <button type="submit" class="ow-btn ow-btn--green">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none">
                                        <line x1="12" y1="5" x2="12" y2="19" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                        <line x1="5" y1="12" x2="19" y2="12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                    </svg>
                                    {{ __('Add Credits') }}
                                </button>
                            </form>
                        </div>
                    </div>

                </div>

                {{-- Credit activity table --}}
                <div class="ow-card adm-section-card">
                    <div class="dash-card__head">
                        <div class="adm-panel-icon adm-panel-icon--blue">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none">
                                <rect x="1" y="4" width="22" height="16" rx="2" stroke="currentColor" stroke-width="1.8"/>
                                <line x1="1" y1="10" x2="23" y2="10" stroke="currentColor" stroke-width="1.8"/>
                            </svg>
                        </div>
                        <span class="adm-panel-title">{{ __('Credit Activity') }}</span>
                        <span class="adm-revenue-pill">
                            {{ __('Revenue:') }} <strong>KSh {{ number_format($totalRevenue, 2) }}</strong>
                        </span>
                    </div>

                    <div class="table-responsive">
                        <table class="adm-table">
                            <thead>
                                <tr>
                                    <th>{{ __('Date') }}</th>
                                    <th>{{ __('Owner') }}</th>
                                    <th>{{ __('Type') }}</th>
                                    <th>{{ __('Credits') }}</th>
                                    <th>{{ __('Amount Paid') }}</th>
                                    <th>{{ __('Reference') }}</th>
                                    <th>{{ __('Status') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentPurchases as $tx)
                                <tr>
                                    <td style="white-space:nowrap;">{{ $tx->created_at->format('d M Y H:i') }}</td>
                                    <td style="font-weight:500;color:var(--gray-800);">{{ $tx->owner->user->name ?? '—' }}</td>
                                    <td>
                                        <span class="ow-badge ow-badge--type-{{ $tx->type }}">
                                            {{ ucfirst(str_replace('_',' ',$tx->type)) }}
                                        </span>
                                    </td>
                                    <td class="adm-col--credit">+{{ $tx->quantity }}</td>
                                    <td>{{ $tx->amount_paid ? 'KSh '.number_format($tx->amount_paid,2) : '—' }}</td>
                                    <td style="font-family:monospace;font-size:11px;font-weight:500;color:#0C447C;">{{ $tx->reference ?? '—' }}</td>
                                    <td>
                                        <span class="ow-badge ow-badge--{{ $tx->status === 'success' ? 'paid' : ($tx->status === 'pending' ? 'pending' : 'danger') }}">
                                            {{ ucfirst($tx->status) }}
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="adm-empty">{{ __('No activity yet') }}</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="adm-pagination">{{ $recentPurchases->links() }}</div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection

@push('style')
<style>
/* ── Design tokens (mirrors owner blade) ──────────────────────── */
:root {
    --blue:         #185FA5;
    --blue-hover:   #0F4A84;
    --blue-light:   #E6F1FB;
    --blue-border:  #B5D4F4;
    --blue-faint:   #185ea56e;
    --green:        #1D9E75;
    --green-dark:   #0F6E56;
    --green-light:  #E1F5EE;
    --amber:        #854F0B;
    --amber-light:  #FAEEDA;
    --amber-border: #F5D9A8;
    --red:          #993C1D;
    --red-light:    #FAECE7;
    --purple:       #534AB7;
    --gray-900:     #111827;
    --gray-800:     #1f2937;
    --gray-700:     #374151;
    --gray-500:     #6b7280;
    --gray-400:     #9ca3af;
    --gray-200:     #e5e7eb;
    --gray-100:     #f3f4f6;
    --gray-50:      #fafafa;
    --white:        #ffffff;
}

/* ── Page header ──────────────────────────────────────────────── */
.adm-page-header  { display:flex; align-items:flex-start; justify-content:space-between; flex-wrap:wrap; gap:1rem; }
.adm-page-title   { font-size:22px; font-weight:500; color:var(--gray-900); margin:0 0 4px; }
.adm-page-subtitle{ font-size:13px; color:var(--gray-500); margin:0; }

/* ── Alert ────────────────────────────────────────────────────── */
.adm-alert { display:flex; align-items:center; gap:8px; padding:12px 16px; border-radius:8px; font-size:13px; font-weight:500; border:0.5px solid transparent; }
.adm-alert--success { background:var(--green-light); color:var(--green-dark); border-color:#A7DFC9; }

/* ── Form grid ────────────────────────────────────────────────── */
.adm-form-grid { display:grid; grid-template-columns:1fr 1fr; gap:1.25rem; }
@media(max-width:768px){ .adm-form-grid { grid-template-columns:1fr; } }

/* ── Outer card (shared with owner blade) ─────────────────────── */
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

/* ── Card head ────────────────────────────────────────────────── */
.dash-card__head { display:flex; align-items:center; gap:10px; padding:.75rem 1.1rem; border-bottom:0.5px solid var(--gray-200); background:var(--gray-50); }

/* ── Panel icon ───────────────────────────────────────────────── */
.adm-panel-icon { width:28px; height:28px; border-radius:7px; display:inline-flex; align-items:center; justify-content:center; flex-shrink:0; }
.adm-panel-icon--blue  { background:var(--blue-light);  color:var(--blue); }
.adm-panel-icon--green { background:var(--green-light); color:var(--green); }
.adm-panel-title { font-size:14px; font-weight:500; color:var(--gray-900); }

/* ── Revenue pill (activity panel head) ───────────────────────── */
.adm-revenue-pill {
    margin-left:auto; font-size:12px; font-weight:500; color:var(--gray-500);
    background:var(--gray-100); border:0.5px solid var(--gray-200);
    border-radius:99px; padding:3px 10px; white-space:nowrap;
}
.adm-revenue-pill strong { color:var(--green-dark); }

/* ── Form body ────────────────────────────────────────────────── */
.adm-form-card  { }
.adm-form-body  { padding:20px; }
.adm-section-card { }

.adm-field { display:flex; flex-direction:column; gap:5px; }
.adm-field-label {
    font-size:10px; font-weight:500; text-transform:uppercase;
    letter-spacing:.07em; color:var(--gray-400);
}
.adm-input {
    width:100%; padding:7px 10px;
    border:0.5px solid var(--gray-200); border-radius:7px;
    font-size:13px; color:var(--gray-700); outline:none;
    background:var(--white);
    transition:border-color .15s, box-shadow .15s;
}
.adm-input:focus { border-color:var(--blue); box-shadow:0 0 0 3px rgba(24,95,165,.1); }
.adm-field-hint { font-size:11px; color:var(--gray-400); }

/* ── Buttons ──────────────────────────────────────────────────── */
.ow-btn {
    display:inline-flex; align-items:center; gap:6px;
    font-size:12px; font-weight:500; padding:7px 15px;
    border-radius:7px; border:none; cursor:pointer;
    transition:all .13s;
}
.ow-btn--primary { background:var(--blue);  color:var(--white); }
.ow-btn--primary:hover { background:var(--blue-hover); transform:translateY(-1px); }
.ow-btn--green   { background:var(--green); color:var(--white); }
.ow-btn--green:hover   { background:var(--green-dark); transform:translateY(-1px); }

/* ── Table ────────────────────────────────────────────────────── */
.adm-table { width:100%; border-collapse:collapse; font-size:13px; }
.adm-table thead { background:var(--gray-50); border-bottom:0.5px solid var(--gray-200); }
.adm-table th { padding:.65rem 1rem; font-size:10px; font-weight:500; text-transform:uppercase; letter-spacing:.07em; color:var(--gray-500); white-space:nowrap; }
.adm-table td { padding:.8rem 1rem; border-bottom:0.5px solid var(--gray-100); color:var(--gray-700); vertical-align:middle; }
.adm-table tr:last-child td { border-bottom:none; }
.adm-table tbody tr:nth-child(even) td { background:var(--gray-50); }
.adm-table tbody tr:hover td { background:var(--gray-100); }
.adm-col--credit { color:var(--green); font-weight:600; }
.adm-empty { text-align:center; color:var(--gray-400); padding:1.5rem 1rem; }

/* ── Badges (shared tokens) ───────────────────────────────────── */
.ow-badge {
    display:inline-flex; align-items:center; gap:4px;
    font-size:11px; font-weight:500; padding:3px 9px;
    border-radius:99px; white-space:nowrap;
}
.ow-badge--paid,
.ow-badge--success       { background:var(--green-light);  color:var(--green-dark); }
.ow-badge--danger,
.ow-badge--failed        { background:var(--red-light);    color:var(--red); }
.ow-badge--pending       { background:var(--amber-light);  color:var(--amber); border:0.5px solid var(--amber-border); }
.ow-badge--blue          { background:var(--blue-light);   color:#0C447C; border:0.5px solid var(--blue-border); }
.ow-badge--type-purchase      { background:var(--green-light);  color:var(--green-dark); }
.ow-badge--type-package_grant { background:#EDE9FF; color:var(--purple); }
.ow-badge--type-manual_topup  { background:var(--blue-light);   color:#0C447C; border:0.5px solid var(--blue-border); }

/* ── Pagination ───────────────────────────────────────────────── */
.adm-pagination { padding:12px 20px; border-top:0.5px solid var(--gray-200); background:var(--gray-50); display:flex; justify-content:flex-end; }

/* ── Utilities ────────────────────────────────────────────────── */
.mb-3 { margin-bottom:1rem; }
.mb-4 { margin-bottom:1.5rem; }
.mt-3 { margin-top:1rem; }
</style>
@endpush