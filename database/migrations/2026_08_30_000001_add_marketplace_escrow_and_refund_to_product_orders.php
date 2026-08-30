<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Marketplace escrow + refund tracking on product_orders.
 *
 * Escrow (best-practice marketplace model): the platform HOLDS a paid order's proceeds
 * until it is DELIVERED, then releases the net to the owner's wallet. So a refund before
 * release is trivial (no clawback), and a refund after release uses the existing reversal.
 *
 * settlement_status: null = legacy/pre-escrow (already credited under the old immediate model)
 *                    · held = paid, platform holds proceeds, owner not yet credited
 *                    · released = delivered, owner credited
 *                    · refunded = buyer refunded (no owner credit stands)
 * refund_status:     null · requested (awaiting admin green-light) · processing (B2C in flight)
 *                    · refunded · failed
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_orders', function (Blueprint $table) {
            if (! Schema::hasColumn('product_orders', 'settlement_status')) {
                $table->string('settlement_status')->nullable()->after('fulfilment_status');
            }
            if (! Schema::hasColumn('product_orders', 'settlement_released_at')) {
                $table->timestamp('settlement_released_at')->nullable()->after('settlement_status');
            }
            if (! Schema::hasColumn('product_orders', 'refund_status')) {
                $table->string('refund_status')->nullable()->after('settlement_released_at');
            }
            if (! Schema::hasColumn('product_orders', 'refund_reference')) {
                $table->string('refund_reference')->nullable()->after('refund_status');
            }
            if (! Schema::hasColumn('product_orders', 'refund_amount')) {
                $table->decimal('refund_amount', 12, 2)->nullable()->after('refund_reference');
            }
            if (! Schema::hasColumn('product_orders', 'refunded_at')) {
                $table->timestamp('refunded_at')->nullable()->after('refund_amount');
            }
        });
    }

    public function down(): void
    {
        Schema::table('product_orders', function (Blueprint $table) {
            foreach (['settlement_status', 'settlement_released_at', 'refund_status', 'refund_reference', 'refund_amount', 'refunded_at'] as $col) {
                if (Schema::hasColumn('product_orders', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
