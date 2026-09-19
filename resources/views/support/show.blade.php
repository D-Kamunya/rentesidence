@extends($layout)

@section('content')
@php
    $pillClass = ['open' => 'sup-pill--open', 'answered' => 'sup-pill--answered', 'resolved' => 'sup-pill--resolved', 'closed' => 'sup-pill--closed'];
    $pillLabel = ['open' => __('Awaiting reply'), 'answered' => __('Replied'), 'resolved' => __('Resolved'), 'closed' => __('Closed')];
@endphp
<div class="main-content">
<div class="page-content">
  <div class="container-fluid">
    <div class="page-content-wrapper p-30 radius-20" style="background:#f6f7f9;">

      <a href="{{ route('support.index') }}" class="sup-btn sup-btn--ghost" style="margin-bottom:16px;">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M15 6l-6 6 6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        {{ __('All requests') }}
      </a>

      <div class="sup-head">
        <div>
          <h1 style="font-size:19px;">{{ $ticket->subject }}</h1>
          <p>{{ __('Opened') }} {{ $ticket->created_at->format('M j, Y') }}</p>
        </div>
        <span class="sup-pill {{ $pillClass[$ticket->status] ?? 'sup-pill--closed' }}">{{ $pillLabel[$ticket->status] ?? ucfirst($ticket->status) }}</span>
      </div>

      @if (session('success'))<div class="sup-flash sup-flash--ok">{{ session('success') }}</div>@endif
      @if (session('error'))<div class="sup-flash sup-flash--err">{{ session('error') }}</div>@endif

      <div class="sup-card">
        @include('support._thread', ['viewerIsAdmin' => false])

        @if ($ticket->isClosed())
          <div class="sup-closed-note">{{ __('This request is closed. Open a new one if you still need help.') }}</div>
        @else
          <form method="POST" action="{{ route('support.reply', $ticket->id) }}" class="sup-reply">
            @csrf
            <textarea name="body" rows="3" maxlength="5000" required placeholder="{{ __('Type your reply…') }}">{{ old('body') }}</textarea>
            @error('body')<div class="err" style="color:#B42318;font-size:12px;margin-top:5px;">{{ $message }}</div>@enderror
            <div class="sup-reply__row">
              <button type="submit" class="sup-btn sup-btn--primary">{{ __('Send reply') }}</button>
            </div>
          </form>
        @endif
      </div>

    </div>
  </div>
</div>
</div>

@include('support._styles')
@endsection
