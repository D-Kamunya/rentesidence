<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Job and household size are OPTIONAL at onboarding (validation was relaxed in the
 * onboarding-friction pass — they're PII best authored by the tenant), but the `tenants` table
 * still had `job` (string) and `family_member` (integer) as NOT NULL. So a blank value passed
 * validation and then failed the INSERT with a 23000 integrity violation — blocking both a
 * fresh add and a returning-tenant reconnect. Make both nullable to match the validation. (`age`
 * was already nullable.) Downstream reads already null-guard these (profile shows '—').
 * Guarded/idempotent.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('tenants')) {
            return;
        }
        Schema::table('tenants', function (Blueprint $table) {
            if (Schema::hasColumn('tenants', 'job')) {
                $table->string('job')->nullable()->change();
            }
            if (Schema::hasColumn('tenants', 'family_member')) {
                $table->integer('family_member')->nullable()->change();
            }
        });
    }

    public function down(): void
    {
        // Intentionally NOT reverting to NOT NULL: rows legitimately created with null job/household
        // would make a down-migration fail, and NOT NULL was the bug. Leave them nullable.
    }
};
