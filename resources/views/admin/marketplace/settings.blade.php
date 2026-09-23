@extends('admin.layouts.app')

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="page-content-wrapper bg-white p-30 radius-20">
                @include('centresidence._design')

                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between border-bottom mb-20">
                            <div class="page-title-left">
                                <h3 class="mb-sm-0">{{ __('Settings') }}</h3>
                            </div>
                            <div class="page-title-right">
                                <ol class="breadcrumb mb-0">
                                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" title="{{ __('Dashboard') }}">{{ __('Dashboard') }}</a></li>
                                    <li class="breadcrumb-item"><a href="#" title="{{ __('Settings') }}">{{ __('Settings') }}</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">{{ $pageTitle }}</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="settings-page-layout-wrap position-relative">
                    <div class="row">
                        @include('admin.setting.sidebar')

                        <div class="col-md-12 col-lg-12 col-xl-8 col-xxl-9">
                            <div class="mb-4">
                                <h2 style="font-size:20px;font-weight:600;color:#111827;margin:0 0 4px;">{{ $pageTitle }}</h2>
                                <p style="font-size:13.5px;color:#6b7280;margin:0;">{{ __('How long the platform holds a paid order before releasing the seller their money (escrow timing).') }}</p>
                            </div>

                            <div style="background:#F0F6FC;border:0.5px solid #cfe0f2;border-radius:12px;padding:14px 16px;max-width:640px;margin-bottom:18px;">
                                <p style="font-size:12.5px;color:#0C447C;margin:0;line-height:1.55;">
                                    {{ __('When a buyer pays, we hold the proceeds. The seller is only paid once it is safe to release. These two values control that timing — they are separate on purpose.') }}
                                </p>
                            </div>

                            <form action="{{ route('admin.marketplace.settings.update') }}" method="POST"
                                  style="background:#fff;border:0.5px solid #e5e7eb;border-radius:12px;padding:22px;max-width:640px;">
                                @csrf
                                @method('PUT')

                                <div style="margin-bottom:22px;">
                                    <label style="display:block;font-size:12px;font-weight:500;color:#374151;margin-bottom:6px;">{{ __('Return / settlement window after delivery (days)') }}</label>
                                    <input type="number" name="marketplace_return_window_days" min="0" max="60" value="{{ $returnWindowDays }}" required
                                           style="width:180px;border:0.5px solid #e5e7eb;border-radius:9px;padding:10px 12px;font-size:14px;outline:none;">
                                    <p style="font-size:11.5px;color:#9ca3af;margin:6px 0 0;line-height:1.55;">
                                        {{ __('Once an order is marked delivered, this is BOTH how long the buyer can still cancel or return AND how long we hold before paying the seller. We release exactly when the buyer can no longer pull out. A buyer who taps "Confirm receipt" releases it immediately. Keep it short — our sellers and buyers are usually close by.') }}
                                    </p>
                                </div>

                                <div style="margin-bottom:22px;">
                                    <label style="display:block;font-size:12px;font-weight:500;color:#374151;margin-bottom:6px;">{{ __('Auto-release grace for undelivered orders (days)') }}</label>
                                    <input type="number" name="marketplace_auto_release_days" min="1" max="120" value="{{ $autoReleaseDays }}" required
                                           style="width:180px;border:0.5px solid #e5e7eb;border-radius:9px;padding:10px 12px;font-size:14px;outline:none;">
                                    <p style="font-size:11.5px;color:#9ca3af;margin:6px 0 0;line-height:1.55;">
                                        {{ __('Safety net only: if an order is paid but never marked delivered, we release the seller their money after this many days so it never gets stuck in escrow. Keep this LONGER than the window above — releasing an undelivered order early is the risky direction. Give far-away sellers enough room to fulfil.') }}
                                    </p>
                                </div>

                                <button type="submit"
                                        style="background:#185FA5;color:#fff;border:none;border-radius:9px;font-size:13.5px;font-weight:500;padding:10px 22px;cursor:pointer;">
                                    {{ __('Save Settings') }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
