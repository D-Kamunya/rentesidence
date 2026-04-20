<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

 
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
 
            // 'marketplace' = visible in marketplace, unclaimed
            // 'claimed'     = affiliate has taken ownership
            // null          = normal affiliate-submitted lead (not from marketplace)
            $table->enum('marketplace_status', ['marketplace', 'claimed'])
                  ->nullable()
                  ->after('status');
 
            // When admin loaded this lead into the marketplace
            $table->timestamp('marketplace_at')->nullable()->after('marketplace_status');
 
            // When an affiliate claimed it
            $table->timestamp('claimed_at')->nullable()->after('marketplace_at');
 
            // 'admin'     = loaded by admin into marketplace
            // 'affiliate' = submitted by affiliate directly
            $table->enum('source', ['admin', 'affiliate'])
                  ->default('affiliate')
                  ->after('claimed_at');
 
            // Track how many times this lead has cycled through the marketplace
            $table->unsignedTinyInteger('marketplace_cycles')->default(0)->after('source');
        });
 
    }
 
    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn([
                'marketplace_status',
                'marketplace_at',
                'claimed_at',
                'source',
                'marketplace_cycles',
            ]);
        });
    }
};
