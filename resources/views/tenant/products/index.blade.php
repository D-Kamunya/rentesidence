@extends('tenant.layouts.app')

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
                                                            <form method="GET" action="{{ route('tenant.products.index') }}">
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
                                                                        <option value="foods" {{ request('category') == 'foods' ? 'selected' : '' }}>Foods</option>
                                                                        <option value="gas" {{ request('category') == 'gas' ? 'selected' : '' }}>Gas</option>
                                                                        <option value="clothes" {{ request('category') == 'clothes' ? 'selected' : '' }}>Clothes</option>
                                                                        <option value="shoes" {{ request('category') == 'shoes' ? 'selected' : '' }}>Shoes</option>
                                                                        <option value="bags" {{ request('category') == 'bags' ? 'selected' : '' }}>Bags</option>
                                                                        <option value="electronics" {{ request('category') == 'electronics' ? 'selected' : '' }}>Electronics</option>
                                                                        <option value="beautyproducts" {{ request('category') == 'beautyproducts' ? 'selected' : '' }}>Beauty Products</option>
                                                                        <option value="kitchenware" {{ request('category') == 'kitchenware' ? 'selected' : '' }}>Kitchenware</option>
                                                                        <option value="detergents" {{ request('category') == 'detergents' ? 'selected' : '' }}>Detergents</option>
                                                                        <option value="furniture" {{ request('category') == 'furniture' ? 'selected' : '' }}>Furniture</option>
                                                                        <option value="sanitation" {{ request('category') == 'sanitation' ? 'selected' : '' }}>Sanitation</option>
                                                                        <option value="toiletries" {{ request('category') == 'toiletries' ? 'selected' : '' }}>Toiletries</option>
                                                                        <option value="hygiene" {{ request('category') == 'hygiene' ? 'selected' : '' }}>Hygiene</option>
                                                                    </select>
                                                                </div>
                                                                <div class="col-auto mb-25">
                                                                    <button type="submit" class="default-btn theme-btn-purple w-auto"
                                                                        title="{{ __('Filter') }}">{{ __('Filter') }}</button>
                                                                        <a href="{{ route('tenant.products.index') }}" class="action-button theme-btn mt-25">Reset Filters</a>
                                                                </div>
                                                            </form>
                                                
                                                </div>
                                            </div>

                                            <div class="col-xl-12 col-xxl-6 tenants-top-bar-right">
                                                <div class="row justify-content-end">
                                                    <div class="col-auto mb-25">
                                                        <a href="" class="theme-btn w-auto"
                                                            title="{{ __('Add Product/service') }}">{{ __('My Cart') }}</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <!-- Products Top Bar End -->

                            <!-- Products/services Wrap Start -->
                                            @if($products->count() > 0) 
                                                <div class="row">
                                                    @foreach ($products as $product)
                                                        <div style="padding: 30px;">
                                                            @php
                                                                $images = json_decode($product->images, true); // Decode the JSON
                                                            @endphp
                                                                @if(is_array($images) && count($images) > 0)
                                                                        @foreach($images as $image)
                                                                            <img src="{{ asset('storage/' . $image) }}" alt="{{ $product->name }}" style="max-width: 150px;">
                                                                        @endforeach
                                                                    @else
                                                                        <p>No images available.</p>
                                                                @endif
                                                                <h2 style="font-size: 20px; font-weight:bold;">{{ $product->name }}</h2>
                                                                <p>{{ $product->description }}</p>
                                                                <div class="property-item-info d-flex mt-15 flex-wrap bg-white theme-border py-3 px-2 radius-4 p-5">
                                                                    <p style="padding: 20px; color:blue; font-weight:bold;">Ksh.{{ $product->price }}</p>
                                                                    <p style="padding: 20px;">Category: {{ $product->category }}</p>
                                                                    <p style="padding: 20px;">Type: {{ $product->type }}</p>
                                                                </div>
                                                                    <form action="" method="POST">
                                                                        <button type="submit" class="action-button theme-btn mt-25">Add to cart</button>
                                                                        <button type="submit" class="action-button theme-btn mt-25">Order Now</button>
                                                                    </form> 
                                                        </div> 
                                                    @endforeach
                                                            {{ $products->links() }}
                                                        @else
                                                            <p>No products found.</p>
                                                        @endif
                                                </div>
                        </div>              
                        <!-- Products/services Wrap End -->
                
                        <!-- All products Area row End -->
                    </div>
                    <!-- Page Content Wrapper End -->
                </div>
            </div>
        </div>
    </div>
        <!-- End Page-content -->
        <input type="hidden" id="getAllTenantRoute" value="{{ route('owner.tenant.index', ['type' => 'all']) }}">
        <input type="hidden" id="getPropertyUnitsRoute" value="{{ route('owner.property.getPropertyUnits') }}">

@endsection
@if (getOption('app_card_data_show', 1) != 1)
    @push('style')
        @include('common.layouts.datatable-style')
    @endpush
    @push('script')
        @include('common.layouts.datatable-script')
        <script src="{{ asset('assets/js/custom/tenant-datatable.js') }}"></script>
    @endpush
@endif
@push('script')
    <script src="{{ asset('assets/js/custom/tenant-list.js') }}"></script>
@endpush

