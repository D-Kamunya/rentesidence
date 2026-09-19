@extends('admin.layouts.app')

@section('content')
@php
    $pillClass = ['open' => 'sup-pill--open', 'answered' => 'sup-pill--answered', 'resolved' => 'sup-pill--resolved', 'closed' => 'sup-pill--closed'];
    $roleLabel = [
        USER_ROLE_OWNER => __('Owner'), USER_ROLE_TENANT => __('Tenant'), USER_ROLE_MAINTAINER => __('Maintainer'),
        USER_ROLE_AFFILIATE => __('Affiliate'), USER_ROLE_FINANCE_PARTNER => __('Finance partner'),
    ];
    $roleSlug = [
        USER_ROLE_OWNER => 'owner', USER_ROLE_TENANT => 'tenant', USER_ROLE_MAINTAINER => 'maintainer',
        USER_ROLE_AFFILIATE => 'affiliate', USER_ROLE_FINANCE_PARTNER => 'finance',
    ];
    $req = $ticket->requester;
    $reqSlug = $roleSlug[$ticket->requester_role] ?? 'maintainer';
@endphp
<div class="main-content">
  <div class="page-content">
    <div class="container-fluid">
      <div class="page-content-wrapper p-30 radius-20" style="background:#f6f7f9;">

        <a href="{{ route('admin.support.index') }}" class="sup-btn sup-btn--ghost" style="margin-bottom:16px;">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M15 6l-6 6 6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
          {{ __('Support inbox') }}
        </a>

        <div class="sup-head">
          <div>
            <h1 style="font-size:19px;">{{ $ticket->subject }}</h1>
            <p>
              <span class="sup-role sup-role--{{ $reqSlug }}">{{ $roleLabel[$ticket->requester_role] ?? __('User') }}</span>
              {{ trim(optional($req)->first_name . ' ' . optional($req)->last_name) ?: __('User') }}
              @if (optional($req)->email)<span style="color:#c3c6cb;">·</span> {{ $req->email }}@endif
            </p>
          </div>
          <span class="sup-pill {{ $pillClass[$ticket->status] ?? 'sup-pill--closed' }}">{{ ucfirst($ticket->status) }}</span>
        </div>

        @if (session('success'))<div class="sup-flash sup-flash--ok">{{ session('success') }}</div>@endif
        @if (session('error'))<div class="sup-flash sup-flash--err">{{ session('error') }}</div>@endif

        <div class="sup-card">
          @include('support._thread', ['viewerIsAdmin' => true])

          @if ($ticket->isClosed())
            <div class="sup-closed-note">
              {{ __('This ticket is closed.') }}
              <form method="POST" action="{{ route('admin.support.status', $ticket->id) }}" style="display:inline;">
                @csrf<input type="hidden" name="status" value="open">
                <button type="submit" class="sup-btn sup-btn--ghost" style="margin-left:8px;padding:6px 12px;">{{ __('Reopen') }}</button>
              </form>
            </div>
          @else
            <form method="POST" action="{{ route('admin.support.reply', $ticket->id) }}" class="sup-reply">
              @csrf
              <textarea name="body" rows="3" maxlength="5000" required placeholder="{{ __('Reply to the requester…') }}">{{ old('body') }}</textarea>
              @error('body')<div class="err" style="color:#B42318;font-size:12px;margin-top:5px;">{{ $message }}</div>@enderror
              <div class="sup-reply__row">
                <span style="margin-right:auto;font-size:12px;color:#9aa2ad;">{{ __('The requester is notified by in-app alert.') }}</span>
                <button type="submit" class="sup-btn sup-btn--primary">{{ __('Send reply') }}</button>
              </div>
            </form>
            <div class="sup-closed-note" style="border-top:1px solid #eef0f3;">
              <form method="POST" action="{{ route('admin.support.status', $ticket->id) }}" style="display:inline;">
                @csrf<input type="hidden" name="status" value="resolved">
                <button type="submit" class="sup-btn sup-btn--ghost" style="padding:6px 12px;">{{ __('Mark resolved') }}</button>
              </form>
              <form method="POST" action="{{ route('admin.support.status', $ticket->id) }}" style="display:inline;margin-left:6px;"
                    data-cs-confirm="{{ __('Close this ticket? The requester can no longer reply on it.') }}">
                @csrf<input type="hidden" name="status" value="closed">
                <button type="submit" class="sup-btn sup-btn--ghost" style="padding:6px 12px;">{{ __('Close ticket') }}</button>
              </form>
            </div>
          @endif
        </div>

      </div>
    </div>
  </div>
</div>
@include('support._styles')
@endsection
