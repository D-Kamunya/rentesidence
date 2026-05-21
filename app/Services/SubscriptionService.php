<?php

namespace App\Services;

use App\Models\GatewayCurrency;
use App\Models\OwnerPackage;
use App\Models\Package;
use App\Models\User;
use App\Traits\ResponseTrait;
use Carbon\Carbon;

class SubscriptionService
{
    use ResponseTrait;

    public function getCurrentPlan($userId = null)
    {
        $userId = $userId ?? auth()->id();
    
        $ownerPackage = OwnerPackage::query()
            ->leftJoin('subscription_orders', 'subscription_orders.id', '=', 'owner_packages.order_id')
            ->leftJoin('packages', 'packages.id', '=', 'owner_packages.package_id')
            ->where('owner_packages.user_id', $userId)
            ->whereIn('owner_packages.status', [ACTIVE])
            ->whereDate('owner_packages.end_date', '>=', now())
            ->select([
                'owner_packages.*',
                'subscription_orders.duration_type',
                // Pull commission columns from packages table
                'packages.commission_markup',
                'packages.commission_discount',
                'packages.max_marketplace_listings',
                'packages.monthly_sms_credits',
                'packages.pricing_model',
                'packages.name as package_name',
            ])
            ->first();
    
        return $ownerPackage?->makeHidden([
            'created_at', 'updated_at', 'deleted_at',
            'is_trail', 'order_id', 'package_id', 'user_id',
        ]);
    }

    public function getAllPackages()
    {
        return Package::where('status', ACTIVE)->where('is_trail', '!=', ACTIVE)->get();
    }

    public function getById($id)
    {
        $package = Package::query()->findOrFail($id);
        return $package?->makeHidden(['created_at', 'deleted_at', 'updated_at']);
    }

    public function getCurrencyByGatewayId($id)
    {
        $userId = User::where('role', USER_ROLE_ADMIN)->first()->id;
        $currencies = GatewayCurrency::where(['owner_user_id' => $userId, 'gateway_id' => $id])->get();
        foreach ($currencies as $currency) {
            $currency->symbol =  $currency->symbol;
        }
        return $currencies?->makeHidden(['created_at', 'updated_at', 'deleted_at', 'gateway_id', 'owner_user_id']);
    }

    public function cancel()
    {
        return OwnerPackage::query()
            ->where(['user_id' => auth()->id(), 'status' => ACTIVE])
            ->whereDate('end_date', '>=', now()->toDateTimeString())
            ->update(['status' => DEACTIVATE]);
    }
    public function getSubscriptionState($userId = null)
    {
        $userId = $userId ?? auth()->id();

        $currentPlan = $this->getCurrentPlan($userId);

        if ($currentPlan) {
            $expiry = Carbon::parse($currentPlan->end_date)->startOfDay();
            $today = now()->startOfDay();
            $daysLeft = $today->diffInDays($expiry, false);

            if ($daysLeft <= 3) {
                return [
                    'state' => 'expiring',
                    'days_left' => $daysLeft,
                    'expiry_date' => $expiry
                ];
            }

            return [
                'state' => 'active',
                'days_left' => $daysLeft,
                'expiry_date' => $expiry
            ];
        }

        // If no current plan, check if user ever had one
        $latestPlan = OwnerPackage::query()
            ->where('user_id', $userId)
            ->orderByDesc('end_date')
            ->first();

        if (!$latestPlan) {
            return ['state' => 'none'];
        }

        return ['state' => 'expired'];
    }

    // In SubscriptionService.php

    /**
     * Get unit limit information for the current owner
     * 
     * @return array
     */
    public function getUnitLimit()
    {
        $user = auth()->user();
        
        // Get the active owner package for this user
        $activePackage = \App\Models\OwnerPackage::where('user_id', $user->id)
            ->where('status', ACTIVE)
            ->latest()
            ->first();
        
        if (!$activePackage) {
            return [
                'total' => 0,
                'used' => 0,
                'remaining' => 0,
                'package_name' => 'No Plan'
            ];
        }
        
        // Total units allowed by the package
        $totalUnits = $activePackage->max_unit ?? 0;
        
        // Count used units across all properties belonging to this owner
        // PropertyUnit belongs to Property which has owner_user_id = users.id
        $usedUnits = \App\Models\PropertyUnit::whereHas('property', function ($query) use ($user) {
            $query->where('owner_user_id', $user->id);
        })->count();
        
        $remainingUnits = max(0, $totalUnits - $usedUnits);
        
        return [
            'total' => $totalUnits,
            'used' => $usedUnits,
            'remaining' => $remainingUnits,
            'package_name' => $activePackage->name
        ];
    }
}
