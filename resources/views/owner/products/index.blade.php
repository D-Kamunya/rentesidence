@extends('owner.layouts.app')

@section('content')
    <div class="main-content">

    <div class="page-content">
        <div class="page-content-wrapper bg-white p-30 radius-20"> 
            <div class="container-fluid">
                <!-- Page Content Wrapper Start -->
                <div class="page-content-wrapper bg-white p-30 radius-20">
                    <!-- start page title -->
                    <div class="row">
                        <div class="col-12">
                            <div
                                class="page-title-box d-sm-flex align-items-center justify-content-between border-bottom mb-20">
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
                                            @if (getOption('app_card_data_show', 1) == 1)
                                                <div class="col-md-6 col-lg-6 col-xl-4 col-xxl-4 mb-25">
                                                    <select class="form-select flex-shrink-0 property_id">
                                                        <option value="0">--{{ __('Select category') }}--</option>
                                                        
                                                    </select>
                                                </div>
                                                <div class="col-md-6 col-lg-6 col-xl-4 col-xxl-4 mb-25">
                                                    <select class="form-select flex-shrink-0 unit_id">
                                                        <option value="0" selected>--{{ __('Select category') }}--</option>
                                                    </select>
                                                </div>
                                                <div class="col-auto mb-25">
                                                    <button type="button" class="default-btn theme-btn-purple w-auto"
                                                        id="applySearch"
                                                        title="{{ __('Apply') }}">{{ __('Apply') }}</button>
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="col-xl-12 col-xxl-6 tenants-top-bar-right">
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
                                                                    <a href="{{ route('owner.products.edit', $product->id) }}" class="action-button theme-btn mt-25">Edit</a>
                                                                    <form action="{{ route('owner.products.destroy', $product->id) }}" method="POST">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" class="action-button theme-btn mt-25">Delete</button>
                                                                    </form> 
                                                                </div>      
                                                        </div>     
                                                        @endforeach
                                                            {{ $products->links() }}
                                                        @else
                                                            <p>No products found.</p>
                                                        @endif
                                                </div>              
                        <!-- Products/services Wrap End -->
                    </div>
                    <!-- All products Area row End -->
                </div>
                <!-- Page Content Wrapper End -->
            </div>
        </div>
    </div>
        <!-- End Page-content -->
    </div>
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
