<?php

namespace Tests\Unit\Centresidence;

use App\Centresidence\Support\Money;
use Tests\TestCase;

/**
 * Money is the foundation every Centresidence engine computes on, so its
 * exactness is asserted directly — including the handbook's own worked figures.
 */
class MoneyTest extends TestCase
{
    public function test_from_decimal_and_back_is_exact(): void
    {
        $this->assertSame('12.50', Money::fromDecimal('12.50')->toDecimal());
        $this->assertSame(1250, Money::fromDecimal('12.50')->toMinor());
        $this->assertSame('0.00', Money::zero()->toDecimal());
    }

    public function test_addition_and_subtraction(): void
    {
        $a = Money::fromDecimal('10000.00'); // base subscription
        $b = Money::fromDecimal('2375.00');  // module costs
        $this->assertSame('12375.00', $a->plus($b)->toDecimal());
        $this->assertSame('7625.00', $a->minus(Money::fromDecimal('2375.00'))->toDecimal());
    }

    /** Handbook §19 Test Case 1: 20 water meters × KES 100 + 5 locks × KES 75. */
    public function test_handbook_subscription_invoice_totals(): void
    {
        $subscription = Money::fromDecimal('10000.00');
        $water  = Money::fromDecimal('100.00')->timesQuantity(20); // 2,000
        $locks  = Money::fromDecimal('75.00')->timesQuantity(5);   // 375

        $this->assertSame('2000.00', $water->toDecimal());
        $this->assertSame('375.00', $locks->toDecimal());
        $this->assertSame('12375.00', $subscription->plus($water)->plus($locks)->toDecimal());
    }

    /** Handbook §7.3 / §19 Test Case 4: 500 litres × KES 0.02 commission = KES 10.00. */
    public function test_token_commission_from_units_times_rate(): void
    {
        $commission = Money::fromUnitsTimesRate('500', '0.0200');
        $this->assertSame('10.00', $commission->toDecimal());

        // Owner nets KES 90.00 of a KES 100 purchase.
        $owner = Money::fromDecimal('100.00')->minus($commission);
        $this->assertSame('90.00', $owner->toDecimal());
    }

    public function test_percentage_rounds_half_up(): void
    {
        // 1% transaction fee on KES 100,000 rent = KES 1,000.
        $this->assertSame('1000.00', Money::fromDecimal('100000.00')->percentage('1')->toDecimal());
        // 50% fallback cap on KES 100,000 = KES 50,000.
        $this->assertSame('50000.00', Money::fromDecimal('100000.00')->percentage('50')->toDecimal());
        // Rounding: 1% of 12.345 → 0.12345 → KES 0.12.
        $this->assertSame('0.12', Money::fromDecimal('12.345')->percentage('1')->toDecimal());
    }

    public function test_proration_half_month(): void
    {
        // A device active 15 of 30 days pays half (handbook is_prorated rule).
        $this->assertSame('50.00', Money::fromDecimal('100.00')->prorate(15, 30)->toDecimal());
        // Full period pays full.
        $this->assertSame('100.00', Money::fromDecimal('100.00')->prorate(30, 30)->toDecimal());
    }

    public function test_capped_at(): void
    {
        $fallback = Money::fromDecimal('1500.00');
        $cap = Money::fromDecimal('50000.00');
        $this->assertSame('1500.00', $fallback->cappedAt($cap)->toDecimal());

        $big = Money::fromDecimal('60000.00');
        $this->assertSame('50000.00', $big->cappedAt($cap)->toDecimal());
    }

    public function test_currency_mismatch_throws(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Money::fromDecimal('1', 'KES')->plus(Money::fromDecimal('1', 'USD'));
    }
}
