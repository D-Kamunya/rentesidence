<?php

/*
|--------------------------------------------------------------------------
| Owner prepaid credit buckets
|--------------------------------------------------------------------------
|
| ONE registry describing every prepaid, consumable credit an owner can hold
| (SMS, e-signature agreements, and any future kind — screening hits, etc.).
| The single money rail (App\Services\Credit\CreditService) reads a bucket's
| shape from here, so adding a new credit type is a config entry, not new
| service/ledger/controller/callback code.
|
| NOTE: the Centresidence financing "wallet" (owner_wallets / wallet_transactions,
| credited by domain events inside the simulation) is deliberately NOT a bucket
| here — it is a different abstraction and stays on its own rail.
|
| Each balance lives in a typed column on `owners`; every movement is ledgered
| in the shared `owner_credit_transactions` table, tagged with the bucket key.
| A bucket with `pools => true` splits its balance into an expiring monthly
| "granted" allowance and a non-expiring "purchased" pool (SMS); the total is
| always kept in `balance_column`.
|
*/

return [

    'buckets' => [

        'sms' => [
            'label'            => 'SMS credits',
            'icon'             => 'ri-message-2-line',
            'unit'             => 'SMS credit',       // singular; pluralised with +s for display
            'balance_column'   => 'sms_credits',
            'pools'            => true,
            'granted_column'   => 'sms_granted_credits',
            'purchased_column' => 'sms_purchased_credits',
            'price_option'     => 'sms_credit_price', // getOption() key for KES unit price
            'default_price'    => 1.00,
            'min_quantity'     => 10,
            'max_quantity'     => 10000,
            'index_route'      => 'owner.sms.credits.index',
            'verify_route'     => 'owner.sms.credits.verify',
            'deduct_description' => 'SMS sent',
        ],

        'agreement' => [
            'label'            => 'Agreement credits',
            'icon'             => 'ri-quill-pen-line',
            'unit'             => 'agreement credit',
            'balance_column'   => 'agreement_credits',
            'pools'            => false,
            'price_option'     => 'agreement_price',
            'default_price'    => 50,
            'min_quantity'     => 1,
            'max_quantity'     => 1000,
            'index_route'      => 'owner.agreement.index',
            'verify_route'     => 'owner.agreement.credits.verify',
            'deduct_description' => 'Agreement sent',
            // Optional: a callable(ownerUserId): bool. When it returns true the balance is
            // shown as "Unlimited (included in your plan)" instead of a purchased count —
            // subscription/transaction plans (and standalone installs) cover agreements with
            // no metering. Purchased credits, when metered on the free tier, still count.
            'unlimited_resolver' => [\App\Services\Agreement\AgreementService::class, 'ownerHasUnlimited'],
            // Optional: a callable(ownerUserId): ?['quota','used','remaining']. When set, a
            // metered (free-plan) owner sees a COMPUTED monthly free allowance ("N of 10 free
            // this month") rather than just their purchased balance — the free 10 aren't a
            // stored pool, they're counted from this month's free-covered sends and reset on the 1st.
            'allowance_resolver' => [\App\Services\Agreement\AgreementService::class, 'freeMonthlyAllowance'],
        ],

        'screening' => [
            'label'            => 'Screening credits',
            'icon'             => 'ri-shield-user-line',
            'unit'             => 'screening credit',
            'balance_column'   => 'screening_credits',
            'pools'            => false,
            'price_option'     => 'screening_price',
            'default_price'    => 30,
            'min_quantity'     => 1,
            'max_quantity'     => 1000,
            'index_route'      => 'owner.screening.index',
            'verify_route'     => 'owner.screening.credits.verify',
            'deduct_description' => 'Tenant screening lookup',
            // Paid plans (subscription/transaction) and standalone installs include screening
            // with no metering; the free tier meters per lookup (small monthly free allowance,
            // then purchased credits). Mirrors the agreement bucket's coverage model.
            'unlimited_resolver' => [\App\Services\Screening\ScreeningLookupService::class, 'ownerHasUnlimited'],
            'allowance_resolver' => [\App\Services\Screening\ScreeningLookupService::class, 'freeMonthlyAllowance'],
        ],

    ],

];
