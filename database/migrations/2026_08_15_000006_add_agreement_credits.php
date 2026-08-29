<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Agreement credits (tokenized, SMS-credit style). Unlike SMS, the FREE tier is a MONTHLY
 * allowance computed from this month's free-covered sends (use-it-or-lose-it, no cron), so
 * we only store the PURCHASED balance here (rolls over). `agreements.billed_as` records how
 * each send was covered (plan | free | credit) so the monthly free count is auditable.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('owners') && ! Schema::hasColumn('owners', 'agreement_credits')) {
            Schema::table('owners', function (Blueprint $table) {
                $table->unsignedInteger('agreement_credits')->default(0)->after('user_id');
            });
        }

        if (Schema::hasTable('agreements') && ! Schema::hasColumn('agreements', 'billed_as')) {
            Schema::table('agreements', function (Blueprint $table) {
                $table->string('billed_as', 12)->nullable()->after('status'); // plan | free | credit
            });
        }

        if (! Schema::hasTable('agreement_credit_transactions')) {
            Schema::create('agreement_credit_transactions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('owner_user_id');
                $table->enum('type', ['purchase', 'deduct', 'refund', 'manual_topup']);
                $table->unsignedInteger('quantity');
                $table->decimal('amount_paid', 10, 2)->nullable();
                $table->unsignedInteger('balance_before');
                $table->unsignedInteger('balance_after');
                $table->string('reference')->nullable();     // STK CheckoutRequestID at purchase
                $table->string('payment_id')->nullable();
                $table->string('description')->nullable();
                $table->enum('status', ['success', 'failed', 'pending'])->default('success');
                $table->timestamps();

                $table->index('owner_user_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('agreement_credit_transactions');
        if (Schema::hasTable('owners') && Schema::hasColumn('owners', 'agreement_credits')) {
            Schema::table('owners', fn (Blueprint $t) => $t->dropColumn('agreement_credits'));
        }
        if (Schema::hasTable('agreements') && Schema::hasColumn('agreements', 'billed_as')) {
            Schema::table('agreements', fn (Blueprint $t) => $t->dropColumn('billed_as'));
        }
    }
};
