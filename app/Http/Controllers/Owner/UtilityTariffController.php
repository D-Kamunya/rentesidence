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
            'units_per_kes'      => 'required|numeric|gt:0',
        ]);

        $module = PropertyModule::with(['module', 'tokenConfig', 'property'])
            ->where('owner_id', (int) auth()->id())
            ->find($data['property_module_id']);

        abort_unless($module, 404); // IDOR guard — must be this owner's module

        if (! optional($module->module)->is_metered) {
            return back()->with('error', __('This module is not metered, so it has no tariff.'));
        }
        $config = $module->tokenConfig;
        if (! $config) {
            return back()->with('error', __('This module has no token configuration to price yet.'));
        }

        $commission = (string) ($config->centresidence_commission_per_token_unit ?? '0');
        $units      = (string) $data['units_per_kes'];

        // HARD floor — the ONE block: the tariff must cover our commission (owner revenue >= 0).
        if (! $svc->meetsFloor($units, $commission)) {
            return back()->with('error', __('That tariff is too low — it would not cover the platform commission on this utility. Please increase the rate.'));
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
