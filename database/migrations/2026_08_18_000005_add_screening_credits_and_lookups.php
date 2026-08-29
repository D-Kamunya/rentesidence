<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Owner tenant-screening (Step 4 of the Global Tenant ID). Screening rides the unified
 * prepaid-credit rail as the `screening` bucket, so the ledger is the shared
 * `owner_credit_transactions` table — we only need the balance column here.
 *
 * `tenant_screening_lookups` is the ACCESS LOG: one row per lookup an owner runs. It powers
 * three things at once — (1) tenant transparency ("who viewed my Rental ID"), (2) the free
 * monthly allowance count (billed_as='free' rows this month, use-it-or-lose-it, no cron),
 * and (3) an audit trail for the bureau/fairness posture. It snapshots the score shown so a
 * later recompute never rewrites what an owner actually saw.
 *
 * Self-healing: guarded so it's safe to re-run on the shared host with no manual steps.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('owners') && ! Schema::hasColumn('owners', 'screening_credits')) {
            Schema::table('owners', function (Blueprint $table) {
                $table->unsignedInteger('screening_credits')->default(0)->after('user_id');
            });
        }

        if (! Schema::hasTable('tenant_screening_lookups')) {
            Schema::create('tenant_screening_lookups', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('owner_user_id');            // who screened
                $table->string('identity_key')->nullable();             // canonical 2547… (resolved person)
                $table->string('phone')->nullable();                    // phone as entered/normalised
                $table->unsignedBigInteger('tenant_credit_profile_id')->nullable();
                // Snapshot of what the owner was shown, frozen at lookup time.
                $table->decimal('score', 5, 2)->nullable();
                $table->string('score_band', 20)->nullable();
                $table->string('score_grade', 2)->nullable();
                $table->boolean('was_thin_file')->default(false);
                $table->boolean('was_activated')->default(false);       // had the tenant claimed their ID?
                $table->string('billed_as', 12)->nullable();            // plan | free | credit
                $table->timestamps();

                $table->index('owner_user_id');
                $table->index('identity_key');
                $table->index('tenant_credit_profile_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_screening_lookups');
        if (Schema::hasTable('owners') && Schema::hasColumn('owners', 'screening_credits')) {
            Schema::table('owners', fn (Blueprint $t) => $t->dropColumn('screening_credits'));
        }
    }
};
