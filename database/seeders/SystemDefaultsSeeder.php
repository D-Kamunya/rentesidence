<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

/**
 * Plants sane defaults for the operational "knobs" our own features read via getOption()
 * — marketplace escrow timing, the credit-rail prices/quotas, SMS behaviour, plan-notice
 * timing. Every one of these already has a code-level fallback in getOption(), so the app
 * works even unseeded; this seeder MATERIALISES them so a fresh deploy comes up fully
 * configured and every knob is visible/auditable in one place (no mental go-live checklist).
 *
 * ── Self-healing & SAFE to re-run ──────────────────────────────────────────────
 * ONLY-IF-ABSENT: a key already present in the settings table is left untouched. This is
 * the critical difference from BrandingSeeder (which overwrites): app:deploy runs seeders
 * on every deploy, so overwriting here would silently reset an admin's tuned values (e.g.
 * a return window bumped to 3) back to the default on the next pull. Never do that.
 *
 * Deliberately EXCLUDED:
 *   - Base-template general settings (app_name, frontend/email toggles) — owned by the
 *     admin General Settings UI + BrandingSeeder; not ours to seed here.
 *   - The package/plan catalog and owner Terms & Conditions — gated on the agency +
 *     free-tier sittings; seed those only once those decisions are made.
 *   - Credentials/keys (M-Pesa, SMTP, gateways) — real per-environment secrets, entered
 *     at go-live, never seeded.
 *
 *   php artisan db:seed --class=Database\\Seeders\\SystemDefaultsSeeder
 */
class SystemDefaultsSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            // ── Marketplace escrow timing (Admin → Settings → Marketplace Settings) ──
            // Return/settlement window after delivery: how long a buyer can still cancel/
            // return AND when a delivered order's funds release to the seller (same value).
            'marketplace_return_window_days' => 2,
            // Safety-net grace: release paid-but-never-delivered orders so money never
            // sticks in escrow. Kept generous — covers far-flung sellers; must be >= window.
            'marketplace_auto_release_days'  => 7,

            // ── Credit rail — agreements (Admin → Settings → Agreement Settings) ──
            'agreement_free_quota' => 10,   // free e-sign agreements/month on the free plan
            'agreement_price'      => 50,   // price per agreement credit once quota is used

            // ── Credit rail — tenant screening ──
            'screening_free_quota' => 3,    // free screenings/month on the free plan
            'screening_price'      => 30,   // price per screening credit once quota is used

            // ── SMS behaviour / credit rail ──
            'sms_credit_price'          => 1.00, // price per SMS credit
            'sms_low_credit_threshold'  => 50,   // when to fire the low-credit nudge
            'sms_reminder_cooldown_hours' => 24, // min gap between the same SMS reminder

            // ── Subscription / plan notices ──
            'plan_expiry_notice_days' => 3, // days before expiry to warn the owner
        ];

        // Pull existing keys once so we only insert what's missing (never overwrite).
        $existing = Setting::whereIn('option_key', array_keys($defaults))
            ->pluck('option_key')
            ->all();

        $planted = 0;
        foreach ($defaults as $key => $value) {
            if (in_array($key, $existing, true)) {
                continue; // admin (or a prior seed) already set it — leave it alone
            }
            Setting::create(['option_key' => $key, 'option_value' => (string) $value]);
            $planted++;
        }

        // Refresh the in-memory settings cache for the rest of this request.
        config(['settings' => Setting::pluck('option_value', 'option_key')->toArray()]);

        if ($this->command) {
            $this->command->info("SystemDefaultsSeeder: planted {$planted} missing setting(s); existing values left untouched.");
        }
    }
}
