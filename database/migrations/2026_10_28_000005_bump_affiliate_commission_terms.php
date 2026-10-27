<?php

use Illuminate\Database\Migrations\Migration;

/**
 * Affiliate earnings uplift (sitting decision, 2026-10-28): the first-time commission rate goes
 * 30% → 40% and the recurring-commission window 12 → 24 months. Both are live settings every
 * earnings surface reads (academy, KB, apply page, calculator, the commission engine), so flipping
 * them here propagates everywhere automatically.
 *
 * Only a value still at its OLD default is changed, so a deliberate admin override is preserved.
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->bumpIfDefault('FIRST_TIME_COMMISSION_RATE', '30', '40');
        $this->bumpIfDefault('RECURRING_COMMISSION_MONTHS', '12', '24');
    }

    public function down(): void
    {
        $this->bumpIfDefault('FIRST_TIME_COMMISSION_RATE', '40', '30');
        $this->bumpIfDefault('RECURRING_COMMISSION_MONTHS', '24', '12');
    }

    private function bumpIfDefault(string $key, string $from, string $to): void
    {
        $current = getOption($key);
        // Set when it's still the old default or missing; leave a custom admin value untouched.
        if ($current === null || $current === '' || (string) $current === $from) {
            setOption($key, $to);
        }
    }
};
