<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The permanent/previous address fields are OPTIONAL now (onboarding-friction pass). They were
 * created nullable, but a later ->change() migration (2023_03_06) re-typed the *_country_id /
 * *_state_id / *_city_id columns to string WITHOUT ->nullable(), which silently flipped them to
 * NOT NULL. So saving a tenant with a blank address would fail the tenant_details INSERT with a
 * 23000 violation (the next error after tenants.job). Make every permanent_/previous_ address
 * column nullable to match the validation. Guarded/idempotent; re-nullabling an already-nullable
 * column is a no-op.
 */
return new class extends Migration
{
    private array $cols = [
        'previous_address', 'previous_country_id', 'previous_state_id', 'previous_city_id', 'previous_zip_code',
        'permanent_address', 'permanent_country_id', 'permanent_state_id', 'permanent_city_id', 'permanent_zip_code',
    ];

    public function up(): void
    {
        if (! Schema::hasTable('tenant_details')) {
            return;
        }
        Schema::table('tenant_details', function (Blueprint $table) {
            foreach ($this->cols as $col) {
                if (Schema::hasColumn('tenant_details', $col)) {
                    $table->string($col)->nullable()->change();
                }
            }
        });
    }

    public function down(): void
    {
        // Not reverted — NOT NULL here was the bug; rows with null address values would block it.
    }
};
