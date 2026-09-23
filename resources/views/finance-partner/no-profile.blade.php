@extends('finance-partner.layouts.app')

@section('content')
    <div class="cs-card">
        <div class="cs-card__body" style="text-align:center;padding:40px;">
            <i class="ri-bank-line" style="font-size:42px;color:var(--blue);"></i>
            <h2 class="cs-card__title" style="margin-top:14px;">{{ __('No partner profile linked') }}</h2>
            <p class="cs-muted">{{ __('Your account is not yet linked to a finance partner profile. Please contact the platform administrator.') }}</p>
        </div>
    </div>
@endsection
