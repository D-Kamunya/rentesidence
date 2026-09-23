{{-- Shared support thread. Expects $ticket (with replies.author) and $viewerIsAdmin (bool). --}}
<div class="sup-thread" id="supThread">
    @foreach ($ticket->replies as $r)
        @php
            $mine = ((bool) $r->is_admin) === (bool) $viewerIsAdmin;
            $name = $r->is_admin
                ? __('Support')
                : (optional($r->author)->first_name . ' ' . optional($r->author)->last_name);
            $name = trim($name) ?: __('User');
            $initials = strtoupper(mb_substr(trim($name), 0, 1));
        @endphp
        <div class="sup-msg {{ $mine ? 'sup-msg--mine' : '' }}">
            <div class="sup-msg__av {{ $r->is_admin ? 'sup-msg__av--admin' : 'sup-msg__av--user' }}">{{ $r->is_admin ? 'CS' : $initials }}</div>
            <div>
                <div class="sup-msg__bubble">
                    <div class="sup-msg__who">{{ $mine ? __('You') : $name }}</div>
                    <div class="sup-msg__text">{{ $r->body }}</div>
                </div>
                <div class="sup-msg__time">{{ $r->created_at->format('M j, Y · H:i') }}</div>
            </div>
        </div>
    @endforeach
</div>
<script>
    // Keep the newest message in view.
    (function () { var t = document.getElementById('supThread'); if (t) t.scrollTop = t.scrollHeight; })();
</script>
