 <!-- Footer Start -->
 <footer class="footer-area position-relative">
     <div class="container position-relative">
         <!-- footer-widget-area -->
         <div class="row footer-top-part">
             <div class="col-12 col-sm-12 col-md-12 col-lg-3">
                 <div class="footer-widget footer-about">
                     <a href="{{ route('frontend') }}"><img src="{{ getSettingImage('app_logo_white') }}"
                             alt="{{ getOption('app_name') }}"></a>
                     <address class="big-para">
                         <p>{{ getOption('app_footer_text') }}</p>
                     </address>
                     <div class="footer-social mt-20">
                         <ul class="d-flex align-items-center">
                             @if (getOption('facebook_url'))
                                 <li><a href="{{ getOption('facebook_url') }}" target="_blank"><span class="iconify"
                                             data-icon="brandico:facebook"></span></a></li>
                             @endif
                             @if (getOption('twitter_url'))
                                 <li><a href="{{ getOption('twitter_url') }}" target="_blank"><span class="iconify"
                                             data-icon="akar-icons:twitter-fill"></span></a></li>
                             @endif
                             @if (getOption('linkedin_url'))
                                 <li><a href="{{ getOption('linkedin_url') }}" target="_blank"><span class="iconify"
                                             data-icon="jam:linkedin"></span></a></li>
                             @endif
                             @if (getOption('skype_url'))
                                 <li><a href="{{ getOption('skype_url') }}" target="_blank"><span class="iconify"
                                             data-icon="ant-design:skype-filled"></span></a></li>
                             @endif
                         </ul>
                     </div>
                 </div>
             </div>
             <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                 <div class="footer-widget">
                     <h4 class="footer-widget-title font-medium">{{ __('Product') }}</h4>
                     <div class="footer-links d-flex">
                         <ul>
                             <li><a href="{{ route('login') }}">{{ __('Dashboard') }}</a></li>
                             @if (getOption('home_pricing_section_status', 1) == ACTIVE)
                                 <li><a href="{{ route('frontend') }}#pricing">{{ __('Pricing') }}</a></li>
                             @endif
                             @if (getOption('home_testimonial_section_status', 1) == ACTIVE)
                                 <li><a href="{{ route('frontend') }}#testimonial">{{ __('Testimonials') }}</a></li>
                             @endif
                         </ul>
                     </div>
                 </div>
             </div>
             <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                 <div class="footer-widget">
                     <h4 class="footer-widget-title font-medium">{{ __('Pages') }}</h4>
                     <div class="footer-links d-flex">
                         <ul>
                             <li><a href="{{ route('terms-conditions') }}">{{ __('Terms & Conditions') }}</a></li>
                             <li><a href="{{ route('privacy-policy') }}">{{ __('Privacy Policy') }}</a></li>
                             <li><a href="{{ route('cookie-policy') }}">{{ __('Cookie Policy') }}</a></li>
                         </ul>
                     </div>
                 </div>
             </div>
             <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                 <div class="footer-widget">
                     <h4 class="footer-widget-title font-medium">{{ __('Support') }}</h4>
                     <div class="footer-links d-flex">
                         <ul>
                             @if (getOption('home_faq_section_status', 1) == ACTIVE)
                                 <li><a href="{{ route('frontend') }}#faqs">{{ __('Faqs') }}</a></li>
                             @endif
                             @if (getOption('home_about_us_section_status', 1) == ACTIVE)
                                 <li><a href="{{ route('frontend') }}#about">{{ __('About Us') }}</a></li>
                             @endif
                             @if (getOption('home_how_it_word_section_status', 1) == ACTIVE)
                                 <li><a href="{{ route('frontend') }}#how-it-works">{{ __('How it Works') }}</a></li>
                             @endif
                             <li><a href="{{ route('frontend') }}#support">{{ __('Contact Us') }}</a></li>
                         </ul>
                     </div>
                 </div>
             </div>
         </div>
     </div>
     <div class="copyright-wrapper">
         <div class="container">
             <!--copyright-text-->
             <div class="row">
                 <div class="col-12 col-md-12">
                     <div class="copyright-text text-center">
                         <p>{{ getOption('app_copyright') }}</p>
                     </div>
                 </div>
             </div>
         </div>
     </div>
 </footer>
 <!-- Footer End -->
