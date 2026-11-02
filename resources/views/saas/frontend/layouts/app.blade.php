<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    @if (isset($_SERVER['HTTPS']))
        <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests" />
    @endif
    <meta name="title" content="{{ getOption('app_name') }}">
    {{-- Per-page overridable: a blog post (or any view) sets @section('meta_description') etc.;
         everything else falls back to the global site defaults. --}}
    <meta name="description" content="@yield('meta_description', getOption('meta_description'))">
    <meta name="keywords" content="@yield('meta_keywords', getOption('meta_keyword'))">
    <meta name="author" content="{{ getOption('meta_author') }}">
    <link rel="canonical" href="@yield('canonical', url()->current())">

    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:site_name" content="{{ getOption('app_name') }}">
    <meta property="og:title" content="@yield('og_title', getOption('app_name'))">
    <meta property="og:description" content="@yield('og_description', getOption('meta_description'))">
    <meta property="og:image" content="@yield('og_image', getSettingImage('app_logo'))">
    <meta property="og:url" content="@yield('canonical', url()->current())">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('og_title', getOption('app_name'))">
    <meta name="twitter:description" content="@yield('og_description', getOption('meta_description'))">
    <meta name="twitter:image" content="@yield('og_image', getSettingImage('app_logo'))">

    <meta name="msapplication-TileImage" content="{{ getSettingImage('app_logo') }}">

    <meta name="msapplication-TileColor" content="#F8F8F8">
    <meta name="theme-color" content="#185FA5">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>{{ getOption('app_name') . ' - ' . @$pageTitle }}</title>

    @include('saas.frontend.layouts.style')

    <!-- FAVICONS -->
    <link rel="icon" href="{{ getSettingImage('app_fav_icon') }}" type="image/png" sizes="16x16">
    <link rel="shortcut icon" href="{{ getSettingImage('app_fav_icon') }}" type="image/x-icon">
    <link rel="shortcut icon" href="{{ getSettingImage('app_fav_icon') }}">

    @include('common.layouts.pwa-meta')

    {{-- Organization identity for search engines (brand entity + logo in rich results). --}}
    <script type="application/ld+json">
    {!! json_encode([
        '@context' => 'https://schema.org',
        '@type'    => 'Organization',
        'name'     => getOption('app_name') ?: 'Centresidence',
        'url'      => url('/'),
        'logo'     => getSettingImage('app_logo'),
        'description' => getOption('meta_description') ?: null,
    ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
    </script>

    {{-- Per-page extra head tags + structured data. --}}
    @stack('meta')
    @stack('jsonld')
</head>

<body class="{{ selectedLanguage()->rtl == 1 ? 'direction-rtl' : 'direction-ltr' }}">

    <!-- Pre Loader Area start -->
    @include('common.layouts.preloader')
    <!-- Pre Loader Area End -->

    <!--Main Menu/Navbar Area Start -->
    @include('saas.frontend.layouts.menu')
    <!--Main Menu/Navbar Area End -->

    <main data-bs-spy="scroll" data-bs-target="#mainNav" data-bs-root-margin="0px 0px -40%" data-bs-smooth-scroll="true"
        tabindex="0">
        @yield('content')
        @include('saas.frontend.layouts.footer')
    </main>

    @include('saas.frontend.layouts.script')
    <script>
        $("#topBannerClose").on('click', function() {
            $(this).parent().remove();
        });
    </script>
</body>

</html>
