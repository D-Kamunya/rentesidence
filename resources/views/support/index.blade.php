@extends($layout)

@section('content')
@php
    $pillClass = [
        'open' => 'sup-pill--open', 'answered' => 'sup-pill--answered',
        'resolved' => 'sup-pill--resolved', 'closed' => 'sup-pill--closed',
    ];
    $pillLabel = [
        'open' => __('Awaiting reply'), 'answered' => __('Replied'),
        'resolved' => __('Resolved'), 'closed' => __('Closed'),
    ];
@endphp
@php $isTenant = (int) auth()->user()->role === USER_ROLE_TENANT; @endphp
@include('support._chrome-open')

      <div class="sup-head">
        <div>
          <h1>{{ __('Centresidence Support') }}</h1>
          <p>{{ __('Need a hand? Send us a message and we\'ll reply right here.') }}</p>
        </div>
        <button type="button" class="sup-btn sup-btn--primary" onclick="document.getElementById('supNewModal').classList.add('is-open')">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
          {{ __('New request') }}
        </button>
      </div>

      @if ($isTenant)
        <div class="sup-brandnote">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M12 2l7 3v6c0 4.5-3 8-7 9-4-1-7-4.5-7-9V5l7-3z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><path d="M9 12l2 2 4-4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
          <span>{{ __('This is Centresidence support — the platform team, not your landlord. We help with your account, payments and using the app.') }}</span>
        </div>
      @endif

      @if (session('success'))<div class="sup-flash sup-flash--ok">{{ session('success') }}</div>@endif
      @if (session('error'))<div class="sup-flash sup-flash--err">{{ session('error') }}</div>@endif

      @if ($tickets->isEmpty())
        <div class="sup-empty">
          {{ __('No support requests yet.') }}<br>
          <span style="font-size:13px;">{{ __('Tap "New request" to start a conversation with our team.') }}</span>
        </div>
      @else
        <div class="sup-list">
          @foreach ($tickets as $t)
            <a href="{{ route('support.show', $t->id) }}" class="sup-item">
              <div class="sup-item__body">
                <div class="sup-item__subject">
                  @if ($t->requester_unread && $t->status !== 'closed')<span class="sup-unread-dot" title="{{ __('New reply') }}"></span>@endif
                  {{ $t->subject }}
                </div>
                <div class="sup-item__meta">
                  {{ $t->replies_count }} {{ Str::plural(__('message'), $t->replies_count) }}
                  · {{ __('updated') }} {{ optional($t->last_reply_at)->diffForHumans() ?? $t->created_at->diffForHumans() }}
                </div>
              </div>
              <span class="sup-pill {{ $pillClass[$t->status] ?? 'sup-pill--closed' }}">{{ $pillLabel[$t->status] ?? ucfirst($t->status) }}</span>
              <span class="sup-item__go"><svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M9 6l6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
            </a>
          @endforeach
        </div>
        @if ($tickets->hasPages())<div class="mt-4">{{ $tickets->links() }}</div>@endif
      @endif

@include('support._chrome-close')

{{-- New request modal --}}
<div class="sup-modal" id="supNewModal">
  <div class="sup-modal__card">
    <div class="sup-modal__head">
      <h3>{{ __('New support request') }}</h3>
      <button type="button" class="sup-modal__x" onclick="document.getElementById('supNewModal').classList.remove('is-open')">&times;</button>
    </div>
    <form method="POST" action="{{ route('support.store') }}">
      @csrf
      <div class="sup-field">
        <label>{{ __('Subject') }}</label>
        <input type="text" name="subject" value="{{ old('subject') }}" maxlength="160" required placeholder="{{ __('Briefly, what do you need help with?') }}">
        @error('subject')<div class="err">{{ $message }}</div>@enderror
      </div>
      <div class="sup-field">
        <label>{{ __('Message') }}</label>
        <textarea name="body" rows="5" maxlength="5000" required placeholder="{{ __('Tell us what\'s going on…') }}">{{ old('body') }}</textarea>
        @error('body')<div class="err">{{ $message }}</div>@enderror
      </div>
      <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:6px;">
        <button type="button" class="sup-btn sup-btn--ghost" onclick="document.getElementById('supNewModal').classList.remove('is-open')">{{ __('Cancel') }}</button>
        <button type="submit" class="sup-btn sup-btn--primary">{{ __('Send') }}</button>
      </div>
    </form>
  </div>
</div>

@include('support._styles')
@if ($errors->any() && old('subject'))
  <script>document.addEventListener('DOMContentLoaded',function(){document.getElementById('supNewModal').classList.add('is-open');});</script>
@endif
@endsection
