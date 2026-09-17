@extends('admin.layouts.app')

@section('content')
<div class="main-content">
  <div class="page-content">
    <div class="container-fluid">
      <div class="page-content-wrapper bg-white p-30 radius-20">
        <div class="container-fluid">
          @php $pageTitle = 'Referral Payouts'; @endphp

          <style>
            .rp-head h1{font-size:22px;font-weight:700;color:#1b1e22;margin:0 0 4px;}
            .rp-head p{color:#6b7280;font-size:13.5px;margin:0 0 22px;}
            .rp-sec{margin-bottom:34px;}
            .rp-sec h2{font-size:16px;font-weight:700;color:#1b1e22;margin:0 0 4px;}
            .rp-sec .hint{font-size:12.5px;color:#9aa2ad;margin:0 0 14px;}
            .rp-table{width:100%;border-collapse:collapse;}
            .rp-table th{text-align:left;font-size:11px;text-transform:uppercase;letter-spacing:.05em;color:#9aa2ad;font-weight:600;padding:9px 12px;border-bottom:1px solid #eee;}
            .rp-table td{padding:12px;border-bottom:1px solid #f3f0ea;font-size:13.5px;color:#333;vertical-align:middle;}
            .rp-amt{font-weight:700;color:#0F6E56;font-variant-numeric:tabular-nums;}
            .rp-chip{display:inline-block;font-size:11px;font-weight:650;padding:3px 10px;border-radius:999px;}
            .rp-chip--paid{background:#E1F5EE;color:#0F6E56;} .rp-chip--processing{background:#E6F1FB;color:#185FA5;}
            .rp-chip--failed{background:#FBE9E7;color:#B42318;} .rp-chip--cancelled{background:#f3f4f6;color:#6b7280;}
            .rp-chip--pending{background:#FEF3E7;color:#B45309;}
            .rp-btn{border:none;border-radius:8px;padding:7px 13px;font-size:12.5px;font-weight:650;cursor:pointer;}
            .rp-btn--pay{background:#185FA5;color:#fff;} .rp-btn--manual{background:#fff;border:1px solid #e6e1d8;color:#4a4f57;}
            .rp-btn--claw{background:#fff;border:1px solid #f0b8b0;color:#B42318;}
            .rp-actions{display:flex;gap:7px;flex-wrap:wrap;}
            .rp-empty{padding:22px;text-align:center;color:#9aa2ad;font-size:13.5px;}
            .rp-flash{border-radius:10px;padding:11px 14px;font-size:13.5px;margin-bottom:18px;}
            .rp-flash--ok{background:#E1F5EE;border:1px solid #9ad9c4;color:#0F6E56;}
            .rp-flash--err{background:#FBE9E7;border:1px solid #f0b8b0;color:#B42318;}
          </style>

          <div class="rp-head">
            <h1>{{ __('Referral Payouts') }}</h1>
            <p>{{ __('Invite-a-landlord rewards. Payouts are batched per tenant and only appear once past the holding period and above the minimum of') }} {{ $currency }} {{ number_format($minPayout) }}.</p>
          </div>

          @if (session('success'))<div class="rp-flash rp-flash--ok">{{ session('success') }}</div>@endif
          @if (session('error'))<div class="rp-flash rp-flash--err">{{ session('error') }}</div>@endif

          {{-- ── Eligible for payout ── --}}
          <div class="rp-sec">
            <h2>{{ __('Ready to pay') }}</h2>
            <p class="hint">{{ __('Tenants whose confirmed rewards have cleared the holding period and the minimum floor.') }}</p>
            @if ($eligible->isEmpty())
              <div class="rp-empty">{{ __('No tenants are due a payout right now.') }}</div>
            @else
              <table class="rp-table">
                <thead><tr><th>{{ __('Tenant') }}</th><th>{{ __('Phone') }}</th><th>{{ __('Rewards') }}</th><th>{{ __('Payable') }}</th><th>{{ __('Action') }}</th></tr></thead>
                <tbody>
                  @foreach ($eligible as $t)
                    <tr>
                      <td>{{ $t->name }}</td>
                      <td>{{ $t->phone ?: '—' }}</td>
                      <td>{{ $t->reward_count }}</td>
                      <td class="rp-amt">{{ $currency }} {{ number_format($t->payable, 0) }}</td>
                      <td>
                        <div class="rp-actions">
                          <form method="POST" action="{{ route('admin.referral-payouts.payout', $t->user_id) }}">
                            @csrf
                            <input type="hidden" name="method" value="b2c">
                            <button type="submit" class="rp-btn rp-btn--pay"
                              data-cs-confirm="{{ __('Send :cur :amt to :name via M-Pesa?', ['cur' => $currency, 'amt' => number_format($t->payable,0), 'name' => $t->name]) }}"
                              @if(!$t->phone) disabled title="{{ __('No phone on file') }}" @endif>{{ __('Pay via M-Pesa') }}</button>
                          </form>
                          <form method="POST" action="{{ route('admin.referral-payouts.payout', $t->user_id) }}">
                            @csrf
                            <input type="hidden" name="method" value="manual">
                            <button type="submit" class="rp-btn rp-btn--manual"
                              data-cs-confirm="{{ __('Record :cur :amt to :name as settled manually (paid out-of-band)?', ['cur' => $currency, 'amt' => number_format($t->payable,0), 'name' => $t->name]) }}">{{ __('Mark settled') }}</button>
                          </form>
                        </div>
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            @endif
          </div>

          {{-- ── Held for review ── --}}
          @if ($flagged->isNotEmpty())
          <div class="rp-sec">
            <h2>{{ __('Held for review') }}</h2>
            <p class="hint">{{ __('Rewards flagged (self-referral / velocity). Not auto-payable — claw back if the referred owner churned or the referral is not genuine.') }}</p>
            <table class="rp-table">
              <thead><tr><th>{{ __('Referrer') }}</th><th>{{ __('Reward') }}</th><th>{{ __('Trigger') }}</th><th>{{ __('Action') }}</th></tr></thead>
              <tbody>
                @foreach ($flagged as $r)
                  <tr>
                    <td>{{ optional($r->referrer)->first_name }} {{ optional($r->referrer)->last_name }} <span style="color:#9aa2ad;">#{{ $r->referrer_user_id }}</span></td>
                    <td class="rp-amt">{{ $r->currency }} {{ number_format($r->reward_amount, 0) }}</td>
                    <td>{{ $r->trigger_reason }}</td>
                    <td>
                      <form method="POST" action="{{ route('admin.referral-payouts.clawback', $r->owner_id) }}">
                        @csrf
                        <button type="submit" class="rp-btn rp-btn--claw"
                          data-cs-confirm="{{ __('Claw back this reward? This reverses it and it will not be paid.') }}">{{ __('Claw back') }}</button>
                      </form>
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
          @endif

          {{-- ── Payout history ── --}}
          <div class="rp-sec">
            <h2>{{ __('Payout history') }}</h2>
            @if ($history->isEmpty())
              <div class="rp-empty">{{ __('No payouts yet.') }}</div>
            @else
              <table class="rp-table">
                <thead><tr><th>{{ __('Tenant') }}</th><th>{{ __('Amount') }}</th><th>{{ __('Method') }}</th><th>{{ __('Status') }}</th><th>{{ __('M-Pesa ref') }}</th><th>{{ __('Date') }}</th></tr></thead>
                <tbody>
                  @foreach ($history as $p)
                    <tr>
                      <td>{{ optional($p->referrer)->first_name }} {{ optional($p->referrer)->last_name }} <span style="color:#9aa2ad;">#{{ $p->referrer_user_id }}</span></td>
                      <td class="rp-amt">{{ $p->currency }} {{ number_format($p->amount, 0) }}</td>
                      <td>{{ $p->settlement_method ?: '—' }}</td>
                      <td><span class="rp-chip rp-chip--{{ $p->status }}">{{ ucfirst($p->status) }}</span></td>
                      <td style="font-family:monospace;font-size:12px;">{{ $p->transaction_id ?: ($p->mpesa_reference ? \Illuminate\Support\Str::limit($p->mpesa_reference, 14) : '—') }}</td>
                      <td>{{ $p->processed_at ? $p->processed_at->format('M j, Y') : $p->created_at->format('M j, Y') }}</td>
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
</div>
@endsection
