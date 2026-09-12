<?php

namespace Database\Seeders;

use App\Models\Package;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * The owner subscription CATALOGUE — the KENYA / East-Africa ladder.
 *
 * MODEL = flat-per-band (confirmed against the admin package modal, which computes
 * monthly_price = per_monthly_price × max_unit). An owner pays ONE flat price for the
 * band their unit-count falls into; adding units within a band does not raise the bill
 * ("grow your portfolio without growing your costs"). max_unit is the band CEILING; the
 * previous band's ceiling is the implicit floor (Free ≤30 → Starter 31–60 → …). This is
 * a mainstream global model (RentRedi/DoorLoop/Buildium all price flat).
 *
 * ⚠️ THESE ARE KENYAN (KES) PRICES ONLY. Do NOT treat them as the global price via FX
 * conversion — that underprices the West 5–10× and under-signals value. A per-market
 * (USD/EUR) ladder is a separate, market-calibrated set of bands and a hard requirement
 * before the first non-KE market (see memory: per-market-pricing-requirement).
 *
 * AUTHORITATIVE-ONCE: a version sentinel (package_catalog_v1) force-establishes the
 * correct catalogue exactly once — fixing the stale/inverted legacy rows (e.g. Free's
 * commission markup was < Starter's) and seeding the new bands — then locks so it never
 * silently reverts an admin's later price edits on a redeploy. Bump the sentinel (v2, …)
 * to intentionally re-baseline. Idempotent for fresh installs (no sentinel → seeds all).
 *
 * NOT seeded on purpose:
 *  - Portfolio+ (1000+ units) = a "talk to us" sales CTA, never a self-serve row (a
 *    KES 0 unlimited-unit plan would be a free-everything exploit). >1000 units simply
 *    hits the unit gate and is routed to sales.
 *  - Agency bands — gated on the agency sitting (agency-system-design).
 */
class PackageCatalogSeeder extends Seeder
{
    public function run(): void
    {
        // v5 sets maintainer/invoice/auto-invoice limits to -1 (unlimited) — they were 0,
        // which reads/enforces as "zero allowed". (v4 soft-deleted stale bands; v3 zeroed the
        // FREE SMS grant; v2 flattened/tapered the paid grants.)
        if (getOption('package_catalog_v5')) {
            return;
        }

        // name, max_unit(ceiling), per_monthly, pricing_model, is_default, is_trail,
        // commission markup, commission discount (descending net delta → highest effective
        // rate on Free, lowest on the top tier; 3% floor in CommissionService protects it),
        // monthly SMS grant (reset each cycle — no carry-over).
        $bands = [
            // SMS grant = ~2 SMS/unit as a perk on PAID bands (email-first covers receipts/
            // notices; the SMS-worthy load is the bounded payment-reminder cadence), TAPERING at
            // the top so we don't give away our SMS revenue line to the largest, least price-
            // sensitive owners (they buy beyond the perk). No rollover — reset each cycle.
            // FREE gets ZERO grant: a free owner's whole value is usage revenue and SMS is the
            // earliest/most reliable usage rail — a recurring free grant would permanently blunt
            // it. Email reminders stay free, so the tier isn't crippled; SMS is paid from msg 1
            // (normalised in KE). Trial keeps a grant — it's a time-boxed conversion tool.
            // name             maxU   perM  model           def trail  mkup  disc   sms
            ['Free',            30,      0,  'free',          1,   0,    2.0,  0.0,     0],
            ['Starter',         60,     50,  'subscription',  0,   0,    1.5,  0.0,   120],
            ['Growth',         100,     40,  'subscription',  0,   0,    1.0,  0.0,   200],
            ['Professional',   150,     39,  'subscription',  0,   0,    0.5,  0.0,   300],
            ['Business',       200,     38,  'subscription',  0,   0,    0.0,  0.0,   400],
            ['Enterprise',     400,     30,  'subscription',  0,   0,    0.0,  0.5,   600],
            ['Corporate',      600,     29,  'subscription',  0,   0,    0.0,  1.0,   800],
            ['Institutional', 1000,     24,  'subscription',  0,   0,    0.0,  1.5,  1000],
            // Transaction: 1% of rent, zero upfront, unlocks financing. No unit ceiling.
            ['Transaction', 1000000,     0,  'transaction',   0,   0,    0.0,  1.0,   100],
            // Trial: admin one-tap taste of the full experience; time-boxed via end_date +
            // ExpireTrials, cost-capped by a SMALL SMS grant (not unlimited).
            ['Trial',           50,      0,  'free',          0,   1,    0.0,  0.0,    50],
        ];

        DB::transaction(function () use ($bands) {
            foreach ($bands as [$name, $maxUnit, $perMonthly, $model, $isDefault, $isTrail, $markup, $discount, $sms]) {
                // Flat band price = per-unit × ceiling. Annual = 2 months free (×10).
                $perYearly    = $perMonthly * 10;
                $monthlyPrice = $perMonthly * $maxUnit;
                $yearlyPrice  = $perYearly  * $maxUnit;

                Package::updateOrCreate(
                    ['name' => $name],
                    [
                        'slug'                     => $name, // existing rows use name-as-slug
                        'type'                     => 2,
                        'pricing_model'            => $model,
                        'max_unit'                 => $maxUnit,
                        // Unit-only gating: everything else is UNLIMITED. property/tenant are
                        // dead columns (getOwnerLimit short-circuits them to PHP_INT_MAX), but
                        // maintainer/invoice/auto-invoice ARE read (getOwnerLimit enforces any
                        // value != -1) and shown on the plan card, so they must be -1 =
                        // unlimited — NOT 0 (which reads/enforces as "zero allowed").
                        'max_property'             => -1,
                        'max_tenant'               => -1,
                        'max_maintainer'           => -1,
                        'max_invoice'              => -1,
                        'max_auto_invoice'         => -1,
                        'ticket_support'           => ACTIVE,
                        'notice_support'           => ACTIVE,
                        'per_monthly_price'        => $perMonthly,
                        'per_yearly_price'         => $perYearly,
                        'monthly_price'            => $monthlyPrice,
                        'yearly_price'             => $yearlyPrice,
                        'commission_markup'        => $markup,
                        'commission_discount'      => $discount,
                        'max_marketplace_listings' => 0, // 0 = unlimited (listing cap lifted for all)
                        'monthly_sms_credits'      => $sms,
                        'is_default'               => $isDefault,
                        'is_trail'                 => $isTrail,
                        'status'                   => ACTIVE,
                    ]
                );
            }

            // Retire stale legacy bands not in the canonical ladder (e.g. Platinum @5u) so a
            // fresh seed is authoritative end-to-end. SOFT delete (SoftDeletes) — the row is
            // hidden from the catalogue/admin list but kept, so any historical subscription_order
            // reference still resolves via withTrashed(). Safe: no owners are on legacy bands.
            // Fresh installs have nothing to prune; existing DBs shed the leftovers.
            Package::whereNotIn('name', array_column($bands, 0))->delete();
        });

        setOption('package_catalog_v5', '1');
    }
}
