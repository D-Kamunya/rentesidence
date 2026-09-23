<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Real disbursement lifecycle so a facility is only REPAYABLE once money has
 * actually been released. Before this, a facility was `active` (and its rent
 * repayment ran) the moment it was approved — repaying a loan that was never
 * disbursed. Now: awaiting → pending_confirmation (recorded, both channels) →
 * disbursed (confirmed / auto), and rent settlement gates on `disbursed`.
 *
 * Backfill: facilities that already exist were operating AS-IF disbursed, so we
 * mark them disbursed (using their disbursement_date or created_at) to avoid
 * abruptly halting live repayments. Only NEW facilities start `awaiting`.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('finance_facilities')) {
            return;
        }

        Schema::table('finance_facilities', function (Blueprint $table) {
            if (! Schema::hasColumn('finance_facilities', 'disbursement_status')) {
                $table->string('disbursement_status', 32)->default('awaiting')->after('status');
            }
            if (! Schema::hasColumn('finance_facilities', 'disbursed_at')) {
                $table->timestamp('disbursed_at')->nullable()->after('disbursement_status');
            }
            if (! Schema::hasColumn('finance_facilities', 'disbursement_channel')) {
                $table->string('disbursement_channel', 24)->nullable()->after('disbursed_at');
            }
            if (! Schema::hasColumn('finance_facilities', 'disbursement_reference')) {
                $table->string('disbursement_reference')->nullable()->after('disbursement_channel');
            }
        });

        // Backfill existing facilities as already-disbursed (they operated that way).
        if (Schema::hasColumn('finance_facilities', 'disbursed_at')) {
            DB::table('finance_facilities')->whereNull('disbursed_at')->update([
                'disbursement_status' => 'disbursed',
                'disbursed_at'        => DB::raw('COALESCE(disbursement_date, created_at)'),
                'disbursement_channel'=> 'manual',
            ]);
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('finance_facilities')) {
            return;
        }
        Schema::table('finance_facilities', function (Blueprint $table) {
            foreach (['disbursement_status', 'disbursed_at', 'disbursement_channel', 'disbursement_reference'] as $col) {
                if (Schema::hasColumn('finance_facilities', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
