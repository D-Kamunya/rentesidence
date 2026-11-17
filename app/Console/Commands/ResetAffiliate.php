<?php

namespace App\Console\Commands;

use App\Models\Affiliate;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Resets a TEST affiliate account back to fresh — KEEPS the login, wipes its activity so its test
 * leads stop inflating the main lead count and it reads like a brand-new affiliate. Preview-first:
 * run WITHOUT --yes to see exactly what it would remove (a dry run), then add --yes to execute.
 *
 *   php artisan affiliate:reset dimaxas424@hiringview.com          # preview
 *   php artisan affiliate:reset dimaxas424@hiringview.com --yes    # execute
 *
 * Removes (hard delete — these are test rows): leads + lead suggestions (keyed on the affiliate's
 * USER id), academy progress, and commission / commission-payment / withdrawal rows (keyed on the
 * affiliate id). Un-attributes any owners it referred (owners.affiliate_id → null; the owners
 * themselves are untouched). Resets academy status + the welcome/engagement cadence + nudge counters.
 * Does NOT delete the user or affiliate record.
 */
class ResetAffiliate extends Command
{
    protected $signature = 'affiliate:reset {email : The affiliate account email} {--yes : Actually execute (otherwise just preview)}';
    protected $description = 'Reset a test affiliate to fresh (clears its leads, academy progress, commissions, cadence). Preview without --yes.';

    public function handle(): int
    {
        $email = trim((string) $this->argument('email'));
        $user  = User::where('email', $email)->first();
        if (! $user) {
            $this->error("No user with email \"{$email}\".");
            return self::FAILURE;
        }
        $affiliate = Affiliate::where('user_id', $user->id)->first();
        if (! $affiliate) {
            $this->error("User \"{$email}\" is not an affiliate.");
            return self::FAILURE;
        }

        $uid = (int) $user->id;        // leads + suggestions key on the affiliate's USER id
        $aid = (int) $affiliate->id;   // money + academy + owner attribution key on the affiliate id

        $counts = [
            ['leads',                         DB::table('leads')->where('affiliate_id', $uid)->count()],
            ['lead_suggestions',              DB::table('lead_suggestions')->where('affiliate_id', $uid)->count()],
            ['affiliate_academy_progress',    DB::table('affiliate_academy_progress')->where('affiliate_id', $aid)->count()],
            ['affiliate_commissions',         DB::table('affiliate_commissions')->where('affiliate_id', $aid)->count()],
            ['affiliate_commission_payments', DB::table('affiliate_commission_payments')->where('affiliate_id', $aid)->count()],
            ['affiliate_withdrawals',         DB::table('affiliate_withdrawals')->where('affiliate_id', $aid)->count()],
            ['owners to un-attribute',        DB::table('owners')->where('affiliate_id', $aid)->count()],
        ];

        $name = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''));
        $this->info("Affiliate: {$name} <{$email}>  (affiliate id={$aid}, user id={$uid})");
        $this->table(['Will remove / reset', 'Rows'], $counts);
        $this->line('Also resets: academy_status→not_started, academy_certified_at, welcomed_at, engagement cadence, nudge counters.');

        if (! $this->option('yes')) {
            $this->warn('PREVIEW only — nothing changed. Re-run with --yes to execute.');
            return self::SUCCESS;
        }

        DB::transaction(function () use ($uid, $aid, $affiliate) {
            DB::table('leads')->where('affiliate_id', $uid)->delete();
            DB::table('lead_suggestions')->where('affiliate_id', $uid)->delete();
            DB::table('affiliate_academy_progress')->where('affiliate_id', $aid)->delete();
            DB::table('affiliate_commissions')->where('affiliate_id', $aid)->delete();
            DB::table('affiliate_commission_payments')->where('affiliate_id', $aid)->delete();
            DB::table('affiliate_withdrawals')->where('affiliate_id', $aid)->delete();
            // Un-attribute owners (keep the owners; just drop the referral link).
            DB::table('owners')->where('affiliate_id', $aid)->update(['affiliate_id' => null]);

            $affiliate->forceFill([
                'academy_status'            => 'not_started',
                'academy_certified_at'      => null,
                'welcomed_at'               => null,
                'engagement_followup_at'    => null,
                'engagement_followup_count' => 0,
                'engagement_followup_step'  => null,
                'activation_nudge_count'    => 0,
                'activation_nudged_at'      => null,
            ])->save();
        });

        $this->info("Affiliate \"{$email}\" reset to fresh.");
        return self::SUCCESS;
    }
}
