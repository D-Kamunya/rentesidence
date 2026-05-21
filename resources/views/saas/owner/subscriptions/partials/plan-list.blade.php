{{--
    plan-list.blade.php  — choosePlanModal inner content
    JS hooks preserved: #subscribeBtn, .payment-gateway-active, .current-plan-button,
    #monthly-yearly-button, .price-monthly, .price-yearly, .plan_type, .quantity,
    .per_monthly_price, .per_yearly_price, data-handler="setPaymentModal"
--}}

<style>
/* ── Modal shell ───────────────────────────────────────────── */
.cpm-wrap {
    font-size: 13px;
    color: var(--gray-700, #374151);
}

/* ── Billing toggle ─────────────────────────────────────────── */
.cpm-toggle-bar {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    padding: 16px 24px 0;
    font-size: 12px;
    font-weight: 500;
    color: var(--gray-500, #6b7280);
}

.cpm-toggle-label { user-select: none; }
.cpm-toggle-label.is-active { color: var(--gray-900, #111827); }

/* pill toggle */
.cpm-toggle-switch {
    position: relative;
    width: 40px;
    height: 22px;
    flex-shrink: 0;
}
.cpm-toggle-switch input { opacity: 0; width: 0; height: 0; }
.cpm-toggle-track {
    position: absolute; inset: 0;
    background: var(--gray-200, #e5e7eb);
    border-radius: 99px;
    cursor: pointer;
    transition: background .18s;
}
.cpm-toggle-track::after {
    content: '';
    position: absolute;
    left: 3px; top: 3px;
    width: 16px; height: 16px;
    border-radius: 50%;
    background: #fff;
    box-shadow: 0 1px 3px rgba(0,0,0,.18);
    transition: transform .18s;
}
.cpm-toggle-switch input:checked ~ .cpm-toggle-track { background: var(--blue, #185FA5); }
.cpm-toggle-switch input:checked ~ .cpm-toggle-track::after { transform: translateX(18px); }

.cpm-yearly-badge {
    display: inline-flex; align-items: center;
    font-size: 10px; font-weight: 500;
    padding: 2px 7px; border-radius: 99px;
    background: var(--green-light, #E1F5EE);
    color: var(--green-dark, #0F6E56);
    margin-left: 2px;
}

/* ── Section divider ─────────────────────────────────────────── */
.cpm-section {
    padding: 20px 24px 0;
}
.cpm-section:last-child { padding-bottom: 24px; }

.cpm-section-label {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 10px;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: .07em;
    color: var(--gray-400, #9ca3af);
    margin-bottom: 12px;
}
.cpm-section-label::after {
    content: '';
    flex: 1;
    height: 0.5px;
    background: var(--gray-200, #e5e7eb);
}

/* ── Tier descriptor (above the cards grid) ─────────────────── */
.cpm-tier-desc {
    font-size: 12px;
    color: var(--gray-500, #6b7280);
    margin-bottom: 14px;
    line-height: 1.55;
}

/* ── Recommended badge ──────────────────────────────────────── */
.cpm-recommended-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 10px;
    font-weight: 600;
    padding: 2px 8px;
    border-radius: 99px;
    background: var(--blue, #185FA5);
    color: #fff;
    margin-left: 8px;
    letter-spacing: 0.02em;
}

/* ── Plan card grid ──────────────────────────────────────────── */
.cpm-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 12px;
}

/* ── Individual plan card ───────────────────────────────────── */
.cpm-card {
    background: #fff;
    border: 0.5px solid var(--blue-faint, #185ea56e);
    border-radius: 12px;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    transition: all .22s ease;
    box-shadow:
        0 4px 12px rgba(0,0,0,.04),
        0 0 0 1px rgba(24,95,165,.05),
        0 6px 18px rgba(24,95,165,.06);
}
.cpm-card:hover {
    border-color: var(--blue, #185FA5);
    transform: translateY(-2px);
    box-shadow:
        0 10px 25px rgba(0,0,0,.06),
        0 0 0 1px rgba(24,95,165,.12),
        0 12px 30px rgba(24,95,165,.18);
}
.cpm-card.is-current {
    border-color: var(--green, #1D9E75);
    box-shadow:
        0 4px 12px rgba(0,0,0,.04),
        0 0 0 1px rgba(29,158,117,.12),
        0 6px 18px rgba(29,158,117,.1);
}
.cpm-card.is-current .cpm-recommended-badge {
    display: none;
}

/* card head */
.cpm-card-head {
    padding: 14px 16px 10px;
    border-bottom: 0.5px solid var(--gray-200, #e5e7eb);
    background: var(--gray-50, #fafafa);
}
.cpm-card-name {
    font-size: 13px;
    font-weight: 600;
    color: var(--gray-900, #111827);
    margin: 0 0 2px;
    display: flex;
    align-items: center;
    gap: 6px;
}
.cpm-card-desc {
    font-size: 11px;
    color: var(--gray-500, #6b7280);
    margin: 0;
    line-height: 1.4;
}

/* price row */
.cpm-card-price {
    padding: 12px 16px 0;
}
.cpm-price-amount {
    font-size: 20px;
    font-weight: 600;
    color: var(--gray-900, #111827);
    line-height: 1;
}
.cpm-price-per {
    font-size: 11px;
    font-weight: 400;
    color: var(--gray-500, #6b7280);
    margin-left: 3px;
}
.cpm-price-free {
    font-size: 20px;
    font-weight: 600;
    color: var(--green-dark, #0F6E56);
}

/* features list */
.cpm-features {
    padding: 10px 16px;
    flex: 1;
    list-style: none;
    margin: 0;
}
.cpm-features li {
    display: flex;
    align-items: flex-start;
    gap: 7px;
    font-size: 11px;
    color: var(--gray-700, #374151);
    padding: 4px 0;
    border-bottom: 0.5px solid var(--gray-100, #f3f4f6);
    line-height: 1.4;
}
.cpm-features li:last-child { border-bottom: none; }
.cpm-feat-icon { flex-shrink: 0; margin-top: 1px; color: var(--green, #1D9E75); }
.cpm-feat-icon--blue { color: var(--blue, #185FA5); }

/* card footer */
.cpm-card-foot {
    padding: 12px 16px;
    border-top: 0.5px solid var(--gray-200, #e5e7eb);
    background: var(--gray-50, #fafafa);
}

/* ── Buttons ─────────────────────────────────────────────────── */
.cpm-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    width: 100%;
    font-size: 12px;
    font-weight: 500;
    padding: 7px 14px;
    border-radius: 7px;
    border: none;
    cursor: pointer;
    transition: all .13s;
    text-decoration: none;
}
.cpm-btn--primary {
    background: var(--blue, #185FA5);
    color: #fff;
}
.cpm-btn--primary:hover { background: var(--blue-hover, #0F4A84); transform: translateY(-1px); }
.cpm-btn--current {
    background: var(--green-light, #E1F5EE);
    color: var(--green-dark, #0F6E56);
    border: 0.5px solid #A7DFC9;
    cursor: default;
}
.cpm-btn--ghost {
    background: var(--gray-100, #f3f4f6);
    color: var(--gray-700, #374151);
    border: 0.5px solid var(--gray-200, #e5e7eb);
}
.cpm-btn--ghost:hover { background: var(--gray-200, #e5e7eb); }

/* ── Free / Transaction single-row cards ────────────────────── */
.cpm-simple-card {
    background: #fff;
    border: 0.5px solid var(--blue-faint, #185ea56e);
    border-radius: 12px;
    padding: 16px 20px;
    display: flex;
    align-items: center;
    gap: 16px;
    justify-content: space-between;
    transition: all .22s ease;
    box-shadow:
        0 4px 12px rgba(0,0,0,.04),
        0 0 0 1px rgba(24,95,165,.05),
        0 6px 18px rgba(24,95,165,.06);
}
.cpm-simple-card:hover {
    border-color: var(--blue, #185FA5);
    transform: translateY(-2px);
}
.cpm-simple-card.is-current { 
    border-color: var(--green, #1D9E75); 
}
.cpm-simple-card.is-recommended {
    border-color: var(--blue, #185FA5);
    border-width: 1.5px;
    box-shadow:
        0 4px 12px rgba(0,0,0,.04),
        0 0 0 1px rgba(24,95,165,.15),
        0 8px 24px rgba(24,95,165,.12);
    position: relative;
}
.cpm-simple-card.is-recommended.is-current {
    border-color: var(--green, #1D9E75);
    border-width: 1.5px;
}
.cpm-simple-card.is-recommended::before {
    content: 'Recommended';
    position: absolute;
    top: -8px;
    right: 16px;
    font-size: 9px;
    font-weight: 600;
    padding: 2px 8px;
    border-radius: 99px;
    background: var(--blue, #185FA5);
    color: #fff;
    letter-spacing: 0.02em;
    z-index: 1;
}
.cpm-simple-card.is-recommended.is-current::before {
    display: none;
}

.cpm-simple-icon {
    width: 40px; height: 40px;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
    font-size: 18px;
}
.cpm-simple-icon--green { background: var(--green-light, #E1F5EE); color: var(--green-dark, #0F6E56); }
.cpm-simple-icon--blue  { background: var(--blue-light, #E6F1FB);  color: var(--blue, #185FA5); }

.cpm-simple-body { flex: 1; min-width: 0; }
.cpm-simple-name {
    font-size: 13px; font-weight: 600;
    color: var(--gray-900, #111827);
    margin: 0 0 2px;
}
.cpm-simple-desc {
    font-size: 11px; color: var(--gray-500, #6b7280);
    margin: 0; line-height: 1.4;
}
.cpm-simple-action { flex-shrink: 0; }

/* ── Responsive ──────────────────────────────────────────────── */
@media (max-width: 768px) {
    /* Section padding adjustments */
    .cpm-section {
        padding: 16px 16px 0;
    }
    .cpm-section:last-child { 
        padding-bottom: 20px; 
    }

    /* Larger section labels */
    .cpm-section-label {
        font-size: 12px;
        margin-bottom: 16px;
    }

    /* Larger tier descriptions */
    .cpm-tier-desc {
        font-size: 14px;
        margin-bottom: 20px;
    }

    /* Grid - full width single column with larger gaps */
    .cpm-grid {
        grid-template-columns: 1fr;
        gap: 16px;
    }

    /* Larger plan cards */
    .cpm-card {
        border-radius: 16px;
        border-width: 1px;
    }

    .cpm-card-head {
        padding: 20px 20px 14px;
    }

    .cpm-card-name {
        font-size: 16px;
        gap: 8px;
    }

    .cpm-card-desc {
        font-size: 13px;
        margin-top: 4px;
    }

    /* Larger price display */
    .cpm-card-price {
        padding: 16px 20px 0;
    }

    .cpm-price-amount {
        font-size: 28px;
    }

    .cpm-price-per {
        font-size: 13px;
    }

    /* Larger features list */
    .cpm-features {
        padding: 14px 20px;
    }

    .cpm-features li {
        font-size: 13px;
        padding: 6px 0;
        gap: 10px;
        line-height: 1.5;
    }

    .cpm-feat-icon {
        width: 14px;
        height: 14px;
        margin-top: 2px;
    }

    .cpm-feat-icon svg {
        width: 14px;
        height: 14px;
    }

    /* Larger footer and buttons */
    .cpm-card-foot {
        padding: 16px 20px;
    }

    .cpm-btn {
        font-size: 14px;
        padding: 10px 18px;
        border-radius: 10px;
    }

    /* Simple cards - stack vertically */
    .cpm-simple-card {
        flex-direction: column;
        align-items: flex-start;
        padding: 20px;
        gap: 16px;
        border-radius: 16px;
        border-width: 1px;
    }

    .cpm-simple-card.is-recommended::before {
        font-size: 11px;
        padding: 4px 12px;
        top: -10px;
        right: 20px;
    }

    .cpm-simple-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
    }

    .cpm-simple-icon svg {
        width: 22px;
        height: 22px;
    }

    .cpm-simple-name {
        font-size: 16px;
        margin-bottom: 4px;
    }

    .cpm-simple-desc {
        font-size: 13px;
        line-height: 1.6;
    }

    /* Full width action buttons on simple cards */
    .cpm-simple-action {
        width: 100%;
    }

    .cpm-simple-action .cpm-btn {
        font-size: 14px;
        padding: 10px 18px;
    }

    /* Recommended badge adjustments */
    .cpm-recommended-badge {
        font-size: 11px;
        padding: 3px 10px;
    }
}

/* Extra small devices */
@media (max-width: 480px) {
    .cpm-section {
        padding: 12px 12px 0;
    }

    .cpm-card-head {
        padding: 18px 16px 12px;
    }

    .cpm-card-name {
        font-size: 15px;
    }

    .cpm-price-amount {
        font-size: 24px;
    }

    .cpm-features {
        padding: 12px 16px;
    }

    .cpm-features li {
        font-size: 12px;
        padding: 5px 0;
    }

    .cpm-card-foot {
        padding: 14px 16px;
    }

    .cpm-btn {
        font-size: 13px;
        padding: 9px 16px;
    }

    .cpm-simple-card {
        padding: 16px;
    }

    .cpm-simple-name {
        font-size: 15px;
    }

    .cpm-simple-desc {
        font-size: 12px;
    }
}
</style>

<div class="cpm-wrap">

    {{-- ── Billing cycle toggle (only relevant for subscription plans) ── --}}
    @php
        $hasSubscriptionPlans = $plans->contains(fn($p) => ($p->pricing_model ?? 'subscription') === 'subscription' || !in_array($p->pricing_model ?? '', ['free','transaction']));
        $freePlans = $plans->filter(fn($p) => ($p->pricing_model ?? '') === 'free');
        $transactionPlans = $plans->filter(fn($p) => ($p->pricing_model ?? '') === 'transaction');
        $subscriptionPlans = $plans->filter(fn($p) => !in_array($p->pricing_model ?? '', ['free','transaction']));
        
        // Determine recommended plan - transaction if available and not current, otherwise first subscription
        $recommendedPlanId = null;
        if ($transactionPlans->count() && !$transactionPlans->contains('id', $currentPlan?->package_id)) {
            $recommendedPlanId = $transactionPlans->first()->id;
        } elseif ($subscriptionPlans->count()) {
            $recommendedPlanId = $subscriptionPlans->first()->id;
        }
    @endphp


    {{-- ═══════════════════════════════════════════════════════════
         TIER 1 — FREE
    ════════════════════════════════════════════════════════════ --}}
    @if($freePlans->count())
    <div class="cpm-section">
        <div class="cpm-section-label">
            <svg width="11" height="11" viewBox="0 0 11 11" fill="none">
                <circle cx="5.5" cy="5.5" r="4.5" stroke="currentColor" stroke-width="1.4"/>
                <path d="M3.5 5.5L5 7L7.5 4" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            Free
        </div>
        <p class="cpm-tier-desc">Get started at no cost. No credit card required.</p>

        @foreach($freePlans as $plan)
        <div class="cpm-simple-card {{ $plan->id == $currentPlan?->package_id ? 'is-current' : '' }}">
            <div class="cpm-simple-icon cpm-simple-icon--green">
                <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
                    <path d="M9 2L11.12 6.26L16 7L12.5 10.41L13.24 15.27L9 13.01L4.76 15.27L5.5 10.41L2 7L6.88 6.26L9 2Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
                </svg>
            </div>
            <div class="cpm-simple-body">
                <p class="cpm-simple-name">{{ $plan->name }}</p>
                <p class="cpm-simple-desc">
                    @if($plan->max_property ?? null)
                        - Up to {{ $plan->max_property }} {{ $plan->max_property == 1 ? 'property' : 'properties' }}
                    @endif

                    @if($plan->max_unit ?? null)
                        - Up to {{ $plan->max_unit }} Units
                    @endif

                    @if($plan->max_marketplace_listings ?? null)
                        - Up to {{ $plan->max_marketplace_listings }} Markeplace listings 
                    @endif

                    @if($plan->commission_discount ?? null)
                        - {{ $plan->commission_discount }}% discount applied 
                    @endif

                    - 0 free SMS 

                   <b> - No payment required </b>
                </p>
            </div>
            <div class="cpm-simple-action">
                <form class="ajax" action="{{ route('owner.subscription.order') }}" method="post"
                      enctype="multipart/form-data" data-handler="setPaymentModal">
                    @csrf
                    <input type="hidden" name="id" value="{{ $plan->id }}">
                    <input type="hidden" class="plan_type" name="duration_type" value="1">
                    <input type="hidden" name="per_monthly_price" value="{{ $plan->per_monthly_price ?? 0 }}">
                    <input type="hidden" name="per_yearly_price" value="{{ $plan->per_yearly_price ?? 0 }}">
                    @if($plan->id == $currentPlan?->package_id)
                        <button type="button" class="cpm-btn cpm-btn--current" disabled>
                            <svg width="11" height="11" viewBox="0 0 11 11" fill="none">
                                <path d="M2 5.5L4.5 8L9 3" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            Current Plan
                        </button>
                    @else
                        <button type="button" id="subscribeBtn" class="cpm-btn cpm-btn--primary"
                            data-pricing-model="free">
                            Get Started Free
                        </button>
                    @endif
                </form>
            </div>
        </div>
        @endforeach
    </div>
    @endif

    {{-- ═══════════════════════════════════════════════════════════
         TIER 2 — TRANSACTION (Revenue Share) — RECOMMENDED
    ════════════════════════════════════════════════════════════ --}}
    @if($transactionPlans->count())
    <div class="cpm-section">
        <div class="cpm-section-label">
            <svg width="11" height="11" viewBox="0 0 11 11" fill="none">
                <path d="M1.5 5.5H9.5M7 3L9.5 5.5L7 8" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            Transaction-based
        </div>
        <p class="cpm-tier-desc">No upfront cost. A small platform fee is applied on each tenant payment collected. <strong style="color:var(--blue,#185FA5);">Most popular choice for growing portfolios.</strong></p>

        @foreach($transactionPlans as $plan)
        <div class="cpm-simple-card {{ $plan->id == $currentPlan?->package_id ? 'is-current' : '' }} {{ $plan->id == $recommendedPlanId ? 'is-recommended' : '' }}">
            <div class="cpm-simple-icon cpm-simple-icon--blue">
                <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
                    <path d="M3 9h12M9 3v12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                    <rect x="1.5" y="4.5" width="15" height="9" rx="2" stroke="currentColor" stroke-width="1.5"/>
                </svg>
            </div>
            <div class="cpm-simple-body">
                <p class="cpm-simple-name">
                    {{ $plan->name }}
                </p>
                <p class="cpm-simple-desc">
                    <b> - Unlimited units and properties </b>

                    <b>  - Unlimited Marketplace Listings </b>

                    <b> - Centresidence wallet </b>

                    - 200 Free monthly SMS

                    - 1% per transaction

                    @if(($plan->commission_markup ?? null) || ($plan->commission_discount ?? null))
                        @if($plan->commission_discount ?? null)
                            - {{ $plan->commission_discount }}% discount applied
                        @endif
                    @endif
                    <b> - No monthly fee </b>
                </p>
            </div>
            <div class="cpm-simple-action">
                <form class="ajax" action="{{ route('owner.subscription.order') }}" method="post"
                      enctype="multipart/form-data" data-handler="setPaymentModal">
                    @csrf
                    <input type="hidden" name="id" value="{{ $plan->id }}">
                    <input type="hidden" class="plan_type" name="duration_type" value="1">
                    <input type="hidden" name="per_monthly_price" value="{{ $plan->per_monthly_price ?? 0 }}">
                    <input type="hidden" name="per_yearly_price" value="{{ $plan->per_yearly_price ?? 0 }}">
                    @if($plan->id == $currentPlan?->package_id)
                        <button type="button" class="cpm-btn cpm-btn--current" disabled>
                            <svg width="11" height="11" viewBox="0 0 11 11" fill="none">
                                <path d="M2 5.5L4.5 8L9 3" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            Current Plan
                        </button>
                    @else
                        <button type="button" id="subscribeBtn" class="cpm-btn cpm-btn--primary"
                        data-pricing-model="transaction">
                            Select Plan
                            <svg width="11" height="11" viewBox="0 0 11 11" fill="none">
                                <path d="M2 5.5H9M6.5 3L9 5.5L6.5 8" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </button>
                    @endif
                </form>
            </div>
        </div>
        @endforeach
    </div>
    @endif

    {{-- ═══════════════════════════════════════════════════════════
         TIER 3 — SUBSCRIPTION (Feature packages)
    ════════════════════════════════════════════════════════════ --}}
    @if($subscriptionPlans->count())
    <div class="cpm-section">
        <div class="cpm-section-label">
            <svg width="11" height="11" viewBox="0 0 11 11" fill="none">
                <path d="M2 8.5L5.5 2L9 8.5H2Z" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round"/>
            </svg>
            Subscription Plans
        </div>
        <p class="cpm-tier-desc">Flat monthly or yearly fee. Unlock higher limits, automation, and priority support. Best for agencies with volume. </p>

        <div class="cpm-grid">
            @foreach($subscriptionPlans as $plan)
            <div class="cpm-card {{ $plan->id == $currentPlan?->package_id ? 'is-current' : '' }}">

                {{-- Head --}}
                <div class="cpm-card-head">
                    <p class="cpm-card-name">
                        {{ $plan->name }}
                        @if($plan->id == $currentPlan?->package_id)
                            <span style="display:inline-flex;align-items:center;gap:3px;font-size:10px;font-weight:500;padding:2px 7px;border-radius:99px;background:var(--green-light,#E1F5EE);color:var(--green-dark,#0F6E56);">
                                <svg width="8" height="8" viewBox="0 0 8 8" fill="currentColor"><circle cx="4" cy="4" r="3"/></svg>
                                Active
                            </span>
                        @elseif($plan->id == $recommendedPlanId && !$transactionPlans->count())
                            <span class="cpm-recommended-badge">
                                <svg width="8" height="8" viewBox="0 0 8 8" fill="currentColor"><path d="M4 0L5 3L8 4L5 5L4 8L3 5L0 4L3 3L4 0Z"/></svg>
                                Recommended
                            </span>
                        @endif
                    </p>
                    @if($plan->description)
                    <p class="cpm-card-desc">{{ $plan->description }}</p>
                    @endif
                </div>

                {{-- Price --}}
                <div class="cpm-card-price">
                    <div class="price-monthly">
                        <span class="cpm-price-amount">{{ currencyPrice($plan->monthly_price) }}</span>
                        <span class="cpm-price-per">/mo</span>
                    </div>
                    <div class="price-yearly d-none">
                        <span class="cpm-price-amount">{{ currencyPrice($plan->yearly_price) }}</span>
                        <span class="cpm-price-per">/yr</span>
                    </div>
                </div>

                {{-- Features --}}
                <ul class="cpm-features">
                    @if($plan->type == PACKAGE_TYPE_PROPERTY)
                        <li>
                            <svg class="cpm-feat-icon" width="11" height="11" viewBox="0 0 11 11" fill="none"><path d="M2 5.5L4.5 8L9 3" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            <span class="price-monthly">{{ currencyPrice($plan->per_monthly_price) }} per property</span>
                            <span class="price-yearly d-none">{{ currencyPrice($plan->per_yearly_price) }} per property</span>
                        </li>
                    @endif
                    @if($plan->type == PACKAGE_TYPE_UNIT)
                        <li>
                            <svg class="cpm-feat-icon" width="11" height="11" viewBox="0 0 11 11" fill="none"><path d="M2 5.5L4.5 8L9 3" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            <span class="price-monthly">Up to {{ $plan->max_unit }} units</span>
                            <span class="price-yearly d-none" style="color:var(--green-dark,#0F6E56);font-weight:600;">2 months free!</span>
                        </li>
                    @endif
                    @if($plan->type == PACKAGE_TYPE_TENANT)
                        <li>
                            <svg class="cpm-feat-icon" width="11" height="11" viewBox="0 0 11 11" fill="none"><path d="M2 5.5L4.5 8L9 3" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            <span class="price-monthly">{{ currencyPrice($plan->per_monthly_price) }} per tenant</span>
                            <span class="price-yearly d-none">{{ currencyPrice($plan->per_yearly_price) }} per tenant</span>
                        </li>
                    @endif
                    <li>
                        <svg class="cpm-feat-icon" width="11" height="11" viewBox="0 0 11 11" fill="none"><path d="M2 5.5L4.5 8L9 3" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        {{ $plan->max_maintainer == -1 ? 'Unlimited maintainers' : $plan->max_maintainer . ' maintainers' }}
                    </li>
                    <li>
                        <svg class="cpm-feat-icon" width="11" height="11" viewBox="0 0 11 11" fill="none"><path d="M2 5.5L4.5 8L9 3" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        {{ $plan->max_invoice == -1 ? 'Unlimited invoices' : $plan->max_invoice . ' invoices' }}
                    </li>
                    <li>
                        <svg class="cpm-feat-icon" width="11" height="11" viewBox="0 0 11 11" fill="none"><path d="M2 5.5L4.5 8L9 3" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        {{ $plan->max_auto_invoice == -1 ? 'Unlimited auto-invoices' : $plan->max_auto_invoice . ' auto-invoices' }}
                    </li>
                    @if($plan->ticket_support == ACTIVE)
                    <li>
                        <svg class="cpm-feat-icon cpm-feat-icon--blue" width="11" height="11" viewBox="0 0 11 11" fill="none"><path d="M2 5.5L4.5 8L9 3" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        Ticket support
                    </li>
                    @endif
                    @if($plan->notice_support == ACTIVE)
                    <li>
                        <svg class="cpm-feat-icon cpm-feat-icon--blue" width="11" height="11" viewBox="0 0 11 11" fill="none"><path d="M2 5.5L4.5 8L9 3" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        Notice support
                    </li>
                    @endif
                </ul>

                {{-- Footer / CTA --}}
                <div class="cpm-card-foot">
                    <form class="ajax" action="{{ route('owner.subscription.order') }}" method="post"
                          enctype="multipart/form-data" data-handler="setPaymentModal">
                        @csrf
                        <input type="hidden" name="id" value="{{ $plan->id }}">
                        <input type="hidden" class="plan_type" name="duration_type" value="1">
                        <input type="hidden" name="per_monthly_price" value="{{ $plan->per_monthly_price }}">
                        <input type="hidden" name="per_yearly_price" value="{{ $plan->per_yearly_price }}">

                        @if($plan->id == $currentPlan?->package_id)
                            {{-- Monthly --}}
                            <button type="submit"
                                class="cpm-btn payment-gateway-active price-monthly {{ $currentPlan->duration_type == PACKAGE_DURATION_TYPE_MONTHLY ? 'cpm-btn--current current-plan-button' : 'cpm-btn--primary d-none' }}"
                                {{ $currentPlan->duration_type == PACKAGE_DURATION_TYPE_MONTHLY ? 'disabled' : '' }}>
                                @if($currentPlan->duration_type == PACKAGE_DURATION_TYPE_MONTHLY)
                                    <svg width="11" height="11" viewBox="0 0 11 11" fill="none"><path d="M2 5.5L4.5 8L9 3" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    Current Plan
                                @else
                                    Subscribe
                                @endif
                            </button>
                            {{-- Yearly --}}
                            <button type="submit"
                                class="cpm-btn payment-gateway-active price-yearly {{ $currentPlan->duration_type == PACKAGE_DURATION_TYPE_YEARLY ? 'cpm-btn--current current-plan-button d-none' : 'cpm-btn--primary d-none' }}"
                                {{ $currentPlan->duration_type == PACKAGE_DURATION_TYPE_YEARLY ? 'disabled' : '' }}>
                                @if($currentPlan->duration_type == PACKAGE_DURATION_TYPE_YEARLY)
                                    <svg width="11" height="11" viewBox="0 0 11 11" fill="none"><path d="M2 5.5L4.5 8L9 3" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    Current Plan
                                @else
                                    Subscribe Yearly
                                @endif
                            </button>
                        @else
                            <button type="submit" id="subscribeBtn" class="cpm-btn cpm-btn--primary">
                                Subscribe
                                <svg width="11" height="11" viewBox="0 0 11 11" fill="none">
                                    <path d="M2 5.5H9M6.5 3L9 5.5L6.5 8" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </button>
                        @endif
                    </form>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

</div>

<script>
// Sync toggle label active state (visual only — the existing JS handles d-none toggling)
(function () {
    var toggle = document.getElementById('monthly-yearly-button');
    if (!toggle) return;
    var monthlyLabel = document.querySelector('.price-monthly-label');
    var yearlyLabel  = document.querySelector('.price-yearly-label');
    toggle.addEventListener('change', function () {
        if (this.checked) {
            monthlyLabel && monthlyLabel.classList.remove('is-active');
            yearlyLabel  && yearlyLabel.classList.add('is-active');
        } else {
            yearlyLabel  && yearlyLabel.classList.remove('is-active');
            monthlyLabel && monthlyLabel.classList.add('is-active');
        }
    });
}());
</script>