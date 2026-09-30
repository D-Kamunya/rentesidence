<?php

namespace Tests\Feature\Credit;

use App\Models\OwnerPackage;
use App\Models\Package;
use App\Services\Credit\CreditService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Per-domain SMS pricing: an owner's plan may set a lower per-credit price (paid domains cheaper
 * than Free), resolved by CreditService and floored above our gateway cost. Isolated sqlite harness.
 */
class SmsDomainPricingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['database.connections.smsp_sqlite' => [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '', 'foreign_key_constraints' => false,
        ]]);
        config(['database.default' => 'smsp_sqlite']);
        DB::purge('smsp_sqlite');
        \Illuminate\Database\Eloquent\Model::unguard();

        // getOption reads config('settings').
        config(['settings' => ['sms_credit_price' => 1.00, 'sms_cost_floor' => 0.80]]);

        Schema::create('packages', function ($t) {
            $t->id();
            $t->string('name')->nullable();
            $t->string('pricing_model')->nullable();
            $t->decimal('sms_price_per_credit', 8, 2)->nullable();
            $t->unsignedTinyInteger('status')->nullable();
            $t->softDeletes();
            $t->timestamps();
        });
        Schema::create('owner_packages', function ($t) {
            $t->id();
            $t->unsignedBigInteger('user_id');
            $t->unsignedBigInteger('package_id');
            $t->string('pricing_model')->nullable();
            $t->unsignedTinyInteger('status')->nullable();
            $t->date('end_date')->nullable();
            $t->softDeletes();
            $t->timestamps();
        });
    }

    private function ownerOnPlan(int $userId, ?float $smsPrice, string $model = 'subscription'): void
    {
        $pkg = Package::create(['name' => 'P' . $userId, 'pricing_model' => $model, 'sms_price_per_credit' => $smsPrice, 'status' => ACTIVE]);
        OwnerPackage::create([
            'user_id' => $userId, 'package_id' => $pkg->id, 'pricing_model' => $model,
            'status' => ACTIVE, 'end_date' => now()->addYear()->toDateString(),
        ]);
    }

    public function test_paid_plan_uses_its_own_lower_rate(): void
    {
        $this->ownerOnPlan(1, 0.90);
        $this->assertSame(0.90, CreditService::pricePerUnit('sms', 1));
        $this->assertSame(9.0, CreditService::amountForCredits('sms', 10, 1)); // 10 × 0.90
    }

    public function test_null_plan_rate_falls_back_to_global(): void
    {
        $this->ownerOnPlan(2, null, 'free');
        $this->assertSame(1.00, CreditService::pricePerUnit('sms', 2)); // global sms_credit_price
    }

    public function test_rate_below_cost_is_floored(): void
    {
        $this->ownerOnPlan(3, 0.50); // below the 0.80 cost floor
        $this->assertSame(0.80, CreditService::pricePerUnit('sms', 3));
    }

    public function test_no_owner_uses_the_global_price(): void
    {
        $this->assertSame(1.00, CreditService::pricePerUnit('sms'));
        $this->assertSame(1.00, CreditService::pricePerUnit('sms', 999)); // owner with no active plan → global
    }
}
