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
    protected $signature = 'affiliates:wipe-v1 {--force : Actually perform the wipe (omit for a dry run)}';

    protected $description = 'One-off: wipe the V1 affiliate roster (and the owner/lead records they created) for a fresh batch. Dry run unless --force.';

    public function handle(): int
    {
        $force = (bool) $this->option('force');

        $affiliates = Affiliate::with('user')->get();
        if ($affiliates->isEmpty()) {
            $this->info('No affiliates found — nothing to wipe.');
            return self::SUCCESS;
        }

        $affiliateIds     = $affiliates->pluck('id')->map(fn ($v) => (int) $v)->all();
        $affiliateUserIds = $affiliates->pluck('user_id')->filter()->map(fn ($v) => (int) $v)->all();

        // ---- Money guard (belt-and-braces, even though V1 had no usage) ----
        $commissionSvc = app(AffiliateCommissionService::class);
        $owed = 0.0;
        foreach ($affiliateIds as $aid) {
            $owed += (float) $commissionSvc->getAvailableBalance($aid);
        }
        $inFlight = AffiliateWithdrawal::whereIn('affiliate_id', $affiliateIds)
            ->whereIn('status', [AFFILIATE_WITHDRAWAL_PENDING, AFFILIATE_WITHDRAWAL_PROCESSING])
            ->exists();
        if ($owed > 0.009 || $inFlight) {
            $this->error('ABORT — money guard tripped; refusing to delete while funds are involved:');
            if ($owed > 0.009) {
                $this->error('  unpaid affiliate balance total: ' . number_format($owed, 2));
            }
            if ($inFlight) {
                $this->error('  a withdrawal is pending/processing.');
            }
            $this->error('Settle or zero these first, then re-run.');
            return self::FAILURE;
        }

        // Graduated affiliates share a users row with a tenant → revert to tenant, never delete.
        $graduated         = $affiliates->whereNotNull('origin_tenant_user_id');
        $graduatedUserIds  = $graduated->pluck('user_id')->filter()->map(fn ($v) => (int) $v)->all();
        $standaloneUserIds = array_values(array_diff($affiliateUserIds, $graduatedUserIds));

        // Owners the affiliates created (clean slate: soft delete owner + its login user).
        $owners       = Owner::whereIn('affiliate_id', $affiliateIds)->get();
        $ownerIds     = $owners->pluck('id')->map(fn ($v) => (int) $v)->all();
        $ownerUserIds = $owners->pluck('user_id')->filter()->map(fn ($v) => (int) $v)->all();

        // Leads created by affiliates (leads.affiliate_id stores the affiliate USER id).
        $leadIds       = Lead::whereIn('affiliate_id', $affiliateUserIds)->pluck('id')->all();
        $productCount  = AffiliateProduct::whereIn('affiliate_id', $affiliateIds)->count();

        // ---- Report ----
        $this->newLine();
        $this->info('V1 affiliate wipe — ' . ($force ? 'EXECUTING' : 'DRY RUN (pass --force to apply)'));
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

        // Show WHO is being wiped, so it can be eyeballed before --force.
        $roster = $affiliates->map(function ($a) {
            return [
                $a->id,
                optional($a->user)->email ?? '(no user)',
                $a->origin_tenant_user_id ? 'graduated' : 'standalone',
            ];
        })->all();
        $this->table(['affiliate_id', 'email', 'type'], $roster);

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
