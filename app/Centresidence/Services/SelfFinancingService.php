<?php

namespace App\Centresidence\Services;

use App\Centresidence\Models\ModulePricingCatalogueItem;
use App\Centresidence\Models\SelfFinancedModule;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Self-financing: an owner funds a module deployment themselves (no partner, no
 * facility, no rent deduction). The cost is the same transparent hardware +
 * installation total used everywhere else — the owner just pays it directly.
 */
class SelfFinancingService
{
    public function createOrder(int $ownerId, int $propertyId, ModulePricingCatalogueItem $item, int $quantity): SelfFinancedModule
    {
        $hardware = $item->baseCost($quantity);
        $installation = $item->installationCost($quantity);
        $total = $hardware->plus($installation);

        return DB::transaction(function () use ($ownerId, $propertyId, $item, $quantity, $hardware, $installation, $total) {
            $order = SelfFinancedModule::create([
                'owner_id' => $ownerId,
                'property_id' => $propertyId,
                'module_id' => $item->module_id,
                'catalogue_item_id' => $item->id,
                'quantity' => $quantity,
                'hardware_cost' => $hardware->toDecimal(),
                'installation_cost' => $installation->toDecimal(),
                'total_cost' => $total->toDecimal(),
                'status' => SelfFinancedModule::STATUS_PENDING_PAYMENT,
            ]);

            $order->forceFill([
                'reference' => 'SELF-' . now()->year . '-' . str_pad((string) $order->id, 5, '0', STR_PAD_LEFT),
            ])->save();

            return $order;
        });
    }

    public function markPaid(SelfFinancedModule $order): SelfFinancedModule
    {
        $order->forceFill(['status' => SelfFinancedModule::STATUS_PAID, 'paid_at' => Carbon::now()])->save();

        return $order;
    }

    public function markDeployed(SelfFinancedModule $order): SelfFinancedModule
    {
        $order->forceFill(['status' => SelfFinancedModule::STATUS_DEPLOYED, 'deployed_at' => Carbon::now()])->save();

        return $order;
    }
}
