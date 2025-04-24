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
                                    <div class="page-title-box d-sm-flex align-items-center justify-content-between border-bottom mb-20">
                                        <div class="page-title-right">
                                            <ol class="breadcrumb mb-0">
                                                <a href="{{ route('tenant.product.index') }}" class="breadcrumb-item">Back to Marketplace</a>
                                            </ol>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <!-- end page title -->
                        <div class="container mt-5">
                            <div class="row">
                                <div class="col-md-5">
                                    <div class="product-images">
                                        @php
                                            $images = json_decode($product->images, true); // Decode the JSON
                                        @endphp
                                            <img id="mainImage" src="{{ asset('storage/' . $images[0]) }}" alt="{{ $product->name }}" class="img-fluid" style="max-width: 100%; height: auto; max-height: 500px;">
                                        
                                        <div class="thumbnails">
                                            @php
                                                 $images = json_decode($product->images, true); // Decode the JSON
                                            @endphp
                                            
                                            @if(is_array($images) && count($images) > 0)
                                                @foreach($images as $image)
                                                    <img src="{{ asset('storage/' . $image) }}" alt="{{ $product->name }}" class="img-thumbnail img-fluid thumbnail-img" style="width: 80px; height: 80px; margin-right: 10px; cursor: pointer;">
                                                @endforeach
                                            @else
                                                <p>No images available.</p>
                                            @endif
                                        </div>
                                    </div>
                                </div> 
                                <div class="col-md-5">
                                    <div class="product-card" style="border: none; box-shadow: none; padding: 0; margin: 0; text-align: left;">
                                        <h2 class="product-title" style="font-size: 20px; font-weight:bold; padding: 10px 0 10px 0;">
                                            {{ $product->name }}
                                        </h2>
                                        <p style="padding-bottom: 20px;">{{ $product->description }}</p>
                                        <h4 class="product-price" style="padding-bottom: 20px; color:blue; font-weight:bold;">
                                            Ksh.{{ $product->price }}
                                        </h4>

                                        <!-- Hidden fields for JS -->
                                        <h2 class="product-id d-none">{{ $product->id }}</h2>
                                        <img src="{{ asset('storage/' . $image) }}" alt="{{ $product->name }}" class="d-none product-image">

                                        <!-- Add to Cart Button -->
                                        <form id="add-to-cart-form">
                                            @csrf
                                            <!-- <div class="form-group">
                                                <label for="quantity">Quantity</label>
                                                <input type="number" name="quantity" id="quantity" class="form-control" value="1" min="1">
                                            </div> -->
                                            <button type="button" class="action-button theme-btn mt-25 add-to-cart-button">
                                                Add to Cart
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <!-- Page Content Wrapper End -->
                </div>
            </div>
        </div>
    </div>
    <!-- Floating Cart Button -->
    <div id="floating-cart-button" class="floating-cart" data-url="{{ route('tenant.product.pay') }}">
        <i class="fas fa-shopping-cart"></i>
        <span id="cart-counter" class="cart-counter">0</span>
    </div>
@endsection

<!-- Include the product.js file -->
<script>
    // Image control logic
    document.addEventListener('DOMContentLoaded', function() {
        // Select all thumbnails
        const thumbnails = document.querySelectorAll('.thumbnail-img');
        
        // Main image element
        const mainImage = document.getElementById('mainImage');
        
        // Add click event to each thumbnail
        thumbnails.forEach(function(thumbnail) {
            thumbnail.addEventListener('click', function() {
                mainImage.src = this.src; // Set main image source to the clicked thumbnail's source
            });
        });
    });
</script>
@push('script')
    <script src="{{ asset('assets/js/custom/tenant-product.js') }}"></script>
@endpush
@push('style')
    <link rel="stylesheet" href="{{ asset('assets/css/product.css') }}">
@endpush

