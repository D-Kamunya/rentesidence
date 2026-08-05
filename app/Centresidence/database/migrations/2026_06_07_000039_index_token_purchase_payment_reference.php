<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Indexes token_purchases.payment_reference so the Token Engine can cheaply
 * dedupe by the payment provider's reference — preventing a double-fired
 * payment webhook from double-crediting a tenant's wallet.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('token_purchases', function (Blueprint $table) {
            $table->index('payment_reference', 'cs_token_payref_idx');
        });
    }

    public function down(): void
    {
        Schema::table('token_purchases', function (Blueprint $table) {
            $table->dropIndex('cs_token_payref_idx');
        });
    }
};
