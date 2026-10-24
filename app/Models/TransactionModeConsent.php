<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

/**
 * A recorded consent to the Transaction-mode money-path change (rent collected into the owner's
 * Centresidence wallet). Legal evidence — one row per consent event. See the create migration.
 */
class TransactionModeConsent extends Model
{
    public const VERSION         = '1.0';
    public const SOURCE_SELF     = 'self_switch';
    public const SOURCE_FINANCE  = 'financing_apply';

    protected $fillable = [
        'owner_user_id', 'source', 'consent_version', 'acknowledged_text',
        'ip_address', 'user_agent', 'consented_at',
    ];

    protected $casts = ['consented_at' => 'datetime'];

    /** Record a consent event. Never throws (consent logging must not block the money action). */
    public static function record(int $ownerUserId, string $source, ?Request $request = null, ?string $acknowledgedText = null): void
    {
        try {
            static::create([
                'owner_user_id'     => $ownerUserId,
                'source'            => $source,
                'consent_version'   => self::VERSION,
                'acknowledged_text' => $acknowledgedText,
                'ip_address'        => $request?->ip(),
                'user_agent'        => $request ? substr((string) $request->userAgent(), 0, 2000) : null,
                'consented_at'      => now(),
            ]);
        } catch (\Throwable $e) {
            // Table missing (pre-migration) or any failure: do not break the switch/apply.
        }
    }

    /** The canonical statement an owner agrees to — kept in one place so UI + record stay in step. */
    public static function statement(): string
    {
        return __('I understand that on Transaction mode my rent is collected into my Centresidence wallet (with the platform fee / any financing repayment taken at source) rather than paid directly to me, and that I withdraw it to M-Pesa. I consent to this change to how my rent is handled.');
    }
}
