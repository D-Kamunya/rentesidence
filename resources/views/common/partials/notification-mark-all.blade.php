{{--
    "Mark all as read" for the notification bell dropdown — shared across every account's header.
    Drop this inside the dropdown header's <div class="col-auto">. It renders the link only when
    there are unread notifications, and wires ONE delegated click handler (via @once) that POSTs to
    the shared notification.readAll route, then clears the badge + empties the list in place.
--}}
@if (count(getNotificationLimit(auth()->id())) > 0)
    <a href="javascript:void(0)" class="js-notif-read-all theme-link"
       data-url="{{ route('notification.readAll') }}"
       data-empty="{{ __('No new notifications') }}"
       data-done="{{ __('All notifications marked as read') }}"
       style="text-decoration:none;font-size:12px;color:#185FA5;white-space:nowrap;">{{ __('Mark all as read') }}</a>
@endif

@once
    <script>
        (function () {
            if (window.__notifReadAllWired) return;
            window.__notifReadAllWired = true;
            document.addEventListener('click', function (e) {
                var btn = e.target.closest('.js-notif-read-all');
                if (!btn) return;
                e.preventDefault();
                e.stopPropagation();
                var csrf = document.querySelector('meta[name="csrf-token"]');
                fetch(btn.dataset.url, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf ? csrf.content : '', 'Accept': 'application/json' }
                }).then(function (r) { return r.json(); }).then(function (d) {
                    if (!d || !d.success) return;
                    // Remove the unread badge (admin-theme .noti-dot AND finance-partner .fp-badge).
                    document.querySelectorAll('#page-header-notifications-dropdown .noti-dot, .fp-badge')
                        .forEach(function (el) { el.remove(); });
                    // Empty the list in place (admin-theme .dropdown-menu/[data-simplebar] or fp .fp-dd__menu/.fp-dd__scroll).
                    var dropdown = btn.closest('.dropdown-menu') || btn.closest('.fp-dd__menu');
                    if (dropdown) {
                        var list = dropdown.querySelector('[data-simplebar], .fp-dd__scroll');
                        if (list) list.innerHTML =
                            '<div class="text-center text-muted p-3" style="font-size:13px;">' + btn.dataset.empty + '</div>';
                    }
                    btn.style.display = 'none';
                    if (typeof toastr !== 'undefined') toastr.success(btn.dataset.done);
                }).catch(function () {});
            });
        })();
    </script>
@endonce
