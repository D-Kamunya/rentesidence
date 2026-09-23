<?php

namespace App\Centresidence\Support;

use InvalidArgumentException;

/**
 * Immutable money value object.
 *
 * Centresidence moves real money through commission, token and settlement
 * math — chains of multiplications and percentage splits where naive float
 * arithmetic accumulates drift and produces reconciliation disputes. To avoid
 * that, every amount is held as an integer number of **minor units** (cents),
 * and all per-unit rate math is done with bcmath at high precision and rounded
 * back to cents exactly once, at the boundary.
 *
 * Rule of thumb across the engines:
 *   - Amounts of money   → Money (minor units, exact).
 *   - Per-unit rates/%   → decimal strings fed through the static helpers,
 *                          which collapse to a Money at the end.
 *
 * Example (handbook §7.3 token economics):
 *   $units = '500';                 // litres a tenant bought for KES 100
 *   $commissionPerLitre = '0.0200'; // KES per litre
 *   Money::fromUnitsTimesRate($units, $commissionPerLitre)->toDecimal(); // "10.00"
 */
final class Money
{
    private int $minorUnits;
    private string $currency;

    private function __construct(int $minorUnits, string $currency)
    {
        $this->minorUnits = $minorUnits;
        $this->currency = strtoupper($currency);
    }

    private static function scale(): int
    {
        return (int) config('centresidence.money.scale', 2);
    }

    private static function rateScale(): int
    {
        return (int) config('centresidence.money.rate_scale', 6);
    }

    private static function factor(): int
    {
        return 10 ** self::scale();
    }

    private static function defaultCurrency(): string
    {
        return (string) config('centresidence.money.currency', 'KES');
    }

    // ── Constructors ──────────────────────────────────────────────────────

    /** Zero amount. */
    public static function zero(?string $currency = null): self
    {
        return new self(0, $currency ?? self::defaultCurrency());
    }

    /** Build from an integer count of minor units (cents). */
    public static function fromMinor(int $minorUnits, ?string $currency = null): self
    {
        return new self($minorUnits, $currency ?? self::defaultCurrency());
    }

    /**
     * Build from a major-unit amount (e.g. "12.50", 12.5, 12). Decimal strings
     * are preferred; the value is rounded to the configured scale.
     */
    public static function fromDecimal($amount, ?string $currency = null): self
    {
        $minor = self::roundToMinor((string) $amount);

        return new self($minor, $currency ?? self::defaultCurrency());
    }

    /**
     * units × rate-per-unit → money. Used for token commission/revenue where
     * the rate is sub-cent precision. Computed with bcmath, rounded to cents.
     */
    public static function fromUnitsTimesRate($units, $ratePerUnit, ?string $currency = null): self
    {
        $product = bcmul((string) $units, (string) $ratePerUnit, self::rateScale() + 2);

        return new self(self::roundToMinor($product), $currency ?? self::defaultCurrency());
    }

    // ── Arithmetic (exact, integer minor units) ──────────────────────────

    public function plus(self $other): self
    {
        $this->assertSameCurrency($other);

        return new self($this->minorUnits + $other->minorUnits, $this->currency);
    }

    public function minus(self $other): self
    {
        $this->assertSameCurrency($other);

        return new self($this->minorUnits - $other->minorUnits, $this->currency);
    }

    /** Multiply by an integer quantity (e.g. rate × active_meter_count). Exact. */
    public function timesQuantity(int $quantity): self
    {
        return new self($this->minorUnits * $quantity, $this->currency);
    }

    /**
     * A percentage of this amount, rounded to cents. e.g. ->percentage('1')
     * for the 1% transaction fee, ->percentage('50') for the fallback cap.
     */
    public function percentage($percent): self
    {
        $full = bcdiv(bcmul((string) $this->minorUnits, (string) $percent, 6), '100', 6);

        return new self(self::roundDecimalToInt($full), $this->currency);
    }

    /**
     * Prorate by a day fraction (activeDays / periodDays), rounded to cents.
     * Supports the cost-component `is_prorated` rule (handbook §5).
     */
    public function prorate(int $activeDays, int $periodDays): self
    {
        if ($periodDays <= 0) {
            throw new InvalidArgumentException('periodDays must be positive.');
        }
        if ($activeDays >= $periodDays) {
            return $this;
        }
        $full = bcdiv(bcmul((string) $this->minorUnits, (string) $activeDays, 6), (string) $periodDays, 6);

        return new self(self::roundDecimalToInt($full), $this->currency);
    }

    /** Cap this amount at another (returns the smaller). */
    public function cappedAt(self $ceiling): self
    {
        $this->assertSameCurrency($ceiling);

        return $this->minorUnits <= $ceiling->minorUnits ? $this : $ceiling;
    }

    // ── Comparison ────────────────────────────────────────────────────────

    public function isZero(): bool
    {
        return $this->minorUnits === 0;
    }

    public function isPositive(): bool
    {
        return $this->minorUnits > 0;
    }

    public function greaterThan(self $other): bool
    {
        $this->assertSameCurrency($other);

        return $this->minorUnits > $other->minorUnits;
    }

    public function equals(self $other): bool
    {
        return $this->currency === $other->currency && $this->minorUnits === $other->minorUnits;
    }

    // ── Output ────────────────────────────────────────────────────────────

    public function toMinor(): int
    {
        return $this->minorUnits;
    }

    /** Major-unit decimal string, fixed to scale — safe for DB decimal columns. */
    public function toDecimal(): string
    {
        $negative = $this->minorUnits < 0;
        $abs = abs($this->minorUnits);
        $factor = self::factor();
        $whole = intdiv($abs, $factor);
        $frac = $abs % $factor;
        $decimal = sprintf('%d.%0' . self::scale() . 'd', $whole, $frac);

        return $negative ? '-' . $decimal : $decimal;
    }

    public function toFloat(): float
    {
        return $this->minorUnits / self::factor();
    }

    public function currency(): string
    {
        return $this->currency;
    }

    public function __toString(): string
    {
        return $this->currency . ' ' . $this->toDecimal();
    }

    // ── Internals ─────────────────────────────────────────────────────────

    private static function roundToMinor(string $majorAmount): int
    {
        // Scale to minor units at high precision, then round half-up to int.
        $scaled = bcmul($majorAmount, (string) self::factor(), 6);

        return self::roundDecimalToInt($scaled);
    }

    private static function roundDecimalToInt(string $decimal): int
    {
        // Half-up rounding of a bcmath decimal string to the nearest integer,
        // independent of locale/float representation.
        $negative = strncmp($decimal, '-', 1) === 0;
        $decimal = ltrim($decimal, '-');
        $rounded = bcadd($decimal, '0.5', 0); // bcadd with scale 0 truncates toward zero
        $int = (int) $rounded;

        return $negative ? -$int : $int;
    }

    private function assertSameCurrency(self $other): void
    {
        if ($this->currency !== $other->currency) {
            throw new InvalidArgumentException(
                "Currency mismatch: {$this->currency} vs {$other->currency}."
            );
        }
    }
}
