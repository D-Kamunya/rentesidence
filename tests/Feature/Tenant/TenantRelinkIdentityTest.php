<?php

namespace Tests\Feature\Tenant;

use App\Models\Tenant;
use App\Models\User;
use App\Services\TenantService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use ReflectionMethod;
use Tests\TestCase;

/**
 * Detect-and-link onboarding, Phase 1 — TenantService::resolveTenantIdentity().
 *
 * When an owner adds a tenant whose email/phone already exists, the old behaviour was a hard
 * "already taken" wall (and, because unique: ignores the soft-delete scope, a deleted tenant
 * permanently burned that identity). The resolver instead:
 *   - RECONNECTS the owner's OWN returning tenant (closed or soft-deleted) → returns the user;
 *   - BLOCKS a non-tenant account, someone else's tenant, or a person already in an ACTIVE
 *     tenancy with this owner → returns a message, no user.
 * Cross-owner / unclaimed-Helper linking (needs consent) is Phase 2 and is NOT reconnected here.
 */
class TenantRelinkIdentityTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['database.connections.rl_sqlite' => [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
        ]]);
        config(['database.default' => 'rl_sqlite']);
        DB::purge('rl_sqlite');

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
            $t->unsignedBigInteger('owner_user_id')->nullable();
            $t->unsignedBigInteger('property_id')->nullable();
            $t->unsignedBigInteger('unit_id')->nullable();
            $t->unsignedTinyInteger('status')->nullable();
            $t->softDeletes();
            $t->timestamps();
        });
    }

    private function resolve(?string $email, ?string $contact): array
    {
        $svc = new TenantService();
        $m = new ReflectionMethod($svc, 'resolveTenantIdentity');
        $m->setAccessible(true);
        return $m->invoke($svc, $email, $contact);
    }

    private function owner(): User
    {
        $u = new User();
        $u->role = USER_ROLE_OWNER;
        $u->save();
        return $u;
    }

    private function makeTenant(int $ownerUserId, int $status, string $email, string $phone, bool $trashed = false): User
    {
        $u = new User();
        $u->first_name = 'Ret';
        $u->role = USER_ROLE_TENANT;
        $u->owner_user_id = $ownerUserId;
        $u->email = $email;
        $u->contact_number = $phone;
        $u->status = ACTIVE;
        $u->save();

        $t = new Tenant();
        $t->user_id = $u->id;
        $t->owner_user_id = $ownerUserId;
        $t->status = $status;
        $t->save();

        if ($trashed) {
            $t->delete();
            $u->delete();
            $u->refresh();
        }
        return $u;
    }

    public function test_brand_new_identity_creates_fresh(): void
    {
        $this->be($this->owner());
        [$user, $err] = $this->resolve('new@example.test', '0700000001');
        $this->assertNull($user);
        $this->assertNull($err);
    }

    public function test_own_closed_tenant_is_reconnected(): void
    {
        $owner = $this->owner();
        $this->makeTenant($owner->id, TENANT_STATUS_CLOSE, 'closed@example.test', '0700000002');
        $this->be($owner);

        [$user, $err] = $this->resolve('closed@example.test', '0700000002');
        $this->assertNull($err);
        $this->assertNotNull($user);
        $this->assertSame('closed@example.test', $user->email);
    }

    public function test_own_soft_deleted_tenant_is_reconnected(): void
    {
        $owner = $this->owner();
        $this->makeTenant($owner->id, TENANT_STATUS_CLOSE, 'gone@example.test', '0700000003', trashed: true);
        $this->be($owner);

        // Matched by phone; the user row is soft-deleted (the identity the unique: wall used to burn).
        [$user, $err] = $this->resolve(null, '0700000003');
        $this->assertNull($err);
        $this->assertNotNull($user);
        $this->assertTrue($user->trashed());
    }

    public function test_own_active_tenant_is_blocked_not_duplicated(): void
    {
        $owner = $this->owner();
        $this->makeTenant($owner->id, TENANT_STATUS_ACTIVE, 'active@example.test', '0700000004');
        $this->be($owner);

        [$user, $err] = $this->resolve('active@example.test', '0700000004');
        $this->assertNull($user);
        $this->assertNotNull($err);
    }

    public function test_another_owners_tenant_is_blocked(): void
    {
        $me = $this->owner();
        $other = $this->owner();
        $this->makeTenant($other->id, TENANT_STATUS_CLOSE, 'theirs@example.test', '0700000005');
        $this->be($me);

        [$user, $err] = $this->resolve('theirs@example.test', '0700000005');
        $this->assertNull($user);
        $this->assertNotNull($err);
    }

    public function test_non_tenant_account_is_blocked(): void
    {
        $owner = $this->owner();
        // An owner/other-role account holding that email must never be reconnected as a tenant.
        $stranger = new User();
        $stranger->role = USER_ROLE_OWNER;
        $stranger->email = 'owner@example.test';
        $stranger->contact_number = '0700000006';
        $stranger->save();
        $this->be($owner);

        [$user, $err] = $this->resolve('owner@example.test', '0700000006');
        $this->assertNull($user);
        $this->assertNotNull($err);
    }
}
