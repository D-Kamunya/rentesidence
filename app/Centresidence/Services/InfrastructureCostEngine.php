<?php

namespace App\Centresidence\Services;

use App\Centresidence\Events\InfrastructureInvoiceGenerated;
use App\Centresidence\Models\InfrastructureTopology;
use App\Centresidence\Models\OwnerInfrastructureInvoice;
use App\Centresidence\Models\PropertyModule;
use App\Centresidence\Support\Money;
use App\Models\Property;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Infrastructure Cost Engine (handbook §6.2 / §8.3).
 *
 * For TRANSACTION-billed modules, metered costs are recovered from token
 * revenue (Token Engine, WP4). Non-metered costs (locks, parking) have no token
 * flow, so they are billed directly here as a separate
 * `owner_infrastructure_invoice` per (owner, property, month). Idempotent.
 *
 * Design note (handbook §4.3 vs §8.3 reconciliation): owners are billed via
 * non-metered module cost components × device count — NOT by distributing the
 * gateway's monthly_base_cost. The topology base cost is Centresidence's
 * internal cost target (used for gateway gating + cost-recovery analytics),
 * recorded but not charged, so there is no double billing. This is what makes
 * handbook §19 Test Case 2 (locks 2 × KES 75 = KES 150) reconcile exactly.
 */
class InfrastructureCostEngine
{
    public function __construct(private ModuleCostCalculator $calculator)
    {
    }

    public function generateForProperty(Property $property, Carbon $month): ?OwnerInfrastructureInvoice
    {
        $month = $month->copy()->startOfMonth();
        $periodDays = $month->daysInMonth;
        $ownerId = $property->owner_user_id; // legacy properties link owner via owner_user_id

        $propertyModules = PropertyModule::query()
            ->active()
            ->where('property_id', $property->id)
            ->where('billing_model', PropertyModule::BILLING_TRANSACTION)
            ->with('module.activeCostComponents')
            ->get();

        if ($propertyModules->isEmpty()) {
            return null;
        }

        $hasGateway = InfrastructureTopology::query()
            ->active()
            ->effectiveOn($month->toDateString())
            ->where('owner_id', $ownerId)
            ->where('property_id', $property->id)
            ->exists();

        $lines = [];
        $total = Money::zero();

        foreach ($propertyModules as $pm) {
            $module = $pm->module;

            // Software + gateway infra costs are billed for ALL modules (metered
            // too). For metered modules, per-token commission is now a SEPARATE
            // income share (gas only) carved by the TokenEngine — it no longer
            // doubles as infra-cost recovery.
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

                $lines[] = $this->calculator->buildLine($module, $component, $deviceCount, $cost);
                $total = $total->plus($cost);
            }
        }

        // Nothing to bill.
        if (empty($lines)) {
            return null;
        }

        $invoice = DB::transaction(function () use ($ownerId, $property, $month, $lines, $total) {
            return OwnerInfrastructureInvoice::updateOrCreate(
                [
                    'owner_id'      => $ownerId,
                    'property_id'   => $property->id,
                    // Match on the Carbon (start-of-month) so the lookup value
                    // is identical to the stored datetime on re-run (idempotent).
                    'billing_month' => $month,
                ],
                [
                    'breakdown_json' => $lines,
                    'total_amount'   => $total->toDecimal(),
                    'status'         => OwnerInfrastructureInvoice::STATUS_PENDING,
                ]
            );
        });

        InfrastructureInvoiceGenerated::dispatch($invoice);

        return $invoice;
    }

    /**
     * Steady-state monthly infrastructure cost for a property's already-deployed
     * transaction-billed modules — full-month rate, no proration, no DB write.
     *
     * Used by the financing feasibility gate/projection: this recurring cost is
     * recovered from the same rent that services a new facility, so it must be
     * counted when checking whether a facility fits the owner's deduction cap.
     *
     * @return array{cost: \App\Centresidence\Support\Money, modules: int}
     */
    public function projectedMonthlyForProperty(Property $property, ?Carbon $month = null): array
    {
        $month = ($month ?? Carbon::now())->copy()->startOfMonth();
        $periodDays = $month->daysInMonth;
        $ownerId = $property->owner_user_id;

        $propertyModules = PropertyModule::query()
            ->active()
            ->where('property_id', $property->id)
            ->where('billing_model', PropertyModule::BILLING_TRANSACTION)
            ->with('module.activeCostComponents')
            ->get();

        if ($propertyModules->isEmpty()) {
            return ['cost' => Money::zero(), 'modules' => 0];
        }

        $hasGateway = InfrastructureTopology::query()
            ->active()
            ->effectiveOn($month->toDateString())
            ->where('owner_id', $ownerId)
            ->where('property_id', $property->id)
            ->exists();

        $total = Money::zero();
        $counted = 0;
        foreach ($propertyModules as $pm) {
            $module = $pm->module;
            if (! $module) {
                continue;
            }

            $deviceCount = (int) $pm->active_meter_count;
            $moduleHadCost = false;
            foreach ($module->activeCostComponents as $component) {
                // Full-month rate (activeDays = periodDays) for a steady-state figure.
                $cost = $this->calculator->componentCost($component, $deviceCount, $periodDays, $periodDays, $hasGateway);
                if ($cost !== null) {
                    $total = $total->plus($cost);
                    $moduleHadCost = true;
                }
            }
            if ($moduleHadCost) {
                $counted++;
            }
        }

        return ['cost' => $total, 'modules' => $counted];
    }
}
