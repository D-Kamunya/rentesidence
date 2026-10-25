@extends('admin.layouts.app')

@section('content')
<div class="main-content">
  <div class="page-content">
    <div class="container-fluid">
      <div class="page-content-wrapper p-30 radius-20" style="background:#f6f7f9;">

        <style>
          .oc-head{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:6px;}
          .oc-head h1{font-size:22px;font-weight:700;color:#1b1e22;margin:0;}
          .oc-sub{color:#6b7280;font-size:13.5px;margin:0 0 20px;}
          .oc-btn{display:inline-flex;align-items:center;gap:7px;border:none;border-radius:9px;background:#185FA5;color:#fff;font-size:13.5px;font-weight:650;padding:10px 16px;cursor:pointer;text-decoration:none;}
          .oc-btn:hover{background:#0F4A84;color:#fff;}
          .oc-flash{border-radius:10px;padding:11px 14px;font-size:13.5px;margin-bottom:16px;}
          .oc-flash--ok{background:#E1F5EE;border:1px solid #9ad9c4;color:#0F6E56;} .oc-flash--err{background:#FBE9E7;border:1px solid #f0b8b0;color:#B42318;}
          .oc-card{background:#fff;border:1px solid #ececec;border-radius:14px;overflow:hidden;box-shadow:0 1px 3px rgba(16,24,40,.04);}
          .oc-table{width:100%;border-collapse:collapse;}
          .oc-table th{text-align:left;font-size:11px;text-transform:uppercase;letter-spacing:.05em;color:#9aa2ad;font-weight:600;padding:12px 16px;border-bottom:1px solid #eee;background:#fafafa;}
          .oc-table td{padding:14px 16px;border-bottom:1px solid #f3f0ea;font-size:13.5px;color:#333;vertical-align:middle;}
          .oc-name{font-weight:650;color:#1b1e22;text-decoration:none;}
          .oc-name:hover{color:#185FA5;}
          .oc-pill{display:inline-block;font-size:11px;font-weight:650;padding:3px 10px;border-radius:999px;text-transform:capitalize;}
          .oc-pill--draft{background:#f3f4f6;color:#6b7280;} .oc-pill--sending{background:#FEF3E7;color:#B45309;} .oc-pill--sent{background:#E6F1FB;color:#0F4A84;}
          .oc-num{font-variant-numeric:tabular-nums;font-weight:600;}
          .oc-empty{padding:34px;text-align:center;color:#9aa2ad;font-size:13.5px;}
        </style>

        <div class="oc-head">
          <h1>{{ __('Outreach Campaigns') }}</h1>
          <a href="{{ route('admin.outreach.create') }}" class="oc-btn">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
            {{ __('New campaign') }}
          </a>
        </div>
        <p class="oc-sub">{{ __('Paste a vetted list, send a system-styled email to each, and track who signs up — with automatic follow-ups to non-responders.') }}</p>

        @if (session('success'))<div class="oc-flash oc-flash--ok">{{ session('success') }}</div>@endif
        @if (session('error'))<div class="oc-flash oc-flash--err">{{ session('error') }}</div>@endif

        <div class="oc-card">
          @if ($campaigns->isEmpty())
            <div class="oc-empty">{{ __('No campaigns yet. Create one to start recruiting.') }}</div>
          @else
            <table class="oc-table">
              <thead><tr>
                <th>{{ __('Campaign') }}</th><th>{{ __('Status') }}</th><th>{{ __('Recipients') }}</th><th>{{ __('Sent') }}</th><th>{{ __('Signed up') }}</th><th>{{ __('Created') }}</th>
              </tr></thead>
              <tbody>
                @foreach ($campaigns as $c)
                  <tr>
                    <td>
                      <a href="{{ route('admin.outreach.show', $c->id) }}" class="oc-name">{{ $c->name }}</a>
                      <div style="font-size:11.5px;color:#9aa2ad;text-transform:capitalize;">{{ $c->type }}</div>
                    </td>
                    <td><span class="oc-pill oc-pill--{{ $c->status }}">{{ $c->status }}</span></td>
                    <td class="oc-num">{{ $c->recipients_count }}</td>
                    <td class="oc-num">{{ $c->sent_count }}</td>
                    <td class="oc-num" style="color:#0F6E56;">{{ $c->converted_count }}</td>
                    <td>{{ $c->created_at->format('M j, Y') }}</td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          @endif
        </div>

        @if ($campaigns->hasPages())<div class="mt-4">{{ $campaigns->links() }}</div>@endif

      </div>
    </div>
  </div>
</div>
@endsection
