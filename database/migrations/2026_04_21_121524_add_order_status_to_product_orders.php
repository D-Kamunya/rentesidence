<?php
 
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
 
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_orders', function (Blueprint $table) {
            $table->tinyInteger('order_status')
                  ->default(0)
                  ->nullable()
                  ->comment('0=pending, 1=completed')
                  ->after('payment_status');
        });
    }
 
    public function down(): void
    {
        Schema::table('product_orders', function (Blueprint $table) {
            $table->dropColumn('order_status');
        });
    }
};