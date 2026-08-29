<?php

namespace App\Services\Sms;

use App\Models\Owner;
use App\Models\SmsHistory;
use App\Jobs\SendSmsCreditsEmailJob;
use App\Services\Credit\CreditService;
use Illuminate\Support\Facades\Log;

/**
 * SMS-credit domain facade over the shared money rail (CreditService, bucket 'sms'). The
 * atomic balance mechanics, the granted/purchased pool split and idempotent top-ups all
 * live in CreditService; what stays here is SMS-specific: low/zero-balance notifications,
 * the monthly package-grant reset, retry lists, and pricing helpers.
 */
class SmsCreditsService
{
    private const BUCKET = 'sms';

    public static function getOwner(?int $ownerUserId): ?Owner
    {
        return CreditService::getOwner($ownerUserId);
    }

    /** Current credit balance for an owner. */
    public static function balance(?int $ownerUserId): int
    {
        return CreditService::balance(self::BUCKET, $ownerUserId);
    }

    /**
     * Pre-flight check: does the owner have enough SMS credit to cover `$need` messages?
     * Standalone installs (no owner) are unmetered → always true. Use this to warn the
     * owner on the CURRENT page before an owner-initiated send, since the actual deduction
     * happens later in a queued job and would otherwise fail silently.
     */
    public static function hasCredits(?int $ownerUserId, int $need = 1): bool
    {
        if ($ownerUserId === null) {
            return true;
        }

        return self::balance($ownerUserId) >= max(1, $need);
    }

    /**
     * Deduct one credit atomically. Returns true on success, false if insufficient. Fires
     * low/zero-balance notifications against a consistent before/after, inside the lock.
     */
    public static function deductOne(int $ownerUserId, string $description = ''): bool
    {
        return CreditService::deductOne(self::BUCKET, $ownerUserId, $description, function ($owner, $before, $after) {
            $threshold = (int) getOption('sms_low_credit_threshold', 50);
            if ($after <= $threshold && $before > $threshold) {
                self::notifyLowCredits($owner, $after);
            }
            if ($after === 0) {
                self::notifyZeroCredits($owner);
            }
        });
    }

    /**
     * Add credits — purchase, manual top-up, or refund. Credits land in the non-expiring
     * purchased pool. Returns the new balance.
     */
    public static function addCredits(
        int    $ownerUserId,
        int    $quantity,
        string $type = 'purchase',
        float  $amountPaid = 0,
        string $reference = '',
        string $description = '',
        ?int   $existingTransactionId = null
    ): int {
        return CreditService::addCredits(self::BUCKET, $ownerUserId, $quantity, [
            'type'                    => $type,
            'amount_paid'             => $amountPaid > 0 ? $amountPaid : null,
            'reference'               => $reference !== '' ? $reference : null,
            'description'             => $description,
            'existing_transaction_id' => $existingTransactionId,
        ]);
    }

    /**
     * RESET the owner's granted (package) SMS allowance for a new period — it does NOT roll
     * over. Purchased credits are untouched. `$credits` may be 0 to clear a leftover
     * allowance. Safe to call on every renewal.
     */
    public static function grantPackageCredits(int $ownerUserId, int $credits, string $packageName): void
    {
        $credits = max(0, $credits);
        CreditService::resetGrantedAllowance(
            self::BUCKET,
            $ownerUserId,
            $credits,
            "Monthly allowance reset to {$credits} from {$packageName} package"
        );
        Log::info("SmsCreditsService: reset granted allowance to {$credits} for owner_user_id={$ownerUserId} from package {$packageName}");
    }

    /**
     * Balance broken into its two pools for display: the resetting monthly allowance and the
     * owner's non-expiring purchased credits (+ the total).
     */
    public static function breakdown(?int $ownerUserId): array
    {
        return CreditService::breakdown(self::BUCKET, $ownerUserId);
    }

    /**
     * Retryable failed messages — blocked by insufficient credits, last N days.
     */
    public static function getRetryableFailed(int $ownerUserId, int $days = 30): \Illuminate\Support\Collection
    {
        return SmsHistory::where('owner_user_id', $ownerUserId)
            ->where('status', SMS_STATUS_FAILED)
            ->where('error', 'Insufficient SMS credits')
            ->where('created_at', '>=', now()->subDays($days))
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * KES amount → how many credits.
     */
    public static function creditsForAmount(float $amount): int
    {
        return CreditService::creditsForAmount(self::BUCKET, $amount);
    }

    /**
     * N credits → KES cost.
     */
    public static function amountForCredits(int $credits): float
    {
        return CreditService::amountForCredits(self::BUCKET, $credits);
    }

    private static function notifyLowCredits(Owner $owner, int $remaining): void
    {
        try {
            $title   = __('SMS Credits Running Low');
            $body    = __('You have :count SMS credits remaining. Top up to avoid interruptions.', ['count' => $remaining]);
            $url     = route('owner.sms.credits.index');
            $subject = __('Action Required: Your SMS Credits Are Running Low');
            $message = __('You have :count SMS credits remaining. Please top up soon to ensure your tenant notifications continue without interruption.', ['count' => $remaining]);

            SendSmsCreditsEmailJob::dispatch(
                $owner->user,
                (object) ['subject' => $subject, 'message' => $message],
                (object) ['title'   => $title,   'body'    => $body, 'url' => $url],
            );
        } catch (\Exception $e) {
            Log::error('SmsCreditsService: low-credits notify failed – ' . $e->getMessage());
        }
    }

    private static function notifyZeroCredits(Owner $owner): void
    {
        try {
            $title   = __('SMS Credits Exhausted');
            $body    = __('You have 0 SMS credits. Tenant SMS notifications are paused until you top up.');
            $url     = route('owner.sms.credits.index');
            $subject = __('Urgent: SMS Credits Exhausted');
            $message = __('Your SMS credit balance has reached zero. Tenant SMS notifications are currently paused. Top up now to resume sending.');

            SendSmsCreditsEmailJob::dispatch(
                $owner->user,
                (object) ['subject' => $subject, 'message' => $message],
                (object) ['title'   => $title,   'body'    => $body, 'url' => $url],
            );
        } catch (\Exception $e) {
            Log::error('SmsCreditsService: zero-credits notify failed – ' . $e->getMessage());
        }
    }

    public static function notifySendSummary(int $ownerUserId, int $sent, int $failed, int $blocked): void
    {
        try {
            $owner = self::getOwner($ownerUserId);
            if (!$owner) return;

            $total   = $sent + $failed + $blocked;
            $title   = __('SMS Send Summary');
            $body    = __(
                ':total SMS attempted — :sent sent, :blocked paused (no credits), :failed failed.',
                ['total' => $total, 'sent' => $sent, 'blocked' => $blocked, 'failed' => $failed]
            );
            $url     = route('owner.sms.credits.index');
            $subject = __('SMS Send Summary: :total Messages Attempted', ['total' => $total]);
            $message = __(
                'Here is a summary of your latest SMS batch: :total attempted, :sent delivered, :blocked paused due to insufficient credits, :failed failed.',
                ['total' => $total, 'sent' => $sent, 'blocked' => $blocked, 'failed' => $failed]
            );

            SendSmsCreditsEmailJob::dispatch(
                $owner->user,
                (object) ['subject' => $subject, 'message' => $message],
                (object) ['title'   => $title,   'body'    => $body, 'url' => $url],
            );
        } catch (\Exception $e) {
            Log::error('SmsCreditsService: send-summary notify failed – ' . $e->getMessage());
        }
    }
}
