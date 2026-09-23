<?php

namespace App\Centresidence\Simulation;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Boots an isolated in-memory sqlite database with the Centresidence schema and
 * minimal legacy-table stubs. Used by both the simulation command and the
 * DB-backed tests so the platform can be exercised end-to-end WITHOUT touching
 * the live MySQL database or running the 169 legacy migrations.
 *
 * Legacy models (User/Property/PropertyUnit) use SoftDeletes, so the stubs
 * include deleted_at.
 */
class Sandbox
{
    /**
     * Configure + reset the sandbox connection, create stub legacy tables, and
     * run the module migrations. Leaves it as the default connection.
     */
    public static function boot(string $connection = 'cs_sqlite'): void
    {
        config([
            'database.default' => $connection,
            "database.connections.$connection" => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'foreign_key_constraints' => true,
            ],
        ]);

        DB::purge($connection);
        DB::setDefaultConnection($connection);

        self::stubLegacyTables();
        self::runModuleMigrations();
    }

    public static function stubLegacyTables(): void
    {
        Schema::create('users', function ($t) {
            $t->id();
            $t->string('first_name')->nullable();
            $t->string('last_name')->nullable();
            $t->string('email')->nullable();
            $t->string('password')->nullable();
            $t->string('role')->nullable();
            $t->tinyInteger('status')->nullable();
            $t->softDeletes();
            $t->timestamps();
        });
        Schema::create('properties', function ($t) {
            $t->id();
            // Legacy properties link the owner via owner_user_id (NOT owner_id).
            $t->unsignedBigInteger('owner_user_id')->nullable();
            $t->string('name')->nullable();
            $t->softDeletes();
            $t->timestamps();
        });
        Schema::create('property_units', function ($t) {
            $t->id();
            $t->unsignedBigInteger('property_id')->nullable();
            $t->string('name')->nullable();
            $t->softDeletes();
            $t->timestamps();
        });

        // Minimal shapes of the legacy rental tables the CashflowService reads,
        // so the financing flow can be exercised in-memory.
        Schema::create('invoices', function ($t) {
            $t->id();
            $t->unsignedBigInteger('property_id')->nullable();
            $t->unsignedBigInteger('property_unit_id')->nullable();
            $t->string('month')->nullable();
            $t->decimal('amount', 14, 2)->nullable();
            $t->tinyInteger('status')->default(0); // 0=pending,1=paid,2=overdue
            $t->softDeletes();
            $t->timestamps();
        });
        Schema::create('tenants', function ($t) {
            $t->id();
            $t->unsignedBigInteger('user_id')->nullable();
            $t->unsignedBigInteger('property_id')->nullable();
            $t->unsignedBigInteger('unit_id')->nullable();
            $t->tinyInteger('status')->default(3); // 1=active
            $t->softDeletes();
            $t->timestamps();
        });
        Schema::create('expenses', function ($t) {
            $t->id();
            $t->unsignedBigInteger('property_id')->nullable();
            $t->decimal('total_amount', 14, 2)->default(0);
            $t->softDeletes();
            $t->timestamps();
        });
        // Owner pricing model (free | subscription | transaction) — gates financing.
        Schema::create('owner_packages', function ($t) {
            $t->id();
            $t->unsignedBigInteger('user_id')->nullable();
            $t->unsignedBigInteger('order_id')->nullable();
            $t->string('pricing_model')->default('free');
            $t->date('end_date')->nullable();
            $t->tinyInteger('status')->default(1);
            $t->softDeletes();
            $t->timestamps();
        });
        Schema::create('subscription_orders', function ($t) {
            $t->id();
            $t->tinyInteger('duration_type')->nullable();
            $t->timestamps();
        });

        // Owner wallet (legacy) — token net revenue is credited here.
        Schema::create('owner_wallets', function ($t) {
            $t->id();
            $t->unsignedBigInteger('user_id');
            $t->decimal('balance', 14, 2)->default(0);
            $t->timestamps();
        });
        Schema::create('wallet_transactions', function ($t) {
            $t->id();
            $t->unsignedBigInteger('owner_wallet_id');
            $t->unsignedBigInteger('product_order_id')->nullable();
            $t->unsignedBigInteger('invoice_order_id')->nullable();
            $t->string('transaction_source')->nullable();
            $t->decimal('gross_amount', 14, 2)->nullable();
            $t->decimal('commission_rate', 8, 2)->nullable();
            $t->decimal('commission_amount', 14, 2)->nullable();
            $t->decimal('net_amount', 14, 2)->nullable();
            $t->string('type')->nullable();
            $t->string('description')->nullable();
            $t->timestamps();
        });
    }

    public static function runModuleMigrations(): void
    {
        $path = base_path('app/Centresidence/database/migrations');
        foreach (glob($path . '/*.php') as $file) {
            (require $file)->up();
        }
    }
}
