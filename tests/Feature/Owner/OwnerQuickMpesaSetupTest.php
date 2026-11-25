<?php

namespace Tests\Feature\Owner;

use App\Models\Gateway;
use App\Models\GatewayCurrency;
use App\Models\MpesaAccount;
use App\Models\User;
use App\Services\GatewayService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Inline M-Pesa quick-setup (dashboard checklist "get paid" step) — asserts it activates the owner's
 * gateway + stores a single ACTIVE Till/Paybill + locks KES at 1.00, and is additive (never wipes the
 * owner's other accounts, unlike the full settings form).
 */
class OwnerQuickMpesaSetupTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['database.connections.qm_sqlite' => [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '', 'foreign_key_constraints' => false,
        ]]);
        config(['database.default' => 'qm_sqlite']);
        DB::purge('qm_sqlite');
        \Illuminate\Database\Eloquent\Model::unguard();

        Schema::create('gateways', function ($t) {
            $t->id(); $t->unsignedBigInteger('owner_user_id')->nullable(); $t->string('title')->nullable();
            $t->string('slug')->nullable(); $t->tinyInteger('status')->nullable(); $t->tinyInteger('mode')->nullable();
            $t->string('url')->nullable(); $t->string('key')->nullable(); $t->string('secret')->nullable();
            $t->string('image')->nullable(); $t->softDeletes(); $t->timestamps();
        });
        Schema::create('mpesa_accounts', function ($t) {
            $t->id(); $t->unsignedBigInteger('owner_user_id'); $t->unsignedBigInteger('gateway_id')->nullable();
            $t->string('account_type')->nullable(); $t->string('paybill')->nullable(); $t->string('till_number')->nullable();
            $t->string('account_name')->nullable(); $t->string('passkey')->nullable(); $t->tinyInteger('status')->nullable();
            $t->softDeletes(); $t->timestamps();
        });
        Schema::create('gateway_currencies', function ($t) {
            $t->id(); $t->unsignedBigInteger('owner_user_id')->nullable(); $t->unsignedBigInteger('gateway_id');
            $t->string('currency')->nullable(); $t->decimal('conversion_rate', 16, 6)->nullable(); $t->softDeletes(); $t->timestamps();
        });
    }

    private function actAsOwner(int $id = 1): void
    {
        $u = new User(); $u->id = $id; $this->be($u);
    }

    public function test_saving_a_till_activates_gateway_account_and_locks_kes(): void
    {
        $this->actAsOwner();

        app(GatewayService::class)->quickMpesaSetup(['account_type' => 'TILLNUMBER', 'till_number' => '5203988']);

        $gateway = Gateway::where('owner_user_id', 1)->where('slug', 'mpesa')->first();
        $this->assertNotNull($gateway);
        $this->assertSame(ACTIVE, (int) $gateway->status);

        $acct = MpesaAccount::where('owner_user_id', 1)->first();
        $this->assertSame('TILLNUMBER', $acct->account_type);
        $this->assertSame('5203988', $acct->till_number);
        $this->assertNull($acct->paybill);
        $this->assertSame(ACTIVE, (int) $acct->status);

        $kes = GatewayCurrency::where('gateway_id', $gateway->id)->where('currency', 'KES')->first();
        $this->assertNotNull($kes);
        $this->assertEquals(1, (float) $kes->conversion_rate);
    }

    public function test_saving_a_paybill_stores_name_and_number(): void
    {
        $this->actAsOwner();

        app(GatewayService::class)->quickMpesaSetup([
            'account_type' => 'PAYBILL', 'paybill_number' => '400200', 'account_name' => 'Acme Rentals',
        ]);

        $acct = MpesaAccount::where('owner_user_id', 1)->first();
        $this->assertSame('PAYBILL', $acct->account_type);
        $this->assertSame('400200', $acct->paybill);
        $this->assertSame('Acme Rentals', $acct->account_name);
        $this->assertNull($acct->till_number);
    }

    public function test_quick_setup_is_additive_and_idempotent(): void
    {
        $this->actAsOwner();
        $svc = app(GatewayService::class);

        // A pre-existing account from the full settings form must survive inline setup.
        $gw = Gateway::create(['owner_user_id' => 1, 'slug' => 'mpesa', 'status' => DEACTIVATE]);
        MpesaAccount::create(['owner_user_id' => 1, 'gateway_id' => $gw->id, 'account_type' => 'PAYBILL', 'paybill' => '111111', 'account_name' => 'Old', 'status' => ACTIVE]);

        // Run the same till twice — same type updates in place, so no duplicate piles up.
        $svc->quickMpesaSetup(['account_type' => 'TILLNUMBER', 'till_number' => '5203988']);
        $svc->quickMpesaSetup(['account_type' => 'TILLNUMBER', 'till_number' => '5203988']);

        $this->assertSame(1, MpesaAccount::where('owner_user_id', 1)->where('account_type', 'TILLNUMBER')->count());
        $this->assertSame(1, MpesaAccount::where('owner_user_id', 1)->where('account_type', 'PAYBILL')->count()); // the old one is untouched
    }
}
