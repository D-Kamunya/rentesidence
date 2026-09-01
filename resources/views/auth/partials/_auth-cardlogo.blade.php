{{-- Mobile-only logo shown at the top of the form card (the brand panel hides on small screens). --}}
@php
    $appLogo = getSettingImage('app_logo');
    $hasLogo = $appLogo && !\Illuminate\Support\Str::contains($appLogo, 'empty-user');
@endphp
<div class="cs-auth__cardlogo">
    @if ($hasLogo)
        <img src="{{ $appLogo }}" alt="{{ getOption('app_name') }}" onerror="this.style.display='none';this.nextElementSibling.style.display='inline';">
        <span class="cs-auth__wordmark cs-auth__wordmark--dark" style="display:none;">{{ getOption('app_name') }}</span>
    @else
        <span class="cs-auth__wordmark cs-auth__wordmark--dark">{{ getOption('app_name') }}</span>
    @endif
</div>
