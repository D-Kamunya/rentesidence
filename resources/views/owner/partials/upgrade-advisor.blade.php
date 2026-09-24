{{-- Owner Upgrade Advisor — the top personalised suggestion, computed live from the owner's
     state (plan, units, rent, infra) and dismissible server-side (not localStorage). Replaces the
     old static financing banner; the financing nudge is now one targeted category among several. --}}
@php
    $csAdvisorTop = app(\App\Services\OwnerAdvisor\OwnerAdvisorService::class)->topFor((int) auth()->id(), 1);
    $csAdvisor = $csAdvisorTop[0] ?? null;
    $csAdvisorIco = [
        'upgrade'   => 'ri-arrow-up-circle-line',
        'financing' => 'ri-funds-line',
        'cap'       => 'ri-bar-chart-box-line',
        'sms'       => 'ri-message-2-line',
    ][$csAdvisor->category ?? ''] ?? 'ri-lightbulb-flash-line';
@endphp
@if ($csAdvisor)
    <div class="cs-fin-nudge" id="csAdvisorNudge" data-key="{{ $csAdvisor->key }}">
        <a href="{{ $csAdvisor->ctaUrl }}" class="cs-fin-nudge__link">
            <span class="cs-fin-nudge__ico"><i class="{{ $csAdvisorIco }}"></i></span>
            <span class="cs-fin-nudge__txt">
                <span class="cs-fin-nudge__title">{{ $csAdvisor->title }}</span>
                <span class="cs-fin-nudge__sub">{{ $csAdvisor->body }}</span>
            </span>
            <span class="cs-fin-nudge__cta">{{ $csAdvisor->ctaLabel }}</span>
        </a>
        <button type="button" class="cs-fin-nudge__x" aria-label="{{ __('Dismiss') }}" onclick="csAdvisorDismiss()">&times;</button>
    </div>
    <script>
        (function () {
            var el = document.getElementById('csAdvisorNudge');
            if (!el) return;
            window.csAdvisorDismiss = function () {
                el.hidden = true;
                el.style.display = 'none';
                try {
                    fetch('{{ route('owner.advisor.dismiss') }}', {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/x-www-form-urlencoded' },
                        credentials: 'same-origin',
                        body: 'key=' + encodeURIComponent(el.getAttribute('data-key'))
                    });
                } catch (e) {}
            };
        })();
    </script>
@endif
