{{--
    Mobile-only brand header for the slide-in menu. On phones the menu opens flush to "Dashboard"
    with empty space above it (the brand lives in the desktop top bar, which is collapsed on mobile)
    — this fills that gap with the logo. Hidden on lg+ where the top bar already shows the brand.
    Shared by every account type's sidebar.
--}}
<div class="ow-menu-brand d-lg-none">
    {{-- The square CS icon (favicon), not the wide wordmark — it fits the narrow drawer without
         bleeding off the edge. --}}
    <img src="{{ getSettingImage('app_fav_icon', asset('assets/images/cs-icon.png')) }}"
         alt="{{ getOption('app_name') ?: 'Centresidence' }}">
</div>
