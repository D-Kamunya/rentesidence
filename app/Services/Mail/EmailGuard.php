<?php

namespace App\Services\Mail;

use App\Models\EmailSuppression;
use Illuminate\Support\Facades\Schema;

/**
 * Deliverability safety net. Decides whether an outbound email should be SUPPRESSED (skipped)
 * to protect sender reputation — consulted on every send via the MessageSending hook in
 * AppServiceProvider. A suppressed send also raises a System Health incident (human-in-the-loop).
 *
 * What it blocks:
 *   1. Anything on the explicit suppression list (admin-added hard-bouncers, or auto-added below).
 *   2. RFC-reserved / test domains that can NEVER be a real inbox (example.com, *.test, *.invalid…).
 *   3. A small, high-confidence set of obvious test local-parts (test@, owner@, noreply@…).
 *
 * Design: FAIL-OPEN. If anything goes wrong it returns null (allow) — a guard bug must never
 * silently stop legitimate mail. Heuristic matches are recorded with source='auto' so an admin
 * can see and remove a false positive from the suppression list; nothing here is irreversible.
 */
class EmailGuard
{
    /** Whole domains that are reserved for documentation/testing — never a real mailbox. */
    private const RESERVED_DOMAINS = ['example.com', 'example.org', 'example.net', 'test.com', 'localhost'];

    /** Reserved TLDs (RFC 2606 / 6761) — e.g. foo@bar.test, foo@bar.invalid. */
    private const RESERVED_TLDS = ['test', 'example', 'invalid', 'localhost', 'local'];

    /** Obvious placeholder local-parts (exact match) that almost always mean test data. */
    private const TEST_LOCALS = [
        'test', 'test1', 'test123', 'tests', 'testing', 'testuser',
        'owner', 'admin', 'example', 'sample', 'demo', 'placeholder',
        'asdf', 'asdfgh', 'qwerty', 'fake', 'fakeemail', 'dummy',
        'noreply', 'no-reply', 'donotreply', 'none', 'null', 'nobody',
    ];

    /**
     * @return string|null  A reason string if the address should be suppressed, else null (send).
     */
    public static function shouldSuppress(?string $email): ?string
    {
        try {
            $email = strtolower(trim((string) $email));
            if ($email === '' || substr_count($email, '@') !== 1) {
                return null; // let normal validation handle malformed addresses
            }

            // 1. Explicit suppression list (admin or previously auto-added).
            if (Schema::hasTable('email_suppressions')) {
                $row = EmailSuppression::where('email', $email)->first();
                if ($row) {
                    return $row->reason ?: 'suppressed';
                }
            }

            [$local, $domain] = explode('@', $email, 2);
            $tld = (string) substr((string) strrchr($domain, '.'), 1);

            // 2. Reserved/test domain — can never be a real inbox.
            if (in_array($domain, self::RESERVED_DOMAINS, true) || in_array($tld, self::RESERVED_TLDS, true)) {
                self::suppress($email, 'reserved/test domain', 'auto');
                return 'reserved/test domain';
            }

            // 3. Obvious placeholder local-part on any domain.
            if (in_array($local, self::TEST_LOCALS, true)) {
                self::suppress($email, 'test-pattern address', 'auto');
                return 'test-pattern address';
            }

            return null;
        } catch (\Throwable $e) {
            return null; // FAIL-OPEN — never block real mail because of a guard error
        }
    }

    /** Add an address to the suppression list (idempotent). */
    public static function suppress(string $email, string $reason, string $source = 'auto'): void
    {
        try {
            if (! Schema::hasTable('email_suppressions')) {
                return;
            }
            EmailSuppression::firstOrCreate(
                ['email' => strtolower(trim($email))],
                ['reason' => $reason, 'source' => $source]
            );
        } catch (\Throwable $e) {
            // best-effort; never throw from the guard
        }
    }
}
