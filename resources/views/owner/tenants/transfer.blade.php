@extends('owner.layouts.app')

@section('content')
<div class="main-content">
  <div class="page-content">
    <div class="container-fluid">
      <div class="page-content-wrapper p-30 radius-20" style="background:#f6f7f9;">

        <style>
          .tt-head h1{font-size:22px;font-weight:700;color:#1b1e22;margin:0 0 4px;}
          .tt-head p{color:#6b7280;font-size:13.5px;margin:0 0 20px;}
          .tt-back{display:inline-flex;align-items:center;gap:6px;font-size:13px;font-weight:600;color:#6b7280;text-decoration:none;margin-bottom:14px;}
          .tt-back:hover{color:#185FA5;}
          .tt-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px;align-items:start;}
          @media(max-width:820px){.tt-grid{grid-template-columns:1fr;}}
          .tt-card{background:#fff;border:1px solid #ececec;border-radius:14px;padding:20px 22px;box-shadow:0 1px 3px rgba(16,24,40,.04);margin-bottom:16px;}
          .tt-card h2{font-size:15px;font-weight:700;color:#1b1e22;margin:0 0 14px;display:flex;align-items:center;gap:8px;}
          .tt-kv{display:flex;justify-content:space-between;align-items:baseline;padding:8px 0;border-top:1px solid #f3f0ea;font-size:13.5px;}
          .tt-kv:first-of-type{border-top:none;}
          .tt-kv .k{color:#6b7280;} .tt-kv .v{font-weight:650;color:#1b1e22;}
          .tt-notice{display:flex;gap:11px;align-items:flex-start;border-radius:12px;padding:13px 15px;font-size:13px;line-height:1.55;margin-bottom:16px;}
          .tt-notice--warn{background:#FEF3E7;border:1px solid #F5D9A8;color:#8a5a12;}
          .tt-notice--ok{background:#E1F5EE;border:1px solid #9ad9c4;color:#0F6E56;}
          .tt-inv{width:100%;border-collapse:collapse;margin-top:4px;}
          .tt-inv th{text-align:left;font-size:11px;text-transform:uppercase;letter-spacing:.04em;color:#9aa2ad;font-weight:600;padding:7px 8px;border-bottom:1px solid #eee;}
          .tt-inv td{padding:9px 8px;border-bottom:1px solid #f3f0ea;font-size:13px;color:#333;}
          .tt-inv tfoot td{font-weight:750;color:#1b1e22;border-top:1px solid #e6e1d8;border-bottom:none;}
          .tt-field{margin-bottom:14px;}
          .tt-field label{display:block;font-size:12.5px;font-weight:650;color:#4a4f57;margin-bottom:6px;}
          .tt-field select,.tt-field textarea{width:100%;border:1px solid #e6e1d8;border-radius:10px;padding:11px 13px;font-size:14px;color:#1b1e22;outline:none;background:#fff;}
          .tt-field select:focus,.tt-field textarea:focus{border-color:#185FA5;box-shadow:0 0 0 3px rgba(24,95,165,.12);}
          .tt-btn{border:none;border-radius:11px;padding:13px 18px;font-size:14.5px;font-weight:700;cursor:pointer;}
          .tt-btn--primary{background:#185FA5;color:#fff;width:100%;}
          .tt-btn--primary:disabled{opacity:.5;cursor:not-allowed;}
          .tt-flash{border-radius:10px;padding:11px 14px;font-size:13.5px;margin-bottom:16px;}
          .tt-flash--err{background:#FBE9E7;border:1px solid #f0b8b0;color:#B42318;}
          .tt-new-terms{background:#f6f7f9;border:1px solid #eef0f3;border-radius:10px;padding:12px 14px;margin-top:2px;font-size:12.5px;color:#6b7280;display:none;}
        </style>

        <a href="{{ route('owner.tenant.details', $tenant->id) }}" class="tt-back">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M15 6l-6 6 6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
          {{ __('Back to tenant') }}
        </a>

        <div class="tt-head">
          <h1>{{ __('Transfer') }} {{ optional($tenant->user)->name ?? __('tenant') }}</h1>
          <p>{{ __('Move this tenant to another unit in') }} <b>{{ optional($tenant->property)->name }}</b> {{ __('— without closing their tenancy. Their history and account carry over.') }}</p>
        </div>

        @if (session('error'))<div class="tt-flash tt-flash--err">{{ session('error') }}</div>@endif
        @if ($errors->any())<div class="tt-flash tt-flash--err">{{ $errors->first() }}</div>@endif

        <div class="tt-grid">
          {{-- Left: current + standing invoices --}}
          <div>
            <div class="tt-card">
              <h2>{{ __('Current unit') }}</h2>
              <div class="tt-kv"><span class="k">{{ __('Unit') }}</span><span class="v">{{ optional($tenant->unit)->unit_name ?? ('#' . $tenant->unit_id) }}</span></div>
              <div class="tt-kv"><span class="k">{{ __('Rent') }}</span><span class="v">{{ currencyPrice($tenant->general_rent) }}</span></div>
              <div class="tt-kv"><span class="k">{{ __('Deposit held') }}</span><span class="v">{{ currencyPrice($tenant->security_deposit) }}</span></div>
            </div>

            <div class="tt-card">
              <h2>{{ __('Standing invoices') }}</h2>
              @if ($standingInvoices->isEmpty())
                <div class="tt-notice tt-notice--ok" style="margin-bottom:0;">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" style="flex:none;margin-top:1px;"><path d="M20 6L9 17l-5-5" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                  <span>{{ __('No unpaid invoices — nothing outstanding to consider before the move.') }}</span>
                </div>
              @else
                <div class="tt-notice tt-notice--warn">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" style="flex:none;margin-top:1px;"><path d="M12 8v5M12 16v.5M10.3 3.9 2.4 18a1.9 1.9 0 0 0 1.7 2.9h15.8a1.9 1.9 0 0 0 1.7-2.9L13.7 3.9a1.9 1.9 0 0 0-3.4 0z" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                  <span>{{ __('This tenant has') }} <b>{{ currencyPrice($outstandingTotal) }}</b> {{ __('in unpaid invoices. These stay on their account after the move — settle them first, or arrange off-system as you see fit. Nothing here is auto-cancelled.') }}</span>
                </div>
                <table class="tt-inv">
                  <thead><tr><th>{{ __('Invoice') }}</th><th>{{ __('Month') }}</th><th style="text-align:right;">{{ __('Amount') }}</th></tr></thead>
                  <tbody>
                    @foreach ($standingInvoices as $inv)
                      <tr><td>#{{ $inv->invoice_no ?? $inv->id }}</td><td>{{ $inv->month ?? '—' }}</td><td style="text-align:right;">{{ currencyPrice($inv->amount) }}</td></tr>
                    @endforeach
                  </tbody>
                  <tfoot><tr><td colspan="2">{{ __('Total outstanding') }}</td><td style="text-align:right;">{{ currencyPrice($outstandingTotal) }}</td></tr></tfoot>
                </table>
              @endif
            </div>
          </div>

          {{-- Right: the transfer form --}}
          <div class="tt-card">
            <h2>{{ __('Move to') }}</h2>
            @if ($vacantUnits->isEmpty())
              <div class="tt-notice tt-notice--warn" style="margin-bottom:0;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" style="flex:none;margin-top:1px;"><path d="M12 8v5M12 16v.5M10.3 3.9 2.4 18a1.9 1.9 0 0 0 1.7 2.9h15.8a1.9 1.9 0 0 0 1.7-2.9L13.7 3.9a1.9 1.9 0 0 0-3.4 0z" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                <span>{{ __('There are no vacant units in this property right now. Free up a unit first, then transfer.') }}</span>
              </div>
            @else
              <form method="POST" action="{{ route('owner.tenant.transfer.store', $tenant->id) }}" id="ttForm">
                @csrf
                <div class="tt-field">
                  <label>{{ __('New unit') }}</label>
                  <select name="to_unit_id" id="ttUnit" required>
                    <option value="">{{ __('Select a vacant unit…') }}</option>
                    @foreach ($vacantUnits as $u)
                      <option value="{{ $u->id }}"
                        data-rent="{{ currencyPrice($u->general_rent) }}"
                        data-deposit="{{ currencyPrice($u->security_deposit) }}">{{ $u->unit_name ?? ('#' . $u->id) }}</option>
                    @endforeach
                  </select>
                </div>
                <div class="tt-new-terms" id="ttTerms">
                  <div style="display:flex;justify-content:space-between;padding:2px 0;"><span>{{ __('New rent') }}</span><b id="ttRent">—</b></div>
                  <div style="display:flex;justify-content:space-between;padding:2px 0;"><span>{{ __('New deposit') }}</span><b id="ttDeposit">—</b></div>
                  <div style="margin-top:6px;color:#9aa2ad;">{{ __('The new unit\'s rent, deposit and due date apply going forward. The deposit already held is not auto-adjusted — settle any difference off-system.') }}</div>
                </div>
                <div class="tt-field" style="margin-top:14px;">
                  <label>{{ __('Note') }} <span style="color:#9aa2ad;font-weight:500;">({{ __('optional — recorded on the transfer') }})</span></label>
                  <textarea name="note" rows="3" maxlength="1000" placeholder="{{ __('e.g. reason for the move, any off-system arrangement…') }}">{{ old('note') }}</textarea>
                </div>
                <button type="submit" class="tt-btn tt-btn--primary" id="ttSubmit" disabled
                  data-cs-confirm="{{ __('Transfer this tenant to the selected unit? Their tenancy stays open and any unpaid invoices remain on their account.') }}"
                  data-cs-confirm-title="{{ __('Confirm transfer') }}" data-cs-confirm-ok="{{ __('Yes, transfer') }}">{{ __('Transfer tenant') }}</button>
              </form>
            @endif
          </div>
        </div>

      </div>
    </div>
  </div>
</div>

<script>
  (function () {
    var sel = document.getElementById('ttUnit');
    if (!sel) return;
    var terms = document.getElementById('ttTerms'), submit = document.getElementById('ttSubmit');
    sel.addEventListener('change', function () {
      var o = sel.options[sel.selectedIndex];
      if (sel.value) {
        document.getElementById('ttRent').textContent = o.getAttribute('data-rent') || '—';
        document.getElementById('ttDeposit').textContent = o.getAttribute('data-deposit') || '—';
        terms.style.display = 'block';
        submit.disabled = false;
      } else {
        terms.style.display = 'none';
        submit.disabled = true;
      }
    });
  })();
</script>
@endsection
