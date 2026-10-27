<?php

namespace App\Services;

use App\Models\Package;
use App\Models\ProductCategory;

/**
 * Config-aware academy content.
 *
 * Academy module content (admin-editable, DB-stored) may contain {{tokens}} that are
 * substituted at RENDER time from the LIVE pricing/commission settings — so when an
 * admin changes a commission rate, SMS price or plan setting, every academy module that
 * quotes that number updates itself, instead of going quietly stale and teaching
 * affiliates a figure we no longer honour.
 *
 *   author writes:  "You earn {{first_rate}} of our take on a new owner's first payment."
 *   affiliate sees:  "You earn 30% of our take on a new owner's first payment."
 *
 * Mirrors the marketing_templates {{token}} pattern. Unknown tokens are left verbatim
 * (so a typo is visible on review, never silently blanked). numericData() exposes the
 * same figures as raw numbers for the in-academy earnings calculator.
 */
class AcademyContentService
{
    /** Centresidence keeps 1% of rent collected through the platform (transaction fee). */
    public const PLATFORM_RENT_FEE_PERCENT = 1.0;

    /** Affiliate rent share: 15% of our 1% = a flat 0.15% of gross rent (see PropertyManagementCommissionStrategy). */
    public const AFFILIATE_RENT_PERCENT = 0.15;

    private static ?array $cache = null;

    /**
     * The live figures, as raw numbers — the single source both the tokens and the
     * calculator read, so the taught numbers and the computed ones can never drift.
     */
    public static function numericData(): array
    {
        if (self::$cache !== null) {
            return self::$cache;
        }

        $firstRate     = (float) (getOption('FIRST_TIME_COMMISSION_RATE') ?: 30);
        $recurringRate = (float) (getOption('RECURRING_COMMISSION_RATE') ?: 10);
        $recurMonths   = (int)   (getOption('RECURRING_COMMISSION_MONTHS') ?: 12);
        $globalSms     = (float) (getOption('sms_credit_price') ?: 2.00);

        $free  = Package::where('name', 'Free')->first();
        $trial = Package::where('name', 'Trial')->first();
        // A representative PAID band for the "cheaper when you upgrade" comparison.
        $paid  = Package::where('pricing_model', 'subscription')->orderBy('per_monthly_price')->first();

        $freeSms = $free && $free->sms_price_per_credit !== null ? (float) $free->sms_price_per_credit : $globalSms;
        $paidSms = $paid && $paid->sms_price_per_credit !== null ? (float) $paid->sms_price_per_credit : $globalSms;

        // Platform marketplace commission = base_commission + plan markup − plan discount
        // (CommissionService). The plan markup is the per-plan lever: highest on Free.
        $freeMarkup = $free ? (float) $free->commission_markup : 2.0;

        // Affiliate's marketplace cut = a % of OUR take, from the product category. Uniform at
        // seed (15%); take the max so the taught figure is the best-case the affiliate can quote.
        $marketplaceCut = (float) (ProductCategory::max('affiliate_commission') ?: 15);

        return self::$cache = [
            'first_rate'        => $firstRate,
            'recurring_rate'    => $recurringRate,
            'recurring_months'  => $recurMonths,
            'rent_commission'   => self::AFFILIATE_RENT_PERCENT,
            'platform_rent_fee' => self::PLATFORM_RENT_FEE_PERCENT,
            'marketplace_cut'   => $marketplaceCut,
            'sms_free_price'    => $freeSms,
            'sms_paid_price'    => $paidSms,
            'sms_global_price'  => $globalSms,
            'free_cap'          => $free  ? (int) $free->max_unit  : 30,
            'trial_cap'         => $trial ? (int) $trial->max_unit : 50,
            'trial_sms_grant'   => $trial ? (int) $trial->monthly_sms_credits : 50,
            'free_mkt_markup'   => $freeMarkup,
            // Usage-line prices + free-tier monthly allowances (what a FREE owner pays per use,
            // and gets free each month, before a paid plan makes them unlimited/included). Read
            // live from settings so the taught upgrade pitch always matches real pricing.
            'screening_price'      => (float) getOption('screening_price', 30),
            'agreement_price'      => (float) getOption('agreement_price', 50),
            'screening_free_quota' => (int) getOption('screening_free_quota', 3),
            'agreement_free_quota' => (int) getOption('agreement_free_quota', 3),
        ];
    }

    /**
     * The display map: token => formatted, ready-to-read string (percentages with %,
     * money via the app currency helper so it matches every other price on the platform).
     */
    public static function tokens(): array
    {
        $n = self::numericData();

        $pct = fn ($v) => rtrim(rtrim(number_format((float) $v, 2), '0'), '.') . '%';

        return [
            'first_rate'        => $pct($n['first_rate']),
            'recurring_rate'    => $pct($n['recurring_rate']),
            'recurring_months'  => (string) $n['recurring_months'],
            'rent_commission'   => $pct($n['rent_commission']),
            'platform_rent_fee' => $pct($n['platform_rent_fee']),
            'marketplace_cut'   => $pct($n['marketplace_cut']),
            'sms_free_price'    => trim(currencyPrice($n['sms_free_price'])),
            'sms_paid_price'    => trim(currencyPrice($n['sms_paid_price'])),
            'sms_global_price'  => trim(currencyPrice($n['sms_global_price'])),
            'free_cap'          => (string) $n['free_cap'],
            'trial_cap'         => (string) $n['trial_cap'],
            'trial_sms_grant'   => (string) $n['trial_sms_grant'],
            'free_mkt_markup'   => $pct($n['free_mkt_markup']),
            'screening_price'      => trim(currencyPrice($n['screening_price'])),
            'agreement_price'      => trim(currencyPrice($n['agreement_price'])),
            'screening_free_quota' => (string) $n['screening_free_quota'],
            'agreement_free_quota' => (string) $n['agreement_free_quota'],
            'currency'          => trim(getCurrencySymbol()) ?: 'KSh',
        ];
    }

    /** Substitute {{token}} occurrences in content. Unknown tokens are left untouched. */
    public static function render(?string $html): string
    {
        if ($html === null || $html === '') {
            return (string) $html;
        }

        $tokens = self::tokens();

        return preg_replace_callback('/\{\{\s*([a-z_]+)\s*\}\}/i', function ($m) use ($tokens) {
            $key = strtolower($m[1]);
            return array_key_exists($key, $tokens) ? $tokens[$key] : $m[0];
        }, $html);
    }
}
