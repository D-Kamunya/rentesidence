<?php

namespace App\Console\Commands;

use App\Models\Affiliate;
use App\Models\AffiliateProduct;
use App\Models\AffiliateWithdrawal;
use App\Models\Lead;
use App\Models\LeadSuggestion;
use App\Models\Owner;
use App\Models\User;
use App\Services\AffiliateCommissionService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * ONE-OFF: wipe the V1 affiliate roster so a fresh batch can be onboarded clean.
 *
 * V1 never saw real usage, so there is no commission/money history to preserve. This removes:
 *   - every affiliate record (hard delete — cascades the empty academy-progress/commission rows);
 *   - the affiliate login users (soft delete), EXCEPT graduated affiliates whose user is shared
 *     with a tenant account — those are reverted to a plain tenant, never deleted;
 *   - the owner accounts the affiliates created (soft delete owner + login user — reversible;
 *     their property/unit subtree is left inert, and any tenants under them are left untouched);
 *   - the leads they created (hard delete; activities/suggestions go with them).
 *
 * SAFETY: dry-run by default (prints exactly what it will do); pass --force to apply, inside a
 * single transaction. Refuses outright if any affiliate has an unpaid balance or an in-flight
 * withdrawal, so funds can never be orphaned even if the "no usage" assumption is wrong.
 *
 *   php artisan affiliates:wipe-v1            # dry run — report only
 *   php artisan affiliates:wipe-v1 --force    # perform the wipe
 */
class WipeV1Affiliates extends Command
{
    protected $signature = 'affiliates:wipe-v1 {--keep= : Comma-separated affiliate ids and/or emails to PRESERVE (never wiped)} {--force : Actually perform the wipe (omit for a dry run)}';

    protected $description = 'One-off: wipe the V1 affiliate roster (and the owner/lead records they created) for a fresh batch. Dry run unless --force.';

    public function handle(): int
    {
        $force = (bool) $this->option('force');

        $allAffiliates = Affiliate::with('user')->get();
        if ($allAffiliates->isEmpty()) {
            $this->info('No affiliates found — nothing to wipe.');
            return self::SUCCESS;
        }

        // ---- Resolve the --keep preserve-list (ids and/or emails) ----
        $keepIds = [];
        $keepEmails = [];
        foreach (array_filter(array_map('trim', explode(',', (string) $this->option('keep')))) as $tok) {
            if (str_contains($tok, '@')) {
                $keepEmails[] = strtolower($tok);
            } elseif (ctype_digit($tok)) {
                $keepIds[] = (int) $tok;
            }
        }
        $isKept = function (Affiliate $a) use ($keepIds, $keepEmails): bool {
            $email = strtolower((string) optional($a->user)->email);
            return in_array((int) $a->id, $keepIds, true) || ($email !== '' && in_array($email, $keepEmails, true));
        };

        $commissionSvc = app(AffiliateCommissionService::class);
        $balanceOf = fn (Affiliate $a): float => (float) $commissionSvc->getAvailableBalance((int) $a->id);

        $wipeSet = $allAffiliates->reject($isKept)->values();
        $keptSet = $allAffiliates->filter($isKept)->values();

        // ---- Full roster (every affiliate, marked KEEP/WIPE, with balance) ----
        $this->newLine();
        $this->info('V1 affiliate wipe — ' . ($force ? 'EXECUTING' : 'DRY RUN (pass --force to apply)'));
        $this->table(['affiliate_id', 'email', 'type', 'balance', 'disposition'], $allAffiliates->map(function ($a) use ($isKept, $balanceOf) {
            return [
                $a->id,
                optional($a->user)->email ?? '(no user)',
                $a->origin_tenant_user_id ? 'graduated' : 'standalone',
                number_format($balanceOf($a), 2),
                $isKept($a) ? 'KEEP' : 'WIPE',
            ];
        })->all());
        if ($keptSet->isNotEmpty()) {
            $this->info('Preserving ' . $keptSet->count() . ' affiliate(s): ' . $keptSet->map(fn ($a) => optional($a->user)->email ?? ('#' . $a->id))->implode(', '));
        }
        if ($wipeSet->isEmpty()) {
            $this->warn('Nothing to wipe — every affiliate is in the --keep list.');
            return self::SUCCESS;
        }

        $affiliateIds     = $wipeSet->pluck('id')->map(fn ($v) => (int) $v)->all();
        $affiliateUserIds = $wipeSet->pluck('user_id')->filter()->map(fn ($v) => (int) $v)->all();

        // ---- Money guard — scoped to the WIPE set only (kept affiliates' funds are irrelevant) ----
        $owed = 0.0;
        $withBalance = [];
        foreach ($wipeSet as $a) {
            $b = $balanceOf($a);
            $owed += $b;
            if ($b > 0.009) {
                $withBalance[] = (optional($a->user)->email ?? ('#' . $a->id)) . ' = ' . number_format($b, 2);
            }
        }
        $inFlight = AffiliateWithdrawal::whereIn('affiliate_id', $affiliateIds)
            ->whereIn('status', [AFFILIATE_WITHDRAWAL_PENDING, AFFILIATE_WITHDRAWAL_PROCESSING])
            ->exists();
        if ($withBalance || $inFlight) {
            $this->error('ABORT — money guard tripped on affiliates marked WIPE; refusing to delete while funds are involved:');
            foreach ($withBalance as $line) {
                $this->error('  unpaid balance: ' . $line);
            }
            if ($inFlight) {
                $this->error('  a withdrawal is pending/processing.');
            }
            $this->error('Settle/zero these (or add them to --keep) first, then re-run.');
            return self::FAILURE;
        }

        // Graduated affiliates share a users row with a tenant → revert to tenant, never delete.
        $graduated         = $wipeSet->whereNotNull('origin_tenant_user_id');
        $graduatedUserIds  = $graduated->pluck('user_id')->filter()->map(fn ($v) => (int) $v)->all();
        $standaloneUserIds = array_values(array_diff($affiliateUserIds, $graduatedUserIds));

        // Owners the affiliates created (clean slate: soft delete owner + its login user).
        $owners       = Owner::whereIn('affiliate_id', $affiliateIds)->get();
        $ownerIds     = $owners->pluck('id')->map(fn ($v) => (int) $v)->all();
        $ownerUserIds = $owners->pluck('user_id')->filter()->map(fn ($v) => (int) $v)->all();

        // Leads created by affiliates (leads.affiliate_id stores the affiliate USER id).
        $leadIds       = Lead::whereIn('affiliate_id', $affiliateUserIds)->pluck('id')->all();
        $productCount  = AffiliateProduct::whereIn('affiliate_id', $affiliateIds)->count();

        // ---- Plan (the WIPE set only) ----
        $this->newLine();
        $this->line('Plan for the ' . count($affiliateIds) . ' affiliate(s) marked WIPE:');
        $this->table(['What', 'Count', 'Action'], [
            ['Affiliate records', count($affiliateIds), 'hard delete'],
            ['  - standalone affiliate users', count($standaloneUserIds), 'soft delete'],
            ['  - graduated (ex-tenant) users', count($graduatedUserIds), 'revert role -> tenant (kept)'],
            ['Owners created by affiliates', count($ownerIds), 'soft delete owner + login user'],
            ['  - owner login users', count($ownerUserIds), 'soft delete'],
            ['Leads created by affiliates', count($leadIds), 'hard delete (+ activities/suggestions)'],
            ['Affiliate products', $productCount, 'hard delete'],
            ['Academy progress / commissions', '(auto)', 'cascade on affiliate delete'],
        ]);

        if (! $force) {
            $this->warn('Dry run only — nothing changed. Re-run with --force to perform the wipe.');
            return self::SUCCESS;
        }

        DB::transaction(function () use ($affiliateIds, $standaloneUserIds, $graduated, $ownerIds, $ownerUserIds, $leadIds) {
            // 1. Leads + children (lead_activities cascade on lead delete; suggestions removed first).
            if ($leadIds) {
                LeadSuggestion::whereIn('lead_id', $leadIds)->delete();
                Lead::whereIn('id', $leadIds)->delete();
            }

            // 2. Owners created by affiliates → soft delete owner + login user (reversible).
            if ($ownerIds) {
                Owner::whereIn('id', $ownerIds)->delete();        // soft delete (Owner uses SoftDeletes)
            }
            if ($ownerUserIds) {
                User::whereIn('id', $ownerUserIds)->delete();     // soft delete (User uses SoftDeletes)
            }

            // 3. Affiliate side-table with no cascading FK.
            AffiliateProduct::whereIn('affiliate_id', $affiliateIds)->delete();

            // 4. Hard-delete affiliate rows — DB FKs cascade academy_progress/commissions/payments,
            //    and SET NULL on owners.affiliate_id + affiliate_withdrawals.affiliate_id.
            Affiliate::whereIn('id', $affiliateIds)->delete();

            // 5. Affiliate users: standalone → soft delete; graduated → revert to a plain tenant.
            if ($standaloneUserIds) {
                User::whereIn('id', $standaloneUserIds)->delete(); // soft delete
            }
            foreach ($graduated as $aff) {
                if ($aff->user_id) {
                    User::where('id', $aff->user_id)->update(['role' => USER_ROLE_TENANT]);
                }
            }
        });

        $this->info('Done. V1 affiliates wiped; owners/leads they created removed; graduated users reverted to tenant.');
        return self::SUCCESS;
    }
}
