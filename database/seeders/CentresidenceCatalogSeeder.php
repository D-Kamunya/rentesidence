<?php

namespace Database\Seeders;

use App\Centresidence\Models\Module;
use App\Centresidence\Models\ModulePricingCatalogueItem;
use Illuminate\Database\Seeder;

/**
 * PRODUCTION-SAFE module catalog. Seeds the admin-configurable module
 * environment — modules, presentation copy (owner + financier), pricing,
 * cost components and platform fee — so a fresh live install ships ready to
 * use instead of being hand-built. Contains NO users, applications, facilities
 * or other demo/transaction data; safe to run on the live server.
 *
 *   php artisan db:seed --class=Database\\Seeders\\CentresidenceCatalogSeeder
 *
 * Idempotent: presentation/financier copy is refreshed each run (it is the
 * shipped default), while cost components, pricing and platform fee are created
 * only if missing — so admin edits to rates/prices are never clobbered.
 */
class CentresidenceCatalogSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->definitions() as $i => $d) {
            $module = Module::firstOrCreate(['key' => $d['key']], [
                'name' => $d['name'], 'is_metered' => $d['metered'], 'requires_gateway' => true,
                'token_unit_label' => $d['unit'], 'is_financeable' => true, 'is_active' => true,
            ]);

            // Shipped copy — refreshed each run.
            $module->update([
                'name' => $d['name'], 'description' => $d['description'], 'tagline' => $d['tagline'],
                'cashflow_benefit' => $d['cashflow_benefit'], 'financier_overview' => $d['financier_overview'],
                'how_it_works' => $d['how_it_works'], 'benefits' => $d['benefits'],
                'icon' => $d['icon'], 'accent_color' => $d['color'], 'settlement_target' => 'centresidence',
                'display_order' => $i + 1, 'is_financeable' => true, 'is_active' => true,
                // Token economics: commission is the Centresidence income share —
                // 0 for most modules (owners keep utility revenue), set only where
                // Centresidence shares operational value (gas reticulation).
                'token_units_per_kes' => $d['units_per_kes'] ?? null,
                'token_commission_per_unit' => $d['token_commission'] ?? 0,
            ]);

            // Cost components — created only if missing (platform software fee +
            // LoRaWAN gateway usage are the platform defaults for every module).
            foreach ($d['components'] as $ci => [$cname, $rate, $gw, $fallback]) {
                $module->costComponents()->firstOrCreate(
                    ['component_name' => $cname],
                    ['cost_model' => 'per_active_device', 'rate' => $rate, 'requires_gateway' => $gw,
                     'is_fallback_eligible' => $fallback, 'is_prorated' => true, 'status' => 'active',
                     'display_order' => $ci + 1]
                );
            }

            ModulePricingCatalogueItem::firstOrCreate(['module_id' => $module->id], [
                'item_name' => $d['name'] . ' Unit', 'unit_price' => $d['price'], 'installation_cost' => $d['install'],
                'unit_label' => $d['metered'] ? 'meter' : 'lock', 'is_active' => true,
            ]);

            if ($module->platformFeeConfigs()->count() === 0) {
                $module->platformFeeConfigs()->create(['fee_percentage' => 10, 'is_active' => true]);
            }
        }
    }

    private function definitions(): array
    {
        return [
            [
                'key' => 'water_meter', 'name' => 'Smart Water Meter', 'metered' => true, 'unit' => 'Litres',
                'components' => [['platform_software_fee', 50, false, true], ['lorawan_gateway_usage', 50, true, true]],
                'price' => 3500, 'install' => 800, 'icon' => 'ri-drop-line', 'color' => '#185FA5',
                'units_per_kes' => 5, 'token_commission' => 0, // owner keeps all water revenue
                'description' => 'A LoRaWAN smart water meter installed per unit that dispenses prepaid water against M-Pesa tokens, with real-time consumption telemetry.',
                'tagline' => 'Prepaid water that pays you upfront — no more leakage or unpaid bills.',
                'cashflow_benefit' => 'Tenants prepay for water as tokens, so you collect revenue before a drop is used. It eliminates unpaid water bills, stops losses from estimated billing, and turns a cost centre into reliable monthly cashflow.',
                'financier_overview' => 'Water is the most defensible metered facility to finance: demand is non-discretionary and repayment is deducted at source from rent before it reaches the owner, so arrears are low. Owners adopt it to end unpaid bills and leakage, which keeps utilisation high. One meter is deployed per unit, so the financed amount scales cleanly with unit count.',
                'how_it_works' => "1. A smart water meter is installed for each unit.\n2. Tenants buy water tokens via M-Pesa, anytime.\n3. The meter dispenses exactly what they paid for.\n4. Revenue lands with you automatically, minus a small platform fee.",
                'benefits' => ['Prepaid — stop chasing water bills', 'Ends leakage & estimated-billing losses', 'Real-time consumption visibility', 'Tenants pay only for what they use'],
            ],
            [
                'key' => 'gas_meter', 'name' => 'Smart Gas Meter', 'metered' => true, 'unit' => 'KG',
                'components' => [['platform_software_fee', 60, false, true], ['lorawan_gateway_usage', 40, true, true]],
                'price' => 4200, 'install' => 900, 'icon' => 'ri-fire-line', 'color' => '#854F0B',
                'units_per_kes' => 0.004, 'token_commission' => 10, // income-share (admin-tunable) on reticulated gas
                'description' => 'A LoRaWAN smart gas meter per unit that converts centralised cooking gas into a prepaid, individually-metered utility.',
                'tagline' => 'Turn shared cooking gas into a metered, prepaid earner.',
                'cashflow_benefit' => 'Centralised gas becomes a metered utility tenants top up themselves. You earn on every kilogram dispensed and remove the headache of splitting shared bills.',
                'financier_overview' => 'Gas converts a shared, hard-to-bill cost into prepaid per-unit revenue with strong, recurring demand. Like water, repayment is rent-secured at source. Deployment is one meter per unit; safety and metered control also reduce owner liability, which supports adoption and utilisation.',
                'how_it_works' => "1. A smart gas meter is fitted per unit.\n2. Tenants top up gas tokens via M-Pesa.\n3. Gas flows only for what was paid.\n4. You collect the revenue automatically.",
                'benefits' => ['New prepaid revenue stream', 'No more splitting shared gas bills', 'Safer — controlled, metered supply', 'Usage analytics per unit'],
            ],
            [
                'key' => 'smart_lock', 'name' => 'Smart Lock', 'metered' => false, 'unit' => null,
                'components' => [['lorawan_gateway_usage', 75, true, false]],
                'price' => 6000, 'install' => 1200, 'icon' => 'ri-lock-2-line', 'color' => '#534AB7',
                'description' => 'A keyless smart lock per door, controlled remotely — access is issued or revoked in seconds and every entry is logged.',
                'tagline' => 'Keyless, remote-controlled access that commands premium rent.',
                'cashflow_benefit' => 'Smart-access units let you charge higher rent, cut key-replacement and lock-change costs between tenancies, and turn units over instantly — issue or revoke access remotely in seconds.',
                'financier_overview' => 'Smart locks are a non-metered, premium-rent facility — they generate no token revenue, so repayment comes from the rent uplift and turnover/key-cost savings rather than usage. Best financed where occupancy is strong; repayment is rent-deduction secured like every facility, but underwrite against the rent increase the owner can realistically capture.',
                'how_it_works' => "1. Smart locks are installed on unit doors.\n2. Access codes are issued or revoked remotely.\n3. No physical keys to cut or recover.\n4. Every entry is logged for security.",
                'benefits' => ['Charge premium rent for smart units', 'Zero key-replacement costs', 'Instant turnover between tenants', 'Remote lock/unlock & access logs'],
            ],
        ];
    }
}
