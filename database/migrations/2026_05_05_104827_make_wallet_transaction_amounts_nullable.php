<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->decimal('gross_amount', 12, 2)->nullable()->change();
            $table->decimal('commission_rate', 5, 2)->nullable()->change();
            $table->decimal('commission_amount', 12, 2)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->decimal('gross_amount', 12, 2)->nullable(false)->change();
            $table->decimal('commission_rate', 5, 2)->nullable(false)->change();
            $table->decimal('commission_amount', 12, 2)->nullable(false)->change();
        });
    }
};