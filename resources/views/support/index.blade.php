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
<div class="main-content">
<div class="page-content">
  <div class="container-fluid">
    <div class="page-content-wrapper p-30 radius-20" style="background:#f6f7f9;">

      <div class="sup-head">
        <div>
          <h1>{{ __('Support') }}</h1>
          <p>{{ __('Need a hand? Send us a message and we\'ll reply right here.') }}</p>
        </div>
        <button type="button" class="sup-btn sup-btn--primary" onclick="document.getElementById('supNewModal').classList.add('is-open')">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
          {{ __('New request') }}
        </button>
      </div>

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

    </div>
  </div>
</div>
</div>

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
