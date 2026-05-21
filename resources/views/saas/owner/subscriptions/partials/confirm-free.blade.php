{{--
    confirm-free.blade.php
    Confirmation screen for free & transaction (no-payment) plans.
    Rendered inside #choosePlanModal > #planListBlock via AJAX.
    Display data arrives via window._cfmPlanId, _cfmPlanName, _cfmPricingModel
    set by owner-subscription.js before the AJAX call fires.
--}}

<style>
/* ── Confirmation wrap ─────────────────────────────────────── */
.cfm-wrap {
    padding: 28px 28px 0;
    font-size: 13px;
    color: var(--gray-700, #374151);
}

/* ── Back link ──────────────────────────────────────────────── */
.cfm-back {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 11px;
    font-weight: 500;
    color: var(--gray-500, #6b7280);
    cursor: pointer;
    margin-bottom: 20px;
    background: none;
    border: none;
    padding: 0;
    transition: color .15s;
}
.cfm-back:hover { color: var(--blue, #185FA5); }

/* ── Plan badge ─────────────────────────────────────────────── */
.cfm-plan-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 11px;
    font-weight: 600;
    padding: 4px 12px;
    border-radius: 99px;
    margin-bottom: 14px;
}
.cfm-plan-badge--free        { background: var(--green-light,#E1F5EE); color: var(--green-dark,#0F6E56); }
.cfm-plan-badge--transaction { background: var(--blue-light,#E6F1FB);  color: var(--blue,#185FA5); border: 0.5px solid var(--blue-border,#B5D4F4); }

/* ── Heading ────────────────────────────────────────────────── */
.cfm-heading {
    font-size: 18px;
    font-weight: 600;
    color: var(--gray-900, #111827);
    margin: 0 0 4px;
    line-height: 1.3;
}
.cfm-subheading {
    font-size: 12px;
    color: var(--gray-500, #6b7280);
    margin: 0 0 22px;
    line-height: 1.5;
}

/* ── Points list ────────────────────────────────────────────── */
.cfm-points {
    display: flex;
    flex-direction: column;
    gap: 10px;
    margin-bottom: 22px;
}

.cfm-point {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 12px 14px;
    border-radius: 10px;
    background: var(--gray-50, #fafafa);
    border: 0.5px solid var(--gray-200, #e5e7eb);
}

.cfm-point--warn {
    background: #FFFBEB;
    border-color: #FDE68A;
}

.cfm-point-icon {
    width: 30px;
    height: 30px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.cfm-point-icon--green  { background: var(--green-light,#E1F5EE); color: var(--green-dark,#0F6E56); }
.cfm-point-icon--blue   { background: var(--blue-light,#E6F1FB);  color: var(--blue,#185FA5); }
.cfm-point-icon--amber  { background: #FEF3C7; color: #92400E; }
.cfm-point-icon--purple { background: #EDE9FE; color: #5B21B6; }

.cfm-point-title {
    font-size: 12px;
    font-weight: 600;
    color: var(--gray-900, #111827);
    margin: 0 0 2px;
}
.cfm-point-desc {
    font-size: 11px;
    color: var(--gray-500, #6b7280);
    margin: 0;
    line-height: 1.5;
}

/* ── Checkbox acknowledgement ───────────────────────────────── */
.cfm-ack {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    padding: 14px;
    background: var(--blue-ghost, #185ea51c);
    border: 0.5px solid var(--blue-border, #B5D4F4);
    border-radius: 10px;
    margin-bottom: 22px;
    cursor: pointer;
}

.cfm-ack input[type="checkbox"] {
    margin-top: 1px;
    width: 15px;
    height: 15px;
    flex-shrink: 0;
    accent-color: var(--blue, #185FA5);
    cursor: pointer;
}

.cfm-ack-label {
    font-size: 12px;
    font-weight: 500;
    color: var(--gray-700, #374151);
    line-height: 1.55;
    cursor: pointer;
    user-select: none;
}

/* ── Action footer ──────────────────────────────────────────── */
.cfm-footer {
    padding: 16px 28px;
    background: var(--gray-50, #fafafa);
    border-top: 0.5px solid var(--gray-200, #e5e7eb);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin: 0 -28px;
}

.cfm-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 7px;
    font-size: 13px;
    font-weight: 600;
    padding: 9px 22px;
    border-radius: 8px;
    border: none;
    cursor: pointer;
    transition: all .15s;
    text-decoration: none;
}
.cfm-btn--primary {
    background: var(--blue, #185FA5);
    color: #fff;
    min-width: 160px;
}
.cfm-btn--primary:hover:not(:disabled) {
    background: var(--blue-hover, #0F4A84);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(24,95,165,.25);
    color: #fff;
}
.cfm-btn--primary:disabled {
    opacity: 0.45;
    cursor: not-allowed;
}
.cfm-btn--ghost {
    background: transparent;
    color: var(--gray-500, #6b7280);
    border: 0.5px solid var(--gray-200, #e5e7eb);
    font-weight: 500;
    font-size: 12px;
}
.cfm-btn--ghost:hover { background: var(--gray-100, #f3f4f6); }

/* ── Transaction-specific alert banner ─────────────────────── */
.cfm-tx-alert {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    padding: 12px 14px;
    background: #FFF7ED;
    border: 0.5px solid #FED7AA;
    border-radius: 10px;
    margin-bottom: 16px;
    font-size: 11px;
    color: #92400E;
    line-height: 1.55;
}
.cfm-tx-alert svg { flex-shrink: 0; margin-top: 1px; }

/* ── Spin animation for loading state ───────────────────────── */
@keyframes cfm-spin { to { transform: rotate(360deg); } }
.cfm-spinning { animation: cfm-spin .7s linear infinite; }
</style>

<div class="cfm-wrap" id="cfmWrap">

    {{-- Back ─────────────────────────────────────────────────── --}}
    <button class="cfm-back" id="cfmBackBtn" type="button">
        <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
            <path d="M9 2.5L4.5 7L9 11.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        Back to plans
    </button>

    {{-- Badge (populated by JS) ──────────────────────────────── --}}
    <div id="cfmPlanBadge"></div>

    {{-- Heading ──────────────────────────────────────────────── --}}
    <h2 class="cfm-heading" id="cfmHeading">Confirm your plan</h2>
    <p class="cfm-subheading" id="cfmSubheading">Review the details below before activating.</p>

    {{-- Transaction alert banner (transaction only) ──────────── --}}
    <div class="cfm-tx-alert d-none" id="cfmTxAlert">
        <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
            <path d="M7 1.5L12.5 12H1.5L7 1.5Z" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round"/>
            <path d="M7 5.5V8" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>
            <circle cx="7" cy="10" r=".6" fill="currentColor"/>
        </svg>
        <span id="cfmTxAlertText"></span>
    </div>

    {{-- Points list (populated by JS) ───────────────────────── --}}
    <div class="cfm-points" id="cfmPoints"></div>

    {{-- Acknowledgement checkbox ─────────────────────────────── --}}
    <label class="cfm-ack" for="cfmAckCheck">
        <input type="checkbox" id="cfmAckCheck">
        <span class="cfm-ack-label" id="cfmAckLabel"></span>
    </label>

    {{-- Hidden POST form — submits to activateFree endpoint ──── --}}
    <form method="POST" action="{{ route('owner.subscription.activate.free') }}" id="cfmForm">
        @csrf
        <input type="hidden" name="package_id" id="cfmPackageId">
        <input type="hidden" name="confirmed"   value="1">
    </form>

    {{-- Footer ──────────────────────────────────────────────── --}}
    <div class="cfm-footer">
        <button type="button" class="cfm-btn cfm-btn--ghost" id="cfmCancelBtn">Cancel</button>
        <button type="button" class="cfm-btn cfm-btn--primary" id="cfmConfirmBtn" disabled>
            <svg width="13" height="13" viewBox="0 0 13 13" fill="none">
                <path d="M2.5 6.5L5.2 9.5L10.5 3.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <span id="cfmConfirmLabel">Activate Plan</span>
        </button>
    </div>
</div>

<script>
(function () {

    // ─────────────────────────────────────────────────────────────
    // Point definitions
    // ─────────────────────────────────────────────────────────────

    var FREE_POINTS = [
        {
            icon: 'green',
            svg: '<path d="M2 5.5L4.5 8L9 3" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>',
            title: 'No payment required',
            desc:  'Activate instantly — no credit card or payment needed.'
        },
        {
            icon: 'blue',
            svg: '<circle cx="5.5" cy="5.5" r="4.5" stroke="currentColor" stroke-width="1.4"/><path d="M5.5 4V6.5M5.5 7.5v.5" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>',
            title: 'Usage limits apply',
            desc:  'This plan has caps on properties, tenants, and invoices. You can upgrade at any time.'
        },
        {
            icon: 'green',
            svg: '<path d="M1.5 5.5L4.5 8.5L8.5 2.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>',
            title: 'Active until cancelled',
            desc:  'No renewal dates or expiry. Your plan stays active until you cancel or upgrade.'
        }
    ];

    var TX_POINTS = [
        {
            icon: 'amber',
            warn: true,
            svg: '<path d="M5.5 2L10 9H1L5.5 2Z" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round"/><path d="M5.5 4.5V6.5" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/><circle cx="5.5" cy="8" r=".5" fill="currentColor"/>',
            title: 'Rent collected via platform M-Pesa',
            desc:  'All tenant rent payments are routed through the Centresidence M-Pesa account. Funds (minus the platform fee)'
        },
        {
            icon: 'blue',
            svg: '<path d="M1 5.5h9M7 3l3 2.5L7 8" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>',
            title: '1% platform fee per transaction',
            desc:  'A 1% fee is applied on each rent payment collected. No hidden charges, no monthly bill.'
        },
        {
            icon: 'green',
            svg: '<path d="M2 5.5L4.5 8L9 3" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>',
            title: 'No monthly subscription fee',
            desc:  'You only pay when rent is collected. Nothing is charged if no payments are made.'
        },
        {
            icon: 'purple',
            svg: '<rect x="1" y="2.5" width="9" height="6" rx="1.5" stroke="currentColor" stroke-width="1.4"/><path d="M7 5.5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Z" stroke="currentColor" stroke-width="1.2"/><path d="M9 4.5v2" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>',
            title: 'Centresidence Wallet',
            desc:  'Collected rent lands in your Centresidence Wallet — a clear ledger of every payment made.'
        },
        {
            icon: 'green',
            svg: '<path d="M5.5 1v9M2 5.5l3.5 4 3.5-4" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>',
            title: 'Funds available for withdrawal',
            desc:  'Net funds (rent minus 1%) are available for withdrawal from your Centreisidence Wallet .'
        },
        {
            icon: 'blue',
            svg: '<rect x="1" y="2" width="9" height="7" rx="1.5" stroke="currentColor" stroke-width="1.4"/><path d="M3 7l2-2 2 2" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>',
            title: 'Switch plans anytime',
            desc:  'No lock-in. Upgrade or change your plan whenever your portfolio grows.'
        }
    ];

    // ─────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────

    function renderPoints(points) {
        return points.map(function (p) {
            return '<div class="cfm-point ' + (p.warn ? 'cfm-point--warn' : '') + '">'
                + '<div class="cfm-point-icon cfm-point-icon--' + p.icon + '">'
                + '<svg width="14" height="14" viewBox="0 0 11 11" fill="none">' + p.svg + '</svg>'
                + '</div>'
                + '<div>'
                + '<p class="cfm-point-title">' + p.title + '</p>'
                + '<p class="cfm-point-desc">'  + p.desc  + '</p>'
                + '</div>'
                + '</div>';
        }).join('');
    }

    // ─────────────────────────────────────────────────────────────
    // Boot — read globals set by owner-subscription.js
    // ─────────────────────────────────────────────────────────────

    var planId       = window._cfmPlanId       || '';
    var planName     = window._cfmPlanName     || 'Plan';
    var pricingModel = window._cfmPricingModel || 'free';
    var isTx         = pricingModel === 'transaction';

    // Badge
    var badgeClass = isTx ? 'cfm-plan-badge--transaction' : 'cfm-plan-badge--free';
    var badgeIcon  = isTx
        ? '<svg width="9" height="9" viewBox="0 0 11 11" fill="none"><path d="M1 5.5h9M7 3l3 2.5L7 8" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/></svg>'
        : '<svg width="9" height="9" viewBox="0 0 11 11" fill="currentColor"><circle cx="5.5" cy="5.5" r="4"/></svg>';
    document.getElementById('cfmPlanBadge').innerHTML =
        '<span class="cfm-plan-badge ' + badgeClass + '">' + badgeIcon + ' ' + planName + '</span>';

    // Heading + subheading
    document.getElementById('cfmHeading').textContent = isTx
        ? 'Activate transaction plan'
        : 'Activate free plan';
    document.getElementById('cfmSubheading').textContent = isTx
        ? 'Read the details below carefully — this plan changes how rent payments are collected.'
        : 'No payment needed. Review what\'s included before activating.';

    // Transaction alert banner
    if (isTx) {
        document.getElementById('cfmTxAlertText').innerHTML =
            '<strong>Important:</strong> By selecting this plan, all rent payments from your tenants '
            + 'will be collected via the Centresidence platform M-Pesa account and held in your '
            + '<strong>Centresidence Wallet</strong>. Funds (minus the 1% platform fee) are '
            + 'available for withdrawal. Please ensure your tenants are informed of the '
            + 'new payment channel.';
        document.getElementById('cfmTxAlert').classList.remove('d-none');
    }

    // Points
    document.getElementById('cfmPoints').innerHTML = renderPoints(isTx ? TX_POINTS : FREE_POINTS);

    // Acknowledgement label
    var ackLabel = document.getElementById('cfmAckLabel');
    if (isTx) {
        ackLabel.innerHTML =
            'I understand that <strong>all rent payments will be routed through the Centresidence '
            + 'account</strong>, held in my Centresidence Wallet, and that a 1% platform '
            + 'fee will be deducted per transaction. '
            + 'I agree to activate this plan.';
    } else {
        ackLabel.textContent =
            'I understand the plan limitations and want to activate the free plan.';
    }

    // Confirm button label
    document.getElementById('cfmConfirmLabel').textContent = isTx
        ? 'Yes, Activate Transaction Plan'
        : 'Activate Free Plan';

    // Package ID into hidden form field
    document.getElementById('cfmPackageId').value = planId;

    // ─────────────────────────────────────────────────────────────
    // Interactions
    // ─────────────────────────────────────────────────────────────

    var ackCheck   = document.getElementById('cfmAckCheck');
    var confirmBtn = document.getElementById('cfmConfirmBtn');

    // Enable confirm only when checkbox is ticked
    ackCheck.addEventListener('change', function () {
        confirmBtn.disabled = !this.checked;
    });

    // Confirm -> submit form
    confirmBtn.addEventListener('click', function () {
        if (!ackCheck.checked) return;
        confirmBtn.disabled = true;
        confirmBtn.innerHTML =
            '<svg width="13" height="13" viewBox="0 0 13 13" fill="none" class="cfm-spinning">'
            + '<path d="M6.5 2A4.5 4.5 0 1 1 2 6.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>'
            + '</svg> Activating...';
        document.getElementById('cfmForm').submit();
    });

    // Back -> reload plan list into the modal
    document.getElementById('cfmBackBtn').addEventListener('click', function () {
        commonAjax(
            'GET',
            document.getElementById('chooseAPanRoute').value,
            setPlanModalData,
            setPlanModalData
        );
    });

    // Cancel -> close modal entirely
    document.getElementById('cfmCancelBtn').addEventListener('click', function () {
        $('#choosePlanModal').modal('hide');
    });

}());
</script>