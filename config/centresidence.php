<?php

/*
|--------------------------------------------------------------------------
| Centresidence — Infrastructure & Finance OS
|--------------------------------------------------------------------------
|
| Configuration for the Centresidence module: the IoT infrastructure-cost,
| commission, token and (later) financing engines that layer on top of the
| existing rental product. Everything here is config-driven by design — the
| handbook's first principle is "do not hardcode modules".
|
| This module is intentionally decoupled from the live rental SaaS: it owns
| its own service provider, migrations (loaded from app/Centresidence) and
| events, and never mutates legacy tables.
|
*/

return [

    // Master switch. When false the engines/jobs are dormant; nothing in the
    // live product depends on this being on.
    'enabled' => env('CENTRESIDENCE_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Money & precision
    |--------------------------------------------------------------------------
    |
    | All monetary amounts are computed in integer minor units (cents) via the
    | Money value object to avoid floating-point drift across the many
    | multiplications token/commission math performs. `scale` is the number of
    | decimal places the display currency carries (KES = 2).
    |
    | `rate_scale` is the precision retained for per-unit *rates* (e.g. KES per
    | litre, 0.0200) before a final amount is rounded back to `scale`.
    |
    */
    'money' => [
        'currency'   => env('CENTRESIDENCE_CURRENCY', 'KES'),
        'scale'      => 2,
        'rate_scale' => 6,
        // PHP's round() mode used when collapsing a rate to a cents amount.
        'rounding'   => PHP_ROUND_HALF_UP,
    ],

    /*
    |--------------------------------------------------------------------------
    | Billing cycle defaults
    |--------------------------------------------------------------------------
    |
    | Defaults for the monthly Infrastructure Cost / Commission jobs and the
    | dual-pathway fallback. Per-module / per-partner overrides live in their
    | own config tables; these are only the platform-wide fallbacks.
    |
    */
    'billing' => [
        // Day of month the infrastructure + commission jobs run.
        'cycle_day' => 1,
        // Grace days after a commission invoice goes overdue before token
        // fallback deduction may activate (metered components only).
        'commission_grace_days' => 7,
        // Hard cap: max share of a single rent transaction the commission
        // fallback may intercept (handbook §9.6.1, step 1).
        'fallback_rent_cap_percentage' => 50,
        // GLOBAL ceiling on the TOTAL deducted from one rent payment across all
        // streams (commission recovery + facility repayments + non-metered
        // recovery). Protects owner cashflow so a heavy month can never consume
        // more than this share — the per-stream caps draw from within it.
        'max_total_rent_deduction_percentage' => 60,
        // Hard ceiling on a per-owner CONSENTED cap. An owner may opt into a
        // higher personal deduction cap (to keep a facility's agreed term), but
        // never above this — they always keep at least (100 − this)% of rent.
        'max_consented_rent_deduction_percentage' => 90,
        // Share of a token purchase's OWNER REVENUE intercepted toward overdue
        // metered commission when fallback is active (handbook §8.1 fallback).
        // The tenant always receives full units; only owner revenue is reduced.
        'fallback_token_intercept_percentage' => 100,
        // 1% transaction fee on rent & marketplace (Transaction billing model).
        // Token purchases are explicitly EXEMPT (handbook §6.2 / §7.3).
        'transaction_fee_percentage' => 1,
    ],

    /*
    |--------------------------------------------------------------------------
    | Partner finance fees (how Centresidence earns on facilities it originates
    | & services for a finance partner)
    |--------------------------------------------------------------------------
    | BOTH are collected by NETTING from the partner's remittances — we already
    | hold the repayments we collect, so there is no separate invoice/friction.
    | Per-partner overrides live on finance_partners.origination_fee_percentage /
    | .servicing_fee_percentage; these are the platform-wide defaults (a partner
    | is "reviewed" simply by setting/clearing their own %).
    */
    'partner_fees' => [
        // One-time, % of facility principal. Booked at facility creation but only
        // COLLECTED once the facility is DISBURSED — never demand before the partner
        // has seen the value.
        'origination_percentage' => 2.0,
        // Recurring, % of each remittance batch (the repayments we collected that cycle).
        'servicing_percentage' => 1.0,
        // Cap on how much of a single remittance may go toward clearing origination,
        // so it amortises over several cycles and never starves the partner's payout
        // (origination is designed NOT to be a blocker).
        'origination_collection_cap_percentage' => 25.0,
    ],

    /*
    |--------------------------------------------------------------------------
    | Owner billing model
    |--------------------------------------------------------------------------
    | How an owner is billed for module/infrastructure costs. See handbook §6.
    */
    'owner_billing_models' => [
        'subscription' => 'subscription', // costs added to monthly invoice
        'transaction'  => 'transaction',  // metered via token deduction, 1% fee
    ],

    /*
    |--------------------------------------------------------------------------
    | Partner payouts (settlement remittance)
    |--------------------------------------------------------------------------
    | How collected facility repayments are paid out to finance partners.
    |   'log'   — record the payout but make no real transfer (safe default —
    |             use until M-Pesa credentials are configured and verified).
    |   'mpesa' — real M-Pesa B2C (to a phone) / B2B (to a paybill/till/bank
    |             paybill) disbursement to the partner's settlement account.
    */
    'payouts' => [
        'driver' => env('CENTRESIDENCE_PAYOUT_DRIVER', 'log'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Owner down-payment collection (partial financing)
    |--------------------------------------------------------------------------
    | How the owner's down-payment is collected at disbursement. Centresidence
    | is the installer/payee, so it collects the contribution from the owner.
    |   'log'   — record the collection as settled without a real charge (safe
    |             default — use until M-Pesa STK is configured and verified).
    |   'mpesa' — real M-Pesa STK push to the owner's phone, into the company
    |             rent collection account (getOption centresidence_rent_mpesa_account_id).
    */
    'collections' => [
        'driver' => env('CENTRESIDENCE_COLLECTION_DRIVER', 'log'),
    ],

    /*
    |--------------------------------------------------------------------------
    | ChirpStack / LoRaWAN device network
    |--------------------------------------------------------------------------
    | How provisioned devices are bound to the physical LoRaWAN network.
    |   'simulated' — devices auto-activate (is_simulated=true) with no network
    |                 call, so the metered billing chain can be exercised end to
    |                 end without hardware. Safe default.
    |   'live'      — register the gateway/device in ChirpStack and wait for a
    |                 join/uplink before activating. Requires a reachable
    |                 ChirpStack instance + API credentials (adapter is stubbed
    |                 until go-live).
    */
    'chirpstack' => [
        'driver'         => env('CENTRESIDENCE_CHIRPSTACK_DRIVER', 'simulated'),
        'api_url'        => env('CHIRPSTACK_API_URL'),
        'api_token'      => env('CHIRPSTACK_API_TOKEN'),
        'tenant_id'      => env('CHIRPSTACK_TENANT_ID'),
        'application_id' => env('CHIRPSTACK_APPLICATION_ID'),
        'device_profile' => env('CHIRPSTACK_DEVICE_PROFILE_ID'),
        'timeout'        => (int) env('CHIRPSTACK_HTTP_TIMEOUT', 10),

        // Shared secret ChirpStack sends on its HTTP integration so we can trust
        // the inbound uplink/join webhook. Configure the same value as a custom
        // header (Authorization: Bearer <secret>) on the ChirpStack integration.
        'webhook_secret' => env('CHIRPSTACK_WEBHOOK_SECRET'),

        // Device-specific payload codec (decode uplink → units, encode downlink
        // command → bytes). This is the ONE piece that depends on the physical
        // meter; swap in a meter-specific class once you have its datasheet.
        'codec'          => env('CHIRPSTACK_CODEC', \App\Centresidence\Services\ChirpStack\Codec\GenericMeterCodec::class),

        // Default downlink fPort for credit/actuate commands (meter-specific).
        'downlink_fport' => (int) env('CHIRPSTACK_DOWNLINK_FPORT', 10),
    ],
];
