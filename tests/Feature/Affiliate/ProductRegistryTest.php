<?php

namespace Tests\Feature\Affiliate;

use App\Services\AffiliateOs\ProductRegistry;
use App\Services\Suggestions\PropertySalesSuggestionStrategy;
use Tests\TestCase;

/**
 * Affiliate OS WP-A — the product registry drives per-product dispatch. These are
 * pure config reads (no DB).
 */
class ProductRegistryTest extends TestCase
{
    public function test_default_product_is_property_sales(): void
    {
        $this->assertSame('property_sales', ProductRegistry::default());
        $this->assertTrue(ProductRegistry::exists('property_sales'));
        $this->assertContains('property_sales', ProductRegistry::keys());
    }

    public function test_unknown_product_is_not_registered(): void
    {
        $this->assertFalse(ProductRegistry::exists('nexterra'));
    }

    public function test_resolves_property_suggestion_strategy(): void
    {
        $this->assertInstanceOf(
            PropertySalesSuggestionStrategy::class,
            ProductRegistry::suggestionStrategy('property_sales')
        );
    }

    public function test_unregistered_product_falls_back_to_property_strategy(): void
    {
        // The engine must never die on a legacy/unknown product.
        $this->assertInstanceOf(
            PropertySalesSuggestionStrategy::class,
            ProductRegistry::suggestionStrategy('some_unregistered_product')
        );
    }
}
