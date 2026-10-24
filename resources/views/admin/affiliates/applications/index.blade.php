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
          .aap-fieldrow{font-size:12.5px;color:#4a4f57;margin-top:6px;max-width:44ch;line-height:1.5;}
          .aap-fieldrow .k{color:#9aa2ad;font-weight:600;}
          .aap-consent{display:inline-flex;align-items:center;gap:5px;font-size:11px;font-weight:600;color:#0F6E56;background:#E1F5EE;border-radius:999px;padding:3px 9px;margin-top:8px;}
          .aap-consent--missing{color:#B45309;background:#FEF3E7;}
          .aap-pill{display:inline-block;font-size:11px;font-weight:650;padding:3px 10px;border-radius:999px;text-transform:capitalize;}
          .aap-pill--pending{background:#FEF3E7;color:#B45309;} .aap-pill--approved{background:#E1F5EE;color:#0F6E56;} .aap-pill--rejected{background:#f3f4f6;color:#6b7280;}
          .aap-btn{border:none;border-radius:8px;padding:7px 13px;font-size:12.5px;font-weight:650;cursor:pointer;}
          .aap-btn--go{background:#185FA5;color:#fff;} .aap-btn--no{background:#fff;border:1px solid #f0b8b0;color:#B42318;}
          .aap-actions{display:flex;gap:7px;flex-wrap:wrap;}
          .aap-empty{padding:30px;text-align:center;color:#9aa2ad;font-size:13.5px;}
          .aap-flash{border-radius:10px;padding:11px 14px;font-size:13.5px;margin-bottom:16px;}
          .aap-flash--ok{background:#E1F5EE;border:1px solid #9ad9c4;color:#0F6E56;} .aap-flash--err{background:#FBE9E7;border:1px solid #f0b8b0;color:#B42318;}
          .aap-btn--view{background:#fff;border:1px solid #d7dbe0;color:#185FA5;}
          .aap-btn--view:hover{border-color:#185FA5;background:#f3f8fd;}
          /* applicant detail modal */
          .am-status-row{display:flex;align-items:center;gap:10px;margin-bottom:16px;}
          .am-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px 20px;margin-bottom:4px;}
          @media (max-width:520px){.am-grid{grid-template-columns:1fr;}}
          .am-k{font-size:10.5px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:#9aa2ad;margin-bottom:3px;}
          .am-v{font-size:13.5px;color:#1b1e22;line-height:1.5;word-break:break-word;}
          .am-block{margin-top:16px;}
          .am-block .am-v{white-space:pre-wrap;color:#44474d;}
          #appModal .modal-body{padding:22px 24px;}
          #appModal .modal-footer{gap:8px;}
          /* [hidden] must beat Bootstrap's .modal-footer/.am-block display rules */
          #appModal [hidden]{display:none!important;}
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
                      @if ($a->county || $a->country)
                        <div class="aap-meta">{{ $a->county }}{{ $a->town ? ' · '.$a->town : '' }}{{ $a->country ? ', '.$a->country : '' }}</div>
                      @endif
                    </td>
                    <td>
                      <div>{{ $a->email }}</div>
                      <div class="aap-meta">{{ $a->phone }}</div>
                    </td>
                    <td>{{ $a->created_at->format('M j, Y') }}</td>
                    <td>
                      <span class="aap-pill aap-pill--{{ $a->status }}">{{ $a->status }}</span>
                      @if ($a->agreed_at)
                        <div class="aap-consent" style="margin-top:6px;" title="{{ __('Version') }} {{ $a->consent_version }}{{ $a->consent_ip ? ' · IP '.$a->consent_ip : '' }}">
                          <svg width="11" height="11" viewBox="0 0 24 24" fill="none"><path d="M20 6L9 17l-5-5" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/></svg>
                          {{ __('Terms accepted') }}
                        </div>
                      @else
                        <div class="aap-consent aap-consent--missing" style="margin-top:6px;">{{ __('No consent') }}</div>
                      @endif
                    </td>
                    <td>
                      <button type="button" class="aap-btn aap-btn--view"
                        data-bs-toggle="modal" data-bs-target="#appModal"
                        data-name="{{ $a->name }}"
                        data-email="{{ $a->email }}"
                        data-phone="{{ $a->phone }}"
                        data-location="{{ trim(collect([$a->county, $a->town])->filter()->implode(' · ').($a->country ? ', '.$a->country : ''), ', ') }}"
                        data-experience="{{ $a->experience }}"
                        data-finance="{{ $a->finance_contacts }}"
                        data-pitch="{{ $a->pitch }}"
                        data-status="{{ $a->status }}"
                        data-applied="{{ $a->created_at->format('M j, Y') }}"
                        data-consent="{{ $a->agreed_at ? $a->agreed_at->format('M j, Y') : '' }}"
                        data-consent-version="{{ $a->consent_version }}"
                        data-consent-ip="{{ $a->consent_ip }}"
                        data-approve-url="{{ route('admin.affiliates.applications.approve', $a->id) }}"
                        data-reject-url="{{ route('admin.affiliates.applications.reject', $a->id) }}">{{ __('View') }}</button>
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

{{-- Applicant detail modal — one shared modal, populated from the clicked row's data-* attributes.
     All applicant text is injected via textContent (never innerHTML), so user-supplied fields can't
     inject markup. Approve/Decline live here so an admin can read the full application and act in one
     place; the form actions are set per-application on open. --}}
<div class="modal fade cs-modal" id="appModal" tabindex="-1" aria-labelledby="appModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="appModalLabel">{{ __('Applicant') }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
      </div>
      <div class="modal-body">
        <div class="am-status-row">
          <span id="am-status" class="aap-pill"></span>
          <span class="aap-meta">{{ __('Applied') }} <span id="am-applied"></span></span>
        </div>
        <div class="am-grid">
          <div><div class="am-k">{{ __('Name') }}</div><div class="am-v" id="am-name"></div></div>
          <div><div class="am-k">{{ __('Location') }}</div><div class="am-v" id="am-location"></div></div>
          <div><div class="am-k">{{ __('Email') }}</div><div class="am-v" id="am-email"></div></div>
          <div><div class="am-k">{{ __('Phone') }}</div><div class="am-v" id="am-phone"></div></div>
          <div><div class="am-k">{{ __('Terms consent') }}</div><div class="am-v" id="am-consent"></div></div>
        </div>
        <div class="am-block" id="am-experience-wrap"><div class="am-k">{{ __('Sales / real-estate experience') }}</div><div class="am-v" id="am-experience"></div></div>
        <div class="am-block" id="am-finance-wrap"><div class="am-k">{{ __('Banking / lending contacts') }}</div><div class="am-v" id="am-finance"></div></div>
        <div class="am-block" id="am-pitch-wrap"><div class="am-k">{{ __('How they\'ll promote Centresidence') }}</div><div class="am-v" id="am-pitch"></div></div>
      </div>
      <div class="modal-footer" id="am-actions">
        <form method="POST" id="am-reject-form">
          @csrf
          <button type="submit" class="aap-btn aap-btn--no" id="am-reject-btn" data-cs-confirm="{{ __('Decline this application? The applicant will be emailed a polite notice — this can\'t be undone.') }}">{{ __('Decline') }}</button>
        </form>
        <form method="POST" id="am-approve-form">
          @csrf
          <button type="submit" class="aap-btn aap-btn--go" id="am-approve-btn">{{ __('Create affiliate') }}</button>
        </form>
      </div>
      <div class="modal-footer" id="am-done" hidden>
        <span class="aap-meta" id="am-done-note"></span>
      </div>
    </div>
  </div>
</div>

<script>
  (function () {
    var modal = document.getElementById('appModal');
    if (!modal) return;

    function setText(id, val) { var el = document.getElementById(id); if (el) el.textContent = val || '—'; }
    function toggleBlock(wrapId, valId, val) {
      var wrap = document.getElementById(wrapId);
      if (!wrap) return;
      if (val && val.trim() !== '') { wrap.hidden = false; document.getElementById(valId).textContent = val; }
      else { wrap.hidden = true; }
    }

    modal.addEventListener('show.bs.modal', function (event) {
      var d = (event.relatedTarget || {}).dataset || {};

      setText('am-name', d.name);
      setText('am-email', d.email);
      setText('am-phone', d.phone);
      setText('am-location', d.location);
      setText('am-applied', d.applied);

      // Consent line
      if (d.consent) {
        var c = '✓ ' + d.consent;
        if (d.consentVersion) c += ' · ' + d.consentVersion;
        if (d.consentIp) c += ' · IP ' + d.consentIp;
        setText('am-consent', c);
      } else {
        setText('am-consent', 'No recorded consent');
      }

      toggleBlock('am-experience-wrap', 'am-experience', d.experience);
      toggleBlock('am-finance-wrap', 'am-finance', d.finance);
      toggleBlock('am-pitch-wrap', 'am-pitch', d.pitch);

      // Status pill
      var pill = document.getElementById('am-status');
      pill.textContent = d.status || '';
      pill.className = 'aap-pill aap-pill--' + (d.status || 'pending');

      // Actions: only pending applications can be acted on.
      var actions = document.getElementById('am-actions');
      var done = document.getElementById('am-done');
      if (d.status === 'pending') {
        actions.hidden = false; done.hidden = true;
        document.getElementById('am-approve-form').action = d.approveUrl || '';
        document.getElementById('am-reject-form').action = d.rejectUrl || '';
        document.getElementById('am-approve-btn').setAttribute(
          'data-cs-confirm',
          'Create an affiliate account for ' + (d.name || 'this applicant') + ' and send their login details?'
        );
      } else {
        actions.hidden = true; done.hidden = false;
        document.getElementById('am-done-note').textContent =
          d.status === 'approved' ? 'Affiliate account already created.' : 'This application was declined.';
      }
    });
  })();
</script>
@endsection
