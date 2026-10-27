<?php

namespace Database\Seeders;

use App\Models\ActionTemplate;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Default affiliate marketing/action templates — what an affiliate actually SENDS
 * when the lead-suggestion engine fires a next-best action. Each suggestion carries
 * a `category` (intro / reminder / demo_complete / trial / retention / reengage /
 * trial_expired); SuggestionService::channelsFor() offers the matching templates
 * (email / whatsapp / call). Unseeded = the engine fires suggestions but the affiliate
 * has nothing to act with — so a fresh deploy MUST ship a complete matrix.
 *
 * Copy lives in database/seeders/data/marketing_templates.php (reviewed set + the
 * channel-gap fillers), one canonical template per (category, action_type), covering
 * all 7 engine categories × 3 channels. AUTHORITATIVE-ONCE via `marketing_templates_seed_v2`
 * so a redeploy never clobbers an admin's later edits. On the first run it also RETIRES
 * the pre-seed test templates (their good copy is folded into the defaults here).
 */
class MarketingTemplateSeeder extends Seeder
{
    public function run(): void
    {
        // Free-first copy refresh runs INDEPENDENTLY of the authoritative-once sentinel, so an
        // already-seeded deploy still picks it up. It only rewrites a demo_complete DEFAULT that
        // still carries the pre-free-first "sponsored trial account" copy — an admin-rewritten
        // template (no longer containing that phrase) is left untouched.
        $this->patchDemoCompleteFreeFirst();

        if (getOption('marketing_templates_seed_v2')) {
            return;
        }

        $rows = require database_path('seeders/data/marketing_templates.php');

        DB::transaction(function () use ($rows) {
            foreach ($rows as $r) {
                // One canonical per (category, action_type). name/message are fillable;
                // is_default is not, so set it directly.
                $tpl = ActionTemplate::updateOrCreate(
                    ['category' => $r['category'], 'action_type' => $r['action_type']],
                    ['name' => $r['name'], 'message_template' => $r['message_template']]
                );
                if (! $tpl->is_default) {
                    $tpl->is_default = true;
                    $tpl->save();
                }
            }

            // Retire any leftover non-default (test/dev) templates — their strengths were
            // mined into the canonical set above. Sentinel guards this to the first run,
            // so admin-created templates on later deploys are never touched.
            ActionTemplate::where('is_default', false)->delete();
        });

        setOption('marketing_templates_seed_v2', '1');
    }

    /**
     * Free-first: the post-demo next step is "set them up on Free", not "arrange a sponsored
     * trial". Refreshes the three demo_complete DEFAULT templates (whatsapp / call / email) to the
     * canonical free-first copy — but only where the live copy still contains the old phrase, so an
     * admin who has since rewritten the template keeps their version. Idempotent + self-guarded.
     */
    private function patchDemoCompleteFreeFirst(): void
    {
        if (getOption('marketing_templates_free_first_v1')) {
            return;
        }

        $rows = collect(require database_path('seeders/data/marketing_templates.php'))
            ->where('category', 'demo_complete');

        foreach ($rows as $r) {
            $tpl = ActionTemplate::where('category', 'demo_complete')
                ->where('action_type', $r['action_type'])
                ->where('is_default', true)
                ->first();

            // Only refresh a default still carrying the pre-free-first copy (admin edits untouched).
            if ($tpl && str_contains((string) $tpl->message_template, 'sponsored trial account')) {
                $tpl->name = $r['name'];
                $tpl->message_template = $r['message_template'];
                $tpl->save();
            }
        }

        setOption('marketing_templates_free_first_v1', '1');
    }
}
