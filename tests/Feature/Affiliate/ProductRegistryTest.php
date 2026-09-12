<?php

namespace Tests\Feature\Affiliate;

use App\Services\AffiliateOs\ProductRegistry;
use App\Services\Suggestions\PropertyManagementSuggestionStrategy;
use Tests\TestCase;

/**
 * Affiliate OS WP-A — the product registry drives per-product dispatch. These are
 * pure config reads (no DB).
 */
class ProductRegistryTest extends TestCase
{
    public function test_default_product_is_property_management(): void
    {
        $this->assertSame('property_management', ProductRegistry::default());
        $this->assertTrue(ProductRegistry::exists('property_management'));
        $this->assertContains('property_management', ProductRegistry::keys());
    }

    public function test_unknown_product_is_not_registered(): void
    {
        $this->assertFalse(ProductRegistry::exists('nexterra'));
    }

    public function test_resolves_property_suggestion_strategy(): void
    {
        $this->assertInstanceOf(
            PropertyManagementSuggestionStrategy::class,
            ProductRegistry::suggestionStrategy('property_management')
        );
    }

    public function test_unregistered_product_falls_back_to_property_strategy(): void
    {
        // The engine must never die on a legacy/unknown product.
        $this->assertInstanceOf(
            PropertyManagementSuggestionStrategy::class,
            ProductRegistry::suggestionStrategy('some_unregistered_product')
        );
    }
}
