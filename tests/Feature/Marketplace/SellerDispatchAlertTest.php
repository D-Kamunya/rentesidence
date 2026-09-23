<?php

namespace Tests\Feature\Marketplace;

use App\Jobs\SendSellerDispatchAlertJob;
use App\Jobs\SendSmsJob;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Seller-side dispatch alert on a PAID marketplace order. The OWNER (seller) is always told to
 * organize dispatch (in-app), and when they delegate to the on-site caretaker
 * (owners.caretaker_dispatch_enabled) the MAINTAINER for the buyer's property is alerted too.
 * Both SMS are carved from the OWNER's credit pool (SendSmsJob ownerUserId = owner). A non-paid
 * order alerts nobody. See marketplace-comms-notifications.
 */
class SellerDispatchAlertTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        foreach ([
            'users'    => fn ($t) => [$t->id(), $t->string('email')->nullable(), $t->string('first_name')->nullable(), $t->string('name')->nullable(), $t->string('contact_number')->nullable(), $t->integer('role')->nullable()],
            'owners'   => fn ($t) => [$t->id(), $t->unsignedBigInteger('user_id')->nullable(), $t->boolean('caretaker_dispatch_enabled')->default(true)],
            'products' => fn ($t) => [$t->id(), $t->unsignedBigInteger('owner_user_id')->nullable(), $t->decimal('price', 12, 2)->default(0)],
            'product_orders' => fn ($t) => [$t->id(), $t->string('order_id')->nullable(), $t->integer('payment_status')->default(0), $t->decimal('transaction_amount', 12, 2)->nullable(), $t->decimal('amount', 12, 2)->nullable(), $t->unsignedBigInteger('user_id')->nullable(), $t->string('settlement_status')->nullable()],
            'product_order_items' => fn ($t) => [$t->id(), $t->unsignedBigInteger('product_order_id')->nullable(), $t->unsignedBigInteger('product_id')->nullable(), $t->integer('quantity')->default(1)],
            'tenants'    => fn ($t) => [$t->id(), $t->unsignedBigInteger('user_id')->nullable(), $t->unsignedBigInteger('property_id')->nullable()],
            'properties' => fn ($t) => [$t->id(), $t->unsignedBigInteger('maintainer_id')->nullable()],
            'notifications' => fn ($t) => [$t->id(), $t->string('title')->nullable(), $t->text('body')->nullable(), $t->string('url')->nullable(), $t->string('image')->nullable(), $t->unsignedBigInteger('user_id')->nullable(), $t->unsignedBigInteger('sender_id')->nullable(), $t->boolean('is_seen')->default(0), $t->timestamps()],
        ] as $name => $cols) {
            if (! Schema::hasTable($name)) {
                Schema::create($name, function ($t) use ($cols) { $cols($t); $t->softDeletes(); });
            }
        }
    }

    /**
     * Build a paid order graph. Returns [ownerUserId, maintainerUserId].
     */
    private function makeOrder(bool $caretakerEnabled, int $paymentStatus = 1): array
    {
        $ownerUserId      = DB::table('users')->insertGetId(['email' => 'owner@x.co', 'first_name' => 'Ola', 'name' => 'Ola', 'contact_number' => '254700000001']);
        $buyerUserId      = DB::table('users')->insertGetId(['email' => 'buyer@x.co', 'name' => 'Bee', 'contact_number' => '254700000002']);
        $maintainerUserId = DB::table('users')->insertGetId(['email' => 'care@x.co', 'name' => 'Caro', 'contact_number' => '254700000003']);

        $ownerId   = DB::table('owners')->insertGetId(['user_id' => $ownerUserId, 'caretaker_dispatch_enabled' => $caretakerEnabled]);
        $productId = DB::table('products')->insertGetId(['owner_user_id' => $ownerId, 'price' => 1500]); // products.owner_user_id = owners.id
        $propId    = DB::table('properties')->insertGetId(['maintainer_id' => $maintainerUserId]);
        DB::table('tenants')->insert(['user_id' => $buyerUserId, 'property_id' => $propId]);

        $orderId = DB::table('product_orders')->insertGetId([
            'order_id' => 'ORD-1001', 'payment_status' => $paymentStatus,
            'transaction_amount' => 1500, 'amount' => 1500, 'user_id' => $buyerUserId, 'settlement_status' => 'held',
        ]);
        DB::table('product_order_items')->insert(['product_order_id' => $orderId, 'product_id' => $productId, 'quantity' => 1]);

        return [$orderId, $ownerUserId, $maintainerUserId];
    }

    private function ownerIdOf(SendSmsJob $job): ?int
    {
        $r = new \ReflectionProperty($job, 'ownerUserId');
        $r->setAccessible(true);
        return $r->getValue($job);
    }

    /** @test */
    public function paid_order_with_caretaker_off_alerts_only_the_owner(): void
    {
        Bus::fake([SendSmsJob::class]);
        [$orderId, $ownerUserId] = $this->makeOrder(caretakerEnabled: false);

        (new SendSellerDispatchAlertJob($orderId))->handle();

        // Owner in-app notification created.
        $this->assertSame(1, DB::table('notifications')->where('user_id', $ownerUserId)->count());
        // Exactly one SMS, carved from the owner's credit pool.
        Bus::assertDispatchedTimes(SendSmsJob::class, 1);
        Bus::assertDispatched(SendSmsJob::class, fn ($job) => $this->ownerIdOf($job) === $ownerUserId);
    }

    /** @test */
    public function paid_order_with_caretaker_on_alerts_owner_and_maintainer_both_on_owner_credits(): void
    {
        Bus::fake([SendSmsJob::class]);
        [$orderId, $ownerUserId, $maintainerUserId] = $this->makeOrder(caretakerEnabled: true);

        (new SendSellerDispatchAlertJob($orderId))->handle();

        // Owner + maintainer both get an in-app notification.
        $this->assertSame(1, DB::table('notifications')->where('user_id', $ownerUserId)->count());
        $this->assertSame(1, DB::table('notifications')->where('user_id', $maintainerUserId)->count());
        // Two SMS (owner + maintainer), BOTH drawing on the owner's credit pool.
        Bus::assertDispatchedTimes(SendSmsJob::class, 2);
        Bus::assertDispatched(SendSmsJob::class, fn ($job) => $this->ownerIdOf($job) === $ownerUserId);
    }

    /** @test */
    public function non_paid_order_alerts_nobody(): void
    {
        Bus::fake([SendSmsJob::class]);
        [$orderId] = $this->makeOrder(caretakerEnabled: true, paymentStatus: 0); // pending

        (new SendSellerDispatchAlertJob($orderId))->handle();

        $this->assertSame(0, DB::table('notifications')->count());
        Bus::assertNotDispatched(SendSmsJob::class);
    }
}
