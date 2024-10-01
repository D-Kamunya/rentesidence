@extends('tenant.layouts.app')

@section('content')
    @if (session('success'))
        <script>
            localStorage.removeItem('cartItems');
        </script>
    @endif
    <div class="main-content">
        <div class="page-content">
            <div class="page-content-wrapper"> 
                <div class="container-fluid">
                    <!-- Page Content Wrapper Start -->
                    <div class="page-content-wrapper bg-white p-30 radius-20">
                        <!-- start page title -->
                        <div class="row">
                            <div class="col-12">
                                <div
                                    class="page-title-box d-sm-flex align-items-center justify-content-between border-bottom mb-20">
                                    <div class="page-title-left">
                                        <h3 class="mb-sm-0">Market Place</h3>
                                    </div>
                                    <div class="page-title-right">
                                        <ol class="breadcrumb mb-0">
                                            <li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}"
                                                    title="{{ __('Dashboard') }}">{{ __('Dashboard') }}</a></li>
                                            <li class="breadcrumb-item active" aria-current="page">Marketplace</li>
                                        </ol>
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

                                        <h2 class="product-id d-none">{{$product->id}}</h2>

                                        <h2 class="product-price d-none">{{$product->price}}</h2>

                                        <div class="product-action-buttons">
                                            <!-- Add to Cart Button -->
                                            <button class="action-icon add-to-cart-button" title="Add to Cart">
                                                <i class="fas fa-cart-plus"></i> <!-- Font Awesome cart icon -->
                                            </button>

                                            <!-- Order Now Button -->
                                            <button class="action-icon order-now-button" title="Order Now">
                                                <i class="fas fa-shopping-bag"></i> <!-- Font Awesome shopping bag icon -->
                                            </button>

                                            <!-- More Details Button -->
                                            <button class="action-icon more-button" title="Read More" data-bs-toggle="modal" data-bs-target="#product-details-{{ $product->id }}">
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

    <!-- Floating Cart Button -->
    <div id="floating-cart-button" class="floating-cart" data-url="{{ route('tenant.products.pay') }}">
        <i class="fas fa-shopping-cart"></i>
        <span id="cart-counter" class="cart-counter">0</span>
    </div>

    <div id="toast" class="toast">
        <i class="fas fa-cart-plus"></i> <!-- Shopping cart icon -->
        <span id="toast-message">Item added to cart!</span>
    </div>

    <!-- End Page-content -->
    <input type="hidden" id="getAllTenantRoute" value="{{ route('owner.tenant.index', ['type' => 'all']) }}">
    <input type="hidden" id="getPropertyUnitsRoute" value="{{ route('owner.property.getPropertyUnits') }}">

@endsection

@push('script')
    <script src="{{ asset('assets/js/custom/tenant-product.js') }}"></script>
@endpush
@push('style')
    <link rel="stylesheet" href="{{ asset('assets/css/product.css') }}">
@endpush
