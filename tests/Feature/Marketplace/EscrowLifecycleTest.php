<?php

namespace Tests\Feature\Marketplace;

use App\Models\OwnerWallet;
use App\Models\ProductOrder;
use App\Services\CommissionService;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * The SACRED marketplace money invariants (escrow). These had no automated coverage (the deferred
 * "build a ProductOrder factory" item in marketplace-domain-pass). Locked in here:
 *   - PAID holds funds with the platform (owner NOT credited).
 *   - Release credits the owner NET exactly once (idempotent), commission booked.
 *   - Reversal (refund of a RELEASED order) nets every wallet sum back to zero (no double-count),
 *     idempotent.
 *   - Refund of a HELD order claws back NOTHING (escrow's whole point — no owner-negative).
 * A KES 1000 order with no category + no package resolves to the 3% floor → commission 30, net 970.
 */
class EscrowLifecycleTest extends TestCase
{
    private CommissionService $svc;

    protected function setUp(): void
    {
        parent::setUp();
        Bus::fake(); // holdOnPayment dispatches SendSellerDispatchAlertJob — capture, don't run it.

        $tables = [
            'owners'          => fn ($t) => [$t->id(), $t->unsignedBigInteger('user_id')->nullable()],
            'owner_wallets'   => fn ($t) => [$t->id(), $t->unsignedBigInteger('user_id')->nullable(), $t->decimal('balance', 14, 2)->default(0), $t->timestamps()],
            'packages'        => fn ($t) => [$t->id(), $t->boolean('is_default')->default(0), $t->decimal('commission_markup', 8, 2)->nullable(), $t->decimal('commission_discount', 8, 2)->nullable()],
            'owner_packages'  => fn ($t) => [$t->id(), $t->unsignedBigInteger('user_id')->nullable(), $t->integer('status')->default(0), $t->unsignedBigInteger('package_id')->nullable()],
            'products'        => fn ($t) => [$t->id(), $t->unsignedBigInteger('owner_user_id')->nullable(), $t->unsignedBigInteger('product_category_id')->nullable(), $t->decimal('price', 12, 2)->default(0)],
            'product_orders'  => fn ($t) => [$t->id(), $t->string('order_id')->nullable(), $t->integer('payment_status')->default(0), $t->decimal('transaction_amount', 12, 2)->nullable(), $t->unsignedBigInteger('user_id')->nullable(), $t->string('settlement_status')->nullable(), $t->timestamp('settlement_released_at')->nullable(), $t->timestamps()],
            'product_order_items' => fn ($t) => [$t->id(), $t->unsignedBigInteger('product_order_id')->nullable(), $t->unsignedBigInteger('product_id')->nullable(), $t->integer('quantity')->default(1)],
            'wallet_transactions' => fn ($t) => [$t->id(), $t->unsignedBigInteger('owner_wallet_id')->nullable(), $t->unsignedBigInteger('product_order_id')->nullable(), $t->unsignedBigInteger('invoice_order_id')->nullable(), $t->string('transaction_source')->nullable(), $t->decimal('gross_amount', 14, 2)->default(0), $t->decimal('commission_rate', 8, 2)->default(0), $t->decimal('commission_amount', 14, 2)->default(0), $t->decimal('net_amount', 14, 2)->default(0), $t->string('type')->nullable(), $t->text('description')->nullable(), $t->timestamps()],
        ];
        foreach ($tables as $name => $cols) {
            if (! Schema::hasTable($name)) {
                Schema::create($name, function ($t) use ($cols) { $cols($t); $t->softDeletes(); });
            }
        }

        $this->svc = new CommissionService();
    }

    /** @return array{0:ProductOrder,1:int} [order, ownerUserId] */
    private function makePaidOrder(float $gross = 1000): array
    {
        $ownerUserId = 501 + DB::table('owners')->count(); // synthetic users.id; no users table needed for the money path
        $ownerId     = DB::table('owners')->insertGetId(['user_id' => $ownerUserId]);
        $productId   = DB::table('products')->insertGetId(['owner_user_id' => $ownerId, 'price' => $gross]); // products.owner_user_id = owners.id
        $orderId     = DB::table('product_orders')->insertGetId([
            'order_id' => 'ORD-9001', 'payment_status' => 1, 'transaction_amount' => $gross, 'settlement_status' => null,
        ]);
        DB::table('product_order_items')->insert(['product_order_id' => $orderId, 'product_id' => $productId, 'quantity' => 1]);

        return [ProductOrder::with('orderItems.product')->find($orderId), $ownerUserId];
    }

    private function walletBalance(int $ownerUserId): float
    {
        return (float) OwnerWallet::forUser($ownerUserId)->balance;
    }

    /** @test */
    public function paid_order_holds_funds_and_credits_nobody(): void
    {
        [$order, $ownerUserId] = $this->makePaidOrder();

        $this->svc->holdOnPayment($order);

        $this->assertSame('held', $order->fresh()->settlement_status);
        $this->assertSame(0, DB::table('wallet_transactions')->count());
        $this->assertEqualsWithDelta(0.0, $this->walletBalance($ownerUserId), 0.001);
        Bus::assertDispatched(\App\Jobs\SendSellerDispatchAlertJob::class); // seller alerted on hold
    }

    /** @test */
    public function release_credits_the_owner_net_exactly_once(): void
    {
        [$order, $ownerUserId] = $this->makePaidOrder(1000);
        $this->svc->holdOnPayment($order);

        $first = $this->svc->releaseSettlement($order->fresh());

        $this->assertNotNull($first);
        $this->assertEqualsWithDelta(970.0, $this->walletBalance($ownerUserId), 0.001); // 1000 - 3% (30)
        $credit = DB::table('wallet_transactions')->where('type', 'credit')->first();
        $this->assertEqualsWithDelta(1000.0, (float) $credit->gross_amount, 0.001);
        $this->assertEqualsWithDelta(30.0, (float) $credit->commission_amount, 0.001);
        $this->assertEqualsWithDelta(970.0, (float) $credit->net_amount, 0.001);
        $this->assertSame('released', $order->fresh()->settlement_status);

        // Idempotent: a second release is a no-op (no double-credit).
        $this->assertNull($this->svc->releaseSettlement($order->fresh()));
        $this->assertSame(1, DB::table('wallet_transactions')->where('type', 'credit')->count());
        $this->assertEqualsWithDelta(970.0, $this->walletBalance($ownerUserId), 0.001);
    }

    /** @test */
    public function refund_of_a_released_order_nets_every_wallet_sum_to_zero(): void
    {
        [$order, $ownerUserId] = $this->makePaidOrder(1000);
        $this->svc->holdOnPayment($order);
        $this->svc->releaseSettlement($order->fresh());

        $reversal = $this->svc->reverseOrderCommission($order->fresh());

        $this->assertNotNull($reversal);
        $this->assertEqualsWithDelta(0.0, $this->walletBalance($ownerUserId), 0.001); // clawed back
        // Untyped sums (used by platform-commission/GMV reports) must net to zero for a refunded sale.
        $this->assertEqualsWithDelta(0.0, (float) DB::table('wallet_transactions')->sum('gross_amount'), 0.001);
        $this->assertEqualsWithDelta(0.0, (float) DB::table('wallet_transactions')->sum('commission_amount'), 0.001);
        $this->assertEqualsWithDelta(0.0, (float) DB::table('wallet_transactions')->sum('net_amount'), 0.001);

        // Idempotent: no double-reverse.
        $this->assertNull($this->svc->reverseOrderCommission($order->fresh()));
        $this->assertSame(1, DB::table('wallet_transactions')->where('type', 'refund')->count());
    }

    /** @test */
    public function refund_of_a_held_never_released_order_claws_back_nothing(): void
    {
        [$order, $ownerUserId] = $this->makePaidOrder(1000);
        $this->svc->holdOnPayment($order); // held, never released — owner was never credited

        $reversal = $this->svc->reverseOrderCommission($order->fresh());

        // The escrow win: nothing to reverse, no owner-negative, no clawback.
        $this->assertNull($reversal);
        $this->assertSame(0, DB::table('wallet_transactions')->count());
        $this->assertEqualsWithDelta(0.0, $this->walletBalance($ownerUserId), 0.001);
    }
}
