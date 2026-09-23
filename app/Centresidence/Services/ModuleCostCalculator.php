<?php

namespace App\Centresidence\Services;

use App\Centresidence\Models\Module;
use App\Centresidence\Models\ModuleCostComponent;
use App\Centresidence\Support\Money;

/**
 * Turns a module's cost components into billable money + invoice line items,
 * applying the handbook's cost-model rules consistently for both the Commission
 * Engine and the Infrastructure Cost Engine.
 *
 * Single source of the billing rules (handbook §5/§8.1):
 *   - per_active_device   → rate × active device count (prorated if flagged)
 *   - flat_monthly        → flat rate (prorated if flagged)
 *   - per_unit_consumed   → token/consumption side, handled by the Token Engine
 *                           (WP4); contributes nothing to a periodic invoice
 *   - per_gateway_allocation → topology cost context, not billed per-component
 *   - requires_gateway components are charged ONLY when an active topology
 *     allocation exists for the owner/property (gateway present)
 */
class ModuleCostCalculator
{
    /**
     * Cost contribution of a single component, or null if it does not apply to
     * a periodic invoice for the given context.
     */
    public function componentCost(
        ModuleCostComponent $component,
        int $deviceCount,
        int $activeDays,
        int $periodDays,
        bool $hasGatewayAllocation
    ): ?Money {
        if ($component->requires_gateway && ! $hasGatewayAllocation) {
            return null; // gateway-usage component with no active gateway → not charged
        }

        switch ($component->cost_model) {
            case ModuleCostComponent::COST_MODEL_PER_ACTIVE_DEVICE:
                return $component->perDeviceCost($deviceCount, $activeDays, $periodDays);

            case ModuleCostComponent::COST_MODEL_FLAT_MONTHLY:
                $cost = $component->rateMoney();

                return $component->is_prorated ? $cost->prorate($activeDays, $periodDays) : $cost;

            case ModuleCostComponent::COST_MODEL_PER_UNIT_CONSUMED:
            case ModuleCostComponent::COST_MODEL_PER_GATEWAY_ALLOCATION:
            default:
                return null;
        }
    }

    /** Build a JSON-serialisable invoice line for a component. */
    public function buildLine(Module $module, ModuleCostComponent $component, int $deviceCount, Money $subtotal): array
    {
        return [
            'module_id'      => $module->id,
            'module_name'    => $module->name,
            'component_name' => $component->component_name,
            'cost_model'     => $component->cost_model,
            'device_count'   => $deviceCount,
            'rate'           => (string) $component->rate,
            'subtotal'       => $subtotal->toDecimal(),
        ];
    }
}
