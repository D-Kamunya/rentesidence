<!--=======================================
        All Css Style link
        ===========================================-->

<!-- Bootstrap core CSS -->
<link href="{{ asset('assets/libs/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">

<link href="{{ asset('assets/libs/jquery-ui/jquery-ui.min.css') }}" rel="stylesheet">

<!-- Custom fonts for this template -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@200;300;400;500;600;700;800;900&display=swap"
    rel="stylesheet">

<!-- Animate Css-->
<link rel="stylesheet" href="{{ asset('assets/css/animate.min.css') }}">

<link rel="stylesheet" href="{{ asset('assets/libs/owl-carousel/owl.carousel.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/libs/owl-carousel/owl.theme.default.min.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/venobox/2.0.4/venobox.min.css" />

<!-- Icons -->
<link href="{{ asset('assets/css/icons.min.css') }}" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('assets/css/toastr.min.css') }}">

<style>
    :root {
        @if (getOption('website_color_mode', 0) == ACTIVE)
            --primary-color: {{ getOption('website_primary_color', '#3686FC') }};
            --secondary-color: {{ getOption('website_secondary_color', '#4831DB') }};
            --blue-color: {{ getOption('button_primary_color', '#3686FC') }};
            --primary-hover-color: {{ getOption('button_hover_color', '#0066D9') }};
        @else
            --primary-color: #3686FC;
            --secondary-color: #4831DB;
            --blue-color: #3686FC;
            --primary-hover-color: #0066D9;
        @endif
    }
</style>
<!-- Custom styles for this template -->
<link href="{{ asset('frontend/assets/css/style.css') }}" rel="stylesheet">

<!-- Responsive Css-->
<link rel="stylesheet" href="{{ asset('frontend/assets/css/responsive.css') }}">
@stack('style')
