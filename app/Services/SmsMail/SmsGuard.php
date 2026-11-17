<?php

namespace App\Services\SmsMail;

use App\Models\SmsSuppression;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * SMS deliverability safety net — the sibling of EmailGuard. Decides whether a number should be
 * SUPPRESSED (skipped) to protect our sender reputation / delivery rate, and is consulted on every
 * send at each gateway's sendSms entry (so no path bypasses it).
 *
 * What it blocks:
 *   1. Anything on the explicit sms_suppressions list (admin-added, or auto-added below).
 *   2. Obvious test / seed ranges that can never be a real subscriber (the 0700000xxx demo block).
 *
 * Design: FAIL-OPEN. If anything goes wrong it allows the send — a guard bug must never silently
 * stop legitimate SMS. Heuristic matches are recorded with source='auto' so an admin can see and
 * remove a false positive; nothing here is irreversible.
 */
class SmsGuard
{
    /** Regex patterns (matched against the NORMALISED 254… MSISDN) for ranges that are never real. */
    private const TEST_PATTERNS = [
        '/^254700000\d{3}$/', // demo/seed block 0700000xxx (254700000000–254700000999)
    ];

    /** Drop suppressed numbers from a recipient list; logs what was dropped. Returns the kept numbers. */
    public static function filter(array $numbers): array
    {
        $dropped = [];
        $kept = array_values(array_filter($numbers, function ($number) use (&$dropped) {
            if (self::shouldSuppress($number)) {
                $dropped[] = $number;
                return false;
            }
            return true;
        }));

        if ($dropped) {
            Log::channel('sms-mail')->info('SMS suppressed (never sent — test/invalid numbers): ' . implode(', ', $dropped));
        }

        return $kept;
    }

    /** @return string|null A reason if the number should be suppressed, else null (send). */
    public static function shouldSuppress($number): ?string
    {
        try {
            $msisdn = self::normalize((string) $number);
            if ($msisdn === '') {
                return null;
            }

            // 1. Explicit suppression list (admin or previously auto-added).
            if (Schema::hasTable('sms_suppressions')) {
                $row = SmsSuppression::where('number', $msisdn)->first();
                if ($row) {
                    return $row->reason ?: 'suppressed';
                }
            }

            // 2. Heuristic test/seed ranges — auto-record so an admin can see / undo it.
            foreach (self::TEST_PATTERNS as $pattern) {
                if (@preg_match($pattern, $msisdn) === 1) {
                    self::suppress($msisdn, 'test/seed number range', 'auto');
                    return 'test/seed number range';
                }
            }

            return null;
        } catch (\Throwable $e) {
            return null; // FAIL-OPEN — never block real SMS because of a guard error
        }
    }

    /** Add a number to the suppression list (idempotent). */
    public static function suppress(string $number, string $reason, string $source = 'auto'): void
    {
        try {
            if (! Schema::hasTable('sms_suppressions')) {
                return;
            }
            $msisdn = self::normalize($number);
            if ($msisdn === '') {
                return;
            }
            SmsSuppression::firstOrCreate(
                ['number' => $msisdn],
                ['reason' => $reason, 'source' => $source]
            );
        } catch (\Throwable $e) {
            // best-effort; never throw from the guard
        }
    }

    /** Canonicalise a Kenyan number to 2547XXXXXXXX / 2541XXXXXXXX form for comparison. */
    public static function normalize(string $number): string
    {
        $digits = preg_replace('/\D+/', '', $number); // strip +, spaces, etc.
        if ($digits === '') {
            return '';
        }

        if (str_starts_with($digits, '254')) {
            return $digits;
        }
        if (str_starts_with($digits, '0') && strlen($digits) === 10) {
            return '254' . substr($digits, 1); // 0700000101 → 254700000101
        }
        if (strlen($digits) === 9 && (str_starts_with($digits, '7') || str_starts_with($digits, '1'))) {
            return '254' . $digits; // 700000101 → 254700000101
        }

        return $digits; // leave anything else as-is (foreign / already-odd format)
    }
}
