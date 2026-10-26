{{-- Admin-initiated contact: a "Message" button + modal to start a support thread with one user.
     Pass $messageUser (the target User). The target gets a support bell + sidebar badge + an email.
     cs-confirm lives on the FORM (it skips submit buttons). One target per page → fixed modal id. --}}
@php $mu = $messageUser ?? null; @endphp
@if ($mu)
  <button type="button" data-bs-toggle="modal" data-bs-target="#userMessageModal"
    style="display:inline-flex;align-items:center;gap:7px;background:#fff;border:1px solid #d7dbe0;color:#185FA5;border-radius:8px;font-size:14px;font-weight:600;padding:10px 18px;cursor:pointer;line-height:1.4;">
    <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><rect x="3" y="5" width="18" height="14" rx="2" stroke="currentColor" stroke-width="1.6"/><path d="M4 7l8 6 8-6" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
    {{ __('Message') }} {{ $mu->first_name }}
  </button>

  <div class="modal fade cs-modal" id="userMessageModal" tabindex="-1" aria-labelledby="userMessageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="userMessageModalLabel">{{ __('Message') }} {{ trim(($mu->first_name ?? '') . ' ' . ($mu->last_name ?? '')) ?: $mu->email }}</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
        </div>
        <form method="POST" action="{{ route('admin.user.message') }}"
          data-cs-confirm="{{ __('Send this message? They\'ll get it in their support inbox and by email.') }}">
          @csrf
          <input type="hidden" name="user_id" value="{{ $mu->id }}">
          <div class="modal-body">
            <p style="font-size:12.5px;color:#9aa2ad;margin:0 0 16px;">{{ __('They\'ll see it as a support message (in-app bell + sidebar badge) and get an email prompting them to log in and reply.') }}</p>
            <div class="mb-3">
              <label class="label-text-title color-heading font-medium mb-2">{{ __('Subject') }}</label>
              <input type="text" name="subject" class="form-control" maxlength="160" required placeholder="{{ __('e.g. About your academy retake') }}">
            </div>
            <div class="mb-2">
              <label class="label-text-title color-heading font-medium mb-2">{{ __('Message') }}</label>
              <textarea name="body" class="form-control" rows="5" maxlength="5000" required placeholder="{{ __('Type your message…') }}"></textarea>
            </div>
          </div>
          <div class="modal-footer justify-content-start">
            <button type="submit" class="theme-btn">{{ __('Send message') }}</button>
            <a href="javascript:void(0)" class="theme-btn-back ms-2" data-bs-dismiss="modal">{{ __('Cancel') }}</a>
          </div>
        </form>
      </div>
    </div>
  </div>
@endif
