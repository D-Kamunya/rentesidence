<?php

namespace Tests\Feature\Owner;

use App\Services\OwnerAdvisor\OwnerSetupService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/** The owner setup checklist — steps derived from real data, completing as the owner sets up. */
class OwnerSetupChecklistTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['database.connections.setup_sqlite' => [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '', 'foreign_key_constraints' => false,
        ]]);
        config(['database.default' => 'setup_sqlite']);
        DB::purge('setup_sqlite');
        \Illuminate\Database\Eloquent\Model::unguard();

        Schema::create('properties', function ($t) { $t->id(); $t->unsignedBigInteger('owner_user_id'); $t->softDeletes(); $t->timestamps(); });
        Schema::create('property_units', function ($t) { $t->id(); $t->unsignedBigInteger('property_id'); $t->softDeletes(); $t->timestamps(); });
        Schema::create('tenants', function ($t) { $t->id(); $t->unsignedBigInteger('owner_user_id'); $t->unsignedTinyInteger('status')->nullable(); $t->softDeletes(); $t->timestamps(); });
        Schema::create('invoice_recurring_settings', function ($t) { $t->id(); $t->unsignedBigInteger('owner_user_id'); $t->softDeletes(); $t->timestamps(); });
    }

    private function svc(): OwnerSetupService
    {
        return app(OwnerSetupService::class);
    }

    public function test_a_fresh_owner_has_everything_to_do(): void
    {
        $c = $this->svc()->checklist(1);
        $this->assertSame(4, $c['total']);
        $this->assertSame(0, $c['done']);
        $this->assertFalse($c['complete']);
    }

    public function test_steps_complete_as_the_owner_sets_up(): void
    {
        \App\Models\Property::create(['id' => 10, 'owner_user_id' => 1]);
        $this->assertSame(1, $this->svc()->checklist(1)['done']); // property only

        \App\Models\PropertyUnit::create(['property_id' => 10]);
        $this->assertSame(2, $this->svc()->checklist(1)['done']); // + units

        \App\Models\Tenant::create(['owner_user_id' => 1, 'status' => 1]);
        \App\Models\InvoiceRecurringSetting::create(['owner_user_id' => 1]);

        $done = $this->svc()->checklist(1);
        $this->assertSame(4, $done['done']);
        $this->assertTrue($done['complete']);
    }

    public function test_checklist_is_per_owner(): void
    {
        \App\Models\Property::create(['owner_user_id' => 1]);
        $this->assertSame(1, $this->svc()->checklist(1)['done']);
        $this->assertSame(0, $this->svc()->checklist(2)['done']); // a different owner sees nothing done
    }
}
