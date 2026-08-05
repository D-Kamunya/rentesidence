<?php

use App\Services\Commission\PropertySalesCommissionStrategy;
use App\Services\Suggestions\PropertySalesSuggestionStrategy;

/*
|--------------------------------------------------------------------------
| Affiliate OS — product registry
|--------------------------------------------------------------------------
| One affiliate, many products (see docs/affiliate-os-design.md). Each product
| the affiliate network works is declared here — its display name, settlement
| currency, and the per-product strategy classes the OS dispatches to (commission
| rule + suggestion rule). Adding a spoke (Solidus, Nexterra, Crylac) = adding a
| key here + its strategy classes, NOT touching the OS machinery. Config-not-code.
|
| `default_product` is what existing / untagged rows belong to — the original
| Centresidence property-management motion. Never rename it without a data
| migration: it is the backfill value stamped on all legacy leads/commissions.
*/

return [

    'default_product' => 'property_sales',

    'products' => [

        'property_sales' => [
            'name'                => 'Centresidence — Property Management',
            'currency'            => env('CENTRESIDENCE_CURRENCY', 'KES'),
            // Per-product strategies. commission_strategy is added in WP-B; the
            // suggestion strategy already exists and is dispatched by the engine.
            'suggestion_strategy' => PropertySalesSuggestionStrategy::class,
            'commission_strategy' => PropertySalesCommissionStrategy::class,
        ],

    ],
];
