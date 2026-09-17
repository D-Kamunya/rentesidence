<?php

namespace App\Services;

use App\Models\LandlordReferral;
use App\Models\LandlordReferralCode;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * The invite-a-landlord funnel engine.
 *
 * Two responsibilities, kept separate on purpose:
 *   1. The referral PRIMITIVE — a tenant's stable invite code, recording an invite, and
 *      attaching the resulting Lead when the invited landlord submits the intake form.
 *      This always runs, regardless of whether any reward is switched on.
 *   2. The reward STATE MACHINE — confirming a reward once the referred owner becomes a
 *      real customer (real money through us), holding it, paying it, or clawing it back.
 *      The cash reward itself is a config-gated rule (config/referrals.php); with cash off
 *      the funnel still works on the primitive + graduation-to-affiliate.
 *
 * Money invariant: a referral can be rewarded at most once, and only a real customer
 * (an owner who moved real money) can trigger a cash reward — a phantom owner cannot.
 */
class LandlordReferralService
{
    /** Whether the funnel is switched on at all (per market / season). */
    public function enabled(): bool
    {
        return (bool) config('referrals.enabled', true);
    }

    /** Whether the cash reward layer is on. Off ⇒ funnel still runs (non-cash + graduation). */
    public function cashEnabled(): bool
    {
        return $this->enabled() && (bool) config('referrals.cash_enabled', true);
    }

    // -------------------------------------------------------------------------
    // The primitive — code + invite + lead attachment
    // -------------------------------------------------------------------------

    /** Get (or lazily create) the tenant's single stable, shareable invite code. */
    public function codeForTenant(int $userId): string
    {
        $row = LandlordReferralCode::firstOrCreate(
            ['user_id' => $userId],
            ['code' => $this->generateUniqueCode()]
        );

        return $row->code;
    }

    /** Resolve the referring tenant behind an invite code, or null if unknown. */
    public function resolveReferrer(string $code): ?User
    {
        $row = LandlordReferralCode::where('code', $code)->first();

        return $row ? User::find($row->user_id) : null;
    }

    /**
     * Record an invite the tenant is sending to a specific landlord.
     * Returns null (silently) when the funnel is off or the tenant is over the daily
     * velocity cap — the caller shows a soft message; nothing is written past the cap.
     */
    public function startInvite(User $referrer, array $invitee): ?LandlordReferral
    {
        if (! $this->enabled()) {
            return null;
        }

        if ($this->invitesToday($referrer->id) >= (int) config('referrals.anti_abuse.max_invites_per_day', 20)) {
            return null;
        }

        return LandlordReferral::create([
            'referrer_user_id' => $referrer->id,
            'code'             => $this->codeForTenant($referrer->id),
            'invitee_name'     => $invitee['name'] ?? null,
            'invitee_phone'    => $this->normalizePhone($invitee['phone'] ?? null),
            'invitee_email'    => $invitee['email'] ?? null,
            'invitee_company'  => $invitee['company'] ?? null,
            'status'           => LandlordReferral::STATUS_PENDING,
            'meta'             => ['self_referral' => $this->looksLikeSelfReferral($referrer, $invitee)],
        ]);
    }

    /**
     * Tie a Lead (created from the public intake form) back to a referral.
     * Prefers an existing PENDING invite the tenant already logged for this landlord
     * (matched by phone/email); otherwise opens a fresh lead_created row. Idempotent
     * on the lead — a lead is never attached twice.
     */
    public function attachLead(string $code, Lead $lead, array $invitee = []): ?LandlordReferral
    {
        if (! $this->enabled()) {
            return null;
        }

        $referrer = $this->resolveReferrer($code);
        if (! $referrer) {
            return null;
        }

        // A lead is only ever attributed to one referral.
        if ($existing = LandlordReferral::where('lead_id', $lead->id)->first()) {
            return $existing;
        }

        $phone = $this->normalizePhone($invitee['phone'] ?? null);
        $email = $invitee['email'] ?? null;

        $referral = LandlordReferral::where('referrer_user_id', $referrer->id)
            ->where('status', LandlordReferral::STATUS_PENDING)
            ->when($phone || $email, function ($q) use ($phone, $email) {
                $q->where(function ($q2) use ($phone, $email) {
                    if ($phone) {
                        $q2->orWhere('invitee_phone', $phone);
                    }
                    if ($email) {
                        $q2->orWhere('invitee_email', $email);
                    }
                });
            })
            ->latest()
            ->first();

        if (! $referral) {
            $referral = LandlordReferral::create([
                'referrer_user_id' => $referrer->id,
                'code'             => $code,
                'invitee_name'     => $invitee['name'] ?? null,
                'invitee_phone'    => $phone,
                'invitee_email'    => $email,
                'invitee_company'  => $invitee['company'] ?? null,
                'status'           => LandlordReferral::STATUS_PENDING,
                'meta'             => ['self_referral' => $this->looksLikeSelfReferral($referrer, $invitee)],
            ]);
        }

        $referral->update([
            'lead_id'         => $lead->id,
            'status'          => LandlordReferral::STATUS_LEAD_CREATED,
            'invitee_name'    => $referral->invitee_name ?: ($invitee['name'] ?? null),
            'invitee_phone'   => $referral->invitee_phone ?: $phone,
            'invitee_email'   => $referral->invitee_email ?: $email,
            'invitee_company' => $referral->invitee_company ?: ($invitee['company'] ?? null),
        ]);

        return $referral->fresh();
    }

    // -------------------------------------------------------------------------
    // The reward state machine
    // -------------------------------------------------------------------------

    /**
     * The referred owner became a real customer — confirm the tenant's reward.
     *
     * Called from the real-money signal (first paid subscription, or cumulative revenue
     * crossing the threshold). Idempotent: a referral already rewarded is left untouched,
     * and only pending / lead_created referrals advance. Returns the confirmed referral,
     * or null when there's nothing to confirm (no referral, already rewarded, funnel off).
     *
     * @param  int     $ownerId  owners.id of the referred owner
     * @param  string  $reason   first_subscription | revenue_threshold | manual
     */
    public function confirmForOwner(int $ownerId, string $reason): ?LandlordReferral
    {
        if (! $this->enabled()) {
            return null;
        }

        return DB::transaction(function () use ($ownerId, $reason) {
            $referral = LandlordReferral::where('owner_id', $ownerId)
                ->lockForUpdate()
                ->first()
                ?? LandlordReferral::whereHas('lead', fn ($q) => $q->where('owner_id', $ownerId))
                    ->lockForUpdate()
                    ->first();

            if (! $referral) {
                return null;
            }

            // Idempotent + only forward from a non-rewarded, open state.
            if ($referral->isRewarded() || $referral->isClosed()) {
                return null;
            }

            [$rewardType, $amount] = $this->resolveReward();

            $referral->update([
                'owner_id'       => $ownerId,
                'status'         => LandlordReferral::STATUS_CONFIRMED,
                'reward_type'    => $rewardType,
                'reward_amount'  => $amount,
                'currency'       => $rewardType === LandlordReferral::REWARD_CASH ? config('referrals.currency', 'KES') : null,
                'trigger_reason' => $reason,
                'confirmed_at'   => now(),
                'held_until'     => now()->addDays((int) config('referrals.hold_days', 30)),
                'needs_review'   => $this->shouldFlagForReview($referral),
            ]);

            return $referral->fresh();
        });
    }

    /**
     * Confirm from an owner's USER id (what the payment paths carry) rather than owners.id.
     * Resolves the Owner record, then delegates. Safe to call on every payment — the
     * once-only guard in confirmForOwner() means only the first real-money event rewards.
     */
    public function confirmForOwnerUser(int $ownerUserId, string $reason): ?LandlordReferral
    {
        if (! $this->enabled()) {
            return null;
        }

        $ownerId = \App\Models\Owner::where('user_id', $ownerUserId)->value('id');

        return $ownerId ? $this->confirmForOwner((int) $ownerId, $reason) : null;
    }

    /**
     * Reverse a confirmed (not-yet-paid) reward when the referred owner churns or refunds
     * inside the holding window. A reward already paid out is out of scope here (that's a
     * recovery decision, not an automatic clawback).
     */
    public function clawback(int $ownerId, string $reason = 'churn'): ?LandlordReferral
    {
        $referral = LandlordReferral::where('owner_id', $ownerId)
            ->where('status', LandlordReferral::STATUS_CONFIRMED)
            ->first();

        if (! $referral) {
            return null;
        }

        $meta = $referral->meta ?? [];
        $meta['clawback_reason'] = $reason;

        $referral->update([
            'status'         => LandlordReferral::STATUS_CLAWED_BACK,
            'clawed_back_at' => now(),
            'meta'           => $meta,
        ]);

        return $referral->fresh();
    }

    /** Mark a payable reward as paid (called by the payout run once released). */
    public function markPaid(LandlordReferral $referral): void
    {
        $referral->update([
            'status'  => LandlordReferral::STATUS_PAID,
            'paid_at' => now(),
        ]);
    }

    /** Sum of a tenant's rewards that are confirmed, held-period elapsed, and clear to pay. */
    public function payableBalance(int $referrerUserId): float
    {
        return (float) LandlordReferral::where('referrer_user_id', $referrerUserId)
            ->where('status', LandlordReferral::STATUS_CONFIRMED)
            ->where('reward_type', LandlordReferral::REWARD_CASH)
            ->where('needs_review', false)
            ->whereNotNull('held_until')
            ->where('held_until', '<=', now())
            ->sum('reward_amount');
    }

    // -------------------------------------------------------------------------
    // Graduation
    // -------------------------------------------------------------------------

    /** Confirmed-or-paid referral count — the proof a tenant is a channel. */
    public function confirmedCount(int $referrerUserId): int
    {
        return LandlordReferral::where('referrer_user_id', $referrerUserId)
            ->whereIn('status', LandlordReferral::REWARDED_STATUSES)
            ->count();
    }

    /** Whether the tenant has earned the offer to become a full affiliate. */
    public function graduationEligible(int $referrerUserId): bool
    {
        return $this->confirmedCount($referrerUserId)
            >= (int) config('referrals.graduation_threshold', 3);
    }

    // -------------------------------------------------------------------------
    // Internals
    // -------------------------------------------------------------------------

    /** Cash when the cash layer is on and set; otherwise nothing (non-cash rail is pending). */
    private function resolveReward(): array
    {
        $amount = (float) config('referrals.cash_amount', 0);

        if ($this->cashEnabled() && $amount > 0) {
            return [LandlordReferral::REWARD_CASH, $amount];
        }

        // Non-cash activation credits ride the Helper-credit rail, which isn't built yet.
        // Until then a cash-off funnel still confirms attribution + graduation with no reward.
        return [LandlordReferral::REWARD_NONE, 0.0];
    }

    /** Flag for a human when the referrer looks like a self-referral or is spiking. */
    private function shouldFlagForReview(LandlordReferral $referral): bool
    {
        if (! empty($referral->meta['self_referral'])) {
            return true;
        }

        $recentConfirms = LandlordReferral::where('referrer_user_id', $referral->referrer_user_id)
            ->where('status', LandlordReferral::STATUS_CONFIRMED)
            ->where('confirmed_at', '>=', now()->subDays(30))
            ->count();

        return $recentConfirms >= (int) config('referrals.anti_abuse.manual_review_after', 10);
    }

    /** Shared-instrument smell test: the invited landlord's contact matches the referrer's own. */
    private function looksLikeSelfReferral(User $referrer, array $invitee): bool
    {
        $refPhone = $this->normalizePhone($referrer->contact_number ?? null);
        $invPhone = $this->normalizePhone($invitee['phone'] ?? null);

        if ($refPhone && $invPhone && $refPhone === $invPhone) {
            return true;
        }

        $refEmail = strtolower(trim((string) ($referrer->email ?? '')));
        $invEmail = strtolower(trim((string) ($invitee['email'] ?? '')));

        return $refEmail !== '' && $refEmail === $invEmail;
    }

    private function invitesToday(int $referrerUserId): int
    {
        return LandlordReferral::where('referrer_user_id', $referrerUserId)
            ->where('created_at', '>=', Carbon::today())
            ->count();
    }

    private function normalizePhone(?string $phone): ?string
    {
        if ($phone === null) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $phone);

        return $digits === '' ? null : $digits;
    }

    /** 8-char uppercase alphanumeric, collision-checked. */
    private function generateUniqueCode(): string
    {
        do {
            $code = strtoupper(Str::random(8));
        } while (LandlordReferralCode::where('code', $code)->exists());

        return $code;
    }
}
