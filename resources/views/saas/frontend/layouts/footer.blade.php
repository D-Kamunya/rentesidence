<!-- Footer Start -->
<footer class="footer-area text-white position-relative pt-5">
    <div class="container">
        <div class="row footer-top-part g-4">

            <!-- Brand / About -->
            <div class="col-12 col-sm-12 col-md-6 col-lg-3">
                <div class="footer-widget footer-about">
                    <a href="{{ route('frontend') }}">
                        <img src="{{ asset('assets/images/newlogo.png') }}" alt="Centresidence" 
                             class="img-fluid mb-3 rounded shadow-lg" style="max-width: 200px;">
                    </a>
                    <p class="small text-white-50">
                        Simplifying property management for owners, managers, and tenants. 
                        Manage rentals, payments, and tenants all in one platform.
                    </p>
                    <div class="footer-social mt-4">
                        <ul class="list-unstyled d-flex gap-3">
                            <li><a href="#" class="text-white fs-5 footer-social-link"><span class="iconify" data-icon="mdi:facebook"></span></a></li>
                            <li><a href="#" class="text-white fs-5 footer-social-link"><span class="iconify" data-icon="mdi:twitter"></span></a></li>
                            <li><a href="#" class="text-white fs-5 footer-social-link"><span class="iconify" data-icon="mdi:linkedin"></span></a></li>
                            <li><a href="#" class="text-white fs-5 footer-social-link"><span class="iconify" data-icon="mdi:instagram"></span></a></li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Product -->
            <div class="col-6 col-md-6 col-lg-3">
                <div class="footer-widget">
                    <h5 class="fw-bold mb-4 text-white">Product</h5>
                    <ul class="list-unstyled small">
                        <li class="mb-2"><a href="{{ route('login') }}" class="footer-link">Dashboard</a></li>
                        <li class="mb-2"><a href="{{ route('frontend') }}#testimonials" class="footer-link">Testimonials</a></li>
                        <li class="mb-2"><a href="{{ route('frontend') }}#features" class="footer-link">Features</a></li>
                        <li class="mb-2"><a href="{{ route('house.hunt') }}" class="footer-link">House Hunt</a></li>
                    </ul>
                </div>
            </div>

            <!-- Pages -->
            <div class="col-6 col-md-6 col-lg-3">
                <div class="footer-widget">
                    <h5 class="fw-bold mb-4 text-white">Pages</h5>
                    <ul class="list-unstyled small">
                        <li class="mb-2"><a href="{{ route('terms-conditions') }}" class="footer-link">Terms & Conditions</a></li>
                        <li class="mb-2"><a href="{{ route('privacy-policy') }}" class="footer-link">Privacy Policy</a></li>
                        <li class="mb-2"><a href="{{ route('cookie-policy') }}" class="footer-link">Cookie Policy</a></li>
                    </ul>
                </div>
            </div>

            <!-- Support -->
            <div class="col-12 col-md-6 col-lg-3">
                <div class="footer-widget">
                    <h5 class="fw-bold mb-4 text-white">Support</h5>
                    <ul class="list-unstyled small">
                        <li class="mb-2"><a href="#faqs" class="footer-link">FAQs</a></li>
                        <li class="mb-2"><a href="{{ route('frontend') }}#features" class="footer-link">About Us</a></li>
                        <li class="mb-2"><a href="{{ route('frontend') }}#howitworks" class="footer-link">How it Works</a></li>
                        <li class="mb-2"><a href="{{ route('frontend') }}#contact-us" class="footer-link">Contact Us</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Divider -->
        <hr class="border-secondary my-4">

        <!-- Copyright -->
        <div class="row">
            <div class="col-12 text-center">
                <p class="bottom-text small text-white-50 mb-0">&copy; {{ date('Y') }} Centresidence. All Rights Reserved.</p>
            </div>
        </div>
    </div>
</footer>