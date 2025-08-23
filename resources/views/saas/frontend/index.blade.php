@extends('saas.frontend.layouts.app')
@section('content')

{{-- Hero Section --}}
<section class="hero-silhouette">
  <div class="container">
      <h1 class="display-5 fw-bold mb-3">
          Property Management Made <span style="color:#3685FC">Simple</span>
      </h1>
      <p class="lead text-muted mx-auto" style="max-width: 720px;">
          Streamline rent collection, manage tenants, and track maintenance in one beautiful dashboard.
      </p>

      <a href="{{ route('frontend') }}#contact-us"
         class="theme-btn position-relative mt-4"
         title="{{ __('Get Started') }}">
          {{ __('Get Started') }} 
          <span class="iconify" data-icon="akar-icons:arrow-right"></span>
      </a>
      <div class="container text-center">
        <div class="d-flex justify-content-center mt-4 position-relative property-stack" style="padding-top: 30px;">
            <img src="{{ asset('assets/images/properties-img/1.jpg') }}" alt="Property 1" class="property-img property-img-left">
            <img src="{{ asset('assets/images/properties-img/2.jpg') }}" alt="Property 2" class="property-img property-img-center">
            <img src="{{ asset('assets/images/properties-img/3.jpg') }}" alt="Property 3" class="property-img property-img-right">
        </div> 
    </div>
  </div>
</section>

{{-- Key Features --}}

<section class="py-5 bg-light">
    <div id="features" class="container">
        <h3 class="text-center mb-4 fw-bold">What we're all about</h3>
        <div id="featuresCarousel" 
             class="carousel slide" 
             data-bs-ride="carousel" 
             data-bs-interval="3000" 
             data-bs-wrap="true">

            <div class="carousel-inner">
                @php
                    $features = [
                        ['icon' => 'mdi:home', 'title' => 'Vacant Unit Listings', 'desc' => 'Vacant units are auto listed to our house hunt page to reduce vacancies and increase your cash flow.'],
                        ['icon' => 'mdi:shield-check', 'title' => 'Automate rent collection', 'desc' => 'Centresidence sends rent invoices, tracks payments, and manages tenant queries automatically.'],
                        ['icon' => 'mdi:chart-bar', 'title' => 'Ticketing System', 'desc' => 'Replace calls with a smart ticketing system. Tenants submit maintenance requests and photo evidence directly from their accounts.'],
                        ['icon' => 'mdi:bell-outline', 'title' => 'Notice Board', 'desc' => 'Make important announcements to your tenants easily within their accounts'],
                        ['icon' => 'mdi:search', 'title' => 'Tenant Screening', 'desc' => 'Centresidence auto-screens new tenants, flags behavioral issues from past records, and alerts you instantly.'],
                        ['icon' => 'mdi:shop', 'title' => 'Marketplace', 'desc' => 'Centresidence’s marketplace lets you earn extra by selling to tenants. SMS alerts and direct payments included all built to help you increase your cashflow'],
                        ['icon' => 'mdi:chart-line', 'title' => 'Reports & Insights', 'desc' => 'Get detailed reports to optimize property management.'],
                        ['icon' => 'mdi:cloud-check', 'title' => 'Cloud Access', 'desc' => 'Access your dashboard from anywhere in the world, anytime.'],
                    ];
                @endphp
                @foreach(array_chunk($features, 4) as $index => $featureGroup)
                    <div class="carousel-item {{ $index == 0 ? 'active' : '' }}">
                        <div class="row text-center">
                            @foreach($featureGroup as $f)
                                <div class="col-md-3">
                                    <div class="card feature-card border-0 h-100 shadow-hover">
                                        <div class="card-body py-4">
                                            <span class="iconify fs-1" data-icon="{{ $f['icon'] }}" style="color: #3685FC;"></span>
                                            <h5 class="fw-bold mt-3">{{ $f['title'] }}</h5>
                                            <p class="text-muted small">{{ $f['desc'] }}</p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
            <!-- Controls -->
            <button class="carousel-control-prev custom-carousel-btn" type="button" data-bs-target="#featuresCarousel" data-bs-slide="prev">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-chevron-left">
                    <polyline points="15 18 9 12 15 6"></polyline>
                </svg>
            </button>
            <button class="carousel-control-next custom-carousel-btn" type="button" data-bs-target="#featuresCarousel" data-bs-slide="next">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-chevron-right">
                    <polyline points="9 18 15 12 9 6"></polyline>
                </svg>
            </button>
        </div>
    </div>
</section>

{{-- House Hunt section --}}
<section class="info-section text-light py-5">
<div class="container">
  <div class="row align-items-center">
    
    <!-- Image -->
    <div class="col-md-6 mb-4 mb-md-0">
      <img src="{{ asset('assets/images/househunthome.jpg') }}" alt="Vacant Property" class="img-fluid rounded-3 shadow-lg">
    </div>

    <!-- Text + CTA -->
    <div class="col-md-6">
      <h3 class="fw-bold mb-3">Find Your Next Home</h3>
      <p class="mb-4">
        Easily browse through available vacant properties in real-time. 
        Our platform helps you connect directly with landlords and agents, 
        making your house hunt faster, simpler, and stress-free.
      </p>
      <a href="{{ route('house.hunt') }}" class="btn btn-gradient px-4 py-2">
        Browse Vacant Properties
      </a>
    </div>
  </div>
</div>
</section>


{{-- How It Works --}}
@php
$tenantSteps = [
    ['icon' => 'mdi:magnify', 'title' => 'Search Rentals', 'desc' => 'Browse and find available rental units in real-time.'],
    ['icon' => 'mdi:file-document-edit-outline', 'title' => 'Apply Online', 'desc' => 'Submit your rental application instantly with just a few clicks.'],
    ['icon' => 'mdi:account-check-outline', 'title' => 'Get Approved', 'desc' => 'Landlords review and approve applications quickly.'],
    ['icon' => 'mdi:key-outline', 'title' => 'Move In', 'desc' => 'Once approved, sign your lease and get your keys.'],
];

$agentSteps = [
    ['icon' => 'mdi:domain', 'title' => 'List Properties', 'desc' => 'Easily add and manage rental properties on the platform.'],
    ['icon' => 'mdi:clipboard-list-outline', 'title' => 'Track Applications', 'desc' => 'Monitor tenant applications and approve or decline them in real-time.'],
    ['icon' => 'mdi:cash-multiple', 'title' => 'Collect Rent', 'desc' => 'Streamline rent collection and automate reminders.'],
    ['icon' => 'mdi:chart-line', 'title' => 'Grow Your Business', 'desc' => 'Gain insights with reports and manage your portfolio effortlessly.'],
];
@endphp

<section class="py-5 bg-light">
<div id="howitworks" class="container">
    <div class="text-center mb-5">
        <h3 class="fw-bold">How It Works</h3>
        <p class="text-muted">Choose your journey and see how it works for you.</p>

        <!-- Toggle Buttons -->
        <div class="btn-group mt-3">
            <button class="btn btn-outline-primary active" id="toggleTenant">For Tenants</button>
            <button class="btn btn-outline-primary" id="toggleAgent">For Agents</button>
        </div>
    </div>

    <!-- Steps Container -->
    <div class="row g-4 how-it-works-container">
        @foreach($tenantSteps as $step)
        <div class="col-md-3 step-card tenant-card fade-slide show">
            <div class="card feature-card border-0 h-100 shadow-hover text-center rounded-3">
                <div class="card-body py-4">
                    <span class="iconify fs-1" data-icon="{{ $step['icon'] }}" style="color: #3685FC;"></span>
                    <h5 class="mt-3">{{ $step['title'] }}</h5>
                    <p class="text-muted small">{{ $step['desc'] }}</p>
                </div>
            </div>
        </div>
        @endforeach

        @foreach($agentSteps as $step)
        <div class="col-md-3 step-card agent-card fade-slide">
            <div class="card feature-card border-0 h-100 shadow-hover text-center rounded-3">
                <div class="card-body py-4">
                    <span class="iconify fs-1" data-icon="{{ $step['icon'] }}" style="color: #3685FC;"></span>
                    <h5 class="mt-3">{{ $step['title'] }}</h5>
                    <p class="text-muted small">{{ $step['desc'] }}</p>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
</section>

{{-- Use Case --}}
@php
$useCases = [
    // Core Users
    ['icon' => 'mdi:office-building', 'title' => 'Property Owners', 'desc' => 'Easily manage tenants, leases, and rent collection.', 'type' => 'Core User'],
    ['icon' => 'mdi:account-tie', 'title' => 'Property Managers', 'desc' => 'Track multiple properties and automate management tasks.', 'type' => 'Core User'],
    ['icon' => 'mdi:domain', 'title' => 'Real Estate Agencies', 'desc' => 'Streamline agency operations with centralized tools.', 'type' => 'Core User'],
    ['icon' => 'mdi:city', 'title' => 'Property Developers', 'desc' => 'Sell and manage new developments seamlessly.', 'type' => 'Core User'],
    ['icon' => 'mdi:warehouse', 'title' => 'Facility Managers', 'desc' => 'Handle large buildings and estates with efficiency.', 'type' => 'Core User'],
    ['icon' => 'mdi:home', 'title' => 'Condominium Boards', 'desc' => 'Manage resident associations, fees, and notices.', 'type' => 'Core User'],
    ['icon' => 'mdi:school', 'title' => 'Student Housing Providers', 'desc' => 'Easily allocate rooms and manage student rentals.', 'type' => 'Core User'],

    // Extended Users
    ['icon' => 'mdi:store', 'title' => 'Co-working Spaces', 'desc' => 'Manage memberships, bookings, and payments.', 'type' => 'Extended User'],
    ['icon' => 'mdi:beach', 'title' => 'Vacation Rentals', 'desc' => 'Automate bookings, cleaning schedules, and guest check-ins.', 'type' => 'Extended User'],
    ['icon' => 'mdi:factory', 'title' => 'Industrial Parks', 'desc' => 'Track warehouses, factories, and commercial leases.', 'type' => 'Extended User'],
    ['icon' => 'mdi:shopping', 'title' => 'Shopping Malls', 'desc' => 'Oversee shop leases, utilities, and tenant billing.', 'type' => 'Extended User'],
    ['icon' => 'mdi:warehouse', 'title' => 'Logistics Hubs', 'desc' => 'Allocate storage units and manage rental agreements.', 'type' => 'Extended User'],
    ['icon' => 'mdi:hotel', 'title' => 'Serviced Apartments', 'desc' => 'Handle bookings, utilities, and recurring payments.', 'type' => 'Extended User'],
    ['icon' => 'mdi:church', 'title' => 'Community & Religious Centers', 'desc' => 'Manage halls, facilities, and rental spaces.', 'type' => 'Extended User'],
    ['icon' => 'mdi:account-group', 'title' => 'Government Housing Projects', 'desc' => 'Digitize housing allocation and rent collection.', 'type' => 'Extended User'],
];

// Split into groups of 8 (4 per row, 2 rows per slide)
$chunks = array_chunk($useCases, 8);
@endphp

<section class="py-5">
<div class="container">
    <h3 class="text-center mb-5 fw-bold">Who Should Use Our Software?</h3>
    <div id="useCasesCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="5000">
        <div class="carousel-inner">
            @foreach($chunks as $i => $chunk)
            <div class="carousel-item @if($i==0) active @endif">
                <div class="row row-cols-1 row-cols-md-4 g-4">
                    @foreach($chunk as $case)
                    <div class="col">
                        <div class="card h-100 shadow-sm position-relative">
                            <!-- Ribbon -->
                            <div class="ribbon 
                                @if($case['type'] === 'Core User') ribbon-core-left 
                                @else ribbon-extended-right @endif">
                                    {{ $case['type'] }}
                            </div>
                            <div class="card-body text-center">
                                <span class="iconify fs-1 mb-3" data-icon="{{ $case['icon'] }}" style="color: #3685FC;"></span>
                                <h6 class="fw-bold">{{ $case['title'] }}</h6>
                                <p class="text-muted small">{{ $case['desc'] }}</p>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>
        <button class="carousel-control-prev custom-carousel-btn" type="button" data-bs-target="#useCasesCarousel" data-bs-slide="prev">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-chevron-left">
                    <polyline points="15 18 9 12 15 6"></polyline>
                </svg>
            </button>
            <button class="carousel-control-next custom-carousel-btn" type="button" data-bs-target="#useCasesCarousel" data-bs-slide="next">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-chevron-right">
                    <polyline points="9 18 15 12 9 6"></polyline>
                </svg>
            </button>
    </div>
</div>
</section>

{{--Testimonials--}}
<section class="py-5 bg-light">
  <div id="testimonials" class="container">
    <div class="row align-items-center">
      
      <!-- Left Side -->
      <div class="col-md-4 mb-4 mb-md-0">
        <h3 class="fw-bold">What Our People Say</h3>
        <p class="text-muted">Real feedback from real users who love our platform.</p>
        <div class="mt-3">
          <button class="btn-control btn-pause" id="pauseScroll">Pause</button>
          <button class="btn-control btn-resume" id="resumeScroll">Resume</button>
        </div>
      </div>

      <!-- Right Side: Infinite Scroll -->
      <div class="col-md-8">
        <div class="testimonial-wrapper">
          <div class="testimonial-track">
            
            <!-- Testimonial Card -->
            <div class="testimonial-card">
              <img src="{{ asset('assets/images/alex.jpg') }}" class="reviewer-img" alt="">
              <h5 class="fw-bold">Alex</h5>
              <p>"This platform has transformed how I manage my properties. Super easy to use!"</p>
              <div class="stars">★★★★★</div>
            </div>
            
            <div class="testimonial-card">
              <img src="{{ asset('assets/images/sarah.jpg') }}" class="reviewer-img" alt="">
              <h5 class="fw-bold">Sarah M.</h5>
              <p>"The customer support is top-notch. I feel valued and supported always."</p>
              <div class="stars">★★★★★</div>
            </div>
            
            <div class="testimonial-card">
              <img src="{{ asset('assets/images/mike.jpg') }}" class="reviewer-img" alt="">
              <h5 class="fw-bold">Michael B.</h5>
              <p>"I love how automated everything is. It saves me hours of manual work."</p>
              <div class="stars">★★★★☆</div>
            </div>
            
            <div class="testimonial-card">
              <img src="{{ asset('assets/images/emily.jpg') }}" class="reviewer-img" alt="">
              <h5 class="fw-bold">Emily R.</h5>
              <p>"The software is smooth, modern, and very reliable. Highly recommend!"</p>
              <div class="stars">★★★★★</div>
            </div>
            
            <div class="testimonial-card">
              <img src="{{ asset('assets/images/david.jpg') }}" class="reviewer-img" alt="">
              <h5 class="fw-bold">David K.</h5>
              <p>"Affordable pricing and amazing features. Worth every penny!"</p>
              <div class="stars">★★★★★</div>
            </div>

          </div>
        </div>
      </div>

    </div>
  </div>
</section>

{{-- Contact Us --}}
<!-- Contact Us Section -->
<section class="py-5">
  <div id="contact-us" class="container">
    <div class="row align-items-center">
      
      <!-- Left Side (Image) -->
      <div class="col-md-6 mb-4 mb-md-0">
        <img src="{{ asset('assets/images/newlogo.png') }}" alt="Contact Us" class="img-fluid rounded shadow-lg">
      </div>
      
      <!-- Right Side (Form) -->
      <div class="col-md-6">
        <div class="card shadow-lg contact-card p-4">
          <h4 class="fw-bold mb-2">Talk to Us</h4>
          <p class="text-muted mb-4">We’d love to hear from you. Please fill out the form below and we’ll get back to you shortly.</p>

          <form class="ajax" action="{{ route('contact.message.store') }}" method="POST" data-handler="getShowMessage">
            @csrf
            <div class="row">
              <div class="col-md-6 mb-3">
                <input type="text" name="first_name" class="form-control rounded-3" placeholder="{{ __('First Name') }}">
              </div>
              <div class="col-md-6 mb-3">
                <input type="text" name="last_name" class="form-control rounded-3" placeholder="{{ __('Last Name') }}">
              </div>
            </div>
            
            <div class="col-md-6 mb-3">
                <input type="email" name="email" class="form-control rounded-3" placeholder="{{ __('Email') }}">
              </div>
            <div class="mb-3">
              <input type="tel" name="phone" class="form-control rounded-3" placeholder="{{ __('Phone Number') }}">
            </div>

            <div class="mb-3">
              <input type="text" name="subject" class="form-control rounded-3" placeholder="{{ __('Subject') }}">
            </div>

            <div class="mb-3">
              <textarea name="message" id="" class="form-control rounded-3" rows="4" placeholder="{{ __('Message') }}"></textarea>
            </div>

            <button type="submit" class="contactus-btn w-100">Send Inquiry</button>
          </form>
        </div>
      </div>

    </div>
  </div>
</section>



{{-- CTA --}}
<section class="py-5 bg-light text-center text-light">
    <h3 class="fw-bold mb-3">Are You a Property Owner or Agent?</h3>
    <p class="mb-4">List your properties with us and reach more tenants instantly.</p>
    <a href="{{ route('frontend') }}#contact-us"  class="theme-btn position-relative">Request Demo</a>
</section>
@endsection
@push('script')
    <script src="{{ asset('assets/js/custom/frontend-index.js') }}"></script>
@endpush