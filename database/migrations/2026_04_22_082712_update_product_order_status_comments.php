<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE product_orders
                MODIFY COLUMN payment_status TINYINT NULL DEFAULT 0
                    COMMENT '0=pending, 1=paid, 2=cancelled, 3=refund_pending',
                MODIFY COLUMN order_status TINYINT NULL DEFAULT 0
                    COMMENT '0=pending, 1=completed, 2=cancelled'
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE product_orders
                MODIFY COLUMN payment_status TINYINT NULL DEFAULT 0
                    COMMENT '0=pending, 1=paid, 2=cancelled',
                MODIFY COLUMN order_status TINYINT NULL DEFAULT 0
                    COMMENT '0=pending, 1=completed'
        ");
    }
};