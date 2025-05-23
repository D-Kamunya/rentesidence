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
                                    <h3 class="mb-sm-0">Tenant Applications</h3>
                                </div>
                                <div class="page-title-right">
                                    <ol class="breadcrumb mb-0">
                                        <li class="breadcrumb-item"><a href="{{ route('owner.dashboard') }}"
                                                title="{{ __('Dashboard') }}">{{ __('Dashboard') }}</a></li>
                                        <li class="breadcrumb-item"><a href="{{ route('owner.tenant.index') }}"
                                                title="Home">{{ __('Tenants') }}</a></li>
                                        <li class="breadcrumb-item">{{ __('Tenant Applications') }}</a></li>
                                    </ol>
                                </div>

                            </div>
                        </div>
                    </div>
                    <!-- end page title -->
                     <!-- Tenants Details Layout Wrap Area row Start -->
                    <div class="container py-4">
                        @php
                            // Example mocked applications
                            $applications = [
                                [
                                    'first_name' => 'John',
                                    'last_name' => 'Doe',
                                    'phone' => '0712345678',
                                    'email' => 'john.doe@example.com',
                                    'job' => 'Software Engineer',
                                    'age' => 30,
                                    'address' => '123 Mombasa Road',
                                    'country' => 'Kenya',
                                    'state' => 'Nairobi',
                                    'city' => 'Nairobi',
                                    'zip_code' => '00100',
                                    'property_name' => 'Green Estate',
                                    'unit_name' => 'Unit A-1',
                                    'rent' => 35000,
                                    'unit_id' => 1,
                                ],
                                [
                                    'first_name' => 'Jane',
                                    'last_name' => 'Kamau',
                                    'phone' => '0701122334',
                                    'email' => 'jane.kamau@example.com',
                                    'job' => 'Accountant',
                                    'age' => 28,
                                    'address' => '456 Westlands Avenue',
                                    'country' => 'Kenya',
                                    'state' => 'Nairobi',
                                    'city' => 'Westlands',
                                    'zip_code' => '00200',
                                    'property_name' => 'Sunset Apartments',
                                    'unit_name' => 'Unit B-3',
                                    'rent' => 42000,
                                    'unit_id' => 2,
                                ]
                            ];
                        @endphp

                        <div class="row g-4">
                            @foreach ($applications as $app)
                                <div class="col-md-6">
                                    <div class="card shadow-sm rounded">
                                        <div class="card-body">
                                            <h5 class="card-title fw-bold text-primary">{{ $app['first_name'] }} {{ $app['last_name'] }}</h5>
                                            <p class="mb-1"><strong>Phone:</strong> {{ $app['phone'] }}</p>
                                            <p class="mb-1"><strong>Email:</strong> {{ $app['email'] }}</p>
                                            <p class="mb-1"><strong>Job:</strong> {{ $app['job'] }}</p>
                                            <p class="mb-1"><strong>Age:</strong> {{ $app['age'] }}</p>
                                            <hr>
                                            <p class="mb-1"><strong>Property:</strong> {{ $app['property_name'] }}</p>
                                            <p class="mb-1"><strong>Unit:</strong> {{ $app['unit_name'] }}</p>
                                            <p class="mb-1"><strong>Rent:</strong> KES {{ number_format($app['rent']) }} / Month</p>
                                            <hr>
                                            <p class="mb-1"><strong>Address:</strong> {{ $app['address'] }}</p>
                                            <p class="mb-1"><strong>City:</strong> {{ $app['city'] }}, {{ $app['state'] }}, {{ $app['country'] }}</p>
                                            <p class="mb-3"><strong>Zip Code:</strong> {{ $app['zip_code'] }}</p>
                                            
                                            <button class="btn mt-2 py-3 rounded theme-btn w-100">Assign Tenant</button>
                                            <button class="btn btn-danger mt-2 py-3 rounded w-100">Delete Application</button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <!-- Page Content Wrapper End -->
            </div>
        </div>
        <!-- End Page-content -->
    </div>
@endsection


