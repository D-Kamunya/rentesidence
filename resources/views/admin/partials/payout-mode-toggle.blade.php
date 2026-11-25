{{-- Shared payout-mode switch (owner + affiliate withdrawals). One global option governs both
     channels: 'manual' = pay by M-Pesa then mark paid; 'auto_b2c' = approve fires B2C. Expects
     $payoutMode in scope. --}}
<form method="POST" action="{{ route('admin.wallet.withdrawal.mode') }}" style="margin-left:auto;display:flex;align-items:center;gap:8px;"
      data-cs-confirm="{{ ($payoutMode ?? 'manual') === 'auto_b2c' ? __('Switch to MANUAL payouts? You will pay each request by M-Pesa, then mark it paid.') : __('Switch to AUTOMATED M-Pesa B2C payouts? Approving a request will send the money automatically.') }}"
      data-cs-confirm-title="{{ __('Change payout mode?') }}" data-cs-confirm-ok="{{ __('Yes, switch') }}">
    @csrf
    <span style="font-size:11px;font-weight:600;padding:3px 9px;border-radius:99px;{{ ($payoutMode ?? 'manual') === 'auto_b2c' ? 'background:#E1F5EE;color:#0F6E56;' : 'background:#FAEEDA;color:#854F0B;' }}">
        {{ ($payoutMode ?? 'manual') === 'auto_b2c' ? __('Payouts: Automated B2C') : __('Payouts: Manual') }}
    </span>
    <input type="hidden" name="mode" value="{{ ($payoutMode ?? 'manual') === 'auto_b2c' ? 'manual' : 'auto_b2c' }}">
    <button type="submit" class="cm-btn" style="background:#f3f4f6;color:#374151;border:0.5px solid #e5e7eb;font-size:11px;padding:4px 11px;border-radius:7px;cursor:pointer;">
        {{ ($payoutMode ?? 'manual') === 'auto_b2c' ? __('Switch to Manual') : __('Switch to Automated') }}
    </button>
</form>
