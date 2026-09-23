<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * settings.option_value was varchar(191) (the old global utf8mb4 index-safe default), so any long
 * setting — the Terms & Conditions, privacy/cookie policies, large config blobs — silently
 * truncated at 191 chars. Widen it to TEXT so full-length policy content can be stored. MySQL-only
 * ALTER (sqlite is dynamically typed and never enforced the length, so it's a no-op there).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('settings') || ! Schema::hasColumn('settings', 'option_value')) {
            return;
        }
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE `settings` MODIFY `option_value` TEXT NULL');
        }
    }

    public function down(): void
    {
        // No-op: narrowing back to varchar(191) would re-truncate stored policies. Widening stays.
    }
};
