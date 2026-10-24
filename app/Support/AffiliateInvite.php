<?php

namespace App\Support;

use Carbon\Carbon;
use Illuminate\Support\Str;

/**
 * Campaign-invite state for the affiliate apply page.
 *
 * A single rotatable token + a generous expiry (both admin-editable options) gate the "invited"
 * experience, where a pre-vetted applicant is auto-provisioned an account on submit instead of
 * queueing for admin review. The expiry is the safety net for the admin who forgets to close the
 * campaign: once it passes, invited-provisioning auto-closes and submissions fall back to the normal
 * review path — never a dead-end. Deliberately NOT per-person tokens / a tokens table: this is a
 * campaign mechanism, and config is proportionate.
 */
class AffiliateInvite
{
    public const OPT_TOKEN       = 'affiliate_invite_token';
    public const OPT_EXPIRES     = 'affiliate_invite_expires_at';
    public const DEFAULT_WINDOW_DAYS = 45;

    public static function token(): ?string
    {
        $t = (string) getOption(self::OPT_TOKEN);
        return $t !== '' ? $t : null;
    }

    public static function expiresAt(): ?Carbon
    {
        $v = (string) getOption(self::OPT_EXPIRES);
        if ($v === '') {
            return null;
        }
        try {
            return Carbon::parse($v);
        } catch (\Throwable $e) {
            return null;
        }
    }

    /** A token is configured and still within its window. */
    public static function isActive(): bool
    {
        $exp = self::expiresAt();
        return self::token() !== null && $exp !== null && $exp->isFuture();
    }

    /** The shareable invited link (null when no token is configured). */
    public static function link(): ?string
    {
        $t = self::token();
        return $t ? route('affiliate.apply') . '?invite=' . $t : null;
    }

    /**
     * Classify a submitted/visited token:
     *   'valid'   — matches the current token AND the window is open → auto-provision.
     *   'expired' — matches the current token but the window has closed → graceful fallback + notice.
     *   'none'    — no token supplied, or it doesn't match (organic applicant / rotated-out link).
     */
    public static function classify(?string $provided): string
    {
        $token = self::token();
        if (! $provided || ! $token || ! hash_equals($token, $provided)) {
            return 'none';
        }
        $exp = self::expiresAt();
        if ($exp === null || $exp->isPast()) {
            return 'expired';
        }
        return 'valid';
    }

    /** Rotate to a fresh token and (re)open the window. Invalidates every old link. */
    public static function rotate(int $days = self::DEFAULT_WINDOW_DAYS): string
    {
        $token = Str::random(40);
        setOption(self::OPT_TOKEN, $token);
        setOption(self::OPT_EXPIRES, Carbon::now()->addDays(max(1, $days))->toDateTimeString());
        return $token;
    }

    /** Set the expiry explicitly (extend, or set to the past to close invites immediately). */
    public static function setExpiry(Carbon $when): void
    {
        setOption(self::OPT_EXPIRES, $when->toDateTimeString());
    }
}
