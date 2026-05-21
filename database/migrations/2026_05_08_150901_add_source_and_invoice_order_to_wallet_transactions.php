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
        Schema::table('wallet_transactions', function (Blueprint $table) {
            // Distinguishes marketplace sales from rent commissions
            $table->enum('transaction_source', ['marketplace', 'rent'])
                ->default('marketplace')
                ->after('type')
                ->comment('marketplace=product sale, rent=invoice commission');

            // Links rent commissions back to the invoice order
            $table->unsignedBigInteger('invoice_order_id')
                ->nullable()
                ->after('product_order_id')
                ->comment('Nullable — only set for rent commission transactions');

            $table->foreign('invoice_order_id')
                ->references('id')
                ->on('orders')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->dropForeign(['invoice_order_id']);
            $table->dropColumn(['transaction_source', 'invoice_order_id']);
        });
    }
};
