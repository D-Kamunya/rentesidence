{{-- Owner setup checklist — a live "get set up to collect rent" tracker for new owners. Each step's
     done state is computed from real data; auto-hides once complete, or when the owner dismisses it. --}}
@php
    $csSetup = app(\App\Services\OwnerAdvisor\OwnerSetupService::class)->checklist((int) auth()->id());
    $csSetupDismissed = app(\App\Services\OnboardingTourService::class)->completed((int) auth()->id(), 'owner_setup');
    $csSetupShow = ! $csSetup['complete'] && ! $csSetupDismissed && $csSetup['total'] > 0;
    $csSetupPct = $csSetup['total'] ? round($csSetup['done'] / $csSetup['total'] * 100) : 0;
@endphp
@if ($csSetupShow)
<div class="cs-setup" id="csSetup">
    <button type="button" class="cs-setup__x" aria-label="{{ __('Dismiss') }}" onclick="csSetupDismiss()">&times;</button>
    <div class="cs-setup__head">
        <div>
            <div class="cs-setup__eyebrow">{{ __('Get set up') }}</div>
            <h3 class="cs-setup__title">{{ __('A few steps to start collecting rent') }}</h3>
        </div>
        <div class="cs-setup__count">{{ $csSetup['done'] }}/{{ $csSetup['total'] }}</div>
    </div>
    <div class="cs-setup__bar"><span style="width:{{ $csSetupPct }}%"></span></div>
    <ul class="cs-setup__steps">
        @foreach ($csSetup['steps'] as $step)
            <li class="cs-setup__step {{ $step['done'] ? 'is-done' : '' }}">
                <span class="cs-setup__tick">
                    @if ($step['done'])
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none"><path d="M20 6L9 17l-5-5" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    @endif
                </span>
                <span class="cs-setup__label">{{ $step['label'] }}</span>
                @unless ($step['done'])
                    <a href="{{ $step['cta_url'] }}" class="cs-setup__cta">{{ $step['cta_label'] }} ›</a>
                @endunless
            </li>
        @endforeach
    </ul>
</div>
<style>
    .cs-setup{position:relative;background:#fff;border:1px solid #E6E1D8;border-radius:16px;padding:20px 22px;margin-bottom:20px;box-shadow:0 1px 3px rgba(16,24,40,.04);}
    .cs-setup__x{position:absolute;top:12px;right:14px;background:none;border:none;color:#c3c6cb;font-size:20px;line-height:1;cursor:pointer;}
    .cs-setup__x:hover{color:#6b7280;}
    .cs-setup__head{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;margin-bottom:12px;}
    .cs-setup__eyebrow{font-size:11px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:#185FA5;margin-bottom:3px;}
    .cs-setup__title{font-size:16.5px;font-weight:800;color:#1b1e22;margin:0;letter-spacing:-.01em;}
    .cs-setup__count{font-family:ui-monospace,Menlo,monospace;font-size:13px;font-weight:700;color:#6b7280;background:#f3f4f6;border-radius:999px;padding:4px 11px;white-space:nowrap;}
    .cs-setup__bar{height:6px;background:#eef0f3;border-radius:999px;overflow:hidden;margin-bottom:16px;}
    .cs-setup__bar span{display:block;height:100%;background:linear-gradient(90deg,#185FA5,#3B82C4);border-radius:999px;transition:width .4s;}
    .cs-setup__steps{list-style:none;margin:0;padding:0;display:flex;flex-direction:column;gap:10px;}
    .cs-setup__step{display:flex;align-items:center;gap:11px;font-size:14px;color:#1b1e22;}
    .cs-setup__tick{flex:none;width:22px;height:22px;border-radius:50%;border:1.6px solid #d7dbe0;display:grid;place-items:center;color:#fff;}
    .cs-setup__step.is-done .cs-setup__tick{background:#0F6E56;border-color:#0F6E56;}
    .cs-setup__step.is-done .cs-setup__label{color:#8a9099;text-decoration:line-through;}
    .cs-setup__label{flex:1;}
    .cs-setup__cta{flex:none;font-size:13px;font-weight:700;color:#185FA5;text-decoration:none;white-space:nowrap;}
    .cs-setup__cta:hover{color:#0F4A84;}
</style>
<script>
    (function () {
        var el = document.getElementById('csSetup');
        if (!el) return;
        window.csSetupDismiss = function () {
            el.style.display = 'none';
            try {
                fetch('{{ route('tour.complete') }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/x-www-form-urlencoded' },
                    credentials: 'same-origin', body: 'key=owner_setup'
                });
            } catch (e) {}
        };
    })();
</script>
@endif
