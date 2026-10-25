@extends('admin.layouts.app')

@section('content')
<div class="main-content">
  <div class="page-content">
    <div class="container-fluid">
      <div class="page-content-wrapper p-30 radius-20" style="background:#f6f7f9;">

        <style>
          .ocs-top{display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:4px;}
          .ocs-top h1{font-size:21px;font-weight:700;color:#1b1e22;margin:0;}
          .ocs-meta{font-size:12.5px;color:#9aa2ad;text-transform:capitalize;}
          .ocs-flash{border-radius:10px;padding:11px 14px;font-size:13.5px;margin:14px 0;}
          .ocs-flash--ok{background:#E1F5EE;border:1px solid #9ad9c4;color:#0F6E56;} .ocs-flash--err{background:#FBE9E7;border:1px solid #f0b8b0;color:#B42318;}
          .ocs-tiles{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin:16px 0;}
          @media (max-width:720px){.ocs-tiles{grid-template-columns:repeat(2,1fr);}}
          .ocs-tile{background:#fff;border:1px solid #ececec;border-radius:12px;padding:14px 16px;}
          .ocs-tile .n{font-size:24px;font-weight:750;color:#1b1e22;line-height:1;font-variant-numeric:tabular-nums;}
          .ocs-tile .l{font-size:11.5px;color:#9aa2ad;margin-top:5px;}
          .ocs-tile--go .n{color:#0F6E56;}
          .ocs-bar{display:flex;gap:10px;flex-wrap:wrap;align-items:center;margin:4px 0 18px;}
          .ocs-send{border:none;border-radius:10px;background:#185FA5;color:#fff;font-size:14px;font-weight:700;padding:11px 20px;cursor:pointer;}
          .ocs-send:hover{background:#0F4A84;} .ocs-send:disabled{background:#c7cdd4;cursor:not-allowed;}
          .ocs-del{border:1px solid #f0b8b0;background:#fff;color:#B42318;border-radius:10px;font-size:13px;font-weight:600;padding:10px 16px;cursor:pointer;}
          .ocs-note{border-radius:10px;padding:10px 14px;font-size:12.5px;}
          .ocs-note--warn{background:#FEF6EC;border:1px solid #F5DCB3;color:#8a5a12;}
          .ocs-card{background:#fff;border:1px solid #ececec;border-radius:14px;overflow:hidden;box-shadow:0 1px 3px rgba(16,24,40,.04);margin-bottom:18px;}
          .ocs-card h3{font-size:14px;font-weight:700;color:#1b1e22;margin:0;padding:14px 16px;border-bottom:1px solid #f1f1f1;}
          .ocs-addform{padding:14px 16px;}
          .ocs-addform textarea{width:100%;border:1px solid #e1e4e8;border-radius:10px;padding:10px 12px;font-size:13.5px;font-family:inherit;line-height:1.5;}
          .ocs-addform button{margin-top:10px;border:none;border-radius:9px;background:#185FA5;color:#fff;font-size:13px;font-weight:650;padding:9px 16px;cursor:pointer;}
          .ocs-table{width:100%;border-collapse:collapse;}
          .ocs-table th{text-align:left;font-size:11px;text-transform:uppercase;letter-spacing:.05em;color:#9aa2ad;font-weight:600;padding:11px 16px;border-bottom:1px solid #eee;background:#fafafa;}
          .ocs-table td{padding:12px 16px;border-bottom:1px solid #f3f0ea;font-size:13px;color:#333;}
          .ocs-pill{display:inline-block;font-size:11px;font-weight:650;padding:3px 9px;border-radius:999px;text-transform:capitalize;}
          .ocs-pill--pending{background:#f3f4f6;color:#6b7280;} .ocs-pill--sent{background:#E6F1FB;color:#0F4A84;} .ocs-pill--converted{background:#E1F5EE;color:#0F6E56;} .ocs-pill--failed{background:#FBE9E7;color:#B42318;}
          .ocs-back{font-size:12.5px;color:#185FA5;text-decoration:none;}
        </style>

        <a href="{{ route('admin.outreach.index') }}" class="ocs-back">← {{ __('All campaigns') }}</a>

        <div class="ocs-top" style="margin-top:8px;">
          <div>
            <h1>{{ $campaign->name }}</h1>
            <div class="ocs-meta">{{ $campaign->type }} · {{ $campaign->status }}@if($campaign->sent_at) · {{ __('sent') }} {{ $campaign->sent_at->format('M j, Y') }}@endif</div>
          </div>
        </div>

        @if (session('success'))<div class="ocs-flash ocs-flash--ok">{{ session('success') }}</div>@endif
        @if (session('error'))<div class="ocs-flash ocs-flash--err">{{ session('error') }}</div>@endif

        <div class="ocs-tiles">
          <div class="ocs-tile"><div class="n">{{ $counts['total'] }}</div><div class="l">{{ __('Recipients') }}</div></div>
          <div class="ocs-tile"><div class="n">{{ $counts['pending'] }}</div><div class="l">{{ __('Not sent yet') }}</div></div>
          <div class="ocs-tile"><div class="n">{{ $counts['sent'] }}</div><div class="l">{{ __('Sent, awaiting') }}</div></div>
          <div class="ocs-tile ocs-tile--go"><div class="n">{{ $counts['converted'] }}</div><div class="l">{{ __('Signed up') }}</div></div>
        </div>

        @if ($campaign->isRecruitment() && ! $inviteActive)
          <div class="ocs-note ocs-note--warn" style="margin-bottom:14px;">⚠ {{ __('No active invitation link — generate one on Affiliates → Applications before sending.') }}</div>
        @endif

        <div class="ocs-bar">
          <form method="POST" action="{{ route('admin.outreach.send', $campaign->id) }}"
            data-cs-confirm="{{ __('Send to :n pending recipient(s) now? Follow-ups to non-responders become automatic.', ['n' => $counts['pending']]) }}">
            @csrf
            <button type="submit" class="ocs-send" {{ $counts['pending'] === 0 ? 'disabled' : '' }}>
              {{ $counts['pending'] > 0 ? __('Send to :n pending', ['n' => $counts['pending']]) : __('Nothing pending to send') }}
            </button>
          </form>
          <form method="POST" action="{{ route('admin.outreach.delete', $campaign->id) }}"
            data-cs-confirm="{{ __('Delete this campaign and its recipient list? This cannot be undone.') }}">
            @csrf
            <button type="submit" class="ocs-del">{{ __('Delete campaign') }}</button>
          </form>
        </div>
        <div style="font-size:12px;color:#9aa2ad;margin:-8px 0 16px;">
          {{ __('Sends are paced at :rate/hour so the batch never bursts past your host\'s limit — recipients trickle out automatically, no manual waves.', ['rate' => $campaign->send_per_hour ?: \App\Models\OutreachCampaign::DEFAULT_SEND_PER_HOUR]) }}
        </div>

        <div class="ocs-card">
          <h3>{{ __('Add more recipients') }}</h3>
          <form method="POST" action="{{ route('admin.outreach.recipients', $campaign->id) }}" class="ocs-addform">
            @csrf
            <textarea name="recipients" rows="3" placeholder="email, First Name — one per line"></textarea>
            <button type="submit">{{ __('Add recipients') }}</button>
          </form>
        </div>

        <div class="ocs-card">
          <h3>{{ __('Recipients') }}</h3>
          <table class="ocs-table">
            <thead><tr>
              <th>{{ __('Email') }}</th><th>{{ __('Name') }}</th><th>{{ __('Status') }}</th><th>{{ __('Sent') }}</th><th>{{ __('Follow-ups') }}</th>
            </tr></thead>
            <tbody>
              @forelse ($recipients as $r)
                <tr>
                  <td>{{ $r->email }}</td>
                  <td>{{ $r->first_name ?: '—' }}</td>
                  <td><span class="ocs-pill ocs-pill--{{ $r->status }}">{{ $r->status }}</span></td>
                  <td>{{ $r->sent_at ? $r->sent_at->format('M j') : '—' }}</td>
                  <td>{{ $r->follow_up_count }}</td>
                </tr>
              @empty
                <tr><td colspan="5" style="text-align:center;color:#9aa2ad;padding:24px;">{{ __('No recipients yet.') }}</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
        @if ($recipients->hasPages())<div class="mt-3">{{ $recipients->links() }}</div>@endif

      </div>
    </div>
  </div>
</div>
@endsection
