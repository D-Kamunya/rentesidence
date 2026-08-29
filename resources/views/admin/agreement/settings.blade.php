@extends('admin.layouts.app')

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="page-content-wrapper bg-white p-30 radius-20">
                <div class="container" style="max-width:640px;">

                    <div class="mb-4">
                        <h2 style="font-size:22px;font-weight:500;color:#111827;margin:0 0 4px;">{{ $pageTitle }}</h2>
                        <p style="font-size:13.5px;color:#6b7280;margin:0;">{{ __('Monetization for the e-signature agreements feature.') }}</p>
                    </div>

                    <form action="{{ route('admin.agreement.settings.update') }}" method="POST"
                          style="background:#fff;border:0.5px solid #e5e7eb;border-radius:12px;padding:22px;">
                        @csrf
                        @method('PUT')

                        <div style="margin-bottom:20px;">
                            <label style="display:block;font-size:12px;font-weight:500;color:#374151;margin-bottom:6px;">{{ __('Free agreements per month (free plan)') }}</label>
                            <input type="number" name="agreement_free_quota" min="0" max="1000" value="{{ $freeQuota }}" required
                                   style="width:180px;border:0.5px solid #e5e7eb;border-radius:9px;padding:10px 12px;font-size:14px;outline:none;">
                            <p style="font-size:11.5px;color:#9ca3af;margin:6px 0 0;">{{ __('How many agreements a free-plan owner can send each month at no cost. Resets monthly.') }}</p>
                        </div>

                        <div style="margin-bottom:20px;">
                            <label style="display:block;font-size:12px;font-weight:500;color:#374151;margin-bottom:6px;">{{ __('Price per agreement credit') }} ({{ getCurrencySymbol() }})</label>
                            <input type="number" name="agreement_price" min="0" step="any" value="{{ $price }}" required
                                   style="width:180px;border:0.5px solid #e5e7eb;border-radius:9px;padding:10px 12px;font-size:14px;outline:none;">
                            <p style="font-size:11.5px;color:#9ca3af;margin:6px 0 0;">{{ __('What owners pay per credit (one credit = one agreement) once the free quota is used. Subscription & transaction plans are unlimited.') }}</p>
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
@endsection
