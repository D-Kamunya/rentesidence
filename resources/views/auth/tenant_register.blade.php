@extends('layouts.app')
@push('title')
    {{ __('Create your free account') }} -
@endpush
@section('content')
<div class="cs-auth">
    @include('auth.partials._auth-bg')

    <div class="cs-auth__inner">
        @include('auth.partials._auth-brand', [
            'brandEyebrow'  => __('Your rental life, in your hands'),
            'brandHeadline' => __('Renting? This is your account.'),
            'brandSub'      => __('Free to join — with or without your landlord on Centresidence. Invite your landlord, find your next home, and start building a rental record that\'s yours to keep.'),
            'brandFeats'    => [
                ['<svg width="16" height="16" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="8.5" stroke="currentColor" stroke-width="1.6"/><path d="M12 7.5v9M14.3 9.3c-.5-.8-1.4-1.2-2.3-1.2-1.3 0-2.2.7-2.2 1.8 0 2.6 4.7 1.4 4.7 4 0 1.1-1 1.9-2.4 1.9-1 0-2-.5-2.5-1.3" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/></svg>', __('Invite your landlord and earn a reward when they join')],
                ['<svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M3 10l9-7 9 7v10a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1z" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>', __('Find your next home — live vacant listings')],
                ['<svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M4 7h16v10H4zM4 10h16M8 14h4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>', __('Rent payments & receipts on your phone — coming soon')],
            ],
        ])

        {{-- Form panel --}}
        <main class="cs-auth__panel">
            <div class="cs-auth__card cs-auth__card--wide">
                @include('auth.partials._auth-cardlogo')

                <h2 class="cs-auth__title">{{ __('Create your free account') }}</h2>
                <p class="cs-auth__hint">{{ __('Your rental life, in your hands — with or without your landlord on Centresidence.') }}</p>

                @if (session('error'))
                    <div class="cs-auth__alert">{{ session('error') }}</div>
                @endif

                <form action="{{ route('tenant.join.store') }}" method="post" class="cs-auth__form" autocomplete="off">
                    @csrf

                    {{-- Situation router — routes the tenant straight to the value that fits. --}}
                    <div class="cs-fld">
                        <label class="cs-fld__label">{{ __('What brings you to Centresidence?') }}</label>
                        <div class="tj-router">
                            @php
                                $sit = old('situation', $situation ?? '');
                                $opts = [
                                    'landlord_off' => [__('My landlord isn\'t on Centresidence'), __('Invite them and bring your rental online')],
                                    'moving'       => [__('I\'m looking for a home'),            __('Browse available places to rent')],
                                ];
                            @endphp
                            @foreach ($opts as $val => [$title, $desc])
                                <label class="tj-opt {{ $sit === $val ? 'is-on' : '' }}">
                                    <input type="radio" name="situation" value="{{ $val }}" {{ $sit === $val ? 'checked' : '' }} required>
                                    <span class="tj-opt__dot"></span>
                                    <span class="tj-opt__txt">
                                        <span class="tj-opt__title">{{ $title }}</span>
                                        <span class="tj-opt__desc">{{ $desc }}</span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                        @error('situation')<span class="cs-fld__err">{{ $message }}</span>@enderror
                        {{-- Owner-driven onboarding: a tenant whose landlord is already on Centresidence is
                             added by that landlord directly (and their email/phone is already on file), so
                             this is a sign-in, not a signup. Signpost it rather than build a lookup/claim. --}}
                        <p class="tj-hint">{{ __('Already renting from a landlord on Centresidence? They add you directly —') }}
                            <a href="{{ route('login') }}" class="cs-auth__link">{{ __('sign in') }}</a> {{ __('or') }}
                            <a href="{{ route('password.request') }}" class="cs-auth__link">{{ __('reset your password') }}</a>.</p>
                    </div>

                    <div class="tj-row2">
                        <div class="cs-fld">
                            <label class="cs-fld__label">{{ __('First name') }}</label>
                            <input type="text" name="first_name" value="{{ old('first_name') }}" class="cs-fld__input @error('first_name') is-bad @enderror" placeholder="{{ __('Jane') }}">
                            @error('first_name')<span class="cs-fld__err">{{ $message }}</span>@enderror
                        </div>
                        <div class="cs-fld">
                            <label class="cs-fld__label">{{ __('Last name') }}</label>
                            <input type="text" name="last_name" value="{{ old('last_name') }}" class="cs-fld__input @error('last_name') is-bad @enderror" placeholder="{{ __('Doe') }}">
                            @error('last_name')<span class="cs-fld__err">{{ $message }}</span>@enderror
                        </div>
                    </div>

                    <div class="cs-fld">
                        <label class="cs-fld__label">{{ __('Phone') }}</label>
                        <div class="cs-fld__wrap">
                            <span class="cs-fld__ic">{!! '<svg width="17" height="17" viewBox="0 0 24 24" fill="none"><path d="M6 3h4l2 5-3 2a11 11 0 005 5l2-3 5 2v4a2 2 0 01-2 2A16 16 0 014 6a2 2 0 012-3z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/></svg>' !!}</span>
                            <input type="text" name="contact_number" value="{{ old('contact_number') }}" class="cs-fld__input @error('contact_number') is-bad @enderror" placeholder="{{ __('0700 000 000') }}">
                        </div>
                        @error('contact_number')<span class="cs-fld__err">{{ $message }}</span>@enderror
                    </div>

                    <div class="cs-fld">
                        <label class="cs-fld__label">{{ __('Email') }}</label>
                        <div class="cs-fld__wrap">
                            <span class="cs-fld__ic">{!! '<svg width="17" height="17" viewBox="0 0 24 24" fill="none"><path d="M4 6h16v12H4zM4 7l8 6 8-6" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>' !!}</span>
                            <input type="email" name="email" value="{{ old('email') }}" class="cs-fld__input @error('email') is-bad @enderror" placeholder="{{ __('you@email.com') }}">
                        </div>
                        @error('email')<span class="cs-fld__err">{{ $message }}</span>@enderror
                    </div>

                    <div class="tj-row2">
                        <div class="cs-fld">
                            <label class="cs-fld__label">{{ __('Password') }}</label>
                            <div class="cs-fld__wrap">
                                <span class="cs-fld__ic">{!! '<svg width="17" height="17" viewBox="0 0 24 24" fill="none"><rect x="5" y="10" width="14" height="10" rx="2" stroke="currentColor" stroke-width="1.6"/><path d="M8 10V7a4 4 0 018 0v3" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>' !!}</span>
                                <input type="password" name="password" class="cs-fld__input @error('password') is-bad @enderror" placeholder="{{ __('At least 8 characters') }}">
                                <button type="button" class="cs-fld__eye" id="csPwToggle" aria-label="{{ __('Show password') }}">
                                    {!! '<svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7-10-7-10-7z" stroke="currentColor" stroke-width="1.6"/><circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.6"/></svg>' !!}
                                </button>
                            </div>
                            @error('password')<span class="cs-fld__err">{{ $message }}</span>@enderror
                        </div>
                        <div class="cs-fld">
                            <label class="cs-fld__label">{{ __('Confirm password') }}</label>
                            <div class="cs-fld__wrap">
                                <span class="cs-fld__ic">{!! '<svg width="17" height="17" viewBox="0 0 24 24" fill="none"><rect x="5" y="10" width="14" height="10" rx="2" stroke="currentColor" stroke-width="1.6"/><path d="M8 10V7a4 4 0 018 0v3" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>' !!}</span>
                                <input type="password" name="password_confirmation" class="cs-fld__input" placeholder="{{ __('Repeat password') }}">
                            </div>
                        </div>
                    </div>

                    <label class="cs-check cs-check--terms">
                        <input type="checkbox" name="agree" value="1" {{ old('agree') ? 'checked' : '' }}>
                        <span>{{ __('I agree to the') }} <a href="{{ route('terms-conditions') }}" target="_blank" class="cs-auth__link">{{ __('Terms') }}</a> {{ __('and') }} <a href="{{ route('privacy-policy') }}" target="_blank" class="cs-auth__link">{{ __('Privacy Policy') }}</a>.</span>
                    </label>
                    @error('agree')<span class="cs-fld__err" style="display:block;margin-top:-6px;margin-bottom:8px;">{{ $message }}</span>@enderror

                    <button type="submit" class="cs-auth__submit">
                        <span>{{ __('Create free account') }}</span>
                        {!! '<svg width="17" height="17" viewBox="0 0 24 24" fill="none"><path d="M5 12h14M13 6l6 6-6 6" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"/></svg>' !!}
                    </button>
                </form>

                <p class="cs-auth__foot">{{ __('Already have an account?') }} <a href="{{ route('login') }}" class="cs-auth__link">{{ __('Sign in') }}</a></p>
            </div>
        </main>
    </div>
</div>

@include('auth.partials._auth-styles')
<style>
    .cs-auth__card--wide { max-width:520px; }
    /* The register form is taller than login — with space-between the brand blocks drift apart and
       the middle floats. Center the headline group as the anchor, and pin the logo/trust badge. */
    .cs-auth__brand { justify-content:center; }
    .cs-auth__brandtop { position:absolute; top:54px; left:60px; right:60px; }
    .cs-auth__brandfoot { position:absolute; bottom:54px; left:60px; right:60px; }
    .cs-auth__brandmid { max-width:30rem; }
    .tj-row2 { display:grid; grid-template-columns:1fr 1fr; gap:12px; }
    @media (max-width:480px){ .tj-row2 { grid-template-columns:1fr; } }
    .cs-auth__alert { background:rgba(224,49,49,.12); border:1px solid rgba(224,49,49,.3); color:#ffb4b4;
        border-radius:10px; padding:10px 13px; font-size:13px; margin-bottom:14px; }
    .cs-auth__foot { margin-top:18px; text-align:center; font-size:13.5px; color:var(--muted); }
    .cs-check--terms { align-items:flex-start; gap:9px; margin:2px 0 16px; font-size:13px; color:var(--muted); line-height:1.5; }
    /* Situation router cards */
    .tj-router { display:flex; flex-direction:column; gap:9px; }
    .tj-opt { display:flex; align-items:flex-start; gap:11px; cursor:pointer;
        background:var(--card); border:1px solid var(--line); border-radius:12px; padding:12px 14px;
        transition:border-color .15s ease, background .15s ease; }
    .tj-opt:hover { border-color:rgba(59,130,246,.5); }
    .tj-opt.is-on, .tj-opt:has(input:checked) { border-color:var(--blue); background:rgba(59,130,246,.1); }
    .tj-opt input { position:absolute; opacity:0; pointer-events:none; }
    .tj-opt__dot { flex:none; width:17px; height:17px; margin-top:1px; border-radius:50%;
        border:2px solid var(--faint); position:relative; transition:border-color .15s ease; }
    .tj-opt:has(input:checked) .tj-opt__dot { border-color:var(--blue); }
    .tj-opt:has(input:checked) .tj-opt__dot::after { content:''; position:absolute; inset:3px; border-radius:50%; background:var(--blue); }
    .tj-opt__txt { display:flex; flex-direction:column; gap:2px; }
    .tj-opt__title { font-size:14px; font-weight:600; color:var(--ink); }
    .tj-opt__desc { font-size:12px; color:var(--muted); }
    .tj-hint { margin:9px 2px 0; font-size:12px; color:var(--faint); line-height:1.5; }
</style>
@endsection
@push('script')
@include('auth.partials._auth-scripts')
@endpush
