<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Add `finance_partners` as a knowledge-base audience so admins can author
 * partner-facing guides (how to set up a facility, interest, settlement) that
 * new finance partners read to self-onboard. The enum value is already in the
 * create migration for fresh installs; this extends already-migrated MySQL DBs
 * and adds the partner view counter.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kb_articles', function (Blueprint $table) {
            $table->unsignedBigInteger('views_finance_partner')->default(0)->after('views_affiliate');
        });

        if (DB::getDriverName() === 'mysql') {
            foreach (['kb_categories', 'kb_articles'] as $tbl) {
                DB::statement("ALTER TABLE {$tbl} MODIFY audience "
                    . "ENUM('owners','affiliates','both','finance_partners') NOT NULL DEFAULT 'both'");
            }
        }
    }

    public function down(): void
    {
        Schema::table('kb_articles', function (Blueprint $table) {
            $table->dropColumn('views_finance_partner');
        });

        if (DB::getDriverName() === 'mysql') {
            foreach (['kb_categories', 'kb_articles'] as $tbl) {
                DB::statement("ALTER TABLE {$tbl} MODIFY audience "
                    . "ENUM('owners','affiliates','both') NOT NULL DEFAULT 'both'");
            }
        }
    }
};
