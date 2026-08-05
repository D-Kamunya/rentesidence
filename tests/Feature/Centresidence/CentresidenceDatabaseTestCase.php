<?php

namespace Tests\Feature\Centresidence;

use App\Centresidence\Simulation\Sandbox;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Shared base for Centresidence DB-backed tests.
 *
 * Boots an isolated in-memory sqlite sandbox (via the same Sandbox the
 * simulation command uses), stubbing only the legacy tables the module FKs
 * reference. This never runs the 169 legacy migrations and never touches the
 * real database.
 *
 * Not suffixed *Test so PHPUnit does not collect it as a test case.
 */
abstract class CentresidenceDatabaseTestCase extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Sandbox::boot('cs_sqlite');
        $this->seedLegacyRows();
    }

    /**
     * Seed two owners, each with a property + unit, to support multi-owner
     * topology scenarios.
     */
    protected function seedLegacyRows(): void
    {
        DB::table('users')->insert([
            ['id' => 1, 'first_name' => 'Owner', 'last_name' => 'A'],
            ['id' => 2, 'first_name' => 'Owner', 'last_name' => 'B'],
        ]);
        DB::table('properties')->insert([
            ['id' => 1, 'owner_user_id' => 1, 'name' => 'Property A'],
            ['id' => 2, 'owner_user_id' => 2, 'name' => 'Property B'],
        ]);
        DB::table('property_units')->insert([
            ['id' => 1, 'property_id' => 1, 'name' => 'Unit A1'],
            ['id' => 2, 'property_id' => 2, 'name' => 'Unit B1'],
        ]);
        // Both owners are on transaction mode by default (financing-eligible).
        DB::table('owner_packages')->insert([
            ['user_id' => 1, 'pricing_model' => 'transaction', 'status' => 1],
            ['user_id' => 2, 'pricing_model' => 'transaction', 'status' => 1],
        ]);
    }
}
