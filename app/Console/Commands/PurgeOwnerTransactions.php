<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Clears a TEST owner's financial footprint — packages + transactions — for a clean start on the live
 * site. KEEPS the login (and their properties/tenants, which aren't touched). SOFT-DELETES by default
 * (sets deleted_at) wherever the table supports it, so a mistake is reversible with --undo and no
 * backup dance. The two tables without a deleted_at column (owner_wallets, owner_credit_transactions)
 * are hard-deleted. Use --hard to hard-delete everything. Preview-first: run WITHOUT --yes to see
 * exactly what it would touch.
 *
 *   php artisan owner:purge-transactions owner@gmail.com                 # preview (soft)
 *   php artisan owner:purge-transactions owner@gmail.com --yes           # execute (soft where possible)
 *   php artisan owner:purge-transactions owner@gmail.com --yes --undo    # reverse a soft purge
 *   php artisan owner:purge-transactions owner@gmail.com --yes --hard    # hard-delete everything
 */
class PurgeOwnerTransactions extends Command
{
    protected $signature = 'owner:purge-transactions
        {email : The owner account email}
        {--yes : Actually execute (otherwise just preview)}
        {--hard : Hard-delete even soft-capable tables (not reversible)}
        {--undo : Reverse a previous SOFT purge (restore soft-deleted rows)}';
    protected $description = 'Clear a test owner\'s packages + transactions (soft by default). Preview without --yes; --undo to reverse.';

    /** [table, owner key column, soft-capable (has deleted_at)] */
    public const TABLE_MAP = [
        ['owner_packages',            'user_id',       true],
        ['subscription_orders',       'user_id',       true],
        ['product_orders',            'user_id',       true],
        ['orders',                    'user_id',       true],
        ['invoices',                  'owner_user_id', true],
        ['transactions',              'user_id',       true],
        ['owner_wallets',             'user_id',       false],
        ['owner_credit_transactions', 'owner_user_id', false],
    ];

    public function handle(): int
    {
        $email = trim((string) $this->argument('email'));
        $user  = User::where('email', $email)->first();
        if (! $user) {
            $this->error("No user with email \"{$email}\".");
            return self::FAILURE;
        }
        $id   = (int) $user->id;
        $hard = (bool) $this->option('hard');
        $undo = (bool) $this->option('undo');

        if ($undo && $hard) {
            $this->error('--undo and --hard are mutually exclusive.');
            return self::FAILURE;
        }

        $rows = [];
        foreach (self::TABLE_MAP as [$table, $col, $soft]) {
            if ($undo) {
                // Only soft-capable tables can be un-deleted; count currently soft-deleted rows.
                $n = $soft ? DB::table($table)->where($col, $id)->whereNotNull('deleted_at')->count() : 0;
                $action = $soft ? 'restore' : 'n/a (was hard-deleted)';
            } else {
                $n = DB::table($table)->where($col, $id)->count();
                $action = ($soft && ! $hard) ? 'soft-delete' : 'HARD delete';
            }
            $rows[] = [$table, $col, $n, $action];
        }

        $name = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''));
        $this->info(($undo ? 'UNDO purge for ' : 'Purge for ') . "{$name} <{$email}>  (user id={$id})");
        $this->table(['Table', 'Owner key', 'Rows', 'Action'], $rows);
        if (! $undo) {
            $this->line('Keeps: the login, and their properties / units / tenants (not touched).');
        }

        if (! $this->option('yes')) {
            $this->warn('PREVIEW only — nothing changed. Re-run with --yes to execute.');
            return self::SUCCESS;
        }

        DB::transaction(function () use ($id, $hard, $undo) {
            foreach (self::TABLE_MAP as [$table, $col, $soft]) {
                if ($undo) {
                    if ($soft) {
                        DB::table($table)->where($col, $id)->whereNotNull('deleted_at')->update(['deleted_at' => null]);
                    }
                    continue;
                }
                if ($soft && ! $hard) {
                    DB::table($table)->where($col, $id)->whereNull('deleted_at')->update(['deleted_at' => now()]);
                } else {
                    DB::table($table)->where($col, $id)->delete();
                }
            }
        });

        $this->info($undo
            ? "Restored soft-deleted rows for \"{$email}\"."
            : 'Cleared packages + transactions for "' . $email . '"' . ($hard ? ' (hard).' : ' (soft — reversible with --undo).'));
        return self::SUCCESS;
    }
}
