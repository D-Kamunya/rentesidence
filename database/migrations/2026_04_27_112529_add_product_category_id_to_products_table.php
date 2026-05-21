<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedBigInteger('product_category_id')
                  ->nullable()
                  ->after('category')
                  ->comment('FK to product_categories — drives commission logic');

            $table->foreign('product_category_id')
                  ->references('id')
                  ->on('product_categories')
                  ->onDelete('set null');
        });

        // Backfill existing products by matching category string to slug
        DB::statement("
            UPDATE products p
            JOIN product_categories pc ON p.category = pc.slug
            SET p.product_category_id = pc.id
            WHERE p.product_category_id IS NULL
        ");
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['product_category_id']);
            $table->dropColumn('product_category_id');
        });
    }
};