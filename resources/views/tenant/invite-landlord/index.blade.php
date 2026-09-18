@extends('tenant.layouts.app')

@section('content')
@php
    // Status → [label, text-colour, background] for the referral list chips.
    $statusMeta = [
        'pending'      => [__('Invite sent'),        '#B45309', '#FEF3E7'],
        'lead_created' => [__('Signed up · in review'), '#185FA5', '#E6F1FB'],
        'confirmed'    => [__('Confirmed'),           '#0F6E56', '#E1F5EE'],
        'paid'         => [__('Reward paid'),         '#0F6E56', '#E1F5EE'],
        'clawed_back'  => [__('Reversed'),            '#6b7280', '#f3f4f6'],
        'rejected'     => [__('Not eligible'),        '#6b7280', '#f3f4f6'],
        'expired'      => [__('Expired'),             '#6b7280', '#f3f4f6'],
    ];
    $shareText = __('I use Centresidence for my rent — it\'s a full rental property management system. You should put your rental properties on it. Get set up here: ') . $inviteUrl;
@endphp

<style>
  .il-wrap{max-width:960px;}
  .il-head{margin:0 0 6px;font-size:22px;font-weight:700;color:#1b1e22;}
  .il-sub{margin:0 0 22px;color:#6b7280;font-size:14px;max-width:70ch;}
  .il-grid{display:grid;grid-template-columns:1.15fr .85fr;gap:18px;align-items:start;}
  @media (max-width:820px){.il-grid{grid-template-columns:1fr;}}
  .il-card{background:#fff;border:1px solid #e6e1d8;border-radius:16px;padding:22px 22px 24px;box-shadow:0 1px 2px rgba(20,23,28,.04),0 8px 24px rgba(20,23,28,.05);}
  .il-card h3{margin:0 0 4px;font-size:16px;font-weight:700;color:#1b1e22;}
  .il-card p.muted{margin:0 0 16px;font-size:13px;color:#6b7280;line-height:1.55;}
  .il-linkrow{display:flex;gap:8px;flex-wrap:wrap;}
  .il-linkrow input{flex:1 1 220px;min-width:0;background:#f7f5f1;border:1px solid #e6e1d8;border-radius:10px;padding:11px 13px;font-size:13.5px;color:#333;}
  .il-btn{border:none;border-radius:10px;padding:11px 16px;font-size:13.5px;font-weight:650;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:6px;}
  .il-btn--p{background:#185FA5;color:#fff;} .il-btn--p:hover{filter:brightness(1.07);}
  .il-btn--wa{background:#25d366;color:#fff;} .il-btn--wa:hover{filter:brightness(1.05);}
  .il-btn--ghost{background:#fff;border:1px solid #e6e1d8;color:#185FA5;}
  .il-reward{display:flex;gap:16px;align-items:center;background:#f0f6fc;border:1px solid #cbddf1;border-radius:12px;padding:16px 18px;margin-top:18px;}
  .il-reward .amtbox{flex:none;text-align:center;padding-right:16px;border-right:1px solid #cbddf1;}
  .il-reward .amt{font-size:26px;font-weight:800;color:#185FA5;line-height:1;white-space:nowrap;}
  .il-reward .amtlbl{font-size:10px;color:#6b8fb0;text-transform:uppercase;letter-spacing:.05em;font-weight:700;margin-top:5px;}
  .il-reward .txt{font-size:12.8px;color:#4a4f57;line-height:1.5;}
  .il-stat{display:flex;justify-content:space-between;align-items:baseline;padding:10px 0;border-top:1px solid #efebe3;}
  .il-stat:first-of-type{border-top:none;}
  .il-stat .k{font-size:13px;color:#6b7280;} .il-stat .v{font-size:15px;font-weight:700;color:#1b1e22;}
  .il-field{margin-bottom:13px;}
  .il-field label{display:block;font-size:12.5px;font-weight:600;color:#4a4f57;margin-bottom:5px;}
  .il-field input{width:100%;background:#fff;border:1px solid #e6e1d8;border-radius:10px;padding:10px 12px;font-size:14px;color:#1b1e22;}
  .il-field input:focus{outline:none;border-color:#185FA5;box-shadow:0 0 0 3px rgba(24,95,165,.15);}
  .il-list{width:100%;border-collapse:collapse;margin-top:6px;}
  .il-list th{text-align:left;font-size:11px;text-transform:uppercase;letter-spacing:.05em;color:#9aa2ad;font-weight:600;padding:8px 10px;border-bottom:1px solid #efebe3;}
  .il-list td{padding:11px 10px;border-bottom:1px solid #f3f0ea;font-size:13.5px;color:#333;vertical-align:middle;}
  .il-chip{display:inline-block;font-size:11.5px;font-weight:650;padding:3px 10px;border-radius:999px;}
  .il-empty{padding:26px;text-align:center;color:#9aa2ad;font-size:13.5px;}
  .il-grad{background:linear-gradient(120deg,#0f5a44,#0F6E56);color:#fff;border-radius:14px;padding:16px 18px;margin-bottom:18px;display:flex;gap:14px;align-items:center;flex-wrap:wrap;}
  .il-grad .g-txt{flex:1 1 240px;font-size:14px;line-height:1.45;}
  .il-flash{border-radius:10px;padding:11px 14px;font-size:13.5px;margin-bottom:16px;}
  .il-flash--ok{background:#E1F5EE;border:1px solid #9ad9c4;color:#0F6E56;}
  .il-flash--err{background:#FBE9E7;border:1px solid #f0b8b0;color:#B42318;}
  .il-how{display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-top:12px;}
  @media (max-width:640px){.il-how{grid-template-columns:1fr;}}
</style>

<div class="page-content">
  <div class="container-fluid il-wrap">

    <h1 class="il-head">{{ !empty($isConnected) ? __('Refer a Landlord') : __('Invite Your Landlord') }}</h1>
    @if (!empty($isConnected))
      <p class="il-sub">{{ __('Your landlord is already on Centresidence — but know another landlord who isn\'t? Refer them. When they come on board, you help grow the network') }}@if($cashEnabled && $cashAmount > 0) {{ __('— and you earn a reward') }}@endif.</p>
    @else
      <p class="il-sub">{{ __('Know a landlord who isn\'t on Centresidence yet? Invite them. When they come on board, you help build a rental world that works for you') }}@if($cashEnabled && $cashAmount > 0) {{ __('— and you earn a reward') }}@endif.</p>
    @endif

    @if (session('success'))<div class="il-flash il-flash--ok">{{ session('success') }}</div>@endif
    @if (session('error'))<div class="il-flash il-flash--err">{{ session('error') }}</div>@endif

    @if ($hasGraduated)
      <div class="il-grad">
        <div style="flex:none;font-size:28px;">🎉</div>
        <div class="g-txt"><b>{{ __('You\'re a Centresidence affiliate.') }}</b> {{ __('Switch to your affiliate account from the account menu (top right) to earn ongoing commission and use the full toolkit.') }}</div>
      </div>
    @elseif ($canGraduate)
      <div class="il-grad">
        <div style="flex:none;font-size:28px;">🚀</div>
        <div class="g-txt"><b>{{ __('You\'re a natural at this.') }}</b> {{ __('You\'ve brought') }} {{ $confirmedCount }} {{ __('landlords on board — you\'ve qualified to become a Centresidence affiliate and earn ongoing commission. You keep this tenant account and switch between the two whenever you like.') }}</div>
        <form method="POST" action="{{ route('tenant.invite-landlord.graduate') }}" style="flex:none;">
          @csrf
          <button type="submit" class="il-btn" style="background:#fff;color:#0F6E56;border:none;"
            data-cs-confirm="{{ __('Become a Centresidence affiliate? You\'ll keep your tenant account and get a linked affiliate account you can switch to.') }}">{{ __('Become an affiliate') }} →</button>
        </form>
      </div>
    @endif

    <div class="il-grid">
      {{-- Left: share your link + invite a specific landlord --}}
      <div>
        <div class="il-card">
          <h3>{{ __('Your invite link') }}</h3>
          <p class="muted">{{ __('Share this link with your landlord. When they sign up through it, the invite is credited to you.') }}</p>
          <div class="il-linkrow">
            <input type="text" id="ilLink" value="{{ $inviteUrl }}" readonly onclick="this.select()">
            <button type="button" class="il-btn il-btn--p" onclick="ilCopy()">{{ __('Copy') }}</button>
            <a class="il-btn il-btn--wa" target="_blank" rel="noopener" href="https://wa.me/?text={{ rawurlencode($shareText) }}">{{ __('WhatsApp') }}</a>
          </div>

          @if ($cashEnabled && $cashAmount > 0)
            <div class="il-reward">
              <div class="amtbox">
                <div class="amt">{{ $currency }} {{ number_format($cashAmount) }}</div>
                <div class="amtlbl">{{ __('per referral') }}</div>
              </div>
              <div class="txt">{{ __('Your one-time reward for each landlord you refer who becomes a paying Centresidence customer. Rewards are held for a short period, then paid out per company protocol above a minimum balance.') }}</div>
            </div>
          @endif
        </div>

        <div class="il-card" style="margin-top:16px;">
          <h3>{{ __('Invite a landlord directly') }}</h3>
          <p class="muted">{{ __('Add their phone or email and we\'ll send them your invite by SMS and email. They still fill in a short form themselves — no one is signed up automatically.') }}</p>
          <form method="POST" action="{{ route('tenant.invite-landlord.store') }}">
            @csrf
            <div class="il-field">
              <label>{{ __('Landlord\'s name') }}</label>
              <input type="text" name="invitee_name" value="{{ old('invitee_name') }}" maxlength="120" placeholder="{{ __('Optional') }}">
            </div>
            <div class="il-field">
              <label>{{ __('Phone') }}</label>
              <input type="text" name="invitee_phone" value="{{ old('invitee_phone') }}" maxlength="32" placeholder="0700 000 000">
            </div>
            <div class="il-field">
              <label>{{ __('Email') }}</label>
              <input type="email" name="invitee_email" value="{{ old('invitee_email') }}" maxlength="160" placeholder="{{ __('Optional if you have their phone') }}">
            </div>
            <button type="submit" class="il-btn il-btn--p">{{ __('Save invite') }}</button>
          </form>
        </div>
      </div>

      {{-- Right: your standing + the referrals list --}}
      <div>
        @if ($cashEnabled && $cashAmount > 0)
          <div class="il-card" style="margin-bottom:16px;">
            <h3>{{ __('Your rewards') }}</h3>
            {{-- Headline: everything earned (whether it has cleared the holding period or not). --}}
            <div style="display:flex;align-items:baseline;gap:8px;margin:6px 0 4px;">
              <span style="font-size:26px;font-weight:800;color:#0F6E56;line-height:1;">{{ $currency }} {{ number_format($totalEarned, 0) }}</span>
              <span style="font-size:12px;color:#9aa2ad;">{{ __('earned so far') }}</span>
            </div>
            <div class="il-stat"><span class="k">{{ __('Ready to withdraw') }}</span><span class="v">{{ $currency }} {{ number_format($payableBalance, 0) }}</span></div>
            @if ($pendingBalance > 0)
              <div class="il-stat"><span class="k">{{ __('On the way (still clearing)') }}</span><span class="v" style="color:#B45309;">{{ $currency }} {{ number_format($pendingBalance, 0) }}</span></div>
            @endif
            @if ($paidBalance > 0)
              <div class="il-stat"><span class="k">{{ __('Paid out') }}</span><span class="v">{{ $currency }} {{ number_format($paidBalance, 0) }}</span></div>
            @endif
            <div class="il-stat"><span class="k">{{ __('Landlords confirmed') }}</span><span class="v">{{ $confirmedCount }}</span></div>
            @if (!$canGraduate && $graduationGoal > 0)
              <div class="il-stat"><span class="k">{{ __('To unlock affiliate') }}</span><span class="v">{{ max(0, $graduationGoal - $confirmedCount) }} {{ __('more') }}</span></div>
            @endif

            {{-- Withdrawal control: request → admin reviews & releases (mirrors affiliate withdrawals). --}}
            @if ($pendingPayout)
              <div style="margin-top:14px;padding:11px 13px;border-radius:10px;background:#E6F1FB;border:1px solid #cbddf1;color:#185FA5;font-size:13px;">
                {{ __('Payout of') }} <b>{{ $currency }} {{ number_format($pendingPayout->amount, 0) }}</b>
                @if ($pendingPayout->status === 'processing') {{ __('is being sent to your M-Pesa.') }}
                @else {{ __('requested — under review. You\'ll see it here once it\'s paid.') }}
                @endif
              </div>
            @elseif ($payableBalance >= $minPayout && $payableBalance > 0)
              <button type="button" class="il-btn il-btn--p" style="width:100%;justify-content:center;margin-top:14px;" onclick="document.getElementById('ilPayoutModal').style.display='flex';">
                {{ __('Request payout') }} — {{ $currency }} {{ number_format($payableBalance, 0) }}
              </button>
            @elseif ($payableBalance > 0)
              <p style="margin:12px 0 0;font-size:12px;color:#9aa2ad;">{{ __('You can request a payout once your ready balance reaches') }} {{ $currency }} {{ number_format($minPayout, 0) }}.</p>
            @endif

            <p style="margin:12px 0 0;font-size:11.5px;color:#9aa2ad;line-height:1.5;">{{ __('Rewards clear after a short holding period. You can request a payout once your ready balance reaches') }} <b style="color:#6b7280;">{{ $currency }} {{ number_format($minPayout, 0) }}</b> — {{ __('we then review it and send it to your M-Pesa.') }}</p>
          </div>

          {{-- Request-payout modal --}}
          <div id="ilPayoutModal" style="display:none;position:fixed;inset:0;z-index:1050;background:rgba(20,23,28,.5);align-items:center;justify-content:center;padding:20px;">
            <div style="background:#fff;border-radius:16px;max-width:420px;width:100%;padding:24px;box-shadow:0 20px 60px rgba(0,0,0,.25);">
              <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px;">
                <h3 style="margin:0;font-size:17px;font-weight:700;color:#1b1e22;">{{ __('Request payout') }}</h3>
                <button type="button" onclick="document.getElementById('ilPayoutModal').style.display='none';" style="border:none;background:transparent;font-size:22px;line-height:1;color:#9aa2ad;cursor:pointer;">&times;</button>
              </div>
              <p style="margin:0 0 16px;font-size:13px;color:#6b7280;">{{ __('We\'ll send') }} <b style="color:#0F6E56;">{{ $currency }} {{ number_format($payableBalance, 0) }}</b> {{ __('to your M-Pesa after a quick review.') }}</p>
              <form method="POST" action="{{ route('tenant.invite-landlord.request-payout') }}">
                @csrf
                <label style="display:block;font-size:12.5px;font-weight:600;color:#4a4f57;margin-bottom:6px;">{{ __('M-Pesa number') }}</label>
                <div style="display:flex;align-items:stretch;border:1px solid #e6e1d8;border-radius:10px;overflow:hidden;">
                  <span style="background:#f7f5f1;padding:11px 12px;font-size:14px;color:#6b7280;border-right:1px solid #e6e1d8;">+254</span>
                  <input type="text" name="phone" value="{{ old('phone', $defaultPhone) }}" required maxlength="9" pattern="[71]\d{8}" placeholder="712345678" style="flex:1;border:none;padding:11px 12px;font-size:14.5px;color:#1b1e22;outline:none;">
                </div>
                @error('phone')<div style="color:#B42318;font-size:12px;margin-top:6px;">{{ $message }}</div>@enderror
                <div style="font-size:11.5px;color:#9aa2ad;margin-top:6px;">{{ __('Enter your number without the leading 0 (e.g. 712345678).') }}</div>
                <button type="submit" class="il-btn il-btn--p" style="width:100%;justify-content:center;margin-top:16px;">{{ __('Submit request') }}</button>
              </form>
            </div>
          </div>
        @endif

        <div class="il-card">
          <h3>{{ __('Landlords you\'ve invited') }}</h3>
          @if ($referralList->isEmpty())
            <div class="il-empty">{{ __('No invites yet. Share your link above to get started.') }}</div>
          @else
            <table class="il-list">
              <thead><tr><th>{{ __('Landlord') }}</th><th>{{ __('Status') }}</th></tr></thead>
              <tbody>
                @foreach ($referralList as $r)
                  @php [$lbl, $fg, $bg] = $statusMeta[$r->status] ?? [ucfirst($r->status), '#6b7280', '#f3f4f6']; @endphp
                  <tr>
                    <td>
                      <div style="font-weight:600;color:#1b1e22;">{{ $r->invitee_name ?: ($r->invitee_company ?: __('Invited landlord')) }}</div>
                      @if ($r->invitee_phone || $r->invitee_email)
                        <div style="font-size:12px;color:#9aa2ad;">{{ $r->invitee_phone ?: $r->invitee_email }}</div>
                      @endif
                    </td>
                    <td>
                      <span class="il-chip" style="color:{{ $fg }};background:{{ $bg }};">{{ $lbl }}</span>
                      @if ($r->status === 'confirmed' && $r->reward_type === 'cash' && $r->held_until && $r->held_until->isFuture())
                        <div style="font-size:11px;color:#9aa2ad;margin-top:3px;">{{ __('payable') }} {{ $r->held_until->format('M j') }}</div>
                      @endif
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          @endif
        </div>
      </div>
    </div>

    {{-- The light "Invite your landlord" guide — placed where the tenant acts. How it works,
         what to say, and the honest bit about when a reward is paid. --}}
    <div class="il-card" style="margin-top:18px;">
      <h3>{{ __('How it works') }}</h3>
      <div class="il-how">
        <div style="display:flex;flex-direction:column;gap:6px;">
          <div style="width:30px;height:30px;border-radius:9px;display:grid;place-items:center;background:#E6F1FB;color:#185FA5;font-weight:700;font-size:14px;">1</div>
          <div style="font-weight:650;color:#1b1e22;font-size:14px;">{{ __('Share your link') }}</div>
          <div style="font-size:12.8px;color:#6b7280;line-height:1.5;">{{ __('Send your invite link to a landlord who isn\'t on Centresidence yet — by WhatsApp, SMS, however you like.') }}</div>
        </div>
        <div style="display:flex;flex-direction:column;gap:6px;">
          <div style="width:30px;height:30px;border-radius:9px;display:grid;place-items:center;background:#E6F1FB;color:#185FA5;font-weight:700;font-size:14px;">2</div>
          <div style="font-weight:650;color:#1b1e22;font-size:14px;">{{ __('They get set up') }}</div>
          <div style="font-size:12.8px;color:#6b7280;line-height:1.5;">{{ __('They fill a short form; our team reviews it and helps them start collecting rent. You\'ll see the status here.') }}</div>
        </div>
        <div style="display:flex;flex-direction:column;gap:6px;">
          <div style="width:30px;height:30px;border-radius:9px;display:grid;place-items:center;background:#E1F5EE;color:#0F6E56;font-weight:700;font-size:14px;">3</div>
          <div style="font-weight:650;color:#1b1e22;font-size:14px;">
            @if ($cashEnabled && $cashAmount > 0){{ __('You get rewarded') }}@else{{ __('You build your world') }}@endif
          </div>
          <div style="font-size:12.8px;color:#6b7280;line-height:1.5;">
            @if ($cashEnabled && $cashAmount > 0)
              {{ __('When they become a paying customer, your reward is confirmed, held briefly, then paid out per company protocol.') }}
            @else
              {{ __('Every landlord you bring on makes your rental record, deposits and reminders work better for you.') }}
            @endif
          </div>
        </div>
      </div>

      <div style="margin-top:18px;padding-top:16px;border-top:1px solid #efebe3;">
        <div style="font-weight:650;color:#1b1e22;font-size:14px;margin-bottom:8px;">{{ __('What to tell your landlord') }}</div>
        <ul style="margin:0;padding-left:18px;color:#4a4f57;font-size:13.2px;line-height:1.7;">
          <li>{{ __('Tenants pay rent from their phone (M-Pesa included) — no more chasing or cash trips.') }}</li>
          <li>{{ __('Receipts send themselves, and every unit\'s status is visible at a glance.') }}</li>
          <li>{{ __('It\'s free to start — the essentials cost nothing.') }}</li>
        </ul>
        @if ($cashEnabled && $cashAmount > 0)
          <p style="margin:12px 0 0;font-size:12px;color:#9aa2ad;line-height:1.5;">
            {{ __('A note on rewards: they\'re only paid once your referred landlord actually becomes a paying Centresidence customer, are held for a short window, and are paid on a schedule above a minimum balance — this keeps the program fair for everyone.') }}
          </p>
        @endif
      </div>
    </div>
  </div>
</div>

<script>
  function ilCopy(){
    var el = document.getElementById('ilLink');
    el.select(); el.setSelectionRange(0, 99999);
    try {
      if (navigator.clipboard && navigator.clipboard.writeText) { navigator.clipboard.writeText(el.value); }
      else { document.execCommand('copy'); }
    } catch (e) {}
  }
</script>
@endsection
