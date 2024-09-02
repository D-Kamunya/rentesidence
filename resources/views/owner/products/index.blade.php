@extends('owner.layouts.app')

@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="page-content-wrapper">
                <div class="container-fluid">
                    <!-- Page Content Wrapper Start -->
                    <div class="page-content-wrapper bg-white p-30 radius-20">
                        <!-- start page title -->
                        <div class="row">
                            <div class="col-12">
                                <div class="page-title-box d-sm-flex align-items-center justify-content-between border-bottom mb-20">
                                    <div class="page-title-left">
                                        <h3 class="mb-sm-0">My Shop</h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- end page title -->

                        <!-- Products Area row Start -->
                        <div class="row">
                            <!-- Products Top Bar Start -->
                            <div class="tenants-top-bar">
                                <div class="property-search-inner-bg bg-off-white theme-border radius-4 p-25 pb-0 mb-25">
                                    <div class="row">
                                        <div class="col-xl-12 col-xxl-6 tenants-top-bar-left">
                                            <div class="row">
                                                <form method="GET" action="{{ route('owner.products.index') }}">
                                                    <div class="col-md-6 col-lg-6 col-xl-4 col-xxl-4 mb-25">
                                                        <select name="type" class="form-control">
                                                            <option value="">Select Type</option>
                                                            <option value="product" {{ request('type') == 'product' ? 'selected' : '' }}>Product</option>
                                                            <option value="service" {{ request('type') == 'service' ? 'selected' : '' }}>Service</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-6 col-lg-6 col-xl-4 col-xxl-4 mb-25">
                                                        <select name="category" class="form-control">
                                                            <option value="">Select Category</option>
                                                            <!-- Options for Categories -->
                                                            <option value="foods" {{ request('category') == 'foods' ? 'selected' : '' }}>Foods</option>
                                                            <!-- More categories... -->
                                                        </select>
                                                    </div>
                                                    <div class="col-auto mb-25">
                                                        <button type="submit" class="default-btn theme-btn-purple w-auto"
                                                            title="{{ __('Filter') }}">{{ __('Filter') }}</button>
                                                        <a href="{{ route('owner.products.index') }}" class="action-button theme-btn mt-25">Reset Filters</a>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                        <div class="col-xl-12 col-xxl-12 tenants-top-bar-right">
                                            <div class="row justify-content-end">
                                                <div class="col-auto mb-25">
                                                    <a href="{{ route('owner.products.create') }}" class="theme-btn w-auto"
                                                        title="{{ __('Add Product/service') }}">{{ __('Add Product/service') }}</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Products Top Bar End -->
                        </div>

                        <!-- Products/Services Wrap Start -->
                        @if($products->count() > 0)
                            <div class="row product-grid">
                                @foreach ($products as $product)
                                @php
                                    $images = json_decode($product->images, true); // Decode the JSON into an array
                                @endphp
                                <div class="product-card">
                                    @if(is_array($images) && count($images) > 0)
                                        <img src="{{ asset('storage/' . $images[0]) }}" alt="{{ $product->name }}" class="product-image-cover">
                                    @else
                                        <p>No image available.</p>
                                    @endif

                                    <h2 class="product-title">{{ $product->name }}</h2>

                                    <div class="product-action-buttons">
                                        <a href="{{ route('owner.products.edit', $product->id) }}" class="action-icon">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('owner.products.destroy', $product->id) }}" method="POST" class="delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="action-icon delete-button">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                        <button class="action-icon more-button" data-bs-toggle="modal" data-bs-target="#product-details-{{ $product->id }}">
                                            <i class="fas fa-ellipsis-h"></i>
                                        </button>
                                    </div>
                                </div>

                                <!-- Product Details Modal -->
                                <div class="modal fade" id="product-details-{{ $product->id }}" tabindex="-1" role="dialog" aria-labelledby="productDetailsLabel" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="productDetailsLabel">{{ $product->name }}</h5>
                                                <button type="button" class="close-btn" data-bs-dismiss="modal" aria-label="Close">
                                                    &times;
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="product-cover-image">
                                                    <img src="{{ asset('storage/' . $images[0]) }}" alt="{{ $product->name }}" class="product-image-cover">
                                                </div>

                                                <div class="image-slider-wrapper">
                                                    <button class="scroll-button scroll-button-left">&lt;</button> <!-- Left Scroll Button -->
                                                    <div class="image-slider">
                                                        @foreach($images as $image)
                                                            <div class="image-slide">
                                                                <img src="{{ asset('storage/' . $image) }}" alt="{{ $product->name }}">
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                    <button class="scroll-button scroll-button-right">&gt;</button> <!-- Right Scroll Button -->
                                                </div>

                                                <p><strong>Description:</strong> {{ $product->description }}</p>
                                                <p><strong>Category:</strong> {{ $product->category }}</p>
                                                <p><strong>Type:</strong> {{ $product->type }}</p>
                                                <p><strong>Price:</strong> Ksh. {{ $product->price }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>


                            @endforeach

                            </div>
                            {{ $products->links() }}
                        @else
                            <p>No products found.</p>
                        @endif
                        <!-- Products/Services Wrap End -->
                    </div>
                    <!-- Page Content Wrapper End -->
                </div>
            </div>
        </div>
    </div>
    <!-- End Page-content -->
@endsection

@push('style')
    <link rel="stylesheet" href="{{ asset('assets/css/product.css') }}">
@endpush

@push('script')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const sliders = document.querySelectorAll('.image-slider');
        const scrollAmount = 10; // Adjust scroll amount for each click
        const scrollInterval = 20000; // 5 seconds interval for auto-scrolling

        sliders.forEach(slider => {
            let isDown = false;
            let startX;
            let scrollLeft;
            let autoScroll;

        // Swipe/drag interactions
        slider.addEventListener('mousedown', (e) => {
            isDown = true;
            slider.classList.add('active');
            startX = e.pageX - slider.offsetLeft;
            scrollLeft = slider.scrollLeft;
            clearInterval(autoScroll); // Stop auto-scrolling on interaction
        });

        slider.addEventListener('mouseleave', () => {
            isDown = false;
            slider.classList.remove('active');
            startAutoScroll(slider); // Resume auto-scrolling
        });

        slider.addEventListener('mouseup', () => {
            isDown = false;
            slider.classList.remove('active');
            startAutoScroll(slider); // Resume auto-scrolling
        });

        slider.addEventListener('mousemove', (e) => {
            if (!isDown) return;
            e.preventDefault();
            const x = e.pageX - slider.offsetLeft;
            const walk = (x - startX) * 2;
            slider.scrollLeft = scrollLeft - walk;
        });

        // Touch support for swipe on mobile devices
        slider.addEventListener('touchstart', (e) => {
            isDown = true;
            slider.classList.add('active');
            startX = e.touches[0].pageX - slider.offsetLeft;
            scrollLeft = slider.scrollLeft;
            clearInterval(autoScroll); // Stop auto-scrolling on interaction
        });

        slider.addEventListener('touchend', () => {
            isDown = false;
            slider.classList.remove('active');
            startAutoScroll(slider); // Resume auto-scrolling
        });

        slider.addEventListener('touchmove', (e) => {
            if (!isDown) return;
            e.preventDefault();
            const x = e.touches[0].pageX - slider.offsetLeft;
            const walk = (x - startX) * 2;
            slider.scrollLeft = scrollLeft - walk;
        });

        // Button interactions
        const scrollLeftButton = slider.previousElementSibling; // Assuming buttons are before and after the slider
        const scrollRightButton = slider.nextElementSibling;

        scrollLeftButton.addEventListener('click', () => {
            slider.scrollBy({
                left: -scrollAmount,
                behavior: 'smooth'
            });
            clearInterval(autoScroll); // Stop auto-scrolling on button click
            startAutoScroll(slider); // Resume auto-scrolling
        });

        scrollRightButton.addEventListener('click', () => {
            slider.scrollBy({
                left: scrollAmount,
                behavior: 'smooth'
            });
            clearInterval(autoScroll); // Stop auto-scrolling on button click
            startAutoScroll(slider); // Resume auto-scrolling
        });

        // Function to start automatic scrolling
        function startAutoScroll(slider) {
            autoScroll = setInterval(() => {
                // Scroll to the right, and reset to the start if it reaches the end
                if (slider.scrollLeft + slider.clientWidth >= slider.scrollWidth) {
                    slider.scrollLeft = 0; // Go back to the start
                } else {
                    slider.scrollBy({
                        left: scrollAmount,
                        behavior: 'smooth'
                    });
                }
            }, scrollInterval);
        }

        // Start auto-scrolling initially
        startAutoScroll(slider);
        });
    });

</script>
@endpush


