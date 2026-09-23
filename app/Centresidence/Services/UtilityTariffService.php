<?php

namespace App\Centresidence\Services;

use App\Centresidence\Models\ModuleTokenConfig;

/**
 * Owner-set utility tariff logic. Two guardrail kinds (decision 2026-09-10, token-tariff-ownership):
 *   - SYSTEM-INTEGRITY FLOOR (hard, ours to enforce — it's our economics, not law): the tariff must
 *     leave owner_revenue >= 0, i.e. the price a tenant pays per unit must cover our per-token
 *     commission (gas). For water, commission is 0, so the floor is simply a positive tariff.
 *   - REGULATORY CEILING (ADVISORY ONLY — never blocks): an informational nudge against a researched
 *     county-water / EPRA-LPG reference, always carrying the indemnity line. The owner owns the
 *     system and is SOLELY liable for compliance; we never hard-cap and never chase county changes.
 */
class UtilityTariffService
{
    /** Classify a module by its machine key → 'water' | 'gas' | 'other'. */
    public function utilityClass(?string $moduleKey): string
    {
        $k = strtolower((string) $moduleKey);
        if (str_contains($k, 'water')) return 'water';
        if (str_contains($k, 'gas') || str_contains($k, 'lpg')) return 'gas';
        return 'other';
    }

    /**
     * The canonical SINGULAR unit noun for tariff phrasing ("KES per <unit>"): water → litre,
     * gas → kg. For anything else, fall back to a configured label (singularised) or "unit".
     * Keeps the owner-facing tariff explicit instead of the abstract "units".
     */
    public function unitNoun(string $utilityClass, ?string $configLabel = null): string
    {
        if ($utilityClass === 'water') return 'litre';
        if ($utilityClass === 'gas')   return 'kg';

        $label = trim((string) $configLabel);
        if ($label === '') return 'unit';
        $label = strtolower($label);
        return str_ends_with($label, 's') ? rtrim($label, 's') : $label; // Litres → litre
    }

    /** Convert an owner-entered price/unit (KES) into the stored units_per_kes knob. */
    public function unitsFromPrice(string $pricePerUnit): string
    {
        if (bccomp($pricePerUnit, '0', 8) <= 0) {
            return '0';
        }
        return bcdiv('1', $pricePerUnit, 8);
    }

    /**
     * HARD floor: the tariff must be positive AND leave the owner's revenue per unit >= 0 (i.e. the
     * price covers our commission). Utility-agnostic — reuses the canonical token formula.
     */
    public function meetsFloor(string $unitsPerKes, string $commissionPerUnit = '0'): bool
    {
        if (bccomp($unitsPerKes, '0', 6) <= 0) {
            return false;
        }
        $ownerRevenue = ModuleTokenConfig::computeOwnerRevenuePerUnit($unitsPerKes, $commissionPerUnit);

        return bccomp($ownerRevenue, '0', 6) >= 0;
    }

    /** The price a tenant pays per token unit (KES/unit) for a given tariff. */
    public function pricePerUnit(string $unitsPerKes): float
    {
        if (bccomp($unitsPerKes, '0', 6) <= 0) {
            return 0.0;
        }
        return (float) bcdiv('1', $unitsPerKes, 8);
    }

    /**
     * ADVISORY (informational, never blocks). Returns ['level'=>'info'|'warning', 'reference'=>?float,
     * 'authority'=>string, 'message'=>string]. 'warning' when the price exceeds a known reference;
     * 'info' (gentle compliance reminder) otherwise or when no reference exists.
     */
    public function advisory(float $pricePerUnit, string $utilityClass, ?string $county = null): array
    {
        $cfg       = config('centresidence.utility_tariffs');
        $reference = $this->reference($utilityClass, $county);
        $authority = $cfg['authorities'][$utilityClass] ?? $cfg['authorities']['other'];
        $tolerance = (float) ($cfg['advisory_tolerance'] ?? 1.0);

        $rate = number_format($pricePerUnit, 2);

        if ($reference !== null && $pricePerUnit > $reference * $tolerance) {
            return [
                'level'     => 'warning',
                'reference' => $reference,
                'authority' => $authority,
                'message'   => __('Your rate of :rate per unit exceeds the standard reference of :ref for this utility. Verify the enshrined limit with :authority — you set your own tariff and are solely responsible for regulatory compliance.', [
                    'rate' => $rate, 'ref' => number_format($reference, 2), 'authority' => $authority,
                ]),
            ];
        }

        return [
            'level'     => 'info',
            'reference' => $reference,
            'authority' => $authority,
            'message'   => __('Set your rate in line with your regulated tariff. Verify the applicable limit with :authority — you set your own tariff and are solely responsible for compliance.', [
                'authority' => $authority,
            ]),
        ];
    }

    /** The advisory reference (KES/unit) for a utility + county, or null when we don't have one yet. */
    public function reference(string $utilityClass, ?string $county = null): ?float
    {
        $refs = config('centresidence.utility_tariffs.references', []);

        if ($utilityClass === 'water') {
            $byCounty = $refs['water']['counties'][strtolower((string) $county)] ?? null;
            $value    = $byCounty ?? ($refs['water']['default'] ?? null);
        } elseif ($utilityClass === 'gas') {
            $value = $refs['gas']['default'] ?? null;
        } else {
            $value = null;
        }

        return $value === null ? null : (float) $value;
    }
}
