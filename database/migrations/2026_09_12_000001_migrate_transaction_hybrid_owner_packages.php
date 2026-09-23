<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Retire the "Free tier + transaction billing" HYBRID.
 *
 * The legacy financing flow flipped only `owner_packages.pricing_model` to
 * 'transaction' while leaving `package_id` on the owner's previous tier — so an
 * owner showed as their old plan (e.g. Free) AND transaction billing at once.
 * Transaction is a real PACKAGE an owner is moved onto (see PaymentModeService::
 * switchTo, now fixed to assign it). This backfills any owner still on the old
 * hybrid so package_id and pricing_model agree — they sit cleanly on the
 * Transaction plan. Idempotent (re-running is a no-op once aligned).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('owner_packages') || ! Schema::hasTable('packages')) {
            return;
        }

        $tx = DB::table('packages')
            ->where('pricing_model', 'transaction')
            ->where('status', 1)
            ->orderBy('id')
            ->first();

        if (! $tx) {
            return; // no canonical Transaction package to align to
        }

        // Only the denormalised columns setUserPackage() writes onto owner_packages;
        // commission/SMS/marketplace figures are read from the packages join (via the
        // now-corrected package_id), so they follow automatically.
        DB::table('owner_packages')
            ->where('status', 1)
            ->where('pricing_model', 'transaction')
            ->where('package_id', '!=', $tx->id)
            ->update([
                'package_id'        => $tx->id,
                'name'              => $tx->name,
                'package_type'      => $tx->type,
                'quantity'          => $tx->max_unit,
                'max_unit'          => $tx->max_unit,
                'max_property'      => $tx->max_property,
                'max_tenant'        => $tx->max_tenant,
                'max_maintainer'    => $tx->max_maintainer,
                'max_invoice'       => $tx->max_invoice,
                'max_auto_invoice'  => $tx->max_auto_invoice,
                'ticket_support'    => $tx->ticket_support,
                'notice_support'    => $tx->notice_support,
                'monthly_price'     => $tx->monthly_price,
                'yearly_price'      => $tx->yearly_price,
                'per_monthly_price' => $tx->per_monthly_price,
                'per_yearly_price'  => $tx->per_yearly_price,
                'updated_at'        => now(),
            ]);
    }

    public function down(): void
    {
        // Irreversible: the pre-hybrid package_id isn't recoverable from the row
        // (historical owner_packages rows retain the old tiers if ever needed).
    }
};
