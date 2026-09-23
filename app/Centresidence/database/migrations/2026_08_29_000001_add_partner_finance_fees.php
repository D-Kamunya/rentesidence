<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Two Centresidence earning channels on financed facilities, both collected by
 * NETTING from the partner's remittances:
 *   • origination fee — one-time, % of principal, booked at facility creation,
 *     collected once disbursed;
 *   • servicing fee   — recurring, % of each remittance batch.
 * Per-partner % overrides the platform default in config('centresidence.partner_fees').
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('finance_partners', function (Blueprint $table) {
            if (! Schema::hasColumn('finance_partners', 'origination_fee_percentage')) {
                $table->decimal('origination_fee_percentage', 5, 2)->nullable()->after('settlement_account_details');
            }
            if (! Schema::hasColumn('finance_partners', 'servicing_fee_percentage')) {
                $table->decimal('servicing_fee_percentage', 5, 2)->nullable()->after('origination_fee_percentage');
            }
        });

        Schema::table('finance_facilities', function (Blueprint $table) {
            if (! Schema::hasColumn('finance_facilities', 'origination_fee_amount')) {
                $table->decimal('origination_fee_amount', 14, 2)->default(0)->after('platform_fee_settled_at');
            }
            if (! Schema::hasColumn('finance_facilities', 'origination_fee_collected')) {
                $table->decimal('origination_fee_collected', 14, 2)->default(0)->after('origination_fee_amount');
            }
        });

        Schema::table('partner_remittance_batches', function (Blueprint $table) {
            // total_amount stays = the NET actually remitted (payout reads it), so the
            // gross + fees are recorded alongside for transparency.
            if (! Schema::hasColumn('partner_remittance_batches', 'gross_amount')) {
                $table->decimal('gross_amount', 14, 2)->default(0)->after('total_amount');
            }
            if (! Schema::hasColumn('partner_remittance_batches', 'servicing_fee')) {
                $table->decimal('servicing_fee', 14, 2)->default(0)->after('gross_amount');
            }
            if (! Schema::hasColumn('partner_remittance_batches', 'origination_fee')) {
                $table->decimal('origination_fee', 14, 2)->default(0)->after('servicing_fee');
            }
            if (! Schema::hasColumn('partner_remittance_batches', 'net_amount')) {
                $table->decimal('net_amount', 14, 2)->default(0)->after('origination_fee');
            }
        });
    }

    public function down(): void
    {
        Schema::table('finance_partners', function (Blueprint $table) {
            $table->dropColumn(['origination_fee_percentage', 'servicing_fee_percentage']);
        });
        Schema::table('finance_facilities', function (Blueprint $table) {
            $table->dropColumn(['origination_fee_amount', 'origination_fee_collected']);
        });
        Schema::table('partner_remittance_batches', function (Blueprint $table) {
            $table->dropColumn(['gross_amount', 'servicing_fee', 'origination_fee', 'net_amount']);
        });
    }
};
