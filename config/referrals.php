<?php

/**
 * Invite-a-landlord funnel — the Tenant Helper growth loop.
 *
 * A tenant invites their off-platform landlord; when that landlord becomes a REAL
 * customer (transacts real money through us), the tenant earns a one-time bonus.
 * The whole feature is a config toggle (per market / season), and the reward layer
 * sits on top of the referral primitive + attribution ledger — turn cash off and the
 * funnel still works on non-cash rewards + the graduation-to-affiliate path.
 *
 * Every knob is env-overridable so it can be tuned live without a deploy.
 */
return [

    // Master switch for the whole funnel (invite surface, public landing, rewards).
    'enabled' => env('LANDLORD_REFERRALS_ENABLED', true),

    // Cash reward switch. OFF ⇒ the funnel still runs (non-cash + graduation), no cash fires.
    // Shipped ON: we need the growth, and the anti-abuse is structural (see below).
    'cash_enabled' => env('LANDLORD_REFERRAL_CASH', true),

    // One-time cash bonus per referred owner who becomes a real customer (bounded CAC).
    // Conservative start; treat it as a customer-acquisition cost.
    'cash_amount' => (float) env('LANDLORD_REFERRAL_CASH_AMOUNT', 200),
    'currency'    => env('LANDLORD_REFERRAL_CURRENCY', 'KES'),

    // The "real customer" bar — protects CAC and kills the farm-a-tiny-payment attack.
    // Cash confirms when the referred owner EITHER makes a first paid subscription, OR
    // their cumulative real platform revenue reaches this threshold across the 5 streams
    // (subscription, rent commission, marketplace, utility/infra, financing) — whichever first.
    'revenue_threshold' => (float) env('LANDLORD_REFERRAL_REVENUE_THRESHOLD', 1000),

    // Hold a confirmed reward this many days before it becomes payable (retention +
    // clawback window: an owner who churns / refunds inside it is clawed back).
    'hold_days' => (int) env('LANDLORD_REFERRAL_HOLD_DAYS', 30),

    // Don't pay out until the tenant's payable balance reaches this (startup cashflow +
    // first anti-abuse layer). Rewards accrue and are released together above the floor.
    'min_payout' => (float) env('LANDLORD_REFERRAL_MIN_PAYOUT', 500),

    // Graduation: a tenant with at least this many CONFIRMED referrals has proven they're
    // a channel → they're offered the real affiliate toolkit (recurring commission + Academy).
    'graduation_threshold' => (int) env('LANDLORD_REFERRAL_GRADUATION', 3),

    // Anti-abuse guardrails (mostly structural; these are the tunable numbers).
    'anti_abuse' => [
        // A single tenant can start at most this many invites in a rolling 24h (velocity cap).
        'max_invites_per_day' => (int) env('LANDLORD_REFERRAL_MAX_INVITES_DAY', 20),
        // Flag (don't auto-pay) a referral for manual review when a referrer's confirmed
        // count in a rolling window exceeds this — a spike worth a human look.
        'manual_review_after' => (int) env('LANDLORD_REFERRAL_REVIEW_AFTER', 10),
    ],
];
