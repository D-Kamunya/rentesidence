@extends('admin.layouts.app')

@section('content')
<div class="main-content">
  <div class="page-content">
    <div class="container-fluid">
      <div class="page-content-wrapper p-30 radius-20" style="background:#f6f7f9;">

        <style>
          .aap-head h1{font-size:22px;font-weight:700;color:#1b1e22;margin:0 0 4px;}
          .aap-head p{color:#6b7280;font-size:13.5px;margin:0 0 20px;}
          .aap-tabs{display:flex;flex-wrap:wrap;gap:8px;margin-bottom:18px;}
          .aap-tab{font-size:12.5px;font-weight:600;border-radius:8px;padding:7px 14px;text-decoration:none;border:1px solid #e6e1d8;background:#fff;color:#4a4f57;}
          .aap-tab.is-on{background:#185FA5;color:#fff;border-color:#185FA5;}
          .aap-card{background:#fff;border:1px solid #ececec;border-radius:14px;overflow:hidden;box-shadow:0 1px 3px rgba(16,24,40,.04);}
          .aap-table{width:100%;border-collapse:collapse;}
          .aap-table th{text-align:left;font-size:11px;text-transform:uppercase;letter-spacing:.05em;color:#9aa2ad;font-weight:600;padding:12px 16px;border-bottom:1px solid #eee;background:#fafafa;}
          .aap-table td{padding:14px 16px;border-bottom:1px solid #f3f0ea;font-size:13.5px;color:#333;vertical-align:top;}
          .aap-name{font-weight:650;color:#1b1e22;}
          .aap-meta{font-size:12px;color:#9aa2ad;margin-top:2px;}
          .aap-pitch{font-size:12.5px;color:#6b7280;margin-top:6px;max-width:42ch;line-height:1.5;}
          .aap-pill{display:inline-block;font-size:11px;font-weight:650;padding:3px 10px;border-radius:999px;text-transform:capitalize;}
          .aap-pill--pending{background:#FEF3E7;color:#B45309;} .aap-pill--approved{background:#E1F5EE;color:#0F6E56;} .aap-pill--rejected{background:#f3f4f6;color:#6b7280;}
          .aap-btn{border:none;border-radius:8px;padding:7px 13px;font-size:12.5px;font-weight:650;cursor:pointer;}
          .aap-btn--go{background:#185FA5;color:#fff;} .aap-btn--no{background:#fff;border:1px solid #f0b8b0;color:#B42318;}
          .aap-actions{display:flex;gap:7px;flex-wrap:wrap;}
          .aap-empty{padding:30px;text-align:center;color:#9aa2ad;font-size:13.5px;}
          .aap-flash{border-radius:10px;padding:11px 14px;font-size:13.5px;margin-bottom:16px;}
          .aap-flash--ok{background:#E1F5EE;border:1px solid #9ad9c4;color:#0F6E56;} .aap-flash--err{background:#FBE9E7;border:1px solid #f0b8b0;color:#B42318;}
        </style>

        <div class="aap-head">
          <h1>{{ __('Affiliate Applications') }}</h1>
          <p>{{ __('Prospects who applied via the public') }} <code>/become-an-affiliate</code> {{ __('page. Approve to create their account — a temporary password and login link go out by email and SMS, and they set their own on first sign-in.') }}</p>
        </div>

        @if (session('success'))<div class="aap-flash aap-flash--ok">{{ session('success') }}</div>@endif
        @if (session('error'))<div class="aap-flash aap-flash--err">{{ session('error') }}</div>@endif
        @include('partials.dev-credentials')

        <div class="aap-tabs">
          @foreach (['pending' => __('Pending'), 'approved' => __('Approved'), 'rejected' => __('Declined'), 'all' => __('All')] as $val => $label)
            <a href="{{ route('admin.affiliates.applications.index', ['status' => $val]) }}" class="aap-tab {{ $status === $val ? 'is-on' : '' }}">
              {{ $label }}@if ($val !== 'all' && ($counts[$val] ?? 0) > 0) ({{ $counts[$val] }})@endif
            </a>
          @endforeach
        </div>

        <div class="aap-card">
          @if ($applications->isEmpty())
            <div class="aap-empty">{{ __('No applications here.') }}</div>
          @else
            <table class="aap-table">
              <thead><tr>
                <th>{{ __('Applicant') }}</th><th>{{ __('Contact') }}</th><th>{{ __('Applied') }}</th><th>{{ __('Status') }}</th><th>{{ __('Action') }}</th>
              </tr></thead>
              <tbody>
                @foreach ($applications as $a)
                  <tr>
                    <td>
                      <div class="aap-name">{{ $a->name }}</div>
                      @if ($a->location)<div class="aap-meta">{{ $a->location }}</div>@endif
                      @if ($a->pitch)<div class="aap-pitch">{{ $a->pitch }}</div>@endif
                    </td>
                    <td>
                      <div>{{ $a->email }}</div>
                      <div class="aap-meta">{{ $a->phone }}</div>
                    </td>
                    <td>{{ $a->created_at->format('M j, Y') }}</td>
                    <td><span class="aap-pill aap-pill--{{ $a->status }}">{{ $a->status }}</span></td>
                    <td>
                      @if ($a->status === 'pending')
                        <div class="aap-actions">
                          <form method="POST" action="{{ route('admin.affiliates.applications.approve', $a->id) }}">
                            @csrf
                            <button type="submit" class="aap-btn aap-btn--go"
                              data-cs-confirm="{{ __('Create an affiliate account for :name and send their login details?', ['name' => $a->name]) }}">{{ __('Create affiliate') }}</button>
                          </form>
                          <form method="POST" action="{{ route('admin.affiliates.applications.reject', $a->id) }}">
                            @csrf
                            <button type="submit" class="aap-btn aap-btn--no"
                              data-cs-confirm="{{ __('Decline this application?') }}">{{ __('Decline') }}</button>
                          </form>
                        </div>
                      @elseif ($a->status === 'approved')
                        <span class="aap-meta">{{ __('Account created') }}</span>
                      @else
                        <span class="aap-meta">{{ __('Declined') }}</span>
                      @endif
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          @endif
        </div>

        @if ($applications->hasPages())<div class="mt-4">{{ $applications->links() }}</div>@endif

      </div>
    </div>
  </div>
</div>
@endsection
