<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Records an owner's acceptance of the current Terms & Conditions version so the accept-gate can
 * bind it. Versioned: bumping `terms_version` (getOption) forces everyone to re-accept. Guarded so
 * it ships cleanly via app:deploy.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'terms_accepted_at')) {
                $table->timestamp('terms_accepted_at')->nullable();
            }
            if (! Schema::hasColumn('users', 'terms_accepted_version')) {
                $table->string('terms_accepted_version', 20)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            foreach (['terms_accepted_at', 'terms_accepted_version'] as $col) {
                if (Schema::hasColumn('users', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
