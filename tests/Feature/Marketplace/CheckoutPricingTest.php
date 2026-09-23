<?php

namespace Tests\Feature\Marketplace;

use App\Http\Controllers\ProductPaymentController;
use App\Models\Product;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * SECURITY REGRESSION (pentest finding #2, 2026-09-06): the marketplace checkout
 * must charge a SERVER-computed total (sum of real product price × quantity),
 * never the client-supplied `cartTotal` — otherwise a buyer could POST
 * cartTotal=1 for expensive goods and pay a fraction of the price.
 */
class CheckoutPricingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (! Schema::hasTable('products')) {
            Schema::create('products', function ($t) {
                $t->id();
                $t->string('name')->nullable();
                $t->decimal('price', 12, 2)->default(0);
                $t->timestamps();
            });
        }
    }

    private function serverTotal(array $products, float $clientTotal): float
    {
        $ctrl = new ProductPaymentController();
        $m = new \ReflectionMethod($ctrl, 'computeServerCartTotal');
        $m->setAccessible(true);

        return $m->invoke($ctrl, $products, $clientTotal);
    }

    public function test_tampered_cart_total_is_ignored_for_real_product_prices(): void
    {
        $product = (new Product)->forceFill(['name' => 'Widget', 'price' => 1200])->save()
            ? Product::latest('id')->first()
            : null;

        // Attacker claims cartTotal = 1 for 3 units of a KES 1200 product.
        $charged = $this->serverTotal([['id' => $product->id, 'quantity' => 3]], 1.0);

        $this->assertSame(3600.0, $charged); // real price wins, not the client's 1
    }

    public function test_unknown_product_is_rejected(): void
    {
        $this->expectException(\Exception::class);
        $this->serverTotal([['id' => 999999, 'quantity' => 1]], 1.0);
    }
}
