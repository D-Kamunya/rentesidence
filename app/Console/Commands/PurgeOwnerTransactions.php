<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Clears a TEST owner's financial footprint — packages + transactions — for a clean start on the live
 * site. KEEPS the login (and their properties/tenants, which aren't touched); only the money records
 * go. Preview-first: run WITHOUT --yes to see exactly what it would remove (a dry run), then add --yes.
 *
 *   php artisan owner:purge-transactions owner@gmail.com          # preview
 *   php artisan owner:purge-transactions owner@gmail.com --yes    # execute
 *
 * Removes (hard delete — these are test rows): owner_packages, subscription_orders, product_orders,
 * orders, invoices, transactions, owner_wallets, owner_credit_transactions — all keyed to this owner.
 * Does NOT touch properties, units or tenants (say so and I'll extend it if you want those gone too).
 */
class PurgeOwnerTransactions extends Command
{
    protected $signature = 'owner:purge-transactions {email : The owner account email} {--yes : Actually execute (otherwise just preview)}';
    protected $description = 'Clear a test owner\'s packages + transactions for a clean start. Preview without --yes.';

    public function handle(): int
    {
        $email = trim((string) $this->argument('email'));
        $user  = User::where('email', $email)->first();
        if (! $user) {
            $this->error("No user with email \"{$email}\".");
            return self::FAILURE;
        }
        $id = (int) $user->id; // for the primary owner, user_id and owner_user_id are both their user id

        // [table, owner key column]
        $targets = [
            ['owner_packages',            'user_id'],
            ['subscription_orders',       'user_id'],
            ['product_orders',            'user_id'],
            ['orders',                    'user_id'],
            ['invoices',                  'owner_user_id'],
            ['transactions',              'user_id'],
            ['owner_wallets',             'user_id'],
            ['owner_credit_transactions', 'owner_user_id'],
        ];

        $counts = [];
        foreach ($targets as [$table, $col]) {
            $counts[] = [$table, DB::table($table)->where($col, $id)->count()];
        }

        $name = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''));
        $this->info("Owner: {$name} <{$email}>  (user id={$id})");
        $this->table(['Will remove', 'Rows'], $counts);
        $this->line('Keeps: the login, and their properties / units / tenants (not touched).');

        if (! $this->option('yes')) {
            $this->warn('PREVIEW only — nothing changed. Re-run with --yes to execute.');
            return self::SUCCESS;
        }

        DB::transaction(function () use ($targets, $id) {
            foreach ($targets as [$table, $col]) {
                DB::table($table)->where($col, $id)->delete();
            }
        });

        $this->info("Cleared packages + transactions for \"{$email}\".");
        return self::SUCCESS;
    }
}
