<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Remove the dormant `follow_up` action template.
 *
 * `follow_up` was seeded as a default ActionTemplate (call-only) but the lead-suggestion
 * engine NEVER emits that category — every emitted category (intro / reminder /
 * demo_complete / trial / retention / reengage / trial_expired) already covers every lead
 * state, so it was a harmless orphan that never surfaced in any UI. The MarketingTemplateSeeder
 * is sentinel-gated and doesn't prune, so this one-off delete clears it from already-seeded
 * installs. `follow_up` will be reintroduced properly — all 3 channels + its own engine
 * trigger — if/when we build a dedicated follow-up suggestion. Fresh installs never create it
 * (removed from the seed data), so this is a no-op there.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('action_templates')) {
            DB::table('action_templates')->where('category', 'follow_up')->delete();
        }
    }

    public function down(): void
    {
        // Intentionally irreversible: the orphan carried no behaviour, so there's nothing to
        // restore. A real follow_up feature will seed its own complete template matrix.
    }
};
