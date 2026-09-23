<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Adds a `billing_period` (first-of-covered-month DATE) to invoices. This is the unambiguous
 * period key that makes advance-rent invoicing idempotent ACROSS year boundaries — the existing
 * `month` string is a bare month name (no year, and can drift from due_date), so it can't safely
 * key "has this exact period already been billed?". Both the generate:invoice cron and the
 * on-demand advance-pay flow key off this.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('invoices') && ! Schema::hasColumn('invoices', 'billing_period')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->date('billing_period')->nullable()->after('month')->index();
            });

            // Backfill existing invoices with a sensible period = first day of their created_at
            // month (they were generated within their period, so this is a faithful proxy and is
            // only used to stop re-generating already-billed periods).
            DB::table('invoices')
                ->whereNull('billing_period')
                ->update(['billing_period' => DB::raw("DATE_FORMAT(created_at, '%Y-%m-01')")]);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('invoices') && Schema::hasColumn('invoices', 'billing_period')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->dropColumn('billing_period');
            });
        }
    }
};
