@extends('owner.layouts.app')

@section('content')
<div class="main-content"><div class="page-content"><div class="container-fluid">
    <div class="page-content-wrapper bg-white p-30 radius-20">
        @include('centresidence._design')
        <div class="cs-titlebar"><h1 class="cs-title">{{ __('Switch to transaction mode') }}</h1></div>

        <div class="cs-card" style="max-width:680px;">
            <div class="cs-card__head"><h2 class="cs-card__title">{{ __('Financing needs transaction pricing mode') }}</h2></div>
            <div class="cs-card__body">
                <p style="font-size:13.5px;color:var(--gray-700);">
                    {{ __('To finance') }} <strong>{{ optional($product->module)->name }}</strong>
                    {{ __('with') }} <strong>{{ optional($product->partner)->company_name }}</strong>,
                    {{ __('your rent must route through the platform so repayments can be deducted automatically. On transaction mode, a 1% fee applies to rent and marketplace payments; token purchases are exempt.') }}
                </p>
                <p class="cs-muted">
                    {{ __('Note: while you have an active facility, you cannot switch back to subscription/free mode until it is fully repaid.') }}
                </p>
                <form method="POST" action="{{ route('owner.financing.switch-mode') }}" style="margin-top:14px;">
                    @csrf
                    <input type="hidden" name="partner_module_id" value="{{ $product->id }}">
                    <button type="submit" class="cs-btn cs-btn--primary">{{ __('Switch to transaction mode & continue') }}</button>
                    <a href="{{ route('owner.financing.index') }}" class="cs-btn cs-btn--ghost">{{ __('Cancel') }}</a>
                </form>
            </div>
        </div>
    </div>
</div></div></div>
@endsection
