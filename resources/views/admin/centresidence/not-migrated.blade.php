@extends('admin.layouts.app')

@section('content')
<div class="main-content"><div class="page-content"><div class="container-fluid">
    <div class="page-content-wrapper bg-white p-30 radius-20">
        @include('centresidence._design')
        <div class="cs-titlebar"><h1 class="cs-title">{{ __('Centresidence') }}</h1></div>
        <div class="cs-card">
            <div class="cs-card__body" style="text-align:center;padding:40px;">
                <i class="ri-database-2-line" style="font-size:42px;color:var(--blue);"></i>
                <h2 class="cs-card__title" style="margin-top:14px;font-size:16px;">{{ __('Centresidence is not yet migrated') }}</h2>
                <p class="cs-muted">{{ __('Run the database migrations to activate the Infrastructure & Finance OS, then refresh this page.') }}</p>
                <code style="background:var(--gray-100);padding:4px 10px;border-radius:6px;">php artisan migrate</code>
            </div>
        </div>
    </div>
</div></div></div>
@endsection
