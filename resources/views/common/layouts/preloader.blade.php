{{-- Shared loading splash for every role's layout. Shows the admin-uploaded preloader image when
     one is set; otherwise falls back to the bundled Centresidence logo — so a fresh cPanel pull (where
     the uploaded FileManager record/file may be absent) still shows the brand during load, never the
     placeholder avatar. Gated on app_preloader_status (defaults ON so it shows out of the box). --}}
@if (getOption('app_preloader_status', 1) == 1)
    {{-- CRITICAL CSS, inline on purpose: the preloader is on screen BEFORE style.css finishes
         loading, so its centring can't live only in style.css — that's why on a slow/mobile (4G)
         connection it rendered unstyled, top-left, with the ajax loader still showing. These rules
         apply from the first paint regardless of when style.css arrives. --}}
    <style>
        #preloader {
            position: fixed; inset: 0; z-index: 9999999999;
            background: #fff;
            display: flex; align-items: center; justify-content: center;
        }
        #preloaderInner {
            display: flex; flex-direction: column;
            align-items: center; justify-content: center;
            text-align: center; gap: 14px;
            width: auto; height: auto;
        }
        #preloader #ajaxLoader { display: none; }
    </style>
    <div id="preloader">
        <div id="preloaderInner">
            <img src="{{ getSettingImage('app_preloader', asset('assets/images/cs-icon.png')) }}"
                 alt="{{ getOption('app_name') ?: 'Centresidence' }}"
                 style="max-width:150px;width:38%;height:auto;">
            <img id="ajaxLoader" src="{{ asset('assets/images/ajaxloader.svg') }}" alt="loading">
        </div>
    </div>
@endif
