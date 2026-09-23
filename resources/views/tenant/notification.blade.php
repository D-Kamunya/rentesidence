@extends('tenant.layouts.app')

@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="page-content-wrapper bg-white p-30 radius-20">
                    @include('centresidence._design')

                    <div class="cs-titlebar">
                        <div>
                            <h1 class="cs-title">{{ $pageTitle }}</h1>
                            <ol class="cs-crumb"><li><a href="{{ route('tenant.dashboard') }}">{{ __('Dashboard') }}</a></li><li>›</li><li>{{ $pageTitle }}</li></ol>
                        </div>
                    </div>

                    <div class="cs-card"><div class="cs-card__body">
                        <table id="allDataTable" class="table dt-responsive" style="width:100%;">
                            <thead>
                                <tr>
                                    <th>{{ __('SL') }}</th>
                                    <th>{{ __('Image') }}</th>
                                    <th>{{ __('Name') }}</th>
                                    <th>{{ __('Title') }}</th>
                                    <th>{{ __('Body') }}</th>
                                    <th>{{ __('Time') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach (getNotification(auth()->id()) as $notification)
                                    @php
                                        $url = $notification->url ?? route('tenant.notification');
                                    @endphp
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td><img src="{{ getFileUrl($notification->folder_name, $notification->file_name) }}"
                                                class="me-3 rounded-circle avatar-xs" alt="user-pic"></td>
                                        <td>{{ $notification->first_name }} {{ $notification->last_name }}</td>
                                        <td>{{ $notification->title }}. <a href="{{ $url }}" target="_blank" class="notification-item">{{ __('Click here to view') }}</a></td>
                                        <td>{{ $notification->body }}</td>
                                        <td>{{ $notification->created_at->diffForHumans() }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div></div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('style')
    @include('common.layouts.datatable-style')
@endpush

@push('script')
    @include('common.layouts.datatable-script')
    <script src="{{ asset('assets/js/pages/alldatatables.init.js') }}"></script>
@endpush
