
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
                                    <h3 class="mb-sm-0">Edit products/Services</h3>
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
                                            <form action="{{ route('owner.products.update', $product->id) }}" method="POST" enctype="multipart/form-data">
                                                @csrf
                                                @method('PUT')
                                                <div
                                                    class="form-card add-property-box bg-off-white theme-border radius-4 p-20">
                                                    <div class="add-property-title border-bottom pb-25 mb-25">
                                                        <h4>{{ __('Edit') }} {{ $product->name }}</h4>
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
                                                                    <input type="text" name="name" value="{{ $product->name }}"
                                                                        class="form-control" role="alert">
                                                                </div>
                                                            </div>

                                                            <div class="row">
                                                                <div class="col-md-12 mb-25">
                                                                        <label
                                                                        class="label-text-title color-heading font-medium mb-2">{{ __('Product description') }}
                                                                        <span class="text-danger">*</span></label>
                                                                        <textarea name="description" name="description" class="form-control" 
                                                                         role="alert"> {{ $product->description }}                                                                 
                                                                        </textarea>
                                                                </div>
                                                            </div>

                                                            <div class="row">
                                                                <div class="col-md-12 mb-25">
                                                                    <label
                                                                        class="label-text-title color-heading font-medium mb-2">{{ __('Type') }}
                                                                        <span class="text-danger">*</span></label>
                                                                    <select name="type" name="type"
                                                                         class="form-control">
                                                                        <option value="product" {{ $product->type == 'product' ? 'selected' : '' }}>Product</option>
                                                                        <option value="service" {{ $product->type == 'service' ? 'selected' : '' }}>Service</option>
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
                                                                        <option value="foods" {{ $product->category == 'foods' ? 'selected' : '' }}>Foods</option>
                                                                        <option value="gas" {{ $product->category == 'gas' ? 'selected' : '' }}>Gas</option>
                                                                        <option value="water" {{ $product->category == 'water' ? 'selected' : '' }}>Water</option>
                                                                        <option value="clothes" {{ $product->category == 'clothes' ? 'selected' : '' }}>Clothes</option>
                                                                        <option value="shoes" {{ $product->category == 'shoes' ? 'selected' : '' }}>Shoes</option>
                                                                        <option value="bags" {{ $product->category == 'bags' ? 'selected' : '' }}>Bags</option>
                                                                        <option value="electronics" {{ $product->category == 'electronics' ? 'selected' : '' }}>Electronics</option>
                                                                        <option value="beauty products" {{ $product->category == 'beauty products' ? 'selected' : '' }}>Beauty products</option>
                                                                        <option value="kitchenware" {{ $product->category == 'kitchenware' ? 'selected' : '' }}>Kitchenware</option>
                                                                        <option value="detergents" {{ $product->category == 'detergents' ? 'selected' : '' }}>Detergents</option>
                                                                        <option value="furniture" {{ $product->category == 'furniture' ? 'selected' : '' }}>Furniture</option>
                                                                        <option value="sanitation" {{ $product->category == 'sanitation' ? 'selected' : '' }}>Sanitation</option>
                                                                        <option value="toiletries" {{ $product->category == 'toiletries' ? 'selected' : '' }}>Toiletries</option>
                                                                        <option value="hygiene" {{ $product->category == 'hygiene' ? 'selected' : '' }}>Hygiene</option>
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
                                                                    <input type="number" name="price" value="{{ $product->price }}"
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

                                                                <!-- Display current images -->
                                                                <div class="form-group">
                                                                    <label for="current_images">Current Images</label>
                                                                    <div class="current-images-grid">
                                                                        @foreach(json_decode($product->images) as $image)
                                                                            <div class="image-container">
                                                                                <img src="{{ asset('storage/' . $image) }}" alt="Product Image" class="current-image">
                                                                                <label class="delete-label">
                                                                                    <input type="checkbox" name="delete_images[]" value="{{ $image }}"> Delete
                                                                                </label>
                                                                            </div>
                                                                        @endforeach
                                                                    </div>
                                                                </div>
                                                                    <!-- Option to upload new images (commented because of an error to be sorted out later)--> 
                                                                <div class="form-group">
                                                                    <label for="images">Add New Images</label>
                                                                    <input type="file" name="images[]" id="images" class="form-control" multiple>
                                                                    <div id="image-preview" class="image-preview-container"></div>
                                                                </div>
                                                            </div>
                                                    </div>
                                                </div>
                                                </fieldset>
                                                <!-- Next/Previous Button Start -->
                                                <input type="submit" class="action-button theme-btn mt-25"
                                                    value="Update {{ $product->name }}">
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
    <script src="{{ asset('assets/js/custom/owner-product.js') }}"></script>
@endpush
@push('style')
    <link rel="stylesheet" href="{{ asset('assets/css/product.css') }}">
@endpush
