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
 * ONE EXCEPTION — the $authoritative block at the end FORCES a tiny set of critical toggles ON
 * every seed (the invoice + subscription reminder statuses). These are the getting-paid /
 * retention backbone and the reminder commands hard-exit when they are off, so a stray "off" is
 * a silent total outage with no legitimate upside. Only their ON/OFF status is asserted; the
 * cadence/day-lists stay admin-tunable. Add to $authoritative only for the same kind of
 * must-never-be-off safety switch.
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
            // Agreements are a usage-revenue rail like SMS, so the free plan is NOT given a
            // generous grant (that would undercut the rail). Kept small, not zero: every send
            // costs us one OTP SMS, so a tiny allowance lets a small landlord TEST e-sign
            // (which seeds Global Tenant ID adoption) before they start paying per agreement.
            'agreement_free_quota' => 3,    // free e-sign agreements/month on the free plan
            'agreement_price'      => 50,   // price per agreement credit once quota is used

            // ── Affiliate commission model (Admin → Settings → Affiliate Settings) ──
            // The rates every affiliate income line reads (subscription, rent, marketplace,
            // screening, agreement, gas token, financing). Seeded because getOption() falls
            // back to 0 — an unseeded deploy would silently pay affiliates NOTHING. The
            // first-time rate is the activation bounty (owner's first activity in each line);
            // the recurring rate then applies for RECURRING_COMMISSION_MONTHS, measured per
            // line from that line's first commission.
            'FIRST_TIME_COMMISSION_RATE' => 30, // % of our take on the owner's first event/line
            'RECURRING_COMMISSION_RATE'  => 10, // % of our take thereafter, within the window
            'RECURRING_COMMISSION_MONTHS' => 12, // how long the recurring share runs, per line

            // ── Credit rail — tenant screening ──
            'screening_free_quota' => 3,    // free screenings/month on the free plan
            'screening_price'      => 30,   // price per screening credit once quota is used

            // ── SMS behaviour / credit rail ──
            'sms_credit_price'          => 1.00, // price per SMS credit
            'sms_low_credit_threshold'  => 30,   // low-credit nudge — below the 50-credit trial grant so a fresh account isn't warned on day one
            'sms_reminder_cooldown_hours' => 24, // min gap between the same SMS reminder
            'sms_paused_digest_days'      => 7,  // look-back window for the paused-backlog re-engagement digest
            'sms_paused_digest_throttle_days' => 7, // min gap between paused-digest nudges per owner

            // ── Reminder day-lists (tunable — WHEN to remind; the ON/OFF status is forced below) ──
            // These are only-if-absent so an admin can retune the cadence. The status flags that
            // ACTIVATE reminders are authoritative (see $authoritative) — they must never sit OFF.
            'reminder_days'                       => '3,1',   // invoice: days BEFORE due to remind
            'OVERDUE_REMAINDER_DAYS'              => '1,3,7', // invoice: days AFTER due to remind
            'subscription_reminder_days'          => '7,3,1', // subscription: days BEFORE renewal
            'SUBSCRIPTION_OVERDUE_REMAINDER_DAYS' => '1,3,7', // subscription: days AFTER expiry

            // ── Subscription / plan notices ──
            'plan_expiry_notice_days' => 3, // days before expiry to warn the owner

            // ── Tenancy lifecycle ──
            'vacation_notice_days' => 30, // required notice-to-vacate period (days) before move-out
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

        // ── AUTHORITATIVE (force ON every seed) — the reminder backbone ─────────────────
        // Unlike everything above, these are OVERWRITTEN on every deploy. Rationale (user, go-live):
        // invoice + subscription reminders are the backbone of getting-paid and retention; the
        // commands hard-exit when the status is inactive, so a single stray "off" (a fat-fingered
        // toggle, a stale live value) becomes a SILENT, total reminder outage. There is no legitimate
        // reason to run with them off, so we assert them ON authoritatively. Only the ON/OFF STATUS is
        // forced — the day-lists and everyday-cadence flags remain admin-tunable (only-if-absent above).
        $authoritative = [
            'remainder_status'                      => REMAINDER_STATUS_ACTIVE,              // invoice due reminders
            'OVERDUE_REMAINDER_STATUS'              => REMAINDER_STATUS_ACTIVE,              // invoice overdue reminders
            'subscription_remainder_status'         => SUBSCRIPTION_REMAINDER_STATUS_ACTIVE, // subscription renewal reminders
            'SUBSCRIPTION_OVERDUE_REMAINDER_STATUS' => SUBSCRIPTION_REMAINDER_STATUS_ACTIVE, // subscription post-expiry reminders
        ];
        $forced = 0;
        foreach ($authoritative as $key => $value) {
            $setting = Setting::firstOrNew(['option_key' => $key]);
            if (! $setting->exists || (string) $setting->option_value !== (string) $value) {
                $setting->option_value = (string) $value;
                $setting->save();
                $forced++;
            }
        }

        // Refresh the in-memory settings cache for the rest of this request.
        config(['settings' => Setting::pluck('option_value', 'option_key')->toArray()]);

        if ($this->command) {
            $this->command->info("SystemDefaultsSeeder: planted {$planted} missing setting(s); forced {$forced} authoritative reminder toggle(s) ON; other existing values left untouched.");
        }
    }
}
