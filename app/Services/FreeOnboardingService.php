<?php

namespace App\Services;

use App\Models\Affiliate;
use App\Models\Lead;
use App\Models\Owner;
use App\Models\User;
use App\Jobs\Mail\SendFreeInviteMail;
use App\Jobs\Mail\SendFreeActivatedMail;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * The "Set up on Free" consent-gated onboarding flow.
 *
 * An affiliate invites a lead to activate a FREE owner account from the lead card. We DON'T mint
 * the account on click — we send a time-boxed invite and create the account only when the OWNER
 * accepts (opens the link and sets their own password). The click + password are the owner's
 * consent, and un-accepted invites simply expire — so the owners table never fills with phantom,
 * never-used accounts. Attribution (owners.affiliate_id) is stamped on activation.
 *
 * Invite state is an overlay on the lead (the free_invite_* columns), derived here — the lead
 * status machine is untouched. On acceptance the lead moves to 'converted'.
 */
class FreeOnboardingService
{
    public function __construct(private OwnerOnboardingService $owners)
    {
    }

    /** Days an invite stays valid before it lapses (config-tunable). */
    public function expiryDays(): int
    {
        return max(1, (int) getOption('affiliate_free_invite_expiry_days', 7));
    }

    /** How many un-accepted invites one affiliate may have outstanding at once (anti-phantom cap). */
    public function maxPending(): int
    {
        return max(1, (int) getOption('affiliate_free_invite_max_pending', 15));
    }

    /**
     * Derive the invite state for a lead from its overlay columns:
     *   none | pending | accepted | expired
     */
    public function inviteState(Lead $lead): string
    {
        if ($lead->free_invite_accepted_at) {
            return 'accepted';
        }
        if (! $lead->free_invite_sent_at) {
            return 'none';
        }
        if ($lead->free_invite_expires_at && $lead->free_invite_expires_at->isPast()) {
            return 'expired';
        }
        return 'pending';
    }

    /** Count an affiliate's live (sent, not accepted, not yet expired) invites. */
    public function pendingCountForAffiliate(int $affiliateUserId): int
    {
        return Lead::where('affiliate_id', $affiliateUserId)
            ->whereNotNull('free_invite_sent_at')
            ->whereNull('free_invite_accepted_at')
            ->where('free_invite_expires_at', '>', now())
            ->count();
    }

    /**
     * Whether the affiliate can (re)send a Free invite for this lead right now.
     *
     * @return array{ok: bool, reason: string}
     */
    public function canInvite(Lead $lead): array
    {
        $state = $this->inviteState($lead);

        if ($state === 'accepted' || $lead->status === 'converted') {
            return ['ok' => false, 'reason' => 'This lead has already activated a Centresidence account.'];
        }
        if (in_array($lead->status, ['rejected', 'lost'])) {
            return ['ok' => false, 'reason' => 'This lead is closed.'];
        }
        // Trial-in-flight paths own the lead — don't cross the streams.
        if (in_array($lead->status, ['pending_conversion', 'trial'])) {
            return ['ok' => false, 'reason' => 'A trial request is already in progress for this lead.'];
        }

        $email = $lead->company->email ?? null;
        if (empty($email) || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['ok' => false, 'reason' => 'Add a valid email to this lead first — the activation link is delivered there.'];
        }

        // An existing account means there's nothing to create (they can just sign in).
        if (User::where('email', $email)->exists()) {
            return ['ok' => false, 'reason' => 'This person already has a Centresidence account — they can simply sign in.'];
        }

        return ['ok' => true, 'reason' => ''];
    }

    /**
     * Create (or re-send) a Free activation invite for a lead and notify the owner.
     *
     * @return array{success: bool, message: string}
     */
    public function createInvite(Lead $lead, User $affiliateUser): array
    {
        $gate = $this->canInvite($lead);
        if (! $gate['ok']) {
            return ['success' => false, 'message' => $gate['reason']];
        }

        // Anti-phantom cap: bound how many invites one affiliate can have in flight. A resend of
        // THIS lead's own live invite doesn't count against the cap (it isn't a new prospect).
        $thisIsLive = $this->inviteState($lead) === 'pending';
        if (! $thisIsLive && $this->pendingCountForAffiliate((int) $affiliateUser->id) >= $this->maxPending()) {
            return [
                'success' => false,
                'message' => 'You have ' . $this->maxPending() . ' free-account invites still waiting to be accepted. '
                    . 'Follow those up (or let them lapse) before sending more.',
            ];
        }

        $email = $lead->company->email;

        $lead->forceFill([
            'free_invite_token'       => $this->freshToken(),
            'free_invite_email'       => $email,
            'free_invite_sent_at'     => now(),
            'free_invite_expires_at'  => now()->addDays($this->expiryDays()),
            'free_invite_accepted_at' => null,
            'free_invite_count'       => (int) $lead->free_invite_count + 1,
            'last_activity_at'        => now(),
        ])->save();

        app(LeadService::class)->logActivity(
            $lead,
            'free_invite_sent',
            'Free-account activation invite sent to ' . $email . ' (expires in ' . $this->expiryDays() . ' days).',
            (int) $affiliateUser->id
        );

        SendFreeInviteMail::dispatch(
            $lead->id,
            $email,
            $lead->company->phone,
            trim(($affiliateUser->first_name ?? '') . ' ' . ($affiliateUser->last_name ?? '')) ?: null,
            $this->expiryDays(),
        );

        return [
            'success' => true,
            'message' => 'Invite sent to ' . $email . '. They activate their free account by setting a password — '
                . 'you can track whether they\'ve accepted right here. The invite lasts '
                . $this->expiryDays() . ' days.',
        ];
    }

    /**
     * Accept an invite: create the Free owner account on the owner's consent (their chosen
     * password) and attribute it to the inviting affiliate. Idempotent-ish: a second accept on an
     * already-activated invite is rejected rather than minting a duplicate.
     *
     * @return array{success: bool, message: string, user?: User}
     */
    public function acceptInvite(string $token, string $password): array
    {
        $lead = Lead::with('company')->where('free_invite_token', $token)->first();

        if (! $lead) {
            return ['success' => false, 'message' => 'This invite link is not valid.'];
        }

        $state = $this->inviteState($lead);
        if ($state === 'accepted') {
            return ['success' => false, 'message' => 'This account has already been activated — please sign in.'];
        }
        if ($state !== 'pending') {
            return ['success' => false, 'message' => 'This invite has expired. Ask your contact to send you a fresh one.'];
        }

        $email = $lead->free_invite_email ?: ($lead->company->email ?? null);
        if (empty($email)) {
            return ['success' => false, 'message' => 'This invite is missing an email address.'];
        }

        // A race or a parallel organic signup may have created the account already.
        if (User::where('email', $email)->exists()) {
            $lead->forceFill(['free_invite_accepted_at' => now()])->save();
            return ['success' => false, 'message' => 'An account already exists for this email — please sign in.'];
        }

        // Resolve the inviting affiliate (leads.affiliate_id = the affiliate's USER id) to the
        // affiliates-table PK that owners.affiliate_id references for commission attribution.
        $affiliatePk = optional(Affiliate::where('user_id', $lead->affiliate_id)->first())->id;

        $nameParts = explode(' ', (string) $lead->company->company_name, 2);

        try {
            $result = DB::transaction(function () use ($lead, $email, $password, $affiliatePk, $nameParts) {
                $created = $this->owners->create([
                    'first_name'   => $lead->contact_person_name ?: ($nameParts[0] ?? 'Owner'),
                    'last_name'    => $nameParts[1] ?? '',
                    'email'        => $email,
                    'phone'        => $lead->company->phone,
                    'password'     => $password,         // owner-chosen at consent → no forced reset
                    'affiliate_id' => $affiliatePk,
                ]);

                $owner = $created['owner'];

                $lead->forceFill([
                    'status'                  => 'converted',
                    'owner_id'                => $owner->id,
                    'converted_at'            => now(),
                    'free_invite_accepted_at' => now(),
                    'last_activity_at'        => now(),
                ])->save();

                if ($lead->company) {
                    $lead->company->update(['sales_status' => 'client']);
                }

                app(LeadService::class)->logActivity(
                    $lead,
                    'free_invite_accepted',
                    'Owner accepted the invite and activated a Free account.'
                );

                return $created;
            });
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Free invite acceptance failed: ' . $e->getMessage(), ['lead_id' => $lead->id]);
            return ['success' => false, 'message' => 'Something went wrong activating your account. Please try again.'];
        }

        // Notify the inviting affiliate (email + in-app bell) that their lead is now live on Free.
        SendFreeActivatedMail::dispatch($lead->id, (int) $result['user']->id);

        return ['success' => true, 'message' => 'Your free account is ready.', 'user' => $result['user']];
    }

    /** A unique, URL-safe invite token. */
    private function freshToken(): string
    {
        do {
            $token = Str::random(48);
        } while (Lead::where('free_invite_token', $token)->exists());

        return $token;
    }
}
