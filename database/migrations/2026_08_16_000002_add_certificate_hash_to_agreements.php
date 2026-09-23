<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Store the certificate PDF's own SHA-256 (alongside document_hash = the agreement
 * document's hash) so verification matches EITHER file: for an uploaded-PDF agreement the
 * two are different files (the agreement vs its certificate), and a person is likely to
 * hand over the certificate. For a text-template agreement they're the same file, so the
 * two hashes are equal.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('agreements') && ! Schema::hasColumn('agreements', 'certificate_hash')) {
            Schema::table('agreements', function (Blueprint $table) {
                $table->string('certificate_hash', 64)->nullable()->index()->after('document_hash');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('agreements') && Schema::hasColumn('agreements', 'certificate_hash')) {
            Schema::table('agreements', function (Blueprint $table) {
                $table->dropColumn('certificate_hash');
            });
        }
    }
};
