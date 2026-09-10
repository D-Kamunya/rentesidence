<?php

namespace Tests\Feature\Centresidence;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Owner sets their own utility tariff. The controller enforces the ONE hard guard (system-integrity
 * floor — tariff must cover our commission) + IDOR scoping; the ceiling is advisory only. See
 * token-tariff-ownership.
 */
class OwnerTariffUpdateTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $defs = [
            'modules'             => fn ($t) => [$t->id(), $t->string('key')->nullable(), $t->boolean('is_metered')->default(true), $t->string('name')->nullable()],
            'properties'          => fn ($t) => [$t->id(), $t->string('city')->nullable()],
            'property_modules'    => fn ($t) => [$t->id(), $t->unsignedBigInteger('owner_id')->nullable(), $t->unsignedBigInteger('module_id')->nullable(), $t->unsignedBigInteger('property_id')->nullable(), $t->unsignedBigInteger('property_unit_id')->nullable(), $t->string('status')->default('active')],
            'module_token_config' => fn ($t) => [$t->id(), $t->unsignedBigInteger('property_module_id')->nullable(), $t->decimal('units_per_kes', 12, 4)->nullable(), $t->decimal('centresidence_commission_per_token_unit', 12, 4)->default(0), $t->decimal('owner_revenue_per_token_unit', 12, 4)->nullable(), $t->string('token_unit_label')->nullable(), $t->boolean('is_active')->default(true)],
        ];
        foreach ($defs as $name => $cols) {
            if (! Schema::hasTable($name)) {
                Schema::create($name, function ($t) use ($cols) { $cols($t); $t->timestamps(); $t->softDeletes(); });
            }
        }
    }

    /** @return array{0:int,1:int} [propertyModuleId, tokenConfigId] */
    private function makeModule(int $ownerId, bool $metered, string $key, float $units, float $commission): array
    {
        $moduleId = DB::table('modules')->insertGetId(['key' => $key, 'is_metered' => $metered, 'name' => $key]);
        $propId   = DB::table('properties')->insertGetId(['city' => 'Nairobi']);
        $pmId     = DB::table('property_modules')->insertGetId(['owner_id' => $ownerId, 'module_id' => $moduleId, 'property_id' => $propId, 'status' => 'active']);
        $tcId     = DB::table('module_token_config')->insertGetId(['property_module_id' => $pmId, 'units_per_kes' => $units, 'centresidence_commission_per_token_unit' => $commission, 'token_unit_label' => 'Litres', 'is_active' => true]);

        return [$pmId, $tcId];
    }

    private function submitTariff(int $ownerId, array $data)
    {
        return $this->withoutMiddleware()
            ->actingAs((new User)->forceFill(['id' => $ownerId]))
            ->post(route('owner.devices.tariff.update'), $data);
    }

    /** @test */
    public function a_valid_tariff_is_saved_and_owner_revenue_re_derived(): void
    {
        [$pmId, $tcId] = $this->makeModule(10, true, 'water_meter', 5, 0);

        $this->submitTariff(10, ['property_module_id' => $pmId, 'units_per_kes' => 4])
            ->assertRedirect();

        $this->assertEqualsWithDelta(4.0, (float) DB::table('module_token_config')->where('id', $tcId)->value('units_per_kes'), 0.001);
        // owner_revenue = price(1/4=0.25) - commission(0) = 0.25
        $this->assertEqualsWithDelta(0.25, (float) DB::table('module_token_config')->where('id', $tcId)->value('owner_revenue_per_token_unit'), 0.001);
    }

    /** @test */
    public function a_tariff_below_the_commission_floor_is_rejected(): void
    {
        // Gas commission 0.02/unit; units 100 → price 0.01 < 0.02 → owner revenue negative → blocked.
        [$pmId, $tcId] = $this->makeModule(10, true, 'gas_meter', 5, 0.02);

        $res = $this->submitTariff(10, ['property_module_id' => $pmId, 'units_per_kes' => 100]);
        $res->assertRedirect();
        $res->assertSessionHas('error');

        // Unchanged — the floor blocked the save.
        $this->assertEqualsWithDelta(5.0, (float) DB::table('module_token_config')->where('id', $tcId)->value('units_per_kes'), 0.001);
    }

    /** @test */
    public function an_owner_cannot_set_another_owners_tariff(): void
    {
        [$pmId] = $this->makeModule(10, true, 'water_meter', 5, 0); // belongs to owner 10

        $this->submitTariff(99, ['property_module_id' => $pmId, 'units_per_kes' => 4]) // owner 99 attacks
            ->assertNotFound();
    }
}
