@extends('owner.layouts.app')

@section('content')
    <div class="main-content">

        <div class="page-content">
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
                        <div class="products-wrap">
                            <div class="row">
                            @if($products->count() > 0) 
                                @foreach ($products as $product)
                                    <div>
                                        <h2>{{ $product->name }}</h2>
                                        <p>{{ $product->description }}</p>
                                        <p>{{ $product->price }}</p>
                                        <p>{{ $product->category }}</p>
                                        <p>{{ $product->type }}</p>
                                        <img src="{{ asset('storage/'.$product->image) }}" alt="{{ $product->name }}">
                                        <a href="{{ route('owner.products.edit', $product->id) }}">Edit</a>
                                        <form action="{{ route('owner.products.destroy', $product->id) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit">Delete</button>
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
                    </div>
                    <!-- All products Area row End -->
                </div>
                <!-- Page Content Wrapper End -->
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
