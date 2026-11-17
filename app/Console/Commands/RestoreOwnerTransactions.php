<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Restores an owner's rows (default: invoices + orders — the tenant-score source) from a RESTORED
 * BACKUP that's been imported into a scratch database, keyed by the EXACT owner user id. It only
 * INSERTS rows that are MISSING from live (NOT EXISTS on the primary key) — it never deletes or
 * overwrites, so it can't harm current data. Preview-first: run WITHOUT --yes to see the counts.
 *
 *   # 1) import the pre-purge backup into a scratch DB (does NOT touch live), e.g.
 *   #    mysql -u USER -p -e "CREATE DATABASE centresidence_restore"
 *   #    mysql -u USER -p centresidence_restore < database/backups/2026-11-17_00-00-13.sql
 *   # 2) get the exact id:  php artisan owner:inspect almondmaitresse@gmail.com
 *   # 3) preview:           php artisan restore:owner-transactions <id> --from=centresidence_restore
 *   # 4) execute:           php artisan restore:owner-transactions <id> --from=centresidence_restore --yes
 *   # 5) rebuild scores:    php artisan screening:recompute
 *
 * --tables defaults to invoices,orders (what the tenant scores are built from). Pass a comma list to
 * restore more of what a purge removed (owner_packages, subscription_orders, product_orders,
 * transactions, owner_wallets, owner_credit_transactions). The scratch DB must have the same schema
 * as live (it's a backup of this same app), so columns line up.
 */
class RestoreOwnerTransactions extends Command
{
    protected $signature = 'restore:owner-transactions
        {owner_user_id : The owner\'s exact user id (from owner:inspect)}
        {--from= : The scratch database holding the restored backup}
        {--tables=invoices,orders : Comma list of tables to restore}
        {--yes : Actually execute (otherwise just preview)}';
    protected $description = 'Re-inject an owner\'s missing rows from a restored backup (scratch DB). Insert-only. Preview without --yes.';

    public function handle(): int
    {
        $ownerId = (int) $this->argument('owner_user_id');
        $from    = (string) $this->option('from');

        if ($ownerId <= 0) {
            $this->error('Provide a valid owner_user_id (see owner:inspect).');
            return self::FAILURE;
        }
        if ($from === '' || ! preg_match('/^[A-Za-z0-9_]+$/', $from)) {
            $this->error('Provide --from=<scratch database name> (letters, digits, underscore only).');
            return self::FAILURE;
        }

        // Owner-key column per table (same map as the purge). Only these tables may be restored.
        $colMap = [];
        foreach (InspectOwner::TABLE_MAP as [$t, $c]) {
            $colMap[$t] = $c;
        }

        $requested = array_values(array_filter(array_map('trim', explode(',', (string) $this->option('tables')))));
        foreach ($requested as $t) {
            if (! isset($colMap[$t])) {
                $this->error("Table \"{$t}\" is not restorable (allowed: " . implode(', ', array_keys($colMap)) . ').');
                return self::FAILURE;
            }
        }

        // Confirm the scratch DB is reachable (so a typo fails loudly, not silently).
        try {
            DB::select("SELECT 1 FROM `{$from}`.`invoices` LIMIT 1");
        } catch (\Throwable $e) {
            $this->error("Can't read `{$from}`.invoices — is the backup imported into a database called \"{$from}\" on this server? ({$e->getMessage()})");
            return self::FAILURE;
        }

        $plan = [];
        foreach ($requested as $table) {
            $col = $colMap[$table];
            $missing = DB::selectOne(
                "SELECT COUNT(*) AS c FROM `{$from}`.`{$table}` r
                 WHERE r.`{$col}` = ?
                 AND NOT EXISTS (SELECT 1 FROM `{$table}` l WHERE l.id = r.id)",
                [$ownerId]
            );
            $plan[] = [$table, $col, (int) ($missing->c ?? 0)];
        }

        $this->info("Restore for owner_user_id={$ownerId}  from `{$from}`  (insert-only: adds rows missing from live)");
        $this->table(['Table', 'Owner key', 'Rows to re-inject'], $plan);

        if (! $this->option('yes')) {
            $this->warn('PREVIEW only — nothing changed. Re-run with --yes to execute.');
            return self::SUCCESS;
        }

        $total = 0;
        DB::transaction(function () use ($requested, $colMap, $from, $ownerId, &$total) {
            foreach ($requested as $table) {
                $col = $colMap[$table];
                $total += DB::affectingStatement(
                    "INSERT INTO `{$table}`
                     SELECT r.* FROM `{$from}`.`{$table}` r
                     WHERE r.`{$col}` = ?
                     AND NOT EXISTS (SELECT 1 FROM `{$table}` l WHERE l.id = r.id)",
                    [$ownerId]
                );
            }
        });

        $this->info("Re-injected {$total} row(s). Now run: php artisan screening:recompute");
        return self::SUCCESS;
    }
}
