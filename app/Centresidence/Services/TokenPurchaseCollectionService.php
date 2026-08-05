<?php

namespace App\Centresidence\Services;

use App\Centresidence\Models\PropertyModule;
use App\Centresidence\Models\TokenPurchase;
use App\Centresidence\Models\UtilityWallet;
use App\Centresidence\Support\Money;
use App\Models\MpesaAccount;
use App\Models\Tenant;
use App\Models\User;
use App\Services\Payment\MpesaStkService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * The tenant-facing front door to the Token Engine (handbook §7 / completion-map
 * C1). A tenant buys prepaid utility units for a metered module on their unit;
 * the money is collected via M-Pesa STK into the Centresidence rent account
 * (same rail as infra-bill / down-payment collection), and on the callback the
 * TokenEngine credits the tenant's wallet and the owner's net revenue.
 *
 * Authorization is enforced here, not in the controller: a tenant may only buy
 * tokens for a module attached to a property/unit they actually occupy. The
 * check is re-run at settle time (the callback carries only ids), so a spoofed
 * or stale module id can never credit a wallet the tenant has no claim to.
 */
class TokenPurchaseCollectionService
{
    public function __construct(private TokenEngine $engine)
    {
    }

    /**
     * Metered, active modules the tenant may buy tokens for, each decorated with
     * the tenant's current wallet balance and the module's price per unit.
     */
    public function modulesFor(int $tenantUserId): Collection
    {
        [$propertyIds, $unitIds] = $this->tenantScope($tenantUserId);

        if (empty($propertyIds)) {
            return collect();
        }

        $modules = PropertyModule::query()
            ->active()
            ->with(['module', 'tokenConfig'])
            ->whereHas('module', fn ($q) => $q->where('is_metered', true))
            ->whereHas('tokenConfig', fn ($q) => $q->where('is_active', true))
            ->whereIn('property_id', array_values($propertyIds))
            ->where(function ($q) use ($unitIds) {
                $q->whereNull('property_unit_id');
                if (! empty($unitIds)) {
                    $q->orWhereIn('property_unit_id', array_values($unitIds));
                }
            })
            ->get();

        $wallets = UtilityWallet::query()
            ->where('tenant_user_id', $tenantUserId)
            ->whereIn('property_module_id', $modules->pluck('id'))
            ->get()
            ->keyBy('property_module_id');

        return $modules->map(function (PropertyModule $module) use ($wallets) {
            $module->setAttribute('wallet_balance', (string) (optional($wallets->get($module->id))->balance_units ?? '0'));
            $module->setAttribute('price_per_unit', $module->tokenConfig->pricePerUnit());

            return $module;
        })->values();
    }

    /** True when the tenant has at least one module they can buy tokens for (cheap nav guard). */
    public function hasUtilities(int $tenantUserId): bool
    {
        [$propertyIds, $unitIds] = $this->tenantScope($tenantUserId);

        if (empty($propertyIds)) {
            return false;
        }

        return PropertyModule::query()
            ->active()
            ->whereHas('module', fn ($q) => $q->where('is_metered', true))
            ->whereHas('tokenConfig', fn ($q) => $q->where('is_active', true))
            ->whereIn('property_id', array_values($propertyIds))
            ->where(function ($q) use ($unitIds) {
                $q->whereNull('property_unit_id');
                if (! empty($unitIds)) {
                    $q->orWhereIn('property_unit_id', array_values($unitIds));
                }
            })
            ->exists();
    }

    /**
     * The authorized module for this tenant, or null. The single gate every
     * purchase/settlement passes through — never trust a posted module id.
     */
    public function authorizedModule(int $tenantUserId, int $propertyModuleId): ?PropertyModule
    {
        return $this->modulesFor($tenantUserId)->firstWhere('id', $propertyModuleId);
    }

    /**
     * Initiate a token purchase. Driver-gated exactly like the infra-bill/
     * down-payment collection: 'log' (dev/simulation) settles immediately with
     * no real STK; 'mpesa' prompts the tenant's phone and defers crediting to
     * the async callback.
     *
     * @return array{success:bool, message:string, reference:?string, settled?:bool, purchase?:\App\Centresidence\Models\TokenPurchase}
     */
    public function initiate(PropertyModule $module, int $tenantUserId, float $amount): array
    {
        if ($amount <= 0) {
            return ['success' => false, 'message' => __('Enter an amount greater than zero.'), 'reference' => null];
        }

        if (config('centresidence.collections.driver', 'log') !== 'mpesa') {
            $purchase = $this->engine->purchase(
                $module,
                $tenantUserId,
                Money::fromDecimal($amount),
                ['payment_reference' => 'LOG-' . Str::uuid()->toString()]
            );

            return [
                'success'   => true,
                'message'   => __('Tokens credited.'),
                'reference' => 'LOG',
                'settled'   => true,
                'purchase'  => $purchase,
            ];
        }

        $phone   = optional(User::find($tenantUserId))->contact_number;
        $account = ($accountId = getOption('centresidence_rent_mpesa_account_id'))
            ? MpesaAccount::find($accountId)
            : null;

        if (! $phone || ! $account) {
            Log::warning('Centresidence token purchase STK skipped: missing tenant phone or collection account', [
                'tenant_user_id' => $tenantUserId, 'has_phone' => (bool) $phone, 'has_account' => (bool) $account,
            ]);

            return ['success' => false, 'message' => __('We could not start the payment — please contact support.'), 'reference' => null];
        }

        return app(MpesaStkService::class)->push(
            $phone,
            $amount,
            $account,
            'Utility tokens',
            route('centresidence.token.callback', ['propertyModule' => $module->id, 'tenant' => $tenantUserId])
        );
    }

    /**
     * Settle a confirmed payment into tokens (called by the STK callback). Re-runs
     * authorization from the ids alone, then delegates to the TokenEngine which is
     * idempotent on the payment reference (a re-fired webhook credits once).
     */
    public function settle(int $propertyModuleId, int $tenantUserId, float $amount, ?string $reference): ?TokenPurchase
    {
        if ($amount <= 0) {
            return null;
        }

        $module = $this->authorizedModule($tenantUserId, $propertyModuleId);
        if (! $module) {
            Log::warning('Centresidence token settle rejected: module not authorized for tenant', [
                'tenant_user_id' => $tenantUserId, 'property_module_id' => $propertyModuleId,
            ]);

            return null;
        }

        return $this->engine->purchase(
            $module,
            $tenantUserId,
            Money::fromDecimal($amount),
            ['payment_reference' => $reference]
        );
    }

    /** Distinct property + unit ids the tenant currently occupies (all their leases). */
    private function tenantScope(int $tenantUserId): array
    {
        $leases = Tenant::query()
            ->where('user_id', $tenantUserId)
            ->get(['property_id', 'unit_id']);

        return [
            $leases->pluck('property_id')->filter()->unique()->all(),
            $leases->pluck('unit_id')->filter()->unique()->all(),
        ];
    }
}
