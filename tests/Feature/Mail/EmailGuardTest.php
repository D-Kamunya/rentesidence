<?php

namespace Tests\Feature\Mail;

use App\Models\EmailSuppression;
use App\Services\Mail\EmailGuard;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Deliverability safety net — EmailGuard::shouldSuppress().
 *
 * Automated mail to dead/test addresses hard-bounces and tanks sender reputation (which it did in
 * prod). The guard suppresses the obvious ones before they're sent, auto-recording them so an admin
 * can review. These lock: reserved/test domains + placeholder local-parts are blocked, the explicit
 * suppression list is honoured, real addresses pass, and a heuristic hit lands on the list.
 */
class EmailGuardTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['database.connections.eg_sqlite' => [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
        ]]);
        config(['database.default' => 'eg_sqlite']);
        DB::purge('eg_sqlite');

        Schema::create('email_suppressions', function ($t) {
            $t->id();
            $t->string('email')->unique();
            $t->string('reason')->nullable();
            $t->string('source', 20)->default('auto');
            $t->timestamps();
        });
    }

    public function test_reserved_and_test_domains_are_suppressed(): void
    {
        $this->assertNotNull(EmailGuard::shouldSuppress('someone@example.com'));
        $this->assertNotNull(EmailGuard::shouldSuppress('jane@anything.test'));
        $this->assertNotNull(EmailGuard::shouldSuppress('jane@corp.invalid'));
        $this->assertNotNull(EmailGuard::shouldSuppress('root@localhost'));
    }

    public function test_placeholder_local_parts_are_suppressed(): void
    {
        $this->assertNotNull(EmailGuard::shouldSuppress('test@gmail.com'));
        $this->assertNotNull(EmailGuard::shouldSuppress('owner@gmail.com'));
        $this->assertNotNull(EmailGuard::shouldSuppress('noreply@gmail.com'));
        // System default/placeholder users left over from v1.
        $this->assertNotNull(EmailGuard::shouldSuppress('tenant@gmail.com'));
        $this->assertNotNull(EmailGuard::shouldSuppress('maintainer@gmail.com'));
    }

    public function test_real_addresses_pass(): void
    {
        $this->assertNull(EmailGuard::shouldSuppress('dennis.muriithi@gmail.com'));
        $this->assertNull(EmailGuard::shouldSuppress('jane.doe@company.co.ke'));
    }

    public function test_explicit_suppression_list_is_honoured(): void
    {
        EmailSuppression::create(['email' => 'real.but.bounced@gmail.com', 'reason' => 'hard bounce', 'source' => 'admin']);
        $this->assertSame('hard bounce', EmailGuard::shouldSuppress('real.but.bounced@gmail.com'));
    }

    public function test_heuristic_hit_is_recorded_for_admin_review(): void
    {
        EmailGuard::shouldSuppress('test@gmail.com');
        $this->assertDatabaseHas('email_suppressions', ['email' => 'test@gmail.com', 'source' => 'auto']);
    }

    public function test_malformed_input_is_allowed_through(): void
    {
        // Not the guard's job to reject malformed addresses — normal validation handles those.
        $this->assertNull(EmailGuard::shouldSuppress(''));
        $this->assertNull(EmailGuard::shouldSuppress(null));
        $this->assertNull(EmailGuard::shouldSuppress('not-an-email'));
    }
}
