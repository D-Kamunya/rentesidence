<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Prints the EXACT details for an owner by email — their user id and the live row counts across the
 * tables a purge/restore touches — so you can key the precise owner id into restore:owner-transactions
 * (and see at a glance what's currently present vs missing). Read-only; changes nothing.
 *
 *   php artisan owner:inspect almondmaitresse@gmail.com
 */
class InspectOwner extends Command
{
    protected $signature = 'owner:inspect {email : The owner account email}';
    protected $description = 'Show an owner\'s exact user id + live row counts (read-only).';

    /** [table, owner key column] — same map as purge/restore. */
    public const TABLE_MAP = [
        ['owner_packages',            'user_id'],
        ['subscription_orders',       'user_id'],
        ['product_orders',            'user_id'],
        ['orders',                    'user_id'],
        ['invoices',                  'owner_user_id'],
        ['transactions',              'user_id'],
        ['owner_wallets',             'user_id'],
        ['owner_credit_transactions', 'owner_user_id'],
    ];

    public function handle(): int
    {
        $email = trim((string) $this->argument('email'));
        $user  = User::where('email', $email)->first();
        if (! $user) {
            $this->error("No user with email \"{$email}\".");
            return self::FAILURE;
        }

        $name = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''));
        $this->info("Owner: {$name} <{$email}>");
        $this->line('<comment>user id = ' . $user->id . '</comment>   (key this into restore:owner-transactions)');

        $rows = [];
        foreach (self::TABLE_MAP as [$table, $col]) {
            $rows[] = [$table, $col, DB::table($table)->where($col, $user->id)->count()];
        }
        $this->table(['Table', 'Owner key', 'Rows currently in live'], $rows);

        return self::SUCCESS;
    }
}
