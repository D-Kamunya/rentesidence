<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Early-settlement lifecycle so a facility is only COMPLETED once the payoff has
 * actually been paid + confirmed. Before this, the owner "Settle early" button
 * marked a loan paid off for free (no money collected, partner never paid). Now:
 * initiate (M-Pesa STK to the owner, or a manual bank payment recorded) →
 * pending → confirmed (STK callback / partner confirms) → settled + partner remitted.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('finance_facilities')) {
            return;
        }
        Schema::table('finance_facilities', function (Blueprint $table) {
            if (! Schema::hasColumn('finance_facilities', 'early_settlement_status')) {
                $table->string('early_settlement_status', 24)->nullable()->after('disbursement_reference'); // null | pending | settled
            }
            if (! Schema::hasColumn('finance_facilities', 'early_settlement_channel')) {
                $table->string('early_settlement_channel', 24)->nullable()->after('early_settlement_status');
            }
            if (! Schema::hasColumn('finance_facilities', 'early_settlement_reference')) {
                $table->string('early_settlement_reference')->nullable()->after('early_settlement_channel');
            }
            if (! Schema::hasColumn('finance_facilities', 'early_settlement_amount')) {
                $table->decimal('early_settlement_amount', 14, 2)->nullable()->after('early_settlement_reference');
            }
            if (! Schema::hasColumn('finance_facilities', 'early_settlement_at')) {
                $table->timestamp('early_settlement_at')->nullable()->after('early_settlement_amount');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('finance_facilities')) {
            return;
        }
        Schema::table('finance_facilities', function (Blueprint $table) {
            foreach (['early_settlement_status', 'early_settlement_channel', 'early_settlement_reference', 'early_settlement_amount', 'early_settlement_at'] as $col) {
                if (Schema::hasColumn('finance_facilities', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
