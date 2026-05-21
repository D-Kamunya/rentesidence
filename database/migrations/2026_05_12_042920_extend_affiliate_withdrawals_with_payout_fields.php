<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('affiliate_withdrawals', function (Blueprint $table) {
            $table->string('phone', 20)->nullable()->after('amount');
            $table->string('mpesa_reference')->nullable()->after('status');
            $table->string('transaction_id')->nullable()->after('mpesa_reference');
            $table->timestamp('processed_at')->nullable()->after('transaction_id');
            $table->string('settlement_method')->default('b2c')->after('processed_at')
                ->comment('b2c|manual');
            $table->text('notes')->nullable()->after('settlement_method')
                ->comment('Admin notes, used for manual settlement');
        });
    }

    public function down(): void
    {
        Schema::table('affiliate_withdrawals', function (Blueprint $table) {
            $table->dropColumn([
                'phone', 'mpesa_reference', 'transaction_id',
                'processed_at', 'settlement_method', 'notes',
            ]);
        });
    }
};
