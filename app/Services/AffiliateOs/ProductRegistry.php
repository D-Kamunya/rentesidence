<?php

namespace App\Services\AffiliateOs;

use App\Services\Commission\CommissionRuleStrategy;
use App\Services\Commission\PropertySalesCommissionStrategy;
use App\Services\Suggestions\LeadSuggestionStrategy;
use App\Services\Suggestions\PropertySalesSuggestionStrategy;

/**
 * Reads the Affiliate-OS product registry (config/affiliate_os.php) — the single
 * place that knows which products the network works and which strategies serve
 * each. The OS machinery (suggestion engine, commission ledger) asks the registry
 * for a product's strategy instead of hardcoding one, so adding a spoke is a
 * config change. See docs/affiliate-os-design.md.
 */
class ProductRegistry
{
    /** The product key legacy/untagged rows belong to. */
    public static function default(): string
    {
        return (string) config('affiliate_os.default_product', 'property_sales');
    }

    /** All configured product keys. */
    public static function keys(): array
    {
        return array_keys((array) config('affiliate_os.products', []));
    }

    /** Whether a product key is registered. */
    public static function exists(string $product): bool
    {
        return array_key_exists($product, (array) config('affiliate_os.products', []));
    }

    /** A product's config bag, falling back to the default product for unknown keys. */
    public static function config(string $product): array
    {
        $products = (array) config('affiliate_os.products', []);

        return $products[$product] ?? $products[self::default()] ?? [];
    }

    /**
     * Resolve a product's suggestion strategy. Falls back to property-sales so the
     * engine never dies on an unregistered/legacy product — it just nudges with the
     * original motion. Returns an instance ready to produce candidates.
     */
    public static function suggestionStrategy(string $product): LeadSuggestionStrategy
    {
        $class = self::config($product)['suggestion_strategy'] ?? PropertySalesSuggestionStrategy::class;

        return app($class);
    }

    /**
     * Resolve a product's commission rule strategy. Falls back to property-sales so
     * the ledger never dies on a legacy/unregistered product.
     */
    public static function commissionStrategy(string $product): CommissionRuleStrategy
    {
        $class = self::config($product)['commission_strategy'] ?? PropertySalesCommissionStrategy::class;

        return app($class);
    }
}
