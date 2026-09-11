<?php

namespace App\Http\Controllers\Owner;

use App\Centresidence\Models\PropertyModule;
use App\Centresidence\Services\UtilityTariffService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * The owner sets the retail tariff (units_per_kes) for their OWN metered utility module. Only hard
 * guard = the system-integrity FLOOR (owner revenue must stay >= 0, i.e. the tariff covers our
 * per-token commission) — that's our economics, not law. The regulatory ceiling is ADVISORY only
 * (flashed, never blocks): the owner owns the system and is solely liable for compliance. Commission
 * stays admin-set (gas-only); the owner only touches units_per_kes. IDOR-scoped to owner_id.
 */
class UtilityTariffController extends Controller
{
    public function update(Request $request, UtilityTariffService $svc)
    {
        $data = $request->validate([
            'property_module_id' => 'required|integer',
            // Owners set the PRICE a tenant pays per unit (KES per litre / per kg) — explicit and
            // natural; we convert to the stored units_per_kes knob under the hood.
            'price_per_unit'     => 'required|numeric|gt:0',
        ]);

        $module = PropertyModule::with(['module', 'tokenConfig', 'property'])
            ->where('owner_id', (int) auth()->id())
            ->find($data['property_module_id']);

        abort_unless($module, 404); // IDOR guard — must be this owner's module

        if (! optional($module->module)->is_metered) {
            return back()->with('error', __('This module is not metered, so it has no tariff.'));
        }

        // A metered module can be priced even before a token config exists — create one on first
        // save, inheriting the module's catalogue defaults (our supply margin for gas; the unit label).
        $config = $module->tokenConfig;
        if (! $config) {
            $utilityClass = $svc->utilityClass(optional($module->module)->key);
            $config = new \App\Centresidence\Models\ModuleTokenConfig([
                'property_module_id'                      => $module->id,
                'centresidence_commission_per_token_unit' => (float) (optional($module->module)->token_commission_per_unit ?? 0),
                'token_unit_label'                        => optional($module->module)->unit
                    ?: ($utilityClass === 'gas' ? 'Kg' : ($utilityClass === 'water' ? 'Litres' : 'units')),
                'is_active'                               => true,
            ]);
            $config->property_module_id = $module->id;
        }

        $commission = (string) ($config->centresidence_commission_per_token_unit ?? '0');
        $price      = (string) $data['price_per_unit'];
        $units      = $svc->unitsFromPrice($price); // stored knob = 1 / price

        // HARD floor — the ONE block: the price must cover our commission (owner revenue >= 0).
        if (! $svc->meetsFloor($units, $commission)) {
            return back()->with('error', __('That price is too low — it would not cover our supply margin on this utility. Please increase the rate.'));
        }

        $config->units_per_kes = $units; // owner_revenue_per_token_unit re-derives on save
        $config->save();

        // Advisory (informational, always surfaced — carries the compliance/indemnity line).
        $advisory = $svc->advisory(
            $svc->pricePerUnit($units),
            $svc->utilityClass(optional($module->module)->key),
            optional($module->property)->city
        );
        $message = __('Your tariff has been updated.') . ' ' . $advisory['message'];

        return back()->with($advisory['level'] === 'warning' ? 'warning' : 'success', $message);
    }
}
