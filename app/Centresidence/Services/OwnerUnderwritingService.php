<?php

namespace App\Centresidence\Services;

use App\Models\OwnerWallet;
use App\Models\PropertyUnit;
use App\Models\Tenant;
use App\Models\WalletTransaction;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

/**
 * Owner underwriting snapshot for a financier reviewing an application — this is the rent moat
 * turned into an underwriting product. Built from SETTLED rent (the platform rent credits an
 * owner actually received), i.e. exactly the cashflow a rent-secured facility is repaid from,
 * and serviced at source before it reaches the owner.
 *
 * HONEST BY DESIGN (we onboard financial institutions, so a misleading figure is a liability):
 * we surface the average PLUS the spread (min/max), a coverage + stability signal, occupancy,
 * and the data depth — never a lone rosy average that a lumpy history could hide behind.
 */
class OwnerUnderwritingService
{
    /**
     * @return array{months:int, monthly:array<int,array{label:string,collected:float}>,
     *   average:float, min:float, max:float, total:float, months_with_data:int,
     *   consistency_pct:int, stability_score:int,
     *   occupancy:array{occupied:int,total:int,rate:int}}
     */
    public function snapshot(int $ownerUserId, int $months = 6): array
    {
        $months      = max(1, min(36, $months));
        $seriesStart = Carbon::now()->startOfMonth()->subMonths($months - 1);

        // Monthly GROSS rent settled through the platform (proven, dated, per owner). We use
        // gross_amount (before our 1% + any deductions) — the owner's true rent-earning power.
        $byMonth  = [];
        $walletId = OwnerWallet::where('user_id', $ownerUserId)->value('id');
        if ($walletId && Schema::hasTable('wallet_transactions')) {
            WalletTransaction::where('owner_wallet_id', $walletId)
                ->where('transaction_source', 'rent')
                ->where('created_at', '>=', $seriesStart)
                ->get(['gross_amount', 'created_at'])
                ->each(function ($t) use (&$byMonth) {
                    $key = $t->created_at->format('Y-m');
                    $byMonth[$key] = ($byMonth[$key] ?? 0) + (float) $t->gross_amount;
                });
        }

        // Dense N-month series — a missed month shows as 0, never hidden.
        $monthly = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $d = Carbon::now()->startOfMonth()->subMonths($i);
            $monthly[] = [
                'label'     => $d->format('M Y'),
                'collected' => round((float) ($byMonth[$d->format('Y-m')] ?? 0), 2),
            ];
        }

        $values   = array_column($monthly, 'collected');
        $total    = round(array_sum($values), 2);
        $average  = round($total / $months, 2);
        $withData = array_values(array_filter($values, fn ($v) => $v > 0));

        return [
            'months'           => $months,
            'monthly'          => $monthly,
            'average'          => $average,
            'min'              => $withData ? round(min($withData), 2) : 0.0,
            'max'              => $values ? round(max($values), 2) : 0.0,
            'total'            => $total,
            'months_with_data' => count($withData),
            'consistency_pct'  => (int) round(count($withData) / $months * 100),
            'stability_score'  => $this->stabilityScore($values, $average),
            'occupancy'        => $this->occupancy($ownerUserId),
        ];
    }

    /**
     * 0–100 steadiness of month-to-month rent (100 = flat/reliable, lower = lumpy), from the
     * coefficient of variation. It sits next to the average so a spiky history can't hide.
     */
    private function stabilityScore(array $values, float $average): int
    {
        if ($average <= 0 || count($values) < 2) {
            return 0;
        }
        $variance = 0.0;
        foreach ($values as $v) {
            $variance += ($v - $average) ** 2;
        }
        $std = sqrt($variance / count($values));
        return (int) max(0, min(100, round((1 - $std / $average) * 100)));
    }

    /** Current occupancy for the owner — occupied (active-tenant) units over total units. */
    private function occupancy(int $ownerUserId): array
    {
        $total    = PropertyUnit::whereHas('property', fn ($q) => $q->where('owner_id', $ownerUserId))->count();
        $occupied = (int) Tenant::where('owner_user_id', $ownerUserId)
            ->whereNotNull('unit_id')
            ->distinct()
            ->count('unit_id');
        $occupied = $total > 0 ? min($occupied, $total) : $occupied;

        return [
            'occupied' => $occupied,
            'total'    => $total,
            'rate'     => $total > 0 ? (int) round($occupied / $total * 100) : 0,
        ];
    }
}
