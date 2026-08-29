<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Marketplace fulfilment lifecycle (dispatch). A paid order moves NONE → DISPATCHED → DELIVERED,
 * handled by the on-site caretaker (maintainer) of the buyer's property, or the owner. Separate
 * from payment_status/order_status so fulfilment is tracked cleanly. `caretaker_dispatch_enabled`
 * on owners is the owner's toggle (default ON — caretaker handles dispatch; covers the
 * owner-plays-caretaker case when off). Guarded/self-healing for the shared host.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('product_orders')) {
            Schema::table('product_orders', function (Blueprint $table) {
                if (! Schema::hasColumn('product_orders', 'fulfilment_status')) {
                    $table->unsignedTinyInteger('fulfilment_status')->default(0)->after('order_status'); // 0 none,1 dispatched,2 delivered
                }
                if (! Schema::hasColumn('product_orders', 'dispatched_at')) {
                    $table->timestamp('dispatched_at')->nullable()->after('fulfilment_status');
                }
                if (! Schema::hasColumn('product_orders', 'delivered_at')) {
                    $table->timestamp('delivered_at')->nullable()->after('dispatched_at');
                }
                if (! Schema::hasColumn('product_orders', 'dispatched_by')) {
                    $table->unsignedBigInteger('dispatched_by')->nullable()->after('delivered_at'); // users.id of who handled it
                }
            });
        }

        if (Schema::hasTable('owners') && ! Schema::hasColumn('owners', 'caretaker_dispatch_enabled')) {
            Schema::table('owners', function (Blueprint $table) {
                $table->boolean('caretaker_dispatch_enabled')->default(true)->after('user_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('product_orders')) {
            Schema::table('product_orders', function (Blueprint $table) {
                foreach (['fulfilment_status', 'dispatched_at', 'delivered_at', 'dispatched_by'] as $c) {
                    if (Schema::hasColumn('product_orders', $c)) {
                        $table->dropColumn($c);
                    }
                }
            });
        }
        if (Schema::hasTable('owners') && Schema::hasColumn('owners', 'caretaker_dispatch_enabled')) {
            Schema::table('owners', fn (Blueprint $t) => $t->dropColumn('caretaker_dispatch_enabled'));
        }
    }
};
