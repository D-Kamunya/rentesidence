<?php

namespace Tests\Feature\Centresidence;

use App\Centresidence\Models\Module;
use App\Centresidence\Models\PropertyModule;
use App\Centresidence\Models\TokenPurchase;
use App\Centresidence\Models\UtilityWallet;
use App\Centresidence\Services\TokenPurchaseCollectionService;
use App\Models\OwnerWallet;
use Illuminate\Support\Facades\DB;

/**
 * Completion-map C1 — the tenant front door to the Token Engine. Covers the
 * discovery/authorization surface (which modules a tenant may buy for), the
 * driver-gated settlement (log settles immediately + credits both wallets), and
 * the IDOR + idempotency guards on settle. The engine's economics are proven in
 * TokenEngineTest; this fixes the boundary a real request crosses.
 */
class TenantTokenPurchaseTest extends CentresidenceDatabaseTestCase
{
    private const TENANT = 3; // occupies property 1 / unit 1
    private const OUTSIDER = 4; // occupies property 2 / unit 2

    protected function setUp(): void
    {
        parent::setUp();

        DB::table('users')->insert([
            ['id' => self::TENANT, 'first_name' => 'Tenant', 'last_name' => 'One'],
            ['id' => self::OUTSIDER, 'first_name' => 'Tenant', 'last_name' => 'Two'],
        ]);
        DB::table('tenants')->insert([
            ['user_id' => self::TENANT, 'property_id' => 1, 'unit_id' => 1, 'status' => 1],
            ['user_id' => self::OUTSIDER, 'property_id' => 2, 'unit_id' => 2, 'status' => 1],
        ]);
    }

    private function meteredModule(int $propertyId, ?int $unitId = null, bool $configActive = true): PropertyModule
    {
        $module = Module::create(['key' => 'water_' . uniqid(), 'name' => 'Water Meter', 'is_metered' => true, 'token_unit_label' => 'Litres']);
        $pm = PropertyModule::create([
            'property_id'        => $propertyId,
            'property_unit_id'   => $unitId,
            'module_id'          => $module->id,
            'owner_id'           => 1,
            'active_meter_count' => 5,
            'billing_model'      => PropertyModule::BILLING_SUBSCRIPTION,
            'status'             => PropertyModule::STATUS_ACTIVE,
        ]);
        $pm->tokenConfig()->create([
            'token_unit_label'                        => 'Litres',
            'units_per_kes'                           => '5',
            'centresidence_commission_per_token_unit' => '0.02',
            'is_active'                               => $configActive,
        ]);

        return $pm->fresh('tokenConfig');
    }

    private function service(): TokenPurchaseCollectionService
    {
        return app(TokenPurchaseCollectionService::class);
    }

    public function test_lists_only_metered_modules_on_the_tenants_own_property(): void
    {
        $mine   = $this->meteredModule(1, 1);
        $shared = $this->meteredModule(1, null); // property-level → applies to the unit too
        $this->meteredModule(2, 2);              // another property — must not appear

        // A non-metered module on my property must not appear.
        $nonMetered = Module::create(['key' => 'access', 'name' => 'Access', 'is_metered' => false]);
        PropertyModule::create([
            'property_id' => 1, 'module_id' => $nonMetered->id, 'owner_id' => 1,
            'active_meter_count' => 0, 'billing_model' => PropertyModule::BILLING_SUBSCRIPTION,
            'status' => PropertyModule::STATUS_ACTIVE,
        ]);

        // An inactive-config metered module must not appear.
        $this->meteredModule(1, 1, configActive: false);

        $ids = $this->service()->modulesFor(self::TENANT)->pluck('id')->all();

        sort($ids);
        $this->assertSame([$mine->id, $shared->id], $ids);
    }

    public function test_authorized_module_rejects_a_module_on_another_unit(): void
    {
        $foreign = $this->meteredModule(2, 2);

        $this->assertNull($this->service()->authorizedModule(self::TENANT, $foreign->id));
    }

    public function test_log_driver_initiate_credits_tenant_and_owner_wallets_immediately(): void
    {
        config()->set('centresidence.collections.driver', 'log');
        $pm = $this->meteredModule(1, 1);

        $result = $this->service()->initiate($pm, self::TENANT, 100.0);

        $this->assertTrue($result['success']);
        $this->assertTrue($result['settled']);

        // Tenant wallet credited full units (KES 100 × 5 = 500 litres).
        $wallet = UtilityWallet::where('tenant_user_id', self::TENANT)->first();
        $this->assertSame('500.0000', $wallet->balance_units);

        // Owner net revenue (100 − 500×0.02 = 90) reached the owner wallet.
        $this->assertSame(90.0, (float) OwnerWallet::forUser(1)->balance);
    }

    public function test_settle_refuses_an_unauthorized_module_and_credits_nothing(): void
    {
        $foreign = $this->meteredModule(2, 2); // belongs to the outsider's property

        $purchase = $this->service()->settle($foreign->id, self::TENANT, 100.0, 'MPESA-XYZ');

        $this->assertNull($purchase);
        $this->assertSame(0, TokenPurchase::count());
    }

    public function test_settle_is_idempotent_on_the_payment_reference(): void
    {
        $pm = $this->meteredModule(1, 1);

        $first  = $this->service()->settle($pm->id, self::TENANT, 100.0, 'MPESA-DUP');
        $second = $this->service()->settle($pm->id, self::TENANT, 100.0, 'MPESA-DUP');

        $this->assertNotNull($first);
        $this->assertSame($first->id, $second->id);
        $this->assertSame(1, TokenPurchase::count());
        // Credited once only.
        $this->assertSame('500.0000', UtilityWallet::where('tenant_user_id', self::TENANT)->first()->balance_units);
    }

    public function test_has_utilities_reflects_availability(): void
    {
        $this->assertFalse($this->service()->hasUtilities(self::TENANT));

        $this->meteredModule(1, 1);

        $this->assertTrue($this->service()->hasUtilities(self::TENANT));
        // The outsider on property 2 still sees nothing from property 1's module.
        $this->assertFalse($this->service()->hasUtilities(self::OUTSIDER));
    }
}
