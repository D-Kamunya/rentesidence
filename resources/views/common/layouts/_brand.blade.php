{{-- Shared sidebar/topbar brand mark for the (light) app surfaces: the CS icon +
     the two-tone wordmark. The glossy full logo (app_logo) is reserved for dark
     surfaces like the login; here it would be low-contrast, so we use the icon +
     dark/blue wordmark that reads cleanly on white. Pass $brandHref for the link. --}}
@php
    $appIcon  = getSettingImage('app_fav_icon');
    $hasIcon  = $appIcon && ! \Illuminate\Support\Str::contains($appIcon, 'empty-user');
    $appName  = getOption('app_name', 'Centresidence');
    $brandHref = $brandHref ?? url('/');
    $monogram = \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr(trim($appName), 0, 1));
@endphp
<a href="{{ $brandHref }}" class="logo logo-light cs-brand">
    <span class="logo-sm">
        @if ($hasIcon)
            <img src="{{ $appIcon }}" alt="{{ $appName }}" class="cs-brand__icon">
        @else
            <span class="cs-brand__mono">{{ $monogram }}</span>
        @endif
    </span>
    <span class="logo-lg cs-brand__lockup">
        @if ($hasIcon)
            <img src="{{ $appIcon }}" alt="" class="cs-brand__icon cs-brand__icon--lg">
        @endif
        @if ($appName === 'Centresidence')
            <span class="cs-brand__word"><span class="cs-brand__word--a">centre</span><span class="cs-brand__word--b">sidence</span></span>
        @else
            <span class="cs-brand__word cs-brand__word--a">{{ $appName }}</span>
        @endif
    </span>
</a>
