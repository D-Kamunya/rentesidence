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
    {{-- Advisor card styles live HERE (self-contained) so they can never be stripped from the
         dashboard by accident. The .cs-fin-nudge* class names are kept for historical reasons —
         this card replaced the old static financing banner and reuses its styling. --}}
    <style>
        .cs-fin-nudge { position:relative; margin-bottom:22px; }
        .cs-fin-nudge__link {
            display:flex; align-items:center; gap:14px; flex-wrap:wrap; text-decoration:none;
            background:linear-gradient(90deg,#E6F1FB,#E1F5EE); border:1px solid #B5D4F4;
            border-radius:14px; padding:16px 46px 16px 20px;
        }
        .cs-fin-nudge__ico {
            width:42px; height:42px; border-radius:12px; background:#185FA5; flex:none;
            display:flex; align-items:center; justify-content:center; font-size:22px;
        }
        .cs-fin-nudge__ico i { color:#fff; }
        .cs-fin-nudge__txt { flex:1 1 240px; min-width:0; display:flex; flex-direction:column; gap:3px; }
        .cs-fin-nudge__title { font-weight:700; color:#111827; }
        .cs-fin-nudge__sub { font-size:13px; color:#374151; line-height:1.45; }
        .cs-fin-nudge__cta {
            flex:none; display:inline-flex; align-items:center; gap:6px; background:#185FA5;
            color:#fff; font-size:12px; font-weight:500; padding:8px 16px; border-radius:7px;
            white-space:nowrap; transition:background .13s;
        }
        .cs-fin-nudge__link:hover .cs-fin-nudge__cta { background:#0F4A84; }
        .cs-fin-nudge__x {
            position:absolute; top:8px; right:10px; border:none; background:transparent;
            color:#6b7280; font-size:22px; line-height:1; cursor:pointer; padding:2px 7px;
            border-radius:6px; transition:all .13s;
        }
        .cs-fin-nudge__x:hover { color:#111827; background:rgba(0,0,0,.06); }
        .cs-fin-nudge__learn {
            display:inline-flex; align-items:center; gap:5px; margin:9px 0 0 20px;
            font-size:12.5px; font-weight:500; color:#185FA5; text-decoration:none;
        }
        .cs-fin-nudge__learn:hover { text-decoration:underline; }
        .cs-fin-nudge__learn i { font-size:14px; }
        @media (max-width:600px) {
            .cs-fin-nudge__link { padding:14px 40px 14px 14px; gap:12px; }
            .cs-fin-nudge__txt { flex:1 1 calc(100% - 54px); }
            .cs-fin-nudge__cta { flex:1 1 100%; justify-content:center; }
        }
    </style>
    <div class="cs-fin-nudge" id="csAdvisorNudge" data-key="{{ $csAdvisor->key }}">
        <a href="{{ $csAdvisor->ctaUrl }}" class="cs-fin-nudge__link">
            <span class="cs-fin-nudge__ico"><i class="{{ $csAdvisorIco }}"></i></span>
            <span class="cs-fin-nudge__txt">
                <span class="cs-fin-nudge__title">{{ $csAdvisor->title }}</span>
                <span class="cs-fin-nudge__sub">{{ $csAdvisor->body }}</span>
            </span>
            <span class="cs-fin-nudge__cta">{{ $csAdvisor->ctaLabel }}</span>
        </a>
        @if ($csAdvisor->learnUrl && $csAdvisor->learnLabel)
            <a href="{{ $csAdvisor->learnUrl }}" class="cs-fin-nudge__learn btn-learn">
                <i class="ri-book-open-line"></i> {{ $csAdvisor->learnLabel }}
            </a>
        @endif
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
