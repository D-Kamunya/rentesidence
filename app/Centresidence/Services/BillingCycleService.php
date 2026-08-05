<?php

namespace App\Centresidence\Services;

use App\Centresidence\Events\BillingCycleStarted;
use App\Centresidence\Models\PropertyModule;
use App\Centresidence\Support\Money;
use App\Models\Property;
use Illuminate\Support\Carbon;

/**
 * The monthly billing job (handbook event: BillingCycleStart). For each
 * property with active modules it runs both engines:
 *   - CommissionEngine        → subscription-billed modules → commission invoice
 *   - InfrastructureCostEngine → transaction-billed non-metered → infra invoice
 *
 * The base subscription amount per property is resolved by a caller-supplied
 * closure so this engine stays decoupled from the legacy package/subscription
 * tables. Defaults to zero when none is provided.
 */
class BillingCycleService
{
    public function __construct(
        private CommissionEngine $commissionEngine,
        private InfrastructureCostEngine $infrastructureCostEngine
    ) {
    }

    /**
     * @param  callable|null  $subscriptionResolver  fn(Property $p): Money
     * @return array{month:string, commission_invoices:int, infrastructure_invoices:int}
     */
    public function runForMonth(Carbon $month, ?callable $subscriptionResolver = null): array
    {
        $month = $month->copy()->startOfMonth();
        $resolver = $subscriptionResolver ?? fn (Property $p): Money => Money::zero();

        BillingCycleStarted::dispatch($month);

        $propertyIds = PropertyModule::query()
            ->active()
            ->distinct()
            ->pluck('property_id');

        $commissionCount = 0;
        $infraCount = 0;

        foreach ($propertyIds as $propertyId) {
            $property = Property::find($propertyId);
            if (! $property) {
                continue;
            }

            $commission = $this->commissionEngine->generateForProperty(
                $property,
                $month,
                $resolver($property) ?? Money::zero()
            );
            if ($commission) {
                $commissionCount++;
            }

            $infra = $this->infrastructureCostEngine->generateForProperty($property, $month);
            if ($infra) {
                $infraCount++;
            }
        }

        return [
            'month' => $month->toDateString(),
            'commission_invoices' => $commissionCount,
            'infrastructure_invoices' => $infraCount,
        ];
    }
}
