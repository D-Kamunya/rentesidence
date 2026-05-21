<?php

namespace App\Services\Sms;

use App\Models\Owner;
use App\Models\SmsCreditTransaction;
use App\Models\SmsHistory;
use App\Jobs\SendSmsCreditsEmailJob;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SmsCreditsService
{
    /**
     * Resolve Owner from owner_user_id.
     */
    public static function getOwner(?int $ownerUserId): ?Owner
    {
        if (!$ownerUserId) return null;
        return Owner::where('user_id', $ownerUserId)->first();
    }

    /**
     * Current credit balance for an owner.
     */
    public static function balance(?int $ownerUserId): int
    {
        $owner = self::getOwner($ownerUserId);
        return $owner ? (int) $owner->sms_credits : 0;
    }

    /**
     * Deduct one credit atomically.
     * Returns true on success, false if insufficient.
     */
    public static function deductOne(int $ownerUserId, string $description = ''): bool
    {
        return DB::transaction(function () use ($ownerUserId, $description) {
            $owner = Owner::where('user_id', $ownerUserId)->lockForUpdate()->first();

            if (!$owner || $owner->sms_credits < 1) {
                return false;
            }

            $before = (int) $owner->sms_credits;
            $owner->decrement('sms_credits');
            $after = $before - 1;

            SmsCreditTransaction::create([
                'owner_user_id'  => $ownerUserId,
                'type'           => 'deduct',
                'quantity'       => 1,
                'amount_paid'    => null,
                'balance_before' => $before,
                'balance_after'  => $after,
                'description'    => $description,
                'status'         => 'success',
            ]);

            $threshold = (int) getOption('sms_low_credit_threshold', 50);
            if ($after <= $threshold && $before > $threshold) {
                self::notifyLowCredits($owner, $after);
            }
            if ($after === 0) {
                self::notifyZeroCredits($owner);
            }

            return true;
        });
    }

    /**
     * Add credits — purchase, manual top-up, refund, or package grant.
     * Returns new balance.
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
        return DB::transaction(function () use ($ownerUserId, $quantity, $type, $amountPaid, $reference, $description, $existingTransactionId) {
            $owner = Owner::where('user_id', $ownerUserId)->lockForUpdate()->first();
            if (!$owner) return 0;
    
            $before = (int) $owner->sms_credits;
            $owner->increment('sms_credits', $quantity);
            $after = $before + $quantity;
    
            if ($existingTransactionId) {
                // Update the pending record instead of creating a new one
                SmsCreditTransaction::where('id', $existingTransactionId)
                    ->where('status', 'pending') // safety check
                    ->update([
                        'balance_before' => $before,
                        'balance_after'  => $after,
                        'reference'      => $reference ?: null,
                        'status'         => 'success',
                    ]);
            } else {
                SmsCreditTransaction::create([
                    'owner_user_id'  => $ownerUserId,
                    'type'           => $type,
                    'quantity'       => $quantity,
                    'amount_paid'    => $amountPaid > 0 ? $amountPaid : null,
                    'balance_before' => $before,
                    'balance_after'  => $after,
                    'reference'      => $reference,
                    'description'    => $description,
                    'status'         => 'success',
                ]);
            }
    
            return $after;
        });
    }

    /**
     * Grant monthly SMS credits from a package subscription.
     * Safe to call on every renewal — records it as package_grant type.
     */
    public static function grantPackageCredits(int $ownerUserId, int $credits, string $packageName): void
    {
        if ($credits <= 0) return;

        self::addCredits(
            $ownerUserId,
            $credits,
            'package_grant',
            0,
            '',
            "Monthly grant from {$packageName} package"
        );

        Log::info("SmsCreditsService: granted {$credits} credits to owner_user_id={$ownerUserId} from package {$packageName}");
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
        $pricePerSms = (float) getOption('sms_credit_price', 1.00);
        if ($pricePerSms <= 0) return 0;
        return (int) floor($amount / $pricePerSms);
    }

    /**
     * N credits → KES cost.
     */
    public static function amountForCredits(int $credits): float
    {
        return round($credits * (float) getOption('sms_credit_price', 1.00), 2);
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