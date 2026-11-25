{{-- Reusable "this is covered on a paid plan" upsell, shown at free-tier friction points
     (screening/agreement caps, premium-rate SMS). The CALLER decides when to show it (it already
     knows the owner's coverage), so this partial is purely presentational.
     Params: $uTitle, $uBody; optional $uUrgent (bool → amber, used when a limit is already hit),
     optional $uCta (button label). --}}
@php
    $uUrgent = $uUrgent ?? false;
    $uCta    = $uCta ?? __('See plans');
    $uUrl    = \Illuminate\Support\Facades\Route::has('owner.subscription.index')
        ? route('owner.subscription.index', ['current_plan' => 'no'])
        : url('/owner/subscription');
@endphp
<a href="{{ $uUrl }}" class="cs-upsell{{ $uUrgent ? ' cs-upsell--urgent' : '' }}">
    <span class="cs-upsell__ico" aria-hidden="true">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M12 3l2.5 5 5.5.8-4 3.9.9 5.5L12 16.5 7.1 18.2l.9-5.5-4-3.9L9.5 8 12 3z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/></svg>
    </span>
    <span class="cs-upsell__txt">
        <strong>{{ $uTitle }}</strong>
        <span>{{ $uBody }}</span>
    </span>
    <span class="cs-upsell__cta">{{ $uCta }}
        <svg width="14" height="14" viewBox="0 0 16 16" fill="none"><path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
    </span>
</a>
@once
<style>
    .cs-upsell{display:flex;align-items:center;gap:13px;text-decoration:none;margin-bottom:16px;
        background:linear-gradient(90deg,#EAF2FB,#E6F4EE);border:1px solid #BFD9F2;border-radius:13px;
        padding:13px 16px;transition:transform .14s,box-shadow .14s;}
    .cs-upsell:hover{transform:translateY(-1px);box-shadow:0 10px 22px -14px rgba(24,95,165,.55);text-decoration:none;}
    .cs-upsell--urgent{background:linear-gradient(90deg,#FBF2E2,#FBECEA);border-color:#F0C68A;}
    .cs-upsell__ico{flex:none;width:34px;height:34px;border-radius:10px;background:#185FA5;color:#fff;
        display:grid;place-items:center;}
    .cs-upsell--urgent .cs-upsell__ico{background:#B5740F;}
    .cs-upsell__txt{flex:1;min-width:0;display:flex;flex-direction:column;gap:2px;}
    .cs-upsell__txt strong{color:#1b1e22;font-size:14px;font-weight:700;line-height:1.3;}
    .cs-upsell__txt span{color:#4a4f57;font-size:12.8px;line-height:1.45;}
    .cs-upsell__cta{flex:none;display:inline-flex;align-items:center;gap:5px;color:#185FA5;font-weight:700;
        font-size:13px;white-space:nowrap;}
    .cs-upsell--urgent .cs-upsell__cta{color:#B5740F;}
    @media(max-width:560px){
        .cs-upsell{flex-wrap:wrap;}
        .cs-upsell__cta{width:100%;justify-content:flex-end;}
    }
</style>
@endonce
