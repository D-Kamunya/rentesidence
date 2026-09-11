<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Field-study → quotation → apply workflow for CUSTOM infrastructure installs (e.g. reticulated
 * gas): unlike standardised per-unit modules (water meters × N), these need a per-property site
 * survey + a bespoke quotation before the owner can finance them. `modules.requires_field_study`
 * flags such modules; `field_study_requests` tracks each owner's survey → quote → apply.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('modules') && ! Schema::hasColumn('modules', 'requires_field_study')) {
            Schema::table('modules', function (Blueprint $t) {
                $t->boolean('requires_field_study')->default(false)->after('is_metered');
            });
        }

        if (! Schema::hasTable('field_study_requests')) {
            Schema::create('field_study_requests', function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('owner_id');            // users.id (the landlord/owner)
                $t->unsignedBigInteger('property_id')->nullable();
                $t->unsignedBigInteger('module_id');           // the custom-install module (e.g. gas)
                $t->string('status')->default('requested');    // requested/surveyed/quoted/applied/cancelled
                $t->text('note')->nullable();                   // owner's request note
                $t->decimal('quoted_amount', 14, 2)->nullable(); // installer/admin quote
                $t->text('quote_note')->nullable();             // admin/installer note with the quote
                $t->unsignedBigInteger('quoted_by')->nullable();
                $t->timestamp('quoted_at')->nullable();
                $t->timestamps();
                $t->softDeletes();

                $t->index(['owner_id', 'status']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('field_study_requests');
        if (Schema::hasTable('modules') && Schema::hasColumn('modules', 'requires_field_study')) {
            Schema::table('modules', fn (Blueprint $t) => $t->dropColumn('requires_field_study'));
        }
    }
};
