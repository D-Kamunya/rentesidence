<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::statement("ALTER TABLE sms_credit_transactions 
            MODIFY COLUMN type ENUM('purchase','deduct','refund','manual_topup','package_grant') 
            NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE sms_credit_transactions 
            MODIFY COLUMN type ENUM('purchase','deduct','refund','manual_topup') 
            NOT NULL");
    }
};
