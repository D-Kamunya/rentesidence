{{--
    Origin-aware billing-switch consent note for the financing apply forms.
    The transaction-plan switch is DEFERRED to disbursement, so this is an
    informational heads-up, not a wall — and it's framed by where the owner is
    coming from (subscription → the 1% REPLACES their plan fee; free → the 1% is
    new, so lead with the value). Expects: $currentMode ('free'|'subscription'|'transaction').
--}}
@php $mode = $currentMode ?? 'free'; @endphp
@if ($mode !== 'transaction')
    <div class="cs-alert is-info" style="margin-bottom:16px;">
        <strong>{{ __('Your billing only changes if this is disbursed.') }}</strong>
        @if ($mode === 'subscription')
            {{ __('If approved and disbursed, your billing moves to the Transaction plan — 1% of rent replaces your monthly plan fee, and you get unlimited units. You apply on your current plan; nothing changes unless it disburses.') }}
        @else
            {{ __('If approved and disbursed, your billing moves to the Transaction plan so repayment is collected from rent — 1% of rent (only on rent paid through the platform), plus unlimited units and no upfront cost. You apply on your current plan; nothing changes unless it disburses.') }}
        @endif
    </div>
@endif
