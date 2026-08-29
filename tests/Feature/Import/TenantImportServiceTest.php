<?php

namespace Tests\Feature\Import;

use App\Services\Import\TenantImportService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Validation-engine tests for the bulk tenant import (parse + validate + preview classify).
 * Runs against an isolated in-memory sqlite with just the four tables preload() reads —
 * never the real database. The write path (importRow) is covered by integration.
 */
class TenantImportServiceTest extends TestCase
{
    private TenantImportService $svc;

    protected function setUp(): void
    {
        parent::setUp();

        config(['database.connections.imp_sqlite' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']]);
        config(['database.default' => 'imp_sqlite']);
        DB::purge('imp_sqlite');

        Schema::create('properties', function ($t) {
            $t->id(); $t->unsignedBigInteger('owner_user_id'); $t->string('name'); $t->softDeletes(); $t->timestamps();
        });
        Schema::create('property_units', function ($t) {
            $t->id(); $t->unsignedBigInteger('property_id'); $t->string('unit_name'); $t->softDeletes(); $t->timestamps();
        });
        Schema::create('users', function ($t) {
            $t->id(); $t->unsignedBigInteger('owner_user_id')->nullable(); $t->string('contact_number')->nullable();
            $t->unsignedTinyInteger('role')->default(0); $t->softDeletes(); $t->timestamps();
        });
        Schema::create('tenants', function ($t) {
            $t->id(); $t->unsignedBigInteger('user_id'); $t->unsignedBigInteger('owner_user_id');
            $t->unsignedBigInteger('unit_id')->nullable(); $t->unsignedTinyInteger('status')->default(0);
            $t->softDeletes(); $t->timestamps();
        });

        $this->svc = new TenantImportService();
    }

    protected function tearDown(): void
    {
        foreach (['tenants', 'users', 'property_units', 'properties'] as $tbl) {
            Schema::dropIfExists($tbl);
        }
        parent::tearDown();
    }

    private function writeCsv(string $content): string
    {
        $path = tempnam(sys_get_temp_dir(), 'imptest') . '.csv';
        file_put_contents($path, $content);
        return $path;
    }

    public function test_parses_headers_by_label_and_key_and_skips_blank_lines(): void
    {
        // Mixes human labels and canonical keys; includes a BOM and a blank line.
        $csv = "\xEF\xBB\xBFProperty,unit_name,Monthly Rent,First Name,Phone\n"
             . "Riverside,A1,25000,Jane,0712345678\n"
             . "\n"
             . "Riverside,A2,18000,John,0722000000\n";
        $parsed = $this->svc->parseCsv($this->writeCsv($csv));

        $this->assertCount(2, $parsed['rows']);
        $this->assertSame([], $parsed['missingRequiredHeaders']);
        $this->assertSame('Riverside', $parsed['rows'][0]['property_name']);
        $this->assertSame('A1', $parsed['rows'][0]['unit_name']);
    }

    public function test_flags_missing_required_headers(): void
    {
        // No phone / first name columns.
        $csv = "Property,Unit,Monthly Rent\nRiverside,A1,25000\n";
        $parsed = $this->svc->parseCsv($this->writeCsv($csv));

        $this->assertContains('First Name', $parsed['missingRequiredHeaders']);
        $this->assertContains('Phone', $parsed['missingRequiredHeaders']);
    }

    public function test_catches_type_required_and_format_errors(): void
    {
        $csv = "Property,Unit,Monthly Rent,Rent Type,Rent Due Day,First Name,Phone,Email,Lease Start,Lease End,Opening Balance\n"
             . "Green,B1,notanumber,weekly,45,,123,bademail,2026-13-40,2025-01-01,-50\n";
        $parsed = $this->svc->parseCsv($this->writeCsv($csv));
        $res    = $this->svc->validateRows(1, $parsed['rows']);

        $this->assertSame(0, $res['valid']);
        $this->assertSame(1, $res['errors']);
        $errs = $res['rows'][0]['errors'];
        $joined = implode(' ', $errs);
        $this->assertStringContainsString('Monthly Rent', $joined);
        $this->assertStringContainsString('First Name', $joined);       // required
        $this->assertStringContainsString('Rent Type', $joined);
        $this->assertStringContainsString('Rent Due Day', $joined);
        $this->assertStringContainsString('Email', $joined);
        $this->assertStringContainsString('Phone', $joined);
        $this->assertStringContainsString('Opening Balance', $joined);
    }

    public function test_detects_within_file_duplicate_phone_and_unit(): void
    {
        $csv = "Property,Unit,Monthly Rent,First Name,Phone\n"
             . "Riverside,A1,25000,Jane,0712345678\n"       // ok
             . "Riverside,A1,25000,Other,0733000000\n"      // duplicate unit
             . "Greenview,C1,20000,Mary,0712345678\n";      // duplicate phone
        $parsed = $this->svc->parseCsv($this->writeCsv($csv));
        $res    = $this->svc->validateRows(1, $parsed['rows']);

        $this->assertSame(1, $res['valid']);
        $this->assertSame(2, $res['errors']);
        $this->assertStringContainsString('Duplicate unit', implode(' ', $res['rows'][1]['errors']));
        $this->assertStringContainsString('Duplicate phone', implode(' ', $res['rows'][2]['errors']));
    }

    public function test_counts_sms_and_email_invite_candidates(): void
    {
        $csv = "Property,Unit,Monthly Rent,First Name,Phone,Email\n"
             . "Riverside,A1,25000,Jane,0712345678,jane@example.com\n"  // sms + email
             . "Riverside,A2,25000,John,0722000000,\n";                 // sms only
        $parsed = $this->svc->parseCsv($this->writeCsv($csv));
        $res    = $this->svc->validateRows(1, $parsed['rows']);

        $this->assertSame(2, $res['valid']);
        $this->assertSame(2, $res['summary']['sms_invites']);
        $this->assertSame(1, $res['summary']['email_invites']);
    }

    public function test_existing_tenant_is_update_and_occupied_unit_is_error(): void
    {
        // Seed an existing tenant (phone 254712345678) actively occupying unit A1.
        DB::table('properties')->insert(['id' => 1, 'owner_user_id' => 1, 'name' => 'Riverside']);
        DB::table('property_units')->insert(['id' => 1, 'property_id' => 1, 'unit_name' => 'A1']);
        DB::table('users')->insert(['id' => 1, 'owner_user_id' => 1, 'contact_number' => '254712345678', 'role' => USER_ROLE_TENANT]);
        DB::table('tenants')->insert(['id' => 1, 'user_id' => 1, 'owner_user_id' => 1, 'unit_id' => 1, 'status' => TENANT_STATUS_ACTIVE]);

        // Row A: same tenant (by phone) → update. Row B: a NEW tenant into the occupied A1 → error.
        $csv = "Property,Unit,Monthly Rent,First Name,Phone\n"
             . "Riverside,A2,25000,Jane,0712345678\n"       // existing tenant → update
             . "Riverside,A1,25000,Newbie,0799999999\n";    // A1 already occupied → error
        $parsed = $this->svc->parseCsv($this->writeCsv($csv));
        $res    = $this->svc->validateRows(1, $parsed['rows']);

        $this->assertSame('update', $res['rows'][0]['action']);
        $this->assertSame('error', $res['rows'][1]['action']);
        $this->assertStringContainsString('already has an active tenant', implode(' ', $res['rows'][1]['errors']));
    }
}
