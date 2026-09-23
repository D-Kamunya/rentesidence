<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Unit-only gating means every limit other than units is UNLIMITED. The legacy
 * packages (and the first catalogue seed) carried max_maintainer / max_invoice /
 * max_auto_invoice = 0, which reads and enforces as "zero allowed" — the plan card
 * showed "0 maintainers / 0 invoices" and the subscription page showed "61 / 0".
 * The convention for unlimited is -1 (getOwnerLimit skips any -1; the views render
 * it as ∞ / "Unlimited"). The catalogue seed (v5) fixes the packages; this aligns
 * existing owners' denormalised owner_packages rows. Idempotent.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('owner_packages')) {
            return;
        }

        DB::table('owner_packages')
            ->where('status', 1)
            ->update([
                'max_property'     => -1,
                'max_tenant'       => -1,
                'max_maintainer'   => -1,
                'max_invoice'      => -1,
                'max_auto_invoice' => -1,
                'updated_at'       => now(),
            ]);
    }

    public function down(): void
    {
        // No safe restore — the previous 0 values were the bug being corrected.
    }
};
