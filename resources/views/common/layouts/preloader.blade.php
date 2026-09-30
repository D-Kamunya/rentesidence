{{-- Shared loading splash for every role's layout. Shows the admin-uploaded preloader image when
     one is set; otherwise falls back to the bundled Centresidence logo — so a fresh cPanel pull (where
     the uploaded FileManager record/file may be absent) still shows the brand during load, never the
     placeholder avatar. Gated on app_preloader_status (defaults ON so it shows out of the box). --}}
@if (getOption('app_preloader_status', 1) == 1)
    <div id="preloader">
        <div id="preloaderInner">
            <img src="{{ getSettingImage('app_preloader', asset('assets/images/cs-icon.png')) }}"
                 alt="{{ getOption('app_name') ?: 'Centresidence' }}"
                 style="max-width:150px;width:38%;height:auto;">
            <img id="ajaxLoader" src="{{ asset('assets/images/ajaxloader.svg') }}" alt="loading">
        </div>
    </div>
@endif
