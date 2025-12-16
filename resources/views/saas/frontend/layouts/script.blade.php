<!--=======================================
        All Jquery Script link
        ===========================================-->
<!-- Bootstrap core JavaScript -->
<script src="{{ asset('assets/libs/jquery/jquery-3.6.0.min.js') }}"></script>
<script src="{{ asset('frontend/assets/vendor/jquery/popper.min.js') }}"></script>
<script src="{{ asset('frontend/assets/vendor/bootstrap/js/bootstrap.min.js') }}"></script>

<!-- ==== Plugin JavaScript ==== -->
{{-- <script src="assets/vendor/jquery-easing/jquery.easing.min.js"></script> --}}
<script src="{{ asset('assets/libs/jquery-easing/jquery.easing.min.js') }}"></script>

{{-- <script src="assets/js/jquery-ui.min.js"></script> --}}
<script src="{{ asset('assets/libs/jquery-ui/jquery-ui.min.js') }}"></script>

{{-- <script src="assets/js/owl.carousel.min.js"></script> --}}
<script src="{{ asset('assets/libs/owl-carousel/owl.carousel.min.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/venobox/2.0.4/venobox.min.js"></script>
 
<!--Wow Script-->
<script src="{{ asset('frontend/assets/js/wow.min.js') }}"></script>

<!--Iconify Icon-->
{{-- <script src="assets/js/iconify.min.js"></script> --}}
<script src="{{ asset('assets/js/iconify.min.js') }}"></script>
<script src="{{ asset('assets/js/custom/common.js') }}"></script>
<script src="{{ asset('assets/js/toastr.min.js') }}"></script>
<!-- Standalon Js Script Start -->

<!-- Standalon Js Script End -->

<!-- Menu js -->
<script src="{{ asset('frontend/assets/js/menu.js') }}"></script>

<!-- Custom scripts for this template -->
<script src="{{ asset('frontend/assets/js/custom.js') }}"></script>
@stack('script')

<script>
    var currencySymbol = "{{ getCurrencySymbol() }}";
    var currencyPlacement = "{{ getCurrencyPlacement() }}";
</script>
