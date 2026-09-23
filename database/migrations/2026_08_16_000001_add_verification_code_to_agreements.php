<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * An unguessable per-agreement verification code, printed on the certificate. Lets anyone
 * holding a certificate look the agreement up (by its Ref # + code) on a public verify page
 * and confirm authenticity — including uploading the document so the SERVER computes its
 * SHA-256 and compares to the recorded hash. No one has to run a hash tool by hand.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('agreements') && ! Schema::hasColumn('agreements', 'verification_code')) {
            Schema::table('agreements', function (Blueprint $table) {
                $table->string('verification_code', 32)->nullable()->unique()->after('document_hash');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('agreements') && Schema::hasColumn('agreements', 'verification_code')) {
            Schema::table('agreements', function (Blueprint $table) {
                $table->dropUnique(['verification_code']);
                $table->dropColumn('verification_code');
            });
        }
    }
};
