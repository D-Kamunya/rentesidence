<?php

namespace App\Centresidence\Services;

use App\Centresidence\Events\CommissionInvoiceGenerated;
use App\Centresidence\Models\CentresidenceCommissionInvoice;
use App\Centresidence\Models\InfrastructureTopology;
use App\Centresidence\Models\PropertyModule;
use App\Centresidence\Support\Money;
use App\Models\Property;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Commission Engine (handbook §8.1, primary pathway).
 *
 * For SUBSCRIPTION-billed modules on a property, sums every active module cost
 * component (metered + non-metered) × active device count and produces a
 * `centresidence_commission_invoice` for the (owner, property, month), with the
 * base subscription added. Idempotent per cycle via updateOrCreate.
 *
 * Infrastructure topology is used to (a) gate requires_gateway components and
 * (b) record allocation context — never to double-bill the gateway base cost.
 */
class CommissionEngine
{
    public function __construct(private ModuleCostCalculator $calculator)
    {
    }

    public function generateForProperty(
        Property $property,
        Carbon $month,
        Money $subscriptionAmount
    ): ?CentresidenceCommissionInvoice {
        $month = $month->copy()->startOfMonth();
        $periodDays = $month->daysInMonth;
        $ownerId = $property->owner_user_id; // legacy properties link owner via owner_user_id

        $propertyModules = PropertyModule::query()
            ->active()
            ->where('property_id', $property->id)
            ->where('billing_model', PropertyModule::BILLING_SUBSCRIPTION)
            ->with('module.activeCostComponents')
            ->get();

        // No subscription modules and no base subscription → nothing to bill.
        if ($propertyModules->isEmpty() && ! $subscriptionAmount->isPositive()) {
            return null;
        }

        $topologies = InfrastructureTopology::query()
            ->active()
            ->effectiveOn($month->toDateString())
            ->where('owner_id', $ownerId)
            ->where('property_id', $property->id)
            ->get();
        $hasGateway = $topologies->isNotEmpty();

        $meteredLines = [];
        $nonMeteredLines = [];
        $meteredTotal = Money::zero();
        $nonMeteredTotal = Money::zero();

        foreach ($propertyModules as $pm) {
            $module = $pm->module;
            if (! $module) {
                continue;
            }
            $deviceCount = (int) $pm->active_meter_count;
            $activeDays = $pm->activeDaysInMonth($month, $periodDays);

            foreach ($module->activeCostComponents as $component) {
                $cost = $this->calculator->componentCost($component, $deviceCount, $activeDays, $periodDays, $hasGateway);
                if ($cost === null) {
                    continue;
                }

                $line = $this->calculator->buildLine($module, $component, $deviceCount, $cost);

                if ($module->is_metered) {
                    $meteredLines[] = $line;
                    $meteredTotal = $meteredTotal->plus($cost);
                } else {
                    $nonMeteredLines[] = $line;
                    $nonMeteredTotal = $nonMeteredTotal->plus($cost);
                }
            }
        }

        $infraContext = $topologies->map(fn (InfrastructureTopology $t) => [
            'infrastructure_type'   => $t->infrastructure_type,
            'infrastructure_id'     => $t->infrastructure_id,
            'allocation_percentage' => (string) $t->allocation_percentage,
            'monthly_base_cost'     => (string) $t->monthly_base_cost,
            'owner_share'           => $t->ownerShare()->toDecimal(),
        ])->all();

        $total = $subscriptionAmount->plus($meteredTotal)->plus($nonMeteredTotal);

        $invoice = DB::transaction(function () use (
            $ownerId, $property, $month, $subscriptionAmount,
            $meteredLines, $meteredTotal, $nonMeteredLines, $nonMeteredTotal,
            $infraContext, $total
        ) {
            return CentresidenceCommissionInvoice::updateOrCreate(
                [
                    'owner_id'      => $ownerId,
                    'property_id'   => $property->id,
                    // Match on the Carbon (start-of-month) so the lookup value
                    // is identical to the stored datetime on re-run (idempotent).
                    'billing_month' => $month,
                ],
                [
                    'subscription_amount'              => $subscriptionAmount->toDecimal(),
                    'metered_commission_breakdown'     => $meteredLines,
                    'metered_commission_total'         => $meteredTotal->toDecimal(),
                    'non_metered_commission_breakdown' => $nonMeteredLines,
                    'non_metered_commission_total'     => $nonMeteredTotal->toDecimal(),
                    'infrastructure_cost_breakdown'    => $infraContext,
                    'total_amount'                     => $total->toDecimal(),
                    'status'                           => CentresidenceCommissionInvoice::STATUS_PENDING,
                ]
            );
        });

        CommissionInvoiceGenerated::dispatch($invoice);

        return $invoice;
    }
}
