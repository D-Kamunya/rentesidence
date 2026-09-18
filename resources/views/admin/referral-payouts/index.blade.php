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
            .rp-kpis{display:grid;grid-template-columns:repeat(6,1fr);gap:12px;margin-bottom:30px;}
            @media (max-width:900px){.rp-kpis{grid-template-columns:repeat(3,1fr);}}
            @media (max-width:520px){.rp-kpis{grid-template-columns:repeat(2,1fr);}}
          </style>

          <div class="rp-head">
            <h1>{{ __('Referrals') }}</h1>
            <p>{{ __('Invite-a-landlord rewards. Payouts are batched per tenant and only appear once past the holding period and above the minimum of') }} {{ $currency }} {{ number_format($minPayout) }}.</p>
          </div>

          @if (session('success'))<div class="rp-flash rp-flash--ok">{{ session('success') }}</div>@endif
          @if (session('error'))<div class="rp-flash rp-flash--err">{{ session('error') }}</div>@endif
          @include('partials.dev-credentials')

          {{-- ── Funnel at a glance ── --}}
          @php
            $sc = fn($k) => (int) ($statusCounts[$k] ?? 0);
            $kpis = [
              [__('Referrers'), $totalReferrers, '#185FA5'],
              [__('Invites sent'), $sc('pending'), '#B45309'],
              [__('Signed up'), $sc('lead_created'), '#5B4B9E'],
              [__('Confirmed'), $sc('confirmed'), '#0F6E56'],
              [__('Paid'), $sc('paid'), '#0F6E56'],
              [__('Clawed back'), $sc('clawed_back'), '#B42318'],
              [__('Graduated'), $graduationsCount, '#0F6E56'],
            ];
          @endphp
          <div class="rp-kpis">
            @foreach ($kpis as [$label, $val, $col])
              <div style="background:#fff;border:1px solid #eee;border-radius:12px;padding:14px 16px;">
                <div style="font-size:24px;font-weight:800;color:{{ $col }};line-height:1;">{{ number_format($val) }}</div>
                <div style="font-size:11.5px;color:#9aa2ad;margin-top:5px;text-transform:uppercase;letter-spacing:.04em;font-weight:600;">{{ $label }}</div>
              </div>
            @endforeach
          </div>

          {{-- ── Owner sign-up requests (one-click onboarding) ── --}}
          <div class="rp-sec">
            <h2>{{ __('Owner sign-up requests') }}</h2>
            <p class="hint">{{ __('A referred landlord filled the invite form. Create their owner account in one click — a temporary password and login link go out by email and SMS, and they set their own password on first sign-in.') }}</p>
            @if ($onboardRequests->isEmpty())
              <div class="rp-empty">{{ __('No pending sign-up requests.') }}</div>
            @else
              <table class="rp-table">
                <thead><tr><th>{{ __('Landlord') }}</th><th>{{ __('Contact') }}</th><th>{{ __('Property') }}</th><th>{{ __('Referred by') }}</th><th>{{ __('Action') }}</th></tr></thead>
                <tbody>
                  @foreach ($onboardRequests as $r)
                    @php $co = optional($r->lead)->company; $hasEmail = $co && filter_var($co->email ?: $r->invitee_email, FILTER_VALIDATE_EMAIL); @endphp
                    <tr>
                      <td><div style="font-weight:600;color:#1b1e22;">{{ $r->invitee_name ?: optional($co)->company_name ?: __('Invited landlord') }}</div></td>
                      <td>
                        <div>{{ optional($co)->email ?: $r->invitee_email ?: '—' }}</div>
                        <div style="font-size:12px;color:#9aa2ad;">{{ optional($co)->phone ?: $r->invitee_phone ?: '' }}</div>
                      </td>
                      <td>{{ optional($co)->company_name ?: '—' }}@if(optional($co)->estimated_units) <span style="color:#9aa2ad;">· {{ $co->estimated_units }} {{ __('units') }}</span>@endif</td>
                      <td>{{ optional($r->referrer)->first_name }} {{ optional($r->referrer)->last_name }} <span style="color:#9aa2ad;">#{{ $r->referrer_user_id }}</span></td>
                      <td>
                        @if ($hasEmail)
                          <form method="POST" action="{{ route('admin.referral-payouts.create-owner', $r->id) }}">
                            @csrf
                            <button type="submit" class="rp-btn rp-btn--pay"
                              data-cs-confirm="{{ __('Create an owner account for :name and send their login details?', ['name' => $r->invitee_name ?: optional($co)->company_name]) }}">{{ __('Create owner') }}</button>
                          </form>
                        @else
                          <span style="font-size:12px;color:#B42318;">{{ __('No valid email — cannot onboard') }}</span>
                        @endif
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            @endif
          </div>

          {{-- ── Payout requests (tenant-initiated → admin releases) ── --}}
          <div class="rp-sec">
            <h2>{{ __('Payout requests') }}</h2>
            <p class="hint">{{ __('Tenants have requested these payouts. Review and release via M-Pesa, or record a manual settlement — or decline to return the rewards to their balance.') }}</p>
            @if ($payoutRequests->isEmpty())
              <div class="rp-empty">{{ __('No payout requests right now.') }}</div>
            @else
              <table class="rp-table">
                <thead><tr><th>{{ __('Tenant') }}</th><th>{{ __('Amount') }}</th><th>{{ __('M-Pesa') }}</th><th>{{ __('Requested') }}</th><th>{{ __('Status') }}</th><th>{{ __('Action') }}</th></tr></thead>
                <tbody>
                  @foreach ($payoutRequests as $p)
                    <tr>
                      <td>{{ optional($p->referrer)->first_name }} {{ optional($p->referrer)->last_name }} <span style="color:#9aa2ad;">#{{ $p->referrer_user_id }}</span></td>
                      <td class="rp-amt">{{ $currency }} {{ number_format($p->amount, 0) }}</td>
                      <td style="font-family:monospace;font-size:12.5px;">{{ $p->phone ?: '—' }}</td>
                      <td>{{ $p->created_at->format('M j, Y') }}</td>
                      <td><span class="rp-chip rp-chip--{{ $p->status }}">{{ ucfirst($p->status) }}</span></td>
                      <td>
                        @if ($p->status === 'pending')
                          <div class="rp-actions">
                            <button type="button" class="rp-btn rp-btn--pay"
                              onclick="rpOpenApprove('{{ route('admin.referral-payouts.approve', $p->id) }}', @js(trim(optional($p->referrer)->first_name.' '.optional($p->referrer)->last_name)), '{{ $currency }} {{ number_format($p->amount,0) }}', '{{ $p->phone }}')">{{ __('Review & release') }}</button>
                            <form method="POST" action="{{ route('admin.referral-payouts.reject', $p->id) }}">
                              @csrf
                              <button type="submit" class="rp-btn rp-btn--claw" data-cs-confirm="{{ __('Decline this payout request? The rewards return to the tenant\'s balance.') }}">{{ __('Decline') }}</button>
                            </form>
                          </div>
                        @else
                          <span style="font-size:12px;color:#185FA5;">{{ __('Sending…') }}</span>
                        @endif
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            @endif
          </div>

          {{-- ── Ready to request (informational — the tenant requests from their side) ── --}}
          <div class="rp-sec">
            <h2>{{ __('Ready to request') }}</h2>
            <p class="hint">{{ __('Tenants whose confirmed rewards have cleared the holding period and the minimum — they can request a payout from their invite page.') }}</p>
            @if ($eligible->isEmpty())
              <div class="rp-empty">{{ __('No tenants currently have a withdrawable balance.') }}</div>
            @else
              <table class="rp-table">
                <thead><tr><th>{{ __('Tenant') }}</th><th>{{ __('Phone') }}</th><th>{{ __('Rewards') }}</th><th>{{ __('Withdrawable') }}</th></tr></thead>
                <tbody>
                  @foreach ($eligible as $t)
                    <tr>
                      <td>{{ $t->name }}</td>
                      <td>{{ $t->phone ?: '—' }}</td>
                      <td>{{ $t->reward_count }}</td>
                      <td class="rp-amt">{{ $currency }} {{ number_format($t->payable, 0) }}</td>
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

          {{-- ── Recent referral activity ── --}}
          <div class="rp-sec">
            <h2>{{ __('Recent referral activity') }}</h2>
            <p class="hint">{{ __('Every invite tenants have made — visible from the moment it is created, long before anything is payable.') }}</p>
            @php
              $refStatusMeta = [
                'pending'      => [__('Invite sent'), 'pending'],
                'lead_created' => [__('Signed up'), 'processing'],
                'confirmed'    => [__('Confirmed'), 'paid'],
                'paid'         => [__('Reward paid'), 'paid'],
                'clawed_back'  => [__('Clawed back'), 'failed'],
                'rejected'     => [__('Rejected'), 'cancelled'],
                'expired'      => [__('Expired'), 'cancelled'],
              ];
            @endphp
            @if ($recent->isEmpty())
              <div class="rp-empty">{{ __('No referrals yet.') }}</div>
            @else
              <table class="rp-table">
                <thead><tr><th>{{ __('Referrer') }}</th><th>{{ __('Invited landlord') }}</th><th>{{ __('Status') }}</th><th>{{ __('When') }}</th></tr></thead>
                <tbody>
                  @foreach ($recent as $r)
                    @php [$lbl, $cls] = $refStatusMeta[$r->status] ?? [ucfirst($r->status), 'cancelled']; @endphp
                    <tr>
                      <td>{{ optional($r->referrer)->first_name }} {{ optional($r->referrer)->last_name }} <span style="color:#9aa2ad;">#{{ $r->referrer_user_id }}</span></td>
                      <td>
                        {{ $r->invitee_name ?: ($r->invitee_company ?: '—') }}
                        @if ($r->invitee_phone || $r->invitee_email)<div style="font-size:11.5px;color:#9aa2ad;">{{ $r->invitee_phone ?: $r->invitee_email }}</div>@endif
                      </td>
                      <td><span class="rp-chip rp-chip--{{ $cls }}">{{ $lbl }}</span>@if($r->needs_review)<span class="rp-chip rp-chip--pending" style="margin-left:5px;">{{ __('review') }}</span>@endif</td>
                      <td>{{ $r->created_at->format('M j, Y') }}</td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            @endif
          </div>

          {{-- ── Graduations (tenant → affiliate) ── --}}
          <div class="rp-sec">
            <h2>{{ __('Graduations') }}</h2>
            <p class="hint">{{ __('Tenants who converted to affiliates — proven referrers who upgraded to the full program.') }}</p>
            @if ($graduations->isEmpty())
              <div class="rp-empty">{{ __('No graduations yet.') }}</div>
            @else
              <table class="rp-table">
                <thead><tr><th>{{ __('Tenant') }}</th><th>{{ __('Affiliate code') }}</th><th>{{ __('Graduated') }}</th></tr></thead>
                <tbody>
                  @foreach ($graduations as $g)
                    <tr>
                      <td>{{ $g->name }} <span style="color:#9aa2ad;">#{{ $g->user_id }}</span></td>
                      <td style="font-family:monospace;font-size:12.5px;">{{ $g->code }}</td>
                      <td>{{ $g->graduated_at ? $g->graduated_at->format('M j, Y') : '—' }}</td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            @endif
          </div>

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

{{-- Review & release modal (shared, populated per request) --}}
<div id="rpApproveModal" style="display:none;position:fixed;inset:0;z-index:1050;background:rgba(20,23,28,.5);align-items:center;justify-content:center;padding:20px;">
  <div style="background:#fff;border-radius:16px;max-width:440px;width:100%;padding:24px;box-shadow:0 20px 60px rgba(0,0,0,.25);">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px;">
      <h3 style="margin:0;font-size:17px;font-weight:700;color:#1b1e22;">{{ __('Release payout') }}</h3>
      <button type="button" onclick="document.getElementById('rpApproveModal').style.display='none';" style="border:none;background:transparent;font-size:22px;line-height:1;color:#9aa2ad;cursor:pointer;">&times;</button>
    </div>
    <p style="margin:0 0 16px;font-size:13px;color:#6b7280;"><span id="rpApTenant"></span> — <b id="rpApAmount" style="color:#0F6E56;"></b> <span style="color:#9aa2ad;">→</span> <span id="rpApPhone" style="font-family:monospace;"></span></p>
    <form method="POST" id="rpApproveForm" action="">
      @csrf
      <div style="display:flex;gap:8px;margin-bottom:14px;">
        <label style="flex:1;border:1px solid #e6e1d8;border-radius:10px;padding:10px;text-align:center;cursor:pointer;font-size:13px;">
          <input type="radio" name="method" value="b2c" checked style="margin-right:6px;">{{ __('M-Pesa B2C') }}
        </label>
        <label style="flex:1;border:1px solid #e6e1d8;border-radius:10px;padding:10px;text-align:center;cursor:pointer;font-size:13px;">
          <input type="radio" name="method" value="manual" style="margin-right:6px;">{{ __('Manual') }}
        </label>
      </div>
      <label style="display:block;font-size:12.5px;font-weight:600;color:#4a4f57;margin-bottom:6px;">{{ __('Notes (optional)') }}</label>
      <textarea name="notes" rows="2" style="width:100%;border:1px solid #e6e1d8;border-radius:10px;padding:10px;font-size:13.5px;" placeholder="{{ __('e.g. M-Pesa ref, reason…') }}"></textarea>
      <button type="submit" class="rp-btn rp-btn--pay" style="width:100%;margin-top:14px;">{{ __('Release payout') }}</button>
    </form>
  </div>
</div>
<script>
  function rpOpenApprove(action, tenant, amount, phone){
    document.getElementById('rpApproveForm').setAttribute('action', action);
    document.getElementById('rpApTenant').textContent = tenant || '—';
    document.getElementById('rpApAmount').textContent = amount || '';
    document.getElementById('rpApPhone').textContent = phone || '—';
    document.getElementById('rpApproveModal').style.display = 'flex';
  }
</script>
@endsection
