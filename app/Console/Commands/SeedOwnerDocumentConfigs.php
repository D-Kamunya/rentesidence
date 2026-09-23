<?php

namespace App\Console\Commands;

use App\Models\KycConfig;
use App\Models\User;
use Illuminate\Console\Command;

/**
 * One-time backfill: give existing owners the default plug-and-play document-config
 * requests (National ID, Good Conduct, KRA PIN, Passport Photo) so their tenants can
 * upload documents without the owner configuring anything first. New owners get these
 * at account creation (setOwnerDefaultDocumentConfig); this covers owners created
 * before that was wired. Idempotent — skips any owner that already has a config.
 */
class SeedOwnerDocumentConfigs extends Command
{
    protected $signature = 'owners:seed-document-configs {--dry-run : List what would be seeded without writing}';
    protected $description = 'Seed default document-config requests for owners that have none (plug-and-play backfill).';

    public function handle(): int
    {
        $ownerIds = User::where('role', USER_ROLE_OWNER)->pluck('id');
        $dryRun   = (bool) $this->option('dry-run');
        $seeded   = 0;

        foreach ($ownerIds as $ownerId) {
            // withTrashed: skip owners who ever had a config (incl. ones they deliberately
            // deleted) — matches the self-healing KycConfigService::ensureDefaults rule.
            if (KycConfig::withTrashed()->where('owner_user_id', $ownerId)->exists()) {
                continue;
            }

            if ($dryRun) {
                $this->line("  [dry-run] would seed defaults for owner user_id={$ownerId}");
                $seeded++;
                continue;
            }

            setOwnerDefaultDocumentConfig($ownerId);
            $seeded++;
        }

        $this->info(($dryRun ? '[dry-run] ' : '') . "Seeded default document configs for {$seeded} owner(s) with none.");

        return 0;
    }
}
