<?php

namespace Tests\Feature\Screening;

use App\Models\OwnerCreditTransaction;
use App\Models\TenantScreeningLookup;
use App\Services\Screening\ScreeningLookupService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Behaviour tests for the owner screening lookup (Step 4). Runs against an isolated in-memory
 * sqlite connection with only the tables the flow touches — never the real database.
 *
 * The owner here is a FREE-plan owner (empty owner_packages → no active package → free tier),
 * so we can exercise the full coverage ladder: free monthly allowance → purchased credit.
 * Locks the screening-specific rules: a miss is never charged, the free allowance covers a hit
 * without deducting, an exhausted allowance deducts exactly one credit, and every hit is logged
 * with a frozen score snapshot.
 */
class ScreeningLookupServiceTest extends TestCase
{
    private const OWNER_UID = 7777;

    protected function setUp(): void
    {
        parent::setUp();

        config(['database.connections.screen_sqlite' => [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
        ]]);
        config(['database.default' => 'screen_sqlite']);
        DB::purge('screen_sqlite');

        Schema::create('owners', function ($t) {
            $t->id();
            $t->unsignedBigInteger('user_id')->index();
            $t->unsignedInteger('screening_credits')->default(0);
            $t->softDeletes();
            $t->timestamps();
        });

        Schema::create('owner_packages', function ($t) {
            $t->id();
            $t->unsignedBigInteger('user_id');
            $t->unsignedTinyInteger('status')->default(1);
            $t->string('pricing_model')->nullable();
            $t->date('end_date')->nullable();
            $t->softDeletes();
            $t->timestamps();
        });

        Schema::create('owner_credit_transactions', function ($t) {
            $t->id();
            $t->string('bucket', 32);
            $t->unsignedBigInteger('owner_user_id');
            $t->string('type', 24);
            $t->unsignedInteger('quantity');
            $t->decimal('amount_paid', 10, 2)->nullable();
            $t->unsignedInteger('balance_before');
            $t->unsignedInteger('balance_after');
            $t->string('reference')->nullable();
            $t->string('payment_id')->nullable();
            $t->string('description')->nullable();
            $t->string('status', 12)->default('success');
            $t->timestamps();
        });

        Schema::create('tenant_screening_lookups', function ($t) {
            $t->id();
            $t->unsignedBigInteger('owner_user_id');
            $t->string('identity_key')->nullable();
            $t->string('phone')->nullable();
            $t->unsignedBigInteger('tenant_credit_profile_id')->nullable();
            $t->decimal('score', 5, 2)->nullable();
            $t->string('score_band', 20)->nullable();
            $t->string('score_grade', 2)->nullable();
            $t->boolean('was_thin_file')->default(false);
            $t->boolean('was_activated')->default(false);
            $t->string('billed_as', 12)->nullable();
            $t->timestamps();
        });

        Schema::create('users', function ($t) {
            $t->id();
            $t->string('first_name')->nullable();
            $t->string('last_name')->nullable();
            $t->string('contact_number')->nullable();
            $t->unsignedTinyInteger('role')->nullable();
            $t->softDeletes();
            $t->timestamps();
        });

        Schema::create('tenants', function ($t) {
            $t->id();
            $t->unsignedBigInteger('user_id');
            $t->unsignedBigInteger('owner_user_id')->nullable();
            $t->string('rent_payment_rating')->nullable();
            $t->string('discipline_rating')->nullable();
            $t->softDeletes();
            $t->timestamps();
        });

        Schema::create('invoices', function ($t) {
            $t->id();
            $t->unsignedBigInteger('order_id')->nullable();
            $t->unsignedBigInteger('tenant_id');
            $t->decimal('amount', 12, 2)->default(0);
            $t->date('due_date')->nullable();
            $t->string('billing_period')->nullable();
            $t->integer('status')->default(0);
            $t->softDeletes();
            $t->timestamps();
        });

        Schema::create('orders', function ($t) {
            $t->id();
            $t->softDeletes();
            $t->timestamps();
        });

        Schema::create('tenant_credit_profiles', function ($t) {
            $t->id();
            $t->string('identity_key')->unique();
            $t->string('phone')->nullable();
            $t->string('national_id')->nullable();
            $t->string('display_name')->nullable();
            $t->unsignedInteger('tenancies_count')->default(0);
            $t->unsignedInteger('owners_count')->default(0);
            $t->decimal('landlord_rating_avg', 4, 2)->nullable();
            $t->unsignedInteger('ratings_count')->default(0);
            $t->unsignedInteger('invoices_total')->default(0);
            $t->unsignedInteger('invoices_paid')->default(0);
            $t->unsignedInteger('on_time_count')->default(0);
            $t->unsignedInteger('late_count')->default(0);
            $t->unsignedInteger('overdue_count')->default(0);
            $t->decimal('total_billed', 14, 2)->default(0);
            $t->decimal('total_paid', 14, 2)->default(0);
            $t->decimal('outstanding', 14, 2)->default(0);
            $t->decimal('on_time_rate', 5, 2)->nullable();
            $t->decimal('avg_days_late', 8, 2)->nullable();
            $t->decimal('score', 5, 2)->nullable();
            $t->string('score_band', 20)->nullable();
            $t->string('score_grade', 2)->nullable();
            $t->string('score_version', 10)->nullable();
            $t->boolean('is_thin_file')->default(false);
            $t->text('score_factors')->nullable();
            $t->timestamp('first_activity_at')->nullable();
            $t->timestamp('last_activity_at')->nullable();
            $t->timestamp('computed_at')->nullable();
            $t->timestamp('activated_at')->nullable();
            $t->unsignedBigInteger('activated_by_user_id')->nullable();
            $t->timestamps();
        });

        DB::table('owners')->insert([
            'user_id' => self::OWNER_UID, 'screening_credits' => 5,
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    protected function tearDown(): void
    {
        foreach (['tenant_credit_profiles', 'orders', 'invoices', 'tenants', 'users',
                  'tenant_screening_lookups', 'owner_credit_transactions', 'owner_packages', 'owners'] as $tbl) {
            Schema::dropIfExists($tbl);
        }
        parent::tearDown();
    }

    /** Seed one person with a paid-on-time invoice so a lookup is a HIT. */
    private function seedPerson(): void
    {
        DB::table('users')->insert([
            'id' => 10, 'first_name' => 'Jane', 'last_name' => 'Mwangi',
            'contact_number' => '254712345678', 'role' => USER_ROLE_TENANT,
            'created_at' => now()->subMonths(6), 'updated_at' => now(),
        ]);
        DB::table('tenants')->insert([
            'id' => 20, 'user_id' => 10, 'owner_user_id' => self::OWNER_UID,
            'created_at' => now()->subMonths(6), 'updated_at' => now(),
        ]);
        DB::table('orders')->insert([
            'id' => 30, 'created_at' => now()->subDays(2), 'updated_at' => now()->subDays(2),
        ]);
        DB::table('invoices')->insert([
            'id' => 40, 'order_id' => 30, 'tenant_id' => 20, 'amount' => 15000,
            'due_date' => now()->subDay()->toDateString(), 'billing_period' => now()->subMonth()->toDateString(),
            'status' => INVOICE_STATUS_PAID, 'created_at' => now()->subMonth(), 'updated_at' => now()->subDays(2),
        ]);
    }

    /** A miss (no person on record) is logged for audit but NEVER charged. */
    public function test_miss_is_never_charged(): void
    {
        $result = app(ScreeningLookupService::class)->screen(self::OWNER_UID, '0700000000');

        $this->assertSame('no_record', $result['status']);
        $this->assertNull($result['profile']);

        $lookup = TenantScreeningLookup::first();
        $this->assertNotNull($lookup, 'the miss is still logged');
        $this->assertSame('none', $lookup->billed_as);
        $this->assertNull($lookup->score);

        $this->assertSame(0, OwnerCreditTransaction::where('type', 'deduct')->count());
        $this->assertSame(5, (int) DB::table('owners')->where('user_id', self::OWNER_UID)->value('screening_credits'));
    }

    /** With free allowance remaining, a hit is covered free — logged, snapshot, no deduction. */
    public function test_hit_uses_free_allowance_without_deducting(): void
    {
        config(['settings.screening_free_quota' => 3]);
        $this->seedPerson();

        $result = app(ScreeningLookupService::class)->screen(self::OWNER_UID, '0712345678');

        $this->assertSame('ok', $result['status']);
        $this->assertNotNull($result['profile']->score);

        $lookup = TenantScreeningLookup::first();
        $this->assertSame('free', $lookup->billed_as);
        $this->assertNotNull($lookup->tenant_credit_profile_id);
        $this->assertEquals((float) $result['profile']->score, (float) $lookup->score, 'logged score is a snapshot');

        $this->assertSame(0, OwnerCreditTransaction::where('type', 'deduct')->count());
        $this->assertSame(5, (int) DB::table('owners')->where('user_id', self::OWNER_UID)->value('screening_credits'));
    }

    /** With the free allowance exhausted, a hit deducts exactly one purchased credit. */
    public function test_hit_deducts_one_credit_when_allowance_exhausted(): void
    {
        config(['settings.screening_free_quota' => 0]); // no free lookups → fall through to credits
        $this->seedPerson();

        $result = app(ScreeningLookupService::class)->screen(self::OWNER_UID, '0712345678');

        $this->assertSame('ok', $result['status']);

        $lookup = TenantScreeningLookup::first();
        $this->assertSame('credit', $lookup->billed_as);

        // Exactly one credit consumed: 5 → 4, one deduct ledger row.
        $this->assertSame(1, OwnerCreditTransaction::where('bucket', 'screening')->where('type', 'deduct')->count());
        $this->assertSame(4, (int) DB::table('owners')->where('user_id', self::OWNER_UID)->value('screening_credits'));
    }

    /** A free-plan owner is metered (not unlimited) and exposes a monthly free allowance. */
    public function test_coverage_helpers_for_free_plan(): void
    {
        config(['settings.screening_free_quota' => 3]);

        $this->assertFalse(ScreeningLookupService::ownerHasUnlimited(self::OWNER_UID));
        $allowance = ScreeningLookupService::freeMonthlyAllowance(self::OWNER_UID);
        $this->assertIsArray($allowance);
        $this->assertSame(3, $allowance['quota']);
        $this->assertSame(3, $allowance['remaining']);
    }
}
