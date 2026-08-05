<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Down-payment collection (2026-06-08). When a facility carries an owner
 * down-payment, Centresidence (the installer/payee) collects it from the owner
 * at disbursement so the full deployment cost is settled before install. These
 * columns track the collection state; the matching ledger entry is a
 * facility_transactions row of type 'down_payment'.
 *
 * The 'down_payment' enum value is already present in the create migration for
 * fresh installs (and the sqlite test sandbox). For databases already migrated
 * we extend the live MySQL enum here; sqlite/others are skipped.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('finance_facilities', function (Blueprint $table) {
            // not_required | pending | collected | failed
            $table->string('down_payment_status', 20)->default('not_required')->after('owner_contribution');
            $table->string('down_payment_reference')->nullable()->after('down_payment_status');
            $table->timestamp('down_payment_collected_at')->nullable()->after('down_payment_reference');
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement(
                "ALTER TABLE facility_transactions MODIFY transaction_type "
                . "ENUM('disbursement','down_payment','repayment_principal','repayment_interest',"
                . "'repayment_penalty','fee','adjustment','write_off') NOT NULL"
            );
        }
    }

    public function down(): void
    {
        Schema::table('finance_facilities', function (Blueprint $table) {
            $table->dropColumn(['down_payment_status', 'down_payment_reference', 'down_payment_collected_at']);
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement(
                "ALTER TABLE facility_transactions MODIFY transaction_type "
                . "ENUM('disbursement','repayment_principal','repayment_interest',"
                . "'repayment_penalty','fee','adjustment','write_off') NOT NULL"
            );
        }
    }
};
