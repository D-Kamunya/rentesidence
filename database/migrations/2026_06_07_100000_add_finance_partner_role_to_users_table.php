<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Adds the Centresidence finance-partner role (6) to the users.role enum.
 *
 * Lives in the legacy migration path (it alters a legacy table). Guarded to
 * MySQL — the enum ALTER is a no-op on other drivers (e.g. the sqlite test
 * sandbox, which never runs legacy migrations anyway).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('1', '2', '3', '4', '5', '6')");
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('1', '2', '3', '4', '5')");
    }
};
