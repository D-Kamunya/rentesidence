<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Prospective-affiliate applications from the public "become an affiliate" page (a shareable link
 * the team sends to prospects in the field). Submissions land in an admin inbox; one click turns an
 * approved application into a full affiliate account (temp password + email/SMS creds via
 * AffiliateService::registerAffiliate). Kept separate from general enquiries for clean triage.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('affiliate_applications', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->index();
            $table->string('phone');
            $table->string('location')->nullable();
            $table->text('pitch')->nullable();       // "how will you promote us" — a vetting signal
            $table->string('source')->nullable();     // optional attribution (campaign / who shared the link)
            $table->string('status')->default('pending'); // pending · approved · rejected
            $table->unsignedBigInteger('converted_user_id')->nullable(); // the affiliate user once approved
            $table->text('admin_note')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('affiliate_applications');
    }
};
