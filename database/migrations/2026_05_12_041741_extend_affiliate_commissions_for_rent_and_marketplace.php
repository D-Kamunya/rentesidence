<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Alter existing columns
        DB::statement("ALTER TABLE affiliate_commissions 
            MODIFY subscription_id BIGINT UNSIGNED NULL");

        DB::statement("ALTER TABLE affiliate_commissions 
            MODIFY subscription_amount DECIMAL(12,2) NULL");

        DB::statement("ALTER TABLE affiliate_commissions 
            MODIFY type ENUM('NEW_CLIENT','RECURRING_CLIENT') NULL");

        // Add new columns
        DB::statement("ALTER TABLE affiliate_commissions 
            ADD COLUMN source VARCHAR(255) NOT NULL DEFAULT 'subscription' 
            COMMENT 'subscription|rent|marketplace' AFTER type");

        DB::statement("ALTER TABLE affiliate_commissions 
            ADD COLUMN order_id BIGINT UNSIGNED NULL 
            COMMENT 'Order id for rent or ProductOrder id for marketplace' AFTER source");

        DB::statement("ALTER TABLE affiliate_commissions 
            ADD COLUMN commission_amount DECIMAL(12,2) NOT NULL DEFAULT 0 
            COMMENT 'Actual payout amount to affiliate' AFTER order_id");

        DB::statement("ALTER TABLE affiliate_commissions 
            ADD COLUMN commission_rate DECIMAL(5,2) NOT NULL DEFAULT 0 
            COMMENT 'Rate used to calculate commission' AFTER commission_amount");
    }

    public function down(): void
    {
        // Drop new columns
        DB::statement("ALTER TABLE affiliate_commissions 
            DROP COLUMN source, DROP COLUMN order_id, DROP COLUMN commission_amount, DROP COLUMN commission_rate");

        // Revert altered columns back to NOT NULL
        DB::statement("ALTER TABLE affiliate_commissions 
            MODIFY subscription_id BIGINT UNSIGNED NOT NULL");

        DB::statement("ALTER TABLE affiliate_commissions 
            MODIFY subscription_amount DECIMAL(12,2) NOT NULL");

        DB::statement("ALTER TABLE affiliate_commissions 
            MODIFY type ENUM('NEW_CLIENT','RECURRING_CLIENT') NOT NULL");
    }
};
