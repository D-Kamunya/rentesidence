<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Add 'infrastructure_recovery' to settlement_transactions.transaction_type so
 * infra-cost recovery from rent (Part B) is recorded distinctly from
 * commission_recovery. Already present in the create migration for fresh/sqlite;
 * this extends already-migrated MySQL DBs.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE settlement_transactions MODIFY transaction_type "
                . "ENUM('rent_deduction_principal','rent_deduction_interest','rent_deduction_penalty',"
                . "'commission_recovery','infrastructure_recovery','platform_fee','token_deduction') NOT NULL");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE settlement_transactions MODIFY transaction_type "
                . "ENUM('rent_deduction_principal','rent_deduction_interest','rent_deduction_penalty',"
                . "'commission_recovery','platform_fee','token_deduction') NOT NULL");
        }
    }
};
