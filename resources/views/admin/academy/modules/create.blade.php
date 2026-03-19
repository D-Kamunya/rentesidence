@extends('admin.layouts.app')

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
                                    <h3 class="mb-sm-0"> Centresidence Academy</h3>
                                </div>
                                <div class="page-title-right">
                                    <ol class="breadcrumb mb-0">
                                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"
                                                title="{{ __('Dashboard') }}">{{ __('Dashboard') }}</a></li>
                                        <li class="breadcrumb-item active" aria-current="page">Centresidence Academy</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- end page title -->
                    <h4>Create Module</h4>
                    <hr>

                    <form method="POST" action="{{ route('admin.academy.store') }}">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">Title</label>
                            <input type="text"
                                name="title"
                                class="form-control"
                                required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">YouTube Training Video</label>
                            <input type="text"
                                name="youtube_url"
                                class="form-control"
                                placeholder="Paste YouTube link">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Training Duration (Minutes)</label>
                            <input type="number"
                                name="duration_minutes"
                                class="form-control"
                                placeholder="Example: 12">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Content</label>
                            <textarea name="content"
                                    rows="5"
                                    class="form-control"
                                    required></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Module Order</label>
                            <input type="number"
                                name="module_order"
                                class="form-control"
                                required>
                        </div>

                        <div class="form-check mb-3">
                            <input type="checkbox"
                                name="is_active"
                                value="1"
                                class="form-check-input"
                                id="isActive">
                            <label class="form-check-label" for="isActive">
                                Active
                            </label>
                        </div>

                        <button class="theme-btn w-auto">
                            Save Module
                        </button>

                        <a href="{{ route('admin.academy.index') }}"
                        class="theme-btn-red">
                            Cancel
                        </a>

                    </form>

                </div>
            </div>
        </div>
    </div>
@endsection