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
    $shareText = __('I use Centresidence to manage my rent — you should put your property on it. Get set up here: ') . $inviteUrl;
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
  .il-reward{display:flex;gap:13px;align-items:flex-start;background:#f0f6fc;border:1px solid #cbddf1;border-radius:12px;padding:14px 16px;margin-top:18px;}
  .il-reward .amt{font-size:20px;font-weight:800;color:#185FA5;line-height:1;white-space:nowrap;}
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
</style>

<div class="page-content">
  <div class="container-fluid il-wrap">

    <h1 class="il-head">{{ __('Invite Your Landlord') }}</h1>
    <p class="il-sub">{{ __('Know a landlord who isn\'t on Centresidence yet? Invite them. When they come on board, you help build a rental world that works for you') }}@if($cashEnabled && $cashAmount > 0) {{ __('— and you earn a reward') }}@endif.</p>

    @if (session('success'))<div class="il-flash il-flash--ok">{{ session('success') }}</div>@endif
    @if (session('error'))<div class="il-flash il-flash--err">{{ session('error') }}</div>@endif

    @if ($canGraduate)
      <div class="il-grad">
        <div style="flex:none;font-size:28px;">🚀</div>
        <div class="g-txt"><b>{{ __('You\'re a natural at this.') }}</b> {{ __('You\'ve brought') }} {{ $confirmedCount }} {{ __('landlords on board. Ready to earn ongoing commission and unlock the full toolkit? You\'ve qualified to become a Centresidence affiliate — our team will reach out about the next step.') }}</div>
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
              <div class="amt">{{ $currency }} {{ number_format($cashAmount) }}</div>
              <div class="txt">{{ __('Your one-time reward for each landlord you refer who becomes a paying Centresidence customer. Rewards are held for a short period, then paid out per company protocol above a minimum balance.') }}</div>
            </div>
          @endif
        </div>

        <div class="il-card" style="margin-top:16px;">
          <h3>{{ __('Invite a landlord directly') }}</h3>
          <p class="muted">{{ __('Add their details so we can track the invite. We\'ll still ask them to fill in a short form themselves — no one is signed up automatically.') }}</p>
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
            <div class="il-stat"><span class="k">{{ __('Ready to pay') }}</span><span class="v">{{ $currency }} {{ number_format($payableBalance, 0) }}</span></div>
            <div class="il-stat"><span class="k">{{ __('Landlords confirmed') }}</span><span class="v">{{ $confirmedCount }}</span></div>
            @if (!$canGraduate && $graduationGoal > 0)
              <div class="il-stat"><span class="k">{{ __('To unlock affiliate') }}</span><span class="v">{{ max(0, $graduationGoal - $confirmedCount) }} {{ __('more') }}</span></div>
            @endif
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
