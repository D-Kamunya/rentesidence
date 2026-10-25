@extends('admin.layouts.app')

@section('content')
<div class="main-content">
  <div class="page-content">
    <div class="container-fluid">
      <div class="page-content-wrapper p-30 radius-20" style="background:#f6f7f9;">

        <style>
          .ocf-wrap{max-width:860px;}
          .ocf-h1{font-size:22px;font-weight:700;color:#1b1e22;margin:0 0 4px;}
          .ocf-sub{color:#6b7280;font-size:13.5px;margin:0 0 20px;}
          .ocf-card{background:#fff;border:1px solid #ececec;border-radius:14px;padding:22px 24px;box-shadow:0 1px 3px rgba(16,24,40,.04);margin-bottom:18px;}
          .ocf-field{margin-bottom:16px;}
          .ocf-field label{display:block;font-size:12.5px;font-weight:650;color:#4a4f57;margin-bottom:6px;}
          .ocf-field input,.ocf-field select,.ocf-field textarea{width:100%;border:1px solid #e1e4e8;border-radius:10px;padding:11px 13px;font-size:14px;color:#1b1e22;outline:none;font-family:inherit;}
          .ocf-field textarea{line-height:1.5;resize:vertical;}
          .ocf-field input:focus,.ocf-field select:focus,.ocf-field textarea:focus{border-color:#185FA5;box-shadow:0 0 0 3px rgba(24,95,165,.12);}
          .ocf-row{display:grid;grid-template-columns:1fr 1fr;gap:14px;}
          @media (max-width:640px){.ocf-row{grid-template-columns:1fr;}}
          .ocf-hint{font-size:12px;color:#9aa2ad;margin-top:5px;}
          .ocf-chip{display:inline-flex;align-items:center;gap:5px;font-size:11.5px;font-weight:600;color:#185FA5;background:#E6F1FB;border:1px solid #cfe3f6;border-radius:7px;padding:4px 9px;cursor:pointer;}
          .ocf-note{border-radius:10px;padding:11px 14px;font-size:13px;margin-bottom:16px;}
          .ocf-note--warn{background:#FEF6EC;border:1px solid #F5DCB3;color:#8a5a12;}
          .ocf-note--ok{background:#E1F5EE;border:1px solid #9ad9c4;color:#0F6E56;}
          .ocf-err{color:#B42318;font-size:12px;margin-top:5px;}
          .ocf-actions{display:flex;gap:10px;align-items:center;}
          .ocf-submit{border:none;border-radius:10px;background:#185FA5;color:#fff;font-size:14.5px;font-weight:700;padding:12px 22px;cursor:pointer;}
          .ocf-submit:hover{background:#0F4A84;}
          .ocf-cancel{color:#6b7280;text-decoration:none;font-size:14px;}
        </style>

        <div class="ocf-wrap">
          <h1 class="ocf-h1">{{ __('New Campaign') }}</h1>
          <p class="ocf-sub">{{ __('Recruitment campaigns automatically include the live invitation link, so recipients who apply get an account instantly.') }}</p>

          @if (session('error'))<div class="ocf-note ocf-note--warn">{{ session('error') }}</div>@endif

          @if ($inviteActive)
            <div class="ocf-note ocf-note--ok">✓ {{ __('An invitation link is active — recruitment campaigns will use it automatically.') }}</div>
          @else
            <div class="ocf-note ocf-note--warn">⚠ {{ __('No active invitation link. Generate one on Affiliates → Applications before sending a recruitment campaign, or recipients won\'t be auto-provisioned.') }}</div>
          @endif

          <form method="POST" action="{{ route('admin.outreach.store') }}" data-cs-confirm="{{ __('Create this campaign? You\'ll review recipients before anything is sent.') }}">
            @csrf
            <div class="ocf-card">
              <div class="ocf-row">
                <div class="ocf-field">
                  <label>{{ __('Campaign name') }}</label>
                  <input type="text" name="name" value="{{ old('name', 'BrighterMonday batch') }}" maxlength="160" required>
                  @error('name')<div class="ocf-err">{{ $message }}</div>@enderror
                </div>
                <div class="ocf-field">
                  <label>{{ __('Type') }}</label>
                  <select name="type">
                    <option value="recruitment" {{ old('type') === 'general' ? '' : 'selected' }}>{{ __('Recruitment (auto-includes invite link)') }}</option>
                    <option value="general" {{ old('type') === 'general' ? 'selected' : '' }}>{{ __('General (no invite link / follow-ups)') }}</option>
                  </select>
                </div>
              </div>
              <div class="ocf-field">
                <label>{{ __('Subject') }}</label>
                <input type="text" name="subject" value="{{ old('subject', $defaultSubject) }}" maxlength="200" required>
                @error('subject')<div class="ocf-err">{{ $message }}</div>@enderror
              </div>
              <div class="ocf-field">
                <label>{{ __('Message') }}
                  <span class="ocf-chip" onclick="ocInsert('{{ '{{first_name}}' }}')">+ {{ __('first name') }}</span>
                  <span class="ocf-chip" onclick="ocInsert('{{ '{{invite_link}}' }}')">+ {{ __('invite link') }}</span>
                </label>
                <textarea name="body" id="ocBody" rows="16" required>{{ old('body', $defaultBody) }}</textarea>
                <div class="ocf-hint">{{ __('Use') }} <code>{{ '{{first_name}}' }}</code> {{ __('and') }} <code>{{ '{{invite_link}}' }}</code>{{ __('. Recruitment emails also get a "Set up my account" button with the live link automatically — you don\'t have to paste a URL.') }}</div>
                @error('body')<div class="ocf-err">{{ $message }}</div>@enderror
              </div>
            </div>

            <div class="ocf-card">
              <div class="ocf-field">
                <label>{{ __('Recipients') }}</label>
                <textarea name="recipients" rows="7" placeholder="jane@example.com, Jane&#10;amos@example.com, Amos&#10;grace@example.com" required>{{ old('recipients') }}</textarea>
                <div class="ocf-hint">{{ __('One per line:') }} <code>email</code> {{ __('or') }} <code>email, First Name</code>{{ __('. Invalid lines and duplicates are skipped automatically.') }}</div>
              </div>
              <div class="ocf-row">
                <div class="ocf-field">
                  <label>{{ __('Follow up after (days)') }}</label>
                  <input type="number" name="follow_up_days" value="{{ old('follow_up_days', 4) }}" min="1" max="60">
                </div>
                <div class="ocf-field">
                  <label>{{ __('Max follow-ups') }}</label>
                  <input type="number" name="max_follow_ups" value="{{ old('max_follow_ups', 1) }}" min="0" max="3">
                  <div class="ocf-hint">{{ __('0 = none. Kept low on purpose — the system never spams.') }}</div>
                </div>
              </div>
            </div>

            <div class="ocf-actions">
              <button type="submit" class="ocf-submit">{{ __('Create campaign') }}</button>
              <a href="{{ route('admin.outreach.index') }}" class="ocf-cancel">{{ __('Cancel') }}</a>
            </div>
          </form>
        </div>

      </div>
    </div>
  </div>
</div>

<script>
  function ocInsert(token) {
    var ta = document.getElementById('ocBody');
    if (!ta) return;
    var s = ta.selectionStart || 0, e = ta.selectionEnd || 0;
    ta.value = ta.value.slice(0, s) + token + ta.value.slice(e);
    ta.focus();
    ta.selectionStart = ta.selectionEnd = s + token.length;
  }
</script>
@endsection
