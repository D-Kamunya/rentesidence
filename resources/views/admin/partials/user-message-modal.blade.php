{{-- Admin-initiated contact: ONE modal + a delegated handler. Any `.js-message-user` trigger (with
     data-user-id + data-user-name) opens it pre-filled for that user. Include once per page; add as
     many triggers as you like (a per-row button in a list, or a single button on a detail page).
     The target gets a support bell + sidebar badge + an email. cs-confirm lives on the FORM. --}}
<div class="modal fade cs-modal" id="userMessageModal" tabindex="-1" aria-labelledby="userMessageModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="umTitle">{{ __('Send a message') }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
      </div>
      <form method="POST" action="{{ route('admin.user.message') }}"
        data-cs-confirm="{{ __('Send this message? They\'ll get it in their support inbox and by email.') }}">
        @csrf
        <input type="hidden" name="user_id" id="umUserId" value="">
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
<script>
  (function () {
    document.addEventListener('click', function (e) {
      var btn = e.target.closest('.js-message-user');
      if (!btn) return;
      e.preventDefault();
      var uidEl = document.getElementById('umUserId');
      if (uidEl) uidEl.value = btn.getAttribute('data-user-id') || '';
      var name = btn.getAttribute('data-user-name') || '';
      var t = document.getElementById('umTitle');
      if (t) t.textContent = name ? ('{{ __('Message') }} ' + name) : '{{ __('Send a message') }}';
      var el = document.getElementById('userMessageModal');
      if (window.bootstrap && el) { new window.bootstrap.Modal(el).show(); }
    });
  })();
</script>
