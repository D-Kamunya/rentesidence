@extends('finance-partner.layouts.app')

@section('content')
    @php $type = old('type', $account['type'] ?? 'mpesa_paybill'); @endphp
    <div class="cs-titlebar"><h1 class="cs-title">{{ __('Payout account') }}</h1></div>

    <div class="cs-card" style="max-width:640px;">
        <div class="cs-card__body cs-muted" style="font-size:13px;">
            {{ __('Where Centresidence settles your collected repayments. Payouts go to a paybill, bank or till — never a phone — so every payment is tied to an account and reconciled. You must set this before you can publish a product.') }}
        </div>

        @if ($partner->hasPayoutAccount())
            <div class="cs-card__body" style="padding-top:0;">
                <span class="cs-badge is-paid">{{ __('Set') }}</span>
                <span style="font-size:13px;color:#374151;margin-left:8px;">{{ $partner->payoutAccountLabel() }}</span>
            </div>
        @else
            <div class="cs-card__body" style="padding-top:0;"><span class="cs-badge is-pending">{{ __('Not set') }}</span></div>
        @endif

        <form method="POST" action="{{ route('finance-partner.payout-account.save') }}" class="cs-card__body" style="padding-top:0;">
            @csrf
            <div class="cs-field">
                <label class="cs-label">{{ __('Account type') }}</label>
                <select name="type" id="paType" class="cs-input">
                    <option value="mpesa_paybill" @selected($type === 'mpesa_paybill')>{{ __('M-Pesa Paybill') }}</option>
                    <option value="bank" @selected($type === 'bank')>{{ __('Bank account (via bank paybill)') }}</option>
                    <option value="mpesa_till" @selected($type === 'mpesa_till')>{{ __('M-Pesa Till') }}</option>
                </select>
                <div class="cs-help">{{ __('Banks are paid by M-Pesa B2B to the bank’s paybill with your account number as the reference.') }}</div>
            </div>

            <div class="cs-field pa-bankname" style="{{ $type === 'bank' ? '' : 'display:none;' }}">
                <label class="cs-label">{{ __('Bank name') }}</label>
                <input name="label" class="cs-input" value="{{ old('label', $account['label'] ?? '') }}" placeholder="{{ __('e.g. KCB Bank') }}">
            </div>

            <div class="pa-paybill" style="{{ $type === 'mpesa_till' ? 'display:none;' : '' }}">
                <div class="cs-field">
                    <label class="cs-label">{{ __('Paybill number') }}</label>
                    <input name="paybill" class="cs-input" value="{{ old('paybill', $account['paybill'] ?? '') }}" placeholder="{{ __('e.g. 123456') }}">
                </div>
                <div class="cs-field">
                    <label class="cs-label">{{ __('Account number / reference') }} <span class="cs-muted">({{ __('optional for paybill; required by most banks') }})</span></label>
                    <input name="account" class="cs-input" value="{{ old('account', $account['account'] ?? '') }}" placeholder="{{ __('your account number at the bank/paybill') }}">
                </div>
            </div>

            <div class="cs-field pa-till" style="{{ $type === 'mpesa_till' ? '' : 'display:none;' }}">
                <label class="cs-label">{{ __('Till number') }}</label>
                <input name="till" class="cs-input" value="{{ old('till', $account['till'] ?? '') }}" placeholder="{{ __('e.g. 5678901') }}">
            </div>

            <button type="submit" class="cs-btn cs-btn--primary" style="margin-top:8px;">{{ __('Save payout account') }}</button>
        </form>
    </div>

    <style>.cs-help { font-size:11.5px; color:#6b7280; line-height:1.5; margin-top:5px; }</style>
    <script>
        (function () {
            var sel = document.getElementById('paType');
            if (!sel) return;
            function sync() {
                var t = sel.value;
                document.querySelector('.pa-bankname').style.display = (t === 'bank') ? '' : 'none';
                document.querySelector('.pa-paybill').style.display  = (t === 'mpesa_till') ? 'none' : '';
                document.querySelector('.pa-till').style.display     = (t === 'mpesa_till') ? '' : 'none';
            }
            sel.addEventListener('change', sync); sync();
        })();
    </script>
@endsection
