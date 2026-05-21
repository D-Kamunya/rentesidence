<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->enum('type', ['product', 'service'])->default('product');
            $table->decimal('base_commission', 5, 2)->default(0)->comment('Base commission percentage charged by Centresidence');
            $table->tinyInteger('status')->default(1)->comment('1=active, 0=inactive');
            $table->timestamps();
        });

        // Seed categories directly
        DB::table('product_categories')->insert([
            // Essentials
            ['name' => 'Food & Groceries',   'slug' => 'foods',        'type' => 'product', 'base_commission' => 6,  'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Cooking Gas',        'slug' => 'gas',          'type' => 'product', 'base_commission' => 5,  'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Personal Care & Hygiene', 'slug' => 'personal_care', 'type' => 'product', 'base_commission' => 10, 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Beauty Products',    'slug' => 'beauty',       'type' => 'product', 'base_commission' => 12, 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Pet Supplies',       'slug' => 'pets',         'type' => 'product', 'base_commission' => 7,  'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Stationery',         'slug' => 'stationery',   'type' => 'product', 'base_commission' => 9,  'status' => 1, 'created_at' => now(), 'updated_at' => now()],

            // Lifestyle & Fashion
            ['name' => 'Clothing',           'slug' => 'clothes',      'type' => 'product', 'base_commission' => 12, 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Footwear',           'slug' => 'shoes',        'type' => 'product', 'base_commission' => 12, 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Bags & Accessories', 'slug' => 'bags',         'type' => 'product', 'base_commission' => 12, 'status' => 1, 'created_at' => now(), 'updated_at' => now()],

            // Home & Electronics
            ['name' => 'Electronics',        'slug' => 'electronics',  'type' => 'product', 'base_commission' => 8,  'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Kitchenware',        'slug' => 'kitchenware',  'type' => 'product', 'base_commission' => 9,  'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Furniture',          'slug' => 'furniture',    'type' => 'product', 'base_commission' => 7,  'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Home Essentials',    'slug' => 'home_items',   'type' => 'product', 'base_commission' => 8,  'status' => 1, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('product_categories');
    }
};
