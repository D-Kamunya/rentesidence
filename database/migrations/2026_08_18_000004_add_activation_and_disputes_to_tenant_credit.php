<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tenant ownership of the Global Tenant ID:
 *  - `activated_at` — the tenant opted IN to actively use/share their Rental ID (faster
 *    applications + loan offers). The factual profile always exists (transparency), but this
 *    is the value-exchange consent to proactively surface it.
 *  - `tenant_credit_disputes` — the fairness path: a tenant can dispute what their profile shows.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('tenant_credit_profiles') && ! Schema::hasColumn('tenant_credit_profiles', 'activated_at')) {
            Schema::table('tenant_credit_profiles', function (Blueprint $table) {
                $table->timestamp('activated_at')->nullable()->after('computed_at');
                $table->unsignedBigInteger('activated_by_user_id')->nullable()->after('activated_at');
            });
        }

        if (! Schema::hasTable('tenant_credit_disputes')) {
            Schema::create('tenant_credit_disputes', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_credit_profile_id')->index();
                $table->unsignedBigInteger('user_id');                 // the tenant who raised it
                $table->text('message');
                $table->string('status', 16)->default('open');         // open|reviewing|resolved|rejected
                $table->text('admin_note')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_credit_disputes');
        if (Schema::hasTable('tenant_credit_profiles') && Schema::hasColumn('tenant_credit_profiles', 'activated_at')) {
            Schema::table('tenant_credit_profiles', function (Blueprint $table) {
                $table->dropColumn(['activated_at', 'activated_by_user_id']);
            });
        }
    }
};
