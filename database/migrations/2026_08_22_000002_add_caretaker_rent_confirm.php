<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Owner-gated caretaker rent confirmation. Off by default: an owner may let their on-site
 * caretaker (maintainer) confirm that a tenant has paid rent in cash. The confirmation records a
 * cash payment against the invoice and is attributed to the caretaker (orders.confirmed_by_user_id)
 * so the money stays traceable; the owner is notified on every confirmation.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('owners') && ! Schema::hasColumn('owners', 'caretaker_can_confirm_rent')) {
            Schema::table('owners', function (Blueprint $table) {
                $table->boolean('caretaker_can_confirm_rent')->default(false)->after('user_id');
            });
        }

        if (Schema::hasTable('orders') && ! Schema::hasColumn('orders', 'confirmed_by_user_id')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->unsignedBigInteger('confirmed_by_user_id')->nullable()->after('deposit_by');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('owners', 'caretaker_can_confirm_rent')) {
            Schema::table('owners', function (Blueprint $table) {
                $table->dropColumn('caretaker_can_confirm_rent');
            });
        }
        if (Schema::hasColumn('orders', 'confirmed_by_user_id')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropColumn('confirmed_by_user_id');
            });
        }
    }
};
