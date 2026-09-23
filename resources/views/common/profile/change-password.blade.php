@extends(getLayout() . '.layouts.app')

@section('content')
@php
    // Role-aware dashboard crumb — this view is shared by owner/tenant/maintainer/admin/affiliate
    // via getLayout(), so a hardcoded owner.dashboard link was wrong for everyone else.
    $dashboardRoute = \Route::has(getLayout() . '.dashboard') ? route(getLayout() . '.dashboard') : url('/');
@endphp
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="page-content-wrapper bg-white p-30 radius-20">
                <div class="container">

                    {{-- Page Header --}}
                    <div class="cp-header mb-4">
                        <div>
                            <h2 class="cp-title">{{ $pageTitle }}</h2>
                            <nav aria-label="breadcrumb">
                                <ol class="cp-breadcrumb">
                                    <li><a href="{{ $dashboardRoute }}">{{ __('Dashboard') }}</a></li>
                                    <li aria-current="page">
                                        <svg width="8" height="8" viewBox="0 0 16 16" fill="none">
                                            <path d="M6 4l4 4-4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                        {{ __('Change Password') }}
                                    </li>
                                </ol>
                            </nav>
                        </div>
                    </div>

                    {{-- Card --}}
                    <div class="cp-card">
                        <div class="cp-card__head">
                            <div class="cp-card__icon">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                                    <rect x="4" y="10" width="16" height="11" rx="2" stroke="currentColor" stroke-width="1.8"/>
                                    <path d="M8 10V7a4 4 0 018 0v3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                    <circle cx="12" cy="15.5" r="1.4" fill="currentColor"/>
                                </svg>
                            </div>
                            <div>
                                <p class="cp-card__title">{{ __('Update your password') }}</p>
                                <p class="cp-card__sub">{{ __('Choose a strong password you don\'t use anywhere else.') }}</p>
                            </div>
                        </div>

                        @if (auth()->user()->must_change_password)
                            <div style="display:flex;gap:9px;align-items:flex-start;background:#FEF9EC;border:0.5px solid #F5E4B8;border-radius:10px;padding:12px 14px;margin-bottom:18px;font-size:13px;color:#8A6D1B;line-height:1.5;">
                                <i class="ri-shield-keyhole-line" style="font-size:16px;margin-top:1px;"></i>
                                <span>{{ __('Welcome! You signed in with a temporary password. Set your own password below to continue — enter the temporary one as your current password.') }}</span>
                            </div>
                        @endif

                        <form action="{{ route('change-password.update') }}" method="post" class="cp-form">
                            @csrf

                            <div class="cp-field">
                                <label class="cp-label">{{ __('Current Password') }}</label>
                                <div class="cp-input-wrap">
                                    <input type="password" name="current_password" class="cp-input" placeholder="••••••••" required>
                                    <button type="button" class="cp-eye" data-toggle-password aria-label="{{ __('Show password') }}">
                                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none">
                                            <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z" stroke="currentColor" stroke-width="1.7"/>
                                            <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.7"/>
                                        </svg>
                                    </button>
                                </div>
                                @error('current_password')
                                    <span class="cp-error">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="cp-field">
                                <label class="cp-label">{{ __('New Password') }}</label>
                                <div class="cp-input-wrap">
                                    <input type="password" name="password" class="cp-input" placeholder="••••••••" required>
                                    <button type="button" class="cp-eye" data-toggle-password aria-label="{{ __('Show password') }}">
                                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none">
                                            <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z" stroke="currentColor" stroke-width="1.7"/>
                                            <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.7"/>
                                        </svg>
                                    </button>
                                </div>
                                @error('password')
                                    <span class="cp-error">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="cp-field">
                                <label class="cp-label">{{ __('Confirm Password') }}</label>
                                <div class="cp-input-wrap">
                                    <input type="password" name="password_confirmation" class="cp-input" placeholder="••••••••" required>
                                    <button type="button" class="cp-eye" data-toggle-password aria-label="{{ __('Show password') }}">
                                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none">
                                            <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z" stroke="currentColor" stroke-width="1.7"/>
                                            <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.7"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <div class="cp-actions">
                                <button type="submit" class="cp-btn">{{ __('Update Password') }}</button>
                            </div>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

{{-- Inline (not @push('script')) so the toggle works in every role layout — the
     affiliate layout has no script stack. --}}
<script>
    document.querySelectorAll('[data-toggle-password]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var input = btn.parentElement.querySelector('input');
            if (!input) return;
            var show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            btn.classList.toggle('is-on', show);
        });
    });
</script>
@endsection

@push('style')
<style>
    .cp-header { display:flex; align-items:flex-start; justify-content:space-between; flex-wrap:wrap; gap:1rem; }
    .cp-title  { font-size:22px; font-weight:500; color:#111827; margin:0 0 6px; }
    .cp-breadcrumb { list-style:none; display:flex; align-items:center; gap:6px; margin:0; padding:0; font-size:12px; color:#9ca3af; }
    .cp-breadcrumb a { color:#185FA5; text-decoration:none; font-weight:500; }
    .cp-breadcrumb a:hover { color:#0F4A84; }
    .cp-breadcrumb li { display:flex; align-items:center; gap:6px; }

    .cp-card { background:#fff; border:0.5px solid #e5e7eb; border-radius:14px; max-width:560px; overflow:hidden; }
    .cp-card__head { display:flex; align-items:center; gap:14px; padding:1.25rem 1.5rem; border-bottom:0.5px solid #f3f4f6; background:#fafafa; }
    .cp-card__icon { width:44px; height:44px; border-radius:12px; background:#E6F1FB; color:#185FA5; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
    .cp-card__title { font-size:15px; font-weight:600; color:#111827; margin:0 0 3px; }
    .cp-card__sub   { font-size:12.5px; color:#6b7280; margin:0; }

    .cp-form { padding:1.5rem; display:flex; flex-direction:column; gap:1.15rem; }
    .cp-field { display:flex; flex-direction:column; gap:6px; }
    .cp-label { font-size:12px; font-weight:500; color:#374151; }

    .cp-input-wrap { position:relative; display:flex; align-items:center; }
    .cp-input {
        width:100%; border:0.5px solid #e5e7eb; border-radius:9px;
        padding:10px 42px 10px 13px; font-size:14px; color:#111827; background:#fff;
        outline:none; transition:border-color .15s, box-shadow .15s;
    }
    .cp-input:focus { border-color:#185FA5; box-shadow:0 0 0 3px rgba(24,95,165,.1); }
    .cp-input::placeholder { color:#c4c4c4; letter-spacing:2px; }

    .cp-eye {
        position:absolute; right:6px; top:50%; transform:translateY(-50%);
        background:transparent; border:none; cursor:pointer; color:#9ca3af;
        width:30px; height:30px; display:flex; align-items:center; justify-content:center;
        border-radius:7px; transition:color .15s, background .15s;
    }
    .cp-eye:hover { color:#185FA5; background:#f3f4f6; }
    .cp-eye.is-on { color:#185FA5; }

    .cp-error { font-size:12px; color:#993C1D; }

    .cp-actions { margin-top:.25rem; }
    .cp-btn {
        display:inline-flex; align-items:center; justify-content:center;
        background:#185FA5; color:#fff; border:none; border-radius:9px;
        font-size:13.5px; font-weight:500; padding:10px 22px; cursor:pointer;
        box-shadow:0 2px 8px rgba(24,95,165,.2); transition:background .15s, transform .12s;
    }
    .cp-btn:hover { background:#0F4A84; color:#fff !important; transform:translateY(-1px); }

    @media (max-width:576px) {
        .cp-card { max-width:100%; }
        .cp-btn  { width:100%; }
    }
</style>
@endpush
