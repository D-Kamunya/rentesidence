<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Rename the Affiliate-OS product key `property_sales` → `property_management`.
 *
 * The default product was mis-keyed "property_sales" — it's actually affiliates
 * MARKETING THE PROPERTY-MANAGEMENT SYSTEM to owners (display name was already
 * "Centresidence — Property Management"), not selling property. Renaming the key
 * fixes the drift AND frees `property_sales` for the future property-SALES branch.
 * Config + strategy classes were renamed in code (PropertyManagement*); this
 * realigns the value stamped on legacy rows. Idempotent + reversible.
 */
return new class extends Migration
{
    private array $tables = ['leads', 'affiliate_commissions', 'affiliate_products'];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'product')) {
                DB::table($table)->where('product', 'property_sales')->update(['product' => 'property_management']);
                $this->setDefault($table, 'property_management');
            }
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'product')) {
                DB::table($table)->where('product', 'property_management')->update(['product' => 'property_sales']);
                $this->setDefault($table, 'property_sales');
            }
        }
    }

    /** MySQL-only column-default realignment (sqlite has no simple ALTER DEFAULT; new
     *  rows set product explicitly via ProductRegistry::default() anyway). */
    private function setDefault(string $table, string $value): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }
        try {
            DB::statement("ALTER TABLE `{$table}` ALTER COLUMN `product` SET DEFAULT '{$value}'");
        } catch (\Throwable $e) {
            // best-effort; the code path sets product explicitly regardless
        }
    }
};
