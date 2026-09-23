<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Allow 'token' as a wallet-transaction source, so an owner's NET token revenue
 * (after commission + any fallback) can be credited to their OwnerWallet — the
 * uniform payout path for both subscription and transaction owners.
 * MySQL-only enum extension; legacy table, guarded.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql' && Schema::hasColumn('wallet_transactions', 'transaction_source')) {
            DB::statement("ALTER TABLE wallet_transactions MODIFY transaction_source ENUM('marketplace','rent','token') NOT NULL");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql' && Schema::hasColumn('wallet_transactions', 'transaction_source')) {
            DB::statement("ALTER TABLE wallet_transactions MODIFY transaction_source ENUM('marketplace','rent') NOT NULL");
        }
    }
};
