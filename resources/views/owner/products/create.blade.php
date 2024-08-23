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
                                    <h3 class="mb-sm-0">Add products/Services</h3>
                                </div>
                                <div class="page-title-right">
                                    <ol class="breadcrumb mb-0">
                                        <li class="breadcrumb-item"><a href="{{ route('owner.dashboard') }}"
                                                title="{{ __('Dashboard') }}">{{ __('Dashboard') }}</a></li>
                                        <li class="breadcrumb-item active" aria-current="page">Add products/Services</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- end page title -->

                    <!-- Add Tenants Area row Start -->
                    <div class="all-property-area">

                        <!--Add Tenants Stepper Area Start -->
                        <div class="add-property-stepper-area add-tenants-stepper-area">
                            <div class="row">

                                <!-- Stepper Start -->
                                <div class="col-12">
                                    <div id="msform">
                                        <!-- fieldsets 1 -->
                                        <fieldset>
                                            <form action="{{ route('owner.products.store') }}" method="POST" enctype="multipart/form-data" id="productForm">
                                                @csrf
                                                <div
                                                    class="form-card add-property-box bg-off-white theme-border radius-4 p-20">
                                                    <div class="add-property-title border-bottom pb-25 mb-25">
                                                        <h4>{{ __('Create product or service') }}</h4>
                                                    </div>
                                            
                                                    <div
                                                        class="add-property-inner-box bg-white theme-border radius-4 p-20 pb-0 mb-25">
                                                        <div class="tenants-inner-box-block">
                                                            <div class="add-property-title border-bottom pb-25 mb-25">
                                                                <h4>{{ __('Product Details') }}</h4>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-md-12 mb-25">
                                                                    <label
                                                                        class="label-text-title color-heading font-medium mb-2">{{ __('Product name') }}
                                                                        <span class="text-danger">*</span></label>
                                                                    <input type="text" name="name"
                                                                        class="form-control" role="alert"
                                                                        placeholder="{{ __('product name') }}">
                                                                </div>
                                                            </div>

                                                            <div class="row">
                                                                <div class="col-md-12 mb-25">
                                                                        <label
                                                                        class="label-text-title color-heading font-medium mb-2">{{ __('Product description') }}
                                                                        <span class="text-danger">*</span></label>
                                                                        <textarea name="description" name="description" class="form-control" 
                                                                         role="alert" placeholder="{{ __('product description') }}">                                                                  
                                                                        </textarea>
                                                                </div>
                                                            </div>

                                                            <div class="row">
                                                                <div class="col-md-12 mb-25">
                                                                    <label
                                                                        class="label-text-title color-heading font-medium mb-2">{{ __('Type') }}
                                                                        <span class="text-danger">*</span></label>
                                                                    <select name="type" name="type"
                                                                         class="form-control"
                                                                        placeholder="{{ __('Type') }}">
                                                                        <option value="product">Product</option>
                                                                        <option value="service">Service</option>
                                                                    </select>
                                                                </div>
                                                            </div>

                                                            <div class="row">
                                                                <div class="col-md-12 mb-25">
                                                                    <label
                                                                        class="label-text-title color-heading font-medium mb-2">{{ __('Category') }}
                                                                        <span class="text-danger">*</span></label>
                                                                    <select name="category" name="category"
                                                                         class="form-control">
                                                                        <option value="foods">Foods</option>
                                                                        <option value="gas">Gas</option>
                                                                        <option value="water">Water</option>
                                                                        <option value="clothes">Clothes</option>
                                                                        <option value="shoes">Shoes</option>
                                                                        <option value="bags">Bags</option>
                                                                        <option value="electronics">Electronics</option>
                                                                        <option value="beauty products">Beauty products</option>
                                                                        <option value="kitchenware">Kitchenware</option>
                                                                        <option value="detergents">Detergents</option>
                                                                        <option value="furniture">Furniture</option>
                                                                        <option value="sanitation">Sanitation</option>
                                                                        <option value="toiletries">Toiletries</option>
                                                                        <option value="hygiene">Hygiene</option>
                                                                    </select>
                                                                </div>
                                                            </div>

                                                        </div>
                                                    </div>

                                                    <div
                                                        class="add-property-inner-box bg-white theme-border radius-4 p-20 pb-0 mb-25">
                                                        <div class="tenants-inner-box-block">
                                                            <div class="add-property-title border-bottom pb-25 mb-25">
                                                                <h4>{{ __('Pricing details') }}</h4>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-md-12 mb-25">
                                                                    <label
                                                                        class="label-text-title color-heading font-medium mb-2">{{ __('Pricing details') }}</label>
                                                                    <input type="number" name="price"
                                                                        class="form-control"
                                                                        placeholder="{{ __('Price') }}">
                                                                </div>
                                                            </div>
                                                            
                                                        </div>
                                                    </div>
                                                    
                                                    <div
                                                        class="add-property-inner-box bg-white theme-border radius-4 p-20 pb-0">
                                                        <div class="tenants-inner-box-block">
                                                            <div class="add-property-title border-bottom pb-25 mb-25">
                                                                <h4>{{ __('Product image') }}</h4>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-md-12 mb-25">
                                                                    <label
                                                                        class="label-text-title color-heading font-medium mb-2">{{ __('Product image') }}
                                                                        <span class="text-danger">*</span></label>
                                                                    <input type="file" name="images[]"
                                                                        class="form-control" multiple required>
                                                                </div>
                                                            </div>
                                                    </div>
                                                </div>
                                                </fieldset>
                                                <!-- Next/Previous Button Start -->
                                                <input type="submit" class="action-button theme-btn mt-25"
                                                    value="Add Product/Service">
                                            </form>
                                        </fieldset>
                                    </div>
                                </div>
                                <!-- Stepper End -->

                            </div>
                        </div>
                        <!-- Add Tenants Stepper Area End -->

                    </div>
                    <!-- Add Tenants Area row End -->

                </div>
                <!-- Page Content Wrapper End -->

            </div>

        </div>
        <!-- End Page-content -->

    </div>
    <input type="hidden" id="getStateListRoute" value="{{ route('owner.location.state.list') }}">
    <input type="hidden" id="getCityListRoute" value="{{ route('owner.location.city.list') }}">
    <input type="hidden" id="propertyShowRoute" value="{{ route('owner.property.show', 0) }}">
    <input type="hidden" id="tenantStoreRoute" value="{{ route('owner.tenant.store') }}">
    <input type="hidden" id="tenantListRoute" value="{{ route('owner.tenant.index') }}">
    <input type="hidden" id="getPropertyWithUnitsByIdRoute"
        value="{{ route('owner.property.getPropertyWithUnitsById') }}">
@endsection

@push('script')
    <script src="{{ asset('/') }}assets/js/pages/profile-setting.init.js"></script>
    <script src="{{ asset('assets/js/custom/tenant.js') }}"></script>
@endpush
