<?php

namespace Tests\Feature\Tenant;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * The self-registered "Tenant Helper" ownerless contract (slice 1).
 *
 * A Helper is created as a tenant with a CLOSE tenancy row and no owner_user_id — the SAME shape
 * a moved-out tenant lands in, so it flows through the existing ownerless machinery. These lock:
 *   - a Helper reads as ownerless (isOwnerlessTenant) so the standalone app + guards apply;
 *   - a Helper is distinguishable from a moved-out tenant (isHelperTenant) so it does NOT show
 *     the "your tenancy has ended" banner;
 *   - an active linked tenant is neither.
 */
class TenantHelperOwnerlessTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['database.connections.th_sqlite' => [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
        ]]);
        config(['database.default' => 'th_sqlite']);
        DB::purge('th_sqlite');

        Schema::create('users', function ($t) {
            $t->id();
            $t->string('first_name')->nullable();
            $t->string('last_name')->nullable();
            $t->string('email')->nullable();
            $t->string('contact_number')->nullable();
            $t->unsignedTinyInteger('role')->nullable();
            $t->unsignedBigInteger('owner_user_id')->nullable();
            $t->unsignedTinyInteger('status')->nullable();
            $t->softDeletes();
            $t->timestamps();
        });

        Schema::create('tenants', function ($t) {
            $t->id();
            $t->unsignedBigInteger('user_id');
            $t->string('job')->nullable();
            $t->integer('family_member')->nullable();
            $t->unsignedBigInteger('property_id')->nullable();
            $t->unsignedBigInteger('unit_id')->nullable();
            $t->unsignedTinyInteger('status')->nullable();
            $t->softDeletes();
            $t->timestamps();
        });
    }

    private function makeTenant(?int $ownerUserId, int $tenantStatus): User
    {
        $user = new User();
        $user->role = USER_ROLE_TENANT;
        $user->owner_user_id = $ownerUserId;
        $user->save();

        $tenant = new Tenant();
        $tenant->user_id = $user->id;
        $tenant->status = $tenantStatus;
        $tenant->save();

        return $user->fresh();
    }

    public function test_self_registered_helper_is_ownerless_and_a_helper(): void
    {
        $helper = $this->makeTenant(null, TENANT_STATUS_CLOSE);

        $this->assertTrue($helper->isOwnerlessTenant(), 'A CLOSE tenancy row makes the Helper ownerless.');
        $this->assertTrue($helper->isHelperTenant(), 'No owner_user_id marks it a never-had-a-landlord Helper.');
    }

    public function test_moved_out_tenant_is_ownerless_but_not_a_helper(): void
    {
        // Retains owner_user_id (persists after close) → ownerless, but "tenancy ended", not a Helper.
        $movedOut = $this->makeTenant(999, TENANT_STATUS_CLOSE);

        $this->assertTrue($movedOut->isOwnerlessTenant());
        $this->assertFalse($movedOut->isHelperTenant(), 'A former tenant kept its owner link — not a Helper.');
    }

    public function test_active_linked_tenant_is_neither_ownerless_nor_helper(): void
    {
        $active = $this->makeTenant(999, TENANT_STATUS_ACTIVE);

        $this->assertFalse($active->isOwnerlessTenant());
        $this->assertFalse($active->isHelperTenant());
    }
}
