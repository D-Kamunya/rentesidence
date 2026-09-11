<?php

namespace App\Centresidence\Services;

use App\Centresidence\Exceptions\FacilityActiveModeLockException;
use App\Centresidence\Exceptions\OwnerNotInTransactionModeException;
use App\Centresidence\Models\PropertyModule;
use App\Models\Package;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Owner payment-mode rules for financing (the bridge to the legacy pricing
 * model in `owner_packages.pricing_model`).
 *
 * Financing requires TRANSACTION mode: only then does rent route to the company
 * M-Pesa account where facility repayments can be deducted at source. So:
 *   - an owner must already be on transaction mode to BEGIN an application; and
 *   - they cannot leave transaction mode while any facility is active.
 */
class PaymentModeService
{
    public const MODE_TRANSACTION = 'transaction';
    public const MODE_SUBSCRIPTION = 'subscription';
    public const MODE_FREE = 'free';

    /** The owner's current pricing model (free | subscription | transaction). */
    public function currentMode(int $ownerUserId): string
    {
        if (! Schema::hasTable('owner_packages')) {
            return 'free';
        }

        $package = DB::table('owner_packages')
            ->where('user_id', $ownerUserId)
            ->where('status', 1)
            ->latest('id')
            ->first();

        return $package->pricing_model ?? 'free';
    }

    public function isTransactionMode(int $ownerUserId): bool
    {
        return $this->currentMode($ownerUserId) === self::MODE_TRANSACTION;
    }

    /**
     * Whether the owner's pricing mode can carry a deployed module's recurring
     * infrastructure cost: transaction (recovered from rent) or subscription
     * (bundled in the plan invoice). FREE has no collection rail — modules
     * cannot be deployed for free owners (the infra cost would be uncollectable).
     */
    public function hasModuleBillingRail(int $ownerUserId): bool
    {
        return in_array(
            $this->currentMode($ownerUserId),
            [self::MODE_TRANSACTION, self::MODE_SUBSCRIPTION],
            true
        );
    }

    public function hasActiveFacility(int $ownerUserId): bool
    {
        if (! Schema::hasTable('finance_facilities')) {
            return false;
        }

        return DB::table('finance_facilities')
            ->where('owner_id', $ownerUserId)
            ->where('status', 'active')
            ->exists();
    }

    /**
     * Gate the START of a financing application: the owner must already be on
     * transaction mode (the UI prompts them to switch first).
     */
    public function assertEligibleForFinancing(int $ownerUserId): void
    {
        if (! $this->isTransactionMode($ownerUserId)) {
            throw new OwnerNotInTransactionModeException($ownerUserId);
        }
    }

    /**
     * Guard a pricing-mode switch: an owner cannot leave transaction mode while
     * a facility is active. Call this from the owner settings mode-switch flow.
     */
    public function assertCanSwitchTo(int $ownerUserId, string $newMode): void
    {
        if ($newMode !== self::MODE_TRANSACTION && $this->hasActiveFacility($ownerUserId)) {
            throw new FacilityActiveModeLockException($ownerUserId);
        }
    }

    /**
     * Switch the owner's current active package to a pricing mode (guarded).
     * Used by the owner financing flow to move onto transaction mode before
     * applying. Returns false if there is no active package to update.
     */
    public function switchTo(int $ownerUserId, string $newMode): bool
    {
        $this->assertCanSwitchTo($ownerUserId, $newMode);

        if (! Schema::hasTable('owner_packages')) {
            return false;
        }

        // Transaction (and free) are REAL packages the owner is moved ONTO — not a
        // billing flag layered over their existing tier. Resolve the canonical plan
        // for the mode and assign it, so package_id and pricing_model stay consistent
        // and the owner ends up on ONE coherent plan (no "Free tier + transaction
        // billing" hybrid). Mirrors the direct plan-selection path (activateFree).
        // Only transaction/free have a single canonical package — a subscription TIER
        // is chosen at checkout, so that mode falls through to a plain mode-flip.
        $target = null;
        if (in_array($newMode, [self::MODE_TRANSACTION, self::MODE_FREE], true) && Schema::hasTable('packages')) {
            $target = Package::query()
                ->where('status', ACTIVE)
                ->where('pricing_model', $newMode)
                ->when($newMode === self::MODE_FREE, fn ($q) => $q->where('is_default', ACTIVE))
                ->orderBy('id')
                ->first();
        }

        if ($target) {
            // 50-year duration = open-ended (matches activateFree). setUserPackage
            // deactivates the old active row, creates the new plan row, grants SMS,
            // and syncs module billing to the new mode.
            setUserPackage($ownerUserId, $target, 365 * 50, 1, null);
            return true;
        }

        // No canonical package for this mode (subscription tiers are chosen at
        // checkout, or the catalogue is misconfigured) — fall back to flipping the
        // mode on the current row so billing still follows.
        $current = DB::table('owner_packages')
            ->where('user_id', $ownerUserId)->where('status', 1)->latest('id')->first();
        if (! $current) {
            return false;
        }
        DB::transaction(function () use ($current, $ownerUserId, $newMode) {
            DB::table('owner_packages')->where('id', $current->id)->update(['pricing_model' => $newMode]);
            $this->syncModulesToOwnerMode($ownerUserId);
        });

        return true;
    }

    /**
     * Re-tag all of the owner's modules' billing_model to match their CURRENT
     * pricing mode (the single source of truth). Call this whenever the mode may
     * have changed — the financing mode-switch AND every package activation
     * (`setUserPackage`) — so the billing engines never bill under a stale model:
     *   - transaction → infra recovered from rent (InfrastructureCostEngine)
     *   - subscription/free → billed on the monthly invoice (CommissionEngine)
     * Idempotent; only touches drifted rows. Guarded so legacy installs are safe.
     */
    public function syncModulesToOwnerMode(int $ownerUserId): void
    {
        if (! Schema::hasTable('property_modules')) {
            return;
        }

        $billingModel = $this->isTransactionMode($ownerUserId)
            ? PropertyModule::BILLING_TRANSACTION
            : PropertyModule::BILLING_SUBSCRIPTION;

        DB::table('property_modules')
            ->where('owner_id', $ownerUserId)
            ->where('billing_model', '!=', $billingModel)
            ->update(['billing_model' => $billingModel, 'updated_at' => now()]);
    }
}
