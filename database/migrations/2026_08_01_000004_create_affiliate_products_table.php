<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Affiliate OS WP-A — participation pivot: which products each affiliate works.
 * Keyed on affiliates.id (the affiliate IDENTITY / Affiliate model PK), NOT the
 * user id — participation is about the affiliate entity. (Note the lead/commission
 * `affiliate_id` columns store the owning USER id; that convention is unchanged.)
 * Backfilled: every existing affiliate participates in the default product.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::create('affiliate_products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('affiliate_id')->index(); // → affiliates.id
            $table->string('product')->index();
            $table->timestamp('joined_at')->nullable();
            $table->timestamps();
            $table->unique(['affiliate_id', 'product'], 'affiliate_product_unique');
        });

        // Backfill: enrol every existing affiliate into the default product.
        $default = (string) config('affiliate_os.default_product', 'property_sales');
        $now     = now();

        DB::table('affiliates')->orderBy('id')->pluck('id')
            ->chunk(500)
            ->each(function ($ids) use ($default, $now) {
                $rows = $ids->map(fn ($id) => [
                    'affiliate_id' => $id,
                    'product'      => $default,
                    'joined_at'    => $now,
                    'created_at'   => $now,
                    'updated_at'   => $now,
                ])->all();

                DB::table('affiliate_products')->insertOrIgnore($rows);
            });
    }

    public function down()
    {
        Schema::dropIfExists('affiliate_products');
    }
};
