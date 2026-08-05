<?php

namespace App\Services;

use App\Centresidence\Services\OwnerBillingStandingService;
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
    /**
     * The owner's unified account standing — plan status AND module-infra status
     * in one signal, driving both the dashboard banner and the readonly gate.
     *
     * Plan problems dominate the banner (expired/none/expiring). When the plan is
     * fine, unpaid infra can still downgrade standing: `overdue` → 'restricted'
     * (readonly/degraded access until cleared), `due` → 'infra_due' (gentle nudge).
     * Backward-compatible: still returns state/days_left/expiry_date, plus `infra`,
     * `plan_state`, and `restricted`.
     */
    public function getSubscriptionState($userId = null)
    {
        $userId = $userId ?? auth()->id();

        $plan  = $this->getPlanState($userId);
        $infra = $this->infraStanding($userId);
        $block = $this->infraReadonly($userId); // cadence-aware (monthly infra rides with the plan)

        $overall = $plan['state'];
        if (in_array($plan['state'], ['active', 'expiring'], true)) {
            if ($block) {
                $overall = 'restricted';
            } elseif ($plan['state'] === 'active' && ($infra['amount_due'] ?? 0) > 0) {
                $overall = 'infra_due';
            }
        }

        return array_merge($plan, [
            'state'      => $overall,
            'plan_state' => $plan['state'],
            'infra'      => $infra,
            // readonly signal — cadence-aware, so it can be true even when the banner
            // reads 'expired' (a lapsed monthly owner with unpaid bundled infra).
            'restricted' => $block,
            'renew'      => $this->latestRenewablePlan($userId),
        ]);
    }

    /**
     * The owner's latest PAID subscription plan (regardless of expiry) as renew
     * params — lets the banner/card send them straight to a pre-filled checkout
     * (reuse the same plan) instead of the plan-selection modal. Null if none.
     *
     * @return array{package_id:int, duration_type:int, quantity:int}|null
     */
    private function latestRenewablePlan($userId): ?array
    {
        $p = OwnerPackage::query()
            ->leftJoin('subscription_orders', 'subscription_orders.id', '=', 'owner_packages.order_id')
            ->where('owner_packages.user_id', $userId)
            ->where('owner_packages.pricing_model', 'subscription')
            ->orderByDesc('owner_packages.id')
            ->select('owner_packages.package_id', 'subscription_orders.duration_type', 'owner_packages.quantity')
            ->first();

        if (! $p || ! $p->package_id) {
            return null;
        }

        return [
            'package_id'    => (int) $p->package_id,
            'duration_type' => (int) ($p->duration_type ?? 1),
            'quantity'      => (int) ($p->quantity ?? 1),
        ];
    }

    /** Plan-side status only (active / expiring / expired / none) + days_left. */
    private function getPlanState($userId): array
    {
        $noticeDays  = max(0, (int) getOption('plan_expiry_notice_days', 3));
        $currentPlan = $this->getCurrentPlan($userId);

        if ($currentPlan) {
            $expiry   = Carbon::parse($currentPlan->end_date)->startOfDay();
            $daysLeft = now()->startOfDay()->diffInDays($expiry, false);

            return [
                'state'       => $daysLeft <= $noticeDays ? 'expiring' : 'active',
                'days_left'   => $daysLeft,
                'expiry_date' => $expiry,
            ];
        }

        // No active plan → 'expired' if they ever had one, else 'none'.
        $everHadPlan = OwnerPackage::query()->where('user_id', $userId)->exists();

        return ['state' => $everHadPlan ? 'expired' : 'none'];
    }

    /** Infra-side standing, guarded so the banner never breaks a page render. */
    private function infraStanding($userId): array
    {
        try {
            return app(OwnerBillingStandingService::class)->infraStanding((int) $userId);
        } catch (\Throwable $e) {
            return ['state' => 'current', 'amount_due' => 0.0, 'invoice_count' => 0, 'oldest_billing_month' => null];
        }
    }

    /** Cadence-aware readonly signal (monthly infra rides with the plan), guarded. */
    private function infraReadonly($userId): bool
    {
        try {
            return app(OwnerBillingStandingService::class)->isReadonly((int) $userId);
        } catch (\Throwable $e) {
            return false;
        }
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
