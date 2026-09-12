<?php

namespace Database\Seeders;

use App\Models\DemoPrepSection;
use App\Models\DemoSetting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Default DEMO-PREP guide (admin → Marketing → Demo Prep) — the sections that coach
 * affiliates through running a demo (checklist, walkthrough order, talking points,
 * objections, closing). Unseeded = a fresh deploy has no guidance for the demo phase.
 *
 * Sections are content → seeded here (self-healing, authoritative-once via
 * `demo_prep_seed_v1`; first run retires the pre-seed test rows). The DEMO-ACCOUNT
 * CREDENTIALS (demo_settings: login URL / email / password) are per-environment
 * secrets → NOT seeded; the admin designates the demo account at go-live. We only
 * ensure a demo_settings ROW exists with a helpful note so the admin page works.
 */
class DemoPrepSeeder extends Seeder
{
    public function run(): void
    {
        if (getOption('demo_prep_seed_v1')) {
            return;
        }

        $sections = require database_path('seeders/data/demo_prep_sections.php');
        $titles   = array_column($sections, 'title');

        DB::transaction(function () use ($sections, $titles) {
            foreach ($sections as $s) {
                DemoPrepSection::updateOrCreate(
                    ['title' => $s['title']],
                    ['content' => $s['content'], 'sort_order' => $s['sort_order'], 'is_active' => true]
                );
            }

            // Retire pre-seed test sections (their good siblings are seeded above).
            // Sentinel-guarded, so admin-created sections on later deploys are safe.
            DemoPrepSection::whereNotIn('title', $titles)->delete();

            // Ensure a settings row exists (admin fills the real demo-account creds at
            // go-live — a per-env manual step, never seeded).
            if (DemoSetting::count() === 0) {
                DemoSetting::create([
                    'demo_notes' => 'Set the demo account login URL, email and password above. '
                        . 'Use a dedicated demo owner account; reset its data after each session.',
                ]);
            }
        });

        setOption('demo_prep_seed_v1', '1');
    }
}
