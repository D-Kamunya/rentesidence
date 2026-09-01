<link href="{{ asset('assets/libs/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
<link href="{{ asset('assets/libs/jquery-ui/jquery-ui.min.css') }}" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('assets/css/animate.min.css') }}">

<!-- Google Font CSS -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600&display=swap" rel="stylesheet">

<link rel="stylesheet" href="{{ asset('assets/libs/owl-carousel/owl.carousel.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/libs/owl-carousel/owl.theme.default.min.css') }}">

<link rel="stylesheet" href="{{ asset('assets/libs/venobox/venobox.min.css') }}">
<link href="{{ asset('assets/css/icons.min.css') }}" rel="stylesheet">

<!-- Dropzone css -->
<link href="{{ asset('assets/libs/dropzone/dropzone.css') }}" rel="stylesheet">
<style>
    :root {
        {{-- Brand blue = CS blue (#185FA5 / hover #0F4A84). Admin color-mode can still override
             via the options; the fallbacks below default to CS blue so the whole app's primary
             buttons + link hovers conform (replaced the legacy #3686FC). --}}
        @if (getOption('website_color_mode', 0) == ACTIVE)
            --primary-color: {{ getOption('website_primary_color', '#185FA5') }};
            --secondary-color: {{ getOption('website_secondary_color', '#8253FB') }};
            --button-primary-color: {{ getOption('button_primary_color', '#185FA5') }};
            --button-hover-color: {{ getOption('button_hover_color', '#0F4A84') }};
        @else
            --primary-color: #185FA5;
            --secondary-color: #8253FB;
            --button-primary-color: #185FA5;
            --button-hover-color: #0F4A84;
        @endif
    }
</style>
<link href="{{ asset('assets/css/style.css') }}" rel="stylesheet">
<link href="{{ asset('assets/css/extra-style.css') }}" rel="stylesheet">

<!-- RTL Style Start -->
@if (selectedLanguage()->rtl == 1)
    <link href="{{ asset('assets/css/rtl-style.css') }}" rel="stylesheet">
@endif
<!-- RTL Style End -->

<link rel="stylesheet" href="{{ asset('assets/css/responsive.css') }}">

{{-- Universal mobile fix: every page's content sits in a white box
     (.page-content-wrapper.p-30). Its 30px side padding eats too much width on
     phones and crushes inner content into tall narrow columns. Trim it on small
     screens. Loaded after responsive.css so it wins. Applies app-wide (admin,
     owner, affiliate, tenant, maintainer, finance-partner). --}}
<style>
    @media (max-width: 640px) {
        .page-content-wrapper.p-30 { padding: 14px !important; }
    }

    /* ── Shared brand mark (sidebar/topbar) — see common/layouts/_brand.blade.php ── */
    .cs-brand { display: inline-flex; align-items: center; justify-content: center; height: 100%; }
    .cs-brand__img { max-height: 30px; width: auto; object-fit: contain; }
    .cs-brand__img--lg { max-height: 34px; }
    /* CS icon (submark) — reads cleanly on light topbars where the glossy wordmark can't. */
    .cs-brand__icon { height: 30px; width: auto; object-fit: contain; }
    .cs-brand__lockup { display: inline-flex; align-items: center; gap: 9px; }
    .cs-brand__icon--lg { height: 30px; }
    .cs-brand__word {
        font-family: inherit; font-size: 20px; font-weight: 700; letter-spacing: -0.02em;
        line-height: 1; white-space: nowrap;
    }
    .cs-brand__word--a { color: #1F2430; }   /* dark half / full name */
    .cs-brand__word--b { color: #185FA5; }   /* brand-blue half */
    .cs-brand__mono {
        display: inline-flex; align-items: center; justify-content: center;
        width: 32px; height: 32px; border-radius: 9px;
        background: #185FA5; color: #fff; font-weight: 700; font-size: 16px; line-height: 1;
    }
    /* Keep the brand box a sensible height so a missing/large logo can't balloon it. */
    .navbar-brand-box { display: flex; align-items: center; justify-content: center; }
    .navbar-brand-box .logo-lg img, .navbar-brand-box .logo-sm img { max-height: 34px; width: auto; }

    /* ── Initials avatar fallback (topbar profile, notification senders, etc.) ── */
    .cs-avatar {
        display: inline-flex; align-items: center; justify-content: center;
        width: 32px; height: 32px; border-radius: 50%; flex: none;
        color: #fff; font-weight: 600; font-size: 12px; line-height: 1;
        text-transform: uppercase; vertical-align: middle;
    }
    .cs-avatar.avatar-xs { width: 28px; height: 28px; font-size: 11px; }
    /* Topbar language globe fallback */
    .cs-lang-globe { font-size: 20px; color: #6b7280; line-height: 1; }

    /* ── Document display (consistent across KYC, tenant docs, doc-config, tenant details) ── */
    /* Clickable thumbnail — opens the file full-size in a new tab (view, not download). */
    .doc-thumb { display:inline-flex; align-items:center; justify-content:center; width:54px; height:54px; border-radius:9px;
        border:0.5px solid #e5e7eb; overflow:hidden; background:#f3f4f6; cursor:zoom-in; text-decoration:none; position:relative; transition:border-color .13s, box-shadow .13s; }
    .doc-thumb:hover { border-color:#185FA5; box-shadow:0 0 0 3px rgba(24,95,165,.12); }
    .doc-thumb img { width:100%; height:100%; object-fit:cover; }
    .doc-thumb--pdf { flex-direction:column; gap:1px; background:#FAECE7; color:#A32D2D; cursor:pointer; }
    .doc-thumb--pdf i { font-size:20px; line-height:1; }
    .doc-thumb--pdf span { font-size:8px; font-weight:700; letter-spacing:.03em; }
    .doc-thumb--empty { background:#fafafa; color:#c9ccd1; cursor:default; border-style:dashed; font-size:16px; }
    .doc-thumb--empty:hover { border-color:#e5e7eb; box-shadow:none; }

    /* Large preview inside a modal — click to open full-size. */
    .doc-preview-wrap { display:flex; flex-direction:column; gap:8px; }
    .doc-preview { display:block; width:100%; max-height:340px; object-fit:contain; border-radius:10px; border:0.5px solid #e5e7eb;
        background:#f7f9fc; cursor:zoom-in; }
    .doc-preview-open { align-self:flex-start; font-size:12px; font-weight:600; color:#185FA5; text-decoration:none; display:inline-flex; align-items:center; gap:5px; }
    .doc-preview-open:hover { color:#0F4A84; }
    .doc-preview--pdf { display:flex; align-items:center; gap:12px; padding:16px; border:0.5px solid #e5e7eb; border-radius:10px; background:#fafafa; }
    .doc-preview--pdf .doc-preview__ic { width:44px; height:44px; border-radius:10px; background:#FAECE7; color:#A32D2D; display:flex; align-items:center; justify-content:center; font-size:22px; flex:none; }

    /* Document-request prompt (tenant "please upload X" cards) */
    .doc-request { display:flex; align-items:center; gap:14px; padding:14px 44px 14px 16px; margin-bottom:16px;
        background:#FDF6EC; border:0.5px solid #F5D9A8; border-radius:12px; }
    .doc-request__ic { width:40px; height:40px; border-radius:11px; flex:none; display:inline-flex; align-items:center; justify-content:center; background:#FAEEDA; color:#854F0B; font-size:19px; }
    .doc-request__body { flex:1; min-width:0; }
    .doc-request__title { font-size:14px; font-weight:600; color:#6B3E08; margin:0; }
    .doc-request__text { font-size:12.5px; color:#7A4A10; margin:2px 0 0; }
    .doc-request__btn { flex:none; display:inline-flex; align-items:center; gap:6px; background:#854F0B; color:#fff; border:none;
        font-size:12.5px; font-weight:600; padding:8px 14px; border-radius:8px; cursor:pointer; white-space:nowrap; }
    .doc-request__btn:hover { background:#6f4109; }

    /* style.css sets a blanket `a:hover { color: primary !important }`, which clobbers the
       LABEL colour of every button-styled <a> (View Details, cs-btn links, card CTAs…) →
       blue text on a blue hover fill, i.e. an invisible label. Restore white on the filled
       button classes across every design scheme. (<button> variants aren't affected by the
       a:hover rule; ghost/back variants that stay light on hover are deliberately excluded.) */
    a.cs-btn:hover,
    a.prop-btn--primary:hover, a.prop-btn--upgrade:hover, a.prop-card__cta:hover,
    a.tk-card__cta:hover, a.tk-card__more:hover,
    a.td-btn:hover, a.td-btn--primary:hover,
    a.ta-btn-primary:hover, a.ta-btn-danger:hover,
    a.ul-btn--primary:hover,
    a.ow-btn--primary:hover, a.ow-btn--purple:hover,
    a.theme-btn:hover, a.theme-btn-primary:hover, a.theme-btn-purple:hover,
    a.theme-btn-green:hover, a.theme-btn-red:hover, a.upgrade-btn:hover, a.near-limit-btn:hover,
    div.dt-buttons a.dt-button:hover, div.dt-buttons a.theme-btn:hover {
        color: #fff !important;
    }

    /* Uniform header: give the legacy `.page-title-box` breadcrumb header the cs- look
       app-wide (title weight/size, muted crumb, blue links, no divider) so pages not yet
       converted to cs-titlebar still read on-brand. Hardcoded hex so it works everywhere,
       independent of the cs token layer. Pages already using cs-titlebar are unaffected. */
    .page-title-box { border-bottom: none !important; margin-bottom: 18px !important; padding-bottom: 0 !important; }
    .page-title-box .page-title-left h3, .page-title-box h3 {
        font-size: 22px !important; font-weight: 500 !important; color: #111827 !important; margin: 0 !important;
    }
    .page-title-box .breadcrumb { font-size: 12px; margin: 6px 0 0; padding: 0; background: transparent; }
    .page-title-box .breadcrumb .breadcrumb-item { color: #9ca3af; }
    .page-title-box .breadcrumb .breadcrumb-item a { color: #185FA5; text-decoration: none; }
    .page-title-box .breadcrumb .breadcrumb-item a:hover { color: #0F4A84; }
    .page-title-box .breadcrumb .breadcrumb-item.active { color: #6b7280; }
</style>

<!-- FAVICONS -->
<link rel="icon" href="{{ getSettingImage('app_fav_icon') }}" type="image/png" sizes="16x16">
<link rel="shortcut icon" href="{{ getSettingImage('app_fav_icon') }}" type="image/x-icon">
<link rel="shortcut icon" href="{{ getSettingImage('app_fav_icon') }}">


<!-- Sweetalert & Toastr -->
<link rel="stylesheet" href="{{asset('assets/sweetalert2/sweetalert2.css')}}">
<link rel="stylesheet" href="{{ asset('assets/css/toastr.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/dropify.css') }}">

<!-- Select2 -->
<link href="{{ asset('assets/css/select2.min.css') }}" rel="stylesheet" />
