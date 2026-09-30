<?php

namespace Tests\Feature\Tenant;

use App\Models\Invoice;
use App\Models\Tenant;
use App\Models\TenantDeposit;
use App\Models\User;
use App\Services\DepositService;
use App\Services\InvoiceRecurringService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Locks the deposit lifecycle the tenant-transfer "also collect the deposit" option rides:
 * it reuses the move-in SSOT (generateFirstInvoice → deposit line tagged 'Deposit'), and the
 * shared payment path (recordFromPaidInvoice) turns that paid line into a HELD register entry
 * that surfaces on the deposits-held page and follows reservation/reimbursal — NOT a silent,
 * one-off deposit. Isolated in-memory sqlite harness (the repo pattern) with just the tables
 * this chain touches.
 */
class TenantTransferDepositTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['database.connections.dep_sqlite' => [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '', 'foreign_key_constraints' => false,
        ]]);
        config(['database.default' => 'dep_sqlite']);
        DB::purge('dep_sqlite');
        Queue::fake(); // notifyInvoiceGenerated dispatches jobs — never actually send in tests.
        \Illuminate\Database\Eloquent\Model::unguard(); // seed test rows without per-model fillable lists.

        Schema::create('users', function ($t) {
            $t->id();
            $t->string('first_name')->nullable();
            $t->string('last_name')->nullable();
            $t->string('email')->nullable();
            $t->string('contact_number')->nullable();
            $t->unsignedBigInteger('owner_user_id')->nullable();
            $t->unsignedTinyInteger('role')->nullable();
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
            $t->decimal('general_rent', 14, 2)->nullable();
            $t->decimal('security_deposit', 14, 2)->nullable();
            $t->unsignedTinyInteger('security_deposit_type')->nullable();
            $t->softDeletes();
            $t->timestamps();
        });
        Schema::create('invoice_recurring_settings', function ($t) {
            $t->id();
            $t->unsignedBigInteger('owner_user_id')->nullable();
            $t->unsignedBigInteger('property_id')->nullable();
            $t->unsignedBigInteger('property_unit_id')->nullable();
            $t->string('invoice_prefix')->nullable();
            $t->unsignedTinyInteger('recurring_type')->nullable();
            $t->decimal('amount', 14, 2)->default(0);
            $t->unsignedTinyInteger('status')->nullable();
            $t->softDeletes();
            $t->timestamps();
        });
        Schema::create('invoice_recurring_setting_items', function ($t) {
            $t->id();
            $t->unsignedBigInteger('invoice_recurring_setting_id');
            $t->unsignedBigInteger('invoice_type_id')->nullable();
            $t->decimal('amount', 14, 2)->default(0);
            $t->softDeletes();
            $t->timestamps();
        });
        Schema::create('invoices', function ($t) {
            $t->id();
            $t->string('name')->nullable();
            $t->string('invoice_no')->nullable();
            $t->unsignedBigInteger('tenant_id')->nullable();
            $t->unsignedBigInteger('owner_user_id')->nullable();
            $t->unsignedBigInteger('invoice_recurring_setting_id')->nullable();
            $t->unsignedBigInteger('property_id')->nullable();
            $t->unsignedBigInteger('property_unit_id')->nullable();
            $t->string('month')->nullable();
            $t->date('billing_period')->nullable();
            $t->date('due_date')->nullable();
            $t->string('payment_token')->nullable();
            $t->timestamp('payment_token_expires_at')->nullable();
            $t->decimal('amount', 14, 2)->default(0);
            $t->unsignedTinyInteger('status')->nullable();
            $t->unsignedBigInteger('order_id')->nullable();
            $t->softDeletes();
            $t->timestamps();
        });
        Schema::create('invoice_items', function ($t) {
            $t->id();
            $t->unsignedBigInteger('invoice_id');
            $t->unsignedBigInteger('invoice_type_id')->nullable();
            $t->decimal('amount', 14, 2)->default(0);
            $t->string('description')->nullable();
            $t->timestamps();
        });
        Schema::create('invoice_types', function ($t) {
            $t->id();
            $t->unsignedBigInteger('owner_user_id')->nullable();
            $t->string('name')->nullable();
            $t->decimal('tax', 8, 2)->default(0);
            $t->unsignedTinyInteger('status')->nullable();
            $t->timestamps();
        });
        Schema::create('tenant_deposits', function ($t) {
            $t->id();
            $t->unsignedBigInteger('owner_user_id')->nullable();
            $t->unsignedBigInteger('tenant_id');
            $t->unsignedBigInteger('property_id')->nullable();
            $t->unsignedBigInteger('property_unit_id')->nullable();
            $t->unsignedBigInteger('invoice_id')->nullable();
            $t->unsignedBigInteger('invoice_item_id')->nullable();
            $t->decimal('amount', 14, 2)->default(0);
            $t->string('status')->nullable();
            $t->timestamp('held_at')->nullable();
            $t->timestamp('released_at')->nullable();
            $t->timestamps();
        });
    }

    private function makeTenant(float $deposit = 24000, int $depositType = TYPE_FIXED): Tenant
    {
        $owner = User::create(['first_name' => 'Owner', 'role' => USER_ROLE_OWNER, 'status' => 1]);
        $user  = User::create(['first_name' => 'Kevin', 'contact_number' => '254700000001', 'owner_user_id' => $owner->id, 'role' => USER_ROLE_TENANT, 'status' => 1]);
        $tenant = Tenant::create([
            'user_id' => $user->id, 'owner_user_id' => $owner->id, 'property_id' => 1, 'unit_id' => 5,
            'status' => TENANT_STATUS_ACTIVE, 'general_rent' => 12000, 'security_deposit' => $deposit,
            'security_deposit_type' => $depositType,
        ]);

        // Active recurring setting on the tenant's (new) unit — what transferUnit re-points to.
        \App\Models\InvoiceRecurringSetting::create([
            'owner_user_id' => $owner->id, 'property_id' => 1, 'property_unit_id' => 5,
            'invoice_prefix' => 'INV', 'recurring_type' => INVOICE_RECURRING_TYPE_MONTHLY,
            'amount' => 12000, 'status' => ACTIVE,
        ]);

        return $tenant;
    }

    public function test_deposit_collected_on_transfer_becomes_held_on_payment(): void
    {
        $tenant  = $this->makeTenant(24000);
        $invSvc  = app(InvoiceRecurringService::class);
        $depSvc  = app(DepositService::class);

        // The owner ticked "also collect the deposit" (rent left to the cron → mode 'skip').
        $res = $invSvc->generateFirstInvoice($tenant, 'skip', null, 24000);
        $this->assertTrue($res['ok']);
        $this->assertNotNull($res['invoice_id']);

        // Before payment it is NOT yet held — it lives on the invoice as a Security Deposit line.
        $this->assertSame(0.0, $depSvc->totalHeldForTenant($tenant->id));

        // Pay the invoice → the shared payment path records the deposit line as HELD.
        $invoice = Invoice::find($res['invoice_id']);
        $invoice->update(['status' => INVOICE_STATUS_PAID]);
        $depSvc->recordFromPaidInvoice($invoice);

        // It now surfaces on the deposits-held register for this tenancy.
        $this->assertSame(24000.0, $depSvc->totalHeldForTenant($tenant->id));
        $held = TenantDeposit::where('tenant_id', $tenant->id)->first();
        $this->assertNotNull($held);
        $this->assertSame(TenantDeposit::STATUS_HELD, $held->status);
        $this->assertSame(5, (int) $held->property_unit_id); // attributed to the (new) unit
    }

    public function test_holding_is_idempotent(): void
    {
        $tenant = $this->makeTenant(24000);
        $depSvc = app(DepositService::class);
        $res    = app(InvoiceRecurringService::class)->generateFirstInvoice($tenant, 'skip', null, 24000);

        $invoice = Invoice::find($res['invoice_id']);
        $invoice->update(['status' => INVOICE_STATUS_PAID]);

        // A retried callback / double confirmation must not double-count the held liability.
        $depSvc->recordFromPaidInvoice($invoice);
        $depSvc->recordFromPaidInvoice($invoice);

        $this->assertSame(1, TenantDeposit::where('tenant_id', $tenant->id)->count());
        $this->assertSame(24000.0, $depSvc->totalHeldForTenant($tenant->id));
    }

    public function test_a_second_deposit_is_not_collected_once_one_is_in_play(): void
    {
        $tenant = $this->makeTenant(24000);
        $invSvc = app(InvoiceRecurringService::class);
        $depSvc = app(DepositService::class);

        $first = $invSvc->generateFirstInvoice($tenant, 'skip', null, 24000);
        Invoice::find($first['invoice_id'])->update(['status' => INVOICE_STATUS_PAID]);
        $depSvc->recordFromPaidInvoice(Invoice::find($first['invoice_id']));

        // Attempting to collect again is a no-op — the deposit guard sees one already in play.
        $this->assertTrue($depSvc->tenantHasDeposit($tenant->id));
        $second = $invSvc->generateFirstInvoice($tenant, 'skip', null, 24000);
        $this->assertNull($second['invoice_id']);
        $this->assertSame(24000.0, $depSvc->totalHeldForTenant($tenant->id)); // still just one
    }

    public function test_configured_deposit_resolves_fixed_and_percentage(): void
    {
        $depSvc = app(DepositService::class);

        $fixed = $this->makeTenant(24000, TYPE_FIXED);
        $this->assertSame(24000.0, $depSvc->configuredDepositAmount($fixed, 12000));

        $pct = $this->makeTenant(50, TYPE_PERCENTAGE); // 50% of rent
        $this->assertSame(6000.0, $depSvc->configuredDepositAmount($pct, 12000));
    }
}
