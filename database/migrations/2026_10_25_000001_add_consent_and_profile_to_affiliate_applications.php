<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Deepens the affiliate application into the authoritative opt-in record:
 *  - coverage_area / experience / finance_contacts  → territory + vetting + the finance-partner
 *    pipeline (banking contacts an applicant already has).
 *  - agreed_at / consent_ip / consent_version        → RECORDED consent to the commission-only
 *    Partner Terms, so "I didn't read the JD / I thought there was a salary" (the V1 dispute) has
 *    a timestamped, versioned, IP-stamped answer on the row itself — no PDF chasing.
 * Column-guarded so a re-run on a shared host is safe (self-healing).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('affiliate_applications', function (Blueprint $table) {
            if (! Schema::hasColumn('affiliate_applications', 'coverage_area')) {
                $table->string('coverage_area')->nullable()->after('location'); // areas they'll actually cover
            }
            if (! Schema::hasColumn('affiliate_applications', 'experience')) {
                $table->text('experience')->nullable()->after('pitch'); // prior sales / real-estate experience
            }
            if (! Schema::hasColumn('affiliate_applications', 'finance_contacts')) {
                $table->text('finance_contacts')->nullable()->after('experience'); // banks/SACCOs/lenders they know
            }
            if (! Schema::hasColumn('affiliate_applications', 'agreed_at')) {
                $table->timestamp('agreed_at')->nullable()->after('admin_note'); // when they accepted the terms
            }
            if (! Schema::hasColumn('affiliate_applications', 'consent_ip')) {
                $table->string('consent_ip', 45)->nullable()->after('agreed_at'); // IPv4/IPv6 at acceptance
            }
            if (! Schema::hasColumn('affiliate_applications', 'consent_version')) {
                $table->string('consent_version', 40)->nullable()->after('consent_ip'); // which terms version
            }
        });
    }

    public function down(): void
    {
        Schema::table('affiliate_applications', function (Blueprint $table) {
            foreach (['coverage_area', 'experience', 'finance_contacts', 'agreed_at', 'consent_ip', 'consent_version'] as $col) {
                if (Schema::hasColumn('affiliate_applications', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
