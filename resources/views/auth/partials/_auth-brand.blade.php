{{-- Shared auth brand panel (left side): slideshow + wordmark + headline/feats.
     Self-contained — computes its own slideshow + logo. Reused by login + password pages. --}}
@php
    $authTitle = getOption('sign_in_text_title');
    $authSub   = getOption('sign_in_text_subtitle');
    $authSlides = collect(['sign_in_image', 'sign_in_image_2', 'sign_in_image_3', 'sign_in_image_4'])
        ->filter(fn ($k) => !empty(getOption($k)))
        ->map(fn ($k) => getSettingImage($k))
        ->values();
    $appLogo = getSettingImage('app_logo');
    $hasLogo = $appLogo && !\Illuminate\Support\Str::contains($appLogo, 'empty-user');
@endphp
<aside class="cs-auth__brand">
    @if ($authSlides->isNotEmpty())
        <div class="cs-auth__slides" aria-hidden="true">
            @foreach ($authSlides as $img)
                <div class="cs-auth__slide" style="background-image:url('{{ $img }}')"></div>
            @endforeach
        </div>
        <div class="cs-auth__veil" aria-hidden="true"></div>
    @endif

    <div class="cs-auth__brandtop">
        <span class="cs-auth__logo">
            @if ($hasLogo)
                <img src="{{ $appLogo }}" alt="{{ getOption('app_name') }}" onerror="this.style.display='none';this.nextElementSibling.style.display='inline';">
                <span class="cs-auth__wordmark" style="display:none;">{{ getOption('app_name') }}</span>
            @else
                <span class="cs-auth__wordmark">{{ getOption('app_name') }}</span>
            @endif
        </span>
    </div>

    <div class="cs-auth__brandmid">
        <span class="cs-auth__eyebrow">{{ __('Infrastructure & Finance OS') }}</span>
        <h1 class="cs-auth__headline">{{ $authTitle ? __($authTitle) : __('Run properties. Collect rent. Finance the essentials.') }}</h1>
        <p class="cs-auth__sub">{{ $authSub ? __($authSub) : __('One secure platform for owners, tenants and partners — payments, agreements and infrastructure, end to end.') }}</p>

        <ul class="cs-auth__feats">
            <li><span class="cs-auth__featic">{!! '<svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M3 9.5L12 4l9 5.5M5 11v8h14v-8" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>' !!}</span>{{ __('Properties & tenants, managed end-to-end') }}</li>
            <li><span class="cs-auth__featic">{!! '<svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M4 7h16v10H4zM4 10h16M8 14h4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>' !!}</span>{{ __('Payments & M-Pesa, built in') }}</li>
            <li><span class="cs-auth__featic">{!! '<svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M12 3v18M5 8l7-5 7 5M5 8v8l7 5 7-5V8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>' !!}</span>{{ __('Infrastructure financing, repaid at source') }}</li>
        </ul>
    </div>

    <div class="cs-auth__brandfoot">
        <span class="cs-auth__dot"></span> {{ __('Secure • Encrypted • Trusted') }}
    </div>
</aside>
