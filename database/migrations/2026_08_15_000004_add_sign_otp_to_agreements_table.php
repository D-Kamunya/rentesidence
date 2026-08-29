<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Per-agreement signing OTP — isolated from the login OTP (users.otp) so a signing
 * challenge never interferes with authentication and stays tied to (and auditable on)
 * the specific agreement being signed. Short-lived; cleared once consumed.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('agreements') || Schema::hasColumn('agreements', 'sign_otp')) {
            return;
        }

        Schema::table('agreements', function (Blueprint $table) {
            $table->string('sign_otp', 6)->nullable()->after('otp_verified_at');
            $table->timestamp('sign_otp_expires_at')->nullable()->after('sign_otp');
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('agreements') && Schema::hasColumn('agreements', 'sign_otp')) {
            Schema::table('agreements', function (Blueprint $table) {
                $table->dropColumn(['sign_otp', 'sign_otp_expires_at']);
            });
        }
    }
};
