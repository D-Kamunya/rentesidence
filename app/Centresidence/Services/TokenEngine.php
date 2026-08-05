<?php

namespace App\Centresidence\Services;

use App\Centresidence\Events\TokenPurchased;
use App\Centresidence\Models\Device;
use App\Centresidence\Models\DeviceCommand;
use App\Centresidence\Models\PropertyModule;
use App\Centresidence\Models\TokenPurchase;
use App\Centresidence\Models\UtilityConsumption;
use App\Centresidence\Models\UtilityWallet;
use App\Centresidence\Support\Money;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Token Engine (handbook §7) — utility purchases, embedded commission, wallet
 * credit, device command dispatch, and usage deduction.
 *
 * Embedded commission: the tenant pays a gross amount and ALWAYS receives the
 * full units. Centresidence's commission is derived from the token config and
 * carved out of the owner's revenue — it is NOT a separate tenant-visible
 * charge, and token purchases are exempt from the 1% transaction fee. If
 * fallback is active for the owner's overdue metered commission, a share of the
 * owner's revenue is intercepted (never the tenant's units).
 */
class TokenEngine
{
    public function __construct(private CommissionFallbackService $fallback)
    {
    }

    public function purchase(PropertyModule $propertyModule, ?int $tenantUserId, Money $amount, array $options = []): TokenPurchase
    {
        if (! $amount->isPositive()) {
            throw new RuntimeException('Token purchase amount must be positive.');
        }

        // Idempotency: if this provider reference was already processed, return
        // the existing purchase rather than crediting the wallet twice (guards
        // against double-fired payment webhooks).
        if (! empty($options['payment_reference'])) {
            $existing = TokenPurchase::where('payment_reference', $options['payment_reference'])->first();
            if ($existing) {
                return $existing;
            }
        }

        $config = $propertyModule->tokenConfig;
        if (! $config) {
            throw new RuntimeException("Property module {$propertyModule->id} has no token config.");
        }

        $unitsPerKes = (string) $config->units_per_kes;
        $commissionPerUnit = (string) $config->centresidence_commission_per_token_unit;

        // Units the tenant receives, and the embedded commission split.
        $units = bcmul($amount->toDecimal(), $unitsPerKes, 4);
        $commission = Money::fromUnitsTimesRate($units, $commissionPerUnit);
        $ownerGross = $amount->minus($commission);

        return DB::transaction(function () use (
            $propertyModule, $tenantUserId, $amount, $config,
            $units, $unitsPerKes, $commissionPerUnit, $commission, $ownerGross, $options
        ) {
            $wallet = UtilityWallet::firstOrCreate(
                ['property_module_id' => $propertyModule->id, 'tenant_user_id' => $tenantUserId],
                ['unit_label' => $config->token_unit_label]
            );

            // Lock the wallet row for the duration of the transaction so
            // concurrent purchases serialise (no lost balance updates). On
            // sqlite this is a harmless no-op; on MySQL it pins the row.
            $wallet = UtilityWallet::whereKey($wallet->id)->lockForUpdate()->first();

            // Fallback intercepts from owner revenue only — tenant gets full units.
            $fallbackDeducted = $this->fallback->applyToOwnerRevenue(
                (int) $propertyModule->owner_id,
                (int) $propertyModule->property_id,
                $ownerGross
            );
            $ownerNet = $ownerGross->minus($fallbackDeducted);

            $wallet->creditUnits($units);

            // Dispatch a downlink command to an active device, if any.
            $deviceCommandId = null;
            $device = $propertyModule->activeDevices()->first();
            if ($device) {
                $deviceCommandId = DeviceCommand::create([
                    'device_id' => $device->id,
                    'command' => 'credit_tokens',
                    'payload' => ['units' => $units, 'unit_label' => $config->token_unit_label],
                    'status' => DeviceCommand::STATUS_QUEUED,
                    'issued_at' => Carbon::now(),
                ])->id;
            }

            $purchase = TokenPurchase::create([
                'utility_wallet_id' => $wallet->id,
                'property_module_id' => $propertyModule->id,
                'tenant_user_id' => $tenantUserId,
                'amount' => $amount->toDecimal(),
                'units' => $units,
                'unit_label' => $config->token_unit_label,
                'units_per_kes_snapshot' => $unitsPerKes,
                'commission_per_unit_snapshot' => $commissionPerUnit,
                'centresidence_commission' => $commission->toDecimal(),
                'owner_revenue_gross' => $ownerGross->toDecimal(),
                'fallback_deducted' => $fallbackDeducted->toDecimal(),
                'owner_revenue_net' => $ownerNet->toDecimal(),
                'status' => TokenPurchase::STATUS_COMPLETED,
                'payment_reference' => $options['payment_reference'] ?? null,
                'device_command_id' => $deviceCommandId,
                'purchased_at' => Carbon::now(),
            ]);

            TokenPurchased::dispatch($purchase);

            return $purchase;
        });
    }

    /**
     * Record consumption drawn from a wallet (typically from device telemetry).
     */
    public function recordConsumption(UtilityWallet $wallet, string $units, ?Device $device = null, string $source = 'telemetry'): UtilityConsumption
    {
        return DB::transaction(function () use ($wallet, $units, $device, $source) {
            $wallet->debitUnits($units);

            return UtilityConsumption::create([
                'utility_wallet_id' => $wallet->id,
                'device_id' => $device?->id,
                'units_consumed' => $units,
                'balance_after' => $wallet->balance_units,
                'source' => $source,
                'recorded_at' => Carbon::now(),
            ]);
        });
    }
}
