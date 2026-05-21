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
        Schema::table('sms_credit_transactions', function (Blueprint $table) {
            $table->string('payment_id')->nullable()->after('reference');
        });
    }
    
    public function down(): void
    {
        Schema::table('sms_credit_transactions', function (Blueprint $table) {
            $table->dropColumn('payment_id');
        });
    }
};
