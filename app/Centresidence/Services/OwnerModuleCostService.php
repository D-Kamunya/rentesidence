<?php

namespace App\Centresidence\Services;

use App\Centresidence\Models\InfrastructureTopology;
use App\Centresidence\Models\PropertyModule;
use App\Centresidence\Support\Money;
use Illuminate\Support\Carbon;

/**
 * Read-only "what your modules cost this month" summary for an owner, for
 * DISPLAY on the My Subscription page. Computes the live recurring cost of the
 * owner's subscription-billed modules (platform software fee + gateway usage
 * per active device) without persisting anything — the monthly billing cycle
 * remains the source of truth for actual charges.
 */
class OwnerModuleCostService
{
    public function __construct(private ModuleCostCalculator $calculator)
    {
    }

    /**
     * @param  string  $billingModel  Which modules to summarise — SUBSCRIPTION
     *   (billed monthly with the plan) or TRANSACTION (recovered from rent).
     * @return array{lines: array<int,array>, total: string, has_modules: bool}
     */
    public function currentMonthly(int $ownerId, string $billingModel = PropertyModule::BILLING_SUBSCRIPTION): array
    {
        $month = Carbon::now()->startOfMonth();
        $periodDays = $month->daysInMonth;

        $propertyModules = PropertyModule::query()
            ->active()
            ->where('owner_id', $ownerId)
            ->where('billing_model', $billingModel)
            ->with('module.activeCostComponents', 'property')
            ->get();

        if ($propertyModules->isEmpty()) {
            return ['lines' => [], 'total' => '0.00', 'has_modules' => false];
        }

        // Properties carrying an active gateway topology — gates the
        // requires_gateway components (e.g. lorawan_gateway_usage).
        $gatewayProperties = InfrastructureTopology::query()
            ->active()
            ->effectiveOn($month->toDateString())
            ->where('owner_id', $ownerId)
            ->pluck('property_id')->flip();

        $lines = [];
        $total = Money::zero();

        foreach ($propertyModules as $pm) {
            $module = $pm->module;
            if (! $module) {
                continue;
            }

            $deviceCount = (int) $pm->active_meter_count;
            $hasGateway = $gatewayProperties->has($pm->property_id);

            foreach ($module->activeCostComponents as $component) {
                // Full-month estimate (activeDays = periodDays) for a forward-
                // looking "this month" figure.
                $cost = $this->calculator->componentCost($component, $deviceCount, $periodDays, $periodDays, $hasGateway);
                if ($cost === null) {
                    continue;
                }

                $line = $this->calculator->buildLine($module, $component, $deviceCount, $cost);
                $line['property_name'] = optional($pm->property)->name;
                $lines[] = $line;
                $total = $total->plus($cost);
            }
        }

        return ['lines' => $lines, 'total' => $total->toDecimal(), 'has_modules' => ! empty($lines)];
    }
}
